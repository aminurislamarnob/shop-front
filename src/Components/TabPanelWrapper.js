import React from 'react';
import { TabPanel } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import './TabPanelStyles.scss';
import GeneralSettings from './GeneralSettings';
import ProductSettings from './ProductSettings';
import OrderSettings from './OrderSettings';

const TabPanelWrapper = () => {
	return (
		<div className="wrap">
			<h1>
				{ __( 'ShopFront Settings', 'my-text-domain' ) }
			</h1>
			<TabPanel
				className="my-tab-panel"
				activeClass="is-active"
				tabs={ [
					{
						name: 'general',
						title: __( 'General', 'my-text-domain' ),
						className: 'tab-one',
					},
					{
						name: 'product',
						title: __( 'Product', 'my-text-domain' ),
						className: 'tab-one',
					},
					{
						name: 'order',
						title: __( 'Order', 'my-text-domain' ),
						className: 'tab-two',
					},
				] }
			>
				{ ( tab ) => (
					<div className="components-tab-panel__content">
						{ tab.name === 'general' && (
							<div>
								<GeneralSettings />
							</div>
						) }
						{ tab.name === 'product' && (
							<div>
								<ProductSettings />
							</div>
						) }
						{ tab.name === 'order' && (
							<div>
								<OrderSettings />
							</div>
						) }
					</div>
				) }
			</TabPanel>
		</div>
	);
};

export default TabPanelWrapper;