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

		$response = ( new ProductManager() )->storesuite_save_product( wp_unslash( $_POST ) );

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

		$response = ( new ProductManager() )->storesuite_save_product( wp_unslash( $_POST ) );

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
}
