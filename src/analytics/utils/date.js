import { SETTINGS_STORE_NAME, OPTIONS_STORE_NAME } from '@woocommerce/data';

/**
 * WooCommerce's own default when no range is configured.
 */
export const DEFAULT_DATE_RANGE = 'period=month&compare=previous_year';

/**
 * Resolve the store's default date range query string.
 *
 * On the StoreSuite frontend `wcAdminSettings` is not hydrated into the
 * settings store the way it is in wp-admin, so
 * `getSetting( 'wc_admin', 'wcAdminSettings' ).woocommerce_default_date_range`
 * comes back undefined. Most date helpers fall back to a sane month default,
 * but `getAllowedIntervalsForQuery` falls back to an *empty* period (yielding
 * only "By day"). Resolve the value explicitly — from the settings store, then
 * the preloaded options store, then WC's default — so callers always pass a
 * concrete range.
 *
 * @param {Function} select The `@wordpress/data` select function.
 * @return {string} A `period=…&compare=…` query string.
 */
export function getDefaultDateRange( select ) {
	const wcAdminSettings =
		select( SETTINGS_STORE_NAME ).getSetting( 'wc_admin', 'wcAdminSettings' ) || {};

	return (
		wcAdminSettings.woocommerce_default_date_range ||
		select( OPTIONS_STORE_NAME ).getOption( 'woocommerce_default_date_range' ) ||
		DEFAULT_DATE_RANGE
	);
}
