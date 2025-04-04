<?php

namespace PluginizeLab\ShopFront;

class DashboardMenu {
	/**
	 * The constructor.
	 */
	public function __construct() {
		add_action( 'msf_dashboard_navigation', array( $this, 'add_dashboard_navigations' ) );
	}

	/**
	 * Adds dashboard navigation menus to the admin dashboard.
	 *
	 * This method retrieves the dashboard menus and the URL of the currently active menu,
	 * then outputs the HTML for the menu list, including submenus if present.
	 */
	public function add_dashboard_navigations() {
		$menus       = $this->get_dashboard_menus();
		$current_url = msfc_get_navigation_url( $this->get_active_menu() );

		echo '<ul class="msfc-dashboard-menu">';

		foreach ( $menus as $key => $menu ) {
			// Check if the current user has permission to view this menu item.
			if ( current_user_can( $menu['permission'] ) ) {
				$active_class = ( $current_url === $menu['url'] ) ? ' class=active' : '';

				echo '<li>';
				echo '<a href="' . esc_url( $menu['url'] ) . '"' . esc_attr( $active_class ) . '>';
				echo wp_kses(
					$menu['icon'],
					array(
						'i'    => array( 'class' => array() ),
						'svg'  => array(
							'xmlns'   => array(),
							'width'   => array(),
							'height'  => array(),
							'fill'    => array(),
							'class'   => array(),
							'viewBox' => array(),
						),
						'path' => array(
							'd'         => array(),
							'fill-rule' => array(),
						),
					)
				) . ' ' . esc_html( $menu['title'] );
				echo '</a>';

				// Check for and render submenu.
				if ( isset( $menu['submenu'] ) && is_array( $menu['submenu'] ) ) {
					echo '<ul class="submenu">';
					foreach ( $menu['submenu'] as $subkey => $submenu ) {
						// Check if the current user has permission to view this submenu item.
						if ( current_user_can( $submenu['permission'] ) ) {
							echo '<li>';
							echo '<a href="' . esc_url( $submenu['url'] ) . '">';
							echo wp_kses(
								$submenu['icon'],
								array(
									'i'    => array( 'class' => array() ),
									'svg'  => array(
										'xmlns'   => array(),
										'width'   => array(),
										'height'  => array(),
										'fill'    => array(),
										'class'   => array(),
										'viewBox' => array(),
									),
									'path' => array(
										'd'         => array(),
										'fill-rule' => array(),
									),
								)
							) . ' ' . esc_html( $submenu['title'] );
							echo '</a>';
							echo '</li>';
						}
					}
					echo '</ul>';
				}

				echo '</li>';
			}
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
			'dashboard'  => array(
				'title'      => __( 'Dashboard', 'shop-front' ),
				'icon'       => '<svg xmlns="http://www.w3.org/2000/svg" id="Layer_1" data-name="Layer 1" viewBox="0 0 24 24" width="24" height="24"><path d="M24,13a11.914,11.914,0,0,1-3.508,8.47,3.037,3.037,0,0,1-4.12.174l-1.026-.887a1,1,0,0,1,1.308-1.514l1.027.888a1.014,1.014,0,0,0,1.395-.075,10.044,10.044,0,0,0-.414-14.513,9.9,9.9,0,0,0-7.823-2.478A9.992,9.992,0,0,0,4.962,20.094a1,1,0,0,0,1.357.038l1.027-.889a1,1,0,0,1,1.308,1.514l-1.026.888a3.016,3.016,0,0,1-4.073-.129A12,12,0,1,1,24,13ZM17.707,8.707a1,1,0,0,0-1.414-1.414l-3.775,3.775a2,2,0,1,0,1.414,1.414Z"/></svg>',
				'url'        => msfc_get_navigation_url(),
				'pos'        => 10,
				'permission' => 'manage_woocommerce',
			),
			'products'   => array(
				'title'      => __( 'Products', 'shop-front' ),
				'icon'       => '<svg xmlns="http://www.w3.org/2000/svg" id="Layer_1" data-name="Layer 1" viewBox="0 0 24 24" width="24" height="24"><path d="M19.5,16c0,.553-.447,1-1,1h-2c-.553,0-1-.447-1-1s.447-1,1-1h2c.553,0,1,.447,1,1Zm4.5-1v5c0,2.206-1.794,4-4,4H4c-2.206,0-4-1.794-4-4v-5c0-2.206,1.794-4,4-4h1V4C5,1.794,6.794,0,9,0h6c2.206,0,4,1.794,4,4v7h1c2.206,0,4,1.794,4,4ZM7,11h10V4c0-1.103-.897-2-2-2h-6c-1.103,0-2,.897-2,2v7Zm-3,11h7V13H4c-1.103,0-2,.897-2,2v5c0,1.103,.897,2,2,2Zm18-7c0-1.103-.897-2-2-2h-7v9h7c1.103,0,2-.897,2-2v-5Zm-14.5,0h-2c-.553,0-1,.447-1,1s.447,1,1,1h2c.553,0,1-.447,1-1s-.447-1-1-1ZM14,5c0-.553-.447-1-1-1h-2c-.553,0-1,.447-1,1s.447,1,1,1h2c.553,0,1-.447,1-1Z"/></svg>',
				'url'        => msfc_get_navigation_url( 'products' ),
				'pos'        => 30,
				'permission' => 'manage_woocommerce',
			),
			'orders'     => array(
				'title'      => __( 'Orders', 'shop-front' ),
				'icon'       => '<svg xmlns="http://www.w3.org/2000/svg" id="Layer_1" data-name="Layer 1" viewBox="0 0 24 24" width="24" height="24">
				<path d="M22,14c0,.553-.448,1-1,1H6.737c.416,1.174,1.528,2,2.82,2h9.443c.552,0,1,.447,1,1s-.448,1-1,1H9.557c-2.535,0-4.67-1.898-4.966-4.415L3.215,2.884c-.059-.504-.486-.884-.993-.884H1c-.552,0-1-.447-1-1S.448,0,1,0h1.222c1.521,0,2.802,1.139,2.979,2.649l.041,.351h3.758c.552,0,1,.447,1,1s-.448,1-1,1h-3.522l.941,8h14.581c.552,0,1,.447,1,1Zm-15,6c-1.105,0-2,.895-2,2s.895,2,2,2,2-.895,2-2-.895-2-2-2Zm10,0c-1.105,0-2,.895-2,2s.895,2,2,2,2-.895,2-2-.895-2-2-2Zm2-14.414v-1.586c0-.553-.448-1-1-1s-1,.447-1,1v2c0,.266,.105,.52,.293,.707l1,1c.195,.195,.451,.293,.707,.293s.512-.098,.707-.293c.391-.391,.391-1.023,0-1.414l-.707-.707Zm5,.414c0,3.309-2.691,6-6,6s-6-2.691-6-6S14.691,0,18,0s6,2.691,6,6Zm-2,0c0-2.206-1.794-4-4-4s-4,1.794-4,4,1.794,4,4,4,4-1.794,4-4Z"/>
				</svg>',
				'url'        => msfc_get_navigation_url( 'orders' ),
				'pos'        => 50,
				'permission' => 'manage_woocommerce',
			),
			'categories' => array(
				'title'      => __( 'Categories', 'shop-front' ),
				'icon'       => '<svg xmlns="http://www.w3.org/2000/svg" id="Outline" viewBox="0 0 24 24" width="24" height="24"><path d="M7,0H4A4,4,0,0,0,0,4V7a4,4,0,0,0,4,4H7a4,4,0,0,0,4-4V4A4,4,0,0,0,7,0ZM9,7A2,2,0,0,1,7,9H4A2,2,0,0,1,2,7V4A2,2,0,0,1,4,2H7A2,2,0,0,1,9,4Z"/><path d="M20,0H17a4,4,0,0,0-4,4V7a4,4,0,0,0,4,4h3a4,4,0,0,0,4-4V4A4,4,0,0,0,20,0Zm2,7a2,2,0,0,1-2,2H17a2,2,0,0,1-2-2V4a2,2,0,0,1,2-2h3a2,2,0,0,1,2,2Z"/><path d="M7,13H4a4,4,0,0,0-4,4v3a4,4,0,0,0,4,4H7a4,4,0,0,0,4-4V17A4,4,0,0,0,7,13Zm2,7a2,2,0,0,1-2,2H4a2,2,0,0,1-2-2V17a2,2,0,0,1,2-2H7a2,2,0,0,1,2,2Z"/><path d="M20,13H17a4,4,0,0,0-4,4v3a4,4,0,0,0,4,4h3a4,4,0,0,0,4-4V17A4,4,0,0,0,20,13Zm2,7a2,2,0,0,1-2,2H17a2,2,0,0,1-2-2V17a2,2,0,0,1,2-2h3a2,2,0,0,1,2,2Z"/></svg>',
				'url'        => msfc_get_navigation_url( 'categories' ),
				'pos'        => 30,
				'permission' => 'manage_woocommerce',
			),
			'tags'       => array(
				'title'      => __( 'Tags', 'shop-front' ),
				'icon'       => '<svg xmlns="http://www.w3.org/2000/svg" id="Layer_1" data-name="Layer 1" viewBox="0 0 24 24" width="24" height="24"><path d="M7.707,9.256c.391,.391,.391,1.024,0,1.414-.391,.391-1.024,.391-1.414,0-.391-.391-.391-1.024,0-1.414,.391-.391,1.024-.391,1.414,0Zm13.852,6.085l-.565,.565c-.027,1.233-.505,2.457-1.435,3.399l-3.167,3.208c-.943,.955-2.201,1.483-3.543,1.487h-.017c-1.335,0-2.59-.52-3.534-1.464L1.882,15.183c-.65-.649-.964-1.542-.864-2.453l.765-6.916c.051-.456,.404-.819,.858-.881l6.889-.942c.932-.124,1.87,.193,2.528,.851l7.475,7.412c.387,.387,.697,.823,.931,1.288,.812-1.166,.698-2.795-.342-3.835L12.531,2.302c-.229-.229-.545-.335-.851-.292l-6.889,.942c-.549,.074-1.052-.309-1.127-.855-.074-.547,.309-1.051,.855-1.126L11.409,.028c.921-.131,1.869,.191,2.528,.852l7.589,7.405c1.946,1.945,1.957,5.107,.032,7.057Zm-3.438-1.67l-7.475-7.412c-.223-.223-.536-.326-.847-.287l-6.115,.837-.679,6.14c-.033,.303,.071,.601,.287,.816l7.416,7.353c.569,.57,1.322,.881,2.123,.881h.01c.806-.002,1.561-.319,2.126-.893l3.167-3.208c1.155-1.17,1.149-3.067-.014-4.229Z"/></svg>',
				'url'        => msfc_get_navigation_url( 'tags' ),
				'pos'        => 30,
				'permission' => 'manage_woocommerce',
			),
			'home'       => array(
				'title'      => __( 'Visit Home', 'shop-front' ),
				'icon'       => '<svg xmlns="http://www.w3.org/2000/svg" id="Outline" viewBox="0 0 24 24" width="24" height="24"><path d="M23.121,9.069,15.536,1.483a5.008,5.008,0,0,0-7.072,0L.879,9.069A2.978,2.978,0,0,0,0,11.19v9.817a3,3,0,0,0,3,3H21a3,3,0,0,0,3-3V11.19A2.978,2.978,0,0,0,23.121,9.069ZM15,22.007H9V18.073a3,3,0,0,1,6,0Zm7-1a1,1,0,0,1-1,1H17V18.073a5,5,0,0,0-10,0v3.934H3a1,1,0,0,1-1-1V11.19a1.008,1.008,0,0,1,.293-.707L9.878,2.9a3.008,3.008,0,0,1,4.244,0l7.585,7.586A1.008,1.008,0,0,1,22,11.19Z"/></svg>',
				'url'        => wc_get_page_permalink( 'shop' ),
				'pos'        => 30,
				'permission' => 'manage_woocommerce',
			),
			'logout'     => array(
				'title'      => __( 'Logout', 'shop-front' ),
				'icon'       => '<svg xmlns="http://www.w3.org/2000/svg" id="Outline" viewBox="0 0 24 24" width="24" height="24"><path d="M22.829,9.172,18.95,5.293a1,1,0,0,0-1.414,1.414l3.879,3.879a2.057,2.057,0,0,1,.3.39c-.015,0-.027-.008-.042-.008h0L5.989,11a1,1,0,0,0,0,2h0l15.678-.032c.028,0,.051-.014.078-.016a2,2,0,0,1-.334.462l-3.879,3.879a1,1,0,1,0,1.414,1.414l3.879-3.879a4,4,0,0,0,0-5.656Z"/><path d="M7,22H5a3,3,0,0,1-3-3V5A3,3,0,0,1,5,2H7A1,1,0,0,0,7,0H5A5.006,5.006,0,0,0,0,5V19a5.006,5.006,0,0,0,5,5H7a1,1,0,0,0,0-2Z"/></svg>',
				'url'        => '',
				'pos'        => 30,
				'permission' => 'manage_woocommerce',
			),
			// 'settings'  => array(
			// 'title'      => __( 'Settings', 'shop-front' ),
			// 'icon'       => '<i class="fas fa-cog"></i>',
			// 'url'        => dokan_get_navigation_url( 'settings/store' ),
			// 'pos'        => 200,
			// 'permission' => 'manage_woocommerce',
			// 'submenu'    => array(
			// 'store'   => array(
			// 'title'      => __( 'Store', 'shop-front' ),
			// 'icon'       => '<i class="fas fa-university"></i>',
			// 'url'        => dokan_get_navigation_url( 'settings/store' ),
			// 'pos'        => 30,
			// 'permission' => 'manage_woocommerce',
			// ),
			// 'payment' => array(
			// 'title'      => __( 'Payment', 'shop-front' ),
			// 'icon'       => '<i class="far fa-credit-card"></i>',
			// 'url'        => dokan_get_navigation_url( 'settings/payment' ),
			// 'pos'        => 50,
			// 'permission' => 'manage_woocommerce',
			// ),
			// ),
			// ),
		);

		return apply_filters( 'msf_dashboard_menus', $menus );
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
