<?php
/**
 * MSFC WFM Product List
 * ***/

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

do_action( 'msf_dashboard_wrapper_start' );
?>
<div class="my-shop-front-container">
	<div class="row">
		<div class="col-md-3">
			<?php do_action( 'msf_dashboard_navigation' ); ?>
		</div>
		<div class="col-md-9">
			<?php do_action( 'msf_dashboard_content_before' ); ?>
			<a href="<?php echo esc_url( get_home_url() . '/add-new-product/' ); ?>" class="my-shop-front-button">
				<?php esc_html_e( 'Add New Product', 'shop-front' ); ?>
			</a>
			<div class="my-shop-front-content">
				<?php
				$product_statuses = apply_filters( 'msf_product_listing_post_statuses', array( 'publish', 'draft', 'pending', 'future' ) );
				$stock_statuses   = apply_filters( 'msf_product_stock_statuses', array( 'instock', 'outofstock' ) );
				$product_types    = apply_filters( 'msf_product_types', array( 'simple' => __( 'Simple', 'shop-front' ) ) );

				$posts_per_page = -1;
				$query          = array(
					'posts_per_page' => $posts_per_page,
					'post_type'      => 'product',
					'post_status'    => $product_statuses,
					// Pass filter here later
				);

				$product_query = new WP_Query( $query );
				if ( $product_query->found_posts > 0 ) {
					?>
				<table class="my-shop-front-tbl my-shop-front-product-list-table shop_table">
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
									<div class="product-list-action">
										<a href="<?php echo esc_url( get_home_url() . '/msfc-product-details/' . $product_id . '/' ); ?>" class="woocommerce-button view"><?php esc_html_e( 'View', 'shop-front' ); ?></a>
										<a href="<?php echo esc_url( get_home_url() . '/msfc-edit-product/' . $product_id . '/' ); ?>" class="woocommerce-button edit"><?php esc_html_e( 'Edit', 'shop-front' ); ?></a>
										<form class="d-inline" action="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>" method="POST">
											<?php wp_nonce_field( 'msfc_wfm_dlt_product_nonce_211', 'msfc_wfm_dlt_nonce' ); ?>
											<input type="hidden" name="id" value="<?php echo esc_attr( $product_id ); ?>">
											<input type="hidden" name="action" value="msfc_wfm_trash_product_action">
											<button type="submit" class="inline-button delete"><?php esc_html_e( 'Delete', 'shop-front' ); ?></button>
										</form>
									</div>
								</td>
								<td data-title="<?php esc_attr_e( 'Category', 'shop-front' ); ?>">
									<?php echo wp_kses_post( wc_get_product_category_list( $product_id, ', ', '', '' ) ); ?>
								</td>
								<td data-title="<?php esc_attr_e( 'Status', 'shop-front' ); ?>">
									<?php echo esc_html( msf_get_post_status( get_post_status( $product_id ) ) ); ?>
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
									echo esc_html( $product->get_stock_status() );
									if ( $product->managing_stock() ) {
										echo ' &times; ' . esc_html( $product->get_stock_quantity() );
									}
									?>
								</td>
								<td data-title="<?php esc_attr_e( 'Price', 'shop-front' ); ?>">
									<?php echo wp_kses_post( $product->get_price_html() ); ?>
								</td>
								<td data-title="<?php esc_attr_e( 'Type', 'shop-front' ); ?>">
									<?php msf_get_product_type( $product ); ?>
								</td>
							</tr>
							<?php
						endwhile;
						wp_reset_postdata();
						?>
						</tbody>
					</thead>
				</table>
				<?php } else { ?>
					<div class="alert alert-warning rounded-md" role="alert">
						<svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
						<span><?php esc_html_e( 'No product found!', 'shop-front' ); ?></span>
					</div>
				<?php } ?>
			</div>
			<?php do_action( 'msf_dashboard_content_after' ); ?>
		</div>
	</div>
</div>
<?php do_action( 'msf_dashboard_wrapper_end' ); ?>