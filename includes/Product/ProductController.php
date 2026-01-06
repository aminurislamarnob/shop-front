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
		add_action( 'msf_load_new_product_template', array( $this, 'load_new_product_template' ) );
		add_action( 'msf_load_edit_product_template', array( $this, 'load_edit_product_template' ) );
		add_action( 'msf_dashboard_product_add_form', array( $this, 'load_product_form' ) );
		add_action( 'msf_dashboard_product_edit_form', array( $this, 'load_product_edit_form' ) );
		add_action( 'wp_ajax_msfc_add_product_action', array( $this, 'handle_add_product' ) );
		add_action( 'wp_ajax_msfc_edit_product_action', array( $this, 'handle_edit_product' ) );
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
		msf_get_template_part( 'products/add-new-product', '', $template_args );
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
		msf_get_template_part( 'products/edit-product', '', $template_args );
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
		msf_get_template_part( 'products/product-form', '', $template_args );
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
		msf_get_template_part( 'products/product-form', '', $template_args );
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
			wp_send_json_success( 
				array( 
					'message' => __( 'Product successfully created', 'shop-front' ),
					'context' => 'add'
				) 
			);
		} else {
			wp_send_json_error(
				array(
					'error' => __( 'Something wrong, please try again later', 'shop-front' ),
					'context' => 'add'
				)
			);
		}
	}

	/**
	 * Handle the AJAX request for update a product.
	 */
	public function handle_edit_product() {
		// Verify the nonce.
		if ( ! isset( $_POST['msfc_edit_product_nonce'] ) || ! wp_verify_nonce( wp_unslash( $_POST['msfc_edit_product_nonce'] ), '_msfc_edit_product_' ) ) {
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
			wp_send_json_success( 
				array(
					'message' => __( 'Product successfully updated', 'shop-front' ),
					'context' => 'edit'
				)
			);
			
		} else {
			wp_send_json_error(
				array(
					'error' => __( 'Something wrong, please try again later', 'shop-front' ),
					'context' => 'edit'
				)
			);
		}
	}

}