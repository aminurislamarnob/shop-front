<?php

namespace PluginizeLab\ShopFront\Order;

use WC_Order;

use function Symfony\Component\VarDumper\Dumper\esc;

/**
 * Plugin order manager class
 */
class OrderManager {

	/**
	 * Get all orders.
	 *
	 * @return object
	 */
	public function get_all_orders( $orders_per_page, $current_page ) {
		$orders = wc_get_orders(
			array(
				'type'     => 'shop_order',
				'limit'    => $orders_per_page,
				'page'     => $current_page,
				'paginate' => true,
				'order'    => 'DESC',
				'orderby'  => 'date',
				'return'   => 'objects',
			)
		);
		return $orders;
	}

	/**
	 * Renders the order number.
	 *
	 * @param WC_Order $order The order object for the current row.
	 *
	 * @return void
	 */
	public function get_order_number_column_value( WC_Order $order ): void {
		echo '<a href="' . esc_url( sprintf( msfc_get_navigation_url( 'order-details' ) . '%s', $order->get_id() ) ) . '" class="order-view"><strong>#' . esc_attr( $order->get_order_number() ) . '</strong></a>';
	}

	/**
	 * Renders the order customer name.
	 *
	 * @param WC_Order $order The order object for the current row.
	 *
	 * @return void
	 */
	public function get_order_customer_column_value( WC_Order $order ): void {
		$buyer = '';

		if ( $order->get_billing_first_name() || $order->get_billing_last_name() ) {
			/* translators: 1: first name 2: last name */
			$buyer = trim( sprintf( _x( '%1$s %2$s', 'full name', 'shop-front' ), $order->get_billing_first_name(), $order->get_billing_last_name() ) );
		} elseif ( $order->get_billing_company() ) {
			$buyer = trim( $order->get_billing_company() );
		} elseif ( $order->get_customer_id() ) {
			$user  = get_user_by( 'id', $order->get_customer_id() );
			$buyer = ucwords( $user->display_name );
		}
		echo esc_html( $buyer );
	}

	/**
	 * Renders order billing information.
	 *
	 * @param WC_Order $order The order object for the current row.
	 *
	 * @return void
	 */
	public function get_billing_address_column_value( WC_Order $order ): void {
		$address = $order->get_formatted_billing_address();

		if ( $address ) {
			echo esc_html( preg_replace( '#<br\s*/?>#i', ', ', $address ) );

			if ( $order->get_payment_method() ) {
				/* translators: %s: payment method */
				echo '<span class="description">' . sprintf( esc_html__( 'via %s', 'shop-front' ), esc_html( $order->get_payment_method_title() ) ) . '</span>';
			}
		} else {
			echo '&ndash;';
		}
	}

	/**
	 * Renders order shipping information.
	 *
	 * @param WC_Order $order The order object for the current row.
	 *
	 * @return void
	 */
	public function get_shipping_address_column_value( WC_Order $order ): void {
		$address = $order->get_formatted_shipping_address();

		if ( $address ) {
			echo '<a target="_blank" href="' . esc_url( $order->get_shipping_address_map_url() ) . '">' . esc_html( preg_replace( '#<br\s*/?>#i', ', ', $address ) ) . '</a>';
			if ( $order->get_shipping_method() ) {
				/* translators: %s: shipping method */
				echo '<span class="description">' . sprintf( esc_html__( 'via %s', 'shop-front' ), esc_html( $order->get_shipping_method() ) ) . '</span>';
			}
		} else {
			echo '&ndash;';
		}
	}

	/**
	 * Get the available order actions for a given order.
	 *
	 * @param WC_Order|null $order The order object or null if no order is available.
	 *
	 * @return array
	 */
	public static function get_available_order_actions_for_order( $order ) {
		$actions = array(
			'send_order_details'              => __( 'Send order details to customer', 'woocommerce' ),
			'send_order_details_admin'        => __( 'Resend new order notification', 'woocommerce' ),
			'regenerate_download_permissions' => __( 'Regenerate download permissions', 'woocommerce' ),
		);

		/**
		 * Filter: woocommerce_order_actions
		 * Allows filtering of the available order actions for an order.
		 *
		 * @param array         $actions The available order actions for the order.
		 * @param WC_Order|null $order   The order object or null if no order is available.
		 */
		return apply_filters( 'woocommerce_order_actions', $actions, $order );
	}
}
