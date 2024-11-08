<?php
/**
 * MSFC tag List Page
 * ***/

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use PluginizeLab\ShopFront\ProductCategory\Categories;

do_action( 'msf_dashboard_wrapper_start' );
?>
<div class="my-shop-front-container">
	<div class="row">
		<div class="col-md-2">
			<?php do_action( 'msf_dashboard_navigation' ); ?>
		</div>
		<div class="col-md-10">
			<?php do_action( 'msf_dashboard_content_before' ); ?>
			<a href="<?php echo esc_url( msfc_get_navigation_url( 'add-new-tag' ) ); ?>" class="my-shop-front-button">
				<?php esc_html_e( 'Add New Tag', 'shop-front' ); ?>
			</a>
			<div class="my-shop-front-content" x-data="deleteCategoryHandler()">
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

						if ( ! is_wp_error( $product_tags ) && ! empty( $product_tags ) ) {
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
											<button @click="deleteCategory(<?php echo esc_attr( $product_tag->term_id ); ?>)" type="button" class="inline-button dropdown-link">
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
		</div>
	</div>
</div>
<?php do_action( 'msf_dashboard_wrapper_end' ); ?>