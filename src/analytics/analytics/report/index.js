import { Suspense } from '@wordpress/element';
import { Spinner } from '@woocommerce/components';
import { getQuery } from '@woocommerce/navigation';
import getReports from './get-reports';

export default function ReportPage( { query, path } ) {
	const reports     = getReports();
	const reportQuery = query || getQuery();
	const reportName  = reportQuery.report || ( reports[ 0 ] && reports[ 0 ].report ) || 'overview';

	const activeReport    = reports.find( ( r ) => r.report === reportName ) || reports[ 0 ];
	const ReportComponent = activeReport?.component;

	return (
		<div className="storesuite-analytics-reports">
			<div className="storesuite-analytics-reports-content">
				{ ReportComponent && (
					<Suspense fallback={ <div className="storesuite-analytics-loading"><Spinner /></div> }>
						<ReportComponent query={ reportQuery } path={ path || '' } />
					</Suspense>
				) }
			</div>
		</div>
	);
}
