<?php
/**
 * MSFC category add page
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
			<div class="row">
				<div class="col-md-6">
					<div class="msf-card">
						<form id="msfc-add-category">
						<div class="msf-form-group">
							<label for="product_category_name"><?php esc_html_e( 'Category Name', 'shop-front' ); ?> <span class="req"><?php esc_html_e( '*', 'shop-front' ); ?></span></label>
						<input type="text" class="msf-form-control" id="product_category_name" name="product_category_name" placeholder="<?php echo esc_attr__( 'Product category name', 'shop-front' ); ?>">
						</div>
						<div class="msf-form-group">
							<label for="product_category_slug"><?php esc_html_e( 'Slug', 'shop-front' ); ?></label>
							<input type="text" class="msf-form-control" id="product_category_slug" name="product_category_slug" placeholder="<?php echo esc_attr__( 'Category slug', 'shop-front' ); ?>">
							<small class="msf-form-text"><?php esc_html_e( 'The "slug" is the URL-friendly version of the name. It is usually all lowercase and contains only letters, numbers, and hyphens.', 'shop-front' ); ?></small>
						</div>
							<div class="msf-form-group">
								<label for="product_parent_category"><?php esc_html_e( 'Select Parent Category', 'shop-front' ); ?></label>
								<select class="msf-form-control" id="product_parent_category" name="product_parent_category">
									<option value=""><?php esc_html_e( '-- Select parent category --', 'shop-front' ); ?></option>
									<?php
									$product_categories = get_categories(
										array(
											'orderby'    => 'name',
											'order'      => 'ASC',
											'taxonomy'   => 'product_cat',
											'hide_empty' => false,
										)
									);
									foreach ( $product_categories as $product_category ) {
										?>
										<option value="<?php echo esc_attr( $product_category->slug ); ?>"><?php echo esc_html( $product_category->name ); ?></option>
									<?php } ?>
								</select>
							</div>
						<div class="msf-form-group">
							<label for="product_category_description"><?php esc_html_e( 'Category Description', 'shop-front' ); ?></label>
							<textarea class="msf-form-control" id="product_category_description" name="product_category_description" placeholder="<?php echo esc_attr__( 'Product category description', 'shop-front' ); ?>" rows="3"></textarea>
						</div>
						<div class="msf-form-group">
							<label for="display_type"><?php esc_html_e( 'Display Type', 'shop-front' ); ?></label>
							<select class="msf-form-control" id="display_type" name="display_type">
								<option value=""><?php esc_html_e( 'Default', 'woocommerce' ); ?></option>
								<option value="products"><?php esc_html_e( 'Products', 'woocommerce' ); ?></option>
								<option value="subcategories"><?php esc_html_e( 'Subcategories', 'woocommerce' ); ?></option>
								<option value="both"><?php esc_html_e( 'Both', 'woocommerce' ); ?></option>
							</select>
						</div>
						<div class="msf-form-group">
							<label for="product_category_thumbnail_id"><?php esc_html_e( 'Category Image', 'shop-front' ); ?></label>
							<input type="hidden" id="product_category_thumbnail_id" name="product_category_thumbnail_id" value="">
							<input type="hidden" id="product_category_thumbnail_url" name="product_category_thumbnail_url" value="">
							<div id="category-single-image" class="image-drop-container">
								<div id="category_thumb_img" class="preview-image"></div>
								<div class="image-drop-text">
									<i class="las la-image"></i>
									<span><?php esc_html_e( 'Upload Image', 'shop-front' ); ?></span>
								</div>
							</div>
						</div>
						<div class="msf-form-submission-group">
								<?php wp_nonce_field( '_msfc_add_product_category_', 'msfc_add_product_category_nonce' ); ?>
								<input type="hidden" name="action" value="msfc_add_product_category">
								<div class="msf-button-group">
									<button class="my-shop-front-button" name="save_product_category" type="submit"><?php esc_html_e( 'Submit', 'shop-front' ); ?></button>
									<a href="<?php echo esc_url( msfc_get_navigation_url( 'categories' ) ); ?>" class="my-shop-front-button my-shop-front-button-light"><?php esc_html_e( 'Back', 'shop-front' ); ?></a>
								</div>
							</div>
						</form>
					</div>
				</div>
			</div>
		</main>
	</div>
</div>
<?php do_action( 'msf_dashboard_wrapper_end' ); ?>