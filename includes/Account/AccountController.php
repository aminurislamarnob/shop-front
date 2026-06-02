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
		add_filter( 'pre_get_avatar_data', array( $this, 'filter_custom_avatar' ), 10, 2 );
	}

	/**
	 * Use the StoreSuite custom profile picture for a user's avatar when set.
	 *
	 * @param array $args        Avatar data arguments.
	 * @param mixed $id_or_email User ID, email, comment, post, or user object.
	 * @return array
	 */
	public function filter_custom_avatar( $args, $id_or_email ) {
		if ( ! empty( $args['force_default'] ) ) {
			return $args;
		}

		$user_id = 0;

		if ( is_numeric( $id_or_email ) ) {
			$user_id = (int) $id_or_email;
		} elseif ( is_string( $id_or_email ) && is_email( $id_or_email ) ) {
			$user    = get_user_by( 'email', $id_or_email );
			$user_id = $user ? $user->ID : 0;
		} elseif ( $id_or_email instanceof \WP_User ) {
			$user_id = $id_or_email->ID;
		} elseif ( $id_or_email instanceof \WP_Post ) {
			$user_id = (int) $id_or_email->post_author;
		} elseif ( $id_or_email instanceof \WP_Comment ) {
			$user_id = (int) $id_or_email->user_id;
		}

		if ( $user_id <= 0 ) {
			return $args;
		}

		$attachment_id = (int) get_user_meta( $user_id, 'storesuite_profile_picture_id', true );
		if ( ! $attachment_id ) {
			return $args;
		}

		$size = ! empty( $args['size'] ) ? (int) $args['size'] : 96;
		$url  = wp_get_attachment_image_url( $attachment_id, array( $size, $size ) );
		if ( ! $url ) {
			return $args;
		}

		$args['url']          = $url;
		$args['found_avatar'] = true;

		return $args;
	}

	/**
	 * Save posted billing & shipping address fields onto the customer.
	 *
	 * Mirrors WooCommerce's edit-address handling using the country-aware
	 * address field definitions, so locale fields and state/country are
	 * sanitized and stored correctly.
	 *
	 * @param \WC_Customer $customer Customer to update.
	 * @return void
	 */
	private function save_address_fields( $customer ) {
		foreach ( array( 'billing', 'shipping' ) as $type ) {
			$country_key = $type . '_country';
			// phpcs:ignore WordPress.Security.NonceVerification.Missing -- nonce verified in caller.
			$country = isset( $_POST[ $country_key ] ) ? wc_clean( wp_unslash( $_POST[ $country_key ] ) ) : '';
			$fields  = WC()->countries->get_address_fields( $country, $type . '_' );

			foreach ( $fields as $key => $field ) {
				// phpcs:ignore WordPress.Security.NonceVerification.Missing -- nonce verified in caller.
				if ( ! isset( $_POST[ $key ] ) ) {
					continue;
				}

				// phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- wc_clean sanitizes; nonce verified in caller.
				$value  = wc_clean( wp_unslash( $_POST[ $key ] ) );
				$setter = 'set_' . $key;

				if ( is_callable( array( $customer, $setter ) ) ) {
					$customer->{$setter}( $value );
				} else {
					$customer->update_meta_data( $key, $value );
				}
			}
		}
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
		$account_website      = isset( $_POST['account_website'] ) ? esc_url_raw( wp_unslash( $_POST['account_website'] ) ) : '';
		$account_description  = isset( $_POST['account_description'] ) ? sanitize_textarea_field( wp_unslash( $_POST['account_description'] ) ) : '';
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
		} elseif ( ! empty( $pass1 ) && empty( $pass_cur ) ) {
			$save_pass = false;
			wp_send_json_error( array( 'error' => __( 'Please enter your current password.', 'storesuite' ) ) );
		} elseif ( ! empty( $pass1 ) && empty( $pass2 ) ) {
			$save_pass = false;
			wp_send_json_error( array( 'error' => __( 'Please re-enter your password.', 'storesuite' ) ) );
		} elseif ( ( ! empty( $pass1 ) || ! empty( $pass2 ) ) && $pass1 !== $pass2 ) {
			$save_pass = false;
			wp_send_json_error( array( 'error' => __( 'New passwords do not match.', 'storesuite' ) ) );
		} elseif ( ! empty( $pass1 ) && ! wp_check_password( $pass_cur, $current_user->user_pass, $current_user->ID ) ) {
			$save_pass = false;
			wp_send_json_error( array( 'error' => __( 'Your current password is incorrect.', 'storesuite' ) ) );
		}

		$user               = new \stdClass();
		$user->ID           = $user_id;
		$user->first_name   = $account_first_name;
		$user->last_name    = $account_last_name;
		$user->display_name = $account_display_name;
		$user->user_email   = $account_email;
		$user->user_url     = $account_website;
		$user->description  = $account_description;
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

			// Billing & shipping address fields (Address tab).
			$this->save_address_fields( $customer );

			$customer->save();
		} catch ( \WC_Data_Exception $e ) {
			wp_send_json_error( array( 'error' => $e->getMessage() ) );
		} catch ( \Exception $e ) {
			/* translators: %s: Error message. */
			wp_send_json_error( array( 'error' => sprintf( __( 'An error occurred while saving account details: %s', 'storesuite' ), $e->getMessage() ) ) );
		}

		// Profile picture (media library attachment ID; empty clears it).
		$profile_picture_id = isset( $_POST['account_profile_picture_id'] ) ? absint( wp_unslash( $_POST['account_profile_picture_id'] ) ) : 0;
		if ( $profile_picture_id > 0 ) {
			update_user_meta( $user->ID, 'storesuite_profile_picture_id', $profile_picture_id );
		} else {
			delete_user_meta( $user->ID, 'storesuite_profile_picture_id' );
		}

		do_action( 'woocommerce_save_account_details', $user->ID );

		wp_send_json_success( array( 'message' => __( 'Account details changed successfully.', 'storesuite' ) ) );
	}
}
