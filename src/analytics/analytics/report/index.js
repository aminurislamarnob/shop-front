import { Fragment, Suspense, lazy, useState, useEffect } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { Spinner } from '@woocommerce/components';
import { getQuery } from '@woocommerce/navigation';
import getReports from './get-reports';

function reportHref( reportName ) {
	const params = new URLSearchParams( window.location.search );
	params.set( 'report', reportName );
	return window.location.pathname + '?' + params.toString();
}

function ReportNav( { reports, currentReport, onSelectReport } ) {
	return (
		<nav className="storesuite-analytics-nav">
			<ul>
				{ reports.map( ( report ) => (
					<li key={ report.report } className={ currentReport === report.report ? 'active' : '' }>
						<a
							href={ reportHref( report.report ) }
							onClick={ ( e ) => {
								e.preventDefault();
								onSelectReport( report.report );
							} }
						>
							{ report.title }
						</a>
					</li>
				) ) }
			</ul>
		</nav>
	);
}

export default function ReportPage( { query, path } ) {
	const reports        = getReports();
	const reportQuery    = query || getQuery();
	const reportName     = reportQuery.report || ( reports[ 0 ] && reports[ 0 ].report ) || 'overview';
	const [ currentReport, setCurrentReport ] = useState( reportName );

	useEffect( () => {
		setCurrentReport( reportName );
	}, [ reportName ] );

	const activeReport = reports.find( ( r ) => r.report === currentReport ) || reports[ 0 ];

	const ReportComponent = activeReport?.component;

	return (
		<div className="storesuite-analytics-reports">
			<div className="storesuite-analytics-reports-header">
				<h2>{ __( 'Reports', 'storesuite' ) }</h2>
				<ReportNav
					reports={ reports }
					currentReport={ currentReport }
					onSelectReport={ ( reportName ) => {
					setCurrentReport( reportName );
					window.history.pushState( {}, '', reportHref( reportName ) );
				} }
				/>
			</div>
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
