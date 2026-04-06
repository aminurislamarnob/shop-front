# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

StoreSuite is a WordPress/WooCommerce plugin that provides a frontend store management dashboard. Shop managers and store owners can manage products, orders, coupons, categories, tags, and brands without accessing the WordPress admin panel. Requires WooCommerce as a dependency.

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

## Architecture

### Plugin Bootstrap

`storesuite.php` is the entry point. It loads Composer autoload, then calls `pluginizelab_storesuite()` which initializes the singleton `StoreSuite` class. Initialization order:

1. `plugins_loaded` - dependency check (WooCommerce must be active)
2. `rest_api_init` - register REST routes
3. `init` (priority 4) - initialize all service classes via `init_classes()`

### PHP Structure (`includes/`)

- **Namespace:** `PluginizeLab\StoreSuite` with PSR-4 autoloading from `includes/`
- **Main orchestrator:** `StoreSuite.php` - singleton with service container (`$container` array, accessed via `__get()` magic method)
- **Domain modules:** `Product/`, `Order/`, `Coupon/`, `ProductCategory/`, `ProductTag/`, `ProductBrand/`, `Account/` - each has a Controller (AJAX handlers) and Manager (business logic)
- **REST API:** `REST/SettingsController.php` - namespace `storesuite/v1`, handles admin settings CRUD
- **Core services:** `Assets.php` (script/style enqueuing), `Rewrites.php` (custom URL endpoints), `Main.php` (redirects, access control), `Dashboard.php` (KPI widgets), `Cache.php` (transient/object cache wrapper with `storesuite_` prefix)
- **Abstract base:** `Abstracts/MyStoreSuiteShortcode.php` for shortcode-based pages

### React Admin Interface (`src/`)

- Entry point: `src/admin.js` (builds to `assets/build/admin/script.js`)
- Uses `@wordpress/components`, `@wordpress/api-fetch`, React Router DOM (hash routing)
- Routes: `/` (GeneralSettings), `/appearance-settings` (ColorsSettings), `/pagination-settings` (PaginationSettings)
- Webpack extends `@wordpress/scripts` default config (see `webpack.config.js`)
- Styled with Tailwind CSS

### Template System (`templates/`)

WooCommerce-style template overrides: checks theme's `my-storesuite/` directory first, falls back to plugin templates. Template loading via `storesuite_get_template()` and `storesuite_get_template_part()` in `includes/functions.php`.

### Frontend Dashboard

Rendered via `[storesuite_dashboard]` shortcode on a dedicated page. Uses custom rewrite endpoints for sub-pages (products, orders, categories, etc.). CSS variables injected inline for color customization.

## Coding Standards

- WordPress Coding Standards enforced via PHPCS (`phpcs.xml`)
- PHP 7.4+ minimum, text domain: `storesuite`
- All functions/hooks/options prefixed with `storesuite_`
- `wc_clean` registered as a custom sanitizing function in PHPCS config
- WordPress `wp-scripts` handles JS/CSS linting

## CI/CD

GitHub Actions (`.github/workflows/deploy.yml`) triggers on git tag push: builds assets, creates GitHub release with ZIP artifact, and deploys to WordPress.org.
