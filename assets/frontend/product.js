(function($) {
    var StoreFrontProduct = {
        init: function() {
            this.bindEvents();
            this.errorTips();
        },
        bindEvents: function() {
            var self = this;
            this.initSelect2();
            this.toggleStockFields();
            $(document).on('change', '#_manage_stock', function() {
                self.toggleStockFields();
            });
            $( document.body ).on( 'keyup', 'input[type=text][name*=_global_unique_id]', this.validateGlobalUniqueIdOnKeyUp);
            $( document.body ).on( 'change', 'input[type=text][name*=_global_unique_id]', this.validateGlobalUniqueIdOnChange);
        },
        initSelect2: function() {
            $( '.msf-select2' ).filter( ':not(.enhanced)' ).each( function() {
                var select2_args = {
                    allowClear: $( this ).data( 'allow_clear' ) ? true : false,
                    placeholder: $( this ).data( 'placeholder' ) || '',
                    minimumResultsForSearch: $( this ).data( 'minimum_results_for_search' ) || 0,
                    width: '100%'
                };

                $( this ).selectWoo( select2_args ).addClass( 'enhanced' );
            });
        },
        toggleStockFields: function(){         
            const product_type = $( 'select#post_type' ).val();
            const is_checked = $( '#_manage_stock' ).is( ':checked' );
            console.log(is_checked);
            

            if ( is_checked && 'external' !== product_type ) {
                $( '.show_if_stock_management' ).slideDown( 'fast' );
            } else {
                $( '.show_if_stock_management' ).slideUp( 'fast' );
            }

            if ( 'simple' === product_type ) {
                is_checked ? $( '._stock_status_field' ).slideUp( 'fast' ) : $( '._stock_status_field' ).slideDown( 'fast' );
            }
        },
        validateGlobalUniqueIdOnKeyUp: function() {
            var global_unique_id = $( this ).val();

            if ( /[^0-9\-]/.test( global_unique_id ) ) {
                $( document.body ).triggerHandler( 'wc_add_error_tip', [
                    $( this ),
                    'i18n_global_unique_id_error',
                ] );
            } else {
                $( document.body ).triggerHandler(
                    'wc_remove_error_tip',
                    [ $( this ), 'i18n_global_unique_id_error' ]
                );
            }
        },
        validateGlobalUniqueIdOnChange: function() {
            var global_unique_id = $( this ).val();
            $( this ).val(
                global_unique_id
                    .replace( /[^0-9\-]/g, '' )
                    .replace( /^-+|-+$/g, '' )
            );

            $( document.body ).triggerHandler(
                'wc_remove_error_tip',
                [ $( this ), 'i18n_global_unique_id_error' ]
            );
        },
        errorTips: function() {
            $( document.body )
			.on( 'wc_add_error_tip', function ( e, element, error_type ) {
				var offset = element.position();

				if ( element.parent().find( '.wc_error_tip' ).length === 0 ) {
					element.after(
						'<div class="wc_error_tip ' +
							error_type +
							'">' +
							My_Shop_Front_Product[ error_type ] +
							'</div>'
					);
					element
						.parent()
						.find( '.wc_error_tip' )
						.css(
							'left',
							offset.left +
								element.width() -
								element.width() / 2 -
								$( '.wc_error_tip' ).width() / 2
						)
						.css( 'top', offset.top + element.height() )
						.fadeIn( '100' );
				}
			} )

			.on( 'wc_remove_error_tip', function ( e, element, error_type ) {
				element
					.parent()
					.find( '.wc_error_tip.' + error_type )
					.fadeOut( '100', function () {
						$( this ).remove();
					} );
			} )
        }
    }
    StoreFrontProduct.init();
})(jQuery)