import { test, expect } from '../../../utils/test';
import { ADMIN_STATE, MANAGER_STATE } from '../../../utils/authStates';
import { dashboardPath } from '../../../utils/testData';
import { cleanupNotificationsFixtures, fireNewOrderEvent, wpEval } from './qa';

test.use( { storageState: MANAGER_STATE } );

const BELL = '.storesuite-notification-bell';
const BADGE = '.storesuite-notification-badge';

test.describe( 'QA184 notifications', () => {
	test( 'a new order badges the bell without a page refresh', async ( { page } ) => {
		await page.goto( dashboardPath + '/' );
		// Start from a clean seen state for this manager.
		await page.locator( BELL ).click();
		await page.keyboard.press( 'Escape' );
		await page.reload();
		await expect( page.locator( BADGE ) ).toBeHidden();

		// Marker proves no reload happens while we wait for the poll.
		await page.evaluate( () => ( ( window as any ).__qa184 = 1 ) );
		fireNewOrderEvent();

		await expect( page.locator( BADGE ) ).toBeVisible( { timeout: 40000 } );
		expect( await page.evaluate( () => ( window as any ).__qa184 ) ).toBe( 1 );
		expect( await page.locator( BADGE ).innerText() ).toMatch( /^[1-9]/ );
	} );

	test( 'dropdown lists the order and opening clears the badge', async ( { page } ) => {
		await page.goto( dashboardPath + '/' );
		await expect( page.locator( BADGE ) ).toBeVisible();

		await page.locator( BELL ).click();
		const items = page.locator( '.storesuite-notifications-menu-list .dropdown-link' );
		await expect( items.first() ).toBeVisible();
		await expect( items.first() ).toContainText( /order/i );

		await expect( page.locator( BADGE ) ).toBeHidden( { timeout: 10000 } );

		// The seen state must persist across a reload.
		await page.reload();
		await expect( page.locator( BADGE ) ).toBeHidden();
	} );

	test( 'notifications page paginates, mark-all and clear-all work', async ( {
		page,
	} ) => {
		fireNewOrderEvent( 18 );

		await page.goto( dashboardPath + '/notifications/' );
		// Scope to the page list — the bell dropdown holds hidden items too.
		const rows = page.locator( '.storesuite-notifications-list .storesuite-notification-item' );
		await expect( rows.first() ).toBeVisible();
		const pageOneCount = await rows.count();
		expect( pageOneCount ).toBeLessThanOrEqual( 15 );

		// Pagination to page 2 (custom rewrite: /notifications/page/2).
		const pagination = page.locator( '.storesuite-pagination, nav[aria-label*="agination"], .pagination' );
		await expect( pagination.first() ).toBeVisible();
		await page.goto( dashboardPath + '/notifications/page/2/' );
		await expect(
			page.locator( '.storesuite-notifications-list .storesuite-notification-item' ).first()
		).toBeVisible();

		// Mark all as read.
		await page.goto( dashboardPath + '/notifications/' );
		await page.locator( '.storesuite-notifications-mark-all' ).click();
		await expect(
			page.locator( '.storesuite-notifications-list .storesuite-notification-item.is-unseen' )
		).toHaveCount( 0, { timeout: 10000 } );
		await expect( page.locator( BADGE ) ).toBeHidden();

		// Clear all (may confirm via SweetAlert).
		await page.locator( '.storesuite-notifications-clear-all' ).click();
		const confirm = page.locator( '.swal2-confirm' );
		if ( await confirm.isVisible( { timeout: 2000 } ).catch( () => false ) ) {
			await confirm.click();
		}
		await expect(
			page.locator( '.storesuite-notifications-list .storesuite-notification-item' )
		).toHaveCount( 0, { timeout: 10000 } );
	} );

	test( 'disabling the new-order event stops new notifications', async ( {
		page,
		browser,
	} ) => {
		const toggleNewOrder = async ( enable: boolean ) => {
			const adminContext = await browser.newContext( { storageState: ADMIN_STATE } );
			const admin = await adminContext.newPage();
			await admin.goto( '/wp-admin/admin.php?page=storesuite#/notifications-settings' );
			const control = admin.getByRole( 'checkbox', { name: 'New order' } );
			await control.waitFor( { state: 'attached' } );
			await control.setChecked( enable, { force: true } );
			await expect( control ).toBeChecked( { checked: enable } );
			const [ save ] = await Promise.all( [
				admin.waitForResponse(
					( r ) =>
						r.url().includes( '/storesuite/v1/settings' ) &&
						'POST' === r.request().method()
				),
				admin.getByRole( 'button', { name: 'Save Changes' } ).click(),
			] );
			expect( save.ok() ).toBeTruthy();
			await adminContext.close();

			// The UI save must actually land in the stored settings.
			const stored = wpEval(
				`echo storesuite_get_option_by_key( 'storesuite_notification_new_order' );`
			);
			expect( stored ).toBe( enable ? 'yes' : 'no' );
		};

		const countEvents = () =>
			Number(
				wpEval(
					`global $wpdb; echo (int) $wpdb->get_var( "SELECT COUNT(DISTINCT object_id) FROM {$wpdb->prefix}storesuite_notifications WHERE type = 'new_order'" );`
				)
			);

		await toggleNewOrder( false );
		const before = countEvents();
		fireNewOrderEvent();
		expect(
			countEvents(),
			'No notification may be recorded while the event is off.'
		).toBe( before );

		await toggleNewOrder( true );
		fireNewOrderEvent();
		expect( countEvents() ).toBe( before + 1 );
		// Manager sees it again.
		await page.goto( dashboardPath + '/' );
		await expect( page.locator( BADGE ) ).toBeVisible();
	} );

	test( 'a second manager has an independent unread count', async ( {
		page,
		browser,
	} ) => {
		// Manager 1 clears their own unread state.
		await page.goto( dashboardPath + '/' );
		if ( await page.locator( BADGE ).isVisible() ) {
			await page.locator( BELL ).click();
			await expect( page.locator( BADGE ) ).toBeHidden( { timeout: 10000 } );
		}

		fireNewOrderEvent( 2 );

		// Manager 2 logs in fresh and must see their own unread count.
		// (An empty storageState, or the context would inherit manager 1's
		// cookies from the file-level test.use.)
		const context2 = await browser.newContext( {
			storageState: { cookies: [], origins: [] },
		} );
		const page2 = await context2.newPage();
		await page2.goto( '/my-account/' );
		await page2.locator( 'form.woocommerce-form-login #username' ).fill( 'manager2' );
		await page2.locator( 'form.woocommerce-form-login #password' ).fill( 'password' );
		await page2.locator( 'form.woocommerce-form-login button[name="login"]' ).click();
		await page2.waitForLoadState();
		await page2.goto( dashboardPath + '/' );
		await expect( page2.locator( BADGE ) ).toBeVisible();
		const count2 = await page2.locator( BADGE ).innerText();
		expect( Number( count2.replace( '+', '' ) ) ).toBeGreaterThanOrEqual( 2 );

		// Manager 1's own state is untouched by manager 2's session: after the
		// two new events manager 1 sees exactly those 2 as unread too — but
		// opening manager 2's dropdown must NOT clear manager 1's badge.
		await page2.locator( BELL ).click();
		await expect( page2.locator( BADGE ) ).toBeHidden( { timeout: 10000 } );
		await page.reload();
		await expect(
			page.locator( BADGE ),
			'Manager 1 badge must survive manager 2 reading their own notifications.'
		).toBeVisible();
		await context2.close();
	} );

	test.afterAll( () => {
		cleanupNotificationsFixtures();
	} );
} );
