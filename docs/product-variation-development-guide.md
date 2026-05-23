# How to Build a WooCommerce Product Variation Management System — A Complete Developer Guide

> **What you'll learn:** How to build a full product variation management feature for WooCommerce from scratch — including attribute management, UI shells, AJAX handlers, variation CRUD, bulk actions, image uploads, and drag-and-drop reordering.

---

## Table of Contents

1. [Introduction — What Are Product Variations?](#1-introduction--what-are-product-variations)
2. [The Big Picture — Architecture Overview](#2-the-big-picture--architecture-overview)
3. [Step 0: Product Attributes — The Foundation](#3-step-0-product-attributes--the-foundation)
4. [Step 1: Building the Variations Card Shell](#4-step-1-building-the-variations-card-shell)
5. [Step 2: Loading & Displaying Existing Variations](#5-step-2-loading--displaying-existing-variations)
6. [Step 3: Generating Variations from Attributes](#6-step-3-generating-variations-from-attributes)
7. [Step 4: Saving Variation Data](#7-step-4-saving-variation-data)
8. [Step 5: Adding & Removing Single Variations](#8-step-5-adding--removing-single-variations)
9. [Step 6: Bulk Actions for Variations](#9-step-6-bulk-actions-for-variations)
10. [Step 7: Variation Image Upload](#10-step-7-variation-image-upload)
11. [Step 8: Default Attributes & Drag Reorder](#11-step-8-default-attributes--drag-reorder)
12. [Key Design Decisions & Best Practices](#12-key-design-decisions--best-practices)
13. [File Structure Summary](#13-file-structure-summary)
14. [Wrapping Up](#14-wrapping-up)

---

## 1. Introduction — What Are Product Variations?

If you've ever shopped online, you've seen product variations in action. Think about buying a T-shirt: you pick a **Color** (Red, Blue, Green) and a **Size** (S, M, L, XL). Each combination — like "Red + Large" — is a **product variation**. It has its own price, stock level, SKU, and even its own image.

In WooCommerce, a product that has variations is called a **Variable product**. The parent product holds the shared info (title, description, category), and each child `WC_Product_Variation` object holds the specific details (price, stock, image) for that particular combination.

### What We're Building

We're going to build a **frontend dashboard** that lets shop managers create, edit, and manage product variations **without ever touching wp-admin**. Here's a quick summary of what we'll cover:

| Step | Feature | What It Does |
|------|---------|-------------|
| 0 | Attributes CRUD | Add, save, and remove product attributes (Color, Size, etc.) — **the foundation** |
| 1 | Card Shell | Empty UI wrapper that appears when product type is "Variable" |
| 2 | Load & Display | Fetch existing variations via AJAX, show them in accordion rows |
| 3 | Generate | Create all possible variations from attribute combinations |
| 4 | Save | Persist edits to price, SKU, stock, dimensions, etc. |
| 5 | Add & Remove | Add a blank variation or delete a specific one |
| 6 | Bulk Actions | Set prices/stock on ALL variations at once |
| 7 | Image Upload | Upload per-variation images using WordPress Media Library |
| 8 | Defaults & Reorder | Set default attribute selections + drag-and-drop sorting |

### Prerequisites

Before diving in, make sure you're comfortable with:

- PHP (WordPress plugin development basics)
- JavaScript (jQuery)
- WooCommerce product data model (`WC_Product_Variable`, `WC_Product_Variation`)
- WordPress AJAX (`wp_ajax_*` hooks)
- Basic HTML/CSS for building form UIs

Let's get started!

---

## 2. The Big Picture — Architecture Overview

Before writing any code, let's understand the architecture. Our system has three layers:

```
┌─────────────────────────────────────────┐
│            Frontend (Browser)            │
│   - PHP Templates render the HTML       │
│   - JavaScript handles user actions     │
│   - AJAX calls send data to the server  │
└──────────────────┬──────────────────────┘
                   │ AJAX (POST)
                   ▼
┌─────────────────────────────────────────┐
│         PHP AJAX Controller             │
│   - Validates nonces & permissions      │
│   - Uses WooCommerce API to read/write  │
│   - Returns JSON responses              │
└──────────────────┬──────────────────────┘
                   │
                   ▼
┌─────────────────────────────────────────┐
│        WooCommerce Data Layer           │
│   - WC_Product_Variable (parent)        │
│   - WC_Product_Variation (child)        │
│   - Database (wp_posts, wp_postmeta)    │
└─────────────────────────────────────────┘
```

### Key Design Decisions

Here are some important architectural choices we made early on:

1. **AJAX-loaded variations** — We don't render variations during the initial page load. Instead, we load them asynchronously. This keeps the page fast even for products with hundreds of variations.

2. **Save only modified rows** — We track which variation rows the user actually changed (using a CSS class `.variation-needs-update`). When they click "Save", we only send the dirty rows. This is faster and safer.

3. **Template-based rendering** — We use a custom template system (`storesuite_get_template_part`) so themes can override the markup. This makes the system extensible.

4. **Generation capped at 50** — When auto-generating variations, we cap at 50 per batch. This prevents PHP timeouts for products with many attributes.

5. **Always call `WC_Product_Variable::sync()`** — After every mutation (create, update, delete), we sync the parent product. This recalculates its price range, stock status, and other aggregate data.

---

## 3. Step 0: Product Attributes — The Foundation

Before you can create any variation, you need **attributes**. Attributes define the dimensions of variability — Color, Size, Material, etc. Without them, the variation system has nothing to work with.

Think of it this way: a variation is a specific **combination** of attribute values. "Red + Large" is a combination of the Color attribute (value: Red) and the Size attribute (value: Large). So we need to build the attribute management system first.

WooCommerce supports two types of attributes:

| Type | Example | How Values Work |
|------|---------|----------------|
| **Taxonomy (Global)** | `pa_color`, `pa_size` | Values are WordPress terms. Created once in WooCommerce → Attributes, reusable across all products |
| **Custom (Local)** | `Material`, `Style` | Values are free-text strings stored per-product. Pipe-separated internally (`Cotton \| Polyester \| Silk`) |

### The Attributes Card Template

The attributes section renders above the variations card. It has:

- A list of existing attribute rows (each expandable like an accordion)
- A dropdown to select which attribute to add (global taxonomy or custom)
- "Add attribute" and "Save attributes" buttons

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

// Get all registered global attribute taxonomies
$attribute_taxonomies = wc_get_attribute_taxonomies();
// Get current product's attributes (if editing an existing product)
$product_attributes   = $product ? $product->get_attributes( 'edit' ) : array();
?>

<div class="storesuite-card show_if_variable" id="storesuite-product-attributes">
    <h3>Attributes
        <small>Add attributes for this product (e.g. Color, Size)</small>
    </h3>
    <div class="storesuite-card-content">

        <!-- Existing attribute rows render here -->
        <div id="storesuite-attributes-list">
            <?php
            $i = 0;
            if ( ! empty( $product_attributes ) ) {
                foreach ( $product_attributes as $attribute ) {
                    storesuite_get_template_part( 'products/product-attribute-row', '', array(
                        'attribute'  => $attribute,
                        'i'          => $i,
                        'product'    => $product,
                        'product_id' => $product_id,
                    ) );
                    $i++;
                }
            }
            ?>
        </div>

        <!-- Toolbar: select attribute + action buttons -->
        <div class="storesuite-attribute-toolbar">
            <div class="row">
                <div class="col-md-6">
                    <select id="storesuite-add-attribute-select">
                        <option value="">Custom attribute</option>
                        <?php foreach ( $attribute_taxonomies as $tax ) :
                            $tax_name = wc_attribute_taxonomy_name( $tax->attribute_name );
                            // Skip attributes already added to this product
                            if ( isset( $product_attributes[ $tax_name ] ) ) continue;
                        ?>
                            <option value="<?php echo esc_attr( $tax_name ); ?>">
                                <?php echo esc_html( $tax->attribute_label ); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <button type="button" id="storesuite-add-attribute-btn">Add attribute</button>
                    <button type="button" id="storesuite-save-attributes-btn">Save attributes</button>
                </div>
            </div>
        </div>
    </div>
</div>
```

**Key detail:** Notice that already-added taxonomy attributes are skipped in the dropdown. This prevents duplicate attributes on the same product.

### The Attribute Row Template

Each attribute is rendered as a collapsible row. This is where the user configures the attribute name, its values, visibility on the product page, and — most importantly — whether it's **"Used for variations"**.

```php
<?php
/**
 * Single product attribute row template.
 *
 * @var WC_Product_Attribute $attribute
 * @var int                  $i          Index
 * @var WC_Product|null      $product
 * @var int                  $product_id
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$attr_name    = $attribute->get_name();
$attr_label   = wc_attribute_label( $attr_name );
$is_taxonomy  = $attribute->is_taxonomy();
$is_visible   = $attribute->get_visible();
$is_variation = $attribute->get_variation();
$position     = $attribute->get_position();
$attr_options = $attribute->get_options();
?>

<div class="storesuite-attribute-row" data-index="<?php echo esc_attr( $i ); ?>">
    <!-- Header (click to expand/collapse) -->
    <div class="storesuite-attribute-header">
        <strong><?php echo esc_html( $attr_label ?: 'Attribute' ); ?></strong>
        <span>
            <a href="#" class="storesuite-toggle-attribute">▾</a>
            <a href="#" class="storesuite-remove-attribute">✕</a>
        </span>
    </div>

    <!-- Body (collapsible) -->
    <div class="storesuite-attribute-body" style="display:none;">
        <div class="row">
            <!-- Left column: Name + checkboxes -->
            <div class="col-md-6">
                <div class="storesuite-form-group">
                    <label>Name</label>
                    <?php if ( $is_taxonomy ) : ?>
                        <!-- Taxonomy name is read-only -->
                        <strong><?php echo esc_html( $attr_label ); ?></strong>
                        <input type="hidden"
                               name="attribute_names[<?php echo esc_attr( $i ); ?>]"
                               value="<?php echo esc_attr( $attr_name ); ?>">
                    <?php else : ?>
                        <!-- Custom attribute: editable name -->
                        <input type="text"
                               name="attribute_names[<?php echo esc_attr( $i ); ?>]"
                               value="<?php echo esc_attr( $attr_name ); ?>"
                               placeholder="e.g. Material">
                    <?php endif; ?>

                    <input type="hidden"
                           name="attribute_position[<?php echo esc_attr( $i ); ?>]"
                           value="<?php echo esc_attr( $position ); ?>">
                </div>

                <!-- Visible on product page? -->
                <div class="storesuite-form-group">
                    <input type="checkbox"
                           name="attribute_visibility[<?php echo esc_attr( $i ); ?>]"
                           value="1" <?php checked( $is_visible ); ?>>
                    <label>Visible on the product page</label>
                </div>

                <!-- THE KEY CHECKBOX: Used for variations? -->
                <div class="storesuite-form-group">
                    <input type="checkbox"
                           name="attribute_variation[<?php echo esc_attr( $i ); ?>]"
                           value="1" <?php checked( $is_variation ); ?>>
                    <label>Used for variations</label>
                </div>
            </div>

            <!-- Right column: Attribute values -->
            <div class="col-md-6">
                <div class="storesuite-form-group">
                    <label>Value(s)</label>
                    <?php if ( $is_taxonomy ) : ?>
                        <?php
                        // For taxonomy attributes, show all terms as a multi-select
                        $all_terms    = get_terms( array(
                            'taxonomy'   => $attr_name,
                            'orderby'    => 'name',
                            'hide_empty' => false,
                        ) );
                        $selected_ids = $attr_options; // These are term IDs
                        ?>
                        <select multiple
                                name="attribute_values[<?php echo esc_attr( $i ); ?>][]"
                                class="storesuite-select2"
                                data-placeholder="Select terms">
                            <?php if ( ! is_wp_error( $all_terms ) ) : ?>
                                <?php foreach ( $all_terms as $term ) : ?>
                                    <option value="<?php echo esc_attr( $term->term_id ); ?>"
                                        <?php selected(
                                            in_array( $term->term_id, $selected_ids, true )
                                        ); ?>>
                                        <?php echo esc_html( $term->name ); ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                        <div>
                            <a href="#" class="storesuite-select-all-terms">Select all</a>
                            <a href="#" class="storesuite-select-no-terms">Select none</a>
                        </div>
                    <?php else : ?>
                        <!-- Custom attributes: taggable multi-select -->
                        <select multiple
                                name="attribute_values[<?php echo esc_attr( $i ); ?>][]"
                                class="storesuite-select2"
                                data-placeholder="Type & select custom values"
                                data-tags="true"
                                data-token-separators='["|"]'>
                            <?php foreach ( $attr_options as $option ) : ?>
                                <option value="<?php echo esc_attr( $option ); ?>" selected>
                                    <?php echo esc_html( $option ); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
```

**The critical checkbox:** The `attribute_variation` checkbox with label "Used for variations" is what bridges attributes and variations. When checked, WooCommerce knows this attribute should be used to create variation combinations. Without this checkbox being checked, the attribute just shows on the product page as informational text — it won't generate any variations.

### Add Attribute AJAX Handler (PHP)

When the user clicks "Add attribute", we send an AJAX request to create an empty attribute row:

```php
public function storesuite_ajax_add_attribute() {
    if ( ! check_ajax_referer( 'add-attribute', 'security', false ) ) {
        wp_send_json_error( array( 'message' => 'Invalid nonce.' ) );
    }

    $taxonomy     = isset( $_POST['taxonomy'] )
        ? sanitize_text_field( wp_unslash( $_POST['taxonomy'] ) ) : '';
    $index        = isset( $_POST['i'] ) ? absint( $_POST['i'] ) : 0;
    $product_id   = isset( $_POST['product_id'] ) ? absint( $_POST['product_id'] ) : 0;
    $product_type = isset( $_POST['product_type'] )
        ? wc_clean( wp_unslash( $_POST['product_type'] ) ) : 'simple';

    // Build a WC_Product_Attribute object with sensible defaults
    $attribute = new WC_Product_Attribute();
    $attribute->set_id( wc_attribute_taxonomy_id_by_name( $taxonomy ) );
    $attribute->set_name( $taxonomy );
    $attribute->set_visible( true );

    // Auto-check "Used for variations" when product type is Variable
    $attribute->set_variation( 'variable' === $product_type ? 1 : 0 );

    // Render the attribute row template and return the HTML
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
        wp_send_json_error( array( 'message' => 'Could not generate attribute row.' ) );
    }

    wp_send_json_success( array(
        'html'    => $html,
        'message' => 'Attribute added.',
    ) );
}
```

### Save Attributes AJAX Handler (PHP)

This is where it gets interesting. Saving attributes uses WooCommerce's built-in `WC_Meta_Box_Product_Data::prepare_attributes()` method, which handles all the complex normalization for us. But there's a **gotcha** with custom attributes you need to know about:

```php
public function storesuite_ajax_save_attributes() {
    if ( ! check_ajax_referer( 'save-attributes', 'security', false ) ) {
        wp_send_json_error( array( 'message' => 'Invalid security token.' ) );
    }

    $product_id   = absint( wp_unslash( $_POST['product_id'] ) );
    $product      = wc_get_product( $product_id );
    $product_type = isset( $_POST['product_type'] )
        ? wc_clean( wp_unslash( $_POST['product_type'] ) ) : 'simple';

    if ( ! $product ) {
        wp_send_json_error( array( 'message' => 'Product not found.' ) );
    }

    $attribute_names  = isset( $_POST['attribute_names'] )
        ? stripslashes_deep( (array) $_POST['attribute_names'] ) : array();
    $attribute_values = isset( $_POST['attribute_values'] )
        ? stripslashes_deep( (array) $_POST['attribute_values'] ) : array();

    // ⚠️ CRITICAL GOTCHA: Custom (non-taxonomy) attributes
    // WooCommerce expects custom attribute values as a PIPE-SEPARATED STRING,
    // not as an array. If we send an array, prepare_attributes() treats them
    // as term IDs and casts them to integers (which gives you "0" for text values).
    foreach ( $attribute_names as $index => $name ) {
        if ( empty( $name ) || ! isset( $attribute_values[ $index ] ) ) {
            continue;
        }

        // Taxonomy attributes start with "pa_" — skip those
        if ( 0 === strpos( $name, 'pa_' ) ) {
            continue;
        }

        // Convert array values to pipe-separated string for custom attributes
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

    // Let WooCommerce do the heavy lifting
    $data = array(
        'attribute_names'      => $attribute_names,
        'attribute_values'     => $attribute_values,
        'attribute_visibility' => isset( $_POST['attribute_visibility'] )
            ? stripslashes_deep( (array) $_POST['attribute_visibility'] ) : array(),
        'attribute_variation'  => isset( $_POST['attribute_variation'] )
            ? stripslashes_deep( (array) $_POST['attribute_variation'] ) : array(),
        'attribute_position'   => isset( $_POST['attribute_position'] )
            ? stripslashes_deep( (array) $_POST['attribute_position'] ) : array(),
    );

    // This WooCommerce method normalizes everything into WC_Product_Attribute objects
    $attributes = WC_Meta_Box_Product_Data::prepare_attributes( $data );

    // Re-instantiate the product with the correct class for the product type
    $classname = WC_Product_Factory::get_product_classname( $product_id, $product_type );
    $product   = new $classname( $product_id );
    $product->set_attributes( $attributes );
    $product->save();

    wp_send_json_success( array(
        'message' => 'Attributes successfully saved.',
    ) );
}
```

> **Why the pipe-separated string?** This is one of those WooCommerce internals that will trip you up. For **taxonomy** attributes, `attribute_values` contains term IDs (integers). For **custom** attributes, WooCommerce expects a single string like `"Cotton | Polyester | Silk"`. If you pass an array of strings, `prepare_attributes()` tries to cast them to integers (since it assumes they're term IDs), and you get `0` for every value. The fix is to join custom values with ` | ` before passing them in.

### Attribute JavaScript — Add, Save, Remove

Here's the JavaScript that handles attribute CRUD operations:

```javascript
var StoreSuiteAttributes = {
    index: 0, // Tracks attribute row count for naming

    init: function() {
        // Start index at the number of existing attribute rows
        this.index = $('#storesuite-attributes-list .storesuite-attribute-row').length;

        $(document).on('click', '#storesuite-add-attribute-btn',
            this.addAttribute.bind(this));
        $(document).on('click', '#storesuite-save-attributes-btn',
            this.saveAttributes.bind(this));
        $(document).on('click', '.storesuite-remove-attribute',
            this.removeAttribute);
        $(document).on('click', '.storesuite-toggle-attribute',
            this.toggleAttribute);
        $(document).on('click', '.storesuite-select-all-terms',
            this.selectAllTerms);
        $(document).on('click', '.storesuite-select-no-terms',
            this.selectNoTerms);
    },

    addAttribute: function() {
        var taxonomy = $('#storesuite-add-attribute-select').val();
        var index    = this.index++;

        $.ajax({
            url:  StoreSuiteVariation.ajax_url,
            type: 'POST',
            data: {
                action:       'storesuite_add_attribute',
                security:     StoreSuiteVariation.add_attribute_nonce,
                product_id:   StoreSuiteVariation.product_id || 0,
                product_type: $('#post_type').val(),
                taxonomy:     taxonomy,
                i:            index,
            },
            success: function( response ) {
                if ( response && response.success && response.data.html ) {
                    // Append the new attribute row
                    $('#storesuite-attributes-list').append( response.data.html );

                    // Initialize Select2 on the new row's selects
                    $('.storesuite-select2').filter(':not(.enhanced)').each(function() {
                        $(this).selectWoo({
                            allowClear: true,
                            placeholder: $(this).data('placeholder') || '',
                            tags: $(this).data('tags') || false,
                            tokenSeparators: ['|'],
                            width: '100%',
                        }).addClass('enhanced');
                    });

                    // Remove the used taxonomy from the dropdown
                    if ( taxonomy ) {
                        $('#storesuite-add-attribute-select option[value="'
                            + taxonomy + '"]').remove();
                    }
                }
            },
        });
    },

    saveAttributes: function() {
        var formData = new FormData();
        formData.append('action', 'storesuite_save_attributes');
        formData.append('security', StoreSuiteVariation.save_attributes_nonce);
        formData.append('product_id', StoreSuiteVariation.product_id);
        formData.append('product_type', $('#post_type').val());

        // Collect all attribute fields into FormData
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

        $.ajax({
            url: StoreSuiteVariation.ajax_url,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        text: response.data.message,
                        timer: 1500,
                    });
                    // Reload variations — attributes may have changed
                    StoreSuiteVariations.reload();
                }
            },
        });
    },

    removeAttribute: function(e) {
        e.preventDefault();
        // Simply remove the row from the DOM
        // The next "Save attributes" call will persist the removal
        $(this).closest('.storesuite-attribute-row')
               .slideUp(200, function() { $(this).remove(); });
    },

    toggleAttribute: function(e) {
        e.preventDefault();
        $(this).closest('.storesuite-attribute-row')
               .find('.storesuite-attribute-body')
               .slideToggle(200);
    },

    selectAllTerms: function(e) {
        e.preventDefault();
        var $select = $(this).closest('.storesuite-form-group').find('select');
        $select.find('option').prop('selected', true);
        $select.trigger('change'); // Update Select2 UI
    },

    selectNoTerms: function(e) {
        e.preventDefault();
        var $select = $(this).closest('.storesuite-form-group').find('select');
        $select.find('option').prop('selected', false);
        $select.trigger('change');
    },
};

// Initialize!
StoreSuiteAttributes.init();
```

### The Attribute → Variation Flow

Here's how attributes and variations connect — understanding this flow is key:

```
1. User clicks "Add attribute" → Attribute row appears
   ↓
2. User selects values (e.g. Red, Blue, Green for Color)
   ↓
3. User checks ✅ "Used for variations"
   ↓
4. User clicks "Save attributes" → AJAX saves to database
   ↓
5. Variations section detects variation attributes now exist
   → Guard message disappears, toolbar appears
   ↓
6. User can now click "Create variations from all attributes"
   → Generates Red, Blue, Green variations
```

Without Step 3 (checking "Used for variations"), the attribute is purely informational — it shows on the product page but doesn't generate any product variations.

### How to Test Step 0

1. Go to your frontend product editor and switch to "Variable" product type
2. In the Attributes section, select "Color" from the dropdown → click "Add attribute"
3. **Expected**: A new accordion row appears for Color
4. Expand it → select some color terms (Red, Blue, Green)
5. Check "Used for variations" ✅
6. Click "Save attributes"
7. **Expected**: Attributes saved, and the Variations section below now shows the toolbar (no more "add attributes first" message)
8. Click the ✕ button on an attribute row → click "Save attributes"
9. **Expected**: That attribute is removed

---

## 4. Step 1: Building the Variations Card Shell

The first thing we need is a visible container on the product edit form. This is the "shell" — it doesn't do anything functional yet, but it gives users a place to interact with variations.

### What the Shell Includes

- A card wrapper that only shows when the product type is "Variable"
- Guard messages for edge cases (product not saved yet, no variation attributes)
- A toolbar with a dropdown for actions (add, generate, bulk edit, delete)
- Go + Save buttons
- Empty containers for variations and pagination

### The Template (PHP)

Create a file called `product-variations.php` in your templates directory:

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

// Check which attributes are marked "Used for variations"
$variation_attributes = array();
if ( $product && $product->is_type( 'variable' ) ) {
    foreach ( $product->get_attributes( 'edit' ) as $attribute ) {
        if ( $attribute->get_variation() ) {
            $variation_attributes[] = $attribute;
        }
    }
}
?>

<div class="storesuite-card show_if_variable" id="storesuite-product-variations">
    <h3>Variations
        <small>Manage product variations (e.g. different sizes, colors)</small>
    </h3>
    <div class="storesuite-card-content">

        <?php if ( ! $product_id ) : ?>
            <!-- Guard: Product needs to be saved first -->
            <div class="notice notice-info">
                <p>Save the product first to manage variations.</p>
            </div>

        <?php elseif ( empty( $variation_attributes ) ) : ?>
            <!-- Guard: No variation attributes configured -->
            <div class="notice notice-info">
                <p>Before adding variations, add attributes and mark them
                   as "Used for variations" in the Attributes section above.</p>
            </div>

        <?php else : ?>
            <!-- Toolbar with action dropdown -->
            <div class="variation-toolbar">
                <select id="storesuite-variation-actions">
                    <optgroup label="Add">
                        <option value="add_variation">Add variation</option>
                        <option value="generate_variations">
                            Create variations from all attributes
                        </option>
                    </optgroup>
                    <optgroup label="Bulk actions">
                        <option value="variable_regular_price">Set regular prices</option>
                        <option value="variable_sale_price">Set sale prices</option>
                        <option value="variable_stock_status">Set stock status</option>
                        <option value="toggle_enabled">Toggle "Enabled"</option>
                    </optgroup>
                    <optgroup label="Delete">
                        <option value="delete_all">Delete all variations</option>
                    </optgroup>
                </select>

                <button type="button" id="storesuite-do-variation-action">Go</button>
                <button type="button" id="storesuite-save-variations-btn" disabled>
                    Save changes
                </button>
            </div>

            <!-- Variations will be loaded here via AJAX -->
            <div id="storesuite-variations-container"
                data-product-id="<?php echo esc_attr( $product_id ); ?>"
                data-per-page="15"
                data-page="1"
                data-total="0">
            </div>

            <!-- Pagination will be built here by JavaScript -->
            <div class="storesuite-variation-pagination"></div>

        <?php endif; ?>
    </div>
</div>
```

### Why the `show_if_variable` Class?

WooCommerce uses CSS classes like `show_if_variable` and `hide_if_simple` to toggle sections based on the product type dropdown. When a user switches the product type to "Variable", jQuery shows elements with `show_if_variable` and hides them otherwise. This keeps the UI clean — simple products don't need a variations panel.

### Including the Template in Your Product Form

In your main product form template, add this line after the attributes section:

```php
<?php
storesuite_get_template_part(
    'products/product-variations',
    '',
    array(
        'product'    => $product,
        'product_id' => $product_id,
    )
);
?>
```

### How to Test Step 1

1. Go to your frontend product editor
2. Switch the product type dropdown to "Variable"
3. **Expected**: The variations card appears below attributes (empty, buttons do nothing yet)
4. Switch back to "Simple"
5. **Expected**: The variations card hides

---

## 5. Step 2: Loading & Displaying Existing Variations

Now let's make the shell actually useful. We need to:

1. Create a PHP AJAX handler that fetches a product's variations
2. Create a template for each variation row (accordion-style)
3. Write JavaScript to call the AJAX endpoint and display results
4. Add pagination for products with many variations

### The Variation Row Template (PHP)

Each variation is displayed as an accordion row. The header shows a thumbnail and attribute selects. The body (hidden by default) contains all the editable fields.

```php
<?php
/**
 * Single product variation row template (accordion).
 *
 * @var WC_Product_Variation $variation
 * @var int                  $variation_id
 * @var int                  $loop          Index for array-named inputs
 * @var WC_Product_Variable  $parent
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$variation_image = $variation->get_image_id()
    ? wp_get_attachment_image_src( $variation->get_image_id(), 'thumbnail' )
    : null;
$variation_thumb = $variation_image ? $variation_image[0] : wc_placeholder_img_src( 'thumbnail' );
$parent_attributes = $parent->get_attributes( 'edit' );
$variation_attrs   = $variation->get_attributes();
?>

<div class="storesuite-variation-row" data-variation-id="<?php echo esc_attr( $variation_id ); ?>">
    <!-- Hidden fields for tracking -->
    <input type="hidden"
           name="variable_post_id[<?php echo esc_attr( $loop ); ?>]"
           value="<?php echo esc_attr( $variation_id ); ?>">
    <input type="hidden"
           name="variable_menu_order[<?php echo esc_attr( $loop ); ?>]"
           value="<?php echo esc_attr( $variation->get_menu_order() ); ?>">

    <!-- Header: thumbnail + attribute selects + actions -->
    <div class="storesuite-variation-header">
        <div class="storesuite-variation-thumb">
            <img src="<?php echo esc_url( $variation_thumb ); ?>" width="40" height="40">
        </div>

        <div class="storesuite-variation-attrs">
            <?php foreach ( $parent_attributes as $attribute ) :
                if ( ! $attribute->get_variation() ) continue;

                $attr_name   = $attribute->get_name();
                $attr_label  = wc_attribute_label( $attr_name );
                $attr_key    = sanitize_title( $attr_name );
                $current_val = $variation_attrs[ $attr_key ] ?? '';

                // Get the options (taxonomy terms or custom values)
                if ( $attribute->is_taxonomy() ) {
                    $terms = get_terms( array(
                        'taxonomy'   => $attr_name,
                        'orderby'    => 'name',
                        'hide_empty' => false,
                    ) );
                } else {
                    $terms = $attribute->get_options();
                }
            ?>
                <select name="attribute_<?php echo esc_attr( $attr_key ); ?>[<?php echo esc_attr( $loop ); ?>]">
                    <option value="">Any <?php echo esc_html( $attr_label ); ?>…</option>
                    <?php if ( $attribute->is_taxonomy() && ! is_wp_error( $terms ) ) : ?>
                        <?php foreach ( $terms as $term ) : ?>
                            <option value="<?php echo esc_attr( $term->slug ); ?>"
                                <?php selected( $current_val, $term->slug ); ?>>
                                <?php echo esc_html( $term->name ); ?>
                            </option>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <?php foreach ( $terms as $option ) : ?>
                            <option value="<?php echo esc_attr( $option ); ?>"
                                <?php selected( $current_val, $option ); ?>>
                                <?php echo esc_html( $option ); ?>
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            <?php endforeach; ?>
        </div>

        <span class="storesuite-variation-actions-header">
            <a href="#" class="storesuite-toggle-variation">&#9662;</a>
            <a href="#" class="storesuite-remove-variation"
               data-variation-id="<?php echo esc_attr( $variation_id ); ?>">
                &times;
            </a>
        </span>
    </div>

    <!-- Body (collapsed by default) -->
    <div class="storesuite-variation-body" style="display:none;">
        <!-- Checkboxes: Enabled, Virtual, Downloadable, Manage stock -->
        <div class="row">
            <div class="col-md-3">
                <input type="checkbox"
                       name="variable_enabled[<?php echo esc_attr( $loop ); ?>]"
                       value="1"
                       <?php checked( 'publish' === $variation->get_status() ); ?>>
                <label>Enabled</label>
            </div>
            <div class="col-md-3">
                <input type="checkbox" class="variable_is_virtual"
                       name="variable_is_virtual[<?php echo esc_attr( $loop ); ?>]"
                       value="1"
                       <?php checked( $variation->get_virtual() ); ?>>
                <label>Virtual</label>
            </div>
            <div class="col-md-3">
                <input type="checkbox"
                       name="variable_is_downloadable[<?php echo esc_attr( $loop ); ?>]"
                       value="1"
                       <?php checked( $variation->get_downloadable() ); ?>>
                <label>Downloadable</label>
            </div>
            <div class="col-md-3">
                <input type="checkbox" class="variable_manage_stock"
                       name="variable_manage_stock[<?php echo esc_attr( $loop ); ?>]"
                       value="1"
                       <?php checked( $variation->get_manage_stock() ); ?>>
                <label>Manage stock</label>
            </div>
        </div>

        <!-- Price, SKU, Stock fields -->
        <div class="row">
            <div class="col-md-5">
                <label>Regular price (<?php echo get_woocommerce_currency_symbol(); ?>)</label>
                <input type="text"
                       name="variable_regular_price[<?php echo esc_attr( $loop ); ?>]"
                       value="<?php echo esc_attr( $variation->get_regular_price() ); ?>">
            </div>
            <div class="col-md-5">
                <label>Sale price (<?php echo get_woocommerce_currency_symbol(); ?>)</label>
                <input type="text"
                       name="variable_sale_price[<?php echo esc_attr( $loop ); ?>]"
                       value="<?php echo esc_attr( $variation->get_sale_price() ); ?>">
            </div>
        </div>

        <!-- SKU + Stock Status + Stock Qty -->
        <div class="row">
            <div class="col-md-4">
                <label>SKU</label>
                <input type="text"
                       name="variable_sku[<?php echo esc_attr( $loop ); ?>]"
                       value="<?php echo esc_attr( $variation->get_sku() ); ?>">
            </div>
            <div class="col-md-4">
                <label>Stock status</label>
                <select name="variable_stock_status[<?php echo esc_attr( $loop ); ?>]">
                    <option value="instock" <?php selected( $variation->get_stock_status(), 'instock' ); ?>>
                        In stock
                    </option>
                    <option value="outofstock" <?php selected( $variation->get_stock_status(), 'outofstock' ); ?>>
                        Out of stock
                    </option>
                    <option value="onbackorder" <?php selected( $variation->get_stock_status(), 'onbackorder' ); ?>>
                        On backorder
                    </option>
                </select>
            </div>
            <div class="col-md-4 show_if_variation_manage_stock"
                 <?php echo $variation->get_manage_stock() ? '' : 'style="display:none;"'; ?>>
                <label>Stock qty</label>
                <input type="number"
                       name="variable_stock_qty[<?php echo esc_attr( $loop ); ?>]"
                       value="<?php echo esc_attr( $variation->get_stock_quantity() ); ?>">
            </div>
        </div>

        <!-- Shipping: Weight & Dimensions (hidden when virtual) -->
        <div class="row hide_if_variation_virtual"
             <?php echo $variation->get_virtual() ? 'style="display:none;"' : ''; ?>>
            <div class="col-md-3">
                <label>Weight</label>
                <input type="text"
                       name="variable_weight[<?php echo esc_attr( $loop ); ?>]"
                       value="<?php echo esc_attr( $variation->get_weight() ); ?>">
            </div>
            <div class="col-md-3">
                <label>Length</label>
                <input type="text"
                       name="variable_length[<?php echo esc_attr( $loop ); ?>]"
                       value="<?php echo esc_attr( $variation->get_length() ); ?>">
            </div>
            <div class="col-md-3">
                <label>Width</label>
                <input type="text"
                       name="variable_width[<?php echo esc_attr( $loop ); ?>]"
                       value="<?php echo esc_attr( $variation->get_width() ); ?>">
            </div>
            <div class="col-md-3">
                <label>Height</label>
                <input type="text"
                       name="variable_height[<?php echo esc_attr( $loop ); ?>]"
                       value="<?php echo esc_attr( $variation->get_height() ); ?>">
            </div>
        </div>

        <!-- Description -->
        <div class="row">
            <div class="col-md-12">
                <label>Description</label>
                <textarea name="variable_description[<?php echo esc_attr( $loop ); ?>]"
                          rows="2"><?php echo esc_textarea( $variation->get_description() ); ?></textarea>
            </div>
        </div>
    </div>
</div>
```

### Understanding Array-Named Inputs

Notice how all the input names use `[<?php echo $loop; ?>]`? For example:

```html
<input name="variable_regular_price[0]" value="29.99">
<input name="variable_regular_price[1]" value="39.99">
<input name="variable_regular_price[2]" value="49.99">
```

When PHP receives this, it automatically converts them into an array:

```php
$_POST['variable_regular_price'] = array(
    0 => '29.99',
    1 => '39.99',
    2 => '49.99',
);
```

The `$loop` variable is the index. On the server side, we use `variable_post_id[$loop]` to know which variation ID corresponds to which data.

### The AJAX Handler (PHP)

Now let's write the server-side handler that loads variations:

```php
/**
 * Load product variations via AJAX with pagination.
 */
public function load_variations() {
    // 1. Security check — always verify the nonce!
    if ( ! check_ajax_referer( 'storesuite-variation-nonce', 'security', false ) ) {
        wp_send_json_error( array( 'message' => 'Invalid security token.' ) );
    }

    // 2. Permission check — only product editors allowed
    if ( ! current_user_can( 'edit_products' ) || ! isset( $_POST['product_id'] ) ) {
        wp_send_json_error( array( 'message' => 'Invalid request.' ) );
    }

    // 3. Get the product and validate it's a variable type
    $product_id = absint( wp_unslash( $_POST['product_id'] ) );
    $page       = isset( $_POST['page'] ) ? absint( $_POST['page'] ) : 1;
    $per_page   = isset( $_POST['per_page'] ) ? absint( $_POST['per_page'] ) : 15;
    $product    = wc_get_product( $product_id );

    if ( ! $product || ! $product->is_type( 'variable' ) ) {
        wp_send_json_error( array( 'message' => 'Invalid variable product.' ) );
    }

    // 4. Paginate the children (variation IDs)
    $children    = $product->get_children();
    $total       = count( $children );
    $total_pages = max( 1, (int) ceil( $total / $per_page ) );
    $page        = min( $page, $total_pages );
    $offset      = ( $page - 1 ) * $per_page;
    $page_ids    = array_slice( $children, $offset, $per_page );

    // 5. Render each variation row using the template
    ob_start();
    foreach ( $page_ids as $loop => $child_id ) {
        $variation = wc_get_product( $child_id );
        if ( ! $variation || ! $variation->is_type( 'variation' ) ) {
            continue;
        }

        storesuite_get_template_part(
            'products/product-variation-row',
            '',
            array(
                'variation'    => $variation,
                'variation_id' => $child_id,
                'loop'         => $offset + $loop, // Global index for naming
                'parent'       => $product,
            )
        );
    }
    $html = ob_get_clean();

    // 6. Return everything the frontend needs
    wp_send_json_success(
        array(
            'html'        => $html,
            'total'       => $total,
            'total_pages' => $total_pages,
            'page'        => $page,
        )
    );
}
```

**Important:** Notice how we use `$offset + $loop` for the loop index? This ensures that even on page 2, the input names don't clash with page 1. Variation on position 15 gets index `15`, not `0`.

### The JavaScript (Frontend)

Here's the JS that calls our AJAX endpoint and displays the results:

```javascript
var StoreSuiteVariations = {
    page: 1,

    init: function() {
        // Bind event handlers
        $(document).on('click', '.storesuite-toggle-variation', this.toggleVariation);
        $(document).on('click', '.storesuite-variation-pagination a[data-page]',
            this.onPaginationClick.bind(this));

        // Auto-load on edit page if product is variable
        if ( StoreSuiteVariation.product_id > 0
             && $('#post_type').val() === 'variable' ) {
            this.reload();
        }

        // Reload when product type changes to variable
        $(document).on('change', '#post_type', function() {
            if ( $(this).val() === 'variable'
                 && StoreSuiteVariation.product_id > 0 ) {
                StoreSuiteVariations.reload();
            }
        });
    },

    reload: function() {
        this.loadPage( this.page );
    },

    loadPage: function( page ) {
        var self       = this;
        var $container = $('#storesuite-variations-container');

        if ( ! $container.length ) return;

        // Show loading spinner
        window.StoreSuite.storeSuiteLoader.block( $( '.my-storesuite-wrapper' ) );

        $.ajax({
            url:  StoreSuiteVariation.ajax_url,
            type: 'POST',
            dataType: 'json',
            data: {
                action:     'storesuite_load_variations',
                security:   StoreSuiteVariation.nonce,
                product_id: StoreSuiteVariation.product_id,
                page:       page,
                per_page:   StoreSuiteVariation.per_page,
            },
            success: function( response ) {
                if ( response && response.success ) {
                    var data = response.data;
                    self.page = data.page;
                    $container.html( data.html || '<p>No variations found.</p>' );
                    self.buildPagination(
                        data.total,
                        StoreSuiteVariation.per_page,
                        data.page,
                        data.total_pages
                    );
                }
            },
            complete: function() {
                window.StoreSuite.storeSuiteLoader.unblock( $( '.my-storesuite-wrapper' ) );
            },
        });
    },

    buildPagination: function( total, perPage, currentPage, totalPages ) {
        var $pagination = $('.storesuite-variation-pagination');
        $pagination.empty();

        if ( totalPages <= 1 ) return;

        var html = '<span>' + total + ' variations</span> ';

        // Previous page link
        if ( currentPage > 1 ) {
            html += '<a href="#" data-page="' + (currentPage - 1) + '">&laquo;</a> ';
        }

        // Page numbers
        for ( var i = 1; i <= totalPages; i++ ) {
            if ( i === currentPage ) {
                html += '<span class="current">' + i + '</span> ';
            } else {
                html += '<a href="#" data-page="' + i + '">' + i + '</a> ';
            }
        }

        // Next page link
        if ( currentPage < totalPages ) {
            html += '<a href="#" data-page="' + (currentPage + 1) + '">&raquo;</a>';
        }

        $pagination.html( html );
    },

    toggleVariation: function( e ) {
        e.preventDefault();
        $(this).closest('.storesuite-variation-row')
               .find('.storesuite-variation-body')
               .slideToggle(200);
    },

    onPaginationClick: function( e ) {
        e.preventDefault();
        var page = $(e.currentTarget).data('page');
        if ( page ) this.loadPage( page );
    },
};
```

### Registering the AJAX Action

In your plugin's main class or controller:

```php
class VariationAjax {
    public function __construct() {
        add_action( 'wp_ajax_storesuite_load_variations',
                    array( $this, 'load_variations' ) );
    }
}
```

### Passing Data from PHP to JavaScript

You'll need to localize your script so JavaScript knows where to send AJAX requests:

```php
// In your Assets.php or wherever you enqueue scripts
wp_localize_script( 'storesuite-product-variation', 'StoreSuiteVariation', array(
    'ajax_url'   => admin_url( 'admin-ajax.php' ),
    'nonce'      => wp_create_nonce( 'storesuite-variation-nonce' ),
    'product_id' => $product_id,
    'per_page'   => 15,
    'i18n'       => array(
        'no_variations' => __( 'No variations yet.', 'storesuite' ),
        // ... more strings
    ),
));
```

---

## 6. Step 3: Generating Variations from Attributes

This is one of the coolest features. When a product has attributes like **Color** (Red, Blue) and **Size** (S, M, L), clicking "Create variations from all attributes" should automatically create all 6 combinations:

| Color | Size |
|-------|------|
| Red   | S    |
| Red   | M    |
| Red   | L    |
| Blue  | S    |
| Blue  | M    |
| Blue  | L    |

### The Math: Cartesian Product

The algorithm is called a **Cartesian product**. Given two sets, it produces every possible combination. Here's how we compute it in PHP:

```php
// Start with one empty combination
$combinations = array( array() );

foreach ( $variation_attributes as $attr_key => $options ) {
    $new_combinations = array();

    foreach ( $combinations as $combo ) {
        foreach ( $options as $option ) {
            $new_combo              = $combo;
            $new_combo[ $attr_key ] = $option;
            $new_combinations[]     = $new_combo;
        }
    }

    $combinations = $new_combinations;
}
```

Let's walk through this step by step:

**Before the loop:**
```
$combinations = [ [] ]    // One empty combo
```

**After processing Color (Red, Blue):**
```
$combinations = [
    ['color' => 'red'],
    ['color' => 'blue'],
]
```

**After processing Size (S, M, L):**
```
$combinations = [
    ['color' => 'red',  'size' => 's'],
    ['color' => 'red',  'size' => 'm'],
    ['color' => 'red',  'size' => 'l'],
    ['color' => 'blue', 'size' => 's'],
    ['color' => 'blue', 'size' => 'm'],
    ['color' => 'blue', 'size' => 'l'],
]
```

### Full AJAX Handler

```php
public function generate_variations() {
    // Nonce + capability checks (same pattern as before)
    if ( ! check_ajax_referer( 'storesuite-variation-nonce', 'security', false ) ) {
        wp_send_json_error( array( 'message' => 'Invalid security token.' ) );
    }

    $product_id = absint( wp_unslash( $_POST['product_id'] ) );
    $product    = wc_get_product( $product_id );

    if ( ! $product || ! $product->is_type( 'variable' ) ) {
        wp_send_json_error( array( 'message' => 'Invalid variable product.' ) );
    }

    // Collect variation attributes and their options
    $variation_attributes = array();

    foreach ( $product->get_attributes( 'edit' ) as $attribute ) {
        if ( ! $attribute->get_variation() ) {
            continue;  // Skip attributes not marked "Used for variations"
        }

        $attr_key = sanitize_title( $attribute->get_name() );

        if ( $attribute->is_taxonomy() ) {
            // Global/taxonomy attributes — get assigned term slugs
            $assigned_ids = $attribute->get_options();
            $terms = get_terms( array(
                'taxonomy'   => $attribute->get_name(),
                'include'    => $assigned_ids,
                'hide_empty' => false,
                'fields'     => 'slugs',
            ) );
            $options = is_wp_error( $terms ) ? array() : $terms;
        } else {
            // Custom/local attributes — get raw values
            $options = $attribute->get_options();
        }

        if ( ! empty( $options ) ) {
            $variation_attributes[ $attr_key ] = $options;
        }
    }

    if ( empty( $variation_attributes ) ) {
        wp_send_json_error( array(
            'message' => 'No variation attributes found.'
        ) );
    }

    // Build Cartesian product
    $combinations = array( array() );
    foreach ( $variation_attributes as $attr_key => $options ) {
        $new_combinations = array();
        foreach ( $combinations as $combo ) {
            foreach ( $options as $option ) {
                $new_combo              = $combo;
                $new_combo[ $attr_key ] = $option;
                $new_combinations[]     = $new_combo;
            }
        }
        $combinations = $new_combinations;
    }

    // Create variations (skip existing, cap at 50)
    $max_variations = apply_filters( 'storesuite_max_variations_per_generate', 50 );
    $created        = 0;
    $data_store     = \WC_Data_Store::load( 'product' );

    foreach ( $combinations as $combo ) {
        if ( $created >= $max_variations ) break;

        // Skip if this exact combo already exists
        $existing = $data_store->find_matching_product_variation( $product, $combo );
        if ( $existing ) continue;

        // Create the variation
        $variation = new WC_Product_Variation();
        $variation->set_parent_id( $product_id );
        $variation->set_status( 'publish' );
        $variation->set_attributes( $combo );
        $variation->save();

        ++$created;
    }

    // CRITICAL: Sync the parent product after modifications
    WC_Product_Variable::sync( $product_id );

    wp_send_json_success( array(
        'created' => $created,
        'message' => sprintf( '%d variations created.', $created ),
    ) );
}
```

### The JavaScript (Frontend)

```javascript
generateAll: function() {
    var self = this;

    // Show confirmation dialog using SweetAlert2
    Swal.fire({
        title: 'Generate variations?',
        text: 'This will create variations for all attribute combinations.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'OK',
    }).then( function( result ) {
        if ( ! result.isConfirmed ) return;

        // Show loading spinner
        window.StoreSuite.storeSuiteLoader.block( $( '.my-storesuite-wrapper' ) );

        $.ajax({
            url:  StoreSuiteVariation.ajax_url,
            type: 'POST',
            data: {
                action:     'storesuite_generate_variations',
                security:   StoreSuiteVariation.nonce,
                product_id: StoreSuiteVariation.product_id,
            },
            success: function( response ) {
                if ( response && response.success ) {
                    Swal.fire({
                        icon: 'success',
                        text: response.data.message,
                        timer: 3000,
                    });
                    // Go back to page 1 and reload
                    self.page = 1;
                    self.reload();
                } else {
                    Swal.fire({ icon: 'error', text: response.data.message });
                }
            },
            complete: function() {
                window.StoreSuite.storeSuiteLoader.unblock(
                    $( '.my-storesuite-wrapper' )
                );
            },
        });
    });
},
```

### Why Cap at 50?

Imagine a product with 5 attributes, each having 5 options. That's 5^5 = 3,125 combinations! Creating that many `WC_Product_Variation` objects in a single PHP request could easily hit the execution time limit (typically 30 seconds). The cap of 50 keeps things safe. Users can click "Go" again to generate more — the handler will skip existing ones and create new ones.

---

## 7. Step 4: Saving Variation Data

Now that we can display and generate variations, we need users to be able to **save their edits**. This is where the "dirty tracking" pattern shines.

### How Dirty Tracking Works

When a user changes any field inside a variation row, we add a CSS class `variation-needs-update` to that row:

```javascript
// Listen for any input changes inside the variations container
$(document).on('change input', '#storesuite-variations-container :input', function() {
    var $row = $(this).closest('.storesuite-variation-row');

    if ( ! $row.hasClass('variation-needs-update') ) {
        $row.addClass('variation-needs-update');
    }

    // Enable the save button
    $('#storesuite-save-variations-btn').prop('disabled', false);
});
```

When the user clicks "Save changes", we only collect data from rows with that class. This has two benefits:

1. **Performance** — We don't send unnecessary data to the server
2. **Safety** — We don't accidentally overwrite untouched variations

### The Save Handler (PHP)

```php
public function save_variations() {
    // Nonce + capability checks...

    $product_id = absint( wp_unslash( $_POST['product_id'] ) );
    $product    = wc_get_product( $product_id );

    // Get the list of variation IDs that were submitted
    $variable_post_id = isset( $_POST['variable_post_id'] )
        ? array_map( 'absint', (array) wp_unslash( $_POST['variable_post_id'] ) )
        : array();

    if ( empty( $variable_post_id ) ) {
        wp_send_json_error( array( 'message' => 'No variations to save.' ) );
    }

    $saved = 0;

    foreach ( $variable_post_id as $i => $variation_id ) {
        $variation = wc_get_product( $variation_id );
        if ( ! $variation || ! $variation->is_type( 'variation' ) ) {
            continue;
        }

        // Enabled status (checkbox: present = enabled)
        $enabled = isset( $_POST['variable_enabled'][ $i ] ) ? 'publish' : 'private';
        $variation->set_status( $enabled );

        // SKU
        if ( isset( $_POST['variable_sku'][ $i ] ) ) {
            $variation->set_sku( wc_clean( wp_unslash( $_POST['variable_sku'][ $i ] ) ) );
        }

        // Prices
        if ( isset( $_POST['variable_regular_price'][ $i ] ) ) {
            $variation->set_regular_price(
                wc_clean( wp_unslash( $_POST['variable_regular_price'][ $i ] ) )
            );
        }
        if ( isset( $_POST['variable_sale_price'][ $i ] ) ) {
            $variation->set_sale_price(
                wc_clean( wp_unslash( $_POST['variable_sale_price'][ $i ] ) )
            );
        }

        // Stock management
        $manage_stock = isset( $_POST['variable_manage_stock'][ $i ] );
        $variation->set_manage_stock( $manage_stock );

        if ( $manage_stock && isset( $_POST['variable_stock_qty'][ $i ] ) ) {
            $variation->set_stock_quantity(
                wc_clean( wp_unslash( $_POST['variable_stock_qty'][ $i ] ) )
            );
        }

        if ( isset( $_POST['variable_stock_status'][ $i ] ) ) {
            $variation->set_stock_status(
                wc_clean( wp_unslash( $_POST['variable_stock_status'][ $i ] ) )
            );
        }

        // Virtual & Downloadable (checkboxes)
        $variation->set_virtual( isset( $_POST['variable_is_virtual'][ $i ] ) );
        $variation->set_downloadable( isset( $_POST['variable_is_downloadable'][ $i ] ) );

        // Weight & Dimensions
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

        // Description
        if ( isset( $_POST['variable_description'][ $i ] ) ) {
            $variation->set_description(
                wc_clean( wp_unslash( $_POST['variable_description'][ $i ] ) )
            );
        }

        // Image
        if ( isset( $_POST['variable_image_id'][ $i ] ) ) {
            $variation->set_image_id( absint( $_POST['variable_image_id'][ $i ] ) );
        }

        // Menu order (for drag-and-drop sorting)
        if ( isset( $_POST['variable_menu_order'][ $i ] ) ) {
            $variation->set_menu_order( absint( $_POST['variable_menu_order'][ $i ] ) );
        }

        // Attributes — map attribute selects to this variation
        $parent_attributes = $product->get_attributes( 'edit' );
        $variation_attrs   = array();

        foreach ( $parent_attributes as $attribute ) {
            if ( ! $attribute->get_variation() ) continue;

            $attr_key = sanitize_title( $attribute->get_name() );
            $post_key = 'attribute_' . $attr_key;

            if ( isset( $_POST[ $post_key ][ $i ] ) ) {
                $variation_attrs[ $attr_key ] = wc_clean(
                    wp_unslash( $_POST[ $post_key ][ $i ] )
                );
            }
        }

        if ( ! empty( $variation_attrs ) ) {
            $variation->set_attributes( $variation_attrs );
        }

        $variation->save();
        ++$saved;
    }

    // Don't forget to sync the parent!
    WC_Product_Variable::sync( $product_id );

    wp_send_json_success( array(
        'saved'   => $saved,
        'message' => sprintf( '%d variation(s) saved.', $saved ),
    ) );
}
```

### The Save JavaScript

```javascript
saveVariations: function() {
    var $dirty = $('#storesuite-variations-container .variation-needs-update');

    if ( ! $dirty.length ) return; // Nothing to save

    window.StoreSuite.storeSuiteLoader.block( $( '.my-storesuite-wrapper' ) );

    // Use FormData to collect all inputs from dirty rows
    var formData = new FormData();
    formData.append( 'action', 'storesuite_save_variations' );
    formData.append( 'security', StoreSuiteVariation.save_variations_nonce );
    formData.append( 'product_id', StoreSuiteVariation.product_id );

    $dirty.each( function() {
        $( this ).find( ':input' ).each( function() {
            var $input = $( this );
            var name   = $input.attr( 'name' );
            if ( ! name ) return;

            // Handle checkboxes — only send checked ones
            if ( $input.is( ':checkbox' ) ) {
                if ( $input.is( ':checked' ) ) {
                    formData.append( name, $input.val() );
                }
            } else {
                formData.append( name, $input.val() );
            }
        });
    });

    $.ajax({
        url:  StoreSuiteVariation.ajax_url,
        type: 'POST',
        data: formData,
        processData: false,  // Required for FormData
        contentType: false,  // Required for FormData
        success: function( response ) {
            if ( response && response.success ) {
                Swal.fire({
                    icon: 'success',
                    text: response.data.message,
                    timer: 2000,
                });
                // Clear dirty markers
                $dirty.removeClass( 'variation-needs-update' );
                $( '#storesuite-save-variations-btn' ).prop( 'disabled', true );
            }
        },
        complete: function() {
            window.StoreSuite.storeSuiteLoader.unblock( $( '.my-storesuite-wrapper' ) );
        },
    });
},
```

### CSS for Modified Rows

A small but brilliant UX touch: a blue left border on modified rows:

```css
.variation-needs-update {
    border-left: 3px solid #2271b1 !important;
}
```

This gives users visual feedback about which rows have unsaved changes.

---

## 8. Step 5: Adding & Removing Single Variations

Sometimes you don't want to auto-generate all combinations — you just need to add one specific variation manually. And sometimes you need to delete just one.

### Adding a Blank Variation (PHP)

```php
public function add_variation() {
    // Nonce + capability checks...

    $product_id = absint( wp_unslash( $_POST['product_id'] ) );
    $product    = wc_get_product( $product_id );

    if ( ! $product || ! $product->is_type( 'variable' ) ) {
        wp_send_json_error( array( 'message' => 'Invalid variable product.' ) );
    }

    // Create an empty variation
    $variation = new WC_Product_Variation();
    $variation->set_parent_id( $product_id );
    $variation->set_status( 'publish' );
    $variation->save();

    // Get the new index (total children minus 1)
    $loop = count( $product->get_children() ) - 1;

    // Render the new row using the template
    ob_start();
    storesuite_get_template_part(
        'products/product-variation-row',
        '',
        array(
            'variation'    => $variation,
            'variation_id' => $variation->get_id(),
            'loop'         => $loop,
            'parent'       => $product,
        )
    );
    $html = ob_get_clean();

    wp_send_json_success( array(
        'html'    => $html,
        'message' => 'Variation added.',
    ) );
}
```

### Removing a Variation (PHP)

```php
public function remove_variation() {
    // Nonce + capability checks...

    $variation_id = absint( wp_unslash( $_POST['variation_id'] ) );
    $variation    = wc_get_product( $variation_id );

    if ( ! $variation || ! $variation->is_type( 'variation' ) ) {
        wp_send_json_error( array( 'message' => 'Invalid variation.' ) );
    }

    $parent_id = $variation->get_parent_id();

    // Permanently delete (force = true)
    $variation->delete( true );

    // Sync parent
    WC_Product_Variable::sync( $parent_id );

    wp_send_json_success( array( 'message' => 'Variation deleted.' ) );
}
```

### The JavaScript

For adding:

```javascript
addVariation: function() {
    var self = this;

    window.StoreSuite.storeSuiteLoader.block( $( '.my-storesuite-wrapper' ) );

    $.ajax({
        url: StoreSuiteVariation.ajax_url,
        type: 'POST',
        data: {
            action:     'storesuite_add_variation',
            security:   StoreSuiteVariation.add_variation_nonce,
            product_id: StoreSuiteVariation.product_id,
        },
        success: function( response ) {
            if ( response && response.success ) {
                // Prepend the new row at the top of the list
                $( '#storesuite-variations-container' ).prepend( response.data.html );
                Swal.fire({
                    icon: 'success',
                    text: response.data.message,
                    timer: 1500,
                });
            }
        },
        complete: function() {
            window.StoreSuite.storeSuiteLoader.unblock( $( '.my-storesuite-wrapper' ) );
        },
    });
},
```

For removing (with SweetAlert2 confirmation):

```javascript
onRemoveVariationClick: function( e ) {
    e.preventDefault();
    var self        = this;
    var $btn        = $( e.currentTarget );
    var variationId = $btn.data( 'variation-id' );
    var $row        = $btn.closest( '.storesuite-variation-row' );

    Swal.fire({
        title: 'Remove this variation?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'OK',
    }).then( function( result ) {
        if ( result.isConfirmed ) {
            self.removeVariation( variationId, $row );
        }
    });
},

removeVariation: function( variationId, $row ) {
    window.StoreSuite.storeSuiteLoader.block( $( '.my-storesuite-wrapper' ) );

    var self = this;

    $.ajax({
        url: StoreSuiteVariation.ajax_url,
        type: 'POST',
        data: {
            action:       'storesuite_remove_variation',
            security:     StoreSuiteVariation.remove_variation_nonce,
            variation_id: variationId,
        },
        success: function( response ) {
            if ( response && response.success ) {
                // Animate the row out and then reload to refresh pagination
                $row.slideUp( 200, function() {
                    $row.remove();
                    self.reload();
                });
            }
        },
        complete: function() {
            window.StoreSuite.storeSuiteLoader.unblock( $( '.my-storesuite-wrapper' ) );
        },
    });
},
```

---

## 9. Step 6: Bulk Actions for Variations

When you have 50+ variations, editing each one individually is painful. That's why bulk actions exist — set the price for **all** variations in one click.

### Supported Bulk Actions

| Action | What It Does |
|--------|-------------|
| `variable_regular_price` | Set regular price for all variations |
| `variable_sale_price` | Set sale price for all variations |
| `variable_stock_status` | Set stock status (in stock / out of stock / on backorder) |
| `toggle_enabled` | Flip enabled/disabled status for all variations |
| `delete_all` | Delete ALL variations (with confirmation) |

### The Bulk Edit Handler (PHP)

```php
public function bulk_edit_variations() {
    // Nonce + capability checks...

    $product_id  = absint( wp_unslash( $_POST['product_id'] ) );
    $product     = wc_get_product( $product_id );
    $bulk_action = sanitize_text_field( wp_unslash( $_POST['bulk_action'] ) );
    $value       = wc_clean( wp_unslash( $_POST['value'] ?? '' ) );

    $children = $product->get_children();

    if ( empty( $children ) ) {
        wp_send_json_error( array( 'message' => 'No variations found.' ) );
    }

    // Whitelist allowed actions (security!)
    $allowed = array( 'variable_regular_price', 'variable_sale_price',
                      'variable_stock_status', 'toggle_enabled', 'delete_all' );

    if ( ! in_array( $bulk_action, $allowed, true ) ) {
        wp_send_json_error( array( 'message' => 'Invalid bulk action.' ) );
    }

    $updated = 0;

    // Handle delete_all separately (it removes products, doesn't update them)
    if ( 'delete_all' === $bulk_action ) {
        foreach ( $children as $child_id ) {
            $variation = wc_get_product( $child_id );
            if ( $variation && $variation->is_type( 'variation' ) ) {
                $variation->delete( true );
                ++$updated;
            }
        }

        WC_Product_Variable::sync( $product_id );

        wp_send_json_success( array(
            'updated' => $updated,
            'message' => sprintf( '%d variation(s) deleted.', $updated ),
        ) );
        return;  // Early return — don't fall through!
    }

    // Handle update actions
    foreach ( $children as $child_id ) {
        $variation = wc_get_product( $child_id );
        if ( ! $variation || ! $variation->is_type( 'variation' ) ) continue;

        switch ( $bulk_action ) {
            case 'variable_regular_price':
                $variation->set_regular_price( $value );
                break;

            case 'variable_sale_price':
                $variation->set_sale_price( $value );
                break;

            case 'variable_stock_status':
                $variation->set_stock_status( $value );
                break;

            case 'toggle_enabled':
                $new_status = ( 'publish' === $variation->get_status() )
                    ? 'private'
                    : 'publish';
                $variation->set_status( $new_status );
                break;
        }

        $variation->save();
        ++$updated;
    }

    WC_Product_Variable::sync( $product_id );

    wp_send_json_success( array(
        'updated' => $updated,
        'message' => sprintf( '%d variation(s) updated.', $updated ),
    ) );
}
```

### The JavaScript — Smart Prompts

Different actions need different user inputs. Some need a number (prices), some need a dropdown (stock status), and some just need a yes/no confirmation:

```javascript
bulkAction: function( action ) {
    var self = this;

    switch ( action ) {
        case 'variable_regular_price':
        case 'variable_sale_price':
            // Ask for a price value
            Swal.fire({
                title: 'Set price for all variations',
                input: 'text',
                inputLabel: 'Enter price',
                showCancelButton: true,
                inputValidator: function( val ) {
                    if ( ! val || isNaN( parseFloat( val ) ) ) {
                        return 'Enter a valid price.';
                    }
                },
            }).then( function( result ) {
                if ( result.isConfirmed ) {
                    self.executeBulkAction( action, result.value );
                }
            });
            break;

        case 'variable_stock_status':
            // Ask for a stock status from dropdown
            Swal.fire({
                title: 'Select stock status',
                input: 'select',
                inputOptions: {
                    instock:     'In stock',
                    outofstock:  'Out of stock',
                    onbackorder: 'On backorder',
                },
                showCancelButton: true,
            }).then( function( result ) {
                if ( result.isConfirmed ) {
                    self.executeBulkAction( action, result.value );
                }
            });
            break;

        case 'toggle_enabled':
            Swal.fire({
                title: 'Toggle enabled/disabled for all variations?',
                icon: 'question',
                showCancelButton: true,
            }).then( function( result ) {
                if ( result.isConfirmed ) {
                    self.executeBulkAction( action, '' );
                }
            });
            break;

        case 'delete_all':
            Swal.fire({
                title: 'Delete all variations? This cannot be undone.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d63638', // Red button for danger
            }).then( function( result ) {
                if ( result.isConfirmed ) {
                    self.executeBulkAction( action, '' );
                }
            });
            break;
    }
},

executeBulkAction: function( action, value ) {
    var self = this;

    window.StoreSuite.storeSuiteLoader.block( $( '.my-storesuite-wrapper' ) );

    $.ajax({
        url: StoreSuiteVariation.ajax_url,
        type: 'POST',
        data: {
            action:      'storesuite_bulk_edit_variations',
            security:    StoreSuiteVariation.bulk_edit_nonce,
            product_id:  StoreSuiteVariation.product_id,
            bulk_action: action,
            value:       value,
        },
        success: function( response ) {
            if ( response && response.success ) {
                Swal.fire({
                    icon: 'success',
                    text: response.data.message,
                    timer: 2000,
                });
                self.page = 1;
                self.reload();
            }
        },
        complete: function() {
            window.StoreSuite.storeSuiteLoader.unblock( $( '.my-storesuite-wrapper' ) );
        },
    });
},
```

---

## 10. Step 7: Variation Image Upload

Each variation can have its own image. This is important for showcasing different colors, patterns, or styles. We leverage WordPress's built-in **Media Library** (`wp.media`) so we don't need to build a file upload from scratch.

### The Template (in variation row)

```html
<div class="storesuite-variation-image-upload" data-loop="<?php echo esc_attr( $loop ); ?>">
    <!-- Clickable thumbnail -->
    <img src="<?php echo esc_url( $variation_thumb ); ?>" width="60" height="60">

    <!-- Hidden input stores the attachment ID -->
    <input type="hidden"
           name="variable_image_id[<?php echo esc_attr( $loop ); ?>]"
           value="<?php echo esc_attr( $variation->get_image_id() ); ?>">

    <!-- Remove link (hidden when no image set) -->
    <a href="#" class="storesuite-remove-variation-image"
       <?php echo $variation->get_image_id() ? '' : 'style="display:none;"'; ?>>
        Remove image
    </a>
</div>
```

### The JavaScript — Media Library Integration

```javascript
/**
 * Open WordPress media library when clicking the variation image.
 */
onImageUploadClick: function( e ) {
    e.preventDefault();

    var $upload = $( this ).closest( '.storesuite-variation-image-upload' );
    var $row    = $upload.closest( '.storesuite-variation-row' );

    // Open WordPress media frame
    var frame = wp.media({
        title:    'Choose variation image',
        button:   { text: 'Set image' },
        multiple: false,
        library:  { type: 'image' },
    });

    // When user selects an image
    frame.on( 'select', function() {
        var attachment = frame.state().get( 'selection' ).first().toJSON();

        // Get thumbnail URL (prefer thumbnail size, fall back to full)
        var thumbUrl = ( attachment.sizes && attachment.sizes.thumbnail )
            ? attachment.sizes.thumbnail.url
            : attachment.url;

        // Update the visible thumbnail
        $upload.find( 'img' ).attr( 'src', thumbUrl );

        // Store the attachment ID in the hidden input
        $upload.find( 'input[type="hidden"]' ).val( attachment.id ).trigger( 'change' );

        // Show the "Remove image" link
        $upload.find( '.storesuite-remove-variation-image' ).show();

        // Mark the row as modified
        if ( ! $row.hasClass( 'variation-needs-update' ) ) {
            $row.addClass( 'variation-needs-update' );
        }
        $( '#storesuite-save-variations-btn' ).prop( 'disabled', false );
    });

    frame.open();
},

/**
 * Remove the variation image and reset to placeholder.
 */
onRemoveImageClick: function( e ) {
    e.preventDefault();

    var $upload = $( this ).closest( '.storesuite-variation-image-upload' );
    var $row    = $upload.closest( '.storesuite-variation-row' );

    // Reset to placeholder image
    $upload.find( 'img' ).attr( 'src', StoreSuiteVariation.placeholder_img );

    // Clear the attachment ID (set to 0)
    $upload.find( 'input[type="hidden"]' ).val( 0 ).trigger( 'change' );

    // Hide the "Remove image" link
    $( this ).hide();

    // Mark as modified
    $row.addClass( 'variation-needs-update' );
    $( '#storesuite-save-variations-btn' ).prop( 'disabled', false );
},
```

### Making Sure wp.media Is Available

You need to ensure WordPress Media scripts are loaded on your page. In your `Assets.php`:

```php
wp_enqueue_media(); // This loads wp.media and all its dependencies
```

---

## 11. Step 8: Default Attributes & Drag Reorder

The final two features make the variation system feel complete and polished.

### Default Attributes

When a customer visits a variable product page, WooCommerce can pre-select certain attribute values. For example, "Red" and "Medium" could be pre-selected so the customer sees a price immediately.

#### The Template

```php
<?php
$default_attributes = $product ? $product->get_default_attributes() : array();
?>
<div class="storesuite-default-attributes">
    <h4>Default Form Values</h4>
    <div class="row">
        <?php foreach ( $variation_attributes as $attribute ) :
            $attr_name   = $attribute->get_name();
            $attr_key    = sanitize_title( $attr_name );
            $attr_label  = wc_attribute_label( $attr_name );
            $current_val = $default_attributes[ $attr_key ] ?? '';

            // Get available options
            if ( $attribute->is_taxonomy() ) {
                $terms = get_terms( array(
                    'taxonomy'   => $attr_name,
                    'hide_empty' => false,
                ) );
            } else {
                $terms = $attribute->get_options();
            }
        ?>
            <div class="col-md-4">
                <label><?php echo esc_html( $attr_label ); ?></label>
                <select name="default_attribute_<?php echo esc_attr( $attr_key ); ?>"
                        class="storesuite-default-attribute-select">
                    <option value="">No default</option>
                    <?php if ( $attribute->is_taxonomy() && ! is_wp_error( $terms ) ) : ?>
                        <?php foreach ( $terms as $term ) : ?>
                            <option value="<?php echo esc_attr( $term->slug ); ?>"
                                <?php selected( $current_val, $term->slug ); ?>>
                                <?php echo esc_html( $term->name ); ?>
                            </option>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <?php foreach ( $terms as $option ) : ?>
                            <option value="<?php echo esc_attr( $option ); ?>"
                                <?php selected( $current_val, $option ); ?>>
                                <?php echo esc_html( $option ); ?>
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>
        <?php endforeach; ?>
        <div class="col-md-12">
            <button type="button" id="storesuite-save-default-attrs-btn">
                Save defaults
            </button>
        </div>
    </div>
</div>
```

#### The Save Handler (PHP)

```php
public function save_default_attributes() {
    // Nonce + capability checks...

    $product_id = absint( wp_unslash( $_POST['product_id'] ) );
    $product    = wc_get_product( $product_id );

    $defaults   = array();
    $attributes = $product->get_attributes( 'edit' );

    foreach ( $attributes as $attribute ) {
        if ( ! $attribute->get_variation() ) continue;

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

    wp_send_json_success( array(
        'message' => 'Default attributes saved.',
    ) );
}
```

### Drag-and-Drop Reorder

We use **jQuery UI Sortable** (usually already available in WordPress) to let users drag variations into a different order.

```javascript
initSortable: function() {
    var $container = $( '#storesuite-variations-container' );

    if ( ! $container.length || ! $.fn.sortable ) return;

    $container.sortable({
        items:       '.storesuite-variation-row',
        handle:      '.storesuite-variation-header', // Drag by the header
        cursor:      'move',
        placeholder: 'storesuite-sortable-placeholder',
        opacity:     0.65,
        stop: function() {
            // After the user drops a row, update all menu_order values
            $container.find( '.storesuite-variation-row' ).each( function( index ) {
                // Set menu_order to the new position index
                $( this ).find( 'input[name^="variable_menu_order"]' ).val( index );

                // Mark all rows as needing update
                $( this ).addClass( 'variation-needs-update' );
            });

            // Enable the save button
            $( '#storesuite-save-variations-btn' ).prop( 'disabled', false );
        },
    });
},
```

Call `this.initSortable()` after loading/reloading variations so it's always initialized.

### CSS for the Sort Placeholder

```css
.storesuite-sortable-placeholder {
    border: 2px dashed #c3c4c7;
    background: #f6f7f7;
    min-height: 60px;
    margin-bottom: 12px;
}

.storesuite-variation-header {
    cursor: move;
}
```

---

## 12. Key Design Decisions & Best Practices

Here's a summary of the patterns we used throughout this project. These are good practices for any WordPress/WooCommerce AJAX-driven feature.

### 1. Always Verify Nonces

Every AJAX handler starts with `check_ajax_referer()`. This prevents CSRF attacks.

```php
if ( ! check_ajax_referer( 'my-nonce-action', 'security', false ) ) {
    wp_send_json_error( array( 'message' => 'Invalid security token.' ) );
}
```

### 2. Always Check Capabilities

After the nonce, check if the user has permission:

```php
if ( ! current_user_can( 'edit_products' ) ) {
    wp_send_json_error( array( 'message' => 'Unauthorized.' ) );
}
```

### 3. Always Call `WC_Product_Variable::sync()`

After modifying variations, always sync the parent product. This recalculates:

- Price range (min/max)
- Stock status
- Variation count
- Other aggregate data

```php
WC_Product_Variable::sync( $product_id );
```

### 4. Use `wc_clean()` for Sanitization

WooCommerce provides `wc_clean()` which sanitizes input. Use it for all user inputs:

```php
$price = wc_clean( wp_unslash( $_POST['price'] ) );
```

### 5. Use FormData for Complex Forms

When sending forms with many inputs, `FormData` is cleaner than manually building query strings:

```javascript
var formData = new FormData();
$dirty.each( function() {
    $( this ).find( ':input' ).each( function() {
        formData.append( $(this).attr('name'), $(this).val() );
    });
});
```

### 6. User Feedback Is Essential

Always give users feedback after actions — success toasts, error messages, loading spinners. We use SweetAlert2 for dialog confirmations and feedback toasts.

---

## 13. File Structure Summary

Here's a bird's-eye view of all the files involved:

```
your-plugin/
├── includes/
│   ├── Product/
│   │   └── VariationAjax.php       # All AJAX handlers (attributes + variations)
│   └── Assets.php                   # Script/style enqueuing + localization
├── templates/
│   └── products/
│       ├── product-form.php          # Main product form (includes attribute & variation templates)
│       ├── product-attributes.php    # Attributes card + add toolbar
│       ├── product-attribute-row.php # Single attribute accordion row
│       ├── product-variations.php    # Variations card shell + toolbar
│       └── product-variation-row.php # Single variation accordion row
├── assets/
│   └── frontend/
│       ├── product-variation.js      # All JavaScript logic (attributes + variations)
│       └── style.css                 # Styles including attribute & variation CSS
```

### AJAX Actions Registered

| PHP Method | WordPress AJAX Action | Purpose |
|---|---|---|
| `storesuite_ajax_add_attribute()` | `storesuite_add_attribute` | Add a new attribute row |
| `storesuite_ajax_save_attributes()` | `storesuite_save_attributes` | Save all product attributes |
| `load_variations()` | `storesuite_load_variations` | Fetch & display variations |
| `generate_variations()` | `storesuite_generate_variations` | Auto-generate from attributes |
| `save_variations()` | `storesuite_save_variations` | Save edited variation data |
| `add_variation()` | `storesuite_add_variation` | Create one blank variation |
| `remove_variation()` | `storesuite_remove_variation` | Delete one variation |
| `bulk_edit_variations()` | `storesuite_bulk_edit_variations` | Bulk price/stock/status/delete |
| `save_default_attributes()` | `storesuite_save_default_attributes` | Save default attribute selections |

---

## 14. Wrapping Up

Building a product variation management system is a significant undertaking, but breaking it down into clear, sequential steps makes it very manageable. Let's recap what we built:

0. **Attributes CRUD** — Add, save, and remove product attributes (the foundation that makes variations possible)
1. **Card Shell** — The visible container that shows when product type is "Variable"
2. **Load & Display** — AJAX-powered variation listing with pagination
3. **Generate** — Cartesian product algorithm to create all attribute combinations
4. **Save** — Dirty-tracking to only save modified rows
5. **Add & Remove** — Create blank variations or delete specific ones
6. **Bulk Actions** — Mass-update prices, stock, or delete all
7. **Image Upload** — WordPress Media Library integration per variation
8. **Default Attributes & Reorder** — Pre-selected values and drag-and-drop sorting

### What Makes This Implementation Solid

- **Performance**: AJAX-loaded variations with pagination prevent slow page loads
- **Security**: Nonce verification and capability checks on every handler
- **UX**: Dirty tracking, loading spinners, confirmation dialogs, and visual feedback
- **Extensibility**: Template-based rendering allows theme overrides
- **WooCommerce-native**: Uses `WC_Product_Variable`, `WC_Product_Variation`, and `WC_Product_Variable::sync()` — works perfectly with the WooCommerce data layer

If you're building your own frontend dashboard or headless WooCommerce experience, this pattern should give you a solid foundation to start from. Happy coding! 🚀

---

> **Source**: This guide is based on the [StoreSuite](https://github.com/aminurislamarnob/storesuite) plugin's product variation management implementation — Epic Issue [#86](https://github.com/aminurislamarnob/storesuite/issues/86) with implementation groups [#91](https://github.com/aminurislamarnob/storesuite/issues/91)-[#98](https://github.com/aminurislamarnob/storesuite/issues/98).
