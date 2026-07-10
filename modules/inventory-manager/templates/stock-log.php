<?php
/**
 * Inventory Manager — stock movement log screen.
 *
 * @package StoreSuite
 */

use PluginizeLab\StoreSuite\Modules\InventoryManager\StockLog;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// phpcs:disable WordPress.Security.NonceVerification.Recommended -- read-only log filters.
$storesuite_product = isset( $_GET['product'] ) ? absint( wp_unslash( $_GET['product'] ) ) : 0;
$storesuite_paged   = isset( $_GET['paged'] ) ? max( 1, absint( wp_unslash( $_GET['paged'] ) ) ) : ( get_query_var( 'paged' ) ? (int) get_query_var( 'paged' ) : 1 );
// phpcs:enable WordPress.Security.NonceVerification.Recommended

$storesuite_per_page = (int) apply_filters( 'storesuite_stock_log_per_page', 30 );
$storesuite_log      = StockLog::query(
	array(
		'product_id' => $storesuite_product,
		'paged'      => $storesuite_paged,
		'per_page'   => $storesuite_per_page,
	)
);
$storesuite_list_url = storesuite_get_navigation_url( 'inventory' );

do_action( 'storesuite_dashboard_wrapper_start' );
?>
<div class="my-storesuite-container">
	<aside id="storesuite-dashboard-sidebar" class="my-storesuite-sidebar" role="navigation" aria-label="<?php esc_attr_e( 'Store dashboard navigation', 'storesuite' ); ?>">
		<?php do_action( 'storesuite_dashboard_navigation' ); ?>
	</aside>
	<div class="my-storesuite-wrapper">
		<?php do_action( 'storesuite_dashboard_content_before' ); ?>
		<main class="my-storesuite-page-content">
			<?php do_action( 'storesuite_dashboard_before_main_content' ); ?>

			<?php
			storesuite_get_template_part(
				'dashboard-title',
				null,
				array(
					'page_title'            => __( 'Stock movement log', 'storesuite' ),
					'parent_endpoint_title' => __( 'Inventory', 'storesuite' ),
					'parent_endpoint_url'   => $storesuite_list_url,
				)
			);
			?>

			<div class="storesuite-table-responsive">
				<table class="my-storesuite-tbl storesuite-list-table">
					<thead>
						<tr>
							<th><?php esc_html_e( 'When', 'storesuite' ); ?></th>
							<th><?php esc_html_e( 'Product', 'storesuite' ); ?></th>
							<th><?php esc_html_e( 'Change', 'storesuite' ); ?></th>
							<th><?php esc_html_e( 'Type', 'storesuite' ); ?></th>
							<th><?php esc_html_e( 'Reference', 'storesuite' ); ?></th>
							<th><?php esc_html_e( 'By', 'storesuite' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php if ( empty( $storesuite_log['items'] ) ) : ?>
							<tr><td colspan="6"><?php esc_html_e( 'No stock movements recorded yet.', 'storesuite' ); ?></td></tr>
						<?php else : ?>
							<?php foreach ( $storesuite_log['items'] as $storesuite_row ) : ?>
								<?php
								$storesuite_product_obj = wc_get_product( (int) $storesuite_row['product_id'] );
								$storesuite_user        = get_userdata( (int) $storesuite_row['user_id'] );
								$storesuite_change      = '';
								if ( null !== $storesuite_row['qty_before'] && null !== $storesuite_row['qty_after'] ) {
									$storesuite_change = (int) $storesuite_row['qty_before'] . ' → ' . (int) $storesuite_row['qty_after'];
								} elseif ( null !== $storesuite_row['qty_after'] ) {
									$storesuite_change = '→ ' . (int) $storesuite_row['qty_after'];
								}
								?>
								<tr class="single-product-item storesuite-list-row">
									<td data-title="<?php esc_attr_e( 'When', 'storesuite' ); ?>"><?php echo esc_html( date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $storesuite_row['created_at'] ) ) ); ?></td>
									<td data-title="<?php esc_attr_e( 'Product', 'storesuite' ); ?>"><?php echo esc_html( $storesuite_product_obj ? $storesuite_product_obj->get_name() : '#' . (int) $storesuite_row['product_id'] ); ?></td>
									<td data-title="<?php esc_attr_e( 'Change', 'storesuite' ); ?>"><?php echo esc_html( $storesuite_change ); ?></td>
									<td data-title="<?php esc_attr_e( 'Type', 'storesuite' ); ?>"><span class="storesuite-badge"><?php echo esc_html( $storesuite_row['change_type'] ); ?></span></td>
									<td data-title="<?php esc_attr_e( 'Reference', 'storesuite' ); ?>"><?php echo esc_html( $storesuite_row['reference'] ); ?></td>
									<td data-title="<?php esc_attr_e( 'By', 'storesuite' ); ?>"><?php echo esc_html( $storesuite_user ? $storesuite_user->display_name : __( 'System', 'storesuite' ) ); ?></td>
								</tr>
							<?php endforeach; ?>
						<?php endif; ?>
					</tbody>
				</table>
			</div>

			<?php
			if ( $storesuite_log['total_pages'] > 1 ) {
				storesuite_get_template_part(
					'pagination',
					null,
					array(
						'total_items'  => $storesuite_log['total'],
						'total_pages'  => $storesuite_log['total_pages'],
						'current_page' => $storesuite_paged,
						'per_page'     => $storesuite_per_page,
					)
				);
			}
			?>
		</main>
	</div>
</div>
<?php do_action( 'storesuite_dashboard_wrapper_end' ); ?>
