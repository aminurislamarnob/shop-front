## Context

WooCommerce implements product quick edit in three coordinated pieces:

1. **Form HTML** — `woocommerce/includes/admin/views/html-quick-edit-product.php` (SKU, prices, tax, dimensions, shipping class, visibility, featured, manage stock, stock status with variable warning, stock qty, backorders, COGS block when enabled, hidden `woocommerce_quick_edit` + `woocommerce_quick_edit_nonce`).
2. **List inline data** — On the admin products list, WordPress `get_inline_data( $post )` emits `#inline_{id}` (core post + taxonomy quick-edit payload), then `class-wc-admin-list-table-products.php` emits `<div class="hidden" id="woocommerce_inline_{id}">` with child `<div class="sku">`, `regular_price`, `product_type`, etc., consumed by WooCommerce’s `quick-edit.js`.
3. **Save** — `WC_Admin_Post_Types::bulk_and_quick_edit_save_post()` → private `quick_edit_save()` in `woocommerce/includes/admin/class-wc-admin-post-types.php` (uses `$_REQUEST`, `current_user_can( 'edit_post' )`, nonce `woocommerce_quick_edit_nonce`, `wc_format_decimal`, SKU uniqueness, `maybe_update_stock_status`, `$product->save()`, `do_action( 'woocommerce_product_quick_edit_save', $product )`).

Core **JS** — `woocommerce/assets/js/admin/quick-edit.js` — binds `.editinline`, reads `#woocommerce_inline_{post_id}`, fills `.inline-edit-row`, and toggles `.price_fields`, stock blocks, dimensions for virtual, variable stock warning, etc.

StoreSuite already has modal infrastructure (`StoreSuite.storeSuiteModal`) and a products table; this change **grafts WC quick edit behavior** onto that shell.

### Related: Dokan Pro vendor quick edit (contrast)

[Dokan Pro](https://dokan.co/) solves the same *“edit from product list on the frontend”* problem for **multi-vendor** dashboards with a **different architecture**:

| Area | Dokan Pro (vendor list) | StoreSuite (this change) |
|------|-------------------------|----------------------------|
| Presentation | Extra `<tr>` inline under the list (hide product row, show edit row) | Single shared **modal** (`StoreSuite.storeSuiteModal`) |
| Field contract | `data-field-name` + vendor-specific keys (`stock_quantity`, `reviews_allowed`, …) | WooCommerce admin **POST keys** (`_sku`, `_stock`, …) + `woocommerce_quick_edit_nonce` |
| Inline data | Pre-filled in the edit-row template (no `#woocommerce_inline_{id}`) | Hidden **`#inline_{id}`** via `get_inline_data( $post )` + **`#woocommerce_inline_{id}`** to mirror WC admin DOM |
| Save path | `dokan_product_inline_edit` → `dokan()->product->update()` | StoreSuite AJAX → ported `quick_edit_save()` semantics |
| Extensibility | `dokan_quick_edit_before_column_1_ends`, `dokan_quick_edit_before_column_2_ends`, `dokan_after_quick_edit_form_fields`, `dokan_product_quick_edit_updated` | Woo hooks on copied template + `woocommerce_product_quick_edit_save` |
| List refresh | Replace `<tr>` from AJAX HTML fragment | Full **page reload** (same as StoreSuite bulk edit) unless changed later |

**Takeaway:** Dokan optimizes for **vendor workflows and Dokan APIs**; StoreSuite optimizes for **shop-manager parity with WooCommerce admin**. Neither is wrong; they are different products of record.

## Goals / Non-Goals

**Goals:**

- **Behavioral parity** with WooCommerce admin product quick edit for fields, visibility rules, and saved product state (as defined in the capability spec).
- Modal shell with focus trap / Escape / backdrop consistent with other StoreSuite modals.
- Per-row hidden inline data structurally compatible with what `quick-edit.js` expects, so porting that logic is mostly **selector and container** adaptation (modal instead of `.inline-edit-row`).

**Non-Goals:**

- Matching **Dokan Pro** vendor quick edit (inline row, `data-field-name`, Dokan AJAX/update pipeline) — out of scope unless a future change explicitly targets Dokan interoperability.
- Reimplementing WordPress core **post** quick edit fields (title, slug, status, etc.) unless WooCommerce’s quick edit form already includes them via core hooks (StoreSuite does not need to exceed WC’s product quick edit scope).
- Replacing WooCommerce’s `save_post` admin flow with a different persistence model (e.g. REST-only) unless it still produces **identical** product property updates as `quick_edit_save`.
- Parity with **bulk** edit (`html-bulk-edit-product.php`) — out of scope.

## Decisions

| Decision | Choice | Rationale | Alternatives considered |
|----------|--------|-----------|-------------------------|
| Source of truth | WooCommerce core files listed above | User requirement: do not invent new quick edit rules. | Custom field set (rejected). |
| Markup | Start from a **copy** of `html-quick-edit-product.php` inside StoreSuite templates (adjust outer wrapper for modal), or `include` core file from plugin (fragile if paths change). **Prefer copy** with file header comment citing WC version. | Full control of wrapper; avoids loading admin-only assumptions. | Include core file directly (works but couples to WC internal path). |
| Inline data | Emit **`#inline_{id}`** via `get_inline_data( $post )` then **`#woocommerce_inline_{id}`** (same inner structure as WC list table) inside each product row | Matches WC admin list DOM (core hidden block + WC block); supports `add_inline_data` and extensions expecting `#inline_*`. | JSON endpoint only — would require rewriting all population logic (more divergence from WC). |
| JS | Port **`quick-edit.js`** behaviors into `product.js`, targeting the modal form container instead of `.inline-edit-row` | Same branching as WC; easier to diff when WC updates. | Rewrite from scratch (rejected — drifts from WC). |
| Save | Implement a server path that **executes the same steps** as `quick_edit_save()` (copy-paste with attribution + periodic sync, or private refactor if WC ever exposes a callable API). | `quick_edit_save` is **private**; `new WC_Admin_Post_Types()` is not a service locator. | Reflection / fake `save_post` (rejected — brittle). |
| Nonce | **Payload:** include `woocommerce_quick_edit_nonce` verified with `wp_verify_nonce( ..., 'woocommerce_quick_edit_nonce' )` to match WC’s expectation, **plus** optional StoreSuite AJAX nonce if needed for `admin-ajax.php` action. | Aligns verification with WC’s field name. | StoreSuite-only nonce without WC payload fields (rejected — would diverge from save code). |
| After save | Reload list page (same as current StoreSuite bulk edit success pattern) unless tasks specify row patch. | Low risk; parity with list data refresh. | DOM row only update. |

## Risks / Trade-offs

| Risk | Mitigation |
|------|------------|
| WooCommerce updates `quick_edit_save` | Document “sync point” in code; periodic diff against WC when bumping WC requirement. |
| Text domains / translated strings | If copying WC template, use `storesuite` domain only where strings are re-output per WPCS; or keep `woocommerce` domain in verbatim core snippets (confirm PHPCS policy in project). |
| `quick-edit.js` depends on `inlineEditPost` / `#the-list` | StoreSuite modal path must **not** call `inlineEditPost.revert()` unless `inline-edit-post` script is present; reimplement only the WC-specific portion (the handler body) bound to StoreSuite list + modal. |
| Admin-only WC classes | `WC_Admin_Post_Types` loads in admin; quick edit save uses standard `WC_Product` APIs — porting `quick_edit_save` into StoreSuite uses the same product APIs and should run on frontend AJAX if `wc_get_product` and tax helpers are available (they are on storefront with WooCommerce loaded). |

## Migration Plan

- No DB migration.
- Roll back by removing template/JS/PHP additions.

## Open Questions

- Whether to **enqueue** WooCommerce’s `woocommerce_quick-edit` script on the dashboard (likely **no** — depends on admin bundle and `inlineEditPost`); **default:** port logic only.
- Exact WooCommerce **version** pinned in code comments (set during implementation to the version verified in `composer.json` or `readme`).
