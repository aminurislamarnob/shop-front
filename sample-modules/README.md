# Sample modules

This directory holds **reference implementations only**. Nothing in here is
discovered, built, or shipped:

- `Module\Manager` only scans `modules/*/module.php`, so these modules never
  appear on the Modules screen and cannot be activated.
- Webpack only globs `modules/*/src/index.js`, so their React sources are
  never bundled.
- The whole directory is excluded from release ZIPs via `.distignore`.

## staff-manager

The canonical example used by `docs/how-to-add-module.md`. It demonstrates the
full module contract in its simplest form: a custom DB table created on
activation, per-module settings, a dashboard rewrite endpoint + sidebar menu,
a module-owned REST controller, and a top-level React admin screen registered
through the `storesuite_admin_routes` filter.

## Turning a sample into a real module

Copy the folder into `modules/`:

```bash
cp -R sample-modules/staff-manager modules/my-module
```

Then rename the slug, namespace, class names, option keys, and table names —
`docs/how-to-add-module.md` walks through every step. Once it lives under
`modules/`, discovery, the webpack build, and the Modules admin screen all
pick it up automatically.
