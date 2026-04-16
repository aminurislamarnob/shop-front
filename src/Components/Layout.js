/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';

/**
 * External dependencies
 */
import { Link, Outlet, useLocation } from 'react-router-dom';

/**
 * Internal dependencies
 */
import { GearIcon, PaletteIcon, CollectionIcon } from './icons';
import logo from '../images/store-suite-duotone.svg';

const Layout = () => {
	const location = useLocation();
	const isActive = ( path ) => location.pathname === path;

	return (
		<div className="storesuite-setting-wrapper">
			<section className="storesuite-sidebar-nav">
				<div className="sidebar-logo">
					<img
						src={ logo }
						alt={ __( 'StoreSuite', 'storesuite' ) }
						className="layout-logo"
						width="163"
						height="44"
					/>
				</div>
				<nav>
					<ul>
						<li className={ isActive( '/' ) ? 'active' : '' }>
							<Link to="/">
								<div className="menu-title">
									{ __( 'General', 'storesuite' ) }
								</div>
								<div className="menu-title-description">
									{ __( 'Set dashbord page', 'storesuite' ) }
								</div>
								<div className="menu-icon">
									<GearIcon />
								</div>
							</Link>
						</li>
						<li
							className={
								isActive( '/appearance-settings' )
									? 'active'
									: ''
							}
						>
							<Link to="/appearance-settings">
								<div className="menu-title">
									{ __( 'Colors', 'storesuite' ) }
								</div>
								<div className="menu-title-description">
									{ __( 'Config all colors', 'storesuite' ) }
								</div>
								<div className="menu-icon">
									<PaletteIcon />
								</div>
							</Link>
						</li>
						<li
							className={
								isActive( '/pagination-settings' )
									? 'active'
									: ''
							}
						>
							<Link to="/pagination-settings">
								<div className="menu-title">
									{ __( 'Pagination', 'storesuite' ) }
								</div>
								<div className="menu-title-description">
									{ __(
										'List items per page',
										'storesuite'
									) }
								</div>
								<div className="menu-icon">
									<CollectionIcon />
								</div>
							</Link>
						</li>
					</ul>
				</nav>
			</section>
			<main>
				<Outlet />
			</main>
		</div>
	);
};

export default Layout;
