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
		$frontend_order_script        = SHOP_FRONT_PLUGIN_ASSET . '/frontend/order.js';
		$frontend_form_handler_script = SHOP_FRONT_PLUGIN_ASSET . '/frontend/form-handler.js';
		$frontend_alpinejs_script     = 'https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js';
		$frontend_sweetalert2         = SHOP_FRONT_PLUGIN_ASSET . '/frontend/library/sweetalert2.min.js';

		wp_register_script( 'my_shop_front_admin_script', $admin_script, array( 'my_shop_front-block-editor-script' ), SHOP_FRONT_PLUGIN_VERSION, true );
		wp_register_script( 'my_shop_front_script', $frontend_script, array(), SHOP_FRONT_PLUGIN_VERSION, true );

		// Dashboard scripts.
		wp_register_script( 'my_shop_front_form_handler_script', $frontend_form_handler_script, array( 'my_shop_front_alpinejs' ), filemtime( SHOP_FRONT_DIR . '/assets/frontend/form-handler.js' ), true );
		wp_register_script( 'my_shop_front_alpinejs', $frontend_alpinejs_script, array(), '3.x.x', true );
		wp_register_script( 'my_shop_front_sweetalert2_script', $frontend_sweetalert2, array(), '11.14.5', true );
		
		// Order scripts.
		wp_register_script( 'my_shop_front_order_script', $frontend_order_script, array('my_shop_front_selectWoo'), SHOP_FRONT_PLUGIN_VERSION, true );
		wp_register_script( 'my_shop_front_selectWoo', WC()->plugin_url() . '/assets/js/selectWoo/selectWoo.full.js', array( 'jquery' ), '4.0.3', true );
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
		wp_register_style( 'my_shop_front_poppins', 'https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,400;0,500;0,600;0,700;1,400;1,500;1,600&display=swap', array() );
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
		if ( is_msf_dashboard_page() ) {
			wp_enqueue_style( 'select2' );
			wp_enqueue_style( 'my_shop_front_style' );
			wp_enqueue_style( 'my_shop_front_bs_grid' );
			wp_enqueue_script( 'my_shop_front_script' );

			wp_enqueue_style( 'my_shop_front_poppins' );
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
					'ajax_url'               => admin_url( 'admin-ajax.php' ),
					'msfc_woo_delete_nonce_' => wp_create_nonce( '_msfc_delete_nonce_' ),
				)
			);
			wp_enqueue_media();


			// Order styles and scripts.
			wp_enqueue_script( 'my_shop_front_selectWoo' );
			wp_enqueue_script( 'my_shop_front_order_script' );

			global $theorder;
			$order_id = \Automattic\WooCommerce\Utilities\OrderUtil::get_post_or_order_id( $theorder ) ;
			$default_location = wc_get_customer_default_location();

			wp_localize_script(
				'my_shop_front_order_script',
				'My_Shop_Front_Order',
				array(
					'i18n_no_matches'                 => _x( 'No matches found', 'enhanced select', 'woocommerce' ),
					'i18n_ajax_error'                 => _x( 'Loading failed', 'enhanced select', 'woocommerce' ),
					'i18n_input_too_short_1'          => _x( 'Please enter 1 or more characters', 'enhanced select', 'woocommerce' ),
					'i18n_input_too_short_n'          => _x( 'Please enter %qty% or more characters', 'enhanced select', 'woocommerce' ),
					'i18n_input_too_long_1'           => _x( 'Please delete 1 character', 'enhanced select', 'woocommerce' ),
					'i18n_input_too_long_n'           => _x( 'Please delete %qty% characters', 'enhanced select', 'woocommerce' ),
					'i18n_selection_too_long_1'       => _x( 'You can only select 1 item', 'enhanced select', 'woocommerce' ),
					'i18n_selection_too_long_n'       => _x( 'You can only select %qty% items', 'enhanced select', 'woocommerce' ),
					'i18n_load_more'                  => _x( 'Loading more results&hellip;', 'enhanced select', 'woocommerce' ),
					'i18n_searching'                  => _x( 'Searching&hellip;', 'enhanced select', 'woocommerce' ),
					'ajax_url'                        => admin_url( 'admin-ajax.php' ),
					'search_products_nonce'           => wp_create_nonce( 'search-products' ),
					'search_customers_nonce'          => wp_create_nonce( 'search-customers' ),
					'search_categories_nonce'         => wp_create_nonce( 'search-categories' ),
					'search_taxonomy_terms_nonce'     => wp_create_nonce( 'search-taxonomy-terms' ),
					'search_product_attributes_nonce' => wp_create_nonce( 'search-product-attributes' ),
					'search_pages_nonce'              => wp_create_nonce( 'search-pages' ),
					'search_order_metakeys_nonce'     => wp_create_nonce( 'search-order-metakeys' ),
					'copy_billing'                    => __( 'Copy billing information to shipping information? This will remove any currently entered shipping information.', 'woocommerce' ),
					'load_billing'                    => __( "Load the customer's billing information? This will remove any currently entered billing information.", 'woocommerce' ),
					'load_shipping'                   => __( "Load the customer's shipping information? This will remove any currently entered shipping information.", 'woocommerce' ),
					'no_customer_selected'            => __( 'No customer selected', 'woocommerce' ),
					'get_customer_details_nonce'      => wp_create_nonce( 'get-customer-details' ),
					'add_order_note_nonce'            => wp_create_nonce( 'add-order-note' ),
					'delete_order_note_nonce'         => wp_create_nonce( 'delete-order-note' ),
					'post_id'                         => $order_id,
					'order_item_nonce'                => wp_create_nonce( 'order-item' ),
					'hide_new_customer_form'          => __( 'hide new customer form', 'shop-front' ),
					'add_new_customer_form'           => __( 'add a new customer', 'shop-front' ),
					'new_customer_or'           	  => __( 'Or', 'shop-front' ),
					'tax_based_on'                    => esc_attr( get_option( 'woocommerce_tax_based_on' ) ),
					'i18n_apply_coupon'               => __( 'Enter a coupon code to apply. Discounts are applied to line totals, before taxes.', 'woocommerce' ),
					'i18n_add_fee'                    => __( 'Enter a fixed amount or percentage.', 'shop-front' ),
					'calc_totals_nonce'               => wp_create_nonce( 'calc-totals' ),
					'countries'              		  => wp_json_encode( array_merge( WC()->countries->get_allowed_country_states(), WC()->countries->get_shipping_country_states() ) ),
					'i18n_select_state_text' 		  => esc_attr__( 'Select an option&hellip;', 'woocommerce' ),
					'default_country'                 => isset( $default_location['country'] ) ? $default_location['country'] : '',
					'default_state'          		  => isset( $default_location['state'] ) ? $default_location['state'] : '',
					'placeholder_name'       		  => esc_attr__( 'Name (required)', 'woocommerce' ),
					'placeholder_value'      		  => esc_attr__( 'Value (required)', 'woocommerce' ),
				)
			);
		}
	}
}
