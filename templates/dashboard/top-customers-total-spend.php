<?php
/**
 * Dashboard Top Customers Total Spend Template
 *
 * This template displays the top customers by total spend leaderboard on the dashboard.
 *
 * @package ShopFront
 * @version 1.0.0
 *
 * @var string                           $label     Date range label.
 * @var array|\WP_Error                  $rows      Customer rows data.
 * @var \PluginizeLab\ShopFront\Dashboard $dashboard Dashboard instance for helper methods.
 */

defined( 'ABSPATH' ) || exit;
?>
<div class="col-md-6">
	<div class="msf-card msf-card-with-header msf-dashboard-top-customers">
		<h2 class="msf-card-title">
			<?php echo esc_html__( 'Top customers - Total spend', 'shop-front' ); ?>
		</h2>
		<div>
			<div class="msf-table-responsive">
				<table class="my-shop-front-tbl msf-dashboard-leaderboard-table">
					<thead>
						<tr>
							<th><?php echo esc_html__( 'Customer name', 'shop-front' ); ?></th>
							<th><?php echo esc_html__( 'Orders', 'shop-front' ); ?></th>
							<th><?php echo esc_html__( 'Total spend', 'shop-front' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php if ( is_wp_error( $rows ) ) : ?>
							<tr>
								<td colspan="3">
									<?php echo esc_html( $rows->get_error_message() ); ?>
								</td>
							</tr>
						<?php elseif ( empty( $rows ) ) : ?>
							<tr>
								<td colspan="3">
									<?php echo esc_html__( 'No customers found for this period.', 'shop-front' ); ?>
								</td>
							</tr>
						<?php else : ?>
							<?php foreach ( $rows as $row ) : ?>
								<tr>
									<td>
										<?php
										$customer_name = isset( $row['customer_name'] ) ? (string) $row['customer_name'] : '';
										$customer_id   = isset( $row['customer_id'] ) ? (int) $row['customer_id'] : 0;

										if ( $customer_id > 0 ) {
											$user = get_user_by( 'id', $customer_id );
											if ( $user && function_exists( 'msfc_get_navigation_url' ) ) {
												// Link to customer orders page if available.
												$orders_url = add_query_arg( 'customer', $customer_id, msfc_get_navigation_url( 'orders' ) );
												printf(
													'<a href="%s">%s</a>',
													esc_url( $orders_url ),
													esc_html( $customer_name )
												);
											} else {
												echo esc_html( $customer_name );
											}
										} else {
											echo esc_html( $customer_name );
										}
										?>
									</td>
									<td><?php echo esc_html( number_format_i18n( (int) ( $row['orders_count'] ?? 0 ) ) ); ?></td>
									<td>
										<?php
										echo $dashboard->format_value( $row['total_spend'] ?? null, 'currency' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
										?>
									</td>
								</tr>
							<?php endforeach; ?>
						<?php endif; ?>
					</tbody>
				</table>
			</div>
		</div>
	</div>
</div>
