const SettingsHeader = ( { icon: Icon, logo, title, subTitle, actions } ) => {
	return (
		<div className="storesuite-header-wrapper">
			<div className="storesuite-settings-header">
				<div className="storesuite-settings-header-inner">
					<div className="storesuite-header-text-column">
						<div className="storesuite-header-title-container">
							{ logo ? (
								<img
									className="storesuite-settings-header-logo"
									src={ logo }
									alt={ title }
								/>
							) : (
								<>
									{ Icon && (
										<div className="storesuite-settings-header-icon">
											<Icon />
										</div>
									) }
									<h2>{ title }</h2>
								</>
							) }
						</div>
						{ subTitle && (
							<p className="storesuite-header-subtitle">
								{ subTitle }
							</p>
						) }
					</div>
					{ actions && (
						<div className="storesuite-header-actions">
							{ actions }
						</div>
					) }
				</div>
			</div>
		</div>
	);
};

export default SettingsHeader;
