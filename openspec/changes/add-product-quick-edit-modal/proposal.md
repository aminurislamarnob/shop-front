## Why

Shop managers already learn product quick editing from **WooCommerce admin** (`Products` list → Quick Edit). StoreSuite should offer the same capability on the frontend dashboard **without inventing a parallel field set or different save rules**, so behavior stays predictable and documentation stays aligned with WooCommerce. The only intentional UX difference is **presentation**: quick edit opens in a **StoreSuite modal** (accessible overlay) instead of WordPress core’s inline row editor.

## What Changes

- Add **Quick edit** on each product row on the StoreSuite products list, opening a **modal** (not core’s `inlineEditPost` row).
- **Field list, conditional visibility, and POST field names** SHALL follow WooCommerce’s product quick edit implementation:
  - Markup reference: `woocommerce/includes/admin/views/html-quick-edit-product.php` (including `woocommerce_product_quick_edit_start` / `woocommerce_product_quick_edit_end` hook points when rendering equivalent fields).
  - Client-side population / show-hide rules reference: `woocommerce/assets/js/admin/quick-edit.js` (same toggles for product type, virtual, manage stock, variable stock-status warning, etc.).
  - Per-row hidden “inline” data reference: `woocommerce/includes/admin/list-tables/class-wc-admin-list-table-products.php` (`#woocommerce_inline_{id}` structure and class names used by that script).
- **Persistence** SHALL match WooCommerce’s `WC_Admin_Post_Types::quick_edit_save()` behavior (same properties updated under the same conditions, including simple/external-only price writes, grouped manage-stock rule, stock status handling, sale date clearing on price change, COGS when the Cost of Goods feature is enabled, and `woocommerce_product_quick_edit_save` action). Implementation may port or delegate to that logic, but MUST NOT introduce alternate business rules for the same inputs.
- Transport layer (AJAX action name, StoreSuite nonce) MAY remain StoreSuite-prefixed for consistency with the rest of the plugin, as long as the **submitted field keys and values** remain WooCommerce-compatible for the save routine.

## Capabilities

### New Capabilities

- `dashboard-product-quick-edit`: StoreSuite dashboard behavior for WC-parity quick edit from the product list in a modal, including markup parity, JS parity for field visibility, inline data parity, and save parity with WooCommerce admin quick edit.

### Modified Capabilities

- _(none — no existing `openspec/specs/` baselines in this repository.)_

## Marketplace reference (Dokan Pro)

**Dokan Pro** (vendor dashboard → products) implements quick edit as a **vendor-native inline row**, not WooCommerce admin parity:

- **UI:** A second table row (`<tr class="dokan-product-list-inline-edit-form dokan-hide">`) immediately **after** each product row (`dokan_product_list_table_after_row`), toggled by clicking **Quick Edit** (`item-inline-edit`); the normal row is hidden while the edit row is shown (see `dokan-pro/assets/src/js/inline-editable-table.js`).
- **Fields:** Title, categories (custom UI), tags (Select2), reviews allowed, post status, then “product data” (SKU, prices for simple/external/subscription, weight/LWH, shipping class, visibility, stock) — template `dokan-pro/templates/products/edit/product-list-table-inline-edit-form.php`.
- **Binding:** Inputs use **`data-field-name`** (not WooCommerce admin POST keys); JS collects them into a `data` object.
- **Save:** `wp_ajax_dokan_product_inline_edit` with nonce `product-inline-edit`; server maps to `dokan()->product->update()` and fires **`dokan_product_quick_edit_updated`** (`dokan-pro/includes/Products.php::product_inline_edit`).
- **After save:** AJAX returns **fresh row HTML** and replaces the list row in-place (no full reload by default).

StoreSuite **intentionally does not** follow Dokan’s schema or transport; Dokan is documented here only for **comparison** and future integration discussions (e.g. Dokan + StoreSuite on the same site).

## Impact

- **WooCommerce coupling:** Explicit dependency on WooCommerce files and behavior as the **source of truth**; upgrades to WooCommerce may require diffing `quick_edit_save` / `html-quick-edit-product.php` / `quick-edit.js` when behavior changes.
- **Templates:** `templates/products/products.php` (row action + per-row inline data block mirroring WC list table output); modal wrapper partial; option to **include or adapt** WC’s quick-edit field markup (text domain may remain `woocommerce` where strings are reused from core templates, or be re-wrapped with `storesuite` per project i18n policy during implementation).
- **Frontend:** `assets/frontend/product.js` — logic ported from `quick-edit.js` into modal context; reuse `StoreSuite.storeSuiteModal` for shell a11y.
- **PHP:** `ProductController` / `ProductManager` (or dedicated class) for AJAX save path that executes WC-equivalent `quick_edit_save` semantics; `Assets.php` localization (decimal point / strings as needed, aligned with `woocommerce_quick_edit` / `woocommerce_admin` params where applicable).
