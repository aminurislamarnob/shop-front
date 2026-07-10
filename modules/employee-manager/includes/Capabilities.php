<?php
/**
 * Employee Manager — capability taxonomy.
 *
 * @package StoreSuite
 */

namespace PluginizeLab\StoreSuite\Modules\EmployeeManager;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The granular StoreSuite capabilities a staff role can hold.
 *
 * Each "area" maps to a real WordPress capability named `storesuite_{area}`.
 * `storesuite_current_user_can( $area )` (core) passes when the user holds
 * that cap OR `manage_woocommerce`, so admins/shop managers are unaffected and
 * staff roles opt in per area.
 *
 * The registry is filterable so other modules (CRM, Inventory) can contribute
 * their own area + label to the role editor without this module depending on
 * them.
 */
class Capabilities {

	/**
	 * Base areas shipped by StoreSuite core screens.
	 *
	 * `access_dashboard` is mandatory — without it the whole dashboard is
	 * gated off (see storesuite_redirect_if_not_manager()).
	 *
	 * @return array<string,string> Area identifier => human label.
	 */
	public static function base_areas() {
		return array(
			'access_dashboard' => __( 'Access dashboard', 'storesuite' ),
			'products'         => __( 'Manage products', 'storesuite' ),
			'orders'           => __( 'Manage orders', 'storesuite' ),
			'coupons'          => __( 'Manage coupons', 'storesuite' ),
			'taxonomies'       => __( 'Manage categories, tags, brands & attributes', 'storesuite' ),
			'analytics'        => __( 'View analytics', 'storesuite' ),
			'manage_employees' => __( 'Manage employees', 'storesuite' ),
		);
	}

	/**
	 * The full area registry, including areas other modules register.
	 *
	 * @return array<string,string> Area identifier => human label.
	 */
	public static function all_areas() {
		/**
		 * Filter the StoreSuite capability registry (area => label).
		 *
		 * Other modules add their area here so it appears in the role editor
		 * and is grantable to staff roles.
		 *
		 * @param array<string,string> $areas Area identifier => label.
		 */
		$areas = apply_filters( 'storesuite_capability_registry', self::base_areas() );

		return is_array( $areas ) ? $areas : self::base_areas();
	}

	/**
	 * The real WP capability backing an area.
	 *
	 * @param string $area Area identifier.
	 * @return string
	 */
	public static function cap_for_area( $area ) {
		return 'storesuite_' . $area;
	}

	/**
	 * Turn a list of area identifiers into a WP capability map
	 * (`[cap => true]`) suitable for add_role()/WP_Role.
	 *
	 * `access_dashboard` is always included — every staff role needs it.
	 *
	 * @param string[] $areas Area identifiers.
	 * @return array<string,bool>
	 */
	public static function caps_from_areas( array $areas ) {
		$areas   = array_values( array_unique( array_merge( array( 'access_dashboard' ), $areas ) ) );
		$valid   = array_keys( self::all_areas() );
		$granted = array();

		foreach ( $areas as $area ) {
			if ( in_array( $area, $valid, true ) ) {
				$granted[ self::cap_for_area( $area ) ] = true;
			}
		}

		return $granted;
	}

	/**
	 * The area identifiers a role currently grants, derived from its caps.
	 *
	 * @param \WP_Role|null $role Role object.
	 * @return string[]
	 */
	public static function areas_from_role( $role ) {
		if ( ! $role instanceof \WP_Role ) {
			return array();
		}

		$areas = array();
		foreach ( array_keys( self::all_areas() ) as $area ) {
			$cap = self::cap_for_area( $area );
			if ( ! empty( $role->capabilities[ $cap ] ) ) {
				$areas[] = $area;
			}
		}

		return $areas;
	}
}
