(function($) {
    var StoreFrontOrderConfig = {
        init: function() {
            this.bindEvents();
        },
        bindEvents: function() {
            $('#customer_user').show().selectWoo().hide();
            this.handleSelect2Customer(); // Handle select2 customer
            $( '#customer_user' ).on( 'change', this.changeCustomerUser );
        },

        handleSelect2Customer: function() {
            // Ajax customer search boxes
            $( ':input.wc-customer-search' ).filter( ':not(.enhanced)' ).each( function() {
                var select2_args = {
                    allowClear:  $( this ).data( 'allow_clear' ) ? true : false,
                    placeholder: $( this ).data( 'placeholder' ),
                    minimumInputLength: $( this ).data( 'minimum_input_length' ) ? $( this ).data( 'minimum_input_length' ) : '1',
                    escapeMarkup: function( m ) {
                        return m;
                    },
                    ajax: {
                        url:         My_Shop_Front_Order.ajax_url,
                        dataType:    'json',
                        delay:       1000,
                        data:        function( params ) {
                            return {
                                term:     params.term,
                                action:   'woocommerce_json_search_customers',
                                security: My_Shop_Front_Order.search_customers_nonce,
                                exclude:  $( this ).data( 'exclude' )
                            };
                        },
                        processResults: function( data ) {
                            var terms = [];
                            if ( data ) {
                                $.each( data, function( id, text ) {
                                    terms.push({
                                        id: id,
                                        text: text
                                    });
                                });
                            }
                            return {
                                results: terms
                            };
                        },
                        cache: true
                    }
                };

                select2_args = $.extend( select2_args, StoreFrontOrderConfig.getEnhancedSelectFormatString() );

                $( this ).selectWoo( select2_args ).addClass( 'enhanced' );

                if ( $( this ).data( 'sortable' ) ) {
                    var $select = $(this);
                    var $list   = $( this ).next( '.select2-container' ).find( 'ul.select2-selection__rendered' );

                    $list.sortable({
                        placeholder : 'ui-state-highlight select2-selection__choice',
                        forcePlaceholderSize: true,
                        items       : 'li:not(.select2-search__field)',
                        tolerance   : 'pointer',
                        stop: function() {
                            $( $list.find( '.select2-selection__choice' ).get().reverse() ).each( function() {
                                var id     = $( this ).data( 'data' ).id;
                                var option = $select.find( 'option[value="' + id + '"]' )[0];
                                $select.prepend( option );
                            } );
                        }
                    });
                }
            });
        },

        getEnhancedSelectFormatString: function () {
            return {
                'language': {
                    errorLoading: function() {
                        // Workaround for https://github.com/select2/select2/issues/4355 instead of i18n_ajax_error.
                        return My_Shop_Front_Order.i18n_searching;
                    },
                    inputTooLong: function( args ) {
                        var overChars = args.input.length - args.maximum;
    
                        if ( 1 === overChars ) {
                            return My_Shop_Front_Order.i18n_input_too_long_1;
                        }
    
                        return My_Shop_Front_Order.i18n_input_too_long_n.replace( '%qty%', overChars );
                    },
                    inputTooShort: function( args ) {
                        var remainingChars = args.minimum - args.input.length;
    
                        if ( 1 === remainingChars ) {
                            return My_Shop_Front_Order.i18n_input_too_short_1;
                        }
    
                        return My_Shop_Front_Order.i18n_input_too_short_n.replace( '%qty%', remainingChars );
                    },
                    loadingMore: function() {
                        return My_Shop_Front_Order.i18n_load_more;
                    },
                    maximumSelected: function( args ) {
                        if ( args.maximum === 1 ) {
                            return My_Shop_Front_Order.i18n_selection_too_long_1;
                        }
    
                        return My_Shop_Front_Order.i18n_selection_too_long_n.replace( '%qty%', args.maximum );
                    },
                    noResults: function() {
                        return My_Shop_Front_Order.i18n_no_matches;
                    },
                    searching: function() {
                        return My_Shop_Front_Order.i18n_searching;
                    }
                }
            };
        },

        changeCustomerUser: function() {
			if ( ! $( '#_billing_country' ).val() ) {
				$( 'a.edit_address' ).trigger( 'click' );
				StoreFrontOrderConfig.loadBilling( true );
				StoreFrontOrderConfig.loadShipping( true );
			}

			var customerData = {
				action: 'msfc_set_customer_to_order',
				order_id: My_Shop_Front_Order.post_id,
				customer_id: $( '#customer_user' ).val(),
				security: My_Shop_Front_Order.order_item_nonce
			};

			var prod_search_for_order_box = $( '.product-serach-for-order-box' );
			prod_search_for_order_box.block();
			
			$.ajax({
				url: My_Shop_Front_Order.ajax_url,
				type: 'POST',
				data: customerData,
				success: function(response) {
					if (response.success) {
						// $( '#woocommerce-order-items' ).find( '.inside' ).empty();
						// $( '#woocommerce-order-items' ).find( '.inside' ).append( response.data.html );
						prod_search_for_order_box.unblock();
					} else {
						prod_search_for_order_box.unblock();
						window.alert( response.data.error );
					}
				},
				complete: function() {}
			});
		},

		loadBilling: function( force ) {
			if ( true === force || window.confirm( My_Shop_Front_Order.load_billing ) ) {

				// Get user ID to load data for
				var user_id = $( '#customer_user' ).val();

				// if ( ! user_id ) {
				// 	window.alert( My_Shop_Front_Order.no_customer_selected );
				// 	return false;
				// }

				var data = {
					user_id : user_id,
					action  : 'woocommerce_get_customer_details',
					security: My_Shop_Front_Order.get_customer_details_nonce
				};

				$( this ).closest( 'div.edit_address' ).block({
					message: null,
					overlayCSS: {
						background: '#fff',
						opacity: 0.6
					}
				});

				$.ajax({
					url: My_Shop_Front_Order.ajax_url,
					data: data,
					type: 'POST',
					success: function( response ) {
						if ( response && response.billing ) {
							$.each( response.billing, function( key, data ) {
								$( ':input#_billing_' + key ).val( data ).trigger( 'change' );
							});
						}
						$( 'div.edit_address' ).unblock();
					}
				});
			}
			return false;
		},

		loadShipping: function( force ) {
			if ( true === force || window.confirm( My_Shop_Front_Order.load_shipping ) ) {

				// Get user ID to load data for
				var user_id = $( '#customer_user' ).val();

				// if ( ! user_id ) {
				// 	window.alert( My_Shop_Front_Order.no_customer_selected );
				// 	return false;
				// }

				var data = {
					user_id:      user_id,
					action:       'woocommerce_get_customer_details',
					security:     My_Shop_Front_Order.get_customer_details_nonce
				};

				$( this ).closest( 'div.edit_address' ).block({
					message: null,
					overlayCSS: {
						background: '#fff',
						opacity: 0.6
					}
				});

				$.ajax({
					url: My_Shop_Front_Order.ajax_url,
					data: data,
					type: 'POST',
					success: function( response ) {
						if ( response && response.billing ) {
							$.each( response.shipping, function( key, data ) {
								$( ':input#_shipping_' + key ).val( data ).trigger( 'change' );
							});
						}
						$( 'div.edit_address' ).unblock();
					}
				});
			}
			return false;
		},

		copy_billing_to_shipping: function() {
			if ( window.confirm( My_Shop_Front_Order.copy_billing ) ) {
				$('.order_data_column :input[name^="_billing_"]').each( function() {
					var input_name = $(this).attr('name');
					input_name     = input_name.replace( '_billing_', '_shipping_' );
					$( ':input#' + input_name ).val( $(this).val() ).trigger( 'change' );
				});
			}
			return false;
		},
    }

    /**
	 * Order Notes Panel
	 */
	var NewOrderNotes = {
		init: function() {
			$( '#new_order_notes' )
				.on( 'click', 'button.add-note', this.add_order_note )
				.on( 'click', 'a.delete_note', this.delete_order_note );
		},

		add_order_note: function() {
			if ( ! $( 'textarea#add_order_note' ).val() ) {
				return;
			}

			$( '#new_order_notes' ).block({
				message: null,
				overlayCSS: {
					background: '#fff',
					opacity: 0.6
				}
			});

			var data = {
				action:    'woocommerce_add_order_note',
				post_id:   My_Shop_Front_Order.post_id,
				note:      $( 'textarea#add_order_note' ).val(),
				note_type: $( 'select#order_note_type' ).val(),
				security:  My_Shop_Front_Order.add_order_note_nonce
			};

			$.post( My_Shop_Front_Order.ajax_url, data, function( response ) {
				$( 'ul.order_notes .no-items' ).remove();
				$( 'ul.order_notes' ).prepend( response );
				$( '#new_order_notes' ).unblock();
				$( '#add_order_note' ).val( '' );
                // console.log( response );
				// window.wcTracks.recordEvent( 'order_edit_add_order_note', {
				// 	order_id: woocommerce_admin_meta_boxes.post_id,
				// 	note_type: data.note_type || 'private',
				// 	status: $( '#order_status' ).val()
				// } );
			});

			return false;
		},

		delete_order_note: function() {
			if ( window.confirm( My_Shop_Front_Order.i18n_delete_note ) ) {
				var note = $( this ).closest( 'li.note' );

				$( note ).block({
					message: null,
					overlayCSS: {
						background: '#fff',
						opacity: 0.6
					}
				});

				var data = {
					action:   'woocommerce_delete_order_note',
					note_id:  $( note ).attr( 'rel' ),
					security: My_Shop_Front_Order.delete_order_note_nonce
				};

				$.post( My_Shop_Front_Order.ajax_url, data, function() {
					$( note ).remove();

					if( $( 'ul.order_notes' ).find( 'li' ).length === 0 ) {
						$( 'ul.order_notes' ).append( '<li class="note no-items"><div class="note_content"><p>' + My_Shop_Front_Order.i18n_no_notes + '</p></div></li>' );
					}
				});
			}

			return false;
		}
	};

	/**
	 * Order Notes Panel
	 */
	var NewOrderProducts = {
		init: function() {
            this.bindEvents();
        },
        bindEvents: function() {
			$('#msf_product_search').show().selectWoo().hide();;
            this.handleProductSearch();
			this.handleDeleteSearchItem();
			this.selectProduct();
			this.deleteSearchOrderItem();
			this.addToOrder();
			this.addCoupon();
			this.removeCoupon();
			this.addFee();
			this.addShippingToOrder();
			this.createOrder();
			this.recalculateOrder();
        },

		displayResult: function( self, select2_args ) {
			select2_args = $.extend( select2_args,  StoreFrontOrderConfig.getEnhancedSelectFormatString() );

			$( self ).selectWoo( select2_args ).addClass( 'enhanced' );

			if ( $( self ).data( 'sortable' ) ) {
				var $select = $(self);
				var $list   = $( self ).next( '.select2-container' ).find( 'ul.select2-selection__rendered' );

				$list.sortable({
					placeholder : 'ui-state-highlight select2-selection__choice',
					forcePlaceholderSize: true,
					items       : 'li:not(.select2-search__field)',
					tolerance   : 'pointer',
					stop: function() {
						$( $list.find( '.select2-selection__choice' ).get().reverse() ).each( function() {
							var id     = $( this ).data( 'data' ).id;
							var option = $select.find( 'option[value="' + id + '"]' )[0];
							$select.prepend( option );
						} );
					}
				});
			// Keep multiselects ordered alphabetically if they are not sortable.
			} else if ( $( self ).prop( 'multiple' ) ) {
				$( self ).on( 'change', function(){
					var $children = $( self ).children();
					$children.sort(function(a, b){
						var atext = a.text.toLowerCase();
						var btext = b.text.toLowerCase();

						if ( atext > btext ) {
							return 1;
						}
						if ( atext < btext ) {
							return -1;
						}
						return 0;
					});
					$( self ).html( $children );
				});
			}
		},

		handleProductSearch: function() {
			// Ajax product search box
			$( ':input.wc-product-search' ).filter( ':not(.enhanced)' ).each( function() {
				var select2_args = {
					allowClear:  $( this ).data( 'allow_clear' ) ? true : false,
					placeholder: $( this ).data( 'placeholder' ),
					minimumInputLength: $( this ).data( 'minimum_input_length' ) ? $( this ).data( 'minimum_input_length' ) : '3',
					escapeMarkup: function( m ) {
						return m;
					},
					ajax: {
						url:         My_Shop_Front_Order.ajax_url,
						dataType:    'json',
						delay:       250,
						data:        function( params ) {
							return {
								term         : params.term,
								action       : $( this ).data( 'action' ) || 'woocommerce_json_search_products_and_variations',
								security     : My_Shop_Front_Order.search_products_nonce,
								exclude      : $( this ).data( 'exclude' ),
								exclude_type : $( this ).data( 'exclude_type' ),
								include      : $( this ).data( 'include' ),
								limit        : $( this ).data( 'limit' ),
								display_stock: $( this ).data( 'display_stock' )
							};
						},
						processResults: function( data ) {
							var terms = [];
							if ( data ) {
								$.each( data, function( id, text ) {
									terms.push( { id: id, text: text } );
								});
							}
							return {
								results: terms
							};
						},
						cache: true
					}
				};

				NewOrderProducts.displayResult( this, select2_args );
			});
		},

		handleDeleteSearchItem: function() {
			$(document).on('click', '.delete-search-order-item', function(e) {
				e.preventDefault();
				
				var $row = $(this).closest('tr');
				
				// Fade out and remove
				$row.fadeOut(200, function() {
					$(this).remove();
				});
			});
		},

		selectProduct: function(){
			$( '#msf_product_search' ).on( 'change', function(e) {
				var selectedValue = $(this).val();
    			var selectedText = $(this).find('option:selected').text();

				if(selectedValue){
					var row = `<td data-id="${selectedValue}">${selectedText}</td>
					<td><input type="number" step="1" min="0" max="9999" autocomplete="off" name="item_qty" placeholder="1" size="4" class="msf-form-control quantity-input" /></td>
					<td><button type="button" class="delete-btn delete-search-order-item"><span class="msf-delete-icon"></span></button></td>`;
	
					var item_table      = $( '#search-order-items table.msf-table' ),
						item_table_body = item_table.find( 'tbody' );
						item_table_body.append( '<tr>' + row + '</tr>' );
					
					// Show table
					NewOrderProducts.toggleOrderItemsTable();

					// Clear after a short delay to show the selection was made
					setTimeout(function() {
						$('#msf_product_search').val(null).trigger('change');
					}, 500);
				}
			} );
		},

		deleteSearchOrderItem: function(){
			$(document).on('click', '.delete-search-order-item', function(e) {
				e.preventDefault();
				
				// Remove the closest table row
				$(this).closest('tr').remove();

				// Show/hide table
				NewOrderProducts.toggleOrderItemsTable();
			});
		},

		toggleOrderItemsTable: function() {
			var rowCount = $('#search-order-items tbody tr').length;
			
			if (rowCount > 0) {
				$('#search-order-items').show();
			} else {
				$('#search-order-items').hide();
			}
		},
		
		addToOrder: function() {
			$(document).on('click', '#add-to-order-items', function(e) {
				e.preventDefault();
				var prod_search_for_order_box = $( '.product-serach-for-order-box' );
				prod_search_for_order_box.block();
				
				var item_table      = $( '#search-order-items table.msf-table' ),
					item_table_body = item_table.find( 'tbody' ),
					rows            = item_table_body.find( 'tr' ),
					add_items       = [];

				$( rows ).each( function() {
					var item_id = $( this ).find( 'td[data-id]' ).data('id'),
						item_qty = $( this ).find( 'input[name="item_qty"]' ).val();

					add_items.push( {
						'id' : item_id,
						'qty': item_qty ? item_qty: 1
					} );
				} );

				var data = {
					action   : 'woocommerce_add_order_item',
					order_id : My_Shop_Front_Order.post_id,
					security : My_Shop_Front_Order.order_item_nonce,
					data     : add_items
				};

				// Check if items have changed, if so pass them through so we can save them before adding a new item.
				// if ( 'true' === $( 'button.cancel-action' ).attr( 'data-reload' ) ) {
				// 	data.items = $( 'table.woocommerce_order_items :input[name], .wc-order-totals-items :input[name]' ).serialize();
				// }

				data = NewOrderProducts.filterData( 'add_items', data );

				$.ajax({
					type: 'POST',
					url: My_Shop_Front_Order.ajax_url,
					data: data,
					success: function( response ) {
						if ( response.success ) {
							// Hide table
							$('#search-order-items').hide();
							$( '#search-order-items table tbody' ).empty();
							$('.order-fee-and-shipping-box').addClass('active');

							$( '#woocommerce-order-items' ).find( '.inside' ).empty();
							$( '#woocommerce-order-items' ).find( '.inside' ).append( response.data.html );
							// console.log( response.data.html );

							// Update notes.
							if ( response.data.notes_html ) {
								$( 'ul.order_notes' ).empty();
								$( 'ul.order_notes' ).append( $( response.data.notes_html ).find( 'li' ) );
							}

							// prod_search_for_order_box.reloaded_items();
							prod_search_for_order_box.unblock();
						} else {
							prod_search_for_order_box.unblock();
							window.alert( response.data.error );
						}
					},
					complete: function() {},
					dataType: 'json'
				});
			});
		},

		addCoupon: function() {
			$(document).on('click', '.msfc-apply-coupon', function(e) {
				e.preventDefault();

				var value = $( '#coupon_code' ).val();

				var prod_search_for_order_box = $( '.product-serach-for-order-box' );
				prod_search_for_order_box.block();

				var user_id    = $( '#customer_user' ).val();
				var user_email = $( '#_billing_email' ).val();

				var data = $.extend( {}, NewOrderProducts.getTaxableAddress(), {
					action     : 'woocommerce_add_coupon_discount',
					dataType   : 'json',
					order_id   : My_Shop_Front_Order.post_id,
					security   : My_Shop_Front_Order.order_item_nonce,
					coupon     : value,
					user_id    : user_id,
					user_email : user_email
				} );

				data = NewOrderProducts.filterData( 'add_coupon', data );

				$.ajax( {
					url:     My_Shop_Front_Order.ajax_url,
					data:    data,
					type:    'POST',
					success: function( response ) {
						if ( response.success ) {
							$( '#woocommerce-order-items' ).find( '.inside' ).empty();
							$( '#woocommerce-order-items' ).find( '.inside' ).append( response.data.html );

							// Update notes.
							if ( response.data.notes_html ) {
								$( 'ul.order_notes' ).empty();
								$( 'ul.order_notes' ).append( $( response.data.notes_html ).find( 'li' ) );
							}

							// wc_meta_boxes_order_items.reloaded_items();
							prod_search_for_order_box.unblock();
						} else {
							window.alert( response.data.error );
						}
						prod_search_for_order_box.unblock();
					},
					complete: function() {}
				} );
			});
		},

		removeCoupon: function() {
			$(document).on('click', '.remove-coupon', function(e) {
				e.preventDefault();
				var $this = $( this );
				var prod_search_for_order_box = $( '.product-serach-for-order-box' );
				prod_search_for_order_box.block();

				var data = $.extend( {}, NewOrderProducts.getTaxableAddress(), {
					action : 'woocommerce_remove_order_coupon',
					dataType : 'json',
					order_id : My_Shop_Front_Order.post_id,
					security : My_Shop_Front_Order.order_item_nonce,
					coupon : $this.data( 'code' )
				} );

				data = NewOrderProducts.filterData( 'remove_coupon', data );

				$.post( My_Shop_Front_Order.ajax_url, data, function( response ) {
					if ( response.success ) {
						$( '#woocommerce-order-items' ).find( '.inside' ).empty();
						$( '#woocommerce-order-items' ).find( '.inside' ).append( response.data.html );

						// Update notes.
						if ( response.data.notes_html ) {
							$( 'ul.order_notes' ).empty();
							$( 'ul.order_notes' ).append( $( response.data.notes_html ).find( 'li' ) );
						}
						prod_search_for_order_box.unblock();
					} else {
						window.alert( response.data.error );
					}
					prod_search_for_order_box.unblock();
				});
			});
		},

		addFee: function() {
			$(document).on('click', '.msfc-add-fee', function(e) {
				e.preventDefault();

				var value = $( '#add_fee' ).val();

				var prod_search_for_order_box = $( '.product-serach-for-order-box' );
				prod_search_for_order_box.block();

				var data = $.extend( {}, NewOrderProducts.getTaxableAddress(), {
					action  : 'woocommerce_add_order_fee',
					dataType: 'json',
					order_id: My_Shop_Front_Order.post_id,
					security: My_Shop_Front_Order.order_item_nonce,
					amount  : value
				} );

				data = NewOrderProducts.filterData( 'add_fee', data );

				$.post( My_Shop_Front_Order.ajax_url, data, function( response ) {
					if ( response.success ) {
						$( '#woocommerce-order-items' ).find( '.inside' ).empty();
						$( '#woocommerce-order-items' ).find( '.inside' ).append( response.data.html );
						// wc_meta_boxes_order_items.reloaded_items();
						prod_search_for_order_box.unblock();
						// window.wcTracks.recordEvent( 'order_edit_added_fee', {
						// 	order_id: woocommerce_admin_meta_boxes.post_id,
						// 	status: $( '#order_status' ).val()
						// } );
					} else {
						window.alert( response.data.error );
					}
					// wc_meta_boxes_order.init_tiptip();
					prod_search_for_order_box.unblock();
				});
			});
		},

		addShippingToOrder: function() {
			$(document).on('click', '.msfc-add-shipping', function(e) {
				e.preventDefault();
				var shippingData = {
					action: 'msfc_add_shipping_to_order',
					order_id: My_Shop_Front_Order.post_id,
					shipping_method_title: $('.msf-form-group [name="msf_shipping_method_title"]').val(),
					shipping_cost: $('.msf-form-group [name="msf_shipping_cost"]').val(),
					shipping_method: $('.msf-form-group [name="msf_shipping_method"]').val(),
					security: My_Shop_Front_Order.order_item_nonce
				};

				var prod_search_for_order_box = $( '.product-serach-for-order-box' );
				prod_search_for_order_box.block();
				
				$.ajax({
					url: My_Shop_Front_Order.ajax_url,
					type: 'POST',
					data: shippingData,
					success: function(response) {
						if (response.success) {
							$( '#woocommerce-order-items' ).find( '.inside' ).empty();
							$( '#woocommerce-order-items' ).find( '.inside' ).append( response.data.html );
							prod_search_for_order_box.unblock();
						} else {
							prod_search_for_order_box.unblock();
							window.alert( response.data.error );
						}
					},
					complete: function() {}
				});
			});
		},

		recalculateOrder: function() {
			$(document).on('click', 'button.calculate-action', function(e) {
				var prod_search_for_order_box = $( '.product-serach-for-order-box' );
				prod_search_for_order_box.block();

				var data = $.extend( {}, NewOrderProducts.getTaxableAddress(), {
					action:   'woocommerce_calc_line_taxes',
					order_id: My_Shop_Front_Order.post_id,
					items:    $( 'table.woocommerce_order_items :input[name], .wc-order-totals-items :input[name]' ).serialize(),
					security: My_Shop_Front_Order.calc_totals_nonce
				} );

				data = NewOrderProducts.filterData( 'recalculate', data );

				$( document.body ).trigger( 'order-totals-recalculate-before', data );

				$.ajax({
					url:  My_Shop_Front_Order.ajax_url,
					data: data,
					type: 'POST',
					success: function( response ) {
						$( '#woocommerce-order-items' ).find( '.inside' ).empty();
						$( '#woocommerce-order-items' ).find( '.inside' ).append( response );
						prod_search_for_order_box.unblock();

						$( document.body ).trigger( 'order-totals-recalculate-success', response );
					},
					complete: function( response ) {
						$( document.body ).trigger( 'order-totals-recalculate-complete', response );
					}
				});
			});
			return false;
		},

		saveLineItems: function() {
			var data = {
				order_id: My_Shop_Front_Order.post_id,
				items:    $( 'table.woocommerce_order_items :input[name], .wc-order-totals-items :input[name]' ).serialize(),
				action:   'woocommerce_save_order_items',
				security: My_Shop_Front_Order.order_item_nonce
			};

			data = NewOrderProducts.filterData( 'save_line_items', data );

			var prod_search_for_order_box = $( '.product-serach-for-order-box' );
			prod_search_for_order_box.block();

			$.ajax({
				url:  My_Shop_Front_Order.ajax_url,
				data: data,
				type: 'POST',
				success: function( response ) {
					if ( response.success ) {
						$( '#woocommerce-order-items' ).find( '.inside' ).empty();
						$( '#woocommerce-order-items' ).find( '.inside' ).append( response.data.html );

						// Update notes.
						if ( response.data.notes_html ) {
							$( 'ul.order_notes' ).empty();
							$( 'ul.order_notes' ).append( $( response.data.notes_html ).find( 'li' ) );
						}

						// wc_meta_boxes_order_items.reloaded_items();
						prod_search_for_order_box.unblock();
					} else {
						prod_search_for_order_box.unblock();
						window.alert( response.data.error );
					}
				},
				complete: function() {}
			});

			$( this ).trigger( 'items_saved' );

			return false;
		},

		createOrder: function() {
			$(document).on('click', '.create-order-btn', function(e) {
				e.preventDefault();
				var data = {
					action: 'msfc_create_order',
					order_id: My_Shop_Front_Order.post_id,
					order_status: $('.msf-form-group [name="order_status"]').val(),
					order_date: $('.msf-form-group [name="order_date"]').val(),
					order_date_hour: $('.msf-form-group [name="order_date_hour"]').val(),
					order_date_minute: $('.msf-form-group [name="order_date_minute"]').val(),
					order_date_second: $('.msf-form-group [name="order_date_second"]').val(),
					order_action: $('.msf-form-group [name="order_action"]').val(),
					
					// Billing address
					_billing_first_name: $('#_billing_first_name').val(),
					_billing_last_name: $('#_billing_last_name').val(),
					_billing_company: $('#_billing_company').val(),
					_billing_address_1: $('#_billing_address_1').val(),
					_billing_address_2: $('#_billing_address_2').val(),
					_billing_city: $('#_billing_city').val(),
					_billing_postcode: $('#_billing_postcode').val(),
					_billing_country: $('#_billing_country').val(),
					_billing_state: $('#_billing_state').val(),
					_billing_email: $('#_billing_email').val(),
					_billing_phone: $('#_billing_phone').val(),

					// Shipping address
					_shipping_first_name: $('#_shipping_first_name').val(),
					_shipping_last_name: $('#_shipping_last_name').val(),
					_shipping_company: $('#_shipping_company').val(),
					_shipping_address_1: $('#_shipping_address_1').val(),
					_shipping_address_2: $('#_shipping_address_2').val(),
					_shipping_city: $('#_shipping_city').val(),
					_shipping_postcode: $('#_shipping_postcode').val(),
					_shipping_country: $('#_shipping_country').val(),
					_shipping_state: $('#_shipping_state').val(),
					_shipping_phone: $('#_shipping_phone').val(),
					customer_note: $('#customer_note').val(),

					// Payment
					_payment_method: $('#_payment_method').val(),
					_transaction_id: $('#_transaction_id').val(),
					security: My_Shop_Front_Order.order_item_nonce
				};

				var prod_search_for_order_box = $( '.product-serach-for-order-box' );
				prod_search_for_order_box.block();
				
				$.ajax({
					url: My_Shop_Front_Order.ajax_url,
					type: 'POST',
					data: data,
					success: function(response) {
						if (response.success) {
							$( '#woocommerce-order-items' ).find( '.inside' ).empty();
							$( '#woocommerce-order-items' ).find( '.inside' ).append( response.data.html );

							// Update notes.
							if ( response.data.notes_html ) {
								$( 'ul.order_notes' ).empty();
								$( 'ul.order_notes' ).append( $( response.data.notes_html ).find( 'li' ) );
							}

							prod_search_for_order_box.unblock();
						} else {
							prod_search_for_order_box.unblock();
							window.alert( response.data.error );
						}
					},
					complete: function() {}
				});
			});
		},

		filterData: function( handle, data ) {
			const filteredData = $( '#woocommerce-order-items' )
				.triggerHandler(
					`woocommerce_order_meta_box_${handle}_ajax_data`,
					[ data ]
				);

			if ( filteredData ) {
				return filteredData;
			}

			return data;
		},

		getTaxableAddress: function() {
			var country          = '';
			var state            = '';
			var postcode         = '';
			var city             = '';

			if ( 'shipping' === My_Shop_Front_Order.tax_based_on ) {
				country  = $( '#_shipping_country' ).val();
				state    = $( '#_shipping_state' ).val();
				postcode = $( '#_shipping_postcode' ).val();
				city     = $( '#_shipping_city' ).val();
			}

			if ( 'billing' === My_Shop_Front_Order.tax_based_on || ! country ) {
				country  = $( '#_billing_country' ).val();
				state    = $( '#_billing_state' ).val();
				postcode = $( '#_billing_postcode' ).val();
				city     = $( '#_billing_city' ).val();
			}

			return {
				country:  country,
				state:    state,
				postcode: postcode,
				city:     city
			};
		},
	};

	var ManageOrderAddress = {
		init: function() {
			this.bindEvents();
		},
		bindEvents: function() {
			if (
				! (
					typeof My_Shop_Front_Order === 'undefined' ||
					typeof My_Shop_Front_Order.countries === 'undefined'
				)
			) {
				/* State/Country select boxes */
				this.states = JSON.parse( My_Shop_Front_Order.countries.replace( /&quot;/g, '"' ) );
			}

			$( '.js_field-country' ).selectWoo().on( 'change', this.changeCountry );
			$( '.js_field-country' ).trigger( 'change', [ true ] );
			$( document.body ).on( 'change', 'select.js_field-state', this.changeState );
		},
		changeCountry: function( e, stickValue ) {
			// Check for stickValue before using it
			if ( typeof stickValue === 'undefined' ){
				stickValue = false;
			}

			// Prevent if we don't have the metabox data
			if ( ManageOrderAddress.states === null ){
				return;
			}

			var $this = $( this ),
				country = $this.val(),
				$state = $this.parents( 'div.customer-address-box' ).find( ':input.js_field-state' ),
				$parent = $state.parent(),
				stateValue = $state.val(),
				input_name = $state.attr( 'name' ),
				input_id = $state.attr( 'id' ),
				value = $this.data( 'woocommerce.stickState-' + country ) ? $this.data( 'woocommerce.stickState-' + country ) : stateValue,
				placeholder = $state.attr( 'placeholder' ),
				$newstate;

			if ( stickValue ){
				$this.data( 'woocommerce.stickState-' + country, value );
			}

			// Remove the previous DOM element
			$parent.show().find( '.select2-container' ).remove();

			if ( ! $.isEmptyObject( ManageOrderAddress.states[ country ] ) ) {
				var state = ManageOrderAddress.states[ country ],
					$defaultOption = $( '<option value=""></option>' )
						.text( My_Shop_Front_Order.i18n_select_state_text );

				$newstate = $( '<select></select>' )
					.prop( 'id', input_id )
					.prop( 'name', input_name )
					.prop( 'placeholder', placeholder )
					.addClass( 'js_field-state select short' )
					.append( $defaultOption );

				$.each( state, function( index ) {
					var $option = $( '<option></option>' )
						.prop( 'value', index )
						.text( state[ index ] );
					if ( index === stateValue ) {
						$option.prop( 'selected' );
					}
					$newstate.append( $option );
				} );

				$newstate.val( value );

				$state.replaceWith( $newstate );

				$newstate.show().selectWoo().hide().trigger( 'change' );
			} else {
				$newstate = $( '<input type="text" />' )
					.prop( 'id', input_id )
					.prop( 'name', input_name )
					.prop( 'placeholder', placeholder )
					.addClass( 'js_field-state msf-form-control' )
					.val( stateValue );
				$state.replaceWith( $newstate );
			}

			// Trigger custom event
			$( document.body ).trigger( 'country-change.woocommerce', [country, $( this ).closest( 'div' )] );
		},

		changeState: function() {
			// Here we will find if state value on a select has changed and stick it to the country data
			var $this = $( this ),
				state = $this.val(),
				$country = $this.parents( 'div.customer-address-box' ).find( ':input.js_field-country' ),
				country = $country.val();

			$country.data( 'woocommerce.stickState-' + country, state );
		},
	}

    StoreFrontOrderConfig.init();
    NewOrderNotes.init();
    NewOrderProducts.init();
	ManageOrderAddress.init();
})(jQuery)