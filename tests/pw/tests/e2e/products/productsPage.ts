import type { Page, Locator } from '@playwright/test';
import { dashboardPath } from '../../../utils/testData';

/**
 * Page object for the Products list of the frontend dashboard.
 */
export class ProductsPage {
	readonly page: Page;
	readonly table: Locator;
	readonly rows: Locator;
	readonly selectAll: Locator;
	readonly rowCheckboxes: Locator;

	constructor( page: Page ) {
		this.page = page;
		this.table = page.locator( '.my-storesuite-product-list-table' );
		this.rows = this.table.locator( 'tbody tr.single-product-item' );
		this.selectAll = this.table.locator( '.storesuite-bulk-select-all' );
		this.rowCheckboxes = this.table.locator( '.storesuite-bulk-cb' );
	}

	async goto( query = '' ) {
		await this.page.goto( `${ dashboardPath }/products/${ query }` );
	}

	row( name: string ): Locator {
		return this.rows.filter( { has: this.page.getByRole( 'link', { name, exact: true } ) } );
	}

	async searchFor( term: string ) {
		await this.page.locator( '#search_by' ).fill( term );
		await this.page.locator( '#search_by' ).press( 'Enter' );
	}

	/**
	 * Cell carrying an inline editor for the given field inside a row.
	 */
	inlineCell( row: Locator, field: string ): Locator {
		return row.locator( `td.storesuite-inline-cell[data-inline-field="${ field }"]` );
	}
}
