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
	<h3 class="storesuite-card-title"><?php esc_html_e( 'Variations', 'storesuite' ); ?>
		<small class="storesuite-text-muted"><?php esc_html_e( 'Manage product variations (e.g. different sizes, colors)', 'storesuite' ); ?></small>
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
				<div class="row">
					<div class="col-md-6">
						<select id="storesuite-variation-actions" class="storesuite-form-control">
							<optgroup label="<?php esc_attr_e( 'Add', 'storesuite' ); ?>">
								<option value="add_variation"><?php esc_html_e( 'Add variation', 'storesuite' ); ?></option>
								<option value="generate_variations"><?php esc_html_e( 'Create variations from all attributes', 'storesuite' ); ?></option>
							</optgroup>
							<optgroup label="<?php esc_attr_e( 'Bulk actions', 'storesuite' ); ?>">
								<option value="variable_regular_price"><?php esc_html_e( 'Set regular prices', 'storesuite' ); ?></option>
								<option value="variable_sale_price"><?php esc_html_e( 'Set sale prices', 'storesuite' ); ?></option>
								<option value="variable_stock_status"><?php esc_html_e( 'Set stock status', 'storesuite' ); ?></option>
								<option value="toggle_enabled"><?php esc_html_e( 'Toggle "Enabled"', 'storesuite' ); ?></option>
							</optgroup>
							<optgroup label="<?php esc_attr_e( 'Delete', 'storesuite' ); ?>">
								<option value="delete_all"><?php esc_html_e( 'Delete all variations', 'storesuite' ); ?></option>
							</optgroup>
						</select>
					</div>
					<div class="col-md-6">
						<button type="button" id="storesuite-do-variation-action" class="my-storesuite-button my-storesuite-button-sm">
							<?php esc_html_e( 'Go', 'storesuite' ); ?>
						</button>
						<button type="button" id="storesuite-save-variations-btn" class="my-storesuite-button my-storesuite-button-sm my-storesuite-button-light" disabled>
							<?php esc_html_e( 'Save changes', 'storesuite' ); ?>
						</button>
					</div>
				</div>
			</div>

			<!-- Default attributes -->
			<?php
			$default_attributes = $product ? $product->get_default_attributes() : array();
			?>
			<div class="storesuite-default-attributes storesuite-mb-12">
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
