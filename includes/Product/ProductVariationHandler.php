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
	 * Save product attributes from request data.
	 *
	 * @param int   $product_id Product ID.
	 * @param array $data       Sanitized POST-style data (attribute_names, attribute_values, etc.).
	 * @return void
	 */
	public function save_product_attributes( $product_id, $data ) {
		$attributes = array();

		if ( empty( $data['attribute_names'] ) || ! is_array( $data['attribute_names'] ) ) {
			update_post_meta( $product_id, '_product_attributes', $attributes );
			return;
		}

		$attribute_names  = array_map( 'stripslashes', $data['attribute_names'] );
		$attribute_values = isset( $data['attribute_values'] ) ? $data['attribute_values'] : array();
		$attribute_visibility = isset( $data['attribute_visibility'] ) ? $data['attribute_visibility'] : array();
		$attribute_variation  = isset( $data['attribute_variation'] ) ? $data['attribute_variation'] : array();
		$attribute_is_taxonomy = isset( $data['attribute_is_taxonomy'] ) ? $data['attribute_is_taxonomy'] : array();
		$attribute_position   = isset( $data['attribute_position'] ) ? $data['attribute_position'] : array();
		$attribute_names_max_key = max( array_keys( $attribute_names ) );

		for ( $i = 0; $i <= $attribute_names_max_key; $i++ ) {
			if ( empty( $attribute_names[ $i ] ) ) {
				continue;
			}

			$is_visible   = isset( $attribute_visibility[ $i ] ) ? 1 : 0;
			$is_variation = isset( $attribute_variation[ $i ] ) ? 1 : 0;
			$is_taxonomy  = ! empty( $attribute_is_taxonomy[ $i ] ) ? 1 : 0;
			$position     = isset( $attribute_position[ $i ] ) ? absint( $attribute_position[ $i ] ) : $i;

			if ( $is_taxonomy ) {
				$values = array();
				if ( isset( $attribute_values[ $i ] ) ) {
					if ( is_array( $attribute_values[ $i ] ) ) {
						$values = array_map( 'strval', array_filter( array_map( 'strval', $attribute_values[ $i ] ) ) );
					} else {
						$raw_values = explode( WC_DELIMITER, $attribute_values[ $i ] );
						foreach ( $raw_values as $value ) {
							$value = trim( $value );
							if ( empty( $value ) ) {
								continue;
							}
							$term = get_term_by( 'name', $value, $attribute_names[ $i ] );
							if ( ! $term ) {
								$term = wp_insert_term( $value, $attribute_names[ $i ] );
								if ( ! is_wp_error( $term ) ) {
									$term = get_term_by( 'id', $term['term_id'], $attribute_names[ $i ] );
								}
							}
							if ( $term && ! is_wp_error( $term ) ) {
								$values[] = $term->slug;
							}
						}
					}
				}
				$values = array_filter( $values, 'strlen' );

				if ( taxonomy_exists( $attribute_names[ $i ] ) ) {
					wp_set_object_terms( $product_id, $values, $attribute_names[ $i ] );
				}
				if ( ! empty( $values ) ) {
					$attributes[ sanitize_title( $attribute_names[ $i ] ) ] = array(
						'name'         => wc_clean( $attribute_names[ $i ] ),
						'value'        => '',
						'position'     => $position,
						'is_visible'   => $is_visible,
						'is_variation' => $is_variation,
						'is_taxonomy'  => 1,
					);
				}
			} elseif ( isset( $attribute_values[ $i ] ) ) {
				$values = is_array( $attribute_values[ $i ] )
					? implode( ' ' . WC_DELIMITER . ' ', array_map( 'wc_clean', array_map( 'stripslashes', $attribute_values[ $i ] ) ) )
					: wc_clean( stripslashes( $attribute_values[ $i ] ) );
				$attributes[ sanitize_title( $attribute_names[ $i ] ) ] = array(
					'name'         => wc_clean( $attribute_names[ $i ] ),
					'value'        => $values,
					'position'     => $position,
					'is_visible'   => $is_visible,
					'is_variation' => $is_variation,
					'is_taxonomy'  => 0,
				);
			}
		}

		uasort( $attributes, 'wc_product_attribute_uasort_comparison' );
		update_post_meta( $product_id, '_product_attributes', $attributes );
		update_post_meta( $product_id, '_create_variation', 'yes' );
	}

	/**
	 * Save variations from request data (variable_* arrays).
	 *
	 * @param int $product_id Parent variable product ID.
	 * @return void
	 */
	public function save_product_variations( $product_id ) {
		$attributes = (array) maybe_unserialize( get_post_meta( $product_id, '_product_attributes', true ) );
		$post_data  = wp_unslash( $_POST ); // phpcs:ignore WordPress.Security.NonceVerification.Missing

		if ( empty( $post_data['variable_post_id'] ) || ! is_array( $post_data['variable_post_id'] ) ) {
			\WC_Product_Variable::sync( $product_id );
			return;
		}

		$variable_post_id                = $post_data['variable_post_id'];
		$variable_sku                    = isset( $post_data['variable_sku'] ) ? $post_data['variable_sku'] : array();
		$variable_regular_price          = isset( $post_data['variable_regular_price'] ) ? $post_data['variable_regular_price'] : array();
		$variable_sale_price             = isset( $post_data['variable_sale_price'] ) ? $post_data['variable_sale_price'] : array();
		$upload_image_id                 = isset( $post_data['upload_image_id'] ) ? $post_data['upload_image_id'] : array();
		$variable_menu_order             = isset( $post_data['variation_menu_order'] ) ? $post_data['variation_menu_order'] : array();
		$variable_sale_price_dates_from   = isset( $post_data['variable_sale_price_dates_from'] ) ? $post_data['variable_sale_price_dates_from'] : array();
		$variable_sale_price_dates_to    = isset( $post_data['variable_sale_price_dates_to'] ) ? $post_data['variable_sale_price_dates_to'] : array();
		$variable_enabled                 = isset( $post_data['variable_enabled'] ) ? $post_data['variable_enabled'] : array();
		$variable_manage_stock           = isset( $post_data['variable_manage_stock'] ) ? $post_data['variable_manage_stock'] : array();
		$variable_stock                   = isset( $post_data['variable_stock'] ) ? $post_data['variable_stock'] : array();
		$variable_stock_status            = isset( $post_data['variable_stock_status'] ) ? $post_data['variable_stock_status'] : array();
		$variable_backorders             = isset( $post_data['variable_backorders'] ) ? $post_data['variable_backorders'] : array();
		$variable_low_stock_amount        = isset( $post_data['variable_low_stock_amount'] ) ? $post_data['variable_low_stock_amount'] : array();
		$variable_weight                  = isset( $post_data['variable_weight'] ) ? $post_data['variable_weight'] : array();
		$variable_length                  = isset( $post_data['variable_length'] ) ? $post_data['variable_length'] : array();
		$variable_width                   = isset( $post_data['variable_width'] ) ? $post_data['variable_width'] : array();
		$variable_height                  = isset( $post_data['variable_height'] ) ? $post_data['variable_height'] : array();
		$variable_shipping_class          = isset( $post_data['variable_shipping_class'] ) ? $post_data['variable_shipping_class'] : array();
		$variable_tax_class               = isset( $post_data['variable_tax_class'] ) ? $post_data['variable_tax_class'] : array();
		$variable_description             = isset( $post_data['variable_description'] ) ? $post_data['variable_description'] : array();

		$max_loop = max( array_keys( $variable_post_id ) );

		for ( $i = 0; $i <= $max_loop; $i++ ) {
			if ( ! isset( $variable_post_id[ $i ] ) ) {
				continue;
			}

			$variation_id = absint( $variable_post_id[ $i ] );
			$post_status = isset( $variable_enabled[ $i ] ) ? 'publish' : 'private';
			$manage_stock = isset( $variable_manage_stock[ $i ] ) ? 'yes' : 'no';
			$menu_order = isset( $variable_menu_order[ $i ] ) ? absint( $variable_menu_order[ $i ] ) : $i;

			if ( ! $variation_id ) {
				$variation_id = wp_insert_post(
					array(
						'post_content' => '',
						'post_status'  => $post_status,
						'post_author'  => get_current_user_id(),
						'post_parent'  => $product_id,
						'post_type'    => 'product_variation',
						'menu_order'   => $menu_order,
					)
				);
				if ( $variation_id ) {
					do_action( 'woocommerce_create_product_variation', $variation_id, wc_get_product( $variation_id ) );
				}
			} else {
				wp_update_post(
					array(
						'ID'          => $variation_id,
						'post_status' => $post_status,
						'menu_order'  => $menu_order,
					)
				);
				do_action( 'woocommerce_update_product_variation', $variation_id, wc_get_product( $variation_id ) );
			}

			if ( ! $variation_id ) {
				continue;
			}

			// SKU
			$new_sku = isset( $variable_sku[ $i ] ) ? wc_clean( $variable_sku[ $i ] ) : '';
			if ( '' !== $new_sku && wc_product_has_unique_sku( $variation_id, $new_sku ) ) {
				update_post_meta( $variation_id, '_sku', $new_sku );
			} elseif ( '' === $new_sku ) {
				update_post_meta( $variation_id, '_sku', '' );
			}

			update_post_meta( $variation_id, '_thumbnail_id', isset( $upload_image_id[ $i ] ) ? absint( $upload_image_id[ $i ] ) : 0 );

			// Dimensions
			if ( isset( $variable_weight[ $i ] ) ) {
				update_post_meta( $variation_id, '_weight', '' === $variable_weight[ $i ] ? '' : wc_format_decimal( $variable_weight[ $i ] ) );
			}
			if ( isset( $variable_length[ $i ] ) ) {
				update_post_meta( $variation_id, '_length', '' === $variable_length[ $i ] ? '' : wc_format_decimal( $variable_length[ $i ] ) );
			}
			if ( isset( $variable_width[ $i ] ) ) {
				update_post_meta( $variation_id, '_width', '' === $variable_width[ $i ] ? '' : wc_format_decimal( $variable_width[ $i ] ) );
			}
			if ( isset( $variable_height[ $i ] ) ) {
				update_post_meta( $variation_id, '_height', '' === $variable_height[ $i ] ? '' : wc_format_decimal( $variable_height[ $i ] ) );
			}

			// Stock
			update_post_meta( $variation_id, '_manage_stock', $manage_stock );
			if ( 'yes' === $manage_stock ) {
				$stock = isset( $variable_stock[ $i ] ) ? wc_stock_amount( $variable_stock[ $i ] ) : '';
				wc_update_product_stock( $variation_id, $stock );
				update_post_meta( $variation_id, '_backorders', isset( $variable_backorders[ $i ] ) ? wc_clean( $variable_backorders[ $i ] ) : 'no' );
				update_post_meta( $variation_id, '_low_stock_amount', isset( $variable_low_stock_amount[ $i ] ) ? wc_format_decimal( $variable_low_stock_amount[ $i ] ) : '' );
			} else {
				delete_post_meta( $variation_id, '_backorders' );
				wc_update_product_stock( $variation_id, '' );
			}
			if ( ! empty( $variable_stock_status[ $i ] ) ) {
				wc_update_product_stock_status( $variation_id, $variable_stock_status[ $i ] );
			}

			// Price
			$reg = isset( $variable_regular_price[ $i ] ) ? wc_format_decimal( $variable_regular_price[ $i ] ) : '';
			$sale = isset( $variable_sale_price[ $i ] ) ? wc_format_decimal( $variable_sale_price[ $i ] ) : '';
			$date_from = isset( $variable_sale_price_dates_from[ $i ] ) ? wc_clean( $variable_sale_price_dates_from[ $i ] ) : '';
			$date_to   = isset( $variable_sale_price_dates_to[ $i ] ) ? wc_clean( $variable_sale_price_dates_to[ $i ] ) : '';
			$this->save_variation_prices( $variation_id, $reg, $sale, $date_from, $date_to );

			// Tax class
			if ( isset( $variable_tax_class[ $i ] ) && 'parent' !== $variable_tax_class[ $i ] ) {
				update_post_meta( $variation_id, '_tax_class', wc_clean( $variable_tax_class[ $i ] ) );
			} else {
				delete_post_meta( $variation_id, '_tax_class' );
			}

			// Shipping class
			$shipping_class = isset( $variable_shipping_class[ $i ] ) ? absint( $variable_shipping_class[ $i ] ) : '';
			wp_set_object_terms( $variation_id, $shipping_class, 'product_shipping_class' );

			// Description
			update_post_meta( $variation_id, '_variation_description', isset( $variable_description[ $i ] ) ? wp_kses_post( $variable_description[ $i ] ) : '' );

			// Variation attributes
			$updated_keys = array();
			foreach ( $attributes as $attribute ) {
				if ( empty( $attribute['is_variation'] ) ) {
					continue;
				}
				$key = 'attribute_' . sanitize_title( $attribute['name'] );
				$updated_keys[] = $key;
				$value = isset( $post_data[ $key ][ $i ] ) ? ( $attribute['is_taxonomy'] ? sanitize_title( wp_unslash( $post_data[ $key ][ $i ] ) ) : wc_clean( wp_unslash( $post_data[ $key ][ $i ] ) ) ) : '';
				update_post_meta( $variation_id, $key, $value );
			}
			// Remove old variation attribute meta not in current set.
			if ( ! empty( $updated_keys ) ) {
				global $wpdb;
				$placeholders = implode( ',', array_fill( 0, count( $updated_keys ), '%s' ) );
				$delete_keys  = $wpdb->get_col( $wpdb->prepare( "SELECT meta_key FROM {$wpdb->postmeta} WHERE meta_key LIKE 'attribute_%%' AND meta_key NOT IN ($placeholders) AND post_id = %d", array_merge( $updated_keys, array( $variation_id ) ) ) );
				foreach ( (array) $delete_keys as $key ) {
					delete_post_meta( $variation_id, $key );
				}
			}

			do_action( 'woocommerce_save_product_variation', $variation_id, $i );
		}

		\WC_Product_Variable::sync( $product_id );

		// Default attributes
		$default_attributes = array();
		foreach ( $attributes as $attribute ) {
			if ( empty( $attribute['is_variation'] ) ) {
				continue;
			}
			$key = 'default_attribute_' . sanitize_title( $attribute['name'] );
			$value = isset( $post_data[ $key ] ) ? ( $attribute['is_taxonomy'] ? sanitize_title( wp_unslash( $post_data[ $key ] ) ) : wc_clean( wp_unslash( $post_data[ $key ] ) ) ) : '';
			if ( $value ) {
				$default_attributes[ sanitize_title( $attribute['name'] ) ] = $value;
			}
		}
		update_post_meta( $product_id, '_default_attributes', $default_attributes );
	}

	/**
	 * Save variation regular/sale price and dates.
	 *
	 * @param int    $variation_id Variation product ID.
	 * @param string $regular_price Regular price.
	 * @param string $sale_price    Sale price.
	 * @param string $date_from    Sale start date (Y-m-d).
	 * @param string $date_to      Sale end date (Y-m-d).
	 */
	protected function save_variation_prices( $variation_id, $regular_price, $sale_price, $date_from, $date_to ) {
		$product = wc_get_product( $variation_id );
		if ( ! $product || ! $product->is_type( 'variation' ) ) {
			return;
		}
		$product->set_regular_price( $regular_price );
		$product->set_sale_price( $sale_price );
		if ( $date_from ) {
			$product->set_date_on_sale_from( strtotime( $date_from ) );
		} else {
			$product->set_date_on_sale_from( null );
		}
		if ( $date_to ) {
			$product->set_date_on_sale_to( strtotime( $date_to ) + DAY_IN_SECONDS - 1 );
		} else {
			$product->set_date_on_sale_to( null );
		}
		$product->save();
	}
}
