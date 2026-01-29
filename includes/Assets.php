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
		$frontend_product_script      = SHOP_FRONT_PLUGIN_ASSET . '/frontend/product.js';
		$frontend_form_handler_script = SHOP_FRONT_PLUGIN_ASSET . '/frontend/form-handler.js';
		$frontend_sweetalert2         = SHOP_FRONT_PLUGIN_ASSET . '/frontend/library/sweetalert2.min.js';

		wp_register_script( 'my_shop_front_admin_script', $admin_script, array( 'my_shop_front-block-editor-script' ), SHOP_FRONT_PLUGIN_VERSION, true );
		wp_register_script( 'my_shop_front_moment', 'https://cdn.jsdelivr.net/momentjs/latest/moment.min.js', array(), '2.29.4', true );
		wp_register_script( 'my_shop_front_daterangepicker', 'https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js', array( 'jquery', 'my_shop_front_moment' ), '3.1.0', true );
		wp_register_script( 'my_shop_front_script', $frontend_script, array( 'jquery', 'my_shop_front_daterangepicker' ), SHOP_FRONT_PLUGIN_VERSION, true );

		// Dashboard scripts.
		wp_register_script( 'my_shop_front_form_handler_script', $frontend_form_handler_script, array( 'my_shop_front_selectWoo', 'jquery-ui-datepicker' ), filemtime( SHOP_FRONT_DIR . '/assets/frontend/form-handler.js' ), true );
		wp_register_script( 'my_shop_front_sweetalert2_script', $frontend_sweetalert2, array(), '11.14.5', true );

		// Order scripts.
		wp_register_script( 'my_shop_front_order_script', $frontend_order_script, array( 'my_shop_front_selectWoo' ), SHOP_FRONT_PLUGIN_VERSION, true );
		wp_register_script( 'my_shop_front_product_script', $frontend_product_script, array( 'my_shop_front_selectWoo', 'jquery-ui-datepicker' ), SHOP_FRONT_PLUGIN_VERSION, true );
		wp_register_script( 'my_shop_front_selectWoo', WC()->plugin_url() . '/assets/js/selectWoo/selectWoo.full.js', array( 'jquery' ), '4.0.3', true );
		wp_register_script( 'wc-accounting', WC()->plugin_url() . '/assets/js/accounting/accounting.min.js', array( 'jquery' ), '0.4.2', true );
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

		wp_register_style( 'my_shop_front_admin_style', $admin_style, array(), SHOP_FRONT_PLUGIN_VERSION );
		wp_register_style( 'my_shop_front_style', $frontend_style, array(), SHOP_FRONT_PLUGIN_VERSION );
		wp_register_style( 'my_shop_front_bs_grid', $bs_grid_style, array(), SHOP_FRONT_PLUGIN_VERSION );
		wp_register_style( 'my_shop_front_daterangepicker', 'https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css', array(), '3.1.0' );

		wp_register_style( 'my_shop_front_sweetalert2_style', $frontend_sweetalert2_style, array(), '11.14.5' );
		wp_register_style( 'my_shop_front_poppins', 'https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,400;0,500;0,600;0,700;1,400;1,500;1,600&display=swap', array() );
		wp_register_style( 'my_shop_front_jquery-ui-style', WC()->plugin_url() . '/assets/css/jquery-ui/jquery-ui.min.css', array(), SHOP_FRONT_PLUGIN_VERSION );
	}

	/**
	 * Enqueue admin scripts.
	 *
	 * @return void
	 */
	public function enqueue_admin_scripts() {
		wp_enqueue_script( 'my_shop_front_admin_script' );

		$page = get_current_screen();
		if ( 'woocommerce_page_shop-front' === $page->id ) {
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
			wp_enqueue_style( 'my_shop_front_daterangepicker' );
			wp_enqueue_script( 'my_shop_front_script' );
			wp_localize_script(
				'my_shop_front_script',
				'MSF_Front_Script',
				array(
					'upload_image_text'     => __( 'Upload Image', 'shop-front' ),
					'remove_image_text'     => __( 'Remove Image', 'shop-front' ),
					'upload_product_image'  => __( 'Upload Product Image', 'shop-front' ),
					'insert_image'          => __( 'Insert Image', 'shop-front' ),
					'product_image'         => __( 'Product Image', 'shop-front' ),
					'product_gallery_image' => __( 'Product Gallery Image', 'shop-front' ),
					'upload_category_image' => __( 'Upload Category Image', 'shop-front' ),
					'category_image'        => __( 'Category Image', 'shop-front' ),
					'upload_brand_image'    => __( 'Upload Brand Image', 'shop-front' ),
					'brand_image'           => __( 'Brand Image', 'shop-front' ),
					'upload_gallery_images' => __( 'Upload Product Gallery Images', 'shop-front' ),
				)
			);

			wp_localize_script(
				'my_shop_front_script',
				'MSF_Dashboard_DateRanges_I18n',
				array(
					'today'      => __( 'Today', 'shop-front' ),
					'yesterday'  => __( 'Yesterday', 'shop-front' ),
					'last7'      => __( 'Last 7 Days', 'shop-front' ),
					'last30'     => __( 'Last 30 Days', 'shop-front' ),
					'this_month' => __( 'This Month', 'shop-front' ),
					'last_month' => __( 'Last Month', 'shop-front' ),
				)
			);

			wp_enqueue_style( 'my_shop_front_poppins' );
			wp_enqueue_style( 'my_shop_front_sweetalert2_style' );
			wp_enqueue_script( 'my_shop_front_sweetalert2_script' );
			wp_enqueue_script( 'my_shop_front_form_handler_script' );
			wp_localize_script(
				'my_shop_front_form_handler_script',
				'MSF_Form_Handler',
				array(
					'ajax_url'               => admin_url( 'admin-ajax.php' ),
					'msfc_woo_delete_nonce_' => wp_create_nonce( '_msfc_delete_nonce_' ),
					'search_products_nonce'  => wp_create_nonce( 'search-products' ),
					'coupon_code_generator'  => array(
						'generate_button_text' => esc_html__( 'Generate coupon code', 'shop-front' ),
						'characters'           => apply_filters( 'woocommerce_coupon_code_generator_characters', 'ABCDEFGHJKMNPQRSTUVWXYZ23456789' ),
						'char_length'          => apply_filters( 'woocommerce_coupon_code_generator_character_length', 8 ),
						'prefix'               => apply_filters( 'woocommerce_coupon_code_generator_prefix', '' ),
						'suffix'               => apply_filters( 'woocommerce_coupon_code_generator_suffix', '' ),
					),
					'i18n'                   => array(
						// Common messages.
						'processing'                     => __( 'Processing...', 'shop-front' ),
						'please_wait'                    => __( 'Please wait while we process your request.', 'shop-front' ),
						'success_title'                  => __( 'Success!', 'shop-front' ),
						'error_title'                    => __( 'Error!', 'shop-front' ),
						'ok_button'                      => __( 'OK', 'shop-front' ),
						'yes_button'                     => __( 'Yes', 'shop-front' ),
						'no_button'                      => __( 'No', 'shop-front' ),
						'cancel_button'                  => __( 'Cancel', 'shop-front' ),
						'unexpected_error'               => __( 'An unexpected error occurred. Please try again.', 'shop-front' ),

						// Category messages.
						'category_name_required'         => __( 'Please enter category name.', 'shop-front' ),
						'category_adding'                => __( 'Adding Category...', 'shop-front' ),
						'category_added_successfully'    => __( 'Category added successfully!', 'shop-front' ),
						'category_updating'              => __( 'Updating Category...', 'shop-front' ),
						'category_updated_successfully'  => __( 'Category updated successfully!', 'shop-front' ),
						'category_delete_confirm_title'  => __( 'Are you sure?', 'shop-front' ),
						'category_delete_confirm_text'   => __( 'Do you want to delete this category? This action cannot be undone.', 'shop-front' ),
						'category_delete_confirm_button' => __( 'Yes, delete it!', 'shop-front' ),
						'category_deleting'              => __( 'Deleting Category...', 'shop-front' ),
						'category_deleted_successfully'  => __( 'Category deleted successfully!', 'shop-front' ),

						// Tag messages.
						'tag_name_required'              => __( 'Please enter tag name.', 'shop-front' ),
						'tag_adding'                     => __( 'Adding Tag...', 'shop-front' ),
						'tag_added_successfully'         => __( 'Tag added successfully!', 'shop-front' ),
						'tag_updating'                   => __( 'Updating Tag...', 'shop-front' ),
						'tag_updated_successfully'       => __( 'Tag updated successfully!', 'shop-front' ),
						'tag_delete_confirm_title'       => __( 'Are you sure?', 'shop-front' ),
						'tag_delete_confirm_text'        => __( 'Do you want to delete this tag? This action cannot be undone.', 'shop-front' ),
						'tag_delete_confirm_button'      => __( 'Yes, delete it!', 'shop-front' ),
						'tag_deleting'                   => __( 'Deleting Tag...', 'shop-front' ),
						'tag_deleted_successfully'       => __( 'Tag deleted successfully!', 'shop-front' ),

						// Brand messages.
						'brand_name_required'            => __( 'Please enter brand name.', 'shop-front' ),
						'brand_adding'                   => __( 'Adding Brand...', 'shop-front' ),
						'brand_added_successfully'       => __( 'Brand added successfully!', 'shop-front' ),
						'brand_updating'                 => __( 'Updating Brand...', 'shop-front' ),
						'brand_updated_successfully'     => __( 'Brand updated successfully!', 'shop-front' ),
						'brand_delete_confirm_title'     => __( 'Are you sure?', 'shop-front' ),
						'brand_delete_confirm_text'      => __( 'Do you want to delete this brand? This action cannot be undone.', 'shop-front' ),
						'brand_delete_confirm_button'    => __( 'Yes, delete it!', 'shop-front' ),
						'brand_deleting'                 => __( 'Deleting Brand...', 'shop-front' ),
						'brand_deleted_successfully'     => __( 'Brand deleted successfully!', 'shop-front' ),

						// Product messages.
						'product_title_required'         => __( 'Please enter product title.', 'shop-front' ),
						'product_type_required'          => __( 'Please select a product type.', 'shop-front' ),
						'product_status_required'        => __( 'Please select a product status.', 'shop-front' ),
						'product_description_required'   => __( 'Please enter product description.', 'shop-front' ),
						'product_category_required'      => __( 'Please select at least one category.', 'shop-front' ),
						'product_price_required'         => __( 'Please enter product price.', 'shop-front' ),
						'product_adding'                 => __( 'Adding Product...', 'shop-front' ),
						'product_added_successfully'     => __( 'Product added successfully!', 'shop-front' ),
						'product_updating'               => __( 'Updating Product...', 'shop-front' ),
						'product_updated_successfully'   => __( 'Product updated successfully!', 'shop-front' ),
						'upload_image_text'              => __( 'Upload Image', 'shop-front' ),

						// Coupon messages.
						'coupon_code_required'           => __( 'Coupon code is required.', 'shop-front' ),
						'coupon_discount_type_required'  => __( 'Please select a discount type.', 'shop-front' ),
						'coupon_amount_required'         => __( 'Coupon amount is required.', 'shop-front' ),
						'coupon_adding'                  => __( 'Adding Coupon...', 'shop-front' ),
						'coupon_added_successfully'      => __( 'Coupon added successfully!', 'shop-front' ),
						'coupon_updating'                => __( 'Updating Coupon...', 'shop-front' ),
						'coupon_updated_successfully'    => __( 'Coupon updated successfully!', 'shop-front' ),
						'delete_coupon_warning'          => __( 'This will delete the coupon permanently. This action cannot be undone.', 'shop-front' ),
						'deleting'                       => __( 'Deleting...', 'shop-front' ),
						'are_you_sure'                   => __( 'Are you sure?', 'shop-front' ),
						'yes_delete'                     => __( 'Yes, delete it!', 'shop-front' ),
						'validation_error'               => __( 'Validation Error', 'shop-front' ),
					),
					'coupons_url'            => msfc_get_navigation_url( 'coupons' ),
				)
			);
			wp_enqueue_media();

			// Order styles and scripts.
			wp_enqueue_script( 'my_shop_front_selectWoo' );
			wp_enqueue_script( 'my_shop_front_order_script' );

			wp_enqueue_script( 'wc-accounting' );
			wp_localize_script(
				'wc-accounting',
				'accounting_params',
				array(
					'mon_decimal_point' => wc_get_price_decimal_separator(),
				)
			);

			$order_id = absint( get_query_var( 'order-details' ) );

			if ( ! $order_id ) {
				$order_id = absint( get_query_var( 'edit-order' ) );
			}

			if ( ! $order_id ) {
				global $theorder;
				$order_id = \Automattic\WooCommerce\Utilities\OrderUtil::get_post_or_order_id( $theorder );
			}
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
					'new_customer_or'                 => __( 'Or', 'shop-front' ),
					'tax_based_on'                    => esc_attr( get_option( 'woocommerce_tax_based_on' ) ),
					'i18n_apply_coupon'               => __( 'Enter a coupon code to apply. Discounts are applied to line totals, before taxes.', 'woocommerce' ),
					'i18n_add_fee'                    => __( 'Enter a fixed amount or percentage.', 'shop-front' ),
					'calc_totals_nonce'               => wp_create_nonce( 'calc-totals' ),
					'countries'                       => wp_json_encode( array_merge( WC()->countries->get_allowed_country_states(), WC()->countries->get_shipping_country_states() ) ),
					'i18n_select_state_text'          => esc_attr__( 'Select an option&hellip;', 'woocommerce' ),
					'default_country'                 => isset( $default_location['country'] ) ? $default_location['country'] : '',
					'default_state'                   => isset( $default_location['state'] ) ? $default_location['state'] : '',
					'placeholder_name'                => esc_attr__( 'Name (required)', 'woocommerce' ),
					'placeholder_value'               => esc_attr__( 'Value (required)', 'woocommerce' ),
					'i18n_delete_note'                => __( 'Are you sure you wish to delete this note? This action cannot be undone.', 'woocommerce' ),
					'i18n_no_notes'                   => __( 'There are no notes yet.', 'woocommerce' ),
					'remove_item_notice'              => __( 'Are you sure you want to remove the selected items?', 'woocommerce' ),
					'remove_fee_notice'               => __( 'Are you sure you want to remove the selected fees?', 'woocommerce' ),
					'remove_shipping_notice'          => __( 'Are you sure you want to remove the selected shipping?', 'woocommerce' ),
					'remove_item_meta'                => __( 'Remove this item meta?', 'woocommerce' ),
					'mon_decimal_point'               => wc_get_price_decimal_separator(),
					'rounding_precision'              => wc_get_rounding_precision(),
				)
			);

			// product styles and scripts.
			wp_enqueue_style( 'my_shop_front_jquery-ui-style' );
			wp_enqueue_script( 'my_shop_front_product_script' );

			// Prepare product script data.
			$product_script_data = array(
				'i18n_global_unique_id_error' => __( 'Please enter only numbers and hyphens (-).', 'woocommerce' ),
				'ajax_url'                    => admin_url( 'admin-ajax.php' ),
				'search_products_nonce'       => wp_create_nonce( 'search-products' ),
				'i18n'                        => array(
					// Common messages.
					'success_title'    => __( 'Success!', 'shop-front' ),
					'error_title'      => __( 'Error!', 'shop-front' ),
					'ok_button'        => __( 'OK', 'shop-front' ),
					'unexpected_error' => __( 'An unexpected error occurred. Please try again.', 'shop-front' ),
				),
			);

			wp_localize_script(
				'my_shop_front_product_script',
				'My_Shop_Front_Product',
				$product_script_data
			);
		}
	}
}
