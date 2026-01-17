<?php

namespace PluginizeLab\ShopFront\ProductBrand;

/**
 * Plugin product brands controller class
 */
class BrandController {
	/**
	 * The constructor.
	 */
	public function __construct() {
		add_action( 'wp_ajax_msfc_add_product_brand', array( $this, 'handle_add_brand' ) );
		add_action( 'wp_ajax_msfc_edit_product_brand', array( $this, 'handle_edit_brand' ) );
		add_action( 'wp_ajax_msfc_delete_product_brand', array( $this, 'handle_delete_brand' ) );
	}

	/**
	 * Handle the AJAX request for adding a new product brand.
	 */
	public function handle_add_brand() {

		// Verify the nonce.
		if ( ! isset( $_POST['msfc_add_product_brand_nonce'] ) || ! wp_verify_nonce( wp_unslash( $_POST['msfc_add_product_brand_nonce'] ), '_msfc_add_product_brand_' ) ) {
			wp_send_json_error( array( 'error' => __( 'Nonce verification failed', 'shop-front' ) ) );
		}

		// Check user permissions.
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_send_json_error( array( 'error' => __( 'You do not have permission to perform this action.', 'shop-front' ) ) );
		}

		// Validate inputs.
		$brand_name   = isset( $_POST['product_brand_name'] ) ? sanitize_text_field( wp_unslash( $_POST['product_brand_name'] ) ) : '';
		$parent_brand = isset( $_POST['product_parent_brand'] ) ? sanitize_text_field( wp_unslash( $_POST['product_parent_brand'] ) ) : '';
		$description  = isset( $_POST['product_brand_description'] ) ? sanitize_textarea_field( wp_unslash( $_POST['product_brand_description'] ) ) : '';

		if ( empty( $brand_name ) ) {
			wp_send_json_error( array( 'error' => __( 'Brand Name is required', 'shop-front' ) ) );
		}

		// Check for parent brand.
		$parent_term = $parent_brand ? get_term_by( 'slug', $parent_brand, 'product_brand' ) : null;
		$parent_id   = $parent_term ? $parent_term->term_id : 0;

		// Create a new brand.
		$new_brand = wp_insert_term(
			$brand_name,
			'product_brand',
			array(
				'description' => $description,
				'parent'      => $parent_id,
			)
		);

		if ( is_wp_error( $new_brand ) ) {
			wp_send_json_error( array( 'error' => $new_brand->get_error_message() ) );
		}

		do_action( 'msf_product_brand_created', $new_brand );

		wp_send_json_success( array( 'message' => __( 'Brand successfully created', 'shop-front' ) ) );
	}

	/**
	 * Handle the AJAX request for editing an existing product brand.
	 */
	public function handle_edit_brand() {

		// Verify the nonce.
		if ( ! isset( $_POST['msfc_edit_product_brand_nonce'] ) || ! wp_verify_nonce( wp_unslash( $_POST['msfc_edit_product_brand_nonce'] ), '_msfc_edit_product_brand_' ) ) {
			wp_send_json_error( array( 'error' => __( 'Nonce verification failed', 'shop-front' ) ) );
		}

		// Check user permissions.
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_send_json_error( array( 'error' => __( 'You do not have permission to perform this action.', 'shop-front' ) ) );
		}

		// Validate inputs.
		$brand_id     = isset( $_POST['brand_id'] ) ? absint( $_POST['brand_id'] ) : 0;
		$brand_name   = isset( $_POST['product_brand_name'] ) ? sanitize_text_field( wp_unslash( $_POST['product_brand_name'] ) ) : '';
		$parent_brand = isset( $_POST['product_parent_brand'] ) ? sanitize_text_field( wp_unslash( $_POST['product_parent_brand'] ) ) : '';
		$description  = isset( $_POST['product_brand_description'] ) ? sanitize_textarea_field( wp_unslash( $_POST['product_brand_description'] ) ) : '';

		if ( empty( $brand_id ) ) {
			wp_send_json_error( array( 'error' => __( 'Brand ID is required', 'shop-front' ) ) );
		}

		if ( empty( $brand_name ) ) {
			wp_send_json_error( array( 'error' => __( 'Brand Name is required', 'shop-front' ) ) );
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
				'description' => $description,
				'parent'      => $parent_id,
			)
		);

		if ( is_wp_error( $updated_brand ) ) {
			wp_send_json_error( array( 'error' => $updated_brand->get_error_message() ) );
		}

		do_action( 'msf_product_brand_updated', $updated_brand );

		wp_send_json_success( array( 'message' => __( 'Brand successfully updated', 'shop-front' ) ) );
	}

	/**
	 * Handle the AJAX request for deleting an existing product brand.
	 */
	public function handle_delete_brand() {
		// Verify the nonce.
		if ( ! isset( $_POST['msfc_delete_product_brand_nonce'] ) || ! wp_verify_nonce( wp_unslash( $_POST['msfc_delete_product_brand_nonce'] ), '_msfc_delete_nonce_' ) ) {
			wp_send_json_error( array( 'error' => __( 'Nonce verification failed', 'shop-front' ) ) );
		}

		// Check user permissions.
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_send_json_error( array( 'error' => __( 'You do not have permission to perform this action.', 'shop-front' ) ) );
		}

		// Validate brand ID.
		$brand_id = isset( $_POST['id'] ) ? intval( $_POST['id'] ) : 0;
		if ( ! $brand_id || ! term_exists( $brand_id, 'product_brand' ) ) {
			wp_send_json_error( array( 'error' => __( 'Invalid brand ID', 'shop-front' ) ) );
		}

		// Attempt to delete the brand.
		$result = wp_delete_term( $brand_id, 'product_brand' );

		if ( is_wp_error( $result ) ) {
			wp_send_json_error( array( 'error' => $result->get_error_message() ) );
		} elseif ( $result === false ) {
			wp_send_json_error( array( 'error' => __( 'Failed to delete brand', 'shop-front' ) ) );
		}

		do_action( 'msf_product_brand_deleted', $brand_id );

		wp_send_json_success( array( 'message' => __( 'Brand successfully deleted', 'shop-front' ) ) );
	}
}
