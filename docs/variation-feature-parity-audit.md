# Variation Feature Parity Audit: StoreSuite vs WooCommerce Admin

Compares the variable-product management UI in StoreSuite (`feat/product-variation-management` branch) against the native WooCommerce admin Product → Attributes / Variations screens.

Sources reviewed:
- `includes/Product/VariationAjax.php`
- `templates/products/product-attributes.php`
- `templates/products/product-attribute-row.php`
- `templates/products/product-variations.php`
- `templates/products/product-variation-row.php`
- `templates/products/html-variation-download.php`
- `assets/frontend/product-variation.js`

---

## Attributes section

| Feature | WC Admin | StoreSuite | Notes |
|---|---|---|---|
| Add global (taxonomy) attribute | ✅ | ✅ | Dropdown of registered `pa_*` taxonomies |
| Add custom (non-taxonomy) attribute | ✅ | ✅ | "Custom attribute" option |
| Multi-select term values | ✅ | ✅ | Select2 multiselect |
| Type & tag custom values | ✅ | ✅ | `data-tags="true"` with `\|` separator |
| Visible on product page toggle | ✅ | ✅ | |
| Used for variations toggle | ✅ | ✅ | |
| Select all / Select none terms | ✅ | ✅ | |
| Drag-sort attribute order | ✅ | ❌ | `attribute_position` hidden field exists but no sortable JS wired for attribute rows |
| Expand/collapse rows | ✅ | ✅ | |
| Remove attribute | ✅ | ✅ | |
| Save attributes (AJAX) | ✅ | ✅ | Uses `WC_Meta_Box_Product_Data::prepare_attributes` |
| Add new attribute term inline | ✅ ("Add new") | ❌ | Must pre-create terms in WP admin |
| Create new global attribute on-the-fly | ✅ | ❌ | |

## Variations section

| Feature | WC Admin | StoreSuite | Notes |
|---|---|---|---|
| Add single variation | ✅ | ✅ | `add_variation` AJAX |
| Generate from all attributes | ✅ | ✅ | Cartesian product, caps at 50/batch (`storesuite_max_variations_per_generate` filter) |
| Pagination | ✅ (per_page=10) | ✅ (default 15, `storesuite_variations_per_page` filter) | |
| Default form values | ✅ | ✅ | Saved via separate `save_default_attributes` AJAX |
| Per-variation: Enabled toggle | ✅ | ✅ | |
| Per-variation: Downloadable | ✅ | ✅ | Full files table + limit + expiry |
| Per-variation: Virtual | ✅ | ✅ | |
| Per-variation: Manage stock + qty | ✅ | ✅ | |
| Per-variation: Stock status | ✅ | ✅ | |
| Per-variation: Regular & Sale price | ✅ | ✅ | |
| Sale price schedule (from/to dates) | ✅ | ✅ | `date_on_sale_from` / `date_on_sale_to` with Schedule/Cancel toggle |
| Per-variation: SKU | ✅ | ✅ | |
| Per-variation: GTIN/UPC/EAN/ISBN | ✅ (WC 9.1+) | ✅ | `global_unique_id` |
| Per-variation: Weight & dimensions | ✅ | ✅ | |
| Per-variation: Shipping class | ✅ | ✅ | `variable_shipping_class`; hidden when virtual (`hide_if_variation_virtual`), `0` = Same as parent |
| Per-variation: Tax class | ✅ | ✅ | `variable_tax_class`; only shown when `wc_tax_enabled()`, `parent` = Same as parent |
| Per-variation: Backorders setting | ✅ (allow/notify/no) | ✅ | `no` / `notify` / `yes` |
| Per-variation: Low stock threshold | ✅ | ✅ | `low_stock_amount` |
| Per-variation: Cost of Goods Sold | ✅ (when feature enabled) | ✅ | `cogs_value` (gated on `CostOfGoodsSoldController`) |
| Per-variation: Image | ✅ | ✅ | `variable_image_id`; media uploader handling on frontend |
| Per-variation: Description | ✅ | ✅ | |
| Downloadable files table | ✅ | ✅ | `html-variation-download` row template, add/remove files |
| Download limit / expiry | ✅ | ✅ | |
| Bulk: set regular prices | ✅ | ✅ | |
| Bulk: set sale prices | ✅ | ✅ | |
| Bulk: set stock status | ✅ | ✅ | |
| Bulk: toggle enabled | ✅ | ✅ | |
| Bulk: delete all | ✅ | ✅ | |
| Bulk: increase/decrease price by amount or % | ✅ | ❌ | |
| Bulk: set stock qty / weight / dimensions / length | ✅ | ❌ | |
| Bulk: set shipping class / tax class | ✅ | ❌ | |
| Bulk: set download files / limit / expiry | ✅ | ❌ | |
| Bulk: schedule sale | ✅ | ❌ | |
| (Total bulk actions) | ~25 | 5 | |
| Drag-sort variation order | ✅ | ✅ | `initSortable()` updates `variable_menu_order` inputs on drop |
| Expand/collapse rows | ✅ | ✅ | |

## Save flow

- StoreSuite saves attributes and variations through **separate AJAX endpoints** (each with its own nonce), not as part of the main product save. WC admin posts everything together. Functionally equivalent.
- Parent product synced via `WC_Product_Variable::sync()` after each mutation. ✅

---

## Summary

**Core CRUD and almost all per-variation fields are in place.** Shop managers can add/generate/edit/delete variations, manage attributes (global + custom), set defaults, paginate, drag-reorder variations, and edit price, sale schedule, SKU, GTIN, stock (with backorders + low-stock threshold), downloadable files (limit/expiry), dimensions, COGS, image, and description.

### Remaining gaps vs WC admin (prioritized)

**Medium effort:**
1. **Expanded bulk actions** — WC has ~25, StoreSuite has 5. Missing: price ±%/amount (regular & sale), bulk stock qty, bulk weight/dimensions, bulk shipping/tax class, bulk download settings, schedule sale.

**UX polish:**
2. **Drag-sort for attribute rows** — `attribute_position` hidden field exists; needs sortable JS wired up (the variations sortable in `initSortable()` is a working pattern to mirror).
3. **Inline create** for new global attribute taxonomies or new terms (currently must pre-create them in WP admin).

### Closed since the previous audit
Sale price schedule, backorders, low stock threshold, GTIN/UPC/EAN/ISBN, downloadable files table + limit/expiry, per-variation Cost of Goods Sold, drag-sort variation order, and per-variation shipping class + tax class are now implemented.
