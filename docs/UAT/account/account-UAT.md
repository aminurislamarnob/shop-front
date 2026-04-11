## Overview

User Acceptance Testing checklist for the **Account Details** module in the StoreSuite vendor dashboard.

**Page covered:**

-   `/storesuite-dashboard/edit-account-details/`

**Role under test:** Logged-in vendor/seller
**Prepared:** 2026-04-10

> Tick each box as **Pass ✅**, or comment with **Fail ❌** + repro steps.

---

## Module 1: Page Load & Layout

-   [ ] **UAT-ACC-001** — Page loads with heading "Account details", breadcrumb shows Dashboard > Account > Account details, sidebar "Account" item is active
-   [ ] **UAT-ACC-002** — All four account detail fields are pre-filled with the logged-in user's saved data: First name, Last name, Display name, Email address
-   [ ] **UAT-ACC-003** — "Change Password" toggle is visible below Email address and is in the OFF (collapsed) state by default — password fields are hidden
-   [ ] **UAT-ACC-004** — "Save changes" primary blue button is visible at the bottom of the form

---

## Module 2: Account Details Fields

### First Name

-   [ ] **UAT-ACC-005** — **First name \*** is a required text input; pre-filled with saved value (e.g. "Aminur")
-   [ ] **UAT-ACC-006** — Clearing First name and clicking "Save changes" shows an inline validation error; form not submitted
-   [ ] **UAT-ACC-007** — Updating First name to a new value → Save → reload page shows new value persisted
-   [ ] **UAT-ACC-008** — First name accepts letters, hyphens, and spaces; does not accept numbers or special characters (document actual behaviour)
-   [ ] **UAT-ACC-009** — First name with very long input (e.g. 255+ characters) is handled gracefully (truncated or error shown)

### Last Name

-   [ ] **UAT-ACC-010** — **Last name \*** is a required text input; pre-filled with saved value (e.g. "Islam")
-   [ ] **UAT-ACC-011** — Clearing Last name and clicking "Save changes" shows an inline validation error; form not submitted
-   [ ] **UAT-ACC-012** — Updating Last name → Save → reload page shows new value persisted

### Display Name

-   [ ] **UAT-ACC-013** — **Display name \*** is a required text input; pre-filled with saved value (e.g. "admin")
-   [ ] **UAT-ACC-014** — Help text below the field reads: "This will be how your name will be displayed in the account section and in reviews"
-   [ ] **UAT-ACC-015** — Clearing Display name and clicking "Save changes" shows an inline validation error; form not submitted
-   [ ] **UAT-ACC-016** — Updating Display name → Save → display name updates in the account section and in product reviews on the frontend
-   [ ] **UAT-ACC-017** — Display name accepts special characters (e.g. accented letters, hyphens); document any restrictions

### Email Address

-   [ ] **UAT-ACC-018** — **Email address \*** is a required email input; pre-filled with saved email (e.g. "aminur@welabs.dev")
-   [ ] **UAT-ACC-019** — Clearing Email address and clicking "Save changes" shows an inline validation error; form not submitted
-   [ ] **UAT-ACC-020** — Entering an invalid email format (e.g. "notanemail", "user@", "@domain.com") shows a validation error; form not submitted
-   [ ] **UAT-ACC-021** — Entering an email already registered to another user shows a validation error (duplicate email)
-   [ ] **UAT-ACC-022** — Updating Email address to a valid new email → Save → success notice shown → new email is used for login and notifications
-   [ ] **UAT-ACC-023** — Email address field accepts standard email format with + alias (e.g. user+tag@domain.com)

---

## Module 3: Change Password (Conditional Section)

### Toggle Behaviour

-   [ ] **UAT-PWD-001** — "Change Password" toggle is visible, labelled correctly, and defaults to OFF (collapsed); password fields are hidden
-   [ ] **UAT-PWD-002** — Clicking the "Change Password" toggle turns it ON (blue); "Password change" section heading and three password fields expand and become visible
-   [ ] **UAT-PWD-003** — Clicking the toggle again turns it OFF (grey); password fields collapse and are hidden
-   [ ] **UAT-PWD-004** — Toggling ON and OFF multiple times does not cause layout issues or duplicate fields

### Password Fields (when toggle is ON)

-   [ ] **UAT-PWD-005** — **Current password** label reads "Current password (leave blank to leave unchanged)"; input is masked (password type)
-   [ ] **UAT-PWD-006** — **New password** label reads "New password (leave blank to leave unchanged)"; input is masked
-   [ ] **UAT-PWD-007** — **Confirm new password** label reads "Confirm new password"; input is masked
-   [ ] **UAT-PWD-008** — All three password fields are empty by default

### Password Change — Validation

-   [ ] **UAT-PWD-009** — Entering New password and Confirm new password (matching) without Current password → Save → shows validation error requiring current password (document actual behaviour)
-   [ ] **UAT-PWD-010** — Entering New password and Confirm new password that **do not match** → Save → shows validation error "Passwords do not match"; password not changed
-   [ ] **UAT-PWD-011** — Entering an incorrect Current password → Save → shows validation error; password not changed
-   [ ] **UAT-PWD-012** — Leaving all three password fields blank while toggle is ON → Save → account details saved normally; password is NOT changed
-   [ ] **UAT-PWD-013** — Entering New password that is too short or too weak shows a validation error or strength warning (document any minimum length/strength requirements)
-   [ ] **UAT-PWD-014** — New password same as Current password: document whether this is allowed or blocked with a warning

### Password Change — Success Flow

-   [ ] **UAT-PWD-015** — Entering valid Current password, matching New password, and Confirm new password → Save → success notice shown; user can log out and log back in with the new password
-   [ ] **UAT-PWD-016** — After a successful password change, all three password fields are empty again (not retained in the form)
-   [ ] **UAT-PWD-017** — After a successful password change, the "Change Password" toggle returns to its default OFF state (collapsed) on reload

---

## Module 4: Form Submission

### No-Change Save

-   [ ] **UAT-SUB-001** — Clicking "Save changes" with no modifications shows a success notice; all values remain unchanged

### Partial Save (Account Details Only)

-   [ ] **UAT-SUB-002** — Updating only First name (no password change) → Save → success notice shown; only First name updates; password unchanged

### Partial Save (Password Only)

-   [ ] **UAT-SUB-003** — Toggling Change Password ON, entering valid password fields, leaving account detail fields as-is → Save → success notice shown; only password is updated; account details unchanged

### Combined Save

-   [ ] **UAT-SUB-004** — Updating First name AND changing password in the same save → both changes persist after reload

### Success & Error Notices

-   [ ] **UAT-SUB-005** — A success notice is visible after a valid save; it disappears after a few seconds or on next interaction
-   [ ] **UAT-SUB-006** — A clear error notice is visible when save fails; it describes the specific field(s) causing the failure

---

## Module 5: Data Persistence & Security

-   [ ] **UAT-SEC-001** — After saving updated account details, refreshing the page shows the updated values (not the old ones)
-   [ ] **UAT-SEC-002** — After changing email address, the user can log in using the new email address
-   [ ] **UAT-SEC-003** — After changing password, logging out and back in with the old password fails; new password works
-   [ ] **UAT-SEC-004** — Password field values are never shown in plain text in the page source or network requests
-   [ ] **UAT-SEC-005** — Attempting to update email to an admin-only or protected email (if applicable) is blocked with an appropriate message

---

## Module 6: Navigation

-   [ ] **UAT-NAV-001** — Breadcrumb "Dashboard" link navigates to `/storesuite-dashboard/`
-   [ ] **UAT-NAV-002** — Breadcrumb "Account" link navigates to the Account section (document destination URL)
-   [ ] **UAT-NAV-003** — Sidebar "Account" menu item is highlighted/active on this page
-   [ ] **UAT-NAV-004** — Navigating away from the page with unsaved changes does NOT prompt a warning (document actual behaviour — if a warning should exist, raise as a bug)

---

## Known Observations (Verify or Raise as Bugs)

| ID    | Observation                                                                                                                | Severity |
| ----- | -------------------------------------------------------------------------------------------------------------------------- | -------- |
| B-001 | No profile/avatar image upload on this page — if vendors should be able to set a store avatar, this feature may be missing | Medium   |
| B-002 | No show/hide toggle on password fields — users cannot see what they are typing                                             | Low      |
| B-003 | No password strength indicator on New password field                                                                       | Low      |
| B-004 | No social links, store bio, or store URL fields on this page — if required for vendor profile, these are missing           | Medium   |

---

## Exit Criteria

The following must **all pass** before QA sign-off:

| ID          | Scenario                                           | Priority    |
| ----------- | -------------------------------------------------- | ----------- |
| UAT-ACC-001 | Page loads with correct layout and pre-filled data | 🔴 Critical |
| UAT-ACC-006 | First name required field validation               | 🔴 Critical |
| UAT-ACC-011 | Last name required field validation                | 🔴 Critical |
| UAT-ACC-015 | Display name required field validation             | 🔴 Critical |
| UAT-ACC-019 | Email required field validation                    | 🔴 Critical |
| UAT-ACC-020 | Invalid email format blocked                       | 🔴 Critical |
| UAT-ACC-021 | Duplicate email blocked                            | 🔴 Critical |
| UAT-ACC-022 | Email update saves correctly                       | 🔴 Critical |
| UAT-PWD-002 | Change Password toggle expands fields              | 🔴 Critical |
| UAT-PWD-010 | Mismatched passwords blocked                       | 🔴 Critical |
| UAT-PWD-011 | Wrong current password blocked                     | 🔴 Critical |
| UAT-PWD-015 | Valid password change saves and works at login     | 🔴 Critical |
| UAT-SUB-001 | No-change save shows success notice                | 🟠 High     |
| UAT-SUB-005 | Success notice shown after valid save              | 🟠 High     |
| UAT-SEC-001 | Updated data persists after page refresh           | 🔴 Critical |
| UAT-SEC-003 | Old password rejected after change                 | 🔴 Critical |
