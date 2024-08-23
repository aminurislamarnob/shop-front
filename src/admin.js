import React, { useState, useEffect } from 'react';
import { createRoot } from 'react-dom/client';
import {
	HashRouter as Router,
	Routes,
	Route
} from 'react-router-dom';
import './styles/styles.css';
import './styles/index.scss'; // Include your custom Sass styles
import Layout from './Components/Layout';
import OrderSettings from './Components/OrderSettings';
import GeneralSettings from './Components/GeneralSettings';
import ProductSettings from './Components/ProductSettings';

const App = () => (
	<Router>
		<Routes>
			<Route path="/" element={ <Layout /> }>
                <Route index element={ <GeneralSettings /> } />
                <Route path="product-settings" element={ <ProductSettings /> } />
                <Route path="order-settings" element={ <OrderSettings /> } />
                {/* Add more routes here */}
            </Route>
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
