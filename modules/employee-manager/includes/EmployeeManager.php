<?php
/**
 * Employee Manager — employee CRUD over WP users + the employees table.
 *
 * @package StoreSuite
 */

namespace PluginizeLab\StoreSuite\Modules\EmployeeManager;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Creates and manages staff accounts. An "employee" is a WordPress user holding
 * one StoreSuite staff role, tracked by a row in the employees table (status,
 * who created them, last login).
 */
class EmployeeManager {

	/**
	 * List employees with their user + role data.
	 *
	 * @param array $args {
	 *     @type string $search Optional name/email search.
	 *     @type int    $paged  Page number (1-based).
	 *     @type int    $per_page Rows per page.
	 * }
	 * @return array{items:array,total:int,total_pages:int}
	 */
	public static function list( array $args = array() ) {
		global $wpdb;

		$table    = Installer::employees_table();
		$per_page = isset( $args['per_page'] ) ? max( 1, (int) $args['per_page'] ) : 20;
		$paged    = isset( $args['paged'] ) ? max( 1, (int) $args['paged'] ) : 1;
		$offset   = ( $paged - 1 ) * $per_page;
		$search   = isset( $args['search'] ) ? trim( (string) $args['search'] ) : '';

		// Collect employee user IDs from our table (source of truth for "who is
		// staff"), then hydrate via get_users so search/ordering reuse core.
		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$user_ids = $wpdb->get_col( "SELECT user_id FROM {$table}" );
		$user_ids = array_map( 'absint', $user_ids );

		if ( empty( $user_ids ) ) {
			return array(
				'items'       => array(),
				'total'       => 0,
				'total_pages' => 0,
			);
		}

		$query_args = array(
			'include' => $user_ids,
			'number'  => $per_page,
			'offset'  => $offset,
			'orderby' => 'display_name',
			'order'   => 'ASC',
			'fields'  => 'all',
		);

		if ( '' !== $search ) {
			$query_args['search']         = '*' . esc_attr( $search ) . '*';
			$query_args['search_columns'] = array( 'user_login', 'user_email', 'display_name', 'user_nicename' );
		}

		$query = new \WP_User_Query( $query_args );
		$items = array();

		foreach ( (array) $query->get_results() as $user ) {
			$items[] = self::to_row( $user );
		}

		$total = (int) $query->get_total();

		return array(
			'items'       => $items,
			'total'       => $total,
			'total_pages' => (int) ceil( $total / $per_page ),
		);
	}

	/**
	 * Assemble the display row for an employee user.
	 *
	 * @param \WP_User $user User object.
	 * @return array
	 */
	private static function to_row( $user ) {
		$record = self::get_record( $user->ID );
		$roles  = Roles::all();
		$role   = $record['role'] ?? (string) reset( $user->roles );

		return array(
			'user_id'     => $user->ID,
			'name'        => $user->display_name,
			'email'       => $user->user_email,
			'role'        => $role,
			'role_label'  => isset( $roles[ $role ] ) ? $roles[ $role ]['label'] : $role,
			'status'      => $record['status'] ?? 'active',
			'last_login'  => $record['last_login'] ?? null,
		);
	}

	/**
	 * Fetch the employees-table row for a user, or null.
	 *
	 * @param int $user_id User ID.
	 * @return array|null
	 */
	public static function get_record( $user_id ) {
		global $wpdb;
		$table = Installer::employees_table();

		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$table} WHERE user_id = %d", $user_id ), ARRAY_A );

		return is_array( $row ) ? $row : null;
	}

	/**
	 * Is this user a StoreSuite employee?
	 *
	 * @param int $user_id User ID.
	 * @return bool
	 */
	public static function is_employee( $user_id ) {
		return null !== self::get_record( (int) $user_id );
	}

	/**
	 * How many employees exist.
	 *
	 * @return int
	 */
	public static function count() {
		global $wpdb;
		$table = Installer::employees_table();
		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		return (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table}" );
	}

	/**
	 * Create a new employee: a WP user (if the email is new) plus a staff role
	 * and an employees-table row.
	 *
	 * @param array $data {
	 *     @type string $email      Required.
	 *     @type string $first_name Optional.
	 *     @type string $last_name  Optional.
	 *     @type string $role       Staff role slug. Required, must be a StoreSuite role.
	 * }
	 * @return int|\WP_Error New user ID, or error.
	 */
	public static function create( array $data ) {
		$email = isset( $data['email'] ) ? sanitize_email( $data['email'] ) : '';
		$role  = isset( $data['role'] ) ? sanitize_text_field( $data['role'] ) : '';

		if ( ! is_email( $email ) ) {
			return new \WP_Error( 'storesuite_invalid_email', __( 'A valid email address is required.', 'storesuite' ) );
		}

		if ( ! self::is_valid_role( $role ) ) {
			return new \WP_Error( 'storesuite_invalid_role', __( 'Please choose a valid staff role.', 'storesuite' ) );
		}

		$max = (int) Settings::value( 'max_employees' );
		if ( $max > 0 && self::count() >= $max ) {
			return new \WP_Error( 'storesuite_max_employees', __( 'The maximum number of employees has been reached.', 'storesuite' ) );
		}

		if ( email_exists( $email ) ) {
			return new \WP_Error( 'storesuite_email_exists', __( 'A user with that email already exists.', 'storesuite' ) );
		}

		$first = isset( $data['first_name'] ) ? sanitize_text_field( $data['first_name'] ) : '';
		$last  = isset( $data['last_name'] ) ? sanitize_text_field( $data['last_name'] ) : '';

		$display_name = trim( $first . ' ' . $last );
		if ( '' === $display_name ) {
			$display_name = $email;
		}

		// No password set — the welcome email sends a secure setup link.
		$user_id = wp_insert_user(
			array(
				'user_login'   => self::unique_login( $email ),
				'user_email'   => $email,
				'user_pass'    => wp_generate_password( 24, true, true ),
				'first_name'   => $first,
				'last_name'    => $last,
				'display_name' => $display_name,
				'role'         => $role,
			)
		);

		if ( is_wp_error( $user_id ) ) {
			return $user_id;
		}

		self::upsert_record( $user_id, $role, 'active', get_current_user_id() );

		/**
		 * Fires after a new employee is created.
		 *
		 * @param int    $user_id New user ID.
		 * @param string $role    Assigned role slug.
		 */
		do_action( 'storesuite_employee_created', $user_id, $role );

		return $user_id;
	}

	/**
	 * Update an existing employee's profile fields and/or role.
	 *
	 * @param int   $user_id Target user.
	 * @param array $data    Fields (first_name, last_name, role).
	 * @return true|\WP_Error
	 */
	public static function update( $user_id, array $data ) {
		$user_id = (int) $user_id;
		if ( ! self::is_employee( $user_id ) ) {
			return new \WP_Error( 'storesuite_not_employee', __( 'That employee could not be found.', 'storesuite' ) );
		}

		$fields = array( 'ID' => $user_id );
		if ( isset( $data['first_name'] ) ) {
			$fields['first_name'] = sanitize_text_field( $data['first_name'] );
		}
		if ( isset( $data['last_name'] ) ) {
			$fields['last_name'] = sanitize_text_field( $data['last_name'] );
		}
		if ( isset( $data['first_name'] ) || isset( $data['last_name'] ) ) {
			$user         = get_userdata( $user_id );
			$display_name = trim( ( $fields['first_name'] ?? $user->first_name ) . ' ' . ( $fields['last_name'] ?? $user->last_name ) );

			$fields['display_name'] = '' !== $display_name ? $display_name : $user->user_email;
		}

		$result = wp_update_user( $fields );
		if ( is_wp_error( $result ) ) {
			return $result;
		}

		if ( isset( $data['role'] ) ) {
			$role = sanitize_text_field( $data['role'] );
			if ( ! self::is_valid_role( $role ) ) {
				return new \WP_Error( 'storesuite_invalid_role', __( 'Please choose a valid staff role.', 'storesuite' ) );
			}
			self::set_role( $user_id, $role );
		}

		return true;
	}

	/**
	 * Suspend or reactivate an employee. A suspended employee keeps their WP
	 * account but is denied dashboard access.
	 *
	 * @param int    $user_id Target user.
	 * @param string $status  active|suspended.
	 * @return true|\WP_Error
	 */
	public static function set_status( $user_id, $status ) {
		$user_id = (int) $user_id;
		$status  = 'suspended' === $status ? 'suspended' : 'active';

		if ( ! self::is_employee( $user_id ) ) {
			return new \WP_Error( 'storesuite_not_employee', __( 'That employee could not be found.', 'storesuite' ) );
		}

		global $wpdb;
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$wpdb->update(
			Installer::employees_table(),
			array(
				'status'     => $status,
				'updated_at' => current_time( 'mysql' ),
			),
			array( 'user_id' => $user_id ),
			array( '%s', '%s' ),
			array( '%d' )
		);

		do_action( 'storesuite_employee_status_changed', $user_id, $status );

		return true;
	}

	/**
	 * Remove an employee. Keeps the WP user (they may be a customer too) but
	 * strips the staff role and deletes the employees-table row.
	 *
	 * @param int $user_id Target user.
	 * @return true|\WP_Error
	 */
	public static function delete( $user_id ) {
		$user_id = (int) $user_id;
		$record  = self::get_record( $user_id );
		if ( null === $record ) {
			return new \WP_Error( 'storesuite_not_employee', __( 'That employee could not be found.', 'storesuite' ) );
		}

		$user = get_userdata( $user_id );
		if ( $user && ! empty( $record['role'] ) ) {
			$user->remove_role( $record['role'] );
			// Ensure the account still has a role so it isn't orphaned.
			if ( empty( $user->roles ) ) {
				$user->add_role( 'customer' );
			}
		}

		global $wpdb;
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$wpdb->delete( Installer::employees_table(), array( 'user_id' => $user_id ), array( '%d' ) );

		do_action( 'storesuite_employee_deleted', $user_id );

		return true;
	}

	/**
	 * Record the moment an employee logs in.
	 *
	 * @param int $user_id User ID.
	 * @return void
	 */
	public static function touch_last_login( $user_id ) {
		if ( ! self::is_employee( $user_id ) ) {
			return;
		}
		global $wpdb;
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$wpdb->update(
			Installer::employees_table(),
			array( 'last_login' => current_time( 'mysql' ) ),
			array( 'user_id' => $user_id ),
			array( '%s' ),
			array( '%d' )
		);
	}

	/**
	 * Swap a user's StoreSuite staff role, removing any other staff role first.
	 *
	 * @param int    $user_id User ID.
	 * @param string $role    New staff role slug.
	 * @return void
	 */
	private static function set_role( $user_id, $role ) {
		$user = get_userdata( $user_id );
		if ( ! $user ) {
			return;
		}

		foreach ( $user->roles as $existing ) {
			if ( 0 === strpos( $existing, Roles::ROLE_PREFIX ) ) {
				$user->remove_role( $existing );
			}
		}
		$user->add_role( $role );

		global $wpdb;
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$wpdb->update(
			Installer::employees_table(),
			array(
				'role'       => $role,
				'updated_at' => current_time( 'mysql' ),
			),
			array( 'user_id' => $user_id ),
			array( '%s', '%s' ),
			array( '%d' )
		);
	}

	/**
	 * Insert or update the employees-table row.
	 *
	 * @param int    $user_id    User ID.
	 * @param string $role       Role slug.
	 * @param string $status     Status.
	 * @param int    $created_by Creator user ID.
	 * @return void
	 */
	private static function upsert_record( $user_id, $role, $status, $created_by ) {
		global $wpdb;
		$now = current_time( 'mysql' );

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$wpdb->replace(
			Installer::employees_table(),
			array(
				'user_id'    => $user_id,
				'role'       => $role,
				'status'     => $status,
				'created_by' => $created_by,
				'created_at' => $now,
				'updated_at' => $now,
			),
			array( '%d', '%s', '%s', '%d', '%s', '%s' )
		);
	}

	/**
	 * Build a unique user_login from an email local-part.
	 *
	 * @param string $email Email address.
	 * @return string
	 */
	private static function unique_login( $email ) {
		$base = sanitize_user( current( explode( '@', $email ) ), true );
		if ( '' === $base ) {
			$base = 'staff';
		}
		$login = $base;
		$i     = 1;
		while ( username_exists( $login ) ) {
			$login = $base . $i;
			++$i;
		}
		return $login;
	}

	/**
	 * Is $role one of StoreSuite's staff roles?
	 *
	 * @param string $role Role slug.
	 * @return bool
	 */
	public static function is_valid_role( $role ) {
		return '' !== $role && array_key_exists( $role, Roles::all() );
	}
}
