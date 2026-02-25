<?php
/**
 * Single variation row (variable product).
 *
 * @package StoreSuite
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$parent_data     = isset( $parent_data ) ? $parent_data : array();
$variation_data  = isset( $variation_data ) ? $variation_data : array();
$_sku            = isset( $_sku ) ? $_sku : '';
$_stock          = isset( $_stock ) ? $_stock : '';
$_manage_stock   = isset( $_manage_stock ) ? $_manage_stock : '';
$_stock_status   = isset( $_stock_status ) ? $_stock_status : 'instock';
$_backorders     = isset( $_backorders ) ? $_backorders : 'no';
$_regular_price  = isset( $_regular_price ) ? $_regular_price : '';
$_sale_price     = isset( $_sale_price ) ? $_sale_price : '';
$_sale_price_dates_from = isset( $_sale_price_dates_from ) ? $_sale_price_dates_from : '';
$_sale_price_dates_to   = isset( $_sale_price_dates_to ) ? $_sale_price_dates_to : '';
$_weight         = isset( $_weight ) ? $_weight : '';
$_length         = isset( $_length ) ? $_length : '';
$_width          = isset( $_width ) ? $_width : '';
$_height         = isset( $_height ) ? $_height : '';
$_variation_description = isset( $_variation_description ) ? $_variation_description : '';
$_tax_class       = isset( $variation_data['_tax_class'][0] ) ? $variation_data['_tax_class'][0] : null;
$shipping_class  = isset( $shipping_class ) ? $shipping_class : '';
$image           = isset( $image ) ? $image : wc_placeholder_img_src();
$_thumbnail_id   = isset( $_thumbnail_id ) ? $_thumbnail_id : 0;
$tax_class_options = isset( $parent_data['tax_class_options'] ) ? $parent_data['tax_class_options'] : array( '' => __( 'Standard', 'storesuite' ) );
$_sale_price_dates_from_formatted = is_numeric( $_sale_price_dates_from ) ? date_i18n( 'Y-m-d', $_sale_price_dates_from ) : $_sale_price_dates_from;
$_sale_price_dates_to_formatted   = is_numeric( $_sale_price_dates_to ) ? date_i18n( 'Y-m-d', $_sale_price_dates_to ) : $_sale_price_dates_to;
?>
<div class="storesuite-variation woocommerce_variation wc-metabox" data-variation_id="<?php echo esc_attr( $variation_id ); ?>">
	<h3 class="storesuite-variation-heading">
		<button type="button" class="storesuite-remove-variation button-link" rel="<?php echo absint( $variation_id ); ?>"><?php esc_html_e( 'Remove', 'storesuite' ); ?></button>
		<span class="handlediv" aria-expanded="false" title="<?php esc_attr_e( 'Click to toggle', 'storesuite' ); ?>"></span>
		<strong>#<?php echo esc_html( $variation_id ); ?> — </strong>
		<?php
		foreach ( $parent_data['attributes'] as $attribute ) {
			if ( empty( $attribute['is_variation'] ) ) {
				continue;
			}
			$attr_key   = 'attribute_' . sanitize_title( $attribute['name'] );
			$selected   = isset( $variation_data[ $attr_key ][0] ) ? $variation_data[ $attr_key ][0] : '';
			$label      = wc_attribute_label( $attribute['name'] );
			?>
			<select name="<?php echo esc_attr( $attr_key ); ?>[<?php echo (int) $loop; ?>]" class="msf-form-control variation-attr">
				<option value=""><?php echo esc_html( sprintf( __( 'Any %s…', 'storesuite' ), $label ) ); ?></option>
				<?php
				if ( ! empty( $attribute['is_taxonomy'] ) ) {
					$terms = wp_get_post_terms( $parent_data['id'], $attribute['name'] );
					foreach ( $terms as $term ) {
						echo '<option value="' . esc_attr( $term->slug ) . '" ' . selected( $selected, $term->slug, false ) . '>' . esc_html( $term->name ) . '</option>';
					}
				} else {
					$options = wc_get_text_attributes( $attribute['value'] );
					foreach ( $options as $opt ) {
						$opt_selected = sanitize_title( $selected ) === $selected
							? selected( $selected, sanitize_title( $opt ), false )
							: selected( $selected, $opt, false );
						echo '<option value="' . esc_attr( $opt ) . '" ' . $opt_selected . '>' . esc_html( apply_filters( 'woocommerce_variation_option_name', $opt ) ) . '</option>';
					}
				}
				?>
			</select>
			<?php
		}
		?>
		<input type="hidden" name="variable_post_id[<?php echo (int) $loop; ?>]" value="<?php echo esc_attr( $variation_id ); ?>" />
		<input type="hidden" class="variation_menu_order" name="variation_menu_order[<?php echo (int) $loop; ?>]" value="<?php echo (int) $loop; ?>" />
	</h3>
	<div class="storesuite-variation-content wc-metabox-content" style="display:none;">
		<div class="row">
			<div class="col-md-4">
				<div class="msf-form-group">
					<label><?php esc_html_e( 'Variation image', 'storesuite' ); ?></label>
					<div class="storesuite-variation-image-wrap">
						<a href="#" class="storesuite-upload-variation-image <?php echo $_thumbnail_id ? 'has-image' : ''; ?>" rel="<?php echo absint( $variation_id ); ?>">
							<img src="<?php echo esc_url( $image ); ?>" alt="" width="130" height="130" />
						</a>
						<input type="hidden" name="upload_image_id[<?php echo (int) $loop; ?>]" class="upload_image_id" value="<?php echo esc_attr( $_thumbnail_id ); ?>" />
					</div>
				</div>
				<div class="msf-form-group">
					<label><input type="checkbox" name="variable_enabled[<?php echo (int) $loop; ?>]" value="1" <?php checked( $variation->post_status, 'publish' ); ?> /> <?php esc_html_e( 'Enabled', 'storesuite' ); ?></label>
				</div>
			</div>
			<div class="col-md-8">
				<?php if ( wc_product_sku_enabled() ) : ?>
				<div class="msf-form-group">
					<label><?php esc_html_e( 'SKU', 'storesuite' ); ?></label>
					<input type="text" class="msf-form-control" name="variable_sku[<?php echo (int) $loop; ?>]" value="<?php echo esc_attr( $_sku ); ?>" placeholder="<?php echo esc_attr( $parent_data['sku'] ); ?>" />
				</div>
				<?php else : ?>
					<input type="hidden" name="variable_sku[<?php echo (int) $loop; ?>]" value="" />
				<?php endif; ?>
				<div class="row variable_pricing">
					<div class="col-md-6">
						<div class="msf-form-group">
							<label><?php echo esc_html__( 'Regular price', 'storesuite' ) . ' (' . get_woocommerce_currency_symbol() . ')'; ?></label>
							<input type="text" class="msf-form-control wc_input_price" name="variable_regular_price[<?php echo (int) $loop; ?>]" value="<?php echo esc_attr( $_regular_price ); ?>" placeholder="<?php esc_attr_e( 'Variation price (required)', 'storesuite' ); ?>" />
						</div>
					</div>
					<div class="col-md-6">
						<div class="msf-form-group">
							<label><?php echo esc_html__( 'Sale price', 'storesuite' ) . ' (' . get_woocommerce_currency_symbol() . ')'; ?></label>
							<input type="text" class="msf-form-control wc_input_price" name="variable_sale_price[<?php echo (int) $loop; ?>]" value="<?php echo esc_attr( $_sale_price ); ?>" />
						</div>
					</div>
				</div>
				<div class="row sale_price_dates_fields variation-sale-dates" style="display:none;">
					<div class="col-md-6">
						<div class="msf-form-group">
							<label><?php esc_html_e( 'Sale start date', 'storesuite' ); ?></label>
							<input type="text" class="msf-form-control sale_price_dates_from" name="variable_sale_price_dates_from[<?php echo (int) $loop; ?>]" value="<?php echo esc_attr( $_sale_price_dates_from_formatted ); ?>" placeholder="YYYY-MM-DD" />
						</div>
					</div>
					<div class="col-md-6">
						<div class="msf-form-group">
							<label><?php esc_html_e( 'Sale end date', 'storesuite' ); ?></label>
							<input type="text" class="msf-form-control sale_price_dates_to" name="variable_sale_price_dates_to[<?php echo (int) $loop; ?>]" value="<?php echo esc_attr( $_sale_price_dates_to_formatted ); ?>" placeholder="YYYY-MM-DD" />
						</div>
					</div>
				</div>
				<?php if ( 'yes' === get_option( 'woocommerce_manage_stock' ) ) : ?>
				<div class="row">
					<div class="col-md-6">
						<div class="msf-form-group">
							<label><input type="checkbox" class="variable_manage_stock" name="variable_manage_stock[<?php echo (int) $loop; ?>]" value="yes" <?php checked( $_manage_stock, 'yes' ); ?> /> <?php esc_html_e( 'Manage stock?', 'storesuite' ); ?></label>
						</div>
					</div>
					<div class="col-md-6 show_if_variation_manage_stock" style="<?php echo 'yes' !== $_manage_stock ? 'display:none;' : ''; ?>">
						<div class="msf-form-group">
							<label><?php esc_html_e( 'Stock quantity', 'storesuite' ); ?></label>
							<input type="number" class="msf-form-control" name="variable_stock[<?php echo (int) $loop; ?>]" value="<?php echo esc_attr( $_stock ); ?>" step="1" />
						</div>
					</div>
				</div>
				<div class="row">
					<div class="col-md-6">
						<div class="msf-form-group">
							<label><?php esc_html_e( 'Stock status', 'storesuite' ); ?></label>
							<select name="variable_stock_status[<?php echo (int) $loop; ?>]" class="msf-form-control">
								<?php foreach ( wc_get_product_stock_status_options() as $key => $label ) : ?>
									<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $_stock_status, $key ); ?>><?php echo esc_html( $label ); ?></option>
								<?php endforeach; ?>
							</select>
						</div>
					</div>
					<div class="col-md-6 show_if_variation_manage_stock" style="<?php echo 'yes' !== $_manage_stock ? 'display:none;' : ''; ?>">
						<div class="msf-form-group">
							<label><?php esc_html_e( 'Allow backorders?', 'storesuite' ); ?></label>
							<select name="variable_backorders[<?php echo (int) $loop; ?>]" class="msf-form-control">
								<option value="no" <?php selected( $_backorders, 'no' ); ?>><?php esc_html_e( 'Do not allow', 'storesuite' ); ?></option>
								<option value="notify" <?php selected( $_backorders, 'notify' ); ?>><?php esc_html_e( 'Allow but notify customer', 'storesuite' ); ?></option>
								<option value="yes" <?php selected( $_backorders, 'yes' ); ?>><?php esc_html_e( 'Allow', 'storesuite' ); ?></option>
							</select>
						</div>
					</div>
				</div>
				<?php else : ?>
					<input type="hidden" name="variable_stock_status[<?php echo (int) $loop; ?>]" value="<?php echo esc_attr( $_stock_status ); ?>" />
					<input type="hidden" name="variable_stock[<?php echo (int) $loop; ?>]" value="" />
					<input type="hidden" name="variable_backorders[<?php echo (int) $loop; ?>]" value="no" />
				<?php endif; ?>
				<?php if ( wc_product_weight_enabled() || wc_product_dimensions_enabled() ) : ?>
				<div class="row">
					<?php if ( wc_product_weight_enabled() ) : ?>
					<div class="col-md-6">
						<div class="msf-form-group">
							<label><?php echo esc_html__( 'Weight', 'storesuite' ) . ' (' . esc_html( get_option( 'woocommerce_weight_unit' ) ) . ')'; ?></label>
							<input type="text" class="msf-form-control" name="variable_weight[<?php echo (int) $loop; ?>]" value="<?php echo esc_attr( $_weight ); ?>" placeholder="<?php echo esc_attr( $parent_data['weight'] ); ?>" />
						</div>
					</div>
					<?php endif; ?>
					<?php if ( wc_product_dimensions_enabled() ) : ?>
					<div class="col-md-6">
						<div class="msf-form-group">
							<label><?php echo esc_html__( 'Dimensions (L×W×H)', 'storesuite' ) . ' (' . esc_html( get_option( 'woocommerce_dimension_unit' ) ) . ')'; ?></label>
							<div class="row">
								<div class="col-md-4"><input type="text" class="msf-form-control" name="variable_length[<?php echo (int) $loop; ?>]" value="<?php echo esc_attr( $_length ); ?>" placeholder="<?php echo esc_attr( $parent_data['length'] ); ?>" /></div>
								<div class="col-md-4"><input type="text" class="msf-form-control" name="variable_width[<?php echo (int) $loop; ?>]" value="<?php echo esc_attr( $_width ); ?>" placeholder="<?php echo esc_attr( $parent_data['width'] ); ?>" /></div>
								<div class="col-md-4"><input type="text" class="msf-form-control" name="variable_height[<?php echo (int) $loop; ?>]" value="<?php echo esc_attr( $_height ); ?>" placeholder="<?php echo esc_attr( $parent_data['height'] ); ?>" /></div>
							</div>
						</div>
					</div>
					<?php endif; ?>
				</div>
				<?php endif; ?>
				<div class="msf-form-group">
					<label><?php esc_html_e( 'Shipping class', 'storesuite' ); ?></label>
					<?php
					wp_dropdown_categories(
						array(
							'taxonomy'         => 'product_shipping_class',
							'hide_empty'       => 0,
							'show_option_none' => __( 'Same as parent', 'storesuite' ),
							'name'             => 'variable_shipping_class[' . (int) $loop . ']',
							'class'            => 'msf-form-control',
							'selected'         => $shipping_class,
							'echo'             => 1,
						)
					);
					?>
				</div>
				<?php if ( wc_tax_enabled() ) : ?>
				<div class="msf-form-group">
					<label><?php esc_html_e( 'Tax class', 'storesuite' ); ?></label>
					<select name="variable_tax_class[<?php echo (int) $loop; ?>]" class="msf-form-control">
						<option value="parent" <?php selected( is_null( $_tax_class ), true ); ?>><?php esc_html_e( 'Same as parent', 'storesuite' ); ?></option>
						<?php foreach ( $tax_class_options as $key => $label ) : ?>
							<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $key === $_tax_class, true ); ?>><?php echo esc_html( $label ); ?></option>
						<?php endforeach; ?>
					</select>
				</div>
				<?php endif; ?>
				<div class="msf-form-group">
					<label><?php esc_html_e( 'Variation description', 'storesuite' ); ?></label>
					<textarea class="msf-form-control" name="variable_description[<?php echo (int) $loop; ?>]" rows="3" style="width:100%;"><?php echo esc_textarea( $_variation_description ); ?></textarea>
				</div>
			</div>
		</div>
	</div>
</div>
