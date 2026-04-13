## 1. Inline data and list UI

- [x] 1.1 For each product row in `templates/products/products.php`, output hidden `#woocommerce_inline_{id}` markup mirroring `class-wc-admin-list-table-products.php` (same child class names and data sources from `WC_Product`).
- [x] 1.2 Add a **Quick edit** control on each row (capability-gated with `current_user_can( 'edit_post', $product_id )` or equivalent).
- [x] 1.3 Add modal shell partial (overlay/dialog) and mount region for the quick edit form; initialize with `StoreSuite.storeSuiteModal`.

## 2. Form markup (WooCommerce parity)

- [x] 2.1 Add a StoreSuite template for quick edit fields based on `woocommerce/includes/admin/views/html-quick-edit-product.php` (copy with attribution comment + WC version); preserve field `name` attributes and WooCommerce hooks `woocommerce_product_quick_edit_start` / `woocommerce_product_quick_edit_end` if included.
- [x] 2.2 Resolve shipping class term list and optional sections (tax, dimensions, COGS) using the same `wc_*` conditionals as the core template.

## 3. Client behavior (port of quick-edit.js)

- [x] 3.1 Port population and toggle logic from `woocommerce/assets/js/admin/quick-edit.js` into `assets/frontend/product.js`, binding to StoreSuite list + modal selectors (no dependency on `inlineEditPost` or `#the-list` unless those are explicitly enqueued).
- [x] 3.2 Port `_manage_stock` change handler to swap stock qty/backorders vs stock status visibility like core.
- [x] 3.3 Localize any strings/decimal separator the script needs (align with `woocommerce_admin.mon_decimal_point` / `woocommerce_quick_edit.strings` equivalents from `class-wc-admin-assets.php` where applicable).

## 4. Server save (quick_edit_save parity)

- [x] 4.1 Implement an AJAX handler that accepts the same POST keys as WooCommerce quick edit (including `woocommerce_quick_edit`, `woocommerce_quick_edit_nonce`) and verifies nonce with `wp_verify_nonce( ..., 'woocommerce_quick_edit_nonce' )`.
- [x] 4.2 Implement save by porting `WC_Admin_Post_Types::quick_edit_save()` into StoreSuite (same steps, with code comment referencing WC file and version) or by an equivalent approach that produces identical `WC_Product` state; call `do_action( 'woocommerce_product_quick_edit_save', $product )` after save.
- [x] 4.3 Include any private helper behavior needed from the same WC class (e.g. stock status resolution) if referenced by `quick_edit_save`, copied with attribution.

## 5. Assets

- [x] 5.1 Register/enqueue or extend existing dashboard scripts so quick edit JS runs only on the products list; ensure `storeSuiteModal` dependency order.

## 6. Verification

- [x] 6.1 Compare behavior side-by-side with **WooCommerce → Products → Quick Edit** for: simple, variable, grouped, external, virtual, manage stock on/off, tax on/off, COGS feature on/off (if available).
- [x] 6.2 Run `composer phpcs` and `npm run lint:js` on touched files.
