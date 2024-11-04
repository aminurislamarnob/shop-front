<?php
/**
 * MSFC category edit page
 * ***/

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$category_id = get_query_var( 'edit-category' );
$category_id = absint( $category_id );

do_action( 'msf_dashboard_wrapper_start' );
?>
<div class="my-shop-front-container">
	<div class="row">
		<div class="col-md-2">
			<?php do_action( 'msf_dashboard_navigation' ); ?>
		</div>
		<div class="col-md-10">
			<?php
			if ( $category_id ) {
				$category = get_term( $category_id, 'product_cat' );

				if ( ! $category || is_wp_error( $category ) ) {
					echo '<div class="alert alert-danger">' . esc_html__( 'Category not found.', 'shop-front' ) . '</div>';
					return;
				}
				?>
			<div x-data="categoryEditFormHandler(<?php echo esc_attr( $category->term_id ); ?>)">
				<form id="msfc-edit-category" @submit.prevent="handleCategoryEditSubmission">
					<div class="form-group">
						<label for="product_category_name"><?php esc_html_e( 'Category Name', 'shop-front' ); ?> <span class="req"><?php esc_html_e( '*', 'shop-front' ); ?></span></label>
						<input type="text" class="form-control" id="product_category_name" name="product_category_name" placeholder="<?php echo esc_attr__( 'Product category name', 'shop-front' ); ?>" value="<?php echo esc_attr( $category->name ); ?>" aria-required="true">
					</div>
					<div class="form-group">
						<label for="product_parent_category"><?php esc_html_e( 'Select Parent Category', 'shop-front' ); ?></label>
						<select class="form-control" id="product_parent_category" name="product_parent_category">
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
					<div class="form-group">
						<label for="product_category_description"><?php esc_html_e( 'Category Description', 'shop-front' ); ?></label>
						<textarea class="form-control" id="product_category_description" name="product_category_description" placeholder="<?php echo esc_attr__( 'Product category description', 'shop-front' ); ?>" rows="3"><?php echo esc_textarea( $category->description ); ?></textarea>
					</div>
					<div class="form-group">
						<?php wp_nonce_field( 'msfc_edit_product_category_' . SHOP_FRONT_NONCE_SALT, 'msfc_edit_product_category_nonce' ); ?>
						<input type="hidden" name="action" value="msfc_edit_product_category">
						<input type="hidden" name="category_id" value="<?php echo esc_attr( $category->term_id ); ?>">
						<button class="msfc-wfm-button msfc-submit-btn" name="save_product_category" type="submit"><?php esc_html_e( 'Save Changes', 'shop-front' ); ?></button>
					</div>
				</form>
				<!-- Success and Error Messages -->
				<div x-show="message" x-text="message" class="alert"></div>
				<div x-show="error" x-text="error" class="alert alert-danger"></div>
			</div>
			<?php } ?>
		</div>
	</div>
</div>
<?php do_action( 'msf_dashboard_wrapper_end' ); ?>