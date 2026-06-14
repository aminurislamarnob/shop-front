<?php

namespace PluginizeLab\StoreSuite\Coupon;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Coupon bulk edit service class.
 *
 * Mirrors {@see \PluginizeLab\StoreSuite\Product\ProductBulkEdit}: a bulk status
 * edit and a bulk move-to-trash action for the coupons list page. Coupon bulk
 * edit only changes the post status, matching WooCommerce's own coupon bulk edit.
 */
class CouponBulkEdit {
	/**
	 * The constructor.
	 */
	public function __construct() {
		add_action( 'wp_ajax_storesuite_bulk_edit_coupons', array( $this, 'handle_bulk_edit_coupons_ajax' ) );
		add_action( 'wp_ajax_storesuite_bulk_trash_coupons', array( $this, 'handle_bulk_trash_coupons_ajax' ) );
	}

	/**
	 * AJAX: bulk edit coupon status from the dashboard modal.
	 *
	 * @return void
	 */
	public function handle_bulk_edit_coupons_ajax() {
		check_ajax_referer( 'storesuite_bulk_edit_coupons', 'security' );

		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_send_json_error(
				array(
					'message' => __( 'You are not allowed to bulk edit coupons.', 'storesuite' ),
				),
				403
			);
		}

		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Array sanitized below.
		$coupon_ids = isset( $_POST['post'] ) ? (array) wp_unslash( $_POST['post'] ) : array();
		$coupon_ids = array_map( 'absint', $coupon_ids );
		$coupon_ids = array_values( array_unique( array_filter( $coupon_ids ) ) );

		if ( empty( $coupon_ids ) ) {
			wp_send_json_error(
				array(
					'message' => __( 'No coupons selected.', 'storesuite' ),
				),
				400
			);
		}

		$status           = isset( $_POST['_status'] ) ? sanitize_key( wp_unslash( $_POST['_status'] ) ) : '-1';
		$allowed_statuses = array( 'publish', 'pending', 'draft', 'private' );

		if ( '-1' === $status || ! in_array( $status, $allowed_statuses, true ) ) {
			wp_send_json_error(
				array(
					'message' => __( 'No changes were applied.', 'storesuite' ),
				),
				400
			);
		}

		if ( ! function_exists( 'wp_check_post_lock' ) ) {
			require_once ABSPATH . 'wp-admin/includes/post.php';
		}

		$updated = 0;
		$skipped = 0;
		$locked  = 0;

		foreach ( $coupon_ids as $coupon_id ) {
			if ( 'shop_coupon' !== get_post_type( $coupon_id ) ) {
				++$skipped;
				continue;
			}

			if ( ! current_user_can( 'edit_post', $coupon_id ) ) {
				++$skipped;
				continue;
			}

			if ( wp_check_post_lock( $coupon_id ) ) {
				++$locked;
				continue;
			}

			$result = wp_update_post(
				array(
					'ID'          => $coupon_id,
					'post_status' => $status,
				),
				true
			);

			if ( is_wp_error( $result ) || ! $result ) {
				++$skipped;
				continue;
			}

			do_action( 'storesuite_coupon_updated', $coupon_id, array( 'coupon_status' => $status ) );
			++$updated;
		}

		wp_send_json_success(
			array(
				'message' => $this->format_bulk_edit_result_message( $updated, $skipped, $locked ),
				'updated' => $updated,
				'skipped' => $skipped,
				'locked'  => $locked,
			)
		);
	}

	/**
	 * AJAX: bulk move coupons to trash from the list actions.
	 *
	 * @return void
	 */
	public function handle_bulk_trash_coupons_ajax() {
		check_ajax_referer( 'storesuite_bulk_trash_coupons', 'security' );

		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_send_json_error(
				array(
					'message' => __( 'You are not allowed to delete coupons.', 'storesuite' ),
				),
				403
			);
		}

		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Array sanitized below.
		$coupon_ids = isset( $_POST['coupon_ids'] ) ? (array) wp_unslash( $_POST['coupon_ids'] ) : array();
		$coupon_ids = array_map( 'absint', $coupon_ids );
		$coupon_ids = array_values( array_unique( array_filter( $coupon_ids ) ) );

		if ( empty( $coupon_ids ) ) {
			wp_send_json_error(
				array(
					'message' => __( 'No coupons selected.', 'storesuite' ),
				),
				400
			);
		}

		if ( ! function_exists( 'wp_check_post_lock' ) ) {
			require_once ABSPATH . 'wp-admin/includes/post.php';
		}

		// Use CouponManager so the storesuite_coupon_deleted hook fires consistently.
		$manager = new CouponManager();
		$trashed = 0;
		$locked  = 0;

		foreach ( $coupon_ids as $coupon_id ) {
			if ( 'shop_coupon' !== get_post_type( $coupon_id ) ) {
				continue;
			}

			if ( ! current_user_can( 'delete_post', $coupon_id ) ) {
				continue;
			}

			if ( wp_check_post_lock( $coupon_id ) ) {
				++$locked;
				continue;
			}

			$result = $manager->delete_coupon( $coupon_id, false ); // Soft delete (move to trash).
			if ( ! is_wp_error( $result ) ) {
				++$trashed;
			}
		}

		wp_send_json_success(
			array(
				'message' => $this->format_bulk_trash_result_message( $trashed, $locked ),
				'trashed' => $trashed,
				'locked'  => $locked,
			)
		);
	}

	/**
	 * Human-readable summary for bulk edit counts.
	 *
	 * @param int $updated Number of coupons updated.
	 * @param int $skipped Number of coupons skipped (permission or invalid data).
	 * @param int $locked  Number of coupons skipped because another user holds the edit lock.
	 * @return string
	 */
	private function format_bulk_edit_result_message( $updated, $skipped, $locked = 0 ) {
		$parts = array();

		if ( $updated > 0 ) {
			$parts[] = sprintf(
				/* translators: %d: number of coupons updated */
				_n( '%d coupon updated.', '%d coupons updated.', $updated, 'storesuite' ),
				$updated
			);
		}

		if ( $skipped > 0 ) {
			$parts[] = sprintf(
				/* translators: %d: number of coupons skipped */
				_n(
					'%d coupon was not updated (permission or invalid data).',
					'%d coupons were not updated (permission or invalid data).',
					$skipped,
					'storesuite'
				),
				$skipped
			);
		}

		if ( $locked > 0 ) {
			$parts[] = sprintf(
				/* translators: %d: number of coupons locked by another user */
				_n(
					'%d coupon not updated, currently being edited by another user.',
					'%d coupons not updated, currently being edited by another user.',
					$locked,
					'storesuite'
				),
				$locked
			);
		}

		if ( empty( $parts ) ) {
			return __( 'No changes were applied.', 'storesuite' );
		}

		return implode( "\n", $parts );
	}

	/**
	 * Human-readable summary for bulk trash counts.
	 *
	 * @param int $trashed Number of coupons moved to trash.
	 * @param int $locked  Number of coupons skipped because another user holds the edit lock.
	 * @return string
	 */
	private function format_bulk_trash_result_message( $trashed, $locked = 0 ) {
		$parts = array();

		if ( $trashed > 0 ) {
			$parts[] = sprintf(
				/* translators: %d: number of coupons moved to trash */
				_n( '%d coupon moved to trash.', '%d coupons moved to trash.', $trashed, 'storesuite' ),
				$trashed
			);
		}

		if ( $locked > 0 ) {
			$parts[] = sprintf(
				/* translators: %d: number of coupons not trashed because another user holds the edit lock */
				_n(
					'%d coupon was not moved to trash (another user is editing it).',
					'%d coupons were not moved to trash (another user is editing them).',
					$locked,
					'storesuite'
				),
				$locked
			);
		}

		if ( empty( $parts ) ) {
			return __( 'No changes were applied.', 'storesuite' );
		}

		return implode( "\n", $parts );
	}
}
