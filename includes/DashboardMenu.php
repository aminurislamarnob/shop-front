<?php

namespace PluginizeLab\StoreSuite;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class DashboardMenu {
	/**
	 * The constructor.
	 */
	public function __construct() {
		add_action( 'storesuite_dashboard_navigation', array( $this, 'add_dashboard_navigations' ) );
	}

	/**
	 * Allowed HTML tags for SVG icons in wp_kses.
	 */
	private function allowed_icon_tags(): array {
		return array(
			'i'        => array( 'class' => array() ),
			'svg'      => array(
				'xmlns'            => array(),
				'width'            => array(),
				'height'           => array(),
				'fill'             => array(),
				'class'            => array(),
				'viewBox'          => array(),
				'stroke'           => array(),
				'stroke-width'     => array(),
				'stroke-linecap'   => array(),
				'stroke-linejoin'  => array(),
			),
			'path'     => array(
				'd'         => array(),
				'fill-rule' => array(),
			),
			'polyline' => array( 'points' => array() ),
		);
	}

	/**
	 * Adds dashboard navigation menus to the admin dashboard.
	 */
	public function add_dashboard_navigations() {
		$menus          = $this->get_dashboard_menus();
		$active_menu    = $this->get_active_menu();
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$current_report = isset( $_GET['report'] ) ? sanitize_key( $_GET['report'] ) : 'overview';

		$chevron = '<svg class="storesuite-menu-arrow" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>';

		echo '<ul class="storesuite-dashboard-menu">';

		foreach ( $menus as $key => $menu ) {
			if ( ! current_user_can( $menu['permission'] ) ) {
				continue;
			}

			if ( 'wp_dashboard' === $key && storesuite_get_option_by_key( 'storesuite_prevent_admin_access' ) === 'yes' && ! current_user_can( 'administrator' ) ) {
				continue;
			}

			$has_submenu = ! empty( $menu['submenu'] ) && is_array( $menu['submenu'] );
			$is_active   = ( $active_menu === $key );

			$li_classes = array();
			if ( $has_submenu ) {
				$li_classes[] = 'has-submenu';
			}
			if ( $is_active && $has_submenu ) {
				$li_classes[] = 'is-open';
			}

			$li_class_attr = ! empty( $li_classes )
				? ' class="' . esc_attr( implode( ' ', $li_classes ) ) . '"'
				: '';

			echo '<li' . $li_class_attr . '>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

			echo '<a href="' . esc_url( $menu['url'] ) . '" class="' . ( $is_active ? 'active' : '' ) . '" target="' . esc_attr( $menu['target'] ) . '" data-storesuite-tooltip="' . esc_attr( $menu['title'] ) . '">';
			echo wp_kses( $menu['icon'], $this->allowed_icon_tags() );
			echo '<span>' . esc_html( $menu['title'] ) . '</span>';
			if ( $has_submenu ) {
				echo wp_kses( $chevron, $this->allowed_icon_tags() );
			}
			echo '</a>';

			if ( $has_submenu ) {
				echo '<ul class="submenu">';
				foreach ( $menu['submenu'] as $subkey => $submenu ) {
					if ( ! current_user_can( $submenu['permission'] ) ) {
						continue;
					}
					$sub_active = $is_active && ( $current_report === $subkey );
					echo '<li>';
					echo '<a href="' . esc_url( $submenu['url'] ) . '" class="' . ( $sub_active ? 'active' : '' ) . '">';
					echo '<span>' . esc_html( $submenu['title'] ) . '</span>';
					echo '</a>';
					echo '</li>';
				}
				echo '</ul>';
			}

			echo '</li>';
		}

		echo '</ul>';
	}

	/**
	 * Retrieves the dashboard navigation menus.
	 *
	 * @return array An associative array of dashboard menu items, where each item contains
	 *               properties such as 'title', 'icon', 'url', 'pos', 'permission', and optional 'submenu'.
	 */
	public function get_dashboard_menus(): array {
		$menus = array(
			'analytics'    => array(
				'title'      => __( 'Analytics', 'storesuite' ),
				'icon'       => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M3 3v18h18"/><path d="m19 9-5 5-4-4-3 3"/></svg>',
				'url'        => storesuite_get_navigation_url( 'analytics' ),
				'pos'        => 20,
				'permission' => 'manage_woocommerce',
				'target'     => '_self',
				'submenu'    => array(
					'overview'   => array(
						'title'      => __( 'Overview',    'storesuite' ),
						'url'        => add_query_arg( 'report', 'overview',    storesuite_get_navigation_url( 'analytics' ) ),
						'icon'       => '',
						'permission' => 'manage_woocommerce',
					),
					'revenue'    => array(
						'title'      => __( 'Revenue',     'storesuite' ),
						'url'        => add_query_arg( 'report', 'revenue',     storesuite_get_navigation_url( 'analytics' ) ),
						'icon'       => '',
						'permission' => 'manage_woocommerce',
					),
					'orders'     => array(
						'title'      => __( 'Orders',      'storesuite' ),
						'url'        => add_query_arg( 'report', 'orders',      storesuite_get_navigation_url( 'analytics' ) ),
						'icon'       => '',
						'permission' => 'manage_woocommerce',
					),
					'products'   => array(
						'title'      => __( 'Products',    'storesuite' ),
						'url'        => add_query_arg( 'report', 'products',    storesuite_get_navigation_url( 'analytics' ) ),
						'icon'       => '',
						'permission' => 'manage_woocommerce',
					),
					'variations' => array(
						'title'      => __( 'Variations',  'storesuite' ),
						'url'        => add_query_arg( 'report', 'variations',  storesuite_get_navigation_url( 'analytics' ) ),
						'icon'       => '',
						'permission' => 'manage_woocommerce',
					),
					'categories' => array(
						'title'      => __( 'Categories',  'storesuite' ),
						'url'        => add_query_arg( 'report', 'categories',  storesuite_get_navigation_url( 'analytics' ) ),
						'icon'       => '',
						'permission' => 'manage_woocommerce',
					),
					'stock'      => array(
						'title'      => __( 'Stock',       'storesuite' ),
						'url'        => add_query_arg( 'report', 'stock',       storesuite_get_navigation_url( 'analytics' ) ),
						'icon'       => '',
						'permission' => 'manage_woocommerce',
					),
				),
			),
			'dashboard'    => array(
				'title'      => __( 'Dashboard', 'storesuite' ),
				'icon'       => '<svg xmlns="http://www.w3.org/2000/svg" id="Layer_1" data-name="Layer 1" viewBox="0 0 24 24" width="24" height="24"><path d="M24,13a11.914,11.914,0,0,1-3.508,8.47,3.037,3.037,0,0,1-4.12.174l-1.026-.887a1,1,0,0,1,1.308-1.514l1.027.888a1.014,1.014,0,0,0,1.395-.075,10.044,10.044,0,0,0-.414-14.513,9.9,9.9,0,0,0-7.823-2.478A9.992,9.992,0,0,0,4.962,20.094a1,1,0,0,0,1.357.038l1.027-.889a1,1,0,0,1,1.308,1.514l-1.026.888a3.016,3.016,0,0,1-4.073-.129A12,12,0,1,1,24,13ZM17.707,8.707a1,1,0,0,0-1.414-1.414l-3.775,3.775a2,2,0,1,0,1.414,1.414Z"/></svg>',
				'url'        => storesuite_get_navigation_url(),
				'pos'        => 10,
				'permission' => 'manage_woocommerce',
				'target'     => '_self',
			),
			'products'     => array(
				'title'      => __( 'Products', 'storesuite' ),
				'icon'       => '<svg xmlns="http://www.w3.org/2000/svg" id="Layer_1" data-name="Layer 1" viewBox="0 0 24 24" width="24" height="24"><path d="M19.5,16c0,.553-.447,1-1,1h-2c-.553,0-1-.447-1-1s.447-1,1-1h2c.553,0,1,.447,1,1Zm4.5-1v5c0,2.206-1.794,4-4,4H4c-2.206,0-4-1.794-4-4v-5c0-2.206,1.794-4,4-4h1V4C5,1.794,6.794,0,9,0h6c2.206,0,4,1.794,4,4v7h1c2.206,0,4,1.794,4,4ZM7,11h10V4c0-1.103-.897-2-2-2h-6c-1.103,0-2,.897-2,2v7Zm-3,11h7V13H4c-1.103,0-2,.897-2,2v5c0,1.103,.897,2,2,2Zm18-7c0-1.103-.897-2-2-2h-7v9h7c1.103,0,2-.897,2-2v-5Zm-14.5,0h-2c-.553,0-1,.447-1,1s.447,1,1,1h2c.553,0,1-.447,1-1s-.447-1-1-1ZM14,5c0-.553-.447-1-1-1h-2c-.553,0-1,.447-1,1s.447,1,1,1h2c.553,0,1-.447,1-1Z"/></svg>',
				'url'        => storesuite_get_navigation_url( 'products' ),
				'pos'        => 30,
				'permission' => 'manage_woocommerce',
				'target'     => '_self',
			),
			'orders'       => array(
				'title'      => __( 'Orders', 'storesuite' ),
				'icon'       => '<svg xmlns="http://www.w3.org/2000/svg" id="Layer_1" data-name="Layer 1" viewBox="0 0 24 24" width="24" height="24">
				<path d="M22,14c0,.553-.448,1-1,1H6.737c.416,1.174,1.528,2,2.82,2h9.443c.552,0,1,.447,1,1s-.448,1-1,1H9.557c-2.535,0-4.67-1.898-4.966-4.415L3.215,2.884c-.059-.504-.486-.884-.993-.884H1c-.552,0-1-.447-1-1S.448,0,1,0h1.222c1.521,0,2.802,1.139,2.979,2.649l.041,.351h3.758c.552,0,1,.447,1,1s-.448,1-1,1h-3.522l.941,8h14.581c.552,0,1,.447,1,1Zm-15,6c-1.105,0-2,.895-2,2s.895,2,2,2,2-.895,2-2-.895-2-2-2Zm10,0c-1.105,0-2,.895-2,2s.895,2,2,2,2-.895,2-2-.895-2-2-2Zm2-14.414v-1.586c0-.553-.448-1-1-1s-1,.447-1,1v2c0,.266,.105,.52,.293,.707l1,1c.195,.195,.451,.293,.707,.293s.512-.098,.707-.293c.391-.391,.391-1.023,0-1.414l-.707-.707Zm5,.414c0,3.309-2.691,6-6,6s-6-2.691-6-6S14.691,0,18,0s6,2.691,6,6Zm-2,0c0-2.206-1.794-4-4-4s-4,1.794-4,4,1.794,4,4,4,4-1.794,4-4Z"/>
				</svg>',
				'url'        => storesuite_get_navigation_url( 'orders' ),
				'pos'        => 50,
				'permission' => 'manage_woocommerce',
				'target'     => '_self',
			),
			'categories'   => array(
				'title'      => __( 'Categories', 'storesuite' ),
				'icon'       => '<svg xmlns="http://www.w3.org/2000/svg" id="Outline" viewBox="0 0 24 24" width="24" height="24"><path d="M7,0H4A4,4,0,0,0,0,4V7a4,4,0,0,0,4,4H7a4,4,0,0,0,4-4V4A4,4,0,0,0,7,0ZM9,7A2,2,0,0,1,7,9H4A2,2,0,0,1,2,7V4A2,2,0,0,1,4,2H7A2,2,0,0,1,9,4Z"/><path d="M20,0H17a4,4,0,0,0-4,4V7a4,4,0,0,0,4,4h3a4,4,0,0,0,4-4V4A4,4,0,0,0,20,0Zm2,7a2,2,0,0,1-2,2H17a2,2,0,0,1-2-2V4a2,2,0,0,1,2-2h3a2,2,0,0,1,2,2Z"/><path d="M7,13H4a4,4,0,0,0-4,4v3a4,4,0,0,0,4,4H7a4,4,0,0,0,4-4V17A4,4,0,0,0,7,13Zm2,7a2,2,0,0,1-2,2H4a2,2,0,0,1-2-2V17a2,2,0,0,1,2-2H7a2,2,0,0,1,2,2Z"/><path d="M20,13H17a4,4,0,0,0-4,4v3a4,4,0,0,0,4,4h3a4,4,0,0,0,4-4V17A4,4,0,0,0,20,13Zm2,7a2,2,0,0,1-2,2H17a2,2,0,0,1-2-2V17a2,2,0,0,1,2-2h3a2,2,0,0,1,2,2Z"/></svg>',
				'url'        => storesuite_get_navigation_url( 'categories' ),
				'pos'        => 30,
				'permission' => 'manage_woocommerce',
				'target'     => '_self',
			),
			'brands'       => array(
				'title'      => __( 'Brands', 'storesuite' ),
				'icon'       => '<svg xmlns="http://www.w3.org/2000/svg" id="Layer_1" data-name="Layer 1" viewBox="0 0 24 24" width="24" height="24"><path d="M21,0H3A3,3,0,0,0,0,3V21a3,3,0,0,0,3,3H21a3,3,0,0,0,3-3V3A3,3,0,0,0,21,0ZM22,21a1,1,0,0,1-1,1H3a1,1,0,0,1-1-1V3A1,1,0,0,1,3,2H21a1,1,0,0,1,1,1Z"/><path d="M7,17H17a1,1,0,0,0,0-2H7a1,1,0,0,0,0,2Z"/><path d="M10,12h7a1,1,0,0,0,0-2H10a1,1,0,0,0,0,2Z"/><path d="M17,7H7A1,1,0,0,0,7,9H17a1,1,0,0,0,0-2Z"/></svg>',
				'url'        => storesuite_get_navigation_url( 'brands' ),
				'pos'        => 30,
				'permission' => 'manage_woocommerce',
				'target'     => '_self',
			),
			'tags'         => array(
				'title'      => __( 'Tags', 'storesuite' ),
				'icon'       => '<svg xmlns="http://www.w3.org/2000/svg" id="Layer_1" data-name="Layer 1" viewBox="0 0 24 24" width="24" height="24"><path d="M7.707,9.256c.391,.391,.391,1.024,0,1.414-.391,.391-1.024,.391-1.414,0-.391-.391-.391-1.024,0-1.414,.391-.391,1.024-.391,1.414,0Zm13.852,6.085l-.565,.565c-.027,1.233-.505,2.457-1.435,3.399l-3.167,3.208c-.943,.955-2.201,1.483-3.543,1.487h-.017c-1.335,0-2.59-.52-3.534-1.464L1.882,15.183c-.65-.649-.964-1.542-.864-2.453l.765-6.916c.051-.456,.404-.819,.858-.881l6.889-.942c.932-.124,1.87,.193,2.528,.851l7.475,7.412c.387,.387,.697,.823,.931,1.288,.812-1.166,.698-2.795-.342-3.835L12.531,2.302c-.229-.229-.545-.335-.851-.292l-6.889,.942c-.549,.074-1.052-.309-1.127-.855-.074-.547,.309-1.051,.855-1.126L11.409,.028c.921-.131,1.869,.191,2.528,.852l7.589,7.405c1.946,1.945,1.957,5.107,.032,7.057Zm-3.438-1.67l-7.475-7.412c-.223-.223-.536-.326-.847-.287l-6.115,.837-.679,6.14c-.033,.303,.071,.601,.287,.816l7.416,7.353c.569,.57,1.322,.881,2.123,.881h.01c.806-.002,1.561-.319,2.126-.893l3.167-3.208c1.155-1.17,1.149-3.067-.014-4.229Z"/></svg>',
				'url'        => storesuite_get_navigation_url( 'tags' ),
				'pos'        => 30,
				'permission' => 'manage_woocommerce',
				'target'     => '_self',
			),
			'coupons'      => array(
				'title'      => __( 'Coupons', 'storesuite' ),
				'icon'       => '<svg xmlns="http://www.w3.org/2000/svg" id="Layer_1" data-name="Layer 1" viewBox="0 0 24 24" width="24" height="24"><path d="M19,0H5C2.243,0,0,2.243,0,5v3c0,.552,.448,1,1,1,1.103,0,2,.897,2,2s-.897,2-2,2c-.552,0-1,.448-1,1v5c0,2.757,2.243,5,5,5h14c2.757,0,5-2.243,5-5v-5c0-.552-.448-1-1-1-1.103,0-2-.897-2-2s.897-2,2-2c.552,0,1-.448,1-1v-3c0-2.757-2.243-5-5-5Zm3,7.813c-1.832,.533-3.2,2.221-3.2,4.187s1.368,3.654,3.2,4.187v2.813c0,1.654-1.346,3-3,3H5c-1.654,0-3-1.346-3-3v-2.813c1.832-.533,3.2-2.221,3.2-4.187s-1.368-3.654-3.2-4.187v-2.813c0-1.654,1.346-3,3-3h14c1.654,0,3,1.346,3,3v2.813Zm-6.707,8.481l-5.586-5.586c-.391-.391-.391-1.023,0-1.414s1.023-.391,1.414,0l5.586,5.586c.391,.391,.391,1.023,0,1.414-.195,.195-.451,.293-.707,.293s-.512-.098-.707-.293Zm-1.293-7.294c0-.829,.671-1.5,1.5-1.5s1.5,.671,1.5,1.5-.671,1.5-1.5,1.5-1.5-.671-1.5-1.5Zm-6,6c0,.829-.671,1.5-1.5,1.5s-1.5-.671-1.5-1.5,.671-1.5,1.5-1.5,1.5,.671,1.5,1.5Z"/></svg>',
				'url'        => storesuite_get_navigation_url( 'coupons' ),
				'pos'        => 30,
				'permission' => 'manage_woocommerce',
				'target'     => '_self',
			),
			'edit-account-details' => array(
				'title'      => __( 'Account', 'storesuite' ),
				'icon'       => '<svg xmlns="http://www.w3.org/2000/svg" id="Layer_1" data-name="Layer 1" viewBox="0 0 24 24" width="24" height="24"><path d="M15,6c0-3.309-2.691-6-6-6S3,2.691,3,6s2.691,6,6,6,6-2.691,6-6Zm-6,4c-2.206,0-4-1.794-4-4s1.794-4,4-4,4,1.794,4,4-1.794,4-4,4Zm-.008,4.938c.068,.548-.32,1.047-.869,1.116-3.491,.436-6.124,3.421-6.124,6.946,0,.552-.448,1-1,1s-1-.448-1-1c0-4.531,3.386-8.37,7.876-8.93,.542-.069,1.047,.32,1.116,.869Zm13.704,4.195l-.974-.562c.166-.497,.278-1.019,.278-1.572s-.111-1.075-.278-1.572l.974-.562c.478-.276,.642-.888,.366-1.366-.277-.479-.887-.644-1.366-.366l-.973,.562c-.705-.794-1.644-1.375-2.723-1.594v-1.101c0-.552-.448-1-1-1s-1,.448-1,1v1.101c-1.079,.22-2.018,.801-2.723,1.594l-.973-.562c-.48-.277-1.09-.113-1.366,.366-.276,.479-.112,1.09,.366,1.366l.974,.562c-.166,.497-.278,1.019-.278,1.572s.111,1.075,.278,1.572l-.974,.562c-.478,.276-.642,.888-.366,1.366,.186,.321,.521,.5,.867,.5,.169,0,.341-.043,.499-.134l.973-.562c.705,.794,1.644,1.375,2.723,1.594v1.101c0,.552,.448,1,1,1s1-.448,1-1v-1.101c1.079-.22,2.018-.801,2.723-1.594l.973,.562c.158,.091,.33,.134,.499,.134,.346,0,.682-.179,.867-.5,.276-.479,.112-1.09-.366-1.366Zm-5.696,.866c-1.654,0-3-1.346-3-3s1.346-3,3-3,3,1.346,3,3-1.346,3-3,3Z"/></svg>',
				'url'        => storesuite_get_navigation_url( 'edit-account-details' ),
				'pos'        => 25,
				'permission' => 'manage_woocommerce',
				'target'     => '_self',
			),
			'home'         => array(
				'title'      => __( 'Visit Home', 'storesuite' ),
				'icon'       => '<svg xmlns="http://www.w3.org/2000/svg" id="Layer_1" data-name="Layer 1" viewBox="0 0 24 24" width="24" height="24"><path d="M20,11v8c0,2.757-2.243,5-5,5H5c-2.757,0-5-2.243-5-5V9c0-2.757,2.243-5,5-5H13c.552,0,1,.448,1,1s-.448,1-1,1H5c-1.654,0-3,1.346-3,3v10c0,1.654,1.346,3,3,3H15c1.654,0,3-1.346,3-3V11c0-.552,.448-1,1-1s1,.448,1,1ZM21,0h-7c-.552,0-1,.448-1,1s.448,1,1,1h6.586L8.293,14.293c-.391,.391-.391,1.023,0,1.414,.195,.195,.451,.293,.707,.293s.512-.098,.707-.293L22,3.414v6.586c0,.552,.448,1,1,1s1-.448,1-1V3c0-1.654-1.346-3-3-3Z"></path></svg>',
				'url'        => get_home_url(),
				'pos'        => 30,
				'permission' => 'manage_woocommerce',
				'target'     => '_blank',
			),
			'wp_dashboard' => array(
				'title'      => __( 'WP Dashboard', 'storesuite' ),
				'icon'       => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="-2 -2 24 24" width="24" height="24" class="edit-site-site-icon__icon" aria-hidden="true" focusable="false"><path d="M20 10c0-5.51-4.49-10-10-10C4.48 0 0 4.49 0 10c0 5.52 4.48 10 10 10 5.51 0 10-4.48 10-10zM7.78 15.37L4.37 6.22c.55-.02 1.17-.08 1.17-.08.5-.06.44-1.13-.06-1.11 0 0-1.45.11-2.37.11-.18 0-.37 0-.58-.01C4.12 2.69 6.87 1.11 10 1.11c2.33 0 4.45.87 6.05 2.34-.68-.11-1.65.39-1.65 1.58 0 .74.45 1.36.9 2.1.35.61.55 1.36.55 2.46 0 1.49-1.4 5-1.4 5l-3.03-8.37c.54-.02.82-.17.82-.17.5-.05.44-1.25-.06-1.22 0 0-1.44.12-2.38.12-.87 0-2.33-.12-2.33-.12-.5-.03-.56 1.2-.06 1.22l.92.08 1.26 3.41zM17.41 10c.24-.64.74-1.87.43-4.25.7 1.29 1.05 2.71 1.05 4.25 0 3.29-1.73 6.24-4.4 7.78.97-2.59 1.94-5.2 2.92-7.78zM6.1 18.09C3.12 16.65 1.11 13.53 1.11 10c0-1.3.23-2.48.72-3.59C3.25 10.3 4.67 14.2 6.1 18.09zm4.03-6.63l2.58 6.98c-.86.29-1.76.45-2.71.45-.79 0-1.57-.11-2.29-.33.81-2.38 1.62-4.74 2.42-7.1z"></path></svg>',
				'url'        => get_dashboard_url(),
				'pos'        => 30,
				'permission' => 'manage_woocommerce',
				'target'     => '_self',
			),
			'logout'       => array(
				'title'      => __( 'Logout', 'storesuite' ),
				'icon'       => '<svg xmlns="http://www.w3.org/2000/svg" id="Outline" viewBox="0 0 24 24" width="24" height="24"><path d="M22.829,9.172,18.95,5.293a1,1,0,0,0-1.414,1.414l3.879,3.879a2.057,2.057,0,0,1,.3.39c-.015,0-.027-.008-.042-.008h0L5.989,11a1,1,0,0,0,0,2h0l15.678-.032c.028,0,.051-.014.078-.016a2,2,0,0,1-.334.462l-3.879,3.879a1,1,0,1,0,1.414,1.414l3.879-3.879a4,4,0,0,0,0-5.656Z"/><path d="M7,22H5a3,3,0,0,1-3-3V5A3,3,0,0,1,5,2H7A1,1,0,0,0,7,0H5A5.006,5.006,0,0,0,0,5V19a5.006,5.006,0,0,0,5,5H7a1,1,0,0,0,0-2Z"/></svg>',
				'url'        => esc_url( wp_logout_url( home_url() ) ),
				'pos'        => 30,
				'permission' => 'manage_woocommerce',
				'target'     => '_self',
			),
		);

		return apply_filters( 'storesuite_dashboard_menus', $menus );
	}

	/**
	 * Retrieves the currently active menu based on the current request URL.
	 *
	 * @return string The slug of the currently active menu item, such as 'dashboard' or 'products'.
	 */
	public function get_active_menu() {
		global $wp;

		$request = $wp->request;
		$active  = explode( '/', $request );

		unset( $active[0] );

		if ( $active ) {
			$active_menu = implode( '/', $active );

			if ( $active_menu === 'new-product' ) {
				$active_menu = 'products';
			}

			if ( get_query_var( 'edit' ) && is_singular( 'product' ) ) {
				$active_menu = 'products';
			}
		} else {
			$active_menu = 'dashboard';
		}
		return $active_menu;
	}
}
