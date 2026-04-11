## Overview

User Acceptance Testing checklist for the **Products** module in the StoreSuite vendor dashboard.

**Base URL:** `http://woocommerce.test/storesuite-dashboard/products/`
**Pages covered:**

-   `/storesuite-dashboard/products/`
-   `/storesuite-dashboard/add-new-product/`
-   `/storesuite-dashboard/edit-product/{id}/`

**Role under test:** Logged-in vendor/seller
**Prepared:** 2026-04-10

> Check each box as **Pass ✅** by ticking it, or add a comment with **Fail ❌** + repro steps.

---

## Module 1: Product List

-   [ ] **UAT-PL-001** — Page loads with heading "All Products", correct breadcrumb, sidebar "Products" item active, table visible with columns: IMAGE, NAME, CATEGORY, STATUS, SKU, STOCK, PRICE, TYPE, ACTIONS
-   [ ] **UAT-PL-002** — Each product row shows: thumbnail, name (clickable), category (clickable), status badge (green "Online" / grey "Draft"), SKU (or `–`), stock badge (green "In Stock" / red "Out of Stock"), price, type, three-dot actions menu
-   [ ] **UAT-PL-003** — Three-dot Actions menu on each row shows: **View**, **Edit**, **Delete**
-   [ ] **UAT-PL-004** — Pagination shows "Showing 1 to 10 of N", page numbers and → (next) are clickable, clicking a page loads correct products and highlights that page number
-   [ ] **UAT-PL-005** — Header checkbox selects all rows; unchecking it deselects all rows
-   [ ] **UAT-PL-006** — "Add Product" button (top right) navigates to `/storesuite-dashboard/add-new-product/`

---

## Module 2: Search

-   [ ] **UAT-SR-001** — Search by exact product name returns only that product
-   [ ] **UAT-SR-002** — Search by partial name (e.g. `E2E Variable`) returns all matching products
-   [ ] **UAT-SR-003** — Searching a non-existent term (e.g. `zzznoresult999`) shows an empty/no-results state
-   [ ] **UAT-SR-004** — Clearing the search input restores the full product list

---

## Module 3: Filter

-   [ ] **UAT-FL-001** — Clicking "Filter" button opens the filter panel with heading "Filters", four dropdowns (Category, Type, Stock Status, Brand), and a close (X) button; X closes the panel
-   [ ] **UAT-FL-002** — Filter by Category (e.g. "Clothing") → click "Filter Products" → table shows only products in that category; test with: Accessories (6), Clothing (16), Electronics (3), Hoodies (3), Music (2), Tshirts (5), Uncategorized (13)
-   [ ] **UAT-FL-003** — Filter by Product Type (e.g. "Simple product") → table shows only Simple-type products; options: Simple, Grouped, External/Affiliate, Variable
-   [ ] **UAT-FL-004** — Filter by Stock Status (e.g. "Out of stock") → all rows show red "Out of Stock" badge; options: In stock, Out of stock, On backorder
-   [ ] **UAT-FL-005** — Filter by Brand "Apple Inc." → only Apple Inc. branded products shown (expected: 1 result)
-   [ ] **UAT-FL-006** — Combining multiple filters (e.g. Category = Electronics + Type = Variable) returns intersected results only
-   [ ] **UAT-FL-007** — Clicking "Reset" link resets all dropdowns to defaults and restores full product list
-   [ ] **UAT-FL-008** — Filter + Search combined returns correctly intersected results

---

## Module 4: Add New Product

### Page & Layout

-   [ ] **UAT-AP-001** — Page loads with heading "Add New Product", two-column layout (main form left, "General Informations" sidebar right), all fields empty

### Basic Information

-   [ ] **UAT-AP-002** — Leaving Product Title empty and clicking "Add Product" shows inline validation error; product not created
-   [ ] **UAT-AP-003** — Typing in Product Title and tabbing out auto-populates the Product Slug as lowercase hyphenated version
-   [ ] **UAT-AP-004** — Rich text editor (Description) toolbar works: Bold (⌘B), Italic (⌘I), Underline (⌘U), Strikethrough, Bulleted list, Numbered list, Align Left/Center/Right, Undo, Redo, Insert Link, Fullscreen

### Media

-   [ ] **UAT-AP-005** — "Upload Image" opens media library/file picker; selected image previews on form
-   [ ] **UAT-AP-006** — "Upload Gallery Images" allows multiple images; all thumbnails appear in gallery section

### Category & Taxonomy

-   [ ] **UAT-AP-007** — Category field supports multi-select; selected categories appear as removable tags (X chip)
-   [ ] **UAT-AP-025** — Tags field supports multi-select with autocomplete suggestions

### Pricing

-   [ ] **UAT-AP-008** — Regular Price and Sale Price accept numeric values; entering Sale Price > Regular Price shows a validation warning
-   [ ] **UAT-AP-009** — Clicking "Schedule" link shows Sale Price Date From/To date pickers; valid date range accepted

### Inventory

-   [ ] **UAT-AP-010** — Toggling "Enable product stock management" ON reveals: Quantity, Low Stock Threshold, Allow Backorders? fields; toggling OFF hides them
-   [ ] **UAT-AP-011** — Quantity = 0 is accepted; Quantity = negative number shows validation error
-   [ ] **UAT-AP-012** — "Allow Backorders?" shows options: Do not allow, Allow but notify customer, Allow
-   [ ] **UAT-AP-013** — "Stock Status" shows options: In stock, Out of stock, On backorder
-   [ ] **UAT-AP-014** — "Limit Purchases to 1 Item Per Order?" toggle activates and saves

### Shipping

-   [ ] **UAT-AP-015** — Weight (decimal) and Dimensions (Length, Width, Height) accept valid numbers; non-numeric input is rejected
-   [ ] **UAT-AP-016** — Shipping Class dropdown shows "No shipping class" and any configured classes

### Linked Products

-   [ ] **UAT-AP-017** — Upsells and Cross-sells fields show autocomplete suggestions when typing a product name; selected product appears as a tag

### Others

-   [ ] **UAT-AP-018** — Catalog Visibility dropdown options: Shop and search results, Shop only, Search results only, Hidden
-   [ ] **UAT-AP-019** — "Mark this product as featured" checkbox saves correctly

### Product Creation Flows

-   [ ] **UAT-AP-020** — Create Simple product with valid data → "Add Product" → success notice shown → product appears in list with "Simple" type and correct STATUS badge
-   [ ] **UAT-AP-021** — Change Type to "Variable" → save → product appears in list with "Variable" type
-   [ ] **UAT-AP-022** — Set Status = "Draft" → save → list shows grey "Draft" badge (not "Online")
-   [ ] **UAT-AP-023** — Set Status = "Pending Review" → save → product saved with Pending Review status
-   [ ] **UAT-AP-024** — Brand dropdown shows: Select brand, Apple Inc., Asus, HP — all selectable
-   [ ] **UAT-AP-026** — "Enable Reviews?" set to "Yes" saves correctly
-   [ ] **UAT-AP-027** — Clicking "Back" link returns to `/storesuite-dashboard/products/` without creating a product

---

## Module 5: Edit Product

### Page Load

-   [ ] **UAT-EP-001** — Edit page (`/edit-product/{id}/`) loads with "Edit Product" heading and all fields pre-filled from saved product data (title, slug, permalink, category, type, price, stock, reviews, etc.)
-   [ ] **UAT-EP-012** — Right sidebar button reads "**Update Product**" (not "Add Product")

### Update Flows

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

### Navigation

-   [ ] **UAT-EP-013** — "Back" link returns to `/storesuite-dashboard/products/` without saving unsaved changes
-   [ ] **UAT-EP-014** — Three-dot "Edit" from product list opens correct product edit page with its data pre-filled
-   [ ] **UAT-EP-015** — Three-dot "View" from product list opens the product's public frontend URL in browser
-   [ ] **UAT-EP-016** — Three-dot "Delete" from product list shows confirmation prompt; confirming removes product from list; cancelling keeps product

---

## Known UI Bugs (Fix Before Release)

| ID    | Location         | Issue                                                    | Severity |
| ----- | ---------------- | -------------------------------------------------------- | -------- |
| B-001 | Add/Edit Product | Label reads "**Prduct** Short Description" (missing 'o') | Low      |
| B-002 | Add/Edit Product | Label reads "**Prouduct** Image" (extra 'u')             | Low      |
| B-003 | Add/Edit Product | Label reads "**Prouduct** Gallery Images" (extra 'u')    | Low      |

---

## Exit Criteria

The following must **all pass** before QA sign-off:

| ID         | Scenario                              | Priority    |
| ---------- | ------------------------------------- | ----------- |
| UAT-PL-001 | List loads with correct columns       | 🔴 Critical |
| UAT-PL-003 | Row actions (View, Edit, Delete) work | 🔴 Critical |
| UAT-PL-004 | Pagination works                      | 🟠 High     |
| UAT-SR-001 | Search by name works                  | 🔴 Critical |
| UAT-SR-003 | No results state shown                | 🟡 Medium   |
| UAT-FL-002 | Filter by Category works              | 🔴 Critical |
| UAT-FL-003 | Filter by Type works                  | 🟠 High     |
| UAT-FL-007 | Reset filters restores full list      | 🟠 High     |
| UAT-AP-002 | Required field validation blocks save | 🔴 Critical |
| UAT-AP-020 | Simple product created successfully   | 🔴 Critical |
| UAT-AP-022 | Draft status saved correctly          | 🟠 High     |
| UAT-EP-001 | Edit page loads with pre-filled data  | 🔴 Critical |
| UAT-EP-002 | Product title update saved            | 🔴 Critical |
| UAT-EP-007 | Status change reflected in list       | 🟠 High     |
| UAT-EP-016 | Delete with confirmation works        | 🔴 Critical |
