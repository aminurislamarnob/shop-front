import { __ } from '@wordpress/i18n';
import { createRoot } from 'react-dom/client';
import { Card, CardBody, TextControl } from '@wordpress/components';

const OptionsPage = () => {
	return (
		<div>
			<h1>{ __( 'Options Page', 'wop' ) }</h1>
			<Card>
				<CardBody>
					<TextControl
						label={ __( 'Custom Field', 'wop' ) }
						help={ __( 'This is a custom field.', 'wop' ) }
					/>
				</CardBody>
			</Card>
		</div>
	);
};

document.addEventListener( 'DOMContentLoaded', () => {
const rootElement = document.getElementById( 'shop-front-settings' );
    if ( rootElement ) {
        createRoot( rootElement ).render( <OptionsPage /> );
    }
} );