import React from 'react';
import { Link, Outlet, useLocation } from 'react-router-dom';
import { __ } from '@wordpress/i18n';
import logo from '../images/store-suite-duotone.svg';
import './LayoutStyles.css';

const Layout = () => {
	const location = useLocation();
	const isActive = ( path ) => location.pathname === path;

	return (
		<div className="storesuite-setting-wrapper">
			<section className="storesuite-sidebar-nav">
				<div className="sidebar-logo">
					<img
						src={ logo }
						alt="Site Logo"
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
									<svg
										xmlns="http://www.w3.org/2000/svg"
										width="16"
										height="16"
										fill="currentColor"
										className="bi bi-gear"
										viewBox="0 0 16 16"
									>
										<path d="M8 4.754a3.246 3.246 0 1 0 0 6.492 3.246 3.246 0 0 0 0-6.492M5.754 8a2.246 2.246 0 1 1 4.492 0 2.246 2.246 0 0 1-4.492 0" />
										<path d="M9.796 1.343c-.527-1.79-3.065-1.79-3.592 0l-.094.319a.873.873 0 0 1-1.255.52l-.292-.16c-1.64-.892-3.433.902-2.54 2.541l.159.292a.873.873 0 0 1-.52 1.255l-.319.094c-1.79.527-1.79 3.065 0 3.592l.319.094a.873.873 0 0 1 .52 1.255l-.16.292c-.892 1.64.901 3.434 2.541 2.54l.292-.159a.873.873 0 0 1 1.255.52l.094.319c.527 1.79 3.065 1.79 3.592 0l.094-.319a.873.873 0 0 1 1.255-.52l.292.16c1.64.893 3.434-.902 2.54-2.541l-.159-.292a.873.873 0 0 1 .52-1.255l.319-.094c1.79-.527 1.79-3.065 0-3.592l-.319-.094a.873.873 0 0 1-.52-1.255l.16-.292c.893-1.64-.902-3.433-2.541-2.54l-.292.159a.873.873 0 0 1-1.255-.52zm-2.633.283c.246-.835 1.428-.835 1.674 0l.094.319a1.873 1.873 0 0 0 2.693 1.115l.291-.16c.764-.415 1.6.42 1.184 1.185l-.159.292a1.873 1.873 0 0 0 1.116 2.692l.318.094c.835.246.835 1.428 0 1.674l-.319.094a1.873 1.873 0 0 0-1.115 2.693l.16.291c.415.764-.42 1.6-1.185 1.184l-.291-.159a1.873 1.873 0 0 0-2.693 1.116l-.094.318c-.246.835-1.428.835-1.674 0l-.094-.319a1.873 1.873 0 0 0-2.692-1.115l-.292.16c-.764.415-1.6-.42-1.184-1.185l.159-.291A1.873 1.873 0 0 0 1.945 8.93l-.319-.094c-.835-.246-.835-1.428 0-1.674l.319-.094A1.873 1.873 0 0 0 3.06 4.377l-.16-.292c-.415-.764.42-1.6 1.185-1.184l.292.159a1.873 1.873 0 0 0 2.692-1.115z" />
									</svg>
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
									<svg
										xmlns="http://www.w3.org/2000/svg"
										width="16"
										height="16"
										fill="currentColor"
										class="bi bi-collection"
										viewBox="0 0 16 16"
									>
										<path d="M2.5 3.5a.5.5 0 0 1 0-1h11a.5.5 0 0 1 0 1zm2-2a.5.5 0 0 1 0-1h7a.5.5 0 0 1 0 1zM0 13a1.5 1.5 0 0 0 1.5 1.5h13A1.5 1.5 0 0 0 16 13V6a1.5 1.5 0 0 0-1.5-1.5h-13A1.5 1.5 0 0 0 0 6zm1.5.5A.5.5 0 0 1 1 13V6a.5.5 0 0 1 .5-.5h13a.5.5 0 0 1 .5.5v7a.5.5 0 0 1-.5.5z" />
									</svg>
								</div>
							</Link>
						</li>
						{ /* Add more links as needed */ }
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
