<?php
/**
 * MSFC brand add page
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
						<form id="msfc-add-brand">
						<div class="msf-form-group">
							<label for="product_brand_name"><?php esc_html_e( 'Brand Name', 'shop-front' ); ?> <span class="req"><?php esc_html_e( '*', 'shop-front' ); ?></span></label>
							<input type="text" class="msf-form-control" id="product_brand_name" name="product_brand_name" placeholder="<?php echo esc_attr__( 'Product brand name', 'shop-front' ); ?>" required>
						</div>
						<div class="msf-form-group">
							<label for="product_brand_slug"><?php esc_html_e( 'Slug', 'shop-front' ); ?></label>
							<input type="text" class="msf-form-control" id="product_brand_slug" name="product_brand_slug" placeholder="<?php echo esc_attr__( 'Brand slug', 'shop-front' ); ?>">
							<small class="msf-form-text"><?php esc_html_e( 'The "slug" is the URL-friendly version of the name. It is usually all lowercase and contains only letters, numbers, and hyphens.', 'shop-front' ); ?></small>
						</div>
							<div class="msf-form-group">
								<label for="product_parent_brand"><?php esc_html_e( 'Parent Brand', 'shop-front' ); ?></label>
								<select class="msf-form-control" id="product_parent_brand" name="product_parent_brand">
									<option value=""><?php esc_html_e( 'Select parent brand', 'shop-front' ); ?></option>
									<?php
									$product_brands = get_terms(
										array(
											'taxonomy'   => 'product_brand',
											'hide_empty' => false,
											'orderby'    => 'name',
											'order'      => 'ASC',
										)
									);
									if ( ! empty( $product_brands ) && ! is_wp_error( $product_brands ) ) {
										foreach ( $product_brands as $product_brand ) {
											?>
											<option value="<?php echo esc_attr( $product_brand->slug ); ?>"><?php echo esc_html( $product_brand->name ); ?></option>
											<?php
										}
									}
									?>
								</select>
							</div>
						<div class="msf-form-group">
							<label for="product_brand_description"><?php esc_html_e( 'Brand Description', 'shop-front' ); ?></label>
							<textarea class="msf-form-control" id="product_brand_description" name="product_brand_description" placeholder="<?php echo esc_attr__( 'Product brand description', 'shop-front' ); ?>" rows="3"></textarea>
						</div>
						<div class="msf-form-group">
							<label for="product_brand_thumbnail_id"><?php esc_html_e( 'Brand Image', 'shop-front' ); ?></label>
							<input type="hidden" id="product_brand_thumbnail_id" name="product_brand_thumbnail_id" value="">
							<input type="hidden" id="product_brand_thumbnail_url" name="product_brand_thumbnail_url" value="">
							<div id="brand-single-image" class="image-drop-container">
								<div id="brand_thumb_img" class="preview-image"></div>
								<div class="image-drop-text">
									<i class="las la-image"></i>
									<span><?php esc_html_e( 'Upload Image', 'shop-front' ); ?></span>
								</div>
							</div>
						</div>
						<div class="msf-form-submission-group">
								<?php wp_nonce_field( '_msfc_add_product_brand_', 'msfc_add_product_brand_nonce' ); ?>
								<input type="hidden" name="action" value="msfc_add_product_brand">
								<div class="msf-button-group">
									<button class="my-shop-front-button" name="save_product_brand" type="submit"><?php esc_html_e( 'Submit', 'shop-front' ); ?></button>
									<a href="<?php echo esc_url( msfc_get_navigation_url( 'brands' ) ); ?>" class="my-shop-front-button my-shop-front-button-light"><?php esc_html_e( 'Back', 'shop-front' ); ?></a>
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