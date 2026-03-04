# Step-by-Step Guide: Implementing Product Attributes & Variations in StoreSuite

> **Goal**: Add full WooCommerce Variable Product support (attributes + variations CRUD) to StoreSuite's frontend product form.  
> **Approach**: Hybrid — WooCommerce PHP CRUD classes for data handling + custom AJAX wrappers + custom templates/JS matching StoreSuite's design system.  
> **Date**: March 2, 2026

---

## Table of Contents

- [Phase 0: Prerequisites & Planning](#phase-0-prerequisites--planning)
- [Phase 1: Product Type Switching UI](#phase-1-product-type-switching-ui)
- [Phase 2: Attributes Section — UI & Save](#phase-2-attributes-section--ui--save)
- [Phase 3: Variations Section — Load, Add, Delete](#phase-3-variations-section--load-add-delete)
- [Phase 4: Variation Editing — Full CRUD](#phase-4-variation-editing--full-crud)
- [Phase 5: Bulk Actions & Generate All Variations](#phase-5-bulk-actions--generate-all-variations)
- [Phase 6: Polish & Edge Cases](#phase-6-polish--edge-cases)
- [File Map: What Goes Where](#file-map-what-goes-where)

---

## Phase 0: Prerequisites & Planning

### 0.1 Understand the current codebase

StoreSuite already has:


| What                                         | Where                                                                                                                                                                           |
| -------------------------------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| Product form (add/edit)                      | `templates/products/product-form.php`                                                                                                                                           |
| Product type dropdown (`simple`, `variable`) | `includes/Product/ProductHooks.php` → `set_product_types()`                                                                                                                     |
| `ProductManager::create_product()`           | `includes/Product/ProductManager.php` — already handles `WC_Product_Variable`, calls `set_attributes()`, calls `WC_Product_Variable::sync()`, calls `save_default_attributes()` |
| `ProductController`                          | `includes/Product/ProductController.php` — handles `handle_add_product()` / `handle_edit_product()` via AJAX                                                                    |
| Frontend JS                                  | `assets/frontend/product.js` — form submit, validation, SweetAlert2, select2                                                                                                    |
| Template loader                              | `storesuite_get_template_part()` in `includes/functions.php`                                                                                                                    |
| Assets                                       | `includes/Assets.php` — registers scripts, localizes data                                                                                                                       |
| Design system                                | `storesuite-card`, `storesuite-form-control`, `storesuite-form-group`, Bootstrap grid (`row`/`col-md-`*)                                                                        |


StoreSuite does NOT yet have:

- Attributes UI section in the product form
- Variations UI section in the product form
- AJAX handlers for variation CRUD (load, add, save, remove, bulk edit, generate)
- Frontend JS for variation management
- Variation/attribute template files

### 0.2 File structure you'll create

```
storesuite/
├── includes/
│   └── Product/
│       ├── ProductController.php     ← MODIFY: add attribute sanitization to sanitize_product_data()
│       ├── ProductManager.php        ← MODIFY: add attribute building from POST data in storesuite_save_product()
│       └── VariationAjax.php         ← NEW: AJAX handlers for variation CRUD
├── templates/
│   └── products/
│       ├── product-form.php          ← MODIFY: add attributes + variations sections
│       ├── product-attributes.php    ← NEW: attributes wrapper template
│       ├── product-attribute-row.php ← NEW: single attribute row template
│       ├── product-variations.php    ← NEW: variations wrapper template  
│       └── product-variation-row.php ← NEW: single variation row template
├── assets/
│   └── frontend/
│       ├── product.js                ← MODIFY: add product type toggle logic
│       └── product-variation.js      ← NEW: variation management JS
└── includes/
    └── Assets.php                    ← MODIFY: register + enqueue new JS
```

### 0.3 WooCommerce functions you'll reuse (no custom code needed)

```php
// Attribute helpers
wc_get_attribute_taxonomies()          // List all global attributes (Color, Size, etc.)
wc_attribute_taxonomy_name($slug)      // 'color' → 'pa_color'
wc_attribute_label($taxonomy)          // 'pa_color' → 'Color'
wc_attribute_taxonomy_id_by_name($name)// 'pa_color' → 5
wc_get_text_attributes($string)        // 'S|M|L' → ['S','M','L']

// CRUD classes
new WC_Product_Attribute()             // Build attribute objects
new WC_Product_Variable($id)           // Variable product
new WC_Product_Variation()             // Create/read variations
$product->set_attributes($attrs)       // Save attributes
$variation->save()                     // Persist variation
WC_Product_Variable::sync($id)         // Sync prices/stock to parent

// Data reading
$product->get_children()               // All variation IDs
$product->get_attributes()             // Get attribute objects
$variation->get_regular_price()        // All getters available
wc_get_product_variation_attributes($id) // ['attribute_pa_color' => 'red']
wc_get_product_stock_status_options()  // Already used in product-form.php

// Duplicate check
WC_Data_Store::load('product')->find_matching_product_variation($product, $attrs)
```

---

## Phase 1: Product Type Switching UI

**Goal**: When user changes product type dropdown to "Variable", hide the Pricing card and show Attributes + Variations sections. When "Simple", reverse.

### Step 1.1 — Modify `product-form.php`: wrap sections with type classes

In `templates/products/product-form.php`, add CSS classes to existing cards so JS can show/hide them:

```php
<!-- Wrap the Pricing card -->
<div class="storesuite-card storesuite-card-with-header storesuite-mb-24 show_if_simple hide_if_variable">
    <h3 class="storesuite-card-title"><?php esc_html_e( 'Pricing', 'storesuite' ); ?></h3>
    <!-- ... existing pricing fields ... -->
</div>
```

Add the same `show_if_simple hide_if_variable` to any section that should only show for simple products (Pricing card, most of the Inventory card's price-related fields).

### Step 1.2 — Modify `product.js`: add product type toggle

In `assets/frontend/product.js`, add to the `init()` method:

```js
init: function() {
    // ... existing code ...
    this.toggleProductType();
    $(document).on('change', '#post_type', this.toggleProductType.bind(this));
},

toggleProductType: function() {
    var productType = $('#post_type').val();

    // Hide all type-specific sections first
    $('.show_if_simple, .show_if_variable').hide();
    $('.hide_if_simple, .hide_if_variable').show();

    // Show sections for current type
    $('.show_if_' + productType).show();
    $('.hide_if_' + productType).hide();
},
```

**Verify**: Select "Variable" → Pricing card hides. Select "Simple" → Pricing card shows back.

---

## Phase 2: Attributes Section — UI & Save

### Step 2.1 — Create `templates/products/product-attributes.php`

This is the attributes wrapper that sits inside the product form. It shows:

- A list of existing attributes (each rendered by `product-attribute-row.php`)
- A dropdown to add new attributes (global taxonomies + "Custom attribute")
- A "Save attributes" button

```php
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
        <div class="storesuite-attribute-toolbar" style="margin-top:12px;">
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
```

### Step 2.2 — Create `templates/products/product-attribute-row.php`

This renders one attribute row (collapsible). Handles both global taxonomy attributes and custom text attributes.

```php
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
    $is_taxonomy     = $attribute->is_taxonomy();
    $is_visible      = $attribute->get_visible();
    $is_variation    = $attribute->get_variation();
    $position        = $attribute->get_position();
    $attr_options    = $attribute->get_options();
} else {
    // fallback for array-based attribute
    $attr_name       = $attribute['name'] ?? '';
    $attr_label      = wc_attribute_label( $attr_name );
    $is_taxonomy     = ! empty( $attribute['is_taxonomy'] );
    $is_visible      = ! empty( $attribute['is_visible'] );
    $is_variation    = ! empty( $attribute['is_variation'] );
    $position        = $attribute['position'] ?? 0;
    $attr_options    = $attribute['options'] ?? array();
}
?>

<div class="storesuite-attribute-row storesuite-card storesuite-mb-12" data-index="<?php echo esc_attr( $i ); ?>" data-taxonomy="<?php echo esc_attr( $is_taxonomy ? $attr_name : '' ); ?>">
    <!-- Header (click to expand/collapse) -->
    <div class="storesuite-attribute-header" style="display:flex; justify-content:space-between; align-items:center; padding:10px 16px; cursor:pointer;">
        <strong><?php echo esc_html( $attr_label ?: __( 'Attribute', 'storesuite' ) ); ?></strong>
        <span>
            <a href="#" class="storesuite-toggle-attribute" title="<?php esc_attr_e( 'Toggle', 'storesuite' ); ?>">▾</a>
            <a href="#" class="storesuite-remove-attribute" title="<?php esc_attr_e( 'Remove', 'storesuite' ); ?>" style="color:#d63638; margin-left:8px;">✕</a>
        </span>
    </div>

    <!-- Body (collapsible) -->
    <div class="storesuite-attribute-body storesuite-card-content" style="display:none;">
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
                    <input type="checkbox" name="attribute_visibility[<?php echo esc_attr( $i ); ?>]" value="1" <?php checked( $is_visible ); ?>>
                    <label><?php esc_html_e( 'Visible on the product page', 'storesuite' ); ?></label>
                </div>
                <div class="storesuite-form-group storesuite-form-switch">
                    <input type="checkbox" name="attribute_variation[<?php echo esc_attr( $i ); ?>]" value="1" <?php checked( $is_variation ); ?>>
                    <label><?php esc_html_e( 'Used for variations', 'storesuite' ); ?></label>
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
                        <div style="margin-top:6px;">
                            <a href="#" class="storesuite-select-all-terms"><?php esc_html_e( 'Select all', 'storesuite' ); ?></a>
                             | 
                            <a href="#" class="storesuite-select-no-terms"><?php esc_html_e( 'Select none', 'storesuite' ); ?></a>
                        </div>
                    <?php else : ?>
                        <?php
                        // Custom attribute: options are strings
                        ?>
                        <select multiple class="storesuite-form-control storesuite-select2 storesuite-attribute-values"
                                name="attribute_values[<?php echo esc_attr( $i ); ?>][]"
                                data-placeholder="<?php esc_attr_e( 'Enter values separated by |', 'storesuite' ); ?>"
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
```

### Step 2.3 — Create AJAX handler: `storesuite_save_attributes`

In a new file `includes/Product/VariationAjax.php`:

```php
<?php

namespace PluginizeLab\StoreSuite\Product;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class VariationAjax {

    public function __construct() {
        // Attribute actions
        add_action( 'wp_ajax_storesuite_save_attributes', array( $this, 'save_attributes' ) );

        // Variation actions
        add_action( 'wp_ajax_storesuite_load_variations', array( $this, 'load_variations' ) );
        add_action( 'wp_ajax_storesuite_add_variation', array( $this, 'add_variation' ) );
        add_action( 'wp_ajax_storesuite_save_variations', array( $this, 'save_variations' ) );
        add_action( 'wp_ajax_storesuite_remove_variation', array( $this, 'remove_variation' ) );
        add_action( 'wp_ajax_storesuite_generate_variations', array( $this, 'generate_variations' ) );
        add_action( 'wp_ajax_storesuite_bulk_edit_variations', array( $this, 'bulk_edit_variations' ) );
    }

    // ... handler methods shown in later phases ...
}
```

**The `save_attributes()` handler:**

```php
public function save_attributes() {
    check_ajax_referer( 'storesuite-variation-nonce', 'security' );

    $product_id = absint( $_POST['product_id'] );

    if ( ! current_user_can( 'manage_woocommerce' ) ) {
        wp_send_json_error( 'Unauthorized' );
    }

    $product = wc_get_product( $product_id );
    if ( ! $product ) {
        wp_send_json_error( 'Product not found' );
    }

    $attributes = array();

    if ( isset( $_POST['attribute_names'] ) ) {
        $attr_names      = array_map( 'wc_clean', $_POST['attribute_names'] );
        $attr_values     = isset( $_POST['attribute_values'] ) ? $_POST['attribute_values'] : array();
        $attr_visibility = isset( $_POST['attribute_visibility'] ) ? $_POST['attribute_visibility'] : array();
        $attr_variation  = isset( $_POST['attribute_variation'] ) ? $_POST['attribute_variation'] : array();
        $attr_position   = isset( $_POST['attribute_position'] ) ? $_POST['attribute_position'] : array();
        $attr_is_tax     = isset( $_POST['attribute_is_taxonomy'] ) ? $_POST['attribute_is_taxonomy'] : array();

        foreach ( $attr_names as $i => $name ) {
            if ( empty( $name ) ) {
                continue;
            }

            $is_taxonomy = ! empty( $attr_is_tax[ $i ] );
            $attribute   = new \WC_Product_Attribute();

            if ( $is_taxonomy ) {
                $attribute->set_id( wc_attribute_taxonomy_id_by_name( $name ) );
                $attribute->set_name( $name );
                // For taxonomy: values are term IDs
                $values = isset( $attr_values[ $i ] ) ? array_map( 'absint', $attr_values[ $i ] ) : array();
                $attribute->set_options( $values );
            } else {
                $attribute->set_id( 0 );
                $attribute->set_name( $name );
                // For custom: values are strings
                $values = isset( $attr_values[ $i ] ) ? array_map( 'sanitize_text_field', $attr_values[ $i ] ) : array();
                $attribute->set_options( $values );
            }

            $attribute->set_position( isset( $attr_position[ $i ] ) ? absint( $attr_position[ $i ] ) : $i );
            $attribute->set_visible( isset( $attr_visibility[ $i ] ) );
            $attribute->set_variation( isset( $attr_variation[ $i ] ) );

            $attributes[] = $attribute;
        }
    }

    $product->set_attributes( $attributes );
    $product->save();

    wp_send_json_success( array(
        'message'    => __( 'Attributes saved.', 'storesuite' ),
        'attributes' => count( $attributes ),
    ) );
}
```

### Step 2.4 — Wire up the JS for attributes

In your new `assets/frontend/product-variation.js` (or extend `product.js`), add:

```js
// --- Attribute Management ---
var StoreSuiteAttributes = {
    index: 0, // track attribute row count for naming

    init: function() {
        this.index = $('#storesuite-attributes-list .storesuite-attribute-row').length;

        $(document).on('click', '#storesuite-add-attribute-btn', this.addAttribute.bind(this));
        $(document).on('click', '#storesuite-save-attributes-btn', this.saveAttributes.bind(this));
        $(document).on('click', '.storesuite-remove-attribute', this.removeAttribute);
        $(document).on('click', '.storesuite-toggle-attribute', this.toggleAttribute);
        $(document).on('click', '.storesuite-select-all-terms', this.selectAllTerms);
        $(document).on('click', '.storesuite-select-no-terms', this.selectNoTerms);
    },

    addAttribute: function() {
        var taxonomy = $('#storesuite-add-attribute-select').val();
        var index = this.index++;

        // Build new row via AJAX or use a JS template
        // Simplest: AJAX call that returns rendered template
        $.post(StoreSuiteVariation.ajax_url, {
            action: 'storesuite_add_attribute_row',
            security: StoreSuiteVariation.nonce,
            product_id: StoreSuiteVariation.product_id,
            taxonomy: taxonomy,
            index: index
        }, function(response) {
            if (response.success) {
                $('#storesuite-attributes-list').append(response.data.html);
                // Re-init select2 on new row
                $('.storesuite-select2').filter(':not(.enhanced)').each(function() {
                    $(this).selectWoo({
                        allowClear: true,
                        placeholder: $(this).data('placeholder') || '',
                        tags: $(this).data('tags') || false,
                        tokenSeparators: ['|'],
                        width: '100%'
                    }).addClass('enhanced');
                });

                // Remove the used taxonomy from the dropdown
                if (taxonomy) {
                    $('#storesuite-add-attribute-select option[value="' + taxonomy + '"]').remove();
                }
            }
        });
    },

    saveAttributes: function() {
        var formData = new FormData();
        formData.append('action', 'storesuite_save_attributes');
        formData.append('security', StoreSuiteVariation.nonce);
        formData.append('product_id', StoreSuiteVariation.product_id);

        // Collect all attribute fields
        $('#storesuite-attributes-list :input').each(function() {
            var $input = $(this);
            var name = $input.attr('name');
            if (!name) return;

            if ($input.is(':checkbox')) {
                if ($input.is(':checked')) {
                    formData.append(name, $input.val());
                }
            } else if ($input.is('select[multiple]')) {
                var values = $input.val() || [];
                values.forEach(function(v) {
                    formData.append(name, v);
                });
            } else {
                formData.append(name, $input.val());
            }
        });

        window.StoreSuite.storeSuiteLoader.block($('#storesuite-product-attributes'));

        $.ajax({
            url: StoreSuiteVariation.ajax_url,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    Swal.fire({ icon: 'success', title: response.data.message, timer: 1500, showConfirmButton: false });
                    // Reload variations section (attributes may have changed)
                    StoreSuiteVariations.reload();
                } else {
                    Swal.fire({ icon: 'error', text: response.data });
                }
            },
            complete: function() {
                window.StoreSuite.storeSuiteLoader.unblock($('#storesuite-product-attributes'));
            }
        });
    },

    removeAttribute: function(e) {
        e.preventDefault();
        $(this).closest('.storesuite-attribute-row').slideUp(200, function() { $(this).remove(); });
    },

    toggleAttribute: function(e) {
        e.preventDefault();
        $(this).closest('.storesuite-attribute-row').find('.storesuite-attribute-body').slideToggle(200);
    },

    selectAllTerms: function(e) {
        e.preventDefault();
        var $select = $(this).closest('.storesuite-form-group').find('select');
        $select.find('option').prop('selected', true);
        $select.trigger('change');
    },

    selectNoTerms: function(e) {
        e.preventDefault();
        var $select = $(this).closest('.storesuite-form-group').find('select');
        $select.find('option').prop('selected', false);
        $select.trigger('change');
    }
};
```

### Step 2.5 — Also save attributes during main product form submit

Currently `ProductController::sanitize_product_data()` doesn't handle attribute fields. You need to:

1. In `sanitize_product_data()`, add:

```php
// Attribute data (arrays)
if ( isset( $post_data['attribute_names'] ) ) {
    $data['attribute_names']      = array_map( 'sanitize_text_field', (array) $post_data['attribute_names'] );
    $data['attribute_values']     = isset( $post_data['attribute_values'] ) ? $post_data['attribute_values'] : array();
    $data['attribute_visibility'] = isset( $post_data['attribute_visibility'] ) ? $post_data['attribute_visibility'] : array();
    $data['attribute_variation']  = isset( $post_data['attribute_variation'] ) ? $post_data['attribute_variation'] : array();
    $data['attribute_position']   = isset( $post_data['attribute_position'] ) ? $post_data['attribute_position'] : array();
    $data['attribute_is_taxonomy']= isset( $post_data['attribute_is_taxonomy'] ) ? $post_data['attribute_is_taxonomy'] : array();
}
```

1. In `ProductManager::storesuite_save_product()`, before calling `create_product()`, build the `WC_Product_Attribute` objects:

```php
// Build attributes array if provided
if ( ! empty( $data['attribute_names'] ) ) {
    $attributes = array();
    foreach ( $data['attribute_names'] as $i => $name ) {
        if ( empty( $name ) ) continue;

        $is_taxonomy = ! empty( $data['attribute_is_taxonomy'][ $i ] );
        $attribute = new \WC_Product_Attribute();

        if ( $is_taxonomy ) {
            $attribute->set_id( wc_attribute_taxonomy_id_by_name( $name ) );
            $attribute->set_name( $name );
            $values = isset( $data['attribute_values'][ $i ] ) ? array_map( 'absint', (array) $data['attribute_values'][ $i ] ) : array();
        } else {
            $attribute->set_id( 0 );
            $attribute->set_name( $name );
            $values = isset( $data['attribute_values'][ $i ] ) ? array_map( 'sanitize_text_field', (array) $data['attribute_values'][ $i ] ) : array();
        }

        $attribute->set_options( $values );
        $attribute->set_position( isset( $data['attribute_position'][ $i ] ) ? absint( $data['attribute_position'][ $i ] ) : $i );
        $attribute->set_visible( isset( $data['attribute_visibility'][ $i ] ) );
        $attribute->set_variation( isset( $data['attribute_variation'][ $i ] ) );

        $attributes[] = $attribute;
    }
    $post_data['attributes'] = $attributes;
}
```

The existing `create_product()` already handles `$args['attributes']` at line ~322.

---

## Phase 3: Variations Section — Load, Add, Delete

### Step 3.1 — Create `templates/products/product-variations.php`

This is the variations wrapper template:

```php
<?php
/**
 * Product variations wrapper template.
 *
 * @var WC_Product|null $product
 * @var int             $product_id
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$variation_attributes = array();
$total_variations     = 0;
$per_page             = 15;

if ( $product && $product->is_type( 'variable' ) ) {
    $variation_attributes = array_filter( $product->get_attributes( 'edit' ), 'wc_attributes_array_filter_variation' );
    $total_variations     = count( $product->get_children() );
}
?>

<div class="storesuite-card storesuite-card-with-header storesuite-mb-24 show_if_variable" id="storesuite-product-variations">
    <h3 class="storesuite-card-title">
        <?php esc_html_e( 'Variations', 'storesuite' ); ?>
        <small class="storesuite-text-muted storesuite-variation-count">
            <?php
            if ( $total_variations ) {
                /* translators: %d: variation count */
                printf( esc_html( _n( '%d variation', '%d variations', $total_variations, 'storesuite' ) ), $total_variations );
            }
            ?>
        </small>
    </h3>
    <div class="storesuite-card-content">

        <?php if ( empty( $variation_attributes ) ) : ?>
            <p class="storesuite-no-variation-attrs">
                <?php esc_html_e( 'Before you can add a variation you need to add some attributes in the Attributes section above and check "Used for variations".', 'storesuite' ); ?>
            </p>
        <?php else : ?>

            <!-- Toolbar -->
            <div class="storesuite-variation-toolbar" style="margin-bottom:16px;">
                <div class="row">
                    <div class="col-md-6">
                        <select id="storesuite-variation-actions" class="storesuite-form-control">
                            <option value="add_variation"><?php esc_html_e( 'Add variation', 'storesuite' ); ?></option>
                            <option value="generate_all"><?php esc_html_e( 'Create variations from all attributes', 'storesuite' ); ?></option>
                            <option value="delete_all"><?php esc_html_e( 'Delete all variations', 'storesuite' ); ?></option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <button type="button" id="storesuite-do-variation-action" class="my-storesuite-button my-storesuite-button-sm">
                            <?php esc_html_e( 'Go', 'storesuite' ); ?>
                        </button>
                        <button type="button" id="storesuite-save-variations-btn" class="my-storesuite-button my-storesuite-button-sm my-storesuite-button-light" disabled>
                            <?php esc_html_e( 'Save changes', 'storesuite' ); ?>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Default Attributes -->
            <?php if ( ! empty( $variation_attributes ) ) : ?>
                <div class="storesuite-default-attributes" style="margin-bottom:16px;">
                    <label style="font-weight:500;"><?php esc_html_e( 'Default Form Values:', 'storesuite' ); ?></label>
                    <div class="row" style="margin-top:6px;">
                        <?php foreach ( $variation_attributes as $attribute ) : ?>
                            <?php
                            $selected_default = $product ? $product->get_default_attributes() : array();
                            $attr_key         = sanitize_title( $attribute->get_name() );
                            $current_default  = isset( $selected_default[ $attr_key ] ) ? $selected_default[ $attr_key ] : '';
                            ?>
                            <div class="col-md-3">
                                <select class="storesuite-form-control storesuite-default-attribute"
                                        name="default_attribute_<?php echo esc_attr( $attr_key ); ?>">
                                    <option value=""><?php printf( esc_html__( 'No default %s…', 'storesuite' ), esc_html( wc_attribute_label( $attribute->get_name() ) ) ); ?></option>
                                    <?php if ( $attribute->is_taxonomy() ) : ?>
                                        <?php foreach ( $attribute->get_terms() as $term ) : ?>
                                            <option value="<?php echo esc_attr( $term->slug ); ?>" <?php selected( $current_default, $term->slug ); ?>>
                                                <?php echo esc_html( $term->name ); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    <?php else : ?>
                                        <?php foreach ( $attribute->get_options() as $option ) : ?>
                                            <option value="<?php echo esc_attr( $option ); ?>" <?php selected( $current_default, $option ); ?>>
                                                <?php echo esc_html( $option ); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Variations Container (loaded via AJAX) -->
            <div id="storesuite-variations-container"
                 data-product-id="<?php echo esc_attr( $product_id ); ?>"
                 data-total="<?php echo esc_attr( $total_variations ); ?>"
                 data-per-page="<?php echo esc_attr( $per_page ); ?>"
                 data-page="1">
                <!-- Variation rows will be loaded here via AJAX -->
            </div>

            <!-- Pagination -->
            <?php if ( $total_variations > $per_page ) : ?>
                <div class="storesuite-variation-pagination" style="margin-top:12px; text-align:center;">
                    <!-- Pagination built dynamically by JS -->
                </div>
            <?php endif; ?>

        <?php endif; ?>
    </div>
</div>
```

### Step 3.2 — Create `templates/products/product-variation-row.php`

This is the single variation row (accordion-style):

```php
<?php
/**
 * Single product variation row template.
 *
 * @var WC_Product_Variation $variation
 * @var int                  $variation_id
 * @var int                  $loop         Index
 * @var WC_Product_Variable  $parent
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Ensure WC form helper functions are available
if ( ! function_exists( 'woocommerce_wp_text_input' ) ) {
    include_once WC_ABSPATH . 'includes/admin/wc-meta-box-functions.php';
}

$variation_attributes = $variation->get_attributes(); // e.g. ['pa_color' => 'red']
$parent_attributes    = $parent->get_attributes( 'edit' );
$enabled              = $variation->get_status() === 'publish';
$variation_image_id   = $variation->get_image_id();
$variation_thumb      = $variation_image_id ? wp_get_attachment_image_url( $variation_image_id, 'thumbnail' ) : wc_placeholder_img_src( 'thumbnail' );
?>

<div class="storesuite-variation-row storesuite-card storesuite-mb-8" data-variation-id="<?php echo esc_attr( $variation_id ); ?>">
    <!-- Header -->
    <div class="storesuite-variation-header" style="display:flex; align-items:center; gap:10px; padding:8px 16px; cursor:pointer;">
        <!-- Thumbnail -->
        <img src="<?php echo esc_url( $variation_thumb ); ?>" width="40" height="40" style="border-radius:4px; object-fit:cover;">

        <!-- Attribute selectors -->
        <?php foreach ( $parent_attributes as $attribute ) :
            if ( ! $attribute->get_variation() ) continue;
            $attr_key       = sanitize_title( $attribute->get_name() );
            $selected_value = isset( $variation_attributes[ $attr_key ] ) ? $variation_attributes[ $attr_key ] : '';
        ?>
            <select class="storesuite-form-control" style="width:auto; min-width:100px;"
                    name="attribute_<?php echo esc_attr( $attr_key ); ?>[<?php echo esc_attr( $loop ); ?>]">
                <option value=""><?php printf( esc_html__( 'Any %s…', 'storesuite' ), esc_html( wc_attribute_label( $attribute->get_name() ) ) ); ?></option>
                <?php if ( $attribute->is_taxonomy() ) : ?>
                    <?php foreach ( $attribute->get_terms() as $term ) : ?>
                        <option value="<?php echo esc_attr( $term->slug ); ?>" <?php selected( $selected_value, $term->slug ); ?>>
                            <?php echo esc_html( apply_filters( 'woocommerce_variation_option_name', $term->name, $term, $attribute->get_name(), $parent ) ); ?>
                        </option>
                    <?php endforeach; ?>
                <?php else : ?>
                    <?php foreach ( $attribute->get_options() as $option ) : ?>
                        <option value="<?php echo esc_attr( $option ); ?>" <?php selected( $selected_value, $option ); ?>>
                            <?php echo esc_html( apply_filters( 'woocommerce_variation_option_name', $option, null, $attribute->get_name(), $parent ) ); ?>
                        </option>
                    <?php endforeach; ?>
                <?php endif; ?>
            </select>
        <?php endforeach; ?>

        <span style="margin-left:auto;">
            <a href="#" class="storesuite-toggle-variation" title="▾">▾</a>
            <a href="#" class="storesuite-remove-variation" data-variation-id="<?php echo esc_attr( $variation_id ); ?>" style="color:#d63638; margin-left:8px;" title="✕">✕</a>
        </span>

        <input type="hidden" name="variable_post_id[<?php echo esc_attr( $loop ); ?>]" value="<?php echo esc_attr( $variation_id ); ?>">
        <input type="hidden" name="variable_menu_order[<?php echo esc_attr( $loop ); ?>]" class="variation_menu_order" value="<?php echo esc_attr( $variation->get_menu_order() ); ?>">
    </div>

    <!-- Body (collapsible) -->
    <div class="storesuite-variation-body storesuite-card-content" style="display:none;">
        <div class="row" style="margin-bottom:12px;">
            <!-- Enabled / Virtual / Downloadable / Manage Stock toggles -->
            <div class="col-md-3">
                <div class="storesuite-form-group storesuite-form-switch">
                    <input type="checkbox" name="variable_enabled[<?php echo esc_attr( $loop ); ?>]" value="1" <?php checked( $enabled ); ?>>
                    <label><?php esc_html_e( 'Enabled', 'storesuite' ); ?></label>
                </div>
            </div>
            <div class="col-md-3">
                <div class="storesuite-form-group storesuite-form-switch">
                    <input type="checkbox" class="variable_is_virtual" name="variable_is_virtual[<?php echo esc_attr( $loop ); ?>]" value="1" <?php checked( $variation->get_virtual() ); ?>>
                    <label><?php esc_html_e( 'Virtual', 'storesuite' ); ?></label>
                </div>
            </div>
            <div class="col-md-3">
                <div class="storesuite-form-group storesuite-form-switch">
                    <input type="checkbox" class="variable_is_downloadable" name="variable_is_downloadable[<?php echo esc_attr( $loop ); ?>]" value="1" <?php checked( $variation->get_downloadable() ); ?>>
                    <label><?php esc_html_e( 'Downloadable', 'storesuite' ); ?></label>
                </div>
            </div>
            <div class="col-md-3">
                <div class="storesuite-form-group storesuite-form-switch">
                    <input type="checkbox" class="variable_manage_stock" name="variable_manage_stock[<?php echo esc_attr( $loop ); ?>]" value="1" <?php checked( $variation->get_manage_stock() ); ?>>
                    <label><?php esc_html_e( 'Manage stock', 'storesuite' ); ?></label>
                </div>
            </div>
        </div>

        <!-- Image + SKU -->
        <div class="row">
            <div class="col-md-2">
                <div class="storesuite-form-group storesuite-variation-image-upload">
                    <label><?php esc_html_e( 'Image', 'storesuite' ); ?></label>
                    <div class="storesuite-variation-thumb" data-loop="<?php echo esc_attr( $loop ); ?>">
                        <img src="<?php echo esc_url( $variation_thumb ); ?>" width="60" height="60" style="cursor:pointer; border-radius:4px;">
                        <input type="hidden" name="upload_image_id[<?php echo esc_attr( $loop ); ?>]" value="<?php echo esc_attr( $variation_image_id ); ?>">
                    </div>
                </div>
            </div>
            <div class="col-md-5">
                <div class="storesuite-form-group">
                    <label><?php printf( esc_html__( 'Regular price (%s)', 'storesuite' ), get_woocommerce_currency_symbol() ); ?></label>
                    <input type="text" class="storesuite-form-control wc_input_price" name="variable_regular_price[<?php echo esc_attr( $loop ); ?>]"
                           value="<?php echo esc_attr( wc_format_localized_price( $variation->get_regular_price( 'edit' ) ) ); ?>"
                           placeholder="<?php esc_attr_e( 'Variation price (required)', 'storesuite' ); ?>">
                </div>
            </div>
            <div class="col-md-5">
                <div class="storesuite-form-group">
                    <label><?php printf( esc_html__( 'Sale price (%s)', 'storesuite' ), get_woocommerce_currency_symbol() ); ?></label>
                    <input type="text" class="storesuite-form-control wc_input_price" name="variable_sale_price[<?php echo esc_attr( $loop ); ?>]"
                           value="<?php echo esc_attr( wc_format_localized_price( $variation->get_sale_price( 'edit' ) ) ); ?>">
                </div>
            </div>
        </div>

        <!-- SKU + Stock -->
        <div class="row">
            <div class="col-md-4">
                <div class="storesuite-form-group">
                    <label><?php esc_html_e( 'SKU', 'storesuite' ); ?></label>
                    <input type="text" class="storesuite-form-control" name="variable_sku[<?php echo esc_attr( $loop ); ?>]"
                           value="<?php echo esc_attr( $variation->get_sku( 'edit' ) ); ?>"
                           placeholder="<?php echo esc_attr( $parent->get_sku() ); ?>">
                </div>
            </div>
            <div class="col-md-4">
                <div class="storesuite-form-group">
                    <label><?php esc_html_e( 'Stock status', 'storesuite' ); ?></label>
                    <select class="storesuite-form-control" name="variable_stock_status[<?php echo esc_attr( $loop ); ?>]">
                        <?php foreach ( wc_get_product_stock_status_options() as $key => $label ) : ?>
                            <option value="<?php echo esc_attr( $key ); ?>" <?php selected( $variation->get_stock_status( 'edit' ), $key ); ?>>
                                <?php echo esc_html( $label ); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="col-md-4 show_if_variation_manage_stock">
                <div class="storesuite-form-group">
                    <label><?php esc_html_e( 'Stock qty', 'storesuite' ); ?></label>
                    <input type="number" class="storesuite-form-control" name="variable_stock[<?php echo esc_attr( $loop ); ?>]"
                           value="<?php echo esc_attr( wc_stock_amount( $variation->get_stock_quantity( 'edit' ) ) ); ?>" step="any">
                </div>
            </div>
        </div>

        <!-- Shipping dimensions (hide if virtual) -->
        <div class="hide_if_variation_virtual">
            <div class="row">
                <div class="col-md-3">
                    <div class="storesuite-form-group">
                        <label><?php printf( esc_html__( 'Weight (%s)', 'storesuite' ), esc_html( get_option( 'woocommerce_weight_unit' ) ) ); ?></label>
                        <input type="text" class="storesuite-form-control wc_input_decimal" name="variable_weight[<?php echo esc_attr( $loop ); ?>]"
                               value="<?php echo esc_attr( wc_format_localized_decimal( $variation->get_weight( 'edit' ) ) ); ?>"
                               placeholder="<?php echo esc_attr( $parent->get_weight() ); ?>">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="storesuite-form-group">
                        <label><?php esc_html_e( 'Length', 'storesuite' ); ?></label>
                        <input type="text" class="storesuite-form-control wc_input_decimal" name="variable_length[<?php echo esc_attr( $loop ); ?>]"
                               value="<?php echo esc_attr( wc_format_localized_decimal( $variation->get_length( 'edit' ) ) ); ?>">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="storesuite-form-group">
                        <label><?php esc_html_e( 'Width', 'storesuite' ); ?></label>
                        <input type="text" class="storesuite-form-control wc_input_decimal" name="variable_width[<?php echo esc_attr( $loop ); ?>]"
                               value="<?php echo esc_attr( wc_format_localized_decimal( $variation->get_width( 'edit' ) ) ); ?>">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="storesuite-form-group">
                        <label><?php esc_html_e( 'Height', 'storesuite' ); ?></label>
                        <input type="text" class="storesuite-form-control wc_input_decimal" name="variable_height[<?php echo esc_attr( $loop ); ?>]"
                               value="<?php echo esc_attr( wc_format_localized_decimal( $variation->get_height( 'edit' ) ) ); ?>">
                    </div>
                </div>
            </div>
        </div>

        <!-- Description -->
        <div class="row">
            <div class="col-md-12">
                <div class="storesuite-form-group">
                    <label><?php esc_html_e( 'Description', 'storesuite' ); ?></label>
                    <textarea class="storesuite-form-control" name="variable_description[<?php echo esc_attr( $loop ); ?>]" rows="2"><?php echo esc_textarea( $variation->get_description( 'edit' ) ); ?></textarea>
                </div>
            </div>
        </div>

        <?php do_action( 'storesuite_product_after_variable_attributes', $loop, array(), $variation ); ?>
    </div>
</div>
```

### Step 3.3 — AJAX handler: `load_variations`

```php
public function load_variations() {
    check_ajax_referer( 'storesuite-variation-nonce', 'security' );

    $product_id = absint( $_POST['product_id'] );
    $page       = isset( $_POST['page'] ) ? absint( $_POST['page'] ) : 1;
    $per_page   = isset( $_POST['per_page'] ) ? absint( $_POST['per_page'] ) : 15;

    $product = wc_get_product( $product_id );
    if ( ! $product || ! $product->is_type( 'variable' ) ) {
        wp_send_json_error( 'Invalid product' );
    }

    $children = $product->get_children();
    $total    = count( $children );
    $offset   = ( $page - 1 ) * $per_page;
    $page_ids = array_slice( $children, $offset, $per_page );

    ob_start();
    $loop = $offset;
    foreach ( $page_ids as $variation_id ) {
        $variation = wc_get_product( $variation_id );
        if ( ! $variation ) continue;

        storesuite_get_template_part( 'products/product-variation-row', '', array(
            'variation'    => $variation,
            'variation_id' => $variation_id,
            'loop'         => $loop,
            'parent'       => $product,
        ) );
        $loop++;
    }
    $html = ob_get_clean();

    wp_send_json_success( array(
        'html'        => $html,
        'total'       => $total,
        'total_pages' => ceil( $total / $per_page ),
        'page'        => $page,
    ) );
}
```

### Step 3.4 — AJAX handler: `add_variation`

```php
public function add_variation() {
    check_ajax_referer( 'storesuite-variation-nonce', 'security' );

    $product_id = absint( $_POST['product_id'] );

    if ( ! current_user_can( 'manage_woocommerce' ) ) {
        wp_send_json_error( 'Unauthorized' );
    }

    $variation = new \WC_Product_Variation();
    $variation->set_parent_id( $product_id );
    $variation->set_status( 'publish' );
    $variation_id = $variation->save();

    $parent = wc_get_product( $product_id );
    $loop   = absint( $_POST['loop'] ?? 0 );

    ob_start();
    storesuite_get_template_part( 'products/product-variation-row', '', array(
        'variation'    => wc_get_product( $variation_id ),
        'variation_id' => $variation_id,
        'loop'         => $loop,
        'parent'       => $parent,
    ) );
    $html = ob_get_clean();

    wp_send_json_success( array( 'html' => $html, 'variation_id' => $variation_id ) );
}
```

### Step 3.5 — AJAX handler: `remove_variation`

```php
public function remove_variation() {
    check_ajax_referer( 'storesuite-variation-nonce', 'security' );

    $variation_id = absint( $_POST['variation_id'] );

    if ( ! current_user_can( 'manage_woocommerce' ) ) {
        wp_send_json_error( 'Unauthorized' );
    }

    $variation = wc_get_product( $variation_id );
    if ( ! $variation || ! $variation->is_type( 'variation' ) ) {
        wp_send_json_error( 'Invalid variation' );
    }

    $parent_id = $variation->get_parent_id();
    $variation->delete( true );

    \WC_Product_Variable::sync( $parent_id );

    wp_send_json_success( array( 'message' => __( 'Variation removed.', 'storesuite' ) ) );
}
```

---

## Phase 4: Variation Editing — Full CRUD

### Step 4.1 — AJAX handler: `save_variations`

This is the core save handler — processes all variation form fields at once:

```php
public function save_variations() {
    check_ajax_referer( 'storesuite-variation-nonce', 'security' );

    $product_id = absint( $_POST['product_id'] );

    if ( ! current_user_can( 'manage_woocommerce' ) ) {
        wp_send_json_error( 'Unauthorized' );
    }

    $product = wc_get_product( $product_id );
    if ( ! $product || ! $product->is_type( 'variable' ) ) {
        wp_send_json_error( 'Invalid product' );
    }

    if ( ! isset( $_POST['variable_post_id'] ) ) {
        wp_send_json_error( 'No variation data' );
    }

    $variable_post_ids = array_map( 'absint', $_POST['variable_post_id'] );
    $parent_attributes = $product->get_attributes( 'edit' );

    foreach ( $variable_post_ids as $i => $variation_id ) {
        if ( ! $variation_id ) continue;

        $variation = wc_get_product( $variation_id );
        if ( ! $variation ) continue;

        // Enabled status
        $status = isset( $_POST['variable_enabled'][ $i ] ) ? 'publish' : 'private';
        // Can't use set_status on variation pre-save cleanly, so:
        wp_update_post( array( 'ID' => $variation_id, 'post_status' => $status ) );

        // SKU
        if ( isset( $_POST['variable_sku'][ $i ] ) ) {
            $variation->set_sku( wc_clean( $_POST['variable_sku'][ $i ] ) );
        }

        // Prices
        $variation->set_regular_price( isset( $_POST['variable_regular_price'][ $i ] ) ? wc_format_decimal( $_POST['variable_regular_price'][ $i ] ) : '' );
        $variation->set_sale_price( isset( $_POST['variable_sale_price'][ $i ] ) ? wc_format_decimal( $_POST['variable_sale_price'][ $i ] ) : '' );

        // Stock
        $manage_stock = isset( $_POST['variable_manage_stock'][ $i ] );
        $variation->set_manage_stock( $manage_stock );

        if ( $manage_stock ) {
            $variation->set_stock_quantity( isset( $_POST['variable_stock'][ $i ] ) ? wc_stock_amount( $_POST['variable_stock'][ $i ] ) : null );
            $variation->set_backorders( isset( $_POST['variable_backorders'][ $i ] ) ? wc_clean( $_POST['variable_backorders'][ $i ] ) : 'no' );
        }

        $variation->set_stock_status( isset( $_POST['variable_stock_status'][ $i ] ) ? wc_clean( $_POST['variable_stock_status'][ $i ] ) : 'instock' );

        // Virtual / Downloadable
        $is_virtual      = isset( $_POST['variable_is_virtual'][ $i ] );
        $is_downloadable = isset( $_POST['variable_is_downloadable'][ $i ] );
        $variation->set_virtual( $is_virtual );
        $variation->set_downloadable( $is_downloadable );

        // Dimensions (if not virtual)
        if ( ! $is_virtual ) {
            $variation->set_weight( isset( $_POST['variable_weight'][ $i ] ) ? wc_clean( $_POST['variable_weight'][ $i ] ) : '' );
            $variation->set_length( isset( $_POST['variable_length'][ $i ] ) ? wc_clean( $_POST['variable_length'][ $i ] ) : '' );
            $variation->set_width( isset( $_POST['variable_width'][ $i ] ) ? wc_clean( $_POST['variable_width'][ $i ] ) : '' );
            $variation->set_height( isset( $_POST['variable_height'][ $i ] ) ? wc_clean( $_POST['variable_height'][ $i ] ) : '' );
        }

        // Description
        $variation->set_description( isset( $_POST['variable_description'][ $i ] ) ? wp_kses_post( $_POST['variable_description'][ $i ] ) : '' );

        // Image
        $variation->set_image_id( isset( $_POST['upload_image_id'][ $i ] ) ? absint( $_POST['upload_image_id'][ $i ] ) : 0 );

        // Menu order
        $variation->set_menu_order( isset( $_POST['variable_menu_order'][ $i ] ) ? absint( $_POST['variable_menu_order'][ $i ] ) : 0 );

        // Attribute values for this variation
        $variation_attrs = array();
        foreach ( $parent_attributes as $attribute ) {
            if ( ! $attribute->get_variation() ) continue;

            $attr_key = 'attribute_' . sanitize_title( $attribute->get_name() );
            if ( isset( $_POST[ $attr_key ][ $i ] ) ) {
                $variation_attrs[ sanitize_title( $attribute->get_name() ) ] = wc_clean( $_POST[ $attr_key ][ $i ] );
            }
        }
        $variation->set_attributes( $variation_attrs );

        $variation->save();

        do_action( 'storesuite_save_product_variation', $variation_id, $i );
    }

    // CRITICAL: sync parent product
    \WC_Product_Variable::sync( $product_id );

    wp_send_json_success( array( 'message' => __( 'Variations saved.', 'storesuite' ) ) );
}
```

### Step 4.2 — Variation image upload in JS

Reuse WordPress `wp.media` (already enqueued via `wp_enqueue_media()` in Assets.php):

```js
// In product-variation.js
$(document).on('click', '.storesuite-variation-thumb img', function(e) {
    e.preventDefault();
    var $container = $(this).closest('.storesuite-variation-thumb');
    var $input     = $container.find('input[type=hidden]');
    var $img       = $container.find('img');

    var frame = wp.media({
        title: 'Choose variation image',
        button: { text: 'Set image' },
        multiple: false
    });

    frame.on('select', function() {
        var attachment = frame.state().get('selection').first().toJSON();
        $input.val(attachment.id);
        $img.attr('src', attachment.sizes.thumbnail ? attachment.sizes.thumbnail.url : attachment.url);
        $container.closest('.storesuite-variation-row').addClass('variation-needs-update');
    });

    frame.open();
});
```

### Step 4.3 — Change detection in JS

Track which variation rows have been modified:

```js
// Mark rows as "needs update" when any input changes
$(document).on('change input', '#storesuite-variations-container :input', function() {
    $(this).closest('.storesuite-variation-row').addClass('variation-needs-update');
    $('#storesuite-save-variations-btn').prop('disabled', false);
});
```

Save only modified rows:

```js
saveVariations: function() {
    var $needsUpdate = $('#storesuite-variations-container .variation-needs-update');
    if (!$needsUpdate.length) return;

    var formData = new FormData();
    formData.append('action', 'storesuite_save_variations');
    formData.append('security', StoreSuiteVariation.nonce);
    formData.append('product_id', StoreSuiteVariation.product_id);

    // Only serialize inputs inside changed rows
    $needsUpdate.find(':input').each(function() {
        var name = $(this).attr('name');
        if (!name) return;

        if ($(this).is(':checkbox')) {
            if ($(this).is(':checked')) formData.append(name, $(this).val());
        } else if ($(this).is('select[multiple]')) {
            ($(this).val() || []).forEach(function(v) { formData.append(name, v); });
        } else {
            formData.append(name, $(this).val());
        }
    });

    window.StoreSuite.storeSuiteLoader.block($('#storesuite-product-variations'));

    $.ajax({
        url: StoreSuiteVariation.ajax_url,
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(response) {
            if (response.success) {
                $needsUpdate.removeClass('variation-needs-update');
                $('#storesuite-save-variations-btn').prop('disabled', true);
                Swal.fire({ icon: 'success', title: response.data.message, timer: 1500, showConfirmButton: false });
            }
        },
        complete: function() {
            window.StoreSuite.storeSuiteLoader.unblock($('#storesuite-product-variations'));
        }
    });
}
```

---

## Phase 5: Bulk Actions & Generate All Variations

### Step 5.1 — AJAX handler: `generate_variations`

Generates all possible attribute combinations:

```php
public function generate_variations() {
    check_ajax_referer( 'storesuite-variation-nonce', 'security' );

    $product_id = absint( $_POST['product_id'] );

    if ( ! current_user_can( 'manage_woocommerce' ) ) {
        wp_send_json_error( 'Unauthorized' );
    }

    $product = wc_get_product( $product_id );
    if ( ! $product || ! $product->is_type( 'variable' ) ) {
        wp_send_json_error( 'Invalid product' );
    }

    // Get variation attributes with their options
    $attributes     = $product->get_attributes( 'edit' );
    $variation_attrs = array();

    foreach ( $attributes as $attribute ) {
        if ( ! $attribute->get_variation() ) continue;

        $attr_key = sanitize_title( $attribute->get_name() );

        if ( $attribute->is_taxonomy() ) {
            $options = wp_list_pluck( $attribute->get_terms(), 'slug' );
        } else {
            $options = $attribute->get_options();
        }

        if ( ! empty( $options ) ) {
            $variation_attrs[ $attr_key ] = $options;
        }
    }

    if ( empty( $variation_attrs ) ) {
        wp_send_json_error( __( 'No variation attributes found.', 'storesuite' ) );
    }

    // Generate all combinations using cartesian product
    $combinations = array( array() );
    foreach ( $variation_attrs as $attr_key => $values ) {
        $new_combinations = array();
        foreach ( $combinations as $combo ) {
            foreach ( $values as $value ) {
                $new_combinations[] = array_merge( $combo, array( $attr_key => $value ) );
            }
        }
        $combinations = $new_combinations;
    }

    // Create variations, skipping duplicates
    $data_store = \WC_Data_Store::load( 'product' );
    $created    = 0;

    foreach ( $combinations as $combo ) {
        // Check if this combination already exists
        $match_attrs = array();
        foreach ( $combo as $key => $val ) {
            $match_attrs[ 'attribute_' . $key ] = $val;
        }

        $existing = $data_store->find_matching_product_variation( $product, $match_attrs );

        if ( $existing ) continue;

        $variation = new \WC_Product_Variation();
        $variation->set_parent_id( $product_id );
        $variation->set_attributes( $combo );
        $variation->set_status( 'publish' );
        $variation->save();
        $created++;
    }

    \WC_Product_Variable::sync( $product_id );

    wp_send_json_success( array(
        'message' => sprintf(
            /* translators: %d: count */
            _n( '%d variation created.', '%d variations created.', $created, 'storesuite' ),
            $created
        ),
        'created' => $created,
    ) );
}
```

### Step 5.2 — AJAX handler: `bulk_edit_variations`

Handle bulk operations (set all prices, toggle all, delete all):

```php
public function bulk_edit_variations() {
    check_ajax_referer( 'storesuite-variation-nonce', 'security' );

    $product_id  = absint( $_POST['product_id'] );
    $bulk_action = wc_clean( $_POST['bulk_action'] );
    $data        = isset( $_POST['data'] ) ? $_POST['data'] : array();

    if ( ! current_user_can( 'manage_woocommerce' ) ) {
        wp_send_json_error( 'Unauthorized' );
    }

    $product = wc_get_product( $product_id );
    if ( ! $product || ! $product->is_type( 'variable' ) ) {
        wp_send_json_error( 'Invalid product' );
    }

    $children = $product->get_children();

    switch ( $bulk_action ) {
        case 'delete_all':
            foreach ( $children as $child_id ) {
                $child = wc_get_product( $child_id );
                if ( $child ) $child->delete( true );
            }
            break;

        case 'variable_regular_price':
            foreach ( $children as $child_id ) {
                $child = wc_get_product( $child_id );
                if ( $child ) {
                    $child->set_regular_price( wc_format_decimal( $data['value'] ) );
                    $child->save();
                }
            }
            break;

        case 'variable_sale_price':
            foreach ( $children as $child_id ) {
                $child = wc_get_product( $child_id );
                if ( $child ) {
                    $child->set_sale_price( wc_format_decimal( $data['value'] ) );
                    $child->save();
                }
            }
            break;

        case 'toggle_enabled':
            foreach ( $children as $child_id ) {
                $child  = wc_get_product( $child_id );
                $status = $child->get_status() === 'publish' ? 'private' : 'publish';
                wp_update_post( array( 'ID' => $child_id, 'post_status' => $status ) );
            }
            break;

        // Add more cases as needed...
    }

    \WC_Product_Variable::sync( $product_id );

    wp_send_json_success( array( 'message' => __( 'Bulk action completed.', 'storesuite' ) ) );
}
```

### Step 5.3 — JS: wire up toolbar actions

```js
$('#storesuite-do-variation-action').on('click', function() {
    var action = $('#storesuite-variation-actions').val();

    switch (action) {
        case 'add_variation':
            StoreSuiteVariations.addVariation();
            break;

        case 'generate_all':
            Swal.fire({
                title: 'Generate all variations?',
                text: 'This will create variations for all attribute combinations.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Generate'
            }).then(function(result) {
                if (result.isConfirmed) {
                    StoreSuiteVariations.generateAll();
                }
            });
            break;

        case 'delete_all':
            Swal.fire({
                title: 'Delete all variations?',
                text: 'This cannot be undone.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Delete all'
            }).then(function(result) {
                if (result.isConfirmed) {
                    StoreSuiteVariations.bulkAction('delete_all', {});
                }
            });
            break;
    }
});
```

---

## Phase 6: Polish & Edge Cases

### 6.1 — Register and enqueue the new JS

In `includes/Assets.php`, in `register_scripts()`:

```php
$frontend_variation_script = STORESUITE_PLUGIN_ASSET . '/frontend/product-variation.js';

wp_register_script(
    'storesuite_variation_script',
    $frontend_variation_script,
    array( 'jquery', 'storesuite_selectWoo', 'storesuite_sweetalert2_script', 'jquery-ui-sortable' ),
    STORESUITE_PLUGIN_VERSION,
    true
);
```

In `enqueue_front_scripts()`, add localization and enqueue:

```php
wp_enqueue_script( 'storesuite_variation_script' );
wp_localize_script( 'storesuite_variation_script', 'StoreSuiteVariation', array(
    'ajax_url'   => admin_url( 'admin-ajax.php' ),
    'nonce'      => wp_create_nonce( 'storesuite-variation-nonce' ),
    'product_id' => absint( get_query_var( 'edit-product' ) ),
    'per_page'   => 15,
    'i18n'       => array(
        'confirm_remove'   => __( 'Remove this variation?', 'storesuite' ),
        'confirm_delete_all' => __( 'Delete ALL variations? This cannot be undone.', 'storesuite' ),
        'generated'        => __( 'variations created.', 'storesuite' ),
        'no_attributes'    => __( 'Add variation attributes first.', 'storesuite' ),
        'saved'            => __( 'Changes saved.', 'storesuite' ),
    ),
) );
```

### 6.2 — Include templates in `product-form.php`

In `templates/products/product-form.php`, after the Inventory card (before Shipping), include:

```php
<!-- Attributes Section (variable products) -->
<?php
storesuite_get_template_part( 'products/product-attributes', '', array(
    'product'    => $product,
    'product_id' => $product_id,
) );
?>

<!-- Variations Section (variable products) -->
<?php
storesuite_get_template_part( 'products/product-variations', '', array(
    'product'    => $product,
    'product_id' => $product_id,
) );
?>
```

### 6.3 — Instantiate `VariationAjax` class

In `includes/StoreSuite.php` (or wherever classes are bootstrapped), add:

```php
new \PluginizeLab\StoreSuite\Product\VariationAjax();
```

### 6.4 — Handle type toggle for existing form sections

Add CSS classes to existing cards in `product-form.php`:


| Card           | Classes to Add                                               |
| -------------- | ------------------------------------------------------------ |
| Pricing card   | `show_if_simple hide_if_variable`                            |
| Inventory card | Keep visible for both (stock status applies to variable too) |
| Shipping card  | Keep visible for both                                        |
| Others card    | Keep visible for both                                        |


### 6.5 — Save attributes when main form submits too

Ensure attributes are sent along with the main product form submit. The `#storesuite-add-product` form already serializes via `new FormData(this)` — as long as the attribute inputs are inside the `<form>`, they'll be included.

### 6.6 — Initial variation load on page ready

In `product-variation.js`:

```js
$(function() {
    if ($('#storesuite-variations-container').length && StoreSuiteVariation.product_id) {
        StoreSuiteVariations.loadVariations(1);
    }
});
```

### 6.7 — Handle variation show/hide toggles

```js
// Virtual toggle: hide shipping fields
$(document).on('change', '.variable_is_virtual', function() {
    var $row = $(this).closest('.storesuite-variation-row');
    $(this).is(':checked')
        ? $row.find('.hide_if_variation_virtual').slideUp('fast')
        : $row.find('.hide_if_variation_virtual').slideDown('fast');
});

// Manage stock toggle: show stock qty
$(document).on('change', '.variable_manage_stock', function() {
    var $row = $(this).closest('.storesuite-variation-row');
    $(this).is(':checked')
        ? $row.find('.show_if_variation_manage_stock').slideDown('fast')
        : $row.find('.show_if_variation_manage_stock').slideUp('fast');
});
```

### 6.8 — CSS additions

Add to your frontend stylesheet:

```css
/* Variation rows */
.storesuite-variation-row.variation-needs-update {
    border-left: 3px solid #2271b1;
}

.storesuite-attribute-row .storesuite-attribute-header:hover,
.storesuite-variation-row .storesuite-variation-header:hover {
    background: #f9f9f9;
}

.storesuite-no-variation-attrs {
    padding: 20px;
    text-align: center;
    color: #666;
    font-style: italic;
}
```

---

## File Map: What Goes Where

### New files to create


| File                                           | Purpose                                                                                                                    |
| ---------------------------------------------- | -------------------------------------------------------------------------------------------------------------------------- |
| `includes/Product/VariationAjax.php`           | 6 AJAX handlers: save_attributes, load/add/save/remove variations, generate_variations, bulk_edit                          |
| `templates/products/product-attributes.php`    | Attributes section wrapper + toolbar                                                                                       |
| `templates/products/product-attribute-row.php` | Single attribute row (name, values, checkboxes)                                                                            |
| `templates/products/product-variations.php`    | Variations section wrapper + toolbar + pagination                                                                          |
| `templates/products/product-variation-row.php` | Single variation row (prices, stock, shipping, etc.)                                                                       |
| `assets/frontend/product-variation.js`         | JS: attribute add/remove/save, variation load/add/save/remove/generate, pagination, change tracking, image upload, toggles |


### Existing files to modify


| File                                     | What to Change                                                                                                        |
| ---------------------------------------- | --------------------------------------------------------------------------------------------------------------------- |
| `includes/Assets.php`                    | Register + enqueue `product-variation.js`, localize `StoreSuiteVariation` data                                        |
| `includes/StoreSuite.php` (or Main.php)  | Instantiate `VariationAjax` class                                                                                     |
| `includes/Product/ProductController.php` | Add attribute fields to `sanitize_product_data()`                                                                     |
| `includes/Product/ProductManager.php`    | Build `WC_Product_Attribute` objects from POST data in `storesuite_save_product()`                                    |
| `templates/products/product-form.php`    | Add `show_if_*`/`hide_if_*` classes to cards; include `product-attributes.php` and `product-variations.php` templates |
| `assets/frontend/product.js`             | Add `toggleProductType()` method for show/hide sections based on product type                                         |
| Frontend CSS                             | Add variation/attribute styles                                                                                        |


### Zero custom code needed (WC reuse)


| Task                       | WC Function Used                                                    |
| -------------------------- | ------------------------------------------------------------------- |
| List global attributes     | `wc_get_attribute_taxonomies()`                                     |
| Get attribute label        | `wc_attribute_label()`                                              |
| Build attribute objects    | `new WC_Product_Attribute()` + setters                              |
| Create variations          | `new WC_Product_Variation()` + setters + `save()`                   |
| Read variation data        | `$variation->get_regular_price()`, etc.                             |
| Sync parent after changes  | `WC_Product_Variable::sync($id)`                                    |
| Check duplicate variations | `WC_Data_Store::load('product')->find_matching_product_variation()` |
| Stock status options       | `wc_get_product_stock_status_options()`                             |
| Format prices              | `wc_format_localized_price()`, `wc_format_decimal()`                |


---

## Implementation Order (Recommended)

1. **Phase 1** — Product type toggle (30 min) → verifiable immediately
2. **Phase 2** — Attributes UI + save AJAX (2-3 hours) → can test adding/removing attributes
3. **Phase 3** — Variation load + add + remove (2-3 hours) → can see variations appear
4. **Phase 4** — Variation editing + save (3-4 hours) → full CRUD working
5. **Phase 5** — Generate all + bulk actions (1-2 hours) → power features
6. **Phase 6** — Polish, edge cases, CSS (1-2 hours) → production ready

**Total estimated: 10-15 hours**