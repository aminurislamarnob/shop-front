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
	 * @param int    $orders_per_page Number of orders per page.
	 * @param int    $current_page Current page number.
	 * @param string $search_term Search term to filter orders.
	 * @param string $search_filter Filter type (all, order_id, customer_email, customers, products).
	 * @return object
	 */
	public function get_all_orders( $orders_per_page, $current_page, $search_term = '', $search_filter = 'all' ) {
		$args = array(
			'type'     => 'shop_order',
			'limit'    => $orders_per_page,
			'page'     => $current_page,
			'paginate' => true,
			'order'    => 'DESC',
			'orderby'  => 'date',
			'return'   => 'objects',
		);

		// Add search parameter if provided
		if ( ! empty( $search_term ) ) {
			switch ( $search_filter ) {
				case 'order_id':
					// Search by order ID
					$args['s'] = $search_term;
					break;

				case 'customer_email':
					// Search by customer email
					$args['billing_email'] = $search_term;
					break;

				case 'customers':
					// Search by customer name (billing first name, last name, or display name)
					global $wpdb;
					$args['meta_query'] = array(
						'relation' => 'OR',
						array(
							'key'     => '_billing_first_name',
							'value'   => $search_term,
							'compare' => 'LIKE',
						),
						array(
							'key'     => '_billing_last_name',
							'value'   => $search_term,
							'compare' => 'LIKE',
						),
					);
					break;

				case 'products':
					// Search by product name in order items
					$this->search_orders_by_product( $args, $search_term );
					break;

				case 'all':
				default:
					// Search in all fields (order number, billing name, email, etc.)
					$args['s'] = $search_term;
					break;
			}
		}

		$orders = wc_get_orders( $args );
		return $orders;
	}

	/**
	 * Search orders by product name.
	 *
	 * @param array  $args WC_Order query arguments.
	 * @param string $search_term Product name to search.
	 * @return void
	 */
	private function search_orders_by_product( &$args, $search_term ) {
		global $wpdb;

		// Search in WooCommerce order items table (proper way like WooCommerce core)
		$order_ids = $wpdb->get_col(
			$wpdb->prepare(
				"SELECT DISTINCT order_id FROM {$wpdb->prefix}woocommerce_order_items
				WHERE order_item_type = 'line_item'
				AND order_item_name LIKE %s",
				'%' . $wpdb->esc_like( $search_term ) . '%'
			)
		);

		if ( ! empty( $order_ids ) ) {
			$args['post__in'] = array_map( 'absint', $order_ids );
		} else {
			// Return empty result if no matching products found
			$args['post__in'] = array( 0 );
		}
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
