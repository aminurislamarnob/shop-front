<?php

namespace PluginizeLab\StoreSuite\ProductBrand;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Plugin product brands controller class
 */
class BrandController {
	/**
	 * The constructor.
	 */
	public function __construct() {
		add_action( 'wp_ajax_storesuite_add_product_brand', array( $this, 'handle_add_brand' ) );
		add_action( 'wp_ajax_storesuite_edit_product_brand', array( $this, 'handle_edit_brand' ) );
		add_action( 'wp_ajax_storesuite_delete_product_brand', array( $this, 'handle_delete_brand' ) );
	}

	/**
	 * Handle the AJAX request for adding a new product brand.
	 */
	public function handle_add_brand() {

		// Verify the nonce.
		if ( ! isset( $_POST['storesuite_add_product_brand_nonce'] ) || ! wp_verify_nonce( wp_unslash( $_POST['storesuite_add_product_brand_nonce'] ), '_storesuite_add_product_brand_' ) ) {
			wp_send_json_error( array( 'error' => __( 'Nonce verification failed', 'storesuite' ) ) );
		}

		// Check user permissions.
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_send_json_error( array( 'error' => __( 'You do not have permission to perform this action.', 'storesuite' ) ) );
		}

		// Validate inputs.
		$brand_name   = isset( $_POST['product_brand_name'] ) ? sanitize_text_field( wp_unslash( $_POST['product_brand_name'] ) ) : '';
		$parent_brand = isset( $_POST['product_parent_brand'] ) ? sanitize_text_field( wp_unslash( $_POST['product_parent_brand'] ) ) : '';
		$description  = isset( $_POST['product_brand_description'] ) ? sanitize_textarea_field( wp_unslash( $_POST['product_brand_description'] ) ) : '';
		$thumbnail_id = isset( $_POST['product_brand_thumbnail_id'] ) ? absint( $_POST['product_brand_thumbnail_id'] ) : 0;
		$brand_slug   = isset( $_POST['product_brand_slug'] ) ? sanitize_title( wp_unslash( $_POST['product_brand_slug'] ) ) : '';

		if ( empty( $brand_name ) ) {
			wp_send_json_error( array( 'error' => __( 'Brand Name is required', 'storesuite' ) ) );
		}

		// Generate slug from name if not provided.
		if ( empty( $brand_slug ) ) {
			$brand_slug = sanitize_title( $brand_name );
		}

		// Check if slug already exists.
		$existing_term = get_term_by( 'slug', $brand_slug, 'product_brand' );
		if ( $existing_term ) {
			wp_send_json_error( array( 'error' => __( 'Brand slug already exists. Please choose a different slug.', 'storesuite' ) ) );
		}

		// Check for parent brand.
		$parent_term = $parent_brand ? get_term_by( 'slug', $parent_brand, 'product_brand' ) : null;
		$parent_id   = $parent_term ? $parent_term->term_id : 0;

		// Create a new brand.
		$new_brand = wp_insert_term(
			$brand_name,
			'product_brand',
			array(
				'slug'        => $brand_slug,
				'description' => $description,
				'parent'      => $parent_id,
			)
		);

		if ( is_wp_error( $new_brand ) ) {
			wp_send_json_error( array( 'error' => $new_brand->get_error_message() ) );
		}

		// Save the brand thumbnail if provided.
		if ( $thumbnail_id > 0 ) {
			update_term_meta( $new_brand['term_id'], 'thumbnail_id', $thumbnail_id );
		}

		do_action( 'storesuite_product_brand_created', $new_brand );

		wp_send_json_success( array( 'message' => __( 'Brand successfully created', 'storesuite' ) ) );
	}

	/**
	 * Handle the AJAX request for editing an existing product brand.
	 */
	public function handle_edit_brand() {

		// Verify the nonce.
		if ( ! isset( $_POST['storesuite_edit_product_brand_nonce'] ) || ! wp_verify_nonce( wp_unslash( $_POST['storesuite_edit_product_brand_nonce'] ), '_storesuite_edit_product_brand_' ) ) {
			wp_send_json_error( array( 'error' => __( 'Nonce verification failed', 'storesuite' ) ) );
		}

		// Check user permissions.
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_send_json_error( array( 'error' => __( 'You do not have permission to perform this action.', 'storesuite' ) ) );
		}

		// Validate inputs.
		$brand_id     = isset( $_POST['brand_id'] ) ? absint( $_POST['brand_id'] ) : 0;
		$brand_name   = isset( $_POST['product_brand_name'] ) ? sanitize_text_field( wp_unslash( $_POST['product_brand_name'] ) ) : '';
		$parent_brand = isset( $_POST['product_parent_brand'] ) ? sanitize_text_field( wp_unslash( $_POST['product_parent_brand'] ) ) : '';
		$description  = isset( $_POST['product_brand_description'] ) ? sanitize_textarea_field( wp_unslash( $_POST['product_brand_description'] ) ) : '';
		$thumbnail_id = isset( $_POST['product_brand_thumbnail_id'] ) ? absint( $_POST['product_brand_thumbnail_id'] ) : 0;
		$brand_slug   = isset( $_POST['product_brand_slug'] ) ? sanitize_title( wp_unslash( $_POST['product_brand_slug'] ) ) : '';

		if ( empty( $brand_id ) ) {
			wp_send_json_error( array( 'error' => __( 'Brand ID is required', 'storesuite' ) ) );
		}

		if ( empty( $brand_name ) ) {
			wp_send_json_error( array( 'error' => __( 'Brand Name is required', 'storesuite' ) ) );
		}

		// Get current brand.
		$current_brand = get_term( $brand_id, 'product_brand' );
		if ( ! $current_brand || is_wp_error( $current_brand ) ) {
			wp_send_json_error( array( 'error' => __( 'Brand not found', 'storesuite' ) ) );
		}

		// Generate slug from name if not provided.
		if ( empty( $brand_slug ) ) {
			$brand_slug = sanitize_title( $brand_name );
		}

		// Check if slug already exists (but allow it if it's the current brand's slug).
		if ( $brand_slug !== $current_brand->slug ) {
			$existing_term = get_term_by( 'slug', $brand_slug, 'product_brand' );
			if ( $existing_term && $existing_term->term_id !== $brand_id ) {
				wp_send_json_error( array( 'error' => __( 'Brand slug already exists. Please choose a different slug.', 'storesuite' ) ) );
			}
		}

		// Check for parent brand.
		$parent_term = $parent_brand ? get_term_by( 'slug', $parent_brand, 'product_brand' ) : null;
		$parent_id   = $parent_term ? $parent_term->term_id : 0;

		// Update the brand.
		$updated_brand = wp_update_term(
			$brand_id,
			'product_brand',
			array(
				'name'        => $brand_name,
				'slug'        => $brand_slug,
				'description' => $description,
				'parent'      => $parent_id,
			)
		);

		if ( is_wp_error( $updated_brand ) ) {
			wp_send_json_error( array( 'error' => $updated_brand->get_error_message() ) );
		}

		// Update the brand thumbnail.
		if ( $thumbnail_id > 0 ) {
			update_term_meta( $brand_id, 'thumbnail_id', $thumbnail_id );
		} else {
			delete_term_meta( $brand_id, 'thumbnail_id' );
		}

		do_action( 'storesuite_product_brand_updated', $updated_brand );

		wp_send_json_success( array( 'message' => __( 'Brand successfully updated', 'storesuite' ) ) );
	}

	/**
	 * Handle the AJAX request for deleting an existing product brand.
	 */
	public function handle_delete_brand() {
		// Verify the nonce.
		if ( ! isset( $_POST['storesuite_delete_product_brand_nonce'] ) || ! wp_verify_nonce( wp_unslash( $_POST['storesuite_delete_product_brand_nonce'] ), '_storesuite_delete_nonce_' ) ) {
			wp_send_json_error( array( 'error' => __( 'Nonce verification failed', 'storesuite' ) ) );
		}

		// Check user permissions.
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_send_json_error( array( 'error' => __( 'You do not have permission to perform this action.', 'storesuite' ) ) );
		}

		// Validate brand ID.
		$brand_id = isset( $_POST['id'] ) ? intval( $_POST['id'] ) : 0;
		if ( ! $brand_id || ! term_exists( $brand_id, 'product_brand' ) ) {
			wp_send_json_error( array( 'error' => __( 'Invalid brand ID', 'storesuite' ) ) );
		}

		// Attempt to delete the brand.
		$result = wp_delete_term( $brand_id, 'product_brand' );

		if ( is_wp_error( $result ) ) {
			wp_send_json_error( array( 'error' => $result->get_error_message() ) );
		} elseif ( $result === false ) {
			wp_send_json_error( array( 'error' => __( 'Failed to delete brand', 'storesuite' ) ) );
		}

		do_action( 'storesuite_product_brand_deleted', $brand_id );

		wp_send_json_success( array( 'message' => __( 'Brand successfully deleted', 'storesuite' ) ) );
	}
}
