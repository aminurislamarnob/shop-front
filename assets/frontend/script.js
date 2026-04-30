( function ( $ ) {
	var storeSuiteLoader = {
		block: function ( $container, text ) {
			text = text || 'Processing...';
			if (
				$container.find( '.storesuite-loader-overlay' ).length === 0
			) {
				$container.append(
					'<div class="storesuite-loader-overlay">' +
						'<span class="storesuite-loader-spinner"></span>' +
						'<span class="storesuite-loader-text">' +
						text +
						'</span>' +
						'</div>'
				);
			} else {
				$container.find( '.storesuite-loader-text' ).text( text );
			}

			setTimeout( function () {
				$container
					.find( '.storesuite-loader-overlay' )
					.addClass( 'active' );
			}, 10 );
		},
		unblock: function ( $container ) {
			$container
				.find( '.storesuite-loader-overlay' )
				.removeClass( 'active' );
		},
	};

	/**
	 * Overlay modal helpers: focus return, Escape, Tab cycle, backdrop and close buttons.
	 * Expects the root overlay to use the hidden attribute when closed.
	 * Pass initOverlay { fade: true } and matching CSS (see .storesuite-modal-fade) for opacity transitions.
	 */
	var storeSuiteModal = {
		fadeCloseFallbackMs: 350,

		focusableSelector:
			'a[href], button:not([disabled]), input:not([disabled]):not([type="hidden"]), select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"])',

		/**
		 * @param {jQuery} $overlay      Root overlay element.
		 * @param {string} dialogSelector Selector for the dialog region (default [role="dialog"]).
		 * @return {jQuery}
		 */
		getFocusables: function ( $overlay, dialogSelector ) {
			dialogSelector = dialogSelector || '[role="dialog"]';
			return $overlay
				.find( dialogSelector )
				.find( this.focusableSelector )
				.filter( ':visible' );
		},

		isOpen: function ( $overlay ) {
			return ! $overlay.prop( 'hidden' );
		},

		clearCloseTransition: function ( $overlay ) {
			var timer = $overlay.data( 'storesuite-modal-close-timer' );
			if ( timer ) {
				clearTimeout( timer );
				$overlay.removeData( 'storesuite-modal-close-timer' );
			}
			$overlay.off( 'transitionend.storesuiteModalClose' );
		},

		/**
		 * @param {jQuery} $overlay
		 * @param {object} [options]
		 * @param {string} [options.dialogSelector]
		 */
		open: function ( $overlay, options ) {
			options = options || {};
			var dialogSelector = options.dialogSelector || '[role="dialog"]';
			var self = this;
			var fade = $overlay.data( 'storesuite-modal-fade' );

			this.clearCloseTransition( $overlay );
			$overlay.removeData( 'storesuite-modal-closing' );

			$overlay.data(
				'storesuite-modal-prev-focus',
				document.activeElement
			);
			$overlay.prop( 'hidden', false ).attr( 'aria-hidden', 'false' );

			var focusFirst = function () {
				var $first = self
					.getFocusables( $overlay, dialogSelector )
					.first();
				if ( $first.length ) {
					$first.trigger( 'focus' );
					return;
				}
				var $dialog = $overlay.find( dialogSelector ).first();
				if ( $dialog.length ) {
					$dialog.trigger( 'focus' );
				}
			};

			if ( fade && $overlay[ 0 ] ) {
				window.requestAnimationFrame( function () {
					window.requestAnimationFrame( focusFirst );
				} );
			} else {
				focusFirst();
			}
		},

		close: function ( $overlay ) {
			var fade = $overlay.data( 'storesuite-modal-fade' );
			var prev = $overlay.data( 'storesuite-modal-prev-focus' );
			var self = this;

			var restoreAndCleanup = function () {
				$overlay.removeData( 'storesuite-modal-prev-focus' );
				$overlay.removeData( 'storesuite-modal-closing' );
				if ( prev && typeof prev.focus === 'function' ) {
					prev.focus();
				}
			};

			if ( ! fade ) {
				$overlay.prop( 'hidden', true ).attr( 'aria-hidden', 'true' );
				restoreAndCleanup();
				return;
			}

			if ( $overlay.prop( 'hidden' ) ) {
				this.clearCloseTransition( $overlay );
				restoreAndCleanup();
				return;
			}

			if ( $overlay.data( 'storesuite-modal-closing' ) ) {
				return;
			}
			$overlay.data( 'storesuite-modal-closing', true );

			var el = $overlay[ 0 ];
			var finished = false;
			var done = function () {
				if ( finished ) {
					return;
				}
				finished = true;
				self.clearCloseTransition( $overlay );
				restoreAndCleanup();
			};

			$overlay.attr( 'aria-hidden', 'true' );
			$overlay.prop( 'hidden', true );

			$overlay.one( 'transitionend.storesuiteModalClose', function ( e ) {
				if ( e.target !== el ) {
					return;
				}
				done();
			} );

			var timer = setTimeout( done, self.fadeCloseFallbackMs );
			$overlay.data( 'storesuite-modal-close-timer', timer );
		},

		/**
		 * One-time bind per overlay: Escape, Tab trap, backdrop click, close/cancel clicks.
		 *
		 * @param {jQuery} $overlay
		 * @param {object} [options]
		 * @param {string} [options.dialogSelector]
		 * @param {string} [options.closeSelector] Delegated selector for close controls.
		 * @param {boolean} [options.fade] Opacity fade (requires .storesuite-modal-fade CSS on overlay).
		 */
		initOverlay: function ( $overlay, options ) {
			options = options || {};
			var dialogSelector = options.dialogSelector || '[role="dialog"]';
			var closeSelector =
				options.closeSelector ||
				'.storesuite-modal-cancel, .storesuite-modal-close';

			if ( $overlay.data( 'storesuite-modal-a11y-bound' ) ) {
				return;
			}
			$overlay.data( 'storesuite-modal-a11y-bound', true );

			if ( options.fade ) {
				$overlay.data( 'storesuite-modal-fade', true );
				$overlay.addClass( 'storesuite-modal-fade' );
			}

			var self = this;

			$overlay.on( 'keydown.storesuiteModal', function ( e ) {
				if ( ! self.isOpen( $overlay ) ) {
					return;
				}

				if ( e.key === 'Escape' ) {
					e.preventDefault();
					self.close( $overlay );
					return;
				}

				if ( e.key !== 'Tab' ) {
					return;
				}

				var $focusable = self.getFocusables( $overlay, dialogSelector );
				if ( $focusable.length < 2 ) {
					return;
				}

				var first = $focusable[ 0 ];
				var last = $focusable[ $focusable.length - 1 ];

				if ( e.shiftKey && document.activeElement === first ) {
					e.preventDefault();
					last.focus();
				} else if ( ! e.shiftKey && document.activeElement === last ) {
					e.preventDefault();
					first.focus();
				}
			} );

			$overlay.on( 'click.storesuiteModal', function ( e ) {
				if ( e.target === $overlay[ 0 ] ) {
					self.close( $overlay );
				}
			} );

			$overlay.on(
				'click.storesuiteModal',
				closeSelector,
				function ( e ) {
					e.preventDefault();
					self.close( $overlay );
				}
			);
		},
	};

	// Expose for other StoreSuite scripts
	window.StoreSuite = window.StoreSuite || {};
	window.StoreSuite.storeSuiteLoader = storeSuiteLoader;
	window.StoreSuite.storeSuiteModal = storeSuiteModal;

	var StoreFrontCommonConfig = {
		init: function () {
			this.bindEvents();
		},
		bindEvents: function () {
			this.handleSidebarCollapseToggle();
			this.handleSubmenuToggle();
			this.handleDropdown(); //Handle
			this.closeDropdownOutside(); // Close dropdown when clicking outside
			this.uploadProductImage(); // Upload product image
			this.uploadProductGallaryImages(); // Upload product gallery images
			this.removeGalleryImage(); // Remove gallery image
			this.uploadCategoryImage(); // Upload category image
			this.uploadBrandImage(); // Upload brand image
			this.handleFilterOffcanvas(); // Handle filter off-canvas
			this.handleOrderFilterOffcanvas(); // Handle order filter off-canvas
			this.handleBulkActionCheckbox(); // Handle bulk action checkbox
			this.initDashboardDateRangePicker(); // Dashboard date range picker
		},
		/**
		 * Initialize dashboard date range picker with predefined ranges.
		 */
		initDashboardDateRangePicker: function () {
			var $range = $( '#storesuite_dashboard_range' );
			var $start = $( '#storesuite_dashboard_start' );
			var $end = $( '#storesuite_dashboard_end' );

			if (
				! $range.length ||
				! $.fn.daterangepicker ||
				! window.moment
			) {
				return;
			}

			var initialStart = $start.val();
			var initialEnd = $end.val();

			var start = initialStart
				? moment( initialStart, 'YYYY-MM-DD' )
				: moment().startOf( 'month' );
			var end = initialEnd
				? moment( initialEnd, 'YYYY-MM-DD' )
				: moment().endOf( 'month' );

			function storeSuiteUpdateDashboardRange( startDate, endDate ) {
				$start.val( startDate.format( 'YYYY-MM-DD' ) );
				$end.val( endDate.format( 'YYYY-MM-DD' ) );
				$range.val(
					startDate.format( 'YYYY-MM-DD' ) +
						' - ' +
						endDate.format( 'YYYY-MM-DD' )
				);
			}

			var labels = window.storeSuiteDateRangesI18n || {};
			var ranges = {};

			ranges[ labels.today || 'Today' ] = [ moment(), moment() ];
			ranges[ labels.yesterday || 'Yesterday' ] = [
				moment().subtract( 1, 'days' ),
				moment().subtract( 1, 'days' ),
			];
			ranges[ labels.last7 || 'Last 7 Days' ] = [
				moment().subtract( 6, 'days' ),
				moment(),
			];
			ranges[ labels.last30 || 'Last 30 Days' ] = [
				moment().subtract( 29, 'days' ),
				moment(),
			];
			ranges[ labels.this_month || 'This Month' ] = [
				moment().startOf( 'month' ),
				moment().endOf( 'month' ),
			];
			ranges[ labels.last_month || 'Last Month' ] = [
				moment().subtract( 1, 'month' ).startOf( 'month' ),
				moment().subtract( 1, 'month' ).endOf( 'month' ),
			];

			$range.daterangepicker(
				{
					startDate: start,
					endDate: end,
					autoUpdateInput: false,
					locale: {
						format: 'YYYY-MM-DD',
						separator: ' - ',
					},
					ranges: ranges,
				},
				function ( startDate, endDate ) {
					storeSuiteUpdateDashboardRange( startDate, endDate );
				}
			);

			// Initialize display/value on load.
			storeSuiteUpdateDashboardRange( start, end );
		},
		handleSubmenuToggle: function () {
			$( document ).on(
				'click',
				'ul.storesuite-dashboard-menu li.has-submenu > a',
				function ( e ) {
					// In collapsed sidebar submenus appear as CSS hover flyouts — don't intercept.
					if ( $( '.my-storesuite-container' ).hasClass( 'storesuite-sidebar-collapsed' ) ) {
						return;
					}
					e.preventDefault();
					var $li     = $( this ).closest( 'li' );
					var isOpen  = $li.hasClass( 'is-open' );
					$( 'ul.storesuite-dashboard-menu li.has-submenu.is-open' ).removeClass( 'is-open' );
					if ( ! isOpen ) {
						$li.addClass( 'is-open' );
					}
				}
			);
		},
		handleSidebarCollapseToggle: function () {
			var collapsedPreferenceStorageKey = 'storesuite_sidebar_collapsed';
			var minViewportWidthForCollapsedSidebar = 783;
			var $dashboardContainer = $( '.my-storesuite-container' );
			var $sidebarCollapseToggle = $( '.storesuite-sidebar-trigger' );

			if (
				! $dashboardContainer.length ||
				! $sidebarCollapseToggle.length
			) {
				return;
			}

			function isViewportWideEnoughForCollapsedSidebar() {
				return window.innerWidth >= minViewportWidthForCollapsedSidebar;
			}

			function applySidebarCollapsedState( isCollapsed ) {
				$dashboardContainer.toggleClass(
					'storesuite-sidebar-collapsed',
					isCollapsed
				);
				$sidebarCollapseToggle.attr(
					'aria-expanded',
					isCollapsed ? 'false' : 'true'
				);
			}

			function persistCollapsedPreference( isCollapsed ) {
				try {
					if ( isCollapsed ) {
						localStorage.setItem(
							collapsedPreferenceStorageKey,
							'1'
						);
					} else {
						localStorage.removeItem(
							collapsedPreferenceStorageKey
						);
					}
				} catch ( storageError ) {}
			}

			function readCollapsedPreferenceFromStorage() {
				try {
					return (
						localStorage.getItem(
							collapsedPreferenceStorageKey
						) === '1'
					);
				} catch ( storageError ) {
					return false;
				}
			}

			function syncSidebarCollapsedState() {
				if ( ! isViewportWideEnoughForCollapsedSidebar() ) {
					applySidebarCollapsedState( false );
					return;
				}
				applySidebarCollapsedState(
					readCollapsedPreferenceFromStorage()
				);
			}

			function handleSidebarToggleInteraction( event ) {
				if ( ! isViewportWideEnoughForCollapsedSidebar() ) {
					return;
				}
				event.preventDefault();
				var shouldBeCollapsed = ! $dashboardContainer.hasClass(
					'storesuite-sidebar-collapsed'
				);
				applySidebarCollapsedState( shouldBeCollapsed );
				persistCollapsedPreference( shouldBeCollapsed );
			}

			syncSidebarCollapsedState();
			$( window ).on( 'resize', syncSidebarCollapsedState );
			$sidebarCollapseToggle.on(
				'click',
				handleSidebarToggleInteraction
			);
		},
		handleBulkActionCheckbox: function () {
			$( '#cb-select-all-orders' ).on( 'click', function () {
				var isChecked = $( this ).prop( 'checked' );
				$( 'input[name="bulk_order_ids[]"]' ).prop(
					'checked',
					isChecked
				);
			} );

			$( '#cb-select-all-products' ).on( 'change', function () {
				var checked = $( this ).prop( 'checked' );
				$( '#storesuite-product-bulk-actions' )
					.find( 'input[name="bulk_product_ids[]"]' )
					.prop( 'checked', checked );
			} );
		},
		handleDropdown: function () {
			$( document ).on(
				'click',
				'.storesuite-dropdown-icon',
				function () {
					$( '.storesuite-dropdown-menu' ).hide();
					$( this )
						.closest( '.storesuite-dropdown' )
						.find( '.storesuite-dropdown-menu' )
						.toggle();
				}
			);
		},
		closeDropdownOutside: function () {
			$( document ).on( 'click', function ( event ) {
				if (
					! $( event.target ).closest( '.storesuite-dropdown' ).length
				) {
					$( '.storesuite-dropdown-menu' ).hide();
				}
			} );
		},
		uploadProductImage: function () {
			$( '#product-single-image' ).click( function ( event ) {
				event.preventDefault();

				var image_id = $( '#product_thumbnail_url' ).val();
				var targetContainer = $( this );

				if ( image_id && image_id.length > 0 ) {
					$( '#product_thumbnail_id' ).val( '' );
					$( '#product_thumbnail_url' ).val( '' );
					$( '#product_thumb_img' ).html( '' );
					$( targetContainer )
						.find( '.image-drop-text span' )
						.text( storeSuiteFrontScript.upload_image_text );
					$( targetContainer ).removeClass( 'image-drop-bg' );
				} else {
					// If the media frame already exists, reopen it.
					if ( frame ) {
						frame.open();
						return false;
					}

					// Create a new media frame
					var frame = wp.media( {
						title: storeSuiteFrontScript.upload_product_image,
						button: {
							text: storeSuiteFrontScript.insert_image,
						},
						multiple: false,
					} );

					frame.on( 'select', function () {
						var attachment = frame
							.state()
							.get( 'selection' )
							.first()
							.toJSON();

						// Send the attachment id to our hidden input
						$( '#product_thumbnail_id' ).val( attachment.id );

						// Send the attachment URL to our custom image input field.
						$( '#product_thumb_img' ).html(
							'<img src="' +
								attachment.sizes.thumbnail.url +
								'" alt="' +
								storeSuiteFrontScript.product_image +
								'"/>'
						);
						$( '#product_thumbnail_url' ).val(
							attachment.sizes.thumbnail.url
						);

						//add class to hide text normaly
						$( targetContainer ).addClass( 'image-drop-bg' );
						$( '#product-single-image .image-drop-text span' ).text(
							storeSuiteFrontScript.remove_image_text
						);
					} );

					frame.open();
				}
			} );
		},
		uploadProductGallaryImages: function () {
			$( '#product-gallery-images' ).click( function ( event ) {
				event.preventDefault();

				var targetContainer = $( this );
				// If the media frame already exists, reopen it.
				if ( gframe ) {
					gframe.open();
					return false;
				}

				// Create a new media frame
				var gframe = wp.media( {
					title: storeSuiteFrontScript.upload_gallery_images,
					button: {
						text: storeSuiteFrontScript.insert_image,
					},
					multiple: true,
				} );

				gframe.on( 'select', function () {
					$( '#product_gallery_img' ).html();
					var galleryImageIds = $.map(
						( $( '#product_image_gallery' ).val() || '' ).split(
							','
						),
						function ( imageId ) {
							imageId = $.trim( imageId );
							return imageId ? imageId : null;
						}
					);

					var galleryImageUrls = $.map(
						( $( '#product_image_gallery_url' ).val() || '' ).split(
							','
						),
						function ( imageUrl ) {
							imageUrl = $.trim( imageUrl );
							return imageUrl ? imageUrl : null;
						}
					);
					var attachments = gframe
						.state()
						.get( 'selection' )
						.toJSON();

					for ( var singleItem in attachments ) {
						var attachment = attachments[ singleItem ];
						if (
							$.inArray(
								String( attachment.id ),
								galleryImageIds
							) === -1
						) {
							galleryImageIds.push( String( attachment.id ) );
							galleryImageUrls.push(
								attachment.sizes.thumbnail.url
							);
							$( '#product_gallery_img' ).append(
								'<div class="preview-image-box"><i class="las la-trash" data-id="' +
									attachment.id +
									'"></i><img src="' +
									attachment.sizes.thumbnail.url +
									'" data-id="' +
									attachment.id +
									'" alt="' +
									storeSuiteFrontScript.product_gallery_image +
									'"/></div>'
							);
						}
					}
					// Send the attachment ids to our hidden input
					$( '#product_image_gallery' ).val(
						galleryImageIds.join( ',' )
					);
					$( '#product_image_gallery_url' ).val(
						galleryImageUrls.join( ',' )
					);

					//add class to hide text normaly
					$( targetContainer ).addClass(
						'sm-gallery-image-uploader'
					);
					$( '.product-gallery-images-wrapper' ).removeClass(
						'gallery-has-no-image'
					);
				} );

				gframe.open();
			} );
		},
		removeGalleryImage: function () {
			$( document ).on(
				'click',
				'.remove-gallery-image',
				function ( event ) {
					event.preventDefault();
					var imageId = $( this ).data( 'id' );
					var galleryImageIds = $( '#product_image_gallery' ).val();
					var newGalleryImageIds = galleryImageIds
						.split( ',' )
						.filter( function ( id ) {
							return parseInt( id ) !== parseInt( imageId );
						} )
						.join( ',' );
					$( '#product_image_gallery' ).val( newGalleryImageIds );
					$( this ).closest( '.preview-image-box' ).remove();
				}
			);
		},
		uploadCategoryImage: function () {
			$( '#category-single-image' ).click( function ( event ) {
				event.preventDefault();

				var image_id = $( '#product_category_thumbnail_url' ).val();
				var targetContainer = $( this );

				if ( image_id && image_id.length > 0 ) {
					$( '#product_category_thumbnail_id' ).val( '' );
					$( '#product_category_thumbnail_url' ).val( '' );
					$( '#category_thumb_img' ).html( '' );
					$( targetContainer )
						.find( '.image-drop-text span' )
						.text( storeSuiteFrontScript.upload_image_text );
					$( targetContainer ).removeClass( 'image-drop-bg' );
				} else {
					// If the media frame already exists, reopen it.
					if ( frame ) {
						frame.open();
						return false;
					}

					// Create a new media frame
					var frame = wp.media( {
						title: storeSuiteFrontScript.upload_category_image,
						button: {
							text: storeSuiteFrontScript.insert_image,
						},
						multiple: false,
					} );

					frame.on( 'select', function () {
						var attachment = frame
							.state()
							.get( 'selection' )
							.first()
							.toJSON();

						// Send the attachment id to our hidden input
						$( '#product_category_thumbnail_id' ).val(
							attachment.id
						);

						// Send the attachment URL to our custom image input field.
						$( '#category_thumb_img' ).html(
							'<img src="' +
								attachment.sizes.thumbnail.url +
								'" alt="' +
								storeSuiteFrontScript.category_image +
								'"/>'
						);
						$( '#product_category_thumbnail_url' ).val(
							attachment.sizes.thumbnail.url
						);

						//add class to hide text normaly
						$( targetContainer ).addClass( 'image-drop-bg' );
						$(
							'#category-single-image .image-drop-text span'
						).text( storeSuiteFrontScript.remove_image_text );
					} );

					frame.open();
				}
			} );
		},
		uploadBrandImage: function () {
			$( '#brand-single-image' ).click( function ( event ) {
				event.preventDefault();

				var image_id = $( '#product_brand_thumbnail_url' ).val();
				var targetContainer = $( this );

				if ( image_id && image_id.length > 0 ) {
					$( '#product_brand_thumbnail_id' ).val( '' );
					$( '#product_brand_thumbnail_url' ).val( '' );
					$( '#brand_thumb_img' ).html( '' );
					$( targetContainer )
						.find( '.image-drop-text span' )
						.text( storeSuiteFrontScript.upload_image_text );
					$( targetContainer ).removeClass( 'image-drop-bg' );
				} else {
					// If the media frame already exists, reopen it.
					if ( frame ) {
						frame.open();
						return false;
					}

					// Create a new media frame
					var frame = wp.media( {
						title: storeSuiteFrontScript.upload_brand_image,
						button: {
							text: storeSuiteFrontScript.insert_image,
						},
						multiple: false,
					} );

					frame.on( 'select', function () {
						var attachment = frame
							.state()
							.get( 'selection' )
							.first()
							.toJSON();

						// Send the attachment id to our hidden input
						$( '#product_brand_thumbnail_id' ).val( attachment.id );

						// Send the attachment URL to our custom image input field.
						$( '#brand_thumb_img' ).html(
							'<img src="' +
								attachment.sizes.thumbnail.url +
								'" alt="' +
								storeSuiteFrontScript.brand_image +
								'"/>'
						);
						$( '#product_brand_thumbnail_url' ).val(
							attachment.sizes.thumbnail.url
						);

						//add class to hide text normaly
						$( targetContainer ).addClass( 'image-drop-bg' );
						$( '#brand-single-image .image-drop-text span' ).text(
							storeSuiteFrontScript.remove_image_text
						);
					} );

					frame.open();
				}
			} );
		},
		handleFilterOffcanvas: function () {
			var filterToggle = $( '#storesuite-filter-toggle' );
			var filterOffcanvas = $( '#storesuite-filter-offcanvas' );
			var filterClose = $( '#storesuite-filter-close' );
			var filterOverlay = $( '#storesuite-filter-overlay' );

			// Open off-canvas when filter button is clicked
			filterToggle.on( 'click', function ( event ) {
				event.preventDefault();
				filterOffcanvas.addClass( 'active' );
				filterOverlay.addClass( 'active' );
				$( 'body' ).css( 'overflow', 'hidden' );
			} );

			// Close off-canvas when close button is clicked
			filterClose.on( 'click', function ( event ) {
				event.preventDefault();
				filterOffcanvas.removeClass( 'active' );
				filterOverlay.removeClass( 'active' );
				$( 'body' ).css( 'overflow', 'auto' );
			} );

			// Close off-canvas when overlay is clicked
			filterOverlay.on( 'click', function () {
				filterOffcanvas.removeClass( 'active' );
				filterOverlay.removeClass( 'active' );
				$( 'body' ).css( 'overflow', 'auto' );
			} );

			// Close off-canvas on Escape key
			$( document ).on( 'keydown', function ( event ) {
				if ( event.key === 'Escape' ) {
					filterOffcanvas.removeClass( 'active' );
					filterOverlay.removeClass( 'active' );
					$( 'body' ).css( 'overflow', 'auto' );
				}
			} );
		},
		handleOrderFilterOffcanvas: function () {
			var orderFilterToggle = $( '#storesuite-order-filter-toggle' );
			var orderFilterOffcanvas = $(
				'#storesuite-order-filter-offcanvas'
			);
			var orderFilterClose = $( '#storesuite-order-filter-close' );
			var orderFilterOverlay = $( '#storesuite-order-filter-overlay' );

			// Open off-canvas when filter button is clicked
			orderFilterToggle.on( 'click', function ( event ) {
				event.preventDefault();
				orderFilterOffcanvas.addClass( 'active' );
				orderFilterOverlay.addClass( 'active' );
				$( 'body' ).css( 'overflow', 'hidden' );
			} );

			// Close off-canvas when close button is clicked
			orderFilterClose.on( 'click', function ( event ) {
				event.preventDefault();
				orderFilterOffcanvas.removeClass( 'active' );
				orderFilterOverlay.removeClass( 'active' );
				$( 'body' ).css( 'overflow', 'auto' );
			} );

			// Close off-canvas when overlay is clicked
			orderFilterOverlay.on( 'click', function () {
				orderFilterOffcanvas.removeClass( 'active' );
				orderFilterOverlay.removeClass( 'active' );
				$( 'body' ).css( 'overflow', 'auto' );
			} );

			// Close off-canvas on Escape key
			$( document ).on( 'keydown', function ( event ) {
				if ( event.key === 'Escape' ) {
					orderFilterOffcanvas.removeClass( 'active' );
					orderFilterOverlay.removeClass( 'active' );
					$( 'body' ).css( 'overflow', 'auto' );
				}
			} );
		},
	};
	StoreFrontCommonConfig.init();
} )( jQuery );
