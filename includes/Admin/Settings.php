<?php

namespace PluginizeLab\ShopFront\Admin;

/**
 * Plugin admin page settings class
 */
class Settings {
	/**
	 * The constructor.
	 */
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'add_admin_settings_menu' ), 100 );
	}

	/**
	 * Register settings main menu
	 *
	 * @return void
	 */
	public function add_admin_settings_menu() {
		add_submenu_page(
			'woocommerce',
			'ShopFront Settings',
			'ShopFront',
			'manage_options',
			'shop-front',
			array( $this, 'settings_page_content' ),
		);
	}

	/**
	 * Plugin settings page
	 *
	 * @return void
	 */
	public function settings_page_content() {
		?>
		<div class="wrap">
			<div id="shop-front-settings"></div>
		</div>
		<?php
	}
}