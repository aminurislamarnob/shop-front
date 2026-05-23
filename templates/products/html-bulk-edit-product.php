<?php
/**
 * Bulk edit: WooCommerce product fields (right column) for the StoreSuite modal.
 *
 * Derived from WooCommerce `includes/admin/views/html-bulk-edit-product.php`;
 * vendored here so the dashboard does not depend on WooCommerce admin file paths.
 *
 * @package StoreSuite
 */

use Automattic\WooCommerce\Internal\CostOfGoodsSold\CostOfGoodsSoldController;
use Automattic\WooCommerce\Enums\CatalogVisibility;
use Automattic\WooCommerce\Enums\ProductTaxStatus;
use Automattic\WooCommerce\Utilities\I18nUtil;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>

<div id="woocommerce-fields-bulk" class="storesuite-product-bulk-wc-fields">

	<?php do_action( 'woocommerce_product_bulk_edit_start' ); ?>

	<div class="row">
		<div class="col-md-6">
			<div class="storesuite-form-group">
				<label for="storesuite-bulk-wc-change-regular-price" class="storesuite-form-label"><?php esc_html_e( 'Price', 'storesuite' ); ?></label>
				<select id="storesuite-bulk-wc-change-regular-price" class="storesuite-form-control change_regular_price change_to" name="change_regular_price">
					<?php
					$options = array(
						''  => __( '— No change —', 'storesuite' ),
						'1' => __( 'Change to:', 'storesuite' ),
						'2' => __( 'Increase existing price by (fixed amount or %):', 'storesuite' ),
						'3' => __( 'Decrease existing price by (fixed amount or %):', 'storesuite' ),
					);
					foreach ( $options as $key => $value ) {
						echo '<option value="' . esc_attr( $key ) . '">' . esc_html( $value ) . '</option>';
					}
					?>
				</select>
			</div>
		</div>
		<div class="col-md-6">
			<div class="storesuite-form-group">
				<label for="storesuite-bulk-wc-change-sale-price" class="storesuite-form-label"><?php esc_html_e( 'Sale', 'storesuite' ); ?></label>
				<select id="storesuite-bulk-wc-change-sale-price" class="storesuite-form-control change_sale_price change_to" name="change_sale_price">
					<?php
					$options = array(
						''  => __( '— No change —', 'storesuite' ),
						'1' => __( 'Change to:', 'storesuite' ),
						'2' => __( 'Increase existing sale price by (fixed amount or %):', 'storesuite' ),
						'3' => __( 'Decrease existing sale price by (fixed amount or %):', 'storesuite' ),
						'4' => __( 'Set to regular price decreased by (fixed amount or %):', 'storesuite' ),
					);
					foreach ( $options as $key => $value ) {
						echo '<option value="' . esc_attr( $key ) . '">' . esc_html( $value ) . '</option>';
					}
					?>
				</select>
			</div>
		</div>
	</div>

	<div class="row">
		<div class="col-md-6">
			<div class="storesuite-form-group">
				<label for="storesuite-bulk-wc-regular-price" class="storesuite-form-label"><?php esc_html_e( 'Price value', 'storesuite' ); ?></label>
				<?php /* translators: %s: WooCommerce currency symbol (e.g. $). */ ?>
				<input type="text" id="storesuite-bulk-wc-regular-price" name="_regular_price" class="storesuite-form-control text regular_price" placeholder="<?php echo esc_attr( sprintf( __( 'Enter price (%s)', 'storesuite' ), get_woocommerce_currency_symbol() ) ); ?>" value="" />
			</div>
		</div>
		<div class="col-md-6">
			<div class="storesuite-form-group">
				<label for="storesuite-bulk-wc-sale-price" class="storesuite-form-label"><?php esc_html_e( 'Sale price value', 'storesuite' ); ?></label>
				<?php /* translators: %s: WooCommerce currency symbol (e.g. $). */ ?>
				<input type="text" id="storesuite-bulk-wc-sale-price" name="_sale_price" class="storesuite-form-control text sale_price" placeholder="<?php echo esc_attr( sprintf( __( 'Enter sale price (%s)', 'storesuite' ), get_woocommerce_currency_symbol() ) ); ?>" value="" />
			</div>
		</div>
	</div>

	<?php if ( wc_get_container()->get( CostOfGoodsSoldController::class )->feature_is_enabled() ) : ?>
		<div class="row">
			<div class="col-md-6">
				<div class="storesuite-form-group">
					<label for="storesuite-bulk-wc-change-cogs" class="storesuite-form-label"><?php esc_html_e( 'Cost', 'storesuite' ); ?></label>
					<select id="storesuite-bulk-wc-change-cogs" class="storesuite-form-control change_cogs_value change_to" name="change_cogs_value">
						<?php
						$options = array(
							''  => __( '— No change —', 'storesuite' ),
							'1' => __( 'Change to:', 'storesuite' ),
						);
						foreach ( $options as $key => $value ) {
							echo '<option value="' . esc_attr( $key ) . '">' . esc_html( $value ) . '</option>';
						}
						?>
					</select>
				</div>
			</div>
			<div class="col-md-6">
				<div class="storesuite-form-group">
					<label for="storesuite-bulk-wc-cogs-value" class="storesuite-form-label"><?php esc_html_e( 'Cost value', 'storesuite' ); ?></label>
					<?php /* translators: %s = cost value (formatted as currency) */ ?>
					<input type="text" id="storesuite-bulk-wc-cogs-value" name="_cogs_value" class="storesuite-form-control text cogs_value" placeholder="<?php echo esc_attr( sprintf( __( 'Enter cost value (%s)', 'storesuite' ), get_woocommerce_currency_symbol() ) ); ?>" value="" />
				</div>
			</div>
		</div>
	<?php endif; ?>

	<?php if ( wc_tax_enabled() ) : ?>
		<div class="row">
			<div class="col-md-6">
				<div class="storesuite-form-group">
					<label for="storesuite-bulk-wc-tax-status" class="storesuite-form-label"><?php esc_html_e( 'Tax status', 'storesuite' ); ?></label>
					<select id="storesuite-bulk-wc-tax-status" class="storesuite-form-control tax_status" name="_tax_status">
						<?php
						$options = array(
							''                         => __( '— No change —', 'storesuite' ),
							ProductTaxStatus::TAXABLE  => __( 'Taxable', 'storesuite' ),
							ProductTaxStatus::SHIPPING => __( 'Shipping only', 'storesuite' ),
							ProductTaxStatus::NONE     => _x( 'None', 'Tax status', 'storesuite' ),
						);
						foreach ( $options as $key => $value ) {
							echo '<option value="' . esc_attr( $key ) . '">' . esc_html( $value ) . '</option>';
						}
						?>
					</select>
				</div>
			</div>
			<div class="col-md-6">
				<div class="storesuite-form-group">
					<label for="storesuite-bulk-wc-tax-class" class="storesuite-form-label"><?php esc_html_e( 'Tax class', 'storesuite' ); ?></label>
					<select id="storesuite-bulk-wc-tax-class" class="storesuite-form-control tax_class" name="_tax_class">
						<?php
						$options = array(
							''         => __( '— No change —', 'storesuite' ),
							'standard' => __( 'Standard', 'storesuite' ),
						);

						$tax_classes = WC_Tax::get_tax_classes();

						if ( ! empty( $tax_classes ) ) {
							foreach ( $tax_classes as $class ) {
								$options[ sanitize_title( $class ) ] = $class;
							}
						}

						foreach ( $options as $key => $value ) {
							echo '<option value="' . esc_attr( $key ) . '">' . esc_html( $value ) . '</option>';
						}
						?>
					</select>
				</div>
			</div>
		</div>
	<?php endif; ?>

	<?php if ( wc_product_weight_enabled() ) : ?>
		<div class="row">
			<div class="col-md-6">
				<div class="storesuite-form-group">
					<label for="storesuite-bulk-wc-change-weight" class="storesuite-form-label"><?php esc_html_e( 'Weight', 'storesuite' ); ?></label>
					<select id="storesuite-bulk-wc-change-weight" class="storesuite-form-control change_weight change_to" name="change_weight">
						<?php
						$options = array(
							''  => __( '— No change —', 'storesuite' ),
							'1' => __( 'Change to:', 'storesuite' ),
						);
						foreach ( $options as $key => $value ) {
							echo '<option value="' . esc_attr( $key ) . '">' . esc_html( $value ) . '</option>';
						}
						?>
					</select>
				</div>
			</div>
			<div class="col-md-6">
				<div class="storesuite-form-group">
					<label for="storesuite-bulk-wc-weight" class="storesuite-form-label"><?php esc_html_e( 'Weight value', 'storesuite' ); ?></label>
					<?php
					$placeholder = sprintf(
						/* translators: 1. Weight number; 2. Weight unit; E.g. 2 kg */
						__( '%1$s (%2$s)', 'storesuite' ),
						wc_format_localized_decimal( 0 ),
						I18nUtil::get_weight_unit_label( get_option( 'woocommerce_weight_unit', 'kg' ) )
					);
					?>
					<input type="text" id="storesuite-bulk-wc-weight" name="_weight" class="storesuite-form-control text weight" placeholder="<?php echo esc_attr( $placeholder ); ?>" value="" />
				</div>
			</div>
		</div>
	<?php endif; ?>

	<?php if ( wc_product_dimensions_enabled() ) : ?>
		<?php $dimension_unit_label = I18nUtil::get_dimensions_unit_label( get_option( 'woocommerce_dimension_unit', 'cm' ) ); ?>
		<div class="row">
			<div class="col-md-12">
				<div class="storesuite-form-group">
					<label for="storesuite-bulk-wc-change-dimensions" class="storesuite-form-label"><?php esc_html_e( 'Length / width / height', 'storesuite' ); ?></label>
					<select id="storesuite-bulk-wc-change-dimensions" class="storesuite-form-control change_dimensions change_to" name="change_dimensions">
						<?php
						$options = array(
							''  => __( '— No change —', 'storesuite' ),
							'1' => __( 'Change to:', 'storesuite' ),
						);
						foreach ( $options as $key => $value ) {
							echo '<option value="' . esc_attr( $key ) . '">' . esc_html( $value ) . '</option>';
						}
						?>
					</select>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-12">
				<div class="storesuite-form-group">
					<span class="storesuite-form-label"><?php esc_html_e( 'Dimension values', 'storesuite' ); ?></span>
					<div class="row storesuite-bulk-wc-dimensions-row">
						<div class="col-md-4">
							<?php /* translators: %s: Configured WooCommerce dimension unit (e.g. cm, in). */ ?>
							<input type="text" id="storesuite-bulk-wc-length" name="_length" class="storesuite-form-control text length" placeholder="<?php echo esc_attr( sprintf( __( 'Length (%s)', 'storesuite' ), $dimension_unit_label ) ); ?>" value="" />
						</div>
						<div class="col-md-4">
							<?php /* translators: %s: Configured WooCommerce dimension unit (e.g. cm, in). */ ?>
							<input type="text" id="storesuite-bulk-wc-width" name="_width" class="storesuite-form-control text width" placeholder="<?php echo esc_attr( sprintf( __( 'Width (%s)', 'storesuite' ), $dimension_unit_label ) ); ?>" value="" />
						</div>
						<div class="col-md-4">
							<?php /* translators: %s: Configured WooCommerce dimension unit (e.g. cm, in). */ ?>
							<input type="text" id="storesuite-bulk-wc-height" name="_height" class="storesuite-form-control text height" placeholder="<?php echo esc_attr( sprintf( __( 'Height (%s)', 'storesuite' ), $dimension_unit_label ) ); ?>" value="" />
						</div>
					</div>
				</div>
			</div>
		</div>
	<?php endif; ?>

	<div class="row">
		<div class="col-md-6">
			<div class="storesuite-form-group">
				<label for="storesuite-bulk-wc-shipping-class" class="storesuite-form-label"><?php esc_html_e( 'Shipping class', 'storesuite' ); ?></label>
				<select id="storesuite-bulk-wc-shipping-class" class="storesuite-form-control shipping_class" name="_shipping_class">
					<option value=""><?php esc_html_e( '— No change —', 'storesuite' ); ?></option>
					<option value="_no_shipping_class"><?php esc_html_e( 'No shipping class', 'storesuite' ); ?></option>
					<?php
					foreach ( $shipping_class as $key => $value ) {
						echo '<option value="' . esc_attr( $value->slug ) . '">' . esc_html( $value->name ) . '</option>';
					}
					?>
				</select>
			</div>
		</div>
		<div class="col-md-6">
			<div class="storesuite-form-group">
				<label for="storesuite-bulk-wc-visibility" class="storesuite-form-label"><?php esc_html_e( 'Visibility', 'storesuite' ); ?></label>
				<select id="storesuite-bulk-wc-visibility" class="storesuite-form-control visibility" name="_visibility">
					<?php
					$options = array(
						''                         => __( '— No change —', 'storesuite' ),
						CatalogVisibility::VISIBLE => __( 'Catalog & search', 'storesuite' ),
						CatalogVisibility::CATALOG => __( 'Catalog', 'storesuite' ),
						CatalogVisibility::SEARCH  => __( 'Search', 'storesuite' ),
						CatalogVisibility::HIDDEN  => __( 'Hidden', 'storesuite' ),
					);
					foreach ( $options as $key => $value ) {
						echo '<option value="' . esc_attr( $key ) . '">' . esc_html( $value ) . '</option>';
					}
					?>
				</select>
			</div>
		</div>
	</div>

	<div class="row">
		<div class="col-md-6">
			<div class="storesuite-form-group">
				<label for="storesuite-bulk-wc-featured" class="storesuite-form-label"><?php esc_html_e( 'Featured', 'storesuite' ); ?></label>
				<select id="storesuite-bulk-wc-featured" class="storesuite-form-control featured" name="_featured">
					<?php
					$options = array(
						''    => __( '— No change —', 'storesuite' ),
						'yes' => __( 'Yes', 'storesuite' ),
						'no'  => __( 'No', 'storesuite' ),
					);
					foreach ( $options as $key => $value ) {
						echo '<option value="' . esc_attr( $key ) . '">' . esc_html( $value ) . '</option>';
					}
					?>
				</select>
			</div>
		</div>
		<div class="col-md-6">
			<div class="storesuite-form-group">
				<label for="storesuite-bulk-wc-stock-status" class="storesuite-form-label"><?php esc_html_e( 'In stock?', 'storesuite' ); ?></label>
				<select id="storesuite-bulk-wc-stock-status" class="storesuite-form-control stock_status" name="_stock_status">
					<option value=""><?php echo esc_html__( '— No Change —', 'storesuite' ); ?></option>
					<?php foreach ( wc_get_product_stock_status_options() as $key => $value ) : ?>
						<option value="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $value ); ?></option>
					<?php endforeach; ?>
				</select>
			</div>
		</div>
	</div>

	<?php if ( 'yes' === get_option( 'woocommerce_manage_stock' ) ) : ?>

		<div class="row">
			<div class="col-md-6">
				<div class="storesuite-form-group">
					<label for="storesuite-bulk-wc-manage-stock" class="storesuite-form-label"><?php esc_html_e( 'Manage stock?', 'storesuite' ); ?></label>
					<select id="storesuite-bulk-wc-manage-stock" class="storesuite-form-control manage_stock" name="_manage_stock">
						<?php
						$options = array(
							''    => __( '— No change —', 'storesuite' ),
							'yes' => __( 'Yes', 'storesuite' ),
							'no'  => __( 'No', 'storesuite' ),
						);
						foreach ( $options as $key => $value ) {
							echo '<option value="' . esc_attr( $key ) . '">' . esc_html( $value ) . '</option>';
						}
						?>
					</select>
				</div>
			</div>
			<div class="col-md-6">
				<div class="storesuite-form-group">
					<label for="storesuite-bulk-wc-change-stock" class="storesuite-form-label"><?php esc_html_e( 'Stock qty', 'storesuite' ); ?></label>
					<select id="storesuite-bulk-wc-change-stock" class="storesuite-form-control change_stock change_to" name="change_stock">
						<?php
						$options = array(
							''  => __( '— No change —', 'storesuite' ),
							'1' => __( 'Change to:', 'storesuite' ),
							'2' => __( 'Increase existing stock by:', 'storesuite' ),
							'3' => __( 'Decrease existing stock by:', 'storesuite' ),
						);
						foreach ( $options as $key => $value ) {
							echo '<option value="' . esc_attr( $key ) . '">' . esc_html( $value ) . '</option>';
						}
						?>
					</select>
				</div>
			</div>
		</div>

		<div class="row">
			<div class="col-md-6">
				<div class="storesuite-form-group">
					<label for="storesuite-bulk-wc-stock" class="storesuite-form-label"><?php esc_html_e( 'Stock quantity', 'storesuite' ); ?></label>
					<input type="number" id="storesuite-bulk-wc-stock" name="_stock" class="storesuite-form-control text stock" step="any" placeholder="<?php echo esc_attr__( 'Stock qty', 'storesuite' ); ?>" value="" />
				</div>
			</div>
			<div class="col-md-6">
				<div class="storesuite-form-group">
					<label for="storesuite-bulk-wc-backorders" class="storesuite-form-label"><?php esc_html_e( 'Backorders?', 'storesuite' ); ?></label>
					<select id="storesuite-bulk-wc-backorders" class="storesuite-form-control backorders" name="_backorders">
						<option value=""><?php echo esc_html__( '— No Change —', 'storesuite' ); ?></option>
						<?php foreach ( wc_get_product_backorder_options() as $key => $value ) : ?>
							<option value="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $value ); ?></option>
						<?php endforeach; ?>
					</select>
				</div>
			</div>
		</div>

	<?php endif; ?>

	<div class="row">
		<div class="col-md-6">
			<div class="storesuite-form-group">
				<label for="storesuite-bulk-wc-sold-individually" class="storesuite-form-label"><?php esc_html_e( 'Sold individually?', 'storesuite' ); ?></label>
				<select id="storesuite-bulk-wc-sold-individually" class="storesuite-form-control sold_individually" name="_sold_individually">
					<?php
					$options = array(
						''    => __( '— No change —', 'storesuite' ),
						'yes' => __( 'Yes', 'storesuite' ),
						'no'  => __( 'No', 'storesuite' ),
					);
					foreach ( $options as $key => $value ) {
						echo '<option value="' . esc_attr( $key ) . '">' . esc_html( $value ) . '</option>';
					}
					?>
				</select>
			</div>
		</div>
	</div>

	<?php do_action( 'woocommerce_product_bulk_edit_end' ); ?>

	<input type="hidden" name="woocommerce_bulk_edit" value="1" />
	<input type="hidden" name="woocommerce_quick_edit_nonce" value="<?php echo esc_attr( wp_create_nonce( 'woocommerce_quick_edit_nonce' ) ); ?>" />
</div>
