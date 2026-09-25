<?php
/**
 * Lightweight cache helpers for Customer Dashboard
 *
 * @package EzLens_Secure_Login
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class EzLens_CD_Cache {

	const GROUP = 'ezcd';
	const TTL   = 300; // 5 minutes

	public static function key( $parts ) {
		if ( is_array( $parts ) ) {
			$parts = implode( ':', $parts );
		}
		return 'ezcd_' . md5( (string) $parts );
	}

	public static function get( $key ) {
		return get_transient( self::key( $key ) );
	}

	public static function set( $key, $value, $ttl = null ) {
		$ttl = null === $ttl ? self::TTL : absint( $ttl );
		return set_transient( self::key( $key ), $value, $ttl );
	}

	public static function delete( $key ) {
		return delete_transient( self::key( $key ) );
	}

	public static function remember( $key, $callback, $ttl = null ) {
		$cached = self::get( $key );
		if ( false !== $cached ) {
			return $cached;
		}
		$value = call_user_func( $callback );
		self::set( $key, $value, $ttl );
		return $value;
	}

	public static function flush_user( $user_id ) {
		$user_id = absint( $user_id );
		self::delete( 'orders_' . $user_id );
		self::delete( 'wallet_' . $user_id );
		self::delete( 'rx_' . $user_id );
		self::delete( 'profile_' . $user_id );
		self::delete( 'overview_' . $user_id );
	}

	public static function flush_admin_stats() {
		self::delete( 'admin_global_stats' );
	}
}
