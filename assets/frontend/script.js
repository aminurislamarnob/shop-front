( function ( $ ) {
	var msfcLoader = {
		block: function ( $container, text ) {
			text = text || 'Processing...';
			if ( $container.find( '.msfc-loader-overlay' ).length === 0 ) {
				$container.append(
					'<div class="msfc-loader-overlay">' +
						'<span class="msfc-loader-spinner"></span>' +
						'<span class="msfc-loader-text">' +
						text +
						'</span>' +
						'</div>'
				);
			} else {
				$container.find( '.msfc-loader-text' ).text( text );
			}

			setTimeout( function () {
				$container.find( '.msfc-loader-overlay' ).addClass( 'active' );
			}, 10 );
		},
		unblock: function ( $container ) {
			$container.find( '.msfc-loader-overlay' ).removeClass( 'active' );
		},
	};
	// Expose for other StoreSuite scripts
	window.StoreSuite = window.StoreSuite || {};
	window.StoreSuite.msfcLoader = msfcLoader;

	var StoreFrontCommonConfig = {
		init: function () {
			this.bindEvents();
		},
		bindEvents: function () {
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

			function msfUpdateDashboardRange( startDate, endDate ) {
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
					msfUpdateDashboardRange( startDate, endDate );
				}
			);

			// Initialize display/value on load.
			msfUpdateDashboardRange( start, end );
		},
		handleBulkActionCheckbox: function () {
			$( '#cb-select-all-orders' ).on( 'click', function () {
				var isChecked = $( this ).prop( 'checked' );
				$( 'input[name="bulk_order_ids[]"]' ).prop(
					'checked',
					isChecked
				);
			} );
		},
		handleDropdown: function () {
			$( document ).on( 'click', '.msfc-dropdown-icon', function () {
				$( '.msfc-dropdown-menu' ).hide();
				$( this )
					.closest( '.msfc-dropdown' )
					.find( '.msfc-dropdown-menu' )
					.toggle();
			} );
		},
		closeDropdownOutside: function () {
			$( document ).on( 'click', function ( event ) {
				if ( ! $( event.target ).closest( '.msfc-dropdown' ).length ) {
					$( '.msfc-dropdown-menu' ).hide();
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
			var filterToggle = $( '#msf-filter-toggle' );
			var filterOffcanvas = $( '#msf-filter-offcanvas' );
			var filterClose = $( '#msf-filter-close' );
			var filterOverlay = $( '#msf-filter-overlay' );

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
			var orderFilterToggle = $( '#msf-order-filter-toggle' );
			var orderFilterOffcanvas = $( '#msf-order-filter-offcanvas' );
			var orderFilterClose = $( '#msf-order-filter-close' );
			var orderFilterOverlay = $( '#msf-order-filter-overlay' );

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
