---
name: analytics
description: Add, modify, or debug the StoreSuite Analytics feature. Use when the user asks to add a new report, add a new chart or stat indicator, fix a data-fetching issue, change routing, modify filters, adjust leaderboards, or work on any file under src/analytics/ or includes/Analytics/.
argument-hint: What to change (e.g. "add a Customers report", "add a new stat to the overview", "fix the chart interval selector")
---

# StoreSuite Analytics Skill

Task: $ARGUMENTS

## File Map

### PHP (server-side)
| File | Role |
|------|------|
| `includes/Analytics/Assets.php` | Registers & enqueues the analytics JS/CSS bundle; injects `storeSuiteAnalyticsConfig` and `storeSuiteAnalyticsSettings` globals |
| `includes/Rewrites.php` | Registers `analytics` rewrite endpoint (key: `analytics`, default slug: `analytics`) |
| `templates/analytics/analytics.php` | Renders `#storesuite-analytics-app` mount point; fires `storesuite_dashboard_navigation` and other layout hooks |

### JavaScript (React)
| File | Role |
|------|------|
| `src/analytics/index.js` | Entry point — sets webpack public path from `storeSuiteAnalyticsConfig.assetsPath`, renders `<PageLayout>` with `<ErrorBoundary>` into `#storesuite-analytics-app` |
| `src/analytics/config.js` | Holds `analyticsUrl`, `dashboardPath`, `reportsPath`, `assetsPath` from the PHP-injected global |
| `src/analytics/layout/index.js` | `PageLayout` — `unstable_HistoryRouter` + `SlotFillProvider` + `Routes`; wraps all reports |
| `src/analytics/layout/controller.js` | `Controller` — resolves current page, manages scroll reset, fires `storesuite_analytics_route_handler` action, lazy-loads dashboard |
| `src/analytics/analytics/report/get-reports.js` | Returns the array of report definitions with lazy-loaded components (`webpackChunkName`) |
| `src/analytics/analytics/report/index.js` | `ReportPage` — reads `query.report`, picks the matching report component, wraps in `<Suspense>` |
| `src/analytics/analytics/report/overview/index.js` | Overview report — stat visibility, chart interval/type, leaderboards |
| `src/analytics/analytics/report/overview/config.js` | 15 performance indicators config; `filters` and `advancedFilters` (all `applyFilters`-hookable) |
| `src/analytics/analytics/report/overview/overview-summary.js` | `OverviewSummary` — fetches `performance-indicators` via `getReportItems`, renders `SummaryNumber` cards |
| `src/analytics/analytics/report/overview/overview-leaderboards.js` | `OverviewLeaderboards` — leaderboard visibility + rows-per-table setting via `getLeaderboard` |
| `src/analytics/analytics/components/report-summary/index.js` | Shared `ReportSummary` — `withSelect` → `getSummaryNumbers`, renders `SummaryNumber` cards with delta |
| `src/analytics/analytics/components/report-chart/index.js` | Shared `ReportChart` — `withSelect` → `getReportChartData`, supports time-comparison and item-comparison modes |
| `src/analytics/analytics/components/report-chart/utils.js` | `buildChartData()`, `getChartMode()`, `createDateFormatter()`, `getSelectedFilter()` |
| `src/analytics/analytics/components/report-filters/index.js` | `ReportFilters` — wraps `@woocommerce/components` `Filters` with locale and date params |
| `src/analytics/analytics/components/report-table/index.js` | `ReportTable` — column visibility, sorting, pagination, CSV export via `CompareButton` |
| `src/analytics/analytics/components/report-error/index.js` | `ReportError` — `EmptyContent` with reload button |
| `src/analytics/lib/async-requests/index.js` | API helpers for categories, products, variations, coupons, tax rates (`/wc-analytics` namespace) |
| `src/analytics/utils/admin-settings.js` | `getAdminSetting()` / `setAdminSetting()` for `storeSuiteAnalyticsSettings` |
| `src/analytics/utils/helper.js` | `mapToDashboardRoute()` and `redirectIfAdminUrl()` — rewrites WP admin URLs to frontend dashboard URLs |

## Build & Output

```
webpack entry:  src/analytics/index.js
output bundle:  assets/build/analytics/index.js
lazy chunks:    assets/build/analytics/chunks/[name].js
```

Chunk names (from `webpackChunkName` comments in `get-reports.js`):
`ss-analytics-overview`, `ss-analytics-revenue`, `ss-analytics-orders`,
`ss-analytics-products`, `ss-analytics-variations`, `ss-analytics-categories`, `ss-analytics-stock`

All `@woocommerce/*` packages are **externals** — they resolve to `window.wc.*` at runtime (provided by WooCommerce). Do NOT bundle them.

## PHP Globals Injected into JS

| Global | Set in | Contents |
|--------|--------|----------|
| `storeSuiteAnalyticsConfig` | `Analytics/Assets.php` | `assetsPath`, `analyticsUrl`, `dashboardPath`, `reportsPath` |
| `storeSuiteAnalyticsSettings` | `Analytics/Assets.php` | Settings from `Settings` class |

## Routing

- URL format: `/{dashboard-slug}/analytics/?report=overview`
- `query.report` selects the active report (default: `overview`)
- Switching reports uses `updateQueryString({ report: 'revenue' })`
- All filters, sorting, pagination, and date ranges live in the query string

**Adding a new report:**
1. Create `src/analytics/analytics/report/{name}/index.js` (and `config.js`, `table.js` as needed)
2. Add a lazy import entry in `get-reports.js` with a unique `webpackChunkName`
3. Add the menu item to `DashboardMenu.php` under the Analytics submenu

## Data Fetching Pattern

All data fetching uses `withSelect` from `@wordpress/compose` + WooCommerce data stores:

```js
import { REPORTS_STORE_NAME, SETTINGS_STORE_NAME } from '@woocommerce/data';
import { withSelect } from '@wordpress/data';

export default withSelect( ( select, props ) => {
    const { query } = props;
    const { getSummaryNumbers, getReportChartData, getReportTableData } = select( REPORTS_STORE_NAME );
    // ...
} )( MyComponent );
```

Use `getSummaryNumbers` for stat cards, `getReportChartData` for charts, `getReportTableData` for tables, `getReportItems('performance-indicators', query)` for overview stats.

## LocalStorage Keys (Overview)

| Key | Stores |
|-----|--------|
| `storesuite_analytics_hidden_stats` | Array of hidden indicator keys |
| `storesuite_analytics_hidden_charts` | Array of hidden chart keys |
| `storesuite_analytics_chart_interval` | `day` \| `week` \| `month` \| `quarter` \| `year` |
| `storesuite_analytics_chart_type` | `line` \| `bar` |

## Overview Indicators Config Shape (`overview/config.js`)

```js
{
    key: 'revenue/total_sales',     // stat name for API query
    label: __( 'Total Sales', 'storesuite' ),
    type: 'currency',               // 'currency' | 'number'
    isReverseTrend: false,          // true = lower is better (e.g. refunds)
    endpoint: 'revenue',            // report linked from SummaryNumber click
    linkedReport: 'revenue',
}
```

All indicator and filter arrays pass through `applyFilters( 'storesuite_analytics_*', array )` — extend via WordPress hooks, not by editing the config directly.

## PHP Extension Hooks

| Hook | Type | Purpose |
|------|------|---------|
| `storesuite_analytics_settings` | filter | Final settings array inlined into `storeSuiteAnalyticsSettings`. Add custom `dataEndpoints`, `currentUserData` fields, locale info. |
| `storesuite_analytics_preload_endpoints` | filter | Map of endpoint key → REST path to preload. **Preferred** over Woo's `woocommerce_component_settings_preload_endpoints` (which still applies first for back-compat) so StoreSuite payloads don't leak into the WC admin context. |
| `storesuite_analytics_preload_options` | filter | List of `wp_options` to hydrate into the `@woocommerce/data` options store. |
| `storesuite_analytics_menu_items` | filter | Submenu array for the Analytics sidebar entry — add new report links here. |
| `storesuite_analytics_rest_read_objects` | filter | List of REST object names whose read access is granted to `manage_woocommerce` users on the frontend. |
| `storesuite_before_analytics_app` / `storesuite_after_analytics_app` | action | Fired around the `#storesuite-analytics-app` mount point — use for SlotFill targets or non-React UI. |
| `storesuite_before_dashboard_app` / `storesuite_after_dashboard_app` | action | Same, for the dashboard root. |

## WooCommerce Component Props (Deprecation Fixes)

All `SelectControl` components in this feature must include both:

```jsx
<SelectControl
    __next40pxDefaultSize
    __nextHasNoMarginBottom
    // ...other props
/>
```

Do NOT use the deprecated `position` prop on `Tooltip` — use `placement` instead.

## Currency & Number Formatting

Always use `CurrencyContext` for currency-aware formatting:

```js
import { CurrencyContext } from '@woocommerce/currency';

// Inside class component:
static contextType = CurrencyContext;
const { formatAmount, getCurrencyConfig } = this.context;

// For non-currency numbers:
import { formatValue } from '@woocommerce/number';
formatValue( getCurrencyConfig(), type, value );
```

## URL Rewriting (Admin → Frontend)

`redirectIfAdminUrl()` in `utils/helper.js` intercepts clicks on links that point to `admin.php` (generated internally by WooCommerce data stores) and rewrites them to the frontend dashboard equivalent using `mapToDashboardRoute()`. This runs in `PageLayout` on every location change.

## Critical Rules

1. **Never hardcode URLs** — use `getNewPath()`, `updateQueryString()`, or `storesuite_get_navigation_url()` (PHP).
2. **Never import `@woocommerce/*` into the bundle** — they are externals. Import normally; webpack resolves them to `window.wc.*`.
3. **New report = new lazy chunk** — always add a `/* webpackChunkName: "ss-analytics-{name}" */` comment on the dynamic import in `get-reports.js`.
4. **New report = new Analytics submenu item** — add it to `DashboardMenu.php` under the analytics submenu array.
5. **All `SelectControl` must have `__next40pxDefaultSize` and `__nextHasNoMarginBottom`** to suppress deprecation warnings.
6. **Use `CurrencyContext`** for any monetary value — never format currency manually.
7. **Stats are fetched by stat name** (e.g. `revenue/total_sales`), not by endpoint — refer to `overview/config.js` for available names.
