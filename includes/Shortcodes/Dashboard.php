<?php

namespace PluginizeLab\StoreSuite\Shortcodes;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use PluginizeLab\StoreSuite\Abstracts\MyStoreSuiteShortcode;

class Dashboard extends MyStoreSuiteShortcode {

	protected $shortcode = 'storesuite_dashboard';

	/**
	 * Load template files
	 *
	 * Based on the query vars, load the appropriate template files in the store suite dashboard.
	 *
	 * @param array $atts
	 *
	 * @return string
	 */
	public function render_shortcode( $atts ) {
		global $wp;

		if ( ! function_exists( 'WC' ) ) {
			// translators: 1) wooCommerce installation url
			return wp_kses_post(
				sprintf(
					/* translators: %s: WooCommerce installation URL */
					__( 'Please install <a href="%s"><strong>WooCommerce</strong></a> plugin first', 'storesuite' ),
					esc_url( 'http://wordpress.org/plugins/woocommerce/' )
				)
			);
		}

		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			return esc_html__( 'You have no permission to view this page', 'storesuite' );
		}

		ob_start();

		if ( isset( $wp->query_vars['page'] ) ) {
			storesuite_get_template_part( 'dashboard' );
			return ob_get_clean();
		}

		// Analytics endpoint must be checked before any query-string-based vars (e.g. products, orders)
		// because filter params like ?products=61 also set those query vars, causing the wrong template to load.
		if ( storesuite_is_endpoint_url( 'analytics' ) ) {
			do_action( 'storesuite_load_custom_template', $wp->query_vars );
			return ob_get_clean();
		}

		if ( isset( $wp->query_vars['products'] ) ) {
			storesuite_get_template_part( 'products/products' );
			return ob_get_clean();
		}

		if ( isset( $wp->query_vars['add-new-product'] ) ) {
			if ( ! current_user_can( 'manage_woocommerce' ) ) {
                storesuite_get_template_part( 'global/no-permission' );
            } else {
                do_action( 'storesuite_load_new_product_template', $wp->query_vars );
            }
			return ob_get_clean();
		}

		if ( isset( $wp->query_vars['edit-product'] ) ) {
			if ( ! current_user_can( 'manage_woocommerce' ) ) {
                storesuite_get_template_part( 'global/no-permission' );
            } else {
                do_action( 'storesuite_load_edit_product_template', $wp->query_vars );
            }
			return ob_get_clean();
		}

		if ( isset( $wp->query_vars['orders'] ) ) {
			storesuite_get_template_part( 'orders/orders' );
			return ob_get_clean();
		}

		if ( isset( $wp->query_vars['add-new-order'] ) ) {
			storesuite_get_template_part( 'orders/add-new-order' );
			return ob_get_clean();
		}

		if ( isset( $wp->query_vars['edit-order'] ) ) {
			storesuite_get_template_part( 'orders/edit-order' );
			return ob_get_clean();
		}

		if ( isset( $wp->query_vars['order-details'] ) ) {
			storesuite_get_template_part( 'orders/order-details' );
			return ob_get_clean();
		}

		if ( isset( $wp->query_vars['categories'] ) ) {
			storesuite_get_template_part( 'categories/categories' );
			return ob_get_clean();
		}

		if ( isset( $wp->query_vars['add-new-category'] ) ) {
			storesuite_get_template_part( 'categories/add-new-category' );
			return ob_get_clean();
		}

		if ( isset( $wp->query_vars['edit-category'] ) ) {
			storesuite_get_template_part( 'categories/edit-category' );
			return ob_get_clean();
		}

		if ( isset( $wp->query_vars['tags'] ) ) {
			storesuite_get_template_part( 'tags/tags' );
			return ob_get_clean();
		}

		if ( isset( $wp->query_vars['add-new-tag'] ) ) {
			storesuite_get_template_part( 'tags/add-new-tag' );
			return ob_get_clean();
		}

		if ( isset( $wp->query_vars['edit-tag'] ) ) {
			storesuite_get_template_part( 'tags/edit-tag' );
			return ob_get_clean();
		}

		if ( isset( $wp->query_vars['brands'] ) ) {
			storesuite_get_template_part( 'brands/brands' );
			return ob_get_clean();
		}

		if ( isset( $wp->query_vars['add-new-brand'] ) ) {
			storesuite_get_template_part( 'brands/add-new-brand' );
			return ob_get_clean();
		}

		if ( isset( $wp->query_vars['edit-brand'] ) ) {
			storesuite_get_template_part( 'brands/edit-brand' );
			return ob_get_clean();
		}

		if ( isset( $wp->query_vars['attributes'] ) ) {
			storesuite_get_template_part( 'attributes/attributes' );
			return ob_get_clean();
		}

		if ( isset( $wp->query_vars['add-new-attribute'] ) ) {
			storesuite_get_template_part( 'attributes/add-new-attribute' );
			return ob_get_clean();
		}

		if ( isset( $wp->query_vars['edit-attribute'] ) ) {
			storesuite_get_template_part( 'attributes/edit-attribute' );
			return ob_get_clean();
		}

		if ( isset( $wp->query_vars['attribute-terms'] ) ) {
			storesuite_get_template_part( 'attributes/attribute-terms' );
			return ob_get_clean();
		}

		if ( isset( $wp->query_vars['coupons'] ) ) {
			do_action( 'storesuite_load_coupons_template', $wp->query_vars );
			return ob_get_clean();
		}

		if ( isset( $wp->query_vars['add-new-coupon'] ) ) {
			$template_args = array(
				'query_vars' => $wp->query_vars,
			);
			storesuite_get_template_part( 'coupons/add-new-coupon', '', $template_args );
			return ob_get_clean();
		}

		if ( isset( $wp->query_vars['edit-coupon'] ) ) {
			$template_args = array(
				'query_vars' => $wp->query_vars,
			);
			storesuite_get_template_part( 'coupons/edit-coupon', '', $template_args );
			return ob_get_clean();
		}

		if ( isset( $wp->query_vars['edit-account-details'] ) ) {
			storesuite_get_template_part( 'account/edit-account' );
			return ob_get_clean();
		}

		do_action( 'storesuite_load_custom_template', $wp->query_vars );

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

		if ( isset( $wp->query_vars['pagename'] ) && ( $wp->query_vars['pagename'] === 'storesuite-dashboard' ) ) {
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
