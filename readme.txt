=== StoreSuite – Frontend Shop Manager for WooCommerce with AI – Product, Order, Coupon Management & Analytics Dashboard ===
Contributors: aminurislam01
Tags: woocommerce frontend dashboard, woocommerce order management, woocommerce product management, shop manager, woocommerce ai
Requires at least: 6.9
Tested up to: 7.0
Requires PHP: 8.1
Stable tag: 1.2.1
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

AI-assisted frontend dashboard to manage your WooCommerce store — products, orders, coupons, categories, and analytics in one place.

== Description ==

StoreSuite adds a frontend store management dashboard for WooCommerce. Give store managers one place to run the day-to-day: products, categories, tags, brands, coupons, orders, and key metrics—without switching between multiple admin screens.

= 💎 Premium-grade features, completely free =

StoreSuite includes capabilities that other store-management dashboards often lock behind a Pro plan — at no cost:

* 🤖 AI product content, image, and all-in-one generation
* 🧩 Full variable products, attributes, and variations (with generate-all-variations)
* 📊 Real-time analytics, KPIs, and leaderboards
* 📈 A full Analytics suite with WooCommerce-admin report parity (Revenue, Orders, Products, and more)
* 📱 Fully responsive on mobile, tablet, and desktop
* 📤 CSV product export

No feature gates. No trial limits. No upsell.

= 🚀 Key Features =

* 🖥️ **Manage WooCommerce store easily** – Single dashboard for store operations
* 🤖 **AI content generation** – Draft product titles and descriptions with regenerate and history
* 🪄 **AI all-in-one generator** – One hint drafts the title, short, and long description together
* 🎨 **AI image generation** – Create featured and gallery images from a text prompt
* 🧠 **AI settings** – Toggle AI per field and set custom instructions
* 📦 **Products** – List, add, and edit all WooCommerce product types: simple, variable, grouped, and external/affiliate
* 🧩 **Variable products** – Manage attributes and variations with bulk actions and generate-all-variations
* 📋 **Product attributes** – Manage attributes and their terms (add, edit, list)
* 📤 **CSV export** – Export product lists to CSV for reporting, accounting, or migration, with a configurable export modal
* 🗂️ **Categories** – Manage product categories (add, edit, list)
* 🏷️ **Tags** – Manage product tags (add, edit, list)
* 🏢 **Brands** – Manage product brands
* 🎟️ **Coupons** – Create, edit, and manage WooCommerce coupons
* 🛒 **Orders** – View, create, and edit orders with customer and item details
* 📊 **Real-time dashboard analytics** – Sales KPIs, net sales chart, top products, recent orders, and quick actions
* 📈 **Analytics reports** – A full analytics suite with WooCommerce-admin parity: Revenue, Orders, Products, Variations, Categories, Coupons, Taxes, Downloads, Stock, and Customers reports, each with summary KPIs, an interactive line/bar chart, sortable and paginated data tables, advanced filters, date-range comparison, and CSV export
* 📱 **Fully responsive** – The entire frontend dashboard and analytics reports adapt to mobile and desktop screens with an off-canvas sidebar, stacked cards, and touch-friendly controls
* 🔑 **Bring your own AI** – Built on the WordPress 7.0 central AI connector; generation runs on the provider and keys you configure in WordPress
* ⚡ **Built for WooCommerce HPOS** – Fully compatible with High-Performance Order Storage

= How It Works =

1. Install and activate StoreSuite (WooCommerce must be active).
2. Create a page and add the shortcode `[storesuite_dashboard]`. Normally, on plugin activation StoreSuite creates this dashboard page with the required shortcode and auto-selects it on the StoreSuite settings dashboard page for you.
3. In **WooCommerce → StoreSuite → General** settings, if the dashboard page is not already auto-selected, select that page as the dashboard page and save.
4. Visit the dashboard page when logged in with `manage_woocommerce` capability to manage your store.
5. StoreSuite is compatible with WooCommerce High-Performance Order Storage (HPOS).

== Installation ==

= Standard Installation =
1. Install and activate WooCommerce if you have not already.
2. In your WordPress admin, go to **Plugins → Add New**, search for "StoreSuite", and click **Install Now**, then **Activate**.
3. Alternatively, download the plugin zip and upload it to `/wp-content/plugins/` and activate from **Plugins**.
4. StoreSuite flushes rewrite rules automatically on activation. If dashboard URLs return 404s, go to **Settings → Permalinks** and click **Save Changes** to refresh them.
5. On activation, StoreSuite normally creates the dashboard page with the `[storesuite_dashboard]` shortcode automatically. If it was not created, add a new page (e.g. "Store Dashboard"), insert the shortcode `[storesuite_dashboard]`, and publish.
6. Go to **WooCommerce → StoreSuite → General** in the admin menu.
7. In **Select Dashboard Page**, if the dashboard page is not already auto-selected, choose the page and click **Save Changes**.
8. Visit that page when logged in as a user with store management permissions to use the dashboard.

= Permalinks =

Normally StoreSuite flushes rewrite rules automatically on activation and update. If dashboard URLs return 404s, go to **Settings → Permalinks** and click **Save Changes** so the routes refresh and dashboard URLs work correctly.

== Screenshots ==

1. General Settings
2. Predefined/Custom Color Palette Settings
3. Paginations Settings
4. AI Settings
5. Frontend Dashboard
6. Products List
7. Add New Product
8. Edit Simple Product
9. Edit Variable Product
10. Product Categories List
11. Add New Product Category & Same UI for Edit Category
12. Product Brands List
13. Add New Product Brand & Same UI for Edit Brand
14. Product Tags List
15. Add New Product Tag & Same UI for Edit Tag
16. Product Attributes List
17. Attribute Terms List with Add/Edit terms
18. Orders List
19. Orders Details View
20. Add New Order
21. Edit Order
22. Coupons List
23. Add New Coupon & Same UI for Edit Coupon
24. Account Settings Profile Tab
25. Account Settings Address Tab
26. Account Settings Password Tab


== Frequently Asked Questions ==

= Do I need WooCommerce? =

Yes. StoreSuite requires WooCommerce to be installed and activated.

= Who can access the StoreSuite dashboard? =

Users with the `manage_woocommerce` capability (e.g. Administrators and Shop Managers) can access the dashboard when they visit the page that contains the `[storesuite_dashboard]` shortcode.

= Is it mandatory to update permalinks after installing the plugin? =

No. StoreSuite flushes rewrite rules automatically on activation and update. Only if dashboard URLs return 404s, go to **Settings → Permalinks** and click **Save Changes** so the routes refresh.

= How do I set the dashboard page? =

Create a page, add the shortcode `[storesuite_dashboard]`, then go to **WooCommerce → StoreSuite** and select that page in **Select Dashboard Page** and save.

= Does StoreSuite work with WooCommerce HPOS? =

Yes. StoreSuite declares compatibility with WooCommerce High-Performance Order Storage (HPOS).

== Changelog ==

= 1.2.1 =
* Add a Changelog page to the admin settings app, paginated with a "Load more" button.
* Fix collapsed selectWoo fields on the order add/edit form.

= 1.2.0 =
* Add a full **Analytics** suite with WooCommerce-admin report parity: Revenue, Orders, Products, Variations, Categories, Coupons, Taxes, Downloads, Stock, and Customers reports.
* Each report includes summary KPIs, an interactive chart (line/bar; by day, week, month, quarter, or year), sortable and paginated data tables, and CSV export.
* Add date-range comparison (previous period / previous year) and per-report advanced filters (e.g. product, category, coupon, order status, customer type) for WooCommerce Analytics parity.
* Add a "Data status" import bar and re-skin every report to match the StoreSuite dashboard design language.
* Make the Analytics reports fully responsive on mobile: the report header stacks, summary stats collapse to one per row, charts align to the page gutter, and the table actions row wraps so nothing is clipped.
* Link analytics order numbers to the StoreSuite order-details page and rewrite WooCommerce admin URLs to their frontend dashboard equivalents.

= 1.1.4 =
* Make the entire frontend dashboard responsive for mobile and tablet viewports.
* Off-canvas sidebar on mobile with overlay; all list tables collapse to stacked label/value cards; tables scroll horizontally on tablet.
* Dashboard home: full-width filters, stacked analytics tables, 2-column quick-actions grid.
* Product, order, coupon, category, tag, brand, and attribute pages: toolbar actions, forms, and buttons adapt to available width on every screen size.
* On mobile, the primary Add button moves beside the page title; Export/Filter/Search toolbar buttons become compact icon-only buttons.
* Shorten list page titles: "Product Categories" → "Categories", "Product Tags" → "Tags", "Product Brands" → "Brands", "Product Attributes" → "Attributes".
* Add `storesuite_categories_toolbar_add_button`, `storesuite_tags_toolbar_add_button`, `storesuite_brands_toolbar_add_button`, and `storesuite_attributes_toolbar_add_button` action hooks for customising the Add button in each list toolbar.

= 1.1.3 =
* Fix the Brands and Categories lists showing stale data on the frontend dashboard after brands or categories were added, edited, or deleted outside StoreSuite — for example from the WordPress admin, the WooCommerce REST API, WP-CLI, or a product import. The cached list now refreshes immediately regardless of where the change is made.

= 1.1.2 =
* Add AI product generation: per-field "Generate with AI" buttons for the product title, short description, and long description, with an editable suggestion modal, regenerate, and a history pager to step through suggestions.
* Add an all-in-one "Generate with AI" generator on the Add New and Edit Product pages: enter one hint to draft the title, short description, and long description together, each with its own regenerate and history pager.
* Add AI product image generation for the featured image and gallery images: describe the image, preview it, regenerate, and insert it straight into the media library.
* Add an AI settings page to enable or disable generation per field and to set custom system instructions for each text field and for images.
* Prefix the product slug field with the live permalink base (e.g. https://example.com/product/) on the Add New and Edit Product pages.
* Add a link beside the Edit Product page title that opens the live product page in a new tab.
* Reset the product image and gallery previews after a product is successfully added.
* AI generation features require WordPress 7.0 or later (built on the WordPress core AI Client) and are hidden automatically when unavailable.

= 1.1.1 =
* Prevent browsers from autofilling the account password fields on load.
* Remove the required "*" and "(optional)" markers from the account address fields.

= 1.1.0 =
* Add variable product management: attributes UI, variation CRUD, bulk actions, and generate-all-variations on the frontend product form.
* Add product attributes management pages (list, add, edit, and attribute terms).
* Add product CSV export with a configurable export modal.
* Flush rewrite rules automatically on update so the new attribute endpoints resolve without re-saving permalinks.

= 1.0.6 =
* Rewrite dashboard analytics in React with net sales chart, recent orders, quick actions, and leaderboards.
* Nest Categories, Brands, and Tags under the Products menu with a collapsed-sidebar flyout.
* Improve Lighthouse scores via deferred rendering, reserved widget heights, and per-endpoint asset loading.
* Harden analytics REST permissions and cache preload payloads per user capability.
* Patch dependency vulnerabilities and move build toolchain to Node 22.
* Append order number / coupon ID to the Edit Order and Edit Coupon page titles.
* Link list-table names (products, categories, brands, tags, coupons) to their StoreSuite edit page.
* Keep the Products parent menu active on edit Category / Brand / Tag pages.
* Reactively toggle the order Products section based on the saved order's editable state.
* Fix Upsells / Cross-sells on the product form and Products / Categories selects + datepicker styling on the coupon form.

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
