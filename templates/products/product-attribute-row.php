<?php
/**
 * Single product attribute row template.
 *
 * @var WC_Product_Attribute|array $attribute
 * @var int                        $i            Index
 * @var WC_Product|null            $product
 * @var int                        $product_id
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Normalize: $attribute might be a WC_Product_Attribute object or array
if ( is_object( $attribute ) && method_exists( $attribute, 'get_name' ) ) {
    $attr_name       = $attribute->get_name();
    $attr_label      = wc_attribute_label( $attr_name );
    $is_taxonomy     = $attribute->is_taxonomy() || ( is_string( $attr_name ) && 0 === strpos( $attr_name, 'pa_' ) );
    $is_visible      = $attribute->get_visible();
    $is_variation    = $attribute->get_variation();
    $position        = $attribute->get_position();
    $attr_options    = $attribute->get_options();
} else {
    // fallback for array-based attribute
    $attr_name       = $attribute['name'] ?? '';
    $attr_label      = wc_attribute_label( $attr_name );
    $is_taxonomy     = ! empty( $attribute['is_taxonomy'] ) || ( is_string( $attr_name ) && 0 === strpos( $attr_name, 'pa_' ) );
    $is_visible      = ! empty( $attribute['is_visible'] );
    $is_variation    = ! empty( $attribute['is_variation'] );
    $position        = $attribute['position'] ?? 0;
    $attr_options    = $attribute['options'] ?? array();
}

$default_attributes = ( $product && is_object( $product ) && method_exists( $product, 'get_default_attributes' ) ) ? (array) $product->get_default_attributes() : array();
$default_value      = isset( $default_attributes[ $attr_name ] ) ? (string) $default_attributes[ $attr_name ] : '';
?>

<div class="storesuite-attribute-row storesuite-card storesuite-mb-12" data-index="<?php echo esc_attr( $i ); ?>" data-taxonomy="<?php echo esc_attr( $is_taxonomy ? $attr_name : '' ); ?>">
    <!-- Header (click to expand/collapse) -->
    <div class="storesuite-attribute-header storesuite-attribute-header-layout">
        <strong><?php echo esc_html( $attr_label ?: __( 'Attribute', 'storesuite' ) ); ?></strong>
        <span>
            <a href="#" class="storesuite-toggle-attribute" title="<?php esc_attr_e( 'Toggle', 'storesuite' ); ?>">▾</a>
            <a href="#" class="storesuite-remove-attribute storesuite-attribute-remove" title="<?php esc_attr_e( 'Remove', 'storesuite' ); ?>">✕</a>
        </span>
    </div>

    <!-- Body (collapsible) -->
    <div class="storesuite-attribute-body storesuite-card-content storesuite-attribute-body-hidden">
        <div class="row">
            <!-- Name -->
            <div class="col-md-6">
                <div class="storesuite-form-group">
                    <label><?php esc_html_e( 'Name', 'storesuite' ); ?></label>
                    <?php if ( $is_taxonomy ) : ?>
                        <strong><?php echo esc_html( $attr_label ); ?></strong>
                        <input type="hidden" name="attribute_names[<?php echo esc_attr( $i ); ?>]" value="<?php echo esc_attr( $attr_name ); ?>">
                    <?php else : ?>
                        <input type="text" class="storesuite-form-control" name="attribute_names[<?php echo esc_attr( $i ); ?>]" value="<?php echo esc_attr( $attr_name ); ?>" placeholder="<?php esc_attr_e( 'e.g. Material', 'storesuite' ); ?>">
                    <?php endif; ?>

                    <input type="hidden" name="attribute_position[<?php echo esc_attr( $i ); ?>]" class="attribute_position" value="<?php echo esc_attr( $position ); ?>">
                    <input type="hidden" name="attribute_is_taxonomy[<?php echo esc_attr( $i ); ?>]" value="<?php echo esc_attr( $is_taxonomy ? 1 : 0 ); ?>">
                </div>
                <div class="storesuite-form-group storesuite-form-switch">
                    <input id="storesuite-attribute-visibility-<?php echo esc_attr( $i ); ?>" type="checkbox" name="attribute_visibility[<?php echo esc_attr( $i ); ?>]" value="1" <?php checked( $is_visible ); ?>>
                    <label for="storesuite-attribute-visibility-<?php echo esc_attr( $i ); ?>"><?php esc_html_e( 'Visible on the product page', 'storesuite' ); ?></label>
                </div>
                <div class="storesuite-form-group storesuite-form-switch">
                    <input id="storesuite-attribute-variation-<?php echo esc_attr( $i ); ?>" type="checkbox" name="attribute_variation[<?php echo esc_attr( $i ); ?>]" value="1" <?php checked( $is_variation ); ?>>
                    <label for="storesuite-attribute-variation-<?php echo esc_attr( $i ); ?>"><?php esc_html_e( 'Used for variations', 'storesuite' ); ?></label>
                </div>

                <div class="storesuite-form-group">
                    <label><?php esc_html_e( 'Default value', 'storesuite' ); ?></label>
                    <select class="storesuite-form-control" name="default_attribute[<?php echo esc_attr( $i ); ?>]">
                        <option value=""><?php esc_html_e( 'No default value', 'storesuite' ); ?></option>
                        <?php if ( $is_taxonomy ) : ?>
                            <?php
                            $default_terms = get_terms(
                                array(
                                    'taxonomy'   => $attr_name,
                                    'orderby'    => 'name',
                                    'hide_empty' => false,
                                )
                            );
                            ?>
                            <?php if ( ! is_wp_error( $default_terms ) ) : ?>
                                <?php foreach ( $default_terms as $term ) : ?>
                                    <option value="<?php echo esc_attr( $term->slug ); ?>" <?php selected( $default_value, $term->slug ); ?>>
                                        <?php echo esc_html( $term->name ); ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        <?php else : ?>
                            <?php foreach ( (array) $attr_options as $option ) : ?>
                                <option value="<?php echo esc_attr( $option ); ?>" <?php selected( $default_value, (string) $option ); ?>>
                                    <?php echo esc_html( $option ); ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>
            </div>

            <!-- Values -->
            <div class="col-md-6">
                <div class="storesuite-form-group">
                    <label><?php esc_html_e( 'Value(s)', 'storesuite' ); ?></label>
                    <?php if ( $is_taxonomy ) : ?>
                        <?php
                        $all_terms = get_terms( array(
                            'taxonomy'   => $attr_name,
                            'orderby'    => 'name',
                            'hide_empty' => false,
                        ) );
                        $selected_ids = $attr_options; // term IDs for taxonomy
                        ?>
                        <select multiple class="storesuite-form-control storesuite-select2 storesuite-attribute-values"
                                name="attribute_values[<?php echo esc_attr( $i ); ?>][]"
                                data-placeholder="<?php esc_attr_e( 'Select terms', 'storesuite' ); ?>">
                            <?php if ( ! is_wp_error( $all_terms ) ) : ?>
                                <?php foreach ( $all_terms as $term ) : ?>
                                    <option value="<?php echo esc_attr( $term->term_id ); ?>"
                                        <?php selected( in_array( $term->term_id, $selected_ids, true ) ); ?>>
                                        <?php echo esc_html( $term->name ); ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                        <div class="storesuite-attribute-terms-toggle">
                            <a href="#" class="storesuite-select-all-terms"><?php esc_html_e( 'Select all', 'storesuite' ); ?></a>
                            <a href="#" class="storesuite-select-no-terms"><?php esc_html_e( 'Select none', 'storesuite' ); ?></a>
                        </div>
                    <?php else : ?>
                        <select multiple class="storesuite-form-control storesuite-select2 storesuite-attribute-values"
                                name="attribute_values[<?php echo esc_attr( $i ); ?>][]"
                                data-placeholder="<?php esc_attr_e( 'Type & select custom values', 'storesuite' ); ?>"
                                data-tags="true" data-token-separators='["|"]'>
                            <?php foreach ( $attr_options as $option ) : ?>
                                <option value="<?php echo esc_attr( $option ); ?>" selected><?php echo esc_html( $option ); ?></option>
                            <?php endforeach; ?>
                        </select>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>