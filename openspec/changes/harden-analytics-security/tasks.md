## 1. Capability gating (BLOCKER)

- [x] 1.1 Add `current_user_can( 'manage_woocommerce' )` early-return to `Analytics\Controller::load_template()` and route unauthorized users through the existing dashboard redirect helper.
- [x] 1.2 Add the same gate to `Analytics\Assets::enqueue_scripts()` so the bundle and inline globals never reach a non-admin session.
- [x] 1.3 Add the gate to `Dashboard\Assets::enqueue_scripts()` so the dashboard React bundle and `storeSuiteDashboardConfig` are also gated.
- [ ] 1.4 Manually verify with three sessions: anonymous (redirect), subscriber (redirect, no globals in HTML), shop_manager (full bundle).

## 2. Bootstrap shim exception safety (BLOCKER)

- [x] 2.1 Wrap the `WCAdminAssets::get_instance()->register_scripts()` call inside `Analytics\WCAdminBootstrap::ensure()` in a `try { ... } finally { remove_filter( 'doing_it_wrong_trigger_error', '__return_false' ); }` block.
- [ ] 2.2 Add a defensive test (or manual smoke) that throws inside `register_scripts()` and confirms the filter is removed.

## 3. REST permission whitelist

- [x] 3.1 Audit `lib/async-requests/index.js` and the WC analytics filter dropdowns to enumerate the full `/wc-analytics/*` route surface (taxes, coupons, customers, products, variations, orders, taxonomy endpoints).
- [x] 3.2 Extend `Analytics\RestPermissions::grant_read_access()` with the enumerated routes; confirm each route already has a Woo `manage_woocommerce` permission callback so we are widening, not weakening.
- [ ] 3.3 Smoke-test as a shop_manager: open the orders/products/coupons report filter dropdowns and confirm no console errors and 200 responses.

## 4. Preload cache rekey & invalidation

- [x] 4.1 Replace the per-user transient key in `Analytics\Settings::preload_cache_key()` with a hash of the active capability set + `WC_VERSION`.
- [x] 4.2 Hook `set_user_role`, `add_user_role`, `remove_user_role` to delete the affected user's cached preload payload.
- [x] 4.3 Keep the 15-minute TTL as a safety net and document the rationale in a one-line comment.
- [ ] 4.4 Verify with two admin users (same caps): only one transient entry exists in the object cache.

## 5. Hidden-widget preload skip

- [x] 5.1 In `Dashboard\Assets::enqueue_scripts()`, read `storesuite_dashboard_settings` and compute the list of needed preload endpoints (skip `performance-indicators` when hidden, skip `leaderboards` when hidden).
- [x] 5.2 Pass the computed list into `Analytics\Settings::get_settings( $needed_endpoints )`.
- [ ] 5.3 Verify the inlined `storeSuiteAnalyticsSettings` global omits the hidden endpoints.

## 6. StoreSuite-prefixed preload filter

- [x] 6.1 Apply both `woocommerce_component_settings_preload_endpoints` (existing) and `storesuite_analytics_preload_endpoints` (new) inside `load_preload_endpoints()`, in that order.
- [x] 6.2 Update `.claude/skills/analytics/SKILL.md` to document the new filter as the preferred extension point.

## 7. Script translations path

- [x] 7.1 Add the third argument (`STORESUITE_DIR . '/languages'`) to `wp_set_script_translations()` calls in both `Analytics\Assets` and `Dashboard\Assets`.

## 8. Extensibility filters & actions

- [x] 8.1 Wrap the return of `Analytics\Settings::get_settings()` in `apply_filters( 'storesuite_analytics_settings', $settings )`.
- [x] 8.2 Wrap the analytics submenu items array in `DashboardMenu::add_dashboard_navigations()` with `apply_filters( 'storesuite_analytics_menu_items', $items )` before merging.
- [x] 8.3 Add `do_action( 'storesuite_before_analytics_app' )` and `do_action( 'storesuite_after_analytics_app' )` around the mount `<div>` in `templates/analytics/analytics.php`.
- [x] 8.4 Add `do_action( 'storesuite_before_dashboard_app' )` and `do_action( 'storesuite_after_dashboard_app' )` around the mount `<div>` in `templates/dashboard.php`.

## 9. Rewrites memoization

- [x] 9.1 Cache the `Rewrites` instance on `StoreSuite::$container['storesuite_query']` (or equivalent) so `get_storesuite_query()` returns the same instance across calls in a request.
- [ ] 9.2 Verify `init_query_vars()` runs only once per request via `did_action`/static counter or temporary debug log.

## 10. Verification

- [x] 10.1 Run `composer phpcs` and fix any new violations.
- [ ] 10.2 Run `npm run lint:js` and confirm no new warnings.
- [ ] 10.3 Build assets (`npm run build`) and load the dashboard + analytics page in three sessions; verify the scenarios in `specs/analytics-access-control/spec.md`.
- [ ] 10.4 Re-run Lighthouse on the dashboard root; confirm preload reductions and capability gate did not regress LCP.
- [ ] 10.5 Open a PR targeting `develop` referencing this change directory and the audit report.
