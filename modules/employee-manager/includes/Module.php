<?php
/**
 * Employee Manager module entry point.
 *
 * @package StoreSuite
 */

namespace PluginizeLab\StoreSuite\Modules\EmployeeManager;

use PluginizeLab\StoreSuite\Abstracts\Module as BaseModule;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Role-based staff access module.
 *
 * On activation it creates the employees + activity-log tables and the four
 * predefined staff roles. While active it enforces per-area permissions across
 * the dashboard, exposes an employee-management screen, a frontend login, and a
 * per-employee activity log.
 */
class Module extends BaseModule {

	const ENDPOINT = 'employees';

	/**
	 * {@inheritDoc}
	 */
	public function get_slug() {
		return 'employee-manager';
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_name() {
		return __( 'Employee Manager', 'storesuite' );
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_description() {
		return __( 'Add staff accounts with predefined or custom roles and granular permissions, a frontend-only login, and a per-employee activity log.', 'storesuite' );
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_requires() {
		return array( 'woocommerce/woocommerce.php' );
	}

	/**
	 * One-shot setup: schema + roles. The Manager flags a rewrite flush after
	 * this returns so the `/employees` endpoint is routable next request.
	 *
	 * @return void
	 */
	public function activate() {
		Installer::install();
		Roles::install();
	}

	/**
	 * Boot hooks for an active module. Runs on `storesuite_loaded`.
	 *
	 * @return void
	 */
	public function boot() {
		Installer::maybe_upgrade();

		add_action( 'init', array( $this, 'register_endpoint' ) );
		add_filter( 'storesuite_query_var_filter', array( $this, 'register_query_var' ) );
		add_filter( 'storesuite_dashboard_menus', array( $this, 'register_menu' ), 20 );
		add_action( 'storesuite_load_custom_template', array( $this, 'load_template' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ) );

		( new AjaxController() )->register();
	}

	/**
	 * Render the Team screens when the `/employees/` endpoint is requested.
	 *
	 * A `view` query arg switches between the sub-screens (employees list,
	 * roles editor, activity log) — all under the same endpoint.
	 *
	 * @param array $query_vars Current WP query vars.
	 * @return void
	 */
	public function load_template( $query_vars ) {
		if ( ! is_array( $query_vars ) || ! isset( $query_vars[ self::ENDPOINT ] ) ) {
			return;
		}

		if ( ! storesuite_current_user_can( 'manage_employees' ) ) {
			storesuite_get_template_part( 'global/no-permission' );
			return;
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only view switch.
		$view = isset( $_GET['view'] ) ? sanitize_key( wp_unslash( $_GET['view'] ) ) : 'employees';
		$view = in_array( $view, array( 'roles', 'activity' ), true ) ? $view : 'employees';

		$files    = array(
			'employees' => 'employees.php',
			'roles'     => 'roles.php',
			'activity'  => 'activity-log.php',
		);
		$template = $this->get_path() . '/templates/' . $files[ $view ];

		if ( file_exists( $template ) ) {
			$current_view = $view;
			include $template;
		}
	}

	/**
	 * Enqueue the Team screen script on the employees endpoint.
	 *
	 * @return void
	 */
	public function enqueue_assets() {
		if ( ! storesuite_is_endpoint_url( self::ENDPOINT ) ) {
			return;
		}

		$handle = 'storesuite-employee-manager';
		$src    = $this->get_url() . '/assets/employee.js';

		wp_enqueue_script(
			$handle,
			$src,
			array( 'jquery', 'storesuite_sweetalert2_script' ),
			$this->get_version(),
			true
		);

		wp_localize_script(
			$handle,
			'StoreSuiteEmployee',
			array(
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'nonce'    => wp_create_nonce( AjaxController::NONCE ),
				'listUrl'  => storesuite_get_navigation_url( self::ENDPOINT ),
				'i18n'     => array(
					'confirmDelete'    => __( 'Remove this employee? Their account is kept but loses dashboard access.', 'storesuite' ),
					'confirmSuspend'   => __( 'Suspend this employee? They will be locked out until reactivated.', 'storesuite' ),
					'confirmDeleteRole' => __( 'Delete this role? Members are moved to Customer.', 'storesuite' ),
					'saving'           => __( 'Saving…', 'storesuite' ),
					'success'          => __( 'Done', 'storesuite' ),
					'error'            => __( 'Something went wrong', 'storesuite' ),
				),
			)
		);
	}

	/**
	 * Register the `/employees` rewrite endpoint.
	 *
	 * @return void
	 */
	public function register_endpoint() {
		add_rewrite_endpoint( self::ENDPOINT, EP_PAGES );
	}

	/**
	 * Expose the endpoint to the StoreSuite Rewrites helper.
	 *
	 * @param array $vars Existing query vars.
	 * @return array
	 */
	public function register_query_var( $vars ) {
		if ( is_array( $vars ) ) {
			$vars[ self::ENDPOINT ] = self::ENDPOINT;
		}
		return $vars;
	}

	/**
	 * Add a "Team" item to the dashboard sidebar, gated on the
	 * manage-employees capability.
	 *
	 * @param array $menus Existing menu definitions.
	 * @return array
	 */
	public function register_menu( $menus ) {
		if ( ! is_array( $menus ) ) {
			return $menus;
		}

		$menus['employees'] = array(
			'title'      => __( 'Team', 'storesuite' ),
			'url'        => storesuite_get_navigation_url( self::ENDPOINT ),
			'permission' => Capabilities::cap_for_area( 'manage_employees' ),
			'icon'       => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24"><path d="M12,12a6,6,0,1,0-6-6A6,6,0,0,0,12,12ZM12,2a4,4,0,1,1-4,4A4,4,0,0,1,12,2Z"/><path d="M12,14a9,9,0,0,0-9,9,1,1,0,0,0,2,0,7,7,0,0,1,14,0,1,1,0,0,0,2,0A9,9,0,0,0,12,14Z"/></svg>',
		);

		return $menus;
	}

	/**
	 * {@inheritDoc}
	 */
	public function has_settings() {
		return true;
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_settings_schema() {
		return Settings::get_schema();
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_settings() {
		return Settings::get();
	}

	/**
	 * {@inheritDoc}
	 */
	public function update_settings( array $data ) {
		return Settings::update( $data );
	}

	/**
	 * Permanent teardown: drop tables and remove the staff roles (reassigning
	 * holders to `customer`). Runs from the plugin's root uninstall.php.
	 *
	 * @return void
	 */
	public function uninstall() {
		Roles::remove_all();
		Installer::uninstall();
		delete_option( Settings::OPTION_KEY );
	}
}
