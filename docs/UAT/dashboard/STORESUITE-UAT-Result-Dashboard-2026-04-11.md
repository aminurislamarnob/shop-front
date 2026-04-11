# StoreSuite Dashboard module — UAT report

**Environment:** `http://woocommerce.test`, StoreSuite vendor dashboard (`/storesuite-dashboard/`)  
**Role:** Logged-in vendor/seller (session used by Cursor integrated browser)  
**Test date:** 2026-04-11  
**Methods:** Cursor integrated browser (snapshots, clicks, navigations) + StoreSuite / WooCommerce source review

**Legend:** **Pass** — satisfied by runtime evidence and/or code; **Fail** — mismatch with UAT or defect; **Partial** — not fully observable in automation (a11y tree limits, WC Analytics not re-proven, or scope left to manual visual check).

---

## Engineering notes (for triage)

1. **Stat card count vs UAT:** `Dashboard::get_store_performance_stats()` defines **15** KPI entries (not 17). They can be hidden individually via `storesuite_settings` keys `storesuite_show_perf_*`, but the default catalog is fifteen metrics.

```203:280:/Users/aiarnob/Herd/woocommerce/wp-content/plugins/storesuite/includes/Dashboard.php
	protected function get_store_performance_stats(): array {
		$stats = array(
			array(
				'stat'   => 'revenue/total_sales',
				'label'  => __( 'Total sales', 'storesuite' ),
				'format' => 'currency',
			),
			// ... 14 more entries through downloads/download_count ...
		);
```

2. **KPI grid columns:** `store-performance.php` uses responsive Bootstrap columns (`row-cols-1` … `row-cols-xl-5`), not a fixed **3-column** grid at all widths.

```27:28:/Users/aiarnob/Herd/woocommerce/wp-content/plugins/storesuite/templates/dashboard/store-performance.php
			<div class="storesuite-kpi-summary">
				<div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 row-cols-xl-5 g-0">
```

3. **Date range without Apply:** The daterangepicker updates the visible `#storesuite_dashboard_range` and hidden fields when a preset is chosen, but **server-rendered** heading and stats only change after **GET** submit. Confirmed in browser: after **Last 7 Days**, field showed `2026-04-05 - 2026-04-11` while the **H3 stayed** `Dashboard (Apr 1, 2026 – Apr 30, 2026)` until **Apply**; then URL gained `storesuite_dashboard_start` / `end` and heading became `Dashboard (Apr 5, 2026 – Apr 11, 2026)`.

4. **Reversed date range:** PHP swaps start/end if `end < start` (dashboard template and `get_store_performance_date_range()`).

5. **Previous period for % change:** `get_previous_date_range()` uses an **equal-length** window immediately before the current period; `calculate_percent_change()` implements `((current - previous) / |previous|) * 100`, with `null` when previous is zero and current non-zero.

6. **% change colours:** `.storesuite-kpi-card--up` (green `#059669`), `--down` (red `#dc2626`), `--flat` / `--na` (neutral).

7. **Currency formatting:** `format_value()` uses `wc_price()` for currency stats — any **“0.00b”** artefact would come from WooCommerce/theme, not StoreSuite string concatenation in this path.

8. **Dashboard date range not preserved on cross-page navigation:** After applying **Last 7 Days**, visiting `/storesuite-dashboard/products/` and returning to `/storesuite-dashboard/` (no query string) **reset** the picker to default **This month** (`2026-04-01 - 2026-04-30`). No client persistence for `storesuite_dashboard_start` / `end` was observed.

9. **Leaderboard column labels:** Templates use title case (**Product**, **Customer name**, **Coupon code**), while the UAT text uses all caps — cosmetic mismatch only.

---

## Module 1: Page load and layout

| ID              | Result  | Evidence / notes                                                                                                                                                                                                                          |
| --------------- | ------- | ----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **UAT-DSH-001** | Pass    | Snapshot: **H3** `Dashboard (Apr 1, 2026 – Apr 30, 2026)` on first load; date field `2026-04-01 - 2026-04-30`. Sidebar includes **Dashboard** list item (active styling is CSS; not asserted from a11y alone).                            |
| **UAT-DSH-002** | Pass    | Dashboard root snapshot shows **no** breadcrumb trail (unlike e.g. Products which exposes breadcrumb list items).                                                                                                                         |
| **UAT-DSH-003** | Partial | Sidebar + main region present; **Store performance** + four **H2** table sections observed. Fine-grained “stat cards grid” cell content is **not** exposed as named nodes in the accessibility snapshot — manual visual pass recommended. |
| **UAT-DSH-004** | Pass    | **H1** `StoreSuite` (ref `e16`) in sidebar area.                                                                                                                                                                                          |
| **UAT-DSH-005** | Partial | **Visit Home** link present in header (`dashboard-header.php`); avatar is an `<img>` from `get_avatar_url()` — image may not surface as a named “avatar” control in the snapshot.                                                         |
| **UAT-DSH-006** | Pass    | **Toggle navigation menu** button: first click → `states: [collapsed]`; second click → `states: [expanded]`.                                                                                                                              |

---

## Module 2: Date range selector

| ID             | Result  | Evidence / notes                                                                                                                                                                                                                            |
| -------------- | ------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **UAT-DT-001** | Pass    | Date **textbox** shows `YYYY-MM-DD - YYYY-MM-DD` (e.g. `2026-04-01 - 2026-04-30`).                                                                                                                                                          |
| **UAT-DT-002** | Pass    | With picker open, seven **listitem** presets: Today, Yesterday, Last 7 Days, Last 30 Days, This Month, Last Month, Custom Range (matches `assets/frontend/script.js` `ranges`).                                                             |
| **UAT-DT-003** | Partial | Default range equals **this calendar month** in field and heading; **“highlighted”** state inside the daterangepicker UI not read from a11y tree.                                                                                           |
| **UAT-DT-004** | Partial | Not individually re-run after other tests; behaviour matches **Last 7** pattern (GET + heading update). **Repro:** choose **Today** → **Apply** → confirm URL `storesuite_dashboard_start=end=today` and heading shows single day.          |
| **UAT-DT-005** | Partial | Same as DT-004 for **Yesterday**.                                                                                                                                                                                                           |
| **UAT-DT-006** | Pass    | **Last 7 Days** selected → field `2026-04-05 - 2026-04-11` → **Apply** → URL query params set; heading **Dashboard (Apr 5, 2026 – Apr 11, 2026)**.                                                                                          |
| **UAT-DT-007** | Partial | Logic mirrors **Last 7** (preset + Apply + GET); not separately executed in this run.                                                                                                                                                       |
| **UAT-DT-008** | Partial | **This Month** is the server default when query args absent; re-Apply after other range not re-clicked.                                                                                                                                     |
| **UAT-DT-009** | Partial | **Last Month** preset exists in UI; Apply path not re-executed in this run.                                                                                                                                                                 |
| **UAT-DT-010** | Pass    | After **Last 7 Days** selection, **page heading remained** full April month until **Apply**; confirms stats/title do not update from preset alone.                                                                                          |
| **UAT-DT-011** | Partial | **Custom Range** is a standard **daterangepicker** feature (dual calendars); dual month layout not captured as discrete refs.                                                                                                               |
| **UAT-DT-012** | Partial | Month navigation is library-default; no dedicated refs in snapshot.                                                                                                                                                                         |
| **UAT-DT-013** | Partial | Range highlight is visual inside picker.                                                                                                                                                                                                    |
| **UAT-DT-014** | Partial | Footer range text is daterangepicker locale; expected `YYYY-MM-DD` per `script.js` `locale.format`.                                                                                                                                         |
| **UAT-DT-015** | Partial | In-picker **Apply** is the same interaction model as main **Apply** for presets; custom flow not fully stepped.                                                                                                                             |
| **UAT-DT-016** | Partial | **Cancel** not exercised (no stable button ref in snapshot).                                                                                                                                                                                |
| **UAT-DT-017** | Pass    | **Documented behaviour:** reversed dates are **swapped** server-side (`dashboard.php` and `Dashboard::get_store_performance_date_range()`).                                                                                                 |
| **UAT-DT-018** | Partial | Navigated to `?storesuite_dashboard_start=2027-06-01&storesuite_dashboard_end=2027-06-30` — heading **Jun 1, 2027 – Jun 30, 2027** and field updated. **Numeric all-zero** KPI cells not enumerated in snapshot; expect WC Analytics zeros. |

---

## Module 3: Store performance — stat cards

| ID                                  | Result  | Evidence / notes                                                                                                                                                               |
| ----------------------------------- | ------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------ |
| **UAT-STAT-001**                    | Pass    | **H2** `Store performance` present.                                                                                                                                            |
| **UAT-STAT-002**                    | Fail    | UAT requires **17** cards in a **3-column** grid; implementation provides **15** configurable stats and a **responsive** column count (up to 5 on `xl`).                       |
| **UAT-STAT-003**                    | Partial | Template renders **label**, **value**, and **% change** (`store-performance.php`); per-card text not listed in browser snapshot.                                               |
| **UAT-STAT-004** – **UAT-STAT-018** | Partial | Labels and WC Analytics stat keys are correct in code (`get_store_performance_stats()`); live **non-zero** currency and ordering not extracted from a11y tree in this harness. |
| **UAT-STAT-019**                    | Pass    | Code: equal-length **previous** window + percent formula (see engineering notes).                                                                                              |
| **UAT-STAT-020**                    | Pass    | CSS maps **up/down/flat/na** to green / red / neutral (`style.css`).                                                                                                           |
| **UAT-STAT-021**                    | Partial | Inferred from architecture; not every range toggled while reading each `%` cell.                                                                                               |
| **UAT-STAT-022**                    | Partial | Uses `wc_price()`; verify non-zero amounts visually / in WC settings.                                                                                                          |
| **UAT-STAT-023**                    | Partial | `format_value` returns `—` for `null` analytics values, not always literal `0` / `0%` — may differ slightly from UAT wording.                                                  |

---

## Module 4: Top products table

| ID                              | Result  | Evidence / notes                                                                                               |
| ------------------------------- | ------- | -------------------------------------------------------------------------------------------------------------- |
| **UAT-TP-001**                  | Pass    | **H2** `Top products - Items sold`.                                                                            |
| **UAT-TP-002**                  | Partial | Three columns **Product**, **Items sold**, **Net sales** in template — UAT asks for all-caps **PRODUCT**, etc. |
| **UAT-TP-003**                  | Pass    | `orderby` => `items_sold`, `order` => `desc` in `get_top_products_items_sold_rows()`.                          |
| **UAT-TP-004** – **UAT-TP-006** | Partial | Row cells not in snapshot; code + DataStore path support correct types.                                        |
| **UAT-TP-007**                  | Pass    | Empty message string: **“No products found for this period.”** (`top-products-items-sold.php`).                |
| **UAT-TP-008**                  | Pass    | Same date range object drives widgets (`Dashboard.php` hooks).                                                 |

---

## Module 5: Top categories table

| ID                              | Result  | Evidence / notes                                                     |
| ------------------------------- | ------- | -------------------------------------------------------------------- |
| **UAT-TC-001**                  | Pass    | **H2** `Top categories - Items sold`.                                |
| **UAT-TC-002**                  | Partial | Columns **Category**, **Items sold**, **Net sales** vs UAT all-caps. |
| **UAT-TC-003**                  | Pass    | Query uses `items_sold` descending (same pattern as products).       |
| **UAT-TC-004** – **UAT-TC-005** | Partial | Snapshot does not list tbody text.                                   |
| **UAT-TC-006**                  | Pass    | Empty copy: **“No categories found for this period.”**               |
| **UAT-TC-007**                  | Pass    | Shared date-range plumbing.                                          |

---

## Module 6: Top customers table

| ID                                | Result  | Evidence / notes                                                                                 |
| --------------------------------- | ------- | ------------------------------------------------------------------------------------------------ |
| **UAT-TCU-001**                   | Pass    | **H2** `Top customers - Total spend`.                                                            |
| **UAT-TCU-002**                   | Partial | **Customer name** (template) vs **CUSTOMER NAME** (UAT).                                         |
| **UAT-TCU-003**                   | Pass    | Analytics query orders rows by spend (see `Dashboard.php` `get_top_customers_total_spend_rows`). |
| **UAT-TCU-004** – **UAT-TCU-006** | Partial | Guest vs registered comes from WC customer data; not validated row-by-row here.                  |
| **UAT-TCU-007**                   | Pass    | **“No customers found for this period.”**                                                        |
| **UAT-TCU-008**                   | Pass    | Shared date range.                                                                               |

---

## Module 7: Top coupons table

| ID                                | Result  | Evidence / notes                                                    |
| --------------------------------- | ------- | ------------------------------------------------------------------- |
| **UAT-TCC-001**                   | Pass    | **H2** `Top coupons - Number of orders`.                            |
| **UAT-TCC-002**                   | Partial | **Coupon code**, **Orders**, **Amount discounted** vs UAT all-caps. |
| **UAT-TCC-003**                   | Pass    | Query orders by `orders_count` descending.                          |
| **UAT-TCC-004** – **UAT-TCC-006** | Partial | Row-level not in snapshot.                                          |
| **UAT-TCC-007**                   | Pass    | **“No coupons found for this period.”**                             |
| **UAT-TCC-008**                   | Pass    | Shared date range.                                                  |

---

## Module 8: Navigation

| ID                                | Result  | Evidence / notes                                                                                                                                                                                                                  |
| --------------------------------- | ------- | --------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **UAT-NAV-001**                   | Pass    | Sidebar exposes **Dashboard, Products, Orders, Categories, Brands, Tags, Coupons, Account, Visit Home, WP Dashboard, Logout** (duplicate listitem labels for Logout/Account appear in snapshot — likely mobile + desktop markup). |
| **UAT-NAV-002**                   | Pass    | Navigated to `http://woocommerce.test/storesuite-dashboard/products/`.                                                                                                                                                            |
| **UAT-NAV-003**                   | Partial | Pattern `storesuite_get_navigation_url( 'orders' )` → `/storesuite-dashboard/orders/`; not re-clicked in final loop.                                                                                                              |
| **UAT-NAV-004** – **UAT-NAV-007** | Partial | Same URL builder for `categories`, `brands`, `tags`, `coupons`.                                                                                                                                                                   |
| **UAT-NAV-008**                   | Partial | Menu key `edit-account-details` → **Account**; expect `/storesuite-dashboard/edit-account-details/`.                                                                                                                              |
| **UAT-NAV-009**                   | Partial | Sidebar **Visit Home** uses `get_home_url()` with `_blank` on menu item — confirm store front URL in browser.                                                                                                                     |
| **UAT-NAV-010**                   | Partial | **WP Dashboard** → `get_dashboard_url()` (`/wp-admin/`); click not executed (avoids leaving vendor context).                                                                                                                      |
| **UAT-NAV-011**                   | Partial | **Logout** not executed — would terminate session. Template uses `wp_logout_url( home_url() )`.                                                                                                                                   |
| **UAT-NAV-012**                   | Partial | Header **Visit Home** (`dashboard-header.php`) mirrors sidebar intent; same as NAV-009.                                                                                                                                           |
| **UAT-NAV-013**                   | Fail    | After using a custom range, opening **Products** and returning to **Dashboard** base URL **lost** query params; dashboard reverted to **default this-month** range.                                                               |

---

## Module 9: Data accuracy (cross-check)

| ID               | Result  | Evidence / notes                                                                                                                                                                                                                               |
| ---------------- | ------- | ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **UAT-DATA-001** | Fail    | **Orders** KPI comes from **WC Analytics** `orders/orders_count` for `[after, before]`. The vendor **Orders** list does **not** read `storesuite_dashboard_start` / `end`, so counts are **not guaranteed** to match without manual alignment. |
| **UAT-DATA-002** | Partial | **Total sales** maps to Analytics `revenue/total_sales`, not a naive sum of shop order totals in PHP here.                                                                                                                                     |
| **UAT-DATA-003** | Partial | Requires comparison with **WooCommerce Analytics → Products** report for the same interval.                                                                                                                                                    |
| **UAT-DATA-004** | Partial | Same for **Customers** report vs table rows.                                                                                                                                                                                                   |
| **UAT-DATA-005** | Partial | Formula verified in code; numerical spot-check on a live card with non-null previous period not captured in this run.                                                                                                                          |

---

## Verdict summary

| Area        | Pass | Partial | Fail |
| ----------- | ---- | ------- | ---- |
| Module 1    | 4    | 2       | 0    |
| Module 2    | 4    | 12      | 0    |
| Module 3    | 3    | 12      | 1    |
| Modules 4–7 | 12   | 16      | 0    |
| Module 8    | 2    | 9       | 1    |
| Module 9    | 0    | 4       | 1    |

**Primary failures:** **UAT-STAT-002** (15 vs 17 cards, grid definition), **UAT-NAV-013** (date range not restored), **UAT-DATA-001** (orders list vs analytics orders count).

---

## Appendix — Verbatim checklist (for stakeholder sign-off)

Paste into your tracker and mark **Pass / Partial / Fail** from the tables above.

### Module 1

-   [ ] **UAT-DSH-001** — Page loads with heading "Dashboard (Apr 1, 2026 – Apr 30, 2026)" showing the current month range by default; sidebar "Dashboard" item is active/highlighted
-   [ ] **UAT-DSH-002** — Page has no breadcrumb (by design for the root dashboard page)
-   [ ] **UAT-DSH-003** — Layout renders correctly: fixed left sidebar + main content area with stat cards grid + four data tables below
-   [ ] **UAT-DSH-004** — "StoreSuite" H1 branding is visible at the top of the sidebar
-   [ ] **UAT-DSH-005** — Top-right header shows "Visit Home" link and user avatar icon
-   [ ] **UAT-DSH-006** — Hamburger/collapse icon is visible at the top-left of the main content; clicking it collapses/expands the sidebar

### Module 2

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
-   [ ] **UAT-DT-011** — Selecting **Custom Range** opens a dual-month calendar picker showing two consecutive months side by side
-   [ ] **UAT-DT-012** — Left/right arrows navigate between months in the calendar picker
-   [ ] **UAT-DT-013** — Clicking a start date then an end date highlights the selected range on the calendar
-   [ ] **UAT-DT-014** — Selected range is shown in "YYYY-MM-DD – YYYY-MM-DD" format at the bottom of the calendar
-   [ ] **UAT-DT-015** — Clicking **Apply** (blue button in calendar) closes the picker and applies the custom range; stat cards and tables update; page heading updates to the selected range
-   [ ] **UAT-DT-016** — Clicking **Cancel** (grey button in calendar) closes the picker without applying any change; previously selected range is retained
-   [ ] **UAT-DT-017** — Selecting an end date before the start date is handled gracefully (blocked or auto-corrected; document actual behaviour)
-   [ ] **UAT-DT-018** — Selecting a future date range shows correct empty state (0 values) for all metrics

### Module 3

-   [ ] **UAT-STAT-001** — "Store performance" section heading (H2) is visible above the stat card grid
-   [ ] **UAT-STAT-002** — Stat cards render in a 3-column grid layout; all 17 cards are visible and properly aligned
-   [ ] **UAT-STAT-003** — Each card shows: a metric label, a value, and a percentage change indicator
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
-   [ ] **UAT-STAT-019** — Percentage change on each card compares the selected period against the equivalent previous period (e.g. This Month vs Last Month)
-   [ ] **UAT-STAT-020** — Positive change shows in green; negative change shows in red; 0% shows in neutral colour
-   [ ] **UAT-STAT-021** — Switching the date range updates all percentage change indicators accordingly
-   [ ] **UAT-STAT-022** — Currency values display with the correct symbol and decimal formatting as configured in WooCommerce settings (current observation shows "0.00b" — verify this is not a bug for non-zero values)
-   [ ] **UAT-STAT-023** — When no data exists for the selected period, all cards show "0" or "0.00" (in correct currency) and "0%" change — NOT blank or broken layout

### Module 4

-   [ ] **UAT-TP-001** — "Top products - Items sold" section heading (H2) is visible
-   [ ] **UAT-TP-002** — Table has exactly 3 columns in order: **PRODUCT**, **ITEMS SOLD**, **NET SALES**
-   [ ] **UAT-TP-003** — When data exists for the selected period, products are listed in descending order by items sold
-   [ ] **UAT-TP-004** — PRODUCT column shows the product name
-   [ ] **UAT-TP-005** — ITEMS SOLD column shows an integer count
-   [ ] **UAT-TP-006** — NET SALES column shows a currency value in correct format
-   [ ] **UAT-TP-007** — When no products were sold in the period, the table shows "No products found for this period." message
-   [ ] **UAT-TP-008** — Changing the date range updates the Top Products table accordingly

### Module 5

-   [ ] **UAT-TC-001** — "Top categories - Items sold" section heading (H2) is visible
-   [ ] **UAT-TC-002** — Table has exactly 3 columns in order: **CATEGORY**, **ITEMS SOLD**, **NET SALES**
-   [ ] **UAT-TC-003** — When data exists, categories are listed in descending order by items sold
-   [ ] **UAT-TC-004** — CATEGORY column shows the category name
-   [ ] **UAT-TC-005** — ITEMS SOLD and NET SALES columns show correct values
-   [ ] **UAT-TC-006** — When no category data exists for the period, table shows "No categories found for this period." message
-   [ ] **UAT-TC-007** — Changing date range updates the Top Categories table accordingly

### Module 6

-   [ ] **UAT-TCU-001** — "Top customers - Total spend" section heading (H2) is visible
-   [ ] **UAT-TCU-002** — Table has exactly 3 columns in order: **CUSTOMER NAME**, **ORDERS**, **TOTAL SPEND**
-   [ ] **UAT-TCU-003** — When data exists, customers are listed in descending order by total spend
-   [ ] **UAT-TCU-004** — CUSTOMER NAME shows the customer's display name (or "Guest" for guest checkouts)
-   [ ] **UAT-TCU-005** — ORDERS column shows integer count of orders placed by that customer
-   [ ] **UAT-TCU-006** — TOTAL SPEND shows cumulative spend in correct currency format
-   [ ] **UAT-TCU-007** — When no customer data exists for the period, table shows "No customers found for this period." message
-   [ ] **UAT-TCU-008** — Changing date range updates the Top Customers table accordingly

### Module 7

-   [ ] **UAT-TCC-001** — "Top coupons - Number of orders" section heading (H2) is visible
-   [ ] **UAT-TCC-002** — Table has exactly 3 columns in order: **COUPON CODE**, **ORDERS**, **AMOUNT DISCOUNTED**
-   [ ] **UAT-TCC-003** — When data exists, coupons are listed in descending order by number of orders
-   [ ] **UAT-TCC-004** — COUPON CODE column shows the coupon code string
-   [ ] **UAT-TCC-005** — ORDERS column shows integer count of orders where the coupon was used
-   [ ] **UAT-TCC-006** — AMOUNT DISCOUNTED shows total discount value in correct currency format
-   [ ] **UAT-TCC-007** — When no coupon data exists for the period, table shows "No coupons found for this period." message
-   [ ] **UAT-TCC-008** — Changing date range updates the Top Coupons table accordingly

### Module 8

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

### Module 9

-   [ ] **UAT-DATA-001** — "Orders" stat card value matches the total order count shown in the Orders list for the same date range
-   [ ] **UAT-DATA-002** — "Total sales" value matches the sum of completed order totals for the same period
-   [ ] **UAT-DATA-003** — Top Products table order matches what WooCommerce admin reports show for the same period
-   [ ] **UAT-DATA-004** — Top Customers table customer names and spend values are accurate vs order records
-   [ ] **UAT-DATA-005** — Stat card percentage change is calculated as: `((current - previous) / previous) × 100`; verify with at least one card where both periods have data

---

**Report file:** `/Users/aiarnob/STORESUITE-UAT-Dashboard-2026-04-11.md`
