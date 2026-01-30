<?php

namespace PluginizeLab\StoreSuite\ProductTag;

/**
 * Plugin product tag controller class
 */
class TagController {
	/**
	 * The constructor.
	 */
	public function __construct() {
		add_action( 'wp_ajax_storesuite_add_product_tag', array( $this, 'handle_add_tag' ) );
		add_action( 'wp_ajax_storesuite_edit_product_tag', array( $this, 'handle_edit_tag' ) );
		add_action( 'wp_ajax_storesuite_delete_product_tag', array( $this, 'handle_delete_tag' ) );
	}

	/**
	 * Handle the AJAX request for adding a new product tag.
	 */
	public function handle_add_tag() {

		// Verify the nonce.
		if ( ! isset( $_POST['storesuite_add_product_tag_nonce'] ) || ! wp_verify_nonce( wp_unslash( $_POST['storesuite_add_product_tag_nonce'] ), '_storesuite_add_product_tag_' ) ) {
			wp_send_json_error( array( 'error' => __( 'Nonce verification failed', 'storesuite' ) ) );
		}

		// Check user permissions.
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_send_json_error( array( 'error' => __( 'You do not have permission to perform this action.', 'storesuite' ) ) );
		}

		// Validate inputs.
		$name        = isset( $_POST['name'] ) ? sanitize_text_field( wp_unslash( $_POST['name'] ) ) : '';
		$description = isset( $_POST['description'] ) ? sanitize_textarea_field( wp_unslash( $_POST['description'] ) ) : '';
		$slug        = isset( $_POST['slug'] ) ? sanitize_title( wp_unslash( $_POST['slug'] ) ) : '';

		if ( empty( $name ) ) {
			wp_send_json_error( array( 'error' => __( 'Tag Name is required', 'storesuite' ) ) );
		}

		// Generate slug from name if not provided.
		if ( empty( $slug ) ) {
			$slug = sanitize_title( $name );
		}

		// Check if slug already exists.
		$existing_term = get_term_by( 'slug', $slug, 'product_tag' );
		if ( $existing_term ) {
			wp_send_json_error( array( 'error' => __( 'Tag slug already exists. Please choose a different slug.', 'storesuite' ) ) );
		}

		// Create a new tag.
		$new_category = wp_insert_term(
			$name,
			'product_tag',
			array(
				'slug'        => $slug,
				'description' => $description,
			)
		);

		if ( is_wp_error( $new_category ) ) {
			wp_send_json_error( array( 'error' => $new_category->get_error_message() ) );
		}

		wp_send_json_success( array( 'message' => __( 'Tag successfully created', 'storesuite' ) ) );
	}

	/**
	 * Handle the AJAX request for editing an existing product category.
	 */
	public function handle_edit_tag() {

		// Verify the nonce.
		if ( ! isset( $_POST['storesuite_edit_product_tag_nonce'] ) || ! wp_verify_nonce( wp_unslash( $_POST['storesuite_edit_product_tag_nonce'] ), '_storesuite_edit_product_tag_' ) ) {
			wp_send_json_error( array( 'error' => __( 'Nonce verification failed', 'storesuite' ) ) );
		}

		// Check user permissions.
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_send_json_error( array( 'error' => __( 'You do not have permission to perform this action.', 'storesuite' ) ) );
		}

		// Validate inputs.
		$tag_id      = isset( $_POST['tag_id'] ) ? absint( $_POST['tag_id'] ) : 0;
		$name        = isset( $_POST['name'] ) ? sanitize_text_field( wp_unslash( $_POST['name'] ) ) : '';
		$description = isset( $_POST['description'] ) ? sanitize_textarea_field( wp_unslash( $_POST['description'] ) ) : '';
		$slug        = isset( $_POST['slug'] ) ? sanitize_title( wp_unslash( $_POST['slug'] ) ) : '';

		if ( empty( $tag_id ) ) {
			wp_send_json_error( array( 'error' => __( 'Tag ID is required', 'storesuite' ) ) );
		}

		if ( empty( $name ) ) {
			wp_send_json_error( array( 'error' => __( 'Tag Name is required', 'storesuite' ) ) );
		}

		// Get current tag.
		$current_tag = get_term( $tag_id, 'product_tag' );
		if ( ! $current_tag || is_wp_error( $current_tag ) ) {
			wp_send_json_error( array( 'error' => __( 'Tag not found', 'storesuite' ) ) );
		}

		// Generate slug from name if not provided.
		if ( empty( $slug ) ) {
			$slug = sanitize_title( $name );
		}

		// Check if slug already exists (but allow it if it's the current tag's slug).
		if ( $slug !== $current_tag->slug ) {
			$existing_term = get_term_by( 'slug', $slug, 'product_tag' );
			if ( $existing_term && $existing_term->term_id !== $tag_id ) {
				wp_send_json_error( array( 'error' => __( 'Tag slug already exists. Please choose a different slug.', 'storesuite' ) ) );
			}
		}

		// Update the tag.
		$updated_tag = wp_update_term(
			$tag_id,
			'product_tag',
			array(
				'name'        => $name,
				'slug'        => $slug,
				'description' => $description,
			)
		);

		if ( is_wp_error( $updated_tag ) ) {
			wp_send_json_error( array( 'error' => $updated_tag->get_error_message() ) );
		}

		wp_send_json_success( array( 'message' => __( 'Tag successfully updated', 'storesuite' ) ) );
	}

	/**
	 * Handle the AJAX request for deleting an existing product category.
	 */
	public function handle_delete_tag() {
		// Verify the nonce.
		if ( ! isset( $_POST['storesuite_delete_product_tag_nonce'] ) || ! wp_verify_nonce( wp_unslash( $_POST['storesuite_delete_product_tag_nonce'] ), '_storesuite_delete_nonce_' ) ) {
			wp_send_json_error( array( 'error' => __( 'Nonce verification failed', 'storesuite' ) ) );
		}

		// Check user permissions.
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_send_json_error( array( 'error' => __( 'You do not have permission to perform this action.', 'storesuite' ) ) );
		}

		// Validate category ID.
		$tag_id = isset( $_POST['id'] ) ? intval( $_POST['id'] ) : 0;
		if ( ! $tag_id || ! term_exists( $tag_id, 'product_tag' ) ) {
			wp_send_json_error( array( 'error' => __( 'Invalid tag ID', 'storesuite' ) ) );
		}

		// Attempt to delete the category.
		$result = wp_delete_term( $tag_id, 'product_tag' );

		if ( is_wp_error( $result ) ) {
			wp_send_json_error( array( 'error' => $result->get_error_message() ) );
		} elseif ( $result === false ) {
			wp_send_json_error( array( 'error' => __( 'Failed to delete tag', 'storesuite' ) ) );
		}

		wp_send_json_success( array( 'message' => __( 'Tag successfully deleted', 'storesuite' ) ) );
	}
}
