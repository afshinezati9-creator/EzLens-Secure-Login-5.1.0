<?php
/**
 * Customer Dashboard AJAX — Phase 2+3 (SPA sections + prescriptions)
 *
 * @package EzLens_Secure_Login
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class EzLens_CD_Ajax {

	/** @var self|null */
	private static $instance = null;

	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		$actions = array(
			'ezcd_load_section',
			'ezcd_reorder',
			'ezcd_save_address',
			'ezcd_delete_address',
			'ezcd_save_account',
			'ezcd_change_password',
			'ezcd_wishlist_remove',
			'ezcd_wishlist_add_cart',
			'ezcd_rx_save',
			'ezcd_rx_delete',
			'ezcd_rx_upload',
			'ezcd_patient_profile_save',
			'ezcd_gift_redeem',
			'ezcd_gift_purchase',
			'ezcd_ticket_create',
			'ezcd_ticket_reply',
			'ezcd_review_update',
			'ezcd_review_delete',
			'ezcd_wallet_deposit',
			'ezcd_charity_donate',
		);
		foreach ( $actions as $a ) {
			$method = 'handle_' . str_replace( 'ezcd_', '', $a );
			if ( method_exists( $this, $method ) ) {
				add_action( 'wp_ajax_' . $a, array( $this, $method ) );
			}
		}
	}

	private function require_login() {
		if ( ! is_user_logged_in() ) {
			wp_send_json_error( array( 'message' => 'لطفاً وارد حساب خود شوید' ), 401 );
		}
	}

	private function verify() {
		$nonce = isset( $_REQUEST['nonce'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['nonce'] ) ) : '';
		if ( ! wp_verify_nonce( $nonce, 'ezcd_nonce' ) ) {
			wp_send_json_error( array( 'message' => 'نشست منقضی شده. صفحه را یک‌بار تازه کنید.' ), 403 );
		}
	}

	/** SPA: load section HTML */
	public function handle_load_section() {
		$this->require_login();
		$this->verify();
		$section = isset( $_REQUEST['section'] ) ? sanitize_key( $_REQUEST['section'] ) : 'overview';
		$args    = array();
		if ( ! empty( $_REQUEST['order_id'] ) ) {
			$args['order_id'] = absint( $_REQUEST['order_id'] );
		}
		if ( ! empty( $_REQUEST['edit'] ) ) {
			$args['edit'] = sanitize_key( $_REQUEST['edit'] );
		}
		if ( ! empty( $_REQUEST['tab'] ) ) {
			$args['tab'] = sanitize_key( $_REQUEST['tab'] );
		}
		if ( ! empty( $_REQUEST['ticket'] ) ) {
			$args['ticket'] = absint( $_REQUEST['ticket'] );
		}
		if ( ! empty( $_REQUEST['stab'] ) ) {
			$args['stab'] = sanitize_key( $_REQUEST['stab'] );
		}
		if ( isset( $_REQUEST['edit'] ) ) {
			$args['edit'] = sanitize_key( wp_unslash( $_REQUEST['edit'] ) );
		}
		if ( ! empty( $_REQUEST['ptab'] ) ) {
			$args['ptab'] = sanitize_key( $_REQUEST['ptab'] );
		}
		// Wallet is a critical SPA section: make sure its tables exist before rendering.
		// This also repairs older installations where the wallet module was added after activation.
		if ( 'wallet' === $section ) {
			if ( class_exists( 'EzLens_CD_Wallet' ) ) {
				EzLens_CD_Wallet::maybe_create_table();
			}
			if ( class_exists( 'EzLens_CD_Wallet_Deposits' ) ) {
				EzLens_CD_Wallet_Deposits::maybe_create_table();
			}
		}

		try {
			$html  = EzLens_CD_Sections::render( $section, $args );
			$title = EzLens_CD_Sections::title( $section );
			$url   = $this->section_url( $section, $args );
		} catch ( Throwable $e ) {
			error_log( 'EzLens CD wallet/section AJAX error: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine() );
			wp_send_json_error(
				array(
					'message' => ( defined( 'WP_DEBUG' ) && WP_DEBUG )
						? 'خطا در بارگذاری بخش: ' . $e->getMessage()
						: 'خطا در بارگذاری کیف پول. لطفاً صفحه را تازه کنید.',
				)
			);
		}
		wp_send_json_success(
			array(
				'html'    => $html,
				'title'   => $title,
				'section' => $section,
				'url'     => $url,
			)
		);
	}

	private function section_url( $section, $args = array() ) {
		if ( ! function_exists( 'wc_get_account_endpoint_url' ) ) {
			return home_url( '/my-account/' );
		}
		$endpoint_map = array(
			'overview'      => '',
			'orders'        => 'orders',
			'view-order'    => 'view-order',
			'addresses'     => 'edit-address',
			'account'       => 'edit-account',
			'security'      => 'security',
			'wishlist'      => 'wishlist',
			'prescriptions' => 'prescriptions',
			'support'       => 'support',
			'wallet'        => 'wallet',
			'gift-cards'    => 'gift-cards',
			'coupons'       => 'coupons',
			'referral'      => 'referral',
			'reviews'       => 'reviews',
		);
		$ep = isset( $endpoint_map[ $section ] ) ? $endpoint_map[ $section ] : $section;
		if ( 'view-order' === $section && ! empty( $args['order_id'] ) ) {
			return wc_get_endpoint_url( 'view-order', $args['order_id'], wc_get_page_permalink( 'myaccount' ) );
		}
		if ( '' === $ep ) {
			$url = wc_get_page_permalink( 'myaccount' );
		} else {
			$url = wc_get_account_endpoint_url( $ep );
		}
		if ( ! empty( $args['edit'] ) ) {
			$url = add_query_arg( 'edit', $args['edit'], $url );
		}
		if ( ! empty( $args['tab'] ) ) {
			$url = add_query_arg( 'tab', $args['tab'], $url );
		}
		if ( ! empty( $args['ticket'] ) ) {
			$url = add_query_arg( 'ticket', absint( $args['ticket'] ), $url );
		}
		if ( ! empty( $args['stab'] ) ) {
			$url = add_query_arg( 'stab', sanitize_key( $args['stab'] ), $url );
		}
		return $url;
	}

	public function handle_reorder() {
		$this->require_login();
		$this->verify();
		$oid = isset( $_POST['order_id'] ) ? absint( $_POST['order_id'] ) : 0;
		$r   = EzLens_CD_Orders::reorder_to_cart( $oid );
		if ( is_wp_error( $r ) ) {
			wp_send_json_error( array( 'message' => $r->get_error_message() ) );
		}
		wp_send_json_success(
			array(
				'message'  => 'کالاها با موفقیت به سبد اضافه شدند',
				'cart_url' => function_exists( 'wc_get_cart_url' ) ? wc_get_cart_url() : home_url( '/cart/' ),
			)
		);
	}

	public function handle_save_address() {
		$this->require_login();
		$this->verify();
		$data = wp_unslash( $_POST );
		if ( isset( $data['address'] ) && is_array( $data['address'] ) ) {
			$data = array_merge( $data, $data['address'] );
		}
		$type = isset( $data['type'] ) ? sanitize_key( $data['type'] ) : 'shipping';
		$r    = EzLens_CD_Addresses::save( $type, $data );
		if ( is_wp_error( $r ) ) {
			wp_send_json_error( array( 'message' => $r->get_error_message() ) );
		}
		wp_send_json_success( array( 'message' => 'آدرس با موفقیت ذخیره شد' ) );
	}

	public function handle_delete_address() {
		$this->require_login();
		$this->verify();
		$id = isset( $_POST['id'] ) ? sanitize_key( $_POST['id'] ) : '';
		EzLens_CD_Addresses::delete_book_item( $id );
		wp_send_json_success( array( 'message' => 'آدرس حذف شد' ) );
	}

	public function handle_save_account() {
		$this->require_login();
		$this->verify();
		$r = EzLens_CD_Account::save( wp_unslash( $_POST ) );
		if ( is_wp_error( $r ) ) {
			wp_send_json_error( array( 'message' => $r->get_error_message() ) );
		}
		wp_send_json_success( array( 'message' => 'اطلاعات حساب ذخیره شد' ) );
	}

	public function handle_change_password() {
		$this->require_login();
		$this->verify();
		$cur = isset( $_POST['current_password'] ) ? (string) wp_unslash( $_POST['current_password'] ) : '';
		$new = isset( $_POST['new_password'] ) ? (string) wp_unslash( $_POST['new_password'] ) : '';
		$cf  = isset( $_POST['confirm_password'] ) ? (string) wp_unslash( $_POST['confirm_password'] ) : '';
		$r   = EzLens_CD_Security::change_password( $cur, $new, $cf );
		if ( is_wp_error( $r ) ) {
			wp_send_json_error( array( 'message' => $r->get_error_message() ) );
		}
		wp_send_json_success( array( 'message' => 'رمز عبور با موفقیت تغییر کرد' ) );
	}

	public function handle_wishlist_remove() {
		$this->require_login();
		$this->verify();
		$pid = isset( $_POST['product_id'] ) ? absint( $_POST['product_id'] ) : 0;
		EzLens_CD_Wishlist::remove( $pid );
		wp_send_json_success( array( 'message' => 'از علاقه‌مندی‌ها حذف شد' ) );
	}

	public function handle_wishlist_add_cart() {
		$this->require_login();
		$this->verify();
		$pid = isset( $_POST['product_id'] ) ? absint( $_POST['product_id'] ) : 0;
		$p   = wc_get_product( $pid );
		if ( ! $p || ! $p->is_purchasable() ) {
			wp_send_json_error( array( 'message' => 'این محصول فعلاً قابل خرید نیست' ) );
		}
		$r = WC()->cart->add_to_cart( $pid, 1 );
		if ( ! $r ) {
			wp_send_json_error( array( 'message' => 'افزودن به سبد ممکن نشد' ) );
		}
		wp_send_json_success(
			array(
				'message'  => 'به سبد خرید اضافه شد',
				'cart_url' => wc_get_cart_url(),
			)
		);
	}

	public function handle_rx_save() {
		$this->require_login();
		$this->verify();
		$r = EzLens_CD_Prescriptions::save( wp_unslash( $_POST ) );
		if ( is_wp_error( $r ) ) {
			wp_send_json_error( array( 'message' => $r->get_error_message() ) );
		}
		wp_send_json_success(
			array(
				'message' => 'نسخه ذخیره شد',
				'id'      => (int) $r,
			)
		);
	}

	public function handle_rx_delete() {
		$this->require_login();
		$this->verify();
		$id = isset( $_POST['id'] ) ? absint( $_POST['id'] ) : 0;
		$r  = EzLens_CD_Prescriptions::delete( $id );
		if ( is_wp_error( $r ) ) {
			wp_send_json_error( array( 'message' => $r->get_error_message() ) );
		}
		wp_send_json_success( array( 'message' => 'نسخه حذف شد' ) );
	}

	public function handle_rx_upload() {
		$this->require_login();
		$this->verify();
		if ( empty( $_FILES['file'] ) ) {
			wp_send_json_error( array( 'message' => 'فایلی ارسال نشد' ) );
		}
		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/media.php';
		require_once ABSPATH . 'wp-admin/includes/image.php';
		$attachment_id = media_handle_upload( 'file', 0 );
		if ( is_wp_error( $attachment_id ) ) {
			wp_send_json_error( array( 'message' => $attachment_id->get_error_message() ) );
		}
		wp_send_json_success(
			array(
				'message'       => 'فایل آپلود شد',
				'attachment_id' => (int) $attachment_id,
				'url'           => wp_get_attachment_url( $attachment_id ),
			)
		);
	}

	public function handle_patient_profile_save() {
		$this->require_login();
		$this->verify();
		$data = wp_unslash( $_POST );
		if ( isset( $data['conditions'] ) && ! is_array( $data['conditions'] ) ) {
			$data['conditions'] = array( $data['conditions'] );
		}
		$r = EzLens_CD_Prescriptions::save_profile( $data );
		if ( is_wp_error( $r ) ) {
			wp_send_json_error( array( 'message' => $r->get_error_message() ) );
		}
		wp_send_json_success( array( 'message' => 'اطلاعات پایه پرونده ذخیره شد' ) );
	}

	public function handle_gift_redeem() {
		$this->require_login();
		$this->verify();
		$code = isset( $_POST['code'] ) ? (string) wp_unslash( $_POST['code'] ) : '';
		$r    = EzLens_CD_Gift_Cards::redeem( $code );
		if ( is_wp_error( $r ) ) {
			wp_send_json_error( array( 'message' => $r->get_error_message() ) );
		}
		wp_send_json_success(
			array(
				'message' => 'کارت هدیه با موفقیت به کیف پول اضافه شد',
				'balance' => EzLens_CD_Wallet::balance(),
			)
		);
	}

	public function handle_gift_purchase() {
		$this->require_login();
		$this->verify();
		$amount = isset( $_POST['amount'] ) ? absint( $_POST['amount'] ) : 0;
		$name   = isset( $_POST['recipient_name'] ) ? sanitize_text_field( wp_unslash( $_POST['recipient_name'] ) ) : '';
		$msg    = isset( $_POST['message'] ) ? sanitize_textarea_field( wp_unslash( $_POST['message'] ) ) : '';
		$r      = EzLens_CD_Gift_Cards::purchase( $amount, $name, $msg );
		if ( is_wp_error( $r ) ) {
			wp_send_json_error( array( 'message' => $r->get_error_message() ) );
		}
		wp_send_json_success(
			array(
				'message' => 'کارت هدیه صادر شد. کد را برای گیرنده بفرستید.',
				'code'    => $r['code'],
				'balance' => EzLens_CD_Wallet::balance(),
			)
		);
	}

	public function handle_ticket_create() {
		$this->require_login();
		$this->verify();
		$subject  = isset( $_POST['subject'] ) ? (string) wp_unslash( $_POST['subject'] ) : '';
		$message  = isset( $_POST['message'] ) ? (string) wp_unslash( $_POST['message'] ) : '';
		$topic    = isset( $_POST['topic'] ) ? sanitize_key( $_POST['topic'] ) : '';
		$other    = isset( $_POST['topic_other'] ) ? sanitize_text_field( wp_unslash( $_POST['topic_other'] ) ) : '';
		$priority = isset( $_POST['priority'] ) ? sanitize_key( $_POST['priority'] ) : 'normal';

		if ( 'other' === $topic && $other !== '' ) {
			$subject = $other . ( $subject !== '' ? ' — ' . $subject : '' );
		}

		$attachment_id = 0;
		if ( ! empty( $_FILES['attachment'] ) && ! empty( $_FILES['attachment']['name'] ) ) {
			$max = function_exists( 'ezcd_max_upload_bytes' ) ? ezcd_max_upload_bytes() : ( 20 * 1024 * 1024 );
			if ( ! empty( $_FILES['attachment']['size'] ) && (int) $_FILES['attachment']['size'] > $max ) {
				wp_send_json_error( array( 'message' => 'حجم فایل نباید بیشتر از ۲۰ مگابایت باشد' ) );
			}
			require_once ABSPATH . 'wp-admin/includes/file.php';
			require_once ABSPATH . 'wp-admin/includes/media.php';
			require_once ABSPATH . 'wp-admin/includes/image.php';
			$aid = media_handle_upload( 'attachment', 0 );
			if ( ! is_wp_error( $aid ) ) {
				$attachment_id = (int) $aid;
			}
		} elseif ( ! empty( $_POST['attachment_id'] ) ) {
			$attachment_id = absint( $_POST['attachment_id'] );
		}

		$r = EzLens_CD_Support_Bridge::create_ticket( $subject, $message, $attachment_id, $priority, $topic );
		if ( is_wp_error( $r ) ) {
			wp_send_json_error( array( 'message' => $r->get_error_message() ) );
		}
		wp_send_json_success(
			array(
				'message'   => 'درخواست شما ثبت شد. تیم پشتیبانی همین‌جا پاسخ می‌دهد.',
				'ticket_id' => (int) $r,
			)
		);
	}

	public function handle_ticket_reply() {
		$this->require_login();
		$this->verify();
		$tid = isset( $_POST['ticket_id'] ) ? absint( $_POST['ticket_id'] ) : 0;
		$msg = isset( $_POST['message'] ) ? (string) wp_unslash( $_POST['message'] ) : '';

		$attachment_id = 0;
		if ( ! empty( $_FILES['attachment'] ) && ! empty( $_FILES['attachment']['name'] ) ) {
			$max = function_exists( 'ezcd_max_upload_bytes' ) ? ezcd_max_upload_bytes() : ( 20 * 1024 * 1024 );
			if ( ! empty( $_FILES['attachment']['size'] ) && (int) $_FILES['attachment']['size'] > $max ) {
				wp_send_json_error( array( 'message' => 'حجم فایل نباید بیشتر از ۲۰ مگابایت باشد' ) );
			}
			require_once ABSPATH . 'wp-admin/includes/file.php';
			require_once ABSPATH . 'wp-admin/includes/media.php';
			require_once ABSPATH . 'wp-admin/includes/image.php';
			$aid = media_handle_upload( 'attachment', 0 );
			if ( ! is_wp_error( $aid ) ) {
				$attachment_id = (int) $aid;
			}
		}

		$r = EzLens_CD_Support_Bridge::add_message( $tid, $msg, $attachment_id );
		if ( is_wp_error( $r ) ) {
			wp_send_json_error( array( 'message' => $r->get_error_message() ) );
		}
		wp_send_json_success( array( 'message' => 'پیام شما ارسال شد' ) );
	}

	public function handle_review_update() {
		$this->require_login();
		$this->verify();
		$id      = isset( $_POST['comment_id'] ) ? absint( $_POST['comment_id'] ) : 0;
		$content = isset( $_POST['content'] ) ? (string) wp_unslash( $_POST['content'] ) : '';
		$rating  = isset( $_POST['rating'] ) ? absint( $_POST['rating'] ) : 0;
		$r       = EzLens_CD_Reviews::update( $id, $content, $rating );
		if ( is_wp_error( $r ) ) {
			wp_send_json_error( array( 'message' => $r->get_error_message() ) );
		}
		wp_send_json_success( array( 'message' => 'نظر به‌روز شد' ) );
	}

	public function handle_review_delete() {
		$this->require_login();
		$this->verify();
		$id = isset( $_POST['comment_id'] ) ? absint( $_POST['comment_id'] ) : 0;
		$r  = EzLens_CD_Reviews::delete( $id );
		if ( is_wp_error( $r ) ) {
			wp_send_json_error( array( 'message' => $r->get_error_message() ) );
		}
		wp_send_json_success( array( 'message' => 'نظر حذف شد' ) );
	}

	public function handle_wallet_deposit() {
		$this->require_login();
		$this->verify();
		$method = isset( $_POST['method'] ) ? sanitize_key( wp_unslash( $_POST['method'] ) ) : 'card';
		$amount_raw = isset( $_POST['amount'] ) ? wp_unslash( $_POST['amount'] ) : '';
		$fa = array( '۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹' );
		$en = array( '0', '1', '2', '3', '4', '5', '6', '7', '8', '9' );
		$amount = (int) preg_replace( '/\D+/', '', str_replace( $fa, $en, (string) $amount_raw ) );
		/* Online gateway — shared helper (ZarinPal / WC gateways) */
		if ( 'online' === $method ) {
			if ( function_exists( 'ezcd_create_wallet_topup_order' ) ) {
				$result = ezcd_create_wallet_topup_order( get_current_user_id(), $amount );
			} else {
				$result = new WP_Error( 'missing', 'ماژول پرداخت آنلاین کیف پول در دسترس نیست' );
			}
			if ( is_wp_error( $result ) ) {
				wp_send_json_error( array( 'message' => $result->get_error_message() ) );
			}
			wp_send_json_success(
				array(
					'message'  => 'در حال انتقال به درگاه پرداخت…',
					'redirect' => $result['redirect'],
					'order_id' => $result['order_id'],
				)
			);
		}

		if ( class_exists( 'EzLens_CD_Wallet_Deposits' ) ) {
			$allowed_methods = EzLens_CD_Wallet_Deposits::methods();
			if ( ! isset( $allowed_methods[ $method ] ) ) {
				wp_send_json_error( array( 'message' => 'این روش شارژ کیف پول در حال حاضر فعال نیست' ) );
			}
		}
		$receipt_id = 0;
		$file_key = '';
		if ( ! empty( $_FILES['receipt']['name'] ) ) {
			$file_key = 'receipt';
		} elseif ( ! empty( $_FILES['receipt_card']['name'] ) ) {
			$file_key = 'receipt_card';
		}
		if ( $file_key ) {
			$max = function_exists( 'ezcd_max_upload_bytes' ) ? ezcd_max_upload_bytes() : ( 20 * 1024 * 1024 );
			if ( ! empty( $_FILES[ $file_key ]['size'] ) && (int) $_FILES[ $file_key ]['size'] > $max ) {
				wp_send_json_error( array( 'message' => 'حجم فیش نباید بیشتر از ۲۰ مگابایت باشد' ) );
			}
			require_once ABSPATH . 'wp-admin/includes/file.php';
			require_once ABSPATH . 'wp-admin/includes/media.php';
			require_once ABSPATH . 'wp-admin/includes/image.php';
			$aid = media_handle_upload( $file_key, 0 );
			if ( is_wp_error( $aid ) ) {
				wp_send_json_error( array( 'message' => $aid->get_error_message() ) );
			}
			$receipt_id = (int) $aid;
		}
		$ref = '';
		if ( ! empty( $_POST['ref_code'] ) ) {
			$ref = sanitize_text_field( wp_unslash( $_POST['ref_code'] ) );
		} elseif ( ! empty( $_POST['ref_code_card'] ) ) {
			$ref = sanitize_text_field( wp_unslash( $_POST['ref_code_card'] ) );
		}
		$data = array(
			'amount'     => $amount_raw,
			'method'     => $method,
			'ref_code'   => $ref,
			'receipt_id' => $receipt_id,
		);
		$r = EzLens_CD_Wallet_Deposits::create( $data );
		if ( is_wp_error( $r ) ) {
			wp_send_json_error( array( 'message' => $r->get_error_message() ) );
		}
		$msg = ( 'bank' === $method )
			? 'درخواست اینترنت‌بانک ثبت شد. پس از تأیید سریع، موجودی شارژ می‌شود.'
			: 'فیش ثبت شد. پس از بررسی، مبلغ به کیف پول اضافه می‌شود.';
		wp_send_json_success( array( 'message' => $msg, 'id' => (int) $r ) );
	}

	public function handle_charity_donate() {
		$this->require_login();
		$this->verify();
		$r = EzLens_CD_Charity::donate( wp_unslash( $_POST ) );
		if ( is_wp_error( $r ) ) {
			wp_send_json_error( array( 'message' => $r->get_error_message() ) );
		}
		wp_send_json_success(
			array(
				'message' => 'کمک شما با موفقیت ثبت شد. از همراهی‌تان سپاسگزاریم.',
				'id'      => (int) $r,
			)
		);
	}
}
