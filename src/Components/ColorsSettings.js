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
	const [ buttonText, setButtonText ] = useState( '' );
	const [ buttonBackground, setButtonBackground ] = useState( '' );
	const [ buttonBorder, setButtonBorder ] = useState( '' );
	const [ buttonHoverText, setButtonHoverText ] = useState( '' );
	const [ buttonHoverBackground, setButtonHoverBackground ] = useState( '' );
	const [ buttonHoverBorder, setButtonHoverBorder ] = useState( '' );
	const [ sidebarMenuText, setSidebarMenuText ] = useState( '' );
	const [ sidebarBackground, setSidebarBackground ] = useState( '' );
	const [ sidebarActiveText, setSidebarActiveText ] = useState( '' );
	const [ sidebarActiveBackground, setSidebarActiveBackground ] =
		useState( '' );
	const [ isLoading, setIsLoading ] = useState( false );
	const [ message, setMessage ] = useState( '' );
	const [ error, setError ] = useState( '' );

	useEffect( () => {
		setIsLoading( true );
		apiFetch( { path: '/storesuite/v1/settings' } )
			.then( ( response ) => {
				if ( response.storesuite_color_button_text != null )
					setButtonText(
						String( response.storesuite_color_button_text )
					);
				if ( response.storesuite_color_button_background != null )
					setButtonBackground(
						String( response.storesuite_color_button_background )
					);
				if ( response.storesuite_color_button_border != null )
					setButtonBorder(
						String( response.storesuite_color_button_border )
					);
				if ( response.storesuite_color_button_hover_text != null )
					setButtonHoverText(
						String( response.storesuite_color_button_hover_text )
					);
				if ( response.storesuite_color_button_hover_background != null )
					setButtonHoverBackground(
						String(
							response.storesuite_color_button_hover_background
						)
					);
				if ( response.storesuite_color_button_hover_border != null )
					setButtonHoverBorder(
						String( response.storesuite_color_button_hover_border )
					);
				if ( response.storesuite_color_sidebar_menu_text != null )
					setSidebarMenuText(
						String( response.storesuite_color_sidebar_menu_text )
					);
				if ( response.storesuite_color_sidebar_background != null )
					setSidebarBackground(
						String( response.storesuite_color_sidebar_background )
					);
				if ( response.storesuite_color_sidebar_active_text != null )
					setSidebarActiveText(
						String( response.storesuite_color_sidebar_active_text )
					);
				if (
					response.storesuite_color_sidebar_active_background != null
				)
					setSidebarActiveBackground(
						String(
							response.storesuite_color_sidebar_active_background
						)
					);
			} )
			.catch( ( err ) => setError( err.message ) )
			.finally( () => setIsLoading( false ) );
	}, [] );

	const handleSubmit = async ( e ) => {
		e.preventDefault();
		setIsLoading( true );
		try {
			const response = await apiFetch( {
				path: '/storesuite/v1/settings',
				method: 'POST',
				data: {
					storesuite_color_button_text: buttonText,
					storesuite_color_button_background: buttonBackground,
					storesuite_color_button_border: buttonBorder,
					storesuite_color_button_hover_text: buttonHoverText,
					storesuite_color_button_hover_background:
						buttonHoverBackground,
					storesuite_color_button_hover_border: buttonHoverBorder,
					storesuite_color_sidebar_menu_text: sidebarMenuText,
					storesuite_color_sidebar_background: sidebarBackground,
					storesuite_color_sidebar_active_text: sidebarActiveText,
					storesuite_color_sidebar_active_background:
						sidebarActiveBackground,
				},
			} );
			setButtonText( response.storesuite_color_button_text ?? '' );
			setButtonBackground(
				response.storesuite_color_button_background ?? ''
			);
			setButtonBorder( response.storesuite_color_button_border ?? '' );
			setButtonHoverText(
				response.storesuite_color_button_hover_text ?? ''
			);
			setButtonHoverBackground(
				response.storesuite_color_button_hover_background ?? ''
			);
			setButtonHoverBorder(
				response.storesuite_color_button_hover_border ?? ''
			);
			setSidebarMenuText(
				response.storesuite_color_sidebar_menu_text ?? ''
			);
			setSidebarBackground(
				response.storesuite_color_sidebar_background ?? ''
			);
			setSidebarActiveText(
				response.storesuite_color_sidebar_active_text ?? ''
			);
			setSidebarActiveBackground(
				response.storesuite_color_sidebar_active_background ?? ''
			);
			setMessage( __( 'Settings saved successfully!', 'storesuite' ) );
			setError( '' );
		} catch ( err ) {
			setError( err.message );
			setMessage( '' );
		} finally {
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
						<path d="M8 5a1.5 1.5 0 1 0 0-3 1.5 1.5 0 0 0 0 3zm4 3a1.5 1.5 0 1 0 0-3 1.5 1.5 0 0 0 0 3zM5.5 7a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0zm.5 6a1.5 1.5 0 1 0 0-3 1.5 1.5 0 0 0 0 3z" />
						<path d="M16 8c0 3.15-1.866 2.585-3.567 2.07C11.42 9.763 10.465 9.473 10 10c-.603.683-.48 1.519-.2 2.318C9.23 13.974 8.69 14.95 8 16a8 8 0 1 1 8-8zm-8 7c.611 0 .654-.171.655-.176.078-.146.124-.464.07-1.119-.014-.168-.037-.37-.061-.591-.052-.464-.112-1.005-.118-1.462-.01-.707.083-1.61.704-2.314.369-.417.845-.965 1.492-1.794.472-.552.37-1.42-.232-1.665-.577-.224-1.273-.407-1.95-.508C6.662 6.767 6 5.907 6 5a4 4 0 0 1 8 0c0 .907-.662 1.767-1.6 2.082-.677.101-1.373.284-1.95.508-.603.245-.704 1.113-.232 1.665.647.829 1.123 1.377 1.492 1.794.621.703.714 1.607.704 2.314-.006.457-.066.998-.118 1.462-.024.22-.047.423-.061.591-.054.655-.008.973.07 1.119.001.005.044.176.655.176z" />
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
			<form className="storesuite-colors-form" onSubmit={ handleSubmit }>
				<Card>
					<CardBody>
						<ColorControl
							label={ __( 'Button Text', 'storesuite' ) }
							value={ buttonText }
							onChange={ setButtonText }
						/>
						<ColorControl
							label={ __( 'Button Background', 'storesuite' ) }
							value={ buttonBackground }
							onChange={ setButtonBackground }
						/>
						<ColorControl
							label={ __( 'Button Border', 'storesuite' ) }
							value={ buttonBorder }
							onChange={ setButtonBorder }
						/>
						<ColorControl
							label={ __( 'Button Hover Text', 'storesuite' ) }
							value={ buttonHoverText }
							onChange={ setButtonHoverText }
						/>
						<ColorControl
							label={ __(
								'Button Hover Background',
								'storesuite'
							) }
							value={ buttonHoverBackground }
							onChange={ setButtonHoverBackground }
						/>
						<ColorControl
							label={ __( 'Button Hover Border', 'storesuite' ) }
							value={ buttonHoverBorder }
							onChange={ setButtonHoverBorder }
						/>
						<ColorControl
							label={ __(
								'Dashboard Sidebar Menu Text',
								'storesuite'
							) }
							value={ sidebarMenuText }
							onChange={ setSidebarMenuText }
						/>
						<ColorControl
							label={ __(
								'Dashboard Sidebar Background',
								'storesuite'
							) }
							value={ sidebarBackground }
							onChange={ setSidebarBackground }
						/>
						<ColorControl
							label={ __(
								'Dashboard Sidebar Active/Hover Menu Text',
								'storesuite'
							) }
							value={ sidebarActiveText }
							onChange={ setSidebarActiveText }
						/>
						<ColorControl
							label={ __(
								'Dashboard Sidebar Active Menu Background',
								'storesuite'
							) }
							value={ sidebarActiveBackground }
							onChange={ setSidebarActiveBackground }
						/>
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
