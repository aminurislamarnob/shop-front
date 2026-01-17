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
								<input type="text" class="msf-form-control" id="product_category_name" name="product_category_name" placeholder="<?php echo esc_attr__( 'Product category name', 'shop-front' ); ?>" required>
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