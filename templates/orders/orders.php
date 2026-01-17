<?php
/**
 * MSFC order List Page
 *
 * @package ShopFront
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use PluginizeLab\ShopFront\Order\OrderManager;
$orders_obj = new OrderManager();

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
			<div class="msf-table-responsive">
				<table class="my-shop-front-tbl my-shop-front-product-list-table">
					<thead>
						<tr>
							<th><?php echo esc_html__( 'Order', 'shop-front' ); ?></th>
							<th><?php echo esc_html__( 'Status', 'shop-front' ); ?></th>
							<!-- <th width="20%"><?php // echo esc_html__( 'Billing & Shipping', 'shop-front' ); ?></th> -->
							<th><?php echo esc_html__( 'Order Total', 'shop-front' ); ?></th>
							<th><?php echo esc_html__( 'Total Items', 'shop-front' ); ?></th>
							<th><?php echo esc_html__( 'Customer', 'shop-front' ); ?></th>
							<th><?php echo esc_html__( 'Billing Phone', 'shop-front' ); ?></th>
							<th><?php echo esc_html__( 'Date', 'shop-front' ); ?></th>
							<th class="text-right"><?php echo esc_html__( 'Action', 'shop-front' ); ?></th>
						</tr>
						<tbody>
							<?php
							$current_page    = ( get_query_var( 'paged' ) ) ? absint( get_query_var( 'paged' ) ) : 1;
							$orders_per_page = apply_filters( 'msf_orders_per_page', 2 );
							$orders          = $orders_obj->get_all_orders( $orders_per_page, $current_page );

							if ( empty( $orders ) ) {
								echo '<tr id="order-row-not-found"><td colspan="8">';
								msf_get_template_part(
									'not-found',
									'',
									array(
										'title' => esc_html__( 'No order found!', 'shop-front' ),
										'desc'  => esc_html__( 'There is nothing to display at the moment.', 'shop-front' ),
									)
								);
								echo '</td></tr>';
							} else {
								foreach ( $orders->orders as $order ) { // phpcs:ignore
									$item_count = $order->get_item_count();
									?>
								<tr>
									<td data-title="<?php echo esc_attr__( 'Order', 'shop-front' ); ?>">
										<?php $orders_obj->get_order_number_column_value( $order ); ?>
									</td>
									<td data-title="<?php echo esc_attr__( 'Status', 'shop-front' ); ?>">
										<span class="msfc-badge msfc-badge-<?php echo esc_attr( msf_get_order_status_class( $order->get_status() ) ); ?>">
											<?php echo esc_html( wc_get_order_status_name( $order->get_status() ) ); ?>
										</span>
									</td>
									<!-- <td data-title="<?php echo esc_attr__( 'Billing & Shipping', 'shop-front' ); ?>">
										<div class="msf-billing-shipping-info">
											<span><?php // echo esc_html__( 'Billing: ', 'shop-front' ); ?></span>
											<?php // $orders_obj->get_billing_address_column_value( $order ); ?>
										</div>
										<div class="msf-billing-shipping-info">
											<span><?php // echo esc_html__( 'Shipping: ', 'shop-front' ); ?></span>
											<?php // $orders_obj->get_shipping_address_column_value( $order ); ?>
										</div>
									</td> -->
									<td data-title="<?php echo esc_attr__( 'Total', 'shop-front' ); ?>">
										<?php echo wp_kses_post( $order->get_formatted_order_total() ); ?>
									</td>
									<td data-title="<?php echo esc_html__( 'Total Items', 'shop-front' ); ?>">
										<?php echo esc_html( $item_count ); ?>
									</td>
									<td data-title="<?php echo esc_attr__( 'Customer', 'shop-front' ); ?>">
										<?php $orders_obj->get_order_customer_column_value( $order ); ?>
									</td>
									<td data-title="<?php echo esc_attr__( 'Billing Phone', 'shop-front' ); ?>">
										<?php
										if ( $order->get_billing_phone() ) {
											echo esc_html( $order->get_billing_phone() );
										} else {
											echo esc_html__( 'N/A', 'shop-front' );
										}
										?>
									</td>
									<td data-title="<?php echo esc_attr__( 'Date', 'shop-front' ); ?>">
										<time datetime="<?php echo esc_attr( $order->get_date_created()->date( 'c' ) ); ?>"><?php echo esc_html( wc_format_datetime( $order->get_date_created() ) ); ?></time>
									</td>
									<td class="text-right" data-title="<?php esc_attr_e( 'Actions', 'shop-front' ); ?>">
										<div class="msfc-dropdown">
											<span class="msfc-dropdown-icon">
												<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-three-dots" viewBox="0 0 16 16">
													<path d="M3 9.5a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3m5 0a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3m5 0a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3"/>
												</svg>
											</span>
											<ul class="msfc-dropdown-menu">
												<li>
													<a href="<?php echo esc_url( sprintf( msfc_get_navigation_url( 'order-details' ) . '%s', $order->get_id() ) ); ?>" class="dropdown-link"><?php esc_html_e( 'View', 'shop-front' ); ?></a>
												</li>
											</ul>
										</div>
									</td>
								</tr>
										<?php
								}
							}
							?>
						</tbody>
					</thead>
				</table>
				<?php
				if ( $orders->max_num_pages > 1 ) {
					$start_order = ( $current_page - 1 ) * $orders_per_page + 1;
					$end_order   = min( $orders->total, $current_page * $orders_per_page );
					$big_num     = 999999999;
					$page_links  = paginate_links(
						array(
							'base'      => str_replace( $big_num, '%#%', esc_url( get_pagenum_link( $big_num ) ) ),
							'format'    => '?page=%#%',
							'add_args'  => false,
							'current'   => $current_page,
							'total'     => $orders->max_num_pages,
							'type'      => 'array', // list.
							'prev_text' => '&larr;',
							'next_text' => '&rarr;',
							'end_size'  => 3,
							'mid_size'  => 3,
						)
					);

					echo '<div class="msfc-pagination-wrap">';

					echo '<div class="msfc-result-text">';
					/* translators: %1$s: Start Order, %2$s: End Order, %3$s: Total Orders */
					printf( esc_html__( 'Showing %1$s to %2$s of %3$s', 'shop-front' ), esc_html( $start_order ), esc_html( $end_order ), esc_html( $orders->total ) );
					echo '</div>';

					if ( ! empty( $page_links ) ) {
						echo '<ul class="msfc-pagination"><li>';
						echo wp_kses_post( join( '</li><li>', $page_links ) );
						echo '</li></ul>';
					}
					echo '</div>';
				}
				?>
			</div>
		</main>
	</div>
</div>
<?php do_action( 'msf_dashboard_wrapper_end' ); ?>