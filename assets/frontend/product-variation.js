( function ( $ ) {
	/* ---------------------------------------------------------------
	 * Variations: load, display and paginate product variations.
	 * --------------------------------------------------------------- */
	var StoreSuiteVariations = {
		page: 1,

		init: function () {
			$( document ).on(
				'click',
				'.storesuite-toggle-variation',
				this.toggleVariation
			);
			$( document ).on(
				'click',
				'.storesuite-variation-pagination a[data-page]',
				this.onPaginationClick.bind( this )
			);
			$( document ).on(
				'click',
				'#storesuite-do-variation-action',
				this.onToolbarAction.bind( this )
			);
			$( document ).on(
				'click',
				'#storesuite-save-variations-btn',
				this.saveVariations.bind( this )
			);
			$( document ).on(
				'click',
				'.storesuite-remove-variation',
				this.onRemoveVariationClick.bind( this )
			);

			// Mark rows as needing update on any input change.
			$( document ).on(
				'change input',
				'#storesuite-variations-container :input',
				this.onVariationFieldChange
			);

			// Toggle shipping fields when virtual checkbox changes.
			$( document ).on(
				'change',
				'.variable_is_virtual',
				this.onVirtualChange
			);

			// Toggle downloadable fields when downloadable checkbox changes.
			$( document ).on(
				'change',
				'.variable_is_downloadable',
				this.onDownloadableChange
			);

			// Toggle stock qty field when manage stock checkbox changes.
			$( document ).on(
				'change',
				'.variable_manage_stock',
				this.onManageStockChange
			);

			// Variation image upload and remove.
			$( document ).on(
				'click',
				'.storesuite-variation-image-upload img, .storesuite-variation-image-actions',
				this.onImageUploadClick
			);
			$( document ).on(
				'click',
				'.storesuite-remove-variation-image',
				this.onRemoveImageClick
			);

			// Scroll to the Pricing card default Cost of goods field.
			$( document ).on(
				'click',
				'.storesuite-cogs-default-link',
				function ( e ) {
					e.preventDefault();
					var $target = $( '#_cogs_value' );
					if ( ! $target.length ) {
						return;
					}
					$( 'html, body' ).animate(
						{
							scrollTop: $target.offset().top - 100,
						},
						400,
						function () {
							$target.trigger( 'focus' );
						}
					);
				}
			);

			// Per-variation sale price schedule.
			$( document ).on(
				'click',
				'.storesuite-variation-sale-schedule',
				this.openSaleSchedule
			);
			$( document ).on(
				'click',
				'.storesuite-variation-cancel-schedule',
				this.cancelSaleSchedule
			);

			// Default attributes save.
			$( document ).on(
				'click',
				'#storesuite-save-default-attrs-btn',
				this.saveDefaultAttributes.bind( this )
			);

			// Toggle the Default Form Values section.
			$( document ).on(
				'click',
				'#storesuite-toggle-default-values',
				function ( e ) {
					e.preventDefault();
					$( this ).toggleClass( 'is-active' );
					$( '#storesuite-default-attributes' ).slideToggle( 200 );
				}
			);

			// Auto-load variations on edit page.
			if (
				StoreSuiteVariation.product_id > 0 &&
				$( '#post_type' ).val() === 'variable'
			) {
				this.reload();
			}

			// Reload when product type changes to variable.
			$( document ).on( 'change', '#post_type', function () {
				if (
					$( this ).val() === 'variable' &&
					StoreSuiteVariation.product_id > 0
				) {
					StoreSuiteVariations.reload();
				}
			} );
		},

		reload: function () {
			this.loadPage( this.page );
		},

		loadPage: function ( page ) {
			var self = this;
			var $container = $( '#storesuite-variations-container' );
			var $pagination = $( '.storesuite-variation-pagination' );

			if ( ! $container.length ) {
				return;
			}

			window.StoreSuite.storeSuiteLoader.block(
				$( '.my-storesuite-wrapper' )
			);

			$.ajax( {
				url: StoreSuiteVariation.ajax_url,
				type: 'POST',
				dataType: 'json',
				data: {
					action: 'storesuite_load_variations',
					security: StoreSuiteVariation.nonce,
					product_id: StoreSuiteVariation.product_id,
					page: page,
					per_page: StoreSuiteVariation.per_page,
				},
				success: function ( response ) {
					if ( response && response.success ) {
						var data = response.data;
						self.page = data.page;
						$container
							.attr( 'data-total', data.total )
							.attr( 'data-page', data.page );
						$container.html(
							data.html ||
								'<p class="storesuite-text-muted">' +
									StoreSuiteVariation.i18n.no_variations +
									'</p>'
						);
						self.buildPagination(
							data.total,
							StoreSuiteVariation.per_page,
							data.page,
							data.total_pages
						);
						self.initSortable();
						self.initSaleSchedules();
					}
				},
				complete: function () {
					window.StoreSuite.storeSuiteLoader.unblock(
						$( '.my-storesuite-wrapper' )
					);
				},
			} );
		},

		buildPagination: function ( total, perPage, currentPage, totalPages ) {
			var $pagination = $( '.storesuite-variation-pagination' );
			$pagination.empty();

			if ( totalPages <= 1 ) {
				return;
			}

			var startItem = ( currentPage - 1 ) * perPage + 1;
			var endItem = Math.min( total, currentPage * perPage );

			var items = '';

			if ( currentPage > 1 ) {
				items +=
					'<li><a href="#" data-page="' +
					( currentPage - 1 ) +
					'" class="page-numbers prev">&larr;</a></li>';
			}

			for ( var i = 1; i <= totalPages; i++ ) {
				if ( i === currentPage ) {
					items +=
						'<li><span class="page-numbers current">' +
						i +
						'</span></li>';
				} else {
					items +=
						'<li><a href="#" data-page="' +
						i +
						'" class="page-numbers">' +
						i +
						'</a></li>';
				}
			}

			if ( currentPage < totalPages ) {
				items +=
					'<li><a href="#" data-page="' +
					( currentPage + 1 ) +
					'" class="page-numbers next">&rarr;</a></li>';
			}

			var showingTemplate =
				StoreSuiteVariation.i18n.showing ||
				'Showing %1$s to %2$s of %3$s';
			var resultText = showingTemplate
				.replace( '%1$s', startItem )
				.replace( '%2$s', endItem )
				.replace( '%3$s', total );

			var html =
				'<div class="storesuite-pagination-wrap">' +
				'<div class="storesuite-result-text">' +
				resultText +
				'</div>' +
				'<ul class="storesuite-pagination">' +
				items +
				'</ul>' +
				'</div>';

			$pagination.html( html );
		},

		onToolbarAction: function ( e ) {
			e.preventDefault();
			var action = $( '#storesuite-variation-actions' ).val();

			switch ( action ) {
				case 'add_variation':
					this.addVariation();
					break;
				case 'generate_variations':
					this.generateAll();
					break;
				case '':
					break;
				default:
					this.bulkAction( action );
					break;
			}
		},

		generateAll: function () {
			var self = this;

			Swal.fire( {
				title:
					StoreSuiteVariation.i18n.confirm_generate ||
					'Generate variations?',
				text:
					StoreSuiteVariation.i18n.confirm_generate_text ||
					'This will create variations for all attribute combinations.',
				icon: 'question',
				showCancelButton: true,
				confirmButtonText: StoreSuiteVariation.i18n.ok_button || 'OK',
			} ).then( function ( result ) {
				if ( ! result.isConfirmed ) {
					return;
				}

				window.StoreSuite.storeSuiteLoader.block(
					$( '.my-storesuite-wrapper' )
				);

				$.ajax( {
					url: StoreSuiteVariation.ajax_url,
					type: 'POST',
					dataType: 'json',
					data: {
						action: 'storesuite_generate_variations',
						security: StoreSuiteVariation.nonce,
						product_id: StoreSuiteVariation.product_id,
					},
					success: function ( response ) {
						if ( response && response.success ) {
							Swal.fire( {
								icon: 'success',
								text: response.data.message,
								timer: 3000,
								showConfirmButton: false,
							} );
							self.page = 1;
							self.reload();
						} else {
							Swal.fire( {
								icon: 'error',
								text:
									response.data.message ||
									'Error generating variations.',
							} );
						}
					},
					error: function () {
						Swal.fire( {
							icon: 'error',
							text: 'An unexpected error occurred.',
						} );
					},
					complete: function () {
						window.StoreSuite.storeSuiteLoader.unblock(
							$( '.my-storesuite-wrapper' )
						);
					},
				} );
			} );
		},

		// Bulk actions that prompt for a single value.
		valuePromptActions: [
			'variable_regular_price',
			'variable_sale_price',
			'variable_stock',
			'variable_low_stock_amount',
			'variable_weight',
			'variable_length',
			'variable_width',
			'variable_height',
			'variable_download_limit',
			'variable_download_expiry',
		],

		// Bulk actions that prompt for a fixed amount or percentage.
		priceAdjustActions: [
			'variable_regular_price_increase',
			'variable_regular_price_decrease',
			'variable_sale_price_increase',
			'variable_sale_price_decrease',
		],

		/**
		 * Prompt user for bulk action value and execute.
		 */
		bulkAction: function ( action ) {
			var self = this;
			var i18n = StoreSuiteVariation.i18n;

			if ( 'delete_all' === action ) {
				Swal.fire( {
					title:
						i18n.confirm_delete_all ||
						'Delete all variations? This cannot be undone.',
					icon: 'warning',
					showCancelButton: true,
					confirmButtonColor: '#d63638',
					confirmButtonText: i18n.ok_button || 'OK',
				} ).then( function ( result ) {
					if ( result.isConfirmed ) {
						self.executeBulkAction( action, { allowed: 'true' } );
					}
				} );
				return;
			}

			if ( 'variable_unset_cogs_value' === action ) {
				Swal.fire( {
					title:
						i18n.confirm_remove_cogs ||
						'Remove the custom cost from every variation?',
					icon: 'warning',
					showCancelButton: true,
					confirmButtonText: i18n.ok_button || 'OK',
				} ).then( function ( result ) {
					if ( result.isConfirmed ) {
						self.executeBulkAction( action, {} );
					}
				} );
				return;
			}

			if ( 'variable_sale_schedule' === action ) {
				self.promptSaleSchedule();
				return;
			}

			if ( self.priceAdjustActions.indexOf( action ) !== -1 ) {
				Swal.fire( {
					title:
						i18n.enter_value_fixed_or_percent ||
						'Enter a value (fixed or %)',
					input: 'text',
					showCancelButton: true,
					confirmButtonText: i18n.ok_button || 'OK',
					inputValidator: function ( val ) {
						if ( ! val ) {
							return i18n.enter_a_value || 'Enter a value.';
						}
					},
				} ).then( function ( result ) {
					if ( result.isConfirmed ) {
						self.executeBulkAction( action, {
							value: result.value,
						} );
					}
				} );
				return;
			}

			if ( self.valuePromptActions.indexOf( action ) !== -1 ) {
				Swal.fire( {
					title: i18n.enter_a_value || 'Enter a value',
					input: 'text',
					showCancelButton: true,
					confirmButtonText: i18n.ok_button || 'OK',
				} ).then( function ( result ) {
					if ( result.isConfirmed ) {
						self.executeBulkAction( action, {
							value: result.value,
						} );
					}
				} );
				return;
			}

			// Toggles and stock-status changes run immediately.
			self.executeBulkAction( action, {} );
		},

		/**
		 * Prompt for sale start/end dates, then execute the schedule action.
		 */
		promptSaleSchedule: function () {
			var self = this;
			var i18n = StoreSuiteVariation.i18n;

			Swal.fire( {
				title:
					i18n.sale_start_date ||
					'Sale start date (leave blank to skip)',
				input: 'date',
				showCancelButton: true,
				confirmButtonText: i18n.next_button || 'Next',
			} ).then( function ( fromResult ) {
				if ( ! fromResult.isConfirmed ) {
					return;
				}

				var dateFrom = fromResult.value || '';

				Swal.fire( {
					title:
						i18n.sale_end_date ||
						'Sale end date (leave blank to skip)',
					input: 'date',
					showCancelButton: true,
					confirmButtonText: i18n.ok_button || 'OK',
				} ).then( function ( toResult ) {
					if ( ! toResult.isConfirmed ) {
						return;
					}

					var dateTo = toResult.value || '';

					if ( '' === dateFrom && '' === dateTo ) {
						return;
					}

					self.executeBulkAction( 'variable_sale_schedule', {
						date_from: '' === dateFrom ? 'false' : dateFrom,
						date_to: '' === dateTo ? 'false' : dateTo,
					} );
				} );
			} );
		},

		/**
		 * Send bulk action AJAX request and reload variations.
		 */
		executeBulkAction: function ( action, data ) {
			var self = this;

			window.StoreSuite.storeSuiteLoader.block(
				$( '.my-storesuite-wrapper' )
			);

			$.ajax( {
				url: StoreSuiteVariation.ajax_url,
				type: 'POST',
				dataType: 'json',
				data: {
					action: 'storesuite_bulk_edit_variations',
					security: StoreSuiteVariation.bulk_edit_nonce,
					product_id: StoreSuiteVariation.product_id,
					bulk_action: action,
					data: data || {},
				},
				success: function ( response ) {
					if ( response && response.success ) {
						Swal.fire( {
							icon: 'success',
							text: response.data.message,
							timer: 2000,
							showConfirmButton: false,
						} );
						self.page = 1;
						self.reload();
					} else {
						Swal.fire( {
							icon: 'error',
							text:
								( response.data && response.data.message ) ||
								'Error performing bulk action.',
						} );
					}
				},
				error: function () {
					Swal.fire( {
						icon: 'error',
						text: 'An unexpected error occurred.',
					} );
				},
				complete: function () {
					window.StoreSuite.storeSuiteLoader.unblock(
						$( '.my-storesuite-wrapper' )
					);
				},
			} );
		},

		/**
		 * Add a single blank variation via AJAX.
		 */
		addVariation: function () {
			var self = this;

			window.StoreSuite.storeSuiteLoader.block(
				$( '.my-storesuite-wrapper' )
			);

			$.ajax( {
				url: StoreSuiteVariation.ajax_url,
				type: 'POST',
				dataType: 'json',
				data: {
					action: 'storesuite_add_variation',
					security: StoreSuiteVariation.add_variation_nonce,
					product_id: StoreSuiteVariation.product_id,
				},
				success: function ( response ) {
					if ( response && response.success ) {
						var $container = $(
							'#storesuite-variations-container'
						);
						// Drop the empty-state placeholder before adding the row.
						$container
							.find( '.storesuite-variation-empty' )
							.remove();
						$container.prepend( response.data.html );
						self.initSaleSchedules();
						Swal.fire( {
							icon: 'success',
							text: response.data.message,
							timer: 1500,
							showConfirmButton: false,
						} );
					} else {
						Swal.fire( {
							icon: 'error',
							text:
								( response.data && response.data.message ) ||
								'Error adding variation.',
						} );
					}
				},
				error: function () {
					Swal.fire( {
						icon: 'error',
						text: 'An unexpected error occurred.',
					} );
				},
				complete: function () {
					window.StoreSuite.storeSuiteLoader.unblock(
						$( '.my-storesuite-wrapper' )
					);
				},
			} );
		},

		/**
		 * Handle remove variation button click with SweetAlert2 confirmation.
		 */
		onRemoveVariationClick: function ( e ) {
			e.preventDefault();
			var self = this;
			var $btn = $( e.currentTarget );
			var variationId = $btn.data( 'variation-id' );
			var $row = $btn.closest( '.storesuite-variation-row' );

			Swal.fire( {
				title:
					StoreSuiteVariation.i18n.confirm_remove ||
					'Remove this variation?',
				icon: 'warning',
				showCancelButton: true,
				confirmButtonText: StoreSuiteVariation.i18n.ok_button || 'OK',
			} ).then( function ( result ) {
				if ( ! result.isConfirmed ) {
					return;
				}

				self.removeVariation( variationId, $row );
			} );
		},

		/**
		 * Delete a variation via AJAX and remove the row from the DOM.
		 */
		removeVariation: function ( variationId, $row ) {
			window.StoreSuite.storeSuiteLoader.block(
				$( '.my-storesuite-wrapper' )
			);

			var self = this;

			$.ajax( {
				url: StoreSuiteVariation.ajax_url,
				type: 'POST',
				dataType: 'json',
				data: {
					action: 'storesuite_remove_variation',
					security: StoreSuiteVariation.remove_variation_nonce,
					variation_id: variationId,
				},
				success: function ( response ) {
					if ( response && response.success ) {
						$row.slideUp( 200, function () {
							$row.remove();
							// Reload to refresh pagination and totals.
							self.reload();
						} );
					} else {
						Swal.fire( {
							icon: 'error',
							text:
								( response.data && response.data.message ) ||
								'Error removing variation.',
						} );
					}
				},
				error: function () {
					Swal.fire( {
						icon: 'error',
						text: 'An unexpected error occurred.',
					} );
				},
				complete: function () {
					window.StoreSuite.storeSuiteLoader.unblock(
						$( '.my-storesuite-wrapper' )
					);
				},
			} );
		},

		/**
		 * Initialize jQuery UI Sortable on the variations container for drag reordering.
		 */
		initSortable: function () {
			var $container = $( '#storesuite-variations-container' );
			if ( ! $container.length || ! $.fn.sortable ) {
				return;
			}

			$container.sortable( {
				items: '.storesuite-variation-row',
				handle: '.storesuite-variation-header',
				cursor: 'move',
				placeholder: 'storesuite-sortable-placeholder',
				opacity: 0.65,
				stop: function () {
					// Update menu_order hidden inputs based on new DOM order.
					$container
						.find( '.storesuite-variation-row' )
						.each( function ( index ) {
							$( this )
								.find( 'input[name^="variable_menu_order"]' )
								.val( index );
							if (
								! $( this ).hasClass( 'variation-needs-update' )
							) {
								$( this ).addClass( 'variation-needs-update' );
							}
						} );
					$( '#storesuite-save-variations-btn' ).prop(
						'disabled',
						false
					);
					// Notify the product form dirty-state tracker (sticky
					// "Unsaved Changes" bar) since .val() above is silent.
					$container
						.find( 'input[name^="variable_menu_order"]' )
						.first()
						.trigger( 'change' );
				},
			} );
		},

		/**
		 * Save default attributes via AJAX.
		 */
		saveDefaultAttributes: function () {
			window.StoreSuite.storeSuiteLoader.block(
				$( '.my-storesuite-wrapper' )
			);

			var data = {
				action: 'storesuite_save_default_attributes',
				security: StoreSuiteVariation.default_attributes_nonce,
				product_id: StoreSuiteVariation.product_id,
			};

			// Collect all default attribute selects.
			$( '.storesuite-default-attribute-select' ).each( function () {
				var name = $( this ).attr( 'name' );
				if ( name ) {
					data[ name ] = $( this ).val();
				}
			} );

			$.ajax( {
				url: StoreSuiteVariation.ajax_url,
				type: 'POST',
				dataType: 'json',
				data: data,
				success: function ( response ) {
					if ( response && response.success ) {
						Swal.fire( {
							icon: 'success',
							text: response.data.message,
							timer: 1500,
							showConfirmButton: false,
						} );
					} else {
						Swal.fire( {
							icon: 'error',
							text:
								( response.data && response.data.message ) ||
								'Error saving defaults.',
						} );
					}
				},
				error: function () {
					Swal.fire( {
						icon: 'error',
						text: 'An unexpected error occurred.',
					} );
				},
				complete: function () {
					window.StoreSuite.storeSuiteLoader.unblock(
						$( '.my-storesuite-wrapper' )
					);
				},
			} );
		},

		/**
		 * Mark a variation row as needing an update and enable the save button.
		 */
		onVariationFieldChange: function () {
			var $row = $( this ).closest( '.storesuite-variation-row' );
			if ( ! $row.hasClass( 'variation-needs-update' ) ) {
				$row.addClass( 'variation-needs-update' );
			}
			$( '#storesuite-save-variations-btn' ).prop( 'disabled', false );
		},

		/**
		 * Toggle shipping fields visibility when virtual checkbox changes.
		 */
		onVirtualChange: function () {
			var $row = $( this ).closest( '.storesuite-variation-row' );
			if ( $( this ).is( ':checked' ) ) {
				$row.find( '.hide_if_variation_virtual' ).hide();
			} else {
				$row.find( '.hide_if_variation_virtual' ).show();
			}
		},

		/**
		 * Initialise the sale price schedule UI for each variation row:
		 * datepickers, and show/hide based on whether dates are set.
		 */
		initSaleSchedules: function () {
			var self = this;
			$( '.storesuite-variation-sale-dates' ).each( function () {
				var $dates = $( this );

				$dates.find( 'input' ).datepicker( {
					defaultDate: '',
					dateFormat: 'yy-mm-dd',
					numberOfMonths: 1,
					showButtonPanel: true,
					onSelect: function () {
						self.datePickerSelect( $( this ) );
					},
				} );

				var $row = $dates.closest( '.storesuite-variation-row' );
				var has_dates = false;
				$dates.find( 'input' ).each( function () {
					if ( '' !== $( this ).val() ) {
						has_dates = true;
					}
				} );

				if ( has_dates ) {
					$row.find( '.storesuite-variation-sale-schedule' ).hide();
					$row.find( '.storesuite-variation-cancel-schedule' ).show();
					$dates.show();
				} else {
					$row.find( '.storesuite-variation-sale-schedule' ).show();
					$row.find( '.storesuite-variation-cancel-schedule' ).hide();
					$dates.hide();
				}
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

		openSaleSchedule: function ( e ) {
			e.preventDefault();
			var $row = $( this ).closest( '.storesuite-variation-row' );
			$( this ).hide();
			$row.find( '.storesuite-variation-cancel-schedule' ).show();
			$row.find( '.storesuite-variation-sale-dates' ).slideDown();
		},

		cancelSaleSchedule: function ( e ) {
			e.preventDefault();
			var $row = $( this ).closest( '.storesuite-variation-row' );
			$( this ).hide();
			$row.find( '.storesuite-variation-sale-schedule' ).show();
			var $dates = $row.find( '.storesuite-variation-sale-dates' );
			$dates.slideUp();
			$dates.find( 'input' ).val( '' ).trigger( 'change' );
		},

		/**
		 * Toggle downloadable fields visibility when downloadable checkbox changes.
		 */
		onDownloadableChange: function () {
			var $row = $( this ).closest( '.storesuite-variation-row' );
			if ( $( this ).is( ':checked' ) ) {
				$row.find( '.show_if_variation_downloadable' ).show();
			} else {
				$row.find( '.show_if_variation_downloadable' ).hide();
			}
		},

		/**
		 * Toggle stock qty field visibility when manage stock checkbox changes.
		 */
		onManageStockChange: function () {
			var $row = $( this ).closest( '.storesuite-variation-row' );
			if ( $( this ).is( ':checked' ) ) {
				$row.find( '.show_if_variation_manage_stock' ).show();
			} else {
				$row.find( '.show_if_variation_manage_stock' ).hide();
			}
		},

		/**
		 * Open WordPress media library to choose a variation image.
		 */
		onImageUploadClick: function ( e ) {
			e.preventDefault();
			var $upload = $( this ).closest(
				'.storesuite-variation-image-upload'
			);
			var $row = $upload.closest( '.storesuite-variation-row' );
			var i18n = StoreSuiteVariation.i18n;

			var frame = wp.media( {
				title: i18n.choose_variation_image || 'Choose variation image',
				button: { text: i18n.set_image || 'Set image' },
				multiple: false,
				library: { type: 'image' },
			} );

			frame.on( 'select', function () {
				var attachment = frame
					.state()
					.get( 'selection' )
					.first()
					.toJSON();
				var thumbUrl =
					attachment.sizes && attachment.sizes.thumbnail
						? attachment.sizes.thumbnail.url
						: attachment.url;

				$upload.find( 'img' ).attr( 'src', thumbUrl );
				$upload
					.find( 'input[type="hidden"]' )
					.val( attachment.id )
					.trigger( 'change' );
				$upload.addClass( 'has-variation-image' );

				// Mark row as modified.
				if ( ! $row.hasClass( 'variation-needs-update' ) ) {
					$row.addClass( 'variation-needs-update' );
				}
				$( '#storesuite-save-variations-btn' ).prop(
					'disabled',
					false
				);
			} );

			frame.open();
		},

		/**
		 * Remove the variation image and reset to placeholder.
		 */
		onRemoveImageClick: function ( e ) {
			e.preventDefault();
			// Stop the click from bubbling to the .storesuite-variation-image-actions
			// upload handler, which would otherwise open the media frame.
			e.stopPropagation();
			var $upload = $( this ).closest(
				'.storesuite-variation-image-upload'
			);
			var $row = $upload.closest( '.storesuite-variation-row' );

			$upload
				.find( 'img' )
				.attr( 'src', StoreSuiteVariation.placeholder_img );
			$upload.find( 'input[type="hidden"]' ).val( 0 ).trigger( 'change' );
			$upload.removeClass( 'has-variation-image' );

			// Mark row as modified.
			if ( ! $row.hasClass( 'variation-needs-update' ) ) {
				$row.addClass( 'variation-needs-update' );
			}
			$( '#storesuite-save-variations-btn' ).prop( 'disabled', false );
		},

		/**
		 * Save only the variation rows that have been modified.
		 */
		saveVariations: function ( opts ) {
			var self = this;
			// Silent mode (triggered by the product form submit) skips this
			// module's own loader and success dialog so the product save owns
			// the UX; errors are still surfaced.
			var silent = !! ( opts && opts.silent );
			var $dirty = $(
				'#storesuite-variations-container .variation-needs-update'
			);

			if ( ! $dirty.length ) {
				return;
			}

			if ( ! silent ) {
				window.StoreSuite.storeSuiteLoader.block(
					$( '.my-storesuite-wrapper' )
				);
			}

			var formData = new FormData();
			formData.append( 'action', 'storesuite_save_variations' );
			formData.append(
				'security',
				StoreSuiteVariation.save_variations_nonce
			);
			formData.append( 'product_id', StoreSuiteVariation.product_id );

			// Collect inputs only from dirty rows.
			$dirty.each( function () {
				$( this )
					.find( ':input' )
					.each( function () {
						var $input = $( this );
						var name = $input.attr( 'name' );

						if ( ! name ) {
							return;
						}

						if ( $input.is( ':checkbox' ) ) {
							if ( $input.is( ':checked' ) ) {
								formData.append( name, $input.val() );
							}
						} else if ( $input.is( 'select[multiple]' ) ) {
							var values = $input.val() || [];
							values.forEach( function ( v ) {
								formData.append( name, v );
							} );
						} else {
							formData.append( name, $input.val() );
						}
					} );
			} );

			$.ajax( {
				url: StoreSuiteVariation.ajax_url,
				type: 'POST',
				data: formData,
				processData: false,
				contentType: false,
				success: function ( response ) {
					if ( response && response.success ) {
						if ( ! silent ) {
							Swal.fire( {
								icon: 'success',
								text: response.data.message,
								timer: 2000,
								showConfirmButton: false,
							} );
						}
						$dirty.removeClass( 'variation-needs-update' );
						$( '#storesuite-save-variations-btn' ).prop(
							'disabled',
							true
						);
					} else {
						Swal.fire( {
							icon: 'error',
							text:
								( response.data && response.data.message ) ||
								'Error saving variations.',
						} );
					}
				},
				error: function () {
					Swal.fire( {
						icon: 'error',
						text: 'An unexpected error occurred.',
					} );
				},
				complete: function () {
					if ( ! silent ) {
						window.StoreSuite.storeSuiteLoader.unblock(
							$( '.my-storesuite-wrapper' )
						);
					}
				},
			} );
		},

		toggleVariation: function ( e ) {
			e.preventDefault();
			$( this )
				.closest( '.storesuite-variation-row' )
				.find( '.storesuite-variation-body' )
				.slideToggle( 200 );
			$( this )
				.closest( '.storesuite-variation-row' )
				.find( '.storesuite-variation-body' )
				.toggleClass( 'is-open' );
		},

		onPaginationClick: function ( e ) {
			e.preventDefault();
			var page = $( e.currentTarget ).data( 'page' );
			if ( page ) {
				this.loadPage( page );
			}
		},
	};

	/* ---------------------------------------------------------------
	 * Attributes: add, save, remove product attributes.
	 * --------------------------------------------------------------- */
	var StoreSuiteAttributes = {
		index: 0, // track attribute row count for naming

		init: function () {
			this.index = $(
				'#storesuite-attributes-list .storesuite-attribute-row'
			).length;

			this.toggleHeader();
			this.initSortable();

			$( document ).on(
				'click',
				'#storesuite-add-attribute-btn',
				this.addAttribute.bind( this )
			);
			$( document ).on(
				'click',
				'#storesuite-save-attributes-btn',
				this.saveAttributes.bind( this )
			);
			$( document ).on(
				'click',
				'.storesuite-edit-attribute',
				this.toggleEditAttribute
			);
			$( document ).on(
				'click',
				'.storesuite-remove-attribute',
				this.removeAttribute
			);
			$( document ).on(
				'click',
				'.storesuite-toggle-attribute',
				this.toggleAttribute
			);
			$( document ).on(
				'click',
				'.storesuite-select-all-terms',
				this.selectAllTerms
			);
			$( document ).on(
				'click',
				'.storesuite-select-no-terms',
				this.selectNoTerms
			);
			$( document ).on(
				'click',
				'.storesuite-add-attribute-term',
				this.addAttributeTerm
			);
			$( document ).on(
				'change',
				'.storesuite-attribute-values',
				this.updateTermBadges
			);
		},

		// Show the attributes table only when at least one attribute row exists.
		toggleHeader: function () {
			var hasRows =
				$( '#storesuite-attributes-list .storesuite-attribute-row' )
					.length > 0;
			$( '#storesuite-attributes-table' ).toggle( hasRows );
		},

		/**
		 * Initialize jQuery UI Sortable on the attributes table for drag reordering.
		 * Renumbers the attribute_position hidden inputs on drop so the new order
		 * is persisted on the next "Save Attributes".
		 */
		initSortable: function () {
			var $list = $( '#storesuite-attributes-list' );
			if ( ! $list.length || ! $.fn.sortable ) {
				return;
			}

			$list.sortable( {
				items: 'tr.storesuite-attribute-row',
				handle: '.storesuite-attribute-sort-handle',
				cursor: 'move',
				placeholder: 'storesuite-attribute-sortable-placeholder',
				forcePlaceholderSize: true,
				// Preserve cell widths while dragging a table row.
				helper: function ( event, ui ) {
					ui.children().each( function () {
						$( this ).width( $( this ).width() );
					} );
					return ui;
				},
				stop: function () {
					$list
						.find( 'tr.storesuite-attribute-row' )
						.each( function ( index ) {
							$( this )
								.find( 'input.attribute_position' )
								.val( index );
						} );
					// Notify the product form dirty-state tracker (sticky
					// "Unsaved Changes" bar) since .val() above is silent.
					$list
						.find( 'input.attribute_position' )
						.first()
						.trigger( 'change' );
				},
			} );
		},

		updateTermBadges: function () {
			var $select = $( this );
			var $display = $select
				.closest( '.storesuite-attribute-row' )
				.find( '.storesuite-attribute-terms-display' );
			if ( ! $display.length ) {
				return;
			}
			var html = '';
			$select.find( 'option:selected' ).each( function () {
				var text = $.trim( $( this ).text() );
				html +=
					'<span class="storesuite-term-badge">' +
					$( '<div>' ).text( text ).html() +
					'</span>';
			} );
			$display.html( html );
		},

		addAttribute: function () {
			var taxonomy = $( '#storesuite-add-attribute-select' ).val();

			// Placeholder selected — nothing to add.
			if ( ! taxonomy ) {
				return;
			}

			// A custom (non-taxonomy) attribute is added with an empty taxonomy.
			if ( '__custom__' === taxonomy ) {
				taxonomy = '';
			}

			this.appendAttributeRow( taxonomy );
		},

		/**
		 * Render a new attribute row (custom or for a given taxonomy) via AJAX.
		 */
		appendAttributeRow: function ( taxonomy ) {
			var index = this.index++;

			window.StoreSuite.storeSuiteLoader.block(
				$( '.my-storesuite-wrapper' )
			);

			$.ajax( {
				url: StoreSuiteVariation.ajax_url,
				type: 'POST',
				dataType: 'json',
				data: {
					action: 'storesuite_add_attribute',
					security: StoreSuiteVariation.add_attribute_nonce,
					product_id: StoreSuiteVariation.product_id || 0,
					product_type: $( '#post_type' ).val(),
					taxonomy: taxonomy,
					i: index,
				},
				success: function ( response ) {
					if (
						response &&
						response.success &&
						response.data &&
						response.data.html
					) {
						$( '#storesuite-attributes-list' ).append(
							response.data.html
						);

						StoreSuiteAttributes.toggleHeader();

						$( '.storesuite-select2' )
							.filter( ':not(.enhanced)' )
							.each( function () {
								$( this )
									.selectWoo( {
										allowClear: true,
										placeholder:
											$( this ).data( 'placeholder' ) ||
											'',
										tags: $( this ).data( 'tags' ) || false,
										tokenSeparators: [ '|' ],
										width: '100%',
									} )
									.addClass( 'enhanced' );
							} );

						if ( taxonomy ) {
							var $opt = $(
								'#storesuite-add-attribute-select option[value="' +
									taxonomy +
									'"]'
							);
							var $group = $opt.closest( 'optgroup' );
							$opt.remove();
							// Drop the "Global attributes" group once it is empty.
							if (
								$group.length &&
								$group.children( 'option' ).length === 0
							) {
								$group.remove();
							}
						}

						// Reset the selector back to the placeholder.
						$( '#storesuite-add-attribute-select' ).val( '' );
					}
				},
				complete: function () {
					window.StoreSuite.storeSuiteLoader.unblock(
						$( '.my-storesuite-wrapper' )
					);
				},
			} );
		},

		saveAttributes: function () {
			window.StoreSuite.storeSuiteLoader.block(
				$( '.my-storesuite-wrapper' )
			);

			var formData = new FormData();
			formData.append( 'action', 'storesuite_save_attributes' );
			formData.append(
				'security',
				StoreSuiteVariation.save_attributes_nonce
			);
			formData.append( 'product_id', StoreSuiteVariation.product_id );
			formData.append( 'product_type', $( '#post_type' ).val() );

			// Collect all attribute fields
			$( '#storesuite-attributes-list :input' ).each( function () {
				var $input = $( this );
				var name = $input.attr( 'name' );
				if ( ! name ) return;

				if ( $input.is( ':checkbox' ) ) {
					if ( $input.is( ':checked' ) ) {
						formData.append( name, $input.val() );
					}
				} else if ( $input.is( 'select[multiple]' ) ) {
					var values = $input.val() || [];
					values.forEach( function ( v ) {
						formData.append( name, v );
					} );
				} else {
					formData.append( name, $input.val() );
				}
			} );

			$.ajax( {
				url: StoreSuiteVariation.ajax_url,
				type: 'POST',
				data: formData,
				processData: false,
				contentType: false,
				success: function ( response ) {
					if ( response.success ) {
						Swal.fire( {
							icon: 'success',
							text: response.data.message,
							timer: 1500,
							showConfirmButton: false,
						} );
						// Reload variations section (attributes may have changed).
						StoreSuiteVariations.reload();
					} else {
						Swal.fire( {
							icon: 'error',
							text:
								( response.data && response.data.message ) ||
								response.data ||
								'Error saving attributes.',
						} );
					}
				},
				complete: function () {
					window.StoreSuite.storeSuiteLoader.unblock(
						$( '.my-storesuite-wrapper' )
					);
				},
			} );
		},

		removeAttribute: function ( e ) {
			e.preventDefault();
			$( this )
				.closest( '.storesuite-attribute-row' )
				.slideUp( 200, function () {
					$( this ).remove();
					StoreSuiteAttributes.toggleHeader();
				} );
		},

		toggleAttribute: function ( e ) {
			e.preventDefault();
			$( this )
				.closest( '.storesuite-attribute-row' )
				.find( '.storesuite-attribute-body' )
				.slideToggle( 200 );
		},

		toggleEditAttribute: function ( e ) {
			e.preventDefault();
			var $row = $( this ).closest( '.storesuite-attribute-row' );
			$row.toggleClass( 'is-editing' );
			// Nudge select2 to recalc width now that its container is visible.
			$row.find( 'select.storesuite-attribute-values' ).trigger(
				'change.select2'
			);
		},

		selectAllTerms: function ( e ) {
			e.preventDefault();
			var $select = $( this )
				.closest( 'td, .storesuite-form-group' )
				.find( 'select.storesuite-attribute-values' );
			$select.find( 'option' ).prop( 'selected', true );
			$select.trigger( 'change' );
		},

		selectNoTerms: function ( e ) {
			e.preventDefault();
			var $select = $( this )
				.closest( 'td, .storesuite-form-group' )
				.find( 'select.storesuite-attribute-values' );
			$select.find( 'option' ).prop( 'selected', false );
			$select.trigger( 'change' );
		},

		/**
		 * Create a new term for a global (taxonomy) attribute inline and select it.
		 */
		addAttributeTerm: function ( e ) {
			e.preventDefault();
			var i18n = StoreSuiteVariation.i18n;
			var $row = $( this ).closest( '.storesuite-attribute-row' );
			var taxonomy = $row.data( 'taxonomy' );
			var $select = $row.find( 'select.storesuite-attribute-values' );

			if ( ! taxonomy || ! $select.length ) {
				return;
			}

			Swal.fire( {
				title: i18n.add_term_title || 'Add new term',
				input: 'text',
				inputPlaceholder: i18n.add_term_placeholder || 'Term name',
				showCancelButton: true,
				confirmButtonText: i18n.add_button || 'Add',
				showLoaderOnConfirm: true,
				allowOutsideClick: function () {
					return ! Swal.isLoading();
				},
				preConfirm: function ( term ) {
					if ( ! term ) {
						Swal.showValidationMessage(
							i18n.term_required || 'Please enter a name.'
						);
						return false;
					}
					return $.ajax( {
						url: StoreSuiteVariation.ajax_url,
						type: 'POST',
						dataType: 'json',
						data: {
							action: 'storesuite_add_attribute_term',
							storesuite_add_attribute_term_nonce:
								StoreSuiteVariation.add_term_nonce,
							taxonomy: taxonomy,
							term_name: term,
						},
					} )
						.then( function ( response ) {
							if ( ! response || ! response.success ) {
								throw new Error(
									( response &&
										response.data &&
										( response.data.error ||
											response.data.message ) ) ||
										'Error creating term.'
								);
							}
							return response.data;
						} )
						.catch( function ( err ) {
							Swal.showValidationMessage(
								err.message || 'Error creating term.'
							);
						} );
				},
			} ).then( function ( result ) {
				if ( ! result.value || ! result.value.term_id ) {
					return;
				}
				var data = result.value;
				if (
					$select.find( 'option[value="' + data.term_id + '"]' )
						.length === 0
				) {
					$select.append(
						$( '<option>' ).val( data.term_id ).text( data.name )
					);
				}
				$select
					.find( 'option[value="' + data.term_id + '"]' )
					.prop( 'selected', true );
				$select.trigger( 'change' );
			} );
		},
	};
	// Expose so the main product form submit can also persist dirty variations.
	window.StoreSuiteVariations = StoreSuiteVariations;
	StoreSuiteAttributes.init();
	StoreSuiteVariations.init();
} )( jQuery );
