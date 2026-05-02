/* global storeSuiteDashboardConfig */
export const storeSuiteDashboard = {
	dashboardUrl:  '',
	dashboardPath: '',
	analyticsUrl:  '',
	reportsPath:   '',
	assetsPath:    '',
	...( typeof storeSuiteDashboardConfig !== 'undefined' ? storeSuiteDashboardConfig : {} ),
};
