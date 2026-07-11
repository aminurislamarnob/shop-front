<?php
/**
 * Employee Manager — role definitions & lifecycle.
 *
 * @package StoreSuite
 */

namespace PluginizeLab\StoreSuite\Modules\EmployeeManager;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Creates and manages the staff roles.
 *
 * Predefined roles ship with fixed area sets. Custom roles are real WP roles
 * (slug `storesuite_custom_{key}`) whose caps the admin picks in the role
 * editor. Both flavours are prefixed `storesuite_` so they are easy to
 * enumerate and remove on uninstall.
 *
 * Roles are additive (they only grant `storesuite_*` caps + `read`), so a
 * staff user gets exactly the dashboard areas their role allows and nothing in
 * wp-admin.
 */
class Roles {

	const ROLE_PREFIX        = 'storesuite_';
	const CUSTOM_ROLE_PREFIX = 'storesuite_custom_';

	/**
	 * Predefined roles: role slug => [ label, areas ].
	 *
	 * @return array<string,array{label:string,areas:string[]}>
	 */
	public static function predefined() {
		return array(
			'storesuite_shop_manager'   => array(
				'label' => __( 'Shop Manager', 'storesuite' ),
				'areas' => array( 'access_dashboard', 'products', 'orders', 'coupons', 'taxonomies', 'analytics', 'manage_employees' ),
			),
			'storesuite_order_fulfiller' => array(
				'label' => __( 'Order Fulfiller', 'storesuite' ),
				'areas' => array( 'access_dashboard', 'orders' ),
			),
			'storesuite_product_editor'  => array(
				'label' => __( 'Product Editor', 'storesuite' ),
				'areas' => array( 'access_dashboard', 'products', 'taxonomies' ),
			),
			'storesuite_read_only'       => array(
				'label' => __( 'Read Only', 'storesuite' ),
				'areas' => array( 'access_dashboard', 'analytics' ),
			),
		);
	}

	/**
	 * Create the predefined roles. Called on module activation. Existing roles
	 * are refreshed so a plugin update can adjust their cap sets.
	 *
	 * @return void
	 */
	public static function install() {
		foreach ( self::predefined() as $slug => $def ) {
			self::upsert_role( $slug, $def['label'], $def['areas'] );
		}
	}

	/**
	 * Create or update a role with the caps for the given areas.
	 *
	 * @param string   $slug  Role slug.
	 * @param string   $label Display name.
	 * @param string[] $areas Area identifiers.
	 * @return void
	 */
	public static function upsert_role( $slug, $label, array $areas ) {
		$caps = array_merge(
			array( 'read' => true ),
			Capabilities::caps_from_areas( $areas )
		);

		// remove_role + add_role guarantees the cap set matches exactly (WP has
		// no "replace caps" API; add_role is a no-op if the role exists).
		remove_role( $slug );
		add_role( $slug, $label, $caps );
	}

	/**
	 * All StoreSuite-managed roles, keyed by slug, as
	 * [ slug => [ label, areas, is_custom ] ].
	 *
	 * @return array<string,array>
	 */
	public static function all() {
		$roles  = array();
		$wp     = wp_roles();
		$predef = self::predefined();

		foreach ( $wp->roles as $slug => $data ) {
			if ( 0 !== strpos( $slug, self::ROLE_PREFIX ) ) {
				continue;
			}

			$role  = get_role( $slug );
			$roles[ $slug ] = array(
				'slug'      => $slug,
				'label'     => $data['name'],
				'areas'     => Capabilities::areas_from_role( $role ),
				'is_custom' => 0 === strpos( $slug, self::CUSTOM_ROLE_PREFIX ),
				'is_predefined' => isset( $predef[ $slug ] ),
			);
		}

		return $roles;
	}

	/**
	 * Create/update a custom role from a label + area list.
	 *
	 * @param string   $label Display name.
	 * @param string[] $areas Area identifiers.
	 * @param string   $slug  Optional existing slug (for edits).
	 * @return string|\WP_Error The role slug, or an error.
	 */
	public static function save_custom_role( $label, array $areas, $slug = '' ) {
		$label = sanitize_text_field( $label );
		if ( '' === $label ) {
			return new \WP_Error( 'storesuite_invalid_role', __( 'A role name is required.', 'storesuite' ) );
		}

		if ( '' === $slug ) {
			$key  = sanitize_key( str_replace( '-', '_', sanitize_title( $label ) ) );
			$slug = self::CUSTOM_ROLE_PREFIX . $key;
		}

		// Guard: never let a custom-role save overwrite a predefined role.
		if ( isset( self::predefined()[ $slug ] ) ) {
			return new \WP_Error( 'storesuite_reserved_role', __( 'That role name is reserved.', 'storesuite' ) );
		}

		self::upsert_role( $slug, $label, $areas );

		return $slug;
	}

	/**
	 * Delete a custom role, reassigning its users to a fallback.
	 *
	 * @param string $slug     Role slug (must be a custom role).
	 * @param string $fallback Role to move affected users to (default customer).
	 * @return bool|\WP_Error
	 */
	public static function delete_custom_role( $slug, $fallback = 'customer' ) {
		if ( 0 !== strpos( $slug, self::CUSTOM_ROLE_PREFIX ) ) {
			return new \WP_Error( 'storesuite_not_custom', __( 'Only custom roles can be deleted.', 'storesuite' ) );
		}

		self::reassign_users( $slug, $fallback );
		remove_role( $slug );

		return true;
	}

	/**
	 * Move every user holding $from to $to.
	 *
	 * @param string $from Source role slug.
	 * @param string $to   Destination role slug.
	 * @return void
	 */
	public static function reassign_users( $from, $to ) {
		$users = get_users( array( 'role' => $from ) );
		foreach ( $users as $user ) {
			$user->remove_role( $from );
			$user->add_role( $to );
		}
	}

	/**
	 * Remove every StoreSuite-managed role, reassigning holders to customer.
	 * Called on module uninstall only.
	 *
	 * @return void
	 */
	public static function remove_all() {
		foreach ( array_keys( self::all() ) as $slug ) {
			self::reassign_users( $slug, 'customer' );
			remove_role( $slug );
		}
	}
}
