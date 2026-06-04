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
import StaffManagerAdmin from './Components/StaffManagerAdmin';

const App = () => (
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
					<Route
						path="staff-manager"
						element={ <StaffManagerAdmin /> }
					/>
				</Route>
			</Routes>
		</Router>
	</SettingsProvider>
);

document.addEventListener( 'DOMContentLoaded', () => {
	const container = document.getElementById( 'storesuite-settings' );

	if ( container ) {
		const root = createRoot( container );
		root.render( <App /> );
	}
} );
