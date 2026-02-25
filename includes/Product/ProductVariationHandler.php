<?php

namespace PluginizeLab\StoreSuite\Product;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles saving product attributes and variations (variable products).
 */
class ProductVariationHandler {

	/**
	 * Save product attributes from request data using WC CRUD API.
	 *
	 * @param int   $product_id Product ID.
	 * @param array $data       Sanitized POST-style data (attribute_names, attribute_values, etc.).
	 * @return void
	 */
	public function save_product_attributes( $product_id, $data ) {
		$product = wc_get_product( $product_id );
		if ( ! $product ) {
			return;
		}

		if ( empty( $data['attribute_names'] ) || ! is_array( $data['attribute_names'] ) ) {
			$product->set_attributes( array() );
			$product->save();
			return;
		}

		$attribute_names         = $data['attribute_names'];
		$attribute_values        = isset( $data['attribute_values'] ) ? $data['attribute_values'] : array();
		$attribute_visibility    = isset( $data['attribute_visibility'] ) ? $data['attribute_visibility'] : array();
		$attribute_variation     = isset( $data['attribute_variation'] ) ? $data['attribute_variation'] : array();
		$attribute_is_taxonomy   = isset( $data['attribute_is_taxonomy'] ) ? $data['attribute_is_taxonomy'] : array();
		$attribute_position      = isset( $data['attribute_position'] ) ? $data['attribute_position'] : array();
		$attribute_names_max_key = max( array_keys( $attribute_names ) );

		$attributes = array();

		for ( $i = 0; $i <= $attribute_names_max_key; $i++ ) {
			if ( empty( $attribute_names[ $i ] ) || ! isset( $attribute_values[ $i ] ) ) {
				continue;
			}

			$attribute_name = wc_clean( $attribute_names[ $i ] );
			$is_taxonomy    = ! empty( $attribute_is_taxonomy[ $i ] );
			$attribute_id   = 0;

			if ( $is_taxonomy && 'pa_' === substr( $attribute_name, 0, 3 ) ) {
				$attribute_id = wc_attribute_taxonomy_id_by_name( $attribute_name );
			}

			$options = $attribute_values[ $i ];

			if ( is_array( $options ) ) {
				if ( $is_taxonomy ) {
					// Our attribute UI submits term slugs for taxonomies; convert them to term IDs.
					$term_ids = array();
					foreach ( $options as $opt ) {
						if ( is_numeric( $opt ) ) {
							$term_ids[] = (int) $opt;
						} else {
							$term = get_term_by( 'slug', sanitize_title( $opt ), $attribute_name );
							if ( $term && ! is_wp_error( $term ) ) {
								$term_ids[] = (int) $term->term_id;
							}
						}
					}
					$options = $term_ids;
				} else {
					$options = array_map( 'wc_clean', array_map( 'stripslashes', $options ) );
				}
			} else {
				$options = $is_taxonomy
					? wc_sanitize_textarea( wc_sanitize_term_text_based( $options ) )
					: wc_sanitize_textarea( $options );
				$options = wc_get_text_attributes( $options );
			}

			if ( empty( $options ) ) {
				continue;
			}

			$attribute = new \WC_Product_Attribute();
			$attribute->set_id( $attribute_id );
			$attribute->set_name( $attribute_name );
			$attribute->set_options( $options );
			$attribute->set_position( isset( $attribute_position[ $i ] ) ? absint( $attribute_position[ $i ] ) : $i );
			$attribute->set_visible( isset( $attribute_visibility[ $i ] ) );
			$attribute->set_variation( isset( $attribute_variation[ $i ] ) );

			$attributes[] = $attribute;
		}

		$product->set_attributes( $attributes );
		$product->save();
	}

	/**
	 * Save variations from request data using WC CRUD API.
	 *
	 * @param int $product_id Parent variable product ID.
	 * @return void
	 */
	public function save_product_variations( $product_id ) {
		$parent = wc_get_product( $product_id );
		if ( ! $parent || ! $parent->is_type( 'variable' ) ) {
			return;
		}

		$post_data = wp_unslash( $_POST ); // phpcs:ignore WordPress.Security.NonceVerification.Missing

		if ( empty( $post_data['variable_post_id'] ) || ! is_array( $post_data['variable_post_id'] ) ) {
			\WC_Product_Variable::sync( $product_id );
			return;
		}

		$variable_post_id = $post_data['variable_post_id'];
		$max_loop         = max( array_keys( $variable_post_id ) );

		for ( $i = 0; $i <= $max_loop; $i++ ) {
			if ( ! isset( $variable_post_id[ $i ] ) ) {
				continue;
			}

			$variation_id = absint( $variable_post_id[ $i ] );
			$variation    = new \WC_Product_Variation( $variation_id ? $variation_id : 0 );

			if ( ! $variation_id ) {
				$variation->set_parent_id( $product_id );
			}

			$date_on_sale_from = isset( $post_data['variable_sale_price_dates_from'][ $i ] ) ? wc_clean( $post_data['variable_sale_price_dates_from'][ $i ] ) : '';
			$date_on_sale_to   = isset( $post_data['variable_sale_price_dates_to'][ $i ] ) ? wc_clean( $post_data['variable_sale_price_dates_to'][ $i ] ) : '';

			if ( ! empty( $date_on_sale_from ) ) {
				$date_on_sale_from = gmdate( 'Y-m-d 00:00:00', strtotime( $date_on_sale_from ) );
			}
			if ( ! empty( $date_on_sale_to ) ) {
				$date_on_sale_to = gmdate( 'Y-m-d 23:59:59', strtotime( $date_on_sale_to ) );
			}

			$variation_attributes = $this->prepare_variation_attributes( $parent, $post_data, $i );

			$variation->set_props(
				array(
					'status'            => isset( $post_data['variable_enabled'][ $i ] ) ? 'publish' : 'private',
					'menu_order'        => isset( $post_data['variation_menu_order'][ $i ] ) ? absint( $post_data['variation_menu_order'][ $i ] ) : $i,
					'regular_price'     => isset( $post_data['variable_regular_price'][ $i ] ) ? wc_clean( $post_data['variable_regular_price'][ $i ] ) : null,
					'sale_price'        => isset( $post_data['variable_sale_price'][ $i ] ) ? wc_clean( $post_data['variable_sale_price'][ $i ] ) : null,
					'date_on_sale_from' => $date_on_sale_from,
					'date_on_sale_to'   => $date_on_sale_to,
					'manage_stock'      => isset( $post_data['variable_manage_stock'][ $i ] ),
					'stock_quantity'    => isset( $post_data['variable_stock'][ $i ] ) ? wc_stock_amount( $post_data['variable_stock'][ $i ] ) : null,
					'stock_status'      => isset( $post_data['variable_stock_status'][ $i ] ) ? wc_clean( $post_data['variable_stock_status'][ $i ] ) : null,
					'backorders'        => isset( $post_data['variable_backorders'][ $i ] ) ? wc_clean( $post_data['variable_backorders'][ $i ] ) : null,
					'low_stock_amount'  => isset( $post_data['variable_low_stock_amount'][ $i ] ) && '' !== $post_data['variable_low_stock_amount'][ $i ] ? wc_stock_amount( $post_data['variable_low_stock_amount'][ $i ] ) : '',
					'sku'               => isset( $post_data['variable_sku'][ $i ] ) ? wc_clean( $post_data['variable_sku'][ $i ] ) : '',
					'image_id'          => isset( $post_data['upload_image_id'][ $i ] ) ? absint( $post_data['upload_image_id'][ $i ] ) : null,
					'weight'            => isset( $post_data['variable_weight'][ $i ] ) ? wc_clean( $post_data['variable_weight'][ $i ] ) : '',
					'length'            => isset( $post_data['variable_length'][ $i ] ) ? wc_clean( $post_data['variable_length'][ $i ] ) : '',
					'width'             => isset( $post_data['variable_width'][ $i ] ) ? wc_clean( $post_data['variable_width'][ $i ] ) : '',
					'height'            => isset( $post_data['variable_height'][ $i ] ) ? wc_clean( $post_data['variable_height'][ $i ] ) : '',
					'shipping_class_id' => isset( $post_data['variable_shipping_class'][ $i ] ) ? absint( $post_data['variable_shipping_class'][ $i ] ) : null,
					'tax_class'         => isset( $post_data['variable_tax_class'][ $i ] ) ? sanitize_title( $post_data['variable_tax_class'][ $i ] ) : null,
					'description'       => isset( $post_data['variable_description'][ $i ] ) ? wp_kses_post( $post_data['variable_description'][ $i ] ) : null,
					'attributes'        => $variation_attributes,
				)
			);

			$variation->save();

			do_action( 'woocommerce_save_product_variation', $variation->get_id(), $i );
		}

		// Sync parent variable product data (price ranges, stock, etc.).
		\WC_Product_Variable::sync( $product_id );

		// Default attributes.
		$this->save_default_attributes( $parent, $post_data );
	}

	/**
	 * Prepare variation attributes from POST data.
	 *
	 * @param \WC_Product $parent   Parent variable product.
	 * @param array       $post_data Unslashed POST data.
	 * @param int         $i         Loop index.
	 * @return array
	 */
	protected function prepare_variation_attributes( $parent, $post_data, $i ) {
		$variation_attributes = array();

		foreach ( $parent->get_attributes() as $attribute ) {
			if ( ! $attribute->get_variation() ) {
				continue;
			}

			$key = 'attribute_' . sanitize_title( $attribute->get_name() );

			if ( isset( $post_data[ $key ][ $i ] ) ) {
				$value = $attribute->get_taxonomy()
					? sanitize_title( $post_data[ $key ][ $i ] )
					: wc_clean( $post_data[ $key ][ $i ] );
			} else {
				$value = '';
			}

			$variation_attributes[ sanitize_title( $attribute->get_name() ) ] = $value;
		}

		return $variation_attributes;
	}

	/**
	 * Save default attributes for the parent variable product.
	 *
	 * @param \WC_Product $parent    Parent variable product.
	 * @param array       $post_data Unslashed POST data.
	 * @return void
	 */
	protected function save_default_attributes( $parent, $post_data ) {
		$default_attributes = array();

		foreach ( $parent->get_attributes() as $attribute ) {
			if ( ! $attribute->get_variation() ) {
				continue;
			}

			$key = 'default_attribute_' . sanitize_title( $attribute->get_name() );

			if ( isset( $post_data[ $key ] ) ) {
				$value = $attribute->get_taxonomy()
					? sanitize_title( wp_unslash( $post_data[ $key ] ) )
					: wc_clean( wp_unslash( $post_data[ $key ] ) );
			} else {
				$value = '';
			}

			if ( $value ) {
				$default_attributes[ sanitize_title( $attribute->get_name() ) ] = $value;
			}
		}

		$parent->set_default_attributes( $default_attributes );
		$parent->save();
	}
}
