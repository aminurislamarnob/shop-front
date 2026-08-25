import { test, expect } from '../../../utils/test';
import { MANAGER_STATE } from '../../../utils/authStates';
import { dashboardPath } from '../../../utils/testData';
import { wpEval } from './qa';

test.use( { storageState: MANAGER_STATE } );

/**
 * Simulates updating from 1.2.1 rather than a fresh install: version markers
 * rolled back, the notifications table dropped, and the 1.2.1-era rewrite
 * rules restored (no notifications / import-products rules). The first
 * request after the "update" must self-heal — no manual permalink re-save.
 */
test.describe( 'QA184 upgrade path 1.2.1 → 1.3.0', () => {
	test.beforeAll( () => {
		const out = wpEval(
			`update_option( 'storesuite_rewrite_version', '1.2.1' );
			update_option( 'storesuite_db_version', '1.2.1' );
			global $wpdb;
			$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}storesuite_notifications" );
			// 1.2.1 predates notifications entirely: no cursor option either.
			delete_option( 'storesuite_notifications_last_id' );
			\\PluginizeLab\\StoreSuite\\Cache::delete( 'notifications_last_id' );
			$rules = get_option( 'rewrite_rules' );
			$stripped = 0;
			foreach ( array_keys( (array) $rules ) as $pattern ) {
				if ( false !== strpos( $pattern, 'notifications' ) || false !== strpos( $pattern, 'import-products' ) ) {
					unset( $rules[ $pattern ] );
					$stripped++;
				}
			}
			update_option( 'rewrite_rules', $rules );
			echo 'stripped=' . $stripped;`
		);
		expect( out ).toMatch( /stripped=[1-9]/ );
	} );

	test( '/notifications/ resolves without re-saving permalinks', async ( { page } ) => {
		const response = await page.goto( dashboardPath + '/notifications/' );
		expect( response!.status() ).toBe( 200 );
		// The freshly recreated table is empty, so the page renders its empty
		// state — the heading proves the endpoint resolved to the right template.
		await expect(
			page.getByRole( 'heading', { name: 'Notifications' } ).first()
		).toBeVisible();
		// Not the 404 template.
		await expect( page.locator( 'body' ) ).not.toContainText( /page can.?t be found|not found/i );
	} );

	test( '/import-products/ resolves without re-saving permalinks', async ( { page } ) => {
		const response = await page.goto( dashboardPath + '/import-products/' );
		expect( response!.status() ).toBe( 200 );
		await expect( page.locator( '#storesuite-import-file' ) ).toBeAttached();
	} );

	test( 'upgrader restored version markers and recreated the table', async () => {
		const out = wpEval(
			`global $wpdb;
			$table = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $wpdb->prefix . 'storesuite_notifications' ) );
			echo get_option( 'storesuite_rewrite_version' ) . '|' . get_option( 'storesuite_db_version' ) . '|' . ( $table ? 'table-ok' : 'table-missing' );`
		);
		expect( out ).toBe( '1.3.0|1.3.0|table-ok' );
	} );
} );
