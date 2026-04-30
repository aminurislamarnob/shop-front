const path = require( 'path' );
const defaultConfig = require( '@wordpress/scripts/config/webpack.config' );

module.exports = {
	...defaultConfig,
	entry: {
		'admin/script':    './src/admin.js',
		'analytics/index': './src/analytics/index.js',
	},
	output: {
		...defaultConfig.output,
		path: path.resolve( __dirname, 'assets/build' ),
		filename: '[name].js',
		chunkFilename: 'analytics/chunks/[name].js',
	},
	externals: {
		...defaultConfig.externals,
		'@woocommerce/components':            [ 'window', 'wc', 'components' ],
		'@woocommerce/data':                  [ 'window', 'wc', 'data' ],
		'@woocommerce/date':                  [ 'window', 'wc', 'date' ],
		'@woocommerce/currency':              [ 'window', 'wc', 'currency' ],
		'@woocommerce/navigation':            [ 'window', 'wc', 'navigation' ],
		'@woocommerce/number':                [ 'window', 'wc', 'number' ],
		'@woocommerce/tracks':                [ 'window', 'wc', 'tracks' ],
		'@woocommerce/customer-effort-score': [ 'window', 'wc', 'customerEffortScore' ],
		'@woocommerce/csv-export':            [ 'window', 'wc', 'csvExport' ],
		'@woocommerce/settings':              [ 'window', 'wc', 'wcSettings' ],
	},
	resolve: {
		...defaultConfig.resolve,
		alias: {
			...( defaultConfig.resolve?.alias || {} ),
			'analytics': path.resolve( __dirname, 'src/analytics' ),
		},
	},
};
