import { doAction } from '@wordpress/hooks';
import { Suspense, lazy, useEffect, useRef, useState } from '@wordpress/element';
import { isEqual, omit } from 'lodash';
import { applyFilters, addAction, removeAction, didFilter } from '@wordpress/hooks';
import { __ } from '@wordpress/i18n';
import { getNewPath, getHistory } from '@woocommerce/navigation';
import { Spinner } from '@woocommerce/components';

const Dashboard = lazy( () =>
	import( /* webpackChunkName: "ss-analytics-dashboard" */ '../analytics/report' )
);

export const PAGES_FILTER = 'storesuite_analytics_pages_list';

export const getPages = () => {
	const pages     = [];
	const base      = [ '', __( 'Analytics', 'storesuite' ) ];
	const reportsBC = [ '/analytics/reports', __( 'Reports', 'storesuite' ) ];

	pages.push( {
		container:  Dashboard,
		path:       '*',
		breadcrumbs: [ base, reportsBC ],
		navArgs:    { id: 'storesuite-analytics-overview' },
	} );

	return applyFilters( PAGES_FILTER, pages );
};

export function usePages() {
	const [ pages, setPages ] = useState( getPages );

	useEffect( () => {
		const handleHookAdded = ( hookName ) => {
			if ( hookName === PAGES_FILTER && didFilter( PAGES_FILTER ) > 0 ) {
				setPages( getPages() );
			}
		};
		const namespace = `storesuite/watch_${ PAGES_FILTER }`;
		addAction( 'hookAdded', namespace, handleHookAdded );
		return () => removeAction( 'hookAdded', namespace );
	}, [] );

	return pages;
}

function usePrevious( value ) {
	const ref = useRef();
	useEffect( () => {
		ref.current = value;
	}, [ value ] );
	return ref.current;
}

export const Controller = ( { ...props } ) => {
	const prevProps = usePrevious( props );

	useEffect( () => {
		window.document.documentElement.scrollTop = 0;
	}, [] );

	useEffect( () => {
		if ( prevProps ) {
			const prevBaseQuery = omit( prevProps.query, 'chartType', 'filter', 'paged' );
			const baseQuery     = omit( props.query, 'chartType', 'filter', 'paged' );

			if ( prevProps.query.paged > 1 && ! isEqual( prevBaseQuery, baseQuery ) ) {
				getHistory().replace( getNewPath( { paged: 1 } ) );
			}

			if ( prevProps.match.url !== props.match.url ) {
				window.document.documentElement.scrollTop = 0;
			}

			doAction( 'storesuite_analytics_route_handler', props.match.url );
		}
	}, [ props, prevProps ] );

	const { page, match, query } = props;
	const { url, params } = match;

	function getFallback() {
		return page.fallback ? (
			<page.fallback />
		) : (
			<div className="woocommerce-layout__loading">
				<Spinner />
			</div>
		);
	}

	return (
		<Suspense fallback={ getFallback() }>
			<page.container
				params={ params }
				path={ url }
				pathMatch={ page.path }
				query={ query }
			/>
		</Suspense>
	);
};
