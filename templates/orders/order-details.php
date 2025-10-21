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
			<div class="msf-table-header-part">
				<div class="row">
					<div class="col-md-6">
						<form action="">
							<div class="msf-table-search-input">
								<div class="msf-table-search-icon">
									<svg xmlns="http://www.w3.org/2000/svg" id="Outline" viewBox="0 0 24 24" width="24" height="24">
										<path d="M23.707,22.293l-5.969-5.969a10.016,10.016,0,1,0-1.414,1.414l5.969,5.969a1,1,0,0,0,1.414-1.414ZM10,18a8,8,0,1,1,8-8A8.009,8.009,0,0,1,10,18Z"/>
									</svg>
								</div>
								<input type="text" name="search" id="search" placeholder="<?php esc_attr_e( 'Search Order', 'shop-front' ); ?>" />
							</div>
						</form>
					</div>
					<div class="col-md-6 text-right">
						<a href="<?php echo esc_url( msfc_get_navigation_url( 'add-new-order' ) ); ?>" class="my-shop-front-button">
							<svg xmlns="http://www.w3.org/2000/svg" id="Outline" viewBox="0 0 24 24" width="24" height="24">
								<path d="M23,11H13V1a1,1,0,0,0-1-1h0a1,1,0,0,0-1,1V11H1a1,1,0,0,0-1,1H0a1,1,0,0,0,1,1H11V23a1,1,0,0,0,1,1h0a1,1,0,0,0,1-1V13H23a1,1,0,0,0,1-1h0A1,1,0,0,0,23,11Z"/>
							</svg>
							<?php esc_html_e( 'Add Order', 'shop-front' ); ?>
						</a>
					</div>
				</div>
			</div>
			<div class="row">
				<div class="col-md-9">
					<div class="msf-card">
						<?php do_action( 'msf_before_order_items_table', $order ); ?>

						<div id="woocommerce-order-items">
							<table class="msf-table order-items">
								<thead>
									<tr>
										<th class="item" colspan="2"><?php esc_html_e( 'Item', 'shop-front' ); ?></th>

										<?php do_action( 'woocommerce_admin_order_item_headers', $order ); ?>

										<th class="quantity"><?php esc_html_e( 'Qty', 'shop-front' ); ?></th>

										<th class="line_cost"><?php esc_html_e( 'Totals', 'shop-front' ); ?></th>
									</tr>
								</thead>
								<tbody id="order_items_list">
								<?php
								$order_items = $order->get_items( apply_filters( 'woocommerce_admin_order_item_types', array( 'line_item' ) ) );

								foreach ( $order_items as $item_id => $item ) {
									do_action( 'woocommerce_before_order_item_' . $item['type'] . '_html', $item_id, $item, $order );

									$_product = $item->get_product();
									msf_get_template_part(
										'orders/order-item-html', '', array(
											'order' => $order,
											'item_id' => $item_id,
											'_product' => $_product,
											'item'     => $item,
										)
									);

									do_action( 'woocommerce_order_item_' . $item['type'] . '_html', $item_id, $item, $order );
								}
								?>
								</tbody>

								<tfoot>
								<?php
								if ( $totals = $order->get_order_item_totals() ) { // phpcs:ignore
									foreach ( $totals as $total ) {
										?>
										<tr>
											<th colspan="2"><?php echo wp_kses_data( $total['label'] ); ?></th>
											<td colspan="2" class="value"><?php echo wp_kses_post( $total['value'] ); ?></td>
										</tr>
										<?php
									}
								}
								?>
								</tfoot>

							</table>

							<?php
							$coupons = $order->get_items( 'coupon' );

							if ( $coupons ) {
								?>
								<table class="msf-table order-items">
									<tr>
										<th><?php esc_html_e( 'Coupons', 'shop-front' ); ?></th>
										<td>
											<ul class="list-inline">
												<?php
												foreach ( $coupons as $item_id => $item ) {
													$post_id = $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM {$wpdb->posts} WHERE post_title = %s AND post_type = 'shop_coupon' AND post_status = 'publish' LIMIT 1;", $item['name'] ) ); // phpcs:ignore

													echo '<li><span>' . esc_html( $item['name'] ) . '</span></li>';
												}
												?>
											</ul>
										</td>
									</tr>
								</table>
								<?php
							}
							?>
						</div>

						<?php do_action( 'msf_after_order_items_table', $order ); ?>

						<div class="clear"></div>

						<!-- <div class="" style="width: 100%">
							<div class="msf-panel msf-panel-default">
								<div class="msf-panel-heading"><strong><?php //esc_html_e( 'Downloadable Product Permission', 'shop-front' ); ?></strong></div>
								<div class="msf-panel-body">
									<?php
									//msf_get_template_part( 'orders/downloadable', '', array( 'order' => $order ) );
									?>
								</div>
							</div>
						</div> -->
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