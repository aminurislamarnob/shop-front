/* global storeSuiteAnalyticsConfig */
export const storeSuiteConfig = {
	analyticsUrl:  '',
	dashboardPath: '',
	reportsPath:   '',
	assetsPath:    '',
	...( typeof storeSuiteAnalyticsConfig !== 'undefined' ? storeSuiteAnalyticsConfig : {} ),
};
