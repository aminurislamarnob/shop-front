## Why

PR #130 ships the WooCommerce admin analytics React app onto the StoreSuite frontend dashboard. A code review of the merged branch surfaced **two blockers** (subscriber-level access to the analytics bundle, and a globally leaked `_doing_it_wrong` filter on bootstrap exception) plus a cluster of high-severity gaps in REST permissions, preload caching, and third-party extensibility. These need to be closed before the feature is shipped to end users — the security gaps expose preloaded WC data to non-admins, and the extensibility gaps lock third-party plugins out of a feature that StoreSuite intends to be hookable.

## What Changes

- Add `manage_woocommerce` capability gating to the analytics template render, the analytics React mount, and the dashboard preload payload — non-admins must not receive the bundle or the inlined globals.
- Wrap `WCAdminBootstrap::ensure()` script registration in `try/finally` so the temporary `doing_it_wrong_trigger_error` filter is removed even if `register_scripts()` throws.
- Widen `RestPermissions::grant_read_access` to cover the full `/wc-analytics/*` route surface used by async filter dropdowns (taxes, coupons, customers, products, variations, orders) — currently scoped to `reports`/`settings`/`product_cat` only.
- Re-key the preload transient from per-user to per-capability and bust on role changes (`set_user_role`, `add_user_role`, `remove_user_role`) instead of relying on TTL alone.
- Skip preloading endpoints whose widgets are hidden in `storesuite_dashboard_settings` (leaderboards / performance indicators).
- Pass a `languages/` path argument to `wp_set_script_translations` for both the analytics and dashboard bundles so `.json` translation files load.
- Replace re-use of WC's `woocommerce_component_settings_preload_endpoints` filter with a StoreSuite-prefixed equivalent so the dashboard payload doesn't leak into the WC admin context.
- Introduce extensibility filters: `storesuite_analytics_settings`, `storesuite_analytics_menu_items`, `storesuite_analytics_data_endpoints`, and `do_action( 'storesuite_before_analytics_app' )` / `_after_` mount hooks for SlotFill injection.
- Memoize `pluginizelab_storesuite()->get_storesuite_query()` so it returns the cached `Rewrites` instance instead of constructing a new one on every call.

No breaking changes — all existing filter names continue to work. New filters are additive.

## Capabilities

### New Capabilities
- `analytics-access-control`: capability gating, REST permission whitelist, and authorization rules for the StoreSuite frontend analytics surface.
- `analytics-preload`: server-side preload payload composition, caching strategy, and invalidation rules for the analytics + dashboard React bundles.
- `analytics-extensibility`: third-party hook surface (PHP filters/actions and JS `applyFilters` points) for extending reports, menu items, settings, and the mount template.

### Modified Capabilities
None — this is the first formal spec for the analytics surface.

## Impact

- **PHP files affected**: `includes/Analytics/Controller.php`, `includes/Analytics/Assets.php`, `includes/Analytics/Settings.php`, `includes/Analytics/RestPermissions.php`, `includes/Analytics/WCAdminBootstrap.php`, `includes/Dashboard/Assets.php`, `includes/DashboardMenu.php`, `includes/StoreSuite.php`, `templates/analytics/analytics.php`, `templates/dashboard.php`.
- **JS files affected**: none — all changes are server-side. The new `applyFilters` JS hook for runtime indicator mutation is deferred to a follow-up change.
- **Performance**: preload transient rekeyed; storage drops from O(users × WC versions) to O(roles × WC versions). One additional capability-set hash per request.
- **Backwards compatibility**: existing third-party code that relied on `manage_woocommerce` users hitting the analytics URL is unaffected. Anonymous and subscriber traffic — currently receiving the bundle — will now receive a 403 redirect or the standard non-dashboard layout.
- **Security posture**: closes data exposure on `/storesuite-dashboard/analytics/` for non-`manage_woocommerce` roles.
