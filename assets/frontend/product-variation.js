( function ( $ ) {
	'use strict';

	var StoreSuiteVariations = {
		init: function () {
			this.toggleAttributeContent();
			this.removeAttribute();
			this.addAttribute();
			this.toggleVariationContent();
			this.removeVariation();
			this.addVariation();
			this.linkAllVariations();
			this.variationImageUpload();
			this.variationManageStockToggle();
		},

		getProductId: function () {
			return parseInt( $( '#storesuite-product-id' ).val() || 0, 10 );
		},

		getVariationLoop: function () {
			return parseInt( $( '#storesuite-variation-loop' ).val() || 0, 10 );
		},

		setVariationLoop: function ( val ) {
			$( '#storesuite-variation-loop' ).val( val );
		},

		toggleAttributeContent: function () {
			$( document ).on( 'click', '.storesuite-toggle-attribute', function ( e ) {
				e.preventDefault();
				var $row = $( this ).closest( '.product-attribute-list' );
				$row.find( '.storesuite-attribute-content' ).slideToggle( 200 );
				$row.find( '.toggle-icon' ).text( $row.find( '.storesuite-attribute-content' ).is( ':visible' ) ? '▲' : '▼' );
			} );
		},

		removeAttribute: function () {
			$( document ).on( 'click', '.storesuite-remove-attribute', function ( e ) {
				e.preventDefault();
				$( this ).closest( '.product-attribute-list' ).slideUp( 200, function () {
					$( this ).remove();
				} );
			} );
		},

		addAttribute: function () {
			var self = this;
			$( document ).on( 'click', '.storesuite-add-attribute', function ( e ) {
				e.preventDefault();
				var $list = $( '#storesuite-product-attributes' );
				var predefined = $( '#storesuite-predefined-attribute' ).val();
				var productId = self.getProductId();
				var nextIndex = $list.find( '.product-attribute-list' ).length;

				if ( predefined ) {
					$.ajax( {
						url: typeof StoreSuite_Variations !== 'undefined' ? StoreSuite_Variations.ajax_url : '',
						type: 'POST',
						data: {
							action:   'storesuite_get_predefined_attribute',
							nonce:    typeof StoreSuite_Variations !== 'undefined' ? StoreSuite_Variations.nonce : '',
							taxonomy: predefined,
							i:        nextIndex,
							product_id: productId,
						},
						success: function ( res ) {
							if ( res.success && res.data.html ) {
								$list.append( res.data.html );
								self.initSelect2OnAttributes();
							}
						},
					} );
				} else {
					var tmpl = $( '#tmpl-storesuite-custom-attribute' ).html();
					if ( tmpl ) {
						var html = tmpl.replace( /\{\{i\}\}/g, nextIndex );
						$list.append( html );
						self.initSelect2OnAttributes();
					}
				}
			} );
		},

		initSelect2OnAttributes: function () {
			var $new = $( '#storesuite-product-attributes .storesuite-attr-values' ).filter( ':not(.select2-hidden-accessible)' );
			if ( $new.length && $.fn.selectWoo ) {
				$new.selectWoo( {
					allowClear: true,
					placeholder: $new.data( 'placeholder' ) || '',
					width: '100%',
					tags: $new.data( 'tags' ) || false,
					tokenSeparators: $new.data( 'token-separators' ) || [ ',', '|' ],
				} );
			}
		},

		toggleVariationContent: function () {
			$( document ).on( 'click', '.storesuite-variation-heading, .storesuite-variation-heading .handlediv', function ( e ) {
				if ( $( e.target ).is( '.storesuite-remove-variation' ) ) {
					return;
				}
				e.preventDefault();
				var $row = $( this ).closest( '.storesuite-variation' );
				$row.find( '.storesuite-variation-content' ).slideToggle( 200 );
				$row.toggleClass( 'closed' );
			} );
		},

		removeVariation: function () {
			$( document ).on( 'click', '.storesuite-remove-variation', function ( e ) {
				e.preventDefault();
				var variationId = $( this ).attr( 'rel' );
				var $row = $( this ).closest( '.storesuite-variation' );
				if ( ! variationId ) {
					$row.remove();
					return;
				}
				$.ajax( {
					url: typeof StoreSuite_Variations !== 'undefined' ? StoreSuite_Variations.ajax_url : '',
					type: 'POST',
					data: {
						action:      'storesuite_remove_variation',
						nonce:       typeof StoreSuite_Variations !== 'undefined' ? StoreSuite_Variations.nonce : '',
						variation_id: variationId,
					},
					success: function ( res ) {
						if ( res.success ) {
							$row.slideUp( 200, function () {
								$( this ).remove();
							} );
						}
					},
				} );
			} );
		},

		addVariation: function () {
			var self = this;
			$( document ).on( 'click', '.storesuite-add-variation', function ( e ) {
				e.preventDefault();
				var productId = self.getProductId();
				var loop = self.getVariationLoop();
				if ( ! productId ) {
					return;
				}
				var $btn = $( this );
				$btn.prop( 'disabled', true );
				$.ajax( {
					url: typeof StoreSuite_Variations !== 'undefined' ? StoreSuite_Variations.ajax_url : '',
					type: 'POST',
					data: {
						action:     'storesuite_add_variation',
						nonce:     typeof StoreSuite_Variations !== 'undefined' ? StoreSuite_Variations.nonce : '',
						product_id: productId,
						loop:      loop,
					},
					success: function ( res ) {
						$btn.prop( 'disabled', false );
						if ( res.success && res.data.html ) {
							$( '#storesuite-variations-container' ).append( res.data.html );
							self.setVariationLoop( loop + 1 );
						}
					},
					error: function () {
						$btn.prop( 'disabled', false );
					},
				} );
			} );
		},

		linkAllVariations: function () {
			var self = this;
			$( document ).on( 'click', '.storesuite-link-all-variations', function ( e ) {
				e.preventDefault();
				var productId = self.getProductId();
				if ( ! productId ) {
					return;
				}
				var $btn = $( this );
				$btn.prop( 'disabled', true );
				$.ajax( {
					url: typeof StoreSuite_Variations !== 'undefined' ? StoreSuite_Variations.ajax_url : '',
					type: 'POST',
					data: {
						action:     'storesuite_link_all_variations',
						nonce:     typeof StoreSuite_Variations !== 'undefined' ? StoreSuite_Variations.nonce : '',
						product_id: productId,
					},
					success: function ( res ) {
						$btn.prop( 'disabled', false );
						if ( res.success && res.data.added !== undefined ) {
							if ( res.data.added > 0 ) {
								window.location.reload();
							}
						}
					},
					error: function () {
						$btn.prop( 'disabled', false );
					},
				} );
			} );
		},

		variationImageUpload: function () {
			$( document ).on( 'click', '.storesuite-upload-variation-image', function ( e ) {
				e.preventDefault();
				var $btn = $( this );
				var $wrap = $btn.closest( '.storesuite-variation-image-wrap' );
				var $input = $wrap.find( '.upload_image_id' );
				var $img = $wrap.find( 'img' );
				var placeholder = typeof StoreSuite_Variations !== 'undefined' && StoreSuite_Variations.placeholder_img
					? StoreSuite_Variations.placeholder_img
					: '';

				if ( $btn.hasClass( 'has-image' ) ) {
					$input.val( 0 );
					$img.attr( 'src', placeholder );
					$btn.removeClass( 'has-image' );
					return;
				}

				var frame = wp.media( {
					title: typeof StoreSuite_Variations !== 'undefined' ? StoreSuite_Variations.i18n.set_image : 'Set image',
					button: { text: typeof StoreSuite_Variations !== 'undefined' ? StoreSuite_Variations.i18n.choose_image : 'Choose' },
					library: { type: 'image' },
					multiple: false,
				} );
				frame.on( 'select', function () {
					var att = frame.state().get( 'selection' ).first().toJSON();
					$input.val( att.id );
					$img.attr( 'src', att.sizes && att.sizes.thumbnail ? att.sizes.thumbnail.url : att.url );
					$btn.addClass( 'has-image' );
				} );
				frame.open();
			} );
		},

		variationManageStockToggle: function () {
			$( document ).on( 'change', 'input.variable_manage_stock', function () {
				var $row = $( this ).closest( '.storesuite-variation' );
				var show = $( this ).is( ':checked' );
				$row.find( '.show_if_variation_manage_stock' ).toggle( show );
			} );
		},
	};

	$( function () {
		if ( $( '#msfc-add-product' ).length && $( '#product-attributes-variations-card' ).length ) {
			StoreSuiteVariations.init();
		}
	} );
} )( jQuery );
