<?php
/**
 * Dashboard Top Products Items Sold Template
 *
 * This template displays the top products by items sold leaderboard on the dashboard.
 *
 * @package StoreSuite
 * @version 1.0.0
 *
 * @var string                           $label     Date range label.
 * @var array|\WP_Error                  $rows      Product rows data.
 * @var \PluginizeLab\StoreSuite\Dashboard $dashboard Dashboard instance for helper methods.
 */

defined( 'ABSPATH' ) || exit;
?>
<div class="col-md-6">
	<div class="msf-card msf-card-with-header msf-dashboard-top-products">
		<h2 class="msf-card-title">
			<?php echo esc_html__( 'Top products - Items sold', 'storesuite' ); ?>
		</h2>
		<div>
			<div class="msf-table-responsive">
				<table class="my-storesuite-tbl msf-dashboard-leaderboard-table">
					<thead>
						<tr>
							<th><?php echo esc_html__( 'Product', 'storesuite' ); ?></th>
							<th><?php echo esc_html__( 'Items sold', 'storesuite' ); ?></th>
							<th><?php echo esc_html__( 'Net sales', 'storesuite' ); ?></th>
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
									<?php echo esc_html__( 'No products found for this period.', 'storesuite' ); ?>
								</td>
							</tr>
						<?php else : ?>
							<?php foreach ( $rows as $row ) : ?>
								<tr>
									<td>
										<?php
										$product_name = isset( $row['product_name'] ) ? (string) $row['product_name'] : '';
										$product_id   = isset( $row['product_id'] ) ? (int) $row['product_id'] : 0;

										if ( $product_id > 0 && function_exists( 'storesuite_get_navigation_url' ) ) {
											$edit_url = sprintf( storesuite_get_navigation_url( 'edit-product' ) . '%s', $product_id );
											printf(
												'<a href="%s">%s</a>',
												esc_url( $edit_url ),
												esc_html( $product_name )
											);
										} else {
											echo esc_html( $product_name );
										}
										?>
									</td>
									<td><?php echo esc_html( number_format_i18n( (float) ( $row['items_sold'] ?? 0 ) ) ); ?></td>
									<td>
										<?php
										echo wp_kses_post( $dashboard->format_value( $row['net_revenue'] ?? null, 'currency' ) );
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
