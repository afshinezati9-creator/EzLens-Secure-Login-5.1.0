<?php
/**
 * Coupons & simple loyalty points
 *
 * @package EzLens_Secure_Login
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class EzLens_CD_Coupons {

	const POINTS_META = '_ezcd_loyalty_points';

	public static function points( $user_id = 0 ) {
		$user_id = $user_id ? absint( $user_id ) : get_current_user_id();
		return (int) get_user_meta( $user_id, self::POINTS_META, true );
	}

	/**
	 * Available published coupons (public / unrestricted).
	 *
	 * @return WC_Coupon[]
	 */
	public static function available() {
		if ( ! function_exists( 'wc_get_coupons' ) ) {
			// WC 3+ 
			$posts = get_posts(
				array(
					'post_type'      => 'shop_coupon',
					'post_status'    => 'publish',
					'posts_per_page' => 20,
					'orderby'        => 'date',
					'order'          => 'DESC',
				)
			);
			$out = array();
			foreach ( $posts as $p ) {
				$c = new WC_Coupon( $p->ID );
				if ( self::is_visible( $c ) ) {
					$out[] = $c;
				}
			}
			return $out;
		}
		return array();
	}

	private static function is_visible( $coupon ) {
		if ( ! $coupon || ! is_a( $coupon, 'WC_Coupon' ) ) {
			return false;
		}
		if ( $coupon->get_date_expires() && time() > $coupon->get_date_expires()->getTimestamp() ) {
			return false;
		}
		// Skip private email-restricted heavy coupons for list simplicity
		$emails = $coupon->get_email_restrictions();
		if ( ! empty( $emails ) ) {
			$user = wp_get_current_user();
			if ( ! $user || ! $user->user_email ) {
				return false;
			}
			$ok = false;
			foreach ( $emails as $em ) {
				if ( strcasecmp( $em, $user->user_email ) === 0 ) {
					$ok = true;
					break;
				}
			}
			if ( ! $ok ) {
				return false;
			}
		}
		return true;
	}

	/**
	 * Coupons used in customer's past orders.
	 *
	 * @return string[]
	 */
	public static function used_codes( $user_id = 0 ) {
		$user_id = $user_id ? absint( $user_id ) : get_current_user_id();
		if ( ! $user_id || ! function_exists( 'wc_get_orders' ) ) {
			return array();
		}
		$orders = wc_get_orders(
			array(
				'customer_id' => $user_id,
				'limit'       => 30,
				'status'      => array_keys( wc_get_order_statuses() ),
			)
		);
		$codes = array();
		foreach ( $orders as $order ) {
			foreach ( $order->get_coupon_codes() as $code ) {
				$codes[ $code ] = $code;
			}
		}
		return array_values( $codes );
	}

	public static function discount_label( $coupon ) {
		if ( 'percent' === $coupon->get_discount_type() ) {
			return ezcd_fa( (string) $coupon->get_amount() ) . '٪ تخفیف';
		}
		return EzLens_CD_Wallet::format_amount( $coupon->get_amount() ) . ' تخفیف';
	}
}
