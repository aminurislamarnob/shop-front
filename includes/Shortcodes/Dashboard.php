<?php

namespace PluginizeLab\ShopFront\Shortcodes;

use PluginizeLab\ShopFront\Abstracts\MyShopFrontShortcode;

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
			return sprintf( __( 'Please install <a href="%s"><strong>WooCommerce</strong></a> plugin first', 'shop-front' ), esc_url( 'http://wordpress.org/plugins/woocommerce/' ) );
		}

		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			return __( 'You have no permission to view this page', 'shop-front' );
		}

		ob_start();

		// dd( $wp->query_vars );

		if ( isset( $wp->query_vars['page'] ) ) {
			msf_get_template_part( 'dashboard' );
			return ob_get_clean();
		}

		if ( isset( $wp->query_vars['products'] ) ) {
			msf_get_template_part( 'products/products' );
			return ob_get_clean();
		}

		if ( isset( $wp->query_vars['add-new-product'] ) ) {
			msf_get_template_part( 'products/add-new-product' );
			return ob_get_clean();
		}

		if ( isset( $wp->query_vars['edit-product'] ) ) {
			msf_get_template_part( 'products/edit-product' );
			return ob_get_clean();
		}

		if ( isset( $wp->query_vars['orders'] ) ) {
			msf_get_template_part( 'orders/orders' );
			return ob_get_clean();
		}

		if ( isset( $wp->query_vars['order-details'] ) ) {
			msf_get_template_part( 'orders/order-details' );
			return ob_get_clean();
		}

		if ( isset( $wp->query_vars['categories'] ) ) {
			msf_get_template_part( 'categories/categories' );
			return ob_get_clean();
		}

		if ( isset( $wp->query_vars['add-new-category'] ) ) {
			msf_get_template_part( 'categories/add-new-category' );
			return ob_get_clean();
		}

		if ( isset( $wp->query_vars['edit-category'] ) ) {
			msf_get_template_part( 'categories/edit-category' );
			return ob_get_clean();
		}

		if ( $this->is_query_var_exists( 'tags', 'add-new-tag' ) ) {
			msf_get_template_part( 'tags/add-new-tag' );
			return ob_get_clean();
		}

		if ( $this->is_query_var_exists( 'tags', 'edit-tag' ) ) {
			msf_get_template_part( 'tags/edit-tag' );
			return ob_get_clean();
		}

		if ( isset( $wp->query_vars['tags'] ) ) {
			msf_get_template_part( 'tags/tags' );
			return ob_get_clean();
		}

		do_action( 'msf_load_custom_template', $wp->query_vars );

		return ob_get_clean();
	}

	/**
	 * Check is the query var exists
	 *
	 * @param string $query_var
	 * @param string $query_var_value
	 * @return boolean
	 */
	public function is_query_var_exists( $query_var, $query_var_value ) {
		global $wp;

		if ( isset( $wp->query_vars['pagename'] ) && ( $wp->query_vars['pagename'] === 'my-shop-dashboard' ) ) {
			if ( isset( $wp->query_vars[ $query_var ] ) ) {
				$query_var_parts = explode( '/', $wp->query_vars[ $query_var ] );

				if ( isset( $query_var_parts[0] ) && $query_var_parts[0] === $query_var_value ) {
					return true;
				}
			}
		}

		return false;
	}
}
