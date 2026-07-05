import { compose } from '@wordpress/compose';
import { withSelect, useSelect, useDispatch } from '@wordpress/data';
import '@wordpress/notices';
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
import { SlotFillProvider, SnackbarList } from '@wordpress/components';
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
import { redirectIfAdminUrl } from '../utils/helper';
import { syncSidebar } from '../utils/sidebar-sync';

const WithReactRouterProps = ( { children } ) => {
	const location = useLocation();
	const match    = useMatch( location.pathname );
	const params   = useParams();
	const matchProp = { params, url: match?.pathname || location.pathname };
	return Children.toArray( children ).map( ( child ) =>
		cloneElement( child, { ...child.props, location, match: matchProp } )
	);
};

const Notices = () => {
	const notices = useSelect( ( select ) =>
		select( 'core/notices' )
			.getNotices()
			.filter( ( notice ) => 'snackbar' === notice.type )
	);
	const { removeNotice } = useDispatch( 'core/notices' );

	return (
		<SnackbarList
			notices={ notices }
			onRemove={ removeNotice }
			className="storesuite-analytics-snackbar"
		/>
	);
};

const PageContent = ( { page, match } ) => {
	const location = useLocation();
	const query    = getQuery();

	useEffect( () => {
		redirectIfAdminUrl();
		syncSidebar();
	}, [ location ] );

	return (
		<SlotFillProvider>
			<div className="woocommerce-layout storesuite-analytics-layout">
				<div className="woocommerce-layout__main">
					<Controller page={ page } match={ match } query={ query } />
				</div>
			</div>
			<Notices />
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
