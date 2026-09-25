<?php
/**
 * Manager Inbox REST — wraps EzLens_Inbox for Flutter app.
 * Namespace: ezlens/v1
 *
 * GET    /manager/inbox                 list
 * GET    /manager/inbox/{uid}           message
 * DELETE /manager/inbox/{uid}           delete
 * POST   /manager/inbox/{uid}/reply     reply
 * GET    /manager/inbox/{uid}/attachment/{part}
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class EzLens_Manager_Inbox_REST {

	const NS = 'ezlens/v1';

	public static function init() {
		add_action( 'rest_api_init', array( __CLASS__, 'register_routes' ) );
	}

	public static function can_manage() {
		return current_user_can( 'manage_options' )
			|| current_user_can( 'manage_woocommerce' )
			|| current_user_can( 'edit_others_posts' );
	}

	public static function register_routes() {
		register_rest_route(
			self::NS,
			'/manager/inbox',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( __CLASS__, 'list_messages' ),
				'permission_callback' => array( __CLASS__, 'can_manage' ),
			)
		);
		register_rest_route(
			self::NS,
			'/manager/inbox/(?P<uid>\d+)',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( __CLASS__, 'get_message' ),
					'permission_callback' => array( __CLASS__, 'can_manage' ),
				),
				array(
					'methods'             => WP_REST_Server::DELETABLE,
					'callback'            => array( __CLASS__, 'delete_message' ),
					'permission_callback' => array( __CLASS__, 'can_manage' ),
				),
			)
		);
		register_rest_route(
			self::NS,
			'/manager/inbox/(?P<uid>\d+)/reply',
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( __CLASS__, 'reply_message' ),
				'permission_callback' => array( __CLASS__, 'can_manage' ),
			)
		);
		register_rest_route(
			self::NS,
			'/manager/inbox/(?P<uid>\d+)/attachment/(?P<part>[A-Za-z0-9.]+)',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( __CLASS__, 'download_attachment' ),
				'permission_callback' => array( __CLASS__, 'can_manage' ),
			)
		);
	}

	private static function ensure_inbox() {
		if ( ! class_exists( 'EzLens_Inbox' ) ) {
			$path = defined( 'EZLAUTH_MODULES_DIR' )
				? EZLAUTH_MODULES_DIR . 'inbox/includes/class-inbox.php'
				: dirname( __FILE__, 3 ) . '/inbox/includes/class-inbox.php';
			if ( is_readable( $path ) ) {
				require_once $path;
			}
		}
		return class_exists( 'EzLens_Inbox' );
	}

	public static function list_messages( WP_REST_Request $request ) {
		if ( ! self::ensure_inbox() ) {
			return new WP_Error( 'no_inbox', 'ماژول صندوق ایمیل بارگذاری نشده', array( 'status' => 500 ) );
		}
		$folder  = sanitize_key( $request->get_param( 'folder' ) ?: 'inbox' );
		$page    = max( 1, (int) $request->get_param( 'page' ) );
		$per     = max( 5, min( 50, (int) ( $request->get_param( 'per_page' ) ?: 20 ) ) );
		$search  = sanitize_text_field( $request->get_param( 'search' ) ?: '' );
		$filter  = sanitize_key( $request->get_param( 'filter' ) ?: 'all' );

		$result = EzLens_Inbox::fetch_list( $folder, $page, $per, $search, $filter );
		if ( empty( $result['ok'] ) ) {
			return new WP_Error(
				'inbox_error',
				isset( $result['message'] ) ? $result['message'] : 'خطا در دریافت لیست',
				array( 'status' => 502 )
			);
		}
		return rest_ensure_response( $result );
	}

	public static function get_message( WP_REST_Request $request ) {
		if ( ! self::ensure_inbox() ) {
			return new WP_Error( 'no_inbox', 'ماژول صندوق ایمیل بارگذاری نشده', array( 'status' => 500 ) );
		}
		$uid    = (int) $request['uid'];
		$folder = sanitize_key( $request->get_param( 'folder' ) ?: 'inbox' );
		$result = EzLens_Inbox::fetch_message( $uid, $folder );
		if ( is_wp_error( $result ) ) {
			return $result;
		}
		if ( empty( $result['ok'] ) ) {
			return new WP_Error(
				'inbox_error',
				isset( $result['message'] ) ? $result['message'] : 'پیام یافت نشد',
				array( 'status' => 404 )
			);
		}
		return rest_ensure_response( $result );
	}

	public static function delete_message( WP_REST_Request $request ) {
		if ( ! self::ensure_inbox() ) {
			return new WP_Error( 'no_inbox', 'ماژول صندوق ایمیل بارگذاری نشده', array( 'status' => 500 ) );
		}
		$uid    = (int) $request['uid'];
		$folder = sanitize_key( $request->get_param( 'folder' ) ?: 'inbox' );
		$result = EzLens_Inbox::delete_message( $uid, $folder );
		if ( is_wp_error( $result ) ) {
			return $result;
		}
		if ( empty( $result['ok'] ) && false === $result ) {
			return new WP_Error( 'delete_failed', 'حذف ناموفق', array( 'status' => 500 ) );
		}
		return rest_ensure_response( array( 'ok' => true, 'message' => 'حذف شد' ) );
	}

	public static function reply_message( WP_REST_Request $request ) {
		if ( ! self::ensure_inbox() ) {
			return new WP_Error( 'no_inbox', 'ماژول صندوق ایمیل بارگذاری نشده', array( 'status' => 500 ) );
		}
		$to      = sanitize_email( $request->get_param( 'to' ) );
		$subject = sanitize_text_field( $request->get_param( 'subject' ) ?: '' );
		$body    = wp_kses_post( $request->get_param( 'body' ) ?: '' );
		if ( ! is_email( $to ) || $body === '' ) {
			return new WP_Error( 'invalid', 'گیرنده یا متن نامعتبر است', array( 'status' => 400 ) );
		}
		if ( $subject && stripos( $subject, 'Re:' ) !== 0 ) {
			$subject = 'Re: ' . $subject;
		}
		$headers = array( 'Content-Type: text/html; charset=UTF-8' );
		$s       = EzLens_Inbox::get_settings();
		if ( ! empty( $s['username'] ) ) {
			$headers[] = 'From: ' . $s['username'];
		}
		$ok = wp_mail( $to, $subject ? $subject : 'پاسخ', nl2br( $body ), $headers );
		if ( ! $ok ) {
			return new WP_Error( 'mail_failed', 'ارسال ناموفق — SMTP را بررسی کنید', array( 'status' => 502 ) );
		}
		return rest_ensure_response( array( 'ok' => true, 'message' => 'پاسخ ارسال شد' ) );
	}

	public static function download_attachment( WP_REST_Request $request ) {
		if ( ! self::ensure_inbox() ) {
			return new WP_Error( 'no_inbox', 'ماژول صندوق ایمیل بارگذاری نشده', array( 'status' => 500 ) );
		}
		$uid    = (int) $request['uid'];
		$part   = sanitize_text_field( $request['part'] );
		$folder = sanitize_key( $request->get_param( 'folder' ) ?: 'inbox' );
		$r      = EzLens_Inbox::get_attachment( $uid, $part, $folder );
		if ( is_wp_error( $r ) ) {
			return $r;
		}
		$filename = isset( $r['filename'] ) ? $r['filename'] : 'attachment.bin';
		$data     = isset( $r['data'] ) ? $r['data'] : '';
		$response = new WP_REST_Response( $data );
		$response->header( 'Content-Type', 'application/octet-stream' );
		$response->header( 'Content-Disposition', 'attachment; filename="' . rawurlencode( $filename ) . '"' );
		$response->header( 'Content-Length', (string) strlen( $data ) );
		// Binary body: use filter to send raw
		add_filter(
			'rest_pre_serve_request',
			function ( $served, $result, $request, $server ) use ( $data, $filename ) {
				if ( $served ) {
					return $served;
				}
				// only for our route
				if ( strpos( $request->get_route(), '/manager/inbox/' ) === false || strpos( $request->get_route(), '/attachment/' ) === false ) {
					return $served;
				}
				nocache_headers();
				header( 'Content-Type: application/octet-stream' );
				header( 'Content-Disposition: attachment; filename="' . rawurlencode( $filename ) . '"' );
				header( 'Content-Length: ' . strlen( $data ) );
				echo $data; // phpcs:ignore
				return true;
			},
			10,
			4
		);
		return $response;
	}
}

EzLens_Manager_Inbox_REST::init();
