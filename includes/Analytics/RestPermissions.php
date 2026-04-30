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
		if ( ! $permission
			&& 'read' === $context
			&& in_array( $obj, [ 'reports', 'settings', 'product_cat' ], true )
		) {
			$permission = current_user_can( 'manage_woocommerce' );
		}
		return $permission;
	}
}
