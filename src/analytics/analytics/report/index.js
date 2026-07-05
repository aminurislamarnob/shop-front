import { __ } from '@wordpress/i18n';
import { Suspense } from '@wordpress/element';
import { EmptyContent, Spinner } from '@woocommerce/components';
import { getQuery, getNewPath } from '@woocommerce/navigation';
import getReports from './get-reports';

const REPORT_DESCRIPTIONS = {
	overview:   __( 'A quick look at how your store is performing.', 'storesuite' ),
	revenue:    __( 'Track gross and net sales, taxes, shipping, and refunds over time.', 'storesuite' ),
	orders:     __( 'Analyze order volume, average order value, and order trends.', 'storesuite' ),
	products:   __( 'See which products are driving your sales.', 'storesuite' ),
	variations: __( 'Compare performance across product variations.', 'storesuite' ),
	categories: __( 'Understand which product categories sell best.', 'storesuite' ),
	coupons:    __( 'Measure how your discounts impact orders and revenue.', 'storesuite' ),
	taxes:      __( 'Review the taxes collected on your orders by tax code.', 'storesuite' ),
	downloads:  __( 'Monitor digital product download activity.', 'storesuite' ),
	stock:      __( 'Keep an eye on inventory levels and stock status.', 'storesuite' ),
	customers:  __( 'Get to know your customers and their purchase behavior.', 'storesuite' ),
	settings:   __( 'Customize how your analytics reports are calculated.', 'storesuite' ),
};

const ReportHeader = ( { report, title } ) => (
	<div className="storesuite-analytics-page-header">
		<h3>{ title }</h3>
		{ REPORT_DESCRIPTIONS[ report ] && (
			<p>{ REPORT_DESCRIPTIONS[ report ] }</p>
		) }
	</div>
);

const NoMatch = () => (
	<div className="storesuite-analytics-reports">
		<div className="storesuite-analytics-reports-content">
			<EmptyContent
				title={ __( 'Report not found', 'storesuite' ) }
				message={ __(
					'Sorry, there is no report matching this URL.',
					'storesuite'
				) }
				actionLabel={ __( 'Go to Overview', 'storesuite' ) }
				actionURL={ getNewPath( { report: 'overview' } ) }
			/>
		</div>
	</div>
);

export default function ReportPage( { query, path } ) {
	const reports     = getReports();
	const reportQuery = query || getQuery();
	const reportName  = reportQuery.report || 'overview';

	const activeReport = reports.find( ( r ) => r.report === reportName );

	if ( ! activeReport || ! activeReport.component ) {
		return <NoMatch />;
	}

	const ReportComponent = activeReport.component;

	return (
		<div className="storesuite-analytics-reports">
			<div className="storesuite-analytics-reports-content">
				<ReportHeader
					report={ activeReport.report }
					title={ activeReport.title }
				/>
				<Suspense fallback={ <div className="storesuite-analytics-loading"><Spinner /></div> }>
					<ReportComponent query={ reportQuery } path={ path || '' } />
				</Suspense>
			</div>
		</div>
	);
}
