## Overview

User Acceptance Testing checklist for the **Categories**, **Brands**, and **Tags** taxonomy modules in the StoreSuite vendor dashboard.

**Pages covered:**

-   `/storesuite-dashboard/categories/` + `/storesuite-dashboard/add-new-category/`
-   `/storesuite-dashboard/brands/` + `/storesuite-dashboard/add-new-brand/`
-   `/storesuite-dashboard/tags/` + `/storesuite-dashboard/add-new-tag/`

**Role under test:** Logged-in vendor/seller
**Prepared:** 2026-04-10

> Tick each box as **Pass ✅**, or comment with **Fail ❌** + repro steps.

---

## Module 1: Categories (`/categories/`)

### Page & Table

-   [ ] **UAT-CAT-001** — Page loads with heading "Product Categories", breadcrumb shows Dashboard > Categories > Product Categories, sidebar "Categories" item active
-   [ ] **UAT-CAT-002** — Table renders with all 7 columns in order: IMAGE, NAME, DESCRIPTION, PARENT, SLUG, COUNT, ACTION
-   [ ] **UAT-CAT-003** — Parent categories (e.g. Clothing, Decor, Electronics) appear without indentation; child categories appear indented with an em dash prefix (e.g. "— Accessories")
-   [ ] **UAT-CAT-004** — PARENT column shows the parent name for child categories and "–" for top-level categories
-   [ ] **UAT-CAT-005** — IMAGE column shows the uploaded thumbnail when one exists; shows a placeholder icon when no image is set
-   [ ] **UAT-CAT-006** — DESCRIPTION column shows the description text or "–" when empty
-   [ ] **UAT-CAT-007** — SLUG column shows the URL-friendly slug for each category
-   [ ] **UAT-CAT-008** — COUNT column shows the number of products assigned to each category (e.g. Clothing = 16, Accessories = 6)
-   [ ] **UAT-CAT-009** — ACTION column three-dot menu (⋯) on each row shows: **View**, **Edit**, **Delete**

### Pagination

-   [ ] **UAT-CAT-010** — Pagination shows "Showing 1 to 15 of 23"; page 1 and 2 are clickable; → (next) arrow works; clicking page 2 loads remaining categories

### Search

-   [ ] **UAT-CAT-011** — Search input (placeholder: "Search Category") is visible at top left of table
-   [ ] **UAT-CAT-012** — Searching an exact category name (e.g. "Clothing") returns only that category and its children
-   [ ] **UAT-CAT-013** — Searching a partial name returns all matching categories
-   [ ] **UAT-CAT-014** — Searching a non-existent term shows an empty/no-results state
-   [ ] **UAT-CAT-015** — Clearing search input restores the full category list

### Add Category (`/add-new-category/`)

-   [ ] **UAT-CAT-016** — Clicking **"+ Add Category"** (blue button, top right) navigates to `/storesuite-dashboard/add-new-category/` with heading "Add New Category" and breadcrumb Dashboard > Categories > Add New Category
-   [ ] **UAT-CAT-017** — **Category Name \*** (required) text field with placeholder "Product category name"; leaving it empty and clicking Submit shows inline validation error; category not created
-   [ ] **UAT-CAT-018** — **Slug** (optional) text field with placeholder "Category slug"; help text reads: "The 'slug' is the URL-friendly version of the name. It is usually all lowercase and contains only letters, numbers, and hyphens."
-   [ ] **UAT-CAT-019** — **Select Parent Category** dropdown (optional) defaults to "-- Select parent category --"; all existing categories are listed as options; selecting one sets this as a child category
-   [ ] **UAT-CAT-020** — **Category Description** textarea (optional) with placeholder "Product category description" accepts and saves multi-line text
-   [ ] **UAT-CAT-021** — **Display Type** dropdown (optional) defaults to "Default"; options: Default, Products, Subcategories, Both — all selectable and saved correctly
-   [ ] **UAT-CAT-022** — **Category Image** upload button ("Upload Image") opens media library/file picker; selected image previews on form
-   [ ] **UAT-CAT-023** — **Submit** button (blue) creates category with all filled values; success notice shown; new category appears in list with correct NAME, PARENT, SLUG, COUNT (0), and IMAGE
-   [ ] **UAT-CAT-024** — Category created with a parent appears in list indented under that parent with "—" prefix; PARENT column shows parent name
-   [ ] **UAT-CAT-025** — **Back** link returns to `/storesuite-dashboard/categories/` without creating a category

### Edit Category

-   [ ] **UAT-CAT-026** — Clicking **Edit** from three-dot menu opens edit page with all fields pre-filled (Name, Slug, Parent, Description, Display Type, Image)
-   [ ] **UAT-CAT-027** — Updating Category Name and saving reflects new name in list
-   [ ] **UAT-CAT-028** — Changing Parent Category and saving moves the category into the new hierarchy in the list
-   [ ] **UAT-CAT-029** — Changing Display Type and saving persists the new value
-   [ ] **UAT-CAT-030** — Replacing the image and saving shows new image in IMAGE column

### View & Delete

-   [ ] **UAT-CAT-031** — Clicking **View** from three-dot menu opens the category's public frontend page (e.g. `woocommerce.test/product-category/clothing/`)
-   [ ] **UAT-CAT-032** — Clicking **Delete** from three-dot menu shows a confirmation prompt; confirming removes the category from the list; cancelling keeps it
-   [ ] **UAT-CAT-033** — Deleting a parent category that has children: children remain in list (either moved to top-level or behaviour is documented)

---

## Module 2: Brands (`/brands/`)

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

-   [ ] **UAT-BRN-014** — Clicking **"+ Add Brand"** (blue button, top right) navigates to `/storesuite-dashboard/add-new-brand/` with heading "Add New Brand" and breadcrumb Dashboard > Brands > Add New Brand
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

---

## Module 3: Tags (`/tags/`)

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

---

## Cross-Module Consistency Checks

-   [ ] **UAT-CRS-001** — All three list pages share a consistent layout: heading, breadcrumb, search input (left), Add button (right), table, three-dot row actions
-   [ ] **UAT-CRS-002** — All three Add forms share: Name field (required, asterisk), Slug field (optional, same help text), Description textarea (optional)
-   [ ] **UAT-CRS-003** — Categories and Brands have IMAGE column; Tags does NOT — confirm this is intentional and Tags table layout is correct without it
-   [ ] **UAT-CRS-004** — Categories and Brands have PARENT column; Tags does NOT — confirm Tags form has no parent field
-   [ ] **UAT-CRS-005** — Categories Add form has "Display Type" dropdown unique to it; Brands and Tags do NOT — confirm this is intentional
-   [ ] **UAT-CRS-006** — Categories and Brands Add form button is labelled "Submit"; Tags Add form button is labelled "Add New Tag" — flag this inconsistency if it should be standardised
-   [ ] **UAT-CRS-007** — Brands table column header reads "ACTIONS" (plural); Categories and Tags read "ACTION" (singular) — flag this inconsistency

---

## Known UI Inconsistencies (Observed — Verify or Fix)

| ID    | Location      | Issue                                                                                   | Severity |
| ----- | ------------- | --------------------------------------------------------------------------------------- | -------- |
| B-001 | Brands table  | Column header "ACTIONS" (plural) vs "ACTION" (singular) in Categories/Tags              | Low      |
| B-002 | Tags add form | Primary button "Add New Tag" — inconsistent with "Submit" used in Categories and Brands | Low      |

---

## Exit Criteria

The following must **all pass** before QA sign-off:

| ID          | Scenario                                     | Priority    |
| ----------- | -------------------------------------------- | ----------- |
| UAT-CAT-001 | Categories list loads with correct columns   | 🔴 Critical |
| UAT-CAT-003 | Hierarchy (parent/child) displayed correctly | 🔴 Critical |
| UAT-CAT-010 | Pagination works                             | 🟠 High     |
| UAT-CAT-012 | Search by name works                         | 🔴 Critical |
| UAT-CAT-017 | Required field validation blocks save        | 🔴 Critical |
| UAT-CAT-023 | New category created and appears in list     | 🔴 Critical |
| UAT-CAT-024 | Child category appears under parent          | 🔴 Critical |
| UAT-CAT-026 | Edit opens with pre-filled data              | 🔴 Critical |
| UAT-CAT-032 | Delete with confirmation works               | 🔴 Critical |
| UAT-BRN-001 | Brands list loads with correct columns       | 🔴 Critical |
| UAT-BRN-015 | Required field validation blocks save        | 🔴 Critical |
| UAT-BRN-020 | New brand created and appears in list        | 🔴 Critical |
| UAT-BRN-022 | Edit opens with pre-filled data              | 🔴 Critical |
| UAT-BRN-027 | Delete with confirmation works               | 🔴 Critical |
| UAT-TAG-001 | Tags list loads without IMAGE/PARENT columns | 🔴 Critical |
| UAT-TAG-015 | Required field validation blocks save        | 🔴 Critical |
| UAT-TAG-018 | New tag created and appears in list          | 🔴 Critical |
| UAT-TAG-020 | Edit opens with pre-filled data              | 🔴 Critical |
| UAT-TAG-024 | Delete with confirmation works               | 🔴 Critical |
| UAT-CRS-001 | Consistent layout across all three modules   | 🟠 High     |
