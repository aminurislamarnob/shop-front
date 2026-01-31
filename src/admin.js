import React, { useState, useEffect } from 'react';
import { createRoot } from 'react-dom/client';
import { HashRouter as Router, Routes, Route } from 'react-router-dom';
import './styles/styles.css';
import './styles/index.scss'; // Include your custom Sass styles
import Layout from './Components/Layout';
import ColorsSettings from './Components/ColorsSettings';
import GeneralSettings from './Components/GeneralSettings';
import PaginationSettings from './Components/PaginationSettings';

const App = () => (
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
				{ /* Add more routes here */ }
			</Route>
		</Routes>
	</Router>
);

document.addEventListener( 'DOMContentLoaded', () => {
	const container = document.getElementById( 'storesuite-settings' );
	const root = createRoot( container );
	root.render( <App /> );
} );
