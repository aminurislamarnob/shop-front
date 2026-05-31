<?php

namespace PluginizeLab\StoreSuite\Product;

use WC_Product_Attribute;
use WC_Product_Variation;
use WC_Product_Variable;
use WC_Meta_Box_Product_Data;
use Automattic\WooCommerce\Enums\ProductType;
use Automattic\WooCommerce\Internal\CostOfGoodsSold\CostOfGoodsSoldController;
use Exception;
use WC_Product_Factory;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Plugin variation and attributes AJAX controller class.
 */
class VariationAjax {
	/**
	 * The constructor.
	 */
	public function __construct() {
		add_action( 'wp_ajax_storesuite_add_attribute', array( $this, 'storesuite_ajax_add_attribute' ), 10 );
		add_action( 'wp_ajax_storesuite_save_attributes', array( $this, 'storesuite_ajax_save_attributes' ), 10 );
		add_action( 'wp_ajax_storesuite_load_variations', array( $this, 'load_variations' ), 10 );
		add_action( 'wp_ajax_storesuite_generate_variations', array( $this, 'generate_variations' ), 10 );
		add_action( 'wp_ajax_storesuite_save_variations', array( $this, 'save_variations' ), 10 );
		add_action( 'wp_ajax_storesuite_add_variation', array( $this, 'add_variation' ), 10 );
		add_action( 'wp_ajax_storesuite_remove_variation', array( $this, 'remove_variation' ), 10 );
		add_action( 'wp_ajax_storesuite_bulk_edit_variations', array( $this, 'bulk_edit_variations' ), 10 );
		add_action( 'wp_ajax_storesuite_save_default_attributes', array( $this, 'save_default_attributes' ), 10 );
	}

    /**
     * Add attribute AJAX handler
     *
     * @return void
     */
    public function storesuite_ajax_add_attribute(){
        if ( ! check_ajax_referer( 'add-attribute', 'security', false ) ) {
            wp_send_json_error( array( 'message' => __( 'Invalid nonce.', 'storesuite' ) ) );
        }

        $taxonomy     = isset( $_POST['taxonomy'] ) ? sanitize_text_field( wp_unslash( $_POST['taxonomy'] ) ) : '';
        $index        = isset( $_POST['i'] ) ? absint( $_POST['i'] ) : 0;
        $product_id   = isset( $_POST['product_id'] ) ? absint( $_POST['product_id'] ) : 0;
        $product_type = isset( $_POST['product_type'] ) ? wc_clean( wp_unslash( $_POST['product_type'] ) ) : 'simple';
    
        // Build an attribute object or array compatible with your template.
        $attribute = new WC_Product_Attribute();
        $attribute->set_id( wc_attribute_taxonomy_id_by_name( $taxonomy ) );
        $attribute->set_name( $taxonomy );
        $attribute->set_visible( true );
        $attribute->set_variation( 'variable' === $product_type );

        /* phpcs:disable WooCommerce.Commenting.CommentHooks.MissingHookComment */
		$attribute->set_visible( apply_filters( 'woocommerce_attribute_default_visibility', 1 ) );
		$attribute->set_variation(
			apply_filters(
				'woocommerce_attribute_default_is_variation',
				ProductType::VARIABLE === $product_type ? 1 : 0,
				$product_type
			)
		);
		/* phpcs: enable */
    
        ob_start();
        storesuite_get_template_part(
            'products/product-attribute-row',
            '',
            array(
                'attribute'  => $attribute,
                'i'          => $index,
                'product'    => $product_id ? wc_get_product( $product_id ) : null,
                'product_id' => $product_id,
            )
        );
        $html = ob_get_clean();
    
        if ( ! $html ) {
            wp_send_json_error(
                array( 'message' => __( 'Could not generate attribute row.', 'storesuite' ) )
            );
        }
    
        wp_send_json_success(
            array(
                'html'    => $html,
                'message' => __( 'Attribute added.', 'storesuite' ),
            )
        );
    }

	/**
	 * Load product variations via AJAX with pagination.
	 *
	 * @return void
	 */
	public function load_variations() {
		if ( ! check_ajax_referer( 'storesuite-variation-nonce', 'security', false ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid security token.', 'storesuite' ) ) );
		}

		if ( ! current_user_can( 'edit_products' ) || ! isset( $_POST['product_id'] ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid request.', 'storesuite' ) ) );
		}

		$product_id = absint( wp_unslash( $_POST['product_id'] ) );
		$page       = isset( $_POST['page'] ) ? absint( $_POST['page'] ) : 1;
		$per_page   = isset( $_POST['per_page'] ) ? absint( $_POST['per_page'] ) : 15;
		$product    = wc_get_product( $product_id );

		if ( ! $product || ! $product->is_type( 'variable' ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid variable product.', 'storesuite' ) ) );
		}

		$total       = count( $product->get_children() );
		$total_pages = max( 1, (int) ceil( $total / $per_page ) );
		$page        = min( $page, $total_pages );
		$offset      = ( $page - 1 ) * $per_page;

		// Match the WooCommerce admin variations order: menu_order ASC, ID DESC.
		$variations = wc_get_products(
			array(
				'status'  => array( 'private', 'publish' ),
				'type'    => 'variation',
				'parent'  => $product_id,
				'limit'   => $per_page,
				'page'    => $page,
				'orderby' => array(
					'menu_order' => 'ASC',
					'ID'         => 'DESC',
				),
				'return'  => 'objects',
			)
		);

		ob_start();
		if ( empty( $variations ) ) {
			storesuite_get_template_part( 'products/variation-empty-state' );
		} else {
			foreach ( $variations as $loop => $variation ) {
				if ( ! $variation || ! $variation->is_type( 'variation' ) ) {
					continue;
				}

				storesuite_get_template_part(
					'products/product-variation-row',
					'',
					array(
						'variation'    => $variation,
						'variation_id' => $variation->get_id(),
						'loop'         => $offset + $loop,
						'parent'       => $product,
					)
				);
			}
		}
		$html = ob_get_clean();

		wp_send_json_success(
			array(
				'html'        => $html,
				'total'       => $total,
				'total_pages' => $total_pages,
				'page'        => $page,
			)
		);
	}

	/**
	 * Generate variations from all attribute combinations.
	 *
	 * @return void
	 */
	public function generate_variations() {
		if ( ! check_ajax_referer( 'storesuite-variation-nonce', 'security', false ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid security token.', 'storesuite' ) ) );
		}

		if ( ! current_user_can( 'edit_products' ) || ! isset( $_POST['product_id'] ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid request.', 'storesuite' ) ) );
		}

		$product_id = absint( wp_unslash( $_POST['product_id'] ) );
		$product    = wc_get_product( $product_id );

		if ( ! $product || ! $product->is_type( 'variable' ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid variable product.', 'storesuite' ) ) );
		}

		// Collect variation attribute options.
		$variation_attributes = array();
		foreach ( $product->get_attributes( 'edit' ) as $attribute ) {
			if ( ! $attribute->get_variation() ) {
				continue;
			}

			$attr_key = sanitize_title( $attribute->get_name() );

			if ( $attribute->is_taxonomy() ) {
				$terms   = get_terms( array(
					'taxonomy'   => $attribute->get_name(),
					'orderby'    => 'name',
					'hide_empty' => false,
					'fields'     => 'slugs',
				) );
				$options = is_wp_error( $terms ) ? array() : $terms;

				// Only include terms that are assigned to this product attribute.
				$assigned_ids = $attribute->get_options();
				if ( ! empty( $assigned_ids ) ) {
					$assigned_terms = get_terms( array(
						'taxonomy'   => $attribute->get_name(),
						'include'    => $assigned_ids,
						'hide_empty' => false,
						'fields'     => 'slugs',
					) );
					$options = is_wp_error( $assigned_terms ) ? array() : $assigned_terms;
				}
			} else {
				$options = $attribute->get_options();
			}

			if ( ! empty( $options ) ) {
				$variation_attributes[ $attr_key ] = $options;
			}
		}

		if ( empty( $variation_attributes ) ) {
			wp_send_json_error( array( 'message' => __( 'No variation attributes found. Add attributes and mark them as "Used for variations" first.', 'storesuite' ) ) );
		}

		// Build Cartesian product of all attribute options.
		$combinations = array( array() );
		foreach ( $variation_attributes as $attr_key => $options ) {
			$new_combinations = array();
			foreach ( $combinations as $combo ) {
				foreach ( $options as $option ) {
					$new_combo               = $combo;
					$new_combo[ $attr_key ]  = $option;
					$new_combinations[]      = $new_combo;
				}
			}
			$combinations = $new_combinations;
		}

		// Cap to prevent timeout.
		$max_variations = apply_filters( 'storesuite_max_variations_per_generate', 50 );
		$created        = 0;
		$data_store     = \WC_Data_Store::load( 'product' );

		foreach ( $combinations as $combo ) {
			if ( $created >= $max_variations ) {
				break;
			}

			// Check if this combination already exists.
			$existing = $data_store->find_matching_product_variation( $product, $combo );
			if ( $existing ) {
				continue;
			}

			$variation = new WC_Product_Variation();
			$variation->set_parent_id( $product_id );
			$variation->set_status( 'publish' );
			$variation->set_attributes( $combo );
			$variation->save();

			++$created;
		}

		// Sync parent product data (price range, stock, etc.).
		WC_Product_Variable::sync( $product_id );

		$total_possible = count( $combinations );
		$skipped        = $total_possible - $created;
		$message        = sprintf(
			/* translators: 1: number of variations created, 2: number of existing variations skipped */
			__( '%1$d variations created, %2$d skipped (already exist).', 'storesuite' ),
			$created,
			$skipped > 0 ? $skipped : 0
		);

		if ( $created >= $max_variations && $total_possible > $max_variations ) {
			$message .= ' ' . sprintf(
				/* translators: %d: max number of variations per batch */
				__( 'Generation capped at %d per batch. Run again to create more.', 'storesuite' ),
				$max_variations
			);
		}

		wp_send_json_success(
			array(
				'created' => $created,
				'total'   => $total_possible,
				'message' => $message,
			)
		);
	}

	/**
	 * Add a single blank variation via AJAX.
	 *
	 * Creates an empty WC_Product_Variation under the given parent product,
	 * renders the variation row template, and returns the HTML.
	 *
	 * @return void
	 */
	public function add_variation() {
		if ( ! check_ajax_referer( 'add-variation', 'security', false ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid security token.', 'storesuite' ) ) );
		}

		if ( ! current_user_can( 'edit_products' ) || ! isset( $_POST['product_id'] ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid request.', 'storesuite' ) ) );
		}

		$product_id = absint( wp_unslash( $_POST['product_id'] ) );
		$product    = wc_get_product( $product_id );

		if ( ! $product || ! $product->is_type( 'variable' ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid variable product.', 'storesuite' ) ) );
		}

		$variation = new WC_Product_Variation();
		$variation->set_parent_id( $product_id );
		$variation->set_status( 'publish' );
		$variation->save();

		$variation_id = $variation->get_id();

		// Determine loop index: total children count (the new one is already included).
		$loop = count( $product->get_children() ) - 1;

		ob_start();
		storesuite_get_template_part(
			'products/product-variation-row',
			'',
			array(
				'variation'    => $variation,
				'variation_id' => $variation_id,
				'loop'         => $loop,
				'parent'       => $product,
			)
		);
		$html = ob_get_clean();

		wp_send_json_success(
			array(
				'html'         => $html,
				'variation_id' => $variation_id,
				'message'      => __( 'Variation added.', 'storesuite' ),
			)
		);
	}

	/**
	 * Remove a single variation via AJAX.
	 *
	 * Validates the variation exists and is of type 'variation',
	 * permanently deletes it, and syncs the parent product.
	 *
	 * @return void
	 */
	public function remove_variation() {
		if ( ! check_ajax_referer( 'remove-variation', 'security', false ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid security token.', 'storesuite' ) ) );
		}

		if ( ! current_user_can( 'edit_products' ) || ! isset( $_POST['variation_id'] ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid request.', 'storesuite' ) ) );
		}

		$variation_id = absint( wp_unslash( $_POST['variation_id'] ) );
		$variation    = wc_get_product( $variation_id );

		if ( ! $variation || ! $variation->is_type( 'variation' ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid variation.', 'storesuite' ) ) );
		}

		$parent_id = $variation->get_parent_id();
		$variation->delete( true );

		// Sync parent product data (price range, stock, etc.).
		WC_Product_Variable::sync( $parent_id );

		wp_send_json_success(
			array(
				'message' => __( 'Variation deleted.', 'storesuite' ),
			)
		);
	}

	/**
	 * Save variation data via AJAX.
	 *
	 * Iterates over submitted variation fields and persists each variation.
	 * Syncs parent product data (price range, stock, etc.) at the end.
	 *
	 * @return void
	 */
	public function save_variations() {
		if ( ! check_ajax_referer( 'save-variations', 'security', false ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid security token.', 'storesuite' ) ) );
		}

		if ( ! current_user_can( 'edit_products' ) || ! isset( $_POST['product_id'] ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid request.', 'storesuite' ) ) );
		}

		$product_id = absint( wp_unslash( $_POST['product_id'] ) );
		$product    = wc_get_product( $product_id );

		if ( ! $product || ! $product->is_type( 'variable' ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid variable product.', 'storesuite' ) ) );
		}

		// phpcs:disable WordPress.Security.ValidatedSanitizedInput
		$variable_post_id = isset( $_POST['variable_post_id'] ) ? array_map( 'absint', (array) wp_unslash( $_POST['variable_post_id'] ) ) : array();
		// phpcs:enable

		if ( empty( $variable_post_id ) ) {
			wp_send_json_error( array( 'message' => __( 'No variations to save.', 'storesuite' ) ) );
		}

		$saved = 0;

		foreach ( $variable_post_id as $i => $variation_id ) {
			$variation = wc_get_product( $variation_id );

			if ( ! $variation || ! $variation->is_type( 'variation' ) ) {
				continue;
			}

			// Enabled status.
			$enabled = isset( $_POST['variable_enabled'][ $i ] ) ? 'publish' : 'private';
			$variation->set_status( $enabled );

			// SKU.
			if ( isset( $_POST['variable_sku'][ $i ] ) ) {
				$variation->set_sku( wc_clean( wp_unslash( $_POST['variable_sku'][ $i ] ) ) );
			}

			// GTIN, UPC, EAN, or ISBN.
			if ( isset( $_POST['variable_global_unique_id'][ $i ] ) ) {
				$variation->set_global_unique_id( wc_clean( wp_unslash( $_POST['variable_global_unique_id'][ $i ] ) ) );
			}

			// Prices.
			if ( isset( $_POST['variable_regular_price'][ $i ] ) ) {
				$variation->set_regular_price( wc_clean( wp_unslash( $_POST['variable_regular_price'][ $i ] ) ) );
			}
			if ( isset( $_POST['variable_sale_price'][ $i ] ) ) {
				$variation->set_sale_price( wc_clean( wp_unslash( $_POST['variable_sale_price'][ $i ] ) ) );
			}

			// Sale price schedule.
			$date_from = isset( $_POST['variable_sale_price_dates_from'][ $i ] ) ? wc_clean( wp_unslash( $_POST['variable_sale_price_dates_from'][ $i ] ) ) : '';
			$date_to   = isset( $_POST['variable_sale_price_dates_to'][ $i ] ) ? wc_clean( wp_unslash( $_POST['variable_sale_price_dates_to'][ $i ] ) ) : '';
			$variation->set_date_on_sale_from( $date_from ? wc_clean( $date_from ) : '' );
			$variation->set_date_on_sale_to( $date_to ? wc_clean( $date_to ) : '' );

			// Stock management.
			$manage_stock = isset( $_POST['variable_manage_stock'][ $i ] );
			$variation->set_manage_stock( $manage_stock );

			if ( $manage_stock ) {
				if ( isset( $_POST['variable_stock_qty'][ $i ] ) ) {
					$variation->set_stock_quantity( wc_clean( wp_unslash( $_POST['variable_stock_qty'][ $i ] ) ) );
				}

				// Backorders.
				if ( isset( $_POST['variable_backorders'][ $i ] ) ) {
					$variation->set_backorders( wc_clean( wp_unslash( $_POST['variable_backorders'][ $i ] ) ) );
				}

				// Low stock threshold.
				$low_stock = isset( $_POST['variable_low_stock_amount'][ $i ] ) ? wc_clean( wp_unslash( $_POST['variable_low_stock_amount'][ $i ] ) ) : '';
				$variation->set_low_stock_amount( '' === $low_stock ? '' : wc_stock_amount( $low_stock ) );
			} else {
				$variation->set_backorders( 'no' );
				$variation->set_low_stock_amount( '' );
			}

			if ( isset( $_POST['variable_stock_status'][ $i ] ) ) {
				$variation->set_stock_status( wc_clean( wp_unslash( $_POST['variable_stock_status'][ $i ] ) ) );
			}

			// Cost of Goods Sold value.
			if ( wc_get_container()->get( CostOfGoodsSoldController::class )->feature_is_enabled() ) {
				$cogs_value = isset( $_POST['variable_cogs_value'][ $i ] ) ? wc_clean( wp_unslash( $_POST['variable_cogs_value'][ $i ] ) ) : '';
				$variation->set_cogs_value( '' === $cogs_value ? null : (float) wc_format_decimal( $cogs_value ) );
			}

			// Virtual & Downloadable.
			$variation->set_virtual( isset( $_POST['variable_is_virtual'][ $i ] ) );
			$is_downloadable = isset( $_POST['variable_is_downloadable'][ $i ] );
			$variation->set_downloadable( $is_downloadable );

			if ( $is_downloadable ) {
				// Downloadable files.
				$file_names  = isset( $_POST['_wc_variation_file_names'][ $i ] ) ? array_map( 'wc_clean', wp_unslash( (array) $_POST['_wc_variation_file_names'][ $i ] ) ) : array();
				$file_urls   = isset( $_POST['_wc_variation_file_urls'][ $i ] ) ? wp_unslash( (array) $_POST['_wc_variation_file_urls'][ $i ] ) : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
				$file_hashes = isset( $_POST['_wc_variation_file_hashes'][ $i ] ) ? array_map( 'wc_clean', wp_unslash( (array) $_POST['_wc_variation_file_hashes'][ $i ] ) ) : array();

				$downloads = array();
				foreach ( $file_urls as $index => $url ) {
					$url = trim( $url );
					if ( '' === $url ) {
						continue;
					}

					$key      = ! empty( $file_hashes[ $index ] ) ? $file_hashes[ $index ] : wp_generate_uuid4();
					$download = new \WC_Product_Download();
					$download->set_id( $key );
					$download->set_name( ! empty( $file_names[ $index ] ) ? $file_names[ $index ] : wc_get_filename_from_url( $url ) );
					$download->set_file( esc_url_raw( $url ) );
					$downloads[ $key ] = $download;
				}
				$variation->set_downloads( $downloads );

				// Download limit.
				$download_limit = isset( $_POST['variable_download_limit'][ $i ] ) ? wc_clean( wp_unslash( $_POST['variable_download_limit'][ $i ] ) ) : '';
				$variation->set_download_limit( '' === $download_limit ? '' : absint( $download_limit ) );

				// Download expiry.
				$download_expiry = isset( $_POST['variable_download_expiry'][ $i ] ) ? wc_clean( wp_unslash( $_POST['variable_download_expiry'][ $i ] ) ) : '';
				$variation->set_download_expiry( '' === $download_expiry ? '' : absint( $download_expiry ) );
			}

			// Weight & Dimensions (only if not virtual).
			if ( isset( $_POST['variable_weight'][ $i ] ) ) {
				$variation->set_weight( wc_clean( wp_unslash( $_POST['variable_weight'][ $i ] ) ) );
			}
			if ( isset( $_POST['variable_length'][ $i ] ) ) {
				$variation->set_length( wc_clean( wp_unslash( $_POST['variable_length'][ $i ] ) ) );
			}
			if ( isset( $_POST['variable_width'][ $i ] ) ) {
				$variation->set_width( wc_clean( wp_unslash( $_POST['variable_width'][ $i ] ) ) );
			}
			if ( isset( $_POST['variable_height'][ $i ] ) ) {
				$variation->set_height( wc_clean( wp_unslash( $_POST['variable_height'][ $i ] ) ) );
			}

			// Shipping class (0 = same as parent).
			if ( isset( $_POST['variable_shipping_class'][ $i ] ) ) {
				$variation->set_shipping_class_id( absint( wp_unslash( $_POST['variable_shipping_class'][ $i ] ) ) );
			}

			// Tax class ('parent' = same as parent).
			if ( isset( $_POST['variable_tax_class'][ $i ] ) ) {
				$variation->set_tax_class( sanitize_title( wp_unslash( $_POST['variable_tax_class'][ $i ] ) ) );
			}

			// Description.
			if ( isset( $_POST['variable_description'][ $i ] ) ) {
				$variation->set_description( wc_clean( wp_unslash( $_POST['variable_description'][ $i ] ) ) );
			}

			// Image.
			if ( isset( $_POST['variable_image_id'][ $i ] ) ) {
				$variation->set_image_id( absint( $_POST['variable_image_id'][ $i ] ) );
			}

			// Menu order.
			if ( isset( $_POST['variable_menu_order'][ $i ] ) ) {
				$variation->set_menu_order( absint( $_POST['variable_menu_order'][ $i ] ) );
			}

			// Attributes.
			$parent_attributes = $product->get_attributes( 'edit' );
			$variation_attrs   = array();

			foreach ( $parent_attributes as $attribute ) {
				if ( ! $attribute->get_variation() ) {
					continue;
				}

				$attr_key = sanitize_title( $attribute->get_name() );
				$post_key = 'attribute_' . $attr_key;

				if ( isset( $_POST[ $post_key ][ $i ] ) ) {
					$variation_attrs[ $attr_key ] = wc_clean( wp_unslash( $_POST[ $post_key ][ $i ] ) );
				}
			}

			if ( ! empty( $variation_attrs ) ) {
				$variation->set_attributes( $variation_attrs );
			}

			$variation->save();
			++$saved;
		}

		// Sync parent product data (price range, stock, etc.).
		WC_Product_Variable::sync( $product_id );

		wp_send_json_success(
			array(
				'saved'   => $saved,
				'message' => sprintf(
					/* translators: %d: number of variations saved */
					__( '%d variation(s) saved.', 'storesuite' ),
					$saved
				),
			)
		);
	}

	/**
	 * Bulk edit all variations of a variable product.
	 *
	 * Supported actions: variable_regular_price, variable_sale_price,
	 * variable_stock_status, toggle_enabled, delete_all.
	 *
	 * @return void
	 */
	public function bulk_edit_variations() {
		if ( ! check_ajax_referer( 'bulk-edit-variations', 'security', false ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid security token.', 'storesuite' ) ) );
		}

		if ( ! current_user_can( 'edit_products' ) || ! isset( $_POST['product_id'] ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid request.', 'storesuite' ) ) );
		}

		$product_id  = absint( wp_unslash( $_POST['product_id'] ) );
		$product     = wc_get_product( $product_id );
		$bulk_action = isset( $_POST['bulk_action'] ) ? sanitize_text_field( wp_unslash( $_POST['bulk_action'] ) ) : '';
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- wc_clean sanitizes recursively.
		$data = isset( $_POST['data'] ) && is_array( $_POST['data'] ) ? wc_clean( wp_unslash( $_POST['data'] ) ) : array();

		if ( ! $product || ! $product->is_type( 'variable' ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid variable product.', 'storesuite' ) ) );
		}

		$children = $product->get_children();

		if ( empty( $children ) ) {
			wp_send_json_error( array( 'message' => __( 'No variations found.', 'storesuite' ) ) );
		}

		$allowed_actions = array(
			'toggle_enabled',
			'toggle_downloadable',
			'toggle_virtual',
			'toggle_manage_stock',
			'variable_regular_price',
			'variable_regular_price_increase',
			'variable_regular_price_decrease',
			'variable_sale_price',
			'variable_sale_price_increase',
			'variable_sale_price_decrease',
			'variable_sale_schedule',
			'variable_unset_cogs_value',
			'variable_stock',
			'variable_stock_status_instock',
			'variable_stock_status_outofstock',
			'variable_stock_status_onbackorder',
			'variable_low_stock_amount',
			'variable_length',
			'variable_width',
			'variable_height',
			'variable_weight',
			'variable_download_limit',
			'variable_download_expiry',
			'delete_all',
		);

		if ( ! in_array( $bulk_action, $allowed_actions, true ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid bulk action.', 'storesuite' ) ) );
		}

		// Keep only valid variation objects.
		$variations = array();
		foreach ( $children as $child_id ) {
			$variation = wc_get_product( $child_id );
			if ( $variation && $variation->is_type( 'variation' ) ) {
				$variations[] = $variation;
			}
		}

		if ( 'delete_all' === $bulk_action ) {
			$deleted = 0;
			foreach ( $variations as $variation ) {
				$variation->delete( true );
				++$deleted;
			}

			WC_Product_Variable::sync( $product_id );
			wc_delete_product_transients( $product_id );

			wp_send_json_success(
				array(
					'updated' => $deleted,
					'message' => sprintf(
						/* translators: %d: number of variations deleted */
						__( '%d variation(s) deleted.', 'storesuite' ),
						$deleted
					),
				)
			);
		}

		$value = isset( $data['value'] ) ? $data['value'] : '';

		foreach ( $variations as $variation ) {
			switch ( $bulk_action ) {
				case 'toggle_enabled':
					$variation->set_status( 'private' === $variation->get_status( 'edit' ) ? 'publish' : 'private' );
					break;

				case 'toggle_downloadable':
					$variation->set_downloadable( ! $variation->get_downloadable( 'edit' ) );
					break;

				case 'toggle_virtual':
					$variation->set_virtual( ! $variation->get_virtual( 'edit' ) );
					break;

				case 'toggle_manage_stock':
					$variation->set_manage_stock( ! $variation->get_manage_stock( 'edit' ) );
					break;

				case 'variable_regular_price':
					$variation->set_regular_price( $value );
					break;

				case 'variable_sale_price':
					$variation->set_sale_price( $value );
					break;

				case 'variable_regular_price_increase':
					$this->adjust_price( $variation, 'regular_price', '+', $value );
					break;

				case 'variable_regular_price_decrease':
					$this->adjust_price( $variation, 'regular_price', '-', $value );
					break;

				case 'variable_sale_price_increase':
					$this->adjust_price( $variation, 'sale_price', '+', $value );
					break;

				case 'variable_sale_price_decrease':
					$this->adjust_price( $variation, 'sale_price', '-', $value );
					break;

				case 'variable_sale_schedule':
					if ( isset( $data['date_from'] ) && 'false' !== $data['date_from'] ) {
						$variation->set_date_on_sale_from( gmdate( 'Y-m-d 00:00:00', strtotime( $data['date_from'] ) ) );
					}
					if ( isset( $data['date_to'] ) && 'false' !== $data['date_to'] ) {
						$variation->set_date_on_sale_to( gmdate( 'Y-m-d 23:59:59', strtotime( $data['date_to'] ) ) );
					}
					break;

				case 'variable_unset_cogs_value':
					if ( wc_get_container()->get( CostOfGoodsSoldController::class )->feature_is_enabled() ) {
						$variation->set_cogs_value( null );
					}
					break;

				case 'variable_stock':
					if ( $variation->managing_stock() ) {
						$variation->set_stock_quantity( wc_stock_amount( $value ) );
					} else {
						$variation->set_stock_quantity( null );
					}
					break;

				case 'variable_stock_status_instock':
					$variation->set_stock_status( 'instock' );
					break;

				case 'variable_stock_status_outofstock':
					$variation->set_stock_status( 'outofstock' );
					break;

				case 'variable_stock_status_onbackorder':
					$variation->set_stock_status( 'onbackorder' );
					break;

				case 'variable_low_stock_amount':
					if ( $variation->managing_stock() ) {
						$variation->set_low_stock_amount( wc_stock_amount( $value ) );
					} else {
						$variation->set_low_stock_amount( '' );
					}
					break;

				case 'variable_length':
					$variation->set_length( $value );
					break;

				case 'variable_width':
					$variation->set_width( $value );
					break;

				case 'variable_height':
					$variation->set_height( $value );
					break;

				case 'variable_weight':
					$variation->set_weight( $value );
					break;

				case 'variable_download_limit':
					$variation->set_download_limit( $value );
					break;

				case 'variable_download_expiry':
					$variation->set_download_expiry( $value );
					break;
			}

			$variation->save();
		}

		WC_Product_Variable::sync( $product_id );
		wc_delete_product_transients( $product_id );

		$updated = count( $variations );

		wp_send_json_success(
			array(
				'updated' => $updated,
				'message' => sprintf(
					/* translators: %d: number of variations updated */
					__( '%d variation(s) updated.', 'storesuite' ),
					$updated
				),
			)
		);
	}

	/**
	 * Increase or decrease a variation price by a fixed amount or percentage.
	 *
	 * @param \WC_Product_Variation $variation Variation object.
	 * @param string                $field     Price field (regular_price or sale_price).
	 * @param string                $operator  Either '+' or '-'.
	 * @param string                $value     Fixed amount, or percentage when suffixed with '%'.
	 * @return void
	 */
	private function adjust_price( $variation, $field, $operator, $value ) {
		$field_value = $variation->{"get_$field"}( 'edit' );

		if ( '' === $field_value || null === $field_value ) {
			return;
		}

		if ( '%' === substr( $value, -1 ) ) {
			$percent      = wc_format_decimal( substr( $value, 0, -1 ) );
			$field_value += round( ( $field_value / 100 ) * $percent, wc_get_price_decimals() ) * ( '-' === $operator ? -1 : 1 );
		} else {
			$field_value += (float) $value * ( '-' === $operator ? -1 : 1 );
		}

		$variation->{"set_$field"}( $field_value );
	}

	/**
	 * Save default attributes for a variable product.
	 *
	 * Reads default_attribute_<key> fields from POST, builds the defaults
	 * array, and persists via $product->set_default_attributes().
	 *
	 * @return void
	 */
	public function save_default_attributes() {
		if ( ! check_ajax_referer( 'save-default-attributes', 'security', false ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid security token.', 'storesuite' ) ) );
		}

		if ( ! current_user_can( 'edit_products' ) || ! isset( $_POST['product_id'] ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid request.', 'storesuite' ) ) );
		}

		$product_id = absint( wp_unslash( $_POST['product_id'] ) );
		$product    = wc_get_product( $product_id );

		if ( ! $product || ! $product->is_type( 'variable' ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid variable product.', 'storesuite' ) ) );
		}

		$defaults   = array();
		$attributes = $product->get_attributes( 'edit' );

		foreach ( $attributes as $attribute ) {
			if ( ! $attribute->get_variation() ) {
				continue;
			}

			$attr_key = sanitize_title( $attribute->get_name() );
			$post_key = 'default_attribute_' . $attr_key;

			if ( isset( $_POST[ $post_key ] ) ) {
				$value = wc_clean( wp_unslash( $_POST[ $post_key ] ) );
				if ( '' !== $value ) {
					$defaults[ $attr_key ] = $value;
				}
			}
		}

		$product->set_default_attributes( $defaults );
		$product->save();

		wp_send_json_success(
			array(
				'message' => __( 'Default attributes saved.', 'storesuite' ),
			)
		);
	}

	/**
	 * Save product attributes via AJAX (mirrors WC_AJAX::save_attributes logic).
	 *
	 * Expects POST: product_id, security (nonce), and attribute_names, attribute_values,
	 * attribute_visibility, attribute_variation, attribute_position from the attributes list form.
	 *
	 * @return void
	 */
	public function storesuite_ajax_save_attributes() {
		if ( ! check_ajax_referer( 'save-attributes', 'security', false ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid security token.', 'storesuite' ) ) );
		}

		if ( ! current_user_can( 'edit_products' ) || ! isset( $_POST['product_id'] ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid request.', 'storesuite' ) ) );
		}

		$product_id = absint( wp_unslash( $_POST['product_id'] ) );
		$product    = wc_get_product( $product_id );
        $product_type = isset( $_POST['product_type'] ) ? wc_clean( wp_unslash( $_POST['product_type'] ) ) : ProductType::SIMPLE;

		if ( ! $product ) {
			wp_send_json_error( array( 'message' => __( 'Product not found.', 'storesuite' ) ) );
		}

		$response = array();

		try {
			$attribute_names  = isset( $_POST['attribute_names'] ) ? stripslashes_deep( (array) $_POST['attribute_names'] ) : array();
			$attribute_values = isset( $_POST['attribute_values'] ) ? stripslashes_deep( (array) $_POST['attribute_values'] ) : array();

			// For non-taxonomy (custom) attributes, Woo expects a string with values separated by "|"
			// instead of an array. Convert our multiple-select values to that format so that
			// WC_Meta_Box_Product_Data::prepare_attributes() treats them as text attributes,
			// not as term IDs (which would be cast to integers like 0).
			if ( ! empty( $attribute_names ) && ! empty( $attribute_values ) ) {
				foreach ( $attribute_names as $index => $name ) {
					if ( empty( $name ) || ! isset( $attribute_values[ $index ] ) ) {
						continue;
					}

					// Taxonomy/global attributes start with "pa_". Custom attributes do not.
					if ( 0 === strpos( $name, 'pa_' ) ) {
						continue;
					}

					if ( is_array( $attribute_values[ $index ] ) ) {
						$clean_values = array();
						foreach ( $attribute_values[ $index ] as $val ) {
							$val = wc_clean( wp_unslash( $val ) );
							if ( '' !== $val ) {
								$clean_values[] = $val;
							}
						}

						$attribute_values[ $index ] = implode( ' | ', $clean_values );
					}
				}
			}

			$data = array(
				'attribute_names'      => $attribute_names,
				'attribute_values'     => $attribute_values,
				'attribute_visibility' => isset( $_POST['attribute_visibility'] ) ? stripslashes_deep( (array) $_POST['attribute_visibility'] ) : array(),
				'attribute_variation'  => isset( $_POST['attribute_variation'] ) ? stripslashes_deep( (array) $_POST['attribute_variation'] ) : array(),
				'attribute_position'   => isset( $_POST['attribute_position'] ) ? stripslashes_deep( (array) $_POST['attribute_position'] ) : array(),
			);

			$attributes = WC_Meta_Box_Product_Data::prepare_attributes( $data );
            $classname  = WC_Product_Factory::get_product_classname( $product_id, $product_type );
            $product    = new $classname( $product_id );
			$product->set_attributes( $attributes );
			$product->save();

			ob_start();
			$product_attributes = $product->get_attributes( 'edit' );
			$i                  = 0;
			if ( ! empty( $product_attributes ) ) {
				foreach ( $product_attributes as $attribute ) {
					$args = array(
						'attribute'  => $attribute,
						'i'           => $i,
						'product'     => $product,
						'product_id'  => $product_id,
					);
					storesuite_get_template_part( 'products/product-attribute-row', '', $args );
					++$i;
				}
			}
			$response['html'] = ob_get_clean();
		} catch ( Exception $e ) {
			wp_send_json_error( array( 'message' => $e->getMessage() ) );
		}

		wp_send_json_success(
			array(
				'html'    => $response['html'],
				'message' => __( 'Attributes successfully saved.', 'storesuite' ),
			)
		);
	}
}
