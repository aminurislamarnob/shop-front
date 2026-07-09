<?php

namespace PluginizeLab\StoreSuite\Product;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// WooCommerce loads its exporter only in wp-admin; pull it in for the frontend dashboard.
if ( ! class_exists( '\WC_Product_CSV_Exporter', false ) && defined( 'WC_ABSPATH' ) ) {
	include_once WC_ABSPATH . 'includes/export/class-wc-product-csv-exporter.php';
}

/**
 * Product CSV exporter for the StoreSuite frontend dashboard.
 *
 * Reuses WooCommerce's exporter engine, overriding only the admin-dependent constructor.
 */
class ProductExporter extends \WC_Product_CSV_Exporter {

	/**
	 * Constructor.
	 *
	 * Seeds product types from `wc_get_product_types()` instead of the admin-only
	 * `WC_Admin_Exporters::get_product_types()`.
	 */
	public function __construct() {
		\WC_CSV_Batch_Exporter::__construct();
		$this->set_product_types_to_export( array_merge( array_keys( wc_get_product_types() ), array( 'variation' ) ) );
	}

	/**
	 * Expand the "variable-variation" UI option into both `variable` and `variation`.
	 *
	 * @param array $product_types_to_export Product type slugs selected for export.
	 *
	 * @return void
	 */
	public function set_product_types_to_export( $product_types_to_export ) {
		$variations_with_variable_key = array_search( 'variable-variation', $product_types_to_export, true );

		if ( false !== $variations_with_variable_key ) {
			$product_types_to_export[ $variations_with_variable_key ] = 'variable';
		}

		if ( false !== $variations_with_variable_key && false === array_search( 'variation', $product_types_to_export, true ) ) {
			$product_types_to_export[] = 'variation';
		}

		$this->product_types_to_export = array_map( 'wc_clean', $product_types_to_export );
	}
}
