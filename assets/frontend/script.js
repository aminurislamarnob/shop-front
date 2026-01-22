( function ( $ ) {
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
						.text( MSF_Front_Script.upload_image_text );
					$( targetContainer ).removeClass( 'image-drop-bg' );
				} else {
					// If the media frame already exists, reopen it.
					if ( frame ) {
						frame.open();
						return false;
					}

					// Create a new media frame
					var frame = wp.media( {
						title: MSF_Front_Script.upload_product_image,
						button: {
							text: MSF_Front_Script.insert_image,
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
								MSF_Front_Script.product_image +
								'"/>'
						);
						$( '#product_thumbnail_url' ).val(
							attachment.sizes.thumbnail.url
						);

						//add class to hide text normaly
						$( targetContainer ).addClass( 'image-drop-bg' );
						$( '#product-single-image .image-drop-text span' ).text(
							MSF_Front_Script.remove_image_text
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
					title: MSF_Front_Script.upload_gallery_images,
					button: {
						text: MSF_Front_Script.insert_image,
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
									MSF_Front_Script.product_gallery_image +
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
						.text( MSF_Front_Script.upload_image_text );
					$( targetContainer ).removeClass( 'image-drop-bg' );
				} else {
					// If the media frame already exists, reopen it.
					if ( frame ) {
						frame.open();
						return false;
					}

					// Create a new media frame
					var frame = wp.media( {
						title: MSF_Front_Script.upload_category_image,
						button: {
							text: MSF_Front_Script.insert_image,
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
								MSF_Front_Script.category_image +
								'"/>'
						);
						$( '#product_category_thumbnail_url' ).val(
							attachment.sizes.thumbnail.url
						);

						//add class to hide text normaly
						$( targetContainer ).addClass( 'image-drop-bg' );
						$(
							'#category-single-image .image-drop-text span'
						).text( MSF_Front_Script.remove_image_text );
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
						.text( MSF_Front_Script.upload_image_text );
					$( targetContainer ).removeClass( 'image-drop-bg' );
				} else {
					// If the media frame already exists, reopen it.
					if ( frame ) {
						frame.open();
						return false;
					}

					// Create a new media frame
					var frame = wp.media( {
						title: MSF_Front_Script.upload_brand_image,
						button: {
							text: MSF_Front_Script.insert_image,
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
								MSF_Front_Script.brand_image +
								'"/>'
						);
						$( '#product_brand_thumbnail_url' ).val(
							attachment.sizes.thumbnail.url
						);

						//add class to hide text normaly
						$( targetContainer ).addClass( 'image-drop-bg' );
						$( '#brand-single-image .image-drop-text span' ).text(
							MSF_Front_Script.remove_image_text
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
