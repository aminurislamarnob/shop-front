<?php
/**
 * Cache handler
 *
 * @package ShopFront
 */

namespace PluginizeLab\ShopFront;

/**
 * Cache class
 */
class Cache {
	/**
	 * Cache prefix.
	 *
	 * @var string
	 */
	private static $prefix = 'msf_';

	/**
	 * Cache group for object cache.
	 *
	 * @var string
	 */
	private static $group = 'shop_front';

	/**
	 * Get cached data.
	 *
	 * @param string $key Cache key.
	 * @param bool   $use_transient Use transient instead of object cache.
	 * @return mixed|false Cached data or false if not found.
	 */
	public static function get( $key, $use_transient = true ) {
		$cache_key = self::$prefix . $key;

		if ( $use_transient ) {
			return get_transient( $cache_key );
		}

		return wp_cache_get( $cache_key, self::$group );
	}

	/**
	 * Set cached data.
	 *
	 * @param string $key Cache key.
	 * @param mixed  $data Data to cache.
	 * @param int    $expiration Expiration time in seconds.
	 * @param bool   $use_transient Use transient instead of object cache.
	 * @return bool
	 */
	public static function set( $key, $data, $expiration = HOUR_IN_SECONDS, $use_transient = true ) {
		$cache_key = self::$prefix . $key;

		if ( $use_transient ) {
			return set_transient( $cache_key, $data, $expiration );
		}

		return wp_cache_set( $cache_key, $data, self::$group, $expiration );
	}

	/**
	 * Delete cached data.
	 *
	 * @param string $key Cache key.
	 * @param bool   $use_transient Use transient instead of object cache.
	 * @return bool
	 */
	public static function delete( $key, $use_transient = true ) {
		$cache_key = self::$prefix . $key;

		if ( $use_transient ) {
			return delete_transient( $cache_key );
		}

		return wp_cache_delete( $cache_key, self::$group );
	}

	/**
	 * Flush cache group.
	 *
	 * @param string $group Cache group to flush.
	 * @return bool
	 */
	public static function flush_group( $group = null ) {
		$cache_group = $group ? $group : self::$group;
		return wp_cache_flush_group( $cache_group );
	}

	/**
	 * Get cached data or return default value.
	 *
	 * @param string $key Cache key.
	 * @param mixed  $default_value Default value if cache not found.
	 * @param bool   $use_transient Use transient instead of object cache.
	 * @return mixed Cached data or default value.
	 */
	public static function get_or_default( $key, $default_value = null, $use_transient = true ) {
		$cached = self::get( $key, $use_transient );

		if ( false !== $cached ) {
			return $cached;
		}

		return $default_value;
	}

	/**
	 * Clear multiple cache keys.
	 *
	 * @param array $keys Array of cache keys.
	 * @param bool  $use_transient Use transient instead of object cache.
	 * @return void
	 */
	public static function delete_many( $keys, $use_transient = true ) {
		foreach ( $keys as $key ) {
			self::delete( $key, $use_transient );
		}
	}

	/**
	 * Check if cache exists.
	 *
	 * @param string $key Cache key.
	 * @param bool   $use_transient Use transient instead of object cache.
	 * @return bool
	 */
	public static function has( $key, $use_transient = true ) {
		return false !== self::get( $key, $use_transient );
	}

	/**
	 * Set cache prefix.
	 *
	 * @param string $prefix Cache prefix.
	 * @return void
	 */
	public static function set_prefix( $prefix ) {
		self::$prefix = $prefix;
	}

	/**
	 * Set cache group.
	 *
	 * @param string $group Cache group.
	 * @return void
	 */
	public static function set_group( $group ) {
		self::$group = $group;
	}
}
