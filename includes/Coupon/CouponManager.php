<?php

namespace PluginizeLab\ShopFront\Coupon;

use WP_Error;
use WC_Coupon;

/**
 * Plugin coupon manager class
 */
class CouponManager {
	/**
	 * Create a new coupon
	 *
	 * @param array $data Coupon data.
	 *
	 * @return int|WP_Error Coupon ID on success, WP_Error on failure.
	 */
	public function create_coupon( $data ) {
		try {
			// Create new coupon object.
			$coupon = new WC_Coupon();

			// Set coupon properties.
			$errors = $coupon->set_props(
				array(
					'code'                        => isset( $data['coupon_code'] ) ? wc_format_coupon_code( sanitize_text_field( wp_unslash( $data['coupon_code'] ) ) ) : '',
					'discount_type'               => isset( $data['discount_type'] ) ? sanitize_text_field( wp_unslash( $data['discount_type'] ) ) : 'fixed_cart',
					'amount'                      => isset( $data['coupon_amount'] ) ? wc_format_decimal( wp_unslash( $data['coupon_amount'] ) ) : 0,
					'description'                 => isset( $data['description'] ) ? sanitize_textarea_field( wp_unslash( $data['description'] ) ) : '',
					'date_expires'                => isset( $data['expiry_date'] ) ? sanitize_text_field( wp_unslash( $data['expiry_date'] ) ) : null,
					'individual_use'              => isset( $data['individual_use'] ),
					'product_ids'                 => isset( $data['product_ids'] ) ? array_filter( array_map( 'intval', (array) $data['product_ids'] ) ) : array(),
					'excluded_product_ids'        => isset( $data['exclude_product_ids'] ) ? array_filter( array_map( 'intval', (array) $data['exclude_product_ids'] ) ) : array(),
					'usage_limit'                 => isset( $data['usage_limit'] ) ? absint( $data['usage_limit'] ) : 0,
					'usage_limit_per_user'        => isset( $data['usage_limit_per_user'] ) ? absint( $data['usage_limit_per_user'] ) : 0,
					'limit_usage_to_x_items'      => isset( $data['limit_usage_to_x_items'] ) ? absint( $data['limit_usage_to_x_items'] ) : null,
					'free_shipping'               => isset( $data['free_shipping'] ),
					'product_categories'          => isset( $data['product_categories'] ) ? array_filter( array_map( 'intval', (array) $data['product_categories'] ) ) : array(),
					'excluded_product_categories' => isset( $data['exclude_product_categories'] ) ? array_filter( array_map( 'intval', (array) $data['exclude_product_categories'] ) ) : array(),
					'exclude_sale_items'          => isset( $data['exclude_sale_items'] ),
					'minimum_amount'              => isset( $data['minimum_amount'] ) ? wc_format_decimal( wp_unslash( $data['minimum_amount'] ) ) : '',
					'maximum_amount'              => isset( $data['maximum_amount'] ) ? wc_format_decimal( wp_unslash( $data['maximum_amount'] ) ) : '',
					'email_restrictions'          => isset( $data['customer_email'] ) ? array_filter( array_map( 'trim', explode( ',', sanitize_text_field( wp_unslash( $data['customer_email'] ) ) ) ) ) : array(),
				)
			);

			if ( is_wp_error( $errors ) ) {
				return $errors;
			}

			// Save coupon.
			$coupon_id = $coupon->save();

			do_action( 'msf_new_coupon_created', $coupon_id, $data );

			return $coupon_id;

		} catch ( \Exception $e ) {
			return new WP_Error( 'coupon_error', $e->getMessage() );
		}
	}

	/**
	 * Update an existing coupon
	 *
	 * @param int   $coupon_id Coupon ID.
	 * @param array $data Coupon data.
	 *
	 * @return bool|WP_Error True on success, WP_Error on failure.
	 */
	public function update_coupon( $coupon_id, $data ) {
		try {
			$coupon = new WC_Coupon( $coupon_id );

			if ( ! $coupon->get_id() ) {
				return new WP_Error( 'invalid_coupon', __( 'Coupon not found', 'shop-front' ) );
			}

			// Set coupon properties.
			$errors = $coupon->set_props(
				array(
					'code'                        => isset( $data['coupon_code'] ) ? wc_format_coupon_code( sanitize_text_field( wp_unslash( $data['coupon_code'] ) ) ) : $coupon->get_code(),
					'discount_type'               => isset( $data['discount_type'] ) ? sanitize_text_field( wp_unslash( $data['discount_type'] ) ) : $coupon->get_discount_type(),
					'amount'                      => isset( $data['coupon_amount'] ) ? wc_format_decimal( wp_unslash( $data['coupon_amount'] ) ) : $coupon->get_amount(),
					'description'                 => isset( $data['description'] ) ? sanitize_textarea_field( wp_unslash( $data['description'] ) ) : $coupon->get_description(),
					'date_expires'                => isset( $data['expiry_date'] ) ? sanitize_text_field( wp_unslash( $data['expiry_date'] ) ) : $coupon->get_date_expires(),
					'individual_use'              => isset( $data['individual_use'] ),
					'product_ids'                 => isset( $data['product_ids'] ) ? array_filter( array_map( 'intval', (array) $data['product_ids'] ) ) : $coupon->get_product_ids(),
					'excluded_product_ids'        => isset( $data['exclude_product_ids'] ) ? array_filter( array_map( 'intval', (array) $data['exclude_product_ids'] ) ) : $coupon->get_excluded_product_ids(),
					'usage_limit'                 => isset( $data['usage_limit'] ) ? absint( $data['usage_limit'] ) : $coupon->get_usage_limit(),
					'usage_limit_per_user'        => isset( $data['usage_limit_per_user'] ) ? absint( $data['usage_limit_per_user'] ) : $coupon->get_usage_limit_per_user(),
					'limit_usage_to_x_items'      => isset( $data['limit_usage_to_x_items'] ) ? absint( $data['limit_usage_to_x_items'] ) : $coupon->get_limit_usage_to_x_items(),
					'free_shipping'               => isset( $data['free_shipping'] ),
					'product_categories'          => isset( $data['product_categories'] ) ? array_filter( array_map( 'intval', (array) $data['product_categories'] ) ) : $coupon->get_product_categories(),
					'excluded_product_categories' => isset( $data['exclude_product_categories'] ) ? array_filter( array_map( 'intval', (array) $data['exclude_product_categories'] ) ) : $coupon->get_excluded_product_categories(),
					'exclude_sale_items'          => isset( $data['exclude_sale_items'] ),
					'minimum_amount'              => isset( $data['minimum_amount'] ) ? wc_format_decimal( wp_unslash( $data['minimum_amount'] ) ) : $coupon->get_minimum_amount(),
					'maximum_amount'              => isset( $data['maximum_amount'] ) ? wc_format_decimal( wp_unslash( $data['maximum_amount'] ) ) : $coupon->get_maximum_amount(),
					'email_restrictions'          => isset( $data['customer_email'] ) ? array_filter( array_map( 'trim', explode( ',', sanitize_text_field( wp_unslash( $data['customer_email'] ) ) ) ) ) : $coupon->get_email_restrictions(),
				)
			);

			if ( is_wp_error( $errors ) ) {
				return $errors;
			}

			$coupon->save();

			do_action( 'msf_coupon_updated', $coupon_id, $data );

			return true;

		} catch ( \Exception $e ) {
			return new WP_Error( 'coupon_error', $e->getMessage() );
		}
	}

	/**
	 * Delete a coupon
	 *
	 * @param int  $coupon_id Coupon ID.
	 * @param bool $force_delete Whether to permanently delete or move to trash.
	 *
	 * @return bool|WP_Error True on success, WP_Error on failure.
	 */
	public function delete_coupon( $coupon_id, $force_delete = false ) {
		try {
			$coupon = new WC_Coupon( $coupon_id );

			if ( ! $coupon->get_id() ) {
				return new WP_Error( 'invalid_coupon', __( 'Coupon not found', 'shop-front' ) );
			}

			$coupon->delete( $force_delete );

			do_action( 'msf_coupon_deleted', $coupon_id, $force_delete );

			return true;

		} catch ( \Exception $e ) {
			return new WP_Error( 'coupon_error', $e->getMessage() );
		}
	}
}
