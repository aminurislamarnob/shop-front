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
						<div x-data="brandEditFormHandler(<?php echo esc_attr( $brand->term_id ); ?>)">
							<form id="msfc-edit-brand" @submit.prevent="handleBrandEditSubmission">
								<div class="msf-form-group">
									<label for="product_brand_name"><?php esc_html_e( 'Brand Name', 'shop-front' ); ?> <span class="req"><?php esc_html_e( '*', 'shop-front' ); ?></span></label>
									<input type="text" class="msf-form-control" id="product_brand_name" name="product_brand_name" value="<?php echo esc_attr( $brand->name ); ?>" placeholder="<?php echo esc_attr__( 'Product brand name', 'shop-front' ); ?>" aria-required="true">
								</div>
								<div class="msf-form-group">
									<label for="product_parent_brand"><?php esc_html_e( 'Parent Brand', 'shop-front' ); ?></label>
									<select class="msf-form-control" id="product_parent_brand" name="product_parent_brand">
										<option value=""><?php esc_html_e( 'Select parent brand', 'shop-front' ); ?></option>
										<?php
										$product_brands = get_terms(
											array(
												'taxonomy' => 'product_brand',
												'hide_empty' => false,
												'orderby'  => 'name',
												'order'    => 'ASC',
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
								<div class="msf-form-submission-group">
									<?php wp_nonce_field( '_msfc_edit_product_brand_', 'msfc_edit_product_brand_nonce' ); ?>
									<input type="hidden" name="action" value="msfc_edit_product_brand">
									<input type="hidden" name="brand_id" value="<?php echo esc_attr( $brand->term_id ); ?>">
									<div class="msf-button-group">
										<button class="my-shop-front-button" name="save_product_brand" type="submit"><?php esc_html_e( 'Update', 'shop-front' ); ?></button>
										<a href="<?php echo esc_url( msfc_get_navigation_url( 'brands' ) ); ?>" class="my-shop-front-button my-shop-front-button-light">Back</a>
									</div>
								</div>
							</form>
							<!-- Success and Error Messages -->
							<div x-show="message" x-text="message" class="alert"></div>
							<div x-show="error" x-text="error" class="alert alert-danger"></div>
						</div>
					</div>
				</div>
			</div>
		</main>
	</div>
</div>
<?php do_action( 'msf_dashboard_wrapper_end' ); ?>