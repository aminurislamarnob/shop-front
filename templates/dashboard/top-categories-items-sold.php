<?php
/**
 * Dashboard Top Categories Items Sold Template
 *
 * This template displays the top categories by items sold leaderboard on the dashboard.
 *
 * @package ShopFront
 * @version 1.0.0
 *
 * @var string                           $label     Date range label.
 * @var array|\WP_Error                  $rows      Category rows data.
 * @var \PluginizeLab\ShopFront\Dashboard $dashboard Dashboard instance for helper methods.
 */

defined( 'ABSPATH' ) || exit;
?>
<div class="col-md-6">
	<div class="msf-card msf-card-with-header msf-dashboard-top-categories">
		<h2 class="msf-card-title">
			<?php echo esc_html__( 'Top categories - Items sold', 'shop-front' ); ?>
			<?php if ( '' !== $label ) : ?>
				<small>(<?php echo esc_html( $label ); ?>)</small>
			<?php endif; ?>
		</h2>
		<div>
			<?php if ( is_wp_error( $rows ) ) : ?>
				<p><?php echo esc_html( $rows->get_error_message() ); ?></p>
			<?php elseif ( empty( $rows ) ) : ?>
				<p><?php echo esc_html__( 'No categories found for this period.', 'shop-front' ); ?></p>
			<?php else : ?>
				<div class="msf-table-responsive">
					<table class="my-shop-front-tbl msf-dashboard-leaderboard-table">
						<thead>
							<tr>
								<th><?php echo esc_html__( 'Category', 'shop-front' ); ?></th>
								<th><?php echo esc_html__( 'Items sold', 'shop-front' ); ?></th>
								<th><?php echo esc_html__( 'Net sales', 'shop-front' ); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ( $rows as $row ) : ?>
								<tr>
									<td>
										<?php
										$category_name = isset( $row['category_name'] ) ? (string) $row['category_name'] : '';
										$category_id   = isset( $row['category_id'] ) ? (int) $row['category_id'] : 0;

										if ( $category_id > 0 ) {
											$category_link = get_term_link( $category_id, 'product_cat' );
											if ( ! is_wp_error( $category_link ) ) {
												printf(
													'<a href="%s" target="_blank">%s</a>',
													esc_url( $category_link ),
													esc_html( $category_name )
												);
											} else {
												echo esc_html( $category_name );
											}
										} else {
											echo esc_html( $category_name );
										}
										?>
									</td>
									<td><?php echo esc_html( number_format_i18n( (float) ( $row['items_sold'] ?? 0 ) ) ); ?></td>
									<td>
										<?php
										echo $dashboard->format_value( $row['net_revenue'] ?? null, 'currency' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
										?>
									</td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				</div>
			<?php endif; ?>
		</div>
	</div>
</div>
