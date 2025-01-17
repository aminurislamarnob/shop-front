<?php

namespace PluginizeLab\ShopFront\ProductCategory;

/**
 * Plugin product categories controller class
 */
class CategoryController {
	/**
	 * The constructor.
	 */
	public function __construct() {
		add_action( 'wp_ajax_msfc_add_product_category', array( $this, 'handle_add_category' ) );
		add_action( 'wp_ajax_msfc_edit_product_category', array( $this, 'handle_edit_category' ) );
		add_action( 'wp_ajax_msfc_delete_product_category', array( $this, 'handle_delete_category' ) );
	}

	/**
	 * Handle the AJAX request for adding a new product category.
	 */
	public function handle_add_category() {

		// Verify the nonce.
		if ( ! isset( $_POST['msfc_add_product_category_nonce'] ) || ! wp_verify_nonce( wp_unslash( $_POST['msfc_add_product_category_nonce'] ), '_msfc_add_product_category_' ) ) {
			wp_send_json_error( array( 'error' => __( 'Nonce verification failed', 'shop-front' ) ) );
		}

		// Check user permissions.
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_send_json_error( array( 'error' => __( 'You do not have permission to perform this action.', 'shop-front' ) ) );
		}

		// Validate inputs.
		$category_name   = isset( $_POST['product_category_name'] ) ? sanitize_text_field( wp_unslash( $_POST['product_category_name'] ) ) : '';
		$parent_category = isset( $_POST['product_parent_category'] ) ? sanitize_text_field( wp_unslash( $_POST['product_parent_category'] ) ) : '';
		$description     = isset( $_POST['product_category_description'] ) ? sanitize_textarea_field( wp_unslash( $_POST['product_category_description'] ) ) : '';

		if ( empty( $category_name ) ) {
			wp_send_json_error( array( 'error' => __( 'Category Name is required', 'shop-front' ) ) );
		}

		// Check for parent category.
		$parent_term = $parent_category ? get_term_by( 'slug', $parent_category, 'product_cat' ) : null;
		$parent_id   = $parent_term ? $parent_term->term_id : 0;

		// Create a new category.
		$new_category = wp_insert_term(
			$category_name,
			'product_cat',
			array(
				'parent'      => $parent_id,
				'description' => $description,
			)
		);

		if ( is_wp_error( $new_category ) ) {
			wp_send_json_error( array( 'error' => $new_category->get_error_message() ) );
		}

		wp_send_json_success( array( 'message' => __( 'Category successfully created', 'shop-front' ) ) );
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
