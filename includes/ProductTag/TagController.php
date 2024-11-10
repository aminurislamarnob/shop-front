<?php

namespace PluginizeLab\ShopFront\ProductTag;

/**
 * Plugin product tag controller class
 */
class TagController {
	/**
	 * The constructor.
	 */
	public function __construct() {
		add_action( 'wp_ajax_msfc_add_product_tag', array( $this, 'handle_add_tag' ) );
		add_action( 'wp_ajax_msfc_edit_product_tag', array( $this, 'handle_edit_tag' ) );
		add_action( 'wp_ajax_msfc_delete_product_tag', array( $this, 'handle_delete_tag' ) );
	}

	/**
	 * Handle the AJAX request for adding a new product tag.
	 */
	public function handle_add_tag() {

		// Verify the nonce.
		if ( ! isset( $_POST['msfc_add_product_tag_nonce'] ) || ! wp_verify_nonce( wp_unslash( $_POST['msfc_add_product_tag_nonce'] ), '_msfc_add_product_tag_' ) ) {
			wp_send_json_error( array( 'error' => __( 'Nonce verification failed', 'shop-front' ) ) );
		}

		// Check user permissions.
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_send_json_error( array( 'error' => __( 'You do not have permission to perform this action.', 'shop-front' ) ) );
		}

		// Validate inputs.
		$name        = isset( $_POST['name'] ) ? sanitize_text_field( wp_unslash( $_POST['name'] ) ) : '';
		$description = isset( $_POST['description'] ) ? sanitize_textarea_field( wp_unslash( $_POST['description'] ) ) : '';

		if ( empty( $name ) ) {
			wp_send_json_error( array( 'error' => __( 'Tag Name is required', 'shop-front' ) ) );
		}

		// Create a new category.
		$new_category = wp_insert_term(
			$name,
			'product_tag',
			array(
				'description' => $description,
			)
		);

		if ( is_wp_error( $new_category ) ) {
			wp_send_json_error( array( 'error' => $new_category->get_error_message() ) );
		}

		wp_send_json_success( array( 'message' => __( 'Tag successfully created', 'shop-front' ) ) );
	}

	/**
	 * Handle the AJAX request for editing an existing product category.
	 */
	public function handle_edit_tag() {

		// Verify the nonce.
		if ( ! isset( $_POST['msfc_edit_product_tag_nonce'] ) || ! wp_verify_nonce( wp_unslash( $_POST['msfc_edit_product_tag_nonce'] ), '_msfc_edit_product_tag_' ) ) {
			wp_send_json_error( array( 'error' => __( 'Nonce verification failed', 'shop-front' ) ) );
		}

		// Check user permissions.
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_send_json_error( array( 'error' => __( 'You do not have permission to perform this action.', 'shop-front' ) ) );
		}

		// Validate inputs.
		$tag_id      = isset( $_POST['tag_id'] ) ? absint( $_POST['tag_id'] ) : 0;
		$name        = isset( $_POST['name'] ) ? sanitize_text_field( wp_unslash( $_POST['name'] ) ) : '';
		$description = isset( $_POST['description'] ) ? sanitize_textarea_field( wp_unslash( $_POST['description'] ) ) : '';

		if ( empty( $tag_id ) ) {
			wp_send_json_error( array( 'error' => __( 'Tag ID is required', 'shop-front' ) ) );
		}

		if ( empty( $name ) ) {
			wp_send_json_error( array( 'error' => __( 'Tag Name is required', 'shop-front' ) ) );
		}

		// Update the tag.
		$updated_tag = wp_update_term(
			$tag_id,
			'product_tag',
			array(
				'name'        => $name,
				'description' => $description,
			)
		);

		if ( is_wp_error( $updated_tag ) ) {
			wp_send_json_error( array( 'error' => $updated_tag->get_error_message() ) );
		}

		wp_send_json_success( array( 'message' => __( 'Tag successfully updated', 'shop-front' ) ) );
	}

	/**
	 * Handle the AJAX request for deleting an existing product category.
	 */
	public function handle_delete_tag() {
		// Verify the nonce.
		if ( ! isset( $_POST['msfc_delete_product_tag_nonce'] ) || ! wp_verify_nonce( wp_unslash( $_POST['msfc_delete_product_tag_nonce'] ), '_msfc_delete_product_category_' ) ) {
			wp_send_json_error( array( 'error' => __( 'Nonce verification failed', 'shop-front' ) ) );
		}

		// Check user permissions.
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_send_json_error( array( 'error' => __( 'You do not have permission to perform this action.', 'shop-front' ) ) );
		}

		// Validate category ID.
		$tag_id = isset( $_POST['id'] ) ? intval( $_POST['id'] ) : 0;
		if ( ! $tag_id || ! term_exists( $tag_id, 'product_tag' ) ) {
			wp_send_json_error( array( 'error' => __( 'Invalid tag ID', 'shop-front' ) ) );
		}

		// Attempt to delete the category.
		$result = wp_delete_term( $tag_id, 'product_tag' );

		if ( is_wp_error( $result ) ) {
			wp_send_json_error( array( 'error' => $result->get_error_message() ) );
		} elseif ( $result === false ) {
			wp_send_json_error( array( 'error' => __( 'Failed to delete tag', 'shop-front' ) ) );
		}

		wp_send_json_success( array( 'message' => __( 'Tag successfully deleted', 'shop-front' ) ) );
	}
}
