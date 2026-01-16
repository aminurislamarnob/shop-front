<?php
/**
 * MSFC tag List Page
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
								<input type="text" name="search" id="search" placeholder="<?php esc_attr_e( 'Search Tag', 'shop-front' ); ?>" />
							</div>
						</form>
					</div>
					<div class="col-md-6 text-right">
						<a href="<?php echo esc_url( msfc_get_navigation_url( 'add-new-tag' ) ); ?>" class="my-shop-front-button">
							<svg xmlns="http://www.w3.org/2000/svg" id="Outline" viewBox="0 0 24 24" width="24" height="24">
								<path d="M23,11H13V1a1,1,0,0,0-1-1h0a1,1,0,0,0-1,1V11H1a1,1,0,0,0-1,1H0a1,1,0,0,0,1,1H11V23a1,1,0,0,0,1,1h0a1,1,0,0,0,1-1V13H23a1,1,0,0,0,1-1h0A1,1,0,0,0,23,11Z"/>
							</svg>
							<?php esc_html_e( 'Add Tag', 'shop-front' ); ?>
						</a>
					</div>
				</div>
			</div>
			<div class="msf-table-responsive" x-data="deleteTagHandler()">
				<table class="my-shop-front-tbl my-shop-front-product-list-table">
					<thead>
						<tr>
							<th width="210"><?php echo esc_html__( 'Name', 'shop-front' ); ?></th>
							<th><?php echo esc_html__( 'Description', 'shop-front' ); ?></th>
							<th width="210"><?php echo esc_html__( 'Slug', 'shop-front' ); ?></th>
							<th width="70"><?php echo esc_html__( 'Count', 'shop-front' ); ?></th>
							<th class="text-right"><?php echo esc_html__( 'Action', 'shop-front' ); ?></th>
						</tr>
						<tbody>
						<?php
						$product_tags = get_terms(
							array(
								'taxonomy'   => 'product_tag',
								'hide_empty' => false,
							)
						);

						if ( empty( $product_tags ) ) {
							echo '<tr id="tag-row-not-found"><td colspan="5">';
							msf_get_template_part(
								'not-found',
								'',
								array(
									'title' => esc_html__( 'No tag found!', 'shop-front' ),
									'desc'  => esc_html__( 'There is nothing to display at the moment. Please try adding a tag.', 'shop-front' ),
								)
							);
							echo '</td></tr>';
						} else {
							foreach ( $product_tags as $product_tag ) {
								?>
						<tr id="tag-row-<?php echo esc_attr( $product_tag->term_id ); ?>">
							<td><?php echo esc_html( $product_tag->name ); ?></td>
							<td><?php echo esc_html( wp_trim_words( $product_tag->description, '9', '...' ) ); ?></td>
							<td><?php echo esc_html( $product_tag->slug ); ?></td>
							<td><?php echo esc_html( $product_tag->count ); ?></td>
							<td class="text-right" data-title="<?php esc_attr_e( 'Actions', 'shop-front' ); ?>">
								<div class="msfc-dropdown">
									<span class="msfc-dropdown-icon">
										<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-three-dots" viewBox="0 0 16 16">
											<path d="M3 9.5a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3m5 0a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3m5 0a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3"/>
										</svg>
									</span>
									<ul class="msfc-dropdown-menu">
										<li>
											<a href="<?php echo esc_url( get_category_link( $product_tag->term_id ) ); ?>" class="dropdown-link">
												<?php echo esc_html__( 'View', 'shop-front' ); ?>
											</a>
										</li>
										<li>
											<a href="<?php echo esc_url( sprintf( msfc_get_navigation_url( 'edit-tag' ) . '%s', $product_tag->term_id ) ); ?>" class="dropdown-link"><?php echo esc_html__( 'Edit', 'shop-front' ); ?></a>
										</li>
										<li>
											<button @click="deleteTag(<?php echo esc_attr( $product_tag->term_id ); ?>)" type="button" class="inline-button dropdown-link">
												<?php echo esc_html__( 'Delete', 'shop-front' ); ?>
											</button>
										</li>
									</ul>
								</div>
							</td>
						</tr>
								<?php
							}
						}
						?>
						</tbody>
					</thead>
				</table>
			</div>
		</main>
	</div>
</div>
<?php do_action( 'msf_dashboard_wrapper_end' ); ?>