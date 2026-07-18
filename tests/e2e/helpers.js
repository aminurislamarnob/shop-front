/**
 * Shared constants and helpers for the StoreSuite E2E suite.
 */
const path = require( 'path' );
const { execFileSync } = require( 'child_process' );

const WP_ROOT = path.resolve( __dirname, '..', '..', '..', '..', '..' );

/**
 * Read one key from the serialized storesuite_settings option ('' if unset).
 *
 * @param {string} key Setting key.
 * @return {string} Current value.
 */
function getStoreSuiteSetting( key ) {
	const php = `$s = (array) get_option( 'storesuite_settings', array() ); echo isset( $s['${ key }'] ) ? $s['${ key }'] : '';`;
	return execFileSync( 'wp', [ 'eval', php ], { cwd: WP_ROOT, encoding: 'utf8' } ).trim();
}

/**
 * Write one key into the serialized storesuite_settings option. An empty
 * value removes the key.
 *
 * @param {string} key Setting key.
 * @param {string} value New value ('' to unset).
 */
function setStoreSuiteSetting( key, value ) {
	const php = value
		? `$s = (array) get_option( 'storesuite_settings', array() ); $s['${ key }'] = '${ value }'; update_option( 'storesuite_settings', $s );`
		: `$s = (array) get_option( 'storesuite_settings', array() ); unset( $s['${ key }'] ); update_option( 'storesuite_settings', $s );`;
	execFileSync( 'wp', [ 'eval', php ], { cwd: WP_ROOT, encoding: 'utf8' } );
}

const USERS = {
	admin: { username: 'e2e-admin', password: 'E2E!admin#2026' },
	manager: { username: 'e2e-manager', password: 'E2E!manager#2026' },
	customer: { username: 'e2e-customer', password: 'E2E!customer#2026' },
};

const STORAGE_STATE = {
	admin: path.join( __dirname, '.auth', 'admin.json' ),
	manager: path.join( __dirname, '.auth', 'manager.json' ),
	customer: path.join( __dirname, '.auth', 'customer.json' ),
};

const DASHBOARD = '/storesuite-dashboard/';

/**
 * Absolute path of a dashboard endpoint, e.g. dashboardUrl( 'products' ).
 *
 * @param {string} endpoint Endpoint slug ('' for the dashboard home).
 * @return {string} Path relative to baseURL.
 */
function dashboardUrl( endpoint = '' ) {
	return endpoint ? `${ DASHBOARD }${ endpoint }/` : DASHBOARD;
}

/**
 * Log in through the WooCommerce My Account form.
 *
 * This site's wp-login-and-logout-redirect plugin 302s wp-login.php to
 * /my-account/, so the WooCommerce login form is the real login surface.
 * Third-party redirect plugins may send the browser anywhere afterwards; all
 * that matters is that the auth cookie is set.
 *
 * @param {import('@playwright/test').Page} page Playwright page.
 * @param {{username: string, password: string}} user Credentials.
 */
async function login( page, user ) {
	await page.goto( '/my-account/' );
	await page.fill( 'form.woocommerce-form-login #username', user.username );
	await page.fill( 'form.woocommerce-form-login #password', user.password );
	await Promise.all( [
		page.waitForNavigation(),
		page.click( 'form.woocommerce-form-login button[name="login"]' ),
	] );
}

/**
 * Wait for a SweetAlert2 popup containing the given text, then dismiss it.
 *
 * StoreSuite's frontend forms report success/failure through SweetAlert2, so
 * this is the canonical "the form worked" assertion.
 *
 * @param {import('@playwright/test').Page} page Playwright page.
 * @param {string|RegExp} text Expected popup text.
 */
async function expectSwal( page, text ) {
	const popup = page.locator( '.swal2-popup' );
	const { expect } = require( '@playwright/test' );
	await expect( popup ).toBeVisible();
	await expect( popup ).toContainText( text );

	// Dismiss so the next interaction isn't blocked by the overlay. Use
	// SweetAlert2's own API — the popup's scale-in animation makes
	// coordinate-based clicks land on the backdrop and dismiss instead.
	await page.evaluate( () => window.Swal && window.Swal.clickConfirm() );
	await popup.waitFor( { state: 'hidden' } ).catch( () => {} );
}

/**
 * Confirm the currently open SweetAlert2 dialog via its test API.
 *
 * The popup scale-in animation moves the buttons while Playwright computes
 * click coordinates, so real clicks can land on the backdrop and dismiss the
 * dialog. Swal.clickConfirm() is animation-proof.
 *
 * @param {import('@playwright/test').Page} page Playwright page.
 */
async function confirmSwal( page ) {
	const { expect } = require( '@playwright/test' );
	await expect( page.locator( '.swal2-confirm' ) ).toBeVisible();
	await page.evaluate( () => window.Swal.clickConfirm() );
}

/**
 * Unique suffix so parallel/re-runs never collide on names or slugs.
 *
 * @return {string} e.g. "1721286500123".
 */
function uniq() {
	return String( Date.now() );
}

/**
 * Run a PHP snippet on the site via wp-cli and return trimmed stdout.
 *
 * @param {string} php PHP code (without opening tag).
 * @return {string} stdout.
 */
function wpEval( php ) {
	return execFileSync( 'wp', [ 'eval', php ], { cwd: WP_ROOT, encoding: 'utf8' } ).trim();
}

/**
 * Create a published simple product directly (fixture setup, not the flow
 * under test) and return its ID.
 *
 * @param {string} name  Product name.
 * @param {string} price Regular price.
 * @return {number} Product ID.
 */
function createProduct( name, price = '10' ) {
	return Number(
		wpEval(
			`$p = new WC_Product_Simple(); $p->set_name( '${ name }' ); $p->set_regular_price( '${ price }' ); $p->set_status( 'publish' ); echo $p->save();`
		)
	);
}

/**
 * Create a published coupon directly (fixture setup) and return its ID.
 *
 * @param {string} code   Coupon code.
 * @param {string} amount Discount amount.
 * @return {number} Coupon ID.
 */
function createCoupon( code, amount = '5' ) {
	return Number(
		wpEval(
			`$c = new WC_Coupon(); $c->set_code( '${ code }' ); $c->set_discount_type( 'percent' ); $c->set_amount( ${ amount } ); echo $c->save();`
		)
	);
}

/**
 * Let a JS-triggered page reload finish before navigating elsewhere.
 *
 * The delete success handlers fade the row out and call
 * window.location.reload(); a goto issued while that reload is in flight
 * aborts with net::ERR_ABORTED.
 *
 * @param {import('@playwright/test').Page} page Playwright page.
 */
async function settleReload( page ) {
	await page.waitForTimeout( 1500 );
	await page.waitForLoadState( 'load' ).catch( () => {} );
}

module.exports = {
	USERS,
	STORAGE_STATE,
	DASHBOARD,
	WP_ROOT,
	dashboardUrl,
	login,
	expectSwal,
	confirmSwal,
	uniq,
	settleReload,
	getStoreSuiteSetting,
	setStoreSuiteSetting,
	wpEval,
	createProduct,
	createCoupon,
};
