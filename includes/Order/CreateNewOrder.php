<?php

namespace PluginizeLab\StoreSuite\Order;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Plugin order controller class
 */
class CreateNewOrder {
	/**
	 * The order type.
	 *
	 * @var string
	 */
	private $order_type = 'shop_order';

	/**
	 * Current action.
	 *
	 * @var string
	 */
	private $current_action = '';

	/**
	 * Order object to be used in edit/new form.
	 *
	 * @var \WC_Order
	 */
	private $order;

	/**
	 * The constructor.
	 */
	public function __construct() {
		add_action( 'template_redirect', array( $this, 'maybe_create_empty_order' ) );
	}

	/**
	 * Check if we're on the new order page and create empty order if needed
	 */
	public function maybe_create_empty_order() {
		// Check if we're on your specific page
		if ( ! storesuite_is_page( 'add-new-order' ) ) {
			return;
		}

		// Create empty order
		$this->create_empty_order();
	}

	/**
	 * Verify that user has permission to edit orders.
	 *
	 * @return void
	 */
	private function verify_edit_permission() {
		if ( 'edit_order' === $this->current_action && ( ! isset( $this->order ) || ! $this->order ) ) {
			wp_die( esc_html__( 'You attempted to edit an order that does not exist. Perhaps it was deleted?', 'storesuite' ) );
		}

		if ( $this->order->get_type() !== $this->order_type ) {
			wp_die( esc_html__( 'Order type mismatch.', 'storesuite' ) );
		}

		if ( ! current_user_can( get_post_type_object( $this->order_type )->cap->edit_post, $this->order->get_id() ) && ! current_user_can( 'manage_woocommerce' ) ) {
			wp_die( esc_html__( 'You do not have permission to edit this order', 'storesuite' ) );
		}

		if ( 'trash' === $this->order->get_status() ) {
			wp_die( esc_html__( 'You cannot edit this item because it is in the Trash. Please restore it and try again.', 'storesuite' ) );
		}
	}

	/**
	 * Verify that user has permission to create order.
	 *
	 * @return void
	 */
	private function verify_create_permission() {
		if ( ! current_user_can( get_post_type_object( $this->order_type )->cap->publish_posts ) && ! current_user_can( 'manage_woocommerce' ) ) {
			wp_die( esc_html__( 'You don\'t have permission to create a new order', 'storesuite' ) );
		}

		if ( isset( $this->order ) ) {
			$this->verify_edit_permission();
		}
	}

	/**
	 * Handles initialization of the orders edit form with a new order.
	 *
	 * @return void
	 */
	private function create_empty_order() {
		global $theorder;

		$this->verify_create_permission();

		$order_class_name = wc_get_order_type( $this->order_type )['class_name'];
		if ( ! $order_class_name || ! class_exists( $order_class_name ) ) {
			wp_die();
		}

		$this->order = new $order_class_name();
		$this->order->set_object_read( false );
		$this->order->set_status( 'auto-draft' );
		$this->order->set_created_via( wp_get_current_user()->user_login );
		$this->order->set_currency( get_woocommerce_currency() );
		$this->order->set_prices_include_tax( 'yes' === get_option( 'woocommerce_prices_include_tax' ) );
		$this->order->set_customer_ip_address( \WC_Geolocation::get_ip_address() );
		$this->order->set_customer_user_agent( wc_get_user_agent() );
		$this->order->save();
		$this->handle_edit_lock();

		// Schedule auto-draft cleanup with our own prefixed hook.
		if ( ! wp_next_scheduled( 'storesuite_scheduled_auto_draft_delete' ) ) {
			wp_schedule_event( time(), 'daily', 'storesuite_scheduled_auto_draft_delete' );
		}

		$theorder = $this->order;
	}

	/**
	 * Claims the lock for the order being edited/created (unless it belongs to someone else).
	 * Also handles the 'claim-lock' action which allows taking over the order forcefully.
	 *
	 * @return void
	 */
	private function handle_edit_lock() {
		if ( ! $this->order ) {
			return;
		}

		$edit_lock = wc_get_container()->get( \Automattic\WooCommerce\Internal\Admin\Orders\EditLock::class );

		$locked = $edit_lock->is_locked_by_another_user( $this->order );

		// Take over order?
		$claim_lock = isset( $_GET['claim-lock'] ) ? sanitize_text_field( wp_unslash( $_GET['claim-lock'] ) ) : '';
		$wpnonce    = isset( $_GET['_wpnonce'] ) ? sanitize_key( wp_unslash( $_GET['_wpnonce'] ) ) : '';
		if ( ! empty( $claim_lock ) && wp_verify_nonce( $wpnonce, 'claim-lock-' . $this->order->get_id() ) ) {
			$edit_lock->lock( $this->order );
			wp_safe_redirect( $this->get_edit_url( $this->order->get_id() ) );
			exit;
		}

		if ( ! $locked ) {
			$edit_lock->lock( $this->order );
		}

		add_action(
			'admin_footer',
			function () use ( $edit_lock ) {
				$edit_lock->render_dialog( $this->order );
			}
		);
	}

	/**
	 * Helper method to generate edit link for an order.
	 *
	 * @param int $order_id Order ID.
	 *
	 * @return string Edit link.
	 */
	public function get_edit_url( int $order_id ): string {
		if ( ! wc_get_container()->get( \Automattic\WooCommerce\Internal\DataStores\Orders\CustomOrdersTableController::class )->custom_orders_table_usage_is_enabled() ) {
			return storesuite_get_navigation_url( 'edit-order' ) . $order_id;
		}

		$order = wc_get_order( $order_id );

		// Confirm we could obtain the order object (since it's possible it will not exist, due to a sync issue, or may
		// have been deleted in a separate concurrent request).
		if ( false === $order ) {
			wc_get_logger()->debug(
				sprintf(
					/* translators: %d order ID. */
					__( 'Attempted to determine the edit URL for order %d, however the order does not exist.', 'storesuite' ),
					$order_id
				)
			);
			$order_type = 'shop_order';
		} else {
			$order_type = $order->get_type();
		}

		try {
			$redirect_url = storesuite_get_navigation_url( 'edit-order' ) . $order_id;
		} catch ( \Exception $e ) {
			return '';
		}

		return $redirect_url;
	}
}
