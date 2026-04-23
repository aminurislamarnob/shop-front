/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';

const MENU_ITEMS = 6;

/**
 * Live mini-dashboard preview driven by the current color state.
 *
 * @param {Object} props
 * @param {Object} props.colors  Flat map of color keys to hex strings.
 */
const ColorPreview = ( { colors } ) => {
	return (
		<div className="storesuite-color-preview">
			<p className="storesuite-preview-label">
				{ __( 'Preview', 'storesuite' ) }
			</p>
			<div className="storesuite-preview-wrap">
				{ /* Browser chrome */ }
				<div className="storesuite-preview-header">
					<div className="storesuite-preview-dots">
						{ [ 1, 2, 3 ].map( ( i ) => (
							<span
								key={ i }
								className={ `storesuite-preview-dot storesuite-preview-dot--${ i }` }
							/>
						) ) }
					</div>
				</div>

				{ /* Body */ }
				<div className="storesuite-preview-body">
					{ /* Sidebar */ }
					<div
						className="storesuite-preview-sidebar"
						style={ {
							backgroundColor: colors.sidebarBackground,
						} }
					>
						<div className="storesuite-preview-site-logo" />
						<div className="storesuite-preview-menu">
							{ Array.from(
								{ length: MENU_ITEMS },
								( _, i ) => (
									<div
										key={ i }
										className="storesuite-preview-menu-item"
										style={ {
											backgroundColor:
												i === 0
													? colors.sidebarActiveBackground
													: 'transparent',
										} }
									>
										<span
											className="storesuite-preview-menu-icon"
											style={ {
												backgroundColor:
													i === 0
														? colors.sidebarActiveText
														: colors.sidebarMenuText,
											} }
										/>
										<span
											className="storesuite-preview-menu-text"
											style={ {
												backgroundColor:
													i === 0
														? colors.sidebarActiveText
														: colors.sidebarMenuText,
											} }
										/>
									</div>
								)
							) }
						</div>
					</div>

					{ /* Content area */ }
					<div className="storesuite-preview-content">
						{ /* Row 1: stat cards + primary button */ }
						<div className="storesuite-preview-row">
							<div className="storesuite-preview-stat-cards">
								{ [ 1, 2, 3, 4 ].map( ( i ) => (
									<div
										key={ i }
										className="storesuite-preview-stat-card"
									/>
								) ) }
							</div>
							<div
								className="storesuite-preview-btn"
								style={ {
									backgroundColor: colors.buttonBackground,
									color: colors.buttonText,
								} }
							>
								{ __( 'Button', 'storesuite' ) }
							</div>
						</div>

						{ /* Row 2: chart card + hover button */ }
						<div className="storesuite-preview-chart-card">
							<div className="storesuite-preview-chart-lines">
								{ [ 1, 2, 3 ].map( ( i ) => (
									<span
										key={ i }
										className="storesuite-preview-chart-line"
									/>
								) ) }
							</div>
							<div
								className="storesuite-preview-btn"
								style={ {
									backgroundColor:
										colors.buttonHoverBackground,
									color: colors.buttonHoverText,
								} }
							>
								{ __( 'Button Hover', 'storesuite' ) }
							</div>
						</div>

						{ /* Row 3: placeholder lines + border button */ }
						<div className="storesuite-preview-row">
							<div className="storesuite-preview-text-lines">
								{ [ 1, 2 ].map( ( i ) => (
									<span
										key={ i }
										className="storesuite-preview-text-line"
									/>
								) ) }
							</div>
							<div
								className="storesuite-preview-border-btn"
								style={ { borderColor: colors.borderColor } }
							>
								{ __( 'Button Border', 'storesuite' ) }
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	);
};

export default ColorPreview;
