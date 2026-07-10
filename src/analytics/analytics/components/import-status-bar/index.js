/**
 * "Data status" bar shown in the analytics report header — a frontend port of
 * wc-admin's ImportStatusBar. It surfaces when analytics data was last
 * processed, when the next scheduled batch import runs, and lets the user
 * trigger an import on demand.
 *
 * Gated on the same two conditions wc-admin uses: the `analytics-scheduled-import`
 * feature (which registers the /wc-analytics/imports/* routes) and scheduled
 * import mode being enabled. Both are resolved server-side and inlined via
 * `storeSuiteAnalyticsSettings.scheduledImport` because `window.wcAdminFeatures`
 * is not printed on the frontend.
 */
import { __ } from '@wordpress/i18n';
import apiFetch from '@wordpress/api-fetch';
import { Button, Spinner } from '@wordpress/components';
import { dateI18n } from '@wordpress/date';
import { useCallback, useEffect, useRef, useState } from '@wordpress/element';
import { useDispatch } from '@wordpress/data';

import { getAdminSetting } from '../../../utils/admin-settings';

// Poll interval (ms) while an import is in progress or imminently due.
const POLL_INTERVAL = 5000;

/**
 * Fetches import status, exposes a manual trigger, and polls for progress.
 */
function useImportStatus() {
	const [ status, setStatus ]                       = useState( null );
	const [ isLoading, setIsLoading ]                 = useState( true );
	const [ isTriggeringImport, setIsTriggeringImport ] = useState( false );
	const intervalRef = useRef( null );

	const fetchStatus = useCallback( async () => {
		try {
			const response = await apiFetch( {
				path:   '/wc-analytics/imports/status',
				method: 'GET',
			} );
			setStatus( response );
		} catch ( e ) {
			// Endpoint may be unavailable (feature disabled) — leave status null.
		} finally {
			setIsLoading( false );
		}
	}, [] );

	const triggerImport = useCallback( async () => {
		setIsTriggeringImport( true );
		try {
			await apiFetch( {
				path:   '/wc-analytics/imports/trigger',
				method: 'POST',
			} );
			await fetchStatus();
		} catch ( e ) {
			throw e instanceof Error
				? e
				: new Error( __( 'Failed to trigger import', 'storesuite' ) );
		} finally {
			setIsTriggeringImport( false );
		}
	}, [ fetchStatus ] );

	useEffect( () => {
		fetchStatus();
	}, [ fetchStatus ] );

	// While an import is running (or due within a minute), poll so the bar
	// reflects completion without a page reload.
	useEffect( () => {
		if ( status?.import_in_progress_or_due ) {
			intervalRef.current = window.setInterval( fetchStatus, POLL_INTERVAL );
		} else if ( intervalRef.current ) {
			clearInterval( intervalRef.current );
			intervalRef.current = null;
		}
		return () => {
			if ( intervalRef.current ) {
				clearInterval( intervalRef.current );
				intervalRef.current = null;
			}
		};
	}, [ status?.import_in_progress_or_due, fetchStatus ] );

	return { status, isLoading, triggerImport, isTriggeringImport };
}

/**
 * Format a datetime string (site timezone) or fall back to "Never".
 *
 * @param {string|null} value  Datetime string.
 * @param {string}      format PHP date format.
 * @return {string} Localized date or "Never".
 */
function formatDate( value, format ) {
	if ( ! value ) {
		return __( 'Never', 'storesuite' );
	}
	return dateI18n( format, value, undefined );
}

export default function ImportStatusBar() {
	const { status, isLoading, triggerImport, isTriggeringImport } = useImportStatus();
	const { createNotice } = useDispatch( 'core/notices' );

	const scheduledImport = getAdminSetting( 'scheduledImport', {} );

	// Match wc-admin: only render when the feature is enabled and the store is
	// in scheduled (not immediate) import mode.
	if ( ! scheduledImport.enabled || 'yes' !== scheduledImport.mode ) {
		return null;
	}

	const importBusy = status?.import_in_progress_or_due || isTriggeringImport;

	const onTrigger = async () => {
		try {
			await triggerImport();
			createNotice(
				'success',
				__(
					'Analytics import has started. Your store data will be updated soon.',
					'storesuite'
				),
				{ type: 'snackbar', isDismissible: true }
			);
		} catch ( e ) {
			createNotice(
				'error',
				e instanceof Error
					? e.message
					: __( 'Failed to trigger analytics update.', 'storesuite' ),
				{ type: 'snackbar', isDismissible: true }
			);
		}
	};

	return (
		<div className="woocommerce-analytics-import-status-bar-wrapper">
			<div className="woocommerce-analytics-import-status-bar-wrapper__label">
				{ __( 'Data status:', 'storesuite' ) }
			</div>
			<div
				className="woocommerce-analytics-import-status-bar"
				role="status"
				aria-live="polite"
				aria-atomic="true"
				aria-busy={ isLoading || isTriggeringImport }
			>
				<div className="woocommerce-analytics-import-status-bar__content">
					<span className="woocommerce-analytics-import-status-bar__item">
						<span className="woocommerce-analytics-import-status-bar__label">
							{ __( 'Last updated', 'storesuite' ) }
						</span>
						<span className="woocommerce-analytics-import-status-bar__value">
							{ isLoading ? (
								<Spinner />
							) : (
								formatDate( status?.last_processed_date || null, 'M j H:i' )
							) }
						</span>
					</span>
					<span className="woocommerce-analytics-import-status-bar__item">
						<span className="woocommerce-analytics-import-status-bar__label">
							{ __( 'Next update', 'storesuite' ) }
						</span>
						<span className="woocommerce-analytics-import-status-bar__value">
							{ isLoading ? (
								<Spinner />
							) : (
								formatDate(
									status?.next_scheduled || null,
									// translators: PHP date format, "at" is literal.
									__( 'M j \\a\\t H:i', 'storesuite' )
								)
							) }
						</span>
					</span>
					<Button
						variant="tertiary"
						className="woocommerce-analytics-import-status-bar__trigger"
						onClick={ onTrigger }
						disabled={ isLoading || importBusy }
						aria-disabled={ isLoading || importBusy }
						aria-busy={ importBusy }
						aria-label={
							importBusy
								? __( 'Analytics data import in progress', 'storesuite' )
								: __( 'Manually trigger analytics data import', 'storesuite' )
						}
					>
						{ importBusy ? (
							<Spinner />
						) : (
							__( 'Update now', 'storesuite' )
						) }
					</Button>
				</div>
			</div>
		</div>
	);
}
