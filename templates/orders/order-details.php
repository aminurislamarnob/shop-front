<?php
/**
 * MSFC order details page
 *
 * @package ShopFront
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$order_id = get_query_var( 'order-details' );
$order_id = absint( $order_id );
$order    = wc_get_order( $order_id ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited

if ( ! $order ) {
	return;
}

$order_items        = $order->get_items( apply_filters( 'woocommerce_purchase_order_item_types', 'line_item' ) );
$show_purchase_note = $order->has_status( apply_filters( 'woocommerce_purchase_note_order_statuses', array( 'completed', 'processing' ) ) );
$downloads          = $order->get_downloadable_items();
$actions            = array_filter(
	wc_get_account_orders_actions( $order ),
	function ( $key ) {
		return 'view' !== $key;
	},
	ARRAY_FILTER_USE_KEY
);

// if ( $show_downloads ) {
// wc_get_template(
// 'order/order-downloads.php',
// array(
// 'downloads'  => $downloads,
// 'show_title' => true,
// )
// );
// }

do_action( 'msf_dashboard_wrapper_start' );
?>
<div class="my-shop-front-container">
	<aside class="my-shop-front-sidebar">
		<?php do_action( 'msf_dashboard_navigation' ); ?>
	</aside>
	<div class="my-shop-front-wrapper">
		<?php do_action( 'msf_dashboard_content_before' ); ?>
		<main class="my-shop-front-page-content">
			<?php do_action( 'msf_dashboard_before_main_content' ); ?>
			<div class="row">
				<div class="col-md-9">
					<div class="msf-card">
						<?php do_action( 'woocommerce_order_details_before_order_table', $order ); ?>

						<table class="my-shop-front-tbl my-shop-front-product-list-table">

							<thead>
								<tr>
									<th><?php esc_html_e( 'Product', 'shop-front' ); ?></th>
									<th><?php esc_html_e( 'Total', 'shop-front' ); ?></th>
								</tr>
							</thead>

							<tbody>
								<?php
								do_action( 'woocommerce_order_details_before_order_table_items', $order );

								foreach ( $order_items as $item_id => $item ) {
									$product = $item->get_product();

									wc_get_template(
										'order/order-details-item.php',
										array(
											'order'   => $order,
											'item_id' => $item_id,
											'item'    => $item,
											'show_purchase_note' => $show_purchase_note,
											'purchase_note' => $product ? $product->get_purchase_note() : '',
											'product' => $product,
										)
									);
								}

								do_action( 'woocommerce_order_details_after_order_table_items', $order );
								?>
							</tbody>

							<?php
							if ( ! empty( $actions ) ) :
								?>
							<tfoot>
								<tr>
									<th class="order-actions--heading"><?php esc_html_e( 'Actions', 'shop-front' ); ?>:</th>
									<td>
										<?php
										$wp_button_class = wc_wp_theme_get_element_class_name( 'button' ) ? ' ' . wc_wp_theme_get_element_class_name( 'button' ) : '';
										foreach ( $actions as $key => $action ) { // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
											if ( empty( $action['aria-label'] ) ) {
												// Generate the aria-label based on the action name.
												/* translators: %1$s Action name, %2$s Order number. */
												$action_aria_label = sprintf( __( '%1$s order number %2$s', 'shop-front' ), $action['name'], $order->get_order_number() );
											} else {
												$action_aria_label = $action['aria-label'];
											}
												echo '<a href="' . esc_url( $action['url'] ) . '" class="woocommerce-button' . esc_attr( $wp_button_class ) . ' button ' . sanitize_html_class( $key ) . ' order-actions-button " aria-label="' . esc_attr( $action_aria_label ) . '">' . esc_html( $action['name'] ) . '</a>';
												unset( $action_aria_label );
										}
										?>
									</td>
								</tr>
							</tfoot>
							<?php endif ?>
							<tfoot>
								<?php
								foreach ( $order->get_order_item_totals() as $key => $total ) {
									?>
										<tr>
											<th scope="row"><?php echo esc_html( $total['label'] ); ?></th>
											<td><?php echo wp_kses_post( $total['value'] ); ?></td>
										</tr>
										<?php
								}
								?>
								<?php if ( $order->get_customer_note() ) : ?>
									<tr>
										<th><?php esc_html_e( 'Note:', 'shop-front' ); ?></th>
										<td><?php echo wp_kses( nl2br( wptexturize( $order->get_customer_note() ) ), array( 'br' => array() ) ); ?></td>
									</tr>
								<?php endif; ?>
							</tfoot>
						</table>

						<?php do_action( 'woocommerce_order_details_after_order_table', $order ); ?>
					</div>
					<div class="msf-card">
						<?php
						/**
						 * Action hook fired after the order details.
						 *
						 * @param WC_Order $order Order data.
						 */
						do_action( 'woocommerce_after_order_details', $order );

						wc_get_template( 'order/order-details-customer.php', array( 'order' => $order ) );
						?>
					</div>
				</div>
				<div class="col-md-3">
					<div class="msf-card">
						
					</div>
					<?php
						/**
						 * Action hook fired after the order details action.
						 *
						 * @param WC_Order $order Order data.
						 */
						do_action( 'msfc_after_order_details_action', $order );
					?>
				</div>
			</div>
		</main>
	</div>
</div>
<?php do_action( 'msf_dashboard_wrapper_end' ); ?>