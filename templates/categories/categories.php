<?php
/**
 * MSFC category List Page
 *
 * @package ShopFront
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use PluginizeLab\ShopFront\ProductCategory\Categories;

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
								<input type="text" name="search" id="search" placeholder="<?php esc_attr_e( 'Search Category', 'shop-front' ); ?>" />
							</div>
						</form>
					</div>
					<div class="col-md-6 text-right">
						<a href="<?php echo esc_url( msfc_get_navigation_url( 'add-new-category' ) ); ?>" class="my-shop-front-button">
							<svg xmlns="http://www.w3.org/2000/svg" id="Outline" viewBox="0 0 24 24" width="24" height="24">
								<path d="M23,11H13V1a1,1,0,0,0-1-1h0a1,1,0,0,0-1,1V11H1a1,1,0,0,0-1,1H0a1,1,0,0,0,1,1H11V23a1,1,0,0,0,1,1h0a1,1,0,0,0,1-1V13H23a1,1,0,0,0,1-1h0A1,1,0,0,0,23,11Z"/>
							</svg>
							<?php esc_html_e( 'Add Category', 'shop-front' ); ?>
						</a>
					</div>
				</div>
			</div>
			<?php
			$current_page        = ( get_query_var( 'paged' ) ) ? absint( get_query_var( 'paged' ) ) : 1;
			$categories_per_page = apply_filters( 'msf_categories_per_page', 15 );
			$product_categories  = new Categories();
			$categories_data     = $product_categories->get_paginated_categories_with_children( $categories_per_page, $current_page );
			?>
			<div class="msf-table-responsive">
				<table class="my-shop-front-tbl my-shop-front-product-list-table">
					<thead>
						<tr>
							<th width="210"><?php echo esc_html__( 'Name', 'shop-front' ); ?></th>
							<th><?php echo esc_html__( 'Description', 'shop-front' ); ?></th>
							<th><?php echo esc_html__( 'Parent', 'shop-front' ); ?></th>
							<th width="210"><?php echo esc_html__( 'Slug', 'shop-front' ); ?></th>
							<th width="70"><?php echo esc_html__( 'Count', 'shop-front' ); ?></th>
							<th class="text-right"><?php echo esc_html__( 'Action', 'shop-front' ); ?></th>
						</tr>
						<tbody>
							<?php
							if ( empty( $categories_data->categories ) ) {
								echo '<tr id="tag-category-not-found"><td colspan="6">';
								msf_get_template_part(
									'not-found',
									'',
									array(
										'title' => esc_html__( 'No category found!', 'shop-front' ),
										'desc'  => esc_html__( 'There is nothing to display at the moment. Please try adding a category.', 'shop-front' ),
									)
								);
								echo '</td></tr>';
							} else {
								foreach ( $categories_data->categories as $category_item ) {
									$product_category = $category_item['category'];
									$depth            = $category_item['depth'];
									$parent           = $category_item['parent'];
									$dash_prefix      = str_repeat( '&mdash; ', $depth );

									$template_args = array(
										'category'    => $product_category,
										'dash_prefix' => $dash_prefix,
										'parent'      => $parent,
									);
									msf_get_template_part( 'categories/category-list-table-row', '', $template_args );
								}
							}
							?>
						</tbody>
					</thead>
				</table>
				<?php
				if ( $categories_data->max_num_pages > 1 ) {
					msf_get_template_part(
						'pagination',
						'',
						array(
							'total_items'  => $categories_data->total,
							'total_pages'  => $categories_data->max_num_pages,
							'current_page' => $current_page,
							'per_page'     => $categories_per_page,
						)
					);
				}
				?>
			</div>
		</main>
	</div>
</div>
<?php do_action( 'msf_dashboard_wrapper_end' ); ?>