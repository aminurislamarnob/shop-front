const path = require( 'path' );
const glob = require( 'glob' );
const defaultConfig = require( '@wordpress/scripts/config/webpack.config' );

// Discover every module's React entry point at `modules/<slug>/src/index.js`.
// Each is emitted to `assets/build/modules/<slug>/script.js`, parallel to
// `assets/build/admin/script.js`, so PHP can enqueue it predictably.
const moduleEntries = glob
	.sync( './modules/*/src/index.js' )
	.reduce( ( acc, entry ) => {
		const match = entry.match( /^\.\/modules\/([^/]+)\// );
		if ( ! match ) {
			return acc;
		}
		const slug = match[ 1 ];
		acc[ `modules/${ slug }/script` ] = path.resolve( __dirname, entry );
		return acc;
	}, {} );

module.exports = {
	...defaultConfig,
	entry: {
		'admin/script':    './src/admin.js',
		'analytics/index': './src/analytics/index.js',
		'dashboard/index': './src/dashboard/index.js',
		...moduleEntries,
	},
	output: {
		...defaultConfig.output,
		path: path.resolve( __dirname, 'assets/build' ),
		filename: '[name].js',
		chunkFilename: ( pathData ) => {
			const name = pathData.chunk?.name || '';
			const dir  = name.startsWith( 'ss-dashboard' ) ? 'dashboard' : 'analytics';
			return `${ dir }/chunks/[name].js`;
		},
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
			'dashboard': path.resolve( __dirname, 'src/dashboard' ),
		},
	},
};
