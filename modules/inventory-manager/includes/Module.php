<?php
/**
 * Inventory Manager module entry point.
 *
 * @package StoreSuite
 */

namespace PluginizeLab\StoreSuite\Modules\InventoryManager;

use PluginizeLab\StoreSuite\Abstracts\Module as BaseModule;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Inventory management module: a stock list across products/variations with
 * inline + bulk quantity edits, a low-stock view and dashboard widget, a stock
 * movement log, and low-stock email alerts. Area: `inventory`
 * (cap `storesuite_manage_inventory`).
 */
class Module extends BaseModule {

	const ENDPOINT = 'inventory';
	const AREA     = 'manage_inventory';

	/**
	 * {@inheritDoc}
	 */
	public function get_slug() {
		return 'inventory-manager';
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_name() {
		return __( 'Inventory Manager', 'storesuite' );
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_description() {
		return __( 'A stock list across all product types with inline and bulk quantity updates, a low-stock view, a stock movement log, and low-stock email alerts.', 'storesuite' );
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_requires() {
		return array( 'woocommerce/woocommerce.php' );
	}

	/**
	 * One-shot setup: create the stock log table.
	 *
	 * @return void
	 */
	public function activate() {
		Installer::install();
	}

	/**
	 * Non-destructive teardown: clear scheduled crons.
	 *
	 * @return void
	 */
	public function deactivate() {
		StockLog::unschedule();
		Alerts::unschedule();
	}

	/**
	 * Boot hooks for an active module.
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
		add_action( 'rest_api_init', array( $this, 'register_rest_routes' ) );

		add_filter( 'storesuite_endpoint_capability_map', array( $this, 'register_endpoint_area' ) );
		add_filter( 'storesuite_capability_registry', array( $this, 'register_capability' ) );
		add_filter( 'storesuite_ajax_capability_map', array( $this, 'register_ajax_area' ) );

		( new AjaxController() )->register();
		( new StockLog() )->register();
		( new Alerts() )->register();
		RestController::register_cache_busting();
	}

	/**
	 * @return void
	 */
	public function register_endpoint() {
		add_rewrite_endpoint( self::ENDPOINT, EP_PAGES );
	}

	/**
	 * @param array $vars Query vars.
	 * @return array
	 */
	public function register_query_var( $vars ) {
		if ( is_array( $vars ) ) {
			$vars[ self::ENDPOINT ] = self::ENDPOINT;
		}
		return $vars;
	}

	/**
	 * @param array $map Endpoint => area.
	 * @return array
	 */
	public function register_endpoint_area( $map ) {
		if ( is_array( $map ) ) {
			$map[ self::ENDPOINT ] = self::AREA;
		}
		return $map;
	}

	/**
	 * @param array $areas Area => label.
	 * @return array
	 */
	public function register_capability( $areas ) {
		if ( is_array( $areas ) ) {
			$areas[ self::AREA ] = __( 'Manage inventory', 'storesuite' );
		}
		return $areas;
	}

	/**
	 * Map the module's AJAX actions to the inventory area so staff grants work.
	 *
	 * @param array $map Action => area.
	 * @return array
	 */
	public function register_ajax_area( $map ) {
		if ( is_array( $map ) ) {
			$map['storesuite_inventory_set_stock']   = self::AREA;
			$map['storesuite_inventory_bulk_update'] = self::AREA;
		}
		return $map;
	}

	/**
	 * @return void
	 */
	public function register_rest_routes() {
		( new RestController() )->register_routes();
	}

	/**
	 * @param array $menus Menu definitions.
	 * @return array
	 */
	public function register_menu( $menus ) {
		if ( ! is_array( $menus ) ) {
			return $menus;
		}

		$menus['inventory'] = array(
			'title'      => __( 'Inventory', 'storesuite' ),
			'url'        => storesuite_get_navigation_url( self::ENDPOINT ),
			'permission' => 'storesuite_' . self::AREA,
			'icon'       => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24"><path d="M21,4H3A3,3,0,0,0,0,7v10a3,3,0,0,0,3,3H21a3,3,0,0,0,3-3V7A3,3,0,0,0,21,4Zm1,13a1,1,0,0,1-1,1H3a1,1,0,0,1-1-1V7A1,1,0,0,1,3,6H21a1,1,0,0,1,1,1ZM6,9a1,1,0,0,0-1,1v4a1,1,0,0,0,2,0V10A1,1,0,0,0,6,9Zm5,0a1,1,0,0,0-1,1v4a1,1,0,0,0,2,0V10A1,1,0,0,0,11,9Zm5,0a1,1,0,0,0-1,1v4a1,1,0,0,0,2,0V10A1,1,0,0,0,16,9Z"/></svg>',
		);

		return $menus;
	}

	/**
	 * Render the inventory list or the stock log.
	 *
	 * @param array $query_vars Query vars.
	 * @return void
	 */
	public function load_template( $query_vars ) {
		if ( ! is_array( $query_vars ) || ! isset( $query_vars[ self::ENDPOINT ] ) ) {
			return;
		}

		if ( ! storesuite_current_user_can( self::AREA ) ) {
			storesuite_get_template_part( 'global/no-permission' );
			return;
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only view switch.
		$view    = isset( $_GET['view'] ) && 'log' === $_GET['view'] ? 'stock-log' : 'inventory';
		$template = $this->get_path() . '/templates/' . $view . '.php';

		if ( file_exists( $template ) ) {
			include $template;
		}
	}

	/**
	 * @return void
	 */
	public function enqueue_assets() {
		if ( ! storesuite_is_endpoint_url( self::ENDPOINT ) ) {
			return;
		}

		$handle = 'storesuite-inventory-manager';
		wp_enqueue_script(
			$handle,
			$this->get_url() . '/assets/inventory.js',
			array( 'jquery', 'storesuite_sweetalert2_script' ),
			$this->get_version(),
			true
		);
		wp_localize_script(
			$handle,
			'StoreSuiteInventory',
			array(
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'nonce'    => wp_create_nonce( AjaxController::NONCE ),
				'i18n'     => array(
					'error'          => __( 'Something went wrong', 'storesuite' ),
					'selectProducts' => __( 'Select at least one product.', 'storesuite' ),
					'confirmBulk'    => __( 'Apply this stock change to the selected products?', 'storesuite' ),
				),
			)
		);
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
	 * Permanent teardown.
	 *
	 * @return void
	 */
	public function uninstall() {
		Installer::uninstall();
		delete_option( Settings::OPTION_KEY );
		delete_option( Alerts::QUEUE_OPTION );
	}
}
