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

// Defaults match :root CSS variables in Main::add_storesuite_css_variables()
const COLOR_FIELDS = [
	{
		key: 'buttonText',
		apiKey: 'storesuite_color_button_text',
		label: __( 'Button Text', 'storesuite' ),
		defaultValue: '#ffffff',
	},
	{
		key: 'buttonBackground',
		apiKey: 'storesuite_color_button_background',
		label: __( 'Button Background', 'storesuite' ),
		defaultValue: '#2d5bdb',
	},
	{
		key: 'buttonHoverText',
		apiKey: 'storesuite_color_button_hover_text',
		label: __( 'Button Hover Text', 'storesuite' ),
		defaultValue: '#ffffff',
	},
	{
		key: 'buttonHoverBackground',
		apiKey: 'storesuite_color_button_hover_background',
		label: __( 'Button Hover Background', 'storesuite' ),
		defaultValue: '#213fd4',
	},
	{
		key: 'textColor',
		apiKey: 'storesuite_text_color',
		label: __( 'Normal Text Color', 'storesuite' ),
		defaultValue: '#475569',
	},
	{
		key: 'titleTextColor',
		apiKey: 'storesuite_title_text_color',
		label: __( 'Title Text Color', 'storesuite' ),
		defaultValue: '#334155',
	},
	{
		key: 'liteTextColor',
		apiKey: 'storesuite_lite_text_color',
		label: __( 'Lite Text Color', 'storesuite' ),
		defaultValue: '#828282',
	},
	{
		key: 'iconColor',
		apiKey: 'storesuite_icon_color',
		label: __( 'Icon Color', 'storesuite' ),
		defaultValue: '#94a3b8',
	},
	{
		key: 'sidebarMenuText',
		apiKey: 'storesuite_color_sidebar_menu_text',
		label: __( 'Dashboard Sidebar Menu Text', 'storesuite' ),
		defaultValue: '#334155',
	},
	{
		key: 'sidebarBackground',
		apiKey: 'storesuite_color_sidebar_background',
		label: __( 'Dashboard Sidebar Background', 'storesuite' ),
		defaultValue: '#ffffff',
	},
	{
		key: 'sidebarActiveText',
		apiKey: 'storesuite_color_sidebar_active_text',
		label: __( 'Dashboard Sidebar Active/Hover Menu Text', 'storesuite' ),
		defaultValue: '#213fd4',
	},
	{
		key: 'sidebarActiveBackground',
		apiKey: 'storesuite_color_sidebar_active_background',
		label: __( 'Dashboard Sidebar Active Menu Background', 'storesuite' ),
		defaultValue: '#213fd4',
	},
	{
		key: 'borderColor',
		apiKey: 'storesuite_color_border',
		label: __( 'Border Color', 'storesuite' ),
		defaultValue: '#e2e8f0',
	},
	{
		key: 'liteBgColor',
		apiKey: 'storesuite_color_lite_bg',
		label: __( 'Lite Background Color', 'storesuite' ),
		defaultValue: '#f7f7f7',
	},
];

const ColorControl = ( {
	label,
	value,
	defaultValue = '#ffffff',
	onChange,
} ) => {
	const displayColor = value || defaultValue;
	return (
		<BaseControl label={ label } className="storesuite-color-control">
			<Dropdown
				contentClassName="storesuite-color-picker-dropdown"
				renderContent={ () => (
					<div className="storesuite-color-picker-popover">
						<ColorPicker
							color={ displayColor }
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
						<ColorIndicator colorValue={ displayColor } />
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
};

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

				// Color fields (use default when API returns empty/undefined).
				const colorsData = {};
				COLOR_FIELDS.forEach( ( { key, apiKey, defaultValue } ) => {
					const value = response[ apiKey ];
					colorsData[ key ] =
						value !== undefined && value !== ''
							? String( value )
							: defaultValue ?? '';
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

			// Color fields (use default when API returns empty/undefined).
			const colorsData = {};
			COLOR_FIELDS.forEach( ( { key, apiKey, defaultValue } ) => {
				const value = response[ apiKey ];
				colorsData[ key ] =
					value !== undefined && value !== ''
						? String( value )
						: defaultValue ?? '';
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
						className="bi bi-palette"
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
						<div className="storesuite-colors-form-wrapper">
							{ COLOR_FIELDS.map(
								( { key, label, defaultValue } ) => (
									<div
										key={ key }
										className="storesuite-settings-group"
									>
										<ColorControl
											label={ label }
											value={ colors[ key ] }
											defaultValue={ defaultValue }
											onChange={ ( value ) =>
												setColors( ( prev ) => ( {
													...prev,
													[ key ]: value,
												} ) )
											}
										/>
									</div>
								)
							) }
						</div>
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
