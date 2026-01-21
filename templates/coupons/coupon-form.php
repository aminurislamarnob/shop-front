<?php
/**
 * MSFC coupon add/edit form
 *
 * @package ShopFront
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Initialize variables with default values.
$coupon_id                  = 0;
$coupon                     = null;
$coupon_code                = '';
$discount_type              = 'fixed_cart';
$coupon_amount              = '';
$description                = '';
$expiry_date                = '';
$coupon_status              = 'publish';
$coupon_visibility          = 'public';
$free_shipping              = false;
$individual_use             = false;
$exclude_sale_items         = false;
$minimum_amount             = '';
$maximum_amount             = '';
$usage_limit                = '';
$usage_limit_per_user       = '';
$limit_usage_to_x_items     = '';
$product_ids                = array();
$exclude_product_ids        = array();
$product_categories         = array();
$exclude_product_categories = array();
$customer_email             = '';
$is_edit_mode               = false;

// Check if this is edit mode.
if ( isset( $query_vars['edit-coupon'] ) && ! empty( $query_vars['edit-coupon'] ) ) {
	// Extract coupon ID from query var (could be just ID or "edit-coupon/ID").
	$edit_coupon_var = $query_vars['edit-coupon'];
	$coupon_id_parts = explode( '/', $edit_coupon_var );
	$coupon_id       = absint( end( $coupon_id_parts ) );

	if ( $coupon_id ) {
		$coupon       = new WC_Coupon( $coupon_id );
		$is_edit_mode = true;

		if ( $coupon && $coupon->get_id() ) {
			$coupon_post = get_post( $coupon_id );
			if ( $coupon_post ) {
				$coupon_status = $coupon_post->post_status;

				if ( 'private' === $coupon_post->post_status ) {
					$coupon_visibility = 'private';
				} else {
					$coupon_visibility = 'public';
				}
			}

			// Basic coupon data.
			$coupon_code                = $coupon->get_code();
			$discount_type              = $coupon->get_discount_type();
			$coupon_amount              = $coupon->get_amount();
			$description                = $coupon->get_description();
			$expiry_date                = $coupon->get_date_expires() ? $coupon->get_date_expires()->date( 'Y-m-d' ) : '';
			$free_shipping              = $coupon->get_free_shipping();
			$individual_use             = $coupon->get_individual_use();
			$exclude_sale_items         = $coupon->get_exclude_sale_items();
			$minimum_amount             = $coupon->get_minimum_amount();
			$maximum_amount             = $coupon->get_maximum_amount();
			$usage_limit                = $coupon->get_usage_limit();
			$usage_limit_per_user       = $coupon->get_usage_limit_per_user();
			$limit_usage_to_x_items     = $coupon->get_limit_usage_to_x_items();
			$product_ids                = $coupon->get_product_ids();
			$exclude_product_ids        = $coupon->get_excluded_product_ids();
			$product_categories         = $coupon->get_product_categories();
			$exclude_product_categories = $coupon->get_excluded_product_categories();
			$customer_email             = implode( ', ', $coupon->get_email_restrictions() );
		}
	}
}

$discount_types = wc_get_coupon_types();
?>
<form id="<?php echo $is_edit_mode ? 'msf-edit-coupon' : 'msf-add-coupon'; ?>">
	<div class="row">
		<div class="col-md-8">
			<div class="msf-card msf-card-with-header msf-mb-24">
				<h3 class="msf-card-title"><?php esc_html_e( 'General', 'shop-front' ); ?></h3>
				<div class="msf-card-content">
					<div class="msf-form-group">
						<label for="coupon_code"><?php esc_html_e( 'Coupon Code', 'shop-front' ); ?> <span class="req"><?php esc_html_e( '*', 'shop-front' ); ?></span></label>
						<div class="msf-coupon-code-wrapper">
							<input type="text" class="msf-form-control" id="coupon_code" name="coupon_code" placeholder="<?php echo esc_attr__( 'Enter coupon code', 'shop-front' ); ?>" value="<?php echo esc_attr( $coupon_code ); ?>" required>
						</div>
						<small class="msf-form-text"><a href="#" class="button generate-coupon-code"><?php esc_html_e( 'Generate coupon code', 'shop-front' ); ?></a></small>
					</div>

					<div class="msf-form-group">
						<label for="discount_type"><?php esc_html_e( 'Discount Type', 'shop-front' ); ?> <span class="req"><?php esc_html_e( '*', 'shop-front' ); ?></span></label>
						<select class="msf-form-control" id="discount_type" name="discount_type" required>
							<?php foreach ( $discount_types as $discount_type_key => $discount_type_label ) : ?>
								<option value="<?php echo esc_attr( $discount_type_key ); ?>" <?php selected( $discount_type, $discount_type_key ); ?>><?php echo esc_html( $discount_type_label ); ?></option>
							<?php endforeach; ?>
						</select>
					</div>

					<div class="msf-form-group">
						<label for="coupon_amount"><?php esc_html_e( 'Coupon Amount', 'shop-front' ); ?> <span class="req"><?php esc_html_e( '*', 'shop-front' ); ?></span></label>
						<input type="number" step="0.01" min="0" class="msf-form-control" id="coupon_amount" name="coupon_amount" placeholder="<?php echo esc_attr__( '0.00', 'shop-front' ); ?>" value="<?php echo esc_attr( $coupon_amount ); ?>" required>
						<small class="msf-form-text"><?php esc_html_e( 'Value of the coupon.', 'shop-front' ); ?></small>
					</div>

					<div class="msf-form-group">
						<label for="description"><?php esc_html_e( 'Description', 'shop-front' ); ?></label>
						<textarea class="msf-form-control" id="description" name="description" rows="3" placeholder="<?php echo esc_attr__( 'Optional description', 'shop-front' ); ?>"><?php echo esc_textarea( $description ); ?></textarea>
					</div>

					<div class="msf-form-group">
						<label for="expiry_date"><?php esc_html_e( 'Expiry Date', 'shop-front' ); ?></label>
						<input type="text" class="msf-form-control date-picker" id="expiry_date" name="expiry_date" value="<?php echo esc_attr( $expiry_date ); ?>" placeholder="<?php echo esc_attr__( 'YYYY-MM-DD', 'shop-front' ); ?>">
						<small class="msf-form-text"><?php esc_html_e( 'The coupon will expire at 00:00:00 of this date.', 'shop-front' ); ?></small>
					</div>

					<div class="msf-form-group msf-form-switch">
						<input type="checkbox" class="msf-form-control" id="free_shipping" name="free_shipping" value="yes" <?php checked( $free_shipping, true ); ?>>
						<label for="free_shipping"><?php esc_html_e( 'Allow free shipping', 'shop-front' ); ?></label>
					</div>

					<div class="msf-form-group msf-form-switch">
						<input type="checkbox" class="msf-form-control" id="individual_use" name="individual_use" value="yes" <?php checked( $individual_use, true ); ?>>
						<label for="individual_use"><?php esc_html_e( 'Individual use only', 'shop-front' ); ?></label>
					</div>
				</div>
			</div>

			<div class="msf-card msf-card-with-header msf-mb-24">
				<h3 class="msf-card-title"><?php esc_html_e( 'Usage restriction', 'shop-front' ); ?></h3>
				<div class="msf-card-content">
					<div class="msf-form-group msf-form-switch">
						<input type="checkbox" class="msf-form-control" id="exclude_sale_items" name="exclude_sale_items" value="yes" <?php checked( $exclude_sale_items, true ); ?>>
						<label for="exclude_sale_items"><?php esc_html_e( 'Exclude sale items', 'shop-front' ); ?></label>
						<small class="msf-form-text"><?php esc_html_e( 'Check this box if the coupon should not apply to items on sale.', 'shop-front' ); ?></small>
					</div>

					<div class="row">
						<div class="col-md-6">
							<div class="msf-form-group">
								<label for="minimum_amount"><?php esc_html_e( 'Minimum Spend', 'shop-front' ); ?></label>
								<input type="number" step="0.01" min="0" class="msf-form-control" id="minimum_amount" name="minimum_amount" placeholder="<?php echo esc_attr__( 'No minimum', 'shop-front' ); ?>" value="<?php echo esc_attr( $minimum_amount ); ?>">
								<small class="msf-form-text"><?php esc_html_e( 'Minimum spend (subtotal) required to use the coupon.', 'shop-front' ); ?></small>
							</div>
						</div>
						<div class="col-md-6">
							<div class="msf-form-group">
								<label for="maximum_amount"><?php esc_html_e( 'Maximum Spend', 'shop-front' ); ?></label>
								<input type="number" step="0.01" min="0" class="msf-form-control" id="maximum_amount" name="maximum_amount" placeholder="<?php echo esc_attr__( 'No maximum', 'shop-front' ); ?>" value="<?php echo esc_attr( $maximum_amount ); ?>">
								<small class="msf-form-text"><?php esc_html_e( 'Maximum spend (subtotal) allowed when using the coupon.', 'shop-front' ); ?></small>
							</div>
						</div>
					</div>

					<div class="msf-form-group">
						<label for="product_ids"><?php esc_html_e( 'Products', 'shop-front' ); ?></label>
						<?php
						$excluded_product_types = array_diff( array_keys( wc_get_product_types() ), array( 'simple', 'variable' ) );
						?>
						<select class="msf-form-control wc-product-search" id="product_ids" name="product_ids[]" data-action="woocommerce_json_search_products_and_variations" data-exclude_type="<?php echo esc_attr( implode( ',', $excluded_product_types ) ); ?>" data-display_stock="true" data-placeholder="<?php esc_attr_e( 'Search for a product&hellip;', 'shop-front' ); ?>" data-allow_clear="true" multiple>
							<?php
							if ( ! empty( $product_ids ) ) {
								foreach ( $product_ids as $product_id ) {
									$product = wc_get_product( $product_id );
									if ( $product ) {
										echo '<option value="' . esc_attr( $product_id ) . '" selected="selected">' . esc_html( $product->get_name() ) . '</option>';
									}
								}
							}
							?>
						</select>
					</div>

					<div class="msf-form-group">
						<label for="exclude_product_ids"><?php esc_html_e( 'Exclude Products', 'shop-front' ); ?></label>
						<select class="msf-form-control wc-product-search" id="exclude_product_ids" name="exclude_product_ids[]" data-action="woocommerce_json_search_products_and_variations" data-exclude_type="<?php echo esc_attr( implode( ',', $excluded_product_types ) ); ?>" data-display_stock="true" data-placeholder="<?php esc_attr_e( 'Search for a product&hellip;', 'shop-front' ); ?>" data-allow_clear="true" multiple>
							<?php
							if ( ! empty( $exclude_product_ids ) ) {
								foreach ( $exclude_product_ids as $product_id ) {
									$product = wc_get_product( $product_id );
									if ( $product ) {
										echo '<option value="' . esc_attr( $product_id ) . '" selected="selected">' . esc_html( $product->get_name() ) . '</option>';
									}
								}
							}
							?>
						</select>
					</div>

					<div class="row">
						<div class="col-md-6">
							<div class="msf-form-group">
								<label for="product_categories"><?php esc_html_e( 'Product Categories', 'shop-front' ); ?></label>
								<select class="msf-form-control msf-select2" id="product_categories" name="product_categories[]" multiple="multiple" data-placeholder="<?php echo esc_attr__( 'Any category', 'shop-front' ); ?>">
									<?php
									$categories = get_terms(
										array(
											'taxonomy'   => 'product_cat',
											'hide_empty' => false,
										)
									);
									if ( ! is_wp_error( $categories ) && ! empty( $categories ) ) {
										foreach ( $categories as $category_term ) {
											$selected = in_array( $category_term->term_id, $product_categories, true ) ? 'selected' : '';
											echo '<option value="' . esc_attr( $category_term->term_id ) . '" ' . esc_attr( $selected ) . '>' . esc_html( $category_term->name ) . '</option>';
										}
									}
									?>
								</select>
							</div>
						</div>
						<div class="col-md-6">
							<div class="msf-form-group">
								<label for="exclude_product_categories"><?php esc_html_e( 'Exclude Categories', 'shop-front' ); ?></label>
								<select class="msf-form-control msf-select2" id="exclude_product_categories" name="exclude_product_categories[]" multiple="multiple" data-placeholder="<?php echo esc_attr__( 'No categories', 'shop-front' ); ?>">
									<?php
									if ( ! is_wp_error( $categories ) && ! empty( $categories ) ) {
										foreach ( $categories as $category_term ) {
											$selected = in_array( $category_term->term_id, $exclude_product_categories, true ) ? 'selected' : '';
											echo '<option value="' . esc_attr( $category_term->term_id ) . '" ' . esc_attr( $selected ) . '>' . esc_html( $category_term->name ) . '</option>';
										}
									}
									?>
								</select>
							</div>
						</div>
					</div>

					<div class="msf-form-group">
						<label for="customer_email"><?php esc_html_e( 'Allowed Emails', 'shop-front' ); ?></label>
						<input type="text" class="msf-form-control" id="customer_email" name="customer_email" placeholder="<?php echo esc_attr__( 'No restrictions', 'shop-front' ); ?>" value="<?php echo esc_attr( $customer_email ); ?>">
						<small class="msf-form-text"><?php esc_html_e( 'Separate email addresses with commas. You can use * as a wildcard.', 'shop-front' ); ?></small>
					</div>
				</div>
			</div>
		</div>

		<div class="col-md-4">
			<div class="msf-card msf-card-with-header msf-mb-24">
				<h3 class="msf-card-title"><?php esc_html_e( 'Status & Visibility', 'shop-front' ); ?></h3>
				<div class="msf-card-content">
					<div class="msf-form-group">
						<label for="coupon_status"><?php esc_html_e( 'Status', 'shop-front' ); ?></label>
						<select class="msf-form-control" id="coupon_status" name="coupon_status">
							<option value="publish" <?php selected( $coupon_status, 'publish' ); ?>><?php esc_html_e( 'Published', 'shop-front' ); ?></option>
							<option value="pending" <?php selected( $coupon_status, 'pending' ); ?>><?php esc_html_e( 'Pending review', 'shop-front' ); ?></option>
							<option value="draft" <?php selected( $coupon_status, 'draft' ); ?>><?php esc_html_e( 'Draft', 'shop-front' ); ?></option>
						</select>
					</div>

					<div class="msf-form-group">
						<label for="coupon_visibility"><?php esc_html_e( 'Visibility', 'shop-front' ); ?></label>
						<select class="msf-form-control" id="coupon_visibility" name="coupon_visibility">
							<option value="public" <?php selected( $coupon_visibility, 'public' ); ?>><?php esc_html_e( 'Public', 'shop-front' ); ?></option>
							<option value="private" <?php selected( $coupon_visibility, 'private' ); ?>><?php esc_html_e( 'Private', 'shop-front' ); ?></option>
						</select>
					</div>
				</div>
			</div>

			<div class="msf-card msf-card-with-header msf-mb-24">
				<h3 class="msf-card-title"><?php esc_html_e( 'Usage limits', 'shop-front' ); ?></h3>
				<div class="msf-card-content">
					<div class="msf-form-group">
						<label for="usage_limit"><?php esc_html_e( 'Usage Limit Per Coupon', 'shop-front' ); ?></label>
						<input type="number" step="1" min="0" class="msf-form-control" id="usage_limit" name="usage_limit" placeholder="<?php echo esc_attr__( 'Unlimited usage', 'shop-front' ); ?>" value="<?php echo esc_attr( $usage_limit ); ?>">
					</div>

					<div class="msf-form-group">
						<label for="usage_limit_per_user"><?php esc_html_e( 'Usage Limit Per User', 'shop-front' ); ?></label>
						<input type="number" step="1" min="0" class="msf-form-control" id="usage_limit_per_user" name="usage_limit_per_user" placeholder="<?php echo esc_attr__( 'Unlimited usage', 'shop-front' ); ?>" value="<?php echo esc_attr( $usage_limit_per_user ); ?>">
					</div>

					<div class="msf-form-group">
						<label for="limit_usage_to_x_items"><?php esc_html_e( 'Limit Usage to X Items', 'shop-front' ); ?></label>
						<input type="number" step="1" min="0" class="msf-form-control" id="limit_usage_to_x_items" name="limit_usage_to_x_items" placeholder="<?php echo esc_attr__( 'Apply to all qualifying items', 'shop-front' ); ?>" value="<?php echo esc_attr( $limit_usage_to_x_items ); ?>">
					</div>
				</div>
			</div>

			<?php if ( $is_edit_mode ) : ?>
				<input type="hidden" name="coupon_id" value="<?php echo esc_attr( $coupon_id ); ?>">
				<?php wp_nonce_field( '_msf_edit_coupon_', 'msf_edit_coupon_nonce' ); ?>
				<input type="hidden" name="action" value="msf_edit_coupon">
			<?php else : ?>
				<?php wp_nonce_field( '_msf_add_coupon_', 'msf_add_coupon_nonce' ); ?>
				<input type="hidden" name="action" value="msf_add_coupon">
			<?php endif; ?>

			<div class="msf-form-submission-group">
				<div class="msf-button-group">
					<button class="my-shop-front-button" type="submit"><?php echo $is_edit_mode ? esc_html__( 'Update Coupon', 'shop-front' ) : esc_html__( 'Create Coupon', 'shop-front' ); ?></button>
					<a href="<?php echo esc_url( msfc_get_navigation_url( 'coupons' ) ); ?>" class="my-shop-front-button my-shop-front-button-light"><?php esc_html_e( 'Back', 'shop-front' ); ?></a>
				</div>
			</div>
		</div>
	</div>
</form>
