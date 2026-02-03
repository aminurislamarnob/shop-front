import { __ } from '@wordpress/i18n';
import { useState, useEffect } from 'react';
import {
	BaseControl,
	Button,
	Card,
	CardBody,
	ColorIndicator,
	ColorPicker,
	Dropdown,
	Notice,
	Spinner,
} from '@wordpress/components';
import apiFetch from '@wordpress/api-fetch';

const COLOR_FIELDS = [
	{
		key: 'buttonText',
		apiKey: 'storesuite_color_button_text',
		label: __( 'Button Text', 'storesuite' ),
	},
	{
		key: 'buttonBackground',
		apiKey: 'storesuite_color_button_background',
		label: __( 'Button Background', 'storesuite' ),
	},
	{
		key: 'buttonBorder',
		apiKey: 'storesuite_color_button_border',
		label: __( 'Button Border', 'storesuite' ),
	},
	{
		key: 'buttonHoverText',
		apiKey: 'storesuite_color_button_hover_text',
		label: __( 'Button Hover Text', 'storesuite' ),
	},
	{
		key: 'buttonHoverBackground',
		apiKey: 'storesuite_color_button_hover_background',
		label: __( 'Button Hover Background', 'storesuite' ),
	},
	{
		key: 'buttonHoverBorder',
		apiKey: 'storesuite_color_button_hover_border',
		label: __( 'Button Hover Border', 'storesuite' ),
	},
	{
		key: 'sidebarMenuText',
		apiKey: 'storesuite_color_sidebar_menu_text',
		label: __( 'Dashboard Sidebar Menu Text', 'storesuite' ),
	},
	{
		key: 'sidebarBackground',
		apiKey: 'storesuite_color_sidebar_background',
		label: __( 'Dashboard Sidebar Background', 'storesuite' ),
	},
	{
		key: 'sidebarActiveText',
		apiKey: 'storesuite_color_sidebar_active_text',
		label: __( 'Dashboard Sidebar Active/Hover Menu Text', 'storesuite' ),
	},
	{
		key: 'sidebarActiveBackground',
		apiKey: 'storesuite_color_sidebar_active_background',
		label: __( 'Dashboard Sidebar Active Menu Background', 'storesuite' ),
	},
];

const ColorControl = ( { label, value, onChange } ) => (
	<BaseControl label={ label } className="storesuite-color-control">
		<Dropdown
			contentClassName="storesuite-color-picker-dropdown"
			renderContent={ () => (
				<div className="storesuite-color-picker-popover">
					<ColorPicker
						color={ value || '#ffffff' }
						onChange={ onChange }
						enableAlpha={ false }
					/>
				</div>
			) }
			renderToggle={ ( { isOpen, onToggle } ) => (
				<Button
					className="storesuite-color-toggle"
					onClick={ onToggle }
					aria-expanded={ isOpen }
				>
					<ColorIndicator colorValue={ value || '#ffffff' } />
					<svg
						width="24"
						height="24"
						viewBox="0 0 24 24"
						fill="currentColor"
						aria-hidden
					>
						<path d="M7 10l5 5 5-5z" />
					</svg>
				</Button>
			) }
		/>
	</BaseControl>
);

const ColorsSettings = () => {
	const [ colors, setColors ] = useState( {} );
	const [ isLoading, setIsLoading ] = useState( false );
	const [ message, setMessage ] = useState( '' );
	const [ error, setError ] = useState( '' );

	// Fetch plugin settings.
	useEffect( () => {
		setIsLoading( true );
		const fetchSettings = async () => {
			try {
				const response = await apiFetch( {
					path: '/storesuite/v1/settings',
				} );

				// Color fields.
				const colorsData = {};
				COLOR_FIELDS.forEach( ( { key, apiKey } ) => {
					colorsData[ key ] =
						response[ apiKey ] !== undefined
							? String( response[ apiKey ] )
							: '';
				} );
				setColors( colorsData );

				setError( null );
				setIsLoading( false );
			} catch ( err ) {
				setError( err.message );
				setIsLoading( false );
			}
		};

		fetchSettings();
	}, [] );

	// Handle submit to save color settings.
	const handleSubmit = async ( event ) => {
		event.preventDefault();
		setIsLoading( true );
		try {
			const data = {};
			COLOR_FIELDS.forEach( ( { key, apiKey } ) => {
				data[ apiKey ] = colors[ key ] ?? '';
			} );

			const response = await apiFetch( {
				path: '/storesuite/v1/settings',
				method: 'POST',
				data,
			} );

			// Color fields.
			const colorsData = {};
			COLOR_FIELDS.forEach( ( { key, apiKey } ) => {
				colorsData[ key ] =
					response[ apiKey ] !== undefined
						? String( response[ apiKey ] )
						: '';
			} );
			setColors( colorsData );

			setMessage( __( 'Settings saved successfully!', 'storesuite' ) );
			setError( '' );
			setIsLoading( false );
		} catch ( error ) {
			setError( error.message );
			setMessage( '' );
			setIsLoading( false );
		}
	};

	return (
		<div>
			<div className="settings-header">
				<div className="settings-header-icon">
					<svg
						xmlns="http://www.w3.org/2000/svg"
						width="16"
						height="16"
						fill="currentColor"
						class="bi bi-palette"
						viewBox="0 0 16 16"
					>
						<path d="M8 5a1.5 1.5 0 1 0 0-3 1.5 1.5 0 0 0 0 3m4 3a1.5 1.5 0 1 0 0-3 1.5 1.5 0 0 0 0 3M5.5 7a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0m.5 6a1.5 1.5 0 1 0 0-3 1.5 1.5 0 0 0 0 3" />
						<path d="M16 8c0 3.15-1.866 2.585-3.567 2.07C11.42 9.763 10.465 9.473 10 10c-.603.683-.475 1.819-.351 2.92C9.826 14.495 9.996 16 8 16a8 8 0 1 1 8-8m-8 7c.611 0 .654-.171.655-.176.078-.146.124-.464.07-1.119-.014-.168-.037-.37-.061-.591-.052-.464-.112-1.005-.118-1.462-.01-.707.083-1.61.704-2.314.369-.417.845-.578 1.272-.618.404-.038.812.026 1.16.104.343.077.702.186 1.025.284l.028.008c.346.105.658.199.953.266.653.148.904.083.991.024C14.717 9.38 15 9.161 15 8a7 7 0 1 0-7 7" />
					</svg>
				</div>
				<h2>{ __( 'Colors Settings', 'storesuite' ) }</h2>
			</div>
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
			<form onSubmit={ handleSubmit } className="storesuite-colors-form">
				<Card>
					<CardBody>
						{ COLOR_FIELDS.map( ( { key, label } ) => (
							<div
								key={ key }
								className="storesuite-settings-group"
							>
								<ColorControl
									label={ label }
									value={ colors[ key ] }
									onChange={ ( value ) =>
										setColors( ( prev ) => ( {
											...prev,
											[ key ]: value,
										} ) )
									}
								/>
							</div>
						) ) }
						<Button
							variant="primary"
							type="submit"
							disabled={ isLoading }
						>
							{ isLoading && <Spinner /> }
							{ __( 'Save Changes', 'storesuite' ) }
						</Button>
					</CardBody>
				</Card>
			</form>
		</div>
	);
};

export default ColorsSettings;
