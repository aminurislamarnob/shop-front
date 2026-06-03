# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

StoreSuite is a WordPress/WooCommerce plugin that provides a frontend store management dashboard. Shop managers and store owners can manage products, orders, coupons, categories, tags, and brands without accessing the WordPress admin panel. Requires WooCommerce as a dependency. Declares WooCommerce HPOS (High-Performance Order Storage) compatibility.

## Development Commands

```bash
# Install dependencies
composer update
npm install

# JavaScript/React development
npm start              # Watch mode (hot reload)
npm run build          # Production build (wp-scripts)

# PHP code quality
composer phpcs         # Run PHP CodeSniffer
composer phpcbf        # Auto-fix PHP code
composer phpcs:report  # Generate PHPCS report file

# JS/CSS linting
npm run lint:js        # Lint JavaScript
npm run lint:css       # Lint CSS/SCSS
npm run format         # Auto-format with Prettier

# Release build (creates distributable ZIP)
bash bin/build.sh
```

Note: No PHP or JS test suite exists yet. PHPUnit is configured in `composer.json` but no `tests/` directory has been created.

## Architecture

### Plugin Bootstrap

`storesuite.php` is the entry point. It loads Composer autoload, then calls `pluginizelab_storesuite()` which initializes the singleton `StoreSuite` class. Initialization order:

1. `plugins_loaded` — dependency check (WooCommerce must be active), then `includes()` + `init_hooks()`, then constructs `Module\Manager` into `$container['modules']` and fires the `storesuite_loaded` action (active modules boot off this)
2. `rest_api_init` — registers REST routes via `SettingsController->register_routes()`
3. `init` (priority 4) — `init_classes()` creates all service instances in `$container`
4. `before_woocommerce_init` — declares HPOS compatibility

### PHP Structure (`includes/`)

- **Namespace:** `PluginizeLab\StoreSuite` with PSR-4 autoloading from `includes/`
- **Main orchestrator:** `StoreSuite.php` — singleton accessed globally via `pluginizelab_storesuite()`. Uses a `$container` array with `__get()` magic method for service access (e.g., `pluginizelab_storesuite()->scripts`)
- **Domain modules:** `Product/`, `Order/`, `Coupon/`, `ProductCategory/`, `ProductTag/`, `ProductBrand/`, `Account/` — each follows a Controller + Manager pattern:
  - **Controllers** register `wp_ajax_storesuite_*` hooks, handle form submission, load templates via `storesuite_get_template_part()`
  - **Managers** contain business logic (CRUD operations using WooCommerce APIs)
  - Some modules also have a Hooks class (e.g., `ProductHooks`, `OrderHooks`) for WordPress/WooCommerce action integrations
- **REST API:** `REST/SettingsController.php` — namespace `storesuite/v1`, base `settings`, handles admin settings CRUD. Permission checks require `manage_woocommerce` capability
- **Core services:**
  - `Assets.php` — registers and enqueues scripts/styles; strips theme and disallowed plugin assets on dashboard pages (extensible via `storesuite_allowed_plugin_slugs` and `storesuite_allowed_asset_handles` filters)
  - `Rewrites.php` — registers custom rewrite endpoints for each dashboard sub-page; endpoint slugs are configurable via `get_option('storesuite_myshop_*_endpoint')` with sensible defaults
  - `Main.php` — login redirects, admin access blocking for shop_manager/customer roles, admin bar hiding, CSS variable injection
  - `Dashboard.php` — renders KPI widgets (store performance, top products, top categories, top customers, top coupons) via `storesuite_dashboard_home_widgets` and `storesuite_dashboard_item_solds_widgets` action hooks
  - `Cache.php` — static wrapper around transients/object cache with `storesuite_` prefix and `storesuite` group
  - `DashboardMenu.php` — builds the sidebar navigation via `storesuite_dashboard_navigation` hook; menu items are permission-gated
- **Settings storage:** All plugin settings stored in a single `storesuite_settings` option (serialized array), accessed via `storesuite_get_option_by_key($key)` in `includes/functions.php`
- **Abstract bases:** `Abstracts/MyStoreSuiteShortcode.php` for shortcode-based pages; `Abstracts/Module.php` for the pluggable module system (see below)
- **Global functions:** `includes/functions.php` — template loading (`storesuite_get_template_part()`), endpoint detection (`storesuite_is_endpoint_url()`), navigation URLs, page checks, logging via `storesuite_log()`, access control redirects

### Module System (`modules/` + `includes/Module/`)

Self-contained, independently activatable features (e.g. Staff Manager) live under `modules/<slug>/`. The system has three parts:

- **`Abstracts/Module.php`** — base class every module extends. Defines the contract: abstract `get_slug()`, `get_name()`, `boot()`; overridable `get_description()`, `get_version()`, `get_requires()`; helpers `get_path()`/`get_url()` (derived from the bootstrap `$file` passed to the constructor); and no-op `activate()`/`deactivate()` lifecycle hooks for one-shot setup/teardown.
- **`Module/Manager.php`** (`$container['modules']`) — discovers, tracks, and boots modules.
  - **Discovery** is lazy (`discover()`, triggered by `get_all()`): globs `modules/*/module.php`, `include`s each bootstrap which must `return` a `Module` instance, keys them by slug. Third parties can inject more via the `storesuite_register_modules` filter; the modules dir is filterable via `storesuite_modules_dir`.
  - **Active state** is persisted as a plain array of slugs in the `storesuite_active_modules` option. `get_active_slugs()` intersects stored slugs with on-disk modules (drops stale entries); `get_active()` maps those to instances; `is_active($slug)` checks membership.
  - **Booting**: on `storesuite_loaded`, `boot_active()` calls `boot()` on each active module, firing `storesuite_module_{slug}_loaded` per module and `storesuite_modules_loaded` once at the end.
  - **Activate/deactivate** (`activate($slug)`/`deactivate($slug)`) update the option, call the module's lifecycle hook, and fire `storesuite_module_activated` / `storesuite_module_deactivated`.
- **Module layout** (`modules/staff-manager/` is the reference example): `module.php` is the bootstrap with a plugin-style header comment block (Module Name, Description, Version, Author); it `require`s and `return`s `new ...\Module( __FILE__ )`. The concrete `includes/Module.php` lives under namespace `PluginizeLab\StoreSuite\Modules\<Name>` and registers its behavior in `boot()` (e.g. adding a sidebar item via the `storesuite_dashboard_menus` filter).

### Two Separate Frontend Stacks

1. **Admin Settings Page (React):** Entry point `src/admin.js` builds to `assets/build/admin/script.js`. Uses `@wordpress/components`, `@wordpress/api-fetch`, `@heroicons/react` for icons, React Router DOM (hash routing). Routes:
   - `/` → `GeneralSettings` — dashboard page selector, sidebar branding, admin access toggle
   - `/dashboard-settings` → `DashboardSettings` — performance box and widget visibility toggles
   - `/appearance-settings` → `ColorsSettings` — predefined palettes and custom color pickers
   - `/pagination-settings` → `PaginationSettings` — items-per-page controls

   Styled with `@wordpress/components` built-in styles and plain CSS (`LayoutStyles.css`). Webpack config in `webpack.config.js` extends `@wordpress/scripts`.

   **Header:** `SettingsHeader` renders the top bar with title, subtitle, and an `actions` slot. The header currently shows a **Documentation** (secondary) and **Support Me** (primary, links to `https://buymeacoffee.com/aiarnob`) button.

   **Nav tabs:** Defined in `Layout.js` as hash-router `<Link>` elements with heroicons — `GearIcon` (General), `Squares2X2Icon` (Dashboard), `PaletteIcon` (Appearance), `CodeBracketSquareIcon` (Pagination).

   **Icons:** All heroicons are re-exported from `src/Components/icons.js` (24/outline). Add new icons there before importing elsewhere.

2. **Frontend Dashboard (jQuery + vanilla JS):** Located in `assets/frontend/`. Scripts include `script.js` (main), `form-handler.js` (CRUD forms with SweetAlert2), `order.js` (order management with selectWoo), `product.js` (product forms). Templates rendered server-side via PHP. Localized data passed via `wp_localize_script()` under `storeSuiteFormHandler`, `StoreSuite_Order`, `StoreSuite_Product` globals.

### Template System (`templates/`)

WooCommerce-style template overrides: checks theme's `my-storesuite/` directory first, falls back to plugin templates. Template loading via `storesuite_get_template_part()` in `includes/functions.php`. Path overridable via `storesuite_set_template_path` filter.

### URL Routing (Frontend Dashboard)

The dashboard page (created on activation with `[storesuite_dashboard]` shortcode) uses WordPress rewrite endpoints. Each sub-page (products, orders, categories, tags, brands, coupons, edit-account-details) is a rewrite endpoint. Pagination uses custom rewrite rules (e.g., `storesuite-dashboard/products/page/2`). The `Rewrites` class resolves conflicts with WooCommerce My Account `orders` query var.

### Key Constants

- `STORESUITE_FILE`, `STORESUITE_PLUGIN_FILE` — main plugin file path
- `STORESUITE_DIR`, `STORESUITE_INC_DIR`, `STORESUITE_TEMPLATE_DIR` — directory paths
- `STORESUITE_PLUGIN_URL`, `STORESUITE_PLUGIN_ASSET` — URL paths for assets
- `STORESUITE_PLUGIN_VERSION` — current version string
- `STORESUITE_LOAD_STYLE`, `STORESUITE_LOAD_SCRIPTS` — can be set to `false` to disable default asset loading

## Coding Standards

- WordPress Coding Standards enforced via PHPCS (`phpcs.xml`)
- PHP 7.4+ minimum, text domain: `storesuite`
- All functions/hooks/options prefixed with `storesuite_`
- `wc_clean` registered as a custom sanitizing function in PHPCS config
- Yoda conditions disabled, strict comparisons enforced as errors
- File naming convention rule disabled (allows PSR-4 class filenames)
- WordPress `wp-scripts` handles JS/CSS linting

## CI/CD

GitHub Actions (`.github/workflows/deploy.yml`) triggers on git tag push: installs with `--no-dev`, builds assets, creates ZIP via rsync + `.distignore`, uploads as GitHub release artifact, and deploys to WordPress.org via SVN.
