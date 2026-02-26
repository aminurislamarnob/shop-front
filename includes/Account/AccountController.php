<?php
/**
 * Account controller – handles edit account form submissions via AJAX.
 *
 * @package StoreSuite
 */

namespace PluginizeLab\StoreSuite\Account;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Account controller class.
 *
 * Handles save account details via AJAX (same pattern as other StoreSuite forms).
 */
class AccountController {

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'wp_ajax_storesuite_save_account_details', array( $this, 'handle_save_account_details' ) );
	}

	/**
	 * Handle save account details AJAX request.
	 *
	 * @return void
	 */
	public function handle_save_account_details() {
		if ( ! isset( $_POST['storesuite_save_account_details_nonce'] )
			|| ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['storesuite_save_account_details_nonce'] ) ), '_storesuite_save_account_details_' ) ) {
			wp_send_json_error( array( 'error' => __( 'Nonce verification failed', 'storesuite' ) ) );
		}

		$user_id = get_current_user_id();
		if ( $user_id <= 0 ) {
			wp_send_json_error( array( 'error' => __( 'You must be logged in to update account details.', 'storesuite' ) ) );
		}

		$account_first_name   = ! empty( $_POST['account_first_name'] ) ? wc_clean( wp_unslash( $_POST['account_first_name'] ) ) : '';
		$account_last_name    = ! empty( $_POST['account_last_name'] ) ? wc_clean( wp_unslash( $_POST['account_last_name'] ) ) : '';
		$account_display_name = ! empty( $_POST['account_display_name'] ) ? wc_clean( wp_unslash( $_POST['account_display_name'] ) ) : '';
		$account_email        = ! empty( $_POST['account_email'] ) ? wc_clean( wp_unslash( $_POST['account_email'] ) ) : '';
		$pass_cur             = ! empty( $_POST['password_current'] ) ? $_POST['password_current'] : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.MissingUnslash
		$pass1                = ! empty( $_POST['password_1'] ) ? $_POST['password_1'] : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.MissingUnslash
		$pass2                = ! empty( $_POST['password_2'] ) ? $_POST['password_2'] : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.MissingUnslash
		$save_pass            = true;

		$current_user       = get_user_by( 'id', $user_id );
		$current_first_name = $current_user->first_name;
		$current_last_name  = $current_user->last_name;
		$current_email      = $current_user->user_email;

		$required_fields = apply_filters(
			'woocommerce_save_account_details_required_fields',
			array(
				'account_first_name'   => __( 'First name', 'storesuite' ),
				'account_last_name'    => __( 'Last name', 'storesuite' ),
				'account_display_name' => __( 'Display name', 'storesuite' ),
				'account_email'        => __( 'Email address', 'storesuite' ),
			)
		);

		foreach ( $required_fields as $field_key => $field_name ) {
			if ( empty( $_POST[ $field_key ] ) ) {
				/* translators: %s: Field name. */
				wp_send_json_error( array( 'error' => sprintf( __( '%s is a required field.', 'storesuite' ), $field_name ) ) );
			}
		}

		if ( is_email( $account_display_name ) ) {
			wp_send_json_error( array( 'error' => __( 'Display name cannot be changed to email address due to privacy concern.', 'storesuite' ) ) );
		}

		if ( $account_email ) {
			$account_email = sanitize_email( $account_email );
			if ( ! is_email( $account_email ) ) {
				wp_send_json_error( array( 'error' => __( 'Please provide a valid email address.', 'storesuite' ) ) );
			}
			if ( email_exists( $account_email ) && $account_email !== $current_user->user_email ) {
				wp_send_json_error( array( 'error' => __( 'This email address is already registered.', 'storesuite' ) ) );
			}
		}

		if ( ! empty( $pass_cur ) && empty( $pass1 ) && empty( $pass2 ) ) {
			$save_pass = false;
			wp_send_json_error( array( 'error' => __( 'Please fill out all password fields.', 'storesuite' ) ) );
		} else if ( ! empty( $pass1 ) && empty( $pass_cur ) ) {
			$save_pass = false;
			wp_send_json_error( array( 'error' => __( 'Please enter your current password.', 'storesuite' ) ) );
		} else if ( ! empty( $pass1 ) && empty( $pass2 ) ) {
			$save_pass = false;
			wp_send_json_error( array( 'error' => __( 'Please re-enter your password.', 'storesuite' ) ) );
		} else if ( ( ! empty( $pass1 ) || ! empty( $pass2 ) ) && $pass1 !== $pass2 ) {
			$save_pass = false;
			wp_send_json_error( array( 'error' => __( 'New passwords do not match.', 'storesuite' ) ) );
		} else if ( ! empty( $pass1 ) && ! wp_check_password( $pass_cur, $current_user->user_pass, $current_user->ID ) ) {
			$save_pass = false;
			wp_send_json_error( array( 'error' => __( 'Your current password is incorrect.', 'storesuite' ) ) );
		}

		$user               = new \stdClass();
		$user->ID           = $user_id;
		$user->first_name   = $account_first_name;
		$user->last_name    = $account_last_name;
		$user->display_name = $account_display_name;
		$user->user_email   = $account_email;
		if ( $pass1 && $save_pass ) {
			$user->user_pass = $pass1;
		}

		$errors = new \WP_Error();
		do_action_ref_array( 'woocommerce_save_account_details_errors', array( &$errors, &$user ) );

		if ( $errors->get_error_messages() ) {
			wp_send_json_error( array( 'error' => implode( ' ', $errors->get_error_messages() ) ) );
		}

		$updated = wp_update_user( $user );
		if ( is_wp_error( $updated ) ) {
			wp_send_json_error( array( 'error' => $updated->get_error_message() ) );
		}

		try {
			$customer = new \WC_Customer( $user->ID );

			if ( isset( $user->user_email ) && is_email( $user->user_email ) && $current_email !== $user->user_email ) {
				$customer->set_billing_email( $user->user_email );
			}
			if ( $current_first_name !== $user->first_name ) {
				$customer->set_billing_first_name( $user->first_name );
			}
			if ( $current_last_name !== $user->last_name ) {
				$customer->set_billing_last_name( $user->last_name );
			}

			$customer->save();
		} catch ( \WC_Data_Exception $e ) {
			wp_send_json_error( array( 'error' => $e->getMessage() ) );
		} catch ( \Exception $e ) {
			/* translators: %s: Error message. */
			wp_send_json_error( array( 'error' => sprintf( __( 'An error occurred while saving account details: %s', 'storesuite' ), $e->getMessage() ) ) );
		}

		do_action( 'woocommerce_save_account_details', $user->ID );

		wp_send_json_success( array( 'message' => __( 'Account details changed successfully.', 'storesuite' ) ) );
	}
}
