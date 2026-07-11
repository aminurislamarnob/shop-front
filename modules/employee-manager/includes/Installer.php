<?php
/**
 * Employee Manager — schema installer.
 *
 * @package StoreSuite
 */

namespace PluginizeLab\StoreSuite\Modules\EmployeeManager;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Owns the module's two tables:
 *   {prefix}storesuite_employees     — one row per staff account.
 *   {prefix}storesuite_activity_log  — audit trail of dashboard actions.
 *
 * Uses dbDelta, which is additive, so deactivation is non-destructive (data
 * survives a toggle). Tables are dropped only from the module's uninstall().
 */
class Installer {

	const SCHEMA_VERSION        = '1.0.0';
	const SCHEMA_VERSION_OPTION = 'storesuite_employee_manager_db_version';

	/**
	 * Employees table name (with prefix applied).
	 *
	 * @return string
	 */
	public static function employees_table() {
		global $wpdb;
		return $wpdb->prefix . 'storesuite_employees';
	}

	/**
	 * Activity log table name (with prefix applied).
	 *
	 * @return string
	 */
	public static function activity_log_table() {
		global $wpdb;
		return $wpdb->prefix . 'storesuite_activity_log';
	}

	/**
	 * Create or upgrade the schema. Idempotent.
	 *
	 * @return void
	 */
	public static function install() {
		global $wpdb;

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		$charset_collate = $wpdb->get_charset_collate();
		$employees       = self::employees_table();
		$activity_log    = self::activity_log_table();

		// dbDelta is picky: two spaces after PRIMARY KEY, lowercase types, no
		// trailing commas. Do not reformat.
		$employees_sql = "CREATE TABLE {$employees} (
			id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			user_id BIGINT(20) UNSIGNED NOT NULL,
			role VARCHAR(64) NOT NULL DEFAULT '',
			status VARCHAR(20) NOT NULL DEFAULT 'active',
			created_by BIGINT(20) UNSIGNED NOT NULL DEFAULT 0,
			last_login DATETIME NULL DEFAULT NULL,
			created_at DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
			updated_at DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
			PRIMARY KEY  (id),
			UNIQUE KEY user_id (user_id),
			KEY status (status)
		) {$charset_collate};";

		$activity_log_sql = "CREATE TABLE {$activity_log} (
			id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			user_id BIGINT(20) UNSIGNED NOT NULL DEFAULT 0,
			action VARCHAR(64) NOT NULL DEFAULT '',
			object_type VARCHAR(32) NOT NULL DEFAULT '',
			object_id BIGINT(20) UNSIGNED NOT NULL DEFAULT 0,
			summary VARCHAR(255) NOT NULL DEFAULT '',
			meta LONGTEXT NULL,
			ip VARCHAR(45) NOT NULL DEFAULT '',
			created_at DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
			PRIMARY KEY  (id),
			KEY user_id (user_id),
			KEY action (action),
			KEY object_id (object_id),
			KEY created_at (created_at)
		) {$charset_collate};";

		dbDelta( $employees_sql );
		dbDelta( $activity_log_sql );

		update_option( self::SCHEMA_VERSION_OPTION, self::SCHEMA_VERSION );
	}

	/**
	 * Run dbDelta only when the stored schema version is behind. Called on
	 * every boot so a plugin update can ship a schema change without a re-toggle.
	 *
	 * @return void
	 */
	public static function maybe_upgrade() {
		$installed = get_option( self::SCHEMA_VERSION_OPTION );

		if ( version_compare( (string) $installed, self::SCHEMA_VERSION, '<' ) ) {
			self::install();
		}
	}

	/**
	 * Drop both tables and forget the schema version. Called only from the
	 * module's uninstall() — never on deactivation.
	 *
	 * @return void
	 */
	public static function uninstall() {
		global $wpdb;

		$employees    = self::employees_table();
		$activity_log = self::activity_log_table();

		// Identifiers are built from the trusted $wpdb prefix + hard-coded
		// suffixes; they cannot be parameterised.
		$wpdb->query( "DROP TABLE IF EXISTS {$employees}" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.NoCaching
		$wpdb->query( "DROP TABLE IF EXISTS {$activity_log}" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.NoCaching

		delete_option( self::SCHEMA_VERSION_OPTION );
	}
}
