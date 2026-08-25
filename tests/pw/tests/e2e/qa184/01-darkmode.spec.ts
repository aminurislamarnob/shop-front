import { test, expect } from '../../../utils/test';
import { ADMIN_STATE, MANAGER_STATE } from '../../../utils/authStates';
import { dashboardPath } from '../../../utils/testData';
import { SCRATCH } from './qa';

test.use( { storageState: MANAGER_STATE } );

const DARK_PRESETS: Array<{ label: string; pageBg: string }> = [
	{ label: 'Dark default', pageBg: '#0f172a' },
	{ label: 'Soft dark', pageBg: '#1c2128' },
	{ label: 'Midnight black', pageBg: '#010409' },
	{ label: 'Carbon', pageBg: '#000000' },
];

function hexToRgb( hex: string ): string {
	const n = parseInt( hex.slice( 1 ), 16 );
	return `rgb(${ ( n >> 16 ) & 255 }, ${ ( n >> 8 ) & 255 }, ${ n & 255 })`;
}

test.describe( 'QA184 dark mode', () => {
	test( 'toggle flips light/dark and survives a reload', async ( { page } ) => {
		await page.goto( dashboardPath + '/' );
		await page.evaluate( () => localStorage.setItem( 'storesuite_theme_mode', 'light' ) );
		await page.reload();

		const html = page.locator( 'html' );
		await expect( html ).toHaveAttribute( 'data-theme', 'light' );

		await page.locator( '.storesuite-theme-toggle' ).click();
		await expect( html ).toHaveAttribute( 'data-theme', 'dark' );
		expect(
			await page.evaluate( () => localStorage.getItem( 'storesuite_theme_mode' ) )
		).toBe( 'dark' );

		await page.reload();
		await expect( html ).toHaveAttribute( 'data-theme', 'dark' );

		// Toggle back and forward once more to prove both directions.
		await page.locator( '.storesuite-theme-toggle' ).click();
		await expect( html ).toHaveAttribute( 'data-theme', 'light' );
		await page.locator( '.storesuite-theme-toggle' ).click();
		await expect( html ).toHaveAttribute( 'data-theme', 'dark' );
	} );

	test( 'no flash of the wrong theme on load', async ( { page } ) => {
		await page.goto( dashboardPath + '/' );
		await page.evaluate( () => localStorage.setItem( 'storesuite_theme_mode', 'dark' ) );

		// Hard refresh; the resolver script must sit in <head> BEFORE every
		// stylesheet so data-theme is set prior to first paint.
		await page.reload();
		const ordering = await page.evaluate( () => {
			const script = document.getElementById( 'storesuite-theme-mode' );
			if ( ! script || script.parentElement?.tagName !== 'HEAD' ) {
				return 'script-missing-from-head';
			}
			const sheets = Array.from(
				document.head.querySelectorAll( 'link[rel="stylesheet"]' )
			);
			const late = sheets.filter(
				( sheet ) =>
					script.compareDocumentPosition( sheet ) &
					Node.DOCUMENT_POSITION_PRECEDING
			);
			return late.length ? 'stylesheet-precedes-resolver' : 'ok';
		} );
		expect( ordering ).toBe( 'ok' );
		await expect( page.locator( 'html' ) ).toHaveAttribute( 'data-theme', 'dark' );
	} );

	for ( const preset of DARK_PRESETS ) {
		test( `palette "${ preset.label }" applies on the frontend`, async ( {
			page,
			browser,
		} ) => {
			// Choose the preset through the real admin UI.
			const adminContext = await browser.newContext( { storageState: ADMIN_STATE } );
			const admin = await adminContext.newPage();
			await admin.goto( '/wp-admin/admin.php?page=storesuite#/appearance-settings' );
			await admin
				.locator( '.storesuite-theme-tab', { hasText: 'Dark Mode' } )
				.click();
			await admin
				.locator( '.storesuite-palette-item', { hasText: preset.label } )
				.first()
				.click();
			const [ save ] = await Promise.all( [
				admin.waitForResponse(
					( r ) =>
						r.url().includes( '/storesuite/v1/settings' ) &&
						'POST' === r.request().method()
				),
				admin.getByRole( 'button', { name: 'Save Changes' } ).click(),
			] );
			expect( save.ok(), 'Saving the dark preset must succeed.' ).toBeTruthy();
			await adminContext.close();

			// Verify on the manager-facing dashboard in dark mode.
			await page.goto( dashboardPath + '/' );
			await page.evaluate( () =>
				localStorage.setItem( 'storesuite_theme_mode', 'dark' )
			);
			await page.reload();
			await expect( page.locator( 'html' ) ).toHaveAttribute( 'data-theme', 'dark' );
			await expect
				.poll( async () =>
					page.evaluate( () =>
						getComputedStyle( document.documentElement )
							.getPropertyValue( '--storesuite-page-bg' )
							.trim()
							.toLowerCase()
					)
				)
				.toBe( preset.pageBg );
			// The page ground actually paints with it.
			const bodyBg = await page.evaluate( () => {
				const el =
					document.querySelector( '.storesuite-dashboard-wrap, .storesuite-dashboard' ) ||
					document.body;
				return getComputedStyle( el ).backgroundColor;
			} );
			expect( [ hexToRgb( preset.pageBg ), 'rgba(0, 0, 0, 0)' ] ).toContain( bodyBg );
			await page.screenshot( {
				path: `${ SCRATCH }/shots/palette-${ preset.label.replace( /\s+/g, '-' ) }.png`,
				fullPage: false,
			} );
		} );
	}

	test( 'sidebar logo and icon swap to dark variants', async ( { page } ) => {
		await page.goto( dashboardPath + '/' );
		await page.evaluate( () => localStorage.setItem( 'storesuite_theme_mode', 'light' ) );
		await page.reload();

		const logo = page.locator( 'img.storesuite-sidebar-logo-image' );
		await expect( logo ).toHaveAttribute( 'src', /logo-light/ );

		await page.locator( '.storesuite-theme-toggle' ).click();
		await expect( logo ).toHaveAttribute( 'src', /logo-dark/ );

		const icon = page.locator( 'img.storesuite-sidebar-icon-image' );
		if ( await icon.count() ) {
			expect( await icon.getAttribute( 'src' ) ).toContain( 'icon-dark' );
		}

		// And back.
		await page.locator( '.storesuite-theme-toggle' ).click();
		await expect( logo ).toHaveAttribute( 'src', /logo-light/ );
	} );

	test( 'dark spot-check: list page, product form, order details, analytics', async ( {
		page,
	} ) => {
		await page.goto( dashboardPath + '/' );
		await page.evaluate( () => localStorage.setItem( 'storesuite_theme_mode', 'dark' ) );

		const stops: Array<{ name: string; path: string; marker: string }> = [
			{ name: 'products-list', path: '/products/', marker: 'table' },
			{ name: 'product-form', path: '/add-new-product/', marker: 'form' },
			{ name: 'orders-list', path: '/orders/', marker: 'table' },
			{ name: 'analytics', path: '/analytics/', marker: '#storesuite-analytics, .woocommerce-layout, .storesuite-analytics' },
		];

		for ( const stop of stops ) {
			await page.goto( dashboardPath + stop.path );
			await expect( page.locator( 'html' ) ).toHaveAttribute( 'data-theme', 'dark' );
			await page
				.locator( stop.marker )
				.first()
				.waitFor( { state: 'attached', timeout: 15000 } )
				.catch( () => {} );
			await page.waitForTimeout( 800 );
			await page.screenshot( {
				path: `${ SCRATCH }/shots/dark-${ stop.name }.png`,
				fullPage: false,
			} );
		}

		// Order details: open the first order edit/details link from the list.
		await page.goto( dashboardPath + '/orders/' );
		await page
			.locator( 'a[href*="/order-details/"]' )
			.first()
			.evaluate( ( el ) => ( window.location.href = ( el as HTMLAnchorElement ).href ) );
		await page.waitForLoadState();
		await expect( page.locator( 'html' ) ).toHaveAttribute( 'data-theme', 'dark' );
		await page.waitForTimeout( 500 );
		await page.screenshot( { path: `${ SCRATCH }/shots/dark-order-details.png` } );
	} );
} );
