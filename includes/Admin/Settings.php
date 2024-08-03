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
		add_action( 'admin_menu', array( $this, 'add_admin_settings_menu' ) );
	}

	/**
	 * Register settings main menu
	 *
	 * @return void
	 */
	public function add_admin_settings_menu() {
		add_menu_page(
			'ShopFront Settings',
			'ShopFront',
			'manage_options',
			'shop-front',
			array( $this, 'settings_page_content' )
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
			<h1><?php echo esc_html__( 'ShopFront Settings', 'shop-front' ); ?></h1>
			<div id="shop-front-settings"></div>
		</div>
		<?php
	}
}