<?php
/**
 * Product export: modal form.
 *
 * Drives WooCommerce's product CSV exporter (reused via PluginizeLab\StoreSuite\Product\ProductExporter)
 * through the StoreSuite dashboard. Field selectors are consumed by assets/frontend/product-export.js.
 *
 * @package StoreSuite
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! current_user_can( 'manage_woocommerce' ) ) {
	return;
}

$storesuite_exporter        = new \PluginizeLab\StoreSuite\Product\ProductExporter();
$storesuite_export_columns  = $storesuite_exporter->get_default_column_names();
$storesuite_product_types   = wc_get_product_types();
$storesuite_export_cats     = get_terms(
	array(
		'taxonomy'   => 'product_cat',
		'hide_empty' => false,
	)
);
if ( is_wp_error( $storesuite_export_cats ) ) {
	$storesuite_export_cats = array();
}
?>
<div id="storesuite-product-export-modal" class="storesuite-product-bulk-modal-overlay" hidden aria-hidden="true">
	<div
		class="storesuite-product-bulk-modal-dialog"
		role="dialog"
		aria-modal="true"
		aria-labelledby="storesuite-product-export-title"
		tabindex="-1"
	>
		<div class="storesuite-product-bulk-modal-header">
			<h2 id="storesuite-product-export-title" class="storesuite-product-bulk-modal-title">
				<?php esc_html_e( 'Export products to a CSV file', 'storesuite' ); ?>
			</h2>
			<button type="button" class="storesuite-product-bulk-modal-close my-storesuite-button storesuite-button-ghost" aria-label="<?php esc_attr_e( 'Close', 'storesuite' ); ?>">
				<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" aria-hidden="true" focusable="false">
					<path d="M18,6h0a1,1,0,0,0-1.414,0L12,10.586,7.414,6A1,1,0,0,0,6,6H6a1,1,0,0,0,0,1.414L10.586,12,6,16.586A1,1,0,0,0,6,18h0a1,1,0,0,0,1.414,0L12,13.414,16.586,18A1,1,0,0,0,18,18h0a1,1,0,0,0,0-1.414L13.414,12,18,7.414A1,1,0,0,0,18,6Z"/>
				</svg>
			</button>
		</div>
		<form id="storesuite-product-export-form" class="storesuite-product-bulk-edit-form">
			<input type="hidden" name="product_ids" id="storesuite-export-product-ids" value="" />

			<div class="storesuite-product-bulk-edit-scroll">
				<p class="storesuite-product-export-intro">
					<?php esc_html_e( 'Generate and download a CSV file containing your products. Leave the filters empty to export everything.', 'storesuite' ); ?>
				</p>

				<div class="storesuite-form-group">
					<label for="storesuite-export-columns" class="storesuite-form-label"><?php esc_html_e( 'Which columns should be exported?', 'storesuite' ); ?></label>
					<select name="export_columns" id="storesuite-export-columns" class="storesuite-form-control storesuite-select2 storesuite-export-columns" multiple data-placeholder="<?php esc_attr_e( 'Export all columns', 'storesuite' ); ?>">
						<?php foreach ( $storesuite_export_columns as $storesuite_column_id => $storesuite_column_name ) : ?>
							<option value="<?php echo esc_attr( $storesuite_column_id ); ?>"><?php echo esc_html( $storesuite_column_name ); ?></option>
						<?php endforeach; ?>
						<option value="downloads"><?php esc_html_e( 'Downloads', 'storesuite' ); ?></option>
						<option value="attributes"><?php esc_html_e( 'Attributes', 'storesuite' ); ?></option>
					</select>
				</div>

				<div class="storesuite-form-group">
					<label for="storesuite-export-types" class="storesuite-form-label"><?php esc_html_e( 'Which product types should be exported?', 'storesuite' ); ?></label>
					<select name="export_types" id="storesuite-export-types" class="storesuite-form-control storesuite-select2 storesuite-export-types" multiple data-placeholder="<?php esc_attr_e( 'Export all products', 'storesuite' ); ?>">
						<?php foreach ( $storesuite_product_types as $storesuite_type_value => $storesuite_type_label ) : ?>
							<option value="<?php echo esc_attr( $storesuite_type_value ); ?>"><?php echo esc_html( $storesuite_type_label ); ?></option>
						<?php endforeach; ?>
						<option value="variable-variation"><?php esc_html_e( 'Variable product with variations', 'storesuite' ); ?></option>
						<option value="variation"><?php esc_html_e( 'Product variations', 'storesuite' ); ?></option>
					</select>
				</div>

				<div class="storesuite-form-group storesuite-export-category-row">
					<label for="storesuite-export-category" class="storesuite-form-label"><?php esc_html_e( 'Which product category should be exported?', 'storesuite' ); ?></label>
					<select name="export_category" id="storesuite-export-category" class="storesuite-form-control storesuite-select2 storesuite-export-category" multiple data-placeholder="<?php esc_attr_e( 'Export all categories', 'storesuite' ); ?>">
						<?php foreach ( $storesuite_export_cats as $storesuite_cat ) : ?>
							<option value="<?php echo esc_attr( $storesuite_cat->slug ); ?>"><?php echo esc_html( $storesuite_cat->name ); ?></option>
						<?php endforeach; ?>
					</select>
				</div>

				<div class="storesuite-form-group">
					<label class="storesuite-form-label"><?php esc_html_e( 'Export custom meta?', 'storesuite' ); ?></label>
					<div class="storesuite-form-group storesuite-form-switch">
						<input type="checkbox" id="storesuite-export-meta" value="1" />
						<label for="storesuite-export-meta"><?php esc_html_e( 'Yes, export all custom meta', 'storesuite' ); ?></label>
					</div>
				</div>

				<progress class="storesuite-export-progress" max="100" value="0"></progress>
			</div>

			<div class="storesuite-product-bulk-modal-footer">
				<button type="button" class="my-storesuite-button storesuite-button-neutral-panel storesuite-product-bulk-modal-cancel">
					<?php esc_html_e( 'Cancel', 'storesuite' ); ?>
				</button>
				<input type="submit" class="my-storesuite-button storesuite-export-submit" value="<?php esc_attr_e( 'Generate CSV', 'storesuite' ); ?>" />
			</div>
		</form>
	</div>
</div>
