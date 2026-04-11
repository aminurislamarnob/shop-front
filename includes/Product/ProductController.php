<?php

namespace PluginizeLab\StoreSuite\Product;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Plugin product controller class
 */
class ProductController {
	/**
	 * The constructor.
	 */
	public function __construct() {
		add_action( 'storesuite_load_new_product_template', array( $this, 'load_new_product_template' ) );
		add_action( 'storesuite_load_edit_product_template', array( $this, 'load_edit_product_template' ) );
		add_action( 'storesuite_dashboard_product_add_form', array( $this, 'load_product_form' ) );
		add_action( 'storesuite_dashboard_product_edit_form', array( $this, 'load_product_edit_form' ) );
		add_action( 'wp_ajax_storesuite_add_product_action', array( $this, 'handle_add_product' ) );
		add_action( 'wp_ajax_storesuite_edit_product_action', array( $this, 'handle_edit_product' ) );
		add_action( 'wp_ajax_storesuite_delete_product', array( $this, 'handle_delete_product' ) );
		add_action( 'wp_ajax_storesuite_bulk_edit_products', array( $this, 'handle_bulk_edit_products_ajax' ) );
		add_action( 'template_redirect', array( $this, 'handle_product_bulk_actions' ) );
	}

	/**
	 * Handle POST bulk actions on the StoreSuite products list (e.g. Move to Trash).
	 *
	 * @return void
	 */
	public function handle_product_bulk_actions() {
		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		if ( ! isset( $_POST['storesuite_product_bulk_nonce'] ) ) {
			return;
		}

		if ( ! storesuite_is_page( 'products' ) ) {
			return;
		}

		if ( ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['storesuite_product_bulk_nonce'] ) ), 'storesuite_product_bulk' ) ) {
			wp_safe_redirect( $this->get_products_bulk_redirect_url() );
			exit;
		}

		$action = isset( $_POST['action'] ) ? sanitize_text_field( wp_unslash( $_POST['action'] ) ) : '';

		if ( 'trash' !== $action ) {
			return;
		}

		if ( empty( $_POST['bulk_product_ids'] ) || ! is_array( $_POST['bulk_product_ids'] ) ) {
			wp_safe_redirect( $this->get_products_bulk_redirect_url() );
			exit;
		}

		$product_ids = array_map( 'absint', wp_unslash( $_POST['bulk_product_ids'] ) );
		$product_ids = array_values( array_unique( array_filter( $product_ids ) ) );

		if ( empty( $product_ids ) ) {
			wp_safe_redirect( $this->get_products_bulk_redirect_url() );
			exit;
		}

		if ( ! function_exists( 'wp_check_post_lock' ) ) {
			require_once ABSPATH . 'wp-admin/includes/post.php';
		}

		$trashed = 0;
		$locked  = 0;

		foreach ( $product_ids as $post_id ) {
			if ( 'product' !== get_post_type( $post_id ) ) {
				continue;
			}

			if ( ! current_user_can( 'delete_post', $post_id ) ) {
				continue;
			}

			if ( wp_check_post_lock( $post_id ) ) {
				++$locked;
				continue;
			}

			$result = wp_trash_post( $post_id );
			if ( $result ) {
				++$trashed;
			}
		}

		$query_args = array();
		if ( $trashed > 0 ) {
			$query_args['trashed'] = $trashed;
		}
		if ( $locked > 0 ) {
			$query_args['trash_locked'] = $locked;
		}

		wp_safe_redirect( $this->get_products_bulk_redirect_url( $query_args ) );
		exit;
	}

	/**
	 * AJAX: bulk edit products from the dashboard modal (core bulk_edit_posts + WooCommerce bulk meta).
	 *
	 * @return void
	 */
	public function handle_bulk_edit_products_ajax() {
		check_ajax_referer( 'storesuite_bulk_edit_products', 'security' );

		$post_type_object = get_post_type_object( 'product' );
		if ( ! $post_type_object || ! current_user_can( $post_type_object->cap->edit_posts ) ) {
			wp_send_json_error(
				array(
					'message' => __( 'You are not allowed to bulk edit products.', 'storesuite' ),
				),
				403
			);
		}

		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- bulk_edit_posts / WC sanitize internally.
		$post_data = wp_unslash( $_POST );

		$result = $this->run_product_bulk_edit( $post_data );

		if ( is_wp_error( $result ) ) {
			wp_send_json_error(
				array(
					'message' => $result->get_error_message(),
				)
			);
		}

		wp_send_json_success(
			array(
				'message' => $this->format_bulk_edit_result_message( $result ),
				'updated' => count( $result['updated'] ),
				'skipped' => count( $result['skipped'] ),
				'locked'  => count( $result['locked'] ),
			)
		);
	}

	/**
	 * Run bulk_edit_posts() for product post data.
	 *
	 * @param array<string, mixed> $post_data Unslashed POST-shaped array (must include post[], post_type, etc.).
	 * @return array<string, int[]>|\WP_Error Result array from bulk_edit_posts or error.
	 */
	private function run_product_bulk_edit( array $post_data ) {
		if ( empty( $post_data['post'] ) || ! is_array( $post_data['post'] ) ) {
			return new \WP_Error(
				'storesuite_bulk_no_posts',
				__( 'No products selected.', 'storesuite' )
			);
		}

		require_once ABSPATH . 'wp-admin/includes/post.php';

		if ( ! class_exists( 'WC_Admin_Post_Types', false ) ) {
			require_once WC()->plugin_path() . '/includes/admin/class-wc-admin-post-types.php';
		}

		$done = bulk_edit_posts( $post_data );

		if ( ! is_array( $done ) ) {
			return new \WP_Error(
				'storesuite_bulk_failed',
				__( 'Bulk update could not be completed.', 'storesuite' )
			);
		}

		return $done;
	}

	/**
	 * Human-readable summary for bulk edit counts (matches products list notice copy).
	 *
	 * @param array<string, int[]> $done Return value from bulk_edit_posts().
	 * @return string
	 */
	private function format_bulk_edit_result_message( array $done ) {
		$updated = isset( $done['updated'] ) ? count( $done['updated'] ) : 0;
		$skipped = isset( $done['skipped'] ) ? count( $done['skipped'] ) : 0;
		$locked  = isset( $done['locked'] ) ? count( $done['locked'] ) : 0;

		$parts = array();

		if ( $updated > 0 ) {
			$parts[] = sprintf(
				/* translators: %d: number of products updated */
				_n( '%d product updated.', '%d products updated.', $updated, 'storesuite' ),
				$updated
			);
		}
		if ( $skipped > 0 ) {
			$parts[] = sprintf(
				/* translators: %d: number of products skipped */
				_n(
					'%d product was not updated (permission or invalid data).',
					'%d products were not updated (permission or invalid data).',
					$skipped,
					'storesuite'
				),
				$skipped
			);
		}
		if ( $locked > 0 ) {
			$parts[] = sprintf(
				/* translators: %d: number of products locked by another user */
				_n(
					'%d product not updated, currently being edited by another user.',
					'%d products not updated, currently being edited by another user.',
					$locked,
					'storesuite'
				),
				$locked
			);
		}

		if ( empty( $parts ) ) {
			return __( 'No changes were applied.', 'storesuite' );
		}

		return implode( "\n", $parts );
	}

	/**
	 * Build the products list URL for redirects, preserving list context and optional notices.
	 *
	 * @param array<string, int|string> $extra_query_args Query args to append (e.g. trashed, locked).
	 * @return string
	 */
	private function get_products_bulk_redirect_url( array $extra_query_args = array() ) {
		$base = untrailingslashit( storesuite_get_navigation_url( 'products' ) );

		$paged = max( 1, absint( get_query_var( 'paged' ) ) );
		if ( $paged > 1 ) {
			$base .= '/page/' . $paged;
		}

		$base = trailingslashit( $base );

		$preserve = array();

		// phpcs:disable WordPress.Security.NonceVerification.Recommended -- Read-only query args for redirect.
		$filter_keys = array(
			'search_by'    => 'text',
			'product_cat'  => 'int',
			'product_type' => 'text',
			'stock_status' => 'text',
			'product_brand' => 'int',
		);

		foreach ( $filter_keys as $key => $type ) {
			if ( ! isset( $_GET[ $key ] ) ) {
				continue;
			}

			$raw = wp_unslash( $_GET[ $key ] );
			if ( $raw === '' || $raw === null ) {
				continue;
			}

			if ( 'int' === $type ) {
				$preserve[ $key ] = absint( $raw );
			} else {
				$preserve[ $key ] = sanitize_text_field( $raw );
			}
		}
		// phpcs:enable WordPress.Security.NonceVerification.Recommended

		$url = add_query_arg( array_merge( $preserve, $extra_query_args ), $base );

		return esc_url_raw( $url );
	}

	/**
	 * Load the new product template.
	 *
	 * @param array $query_vars The query variables.
	 *
	 * @return void
	 */
	public function load_new_product_template( $query_vars ) {
		$template_args = array(
			'query_vars' => $query_vars,
		);
		storesuite_get_template_part( 'products/add-new-product', '', $template_args );
	}

	/**
	 * Load the edit product template.
	 *
	 * @param array $query_vars The query variables.
	 *
	 * @return void
	 */
	public function load_edit_product_template( $query_vars ) {
		$template_args = array(
			'query_vars' => $query_vars,
		);
		storesuite_get_template_part( 'products/edit-product', '', $template_args );
	}

	/**
	 * Load the product add form template.
	 *
	 * @param array $query_vars The query variables.
	 *
	 * @return void
	 */
	public function load_product_form( $query_vars ) {
		$template_args = array(
			'query_vars'    => $query_vars,
			'template_type' => 'add-new-product',
		);
		storesuite_get_template_part( 'products/product-form', '', $template_args );
	}

	/**
	 * Load the product edit form template.
	 *
	 * @param array $query_vars The query variables.
	 *
	 * @return void
	 */
	public function load_product_edit_form( $query_vars ) {
		$template_args = array(
			'query_vars'    => $query_vars,
			'template_type' => 'edit-product',
		);
		storesuite_get_template_part( 'products/product-form', '', $template_args );
	}

	/**
	 * Handle the AJAX request for adding a new product.
	 */
	public function handle_add_product() {

		// Verify the nonce.
		if ( ! isset( $_POST['storesuite_add_product_nonce'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['storesuite_add_product_nonce'] ) ), '_storesuite_add_product_' ) ) {
			wp_send_json_error( array( 'error' => __( 'Nonce verification failed', 'storesuite' ) ) );
		}

		// Check user permissions.
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_send_json_error( array( 'error' => __( 'You do not have permission to perform this action.', 'storesuite' ) ) );
		}

		// Build sanitized data array.
		$data = $this->sanitize_product_data( $_POST );

		$response = ( new ProductManager() )->storesuite_save_product( $data );

		if ( is_wp_error( $response ) ) {
			wp_send_json_error( $response->get_error_message() );
		}

		if ( is_int( $response ) ) {
			wp_send_json_success(
				array(
					'message' => __( 'Product successfully created', 'storesuite' ),
					'context' => 'add',
				)
			);
		} else {
			wp_send_json_error(
				array(
					'error'   => __( 'Something wrong, please try again later', 'storesuite' ),
					'context' => 'add',
				)
			);
		}
	}

	/**
	 * Handle the AJAX request for update a product.
	 */
	public function handle_edit_product() {
		// Verify the nonce.
		if ( ! isset( $_POST['storesuite_edit_product_nonce'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['storesuite_edit_product_nonce'] ) ), '_storesuite_edit_product_' ) ) {
			wp_send_json_error( array( 'error' => __( 'Nonce verification failed', 'storesuite' ) ) );
		}

		// Check user permissions.
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_send_json_error( array( 'error' => __( 'You do not have permission to perform this action.', 'storesuite' ) ) );
		}

		// Build sanitized data array.
		$data = $this->sanitize_product_data( $_POST );

		$response = ( new ProductManager() )->storesuite_save_product( $data );

		if ( is_wp_error( $response ) ) {
			wp_send_json_error( $response->get_error_message() );
		}

		if ( is_int( $response ) ) {
			$product = wc_get_product( $response );
			wp_send_json_success(
				array(
					'message'   => __( 'Product successfully updated', 'storesuite' ),
					'context'   => 'edit',
					'permalink' => get_permalink( $response ),
					'slug'      => $product ? $product->get_slug() : '',
				)
			);
		} else {
			wp_send_json_error(
				array(
					'error'   => __( 'Something wrong, please try again later', 'storesuite' ),
					'context' => 'edit',
				)
			);
		}
	}

	/**
	 * Handle the AJAX request for deleting a product (move to trash).
	 */
	public function handle_delete_product() {
		if ( ! isset( $_POST['storesuite_delete_product_nonce'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['storesuite_delete_product_nonce'] ) ), '_storesuite_delete_nonce_' ) ) {
			wp_send_json_error( array( 'error' => __( 'Nonce verification failed', 'storesuite' ) ) );
		}

		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_send_json_error( array( 'error' => __( 'You do not have permission to perform this action.', 'storesuite' ) ) );
		}

		$product_id = isset( $_POST['id'] ) ? absint( $_POST['id'] ) : 0;
		if ( ! $product_id ) {
			wp_send_json_error( array( 'error' => __( 'Invalid product ID', 'storesuite' ) ) );
		}

		$product = wc_get_product( $product_id );
		if ( ! $product ) {
			wp_send_json_error( array( 'error' => __( 'Product not found', 'storesuite' ) ) );
		}

		$result = wp_trash_post( $product_id );
		if ( ! $result ) {
			wp_send_json_error( array( 'error' => __( 'Failed to delete product', 'storesuite' ) ) );
		}

		wp_send_json_success( array( 'message' => __( 'Product successfully deleted', 'storesuite' ) ) );
	}

	/**
	 * Sanitize product data from $_POST.
	 *
	 * @param array $post_data Raw POST data.
	 * @return array Sanitized product data.
	 */
	private function sanitize_product_data( $post_data ) {
		$data = array();

		// Product ID for updates.
		if ( isset( $post_data['product_id'] ) ) {
			$data['product_id'] = absint( $post_data['product_id'] );
		}

		// Text fields.
		if ( isset( $post_data['product_title'] ) ) {
			$data['product_title'] = sanitize_text_field( wp_unslash( $post_data['product_title'] ) );
		}
		if ( isset( $post_data['product_slug'] ) ) {
			$data['product_slug'] = sanitize_title( wp_unslash( $post_data['product_slug'] ) );
		}
		if ( isset( $post_data['post_type'] ) ) {
			$data['post_type'] = sanitize_key( wp_unslash( $post_data['post_type'] ) );
		}
		if ( isset( $post_data['post_status'] ) ) {
			$data['post_status'] = sanitize_key( wp_unslash( $post_data['post_status'] ) );
		}
		if ( isset( $post_data['_visibility'] ) ) {
			$data['_visibility'] = sanitize_key( wp_unslash( $post_data['_visibility'] ) );
		}
		if ( isset( $post_data['_sku'] ) ) {
			$data['_sku'] = sanitize_text_field( wp_unslash( $post_data['_sku'] ) );
		}
		if ( isset( $post_data['_global_unique_id'] ) ) {
			$data['_global_unique_id'] = sanitize_text_field( wp_unslash( $post_data['_global_unique_id'] ) );
		}
		if ( isset( $post_data['_stock_status'] ) ) {
			$data['_stock_status'] = sanitize_key( wp_unslash( $post_data['_stock_status'] ) );
		}
		if ( isset( $post_data['_backorders'] ) ) {
			$data['_backorders'] = sanitize_key( wp_unslash( $post_data['_backorders'] ) );
		}
		if ( isset( $post_data['comment_status'] ) ) {
			$data['comment_status'] = sanitize_key( wp_unslash( $post_data['comment_status'] ) );
		}

		// HTML content fields.
		if ( isset( $post_data['product_description'] ) ) {
			$data['product_description'] = wp_kses_post( wp_unslash( $post_data['product_description'] ) );
		}
		if ( isset( $post_data['product_short_description'] ) ) {
			$data['product_short_description'] = wp_kses_post( wp_unslash( $post_data['product_short_description'] ) );
		}
		if ( isset( $post_data['_purchase_note'] ) ) {
			$data['_purchase_note'] = wp_kses_post( wp_unslash( $post_data['_purchase_note'] ) );
		}

		// Price/decimal fields.
		if ( isset( $post_data['regular_price'] ) ) {
			$data['regular_price'] = $post_data['regular_price'] === '' ? '' : wc_format_decimal( wp_unslash( $post_data['regular_price'] ) );
		}
		if ( isset( $post_data['sale_price'] ) ) {
			$data['sale_price'] = $post_data['sale_price'] === '' ? '' : wc_format_decimal( wp_unslash( $post_data['sale_price'] ) );
		}
		if ( isset( $post_data['weight'] ) ) {
			$data['weight'] = wc_format_decimal( wp_unslash( $post_data['weight'] ) );
		}
		if ( isset( $post_data['length'] ) ) {
			$data['length'] = wc_format_decimal( wp_unslash( $post_data['length'] ) );
		}
		if ( isset( $post_data['width'] ) ) {
			$data['width'] = wc_format_decimal( wp_unslash( $post_data['width'] ) );
		}
		if ( isset( $post_data['height'] ) ) {
			$data['height'] = wc_format_decimal( wp_unslash( $post_data['height'] ) );
		}

		// Integer fields.
		if ( isset( $post_data['product_thumbnail_id'] ) ) {
			$data['product_thumbnail_id'] = absint( $post_data['product_thumbnail_id'] );
		}
		if ( isset( $post_data['menu_order'] ) ) {
			$data['menu_order'] = absint( $post_data['menu_order'] );
		}
		if ( isset( $post_data['_stock_quantity'] ) ) {
			$data['_stock_quantity'] = wc_stock_amount( wp_unslash( $post_data['_stock_quantity'] ) );
		}
		if ( isset( $post_data['_low_stock_amount'] ) ) {
			$data['_low_stock_amount'] = wc_stock_amount( wp_unslash( $post_data['_low_stock_amount'] ) );
		}
		if ( isset( $post_data['product_shipping_class'] ) ) {
			$data['product_shipping_class'] = absint( $post_data['product_shipping_class'] );
		}

		// Checkbox/boolean fields.
		if ( isset( $post_data['_manage_stock'] ) ) {
			$data['_manage_stock'] = sanitize_key( wp_unslash( $post_data['_manage_stock'] ) );
		}
		if ( isset( $post_data['_sold_individually'] ) ) {
			$data['_sold_individually'] = sanitize_key( wp_unslash( $post_data['_sold_individually'] ) );
		}
		if ( isset( $post_data['_featured'] ) ) {
			$data['_featured'] = sanitize_key( wp_unslash( $post_data['_featured'] ) );
		}

		// Date fields.
		if ( isset( $post_data['_sale_price_dates_from'] ) ) {
			$data['_sale_price_dates_from'] = sanitize_text_field( wp_unslash( $post_data['_sale_price_dates_from'] ) );
		}
		if ( isset( $post_data['_sale_price_dates_to'] ) ) {
			$data['_sale_price_dates_to'] = sanitize_text_field( wp_unslash( $post_data['_sale_price_dates_to'] ) );
		}

		// Gallery images - comma-separated IDs.
		if ( isset( $post_data['product_image_gallery'] ) ) {
			$data['product_image_gallery'] = sanitize_text_field( wp_unslash( $post_data['product_image_gallery'] ) );
		}

		// Array of IDs.
		if ( isset( $post_data['product_category'] ) ) {
			$data['product_category'] = array_map( 'absint', (array) $post_data['product_category'] );
		}
		if ( isset( $post_data['product_brand'] ) ) {
			$data['product_brand'] = array_map( 'absint', (array) $post_data['product_brand'] );
		}
		if ( isset( $post_data['product_tags'] ) ) {
			$data['product_tags'] = array_map( 'absint', (array) $post_data['product_tags'] );
		}
		if ( isset( $post_data['upsell_ids'] ) ) {
			$data['upsell_ids'] = array_map( 'absint', (array) $post_data['upsell_ids'] );
		}
		if ( isset( $post_data['crosssell_ids'] ) ) {
			$data['crosssell_ids'] = array_map( 'absint', (array) $post_data['crosssell_ids'] );
		}

		return $data;
	}
}
