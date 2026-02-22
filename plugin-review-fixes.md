# StoreSuite - WordPress.org Plugin Review Fixes

This document lists all issues found based on the WordPress.org plugin review feedback. Each section corresponds to a review category with specific file locations and required fixes.

---

## Table of Contents

1. [Proper Sanitization of Inputs](#1-proper-sanitization-of-inputs)
2. [Proper Escaping of Outputs](#2-proper-escaping-of-outputs)
3. [Nonces and User Permissions Before Processing Requests](#3-nonces-and-user-permissions-before-processing-requests)
4. [Use Prefixes for Declarations, Globals, and Stored Data](#4-use-prefixes-for-declarations-globals-and-stored-data)
5. [Use `wp_enqueue` Commands](#5-use-wp_enqueue-commands)
6. [Other Issues](#6-other-issues)

---

## 1. Proper Sanitization of Inputs

All data from `$_POST`, `$_GET`, `$_REQUEST`, `$_SERVER`, `$_FILES` must be sanitized with the appropriate function. While many inputs are already sanitized, the following need fixes:

### 1.1 `includes/Order/OrderController.php`

**Line 151** - `$_POST['note_id']` is cast with `(int)` but should use `absint()` for consistency and safety:
```php
// Current:
$note_id = (int) $_POST['note_id'];

// Fix:
$note_id = isset( $_POST['note_id'] ) ? absint( $_POST['note_id'] ) : 0;
```

**Line 188** - `$_POST['shipping_cost']` uses `floatval()` without `wp_unslash()`:
```php
// Current:
$shipping_cost = isset( $_POST['shipping_cost'] ) ? floatval( $_POST['shipping_cost'] ) : 0;

// Fix:
$shipping_cost = isset( $_POST['shipping_cost'] ) ? (float) sanitize_text_field( wp_unslash( $_POST['shipping_cost'] ) ) : 0;
```

**Line 389** - `$_POST['context']` is accessed without sanitization:
```php
// Current:
if( isset( $_POST['context'] ) && $_POST['context'] === 'add' ) {

// Fix:
if ( isset( $_POST['context'] ) && sanitize_text_field( wp_unslash( $_POST['context'] ) ) === 'add' ) {
```

**Line 415** - `$_POST['storesuite_bulk_action_nonce']` is missing `sanitize_key()` wrapping in `wp_verify_nonce()`:
```php
// Current:
if ( ! wp_verify_nonce( wp_unslash( $_POST['storesuite_bulk_action_nonce'] ), 'storesuite_order_bulk_action' ) ) {

// Fix:
if ( ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['storesuite_bulk_action_nonce'] ) ), 'storesuite_order_bulk_action' ) ) {
```

**Line 420** - `$_POST['bulk_order_ids']` is not unslashed before sanitizing:
```php
// Current:
$order_ids = array_map( 'absint', $_POST['bulk_order_ids'] );

// Fix:
$order_ids = array_map( 'absint', wp_unslash( $_POST['bulk_order_ids'] ) );
```

### 1.2 `includes/ProductBrand/BrandController.php`

**Line 185** - Uses `intval()` instead of `absint()`:
```php
// Current:
$brand_id = isset( $_POST['id'] ) ? intval( $_POST['id'] ) : 0;

// Fix:
$brand_id = isset( $_POST['id'] ) ? absint( $_POST['id'] ) : 0;
```

### 1.3 `includes/ProductTag/TagController.php`

**Line 155** - Uses `intval()` instead of `absint()`:
```php
// Current:
$tag_id = isset( $_POST['id'] ) ? intval( $_POST['id'] ) : 0;

// Fix:
$tag_id = isset( $_POST['id'] ) ? absint( $_POST['id'] ) : 0;
```

### 1.4 `includes/ProductCategory/CategoryController.php`

**Line 199** - Uses `intval()` instead of `absint()`:
```php
// Current:
$category_id = isset( $_POST['id'] ) ? intval( $_POST['id'] ) : 0;

// Fix:
$category_id = isset( $_POST['id'] ) ? absint( $_POST['id'] ) : 0;
```

### 1.5 `includes/Order/CreateNewOrder.php`

**Line 142** - `$_GET['claim-lock']` and `$_GET['_wpnonce']` are used without proper sanitization. The phpcs ignore comment confirms this is a known issue:
```php
// Current (with phpcs:ignore):
if ( ! empty( $_GET['claim-lock'] ) && wp_verify_nonce( $_GET['_wpnonce'] ?? '', 'claim-lock-' . $this->order->get_id() ) ) {

// Fix:
$claim_lock = isset( $_GET['claim-lock'] ) ? sanitize_text_field( wp_unslash( $_GET['claim-lock'] ) ) : '';
$wpnonce    = isset( $_GET['_wpnonce'] ) ? sanitize_key( wp_unslash( $_GET['_wpnonce'] ) ) : '';
if ( ! empty( $claim_lock ) && wp_verify_nonce( $wpnonce, 'claim-lock-' . $this->order->get_id() ) ) {
```

### 1.6 `includes/Product/ProductController.php`

**Lines 98 & 135** - The entire `$_POST` array is passed directly to `ProductManager->storesuite_save_product()` without any sanitization at the point of passing. While `ProductManager` does some internal sanitization, the raw `$_POST` is passed:
```php
// Current:
$response = ( new ProductManager() )->storesuite_save_product( $_POST );

// Fix: Either sanitize each field before passing, or at minimum unslash:
$response = ( new ProductManager() )->storesuite_save_product( wp_unslash( $_POST ) );
```
> **Note:** Same issue in `includes/Coupon/CouponController.php` lines 96 and 151 where `$_POST` is passed directly to `CouponManager`.

### 1.7 `includes/functions.php`

**Line 22** - Uses `extract()` which is strongly discouraged by WordPress coding standards:
```php
// Current:
if ( $args && is_array( $args ) ) {
    extract( $args ); // phpcs:ignore
}

// Fix: Remove extract() and pass $args to templates directly or access via array keys.
```

### 1.8 `templates/orders/orders.php`

**Line 68** - `$_REQUEST['search-filter']` uses null coalescing without explicit `isset()` check and without `wp_unslash()` before sanitization:
```php
// Current:
$selected = sanitize_text_field( wp_unslash( $_REQUEST['search-filter'] ?? $saved_setting ) );

// Fix:
$selected = isset( $_REQUEST['search-filter'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['search-filter'] ) ) : $saved_setting;
```

---

## 2. Proper Escaping of Outputs

All output must be escaped with the appropriate function (`esc_html()`, `esc_attr()`, `esc_url()`, `wp_kses_post()`, etc.). The WordPress.org review team flags uses of `echo` without escaping.

### 2.1 `templates/dashboard/store-performance.php`

**Line 55** - `$dashboard->format_value()` output is not escaped (uses `phpcs:ignore`):
```php
// Current:
<?php echo $dashboard->format_value( $value, $stat_config['format'] ?? 'number' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>

// Fix: The format_value() method in Dashboard.php already uses wp_kses_post() and esc_html() internally.
// Change to:
<?php echo wp_kses_post( $dashboard->format_value( $value, $stat_config['format'] ?? 'number' ) ); ?>
```

### 2.2 `templates/dashboard/top-products-items-sold.php`

**Line 68** - Same `format_value()` escaping issue:
```php
// Current:
echo $dashboard->format_value( $row['net_revenue'] ?? null, 'currency' ); // phpcs:ignore

// Fix:
echo wp_kses_post( $dashboard->format_value( $row['net_revenue'] ?? null, 'currency' ) );
```

### 2.3 `templates/dashboard/top-categories-items-sold.php`

**Line 72** - Same `format_value()` escaping issue:
```php
// Current:
echo $dashboard->format_value( $row['net_revenue'] ?? null, 'currency' ); // phpcs:ignore

// Fix:
echo wp_kses_post( $dashboard->format_value( $row['net_revenue'] ?? null, 'currency' ) );
```

### 2.4 `templates/dashboard/top-customers-total-spend.php`

**Line 74** - Same `format_value()` escaping issue:
```php
// Current:
echo $dashboard->format_value( $row['total_spend'] ?? null, 'currency' ); // phpcs:ignore

// Fix:
echo wp_kses_post( $dashboard->format_value( $row['total_spend'] ?? null, 'currency' ) );
```

### 2.5 `templates/dashboard/top-coupons-orders-count.php`

**Line 68** - Same `format_value()` escaping issue:
```php
// Current:
echo $dashboard->format_value( $row['amount'] ?? null, 'currency' ); // phpcs:ignore

// Fix:
echo wp_kses_post( $dashboard->format_value( $row['amount'] ?? null, 'currency' ) );
```

### 2.6 `includes/Main.php`

**Line 214** - CSS variables output uses `implode()` with `phpcs:ignore`. The values come from `esc_attr()` but the final `implode()` output is not escaped:
```php
// Current:
:root{ <?php echo implode( ";", $rules ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> }

// Fix: Each $rules value is already escaped with esc_attr(). Wrap the output:
:root{ <?php echo esc_attr( implode( ";", $rules ) ); ?> }
```

### 2.7 `templates/orders/order-filters-offcanvas.php`

**Line 84** - Uses `htmlspecialchars()` which is not a WordPress escaping function:
```php
// Current:
<?php echo htmlspecialchars( wp_kses_post( $user_string ) ); // phpcs:ignore ?>

// Fix:
<?php echo esc_html( wp_kses_post( $user_string ) ); ?>
```

### 2.8 `templates/coupons/coupon-form.php`

**Line 85** - Form `id` attribute is echoed without escaping:
```php
// Current:
<form id="<?php echo $is_edit_mode ? 'msf-edit-coupon' : 'msf-add-coupon'; ?>">

// Fix:
<form id="<?php echo esc_attr( $is_edit_mode ? 'msf-edit-coupon' : 'msf-add-coupon' ); ?>">
```

### 2.9 `includes/StoreSuite.php`

**Line 131** - The `plugin_action_links()` output should escape the `__()` translation:
```php
// Current:
$links[] = '<a href="' . admin_url( 'admin.php?page=storesuite' ) . '">' . __( 'Settings', 'storesuite' ) . '</a>';

// Fix:
$links[] = '<a href="' . esc_url( admin_url( 'admin.php?page=storesuite' ) ) . '">' . esc_html__( 'Settings', 'storesuite' ) . '</a>';
```

---

## 3. Nonces and User Permissions Before Processing Requests

### 3.1 Missing AJAX Handler for Product Delete

**File: `templates/products/products.php` (Line 194)**

The product delete form references `action=storesuite_wfm_trash_product_action`, but there is **no corresponding `wp_ajax_storesuite_wfm_trash_product_action` handler** registered anywhere in the codebase. This AJAX action does not exist, meaning the delete product functionality is broken.

**Required Fix:**
- Create a proper AJAX handler in `ProductController.php` for `wp_ajax_storesuite_wfm_trash_product_action` with nonce verification and permission check.
- Or rename to use a properly registered action.

### 3.2 Order Bulk Actions - Missing Permission Check

**File: `includes/Order/OrderController.php` (Lines 408-462)**

The `handle_order_bulk_actions()` method verifies the nonce but does **NOT** check user permissions with `current_user_can()`:
```php
// Add after nonce verification:
if ( ! current_user_can( 'manage_woocommerce' ) ) {
    wp_safe_redirect( storesuite_get_navigation_url( 'orders' ) );
    exit;
}
```

### 3.3 `$_GET` Usage Without Nonce in Templates (NonceVerification.Recommended)

The following files access `$_GET` parameters without nonce verification. While this is acceptable for read-only filter/search operations (hence `NonceVerification.Recommended` vs `NonceVerification.Missing`), the WordPress.org review team may still flag these. Consider adding a nonce to search/filter forms or document why nonces are not needed for read-only operations:

| File | Lines | Parameters |
|------|-------|------------|
| `templates/products/products.php` | 34, 65-74 | `search_by`, `product_cat`, `product_type`, `stock_status`, `product_brand` |
| `templates/products/product-filters.php` | 14-18 | `product_cat`, `product_type`, `stock_status`, `product_brand`, `search_by` |
| `templates/products/product-filters-offcanvas.php` | 14-18 | Same as above |
| `templates/orders/orders.php` | 48, 68, 149-154 | `search_by`, `search-filter`, `order_status`, `_customer_user`, `order_channel`, `m` |
| `templates/categories/categories.php` | 34, 51 | `search_by` |
| `templates/tags/tags.php` | 34, 51 | `search_by` |
| `templates/dashboard.php` | 24-25 | `storesuite_dashboard_start`, `storesuite_dashboard_end` |
| `includes/Dashboard.php` | 318-319 | `storesuite_dashboard_start`, `storesuite_dashboard_end` |
| `includes/TemplateParts.php` | 46 | `action` |

---

## 4. Use Prefixes for Declarations, Globals, and Stored Data

### 4.1 Unprefixed Global Function

**File: `includes/functions.php` (Line 271)**

The function `is_storesuite_dashboard_page()` does not follow the `storesuite_` prefix pattern:
```php
// Current:
function is_storesuite_dashboard_page() {

// Fix (option A - rename):
function storesuite_is_dashboard_page() {

// Fix (option B - if keeping the name, it's technically prefixed with "is_storesuite_"
// which could be accepted, but standard practice is prefix first)
```

> **Note:** If you rename this function, update all references across the codebase (found in: `includes/Main.php`, `includes/Common.php`, `includes/Assets.php`, `includes/TemplateParts.php`, `includes/Shortcodes/Dashboard.php`, `templates/dashboard.php`, `includes/Dashboard.php`).

### 4.2 Unprefixed Global Function (Edge Case)

**File: `includes/functions.php` (Line 59-83)**

The function `is_storesuite_endpoint_url()` has the same naming concern as above. While it contains "storesuite" in the name, the prefix should come first.

### 4.3 Hardcoded Nonce Salt

**File: `includes/StoreSuite.php` (Line 161)**

A nonce salt is hardcoded as a constant. This is not needed because WordPress has its own nonce system:
```php
// Current:
defined( 'STORESUITE_NONCE_SALT' ) || define( 'STORESUITE_NONCE_SALT', 'iG685CuXEZI2J?@~-t 3v)*_z]e,+CXh/Mu#8Fq4W<B^w9m^c]C8XGn(V~#:dn%C' );

// Fix: Remove this constant if not used, or if needed, generate it dynamically.
// The WordPress.org review team may flag hardcoded secrets.
```

> **Note:** Search the codebase - `STORESUITE_NONCE_SALT` does not appear to be used anywhere else. Remove it.

### 4.4 CSS Class Prefixes

While not strictly required by the review, many CSS classes use `msf-` or `msfc-` prefix. Ensure consistency across the plugin. The main class names should ideally use the `storesuite-` prefix for clarity.

---

## 5. Use `wp_enqueue` Commands

### 5.1 Inline `<style>` Tags in `includes/Main.php`

**Lines 156-176** - The `add_storesuite_dashboard_btn_css()` method outputs inline `<style>` tags directly via `wp_head`. This should use `wp_add_inline_style()` with a registered stylesheet handle:

```php
// Current (in wp_head hook):
?>
<style>
    a.storesuite-dashboard-btn.my-storesuite-button { ... }
</style>
<?php

// Fix: Use wp_add_inline_style() attached to an enqueued stylesheet:
wp_add_inline_style( 'storesuite_style', '
    a.storesuite-dashboard-btn.my-storesuite-button { ... }
');
```

**Lines 212-216** - The `add_storesuite_css_variables()` method outputs inline `<style>` for CSS custom properties. Same fix needed:
```php
// Fix: Use wp_add_inline_style() instead of inline <style> tag
```

### 5.2 External Google Fonts

**File: `includes/Assets.php` (Line 125)**

Loading Google Fonts via external CDN URL:
```php
wp_register_style( 'storesuite_poppins', 'https://fonts.googleapis.com/css2?family=Poppins:...', array(), STORESUITE_PLUGIN_VERSION );
```

The WordPress.org review may flag loading resources from external CDNs. Consider:
- Bundling the font files locally within the plugin's `assets/` directory, OR
- Documenting why the external load is necessary and that it's loaded only on the plugin dashboard page.

---

## 6. Other Issues

### 6.1 Use of `extract()` in Template Loading

**File: `includes/functions.php` (Line 22)**

`extract()` is explicitly discouraged by WordPress coding standards. It creates variables dynamically from array keys, making code harder to read and potentially introducing security issues:
```php
// Current:
if ( $args && is_array( $args ) ) {
    extract( $args ); // phpcs:ignore
}

// Fix: Remove extract() and access variables via $args array in templates.
// In template files, replace $variable with $args['variable'].
```

### 6.2 Missing Product Delete AJAX Handler

As noted in section 3.1, the `storesuite_wfm_trash_product_action` AJAX action referenced in `templates/products/products.php` has no registered handler. This is a critical missing feature.

### 6.3 `$_POST` Data Passed Unsanitized to Manager Classes

**Files affected:**
- `includes/Product/ProductController.php` (Lines 98, 135) - passes raw `$_POST` to `ProductManager`
- `includes/Coupon/CouponController.php` (Lines 96, 151) - passes raw `$_POST` to `CouponManager`

While the Manager classes do some internal sanitization, the raw `$_POST` superglobal should be sanitized at the controller level before being passed.

### 6.4 Remove All `phpcs:ignore` Comments Related to Escaping/Sanitization

The WordPress.org review team specifically looks for `phpcs:ignore` comments that suppress security warnings. All of these should be addressed rather than suppressed:

| File | Line | Suppressed Rule |
|------|------|----------------|
| `includes/Main.php` | 214 | `WordPress.Security.EscapeOutput.OutputNotEscaped` |
| `templates/dashboard/store-performance.php` | 55 | `WordPress.Security.EscapeOutput.OutputNotEscaped` |
| `templates/dashboard/top-products-items-sold.php` | 68 | `WordPress.Security.EscapeOutput.OutputNotEscaped` |
| `templates/dashboard/top-categories-items-sold.php` | 72 | `WordPress.Security.EscapeOutput.OutputNotEscaped` |
| `templates/dashboard/top-customers-total-spend.php` | 74 | `WordPress.Security.EscapeOutput.OutputNotEscaped` |
| `templates/dashboard/top-coupons-orders-count.php` | 68 | `WordPress.Security.EscapeOutput.OutputNotEscaped` |
| `templates/orders/order-filters-offcanvas.php` | 84 | `WordPress.Security.EscapeOutput.OutputNotEscaped` |
| `includes/Order/CreateNewOrder.php` | 142 | `WordPress.Security.ValidatedSanitizedInput.InputNotSanitized` |

### 6.5 WordPress Description Mention

As noted in the review email, make sure the plugin description adequately explains the plugin functionality so that reviewers can test it properly. Include clear setup instructions in the `readme.txt`.

---

## Summary of Changes by File

| File | Issues |
|------|--------|
| `includes/functions.php` | Remove `extract()`, consider renaming `is_storesuite_dashboard_page()` |
| `includes/Main.php` | Replace inline `<style>` with `wp_add_inline_style()`, fix CSS variable output escaping |
| `includes/StoreSuite.php` | Remove unused `STORESUITE_NONCE_SALT`, escape `plugin_action_links` output |
| `includes/Order/OrderController.php` | Fix unsanitized `$_POST['note_id']`, `$_POST['shipping_cost']`, `$_POST['context']`, nonce sanitization, add permission check for bulk actions |
| `includes/Order/CreateNewOrder.php` | Sanitize `$_GET['claim-lock']` and `$_GET['_wpnonce']` |
| `includes/Product/ProductController.php` | Don't pass raw `$_POST` to ProductManager |
| `includes/Coupon/CouponController.php` | Don't pass raw `$_POST` to CouponManager |
| `includes/ProductBrand/BrandController.php` | Use `absint()` instead of `intval()` |
| `includes/ProductTag/TagController.php` | Use `absint()` instead of `intval()` |
| `includes/ProductCategory/CategoryController.php` | Use `absint()` instead of `intval()` |
| `includes/Assets.php` | Consider bundling Google Fonts locally |
| `templates/dashboard/store-performance.php` | Wrap `format_value()` with `wp_kses_post()` |
| `templates/dashboard/top-products-items-sold.php` | Wrap `format_value()` with `wp_kses_post()` |
| `templates/dashboard/top-categories-items-sold.php` | Wrap `format_value()` with `wp_kses_post()` |
| `templates/dashboard/top-customers-total-spend.php` | Wrap `format_value()` with `wp_kses_post()` |
| `templates/dashboard/top-coupons-orders-count.php` | Wrap `format_value()` with `wp_kses_post()` |
| `templates/coupons/coupon-form.php` | Escape form `id` attribute |
| `templates/orders/orders.php` | Fix `$_REQUEST` usage |
| `templates/orders/order-filters-offcanvas.php` | Replace `htmlspecialchars()` with `esc_html()` |
| `templates/products/products.php` | Create missing delete product AJAX handler |

---

## Priority Order for Fixes

1. **Critical (Must fix):** Sections 1, 2, 3 - Input sanitization, output escaping, nonce/permission checks
2. **High (Must fix):** Section 5 - Inline styles should use `wp_add_inline_style()`
3. **Medium (Should fix):** Section 4 - Prefix naming, remove hardcoded nonce salt
4. **Low (Nice to have):** Section 6 - Code quality improvements, `extract()` removal
