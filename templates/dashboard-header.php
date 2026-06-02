<?php
/**
 * Dashboard Header Template
 *
 * @package StoreSuite
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
?>
<header class="storesuite-dashboard-header">
	<div class="row align-items-center">
		<div class="col-md-6">
			<span
				class="storesuite-sidebar-trigger"
				role="button"
				tabindex="0"
				aria-expanded="true"
				aria-controls="storesuite-dashboard-sidebar"
				aria-label="<?php esc_attr_e( 'Toggle navigation menu', 'storesuite' ); ?>"
			>
				<svg xmlns="http://www.w3.org/2000/svg" id="Layer_1" data-name="Layer 1" viewBox="0 0 24 24" width="24" height="24" aria-hidden="true" focusable="false"><path d="M24,3c0,.55-.45,1-1,1H1c-.55,0-1-.45-1-1s.45-1,1-1H23c.55,0,1,.45,1,1ZM7,20H1c-.55,0-1,.45-1,1s.45,1,1,1H7c.55,0,1-.45,1-1s-.45-1-1-1ZM15,11H1c-.55,0-1,.45-1,1s.45,1,1,1H15c.55,0,1-.45,1-1s-.45-1-1-1Z"/></svg>
			</span>
		</div>
		<div class="col-md-6">
			<div class="storesuite-header-right">
				<a href="<?php echo esc_url( get_home_url() ); ?>" class="my-storesuite-button" target="_blank">
					<svg xmlns="http://www.w3.org/2000/svg" id="Layer_1" data-name="Layer 1" viewBox="0 0 24 24" width="24" height="24"><path d="M20,11v8c0,2.757-2.243,5-5,5H5c-2.757,0-5-2.243-5-5V9c0-2.757,2.243-5,5-5H13c.552,0,1,.448,1,1s-.448,1-1,1H5c-1.654,0-3,1.346-3,3v10c0,1.654,1.346,3,3,3H15c1.654,0,3-1.346,3-3V11c0-.552,.448-1,1-1s1,.448,1,1ZM21,0h-7c-.552,0-1,.448-1,1s.448,1,1,1h6.586L8.293,14.293c-.391,.391-.391,1.023,0,1.414,.195,.195,.451,.293,.707,.293s.512-.098,.707-.293L22,3.414v6.586c0,.552,.448,1,1,1s1-.448,1-1V3c0-1.654-1.346-3-3-3Z"/></svg>
					<?php esc_html_e( 'Visit Home', 'storesuite' ); ?>
				</a>
				<?php $storesuite_shop_url = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : get_home_url(); ?>
				<a href="<?php echo esc_url( $storesuite_shop_url ); ?>" class="storesuite-header-icon-link" target="_blank" aria-label="<?php esc_attr_e( 'Visit Store', 'storesuite' ); ?>" title="<?php esc_attr_e( 'Visit Store', 'storesuite' ); ?>">
					<svg xmlns="http://www.w3.org/2000/svg" id="Outline" viewBox="0 0 24 24" width="24" height="24" aria-hidden="true" focusable="false"><path d="M24,10a.988.988,0,0,0-.024-.217l-1.3-5.868A4.968,4.968,0,0,0,17.792,0H6.208a4.968,4.968,0,0,0-4.88,3.915L.024,9.783A.988.988,0,0,0,0,10v1a3.984,3.984,0,0,0,1,2.643V19a5.006,5.006,0,0,0,5,5H18a5.006,5.006,0,0,0,5-5V13.643A3.984,3.984,0,0,0,24,11ZM2,10.109l1.28-5.76A2.982,2.982,0,0,1,6.208,2H7V5A1,1,0,0,0,9,5V2h6V5a1,1,0,0,0,2,0V2h.792A2.982,2.982,0,0,1,20.72,4.349L22,10.109V11a2,2,0,0,1-2,2H19a2,2,0,0,1-2-2,1,1,0,0,0-2,0,2,2,0,0,1-2,2H11a2,2,0,0,1-2-2,1,1,0,0,0-2,0,2,2,0,0,1-2,2H4a2,2,0,0,1-2-2ZM18,22H6a3,3,0,0,1-3-3V14.873A3.978,3.978,0,0,0,4,15H5a3.99,3.99,0,0,0,3-1.357A3.99,3.99,0,0,0,11,15h2a3.99,3.99,0,0,0,3-1.357A3.99,3.99,0,0,0,19,15h1a3.978,3.978,0,0,0,1-.127V19A3,3,0,0,1,18,22Z"/></svg>
				</a>
				<button type="button" class="storesuite-theme-toggle" aria-label="<?php esc_attr_e( 'Toggle dark mode', 'storesuite' ); ?>" title="<?php esc_attr_e( 'Toggle dark mode', 'storesuite' ); ?>" aria-pressed="false">
					<svg class="storesuite-theme-toggle-moon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" aria-hidden="true" focusable="false"><path d="M12.048,21.963a10.014,10.014,0,0,1-3.300-.557A1,1,0,0,1,8.4,19.652a7.149,7.149,0,0,0,2.5.311A7.953,7.953,0,0,0,18.063,12.9a7.027,7.027,0,0,0-.282-2.461,1,1,0,0,1,1.736-.921A9.96,9.96,0,0,1,21.963,15.1,9.918,9.918,0,0,1,12.048,21.963ZM4.337,4.337A10,10,0,0,0,12.048,21a8.948,8.948,0,0,0,8.026-4.964A9.9,9.9,0,0,1,11.9,19.963a8.95,8.95,0,0,1-3.13-.39A9,9,0,0,1,4.337,4.337Z"/><path d="M11.226,2.064A10.045,10.045,0,0,0,4.337,4.337a9,9,0,0,0,4.435,15.236,8.953,8.953,0,0,1-4.706-7.7,9,9,0,0,1,8.05-8.954A9.945,9.945,0,0,0,11.226,2.064Z"/></svg>
					<svg class="storesuite-theme-toggle-sun" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" aria-hidden="true" focusable="false"><path d="M12,6a6,6,0,1,0,6,6A6.006,6.006,0,0,0,12,6Zm0,10a4,4,0,1,1,4-4A4,4,0,0,1,12,16Z"/><path d="M12,4a1,1,0,0,0,1-1V1a1,1,0,0,0-2,0V3A1,1,0,0,0,12,4Z"/><path d="M12,20a1,1,0,0,0-1,1v2a1,1,0,0,0,2,0V21A1,1,0,0,0,12,20Z"/><path d="M5.636,7.05A1,1,0,0,0,7.05,5.636L5.636,4.222A1,1,0,0,0,4.222,5.636Z"/><path d="M18.364,16.95a1,1,0,0,0-1.414,1.414l1.414,1.414a1,1,0,0,0,1.414-1.414Z"/><path d="M4,12a1,1,0,0,0-1-1H1a1,1,0,0,0,0,2H3A1,1,0,0,0,4,12Z"/><path d="M23,11H21a1,1,0,0,0,0,2h2a1,1,0,0,0,0-2Z"/><path d="M5.636,16.95,4.222,18.364a1,1,0,1,0,1.414,1.414L7.05,18.364A1,1,0,0,0,5.636,16.95Z"/><path d="M17.657,7.343a1,1,0,0,0,.707-.293l1.414-1.414a1,1,0,0,0-1.414-1.414L16.95,5.636a1,1,0,0,0,.707,1.707Z"/></svg>
				</button>
				<div class="storesuite-dropdown storesuite-header-dropdown">
					<span class="storesuite-dropdown-icon">
						<?php
						if ( is_user_logged_in() ) {
							$avatar_url = get_avatar_url( get_current_user_id() );
							echo '<img src="' . esc_url( $avatar_url ) . '" />';
						}
						?>
					</span>
					<div class="storesuite-dropdown-menu">
						<?php $storesuite_current_user = wp_get_current_user(); ?>
						<div class="storesuite-dropdown-user">
							<span class="storesuite-dropdown-user-avatar">
								<?php echo get_avatar( get_current_user_id(), 80 ); ?>
							</span>
							<span class="storesuite-dropdown-user-meta">
								<span class="storesuite-dropdown-user-name"><?php echo esc_html( $storesuite_current_user->display_name ); ?></span>
								<span class="storesuite-dropdown-user-email"><?php echo esc_html( $storesuite_current_user->user_email ); ?></span>
							</span>
						</div>
						<ul class="storesuite-dropdown-list">
							<li>
								<a href="<?php echo esc_url( storesuite_get_navigation_url( 'edit-account-details' ) ); ?>" class="dropdown-link">
									<svg xmlns="http://www.w3.org/2000/svg" id="Layer_1" data-name="Layer 1" viewBox="0 0 24 24" width="24" height="24"><path d="M15,6c0-3.309-2.691-6-6-6S3,2.691,3,6s2.691,6,6,6,6-2.691,6-6Zm-6,4c-2.206,0-4-1.794-4-4s1.794-4,4-4,4,1.794,4,4-1.794,4-4,4Zm-.008,4.938c.068,.548-.32,1.047-.869,1.116-3.491,.436-6.124,3.421-6.124,6.946,0,.552-.448,1-1,1s-1-.448-1-1c0-4.531,3.386-8.37,7.876-8.93,.542-.069,1.047,.32,1.116,.869Zm13.704,4.195l-.974-.562c.166-.497,.278-1.019,.278-1.572s-.111-1.075-.278-1.572l.974-.562c.478-.276,.642-.888,.366-1.366-.277-.479-.887-.644-1.366-.366l-.973,.562c-.705-.794-1.644-1.375-2.723-1.594v-1.101c0-.552-.448-1-1-1s-1,.448-1,1v1.101c-1.079,.22-2.018,.801-2.723,1.594l-.973-.562c-.48-.277-1.09-.113-1.366,.366-.276,.479-.112,1.09,.366,1.366l.974,.562c-.166,.497-.278,1.019-.278,1.572s.111,1.075,.278,1.572l-.974,.562c-.478,.276-.642,.888-.366,1.366,.186,.321,.521,.5,.867,.5,.169,0,.341-.043,.499-.134l.973-.562c.705,.794,1.644,1.375,2.723,1.594v1.101c0,.552,.448,1,1,1s1-.448,1-1v-1.101c1.079-.22,2.018-.801,2.723-1.594l.973,.562c.158,.091,.33,.134,.499,.134,.346,0,.682-.179,.867-.5,.276-.479,.112-1.09-.366-1.366Zm-5.696,.866c-1.654,0-3-1.346-3-3s1.346-3,3-3,3,1.346,3,3-1.346,3-3,3Z"/></svg>
									<?php echo esc_html__( 'Account', 'storesuite' ); ?>
								</a>
							</li>
							<li>
								<a href="<?php echo esc_url( wp_logout_url( home_url() ) ); ?>" class="dropdown-link">
									<svg xmlns="http://www.w3.org/2000/svg" id="Outline" viewBox="0 0 24 24" width="24" height="24"><path d="M22.829,9.172,18.95,5.293a1,1,0,0,0-1.414,1.414l3.879,3.879a2.057,2.057,0,0,1,.3.39c-.015,0-.027-.008-.042-.008h0L5.989,11a1,1,0,0,0,0,2h0l15.678-.032c.028,0,.051-.014.078-.016a2,2,0,0,1-.334.462l-3.879,3.879a1,1,0,1,0,1.414,1.414l3.879-3.879a4,4,0,0,0,0-5.656Z"/><path d="M7,22H5a3,3,0,0,1-3-3V5A3,3,0,0,1,5,2H7A1,1,0,0,0,7,0H5A5.006,5.006,0,0,0,0,5V19a5.006,5.006,0,0,0,5,5H7a1,1,0,0,0,0-2Z"/></svg>
									<?php echo esc_html__( 'Logout', 'storesuite' ); ?>
								</a>
							</li>
						</ul>
					</div>
				</div>
			</div>
		</div>
	</div>
</header>