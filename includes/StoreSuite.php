<?php

namespace PluginizeLab\StoreSuite;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * MyStoreSuite class
 *
 * @class MyStoreSuite The class that holds the entire MyStoreSuite plugin
 */
final class StoreSuite {

	/**
	 * Plugin version
	 *
	 * @var string
	 */
	public $version = '1.0.0';

	/**
	 * Instance of self
	 *
	 * @var StoreSuite
	 */
	private static $instance = null;

	/**
	 * Holds various class instances
	 *
	 * @since 2.6.10
	 *
	 * @var array
	 */
	private $container = array();

	/**
	 * Constructor for the MyStoreSuite class
	 *
	 * Sets up all the appropriate hooks and actions
	 * within our plugin.
	 */
	private function __construct() {
		$this->define_constants();

		register_activation_hook( STORESUITE_FILE, array( $this, 'activate' ) );
		register_deactivation_hook( STORESUITE_FILE, array( $this, 'deactivate' ) );

		add_action( 'plugins_loaded', array( $this, 'init_plugin' ) );
		add_action( 'woocommerce_flush_rewrite_rules', array( $this, 'flush_rewrite_rules' ) );
		add_action( 'rest_api_init', array( $this, 'register_rest_route' ) );
		add_action( 'before_woocommerce_init', array( $this, 'make_wc_hpos_compatible' ) );
		add_filter( 'plugin_action_links_' . plugin_basename( STORESUITE_FILE ), array( $this, 'plugin_action_links' ) );
	}

	/**
	 * Initializes the MyStoreSuite() class
	 *
	 * Checks for an existing MyStoreSuite instance
	 * and if it doesn't find one then create a new one.
	 *
	 * @return MyStoreSuite
	 */
	public static function init() {
		if ( self::$instance === null ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Magic getter to bypass referencing objects
	 *
	 * @since 2.6.10
	 *
	 * @param string $prop
	 *
	 * @return Class Instance
	 */
	public function __get( $prop ) {
		if ( array_key_exists( $prop, $this->container ) ) {
			return $this->container[ $prop ];
		}
	}

	/**
	 * Placeholder for activation function
	 *
	 * Nothing is being called here yet.
	 */
	public function activate() {

		// Schedule flush on next request (after our rules are registered on init).
		update_option( 'storesuite_flush_rewrite_rules', 1 );

		// Create plugin page.
		Installer::create_plugin_page();
	}

	/**
	 * Register plugin REST routes
	 *
	 * @return void
	 */
	public function register_rest_route() {
		$this->container['storesuite_admin_settings_controller']->register_routes();
	}

	/**
	 * Declare WooCommerce HPOS compatibility
	 *
	 * @return void
	 */
	public function make_wc_hpos_compatible() {
		if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', STORESUITE_FILE, true );
		}
	}

	/**
	 * Plugin action links
	 *
	 * @param array $links
	 *
	 * @return array
	 */
	public function plugin_action_links( $links ) {
		$links[] = '<a href="' . esc_url( admin_url( 'admin.php?page=storesuite' ) ) . '">' . esc_html__( 'Settings', 'storesuite' ) . '</a>';
		return $links;
	}

	/**
	 * Flush rewrite rules after StoreSuite is activated or woocommerce is activated
	 */
	public function flush_rewrite_rules() {
		// fix rewrite rules.
		flush_rewrite_rules();
	}

	/**
	 * Placeholder for deactivation function
	 *
	 * Nothing being called here yet.
	 */
	public function deactivate() {     }

	/**
	 * Define all constants
	 *
	 * @return void
	 */
	public function define_constants() {
		defined( 'STORESUITE_PLUGIN_VERSION' ) || define( 'STORESUITE_PLUGIN_VERSION', $this->version );
		defined( 'STORESUITE_DIR' ) || define( 'STORESUITE_DIR', dirname( STORESUITE_FILE ) );
		defined( 'STORESUITE_INC_DIR' ) || define( 'STORESUITE_INC_DIR', STORESUITE_DIR . '/includes' );
		defined( 'STORESUITE_TEMPLATE_DIR' ) || define( 'STORESUITE_TEMPLATE_DIR', STORESUITE_DIR . '/templates' );
		defined( 'STORESUITE_PLUGIN_ASSET' ) || define( 'STORESUITE_PLUGIN_ASSET', plugins_url( 'assets', STORESUITE_PLUGIN_FILE ) );

		// give a way to turn off loading styles and scripts from parent theme.
		defined( 'STORESUITE_LOAD_STYLE' ) || define( 'STORESUITE_LOAD_STYLE', true );
		defined( 'STORESUITE_LOAD_SCRIPTS' ) || define( 'STORESUITE_LOAD_SCRIPTS', true );
	}

	/**
	 * Define constant if not already defined
	 *
	 * @param string      $name
	 * @param string|bool $value
	 *
	 * @return void
	 */
	private function define( $name, $value ) {
		if ( ! defined( $name ) ) {
			define( $name, $value );
		}
	}

	/**
	 * Load the plugin after WP User Frontend is loaded
	 *
	 * @return void
	 */
	public function init_plugin() {
		// Check StoreSuite dependency plugins.
		if ( ! $this->has_woocommerce() ) {
			add_action( 'admin_notices', array( $this, 'admin_error_notice_for_dependency_missing' ) );
			return;
		}

		$this->includes();
		$this->init_hooks();

		do_action( 'storesuite_loaded' );
	}

	/**
	 * Initialize the actions
	 *
	 * @return void
	 */
	public function init_hooks() {
		// initialize the classes.
		add_action( 'init', array( $this, 'init_classes' ), 4 );
		add_action( 'plugins_loaded', array( $this, 'after_plugins_loaded' ) );
		add_action( 'init', array( $this, 'maybe_flush_rewrite_rules' ), 999 );
	}

	/**
	 * Include all the required files
	 *
	 * @return void
	 */
	public function includes() {
		// Include all the required files.
	}

	/**
	 * Init all the classes
	 *
	 * @return void
	 */
	public function init_classes() {
		require_once STORESUITE_INC_DIR . '/functions.php';

		$this->container['cache']                                  = new Cache();
		$this->container['storesuite_main']                        = new Main();
		$this->container['scripts']                                = new Assets();
		$this->container['storesuite_installer']                   = new Installer();
		$this->container['storesuite_common']                      = new Common();
		$this->container['storesuite_helper']                      = new Helper();
		$this->container['storesuite_rewrites']                    = new Rewrites();
		$this->container['storesuite_dashboard_menu']              = new DashboardMenu();
		$this->container['storesuite_dashboard']                   = new Dashboard();
		$this->container['storesuite_dashboard_header']            = new TemplateParts();
		$this->container['storesuite_shortcode']                   = new Shortcodes\Shortcodes();
		$this->container['storesuite_admin_settings']              = new Admin\Settings();
		$this->container['storesuite_admin_bar']                   = new Admin\AdminBar();
		$this->container['storesuite_admin_settings_controller']   = new REST\SettingsController();
		$this->container['storesuite_product_categories']          = new ProductCategory\Categories();
		$this->container['storesuite_product_category_controller'] = new ProductCategory\CategoryController();
		$this->container['storesuite_product_brands']              = new ProductBrand\Brands();
		$this->container['storesuite_product_brand_controller']    = new ProductBrand\BrandController();
		$this->container['storesuite_product_tags']                = new ProductTag\Tags();
		$this->container['storesuite_product_tag_controller']      = new ProductTag\TagController();
		$this->container['storesuite_product_attribute_controller'] = new ProductAttribute\AttributeController();
		$this->container['storesuite_product_controller']          = new Product\ProductController();
		$this->container['storesuite_product_hooks']               = new Product\ProductHooks();
		$this->container['storesuite_order_controller']            = new Order\OrderController();
		$this->container['storesuite_create_new_order']            = new Order\CreateNewOrder();
		$this->container['storesuite_order_manager']               = new Order\OrderManager();
		$this->container['storesuite_order_hooks']                 = new Order\OrderHooks();
		$this->container['storesuite_coupon_controller']           = new Coupon\CouponController();
		$this->container['storesuite_coupon_manager']              = new Coupon\CouponManager();
		$this->container['storesuite_account_controller']          = new Account\AccountController();
		$this->container['storesuite_handle_paginations']          = new HandlePaginations();
	}

	/**
	 * Executed after all plugins are loaded
	 *
	 * At this point StoreSuite Pro is loaded
	 *
	 * @return void
	 */
	public function after_plugins_loaded() {
		// Initiate background processes and other tasks.
	}

	/**
	 * Maybe flush rewrite rules
	 *
	 * @return void
	 */
	public function maybe_flush_rewrite_rules() {
		if ( get_option( 'storesuite_flush_rewrite_rules' ) ) {
			flush_rewrite_rules();
			delete_option( 'storesuite_flush_rewrite_rules' );
		}
	}

	/**
	 * Check whether woocommerce is installed and active
	 *
	 * @return bool
	 */
	public function has_woocommerce() {
		return class_exists( 'WooCommerce' );
	}

	/**
	 * Check whether woocommerce is installed
	 *
	 * @return bool
	 */
	public function is_woocommerce_installed() {
		return in_array( 'woocommerce/woocommerce.php', array_keys( get_plugins() ), true );
	}

	/**
	 * Dependency error message
	 *
	 * @return void
	 */
	protected function get_dependency_message() {
		return __( 'My StoreSuite plugin is enabled but not effective. It requires dependency plugins to work.', 'storesuite' );
	}

	/**
	 * Admin error notice for missing dependency plugins
	 *
	 * @return void
	 */
	public function admin_error_notice_for_dependency_missing() {
		$class = 'notice notice-error';
		printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), esc_html( $this->get_dependency_message() ) );
	}

	/**
	 * Get the plugin url.
	 *
	 * @return string
	 */
	public function plugin_url() {
		return untrailingslashit( plugins_url( '/', STORESUITE_FILE ) );
	}

	/**
	 * Get the template file path to require or include.
	 *
	 * @param string $name
	 * @return string
	 */
	public function get_template( $name ) {
		$template = untrailingslashit( STORESUITE_TEMPLATE_DIR ) . '/' . untrailingslashit( $name );

		return apply_filters( 'storesuite_template', $template, $name );
	}

	/**
	 * Get the plugin path.
	 *
	 * @return string
	 */
	public function plugin_path() {
		return untrailingslashit( plugin_dir_path( __FILE__ ) );
	}

	/**
	 * Get the template path.
	 *
	 * @return string
	 */
	public function template_path() {
		return apply_filters( 'storesuite_template_path', 'my-storesuite/' );
	}

	/**
	 * Access rewrites query globally by plugin main class
	 * Ex: pluginizelab_storesuite()->get_storesuite_query()->get_current_endpoint();
	 *
	 * @return object
	 */
	public function get_storesuite_query() {
		return new Rewrites();
	}
}
