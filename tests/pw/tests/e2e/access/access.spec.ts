import { test, expect } from '../../../utils/test';
import { MANAGER_STATE, CUSTOMER_STATE } from '../../../utils/authStates';
import { dashboardPath } from '../../../utils/testData';

/**
 * Access control: only users who may manage the store reach the dashboard.
 */
test.describe( 'logged-out visitors', () => {
	test( 'are redirected away from the dashboard', async ( { page } ) => {
		await page.goto( `${ dashboardPath }/` );

		await expect( page ).toHaveURL( /my-account/ );
		await expect( page.locator( '.my-storesuite-sidebar' ) ).toHaveCount( 0 );
	} );
} );

test.describe( 'customers', () => {
	test.use( { storageState: CUSTOMER_STATE } );

	test( 'are redirected away from the dashboard', async ( { page } ) => {
		await page.goto( `${ dashboardPath }/` );

		await expect( page ).not.toHaveURL( new RegExp( dashboardPath ) );
		await expect( page.locator( '.my-storesuite-sidebar' ) ).toHaveCount( 0 );
	} );
} );

test.describe( 'shop managers', () => {
	test.use( { storageState: MANAGER_STATE } );

	test( 'reach the dashboard', async ( { page } ) => {
		await page.goto( `${ dashboardPath }/` );

		await expect( page ).toHaveURL( new RegExp( dashboardPath ) );
		await expect( page.locator( '.my-storesuite-sidebar' ) ).toBeVisible();
	} );
} );
