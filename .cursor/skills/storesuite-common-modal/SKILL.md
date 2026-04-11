---
name: storesuite-common-modal
description: >-
  Documents StoreSuite.shared modal API (StoreSuite.storeSuiteModal) for overlay
  dialogs with a11y and optional fade. Use when adding or changing dashboard
  modals in the storesuite plugin, wiring Escape/Tab/backdrop/close behavior, or
  when the user mentions reusable modal, storeSuiteModal, or modal fade in
  StoreSuite frontend assets.
---

# StoreSuite common modal (`storeSuiteModal`)

## When to use this

- Adding a new overlay modal on the StoreSuite dashboard (jQuery).
- Refactoring custom modal keydown/click logic: prefer the shared helper instead of duplicating Escape, Tab trap, backdrop close, and focus restore.

## Prerequisites

- **Script**: `assets/frontend/script.js` defines `window.StoreSuite.storeSuiteModal` and assigns it next to `storeSuiteLoader`.
- **Load order**: Any script that calls `storeSuiteModal` must run **after** `storesuite_script` is enqueued. For product dashboard code, `storesuite_product_script` already lists `storesuite_script` as a dependency in `includes/Assets.php`.
- **Guard**: If the modal markup is optional, check `window.StoreSuite && window.StoreSuite.storeSuiteModal` before use.

## Markup contract

1. **Root overlay**: One element that receives `hidden` and `aria-hidden` toggles (e.g. `<div id="my-modal" class="my-modal-overlay" hidden aria-hidden="true">`).
2. **Dialog region**: A descendant with `role="dialog"`, `aria-modal="true"`, a labelled title (`aria-labelledby`), and typically `tabindex="-1"` so focus can land on the dialog if no focusable control matches.
3. **Close controls**: Buttons (or links) matched by your `closeSelector` passed to `initOverlay` (delegated clicks call `close()`).

Default close class names (if you omit `closeSelector`): `.storesuite-modal-cancel`, `.storesuite-modal-close`.

## API (read before implementing)

Call **`initOverlay` once** per overlay (guarded internally with `storesuite-modal-a11y-bound`).

| Method | Purpose |
|--------|---------|
| `initOverlay($overlay, options)` | Escape closes, Tab cycles within dialog, backdrop click on root closes, delegated `closeSelector` clicks close. Options: `dialogSelector` (default `[role="dialog"]`), `closeSelector`, `fade` (boolean). |
| `open($overlay, options)` | Saves `document.activeElement`, removes `hidden`, sets `aria-hidden="false"`, focuses first focusable or dialog node. Options: `dialogSelector`. |
| `close($overlay)` | Sets `hidden` / `aria-hidden`, restores focus (after fade transition if `fade` was enabled). |
| `getFocusables($overlay, dialogSelector)` | Rarely needed; used internally for Tab trap. |
| `isOpen($overlay)` | `!$overlay.prop('hidden')`. |

Programmatic open/close: `StoreSuite.storeSuiteModal.open($modal)` / `.close($modal)`.

## Standard wiring pattern

```javascript
var modal = window.StoreSuite && window.StoreSuite.storeSuiteModal;
var $modal = $( '#my-feature-modal' );

if ( ! modal || ! $modal.length ) {
	return;
}

modal.initOverlay( $modal, {
	closeSelector: '.my-modal-close, .my-modal-cancel',
	fade: true, // optional; see CSS below
} );

// Your feature-specific code (e.g. submit intercepted, then):
modal.open( $modal );
```

Keep **feature-specific** logic (forms, AJAX, hidden field IDs) in the feature script; keep **generic** a11y and overlay behavior in `initOverlay` / `open` / `close`.

## Fade animation (`fade: true`)

1. Pass **`fade: true`** in `initOverlay` options (adds class `storesuite-modal-fade` and internal data).
2. Add CSS so `[hidden]` does not use `display: none` on that overlay (opacity/visibility must transition). Reference implementation: `assets/frontend/style.css` — search for **`storesuite-modal-fade`** and **`.storesuite-product-bulk-modal-overlay`** (non-fade overlays keep `display: none` via `[hidden]:not(.storesuite-modal-fade)`).

Include **`prefers-reduced-motion: reduce`** when defining transitions (see existing rule on `.storesuite-modal-fade`).

## Reference implementation

- **Helper**: `assets/frontend/script.js` — object `storeSuiteModal`, exposed as `window.StoreSuite.storeSuiteModal`.
- **Consumer**: `assets/frontend/product.js` — `initProductBulkEditModal` calls `initOverlay` with `fade: true` and product-specific `closeSelector`; `openProductBulkModal` fills fields then `modal.open( $modal )`.
- **Template**: `templates/products/product-bulk-edit-modal.php`.

## Checklist for new modals

- [ ] Overlay root uses `hidden` + `aria-hidden` when closed.
- [ ] Inner panel has `role="dialog"` and correct `aria-*` labelling.
- [ ] `initOverlay` called once with the right `closeSelector` (and `fade` + CSS if animating).
- [ ] Enqueued script depends on `storesuite_script` if it uses `StoreSuite.storeSuiteModal`.
- [ ] No second copy of Escape/Tab/backdrop handlers on the same overlay.
