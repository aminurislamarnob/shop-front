# StoreSuite — Cross-module consistency (Categories / Brands / Tags)

**Scope:** Taxonomy list pages and Add forms for **Categories**, **Brands**, and **Tags** in StoreSuite.  
**Evidence:** Static review of `wp-content/plugins/storesuite/templates/` (+ `includes/Rewrites.php` for page titles).  
**Date context:** April 11, 2026

**Environment (for live re-checks):** `http://woocommerce.test/storesuite-dashboard/` — paths: `categories/`, `brands/`, `tags/`, and respective `add-new-*` endpoints.

---

## Legend

| Status   | Meaning                                                               |
| -------- | --------------------------------------------------------------------- |
| **Pass** | Matches the intended cross-module pattern                             |
| **Flag** | Documented inconsistency or decision point (not necessarily a defect) |

---

## Results

| ID              | Status   | Notes                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                  |
| --------------- | -------- | ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **UAT-CRS-001** | **Pass** | All three list templates share the same layout shell: `my-storesuite-container` → sidebar → `main.my-storesuite-page-content` → `storesuite-table-header-part` with `col-md-6` **search** (GET `search_by`) on the **left** and **`my-storesuite-button` Add** link on the **right** → responsive **table** → per-row **⋯** dropdown with **View**, **Edit**, **Delete**. Files: `templates/categories/categories.php`, `templates/brands/brands.php`, `templates/tags/tags.php`. List **headings** / document titles are module-specific (`includes/Rewrites.php`: Product Categories, Product Brands, Product Tags). |
| **UAT-CRS-002** | **Pass** | All three **Add** forms include: **Name** (required, asterisk on label), **Slug** (optional) with the **same** slug help `<small>` text (_“The "slug" is the URL-friendly version of the name…”_), **Description** textarea (optional). Field **labels** differ by module (**Category Name** / **Brand Name** / **Tag Name**, etc.) — same pattern, not identical strings. Categories and Brands add **extra** fields (parent, image; category also **Display Type**); Tags do not — see CRS-003–005.                                                                                                                  |
| **UAT-CRS-003** | **Pass** | **Intentional:** **Categories** and **Brands** tables include an **Image** column; **Tags** does **not** (`tags.php` has no image `<th>`). Tags layout is **Name, Description, Slug, Count, Action** — correct for tags without media column.                                                                                                                                                                                                                                                                                                                                                                          |
| **UAT-CRS-004** | **Pass** | **Intentional:** **Categories** and **Brands** have a **Parent** column and parent `<select>` on Add/Edit; **Tags** has **no** parent column and `add-new-tag.php` has **no** parent field.                                                                                                                                                                                                                                                                                                                                                                                                                            |
| **UAT-CRS-005** | **Pass** | **Intentional:** **Display Type** exists only on the **Category** Add (and Edit) form (`add-new-category.php`, `edit-category.php`). **Brands** and **Tags** Add forms do **not** include it.                                                                                                                                                                                                                                                                                                                                                                                                                          |
| **UAT-CRS-006** | **Flag** | **Submit** vs **Add New Tag:** **Categories** and **Brands** use primary button label **`Submit`** (`add-new-category.php`, `add-new-brand.php`). **Tags** uses **`Add New Tag`** (`add-new-tag.php`). Consider standardising (e.g. all **Submit**, or **Add New Category** / **Add New Brand** / **Add New Tag**) for UX and documentation.                                                                                                                                                                                                                                                                           |
| **UAT-CRS-007** | **Flag** | **Action** vs **Actions:** **Categories** and **Tags** last column header string is **`Action`** (singular) — `categories.php`, `tags.php`. **Brands** uses **`Actions`** (plural, title case in source — not all-caps **`ACTIONS`**). Align to one term across the three taxonomy lists (and optionally with **Coupons**, which uses **`Actions`** in `coupons.php`).                                                                                                                                                                                                                                                 |

---

## Optional observations (not in CRS-001–007)

-   **Add** CTA link text differs: **Add Category** / **Add Brand** / **Add Tag** — consistent pattern, module-specific noun.
-   **Coupons** list uses **`Actions`** (same plural as Brands), so **Categories** + **Tags** are the outliers for **Action** vs **Actions** within the plugin’s list tables.

---

## Source references (StoreSuite)

| Area            | Path (under plugin `templates/`)  |
| --------------- | --------------------------------- |
| Categories list | `categories/categories.php`       |
| Brands list     | `brands/brands.php`               |
| Tags list       | `tags/tags.php`                   |
| Category Add    | `categories/add-new-category.php` |
| Brand Add       | `brands/add-new-brand.php`        |
| Tag Add         | `tags/add-new-tag.php`            |
| Endpoint titles | `includes/Rewrites.php`           |

---

## Original checklist (reference)

-   [ ] **UAT-CRS-001** — All three list pages share a consistent layout: heading, breadcrumb, search input (left), Add button (right), table, three-dot row actions
-   [ ] **UAT-CRS-002** — All three Add forms share: Name field (required, asterisk), Slug field (optional, same help text), Description textarea (optional)
-   [ ] **UAT-CRS-003** — Categories and Brands have IMAGE column; Tags does NOT — confirm this is intentional and Tags table layout is correct without it
-   [ ] **UAT-CRS-004** — Categories and Brands have PARENT column; Tags does NOT — confirm Tags form has no parent field
-   [ ] **UAT-CRS-005** — Categories Add form has "Display Type" dropdown unique to it; Brands and Tags do NOT — confirm this is intentional
-   [ ] **UAT-CRS-006** — Categories and Brands Add form button is labelled "Submit"; Tags Add form button is labelled "Add New Tag" — flag this inconsistency if it should be standardised
-   [ ] **UAT-CRS-007** — Brands table column header reads "ACTIONS" (plural); Categories and Tags read "ACTION" (singular) — flag this inconsistency

**Note on CRS-007 vs code:** Default English in the repo is **`Actions`** (title case) for Brands, not all-caps **`ACTIONS`**. The inconsistency to fix in copy/design is **singular `Action`** vs **plural `Actions`**.
