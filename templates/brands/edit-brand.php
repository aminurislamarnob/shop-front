<?php
/**
 * MSFC brand edit page
 *
 * @package ShopFront
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use PluginizeLab\ShopFront\ProductBrand\Brands;

$brand_id = get_query_var( 'edit-brand' );
$brand_id = absint( $brand_id );

if ( ! $brand_id ) {
	wp_die( esc_html__( 'Invalid brand ID', 'shop-front' ) );
}

$brands_obj = new Brands();
$brand      = $brands_obj->get_brand_by_id( $brand_id );

if ( ! $brand || is_wp_error( $brand ) ) {
	wp_die( esc_html__( 'Brand not found', 'shop-front' ) );
}

// Get brand thumbnail.
$thumbnail_id  = absint( get_term_meta( $brand_id, 'thumbnail_id', true ) );
$thumbnail_url = '';
if ( $thumbnail_id ) {
	$thumbnail_url = wp_get_attachment_image_url( $thumbnail_id, 'thumbnail' );
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
						<form id="msfc-edit-brand">
							<div class="msf-form-group">
								<label for="product_brand_name"><?php esc_html_e( 'Brand Name', 'shop-front' ); ?> <span class="req"><?php esc_html_e( '*', 'shop-front' ); ?></span></label>
								<input type="text" class="msf-form-control" id="product_brand_name" name="product_brand_name" value="<?php echo esc_attr( $brand->name ); ?>" placeholder="<?php echo esc_attr__( 'Product brand name', 'shop-front' ); ?>">
							</div>
							<div class="msf-form-group">
								<label for="product_brand_slug"><?php esc_html_e( 'Slug', 'shop-front' ); ?></label>
								<input type="text" class="msf-form-control" id="product_brand_slug" name="product_brand_slug" placeholder="<?php echo esc_attr__( 'Brand slug', 'shop-front' ); ?>" value="<?php echo esc_attr( $brand->slug ); ?>">
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
											// Don't allow selecting itself as parent.
											if ( $product_brand->term_id === $brand->term_id ) {
												continue;
											}
											$selected = ( $brand->parent === $product_brand->term_id ) ? 'selected' : '';
											?>
											<option value="<?php echo esc_attr( $product_brand->slug ); ?>" <?php echo esc_attr( $selected ); ?>><?php echo esc_html( $product_brand->name ); ?></option>
											<?php
										}
									}
									?>
								</select>
							</div>
						<div class="msf-form-group">
							<label for="product_brand_description"><?php esc_html_e( 'Brand Description', 'shop-front' ); ?></label>
							<textarea class="msf-form-control" id="product_brand_description" name="product_brand_description" placeholder="<?php echo esc_attr__( 'Product brand description', 'shop-front' ); ?>" rows="3"><?php echo esc_textarea( $brand->description ); ?></textarea>
						</div>
						<div class="msf-form-group">
							<label for="product_brand_thumbnail_id"><?php esc_html_e( 'Brand Image', 'shop-front' ); ?></label>
							<input type="hidden" id="product_brand_thumbnail_id" name="product_brand_thumbnail_id" value="<?php echo esc_attr( $thumbnail_id ); ?>">
							<input type="hidden" id="product_brand_thumbnail_url" name="product_brand_thumbnail_url" value="<?php echo esc_url( $thumbnail_url ); ?>">
							<div id="brand-single-image" class="image-drop-container<?php echo esc_attr( $thumbnail_id ? ' image-drop-bg' : '' ); ?>">
								<div id="brand_thumb_img" class="preview-image">
									<?php if ( $thumbnail_url ) : ?>
										<img src="<?php echo esc_url( $thumbnail_url ); ?>" alt="<?php esc_attr_e( 'Brand thumbnail', 'shop-front' ); ?>">
									<?php endif; ?>
								</div>
								<div class="image-drop-text">
									<i class="las la-image"></i>
									<?php if ( $thumbnail_id ) : ?>
										<span><?php esc_html_e( 'Remove Image', 'shop-front' ); ?></span>
									<?php else : ?>
										<span><?php esc_html_e( 'Upload Image', 'shop-front' ); ?></span>
									<?php endif; ?>
								</div>
							</div>
						</div>
						<div class="msf-form-submission-group">
								<?php wp_nonce_field( '_msfc_edit_product_brand_', 'msfc_edit_product_brand_nonce' ); ?>
								<input type="hidden" name="action" value="msfc_edit_product_brand">
								<input type="hidden" name="brand_id" value="<?php echo esc_attr( $brand->term_id ); ?>">
								<div class="msf-button-group">
									<button class="my-shop-front-button" name="save_product_brand" type="submit"><?php esc_html_e( 'Update', 'shop-front' ); ?></button>
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