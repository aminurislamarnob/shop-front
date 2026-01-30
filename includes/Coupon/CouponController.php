<?php

namespace PluginizeLab\StoreSuite\Coupon;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Plugin coupon controller class
 */
class CouponController {
	/**
	 * The constructor.
	 */
	public function __construct() {
		add_action( 'storesuite_load_coupons_template', array( $this, 'load_coupons_template' ) );
		add_action( 'storesuite_dashboard_coupon_add_form', array( $this, 'load_coupon_add_form' ) );
		add_action( 'storesuite_dashboard_coupon_edit_form', array( $this, 'load_coupon_edit_form' ) );
		add_action( 'wp_ajax_storesuite_add_coupon', array( $this, 'handle_add_coupon' ) );
		add_action( 'wp_ajax_storesuite_edit_coupon', array( $this, 'handle_edit_coupon' ) );
		add_action( 'wp_ajax_storesuite_delete_coupon', array( $this, 'handle_delete_coupon' ) );
	}

	/**
	 * Load the coupons list template.
	 *
	 * @param array $query_vars The query variables.
	 *
	 * @return void
	 */
	public function load_coupons_template( $query_vars ) {
		$template_args = array(
			'query_vars' => $query_vars,
		);
		storesuite_get_template_part( 'coupons/coupons', '', $template_args );
	}

	/**
	 * Load the coupon add form template.
	 *
	 * @param array $query_vars The query variables.
	 *
	 * @return void
	 */
	public function load_coupon_add_form( $query_vars ) {
		$template_args = array(
			'query_vars'    => $query_vars,
			'template_type' => 'add-new-coupon',
		);
		storesuite_get_template_part( 'coupons/coupon-form', '', $template_args );
	}

	/**
	 * Load the coupon edit form template.
	 *
	 * @param array $query_vars The query variables.
	 *
	 * @return void
	 */
	public function load_coupon_edit_form( $query_vars ) {
		$template_args = array(
			'query_vars'    => $query_vars,
			'template_type' => 'edit-coupon',
		);
		storesuite_get_template_part( 'coupons/coupon-form', '', $template_args );
	}

	/**
	 * Handle the AJAX request for adding a new coupon.
	 */
	public function handle_add_coupon() {
		// Verify the nonce.
		if ( ! isset( $_POST['storesuite_add_coupon_nonce'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['storesuite_add_coupon_nonce'] ) ), '_storesuite_add_coupon_' ) ) {
			wp_send_json_error( array( 'error' => __( 'Nonce verification failed', 'storesuite' ) ) );
		}

		// Check user permissions.
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_send_json_error( array( 'error' => __( 'You do not have permission to perform this action.', 'storesuite' ) ) );
		}

		// Validate coupon code.
		if ( empty( $_POST['coupon_code'] ) ) {
			wp_send_json_error( array( 'error' => __( 'Coupon code is required.', 'storesuite' ) ) );
		}

		// Check for duplicate coupon code.
		$coupon_code = wc_format_coupon_code( sanitize_text_field( wp_unslash( $_POST['coupon_code'] ) ) );
		$existing_id = wc_get_coupon_id_by_code( $coupon_code );

		if ( $existing_id ) {
			wp_send_json_error( array( 'error' => __( 'Coupon code already exists. Please choose a different code.', 'storesuite' ) ) );
		}

		$response = ( new CouponManager() )->create_coupon( $_POST );

		if ( is_wp_error( $response ) ) {
			wp_send_json_error( array( 'error' => $response->get_error_message() ) );
		}

		if ( is_int( $response ) && $response > 0 ) {
			wp_send_json_success(
				array(
					'message'   => __( 'Coupon successfully created', 'storesuite' ),
					'context'   => 'add',
					'coupon_id' => $response,
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
	 * Handle the AJAX request for editing a coupon.
	 */
	public function handle_edit_coupon() {
		// Verify the nonce.
		if ( ! isset( $_POST['storesuite_edit_coupon_nonce'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['storesuite_edit_coupon_nonce'] ) ), '_storesuite_edit_coupon_' ) ) {
			wp_send_json_error( array( 'error' => __( 'Nonce verification failed', 'storesuite' ) ) );
		}

		// Check user permissions.
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_send_json_error( array( 'error' => __( 'You do not have permission to perform this action.', 'storesuite' ) ) );
		}

		$coupon_id = isset( $_POST['coupon_id'] ) ? absint( $_POST['coupon_id'] ) : 0;

		if ( ! $coupon_id ) {
			wp_send_json_error( array( 'error' => __( 'Invalid coupon ID.', 'storesuite' ) ) );
		}

		// Validate coupon code if provided.
		if ( isset( $_POST['coupon_code'] ) && ! empty( $_POST['coupon_code'] ) ) {
			$coupon_code = wc_format_coupon_code( sanitize_text_field( wp_unslash( $_POST['coupon_code'] ) ) );
			$existing_id = wc_get_coupon_id_by_code( $coupon_code );

			// Check if the code exists and belongs to a different coupon.
			if ( $existing_id && $existing_id !== $coupon_id ) {
				wp_send_json_error( array( 'error' => __( 'Coupon code already exists. Please choose a different code.', 'storesuite' ) ) );
			}
		}

		$response = ( new CouponManager() )->update_coupon( $coupon_id, $_POST );

		if ( is_wp_error( $response ) ) {
			wp_send_json_error( array( 'error' => $response->get_error_message() ) );
		}

		if ( $response ) {
			wp_send_json_success(
				array(
					'message' => __( 'Coupon successfully updated', 'storesuite' ),
					'context' => 'edit',
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
	 * Handle the AJAX request for deleting a coupon.
	 */
	public function handle_delete_coupon() {
		// Verify the nonce.
		if ( ! isset( $_POST['storesuite_delete_coupon_nonce'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['storesuite_delete_coupon_nonce'] ) ), '_storesuite_delete_coupon_' ) ) {
			wp_send_json_error( array( 'error' => __( 'Nonce verification failed', 'storesuite' ) ) );
		}

		// Check user permissions.
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_send_json_error( array( 'error' => __( 'You do not have permission to perform this action.', 'storesuite' ) ) );
		}

		$coupon_id = isset( $_POST['coupon_id'] ) ? absint( $_POST['coupon_id'] ) : 0;

		if ( ! $coupon_id ) {
			wp_send_json_error( array( 'error' => __( 'Invalid coupon ID.', 'storesuite' ) ) );
		}

		// Use CouponManager so hooks like storesuite_coupon_deleted fire consistently.
		$response = ( new CouponManager() )->delete_coupon( $coupon_id, false ); // Soft delete (move to trash).

		if ( is_wp_error( $response ) ) {
			wp_send_json_error( array( 'error' => $response->get_error_message() ) );
		}

		wp_send_json_success( array( 'message' => __( 'Coupon successfully deleted', 'storesuite' ) ) );
	}
}
