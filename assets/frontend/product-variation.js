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
		currentPage: 1,
		totalPages: 1,

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
			$( document ).on( 'click', '.storesuite-variation-page-link', this.changePage.bind( this ) );
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
						StoreSuiteVariations.currentPage = response.data.page || 1;
						StoreSuiteVariations.totalPages = response.data.total_pages || 1;
						$( '#storesuite-variations-container' ).html( response.data.html );
						$( '.storesuite-variation-count' ).text( response.data.total ? response.data.total + ' variations' : '' );
						StoreSuiteVariations.renderPagination();
					} else if ( response && response.data && response.data.message ) {
						Swal.fire( { icon: 'error', text: response.data.message } );
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
						StoreSuiteVariations.updateCountByDelta( 1 );
					} else if ( response && response.data && response.data.message ) {
						Swal.fire( { icon: 'error', text: response.data.message } );
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
					} else if ( response && response.data && response.data.message ) {
						Swal.fire( { icon: 'error', text: response.data.message } );
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
					product_id: StoreSuiteVariation.product_id
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
					product_id: StoreSuiteVariation.product_id,
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

	StoreSuiteAttributes.init();
	StoreSuiteVariations.init();
} )( jQuery );