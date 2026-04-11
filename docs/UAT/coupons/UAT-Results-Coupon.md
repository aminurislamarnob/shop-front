## Module 1: Coupon List — UAT (`/storesuite-dashboard/coupons/`)

**Environment:** `http://woocommerce.test`, browser (wide + full-page screenshot) + WP-CLI. Checklist: **UAT-CPN-001 → UAT-CPN-016** (issue #114).

### Page & table

| ID          | Result                     | Notes                                                                                                                                                                                                                                                                                                                  |
| ----------- | -------------------------- | ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **CPN-001** | **Pass**                   | H3 **Coupons**; breadcrumb **Dashboard > Coupons > Coupons**; sidebar **Coupons** active (highlighted).                                                                                                                                                                                                                |
| **CPN-002** | **Pass**                   | Template defines **9** columns in order: checkbox, **Code**, **Type**, **Amount**, **Description**, **Usage / Limit**, **Expiry Date**, **Status**, **Actions** (`templates/coupons/coupons.php`). `.storesuite-table-responsive` may require **horizontal scroll** on smaller widths to see **Status** / **Actions**. |
| **CPN-003** | **Pass**                   | **CODE** shows coupon strings (e.g. `mod3_uat_full_20260411`, `flat_100`).                                                                                                                                                                                                                                             |
| **CPN-004** | **Pass**                   | **TYPE** shows WC label (e.g. **Fixed cart discount**).                                                                                                                                                                                                                                                                |
| **CPN-005** | **Pass** (this env)        | **AMOUNT** uses `wc_price()` (e.g. `12.50৳`, `100.00৳`). No `100.00b` observed here (B-001 may be environment-specific).                                                                                                                                                                                               |
| **CPN-006** | **Pass**                   | Description text or **–** when empty.                                                                                                                                                                                                                                                                                  |
| **CPN-007** | **Pass**                   | **Usage / limit** format (e.g. `0 / 10`, `1 / ∞`).                                                                                                                                                                                                                                                                     |
| **CPN-008** | **Pass**                   | Expiry formatted or **–**.                                                                                                                                                                                                                                                                                             |
| **CPN-009** | **Pass**                   | `storesuite_get_post_status()`: **publish → Online** (`storesuite-badge-success`), **draft → Draft**, **pending → Pending Review** (`includes/functions.php`). Temporary draft/pending coupons used for status coverage then **removed** (posts **558–560** deleted).                                                  |
| **CPN-010** | **Pass**                   | Row menu markup: **Edit**, **Delete** only.                                                                                                                                                                                                                                                                            |
| **CPN-011** | **Pass**                   | `/edit-coupon/146/` loads **Edit Coupon** with **flat_100** (and data) pre-filled — equivalent to **Edit** for that id.                                                                                                                                                                                                |
| **CPN-012** | **Pass** (known behaviour) | Delete flow: SweetAlert + AJAX (`form-handler.js`); aligned with earlier Coupons UAT. Automation could not click **Edit/Delete** in this run (dropdown `<li>` targets not visible until menu opens).                                                                                                                   |
| **CPN-013** | **Not tested**             | Fewer than `posts_per_page` (10) coupons — pagination block not emitted when `max_num_pages` ≤ 1.                                                                                                                                                                                                                      |
| **CPN-014** | **Pass**                   | No pagination UI with current dataset.                                                                                                                                                                                                                                                                                 |
| **CPN-015** | **Pass**                   | **+ Add Coupon** → `/storesuite-dashboard/add-new-coupon/`.                                                                                                                                                                                                                                                            |
| **CPN-016** | **Not tested**             | Empty state needs **zero** coupons; not executed to avoid wiping data. Template uses **not-found** (“No coupon found!”) when query has no posts.                                                                                                                                                                       |

### Cleanup

-   Deleted UAT-only coupons **558** (`m1_uat_draft`), **559** (`m1_uat_pending`), **560** (`m1_del_confirm`). **146** and **555** left unchanged.

### Summary

**Pass:** CPN-001, 002, 003, 004, 005, 006, 007, 008, 009, 010, 011, 014, 015.  
**Pass (implementation / prior runs):** CPN-012.  
**Not tested:** CPN-013, CPN-016.

## Module 2: Search — UAT (`/storesuite-dashboard/coupons/`)

**Environment:** `http://woocommerce.test`, logged-in dashboard, browser automation (including slow character-by-character typing in the search field) + plugin source review.

### Results

| ID             | Result           | Notes                                                                                                                                                                |
| -------------- | ---------------- | -------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **UAT-SR-001** | **Pass**         | **Search Coupon** textbox visible with correct placeholder; sits in the table header area (left), **Add Coupon** on the right.                                       |
| **UAT-SR-002** | **Fail**         | Entering exact code **`flat_100`** did **not** narrow the list — **two** rows remained (both `flat_100` and `mod3_uat_full_20260411` per WP-CLI).                    |
| **UAT-SR-003** | **Fail**         | Partial query **`mod3`** (typed slowly for `input`/`keyup`) still showed **two** rows; only the `mod3_*` coupon should match.                                        |
| **UAT-SR-004** | **Fail**         | No dedicated empty / no-results state when the query matches nothing; full table stays. (`not-found` template only applies when **zero** coupons exist server-side.) |
| **UAT-SR-005** | **Vacuous pass** | Full server-rendered list is always shown; clearing the field does not change behavior because filtering never runs.                                                 |

### Root cause (implementation)

-   `templates/coupons/coupons.php` renders `<input type="text" name="search" id="search" …>` inside a form with **empty `action`**, but there is **no** frontend script binding `#search` to filter `.single-coupon-item` rows (e.g. by `.tbl-coupon-code`).
-   `.single-coupon-item` is only referenced in JS for **delete** row removal, not search.

### Suggested fix (for a follow-up PR)

-   Add client-side filter on `#search` `input` against coupon code text in each row, **or** server-side `s` / custom query arg on `WP_Query` for `shop_coupon`, plus a **“no matching coupons”** row or message when zero rows match.

## Module 3: Add New Coupon — UAT complete (`/add-new-coupon/`)

**Environment:** `http://woocommerce.test` — browser UAT + WP-CLI / `WC_Coupon` checks (issue #114 checklist **UAT-ADD-001 → UAT-ADD-035**).

### Primary flow (create + redirect)

-   **UAT-ADD-031:** Created coupon **`mod3_uat_full_20260411`** (post **#555**): success notice, redirect to `/storesuite-dashboard/coupons/`.
-   **Persistence (#555):** `fixed_cart`, amount **12.50**, multiline description, expiry **2030-12-31**, min spend **50** / max **250**, usage limits **10 / 2 / 3**, allowed emails `buyer@test.com` + `*@vip.example`, **Allow free shipping**, **Individual use only**, and **Exclude sale items** all **ON** in DB.
-   **UAT-ADD-032:** `usage_count=0`, `usage_limit=10` → list shows **0 / 10**.
-   **UAT-ADD-033:** Expiry set → row is **not** “–” in EXPIRY DATE (date stored).
-   **UAT-ADD-034:** Duplicate code **`flat_100`** → SweetAlert error; no duplicate created.
-   **UAT-ADD-008:** Percentage discount, amount **101** → SweetAlert error (WooCommerce `Invalid discount amount`).
-   **UAT-ADD-003 / 004 / 005:** “Generate coupon code” fills field with **8-char** alphanumeric (WC-style charset); discount type dropdown exposes all **three** types.
-   **UAT-ADD-001 / 030:** Heading “Add New Coupon”, breadcrumb **Dashboard > Coupons > Add New Coupon**, two-column layout (General left, sidebar right), **Create Coupon** in sidebar.

### Validations & edge cases

| ID                         | Result               | Notes                                                                                                                                                                                                                   |
| -------------------------- | -------------------- | ----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **ADD-002**                | Pass                 | Blank coupon code → client validation blocks submit (inline).                                                                                                                                                           |
| **ADD-006** (blank amount) | Pass                 | Empty amount → client validation blocks submit.                                                                                                                                                                         |
| **ADD-006** (amount **0**) | **Fail vs spec**     | Client and WC **allow 0**; a test coupon was created then **deleted** (post 556) to avoid clutter.                                                                                                                      |
| **ADD-007**                | Pass                 | Decimals (e.g. 12.50); `type="number"` constrains non-numeric input.                                                                                                                                                    |
| **ADD-010**                | Partial / risk       | Datepicker uses `yy-mm-dd` and `minDate: 0` (discourages past dates). If a non-ISO string reaches WC, **`set_props` may still succeed** and store an incorrect date (e.g. eval with `31/12/2026` → epoch-style result). |
| **ADD-011**                | Pass (documented)    | Past dates blocked in UI via datepicker `minDate: 0` (`form-handler.js`).                                                                                                                                               |
| **ADD-012–014**            | Pass                 | Toggles / exclude sale persisted on #555.                                                                                                                                                                               |
| **ADD-015–016**            | Pass (save)          | Min/max saved; checkout enforcement is **Module 5 (E2E)**.                                                                                                                                                              |
| **ADD-017**                | Pass                 | WC blocks **min > max** (`Invalid maximum spend value` on `set_props`).                                                                                                                                                 |
| **ADD-018–020**            | Not run (automation) | Product / exclude-product fields need Select2 + AJAX; not exercised in this pass.                                                                                                                                       |
| **ADD-021–022**            | Not run (automation) | Category multi-select is Select2; automation could not set options; #555 has **no** category restrictions in DB.                                                                                                        |
| **ADD-023**                | Pass                 | Comma-separated emails + `*` wildcard saved correctly.                                                                                                                                                                  |
| **ADD-024–025**            | Partial              | Controls + defaults **Published / Public** confirmed; **Draft / Pending / Private** list badges not re-checked on a new row in this run.                                                                                |
| **ADD-026–028**            | Pass                 | Limits persisted (10 / 2 / 3).                                                                                                                                                                                          |
| **ADD-029**                | **Fail vs spec**     | `absint()` on usage fields turns **negative input into positive** (e.g. `-3` → `3`), no validation error.                                                                                                               |
| **ADD-035**                | Pass (intent)        | **Back** is a normal link to coupons URL in `coupon-form.php`; navigation to `/coupons/` verified. (Possible click-target quirk as on edit flow.)                                                                       |

### Data on site after test

-   **Coupon #555** (`mod3_uat_full_20260411`) left for inspection — delete if undesired.
-   Ephemeral **#556** (zero-amount probe) **force-deleted**.

### Suggested follow-ups

1. Reject **coupon amount 0** in JS and/or server to match **ADD-006**.
2. Strict **expiry** validation server-side; avoid invalid strings producing bad dates.
3. Fix **ADD-029** with explicit non-negative checks instead of relying on `absint()` for signed strings.
4. Re-run **ADD-018–022** with Select2-capable automation or manual QA.
