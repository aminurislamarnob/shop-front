import React from 'react';
import { TabPanel } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import './TabPanelStyles.scss';
import GeneralSettings from './GeneralSettings';
import PaginationSettings from './PaginationSettings';

const TabPanelWrapper = () => {
	return (
		<div className="wrap">
			<h1>{__('StoreSuite Settings', 'my-text-domain')}</h1>
			<TabPanel
				className="my-tab-panel"
				activeClass="is-active"
				tabs={[
					{
						name: 'general',
						title: __('General', 'my-text-domain'),
						className: 'tab-one',
					},
					{
						name: 'pagination',
						title: __('Pagination', 'my-text-domain'),
						className: 'tab-three',
					},
				]}
			>
				{(tab) => (
					<div className="components-tab-panel__content">
						{tab.name === 'general' && (
							<div>
								<GeneralSettings />
							</div>
						)}
						{tab.name === 'pagination' && (
							<div>
								<PaginationSettings />
							</div>
						)}
					</div>
				)}
			</TabPanel>
		</div>
	);
};

export default TabPanelWrapper;
