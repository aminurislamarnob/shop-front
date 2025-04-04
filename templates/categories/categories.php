<?php
/**
 * MSFC category List Page
 * ***/

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
			<a href="<?php echo esc_url( msfc_get_navigation_url( 'add-new-category' ) ); ?>" class="my-shop-front-button">
				<?php esc_html_e( 'Add New Category', 'shop-front' ); ?>
			</a>
			<div class="msf-table-responsive" x-data="deleteCategoryHandler()">
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
								$product_categories   = new Categories();
								$categories_hierarchy = $product_categories->get_product_parent_and_subcategories_recursively();
								$product_categories->display_categories_recursively( $categories_hierarchy );
							?>
						</tbody>
					</thead>
				</table>
			</div>
		</main>
	</div>
</div>
<?php do_action( 'msf_dashboard_wrapper_end' ); ?>