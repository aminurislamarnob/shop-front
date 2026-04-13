## ADDED Requirements

### Requirement: WooCommerce admin quick edit as the behavioral reference

The system SHALL implement product quick edit on the StoreSuite products list such that **fields, default values, conditional visibility, POST parameter names, nonce name, and resulting product data** match WooCommerce admin’s product quick edit, as implemented in:

- `woocommerce/includes/admin/views/html-quick-edit-product.php`
- `woocommerce/assets/js/admin/quick-edit.js` (show/hide and population rules)
- `woocommerce/includes/admin/list-tables/class-wc-admin-list-table-products.php` (hidden `#woocommerce_inline_{id}` data; preceded on the admin list by WordPress `get_inline_data()` → `#inline_{id}` in `wp-admin/includes/template.php`)
- `woocommerce/includes/admin/class-wc-admin-post-types.php` (`quick_edit_save` logic reached when `woocommerce_quick_edit` is present in the request)

The system SHALL NOT define a separate or smaller “invented” field set for the same product types unless WooCommerce itself omits or hides those fields for that context.

The system SHALL **not** be required to match **Dokan Pro** vendor-dashboard quick edit (inline sibling `<tr>`, `data-field-name` contract, `dokan_product_inline_edit`, or Dokan-specific hooks). Dokan is a documented **contrast** only (see change `design.md`).

#### Scenario: Simple product shows price fields

- **WHEN** quick edit is opened for a **simple** product
- **THEN** price fields SHALL be shown and SHALL follow the same rules as WooCommerce’s `quick-edit.js` for simple products

#### Scenario: Non-simple product hides price fields

- **WHEN** quick edit is opened for a product type that is not simple or external
- **THEN** the price group SHALL be hidden the same way as in WooCommerce’s `quick-edit.js`

#### Scenario: Variable product stock status warning

- **WHEN** quick edit is opened for a **variable** product
- **THEN** the stock status “changes all variations” warning SHALL be shown or toggled the same way as in WooCommerce’s `quick-edit.js`

### Requirement: Per-row inline data compatible with WooCommerce admin list

For each product row on the StoreSuite list, the system SHALL reproduce the **same pair of hidden blocks** WooCommerce outputs on the admin product list for that post (when the viewer may `edit_post` for core `get_inline_data` output):

1. **`#inline_{product_id}`** — output via WordPress `get_inline_data( $post )` (post title, slug, status, taxonomies in quick-edit form, `add_inline_data`, etc.).
2. **`#woocommerce_inline_{product_id}`** — WooCommerce product inline data whose inner structure (class names and meaning of each value) matches `class-wc-admin-list-table-products.php` so the same population logic as `quick-edit.js` can be applied.

#### Scenario: Inline data present for each listed product

- **WHEN** the products table renders a product with ID `N` for a user who may edit that post
- **THEN** a hidden element with id `inline_N` SHOULD be emitted when `get_inline_data()` runs (WordPress core behavior), and a hidden element with id `woocommerce_inline_N` SHALL exist and SHALL contain the same categories of values as WooCommerce’s list table (e.g. sku, regular_price, sale_price, product_type, manage_stock, stock_status, …) for that product

#### Scenario: WooCommerce-only inline block always present for list rows

- **WHEN** the product row is rendered for the StoreSuite list
- **THEN** `woocommerce_inline_N` SHALL exist for that product ID `N` so WC-parity quick edit population continues to work regardless of `get_inline_data` capability gating

### Requirement: Modal presentation with accessible overlay

Quick edit SHALL open inside a StoreSuite modal overlay that provides focus management, Escape to close, and close controls consistent with `StoreSuite.storeSuiteModal`.

#### Scenario: Modal opens from list action

- **WHEN** the user invokes quick edit from the product list
- **THEN** the quick edit form SHALL appear inside a modal dialog and MUST remain usable with keyboard navigation

### Requirement: Save semantics equivalent to WooCommerce quick_edit_save

On save, the system SHALL persist changes using logic equivalent to WooCommerce’s `WC_Admin_Post_Types::quick_edit_save()` for the submitted quick edit fields, including but not limited to:

- Mapping of request keys (`_weight`, `_visibility`, `_tax_status`, `_sku`, `_shipping_class`, `_featured`, `_regular_price`, `_sale_price`, stock fields, COGS when enabled) to `WC_Product` setters in the same conditions as core
- Simple/external-only regular and sale price updates; clearing scheduled sale dates when prices change, as in core
- SKU uniqueness handling consistent with `wc_product_has_unique_sku`
- Stock / backorders rules respecting product type (e.g. external and grouped behaviors as in core)
- Firing `do_action( 'woocommerce_product_quick_edit_save', $product )` after a successful save when equivalent to core

#### Scenario: Unauthorized user cannot save

- **WHEN** a user who cannot `edit_post` for the product submits a quick edit save
- **THEN** the server SHALL reject the operation and MUST NOT modify the product

#### Scenario: Invalid quick edit nonce rejected

- **WHEN** the request is missing `woocommerce_quick_edit_nonce` or verification fails for action `woocommerce_quick_edit_nonce`
- **THEN** the server SHALL reject the save and MUST NOT modify the product

### Requirement: Successful save feedback

After a successful save, the system SHALL confirm success using the same user-feedback patterns as other StoreSuite product AJAX operations and SHALL refresh the product list so displayed data matches the database.

#### Scenario: Success path refreshes list

- **WHEN** a quick edit save completes successfully
- **THEN** the user SHALL see success feedback and the product list view SHALL be updated to reflect new values (full page reload is acceptable)
