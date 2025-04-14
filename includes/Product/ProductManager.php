<?php

namespace PluginizeLab\ShopFront\Product;

use WP_Error;

/**
 * Plugin product manager class
 */
class ProductManager {
	/**
	 * Innsert new product
	 *
	 * @param array $args
	 *
	 * @return int|bool|WP_Error
	 */
	public function msf_save_product( $args ) {
		$defaults = array(
			'post_title'   => '',
			'post_content' => '',
			'post_excerpt' => '',
			'post_status'  => '',
			'post_type'    => 'product',
			'product_tag'  => array(),
			'_visibility'  => 'visible',
		);

		$data = wp_parse_args( $args, $defaults );

		// dd( $data );

		if ( empty( $data['product_title'] ) ) {
			return new WP_Error( 'no-title', __( 'Please enter product title', 'dokan-lite' ) );
		}

		if ( empty( $data['product_category'] ) ) {
			return new WP_Error( 'no-category', __( 'Please select a category', 'dokan-lite' ) );
		}

		$post_status = ! empty( $data['post_status'] ) ? sanitize_text_field( $data['post_status'] ) : 'publish';

		if ( ! empty( $data['ID'] ) ) {
			$post_arr['ID'] = absint( $data['ID'] );

			// if ( ! dokan_is_product_author( $post_arr['ID'] ) ) {
			// return new WP_Error( 'not-own', __( 'Sorry, You can not modify another vendor\'s product !', 'dokan-lite' ) );
			// }

			$is_updating = true;
		} else {
			$is_updating = false;
		}

		$post_data = array(
			'id'                => $is_updating ? $post_arr['ID'] : '',
			'name'              => sanitize_text_field( $data['product_title'] ),
			'type'              => ! empty( $data['product_type'] ) ? $data['product_type'] : 'simple',
			'description'       => wp_kses_post( $data['product_description'] ),
			'short_description' => wp_kses_post( $data['product_short_description'] ),
			'status'            => $post_status,
		);

		// if ( ! isset( $data['chosen_product_cat'] ) ) {
		// if ( Helper::product_category_selection_is_single() ) {
		// $cat_ids[] = $data['product_cat'];
		// } elseif ( ! empty( $data['product_cat'] ) ) {
		// $cat_ids = array_map( 'absint', (array) $data['product_cat'] );
		// }
		// $post_data['categories'] = $cat_ids;
		// }

		if ( ! empty( $data['product_category'] ) ) {
			$post_data['categories'] = array_map( 'absint', (array) $data['product_category'] );
		}

		if ( isset( $data['product_thumbnail_id'] ) ) {
			$post_data['featured_image_id'] = ! empty( $data['product_thumbnail_id'] ) ? absint( $data['product_thumbnail_id'] ) : '';
		}

		if ( isset( $data['product_image_gallery'] ) ) {
			$post_data['gallery_image_ids'] = ! empty( $data['product_image_gallery'] ) ? wc_clean( $data['product_image_gallery'] ) : '';
		}

		if ( ! empty( $data['product_tags'] ) ) {
			$post_data['tags'] = array_map( 'absint', (array) $data['product_tags'] );
		}

		if ( isset( $data['regular_price'] ) ) {
			$post_data['regular_price'] = $data['regular_price'] === '' ? '' : wc_format_decimal( $data['regular_price'] );
		}

		if ( isset( $data['sale_price'] ) ) {
			$post_data['sale_price'] = wc_format_decimal( $data['sale_price'] );
		}

		// Need to implement later
		if ( isset( $data['_sale_price_dates_from'] ) ) {
			$post_data['date_on_sale_from'] = wc_clean( $data['_sale_price_dates_from'] );
		}

		// Need to implement later
		if ( isset( $data['_sale_price_dates_to'] ) ) {
			$post_data['date_on_sale_to'] = wc_clean( $data['_sale_price_dates_to'] );
		}

		// Need to implement later (Maybe)
		// if ( isset( $data['_visibility'] ) && array_key_exists( $data['_visibility'], dokan_get_product_visibility_options() ) ) {
		// $post_data['catalog_visibility'] = sanitize_text_field( $data['_visibility'] );
		// }

		if ( isset( $data['weight'] ) ) {
			$post_data['weight'] = wc_clean( $data['weight'] );
		}

		if ( isset( $data['length'] ) ) {
			$post_data['length'] = wc_clean( $data['length'] );
		}

		if ( isset( $data['width'] ) ) {
			$post_data['width'] = wc_clean( $data['width'] );
		}

		if ( isset( $data['height'] ) ) {
			$post_data['height'] = wc_clean( $data['height'] );
		}

		$product = $this->create_product( $post_data );

		if ( ! $is_updating ) {
			do_action( 'dokan_new_product_added', $product->get_id(), $data );
		} else {
			do_action( 'dokan_product_updated', $product->get_id(), $data );
		}

		if ( $product ) {
			return $product->get_id();
		}

		return false;
	}

	/**
	 * Create Product
	 *
	 * @throws \WC_Data_Exception
	 * @return WC_Product|null|false
	 */
	public function create_product( $args = array() ) {
		$id = isset( $args['id'] ) ? absint( $args['id'] ) : 0;

		// Using the correct class and methods.
		if ( isset( $args['type'] ) ) {
			$classname = \WC_Product_Factory::get_classname_from_product_type( $args['type'] );

			if ( ! class_exists( $classname ) ) {
				$classname = 'WC_Product_Simple';
			}

			$product = new $classname( $id );
		} elseif ( isset( $args['id'] ) ) {
			$product = wc_get_product( $id );
		} else {
			$product = new \WC_Product_Simple();
		}

		// Post title.
		if ( isset( $args['name'] ) ) {
			$product->set_name( wp_filter_post_kses( $args['name'] ) );
		}

		// Post content.
		if ( isset( $args['description'] ) ) {
			$product->set_description( wp_filter_post_kses( $args['description'] ) );
		}

		// Post excerpt.
		if ( isset( $args['short_description'] ) ) {
			$product->set_short_description( wp_filter_post_kses( $args['short_description'] ) );
		}

		// Post status.
		if ( isset( $args['status'] ) ) {
			$product->set_status( get_post_status_object( $args['status'] ) ? $args['status'] : 'draft' );
		}

		// Post slug.
		if ( isset( $args['slug'] ) ) {
			$product->set_slug( $args['slug'] );
		}

		// Menu order.
		if ( isset( $args['menu_order'] ) ) {
			$product->set_menu_order( $args['menu_order'] );
		}

		// Comment status.
		if ( isset( $args['reviews_allowed'] ) ) {
			$product->set_reviews_allowed( $args['reviews_allowed'] );
		}

		// Virtual.
		if ( isset( $args['virtual'] ) ) {
			$product->set_virtual( $args['virtual'] );
		}

		// Tax status.
		if ( isset( $args['tax_status'] ) ) {
			$product->set_tax_status( $args['tax_status'] );
		}

		// Tax Class.
		if ( isset( $args['tax_class'] ) ) {
			$product->set_tax_class( $args['tax_class'] );
		}

		// Catalog Visibility.
		if ( isset( $args['catalog_visibility'] ) ) {
			$product->set_catalog_visibility( $args['catalog_visibility'] );
		}

		// Purchase Note.
		if ( isset( $args['purchase_note'] ) ) {
			$product->set_purchase_note( wp_kses_post( wp_unslash( $args['purchase_note'] ) ) );
		}

		// Featured Product.
		if ( isset( $args['featured'] ) ) {
			$product->set_featured( $args['featured'] );
		}

		// Shipping data.
		$product = $this->save_product_shipping_data( $product, $args );

		// SKU.
		if ( isset( $args['sku'] ) ) {
			$product->set_sku( wc_clean( $args['sku'] ) );
		}

		// Attributes.
		if ( isset( $args['attributes'] ) ) {
			$product->set_attributes( $args['attributes'] );
		}

		// Sales and prices.
		if ( in_array( $product->get_type(), array( 'variable', 'grouped' ), true ) ) {
			$product->set_regular_price( '' );
			$product->set_sale_price( '' );
			$product->set_date_on_sale_to( '' );
			$product->set_date_on_sale_from( '' );
			$product->set_price( '' );
		} else {
			// Regular Price.
			if ( isset( $args['regular_price'] ) ) {
				$product->set_regular_price( $args['regular_price'] );
			}

			// Sale Price.
			if ( isset( $args['sale_price'] ) ) {
				$product->set_sale_price( $args['sale_price'] );
			}

			if ( isset( $args['date_on_sale_from'] ) ) {
				$product->set_date_on_sale_from( $args['date_on_sale_from'] );
			}

			if ( isset( $args['date_on_sale_from_gmt'] ) ) {
				$product->set_date_on_sale_from( $args['date_on_sale_from_gmt'] ? strtotime( $args['date_on_sale_from_gmt'] ) : null );
			}

			if ( isset( $args['date_on_sale_to'] ) ) {
				$product->set_date_on_sale_to( $args['date_on_sale_to'] );
			}

			if ( isset( $args['date_on_sale_to_gmt'] ) ) {
				$product->set_date_on_sale_to( $args['date_on_sale_to_gmt'] ? strtotime( $args['date_on_sale_to_gmt'] ) : null );
			}
		}

		// Product parent ID.
		if ( isset( $args['parent_id'] ) ) {
			$product->set_parent_id( $args['parent_id'] );
		}

		// Sold individually.
		if ( isset( $args['sold_individually'] ) ) {
			$product->set_sold_individually( $args['sold_individually'] );
		}

		// Stock status; stock_status has priority over in_stock.
		if ( isset( $args['stock_status'] ) ) {
			$stock_status = $args['stock_status'];
		} else {
			$stock_status = $product->get_stock_status();
		}

		// Stock data.
		if ( 'yes' === get_option( 'woocommerce_manage_stock' ) ) {
			// Manage stock.
			if ( isset( $args['manage_stock'] ) ) {
				$product->set_manage_stock( $args['manage_stock'] );
			}

			// Backorders.
			if ( isset( $args['backorders'] ) ) {
				$product->set_backorders( $args['backorders'] );
			}

			if ( $product->is_type( 'grouped' ) ) {
				$product->set_manage_stock( 'no' );
				$product->set_backorders( 'no' );
				$product->set_stock_quantity( '' );
				$product->set_stock_status( $stock_status );
			} elseif ( $product->is_type( 'external' ) ) {
				$product->set_manage_stock( 'no' );
				$product->set_backorders( 'no' );
				$product->set_stock_quantity( '' );
				$product->set_stock_status( 'instock' );
			} elseif ( $product->get_manage_stock() ) {
				// Stock status is always determined by children so sync later.
				if ( ! $product->is_type( 'variable' ) ) {
					$product->set_stock_status( $stock_status );
				}

				// Stock quantity.
				if ( isset( $args['stock_quantity'] ) ) {
					$product->set_stock_quantity( wc_stock_amount( $args['stock_quantity'] ) );
				} elseif ( isset( $args['inventory_delta'] ) ) {
					$stock_quantity  = wc_stock_amount( $product->get_stock_quantity() );
					$stock_quantity += wc_stock_amount( $args['inventory_delta'] );
					$product->set_stock_quantity( wc_stock_amount( $stock_quantity ) );
				}
			} else {
				// Don't manage stock.
				$product->set_manage_stock( 'no' );
				$product->set_stock_quantity( '' );
				$product->set_stock_status( $stock_status );
			}
		} elseif ( ! $product->is_type( 'variable' ) ) {
			$product->set_stock_status( $stock_status );
		}

		// sync stock status
		$product = $this->maybe_update_stock_status( $product, $stock_status );

		// Upsells.
		if ( isset( $args['upsell_ids'] ) ) {
			$upsells = array();
			$ids     = $args['upsell_ids'];

			if ( ! empty( $ids ) ) {
				foreach ( $ids as $id ) {
					if ( $id && $id > 0 ) {
						$upsells[] = $id;
					}
				}
			}

			$product->set_upsell_ids( $upsells );
		}

		// Cross sells.
		if ( isset( $args['cross_sell_ids'] ) ) {
			$crosssells = array();
			$ids        = $args['cross_sell_ids'];

			if ( ! empty( $ids ) ) {
				foreach ( $ids as $id ) {
					if ( $id && $id > 0 ) {
						$crosssells[] = $id;
					}
				}
			}

			$product->set_cross_sell_ids( $crosssells );
		}

		// Product categories.
		if ( isset( $args['categories'] ) && is_array( $args['categories'] ) ) {

			$product->set_category_ids( $args['categories'] );
		}

		// Product tags.
		if ( isset( $args['tags'] ) && is_array( $args['tags'] ) ) {
			$product->set_tag_ids( $args['tags'] );
		}

		// Downloadable.
		if ( isset( $args['downloadable'] ) ) {
			$product->set_downloadable( $args['downloadable'] );
		}

		// Downloadable options.
		if ( $product->get_downloadable() ) {

			// Downloadable files.
			if ( isset( $args['downloads'] ) && is_array( $args['downloads'] ) ) {
				$product = $this->save_downloadable_files( $product, $args['downloads'] );
			}

			// Download limit.
			if ( isset( $args['download_limit'] ) ) {
				$product->set_download_limit( $args['download_limit'] );
			}

			// Download expiry.
			if ( isset( $args['download_expiry'] ) ) {
				$product->set_download_expiry( $args['download_expiry'] );
			}
		}

		// Product url and button text for external products.
		if ( $product->is_type( 'external' ) ) {
			if ( isset( $args['external_url'] ) ) {
				$product->set_product_url( $args['external_url'] );
			}

			if ( isset( $args['button_text'] ) ) {
				$product->set_button_text( $args['button_text'] );
			}
		}

		// Save default attributes for variable products.
		if ( $product->is_type( 'variable' ) ) {
			$product = $this->save_default_attributes( $product, $args );
		}

		// Set children for a grouped product.
		if ( $product->is_type( 'grouped' ) && isset( $args['grouped_products'] ) ) {
			$product->set_children( $args['grouped_products'] );
		}

		// Set featured image id
		if ( ! empty( $args['featured_image_id'] ) ) {
			$product->set_image_id( $args['featured_image_id'] );
		}

		// Set gallery image ids
		if ( ! empty( $args['gallery_image_ids'] ) ) {
			$product->set_gallery_image_ids( $args['gallery_image_ids'] );
		}

		// Allow set meta_data.
		if ( ! empty( $args['meta_data'] ) && is_array( $args['meta_data'] ) ) {
			foreach ( $args['meta_data'] as $meta ) {
				$product->update_meta_data( $meta['key'], $meta['value'], isset( $meta['id'] ) ? $meta['id'] : '' );
			}
		}

		if ( ! empty( $args['date_created'] ) ) {
			$date = rest_parse_date( $args['date_created'] );

			if ( $date ) {
				$product->set_date_created( $date );
			}
		}

		if ( ! empty( $args['date_created_gmt'] ) ) {
			$date = rest_parse_date( $args['date_created_gmt'], true );

			if ( $date ) {
				$product->set_date_created( $date );
			}
		}

		// Set total sales for newly created product
		if ( ! empty( $id ) ) {
			$product->set_total_sales( 0 );
		}

		$product_id = $product->save();

		return wc_get_product( $product_id );
	}

	/**
	 * Save product shipping data.
	 *
	 * @param WC_Product $product Product instance.
	 * @param array      $data    Shipping data.
	 *
	 * @return WC_Product
	 */
	protected function save_product_shipping_data( $product, $data ) {

		if ( isset( $data['virtual'] ) && true === $data['virtual'] ) {
			$product->set_weight( '' );
			$product->set_height( '' );
			$product->set_length( '' );
			$product->set_width( '' );
		} else {
			if ( isset( $data['weight'] ) ) {
				$product->set_weight( $data['weight'] );
			}

			if ( isset( $data['height'] ) ) {
				$product->set_height( $data['height'] );
			}

			if ( isset( $data['width'] ) ) {
				$product->set_width( $data['width'] );
			}

			if ( isset( $data['length'] ) ) {
				$product->set_length( $data['length'] );
			}
		}

		// Set shipping class.
		if ( isset( $data['shipping_class'] ) ) {
			$data_store        = $product->get_data_store();
			$shipping_class_id = $data_store->get_shipping_class_id_by_slug( wc_clean( $data['shipping_class'] ) );
			$product->set_shipping_class_id( $shipping_class_id );
		}

		return $product;
	}

	/**
	 * Sync stock stats for variable products.
	 *
	 * @param WC_Product $product
	 * @param string     $stock_status
	 *
	 * @return mixed
	 */
	protected function maybe_update_stock_status( $product, $stock_status ) {
		if ( $product->is_type( 'external' ) ) {
			// External products are always in stock.
			$product->set_stock_status( 'instock' );
		} elseif ( isset( $stock_status ) ) {
			if ( $product->is_type( 'variable' ) && ! $product->get_manage_stock() ) {
				// Stock status is determined by children.
				foreach ( $product->get_children() as $child_id ) {
					$child = wc_get_product( $child_id );
					if ( ! $product->get_manage_stock() ) {
						$child->set_stock_status( $stock_status );
						$child->save();
					}
				}
				$product = \WC_Product_Variable::sync( $product, false );
			} else {
				$product->set_stock_status( $stock_status );
			}
		}

		return $product;
	}

	/**
	 * Save downloadable files.
	 *
	 * @param WC_Product $product    Product instance.
	 * @param array      $downloads  Downloads data.
	 *
	 * @return WC_Product
	 */
	protected function save_downloadable_files( $product, $downloads ) {
		$files = array();
		foreach ( $downloads as $key => $file ) {
			if ( empty( $file['file'] ) ) {
				continue;
			}

			$download = new \WC_Product_Download();
			$download->set_id( $key );
			$download->set_name( $file['name'] ? $file['name'] : wc_get_filename_from_url( $file['file'] ) );
			$download->set_file( apply_filters( 'woocommerce_file_download_path', $file['file'], $product, $key ) );
			$files[] = $download;
		}
		$product->set_downloads( $files );

		return $product;
	}

	/**
	 * Save default attributes.
	 *
	 * @param WC_Product       $product Product instance.
	 * @param \WP_REST_Request $request Request data.
	 *
	 * @return WC_Product
	 */
	public function save_default_attributes( $product, $request ) {
		if ( isset( $request['default_attributes'] ) && is_array( $request['default_attributes'] ) ) {
			$attributes         = $product->get_attributes();
			$default_attributes = array();

			foreach ( $request['default_attributes'] as $attribute ) {
				$attribute_id   = 0;
				$attribute_name = '';

				// Check ID for global attributes or name for product attributes.
				if ( ! empty( $attribute['id'] ) ) {
					$attribute_id   = absint( $attribute['id'] );
					$attribute_name = wc_attribute_taxonomy_name_by_id( $attribute_id );
				} elseif ( ! empty( $attribute['name'] ) ) {
					$attribute_name = sanitize_title( $attribute['name'] );
				}

				if ( ! $attribute_id && ! $attribute_name ) {
					continue;
				}

				if ( isset( $attributes[ $attribute_name ] ) ) {
					$_attribute = $attributes[ $attribute_name ];

					if ( $_attribute['is_variation'] ) {
						$value = isset( $attribute['option'] ) ? wc_clean( stripslashes( $attribute['option'] ) ) : '';

						if ( ! empty( $_attribute['is_taxonomy'] ) ) {
							// If dealing with a taxonomy, we need to get the slug from the name posted to the API.
							$term = get_term_by( 'name', $value, $attribute_name );

							if ( $term && ! is_wp_error( $term ) ) {
								$value = $term->slug;
							} else {
								$value = sanitize_title( $value );
							}
						}

						if ( $value ) {
							$default_attributes[ $attribute_name ] = $value;
						}
					}
				}
			}

			$product->set_default_attributes( $default_attributes );
		}

		return $product;
	}
}
