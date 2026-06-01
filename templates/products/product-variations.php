<?php
/**
 * Product variations wrapper template.
 *
 * @var WC_Product|null $product
 * @var int             $product_id
 *
 * @package StoreSuite
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$variation_attributes = array();
if ( $product && $product->is_type( 'variable' ) ) {
	$product_attributes = $product->get_attributes( 'edit' );
	foreach ( $product_attributes as $attribute ) {
		if ( $attribute->get_variation() ) {
			$variation_attributes[] = $attribute;
		}
	}
}
?>

<div class="storesuite-card storesuite-card-with-header storesuite-mb-24 show_if_variable" id="storesuite-product-variations">
	<h3 class="storesuite-card-title">
		<span class="storesuite-card-title-text">
			<?php esc_html_e( 'Variations', 'storesuite' ); ?>
			<small class="storesuite-text-muted"><?php esc_html_e( 'Manage product variations (e.g. different sizes, colors)', 'storesuite' ); ?></small>
		</span>
		<?php if ( $product_id && ! empty( $variation_attributes ) ) : ?>
			<a href="#" id="storesuite-toggle-default-values" class="storesuite-text-link-button storesuite-hidden">
				<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="16" height="16" fill="currentColor"><path d="M12,8a4,4,0,1,0,4,4A4,4,0,0,0,12,8Zm0,6a2,2,0,1,1,2-2A2,2,0,0,1,12,14Z"/><path d="M21.294,13.9l-.444-.256a9.1,9.1,0,0,0,0-3.29l.444-.256a3,3,0,1,0-3-5.2l-.445.257A8.977,8.977,0,0,0,15,3.513V3a3,3,0,0,0-6,0v.513A8.977,8.977,0,0,0,6.152,5.158L5.705,4.9a3,3,0,0,0-3,5.2l.444.256a9.1,9.1,0,0,0,0,3.29l-.444.256a3,3,0,1,0,3,5.2l.445-.257A8.977,8.977,0,0,0,9,20.487V21a3,3,0,0,0,6,0v-.513a8.977,8.977,0,0,0,2.848-1.645l.447.258a3,3,0,0,0,3-5.2Zm-2.667-2.044a7.085,7.085,0,0,1,0,2.286,1,1,0,0,0,.485,1.044l1.181.682a1,1,0,1,1-1,1.733l-1.184-.683a1,1,0,0,0-1.143.113,6.981,6.981,0,0,1-1.979,1.149,1,1,0,0,0-.659.94V21a1,1,0,0,1-2,0V19.83a1,1,0,0,0-.659-.94A6.981,6.981,0,0,1,7.69,17.741a1,1,0,0,0-1.143-.113l-1.184.683a1,1,0,1,1-1-1.733l1.181-.682a1,1,0,0,0,.485-1.044,7.085,7.085,0,0,1,0-2.286,1,1,0,0,0-.485-1.044L4.363,10.84a1,1,0,1,1,1-1.733l1.184.683A1,1,0,0,0,7.69,9.677,6.981,6.981,0,0,1,9.669,8.528a1,1,0,0,0,.659-.94V6a1,1,0,0,1,2,0V7.588a1,1,0,0,0,.659.94,6.981,6.981,0,0,1,1.979,1.149,1,1,0,0,0,1.143.113l1.184-.683a1,1,0,1,1,1,1.733l-1.181.682A1,1,0,0,0,18.627,11.856Z"/></svg>
				<?php esc_html_e( 'Set Default Values', 'storesuite' ); ?>
			</a>
		<?php endif; ?>
	</h3>
	<div class="storesuite-card-content">

		<?php if ( ! $product_id ) : ?>
			<div class="storesuite-notice storesuite-notice-info">
				<p><?php esc_html_e( 'Save the product first to manage variations.', 'storesuite' ); ?></p>
			</div>
		<?php elseif ( empty( $variation_attributes ) ) : ?>
			<div class="storesuite-notice storesuite-notice-info">
				<p><?php esc_html_e( 'Before adding variations, add some attributes on the Attributes section above and mark them as "Used for variations".', 'storesuite' ); ?></p>
			</div>
		<?php else : ?>

			<!-- Variation toolbar -->
			<div class="storesuite-variation-toolbar">
				<div class="storesuite-variation-toolbar-primary">
					<button type="button" id="storesuite-generate-variations-btn" class="my-storesuite-button my-storesuite-button-soft">
						<?php esc_html_e( 'Generate variations', 'storesuite' ); ?>
					</button>
					<button type="button" id="storesuite-add-variation-btn" class="my-storesuite-button my-storesuite-button-soft">
						<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="16" height="16" fill="currentColor"><path d="M23,11H13V1a1,1,0,0,0-2,0V11H1a1,1,0,0,0,0,2H11V23a1,1,0,0,0,2,0V13H23a1,1,0,0,0,0-2Z"/></svg>
						<?php esc_html_e( 'Add manually', 'storesuite' ); ?>
					</button>
					<div class="storesuite-input-group storesuite-variation-bulk-group storesuite-hidden">
						<select id="storesuite-bulk-action-select" class="storesuite-form-control">
						<option value="" disabled selected><?php esc_html_e( 'Bulk actions', 'storesuite' ); ?></option>
						<optgroup label="<?php esc_attr_e( 'Status', 'storesuite' ); ?>">
							<option value="toggle_enabled"><?php esc_html_e( 'Toggle "Enabled"', 'storesuite' ); ?></option>
							<option value="toggle_downloadable"><?php esc_html_e( 'Toggle "Downloadable"', 'storesuite' ); ?></option>
							<option value="toggle_virtual"><?php esc_html_e( 'Toggle "Virtual"', 'storesuite' ); ?></option>
						</optgroup>
						<optgroup label="<?php esc_attr_e( 'Pricing', 'storesuite' ); ?>">
							<option value="variable_regular_price"><?php esc_html_e( 'Set regular prices', 'storesuite' ); ?></option>
							<option value="variable_regular_price_increase"><?php esc_html_e( 'Increase regular prices (fixed amount or percentage)', 'storesuite' ); ?></option>
							<option value="variable_regular_price_decrease"><?php esc_html_e( 'Decrease regular prices (fixed amount or percentage)', 'storesuite' ); ?></option>
							<option value="variable_sale_price"><?php esc_html_e( 'Set sale prices', 'storesuite' ); ?></option>
							<option value="variable_sale_price_increase"><?php esc_html_e( 'Increase sale prices (fixed amount or percentage)', 'storesuite' ); ?></option>
							<option value="variable_sale_price_decrease"><?php esc_html_e( 'Decrease sale prices (fixed amount or percentage)', 'storesuite' ); ?></option>
							<option value="variable_sale_schedule"><?php esc_html_e( 'Set scheduled sale dates', 'storesuite' ); ?></option>
						</optgroup>
						<?php if ( wc_get_container()->get( \Automattic\WooCommerce\Internal\CostOfGoodsSold\CostOfGoodsSoldController::class )->feature_is_enabled() ) : ?>
							<optgroup label="<?php esc_attr_e( 'Cost of goods', 'storesuite' ); ?>">
								<option value="variable_unset_cogs_value"><?php esc_html_e( 'Remove custom costs', 'storesuite' ); ?></option>
							</optgroup>
						<?php endif; ?>
						<optgroup label="<?php esc_attr_e( 'Inventory', 'storesuite' ); ?>">
							<option value="toggle_manage_stock"><?php esc_html_e( 'Toggle "Manage stock"', 'storesuite' ); ?></option>
							<option value="variable_stock"><?php esc_html_e( 'Stock', 'storesuite' ); ?></option>
							<option value="variable_stock_status_instock"><?php esc_html_e( 'Set Status - In stock', 'storesuite' ); ?></option>
							<option value="variable_stock_status_outofstock"><?php esc_html_e( 'Set Status - Out of stock', 'storesuite' ); ?></option>
							<option value="variable_stock_status_onbackorder"><?php esc_html_e( 'Set Status - On backorder', 'storesuite' ); ?></option>
							<option value="variable_low_stock_amount"><?php esc_html_e( 'Low stock threshold', 'storesuite' ); ?></option>
						</optgroup>
						<optgroup label="<?php esc_attr_e( 'Shipping', 'storesuite' ); ?>">
							<option value="variable_length"><?php esc_html_e( 'Length', 'storesuite' ); ?></option>
							<option value="variable_width"><?php esc_html_e( 'Width', 'storesuite' ); ?></option>
							<option value="variable_height"><?php esc_html_e( 'Height', 'storesuite' ); ?></option>
							<option value="variable_weight"><?php esc_html_e( 'Weight', 'storesuite' ); ?></option>
						</optgroup>
						<optgroup label="<?php esc_attr_e( 'Downloadable products', 'storesuite' ); ?>">
							<option value="variable_download_limit"><?php esc_html_e( 'Download limit', 'storesuite' ); ?></option>
							<option value="variable_download_expiry"><?php esc_html_e( 'Download expiry', 'storesuite' ); ?></option>
						</optgroup>
						<optgroup label="<?php esc_attr_e( 'Delete', 'storesuite' ); ?>">
							<option value="delete_all"><?php esc_html_e( 'Delete all variations', 'storesuite' ); ?></option>
						</optgroup>
						</select>
						<button type="button" id="storesuite-apply-bulk-action" class="my-storesuite-button my-storesuite-button-soft">
							<?php esc_html_e( 'Apply', 'storesuite' ); ?>
						</button>
					</div>
				</div>
			</div>

			<!-- Default attributes -->
			<?php
			$default_attributes = $product ? $product->get_default_attributes() : array();
			?>
			<div class="storesuite-default-attributes storesuite-mb-12" id="storesuite-default-attributes" style="display:none;">
				<h4 class="storesuite-default-attributes-title"><?php esc_html_e( 'Default Form Values', 'storesuite' ); ?></h4>
				<div class="row">
					<?php foreach ( $variation_attributes as $attribute ) :
						$attr_name   = $attribute->get_name();
						$attr_key    = sanitize_title( $attr_name );
						$attr_label  = wc_attribute_label( $attr_name );
						$current_val = isset( $default_attributes[ $attr_key ] ) ? $default_attributes[ $attr_key ] : '';

						if ( $attribute->is_taxonomy() ) {
							$terms = get_terms( array(
								'taxonomy'   => $attr_name,
								'orderby'    => 'name',
								'hide_empty' => false,
							) );
						} else {
							$terms = $attribute->get_options();
						}
					?>
						<div class="col-md-4">
							<div class="storesuite-form-group">
								<label><?php echo esc_html( $attr_label ); ?></label>
								<select name="default_attribute_<?php echo esc_attr( $attr_key ); ?>" class="storesuite-form-control storesuite-default-attribute-select">
									<option value=""><?php esc_html_e( 'No default', 'storesuite' ); ?></option>
									<?php if ( $attribute->is_taxonomy() && ! is_wp_error( $terms ) ) : ?>
										<?php foreach ( $terms as $term ) : ?>
											<option value="<?php echo esc_attr( $term->slug ); ?>" <?php selected( $current_val, $term->slug ); ?>>
												<?php echo esc_html( $term->name ); ?>
											</option>
										<?php endforeach; ?>
									<?php elseif ( ! $attribute->is_taxonomy() ) : ?>
										<?php foreach ( $terms as $option ) : ?>
											<option value="<?php echo esc_attr( $option ); ?>" <?php selected( $current_val, $option ); ?>>
												<?php echo esc_html( $option ); ?>
											</option>
										<?php endforeach; ?>
									<?php endif; ?>
								</select>
							</div>
						</div>
					<?php endforeach; ?>
					<div class="col-md-12">
						<button type="button" id="storesuite-save-default-attrs-btn" class="my-storesuite-button my-storesuite-button-sm my-storesuite-button-light">
							<?php esc_html_e( 'Save defaults', 'storesuite' ); ?>
						</button>
					</div>
				</div>
			</div>

			<!-- Variations container (loaded via AJAX) -->
			<div id="storesuite-variations-container"
				data-product-id="<?php echo esc_attr( $product_id ); ?>"
				data-per-page="<?php echo esc_attr( apply_filters( 'storesuite_variations_per_page', 15 ) ); ?>"
				data-page="1"
				data-total="0">
			</div>

			<!-- Pagination -->
			<div class="storesuite-variation-pagination"></div>

		<?php endif; ?>

	</div>
</div>
