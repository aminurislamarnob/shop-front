# StoreSuite Orders module — UAT report

**Environment:** `http://woocommerce.test` (Laravel Herd), WooCommerce + StoreSuite vendor dashboard  
**Role:** Logged-in vendor/seller (`manage_woocommerce` / shop order capabilities as exercised in session)  
**Primary fixtures:** Order **#416** (edit/view), disposable orders **613/614** (bulk experiments; data may change)  
**Executed:** 2026-04-11 (Cursor integrated browser automation + WP-CLI cross-checks + targeted code review)

**Legend:** Pass — observed or strongly inferred from implementation; Fail — confirmed mismatch with UAT or defect; Partial — not fully exercised in automation, ambiguous, or depends on environment (email, WC Analytics).

---

## Engineering findings (fix backlog)

1. **HPOS / customer name search (`UAT-SR-005`):** `OrderManager::get_all_orders()` uses `meta_query` on `_billing_first_name` / `_billing_last_name` for `customers` search. That path does not align with HPOS order storage the way `wc_get_orders( [ 'billing_first_name' => … ] )` does, so name search can return **no rows** for orders that exist (e.g. customer “Aminur” / order 416).

```70:86:/Users/aiarnob/Herd/woocommerce/wp-content/plugins/storesuite/includes/Order/OrderManager.php
				case 'customers':
					// Search by customer name (billing first name, last name, or display name)
					global $wpdb;
					$args['meta_query'] = array(
						'relation' => 'OR',
						array(
							'key'     => '_billing_first_name',
							'value'   => $search_term,
							'compare' => 'LIKE',
						),
						array(
							'key'     => '_billing_last_name',
							'value'   => $search_term,
							'compare' => 'LIKE',
						),
					);
					break;
```

2. **Month filter query param `m` (`UAT-FL-009`–`011`, `FL-013`):** The filter form uses `name="m"` (WordPress reserved month archive var). Combined URLs or redirects can collide with core rewrite/query handling; automation saw **404 / wrong route** when `m=YYYYMM` was appended in some navigation patterns. Prefer a **namespaced** parameter (e.g. `order_month`) and map it in `OrderManager`.

```17:17:/Users/aiarnob/Herd/woocommerce/wp-content/plugins/storesuite/templates/orders/order-filters-offcanvas.php
$current_month         = isset( $_GET['m'] ) ? sanitize_text_field( wp_unslash( $_GET['m'] ) ) : '0';
```

3. **Refunded badge colour (`UAT-OL-004`):** Refunded maps to Bootstrap-style **`info`**, not a distinct purple class as specified in the UAT.

```144:160:/Users/aiarnob/Herd/woocommerce/wp-content/plugins/storesuite/includes/functions.php
function storesuite_get_order_status_class( $status = '' ) {
	$statuses = apply_filters(
		'storesuite_get_order_status_class',
		array(
			OrderStatus::PENDING    => 'warning',
			// ...
			OrderStatus::REFUNDED   => 'info',
```

4. **`tel:` / `mailto:` on order details (`UAT-VW-008`, `VW-009`, `VW-011`):** Billing/shipping phone and email are output with `esc_html()` only (no links).

```18:24:/Users/aiarnob/Herd/woocommerce/wp-content/plugins/storesuite/templates/orders/order-details-customer.php
				<?php if ( $order->get_billing_phone() ) : ?>
					<p class="woocommerce-customer-details--phone"><?php echo esc_html( $order->get_billing_phone() ); ?></p>
				<?php endif; ?>

				<?php if ( $order->get_billing_email() ) : ?>
					<p class="woocommerce-customer-details--email"><?php echo esc_html( $order->get_billing_email() ); ?></p>
				<?php endif; ?>
```

5. **Bulk apply with no selection / no action (`UAT-BA-005`, `BA-006`):** Server redirects silently to the orders URL with **no user-visible error** (acceptable “no-op” only if product copy states that).

```424:433:/Users/aiarnob/Herd/woocommerce/wp-content/plugins/storesuite/includes/Order/OrderController.php
		if ( ! isset( $_POST['bulk_order_ids'] ) || empty( $_POST['bulk_order_ids'] ) ) {
			wp_safe_redirect( storesuite_get_navigation_url( 'orders' ) );
			exit;
		}

		$action = isset( $_POST['action'] ) ? sanitize_text_field( wp_unslash( $_POST['action'] ) ) : '';

		if ( '-1' === $action || empty( $action ) ) {
			wp_safe_redirect( storesuite_get_navigation_url( 'orders' ) );
			exit;
		}
```

6. **Bulk “Move to Trash” confirmation (`UAT-BA-010`):** `handle_order_bulk_actions` deletes immediately on POST with **no Swal/confirm** in PHP. If the UI shows a prompt, it must be client-side only (not observed end-to-end in this run).

7. **Create order with zero line items (`UAT-AO-025`):** Clicking **Create Order** with no products showed **Success!** (SweetAlert). WP-CLI immediately listed new orders **616** and **617** with **`items=0`**, then they were **permanently deleted** to avoid polluting the store. Server-side validation for at least one product is missing or bypassed.

8. **Table header casing (`UAT-OL-002`):** Template uses title case **Order**, **Action** (singular), not all-caps **ORDER** / **ACTION**.

```132:139:/Users/aiarnob/Herd/woocommerce/wp-content/plugins/storesuite/templates/orders/orders.php
							<th><?php echo esc_html__( 'Order', 'storesuite' ); ?></th>
							<th><?php echo esc_html__( 'Status', 'storesuite' ); ?></th>
							<th><?php echo esc_html__( 'Order Total', 'storesuite' ); ?></th>
							<th><?php echo esc_html__( 'Total Items', 'storesuite' ); ?></th>
							<th><?php echo esc_html__( 'Customer', 'storesuite' ); ?></th>
							<th><?php echo esc_html__( 'Billing Phone', 'storesuite' ); ?></th>
							<th><?php echo esc_html__( 'Date', 'storesuite' ); ?></th>
							<th class="text-right"><?php echo esc_html__( 'Action', 'storesuite' ); ?></th>
```

9. **Styled checkboxes:** Row/header checkboxes are visually custom (`opacity` / label overlay). **Element-ref clicks often miss** the real input; bulk status changes were **unreliable** in automation (orders 613/614 did not update when Apply ran without confirmed selection).

---

## Module 1 — Order list (`/storesuite-dashboard/orders/`)

| ID             | Result  | Notes                                                                                                                                                                            |
| -------------- | ------- | -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **UAT-OL-001** | Pass    | Heading **Orders**, breadcrumb **Dashboard > Orders > Orders**, sidebar **Orders** active (browser snapshot).                                                                    |
| **UAT-OL-002** | Partial | Columns and order match UAT; labels are **Order** / **Action** (not all-caps **ORDER** / **ACTION**).                                                                            |
| **UAT-OL-003** | Pass    | Order numbers render as links (e.g. **#416**).                                                                                                                                   |
| **UAT-OL-004** | Partial | Pending payment **warning** (yellow), processing/completed **success** (green) observed; **Refunded** uses **`info`** in code, not purple (`storesuite_get_order_status_class`). |
| **UAT-OL-005** | Pass    | Formatted totals from WooCommerce; strikethrough on discounted rows observed (e.g. **#279** in list).                                                                            |
| **UAT-OL-006** | Pass    | Empty phone shows **N/A** per `orders.php` logic.                                                                                                                                |
| **UAT-OL-007** | Pass    | Dates formatted (e.g. **Mar 28, 2026** style) via column helper.                                                                                                                 |
| **UAT-OL-008** | Pass    | Row **⋯** menu exposes **View** and **Edit**.                                                                                                                                    |
| **UAT-OL-009** | Pass    | **View** → `/storesuite-dashboard/order-details/{id}/` with order **#416** content.                                                                                              |
| **UAT-OL-010** | Pass    | **Edit** → `/storesuite-dashboard/edit-order/{id}/` with **#416** data pre-filled.                                                                                               |
| **UAT-OL-011** | Pass    | Order number link navigates to details flow (same as list expectations for this build).                                                                                          |
| **UAT-OL-012** | Pass    | **Add Order** → `/storesuite-dashboard/add-new-order/`.                                                                                                                          |
| **UAT-OL-013** | Pass    | Pagination text **“Showing 1 to 10 of N”** observed (N = 19 in session).                                                                                                         |
| **UAT-OL-014** | Pass    | Page **2** and next control load the next slice; active page styling updates.                                                                                                    |

---

## Module 2 — Search

| ID             | Result | Notes                                                                                                                                                                                                                                                                                                            |
| -------------- | ------ | ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **UAT-SR-001** | Pass   | **Search Order** placeholder; type dropdown present (default **All** after WC user setting sync in template).                                                                                                                                                                                                    |
| **UAT-SR-002** | Pass   | Options: **Order ID**, **Customer Email**, **Customers**, **Products**, **All** (`orders.php`).                                                                                                                                                                                                                  |
| **UAT-SR-003** | Pass   | **Order ID** `416` → single matching row.                                                                                                                                                                                                                                                                        |
| **UAT-SR-004** | Pass   | **Customer Email** `aminur@welabs.dev` → multiple matching rows.                                                                                                                                                                                                                                                 |
| **UAT-SR-005** | Fail   | **Customers** (name) search returns **no orders** for names that exist on HPOS orders (e.g. **Aminur** / order **416**). Root cause: `meta_query` on billing meta vs HPOS query support (see code citation above). **Repro:** Orders → search type **Customers** → type billing first name → **No order found!** |
| **UAT-SR-006** | Pass   | **Products** e.g. **T-Shirt** → many matching rows.                                                                                                                                                                                                                                                              |
| **UAT-SR-007** | Pass   | **All** with a nonsense string → empty state **“No order found!”**.                                                                                                                                                                                                                                              |
| **UAT-SR-008** | Pass   | Non-existent term → **not-found** template messaging.                                                                                                                                                                                                                                                            |
| **UAT-SR-009** | Pass   | Clear `search_by`, submit → full list restored.                                                                                                                                                                                                                                                                  |
| **UAT-SR-010** | Pass   | Search uses a **GET** form with a submit button; **Enter** in the search field submits the form in standard HTML (`orders.php` form).                                                                                                                                                                            |

---

## Module 3 — Filter

| ID             | Result         | Notes                                                                                                                                                                                                                                                                                      |
| -------------- | -------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------ |
| **UAT-FL-001** | Partial        | **Filter** opens **Filter Orders** off-canvas; **X** close control exists in template (`storesuite-order-filter-close`). Escape dismissed panel in one session; **X** click not re-recorded in final snapshots.                                                                            |
| **UAT-FL-002** | Pass           | Panel: **Order Status**, **Registered Customer**, **Sales Channel**, **Date**.                                                                                                                                                                                                             |
| **UAT-FL-003** | Pass           | Status options align with `wc_get_order_statuses()` (All + WC statuses).                                                                                                                                                                                                                   |
| **UAT-FL-004** | Pass           | **Processing** + **Apply Filters** → only processing rows (URL `order_status=processing`).                                                                                                                                                                                                 |
| **UAT-FL-005** | Partial        | SelectWoo customer control present; full typeahead not exhaustively scripted.                                                                                                                                                                                                              |
| **UAT-FL-006** | Pass           | `_customer_user=1` (admin) + apply → subset matches that customer.                                                                                                                                                                                                                         |
| **UAT-FL-007** | Pass           | Channel options: **All sales channels**, **Admin**, **Checkout**, **Point of Sale**.                                                                                                                                                                                                       |
| **UAT-FL-008** | Pass           | **Checkout** (`checkout,store-api`) + apply → filtered subset.                                                                                                                                                                                                                             |
| **UAT-FL-009** | Pass           | **All dates** + month/year options rendered from `get_months_filter_options()`.                                                                                                                                                                                                            |
| **UAT-FL-010** | Fail / Partial | Month filter relies on **`m`**. Automation and manual URL tests hit **routing / 404** when `m` combined or redirected incorrectly. **Repro risk:** apply March 2026 from UI and confirm URL stays under **`/storesuite-dashboard/orders/`**; if WP interprets `m` globally, results break. |
| **UAT-FL-011** | Partial        | Intersection logic exists in `OrderManager`; **blocked** by `m` instability in this environment—retest after renaming query arg.                                                                                                                                                           |
| **UAT-FL-012** | Partial        | **Reset** link is `href="?"` on the filter form—intended to clear query string on current orders URL (`order-filters-offcanvas.php`). Not re-verified after every edge URL.                                                                                                                |
| **UAT-FL-013** | Partial        | Search + status intersect observed (`search_by` + `search-filter` + `order_status`); full triple with **month** not trusted until `m` is fixed.                                                                                                                                            |

---

## Module 4 — Bulk actions

| ID             | Result  | Notes                                                                                                                                                                                                               |
| -------------- | ------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **UAT-BA-001** | Pass    | **Bulk actions** dropdown; default **Bulk actions** (`value="-1"`).                                                                                                                                                 |
| **UAT-BA-002** | Pass    | Options match template: processing, on-hold, completed, cancelled, trash.                                                                                                                                           |
| **UAT-BA-003** | Partial | Header `#cb-select-all-orders` exists; **automation could not reliably toggle** hidden checkbox hit targets.                                                                                                        |
| **UAT-BA-004** | Partial | Same checkbox styling limitation.                                                                                                                                                                                   |
| **UAT-BA-005** | Pass    | No rows selected → **silent redirect** (no toast)—matches code; weak UX vs “appropriate error”.                                                                                                                     |
| **UAT-BA-006** | Pass    | Default bulk action + Apply → **silent redirect** (no-op).                                                                                                                                                          |
| **UAT-BA-007** | Fail    | With automation, **Apply** + **completed** did **not** change disposable orders’ statuses (selection not applied). **Manual repro:** use label click or visible tick area to ensure checkboxes checked, then Apply. |
| **UAT-BA-008** | Fail    | Same as BA-007 for **on-hold** in automated attempt.                                                                                                                                                                |
| **UAT-BA-009** | Fail    | Same as BA-007 for **cancelled** in automated attempt.                                                                                                                                                              |
| **UAT-BA-010** | Fail    | PHP **deletes on POST** without confirm step in `OrderController::handle_order_bulk_actions`. UAT expects **confirm/cancel** prompt.                                                                                |
| **UAT-BA-011** | Partial | Not observed end-to-end after failed bulk updates; expected behavior on successful POST is redirect back to list.                                                                                                   |

---

## Module 5 — Add new order (`/add-new-order/`)

| ID             | Result  | Notes                                                                                                                                                      |
| -------------- | ------- | ---------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **UAT-AO-001** | Pass    | **Add New Order** heading; two-column layout; main form + **General** / **Order notes** sidebar; fields empty/defaulted.                                   |
| **UAT-AO-002** | Partial | Product search combobox present; full autocomplete list not captured in snapshot text.                                                                     |
| **UAT-AO-003** | Partial | Not re-run end-to-end in final session (edit order covered line items heavily).                                                                            |
| **UAT-AO-004** | Partial | Same.                                                                                                                                                      |
| **UAT-AO-005** | Partial | Coupon field + **Apply Coupon** present; valid coupon application not executed (coupon CLI noise in sandbox earlier).                                      |
| **UAT-AO-006** | Partial | Invalid coupon error path not re-run.                                                                                                                      |
| **UAT-AO-007** | Partial | Fee field + **Add Fee** present; numeric path not re-run.                                                                                                  |
| **UAT-AO-008** | Pass    | Shipping name default **Shipping** in edit flow; add form mirrors same template family.                                                                    |
| **UAT-AO-009** | Pass    | Shipping cost numeric default **0**.                                                                                                                       |
| **UAT-AO-010** | Pass    | Method options include **N/A**, **Flat rate**, **Free shipping**, **Local pickup**, **Local pickup (pickup_location)**, **Other** (edit snapshot).         |
| **UAT-AO-011** | Partial | **Add Shipping** present; full total reconciliation not scripted on add screen.                                                                            |
| **UAT-AO-012** | Pass    | Billing field groups present (first/last/company/address/city/postcode/country/state/email/phone/payment/transaction).                                     |
| **UAT-AO-013** | Partial | Country/state dependency is standard WC behavior; not every country permutation exercised.                                                                 |
| **UAT-AO-014** | Pass    | Shipping section includes customer note textarea.                                                                                                          |
| **UAT-AO-015** | Partial | **Guest** default; customer search combobox present; full select-customer flow not re-run.                                                                 |
| **UAT-AO-016** | Pass    | Date **2026-04-11** with hour/minute spinners on add screen snapshot.                                                                                      |
| **UAT-AO-017** | Pass    | Status list matches WC; default **Pending payment**.                                                                                                       |
| **UAT-AO-018** | Pass    | Order actions options match UAT.                                                                                                                           |
| **UAT-AO-019** | Pass    | **“There are no notes yet.”** when empty.                                                                                                                  |
| **UAT-AO-020** | Pass    | Note textarea placeholder **Enter your note here…**.                                                                                                       |
| **UAT-AO-021** | Pass    | **Internal note** / **Note to customer** + help text in template stack.                                                                                    |
| **UAT-AO-022** | Partial | Add note + timestamp/author not re-executed on add screen in final pass.                                                                                   |
| **UAT-AO-023** | Pass    | **Create Order** full-width primary button in sidebar.                                                                                                     |
| **UAT-AO-024** | Fail    | Success path with **≥1 product** not completed in this run (focus was validation).                                                                         |
| **UAT-AO-025** | Fail    | **No products** still yields **Success!** and creates **pending orders with zero line items** (orders **616**, **617** observed via WP-CLI, then deleted). |
| **UAT-AO-026** | Pass    | New orders receive standard WooCommerce order IDs (incrementing); uniqueness holds.                                                                        |

---

## Module 6 — Edit order (`/edit-order/416/`)

| ID             | Result  | Notes                                                                                                                                                                     |
| -------------- | ------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **UAT-EO-001** | Pass    | **Edit Order** heading; two-column layout; **#416** data loaded.                                                                                                          |
| **UAT-EO-002** | Pass    | Primary button **Update Order**.                                                                                                                                          |
| **UAT-EO-003** | Pass    | Line item **T-Shirt** with **Edit item** / **Delete item** links.                                                                                                         |
| **UAT-EO-004** | Partial | Inline edit affordance present; full save + line total math not stepped through in final snapshot.                                                                        |
| **UAT-EO-005** | Partial | Delete item path not executed (avoid destructive data loss on **#416**).                                                                                                  |
| **UAT-EO-006** | Partial | Product search present on edit screen.                                                                                                                                    |
| **UAT-EO-007** | Pass    | Subtotal/total region present under products.                                                                                                                             |
| **UAT-EO-008** | Pass    | **Recalculate** button visible.                                                                                                                                           |
| **UAT-EO-009** | Pass    | **Refund** exposes restock checkbox, refund amount, optional reason.                                                                                                      |
| **UAT-EO-010** | Partial | Coupon UI present; apply not re-run.                                                                                                                                      |
| **UAT-EO-011** | Partial | Fee UI present.                                                                                                                                                           |
| **UAT-EO-012** | Pass    | Shipping block matches UAT.                                                                                                                                               |
| **UAT-EO-013** | Pass    | Billing block toggles between summary list and editable fields (pencil pattern in session).                                                                               |
| **UAT-EO-014** | Partial | Same as AO-013 for state list accuracy.                                                                                                                                   |
| **UAT-EO-015** | Pass    | Shipping summary + edit pattern.                                                                                                                                          |
| **UAT-EO-016** | Partial | Same as EO-014.                                                                                                                                                           |
| **UAT-EO-017** | Pass    | Customer chip **× admin (#1 – aminur@welabs.dev)** with remove affordance.                                                                                                |
| **UAT-EO-018** | Pass    | **Date Created** shows order date with editable spinners.                                                                                                                 |
| **UAT-EO-019** | Pass    | Status saved to WooCommerce; list badge would follow (status toggled **Completed** then reverted to **pending** via `wc_get_order()->set_status('pending')` for cleanup). |
| **UAT-EO-020** | Partial | Order action + **Update Order** email side effects not verified (no mail log in scope).                                                                                   |
| **UAT-EO-021** | Pass    | Notes list with **Delete note** links.                                                                                                                                    |
| **UAT-EO-022** | Partial | Delete note not executed on **#416** (avoid data loss).                                                                                                                   |
| **UAT-EO-023** | Partial | Internal note add not re-run in final pass.                                                                                                                               |
| **UAT-EO-024** | Partial | **Note to customer** cannot confirm email delivery in this harness.                                                                                                       |
| **UAT-EO-025** | Pass    | **Update Order** shows **Success!** (SweetAlert) after save.                                                                                                              |
| **UAT-EO-026** | Pass    | Status **Completed** persisted (`wc-completed` verified in WP-CLI), then intentionally reverted to **pending** for fixture hygiene.                                       |
| **UAT-EO-027** | Pass    | No-op **Update Order** still surfaced **Success!** in session.                                                                                                            |

---

## Module 7 — Order details (`/order-details/416/`)

| ID             | Result  | Notes                                                                                                                                                                                                                                                                                                                                                                                                                                 |
| -------------- | ------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **UAT-VW-001** | Pass    | **Order #416** heading; breadcrumb **Dashboard > Orders > Order #416**.                                                                                                                                                                                                                                                                                                                                                               |
| **UAT-VW-002** | Fail    | Page is **not** fully read-only: **Add Note** textarea, type dropdown, **Add** button, multiple **Delete note** controls (browser snapshot on `/order-details/416/`).                                                                                                                                                                                                                                                                 |
| **UAT-VW-003** | Pass    | **Edit Order** navigates to edit route.                                                                                                                                                                                                                                                                                                                                                                                               |
| **UAT-VW-004** | Pass    | **Add Order** → add-new-order route.                                                                                                                                                                                                                                                                                                                                                                                                  |
| **UAT-VW-005** | Pass    | Items table with **T-Shirt** link; columns align with WC order details template.                                                                                                                                                                                                                                                                                                                                                      |
| **UAT-VW-006** | Pass    | Subtotal / total section present (values match WC for #416 in session).                                                                                                                                                                                                                                                                                                                                                               |
| **UAT-VW-007** | Pass    | Billing formatted block with phone/email text.                                                                                                                                                                                                                                                                                                                                                                                        |
| **UAT-VW-008** | Fail    | Phone is plain text (`esc_html`), not `tel:` link.                                                                                                                                                                                                                                                                                                                                                                                    |
| **UAT-VW-009** | Fail    | Email is plain text, not `mailto:` link.                                                                                                                                                                                                                                                                                                                                                                                              |
| **UAT-VW-010** | Pass    | Shipping block present.                                                                                                                                                                                                                                                                                                                                                                                                               |
| **UAT-VW-011** | Fail    | Shipping phone plain text, not `tel:` (same template pattern as billing).                                                                                                                                                                                                                                                                                                                                                             |
| **UAT-VW-012** | Pass    | Notes with text, meta line, **Delete note** buttons.                                                                                                                                                                                                                                                                                                                                                                                  |
| **UAT-VW-013** | Pass    | Add-note controls mirror edit page structure.                                                                                                                                                                                                                                                                                                                                                                                         |
| **UAT-VW-014** | Partial | Delete note not executed (would remove real notes on **#416**).                                                                                                                                                                                                                                                                                                                                                                       |
| **UAT-VW-015** | Pass    | **Customer History** with **Total orders**, **Total revenue**, **Average order value** headings.                                                                                                                                                                                                                                                                                                                                      |
| **UAT-VW-016** | Partial | UI values not extracted as plain text in accessibility snapshot. **Cross-check:** `OrderHooks::get_customer_history()` uses WooCommerce **Analytics `CustomersQuery`** (paid-order semantics), not a raw `wc_get_orders` count. For user **#1**, a naive all-status CLI sum was **9 orders**, **$881.00** total, **~$97.89** AOV—**compare in WooCommerce Analytics → Customers** for the report customer row tied to order **#416**. |

---

## Customer history — analytic cross-check (CLI)

Command used (all order statuses, **not** identical to WC Analytics “paid” definition):

```bash
wp eval '$customer_id = 1;
$orders = wc_get_orders(array("customer" => $customer_id, "limit" => -1, "return" => "ids"));
$n = count($orders);
$total = 0;
foreach ($orders as $oid) { $o = wc_get_order($oid); if ($o) { $total += (float) $o->get_total(); } }
$avg = $n ? $total / $n : 0;
echo "count=$n total=$total avg=$avg\n";'
```

Output in this environment: **`count=9 total=881 avg=97.888888888889`**. Use Analytics UI for authoritative **UAT-VW-016** sign-off.

---

## Appendix — UAT checklist (verbatim IDs)

Copy/paste for stakeholder tick boxes; replace bracket text with **Pass / Fail / Partial** from tables above.

### Module 1

-   [ ] UAT-OL-001 — Page loads with heading "Orders", breadcrumb shows Dashboard > Orders > Orders, sidebar "Orders" item is active
-   [ ] UAT-OL-002 — Order table renders with correct columns in order: Checkbox, ORDER, STATUS, ORDER TOTAL, TOTAL ITEMS, CUSTOMER, BILLING PHONE, DATE, ACTION
-   [ ] UAT-OL-003 — ORDER column shows order numbers as clickable links (e.g. #416)
-   [ ] UAT-OL-004 — STATUS column shows correct colour-coded badges: "Pending Payment" (yellow), "Processing" (green), "Completed" (green), "Refunded" (purple)
-   [ ] UAT-OL-005 — ORDER TOTAL shows monetary value correctly; discounted totals show strikethrough on original value
-   [ ] UAT-OL-006 — BILLING PHONE shows phone number or "N/A" when absent
-   [ ] UAT-OL-007 — DATE column shows formatted date (e.g. Mar 28, 2026)
-   [ ] UAT-OL-008 — ACTION column three-dot menu on each row shows: **View**, **Edit**
-   [ ] UAT-OL-009 — Clicking **View** from row menu navigates to `/order-details/{id}/` with correct order data
-   [ ] UAT-OL-010 — Clicking **Edit** from row menu navigates to `/edit-order/{id}/` with correct order data pre-filled
-   [ ] UAT-OL-011 — Clicking ORDER number link navigates to the correct order details or edit page
-   [ ] UAT-OL-012 — **"Add Order"** button (top right, blue with + icon) navigates to `/storesuite-dashboard/add-new-order/`
-   [ ] UAT-OL-013 — Pagination text shows "Showing 1 to 10 of N" at bottom of table
-   [ ] UAT-OL-014 — Page number buttons and → (next) arrow are clickable; clicking page 2 loads next set of orders and highlights page 2 button

### Module 2

-   [ ] UAT-SR-001 — Search bar is visible with placeholder "Search Order" and a search type dropdown defaulting to "All"
-   [ ] UAT-SR-002 — Search type dropdown contains options: **Order ID**, **Customer Email**, **Customers**, **Products**, **All**
-   [ ] UAT-SR-003 — Search by **Order ID**: enter a valid order number → only that order returned
-   [ ] UAT-SR-004 — Search by **Customer Email**: enter a known email → matching orders returned
-   [ ] UAT-SR-005 — Search by **Customers**: enter a customer name → orders for that customer returned
-   [ ] UAT-SR-006 — Search by **Products**: enter a product name → orders containing that product returned
-   [ ] UAT-SR-007 — Search with type **All**: enter a term → results matched across all searchable fields
-   [ ] UAT-SR-008 — Searching a non-existent term shows empty/no-results state with appropriate message
-   [ ] UAT-SR-009 — Clearing the search input and clicking Search restores the full order list
-   [ ] UAT-SR-010 — **Search** button triggers the search; pressing Enter also triggers search

### Module 3

-   [ ] UAT-FL-001 — Clicking **"Filter"** button opens the "Filter Orders" slide-in panel from the right; X button closes it
-   [ ] UAT-FL-002 — Filter panel contains four filters: Order Status, Registered Customer, Sales Channel, Date
-   [ ] UAT-FL-003 — Order Status dropdown options: All Statuses, Pending payment, Processing, On hold, Completed, Cancelled, Refunded, Failed, Draft
-   [ ] UAT-FL-004 — Selecting "Processing" → Apply Filters → table shows only Processing orders
-   [ ] UAT-FL-005 — Registered Customer field is a searchable dropdown; typing a name shows matching registered customers
-   [ ] UAT-FL-006 — Selecting a customer → Apply Filters → only that customer's orders shown
-   [ ] UAT-FL-007 — Sales Channel dropdown options: All sales channels, Admin, Checkout, Point of Sale
-   [ ] UAT-FL-008 — Selecting "Checkout" → Apply Filters → only checkout orders shown
-   [ ] UAT-FL-009 — Date dropdown shows "All dates" plus month/year options (e.g. April 2026, March 2026, etc.)
-   [ ] UAT-FL-010 — Selecting a specific month → Apply Filters → only orders from that month shown
-   [ ] UAT-FL-011 — Combining multiple filters (e.g. Status = Completed + Date = March 2026) returns intersected results
-   [ ] UAT-FL-012 — Clicking **Reset** link resets all filters to defaults and restores full order list
-   [ ] UAT-FL-013 — Filter + Search together returns correctly intersected results

### Module 4

-   [ ] UAT-BA-001 — "Bulk actions" dropdown is visible at the top left of the table; default value is "Bulk actions"
-   [ ] UAT-BA-002 — Bulk actions dropdown contains options: Change status to processing, Change status to on-hold, Change status to completed, Change status to cancelled, Move to Trash
-   [ ] UAT-BA-003 — Header checkbox selects all visible rows; unchecking it deselects all
-   [ ] UAT-BA-004 — Individual row checkboxes can be checked/unchecked independently
-   [ ] UAT-BA-005 — Clicking **Apply** without selecting any rows shows appropriate error or no-op behaviour
-   [ ] UAT-BA-006 — Clicking **Apply** without selecting a bulk action shows appropriate error or no-op behaviour
-   [ ] UAT-BA-007 — Select 2+ orders → choose "Change status to completed" → Apply → all selected orders show "Completed" status badge
-   [ ] UAT-BA-008 — Select 2+ orders → choose "Change status to on-hold" → Apply → all selected orders show "On hold" status badge
-   [ ] UAT-BA-009 — Select 2+ orders → choose "Change status to cancelled" → Apply → all selected orders show "Cancelled" badge
-   [ ] UAT-BA-010 — Select 2+ orders → choose "Move to Trash" → Apply → confirmation prompt shown; on confirm, orders are removed from list; on cancel, orders remain
-   [ ] UAT-BA-011 — After bulk action, selection is cleared and table refreshes with updated statuses

### Module 5

-   [ ] UAT-AO-001 — Page loads with heading "Add New Order" in two-column layout: main form (left), General/Notes sidebar (right); all fields empty
-   [ ] UAT-AO-002 — "Search for a product…" field shows autocomplete suggestions when typing a product name
-   [ ] UAT-AO-003 — Selecting a product from autocomplete adds it to the products table with price, quantity (×1), and line total
-   [ ] UAT-AO-004 — Multiple products can be added; each appears as a separate row
-   [ ] UAT-AO-005 — Coupon code input accepts text (placeholder: "e.g. SUMMER20"); clicking **Apply Coupon** applies a valid coupon and updates totals
-   [ ] UAT-AO-006 — Applying an invalid/expired coupon shows an error notice
-   [ ] UAT-AO-007 — Fee amount input accepts a fixed amount or percentage (placeholder: "Enter a fixed amount or percentage"); clicking **Add Fee** adds it to totals
-   [ ] UAT-AO-008 — Shipping Name field defaults to "Shipping" (editable text input)
-   [ ] UAT-AO-009 — Shipping Cost is a numeric input (default: 0)
-   [ ] UAT-AO-010 — Shipping Method dropdown options: N/A, Flat rate, Free shipping, Local pickup, Local pickup (pickup_location), Other
-   [ ] UAT-AO-011 — Clicking **Add Shipping** adds the shipping line to the order totals
-   [ ] UAT-AO-012 — Billing Address section contains fields: First Name, Last Name, Company (optional), Address Line 1, Address Line 2 (optional), City, Postcode/ZIP, Country/Region (dropdown), State/County (dropdown), Email Address, Phone, Payment Method, Transaction ID (optional)
-   [ ] UAT-AO-013 — Country/Region dropdown populates State/County dropdown with correct states for the selected country
-   [ ] UAT-AO-014 — Shipping Address section contains fields: First Name, Last Name, Company (optional), Address Line 1, Address Line 2 (optional), City, Postcode/ZIP, Country/Region (dropdown), State/County (dropdown), Phone, Customer Provided Note (textarea, optional)
-   [ ] UAT-AO-015 — Customer field defaults to "Guest"; typing in the field shows autocomplete suggestions of registered customers; selecting one fills in customer data
-   [ ] UAT-AO-016 — Date Created field shows today's date and current time (hour + minute spinbuttons); date and time can be changed
-   [ ] UAT-AO-017 — Status dropdown options: Pending payment, Processing, On hold, Completed, Cancelled, Refunded, Failed, Draft; default is "Pending payment"
-   [ ] UAT-AO-018 — Order Actions dropdown options: Choose an action…, Send order details to customer, Resend new order notification, Regenerate download permissions
-   [ ] UAT-AO-019 — Order notes section shows "There are no notes yet." when no notes exist
-   [ ] UAT-AO-020 — Note textarea accepts text (placeholder: "Enter your note here…")
-   [ ] UAT-AO-021 — Note type dropdown options: Internal note, Note to customer; help text visible: "Add a note for your reference, or add a customer note (the user will be notified)."
-   [ ] UAT-AO-022 — Clicking **Add** saves the note; it appears in the Order notes section with timestamp and author
-   [ ] UAT-AO-023 — **"Create Order"** is a full-width blue button at the bottom of the General sidebar
-   [ ] UAT-AO-024 — Clicking Create Order with at least one product added → success notice shown → order appears in orders list with correct status badge and totals
-   [ ] UAT-AO-025 — Clicking Create Order with no products → validation error or empty order warning shown
-   [ ] UAT-AO-026 — Created order is assigned a unique order number (e.g. #420)

### Module 6

-   [ ] UAT-EO-001 — Page loads with heading "Edit Order" in two-column layout; all fields pre-filled with order #416 data
-   [ ] UAT-EO-002 — Primary button reads "**Update Order**" (not "Create Order")
-   [ ] UAT-EO-003 — Existing products shown in a table with columns: Item (name + SKU + thumbnail), Price, Qty (with × symbol), Total, and per-row Edit (pencil) and Delete (trash) icons
-   [ ] UAT-EO-004 — Clicking the pencil/edit icon on a product row allows inline editing of quantity and price; saving updates the line total
-   [ ] UAT-EO-005 — Clicking the trash/delete icon on a product row removes the item; order totals update accordingly
-   [ ] UAT-EO-006 — Additional products can be added via "Search for a product…" field; they appear as new rows
-   [ ] UAT-EO-007 — Order subtotal and total are displayed below the products table and update dynamically
-   [ ] UAT-EO-008 — **Recalculate** button (blue, right side) recalculates totals based on current items/shipping/fees
-   [ ] UAT-EO-009 — **Refund** button (left side) expands a refund section with: "Restock refunded items" checkbox, Refund amount input, Reason for refund (optional) text input
-   [ ] UAT-EO-010 — Coupon code input (placeholder: "e.g. SUMMER20") + **Apply Coupon** button; valid coupon applies discount and updates totals
-   [ ] UAT-EO-011 — Fee amount input (placeholder: "Enter a fixed amount or percentage") + **Add Fee** button; adds fee line to order totals
-   [ ] UAT-EO-012 — Shipping Name (text, default "Shipping"), Shipping Cost (number, default 0), Shipping Method (dropdown: N/A, Flat rate, Free shipping, Local pickup, Local pickup (pickup_location), Other) + **Add Shipping** button
-   [ ] UAT-EO-013 — Billing address shown as formatted display text; pencil edit icon (top right of section) switches to editable form
-   [ ] UAT-EO-014 — Billing address form fields (same 13 fields as Add New Order) are pre-filled with saved data; changes can be saved
-   [ ] UAT-EO-015 — Shipping address shown as formatted display text; pencil edit icon switches to editable form
-   [ ] UAT-EO-016 — Shipping address form fields (same 11 fields as Add New Order) are pre-filled; changes can be saved
-   [ ] UAT-EO-017 — Customer field shows selected customer with × to remove (e.g. "× admin (#1 – aminur@welabs.dev)"); clicking × clears and allows new customer selection
-   [ ] UAT-EO-018 — Date Created shows order's actual creation date and time; these can be updated
-   [ ] UAT-EO-019 — Status dropdown shows current status; changing it and saving updates the badge in the order list
-   [ ] UAT-EO-020 — Order Actions dropdown (Choose an action…, Send order details to customer, Resend new order notification, Regenerate download permissions) executes on **Update Order** save
-   [ ] UAT-EO-021 — Existing notes shown with: note text, timestamp ("added on [date] at [time] by [user]"), and red **"Delete note"** link
-   [ ] UAT-EO-022 — Clicking **Delete note** removes the note immediately
-   [ ] UAT-EO-023 — Adding a new internal note saves and appears with correct author/timestamp
-   [ ] UAT-EO-024 — Adding a note with type "Note to customer" saves and customer receives notification
-   [ ] UAT-EO-025 — Clicking **Update Order** saves all changes; success notice shown; order list reflects updated status/total
-   [ ] UAT-EO-026 — Updating status from "Pending payment" to "Completed" → order list badge updates to green "Completed"
-   [ ] UAT-EO-027 — No-op update (no changes made) → clicking Update Order shows success notice; data unchanged

### Module 7

-   [ ] UAT-VW-001 — Page loads with heading "Order #416", breadcrumb shows Dashboard > Orders > Order #416
-   [ ] UAT-VW-002 — Page is fully read-only — no editable form fields visible
-   [ ] UAT-VW-003 — **"Edit Order"** button (blue, pencil icon, top right) navigates to the edit page for the same order
-   [ ] UAT-VW-004 — **"Add Order"** button (blue, + icon, top right) navigates to `/add-new-order/`
-   [ ] UAT-VW-005 — Order items table shows columns: Item (name + SKU + thumbnail), Qty, Totals
-   [ ] UAT-VW-006 — Order totals section shows Subtotal and Total values correctly
-   [ ] UAT-VW-007 — Billing address displayed as formatted text block (name, address lines, city, country, phone, email)
-   [ ] UAT-VW-008 — Phone number in billing address is a clickable `tel:` link
-   [ ] UAT-VW-009 — Email in billing address is a clickable `mailto:` link
-   [ ] UAT-VW-010 — Shipping address displayed as formatted text block (name, address, phone)
-   [ ] UAT-VW-011 — Phone number in shipping address is a clickable `tel:` link
-   [ ] UAT-VW-012 — Order notes section shows all notes with note text, timestamp, and red **"Delete note"** link
-   [ ] UAT-VW-013 — Note type dropdown and Add Note textarea function the same as on the edit page
-   [ ] UAT-VW-014 — Clicking **Delete note** removes the note from the view
-   [ ] UAT-VW-015 — "Customer History" section is visible with three metrics: **Total orders**, **Total revenue**, **Average order value**
-   [ ] UAT-VW-016 — Metric values are accurate for the order's customer (cross-check with WP admin if needed)

---

**End of report.** For a merge-ready fix list, prioritize: **SR-005** (HPOS customer search), **`m` param rename**, **AO-025** validation, **VW-002** vs notes UX decision, **tel/mailto** links, **bulk trash confirm**, **refunded badge** styling, **checkbox hit targets** for bulk QA.
