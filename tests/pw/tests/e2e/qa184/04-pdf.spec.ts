import { test, expect } from '../../../utils/test';
import { MANAGER_STATE } from '../../../utils/authStates';
import { dashboardPath } from '../../../utils/testData';
import { SCRATCH, wpCli } from './qa';

test.use( { storageState: MANAGER_STATE } );

async function firstOrderDetailsUrl( page ): Promise<string> {
	await page.goto( dashboardPath + '/orders/' );
	const href = await page
		.locator( 'a[href*="/order-details/"]' )
		.first()
		.getAttribute( 'href' );
	if ( ! href ) {
		throw new Error( 'No order details link found on the orders list.' );
	}
	return href;
}

test.describe( 'QA184 PDF invoices', () => {
	test( 'with no PDF plugin: no Documents card and no PHP notices', async ( {
		page,
	} ) => {
		wpCli( 'plugin deactivate woocommerce-pdf-invoices-packing-slips' );

		const url = await firstOrderDetailsUrl( page );
		await page.goto( url );

		await expect( page.locator( '.storesuite-order-documents' ) ).toHaveCount( 0 );
		const body = await page.content();
		expect( body ).not.toMatch( /(Fatal error|Warning:.*\.php|Notice:.*\.php|Deprecated:.*\.php)/ );

		// Row-actions menu on the list must not offer documents either.
		await page.goto( dashboardPath + '/orders/' );
		await expect( page.locator( 'a.storesuite-print-document' ) ).toHaveCount( 0 );
	} );

	test( 'with WPO active: documents in row menu and Documents card, download is a real PDF', async ( {
		page,
	} ) => {
		wpCli( 'plugin activate woocommerce-pdf-invoices-packing-slips' );
		// A fresh WPO install has no documents enabled until its wizard runs;
		// enable the invoice PDF the way the wizard would.
		wpCli(
			`option update wpo_wcpdf_documents_settings_invoice '{"enabled":1}' --format=json`
		);

		const url = await firstOrderDetailsUrl( page );
		await page.goto( url );

		const card = page.locator( '.storesuite-order-documents' );
		await expect( card ).toBeVisible();
		const invoiceLink = card.locator( 'a', { hasText: /invoice/i } ).first();
		await expect( invoiceLink ).toBeVisible();
		await page.screenshot( { path: `${ SCRATCH }/shots/pdf-documents-card.png` } );

		// The link must stream an actual PDF.
		const href = await invoiceLink.getAttribute( 'href' );
		const response = await page.request.get( href! );
		expect( response.status() ).toBe( 200 );
		const bytes = await response.body();
		expect( bytes.subarray( 0, 5 ).toString() ).toBe( '%PDF-' );

		// Orders list row menu shows the same document actions.
		await page.goto( dashboardPath + '/orders/' );
		const rowDoc = page.locator( '.dropdown-link', { hasText: /invoice/i } ).first();
		await expect( rowDoc ).toBeAttached();

		// No PHP notices anywhere on these pages.
		const body = await page.content();
		expect( body ).not.toMatch( /(Fatal error|Warning:.*\.php|Notice:.*\.php)/ );
	} );

	test( 'print documents open the print flow in place (StoreSuite handler)', async ( {
		page,
	} ) => {
		// WPO documents stream PDFs (download links); the print-in-place path
		// is used by WebToffee-style HTML documents. wordpress.org is not
		// reachable from this sandbox, so exercise StoreSuite's own handler
		// with a synthetic document link returning HTML.
		const url = await firstOrderDetailsUrl( page );
		await page.goto( url );

		await page.evaluate( ( dash ) => {
			const a = document.createElement( 'a' );
			a.href = dash + '/orders/';
			a.className = 'storesuite-print-document';
			a.textContent = 'QA print doc';
			a.id = 'qa184-print-link';
			document.body.appendChild( a );
		}, dashboardPath );

		// A plain DOM click: the injected anchor sits behind the page footer,
		// and only the jQuery delegate handler matters here.
		await page.evaluate( () =>
			( document.getElementById( 'qa184-print-link' ) as HTMLElement ).click()
		);

		// The handler must fetch the document, build a hidden iframe and print
		// from it — without navigating the dashboard away.
		// The handler appends the iframe, then prints from it 500ms later —
		// patch the iframe window's print inside that window.
		await page.waitForFunction(
			() => document.querySelectorAll( 'iframe' ).length > 0,
			undefined,
			{ timeout: 10000 }
		);
		await page.evaluate( () => {
			const frames = document.querySelectorAll( 'iframe' );
			const frame = frames[ frames.length - 1 ] as HTMLIFrameElement;
			( frame.contentWindow as any ).print = () => {
				( window as any ).__qa184Printed = true;
			};
		} );
		await expect
			.poll(
				async () => page.evaluate( () => ( window as any ).__qa184Printed ),
				{ timeout: 10000 }
			)
			.toBeTruthy();
		expect( page.url().replace( /\/$/, '' ) ).toBe(
			new URL( url, page.url() ).href.replace( /\/$/, '' )
		);
	} );

	test.afterAll( () => {
		wpCli( 'plugin deactivate woocommerce-pdf-invoices-packing-slips' );
	} );
} );
