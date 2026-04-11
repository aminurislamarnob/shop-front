## Overview

User Acceptance Testing checklist for the **Coupons** module in the StoreSuite vendor dashboard.

**Pages covered:**

-   `/storesuite-dashboard/coupons/` — Coupon list, search
-   `/storesuite-dashboard/add-new-coupon/` — Create coupon
-   `/storesuite-dashboard/edit-coupon/{id}/` — Edit coupon (tested with #146)

**Role under test:** Logged-in vendor/seller
**Prepared:** 2026-04-10

> Tick each box as **Pass ✅**, or comment with **Fail ❌** + repro steps.

---

## Module 1: Coupon List (`/coupons/`)

### Page & Table

-   [ ] **UAT-CPN-001** — Page loads with heading "Coupons", breadcrumb shows Dashboard > Coupons > Coupons, sidebar "Coupons" item is active
-   [ ] **UAT-CPN-002** — Table renders with all 9 columns in order: Checkbox, CODE, TYPE, AMOUNT, DESCRIPTION, USAGE / LIMIT, EXPIRY DATE, STATUS, ACTIONS
-   [ ] **UAT-CPN-003** — CODE column shows the coupon code string (e.g. `flat_100`)
-   [ ] **UAT-CPN-004** — TYPE column shows discount type label (e.g. "Fixed cart discount", "Percentage discount", "Fixed product discount")
-   [ ] **UAT-CPN-005** — AMOUNT column shows the coupon value in correct currency format
-   [ ] **UAT-CPN-006** — DESCRIPTION column shows description text or "–" when no description is set
-   [ ] **UAT-CPN-007** — USAGE / LIMIT column shows format `used / limit` (e.g. "1 / ∞" for unlimited; "2 / 5" for limited)
-   [ ] **UAT-CPN-008** — EXPIRY DATE column shows the date in readable format or "–" when no expiry is set
-   [ ] **UAT-CPN-009** — STATUS column shows a colour-coded badge: green "Online" for Published coupons; verify badge colour/text for Draft and Pending Review statuses
-   [ ] **UAT-CPN-010** — ACTIONS column three-dot menu (⋯) on each row shows exactly: **Edit**, **Delete**
-   [ ] **UAT-CPN-011** — Clicking **Edit** navigates to `/storesuite-dashboard/edit-coupon/{id}/` with correct coupon data pre-filled
-   [ ] **UAT-CPN-012** — Clicking **Delete** shows a confirmation prompt; confirming removes the coupon from the list; cancelling keeps it

### Pagination

-   [ ] **UAT-CPN-013** — When more than one page of coupons exists, pagination shows "Showing X to Y of Z", page number buttons and → (next) are clickable and load the correct coupons
-   [ ] **UAT-CPN-014** — When all coupons fit on one page, no pagination controls are shown

### Add Coupon Button

-   [ ] **UAT-CPN-015** — **"Add Coupon"** button (blue, "+" icon, top right) navigates to `/storesuite-dashboard/add-new-coupon/`

### Empty State

-   [ ] **UAT-CPN-016** — When no coupons exist, the table shows a clear empty/no-data state message instead of an empty table

---

## Module 2: Search (`/coupons/`)

-   [ ] **UAT-SR-001** — Search input (placeholder: "Search Coupon") is visible at top left of the table
-   [ ] **UAT-SR-002** — Searching by exact coupon code (e.g. `flat_100`) returns only that coupon
-   [ ] **UAT-SR-003** — Searching by partial coupon code (e.g. `flat`) returns all coupons whose code contains that string
-   [ ] **UAT-SR-004** — Searching a non-existent term shows an empty/no-results state
-   [ ] **UAT-SR-005** — Clearing the search input restores the full coupon list

---

## Module 3: Add New Coupon (`/add-new-coupon/`)

### Page & Layout

-   [ ] **UAT-ADD-001** — Page loads with heading "Add New Coupon", breadcrumb shows Dashboard > Coupons > Add New Coupon, two-column layout: main form (left), Status & Visibility + Usage limits sidebar (right); all fields empty/default

---

### Section: General

-   [ ] **UAT-ADD-002** — **Coupon Code \*** (required) text field with placeholder "Enter coupon code"; leaving it blank and clicking Create Coupon shows inline validation error; coupon not created
-   [ ] **UAT-ADD-003** — **"Generate coupon code"** blue link below the Coupon Code field auto-populates the field with a randomly generated code when clicked
-   [ ] **UAT-ADD-004** — Generated coupon code is unique and follows a valid format (alphanumeric, no spaces)
-   [ ] **UAT-ADD-005** — **Discount Type \*** (required) dropdown defaults to "Fixed cart discount"; all three options are selectable:
    -   `Percentage discount`
    -   `Fixed cart discount`
    -   `Fixed product discount`
-   [ ] **UAT-ADD-006** — **Coupon Amount \*** (required) number input with placeholder "0.00" and help text "Value of the coupon"; leaving it blank or entering 0 and submitting shows validation error
-   [ ] **UAT-ADD-007** — Coupon Amount accepts decimals (e.g. 12.50); rejects non-numeric input
-   [ ] **UAT-ADD-008** — When Discount Type = "Percentage discount", Coupon Amount of 101 or more shows a validation error (cannot exceed 100%)
-   [ ] **UAT-ADD-009** — **Description** textarea (optional) with placeholder "Optional description"; accepts multi-line text; value saved and shown in DESCRIPTION column of list
-   [ ] **UAT-ADD-010** — **Expiry Date** text field with placeholder "YYYY-MM-DD" and help text "The coupon will expire at 00:00:00 of this date."; accepts valid date (e.g. 2026-12-31); rejects invalid format (e.g. 31/12/2026)
-   [ ] **UAT-ADD-011** — Setting an expiry date in the past is either blocked with a validation warning or accepted (document behaviour)
-   [ ] **UAT-ADD-012** — **Allow free shipping** toggle (default: OFF); toggling ON saves the setting with the coupon; toggling OFF removes it
-   [ ] **UAT-ADD-013** — **Individual use only** toggle (default: OFF); toggling ON saves; means coupon cannot be used in conjunction with other coupons

---

### Section: Usage Restriction

-   [ ] **UAT-ADD-014** — **Exclude sale items** toggle (default: OFF) with help text "Check this box if the coupon should not apply to items on sale."; toggling ON saves correctly
-   [ ] **UAT-ADD-015** — **Minimum Spend** number input (placeholder: "No minimum") with help text "Minimum spend (subtotal) required to use the coupon."; entering a value (e.g. 50) saves and is enforced at checkout
-   [ ] **UAT-ADD-016** — **Maximum Spend** number input (placeholder: "No maximum") with help text "Maximum spend (subtotal) allowed when using the coupon."; entering a value saves correctly
-   [ ] **UAT-ADD-017** — Setting Minimum Spend > Maximum Spend shows a validation warning or is blocked (document behaviour)
-   [ ] **UAT-ADD-018** — **Products** autocomplete field (placeholder: "Search for a product…"); typing a product name shows matching suggestions; selecting limits the coupon to that product only
-   [ ] **UAT-ADD-019** — Multiple products can be added to the Products field; each appears as a removable tag/chip
-   [ ] **UAT-ADD-020** — **Exclude Products** autocomplete field (placeholder: "Search for a product…"); works same as Products field but excludes selected products from the coupon
-   [ ] **UAT-ADD-021** — **Product Categories** autocomplete field (placeholder: "Any category"); typing shows matching categories; selection limits coupon to that category
-   [ ] **UAT-ADD-022** — **Exclude Categories** autocomplete field (placeholder: "No categories"); typing shows matching categories; selection excludes that category from the coupon
-   [ ] **UAT-ADD-023** — **Allowed Emails** text field (placeholder: "No restrictions") with help text "Separate email addresses with commas. You can use _ as a wildcard."; entering multiple emails comma-separated saves correctly; wildcard `_` accepted

---

### Section: Status & Visibility (Sidebar)

-   [ ] **UAT-ADD-024** — **Status** dropdown defaults to "Published"; all three options selectable:
    -   `Published` → shows green "Online" badge in list
    -   `Pending review` → shows correct badge in list
    -   `Draft` → shows correct badge in list
-   [ ] **UAT-ADD-025** — **Visibility** dropdown defaults to "Public"; options: Public, Private — both selectable and saved

---

### Section: Usage Limits (Sidebar)

-   [ ] **UAT-ADD-026** — **Usage Limit Per Coupon** number input (placeholder: "Unlimited usage"); entering a value (e.g. 10) limits total uses; leaving blank means unlimited (shows "∞" in USAGE / LIMIT column)
-   [ ] **UAT-ADD-027** — **Usage Limit Per User** number input (placeholder: "Unlimited usage"); entering a value (e.g. 1) limits uses per individual user; leaving blank means unlimited
-   [ ] **UAT-ADD-028** — **Limit Usage to X Items** number input (placeholder: "Apply to all qualifying items"); entering a value limits how many items in a single order the coupon applies to
-   [ ] **UAT-ADD-029** — Entering a negative number in any usage limit field shows a validation error

---

### Create Coupon Flow

-   [ ] **UAT-ADD-030** — **"Create Coupon"** is a full-width blue primary button at bottom of right sidebar
-   [ ] **UAT-ADD-031** — Submitting with all required fields filled (Code, Type, Amount) shows a success notice; coupon appears in the list with correct CODE, TYPE, AMOUNT, STATUS
-   [ ] **UAT-ADD-032** — USAGE / LIMIT column shows `0 / ∞` for a newly created coupon with no usage limit set
-   [ ] **UAT-ADD-033** — EXPIRY DATE column shows "–" for a newly created coupon with no expiry set
-   [ ] **UAT-ADD-034** — Creating a coupon with a duplicate code (already exists) shows a validation error; no duplicate created
-   [ ] **UAT-ADD-035** — **"Back"** link returns to `/storesuite-dashboard/coupons/` without creating a coupon

---

## Module 4: Edit Coupon (`/edit-coupon/{id}/`)

### Page & Pre-fill

-   [ ] **UAT-EDT-001** — Edit page loads with heading "Edit Coupon", breadcrumb shows Dashboard > Coupons > Edit Coupon
-   [ ] **UAT-EDT-002** — All fields are pre-filled with the coupon's saved values: Code, Discount Type, Amount, Description, Expiry Date, toggles, restrictions, status, visibility, usage limits
-   [ ] **UAT-EDT-003** — Primary button reads **"Update Coupon"** (not "Create Coupon")

### Update Flows

-   [ ] **UAT-EDT-004** — Updating Coupon Code to a new unique code → save → CODE column in list reflects new code
-   [ ] **UAT-EDT-005** — Updating Coupon Code to an already-existing code shows a validation error; update blocked
-   [ ] **UAT-EDT-006** — Changing Discount Type (e.g. Fixed cart → Percentage) → save → TYPE column in list reflects new type
-   [ ] **UAT-EDT-007** — Updating Coupon Amount → save → AMOUNT column in list reflects new value
-   [ ] **UAT-EDT-008** — Adding a Description → save → DESCRIPTION column in list reflects the text
-   [ ] **UAT-EDT-009** — Setting an Expiry Date → save → EXPIRY DATE column in list shows the date
-   [ ] **UAT-EDT-010** — Removing an Expiry Date (clear the field) → save → EXPIRY DATE column reverts to "–"
-   [ ] **UAT-EDT-011** — Toggling Allow free shipping ON → save → reload edit page shows toggle is ON
-   [ ] **UAT-EDT-012** — Toggling Individual use only ON → save → reload edit page shows toggle is ON
-   [ ] **UAT-EDT-013** — Setting Minimum Spend → save → value persists on reload
-   [ ] **UAT-EDT-014** — Adding products to Products field → save → field shows selected products on reload
-   [ ] **UAT-EDT-015** — Changing Status from Published → Draft → save → STATUS badge in list updates accordingly
-   [ ] **UAT-EDT-016** — Changing Visibility from Public → Private → save → persists on reload
-   [ ] **UAT-EDT-017** — Setting Usage Limit Per Coupon → save → USAGE / LIMIT column in list shows `used / limit` (e.g. "0 / 5")
-   [ ] **UAT-EDT-018** — No-op update (open edit, change nothing, click Update Coupon) → success notice shown; all values unchanged
-   [ ] **UAT-EDT-019** — **"Back"** link returns to `/storesuite-dashboard/coupons/` without saving unsaved changes

---

## Module 5: Coupon Behaviour Validation (End-to-End)

-   [ ] **UAT-E2E-001** — A coupon with Discount Type "Percentage discount" and Amount 20 applies a 20% discount at checkout
-   [ ] **UAT-E2E-002** — A coupon with Discount Type "Fixed cart discount" and Amount 100 deducts a flat amount from cart total
-   [ ] **UAT-E2E-003** — A coupon with Discount Type "Fixed product discount" applies discount to specific product price
-   [ ] **UAT-E2E-004** — A coupon with Allow free shipping ON removes shipping cost at checkout
-   [ ] **UAT-E2E-005** — A coupon with Individual use only ON cannot be combined with another coupon at checkout
-   [ ] **UAT-E2E-006** — A coupon with Exclude sale items ON does not apply to products currently on sale
-   [ ] **UAT-E2E-007** — A coupon with Minimum Spend = 50 is rejected at checkout when cart subtotal < 50
-   [ ] **UAT-E2E-008** — A coupon with Maximum Spend = 200 is rejected at checkout when cart subtotal > 200
-   [ ] **UAT-E2E-009** — A coupon with Products restriction only applies to those specific products; other cart items are not discounted
-   [ ] **UAT-E2E-010** — A coupon with Exclude Products set does not discount those products
-   [ ] **UAT-E2E-011** — A coupon with Product Categories restriction only applies to products in those categories
-   [ ] **UAT-E2E-012** — A coupon with Allowed Emails set can only be used by customers with those email addresses; other customers see an error
-   [ ] **UAT-E2E-013** — A coupon with Expiry Date in the past is rejected at checkout with an appropriate error message
-   [ ] **UAT-E2E-014** — A coupon with Usage Limit Per Coupon = 1 works on the first use; on the second use it is rejected with an error
-   [ ] **UAT-E2E-015** — A coupon with Usage Limit Per User = 1 can only be used once per customer account; a second attempt is rejected
-   [ ] **UAT-E2E-016** — The USAGE / LIMIT counter in the list increments correctly after each successful coupon use at checkout
-   [ ] **UAT-E2E-017** — A Draft coupon cannot be applied at checkout
-   [ ] **UAT-E2E-018** — A Private coupon can be applied by direct code entry but is not publicly advertised

---

## Known UI Issues (Observed — Verify or Fix)

| ID    | Location                     | Issue                                                                                                                        | Severity |
| ----- | ---------------------------- | ---------------------------------------------------------------------------------------------------------------------------- | -------- |
| B-001 | Coupons list — AMOUNT column | Displays "100.00b" instead of proper currency format (e.g. "₹100.00" or "$100.00") — likely a currency symbol/formatting bug | Medium   |

---

## Exit Criteria

The following must **all pass** before QA sign-off:

| ID          | Scenario                                | Priority    |
| ----------- | --------------------------------------- | ----------- |
| UAT-CPN-001 | Coupon list loads with correct columns  | 🔴 Critical |
| UAT-CPN-010 | Row actions (Edit, Delete) work         | 🔴 Critical |
| UAT-CPN-012 | Delete with confirmation works          | 🔴 Critical |
| UAT-SR-002  | Search by coupon code works             | 🔴 Critical |
| UAT-SR-004  | No-results state shown                  | 🟡 Medium   |
| UAT-ADD-002 | Required field validation blocks save   | 🔴 Critical |
| UAT-ADD-003 | Generate coupon code link works         | 🟠 High     |
| UAT-ADD-005 | All discount types selectable           | 🔴 Critical |
| UAT-ADD-008 | Percentage > 100% blocked               | 🟠 High     |
| UAT-ADD-010 | Expiry date format validated            | 🟠 High     |
| UAT-ADD-031 | New coupon created and appears in list  | 🔴 Critical |
| UAT-ADD-034 | Duplicate coupon code blocked           | 🔴 Critical |
| UAT-EDT-001 | Edit page loads with pre-filled data    | 🔴 Critical |
| UAT-EDT-015 | Status change reflected in list         | 🟠 High     |
| UAT-EDT-018 | Update Coupon saves changes             | 🔴 Critical |
| UAT-E2E-001 | Percentage discount applies at checkout | 🔴 Critical |
| UAT-E2E-002 | Fixed cart discount applies at checkout | 🔴 Critical |
| UAT-E2E-007 | Minimum spend enforced at checkout      | 🔴 Critical |
| UAT-E2E-013 | Expired coupon rejected at checkout     | 🔴 Critical |
| UAT-E2E-014 | Usage limit per coupon enforced         | 🔴 Critical |
| UAT-E2E-016 | USAGE counter increments after use      | 🟠 High     |
