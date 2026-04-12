( function ( $ ) {
	var StoreFrontProduct = {
		init: function () {
			this.bindEvents();
			this.errorTips();
			this.initSalePriceSchedule();
			this.initSelect2();
			this.toggleStockFields();
			this.salePriceDatesPicker();
			this.handleProductSubmit();
			this.handleProductBulkEditSubmit();
			this.handleProductDelete();
			this.initProductBulkEditModal();
			this.initProductInlineQuickEditTable();
		},
		bindEvents: function () {
			var self = this;
			$( document ).on( 'change', '#_manage_stock', function () {
				self.toggleStockFields();
			} );
			$( document.body ).on(
				'keyup',
				'input[type=text][name*=_global_unique_id]',
				this.validateGlobalUniqueIdOnKeyUp
			);
			$( document.body ).on(
				'change',
				'input[type=text][name*=_global_unique_id]',
				this.validateGlobalUniqueIdOnChange
			);
			$( document ).on(
				'click',
				'.sale_schedule',
				this.openSaleSchedule
			);
			$( document ).on(
				'click',
				'.cancel_sale_schedule',
				this.cancelSaleSchedule
			);
		},
		/**
		 * Common validation function for required fields
		 * Makes field border red and shows error message below invalid field
		 *
		 * @param {jQuery} $form - The form element
		 * @param {Array} fields - Array of objects with selector and message properties
		 * @return {boolean} - Returns true if all fields are valid, false otherwise
		 */
		validateRequiredFields: function ( $form, fields ) {
			var isValid = true;
			var self = this;

			// Clear all previous errors
			$form.find( '.storesuite-field-error' ).remove();
			$form
				.find( '.storesuite-form-control' )
				.removeClass( 'storesuite-field-invalid' );

			// Validate each field
			$.each( fields, function ( index, field ) {
				var $field = $form.find( field.selector );
				var value = $field.val();

				// Check if field is empty or invalid
				var isEmpty = false;
				if ( $field.is( 'select' ) ) {
					isEmpty = ! value || value === '';
				} else if ( field.type === 'number' ) {
					isEmpty =
						! value ||
						value.trim() === '' ||
						parseFloat( value ) < 0;
				} else {
					isEmpty = ! value || value.trim() === '';
				}

				if ( isEmpty ) {
					isValid = false;
					self.markFieldAsInvalid( $field, field.message );
				}
			} );

			return isValid;
		},

		/**
		 * Mark a field as invalid by adding red border and error message
		 *
		 * @param {jQuery} $field - The field element
		 * @param {string} message - Error message to display
		 */
		markFieldAsInvalid: function ( $field, message ) {
			// Add invalid class to field
			$field.addClass( 'storesuite-field-invalid' );

			// Create error message element
			var $errorMsg = $(
				'<span class="storesuite-field-error">' + message + '</span>'
			);

			// Insert error message after the field
			$field.after( $errorMsg );

			// Remove error on field change
			$field.one( 'input change', function () {
				$( this ).removeClass( 'storesuite-field-invalid' );
				$( this ).siblings( '.storesuite-field-error' ).remove();
			} );
		},
		/**
		 * Handle Product Submit (Add/Edit)
		 */
		handleProductSubmit: function () {
			var self = this;

			$( document ).on(
				'submit',
				'#storesuite-add-product',
				function ( e ) {
					e.preventDefault();

					var $form = $( this );

					// Define required fields for validation
					var requiredFields = [
						{
							selector: '#product_title',
							message:
								storeSuiteFormHandler.i18n
									.product_title_required,
						},
						{
							selector: '#post_type',
							message:
								storeSuiteFormHandler.i18n
									.product_type_required,
						},
						{
							selector: '#post_status',
							message:
								storeSuiteFormHandler.i18n
									.product_status_required,
						},
					];

					// Validate required fields
					if (
						! self.validateRequiredFields( $form, requiredFields )
					) {
						return;
					}

					// Force TinyMCE editor content to update the textarea
					if ( typeof tinyMCE !== 'undefined' ) {
						var editor = tinyMCE.get( 'product_description' );
						if ( editor ) {
							editor.save();
						}
					}

					var formData = new FormData( this );

					var $submitBtn = $form.find( 'button[type="submit"]' );
					$submitBtn.prop( 'disabled', true );

					// Show loader
					window.StoreSuite.storeSuiteLoader.block(
						$( '.my-storesuite-wrapper' )
					);

					$.ajax( {
						url: storeSuiteFormHandler.ajax_url,
						type: 'POST',
						data: formData,
						processData: false,
						contentType: false,
						success: function ( response ) {
							Swal.close();
							if ( response.success ) {
								Swal.fire( {
									icon: 'success',
									title: storeSuiteFormHandler.i18n
										.success_title,
									text: response.data.message,
									confirmButtonText:
										storeSuiteFormHandler.i18n.ok_button,
								} );

								// Reset form fields only if adding (not editing)
								if ( response.data.context === 'add' ) {
									$form[ 0 ].reset();

									// Clear TinyMCE editor
									if ( typeof tinyMCE !== 'undefined' ) {
										var editor = tinyMCE.get(
											'product_description'
										);
										if ( editor ) {
											editor.setContent( '' );
										}
									}

									// Clear select2 fields if present
									$form
										.find( '.storesuite-select2' )
										.val( null )
										.trigger( 'change' );
								}

								// Update permalink and slug field if editing product
								if (
									response.data.context === 'edit' &&
									response.data.permalink &&
									response.data.slug
								) {
									var $permalinkLink = $(
										'label[for="product_slug"] small a'
									);
									if ( $permalinkLink.length ) {
										$permalinkLink.attr(
											'href',
											response.data.permalink
										);
										$permalinkLink.text(
											response.data.permalink
										);
									}

									// Update slug field value
									var $slugField = $( '#product_slug' );
									if ( $slugField.length ) {
										$slugField.val( response.data.slug );
									}
								}
							} else {
								self.showError(
									response.data.error || response.data
								);
							}
						},
						error: function ( xhr, status, error ) {
							Swal.close();
							self.showError();
						},
						complete: function () {
							$submitBtn.prop( 'disabled', false );
							window.StoreSuite.storeSuiteLoader.unblock(
								$( '.my-storesuite-wrapper' )
							);
						},
					} );
				}
			);
		},

		/**
		 * Bulk edit products (modal): AJAX submit aligned with handleProductSubmit.
		 */
		handleProductBulkEditSubmit: function () {
			var self = this;

			$( document ).on(
				'submit',
				'#storesuite-product-bulk-edit-form',
				function ( e ) {
					e.preventDefault();

					var $form = $( this );
					var $modal = $( '#storesuite-product-bulk-edit-modal' );
					var modal =
						window.StoreSuite &&
						window.StoreSuite.storeSuiteModal;
					var bulk =
						typeof StoreSuite_Product !== 'undefined'
							? StoreSuite_Product.bulk_edit || {}
							: {};

					if ( ! $modal.length || ! bulk.nonce || ! bulk.ajax_action ) {
						return;
					}

					var formData = new FormData( this );
					formData.append( 'action', bulk.ajax_action );
					formData.append( 'security', bulk.nonce );

					var $submitBtn = $form.find( '.storesuite-bulk-edit-submit' );
					$submitBtn.prop( 'disabled', true );

					window.StoreSuite.storeSuiteLoader.block(
						$( '.my-storesuite-wrapper' )
					);

					$.ajax( {
						url: storeSuiteFormHandler.ajax_url,
						type: 'POST',
						data: formData,
						processData: false,
						contentType: false,
						success: function ( response ) {
							Swal.close();
							if ( response.success ) {
								if ( modal && $modal.length ) {
									modal.close( $modal );
								}
								Swal.fire( {
									icon: 'success',
									title:
										bulk.success_title ||
										storeSuiteFormHandler.i18n
											.success_title,
									text:
										response.data &&
										response.data.message
											? response.data.message
											: '',
									confirmButtonText:
										storeSuiteFormHandler.i18n.ok_button,
								} ).then( function () {
									window.location.reload();
								} );
							} else {
								var failMsg;
								if ( response.data ) {
									failMsg =
										response.data.message ||
										response.data.error;
									if (
										! failMsg &&
										typeof response.data === 'string'
									) {
										failMsg = response.data;
									}
								}
								self.showError( failMsg );
							}
						},
						error: function ( xhr ) {
							Swal.close();
							var msg;
							if (
								xhr &&
								xhr.responseJSON &&
								xhr.responseJSON.data
							) {
								msg =
									xhr.responseJSON.data.message ||
									xhr.responseJSON.data.error;
							}
							self.showError( msg );
						},
						complete: function () {
							$submitBtn.prop( 'disabled', false );
							window.StoreSuite.storeSuiteLoader.unblock(
								$( '.my-storesuite-wrapper' )
							);
						},
					} );
				}
			);
		},

		showError: function ( message ) {
			Swal.fire( {
				icon: 'error',
				title: storeSuiteFormHandler.i18n.error_title,
				text: message || storeSuiteFormHandler.i18n.unexpected_error,
				confirmButtonText: storeSuiteFormHandler.i18n.ok_button,
			} );
		},
		initSelect2: function () {
			$( '.storesuite-select2' )
				.filter( ':not(.enhanced)' )
				.each( function () {
					var select2_args = {
						allowClear: $( this ).data( 'allow_clear' )
							? true
							: false,
						placeholder: $( this ).data( 'placeholder' ) || '',
						minimumResultsForSearch:
							$( this ).data( 'minimum_results_for_search' ) || 0,
						width: '100%',
					};

					$( this ).selectWoo( select2_args ).addClass( 'enhanced' );
				} );
		},

		// Tear down selectWoo on inline quick edit taxonomy fields (before hide / replace).
		destroyInlineQuickEditSelectWoo: function ( $scope ) {
			if ( ! $scope || ! $scope.length ) {
				return;
			}
			if ( typeof $.fn.selectWoo !== 'function' ) {
				return;
			}
			$scope
				.find( '.storesuite-inline-quick-edit-select2.enhanced' )
				.each( function () {
					var $el = $( this );
					try {
						$el.selectWoo( 'destroy' );
					} catch ( err ) {
						// Ignore if already destroyed.
					}
					$el.removeClass( 'enhanced' );
				} );
		},

		// Init selectWoo on inline quick edit category/tag multiselects (same args as product form).
		initInlineQuickEditSelectWoo: function ( $scope ) {
			if ( ! $scope || ! $scope.length ) {
				return;
			}
			if ( typeof $.fn.selectWoo !== 'function' ) {
				return;
			}
			$scope
				.find( '.storesuite-inline-quick-edit-select2' )
				.filter( ':not(.enhanced)' )
				.each( function () {
					var select2_args = {
						allowClear: $( this ).data( 'allow_clear' )
							? true
							: false,
						placeholder: $( this ).data( 'placeholder' ) || '',
						minimumResultsForSearch:
							$( this ).data( 'minimum_results_for_search' ) || 0,
						width: '100%',
					};

					$( this ).selectWoo( select2_args ).addClass( 'enhanced' );
				} );
		},
		toggleStockFields: function () {
			const product_type = $( 'select#post_type' ).val();
			const is_checked = $( '#_manage_stock' ).is( ':checked' );

			if ( is_checked && 'external' !== product_type ) {
				$( '.show_if_stock_management' ).slideDown( 'fast' );
			} else {
				$( '.show_if_stock_management' ).slideUp( 'fast' );
			}

			if ( 'simple' === product_type ) {
				is_checked
					? $( '._stock_status_field' ).slideUp( 'fast' )
					: $( '._stock_status_field' ).slideDown( 'fast' );
			}
		},
		validateGlobalUniqueIdOnKeyUp: function () {
			var global_unique_id = $( this ).val();

			if ( /[^0-9\-]/.test( global_unique_id ) ) {
				$( document.body ).triggerHandler( 'wc_add_error_tip', [
					$( this ),
					'i18n_global_unique_id_error',
				] );
			} else {
				$( document.body ).triggerHandler( 'wc_remove_error_tip', [
					$( this ),
					'i18n_global_unique_id_error',
				] );
			}
		},
		validateGlobalUniqueIdOnChange: function () {
			var global_unique_id = $( this ).val();
			$( this ).val(
				global_unique_id
					.replace( /[^0-9\-]/g, '' )
					.replace( /^-+|-+$/g, '' )
			);

			$( document.body ).triggerHandler( 'wc_remove_error_tip', [
				$( this ),
				'i18n_global_unique_id_error',
			] );
		},
		errorTips: function () {
			$( document.body )
				.on( 'wc_add_error_tip', function ( e, element, error_type ) {
					var offset = element.position();

					if (
						element.parent().find( '.wc_error_tip' ).length === 0
					) {
						element.after(
							'<div class="wc_error_tip ' +
								error_type +
								'">' +
								StoreSuite_Product[ error_type ] +
								'</div>'
						);
						element
							.parent()
							.find( '.wc_error_tip' )
							.css(
								'left',
								offset.left +
									element.width() -
									element.width() / 2 -
									$( '.wc_error_tip' ).width() / 2
							)
							.css( 'top', offset.top + element.height() )
							.fadeIn( '100' );
					}
				} )

				.on(
					'wc_remove_error_tip',
					function ( e, element, error_type ) {
						element
							.parent()
							.find( '.wc_error_tip.' + error_type )
							.fadeOut( '100', function () {
								$( this ).remove();
							} );
					}
				);
		},
		salePriceDatesPicker: function () {
			var self = this;
			$( '.sale_price_dates_fields' ).each( function () {
				$( this )
					.find( 'input' )
					.datepicker( {
						defaultDate: '',
						dateFormat: 'yy-mm-dd',
						numberOfMonths: 1,
						showButtonPanel: true,
						onSelect: function () {
							self.datePickerSelect( $( this ) );
						},
					} );
				$( this )
					.find( 'input' )
					.each( function () {
						self.datePickerSelect( $( this ) );
					} );
			} );
		},
		datePickerSelect: function ( datepicker ) {
			var option = $( datepicker ).next().is( '.hasDatepicker' )
					? 'minDate'
					: 'maxDate',
				otherDateField =
					'minDate' === option
						? $( datepicker ).next()
						: $( datepicker ).prev(),
				date = $( datepicker ).datepicker( 'getDate' );

			$( otherDateField ).datepicker( 'option', option, date );
			$( datepicker ).trigger( 'change' );
		},
		initSalePriceSchedule: function () {
			$( '.sale_price_dates_fields' ).each( function () {
				var sale_schedule_set = false;

				$( this )
					.find( 'input' )
					.each( function () {
						if ( '' !== $( this ).val() ) {
							sale_schedule_set = true;
						}
					} );

				if ( sale_schedule_set ) {
					$( '.sale_schedule' ).hide();
					$( '.cancel_sale_schedule' ).show();
					$( '.sale_price_dates_fields' ).slideDown();
				} else {
					$( '.sale_schedule' ).show();
					$( '.cancel_sale_schedule' ).hide();
					$( '.sale_price_dates_fields' ).slideUp();
				}
			} );
		},
		openSaleSchedule: function () {
			$( this ).hide();
			$( '.cancel_sale_schedule' ).show();
			$( '.sale_price_dates_fields' ).slideDown();

			return false;
		},
		cancelSaleSchedule: function () {
			$( this ).hide();
			$( '.sale_schedule' ).show();
			$( '.sale_price_dates_fields' ).slideUp();
			$( '.sale_price_dates_fields' ).find( 'input' ).val( '' );

			return false;
		},

		/**
		 * Handle product delete (move to trash) via AJAX.
		 */
		handleProductDelete: function () {
			var self = this;

			$( document ).on(
				'click',
				'.storesuite-delete-product',
				function ( e ) {
					e.preventDefault();

					var productId = $( this ).data( 'product-id' );

					if (
						! productId ||
						typeof Swal === 'undefined' ||
						typeof storeSuiteFormHandler === 'undefined'
					) {
						return;
					}

					var i18n = storeSuiteFormHandler.i18n || {};
					var confirmTitle =
						i18n.product_delete_confirm_title || i18n.are_you_sure;
					var confirmText = i18n.product_delete_warning;
					var confirmButton = i18n.yes_delete;
					var cancelButton = i18n.cancel_button;
					var deletingText = i18n.deleting;
					var successTitle = i18n.success_title;
					var errorTitle = i18n.error_title;

					Swal.fire( {
						title: confirmTitle,
						text: confirmText,
						icon: 'warning',
						showCancelButton: true,
						confirmButtonText: confirmButton,
						cancelButtonText: cancelButton,
					} ).then( function ( result ) {
						if ( ! result.isConfirmed ) {
							return;
						}

						Swal.fire( {
							title: deletingText,
							text: storeSuiteFormHandler.i18n.please_wait,
							allowOutsideClick: false,
							didOpen: function () {
								Swal.showLoading();
							},
						} );

						var formData = new FormData();
						formData.append( 'id', productId );
						formData.append(
							'action',
							'storesuite_delete_product'
						);
						formData.append(
							'storesuite_delete_product_nonce',
							storeSuiteFormHandler.storesuite_woo_delete_nonce_
						);

						$.ajax( {
							url: storeSuiteFormHandler.ajax_url,
							type: 'POST',
							data: formData,
							processData: false,
							contentType: false,
							success: function ( response ) {
								Swal.close();

								if ( response.success ) {
									Swal.fire( {
										icon: 'success',
										title: successTitle,
										text: response.data.message || '',
									} );
									$( '#product-row-' + productId ).fadeOut(
										300,
										function () {
											$( this ).remove();
										}
									);
								} else {
									Swal.fire( {
										icon: 'error',
										title: errorTitle,
										text:
											response.data && response.data.error
												? response.data.error
												: storeSuiteFormHandler.i18n
														.unexpected_error,
									} );
								}
							},
							error: function () {
								Swal.close();
								Swal.fire( {
									icon: 'error',
									title: errorTitle,
									text: storeSuiteFormHandler.i18n
										.unexpected_error,
								} );
							},
						} );
					} );
				}
			);
		},

		/**
		 * Products list: bulk Edit opens modal (a11y via StoreSuite.storeSuiteModal).
		 */
		initProductBulkEditModal: function () {
			var self = this;
			var modal = window.StoreSuite && window.StoreSuite.storeSuiteModal;
			var $modal = $( '#storesuite-product-bulk-edit-modal' );

			if ( ! modal || ! $modal.length ) {
				return;
			}

			modal.initOverlay( $modal, {
				fade: true,
				closeSelector:
					'.storesuite-product-bulk-modal-cancel, .storesuite-product-bulk-modal-close',
			} );

			$( document ).on(
				'submit',
				'#storesuite-product-bulk-actions',
				function ( e ) {
					if ( $( '#bulk-action-selector-products' ).val() !== 'edit' ) {
						return;
					}

					e.preventDefault();

					var ids = $( '#storesuite-product-bulk-actions' )
						.find( 'input[name="bulk_product_ids[]"]:checked' )
						.map( function () {
							return $( this ).val();
						} )
						.get();

					if ( ! ids.length ) {
						if ( typeof Swal === 'undefined' ) {
							return;
						}
						var bulk = StoreSuite_Product.bulk_edit || {};
						var i18n = StoreSuite_Product.i18n || {};
						Swal.fire( {
							icon: 'warning',
							title:
								bulk.select_products_title ||
								i18n.error_title,
							text:
								bulk.select_products_message ||
								i18n.unexpected_error,
							confirmButtonText:
								bulk.ok_button || i18n.ok_button,
						} );
						return;
					}

					self.openProductBulkModal( $modal, ids );
				}
			);
		},

		openProductBulkModal: function ( $modal, postIds ) {
			var modal = window.StoreSuite && window.StoreSuite.storeSuiteModal;
			if ( ! modal ) {
				return;
			}

			var $ids = $( '#storesuite-bulk-edit-post-ids' );
			var i;

			$ids.empty();

			for ( i = 0; i < postIds.length; i++ ) {
				$ids.append(
					$( '<input>', {
						type: 'hidden',
						name: 'post[]',
						value: postIds[ i ],
					} )
				);
			}

			modal.open( $modal );
		},

		/**
		 * Products list: per-row inline quick edit (hidden sibling tr).
		 */
		initProductInlineQuickEditTable: function () {
			var self = this;
			var qe =
				typeof StoreSuite_Product !== 'undefined' &&
				StoreSuite_Product.quick_edit
					? StoreSuite_Product.quick_edit
					: null;
			var $table = $( 'table.storesuite-inline-editable-table' );

			if ( ! $table.length || ! qe || ! qe.ajax_action || ! qe.nonce ) {
				return;
			}

			var current_tr = null;
			var current_tr_pos_y = 0;
			var edit_form = null;

			function scrollInlineEditIntoView() {
				$( 'html, body' ).scrollTop(
					Math.max( 0, current_tr_pos_y - 50 )
				);
			}

			$table.on(
				'click',
				'.storesuite-item-inline-edit',
				function ( e ) {
					e.preventDefault();
					var postId = $( this ).data( 'product-id' );
					if ( ! postId ) {
						return;
					}

					$table
						.find( 'tr.storesuite-product-list-inline-edit-form' )
						.each( function () {
							self.destroyInlineQuickEditSelectWoo(
								$( this ).find( 'fieldset' )
							);
						} )
						.addClass( 'storesuite-hide' );

					current_tr = $( this ).closest( 'tr' );
					current_tr_pos_y = current_tr.offset().top;
					current_tr.addClass( 'storesuite-hide' );

					edit_form = current_tr.next(
						'tr.storesuite-product-list-inline-edit-form'
					);
					if ( ! edit_form.length ) {
						current_tr.removeClass( 'storesuite-hide' );
						return;
					}

					edit_form.removeClass( 'storesuite-hide' );
					self.bindStoresuiteInlineQuickEditToggles( edit_form );
					self.initInlineQuickEditSelectWoo(
						edit_form.find( 'fieldset' )
					);
					scrollInlineEditIntoView();
				}
			);

			$table.on(
				'click',
				'.storesuite-inline-edit-cancel',
				function ( e ) {
					e.preventDefault();
					if ( ! current_tr || ! current_tr.length ) {
						return;
					}
					var $inline_form = current_tr.next(
						'tr.storesuite-product-list-inline-edit-form'
					);
					self.destroyInlineQuickEditSelectWoo(
						$inline_form.find( 'fieldset' )
					);
					current_tr.removeClass( 'storesuite-hide' );
					$inline_form.addClass( 'storesuite-hide' );
					scrollInlineEditIntoView();
				}
			);

			$table.on(
				'click',
				'.storesuite-inline-edit-update',
				function ( e ) {
					e.preventDefault();

					if ( ! edit_form || ! edit_form.length ) {
						return;
					}

					var $btn = $( this );
					var $wrap = $btn.closest(
						'.storesuite-inline-edit-update-wrap'
					);
					var $fieldset = $btn.closest( 'fieldset' );

					var data = {};
					edit_form.find( '[data-field-name]' ).each( function () {
						var $field = $( this );
						if ( $field.closest( '.storesuite-hide' ).length ) {
							return;
						}
						var key = $field.data( 'field-name' );
						if ( $field.attr( 'type' ) === 'checkbox' ) {
							if ( $field.is( ':checked' ) ) {
								data[ key ] = true;
							}
						} else {
							var val = $field.val();
							if ( $field.prop( 'multiple' ) ) {
								data[ key ] =
									val === null ? [] : val;
							} else {
								data[ key ] =
									val === null ? '' : val;
							}
						}
					} );

					$wrap.addClass( 'storesuite-inline-edit-loading' );
					$fieldset.prop( 'disabled', true );

					$.ajax( {
						url: storeSuiteFormHandler.ajax_url,
						method: 'POST',
						dataType: 'json',
						data: {
							action: qe.ajax_action,
							security: qe.nonce,
							data: data,
						},
					} )
						.done( function ( response ) {
							if (
								response.success &&
								response.data &&
								response.data.row
							) {
								scrollInlineEditIntoView();
								self.destroyInlineQuickEditSelectWoo(
									edit_form.find( 'fieldset' )
								);
								edit_form
									.addClass( 'storesuite-hide' )
									.prev()
									.replaceWith( response.data.row );
								current_tr = edit_form.prev();
							} else {
								var failMsg;
								if ( response.data ) {
									failMsg =
										response.data.message ||
										response.data.error;
									if (
										! failMsg &&
										typeof response.data === 'string'
									) {
										failMsg = response.data;
									}
								}
								self.showError( failMsg );
							}
						} )
						.fail( function ( xhr ) {
							var msg;
							if (
								xhr &&
								xhr.responseJSON &&
								xhr.responseJSON.data
							) {
								msg =
									xhr.responseJSON.data.message ||
									xhr.responseJSON.data.error;
								if (
									! msg &&
									typeof xhr.responseJSON.data === 'string'
								) {
									msg = xhr.responseJSON.data;
								}
							}
							self.showError( msg );
						} )
						.always( function () {
							$wrap.removeClass(
								'storesuite-inline-edit-loading'
							);
							$fieldset.prop( 'disabled', false );
						} );
				}
			);
		},

		bindStoresuiteInlineQuickEditToggles: function ( $form ) {
			$form
				.off( 'change.storesuiteInlineQe' )
				.on(
					'change.storesuiteInlineQe',
					'input[data-field-toggler]',
					function () {
						var name = $( this ).data( 'field-name' );
						var checked = $( this ).is( ':checked' );
						$form
							.find( '[data-field-toggle="' + name + '"]' )
							.each( function () {
								var $el = $( this );
								var showOn =
									$el.attr( 'data-field-show-on' ) ===
									'true';
								var match = checked === showOn;
								$el.toggleClass( 'storesuite-hide', ! match );
							} );
					}
				);

			$form.find( 'input[data-field-toggler]' ).trigger( 'change' );
		},
	};
	StoreFrontProduct.init();
} )( jQuery );
