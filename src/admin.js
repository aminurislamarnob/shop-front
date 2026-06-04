/**
 * WordPress dependencies
 */
import { createRoot } from '@wordpress/element';
import { applyFilters } from '@wordpress/hooks';

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
 * Module routes are contributed via the `storesuite_admin_routes`
 * `@wordpress/hooks` filter. Each module's entry bundle (enqueued after the
 * core admin script via `wp_enqueue_script` dependencies, therefore loaded
 * BEFORE `DOMContentLoaded` fires) calls `addFilter` to push its routes:
 *
 *   addFilter( 'storesuite_admin_routes', 'staff-manager', ( routes ) => {
 *       routes.push( { path: '/staff-manager', element: StaffManagerAdmin } );
 *       return routes;
 *   } );
 *
 * Using the WordPress hooks API instead of a bespoke `window.StoreSuite.*`
 * registry matches Dokan Pro's pattern: no custom global surface, any script
 * (bundled or inline) can contribute routes, and third-party modules
 * integrate without learning a StoreSuite-specific API.
 */
const collectModuleRoutes = () => {
	const routes = applyFilters( 'storesuite_admin_routes', [] );
	if ( ! Array.isArray( routes ) ) {
		return [];
	}
	return routes.filter(
		( route ) =>
			route &&
			typeof route.path === 'string' &&
			route.path.length > 0 &&
			typeof route.element === 'function'
	);
};

const App = () => {
	const moduleRoutes = collectModuleRoutes();

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
						{ moduleRoutes.map( ( route ) => {
							const Component = route.element;
							const normalized = route.path.replace( /^\/+/, '' );
							return (
								<Route
									key={ normalized }
									path={ normalized }
									element={ <Component /> }
								/>
							);
						} ) }
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
