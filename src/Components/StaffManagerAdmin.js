/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { Card, CardBody } from '@wordpress/components';
import { Link } from 'react-router-dom';

/**
 * Internal dependencies
 */
import { PuzzlePieceIcon } from './icons';

/**
 * Landing page for the dynamic "Staff" top-nav tab that Staff Manager injects
 * while the module is active. The tab is wired up by the module's
 * `get_admin_tabs()` PHP method; Layout merges it into the nav based on the
 * REST modules list. Keep this screen lightweight — the real CRUD belongs in
 * the frontend dashboard, not the admin settings app.
 */
const StaffManagerAdmin = () => (
	<div
		className="storesuite-section storesuite-section--narrow"
		id="storesuite-staff-manager-admin"
	>
		<Card className="storesuite-form-header-card">
			<CardBody className="storesuite-form-section-header">
				<h3 className="storesuite-section-title">
					{ __( 'Staff', 'storesuite' ) }
				</h3>
				<p className="storesuite-section-description">
					{ __(
						'Manage who has access to your storefront dashboard. This tab is added dynamically by the Staff Manager module and disappears when the module is deactivated.',
						'storesuite'
					) }
				</p>
			</CardBody>
		</Card>
		<Card>
			<CardBody className="storesuite-form-section-body">
				<div className="storesuite-staff-admin-intro">
					<PuzzlePieceIcon />
					<div>
						<h4>
							{ __(
								'Module-injected tab demo',
								'storesuite'
							) }
						</h4>
						<p>
							{ __(
								'This screen is rendered because Staff Manager is active and exposes a top-level tab through its `get_admin_tabs()` hook. Deactivate the module from the',
								'storesuite'
							) }{ ' ' }
							<Link to="/modules">
								{ __( 'Modules tab', 'storesuite' ) }
							</Link>{ ' ' }
							{ __(
								'and this entry will vanish from the navigation.',
								'storesuite'
							) }
						</p>
					</div>
				</div>
			</CardBody>
		</Card>
	</div>
);

export default StaffManagerAdmin;
