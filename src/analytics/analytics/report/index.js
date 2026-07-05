import { __ } from '@wordpress/i18n';
import { Suspense } from '@wordpress/element';
import { EmptyContent, Spinner } from '@woocommerce/components';
import { getQuery, getNewPath } from '@woocommerce/navigation';
import getReports from './get-reports';

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
				<Suspense fallback={ <div className="storesuite-analytics-loading"><Spinner /></div> }>
					<ReportComponent query={ reportQuery } path={ path || '' } />
				</Suspense>
			</div>
		</div>
	);
}
