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
					var isOpen = $li.hasClass( 'is-open' );
					$(
						'ul.storesuite-dashboard-menu li.has-submenu.is-open'
					).removeClass( 'is-open' );
					if ( ! isOpen ) {
						$li.addClass( 'is-open' );
					}
				}
			);
		},

		handleSidebarCollapseToggle: function () {
			var collapsedPreferenceStorageKey = 'storesuite_sidebar_collapsed';
			var minViewportWidthForCollapsedSidebar = 783;
			var $dashboardContainer = $( '.my-storesuite-container' );
			var $sidebarCollapseToggle = $( '.storesuite-sidebar-trigger' );

			if (
				! $dashboardContainer.length ||
				! $sidebarCollapseToggle.length
			) {
				return;
			}

			function isViewportWideEnoughForCollapsedSidebar() {
				return window.innerWidth >= minViewportWidthForCollapsedSidebar;
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

			function syncSidebarCollapsedState() {
				if ( ! isViewportWideEnoughForCollapsedSidebar() ) {
					applySidebarCollapsedState( false );
					return;
				}
				applySidebarCollapsedState(
					readCollapsedPreferenceFromStorage()
				);
			}

			function handleSidebarToggleInteraction( event ) {
				if ( ! isViewportWideEnoughForCollapsedSidebar() ) {
					return;
				}
				event.preventDefault();
				var shouldBeCollapsed = ! $dashboardContainer.hasClass(
					'storesuite-sidebar-collapsed'
				);
				applySidebarCollapsedState( shouldBeCollapsed );
				persistCollapsedPreference( shouldBeCollapsed );
			}

			syncSidebarCollapsedState();
			$( window ).on( 'resize', syncSidebarCollapsedState );
			$sidebarCollapseToggle.on(
				'click',
				handleSidebarToggleInteraction
			);
		},

		handleDropdown: function () {
			$( document ).on(
				'click',
				'.storesuite-dropdown-icon',
				function () {
					$( '.storesuite-dropdown-menu' ).hide();
					$( this )
						.closest( '.storesuite-dropdown' )
						.find( '.storesuite-dropdown-menu' )
						.toggle();
				}
			);
		},

		closeDropdownOutside: function () {
			$( document ).on( 'click', function ( event ) {
				if (
					! $( event.target ).closest( '.storesuite-dropdown' ).length
				) {
					$( '.storesuite-dropdown-menu' ).hide();
				}
			} );
		},
	};

	StoreSuiteGlobal.init();
} )( jQuery );
