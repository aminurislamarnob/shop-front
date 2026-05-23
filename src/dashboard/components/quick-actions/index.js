import { __ } from '@wordpress/i18n';
import {
	ArchiveBoxIcon,
	CubeIcon,
	ClipboardDocumentListIcon,
	DocumentPlusIcon,
	TagIcon,
	ChartBarIcon,
	TicketIcon,
	UserCircleIcon,
} from '@heroicons/react/24/outline';

import { storeSuiteDashboard } from '../../config';

function getActions() {
	const base = storeSuiteDashboard.dashboardUrl || '';
	const adminUrl = ( window.wcSettings?.adminUrl || '' ).replace( /\/$/, '' );

	return [
		{
			label: __( 'All Products', 'storesuite' ),
			icon: ArchiveBoxIcon,
			color: 'purple',
			href: base + '/products/',
		},
		{
			label: __( 'Add Product', 'storesuite' ),
			icon: CubeIcon,
			color: 'green',
			href: base + '/add-new-product/',
		},
		{
			label: __( 'View Orders', 'storesuite' ),
			icon: ClipboardDocumentListIcon,
			color: 'teal',
			href: base + '/orders/',
		},
		{
			label: __( 'Create Order', 'storesuite' ),
			icon: DocumentPlusIcon,
			color: 'blue',
			href: base + '/add-new-order/',
		},
		{
			label: __( 'Coupons', 'storesuite' ),
			icon: TicketIcon,
			color: 'orange',
			href: base + '/coupons/',
		},
		{
			label: __( 'Create Coupon', 'storesuite' ),
			icon: TagIcon,
			color: 'pink',
			href: base + '/add-new-coupon/',
		},
		{
			label: __( 'View Reports', 'storesuite' ),
			icon: ChartBarIcon,
			color: 'amber',
			href: storeSuiteDashboard.analyticsUrl || '#',
		},
		{
			label: __( 'Account', 'storesuite' ),
			icon: UserCircleIcon,
			color: 'gray',
			href: base + '/edit-account-details/',
		},
	];
}

export default function QuickActions() {
	const actions = getActions();

	return (
		<div className="storesuite-quick-actions">
			<h3 className="storesuite-quick-actions__title">
				{ __( 'Quick actions', 'storesuite' ) }
			</h3>
			<div className="storesuite-quick-actions__grid">
				{ actions.map( ( action ) => {
					const Icon = action.icon;
					return (
						<a
							key={ action.label }
							href={ action.href }
							className={ `storesuite-quick-action-item storesuite-quick-action-item--${ action.color }` }
						>
							<span className="storesuite-quick-action-item__icon-wrap">
								<Icon className="storesuite-quick-action-item__icon" />
							</span>
							<span className="storesuite-quick-action-item__label">
								{ action.label }
							</span>
						</a>
					);
				} ) }
			</div>
		</div>
	);
}
