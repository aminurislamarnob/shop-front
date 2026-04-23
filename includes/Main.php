<?php

/**
 * Main class.
 *
 * @package StoreSuite
 */

namespace PluginizeLab\StoreSuite;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Main class.
 *
 * Handles main functionality of the plugin.
 */
class Main {
	/**
	 * Constructor.
	 */
	public function __construct() {
		add_filter( 'login_redirect', array( $this, 'redirect_after_login' ), 1, 2 );
		add_filter( 'woocommerce_login_redirect', array( $this, 'redirect_after_login' ), 1, 2 );
		add_action( 'admin_init', array( $this, 'block_admin_access' ) );
		add_action( 'template_redirect', array( $this, 'redirect_if_not_logged_in_manager' ), 11 );
		add_filter( 'show_admin_bar', array( $this, 'hide_admin_bar' ) );
		add_action( 'woocommerce_account_dashboard', array( $this, 'add_storesuite_dashboard_btn' ), 1 );
		add_action( 'wp_enqueue_scripts', array( $this, 'add_storesuite_dashboard_btn_css' ), 20 );
		add_action( 'wp_enqueue_scripts', array( $this, 'add_storesuite_css_variables' ), 20 );
	}

	/**
	 * Block user access to admin panel for specific roles
	 *
	 * @global string $pagenow
	 */
	public function block_admin_access() {
		global $pagenow, $current_user;

		if ( defined( 'WP_CLI' ) ) {
			return;
		}

		$is_prevent_admin_access = storesuite_get_option_by_key( 'storesuite_prevent_admin_access' );
		$valid_pages = array( 'admin-ajax.php', 'admin-post.php', 'async-upload.php', 'media-upload.php' );
		$user_role   = reset( $current_user->roles );

		if ( ( 'yes' === $is_prevent_admin_access ) && in_array( $user_role, array( 'shop_manager', 'customer' ), true ) && ( ! in_array( $pagenow, $valid_pages, true ) ) ) {
			wp_safe_redirect( home_url() );
			exit;
		}
	}

	/**
	 * Hide admin bar for logged-in users if prevent admin access is enabled.
	 *
	 * @param bool $show Whether to show the admin bar.
	 * @return bool
	 */
	public function hide_admin_bar( $show ) {
		if ( ! is_user_logged_in() ) {
			return $show;
		}

		$is_prevent_admin_access = storesuite_get_option_by_key( 'storesuite_prevent_admin_access' );

		if ( 'yes' === $is_prevent_admin_access ) {
			return false;
		}

		return $show;
	}

	/**
	 * Redirect if not logged in and not manager.
	 *
	 * @return void
	 */
	public function redirect_if_not_logged_in_manager() {
		if ( is_page() && storesuite_is_dashboard_page() ) {
			storesuite_redirect_if_not_logged_in();
			storesuite_redirect_if_not_manager();
		}
	}

	/**
	 * Redirect after wooCommerce login
	 * my account page
	 *
	 * @global string $action
	 */
	public function redirect_after_login( $redirect_to, $user ) {

		// 1) Admins → WP admin dashboard.
		if ( $user instanceof \WP_User && in_array( 'administrator', (array) $user->roles, true ) ) {
			wp_safe_redirect( admin_url() );
			exit();
		}
	
		// 2) Non-admins who can manage WooCommerce → StoreSuite dashboard.
		if ( user_can( $user, 'manage_woocommerce' ) ) {
			$this->redirect_to_storesuite_dashboard(); // This already redirects & exits if page is set.
		}
	
		// 3) Everyone else → normal My Account page.
		wp_safe_redirect( wc_get_page_permalink( 'myaccount' ) );
		exit();
	}

	/**
	 * Redirect to the storesuite dashboard page
	 *
	 * @return void
	 */
	public function redirect_to_storesuite_dashboard() {
		$page_id = (int) storesuite_get_option_by_key( 'storesuite_dashboard_page_id' );

		if ( $page_id ) {
			wp_safe_redirect( storesuite_get_navigation_url() );
			exit();
		}
	}

	/**
	 * Add go to storesuite dashboard button to the woocommerce my account page
	 *
	 * @return string
	 */
	public function add_storesuite_dashboard_btn() {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			return;
		}

		printf(
			'<div><a href="%s" class="storesuite-dashboard-btn my-storesuite-button">%s</a></div>',
			esc_url( storesuite_get_navigation_url() ),
			esc_html__( 'StoreSuite Dashboard', 'storesuite' )
		);
	}

	/**
	 * Add CSS for the storesuite dashboard button
	 *
	 * @return void
	 */
	public function add_storesuite_dashboard_btn_css() {

		if ( ! is_user_logged_in() || ! is_account_page() || ! current_user_can( 'manage_woocommerce' ) ) {
			return;
		}

		$css = 'a.storesuite-dashboard-btn.my-storesuite-button {
			background: #2d5bdb;
			display: inline-flex;
			align-items: center;
			padding: 12px 20px;
			font-size: 14px;
			color: #fff;
			border-radius: 6px;
			cursor: pointer;
			line-height: 1.15;
			border: 1px solid #2d5bdb;
			justify-content: center;
			font-weight: 600;
		}
		a.storesuite-dashboard-btn.my-storesuite-button:hover {
			background: #213fd4;
			border: 1px solid #213fd4;
		}';

		wp_register_style( 'storesuite-dashboard-btn', false );
		wp_enqueue_style( 'storesuite-dashboard-btn' );
		wp_add_inline_style( 'storesuite-dashboard-btn', $css );
	}

	public function add_storesuite_css_variables() {
		if ( ! storesuite_is_dashboard_page() ) {
			return;
		}

		$css_vars = array(
			'--storesuite-primary-bg'             => 'storesuite_color_button_background',
			'--storesuite-primary-bg-hover'      => 'storesuite_color_button_hover_background',
			'--storesuite-button-text-color'     => 'storesuite_color_button_text',
			'--storesuite-button-text-hover-color' => 'storesuite_color_button_hover_text',
			'--storesuite-text-black'             => 'storesuite_title_text_color',
			'--storesuite-text-color'             => 'storesuite_text_color',
			'--storesuite-text-color-light'       => 'storesuite_lite_text_color',
			'--storesuite-icon-color'             => 'storesuite_icon_color',
			'--storesuite-sidebar-bg-color'       => 'storesuite_color_sidebar_background',
			'--storesuite-sidebar-menu-text'     => 'storesuite_color_sidebar_menu_text',
			'--storesuite-sidebar-active-text'    => 'storesuite_color_sidebar_active_text',
			'--storesuite-sidebar-active-background' => 'storesuite_color_sidebar_active_background',
			'--storesuite-sidebar-border-color'   => 'storesuite_color_sidebar_border',
			'--storesuite-bg-color-light'         => 'storesuite_color_lite_bg',
			'--storesuite-border-color'           => 'storesuite_color_border',
		);

		$rules = array();
		foreach ( $css_vars as $var_name => $option_key ) {
			$value = storesuite_get_option_by_key( $option_key );
			if ( $value !== '' && $value !== null ) {
				$rules[] = $var_name . ': ' . esc_attr( $value );
			}
		}
		if ( empty( $rules ) ) {
			return;
		}

		$css = ':root{ ' . esc_attr( implode( ';', $rules ) ) . ' }';
		wp_register_style( 'storesuite-css-variables', false );
		wp_enqueue_style( 'storesuite-css-variables' );
		wp_add_inline_style( 'storesuite-css-variables', $css );
	}
}
