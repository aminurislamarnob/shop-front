/**
 * WordPress dependencies
 */
import { useState, useEffect } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
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

/**
 * Internal dependencies
 */
import { PaletteIcon } from './icons';

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
	colorKey,
	defaultValue = '#ffffff',
	onChange,
} ) => {
	const displayColor = value || defaultValue;
	const controlId = `storesuite-color-${ colorKey }`;
	return (
		<BaseControl
			id={ controlId }
			label={ label }
			className="storesuite-color-control"
		>
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
		} catch ( submitError ) {
			setError( submitError.message );
			setMessage( '' );
			setIsLoading( false );
		}
	};

	return (
		<div>
			<div className="settings-header">
				<div className="settings-header-icon">
					<PaletteIcon />
				</div>
				<h2>{ __( 'Colors Settings', 'storesuite' ) }</h2>
			</div>
			{ message && (
				<Notice
					className="storesuite-notice"
					status="success"
					isDismissible
					onDismiss={ () => setMessage( '' ) }
				>
					{ message }
				</Notice>
			) }
			{ error && (
				<Notice
					className="storesuite-notice"
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
											colorKey={ key }
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
