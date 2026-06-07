( function ( $ ) {
	var StoreSuiteGlobal = {
		init: function () {
			this.handleSidebarCollapseToggle();
			this.handleSubmenuToggle();
			this.handleDropdown();
			this.closeDropdownOutside();
		},

		handleSubmenuToggle: function () {
			$( document ).on(
				'click',
				'ul.storesuite-dashboard-menu li.has-submenu > a',
				function ( e ) {
					// In collapsed sidebar submenus appear as CSS hover flyouts — don't intercept.
					if (
						$( '.my-storesuite-container' ).hasClass(
							'storesuite-sidebar-collapsed'
						)
					) {
						return;
					}
					e.preventDefault();
					var $li = $( this ).closest( 'li' );
					var $submenu = $li.children( '.submenu' );
					if ( $li.hasClass( 'is-open' ) ) {
						$submenu.stop( true, false ).slideUp( 250, function () {
							$li.removeClass( 'is-open' );
							$submenu.css( 'display', '' );
						} );
					} else {
						$li.addClass( 'is-open' );
						$submenu
							.stop( true, false )
							.hide()
							.slideDown( 250, function () {
								$submenu.css( 'display', '' );
							} );
					}
				}
			);
		},

		handleSidebarCollapseToggle: function () {
			var collapsedPreferenceStorageKey = 'storesuite_sidebar_collapsed';
			var minViewportWidthForCollapsedSidebar = 768;
			var $dashboardContainer = $( '.my-storesuite-container' );
			var $sidebarCollapseToggle = $( '.storesuite-sidebar-trigger' );

			if (
				! $dashboardContainer.length ||
				! $sidebarCollapseToggle.length
			) {
				return;
			}

			function isViewportWideEnoughForCollapsedSidebar() {
				// Use the layout viewport (clientWidth) so this matches the CSS
				// media queries; window.innerWidth tracks the visual viewport and
				// can diverge under zoom / dev tools, desyncing JS from CSS.
				return (
					document.documentElement.clientWidth >=
					minViewportWidthForCollapsedSidebar
				);
			}

			function applySidebarCollapsedState( isCollapsed ) {
				$dashboardContainer.toggleClass(
					'storesuite-sidebar-collapsed',
					isCollapsed
				);
				$sidebarCollapseToggle.attr(
					'aria-expanded',
					isCollapsed ? 'false' : 'true'
				);
			}

			function persistCollapsedPreference( isCollapsed ) {
				try {
					if ( isCollapsed ) {
						localStorage.setItem(
							collapsedPreferenceStorageKey,
							'1'
						);
					} else {
						localStorage.removeItem(
							collapsedPreferenceStorageKey
						);
					}
				} catch ( storageError ) {}
			}

			function readCollapsedPreferenceFromStorage() {
				try {
					return (
						localStorage.getItem(
							collapsedPreferenceStorageKey
						) === '1'
					);
				} catch ( storageError ) {
					return false;
				}
			}

			function applyMobileOpenState( isOpen ) {
				$dashboardContainer.toggleClass(
					'storesuite-sidebar-mobile-open',
					isOpen
				);
				$sidebarCollapseToggle.attr(
					'aria-expanded',
					isOpen ? 'true' : 'false'
				);
			}

			function syncSidebarCollapsedState() {
				if ( ! isViewportWideEnoughForCollapsedSidebar() ) {
					// Narrow viewport: drop the desktop collapse, start closed.
					applySidebarCollapsedState( false );
					applyMobileOpenState( false );
					return;
				}
				// Wide viewport: drop the off-canvas state, restore preference.
				$dashboardContainer.removeClass(
					'storesuite-sidebar-mobile-open'
				);
				applySidebarCollapsedState(
					readCollapsedPreferenceFromStorage()
				);
			}

			function handleSidebarToggleInteraction( event ) {
				event.preventDefault();

				// Narrow viewport: the trigger opens/closes the off-canvas drawer.
				if ( ! isViewportWideEnoughForCollapsedSidebar() ) {
					applyMobileOpenState(
						! $dashboardContainer.hasClass(
							'storesuite-sidebar-mobile-open'
						)
					);
					return;
				}

				var shouldBeCollapsed = ! $dashboardContainer.hasClass(
					'storesuite-sidebar-collapsed'
				);
				applySidebarCollapsedState( shouldBeCollapsed );
				persistCollapsedPreference( shouldBeCollapsed );
			}

			// Close the off-canvas drawer when tapping the backdrop (outside
			// the sidebar and away from the trigger).
			function handleOutsideClickToClose( event ) {
				if (
					isViewportWideEnoughForCollapsedSidebar() ||
					! $dashboardContainer.hasClass(
						'storesuite-sidebar-mobile-open'
					)
				) {
					return;
				}
				var $target = $( event.target );
				if (
					$target.closest( '.my-storesuite-sidebar' ).length ||
					$target.closest( '.storesuite-sidebar-trigger' ).length
				) {
					return;
				}
				applyMobileOpenState( false );
			}

			syncSidebarCollapsedState();
			$( window ).on( 'resize', syncSidebarCollapsedState );
			$sidebarCollapseToggle.on(
				'click',
				handleSidebarToggleInteraction
			);
			$( document ).on( 'click', handleOutsideClickToClose );
		},

		handleDropdown: function () {
			$( document ).on(
				'click',
				'.storesuite-dropdown-icon',
				function () {
					var $menu = $( this )
						.closest( '.storesuite-dropdown' )
						.find( '.storesuite-dropdown-menu' );
					$( '.storesuite-dropdown-menu' )
						.not( $menu )
						.stop( true, false )
						.slideUp( 200 );
					$menu.stop( true, false ).slideToggle( 200 );
				}
			);
		},

		closeDropdownOutside: function () {
			$( document ).on( 'click', function ( event ) {
				if (
					! $( event.target ).closest( '.storesuite-dropdown' ).length
				) {
					$( '.storesuite-dropdown-menu' )
						.stop( true, false )
						.slideUp( 200 );
				}
			} );
		},
	};

	StoreSuiteGlobal.init();
} )( jQuery );
