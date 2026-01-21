<?php
/**
 * MSFC product List Page
 *
 * @package ShopFront
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use PluginizeLab\ShopFront\Product\Products;

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
					<div class="col-md-3">
						<form action="" method="get">
							<div class="msf-table-search-input">
								<div class="msf-table-search-icon">
									<svg xmlns="http://www.w3.org/2000/svg" id="Outline" viewBox="0 0 24 24" width="24" height="24">
										<path d="M23.707,22.293l-5.969-5.969a10.016,10.016,0,1,0-1.414,1.414l5.969,5.969a1,1,0,0,0,1.414-1.414ZM10,18a8,8,0,1,1,8-8A8.009,8.009,0,0,1,10,18Z"/>
									</svg>
								</div>
								<input type="text" name="search_by" id="search_by" placeholder="<?php esc_attr_e( 'Search Product', 'shop-front' ); ?>" value="<?php echo esc_attr( isset( $_GET['search_by'] ) ? sanitize_text_field( wp_unslash( $_GET['search_by'] ) ) : '' ); ?>" />
							</div>
						</form>
					</div>
					<div class="col-md-9 text-right">
						<div class="row justify-content-end">
							<div class="col-md-auto">
								<a href="<?php echo esc_url( msfc_get_navigation_url( 'add-new-product' ) ); ?>" class="my-shop-front-button">
									<svg xmlns="http://www.w3.org/2000/svg" id="Outline" viewBox="0 0 24 24" width="24" height="24">
										<path d="M23,11H13V1a1,1,0,0,0-1-1h0a1,1,0,0,0-1,1V11H1a1,1,0,0,0-1,1H0a1,1,0,0,0,1,1H11V23a1,1,0,0,0,1,1h0a1,1,0,0,0,1-1V13H23a1,1,0,0,0,1-1h0A1,1,0,0,0,23,11Z"/>
									</svg>
									<?php esc_html_e( 'Add Product', 'shop-front' ); ?>
								</a>
							</div>
							<div class="col-md-auto">
								<button type="button" class="my-shop-front-button msf-filter-toggle" id="msf-filter-toggle">
									<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-funnel" viewBox="0 0 16 16">
										<path d="M1.5 1.5A.5.5 0 0 1 2 1h12a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-.128.334L10 8.692V13.5a.5.5 0 0 1-.342.474l-3 1A.5.5 0 0 1 6 14.5V8.692L1.628 3.834A.5.5 0 0 1 1.5 3.5zm1 .5v1.308l4.372 4.858A.5.5 0 0 1 7 8.5v5.306l2-.666V8.5a.5.5 0 0 1 .128-.334L13.5 3.308V2z"/>
									</svg>
									<?php esc_html_e( 'Filter', 'shop-front' ); ?>
								</button>
							</div>
						</div>
					</div>
				</div>
			</div>
			<!-- Off-canvas Filter -->
			<?php msf_get_template_part( 'products/product-filters-offcanvas' ); ?>
			<div class="msf-table-responsive">
				<?php
				$current_page = ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : 1;
				$search_term  = isset( $_GET['search_by'] ) ? sanitize_text_field( wp_unslash( $_GET['search_by'] ) ) : '';

				// Get filter parameters
				$filters = array(
					'category'     => isset( $_GET['product_cat'] ) ? absint( $_GET['product_cat'] ) : '',
					'product_type' => isset( $_GET['product_type'] ) ? sanitize_text_field( wp_unslash( $_GET['product_type'] ) ) : '',
					'stock_status' => isset( $_GET['stock_status'] ) ? sanitize_text_field( wp_unslash( $_GET['stock_status'] ) ) : '',
					'brand'        => isset( $_GET['product_brand'] ) ? absint( $_GET['product_brand'] ) : '',
				);

				$products_obj  = new Products();
				$products_data = $products_obj->get_paginated_products( $current_page, $search_term, $filters );
				$product_query = $products_data->products;

				if ( $product_query->found_posts > 0 ) {
					?>
				<table class="my-shop-front-tbl my-shop-front-product-list-table">
					<thead>
						<tr>
							<th>
								<label class="my-shop-front-checkbox">
									<input type="checkbox" name="" id="" class="my-shop-front-checkbox-input">
									<span class="my-shop-front-checkbox-back"></span>
									<span class="my-shop-front-tick">
										<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-check" viewBox="0 0 16 16">
											<path d="M10.97 4.97a.75.75 0 0 1 1.07 1.05l-3.99 4.99a.75.75 0 0 1-1.08.02L4.324 8.384a.75.75 0 1 1 1.06-1.06l2.094 2.093 3.473-4.425z"/>
										</svg>
									</span>
								</label>
							</th>
							<th><?php esc_html_e( 'Image', 'shop-front' ); ?></th>
							<th><?php esc_html_e( 'Name', 'shop-front' ); ?></th>
							<th><?php esc_html_e( 'Category', 'shop-front' ); ?></th>
							<th><?php esc_html_e( 'Status', 'shop-front' ); ?></th>
							<th><?php esc_html_e( 'SKU', 'shop-front' ); ?></th>
							<th><?php esc_html_e( 'Stock', 'shop-front' ); ?></th>
							<th><?php esc_html_e( 'Price', 'shop-front' ); ?></th>
							<th><?php esc_html_e( 'Type', 'shop-front' ); ?></th>
							<th class="text-right"><?php esc_html_e( 'Actions', 'shop-front' ); ?></th>
						</tr>
						<tbody>
						<?php
						while ( $product_query->have_posts() ) :
							$product_query->the_post();
							$product_id     = get_the_ID();
							$product        = wc_get_product( get_the_ID() );
							$msfc_wfm_thumb = get_the_post_thumbnail_url( $product_id, 'thumbnail' );
							if ( ! empty( $msfc_wfm_thumb ) ) {
								$msfc_wfm_thumb = $msfc_wfm_thumb;
							} else {
								$msfc_wfm_thumb = wc_placeholder_img_src( 'thumbnail' );
							}
							?>
							<tr class="single-product-item">
								<td>
									<label class="my-shop-front-checkbox">
										<input type="checkbox" name="" id="" class="my-shop-front-checkbox-input">
										<span class="my-shop-front-checkbox-back"></span>
										<span class="my-shop-front-tick">
											<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-check" viewBox="0 0 16 16">
												<path d="M10.97 4.97a.75.75 0 0 1 1.07 1.05l-3.99 4.99a.75.75 0 0 1-1.08.02L4.324 8.384a.75.75 0 1 1 1.06-1.06l2.094 2.093 3.473-4.425z"/>
											</svg>
										</span>
									</label>
								</td>
								<td data-title="<?php esc_attr_e( 'Image', 'shop-front' ); ?>">
									<img src="<?php echo esc_url( $msfc_wfm_thumb ); ?>" class="my-shop-front-thumb" alt="<?php echo esc_attr( get_the_title( $product_id ) ); ?>">
								</td>
								<td class="tbl-product-name" data-title="<?php esc_attr_e( 'Name', 'shop-front' ); ?>">
									<a href="<?php echo esc_url( get_the_permalink( $product_id ) ); ?>"><?php echo esc_attr( get_the_title( $product_id ) ); ?></a>
								</td>
								<td data-title="<?php esc_attr_e( 'Category', 'shop-front' ); ?>">
									<?php echo wp_kses_post( wc_get_product_category_list( $product_id, ', ', '', '' ) ); ?>
								</td>
								<td data-title="<?php esc_attr_e( 'Status', 'shop-front' ); ?>">
									<span class="msfc-badge msfc-badge-<?php echo esc_attr( msf_get_post_status_class( get_post_status( $product_id ) ) ); ?>">
										<?php echo esc_html( msf_get_post_status( get_post_status( $product_id ) ) ); ?>
									</span>
								</td>
								<td data-title="<?php esc_attr_e( 'SKU', 'shop-front' ); ?>">
									<?php
									if ( $product->get_sku() ) {
										echo esc_html( $product->get_sku() );
									} else {
										echo '<span class="no-sku">&ndash;</span>';
									}
									?>
								</td>
								<td data-title="<?php esc_attr_e( 'Stock', 'shop-front' ); ?>">
									<?php
									$stock_count = '';
									if ( $product->managing_stock() ) {
										$stock_count = '(' . $product->get_stock_quantity() . ')';
									}

									if ( $product->is_on_backorder() ) {
										echo '<span class="msfc-badge msfc-badge-warning">' . esc_html__( 'On backorder', 'shop-front' ) . '</span>';
									} elseif ( $product->is_in_stock() ) {
										echo '<span class="msfc-badge msfc-badge-success">' . esc_html__( 'In stock', 'shop-front' ) . esc_html( $stock_count ) . '</span>';
									} else {
										echo '<span class="msfc-badge msfc-badge-danger">' . esc_html__( 'Out of stock', 'shop-front' ) . '</span>';
									}
									?>
								</td>
								<td data-title="<?php esc_attr_e( 'Price', 'shop-front' ); ?>">
									<?php echo wp_kses_post( $product->get_price_html() ); ?>
								</td>
								<td data-title="<?php esc_attr_e( 'Type', 'shop-front' ); ?>">
									<?php msf_get_product_type( $product ); ?>
								</td>
								<td class="text-right" data-title="<?php esc_attr_e( 'Actions', 'shop-front' ); ?>">
									<div class="msfc-dropdown">
										<span class="msfc-dropdown-icon">
											<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-three-dots" viewBox="0 0 16 16">
												<path d="M3 9.5a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3m5 0a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3m5 0a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3"/>
											</svg>
										</span>
										<ul class="msfc-dropdown-menu">
											<li>
												<a href="<?php echo esc_url( get_permalink() ); ?>" target="_blank" class="dropdown-link"><?php esc_html_e( 'View', 'shop-front' ); ?></a>
											</li>
											<li>
												<a href="<?php echo esc_url( sprintf( msfc_get_navigation_url( 'edit-product' ) . '%s', $product_id ) ); ?>" class="dropdown-link"><?php esc_html_e( 'Edit', 'shop-front' ); ?></a>
											</li>
											<li>
												<form action="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>" method="POST">
													<?php wp_nonce_field( 'msfc_wfm_dlt_product_nonce_211', 'msfc_wfm_dlt_nonce' ); ?>
													<input type="hidden" name="id" value="<?php echo esc_attr( $product_id ); ?>">
													<input type="hidden" name="action" value="msfc_wfm_trash_product_action">
													<button type="submit" class="inline-button dropdown-link"><?php esc_html_e( 'Delete', 'shop-front' ); ?></button>
												</form>
											</li>
										</ul>
									</div>
								</td>
							</tr>
							<?php
						endwhile;
						wp_reset_postdata();
						?>
						</tbody>
					</thead>
				</table>
					<?php
					$total_pages = $product_query->max_num_pages;

					if ( $total_pages > 1 ) {
						$current_page_num = max( 1, $current_page );
						$total_products   = $product_query->found_posts;
						msf_get_template_part(
							'pagination',
							'',
							array(
								'total_items'  => $total_products,
								'total_pages'  => $total_pages,
								'current_page' => $current_page_num,
								'per_page'     => $products_data->per_page,
							)
						);
					}
				} else {
					msf_get_template_part(
						'not-found',
						'',
						array(
							'title' => esc_html__( 'No product found!', 'shop-front' ),
							'desc'  => esc_html__( 'There is nothing to display at the moment. Please try adding a product.', 'shop-front' ),
						)
					);
				}
				?>
			</div>
		</main>
		<?php do_action( 'msf_dashboard_content_after' ); ?>
	</div>
</div>
<?php do_action( 'msf_dashboard_wrapper_end' ); ?>