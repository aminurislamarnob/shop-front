<?php

namespace PluginizeLab\StoreSuite\Product;

use WC_Product_Attribute;
use WC_Meta_Box_Product_Data;
use Automattic\WooCommerce\Enums\ProductType;
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
