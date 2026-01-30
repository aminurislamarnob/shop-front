<?php
/**
 * MSFC order details customer history template.
 *
 * @package StoreSuite
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
	<div class="card-title-with-link">
		<h3 class="msf-card-title"><?php esc_html_e( 'Customer History', 'storesuite' ); ?></h3>
	</div>
	<div class="msf-card-content">
		<div class="customer-history order-attribution-metabox">
			<div class="customer-history-item">
				<h4><?php esc_html_e( 'Total orders', 'storesuite' ); ?></h4>
				<span class="order-attribution-total-orders">
					<?php echo esc_html( $orders_count ); ?>
				</span>
			</div>
			<div class="customer-history-item">
				<h4><?php esc_html_e( 'Total revenue', 'storesuite' ); ?></h4>
				<span class="order-attribution-total-spend">
					<?php echo wp_kses_post( wc_price( $total_spend ) ); ?>
				</span>
			</div>
			<div class="customer-history-item">
				<h4><?php esc_html_e( 'Average order value', 'storesuite' ); ?></h4>
				<span class="order-attribution-average-order-value">
					<?php echo wp_kses_post( wc_price( $avg_order_value ) ); ?>
				</span>
			</div>
		</div>
	</div>
</div>
