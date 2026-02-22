# Re: StoreSuite - Plugin Review Fixes Applied

Hi Plugin Review Team,

Thank you for taking the time to review StoreSuite and for the detailed feedback. I have carefully gone through each point and applied all the necessary fixes. Here is a summary of the changes made:

## 1. Proper Sanitization of Inputs

- Replaced all uses of `intval()` and `floatval()` with `absint()` and proper `sanitize_text_field( wp_unslash() )` wrappers across all controller files (OrderController, BrandController, TagController, CategoryController).
- Added `sanitize_key()` and `wp_unslash()` to all nonce values before passing them to `wp_verify_nonce()`.
- Sanitized all `$_POST` and `$_GET` superglobal accesses, including `$_POST['note_id']`, `$_POST['shipping_cost']`, `$_POST['context']`, `$_POST['bulk_order_ids']`, and `$_GET['claim-lock']`.
- Removed the use of `extract()` in the template loader function and replaced it with direct array access.
- Ensured all data passed to manager classes (ProductManager, CouponManager) is properly sanitized at the controller level.

## 2. Proper Escaping of Outputs

- Wrapped all `format_value()` output calls in dashboard templates with `wp_kses_post()` (store-performance, top-products, top-categories, top-customers, top-coupons).
- Replaced `htmlspecialchars()` with proper WordPress escaping function `esc_html()` in order filter templates.
- Escaped the CSS variable output in `Main.php` using `esc_attr()`.
- Added `esc_attr()` to unescaped HTML attributes (e.g., coupon form `id`).
- Used `esc_url()` and `esc_html__()` in `plugin_action_links()`.
- Removed all `phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped` comments by properly escaping the associated output.

## 3. Nonces and User Permissions Before Processing Requests

- Added `current_user_can( 'manage_woocommerce' )` permission check to the order bulk actions handler.
- Created a proper AJAX handler for the product delete action (`storesuite_wfm_trash_product_action`) with full nonce verification and capability check.
- Properly sanitized all nonce field values before verification.

## 4. Use Prefixes for Declarations, Globals, and Stored Data

- Renamed the unprefixed global function `is_storesuite_dashboard_page()` to follow the `storesuite_` prefix convention and updated all references across the codebase.
- Removed the unused hardcoded `STORESUITE_NONCE_SALT` constant.

## 5. Use `wp_enqueue` Commands

- Replaced inline `<style>` tags output via `wp_head` with `wp_add_inline_style()` attached to the registered `storesuite_style` handle.
- Bundled the Google Fonts (Poppins) locally within the plugin assets instead of loading from an external CDN.

---

All changes have been committed and the updated plugin is ready for re-review. Please let me know if anything else needs to be addressed.

Best regards,
Aminur Islam Arnob
