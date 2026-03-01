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
			this.handleProductDelete();
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
			$form.find( '.msf-field-error' ).remove();
			$form
				.find( '.msf-form-control' )
				.removeClass( 'msf-field-invalid' );

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
			$field.addClass( 'msf-field-invalid' );

			// Create error message element
			var $errorMsg = $(
				'<span class="msf-field-error">' + message + '</span>'
			);

			// Insert error message after the field
			$field.after( $errorMsg );

			// Remove error on field change
			$field.one( 'input change', function () {
				$( this ).removeClass( 'msf-field-invalid' );
				$( this ).siblings( '.msf-field-error' ).remove();
			} );
		},
		/**
		 * Handle Product Submit (Add/Edit)
		 */
		handleProductSubmit: function () {
			var self = this;

			$( document ).on( 'submit', '#msfc-add-product', function ( e ) {
				e.preventDefault();

				var $form = $( this );

				// Define required fields for validation
				var requiredFields = [
					{
						selector: '#product_title',
						message: storeSuiteFormHandler.i18n.product_title_required,
					},
					{
						selector: '#post_type',
						message: storeSuiteFormHandler.i18n.product_type_required,
					},
					{
						selector: '#post_status',
						message: storeSuiteFormHandler.i18n.product_status_required,
					},
				];

				// Validate required fields
				if ( ! self.validateRequiredFields( $form, requiredFields ) ) {
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
				window.StoreSuite.msfcLoader.block(
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
								title: storeSuiteFormHandler.i18n.success_title,
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
									.find( '.msf-select2' )
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
						window.StoreSuite.msfcLoader.unblock(
							$( '.my-storesuite-wrapper' )
						);
					},
				} );
			} );
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
			$( '.msf-select2' )
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

			$( document ).on( 'click', '.msfc-delete-product', function ( e ) {
				e.preventDefault();

				var productId = $( this ).data( 'product-id' );

				if ( ! productId || typeof Swal === 'undefined' || typeof storeSuiteFormHandler === 'undefined' ) {
					return;
				}

				var i18n = storeSuiteFormHandler.i18n || {};
				var confirmTitle = i18n.product_delete_confirm_title || i18n.are_you_sure || 'Are you sure?';
				var confirmText = i18n.product_delete_warning || 'Do you want to delete this product? It will be moved to trash.';
				var confirmButton = i18n.yes_delete || 'Yes, delete it!';
				var cancelButton = i18n.cancel_button || 'Cancel';
				var deletingText = i18n.deleting || 'Deleting...';
				var successTitle = i18n.success_title || 'Success!';
				var errorTitle = i18n.error_title || 'Error!';

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
						text: storeSuiteFormHandler.i18n.please_wait || 'Please wait...',
						allowOutsideClick: false,
						didOpen: function () {
							Swal.showLoading();
						},
					} );

					var formData = new FormData();
					formData.append( 'id', productId );
					formData.append( 'action', 'storesuite_delete_product' );
					formData.append( 'storesuite_delete_product_nonce', storeSuiteFormHandler.storesuite_woo_delete_nonce_ );

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
								$( '#product-row-' + productId ).fadeOut( 300, function () {
									$( this ).remove();
								} );
							} else {
								Swal.fire( {
									icon: 'error',
									title: errorTitle,
									text: response.data && response.data.error ? response.data.error : ( storeSuiteFormHandler.i18n.unexpected_error || 'An unexpected error occurred.' ),
								} );
							}
						},
						error: function () {
							Swal.close();
							Swal.fire( {
								icon: 'error',
								title: errorTitle,
								text: storeSuiteFormHandler.i18n.unexpected_error || 'An unexpected error occurred. Please try again.',
							} );
						},
					} );
				} );
			} );
		},
	};
	StoreFrontProduct.init();
} )( jQuery );
