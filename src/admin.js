/**
 * WordPress dependencies
 */
import { createRoot } from '@wordpress/element';

/**
 * External dependencies
 */
import { HashRouter as Router, Routes, Route } from 'react-router-dom';

/**
 * Internal dependencies
 */
import './Components/LayoutStyles.css';
import { SettingsProvider } from './context/SettingsContext';
import Layout from './Components/Layout';
import ColorsSettings from './Components/ColorsSettings';
import GeneralSettings from './Components/GeneralSettings';
import ModulesSettings from './Components/ModulesSettings';
import ModuleSettings from './Components/ModuleSettings';
import PaginationSettings from './Components/PaginationSettings';

/**
 * Module registry — set up synchronously at the top of the core bundle so
 * module bundles (enqueued with this script as a dependency, therefore loaded
 * AFTER it but BEFORE DOMContentLoaded) can register their React screens
 * before the App mounts and reads the registry.
 *
 * Module bundles call `window.StoreSuite.registerScreens( slug, screens )`
 * where `screens` is `{ '/route-path': ReactComponent }`. The path becomes a
 * React Router route under the Layout outlet. Components registered this way
 * are rendered inside the same Layout/Router as the built-in routes, so they
 * inherit the snackbar host and module-aware top-nav for free.
 */
const moduleScreens = new Map();

window.StoreSuite = window.StoreSuite || {};

window.StoreSuite.registerScreens = ( slug, screens ) => {
	if ( ! slug || typeof screens !== 'object' || screens === null ) {
		return;
	}
	Object.entries( screens ).forEach( ( [ path, Component ] ) => {
		if (
			typeof path !== 'string' ||
			path.length === 0 ||
			typeof Component !== 'function'
		) {
			return;
		}
		// Normalize: store without leading slash so it matches React Router
		// child route paths. Strip duplicate slashes for safety.
		const normalized = path.replace( /^\/+/, '' );
		moduleScreens.set( normalized, { slug, Component } );
	} );
};

window.StoreSuite.getScreens = () => Array.from( moduleScreens.entries() );

const App = () => {
	const screens = window.StoreSuite.getScreens();

	return (
		<SettingsProvider>
			<Router>
				<Routes>
					<Route path="/" element={ <Layout /> }>
						<Route index element={ <GeneralSettings /> } />
						<Route
							path="appearance-settings"
							element={ <ColorsSettings /> }
						/>
						<Route
							path="pagination-settings"
							element={ <PaginationSettings /> }
						/>
						<Route path="modules" element={ <ModulesSettings /> } />
						<Route
							path="modules/:slug"
							element={ <ModuleSettings /> }
						/>
						{ screens.map( ( [ path, { Component } ] ) => (
							<Route
								key={ path }
								path={ path }
								element={ <Component /> }
							/>
						) ) }
					</Route>
				</Routes>
			</Router>
		</SettingsProvider>
	);
};

document.addEventListener( 'DOMContentLoaded', () => {
	const container = document.getElementById( 'storesuite-settings' );

	if ( container ) {
		const root = createRoot( container );
		root.render( <App /> );
	}
} );
