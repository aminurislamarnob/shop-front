import React, { useState, useEffect } from 'react';
import { createRoot } from 'react-dom/client';
import {
	HashRouter as Router,
	Routes,
	Route
} from 'react-router-dom';
import './styles/styles.css';
import './styles/index.scss'; // Include your custom Sass styles
import TabPanelWrapper from './Components/TabPanelWrapper';

const App = () => (
	<Router>
		<Routes>
			<Route path="/" element={ <TabPanelWrapper /> } />
		</Routes>
	</Router>
);

document.addEventListener( 'DOMContentLoaded', () => {
	const container = document.getElementById(
		'shop-front-settings'
	);
	const root = createRoot( container );
	root.render( <App /> );
} );
