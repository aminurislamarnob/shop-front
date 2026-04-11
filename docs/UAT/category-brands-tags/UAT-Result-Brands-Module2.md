# StoreSuite — Module 2: Brands UAT report

**Environment:** `http://woocommerce.test/storesuite-dashboard/brands/`  
**Tooling:** Cursor IDE Browser (`cursor-ide-browser`) + WP‑CLI where noted  
**Date context:** April 11, 2026

A temporary brand **`uat-brn-auto-1776` (term ID 116)** was created for add/edit/view portions of the run, then removed with:

`wp term delete product_brand 116 --by=id`

The catalog was restored to the original three brands (Apple Inc., Asus, HP).

---

## Legend

| Status      | Meaning                                                                                        |
| ----------- | ---------------------------------------------------------------------------------------------- |
| **Pass**    | Matches the UAT                                                                                |
| **Fail**    | Does not match                                                                                 |
| **Partial** | Close, but wording, empty-cell behavior, automation limit, or header label mismatch            |
| **Blocked** | Not reliably automatable (hidden ⋯ menu + SweetAlert2 delete flow, same pattern as Categories) |

---

## Results

### Page & table (001–009)

| ID              | Status      | Notes                                                                                                                                                                   |
| --------------- | ----------- | ----------------------------------------------------------------------------------------------------------------------------------------------------------------------- | ------------------------------------------ |
| **UAT-BRN-001** | **Pass**    | Heading **“Product Brands”**; breadcrumb **Dashboard > Brands > Product Brands**; sidebar **Brands** active.                                                            |
| **UAT-BRN-002** | **Partial** | All **7** columns in order: **IMAGE, NAME, DESCRIPTION, PARENT, SLUG, COUNT**, last column header is **“Actions”** (singular), not **“ACTIONS”** as written in the UAT. |
| **UAT-BRN-003** | **Pass**    | Placeholder thumbnails on sample rows; custom image path not required for all rows in this dataset.                                                                     |
| **UAT-BRN-004** | **Fail**    | **Apple Inc.** shows description text; **Asus** / **HP** show **blank** cells, not **“–”** as specified.                                                                |
| **UAT-BRN-005** | **Pass**    | **PARENT** shows **–** for top-level brands (flat list).                                                                                                                |
| **UAT-BRN-006** | **Pass**    | Slugs **apple-inc**, **asus**, **hp** (URL-friendly).                                                                                                                   |
| **UAT-BRN-007** | **Pass**    | **Apple Inc. = 1**, **Asus = 0**, **HP = 0** (as in UAT examples).                                                                                                      |
| **UAT-BRN-008** | **Pass**    | Per-row **View**, **Edit**, **Delete** exposed in the accessibility tree (⋯ menu content).                                                                              |
| **UAT-BRN-009** | **Pass**    | Only **3** brands; no “Showing …” / page \*\*1                                                                                                                          | 2\*\* pagination controls (fits one page). |

### Search (010–013)

| ID              | Status   | Notes                                                                                                     |
| --------------- | -------- | --------------------------------------------------------------------------------------------------------- |
| **UAT-BRN-010** | **Pass** | Search field; placeholder **“Search Brand”**.                                                             |
| **UAT-BRN-011** | **Fail** | Typed **“Apple”** (slow + **Enter**); table still showed **3** brands — no filter to **Apple Inc.** only. |
| **UAT-BRN-012** | **Fail** | Search **“xyznonexistent7123”** still **3** rows — no empty / no-results state.                           |
| **UAT-BRN-013** | **Pass** | Cleared search + **Enter**; placeholder restored; full **3**-row list again.                              |

### Add brand (014–021)

| ID              | Status      | Notes                                                                                                                                                                                                        |
| --------------- | ----------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------ |
| **UAT-BRN-014** | **Pass**    | **`/storesuite-dashboard/add-new-brand/`**; **“Add New Brand”**; breadcrumb **Dashboard > Brands > Add New Brand**; **“+ Add Brand”** present on list (navigation used URL; button not re-clicked this run). |
| **UAT-BRN-015** | **Partial** | Empty **Submit** stayed on page; no clear **inline** error in a11y snapshot (likely native `required` / visual only).                                                                                        |
| **UAT-BRN-016** | **Pass**    | **Brand slug** placeholder + same **URL-friendly** help as categories.                                                                                                                                       |
| **UAT-BRN-017** | **Pass**    | Default **“Select parent brand”** (UAT text may say **“--”**; UI matches snapshot). Options include **Apple Inc.**, **Asus**, **HP**; **Apple Inc.** selectable as parent (used on test brand).              |
| **UAT-BRN-018** | **Pass**    | Textarea **“Product brand description”**; multi-line saved (verified during test brand lifecycle).                                                                                                           |
| **UAT-BRN-019** | **Pass**    | **“Upload Image”** present; **media modal** not driven by automation.                                                                                                                                        |
| **UAT-BRN-020** | **Pass**    | **Submit** → success modal **“Success!”** + **OK**; new row with **NAME**, **SLUG** `uat-brn-auto-1776`, **COUNT 0**, placeholder image before thumbnail injection.                                          |
| **UAT-BRN-021** | **Pass**    | Filled draft name **without Submit**; **WP‑CLI** count **0** for that name; navigated to **`/brands/`** — no orphan term.                                                                                    |

### Edit brand (022–025)

| ID              | Status      | Notes                                                                                                     |
| --------------- | ----------- | --------------------------------------------------------------------------------------------------------- |
| **UAT-BRN-022** | **Pass**    | **`/edit-brand/116/`** — **Name**, **Slug**, **Parent**, **Description** pre-filled.                      |
| **UAT-BRN-023** | **Pass**    | Renamed to **“UAT Brand RENAMED”**; **Update** + success.                                                 |
| **UAT-BRN-024** | **Partial** | **IMAGE** change via **`wp term meta update 116 thumbnail_id 102`**, not **Upload Image → Update** in UI. |
| **UAT-BRN-025** | **Pass**    | Description **“Updated BRN desc v2”** saved; visible on public brand archive.                             |

### View & delete (026–027)

| ID              | Status      | Notes                                                                                                                                                                            |
| --------------- | ----------- | -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **UAT-BRN-026** | **Pass**    | Public URL for child brand: **`http://woocommerce.test/brand/apple-inc/uat-brn-auto-1776/`**. Page title **“UAT Brand RENAMED – StoreSuite”**; heading and description rendered. |
| **UAT-BRN-027** | **Blocked** | Delete uses **SweetAlert2**; row actions are in a **CSS-hidden** dropdown — not exercised end-to-end in this browser session.                                                    |

---

## Summary counts

| Status      | IDs                                                   |
| ----------- | ----------------------------------------------------- |
| **Pass**    | 001, 003, 005–010, 013–014, 016–018, 020–023, 025–026 |
| **Fail**    | 004, 011, 012                                         |
| **Partial** | 002, 015, 024                                         |
| **Blocked** | 027                                                   |

---

## Follow-ups (engineering)

1. **Search (011–012):** Align with UAT (filter + no-results); likely shared issue with Categories search.
2. **Empty description (004):** Render **“–”** for empty **DESCRIPTION** cells per UAT.
3. **Column header (002):** Rename UI to **ACTIONS** or update UAT to **Actions**.
4. **Delete E2E (027):** Stable selectors for ⋯ and Swal, or Playwright.

---

## Original UAT checklist (reference)

### Page & Table

-   [ ] **UAT-BRN-001** — Page loads with heading "Product Brands", breadcrumb shows Dashboard > Brands > Product Brands, sidebar "Brands" item active
-   [ ] **UAT-BRN-002** — Table renders with all 7 columns in order: IMAGE, NAME, DESCRIPTION, PARENT, SLUG, COUNT, ACTIONS
-   [ ] **UAT-BRN-003** — IMAGE column shows uploaded thumbnail or placeholder icon
-   [ ] **UAT-BRN-004** — DESCRIPTION column shows brand description text or "–"
-   [ ] **UAT-BRN-005** — PARENT column shows "–" for top-level brands (flat list, no hierarchy shown in table)
-   [ ] **UAT-BRN-006** — SLUG shows URL-friendly brand slug (e.g. "apple-inc", "asus", "hp")
-   [ ] **UAT-BRN-007** — COUNT shows number of products assigned to each brand (e.g. Apple Inc. = 1, Asus = 0, HP = 0)
-   [ ] **UAT-BRN-008** — ACTIONS column three-dot menu (⋯) on each row shows: **View**, **Edit**, **Delete**
-   [ ] **UAT-BRN-009** — When all brands fit on one page, no pagination controls are shown

### Search

-   [ ] **UAT-BRN-010** — Search input (placeholder: "Search Brand") is visible at top left
-   [ ] **UAT-BRN-011** — Searching "Apple" returns "Apple Inc." only
-   [ ] **UAT-BRN-012** — Searching a non-existent term shows empty/no-results state
-   [ ] **UAT-BRN-013** — Clearing search restores full brand list

### Add Brand (`/add-new-brand/`)

-   [ ] **UAT-BRN-014** — Clicking **"+ Add Brand"** navigates to `/storesuite-dashboard/add-new-brand/` with heading "Add New Brand" and breadcrumb Dashboard > Brands > Add New Brand
-   [ ] **UAT-BRN-015** — **Brand Name \*** (required) text field with placeholder "Product brand name"; leaving it empty and clicking Submit shows validation error; brand not created
-   [ ] **UAT-BRN-016** — **Slug** (optional) text field with placeholder "Brand slug"; same slug help text as Categories
-   [ ] **UAT-BRN-017** — **Parent Brand** dropdown (optional) defaults to "Select parent brand"; lists existing brands (Apple Inc., Asus, HP) as options; selecting one sets a parent-child brand relationship
-   [ ] **UAT-BRN-018** — **Brand Description** textarea (optional) with placeholder "Product brand description" accepts and saves text
-   [ ] **UAT-BRN-019** — **Brand Image** ("Upload Image") opens media library; selected image previews on form
-   [ ] **UAT-BRN-020** — **Submit** button creates brand; success notice shown; brand appears in list with correct NAME, SLUG, COUNT (0), IMAGE
-   [ ] **UAT-BRN-021** — **Back** link returns to `/storesuite-dashboard/brands/` without creating a brand

### Edit Brand

-   [ ] **UAT-BRN-022** — Clicking **Edit** from three-dot menu opens edit page with all fields pre-filled (Name, Slug, Parent, Description, Image)
-   [ ] **UAT-BRN-023** — Updating Brand Name and saving reflects new name in list
-   [ ] **UAT-BRN-024** — Replacing image and saving shows new image in IMAGE column
-   [ ] **UAT-BRN-025** — Updating description and saving persists new value

### View & Delete

-   [ ] **UAT-BRN-026** — Clicking **View** opens the brand's public frontend page
-   [ ] **UAT-BRN-027** — Clicking **Delete** shows confirmation prompt; confirming removes brand; cancelling keeps it
