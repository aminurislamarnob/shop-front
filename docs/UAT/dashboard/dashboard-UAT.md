## Overview

User Acceptance Testing checklist for the **Dashboard** module in the StoreSuite vendor dashboard.

**Page covered:** `/storesuite-dashboard/`

**Role under test:** Logged-in vendor/seller
**Prepared:** 2026-04-10

> Tick each box as **Pass ✅**, or comment with **Fail ❌** + repro steps.

---

## Module 1: Page Load & Layout

-   [ ] **UAT-DSH-001** — Page loads with heading "Dashboard (Apr 1, 2026 – Apr 30, 2026)" showing the current month range by default; sidebar "Dashboard" item is active/highlighted
-   [ ] **UAT-DSH-002** — Page has no breadcrumb (by design for the root dashboard page)
-   [ ] **UAT-DSH-003** — Layout renders correctly: fixed left sidebar + main content area with stat cards grid + four data tables below
-   [ ] **UAT-DSH-004** — "StoreSuite" H1 branding is visible at the top of the sidebar
-   [ ] **UAT-DSH-005** — Top-right header shows "Visit Home" link and user avatar icon
-   [ ] **UAT-DSH-006** — Hamburger/collapse icon is visible at the top-left of the main content; clicking it collapses/expands the sidebar

---

## Module 2: Date Range Selector

### Preset Options

-   [ ] **UAT-DT-001** — Date range selector is visible at the top-right of the main content area, showing the current selected range in "YYYY-MM-DD – YYYY-MM-DD" format
-   [ ] **UAT-DT-002** — Clicking the date range field opens a dropdown with exactly these preset options: **Today**, **Yesterday**, **Last 7 Days**, **Last 30 Days**, **This Month**, **Last Month**, **Custom Range**
-   [ ] **UAT-DT-003** — "This Month" is highlighted/selected by default on first load
-   [ ] **UAT-DT-004** — Selecting **Today** → clicking Apply → all stat cards and tables update to show today's data only; page heading updates to today's date
-   [ ] **UAT-DT-005** — Selecting **Yesterday** → Apply → data updates to yesterday's figures
-   [ ] **UAT-DT-006** — Selecting **Last 7 Days** → Apply → data reflects the past 7 days
-   [ ] **UAT-DT-007** — Selecting **Last 30 Days** → Apply → data reflects the past 30 days
-   [ ] **UAT-DT-008** — Selecting **This Month** → Apply → data reflects the current calendar month
-   [ ] **UAT-DT-009** — Selecting **Last Month** → Apply → data reflects the previous calendar month
-   [ ] **UAT-DT-010** — Data does NOT update until the **Apply** button is clicked; selecting a preset without clicking Apply leaves the dashboard unchanged

### Custom Range Calendar

-   [ ] **UAT-DT-011** — Selecting **Custom Range** opens a dual-month calendar picker showing two consecutive months side by side
-   [ ] **UAT-DT-012** — Left/right arrows navigate between months in the calendar picker
-   [ ] **UAT-DT-013** — Clicking a start date then an end date highlights the selected range on the calendar
-   [ ] **UAT-DT-014** — Selected range is shown in "YYYY-MM-DD – YYYY-MM-DD" format at the bottom of the calendar
-   [ ] **UAT-DT-015** — Clicking **Apply** (blue button in calendar) closes the picker and applies the custom range; stat cards and tables update; page heading updates to the selected range
-   [ ] **UAT-DT-016** — Clicking **Cancel** (grey button in calendar) closes the picker without applying any change; previously selected range is retained
-   [ ] **UAT-DT-017** — Selecting an end date before the start date is handled gracefully (blocked or auto-corrected; document actual behaviour)
-   [ ] **UAT-DT-018** — Selecting a future date range shows correct empty state (0 values) for all metrics

---

## Module 3: Store Performance — Stat Cards

**Precondition:** Use a date range known to have actual order/sales data (e.g. Last 30 Days) to verify non-zero values display correctly.

### Layout

-   [ ] **UAT-STAT-001** — "Store performance" section heading (H2) is visible above the stat card grid
-   [ ] **UAT-STAT-002** — Stat cards render in a 3-column grid layout; all 17 cards are visible and properly aligned
-   [ ] **UAT-STAT-003** — Each card shows: a metric label, a value, and a percentage change indicator

### Individual Stat Cards (verify label, value format, and % change all display)

-   [ ] **UAT-STAT-004** — **Total sales** — shows total revenue in correct currency format (not "0.00b" — verify currency symbol renders correctly)
-   [ ] **UAT-STAT-005** — **Gross sales** — shows gross revenue before deductions
-   [ ] **UAT-STAT-006** — **Net sales** — shows revenue after refunds/discounts
-   [ ] **UAT-STAT-007** — **Orders** — shows integer count of orders in the period
-   [ ] **UAT-STAT-008** — **Average order value** — shows average revenue per order in currency format
-   [ ] **UAT-STAT-009** — **Products sold** — shows integer count of product units sold
-   [ ] **UAT-STAT-010** — **Variations sold** — shows integer count of variation units sold
-   [ ] **UAT-STAT-011** — **Returns** — shows total refunded amount in currency format
-   [ ] **UAT-STAT-012** — **Discounted orders** — shows count of orders where a coupon was applied
-   [ ] **UAT-STAT-013** — **Net discount amount** — shows total discount value in currency format
-   [ ] **UAT-STAT-014** — **Total tax** — shows total tax collected in currency format
-   [ ] **UAT-STAT-015** — **Order tax** — shows tax on order subtotal
-   [ ] **UAT-STAT-016** — **Shipping tax** — shows tax applied to shipping
-   [ ] **UAT-STAT-017** — **Shipping** — shows total shipping revenue collected
-   [ ] **UAT-STAT-018** — **Downloads** — shows count of digital product downloads

### Percentage Change Indicator

-   [ ] **UAT-STAT-019** — Percentage change on each card compares the selected period against the equivalent previous period (e.g. This Month vs Last Month)
-   [ ] **UAT-STAT-020** — Positive change shows in green; negative change shows in red; 0% shows in neutral colour
-   [ ] **UAT-STAT-021** — Switching the date range updates all percentage change indicators accordingly

### Currency Format

-   [ ] **UAT-STAT-022** — Currency values display with the correct symbol and decimal formatting as configured in WooCommerce settings (current observation shows "0.00b" — verify this is not a bug for non-zero values)

### Empty State

-   [ ] **UAT-STAT-023** — When no data exists for the selected period, all cards show "0" or "0.00" (in correct currency) and "0%" change — NOT blank or broken layout

---

## Module 4: Top Products Table

-   [ ] **UAT-TP-001** — "Top products - Items sold" section heading (H2) is visible
-   [ ] **UAT-TP-002** — Table has exactly 3 columns in order: **PRODUCT**, **ITEMS SOLD**, **NET SALES**
-   [ ] **UAT-TP-003** — When data exists for the selected period, products are listed in descending order by items sold
-   [ ] **UAT-TP-004** — PRODUCT column shows the product name
-   [ ] **UAT-TP-005** — ITEMS SOLD column shows an integer count
-   [ ] **UAT-TP-006** — NET SALES column shows a currency value in correct format
-   [ ] **UAT-TP-007** — When no products were sold in the period, the table shows "No products found for this period." message
-   [ ] **UAT-TP-008** — Changing the date range updates the Top Products table accordingly

---

## Module 5: Top Categories Table

-   [ ] **UAT-TC-001** — "Top categories - Items sold" section heading (H2) is visible
-   [ ] **UAT-TC-002** — Table has exactly 3 columns in order: **CATEGORY**, **ITEMS SOLD**, **NET SALES**
-   [ ] **UAT-TC-003** — When data exists, categories are listed in descending order by items sold
-   [ ] **UAT-TC-004** — CATEGORY column shows the category name
-   [ ] **UAT-TC-005** — ITEMS SOLD and NET SALES columns show correct values
-   [ ] **UAT-TC-006** — When no category data exists for the period, table shows "No categories found for this period." message
-   [ ] **UAT-TC-007** — Changing date range updates the Top Categories table accordingly

---

## Module 6: Top Customers Table

-   [ ] **UAT-TCU-001** — "Top customers - Total spend" section heading (H2) is visible
-   [ ] **UAT-TCU-002** — Table has exactly 3 columns in order: **CUSTOMER NAME**, **ORDERS**, **TOTAL SPEND**
-   [ ] **UAT-TCU-003** — When data exists, customers are listed in descending order by total spend
-   [ ] **UAT-TCU-004** — CUSTOMER NAME shows the customer's display name (or "Guest" for guest checkouts)
-   [ ] **UAT-TCU-005** — ORDERS column shows integer count of orders placed by that customer
-   [ ] **UAT-TCU-006** — TOTAL SPEND shows cumulative spend in correct currency format
-   [ ] **UAT-TCU-007** — When no customer data exists for the period, table shows "No customers found for this period." message
-   [ ] **UAT-TCU-008** — Changing date range updates the Top Customers table accordingly

---

## Module 7: Top Coupons Table

-   [ ] **UAT-TCC-001** — "Top coupons - Number of orders" section heading (H2) is visible
-   [ ] **UAT-TCC-002** — Table has exactly 3 columns in order: **COUPON CODE**, **ORDERS**, **AMOUNT DISCOUNTED**
-   [ ] **UAT-TCC-003** — When data exists, coupons are listed in descending order by number of orders
-   [ ] **UAT-TCC-004** — COUPON CODE column shows the coupon code string
-   [ ] **UAT-TCC-005** — ORDERS column shows integer count of orders where the coupon was used
-   [ ] **UAT-TCC-006** — AMOUNT DISCOUNTED shows total discount value in correct currency format
-   [ ] **UAT-TCC-007** — When no coupon data exists for the period, table shows "No coupons found for this period." message
-   [ ] **UAT-TCC-008** — Changing date range updates the Top Coupons table accordingly

---

## Module 8: Navigation

-   [ ] **UAT-NAV-001** — All 11 sidebar menu items are visible and clickable: Dashboard, Products, Orders, Categories, Brands, Tags, Coupons, Account, Visit Home, WP Dashboard, Logout
-   [ ] **UAT-NAV-002** — Clicking **Products** navigates to `/storesuite-dashboard/products/`
-   [ ] **UAT-NAV-003** — Clicking **Orders** navigates to `/storesuite-dashboard/orders/`
-   [ ] **UAT-NAV-004** — Clicking **Categories** navigates to `/storesuite-dashboard/categories/`
-   [ ] **UAT-NAV-005** — Clicking **Brands** navigates to `/storesuite-dashboard/brands/`
-   [ ] **UAT-NAV-006** — Clicking **Tags** navigates to `/storesuite-dashboard/tags/`
-   [ ] **UAT-NAV-007** — Clicking **Coupons** navigates to `/storesuite-dashboard/coupons/`
-   [ ] **UAT-NAV-008** — Clicking **Account** navigates to `/storesuite-dashboard/edit-account-details/` or account section
-   [ ] **UAT-NAV-009** — Clicking **Visit Home** (sidebar) opens the store homepage (`http://woocommerce.test/`)
-   [ ] **UAT-NAV-010** — Clicking **WP Dashboard** opens the WordPress admin (`/wp-admin/`)
-   [ ] **UAT-NAV-011** — Clicking **Logout** logs the user out and redirects to the login page
-   [ ] **UAT-NAV-012** — "Visit Home" link in top-right header also opens the store homepage
-   [ ] **UAT-NAV-013** — Navigating back to Dashboard from any module restores the dashboard with the previously selected date range

---

## Module 9: Data Accuracy (Cross-check)

-   [ ] **UAT-DATA-001** — "Orders" stat card value matches the total order count shown in the Orders list for the same date range
-   [ ] **UAT-DATA-002** — "Total sales" value matches the sum of completed order totals for the same period
-   [ ] **UAT-DATA-003** — Top Products table order matches what WooCommerce admin reports show for the same period
-   [ ] **UAT-DATA-004** — Top Customers table customer names and spend values are accurate vs order records
-   [ ] **UAT-DATA-005** — Stat card percentage change is calculated as: `((current - previous) / previous) × 100`; verify with at least one card where both periods have data

---

## Known UI Issues (Observed — Verify or Fix)

| ID    | Observation                                                                                                                    | Severity  |
| ----- | ------------------------------------------------------------------------------------------------------------------------------ | --------- |
| B-001 | Currency values display as "0.00b" — the "b" suffix appears to be a currency formatting bug (should show symbol like ₹ or $)   | 🔴 High   |
| B-002 | No charts or graphs on the dashboard — only tables and stat cards; if a sales trend chart is expected, it may be missing       | 🟡 Medium |
| B-003 | No "View all" links on any of the four tables — users cannot navigate from a table row to the related module                   | 🟡 Medium |
| B-004 | No notifications or announcement panel — if system alerts or low-stock warnings are expected on the dashboard, they are absent | 🟡 Medium |

---

## Exit Criteria

The following must **all pass** before QA sign-off:

| ID           | Scenario                                                    | Priority    |
| ------------ | ----------------------------------------------------------- | ----------- |
| UAT-DSH-001  | Dashboard loads with correct heading and default date range | 🔴 Critical |
| UAT-DT-002   | All 7 date range preset options present                     | 🔴 Critical |
| UAT-DT-004   | "Today" preset filters data correctly                       | 🔴 Critical |
| UAT-DT-010   | Data does not update without clicking Apply                 | 🟠 High     |
| UAT-DT-015   | Custom range calendar applies correctly                     | 🔴 Critical |
| UAT-STAT-002 | All 17 stat cards render without layout breaks              | 🔴 Critical |
| UAT-STAT-022 | Currency format displays correctly (not "0.00b")            | 🔴 Critical |
| UAT-STAT-023 | Empty state shows 0 values, not blank/broken                | 🟠 High     |
| UAT-STAT-020 | Positive/negative % change shown in correct colours         | 🟠 High     |
| UAT-TP-003   | Top Products sorted by items sold descending                | 🔴 Critical |
| UAT-TP-007   | Empty state message shown correctly                         | 🟠 High     |
| UAT-TCU-003  | Top Customers sorted by total spend descending              | 🔴 Critical |
| UAT-TCC-003  | Top Coupons sorted by number of orders descending           | 🟠 High     |
| UAT-NAV-001  | All sidebar nav items work                                  | 🔴 Critical |
| UAT-DATA-001 | Orders count matches Orders list for same period            | 🔴 Critical |
| UAT-DATA-002 | Total sales matches order records                           | 🔴 Critical |
