=== StoreSuite - A frontend dashboard to manage your WooCommerce store ===
Contributors: aminurislam01
Tags: woocommerce, store management, shop manager, store suite, front-end shop manager
Requires at least: 6.4
Tested up to: 6.9
Requires PHP: 8.1
Stable tag: 1.0.3
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Manage your WooCommerce store easily from a front-end dashboard.

== Description ==

StoreSuite adds a frontend store management dashboard for WooCommerce. Give store managers one place to run the day-to-day: products, categories, tags, brands, coupons, orders, and key metrics—without switching between multiple admin screens.

= Key Features =

* **Manage WooCommerce store easily** – Single dashboard for store operations
* **Products** – List, add, and edit WooCommerce simple products
* **Products** – Variable product management (Comming soon)
* **Categories** – Manage product categories (add, edit, list)
* **Tags** – Manage product tags (add, edit, list)
* **Brands** – Manage product brands
* **Coupons** – Create, edit, and manage WooCommerce coupons
* **Orders** – View, create, and edit orders with customer and item details
* **Dashboard quick analytics** – Store performance KPIs and leaderboards:
  * Total sales, gross sales, net sales
  * Orders count and average order value
  * Products sold and variations sold
  * Returns, discounted orders, net discount amount
  * Tax and shipping totals, download counts
  * Top products by items sold
  * Top categories by items sold
  * Top customers by total spend
  * Top coupons by order count

= How It Works =

1. Install and activate StoreSuite (WooCommerce must be active).
2. Create a page and add the shortcode `[storesuite_dashboard]`.
3. In **WooCommerce → StoreSuite** settings, select that page as the dashboard page and save.
4. Visit the dashboard page when logged in with `manage_woocommerce` capability to manage your store.
5. StoreSuite is compatible with WooCommerce High-Performance Order Storage (HPOS).

== Installation ==

= Standard Installation =
1. Install and activate WooCommerce if you have not already.
2. In your WordPress admin, go to **Plugins → Add New**, search for "StoreSuite", and click **Install Now**, then **Activate**.
3. Alternatively, download the plugin zip and upload it to `/wp-content/plugins/` and activate from **Plugins**.
4. Go to **Settings → Permalinks** and click **Save Changes** to flush rewrite rules.
5. Create a new page (e.g. "Store Dashboard"), add the shortcode `[storesuite_dashboard]`, and publish.
6. Go to **WooCommerce → StoreSuite** in the admin menu.
7. In **Select Dashboard Page**, choose the page you created and click **Save Changes**.
8. Visit that page when logged in as a user with store management permissions to use the dashboard.

= Permalinks =

After installing or reactivating StoreSuite, go to **Settings → Permalinks** and click **Save Changes** so dashboard URLs work correctly.

== Screenshots ==

1. StoreSuite General Settings
2. StoreSuite Color Settings
3. StoreSuite Paginations Settings
4. Frontend Dashboard
5. Products List
6. Products Filter
7. Add New Product
8. Edit Product
9. Orders List
10. Orders Filter
11. Orders Details View
12. Edit Order
13. Add New Order
14. Product Categories List
15. Add New Product Category
16. Edit Product Category
17. Product Brands List
18. Add New Product Brand
19. Edit Product Brand
20. Product Tags List
21. Add New Product Tag
22. Edit Product Tag
23. Coupons List
24. Add New Coupon
25. Edit Coupon
26. Account Settings

== Frequently Asked Questions ==

= Do I need WooCommerce? =

Yes. StoreSuite requires WooCommerce to be installed and activated.

= Who can access the StoreSuite dashboard? =

Users with the `manage_woocommerce` capability (e.g. Administrators and Shop Managers) can access the dashboard when they visit the page that contains the `[storesuite_dashboard]` shortcode.

= Is it mandatory to update permalinks after installing the plugin? =

Yes. Go to **Settings → Permalinks** and click **Save Changes** after activation so dashboard routes work properly.

= How do I set the dashboard page? =

Create a page, add the shortcode `[storesuite_dashboard]`, then go to **WooCommerce → StoreSuite** and select that page in **Select Dashboard Page** and save.

= Does StoreSuite work with WooCommerce HPOS? =

Yes. StoreSuite declares compatibility with WooCommerce High-Performance Order Storage (HPOS).

== Changelog ==

= 1.0.3 =
* Minor update on admin settings UI.

= 1.0.2 =
* Redesign and optimize React admin settings UI.
* Add predefined/custom color palette system with live preview.
* Improve dashboard and general settings controls and save flow.

= 1.0.1 =
* Reorder My Account Dropdown Sub menus.

= 1.0.0 =
* Initial release.
* Dashboard with store performance KPIs and analytics.
* Products management.
* Categories, tags, and brands management.
* Coupons management.
* Orders: list, view, create, edit feature.
* Shortcode: [storesuite_dashboard].
* WooCommerce HPOS compatible.
* Collapsible dashboard sidebar: header control toggles expanded vs icon-only rail; menu labels on hover/focus; preference stored in the browser; full sidebar layout on smaller viewports.
* General settings: optional sidebar logo (expanded) and sidebar icon (collapsed), chosen from the media library; outputs in the dashboard sidebar with updated styling.
* Accessibility: navigation landmark on the sidebar, toggle semantics (role, aria-expanded, aria-controls), and screen-reader-friendly site title when a custom logo is shown.
