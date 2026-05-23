import { find } from 'lodash';

export default function getSelectedChart( chartName, charts = [] ) {
	const chart = find( charts, { key: chartName } );
	if ( chart ) {
		return chart;
	}
	return charts[ 0 ];
}
