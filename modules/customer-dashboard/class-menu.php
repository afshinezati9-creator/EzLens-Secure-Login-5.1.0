<?php
/**
 * Customer Dashboard menu definition
 *
 * @package EzLens_Secure_Login
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class EzLens_CD_Menu {

	/**
	 * Full sidebar menu (desktop).
	 *
	 * @return array[]
	 */
	public static function section_enabled( $id ) {
		$sections = get_option( 'ezlens_cd_sections', array() );
		if ( ! is_array( $sections ) || ! $sections ) {
			return true;
		}
		// map menu id to option key
		$key = $id;
		if ( 'gift_cards' === $id || 'gift-cards' === $id ) {
			$key = 'gift_cards';
		}
		if ( ! array_key_exists( $key, $sections ) ) {
			return true;
		}
		return (int) $sections[ $key ] === 1;
	}

	public static function items() {
		$items = array(
			array(
				'id'       => 'overview',
				'label'    => 'پیشخوان',
				'icon'     => 'home',
				'endpoint' => '',
				'section'  => 'overview',
			),
			array(
				'id'       => 'orders',
				'label'    => 'سفارش‌های من',
				'icon'     => 'package',
				'endpoint' => 'orders',
				'section'  => 'orders',
			),
			array(
				'id'       => 'prescriptions',
				'label'    => 'پرونده بیمار',
				'icon'     => 'eye',
				'endpoint' => 'prescriptions',
				'section'  => 'prescriptions',
			),
			array(
				'id'       => 'wishlist',
				'label'    => 'علاقه‌مندی‌ها',
				'icon'     => 'heart',
				'endpoint' => 'wishlist',
				'section'  => 'wishlist',
			),
			array(
				'id'       => 'addresses',
				'label'    => 'آدرس‌های من',
				'icon'     => 'map-pin',
				'endpoint' => 'edit-address',
				'section'  => 'addresses',
			),
			array(
				'id'       => 'reviews',
				'label'    => 'نظرات من',
				'icon'     => 'star',
				'endpoint' => 'reviews',
				'section'  => 'reviews',
			),
			array(
				'id'       => 'coupons',
				'label'    => 'تخفیف‌ها و امتیازات',
				'icon'     => 'percent',
				'endpoint' => 'coupons',
				'section'  => 'coupons',
			),
			array(
				'id'       => 'gift_cards',
				'label'    => 'کارت هدیه',
				'icon'     => 'gift',
				'endpoint' => 'gift-cards',
				'section'  => 'gift_cards',
			),
			array(
				'id'       => 'wallet',
				'label'    => 'کیف پول',
				'icon'     => 'wallet',
				'endpoint' => 'wallet',
				'section'  => 'wallet',
			),
			array(
				'id'       => 'charity',
				'label'    => 'هم‌یاری بینایی',
				'icon'     => 'heart',
				'endpoint' => 'charity',
				'section'  => 'charity',
			),
			array(
				'id'       => 'support',
				'label'    => 'پشتیبانی',
				'icon'     => 'headset',
				'endpoint' => 'support',
				'section'  => 'support',
			),
			array(
				'id'       => 'account',
				'label'    => 'اطلاعات حساب',
				'icon'     => 'user',
				'endpoint' => 'edit-account',
				'section'  => 'account',
			),
			array(
				'id'       => 'security',
				'label'    => 'امنیت حساب',
				'icon'     => 'shield',
				'endpoint' => 'security',
				'section'  => 'security',
			),
			array(
				'id'       => 'referral',
				'label'    => 'دعوت دوستان',
				'icon'     => 'users',
				'endpoint' => 'referral',
				'section'  => 'referral',
				'badge'    => 'هدیه',
			),
			array(
				'id'       => 'logout',
				'label'    => 'خروج',
				'icon'     => 'log-out',
				'endpoint' => 'customer-logout',
				'section'  => null,
			),
		);

		$out = array();
		foreach ( $items as $item ) {
			if ( null !== $item['section'] && ! ezcd_section_enabled( $item['section'] ) ) {
				continue;
			}
			$out[] = $item;
		}
		return $out;
	}

	/**
	 * Mobile bottom nav (4 primary).
	 *
	 * @return array[]
	 */
	public static function bottom_items() {
		$all = self::items();
		$want = array( 'overview', 'orders', 'prescriptions', 'support' );
		$out  = array();
		$map_label = array(
			'orders' => 'سفارش‌ها',
			'overview' => 'پیشخوان',
			'prescriptions' => 'نسخه‌ها',
			'support' => 'پشتیبانی',
		);
		foreach ( $all as $item ) {
			if ( in_array( $item['id'], $want, true ) ) {
				if ( isset( $map_label[ $item['id'] ] ) ) {
					$item['label'] = $map_label[ $item['id'] ];
				}
				$out[] = $item;
			}
		}
		return $out;
	}

	/**
	 * @param string $endpoint
	 * @return string
	 */
	public static function url_for( $endpoint ) {
		if ( ! function_exists( 'wc_get_account_endpoint_url' ) ) {
			return home_url( '/my-account/' );
		}
		if ( '' === $endpoint || 'overview' === $endpoint ) {
			return wc_get_page_permalink( 'myaccount' );
		}
		if ( 'customer-logout' === $endpoint ) {
			return wc_get_account_endpoint_url( 'customer-logout' );
		}
		return wc_get_account_endpoint_url( $endpoint );
	}
}
