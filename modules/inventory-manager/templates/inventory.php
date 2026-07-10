<?php
/**
 * Inventory Manager — stock list screen.
 *
 * @package StoreSuite
 */

use PluginizeLab\StoreSuite\Modules\InventoryManager\StockRepository;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// phpcs:disable WordPress.Security.NonceVerification.Recommended -- read-only list filters.
$storesuite_search   = isset( $_GET['search_by'] ) ? sanitize_text_field( wp_unslash( $_GET['search_by'] ) ) : '';
$storesuite_status   = isset( $_GET['stock_status'] ) ? sanitize_text_field( wp_unslash( $_GET['stock_status'] ) ) : '';
$storesuite_low_only = ! empty( $_GET['low_only'] );
$storesuite_paged    = isset( $_GET['paged'] ) ? max( 1, absint( wp_unslash( $_GET['paged'] ) ) ) : ( get_query_var( 'paged' ) ? (int) get_query_var( 'paged' ) : 1 );
// phpcs:enable WordPress.Security.NonceVerification.Recommended

$storesuite_per_page = (int) apply_filters( 'storesuite_inventory_per_page', 20 );
$storesuite_result   = StockRepository::get_paginated(
	array(
		'search'       => $storesuite_search,
		'stock_status' => $storesuite_status,
		'low_only'     => $storesuite_low_only,
		'paged'        => $storesuite_paged,
		'per_page'     => $storesuite_per_page,
	)
);
$storesuite_log_url = add_query_arg( 'view', 'log', storesuite_get_navigation_url( 'inventory' ) );

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
				array( 'page_title' => __( 'Inventory', 'storesuite' ) )
			);

			// Low-stock summary banner (skipped while already filtering low-only).
			if ( ! $storesuite_low_only ) {
				$storesuite_low_count = StockRepository::get_paginated(
					array(
						'low_only' => true,
						'per_page' => 1,
						'paged'    => 1,
					)
				)['total'];
				if ( $storesuite_low_count > 0 ) {
					$storesuite_low_url = add_query_arg( 'low_only', '1', storesuite_get_navigation_url( 'inventory' ) );
					?>
					<div class="storesuite-bulk-edit-feedback storesuite-form-group storesuite-inventory-low-banner" role="status">
						<span class="storesuite-badge storesuite-badge-warning"><?php echo esc_html( $storesuite_low_count ); ?></span>
						<?php
						printf(
							/* translators: %s: link to the low-stock view */
							esc_html__( 'product(s) are at or below their low-stock threshold. %s', 'storesuite' ),
							'<a href="' . esc_url( $storesuite_low_url ) . '">' . esc_html__( 'View low stock', 'storesuite' ) . '</a>'
						);
						?>
					</div>
					<?php
				}
			}
			?>

			<div class="storesuite-table-header-part">
				<div class="row align-items-center">
					<div class="col-md">
						<form action="" method="get" class="storesuite-inventory-filters">
							<div class="storesuite-table-search-input">
								<div class="storesuite-table-search-icon">
									<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20" height="20" aria-hidden="true" focusable="false"><path d="M23.707,22.293l-5.969-5.969a10.016,10.016,0,1,0-1.414,1.414l5.969,5.969a1,1,0,0,0,1.414-1.414ZM10,18a8,8,0,1,1,8-8A8.009,8.009,0,0,1,10,18Z"/></svg>
								</div>
								<input type="text" name="search_by" placeholder="<?php esc_attr_e( 'Search product or SKU', 'storesuite' ); ?>" value="<?php echo esc_attr( $storesuite_search ); ?>" />
							</div>
							<select name="stock_status" class="storesuite-form-control">
								<option value=""><?php esc_html_e( 'All stock statuses', 'storesuite' ); ?></option>
								<option value="instock" <?php selected( $storesuite_status, 'instock' ); ?>><?php esc_html_e( 'In stock', 'storesuite' ); ?></option>
								<option value="outofstock" <?php selected( $storesuite_status, 'outofstock' ); ?>><?php esc_html_e( 'Out of stock', 'storesuite' ); ?></option>
								<option value="onbackorder" <?php selected( $storesuite_status, 'onbackorder' ); ?>><?php esc_html_e( 'On backorder', 'storesuite' ); ?></option>
							</select>
							<label class="storesuite-low-only">
								<input type="checkbox" name="low_only" value="1" <?php checked( $storesuite_low_only ); ?> />
								<?php esc_html_e( 'Low stock only', 'storesuite' ); ?>
							</label>
							<button type="submit" class="my-storesuite-button my-storesuite-button-light"><?php esc_html_e( 'Filter', 'storesuite' ); ?></button>
						</form>
					</div>
					<div class="col-md-auto text-md-end">
						<a href="<?php echo esc_url( $storesuite_log_url ); ?>" class="my-storesuite-button my-storesuite-button-light"><?php esc_html_e( 'Movement log', 'storesuite' ); ?></a>
					</div>
				</div>
			</div>

			<div class="storesuite-inventory-bulk storesuite-form-group">
				<select id="storesuite-inventory-bulk-op" class="storesuite-form-control">
					<option value="set"><?php esc_html_e( 'Set stock to', 'storesuite' ); ?></option>
					<option value="increase"><?php esc_html_e( 'Increase by', 'storesuite' ); ?></option>
					<option value="decrease"><?php esc_html_e( 'Decrease by', 'storesuite' ); ?></option>
				</select>
				<input type="number" id="storesuite-inventory-bulk-qty" class="storesuite-form-control" value="0" />
				<button type="button" class="my-storesuite-button" id="storesuite-inventory-bulk-apply"><?php esc_html_e( 'Apply to selected', 'storesuite' ); ?></button>
			</div>

			<div class="storesuite-table-responsive">
				<table class="my-storesuite-tbl storesuite-list-table">
					<thead>
						<tr>
							<th class="check-column"><input type="checkbox" id="storesuite-inventory-select-all" /></th>
							<th><?php esc_html_e( 'Product', 'storesuite' ); ?></th>
							<th><?php esc_html_e( 'SKU', 'storesuite' ); ?></th>
							<th><?php esc_html_e( 'Status', 'storesuite' ); ?></th>
							<th><?php esc_html_e( 'Stock', 'storesuite' ); ?></th>
							<th><?php esc_html_e( 'Low threshold', 'storesuite' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php if ( empty( $storesuite_result['items'] ) ) : ?>
							<tr><td colspan="6"><?php esc_html_e( 'No stock-managed products found.', 'storesuite' ); ?></td></tr>
						<?php else : ?>
							<?php foreach ( $storesuite_result['items'] as $storesuite_item ) : ?>
								<tr class="single-product-item storesuite-list-row <?php echo $storesuite_item['is_low'] ? 'storesuite-row-low' : ''; ?>" data-id="<?php echo esc_attr( $storesuite_item['id'] ); ?>">
									<td class="check-column"><input type="checkbox" class="storesuite-inventory-check" value="<?php echo esc_attr( $storesuite_item['id'] ); ?>" /></td>
									<td data-title="<?php esc_attr_e( 'Product', 'storesuite' ); ?>">
										<a href="<?php echo esc_url( $storesuite_item['edit_url'] ); ?>"><?php echo esc_html( $storesuite_item['name'] ); ?></a>
									</td>
									<td data-title="<?php esc_attr_e( 'SKU', 'storesuite' ); ?>"><?php echo esc_html( $storesuite_item['sku'] ); ?></td>
									<td data-title="<?php esc_attr_e( 'Status', 'storesuite' ); ?>">
										<?php
										$storesuite_badge  = 'success';
										$storesuite_labels = array(
											'instock'     => __( 'In stock', 'storesuite' ),
											'outofstock'  => __( 'Out of stock', 'storesuite' ),
											'onbackorder' => __( 'On backorder', 'storesuite' ),
										);
										if ( 'outofstock' === $storesuite_item['stock_status'] ) {
											$storesuite_badge = 'danger';
										} elseif ( 'onbackorder' === $storesuite_item['stock_status'] ) {
											$storesuite_badge = 'warning';
										}
										$storesuite_status_label = $storesuite_labels[ $storesuite_item['stock_status'] ] ?? $storesuite_item['stock_status'];
										?>
										<span class="storesuite-badge storesuite-badge-<?php echo esc_attr( $storesuite_badge ); ?>"><?php echo esc_html( $storesuite_status_label ); ?></span>
									</td>
									<td data-title="<?php esc_attr_e( 'Stock', 'storesuite' ); ?>">
										<span class="storesuite-inventory-qty-edit">
											<input type="number" class="storesuite-form-control storesuite-inventory-qty" value="<?php echo esc_attr( $storesuite_item['stock_qty'] ); ?>" />
											<button type="button" class="my-storesuite-button edit storesuite-inventory-save"><?php esc_html_e( 'Save', 'storesuite' ); ?></button>
										</span>
									</td>
									<td data-title="<?php esc_attr_e( 'Low threshold', 'storesuite' ); ?>"><?php echo esc_html( $storesuite_item['low_stock'] ); ?></td>
								</tr>
							<?php endforeach; ?>
						<?php endif; ?>
					</tbody>
				</table>
			</div>

			<?php
			if ( $storesuite_result['total_pages'] > 1 ) {
				storesuite_get_template_part(
					'pagination',
					null,
					array(
						'total_items'  => $storesuite_result['total'],
						'total_pages'  => $storesuite_result['total_pages'],
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
