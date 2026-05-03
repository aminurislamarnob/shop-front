## Context

PR #130 ported the WooCommerce admin analytics React app to the StoreSuite frontend dashboard, shipping `src/analytics/`, `includes/Analytics/`, and a parallel set of dashboard widgets in `src/dashboard/`. The implementation follows Dokan's pattern (externalize `@woocommerce/*` to `window.wc.*`, lazy-load reports, inline-localize a preload payload). A post-merge audit revealed gaps the original PR did not cover:

1. The analytics route mounts and ships preload globals without checking the visitor's role.
2. The bootstrap shim that suppresses WC's `_doing_it_wrong` notice doesn't unwind cleanly on exception.
3. The REST permission whitelist for non-`manage_woocommerce` users is too narrow.
4. The preload transient is keyed per-user, multiplying storage and never busting on role change.
5. Several core extensibility hooks (settings filter, menu filter, mount actions) were not added, so third parties can't extend without forking.

The fix has to land before the feature is announced — the security gaps are user-visible and the extensibility gaps will become harder to add later (third-party code will start coupling to the current shape).

## Goals / Non-Goals

**Goals:**
- Block non-`manage_woocommerce` users from receiving the analytics bundle, preload payload, or template render.
- Make the bootstrap shim exception-safe so it can never silence sibling plugins' debug notices.
- Cover the full `/wc-analytics/*` route surface that async filter dropdowns rely on.
- Reduce preload transient storage and tie its lifetime to role changes, not just WC version.
- Add the missing extensibility hooks (filters + mount actions) so third parties can extend.

**Non-Goals:**
- Re-architecting the report registry or routing — `path:'*'` route consolidation is noted in the audit but deferred.
- Implementing the queued CSV download endpoint — out of scope for this change; tracked separately.
- JS-side runtime hooks around `indicators` post-fetch mutation — deferred.
- Multisite-wide rewrite flush.

## Decisions

### 1. Capability check location: template + asset enqueue, not just REST

**Decision:** Gate `Controller::load_template()`, `Analytics/Assets::enqueue_scripts()`, and `Dashboard/Assets::enqueue_scripts()` on `current_user_can( 'manage_woocommerce' )` and short-circuit early.

**Alternatives considered:**
- *REST-only enforcement* — relies on the `/wc-analytics/*` 401 to protect data. Rejected because the inlined preload payload (`storeSuiteAnalyticsSettings`) ships server-rendered HTML that's already exposed regardless of REST.
- *Body class redirect* — gate at the page level via `template_redirect`. Rejected because the dashboard page is shared with non-analytics endpoints; a hard redirect would break the rest of the dashboard for shop_managers (who already have `manage_woocommerce`).

The chosen layering keeps each enqueue independent and reads naturally next to the existing `storesuite_is_endpoint_url( 'analytics' )` guard.

### 2. `try/finally` for `WCAdminBootstrap::ensure()`

**Decision:** Wrap `WCAdminAssets::register_scripts()` in `try { } finally { remove_filter(...); }` so the `doing_it_wrong_trigger_error` filter is always removed.

PHP 7.4 supports `finally`. No alternative considered — this is a straightforward cleanup-on-exception pattern.

### 3. Preload transient key composition

**Decision:** Replace the per-user key with a key derived from `( WC_VERSION, hash of user's effective capabilities )`. Bust on `set_user_role`, `add_user_role`, `remove_user_role`, and `wc_updated`.

**Rationale:** All `manage_woocommerce` users see the same preload payload; storing one cache entry per user wastes object cache slots. Hashing capabilities (rather than role names) handles custom roles correctly.

**Alternatives considered:**
- *Single global key* — simpler but breaks if a future requirement exposes per-user data in the preload (e.g. saved filters). Rejected for forward compatibility.
- *Bust by user_meta version counter* — over-engineered; capability changes are rare and the hooks above cover them.

### 4. New StoreSuite-prefixed filter for preload endpoints

**Decision:** Stop reusing WC's `woocommerce_component_settings_preload_endpoints` filter; add `storesuite_analytics_preload_endpoints` and call both (Woo's first, then ours) so plugins can extend either surface independently.

**Rationale:** Re-using Woo's filter means a third-party plugin extending the WC admin analytics inadvertently extends StoreSuite's frontend payload, which is a context leak. A prefixed filter keeps the surfaces independent while still allowing opt-in passthrough.

### 5. Capability check on `Settings.php` preload generation

**Decision:** `Settings::get_settings()` and `Settings::get_preload_options()` already run from gated enqueue contexts. Don't double-check — but document that they are unsafe to call outside an authorized context, and make `Controller::load_template()` the single gate.

This avoids the temptation to silently filter the payload for low-cap users (which would mask permission bugs).

### 6. `Rewrites` instance memoization

**Decision:** Cache the `Rewrites` instance on `StoreSuite::$container` like the other services. Every consumer (DashboardMenu, functions.php endpoint helpers) already calls through `pluginizelab_storesuite()->get_storesuite_query()`.

The current code constructs a fresh `Rewrites()` per call, re-running `get_post()` and filter chains. A static singleton on the container fixes it without changing the public API.

## Risks / Trade-offs

- **Risk:** A capability change that doesn't fire one of `set_user_role` / `add_user_role` / `remove_user_role` (e.g. direct DB update) leaves stale preload data in the user's cache. → **Mitigation:** TTL stays at 15 minutes as a safety net; document the assumption.
- **Risk:** Tightening `RestPermissions::grant_read_access` whitelist to match WC's full surface could expose a route whose default permission is weaker than ours. → **Mitigation:** Only whitelist routes that already require `manage_woocommerce` upstream; never grant read access to routes that don't have a Woo permission callback. Test with a shop_manager user.
- **Risk:** Adding `do_action( 'storesuite_before_analytics_app' )` invites third parties to render output that breaks the React mount layout. → **Mitigation:** Document the contract — output must be wrapped in a `<div>` and styled to not displace `#storesuite-analytics-app`.
- **Trade-off:** Memoizing `Rewrites` means a filter that mutates `query_vars` after `init` no longer takes effect — but that filter (`storesuite_dashboard_query_vars`) already runs at `init` priority 10, so this is a non-issue in practice.

## Migration Plan

1. Land all changes behind a single PR targeting `develop`.
2. Bump plugin version (`STORESUITE_PLUGIN_VERSION`) — preload transient key already includes WC version, but a plugin-version bump forces a hard cache miss on update.
3. No DB migration needed. No setting migration needed.
4. Smoke-test as: anonymous user → 404/redirect; subscriber → no bundle; shop_manager → full bundle; admin → full bundle.
5. Rollback: revert the PR. No data is written that would need cleanup.

## Open Questions

- Should the analytics route render a "permission denied" message for logged-in non-admins, or 404 silently? Current dashboard endpoints use silent redirect; matching that is the proposed default.
- Whether `wc_updated` is the right hook for cache invalidation on WC updates, or if a `version_compare` check on every request is safer. Defer until first WC upgrade hits production.
