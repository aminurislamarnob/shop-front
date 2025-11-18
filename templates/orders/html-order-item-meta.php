<?php
/**
 * Shows an order item meta
 *
 * @package WooCommerce\Admin
 * @var object $item The item being displayed
 * @var int $item_id The id of the item being displayed
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$hidden_order_itemmeta = apply_filters(
	'woocommerce_hidden_order_itemmeta',
	array(
		'_qty',
		'_tax_class',
		'_product_id',
		'_variation_id',
		'_line_subtotal',
		'_line_subtotal_tax',
		'_line_total',
		'_line_tax',
		'method_id',
		'cost',
		'_reduced_stock',
		'_restock_refunded_items',
	)
);
?><div class="view">
	<?php
	$meta_data = $item->get_all_formatted_meta_data( '' );
	if ( $meta_data ) :
		?>
		<div class="order-item-meta-wrapper">
			<?php
			foreach ( $meta_data as $meta_id => $meta ) :
				if ( in_array( $meta->key, $hidden_order_itemmeta, true ) ) {
					continue;
				}
				?>
				<div class="order-item-meta">
					<strong><?php echo wp_kses_post( $meta->display_key ); ?>:</strong><?php echo wp_kses_post( force_balance_tags( $meta->display_value ) ); ?>
				</div>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>
</div>
