/* global storeSuiteDashboardSettings */
import { getSetting } from '@woocommerce/settings';

const mutableSources = [ 'wcAdminSettings', 'preloadSettings' ];

const wcAdminSettings = getSetting( 'admin', {} );
const dashboardSettings =
	typeof storeSuiteDashboardSettings !== 'undefined' ? storeSuiteDashboardSettings : {};

const adminSettings = {
	...wcAdminSettings,
	...dashboardSettings,
};

const ADMIN_SETTINGS_SOURCE = Object.keys( adminSettings ).reduce( ( source, key ) => {
	if ( ! mutableSources.includes( key ) ) {
		source[ key ] = adminSettings[ key ];
	}
	return source;
}, {} );

export function getAdminSetting( name, fallback = false, filter = ( val ) => val ) {
	if ( mutableSources.includes( name ) ) {
		return fallback;
	}
	const value = Object.prototype.hasOwnProperty.call( ADMIN_SETTINGS_SOURCE, name )
		? ADMIN_SETTINGS_SOURCE[ name ]
		: fallback;
	return filter( value, fallback );
}

export const LOCALE   = getSetting( 'locale', {} );
export const CURRENCY = getSetting( 'currency', {} );
