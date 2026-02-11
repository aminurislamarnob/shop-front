### StoreSuite – User Acceptance Test (UAT)

## Overview

StoreSuite provides a **frontend store management dashboard** for WooCommerce shop managers to manage products, orders, categories, tags, brands, coupons, and see store performance KPIs.

---

## Preconditions

- **WordPress** installed and running.
- **WooCommerce** active and configured.
- **StoreSuite** plugin installed and activated.
- Pretty permalinks enabled (non-plain).
- Test users:
  - **Admin** (`administrator`).
  - **Shop Manager** (`shop_manager`).
  - **Customer** (`customer`).
- Browser cache cleared / private window for login/logout tests.

---

## Roles

- **Admin**: Full access to WP Admin, WooCommerce, and StoreSuite.
- **Shop Manager**: Frontend StoreSuite dashboard access, no WP Admin (when prevented).
- **Customer**: No access to StoreSuite dashboard.

---

## UAT Scenarios

### 1. Plugin installation & dependency handling

- **Preconditions**
  - WooCommerce **inactive**.
- **Steps**
  1. Activate `StoreSuite` from `Plugins → Installed Plugins`.
- **Expected**
  - Admin notice appears indicating StoreSuite requires WooCommerce.
  - StoreSuite remains inactive in effect (no dashboard page behavior).
- **Steps (with WooCommerce active)**
  1. Activate WooCommerce.
  2. Activate StoreSuite.
- **Expected**
  - No dependency error notices.
  - A “Settings” link appears under StoreSuite in the plugins list.
  - No PHP errors in logs.

---

### 2. Automatic “StoreSuite Dashboard” page

- **Preconditions**
  - StoreSuite just activated, WooCommerce active.
- **Steps**
  1. Go to `Pages → All Pages`.
  2. Locate page titled **“StoreSuite Dashboard”** with slug `storesuite-dashboard`.
  3. Edit the page and switch to Code/HTML view.
- **Expected**
  - Page status is **Published**.
  - Page content contains the shortcode `[storesuite_dashboard]` (wrapped by Gutenberg shortcode comments is acceptable).
  - Page is marked with the post state **“StoreSuite Dashboard Page”** in the page list.

---

### 3. Settings page under WooCommerce

- **Preconditions**
  - Logged in as **Admin**.
- **Steps**
  1. Navigate to `WooCommerce → StoreSuite`.
- **Expected**
  - A page with heading **“StoreSuite Settings”** is shown.
  - A React/Vue-style container `#storesuite-settings` renders without JS errors in the console.
  - No PHP/JS errors.

---

### 4. StoreSuite dashboard access control & redirects

#### 4.1 Unauthenticated user redirected to login

- **Preconditions**
  - “StoreSuite Dashboard” page exists.
  - Logged out.
- **Steps**
  1. Visit the public URL of the dashboard page (e.g. `/storesuite-dashboard/`).
- **Expected**
  - User is redirected to the WooCommerce **My Account** page.
  - After login, they are redirected to the configured dashboard / My Account per settings.

#### 4.2 Customer blocked from dashboard

- **Preconditions**
  - Logged in as **Customer**.
- **Steps**
  1. Visit `/storesuite-dashboard/`.
- **Expected**
  - Customer is redirected away (home or My Account) and **cannot** access dashboard content.
  - If accessing via shortcode directly, message like **“You have no permission to view this page”** is displayed.

#### 4.3 Shop Manager access

- **Preconditions**
  - Logged in as **Shop Manager** with `manage_woocommerce` capability.
- **Steps**
  1. Visit `/storesuite-dashboard/`.
- **Expected**
  - Dashboard loads successfully with navigation sidebar and main content.
  - No access denied messages.

---

### 5. Login redirects & admin access blocking

#### 5.1 Login redirect to dashboard

- **Preconditions**
  - Dashboard page option configured (dashboard page created by installer).
  - Logged out.
- **Steps**
  1. Log in from WooCommerce **My Account** page as a Shop Manager.
- **Expected**
  - After successful login, user is redirected to **StoreSuite dashboard** URL (not generic My Account).

#### 5.2 Admin area blocked for shop manager / customer

- **Preconditions**
  - Option `storesuite_prevent_admin_access` set to `yes` (via settings or DB).
  - Logged in as **Shop Manager** or **Customer**.
- **Steps**
  1. Directly visit `/wp-admin/`.
- **Expected**
  - Request is redirected to the site **home URL**.
  - Exceptions: `admin-ajax.php`, `admin-post.php`, uploads endpoints still work.
  - Admin bar is **hidden** on the frontend for that user.

---

### 6. My Account → “StoreSuite Dashboard” button

- **Preconditions**
  - Logged in as **Shop Manager** (has `manage_woocommerce`).
- **Steps**
  1. Visit WooCommerce **My Account** dashboard.
- **Expected**
  - A button/link “**StoreSuite Dashboard**” is visible.
  - Clicking it opens the StoreSuite dashboard page in the same tab.
  - Button styling matches the custom CSS (blue background, white text, hover styles).

---

### 7. Dashboard navigation & layout

- **Preconditions**
  - Logged in as **Shop Manager**.
  - On `/storesuite-dashboard/`.
- **Steps**
  1. Inspect the left navigation menu.
- **Expected**
  - Menu items exist (depending on configuration): **Dashboard, Products, Orders, Categories, Brands, Tags, Coupons, Visit Home, WP Dashboard, Logout**.
  - Each item points to the correct URL:
    - `Dashboard` → `/storesuite-dashboard/`
    - `Products` → `/storesuite-dashboard/products/`
    - `Orders` → `/storesuite-dashboard/orders/`
    - `Categories` → `/storesuite-dashboard/categories/`
    - `Brands` → `/storesuite-dashboard/brands/`
    - `Tags` → `/storesuite-dashboard/tags/`
    - `Coupons` → `/storesuite-dashboard/coupons/`
  - Clicking each menu item loads the corresponding section template without 404 or PHP errors.
  - The active menu item is visually highlighted.

---

### 8. Dashboard KPIs & leaderboards

- **Preconditions**
  - Sample orders, products, categories, coupons, downloads in WooCommerce.
  - Logged in as **Shop Manager**, on dashboard main view.
- **Steps**
  1. View **Store Performance** KPIs area.
  2. Adjust dashboard date range using the date picker (if present).
- **Expected**
  - KPIs such as **Total sales, Gross sales, Net sales, Orders, Avg order value, Products sold, Variations sold, Returns, Discounted orders, Net discount amount, Tax, Shipping, Downloads** display values matching WooCommerce Analytics for the same range.
  - Percentage change indicators (if shown) behave as:
    - `0%` when both periods are zero.
    - Not `NaN` or infinite when previous period is zero.
- **Steps**
  1. Scroll to **Top products – Items sold**, **Top categories – Items sold**, **Top customers – Total spend**, **Top coupons – Orders count** widgets.
- **Expected**
  - Each table lists at most the configured number of rows (default **5**).
  - Product/category/customer/coupon names and metrics match WooCommerce Analytics reports for the same date range.
  - If relevant WooCommerce Analytics classes are not available, widgets fail gracefully (empty or no data, but no fatal errors).

---

### 9. Products management (frontend)

- **Preconditions**
  - Logged in as **Shop Manager**, on `/storesuite-dashboard/products/`.
- **Steps**
  1. Verify product list: titles, prices, stock, status labels (`Online`, `Draft`, etc.).
  2. Use pagination and filters (if present).
  3. Click **Add New Product**.
  4. Fill in required fields: name, price, stock, description, product type (simple/virtual/downloadable).
  5. Save/publish.
- **Expected**
  - Product list matches WooCommerce products (status and type indicators rendered via `storesuite_get_product_type()` and `storesuite_get_post_status()`).
  - New product appears in both **WooCommerce admin products list** and StoreSuite products list.
  - Editing an existing product from the frontend updates the same product in WooCommerce.

---

### 10. Orders management (frontend)

- **Preconditions**
  - Existing WooCommerce orders.
  - Logged in as **Shop Manager**, on `/storesuite-dashboard/orders/`.
- **Steps**
  1. Verify orders table: order number, status, total, date.
  2. Confirm status labels and CSS classes correspond to WooCommerce statuses (pending, processing, completed, failed, refunded, etc.).
  3. Click **Add New Order**, create a simple order with at least one product.
  4. Save and then open **Edit Order** and **Order Details** views.
- **Expected**
  - Order list matches WooCommerce orders for that store.
  - Status badges/classes reflect mapping in `storesuite_get_order_status_class()`.
  - Newly created order is visible and editable in both StoreSuite and WooCommerce admin.
  - Order notes, customer history, downloads and attribution templates load without error where applicable.

---

### 11. Categories management

- **Preconditions**
  - Logged in as **Shop Manager**, on `/storesuite-dashboard/categories/`.
- **Steps**
  1. Verify existing categories are listed with correct names, slugs, product counts.
  2. Click **Add New Category**, enter name, slug, parent, description and save.
  3. Edit an existing category and change its name/description.
- **Expected**
  - Category list matches WooCommerce product categories.
  - Newly added category appears both in StoreSuite and WooCommerce admin.
  - Edited category reflects updated data everywhere.

---

### 12. Tags management

- **Preconditions**
  - Logged in as **Shop Manager**, on `/storesuite-dashboard/tags/`.
- **Steps**
  1. Verify existing tags are listed.
  2. Create a new tag via **Add New Tag**.
  3. Edit an existing tag.
- **Expected**
  - Tags lists and modifications synchronize with WooCommerce product tags.
  - No permission errors for shop managers.

---

### 13. Brands management

- **Preconditions**
  - Brand taxonomy/plugin configured if StoreSuite relies on one.
  - Logged in as **Shop Manager**, on `/storesuite-dashboard/brands/`.
- **Steps**
  1. Verify brands list shows existing brands.
  2. Add a new brand (name, slug, etc.).
  3. Edit an existing brand.
- **Expected**
  - Brands list and changes are reflected where brands are used (frontend/catalog if applicable).
  - No fatal errors even if brand taxonomy is empty initially.

---

### 14. Coupons management

- **Preconditions**
  - Logged in as **Shop Manager**, on `/storesuite-dashboard/coupons/`.
- **Steps**
  1. View coupons list (code, amount, usage, expiry).
  2. Click **Add New Coupon**, configure discount type, amount, usage limits, dates; save.
  3. Edit an existing coupon from the list.
- **Expected**
  - Coupons list matches WooCommerce coupons.
  - Newly created coupon works in cart/checkout.
  - Editing coupon via StoreSuite updates the WooCommerce coupon.

---

### 15. Logout and navigation helpers

- **Preconditions**
  - Logged in as **Shop Manager**, on the dashboard.
- **Steps**
  1. Click **Visit Home** menu item.
  2. Click **WP Dashboard** menu item.
  3. Click **Logout** menu item.
- **Expected**
  - Visit Home opens the site home URL.
  - WP Dashboard opens `/wp-admin/` (if allowed by the prevent-admin-access setting).
  - Logout logs user out and redirects to the appropriate page (e.g. home or login), and subsequent access to `/storesuite-dashboard/` behaves as in Scenario 4.1.

