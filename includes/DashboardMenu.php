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
				'icon'       => '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-speedometer2" viewBox="0 0 16 16"><path d="M8 4a.5.5 0 0 1 .5.5V6a.5.5 0 0 1-1 0V4.5A.5.5 0 0 1 8 4M3.732 5.732a.5.5 0 0 1 .707 0l.915.914a.5.5 0 1 1-.708.708l-.914-.915a.5.5 0 0 1 0-.707M2 10a.5.5 0 0 1 .5-.5h1.586a.5.5 0 0 1 0 1H2.5A.5.5 0 0 1 2 10m9.5 0a.5.5 0 0 1 .5-.5h1.5a.5.5 0 0 1 0 1H12a.5.5 0 0 1-.5-.5m.754-4.246a.39.39 0 0 0-.527-.02L7.547 9.31a.91.91 0 1 0 1.302 1.258l3.434-4.297a.39.39 0 0 0-.029-.518z"/><path fill-rule="evenodd" d="M0 10a8 8 0 1 1 15.547 2.661c-.442 1.253-1.845 1.602-2.932 1.25C11.309 13.488 9.475 13 8 13c-1.474 0-3.31.488-4.615.911-1.087.352-2.49.003-2.932-1.25A8 8 0 0 1 0 10m8-7a7 7 0 0 0-6.603 9.329c.203.575.923.876 1.68.63C4.397 12.533 6.358 12 8 12s3.604.532 4.923.96c.757.245 1.477-.056 1.68-.631A7 7 0 0 0 8 3"/></svg>',
				'url'        => msfc_get_navigation_url(),
				'pos'        => 10,
				'permission' => 'manage_woocommerce',
			),
			'products'   => array(
				'title'      => __( 'Products', 'shop-front' ),
				'icon'       => '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-archive" viewBox="0 0 16 16"><path d="M0 2a1 1 0 0 1 1-1h14a1 1 0 0 1 1 1v2a1 1 0 0 1-1 1v7.5a2.5 2.5 0 0 1-2.5 2.5h-9A2.5 2.5 0 0 1 1 12.5V5a1 1 0 0 1-1-1zm2 3v7.5A1.5 1.5 0 0 0 3.5 14h9a1.5 1.5 0 0 0 1.5-1.5V5zm13-3H1v2h14zM5 7.5a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5"/></svg>',
				'url'        => msfc_get_navigation_url( 'products' ),
				'pos'        => 30,
				'permission' => 'manage_woocommerce',
			),
			'orders'     => array(
				'title'      => __( 'Orders', 'shop-front' ),
				'icon'       => '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-basket" viewBox="0 0 16 16"><path d="M5.757 1.071a.5.5 0 0 1 .172.686L3.383 6h9.234L10.07 1.757a.5.5 0 1 1 .858-.514L13.783 6H15a1 1 0 0 1 1 1v1a1 1 0 0 1-1 1v4.5a2.5 2.5 0 0 1-2.5 2.5h-9A2.5 2.5 0 0 1 1 13.5V9a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1h1.217L5.07 1.243a.5.5 0 0 1 .686-.172zM2 9v4.5A1.5 1.5 0 0 0 3.5 15h9a1.5 1.5 0 0 0 1.5-1.5V9zM1 7v1h14V7zm3 3a.5.5 0 0 1 .5.5v3a.5.5 0 0 1-1 0v-3A.5.5 0 0 1 4 10m2 0a.5.5 0 0 1 .5.5v3a.5.5 0 0 1-1 0v-3A.5.5 0 0 1 6 10m2 0a.5.5 0 0 1 .5.5v3a.5.5 0 0 1-1 0v-3A.5.5 0 0 1 8 10m2 0a.5.5 0 0 1 .5.5v3a.5.5 0 0 1-1 0v-3a.5.5 0 0 1 .5-.5m2 0a.5.5 0 0 1 .5.5v3a.5.5 0 0 1-1 0v-3a.5.5 0 0 1 .5-.5"/></svg>',
				'url'        => msfc_get_navigation_url( 'orders' ),
				'pos'        => 50,
				'permission' => 'manage_woocommerce',
			),
			'categories' => array(
				'title'      => __( 'Categories', 'shop-front' ),
				'icon'       => '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-diagram-3" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M6 3.5A1.5 1.5 0 0 1 7.5 2h1A1.5 1.5 0 0 1 10 3.5v1A1.5 1.5 0 0 1 8.5 6v1H14a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-1 0V8h-5v.5a.5.5 0 0 1-1 0V8h-5v.5a.5.5 0 0 1-1 0v-1A.5.5 0 0 1 2 7h5.5V6A1.5 1.5 0 0 1 6 4.5zM8.5 5a.5.5 0 0 0 .5-.5v-1a.5.5 0 0 0-.5-.5h-1a.5.5 0 0 0-.5.5v1a.5.5 0 0 0 .5.5zM0 11.5A1.5 1.5 0 0 1 1.5 10h1A1.5 1.5 0 0 1 4 11.5v1A1.5 1.5 0 0 1 2.5 14h-1A1.5 1.5 0 0 1 0 12.5zm1.5-.5a.5.5 0 0 0-.5.5v1a.5.5 0 0 0 .5.5h1a.5.5 0 0 0 .5-.5v-1a.5.5 0 0 0-.5-.5zm4.5.5A1.5 1.5 0 0 1 7.5 10h1a1.5 1.5 0 0 1 1.5 1.5v1A1.5 1.5 0 0 1 8.5 14h-1A1.5 1.5 0 0 1 6 12.5zm1.5-.5a.5.5 0 0 0-.5.5v1a.5.5 0 0 0 .5.5h1a.5.5 0 0 0 .5-.5v-1a.5.5 0 0 0-.5-.5zm4.5.5a1.5 1.5 0 0 1 1.5-1.5h1a1.5 1.5 0 0 1 1.5 1.5v1a1.5 1.5 0 0 1-1.5 1.5h-1a1.5 1.5 0 0 1-1.5-1.5zm1.5-.5a.5.5 0 0 0-.5.5v1a.5.5 0 0 0 .5.5h1a.5.5 0 0 0 .5-.5v-1a.5.5 0 0 0-.5-.5z"/></svg>',
				'url'        => msfc_get_navigation_url( 'categories' ),
				'pos'        => 30,
				'permission' => 'manage_woocommerce',
			),
			'tags'       => array(
				'title'      => __( 'Tags', 'shop-front' ),
				'icon'       => '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-tags" viewBox="0 0 16 16"><path d="M3 2v4.586l7 7L14.586 9l-7-7zM2 2a1 1 0 0 1 1-1h4.586a1 1 0 0 1 .707.293l7 7a1 1 0 0 1 0 1.414l-4.586 4.586a1 1 0 0 1-1.414 0l-7-7A1 1 0 0 1 2 6.586z"/><path d="M5.5 5a.5.5 0 1 1 0-1 .5.5 0 0 1 0 1m0 1a1.5 1.5 0 1 0 0-3 1.5 1.5 0 0 0 0 3M1 7.086a1 1 0 0 0 .293.707L8.75 15.25l-.043.043a1 1 0 0 1-1.414 0l-7-7A1 1 0 0 1 0 7.586V3a1 1 0 0 1 1-1z"/></svg>',
				'url'        => msfc_get_navigation_url( 'tags' ),
				'pos'        => 30,
				'permission' => 'manage_woocommerce',
			),
			'home'       => array(
				'title'      => __( 'Visit Store', 'shop-front' ),
				'icon'       => '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-shop-window" viewBox="0 0 16 16"><path d="M2.97 1.35A1 1 0 0 1 3.73 1h8.54a1 1 0 0 1 .76.35l2.609 3.044A1.5 1.5 0 0 1 16 5.37v.255a2.375 2.375 0 0 1-4.25 1.458A2.37 2.37 0 0 1 9.875 8 2.37 2.37 0 0 1 8 7.083 2.37 2.37 0 0 1 6.125 8a2.37 2.37 0 0 1-1.875-.917A2.375 2.375 0 0 1 0 5.625V5.37a1.5 1.5 0 0 1 .361-.976zm1.78 4.275a1.375 1.375 0 0 0 2.75 0 .5.5 0 0 1 1 0 1.375 1.375 0 0 0 2.75 0 .5.5 0 0 1 1 0 1.375 1.375 0 1 0 2.75 0V5.37a.5.5 0 0 0-.12-.325L12.27 2H3.73L1.12 5.045A.5.5 0 0 0 1 5.37v.255a1.375 1.375 0 0 0 2.75 0 .5.5 0 0 1 1 0M1.5 8.5A.5.5 0 0 1 2 9v6h12V9a.5.5 0 0 1 1 0v6h.5a.5.5 0 0 1 0 1H.5a.5.5 0 0 1 0-1H1V9a.5.5 0 0 1 .5-.5m2 .5a.5.5 0 0 1 .5.5V13h8V9.5a.5.5 0 0 1 1 0V13a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V9.5a.5.5 0 0 1 .5-.5"/></svg>',
				'url'        => wc_get_page_permalink( 'shop' ),
				'pos'        => 30,
				'permission' => 'manage_woocommerce',
			),
			'logout'     => array(
				'title'      => __( 'Logout', 'shop-front' ),
				'icon'       => '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-escape" viewBox="0 0 16 16"><path d="M8.538 1.02a.5.5 0 1 0-.076.998 6 6 0 1 1-6.445 6.444.5.5 0 0 0-.997.076A7 7 0 1 0 8.538 1.02"/><path d="M7.096 7.828a.5.5 0 0 0 .707-.707L2.707 2.025h2.768a.5.5 0 1 0 0-1H1.5a.5.5 0 0 0-.5.5V5.5a.5.5 0 0 0 1 0V2.732z"/></svg>',
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
