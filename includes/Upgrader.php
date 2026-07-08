<?php

namespace PluginizeLab\StoreSuite;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Plugin data upgrader.
 *
 * Runs one-shot, idempotent data migrations when the stored DB version
 * is behind the plugin version. The DB version is stamped only after a
 * successful run, so a failure mid-migration can be retried on the next
 * load.
 */
class Upgrader {

	/**
	 * Option name used to record the DB schema version already migrated to.
	 *
	 * @var string
	 */
	const DB_VERSION_OPTION = 'storesuite_db_version';

	/**
	 * Compare the stored DB version against the running plugin version and
	 * dispatch any outstanding migrations.
	 *
	 * @return void
	 */
	public function maybe_upgrade() {
		$stored  = get_option( self::DB_VERSION_OPTION, '0' );
		$current = STORESUITE_PLUGIN_VERSION;

		if ( version_compare( $stored, $current, '>=' ) ) {
			return;
		}

		if ( version_compare( $stored, '1.1.0', '<' ) ) {
			$this->migrate_to_1_1_0();
		}

		if ( version_compare( $stored, '1.1.4', '<' ) ) {
			$this->migrate_to_1_1_4();
		}

		update_option( self::DB_VERSION_OPTION, $current );
	}

	/**
	 * Schedule a rewrite-rules flush for the attribute endpoints added in 1.1.0.
	 *
	 * The activation hook does not fire on plugin updates, so existing installs
	 * would otherwise 404 on the new `attributes`, `add-new-attribute`,
	 * `edit-attribute`, and `attribute-terms` pages until permalinks were saved
	 * by hand. We reuse the existing `storesuite_flush_rewrite_rules` flag, which
	 * StoreSuite::maybe_flush_rewrite_rules() consumes on init (priority 999),
	 * after Rewrites::add_endpoints() has registered the new rules.
	 *
	 * Idempotent: reruns are safe because the outer version gate prevents
	 * re-entry, and the flag is a simple set/delete.
	 *
	 * @return void
	 */
	private function migrate_to_1_1_0() {
		update_option( 'storesuite_flush_rewrite_rules', 1 );
	}

	/**
	 * Schedule a rewrite-rules flush for the attribute terms pagination rule
	 * added in 1.1.4.
	 *
	 * Without a flush, existing installs would 404 on
	 * `attribute-terms/page/N` until permalinks were saved by hand. Reuses the
	 * same `storesuite_flush_rewrite_rules` flag consumed on init.
	 *
	 * @return void
	 */
	private function migrate_to_1_1_4() {
		update_option( 'storesuite_flush_rewrite_rules', 1 );
	}
}
