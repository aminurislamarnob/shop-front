import { addQueryArgs } from '@wordpress/url';
import apiFetch from '@wordpress/api-fetch';
import { identity } from 'lodash';
import { getIdsFromQuery } from '@woocommerce/navigation';

const NAMESPACE = '/wc-analytics';

export function getRequestByIdString( path, handleData = identity ) {
	return function( queryString = '' ) {
		const idList = getIdsFromQuery( queryString );
		if ( idList.length < 1 ) {
			return Promise.resolve( [] );
		}
		const payload = {
			include:  idList.join( ',' ),
			per_page: idList.length,
		};
		return apiFetch( { path: addQueryArgs( path, payload ) } ).then(
			( data ) => data.map( handleData )
		);
	};
}

export const getCategoryLabels = getRequestByIdString(
	NAMESPACE + '/products/categories',
	( cat ) => ( { key: cat.id, label: cat.name } )
);

export const getProductLabels = getRequestByIdString(
	NAMESPACE + '/products',
	( product ) => ( { key: product.id, label: product.name } )
);

export const getVariationLabels = getRequestByIdString(
	NAMESPACE + '/variations',
	( variation ) => ( { key: variation.id, label: variation.name } )
);

export const getCouponLabels = getRequestByIdString(
	NAMESPACE + '/coupons',
	( coupon ) => ( { key: coupon.id, label: coupon.code } )
);

export const getTaxRateLabels = getRequestByIdString(
	NAMESPACE + '/taxes',
	( tax ) => ( { key: tax.id, label: tax.name } )
);
