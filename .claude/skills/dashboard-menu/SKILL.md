---
name: dashboard-menu
description: Add, modify, or debug the StoreSuite frontend dashboard sidebar navigation menu. Use when the user asks to add a menu item, add a submenu, change a menu URL or icon, fix active-state highlighting, or anything related to DashboardMenu.php.
argument-hint: What to change (e.g. "add submenu to Coupons", "add a new Reports item")
---

# StoreSuite Dashboard Menu Skill

Task: $ARGUMENTS

## Key Files

| File | Role |
|------|------|
| `includes/DashboardMenu.php` | All menu data and HTML rendering — primary file |
| `includes/Rewrites.php` | Defines all rewrite endpoint slugs (`init_query_vars`) and `get_current_endpoint()` |
| `includes/functions.php` | `storesuite_get_navigation_url( $slug )` URL helper |
| `assets/frontend/style.css` | Sidebar and submenu styles |
| `assets/frontend/script.js` | Submenu toggle behaviour |

Read `includes/DashboardMenu.php` in full before making any changes.

## Menu Entry Shape

Each top-level menu item in `get_dashboard_menus()`:

```php
'slug' => [
    'title'      => __( 'Label', 'storesuite' ),
    'icon'       => '<svg .../>',                         // inline SVG only
    'url'        => storesuite_get_navigation_url( 'slug' ),
    'pos'        => 30,
    'permission' => 'manage_woocommerce',
    'target'     => '_self',
    'submenu'    => [ /* optional */ ],
]
```

The full array is filterable: `apply_filters( 'storesuite_dashboard_menus', $menus )`.

## Submenu Entry Shape

```php
'submenu-slug' => [
    'title'      => __( 'Label', 'storesuite' ),
    'url'        => storesuite_get_navigation_url( 'endpoint-slug' ),
    'permission' => 'manage_woocommerce',
    'endpoint'   => 'endpoint-slug',   // drives active-state detection
]
```

## Active-State Rules

### Submenu active state
- **With `endpoint` key** — compared against `pluginizelab_storesuite()->get_storesuite_query()->get_current_endpoint()`. Use for all standard rewrite-endpoint submenus.
- **Without `endpoint` key** — compared against `$_GET['report']` matched to the subkey. Used **only** by Analytics submenus (overview, revenue, orders, products, variations, categories, stock).

### Parent active state (`get_active_menu`)
`get_active_menu()` parses `$wp->request` and maps sub-page endpoints back to their parent slug via `$endpoint_to_parent`. When adding a new section that has edit or add-new pages, add entries to that map:

```php
$endpoint_to_parent = [
    'add-new-product'  => 'products',
    'edit-product'     => 'products',
    'add-new-order'    => 'orders',
    'edit-order'       => 'orders',
    'order-details'    => 'orders',
    'add-new-category' => 'categories',
    'edit-category'    => 'categories',
    'add-new-brand'    => 'brands',
    'edit-brand'       => 'brands',
    'add-new-tag'      => 'tags',
    'edit-tag'         => 'tags',
    'add-new-coupon'   => 'coupons',
    'edit-coupon'      => 'coupons',
];
```

## Critical Rules

1. **Always use `storesuite_get_navigation_url( $slug )`** — never hardcode URLs.
2. **Verify the slug exists** in `Rewrites::init_query_vars()` before using it. Do not invent slugs.
3. **Get current endpoint** via `pluginizelab_storesuite()->get_storesuite_query()->get_current_endpoint()` — do NOT use `pluginizelab_storesuite()->rewrites` or `pluginizelab_storesuite()->storesuite_rewrites`, they return null.
4. **SVG icons** must pass through `wp_kses( $icon, $this->allowed_icon_tags() )`. If you add SVG attributes not in that allowlist, add them to `allowed_icon_tags()` first.
5. **When adding a new top-level item** with add-new or edit sub-pages, update `$endpoint_to_parent` in `get_active_menu()`.

## Checklist Before Finishing

- [ ] Slug exists in `Rewrites::init_query_vars()`
- [ ] URL uses `storesuite_get_navigation_url()`
- [ ] Submenu items have `endpoint` key (unless Analytics-style `?report=` items)
- [ ] `$endpoint_to_parent` updated if new edit/add-new endpoints introduced
- [ ] Any new SVG attributes added to `allowed_icon_tags()`
