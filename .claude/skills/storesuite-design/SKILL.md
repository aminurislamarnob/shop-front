---
name: storesuite-design
description: StoreSuite frontend dashboard design system — design tokens, layout shell, cards, buttons, forms, tables, badges, dropdowns, switches, pagination, and modals shared by Products, Orders, Coupons, Categories, Tags, Brands, and Account pages. Use when adding a new page or component, restyling existing UI, or matching existing visual conventions. Excludes the dashboard home (React analytics widgets in src/dashboard/) and the Analytics page (src/analytics/) — those use WooCommerce components.
argument-hint: What to build or restyle (e.g. "add a new tags list page", "match button style on the brands form", "build a list view for variations")
---

# StoreSuite Design Skill

Task: $ARGUMENTS

Use this skill when working on **server-rendered frontend dashboard pages** (Products, Orders, Coupons, Categories, Tags, Brands, Account, plus any new sub-page rendered inside the dashboard shell). It does **not** apply to the dashboard home (which uses WooCommerce React widgets in `src/dashboard/`) or to the Analytics page (`src/analytics/`).

## Scope

| Applies to | Does NOT apply to |
|---|---|
| `templates/{products,orders,coupons,categories,tags,brands,account}/*.php` | `templates/dashboard.php` (React mount) |
| `templates/dashboard-header.php`, `dashboard-title.php`, `not-found.php`, `pagination.php` | `templates/analytics/*` (React mount) |
| `assets/frontend/style.css`, `assets/frontend/script.js`, `form-handler.js`, `order.js`, `product.js` | `src/dashboard/`, `src/analytics/` (React bundles) |
| WP admin settings (`src/admin.js`) — separate but uses same color tokens | |

## Design Tokens (CSS custom properties)

Defined at `:root` in `assets/frontend/style.css` (lines 15–34). Always reference these — never hardcode hex values for primary/text/border/icon.

```css
--body-font-size: 14px;
--storesuite-primary-bg: #2d5bdb;            /* CTA blue */
--storesuite-primary-bg-hover: #213fd4;      /* CTA blue hover */
--storesuite-text-black: #334155;            /* slate-700 — headings */
--storesuite-text-color: rgb(71 85 105);     /* slate-600 — body */
--storesuite-text-color-light: rgb(130 130 130);
--storesuite-icon-color: rgb(148 163 184);   /* slate-400 */
--storesuite-bg-color-light: #f7f7f7;
--storesuite-border-color: rgb(226 232 240); /* slate-200 */
--storesuite-button-text-color: #ffffff;
--storesuite-button-text-hover-color: #ffffff;
--storesuite-sidebar-bg-color: #ffffff;
--storesuite-sidebar-border-color: rgb(226 232 240);
--storesuite-sidebar-menu-text: rgb(71 85 105);
--storesuite-sidebar-active-text: #213fd4;
--storesuite-sidebar-active-background: #213fd4;
--storesuite-sidebar-width-expanded: 290px;
--storesuite-sidebar-width-collapsed: 72px;
```

### Typography

- Body: `14px` (`--body-font-size`)
- Page titles (`.storesuite-page-main-title`): `20px / 600`
- Card titles (`.storesuite-card-title`): `16px / 500`
- Form labels: `14px / 500`
- Form controls: `14px`
- Badges: `11px`
- Pagination links: `14px`

### Border Radius

- Buttons / form controls / cards / search inputs: **`6px`**
- Badges: **`6px`**
- Tables (responsive wrapper) / pagination links / checkboxes / thumbnails: **`5px`**
- Image drop / card-with-header overflow / WP editor: **`8px`** (legacy — prefer `6px` for new work)
- Pills inside switches: **`50%` / `26px`**

### Spacing

- Container: `padding: 24px` on `.woocommerce-layout__main` (admin)
- Card body padding: `24px` (`.storesuite-card`, `.storesuite-card-with-header .storesuite-card-content`)
- Form group margin-bottom: handled by Bootstrap-grid — use `.storesuite-mb-20` (20px) or `.storesuite-mb-24` (24px) helpers
- Table cell padding: `12px`
- Button padding: `10px 20px` (default), `0px 12px` height `25px` (table actions)
- Badge padding: `6px 10px`

### Grid System

Frontend pages use **Bootstrap 5 grid** (`assets/frontend/bootstrap-grid.min.css`). Always wrap layouts in `<div class="row">` + `<div class="col-md-X">`. Common patterns:

- Two-column form (main + sidebar): `col-md-8` + `col-md-4`
- Inline toolbar: `col-md-auto` (actions) + `col-md` (search) + `col-md-auto text-md-end` (buttons)
- Auto-fit row: `align-items-center` on `.row`

## Page Shell

Every dashboard sub-page follows this exact wrapper structure:

```php
<?php do_action( 'storesuite_dashboard_wrapper_start' ); ?>
<div class="my-storesuite-container">
    <aside id="storesuite-dashboard-sidebar" class="my-storesuite-sidebar"
           role="navigation"
           aria-label="<?php esc_attr_e( 'Store dashboard navigation', 'storesuite' ); ?>">
        <?php do_action( 'storesuite_dashboard_navigation' ); ?>
    </aside>
    <div class="my-storesuite-wrapper">
        <?php do_action( 'storesuite_dashboard_content_before' ); ?>
        <main class="my-storesuite-page-content">
            <?php do_action( 'storesuite_dashboard_before_main_content' ); ?>
            <!-- PAGE CONTENT HERE -->
        </main>
    </div>
</div>
<?php do_action( 'storesuite_dashboard_wrapper_end' ); ?>
```

- `.my-storesuite-container` — flex container with sidebar offset variables
- `.my-storesuite-sidebar` — fixed left nav, `290px` expanded / `72px` collapsed, white bg
- `.my-storesuite-wrapper` — main content area (offset by sidebar width)
- `.my-storesuite-page-content` — max-width `1536px` at `min-width: 1536px`, font-size `14px`

### Page Header / Breadcrumb

For any page below the dashboard root, render the title block with breadcrumb:

```php
<?php
storesuite_get_template_part(
    'dashboard-title',
    null,
    [
        'page_title'            => __( 'Edit product', 'storesuite' ),
        'parent_endpoint_title' => __( 'Products', 'storesuite' ),
        'parent_endpoint_url'   => storesuite_get_navigation_url( 'products' ),
    ]
);
?>
```

This produces:
- `.storesuite-dashboard-title-wrapper` (flex, space-between, `margin-bottom: 20px`)
- `.storesuite-page-main-title` (`h3`, `20px / 600`, slate)
- `.storesuite-dashboard-braedcrumb` (note legacy spelling) — inline list with chevron SVG separators, breadcrumb anchors in primary blue, current item in slate

## Cards

Two card variants:

### Plain card (no header)
```html
<div class="storesuite-card storesuite-mb-24">
    <!-- content -->
</div>
```
Background `#fff`, radius `6px`, padding `24px`.

### Card with header
```html
<div class="storesuite-card storesuite-card-with-header storesuite-mb-24">
    <h3 class="storesuite-card-title">Section title</h3>
    <div class="storesuite-card-content">
        <!-- form fields, etc. -->
    </div>
</div>
```
- Wrapper: white bg, radius `8px`, subtle shadow `0 1px 3px 0 rgba(0,0,0,.1), 0 1px 2px -1px rgba(0,0,0,.1)`, `padding: 0`, `overflow: hidden`
- Title: `16px / 500`, slate, `padding: 16px 24px`, bottom border `1px solid #e5e7eb`
- Content: `padding: 24px`

## Buttons

Primary class `.my-storesuite-button` — solid blue CTA. Use anchors or buttons interchangeably.

```html
<!-- Primary -->
<a href="..." class="my-storesuite-button">
    <svg width="16" height="16">…</svg>
    <?php esc_html_e( 'Add Product', 'storesuite' ); ?>
</a>

<!-- Secondary (light) -->
<button class="my-storesuite-button my-storesuite-button-light">
    <svg width="16" height="16">…</svg>
    Filter
</button>
```

| Variant | bg | text | border |
|---|---|---|---|
| Primary | `--storesuite-primary-bg` | white | matches bg |
| Hover | `--storesuite-primary-bg-hover` | white | matches bg |
| Light | `#fff` | `--storesuite-text-color-light` | `--storesuite-border-color` |
| Light hover | primary-bg-hover | white | primary-bg-hover |

Specs: `padding: 10px 20px`, `font-size: 14px`, `border-radius: 6px`, inline-flex centered. Inline SVG icons go **before** the label, `width/height: 11px` (`16px` for header buttons), `margin-right: 5px`, fill currentColor.

### Table-row actions
Inside `table .action-buttons`: smaller pills, `height: 25px`, `padding: 0 12px`, `font-size: 13px`, `border-radius: 5px`, white text. Built-in modifiers: `.edit` (slate `#34495e`), `.delete` (primary blue), `.view` (green `#27ae60`).

## Forms

Wrap each field in `.storesuite-form-group` (margin handled inside cards by Bootstrap rows or stacked spacing).

```html
<div class="storesuite-form-group">
    <label for="name">
        <?php esc_html_e( 'Name', 'storesuite' ); ?>
        <span class="req">*</span>
    </label>
    <input type="text" class="storesuite-form-control" id="name" name="name" />
    <small class="storesuite-form-text">Helper text…</small>
</div>
```

### `.storesuite-form-control`
- Width `100%`, height `40px`, padding `8px 12px`
- Border `1px solid var(--storesuite-border-color)`, radius `6px`
- Focus: border color `--storesuite-primary-bg`, no box-shadow
- Invalid (`.storesuite-field-invalid`): `border-color: #dc3545`, light red glow

### Required marker
`<span class="req">*</span>` — red asterisk (`#e74c3c`).

### Field errors
Inline error: `<span class="storesuite-field-error">…</span>` — red text, `13px`, margin-top `5px`.
Form-group invalid: add class `is-invalid` to `.storesuite-form-group` to flag inline editor / select wrappers.

### Toggle switch
```html
<div class="storesuite-form-group storesuite-form-switch">
    <input type="checkbox" class="storesuite-form-control" id="free_shipping" name="free_shipping" value="yes">
    <label for="free_shipping">Allow free shipping</label>
</div>
```
Track `40×20px`, knob `14×14px`, pill radius. Off `#ccc` → on `--storesuite-primary-bg`. Disabled drops to 50% opacity.

### Custom checkbox
```html
<label class="my-storesuite-checkbox">
    <input type="checkbox" class="my-storesuite-checkbox-input">
    <span class="my-storesuite-checkbox-back"></span>
    <span class="my-storesuite-tick">
        <svg width="16" height="16">…check…</svg>
    </span>
</label>
```
`16×16px`, white bg, slate border, primary-blue when checked. Used heavily in table column "select all" headers.

### Select2
Available via `select2` selector hooks; styled to match `.storesuite-form-control` height (`40px`) inside `.storesuite-main-dashboard` scope.

### WordPress editor (TinyMCE)
Wrap the toolbar/container — the skill auto-styles `.wp-editor-container` with `border-radius: 8px` and toolbar bg `#f5f5f5`.

## Tables

### Wrapper + table

```html
<div class="storesuite-table-responsive">
    <table class="my-storesuite-tbl my-storesuite-product-list-table">
        <thead><tr><th>…</th></tr></thead>
        <tbody><tr class="single-product-item"><td>…</td></tr></tbody>
    </table>
</div>
```

- `.storesuite-table-responsive` — `overflow-x: auto`, radius `4px`
- `.my-storesuite-tbl` — collapsed borders, white thead/tbody, no cell borders by default
- `th`: `padding: 10px`, uppercase, `font-weight: 600`, slate text
- `td`: `padding: 12px`, top-border `1px solid var(--storesuite-border-color)`
- First-column anchors: primary blue, underline on hover

### Toolbar above table

```html
<div class="storesuite-table-header-part">
    <div class="row align-items-center">
        <div class="col-md-auto"> <!-- bulk actions --> </div>
        <div class="col-md">      <!-- search --> </div>
        <div class="col-md-auto text-md-end"> <!-- buttons --> </div>
    </div>
</div>
```

### Search input
```html
<div class="storesuite-table-search-input">
    <div class="storesuite-table-search-icon">
        <svg width="20" height="20">…magnifier…</svg>
    </div>
    <input type="text" name="search_by" placeholder="Search…" />
</div>
```
White bg, border + radius `6px`, max-width `290px`, leading icon, focus removes border highlight.

### Action column
Hover-revealed inline links under each row. Wrap actions in `<div class="product-list-action">` — `opacity: 0` + `visibility: hidden` until parent `tr.single-product-item:hover`.

### Empty state
Use `templates/not-found.php` (`.storesuite-no-data-found`): centered, `padding: 100px 0`, `h2` `22px / 500` slate, lead paragraph in body slate, optional illustration above.

## Badges

```html
<span class="storesuite-badge">Default</span>
<span class="storesuite-badge storesuite-badge-success">In stock</span>
<span class="storesuite-badge storesuite-badge-danger">Out of stock</span>
<span class="storesuite-badge storesuite-badge-warning">Backorder</span>
<span class="storesuite-badge storesuite-badge-info">Featured</span>
```

| Variant | bg | text |
|---|---|---|
| default | `rgb(243 244 246)` | `rgb(107 114 128)` |
| success | `rgb(209 250 229)` | `rgb(16 185 129)` |
| danger | `rgb(255 228 230)` | `rgb(244 63 94)` |
| warning | `rgb(254 243 199)` | `rgb(245 158 11)` |
| info | `rgb(224 231 255)` | `rgb(99 102 241)` |

`6px` radius, `padding: 6px 10px`, `font-size: 11px`, `text-transform: capitalize`. (Order status colors are special-cased — `span.order-pending` orange, `.order-on-hold` purple, `.order-failed` salmon.)

## Dropdowns (kebab menu / header user menu)

```html
<div class="storesuite-dropdown">
    <span class="storesuite-dropdown-icon">⋮</span>
    <ul class="storesuite-dropdown-menu">
        <li><a href="…" class="dropdown-link"><svg/>Edit</a></li>
        <li><a href="…" class="dropdown-link"><svg/>Delete</a></li>
    </ul>
</div>
```
Menu hidden by default, absolute-positioned (`right: 0; top: 20px`), white bg, `5px` radius, drop shadow, `width: 120px`. Toggle visibility from JS by adding `display: block` (or removing `display: none`) to `.storesuite-dropdown-menu` — see `assets/frontend/script.js`. Hover state on `.dropdown-link` is solid primary-bg with white text.

## Pagination

Render via `storesuite_get_template_part( 'pagination', null, [...] )`.

```html
<div class="storesuite-pagination-wrap">
    <span class="storesuite-result-text">Showing 1–10 of 42</span>
    <ul class="storesuite-pagination">
        <li><a href="?page/1">1</a></li>
        <li><span class="current">2</span></li>
        <li><a href="?page/3">3</a></li>
    </ul>
</div>
```
Each link/current: white bg, `5px` radius, `32×32px` square, primary-blue + white on current/hover.

## Sidebar (Dashboard navigation)

Rendered by `DashboardMenu.php` via the `storesuite_dashboard_navigation` action. Width is a CSS variable (`--storesuite-sidebar-current-width`) that toggles between `--storesuite-sidebar-width-expanded` (290px) and `--storesuite-sidebar-width-collapsed` (72px) via the toggle in `dashboard-header.php`. White bg, right border, smooth `0.28s ease` width transition. Active item color: `--storesuite-sidebar-active-text`.

For changing the sidebar items themselves (not styling), use the **`dashboard-menu`** skill.

## Header

`dashboard-header.php` renders a sticky-ish bar with:
- Sidebar trigger (`.storesuite-sidebar-trigger`) — hamburger SVG, ARIA-controls the sidebar
- "Visit Home" external-link button (`.my-storesuite-button` variant)
- User dropdown (`.storesuite-dropdown.storesuite-header-dropdown`) — avatar `img` with Account / Logout items

Always render via `dashboard-header.php` — never duplicate the markup in a page template.

## Modals (bulk edit / quick edit)

Used in product bulk edit. Class structure: `.storesuite-product-bulk-modal-overlay` (host element) with `.storesuite-modal-fade` for transitions. `[hidden]` toggles visibility. Pattern is intentionally minimal — for new modals follow the same `.storesuite-modal-fade` pattern and use `.storesuite-card-with-header` inside for consistency.

## Off-canvas filters

Side-panel filter UIs are loaded via dedicated template parts (e.g. `templates/products/product-filters-offcanvas.php`, `templates/orders/order-filters-offcanvas.php`) and toggled by `.storesuite-filter-toggle` buttons. Reuse one of those templates as a starting point when adding filterable lists.

## Bulk-action feedback

After a bulk operation redirects with `?updated=N&skipped=N&...` query args, render the standard feedback block at the top of the page (see `templates/products/products.php` lines 25–98 for the canonical implementation). Wrap in `.storesuite-bulk-edit-feedback.storesuite-form-group` with `role="status"`, and use `.storesuite-text-success` for positive lines.

## Icons

All inline SVGs are pasted directly (no icon font). Default sizes:
- Buttons: `11×11px` (table), `16×16px` (header), `20×20px` (large)
- Header icons: `24×24px`
- Sidebar items: `16×16px`
- Dropdown items: `14×14px`

Use `fill="currentColor"` so they inherit text color, except for slate-400 default icon color (`fill: var(--storesuite-icon-color)`) on dropdown indicators.

For React (admin or dashboard) the project uses `@heroicons/react/24/outline`. **Do not** use heroicons in PHP templates — paste the SVG inline.

## JavaScript handlers

Frontend pages bind behaviors in `assets/frontend/`:
- `script.js` — sidebar toggle, dropdown toggles, generic UI
- `form-handler.js` — generic CRUD form submission with SweetAlert2 confirmations & WP REST `apiFetch` (localized as `storeSuiteFormHandler`)
- `order.js` — order edit / line items / select2 customer search (localized as `StoreSuite_Order`)
- `product.js` — product form / variations / image gallery (localized as `StoreSuite_Product`)

When adding a new page that needs interactivity, prefer **extending `form-handler.js`** with a new bind selector rather than creating a new entry script.

## Accessibility

- Sidebar `<aside>` must have `role="navigation"` and a translated `aria-label`
- Toggles that control panels must use `aria-expanded` + `aria-controls` (see `.storesuite-sidebar-trigger`)
- Bulk feedback regions use `role="status"`
- Keep `<label for>` paired with form control `id`s — never rely on visual order alone
- All decorative SVGs: `aria-hidden="true" focusable="false"`

## Responsive styles

All responsive (mobile/tablet) CSS lives in **`assets/frontend/responsive.css`** — never add media queries to `style.css`. It is registered as `storesuite_responsive_style` (depends on `storesuite_style`) and enqueued right after it in `includes/Assets.php`, so it overrides the desktop shell.

**Breakpoints — use only these two, and never repeat one:**
- `@media ( max-width: 767px )` — mobile + tablet. Matches the sidebar collapse logic in `global.js`, which activates at `>= 768px`.
- `@media ( max-width: 575px )` — phone-only refinements.

Put **every** rule for a breakpoint inside a **single** `@media` block. Do not open the same `@media ( max-width: 767px )` (or `575px`) more than once in the file. Don't invent other ad-hoc breakpoints for new work.

**Selectors:**
- Prefer a **unique, semantic class** over ID selectors or generic Bootstrap chains. If the markup only offers `#some-id`, `.row.justify-content-end`, or `.col-md-*` to hook onto, **add a purpose class to the template first** (e.g. `storesuite-products-toolbar`, `storesuite-toolbar-actions` / `-search` / `-bulk`) and style that.
- For rules shared across several modals, target the **common** `.storesuite-product-bulk-modal-overlay` / `.storesuite-product-bulk-modal-*` classes — not per-modal IDs like `#storesuite-product-bulk-edit-modal`.
- Reuse existing classes and design tokens, same as global CSS.

**Keep it lean:**
- Very few comments — short section labels only; skip comments where the selector is self-explanatory.
- When multiple selectors in the same media query share one identical declaration (e.g. `display: none`), **merge them into a single comma-separated selector list**.

**JS parity:** any JS that branches on viewport width must read `document.documentElement.clientWidth` (the layout viewport) so it matches the CSS media queries — not `window.innerWidth`. The sidebar threshold is `768`.

**Mobile list tables:** list tables collapse to **stacked cards** (hide `thead`, set `table/tr/td` to `display:block`, surface each cell's label via `td[data-title]::before`). Ensure every data `<td>` in the row template carries a `data-title`.

## Critical rules

1. **Use design tokens** — never hardcode `#2d5bdb`, `rgb(226 232 240)`, etc. Reference `var(--storesuite-*)`.
2. **Reuse classes** — `.storesuite-card`, `.my-storesuite-button`, `.storesuite-form-control`, `.storesuite-badge`, `.my-storesuite-tbl`, `.storesuite-pagination`. Don't invent parallel ones.
3. **Bootstrap 5 grid only** — `.row`/`.col-md-*`/`align-items-*`/`text-md-end`. No flex utilities outside that.
4. **Always go through the page shell** — never render a sub-page without the `my-storesuite-container > sidebar + my-storesuite-wrapper > my-storesuite-page-content` wrapper, and always fire the `storesuite_dashboard_*` actions in the right order.
5. **Title bar via `dashboard-title.php`** — pass `page_title`, optional `parent_endpoint_title` + `parent_endpoint_url`. Don't render `<h3 class="storesuite-page-main-title">` manually.
6. **Translate everything** — text domain `storesuite`. Use `__()`, `esc_html__()`, `esc_attr__()`, `_n()`. Escape on output.
7. **Inline SVG, never `<i>` icon fonts.**
8. **Templates are themable** — load via `storesuite_get_template_part( $template, null, $args )` so the theme's `my-storesuite/` directory can override them.
9. **Don't import this design system into the React bundles** — `src/dashboard/` and `src/analytics/` use WooCommerce components and Heroicons; only the shared CSS variables (`--storesuite-*`) are available there since `style.css` is also loaded.
10. **Test at `≥1536px`, `≤767px`, and `≤575px`** — page content caps at `1536px`. All responsive rules go in `assets/frontend/responsive.css` using the `≤767px` (mobile/tablet) and `≤575px` (phone) breakpoints only — see the **Responsive styles** section.

## Adding a new page (recipe)

1. Create the template `templates/{feature}/{feature}.php` mirroring `templates/products/products.php` shell.
2. Render the title via `storesuite_get_template_part( 'dashboard-title', null, [ 'page_title' => __( 'Things', 'storesuite' ) ] )`.
3. Add a toolbar with `.storesuite-table-header-part` (search + bulk + add button).
4. Wrap data in `.storesuite-table-responsive > table.my-storesuite-tbl` for lists, or `.storesuite-card.storesuite-card-with-header` for forms.
5. Append `<?php storesuite_get_template_part( 'pagination', null, [ ... ] ); ?>` for paginated lists.
6. Register a rewrite endpoint in `includes/Rewrites.php`, a Controller (`includes/{Feature}/Controller.php`) to handle the request, and add the menu item via the `dashboard-menu` skill.
