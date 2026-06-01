<?php

namespace PluginizeLab\StoreSuite\Product;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Include the WooCommerce product CSV exporter.
 *
 * WooCommerce only loads its exporter classes inside `WC_Admin_Exporters`, which runs in
 * wp-admin. StoreSuite's dashboard is a frontend page, so we pull the class in directly via
 * the always-defined `WC_ABSPATH` constant before declaring our subclass.
 */
if ( ! class_exists( '\WC_Product_CSV_Exporter', false ) && defined( 'WC_ABSPATH' ) ) {
	include_once WC_ABSPATH . 'includes/export/class-wc-product-csv-exporter.php';
}

/**
 * Product CSV exporter for the StoreSuite frontend dashboard.
 *
 * Reuses WooCommerce's exporter engine (columns, batching, attribute/meta/download handling and
 * CSV-injection escaping). Only the constructor is overridden so we don't depend on the admin-only
 * `WC_Admin_Exporters::get_product_types()` helper.
 */
class ProductExporter extends \WC_Product_CSV_Exporter {

	/**
	 * Constructor.
	 *
	 * Skips the parent `WC_Product_CSV_Exporter::__construct()` (which calls the admin-only
	 * `WC_Admin_Exporters::get_product_types()`) and seeds the product types from the core
	 * `wc_get_product_types()` helper instead.
	 */
	public function __construct() {
		\WC_CSV_Batch_Exporter::__construct();
		$this->set_product_types_to_export( array_merge( array_keys( wc_get_product_types() ), array( 'variation' ) ) );
	}

	/**
	 * Product types to export.
	 *
	 * Expands the "variable-variation" UI option into both `variable` (parent) and `variation`
	 * (children) so a variable product is exported together with all of its variations.
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
