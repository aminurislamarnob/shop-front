<?php
/**
 * Single product variation row template (accordion).
 *
 * @var WC_Product_Variation $variation
 * @var int                  $variation_id
 * @var int                  $loop          Index for array-named inputs.
 * @var WC_Product_Variable  $parent
 *
 * @package StoreSuite
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$variation_data   = $variation->get_data();
$variation_image  = $variation->get_image_id()
	? wp_get_attachment_image_src( $variation->get_image_id(), 'thumbnail' )
	: null;
$variation_thumb  = $variation_image ? $variation_image[0] : wc_placeholder_img_src( 'thumbnail' );
$parent_attributes = $parent->get_attributes( 'edit' );
$variation_attrs   = $variation->get_attributes();
?>

<div class="storesuite-variation-row storesuite-card storesuite-mb-12" data-variation-id="<?php echo esc_attr( $variation_id ); ?>">
	<input type="hidden" name="variable_post_id[<?php echo esc_attr( $loop ); ?>]" value="<?php echo esc_attr( $variation_id ); ?>">
	<input type="hidden" name="variable_menu_order[<?php echo esc_attr( $loop ); ?>]" value="<?php echo esc_attr( $variation->get_menu_order() ); ?>">

	<!-- Header -->
	<div class="storesuite-variation-header">
		<div class="storesuite-variation-thumb">
			<img src="<?php echo esc_url( $variation_thumb ); ?>" width="40" height="40" alt="">
		</div>

		<div class="storesuite-variation-attrs">
			<?php foreach ( $parent_attributes as $attribute ) : ?>
				<?php
				if ( ! $attribute->get_variation() ) {
					continue;
				}
				$attr_name    = $attribute->get_name();
				$attr_label   = wc_attribute_label( $attr_name );
				$current_val  = isset( $variation_attrs[ sanitize_title( $attr_name ) ] ) ? $variation_attrs[ sanitize_title( $attr_name ) ] : '';

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
				<select name="attribute_<?php echo esc_attr( sanitize_title( $attr_name ) ); ?>[<?php echo esc_attr( $loop ); ?>]"
					class="storesuite-form-control storesuite-variation-attr-select">
					<option value=""><?php /* translators: %s: attribute label */ printf( esc_html__( 'Any %s…', 'storesuite' ), esc_html( $attr_label ) ); ?></option>
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
			<?php endforeach; ?>
		</div>

		<span class="storesuite-variation-actions-header">
			<a href="#" class="storesuite-toggle-variation" title="<?php esc_attr_e( 'Toggle', 'storesuite' ); ?>">&#9662;</a>
			<a href="#" class="storesuite-remove-variation" data-variation-id="<?php echo esc_attr( $variation_id ); ?>" title="<?php esc_attr_e( 'Remove', 'storesuite' ); ?>">&times;</a>
		</span>
	</div>

	<!-- Body (collapsed by default) -->
	<div class="storesuite-variation-body" style="display:none;">
		<div class="storesuite-card-content">
			<div class="row">
				<!-- Enabled / Virtual / Downloadable / Manage stock -->
				<div class="col-md-3">
					<div class="storesuite-form-group storesuite-form-switch">
						<input type="checkbox" id="variable_enabled_<?php echo esc_attr( $loop ); ?>"
							name="variable_enabled[<?php echo esc_attr( $loop ); ?>]" value="1"
							<?php checked( 'publish' === $variation->get_status() || ! $variation_id ); ?>>
						<label for="variable_enabled_<?php echo esc_attr( $loop ); ?>"><?php esc_html_e( 'Enabled', 'storesuite' ); ?></label>
					</div>
				</div>
				<div class="col-md-3">
					<div class="storesuite-form-group storesuite-form-switch">
						<input type="checkbox" class="variable_is_virtual" id="variable_is_virtual_<?php echo esc_attr( $loop ); ?>"
							name="variable_is_virtual[<?php echo esc_attr( $loop ); ?>]" value="1"
							<?php checked( $variation->get_virtual() ); ?>>
						<label for="variable_is_virtual_<?php echo esc_attr( $loop ); ?>"><?php esc_html_e( 'Virtual', 'storesuite' ); ?></label>
					</div>
				</div>
				<div class="col-md-3">
					<div class="storesuite-form-group storesuite-form-switch">
						<input type="checkbox" id="variable_is_downloadable_<?php echo esc_attr( $loop ); ?>"
							name="variable_is_downloadable[<?php echo esc_attr( $loop ); ?>]" value="1"
							<?php checked( $variation->get_downloadable() ); ?>>
						<label for="variable_is_downloadable_<?php echo esc_attr( $loop ); ?>"><?php esc_html_e( 'Downloadable', 'storesuite' ); ?></label>
					</div>
				</div>
				<div class="col-md-3">
					<div class="storesuite-form-group storesuite-form-switch">
						<input type="checkbox" class="variable_manage_stock" id="variable_manage_stock_<?php echo esc_attr( $loop ); ?>"
							name="variable_manage_stock[<?php echo esc_attr( $loop ); ?>]" value="1"
							<?php checked( $variation->get_manage_stock() ); ?>>
						<label for="variable_manage_stock_<?php echo esc_attr( $loop ); ?>"><?php esc_html_e( 'Manage stock', 'storesuite' ); ?></label>
					</div>
				</div>
			</div>

			<div class="row">
				<!-- Image -->
				<div class="col-md-2">
					<div class="storesuite-form-group storesuite-variation-image-group">
						<label><?php esc_html_e( 'Image', 'storesuite' ); ?></label>
						<div class="storesuite-variation-image-upload" data-loop="<?php echo esc_attr( $loop ); ?>">
							<img src="<?php echo esc_url( $variation_thumb ); ?>" width="60" height="60" alt="">
							<input type="hidden" name="variable_image_id[<?php echo esc_attr( $loop ); ?>]" value="<?php echo esc_attr( $variation->get_image_id() ); ?>">
							<a href="#" class="storesuite-remove-variation-image" <?php echo $variation->get_image_id() ? '' : 'style="display:none;"'; ?>><?php esc_html_e( 'Remove image', 'storesuite' ); ?></a>
						</div>
					</div>
				</div>
				<!-- Regular price -->
				<div class="col-md-5">
					<div class="storesuite-form-group">
						<label for="variable_regular_price_<?php echo esc_attr( $loop ); ?>">
							<?php /* translators: %s: currency symbol */ printf( esc_html__( 'Regular price (%s)', 'storesuite' ), esc_html( get_woocommerce_currency_symbol() ) ); ?>
						</label>
						<input type="text" class="storesuite-form-control wc_input_price"
							id="variable_regular_price_<?php echo esc_attr( $loop ); ?>"
							name="variable_regular_price[<?php echo esc_attr( $loop ); ?>]"
							value="<?php echo esc_attr( wc_format_localized_price( $variation->get_regular_price() ) ); ?>">
					</div>
				</div>
				<!-- Sale price -->
				<div class="col-md-5">
					<div class="storesuite-form-group">
						<label for="variable_sale_price_<?php echo esc_attr( $loop ); ?>">
							<?php /* translators: %s: currency symbol */ printf( esc_html__( 'Sale price (%s)', 'storesuite' ), esc_html( get_woocommerce_currency_symbol() ) ); ?>
						</label>
						<input type="text" class="storesuite-form-control wc_input_price"
							id="variable_sale_price_<?php echo esc_attr( $loop ); ?>"
							name="variable_sale_price[<?php echo esc_attr( $loop ); ?>]"
							value="<?php echo esc_attr( wc_format_localized_price( $variation->get_sale_price() ) ); ?>">
					</div>
				</div>
			</div>

			<div class="row">
				<!-- SKU -->
				<div class="col-md-4">
					<div class="storesuite-form-group">
						<label for="variable_sku_<?php echo esc_attr( $loop ); ?>"><?php esc_html_e( 'SKU', 'storesuite' ); ?></label>
						<input type="text" class="storesuite-form-control"
							id="variable_sku_<?php echo esc_attr( $loop ); ?>"
							name="variable_sku[<?php echo esc_attr( $loop ); ?>]"
							value="<?php echo esc_attr( $variation->get_sku() ); ?>">
					</div>
				</div>
				<!-- Stock status -->
				<div class="col-md-4">
					<div class="storesuite-form-group">
						<label for="variable_stock_status_<?php echo esc_attr( $loop ); ?>"><?php esc_html_e( 'Stock status', 'storesuite' ); ?></label>
						<select class="storesuite-form-control"
							id="variable_stock_status_<?php echo esc_attr( $loop ); ?>"
							name="variable_stock_status[<?php echo esc_attr( $loop ); ?>]">
							<option value="instock" <?php selected( $variation->get_stock_status(), 'instock' ); ?>><?php esc_html_e( 'In stock', 'storesuite' ); ?></option>
							<option value="outofstock" <?php selected( $variation->get_stock_status(), 'outofstock' ); ?>><?php esc_html_e( 'Out of stock', 'storesuite' ); ?></option>
							<option value="onbackorder" <?php selected( $variation->get_stock_status(), 'onbackorder' ); ?>><?php esc_html_e( 'On backorder', 'storesuite' ); ?></option>
						</select>
					</div>
				</div>
				<!-- Stock quantity -->
				<div class="col-md-4 show_if_variation_manage_stock" <?php echo $variation->get_manage_stock() ? '' : 'style="display:none;"'; ?>>
					<div class="storesuite-form-group">
						<label for="variable_stock_qty_<?php echo esc_attr( $loop ); ?>"><?php esc_html_e( 'Stock qty', 'storesuite' ); ?></label>
						<input type="number" class="storesuite-form-control" step="any"
							id="variable_stock_qty_<?php echo esc_attr( $loop ); ?>"
							name="variable_stock_qty[<?php echo esc_attr( $loop ); ?>]"
							value="<?php echo esc_attr( $variation->get_stock_quantity() ); ?>">
					</div>
				</div>
			</div>

			<!-- Shipping dimensions (hidden when virtual) -->
			<div class="row hide_if_variation_virtual" <?php echo $variation->get_virtual() ? 'style="display:none;"' : ''; ?>>
				<div class="col-md-3">
					<div class="storesuite-form-group">
						<label for="variable_weight_<?php echo esc_attr( $loop ); ?>">
							<?php /* translators: %s: weight unit */ printf( esc_html__( 'Weight (%s)', 'storesuite' ), esc_html( get_option( 'woocommerce_weight_unit' ) ) ); ?>
						</label>
						<input type="text" class="storesuite-form-control"
							id="variable_weight_<?php echo esc_attr( $loop ); ?>"
							name="variable_weight[<?php echo esc_attr( $loop ); ?>]"
							value="<?php echo esc_attr( $variation->get_weight() ); ?>">
					</div>
				</div>
				<div class="col-md-3">
					<div class="storesuite-form-group">
						<label for="variable_length_<?php echo esc_attr( $loop ); ?>">
							<?php /* translators: %s: dimension unit */ printf( esc_html__( 'Length (%s)', 'storesuite' ), esc_html( get_option( 'woocommerce_dimension_unit' ) ) ); ?>
						</label>
						<input type="text" class="storesuite-form-control"
							id="variable_length_<?php echo esc_attr( $loop ); ?>"
							name="variable_length[<?php echo esc_attr( $loop ); ?>]"
							value="<?php echo esc_attr( $variation->get_length() ); ?>">
					</div>
				</div>
				<div class="col-md-3">
					<div class="storesuite-form-group">
						<label for="variable_width_<?php echo esc_attr( $loop ); ?>">
							<?php /* translators: %s: dimension unit */ printf( esc_html__( 'Width (%s)', 'storesuite' ), esc_html( get_option( 'woocommerce_dimension_unit' ) ) ); ?>
						</label>
						<input type="text" class="storesuite-form-control"
							id="variable_width_<?php echo esc_attr( $loop ); ?>"
							name="variable_width[<?php echo esc_attr( $loop ); ?>]"
							value="<?php echo esc_attr( $variation->get_width() ); ?>">
					</div>
				</div>
				<div class="col-md-3">
					<div class="storesuite-form-group">
						<label for="variable_height_<?php echo esc_attr( $loop ); ?>">
							<?php /* translators: %s: dimension unit */ printf( esc_html__( 'Height (%s)', 'storesuite' ), esc_html( get_option( 'woocommerce_dimension_unit' ) ) ); ?>
						</label>
						<input type="text" class="storesuite-form-control"
							id="variable_height_<?php echo esc_attr( $loop ); ?>"
							name="variable_height[<?php echo esc_attr( $loop ); ?>]"
							value="<?php echo esc_attr( $variation->get_height() ); ?>">
					</div>
				</div>
			</div>

			<div class="row">
				<!-- Description -->
				<div class="col-md-12">
					<div class="storesuite-form-group">
						<label for="variable_description_<?php echo esc_attr( $loop ); ?>"><?php esc_html_e( 'Description', 'storesuite' ); ?></label>
						<textarea class="storesuite-form-control" rows="2"
							id="variable_description_<?php echo esc_attr( $loop ); ?>"
							name="variable_description[<?php echo esc_attr( $loop ); ?>]"><?php echo esc_textarea( $variation->get_description() ); ?></textarea>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
