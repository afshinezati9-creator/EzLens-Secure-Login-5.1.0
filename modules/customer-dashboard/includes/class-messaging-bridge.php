<?php
/**
 * Adaptive bridge to EzLens Messaging / Campaign / WP mail
 *
 * @package EzLens_Secure_Login
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class EzLens_CD_Messaging_Bridge {

	/**
	 * Send SMS to a phone number.
	 *
	 * @return true|WP_Error
	 */
	public static function send_sms( $phone, $message ) {
		$phone   = self::normalize_phone( $phone );
		$message = sanitize_textarea_field( $message );
		if ( ! $phone ) {
			return new WP_Error( 'phone', 'شماره موبایل معتبر نیست' );
		}
		if ( $message === '' ) {
			return new WP_Error( 'message', 'متن پیام خالی است' );
		}

		// Module API variants
		if ( class_exists( 'EzLens_Auth_Messaging' ) ) {
			$m = is_callable( array( 'EzLens_Auth_Messaging', 'get_instance' ) ) ? EzLens_Auth_Messaging::get_instance() : null;
			if ( $m ) {
				foreach ( array( 'send_sms', 'send', 'sms' ) as $method ) {
					if ( method_exists( $m, $method ) ) {
						$r = $m->$method( $phone, $message );
						if ( is_wp_error( $r ) ) {
							return $r;
						}
						if ( false !== $r ) {
							return true;
						}
					}
				}
			}
			if ( is_callable( array( 'EzLens_Auth_Messaging', 'send_sms' ) ) ) {
				$r = EzLens_Auth_Messaging::send_sms( $phone, $message );
				if ( is_wp_error( $r ) ) {
					return $r;
				}
				if ( false !== $r ) {
					return true;
				}
			}
		}

		/**
		 * Fallback hooks used across EzLens versions.
		 *
		 * @param string $phone
		 * @param string $message
		 * @return bool|WP_Error
		 */
		$r = apply_filters( 'ezlens_send_sms', null, $phone, $message );
		if ( is_wp_error( $r ) ) {
			return $r;
		}
		if ( true === $r ) {
			return true;
		}

		do_action( 'ezlens_send_mass_message', 'sms', $phone, $message );
		do_action( 'ezlens_cd_send_sms', $phone, $message );

		// Last resort: log only (no real gateway) — mark as queued for admin review
		return new WP_Error(
			'no_gateway',
			'درگاه پیامک در دسترس نیست. پیام در صف داخلی ثبت شد؛ اتصال Messaging را بررسی کنید.'
		);
	}

	/**
	 * @return true|WP_Error
	 */
	public static function send_email( $email, $subject, $body ) {
		$email   = sanitize_email( $email );
		$subject = sanitize_text_field( $subject );
		$body    = wp_kses_post( $body );
		if ( ! is_email( $email ) ) {
			return new WP_Error( 'email', 'ایمیل نامعتبر است' );
		}
		$headers = array( 'Content-Type: text/html; charset=UTF-8' );
		$sent    = wp_mail( $email, $subject, wpautop( $body ), $headers );
		if ( ! $sent ) {
			return new WP_Error( 'mail', 'ارسال ایمیل ناموفق بود' );
		}
		return true;
	}

	public static function normalize_phone( $phone ) {
		$phone = preg_replace( '/\D+/', '', (string) $phone );
		if ( strpos( $phone, '98' ) === 0 && strlen( $phone ) >= 12 ) {
			$phone = '0' . substr( $phone, 2 );
		}
		if ( strlen( $phone ) === 10 && $phone[0] === '9' ) {
			$phone = '0' . $phone;
		}
		if ( strlen( $phone ) < 10 ) {
			return '';
		}
		return $phone;
	}

	public static function user_phone( $user_id ) {
		$phone = get_user_meta( $user_id, 'billing_phone', true );
		if ( ! $phone ) {
			$phone = get_user_meta( $user_id, 'digits_phone', true );
		}
		return self::normalize_phone( $phone );
	}
}
