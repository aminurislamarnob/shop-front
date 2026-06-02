<?php

namespace PluginizeLab\StoreSuite\Product;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Drives WooCommerce's product CSV exporter from the StoreSuite frontend dashboard.
 *
 * Two AJAX endpoints are exposed (both reachable for logged-in dashboard users via admin-ajax):
 *  - `storesuite_product_export`        — runs one batch step, writing rows to a temp CSV.
 *  - `storesuite_download_product_csv`  — streams the finished temp CSV and deletes it.
 */
class ProductExportController {

	/**
	 * Nonce action for the batch export request.
	 */
	const EXPORT_NONCE = 'storesuite_product_export';

	/**
	 * Nonce action for the file download request.
	 */
	const DOWNLOAD_NONCE = 'storesuite-download-product-csv';

	/**
	 * The constructor.
	 */
	public function __construct() {
		add_action( 'wp_ajax_storesuite_product_export', array( $this, 'handle_product_export' ) );
		add_action( 'wp_ajax_storesuite_download_product_csv', array( $this, 'handle_download' ) );
	}

	/**
	 * AJAX: run a single export batch step.
	 *
	 * Mirrors WooCommerce's `WC_Admin_Exporters::do_ajax_product_export()` but scoped to the
	 * StoreSuite dashboard (own nonce + capability, no admin dependency).
	 *
	 * @return void
	 */
	public function handle_product_export() {
		check_ajax_referer( self::EXPORT_NONCE, 'security' );

		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_send_json_error( array( 'error' => __( 'You do not have permission to perform this action.', 'storesuite' ) ) );
		}

		$step     = isset( $_POST['step'] ) ? absint( wp_unslash( $_POST['step'] ) ) : 1;
		$exporter = new ProductExporter();

		if ( ! empty( $_POST['columns'] ) ) {
			$exporter->set_column_names( wc_clean( wp_unslash( $_POST['columns'] ) ) );
		}

		if ( ! empty( $_POST['selected_columns'] ) ) {
			$exporter->set_columns_to_export( wc_clean( wp_unslash( $_POST['selected_columns'] ) ) );
		}

		if ( ! empty( $_POST['export_meta'] ) ) {
			$exporter->enable_meta_export( true );
		}

		if ( ! empty( $_POST['export_types'] ) ) {
			$exporter->set_product_types_to_export( wc_clean( wp_unslash( $_POST['export_types'] ) ) );
		}

		if ( ! empty( $_POST['export_category'] ) ) {
			$exporter->set_product_category_to_export( wc_clean( wp_unslash( $_POST['export_category'] ) ) );
		}

		if ( ! empty( $_POST['export_product_ids'] ) ) {
			$product_ids = array_filter( array_map( 'absint', explode( ',', wc_clean( wp_unslash( $_POST['export_product_ids'] ) ) ) ) );
			$exporter->set_product_ids_to_export( $product_ids );
		}

		if ( ! empty( $_POST['filename'] ) ) {
			$exporter->set_filename( sanitize_file_name( wp_unslash( $_POST['filename'] ) ) );
		}

		$exporter->set_page( $step );
		$exporter->generate_file();

		if ( 100 === $exporter->get_percent_complete() ) {
			wp_send_json_success(
				array(
					'step'       => 'done',
					'percentage' => 100,
					'url'        => add_query_arg(
						array(
							'action'   => 'storesuite_download_product_csv',
							'nonce'    => wp_create_nonce( self::DOWNLOAD_NONCE ),
							'filename' => rawurlencode( $exporter->get_filename() ),
						),
						admin_url( 'admin-ajax.php' )
					),
				)
			);
		}

		wp_send_json_success(
			array(
				'step'       => ++$step,
				'percentage' => $exporter->get_percent_complete(),
				'columns'    => $exporter->get_column_names(),
			)
		);
	}

	/**
	 * AJAX: stream the generated CSV file to the browser.
	 *
	 * The batch exporter's `export()` reads the temp file from the uploads dir, sends the CSV
	 * download headers, echoes the content, unlinks the temp file and exits.
	 *
	 * @return void
	 */
	public function handle_download() {
		if ( ! isset( $_GET['nonce'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_GET['nonce'] ) ), self::DOWNLOAD_NONCE ) ) {
			wp_die( esc_html__( 'Security verification failed.', 'storesuite' ) );
		}

		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_die( esc_html__( 'You do not have permission to perform this action.', 'storesuite' ) );
		}

		$filename = isset( $_GET['filename'] ) ? sanitize_file_name( wp_unslash( $_GET['filename'] ) ) : '';
		if ( empty( $filename ) ) {
			wp_die( esc_html__( 'Invalid export file.', 'storesuite' ) );
		}

		$exporter = new ProductExporter();
		$exporter->set_filename( $filename );
		$exporter->export();
	}
}
