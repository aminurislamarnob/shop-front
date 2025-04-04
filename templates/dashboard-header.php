<header class="msf-dashboard-header">
	<div class="row align-items-center">
		<div class="col-md-6">
			<span class="msf-sidebar-trigger">
				<svg xmlns="http://www.w3.org/2000/svg" id="Layer_1" data-name="Layer 1" viewBox="0 0 24 24" width="24" height="24"><path d="M24,3c0,.55-.45,1-1,1H1c-.55,0-1-.45-1-1s.45-1,1-1H23c.55,0,1,.45,1,1ZM7,20H1c-.55,0-1,.45-1,1s.45,1,1,1H7c.55,0,1-.45,1-1s-.45-1-1-1ZM15,11H1c-.55,0-1,.45-1,1s.45,1,1,1H15c.55,0,1-.45,1-1s-.45-1-1-1Z"/></svg>
			</span>
		</div>
		<div class="col-md-6">
			<div class="msf-header-right">
				<a href="<?php echo esc_url( get_home_url() ); ?>" class="my-shop-front-button">
					<svg xmlns="http://www.w3.org/2000/svg" id="Layer_1" data-name="Layer 1" viewBox="0 0 24 24" width="24" height="24"><path d="M20,11v8c0,2.757-2.243,5-5,5H5c-2.757,0-5-2.243-5-5V9c0-2.757,2.243-5,5-5H13c.552,0,1,.448,1,1s-.448,1-1,1H5c-1.654,0-3,1.346-3,3v10c0,1.654,1.346,3,3,3H15c1.654,0,3-1.346,3-3V11c0-.552,.448-1,1-1s1,.448,1,1ZM21,0h-7c-.552,0-1,.448-1,1s.448,1,1,1h6.586L8.293,14.293c-.391,.391-.391,1.023,0,1.414,.195,.195,.451,.293,.707,.293s.512-.098,.707-.293L22,3.414v6.586c0,.552,.448,1,1,1s1-.448,1-1V3c0-1.654-1.346-3-3-3Z"/></svg>
					<?php esc_html_e( 'Visit Home', 'shop-front' ); ?>
				</a>
				<div class="msfc-dropdown msf-header-dropdown">
					<span class="msfc-dropdown-icon">
						<?php
						if ( is_user_logged_in() ) {
							$avatar_url = get_avatar_url( get_current_user_id() );
							echo '<img src="' . esc_url( $avatar_url ) . '" />';
						}
						?>
					</span>
					<ul class="msfc-dropdown-menu">
						<li>
							<a href="<?php echo esc_url( wp_logout_url( home_url() ) ); ?>" class="dropdown-link">
								<svg xmlns="http://www.w3.org/2000/svg" id="Outline" viewBox="0 0 24 24" width="24" height="24"><path d="M22.829,9.172,18.95,5.293a1,1,0,0,0-1.414,1.414l3.879,3.879a2.057,2.057,0,0,1,.3.39c-.015,0-.027-.008-.042-.008h0L5.989,11a1,1,0,0,0,0,2h0l15.678-.032c.028,0,.051-.014.078-.016a2,2,0,0,1-.334.462l-3.879,3.879a1,1,0,1,0,1.414,1.414l3.879-3.879a4,4,0,0,0,0-5.656Z"/><path d="M7,22H5a3,3,0,0,1-3-3V5A3,3,0,0,1,5,2H7A1,1,0,0,0,7,0H5A5.006,5.006,0,0,0,0,5V19a5.006,5.006,0,0,0,5,5H7a1,1,0,0,0,0-2Z"/></svg>
								<?php echo esc_html__( 'Logout', 'shop-front' ); ?>
							</a>
						</li>
					</ul>
				</div>
			</div>
		</div>
	</div>
</header>