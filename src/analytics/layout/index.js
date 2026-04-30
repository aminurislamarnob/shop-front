import { compose } from '@wordpress/compose';
import { withSelect } from '@wordpress/data';
import { Component, Suspense, Fragment, Children, cloneElement, useEffect } from '@wordpress/element';
import {
	unstable_HistoryRouter as HistoryRouter,
	Route,
	Routes,
	useLocation,
	useMatch,
	useParams,
} from 'react-router-dom';
import { identity } from 'lodash';
import { SlotFillProvider } from '@wordpress/components';
import {
	getHistory,
	getQuery,
} from '@woocommerce/navigation';
import {
	withOptionsHydration,
} from '@woocommerce/data';

import { Controller, usePages } from './controller';
import { getAdminSetting } from '../utils/admin-settings';
import { storeSuiteConfig } from '../config';

// Patch getHistory() so any WC admin URL (admin.php?page=wc-admin&...) that gets
// pushed/replaced is silently rewritten to a frontend-friendly URL before it hits
// the browser's address bar or React Router's state.
( () => {
	const h     = getHistory();
	const _push    = h.push.bind( h );
	const _replace = h.replace.bind( h );

	function toFrontendPath( to ) {
		if ( typeof to !== 'string' || ! to.includes( 'admin.php' ) ) {
			return to;
		}
		const url    = new URL( to, window.location.href );
		const params = new URLSearchParams( url.search );
		params.delete( 'page' );
		params.delete( 'path' );
		// Keep the active report tab when the caller didn't specify one.
		if ( ! params.has( 'report' ) ) {
			const current = new URLSearchParams( window.location.search ).get( 'report' );
			if ( current ) {
				params.set( 'report', current );
			}
		}
		const qs = params.toString();
		return window.location.pathname + ( qs ? '?' + qs : '' );
	}

	h.push    = ( to, state ) => _push( toFrontendPath( to ), state );
	h.replace = ( to, state ) => _replace( toFrontendPath( to ), state );
} )();

const WithReactRouterProps = ( { children } ) => {
	const location = useLocation();
	const match    = useMatch( location.pathname );
	const params   = useParams();
	const matchProp = { params, url: match?.pathname || location.pathname };
	return Children.toArray( children ).map( ( child ) =>
		cloneElement( child, { ...child.props, location, match: matchProp } )
	);
};

const PageContent = ( { page, match } ) => {
	const query = getQuery();
	return (
		<SlotFillProvider>
			<div className="woocommerce-layout storesuite-analytics-layout">
				<div className="woocommerce-layout__main">
					<Controller page={ page } match={ match } query={ query } />
				</div>
			</div>
		</SlotFillProvider>
	);
};

const Layout = ( { page } ) => {
	const location  = useLocation();
	const match     = useMatch( location.pathname );
	const params    = useParams();
	const matchProp = { params, url: match?.pathname || location.pathname };

	return <PageContent page={ page } match={ matchProp } />;
};

const _PageLayout = () => {
	const pages = usePages();
	const path  = document.location.pathname;

	return (
		<HistoryRouter history={ getHistory() }>
			<Routes>
				{ pages.map( ( page ) => (
					<Route
						key={ page.path }
						path={ page.path }
						element={ <Layout page={ page } /> }
					/>
				) ) }
			</Routes>
		</HistoryRouter>
	);
};

export const PageLayout = compose(
	window.wcSettings?.admin
		? withOptionsHydration( {
			...getAdminSetting( 'preloadOptions', {} ),
		  } )
		: identity
)( _PageLayout );
