import { useRef, useEffect } from '@wordpress/element';

/*
 * Wraps a WooCommerce TableCard so its body cells carry a `data-label`
 * mirroring their column header. On mobile the stylesheet uses that label
 * (via `::before`) to render the table as stacked "label: value" cards,
 * matching the frontend product list design.
 *
 * WooCommerce's Table does not emit per-cell labels, so we copy them from
 * the `<thead>` after each render and keep them in sync with a
 * MutationObserver (the table re-renders on data/pagination changes).
 */
export default function ResponsiveTable( { children, className = '' } ) {
	const ref = useRef( null );

	useEffect( () => {
		const root = ref.current;

		if ( ! root ) {
			return undefined;
		}

		const labelCells = () => {
			root.querySelectorAll( '.woocommerce-table__table table' ).forEach(
				( table ) => {
					const headers = Array.from(
						table.querySelectorAll( '.woocommerce-table__header' )
					).map( ( th ) => th.textContent.trim() );

					if ( ! headers.length ) {
						return;
					}

					table.querySelectorAll( 'tbody tr' ).forEach( ( row ) => {
						// First column is a th[scope="row"]; include both th
						// and td so labels stay aligned with the headers.
						const cells = row.querySelectorAll(
							'.woocommerce-table__item'
						);

						cells.forEach( ( cell, i ) => {
							if ( headers[ i ] ) {
								cell.setAttribute( 'data-label', headers[ i ] );
							}
						} );
					} );
				}
			);
		};

		labelCells();

		const observer = new window.MutationObserver( labelCells );
		observer.observe( root, { childList: true, subtree: true } );

		return () => observer.disconnect();
	}, [] );

	return (
		<div
			className={ `storesuite-responsive-table ${ className }`.trim() }
			ref={ ref }
		>
			{ children }
		</div>
	);
}
