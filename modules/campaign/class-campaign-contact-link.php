<?php
/**
 * اتصال مخاطب خارجی کمپین به کاربر بعد از ثبت‌نام — فاز ۷
 * @version 1.0.0
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class EzLens_Auth_Campaign_Contact_Link {

	public static function init() {
		add_action( 'user_register', array( __CLASS__, 'link_user' ), 20, 1 );
		add_action( 'woocommerce_created_customer', array( __CLASS__, 'link_user' ), 20, 1 );
	}

	/**
	 * اگر ایمیل یا موبایل با مخاطب کمپین یکی بود، user_id را در meta مخاطب و برعکس ذخیره می‌کند.
	 */
	public static function link_user( $user_id ) {
		$user_id = (int) $user_id;
		$user    = get_userdata( $user_id );
		if ( ! $user ) {
			return;
		}
		global $wpdb;
		$table = $wpdb->prefix . 'ezlens_campaign_contacts';
		if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) ) !== $table ) {
			return;
		}

		$email = strtolower( sanitize_email( $user->user_email ) );
		$phone = get_user_meta( $user_id, 'billing_phone', true );
		if ( ! $phone ) {
			$phone = get_user_meta( $user_id, 'user_phone', true );
		}
		if ( class_exists( 'EzLens_Auth_Helper' ) && $phone ) {
			$phone = EzLens_Auth_Helper::normalize_mobile( $phone );
		} else {
			$phone = preg_replace( '/\D+/', '', (string) $phone );
		}

		$ids = array();
		if ( $email ) {
			$ids = array_merge(
				$ids,
				(array) $wpdb->get_col( $wpdb->prepare( "SELECT id FROM {$table} WHERE email=%s", $email ) )
			);
		}
		if ( $phone ) {
			$ids = array_merge(
				$ids,
				(array) $wpdb->get_col( $wpdb->prepare( "SELECT id FROM {$table} WHERE phone=%s", $phone ) )
			);
		}
		$ids = array_unique( array_filter( array_map( 'intval', $ids ) ) );
		if ( ! $ids ) {
			return;
		}

		foreach ( $ids as $cid ) {
			// meta سمت کاربر
			update_user_meta( $user_id, '_ezlens_campaign_contact_id', $cid );
			// در extra_fields مخاطب اگر JSON است user_id را بنویس
			$extra = $wpdb->get_var( $wpdb->prepare( "SELECT extra_fields FROM {$table} WHERE id=%d", $cid ) );
			$data  = array();
			if ( $extra ) {
				$decoded = json_decode( (string) $extra, true );
				if ( is_array( $decoded ) ) {
					$data = $decoded;
				}
			}
			$data['linked_user_id'] = $user_id;
			$data['linked_at']      = current_time( 'mysql' );
			$wpdb->update(
				$table,
				array( 'extra_fields' => wp_json_encode( $data, JSON_UNESCAPED_UNICODE ) ),
				array( 'id' => $cid )
			);
		}

		do_action( 'ezlens_campaign_contact_linked', $user_id, $ids );
	}
}

EzLens_Auth_Campaign_Contact_Link::init();
