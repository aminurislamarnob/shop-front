<?php

namespace PluginizeLab\StoreSuite\Analytics;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class RestPermissions {
	public function register_hooks(): void {
		add_filter( 'woocommerce_rest_check_permissions', [ $this, 'grant_read_access' ], 20, 4 );
	}

	/**
	 * Grant read access to WC analytics REST endpoints for users with manage_woocommerce.
	 *
	 * @param bool   $permission Current permission status.
	 * @param string $context    Request context (read, write, etc.).
	 * @param int    $obj_id     Object ID.
	 * @param string $obj        Object type.
	 * @return bool
	 */
	public function grant_read_access( $permission, $context, $obj_id, $obj ): bool {
		if ( $permission || 'read' !== $context ) {
			return $permission;
		}

		// Routes used by /wc-analytics/* and the async-filter dropdowns
		// (taxes/coupons/customers/products/variations/orders/taxonomy
		// terms). WooCommerce already gates these on manage_woocommerce
		// upstream — we widen for shop_managers reaching the frontend
		// dashboard.
		$allowed = apply_filters(
			'storesuite_analytics_rest_read_objects',
			[
				'reports',
				'settings',
				'product_cat',
				'product_tag',
				'product_brand',
				'taxes',
				'coupons',
				'customers',
				'products',
				'variations',
				'orders',
				'order',
			]
		);

		if ( in_array( $obj, $allowed, true ) ) {
			$permission = current_user_can( 'manage_woocommerce' );
		}
		return $permission;
	}
}
