<?php
/**
 * Edit account form – same fields as WooCommerce My Account, StoreSuite form styling.
 *
 * @package StoreSuite
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$user = get_user_by( 'id', get_current_user_id() );
if ( ! $user ) {
	return;
}

// Active settings tab from the ?tab= query var (defaults to profile).
$storesuite_allowed_tabs = array( 'profile', 'address', 'password' );
// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only UI state.
$storesuite_active_tab = isset( $_GET['tab'] ) ? sanitize_key( wp_unslash( $_GET['tab'] ) ) : 'profile';
if ( ! in_array( $storesuite_active_tab, $storesuite_allowed_tabs, true ) ) {
	$storesuite_active_tab = 'profile';
}

do_action( 'storesuite_dashboard_wrapper_start' );
?>
<div class="my-storesuite-container">
	<aside id="storesuite-dashboard-sidebar" class="my-storesuite-sidebar" role="navigation" aria-label="<?php esc_attr_e( 'Store dashboard navigation', 'storesuite' ); ?>">
		<?php do_action( 'storesuite_dashboard_navigation' ); ?>
	</aside>
	<div class="my-storesuite-wrapper">
		<?php do_action( 'storesuite_dashboard_content_before' ); ?>
		<main class="my-storesuite-page-content">
			<?php do_action( 'storesuite_dashboard_before_main_content' ); ?>

			<?php
			if ( function_exists( 'wc_print_notices' ) ) {
				wc_print_notices();
			}
			?>

			<?php do_action( 'woocommerce_before_edit_account_form' ); ?>

			<form id="storesuite-edit-account-form" class="storesuite-edit-account-form edit-account" action="" method="post" <?php do_action( 'woocommerce_edit_account_form_tag' ); ?>>
				<?php do_action( 'woocommerce_edit_account_form_start' ); ?>

				<div class="storesuite-card storesuite-mb-24 storesuite-account-header-card">
					<div class="storesuite-account-header-text">
						<h2 class="storesuite-account-header-title">
							<?php
							/* translators: %s: user display name. */
							printf( esc_html__( 'Hi, %s!', 'storesuite' ), esc_html( $user->display_name ) );
							?>
						</h2>
						<p class="storesuite-account-header-subtitle"><?php esc_html_e( 'Manage your account information and preferences', 'storesuite' ); ?></p>
					</div>
					<button type="submit" class="my-storesuite-button" name="save_account_details" value="<?php esc_attr_e( 'Save All Changes', 'storesuite' ); ?>"><?php esc_html_e( 'Save All Changes', 'storesuite' ); ?></button>
				</div>

				<div class="row storesuite-account-layout">
					<div class="col-md-3">
						<div class="storesuite-card storesuite-account-nav">
							<ul class="storesuite-account-nav-list">
								<li>
									<a href="#" class="storesuite-account-nav-link<?php echo 'profile' === $storesuite_active_tab ? ' is-active' : ''; ?>" data-account-tab="profile">
										<svg xmlns="http://www.w3.org/2000/svg" id="Layer_1" viewBox="0 0 24 24" width="24" height="24" data-name="Layer 1" aria-hidden="true"><path d="m12 1c-7.71 0-11 3.29-11 11s3.29 11 11 11 11-3.29 11-11-3.29-11-11-11zm-4.293 19.475c.377-1.544 1.37-2.475 4.293-2.475s3.917.931 4.293 2.475c-1.176.357-2.594.525-4.293.525s-3.117-.168-4.293-.525zm10.413-.845c-1.012-3.217-3.916-3.631-6.119-3.631s-5.107.413-6.119 3.631c-2.028-1.35-2.881-3.774-2.881-7.631-.001-6.56 2.438-8.999 8.999-8.999s9 2.439 9 9c0 3.857-.853 6.281-2.881 7.631zm-6.12-13.63c-2.691 0-4 1.309-4 4s1.309 4 4 4 4-1.309 4-4-1.309-4-4-4zm0 6c-1.589 0-2-.411-2-2s.411-2 2-2 2 .411 2 2-.411 2-2 2z"/></svg>
										<?php esc_html_e( 'Profile', 'storesuite' ); ?>
									</a>
								</li>
								<li>
									<a href="#" class="storesuite-account-nav-link<?php echo 'address' === $storesuite_active_tab ? ' is-active' : ''; ?>" data-account-tab="address">
										<svg xmlns="http://www.w3.org/2000/svg" id="Outline" viewBox="0 0 24 24" width="24" height="24" aria-hidden="true"><path d="M12,6a4,4,0,1,0,4,4A4,4,0,0,0,12,6Zm0,6a2,2,0,1,1,2-2A2,2,0,0,1,12,12Z"/><path d="M12,24a5.271,5.271,0,0,1-4.311-2.2c-3.811-5.257-5.744-9.209-5.744-11.747a10.055,10.055,0,0,1,20.11,0c0,2.538-1.933,6.49-5.744,11.747A5.271,5.271,0,0,1,12,24ZM12,2.181a7.883,7.883,0,0,0-7.874,7.874c0,2.01,1.893,5.727,5.329,10.466a3.145,3.145,0,0,0,5.09,0c3.436-4.739,5.329-8.456,5.329-10.466A7.883,7.883,0,0,0,12,2.181Z"/></svg>
										<?php esc_html_e( 'Address', 'storesuite' ); ?>
									</a>
								</li>
								<li>
									<a href="#" class="storesuite-account-nav-link<?php echo 'password' === $storesuite_active_tab ? ' is-active' : ''; ?>" data-account-tab="password">
										<svg xmlns="http://www.w3.org/2000/svg" id="Outline" viewBox="0 0 24 24" width="24" height="24" aria-hidden="true"><path d="M19,8.424V7A7,7,0,0,0,5,7V8.424A5,5,0,0,0,2,13v6a5.006,5.006,0,0,0,5,5H17a5.006,5.006,0,0,0,5-5V13A5,5,0,0,0,19,8.424ZM7,7A5,5,0,0,1,17,7V8H7ZM20,19a3,3,0,0,1-3,3H7a3,3,0,0,1-3-3V13a3,3,0,0,1,3-3H17a3,3,0,0,1,3,3Z"/><path d="M12,14a1,1,0,0,0-1,1v2a1,1,0,0,0,2,0V15A1,1,0,0,0,12,14Z"/></svg>
										<?php esc_html_e( 'Password', 'storesuite' ); ?>
									</a>
								</li>
								<li>
									<a href="<?php echo esc_url( wp_logout_url( home_url() ) ); ?>" class="storesuite-account-nav-link storesuite-account-nav-logout">
										<svg xmlns="http://www.w3.org/2000/svg" id="Outline" viewBox="0 0 24 24" width="24" height="24" aria-hidden="true"><path d="M22.829,9.172,18.95,5.293a1,1,0,0,0-1.414,1.414l3.879,3.879a2.057,2.057,0,0,1,.3.39c-.015,0-.027-.008-.042-.008h0L5.989,11a1,1,0,0,0,0,2h0l15.678-.032c.028,0,.051-.014.078-.016a2,2,0,0,1-.334.462l-3.879,3.879a1,1,0,1,0,1.414,1.414l3.879-3.879a4,4,0,0,0,0-5.656Z"/><path d="M7,22H5a3,3,0,0,1-3-3V5A3,3,0,0,1,5,2H7A1,1,0,0,0,7,0H5A5.006,5.006,0,0,0,0,5V19a5.006,5.006,0,0,0,5,5H7a1,1,0,0,0,0-2Z"/></svg>
										<?php esc_html_e( 'Logout', 'storesuite' ); ?>
									</a>
								</li>
							</ul>
						</div>
					</div>
					<div class="col-md-9">
						<div class="storesuite-account-panel<?php echo 'profile' === $storesuite_active_tab ? ' is-active' : ''; ?>" data-account-panel="profile">
						<div class="storesuite-card storesuite-card-with-header storesuite-mb-24">
							<h3 class="storesuite-card-title"><?php esc_html_e( 'My Profile', 'storesuite' ); ?></h3>
							<div class="storesuite-card-content">
								<?php
								$storesuite_avatar_id  = (int) get_user_meta( $user->ID, 'storesuite_profile_picture_id', true );
								$storesuite_avatar_url = $storesuite_avatar_id ? wp_get_attachment_image_url( $storesuite_avatar_id, 'thumbnail' ) : '';
								if ( ! $storesuite_avatar_url ) {
									$storesuite_avatar_url = get_avatar_url( $user->ID );
								}
								?>
								<div class="storesuite-form-group storesuite-account-avatar-group">
									<label for="account_profile_picture_id"><?php esc_html_e( 'Profile picture', 'storesuite' ); ?></label>
									<input type="hidden" id="account_profile_picture_id" name="account_profile_picture_id" value="<?php echo esc_attr( $storesuite_avatar_id ? $storesuite_avatar_id : '' ); ?>">
									<div id="storesuite-account-avatar" class="storesuite-account-avatar<?php echo esc_attr( $storesuite_avatar_id ? ' has-image' : '' ); ?>">
										<span class="storesuite-account-avatar-preview">
											<img src="<?php echo esc_url( $storesuite_avatar_url ); ?>" alt="<?php esc_attr_e( 'Profile picture', 'storesuite' ); ?>">
										</span>
										<button type="button" class="storesuite-account-avatar-upload" aria-label="<?php esc_attr_e( 'Upload image', 'storesuite' ); ?>" title="<?php esc_attr_e( 'Upload image', 'storesuite' ); ?>">
											<svg xmlns="http://www.w3.org/2000/svg" id="Layer_1" data-name="Layer 1" viewBox="0 0 24 24" width="16" height="16" aria-hidden="true"><path d="m7,24h-2c-2.757,0-5-2.243-5-5v-2c0-.553.447-1,1-1s1,.447,1,1v2c0,1.654,1.346,3,3,3h2c.553,0,1,.447,1,1s-.447,1-1,1Zm17-5v-2c0-.553-.447-1-1-1s-1,.447-1,1v2c0,1.654-1.346,3-3,3h-2c-.553,0-1,.447-1,1s.447,1,1,1h2c2.757,0,5-2.243,5-5Zm0-12v-2c0-2.757-2.243-5-5-5h-2c-.553,0-1,.447-1,1s.447,1,1,1h2c1.654,0,3,1.346,3,3v2c0,.553.447,1,1,1s1-.447,1-1Zm-22,0v-2c0-1.654,1.346-3,3-3h2c.553,0,1-.447,1-1s-.447-1-1-1h-2C2.243,0,0,2.243,0,5v2c0,.553.447,1,1,1s1-.447,1-1Zm16,3v4c0,1.654-1.346,3-3,3h-6c-1.654,0-3-1.346-3-3v-4c0-1.639,1.321-2.974,2.953-2.999l.696-1.083c.368-.574.997-.918,1.682-.918h1.338c.685,0,1.313.344,1.683.919l.695,1.082c1.633.025,2.953,1.36,2.953,2.999Zm-4,2c0-1.105-.895-2-2-2s-2,.895-2,2,.895,2,2,2,2-.895,2-2Z"/></svg>
										</button>
									</div>
								</div>

								<div class="row">
									<div class="col-md-6">
										<div class="storesuite-form-group">
											<label for="account_first_name"><?php esc_html_e( 'First name', 'storesuite' ); ?> <span class="req">*</span></label>
											<input type="text" class="storesuite-form-control" name="account_first_name" id="account_first_name" autocomplete="given-name" value="<?php echo esc_attr( $user->first_name ); ?>" />
										</div>
									</div>
									<div class="col-md-6">
										<div class="storesuite-form-group">
											<label for="account_last_name"><?php esc_html_e( 'Last name', 'storesuite' ); ?> <span class="req">*</span></label>
											<input type="text" class="storesuite-form-control" name="account_last_name" id="account_last_name" autocomplete="family-name" value="<?php echo esc_attr( $user->last_name ); ?>" />
										</div>
									</div>
								</div>

								<div class="storesuite-form-group">
									<label for="account_display_name"><?php esc_html_e( 'Display name', 'storesuite' ); ?> <span class="req">*</span></label>
									<input type="text" class="storesuite-form-control" name="account_display_name" id="account_display_name" value="<?php echo esc_attr( $user->display_name ); ?>" />
									<small class="storesuite-form-text"><?php esc_html_e( 'This will be how your name will be displayed in the account section and in reviews', 'storesuite' ); ?></small>
								</div>

								<div class="row">
									<div class="col-md-6">
										<div class="storesuite-form-group">
											<label for="account_email"><?php esc_html_e( 'Email address', 'storesuite' ); ?> <span class="req">*</span></label>
											<input type="email" class="storesuite-form-control" name="account_email" id="account_email" autocomplete="email" value="<?php echo esc_attr( $user->user_email ); ?>" />
										</div>
									</div>
									<div class="col-md-6">
										<div class="storesuite-form-group">
											<label for="account_website"><?php esc_html_e( 'Website', 'storesuite' ); ?></label>
											<input type="url" class="storesuite-form-control" name="account_website" id="account_website" autocomplete="url" value="<?php echo esc_url( $user->user_url ); ?>" />
										</div>
									</div>
								</div>

								<div class="storesuite-form-group">
									<label for="account_description"><?php esc_html_e( 'Biography', 'storesuite' ); ?></label>
									<textarea class="storesuite-form-control" name="account_description" id="account_description" rows="4"><?php echo esc_textarea( get_user_meta( $user->ID, 'description', true ) ); ?></textarea>
									<small class="storesuite-form-text"><?php esc_html_e( 'Share a little biographical information to fill out your profile. This may be shown publicly.', 'storesuite' ); ?></small>
								</div>

								<?php do_action( 'woocommerce_edit_account_form_fields' ); ?>
							</div>
						</div>
						</div><!-- /profile panel -->

						<div class="storesuite-account-panel<?php echo 'address' === $storesuite_active_tab ? ' is-active' : ''; ?>" data-account-panel="address">
							<?php
							$storesuite_address_types = array(
								'billing'  => __( 'Billing address', 'storesuite' ),
								'shipping' => __( 'Shipping address', 'storesuite' ),
							);
							foreach ( $storesuite_address_types as $storesuite_atype => $storesuite_atitle ) :
								$storesuite_country = get_user_meta( $user->ID, $storesuite_atype . '_country', true );
								$storesuite_fields  = WC()->countries->get_address_fields( $storesuite_country, $storesuite_atype . '_' );
								?>
								<div class="storesuite-card storesuite-card-with-header storesuite-mb-24 storesuite-address-card">
									<h3 class="storesuite-card-title storesuite-address-card-title">
										<span><?php echo esc_html( $storesuite_atitle ); ?></span>
										<?php if ( 'shipping' === $storesuite_atype ) : ?>
											<button type="button" class="my-storesuite-button my-storesuite-button-soft storesuite-copy-billing" aria-label="<?php esc_attr_e( 'Copy from billing', 'storesuite' ); ?>" title="<?php esc_attr_e( 'Copy from billing', 'storesuite' ); ?>">
											<svg xmlns="http://www.w3.org/2000/svg" id="Layer_1" viewBox="0 0 24 24" width="16" height="16" data-name="Layer 1" aria-hidden="true"><path d="m15 20h-10a5.006 5.006 0 0 1 -5-5v-10a5.006 5.006 0 0 1 5-5h10a5.006 5.006 0 0 1 5 5v10a5.006 5.006 0 0 1 -5 5zm-10-18a3 3 0 0 0 -3 3v10a3 3 0 0 0 3 3h10a3 3 0 0 0 3-3v-10a3 3 0 0 0 -3-3zm19 17v-13a1 1 0 0 0 -2 0v13a3 3 0 0 1 -3 3h-13a1 1 0 0 0 0 2h13a5.006 5.006 0 0 0 5-5z"/></svg>
										</button>
										<?php endif; ?>
									</h3>
									<div class="storesuite-card-content">
										<div class="storesuite-address-grid">
											<?php
											foreach ( $storesuite_fields as $storesuite_key => $storesuite_field ) {
												$storesuite_value = get_user_meta( $user->ID, $storesuite_key, true );
												if ( '' === $storesuite_value && 'billing_email' === $storesuite_key ) {
													$storesuite_value = $user->user_email;
												}
												woocommerce_form_field( $storesuite_key, $storesuite_field, $storesuite_value );
											}
											?>
										</div>
									</div>
								</div>
							<?php endforeach; ?>
						</div><!-- /address panel -->

						<div class="storesuite-account-panel<?php echo 'password' === $storesuite_active_tab ? ' is-active' : ''; ?>" data-account-panel="password">
						<div id="storesuite-edit-account-password-card" class="storesuite-card storesuite-card-with-header storesuite-mb-24">
							<h3 class="storesuite-card-title storesuite-text-capitalize"><?php esc_html_e( 'Password change', 'storesuite' ); ?></h3>
							<div class="storesuite-card-content">
								<div class="storesuite-form-group">
									<label for="password_current"><?php esc_html_e( 'Current password (leave blank to leave unchanged)', 'storesuite' ); ?></label>
									<div class="storesuite-password-field">
										<input type="password" class="storesuite-form-control" name="password_current" id="password_current" autocomplete="current-password" />
										<button
											type="button"
											class="storesuite-password-toggle"
											data-target="#password_current"
											data-show-label="<?php esc_attr_e( 'Show password', 'storesuite' ); ?>"
											data-hide-label="<?php esc_attr_e( 'Hide password', 'storesuite' ); ?>"
											aria-label="<?php esc_attr_e( 'Show password', 'storesuite' ); ?>"
											title="<?php esc_attr_e( 'Show password', 'storesuite' ); ?>"
										>
											<span class="storesuite-password-icon storesuite-password-icon-show" aria-hidden="true">
												<svg xmlns="http://www.w3.org/2000/svg" id="Outline" viewBox="0 0 24 24" width="24" height="24"><path d="M23.271,9.419C21.72,6.893,18.192,2.655,12,2.655S2.28,6.893.729,9.419a4.908,4.908,0,0,0,0,5.162C2.28,17.107,5.808,21.345,12,21.345s9.72-4.238,11.271-6.764A4.908,4.908,0,0,0,23.271,9.419Zm-1.705,4.115C20.234,15.7,17.219,19.345,12,19.345S3.766,15.7,2.434,13.534a2.918,2.918,0,0,1,0-3.068C3.766,8.3,6.781,4.655,12,4.655s8.234,3.641,9.566,5.811A2.918,2.918,0,0,1,21.566,13.534Z"/><path d="M12,7a5,5,0,1,0,5,5A5.006,5.006,0,0,0,12,7Zm0,8a3,3,0,1,1,3-3A3,3,0,0,1,12,15Z"/></svg>
											</span>
											<span class="storesuite-password-icon storesuite-password-icon-hide storesuite-hide" aria-hidden="true">
												<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24"><g id="_01_align_center" data-name="01 align center"><path d="M23.821,11.181v0a15.736,15.736,0,0,0-4.145-5.44l3.032-3.032L21.293,1.293,18,4.583A11.783,11.783,0,0,0,12,3C4.5,3,1.057,9.261.179,11.181a1.969,1.969,0,0,0,0,1.64,15.736,15.736,0,0,0,4.145,5.44L1.293,21.293l1.414,1.414L6,19.417A11.783,11.783,0,0,0,12,21c7.5,0,10.943-6.261,11.821-8.181A1.968,1.968,0,0,0,23.821,11.181ZM2,12.011C2.75,10.366,5.693,5,12,5a9.847,9.847,0,0,1,4.518,1.068L14.753,7.833a4.992,4.992,0,0,0-6.92,6.92L5.754,16.832A13.647,13.647,0,0,1,2,12.011ZM15,12a3,3,0,0,1-3,3,2.951,2.951,0,0,1-1.285-.3L14.7,10.715A2.951,2.951,0,0,1,15,12ZM9,12a3,3,0,0,1,3-3,2.951,2.951,0,0,1,1.285.3L9.3,13.285A2.951,2.951,0,0,1,9,12Zm3,7a9.847,9.847,0,0,1-4.518-1.068l1.765-1.765a4.992,4.992,0,0,0,6.92-6.92l2.078-2.078A13.584,13.584,0,0,1,22,12C21.236,13.657,18.292,19,12,19Z"/></g></svg>
											</span>
										</button>
									</div>
								</div>
								<div class="storesuite-form-group">
									<label for="password_1"><?php esc_html_e( 'New password (leave blank to leave unchanged)', 'storesuite' ); ?></label>
									<div class="storesuite-password-field">
										<input type="password" class="storesuite-form-control" name="password_1" id="password_1" autocomplete="new-password" />
										<button
											type="button"
											class="storesuite-password-toggle"
											data-target="#password_1"
											data-show-label="<?php esc_attr_e( 'Show password', 'storesuite' ); ?>"
											data-hide-label="<?php esc_attr_e( 'Hide password', 'storesuite' ); ?>"
											aria-label="<?php esc_attr_e( 'Show password', 'storesuite' ); ?>"
											title="<?php esc_attr_e( 'Show password', 'storesuite' ); ?>"
										>
											<span class="storesuite-password-icon storesuite-password-icon-show" aria-hidden="true">
												<svg xmlns="http://www.w3.org/2000/svg" id="Outline" viewBox="0 0 24 24" width="24" height="24"><path d="M23.271,9.419C21.72,6.893,18.192,2.655,12,2.655S2.28,6.893.729,9.419a4.908,4.908,0,0,0,0,5.162C2.28,17.107,5.808,21.345,12,21.345s9.72-4.238,11.271-6.764A4.908,4.908,0,0,0,23.271,9.419Zm-1.705,4.115C20.234,15.7,17.219,19.345,12,19.345S3.766,15.7,2.434,13.534a2.918,2.918,0,0,1,0-3.068C3.766,8.3,6.781,4.655,12,4.655s8.234,3.641,9.566,5.811A2.918,2.918,0,0,1,21.566,13.534Z"/><path d="M12,7a5,5,0,1,0,5,5A5.006,5.006,0,0,0,12,7Zm0,8a3,3,0,1,1,3-3A3,3,0,0,1,12,15Z"/></svg>
											</span>
											<span class="storesuite-password-icon storesuite-password-icon-hide storesuite-hide" aria-hidden="true">
												<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24"><g id="_01_align_center" data-name="01 align center"><path d="M23.821,11.181v0a15.736,15.736,0,0,0-4.145-5.44l3.032-3.032L21.293,1.293,18,4.583A11.783,11.783,0,0,0,12,3C4.5,3,1.057,9.261.179,11.181a1.969,1.969,0,0,0,0,1.64,15.736,15.736,0,0,0,4.145,5.44L1.293,21.293l1.414,1.414L6,19.417A11.783,11.783,0,0,0,12,21c7.5,0,10.943-6.261,11.821-8.181A1.968,1.968,0,0,0,23.821,11.181ZM2,12.011C2.75,10.366,5.693,5,12,5a9.847,9.847,0,0,1,4.518,1.068L14.753,7.833a4.992,4.992,0,0,0-6.92,6.92L5.754,16.832A13.647,13.647,0,0,1,2,12.011ZM15,12a3,3,0,0,1-3,3,2.951,2.951,0,0,1-1.285-.3L14.7,10.715A2.951,2.951,0,0,1,15,12ZM9,12a3,3,0,0,1,3-3,2.951,2.951,0,0,1,1.285.3L9.3,13.285A2.951,2.951,0,0,1,9,12Zm3,7a9.847,9.847,0,0,1-4.518-1.068l1.765-1.765a4.992,4.992,0,0,0,6.92-6.92l2.078-2.078A13.584,13.584,0,0,1,22,12C21.236,13.657,18.292,19,12,19Z"/></g></svg>
											</span>
										</button>
									</div>
								</div>
								<div class="storesuite-form-group">
									<label for="password_2"><?php esc_html_e( 'Confirm new password', 'storesuite' ); ?></label>
									<div class="storesuite-password-field">
										<input type="password" class="storesuite-form-control" name="password_2" id="password_2" autocomplete="new-password" />
										<button
											type="button"
											class="storesuite-password-toggle"
											data-target="#password_2"
											data-show-label="<?php esc_attr_e( 'Show password', 'storesuite' ); ?>"
											data-hide-label="<?php esc_attr_e( 'Hide password', 'storesuite' ); ?>"
											aria-label="<?php esc_attr_e( 'Show password', 'storesuite' ); ?>"
											title="<?php esc_attr_e( 'Show password', 'storesuite' ); ?>"
										>
											<span class="storesuite-password-icon storesuite-password-icon-show" aria-hidden="true">
												<svg xmlns="http://www.w3.org/2000/svg" id="Outline" viewBox="0 0 24 24" width="24" height="24"><path d="M23.271,9.419C21.72,6.893,18.192,2.655,12,2.655S2.28,6.893.729,9.419a4.908,4.908,0,0,0,0,5.162C2.28,17.107,5.808,21.345,12,21.345s9.72-4.238,11.271-6.764A4.908,4.908,0,0,0,23.271,9.419Zm-1.705,4.115C20.234,15.7,17.219,19.345,12,19.345S3.766,15.7,2.434,13.534a2.918,2.918,0,0,1,0-3.068C3.766,8.3,6.781,4.655,12,4.655s8.234,3.641,9.566,5.811A2.918,2.918,0,0,1,21.566,13.534Z"/><path d="M12,7a5,5,0,1,0,5,5A5.006,5.006,0,0,0,12,7Zm0,8a3,3,0,1,1,3-3A3,3,0,0,1,12,15Z"/></svg>
											</span>
											<span class="storesuite-password-icon storesuite-password-icon-hide storesuite-hide" aria-hidden="true">
												<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24"><g id="_01_align_center" data-name="01 align center"><path d="M23.821,11.181v0a15.736,15.736,0,0,0-4.145-5.44l3.032-3.032L21.293,1.293,18,4.583A11.783,11.783,0,0,0,12,3C4.5,3,1.057,9.261.179,11.181a1.969,1.969,0,0,0,0,1.64,15.736,15.736,0,0,0,4.145,5.44L1.293,21.293l1.414,1.414L6,19.417A11.783,11.783,0,0,0,12,21c7.5,0,10.943-6.261,11.821-8.181A1.968,1.968,0,0,0,23.821,11.181ZM2,12.011C2.75,10.366,5.693,5,12,5a9.847,9.847,0,0,1,4.518,1.068L14.753,7.833a4.992,4.992,0,0,0-6.92,6.92L5.754,16.832A13.647,13.647,0,0,1,2,12.011ZM15,12a3,3,0,0,1-3,3,2.951,2.951,0,0,1-1.285-.3L14.7,10.715A2.951,2.951,0,0,1,15,12ZM9,12a3,3,0,0,1,3-3,2.951,2.951,0,0,1,1.285.3L9.3,13.285A2.951,2.951,0,0,1,9,12Zm3,7a9.847,9.847,0,0,1-4.518-1.068l1.765-1.765a4.992,4.992,0,0,0,6.92-6.92l2.078-2.078A13.584,13.584,0,0,1,22,12C21.236,13.657,18.292,19,12,19Z"/></g></svg>
											</span>
										</button>
									</div>
								</div>
							</div>
						</div>
						</div><!-- /password panel -->
					</div>
				</div>

				<?php do_action( 'woocommerce_edit_account_form' ); ?>

					<?php wp_nonce_field( '_storesuite_save_account_details_', 'storesuite_save_account_details_nonce' ); ?>
					<input type="hidden" name="action" value="storesuite_save_account_details" />

					<div class="storesuite-account-footer-actions">
						<button type="submit" class="my-storesuite-button" name="save_account_details" value="<?php esc_attr_e( 'Save All Changes', 'storesuite' ); ?>"><?php esc_html_e( 'Save All Changes', 'storesuite' ); ?></button>
					</div>

				<?php do_action( 'woocommerce_edit_account_form_end' ); ?>
			</form>

			<?php do_action( 'woocommerce_after_edit_account_form' ); ?>
		</main>
	</div>
</div>
<?php do_action( 'storesuite_dashboard_wrapper_end' ); ?>
