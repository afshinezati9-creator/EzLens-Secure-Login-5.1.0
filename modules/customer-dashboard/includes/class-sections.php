<?php
/**
 * Render dashboard section HTML (for full page + AJAX)
 *
 * @package EzLens_Secure_Login
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class EzLens_CD_Sections {

	/**
	 * Map menu/endpoint keys to template file basename.
	 */
	public static function map() {
		return array(
			'overview'      => 'overview',
			'orders'        => 'orders',
			'view-order'    => 'view-order',
			'addresses'     => 'addresses',
			'edit-address'  => 'addresses',
			'account'       => 'account',
			'edit-account'  => 'account',
			'security'      => 'security',
			'wishlist'      => 'wishlist',
			'prescriptions' => 'prescriptions',
			'reviews'       => 'reviews',
			'coupons'       => 'coupons',
			'gift_cards'    => 'gift-cards',
			'gift-cards'    => 'gift-cards',
			'wallet'        => 'wallet',
			'charity'       => 'charity',
			'support'       => 'support',
			'referral'      => 'referral',
		);
	}

	/**
	 * @param string $key Section key
	 * @param array  $args Extra (order_id, edit address type, …)
	 * @return string HTML
	 */
	public static function render( $key, $args = array() ) {
		$key  = sanitize_key( $key );
		$map  = self::map();
		$file = isset( $map[ $key ] ) ? $map[ $key ] : 'placeholder';
		$current = $key;

		// Pass args into template scope
		if ( ! empty( $args['order_id'] ) ) {
			$GLOBALS['ezcd_order_id'] = absint( $args['order_id'] );
		}
		if ( ! empty( $args['edit'] ) ) {
			$_GET['edit'] = sanitize_key( $args['edit'] );
		}
		if ( ! empty( $args['tab'] ) ) {
			$_GET['tab'] = sanitize_key( $args['tab'] );
		}
		if ( ! empty( $args['ticket'] ) ) {
			$_GET['ticket'] = absint( $args['ticket'] );
			$GLOBALS['ezcd_ticket_id'] = absint( $args['ticket'] );
		}
		if ( isset( $args['edit'] ) ) {
			$_GET['edit'] = sanitize_key( $args['edit'] );
		}
		if ( ! empty( $args['ptab'] ) ) {
			$_GET['ptab'] = sanitize_key( $args['ptab'] );
		}
		if ( ! empty( $args['stab'] ) ) {
			$_GET['stab'] = sanitize_key( $args['stab'] );
		}

		ob_start();
		$path = dirname( __DIR__ ) . '/templates/sections/' . $file . '.php';
		if ( 'placeholder' === $file || ! is_readable( $path ) ) {
			$ph = dirname( __DIR__ ) . '/templates/placeholder.php';
			if ( is_readable( $ph ) ) {
				include $ph;
			} else {
				echo '<p>بخش در دسترس نیست.</p>';
			}
		} else {
			include $path;
		}
		return ob_get_clean();
	}

	public static function title( $key ) {
		$key = sanitize_key( $key );
		if ( 'view-order' === $key ) {
			return 'جزئیات سفارش';
		}
		if ( class_exists( 'EzLens_CD_Menu' ) ) {
			foreach ( EzLens_CD_Menu::items() as $it ) {
				if ( $it['id'] === $key || $it['endpoint'] === $key ) {
					return $it['label'];
				}
			}
		}
		$fallback = array(
			'overview'      => 'پیشخوان',
			'orders'        => 'سفارش‌های من',
			'addresses'     => 'آدرس‌های من',
			'account'       => 'اطلاعات حساب',
			'security'      => 'امنیت حساب',
			'wishlist'      => 'علاقه‌مندی‌ها',
			'prescriptions' => 'پرونده بیمار',
			'support'       => 'پشتیبانی',
			'reviews'       => 'نظرات من',
			'wallet'        => 'کیف پول',
			'charity'       => 'هم‌یاری بینایی',
			'gift-cards'    => 'کارت هدیه',
			'gift_cards'    => 'کارت هدیه',
			'coupons'       => 'تخفیف‌ها و امتیازات',
			'referral'      => 'دعوت دوستان',
		);
		return isset( $fallback[ $key ] ) ? $fallback[ $key ] : 'حساب کاربری';
	}
}
