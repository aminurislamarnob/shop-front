(function($) {
    var StoreFrontOrderConfig = {
        init: function() {
            this.bindEvents();
        },
        bindEvents: function() {
            this.handleSelect2(); // Handle select2
            this.handleSelect2Customer(); // Handle select2 customer
            $( '#customer_user' ).on( 'change', this.changeCustomerUser );
        },

        handleSelect2: function() {
            $('#customer_user').select2();
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
		},

		loadBilling: function( force ) {
			if ( true === force || window.confirm( My_Shop_Front_Order.load_billing ) ) {

				// Get user ID to load data for
				var user_id = $( '#customer_user' ).val();
                console.log( user_id );

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
                        console.log( response );
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
                        console.log( response );
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
		}
    }

    /**
	 * Order Notes Panel
	 */
	var NewOrderNotes = {
		init: function() {
			$( '#new_order_notes' )
				.on( 'click', 'button.add-note', this.add_order_note )
				.on( 'click', 'a.delete-note', this.delete_order_note );

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
                console.log( response );
				// window.wcTracks.recordEvent( 'order_edit_add_order_note', {
				// 	order_id: woocommerce_admin_meta_boxes.post_id,
				// 	note_type: data.note_type || 'private',
				// 	status: $( '#order_status' ).val()
				// } );
			});

			return false;
		},

		delete_order_note: function() {
			if ( window.confirm( woocommerce_admin_meta_boxes.i18n_delete_note ) ) {
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
				});
			}

			return false;
		}
	};
    StoreFrontOrderConfig.init();
    NewOrderNotes.init();
})(jQuery)