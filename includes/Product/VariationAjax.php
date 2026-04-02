<?php

namespace PluginizeLab\StoreSuite\Product;

use WC_Product_Attribute;
use WC_Meta_Box_Product_Data;
use Automattic\WooCommerce\Enums\ProductType;
use Exception;
use WC_Data_Store;
use WC_Product_Factory;
use WC_Product_Variable;
use WC_Product_Variation;

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
		add_action( 'wp_ajax_storesuite_load_variations', array( $this, 'storesuite_ajax_load_variations' ), 10 );
		add_action( 'wp_ajax_storesuite_add_variation', array( $this, 'storesuite_ajax_add_variation' ), 10 );
		add_action( 'wp_ajax_storesuite_save_variations', array( $this, 'storesuite_ajax_save_variations' ), 10 );
		add_action( 'wp_ajax_storesuite_remove_variation', array( $this, 'storesuite_ajax_remove_variation' ), 10 );
		add_action( 'wp_ajax_storesuite_generate_variations', array( $this, 'storesuite_ajax_generate_variations' ), 10 );
		add_action( 'wp_ajax_storesuite_bulk_edit_variations', array( $this, 'storesuite_ajax_bulk_edit_variations' ), 10 );
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

		// Keep taxonomy attributes in WooCommerce format (pa_*) and resolve ID reliably.
		if ( '' !== $taxonomy && 0 !== strpos( $taxonomy, 'pa_' ) && taxonomy_exists( wc_attribute_taxonomy_name( $taxonomy ) ) ) {
			$taxonomy = wc_attribute_taxonomy_name( $taxonomy );
		}
		$taxonomy_id = wc_attribute_taxonomy_id_by_name( $taxonomy );
		if ( 0 === $taxonomy_id && 0 === strpos( $taxonomy, 'pa_' ) ) {
			$taxonomy_id = wc_attribute_taxonomy_id_by_name( substr( $taxonomy, 3 ) );
		}
    
        // Build an attribute object or array compatible with your template.
        $attribute = new WC_Product_Attribute();
		$is_variable_product = ( ProductType::VARIABLE === $product_type || 'variable' === $product_type );
		$attribute->set_id( $taxonomy_id );
		$attribute->set_name( $taxonomy );
		$attribute->set_visible( true );
		$attribute->set_variation( $is_variable_product ? 1 : 0 );

        /* phpcs:disable WooCommerce.Commenting.CommentHooks.MissingHookComment */
		$attribute->set_visible( apply_filters( 'woocommerce_attribute_default_visibility', 1 ) );
		$attribute->set_variation(
			apply_filters(
				'woocommerce_attribute_default_is_variation',
				$is_variable_product ? 1 : 0,
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

	/**
	 * Load variations list by page.
	 *
	 * @return void
	 */
	public function storesuite_ajax_load_variations() {
		if ( ! check_ajax_referer( 'storesuite-variation-nonce', 'security', false ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid security token.', 'storesuite' ) ) );
		}

		$product = $this->get_variable_product_from_request();
		if ( ! $product ) {
			wp_send_json_error( array( 'message' => __( 'Invalid variable product.', 'storesuite' ) ) );
		}

		$page     = isset( $_POST['page'] ) ? max( 1, absint( wp_unslash( $_POST['page'] ) ) ) : 1;
		$per_page = isset( $_POST['per_page'] ) ? max( 1, absint( wp_unslash( $_POST['per_page'] ) ) ) : 15;
		$children = $product->get_children();
		$total    = count( $children );
		$offset   = ( $page - 1 ) * $per_page;
		$page_ids = array_slice( $children, $offset, $per_page );

		ob_start();
		$loop = $offset;
		foreach ( $page_ids as $variation_id ) {
			$variation = wc_get_product( $variation_id );
			if ( ! $variation || ! $variation->is_type( 'variation' ) ) {
				continue;
			}
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
			++$loop;
		}

		wp_send_json_success(
			array(
				'html'        => ob_get_clean(),
				'total'       => $total,
				'total_pages' => (int) ceil( $total / $per_page ),
				'page'        => $page,
			)
		);
	}

	/**
	 * Add empty variation row.
	 *
	 * @return void
	 */
	public function storesuite_ajax_add_variation() {
		if ( ! check_ajax_referer( 'storesuite-variation-nonce', 'security', false ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid security token.', 'storesuite' ) ) );
		}

		if ( ! current_user_can( 'edit_products' ) ) {
			wp_send_json_error( array( 'message' => __( 'You are not allowed to add variations.', 'storesuite' ) ) );
		}

		$product = $this->get_variable_product_from_request();
		if ( ! $product ) {
			wp_send_json_error( array( 'message' => __( 'Invalid variable product.', 'storesuite' ) ) );
		}

		$variation = new WC_Product_Variation();
		$variation->set_parent_id( $product->get_id() );
		$variation->set_status( 'publish' );
		$variation_id = $variation->save();
		$loop         = isset( $_POST['loop'] ) ? absint( wp_unslash( $_POST['loop'] ) ) : 0;

		ob_start();
		storesuite_get_template_part(
			'products/product-variation-row',
			'',
			array(
				'variation'    => wc_get_product( $variation_id ),
				'variation_id' => $variation_id,
				'loop'         => $loop,
				'parent'       => $product,
			)
		);

		wp_send_json_success(
			array(
				'html'         => ob_get_clean(),
				'variation_id' => $variation_id,
			)
		);
	}

	/**
	 * Save variation rows.
	 *
	 * @return void
	 */
	public function storesuite_ajax_save_variations() {
		if ( ! check_ajax_referer( 'storesuite-variation-nonce', 'security', false ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid security token.', 'storesuite' ) ) );
		}

		if ( ! current_user_can( 'edit_products' ) ) {
			wp_send_json_error( array( 'message' => __( 'You are not allowed to save variations.', 'storesuite' ) ) );
		}

		$product = $this->get_variable_product_from_request();
		if ( ! $product ) {
			wp_send_json_error( array( 'message' => __( 'Invalid variable product.', 'storesuite' ) ) );
		}

		$variation_ids = isset( $_POST['variable_post_id'] ) ? array_map( 'absint', (array) wp_unslash( $_POST['variable_post_id'] ) ) : array();
		if ( empty( $variation_ids ) ) {
			wp_send_json_error( array( 'message' => __( 'No variations to save.', 'storesuite' ) ) );
		}

		$parent_attributes = $product->get_attributes( 'edit' );

		$submitted_combinations = array();

		foreach ( $variation_ids as $index => $variation_id ) {
			$variation = wc_get_product( $variation_id );
			if ( ! $variation || ! $variation->is_type( 'variation' ) ) {
				continue;
			}
			if ( (int) $variation->get_parent_id() !== (int) $product->get_id() ) {
				wp_send_json_error( array( 'message' => __( 'Variation parent mismatch.', 'storesuite' ) ) );
			}
			if ( ! current_user_can( 'edit_post', $variation_id ) ) {
				wp_send_json_error( array( 'message' => __( 'You are not allowed to edit one or more variations.', 'storesuite' ) ) );
			}

			wp_update_post(
				array(
					'ID'          => $variation_id,
					'post_status' => isset( $_POST['variable_enabled'][ $index ] ) ? 'publish' : 'private',
				)
			);

			$variation->set_sku( isset( $_POST['variable_sku'][ $index ] ) ? wc_clean( wp_unslash( $_POST['variable_sku'][ $index ] ) ) : '' );

			$regular_price = isset( $_POST['variable_regular_price'][ $index ] ) ? wc_format_decimal( wp_unslash( $_POST['variable_regular_price'][ $index ] ) ) : '';
			$sale_price    = isset( $_POST['variable_sale_price'][ $index ] ) ? wc_format_decimal( wp_unslash( $_POST['variable_sale_price'][ $index ] ) ) : '';

			if ( '' !== $sale_price && '' !== $regular_price && (float) $sale_price > (float) $regular_price ) {
				wp_send_json_error(
					array(
						/* translators: %d: variation id */
						'message' => sprintf( esc_html__( 'Sale price cannot be greater than regular price for variation #%d.', 'storesuite' ), absint( $variation_id ) ),
					)
				);
			}

			$variation->set_regular_price( $regular_price );
			$variation->set_sale_price( $sale_price );

			$manage_stock = isset( $_POST['variable_manage_stock'][ $index ] );
			$variation->set_manage_stock( $manage_stock );
			if ( $manage_stock && isset( $_POST['variable_stock'][ $index ] ) ) {
				$variation->set_stock_quantity( wc_stock_amount( wp_unslash( $_POST['variable_stock'][ $index ] ) ) );
			}
			$variation->set_stock_status( isset( $_POST['variable_stock_status'][ $index ] ) ? wc_clean( wp_unslash( $_POST['variable_stock_status'][ $index ] ) ) : 'instock' );
			$variation->set_menu_order( isset( $_POST['variation_menu_order'][ $index ] ) ? absint( wp_unslash( $_POST['variation_menu_order'][ $index ] ) ) : $index );

			$variation_attrs = array();
			foreach ( $parent_attributes as $attribute ) {
				if ( ! $attribute->get_variation() ) {
					continue;
				}
				$attribute_name = sanitize_title( $attribute->get_name() );
				$field_key      = 'attribute_' . $attribute_name;
				if ( isset( $_POST[ $field_key ][ $index ] ) ) {
					$variation_attrs[ $attribute_name ] = wc_clean( wp_unslash( $_POST[ $field_key ][ $index ] ) );
				}
			}

			$combination_hash = $this->get_variation_combination_hash( $variation_attrs );
			if ( isset( $submitted_combinations[ $combination_hash ] ) ) {
				wp_send_json_error( array( 'message' => __( 'Duplicate variation combinations are not allowed.', 'storesuite' ) ) );
			}
			$submitted_combinations[ $combination_hash ] = true;

			if ( ! $this->is_variation_combination_unique( $product, $variation_attrs, $variation_id ) ) {
				wp_send_json_error( array( 'message' => __( 'A variation with the same attribute combination already exists.', 'storesuite' ) ) );
			}

			$variation->set_attributes( $variation_attrs );
			$variation->save();
		}

		WC_Product_Variable::sync( $product->get_id() );

		wp_send_json_success(
			array(
				'message' => __( 'Variations saved.', 'storesuite' ),
			)
		);
	}

	/**
	 * Remove single variation.
	 *
	 * @return void
	 */
	public function storesuite_ajax_remove_variation() {
		if ( ! check_ajax_referer( 'storesuite-variation-nonce', 'security', false ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid security token.', 'storesuite' ) ) );
		}

		if ( ! current_user_can( 'edit_products' ) ) {
			wp_send_json_error( array( 'message' => __( 'You are not allowed to remove variations.', 'storesuite' ) ) );
		}

		$variation_id = isset( $_POST['variation_id'] ) ? absint( wp_unslash( $_POST['variation_id'] ) ) : 0;
		$variation    = wc_get_product( $variation_id );
		if ( ! $variation || ! $variation->is_type( 'variation' ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid variation.', 'storesuite' ) ) );
		}

		$parent_id = $variation->get_parent_id();
		if ( ! current_user_can( 'edit_post', $variation_id ) || ! current_user_can( 'edit_post', $parent_id ) ) {
			wp_send_json_error( array( 'message' => __( 'You are not allowed to remove this variation.', 'storesuite' ) ) );
		}
		$variation->delete( true );
		WC_Product_Variable::sync( $parent_id );

		wp_send_json_success(
			array(
				'message' => __( 'Variation removed.', 'storesuite' ),
			)
		);
	}

	/**
	 * Generate variation combinations.
	 *
	 * @return void
	 */
	public function storesuite_ajax_generate_variations() {
		if ( ! check_ajax_referer( 'storesuite-variation-nonce', 'security', false ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid security token.', 'storesuite' ) ) );
		}

		if ( ! current_user_can( 'edit_products' ) ) {
			wp_send_json_error( array( 'message' => __( 'You are not allowed to generate variations.', 'storesuite' ) ) );
		}

		$product = $this->get_variable_product_from_request();
		if ( ! $product ) {
			wp_send_json_error( array( 'message' => __( 'Invalid variable product.', 'storesuite' ) ) );
		}

		$variation_attributes = array();
		foreach ( $product->get_attributes( 'edit' ) as $attribute ) {
			if ( ! $attribute->get_variation() ) {
				continue;
			}

			$attribute_name = sanitize_title( $attribute->get_name() );
			$options        = $attribute->is_taxonomy() ? wp_list_pluck( $attribute->get_terms(), 'slug' ) : $attribute->get_options();
			$options        = array_values( array_filter( $options ) );
			if ( ! empty( $options ) ) {
				$variation_attributes[ $attribute_name ] = $options;
			}
		}

		if ( empty( $variation_attributes ) ) {
			wp_send_json_error( array( 'message' => __( 'No variation attributes found.', 'storesuite' ) ) );
		}

		$combinations = array( array() );
		foreach ( $variation_attributes as $attribute_name => $options ) {
			$new_combinations = array();
			foreach ( $combinations as $combination ) {
				foreach ( $options as $option ) {
					$new_combinations[] = array_merge( $combination, array( $attribute_name => $option ) );
				}
			}
			$combinations = $new_combinations;
		}

		$max_per_run  = max( 1, (int) apply_filters( 'storesuite_max_variations_per_run', 50 ) );
		$created      = 0;
		$data_store   = WC_Data_Store::load( 'product' );

		foreach ( $combinations as $combination ) {
			if ( $created >= $max_per_run ) {
				break;
			}

			$match_attributes = array();
			foreach ( $combination as $key => $value ) {
				$match_attributes[ 'attribute_' . $key ] = $value;
			}

			$existing = $data_store->find_matching_product_variation( $product, $match_attributes );
			if ( $existing ) {
				continue;
			}

			$variation = new WC_Product_Variation();
			$variation->set_parent_id( $product->get_id() );
			$variation->set_attributes( $combination );
			$variation->set_status( 'publish' );
			$variation->save();
			++$created;
		}

		WC_Product_Variable::sync( $product->get_id() );

		wp_send_json_success(
			array(
				/* translators: %d: created variations count */
				'message' => sprintf( esc_html__( '%d variations created.', 'storesuite' ), absint( $created ) ),
				'created' => $created,
			)
		);
	}

	/**
	 * Bulk variation actions.
	 *
	 * @return void
	 */
	public function storesuite_ajax_bulk_edit_variations() {
		if ( ! check_ajax_referer( 'storesuite-variation-nonce', 'security', false ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid security token.', 'storesuite' ) ) );
		}

		if ( ! current_user_can( 'edit_products' ) ) {
			wp_send_json_error( array( 'message' => __( 'You are not allowed to update variations.', 'storesuite' ) ) );
		}

		$product = $this->get_variable_product_from_request();
		if ( ! $product ) {
			wp_send_json_error( array( 'message' => __( 'Invalid variable product.', 'storesuite' ) ) );
		}

		$bulk_action = isset( $_POST['bulk_action'] ) ? wc_clean( wp_unslash( $_POST['bulk_action'] ) ) : '';
		if ( 'delete_all' !== $bulk_action ) {
			wp_send_json_error( array( 'message' => __( 'Unsupported bulk action.', 'storesuite' ) ) );
		}

		foreach ( $product->get_children() as $variation_id ) {
			$variation = wc_get_product( $variation_id );
			if ( $variation ) {
				$variation->delete( true );
			}
		}
		WC_Product_Variable::sync( $product->get_id() );

		wp_send_json_success(
			array(
				'message' => __( 'All variations deleted.', 'storesuite' ),
			)
		);
	}

	/**
	 * Get variable product from request.
	 *
	 * @return WC_Product_Variable|false
	 */
	private function get_variable_product_from_request() {
		$product_id = isset( $_POST['product_id'] ) ? absint( wp_unslash( $_POST['product_id'] ) ) : 0;
		$product    = wc_get_product( $product_id );
		if ( ! $product || ! $product->is_type( 'variable' ) || ! current_user_can( 'edit_post', $product_id ) ) {
			return false;
		}

		return $product;
	}

	/**
	 * Build stable hash for a variation attribute combination.
	 *
	 * @param array $variation_attrs Variation attributes.
	 *
	 * @return string
	 */
	private function get_variation_combination_hash( $variation_attrs ) {
		$normalized = array();
		foreach ( $variation_attrs as $key => $value ) {
			$normalized[ sanitize_title( $key ) ] = '' === $value ? '' : sanitize_title( $value );
		}
		ksort( $normalized );

		return wp_json_encode( $normalized );
	}

	/**
	 * Check if variation combination is unique across stored variations.
	 *
	 * @param WC_Product_Variable $product      Parent variable product.
	 * @param array               $attributes   Variation attributes.
	 * @param int                 $variation_id Current variation id being edited.
	 *
	 * @return bool
	 */
	private function is_variation_combination_unique( $product, $attributes, $variation_id = 0 ) {
		if ( empty( $attributes ) ) {
			return true;
		}

		$data_store       = WC_Data_Store::load( 'product' );
		$match_attributes = array();
		foreach ( $attributes as $key => $value ) {
			$match_attributes[ 'attribute_' . sanitize_title( $key ) ] = $value;
		}

		$existing_id = $data_store->find_matching_product_variation( $product, $match_attributes );

		return empty( $existing_id ) || (int) $existing_id === (int) $variation_id;
	}
}
