# StoreSuite WooCommerce Analytics Integration — Development Plan

## Overview

This document describes how to embed WooCommerce's React-powered analytics screens (Revenue, Orders, Products, Variations, Categories, Stock, etc.) into the StoreSuite frontend dashboard, using Dokan Lite + Pro's implementation as the reference blueprint.

**StoreSuite is a single-vendor plugin.** Both admin and shop_manager manage the same store and therefore see identical analytics data — the full, unscoped WooCommerce analytics. This is fundamentally simpler than Dokan (a multi-vendor marketplace that must isolate each vendor's data). **No custom DB table, no SQL JOIN filters, no cache key modification, and no data store replacement are required.**

---

## How Dokan Ports WooCommerce Analytics — Reference Analysis

### Core Insight

WooCommerce ships a full React analytics application bundled in packages: `@woocommerce/data`, `@woocommerce/components`, `@woocommerce/date`, `@woocommerce/currency`, `@woocommerce/navigation`. These are registered as WordPress script handles (`wc-components`, `wc-admin-layout`, `wc-experimental`, etc.) but are **only loaded in wp-admin by default**. Dokan does not rebuild any analytics UI — it reuses WC's component packages wholesale, wires a custom React entry point, and on the PHP side handles two concerns: (1) loading WC admin assets on the frontend, and (2) scoping API responses to the current vendor.

### Dokan's 5-Layer Architecture (for reference)

```
┌─────────────────────────────────────────────────────────────────┐
│  LAYER 1 — React App (Frontend)                                  │
│  Mounts into <div id="dokan-analytics-app">                      │
│  Uses @woocommerce/* packages — no UI rebuilt from scratch       │
│  Reports lazy-loaded as separate webpack chunks                  │
└─────────────────────────────────────────────────────────────────┘
┌─────────────────────────────────────────────────────────────────┐
│  LAYER 2 — Asset Loading                                         │
│  Calls WCAdminAssets::register_scripts() on frontend             │
│  so all WC admin script handles are available outside wp-admin   │
└─────────────────────────────────────────────────────────────────┘
┌─────────────────────────────────────────────────────────────────┐
│  LAYER 3 — REST API Permissions                                  │
│  woocommerce_rest_check_permissions → grants vendor read access  │
│  to /wc-analytics/ endpoints                                     │
└─────────────────────────────────────────────────────────────────┘
┌─────────────────────────────────────────────────────────────────┐
│  LAYER 4 — SQL Query Filtering  ← DOKAN-ONLY, NOT NEEDED HERE   │
│  JOIN wp_dokan_order_stats + WHERE vendor_id = X                 │
│  Isolates each vendor's data from the shared WC analytics tables │
└─────────────────────────────────────────────────────────────────┘
┌─────────────────────────────────────────────────────────────────┐
│  LAYER 5 — Custom DB Table      ← DOKAN-ONLY, NOT NEEDED HERE   │
│  wp_dokan_order_stats stores vendor_id per order                 │
│  Required because WC tables have no vendor concept               │
└─────────────────────────────────────────────────────────────────┘
```

**Layers 4 and 5 exist solely to solve the multi-vendor data isolation problem. StoreSuite has no such problem — skip them entirely.**

### Why StoreSuite Skips Layers 4 & 5

| Dokan | StoreSuite |
|---|---|
| Many vendors share one WC install | One store, one set of owners |
| Vendor A must not see Vendor B's orders | Admin and shop_manager see the same orders |
| Needs `vendor_id` JOIN to filter analytics SQL | WC analytics data is already the full store — no filter needed |
| Custom table + sync job required | No custom table, no sync job |

---

## StoreSuite Analytics Implementation Plan

### Target Analytics Screens (from screenshots)

1. **Revenue** — Gross sales, Returns, Coupons, Net sales, Taxes, Shipping, Total sales
2. **Orders** — Orders count, Net sales, Average order value, Average items per order
3. **Products** — Items sold, Net sales, Orders (with product/category filter)
4. **Variations** — Items sold, Net sales, Orders (with variation filter)
5. **Categories** — Items sold, Net sales, Orders, Products count
6. **Stock** — Product/Variation, SKU, Status, Stock quantity (table-only, no chart)

---

## Phase 1 — PHP Backend (3 files)

### 1.1 REST API Permissions

**File:** `includes/Analytics/RestPermissions.php`

WC analytics REST endpoints (`/wc-analytics/*`) check permissions server-side. By default they pass only for users with `view_woocommerce_reports` or `manage_woocommerce`. The `shop_manager` role has `manage_woocommerce` in WC by default, but this filter ensures access is granted safely in the frontend context (where `is_admin()` is false):

```php
namespace PluginizeLab\StoreSuite\Analytics;

use WP_REST_Request;
use WP_REST_Server;

class RestPermissions {
    public function register_hooks(): void {
        add_filter( 'woocommerce_rest_check_permissions', [ $this, 'grant_read_access' ], 20, 4 );
        add_filter( 'woocommerce_rest_api_option_permissions', [ $this, 'add_option_permissions' ], 10, 2 );
        add_filter( 'woocommerce_component_settings_preload_endpoints', [ $this, 'add_preload_endpoints' ], 20 );
    }

    public function grant_read_access( $permission, $context, $obj_id, $obj ): bool {
        if ( ! $permission && $context === 'read'
            && in_array( $obj, [ 'reports', 'settings', 'product_cat' ], true )
            && storesuite_is_endpoint_url()
        ) {
            $permission = current_user_can( 'manage_woocommerce' );
        }
        return $permission;
    }

    public function add_option_permissions( array $permission, WP_REST_Request $request ): array {
        if ( $request->get_method() !== WP_REST_Server::READABLE ) {
            return $permission;
        }
        // Allow reading woocommerce_admin_install_timestamp option on frontend.
        $permission['woocommerce_admin_install_timestamp'] = current_user_can( 'manage_woocommerce' );
        return $permission;
    }

    public function add_preload_endpoints( array $endpoints ): array {
        if ( storesuite_is_endpoint_url() ) {
            $endpoints['performanceIndicators'] = '/wc-analytics/reports/performance-indicators/allowed';
            $endpoints['leaderboards']          = '/wc-analytics/leaderboards/allowed';
        }
        return $endpoints;
    }
}
```

### 1.2 Settings Payload

**File:** `includes/Analytics/Settings.php`

Builds the `storeSuiteAnalyticsSettings` JS global that hydrates the React app. Mirrors WC Admin's own settings payload (which normally goes to wp-admin only):

```php
namespace PluginizeLab\StoreSuite\Analytics;

class Settings {
    public function get_settings(): array {
        $settings = [];
        $settings['stockStatuses']      = wc_get_product_stock_status_options();
        $settings['isAnalyticsEnabled'] = true;
        $settings['lastDayOfTheMonth']  = ( new \DateTime() )->format( 'Y-m-d' );
        $settings['firstDayOfTheMonth'] = ( new \DateTime( 'first day of this month' ) )->format( 'Y-m-d' );
        $settings                       = array_merge( $settings, $this->load_preload_endpoints() );
        return $settings;
    }

    protected function load_preload_endpoints(): array {
        // Mirrors WC's own approach in Internal\Admin\Settings::get_preload_data()
        try {
            $analytics         = \Automattic\WooCommerce\Internal\Admin\Analytics::get_instance();
            $raw_endpoints     = apply_filters(
                'woocommerce_component_settings_preload_endpoints',
                $analytics->add_preload_endpoints( [] )
            );
            $preload_data      = array_reduce(
                array_values( $raw_endpoints ),
                'rest_preload_api_request'
            );
            $data_endpoints    = [];
            foreach ( $raw_endpoints as $key => $endpoint ) {
                $data_endpoints[ $key ] = $preload_data[ $endpoint ]['body'] ?? [];
            }
            return [ 'dataEndpoints' => $data_endpoints ];
        } catch ( \Exception $e ) {
            storesuite_log( 'Analytics settings preload error: ' . $e->getMessage() );
            return [];
        }
    }
}
```

### 1.3 Asset Loader

**File:** `includes/Analytics/Assets.php`

The most critical file. WC admin assets are only registered for wp-admin pages; this bootstraps them on the frontend so all `wc-*` script handles are available as dependencies:

```php
namespace PluginizeLab\StoreSuite\Analytics;

use Automattic\WooCommerce\Internal\Admin\WCAdminAssets;

class Assets {
    public function register_hooks(): void {
        add_action( 'init', [ $this, 'register_scripts' ] );
        add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
    }

    public function register_scripts(): void {
        if ( is_admin() ) {
            return; // WC handles admin registration itself.
        }

        // Suppress _doing_it_wrong triggered by WC when registering admin scripts on frontend.
        add_filter( 'doing_it_wrong_trigger_error', '__return_false' );
        WCAdminAssets::get_instance()->register_scripts();
        remove_filter( 'doing_it_wrong_trigger_error', '__return_false' );

        $asset_file  = STORESUITE_DIR . '/assets/build/analytics/index.asset.php';
        $asset       = file_exists( $asset_file ) ? include $asset_file : [];
        $deps        = $asset['dependencies'] ?? [];
        $version     = $asset['version'] ?? STORESUITE_PLUGIN_VERSION;

        // WC UI dependencies needed by @woocommerce/components in a frontend context.
        $wc_deps = [ 'wc-components', 'wc-admin-layout', 'wc-customer-effort-score', 'wp-components', 'wc-experimental' ];
        $deps    = array_merge( $deps, $wc_deps );

        wp_register_script(
            'storesuite-analytics',
            STORESUITE_PLUGIN_ASSET . '/build/analytics/index.js',
            $deps,
            $version,
            true
        );

        wp_register_style(
            'storesuite-analytics',
            STORESUITE_PLUGIN_ASSET . '/build/analytics/index.css',
            [ 'wc-components', 'wp-components' ],
            $version
        );
    }

    public function enqueue_scripts(): void {
        if ( ! storesuite_is_endpoint_url( 'analytics' ) ) {
            return;
        }

        $settings = ( new Settings() )->get_settings();

        wp_enqueue_script( 'storesuite-analytics' );
        wp_enqueue_style( 'storesuite-analytics' );
        wp_set_script_translations( 'storesuite-analytics', 'storesuite' );

        wp_add_inline_script(
            'storesuite-analytics',
            'var storeSuiteAnalyticsConfig = ' . wp_json_encode( [
                'analyticsUrl'  => storesuite_get_navigation_url( 'analytics' ),
                'dashboardPath' => wp_parse_url( storesuite_get_navigation_url(), PHP_URL_PATH ),
                'reportsPath'   => wp_parse_url( storesuite_get_navigation_url( 'analytics' ), PHP_URL_PATH ),
            ] ),
            'before'
        );

        wp_add_inline_script(
            'storesuite-analytics',
            'var storeSuiteAnalyticsSettings = ' . wp_json_encode( $settings ),
            'before'
        );
    }
}
```

---

## Phase 2 — Dashboard Integration (PHP)

### 2.1 Rewrite Endpoint

**File:** `includes/Rewrites.php` (extend existing `register_endpoints()`)

```php
// Add alongside existing endpoints
$analytics_endpoint = get_option( 'storesuite_myshop_analytics_endpoint', 'analytics' );
add_rewrite_endpoint( $analytics_endpoint, EP_PAGES );
```

### 2.2 Dashboard Menu Item

**File:** `includes/DashboardMenu.php` (extend existing menu array)

```php
[
    'title' => __( 'Analytics', 'storesuite' ),
    'icon'  => '<svg>...</svg>', // chart-bar heroicon (24/outline)
    'url'   => storesuite_get_navigation_url( 'analytics' ),
    'slug'  => 'analytics',
]
```

### 2.3 Analytics Template

**File:** `templates/analytics/analytics.php` (new)

```php
<?php defined( 'ABSPATH' ) || exit; ?>
<?php storesuite_get_template_part( 'global/dashboard-header' ); ?>
<div id="storesuite-analytics-app"></div>
<?php storesuite_get_template_part( 'global/dashboard-footer' ); ?>
```

### 2.4 Template Controller

**File:** `includes/Analytics/Controller.php` (new)

```php
namespace PluginizeLab\StoreSuite\Analytics;

class Controller {
    public function register_hooks(): void {
        add_action( 'storesuite_load_custom_template', [ $this, 'load_template' ] );
        add_action( 'storesuite_dashboard_before_widgets', [ $this, 'inject_mount_point' ], 20 );
    }

    public function load_template( array $query_vars ): void {
        $endpoint = get_option( 'storesuite_myshop_analytics_endpoint', 'analytics' );
        if ( isset( $query_vars[ $endpoint ] ) ) {
            storesuite_get_template_part( 'analytics/analytics' );
        }
    }
}
```

---

## Phase 3 — React Frontend

### 3.1 Directory Structure

```
src/analytics/
├── index.js                          ← app entry — mounts into #storesuite-analytics-app
├── config.js                         ← reads storeSuiteAnalyticsConfig global
├── error-boundary/
│   └── index.tsx
├── stylesheets/
│   └── _index.scss
├── layout/
│   ├── index.js                      ← PageLayout (router shell)
│   ├── controller.js                 ← page/route registry + Controller component
│   ├── navigation.js                 ← breadcrumb navigation
│   └── style.scss
├── dashboard/
│   ├── index.js                      ← Overview: StorePerformance + Charts + Leaderboards
│   ├── default-sections.js
│   └── style.scss
├── analytics/
│   ├── components/
│   │   ├── report-chart/index.js     ← wraps @woocommerce/components Chart
│   │   ├── report-summary/index.js   ← wraps SummaryList / SummaryNumber
│   │   ├── report-filters/index.js
│   │   ├── report-error/index.js
│   │   └── leaderboard/index.js
│   └── report/
│       ├── get-reports.js            ← lazy report registry
│       ├── revenue/{index,config,table}.js
│       ├── orders/{index,config,table}.js
│       ├── products/{index,config,table,utils}.js
│       ├── variations/{index,config,table}.js
│       ├── categories/{index,config,table}.js
│       └── stock/{index,config,table,utils}.js
└── utils/
    ├── admin-settings.js             ← reads storeSuiteAnalyticsSettings
    └── index.js
```

### 3.2 App Entry Point (`src/analytics/index.js`)

```js
import '@wordpress/notices';
import { createRoot } from '@wordpress/element';
import { withCurrentUserHydration, withSettingsHydration } from '@woocommerce/data';
import { PageLayout } from './layout';
import { ErrorBoundary } from './error-boundary';
import domReady from '@wordpress/dom-ready';

domReady( () => {
    const appRoot = document.getElementById( 'storesuite-analytics-app' );
    if ( ! appRoot ) return;

    const root = createRoot( appRoot );
    const hydrateUser = window.storeSuiteAnalyticsSettings?.currentUserData;

    let HydratedLayout = withSettingsHydration( 'wc_admin', window.wcSettings?.admin )( PageLayout );

    const preloadSettings = window.wcSettings?.admin?.preloadSettings;
    if ( preloadSettings?.general ) {
        HydratedLayout = withSettingsHydration( 'general', { general: preloadSettings.general } )( HydratedLayout );
    }
    if ( hydrateUser ) {
        HydratedLayout = withCurrentUserHydration( hydrateUser )( HydratedLayout );
    }

    root.render(
        <ErrorBoundary>
            <HydratedLayout />
        </ErrorBoundary>
    );
} );
```

### 3.3 Config (`src/analytics/config.js`)

```js
export const storeSuiteConfig = {
    analyticsUrl:  '',
    dashboardPath: '',
    reportsPath:   '',
    ...( typeof storeSuiteAnalyticsConfig !== 'undefined' ? storeSuiteAnalyticsConfig : {} ),
};
```

### 3.4 Report Registry (`src/analytics/analytics/report/get-reports.js`)

```js
import { __ } from '@wordpress/i18n';
import { applyFilters } from '@wordpress/hooks';
import { lazy } from '@wordpress/element';

const RevenueReport    = lazy( () => import( /* webpackChunkName: "ss-analytics-revenue" */    './revenue' ) );
const OrdersReport     = lazy( () => import( /* webpackChunkName: "ss-analytics-orders" */     './orders' ) );
const ProductsReport   = lazy( () => import( /* webpackChunkName: "ss-analytics-products" */   './products' ) );
const VariationsReport = lazy( () => import( /* webpackChunkName: "ss-analytics-variations" */ './variations' ) );
const CategoriesReport = lazy( () => import( /* webpackChunkName: "ss-analytics-categories" */ './categories' ) );
const StockReport      = lazy( () => import( /* webpackChunkName: "ss-analytics-stock" */      './stock' ) );

export default () => applyFilters( 'storesuite_analytics_reports_list', [
    { report: 'revenue',    title: __( 'Revenue',    'storesuite' ), component: RevenueReport    },
    { report: 'orders',     title: __( 'Orders',     'storesuite' ), component: OrdersReport     },
    { report: 'products',   title: __( 'Products',   'storesuite' ), component: ProductsReport   },
    { report: 'variations', title: __( 'Variations', 'storesuite' ), component: VariationsReport },
    { report: 'categories', title: __( 'Categories', 'storesuite' ), component: CategoriesReport },
    { report: 'stock',      title: __( 'Stock',      'storesuite' ), component: StockReport      },
] );
```

### 3.5 Per-Report Components

Each report is a thin composition of `@woocommerce/components` and `@woocommerce/data` primitives — nothing custom is built:

```jsx
// Example: src/analytics/analytics/report/revenue/index.js
import { Component, Fragment } from '@wordpress/element';
import { advancedFilters, charts, filters } from './config';
import getSelectedChart from '../../lib/get-selected-chart';
import ReportChart from '../../components/report-chart';
import ReportSummary from '../../components/report-summary';
import ReportFilters from '../../components/report-filters';
import RevenueReportTable from './table';

export default class RevenueReport extends Component {
    render() {
        const { path, query } = this.props;
        return (
            <Fragment>
                <ReportFilters query={query} path={path} report="revenue" filters={filters} advancedFilters={advancedFilters} />
                <ReportSummary charts={charts} endpoint="revenue" query={query}
                    selectedChart={getSelectedChart( query.chart, charts )} />
                <ReportChart charts={charts} endpoint="revenue" path={path}
                    query={query} selectedChart={getSelectedChart( query.chart, charts )} />
                <RevenueReportTable query={query} />
            </Fragment>
        );
    }
}
```

Each report's `config.js` declares chart keys, labels, types (`currency` / `number`), and filter options — all prefixed with `storesuite_analytics_` for filter names.

### 3.6 Key `@woocommerce/*` Imports Driving Data

| Import | Source package | What it provides |
|---|---|---|
| `getReportChartData` | `@woocommerce/data` | REST-backed chart data selector |
| `Chart` | `@woocommerce/components` | Line/bar chart component |
| `ReportTable` | `@woocommerce/components` | Sortable/paginated data table |
| `SummaryList`, `SummaryNumber` | `@woocommerce/components` | KPI summary boxes |
| `getCurrentDates`, `getIntervalForQuery` | `@woocommerce/date` | Date range utilities |
| `CurrencyContext` | `@woocommerce/currency` | Currency formatting |
| `getHistory`, `getNewPath` | `@woocommerce/navigation` | URL/query state management |

---

## Phase 4 — Webpack Build Configuration

### 4.1 New Entry Point

**File:** `webpack.config.js` (extend existing)

```js
const defaultConfig = require( '@wordpress/scripts/config/webpack.config' );
const path = require( 'path' );

module.exports = {
    ...defaultConfig,
    entry: {
        'admin/script':    './src/admin.js',       // existing
        'analytics/index': './src/analytics/index.js', // new
    },
    output: {
        ...defaultConfig.output,
        path: path.resolve( __dirname, 'assets/build' ),
        chunkFilename: 'analytics/chunks/[name].js',
    },
};
```

### 4.2 External Dependencies

WooCommerce analytics packages are **not bundled** — they are loaded as WP script dependencies at runtime. Add them as webpack externals:

```js
externals: {
    ...defaultConfig.externals,
    '@woocommerce/components': [ 'window', 'wc', 'components' ],
    '@woocommerce/data':       [ 'window', 'wc', 'data' ],
    '@woocommerce/date':       [ 'window', 'wc', 'date' ],
    '@woocommerce/currency':   [ 'window', 'wc', 'currency' ],
    '@woocommerce/navigation': [ 'window', 'wc', 'navigation' ],
    '@woocommerce/number':     [ 'window', 'wc', 'number' ],
},
```

---

## Phase 5 — StoreSuite Main Class Wiring

**File:** `includes/StoreSuite.php` — extend `init_classes()`

```php
// Analytics (3 PHP classes only — no SQL layer needed)
$container['analytics_permissions'] = new Analytics\RestPermissions();
$container['analytics_controller']  = new Analytics\Controller();
$container['analytics_assets']      = new Analytics\Assets();
```

All three classes call `register_hooks()` via the same pattern used for other services.

---

## Phase 6 — Optional: Admin Settings Toggle

Add an **Enable Analytics** toggle in `DashboardSettings` (existing React settings page). Store in `storesuite_settings['enable_analytics']`. `DashboardMenu.php` and `Controller.php` both check this option before exposing the nav item and template.

---

## Implementation Order

```
Sprint 1 — PHP (all 3 files + dashboard wiring)
  ├─ RestPermissions.php
  ├─ Settings.php
  ├─ Assets.php
  ├─ Controller.php
  ├─ Rewrites.php → add analytics endpoint
  ├─ DashboardMenu.php → add nav item
  └─ templates/analytics/analytics.php

Sprint 2 — React App
  ├─ webpack.config.js → add entry + externals
  ├─ src/analytics/index.js (entry point)
  ├─ src/analytics/config.js
  ├─ src/analytics/error-boundary/
  ├─ src/analytics/layout/ (router + controller)
  ├─ src/analytics/utils/admin-settings.js
  ├─ src/analytics/analytics/components/ (report-chart, report-summary, report-filters, report-error)
  ├─ Revenue report (simplest — no withSelect needed)
  ├─ Orders report
  ├─ Products report (most complex — single-product variable detection via withSelect)
  ├─ Variations report
  ├─ Categories report
  ├─ Stock report (table-only, no chart)
  └─ src/analytics/dashboard/ (StorePerformance + Leaderboards overview)

Sprint 3 — QA & Polish
  ├─ Verify both admin and shop_manager see identical data
  ├─ Verify /wc-analytics/* REST calls succeed on frontend (check Network tab)
  ├─ Verify webpack chunks load correctly (no 404s)
  ├─ Integrate StoreSuite CSS variables into analytics stylesheet
  └─ Settings toggle (enable/disable analytics nav item)
```

---

## Key Technical Risks & Mitigations

| Risk | Mitigation |
|---|---|
| `WCAdminAssets::get_instance()` internal class renamed/removed in WC upgrade | Wrap in `try/catch`; log with `storesuite_log()`; test on each WC major |
| `_doing_it_wrong` triggered when registering WC admin scripts on frontend | Temporarily suppress with `doing_it_wrong_trigger_error` filter (same technique as Dokan) |
| WC analytics REST endpoints call `is_admin()` internally and bail early | `woocommerce_rest_check_permissions` filter is the correct bypass — verified in Dokan's implementation |
| Webpack chunks 404 (wrong `publicPath`) | Set `output.publicPath` to the plugin's asset URL in webpack config |
| `@woocommerce/navigation` uses `window.history` and conflicts with frontend page routing | Wrap in `ErrorBoundary`; use WC navigation's own `getHistory()` which works outside wp-admin |

---

## File Summary

### New PHP Files (4 files)

```
includes/Analytics/
├── Assets.php
├── Controller.php
├── RestPermissions.php
└── Settings.php
```

### New JS/React Files

```
src/analytics/
├── index.js
├── config.js
├── error-boundary/index.tsx
├── stylesheets/_index.scss
├── layout/{index,controller,navigation,style}.js
├── dashboard/{index,default-sections,style}.js
├── analytics/
│   ├── components/
│   │   ├── report-chart/index.js
│   │   ├── report-summary/index.js
│   │   ├── report-filters/index.js
│   │   ├── report-error/index.js
│   │   └── leaderboard/index.js
│   └── report/
│       ├── get-reports.js
│       ├── revenue/{index,config,table}.js
│       ├── orders/{index,config,table}.js
│       ├── products/{index,config,table,utils}.js
│       ├── variations/{index,config,table}.js
│       ├── categories/{index,config,table}.js
│       └── stock/{index,config,table,utils}.js
└── utils/admin-settings.js
```

### Modified Files

```
includes/StoreSuite.php     ← register 3 Analytics classes in init_classes()
includes/Rewrites.php       ← register 'analytics' rewrite endpoint
includes/DashboardMenu.php  ← add Analytics nav item
webpack.config.js           ← add analytics entry + chunk output + externals
```

### New Template

```
templates/analytics/analytics.php
```
