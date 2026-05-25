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

// Cost of Goods Sold feature availability.
$cogs_is_enabled = wc_get_container()->get( \Automattic\WooCommerce\Internal\CostOfGoodsSold\CostOfGoodsSoldController::class )->feature_is_enabled();
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
					// Only the terms assigned to this product's attribute, like the WooCommerce admin.
					$terms = wc_get_product_terms(
						$parent->get_id(),
						$attr_name,
						array( 'fields' => 'all' )
					);
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
			<a href="#" class="my-storesuite-button my-storesuite-button-soft storesuite-toggle-variation" aria-label="<?php esc_attr_e( 'Edit variation', 'storesuite' ); ?>" title="<?php esc_attr_e( 'Edit variation', 'storesuite' ); ?>">
				<svg xmlns="http://www.w3.org/2000/svg" id="Layer_1" data-name="Layer 1" viewBox="0 0 24 24" width="16" height="16" fill="currentColor"><path d="m18.813,10c.309,0,.601-.143.79-.387s.255-.562.179-.861c-.311-1.217-.945-2.329-1.833-3.217l-3.485-3.485c-1.322-1.322-3.08-2.05-4.95-2.05h-4.515C2.243,0,0,2.243,0,5v14c0,2.757,2.243,5,5,5h3c.552,0,1-.448,1-1s-.448-1-1-1h-3c-1.654,0-3-1.346-3-3V5c0-1.654,1.346-3,3-3h4.515c.163,0,.325.008.485.023v4.977c0,1.654,1.346,3,3,3h5.813Zm-6.813-3V2.659c.379.218.732.488,1.05.806l3.485,3.485c.314.314.583.668.803,1.05h-4.338c-.551,0-1-.449-1-1Zm11.122,4.879c-1.134-1.134-3.11-1.134-4.243,0l-6.707,6.707c-.755.755-1.172,1.76-1.172,2.829v1.586c0,.552.448,1,1,1h1.586c1.069,0,2.073-.417,2.828-1.172l6.707-6.707c.567-.567.879-1.32.879-2.122s-.312-1.555-.878-2.121Zm-1.415,2.828l-6.708,6.707c-.377.378-.879.586-1.414.586h-.586v-.586c0-.534.208-1.036.586-1.414l6.708-6.707c.377-.378,1.036-.378,1.414,0,.189.188.293.439.293.707s-.104.518-.293.707Z"/></svg>
			</a>
			<a href="#" class="my-storesuite-button my-storesuite-button-danger-soft storesuite-remove-variation" data-variation-id="<?php echo esc_attr( $variation_id ); ?>" aria-label="<?php esc_attr_e( 'Remove variation', 'storesuite' ); ?>" title="<?php esc_attr_e( 'Remove variation', 'storesuite' ); ?>">
				<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="16" height="16" fill="currentColor"><path d="M21,4H17.9A5.009,5.009,0,0,0,13,0H11A5.009,5.009,0,0,0,6.1,4H3A1,1,0,0,0,3,6H4V19a5.006,5.006,0,0,0,5,5h6a5.006,5.006,0,0,0,5-5V6h1a1,1,0,0,0,0-2ZM11,2h2a3.006,3.006,0,0,1,2.829,2H8.171A3.006,3.006,0,0,1,11,2Zm7,17a3,3,0,0,1-3,3H9a3,3,0,0,1-3-3V6H18Z"/><path d="M10,18a1,1,0,0,0,1-1V11a1,1,0,0,0-2,0v6A1,1,0,0,0,10,18Z"/><path d="M14,18a1,1,0,0,0,1-1V11a1,1,0,0,0-2,0v6A1,1,0,0,0,14,18Z"/></svg>
			</a>
		</span>
	</div>

	<!-- Body (collapsed by default) -->
	<div class="storesuite-variation-body" style="display:none;">
		<div class="storesuite-card-content">
			<div class="variable-switches-group">
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
			</div>

			<div class="row">
				<!-- Image -->
				<div class="col-md-2">
					<div class="storesuite-form-group storesuite-variation-image-group">
						<label><?php esc_html_e( 'Image', 'storesuite' ); ?></label>
						<div class="storesuite-variation-image-upload<?php echo $variation->get_image_id() ? ' has-variation-image' : ''; ?>" data-loop="<?php echo esc_attr( $loop ); ?>">
							<img src="<?php echo esc_url( $variation_thumb ); ?>" width="100" height="100" alt="">
							<input type="hidden" name="variable_image_id[<?php echo esc_attr( $loop ); ?>]" value="<?php echo esc_attr( $variation->get_image_id() ); ?>">
							<span class="storesuite-variation-image-actions">
								<a href="#" class="storesuite-upload-variation-image" aria-label="<?php esc_attr_e( 'Upload image', 'storesuite' ); ?>" title="<?php esc_attr_e( 'Upload image', 'storesuite' ); ?>">
									<svg xmlns="http://www.w3.org/2000/svg" id="Outline" viewBox="0 0 24 24" width="18" height="18" fill="currentColor"><path d="M18.4,7.379a1.128,1.128,0,0,1-.769-.754h0a8,8,0,1,0-15.1,5.237A1.046,1.046,0,0,1,2.223,13.1,5.5,5.5,0,0,0,.057,18.3,5.622,5.622,0,0,0,5.683,23H11a1,1,0,0,0,1-1h0a1,1,0,0,0-1-1H5.683a3.614,3.614,0,0,1-3.646-2.981,3.456,3.456,0,0,1,1.376-3.313A3.021,3.021,0,0,0,4.4,11.141a6.113,6.113,0,0,1-.073-4.126A5.956,5.956,0,0,1,9.215,3.05,6.109,6.109,0,0,1,9.987,3a5.984,5.984,0,0,1,5.756,4.28,2.977,2.977,0,0,0,2.01,1.99,5.934,5.934,0,0,1,.778,11.09.976.976,0,0,0-.531.888h0a.988.988,0,0,0,1.388.915c4.134-1.987,6.38-7.214,2.88-12.264A6.935,6.935,0,0,0,18.4,7.379Z"/><path d="M18.707,16.707a1,1,0,0,0,0-1.414l-1.586-1.586a3,3,0,0,0-4.242,0l-1.586,1.586a1,1,0,0,0,1.414,1.414L14,15.414V23a1,1,0,0,0,2,0V15.414l1.293,1.293a1,1,0,0,0,1.414,0Z"/></svg>
								</a>
								<a href="#" class="storesuite-remove-variation-image" aria-label="<?php esc_attr_e( 'Remove image', 'storesuite' ); ?>" title="<?php esc_attr_e( 'Remove image', 'storesuite' ); ?>">
									<svg xmlns="http://www.w3.org/2000/svg" id="Outline" viewBox="0 0 24 24" width="18" height="18" fill="currentColor"><path d="M18,6h0a1,1,0,0,0-1.414,0L12,10.586,7.414,6A1,1,0,0,0,6,6H6A1,1,0,0,0,6,7.414L10.586,12,6,16.586A1,1,0,0,0,6,18H6a1,1,0,0,0,1.414,0L12,13.414,16.586,18A1,1,0,0,0,18,18h0a1,1,0,0,0,0-1.414L13.414,12,18,7.414A1,1,0,0,0,18,6Z"/></svg>
								</a>
							</span>
						</div>
					</div>
				</div>
				<!-- SKU -->
				<div class="col-md-5">
					<div class="storesuite-form-group">
						<label for="variable_sku_<?php echo esc_attr( $loop ); ?>"><?php esc_html_e( 'SKU', 'storesuite' ); ?></label>
						<input type="text" class="storesuite-form-control"
							id="variable_sku_<?php echo esc_attr( $loop ); ?>"
							name="variable_sku[<?php echo esc_attr( $loop ); ?>]"
							value="<?php echo esc_attr( $variation->get_sku() ); ?>">
					</div>
				</div>
				<!-- GTIN, UPC, EAN, or ISBN -->
				<div class="col-md-5">
					<div class="storesuite-form-group">
						<label for="variable_global_unique_id_<?php echo esc_attr( $loop ); ?>"><?php esc_html_e( 'GTIN, UPC, EAN, or ISBN', 'storesuite' ); ?></label>
						<input type="text" class="storesuite-form-control"
							id="variable_global_unique_id_<?php echo esc_attr( $loop ); ?>"
							name="variable_global_unique_id[<?php echo esc_attr( $loop ); ?>]"
							value="<?php echo esc_attr( $variation->get_global_unique_id( 'edit' ) ); ?>">
					</div>
				</div>
			</div>

			<div class="row">
				<!-- Regular price -->
				<div class="col-md-6">
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
				<div class="col-md-6">
					<div class="storesuite-form-group">
						<div class="row">
							<div class="col-md-8">
								<label for="variable_sale_price_<?php echo esc_attr( $loop ); ?>">
									<?php /* translators: %s: currency symbol */ printf( esc_html__( 'Sale price (%s)', 'storesuite' ), esc_html( get_woocommerce_currency_symbol() ) ); ?>
								</label>
							</div>
							<div class="col-md-4 text-right">
								<a href="#" class="storesuite-variation-sale-schedule"><?php esc_html_e( 'Schedule', 'storesuite' ); ?></a>
								<a href="#" class="storesuite-variation-cancel-schedule"><?php esc_html_e( 'Cancel', 'storesuite' ); ?></a>
							</div>
						</div>
						<input type="text" class="storesuite-form-control wc_input_price"
							id="variable_sale_price_<?php echo esc_attr( $loop ); ?>"
							name="variable_sale_price[<?php echo esc_attr( $loop ); ?>]"
							value="<?php echo esc_attr( wc_format_localized_price( $variation->get_sale_price() ) ); ?>">
					</div>
				</div>
			</div>

			<?php
			$variation_sale_from = $variation->get_date_on_sale_from( 'edit' ) ? wp_date( 'Y-m-d', $variation->get_date_on_sale_from( 'edit' )->getTimestamp() ) : '';
			$variation_sale_to   = $variation->get_date_on_sale_to( 'edit' ) ? wp_date( 'Y-m-d', $variation->get_date_on_sale_to( 'edit' )->getTimestamp() ) : '';
			?>
			<div class="row storesuite-variation-sale-dates">
				<div class="col-md-6">
					<div class="storesuite-form-group">
						<label for="variable_sale_price_dates_from_<?php echo esc_attr( $loop ); ?>"><?php esc_html_e( 'Sale start date', 'storesuite' ); ?></label>
						<input type="text" class="storesuite-form-control"
							id="variable_sale_price_dates_from_<?php echo esc_attr( $loop ); ?>"
							name="variable_sale_price_dates_from[<?php echo esc_attr( $loop ); ?>]"
							value="<?php echo esc_attr( $variation_sale_from ); ?>"
							placeholder="<?php echo esc_attr( _x( 'From&hellip; YYYY-MM-DD', 'placeholder', 'storesuite' ) ); ?>">
					</div>
				</div>
				<div class="col-md-6">
					<div class="storesuite-form-group">
						<label for="variable_sale_price_dates_to_<?php echo esc_attr( $loop ); ?>"><?php esc_html_e( 'Sale end date', 'storesuite' ); ?></label>
						<input type="text" class="storesuite-form-control"
							id="variable_sale_price_dates_to_<?php echo esc_attr( $loop ); ?>"
							name="variable_sale_price_dates_to[<?php echo esc_attr( $loop ); ?>]"
							value="<?php echo esc_attr( $variation_sale_to ); ?>"
							placeholder="<?php echo esc_attr( _x( 'To&hellip; YYYY-MM-DD', 'placeholder', 'storesuite' ) ); ?>">
					</div>
				</div>
			</div>

			<div class="row">
				<?php if ( $cogs_is_enabled ) : ?>
					<!-- Cost of goods -->
					<div class="col-md">
						<div class="storesuite-form-group">
							<label for="variable_cogs_value_<?php echo esc_attr( $loop ); ?>">
								<?php /* translators: %s: currency symbol */ printf( esc_html__( 'Cost (%s)', 'storesuite' ), esc_html( get_woocommerce_currency_symbol() ) ); ?>
							</label>
							<input type="text" class="storesuite-form-control wc_input_price"
								id="variable_cogs_value_<?php echo esc_attr( $loop ); ?>"
								name="variable_cogs_value[<?php echo esc_attr( $loop ); ?>]"
								value="<?php echo esc_attr( wc_format_localized_price( $variation->get_cogs_value() ) ); ?>"
								placeholder="<?php esc_attr_e( '0 (default)', 'storesuite' ); ?>">
							<small class="storesuite-form-text">
								<?php
								printf(
									/* translators: %1$s: opening link tag, %2$s: closing link tag */
									esc_html__( 'You can specify a %1$sdefault value%2$s for all variations.', 'storesuite' ),
									'<a href="#_cogs_value" class="storesuite-cogs-default-link">',
									'</a>'
								);
								?>
							</small>
						</div>
					</div>
				<?php endif; ?>
				<!-- Stock status -->
				<div class="col-md">
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
				<div class="col-md show_if_variation_manage_stock" <?php echo $variation->get_manage_stock() ? '' : 'style="display:none;"'; ?>>
					<div class="storesuite-form-group">
						<label for="variable_stock_qty_<?php echo esc_attr( $loop ); ?>"><?php esc_html_e( 'Stock qty', 'storesuite' ); ?></label>
						<input type="number" class="storesuite-form-control" step="any"
							id="variable_stock_qty_<?php echo esc_attr( $loop ); ?>"
							name="variable_stock_qty[<?php echo esc_attr( $loop ); ?>]"
							value="<?php echo esc_attr( $variation->get_stock_quantity() ); ?>">
					</div>
				</div>
			</div>

			<div class="row show_if_variation_manage_stock" <?php echo $variation->get_manage_stock() ? '' : 'style="display:none;"'; ?>>
				<!-- Allow backorders -->
				<div class="col-md-6">
					<div class="storesuite-form-group">
						<label for="variable_backorders_<?php echo esc_attr( $loop ); ?>"><?php esc_html_e( 'Allow backorders?', 'storesuite' ); ?></label>
						<select class="storesuite-form-control"
							id="variable_backorders_<?php echo esc_attr( $loop ); ?>"
							name="variable_backorders[<?php echo esc_attr( $loop ); ?>]">
							<option value="no" <?php selected( $variation->get_backorders(), 'no' ); ?>><?php esc_html_e( 'Do not allow', 'storesuite' ); ?></option>
							<option value="notify" <?php selected( $variation->get_backorders(), 'notify' ); ?>><?php esc_html_e( 'Allow but notify customer', 'storesuite' ); ?></option>
							<option value="yes" <?php selected( $variation->get_backorders(), 'yes' ); ?>><?php esc_html_e( 'Allow', 'storesuite' ); ?></option>
						</select>
					</div>
				</div>
				<!-- Low stock threshold -->
				<div class="col-md-6">
					<div class="storesuite-form-group">
						<label for="variable_low_stock_amount_<?php echo esc_attr( $loop ); ?>"><?php esc_html_e( 'Low stock threshold', 'storesuite' ); ?></label>
						<input type="number" class="storesuite-form-control" step="any" min="0"
							id="variable_low_stock_amount_<?php echo esc_attr( $loop ); ?>"
							name="variable_low_stock_amount[<?php echo esc_attr( $loop ); ?>]"
							value="<?php echo esc_attr( $variation->get_low_stock_amount( 'edit' ) ); ?>"
							placeholder="<?php /* translators: %d: store-wide low stock amount */ printf( esc_attr__( 'Store-wide threshold (%d)', 'storesuite' ), esc_attr( get_option( 'woocommerce_notify_low_stock_amount', 2 ) ) ); ?>">
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
