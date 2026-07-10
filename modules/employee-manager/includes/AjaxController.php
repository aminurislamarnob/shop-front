<?php
/**
 * Employee Manager — AJAX endpoints for the staff screens.
 *
 * @package StoreSuite
 */

namespace PluginizeLab\StoreSuite\Modules\EmployeeManager;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers the `wp_ajax_storesuite_*` handlers that power the Team screens.
 * Every handler verifies the module nonce and the manage-employees capability.
 */
class AjaxController {

	const NONCE = 'storesuite_employee_manager';

	/**
	 * Hook the handlers. Called from Module::boot().
	 *
	 * @return void
	 */
	public function register() {
		add_action( 'wp_ajax_storesuite_add_employee', array( $this, 'add_employee' ) );
		add_action( 'wp_ajax_storesuite_update_employee', array( $this, 'update_employee' ) );
		add_action( 'wp_ajax_storesuite_suspend_employee', array( $this, 'suspend_employee' ) );
		add_action( 'wp_ajax_storesuite_delete_employee', array( $this, 'delete_employee' ) );
		add_action( 'wp_ajax_storesuite_save_custom_role', array( $this, 'save_custom_role' ) );
		add_action( 'wp_ajax_storesuite_delete_custom_role', array( $this, 'delete_custom_role' ) );
		add_action( 'wp_ajax_storesuite_resend_welcome_email', array( $this, 'resend_welcome_email' ) );
	}

	/**
	 * Shared guard: verify nonce + capability, or send a JSON error and die.
	 *
	 * @return void
	 */
	private function guard() {
		if ( ! isset( $_POST['security'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['security'] ) ), self::NONCE ) ) {
			wp_send_json_error( array( 'error' => __( 'Security check failed. Please reload and try again.', 'storesuite' ) ) );
		}

		if ( ! storesuite_current_user_can( 'manage_employees' ) ) {
			wp_send_json_error( array( 'error' => __( 'You do not have permission to manage employees.', 'storesuite' ) ) );
		}
	}

	/**
	 * Return a WP_Error as a JSON error response.
	 *
	 * @param \WP_Error $error Error.
	 * @return void
	 */
	private function fail( \WP_Error $error ) {
		wp_send_json_error( array( 'error' => $error->get_error_message() ) );
	}

	/**
	 * POST handler — create an employee.
	 *
	 * @return void
	 */
	public function add_employee() {
		$this->guard();

		$result = EmployeeManager::create(
			array(
				'email'      => isset( $_POST['email'] ) ? wp_unslash( $_POST['email'] ) : '', // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- sanitized in create().
				'first_name' => isset( $_POST['first_name'] ) ? wp_unslash( $_POST['first_name'] ) : '', // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- sanitized in create().
				'last_name'  => isset( $_POST['last_name'] ) ? wp_unslash( $_POST['last_name'] ) : '', // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- sanitized in create().
				'role'       => isset( $_POST['role'] ) ? sanitize_text_field( wp_unslash( $_POST['role'] ) ) : '',
			)
		);

		if ( is_wp_error( $result ) ) {
			$this->fail( $result );
		}

		wp_send_json_success(
			array(
				'message' => __( 'Employee added. A setup email has been sent.', 'storesuite' ),
				'user_id' => $result,
			)
		);
	}

	/**
	 * POST handler — update an employee.
	 *
	 * @return void
	 */
	public function update_employee() {
		$this->guard();

		$user_id = isset( $_POST['user_id'] ) ? absint( wp_unslash( $_POST['user_id'] ) ) : 0;

		$result = EmployeeManager::update(
			$user_id,
			array(
				'first_name' => isset( $_POST['first_name'] ) ? wp_unslash( $_POST['first_name'] ) : '', // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- sanitized in update().
				'last_name'  => isset( $_POST['last_name'] ) ? wp_unslash( $_POST['last_name'] ) : '', // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- sanitized in update().
				'role'       => isset( $_POST['role'] ) ? sanitize_text_field( wp_unslash( $_POST['role'] ) ) : '',
			)
		);

		if ( is_wp_error( $result ) ) {
			$this->fail( $result );
		}

		wp_send_json_success( array( 'message' => __( 'Employee updated.', 'storesuite' ) ) );
	}

	/**
	 * POST handler — suspend/reactivate an employee.
	 *
	 * @return void
	 */
	public function suspend_employee() {
		$this->guard();

		$user_id = isset( $_POST['user_id'] ) ? absint( wp_unslash( $_POST['user_id'] ) ) : 0;
		$status  = isset( $_POST['status'] ) ? sanitize_text_field( wp_unslash( $_POST['status'] ) ) : 'suspended';

		$result = EmployeeManager::set_status( $user_id, $status );
		if ( is_wp_error( $result ) ) {
			$this->fail( $result );
		}

		wp_send_json_success( array( 'message' => __( 'Employee status updated.', 'storesuite' ) ) );
	}

	/**
	 * POST handler — delete an employee.
	 *
	 * @return void
	 */
	public function delete_employee() {
		$this->guard();

		$user_id = isset( $_POST['user_id'] ) ? absint( wp_unslash( $_POST['user_id'] ) ) : 0;

		$result = EmployeeManager::delete( $user_id );
		if ( is_wp_error( $result ) ) {
			$this->fail( $result );
		}

		wp_send_json_success( array( 'message' => __( 'Employee removed.', 'storesuite' ) ) );
	}

	/**
	 * POST handler — create/update a custom role.
	 *
	 * @return void
	 */
	public function save_custom_role() {
		$this->guard();

		$label = isset( $_POST['label'] ) ? sanitize_text_field( wp_unslash( $_POST['label'] ) ) : '';
		$slug  = isset( $_POST['slug'] ) ? sanitize_text_field( wp_unslash( $_POST['slug'] ) ) : '';
		$areas = isset( $_POST['areas'] ) ? array_map( 'sanitize_text_field', (array) wp_unslash( $_POST['areas'] ) ) : array();

		$result = Roles::save_custom_role( $label, $areas, $slug );
		if ( is_wp_error( $result ) ) {
			$this->fail( $result );
		}

		wp_send_json_success(
			array(
				'message' => __( 'Role saved.', 'storesuite' ),
				'slug'    => $result,
			)
		);
	}

	/**
	 * POST handler — delete a custom role.
	 *
	 * @return void
	 */
	public function delete_custom_role() {
		$this->guard();

		$slug = isset( $_POST['slug'] ) ? sanitize_text_field( wp_unslash( $_POST['slug'] ) ) : '';

		$result = Roles::delete_custom_role( $slug );
		if ( is_wp_error( $result ) ) {
			$this->fail( $result );
		}

		wp_send_json_success( array( 'message' => __( 'Role deleted.', 'storesuite' ) ) );
	}

	/**
	 * POST handler — resend the account-setup email to an employee.
	 *
	 * @return void
	 */
	public function resend_welcome_email() {
		$this->guard();

		$user_id = isset( $_POST['user_id'] ) ? absint( wp_unslash( $_POST['user_id'] ) ) : 0;

		if ( ! EmployeeManager::is_employee( $user_id ) ) {
			$this->fail( new \WP_Error( 'storesuite_not_employee', __( 'That employee could not be found.', 'storesuite' ) ) );
		}

		$sent = ( new WelcomeEmail() )->send( $user_id );

		if ( ! $sent ) {
			$this->fail( new \WP_Error( 'storesuite_email_failed', __( 'The email could not be sent. Check your site email configuration.', 'storesuite' ) ) );
		}

		wp_send_json_success( array( 'message' => __( 'Setup email resent.', 'storesuite' ) ) );
	}
}
