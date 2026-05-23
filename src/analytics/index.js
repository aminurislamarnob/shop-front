/* global storeSuiteAnalyticsConfig */

// Set webpack public path for async chunks before any imports that might trigger chunk loading.
if ( typeof storeSuiteAnalyticsConfig !== 'undefined' && storeSuiteAnalyticsConfig.assetsPath ) {
	// eslint-disable-next-line
	__webpack_public_path__ = storeSuiteAnalyticsConfig.assetsPath;
}

import { createRoot } from '@wordpress/element';
import { PageLayout } from './layout';
import { ErrorBoundary } from './error-boundary';
import './stylesheets/_index.scss';

const rootElement = document.getElementById( 'storesuite-analytics-app' );

if ( rootElement ) {
	createRoot( rootElement ).render(
		<ErrorBoundary>
			<PageLayout />
		</ErrorBoundary>
	);
}
