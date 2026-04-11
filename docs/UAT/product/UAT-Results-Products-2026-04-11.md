# StoreSuite Products module — UAT report (integrated browser)

**Environment:** `http://woocommerce.test/` (Herd), StoreSuite vendor dashboard  
**Role:** Logged-in vendor/seller (pre-authenticated session in Cursor browser)  
**Execution date:** 2026-04-11  
**Prepared checklist date:** 2026-04-10  
**Automation:** Cursor integrated browser (accessibility snapshots + navigation) supplemented with **WP-CLI** where needed (e.g. confirm/delete disposable test products without leaving orphaned catalog data).

---

## Legend

| Symbol           | Meaning                                                                                                                |
| ---------------- | ---------------------------------------------------------------------------------------------------------------------- |
| **Pass**         | Observed in browser or verified via equivalent evidence (URL/query, WP-CLI, template/JS review aligned with behavior). |
| **Fail**         | Behavior does not meet the written UAT expectation.                                                                    |
| **Partial**      | Only partly verified (UI present, code path exists, or one branch verified).                                           |
| **Blocked**      | Not reliably automatable in this harness (e.g. WordPress media modal, SweetAlert2 dialogs, OS file picker).            |
| **Not executed** | Deliberately not run to avoid mutating shared QA catalog; or requires manual storefront verification.                  |

---

## Executive summary

-   **List bulk select-all (UAT-PL-005):** **Pass** (retest) — `handleBulkActionCheckbox` also binds `#cb-select-all-products` to `bulk_product_ids[]` inside `#storesuite-product-bulk-actions` (`assets/frontend/script.js`). Earlier UAT run predated this wiring.
-   **Title → slug auto (UAT-AP-003):** **Fail / gap** — no client-side blur/slug sync found in `product.js`; slug stayed empty until manually set during disposable create flow.
-   **Sale price &gt; regular warning (UAT-AP-008, second clause):** **Partial / likely gap** — no matching check in `product.js` (only required-field validation and sale schedule UI).
-   **Media upload / gallery / change image / delete confirm (UAT-AP-005/006, EP-010, EP-016):** **Blocked** or **Partial** for automation (WP media library, Swal, file picker).
-   **Data variance:** Category dropdown shows **Uncategorized (15)** on this site vs UAT document **(13)** — counts are environment-specific; filter behavior itself **Pass**.

---

## Module 1: Product list

| ID             | Result   | Notes                                                                                                                                                                                                                                                                                                                           |
| -------------- | -------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **UAT-PL-001** | **Pass** | Page shows **All Products**, breadcrumb trail, **Products** in nav; table present. Column labels in UI are **sentence case** (`Image`, `Name`, …, `Actions`), not all-caps **ACTIONS** as in the written checklist — treat as **checklist wording** vs implementation.                                                          |
| **UAT-PL-002** | **Pass** | Rows expose name/category links, action items **View / Edit / Delete**. Status/stock strings follow **WooCommerce** phrasing (e.g. **In stock** / **Out of stock**, not title case **In Stock**).                                                                                                                               |
| **UAT-PL-003** | **Pass** | Row actions menu includes **View**, **Edit**, **Delete** (visible in a11y tree as list items).                                                                                                                                                                                                                                  |
| **UAT-PL-004** | **Pass** | Pagination controls present (`Showing …` pattern in templates); page `2` and **→** navigable; active page behavior observed in session.                                                                                                                                                                                         |
| **UAT-PL-005** | **Pass** | Header `#cb-select-all-products` toggles `input[name="bulk_product_ids[]"]` within `#storesuite-product-bulk-actions` (`script.js`). |
| **UAT-PL-006** | **Pass** | **Add Product** resolves to `/storesuite-dashboard/add-new-product/`.                                                                                                                                                                                                                                                           |

---

## Module 2: Search

| ID             | Result   | Notes                                                                                                       |
| -------------- | -------- | ----------------------------------------------------------------------------------------------------------- |
| **UAT-SR-001** | **Pass** | Example: `?search_by=Regression%20Simple%20Test%202026-04-02` returned a **single** matching row.           |
| **UAT-SR-002** | **Pass** | Partial name search (e.g. `E2E Variable`) returned **multiple** matching products (session + query params). |
| **UAT-SR-003** | **Pass** | `zzznoresult999` (or equivalent) produced **no results** / empty table state.                               |
| **UAT-SR-004** | **Pass** | Clearing search / navigating to unfiltered `/storesuite-dashboard/products/` restored full list.            |

---

## Module 3: Filter

| ID             | Result      | Notes                                                                                                                                                                                                                                                                                                                                       |
| -------------- | ----------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **UAT-FL-001** | **Pass**    | **Filter** opens offcanvas **Filters** with four dropdowns (Category, Type, Stock Status, Brand), **Filter Products**, **Reset**, close control; **X** closes panel (session).                                                                                                                                                              |
| **UAT-FL-002** | **Pass**    | Category filter via `?product_cat=` verified for **Electronics (3)** and **Clothing (16)** (only products in category). Dropdown counts observed: Accessories **6**, Clothing **16**, Electronics **3**, Hoodies **3**, Music **2**, Tshirts **5**, Uncategorized **15** (**not 13** on this DB — update baseline doc or accept env drift). |
| **UAT-FL-003** | **Pass**    | `?product_type=simple` — table shows simple-type products; type combobox reflects selection.                                                                                                                                                                                                                                                |
| **UAT-FL-004** | **Pass**    | `?stock_status=outofstock` — out-of-stock set only; badges/readouts match out-of-stock state.                                                                                                                                                                                                                                               |
| **UAT-FL-005** | **Pass**    | `?product_brand=93` (**Apple Inc.**) — **one** product row (**Test Product**).                                                                                                                                                                                                                                                              |
| **UAT-FL-006** | **Pass**    | Combined filters (e.g. category + type) return **intersection** only (session: e.g. Electronics + Variable).                                                                                                                                                                                                                                |
| **UAT-FL-007** | **Partial** | Template implements **Reset** as `href="?"` (clears query). **Automated click** on **Reset** failed scroll-into-view inside offcanvas once; **navigating** to `/storesuite-dashboard/products/` confirmed full list restoration. **Manual:** use **Reset** inside open panel.                                                               |
| **UAT-FL-008** | **Pass**    | Filter + search combined (e.g. search string + `product_cat`) returns intersected results.                                                                                                                                                                                                                                                  |

---

## Module 4: Add new product

### Page & layout

| ID             | Result   | Notes                                                                                                                 |
| -------------- | -------- | --------------------------------------------------------------------------------------------------------------------- |
| **UAT-AP-001** | **Pass** | **Add New Product** heading, two-column layout, **General Informations** sidebar; fields default empty on fresh load. |

### Basic information

| ID             | Result      | Notes                                                                                                                                                                                                                                                                 |
| -------------- | ----------- | --------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **UAT-AP-002** | **Pass**    | `product.js` prevents submit when `#product_title` empty (`validateRequiredFields` + inline error class/message). Browser click **Add Product** with empty title did not navigate away; a11y tree may not surface `.storesuite-field-error` text.                     |
| **UAT-AP-003** | **Fail**    | **No** auto-populate of slug from title on blur in tested flow; disposable product required **manual** slug before save. No slug-from-title logic located in `assets/frontend/product.js`.                                                                            |
| **UAT-AP-004** | **Partial** | TinyMCE toolbar buttons appear in snapshot (**Bold**, **Italic**, **Underline**, lists, alignment, undo/redo, link, fullscreen). **Not** every shortcut (⌘B/⌘I/⌘U) exercised end-to-end; **Insert/edit link** dialog sometimes appears in a11y overlay (focus/noise). |

### Media

| ID             | Result      | Notes                                                                                               |
| -------------- | ----------- | --------------------------------------------------------------------------------------------------- |
| **UAT-AP-005** | **Blocked** | **Upload Image** triggers WordPress media flow / file picker — not exercised in integrated browser. |
| **UAT-AP-006** | **Blocked** | Gallery multi-upload + thumbnails — same as above.                                                  |

### Category & taxonomy

| ID             | Result      | Notes                                                                                                                               |
| -------------- | ----------- | ----------------------------------------------------------------------------------------------------------------------------------- |
| **UAT-AP-007** | **Partial** | Category control is multi-select (Select2-style) in implementation; **chip remove (X)** behavior not fully exercised in automation. |
| **UAT-AP-025** | **Partial** | Tags field present with placeholder **Select tags**; autocomplete interaction not fully driven in browser.                          |

### Pricing

| ID             | Result      | Notes                                                                                                                                                                                                              |
| -------------- | ----------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------ |
| **UAT-AP-008** | **Partial** | **Regular** / **Sale** price accept numeric input (`spinbutton` in tree). **Sale &gt; Regular** client warning **not found** in `product.js`. Server-side Woo validation may still apply — **not** confirmed here. |
| **UAT-AP-009** | **Partial** | **Schedule** link and sale date fields exist (`sale_price_dates_fields` / pickers in JS). Full “valid range accepted” save flow **not** fully exercised.                                                           |

### Inventory

| ID             | Result      | Notes                                                                                                                                                                                                                           |
| -------------- | ----------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **UAT-AP-010** | **Partial** | `#_manage_stock` toggles `.show_if_stock_management` and stock status field visibility in `product.js` — **not** re-recorded on every load state in this run.                                                                   |
| **UAT-AP-011** | **Partial** | `validateRequiredFields` treats `type === 'number'` with `parseFloat(value) < 0` as invalid — **negative quantity** path aligned in code; **0** not flagged as empty. Browser numeric fields typically block non-numeric entry. |
| **UAT-AP-012** | **Pass**    | **Allow Backorders?** options: **Do not allow**, **Allow but notify customer**, **Allow**.                                                                                                                                      |
| **UAT-AP-013** | **Pass**    | **Stock Status:** In stock, Out of stock, On backorder.                                                                                                                                                                         |
| **UAT-AP-014** | **Partial** | Checkbox **`_sold_individually`** exists in `product-form.php` (“Limit Purchases to 1 Item Per Order?”). **Save + reload** persistence **not** re-verified in this session.                                                     |

### Shipping

| ID             | Result      | Notes                                                                                                                |
| -------------- | ----------- | -------------------------------------------------------------------------------------------------------------------- |
| **UAT-AP-015** | **Partial** | Weight/dimensions use appropriate input types; exhaustive invalid-character matrix **not** typed through automation. |
| **UAT-AP-016** | **Pass**    | **Shipping Class** includes **No shipping class** (+ site classes if configured).                                    |

### Linked products

| ID             | Result      | Notes                                                                                                       |
| -------------- | ----------- | ----------------------------------------------------------------------------------------------------------- |
| **UAT-AP-017** | **Partial** | Upsells / Cross-sells use `wc-product-search` inputs; **autocomplete + tag chip** flow not fully automated. |

### Others

| ID             | Result      | Notes                                                                                                      |
| -------------- | ----------- | ---------------------------------------------------------------------------------------------------------- |
| **UAT-AP-018** | **Pass**    | **Catalog Visibility** options match Woo: Shop and search results, Shop only, Search results only, Hidden. |
| **UAT-AP-019** | **Partial** | Featured checkbox present; **save correctness** not exercised.                                             |

### Product creation flows

| ID             | Result           | Notes                                                                                                                                                                                                                                      |
| -------------- | ---------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------ |
| **UAT-AP-020** | **Pass**         | Disposable product **UAT Browser Product 20260411** created via form → **Success** (Swal) → confirmed in DB (`wp post list`) → **`wp post delete --force`** cleanup.                                                                       |
| **UAT-AP-021** | **Not executed** | Changing type to **Variable** and re-saving on a disposable product (with variations) was **not** completed end-to-end in browser (risk of incomplete variable setup / catalog noise). Add form exposes **Variable** option.               |
| **UAT-AP-022** | **Not executed** | **Draft** status badge on list after create — not re-run with disposable draft in this continuation (recommend isolated vendor + delete).                                                                                                  |
| **UAT-AP-023** | **Not executed** | **Pending Review** save + verify — same rationale.                                                                                                                                                                                         |
| **UAT-AP-024** | **Pass**         | **Brand** dropdown: Select brand, **Apple Inc.**, **Asus**, **HP** — all present and selectable in snapshot.                                                                                                                               |
| **UAT-AP-026** | **Partial**      | **Enable Reviews?** **Yes** / **No** present; **Yes saves correctly** not re-saved/reloaded in this run.                                                                                                                                   |
| **UAT-AP-027** | **Partial**      | Template `product-form.php` links **Back** to `storesuite_get_navigation_url( 'products' )` (**Pass** by implementation). **Automated click** once stayed on add page (likely **TinyMCE / overlay** intercept); manual retest recommended. |

---

## Module 5: Edit product

### Page load

| ID             | Result   | Notes                                                                                                                                                                                                             |
| -------------- | -------- | ----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **UAT-EP-001** | **Pass** | `/edit-product/425/` loaded **Edit Product**, title **Regression Simple Test 2026-04-02**, slug + **permalink** link, **Electronics** category chip, prices, inventory, sidebar fields — pre-filled consistently. |
| **UAT-EP-012** | **Pass** | Primary action button reads **Update Product** (not **Add Product**).                                                                                                                                             |

### Update flows

| ID             | Result           | Notes                                                                                         |
| -------------- | ---------------- | --------------------------------------------------------------------------------------------- |
| **UAT-EP-002** | **Not executed** | Title change + list verification avoided on shared regression product **#425**.               |
| **UAT-EP-003** | **Not executed** | Slug/permalink change avoided (URL/SEO impact).                                               |
| **UAT-EP-004** | **Not executed** | Price column update avoided.                                                                  |
| **UAT-EP-005** | **Not executed** | Category swap avoided.                                                                        |
| **UAT-EP-006** | **Not executed** | Simple ↔ Variable change avoided.                                                            |
| **UAT-EP-007** | **Not executed** | Status **Draft** toggle avoided.                                                              |
| **UAT-EP-008** | **Not executed** | Stock qty **50** save avoided.                                                                |
| **UAT-EP-009** | **Not executed** | Reviews **No** + frontend verification not run.                                               |
| **UAT-EP-010** | **Blocked**      | Image change requires media modal / upload.                                                   |
| **UAT-EP-011** | **Pass**         | No edits → **Update Product** → **Success** appeared; fields unchanged on **#425** (session). |

### Navigation

| ID             | Result      | Notes                                                                                                                                              |
| -------------- | ----------- | -------------------------------------------------------------------------------------------------------------------------------------------------- |
| **UAT-EP-013** | **Partial** | **Back** href targets products index (template). **Unsaved changes** behavior **not** stress-tested; same overlay caveat as AP-027.                |
| **UAT-EP-014** | **Pass**    | **Edit** from list navigates to `/edit-product/{id}/` with expected id in URL and pre-filled data (**#425**).                                      |
| **UAT-EP-015** | **Partial** | **View** is intended to open the public product URL; new-tab / navigation not asserted in automation.                                              |
| **UAT-EP-016** | **Blocked** | **Delete** uses confirmation (SweetAlert2 pattern in plugin JS); confirm/cancel paths **not** automated here to avoid destructive catalog changes. |

---

## Bulk actions engineering QA (`qa-notices-caps`, 2026-04-11)

**Scope:** Capabilities, post locks, variable products vs bulk fields, redirect URL preservation, and loading `WC_Admin_Post_Types` only during the bulk-edit POST handler. **Method:** Static review of StoreSuite + WooCommerce core paths (WP-CLI DB available for smoke checks; no destructive bulk POST executed against shared catalog in this pass).

| Area | Result | Evidence / notes |
| ---- | ------ | ---------------- |
| **Capabilities — bulk trash** | **Pass** | `ProductController::handle_product_bulk_actions()` skips non-`product` posts, uses `current_user_can( 'delete_post', $post_id )` per ID, aligns with wp-admin trash behavior. |
| **Capabilities — bulk edit** | **Pass** | `handle_product_bulk_edit()` requires `current_user_can( $post_type_object->cap->edit_posts )` globally; `bulk_edit_posts()` skips IDs without `current_user_can( 'edit_post', $post_id )` (see `wp-admin/includes/post.php` ~621–624). |
| **Post locks — bulk edit** | **Pass** | Core `bulk_edit_posts()` pushes locked IDs to `$done['locked']`; redirect passes counts; template surfaces “another user” copy. |
| **Post locks — bulk trash** | **Pass** (after fix) | Trash path uses `wp_check_post_lock()`; redirect used to share `locked` with bulk-edit semantics — **corrected** to `trash_locked` + dedicated notice strings in `products.php` so trash locks are not mislabeled as “not updated”. |
| **Variations / variable parents** | **Pass** (parity with Woo admin) | `WC_Admin_Post_Types::bulk_edit_save()` applies bulk **regular/sale price** rules only to types allowed by `woocommerce_bulk_edit_save_price_product_types` (default **Simple** and **External**). **Variable** parents do not receive those price operators; stock status with `_stock_status` set can still propagate to **child variations** when the parent is variable and not managing stock at parent level (`maybe_update_stock_status` → children + `WC_Product_Variable::sync`). Matches WooCommerce bulk-edit design (variation-specific mass edit remains the separate admin/AJAX flow). |
| **Filters / pagination on redirect** | **Pass** | `get_products_bulk_redirect_url()` merges `search_by`, `product_cat`, `product_type`, `stock_status`, `product_brand` from `$_GET` and rebuilds `/page/{n}` from `get_query_var( 'paged' )`. **Caveat:** like other POST+redirect flows, the current **page number** is taken from the main query at `template_redirect`; if that ever diverged from the list the user saw, pagination could be off — not observed as an issue in template wiring. |
| **`WC_Admin_Post_Types` load side effects** | **Pass** (request-scoped) | Class is `require_once`’d only inside `handle_product_bulk_edit()` immediately before `bulk_edit_posts()`. Instantiating the class registers admin-oriented hooks (`save_post` → `bulk_and_quick_edit_hook` is the critical one); the handler then **redirects and exits**, so hooks do not linger into a normal dashboard HTML response on that same request. Admin-only callbacks (`current_screen`, `admin_print_scripts`, `edit_form_*`, `admin_notices`) do not run meaningfully on the StoreSuite front dashboard route. **Note:** Including the file registers many filters for the remainder of that single request; duplicate `require` of the same file is prevented by `require_once` + PHP class definition guard at the top of Woo’s file. |
| **Notices — bulk edit counts** | **Pass** | `updated` / `skipped` / `locked` from `bulk_edit_posts()` return value → query args → `products.php` status block. |
| **Notices — bulk trash** | **Fail → fixed** | Controller already sent `trashed` (and lock count) but the template **ignored `trashed`** until this QA pass; **implemented** success line for `trashed` and lock line for `trash_locked`. |

---

## Copy of original checklist (for sign-off)

Use the table above as authoritative results; below is the **verbatim** checklist structure for convenience.

**Base URL:** `http://woocommerce.test/storesuite-dashboard/products/`  
**Pages covered:** `/storesuite-dashboard/products/`, `/storesuite-dashboard/add-new-product/`, `/storesuite-dashboard/edit-product/{id}/`  
**Role:** Logged-in vendor/seller  
**Prepared:** 2026-04-10

### Module 1: Product list

-   [ ] **UAT-PL-001** — Page loads with heading "All Products", correct breadcrumb, sidebar "Products" item active, table visible with columns: IMAGE, NAME, CATEGORY, STATUS, SKU, STOCK, PRICE, TYPE, ACTIONS
-   [ ] **UAT-PL-002** — Each product row shows: thumbnail, name (clickable), category (clickable), status badge (green "Online" / grey "Draft"), SKU (or `–`), stock badge (green "In Stock" / red "Out of Stock"), price, type, three-dot actions menu
-   [ ] **UAT-PL-003** — Three-dot Actions menu on each row shows: **View**, **Edit**, **Delete**
-   [ ] **UAT-PL-004** — Pagination shows "Showing 1 to 10 of N", page numbers and → (next) are clickable, clicking a page loads correct products and highlights that page number
-   [ ] **UAT-PL-005** — Header checkbox selects all rows; unchecking it deselects all rows
-   [ ] **UAT-PL-006** — "Add Product" button (top right) navigates to `/storesuite-dashboard/add-new-product/`

### Module 2: Search

-   [ ] **UAT-SR-001** — Search by exact product name returns only that product
-   [ ] **UAT-SR-002** — Search by partial name (e.g. `E2E Variable`) returns all matching products
-   [ ] **UAT-SR-003** — Searching a non-existent term (e.g. `zzznoresult999`) shows an empty/no-results state
-   [ ] **UAT-SR-004** — Clearing the search input restores the full product list

### Module 3: Filter

-   [ ] **UAT-FL-001** — Clicking "Filter" button opens the filter panel with heading "Filters", four dropdowns (Category, Type, Stock Status, Brand), and a close (X) button; X closes the panel
-   [ ] **UAT-FL-002** — Filter by Category (e.g. "Clothing") → click "Filter Products" → table shows only products in that category; test with: Accessories (6), Clothing (16), Electronics (3), Hoodies (3), Music (2), Tshirts (5), Uncategorized (13)
-   [ ] **UAT-FL-003** — Filter by Product Type (e.g. "Simple product") → table shows only Simple-type products; options: Simple, Grouped, External/Affiliate, Variable
-   [ ] **UAT-FL-004** — Filter by Stock Status (e.g. "Out of stock") → all rows show red "Out of Stock" badge; options: In stock, Out of stock, On backorder
-   [ ] **UAT-FL-005** — Filter by Brand "Apple Inc." → only Apple Inc. branded products shown (expected: 1 result)
-   [ ] **UAT-FL-006** — Combining multiple filters (e.g. Category = Electronics + Type = Variable) returns intersected results only
-   [ ] **UAT-FL-007** — Clicking "Reset" link resets all dropdowns to defaults and restores full product list
-   [ ] **UAT-FL-008** — Filter + Search combined returns correctly intersected results

### Module 4: Add New Product

#### Page & layout

-   [ ] **UAT-AP-001** — Page loads with heading "Add New Product", two-column layout (main form left, "General Informations" sidebar right), all fields empty

#### Basic Information

-   [ ] **UAT-AP-002** — Leaving Product Title empty and clicking "Add Product" shows inline validation error; product not created
-   [ ] **UAT-AP-003** — Typing in Product Title and tabbing out auto-populates the Product Slug as lowercase hyphenated version
-   [ ] **UAT-AP-004** — Rich text editor (Description) toolbar works: Bold (⌘B), Italic (⌘I), Underline (⌘U), Strikethrough, Bulleted list, Numbered list, Align Left/Center/Right, Undo, Redo, Insert Link, Fullscreen

#### Media

-   [ ] **UAT-AP-005** — "Upload Image" opens media library/file picker; selected image previews on form
-   [ ] **UAT-AP-006** — "Upload Gallery Images" allows multiple images; all thumbnails appear in gallery section

#### Category & Taxonomy

-   [ ] **UAT-AP-007** — Category field supports multi-select; selected categories appear as removable tags (X chip)
-   [ ] **UAT-AP-025** — Tags field supports multi-select with autocomplete suggestions

#### Pricing

-   [ ] **UAT-AP-008** — Regular Price and Sale Price accept numeric values; entering Sale Price > Regular Price shows a validation warning
-   [ ] **UAT-AP-009** — Clicking "Schedule" link shows Sale Price Date From/To date pickers; valid date range accepted

#### Inventory

-   [ ] **UAT-AP-010** — Toggling "Enable product stock management" ON reveals: Quantity, Low Stock Threshold, Allow Backorders? fields; toggling OFF hides them
-   [ ] **UAT-AP-011** — Quantity = 0 is accepted; Quantity = negative number shows validation error
-   [ ] **UAT-AP-012** — "Allow Backorders?" shows options: Do not allow, Allow but notify customer, Allow
-   [ ] **UAT-AP-013** — "Stock Status" shows options: In stock, Out of stock, On backorder
-   [ ] **UAT-AP-014** — "Limit Purchases to 1 Item Per Order?" toggle activates and saves

#### Shipping

-   [ ] **UAT-AP-015** — Weight (decimal) and Dimensions (Length, Width, Height) accept valid numbers; non-numeric input is rejected
-   [ ] **UAT-AP-016** — Shipping Class dropdown shows "No shipping class" and any configured classes

#### Linked Products

-   [ ] **UAT-AP-017** — Upsells and Cross-sells fields show autocomplete suggestions when typing a product name; selected product appears as a tag

#### Others

-   [ ] **UAT-AP-018** — Catalog Visibility dropdown options: Shop and search results, Shop only, Search results only, Hidden
-   [ ] **UAT-AP-019** — "Mark this product as featured" checkbox saves correctly

#### Product Creation Flows

-   [ ] **UAT-AP-020** — Create Simple product with valid data → "Add Product" → success notice shown → product appears in list with "Simple" type and correct STATUS badge
-   [ ] **UAT-AP-021** — Change Type to "Variable" → save → product appears in list with "Variable" type
-   [ ] **UAT-AP-022** — Set Status = "Draft" → save → list shows grey "Draft" badge (not "Online")
-   [ ] **UAT-AP-023** — Set Status = "Pending Review" → save → product saved with Pending Review status
-   [ ] **UAT-AP-024** — Brand dropdown shows: Select brand, Apple Inc., Asus, HP — all selectable
-   [ ] **UAT-AP-026** — "Enable Reviews?" set to "Yes" saves correctly
-   [ ] **UAT-AP-027** — Clicking "Back" link returns to `/storesuite-dashboard/products/` without creating a product

### Module 5: Edit Product

#### Page Load

-   [ ] **UAT-EP-001** — Edit page (`/edit-product/{id}/`) loads with "Edit Product" heading and all fields pre-filled from saved product data (title, slug, permalink, category, type, price, stock, reviews, etc.)
-   [ ] **UAT-EP-012** — Right sidebar button reads "**Update Product**" (not "Add Product")

#### Update Flows

-   [ ] **UAT-EP-002** — Update Product Title → save → list and reload show new title
-   [ ] **UAT-EP-003** — Update Product Slug → save → permalink updates to new slug URL
-   [ ] **UAT-EP-004** — Update Regular Price → save → PRICE column in list reflects new value; other fields unchanged
-   [ ] **UAT-EP-005** — Remove category "Uncategorized", add "Electronics" → save → CATEGORY column shows "Electronics"
-   [ ] **UAT-EP-006** — Change Type from "Variable" to "Simple" → save → TYPE column updates; no data corruption
-   [ ] **UAT-EP-007** — Change Status to "Draft" → save → STATUS badge in list changes from green "Online" to grey "Draft"
-   [ ] **UAT-EP-008** — Enable stock management, set Quantity = 50 → save → reload edit page shows toggle ON and Quantity = 50
-   [ ] **UAT-EP-009** — Change "Enable Reviews?" to "No" → save → reload shows "No"; reviews disabled on frontend
-   [ ] **UAT-EP-010** — Change product image → save → new image appears in IMAGE column in product list
-   [ ] **UAT-EP-011** — Open edit page, make no changes, click "Update Product" → success notice shown; all values unchanged

#### Navigation

-   [ ] **UAT-EP-013** — "Back" link returns to `/storesuite-dashboard/products/` without saving unsaved changes
-   [ ] **UAT-EP-014** — Three-dot "Edit" from product list opens correct product edit page with its data pre-filled
-   [ ] **UAT-EP-015** — Three-dot "View" from product list opens the product's public frontend URL in browser
-   [ ] **UAT-EP-016** — Three-dot "Delete" from product list shows confirmation prompt; confirming removes product from list; cancelling keeps product

---

## Suggested follow-ups (engineering)

1. **Slug UX:** implement title→slug on blur for add form (and optionally on edit when slug empty), or update UAT if slug is intentionally manual.
2. **Client pricing guard:** optional `sale_price > regular_price` warning before AJAX submit.
3. **Dedicated UAT vendor + disposable products** to run **AP-021–023** and **EP-002–010** without touching shared regression SKUs.

---

**Report file:** `/Users/aiarnob/STORESUITE-UAT-Products-2026-04-11.md`
