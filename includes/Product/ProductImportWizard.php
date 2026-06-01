<?php

namespace PluginizeLab\StoreSuite\Product;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Include WooCommerce's product CSV importer wizard controller.
 *
 * WooCommerce only loads this class in wp-admin (via WC_Admin_Importers). StoreSuite's dashboard is a
 * frontend page, so we pull it in directly through the always-defined WC_ABSPATH constant before
 * declaring our subclass.
 */
if ( ! class_exists( '\WC_Product_CSV_Importer_Controller' ) && defined( 'WC_ABSPATH' ) ) {
	include_once WC_ABSPATH . 'includes/admin/importers/class-wc-product-csv-importer-controller.php';
}

// The controller's get_importer() instantiates WC_Product_CSV_Importer without including it (WC only
// loads it in wp-admin). Pull it in so the mapping/import steps work on the frontend.
if ( ! class_exists( '\WC_Product_CSV_Importer' ) && defined( 'WC_ABSPATH' ) ) {
	include_once WC_ABSPATH . 'includes/import/class-wc-product-csv-importer.php';
}

/**
 * Product CSV import wizard for the StoreSuite frontend dashboard.
 *
 * Reuses WooCommerce's import wizard (upload → mapping → import → done) and only overrides the chrome:
 * the StoreSuite dashboard shell is rendered by templates/products/import-products.php around dispatch(),
 * so the WC admin page wrapper is replaced with a light container, and the done screen links back to the
 * StoreSuite products page. All mapping/auto-map/batch logic is inherited.
 */
class ProductImportWizard extends \WC_Product_CSV_Importer_Controller {

	/**
	 * Output the wizard header.
	 *
	 * Replaces WooCommerce's admin `.wrap` chrome with a light container (the StoreSuite shell wraps this).
	 *
	 * @return void
	 */
	protected function output_header() {
		// `woocommerce-progress-form-wrapper` is the scope WooCommerce's admin.css uses for the wizard
		// steps bar, form table and progress bar; without it the wizard renders unstyled.
		echo '<div class="storesuite-import-wizard woocommerce woocommerce-progress-form-wrapper">';
	}

	/**
	 * Output the wizard footer.
	 *
	 * @return void
	 */
	protected function output_footer() {
		echo '</div>';
	}

	/**
	 * Done step.
	 *
	 * Renders a StoreSuite-themed summary whose "View products" button points to the dashboard products
	 * page instead of wp-admin.
	 *
	 * @return void
	 */
	protected function done() {
		if ( ! isset( $_REQUEST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_REQUEST['_wpnonce'] ) ), 'woocommerce-csv-importer' ) ) {
			return;
		}

		$imported = isset( $_REQUEST['products-imported'] ) ? absint( wp_unslash( $_REQUEST['products-imported'] ) ) : 0;
		$updated  = isset( $_REQUEST['products-updated'] ) ? absint( wp_unslash( $_REQUEST['products-updated'] ) ) : 0;
		$failed   = isset( $_REQUEST['products-failed'] ) ? absint( wp_unslash( $_REQUEST['products-failed'] ) ) : 0;
		$skipped  = isset( $_REQUEST['products-skipped'] ) ? absint( wp_unslash( $_REQUEST['products-skipped'] ) ) : 0;
		$errors   = array_filter( (array) get_user_option( 'product_import_error_log' ) );

		storesuite_get_template_part(
			'products/import-done',
			'',
			array(
				'imported' => $imported,
				'updated'  => $updated,
				'failed'   => $failed,
				'skipped'  => $skipped,
				'errors'   => $errors,
			)
		);
	}

	/**
	 * Validate that an uploaded import file path is safe and a CSV.
	 *
	 * Public wrapper around WooCommerce's protected validate_file_path() so the AJAX handler can reuse it.
	 *
	 * @param string $path Absolute file path.
	 *
	 * @throws \Exception When the path is invalid or not a CSV/TXT file.
	 *
	 * @return void
	 */
	public static function validate_import_file( $path ) {
		self::validate_file_path( $path );
	}
}
