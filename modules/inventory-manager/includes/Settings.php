<?php
/**
 * Inventory Manager — settings helper.
 *
 * @package StoreSuite
 */

namespace PluginizeLab\StoreSuite\Modules\InventoryManager;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Owns the `storesuite_inventory_manager_settings` option.
 */
class Settings {

	const OPTION_KEY = 'storesuite_inventory_manager_settings';

	/**
	 * @return array
	 */
	public static function get_schema() {
		return array(
			'enable_alerts'               => array(
				'type'        => 'toggle',
				'label'       => __( 'Low-stock email alerts', 'storesuite' ),
				'description' => __( 'Email a notification when a product drops to or below its low-stock threshold.', 'storesuite' ),
				'default'     => true,
			),
			'alert_mode'                  => array(
				'type'        => 'select',
				'label'       => __( 'Alert delivery', 'storesuite' ),
				'description' => __( 'Send an email immediately per product, or one daily digest.', 'storesuite' ),
				'default'     => 'immediate',
				'options'     => array(
					'immediate' => __( 'Immediate', 'storesuite' ),
					'daily'     => __( 'Daily digest', 'storesuite' ),
				),
			),
			'alert_recipients'            => array(
				'type'        => 'text',
				'label'       => __( 'Alert recipients', 'storesuite' ),
				'description' => __( 'Comma-separated email addresses. Leave empty to use the site admin email.', 'storesuite' ),
				'default'     => '',
			),
			'enable_stock_log'            => array(
				'type'        => 'toggle',
				'label'       => __( 'Record stock movement log', 'storesuite' ),
				'description' => __( 'Log every stock change with quantity, reason and who made it.', 'storesuite' ),
				'default'     => true,
			),
			'log_retention_days'          => array(
				'type'        => 'number',
				'label'       => __( 'Stock log retention (days)', 'storesuite' ),
				'description' => __( 'Older movement entries are purged daily. Use 0 to keep forever.', 'storesuite' ),
				'default'     => 180,
				'min'         => 0,
				'max'         => 3650,
			),
			'low_stock_default_threshold' => array(
				'type'        => 'number',
				'label'       => __( 'Default low-stock threshold', 'storesuite' ),
				'description' => __( 'Used when a product has no per-product low-stock amount set.', 'storesuite' ),
				'default'     => 2,
				'min'         => 0,
				'max'         => 10000,
			),
		);
	}

	/**
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
	 * @param string $key Setting key.
	 * @return mixed
	 */
	public static function value( $key ) {
		$all = self::get();
		return $all[ $key ] ?? null;
	}

	/**
	 * @param array $data Raw input.
	 * @return array
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
			case 'select':
				$options = (array) ( $field['options'] ?? array() );
				$value   = (string) $value;
				return array_key_exists( $value, $options ) ? $value : ( $field['default'] ?? '' );
			case 'text':
			default:
				return sanitize_text_field( (string) $value );
		}
	}
}
