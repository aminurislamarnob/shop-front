<?php
/**
 * MSFC order List Page
 *
 * @package StoreSuite
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use PluginizeLab\StoreSuite\Order\OrderManager;
$orders_obj = new OrderManager();

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
					<div class="col-md-auto">
						<div class="msf-form-group d-flex align-items-center msf-bulk-order-actions">
							<select name="action" id="bulk-action-selector-top" class="msf-form-control" form="msf-order-bulk-actions">
								<option value="-1"><?php esc_html_e( 'Bulk actions', 'storesuite' ); ?></option>
								<option value="mark_processing"><?php esc_html_e( 'Change status to processing', 'storesuite' ); ?></option>
								<option value="mark_on-hold"><?php esc_html_e( 'Change status to on-hold', 'storesuite' ); ?></option>
								<option value="mark_completed"><?php esc_html_e( 'Change status to completed', 'storesuite' ); ?></option>
								<option value="mark_cancelled"><?php esc_html_e( 'Change status to cancelled', 'storesuite' ); ?></option>
								<option value="trash"><?php esc_html_e( 'Move to Trash', 'storesuite' ); ?></option>
							</select>
							<button type="submit" id="doaction" class="my-storesuite-button" form="msf-order-bulk-actions"><?php esc_html_e( 'Apply', 'storesuite' ); ?></button>
						</div>
					</div>
					<div class="col-md-auto">
						<form action="" method="get" class="msf-search-form msf-order-search-form">
							<div class="msf-table-search-input msf-form-group">
								<div class="msf-table-search-icon">
									<svg xmlns="http://www.w3.org/2000/svg" id="Outline" viewBox="0 0 24 24" width="24" height="24">
										<path d="M23.707,22.293l-5.969-5.969a10.016,10.016,0,1,0-1.414,1.414l5.969,5.969a1,1,0,0,0,1.414-1.414ZM10,18a8,8,0,1,1,8-8A8.009,8.009,0,0,1,10,18Z"/>
									</svg>
								</div>
								<input type="text" name="search_by" id="search_by" placeholder="<?php esc_attr_e( 'Search Order', 'storesuite' ); ?>" value="<?php echo esc_attr( isset( $_GET['search_by'] ) ? sanitize_text_field( wp_unslash( $_GET['search_by'] ) ) : '' ); ?>" />
							</div>
							<div class="msf-form-group">
								<?php
								$options = array(
									'order_id'       => __( 'Order ID', 'woocommerce' ),
									'customer_email' => __( 'Customer Email', 'woocommerce' ),
									'customers'      => __( 'Customers', 'woocommerce' ),
									'products'       => __( 'Products', 'woocommerce' ),
									'all'            => __( 'All', 'woocommerce' ),
								);

								/**
								 * Filters the search filters available in the admin order search. Can be used to add new or remove existing filters.
								 * When adding new filters, `woocommerce_hpos_generate_where_for_search_filter` should also be used to generate the WHERE clause for the new filter
								 *
								 * @param $options array List of available filters.
								 */
								$options       = apply_filters( 'woocommerce_hpos_admin_search_filters', $options );
								$saved_setting = get_user_setting( 'wc-search-filter-hpos-admin', 'all' );
								$selected      = sanitize_text_field( wp_unslash( $_REQUEST['search-filter'] ?? $saved_setting ) );
								if ( $saved_setting !== $selected ) {
									set_user_setting( 'wc-search-filter-hpos-admin', $selected );
								}
								?>
								<select name="search-filter" id="order-search-filter" class="msf-form-control">
									<?php foreach ( $options as $value => $label ) { ?>
										<option value="<?php echo esc_attr( wp_unslash( sanitize_text_field( $value ) ) ); ?>" <?php selected( $value, sanitize_text_field( wp_unslash( $selected ) ) ); ?>><?php echo esc_html( $label ); ?></option>
									<?php } ?>
								</select>
							</div>
							<div class="msf-form-group">
								<button type="submit" class="my-storesuite-button">
									<div class="msf-table-search-icon">
										<svg xmlns="http://www.w3.org/2000/svg" id="Outline" viewBox="0 0 24 24" width="24" height="24">
											<path d="M23.707,22.293l-5.969-5.969a10.016,10.016,0,1,0-1.414,1.414l5.969,5.969a1,1,0,0,0,1.414-1.414ZM10,18a8,8,0,1,1,8-8A8.009,8.009,0,0,1,10,18Z"/>
										</svg>
									</div>
									<?php esc_html_e( 'Search', 'storesuite' ); ?>
								</button>
							</div>
						</form>
					</div>
					<div class="col-md-4 text-right">
						<div class="row justify-content-end">
							<div class="col-md-auto">
								<a href="<?php echo esc_url( storesuite_get_navigation_url( 'add-new-order' ) ); ?>" class="my-storesuite-button">
									<svg xmlns="http://www.w3.org/2000/svg" id="Outline" viewBox="0 0 24 24" width="24" height="24">
										<path d="M23,11H13V1a1,1,0,0,0-1-1h0a1,1,0,0,0-1,1V11H1a1,1,0,0,0-1,1H0a1,1,0,0,0,1,1H11V23a1,1,0,0,0,1,1h0a1,1,0,0,0,1-1V13H23a1,1,0,0,0,1-1h0A1,1,0,0,0,23,11Z"/>
									</svg>
									<?php esc_html_e( 'Add Order', 'storesuite' ); ?>
								</a>
							</div>
							<div class="col-md-auto">
								<button type="button" class="my-storesuite-button msf-filter-toggle" id="msf-order-filter-toggle">
									<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-funnel" viewBox="0 0 16 16">
										<path d="M1.5 1.5A.5.5 0 0 1 2 1h12a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-.128.334L10 8.692V13.5a.5.5 0 0 1-.342.474l-3 1A.5.5 0 0 1 6 14.5V8.692L1.628 3.834A.5.5 0 0 1 1.5 3.5zm1 .5v1.308l4.372 4.858A.5.5 0 0 1 7 8.5v5.306l2-.666V8.5a.5.5 0 0 1 .128-.334L13.5 3.308V2z"/>
									</svg>
									<?php esc_html_e( 'Filter', 'storesuite' ); ?>
								</button>
							</div>
						</div>
					</div>
				</div>
			</div>
			<!-- Off-canvas Order Filter -->
			<?php storesuite_get_template_part( 'orders/order-filters-offcanvas' ); ?>
			<form id="msf-order-bulk-actions" method="post">
				<?php wp_nonce_field( 'storesuite_order_bulk_action', 'storesuite_bulk_action_nonce' ); ?>
				<div class="msf-table-responsive">
				<table class="my-storesuite-tbl my-storesuite-product-list-table">
					<thead>
						<tr>
							<th class="check-column">
								<label class="my-storesuite-checkbox">
									<input type="checkbox" id="cb-select-all-orders" class="my-storesuite-checkbox-input">
									<span class="my-storesuite-checkbox-back"></span>
									<span class="my-storesuite-tick">
										<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-check" viewBox="0 0 16 16">
											<path d="M10.97 4.97a.75.75 0 0 1 1.07 1.05l-3.99 4.99a.75.75 0 0 1-1.08.02L4.324 8.384a.75.75 0 1 1 1.06-1.06l2.094 2.093 3.473-4.425z"></path>
										</svg>
									</span>
								</label>
							</th>
							<th><?php echo esc_html__( 'Order', 'storesuite' ); ?></th>
							<th><?php echo esc_html__( 'Status', 'storesuite' ); ?></th>
							<th><?php echo esc_html__( 'Order Total', 'storesuite' ); ?></th>
							<th><?php echo esc_html__( 'Total Items', 'storesuite' ); ?></th>
							<th><?php echo esc_html__( 'Customer', 'storesuite' ); ?></th>
							<th><?php echo esc_html__( 'Billing Phone', 'storesuite' ); ?></th>
							<th><?php echo esc_html__( 'Date', 'storesuite' ); ?></th>
							<th class="text-right"><?php echo esc_html__( 'Action', 'storesuite' ); ?></th>
						</tr>
						<tbody>
							<?php
							$current_page    = ( get_query_var( 'paged' ) ) ? absint( get_query_var( 'paged' ) ) : 1;
							$orders_per_page = apply_filters( 'storesuite_orders_per_page', 10 );

							// Build filters array
							$filters = array(
								'search_term'    => isset( $_GET['search_by'] ) ? sanitize_text_field( wp_unslash( $_GET['search_by'] ) ) : '',
								'search_filter'  => isset( $_GET['search-filter'] ) ? sanitize_text_field( wp_unslash( $_GET['search-filter'] ) ) : 'all',
								'order_status'   => isset( $_GET['order_status'] ) ? sanitize_text_field( wp_unslash( $_GET['order_status'] ) ) : '',
								'_customer_user' => isset( $_GET['_customer_user'] ) ? sanitize_text_field( wp_unslash( $_GET['_customer_user'] ) ) : '',
								'order_channel'  => isset( $_GET['order_channel'] ) ? sanitize_text_field( wp_unslash( $_GET['order_channel'] ) ) : '',
								'm'              => isset( $_GET['m'] ) ? sanitize_text_field( wp_unslash( $_GET['m'] ) ) : '',
							);

							$orders = $orders_obj->get_all_orders( $orders_per_page, $current_page, $filters );

							if ( empty( $orders->orders ) ) {
								echo '<tr id="order-row-not-found"><td colspan="9">';
								storesuite_get_template_part(
									'not-found',
									'',
									array(
										'title' => esc_html__( 'No order found!', 'storesuite' ),
										'desc'  => esc_html__( 'There is nothing to display at the moment.', 'storesuite' ),
									)
								);
								echo '</td></tr>';
							} else {
								foreach ( $orders->orders as $order ) { // phpcs:ignore
									?>
								<tr>
									<td class="check-column">
										<label class="my-storesuite-checkbox">
											<input type="checkbox" name="bulk_order_ids[]" value="<?php echo esc_attr( $order->get_id() ); ?>" class="my-storesuite-checkbox-input">
											<span class="my-storesuite-checkbox-back"></span>
											<span class="my-storesuite-tick">
												<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-check" viewBox="0 0 16 16">
													<path d="M10.97 4.97a.75.75 0 0 1 1.07 1.05l-3.99 4.99a.75.75 0 0 1-1.08.02L4.324 8.384a.75.75 0 1 1 1.06-1.06l2.094 2.093 3.473-4.425z"></path>
												</svg>
											</span>
										</label>
									</td>
									<td data-title="<?php echo esc_attr__( 'Order', 'storesuite' ); ?>">
										<?php $orders_obj->get_order_number_column_value( $order ); ?>
									</td>
									<td data-title="<?php echo esc_attr__( 'Status', 'storesuite' ); ?>">
										<span class="msfc-badge msfc-badge-<?php echo esc_attr( storesuite_get_order_status_class( $order->get_status() ) ); ?>">
											<?php echo esc_html( wc_get_order_status_name( $order->get_status() ) ); ?>
										</span>
									</td>
									<td data-title="<?php echo esc_attr__( 'Total', 'storesuite' ); ?>">
										<?php echo wp_kses_post( $order->get_formatted_order_total() ); ?>
									</td>
									<td data-title="<?php echo esc_html__( 'Total Items', 'storesuite' ); ?>">
										<?php echo esc_html( $order->get_item_count() ); ?>
									</td>
									<td data-title="<?php echo esc_attr__( 'Customer', 'storesuite' ); ?>">
										<?php $orders_obj->get_order_customer_column_value( $order ); ?>
									</td>
									<td data-title="<?php echo esc_attr__( 'Billing Phone', 'storesuite' ); ?>">
										<?php
										if ( $order->get_billing_phone() ) {
											echo esc_html( $order->get_billing_phone() );
										} else {
											echo esc_html__( 'N/A', 'storesuite' );
										}
										?>
									</td>
									<td data-title="<?php echo esc_attr__( 'Date', 'storesuite' ); ?>">
										<time datetime="<?php echo esc_attr( $order->get_date_created()->date( 'c' ) ); ?>"><?php echo esc_html( wc_format_datetime( $order->get_date_created() ) ); ?></time>
									</td>
									<td class="text-right" data-title="<?php esc_attr_e( 'Actions', 'storesuite' ); ?>">
										<div class="msfc-dropdown">
											<span class="msfc-dropdown-icon">
												<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-three-dots" viewBox="0 0 16 16">
													<path d="M3 9.5a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3m5 0a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3m5 0a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3"/>
												</svg>
											</span>
											<ul class="msfc-dropdown-menu">
												<li>
													<a href="<?php echo esc_url( sprintf( storesuite_get_navigation_url( 'order-details' ) . '%s', $order->get_id() ) ); ?>" class="dropdown-link"><?php esc_html_e( 'View', 'storesuite' ); ?></a>
												</li>
												<li>
													<a href="<?php echo esc_url( sprintf( storesuite_get_navigation_url( 'edit-order' ) . '%s', $order->get_id() ) ); ?>" class="dropdown-link"><?php echo esc_html__( 'Edit', 'storesuite' ); ?></a>
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
					storesuite_get_template_part(
						'pagination',
						'',
						array(
							'total_items'  => $orders->total,
							'total_pages'  => $orders->max_num_pages,
							'current_page' => $current_page,
							'per_page'     => $orders_per_page,
						)
					);
				}
				?>
				</div>
			</form>
		</main>
	</div>
</div>
<?php do_action( 'storesuite_dashboard_wrapper_end' ); ?>