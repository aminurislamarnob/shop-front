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
		add_action( 'wp_ajax_storesuite_add_variation', array( $this, 'ajax_add_variation' ) );
		add_action( 'wp_ajax_storesuite_link_all_variations', array( $this, 'ajax_link_all_variations' ) );
		add_action( 'wp_ajax_storesuite_remove_variation', array( $this, 'ajax_remove_variation' ) );
		add_action( 'wp_ajax_storesuite_get_predefined_attribute', array( $this, 'ajax_get_predefined_attribute' ) );
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

		$response = ( new ProductManager() )->storesuite_save_product( wp_unslash( $_POST ) );

		if ( is_wp_error( $response ) ) {
			wp_send_json_error( $response->get_error_message() );
		}

		if ( is_int( $response ) ) {
			$product = wc_get_product( $response );
			$payload = array(
				'message' => __( 'Product successfully created', 'storesuite' ),
				'context' => 'add',
			);
			if ( $product && $product->is_type( 'variable' ) ) {
				$payload['product_id']        = $response;
				$payload['redirect_edit_url'] = storesuite_get_navigation_url( 'edit-product' ) . $response;
			}
			wp_send_json_success( $payload );
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

		$response = ( new ProductManager() )->storesuite_save_product( wp_unslash( $_POST ) );

		if ( is_wp_error( $response ) ) {
			wp_send_json_error( $response->get_error_message() );
		}

		if ( is_int( $response ) ) {
			$product = wc_get_product( $response );
			if ( $product && $product->is_type( 'variable' ) ) {
				$handler = new ProductVariationHandler();
				$handler->save_product_attributes( $response, wp_unslash( $_POST ) );
				$handler->save_product_variations( $response );
			}
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
	 * AJAX: Add a single variation (empty) and return its row HTML.
	 */
	public function ajax_add_variation() {
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['nonce'] ) ), 'storesuite_variations' ) ) {
			wp_send_json_error( array( 'error' => __( 'Invalid nonce', 'storesuite' ) ) );
		}
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_send_json_error( array( 'error' => __( 'Permission denied', 'storesuite' ) ) );
		}
		$product_id = isset( $_POST['product_id'] ) ? absint( $_POST['product_id'] ) : 0;
		$loop       = isset( $_POST['loop'] ) ? absint( $_POST['loop'] ) : 0;
		if ( ! $product_id ) {
			wp_send_json_error( array( 'error' => __( 'Invalid product', 'storesuite' ) ) );
		}
		$product = wc_get_product( $product_id );
		if ( ! $product || ! $product->is_type( 'variable' ) ) {
			wp_send_json_error( array( 'error' => __( 'Not a variable product', 'storesuite' ) ) );
		}
		$variation_object = new \WC_Product_Variation();
		$variation_object->set_parent_id( $product_id );
		$variation_object->set_status( 'publish' );
		$variation_object->set_menu_order( -1 );
		$variation_object->set_attributes(
			array_fill_keys(
				array_map( 'sanitize_title', array_keys( $product->get_variation_attributes() ) ),
				''
			)
		);
		$variation_id = $variation_object->save();
		if ( ! $variation_id ) {
			wp_send_json_error( array( 'error' => __( 'Could not create variation', 'storesuite' ) ) );
		}
		$html = $this->get_variation_row_html( $product_id, $variation_id, $loop );
		wp_send_json_success( array( 'variation_id' => $variation_id, 'loop' => $loop, 'html' => $html ) );
	}

	/**
	 * AJAX: Link all variations (create all attribute combinations).
	 */
	public function ajax_link_all_variations() {
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['nonce'] ) ), 'storesuite_variations' ) ) {
			wp_send_json_error( array( 'error' => __( 'Invalid nonce', 'storesuite' ) ) );
		}
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_send_json_error( array( 'error' => __( 'Permission denied', 'storesuite' ) ) );
		}
		$product_id = isset( $_POST['product_id'] ) ? absint( $_POST['product_id'] ) : 0;
		if ( ! $product_id ) {
			wp_send_json_error( array( 'error' => __( 'Invalid product', 'storesuite' ) ) );
		}
		$product = wc_get_product( $product_id );
		if ( ! $product || ! $product->is_type( 'variable' ) ) {
			wp_send_json_error( array( 'error' => __( 'Not a variable product', 'storesuite' ) ) );
		}
		$data_store = $product->get_data_store();
		if ( ! is_callable( array( $data_store, 'create_all_product_variations' ) ) ) {
			wp_send_json_error( array( 'error' => __( 'Could not generate variations.', 'storesuite' ) ) );
		}
		$max_linked = defined( 'WC_MAX_LINKED_VARIATIONS' ) ? WC_MAX_LINKED_VARIATIONS : 49;
		$added      = $data_store->create_all_product_variations( $product, $max_linked );
		$data_store->sort_all_product_variations( $product_id );

		if ( 0 === $added ) {
			wp_send_json_error( array( 'error' => __( 'No new variations created. Make sure you have saved at least one attribute marked "Used for variations" with values.', 'storesuite' ) ) );
		}
		wp_send_json_success( array( 'added' => $added, 'message' => sprintf( __( '%d variation(s) created.', 'storesuite' ), $added ) ) );
	}

	/**
	 * AJAX: Remove a variation.
	 */
	public function ajax_remove_variation() {
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['nonce'] ) ), 'storesuite_variations' ) ) {
			wp_send_json_error( array( 'error' => __( 'Invalid nonce', 'storesuite' ) ) );
		}
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_send_json_error( array( 'error' => __( 'Permission denied', 'storesuite' ) ) );
		}
		$variation_id = isset( $_POST['variation_id'] ) ? absint( $_POST['variation_id'] ) : 0;
		if ( ! $variation_id ) {
			wp_send_json_error( array( 'error' => __( 'Invalid variation', 'storesuite' ) ) );
		}
		$variation = wc_get_product( $variation_id );
		if ( ! $variation || ! $variation->is_type( 'variation' ) ) {
			wp_send_json_error( array( 'error' => __( 'Variation not found', 'storesuite' ) ) );
		}
		$variation->delete( true );
		wp_send_json_success( array( 'message' => __( 'Variation removed', 'storesuite' ) ) );
	}

	/**
	 * AJAX: Get HTML for a predefined (global) attribute row.
	 */
	public function ajax_get_predefined_attribute() {
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['nonce'] ) ), 'storesuite_variations' ) ) {
			wp_send_json_error( array( 'error' => __( 'Invalid nonce', 'storesuite' ) ) );
		}
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_send_json_error( array( 'error' => __( 'Permission denied', 'storesuite' ) ) );
		}
		$taxonomy = isset( $_POST['taxonomy'] ) ? sanitize_text_field( wp_unslash( $_POST['taxonomy'] ) ) : '';
		$i        = isset( $_POST['i'] ) ? absint( $_POST['i'] ) : 0;
		$product_id = isset( $_POST['product_id'] ) ? absint( $_POST['product_id'] ) : 0;
		if ( ! $taxonomy ) {
			wp_send_json_error( array( 'error' => __( 'Invalid attribute', 'storesuite' ) ) );
		}
		$attribute = array(
			'name'         => $taxonomy,
			'value'        => '',
			'is_visible'   => 1,
			'is_variation' => 1,
			'is_taxonomy'  => 1,
		);
		global $wc_product_attributes;
		$attribute_taxonomy = isset( $wc_product_attributes[ $taxonomy ] ) ? $wc_product_attributes[ $taxonomy ] : null;
		$attribute_label   = wc_attribute_label( $taxonomy );
		ob_start();
		storesuite_get_template_part(
			'products/edit/html-product-attribute',
			'',
			array(
				'thepostid'          => $product_id,
				'taxonomy'           => $taxonomy,
				'attribute_taxonomy' => $attribute_taxonomy,
				'attribute_label'    => $attribute_label,
				'attribute'          => $attribute,
				'metabox_class'      => array( 'taxonomy', $taxonomy ),
				'position'           => $i,
				'i'                  => $i,
			)
		);
		$html = ob_get_clean();
		wp_send_json_success( array( 'html' => $html ) );
	}

	/**
	 * Get a single variation row HTML for the given variation.
	 *
	 * @param int $product_id   Parent product ID.
	 * @param int $variation_id Variation ID.
	 * @param int $loop         Loop index.
	 * @return string
	 */
	public function get_variation_row_html( $product_id, $variation_id, $loop ) {
		$product   = wc_get_product( $product_id );
		$variation = get_post( $variation_id );
		if ( ! $product || ! $product->is_type( 'variable' ) || ! $variation ) {
			return '';
		}
		$attributes     = (array) maybe_unserialize( get_post_meta( $product_id, '_product_attributes', true ) );
		$variation_data = get_post_meta( $variation_id );
		$variation_data['variation_post_id'] = $variation_id;
		$_thumbnail_id  = isset( $variation_data['_thumbnail_id'][0] ) ? absint( $variation_data['_thumbnail_id'][0] ) : 0;
		$image = $_thumbnail_id ? wp_get_attachment_thumb_url( $_thumbnail_id ) : wc_placeholder_img_src();
		$shipping_classes = get_the_terms( $variation_id, 'product_shipping_class' );
		$shipping_class   = ( $shipping_classes && ! is_wp_error( $shipping_classes ) ) ? current( $shipping_classes )->term_id : '';
		$tax_class_options = array( '' => __( 'Standard', 'storesuite' ) );
		if ( class_exists( 'WC_Tax' ) ) {
			foreach ( \WC_Tax::get_tax_classes() as $class ) {
				$tax_class_options[ sanitize_title( $class ) ] = esc_html( $class );
			}
		}
		$parent_data = array(
			'id'                => $product_id,
			'attributes'        => $attributes,
			'tax_class_options' => $tax_class_options,
			'sku'               => $product->get_sku(),
			'weight'            => $product->get_weight(),
			'length'            => $product->get_length(),
			'width'             => $product->get_width(),
			'height'            => $product->get_height(),
			'backorder_options' => wc_get_product_backorder_options(),
			'stock_status_options' => wc_get_product_stock_status_options(),
		);
		$_stock_status = isset( $variation_data['_stock_status'][0] ) ? $variation_data['_stock_status'][0] : 'instock';
		$_backorders   = isset( $variation_data['_backorders'][0] ) ? $variation_data['_backorders'][0] : 'no';
		$_regular_price = isset( $variation_data['_regular_price'][0] ) ? $variation_data['_regular_price'][0] : '';
		$_sale_price    = isset( $variation_data['_sale_price'][0] ) ? $variation_data['_sale_price'][0] : '';
		$_sale_price_dates_from = isset( $variation_data['_sale_price_dates_from'][0] ) ? $variation_data['_sale_price_dates_from'][0] : '';
		$_sale_price_dates_to  = isset( $variation_data['_sale_price_dates_to'][0] ) ? $variation_data['_sale_price_dates_to'][0] : '';
		$_variation_description = isset( $variation_data['_variation_description'][0] ) ? $variation_data['_variation_description'][0] : '';
		ob_start();
		storesuite_get_template_part(
			'products/edit/html-product-variation',
			'',
			array(
				'loop'            => $loop,
				'variation_id'    => $variation_id,
				'variation'       => $variation,
				'parent_data'     => $parent_data,
				'variation_data'  => $variation_data,
				'_thumbnail_id'   => $_thumbnail_id,
				'image'           => $image,
				'shipping_class'  => $shipping_class,
				'_sku'            => isset( $variation_data['_sku'][0] ) ? $variation_data['_sku'][0] : '',
				'_stock'          => isset( $variation_data['_stock'][0] ) ? $variation_data['_stock'][0] : '',
				'_manage_stock'   => isset( $variation_data['_manage_stock'][0] ) ? $variation_data['_manage_stock'][0] : '',
				'_stock_status'   => $_stock_status,
				'_backorders'     => $_backorders,
				'_regular_price'  => $_regular_price,
				'_sale_price'     => $_sale_price,
				'_sale_price_dates_from' => $_sale_price_dates_from,
				'_sale_price_dates_to'   => $_sale_price_dates_to,
				'_weight'         => isset( $variation_data['_weight'][0] ) ? $variation_data['_weight'][0] : '',
				'_length'         => isset( $variation_data['_length'][0] ) ? $variation_data['_length'][0] : '',
				'_width'          => isset( $variation_data['_width'][0] ) ? $variation_data['_width'][0] : '',
				'_height'         => isset( $variation_data['_height'][0] ) ? $variation_data['_height'][0] : '',
				'_variation_description' => $_variation_description,
			)
		);
		return ob_get_clean();
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
}
