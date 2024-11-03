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
	}

	/**
	 * Handle the AJAX request for adding a new product category.
	 */
	public function handle_add_category() {
		$unique_action = 'msfc_add_product_category_' . SHOP_FRONT_NONCE_SALT;

		// Verify the nonce.
		if ( ! isset( $_POST['msfc_add_product_category_nonce'] ) || ! wp_verify_nonce( wp_unslash( $_POST['msfc_add_product_category_nonce'] ), $unique_action ) ) {
			wp_send_json_error( array( 'error' => __( 'Nonce verification failed', 'shop-front' ) ) );
		}

		// Check user permissions.
		if ( ! current_user_can( 'manage_categories' ) ) {
			wp_send_json_error( array( 'error' => __( 'You do not have permission to perform this action.', 'shop-front' ) ) );
		}

		// Validate inputs.
		$category_name   = sanitize_text_field( wp_unslash( $_POST['product_category_name'] ) );
		$parent_category = sanitize_text_field( wp_unslash( $_POST['product_parent_category'] ) );
		$description     = sanitize_textarea_field( wp_unslash( $_POST['product_category_description'] ) );

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
}
