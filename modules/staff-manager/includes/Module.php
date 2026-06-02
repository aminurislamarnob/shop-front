<?php
/**
 * Staff Manager module entry point.
 *
 * @package StoreSuite
 */

namespace PluginizeLab\StoreSuite\Modules\StaffManager;

use PluginizeLab\StoreSuite\Abstracts\Module as BaseModule;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Example module that demonstrates the StoreSuite module contract.
 *
 * When active, it adds a "Staff" item to the dashboard sidebar. Real CRUD,
 * templates, and assets would live under this module's own directory.
 */
class Module extends BaseModule {

	/**
	 * {@inheritDoc}
	 */
	public function get_slug() {
		return 'staff-manager';
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_name() {
		return __( 'Staff Manager', 'storesuite' );
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_description() {
		return __( 'Add staff accounts with scoped capabilities so team members can manage the store without full WordPress admin access.', 'storesuite' );
	}

	/**
	 * {@inheritDoc}
	 */
	public function boot() {
		add_filter( 'storesuite_dashboard_navigation', array( $this, 'register_menu' ), 20 );
	}

	/**
	 * Add a Staff item to the dashboard sidebar.
	 *
	 * The actual route, template, and controller would be wired up here once
	 * the feature is built out.
	 *
	 * @param array $menus Existing menu definitions.
	 * @return array
	 */
	public function register_menu( $menus ) {
		if ( ! is_array( $menus ) ) {
			return $menus;
		}

		$menus['staff'] = array(
			'title'      => __( 'Staff', 'storesuite' ),
			'url'        => home_url( '/storesuite-dashboard/staff/' ),
			'permission' => 'manage_woocommerce',
			'icon'       => '',
		);

		return $menus;
	}
}
