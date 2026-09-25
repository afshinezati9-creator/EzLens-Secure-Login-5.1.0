<?php
/**
 * قالب‌های آماده کمپین + merge tags — فاز ۷
 * @version 1.0.0
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class EzLens_Auth_Campaign_Templates {

	/**
	 * لیست قالب‌های آماده (قابل فیلتر)
	 */
	public static function all() {
		$site = get_bloginfo( 'name' );
		$templates = array(
			'welcome_sms'         => array(
				'id'      => 'welcome_sms',
				'channel' => 'sms',
				'label'   => 'خوش‌آمد SMS',
				'subject' => '',
				'body'    => '{first_name} عزیز، به {site_name} خوش آمدید. از پنل حسابتان سفارش و نسخه‌ها را مدیریت کنید.',
			),
			'promo_email'         => array(
				'id'      => 'promo_email',
				'channel' => 'email',
				'label'   => 'تخفیف ایمیلی',
				'subject' => '{first_name}، پیشنهاد ویژه {site_name}',
				'body'    => "سلام {name}\n\nبرای شما یک پیشنهاد ویژه آماده کرده‌ایم.\nکد پیگیری سفارش قبلی: {last_order_id}\n\nبا احترام\n{site_name}",
			),
			'order_followup_sms'  => array(
				'id'      => 'order_followup_sms',
				'channel' => 'sms',
				'label'   => 'پیگیری بعد از خرید',
				'subject' => '',
				'body'    => '{first_name} عزیز، از خریدتان متشکریم. سوالی بود با شماره سفارش در پشتیبانی پیام دهید. {site_name}',
			),
			'lens_reminder_sms'   => array(
				'id'      => 'lens_reminder_sms',
				'channel' => 'sms',
				'label'   => 'یادآوری تعویض لنز',
				'subject' => '',
				'body'    => '{first_name} عزیز، زمان تقریبی تعویض لنز نزدیک است. از حساب کاربری می‌توانید سریع سفارش مجدد ثبت کنید. {site_name}',
			),
			'reengagement_email'  => array(
				'id'      => 'reengagement_email',
				'channel' => 'email',
				'label'   => 'بازگشت مشتری',
				'subject' => 'دلمان برایتان تنگ شده — {site_name}',
				'body'    => "سلام {name}\n\nمدتی است سراغی از فروشگاه نگرفته‌اید. اگر برای چشم یا لنز نیاز به راهنمایی دارید، پشتیبانی {site_name} در خدمت شماست.\n\nلغو اشتراک: {unsubscribe_url}",
			),
		);
		return apply_filters( 'ezlens_campaign_templates', $templates, $site );
	}

	public static function get( $id ) {
		$all = self::all();
		return isset( $all[ $id ] ) ? $all[ $id ] : null;
	}

	/**
	 * متغیرهای استاندارد (هم‌تراز send_campaign)
	 */
	public static function merge_vars_help() {
		return array(
			'{name}', '{first_name}', '{last_name}', '{phone}', '{email}',
			'{site_name}', '{date}', '{user_id}', '{order_count}', '{last_order_id}', '{unsubscribe_url}',
		);
	}

	public static function apply( $text, array $vars ) {
		return strtr( (string) $text, $vars );
	}
}

// AJAX سبک برای انتخاب قالب در ادمین
add_action( 'wp_ajax_ezlens_campaign_get_templates', function () {
	check_ajax_referer( 'ezlens_auth_nonce', 'nonce' );
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error( array( 'message' => 'دسترسی غیرمجاز' ) );
	}
	$channel = isset( $_POST['channel'] ) ? sanitize_key( $_POST['channel'] ) : '';
	$list    = array_values( EzLens_Auth_Campaign_Templates::all() );
	if ( $channel === 'sms' || $channel === 'email' ) {
		$list = array_values( array_filter( $list, function ( $t ) use ( $channel ) {
			return ( $t['channel'] ?? '' ) === $channel;
		} ) );
	}
	wp_send_json_success(
		array(
			'templates' => $list,
			'tags'      => EzLens_Auth_Campaign_Templates::merge_vars_help(),
		)
	);
} );
