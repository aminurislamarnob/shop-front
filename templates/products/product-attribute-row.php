<?php
/**
 * Single product attribute row template.
 *
 * @var WC_Product_Attribute|array $attribute
 * @var int                        $i            Index
 * @var WC_Product|null            $product
 * @var int                        $product_id
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Normalize: $attribute might be a WC_Product_Attribute object or array
if ( is_object( $attribute ) && method_exists( $attribute, 'get_name' ) ) {
	$attr_name    = $attribute->get_name();
	$attr_label   = wc_attribute_label( $attr_name );
	$is_taxonomy  = $attribute->is_taxonomy();
	$is_visible   = $attribute->get_visible();
	$is_variation = $attribute->get_variation();
	$position     = $attribute->get_position();
	$attr_options = $attribute->get_options();
} else {
	// fallback for array-based attribute
	$attr_name    = $attribute['name'] ?? '';
	$attr_label   = wc_attribute_label( $attr_name );
	$is_taxonomy  = ! empty( $attribute['is_taxonomy'] );
	$is_visible   = ! empty( $attribute['is_visible'] );
	$is_variation = ! empty( $attribute['is_variation'] );
	$position     = $attribute['position'] ?? 0;
	$attr_options = $attribute['options'] ?? array();
}

// Resolve taxonomy terms once for both display badges and the editable select.
$all_terms = array();
if ( $is_taxonomy ) {
	$all_terms = get_terms(
		array(
			'taxonomy'   => $attr_name,
			'orderby'    => 'name',
			'hide_empty' => false,
		)
	);
	if ( is_wp_error( $all_terms ) ) {
		$all_terms = array();
	}
}

// Selected term labels for the read-only badge display.
$selected_labels = array();
if ( $is_taxonomy ) {
	foreach ( $all_terms as $term ) {
		if ( in_array( $term->term_id, $attr_options, true ) ) {
			$selected_labels[] = $term->name;
		}
	}
} else {
	$selected_labels = $attr_options;
}

// Start in edit mode when there are no values selected yet (e.g. a new attribute).
$is_editing = empty( $attr_options );
?>
<tr class="storesuite-attribute-row<?php echo $is_editing ? ' is-editing' : ''; ?>" data-index="<?php echo esc_attr( $i ); ?>" data-taxonomy="<?php echo esc_attr( $is_taxonomy ? $attr_name : '' ); ?>">
	<td class="storesuite-attribute-col-name">
		<div class="storesuite-attribute-name-head">
			<span class="storesuite-attribute-sort-handle" aria-hidden="true" title="<?php esc_attr_e( 'Drag to reorder', 'storesuite' ); ?>">
				<svg xmlns="http://www.w3.org/2000/svg" id="arrow-circle-down" viewBox="0 0 24 24" width="14" height="14" fill="currentColor"><path d="M23.351,10.253c-.233-.263-.462-.513-.619-.67L20.487,7.3a1,1,0,0,0-1.426,1.4l2.251,2.29L21.32,11H13V2.745l2.233,2.194a1,1,0,0,0,1.4-1.426l-2.279-2.24c-.163-.163-.413-.391-.674-.623A2.575,2.575,0,0,0,12.028.006.28.28,0,0,0,12,0l-.011,0a2.584,2.584,0,0,0-1.736.647c-.263.233-.513.462-.67.619L7.3,3.513A1,1,0,1,0,8.7,4.939l2.29-2.251L11,2.68V11H2.68l.015-.015L4.939,8.7A1,1,0,1,0,3.513,7.3L1.274,9.577c-.163.163-.392.413-.624.675A2.581,2.581,0,0,0,0,11.989L0,12c0,.01.005.019.006.029A2.573,2.573,0,0,0,.65,13.682c.233.262.461.512.618.67l2.245,2.284a1,1,0,0,0,1.426-1.4L2.744,13H11v8.32l-.015-.014L8.7,19.062a1,1,0,1,0-1.4,1.425l2.278,2.239c.163.163.413.391.675.624a2.587,2.587,0,0,0,3.43,0c.262-.233.511-.46.669-.619l2.284-2.244a1,1,0,1,0-1.4-1.425L13,21.256V13h8.256l-2.2,2.233a1,1,0,1,0,1.426,1.4l2.239-2.279c.163-.163.391-.413.624-.675A2.589,2.589,0,0,0,23.351,10.253Z"/></svg>
			</span>
			<?php if ( $is_taxonomy ) : ?>
				<span><?php echo esc_html( $attr_label ); ?></span>
				<input type="hidden" name="attribute_names[<?php echo esc_attr( $i ); ?>]" value="<?php echo esc_attr( $attr_name ); ?>">
			<?php else : ?>
				<span class="storesuite-attribute-custom-label"><?php echo esc_html( $attr_label ?: __( 'Attribute', 'storesuite' ) ); ?></span>
			<?php endif; ?>
			<input type="hidden" name="attribute_position[<?php echo esc_attr( $i ); ?>]" class="attribute_position" value="<?php echo esc_attr( $position ); ?>">
			<input type="hidden" name="attribute_is_taxonomy[<?php echo esc_attr( $i ); ?>]" value="<?php echo esc_attr( $is_taxonomy ? 1 : 0 ); ?>">
		</div>

		<!-- Read-only selected values -->
		<div class="storesuite-attribute-terms-display">
			<?php foreach ( $selected_labels as $label ) : ?>
				<span class="storesuite-term-badge"><?php echo esc_html( $label ); ?></span>
			<?php endforeach; ?>
		</div>

		<!-- Editable values -->
		<div class="storesuite-attribute-edit-wrap">
			<div class="storesuite-attribute-edit-inner">
				<?php if ( ! $is_taxonomy ) : ?>
					<div class="storesuite-form-group">
						<input type="text" class="storesuite-form-control storesuite-attribute-name-input" name="attribute_names[<?php echo esc_attr( $i ); ?>]" value="<?php echo esc_attr( $attr_name ); ?>" placeholder="<?php esc_attr_e( 'e.g. Material', 'storesuite' ); ?>">
					</div>
				<?php endif; ?>
				<?php if ( $is_taxonomy ) : ?>
					<select multiple class="storesuite-form-control storesuite-select2 storesuite-attribute-values"
						name="attribute_values[<?php echo esc_attr( $i ); ?>][]"
						data-placeholder="<?php esc_attr_e( 'Select terms', 'storesuite' ); ?>">
						<?php foreach ( $all_terms as $term ) : ?>
							<option value="<?php echo esc_attr( $term->term_id ); ?>"
								<?php selected( in_array( $term->term_id, $attr_options, true ) ); ?>>
								<?php echo esc_html( $term->name ); ?>
							</option>
						<?php endforeach; ?>
					</select>
				<?php else : ?>
					<select multiple class="storesuite-form-control storesuite-select2 storesuite-attribute-values"
						name="attribute_values[<?php echo esc_attr( $i ); ?>][]"
						data-placeholder="<?php esc_attr_e( 'Type & select custom values', 'storesuite' ); ?>"
						data-tags="true" data-token-separators='["|"]'>
						<?php foreach ( $attr_options as $option ) : ?>
							<option value="<?php echo esc_attr( $option ); ?>" selected><?php echo esc_html( $option ); ?></option>
						<?php endforeach; ?>
					</select>
				<?php endif; ?>
				<div class="storesuite-attribute-terms-toggle">
					<a href="#" class="my-storesuite-button my-storesuite-button-soft storesuite-select-all-terms"><?php esc_html_e( 'Select all', 'storesuite' ); ?></a>
					<a href="#" class="my-storesuite-button my-storesuite-button-danger-soft storesuite-select-no-terms"><?php esc_html_e( 'Select none', 'storesuite' ); ?></a>
					<?php if ( $is_taxonomy ) : ?>
						<a href="#" class="my-storesuite-button my-storesuite-button-soft storesuite-add-attribute-term"><?php esc_html_e( 'Add new term', 'storesuite' ); ?></a>
					<?php endif; ?>
				</div>
			</div>
		</div>
	</td>
	<td class="storesuite-attribute-col-visible">
		<div class="storesuite-form-group storesuite-form-switch storesuite-attribute-variation-switch">
			<input id="attribute_visibility_<?php echo esc_attr( $i ); ?>" type="checkbox" name="attribute_visibility[<?php echo esc_attr( $i ); ?>]" value="1" <?php checked( $is_visible ); ?>>
			<label for="attribute_visibility_<?php echo esc_attr( $i ); ?>" aria-label="<?php esc_attr_e( 'Visible on the product page', 'storesuite' ); ?>" title="<?php esc_attr_e( 'Visible on the product page', 'storesuite' ); ?>"></label>
		</div>
	</td>
	<td class="storesuite-attribute-col-variation">
		<div class="storesuite-form-group storesuite-form-switch storesuite-attribute-variation-switch">
			<input id="attribute_variation_<?php echo esc_attr( $i ); ?>" type="checkbox" name="attribute_variation[<?php echo esc_attr( $i ); ?>]" value="1" <?php checked( $is_variation ); ?>>
			<label for="attribute_variation_<?php echo esc_attr( $i ); ?>" aria-label="<?php esc_attr_e( 'Used for variations', 'storesuite' ); ?>" title="<?php esc_attr_e( 'Used for variations', 'storesuite' ); ?>"></label>
		</div>
	</td>
	<td class="storesuite-attribute-col-actions">
		<a href="#" class="my-storesuite-button my-storesuite-button-soft storesuite-edit-attribute" aria-label="<?php esc_attr_e( 'Edit values', 'storesuite' ); ?>" title="<?php esc_attr_e( 'Edit values', 'storesuite' ); ?>">
			<svg xmlns="http://www.w3.org/2000/svg" id="Layer_1" data-name="Layer 1" viewBox="0 0 24 24" width="16" height="16" fill="currentColor"><path d="m18.813,10c.309,0,.601-.143.79-.387s.255-.562.179-.861c-.311-1.217-.945-2.329-1.833-3.217l-3.485-3.485c-1.322-1.322-3.08-2.05-4.95-2.05h-4.515C2.243,0,0,2.243,0,5v14c0,2.757,2.243,5,5,5h3c.552,0,1-.448,1-1s-.448-1-1-1h-3c-1.654,0-3-1.346-3-3V5c0-1.654,1.346-3,3-3h4.515c.163,0,.325.008.485.023v4.977c0,1.654,1.346,3,3,3h5.813Zm-6.813-3V2.659c.379.218.732.488,1.05.806l3.485,3.485c.314.314.583.668.803,1.05h-4.338c-.551,0-1-.449-1-1Zm11.122,4.879c-1.134-1.134-3.11-1.134-4.243,0l-6.707,6.707c-.755.755-1.172,1.76-1.172,2.829v1.586c0,.552.448,1,1,1h1.586c1.069,0,2.073-.417,2.828-1.172l6.707-6.707c.567-.567.879-1.32.879-2.122s-.312-1.555-.878-2.121Zm-1.415,2.828l-6.708,6.707c-.377.378-.879.586-1.414.586h-.586v-.586c0-.534.208-1.036.586-1.414l6.708-6.707c.377-.378,1.036-.378,1.414,0,.189.188.293.439.293.707s-.104.518-.293.707Z"/></svg>
		</a>
		<a href="#" class="my-storesuite-button my-storesuite-button-danger-soft storesuite-remove-attribute" aria-label="<?php esc_attr_e( 'Remove attribute', 'storesuite' ); ?>" title="<?php esc_attr_e( 'Remove attribute', 'storesuite' ); ?>">
			<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="16" height="16" fill="currentColor"><path d="M21,4H17.9A5.009,5.009,0,0,0,13,0H11A5.009,5.009,0,0,0,6.1,4H3A1,1,0,0,0,3,6H4V19a5.006,5.006,0,0,0,5,5h6a5.006,5.006,0,0,0,5-5V6h1a1,1,0,0,0,0-2ZM11,2h2a3.006,3.006,0,0,1,2.829,2H8.171A3.006,3.006,0,0,1,11,2Zm7,17a3,3,0,0,1-3,3H9a3,3,0,0,1-3-3V6H18Z"/><path d="M10,18a1,1,0,0,0,1-1V11a1,1,0,0,0-2,0v6A1,1,0,0,0,10,18Z"/><path d="M14,18a1,1,0,0,0,1-1V11a1,1,0,0,0-2,0v6A1,1,0,0,0,14,18Z"/></svg>
		</a>
	</td>
</tr>
