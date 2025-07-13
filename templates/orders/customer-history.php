<?php
/**
 * MSFC order details customer history template.
 *
 * @package ShopFront
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Variables used in this file.
 *
 * @var int   $orders_count   The number of paid orders placed by the current customer.
 * @var float $total_spend   The total money spent by the current customer.
 * @var float $avg_order_value The average money spent by the current customer.
 */
?>
<div class="msf-card">
	<div class="customer-history order-attribution-metabox">
		<h4>
			<?php
			esc_html_e( 'Total orders', 'shop-front' );
			echo wp_kses_post(
				wc_help_tip(
					__( 'Total number of non-cancelled, non-failed orders for this customer, including the current one.', 'shop-front' )
				)
			);
			?>
		</h4>

		<span class="order-attribution-total-orders">
			<?php echo esc_html( $orders_count ); ?>
		</span>

		<h4>
			<?php
			esc_html_e( 'Total revenue', 'shop-front' );
			echo wp_kses_post(
				wc_help_tip(
					__( "This is the Customer Lifetime Value, or the total amount you have earned from this customer's orders.", 'shop-front' )
				)
			);
			?>
		</h4>
		<span class="order-attribution-total-spend">
			<?php echo wp_kses_post( wc_price( $total_spend ) ); ?>
		</span>

		<h4><?php esc_html_e( 'Average order value', 'shop-front' ); ?></h4>
		<span class="order-attribution-average-order-value">
			<?php echo wp_kses_post( wc_price( $avg_order_value ) ); ?>
		</span>
	</div>
</div>
