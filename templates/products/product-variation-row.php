<?php
/**
 * Single product variation row template.
 *
 * @var WC_Product_Variation $variation
 * @var int                  $variation_id
 * @var int                  $loop
 * @var WC_Product_Variable  $parent
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$variation_attributes = $variation->get_attributes();
$parent_attributes    = $parent->get_attributes( 'edit' );
$enabled              = ( 'publish' === $variation->get_status() );
?>
<div class="storesuite-variation-row storesuite-card storesuite-mb-12" data-variation-id="<?php echo esc_attr( $variation_id ); ?>">
	<div class="storesuite-variation-header" style="display:flex; justify-content:space-between; align-items:center; padding:10px 16px; cursor:pointer;">
		<strong><?php printf( esc_html__( 'Variation #%d', 'storesuite' ), absint( $variation_id ) ); ?></strong>
		<span>
			<a href="#" class="storesuite-toggle-variation" title="<?php esc_attr_e( 'Toggle', 'storesuite' ); ?>">▾</a>
			<a href="#" class="storesuite-remove-variation" data-variation-id="<?php echo esc_attr( $variation_id ); ?>" title="<?php esc_attr_e( 'Remove', 'storesuite' ); ?>" style="color:#d63638; margin-left:8px;">✕</a>
		</span>
		<input type="hidden" name="variable_post_id[<?php echo esc_attr( $loop ); ?>]" value="<?php echo esc_attr( $variation_id ); ?>">
		<input type="hidden" name="variation_menu_order[<?php echo esc_attr( $loop ); ?>]" value="<?php echo esc_attr( $variation->get_menu_order() ); ?>">
	</div>

	<div class="storesuite-variation-body storesuite-card-content" style="display:none;">
		<div class="row">
			<?php foreach ( $parent_attributes as $attribute ) : ?>
				<?php
				if ( ! $attribute->get_variation() ) {
					continue;
				}
				$attribute_key = sanitize_title( $attribute->get_name() );
				$current_value = isset( $variation_attributes[ $attribute_key ] ) ? $variation_attributes[ $attribute_key ] : '';
				?>
				<div class="col-md-4">
					<div class="storesuite-form-group">
						<label><?php echo esc_html( wc_attribute_label( $attribute->get_name() ) ); ?></label>
						<select class="storesuite-form-control" name="attribute_<?php echo esc_attr( $attribute_key ); ?>[<?php echo esc_attr( $loop ); ?>]">
							<option value=""><?php printf( esc_html__( 'Any %s', 'storesuite' ), esc_html( wc_attribute_label( $attribute->get_name() ) ) ); ?></option>
							<?php if ( $attribute->is_taxonomy() ) : ?>
								<?php foreach ( $attribute->get_terms() as $term ) : ?>
									<option value="<?php echo esc_attr( $term->slug ); ?>" <?php selected( $current_value, $term->slug ); ?>>
										<?php echo esc_html( $term->name ); ?>
									</option>
								<?php endforeach; ?>
							<?php else : ?>
								<?php foreach ( $attribute->get_options() as $option ) : ?>
									<option value="<?php echo esc_attr( $option ); ?>" <?php selected( $current_value, $option ); ?>>
										<?php echo esc_html( $option ); ?>
									</option>
								<?php endforeach; ?>
							<?php endif; ?>
						</select>
					</div>
				</div>
			<?php endforeach; ?>
		</div>

		<div class="row">
			<div class="col-md-3">
				<div class="storesuite-form-group storesuite-form-switch">
					<input type="checkbox" name="variable_enabled[<?php echo esc_attr( $loop ); ?>]" value="1" <?php checked( $enabled ); ?>>
					<label><?php esc_html_e( 'Enabled', 'storesuite' ); ?></label>
				</div>
			</div>
			<div class="col-md-3">
				<div class="storesuite-form-group storesuite-form-switch">
					<input type="checkbox" class="variable_manage_stock" name="variable_manage_stock[<?php echo esc_attr( $loop ); ?>]" value="1" <?php checked( $variation->get_manage_stock() ); ?>>
					<label><?php esc_html_e( 'Manage stock', 'storesuite' ); ?></label>
				</div>
			</div>
			<div class="col-md-3">
				<div class="storesuite-form-group">
					<label><?php esc_html_e( 'Regular price', 'storesuite' ); ?></label>
					<input type="text" class="storesuite-form-control" name="variable_regular_price[<?php echo esc_attr( $loop ); ?>]" value="<?php echo esc_attr( wc_format_localized_price( $variation->get_regular_price( 'edit' ) ) ); ?>">
				</div>
			</div>
			<div class="col-md-3">
				<div class="storesuite-form-group">
					<label><?php esc_html_e( 'Sale price', 'storesuite' ); ?></label>
					<input type="text" class="storesuite-form-control" name="variable_sale_price[<?php echo esc_attr( $loop ); ?>]" value="<?php echo esc_attr( wc_format_localized_price( $variation->get_sale_price( 'edit' ) ) ); ?>">
				</div>
			</div>
			<div class="col-md-4">
				<div class="storesuite-form-group">
					<label><?php esc_html_e( 'SKU', 'storesuite' ); ?></label>
					<input type="text" class="storesuite-form-control" name="variable_sku[<?php echo esc_attr( $loop ); ?>]" value="<?php echo esc_attr( $variation->get_sku( 'edit' ) ); ?>">
				</div>
			</div>
			<div class="col-md-4 show_if_variation_manage_stock" style="<?php echo $variation->get_manage_stock() ? '' : 'display:none;'; ?>">
				<div class="storesuite-form-group">
					<label><?php esc_html_e( 'Stock qty', 'storesuite' ); ?></label>
					<input type="number" class="storesuite-form-control" name="variable_stock[<?php echo esc_attr( $loop ); ?>]" step="any" value="<?php echo esc_attr( wc_stock_amount( $variation->get_stock_quantity( 'edit' ) ) ); ?>">
				</div>
			</div>
			<div class="col-md-4">
				<div class="storesuite-form-group">
					<label><?php esc_html_e( 'Stock status', 'storesuite' ); ?></label>
					<select class="storesuite-form-control" name="variable_stock_status[<?php echo esc_attr( $loop ); ?>]">
						<?php foreach ( wc_get_product_stock_status_options() as $key => $label ) : ?>
							<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $variation->get_stock_status( 'edit' ), $key ); ?>><?php echo esc_html( $label ); ?></option>
						<?php endforeach; ?>
					</select>
				</div>
			</div>
		</div>
	</div>
</div>
