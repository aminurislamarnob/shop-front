<?php
/**
 * MSFC product List Page
 *
 * @package ShopFront
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
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
								<input type="text" name="search" id="search" placeholder="<?php esc_attr_e( 'Search Product', 'shop-front' ); ?>" />
							</div>
						</form>
					</div>
					<div class="col-md-6 text-right">
						<a href="<?php echo esc_url( msfc_get_navigation_url( 'add-new-product' ) ); ?>" class="my-shop-front-button">
							<svg xmlns="http://www.w3.org/2000/svg" id="Outline" viewBox="0 0 24 24" width="24" height="24">
								<path d="M23,11H13V1a1,1,0,0,0-1-1h0a1,1,0,0,0-1,1V11H1a1,1,0,0,0-1,1H0a1,1,0,0,0,1,1H11V23a1,1,0,0,0,1,1h0a1,1,0,0,0,1-1V13H23a1,1,0,0,0,1-1h0A1,1,0,0,0,23,11Z"/>
							</svg>
							<?php esc_html_e( 'Add Product', 'shop-front' ); ?>
						</a>
					</div>
				</div>
			</div>
			<div class="msf-table-responsive">
				<?php
				$product_statuses = apply_filters( 'msf_product_listing_post_statuses', array( 'publish', 'draft', 'pending', 'future' ) );
				$stock_statuses   = apply_filters( 'msf_product_stock_statuses', array( 'instock', 'outofstock' ) );
				$product_types    = apply_filters( 'msf_product_types', array( 'simple' => __( 'Simple', 'shop-front' ) ) );

				$posts_per_page = apply_filters( 'msf_products_per_page', 10 );
				$current_page   = ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : 1; // Get current page number, default to 1.

				$query = array(
					'posts_per_page' => $posts_per_page,
					'post_type'      => 'product',
					'post_status'    => $product_statuses,
					'paged'          => $current_page,
				);

				$product_query = new WP_Query( $query );
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
					$base_url         = msfc_get_navigation_url( 'products' );
					$current_page_num = max( 1, $current_page );
					$total_pages      = $product_query->max_num_pages;
					$total_products   = $product_query->found_posts;
					$start_product    = ( $current_page_num - 1 ) * $posts_per_page + 1;
					$end_product      = min( $total_products, $current_page_num * $posts_per_page );

					if ( $total_pages > 1 ) {
						$big_num    = 999999999;
						$page_links = paginate_links(
							array(
								'base'      => str_replace( $big_num, '%#%', esc_url( get_pagenum_link( $big_num ) ) ),
								'format'    => '?page=%#%',
								'add_args'  => false,
								'current'   => $current_page_num,
								'total'     => $total_pages,
								'type'      => 'array', // list.
								'prev_text' => '&larr;',
								'next_text' => '&rarr;',
								'end_size'  => 3,
								'mid_size'  => 3,
							)
						);

						echo '<div class="msfc-pagination-wrap">';

						echo '<div class="msfc-result-text">';
						/* translators: %1$s: Start Product, %2$s: End Product, %3$s: Total Products */
						printf( esc_html__( 'Showing %1$s to %2$s of %3$s', 'shop-front' ), esc_html( $start_product ), esc_html( $end_product ), esc_html( $total_products ) );
						echo '</div>';

						if ( ! empty( $page_links ) ) {
							echo '<ul class="msfc-pagination"><li>';
							echo wp_kses_post( join( '</li><li>', $page_links ) );
							echo '</li></ul>';
						}
						echo '</div>';
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