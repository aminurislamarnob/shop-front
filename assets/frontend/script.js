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
						title: 'Upload Product Image',
						button: {
							text: 'Insert Image',
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
								'" alt="Product Image"/>'
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
					title: 'Upload Product Image',
					button: {
						text: 'Insert Image',
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
									'" alt="Product Gallery Image"/></div>'
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
	};
	StoreFrontCommonConfig.init();
} )( jQuery );
