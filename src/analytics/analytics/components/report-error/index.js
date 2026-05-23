import { __ } from '@wordpress/i18n';
import PropTypes from 'prop-types';
import { EmptyContent } from '@woocommerce/components';

function ReportError( { className } ) {
	return (
		<EmptyContent
			className={ className }
			title={ __( 'There was an error getting your stats. Please try again.', 'storesuite' ) }
			actionLabel={ __( 'Reload', 'storesuite' ) }
			actionCallback={ () => window.location.reload() }
		/>
	);
}

ReportError.propTypes = {
	className: PropTypes.string,
};

export default ReportError;
