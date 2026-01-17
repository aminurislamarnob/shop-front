<?php
/**
 * MSFC brands list page
 *
 * @package ShopFront
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use PluginizeLab\ShopFront\ProductBrand\Brands;

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
								<input type="text" name="search" id="search" placeholder="<?php esc_attr_e( 'Search Brand', 'shop-front' ); ?>" />
							</div>
						</form>
					</div>
					<div class="col-md-6 text-right">
						<a href="<?php echo esc_url( msfc_get_navigation_url( 'add-new-brand' ) ); ?>" class="my-shop-front-button">
							<svg xmlns="http://www.w3.org/2000/svg" id="Outline" viewBox="0 0 24 24" width="24" height="24">
								<path d="M23,11H13V1a1,1,0,0,0-1-1h0a1,1,0,0,0-1,1V11H1a1,1,0,0,0-1,1H0a1,1,0,0,0,1,1H11V23a1,1,0,0,0,1,1h0a1,1,0,0,0,1-1V13H23a1,1,0,0,0,1-1h0A1,1,0,0,0,23,11Z"/>
							</svg>
							<?php esc_html_e( 'Add Brand', 'shop-front' ); ?>
						</a>
					</div>
				</div>
			</div>
			<?php
			$current_page    = ( get_query_var( 'paged' ) ) ? absint( get_query_var( 'paged' ) ) : 1;
			$brands_per_page = apply_filters( 'msf_brands_per_page', 10 );
			$product_brands  = new Brands();
			$brands_data     = $product_brands->get_paginated_brands_with_children( $brands_per_page, $current_page );
			?>
			<div class="msf-table-responsive">
				<table class="my-shop-front-tbl my-shop-front-product-list-table">
					<thead>
						<tr>
							<th><?php esc_html_e( 'Name', 'shop-front' ); ?></th>
							<th><?php esc_html_e( 'Description', 'shop-front' ); ?></th>
							<th><?php esc_html_e( 'Parent', 'shop-front' ); ?></th>
							<th><?php esc_html_e( 'Slug', 'shop-front' ); ?></th>
							<th><?php esc_html_e( 'Count', 'shop-front' ); ?></th>
							<th class="text-right"><?php esc_html_e( 'Actions', 'shop-front' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php
						if ( empty( $brands_data->brands ) ) {
							echo '<tr id="brand-row-not-found"><td colspan="6">';
							msf_get_template_part(
								'not-found',
								'',
								array(
									'title' => esc_html__( 'No brand found!', 'shop-front' ),
									'desc'  => esc_html__( 'There is nothing to display at the moment. Please try adding a brand.', 'shop-front' ),
								)
							);
							echo '</td></tr>';
						} else {
							foreach ( $brands_data->brands as $brand_item ) {
								$product_brand = $brand_item['brand'];
								$depth         = $brand_item['depth'];
								$parent        = $brand_item['parent'];
								$dash_prefix   = str_repeat( '&mdash; ', $depth );

								$template_args = array(
									'brand'       => $product_brand,
									'dash_prefix' => $dash_prefix,
									'parent'      => $parent,
								);
								msf_get_template_part( 'brands/brand-list-table-row', '', $template_args );
							}
						}
						?>
					</tbody>
				</table>
				<?php
				if ( $brands_data->max_num_pages > 1 ) {
					msf_get_template_part(
						'pagination',
						'',
						array(
							'total_items'  => $brands_data->total,
							'total_pages'  => $brands_data->max_num_pages,
							'current_page' => $current_page,
							'per_page'     => $brands_per_page,
						)
					);
				}
				?>
			</div>
		</main>
	</div>
</div>
<?php do_action( 'msf_dashboard_wrapper_end' ); ?>