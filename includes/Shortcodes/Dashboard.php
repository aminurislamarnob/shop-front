<?php

namespace WeLabs\MyShopFront\Shortcodes;

use WeLabs\MyShopFront\Abstracts\MyShopFrontShortcode;

class Dashboard extends MyShopFrontShortcode {

    protected $shortcode = 'my_shop_front_dashboard';

    /**
     * Load template files
     *
     * Based on the query vars, load the appropriate template files in the my shop front dashboard.
     *
     * @param array $atts
     *
     * @return string
     */
    public function render_shortcode( $atts ) {
        global $wp;

        if ( ! function_exists( 'WC' ) ) {
            // translators: 1) wooCommerce installation url
            return sprintf( __( 'Please install <a href="%s"><strong>WooCommerce</strong></a> plugin first', 'my-shop-front' ), esc_url( 'http://wordpress.org/plugins/woocommerce/' ) );
        }

        if ( ! current_user_can( 'manage_woocommerce' ) ) {
            return __( 'You have no permission to view this page', 'my-shop-front' );
        }

        ob_start();

        // dd( $wp->query_vars );

        if ( isset( $wp->query_vars['products'] ) ) {
            // if ( ! current_user_can( 'manage_woocommerce' ) ) {
            //     dokan_get_template_part( 'global/no-permission' );
            // } else {
                msf_get_template_part( 'products/products' );
            // }

            return ob_get_clean();
        }

        do_action( 'msf_load_custom_template', $wp->query_vars );

        return ob_get_clean();
    }
}
