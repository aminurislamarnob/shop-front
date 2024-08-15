<?php

namespace PluginizeLab\ShopFront;

class DashboardMenu {
	/**
	 * The constructor.
	 */
	public function __construct() {
		add_action( 'msf_dashboard_navigation', array( $this, 'add_dashboard_navigations' ) );
	}

	public function add_dashboard_navigations() {
		$menus = $this->get_dashboard_menus();
		echo '<ul class="dashboard-menu">';

		foreach ( $menus as $key => $menu ) {
			// Check if the current user has permission to view this menu item
			if ( current_user_can( $menu['permission'] ) ) {
				echo '<li>';
				echo '<a href="' . esc_url( $menu['url'] ) . '">';
				echo $menu['icon'] . ' ' . esc_html( $menu['title'] );
				echo '</a>';

				// Check for and render submenu
				if ( isset( $menu['submenu'] ) && is_array( $menu['submenu'] ) ) {
					echo '<ul class="submenu">';
					foreach ( $menu['submenu'] as $subkey => $submenu ) {
						// Check if the current user has permission to view this submenu item
						if ( current_user_can( $submenu['permission'] ) ) {
							echo '<li>';
							echo '<a href="' . esc_url( $submenu['url'] ) . '">';
							echo $submenu['icon'] . ' ' . esc_html( $submenu['title'] );
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
	 * Get Dashboard Navigation menus
	 *
	 * @return array
	 */
	public function get_dashboard_menus(): array {
		$menus = array(
			'dashboard' => array(
				'title'      => __( 'Dashboard', 'dokan-lite' ),
				'icon'       => '<i class="fas fa-tachometer-alt"></i>',
				'url'        => dokan_get_navigation_url(),
				'pos'        => 10,
				'permission' => 'dokan_view_overview_menu',
			),
			'products'  => array(
				'title'      => __( 'Products', 'dokan-lite' ),
				'icon'       => '<i class="fas fa-briefcase"></i>',
				'url'        => dokan_get_navigation_url( 'products' ),
				'pos'        => 30,
				'permission' => 'dokan_view_product_menu',
			),
			'orders'    => array(
				'title'      => __( 'Orders', 'dokan-lite' ),
				'icon'       => '<i class="fas fa-shopping-cart"></i>',
				'url'        => dokan_get_navigation_url( 'orders' ),
				'pos'        => 50,
				'permission' => 'dokan_view_order_menu',
			),
			'withdraw'  => array(
				'title'      => __( 'Withdraw', 'dokan-lite' ),
				'icon'       => '<i class="fas fa-upload"></i>',
				'url'        => dokan_get_navigation_url( 'withdraw' ),
				'pos'        => 70,
				'permission' => 'dokan_view_withdraw_menu',
			),
			'settings'  => array(
				'title'   => __( 'Settings', 'dokan-lite' ),
				'icon'    => '<i class="fas fa-cog"></i>',
				'url'     => dokan_get_navigation_url( 'settings/store' ),
				'pos'     => 200,
				'submenu' => array(
					'store'   => array(
						'title'      => __( 'Store', 'dokan-lite' ),
						'icon'       => '<i class="fas fa-university"></i>',
						'url'        => dokan_get_navigation_url( 'settings/store' ),
						'pos'        => 30,
						'permission' => 'dokan_view_store_settings_menu',
					),
					'payment' => array(
						'title'      => __( 'Payment', 'dokan-lite' ),
						'icon'       => '<i class="far fa-credit-card"></i>',
						'url'        => dokan_get_navigation_url( 'settings/payment' ),
						'pos'        => 50,
						'permission' => 'dokan_view_store_payment_menu',
					),
				),
			),
		);

		return apply_filters( 'msf_dashboard_menus', $menus );
	}
}
