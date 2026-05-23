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
import PaginationSettings from './Components/PaginationSettings';

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
