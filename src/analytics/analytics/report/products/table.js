import { __, _n, sprintf, _x } from '@wordpress/i18n';
import { Component } from '@wordpress/element';
import { compose } from '@wordpress/compose';
import { withSelect } from '@wordpress/data';
import { Link, Tag } from '@woocommerce/components';
import { formatValue } from '@woocommerce/number';
import { getNewPath, getPersistedQuery } from '@woocommerce/navigation';
import { CurrencyContext } from '@woocommerce/currency';
import { itemsStore } from '@woocommerce/data';
import ReportTable from '../../components/report-table';
import { getAdminSetting } from '../../../utils/admin-settings';

const manageStock   = getAdminSetting( 'manageStock', 'no' );
const stockStatuses = getAdminSetting( 'stockStatuses', {} );

function isLowStock( stockStatus, stockQuantity, lowStockAmount ) {
	return !! lowStockAmount && stockStatus === 'instock' && stockQuantity <= lowStockAmount;
}

class ProductsReportTable extends Component {
	constructor() {
		super();
		this.getHeadersContent = this.getHeadersContent.bind( this );
		this.getRowsContent    = this.getRowsContent.bind( this );
		this.getSummary        = this.getSummary.bind( this );
	}

	getHeadersContent() {
		return [
			{ label: __( 'Product title', 'storesuite' ), key: 'product_name', required: true, isLeftAligned: true, isSortable: true },
			{ label: __( 'SKU', 'storesuite' ),           key: 'sku',          hiddenByDefault: true, isSortable: true },
			{ label: __( 'Items sold', 'storesuite' ),    key: 'items_sold',   required: true, defaultSort: true, isSortable: true, isNumeric: true },
			{ label: __( 'Net sales', 'storesuite' ),     key: 'net_revenue',  required: true, isSortable: true, isNumeric: true },
			{ label: __( 'Orders', 'storesuite' ),        key: 'orders_count', isSortable: true, isNumeric: true },
			{ label: __( 'Category', 'storesuite' ),      key: 'product_cat' },
			{ label: __( 'Variations', 'storesuite' ),    key: 'variations',   isSortable: true },
			manageStock === 'yes' ? { label: __( 'Status', 'storesuite' ), key: 'stock_status' } : null,
			manageStock === 'yes' ? { label: __( 'Stock', 'storesuite' ),  key: 'stock', isNumeric: true } : null,
		].filter( Boolean );
	}

	getRowsContent( data = [] ) {
		const { query, categories } = this.props;
		const persistedQuery = getPersistedQuery( query );
		const { render: renderCurrency, getCurrencyConfig } = this.context;
		const currency = getCurrencyConfig();

		return data.map( ( row ) => {
			const { product_id, items_sold, net_revenue, orders_count } = row;
			const {
				category_ids    = [],
				low_stock_amount,
				manage_stock,
				sku             = '',
				stock_status,
				stock_quantity,
				variations      = [],
				name            = '',
			} = row.extended_info || {};

			const productLink = getNewPath( persistedQuery, '/analytics/products', {
				filter:   'single_product',
				products: product_id,
			} );
			const ordersLink = getNewPath( persistedQuery, '/analytics/orders', {
				filter:           'advanced',
				product_includes: product_id,
			} );

			const productCategories = category_ids && categories
				? category_ids.map( ( id ) => categories.get( id ) ).filter( Boolean )
				: [];

			const categoryDisplay = (
				<div className="woocommerce-table__product-categories">
					{ productCategories[ 0 ] && (
						<Link
							href={ getNewPath( persistedQuery, '/analytics/categories', {
								filter:     'single_category',
								categories: productCategories[ 0 ].id,
							} ) }
							type="wc-admin"
						>
							{ productCategories[ 0 ].name }
						</Link>
					) }
					{ productCategories.length > 1 && (
						<Tag
							label={ sprintf(
								/* translators: %d: number of additional categories */
								_x( '+%d more', 'categories', 'storesuite' ),
								productCategories.length - 1
							) }
							popoverContents={ productCategories.slice( 1 ).map( ( cat ) => (
								<Link
									key={ cat.id }
									href={ getNewPath( persistedQuery, '/analytics/categories', {
										filter:     'single_category',
										categories: cat.id,
									} ) }
									type="wc-admin"
								>
									{ cat.name }
								</Link>
							) ) }
						/>
					) }
				</div>
			);

			const stockStatusLabel = isLowStock( stock_status, stock_quantity, low_stock_amount )
				? __( 'Low', 'storesuite' )
				: ( stockStatuses[ stock_status ] || '' );

			return [
				{
					display: <Link href={ productLink } type="wc-admin">{ name }</Link>,
					value:   name,
				},
				{ display: sku, value: sku },
				{ display: formatValue( currency, 'number', items_sold ),  value: Number( items_sold ) },
				{ display: renderCurrency( net_revenue ),                   value: Number( net_revenue ) },
				{
					display: <Link href={ ordersLink } type="wc-admin">{ orders_count }</Link>,
					value:   Number( orders_count ),
				},
				{
					display: categoryDisplay,
					value:   productCategories.map( ( c ) => c.name ).join( ', ' ),
				},
				{
					display: formatValue( currency, 'number', variations.length ),
					value:   variations.length,
				},
				manageStock === 'yes' ? {
					display: manage_stock ? stockStatusLabel : __( 'N/A', 'storesuite' ),
					value:   manage_stock ? stockStatuses[ stock_status ] : null,
				} : null,
				manageStock === 'yes' ? {
					display: manage_stock
						? formatValue( currency, 'number', stock_quantity )
						: __( 'N/A', 'storesuite' ),
					value: stock_quantity,
				} : null,
			].filter( Boolean );
		} );
	}

	getSummary( totals, totalResults = 0 ) {
		const { items_sold = 0, net_revenue = 0, orders_count = 0 } = totals;
		const { formatAmount, getCurrencyConfig } = this.context;
		const currency = getCurrencyConfig();
		return [
			{ label: _n( 'product', 'products', totalResults, 'storesuite' ), value: formatValue( currency, 'number', totalResults ) },
			{ label: __( 'Items sold', 'storesuite' ),                         value: formatValue( currency, 'number', items_sold ) },
			{ label: __( 'Net sales', 'storesuite' ),                          value: formatAmount( net_revenue ) },
			{ label: __( 'Orders', 'storesuite' ),                             value: formatValue( currency, 'number', orders_count ) },
		];
	}

	render() {
		const { query, filters, advancedFilters, isRequesting, baseSearchQuery, hideCompare } = this.props;
		return (
			<ReportTable
				endpoint="products"
				getHeadersContent={ this.getHeadersContent }
				getRowsContent={ this.getRowsContent }
				getSummary={ this.getSummary }
				summaryFields={ [ 'items_sold', 'net_revenue', 'orders_count' ] }
				isRequesting={ isRequesting }
				itemIdField="product_id"
				query={ query }
				searchBy="products"
				compareBy={ hideCompare ? undefined : 'products' }
				compareParam="filter"
				title={ __( 'Products', 'storesuite' ) }
				columnPrefsKey="products_report_columns"
				filters={ filters }
				advancedFilters={ advancedFilters }
				baseSearchQuery={ baseSearchQuery }
				tableQuery={ {
					orderby:      query.orderby || 'items_sold',
					order:        query.order || 'desc',
					extended_info: true,
					segmentby:    query.segmentby,
				} }
			/>
		);
	}
}

ProductsReportTable.contextType = CurrencyContext;

export default compose(
	withSelect( ( select, props ) => {
		const { isRequesting, query } = props;
		if ( isRequesting || ( query.search && ( ! query.products || ! query.products.length ) ) ) {
			return {};
		}
		const { getItems, getItemsError, isResolving } = select( itemsStore );
		const itemsQuery = { per_page: -1 };
		return {
			categories:   getItems( 'categories', itemsQuery ),
			isError:      Boolean( getItemsError( 'categories', itemsQuery ) ),
			isRequesting: isResolving( 'getItems', [ 'categories', itemsQuery ] ),
		};
	} )
)( ProductsReportTable );
