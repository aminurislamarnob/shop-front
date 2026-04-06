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

$variation_image_id = $variation->get_image_id( 'edit' );
$variation_thumb    = $variation_image_id ? wp_get_attachment_image_url( $variation_image_id, 'thumbnail' ) : '';

$sale_from = $variation->get_date_on_sale_from( 'edit' );
$sale_to   = $variation->get_date_on_sale_to( 'edit' );
$sale_price_dates_from = ( $sale_from instanceof \DateTimeInterface ) ? $sale_from->format( 'Y-m-d' ) : '';
$sale_price_dates_to   = ( $sale_to instanceof \DateTimeInterface ) ? $sale_to->format( 'Y-m-d' ) : '';

$manage_stock_globally = 'yes' === get_option( 'woocommerce_manage_stock' );
$weight_unit           = get_option( 'woocommerce_weight_unit', 'kg' );
$dimension_unit        = get_option( 'woocommerce_dimension_unit', 'cm' );
$parent_weight         = $parent->get_weight();
$parent_length         = $parent->get_length();
$parent_width          = $parent->get_width();
$parent_height         = $parent->get_height();
?>
<div class="storesuite-variation-row storesuite-card storesuite-mb-12" data-variation-id="<?php echo esc_attr( $variation_id ); ?>">
	<div class="storesuite-variation-header" style="display:flex; justify-content:space-between; align-items:center; padding:10px 16px; cursor:pointer;">
		<strong>
			<?php
			/* translators: %d: variation ID */
			printf( esc_html__( 'Variation #%d', 'storesuite' ), absint( $variation_id ) );
			?>
		</strong>
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
							<?php /* translators: %s: attribute label (e.g. Color, Size). */ ?>
							<option value=""><?php printf( esc_html__( 'Any %s', 'storesuite' ), esc_html( wc_attribute_label( $attribute->get_name() ) ) ); ?></option>
							<?php if ( $attribute->is_taxonomy() ) : ?>
								<?php foreach ( $attribute->get_terms() as $attr_term ) : ?>
									<option value="<?php echo esc_attr( $attr_term->slug ); ?>" <?php selected( $current_value, $attr_term->slug ); ?>>
										<?php echo esc_html( $attr_term->name ); ?>
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

		<div class="row storesuite-mb-12">
			<div class="col-md-12">
				<label class="storesuite-mb-8" style="display:block;"><?php esc_html_e( 'Variation image', 'storesuite' ); ?></label>
				<div class="storesuite-variation-image-uploader image-drop-container<?php echo $variation_thumb ? ' image-drop-bg' : ''; ?>"
					style="max-width:120px; cursor:pointer;"
					data-loop="<?php echo esc_attr( $loop ); ?>">
					<input type="hidden" class="storesuite-variation-image-id" name="upload_image_id[<?php echo esc_attr( $loop ); ?>]" value="<?php echo esc_attr( $variation_image_id ? $variation_image_id : '' ); ?>">
					<div class="storesuite-variation-image-thumb preview-image">
						<?php if ( $variation_thumb ) : ?>
							<img src="<?php echo esc_url( $variation_thumb ); ?>" alt="">
						<?php endif; ?>
					</div>
					<div class="image-drop-text">
						<span class="storesuite-variation-image-label">
							<?php echo $variation_thumb ? esc_html__( 'Remove image', 'storesuite' ) : esc_html__( 'Upload image', 'storesuite' ); ?>
						</span>
					</div>
				</div>
			</div>
		</div>

		<div class="row">
			<?php if ( wc_product_sku_enabled() ) : ?>
				<div class="col-md-6">
					<div class="storesuite-form-group">
						<label><?php esc_html_e( 'SKU', 'storesuite' ); ?></label>
						<input type="text" class="storesuite-form-control" name="variable_sku[<?php echo esc_attr( $loop ); ?>]" value="<?php echo esc_attr( $variation->get_sku( 'edit' ) ); ?>">
					</div>
				</div>
			<?php endif; ?>
			<div class="col-md-6">
				<div class="storesuite-form-group">
					<label for="variable_global_unique_id_<?php echo esc_attr( $variation_id ); ?>"><?php esc_html_e( 'GTIN, UPC, EAN, or ISBN', 'storesuite' ); ?></label>
					<input type="text" class="storesuite-form-control" id="variable_global_unique_id_<?php echo esc_attr( $variation_id ); ?>" name="variable_global_unique_id[<?php echo esc_attr( $loop ); ?>]" value="<?php echo esc_attr( $variation->get_global_unique_id( 'edit' ) ); ?>">
				</div>
			</div>
		</div>

		<div class="row">
			<div class="col-md-3">
				<div class="storesuite-form-group storesuite-form-switch">
					<?php $enabled_id = 'storesuite-variation-enabled-' . absint( $variation_id ); ?>
					<input type="checkbox" class="storesuite-form-control" id="<?php echo esc_attr( $enabled_id ); ?>" name="variable_enabled[<?php echo esc_attr( $loop ); ?>]" value="1" <?php checked( $enabled ); ?>>
					<label for="<?php echo esc_attr( $enabled_id ); ?>"><?php esc_html_e( 'Enabled', 'storesuite' ); ?></label>
				</div>
			</div>
			<div class="col-md-3">
				<div class="storesuite-form-group storesuite-form-switch">
					<?php $dl_id = 'storesuite-variation-dl-' . absint( $variation_id ); ?>
					<input type="checkbox" class="storesuite-form-control variable_is_downloadable" id="<?php echo esc_attr( $dl_id ); ?>" name="variable_is_downloadable[<?php echo esc_attr( $loop ); ?>]" value="1" <?php checked( $variation->get_downloadable( 'edit' ) ); ?>>
					<label for="<?php echo esc_attr( $dl_id ); ?>"><?php esc_html_e( 'Downloadable', 'storesuite' ); ?></label>
				</div>
			</div>
			<div class="col-md-3">
				<div class="storesuite-form-group storesuite-form-switch">
					<?php $virt_id = 'storesuite-variation-virtual-' . absint( $variation_id ); ?>
					<input type="checkbox" class="storesuite-form-control variable_is_virtual" id="<?php echo esc_attr( $virt_id ); ?>" name="variable_is_virtual[<?php echo esc_attr( $loop ); ?>]" value="1" <?php checked( $variation->get_virtual( 'edit' ) ); ?>>
					<label for="<?php echo esc_attr( $virt_id ); ?>"><?php esc_html_e( 'Virtual', 'storesuite' ); ?></label>
				</div>
			</div>
			<?php if ( $manage_stock_globally ) : ?>
				<div class="col-md-3">
					<div class="storesuite-form-group storesuite-form-switch">
						<?php $manage_stock_id = 'storesuite-variation-manage-stock-' . absint( $variation_id ); ?>
						<input type="checkbox" class="storesuite-form-control variable_manage_stock" id="<?php echo esc_attr( $manage_stock_id ); ?>" name="variable_manage_stock[<?php echo esc_attr( $loop ); ?>]" value="1" <?php checked( $variation->get_manage_stock() ); ?>>
						<label for="<?php echo esc_attr( $manage_stock_id ); ?>"><?php esc_html_e( 'Manage stock', 'storesuite' ); ?></label>
					</div>
				</div>
			<?php endif; ?>
		</div>

		<div class="storesuite-sale-schedule-scope">
			<div class="row">
				<div class="col-md-6">
					<div class="storesuite-form-group">
						<?php
						/* translators: %s: currency symbol */
						$reg_label = sprintf( esc_html__( 'Regular price (%s)', 'storesuite' ), get_woocommerce_currency_symbol() );
						?>
						<label><?php echo esc_html( $reg_label ); ?></label>
						<input type="text" class="storesuite-form-control" name="variable_regular_price[<?php echo esc_attr( $loop ); ?>]" value="<?php echo esc_attr( wc_format_localized_price( $variation->get_regular_price( 'edit' ) ) ); ?>">
					</div>
				</div>
				<div class="col-md-6">
					<div class="storesuite-form-group">
						<div class="row">
							<div class="col-md-8">
								<?php
								/* translators: %s: currency symbol */
								$sale_label = sprintf( esc_html__( 'Sale price (%s)', 'storesuite' ), get_woocommerce_currency_symbol() );
								?>
								<label><?php echo esc_html( $sale_label ); ?></label>
							</div>
							<div class="col-md-4 text-right">
								<a href="#" class="sale_schedule"><?php esc_html_e( 'Schedule', 'storesuite' ); ?></a>
								<a href="#" class="cancel_sale_schedule" style="<?php echo ( $sale_price_dates_from || $sale_price_dates_to ) ? '' : 'display:none;'; ?>"><?php esc_html_e( 'Cancel', 'storesuite' ); ?></a>
							</div>
						</div>
						<input type="text" class="storesuite-form-control" name="variable_sale_price[<?php echo esc_attr( $loop ); ?>]" value="<?php echo esc_attr( wc_format_localized_price( $variation->get_sale_price( 'edit' ) ) ); ?>">
					</div>
				</div>
				<div class="col-md-12">
					<div class="row sale_price_dates_fields" style="<?php echo ( $sale_price_dates_from || $sale_price_dates_to ) ? '' : 'display:none;'; ?>">
						<div class="col-md-6">
							<div class="storesuite-form-group">
								<label><?php esc_html_e( 'Sale start date', 'storesuite' ); ?></label>
								<input type="text" class="storesuite-form-control sale_price_dates_from" name="variable_sale_price_dates_from[<?php echo esc_attr( $loop ); ?>]" value="<?php echo esc_attr( $sale_price_dates_from ); ?>" placeholder="<?php esc_attr_e( 'YYYY-MM-DD', 'storesuite' ); ?>" maxlength="10" autocomplete="off">
							</div>
						</div>
						<div class="col-md-6">
							<div class="storesuite-form-group">
								<label><?php esc_html_e( 'Sale end date', 'storesuite' ); ?></label>
								<input type="text" class="storesuite-form-control sale_price_dates_to" name="variable_sale_price_dates_to[<?php echo esc_attr( $loop ); ?>]" value="<?php echo esc_attr( $sale_price_dates_to ); ?>" placeholder="<?php esc_attr_e( 'YYYY-MM-DD', 'storesuite' ); ?>" maxlength="10" autocomplete="off">
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>

		<?php if ( $manage_stock_globally ) : ?>
			<div class="row show_if_variation_manage_stock" style="<?php echo $variation->get_manage_stock() ? '' : 'display:none;'; ?>">
				<div class="col-md-4">
					<div class="storesuite-form-group">
						<label><?php esc_html_e( 'Stock quantity', 'storesuite' ); ?></label>
						<input type="number" class="storesuite-form-control" name="variable_stock[<?php echo esc_attr( $loop ); ?>]" step="any" value="<?php echo esc_attr( wc_stock_amount( $variation->get_stock_quantity( 'edit' ) ) ); ?>">
					</div>
				</div>
				<div class="col-md-4">
					<div class="storesuite-form-group">
						<label><?php esc_html_e( 'Allow backorders?', 'storesuite' ); ?></label>
						<select class="storesuite-form-control" name="variable_backorders[<?php echo esc_attr( $loop ); ?>]">
							<?php foreach ( wc_get_product_backorder_options() as $key => $blabel ) : ?>
								<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $variation->get_backorders( 'edit' ), $key ); ?>><?php echo esc_html( $blabel ); ?></option>
							<?php endforeach; ?>
						</select>
					</div>
				</div>
				<div class="col-md-4">
					<div class="storesuite-form-group">
						<label><?php esc_html_e( 'Low stock threshold', 'storesuite' ); ?></label>
						<input type="number" class="storesuite-form-control" name="variable_low_stock_amount[<?php echo esc_attr( $loop ); ?>]" step="any" value="<?php echo esc_attr( wc_stock_amount( $variation->get_low_stock_amount( 'edit' ) ) ); ?>">
					</div>
				</div>
			</div>
		<?php endif; ?>

		<div class="row">
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
			<?php if ( wc_product_weight_enabled() ) : ?>
				<div class="col-md-4 hide_if_variation_virtual" style="<?php echo $variation->get_virtual( 'edit' ) ? 'display:none;' : ''; ?>">
					<div class="storesuite-form-group">
						<?php
						/* translators: %s: weight unit */
						$wlabel = sprintf( esc_html__( 'Weight (%s)', 'storesuite' ), esc_html( $weight_unit ) );
						?>
						<label><?php echo esc_html( $wlabel ); ?></label>
						<input type="text" class="storesuite-form-control" name="variable_weight[<?php echo esc_attr( $loop ); ?>]" value="<?php echo esc_attr( wc_format_localized_decimal( $variation->get_weight( 'edit' ) ) ); ?>" placeholder="<?php echo esc_attr( wc_format_localized_decimal( $parent_weight ) ); ?>">
					</div>
				</div>
			<?php endif; ?>
		</div>

		<?php if ( wc_product_dimensions_enabled() ) : ?>
			<div class="row hide_if_variation_virtual" style="<?php echo $variation->get_virtual( 'edit' ) ? 'display:none;' : ''; ?>">
				<div class="col-md-12">
					<div class="storesuite-form-group">
						<?php
						/* translators: %s: dimension unit */
						$dlabel = sprintf( esc_html__( 'Dimensions (L×W×H) (%s)', 'storesuite' ), esc_html( $dimension_unit ) );
						?>
						<label><?php echo esc_html( $dlabel ); ?></label>
						<div class="row">
							<div class="col-md-4">
								<input type="text" class="storesuite-form-control" name="variable_length[<?php echo esc_attr( $loop ); ?>]" placeholder="<?php esc_attr_e( 'Length', 'storesuite' ); ?>" value="<?php echo esc_attr( wc_format_localized_decimal( $variation->get_length( 'edit' ) ) ); ?>">
							</div>
							<div class="col-md-4">
								<input type="text" class="storesuite-form-control" name="variable_width[<?php echo esc_attr( $loop ); ?>]" placeholder="<?php esc_attr_e( 'Width', 'storesuite' ); ?>" value="<?php echo esc_attr( wc_format_localized_decimal( $variation->get_width( 'edit' ) ) ); ?>">
							</div>
							<div class="col-md-4">
								<input type="text" class="storesuite-form-control" name="variable_height[<?php echo esc_attr( $loop ); ?>]" placeholder="<?php esc_attr_e( 'Height', 'storesuite' ); ?>" value="<?php echo esc_attr( wc_format_localized_decimal( $variation->get_height( 'edit' ) ) ); ?>">
							</div>
						</div>
						<small class="storesuite-text-muted"><?php esc_html_e( 'Leave blank to match the parent product dimensions where applicable.', 'storesuite' ); ?></small>
					</div>
				</div>
			</div>
		<?php endif; ?>
	</div>
</div>
