## Overview

User Acceptance Testing checklist for the **Orders** module in the StoreSuite vendor dashboard.

**Pages covered:**

-   `/storesuite-dashboard/orders/` — Order list, search, filter, bulk action
-   `/storesuite-dashboard/add-new-order/` — Create order
-   `/storesuite-dashboard/edit-order/{id}/` — Edit order (tested with #416)
-   `/storesuite-dashboard/order-details/{id}/` — View order (tested with #416)

**Role under test:** Logged-in vendor/seller
**Prepared:** 2026-04-10

> Tick each box as **Pass ✅**, or comment with **Fail ❌** + repro steps.

---

## Module 1: Order List (`/orders/`)

### Page & Table

-   [ ] **UAT-OL-001** — Page loads with heading "Orders", breadcrumb shows Dashboard > Orders > Orders, sidebar "Orders" item is active
-   [ ] **UAT-OL-002** — Order table renders with correct columns in order: Checkbox, ORDER, STATUS, ORDER TOTAL, TOTAL ITEMS, CUSTOMER, BILLING PHONE, DATE, ACTION
-   [ ] **UAT-OL-003** — ORDER column shows order numbers as clickable links (e.g. #416)
-   [ ] **UAT-OL-004** — STATUS column shows correct colour-coded badges: "Pending Payment" (yellow), "Processing" (green), "Completed" (green), "Refunded" (purple)
-   [ ] **UAT-OL-005** — ORDER TOTAL shows monetary value correctly; discounted totals show strikethrough on original value
-   [ ] **UAT-OL-006** — BILLING PHONE shows phone number or "N/A" when absent
-   [ ] **UAT-OL-007** — DATE column shows formatted date (e.g. Mar 28, 2026)
-   [ ] **UAT-OL-008** — ACTION column three-dot menu on each row shows: **View**, **Edit**
-   [ ] **UAT-OL-009** — Clicking **View** from row menu navigates to `/order-details/{id}/` with correct order data
-   [ ] **UAT-OL-010** — Clicking **Edit** from row menu navigates to `/edit-order/{id}/` with correct order data pre-filled
-   [ ] **UAT-OL-011** — Clicking ORDER number link navigates to the correct order details or edit page
-   [ ] **UAT-OL-012** — **"Add Order"** button (top right, blue with + icon) navigates to `/storesuite-dashboard/add-new-order/`

### Pagination

-   [ ] **UAT-OL-013** — Pagination text shows "Showing 1 to 10 of N" at bottom of table
-   [ ] **UAT-OL-014** — Page number buttons and → (next) arrow are clickable; clicking page 2 loads next set of orders and highlights page 2 button

---

## Module 2: Search (`/orders/`)

-   [ ] **UAT-SR-001** — Search bar is visible with placeholder "Search Order" and a search type dropdown defaulting to "All"
-   [ ] **UAT-SR-002** — Search type dropdown contains options: **Order ID**, **Customer Email**, **Customers**, **Products**, **All**
-   [ ] **UAT-SR-003** — Search by **Order ID**: enter a valid order number → only that order returned
-   [ ] **UAT-SR-004** — Search by **Customer Email**: enter a known email → matching orders returned
-   [ ] **UAT-SR-005** — Search by **Customers**: enter a customer name → orders for that customer returned
-   [ ] **UAT-SR-006** — Search by **Products**: enter a product name → orders containing that product returned
-   [ ] **UAT-SR-007** — Search with type **All**: enter a term → results matched across all searchable fields
-   [ ] **UAT-SR-008** — Searching a non-existent term shows empty/no-results state with appropriate message
-   [ ] **UAT-SR-009** — Clearing the search input and clicking Search restores the full order list
-   [ ] **UAT-SR-010** — **Search** button triggers the search; pressing Enter also triggers search

---

## Module 3: Filter (`/orders/`)

-   [ ] **UAT-FL-001** — Clicking **"Filter"** button (top right, blue with filter icon) opens the "Filter Orders" slide-in panel from the right; X button closes it
-   [ ] **UAT-FL-002** — Filter panel contains four filters: Order Status, Registered Customer, Sales Channel, Date

### Order Status Filter

-   [ ] **UAT-FL-003** — Order Status dropdown options: All Statuses, Pending payment, Processing, On hold, Completed, Cancelled, Refunded, Failed, Draft
-   [ ] **UAT-FL-004** — Selecting "Processing" → Apply Filters → table shows only Processing orders

### Registered Customer Filter

-   [ ] **UAT-FL-005** — Registered Customer field is a searchable dropdown; typing a name shows matching registered customers
-   [ ] **UAT-FL-006** — Selecting a customer → Apply Filters → only that customer's orders shown

### Sales Channel Filter

-   [ ] **UAT-FL-007** — Sales Channel dropdown options: All sales channels, Admin, Checkout, Point of Sale
-   [ ] **UAT-FL-008** — Selecting "Checkout" → Apply Filters → only checkout orders shown

### Date Filter

-   [ ] **UAT-FL-009** — Date dropdown shows "All dates" plus month/year options (e.g. April 2026, March 2026, etc.)
-   [ ] **UAT-FL-010** — Selecting a specific month → Apply Filters → only orders from that month shown

### Combined & Reset

-   [ ] **UAT-FL-011** — Combining multiple filters (e.g. Status = Completed + Date = March 2026) returns intersected results
-   [ ] **UAT-FL-012** — Clicking **Reset** link resets all filters to defaults and restores full order list
-   [ ] **UAT-FL-013** — Filter + Search together returns correctly intersected results

---

## Module 4: Bulk Actions (`/orders/`)

-   [ ] **UAT-BA-001** — "Bulk actions" dropdown is visible at the top left of the table; default value is "Bulk actions"
-   [ ] **UAT-BA-002** — Bulk actions dropdown contains options: Change status to processing, Change status to on-hold, Change status to completed, Change status to cancelled, Move to Trash
-   [ ] **UAT-BA-003** — Header checkbox selects all visible rows; unchecking it deselects all
-   [ ] **UAT-BA-004** — Individual row checkboxes can be checked/unchecked independently
-   [ ] **UAT-BA-005** — Clicking **Apply** without selecting any rows shows appropriate error or no-op behaviour
-   [ ] **UAT-BA-006** — Clicking **Apply** without selecting a bulk action shows appropriate error or no-op behaviour
-   [ ] **UAT-BA-007** — Select 2+ orders → choose "Change status to completed" → Apply → all selected orders show "Completed" status badge
-   [ ] **UAT-BA-008** — Select 2+ orders → choose "Change status to on-hold" → Apply → all selected orders show "On hold" status badge
-   [ ] **UAT-BA-009** — Select 2+ orders → choose "Change status to cancelled" → Apply → all selected orders show "Cancelled" badge
-   [ ] **UAT-BA-010** — Select 2+ orders → choose "Move to Trash" → Apply → confirmation prompt shown; on confirm, orders are removed from list; on cancel, orders remain
-   [ ] **UAT-BA-011** — After bulk action, selection is cleared and table refreshes with updated statuses

---

## Module 5: Add New Order (`/add-new-order/`)

### Page & Layout

-   [ ] **UAT-AO-001** — Page loads with heading "Add New Order" in two-column layout: main form (left), General/Notes sidebar (right); all fields empty

### Products Section

-   [ ] **UAT-AO-002** — "Search for a product…" field shows autocomplete suggestions when typing a product name
-   [ ] **UAT-AO-003** — Selecting a product from autocomplete adds it to the products table with price, quantity (×1), and line total
-   [ ] **UAT-AO-004** — Multiple products can be added; each appears as a separate row

### Discounts & Fees Section

-   [ ] **UAT-AO-005** — Coupon code input accepts text (placeholder: "e.g. SUMMER20"); clicking **Apply Coupon** applies a valid coupon and updates totals
-   [ ] **UAT-AO-006** — Applying an invalid/expired coupon shows an error notice
-   [ ] **UAT-AO-007** — Fee amount input accepts a fixed amount or percentage (placeholder: "Enter a fixed amount or percentage"); clicking **Add Fee** adds it to totals

### Shipping Section

-   [ ] **UAT-AO-008** — Shipping Name field defaults to "Shipping" (editable text input)
-   [ ] **UAT-AO-009** — Shipping Cost is a numeric input (default: 0)
-   [ ] **UAT-AO-010** — Shipping Method dropdown options: N/A, Flat rate, Free shipping, Local pickup, Local pickup (pickup_location), Other
-   [ ] **UAT-AO-011** — Clicking **Add Shipping** adds the shipping line to the order totals

### Billing Address Section

-   [ ] **UAT-AO-012** — Billing Address section contains fields: First Name, Last Name, Company (optional), Address Line 1, Address Line 2 (optional), City, Postcode/ZIP, Country/Region (dropdown), State/County (dropdown), Email Address, Phone, Payment Method, Transaction ID (optional)
-   [ ] **UAT-AO-013** — Country/Region dropdown populates State/County dropdown with correct states for the selected country

### Shipping Address Section

-   [ ] **UAT-AO-014** — Shipping Address section contains fields: First Name, Last Name, Company (optional), Address Line 1, Address Line 2 (optional), City, Postcode/ZIP, Country/Region (dropdown), State/County (dropdown), Phone, Customer Provided Note (textarea, optional)

### General Sidebar

-   [ ] **UAT-AO-015** — Customer field defaults to "Guest"; typing in the field shows autocomplete suggestions of registered customers; selecting one fills in customer data
-   [ ] **UAT-AO-016** — Date Created field shows today's date and current time (hour + minute spinbuttons); date and time can be changed
-   [ ] **UAT-AO-017** — Status dropdown options: Pending payment, Processing, On hold, Completed, Cancelled, Refunded, Failed, Draft; default is "Pending payment"
-   [ ] **UAT-AO-018** — Order Actions dropdown options: Choose an action…, Send order details to customer, Resend new order notification, Regenerate download permissions

### Order Notes Sidebar

-   [ ] **UAT-AO-019** — Order notes section shows "There are no notes yet." when no notes exist
-   [ ] **UAT-AO-020** — Note textarea accepts text (placeholder: "Enter your note here…")
-   [ ] **UAT-AO-021** — Note type dropdown options: Internal note, Note to customer; help text visible: "Add a note for your reference, or add a customer note (the user will be notified)."
-   [ ] **UAT-AO-022** — Clicking **Add** saves the note; it appears in the Order notes section with timestamp and author

### Create Order

-   [ ] **UAT-AO-023** — **"Create Order"** is a full-width blue button at the bottom of the General sidebar
-   [ ] **UAT-AO-024** — Clicking Create Order with at least one product added → success notice shown → order appears in orders list with correct status badge and totals
-   [ ] **UAT-AO-025** — Clicking Create Order with no products → validation error or empty order warning shown
-   [ ] **UAT-AO-026** — Created order is assigned a unique order number (e.g. #420)

---

## Module 6: Edit Order (`/edit-order/{id}/`)

### Page & Layout

-   [ ] **UAT-EO-001** — Page loads with heading "Edit Order" in two-column layout; all fields pre-filled with order #416 data
-   [ ] **UAT-EO-002** — Primary button reads "**Update Order**" (not "Create Order")

### Products Section

-   [ ] **UAT-EO-003** — Existing products shown in a table with columns: Item (name + SKU + thumbnail), Price, Qty (with × symbol), Total, and per-row Edit (pencil) and Delete (trash) icons
-   [ ] **UAT-EO-004** — Clicking the pencil/edit icon on a product row allows inline editing of quantity and price; saving updates the line total
-   [ ] **UAT-EO-005** — Clicking the trash/delete icon on a product row removes the item; order totals update accordingly
-   [ ] **UAT-EO-006** — Additional products can be added via "Search for a product…" field; they appear as new rows
-   [ ] **UAT-EO-007** — Order subtotal and total are displayed below the products table and update dynamically
-   [ ] **UAT-EO-008** — **Recalculate** button (blue, right side) recalculates totals based on current items/shipping/fees
-   [ ] **UAT-EO-009** — **Refund** button (left side) expands a refund section with: "Restock refunded items" checkbox, Refund amount input, Reason for refund (optional) text input

### Discounts & Fees Section

-   [ ] **UAT-EO-010** — Coupon code input (placeholder: "e.g. SUMMER20") + **Apply Coupon** button; valid coupon applies discount and updates totals
-   [ ] **UAT-EO-011** — Fee amount input (placeholder: "Enter a fixed amount or percentage") + **Add Fee** button; adds fee line to order totals

### Shipping Section

-   [ ] **UAT-EO-012** — Shipping Name (text, default "Shipping"), Shipping Cost (number, default 0), Shipping Method (dropdown: N/A, Flat rate, Free shipping, Local pickup, Local pickup (pickup_location), Other) + **Add Shipping** button

### Billing Address Section

-   [ ] **UAT-EO-013** — Billing address shown as formatted display text; pencil edit icon (top right of section) switches to editable form
-   [ ] **UAT-EO-014** — Billing address form fields (same 13 fields as Add New Order) are pre-filled with saved data; changes can be saved

### Shipping Address Section

-   [ ] **UAT-EO-015** — Shipping address shown as formatted display text; pencil edit icon switches to editable form
-   [ ] **UAT-EO-016** — Shipping address form fields (same 11 fields as Add New Order) are pre-filled; changes can be saved

### General Sidebar

-   [ ] **UAT-EO-017** — Customer field shows selected customer with × to remove (e.g. "× admin (#1 – aminur@welabs.dev)"); clicking × clears and allows new customer selection
-   [ ] **UAT-EO-018** — Date Created shows order's actual creation date and time; these can be updated
-   [ ] **UAT-EO-019** — Status dropdown shows current status; changing it and saving updates the badge in the order list
-   [ ] **UAT-EO-020** — Order Actions dropdown (Choose an action…, Send order details to customer, Resend new order notification, Regenerate download permissions) executes on **Update Order** save

### Order Notes Sidebar

-   [ ] **UAT-EO-021** — Existing notes shown with: note text, timestamp ("added on [date] at [time] by [user]"), and red **"Delete note"** link
-   [ ] **UAT-EO-022** — Clicking **Delete note** removes the note immediately
-   [ ] **UAT-EO-023** — Adding a new internal note saves and appears with correct author/timestamp
-   [ ] **UAT-EO-024** — Adding a note with type "Note to customer" saves and customer receives notification

### Update Order

-   [ ] **UAT-EO-025** — Clicking **Update Order** saves all changes; success notice shown; order list reflects updated status/total
-   [ ] **UAT-EO-026** — Updating status from "Pending payment" to "Completed" → order list badge updates to green "Completed"
-   [ ] **UAT-EO-027** — No-op update (no changes made) → clicking Update Order shows success notice; data unchanged

---

## Module 7: Order Details / View (`/order-details/{id}/`)

### Page & Layout

-   [ ] **UAT-VW-001** — Page loads with heading "Order #416", breadcrumb shows Dashboard > Orders > Order #416
-   [ ] **UAT-VW-002** — Page is fully read-only — no editable form fields visible
-   [ ] **UAT-VW-003** — **"Edit Order"** button (blue, pencil icon, top right) navigates to the edit page for the same order
-   [ ] **UAT-VW-004** — **"Add Order"** button (blue, + icon, top right) navigates to `/add-new-order/`

### Order Items Table

-   [ ] **UAT-VW-005** — Order items table shows columns: Item (name + SKU + thumbnail), Qty, Totals
-   [ ] **UAT-VW-006** — Order totals section shows Subtotal and Total values correctly

### Billing & Shipping Addresses

-   [ ] **UAT-VW-007** — Billing address displayed as formatted text block (name, address lines, city, country, phone, email)
-   [ ] **UAT-VW-008** — Phone number in billing address is a clickable `tel:` link
-   [ ] **UAT-VW-009** — Email in billing address is a clickable `mailto:` link
-   [ ] **UAT-VW-010** — Shipping address displayed as formatted text block (name, address, phone)
-   [ ] **UAT-VW-011** — Phone number in shipping address is a clickable `tel:` link

### Order Notes

-   [ ] **UAT-VW-012** — Order notes section shows all notes with note text, timestamp, and red **"Delete note"** link
-   [ ] **UAT-VW-013** — Note type dropdown and Add Note textarea function the same as on the edit page
-   [ ] **UAT-VW-014** — Clicking **Delete note** removes the note from the view

### Customer History

-   [ ] **UAT-VW-015** — "Customer History" section is visible with three metrics: **Total orders**, **Total revenue**, **Average order value**
-   [ ] **UAT-VW-016** — Metric values are accurate for the order's customer (cross-check with WP admin if needed)

---

## Exit Criteria

The following must **all pass** before QA sign-off:

| ID         | Scenario                                  | Priority    |
| ---------- | ----------------------------------------- | ----------- |
| UAT-OL-001 | Order list loads with correct columns     | 🔴 Critical |
| UAT-OL-008 | Row actions (View, Edit) work             | 🔴 Critical |
| UAT-OL-013 | Pagination works correctly                | 🟠 High     |
| UAT-SR-003 | Search by Order ID works                  | 🔴 Critical |
| UAT-SR-004 | Search by Customer Email works            | 🔴 Critical |
| UAT-SR-008 | No-results state shown                    | 🟡 Medium   |
| UAT-FL-004 | Filter by Order Status works              | 🔴 Critical |
| UAT-FL-012 | Reset filters restores full list          | 🟠 High     |
| UAT-BA-007 | Bulk status change works                  | 🔴 Critical |
| UAT-BA-010 | Bulk Move to Trash with confirmation      | 🔴 Critical |
| UAT-AO-024 | New order created with success notice     | 🔴 Critical |
| UAT-AO-025 | Empty order blocked with validation       | 🟠 High     |
| UAT-EO-004 | Edit product row inline (qty/price)       | 🔴 Critical |
| UAT-EO-019 | Status change saved and reflected in list | 🔴 Critical |
| UAT-EO-025 | Update Order saves all changes            | 🔴 Critical |
| UAT-VW-001 | Order details page loads correctly        | 🔴 Critical |
| UAT-VW-002 | Details page is fully read-only           | 🟠 High     |
| UAT-VW-015 | Customer History metrics visible          | 🟡 Medium   |
