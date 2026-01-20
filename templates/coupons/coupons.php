<?php
/**
 * MSFC coupon List Page
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
								<input type="text" name="search" id="search" placeholder="<?php esc_attr_e( 'Search Coupon', 'shop-front' ); ?>" />
							</div>
						</form>
					</div>
					<div class="col-md-6 text-right">
						<a href="<?php echo esc_url( msfc_get_navigation_url( 'add-new-coupon' ) ); ?>" class="my-shop-front-button">
							<svg xmlns="http://www.w3.org/2000/svg" id="Outline" viewBox="0 0 24 24" width="24" height="24">
								<path d="M23,11H13V1a1,1,0,0,0-1-1h0a1,1,0,0,0-1,1V11H1a1,1,0,0,0-1,1H0a1,1,0,0,0,1,1H11V23a1,1,0,0,0,1,1h0a1,1,0,0,0,1-1V13H23a1,1,0,0,0,1-1h0A1,1,0,0,0,23,11Z"/>
							</svg>
							<?php esc_html_e( 'Add Coupon', 'shop-front' ); ?>
						</a>
					</div>
				</div>
			</div>
			<div class="msf-table-responsive">
				<?php
				$coupon_statuses = apply_filters( 'msf_coupon_listing_post_statuses', array( 'publish', 'draft', 'pending' ) );

				$posts_per_page = apply_filters( 'msf_coupons_per_page', 10 );
				$current_page   = ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : 1;

				$query = array(
					'posts_per_page' => $posts_per_page,
					'post_type'      => 'shop_coupon',
					'post_status'    => $coupon_statuses,
					'paged'          => $current_page,
					'orderby'        => 'date',
					'order'          => 'DESC',
				);

				$coupon_query = new WP_Query( $query );
				if ( $coupon_query->found_posts > 0 ) {
					?>
				<table class="my-shop-front-tbl my-shop-front-coupon-list-table">
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
							<th><?php esc_html_e( 'Code', 'shop-front' ); ?></th>
							<th><?php esc_html_e( 'Type', 'shop-front' ); ?></th>
							<th><?php esc_html_e( 'Amount', 'shop-front' ); ?></th>
							<th><?php esc_html_e( 'Description', 'shop-front' ); ?></th>
							<th><?php esc_html_e( 'Usage / Limit', 'shop-front' ); ?></th>
							<th><?php esc_html_e( 'Expiry Date', 'shop-front' ); ?></th>
							<th><?php esc_html_e( 'Status', 'shop-front' ); ?></th>
							<th class="text-right"><?php esc_html_e( 'Actions', 'shop-front' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php
						while ( $coupon_query->have_posts() ) :
							$coupon_query->the_post();
							$coupon_id = get_the_ID();
							$coupon    = new WC_Coupon( $coupon_id );
							?>
							<tr class="single-coupon-item">
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
								<td class="tbl-coupon-code" data-title="<?php esc_attr_e( 'Code', 'shop-front' ); ?>">
									<strong><?php echo esc_html( $coupon->get_code() ); ?></strong>
								</td>
								<td data-title="<?php esc_attr_e( 'Type', 'shop-front' ); ?>">
									<?php echo esc_html( wc_get_coupon_type( $coupon->get_discount_type() ) ); ?>
								</td>
								<td data-title="<?php esc_attr_e( 'Amount', 'shop-front' ); ?>">
									<?php
									if ( 'percent' === $coupon->get_discount_type() ) {
										echo esc_html( $coupon->get_amount() ) . '%';
									} else {
										echo wp_kses_post( wc_price( $coupon->get_amount() ) );
									}
									?>
								</td>
								<td data-title="<?php esc_attr_e( 'Description', 'shop-front' ); ?>">
									<?php
									$description = $coupon->get_description();
									if ( $description ) {
										echo esc_html( wp_trim_words( $description, 10, '...' ) );
									} else {
										echo '<span class="no-description">&ndash;</span>';
									}
									?>
								</td>
								<td data-title="<?php esc_attr_e( 'Usage / Limit', 'shop-front' ); ?>">
									<?php
									$usage_count = $coupon->get_usage_count();
									$usage_limit = $coupon->get_usage_limit();

									if ( $usage_limit ) {
										printf(
											/* translators: 1: usage count 2: usage limit */
											esc_html__( '%1$d / %2$d', 'shop-front' ),
											absint( $usage_count ),
											absint( $usage_limit )
										);
									} else {
										printf(
											/* translators: %d: usage count */
											esc_html__( '%d / &infin;', 'shop-front' ),
											absint( $usage_count )
										);
									}
									?>
								</td>
								<td data-title="<?php esc_attr_e( 'Expiry Date', 'shop-front' ); ?>">
									<?php
									$expiry_date = $coupon->get_date_expires();
									if ( $expiry_date ) {
										echo esc_html( $expiry_date->date_i18n( get_option( 'date_format' ) ) );
									} else {
										echo '<span class="no-expiry">&ndash;</span>';
									}
									?>
								</td>
								<td data-title="<?php esc_attr_e( 'Status', 'shop-front' ); ?>">
									<span class="msfc-badge msfc-badge-<?php echo esc_attr( msf_get_post_status_class( get_post_status( $coupon_id ) ) ); ?>">
										<?php echo esc_html( msf_get_post_status( get_post_status( $coupon_id ) ) ); ?>
									</span>
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
												<a href="<?php echo esc_url( sprintf( msfc_get_navigation_url( 'edit-coupon' ) . '%s', $coupon_id ) ); ?>" class="dropdown-link"><?php esc_html_e( 'Edit', 'shop-front' ); ?></a>
											</li>
											<li>
												<form action="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>" method="POST" class="delete-coupon-form">
													<?php wp_nonce_field( '_msf_delete_coupon_', 'msf_delete_coupon_nonce' ); ?>
													<input type="hidden" name="coupon_id" value="<?php echo esc_attr( $coupon_id ); ?>">
													<input type="hidden" name="action" value="msf_delete_coupon">
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
				</table>
					<?php
					$total_pages = $coupon_query->max_num_pages;

					if ( $total_pages > 1 ) {
						$current_page_num = max( 1, $current_page );
						$total_coupons    = $coupon_query->found_posts;
						msf_get_template_part(
							'pagination',
							'',
							array(
								'total_items'  => $total_coupons,
								'total_pages'  => $total_pages,
								'current_page' => $current_page_num,
								'per_page'     => $posts_per_page,
							)
						);
					}
				} else {
					msf_get_template_part(
						'not-found',
						'',
						array(
							'title' => esc_html__( 'No coupon found!', 'shop-front' ),
							'desc'  => esc_html__( 'There is nothing to display at the moment. Please try adding a coupon.', 'shop-front' ),
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
