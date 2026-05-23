<?php
/**
 * StoreSuite tag List Page
 *
 * @package StoreSuite
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use PluginizeLab\StoreSuite\ProductTag\Tags;

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
			<div class="storesuite-table-header-part">
				<div class="row">
					<div class="col-md-6">
					<form action="" method="get">
						<div class="storesuite-table-search-input">
							<div class="storesuite-table-search-icon">
								<svg xmlns="http://www.w3.org/2000/svg" id="Outline" viewBox="0 0 24 24" width="24" height="24">
									<path d="M23.707,22.293l-5.969-5.969a10.016,10.016,0,1,0-1.414,1.414l5.969,5.969a1,1,0,0,0,1.414-1.414ZM10,18a8,8,0,1,1,8-8A8.009,8.009,0,0,1,10,18Z"/>
								</svg>
							</div>
							<input type="text" name="search_by" id="search_by" placeholder="<?php esc_attr_e( 'Search Tag', 'storesuite' ); ?>" value="<?php echo esc_attr( isset( $_GET['search_by'] ) ? sanitize_text_field( wp_unslash( $_GET['search_by'] ) ) : '' ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only search; no state change. ?>" />
							</div>
						</form>
					</div>
					<div class="col-md-6 text-right">
						<a href="<?php echo esc_url( storesuite_get_navigation_url( 'add-new-tag' ) ); ?>" class="my-storesuite-button">
							<svg xmlns="http://www.w3.org/2000/svg" id="Outline" viewBox="0 0 24 24" width="24" height="24">
								<path d="M23,11H13V1a1,1,0,0,0-1-1h0a1,1,0,0,0-1,1V11H1a1,1,0,0,0-1,1H0a1,1,0,0,0,1,1H11V23a1,1,0,0,0,1,1h0a1,1,0,0,0,1-1V13H23a1,1,0,0,0,1-1h0A1,1,0,0,0,23,11Z"/>
							</svg>
							<?php esc_html_e( 'Add Tag', 'storesuite' ); ?>
						</a>
					</div>
				</div>
			</div>
			<?php
			$current_page  = ( get_query_var( 'paged' ) ) ? absint( get_query_var( 'paged' ) ) : 1;
			$tags_per_page = apply_filters( 'storesuite_tags_per_page', 10 );
			$search_term   = isset( $_GET['search_by'] ) ? sanitize_text_field( wp_unslash( $_GET['search_by'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only search; no state change.
			$product_tags  = new Tags();
			$tags_data     = $product_tags->get_paginated_tags( $tags_per_page, $current_page, $search_term );
			?>
			<div class="storesuite-table-responsive">
				<table class="my-storesuite-tbl my-storesuite-product-list-table my-storesuite-tags-table">
					<thead>
						<tr>
							<th width="210"><?php echo esc_html__( 'Name', 'storesuite' ); ?></th>
							<th><?php echo esc_html__( 'Description', 'storesuite' ); ?></th>
							<th width="210"><?php echo esc_html__( 'Slug', 'storesuite' ); ?></th>
							<th width="70"><?php echo esc_html__( 'Count', 'storesuite' ); ?></th>
							<th class="text-right"><?php echo esc_html__( 'Actions', 'storesuite' ); ?></th>
						</tr>
						<tbody>
						<?php
						if ( empty( $tags_data->tags ) ) {
							echo '<tr id="tag-row-not-found"><td colspan="5">';
							storesuite_get_template_part(
								'not-found',
								'',
								array(
									'title' => esc_html__( 'No tag found!', 'storesuite' ),
									'desc'  => esc_html__( 'There is nothing to display at the moment. Please try adding a tag.', 'storesuite' ),
								)
							);
							echo '</td></tr>';
						} else {
							foreach ( $tags_data->tags as $product_tag ) {
								?>
						<tr id="tag-row-<?php echo esc_attr( $product_tag->term_id ); ?>">
							<td><a href="<?php echo esc_url( sprintf( storesuite_get_navigation_url( 'edit-tag' ) . '%s', $product_tag->term_id ) ); ?>"><?php echo esc_html( $product_tag->name ); ?></a></td>
							<td><?php echo esc_html( wp_trim_words( $product_tag->description, '9', '...' ) ); ?></td>
							<td><?php echo esc_html( $product_tag->slug ); ?></td>
							<td><?php echo esc_html( $product_tag->count ); ?></td>
							<td class="text-right" data-title="<?php esc_attr_e( 'Actions', 'storesuite' ); ?>">
								<div class="storesuite-dropdown">
									<span class="storesuite-dropdown-icon">
										<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-three-dots" viewBox="0 0 16 16">
											<path d="M3 9.5a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3m5 0a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3m5 0a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3"/>
										</svg>
									</span>
									<ul class="storesuite-dropdown-menu">
										<li>
											<a href="<?php echo esc_url( get_category_link( $product_tag->term_id ) ); ?>" class="dropdown-link">
												<?php echo esc_html__( 'View', 'storesuite' ); ?>
											</a>
										</li>
										<li>
											<a href="<?php echo esc_url( sprintf( storesuite_get_navigation_url( 'edit-tag' ) . '%s', $product_tag->term_id ) ); ?>" class="dropdown-link"><?php echo esc_html__( 'Edit', 'storesuite' ); ?></a>
										</li>
										<li>
											<button type="button" class="inline-button dropdown-link storesuite-delete-tag" data-tag-id="<?php echo esc_attr( $product_tag->term_id ); ?>">
												<?php echo esc_html__( 'Delete', 'storesuite' ); ?>
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
				<?php
				if ( $tags_data->max_num_pages > 1 ) {
					storesuite_get_template_part(
						'pagination',
						'',
						array(
							'total_items'  => $tags_data->total,
							'total_pages'  => $tags_data->max_num_pages,
							'current_page' => $current_page,
							'per_page'     => $tags_per_page,
						)
					);
				}
				?>
			</div>
		</main>
	</div>
</div>
<?php do_action( 'storesuite_dashboard_wrapper_end' ); ?>