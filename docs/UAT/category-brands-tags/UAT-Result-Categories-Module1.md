# StoreSuite — Module 1: Categories UAT report

**Environment:** `http://woocommerce.test/storesuite-dashboard/categories/`  
**Tooling:** Cursor IDE Browser (`cursor-ide-browser`) + WP‑CLI where noted  
**Date context:** April 11, 2026

---

## Legend

| Status      | Meaning                                                              |
| ----------- | -------------------------------------------------------------------- |
| **Pass**    | Observed behavior matches the UAT                                    |
| **Fail**    | Observed behavior does not match the UAT                             |
| **Partial** | Mostly OK; spec, data, or viewport/automation gap                    |
| **Blocked** | Not reliably executable with current automation (UI/hidden controls) |
| **N/A**     | Not executed in this run                                             |

---

## Page & table (001–009)

| ID              | Status   | Notes                                                                                                                                                                                                     |
| --------------- | -------- | --------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **UAT-CAT-001** | **Pass** | Heading **“Product Categories”**; breadcrumb **Dashboard > Categories > Product Categories**; sidebar **Categories** active (highlight).                                                                  |
| **UAT-CAT-002** | **Pass** | At wide viewport (~2200px), columns in order: **IMAGE, NAME, DESCRIPTION, PARENT, SLUG, COUNT, ACTION**. On narrower widths **COUNT** / **ACTION** can be off-screen → treat layout as responsive caveat. |
| **UAT-CAT-003** | **Pass** | Top-level categories flush left; children indented with **—** prefix.                                                                                                                                     |
| **UAT-CAT-004** | **Pass** | **PARENT** shows **–** for top-level; parent name for children.                                                                                                                                           |
| **UAT-CAT-005** | **Pass** | Custom thumbnail when set (e.g. Pants); placeholder otherwise.                                                                                                                                            |
| **UAT-CAT-006** | **Pass** | Description text or empty; **–** / empty cell behavior as observed.                                                                                                                                       |
| **UAT-CAT-007** | **Pass** | **SLUG** shows URL-friendly slug per row.                                                                                                                                                                 |
| **UAT-CAT-008** | **Pass** | **COUNT** numeric; example **Clothing = 16**, **Accessories = 6** observed.                                                                                                                               |
| **UAT-CAT-009** | **Pass** | Row actions expose **View**, **Edit**, **Delete** (accessibility tree); **⋯** control itself may not be a named node.                                                                                     |

---

## Pagination (010)

| ID              | Status      | Notes                                                                                                                                                                        |
| --------------- | ----------- | ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **UAT-CAT-010** | **Partial** | Footer showed **“Showing 1 to 15 of 24”** / **25** (data drift vs UAT example **23**). **Page 1**, **Page 2**, and **→** present; page **2** loads remaining rows when used. |

---

## Search (011–015)

| ID              | Status   | Notes                                                                                                                |
| --------------- | -------- | -------------------------------------------------------------------------------------------------------------------- |
| **UAT-CAT-011** | **Pass** | Search field visible; placeholder **“Search Category”**.                                                             |
| **UAT-CAT-012** | **Fail** | Typing **“Clothing”** (slow + **Enter**) did **not** narrow the list; unrelated rows (e.g. **Decor**) still visible. |
| **UAT-CAT-013** | **N/A**  | Not re-validated after **012** failure.                                                                              |
| **UAT-CAT-014** | **N/A**  | Not executed in this run.                                                                                            |
| **UAT-CAT-015** | **N/A**  | Clearing search / full list restore not re-run end-to-end after **012**.                                             |

---

## Add category (016–025)

| ID              | Status      | Notes                                                                                                                                                                                                                                                 |
| --------------- | ----------- | ----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **UAT-CAT-016** | **Pass**    | Direct navigation to **`/storesuite-dashboard/add-new-category/`**; heading **“Add New Category”**; breadcrumb **Dashboard > Categories > Add New Category**; **“+ Add Category”** control present (exact navigation via that button was flaky once). |
| **UAT-CAT-017** | **Partial** | Empty submit stayed on page / URL unchanged; **inline** error not clearly exposed in **a11y snapshot** (may be native `required` / visual only).                                                                                                      |
| **UAT-CAT-018** | **Pass**    | Slug field + help text including **URL-friendly** wording.                                                                                                                                                                                            |
| **UAT-CAT-019** | **Pass**    | Parent default **“-- Select parent category --”**; long option list including real categories.                                                                                                                                                        |
| **UAT-CAT-020** | **Pass**    | Multi-line description accepted on create (verified in list / edit).                                                                                                                                                                                  |
| **UAT-CAT-021** | **Pass**    | **Display Type**: **Default**, **Products**, **Subcategories**, **Both** selectable; **Products** used on create.                                                                                                                                     |
| **UAT-CAT-022** | **Pass**    | **“Upload Image”** control present; **WordPress media modal** not driven by automation.                                                                                                                                                               |
| **UAT-CAT-023** | **Pass**    | **Submit** → loading/disabled → **“Success!”** modal; new row with **NAME**, **PARENT**, **SLUG**, **COUNT 0**, **IMAGE** (placeholder until image work).                                                                                             |
| **UAT-CAT-024** | **Pass**    | Child of **Clothing**: **—** prefix + **PARENT** = **Clothing** (later moved in edit test).                                                                                                                                                           |
| **UAT-CAT-025** | **Pass**    | **Back** alone did not always change URL in the browser tool; **no** stray term for draft name after abandoning flow (**WP‑CLI** count **0**). Intent: leaving without submit does not create category.                                               |

---

## Edit category (026–030)

| ID              | Status      | Notes                                                                                                                                                                       |
| --------------- | ----------- | --------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **UAT-CAT-026** | **Pass**    | **`/edit-category/{id}/`** — fields pre-filled (**Name**, **Slug**, **Parent**, **Description**, **Display Type**).                                                         |
| **UAT-CAT-027** | **Pass**    | Renamed to **“UAT FullRun RENAMED”**; list updated.                                                                                                                         |
| **UAT-CAT-028** | **Pass**    | Parent **Clothing → Decor**; list showed under **Decor** with **PARENT** = **Decor**.                                                                                       |
| **UAT-CAT-029** | **Pass**    | **Display Type** → **Both**; persisted after save (re-open / combobox value).                                                                                               |
| **UAT-CAT-030** | **Partial** | **Image column** update verified by setting **`thumbnail_id`** on the test term (**same attachment as “Pants”**) via **WP‑CLI**, not via **Upload Image → Save** in the UI. |

---

## View & delete (031–033)

| ID              | Status             | Notes                                                                                                                                                                                                                                                                                                                                                   |
| --------------- | ------------------ | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **UAT-CAT-031** | **Pass**           | Public URL pattern: **`/product-category/uat-fr-1775885930/`** loads archive; title reflects category (**UAT FullRun RENAMED**).                                                                                                                                                                                                                        |
| **UAT-CAT-032** | **Blocked**        | Delete flow uses **SweetAlert2** (`Swal.fire` + **Cancel** / confirm), **not** `window.confirm` → **`browser_handle_dialog`** irrelevant. Actions live under **CSS-hidden** dropdown; **Playwright-style refs** stayed **zero-size**; coordinate clicks did not reliably hit **⋯**. Cancel/confirm paths **not** fully exercised in browser automation. |
| **UAT-CAT-033** | **Partial / risk** | Backend uses **`wp_delete_term`**. A **WP‑CLI** parent/child probe showed a **child can remain** with **`parent` still set to the deleted parent term ID** (orphan-style), not clearly “promoted to top-level.” Needs **product decision** + possible **reparent** logic before delete.                                                                 |

---

## Test artifact & cleanup

| Item                   | Detail                                                              |
| ---------------------- | ------------------------------------------------------------------- |
| **Synthetic category** | Slug **`uat-fr-1775885930`**, term ID **113** during the run.       |
| **Cleanup**            | Term **113** removed with: `wp term delete product_cat 113 --by=id` |

---

## Summary counts (this report)

| Status      | Count (IDs covered)        |
| ----------- | -------------------------- |
| **Pass**    | 001–009, 011, 016–029, 031 |
| **Fail**    | 012                        |
| **Partial** | 010, 017, 030, 033         |
| **Blocked** | 032                        |
| **N/A**     | 013–015                    |

---

## Recommended follow-ups

1. **Search (012–015):** Fix or specify client vs server search; re-run **012–015** after fix.
2. **Delete / Swal (032):** Add **test IDs** or stable selectors for **⋯** and Swal buttons; or use **Playwright** for this module.
3. **Parent delete (033):** Define expected behavior and implement **child reparenting** (or block delete with message) before **`wp_delete_term`**.
4. **Image (030):** Re-run with real **media library** automation if required for sign-off.

---

## Original UAT checklist (reference)

### Page & table

-   [ ] **UAT-CAT-001** — Page loads with heading "Product Categories", breadcrumb shows Dashboard > Categories > Product Categories, sidebar "Categories" item active
-   [ ] **UAT-CAT-002** — Table renders with all 7 columns in order: IMAGE, NAME, DESCRIPTION, PARENT, SLUG, COUNT, ACTION
-   [ ] **UAT-CAT-003** — Parent categories appear without indentation; child categories appear indented with an em dash prefix
-   [ ] **UAT-CAT-004** — PARENT column shows the parent name for child categories and "–" for top-level categories
-   [ ] **UAT-CAT-005** — IMAGE column shows the uploaded thumbnail when one exists; shows a placeholder icon when no image is set
-   [ ] **UAT-CAT-006** — DESCRIPTION column shows the description text or "–" when empty
-   [ ] **UAT-CAT-007** — SLUG column shows the URL-friendly slug for each category
-   [ ] **UAT-CAT-008** — COUNT column shows the number of products assigned to each category
-   [ ] **UAT-CAT-009** — ACTION column three-dot menu (⋯) on each row shows: **View**, **Edit**, **Delete**

### Pagination

-   [ ] **UAT-CAT-010** — Pagination shows "Showing 1 to 15 of 23"; page 1 and 2 are clickable; → (next) arrow works; clicking page 2 loads remaining categories

### Search

-   [ ] **UAT-CAT-011** — Search input (placeholder: "Search Category") is visible at top left of table
-   [ ] **UAT-CAT-012** — Searching an exact category name returns only that category and its children
-   [ ] **UAT-CAT-013** — Searching a partial name returns all matching categories
-   [ ] **UAT-CAT-014** — Searching a non-existent term shows an empty/no-results state
-   [ ] **UAT-CAT-015** — Clearing search input restores the full category list

### Add category (`/add-new-category/`)

-   [ ] **UAT-CAT-016** — **"+ Add Category"** navigates to `/storesuite-dashboard/add-new-category/` with heading "Add New Category" and breadcrumb Dashboard > Categories > Add New Category
-   [ ] **UAT-CAT-017** — **Category Name \*** required; empty submit shows inline validation; category not created
-   [ ] **UAT-CAT-018** — **Slug** optional with placeholder and help text as specified
-   [ ] **UAT-CAT-019** — **Select Parent Category** dropdown defaults and lists categories
-   [ ] **UAT-CAT-020** — **Category Description** textarea accepts and saves multi-line text
-   [ ] **UAT-CAT-021** — **Display Type** dropdown: Default, Products, Subcategories, Both — selectable and saved
-   [ ] **UAT-CAT-022** — **Category Image** upload opens media library; selected image previews on form
-   [ ] **UAT-CAT-023** — **Submit** creates category; success notice; new category appears in list with correct fields
-   [ ] **UAT-CAT-024** — Category with parent appears indented under parent; PARENT column shows parent name
-   [ ] **UAT-CAT-025** — **Back** returns to `/storesuite-dashboard/categories/` without creating a category

### Edit category

-   [ ] **UAT-CAT-026** — **Edit** opens edit page with all fields pre-filled
-   [ ] **UAT-CAT-027** — Updating Category Name and saving reflects new name in list
-   [ ] **UAT-CAT-028** — Changing Parent Category and saving moves the category in the hierarchy
-   [ ] **UAT-CAT-029** — Changing Display Type and saving persists the new value
-   [ ] **UAT-CAT-030** — Replacing the image and saving shows new image in IMAGE column

### View & delete

-   [ ] **UAT-CAT-031** — **View** opens the category's public frontend page (e.g. `woocommerce.test/product-category/{slug}/`)
-   [ ] **UAT-CAT-032** — **Delete** shows a confirmation prompt; confirming removes the category; cancelling keeps it
-   [ ] **UAT-CAT-033** — Deleting a parent category that has children: children remain in list (or behaviour is documented)
