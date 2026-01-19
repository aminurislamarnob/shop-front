<?php
/**
 * MSFC category edit page
 *
 * @package ShopFront
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$category_id = get_query_var( 'edit-category' );
$category_id = absint( $category_id );

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
						<?php
						if ( $category_id ) {
							$category = get_term( $category_id, 'product_cat' );

							if ( ! $category || is_wp_error( $category ) ) {
								echo '<div class="alert alert-danger">' . esc_html__( 'Category not found.', 'shop-front' ) . '</div>';
								return;
							}

							// Get category thumbnail.
							$thumbnail_id  = absint( get_term_meta( $category_id, 'thumbnail_id', true ) );
							$thumbnail_url = '';
							if ( $thumbnail_id ) {
								$thumbnail_url = wp_get_attachment_image_url( $thumbnail_id, 'thumbnail' );
							}

							// Get display type.
							$display_type = get_term_meta( $category_id, 'display_type', true );
							?>
						<form id="msfc-edit-category">
							<div class="msf-form-group">
								<label for="product_category_name"><?php esc_html_e( 'Category Name', 'shop-front' ); ?> <span class="req"><?php esc_html_e( '*', 'shop-front' ); ?></span></label>
								<input type="text" class="msf-form-control" id="product_category_name" name="product_category_name" placeholder="<?php echo esc_attr__( 'Product category name', 'shop-front' ); ?>" value="<?php echo esc_attr( $category->name ); ?>" required>
							</div>
							<div class="msf-form-group">
								<label for="product_category_slug"><?php esc_html_e( 'Slug', 'shop-front' ); ?></label>
								<input type="text" class="msf-form-control" id="product_category_slug" name="product_category_slug" placeholder="<?php echo esc_attr__( 'Category slug', 'shop-front' ); ?>" value="<?php echo esc_attr( $category->slug ); ?>">
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
										if ( $product_category->term_id !== $category->term_id ) { // Prevent setting itself as parent.
											?>
											<option value="<?php echo esc_attr( $product_category->slug ); ?>" <?php selected( $category->parent, $product_category->term_id ); ?>>
												<?php echo esc_html( $product_category->name ); ?>
											</option>
											<?php
										}
									}
									?>
								</select>
							</div>
							<div class="msf-form-group">
								<label for="product_category_description"><?php esc_html_e( 'Category Description', 'shop-front' ); ?></label>
								<textarea class="msf-form-control" id="product_category_description" name="product_category_description" placeholder="<?php echo esc_attr__( 'Product category description', 'shop-front' ); ?>" rows="3"><?php echo esc_textarea( $category->description ); ?></textarea>
							</div>
							<div class="msf-form-group">
								<label for="display_type"><?php esc_html_e( 'Display Type', 'shop-front' ); ?></label>
								<select class="msf-form-control" id="display_type" name="display_type">
									<option value="" <?php selected( $display_type, '' ); ?>><?php esc_html_e( 'Default', 'woocommerce' ); ?></option>
									<option value="products" <?php selected( $display_type, 'products' ); ?>><?php esc_html_e( 'Products', 'woocommerce' ); ?></option>
									<option value="subcategories" <?php selected( $display_type, 'subcategories' ); ?>><?php esc_html_e( 'Subcategories', 'woocommerce' ); ?></option>
									<option value="both" <?php selected( $display_type, 'both' ); ?>><?php esc_html_e( 'Both', 'woocommerce' ); ?></option>
								</select>
							</div>
							<div class="msf-form-group">
								<label for="product_category_thumbnail_id"><?php esc_html_e( 'Category Image', 'shop-front' ); ?></label>
								<input type="hidden" id="product_category_thumbnail_id" name="product_category_thumbnail_id" value="<?php echo esc_attr( $thumbnail_id ); ?>">
								<input type="hidden" id="product_category_thumbnail_url" name="product_category_thumbnail_url" value="<?php echo esc_url( $thumbnail_url ); ?>">
								<div id="category-single-image" class="image-drop-container<?php echo esc_attr( $thumbnail_id ? ' image-drop-bg' : '' ); ?>">
									<div id="category_thumb_img" class="preview-image">
										<?php if ( $thumbnail_url ) : ?>
											<img src="<?php echo esc_url( $thumbnail_url ); ?>" alt="<?php esc_attr_e( 'Category thumbnail', 'shop-front' ); ?>">
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
								<?php wp_nonce_field( '_msfc_edit_product_category_', 'msfc_edit_product_category_nonce' ); ?>
								<input type="hidden" name="action" value="msfc_edit_product_category">
								<input type="hidden" name="category_id" value="<?php echo esc_attr( $category->term_id ); ?>">
								<div class="msf-button-group">
									<button class="my-shop-front-button" name="save_product_category" type="submit"><?php esc_html_e( 'Save Changes', 'shop-front' ); ?></button>
									<a href="<?php echo esc_url( msfc_get_navigation_url( 'categories' ) ); ?>" class="my-shop-front-button my-shop-front-button-light"><?php esc_html_e( 'Back', 'shop-front' ); ?></a>
								</div>
							</div>
						</form>
						<?php } ?>
					</div>
				</div>
			</div>
		</main>
	</div>
</div>
<?php do_action( 'msf_dashboard_wrapper_end' ); ?>