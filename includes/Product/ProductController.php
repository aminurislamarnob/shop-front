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
}
