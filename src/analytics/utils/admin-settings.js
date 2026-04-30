/* global storeSuiteAnalyticsSettings */
import { getSetting } from '@woocommerce/settings';

const mutableSources = [ 'wcAdminSettings', 'preloadSettings' ];

const wcAdminSettings = getSetting( 'admin', {} );
const storeSuiteSettings =
	typeof storeSuiteAnalyticsSettings !== 'undefined' ? storeSuiteAnalyticsSettings : {};

const adminSettings = {
	...wcAdminSettings,
	...storeSuiteSettings,
};

const ADMIN_SETTINGS_SOURCE = Object.keys( adminSettings ).reduce( ( source, key ) => {
	if ( ! mutableSources.includes( key ) ) {
		source[ key ] = adminSettings[ key ];
	}
	return source;
}, {} );

export function getAdminSetting( name, fallback = false, filter = ( val ) => val ) {
	if ( mutableSources.includes( name ) ) {
		// Mutable settings should be accessed via data store.
		return fallback;
	}
	const value = Object.prototype.hasOwnProperty.call( ADMIN_SETTINGS_SOURCE, name )
		? ADMIN_SETTINGS_SOURCE[ name ]
		: fallback;
	return filter( value, fallback );
}

export function setAdminSetting( name, value, filter = ( val ) => val ) {
	if ( mutableSources.includes( name ) ) {
		return;
	}
	ADMIN_SETTINGS_SOURCE[ name ] = filter( value );
}

export const LOCALE        = getSetting( 'locale', {} );
export const CURRENCY      = getSetting( 'currency', {} );
export const ORDER_STATUSES = getAdminSetting( 'orderStatuses', {} );
