<?php

namespace PluginizeLab\StoreSuite;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Assets {
	/**
	 * Default plugin slugs whose assets are kept on the StoreSuite dashboard.
	 * Third parties can add more via the storesuite_allowed_plugin_slugs filter.
	 *
	 * @var string[]
	 */
	private static $allowed_plugin_slugs = array( 'woocommerce', 'storesuite' );

	/**
	 * Default script/style handles that are never removed on the StoreSuite dashboard.
	 * Third parties can add more via the storesuite_allowed_asset_handles filter.
	 *
	 * @var string[]
	 */
	private static $allowed_asset_handles = array();

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

		// Priority 7: after most themes enqueue (0–6) but before wp_print_styles (8).
		add_action( 'wp_head', array( $this, 'remove_all_theme_assets' ), 7 );
	}

	/**
	 * Whether an asset URL should be removed (theme or disallowed plugin).
	 *
	 * @param string   $src             Asset src URL.
	 * @param string[] $theme_uris      Theme base URIs to match.
	 * @param string[] $allowed_plugins Plugin slugs to keep.
	 * @return bool
	 */
	private function should_remove_asset( $src, array $theme_uris, array $allowed_plugins ) {
		foreach ( $theme_uris as $uri ) {
			if ( strpos( $src, $uri ) === 0 ) {
				return true;
			}
		}

		$prefix = plugins_url();
		if ( strpos( $src, $prefix ) === false ) {
			return false;
		}

		$slug = strtok( substr( $src, strpos( $src, $prefix ) + strlen( $prefix ) ), '/?' );
		return $slug && ! in_array( $slug, $allowed_plugins, true );
	}

	/**
	 * Register all scripts and styles.
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
		$admin_script                 = STORESUITE_PLUGIN_ASSET . '/admin/script.js';
		$frontend_script              = STORESUITE_PLUGIN_ASSET . '/frontend/script.js';
		$frontend_order_script        = STORESUITE_PLUGIN_ASSET . '/frontend/order.js';
		$frontend_product_script      = STORESUITE_PLUGIN_ASSET . '/frontend/product.js';
		$frontend_form_handler_script = STORESUITE_PLUGIN_ASSET . '/frontend/form-handler.js';
		$frontend_sweetalert2         = STORESUITE_PLUGIN_ASSET . '/frontend/library/sweetalert2.min.js';
		$storesuite_daterangepicker   = STORESUITE_PLUGIN_ASSET . '/frontend/library/daterangepicker.min.js';

		wp_register_script( 'storesuite_admin_script', $admin_script, array(), STORESUITE_PLUGIN_VERSION, true );
		wp_register_script( 'storesuite_daterangepicker', $storesuite_daterangepicker, array( 'jquery', 'moment' ), '3.1.0', true );
		wp_register_script( 'storesuite_script', $frontend_script, array( 'jquery', 'storesuite_daterangepicker' ), STORESUITE_PLUGIN_VERSION, true );

		// Dashboard scripts.
		wp_register_script( 'storesuite_form_handler_script', $frontend_form_handler_script, array( 'storesuite_selectWoo', 'jquery-ui-datepicker' ), filemtime( STORESUITE_DIR . '/assets/frontend/form-handler.js' ), true );
		wp_register_script( 'storesuite_sweetalert2_script', $frontend_sweetalert2, array(), '11.14.5', true );

		// Order scripts.
		wp_register_script( 'storesuite_order_script', $frontend_order_script, array( 'storesuite_selectWoo' ), STORESUITE_PLUGIN_VERSION, true );
		wp_register_script( 'storesuite_product_script', $frontend_product_script, array( 'storesuite_selectWoo', 'jquery-ui-datepicker' ), STORESUITE_PLUGIN_VERSION, true );
		wp_register_script( 'storesuite_selectWoo', WC()->plugin_url() . '/assets/js/selectWoo/selectWoo.full.js', array( 'jquery' ), '4.0.3', true );
		wp_register_script( 'wc-accounting', WC()->plugin_url() . '/assets/js/accounting/accounting.min.js', array( 'jquery' ), '0.4.2', true );
	}

	/**
	 * Register styles.
	 *
	 * @return void
	 */
	public function register_styles() {
		$admin_style                      = STORESUITE_PLUGIN_ASSET . '/admin/style.css';
		$frontend_style                   = STORESUITE_PLUGIN_ASSET . '/frontend/style.css';
		$bs_grid_style                    = STORESUITE_PLUGIN_ASSET . '/frontend/bootstrap-grid.min.css';
		$frontend_sweetalert2_style       = STORESUITE_PLUGIN_ASSET . '/frontend/library/sweetalert2.min.css';
		$storesuite_daterangepicker_style = STORESUITE_PLUGIN_ASSET . '/frontend/library/daterangepicker.css';

		wp_register_style( 'storesuite_admin_style', $admin_style, array(), STORESUITE_PLUGIN_VERSION );
		wp_register_style( 'storesuite_style', $frontend_style, array(), STORESUITE_PLUGIN_VERSION );
		wp_register_style( 'storesuite_bs_grid', $bs_grid_style, array(), STORESUITE_PLUGIN_VERSION );
		wp_register_style( 'storesuite_daterangepicker', $storesuite_daterangepicker_style, array(), '3.1.0' );

		wp_register_style( 'storesuite_sweetalert2_style', $frontend_sweetalert2_style, array(), '11.14.5' );
		wp_register_style( 'storesuite_poppins', 'https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,400;0,500;0,600;0,700;1,400;1,500;1,600&display=swap', array(), STORESUITE_PLUGIN_VERSION );
		wp_register_style( 'storesuite_jquery-ui-style', WC()->plugin_url() . '/assets/css/jquery-ui/jquery-ui.min.css', array(), STORESUITE_PLUGIN_VERSION );
	}

	/**
	 * Enqueue admin scripts.
	 *
	 * @return void
	 */
	public function enqueue_admin_scripts() {
		$page = get_current_screen();
		if ( 'woocommerce_page_storesuite' === $page->id ) {
			$asset_file = include STORESUITE_DIR . '/assets/build/admin/script.asset.php';

			wp_enqueue_script(
				'storesuite-admin-page',
				STORESUITE_PLUGIN_ASSET . '/build/admin/script.js',
				$asset_file['dependencies'],
				$asset_file['version'],
				true
			);

			wp_enqueue_style(
				'storesuite-admin-styles',
				STORESUITE_PLUGIN_ASSET . '/build/admin.css',
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
		if ( is_storesuite_dashboard_page() ) {
			wp_enqueue_style( 'select2' );
			wp_enqueue_style( 'storesuite_style' );
			wp_enqueue_style( 'storesuite_bs_grid' );
			wp_enqueue_style( 'storesuite_daterangepicker' );
			wp_enqueue_script( 'storesuite_script' );
			wp_localize_script(
				'storesuite_script',
				'MSF_Front_Script',
				array(
					'upload_image_text'     => __( 'Upload Image', 'storesuite' ),
					'remove_image_text'     => __( 'Remove Image', 'storesuite' ),
					'upload_product_image'  => __( 'Upload Product Image', 'storesuite' ),
					'insert_image'          => __( 'Insert Image', 'storesuite' ),
					'product_image'         => __( 'Product Image', 'storesuite' ),
					'product_gallery_image' => __( 'Product Gallery Image', 'storesuite' ),
					'upload_category_image' => __( 'Upload Category Image', 'storesuite' ),
					'category_image'        => __( 'Category Image', 'storesuite' ),
					'upload_brand_image'    => __( 'Upload Brand Image', 'storesuite' ),
					'brand_image'           => __( 'Brand Image', 'storesuite' ),
					'upload_gallery_images' => __( 'Upload Product Gallery Images', 'storesuite' ),
				)
			);

			wp_localize_script(
				'storesuite_script',
				'MSF_Dashboard_DateRanges_I18n',
				array(
					'today'      => __( 'Today', 'storesuite' ),
					'yesterday'  => __( 'Yesterday', 'storesuite' ),
					'last7'      => __( 'Last 7 Days', 'storesuite' ),
					'last30'     => __( 'Last 30 Days', 'storesuite' ),
					'this_month' => __( 'This Month', 'storesuite' ),
					'last_month' => __( 'Last Month', 'storesuite' ),
				)
			);

			wp_enqueue_style( 'storesuite_poppins' );
			wp_enqueue_style( 'storesuite_sweetalert2_style' );
			wp_enqueue_script( 'storesuite_sweetalert2_script' );
			wp_enqueue_script( 'storesuite_form_handler_script' );
			wp_localize_script(
				'storesuite_form_handler_script',
				'MSF_Form_Handler',
				array(
					'ajax_url'                     => admin_url( 'admin-ajax.php' ),
					'storesuite_woo_delete_nonce_' => wp_create_nonce( '_storesuite_delete_nonce_' ),
					'search_products_nonce'        => wp_create_nonce( 'search-products' ),
					'coupon_code_generator'        => array(
						'generate_button_text' => esc_html__( 'Generate coupon code', 'storesuite' ),
						'characters'           => apply_filters( 'woocommerce_coupon_code_generator_characters', 'ABCDEFGHJKMNPQRSTUVWXYZ23456789' ),
						'char_length'          => apply_filters( 'woocommerce_coupon_code_generator_character_length', 8 ),
						'prefix'               => apply_filters( 'woocommerce_coupon_code_generator_prefix', '' ),
						'suffix'               => apply_filters( 'woocommerce_coupon_code_generator_suffix', '' ),
					),
					'i18n'                         => array(
						// Common messages.
						'processing'                     => __( 'Processing...', 'storesuite' ),
						'please_wait'                    => __( 'Please wait while we process your request.', 'storesuite' ),
						'success_title'                  => __( 'Success!', 'storesuite' ),
						'error_title'                    => __( 'Error!', 'storesuite' ),
						'ok_button'                      => __( 'OK', 'storesuite' ),
						'yes_button'                     => __( 'Yes', 'storesuite' ),
						'no_button'                      => __( 'No', 'storesuite' ),
						'cancel_button'                  => __( 'Cancel', 'storesuite' ),
						'unexpected_error'               => __( 'An unexpected error occurred. Please try again.', 'storesuite' ),

						// Category messages.
						'category_name_required'         => __( 'Please enter category name.', 'storesuite' ),
						'category_adding'                => __( 'Adding Category...', 'storesuite' ),
						'category_added_successfully'    => __( 'Category added successfully!', 'storesuite' ),
						'category_updating'              => __( 'Updating Category...', 'storesuite' ),
						'category_updated_successfully'  => __( 'Category updated successfully!', 'storesuite' ),
						'category_delete_confirm_title'  => __( 'Are you sure?', 'storesuite' ),
						'category_delete_confirm_text'   => __( 'Do you want to delete this category? This action cannot be undone.', 'storesuite' ),
						'category_delete_confirm_button' => __( 'Yes, delete it!', 'storesuite' ),
						'category_deleting'              => __( 'Deleting Category...', 'storesuite' ),
						'category_deleted_successfully'  => __( 'Category deleted successfully!', 'storesuite' ),

						// Tag messages.
						'tag_name_required'              => __( 'Please enter tag name.', 'storesuite' ),
						'tag_adding'                     => __( 'Adding Tag...', 'storesuite' ),
						'tag_added_successfully'         => __( 'Tag added successfully!', 'storesuite' ),
						'tag_updating'                   => __( 'Updating Tag...', 'storesuite' ),
						'tag_updated_successfully'       => __( 'Tag updated successfully!', 'storesuite' ),
						'tag_delete_confirm_title'       => __( 'Are you sure?', 'storesuite' ),
						'tag_delete_confirm_text'        => __( 'Do you want to delete this tag? This action cannot be undone.', 'storesuite' ),
						'tag_delete_confirm_button'      => __( 'Yes, delete it!', 'storesuite' ),
						'tag_deleting'                   => __( 'Deleting Tag...', 'storesuite' ),
						'tag_deleted_successfully'       => __( 'Tag deleted successfully!', 'storesuite' ),

						// Brand messages.
						'brand_name_required'            => __( 'Please enter brand name.', 'storesuite' ),
						'brand_adding'                   => __( 'Adding Brand...', 'storesuite' ),
						'brand_added_successfully'       => __( 'Brand added successfully!', 'storesuite' ),
						'brand_updating'                 => __( 'Updating Brand...', 'storesuite' ),
						'brand_updated_successfully'     => __( 'Brand updated successfully!', 'storesuite' ),
						'brand_delete_confirm_title'     => __( 'Are you sure?', 'storesuite' ),
						'brand_delete_confirm_text'      => __( 'Do you want to delete this brand? This action cannot be undone.', 'storesuite' ),
						'brand_delete_confirm_button'    => __( 'Yes, delete it!', 'storesuite' ),
						'brand_deleting'                 => __( 'Deleting Brand...', 'storesuite' ),
						'brand_deleted_successfully'     => __( 'Brand deleted successfully!', 'storesuite' ),

						// Product messages.
						'product_title_required'         => __( 'Please enter product title.', 'storesuite' ),
						'product_type_required'          => __( 'Please select a product type.', 'storesuite' ),
						'product_status_required'        => __( 'Please select a product status.', 'storesuite' ),
						'product_description_required'   => __( 'Please enter product description.', 'storesuite' ),
						'product_category_required'      => __( 'Please select at least one category.', 'storesuite' ),
						'product_price_required'         => __( 'Please enter product price.', 'storesuite' ),
						'product_adding'                 => __( 'Adding Product...', 'storesuite' ),
						'product_added_successfully'     => __( 'Product added successfully!', 'storesuite' ),
						'product_updating'               => __( 'Updating Product...', 'storesuite' ),
						'product_updated_successfully'   => __( 'Product updated successfully!', 'storesuite' ),
						'upload_image_text'              => __( 'Upload Image', 'storesuite' ),

						// Coupon messages.
						'coupon_code_required'           => __( 'Coupon code is required.', 'storesuite' ),
						'coupon_discount_type_required'  => __( 'Please select a discount type.', 'storesuite' ),
						'coupon_amount_required'         => __( 'Coupon amount is required.', 'storesuite' ),
						'coupon_adding'                  => __( 'Adding Coupon...', 'storesuite' ),
						'coupon_added_successfully'      => __( 'Coupon added successfully!', 'storesuite' ),
						'coupon_updating'                => __( 'Updating Coupon...', 'storesuite' ),
						'coupon_updated_successfully'    => __( 'Coupon updated successfully!', 'storesuite' ),
						'delete_coupon_warning'          => __( 'This will delete the coupon permanently. This action cannot be undone.', 'storesuite' ),
						'deleting'                       => __( 'Deleting...', 'storesuite' ),
						'are_you_sure'                   => __( 'Are you sure?', 'storesuite' ),
						'yes_delete'                     => __( 'Yes, delete it!', 'storesuite' ),
						'validation_error'               => __( 'Validation Error', 'storesuite' ),

						// Account details (edit account).
						'account_first_name_required'    => __( 'First name is required.', 'storesuite' ),
						'account_last_name_required'     => __( 'Last name is required.', 'storesuite' ),
						'account_display_name_required'  => __( 'Display name is required.', 'storesuite' ),
						'account_email_required'         => __( 'Email address is required.', 'storesuite' ),
					),
					'coupons_url'                  => storesuite_get_navigation_url( 'coupons' ),
				)
			);
			wp_enqueue_media();

			// Order styles and scripts.
			wp_enqueue_script( 'storesuite_selectWoo' );
			wp_enqueue_script( 'storesuite_order_script' );

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
				'storesuite_order_script',
				'StoreSuite_Order',
				array(
					'i18n_no_matches'                 => _x( 'No matches found', 'enhanced select', 'storesuite' ),
					'i18n_ajax_error'                 => _x( 'Loading failed', 'enhanced select', 'storesuite' ),
					'i18n_input_too_short_1'          => _x( 'Please enter 1 or more characters', 'enhanced select', 'storesuite' ),
					'i18n_input_too_short_n'          => _x( 'Please enter %qty% or more characters', 'enhanced select', 'storesuite' ),
					'i18n_input_too_long_1'           => _x( 'Please delete 1 character', 'enhanced select', 'storesuite' ),
					'i18n_input_too_long_n'           => _x( 'Please delete %qty% characters', 'enhanced select', 'storesuite' ),
					'i18n_selection_too_long_1'       => _x( 'You can only select 1 item', 'enhanced select', 'storesuite' ),
					'i18n_selection_too_long_n'       => _x( 'You can only select %qty% items', 'enhanced select', 'storesuite' ),
					'i18n_load_more'                  => _x( 'Loading more results&hellip;', 'enhanced select', 'storesuite' ),
					'i18n_searching'                  => _x( 'Searching&hellip;', 'enhanced select', 'storesuite' ),
					'ajax_url'                        => admin_url( 'admin-ajax.php' ),
					'search_products_nonce'           => wp_create_nonce( 'search-products' ),
					'search_customers_nonce'          => wp_create_nonce( 'search-customers' ),
					'search_categories_nonce'         => wp_create_nonce( 'search-categories' ),
					'search_taxonomy_terms_nonce'     => wp_create_nonce( 'search-taxonomy-terms' ),
					'search_product_attributes_nonce' => wp_create_nonce( 'search-product-attributes' ),
					'search_pages_nonce'              => wp_create_nonce( 'search-pages' ),
					'search_order_metakeys_nonce'     => wp_create_nonce( 'search-order-metakeys' ),
					'copy_billing'                    => __( 'Copy billing information to shipping information? This will remove any currently entered shipping information.', 'storesuite' ),
					'load_billing'                    => __( "Load the customer's billing information? This will remove any currently entered billing information.", 'storesuite' ),
					'load_shipping'                   => __( "Load the customer's shipping information? This will remove any currently entered shipping information.", 'storesuite' ),
					'no_customer_selected'            => __( 'No customer selected', 'storesuite' ),
					'get_customer_details_nonce'      => wp_create_nonce( 'get-customer-details' ),
					'add_order_note_nonce'            => wp_create_nonce( 'add-order-note' ),
					'delete_order_note_nonce'         => wp_create_nonce( 'delete-order-note' ),
					'post_id'                         => $order_id,
					'order_item_nonce'                => wp_create_nonce( 'order-item' ),
					'hide_new_customer_form'          => __( 'hide new customer form', 'storesuite' ),
					'add_new_customer_form'           => __( 'add a new customer', 'storesuite' ),
					'new_customer_or'                 => __( 'Or', 'storesuite' ),
					'tax_based_on'                    => esc_attr( get_option( 'woocommerce_tax_based_on' ) ),
					'i18n_apply_coupon'               => __( 'Enter a coupon code to apply. Discounts are applied to line totals, before taxes.', 'storesuite' ),
					'i18n_add_fee'                    => __( 'Enter a fixed amount or percentage.', 'storesuite' ),
					'calc_totals_nonce'               => wp_create_nonce( 'calc-totals' ),
					'countries'                       => wp_json_encode( array_merge( WC()->countries->get_allowed_country_states(), WC()->countries->get_shipping_country_states() ) ),
					'i18n_select_state_text'          => esc_attr__( 'Select an option&hellip;', 'storesuite' ),
					'default_country'                 => isset( $default_location['country'] ) ? $default_location['country'] : '',
					'default_state'                   => isset( $default_location['state'] ) ? $default_location['state'] : '',
					'placeholder_name'                => esc_attr__( 'Name (required)', 'storesuite' ),
					'placeholder_value'               => esc_attr__( 'Value (required)', 'storesuite' ),
					'i18n_delete_note'                => __( 'Are you sure you wish to delete this note? This action cannot be undone.', 'storesuite' ),
					'i18n_no_notes'                   => __( 'There are no notes yet.', 'storesuite' ),
					'remove_item_notice'              => __( 'Are you sure you want to remove the selected items?', 'storesuite' ),
					'remove_fee_notice'               => __( 'Are you sure you want to remove the selected fees?', 'storesuite' ),
					'remove_shipping_notice'          => __( 'Are you sure you want to remove the selected shipping?', 'storesuite' ),
					'remove_item_meta'                => __( 'Remove this item meta?', 'storesuite' ),
					'mon_decimal_point'               => wc_get_price_decimal_separator(),
					'rounding_precision'              => wc_get_rounding_precision(),
					'i18n_select_items'            => __( 'Please select some items.', 'storesuite' ),
					'i18n_do_refund'               => __( 'Are you sure you wish to process this refund request? This action cannot be undone.', 'storesuite' ),
					'i18n_delete_refund'           => __( 'Are you sure you wish to delete this refund? This action cannot be undone.', 'storesuite' ),
					'currency_format_num_decimals' => wc_get_price_decimals(),
					'currency_format_symbol'       => get_woocommerce_currency_symbol(),
					'currency_format_decimal_sep'  => esc_attr( wc_get_price_decimal_separator() ),
					'currency_format_thousand_sep' => esc_attr( wc_get_price_thousand_separator() ),
					'currency_format'              => esc_attr( str_replace( [ '%1$s', '%2$s' ], [ '%s', '%v' ], get_woocommerce_price_format() ) ), // For accounting JS
					'round_at_subtotal'            => get_option( 'woocommerce_tax_round_at_subtotal', 'no' ),
					'order_ok_button'              => __( 'OK', 'storesuite' ),
					'order_success_title'          => __( 'Success!', 'storesuite' ),
				)
			);

			// product styles and scripts.
			wp_enqueue_style( 'storesuite_jquery-ui-style' );
			wp_enqueue_script( 'storesuite_product_script' );

			// Prepare product script data.
			$product_script_data = array(
				'i18n_global_unique_id_error' => __( 'Please enter only numbers and hyphens (-).', 'storesuite' ),
				'ajax_url'                    => admin_url( 'admin-ajax.php' ),
				'search_products_nonce'       => wp_create_nonce( 'search-products' ),
				'i18n'                        => array(
					// Common messages.
					'success_title'    => __( 'Success!', 'storesuite' ),
					'error_title'      => __( 'Error!', 'storesuite' ),
					'ok_button'        => __( 'OK', 'storesuite' ),
					'unexpected_error' => __( 'An unexpected error occurred. Please try again.', 'storesuite' ),
				),
			);

			wp_localize_script(
				'storesuite_product_script',
				'StoreSuite_Product',
				$product_script_data
			);
		}
	}

	/**
	 * Remove theme and other-plugin assets on the StoreSuite dashboard (keep allowed plugins).
	 *
	 * @return void
	 */
	public function remove_all_theme_assets() {
		if ( ! is_storesuite_dashboard_page() ) {
			return;
		}

		$allowed_plugins = apply_filters( 'storesuite_allowed_plugin_slugs', self::$allowed_plugin_slugs );
		$allowed_plugins = array_filter( array_map( 'strval', (array) $allowed_plugins ) );

		$allowed_handles = apply_filters( 'storesuite_allowed_asset_handles', self::$allowed_asset_handles );
		$allowed_handles = array_filter( array_map( 'strval', (array) $allowed_handles ) );

		$theme_uris = array_filter(
			array_unique(
				array(
					rtrim( get_stylesheet_directory_uri(), '/' ),
					rtrim( get_template_directory_uri(), '/' ),
				)
			)
		);

		global $wp_styles, $wp_scripts;

		if ( ! empty( $wp_styles->registered ) ) {
			foreach ( $wp_styles->registered as $handle => $obj ) {
				if ( in_array( $handle, $allowed_handles, true ) ) {
					continue;
				}
				if ( isset( $obj->src ) && $this->should_remove_asset( $obj->src, $theme_uris, $allowed_plugins ) ) {
					wp_dequeue_style( $handle );
					wp_deregister_style( $handle );
				}
			}
		}

		if ( ! empty( $wp_scripts->registered ) ) {
			foreach ( $wp_scripts->registered as $handle => $obj ) {
				if ( in_array( $handle, $allowed_handles, true ) ) {
					continue;
				}
				if ( isset( $obj->src ) && $this->should_remove_asset( $obj->src, $theme_uris, $allowed_plugins ) ) {
					wp_dequeue_script( $handle );
					wp_deregister_script( $handle );
				}
			}
		}
	}
}
