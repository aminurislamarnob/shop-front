<?php

namespace PluginizeLab\ShopFront;

class Assets {
	/**
	 * The constructor.
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'register_all_scripts' ), 10 );

		if ( is_admin() ) {
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ), 10 );
		} else {
			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_front_scripts' ) );
		}
	}

	/**
	 * Register all Dokan scripts and styles.
	 *
	 * @return void
	 */
	public function register_all_scripts() {
		$this->register_styles();
		$this->register_scripts();
	}

	/**
	 * Register scripts.
	 *
	 * @param array $scripts
	 *
	 * @return void
	 */
	public function register_scripts() {
		$admin_script                 = SHOP_FRONT_PLUGIN_ASSET . '/admin/script.js';
		$frontend_script              = SHOP_FRONT_PLUGIN_ASSET . '/frontend/script.js';
		$frontend_form_handler_script = SHOP_FRONT_PLUGIN_ASSET . '/frontend/form-handler.js';
		$frontend_alpinejs_script     = 'https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js';
		$frontend_sweetalert2         = SHOP_FRONT_PLUGIN_ASSET . '/frontend/library/sweetalert2.min.js';

		wp_register_script( 'my_shop_front_admin_script', $admin_script, array( 'my_shop_front-block-editor-script' ), filemtime( SHOP_FRONT_DIR . '/assets/admin/script.js' ), true );
		wp_register_script( 'my_shop_front_script', $frontend_script, array(), filemtime( SHOP_FRONT_DIR . '/assets/frontend/script.js' ), true );

		// Dashboard scripts.
		wp_register_script( 'my_shop_front_form_handler_script', $frontend_form_handler_script, array( 'my_shop_front_alpinejs' ), filemtime( SHOP_FRONT_DIR . '/assets/frontend/form-handler.js' ), true );
		wp_register_script( 'my_shop_front_alpinejs', $frontend_alpinejs_script, array(), '3.x.x', true );
		wp_register_script( 'my_shop_front_sweetalert2_script', $frontend_sweetalert2, array(), '11.14.5', true );
	}

	/**
	 * Register styles.
	 *
	 * @return void
	 */
	public function register_styles() {
		$admin_style                = SHOP_FRONT_PLUGIN_ASSET . '/admin/style.css';
		$frontend_style             = SHOP_FRONT_PLUGIN_ASSET . '/frontend/style.css';
		$bs_grid_style              = SHOP_FRONT_PLUGIN_ASSET . '/frontend/bootstrap-grid.min.css';
		$frontend_sweetalert2_style = SHOP_FRONT_PLUGIN_ASSET . '/frontend/library/sweetalert2.min.css';

		wp_register_style( 'my_shop_front_admin_style', $admin_style, array(), filemtime( SHOP_FRONT_DIR . '/assets/admin/style.css' ) );
		wp_register_style( 'my_shop_front_style', $frontend_style, array(), filemtime( SHOP_FRONT_DIR . '/assets/frontend/style.css' ) );
		wp_register_style( 'my_shop_front_bs_grid', $bs_grid_style, array(), filemtime( SHOP_FRONT_DIR . '/assets/frontend/bootstrap-grid.min.css' ) );

		wp_register_style( 'my_shop_front_sweetalert2_style', $frontend_sweetalert2_style, array(), '11.14.5' );
	}

	/**
	 * Enqueue admin scripts.
	 *
	 * @return void
	 */
	public function enqueue_admin_scripts() {
		wp_enqueue_script( 'my_shop_front_admin_script' );

		$page = get_current_screen();
		if ( 'woocommerce_page_shop-front' == $page->id ) {
			$asset_file = include SHOP_FRONT_DIR . '/assets/build/admin/script.asset.php';

			wp_enqueue_script(
				'shop-front-admin-page',
				SHOP_FRONT_PLUGIN_ASSET . '/build/admin/script.js',
				$asset_file['dependencies'],
				$asset_file['version'],
				true
			);

			wp_enqueue_style(
				'shop-front-admin-styles',
				SHOP_FRONT_PLUGIN_ASSET . '/build/admin.css',
				array( 'wp-components' ),
				$asset_file['version'] ?? null,
			);

			wp_enqueue_style( 'wp-components' );
		}
	}

	/**
	 * Enqueue front-end scripts.
	 *
	 * @return void
	 */
	public function enqueue_front_scripts() {
		wp_enqueue_style( 'my_shop_front_style' );
		wp_enqueue_style( 'my_shop_front_bs_grid' );
		wp_enqueue_script( 'my_shop_front_script' );
		wp_localize_script(
			'my_shop_front_script',
			'My_Shop_Front',
			array()
		);

		if ( is_msf_dashboard_page() ) {
			wp_enqueue_style( 'my_shop_front_sweetalert2_style' );
			wp_enqueue_script( 'my_shop_front_sweetalert2_script' );
			wp_enqueue_script( 'my_shop_front_alpinejs' );
			add_filter(
				'script_loader_tag',
				function ( $tag, $handle ) {
					if ( 'my_shop_front_alpinejs' !== $handle ) {
						return $tag;
					}
					return str_replace( ' src', ' defer="defer" src', $tag );
				},
				10,
				2
			);

			wp_enqueue_script( 'my_shop_front_form_handler_script' );
			wp_localize_script(
				'my_shop_front_form_handler_script',
				'My_Shop_Front_Form_Handler',
				array(
					'ajax_url'              => admin_url( 'admin-ajax.php' ),
					'category_delete_nonce' => wp_create_nonce( '_msfc_delete_product_category_' ),
				)
			);
		}
	}
}
