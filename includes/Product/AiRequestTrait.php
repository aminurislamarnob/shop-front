<?php

namespace PluginizeLab\StoreSuite\Product;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Shared helpers for product AI AJAX endpoints.
 *
 * Centralizes the core AI Client readiness check and the per-request guard
 * (nonce + capability + availability) used by ProductAI and ProductImageAI, so
 * the gate logic and error copy live in one place.
 */
trait AiRequestTrait {

	/**
	 * Whether the core AI Client supports a given capability in this environment.
	 *
	 * Requires WordPress 7.0+ (core AI Client) and AI support enabled. Memoized
	 * per capability method for the duration of the request.
	 *
	 * @param string $capability_method AI Client readiness method, e.g.
	 *                                   `is_supported_for_text_generation`.
	 * @return bool
	 */
	protected static function is_ai_capability_supported( $capability_method ) {
		static $supported = array();

		if ( isset( $supported[ $capability_method ] ) ) {
			return $supported[ $capability_method ];
		}

		$supported[ $capability_method ] = function_exists( 'wp_ai_client_prompt' )
			&& function_exists( 'wp_supports_ai' )
			&& wp_supports_ai()
			&& wp_ai_client_prompt()->{ $capability_method }();

		return $supported[ $capability_method ];
	}

	/**
	 * Enforce the capability and availability gates for a product AI request.
	 *
	 * Call after check_ajax_referer(): sends a JSON error and halts when the
	 * current user lacks permission or the required AI capability is unavailable.
	 * Pass `true` (the default) to skip the availability gate where it does not
	 * apply, e.g. inserting an image that was already generated.
	 *
	 * @param bool   $is_supported        Whether the needed AI capability is available.
	 * @param string $unavailable_message Message shown when AI is not available.
	 */
	protected function guard_ai_request( $is_supported = true, $unavailable_message = '' ) {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_send_json_error( array( 'message' => __( 'You do not have permission to perform this action.', 'storesuite' ) ) );
		}

		if ( ! $is_supported ) {
			wp_send_json_error(
				array(
					'reason'  => 'unavailable',
					'message' => $unavailable_message,
				)
			);
		}
	}
}
