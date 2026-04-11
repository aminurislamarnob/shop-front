# StoreSuite — Module 3: Tags UAT report

**Environment:** `http://woocommerce.test/storesuite-dashboard/tags/`  
**Tooling:** Cursor IDE Browser (`cursor-ide-browser`) + WP‑CLI where noted  
**Date context:** April 11, 2026

A temporary tag **`uat-tag-auto-2026` (term ID 117)** was created for add/edit/view portions of the run, then removed with:

`wp term delete product_tag 117 --by=id`

The catalog was restored to the original four tags (**Design**, **Development**, **Electronics**, **WordPress**).

A **Back / no-save** check used the draft name **`SHOULD_NOT_PERSIST_9191`**; **WP‑CLI** search count was **0** (no orphan term).

---

## Legend

| Status      | Meaning                                                                                                 |
| ----------- | ------------------------------------------------------------------------------------------------------- |
| **Pass**    | Matches the UAT                                                                                         |
| **Fail**    | Does not match                                                                                          |
| **Partial** | Close, but wording, empty-cell behavior, automation limit, or validation not visible in a11y snapshot   |
| **Blocked** | Not reliably automatable (hidden ⋯ menu + SweetAlert2 delete flow, same pattern as Categories / Brands) |

---

## Results

### Page & table (001–008)

| ID              | Status   | Notes                                                                                                                                                       |
| --------------- | -------- | ----------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **UAT-TAG-001** | **Pass** | Heading **Product Tags**; breadcrumb **Dashboard > Tags > Product Tags**; sidebar **Tags** active.                                                          |
| **UAT-TAG-002** | **Pass** | Table has **five** columns in order: **Name**, **Description**, **Slug**, **Count**, **Action** — no image, no parent (per `templates/tags/tags.php`).      |
| **UAT-TAG-003** | **Pass** | **NAME** column outputs plain `esc_html( $product_tag->name )` — no images or hierarchy/indentation.                                                        |
| **UAT-TAG-004** | **Fail** | **Electronics** had an empty description in the DB; the cell is **blank**, not **“–”** as specified (`tags.php` echoes description with no empty fallback). |
| **UAT-TAG-005** | **Pass** | Slugs URL-friendly (e.g. **design**, **development**, **electronics**, **wordpress**) per **WP‑CLI** `term list`.                                           |
| **UAT-TAG-006** | **Pass** | **COUNT** matches DB (e.g. **Development = 1**, others **0** in this dataset).                                                                              |
| **UAT-TAG-007** | **Pass** | Per-row **View**, **Edit**, **Delete** exposed in the accessibility tree (⋯ menu content).                                                                  |
| **UAT-TAG-008** | **Pass** | Tags fit one page; no pagination controls when `max_num_pages <= 1`.                                                                                        |

### Search (009–012)

| ID              | Status      | Notes                                                                                                                                                                                                                                                              |
| --------------- | ----------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------ |
| **UAT-TAG-009** | **Pass**    | Search input; placeholder **Search Tag**.                                                                                                                                                                                                                          |
| **UAT-TAG-010** | **Partial** | **`/tags/?search_by=Design`** returns **only** the **Design** row (server filter works). In MCP, **fill + Enter** and **type … submit** did **not** add `?search_by=` or reduce rows — **manual** verification of Enter-to-submit in a normal browser recommended. |
| **UAT-TAG-011** | **Pass**    | **`?search_by=xyznonexistent7123`** → **No tag found!** empty / no-results state.                                                                                                                                                                                  |
| **UAT-TAG-012** | **Pass**    | Navigating back to **`/storesuite-dashboard/tags/`** restores the full tag list.                                                                                                                                                                                   |

### Add tag (013–019)

| ID              | Status      | Notes                                                                                                                                                                                                                                                                        |
| --------------- | ----------- | ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **UAT-TAG-013** | **Partial** | **`/storesuite-dashboard/add-new-tag/`**; heading **Add New Tag**; breadcrumb **Dashboard > Tags > Add New Tag**. List CTA accessible name is **Add Tag** (**+** is visual in the button, not in the accessible name) — not the literal string **“+ Add Tag”** from the UAT. |
| **UAT-TAG-014** | **Pass**    | Exactly **Tag Name \***, **Slug**, **Tag Description** — no parent, display type, or image.                                                                                                                                                                                  |
| **UAT-TAG-015** | **Partial** | Empty name + **Add New Tag**: **JS** validation in `form-handler.js` (`validateRequiredFields`); **no** validation message in the a11y snapshot after empty submit in this session.                                                                                          |
| **UAT-TAG-016** | **Pass**    | **Slug** placeholder **Tag slug**; same slug help **small** text as other modules (`add-new-tag.php`).                                                                                                                                                                       |
| **UAT-TAG-017** | **Pass**    | **Tag Description** textarea; placeholder **Product tag description**.                                                                                                                                                                                                       |
| **UAT-TAG-018** | **Pass**    | Primary button **Add New Tag**; success (**Success** / Swal); tag **117** created and appeared in the list.                                                                                                                                                                  |
| **UAT-TAG-019** | **Pass**    | Draft name **`SHOULD_NOT_PERSIST_9191`** without submit; **WP‑CLI** search count **0**; **Back** is a normal link to **`/tags/`** (MCP click on **Back** did not navigate in one attempt; outcome verified via DB + direct navigation).                                      |

### Edit tag (020–022)

| ID              | Status   | Notes                                                                                                              |
| --------------- | -------- | ------------------------------------------------------------------------------------------------------------------ |
| **UAT-TAG-020** | **Pass** | **`/edit-tag/117/`** — **Tag Name \***, **Slug**, **Tag Description** pre-filled.                                  |
| **UAT-TAG-021** | **Pass** | Renamed to **UAT Tag RENAMED 2026**; **Save Changes** + success; list and public title updated.                    |
| **UAT-TAG-022** | **Pass** | Description **UAT desc updated v3 final.** — confirmed with **`wp term get product_tag 117 --field=description`**. |

### View & delete (023–024)

| ID              | Status      | Notes                                                                                                                                                                           |
| --------------- | ----------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **UAT-TAG-023** | **Pass**    | Public archive **`http://woocommerce.test/product-tag/uat-tag-auto-2026/`** — document title **UAT Tag RENAMED 2026 – StoreSuite**; **H1** and description text on the archive. |
| **UAT-TAG-024** | **Blocked** | **Delete** + SweetAlert2 confirm/cancel not driven end-to-end (hidden ⋯ pattern).                                                                                               |

---

## Summary counts

| Status      | IDs                                              |
| ----------- | ------------------------------------------------ |
| **Pass**    | 001–003, 005–009, 011–012, 014, 016–019, 020–023 |
| **Fail**    | 004                                              |
| **Partial** | 010, 013, 015                                    |
| **Blocked** | 024                                              |

---

## Follow-ups (engineering)

1. **004:** Render **“–”** for empty **DESCRIPTION** cells in `templates/tags/tags.php`.
2. **010:** Ensure search **GET** form submits reliably on **Enter** (or add explicit submit); align MCP E2E with real UX.
3. **015:** Expose validation to assistive tech (e.g. `aria-invalid` + `aria-describedby`) if not already on the live invalid markup.
4. **013:** Align UAT copy with UI (**Add Tag** vs **+ Add Tag**) or change button label.
5. **024:** Stable selectors for ⋯ + Swal, or Playwright coverage.

---

## Original UAT checklist (reference)

### Page & Table

-   [ ] **UAT-TAG-001** — Page loads with heading "Product Tags", breadcrumb shows Dashboard > Tags > Product Tags, sidebar "Tags" item active
-   [ ] **UAT-TAG-002** — Table renders with exactly 5 columns in order: NAME, DESCRIPTION, SLUG, COUNT, ACTION (note: NO IMAGE column, NO PARENT column — by design)
-   [ ] **UAT-TAG-003** — NAME column shows tag name as plain text (no images, no hierarchy/indentation)
-   [ ] **UAT-TAG-004** — DESCRIPTION column shows tag description or "–"
-   [ ] **UAT-TAG-005** — SLUG shows URL-friendly tag slug
-   [ ] **UAT-TAG-006** — COUNT shows number of products using this tag
-   [ ] **UAT-TAG-007** — ACTION column three-dot menu (⋯) on each row shows: **View**, **Edit**, **Delete**
-   [ ] **UAT-TAG-008** — When all tags fit on one page, no pagination controls are shown

### Search

-   [ ] **UAT-TAG-009** — Search input (placeholder: "Search Tag") is visible at top left
-   [ ] **UAT-TAG-010** — Searching "Design" returns only the "Design" tag
-   [ ] **UAT-TAG-011** — Searching a non-existent term shows empty/no-results state
-   [ ] **UAT-TAG-012** — Clearing search restores full tag list

### Add Tag (`/add-new-tag/`)

-   [ ] **UAT-TAG-013** — Clicking **"+ Add Tag"** (blue button, top right) navigates to `/storesuite-dashboard/add-new-tag/` with heading "Add New Tag" and breadcrumb Dashboard > Tags > Add New Tag
-   [ ] **UAT-TAG-014** — Form contains exactly 3 fields: **Tag Name \*** (required), **Slug** (optional), **Tag Description** (optional textarea) — NO parent dropdown, NO display type, NO image upload (by design)
-   [ ] **UAT-TAG-015** — **Tag Name \*** text field with placeholder "Product tag name"; leaving it empty and clicking "Add New Tag" shows validation error; tag not created
-   [ ] **UAT-TAG-016** — **Slug** (optional) with placeholder "Tag slug" and same slug help text as other modules
-   [ ] **UAT-TAG-017** — **Tag Description** textarea (optional) with placeholder "Product tag description"
-   [ ] **UAT-TAG-018** — Primary button is labelled **"Add New Tag"** (not "Submit" — different from Categories and Brands); clicking it creates the tag; success notice shown; tag appears in list
-   [ ] **UAT-TAG-019** — **Back** link returns to `/storesuite-dashboard/tags/` without creating a tag

### Edit Tag

-   [ ] **UAT-TAG-020** — Clicking **Edit** from three-dot menu opens edit page with Name, Slug, Description pre-filled
-   [ ] **UAT-TAG-021** — Updating Tag Name and saving reflects new name in list
-   [ ] **UAT-TAG-022** — Updating Description and saving persists new value

### View & Delete

-   [ ] **UAT-TAG-023** — Clicking **View** opens the tag's public frontend archive page
-   [ ] **UAT-TAG-024** — Clicking **Delete** shows confirmation prompt; confirming removes tag; cancelling keeps it
