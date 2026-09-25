<?php
/**
 * رجیستری صفحات ورود (بدون پنل کاربری)
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class EzLens_Login_Pages_Registry {

	/**
	 * @return array<string,array{label:string,shortcode:string,description:string}>
	 */
	public static function pages() {
		return array(
			'customer-login' => array(
				'label'       => 'ورود مشتری',
				'shortcode'   => '[minimal_auth]',
				'description' => 'ورود / ثبت‌نام کاربران فروشگاه با OTP و رمز',
			),
			'admin-login'    => array(
				'label'       => 'ورود مدیر',
				'shortcode'   => '[admin_login_page]',
				'description' => 'صفحه ورود امن مدیران (جدا از wp-login)',
			),
			'lost-password'  => array(
				'label'       => 'فراموشی رمز',
				'shortcode'   => '[ezlens_lost_password]',
				'description' => 'بازیابی رمز عبور مشتری',
			),
		);
	}

	public static function is_valid( $slug ) {
		return isset( self::pages()[ $slug ] );
	}
}
