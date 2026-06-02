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

## 3. `Universal.Operators.DisallowShortTernary.Found` (2 errors)

Replace the short ternary `?:` with a full ternary (or `??` when checking for
existence/null).

| File                                           | Line |
| ---------------------------------------------- | ---- |
| `includes/REST/SettingsController.php`         | 158  |
| `templates/products/product-attribute-row.php` | 74   |

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
