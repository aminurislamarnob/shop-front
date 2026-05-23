# Variation Feature Parity Audit: StoreSuite vs WooCommerce Admin

Compares the variable-product management UI in StoreSuite (`feat/product-variation-management` branch) against the native WooCommerce admin Product → Attributes / Variations screens.

Sources reviewed:
- `includes/Product/VariationAjax.php`
- `templates/products/product-attributes.php`
- `templates/products/product-attribute-row.php`
- `templates/products/product-variations.php`
- `templates/products/product-variation-row.php`
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
| Drag-sort attribute order | ✅ | ❌ | `attribute_position` hidden field exists but no sortable JS wired |
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
| Per-variation: Downloadable | ✅ | ✅ (checkbox only) | ⚠️ No files UI / no download limit / no expiry |
| Per-variation: Virtual | ✅ | ✅ | |
| Per-variation: Manage stock + qty | ✅ | ✅ | |
| Per-variation: Stock status | ✅ | ✅ | |
| Per-variation: Regular & Sale price | ✅ | ✅ | |
| Sale price schedule (from/to dates) | ✅ | ❌ | Missing |
| Per-variation: SKU | ✅ | ✅ | |
| Per-variation: GTIN/UPC/EAN/ISBN | ✅ (WC 9.1+) | ❌ | Missing |
| Per-variation: Weight & dimensions | ✅ | ✅ | |
| Per-variation: Shipping class | ✅ | ❌ | Missing |
| Per-variation: Tax class | ✅ | ❌ | Missing |
| Per-variation: Backorders setting | ✅ (allow/notify/no) | ❌ | Only `stock_status` is set |
| Per-variation: Low stock threshold | ✅ | ❌ | Missing |
| Per-variation: Image | ✅ | ✅ | `variable_image_id`; media uploader handling on frontend |
| Per-variation: Description | ✅ | ✅ | |
| Downloadable files table | ✅ | ❌ | Missing entirely |
| Download limit / expiry | ✅ | ❌ | Missing |
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
| Drag-sort variation order | ✅ | ❌ | `variable_menu_order` stored as hidden, no sortable handle wired |
| Expand/collapse rows | ✅ | ✅ | |

## Save flow

- StoreSuite saves attributes and variations through **separate AJAX endpoints** (each with its own nonce), not as part of the main product save. WC admin posts everything together. Functionally equivalent.
- Parent product synced via `WC_Product_Variable::sync()` after each mutation. ✅

---

## Summary

**Core CRUD is in place.** Shop managers can add/generate/edit/delete variations, manage attributes (global + custom), set defaults, paginate, edit price/SKU/stock/dimensions/description, set per-variation image, and run the most common bulk actions.

### Gaps vs WC admin (prioritized)

**High-impact, low-effort** — add a few `set_*` calls in `VariationAjax::save_variations()` plus matching template fields:
1. **Sale price schedule** (`date_on_sale_from`, `date_on_sale_to`)
2. **Backorders** setting (`no` / `notify` / `yes`) — currently only `stock_status` is saved
3. **Shipping class** (`shipping_class_id`)
4. **Tax class** (`tax_class`)
5. **Low stock threshold** (`low_stock_amount`)
6. **GTIN/UPC/EAN/ISBN** (WC 9.1+ global identifiers)

**Medium effort:**
7. **Downloadable files table** — files, download limit, expiry (currently only the checkbox toggle)
8. **Expanded bulk actions** — WC has ~25, StoreSuite has 5. Missing: price ±%/amount, bulk stock qty, bulk weight/dimensions, bulk shipping/tax class, bulk download settings, schedule sale.

**UX polish:**
9. **Drag-sort** for both attributes and variations — hidden position/menu_order fields exist; just needs sortable JS wired up.
10. **Inline create** for new global attribute taxonomies or new terms (currently must pre-create them in WP admin).
