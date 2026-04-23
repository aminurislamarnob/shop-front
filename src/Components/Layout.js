/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { Button, Spinner, Card, CardBody, SnackbarList } from '@wordpress/components';
import { Link, Outlet, useLocation } from 'react-router-dom';
import { useSelect, useDispatch } from '@wordpress/data';
import { store as noticesStore } from '@wordpress/notices';

/**
 * Internal dependencies
 */
import { useSettings } from '../context/SettingsContext';
import {
	CodeBracketSquareIcon,
	GearIcon,
	PaletteIcon,
	Squares2X2Icon,
} from './icons';
import SettingsHeader from './SettingsHeader';

const Layout = () => {
	const { isLoading } = useSettings();
	const location = useLocation();
	const isActive = ( path ) => location.pathname === path;

	const notices = useSelect( ( select ) =>
		select( noticesStore ).getNotices()
	);
	const { removeNotice } = useDispatch( noticesStore );

	const snackbarNotices = notices.filter(
		( notice ) => notice.type === 'snackbar'
	);

	return (
		<div className="storesuite-admin-app">
			<SettingsHeader
				icon={ Squares2X2Icon }
				title={ __( 'StoreSuite', 'storesuite' ) }
				subTitle={ __(
					'Configure your frontend dashboard pages, appearance, and pagination.',
					'storesuite'
				) }
				actions={
					<>
						<Button
							variant="secondary"
							href="https://pluginizelab.com/docs/storesuite/"
							target="_blank"
							rel="noreferrer"
						>
							{ __( 'Documentation', 'storesuite' ) }
						</Button>
						<Button
							variant="primary"
							href="https://buymeacoffee.com/aiarnob"
							target="_blank"
							rel="noreferrer"
						>
							{ __( 'Support Me', 'storesuite' ) }
						</Button>
					</>
				}
			/>

			<main className="storesuite-main-content storesuite-setting-wrapper">
				{ isLoading ? (
					<div className="storesuite-content-body">
						<div className="storesuite-hash-nav">
							<div
								className="storesuite-skeleton-tab"
								style={ { width: '120px' } }
							></div>
							<div
								className="storesuite-skeleton-tab"
								style={ { width: '116px' } }
							></div>
							<div
								className="storesuite-skeleton-tab"
								style={ { width: '110px' } }
							></div>
							<div
								className="storesuite-skeleton-tab"
								style={ { width: '100px' } }
							></div>
						</div>
						<div className="storesuite-section">
							<Card>
								<CardBody className="storesuite-form-section-body">
									<div className="storesuite-loading">
										<Spinner />
									</div>
								</CardBody>
							</Card>
						</div>
					</div>
				) : (
					<div className="storesuite-content-body">
						<div className="storesuite-hash-nav">
							<Link
								to="/"
								className={ isActive( '/' ) ? 'is-active' : '' }
							>
								<GearIcon />
								{ __( 'General', 'storesuite' ) }
							</Link>
							<Link
								to="/dashboard-settings"
								className={
									isActive( '/dashboard-settings' )
										? 'is-active'
										: ''
								}
							>
								<Squares2X2Icon />
								{ __( 'Dashboard', 'storesuite' ) }
							</Link>
							<Link
								to="/appearance-settings"
								className={
									isActive( '/appearance-settings' )
										? 'is-active'
										: ''
								}
							>
								<PaletteIcon />
								{ __( 'Appearance', 'storesuite' ) }
							</Link>
							<Link
								to="/pagination-settings"
								className={
									isActive( '/pagination-settings' )
										? 'is-active'
										: ''
								}
							>
								<CodeBracketSquareIcon />
								{ __( 'Pagination', 'storesuite' ) }
							</Link>
						</div>

						<Outlet />
					</div>
				) }
			</main>

			<SnackbarList
				notices={ snackbarNotices }
				className="components-editor-notices__snackbar"
				onRemove={ removeNotice }
			/>
		</div>
	);
};

export default Layout;
