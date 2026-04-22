# StoreSuite Admin Settings — Redesign Plan

Adopt the `wp-change-email-sender` React admin design for the StoreSuite
admin settings page without altering any business logic, field keys, or
REST endpoints.

## Design of `wp-change-email-sender` admin panel

### Layout
- **Top header bar** (full-width, white, border-bottom) — icon + title +
  subtitle on the left, action buttons (Documentation / Support Me) on
  the right. Component: `SettingsHeader`.
- **No side nav.** A horizontal tab bar sits under the header, built
  with `react-router-dom` `<Link>` (bottom-border active state, purple
  `#5539FD`). Content is centered with `max-width: 1200px`.
- **Section pattern** — each route renders two stacked cards:
  1. `wpces-form-header-card` → section title + description
  2. body card → form controls + submit button

### State & UX patterns
- **`SettingsContext`** (React Context Provider) wraps the app. A single
  `apiFetch` on mount populates shared `settings` and exposes
  `{ settings, isLoading, isSaving, saveSettings }`. Every tab reads and
  writes through it — no per-tab fetch/save duplication.
- **Global snackbar notices** via `@wordpress/notices` store +
  `<SnackbarList>` anchored at bottom-center. Success/error use
  `CheckBadgeIcon` / `ExclamationCircleIcon` from heroicons. Replaces
  inline `<Notice>` banners.
- **Skeleton loader** while `isLoading` — placeholder tab bars + spinner
  card.
- **Brand tokens** — primary `#5539FD`, hover `#635BFF`, toggle thumb
  `#0A2540`.

## StoreSuite today (baseline)

- **Left sidebar nav** (225px, logo + 3 menu items with icon + title +
  description) — will be replaced by the top bar + tabs.
- Each tab fetches settings independently and keeps its own
  `isLoading` / `message` / `error` state. `<Notice>` banners render
  inline per tab.
- Each tab has an in-content icon+title header (`.settings-header` with
  left accent bar) — will be replaced by the shared top header bar.
- `GeneralSettings` additionally fetches `/wp/v2/pages` for the
  dashboard page picker.
- Already uses heroicons, `@wordpress/components`, and
  `react-router-dom` HashRouter.

Dependencies already align — no `package.json` changes needed.

## Implementation plan (no logical change)

**Scope rule:** keep every existing field key, `apiKey`, API endpoint,
and child component (`DashboardSidebarImageControl`, the
color/pagination/perf-box/widget lists, the `/wp/v2/pages` fetch). Only
layout, state-sharing, and notice UX change.

### 1. Add shared state layer — `src/context/SettingsContext.js` (new)
- Port the wp-change-email-sender pattern 1:1, pointed at
  `/storesuite/v1/settings`.
- Exports `SettingsProvider` + `useSettings()`. Provides
  `{ settings, isLoading, isSaving, saveSettings }`.
- `saveSettings(data, customMessage?)` POSTs to the settings endpoint,
  updates local state from the response, and dispatches a snackbar
  success/error notice (with heroicon).
- Text domain: `storesuite`. Notice IDs prefixed `storesuite-*`.

### 2. Rework `src/admin.js`
- Wrap `<Router>` in `<SettingsProvider>`.
- Keep the same three routes (`/`, `/appearance-settings`,
  `/pagination-settings`) and the container ID `storesuite-settings`.

### 3. Add `src/Components/SettingsHeader.js` (new)
- 1:1 port — renders icon, title, subtitle, optional actions slot.
  Class names renamed to `storesuite-*`.

### 4. Rewrite `src/Components/Layout.js`
- Drop the sidebar + logo block.
- Render `<SettingsHeader>` with a StoreSuite icon (e.g. `GearIcon` or
  add `Squares2X2Icon`), title `"StoreSuite"`, a subtitle such as
  "Configure your frontend dashboard pages, appearance, and
  pagination.", and actions (Documentation / Support links — keep or
  omit if no URLs yet).
- Below the header: a horizontal tab nav with three `<Link>`s (General
  / Appearance / Pagination), using `useLocation` for active state.
- Inside the content body: render `<Outlet />` when loaded, or the
  skeleton tabs + spinner card while `useSettings().isLoading`.
- Mount the global `<SnackbarList>` here (filtered to
  `type === 'snackbar'`).

### 5. Refactor `src/Components/GeneralSettings.js`
- Remove the local `apiFetch` for `/storesuite/v1/settings`, the local
  `isLoading` / `message` / `error` state, and the inline `<Notice>`
  blocks.
- Read initial values from `useSettings().settings` (derive once via
  `useState` initializers, same shape as before).
- Keep the local `/wp/v2/pages` fetch — this is tab-local data, not
  plugin settings.
- On submit: build the same payload (unchanged `storesuite_*` keys +
  yes/no mapping), call `saveSettings(payload)`. Success/error
  snackbars come from the provider.
- Replace the in-content `.settings-header` with the
  `wpces-form-header-card` + body-card pattern — start with a single
  header card per tab and keep inner section groupings via
  `storesuite-settings-sec-header` to minimise churn.
- Submit button shows `<Spinner>` when `isSaving`.

### 6. Refactor `src/Components/ColorsSettings.js` and `PaginationSettings.js`
- Same pattern as above: drop the local fetch + notices, derive initial
  state from `useSettings().settings`, call `saveSettings` on submit.
- Wrap in the `wpces-form-header-card` + body-card layout. The
  `ColorControl` internals are untouched.

### 7. Keep `src/Components/DashboardSidebarImageControl.js` as-is
- It's a pure controlled input — no logic change needed.

### 8. Rewrite `src/Components/LayoutStyles.css`
- Remove left-sidebar rules (`.storesuite-sidebar-nav`,
  `.sidebar-logo`, `.menu-icon`, the `::before` accent bar on
  `.settings-header`).
- Port wp-change-email-sender styles under the `storesuite-*` prefix:
  `storesuite-admin-app`, `storesuite-header-wrapper`,
  `storesuite-main-content storesuite-setting-wrapper`,
  `storesuite-content-body`, `storesuite-hash-nav` (with `.is-active`
  purple underline), `storesuite-section` (700–1200px clamp),
  `storesuite-form-header-card`, `storesuite-form-section-body`,
  `storesuite-settings-group`, skeleton tab animation, snackbar
  positioning.
- Keep existing `storesuite-color-*`, `storesuite-colors-form-wrapper`,
  `storesuite-sidebar-image-control__*` rules — already compatible.
- Scope the wrap override to StoreSuite's admin screen:
  `.woocommerce_page_storesuite #wpcontent { padding-inline-start: 0; }`.

### 9. Icon additions — `src/Components/icons.js`
- Re-export `CheckBadgeIcon` and `ExclamationCircleIcon` from
  `@heroicons/react/24/outline` (used by the provider's snackbars).
- Pick a header icon (`Squares2X2Icon`, or reuse `GearIcon`).

### 10. No PHP changes required
- `includes/Admin/Settings.php` container ID stays `storesuite-settings`.
- `includes/Assets.php` enqueue is unchanged — the build output path is
  the same.
- `REST/SettingsController` is unchanged — same endpoint, same keys.

### 11. Verification
- `npm run build` — confirm the bundle builds cleanly.
- Load `wp-admin/admin.php?page=storesuite` and verify:
  - Skeleton shows briefly, then tabs + content render.
  - Switching tabs doesn't re-fetch settings (shared context).
  - Saving on each tab shows a bottom-center snackbar with checkmark
    icon; fields repopulate from the response.
  - `/wp/v2/pages` dropdown still lists pages on the General tab.
  - Media picker still works for sidebar logo/icon.
  - The StoreSuite frontend dashboard still reflects saved colors,
    pagination, and performance-box toggles (logic untouched).

**Out of scope:** changing any `storesuite_*` option key, REST route,
or child control behaviour; introducing lazy chunks / `public-path.js`
(not needed); altering the PHP settings page or menu registration.
