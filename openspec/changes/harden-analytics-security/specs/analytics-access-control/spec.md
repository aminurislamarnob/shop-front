## ADDED Requirements

### Requirement: Capability gate on analytics template render

The analytics template (`templates/analytics/analytics.php`) SHALL only render the React mount point and dashboard layout for users who pass `current_user_can( 'manage_woocommerce' )`. The gating MUST happen in `includes/Analytics/Controller.php::load_template()` before any layout or template part is loaded.

#### Scenario: Anonymous visitor hits the analytics URL
- **WHEN** a visitor without a WordPress session requests `/<dashboard-slug>/analytics/`
- **THEN** the system SHALL run the existing dashboard access redirect (`storesuite_redirect_unauthorized_dashboard_access`) and the analytics template SHALL NOT be rendered.

#### Scenario: Logged-in subscriber hits the analytics URL
- **WHEN** a subscriber who lacks `manage_woocommerce` requests the analytics URL
- **THEN** the system SHALL redirect to the configured dashboard home (or front page if not configured) and the analytics template, mount point, and preload globals SHALL NOT be rendered.

#### Scenario: Shop manager hits the analytics URL
- **WHEN** a user with `manage_woocommerce` requests the analytics URL
- **THEN** the analytics template SHALL render the React mount point and the preload globals SHALL be inlined.

### Requirement: Capability gate on analytics asset enqueue

`Analytics\Assets::enqueue_scripts()` and `Dashboard\Assets::enqueue_scripts()` SHALL short-circuit and enqueue nothing when the current user lacks `manage_woocommerce`. This SHALL be enforced even if the request reaches a dashboard endpoint via an unexpected route.

#### Scenario: Subscriber session enqueue
- **WHEN** the dashboard asset enqueue runs for a logged-in user without `manage_woocommerce`
- **THEN** the analytics React bundle, the dashboard React bundle, and the inline `storeSuiteAnalyticsConfig` / `storeSuiteAnalyticsSettings` / `storeSuiteDashboardConfig` globals SHALL NOT be enqueued or printed.

### Requirement: REST permission whitelist covers async-filter routes

`Analytics\RestPermissions::grant_read_access()` SHALL grant read access to the `/wc-analytics/*` routes used by the analytics React app's async filter dropdowns: `reports`, `settings`, `taxes`, `coupons`, `customers`, `products`, `variations`, `orders`, and any taxonomy endpoints reachable via `lib/async-requests/index.js`. Routes SHALL only be whitelisted when WooCommerce already enforces `manage_woocommerce` upstream.

#### Scenario: Shop manager opens the orders report filter dropdown
- **WHEN** a `manage_woocommerce` user opens the customers/coupons/taxes filter dropdown
- **THEN** the corresponding `/wc-analytics/*` request SHALL succeed with a 200 status.

#### Scenario: Subscriber attempts a whitelisted route
- **WHEN** a subscriber session calls a whitelisted `/wc-analytics/*` route
- **THEN** WooCommerce's upstream permission callback SHALL still reject the request (the StoreSuite whitelist only widens the route surface for users who already pass Woo's checks).

### Requirement: Bootstrap shim is exception-safe

`Analytics\WCAdminBootstrap::ensure()` SHALL guarantee the temporary `doing_it_wrong_trigger_error` filter is removed even if `WCAdminAssets::register_scripts()` throws an exception or fatal error. Implementation MUST use a `try { ... } finally { remove_filter(...); }` block.

#### Scenario: Bootstrap registers scripts successfully
- **WHEN** `WCAdminBootstrap::ensure()` runs for the first time on a request and `register_scripts()` returns normally
- **THEN** the `doing_it_wrong_trigger_error` filter SHALL be removed before `ensure()` returns.

#### Scenario: Bootstrap throws during registration
- **WHEN** `register_scripts()` throws during bootstrap
- **THEN** the `doing_it_wrong_trigger_error` filter SHALL still be removed before the exception propagates, leaving no global silencing in place.
