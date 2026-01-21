( function ( $ ) {
	'use strict';

	var StoreFrontFormHandler = {
		init: function () {
			this.bindEvents();
			this.initDatePicker();
			this.initProductSearch();
		},

		bindEvents: function () {
			this.handleCategoryAdd();
			this.handleCategoryEdit();
			this.handleCategoryDelete();
			this.handleTagAdd();
			this.handleTagEdit();
			this.handleTagDelete();
			this.handleBrandAdd();
			this.handleBrandEdit();
			this.handleBrandDelete();
			this.handleCouponAdd();
			this.handleCouponEdit();
			this.handleCouponDelete();
			this.handleGenerateCouponCode();
		},

		/**
		 * Initialize datepicker for expiry date field
		 */
		initDatePicker: function () {
			if ( $( '#expiry_date' ).length ) {
				$( '#expiry_date' ).datepicker( {
					defaultDate: '',
					dateFormat: 'yy-mm-dd',
					numberOfMonths: 1,
					showButtonPanel: true,
					minDate: 0, // Prevent selecting past dates
				} );
			}
		},

		/**
		 * Initialize AJAX product search for coupon form
		 */
		initProductSearch: function () {
			$( ':input.wc-product-search' )
				.filter( ':not(.enhanced)' )
				.each( function () {
					var select2_args = {
						allowClear: $( this ).data( 'allow_clear' )
							? true
							: false,
						placeholder: $( this ).data( 'placeholder' ),
						minimumInputLength: $( this ).data(
							'minimum_input_length'
						)
							? $( this ).data( 'minimum_input_length' )
							: '3',
						escapeMarkup: function ( m ) {
							return m;
						},
						ajax: {
							url: MSF_Form_Handler.ajax_url,
							dataType: 'json',
							delay: 250,
							data: function ( params ) {
								return {
									term: params.term,
									action:
										$( this ).data( 'action' ) ||
										'woocommerce_json_search_products_and_variations',
									security:
										MSF_Form_Handler.search_products_nonce,
									exclude: $( this ).data( 'exclude' ),
									exclude_type:
										$( this ).data( 'exclude_type' ),
									include: $( this ).data( 'include' ),
									limit: $( this ).data( 'limit' ),
									display_stock:
										$( this ).data( 'display_stock' ),
								};
							},
							processResults: function ( data ) {
								var terms = [];
								if ( data ) {
									$.each( data, function ( id, text ) {
										terms.push( { id: id, text: text } );
									} );
								}
								return {
									results: terms,
								};
							},
							cache: true,
						},
					};

					$( this ).selectWoo( select2_args ).addClass( 'enhanced' );
				} );
		},

		/**
		 * Handle generate coupon code button click
		 */
		handleGenerateCouponCode: function () {
			$( document ).on(
				'click',
				'.button.generate-coupon-code',
				function ( e ) {
					e.preventDefault();

					var $coupon_code_field = $( '#coupon_code' ),
						result = '',
						generator = MSF_Form_Handler.coupon_code_generator;

					// Generate random code
					for ( var i = 0; i < generator.char_length; i++ ) {
						result += generator.characters.charAt(
							Math.floor(
								Math.random() * generator.characters.length
							)
						);
					}

					// Add prefix and suffix
					result = generator.prefix + result + generator.suffix;

					// Set the generated code to the input field
					$coupon_code_field.trigger( 'focus' ).val( result );
				}
			);
		},

		/**
		 * Show loading state with SweetAlert2
		 */
		showLoading: function ( title ) {
			Swal.fire( {
				title: title || MSF_Form_Handler.i18n.processing,
				text: MSF_Form_Handler.i18n.please_wait,
				icon: 'info',
				allowOutsideClick: false,
				didOpen: () => {
					Swal.showLoading();
				},
			} );
		},

		/**
		 * Show success message
		 */
		showSuccess: function ( message ) {
			Swal.fire( {
				icon: 'success',
				title: MSF_Form_Handler.i18n.success_title,
				text: message,
				confirmButtonText: MSF_Form_Handler.i18n.ok_button,
			} );
		},

		/**
		 * Show error message
		 */
		showError: function ( message ) {
			Swal.fire( {
				icon: 'error',
				title: MSF_Form_Handler.i18n.error_title,
				text: message || MSF_Form_Handler.i18n.unexpected_error,
				confirmButtonText: MSF_Form_Handler.i18n.ok_button,
			} );
		},

		/**
		 * Handle Category Add
		 */
		handleCategoryAdd: function () {
			var self = this;

			$( document ).on( 'submit', '#msfc-add-category', function ( e ) {
				e.preventDefault();

				var $form = $( this );
				var formData = new FormData( this );

				// Validate required fields
				var categoryName = $form
					.find( '#product_category_name' )
					.val()
					.trim();

				if ( ! categoryName ) {
					Swal.fire( {
						icon: 'warning',
						title: MSF_Form_Handler.i18n.validation_error,
						text: MSF_Form_Handler.i18n.category_name_required,
						confirmButtonText: MSF_Form_Handler.i18n.ok_button,
					} );
					return;
				}

				var $submitBtn = $form.find( 'button[type="submit"]' );
				$submitBtn.prop( 'disabled', true );

				$.ajax( {
					url: MSF_Form_Handler.ajax_url,
					type: 'POST',
					data: formData,
					processData: false,
					contentType: false,
					success: function ( response ) {
						Swal.close();

						if ( response.success ) {
							self.showSuccess( response.data.message );
							$form[ 0 ].reset();
							// Clear category image.
							$( '#product_category_thumbnail_id' ).val( '' );
							$( '#product_category_thumbnail_url' ).val( '' );
							$( '#category_thumb_img' ).html( '' );
							$( '#category-single-image' ).removeClass(
								'image-drop-bg'
							);
							$(
								'#category-single-image .image-drop-text span'
							).text( MSF_Form_Handler.i18n.upload_image_text );
						} else {
							self.showError( response.data.error );
						}
					},
					error: function ( xhr, status, error ) {
						Swal.close();
						self.showError();
					},
					complete: function () {
						$submitBtn.prop( 'disabled', false );
					},
				} );
			} );
		},

		/**
		 * Handle Category Edit
		 */
		handleCategoryEdit: function () {
			var self = this;

			$( document ).on( 'submit', '#msfc-edit-category', function ( e ) {
				e.preventDefault();

				var $form = $( this );
				var formData = new FormData( this );

				// Validate required fields
				var categoryName = $form
					.find( '#product_category_name' )
					.val()
					.trim();

				if ( ! categoryName ) {
					Swal.fire( {
						icon: 'warning',
						title: MSF_Form_Handler.i18n.validation_error,
						text: MSF_Form_Handler.i18n.category_name_required,
						confirmButtonText: MSF_Form_Handler.i18n.ok_button,
					} );
					return;
				}

				var $submitBtn = $form.find( 'button[type="submit"]' );
				$submitBtn.prop( 'disabled', true );

				$.ajax( {
					url: MSF_Form_Handler.ajax_url,
					type: 'POST',
					data: formData,
					processData: false,
					contentType: false,
					success: function ( response ) {
						Swal.close();

						if ( response.success ) {
							self.showSuccess( response.data.message );
						} else {
							self.showError( response.data.error );
						}
					},
					error: function ( xhr, status, error ) {
						Swal.close();
						self.showError();
					},
					complete: function () {
						$submitBtn.prop( 'disabled', false );
					},
				} );
			} );
		},

		/**
		 * Handle Category Delete
		 */
		handleCategoryDelete: function () {
			var self = this;

			$( document ).on( 'click', '.msfc-delete-category', function ( e ) {
				e.preventDefault();

				var categoryId = $( this ).data( 'category-id' );

				if ( ! categoryId ) {
					return;
				}

				Swal.fire( {
					title: MSF_Form_Handler.i18n.are_you_sure,
					text: MSF_Form_Handler.i18n.delete_category_warning,
					icon: 'warning',
					showCancelButton: true,
					confirmButtonText: MSF_Form_Handler.i18n.yes_delete,
					cancelButtonText: MSF_Form_Handler.i18n.cancel_button,
				} ).then( function ( result ) {
					if ( ! result.isConfirmed ) {
						return;
					}

					self.showLoading( MSF_Form_Handler.i18n.deleting );

					var formData = new FormData();
					formData.append( 'id', categoryId );
					formData.append( 'action', 'msfc_delete_product_category' );
					formData.append(
						'msfc_delete_product_category_nonce',
						MSF_Form_Handler.msfc_woo_delete_nonce_
					);

					$.ajax( {
						url: MSF_Form_Handler.ajax_url,
						type: 'POST',
						data: formData,
						processData: false,
						contentType: false,
						success: function ( response ) {
							Swal.close();

							if ( response.success ) {
								self.showSuccess( response.data.message );
								$( '#category-row-' + categoryId ).fadeOut(
									300,
									function () {
										$( this ).remove();
									}
								);
							} else {
								self.showError( response.data.error );
							}
						},
						error: function ( xhr, status, error ) {
							Swal.close();
							self.showError();
						},
					} );
				} );
			} );
		},

		/**
		 * Handle Tag Add
		 */
		handleTagAdd: function () {
			var self = this;

			$( document ).on( 'submit', '#msfc-add-tag', function ( e ) {
				e.preventDefault();

				var $form = $( this );
				var formData = new FormData( this );

				// Validate required fields
				var tagName = $form.find( '#name' ).val().trim();

				if ( ! tagName ) {
					Swal.fire( {
						icon: 'warning',
						title: MSF_Form_Handler.i18n.validation_error,
						text: MSF_Form_Handler.i18n.tag_name_required,
						confirmButtonText: MSF_Form_Handler.i18n.ok_button,
					} );
					return;
				}

				var $submitBtn = $form.find( 'button[type="submit"]' );
				$submitBtn.prop( 'disabled', true );

				$.ajax( {
					url: MSF_Form_Handler.ajax_url,
					type: 'POST',
					data: formData,
					processData: false,
					contentType: false,
					success: function ( response ) {
						Swal.close();

						if ( response.success ) {
							self.showSuccess( response.data.message );
							$form[ 0 ].reset();
						} else {
							self.showError( response.data.error );
						}
					},
					error: function ( xhr, status, error ) {
						Swal.close();
						self.showError();
					},
					complete: function () {
						$submitBtn.prop( 'disabled', false );
					},
				} );
			} );
		},

		/**
		 * Handle Tag Edit
		 */
		handleTagEdit: function () {
			var self = this;

			$( document ).on( 'submit', '#msfc-edit-tag', function ( e ) {
				e.preventDefault();

				var $form = $( this );
				var formData = new FormData( this );

				// Validate required fields
				var tagName = $form.find( '#name' ).val().trim();

				if ( ! tagName ) {
					Swal.fire( {
						icon: 'warning',
						title: MSF_Form_Handler.i18n.validation_error,
						text: MSF_Form_Handler.i18n.tag_name_required,
						confirmButtonText: MSF_Form_Handler.i18n.ok_button,
					} );
					return;
				}

				var $submitBtn = $form.find( 'button[type="submit"]' );
				$submitBtn.prop( 'disabled', true );

				$.ajax( {
					url: MSF_Form_Handler.ajax_url,
					type: 'POST',
					data: formData,
					processData: false,
					contentType: false,
					success: function ( response ) {
						Swal.close();

						if ( response.success ) {
							self.showSuccess( response.data.message );
						} else {
							self.showError( response.data.error );
						}
					},
					error: function ( xhr, status, error ) {
						Swal.close();
						self.showError();
					},
					complete: function () {
						$submitBtn.prop( 'disabled', false );
					},
				} );
			} );
		},

		/**
		 * Handle Tag Delete
		 */
		handleTagDelete: function () {
			var self = this;

			$( document ).on( 'click', '.msfc-delete-tag', function ( e ) {
				e.preventDefault();

				var tagId = $( this ).data( 'tag-id' );

				if ( ! tagId ) {
					return;
				}

				Swal.fire( {
					title: MSF_Form_Handler.i18n.are_you_sure,
					text: MSF_Form_Handler.i18n.delete_tag_warning,
					icon: 'warning',
					showCancelButton: true,
					confirmButtonText: MSF_Form_Handler.i18n.yes_delete,
					cancelButtonText: MSF_Form_Handler.i18n.cancel_button,
				} ).then( function ( result ) {
					if ( ! result.isConfirmed ) {
						return;
					}

					self.showLoading( MSF_Form_Handler.i18n.deleting );

					var formData = new FormData();
					formData.append( 'id', tagId );
					formData.append( 'action', 'msfc_delete_product_tag' );
					formData.append(
						'msfc_delete_product_tag_nonce',
						MSF_Form_Handler.msfc_woo_delete_nonce_
					);

					$.ajax( {
						url: MSF_Form_Handler.ajax_url,
						type: 'POST',
						data: formData,
						processData: false,
						contentType: false,
						success: function ( response ) {
							Swal.close();

							if ( response.success ) {
								self.showSuccess( response.data.message );
								$( '#tag-row-' + tagId ).fadeOut(
									300,
									function () {
										$( this ).remove();
									}
								);
							} else {
								self.showError( response.data.error );
							}
						},
						error: function ( xhr, status, error ) {
							Swal.close();
							self.showError();
						},
					} );
				} );
			} );
		},

		/**
		 * Handle Brand Add
		 */
		handleBrandAdd: function () {
			var self = this;

			$( document ).on( 'submit', '#msfc-add-brand', function ( e ) {
				e.preventDefault();

				var $form = $( this );
				var formData = new FormData( this );

				// Validate required fields
				var brandName = $form
					.find( '#product_brand_name' )
					.val()
					.trim();

				if ( ! brandName ) {
					Swal.fire( {
						icon: 'warning',
						title: MSF_Form_Handler.i18n.validation_error,
						text: MSF_Form_Handler.i18n.brand_name_required,
						confirmButtonText: MSF_Form_Handler.i18n.ok_button,
					} );
					return;
				}

				var $submitBtn = $form.find( 'button[type="submit"]' );
				$submitBtn.prop( 'disabled', true );

				$.ajax( {
					url: MSF_Form_Handler.ajax_url,
					type: 'POST',
					data: formData,
					processData: false,
					contentType: false,
					success: function ( response ) {
						Swal.close();

						if ( response.success ) {
							self.showSuccess( response.data.message );
							$form[ 0 ].reset();
							// Clear brand image.
							$( '#product_brand_thumbnail_id' ).val( '' );
							$( '#product_brand_thumbnail_url' ).val( '' );
							$( '#brand_thumb_img' ).html( '' );
							$( '#brand-single-image' ).removeClass(
								'image-drop-bg'
							);
							$(
								'#brand-single-image .image-drop-text span'
							).text( MSF_Form_Handler.i18n.upload_image_text );
						} else {
							self.showError( response.data.error );
						}
					},
					error: function ( xhr, status, error ) {
						Swal.close();
						self.showError();
					},
					complete: function () {
						$submitBtn.prop( 'disabled', false );
					},
				} );
			} );
		},

		/**
		 * Handle Brand Edit
		 */
		handleBrandEdit: function () {
			var self = this;

			$( document ).on( 'submit', '#msfc-edit-brand', function ( e ) {
				e.preventDefault();

				var $form = $( this );
				var formData = new FormData( this );

				// Validate required fields
				var brandName = $form
					.find( '#product_brand_name' )
					.val()
					.trim();

				if ( ! brandName ) {
					Swal.fire( {
						icon: 'warning',
						title: MSF_Form_Handler.i18n.validation_error,
						text: MSF_Form_Handler.i18n.brand_name_required,
						confirmButtonText: MSF_Form_Handler.i18n.ok_button,
					} );
					return;
				}

				var $submitBtn = $form.find( 'button[type="submit"]' );
				$submitBtn.prop( 'disabled', true );

				$.ajax( {
					url: MSF_Form_Handler.ajax_url,
					type: 'POST',
					data: formData,
					processData: false,
					contentType: false,
					success: function ( response ) {
						Swal.close();

						if ( response.success ) {
							self.showSuccess( response.data.message );
						} else {
							self.showError( response.data.error );
						}
					},
					error: function ( xhr, status, error ) {
						Swal.close();
						self.showError();
					},
					complete: function () {
						$submitBtn.prop( 'disabled', false );
					},
				} );
			} );
		},

		/**
		 * Handle Brand Delete
		 */
		handleBrandDelete: function () {
			var self = this;

			$( document ).on( 'click', '.msfc-delete-brand', function ( e ) {
				e.preventDefault();

				var brandId = $( this ).data( 'brand-id' );

				if ( ! brandId ) {
					return;
				}

				Swal.fire( {
					title: MSF_Form_Handler.i18n.are_you_sure,
					text: MSF_Form_Handler.i18n.delete_brand_warning,
					icon: 'warning',
					showCancelButton: true,
					confirmButtonText: MSF_Form_Handler.i18n.yes_delete,
					cancelButtonText: MSF_Form_Handler.i18n.cancel_button,
				} ).then( function ( result ) {
					if ( ! result.isConfirmed ) {
						return;
					}

					var formData = new FormData();
					formData.append( 'id', brandId );
					formData.append( 'action', 'msfc_delete_product_brand' );
					formData.append(
						'msfc_delete_product_brand_nonce',
						MSF_Form_Handler.msfc_woo_delete_nonce_
					);

					$.ajax( {
						url: MSF_Form_Handler.ajax_url,
						type: 'POST',
						data: formData,
						processData: false,
						contentType: false,
						success: function ( response ) {
							Swal.close();

							if ( response.success ) {
								self.showSuccess( response.data.message );
								$( '#brand-row-' + brandId ).fadeOut(
									300,
									function () {
										$( this ).remove();
									}
								);
							} else {
								self.showError( response.data.error );
							}
						},
						error: function ( xhr, status, error ) {
							Swal.close();
							self.showError();
						},
					} );
				} );
			} );
		},

		/**
		 * Handle Coupon Add
		 */
		handleCouponAdd: function () {
			var self = this;

			$( document ).on( 'submit', '#msf-add-coupon', function ( e ) {
				e.preventDefault();

				var $form = $( this );
				var formData = new FormData( this );

				// Validate required fields
				var couponCode = $form.find( '#coupon_code' ).val().trim();
				var couponAmount = $form.find( '#coupon_amount' ).val().trim();

				if ( ! couponCode ) {
					Swal.fire( {
						icon: 'warning',
						title: MSF_Form_Handler.i18n.validation_error,
						text: MSF_Form_Handler.i18n.coupon_code_required,
						confirmButtonText: MSF_Form_Handler.i18n.ok_button,
					} );
					return;
				}

				if ( ! couponAmount || parseFloat( couponAmount ) < 0 ) {
					Swal.fire( {
						icon: 'warning',
						title: MSF_Form_Handler.i18n.validation_error,
						text: MSF_Form_Handler.i18n.coupon_amount_required,
						confirmButtonText: MSF_Form_Handler.i18n.ok_button,
					} );
					return;
				}

				var $submitBtn = $form.find( 'button[type="submit"]' );
				$submitBtn.prop( 'disabled', true );

				$.ajax( {
					url: MSF_Form_Handler.ajax_url,
					type: 'POST',
					data: formData,
					processData: false,
					contentType: false,
					success: function ( response ) {
						Swal.close();

						if ( response.success ) {
							self.showSuccess( response.data.message );
							setTimeout( function () {
								window.location.href =
									MSF_Form_Handler.coupons_url ||
									window.location.href.replace(
										'add-new-coupon',
										'coupons'
									);
							}, 1500 );
						} else {
							self.showError( response.data.error );
						}
					},
					error: function ( xhr, status, error ) {
						Swal.close();
						self.showError();
					},
					complete: function () {
						$submitBtn.prop( 'disabled', false );
					},
				} );
			} );
		},

		/**
		 * Handle Coupon Edit
		 */
		handleCouponEdit: function () {
			var self = this;

			$( document ).on( 'submit', '#msf-edit-coupon', function ( e ) {
				e.preventDefault();

				var $form = $( this );
				var formData = new FormData( this );

				// Validate required fields
				var couponCode = $form.find( '#coupon_code' ).val().trim();
				var couponAmount = $form.find( '#coupon_amount' ).val().trim();

				if ( ! couponCode ) {
					Swal.fire( {
						icon: 'warning',
						title: MSF_Form_Handler.i18n.validation_error,
						text: MSF_Form_Handler.i18n.coupon_code_required,
						confirmButtonText: MSF_Form_Handler.i18n.ok_button,
					} );
					return;
				}

				if ( ! couponAmount || parseFloat( couponAmount ) < 0 ) {
					Swal.fire( {
						icon: 'warning',
						title: MSF_Form_Handler.i18n.validation_error,
						text: MSF_Form_Handler.i18n.coupon_amount_required,
						confirmButtonText: MSF_Form_Handler.i18n.ok_button,
					} );
					return;
				}

				var $submitBtn = $form.find( 'button[type="submit"]' );
				$submitBtn.prop( 'disabled', true );

				$.ajax( {
					url: MSF_Form_Handler.ajax_url,
					type: 'POST',
					data: formData,
					processData: false,
					contentType: false,
					success: function ( response ) {
						Swal.close();

						if ( response.success ) {
							self.showSuccess( response.data.message );
							setTimeout( function () {
								window.location.href =
									MSF_Form_Handler.coupons_url ||
									window.location.href.replace(
										/edit-coupon\/\d+/,
										'coupons'
									);
							}, 1500 );
						} else {
							self.showError( response.data.error );
						}
					},
					error: function ( xhr, status, error ) {
						Swal.close();
						self.showError();
					},
					complete: function () {
						$submitBtn.prop( 'disabled', false );
					},
				} );
			} );
		},

		/**
		 * Handle Coupon Delete
		 */
		handleCouponDelete: function () {
			var self = this;

			$( document ).on( 'submit', '.delete-coupon-form', function ( e ) {
				e.preventDefault();

				var $form = $( this );
				var couponId = $form.find( 'input[name="coupon_id"]' ).val();

				if ( ! couponId ) {
					return;
				}

				Swal.fire( {
					title: MSF_Form_Handler.i18n.are_you_sure,
					text: MSF_Form_Handler.i18n.delete_coupon_warning,
					icon: 'warning',
					showCancelButton: true,
					confirmButtonText: MSF_Form_Handler.i18n.yes_delete,
					cancelButtonText: MSF_Form_Handler.i18n.cancel_button,
				} ).then( function ( result ) {
					if ( ! result.isConfirmed ) {
						return;
					}

					var formData = new FormData( $form[ 0 ] );

					$.ajax( {
						url: MSF_Form_Handler.ajax_url,
						type: 'POST',
						data: formData,
						processData: false,
						contentType: false,
						success: function ( response ) {
							Swal.close();

							if ( response.success ) {
								self.showSuccess( response.data.message );
								$form
									.closest( 'tr' )
									.fadeOut( 300, function () {
										$( this ).remove();
										// Reload page if no coupons left
										if (
											$( '.single-coupon-item' )
												.length === 0
										) {
											window.location.reload();
										}
									} );
							} else {
								self.showError( response.data.error );
							}
						},
						error: function ( xhr, status, error ) {
							Swal.close();
							self.showError();
						},
					} );
				} );

				return false;
			} );
		},
	};

	StoreFrontFormHandler.init();
} )( jQuery );
