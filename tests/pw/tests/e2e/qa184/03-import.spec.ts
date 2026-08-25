import { test, expect } from '../../../utils/test';
import { MANAGER_STATE } from '../../../utils/authStates';
import { dashboardPath } from '../../../utils/testData';
import { SCRATCH, wpEval } from './qa';
import type { Page } from '@playwright/test';

test.use( { storageState: MANAGER_STATE } );

/** Drive the wizard from upload through to the done screen. */
async function runImport(
	page: Page,
	csv: string,
	updateExisting: boolean
): Promise<void> {
	await page.goto( dashboardPath + '/import-products/' );
	await page.locator( '#storesuite-import-file' ).setInputFiles( csv );
	if ( updateExisting ) {
		// Styled as a switch: the input itself sits off-viewport, so drive it
		// through its label like a user would.
		await page.locator( 'label[for="woocommerce-importer-update-existing"]' ).click();
		await expect(
			page.locator( '#woocommerce-importer-update-existing' )
		).toBeChecked();
	}
	await page.locator( 'button.storesuite-import-submit' ).click();

	// Mapping step.
	await expect( page.locator( 'button.storesuite-import-btn--run' ) ).toBeVisible();
	await page.locator( 'button.storesuite-import-btn--run' ).click();

	// Progress runs the WooCommerce AJAX importer, then lands on done.
	await expect( page.locator( '.storesuite-import-done' ) ).toBeVisible( {
		timeout: 60000,
	} );
}

/** Read a stat value off the done screen by its label. */
async function statValue( page: Page, label: RegExp ): Promise<number> {
	const stat = page
		.locator( '.storesuite-import-done__stat' )
		.filter( { has: page.locator( '.storesuite-import-done__stat-label', { hasText: label } ) } );
	if ( ! ( await stat.count() ) ) {
		return 0;
	}
	const text = await stat.first().locator( '.storesuite-import-done__stat-value' ).innerText();
	return Number( text.replace( /[^\d]/g, '' ) ) || 0;
}

test.describe( 'QA184 CSV import', () => {
	test.beforeAll( () => {
		// A clean slate for the QA SKUs.
		wpEval(
			`foreach ( array( 'QA184-A', 'QA184-B', 'QA184-C', 'QA184-D' ) as $sku ) {
				$id = wc_get_product_id_by_sku( $sku );
				if ( $id ) { wp_delete_post( $id, true ); }
			}
			echo 'clean';`
		);
	} );

	test( 'upload → mapping → import → done with the right counts', async ( { page } ) => {
		await runImport( page, `${ SCRATCH }/import-new.csv`, false );

		expect( await statValue( page, /imported/i ) ).toBe( 3 );
		expect( await statValue( page, /failed/i ) ).toBe( 0 );

		const created = wpEval(
			`echo (int) (bool) wc_get_product_id_by_sku( 'QA184-A' );
			echo (int) (bool) wc_get_product_id_by_sku( 'QA184-B' );
			echo (int) (bool) wc_get_product_id_by_sku( 'QA184-C' );`
		);
		expect( created ).toBe( '111' );
		await page.screenshot( { path: `${ SCRATCH }/shots/import-done-new.png` } );
	} );

	test( 'update existing products updates by SKU instead of duplicating', async ( {
		page,
	} ) => {
		await runImport( page, `${ SCRATCH }/import-update.csv`, true );

		expect( await statValue( page, /updated/i ) ).toBe( 3 );
		expect( await statValue( page, /imported/i ) ).toBe( 0 );

		const state = wpEval(
			`$id = wc_get_product_id_by_sku( 'QA184-A' );
			$p = wc_get_product( $id );
			$dupes = get_posts( array( 'post_type' => 'product', 'post_status' => 'any', 'meta_key' => '_sku', 'meta_value' => 'QA184-A', 'fields' => 'ids', 'numberposts' => -1 ) );
			echo $p->get_regular_price() . '|' . count( $dupes ) . '|' . $p->get_name();`
		);
		const [ price, dupes, name ] = state.split( '|' );
		expect( Number( price ) ).toBe( 31 );
		expect( dupes ).toBe( '1' );
		expect( name ).toContain( 'v2' );
	} );

	test( 'a bad row lands on the done step with a readable import log', async ( {
		page,
	} ) => {
		// Row 2 reuses the seeded HOOD-1 SKU without update-existing → WooCommerce
		// records it as a skipped row, and the done step must surface the log.
		await runImport( page, `${ SCRATCH }/import-bad.csv`, false );

		expect( await statValue( page, /imported/i ) ).toBe( 1 );
		expect(
			( await statValue( page, /skipped/i ) ) + ( await statValue( page, /failed/i ) )
		).toBe( 1 );

		const toggle = page.locator( '.storesuite-import-done__log-toggle' );
		await expect( toggle ).toBeVisible();
		await toggle.click();
		const log = page.locator( '#storesuite-import-error-log' );
		await expect( log ).toBeVisible();
		await expect( log.locator( 'table' ) ).toContainText( /sku/i );
		await page.screenshot( { path: `${ SCRATCH }/shots/import-bad-row-log.png`, fullPage: true } );
	} );

	test.afterAll( () => {
		wpEval(
			`foreach ( array( 'QA184-A', 'QA184-B', 'QA184-C', 'QA184-D' ) as $sku ) {
				$id = wc_get_product_id_by_sku( $sku );
				if ( $id ) { wp_delete_post( $id, true ); }
			}
			echo 'clean';`
		);
	} );
} );
