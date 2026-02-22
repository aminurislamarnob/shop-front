<?php
/**
 * Dashboard Top Coupons Orders Count Template
 *
 * This template displays the top coupons by number of orders leaderboard on the dashboard.
 *
 * @package StoreSuite
 * @version 1.0.0
 *
 * @var string                           $label     Date range label.
 * @var array|\WP_Error                  $rows      Coupon rows data.
 * @var \PluginizeLab\StoreSuite\Dashboard $dashboard Dashboard instance for helper methods.
 */

defined( 'ABSPATH' ) || exit;
?>
<div class="col-md-6">
	<div class="msf-card msf-card-with-header msf-dashboard-top-coupons">
		<h2 class="msf-card-title">
			<?php echo esc_html__( 'Top coupons - Number of orders', 'storesuite' ); ?>
		</h2>
		<div>
			<div class="msf-table-responsive">
				<table class="my-storesuite-tbl msf-dashboard-leaderboard-table">
					<thead>
						<tr>
							<th><?php echo esc_html__( 'Coupon code', 'storesuite' ); ?></th>
							<th><?php echo esc_html__( 'Orders', 'storesuite' ); ?></th>
							<th><?php echo esc_html__( 'Amount discounted', 'storesuite' ); ?></th>
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
									<?php echo esc_html__( 'No coupons found for this period.', 'storesuite' ); ?>
								</td>
							</tr>
						<?php else : ?>
							<?php foreach ( $rows as $row ) : ?>
								<tr>
									<td>
										<?php
										$coupon_code = isset( $row['coupon_code'] ) ? (string) $row['coupon_code'] : '';
										$coupon_id   = isset( $row['coupon_id'] ) ? (int) $row['coupon_id'] : 0;

										if ( $coupon_id > 0 && function_exists( 'storesuite_get_navigation_url' ) ) {
											$coupon_url = add_query_arg( 'coupon_id', $coupon_id, storesuite_get_navigation_url( 'coupons' ) );
											printf(
												'<a href="%s">%s</a>',
												esc_url( $coupon_url ),
												esc_html( $coupon_code )
											);
										} else {
											echo esc_html( $coupon_code );
										}
										?>
									</td>
									<td><?php echo esc_html( number_format_i18n( (int) ( $row['orders_count'] ?? 0 ) ) ); ?></td>
									<td>
										<?php
										echo wp_kses_post( $dashboard->format_value( $row['amount'] ?? null, 'currency' ) );
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
