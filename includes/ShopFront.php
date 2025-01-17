<?php

namespace PluginizeLab\ShopFront;

/**
 * MyShopFront class
 *
 * @class MyShopFront The class that holds the entire MyShopFront plugin
 */
final class ShopFront {

	/**
	 * Plugin version
	 *
	 * @var string
	 */
	public $version = '0.0.1';

	/**
	 * Instance of self
	 *
	 * @var ShopFront
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
	 * Constructor for the MyShopFront class
	 *
	 * Sets up all the appropriate hooks and actions
	 * within our plugin.
	 */
	private function __construct() {
		$this->define_constants();

		register_activation_hook( SHOP_FRONT_FILE, array( $this, 'activate' ) );
		register_deactivation_hook( SHOP_FRONT_FILE, array( $this, 'deactivate' ) );

		add_action( 'plugins_loaded', array( $this, 'init_plugin' ) );
		add_action( 'woocommerce_flush_rewrite_rules', array( $this, 'flush_rewrite_rules' ) );
		add_action( 'rest_api_init', array( $this, 'register_rest_route' ) );
	}

	/**
	 * Initializes the MyShopFront() class
	 *
	 * Checks for an existing MyShopFront instance
	 * and if it doesn't find one then create a new one.
	 *
	 * @return MyShopFront
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

		// Rewrite rules during SHOP_FRONT activation.
		if ( $this->has_woocommerce() ) {
			$this->flush_rewrite_rules();
		}

		// Create plugin page.
		Installer::create_plugin_page();
	}

	/**
	 * Register plugin REST routes
	 *
	 * @return void
	 */
	public function register_rest_route() {
		$this->container['msf_admin_settings_controller']->register_routes();
	}

	/**
	 * Flush rewrite rules after SHOP_FRONT is activated or woocommerce is activated
	 *
	 * @since 3.2.8
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
		defined( 'SHOP_FRONT_PLUGIN_VERSION' ) || define( 'SHOP_FRONT_PLUGIN_VERSION', $this->version );
		defined( 'SHOP_FRONT_DIR' ) || define( 'SHOP_FRONT_DIR', dirname( SHOP_FRONT_FILE ) );
		defined( 'SHOP_FRONT_INC_DIR' ) || define( 'SHOP_FRONT_INC_DIR', SHOP_FRONT_DIR . '/includes' );
		defined( 'SHOP_FRONT_TEMPLATE_DIR' ) || define( 'SHOP_FRONT_TEMPLATE_DIR', SHOP_FRONT_DIR . '/templates' );
		defined( 'SHOP_FRONT_PLUGIN_ASSET' ) || define( 'SHOP_FRONT_PLUGIN_ASSET', plugins_url( 'assets', SHOP_FRONT_FILE ) );
		defined( 'SHOP_FRONT_NONCE_SALT' ) || define( 'SHOP_FRONT_NONCE_SALT', 'iG685CuXEZI2J?@~-t 3v)*_z]e,+CXh/Mu#8Fq4W<B^w9m^c]C8XGn(V~#:dn%C' );

		// give a way to turn off loading styles and scripts from parent theme.
		defined( 'SHOP_FRONT_LOAD_STYLE' ) || define( 'SHOP_FRONT_LOAD_STYLE', true );
		defined( 'SHOP_FRONT_LOAD_SCRIPTS' ) || define( 'SHOP_FRONT_LOAD_SCRIPTS', true );
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
		// Check SHOP_FRONT dependency plugins.
		if ( ! $this->has_woocommerce() ) {
			add_action( 'admin_notices', array( $this, 'admin_error_notice_for_dependency_missing' ) );
			return;
		}

		$this->includes();
		$this->init_hooks();

		do_action( 'shop_front_loaded' );
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
		require_once SHOP_FRONT_INC_DIR . '/functions.php';

		$this->container['scripts']                         = new Assets();
		$this->container['msf_installer']                   = new Installer();
		$this->container['msf_common']                      = new Common();
		$this->container['msf_helper']                      = new Helper();
		$this->container['msf_rewrites']                    = new Rewrites();
		$this->container['msf_dashboard_menu']              = new DashboardMenu();
		$this->container['msf_shortcode']                   = new Shortcodes\Shortcodes();
		$this->container['msf_admin_settings']              = new Admin\Settings();
		$this->container['msf_admin_settings_controller']   = new REST\SettingsController();
		$this->container['msf_product_categories']          = new ProductCategory\Categories();
		$this->container['msf_product_category_controller'] = new ProductCategory\CategoryController();
		$this->container['msf_product_tag_controller']      = new ProductTag\TagController();
	}

	/**
	 * Executed after all plugins are loaded
	 *
	 * At this point SHOP_FRONT Pro is loaded
	 *
	 * @since 2.8.7
	 *
	 * @return void
	 */
	public function after_plugins_loaded() {
		// Initiate background processes and other tasks.
	}

	/**
	 * Check whether woocommerce is installed and active
	 *
	 * @since 2.9.16
	 *
	 * @return bool
	 */
	public function has_woocommerce() {
		return class_exists( 'WooCommerce' );
	}

	/**
	 * Check whether woocommerce is installed
	 *
	 * @since 3.2.8
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
		return __( 'My Shop Front plugin is enabled but not effective. It requires dependency plugins to work.', 'my-shop-front' );
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
		return untrailingslashit( plugins_url( '/', SHOP_FRONT_FILE ) );
	}

	/**
	 * Get the template file path to require or include.
	 *
	 * @param string $name
	 * @return string
	 */
	public function get_template( $name ) {
		$template = untrailingslashit( SHOP_FRONT_TEMPLATE_DIR ) . '/' . untrailingslashit( $name );

		return apply_filters( 'shop_front_template', $template, $name );
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
		return apply_filters( 'msf_template_path', 'my-shop-front/' );
	}

	/**
	 * Access rewrites query globally by plugin main class
	 * Ex: pluginizelab_shop_front()->get_msf_query()->get_current_endpoint();
	 *
	 * @return object
	 */
	public function get_msf_query() {
		return new Rewrites();
	}
}
