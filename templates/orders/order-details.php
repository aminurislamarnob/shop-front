<?php
/**
 * MSFC order details page
 *
 * @package StoreSuite
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

do_action( 'storesuite_dashboard_wrapper_start' );
?>
<div class="my-storesuite-container">
	<aside class="my-storesuite-sidebar">
		<?php do_action( 'storesuite_dashboard_navigation' ); ?>
	</aside>
	<div class="my-storesuite-wrapper">
		<?php do_action( 'storesuite_dashboard_content_before' ); ?>
		<main class="my-storesuite-page-content">
			<?php do_action( 'storesuite_dashboard_before_main_content' ); ?>
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
								<input type="text" name="search" id="search" placeholder="<?php esc_attr_e( 'Search Order', 'storesuite' ); ?>" />
							</div>
						</form>
					</div>
					<div class="col-md-6 text-right">
						<a href="<?php echo esc_url( storesuite_get_navigation_url( 'add-new-order' ) ); ?>" class="my-storesuite-button">
							<svg xmlns="http://www.w3.org/2000/svg" id="Outline" viewBox="0 0 24 24" width="24" height="24">
								<path d="M23,11H13V1a1,1,0,0,0-1-1h0a1,1,0,0,0-1,1V11H1a1,1,0,0,0-1,1H0a1,1,0,0,0,1,1H11V23a1,1,0,0,0,1,1h0a1,1,0,0,0,1-1V13H23a1,1,0,0,0,1-1h0A1,1,0,0,0,23,11Z"/>
							</svg>
							<?php esc_html_e( 'Add Order', 'storesuite' ); ?>
						</a>
						<a href="<?php echo esc_url( sprintf( storesuite_get_navigation_url( 'edit-order' ) . '%s', $order->get_id() ) ); ?>" class="my-storesuite-button">
							<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" class="bi bi-pencil" viewBox="0 0 16 16">
								<path d="M12.146.146a.5.5 0 0 1 .708 0l3 3a.5.5 0 0 1 0 .708l-10 10a.5.5 0 0 1-.168.11l-5 2a.5.5 0 0 1-.65-.65l2-5a.5.5 0 0 1 .11-.168zM11.207 2.5 13.5 4.793 14.793 3.5 12.5 1.207zm1.586 3L10.5 3.207 4 9.707V10h.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.5h.293zm-9.761 5.175-.106.106-1.528 3.821 3.821-1.528.106-.106A.5.5 0 0 1 5 12.5V12h-.5a.5.5 0 0 1-.5-.5V11h-.5a.5.5 0 0 1-.468-.325"/>
							</svg>
							<?php esc_html_e( 'Edit Order', 'storesuite' ); ?>
						</a>
					</div>
				</div>
			</div>
			<div class="msfc-dashboard-order-details">
				<div class="row">
					<div class="col-md-9">
						<div class="msf-card">
							<?php do_action( 'storesuite_before_order_items_table', $order ); ?>
	
							<div id="woocommerce-order-items">
								<table class="msf-table order-items">
									<thead>
										<tr>
											<th class="item" colspan="2"><?php esc_html_e( 'Item', 'storesuite' ); ?></th>
	
											<?php do_action( 'woocommerce_admin_order_item_headers', $order ); ?>
	
											<th class="quantity"><?php esc_html_e( 'Qty', 'storesuite' ); ?></th>
	
											<th class="line_cost"><?php esc_html_e( 'Totals', 'storesuite' ); ?></th>
										</tr>
									</thead>
									<tbody id="order_items_list">
									<?php
									$order_items = $order->get_items( apply_filters( 'woocommerce_admin_order_item_types', array( 'line_item' ) ) );

									foreach ( $order_items as $item_id => $item ) {
										do_action( 'woocommerce_before_order_item_' . $item['type'] . '_html', $item_id, $item, $order );

										$_product = $item->get_product();
										storesuite_get_template_part(
											'orders/order-item-html',
											'',
											array(
												'order'    => $order,
												'item_id'  => $item_id,
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

									$coupons = $order->get_items( 'coupon' );

									if ( $coupons ) {
										?>
										<tr>
											<th colspan="2"><?php esc_html_e( 'Coupons', 'storesuite' ); ?></th>
											<td colspan="2" class="value">
												<ul class="list-inline">
													<?php
													foreach ( $coupons as $item_id => $item ) {
														echo '<li><span>' . esc_html( $item['name'] ) . '</span></li>';
													}
													?>
												</ul>
											</td>
										</tr>
										<?php
									}
									?>
									</tfoot>
								</table>
							</div>
							<?php do_action( 'storesuite_after_order_items_table', $order ); ?>
						</div>
						<?php
						/**
						 * Action hook fired after the order details.
						 *
						 * @param WC_Order $order Order data.
						 */
						do_action( 'woocommerce_after_order_details', $order );

						storesuite_get_template_part( 'orders/order-details-customer', '', array( 'order' => $order ) );
						?>
					</div>
					<div class="col-md-3">
						<?php
							/**
							 * Action hook fired after the order details action.
							 *
							 * @param WC_Order $order Order data.
							 */
							do_action( 'storesuite_after_order_details_action', $order );
						?>
					</div>
				</div>
			</div>
		</main>
	</div>
</div>
<?php do_action( 'storesuite_dashboard_wrapper_end' ); ?>