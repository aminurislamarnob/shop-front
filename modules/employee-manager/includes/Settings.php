<?php
/**
 * Employee Manager — settings helper.
 *
 * @package StoreSuite
 */

namespace PluginizeLab\StoreSuite\Modules\EmployeeManager;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Owns the `storesuite_employee_manager_settings` option. Schema-driven: the
 * same schema powers the generic Modules → Configure form, the defaults, and
 * sanitisation.
 */
class Settings {

	const OPTION_KEY = 'storesuite_employee_manager_settings';

	/**
	 * Describe every configurable field.
	 *
	 * @return array
	 */
	public static function get_schema() {
		return array(
			'enable_activity_log'   => array(
				'type'        => 'toggle',
				'label'       => __( 'Enable activity log', 'storesuite' ),
				'description' => __( 'Record dashboard actions (product/order changes, logins) per employee.', 'storesuite' ),
				'default'     => true,
			),
			'log_retention_days'    => array(
				'type'        => 'number',
				'label'       => __( 'Activity log retention (days)', 'storesuite' ),
				'description' => __( 'Older log entries are purged daily. Use 0 to keep entries forever.', 'storesuite' ),
				'default'     => 90,
				'min'         => 0,
				'max'         => 3650,
			),
			'max_employees'         => array(
				'type'        => 'number',
				'label'       => __( 'Maximum employees', 'storesuite' ),
				'description' => __( 'Hard cap on staff accounts. Use 0 for unlimited.', 'storesuite' ),
				'default'     => 0,
				'min'         => 0,
				'max'         => 10000,
			),
			'welcome_email_subject' => array(
				'type'        => 'text',
				'label'       => __( 'Welcome email subject', 'storesuite' ),
				'description' => __( 'Subject line for the account-setup email sent to new employees.', 'storesuite' ),
				'default'     => __( 'Your team account is ready', 'storesuite' ),
			),
			'lock_wp_admin'         => array(
				'type'        => 'toggle',
				'label'       => __( 'Lock employees out of wp-admin', 'storesuite' ),
				'description' => __( 'Redirect staff roles away from the WordPress admin area to the frontend dashboard.', 'storesuite' ),
				'default'     => true,
			),
		);
	}

	/**
	 * Defaults pulled from the schema.
	 *
	 * @return array
	 */
	public static function get_defaults() {
		$defaults = array();
		foreach ( self::get_schema() as $key => $field ) {
			$defaults[ $key ] = $field['default'] ?? '';
		}
		return $defaults;
	}

	/**
	 * Current settings merged with defaults.
	 *
	 * @return array
	 */
	public static function get() {
		$stored = get_option( self::OPTION_KEY, array() );
		if ( ! is_array( $stored ) ) {
			$stored = array();
		}
		return array_merge( self::get_defaults(), $stored );
	}

	/**
	 * Read a single setting.
	 *
	 * @param string $key Setting key.
	 * @return mixed
	 */
	public static function value( $key ) {
		$all = self::get();
		return $all[ $key ] ?? null;
	}

	/**
	 * Sanitise input against the schema and persist.
	 *
	 * @param array $data Raw input.
	 * @return array Canonical post-save values.
	 */
	public static function update( array $data ) {
		$schema = self::get_schema();
		$clean  = self::get();

		foreach ( $schema as $key => $field ) {
			if ( ! array_key_exists( $key, $data ) ) {
				continue;
			}
			$clean[ $key ] = self::sanitize_field( $data[ $key ], $field );
		}

		update_option( self::OPTION_KEY, $clean );
		return $clean;
	}

	/**
	 * Coerce a value to its schema type.
	 *
	 * @param mixed $value Raw value.
	 * @param array $field Schema entry.
	 * @return mixed
	 */
	private static function sanitize_field( $value, array $field ) {
		$type = $field['type'] ?? 'text';

		switch ( $type ) {
			case 'toggle':
				return (bool) $value;

			case 'number':
				$value = (int) $value;
				if ( isset( $field['min'] ) ) {
					$value = max( (int) $field['min'], $value );
				}
				if ( isset( $field['max'] ) ) {
					$value = min( (int) $field['max'], $value );
				}
				return $value;

			case 'text':
			default:
				return sanitize_text_field( (string) $value );
		}
	}
}
