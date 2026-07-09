<?php
/**
 * Product attributes wrapper template.
 *
 * @var WC_Product|null $product
 * @var int             $product_id
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$attribute_taxonomies = wc_get_attribute_taxonomies();
$product_attributes   = $product ? $product->get_attributes( 'edit' ) : array();
?>

<div class="storesuite-card storesuite-card-with-header storesuite-mb-24 hide_if_grouped" id="storesuite-product-attributes">
	<h3 class="storesuite-card-title">
		<?php esc_html_e( 'Attributes', 'storesuite' ); ?>
	</h3>
	<div class="storesuite-card-content">
		<?php // Marker so the product save handler knows attributes were submitted (and can clear them when all rows are removed). ?>
		<input type="hidden" name="storesuite_attributes_submitted" value="1">
		<p class="storesuite-attributes-subtitle">
			<?php esc_html_e( 'Manage product attributes for content and filtering', 'storesuite' ); ?>
		</p>

		<table class="my-storesuite-tbl my-storesuite-attributes-table" id="storesuite-attributes-table"<?php echo empty( $product_attributes ) ? ' style="display:none;"' : ''; ?>>
			<thead>
				<tr>
					<th class="storesuite-attribute-col-name"><?php esc_html_e( 'Attribute', 'storesuite' ); ?></th>
					<th class="storesuite-attribute-col-visible"><?php esc_html_e( 'Visible', 'storesuite' ); ?></th>
					<th class="storesuite-attribute-col-variation"><?php esc_html_e( 'Variation', 'storesuite' ); ?></th>
					<th class="storesuite-attribute-col-actions">&nbsp;</th>
				</tr>
			</thead>
			<tbody id="storesuite-attributes-list" class="storesuite-attributes-list">
				<?php
				$i = 0;
				if ( ! empty( $product_attributes ) ) {
					foreach ( $product_attributes as $attribute ) {
						$args = array(
							'attribute'  => $attribute,
							'i'          => $i,
							'product'    => $product,
							'product_id' => $product_id,
						);
						storesuite_get_template_part( 'products/product-attribute-row', '', $args );
						++$i;
					}
				}
				?>
			</tbody>
		</table>

	</div>

	<!-- Add attribute toolbar (card footer) -->
	<div class="storesuite-card-footer storesuite-attribute-toolbar" id="storesuite-attribute-toolbar">
		<?php
		// Global attributes not already added to this product.
		$available_global_attributes = array();
		foreach ( $attribute_taxonomies as $tax ) {
			$tax_name = wc_attribute_taxonomy_name( $tax->attribute_name );
			if ( isset( $product_attributes[ $tax_name ] ) ) {
				continue;
			}
			$available_global_attributes[ $tax_name ] = $tax->attribute_label;
		}
		?>
		<?php if ( ! empty( $available_global_attributes ) ) : ?>
			<div class="storesuite-input-group">
				<select id="storesuite-add-attribute-select" class="storesuite-form-control">
					<option value="" disabled selected><?php esc_html_e( 'Select attribute', 'storesuite' ); ?></option>
					<?php foreach ( $available_global_attributes as $tax_name => $tax_label ) : ?>
						<option value="<?php echo esc_attr( $tax_name ); ?>"><?php echo esc_html( $tax_label ); ?></option>
					<?php endforeach; ?>
				</select>
				<button type="button" id="storesuite-add-attribute-btn" class="my-storesuite-button my-storesuite-button-soft">
					<?php esc_html_e( 'Add', 'storesuite' ); ?> <span class="storesuite-add-attribute-btn-label"><?php esc_html_e( 'Attribute', 'storesuite' ); ?></span>
				</button>
			</div>
		<?php endif; ?>
		<button type="button" id="storesuite-add-custom-attribute-btn" class="my-storesuite-button my-storesuite-button-soft">
			<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="16" height="16" fill="currentColor"><path d="M23,11H13V1a1,1,0,0,0-2,0V11H1a1,1,0,0,0,0,2H11V23a1,1,0,0,0,2,0V13H23a1,1,0,0,0,0-2Z"/></svg>
			<?php esc_html_e( 'New custom attribute', 'storesuite' ); ?>
		</button>
	</div>
</div>
