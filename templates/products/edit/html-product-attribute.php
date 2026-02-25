<?php
/**
 * Single product attribute row (variable product).
 *
 * @package StoreSuite
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$metabox_class = isset( $metabox_class ) && is_array( $metabox_class ) ? $metabox_class : array();
$thepostid     = isset( $thepostid ) ? absint( $thepostid ) : 0;
$attribute_taxonomy = isset( $attribute_taxonomy ) ? $attribute_taxonomy : null;
$attr_type = ( $attribute_taxonomy && isset( $attribute_taxonomy->attribute_type ) ) ? $attribute_taxonomy->attribute_type : 'select';
if ( ! in_array( $attr_type, array( 'select', 'text' ), true ) ) {
	$attr_type = 'select';
}
?>
<li class="product-attribute-list msf-attribute-list <?php echo esc_attr( implode( ' ', $metabox_class ) ); ?>" data-taxonomy="<?php echo esc_attr( $taxonomy ); ?>">
	<div class="msf-attribute-heading">
		<span><strong><?php echo ! empty( $attribute_label ) ? esc_html( $attribute_label ) : esc_html__( 'Attribute Name', 'storesuite' ); ?></strong></span>
		<a href="#" class="storesuite-remove-attribute"><?php esc_html_e( 'Remove', 'storesuite' ); ?></a>
		<a href="#" class="storesuite-toggle-attribute"><span class="toggle-icon">▼</span></a>
	</div>
	<div class="msf-attribute-item msf-clearfix storesuite-attribute-content" style="display:none;">
		<div class="msf-form-group">
			<label class="form-label"><?php esc_html_e( 'Name', 'storesuite' ); ?></label>
			<?php if ( ! empty( $attribute['is_taxonomy'] ) ) : ?>
				<strong><?php echo esc_html( $attribute_label ); ?></strong>
				<input type="hidden" name="attribute_names[<?php echo (int) $i; ?>]" value="<?php echo esc_attr( $taxonomy ); ?>" />
			<?php else : ?>
				<input type="text" class="msf-form-control attribute_name" name="attribute_names[<?php echo (int) $i; ?>]" value="<?php echo esc_attr( $attribute['name'] ); ?>" />
			<?php endif; ?>
			<input type="hidden" name="attribute_position[<?php echo (int) $i; ?>]" class="attribute_position" value="<?php echo (int) $position; ?>" />
			<input type="hidden" name="attribute_is_taxonomy[<?php echo (int) $i; ?>]" value="<?php echo ! empty( $attribute['is_taxonomy'] ) ? 1 : 0; ?>" />
			<label class="msf-checkbox-label"><input type="checkbox" <?php checked( ! empty( $attribute['is_visible'] ) ); ?> name="attribute_visibility[<?php echo (int) $i; ?>]" value="1" /> <?php esc_html_e( 'Visible on the product page', 'storesuite' ); ?></label>
			<label class="msf-checkbox-label show_if_variable"><input type="checkbox" <?php checked( ! empty( $attribute['is_variation'] ) ); ?> name="attribute_variation[<?php echo (int) $i; ?>]" value="1" /> <?php esc_html_e( 'Used for variations', 'storesuite' ); ?></label>
		</div>
		<div class="msf-form-group dokan-attribute-values">
			<label class="form-label"><?php esc_html_e( 'Value(s)', 'storesuite' ); ?></label>
			<?php if ( ! empty( $attribute['is_taxonomy'] ) && taxonomy_exists( $taxonomy ) ) : ?>
				<?php
				$all_terms = get_terms(
					array(
						'taxonomy'   => $taxonomy,
						'orderby'    => 'name',
						'hide_empty' => false,
					)
				);
				$post_terms = $thepostid ? wp_get_post_terms( $thepostid, $taxonomy, array( 'fields' => 'ids' ) ) : array();
				$post_terms = is_wp_error( $post_terms ) ? array() : $post_terms;
				?>
				<?php if ( 'text' === $attr_type ) : ?>
					<?php
					$attr_val = $thepostid ? wp_get_post_terms( $thepostid, $taxonomy, array( 'fields' => 'names' ) ) : array();
					$attr_val = is_wp_error( $attr_val ) ? array() : $attr_val;
					?>
					<select name="attribute_values[<?php echo (int) $i; ?>][]" multiple style="width:100%" class="msf-form-control msf-select2 storesuite-attr-values" data-placeholder="<?php echo esc_attr( sprintf( __( 'Enter text or separate with "%s"', 'storesuite' ), WC_DELIMITER ) ); ?>" data-tags="true" data-token-separators="[',', '|']">
						<?php foreach ( $attr_val as $val ) : ?>
							<option value="<?php echo esc_attr( $val ); ?>" selected><?php echo esc_html( $val ); ?></option>
						<?php endforeach; ?>
					</select>
				<?php else : ?>
					<select name="attribute_values[<?php echo (int) $i; ?>][]" multiple style="width:100%" class="msf-form-control msf-select2 storesuite-attr-values" data-placeholder="<?php esc_attr_e( 'Select terms', 'storesuite' ); ?>">
						<?php
						foreach ( $all_terms as $term ) {
							$selected = in_array( $term->term_id, $post_terms, true ) ? ' selected' : '';
							echo '<option value="' . esc_attr( $term->slug ) . '"' . $selected . '>' . esc_html( $term->name ) . '</option>';
						}
						?>
					</select>
				<?php endif; ?>
			<?php else : ?>
				<?php
				$custom_values = array();
				if ( ! empty( $attribute['value'] ) ) {
					$custom_values = array_map( 'trim', explode( WC_DELIMITER, $attribute['value'] ) );
				}
				?>
				<select name="attribute_values[<?php echo (int) $i; ?>][]" multiple style="width:100%" class="msf-form-control msf-select2 storesuite-attr-values" data-placeholder="<?php echo esc_attr( sprintf( __( 'Enter text or separate with "%s"', 'storesuite' ), WC_DELIMITER ) ); ?>" data-tags="true" data-token-separators="[',', '|']">
					<?php foreach ( $custom_values as $val ) : ?>
						<option value="<?php echo esc_attr( $val ); ?>" selected><?php echo esc_html( $val ); ?></option>
					<?php endforeach; ?>
				</select>
			<?php endif; ?>
		</div>
	</div>
</li>
