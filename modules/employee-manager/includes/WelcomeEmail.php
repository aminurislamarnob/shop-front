<?php
/**
 * Employee Manager — welcome / account-setup email.
 *
 * @package StoreSuite
 */

namespace PluginizeLab\StoreSuite\Modules\EmployeeManager;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Sends new employees a branded email with a secure, single-use link to set
 * their own password on the frontend login page (never wp-login.php).
 *
 * Wrapped in WooCommerce's email header/footer when the mailer is available so
 * it inherits store branding, without needing a full WC_Email subclass.
 */
class WelcomeEmail {

	/**
	 * Register hooks. Called from Module::boot().
	 *
	 * @return void
	 */
	public function register() {
		add_action( 'storesuite_employee_created', array( $this, 'send' ), 20, 2 );
	}

	/**
	 * Build and send the setup email for a user.
	 *
	 * @param int    $user_id New employee user ID.
	 * @param string $role    Assigned role (unused in the body, kept for hooks).
	 * @return bool Whether the mail was accepted for delivery.
	 */
	public function send( $user_id, $role = '' ) {
		unset( $role );

		$user = get_userdata( (int) $user_id );
		if ( ! $user ) {
			return false;
		}

		$setup_url = $this->setup_url( $user );
		$subject   = (string) Settings::value( 'welcome_email_subject' );
		$subject   = '' !== $subject ? $subject : __( 'Your team account is ready', 'storesuite' );

		$body = $this->body( $user, $setup_url );

		/**
		 * Filter the welcome email arguments before sending.
		 *
		 * @param array    $args    [ to, subject, body ].
		 * @param \WP_User $user    Recipient.
		 * @param string   $setup_url Password-setup URL.
		 */
		$args = apply_filters(
			'storesuite_employee_welcome_email',
			array(
				'to'      => $user->user_email,
				'subject' => $subject,
				'body'    => $body,
			),
			$user,
			$setup_url
		);

		$headers = array( 'Content-Type: text/html; charset=UTF-8' );
		$content = $this->wrap( $args['subject'], $args['body'] );

		return wp_mail( $args['to'], $args['subject'], $content, $headers );
	}

	/**
	 * Generate the single-use password-setup URL on the frontend login page.
	 *
	 * @param \WP_User $user Recipient.
	 * @return string
	 */
	private function setup_url( $user ) {
		$key = get_password_reset_key( $user );
		if ( is_wp_error( $key ) ) {
			return ( new LoginHandler() )->login_url();
		}

		return add_query_arg(
			array(
				'action' => 'setup',
				'key'    => rawurlencode( $key ),
				'login'  => rawurlencode( $user->user_login ),
			),
			( new LoginHandler() )->login_url()
		);
	}

	/**
	 * The email body (inner HTML).
	 *
	 * @param \WP_User $user      Recipient.
	 * @param string   $setup_url Setup link.
	 * @return string
	 */
	private function body( $user, $setup_url ) {
		$name  = $user->first_name ? $user->first_name : $user->display_name;
		$store = get_bloginfo( 'name' );

		$lines   = array();
		$lines[] = '<p>' . sprintf( /* translators: %s: employee first name */ esc_html__( 'Hi %s,', 'storesuite' ), esc_html( $name ) ) . '</p>';
		$lines[] = '<p>' . sprintf( /* translators: %s: store name */ esc_html__( 'An account has been created for you to help manage %s. Click the button below to choose your password and sign in.', 'storesuite' ), esc_html( $store ) ) . '</p>';
		$lines[] = '<p><a href="' . esc_url( $setup_url ) . '" style="display:inline-block;padding:10px 20px;background:#2d5bdb;color:#ffffff;border-radius:6px;text-decoration:none;">' . esc_html__( 'Set your password', 'storesuite' ) . '</a></p>';
		$lines[] = '<p>' . esc_html__( 'This link expires for security. If it stops working, ask your store administrator to resend it.', 'storesuite' ) . '</p>';

		return implode( "\n", $lines );
	}

	/**
	 * Wrap the body in WooCommerce's email template when available.
	 *
	 * @param string $subject Heading.
	 * @param string $body    Inner HTML.
	 * @return string
	 */
	private function wrap( $subject, $body ) {
		if ( function_exists( 'WC' ) && WC()->mailer() ) {
			return WC()->mailer()->wrap_message( $subject, $body );
		}
		return $body;
	}
}
