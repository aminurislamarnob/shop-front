<?php

namespace PluginizeLab\ShopFront;

/**
 * ShopFront class
 *
 * @class ShopFront The class that holds the entire ShopFront plugin
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
    private $container = [];

    /**
     * Plugin dependencies
     *
     * @since 2.6.10
     *
     * @var array
     */
    private const SHOP_FRONT_DEPENEDENCIES = [
        'plugins' => [
            // 'woocommerce/woocommerce.php',
            // 'dokan-lite/dokan.php',
            // 'dokan-pro/dokan-pro.php'
        ],
        'classes' => [
            // 'Woocommerce',
            // 'WeDevs_Dokan',
            // 'Dokan_Pro'
        ],
        'functions' => [
            // 'dokan_admin_menu_position'
        ],
    ];

    /**
     * Constructor for the ShopFront class
     *
     * Sets up all the appropriate hooks and actions
     * within our plugin.
     */
    private function __construct() {
        $this->define_constants();

        register_activation_hook( SHOP_FRONT_FILE, [ $this, 'activate' ] );
        register_deactivation_hook( SHOP_FRONT_FILE, [ $this, 'deactivate' ] );

        add_action( 'plugins_loaded', [ $this, 'init_plugin' ] );
        add_action( 'woocommerce_flush_rewrite_rules', [ $this, 'flush_rewrite_rules' ] );
    }

    /**
     * Initializes the ShopFront() class
     *
     * Checks for an existing ShopFront instance
     * and if it doesn't find one then create a new one.
     *
     * @return ShopFront
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
        // Check shop_front dependency plugins
        if ( ! $this->check_dependencies() ) {
            wp_die( $this->get_dependency_message() );
        }

        // Rewrite rules during shop_front activation
        if ( $this->has_woocommerce() ) {
            $this->flush_rewrite_rules();
        }
    }

    /**
     * Flush rewrite rules after shop_front is activated or woocommerce is activated
     *
     * @since 3.2.8
     */
    public function flush_rewrite_rules() {
        // fix rewrite rules
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
        $this->define( 'SHOP_FRONT_PLUGIN_VERSION', $this->version );
        $this->define( 'SHOP_FRONT_DIR', dirname( SHOP_FRONT_FILE ) );
        $this->define( 'SHOP_FRONT_INC_DIR', SHOP_FRONT_DIR . '/includes' );
        $this->define( 'SHOP_FRONT_TEMPLATE_DIR', SHOP_FRONT_DIR . '/templates' );
        $this->define( 'SHOP_FRONT_PLUGIN_ASSET', plugins_url( 'assets', SHOP_FRONT_FILE ) );

        // give a way to turn off loading styles and scripts from parent theme
        $this->define( 'SHOP_FRONT_LOAD_STYLE', true );
        $this->define( 'SHOP_FRONT_LOAD_SCRIPTS', true );
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
        // Check shop_front dependency plugins
        if ( ! $this->check_dependencies() ) {
            add_action( 'admin_notices', [ $this, 'admin_error_notice_for_dependency_missing' ] );
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
        // initialize the classes
        add_action( 'init', [ $this, 'init_classes' ], 4 );
        add_action( 'plugins_loaded', [ $this, 'after_plugins_loaded' ] );
    }

    /**
     * Include all the required files
     *
     * @return void
     */
    public function includes() {
        // include_once STUB_PLUGIN_DIR . '/functions.php';
    }

    /**
     * Init all the classes
     *
     * @return void
     */
    public function init_classes() {
        require_once SHOP_FRONT_INC_DIR . '/functions.php';

        $this->container['scripts'] = new Assets();
        $this->container['msf_installer'] = new Installer();
        $this->container['msf_common'] = new Common();
        $this->container['msf_helper'] = new Helper();
        $this->container['msf_rewrites'] = new Rewrites();
        $this->container['msf_shortcode'] = new Shortcodes\Shortcodes();
    }

    /**
     * Executed after all plugins are loaded
     *
     * At this point shop_front Pro is loaded
     *
     * @since 2.8.7
     *
     * @return void
     */
    public function after_plugins_loaded() {
        // Initiate background processes and other tasks
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
     * Check plugin dependencies
     *
     * @return boolean
     */
    public function check_dependencies() {
        if ( array_key_exists( 'plugins', self::SHOP_FRONT_DEPENEDENCIES ) && ! empty( self::SHOP_FRONT_DEPENEDENCIES['plugins'] ) ) {
            for ( $plugin_counter = 0; $plugin_counter < count( self::SHOP_FRONT_DEPENEDENCIES['plugins'] ); $plugin_counter++ ) {
                if ( ! is_plugin_active( self::SHOP_FRONT_DEPENEDENCIES['plugins'][ $plugin_counter ] ) ) {
                    return false;
                }
            }
        } elseif ( array_key_exists( 'classes', self::SHOP_FRONT_DEPENEDENCIES ) && ! empty( self::SHOP_FRONT_DEPENEDENCIES['classes'] ) ) {
            for ( $class_counter = 0; $class_counter < count( self::SHOP_FRONT_DEPENEDENCIES['classes'] ); $class_counter++ ) {
                if ( ! class_exists( self::SHOP_FRONT_DEPENEDENCIES['classes'][ $class_counter ] ) ) {
                    return false;
                }
            }
        } elseif ( array_key_exists( 'functions', self::SHOP_FRONT_DEPENEDENCIES ) && ! empty( self::SHOP_FRONT_DEPENEDENCIES['functions'] ) ) {
            for ( $func_counter = 0; $func_counter < count( self::SHOP_FRONT_DEPENEDENCIES['functions'] ); $func_counter++ ) {
                if ( ! function_exists( self::SHOP_FRONT_DEPENEDENCIES['functions'][ $func_counter ] ) ) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Dependency error message
     *
     * @return void
     */
    protected function get_dependency_message() {
        return __( 'Shop Front plugin is enabled but not effective. It requires dependency plugins to work.', 'shop-front' );
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

        return apply_filters( 'shop-front_template', $template, $name );
    }

    /**
     * Access rewrites query globally by plugin main class
     * Ex: pluginizelab_shop_front()->get_msf_query()->get_current_endpoint();
     *
     * @return void
     */
    public function get_msf_query() {
        return new Rewrites();
    }
}