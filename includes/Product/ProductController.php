<?php

namespace PluginizeLab\StoreSuite\Product;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Plugin product controller class
 */
class ProductController {
	/**
	 * The constructor.
	 */
	public function __construct() {
		add_action( 'storesuite_load_new_product_template', array( $this, 'load_new_product_template' ) );
		add_action( 'storesuite_load_edit_product_template', array( $this, 'load_edit_product_template' ) );
		add_action( 'storesuite_dashboard_product_add_form', array( $this, 'load_product_form' ) );
		add_action( 'storesuite_dashboard_product_edit_form', array( $this, 'load_product_edit_form' ) );
		add_action( 'wp_ajax_storesuite_add_product_action', array( $this, 'handle_add_product' ) );
		add_action( 'wp_ajax_storesuite_edit_product_action', array( $this, 'handle_edit_product' ) );
		add_action( 'wp_ajax_storesuite_delete_product', array( $this, 'handle_delete_product' ) );
	}

	/**
	 * Load the new product template.
	 *
	 * @param array $query_vars The query variables.
	 *
	 * @return void
	 */
	public function load_new_product_template( $query_vars ) {
		$template_args = array(
			'query_vars' => $query_vars,
		);
		storesuite_get_template_part( 'products/add-new-product', '', $template_args );
	}

	/**
	 * Load the edit product template.
	 *
	 * @param array $query_vars The query variables.
	 *
	 * @return void
	 */
	public function load_edit_product_template( $query_vars ) {
		$template_args = array(
			'query_vars' => $query_vars,
		);
		storesuite_get_template_part( 'products/edit-product', '', $template_args );
	}

	/**
	 * Load the product add form template.
	 *
	 * @param array $query_vars The query variables.
	 *
	 * @return void
	 */
	public function load_product_form( $query_vars ) {
		$template_args = array(
			'query_vars'    => $query_vars,
			'template_type' => 'add-new-product',
		);
		storesuite_get_template_part( 'products/product-form', '', $template_args );
	}

	/**
	 * Load the product edit form template.
	 *
	 * @param array $query_vars The query variables.
	 *
	 * @return void
	 */
	public function load_product_edit_form( $query_vars ) {
		$template_args = array(
			'query_vars'    => $query_vars,
			'template_type' => 'edit-product',
		);
		storesuite_get_template_part( 'products/product-form', '', $template_args );
	}

	/**
	 * Handle the AJAX request for adding a new product.
	 */
	public function handle_add_product() {

		// Verify the nonce.
		if ( ! isset( $_POST['storesuite_add_product_nonce'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['storesuite_add_product_nonce'] ) ), '_storesuite_add_product_' ) ) {
			wp_send_json_error( array( 'error' => __( 'Nonce verification failed', 'storesuite' ) ) );
		}

		// Check user permissions.
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_send_json_error( array( 'error' => __( 'You do not have permission to perform this action.', 'storesuite' ) ) );
		}

		// Build sanitized data array.
		$data = $this->sanitize_product_data( $_POST );

		$response = ( new ProductManager() )->storesuite_save_product( $data );

		if ( is_wp_error( $response ) ) {
			wp_send_json_error( $response->get_error_message() );
		}

		if ( is_int( $response ) ) {
			wp_send_json_success(
				array(
					'message' => __( 'Product successfully created', 'storesuite' ),
					'context' => 'add',
				)
			);
		} else {
			wp_send_json_error(
				array(
					'error'   => __( 'Something wrong, please try again later', 'storesuite' ),
					'context' => 'add',
				)
			);
		}
	}

	/**
	 * Handle the AJAX request for update a product.
	 */
	public function handle_edit_product() {
		// Verify the nonce.
		if ( ! isset( $_POST['storesuite_edit_product_nonce'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['storesuite_edit_product_nonce'] ) ), '_storesuite_edit_product_' ) ) {
			wp_send_json_error( array( 'error' => __( 'Nonce verification failed', 'storesuite' ) ) );
		}

		// Check user permissions.
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_send_json_error( array( 'error' => __( 'You do not have permission to perform this action.', 'storesuite' ) ) );
		}

		// Build sanitized data array.
		$data = $this->sanitize_product_data( $_POST );

		$response = ( new ProductManager() )->storesuite_save_product( $data );

		if ( is_wp_error( $response ) ) {
			wp_send_json_error( $response->get_error_message() );
		}

		if ( is_int( $response ) ) {
			$product = wc_get_product( $response );
			wp_send_json_success(
				array(
					'message'   => __( 'Product successfully updated', 'storesuite' ),
					'context'   => 'edit',
					'permalink' => get_permalink( $response ),
					'slug'      => $product ? $product->get_slug() : '',
				)
			);
		} else {
			wp_send_json_error(
				array(
					'error'   => __( 'Something wrong, please try again later', 'storesuite' ),
					'context' => 'edit',
				)
			);
		}
	}

	/**
	 * Handle the AJAX request for deleting a product (move to trash).
	 */
	public function handle_delete_product() {
		if ( ! isset( $_POST['storesuite_delete_product_nonce'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['storesuite_delete_product_nonce'] ) ), '_storesuite_delete_nonce_' ) ) {
			wp_send_json_error( array( 'error' => __( 'Nonce verification failed', 'storesuite' ) ) );
		}

		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_send_json_error( array( 'error' => __( 'You do not have permission to perform this action.', 'storesuite' ) ) );
		}

		$product_id = isset( $_POST['id'] ) ? absint( $_POST['id'] ) : 0;
		if ( ! $product_id ) {
			wp_send_json_error( array( 'error' => __( 'Invalid product ID', 'storesuite' ) ) );
		}

		$product = wc_get_product( $product_id );
		if ( ! $product ) {
			wp_send_json_error( array( 'error' => __( 'Product not found', 'storesuite' ) ) );
		}

		$result = wp_trash_post( $product_id );
		if ( ! $result ) {
			wp_send_json_error( array( 'error' => __( 'Failed to delete product', 'storesuite' ) ) );
		}

		wp_send_json_success( array( 'message' => __( 'Product successfully deleted', 'storesuite' ) ) );
	}

	/**
	 * Sanitize product data from $_POST.
	 *
	 * @param array $post_data Raw POST data.
	 * @return array Sanitized product data.
	 */
	private function sanitize_product_data( $post_data ) {
		$data = array();

		// Product ID for updates.
		if ( isset( $post_data['product_id'] ) ) {
			$data['product_id'] = absint( $post_data['product_id'] );
		}

		// Text fields.
		if ( isset( $post_data['product_title'] ) ) {
			$data['product_title'] = sanitize_text_field( wp_unslash( $post_data['product_title'] ) );
		}
		if ( isset( $post_data['product_slug'] ) ) {
			$data['product_slug'] = sanitize_title( wp_unslash( $post_data['product_slug'] ) );
		}
		if ( isset( $post_data['post_type'] ) ) {
			$data['post_type'] = sanitize_key( wp_unslash( $post_data['post_type'] ) );
		}
		if ( isset( $post_data['post_status'] ) ) {
			$data['post_status'] = sanitize_key( wp_unslash( $post_data['post_status'] ) );
		}
		if ( isset( $post_data['_visibility'] ) ) {
			$data['_visibility'] = sanitize_key( wp_unslash( $post_data['_visibility'] ) );
		}
		if ( isset( $post_data['_sku'] ) ) {
			$data['_sku'] = sanitize_text_field( wp_unslash( $post_data['_sku'] ) );
		}
		if ( isset( $post_data['_global_unique_id'] ) ) {
			$data['_global_unique_id'] = sanitize_text_field( wp_unslash( $post_data['_global_unique_id'] ) );
		}
		if ( isset( $post_data['_stock_status'] ) ) {
			$data['_stock_status'] = sanitize_key( wp_unslash( $post_data['_stock_status'] ) );
		}
		if ( isset( $post_data['_backorders'] ) ) {
			$data['_backorders'] = sanitize_key( wp_unslash( $post_data['_backorders'] ) );
		}
		if ( isset( $post_data['comment_status'] ) ) {
			$data['comment_status'] = sanitize_key( wp_unslash( $post_data['comment_status'] ) );
		}

		// HTML content fields.
		if ( isset( $post_data['product_description'] ) ) {
			$data['product_description'] = wp_kses_post( wp_unslash( $post_data['product_description'] ) );
		}
		if ( isset( $post_data['product_short_description'] ) ) {
			$data['product_short_description'] = wp_kses_post( wp_unslash( $post_data['product_short_description'] ) );
		}
		if ( isset( $post_data['_purchase_note'] ) ) {
			$data['_purchase_note'] = wp_kses_post( wp_unslash( $post_data['_purchase_note'] ) );
		}

		// Price/decimal fields.
		if ( isset( $post_data['regular_price'] ) ) {
			$data['regular_price'] = $post_data['regular_price'] === '' ? '' : wc_format_decimal( wp_unslash( $post_data['regular_price'] ) );
		}
		if ( isset( $post_data['sale_price'] ) ) {
			$data['sale_price'] = $post_data['sale_price'] === '' ? '' : wc_format_decimal( wp_unslash( $post_data['sale_price'] ) );
		}
		if ( isset( $post_data['weight'] ) ) {
			$data['weight'] = wc_format_decimal( wp_unslash( $post_data['weight'] ) );
		}
		if ( isset( $post_data['length'] ) ) {
			$data['length'] = wc_format_decimal( wp_unslash( $post_data['length'] ) );
		}
		if ( isset( $post_data['width'] ) ) {
			$data['width'] = wc_format_decimal( wp_unslash( $post_data['width'] ) );
		}
		if ( isset( $post_data['height'] ) ) {
			$data['height'] = wc_format_decimal( wp_unslash( $post_data['height'] ) );
		}

		// Integer fields.
		if ( isset( $post_data['product_thumbnail_id'] ) ) {
			$data['product_thumbnail_id'] = absint( $post_data['product_thumbnail_id'] );
		}
		if ( isset( $post_data['menu_order'] ) ) {
			$data['menu_order'] = absint( $post_data['menu_order'] );
		}
		if ( isset( $post_data['_stock_quantity'] ) ) {
			$data['_stock_quantity'] = wc_stock_amount( wp_unslash( $post_data['_stock_quantity'] ) );
		}
		if ( isset( $post_data['_low_stock_amount'] ) ) {
			$data['_low_stock_amount'] = wc_stock_amount( wp_unslash( $post_data['_low_stock_amount'] ) );
		}
		if ( isset( $post_data['product_shipping_class'] ) ) {
			$data['product_shipping_class'] = absint( $post_data['product_shipping_class'] );
		}

		// Checkbox/boolean fields.
		if ( isset( $post_data['_manage_stock'] ) ) {
			$data['_manage_stock'] = sanitize_key( wp_unslash( $post_data['_manage_stock'] ) );
		}
		if ( isset( $post_data['_sold_individually'] ) ) {
			$data['_sold_individually'] = sanitize_key( wp_unslash( $post_data['_sold_individually'] ) );
		}
		if ( isset( $post_data['_featured'] ) ) {
			$data['_featured'] = sanitize_key( wp_unslash( $post_data['_featured'] ) );
		}

		// Date fields.
		if ( isset( $post_data['_sale_price_dates_from'] ) ) {
			$data['_sale_price_dates_from'] = sanitize_text_field( wp_unslash( $post_data['_sale_price_dates_from'] ) );
		}
		if ( isset( $post_data['_sale_price_dates_to'] ) ) {
			$data['_sale_price_dates_to'] = sanitize_text_field( wp_unslash( $post_data['_sale_price_dates_to'] ) );
		}

		// Gallery images - comma-separated IDs.
		if ( isset( $post_data['product_image_gallery'] ) ) {
			$data['product_image_gallery'] = sanitize_text_field( wp_unslash( $post_data['product_image_gallery'] ) );
		}

		// Array of IDs.
		if ( isset( $post_data['product_category'] ) ) {
			$data['product_category'] = array_map( 'absint', (array) $post_data['product_category'] );
		}
		if ( isset( $post_data['product_brand'] ) ) {
			$data['product_brand'] = array_map( 'absint', (array) $post_data['product_brand'] );
		}
		if ( isset( $post_data['product_tags'] ) ) {
			$data['product_tags'] = array_map( 'absint', (array) $post_data['product_tags'] );
		}
		if ( isset( $post_data['upsell_ids'] ) ) {
			$data['upsell_ids'] = array_map( 'absint', (array) $post_data['upsell_ids'] );
		}
		if ( isset( $post_data['crosssell_ids'] ) ) {
			$data['crosssell_ids'] = array_map( 'absint', (array) $post_data['crosssell_ids'] );
		}

		return $data;
	}
}
