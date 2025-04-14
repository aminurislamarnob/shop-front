<?php

namespace PluginizeLab\ShopFront\Product;

/**
 * Plugin product controller class
 */
class ProductController {
	/**
	 * The constructor.
	 */
	public function __construct() {
		add_action( 'wp_ajax_msfc_add_product_action', array( $this, 'handle_add_product' ) );
		// add_action( 'wp_ajax_msfc_edit_product_category', array( $this, 'handle_edit_category' ) );
		// add_action( 'wp_ajax_msfc_delete_product_category', array( $this, 'handle_delete_category' ) );
	}

	/**
	 * Handle the AJAX request for adding a new product.
	 */
	public function handle_add_product() {

		// Verify the nonce.
		if ( ! isset( $_POST['msfc_add_product_nonce'] ) || ! wp_verify_nonce( wp_unslash( $_POST['msfc_add_product_nonce'] ), '_msfc_add_product_' ) ) {
			wp_send_json_error( array( 'error' => __( 'Nonce verification failed', 'shop-front' ) ) );
		}

		// Check user permissions.
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_send_json_error( array( 'error' => __( 'You do not have permission to perform this action.', 'shop-front' ) ) );
		}

		$response = ( new ProductManager() )->msf_save_product( $_POST );

		if ( is_wp_error( $response ) ) {
			wp_send_json_error( $response->get_error_message() );
		}

		if ( is_int( $response ) ) {
			wp_send_json_success( array( 'message' => __( 'Product successfully created', 'shop-front' ) ) );
		} else {
			wp_send_json_error( array( 'error' => __( 'Something wrong, please try again later', 'shop-front' ) ) );
		}
	}

	/**
	 * Handle the AJAX request for editing an existing product category.
	 */
	public function handle_edit_category() {

		// Verify the nonce.
		if ( ! isset( $_POST['msfc_edit_product_category_nonce'] ) || ! wp_verify_nonce( wp_unslash( $_POST['msfc_edit_product_category_nonce'] ), '_msfc_edit_product_category_' ) ) {
			wp_send_json_error( array( 'error' => __( 'Nonce verification failed', 'shop-front' ) ) );
		}

		// Check user permissions.
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_send_json_error( array( 'error' => __( 'You do not have permission to perform this action.', 'shop-front' ) ) );
		}

		// Validate inputs.
		$category_id     = isset( $_POST['category_id'] ) ? absint( $_POST['category_id'] ) : 0;
		$category_name   = isset( $_POST['product_category_name'] ) ? sanitize_text_field( wp_unslash( $_POST['product_category_name'] ) ) : '';
		$parent_category = isset( $_POST['product_parent_category'] ) ? sanitize_text_field( wp_unslash( $_POST['product_parent_category'] ) ) : '';
		$description     = isset( $_POST['product_category_description'] ) ? sanitize_textarea_field( wp_unslash( $_POST['product_category_description'] ) ) : '';

		if ( empty( $category_id ) ) {
			wp_send_json_error( array( 'error' => __( 'Category ID is required', 'shop-front' ) ) );
		}

		if ( empty( $category_name ) ) {
			wp_send_json_error( array( 'error' => __( 'Category Name is required', 'shop-front' ) ) );
		}

		// Check for parent category.
		$parent_term = $parent_category ? get_term_by( 'slug', $parent_category, 'product_cat' ) : null;
		$parent_id   = $parent_term ? $parent_term->term_id : 0;

		// Update the category.
		$updated_category = wp_update_term(
			$category_id,
			'product_cat',
			array(
				'name'        => $category_name,
				'parent'      => $parent_id,
				'description' => $description,
			)
		);

		if ( is_wp_error( $updated_category ) ) {
			wp_send_json_error( array( 'error' => $updated_category->get_error_message() ) );
		}

		wp_send_json_success( array( 'message' => __( 'Category successfully updated', 'shop-front' ) ) );
	}

	/**
	 * Handle the AJAX request for deleting an existing product category.
	 */
	public function handle_delete_category() {
		// Verify the nonce.
		if ( ! isset( $_POST['msfc_delete_product_category_nonce'] ) || ! wp_verify_nonce( wp_unslash( $_POST['msfc_delete_product_category_nonce'] ), '_msfc_delete_product_category_' ) ) {
			wp_send_json_error( array( 'error' => __( 'Nonce verification failed', 'shop-front' ) ) );
		}

		// Check user permissions.
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_send_json_error( array( 'error' => __( 'You do not have permission to perform this action.', 'shop-front' ) ) );
		}

		// Validate category ID.
		$category_id = isset( $_POST['id'] ) ? intval( $_POST['id'] ) : 0;
		if ( ! $category_id || ! term_exists( $category_id, 'product_cat' ) ) {
			wp_send_json_error( array( 'error' => __( 'Invalid category ID', 'shop-front' ) ) );
		}

		// Attempt to delete the category.
		$result = wp_delete_term( $category_id, 'product_cat' );

		if ( is_wp_error( $result ) ) {
			wp_send_json_error( array( 'error' => $result->get_error_message() ) );
		} elseif ( $result === false ) {
			wp_send_json_error( array( 'error' => __( 'Failed to delete category', 'shop-front' ) ) );
		}

		wp_send_json_success( array( 'message' => __( 'Category successfully deleted', 'shop-front' ) ) );
	}
}
