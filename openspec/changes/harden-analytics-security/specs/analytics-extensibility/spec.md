## ADDED Requirements

### Requirement: Filterable analytics settings payload

The settings array returned by `Analytics\Settings::get_settings()` SHALL pass through `apply_filters( 'storesuite_analytics_settings', $settings )` before being returned. Third parties SHALL be able to inject additional `dataEndpoints`, `currentUserData` flags, locale info, or custom keys without forking the file.

#### Scenario: Plugin appends a custom data endpoint
- **WHEN** a plugin hooks into `storesuite_analytics_settings` and adds a new `dataEndpoints['my-report']` URL
- **THEN** the inlined `storeSuiteAnalyticsSettings` global SHALL include the new endpoint and `getAdminSetting( 'dataEndpoints' )` on the JS side SHALL return it.

### Requirement: Filterable analytics submenu

`DashboardMenu::add_dashboard_navigations()` SHALL pass the analytics submenu items array through `apply_filters( 'storesuite_analytics_menu_items', $items )` before merging them into the menu. The filter SHALL receive each item with `permission`, `url`, `icon`, and `title` keys so plugins can inject their own report links.

#### Scenario: Plugin adds a Customers report submenu item
- **WHEN** a plugin appends `[ 'title' => 'Customers', 'url' => add_query_arg( 'report', 'customers', $analytics_url ), 'permission' => 'manage_woocommerce' ]` via the filter
- **THEN** the item SHALL appear under the Analytics submenu in the dashboard sidebar with active-state highlighting when its query var matches.

### Requirement: Mount template hooks

The analytics and dashboard templates SHALL each fire `do_action( 'storesuite_before_<context>_app', $context_args )` immediately before the React mount `<div>` and `do_action( 'storesuite_after_<context>_app', $context_args )` immediately after, where `<context>` is `analytics` or `dashboard`. These hooks let extensions inject SlotFill targets, debug bars, or non-React UI without forking the templates.

#### Scenario: Plugin renders a SlotFill target before the mount
- **WHEN** a plugin hooks `storesuite_before_analytics_app` and echoes `<div id="my-slotfill-root"></div>`
- **THEN** the markup SHALL render in the DOM before `#storesuite-analytics-app` and SHALL be available to React SlotFill providers.

### Requirement: Memoized rewrites accessor

`pluginizelab_storesuite()->get_storesuite_query()` SHALL return a single shared `Rewrites` instance per request, stored on the StoreSuite container. Repeated calls SHALL NOT re-construct the object or re-run filter chains.

#### Scenario: Multiple consumers in one request
- **WHEN** `DashboardMenu::add_dashboard_navigations()`, `storesuite_is_endpoint_url()`, and `storesuite_get_navigation_url()` all call the accessor in the same request
- **THEN** they SHALL all receive the same `Rewrites` instance and the underlying `init_query_vars()` filter chain SHALL only have run once.
