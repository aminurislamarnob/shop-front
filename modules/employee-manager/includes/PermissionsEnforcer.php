<?php
/**
 * Employee Manager — permission enforcement.
 *
 * @package StoreSuite
 */

namespace PluginizeLab\StoreSuite\Modules\EmployeeManager;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Bridges StoreSuite's granular `storesuite_{area}` capabilities to the WordPress
 * / WooCommerce capabilities the core dashboard actually checks, without editing
 * any core file.
 *
 * Three responsibilities:
 *  1. Rewrite the sidebar menu items' `permission` keys to the granular caps so
 *     each role sees only its areas.
 *  2. Block suspended employees from the dashboard.
 *  3. Grant the WooCommerce caps a staff role needs, tightly scoped to a
 *     dashboard page render or a single allowed `storesuite_*` AJAX action.
 */
class PermissionsEnforcer {

	/**
	 * Register the enforcement hooks. Called from Module::boot().
	 *
	 * @return void
	 */
	public function register() {
		add_filter( 'storesuite_dashboard_menus', array( $this, 'rewrite_menu_permissions' ), 99 );
		add_filter( 'storesuite_user_can', array( $this, 'block_suspended' ), 10, 3 );
		add_filter( 'user_has_cap', array( $this, 'grant_scoped_caps' ), 10, 3 );
		add_filter( 'woocommerce_rest_check_permissions', array( $this, 'grant_analytics_rest_read' ), 21, 2 );
	}

	/**
	 * Let staff holding the analytics area read WooCommerce REST data, which the
	 * Analytics React app needs. Scoped to the read context only — write
	 * operations still require the matching area's caps. Mirrors the intent of
	 * Analytics\RestPermissions, which grants the same for manage_woocommerce.
	 *
	 * @param bool   $permission Current decision.
	 * @param string $context    Request context (read|create|edit|delete|batch).
	 * @return bool
	 */
	public function grant_analytics_rest_read( $permission, $context ) {
		if ( $permission || 'read' !== $context ) {
			return $permission;
		}

		// Grant read when the current user holds the granular analytics cap and
		// is not a suspended employee.
		if ( current_user_can( Capabilities::cap_for_area( 'analytics' ) ) && ! self::is_suspended( get_current_user_id() ) ) {
			return true;
		}

		return $permission;
	}

	/**
	 * Map each core menu item (and submenu item) to the granular capability for
	 * its area, so DashboardMenu's per-item permission check hides areas a role
	 * can't reach.
	 *
	 * @param array $menus Menu definitions.
	 * @return array
	 */
	public function rewrite_menu_permissions( $menus ) {
		if ( ! is_array( $menus ) ) {
			return $menus;
		}

		$top = array(
			'dashboard'            => 'access_dashboard',
			'products'             => 'products',
			'orders'               => 'orders',
			'coupons'              => 'coupons',
			'analytics'            => 'analytics',
			'edit-account-details' => 'access_dashboard',
			'home'                 => 'access_dashboard',
			'logout'               => 'access_dashboard',
		);

		$submenu = array(
			'products'        => 'products',
			'add-new-product' => 'products',
			'categories'      => 'taxonomies',
			'brands'          => 'taxonomies',
			'tags'            => 'taxonomies',
			'attributes'      => 'taxonomies',
			'orders'          => 'orders',
			'add-new-order'   => 'orders',
			'coupons'         => 'coupons',
			'add-new-coupon'  => 'coupons',
		);

		foreach ( $menus as $key => &$item ) {
			if ( isset( $top[ $key ] ) && isset( $item['permission'] ) ) {
				$item['permission'] = Capabilities::cap_for_area( $top[ $key ] );
			}

			if ( ! empty( $item['submenu'] ) && is_array( $item['submenu'] ) ) {
				foreach ( $item['submenu'] as $sub_key => &$sub_item ) {
					if ( 'analytics' === $key && isset( $sub_item['permission'] ) ) {
						$sub_item['permission'] = Capabilities::cap_for_area( 'analytics' );
					} elseif ( isset( $submenu[ $sub_key ], $sub_item['permission'] ) ) {
						$sub_item['permission'] = Capabilities::cap_for_area( $submenu[ $sub_key ] );
					}
				}
				unset( $sub_item );
			}
		}
		unset( $item );

		return $menus;
	}

	/**
	 * Deny every StoreSuite area to a suspended employee.
	 *
	 * @param bool   $allowed Current decision.
	 * @param string $area    Area identifier.
	 * @param int    $user_id User ID.
	 * @return bool
	 */
	public function block_suspended( $allowed, $area, $user_id ) {
		unset( $area );
		if ( $allowed && $user_id && self::is_suspended( $user_id ) ) {
			return false;
		}
		return $allowed;
	}

	/**
	 * Grant the WooCommerce caps a staff role needs — scoped to a dashboard
	 * render or a single allowed AJAX action, never globally.
	 *
	 * @param array    $allcaps The user's current capabilities.
	 * @param string[] $caps    The primitive caps being checked (unused).
	 * @param array    $args    [ requested_cap, user_id, ... ] (unused).
	 * @return array
	 */
	public function grant_scoped_caps( $allcaps, $caps, $args ) {
		unset( $caps, $args );

		// Only staff accounts get grants; admins/shop managers already pass.
		if ( empty( $allcaps['storesuite_access_dashboard'] ) ) {
			return $allcaps;
		}

		$user_id = get_current_user_id();
		if ( ! $user_id || self::is_suspended( $user_id ) ) {
			return $allcaps;
		}

		$ajax_area    = $this->current_ajax_area();
		$on_dashboard = function_exists( 'storesuite_is_dashboard_page' ) && storesuite_is_dashboard_page();

		if ( null === $ajax_area && ! $on_dashboard ) {
			return $allcaps;
		}

		$held = $this->held_areas( $allcaps );

		// On any dashboard/AJAX request, grant the granular WC caps for every
		// area the role holds (needed for reads and post-type meta caps).
		foreach ( $held as $area ) {
			foreach ( self::wc_caps_for_area( $area ) as $cap ) {
				$allcaps[ $cap ] = true;
			}
		}

		// Many core AJAX handlers gate on manage_woocommerce. Grant it ONLY for
		// a storesuite_* action whose area the role holds, so the grant is
		// confined to that single request/handler.
		if ( null !== $ajax_area && in_array( $ajax_area, $held, true ) ) {
			$allcaps['manage_woocommerce'] = true;
		}

		return $allcaps;
	}

	/**
	 * The area a current storesuite_* AJAX action maps to, or null.
	 *
	 * @return string|null
	 */
	private function current_ajax_area() {
		if ( ! wp_doing_ajax() ) {
			return null;
		}

		// Reading the action name for cap-mapping needs no nonce; each handler
		// verifies its own nonce before doing work.
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$action = isset( $_REQUEST['action'] ) ? sanitize_key( wp_unslash( $_REQUEST['action'] ) ) : '';
		if ( '' === $action || 0 !== strpos( $action, 'storesuite_' ) ) {
			return null;
		}

		$map = self::ajax_capability_map();
		return $map[ $action ] ?? null;
	}

	/**
	 * Areas the user holds, derived from their granular caps.
	 *
	 * @param array $allcaps Capability map.
	 * @return string[]
	 */
	private function held_areas( $allcaps ) {
		$held = array();
		foreach ( array_keys( Capabilities::all_areas() ) as $area ) {
			if ( ! empty( $allcaps[ Capabilities::cap_for_area( $area ) ] ) ) {
				$held[] = $area;
			}
		}
		return $held;
	}

	/**
	 * Is this user a suspended employee?
	 *
	 * @param int $user_id User ID.
	 * @return bool
	 */
	public static function is_suspended( $user_id ) {
		$record = EmployeeManager::get_record( (int) $user_id );
		return $record && 'suspended' === $record['status'];
	}

	/**
	 * The WooCommerce primitive caps to grant for a given area.
	 *
	 * @param string $area Area identifier.
	 * @return string[]
	 */
	public static function wc_caps_for_area( $area ) {
		$map = array(
			'products'   => array(
				'edit_products',
				'edit_published_products',
				'edit_others_products',
				'edit_private_products',
				'read_private_products',
				'publish_products',
				'delete_products',
				'delete_published_products',
				'delete_others_products',
				'manage_product_terms',
				'assign_product_terms',
				'upload_files',
			),
			'orders'     => array(
				'edit_shop_orders',
				'edit_others_shop_orders',
				'edit_published_shop_orders',
				'read_private_shop_orders',
				'publish_shop_orders',
				'delete_shop_orders',
			),
			'coupons'    => array(
				'edit_shop_coupons',
				'edit_others_shop_coupons',
				'edit_published_shop_coupons',
				'read_private_shop_coupons',
				'publish_shop_coupons',
				'delete_shop_coupons',
			),
			'taxonomies' => array(
				'manage_product_terms',
				'edit_product_terms',
				'delete_product_terms',
				'assign_product_terms',
				'edit_products',
			),
		);

		/**
		 * Filter the WooCommerce caps granted for a StoreSuite area.
		 *
		 * @param string[] $caps Capability slugs.
		 * @param string   $area Area identifier.
		 */
		return apply_filters( 'storesuite_wc_caps_for_area', $map[ $area ] ?? array(), $area );
	}

	/**
	 * Default map of storesuite_* AJAX action => area. Filterable so other
	 * modules register their own actions.
	 *
	 * @return array<string,string>
	 */
	public static function ajax_capability_map() {
		$map = array(
			// Products.
			'storesuite_add_product_action'         => 'products',
			'storesuite_edit_product_action'        => 'products',
			'storesuite_delete_product'             => 'products',
			'storesuite_product_quick_edit'         => 'products',
			'storesuite_get_product_quick_edit_form' => 'products',
			'storesuite_bulk_edit_products'         => 'products',
			'storesuite_bulk_trash_products'        => 'products',
			'storesuite_product_export'             => 'products',
			'storesuite_download_product_csv'       => 'products',
			'storesuite_load_variations'            => 'products',
			'storesuite_generate_variations'        => 'products',
			'storesuite_save_variations'            => 'products',
			'storesuite_add_variation'              => 'products',
			'storesuite_remove_variation'           => 'products',
			'storesuite_bulk_edit_variations'       => 'products',
			'storesuite_save_default_attributes'    => 'products',
			'storesuite_generate_product_field'     => 'products',
			'storesuite_generate_product_bundle'    => 'products',
			'storesuite_generate_product_image'     => 'products',
			'storesuite_insert_product_image'       => 'products',
			// Orders.
			'storesuite_add_order_note'             => 'orders',
			'storesuite_delete_order_note'          => 'orders',
			'storesuite_add_shipping_to_order'      => 'orders',
			'storesuite_create_order'               => 'orders',
			// Coupons.
			'storesuite_add_coupon'                 => 'coupons',
			'storesuite_edit_coupon'                => 'coupons',
			'storesuite_delete_coupon'              => 'coupons',
			'storesuite_bulk_edit_coupons'          => 'coupons',
			'storesuite_bulk_trash_coupons'         => 'coupons',
			// Taxonomies.
			'storesuite_add_product_category'       => 'taxonomies',
			'storesuite_edit_product_category'      => 'taxonomies',
			'storesuite_delete_product_category'    => 'taxonomies',
			'storesuite_add_product_brand'          => 'taxonomies',
			'storesuite_edit_product_brand'         => 'taxonomies',
			'storesuite_delete_product_brand'       => 'taxonomies',
			'storesuite_add_product_tag'            => 'taxonomies',
			'storesuite_edit_product_tag'           => 'taxonomies',
			'storesuite_delete_product_tag'         => 'taxonomies',
			'storesuite_add_product_attribute'      => 'taxonomies',
			'storesuite_edit_product_attribute'     => 'taxonomies',
			'storesuite_delete_product_attribute'   => 'taxonomies',
			'storesuite_add_attribute_term'         => 'taxonomies',
			'storesuite_edit_attribute_term'        => 'taxonomies',
			'storesuite_delete_attribute_term'      => 'taxonomies',
			'storesuite_bulk_delete_terms'          => 'taxonomies',
			'storesuite_get_list_quick_edit_form'   => 'taxonomies',
			'storesuite_save_list_quick_edit'       => 'taxonomies',
		);

		/**
		 * Filter the AJAX action => area map used to scope capability grants.
		 *
		 * @param array<string,string> $map Action => area.
		 */
		return apply_filters( 'storesuite_ajax_capability_map', $map );
	}
}
