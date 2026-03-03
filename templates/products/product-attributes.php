<?php
/**
 * Product attributes wrapper template.
 *
 * @var WC_Product|null $product
 * @var int             $product_id
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$attribute_taxonomies = wc_get_attribute_taxonomies();
$product_attributes   = $product ? $product->get_attributes( 'edit' ) : array();
?>

<div class="storesuite-card storesuite-card-with-header storesuite-mb-24 show_if_variable" id="storesuite-product-attributes">
    <h3 class="storesuite-card-title"><?php esc_html_e( 'Attributes', 'storesuite' ); ?>
        <small class="storesuite-text-muted"><?php esc_html_e( 'Add attributes for this product (e.g. Color, Size)', 'storesuite' ); ?></small>
    </h3>
    <div class="storesuite-card-content">

        <!-- Existing attribute rows -->
        <div id="storesuite-attributes-list" class="storesuite-attributes-list">
            <?php
            $i = 0;
            if ( ! empty( $product_attributes ) ) {
                foreach ( $product_attributes as $attribute ) {
                    $args = array(
                        'attribute'  => $attribute,
                        'i'          => $i,
                        'product'    => $product,
                        'product_id' => $product_id,
                    );
                    storesuite_get_template_part( 'products/product-attribute-row', '', $args );
                    $i++;
                }
            }
            ?>
        </div>

        <!-- Add attribute toolbar -->
        <div class="storesuite-attribute-toolbar storesuite-attribute-toolbar-spacing">
            <div class="row">
                <div class="col-md-6">
                    <select id="storesuite-add-attribute-select" class="storesuite-form-control">
                        <option value=""><?php esc_html_e( 'Custom attribute', 'storesuite' ); ?></option>
                        <?php foreach ( $attribute_taxonomies as $tax ) : ?>
                            <?php
                            $tax_name = wc_attribute_taxonomy_name( $tax->attribute_name );
                            // Skip if already added
                            $already_added = isset( $product_attributes[ $tax_name ] );
                            if ( $already_added ) {
                                continue;
                            }
                            ?>
                            <option value="<?php echo esc_attr( $tax_name ); ?>">
                                <?php echo esc_html( $tax->attribute_label ); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <button type="button" id="storesuite-add-attribute-btn" class="my-storesuite-button my-storesuite-button-sm">
                        <?php esc_html_e( 'Add attribute', 'storesuite' ); ?>
                    </button>
                    <button type="button" id="storesuite-save-attributes-btn" class="my-storesuite-button my-storesuite-button-sm my-storesuite-button-light">
                        <?php esc_html_e( 'Save attributes', 'storesuite' ); ?>
                    </button>
                </div>
            </div>
        </div>

    </div>
</div>