<?php
/**
 * Employee Manager — frontend login & password setup.
 *
 * @package StoreSuite
 */

namespace PluginizeLab\StoreSuite\Modules\EmployeeManager;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Provides a frontend-only login experience so staff never touch wp-login.php:
 *  - a `[storesuite_employee_login]` shortcode rendering the login form;
 *  - an AJAX login (`storesuite_employee_login`) over wp_signon with throttling;
 *  - a secure password-setup flow reached from the welcome email
 *    (`?action=setup&key=…&login=…`);
 *  - redirect + admin-block wiring so staff land on the dashboard.
 */
class LoginHandler {

	const PAGE_OPTION    = 'storesuite_employee_login_page_id';
	const LOGIN_NONCE    = 'storesuite_employee_login';
	const THROTTLE_MAX   = 6;
	const THROTTLE_BLOCK = 900; // 15 minutes.

	/**
	 * Register hooks. Called from Module::boot().
	 *
	 * @return void
	 */
	public function register() {
		add_shortcode( 'storesuite_employee_login', array( $this, 'render' ) );
		add_action( 'wp_ajax_nopriv_storesuite_employee_login', array( $this, 'ajax_login' ) );
		add_action( 'wp_ajax_storesuite_employee_login', array( $this, 'ajax_login' ) );

		// Send non-logged-in dashboard visitors to our login page.
		add_filter( 'storesuite_login_redirect_url', array( $this, 'login_url' ) );

		// Keep staff roles out of wp-admin.
		if ( (bool) Settings::value( 'lock_wp_admin' ) ) {
			add_filter( 'storesuite_blocked_admin_roles', array( $this, 'blocked_roles' ) );
		}

		// Handle login + password-setup submissions early (before output).
		add_action( 'template_redirect', array( $this, 'maybe_handle_login' ) );
		add_action( 'template_redirect', array( $this, 'maybe_handle_password_setup' ) );
	}

	/**
	 * Process a posted login form (works without JavaScript). Verifies nonce,
	 * throttles by IP, signs in, and redirects to the dashboard.
	 *
	 * @return void
	 */
	public function maybe_handle_login() {
		if ( ! isset( $_POST['storesuite_login_nonce'] ) ) {
			return;
		}

		if ( ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['storesuite_login_nonce'] ) ), self::LOGIN_NONCE ) ) {
			return;
		}

		// Honeypot.
		if ( ! empty( $_POST['website'] ) ) {
			return;
		}

		$throttle = 'storesuite_login_throttle_' . md5( self::client_ip() );
		if ( (int) get_transient( $throttle ) >= self::THROTTLE_MAX ) {
			wp_safe_redirect( add_query_arg( 'login', 'throttled', $this->login_url() ) );
			exit;
		}

		$username = isset( $_POST['username'] ) ? sanitize_text_field( wp_unslash( $_POST['username'] ) ) : '';
		$password = isset( $_POST['password'] ) ? (string) wp_unslash( $_POST['password'] ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- password.
		$remember = ! empty( $_POST['remember'] );

		$user = wp_signon(
			array(
				'user_login'    => $username,
				'user_password' => $password,
				'remember'      => $remember,
			),
			is_ssl()
		);

		if ( is_wp_error( $user ) ) {
			$attempts = (int) get_transient( $throttle );
			set_transient( $throttle, $attempts + 1, self::THROTTLE_BLOCK );
			wp_safe_redirect( add_query_arg( 'login', 'failed', $this->login_url() ) );
			exit;
		}

		delete_transient( $throttle );

		$redirect = ( user_can( $user, 'storesuite_access_dashboard' ) || user_can( $user, 'manage_woocommerce' ) )
			? storesuite_get_navigation_url()
			: wc_get_page_permalink( 'myaccount' );

		wp_safe_redirect( $redirect );
		exit;
	}

	/**
	 * Create the login page on activation (idempotent).
	 *
	 * @return void
	 */
	public static function create_page() {
		$existing = (int) get_option( self::PAGE_OPTION );
		if ( $existing && 'page' === get_post_type( $existing ) && 'trash' !== get_post_status( $existing ) ) {
			return;
		}

		$page_id = wp_insert_post(
			array(
				'post_title'   => __( 'Team Login', 'storesuite' ),
				'post_name'    => 'team-login',
				'post_content' => '[storesuite_employee_login]',
				'post_status'  => 'publish',
				'post_type'    => 'page',
			)
		);

		if ( $page_id && ! is_wp_error( $page_id ) ) {
			update_option( self::PAGE_OPTION, (int) $page_id );
		}
	}

	/**
	 * The login page URL, falling back to My Account if the page is missing.
	 *
	 * @param string $default_url Incoming default URL.
	 * @return string
	 */
	public function login_url( $default_url = '' ) {
		$page_id = (int) get_option( self::PAGE_OPTION );
		$url     = $page_id ? get_permalink( $page_id ) : '';

		if ( $url ) {
			return $url;
		}

		return $default_url ? $default_url : wc_get_page_permalink( 'myaccount' );
	}

	/**
	 * Add the staff roles to the wp-admin blocked list.
	 *
	 * @param string[] $roles Current blocked roles.
	 * @return string[]
	 */
	public function blocked_roles( $roles ) {
		$roles = is_array( $roles ) ? $roles : array();
		return array_values( array_unique( array_merge( $roles, array_keys( Roles::all() ) ) ) );
	}

	/**
	 * Shortcode: render the login form, the password-setup form, or a
	 * "you're signed in" panel.
	 *
	 * @return string
	 */
	public function render() {
		ob_start();

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only view branch; the setup key is validated in the template.
		$action = isset( $_GET['action'] ) ? sanitize_key( wp_unslash( $_GET['action'] ) ) : '';

		$template = __DIR__ . '/../templates/login-form.php';
		if ( file_exists( $template ) ) {
			$login_handler = $this;
			$login_view    = 'setup' === $action ? 'setup' : 'login';
			include $template;
		}

		return ob_get_clean();
	}

	/**
	 * AJAX login handler (nopriv). Verifies nonce, throttles by IP, signs the
	 * user in, and returns the dashboard redirect URL.
	 *
	 * @return void
	 */
	public function ajax_login() {
		if ( ! isset( $_POST['security'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['security'] ) ), self::LOGIN_NONCE ) ) {
			wp_send_json_error( array( 'error' => __( 'Security check failed. Please reload and try again.', 'storesuite' ) ) );
		}

		// Honeypot: bots fill hidden fields.
		if ( ! empty( $_POST['website'] ) ) {
			wp_send_json_error( array( 'error' => __( 'Login failed.', 'storesuite' ) ) );
		}

		$key = 'storesuite_login_throttle_' . md5( self::client_ip() );
		if ( (int) get_transient( $key ) >= self::THROTTLE_MAX ) {
			wp_send_json_error( array( 'error' => __( 'Too many attempts. Please try again later.', 'storesuite' ) ) );
		}

		$username = isset( $_POST['username'] ) ? sanitize_text_field( wp_unslash( $_POST['username'] ) ) : '';
		$password = isset( $_POST['password'] ) ? (string) wp_unslash( $_POST['password'] ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- passwords must not be altered.
		$remember = ! empty( $_POST['remember'] );

		$user = wp_signon(
			array(
				'user_login'    => $username,
				'user_password' => $password,
				'remember'      => $remember,
			),
			is_ssl()
		);

		if ( is_wp_error( $user ) ) {
			$attempts = (int) get_transient( $key );
			set_transient( $key, $attempts + 1, self::THROTTLE_BLOCK );
			wp_send_json_error( array( 'error' => __( 'Invalid username or password.', 'storesuite' ) ) );
		}

		delete_transient( $key );

		// Route staff to the dashboard, others to My Account.
		$redirect = ( user_can( $user, 'storesuite_access_dashboard' ) || user_can( $user, 'manage_woocommerce' ) )
			? storesuite_get_navigation_url()
			: wc_get_page_permalink( 'myaccount' );

		wp_send_json_success( array( 'redirect' => $redirect ) );
	}

	/**
	 * Process a password-setup form submission from the welcome-email link.
	 *
	 * @return void
	 */
	public function maybe_handle_password_setup() {
		if ( ! isset( $_POST['storesuite_setup_nonce'] ) ) {
			return;
		}

		if ( ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['storesuite_setup_nonce'] ) ), 'storesuite_password_setup' ) ) {
			return;
		}

		$login = isset( $_POST['login'] ) ? sanitize_text_field( wp_unslash( $_POST['login'] ) ) : '';
		$key   = isset( $_POST['key'] ) ? sanitize_text_field( wp_unslash( $_POST['key'] ) ) : '';
		$pass1 = isset( $_POST['pass1'] ) ? (string) wp_unslash( $_POST['pass1'] ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- password.
		$pass2 = isset( $_POST['pass2'] ) ? (string) wp_unslash( $_POST['pass2'] ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- password.

		$user = check_password_reset_key( $key, $login );
		if ( is_wp_error( $user ) ) {
			wp_safe_redirect( add_query_arg( 'setup', 'expired', $this->login_url() ) );
			exit;
		}

		if ( '' === $pass1 || $pass1 !== $pass2 ) {
			wp_safe_redirect(
				add_query_arg(
					array(
						'action' => 'setup',
						'key'    => rawurlencode( $key ),
						'login'  => rawurlencode( $login ),
						'err'    => 'mismatch',
					),
					$this->login_url()
				)
			);
			exit;
		}

		reset_password( $user, $pass1 );

		wp_safe_redirect( add_query_arg( 'setup', 'done', $this->login_url() ) );
		exit;
	}

	/**
	 * Best-effort client IP.
	 *
	 * @return string
	 */
	private static function client_ip() {
		return isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
	}

	/**
	 * Remove the login page on uninstall.
	 *
	 * @return void
	 */
	public static function remove_page() {
		$page_id = (int) get_option( self::PAGE_OPTION );
		if ( $page_id ) {
			wp_delete_post( $page_id, true );
		}
		delete_option( self::PAGE_OPTION );
	}
}
