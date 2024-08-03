import { __ } from '@wordpress/i18n';
import { useState, useEffect } from 'react';
import { createRoot } from 'react-dom/client';
import { Button, Card, CardBody, Notice, TextControl, Spinner } from '@wordpress/components';
import apiFetch from '@wordpress/api-fetch';

const OptionsPage = () => {
	const [ productPerPage, setProductPerPage ] = useState( '' );
	const [ isLoading, setIsLoading ] = useState( false );
	const [ message, setMessage ] = useState( '' );
	const [ error, setError ] = useState( '' );

	useEffect(() => {
		setIsLoading( true );
        const fetchSettings = async () => {
            try {
                const response = await apiFetch({
                    path: '/msf-shop-front/v1/settings',
                });
                setProductPerPage(response.msf_product_per_page);
                setError(null); // Clear any previous errors
				setIsLoading( false );
            } catch (err) {
                setError( err.message );
				setIsLoading( false );
            }
        };

        fetchSettings();
    }, []);

	const handleSubmit = async ( event ) => {
		event.preventDefault();
		setIsLoading( true );
		try {
			const { msf_product_per_page } = await apiFetch( {
				path: '/msf-shop-front/v1/settings',
				method: 'POST',
				data: {
					msf_product_per_page: productPerPage,
				},
			} );
			setProductPerPage( msf_product_per_page );
			setMessage(
				__( 'Settings saved successfully!', 'shop-front' )
			);
			setError( '' );
			setIsLoading( false );
		} catch (error) {
			setError( error.message );
			setMessage( '' );
			setIsLoading( false );
		}
		
	};

	return (
		<div>
			{/* <h1>{ __( 'Options Page', 'shop-front' ) }</h1> */}
			{ message && (
				<Notice
					className="w-full mb-4"
					status="success"
					isDismissible
					onDismiss={ () => setMessage( '' ) }
				>
					{ message }
				</Notice>
			) }
			{ error && (
				<Notice
					className="w-full mb-4"
					status="error"
					isDismissible
					onDismiss={ () => setError( '' ) }
				>
					{ error }
				</Notice>
			) }

			<form onSubmit={ handleSubmit }>
				<Card>
					<CardBody>
						<TextControl
							label={ __( 'Product per page', 'shop-front' ) }
							help={ __( 'Default: 10', 'shop-front' ) }
							value={ productPerPage }
							onChange={ setProductPerPage }
						/>
						<Button variant="primary" type="submit" disabled={ isLoading }>
							{ isLoading && <Spinner /> }
							{ __( 'Save', 'shop-front' ) }
						</Button>
					</CardBody>
				</Card>
			</form>
		</div>
	);
};

document.addEventListener( 'DOMContentLoaded', () => {
const rootElement = document.getElementById( 'shop-front-settings' );
    if ( rootElement ) {
        createRoot( rootElement ).render( <OptionsPage /> );
    }
} );