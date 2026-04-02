( function( $ ) {
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

			window.StoreSuite.storeSuiteLoader.block( $( '.my-storesuite-wrapper' ) );

			$.post(
				StoreSuiteVariation.ajax_url,
				{
					action: 'storesuite_add_attribute',
					security: StoreSuiteVariation.add_attribute_nonce,
					product_id: StoreSuiteVariation.product_id || 0,
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
					window.StoreSuite.storeSuiteLoader.unblock( $( '.my-storesuite-wrapper' ) );
				}
			);
		},

		saveAttributes: function() {
			var formData = new FormData();
			formData.append( 'action', 'storesuite_save_attributes' );
			formData.append( 'security', StoreSuiteVariation.save_attributes_nonce );
			formData.append( 'product_id', StoreSuiteVariation.product_id );
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

			window.StoreSuite.storeSuiteLoader.block( $( '.my-storesuite-wrapper' ) );

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
					window.StoreSuite.storeSuiteLoader.unblock( $( '.my-storesuite-wrapper' ) );
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
		init: function() {
			if ( ! $( '#storesuite-variations-container' ).length || ! StoreSuiteVariation.product_id ) {
				return;
			}

			this.bindEvents();
			this.loadVariations( 1 );
		},

		bindEvents: function() {
			$( document ).on( 'click', '#storesuite-do-variation-action', this.handleToolbarAction.bind( this ) );
			$( document ).on( 'click', '#storesuite-save-variations-btn', this.saveVariations.bind( this ) );
			$( document ).on( 'click', '.storesuite-toggle-variation', this.toggleVariationRow );
			$( document ).on( 'click', '.storesuite-remove-variation', this.removeVariation.bind( this ) );
			$( document ).on( 'change input', '#storesuite-variations-container :input', this.markVariationDirty );
			$( document ).on( 'change', '.variable_manage_stock', this.toggleManageStock );
		},

		loadVariations: function( page ) {
			var perPage = $( '#storesuite-variations-container' ).data( 'per-page' ) || StoreSuiteVariation.per_page || 15;
			window.StoreSuite.storeSuiteLoader.block( $( '.my-storesuite-wrapper' ) );

			$.post(
				StoreSuiteVariation.ajax_url,
				{
					action: 'storesuite_load_variations',
					security: StoreSuiteVariation.nonce,
					product_id: StoreSuiteVariation.product_id,
					page: page || 1,
					per_page: perPage
				}
			).done(
				function( response ) {
					if ( response.success ) {
						$( '#storesuite-variations-container' ).html( response.data.html );
						$( '.storesuite-variation-count' ).text( response.data.total ? response.data.total + ' variations' : '' );
					}
				}
			).always(
				function() {
					window.StoreSuite.storeSuiteLoader.unblock( $( '.my-storesuite-wrapper' ) );
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
			var loop = $( '#storesuite-variations-container .storesuite-variation-row' ).length;
			window.StoreSuite.storeSuiteLoader.block( $( '.my-storesuite-wrapper' ) );

			$.post(
				StoreSuiteVariation.ajax_url,
				{
					action: 'storesuite_add_variation',
					security: StoreSuiteVariation.nonce,
					product_id: StoreSuiteVariation.product_id,
					loop: loop
				}
			).done(
				function( response ) {
					if ( response.success ) {
						$( '#storesuite-variations-container' ).append( response.data.html );
					}
				}
			).always(
				function() {
					window.StoreSuite.storeSuiteLoader.unblock( $( '.my-storesuite-wrapper' ) );
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
			formData.append( 'product_id', StoreSuiteVariation.product_id );

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

			window.StoreSuite.storeSuiteLoader.block( $( '.my-storesuite-wrapper' ) );
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
					}
				}
			).always(
				function() {
					window.StoreSuite.storeSuiteLoader.unblock( $( '.my-storesuite-wrapper' ) );
				}
			);
		},

		removeVariation: function( e ) {
			e.preventDefault();
			var variationId = $( e.currentTarget ).data( 'variation-id' );
			var $row = $( e.currentTarget ).closest( '.storesuite-variation-row' );

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
					product_id: StoreSuiteVariation.product_id
				}
			).done(
				function( response ) {
					if ( response.success ) {
						Swal.fire( { icon: 'success', text: response.data.message, timer: 1500, showConfirmButton: false } );
						StoreSuiteVariations.reload();
					}
				}
			);
		},

		bulkDeleteAll: function() {
			$.post(
				StoreSuiteVariation.ajax_url,
				{
					action: 'storesuite_bulk_edit_variations',
					security: StoreSuiteVariation.nonce,
					product_id: StoreSuiteVariation.product_id,
					bulk_action: 'delete_all'
				}
			).done(
				function( response ) {
					if ( response.success ) {
						StoreSuiteVariations.reload();
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
		}
	};

	StoreSuiteAttributes.init();
	StoreSuiteVariations.init();
} )( jQuery );