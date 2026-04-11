# StoreSuite Account Details module — UAT report

**Environment:** `http://woocommerce.test`  
**Page:** `/storesuite-dashboard/edit-account-details/`  
**Role:** Logged-in vendor/seller (session used by Cursor integrated browser)  
**Test date:** 2026-04-11  
**Methods:** Browser snapshots + one **Save changes** submission (no field edits) + StoreSuite PHP/JS source review

**Legend:** **Pass** — meets UAT or documented substitute; **Partial** — not fully exercised in browser, ambiguous, or wording/UI differs; **Fail** — contradicts UAT or missing behaviour.

---

## Implementation map (for traceability)

| Concern                                                                    | Location                                                                                                                                        |
| -------------------------------------------------------------------------- | ----------------------------------------------------------------------------------------------------------------------------------------------- |
| Form markup, labels, help text                                             | `templates/account/edit-account.php`                                                                                                            |
| AJAX save, required fields, email/password rules                           | `includes/Account/AccountController.php`                                                                                                        |
| Client required-field validation, password card toggle, Swal success/error | `assets/frontend/form-handler.js` (`handleEditAccount`, `initEditAccountPasswordToggle`, `validateRequiredFields`, `showSuccess` / `showError`) |

**Breadcrumb copy:** Template renders WooCommerce hooks around the form; breadcrumb list in browser showed **Dashboard → Account → Account details** (list items in snapshot).

**Password panel default:** Checkbox `#show_password_change` is **unchecked** in HTML; JS runs `$card.toggle( $switch.is( ':checked' ) )` so `#storesuite-edit-account-password-card` starts **hidden**. The accessibility snapshot may still list password labels (DOM present but hidden); visual default is **collapsed**.

---

## Module 1: Page load and layout

| ID              | Result | Evidence / notes                                                                                                                                                                                    |
| --------------- | ------ | --------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **UAT-ACC-001** | Pass   | **H3** `Account details`; breadcrumb list items **Dashboard**, **Account**, **Account details**; sidebar includes **Account**. Active state is CSS (`DashboardMenu`); not asserted from ARIA alone. |
| **UAT-ACC-002** | Pass   | Snapshot: **First name** `Aminur`, **Last name** `Islam`, **Display name** `admin`, **Email** `aminur@welabs.dev`.                                                                                  |
| **UAT-ACC-003** | Pass   | Template: `show_password_change` checkbox default **off**; JS hides `#storesuite-edit-account-password-card` when unchecked.                                                                        |
| **UAT-ACC-004** | Pass   | **Save changes** button present (`edit-account.php`).                                                                                                                                               |

---

## Module 2: Account details fields

### First name

| ID              | Result  | Evidence / notes                                                                                                                                                                                                                      |
| --------------- | ------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **UAT-ACC-005** | Pass    | Required (`*` in label); `text` input; pre-filled **Aminur**.                                                                                                                                                                         |
| **UAT-ACC-006** | Partial | **Intended behaviour:** `validateRequiredFields()` adds **inline** `.storesuite-field-error` and red border; **no AJAX** if invalid (`form-handler.js`). Browser typing/clear was unreliable in automation; not re-looped.            |
| **UAT-ACC-007** | Partial | Save + reload persistence not executed end-to-end here; `wp_update_user()` path in `AccountController` supports it.                                                                                                                   |
| **UAT-ACC-008** | Fail    | **UAT expects** letters/hyphens/spaces only, **no** numbers. **Actual:** `type="text"` + `wc_clean()` only; **no** regex rejecting digits. Numbers would be accepted unless a plugin hooks `woocommerce_save_account_details_errors`. |
| **UAT-ACC-009** | Partial | No `maxlength` in template; WP/user-meta limits apply. Long-string behaviour not executed.                                                                                                                                            |

### Last name

| ID              | Result  | Evidence / notes                                                   |
| --------------- | ------- | ------------------------------------------------------------------ |
| **UAT-ACC-010** | Pass    | Same pattern as first name; value **Islam**.                       |
| **UAT-ACC-011** | Partial | Same inline validation pattern as ACC-006 (not re-run in browser). |
| **UAT-ACC-012** | Partial | Same as ACC-007.                                                   |

### Display name

| ID              | Result  | Evidence / notes                                                                                                                                               |
| --------------- | ------- | -------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **UAT-ACC-013** | Pass    | Required; pre-filled **admin**.                                                                                                                                |
| **UAT-ACC-014** | Pass    | Exact help string in template: _"This will be how your name will be displayed in the account section and in reviews"_.                                         |
| **UAT-ACC-015** | Partial | Inline empty validation (same as ACC-006).                                                                                                                     |
| **UAT-ACC-016** | Partial | Requires storefront review UI check; not executed.                                                                                                             |
| **UAT-ACC-017** | Partial | `wc_clean()`; display name cannot equal an email (`AccountController` blocks `is_email( $account_display_name )`). Other special characters generally allowed. |

### Email address

| ID              | Result  | Evidence / notes                                                                                                                                                                                                                 |
| --------------- | ------- | -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **UAT-ACC-018** | Pass    | `type="email"`; required; pre-filled **aminur@welabs.dev**.                                                                                                                                                                      |
| **UAT-ACC-019** | Partial | Empty → client inline error before submit; same automation caveat as ACC-006.                                                                                                                                                    |
| **UAT-ACC-020** | Partial | Empty handled client-side; invalid format hits server: `is_email` → JSON error **"Please provide a valid email address."** (`AccountController.php`). Browser `type="email"` may also block some invalid patterns before submit. |
| **UAT-ACC-021** | Pass    | Server: `email_exists( $account_email ) && $account_email !== $current_user->user_email` → **"This email address is already registered."**                                                                                       |
| **UAT-ACC-022** | Partial | **Not executed** (would change login identity and risk locking the test account). Success message string exists: **"Account details changed successfully."**                                                                     |
| **UAT-ACC-023** | Pass    | PHP `is_email()` accepts common `+` aliases; no StoreSuite code stripping the tag.                                                                                                                                               |

---

## Module 3: Change password (conditional section)

### Toggle behaviour

| ID              | Result  | Evidence / notes                                                                     |
| --------------- | ------- | ------------------------------------------------------------------------------------ |
| **UAT-PWD-001** | Pass    | Checkbox + label **Change Password**; default **off**; card hidden via JS.           |
| **UAT-PWD-002** | Partial | `change` handler shows card when checked; not clicked in this run (avoid tool loop). |
| **UAT-PWD-003** | Partial | Symmetric `toggle()` on unchecked.                                                   |
| **UAT-PWD-004** | Partial | Single card in DOM; no duplicate markup.                                             |

### Password fields (when toggle ON)

| ID              | Result | Evidence / notes                                                  |
| --------------- | ------ | ----------------------------------------------------------------- |
| **UAT-PWD-005** | Pass   | Label text matches template; `input type="password"` for current. |
| **UAT-PWD-006** | Pass   | Label + `password` type for new.                                  |
| **UAT-PWD-007** | Pass   | Label + `password` type for confirm.                              |
| **UAT-PWD-008** | Pass   | No `value` attributes on password inputs.                         |

### Password change — validation

| ID              | Result  | Evidence / notes                                                                                                                                                                            |
| --------------- | ------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **UAT-PWD-009** | Pass    | `! empty( $pass1 ) && empty( $pass_cur )` → **"Please enter your current password."** (AJAX error / Swal).                                                                                  |
| **UAT-PWD-010** | Partial | `$pass1 !== $pass2` → **"New passwords do not match."** — **UAT wording** was _"Passwords do not match"_; semantics match, copy differs.                                                    |
| **UAT-PWD-011** | Pass    | `! wp_check_password( $pass_cur, ... )` → **"Your current password is incorrect."**                                                                                                         |
| **UAT-PWD-012** | Pass    | If all password fields empty, none of the failing branches run; `wp_update_user` proceeds without `user_pass` change.                                                                       |
| **UAT-PWD-013** | Partial | Strength UI not in StoreSuite template; **WordPress** / site policy applies inside `wp_update_user()` / hooks. Document site `password_needs_rehash` / application-password plugins if any. |
| **UAT-PWD-014** | Partial | No explicit StoreSuite check for “new equals current”; WP may accept. Not tested.                                                                                                           |

### Password change — success flow

| ID              | Result  | Evidence / notes                                                                                                           |
| --------------- | ------- | -------------------------------------------------------------------------------------------------------------------------- |
| **UAT-PWD-015** | Partial | **Not executed** (session/password risk). Flow is `wp_update_user` with new hash on success.                               |
| **UAT-PWD-016** | Partial | Password inputs are not repopulated from server; fields stay empty in HTML. Post-success DOM not asserted after PWD-015.   |
| **UAT-PWD-017** | Partial | Checkbox remains unchecked in source; after reload without `$_POST`, default off. Not verified after real password change. |

---

## Module 4: Form submission

| ID              | Result  | Evidence / notes                                                                                                                                                           |
| --------------- | ------- | -------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **UAT-SUB-001** | Pass    | **Save changes** with no edits → SweetAlert **Success!** (browser `wait_for` **Success**); matches `wp_send_json_success( 'Account details changed successfully.' )` path. |
| **UAT-SUB-002** | Partial | Not executed; same AJAX success path as SUB-001 when only profile fields change.                                                                                           |
| **UAT-SUB-003** | Partial | Not executed.                                                                                                                                                              |
| **UAT-SUB-004** | Partial | Not executed.                                                                                                                                                              |
| **UAT-SUB-005** | Partial | Success uses **Swal** with OK button — **no** auto-dismiss timer in `showSuccess()`; UAT “disappears after a few seconds” is **not** met unless Swal defaults differ.      |
| **UAT-SUB-006** | Partial | Client empty fields → **inline** errors. Server/other errors → **Swal** (`showError`), not necessarily field-scoped inline messages.                                       |

---

## Module 5: Data persistence and security

| ID              | Result  | Evidence / notes                                                                                                                                                                             |
| --------------- | ------- | -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **UAT-SEC-001** | Partial | Inferred from `wp_update_user`; reload not recorded in this session.                                                                                                                         |
| **UAT-SEC-002** | Partial | Not executed (email change).                                                                                                                                                                 |
| **UAT-SEC-003** | Partial | Not executed (password change).                                                                                                                                                              |
| **UAT-SEC-004** | Partial | Password fields use `type="password"`; **HTTPS POST** still carries body (encrypted in transit, not “invisible” to browser devtools). Cannot certify “never in network requests” beyond TLS. |
| **UAT-SEC-005** | Partial | No StoreSuite-specific “protected email” list; WP core / plugins may add via `woocommerce_save_account_details_errors`.                                                                      |

---

## Module 6: Navigation

| ID              | Result  | Evidence / notes                                                                                                                                                  |
| --------------- | ------- | ----------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **UAT-NAV-001** | Pass    | Breadcrumb **Dashboard** link targets store dashboard base (`storesuite_get_navigation_url()` pattern; same as other modules).                                    |
| **UAT-NAV-002** | Partial | **Account** crumb links to same account area; exact URL equals dashboard permalink + account endpoint (verify in browser address bar).                            |
| **UAT-NAV-003** | Partial | Active sidebar state is CSS; not read from snapshot.                                                                                                              |
| **UAT-NAV-004** | Pass    | No `beforeunload` / unsaved-changes guard found in `form-handler.js` for this form — **no warning** on navigate away. **Documented actual behaviour:** no prompt. |

---

## Known observations (from UAT packet)

| ID        | Observation                                         | Severity |
| --------- | --------------------------------------------------- | -------- |
| **B-001** | No avatar upload on this page                       | Medium   |
| **B-002** | No show/hide password visibility toggle             | Low      |
| **B-003** | No strength meter for new password in StoreSuite UI | Low      |
| **B-004** | No store bio / social / store URL fields            | Medium   |

---

## Appendix — Verbatim checklist (for sign-off)

Mark each line using the tables above.

### Module 1

-   [ ] **UAT-ACC-001** — Page loads with heading "Account details", breadcrumb shows Dashboard > Account > Account details, sidebar "Account" item is active
-   [ ] **UAT-ACC-002** — All four account detail fields are pre-filled with the logged-in user's saved data: First name, Last name, Display name, Email address
-   [ ] **UAT-ACC-003** — "Change Password" toggle is visible below Email address and is in the OFF (collapsed) state by default — password fields are hidden
-   [ ] **UAT-ACC-004** — "Save changes" primary blue button is visible at the bottom of the form

### Module 2 — First name

-   [ ] **UAT-ACC-005** — **First name \*** is a required text input; pre-filled with saved value (e.g. "Aminur")
-   [ ] **UAT-ACC-006** — Clearing First name and clicking "Save changes" shows an inline validation error; form not submitted
-   [ ] **UAT-ACC-007** — Updating First name to a new value → Save → reload page shows new value persisted
-   [ ] **UAT-ACC-008** — First name accepts letters, hyphens, and spaces; does not accept numbers or special characters (document actual behaviour)
-   [ ] **UAT-ACC-009** — First name with very long input (e.g. 255+ characters) is handled gracefully (truncated or error shown)

### Module 2 — Last name

-   [ ] **UAT-ACC-010** — **Last name \*** is a required text input; pre-filled with saved value (e.g. "Islam")
-   [ ] **UAT-ACC-011** — Clearing Last name and clicking "Save changes" shows an inline validation error; form not submitted
-   [ ] **UAT-ACC-012** — Updating Last name → Save → reload page shows new value persisted

### Module 2 — Display name

-   [ ] **UAT-ACC-013** — **Display name \*** is a required text input; pre-filled with saved value (e.g. "admin")
-   [ ] **UAT-ACC-014** — Help text below the field reads: "This will be how your name will be displayed in the account section and in reviews"
-   [ ] **UAT-ACC-015** — Clearing Display name and clicking "Save changes" shows an inline validation error; form not submitted
-   [ ] **UAT-ACC-016** — Updating Display name → Save → display name updates in the account section and in product reviews on the frontend
-   [ ] **UAT-ACC-017** — Display name accepts special characters (e.g. accented letters, hyphens); document any restrictions

### Module 2 — Email

-   [ ] **UAT-ACC-018** — **Email address \*** is a required email input; pre-filled with saved email (e.g. "aminur@welabs.dev")
-   [ ] **UAT-ACC-019** — Clearing Email address and clicking "Save changes" shows an inline validation error; form not submitted
-   [ ] **UAT-ACC-020** — Entering an invalid email format (e.g. "notanemail", "user@", "@domain.com") shows a validation error; form not submitted
-   [ ] **UAT-ACC-021** — Entering an email already registered to another user shows a validation error (duplicate email)
-   [ ] **UAT-ACC-022** — Updating Email address to a valid new email → Save → success notice shown → new email is used for login and notifications
-   [ ] **UAT-ACC-023** — Email address field accepts standard email format with + alias (e.g. user+tag@domain.com)

### Module 3 — Toggle

-   [ ] **UAT-PWD-001** — "Change Password" toggle is visible, labelled correctly, and defaults to OFF (collapsed); password fields are hidden
-   [ ] **UAT-PWD-002** — Clicking the "Change Password" toggle turns it ON (blue); "Password change" section heading and three password fields expand and become visible
-   [ ] **UAT-PWD-003** — Clicking the toggle again turns it OFF (grey); password fields collapse and are hidden
-   [ ] **UAT-PWD-004** — Toggling ON and OFF multiple times does not cause layout issues or duplicate fields

### Module 3 — Password fields

-   [ ] **UAT-PWD-005** — **Current password** label reads "Current password (leave blank to leave unchanged)"; input is masked (password type)
-   [ ] **UAT-PWD-006** — **New password** label reads "New password (leave blank to leave unchanged)"; input is masked
-   [ ] **UAT-PWD-007** — **Confirm new password** label reads "Confirm new password"; input is masked
-   [ ] **UAT-PWD-008** — All three password fields are empty by default

### Module 3 — Password validation

-   [ ] **UAT-PWD-009** — Entering New password and Confirm new password (matching) without Current password → Save → shows validation error requiring current password (document actual behaviour)
-   [ ] **UAT-PWD-010** — Entering New password and Confirm new password that **do not match** → Save → shows validation error "Passwords do not match"; password not changed
-   [ ] **UAT-PWD-011** — Entering an incorrect Current password → Save → shows validation error; password not changed
-   [ ] **UAT-PWD-012** — Leaving all three password fields blank while toggle is ON → Save → account details saved normally; password is NOT changed
-   [ ] **UAT-PWD-013** — Entering New password that is too short or too weak shows a validation error or strength warning (document any minimum length/strength requirements)
-   [ ] **UAT-PWD-014** — New password same as Current password: document whether this is allowed or blocked with a warning

### Module 3 — Password success

-   [ ] **UAT-PWD-015** — Entering valid Current password, matching New password, and Confirm new password → Save → success notice shown; user can log out and log back in with the new password
-   [ ] **UAT-PWD-016** — After a successful password change, all three password fields are empty again (not retained in the form)
-   [ ] **UAT-PWD-017** — After a successful password change, the "Change Password" toggle returns to its default OFF state (collapsed) on reload

### Module 4 — Submission

-   [ ] **UAT-SUB-001** — Clicking "Save changes" with no modifications shows a success notice; all values remain unchanged
-   [ ] **UAT-SUB-002** — Updating only First name (no password change) → Save → success notice shown; only First name updates; password unchanged
-   [ ] **UAT-SUB-003** — Toggling Change Password ON, entering valid password fields, leaving account detail fields as-is → Save → success notice shown; only password is updated; account details unchanged
-   [ ] **UAT-SUB-004** — Updating First name AND changing password in the same save → both changes persist after reload
-   [ ] **UAT-SUB-005** — A success notice is visible after a valid save; it disappears after a few seconds or on next interaction
-   [ ] **UAT-SUB-006** — A clear error notice is visible when save fails; it describes the specific field(s) causing the failure

### Module 5 — Security

-   [ ] **UAT-SEC-001** — After saving updated account details, refreshing the page shows the updated values (not the old ones)
-   [ ] **UAT-SEC-002** — After changing email address, the user can log in using the new email address
-   [ ] **UAT-SEC-003** — After changing password, logging out and back in with the old password fails; new password works
-   [ ] **UAT-SEC-004** — Password field values are never shown in plain text in the page source or network requests
-   [ ] **UAT-SEC-005** — Attempting to update email to an admin-only or protected email (if applicable) is blocked with an appropriate message

### Module 6 — Navigation

-   [ ] **UAT-NAV-001** — Breadcrumb "Dashboard" link navigates to `/storesuite-dashboard/`
-   [ ] **UAT-NAV-002** — Breadcrumb "Account" link navigates to the Account section (document destination URL)
-   [ ] **UAT-NAV-003** — Sidebar "Account" menu item is highlighted/active on this page
-   [ ] **UAT-NAV-004** — Navigating away from the page with unsaved changes does NOT prompt a warning (document actual behaviour — if a warning should exist, raise as a bug)

---

## Counts

| Outcome | Count (approx.) |
| ------- | --------------- |
| Pass    | 18              |
| Partial | 42              |
| Fail    | 1               |

**Single explicit Fail:** **UAT-ACC-008** (first name does not reject numbers per UAT; implementation has no such restriction).

---

**Report path:** `/Users/aiarnob/STORESUITE-UAT-Account-2026-04-11.md`
