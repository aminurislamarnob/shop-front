import { __ } from '@wordpress/i18n';
import { useEffect, useState } from '@wordpress/element';
import { useSelect, useDispatch } from '@wordpress/data';
import apiFetch from '@wordpress/api-fetch';
import { Button, CheckboxControl, SelectControl, Card, CardBody, CardHeader } from '@wordpress/components';
import { OPTIONS_STORE_NAME } from '@woocommerce/data';
import { DateRangeFilterPicker } from '@woocommerce/components';
import { getDateParamsFromQuery, getCurrentDates, isoDateFormat } from '@woocommerce/date';
import { parse, stringify } from 'qs';
import { getAdminSetting } from '../../../utils/admin-settings';

const OPTION_EXCLUDED    = 'woocommerce_excluded_report_order_statuses';
const OPTION_ACTIONABLE  = 'woocommerce_actionable_order_statuses';
const OPTION_DATE_RANGE  = 'woocommerce_default_date_range';
const OPTION_DATE_TYPE   = 'woocommerce_date_type';

const DEFAULT_DATE_RANGE = 'period=month&compare=previous_year';

// WooCommerce's effective defaults when the options have never been saved
// (see WC's Reports DataStore and wc-admin's analytics settings config).
const DEFAULT_EXCLUDED   = [ 'pending', 'failed', 'cancelled' ];
const DEFAULT_ACTIONABLE = [ 'processing', 'on-hold' ];

const useAnalyticsOptions = () =>
	useSelect( ( select ) => {
		const { getOption, hasFinishedResolution } = select( OPTIONS_STORE_NAME );
		return {
			excluded:   getOption( OPTION_EXCLUDED ),
			actionable: getOption( OPTION_ACTIONABLE ),
			dateRange:  getOption( OPTION_DATE_RANGE ),
			dateType:   getOption( OPTION_DATE_TYPE ),
			isLoading:  ! (
				hasFinishedResolution( 'getOption', [ OPTION_EXCLUDED ] ) &&
				hasFinishedResolution( 'getOption', [ OPTION_ACTIONABLE ] ) &&
				hasFinishedResolution( 'getOption', [ OPTION_DATE_RANGE ] ) &&
				hasFinishedResolution( 'getOption', [ OPTION_DATE_TYPE ] )
			),
		};
	} );

const StatusCheckboxGroup = ( { label, help, statuses, value, onChange } ) => (
	<fieldset className="storesuite-analytics-settings__field">
		<legend className="storesuite-analytics-settings__label">{ label }</legend>
		<p className="storesuite-analytics-settings__help">{ help }</p>
		<div className="storesuite-analytics-settings__statuses">
			{ Object.keys( statuses ).map( ( status ) => (
				<CheckboxControl
					key={ status }
					__nextHasNoMarginBottom
					label={ statuses[ status ] }
					checked={ value.includes( status ) }
					onChange={ ( checked ) =>
						onChange(
							checked
								? [ ...value, status ]
								: value.filter( ( s ) => s !== status )
						)
					}
				/>
			) ) }
		</div>
	</fieldset>
);

export default function SettingsReport() {
	const options = useAnalyticsOptions();
	const { createNotice } = useDispatch( 'core/notices' );

	const [ isSaving, setIsSaving ]     = useState( false );
	const [ excluded, setExcluded ]     = useState( null );
	const [ actionable, setActionable ] = useState( null );
	const [ dateRange, setDateRange ]   = useState( null );
	const [ dateType, setDateType ]     = useState( null );

	useEffect( () => {
		if ( ! options.isLoading ) {
			setExcluded( ( v ) => ( null === v ? ( options.excluded || DEFAULT_EXCLUDED ) : v ) );
			setActionable( ( v ) => ( null === v ? ( options.actionable || DEFAULT_ACTIONABLE ) : v ) );
			setDateRange( ( v ) => ( null === v ? ( options.dateRange || DEFAULT_DATE_RANGE ) : v ) );
			setDateType( ( v ) => ( null === v ? ( options.dateType || 'date_paid' ) : v ) );
		}
	}, [ options.isLoading ] );

	if ( options.isLoading || null === excluded ) {
		return null;
	}

	const statuses = {
		...getAdminSetting( 'orderStatuses', {} ),
		...getAdminSetting( 'unregisteredOrderStatuses', {} ),
	};

	const rangeQuery = parse( String( dateRange ).replace( /&amp;/g, '&' ) );
	const { period, compare, before, after } = getDateParamsFromQuery( rangeQuery, DEFAULT_DATE_RANGE );
	const { primary: primaryDate, secondary: secondaryDate } = getCurrentDates( rangeQuery, DEFAULT_DATE_RANGE );

	const save = async () => {
		setIsSaving( true );
		try {
			await apiFetch( {
				path:   '/wc-analytics/settings/wc_admin/batch',
				method: 'POST',
				data: {
					update: [
						{ id: OPTION_EXCLUDED,   value: excluded },
						{ id: OPTION_ACTIONABLE, value: actionable },
						{ id: OPTION_DATE_RANGE, value: dateRange },
						{ id: OPTION_DATE_TYPE,  value: dateType },
					],
				},
			} );
			createNotice(
				'success',
				__( 'Your settings have been successfully saved.', 'storesuite' ),
				{ type: 'snackbar' }
			);
		} catch ( error ) {
			createNotice(
				'error',
				__( 'There was an error saving your settings. Please try again.', 'storesuite' ),
				{ type: 'snackbar' }
			);
		}
		setIsSaving( false );
	};

	return (
		<Card className="storesuite-analytics-settings">
			<CardHeader>
				<h2>{ __( 'Analytics Settings', 'storesuite' ) }</h2>
			</CardHeader>
			<CardBody>
				<StatusCheckboxGroup
					label={ __( 'Excluded statuses', 'storesuite' ) }
					help={ __( 'Orders with these statuses are excluded from the totals in your reports.', 'storesuite' ) }
					statuses={ statuses }
					value={ excluded }
					onChange={ setExcluded }
				/>

				<StatusCheckboxGroup
					label={ __( 'Actionable statuses', 'storesuite' ) }
					help={ __( 'Orders with these statuses require action on behalf of the store admin.', 'storesuite' ) }
					statuses={ statuses }
					value={ actionable }
					onChange={ setActionable }
				/>

				<fieldset className="storesuite-analytics-settings__field">
					<legend className="storesuite-analytics-settings__label">
						{ __( 'Default date range', 'storesuite' ) }
					</legend>
					<p className="storesuite-analytics-settings__help">
						{ __( 'Select a default date range. When no range is selected, reports will be viewed by the default date range.', 'storesuite' ) }
					</p>
					<DateRangeFilterPicker
						query={ rangeQuery }
						onRangeSelect={ ( range ) => setDateRange( stringify( range ) ) }
						dateQuery={ { period, compare, before, after, primaryDate, secondaryDate } }
						isoDateFormat={ isoDateFormat }
					/>
				</fieldset>

				<fieldset className="storesuite-analytics-settings__field">
					<legend className="storesuite-analytics-settings__label">
						{ __( 'Date type', 'storesuite' ) }
					</legend>
					<SelectControl
						__next40pxDefaultSize
						__nextHasNoMarginBottom
						help={ __( 'Database date field considered for Revenue and Orders reports.', 'storesuite' ) }
						options={ [
							{ value: 'date_created',   label: __( 'Date created', 'storesuite' ) },
							{ value: 'date_paid',      label: __( 'Date paid', 'storesuite' ) },
							{ value: 'date_completed', label: __( 'Date completed', 'storesuite' ) },
						] }
						value={ dateType }
						onChange={ setDateType }
					/>
				</fieldset>

				<div className="storesuite-analytics-settings__actions">
					<Button variant="primary" isBusy={ isSaving } disabled={ isSaving } onClick={ save }>
						{ __( 'Save settings', 'storesuite' ) }
					</Button>
				</div>
			</CardBody>
		</Card>
	);
}
