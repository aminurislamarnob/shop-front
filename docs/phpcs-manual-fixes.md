# PHPCS — Manual Fixes Required

Branch: `feat/product-csv-export` (compared against `develop`)

These PHPCS errors fall on lines changed in this branch and **cannot** be auto-fixed
by `composer phpcbf` — they need code changes and human judgement. The mechanical
issues (whitespace, indentation, function-call signatures) have already been fixed
with `phpcbf`.

Re-check after fixing with:

```bash
composer phpcs
```

**Summary:** 59 errors across 9 files, in three categories.

---

## 1. Security — `includes/ProductAttribute/AttributeController.php` (46 errors)

The highest-priority group. Form handlers read `$_POST`/`$_REQUEST` without a verified
nonce, and one value is used unsanitized.

### `WordPress.Security.NonceVerification.Missing` (44 occurrences)

Lines: **60–64, 111–116, 159, 184–187, 234–238, 285–286**

Each handler that processes submitted data must verify a nonce before touching
`$_POST`/`$_GET`/`$_REQUEST`. WooCommerce-style pattern:

```php
// On the form:  wp_nonce_field( 'storesuite_save_attribute', 'storesuite_attribute_nonce' );

if (
    ! isset( $_POST['storesuite_attribute_nonce'] )
    || ! wp_verify_nonce(
        sanitize_key( wp_unslash( $_POST['storesuite_attribute_nonce'] ) ),
        'storesuite_save_attribute'
    )
) {
    return; // or wp_die() / wp_send_json_error()
}
```

If a nonce **is** already verified earlier in the call chain (e.g. via
`check_ajax_referer()` in the AJAX entry point), annotate the read so the sniff knows:

```php
// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Verified in <method/hook>.
```

### `WordPress.Security.ValidatedSanitizedInput.InputNotSanitized` (2 occurrences)

Lines: **61, 113** — `$_POST['attribute_name']` used raw.

Wrap on read:

```php
$attribute_name = isset( $_POST['attribute_name'] )
    ? sanitize_text_field( wp_unslash( $_POST['attribute_name'] ) )
    : '';
```

> ⚠️ These change auth/validation behaviour — test attribute create/edit/delete and
> term create/edit/delete flows after fixing.

---

## 2. `WordPress.WP.GlobalVariablesOverride.Prohibited` (12 errors)

Templates assign to variables that collide with WordPress core globals
(`$taxonomy`, `$term`, `$tax`). Rename the locals to the `storesuite_` prefix used
elsewhere in the templates, and update every reference within the same file.

| File | Line | Variable | Suggested rename |
|------|------|----------|------------------|
| `templates/attributes/attribute-terms.php` | 14 | `$taxonomy` | `$storesuite_taxonomy` |
| `templates/attributes/attribute-terms.php` | 147 | `$term` | `$storesuite_term` |
| `templates/attributes/attributes.php` | 72 | `$taxonomy` | `$storesuite_taxonomy` |
| `templates/attributes/attributes.php` | 82 | `$term` | `$storesuite_term` |
| `templates/attributes/edit-attribute-term.php` | 14 | `$taxonomy` | `$storesuite_taxonomy` |
| `templates/attributes/edit-attribute-term.php` | 25 | `$term` | `$storesuite_term` |
| `templates/products/product-attribute-row.php` | 52 | `$term` | `$storesuite_term` |
| `templates/products/product-attribute-row.php` | 99 | `$term` | `$storesuite_term` |
| `templates/products/product-attributes.php` | 62 | `$tax` | `$storesuite_tax` |
| `templates/products/product-variation-row.php` | 64 | `$term` | `$storesuite_term` |
| `templates/products/product-variations.php` | 144 | `$term` | `$storesuite_term` |

Example:

```php
// Before
$term = get_term( $term_id, $taxonomy );
// After
$storesuite_term = get_term( $term_id, $storesuite_taxonomy );
```

---

## 3. `Universal.Operators.DisallowShortTernary.Found` (2 errors)

Replace the short ternary `?:` with a full ternary (or `??` when checking for
existence/null).

| File | Line |
|------|------|
| `includes/REST/SettingsController.php` | 158 |
| `templates/products/product-attribute-row.php` | 74 |

```php
// Before
$value = $input ?: 'default';
// After
$value = ! empty( $input ) ? $input : 'default';
// or, if guarding against null/undefined:
$value = $input ?? 'default';
```

---

## Not in scope (false positives)

`current_user_can( 'manage_woocommerce' )` / `'edit_products' )` raise
`WordPress.WP.Capabilities.Unknown` warnings — these are valid WooCommerce
capabilities the sniff doesn't know about. Either leave them, or register them in
`phpcs.xml` via the `custom_capabilities` property to silence.
