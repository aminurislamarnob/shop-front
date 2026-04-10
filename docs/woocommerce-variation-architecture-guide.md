# How WooCommerce Implements Product Variations in the Admin Panel

> A deep-dive into WooCommerce's internal architecture for managing variable products and their variations on the wp-admin product add/edit screen.

---

## Table of Contents

1. [Introduction — What This Guide Covers](#1-introduction--what-this-guide-covers)
2. [The Big Picture — Architecture Overview](#2-the-big-picture--architecture-overview)
3. [The Data Model — Core PHP Classes](#3-the-data-model--core-php-classes)
4. [The Meta Box Shell — Product Data Panel](#4-the-meta-box-shell--product-data-panel)
5. [The Attributes Tab — Where Variations Begin](#5-the-attributes-tab--where-variations-begin)
6. [The Variations Tab — Panel & Toolbar](#6-the-variations-tab--panel--toolbar)
7. [Single Variation Row — The Accordion Template](#7-single-variation-row--the-accordion-template)
8. [AJAX Handlers — The PHP Backend](#8-ajax-handlers--the-php-backend)
9. [JavaScript — The Frontend Brain](#9-javascript--the-frontend-brain)
10. [Saving Variations — The Full Form Submit Path](#10-saving-variations--the-full-form-submit-path)
11. [Bulk Edit — Mass-Updating Variations](#11-bulk-edit--mass-updating-variations)
12. [Variation Images — Media Library Integration](#12-variation-images--media-library-integration)
13. [Default Attributes & Sorting](#13-default-attributes--sorting)
14. [Script Enqueuing & Localization](#14-script-enqueuing--localization)
15. [Extensibility — Action & Filter Hooks](#15-extensibility--action--filter-hooks)
16. [File Structure Summary](#16-file-structure-summary)
17. [Wrapping Up — Key Takeaways](#17-wrapping-up--key-takeaways)

---

## 1. Introduction — What This Guide Covers

If you've ever created a WooCommerce "Variable" product — say a T-shirt with multiple sizes and colors — you've interacted with one of the most complex features in the WooCommerce admin. Behind that seemingly simple UI, there's a sophisticated system involving:

- **A tabbed meta box** registered via `WC_Meta_Box_Product_Data`
- **Two dedicated tabs** — "Attributes" (the foundation) and "Variations" (the actual variation management)
- **7+ AJAX endpoints** for loading, saving, generating, and bulk-editing variations
- **A full JavaScript module** (~1,750 lines) handling UI state, pagination, dirty-tracking, and media uploads
- **PHP view templates** for rendering each variation row server-side

This guide walks you through every layer — from the WordPress meta box registration to the final `$variation->save()` call — so you can understand, debug, or extend WooCommerce's variation system with confidence.

### Who This Is For

- WordPress/WooCommerce plugin developers who need to **extend** the variation system
- Developers building **custom product editors** (like frontend dashboards)
- Anyone who wants to understand WooCommerce's internal architecture at a deep level

---

## 2. The Big Picture — Architecture Overview

Here's how all the pieces fit together:

```
┌─────────────────────────────────────────────────────────┐
│                   wp-admin Product Editor                │
│                                                         │
│  ┌─── Product Data Meta Box (WC_Meta_Box_Product_Data)──┐│
│  │                                                      ││
│  │  Tabs: General | Inventory | Shipping | ...          ││
│  │        Attributes | Variations | Advanced            ││
│  │                                                      ││
│  │  ┌─ Attributes Tab ──────────────────────────────┐   ││
│  │  │  • Add taxonomy / custom attributes           │   ││
│  │  │  • "Used for variations" checkbox             │   ││
│  │  │  • AJAX: save_attributes, add_attribute       │   ││
│  │  └───────────────────────────────────────────────┘   ││
│  │                                                      ││
│  │  ┌─ Variations Tab ─────────────────────────────┐    ││
│  │  │  • Default Form Values (selects)             │    ││
│  │  │  • Toolbar: Generate | Add Manually | Bulk   │    ││
│  │  │  • Pagination (15 per page, data-attributes) │    ││
│  │  │  • .woocommerce_variations container (AJAX)  │    ││
│  │  │  • Save/Cancel buttons                       │    ││
│  │  └──────────────────────────────────────────────┘    ││
│  └──────────────────────────────────────────────────────┘│
│                                                         │
│  JS: meta-boxes-product-variation.js                    │
│      ├── wc_meta_boxes_product_variations_actions       │
│      ├── wc_meta_boxes_product_variations_media         │
│      ├── wc_meta_boxes_product_variations_ajax          │
│      └── wc_meta_boxes_product_variations_pagenav       │
│                                                         │
│  PHP AJAX: WC_AJAX (class-wc-ajax.php)                  │
│      ├── load_variations()                              │
│      ├── save_variations()                              │
│      ├── add_variation()                                │
│      ├── link_all_variations()                          │
│      ├── remove_variations()                            │
│      ├── save_attributes()                              │
│      └── bulk_edit_variations()                         │
└─────────────────────────────────────────────────────────┘
```

### The Request Flow (Simplified)

1. User opens a product edit screen → `WC_Meta_Box_Product_Data::output()` renders the meta box
2. User clicks the **Variations** tab → JavaScript lazy-loads variations via `woocommerce_load_variations` AJAX
3. Server queries `product_variation` posts → renders `html-variation-admin.php` for each → returns HTML
4. JavaScript injects the HTML into `.woocommerce_variations` → triggers `woocommerce_variations_loaded`
5. User edits a field → `.variation-needs-update` class is added (dirty tracking)
6. User clicks **Save changes** → JS serializes only dirty rows → posts to `woocommerce_save_variations`
7. Server calls `WC_Meta_Box_Product_Data::save_variations()` → loops through `$_POST['variable_post_id']` → calls `$variation->set_props()` + `$variation->save()` for each

---

## 3. The Data Model — Core PHP Classes

WooCommerce uses three core classes that form the data layer for variations:

### `WC_Product_Attribute` — The Building Block

Every attribute (Color, Size, Material, etc.) is represented by this class. It implements `ArrayAccess` for backwards compatibility.

```php
// File: includes/class-wc-product-attribute.php

class WC_Product_Attribute implements ArrayAccess {

    protected $data = array(
        'id'        => 0,      // 0 = custom attribute, >0 = taxonomy attribute
        'name'      => '',     // 'pa_color' for taxonomy, 'Material' for custom
        'options'   => array(),// Term IDs (taxonomy) or string values (custom)
        'position'  => 0,      // Sort order
        'visible'   => false,  // Show on product page
        'variation' => false,  // ← THE KEY FLAG: "Used for variations"
    );

    public function is_taxonomy() {
        return 0 < $this->get_id(); // If id > 0, it's a registered taxonomy
    }
}
```

The `variation` flag is critical — only attributes with `variation => true` appear in the Variations tab.

### `WC_Product_Variable` — The Parent Product

This extends `WC_Product` and adds variation-specific methods. It's the parent that holds all child variations.

```php
// File: includes/class-wc-product-variable.php

class WC_Product_Variable extends WC_Product {

    protected $children = null;           // All variation IDs
    protected $visible_children = null;   // Published/visible variation IDs
    protected $variation_attributes = null;// Attributes marked for variations

    // Lazy-loaded — only queried when needed
    public function get_children() {
        if ( null === $this->children ) {
            $children = $this->data_store->read_children( $this );
            $this->set_children( $children['all'] );
            $this->set_visible_children( $children['visible'] );
        }
        return $this->children;
    }

    // Returns only attributes where get_variation() === true
    public function get_variation_attributes() {
        if ( null === $this->variation_attributes ) {
            $this->variation_attributes = $this->data_store->read_variation_attributes( $this );
        }
        return $this->variation_attributes;
    }

    // The critical sync method — called after every mutation
    public static function sync( $product, $save = true ) {
        // Syncs prices, stock status, and attributes from children to parent
        $data_store = WC_Data_Store::load( 'product-variable' );
        $data_store->sync_price( $product );
        $data_store->sync_stock_status( $product );
        self::sync_attributes( $product );
        // ...
    }
}
```

### `WC_Product_Variation` — Each Individual Variation

Each variation is stored as a WordPress post with `post_type = 'product_variation'` and `post_parent` set to the variable product's ID. It extends `WC_Product` and holds its own price, stock, SKU, image, and attribute values.

---

## 4. The Meta Box Shell — Product Data Panel

Everything starts in `WC_Meta_Box_Product_Data::output()`. This is the method WordPress calls to render the "Product data" meta box below the editor.

```php
// File: includes/admin/meta-boxes/class-wc-meta-box-product-data.php

public static function output( $post ) {
    global $thepostid, $product_object;

    $thepostid      = $post->ID;
    $product_object = $thepostid ? wc_get_product( $thepostid ) : new WC_Product();

    wp_nonce_field( 'woocommerce_save_data', 'woocommerce_meta_nonce' );

    include __DIR__ . '/views/html-product-data-panel.php';
}
```

### The Panel Template

`html-product-data-panel.php` renders two things:

1. **Product type selector** — a `<select>` dropdown with "Simple", "Grouped", "External", "Variable"
2. **Tabs** — generated from `get_product_data_tabs()`, which returns an array of tabs with `target`, `class`, and `priority` keys

```php
// The Variations tab definition
'variations' => array(
    'label'    => __( 'Variations', 'woocommerce' ),
    'target'   => 'variable_product_options',   // Matches the panel div ID
    'class'    => array( 'show_if_variable' ),  // Only visible for Variable products
    'priority' => 60,
),
```

That `show_if_variable` class is crucial. WooCommerce's `meta-boxes-product.js` script watches the `#product-type` selector and shows/hides tabs using CSS class toggles. When the user switches to "Variable product," tabs with `show_if_variable` become visible, while others with `hide_if_variable` are hidden.

The tabs content is rendered by two calls:

```php
self::output_tabs();       // Renders General, Inventory, Shipping, Attributes, etc.
self::output_variations(); // Renders the Variations panel specifically
```

---

## 5. The Attributes Tab — Where Variations Begin

Before any variations can exist, you need attributes. The Attributes tab is where they're defined.

### Template: `html-product-data-attributes.php`

```php
// File: includes/admin/meta-boxes/views/html-product-data-attributes.php

<div id="product_attributes" class="panel wc-metaboxes-wrapper hidden">
    <div class="toolbar toolbar-top">
        <!-- Help message about attributes -->
        <div class="actions">
            <button type="button" class="button add_custom_attribute">Add new</button>
            <select class="wc-attribute-search" data-placeholder="Add existing"
                    data-minimum-input-length="0">
            </select>
        </div>
    </div>

    <div class="product_attributes wc-metaboxes">
        <?php foreach ( $product_attributes as $attribute ) : ?>
            <?php include 'html-product-attribute.php'; ?>
        <?php endforeach; ?>
    </div>

    <div class="toolbar toolbar-buttons">
        <button type="button" class="button save_attributes button-primary disabled">
            Save attributes
        </button>
    </div>
</div>
```

There are two ways to add an attribute:

| Method | What Happens |
|--------|-------------|
| **"Add existing"** dropdown | Searches registered WooCommerce attribute taxonomies (e.g. `pa_color`). AJAX calls `woocommerce_add_attribute` which builds a `WC_Product_Attribute` and renders `html-product-attribute.php` |
| **"Add new"** button | Creates a custom (non-taxonomy) attribute. User types a name and pipe-separated values |

### The Attribute Row Template

Each attribute gets a collapsible metabox via `html-product-attribute.php` → `html-product-attribute-inner.php`. The inner template contains a critical checkbox:

```html
<!-- "Used for variations" checkbox -->
<input type="checkbox" 
       name="attribute_variation[<?php echo $i; ?>]" 
       value="1" 
       <?php checked( $attribute->get_variation(), true ); ?> />
```

This checkbox is what connects attributes to the variation system. Only attributes with this checked will appear in the Variations tab.

### Saving Attributes via AJAX

When you click "Save attributes", JavaScript serializes the form data and posts to `woocommerce_save_attributes`:

```php
// File: includes/class-wc-ajax.php

public static function save_attributes() {
    check_ajax_referer( 'save-attributes', 'security' );
    
    if ( ! current_user_can( 'edit_products' ) ) wp_die( -1 );
    
    parse_str( wp_unslash( $_POST['data'] ), $data );
    
    $product = self::create_product_with_attributes( $data );
    
    // Re-render attribute rows with fresh data
    ob_start();
    $attributes = $product->get_attributes( 'edit' );
    // ... render html-product-attribute.php for each
    $response['html'] = ob_get_clean();
    
    wp_send_json_success( $response );
}
```

The `create_product_with_attributes()` helper does the heavy lifting:

```php
private static function create_product_with_attributes( $data ) {
    $attributes = WC_Meta_Box_Product_Data::prepare_attributes( $data );
    $product_id = absint( $_POST['post_id'] );
    $product    = new $classname( $product_id );
    $product->set_attributes( $attributes );
    $product->save();
    return $product;
}
```

### `prepare_attributes()` — The Critical Normalizer

This is the single most important method for attribute data processing:

```php
public static function prepare_attributes( $data = false ) {
    $attributes = array();

    if ( isset( $data['attribute_names'], $data['attribute_values'] ) ) {
        $attribute_names      = $data['attribute_names'];
        $attribute_values     = $data['attribute_values'];
        $attribute_visibility = $data['attribute_visibility'] ?? array();
        $attribute_variation  = $data['attribute_variation'] ?? array();
        $attribute_position   = $data['attribute_position'];

        for ( $i = 0; $i <= max( array_keys( $attribute_names ) ); $i++ ) {
            if ( empty( $attribute_names[ $i ] ) ) continue;
            
            $attribute_name = wc_clean( esc_html( $attribute_names[ $i ] ) );
            $attribute_id   = 0;
            
            // If it starts with 'pa_', it's a taxonomy attribute
            if ( 'pa_' === substr( $attribute_name, 0, 3 ) ) {
                $attribute_id = wc_attribute_taxonomy_id_by_name( $attribute_name );
            }
            
            $options = $attribute_values[ $i ] ?? '';
            
            if ( is_array( $options ) ) {
                // Taxonomy terms sent as array of term IDs
                $options = wp_parse_id_list( $options );
            } else {
                // Custom text-based attributes sent as pipe-separated string
                $options = wc_get_text_attributes( $options );
            }
            
            $attribute = new WC_Product_Attribute();
            $attribute->set_id( $attribute_id );
            $attribute->set_name( $attribute_name );
            $attribute->set_options( $options );
            $attribute->set_position( $attribute_position[ $i ] );
            $attribute->set_visible( isset( $attribute_visibility[ $i ] ) );
            $attribute->set_variation( isset( $attribute_variation[ $i ] ) );
            
            $attributes[] = $attribute;
        }
    }
    return $attributes;
}
```

> **⚠️ Key Gotcha:** For taxonomy attributes, options are sent as an array of term IDs. For custom attributes, they're sent as a pipe-separated string (e.g., `"Small | Medium | Large"`). The `wc_get_text_attributes()` function splits them. If you're building a custom implementation, you **must** match this format exactly.

---

## 6. The Variations Tab — Panel & Toolbar

The Variations tab is rendered by `WC_Meta_Box_Product_Data::output_variations()`:

```php
public static function output_variations() {
    global $post, $wpdb, $product_object;
    
    // Get only attributes where get_variation() === true
    $variation_attributes = array_filter(
        $product_object->get_attributes(),
        array( __CLASS__, 'filter_variation_attributes' )
    );
    
    $default_attributes     = $product_object->get_default_attributes();
    $variations_count       = $wpdb->get_var( /* COUNT(*) query */ );
    $variations_per_page    = absint( apply_filters(
        'woocommerce_admin_meta_boxes_variations_per_page', 15
    ) );
    $variations_total_pages = ceil( $variations_count / $variations_per_page );
    
    include __DIR__ . '/views/html-product-data-variations.php';
}
```

### The Variations Panel Template

`html-product-data-variations.php` has three states:

#### State 1: No Variation Attributes

If no attributes have "Used for variations" checked, WooCommerce shows a helpful empty state:

```html
<div class="add-attributes-container">
    <p>Add some attributes in the <a href="#product_attributes">Attributes</a> tab 
       to generate variations. Make sure to check the <b>Used for variations</b> box.</p>
</div>
```

#### State 2: Has Attributes, No Variations

Shows the toolbar buttons and an illustration with the message: "No variations yet. Generate them from all added attributes or add a new variation manually."

#### State 3: Active Variations

The full toolbar renders with:

1. **Default Form Values** — A select dropdown for each variation attribute, allowing the merchant to pre-select a default (e.g., default color = "Blue")

2. **Toolbar buttons:**
   - `Generate variations` — Runs `link_all_variations` AJAX
   - `Add manually` — Runs `add_variation` AJAX
   - **Bulk Actions** dropdown (hidden class, shown via JS when variations exist)

3. **Pagination** — Page selector, first/prev/next/last buttons, item count

4. **The Variations Container** — This is the critical element:

```html
<div class="woocommerce_variations wc-metaboxes" 
     data-attributes="<?php echo wc_esc_json( wp_json_encode(...) ); ?>"
     data-total="<?php echo $variations_count; ?>"
     data-total_pages="<?php echo $variations_total_pages; ?>"
     data-page="1"
     data-edited="false">
</div>
```

This `div` starts **empty**. Variations are loaded into it via AJAX when the user clicks the Variations tab.

5. **Save/Cancel buttons** at the bottom (disabled by default, enabled when dirty)

### Bulk Actions Dropdown

The bulk actions dropdown is impressively comprehensive:

| Category | Actions |
|----------|---------|
| **Status** | Toggle Enabled, Toggle Downloadable, Toggle Virtual |
| **Pricing** | Set regular prices, Increase/Decrease regular prices (fixed or %), Set sale prices, Increase/Decrease sale prices, Set scheduled sale dates |
| **Inventory** | Toggle Manage Stock, Set stock qty, Set Status (In stock / Out of stock / On backorder), Low stock threshold |
| **Shipping** | Set Length, Width, Height, Weight |
| **Downloads** | Download limit, Download expiry |
| **Destructive** | Delete all variations |

This entire dropdown is extensible via the `woocommerce_variable_product_bulk_edit_actions` action hook.

---

## 7. Single Variation Row — The Accordion Template

Each variation is rendered by `html-variation-admin.php`. It follows the classic WooCommerce "wc-metabox" accordion pattern:

```html
<div class="woocommerce_variation wc-metabox closed">
    <!-- Header (always visible) -->
    <h3>
        <a href="#" class="edit_variation edit">Edit</a>
        <a href="#" class="remove_variation delete" rel="VARIATION_ID">Remove</a>
        <div class="tips sort" data-tip="Drag and drop, or click to set admin variation order"></div>
        <strong>#VARIATION_ID</strong>
        
        <!-- Attribute selector dropdowns (shown in header) -->
        <select name="attribute_pa_color[0]">
            <option value="">Any Color…</option>
            <option value="red" selected>Red</option>
            <option value="blue">Blue</option>
        </select>
        
        <input type="hidden" class="variable_post_id" 
               name="variable_post_id[0]" value="VARIATION_ID" />
        <input type="hidden" class="variation_menu_order" 
               name="variation_menu_order[0]" value="0" />
    </h3>
    
    <!-- Body (hidden by default, shown when clicked) -->
    <div class="woocommerce_variable_attributes wc-metabox-content" style="display: none;">
        <div class="data">
            <!-- Image upload -->
            <!-- SKU, GTIN/UPC/EAN/ISBN -->
            <!-- Enabled, Downloadable, Virtual, Manage Stock checkboxes -->
            <!-- Regular price, Sale price, Sale dates -->
            <!-- Stock quantity, Backorders, Low stock threshold -->
            <!-- Stock status, Weight, Dimensions -->
            <!-- Shipping class, Tax class -->
            <!-- Description textarea -->
            <!-- Downloadable files table -->
        </div>
    </div>
</div>
```

### Input Naming Convention

WooCommerce uses array-indexed input names with the loop index `$loop`:

```
variable_post_id[0]        → The variation's post ID (hidden)
variation_menu_order[0]    → Sort order (hidden)
attribute_pa_color[0]      → Attribute value for this variation
variable_regular_price[0]  → Regular price
variable_sale_price[0]     → Sale price
variable_sku[0]            → SKU
variable_stock[0]          → Stock quantity
variable_enabled[0]        → Enabled checkbox
variable_is_downloadable[0]→ Downloadable checkbox
variable_is_virtual[0]     → Virtual checkbox
variable_manage_stock[0]   → Manage stock checkbox
upload_image_id[0]         → Image attachment ID (hidden)
```

This array-index pattern lets the server-side handler loop from `0` to `max_loop` and process each variation:

```php
$max_loop = max( array_keys( $_POST['variable_post_id'] ) );

for ( $i = 0; $i <= $max_loop; $i++ ) {
    $variation_id = absint( $_POST['variable_post_id'][ $i ] );
    // ... get all fields at index [$i]
}
```

---

## 8. AJAX Handlers — The PHP Backend

All variation AJAX operations live in `WC_AJAX` (`includes/class-wc-ajax.php`). Each handler follows the same security pattern:

```php
public static function some_handler() {
    check_ajax_referer( 'nonce-action', 'security' );
    
    if ( ! current_user_can( 'edit_products' ) ) {
        wp_die( -1 );
    }
    
    // ... actual logic ...
    
    wp_die();
}
```

### Handler Summary

| AJAX Action | PHP Method | Nonce Action | Purpose |
|---|---|---|---|
| `woocommerce_load_variations` | `load_variations()` | `load-variations` | Fetch paginated variation rows (HTML) |
| `woocommerce_save_variations` | `save_variations()` | `save-variations` | Persist all modified variation data |
| `woocommerce_add_variation` | `add_variation()` | `add-variation` | Create one blank variation |
| `woocommerce_link_all_variations` | `link_all_variations()` | `link-variations` | Auto-generate all attribute combinations |
| `woocommerce_remove_variations` | `remove_variations()` | `delete-variations` | Delete one or more variations |
| `woocommerce_bulk_edit_variations` | `bulk_edit_variations()` | `bulk-edit-variations` | Mass-update a property across all variations |
| `woocommerce_save_attributes` | `save_attributes()` | `save-attributes` | Save product attributes |
| `woocommerce_add_attribute` | `add_attribute()` | `add-attribute` | Add a new attribute row |

### `load_variations()` — Fetching Variations

This is called when the user first clicks the Variations tab (lazy loading):

```php
public static function load_variations() {
    check_ajax_referer( 'load-variations', 'security' );
    
    $product_id     = absint( $_POST['product_id'] );
    $product_object = wc_get_product( $product_id );
    $per_page       = ! empty( $_POST['per_page'] ) ? absint( $_POST['per_page'] ) : 10;
    $page           = ! empty( $_POST['page'] ) ? absint( $_POST['page'] ) : 1;
    
    $variations = wc_get_products( array(
        'status'  => array( 'private', 'publish' ),
        'type'    => 'variation',
        'parent'  => $product_id,
        'limit'   => $per_page,
        'page'    => $page,
        'orderby' => array(
            'menu_order' => 'ASC',
            'ID'         => 'DESC',
        ),
        'return'  => 'objects',
    ) );
    
    if ( $variations ) {
        wc_render_invalid_variation_notice( $product_object );
        
        foreach ( $variations as $variation_object ) {
            // Renders html-variation-admin.php for each variation
            self::render_variation_html( $product_object, $variation_object, $loop, $base_cost );
            ++$loop;
        }
    }
    wp_die(); // Response is the buffered HTML
}
```

Notice: the response is **raw HTML**, not JSON. The JavaScript simply injects it into the `.woocommerce_variations` container.

### `link_all_variations()` — Generating Variations

This is WooCommerce's variation generation. It delegates to the data store:

```php
public static function link_all_variations() {
    check_ajax_referer( 'link-variations', 'security' );
    
    // Cap at 50 variations per request
    wc_maybe_define_constant( 'WC_MAX_LINKED_VARIATIONS', 50 );
    wc_set_time_limit( 0 ); // Remove PHP time limit
    
    $product        = wc_get_product( intval( $_POST['post_id'] ) );
    $number_created = self::create_all_product_variations( $product );
    
    echo esc_html( $number_created ); // Returns just the count
    wp_die();
}

private static function create_all_product_variations( $product ) {
    $data_store = $product->get_data_store();
    $number = $data_store->create_all_product_variations(
        $product, 
        Constants::get_constant( 'WC_MAX_LINKED_VARIATIONS' ) // 50
    );
    $data_store->sort_all_product_variations( $product->get_id() );
    return $number;
}
```

The actual Cartesian product algorithm lives in the data store (`WC_Product_Variable_Data_Store_CPT`). It:

1. Gets all variation attributes and their options
2. Generates every possible combination (Cartesian product)
3. Checks if each combination already exists (to avoid duplicates)
4. Creates a `product_variation` post for each new combination
5. Stops at `WC_MAX_LINKED_VARIATIONS` (default: 50)

### `add_variation()` — Adding One Blank Variation

```php
public static function add_variation() {
    check_ajax_referer( 'add-variation', 'security' );
    
    $product_id       = intval( $_POST['post_id'] );
    $loop             = intval( $_POST['loop'] );
    $product_object   = wc_get_product_object( 'variable', $product_id );
    $variation_object = wc_get_product_object( 'variation' );
    
    $variation_object->set_parent_id( $product_id );
    
    // Set all variation attributes to empty (= "Any")
    $variation_object->set_attributes(
        array_fill_keys(
            array_map( 'sanitize_title', 
                array_keys( $product_object->get_variation_attributes() )
            ),
            ''
        )
    );
    
    $variation_object->save();
    
    // Render the HTML for this single new variation
    self::render_variation_html( $product_object, $variation_object, $loop, ... );
    wp_die();
}
```

### `remove_variations()` — Deleting Variations

```php
public static function remove_variations() {
    check_ajax_referer( 'delete-variations', 'security' );
    
    if ( current_user_can( 'edit_products' ) && isset( $_POST['variation_ids'] ) ) {
        $variation_ids = array_map( 'absint', (array) $_POST['variation_ids'] );
        
        foreach ( $variation_ids as $variation_id ) {
            if ( 'product_variation' === get_post_type( $variation_id ) ) {
                $variation = wc_get_product( $variation_id );
                $variation->delete( true ); // true = force delete (no trash)
            }
        }
    }
    wp_die( -1 );
}
```

---

## 9. JavaScript — The Frontend Brain

The entire frontend is managed by `meta-boxes-product-variation.js` (~1,750 lines). It's organized into 4 objects:

### Object 1: `wc_meta_boxes_product_variations_actions`

Handles UI interactions: checkbox toggling, sale schedule, sortable drag-and-drop, and menu order.

```javascript
var wc_meta_boxes_product_variations_actions = {
    init: function() {
        $( '#variable_product_options' )
            .on( 'change', 'input.variable_is_downloadable', this.variable_is_downloadable )
            .on( 'change', 'input.variable_is_virtual', this.variable_is_virtual )
            .on( 'change', 'input.variable_manage_stock', this.variable_manage_stock )
            .on( 'reload', this.reload );
            
        // When variations load, initialize UI components
        $( '#woocommerce-product-data' ).on(
            'woocommerce_variations_loaded', this.variations_loaded
        );
    },
    
    // Re-initialize after variations are loaded
    variations_loaded: function( event, needsUpdate ) {
        // Trigger checkbox change events to show/hide conditional fields
        $( 'input.variable_is_downloadable, ...' ).trigger( 'change' );
        
        // Open sale schedule if dates are set
        $( '.woocommerce_variation' ).each( function() {
            var date_from = $( '.sale_price_dates_from', this ).val();
            if ( '' !== date_from ) {
                $( 'a.sale_schedule', this ).trigger( 'click' );
            }
        });
        
        // Initialize datepickers on sale date fields
        $( '.sale_price_dates_fields input' ).datepicker({ ... });
        
        // Enable drag-and-drop sorting
        $( '.woocommerce_variations' ).sortable({
            items: '.woocommerce_variation',
            handle: '.sort',
            axis: 'y',
            stop: function() {
                // Update menu_order values after reorder
                wc_meta_boxes_product_variations_actions.variation_row_indexes();
            }
        });
        
        // Initialize tipTip tooltips and enhanced selects
        $( '.tips, .help_tip' ).tipTip({ ... });
        $( document.body ).trigger( 'wc-enhanced-select-init' );
    }
};
```

### Object 2: `wc_meta_boxes_product_variations_media`

Handles the `wp.media` integration for variation images:

```javascript
var wc_meta_boxes_product_variations_media = {
    variable_image_frame: null,
    
    add_image: function( event ) {
        var $button = $( this );
        
        if ( $button.is( '.remove' ) ) {
            // Remove image: clear the hidden input, reset to placeholder
            $( '.upload_image_id', ... ).val( '' ).trigger( 'change' );
            $button.find( 'img' ).attr( 'src', placeholder_src );
            $button.removeClass( 'remove' );
        } else {
            // Open media library
            var frame = wp.media({
                title: 'Choose an image',
                button: { text: 'Set variation image' },
                states: [ new wp.media.controller.Library({ filterable: 'all' }) ]
            });
            
            frame.on( 'select', function() {
                var attachment = frame.state().get( 'selection' ).first().toJSON();
                var url = attachment.sizes?.thumbnail?.url || attachment.url;
                
                $( '.upload_image_id', ... ).val( attachment.id ).trigger( 'change' );
                $button.find( 'img' ).attr( 'src', url );
                $button.addClass( 'remove' );
            });
            
            frame.open();
        }
    }
};
```

### Object 3: `wc_meta_boxes_product_variations_ajax`

This is the heaviest object. It handles all AJAX communication and the dirty-tracking/save logic.

#### Lazy Loading

Variations are loaded only when the tab is first clicked:

```javascript
initial_load: function() {
    // Only load if no variations are currently displayed
    if ( 0 === $( '.woocommerce_variations .woocommerce_variation' ).length ) {
        wc_meta_boxes_product_variations_pagenav.go_to_page();
    }
},
```

#### Dirty Tracking

When any input inside a variation changes, its parent `.woocommerce_variation` gets a CSS class:

```javascript
input_changed: function( event ) {
    $( this ).closest( '.woocommerce_variation' )
             .addClass( 'variation-needs-update' );
    
    $( 'button.cancel-variation-changes, button.save-variation-changes' )
        .prop( 'disabled', false );
},
```

This is WooCommerce's version of dirty tracking. Only rows with `.variation-needs-update` are included when saving.

#### Save Logic

```javascript
save_changes: function( callback ) {
    var wrapper     = $( '.woocommerce_variations' );
    var need_update = $( '.variation-needs-update', wrapper );
    
    // Only save rows that have been modified
    if ( 0 < need_update.length ) {
        // Serialize ONLY the dirty rows (not the entire form)
        var data = wc_meta_boxes_product_variations_ajax
                     .get_variations_fields( need_update );
        
        data.action     = 'woocommerce_save_variations';
        data.security   = woocommerce_admin_meta_boxes_variations.save_variations_nonce;
        data.product_id = woocommerce_admin_meta_boxes_variations.post_id;
        
        $.ajax({
            url: woocommerce_admin_meta_boxes_variations.ajax_url,
            data: data,
            type: 'POST',
            success: function( response ) {
                need_update.removeClass( 'variation-needs-update' );
                $( 'button.save-variation-changes' ).attr( 'disabled', 'disabled' );
                
                $( '#woocommerce-product-data' )
                    .trigger( 'woocommerce_variations_saved' );
                    
                if ( typeof callback === 'function' ) callback( response );
            }
        });
    }
},
```

#### Form Submit Integration

WooCommerce intercepts the normal WordPress post form submission to save variations first:

```javascript
save_on_submit: function( e ) {
    var need_update = $( '.woocommerce_variations .variation-needs-update' );
    
    if ( 0 < need_update.length ) {
        e.preventDefault(); // Stop the form submit
        
        // Save variations via AJAX first, then re-submit
        wc_meta_boxes_product_variations_ajax.save_changes(
            wc_meta_boxes_product_variations_ajax.save_on_submit_done
        );
    }
},

save_on_submit_done: function() {
    var postForm = $( 'form#post' );
    postForm.append( '<input type="hidden" name="save-post" value="1" />' )
            .trigger( 'submit' ); // Now submit the form normally
},
```

This is clever — it prevents data loss when the user clicks "Update" with unsaved variation changes.

#### Generate Variations

```javascript
link_all_variations: function() {
    if ( window.confirm( 'Do you want to generate all variations?...' ) ) {
        
        $.post( ajax_url, {
            action: 'woocommerce_link_all_variations',
            post_id: post_id,
            security: link_variation_nonce
        }, function( response ) {
            var count = parseInt( response, 10 ) || 0;
            
            // Show success notice using wp.data (Gutenberg notices API)
            window.wp.data.dispatch( 'core/notices' ).createSuccessNotice(
                count === 1 ? '1 variation added' : count + ' variations added',
                { icon: '🎉' }
            );
            
            if ( count > 0 ) {
                // Reload page 1 with the new variations
                wc_meta_boxes_product_variations_pagenav.go_to_page( 1, count );
            }
        });
    }
},
```

### Object 4: `wc_meta_boxes_product_variations_pagenav`

Handles pagination — updating page numbers, total counts, and navigation:

```javascript
var wc_meta_boxes_product_variations_pagenav = {
    go_to_page: function( page, qty ) {
        page = page || 1;
        // Update data-total, recalculate pages, show/hide nav buttons
        // Then call load_variations( page )
    },
    
    set_paginav: function( qty ) {
        // Update the "Showing X items" text
        // Update the page selector dropdown
        // Enable/disable first/prev/next/last buttons
    }
};
```

---

## 10. Saving Variations — The Full Form Submit Path

When the product is saved (either via the "Update" button or the variation "Save changes" button), variations are processed by `WC_Meta_Box_Product_Data::save_variations()`:

```php
public static function save_variations( $post_id, $post ) {
    if ( ! isset( $_POST['variable_post_id'] ) ) return;
    
    // 1. Save default attributes on the parent product
    $parent = wc_get_product( $post_id );
    $parent->set_default_attributes(
        self::prepare_set_attributes( $parent->get_attributes(), 'default_attribute_' )
    );
    $parent->save();
    
    // 2. Sort existing variations
    $data_store = $parent->get_data_store();
    $data_store->sort_all_product_variations( $parent->get_id() );
    
    // 3. Loop through each submitted variation
    $max_loop = max( array_keys( $_POST['variable_post_id'] ) );
    
    for ( $i = 0; $i <= $max_loop; $i++ ) {
        if ( ! isset( $_POST['variable_post_id'][ $i ] ) ) continue;
        
        $variation_id = absint( $_POST['variable_post_id'][ $i ] );
        $variation    = wc_get_product_object( 'variation', $variation_id );
        
        // 4. Set ALL properties at once using set_props()
        $errors = $variation->set_props( array(
            'status'            => isset( $_POST['variable_enabled'][ $i ] ) ? 'publish' : 'private',
            'menu_order'        => $_POST['variation_menu_order'][ $i ] ?? null,
            'regular_price'     => $_POST['variable_regular_price'][ $i ] ?? null,
            'sale_price'        => $_POST['variable_sale_price'][ $i ] ?? null,
            'virtual'           => isset( $_POST['variable_is_virtual'][ $i ] ),
            'downloadable'      => isset( $_POST['variable_is_downloadable'][ $i ] ),
            'manage_stock'      => isset( $_POST['variable_manage_stock'][ $i ] ),
            'stock_quantity'    => $stock,
            'stock_status'      => $_POST['variable_stock_status'][ $i ] ?? null,
            'backorders'        => $_POST['variable_backorders'][ $i ] ?? null,
            'image_id'          => $_POST['upload_image_id'][ $i ] ?? null,
            'sku'               => $_POST['variable_sku'][ $i ] ?? '',
            'weight'            => $_POST['variable_weight'][ $i ] ?? '',
            'length'            => $_POST['variable_length'][ $i ] ?? '',
            'width'             => $_POST['variable_width'][ $i ] ?? '',
            'height'            => $_POST['variable_height'][ $i ] ?? '',
            'description'       => $_POST['variable_description'][ $i ] ?? null,
            'shipping_class_id' => $_POST['variable_shipping_class'][ $i ] ?? null,
            'tax_class'         => $_POST['variable_tax_class'][ $i ] ?? null,
            'attributes'        => self::prepare_set_attributes(
                $parent->get_attributes(), 'attribute_', $i
            ),
            // ... date fields, download fields, etc.
        ) );
        
        // 5. Fire action hook for extensions
        do_action( 'woocommerce_admin_process_variation_object', $variation, $i );
        
        // 6. Persist to database
        $variation->save();
        
        do_action( 'woocommerce_save_product_variation', $variation_id, $i );
    }
}
```

### Stock Conflict Detection

WooCommerce includes a clever anti-conflict mechanism for stock:

```php
if ( isset( $_POST['variable_stock'][ $i ] ) ) {
    // Check if stock was modified by another process since the form was loaded
    if ( wc_stock_amount( $variation->get_stock_quantity( 'edit' ) ) 
         !== wc_stock_amount( $_POST['variable_original_stock'][ $i ] ) ) {
        
        WC_Admin_Meta_Boxes::add_error(
            'The stock has not been updated because the value has changed since editing.'
        );
    } else {
        $stock = wc_stock_amount( $_POST['variable_stock'][ $i ] );
    }
}
```

The `variable_original_stock` hidden field stores the stock value at the time the form was rendered. If it differs from the current database value when saving, someone else changed it — and WooCommerce refuses the update to prevent data loss.

---

## 11. Bulk Edit — Mass-Updating Variations

The bulk edit handler uses a dynamic dispatch pattern:

```php
public static function bulk_edit_variations() {
    check_ajax_referer( 'bulk-edit-variations', 'security' );
    
    $product_id  = absint( $_POST['product_id'] );
    $bulk_action = wc_clean( $_POST['bulk_action'] );
    $data        = wc_clean( $_POST['data'] ?? array() );
    
    // Get ALL variation IDs (not paginated)
    $variations = get_posts( array(
        'post_parent'    => $product_id,
        'posts_per_page' => -1,
        'post_type'      => 'product_variation',
        'fields'         => 'ids',
        'post_status'    => array( 'publish', 'private' ),
    ) );
    
    // Dynamic method dispatch
    if ( method_exists( __CLASS__, "variation_bulk_action_$bulk_action" ) ) {
        call_user_func(
            array( __CLASS__, "variation_bulk_action_$bulk_action" ),
            $variations, $data
        );
    } else {
        // Fallback: fire action hook for custom bulk actions
        do_action( 'woocommerce_bulk_edit_variations_default',
            $bulk_action, $data, $product_id, $variations );
    }
    
    // Always sync the parent product after bulk edits
    WC_Product_Variable::sync( $product_id );
    wc_delete_product_transients( $product_id );
    
    wp_die();
}
```

### Example Bulk Action Methods

```php
// Toggle enabled/disabled
private static function variation_bulk_action_toggle_enabled( $variations, $data ) {
    foreach ( $variations as $variation_id ) {
        $variation = wc_get_product( $variation_id );
        $variation->set_status(
            'private' === $variation->get_status( 'edit' ) ? 'publish' : 'private'
        );
        $variation->save();
    }
}

// Set regular price with percentage support
private static function variation_bulk_action_variable_regular_price( $variations, $data ) {
    // Handles fixed values and percentage increases/decreases
    // e.g., data.value = "10%" or data.value = "29.99"
}
```

### On the JavaScript Side

The bulk action JS uses a `switch` to determine what data to collect:

```javascript
do_variation_action: function() {
    var action = $( this ).val();
    
    switch ( action ) {
        case 'delete_all':
            // Double confirmation (two window.confirm dialogs!)
            break;
            
        case 'variable_regular_price':
        case 'variable_sale_price':
        case 'variable_stock':
            // Prompt for a value
            value = window.prompt( 'Enter a value' );
            data.value = value;
            break;
            
        case 'variable_regular_price_increase':
        case 'variable_regular_price_decrease':
            // Prompt for fixed amount or percentage
            value = window.prompt( 'Enter a value (fixed or %)' );
            // Parse percentage if present
            if ( value.indexOf( '%' ) >= 0 ) {
                data.value = accounting.unformat( value.replace( /%/, '' ) ) + '%';
            }
            break;
            
        case 'variable_sale_schedule':
            // Two prompts: start date and end date
            data.date_from = window.prompt( 'Enter start date (YYYY-MM-DD)' );
            data.date_to   = window.prompt( 'Enter end date (YYYY-MM-DD)' );
            break;
    }
    
    // Send to server
    $.ajax({
        url: ajax_url,
        data: {
            action: 'woocommerce_bulk_edit_variations',
            security: bulk_edit_variations_nonce,
            product_id: post_id,
            bulk_action: action,
            data: data
        },
        type: 'POST',
        success: function() {
            // Reload page 1 to show updated values
            wc_meta_boxes_product_variations_pagenav.go_to_page( 1, changes );
        }
    });
}
```

---

## 12. Variation Images — Media Library Integration

Each variation can have its own image, independent of the parent product. Here's the full flow:

### Template (PHP)

```php
<p class="form-row upload_image">
    <a href="#" class="upload_image_button tips <?php echo $has_image ? 'remove' : ''; ?>"
       data-tip="<?php echo $has_image ? 'Remove this image' : 'Upload an image'; ?>"
       rel="<?php echo $variation_id; ?>">
        <img src="<?php echo $image_url ?: wc_placeholder_img_src(); ?>" />
        <input type="hidden" 
               name="upload_image_id[<?php echo $loop; ?>]" 
               class="upload_image_id" 
               value="<?php echo $image_id; ?>" />
    </a>
</p>
```

### JavaScript Flow

1. **Click the image** → Opens `wp.media` frame
2. **Select an image** → Gets `attachment.id` and `attachment.sizes.thumbnail.url`
3. **Update hidden input** → Sets `upload_image_id[N]` to the attachment ID
4. **Trigger change** → `.trigger( 'change' )` marks the row as dirty
5. **Update thumbnail** → Swaps the `<img>` src
6. **Toggle class** → Adds `.remove` class to the button so next click removes

The image is saved as the variation's `image_id` property:

```php
'image_id' => isset( $_POST['upload_image_id'][ $i ] ) 
    ? wc_clean( $_POST['upload_image_id'][ $i ] ) 
    : null,
```

---

## 13. Default Attributes & Sorting

### Default Attributes

The "Default Form Values" section lets merchants pre-select attribute values. For example, setting the default color to "Blue" so new visitors see that variation first.

```php
// Template: html-product-data-variations.php

<div class="toolbar toolbar-variations-defaults">
    <div class="variations-defaults">
        <strong>Default Form Values:</strong>
        <?php foreach ( $variation_attributes as $attribute ) : ?>
            <?php $selected = $default_attributes[ sanitize_title( $attribute->get_name() ) ] ?? ''; ?>
            <select name="default_attribute_<?php echo sanitize_title( $attribute->get_name() ); ?>"
                    data-current="<?php echo $selected; ?>">
                <option value="">No default <?php echo wc_attribute_label( $attribute->get_name() ); ?>…</option>
                
                <?php if ( $attribute->is_taxonomy() ) : ?>
                    <?php foreach ( $attribute->get_terms() as $option ) : ?>
                        <option value="<?php echo $option->slug; ?>"
                                <?php selected( $selected, $option->slug ); ?>>
                            <?php echo $option->name; ?>
                        </option>
                    <?php endforeach; ?>
                <?php else : ?>
                    <?php foreach ( $attribute->get_options() as $option ) : ?>
                        <option value="<?php echo $option; ?>"
                                <?php selected( $selected, $option ); ?>>
                            <?php echo $option; ?>
                        </option>
                    <?php endforeach; ?>
                <?php endif; ?>
            </select>
        <?php endforeach; ?>
    </div>
</div>
```

Default attribute changes are serialized along with the variation data when saving. The `data-current` attribute is used to detect changes and implement the "Cancel" button.

### Sorting (Drag-and-Drop + Manual)

WooCommerce offers two ways to reorder variations:

1. **Drag and drop** — jQuery UI Sortable on `.woocommerce_variations`. When you drop a row, the JS recalculates `menu_order` values:

```javascript
$( '.woocommerce_variations' ).sortable({
    items: '.woocommerce_variation',
    handle: '.sort',    // The drag handle icon
    axis: 'y',
    stop: function() {
        // Recalculate menu_order based on DOM position
        $( '.woocommerce_variation' ).each( function( index, el ) {
            $( '.variation_menu_order', el )
                .val( index + 1 + offset )  // offset = (page - 1) * per_page
                .trigger( 'change' );
        });
    }
});
```

2. **Manual menu order** — Click the sort icon to enter a number via `window.prompt()`:

```javascript
set_menu_order: function( event ) {
    var value = window.prompt( 'Variation menu order...', currentValue );
    
    if ( value != null ) {
        $menu_order.val( parseInt( value, 10 ) ).trigger( 'change' );
        
        // Append hidden fields so the server can reorder
        $variation.append( '<input type="hidden" name="new_variation_menu_order_id" value="' + variationId + '" />' );
        $variation.append( '<input type="hidden" name="new_variation_menu_order_value" value="' + value + '" />' );
        
        // Save immediately
        wc_meta_boxes_product_variations_ajax.save_variations();
    }
}
```

On the server side, `save_variations()` handles the reordering:

```php
// Increment menu_order for all variations at or after the new position
$wpdb->query( $wpdb->prepare(
    "UPDATE {$wpdb->posts} SET menu_order = menu_order + 1 
     WHERE post_type = 'product_variation' 
     AND post_parent = %d 
     AND menu_order >= %d 
     AND ID != %d",
    $post_id, $new_value, $new_id
) );
```

---

## 14. Script Enqueuing & Localization

The variation JS is enqueued in `WC_Admin_Assets`:

```php
// File: includes/admin/class-wc-admin-assets.php

wp_register_script(
    'wc-admin-variation-meta-boxes',
    WC()->plugin_url() . '/assets/js/admin/meta-boxes-product-variation.js',
    array(
        'wc-admin-meta-boxes',  // Parent meta box JS
        'wc-serializejson',     // jQuery serializeJSON plugin
        'media-models',         // WordPress media library
        'backbone',             // Backbone.js (for modals)
        'jquery-ui-sortable',   // Drag-and-drop
        'wc-backbone-modal',    // WooCommerce modal component
        'wp-data',              // Gutenberg data layer (for notices)
        'wp-notices',           // WordPress notices
    ),
    $version
);
```

### Localized Data

A large configuration object is passed from PHP → JS via `wp_localize_script`:

```php
wp_localize_script( 'wc-admin-variation-meta-boxes', 'woocommerce_admin_meta_boxes_variations', array(
    'ajax_url'                    => admin_url( 'admin-ajax.php' ),
    'post_id'                     => $post->ID,
    
    // Nonces (one per action)
    'add_variation_nonce'         => wp_create_nonce( 'add-variation' ),
    'link_variation_nonce'        => wp_create_nonce( 'link-variations' ),
    'delete_variations_nonce'     => wp_create_nonce( 'delete-variations' ),
    'load_variations_nonce'       => wp_create_nonce( 'load-variations' ),
    'save_variations_nonce'       => wp_create_nonce( 'save-variations' ),
    'bulk_edit_variations_nonce'  => wp_create_nonce( 'bulk-edit-variations' ),
    
    // Pagination config
    'variations_per_page'         => absint( apply_filters(
        'woocommerce_admin_meta_boxes_variations_per_page', 15
    ) ),
    
    // Images
    'woocommerce_placeholder_img_src' => wc_placeholder_img_src(),
    
    // i18n strings
    'i18n_link_all_variations'    => 'Do you want to generate all variations?... (max 50 per run)',
    'i18n_remove_variation'       => 'Are you sure you want to remove this variation?',
    'i18n_delete_all_variations'  => 'Are you sure you want to delete all variations?...',
    'i18n_last_warning'           => 'Last warning, are you sure?',
    'i18n_choose_image'           => 'Choose an image',
    'i18n_set_image'              => 'Set variation image',
    'i18n_enter_a_value'          => 'Enter a value',
    'i18n_edited_variations'      => 'Save changes before changing page?',
    'i18n_variation_added'        => '1 variation added',
    'i18n_variations_added'       => '%qty% variations added',
    // ... and more
) );
```

---

## 15. Extensibility — Action & Filter Hooks

WooCommerce provides dozens of hooks throughout the variation system. Here are the most important ones:

### Action Hooks

| Hook | Where | Use Case |
|------|-------|----------|
| `woocommerce_product_data_tabs` | Tab registration | Add custom tabs or modify existing ones |
| `woocommerce_variable_product_before_variations` | Before variation list | Add content above variations |
| `woocommerce_variation_header` | Variation header | Add elements to the collapsed header |
| `woocommerce_variation_options` | After checkboxes | Add custom variation-level toggles |
| `woocommerce_variation_options_pricing` | After price fields | Add custom pricing fields |
| `woocommerce_variation_options_inventory` | After stock fields | Add custom inventory fields |
| `woocommerce_variation_options_dimensions` | After dimensions | Add custom physical fields |
| `woocommerce_variation_options_tax` | After tax class | Add custom tax fields |
| `woocommerce_variation_options_download` | After download fields | Add custom download fields |
| `woocommerce_product_after_variable_attributes` | End of variation body | Add custom sections to each variation |
| `woocommerce_variable_product_bulk_edit_actions` | Bulk actions dropdown | Add custom bulk actions |
| `woocommerce_admin_process_variation_object` | Before variation save | Modify variation object before save |
| `woocommerce_save_product_variation` | After variation save | Post-save processing |
| `woocommerce_ajax_save_product_variations` | After all variations saved | Batch post-save processing |
| `woocommerce_bulk_edit_variations_default` | Bulk edit fallback | Handle custom bulk edit actions |
| `woocommerce_bulk_edit_variations` | After any bulk edit | Post-bulk-edit processing |

### Filter Hooks

| Hook | Purpose |
|------|---------|
| `woocommerce_admin_meta_boxes_variations_per_page` | Change pagination (default: 15) |
| `woocommerce_admin_meta_boxes_variations_count` | Override variation count |
| `woocommerce_admin_meta_boxes_prepare_attribute` | Modify attribute before save |
| `woocommerce_variation_option_name` | Customize attribute option display names |
| `woocommerce_attribute_default_visibility` | Default visibility for new attributes |
| `woocommerce_attribute_default_is_variation` | Default "Used for variations" for new attributes |
| `woocommerce_bulk_edit_variations_need_children` | Skip loading children for custom bulk actions |

### Example: Adding a Custom Field to Each Variation

```php
// Add the field
add_action( 'woocommerce_variation_options_pricing', function( $loop, $variation_data, $variation ) {
    woocommerce_wp_text_input( array(
        'id'            => "my_custom_field_{$loop}",
        'name'          => "my_custom_field[{$loop}]",
        'value'         => get_post_meta( $variation->ID, '_my_custom_field', true ),
        'label'         => __( 'My Custom Field', 'my-plugin' ),
        'wrapper_class' => 'form-row form-row-full',
    ) );
}, 10, 3 );

// Save the field
add_action( 'woocommerce_save_product_variation', function( $variation_id, $i ) {
    if ( isset( $_POST['my_custom_field'][ $i ] ) ) {
        update_post_meta( $variation_id, '_my_custom_field',
            wc_clean( wp_unslash( $_POST['my_custom_field'][ $i ] ) )
        );
    }
}, 10, 2 );
```

### Example: Adding a Custom Bulk Action

```php
// Add to dropdown
add_action( 'woocommerce_variable_product_bulk_edit_actions', function() {
    ?>
    <optgroup label="<?php esc_attr_e( 'Custom', 'my-plugin' ); ?>">
        <option value="my_custom_bulk_action">
            <?php esc_html_e( 'Set my custom value', 'my-plugin' ); ?>
        </option>
    </optgroup>
    <?php
} );

// Handle the action
add_action( 'woocommerce_bulk_edit_variations_default', function( $bulk_action, $data, $product_id, $variations ) {
    if ( 'my_custom_bulk_action' !== $bulk_action ) return;
    
    foreach ( $variations as $variation_id ) {
        update_post_meta( $variation_id, '_my_custom_field', wc_clean( $data['value'] ) );
    }
}, 10, 4 );
```

---

## 16. File Structure Summary

Here's the complete file map for WooCommerce's variation system:

```
woocommerce/
├── includes/
│   ├── class-wc-ajax.php                           # All AJAX handlers
│   ├── class-wc-product-attribute.php               # WC_Product_Attribute data class
│   ├── class-wc-product-variable.php                # WC_Product_Variable (parent product)
│   ├── class-wc-product-variation.php               # WC_Product_Variation (child variation)
│   ├── admin/
│   │   ├── class-wc-admin-assets.php                # Script enqueuing + localization
│   │   ├── class-wc-admin-meta-boxes.php            # Meta box registration
│   │   └── meta-boxes/
│   │       ├── class-wc-meta-box-product-data.php   # Product Data meta box controller
│   │       └── views/
│   │           ├── html-product-data-panel.php      # Meta box shell (tabs + product type)
│   │           ├── html-product-data-attributes.php # Attributes tab content
│   │           ├── html-product-attribute.php       # Single attribute accordion wrapper
│   │           ├── html-product-attribute-inner.php # Attribute row inner content
│   │           ├── html-product-data-variations.php # Variations tab (toolbar + container)
│   │           └── html-variation-admin.php         # Single variation accordion row
│   └── data-stores/
│       └── class-wc-product-variable-data-store-cpt.php # Data store (DB queries, generation algo)
├── assets/
│   └── js/
│       └── admin/
│           ├── meta-boxes-product-variation.js      # All variation JavaScript (~1750 lines)
│           └── meta-boxes-product-variation.min.js  # Minified version
```

### AJAX Actions Map

| JavaScript Action | PHP Method | Template Response |
|---|---|---|
| `woocommerce_load_variations` | `WC_AJAX::load_variations()` | Raw HTML (variation rows) |
| `woocommerce_save_variations` | `WC_AJAX::save_variations()` | Error HTML or empty |
| `woocommerce_add_variation` | `WC_AJAX::add_variation()` | Raw HTML (single row) |
| `woocommerce_link_all_variations` | `WC_AJAX::link_all_variations()` | Plain text (count) |
| `woocommerce_remove_variations` | `WC_AJAX::remove_variations()` | Empty |
| `woocommerce_bulk_edit_variations` | `WC_AJAX::bulk_edit_variations()` | Empty |
| `woocommerce_save_attributes` | `WC_AJAX::save_attributes()` | JSON (`{html: "..."}`) |
| `woocommerce_add_attribute` | `WC_AJAX::add_attribute()` | Raw HTML (attribute row) |
| `woocommerce_add_attributes_and_variations` | `WC_AJAX::add_attributes_and_variations()` | JSON success |

---

## 17. Wrapping Up — Key Takeaways

### Architecture Decisions Worth Noting

1. **Server-Side HTML Rendering** — Unlike many modern SPAs, WooCommerce renders variation HTML on the server and sends it back via AJAX. This makes the system template-overridable but means no true client-side state management.

2. **Lazy Loading** — Variations are only loaded when the user clicks the Variations tab. This prevents slow page loads for products with hundreds of variations.

3. **Dirty Tracking via CSS Classes** — Instead of maintaining a JavaScript state object, WooCommerce uses the `.variation-needs-update` CSS class. Only elements with this class are serialized and sent to the server. Simple but effective.

4. **Form Submit Interception** — The JS intercepts the WordPress post form submission, saves variations via AJAX first, then re-submits the form. This prevents data loss.

5. **50-Variation Cap** — `WC_MAX_LINKED_VARIATIONS` is hardcoded at 50. This prevents PHP timeouts during Cartesian product generation. If a product has more than 50 possible combinations, the user must run "Generate variations" multiple times.

6. **Stock Conflict Detection** — The `variable_original_stock` hidden field prevents race conditions when multiple admins edit the same product simultaneously.

7. **Dynamic Bulk Action Dispatch** — The `call_user_func( "variation_bulk_action_$bulk_action" )` pattern makes it easy to add new bulk actions via method naming convention.

8. **Extensive Hook System** — Nearly every step in the process has action and filter hooks, making the variation system highly extensible without modifying core code.

### Common Extension Patterns

- **Add a custom field** → Hook into `woocommerce_product_after_variable_attributes` (render) + `woocommerce_save_product_variation` (save)
- **Add a bulk action** → Hook into `woocommerce_variable_product_bulk_edit_actions` (dropdown) + `woocommerce_bulk_edit_variations_default` (handler)
- **Modify variation data before save** → Hook into `woocommerce_admin_process_variation_object`
- **Change pagination** → Filter `woocommerce_admin_meta_boxes_variations_per_page`
- **Custom attribute handling** → Filter `woocommerce_admin_meta_boxes_prepare_attribute`

### What Makes This System Complex

The interaction between attributes and variations is the source of most complexity. An attribute must be:
1. Created (either as a taxonomy or custom)
2. Assigned to the product
3. Have the "Used for variations" checkbox checked
4. Have values assigned
5. Then variations can be generated as combinations of those values

Any break in this chain — and you'll see the empty state message instead of your variations.

---

> **Source**: This guide is based on code analysis of WooCommerce core (version 9.x), specifically the files under `includes/admin/meta-boxes/`, `includes/class-wc-ajax.php`, and `assets/js/admin/meta-boxes-product-variation.js`.
