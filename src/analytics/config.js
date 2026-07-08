/* global storeSuiteAnalyticsConfig */
export const storeSuiteConfig = {
	analyticsUrl:     '',
	dashboardPath:    '',
	reportsPath:      '',
	assetsPath:       '',
	orderDetailsPath: '',
	...( typeof storeSuiteAnalyticsConfig !== 'undefined' ? storeSuiteAnalyticsConfig : {} ),
};
