<?php

namespace PluginizeLab\ShopFront\Order;

use WC_Order;
use Automattic\WooCommerce\Admin\API\Reports\Customers\Query as CustomersQuery;
use Automattic\WooCommerce\Internal\Traits\OrderAttributionMeta;

/**
 * Plugin order controller class
 */
class OrderHooks {

	use OrderAttributionMeta;

	/**
	 * The constructor.
	 */
	public function __construct() {
		$this->set_fields_and_prefix(); // Set the fields and the field prefix for order attribution meta.
		add_action( 'msfc_after_order_details_action', array( $this, 'add_order_notes' ) );
		add_action( 'msfc_after_order_details_action', array( $this, 'add_customer_history' ) );
	}

	/**
	 * Add order note template
	 *
	 * @param WC_Order $order The order object.
	 */
	public function add_order_notes( WC_Order $order ) {
		$template_args = array(
			'order_id' => $order->get_id(),
		);
		msf_get_template_part( 'orders/order-notes', '', $template_args );
	}

	/**
	 * Add customer history template
	 *
	 * @param WC_Order $order The order object.
	 */
	public function add_customer_history( WC_Order $order ) {
		// No history when adding a new order.
		if ( 'auto-draft' === $order->get_status() ) {
			return;
		}

		$customer_history = null;

		if ( method_exists( $order, 'get_report_customer_id' ) ) {
			$customer_history = $this->get_customer_history( $order->get_report_customer_id() );
		}

		if ( ! $customer_history ) {
			$template_args = array(
				'orders_count'    => 0,
				'total_spend'     => 0,
				'avg_order_value' => 0,
			);
		} else {
			$template_args = array(
				'orders_count'    => $customer_history['orders_count'],
				'total_spend'     => $customer_history['total_spend'],
				'avg_order_value' => $customer_history['avg_order_value'],
			);
		}
		msf_get_template_part( 'orders/customer-history', '', $template_args );
	}

	/**
	 * Get the order history for the customer (data matches Customers report).
	 *
	 * @param int $customer_report_id The reports customer ID (not necessarily User ID).
	 *
	 * @return array|null Order count, total spend, and average spend per order.
	 */
	public function get_customer_history( $customer_report_id ): ?array {

		$args = array(
			'customers'    => array( $customer_report_id ),
			// If unset, these params have default values that affect the results.
			'order_after'  => null,
			'order_before' => null,
		);

		$customers_query = new CustomersQuery( $args );
		$customer_data   = $customers_query->get_data();
		return $customer_data->data[0] ?? null;
	}
}
