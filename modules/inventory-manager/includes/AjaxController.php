<?php
/**
 * Inventory Manager — AJAX endpoints for stock edits.
 *
 * @package StoreSuite
 */

namespace PluginizeLab\StoreSuite\Modules\InventoryManager;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles inline single-item stock edits and bulk stock updates. Every handler
 * verifies the module nonce and the manage-inventory capability.
 */
class AjaxController {

	const NONCE = 'storesuite_inventory_manager';

	/**
	 * Hook handlers. Called from Module::boot().
	 *
	 * @return void
	 */
	public function register() {
		add_action( 'wp_ajax_storesuite_inventory_set_stock', array( $this, 'set_stock' ) );
		add_action( 'wp_ajax_storesuite_inventory_bulk_update', array( $this, 'bulk_update' ) );
	}

	/**
	 * Verify nonce + capability or die with a JSON error.
	 *
	 * @return void
	 */
	private function guard() {
		if ( ! isset( $_POST['security'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['security'] ) ), self::NONCE ) ) {
			wp_send_json_error( array( 'error' => __( 'Security check failed. Please reload and try again.', 'storesuite' ) ) );
		}
		if ( ! storesuite_current_user_can( 'manage_inventory' ) ) {
			wp_send_json_error( array( 'error' => __( 'You do not have permission to manage inventory.', 'storesuite' ) ) );
		}
	}

	/**
	 * POST — set a single item's quantity.
	 *
	 * @return void
	 */
	public function set_stock() {
		$this->guard();

		$id  = isset( $_POST['product_id'] ) ? absint( wp_unslash( $_POST['product_id'] ) ) : 0;
		$qty = isset( $_POST['qty'] ) ? wc_stock_amount( wp_unslash( $_POST['qty'] ) ) : 0; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- wc_stock_amount casts.

		$result = StockRepository::set_quantity( $id, $qty, 'manual' );
		if ( is_wp_error( $result ) ) {
			wp_send_json_error( array( 'error' => $result->get_error_message() ) );
		}

		wp_send_json_success(
			array(
				'message' => __( 'Stock updated.', 'storesuite' ),
				'item'    => StockRepository::describe( $id ),
			)
		);
	}

	/**
	 * POST — bulk update stock across selected items.
	 *
	 * @return void
	 */
	public function bulk_update() {
		$this->guard();

		$ids = isset( $_POST['ids'] ) ? array_map( 'absint', (array) wp_unslash( $_POST['ids'] ) ) : array();
		$op  = isset( $_POST['op'] ) ? sanitize_text_field( wp_unslash( $_POST['op'] ) ) : 'set';
		$qty = isset( $_POST['qty'] ) ? (int) wp_unslash( $_POST['qty'] ) : 0;

		if ( empty( $ids ) ) {
			wp_send_json_error( array( 'error' => __( 'No products selected.', 'storesuite' ) ) );
		}

		$updated = 0;
		foreach ( $ids as $id ) {
			$product = wc_get_product( $id );
			if ( ! $product ) {
				continue;
			}

			$current = (int) $product->get_stock_quantity();
			switch ( $op ) {
				case 'increase':
					$new = $current + $qty;
					break;
				case 'decrease':
					$new = max( 0, $current - $qty );
					break;
				case 'set':
				default:
					$new = $qty;
					break;
			}

			$result = StockRepository::set_quantity( $id, $new, 'bulk' );
			if ( ! is_wp_error( $result ) ) {
				$updated++;
			}
		}

		wp_send_json_success(
			array(
				/* translators: %d: number of products updated */
				'message' => sprintf( _n( '%d product updated.', '%d products updated.', $updated, 'storesuite' ), $updated ),
				'updated' => $updated,
			)
		);
	}
}
