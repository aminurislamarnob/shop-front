/* global storeSuiteDashboardConfig */
export const storeSuiteDashboard = {
	dashboardUrl:  '',
	dashboardPath: '',
	analyticsUrl:  '',
	reportsPath:   '',
	assetsPath:    '',
	canViewOrders: true,
	...( typeof storeSuiteDashboardConfig !== 'undefined' ? storeSuiteDashboardConfig : {} ),
};
