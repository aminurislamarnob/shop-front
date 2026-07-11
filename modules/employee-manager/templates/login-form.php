<?php
/**
 * Employee Manager — frontend login / password-setup form.
 *
 * Rendered by the [storesuite_employee_login] shortcode on a standalone page.
 * Expects $login_view ('login'|'setup') and $login_handler in scope.
 *
 * @package StoreSuite
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$storesuite_view = isset( $login_view ) ? $login_view : 'login';

// Already signed in → point staff at the dashboard.
if ( is_user_logged_in() && 'setup' !== $storesuite_view ) {
	$storesuite_dash = storesuite_get_navigation_url();
	?>
	<div class="storesuite-login-wrap">
		<div class="storesuite-card storesuite-card-with-header">
			<h3 class="storesuite-card-title"><?php esc_html_e( 'You are signed in', 'storesuite' ); ?></h3>
			<div class="storesuite-card-content">
				<?php if ( $storesuite_dash ) : ?>
					<p><a class="my-storesuite-button" href="<?php echo esc_url( $storesuite_dash ); ?>"><?php esc_html_e( 'Go to dashboard', 'storesuite' ); ?></a></p>
				<?php endif; ?>
				<p><a href="<?php echo esc_url( wp_logout_url( $login_handler->login_url() ) ); ?>"><?php esc_html_e( 'Log out', 'storesuite' ); ?></a></p>
			</div>
		</div>
	</div>
	<?php
	return;
}

// phpcs:disable WordPress.Security.NonceVerification.Recommended -- read-only status flags.
// The `login` query arg is a status word on the login view and the account
// username on the setup view; it is disambiguated by $storesuite_view.
$storesuite_login_status = 'setup' !== $storesuite_view && isset( $_GET['login'] ) ? sanitize_key( wp_unslash( $_GET['login'] ) ) : '';
$storesuite_setup_status = isset( $_GET['setup'] ) ? sanitize_key( wp_unslash( $_GET['setup'] ) ) : '';
$storesuite_setup_err    = isset( $_GET['err'] ) ? sanitize_key( wp_unslash( $_GET['err'] ) ) : '';
$storesuite_setup_key    = isset( $_GET['key'] ) ? sanitize_text_field( wp_unslash( $_GET['key'] ) ) : '';
$storesuite_setup_login  = 'setup' === $storesuite_view && isset( $_GET['login'] ) ? sanitize_text_field( wp_unslash( $_GET['login'] ) ) : '';
// phpcs:enable WordPress.Security.NonceVerification.Recommended
?>
<div class="storesuite-login-wrap">
	<div class="storesuite-card storesuite-card-with-header">
		<?php if ( 'setup' === $storesuite_view ) : ?>
			<h3 class="storesuite-card-title"><?php esc_html_e( 'Choose your password', 'storesuite' ); ?></h3>
			<div class="storesuite-card-content">
				<?php if ( 'mismatch' === $storesuite_setup_err ) : ?>
					<p class="storesuite-field-error"><?php esc_html_e( 'Passwords did not match. Please try again.', 'storesuite' ); ?></p>
				<?php endif; ?>
				<form method="post" action="">
					<?php wp_nonce_field( 'storesuite_password_setup', 'storesuite_setup_nonce' ); ?>
					<input type="hidden" name="key" value="<?php echo esc_attr( $storesuite_setup_key ); ?>" />
					<input type="hidden" name="login" value="<?php echo esc_attr( $storesuite_setup_login ); ?>" />
					<div class="storesuite-form-group">
						<label for="storesuite-pass1"><?php esc_html_e( 'New password', 'storesuite' ); ?> <span class="req">*</span></label>
						<input type="password" class="storesuite-form-control" id="storesuite-pass1" name="pass1" required autocomplete="new-password" />
					</div>
					<div class="storesuite-form-group">
						<label for="storesuite-pass2"><?php esc_html_e( 'Confirm password', 'storesuite' ); ?> <span class="req">*</span></label>
						<input type="password" class="storesuite-form-control" id="storesuite-pass2" name="pass2" required autocomplete="new-password" />
					</div>
					<button type="submit" class="my-storesuite-button"><?php esc_html_e( 'Set password & continue', 'storesuite' ); ?></button>
				</form>
			</div>
		<?php else : ?>
			<h3 class="storesuite-card-title"><?php esc_html_e( 'Team login', 'storesuite' ); ?></h3>
			<div class="storesuite-card-content">
				<?php if ( 'done' === $storesuite_setup_status ) : ?>
					<p class="storesuite-text-success"><?php esc_html_e( 'Password set. You can now sign in.', 'storesuite' ); ?></p>
				<?php elseif ( 'expired' === $storesuite_setup_status ) : ?>
					<p class="storesuite-field-error"><?php esc_html_e( 'That setup link has expired. Ask your administrator to resend it.', 'storesuite' ); ?></p>
				<?php endif; ?>
				<?php if ( 'failed' === $storesuite_login_status ) : ?>
					<p class="storesuite-field-error"><?php esc_html_e( 'Invalid username or password.', 'storesuite' ); ?></p>
				<?php elseif ( 'throttled' === $storesuite_login_status ) : ?>
					<p class="storesuite-field-error"><?php esc_html_e( 'Too many attempts. Please try again later.', 'storesuite' ); ?></p>
				<?php endif; ?>
				<form method="post" action="">
					<?php wp_nonce_field( \PluginizeLab\StoreSuite\Modules\EmployeeManager\LoginHandler::LOGIN_NONCE, 'storesuite_login_nonce' ); ?>
					<div class="storesuite-form-group">
						<label for="storesuite-username"><?php esc_html_e( 'Username or email', 'storesuite' ); ?> <span class="req">*</span></label>
						<input type="text" class="storesuite-form-control" id="storesuite-username" name="username" required autocomplete="username" />
					</div>
					<div class="storesuite-form-group">
						<label for="storesuite-password"><?php esc_html_e( 'Password', 'storesuite' ); ?> <span class="req">*</span></label>
						<input type="password" class="storesuite-form-control" id="storesuite-password" name="password" required autocomplete="current-password" />
					</div>
					<div class="storesuite-form-switch storesuite-form-group">
						<input type="checkbox" class="storesuite-form-control" id="storesuite-remember" name="remember" value="1" />
						<label for="storesuite-remember"><?php esc_html_e( 'Remember me', 'storesuite' ); ?></label>
					</div>
					<!-- Honeypot: hidden from users, tempting to bots. -->
					<div style="position:absolute;left:-9999px;" aria-hidden="true">
						<label for="storesuite-website"><?php esc_html_e( 'Leave this field empty', 'storesuite' ); ?></label>
						<input type="text" id="storesuite-website" name="website" tabindex="-1" autocomplete="off" />
					</div>
					<button type="submit" class="my-storesuite-button"><?php esc_html_e( 'Sign in', 'storesuite' ); ?></button>
				</form>
				<p class="storesuite-form-text">
					<a href="<?php echo esc_url( wp_lostpassword_url() ); ?>"><?php esc_html_e( 'Lost your password?', 'storesuite' ); ?></a>
				</p>
			</div>
		<?php endif; ?>
	</div>
</div>
