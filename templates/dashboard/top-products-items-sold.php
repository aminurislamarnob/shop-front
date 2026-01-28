<?php
/**
 * Dashboard Top Products Items Sold Template
 *
 * This template displays the top products by items sold leaderboard on the dashboard.
 *
 * @package ShopFront
 * @version 1.0.0
 *
 * @var string                           $label     Date range label.
 * @var array|\WP_Error                  $rows      Product rows data.
 * @var \PluginizeLab\ShopFront\Dashboard $dashboard Dashboard instance for helper methods.
 */

defined( 'ABSPATH' ) || exit;
?>
<div class="msf-card msf-card-with-header msf-dashboard-top-products">
	<h2 class="msf-card-title">
		<?php echo esc_html__( 'Top products - Items sold', 'shop-front' ); ?>
		<?php if ( '' !== $label ) : ?>
			<small>(<?php echo esc_html( $label ); ?>)</small>
		<?php endif; ?>
	</h2>
	<div class="msf-card-content">
		<?php if ( is_wp_error( $rows ) ) : ?>
			<p><?php echo esc_html( $rows->get_error_message() ); ?></p>
		<?php elseif ( empty( $rows ) ) : ?>
			<p><?php echo esc_html__( 'No products found for this period.', 'shop-front' ); ?></p>
		<?php else : ?>
			<div class="msf-table-responsive">
				<table class="my-shop-front-tbl msf-dashboard-leaderboard-table">
					<thead>
						<tr>
							<th><?php echo esc_html__( 'Product', 'shop-front' ); ?></th>
							<th><?php echo esc_html__( 'Items sold', 'shop-front' ); ?></th>
							<th><?php echo esc_html__( 'Net sales', 'shop-front' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $rows as $row ) : ?>
							<tr>
								<td>
									<?php
									$product_name = isset( $row['product_name'] ) ? (string) $row['product_name'] : '';
									$product_id   = isset( $row['product_id'] ) ? (int) $row['product_id'] : 0;

									if ( $product_id > 0 && function_exists( 'msfc_get_navigation_url' ) ) {
										$edit_url = sprintf( msfc_get_navigation_url( 'edit-product' ) . '%s', $product_id );
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
