/* global storeSuiteDashboardConfig */

if (
	typeof storeSuiteDashboardConfig !== 'undefined' &&
	storeSuiteDashboardConfig.assetsPath
) {
	// eslint-disable-next-line
	__webpack_public_path__ = storeSuiteDashboardConfig.assetsPath;
}

import { createRoot } from '@wordpress/element';
import { App } from './App';
import { ErrorBoundary } from './error-boundary';
import './stylesheets/_index.scss';

const rootElement = document.getElementById( 'storesuite-dashboard-app' );

if ( rootElement ) {
	createRoot( rootElement ).render(
		<ErrorBoundary>
			<App />
		</ErrorBoundary>
	);
}
