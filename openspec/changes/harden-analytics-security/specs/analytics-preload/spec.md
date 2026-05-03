## ADDED Requirements

### Requirement: Preload transient keyed by capability set, not by user

The REST preload transient written by `Analytics\Settings::load_preload_endpoints()` SHALL be keyed by a hash of the current user's effective capability set together with the active WooCommerce version, not by user ID. This keeps storage proportional to the number of distinct capability sets (typically 1–3) instead of the number of users.

#### Scenario: Two admins request the analytics page
- **WHEN** two distinct users with identical capability sets load the analytics page within the TTL window
- **THEN** they SHALL share the same transient entry and only one preload network round-trip SHALL run.

#### Scenario: WooCommerce is upgraded
- **WHEN** WooCommerce is updated to a new version
- **THEN** the previous transient key SHALL miss and a fresh preload SHALL be generated on the next request.

### Requirement: Preload cache invalidates on role/capability changes

The preload transient SHALL be busted when a user's role or capability changes. Implementation MUST hook into `set_user_role`, `add_user_role`, and `remove_user_role` to delete the affected user's cached entry.

#### Scenario: Admin demotes a shop manager to subscriber
- **WHEN** a user is moved from `shop_manager` to `subscriber` via `set_user_role`
- **THEN** any cached preload payload bound to that user's prior capability hash SHALL be deleted, so a subsequent unauthorized request cannot read stale data from the cache.

#### Scenario: TTL safety net for direct DB capability changes
- **WHEN** a user's capabilities are changed via direct database update (bypassing the role hooks)
- **THEN** the existing 15-minute TTL SHALL still cause the cached payload to expire.

### Requirement: Preload skips hidden dashboard widgets

`Dashboard\Assets::enqueue_scripts()` SHALL inspect the `storesuite_dashboard_settings` option (or its filtered equivalent) and SHALL skip preloading endpoints whose corresponding widgets are disabled. Specifically, when `performance_indicators` is hidden the `performance-indicators` endpoint MUST NOT be preloaded; when `leaderboards` is hidden the `leaderboards` endpoint MUST NOT be preloaded.

#### Scenario: Performance widget is disabled in settings
- **WHEN** the `performance_indicators` widget is hidden via `storesuite_dashboard_settings`
- **THEN** `Settings::get_settings()` SHALL be called without `'performanceIndicators'` in the needed-endpoints list and the preload payload SHALL omit it.

#### Scenario: Both widgets enabled
- **WHEN** both widgets are enabled
- **THEN** the existing preload behaviour SHALL be preserved.

### Requirement: StoreSuite-prefixed preload endpoints filter

The PHP filter applied to the preload endpoints map SHALL be `storesuite_analytics_preload_endpoints`. The legacy `woocommerce_component_settings_preload_endpoints` filter SHALL still be applied first (to preserve compatibility with WC-aware extensions) but the StoreSuite-prefixed filter SHALL be the supported extension point for the StoreSuite frontend.

#### Scenario: Third-party adds a custom endpoint
- **WHEN** a plugin hooks into `storesuite_analytics_preload_endpoints` and appends `[ '/storesuite/v1/custom' => [] ]`
- **THEN** the rendered preload payload on the analytics page SHALL include the custom endpoint's response (subject to capability checks).

#### Scenario: Third-party hooks only into the WC filter
- **WHEN** a plugin hooks into `woocommerce_component_settings_preload_endpoints` only
- **THEN** the StoreSuite preload payload SHALL still pick the change up so existing integrations keep working.

### Requirement: Script translations registered with languages path

`wp_set_script_translations()` calls in `Analytics\Assets` and `Dashboard\Assets` SHALL pass the plugin's `languages/` directory as the third argument so `.json` companion translation files are discovered.

#### Scenario: Locale with translation .json file
- **WHEN** a translation `.json` file exists under `STORESUITE_DIR/languages/` for the active locale and bundle
- **THEN** WordPress SHALL load it and `__( ..., 'storesuite' )` strings on the React side SHALL render translated.
