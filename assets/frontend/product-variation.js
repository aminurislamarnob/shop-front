( function( $ ) {
	/**
	 * Product ID for variation AJAX: prefer #storesuite-variations-container data-product-id (always correct on edit screen);
	 * fallback to localized StoreSuiteVariation.product_id.
	 *
	 * @return {number}
	 */
	function storesuite_get_variation_product_id() {
		var $c = $( '#storesuite-variations-container' );
		var fromAttr = 0;
		if ( $c.length ) {
			// Prefer HTML attribute — jQuery .data() can cache/stale vs data-product-id.
			fromAttr = parseInt( $c.attr( 'data-product-id' ), 10 ) || 0;
		}
		if ( ! fromAttr ) {
			fromAttr = parseInt( $( '#storesuite-add-product input[name="product_id"]' ).val(), 10 ) || 0;
		}
		if ( ! fromAttr && typeof StoreSuiteVariation !== 'undefined' && StoreSuiteVariation.product_id ) {
			fromAttr = parseInt( StoreSuiteVariation.product_id, 10 ) || 0;
		}
		return fromAttr;
	}

	function storesuite_variation_safe_block() {
		if ( window.StoreSuite && window.StoreSuite.storeSuiteLoader ) {
			window.StoreSuite.storeSuiteLoader.block( $( '.my-storesuite-wrapper' ) );
		}
	}

	function storesuite_variation_safe_unblock() {
		if ( window.StoreSuite && window.StoreSuite.storeSuiteLoader ) {
			window.StoreSuite.storeSuiteLoader.unblock( $( '.my-storesuite-wrapper' ) );
		}
	}

	var StoreSuiteAttributes = {
		index: 0,

		init: function() {
			this.index = $( '#storesuite-attributes-list .storesuite-attribute-row' ).length;

			$( document ).on( 'click', '#storesuite-add-attribute-btn', this.addAttribute.bind( this ) );
			$( document ).on( 'click', '#storesuite-save-attributes-btn', this.saveAttributes.bind( this ) );
			$( document ).on( 'click', '.storesuite-remove-attribute', this.removeAttribute );
			$( document ).on( 'click', '.storesuite-toggle-attribute', this.toggleAttribute );
			$( document ).on( 'click', '.storesuite-select-all-terms', this.selectAllTerms );
			$( document ).on( 'click', '.storesuite-select-no-terms', this.selectNoTerms );
		},

		addAttribute: function() {
			var taxonomy = $( '#storesuite-add-attribute-select' ).val();
			var index = this.index++;

			storesuite_variation_safe_block();

			$.post(
				StoreSuiteVariation.ajax_url,
				{
					action: 'storesuite_add_attribute',
					security: StoreSuiteVariation.add_attribute_nonce,
					product_id: storesuite_get_variation_product_id(),
					product_type: $( '#post_type' ).val(),
					taxonomy: taxonomy,
					i: index
				}
			).done(
				function( response ) {
					if ( response && response.success && response.data && response.data.html ) {
						$( '#storesuite-attributes-list' ).append( response.data.html );

						$( '.storesuite-select2' ).filter( ':not(.enhanced)' ).each(
							function() {
								$( this ).selectWoo(
									{
										allowClear: true,
										placeholder: $( this ).data( 'placeholder' ) || '',
										tags: $( this ).data( 'tags' ) || false,
										tokenSeparators: [ '|' ],
										width: '100%'
									}
								).addClass( 'enhanced' );
							}
						);

						if ( taxonomy ) {
							$( '#storesuite-add-attribute-select option[value="' + taxonomy + '"]' ).remove();
						}
					}
				}
			).always(
				function() {
					storesuite_variation_safe_unblock();
				}
			);
		},

		saveAttributes: function() {
			var formData = new FormData();
			formData.append( 'action', 'storesuite_save_attributes' );
			formData.append( 'security', StoreSuiteVariation.save_attributes_nonce );
			formData.append( 'product_id', storesuite_get_variation_product_id() );
			formData.append( 'product_type', $( '#post_type' ).val() );

			$( '#storesuite-attributes-list :input' ).each(
				function() {
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
						( $input.val() || [] ).forEach(
							function( value ) {
								formData.append( name, value );
							}
						);
					} else {
						formData.append( name, $input.val() );
					}
				}
			);

			storesuite_variation_safe_block();

			$.ajax(
				{
					url: StoreSuiteVariation.ajax_url,
					type: 'POST',
					data: formData,
					processData: false,
					contentType: false
				}
			).done(
				function( response ) {
					if ( response.success ) {
						Swal.fire( { icon: 'success', text: response.data.message, timer: 1500, showConfirmButton: false } );
						StoreSuiteVariations.reload();
					}
				}
			).always(
				function() {
					storesuite_variation_safe_unblock();
				}
			);
		},

		removeAttribute: function( e ) {
			e.preventDefault();
			$( this ).closest( '.storesuite-attribute-row' ).slideUp( 200, function() { $( this ).remove(); } );
		},

		toggleAttribute: function( e ) {
			e.preventDefault();
			$( this ).closest( '.storesuite-attribute-row' ).find( '.storesuite-attribute-body' ).slideToggle( 200 );
		},

		selectAllTerms: function( e ) {
			e.preventDefault();
			var $select = $( this ).closest( '.storesuite-form-group' ).find( 'select' );
			$select.find( 'option' ).prop( 'selected', true );
			$select.trigger( 'change' );
		},

		selectNoTerms: function( e ) {
			e.preventDefault();
			var $select = $( this ).closest( '.storesuite-form-group' ).find( 'select' );
			$select.find( 'option' ).prop( 'selected', false );
			$select.trigger( 'change' );
		}
	};

	var StoreSuiteVariations = {
		currentPage: 1,
		totalPages: 1,

		init: function() {
			if ( ! $( '#storesuite-variations-container' ).length ) {
				return;
			}

			this.bindEvents();

			if ( ! storesuite_get_variation_product_id() ) {
				return;
			}

			this.loadVariations( 1 );
		},

		/**
		 * Load rows when switching product type to Variable (init may have skipped load earlier).
		 *
		 * @return {void}
		 */
		maybeLoadIfVariable: function() {
			if ( 'variable' !== $( 'select#post_type' ).val() ) {
				return;
			}
			if ( ! $( '#storesuite-variations-container' ).length ) {
				return;
			}
			if ( ! storesuite_get_variation_product_id() ) {
				return;
			}
			if ( $( '#storesuite-variations-container' ).children().length ) {
				return;
			}
			this.loadVariations( 1 );
		},

		bindEvents: function() {
			var self = this;
			$( document ).on( 'click', '#storesuite-do-variation-action', this.handleToolbarAction.bind( this ) );
			$( document ).on( 'click', '#storesuite-save-variations-btn', this.saveVariations.bind( this ) );
			$( document ).on( 'click', '.storesuite-toggle-variation', this.toggleVariationRow );
			$( document ).on( 'click', '.storesuite-remove-variation', this.removeVariation.bind( this ) );
			$( document ).on( 'change input', '#storesuite-variations-container :input', this.markVariationDirty );
			$( document ).on( 'change', '.variable_manage_stock', this.toggleManageStock );
			$( document ).on( 'click', '.storesuite-variation-page-link', this.changePage.bind( this ) );
			$( document ).on( 'change', 'select#post_type', function() {
				self.maybeLoadIfVariable();
			} );
		},

		loadVariations: function( page ) {
			if ( typeof StoreSuiteVariation === 'undefined' ) {
				return;
			}
			var perPage = parseInt( $( '#storesuite-variations-container' ).attr( 'data-per-page' ), 10 ) || StoreSuiteVariation.per_page || 15;
			storesuite_variation_safe_block();

			$.post(
				StoreSuiteVariation.ajax_url,
				{
					action: 'storesuite_load_variations',
					security: StoreSuiteVariation.nonce,
					product_id: storesuite_get_variation_product_id(),
					page: page || 1,
					per_page: perPage
				}
			).done(
				function( response ) {
					if ( response.success ) {
						StoreSuiteVariations.currentPage = response.data.page || 1;
						StoreSuiteVariations.totalPages = response.data.total_pages || 1;
						$( '#storesuite-variations-container' ).html( response.data.html );
						$( '.storesuite-variation-count' ).text( response.data.total ? response.data.total + ' variations' : '' );
						StoreSuiteVariations.renderPagination();
						$( document ).trigger( 'storesuite_variation_dom_updated' );
					} else if ( response && response.data && response.data.message ) {
						Swal.fire( { icon: 'error', text: response.data.message } );
					}
				}
			).fail(
				function( xhr ) {
					var msg = xhr && xhr.responseJSON && xhr.responseJSON.data && xhr.responseJSON.data.message
						? xhr.responseJSON.data.message
						: 'Could not load variations.';
					Swal.fire( { icon: 'error', text: msg } );
				}
			).always(
				function() {
					storesuite_variation_safe_unblock();
				}
			);
		},

		reload: function() {
			if ( $( '#storesuite-variations-container' ).length ) {
				this.loadVariations( 1 );
			}
		},

		handleToolbarAction: function( e ) {
			e.preventDefault();
			var action = $( '#storesuite-variation-actions' ).val();

			if ( 'add_variation' === action ) {
				this.addVariation();
			} else if ( 'generate_all' === action ) {
				this.generateVariations();
			} else if ( 'delete_all' === action ) {
				this.bulkDeleteAll();
			}
		},

		addVariation: function() {
			// Use max existing loop index + 1 so pagination (e.g. page 2: loops 15–29) never collides with row count.
			var maxLoop = -1;
			$( '#storesuite-variations-container .storesuite-variation-row' ).each(
				function() {
					var name = $( this ).find( 'input[name^="variable_post_id"]' ).attr( 'name' );
					if ( name ) {
						var m = name.match( /\[(\d+)\]/ );
						if ( m ) {
							maxLoop = Math.max( maxLoop, parseInt( m[1], 10 ) );
						}
					}
				}
			);
			var loop = maxLoop + 1;
			storesuite_variation_safe_block();

			$.post(
				StoreSuiteVariation.ajax_url,
				{
					action: 'storesuite_add_variation',
					security: StoreSuiteVariation.nonce,
					product_id: storesuite_get_variation_product_id(),
					loop: loop
				}
			).done(
				function( response ) {
					if ( response.success ) {
						$( '#storesuite-variations-container' ).append( response.data.html );
						StoreSuiteVariations.updateCountByDelta( 1 );
						$( document ).trigger( 'storesuite_variation_dom_updated' );
					} else if ( response && response.data && response.data.message ) {
						Swal.fire( { icon: 'error', text: response.data.message } );
					}
				}
			).always(
				function() {
					storesuite_variation_safe_unblock();
				}
			);
		},

		saveVariations: function( e ) {
			e.preventDefault();
			var $dirtyRows = $( '#storesuite-variations-container .variation-needs-update' );
			if ( ! $dirtyRows.length ) {
				return;
			}

			var formData = new FormData();
			formData.append( 'action', 'storesuite_save_variations' );
			formData.append( 'security', StoreSuiteVariation.nonce );
			formData.append( 'product_id', storesuite_get_variation_product_id() );

			$dirtyRows.find( ':input' ).each(
				function() {
					var $input = $( this );
					var name = $input.attr( 'name' );
					if ( ! name ) {
						return;
					}
					if ( $input.is( ':checkbox' ) ) {
						if ( $input.is( ':checked' ) ) {
							formData.append( name, $input.val() );
						}
					} else {
						formData.append( name, $input.val() );
					}
				}
			);

			storesuite_variation_safe_block();
			$.ajax(
				{
					url: StoreSuiteVariation.ajax_url,
					type: 'POST',
					data: formData,
					processData: false,
					contentType: false
				}
			).done(
				function( response ) {
					if ( response.success ) {
						$dirtyRows.removeClass( 'variation-needs-update' );
						$( '#storesuite-save-variations-btn' ).prop( 'disabled', true );
						Swal.fire( { icon: 'success', text: response.data.message, timer: 1500, showConfirmButton: false } );
					} else if ( response && response.data && response.data.message ) {
						Swal.fire( { icon: 'error', text: response.data.message } );
					}
				}
			).always(
				function() {
					storesuite_variation_safe_unblock();
				}
			);
		},

		removeVariation: function( e ) {
			e.preventDefault();
			var variationId = $( e.currentTarget ).data( 'variation-id' );
			var $row = $( e.currentTarget ).closest( '.storesuite-variation-row' );
			if ( ! window.confirm( StoreSuiteVariation.i18n.confirm_remove ) ) {
				return;
			}

			$.post(
				StoreSuiteVariation.ajax_url,
				{
					action: 'storesuite_remove_variation',
					security: StoreSuiteVariation.nonce,
					variation_id: variationId
				}
			).done(
				function( response ) {
					if ( response.success ) {
						$row.remove();
						StoreSuiteVariations.updateCountByDelta( -1 );
					} else if ( response && response.data && response.data.message ) {
						Swal.fire( { icon: 'error', text: response.data.message } );
					}
				}
			);
		},

		generateVariations: function() {
			$.post(
				StoreSuiteVariation.ajax_url,
				{
					action: 'storesuite_generate_variations',
					security: StoreSuiteVariation.nonce,
					product_id: storesuite_get_variation_product_id()
				}
			).done(
				function( response ) {
					if ( response.success ) {
						Swal.fire( { icon: 'success', text: response.data.message, timer: 1500, showConfirmButton: false } );
						StoreSuiteVariations.reload();
					} else if ( response && response.data && response.data.message ) {
						Swal.fire( { icon: 'error', text: response.data.message } );
					}
				}
			);
		},

		bulkDeleteAll: function() {
			if ( ! window.confirm( StoreSuiteVariation.i18n.confirm_delete_all ) ) {
				return;
			}

			$.post(
				StoreSuiteVariation.ajax_url,
				{
					action: 'storesuite_bulk_edit_variations',
					security: StoreSuiteVariation.nonce,
					product_id: storesuite_get_variation_product_id(),
					bulk_action: 'delete_all'
				}
			).done(
				function( response ) {
					if ( response.success ) {
						StoreSuiteVariations.reload();
					} else if ( response && response.data && response.data.message ) {
						Swal.fire( { icon: 'error', text: response.data.message } );
					}
				}
			);
		},

		toggleVariationRow: function( e ) {
			e.preventDefault();
			$( this ).closest( '.storesuite-variation-row' ).find( '.storesuite-variation-body' ).slideToggle( 200 );
		},

		markVariationDirty: function() {
			$( this ).closest( '.storesuite-variation-row' ).addClass( 'variation-needs-update' );
			$( '#storesuite-save-variations-btn' ).prop( 'disabled', false );
		},

		toggleManageStock: function() {
			var $row = $( this ).closest( '.storesuite-variation-row' );
			if ( $( this ).is( ':checked' ) ) {
				$row.find( '.show_if_variation_manage_stock' ).slideDown( 'fast' );
			} else {
				$row.find( '.show_if_variation_manage_stock' ).slideUp( 'fast' );
			}
		},

		changePage: function( e ) {
			e.preventDefault();
			var page = parseInt( $( e.currentTarget ).data( 'page' ), 10 );
			if ( ! page || page < 1 || page === this.currentPage ) {
				return;
			}
			this.loadVariations( page );
		},

		renderPagination: function() {
			var $pagination = $( '#storesuite-variations-pagination' );
			if ( ! $pagination.length ) {
				return;
			}

			if ( this.totalPages <= 1 ) {
				$pagination.empty();
				return;
			}

			var html = '';
			for ( var i = 1; i <= this.totalPages; i++ ) {
				var activeClass = i === this.currentPage ? ' is-active' : '';
				html += '<a href="#" class="storesuite-variation-page-link' + activeClass + '" data-page="' + i + '" style="display:inline-block;padding:4px 8px;margin-right:6px;border:1px solid #ddd;border-radius:4px;">' + i + '</a>';
			}
			$pagination.html( html );
		},

		updateCountByDelta: function( delta ) {
			var $count = $( '.storesuite-variation-count' );
			var text = $.trim( $count.text() );
			var match = text.match( /^(\d+)/ );
			if ( ! match ) {
				return;
			}

			var next = Math.max( 0, parseInt( match[1], 10 ) + delta );
			$count.text( next ? next + ' variations' : '' );
		}
	};

	var StoreSuiteVariationUi = {
		init: function() {
			this.bindVariationImage();
			this.bindVirtualToggle();
			this.refreshVirtualVisibility();
			$( document ).on( 'storesuite_variation_dom_updated', function() {
				StoreSuiteVariationUi.refreshVirtualVisibility();
			} );
		},

		refreshVirtualVisibility: function() {
			$( '.storesuite-variation-row .variable_is_virtual' ).each( function() {
				var $row = $( this ).closest( '.storesuite-variation-row' );
				if ( $( this ).is( ':checked' ) ) {
					$row.find( '.hide_if_variation_virtual' ).hide();
				} else {
					$row.find( '.hide_if_variation_virtual' ).show();
				}
			} );
		},

		bindVariationImage: function() {
			$( document ).on( 'click', '.storesuite-variation-image-uploader', function( e ) {
				e.preventDefault();
				if ( typeof wp === 'undefined' || ! wp.media ) {
					return;
				}
				var i18n = typeof storeSuiteFrontScript !== 'undefined' ? storeSuiteFrontScript : {};
				var $wrap = $( this );
				var $hidden = $wrap.find( '.storesuite-variation-image-id' );
				var $thumb = $wrap.find( '.storesuite-variation-image-thumb' );
				var $label = $wrap.find( '.storesuite-variation-image-label' );

				if ( $hidden.val() ) {
					$hidden.val( '' );
					$thumb.empty();
					$label.text( i18n.upload_image_text || 'Upload Image' );
					$wrap.removeClass( 'image-drop-bg' );
					$wrap.closest( '.storesuite-variation-row' ).addClass( 'variation-needs-update' );
					$( '#storesuite-save-variations-btn' ).prop( 'disabled', false );
					return;
				}

				var frame = wp.media( {
					title: i18n.upload_product_image || 'Upload image',
					button: { text: i18n.insert_image || 'Insert' },
					multiple: false
				} );

				frame.on( 'select', function() {
					var attachment = frame.state().get( 'selection' ).first().toJSON();
					$hidden.val( attachment.id );
					var imgUrl = ( attachment.sizes && attachment.sizes.thumbnail && attachment.sizes.thumbnail.url ) ? attachment.sizes.thumbnail.url : attachment.url;
					$thumb.html( '<img src="' + imgUrl + '" alt="" />' );
					$label.text( i18n.remove_image_text || 'Remove Image' );
					$wrap.addClass( 'image-drop-bg' );
					$wrap.closest( '.storesuite-variation-row' ).addClass( 'variation-needs-update' );
					$( '#storesuite-save-variations-btn' ).prop( 'disabled', false );
				} );

				frame.open();
			} );
		},

		bindVirtualToggle: function() {
			$( document ).on( 'change', '.storesuite-variation-row .variable_is_virtual', function() {
				var $row = $( this ).closest( '.storesuite-variation-row' );
				if ( $( this ).is( ':checked' ) ) {
					$row.find( '.hide_if_variation_virtual' ).hide();
				} else {
					$row.find( '.hide_if_variation_virtual' ).show();
				}
			} );
		}
	};

	$( function() {
		StoreSuiteAttributes.init();
		StoreSuiteVariations.init();
		StoreSuiteVariationUi.init();
	} );
} )( jQuery );