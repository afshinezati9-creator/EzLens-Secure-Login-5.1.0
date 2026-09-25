<?php
/**
 * Customer Dashboard helpers
 *
 * @package EzLens_Secure_Login
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'ezcd_fa' ) ) {
	function ezcd_fa( $text ) {
		if ( is_array( $text ) ) {
			$text = implode( '', $text );
		}
		return str_replace(
			array( '0', '1', '2', '3', '4', '5', '6', '7', '8', '9' ),
			array( '۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹' ),
			(string) $text
		);
	}
}

if ( ! function_exists( 'ezcd_icon' ) ) {
	/**
	 * Load SVG from plugin modern icons.
	 *
	 * @param string $name Icon basename without .svg
	 * @return string
	 */
	function ezcd_icon( $name ) {
		$name = sanitize_file_name( $name );
		$paths = array();
		if ( defined( 'EZLAUTH_PLUGIN_DIR' ) ) {
			$paths[] = EZLAUTH_PLUGIN_DIR . 'assets/icons/modern/' . $name . '.svg';
			$paths[] = EZLAUTH_PLUGIN_DIR . 'assets/icons/' . $name . '.svg';
		}
		foreach ( $paths as $path ) {
			if ( is_readable( $path ) ) {
				$c = file_get_contents( $path );
				if ( false !== $c && trim( $c ) !== '' ) {
					// Ensure SVG attributes stay Latin (site-wide FA digit filters)
					$c = str_replace(
						array( '۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹' ),
						array( '0', '1', '2', '3', '4', '5', '6', '7', '8', '9' ),
						$c
					);
					return $c;
				}
			}
		}
		// fallback های مینیمال برای آیکون‌های گم‌شده
		$fb = array(
			'menu' => '<svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>',
			'grid' => '<svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>',
			'more-horizontal' => '<svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="1"/><circle cx="19" cy="12" r="1"/><circle cx="5" cy="12" r="1"/></svg>',
			'upload' => '<svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>',
		);
		return isset( $fb[ $name ] ) ? $fb[ $name ] : '';
	}
}

if ( ! function_exists( 'ezcd_section_enabled' ) ) {
	function ezcd_section_enabled( $key ) {
		$key = sanitize_key( $key );
		if ( 'overview' === $key ) {
			return true;
		}
		$sections = get_option( 'ezlens_cd_sections', array() );
		if ( is_array( $sections ) && array_key_exists( $key, $sections ) ) {
			return (int) $sections[ $key ] === 1;
		}
		// legacy single options
		return (int) get_option( 'ezlens_cd_section_' . $key, 1 ) === 1;
	}
}

if ( ! function_exists( 'ezcd_is_account_page' ) ) {
	function ezcd_is_account_page() {
		if ( ! function_exists( 'is_account_page' ) ) {
			return false;
		}
		return is_account_page();
	}
}

if ( ! function_exists( 'ezcd_current_endpoint' ) ) {
	function ezcd_current_endpoint() {
		if ( ! function_exists( 'is_wc_endpoint_url' ) ) {
			return 'overview';
		}
		$custom = array(
			'prescriptions',
			'wallet',
			'gift-cards',
			'support',
			'security',
			'wishlist',
			'coupons',
			'referral',
		);
		foreach ( $custom as $ep ) {
			if ( is_wc_endpoint_url( $ep ) ) {
				return $ep;
			}
		}
		// Core WC endpoints
		$wc = array(
			'orders'          => 'orders',
			'view-order'      => 'view-order',
			'edit-address'    => 'addresses',
			'edit-account'    => 'account',
			'payment-methods' => 'account',
			'customer-logout' => 'logout',
		);
		foreach ( $wc as $ep => $map ) {
			if ( is_wc_endpoint_url( $ep ) ) {
				return $map;
			}
		}
		// Dashboard root
		if ( function_exists( 'is_account_page' ) && is_account_page() && ! is_wc_endpoint_url() ) {
			return 'overview';
		}
		return 'overview';
	}
}

if ( ! function_exists( 'ezcd_gregorian_to_jalali' ) ) {
	/**
	 * Convert Gregorian Y-m-d to Jalali array [jy,jm,jd].
	 */
	function ezcd_gregorian_to_jalali( $gy, $gm, $gd ) {
		$gy = (int) $gy;
		$gm = (int) $gm;
		$gd = (int) $gd;
		$g_d_m = array( 0, 31, 59, 90, 120, 151, 181, 212, 243, 273, 304, 334 );
		$gy2   = ( $gm > 2 ) ? ( $gy + 1 ) : $gy;
		$days  = 355666 + ( 365 * $gy ) + (int) ( ( $gy2 + 3 ) / 4 ) - (int) ( ( $gy2 + 99 ) / 100 ) + (int) ( ( $gy2 + 399 ) / 400 ) + $gd + $g_d_m[ $gm - 1 ];
		$jy    = -1595 + ( 33 * (int) ( $days / 12053 ) );
		$days %= 12053;
		$jy   += 4 * (int) ( $days / 1461 );
		$days %= 1461;
		if ( $days > 365 ) {
			$jy   += (int) ( ( $days - 1 ) / 365 );
			$days  = ( $days - 1 ) % 365;
		}
		if ( $days < 186 ) {
			$jm = 1 + (int) ( $days / 31 );
			$jd = 1 + ( $days % 31 );
		} else {
			$jm = 7 + (int) ( ( $days - 186 ) / 30 );
			$jd = 1 + ( ( $days - 186 ) % 30 );
		}
		return array( $jy, $jm, $jd );
	}
}

if ( ! function_exists( 'ezcd_jalali' ) ) {
	/**
	 * Format datetime/date string as Jalali with Persian digits.
	 *
	 * @param string $datetime MySQL or strtotime-able
	 * @param string $format   jdate-like: Y/m/d H:i
	 */
	function ezcd_jalali( $datetime, $format = 'Y/m/d H:i' ) {
		if ( empty( $datetime ) || '0000-00-00 00:00:00' === $datetime ) {
			return '—';
		}
		$ts = is_numeric( $datetime ) ? (int) $datetime : strtotime( (string) $datetime );
		if ( ! $ts ) {
			return ezcd_fa( (string) $datetime );
		}
		list( $jy, $jm, $jd ) = ezcd_gregorian_to_jalali(
			(int) gmdate( 'Y', $ts + (int) ( get_option( 'gmt_offset' ) * HOUR_IN_SECONDS ) ),
			(int) gmdate( 'n', $ts + (int) ( get_option( 'gmt_offset' ) * HOUR_IN_SECONDS ) ),
			(int) gmdate( 'j', $ts + (int) ( get_option( 'gmt_offset' ) * HOUR_IN_SECONDS ) )
		);
		// Prefer local time via date()
		$ts_local = $ts;
		list( $jy, $jm, $jd ) = ezcd_gregorian_to_jalali(
			(int) date( 'Y', $ts_local ),
			(int) date( 'n', $ts_local ),
			(int) date( 'j', $ts_local )
		);
		$h = date( 'H', $ts_local );
		$i = date( 'i', $ts_local );
		$map = array(
			'Y' => (string) $jy,
			'm' => str_pad( (string) $jm, 2, '0', STR_PAD_LEFT ),
			'd' => str_pad( (string) $jd, 2, '0', STR_PAD_LEFT ),
			'H' => $h,
			'i' => $i,
		);
		$out = $format;
		foreach ( $map as $k => $v ) {
			$out = str_replace( $k, $v, $out );
		}
		return ezcd_fa( $out );
	}
}

if ( ! function_exists( 'ezcd_max_upload_bytes' ) ) {
	function ezcd_max_upload_bytes() {
		return 20 * 1024 * 1024; // 20 MB
	}
}
