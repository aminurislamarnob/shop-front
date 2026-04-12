<?php
/**
 * WooCommerce-equivalent product quick edit save for StoreSuite dashboard AJAX.
 *
 * Ported from WC_Admin_Post_Types::quick_edit_save() and maybe_update_stock_status()
 * in woocommerce/includes/admin/class-wc-admin-post-types.php — re-sync when upgrading WooCommerce.
 *
 * @package StoreSuite
 */

namespace PluginizeLab\StoreSuite\Product;

use Automattic\WooCommerce\Enums\ProductStockStatus;
use Automattic\WooCommerce\Enums\ProductType;
use Automattic\WooCommerce\Internal\CostOfGoodsSold\CostOfGoodsSoldController;
use WC_Product;
use WC_Product_Variable;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Applies quick edit POST data to a product using WooCommerce admin semantics.
 */
class ProductQuickEdit {

	/**
	 * Run quick edit save using unslashed request-shaped array (typically wp_unslash( $_POST )).
	 *
	 * @param int                   $post_id      Product post ID.
	 * @param \WC_Product|false     $product      Product object.
	 * @param array<string, mixed> $request_data Request payload (same keys as WC quick edit).
	 * @return true|\WP_Error
	 */
	public static function save( $post_id, $product, array $request_data ) {
		if ( ! $product instanceof WC_Product ) {
			return new \WP_Error(
				'storesuite_quick_edit_invalid_product',
				__( 'Product not found.', 'storesuite' )
			);
		}

		$data_store        = $product->get_data_store();
		$old_regular_price = $product->get_regular_price();
		$old_sale_price    = $product->get_sale_price();
		$input_to_props    = array(
			'_weight'       => 'weight',
			'_length'       => 'length',
			'_width'        => 'width',
			'_height'       => 'height',
			'_visibility'   => 'catalog_visibility',
			'_tax_class'    => 'tax_class',
			'_tax_status'   => 'tax_status',
		);

		foreach ( $input_to_props as $input_var => $prop ) {
			if ( isset( $request_data[ $input_var ] ) ) {
				$product->{"set_{$prop}"}( wc_clean( wp_unslash( $request_data[ $input_var ] ) ) );
			}
		}

		if ( isset( $request_data['_sku'] ) ) {
			$sku = $product->get_sku();
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash -- wc_clean handles; data already unslashed by caller.
			$new_sku = (string) wc_clean( $request_data['_sku'] );

			if ( $new_sku !== $sku ) {
				if ( ! empty( $new_sku ) ) {
					$unique_sku = wc_product_has_unique_sku( $post_id, $new_sku );
					if ( $unique_sku ) {
						$product->set_sku( wc_clean( wp_unslash( $new_sku ) ) );
					}
				} else {
					$product->set_sku( '' );
				}
			}
		}

		if ( ! empty( $request_data['_shipping_class'] ) ) {
			if ( '_no_shipping_class' === $request_data['_shipping_class'] ) {
				$product->set_shipping_class_id( 0 );
			} else {
				// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash
				$shipping_class_id = $data_store->get_shipping_class_id_by_slug( wc_clean( $request_data['_shipping_class'] ) );
				$product->set_shipping_class_id( $shipping_class_id );
			}
		}

		if ( ! empty( $request_data['_tax_class'] ) ) {
			$tax_class = sanitize_title( wp_unslash( $request_data['_tax_class'] ) );
			if ( 'standard' === $tax_class ) {
				$tax_class = '';
			}
			$product->set_tax_class( $tax_class );
		}

		$product->set_featured( isset( $request_data['_featured'] ) );

		if ( $product->is_type( ProductType::SIMPLE ) || $product->is_type( ProductType::EXTERNAL ) ) {
			if ( isset( $request_data['_regular_price'] ) ) {
				// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash
				$new_regular_price = ( '' === $request_data['_regular_price'] ) ? '' : wc_format_decimal( $request_data['_regular_price'] );
				$product->set_regular_price( $new_regular_price );
			} else {
				$new_regular_price = null;
			}

			if ( isset( $request_data['_sale_price'] ) ) {
				// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash
				$new_sale_price = ( '' === $request_data['_sale_price'] ) ? '' : wc_format_decimal( $request_data['_sale_price'] );
				$product->set_sale_price( $new_sale_price );
			} else {
				$new_sale_price = null;
			}

			$price_changed = false;

			if ( ! is_null( $new_regular_price ) && $new_regular_price !== $old_regular_price ) {
				$price_changed = true;
			} elseif ( ! is_null( $new_sale_price ) && $new_sale_price !== $old_sale_price ) {
				$price_changed = true;
			}

			if ( $price_changed ) {
				$product->set_date_on_sale_to( '' );
				$product->set_date_on_sale_from( '' );
			}
		}

		if ( self::is_cogs_feature_enabled() && isset( $request_data['_cogs_value'] ) ) {
			$cogs_value = $request_data['_cogs_value'];
			$cogs_value = '' === $cogs_value ? null : (float) wc_format_decimal( $cogs_value );
			$product->set_cogs_value( $cogs_value );
		}

		// phpcs:disable WordPress.Security.ValidatedSanitizedInput.MissingUnslash, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		$manage_stock = ! empty( $request_data['_manage_stock'] ) && ProductType::GROUPED !== $product->get_type() ? 'yes' : 'no';
		$backorders   = ! empty( $request_data['_backorders'] ) ? wc_clean( $request_data['_backorders'] ) : 'no';
		if ( ! empty( $request_data['_stock_status'] ) ) {
			$stock_status = wc_clean( $request_data['_stock_status'] );
		} else {
			$stock_status = $product->is_type( ProductType::VARIABLE ) ? null : ProductStockStatus::IN_STOCK;
		}
		// phpcs:enable WordPress.Security.ValidatedSanitizedInput.MissingUnslash, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

		$product->set_manage_stock( $manage_stock );

		if ( ProductType::EXTERNAL !== $product->get_type() ) {
			$product->set_backorders( $backorders );
		}

		if ( 'yes' === get_option( 'woocommerce_manage_stock' ) ) {
			$stock_amount = 'yes' === $manage_stock && isset( $request_data['_stock'] ) && is_numeric( wp_unslash( $request_data['_stock'] ) ) ? wc_stock_amount( wp_unslash( $request_data['_stock'] ) ) : '';
			$product->set_stock_quantity( $stock_amount );
		}

		$product = self::maybe_update_stock_status( $product, $stock_status );

		$product->save();

		do_action( 'woocommerce_product_quick_edit_save', $product );

		return true;
	}

	/**
	 * Whether WooCommerce COGS feature is enabled (mirrors WC list table / quick edit template).
	 *
	 * @return bool
	 */
	public static function is_cogs_feature_enabled() {
		if ( ! class_exists( CostOfGoodsSoldController::class ) ) {
			return false;
		}

		return wc_get_container()->get( CostOfGoodsSoldController::class )->feature_is_enabled();
	}

	/**
	 * Ported from WC_Admin_Post_Types::maybe_update_stock_status().
	 *
	 * @param \WC_Product $product      Product.
	 * @param string|null $stock_status Resolved stock status or null for variable “no change”.
	 * @return \WC_Product
	 */
	private static function maybe_update_stock_status( $product, $stock_status ) {
		if ( $product->is_type( ProductType::EXTERNAL ) ) {
			$product->set_stock_status( ProductStockStatus::IN_STOCK );
		} elseif ( isset( $stock_status ) ) {
			if ( $product->is_type( ProductType::VARIABLE ) && ! $product->get_manage_stock() ) {
				foreach ( $product->get_children() as $child_id ) {
					$child = wc_get_product( $child_id );
					if ( ! $product->get_manage_stock() && $child ) {
						$child->set_stock_status( $stock_status );
						$child->save();
					}
				}
				$product = WC_Product_Variable::sync( $product, false );
			} else {
				$product->set_stock_status( $stock_status );
			}
		}

		return $product;
	}
}
