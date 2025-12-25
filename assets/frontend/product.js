(function($) {
    var StoreFrontProduct = {
        init: function() {
            this.bindEvents();
        },
        bindEvents: function() {
            this.manageStock();
            $(document).on('change', '#_manage_stock', this.manageStock);
        },
        manageStock: function(){            
            const product_type = $( 'select#post_type' ).val();

            if ( $( this ).is( ':checked' ) && 'external' !== product_type ) {
                $( '.show_if_stock_management' ).slideDown( 'fast' );
            } else {
                $( '.show_if_stock_management' ).slideUp( 'fast' );
            }

            if ( 'simple' === product_type ) {
                $( this ).is( ':checked' )
                    ? $( '._stock_status_field' ).slideUp( 'fast' )
                    : $( '._stock_status_field' ).slideDown( 'fast' );
            }
        },
    }
    StoreFrontProduct.init();
})(jQuery)