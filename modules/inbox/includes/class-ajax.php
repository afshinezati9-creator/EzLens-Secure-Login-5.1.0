<?php
/**
 * AJAX صندوق ایمیل‌ها
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class EzLens_Inbox_Ajax {

	public static function init() {
		$actions = array(
			'ezlens_inbox_save_settings',
			'ezlens_inbox_list',
			'ezlens_inbox_message',
			'ezlens_inbox_delete',
			'ezlens_inbox_test',
			'ezlens_inbox_attachment',
			'ezlens_inbox_reply',
		);
		foreach ( $actions as $a ) {
			add_action( 'wp_ajax_' . $a, array( __CLASS__, str_replace( 'ezlens_inbox_', 'handle_', $a ) ) );
		}
	}

	private static function guard() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => 'دسترسی غیرمجاز' ), 403 );
		}
		check_ajax_referer( 'ezlens_inbox_nonce', 'nonce' );
	}

	public static function handle_save_settings() {
		self::guard();
		if ( ! class_exists( 'EzLens_Inbox' ) ) {
			wp_send_json_error( array( 'message' => 'کلاس Inbox لود نشده' ) );
		}
		EzLens_Inbox::save_settings( wp_unslash( $_POST ) );
		wp_send_json_success(
			array(
				'message'  => 'تنظیمات ذخیره شد',
				'has_pass' => EzLens_Inbox::has_password(),
			)
		);
	}

	public static function handle_list() {
		self::guard();
		$folder = isset( $_POST['folder'] ) ? sanitize_key( $_POST['folder'] ) : 'inbox';
		$page   = isset( $_POST['page'] ) ? absint( $_POST['page'] ) : 1;
		$search = isset( $_POST['search'] ) ? sanitize_text_field( wp_unslash( $_POST['search'] ) ) : '';
		$filter = isset( $_POST['filter'] ) ? sanitize_key( $_POST['filter'] ) : 'all';
		$r      = EzLens_Inbox::fetch_list( $folder, $page, 20, $search, $filter );
		if ( empty( $r['ok'] ) ) {
			wp_send_json_error( array( 'message' => $r['message'] ?? 'خطا' ) );
		}
		wp_send_json_success( $r );
	}

	public static function handle_message() {
		self::guard();
		$uid    = isset( $_POST['uid'] ) ? absint( $_POST['uid'] ) : 0;
		$folder = isset( $_POST['folder'] ) ? sanitize_key( $_POST['folder'] ) : 'inbox';
		$r      = EzLens_Inbox::fetch_message( $uid, $folder );
		if ( empty( $r['ok'] ) ) {
			wp_send_json_error( array( 'message' => $r['message'] ?? 'خطا' ) );
		}
		wp_send_json_success( $r );
	}

	public static function handle_delete() {
		self::guard();
		$uid    = isset( $_POST['uid'] ) ? absint( $_POST['uid'] ) : 0;
		$folder = isset( $_POST['folder'] ) ? sanitize_key( $_POST['folder'] ) : 'inbox';
		$r      = EzLens_Inbox::delete_message( $uid, $folder );
		if ( empty( $r['ok'] ) ) {
			wp_send_json_error( array( 'message' => $r['message'] ?? 'حذف ناموفق' ) );
		}
		wp_send_json_success( $r );
	}

	public static function handle_test() {
		self::guard();
		$r = EzLens_Inbox::test_connection();
		if ( empty( $r['ok'] ) ) {
			wp_send_json_error( $r );
		}
		wp_send_json_success( $r );
	}

	public static function handle_reply() {
		self::guard();
		$to = isset( $_POST['to'] ) ? sanitize_email( wp_unslash( $_POST['to'] ) ) : '';
		$subject = isset( $_POST['subject'] ) ? sanitize_text_field( wp_unslash( $_POST['subject'] ) ) : '';
		$body = isset( $_POST['body'] ) ? wp_kses_post( wp_unslash( $_POST['body'] ) ) : '';
		if ( ! is_email( $to ) || $body === '' ) {
			wp_send_json_error( array( 'message' => 'گیرنده یا متن نامعتبر است' ) );
		}
		if ( $subject && stripos( $subject, 'Re:' ) !== 0 ) {
			$subject = 'Re: ' . $subject;
		}
		$headers = array( 'Content-Type: text/html; charset=UTF-8' );
		$s = EzLens_Inbox::get_settings();
		if ( ! empty( $s['username'] ) ) {
			$headers[] = 'From: ' . $s['username'];
		}
		$ok = wp_mail( $to, $subject ? $subject : 'پاسخ', nl2br( $body ), $headers );
		if ( ! $ok ) {
			wp_send_json_error( array( 'message' => 'ارسال ناموفق — SMTP را بررسی کنید' ) );
		}
		wp_send_json_success( array( 'message' => 'پاسخ ارسال شد' ) );
	}

	public static function handle_attachment() {
		self::guard();
		$uid    = isset( $_REQUEST['uid'] ) ? absint( $_REQUEST['uid'] ) : 0;
		$part   = isset( $_REQUEST['part'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['part'] ) ) : '1';
		$folder = isset( $_REQUEST['folder'] ) ? sanitize_key( $_REQUEST['folder'] ) : 'inbox';
		$r      = EzLens_Inbox::get_attachment( $uid, $part, $folder );
		if ( is_wp_error( $r ) ) {
			wp_die( esc_html( $r->get_error_message() ) );
		}
		$filename = $r['filename'];
		$data     = $r['data'];
		nocache_headers();
		header( 'Content-Type: application/octet-stream' );
		header( 'Content-Disposition: attachment; filename="' . rawurlencode( $filename ) . '"' );
		header( 'Content-Length: ' . strlen( $data ) );
		echo $data; // phpcs:ignore
		exit;
	}
}

EzLens_Inbox_Ajax::init();
