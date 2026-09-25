<?php
/**
 * Wishlist — user meta fallback (+ Woodmart if present)
 *
 * @package EzLens_Secure_Login
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class EzLens_CD_Wishlist {

	const META = '_ezcd_wishlist';

	/**
	 * @return int[] product ids
	 */
	public static function ids( $user_id = 0 ) {
		$user_id = $user_id ? absint( $user_id ) : get_current_user_id();
		if ( ! $user_id ) {
			return array();
		}
		// Woodmart cookie/meta style
		if ( function_exists( 'woodmart_get_wishlist_count' ) ) {
			// try meta used by woodmart
			$wd = get_user_meta( $user_id, 'woodmart_wishlist_products', true );
			if ( is_array( $wd ) && $wd ) {
				return array_map( 'absint', $wd );
			}
		}
		$ids = get_user_meta( $user_id, self::META, true );
		if ( ! is_array( $ids ) ) {
			$ids = array();
		}
		return array_values( array_filter( array_map( 'absint', $ids ) ) );
	}

	public static function products( $user_id = 0 ) {
		$out = array();
		foreach ( self::ids( $user_id ) as $pid ) {
			$p = wc_get_product( $pid );
			if ( $p ) {
				$out[] = $p;
			}
		}
		return $out;
	}

	public static function add( $product_id, $user_id = 0 ) {
		$user_id = $user_id ? absint( $user_id ) : get_current_user_id();
		$pid     = absint( $product_id );
		if ( ! $user_id || ! $pid ) {
			return false;
		}
		$ids = self::ids( $user_id );
		if ( ! in_array( $pid, $ids, true ) ) {
			$ids[] = $pid;
			update_user_meta( $user_id, self::META, $ids );
		}
		return true;
	}

	public static function remove( $product_id, $user_id = 0 ) {
		$user_id = $user_id ? absint( $user_id ) : get_current_user_id();
		$pid     = absint( $product_id );
		$ids     = array_values( array_diff( self::ids( $user_id ), array( $pid ) ) );
		update_user_meta( $user_id, self::META, $ids );
		return true;
	}
}
