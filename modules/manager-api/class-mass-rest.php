<?php
/**
 * Manager Mass Message REST — bulk SMS/email to site customers.
 * Uses EzLens_CD_Mass_Actions (not full Campaign).
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class EzLens_Manager_Mass_REST {

	const NS = 'ezlens/v1';

	public static function init() {
		add_action( 'rest_api_init', array( __CLASS__, 'register_routes' ) );
	}

	public static function can_manage() {
		return current_user_can( 'manage_options' )
			|| current_user_can( 'manage_woocommerce' )
			|| current_user_can( 'edit_users' );
	}

	private static function ensure() {
		$paths = array();
		if ( defined( 'EZLAUTH_MODULES_DIR' ) ) {
			$paths[] = EZLAUTH_MODULES_DIR . 'customer-dashboard/includes/class-mass-actions.php';
			$paths[] = EZLAUTH_MODULES_DIR . 'customer-dashboard/includes/class-messaging-bridge.php';
		}
		$base = dirname( __FILE__, 3 ) . '/customer-dashboard/includes/';
		$paths[] = $base . 'class-mass-actions.php';
		$paths[] = $base . 'class-messaging-bridge.php';
		foreach ( $paths as $p ) {
			if ( is_readable( $p ) ) {
				require_once $p;
			}
		}
		if ( class_exists( 'EzLens_CD_Mass_Actions' ) ) {
			EzLens_CD_Mass_Actions::maybe_create_table();
			EzLens_CD_Mass_Actions::maybe_create_queue();
		}
	}

	public static function register_routes() {
		register_rest_route( self::NS, '/manager/mass/presets', array(
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => array( __CLASS__, 'presets' ),
			'permission_callback' => array( __CLASS__, 'can_manage' ),
		) );
		register_rest_route( self::NS, '/manager/mass/preview', array(
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => array( __CLASS__, 'preview' ),
			'permission_callback' => array( __CLASS__, 'can_manage' ),
		) );
		register_rest_route( self::NS, '/manager/mass/send', array(
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => array( __CLASS__, 'send' ),
			'permission_callback' => array( __CLASS__, 'can_manage' ),
		) );
		register_rest_route( self::NS, '/manager/mass/logs', array(
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => array( __CLASS__, 'logs' ),
			'permission_callback' => array( __CLASS__, 'can_manage' ),
		) );
		register_rest_route( self::NS, '/manager/mass/queue', array(
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => array( __CLASS__, 'queue' ),
			'permission_callback' => array( __CLASS__, 'can_manage' ),
		) );
		register_rest_route( self::NS, '/manager/mass/search-users', array(
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => array( __CLASS__, 'search_users' ),
			'permission_callback' => array( __CLASS__, 'can_manage' ),
		) );
	}

	private static function to_jalali( $mysql ) {
		$ts = strtotime( (string) $mysql );
		if ( ! $ts ) {
			return (string) $mysql;
		}
		if ( class_exists( 'EzLens_Inbox' ) && method_exists( 'EzLens_Inbox', 'to_jalali' ) ) {
			return EzLens_Inbox::to_jalali( $ts );
		}
		return date_i18n( 'Y-m-d H:i', $ts );
	}

	public static function presets( WP_REST_Request $request ) {
		self::ensure();
		$items = array(
			array(
				'key'         => 'all_customers',
				'label'       => 'همه مشتریان',
				'description' => 'نقش customer و subscriber',
			),
			array(
				'key'         => 'with_orders',
				'label'       => 'دارای سفارش',
				'description' => 'مشتریان با سفارش تکمیل/در حال انجام',
			),
			array(
				'key'         => 'no_orders',
				'label'       => 'بدون سفارش',
				'description' => 'ثبت‌نام کرده ولی خریدی نداشته',
			),
			array(
				'key'         => 'manual',
				'label'       => 'انتخاب دستی',
				'description' => 'جستجو و انتخاب کاربران خاص',
			),
		);
		$counts = array();
		if ( class_exists( 'EzLens_CD_Mass_Actions' ) ) {
			foreach ( array( 'all_customers', 'with_orders', 'no_orders' ) as $k ) {
				$counts[ $k ] = count( EzLens_CD_Mass_Actions::audience( $k ) );
			}
		}
		return rest_ensure_response(
			array(
				'ok'     => true,
				'items'  => $items,
				'counts' => $counts,
			)
		);
	}

	public static function preview( WP_REST_Request $request ) {
		self::ensure();
		if ( ! class_exists( 'EzLens_CD_Mass_Actions' ) ) {
			return new WP_Error( 'no_mass', 'ماژول پیام جمعی بارگذاری نشده', array( 'status' => 500 ) );
		}
		$preset   = sanitize_key( $request->get_param( 'preset' ) ?: 'all_customers' );
		$user_ids = array_filter( array_map( 'absint', (array) $request->get_param( 'user_ids' ) ) );

		if ( 'manual' === $preset ) {
			$ids = $user_ids;
		} else {
			$ids = EzLens_CD_Mass_Actions::audience( $preset );
		}

		$sample = array();
		foreach ( array_slice( $ids, 0, 8 ) as $uid ) {
			$u = get_userdata( $uid );
			if ( ! $u ) {
				continue;
			}
			$phone = '';
			if ( class_exists( 'EzLens_CD_Messaging_Bridge' ) && method_exists( 'EzLens_CD_Messaging_Bridge', 'user_phone' ) ) {
				$phone = (string) EzLens_CD_Messaging_Bridge::user_phone( $uid );
			}
			if ( ! $phone ) {
				$phone = (string) get_user_meta( $uid, 'billing_phone', true );
			}
			$sample[] = array(
				'id'           => (int) $uid,
				'display_name' => $u->display_name,
				'email'        => $u->user_email,
				'phone'        => $phone,
			);
		}

		return rest_ensure_response(
			array(
				'ok'      => true,
				'count'   => count( $ids ),
				'user_ids'=> array_values( array_map( 'intval', $ids ) ),
				'sample'  => $sample,
			)
		);
	}

	public static function send( WP_REST_Request $request ) {
		self::ensure();
		if ( ! class_exists( 'EzLens_CD_Mass_Actions' ) ) {
			return new WP_Error( 'no_mass', 'ماژول پیام جمعی بارگذاری نشده', array( 'status' => 500 ) );
		}

		$channel = sanitize_key( $request->get_param( 'channel' ) ?: 'sms' );
		if ( ! in_array( $channel, array( 'sms', 'email', 'both' ), true ) ) {
			$channel = 'sms';
		}
		$subject = sanitize_text_field( $request->get_param( 'subject' ) ?: '' );
		$message = sanitize_textarea_field( $request->get_param( 'message' ) ?: '' );
		if ( $message === '' ) {
			return new WP_Error( 'message', 'متن پیام لازم است', array( 'status' => 400 ) );
		}
		if ( ( 'email' === $channel || 'both' === $channel ) && $subject === '' ) {
			$subject = 'پیام از ایزی‌لنز';
		}

		$preset   = sanitize_key( $request->get_param( 'preset' ) ?: 'all_customers' );
		$user_ids = array_filter( array_map( 'absint', (array) $request->get_param( 'user_ids' ) ) );

		if ( 'manual' === $preset ) {
			$ids = $user_ids;
		} else {
			$ids = EzLens_CD_Mass_Actions::audience( $preset );
		}
		$ids = array_values( array_unique( array_filter( $ids ) ) );
		if ( empty( $ids ) ) {
			return new WP_Error( 'empty', 'مخاطبی انتخاب نشده', array( 'status' => 400 ) );
		}

		// Safety cap for single request
		$max = 300;
		if ( count( $ids ) > $max ) {
			$ids = array_slice( $ids, 0, $max );
		}

		$result = EzLens_CD_Mass_Actions::send_to_users( $ids, $channel, $subject, $message );

		return rest_ensure_response(
			array(
				'ok'      => true,
				'message' => sprintf(
					'ارسال انجام شد — موفق: %d | ناموفق: %d',
					(int) $result['success'],
					(int) $result['fail']
				),
				'success' => (int) $result['success'],
				'fail'    => (int) $result['fail'],
				'errors'  => array_slice( (array) $result['errors'], 0, 15 ),
				'target'  => count( $ids ),
			)
		);
	}

	public static function logs( WP_REST_Request $request ) {
		self::ensure();
		$limit = max( 5, min( 50, (int) ( $request->get_param( 'limit' ) ?: 20 ) ) );
		$rows  = class_exists( 'EzLens_CD_Mass_Actions' )
			? EzLens_CD_Mass_Actions::recent_logs( $limit )
			: array();
		$items = array();
		foreach ( (array) $rows as $log ) {
			$admin = get_userdata( (int) $log->admin_id );
			$payload = json_decode( (string) $log->payload, true );
			$items[] = array(
				'id'            => (int) $log->id,
				'action_type'   => (string) $log->action_type,
				'channel'       => (string) $log->channel,
				'target_count'  => (int) $log->target_count,
				'success_count' => (int) $log->success_count,
				'fail_count'    => (int) $log->fail_count,
				'created_at'    => (string) $log->created_at,
				'created_fa'    => self::to_jalali( $log->created_at ),
				'admin_name'    => $admin ? $admin->display_name : '',
				'subject'       => is_array( $payload ) ? (string) ( $payload['subject'] ?? '' ) : '',
				'preview'       => is_array( $payload ) ? (string) ( $payload['message'] ?? '' ) : '',
			);
		}
		return rest_ensure_response( array( 'ok' => true, 'items' => $items ) );
	}

	public static function queue( WP_REST_Request $request ) {
		self::ensure();
		$limit = max( 5, min( 50, (int) ( $request->get_param( 'limit' ) ?: 20 ) ) );
		$rows  = class_exists( 'EzLens_CD_Mass_Actions' )
			? EzLens_CD_Mass_Actions::queue_recent( $limit )
			: array();
		// fallback pending
		if ( empty( $rows ) && class_exists( 'EzLens_CD_Mass_Actions' ) ) {
			$rows = EzLens_CD_Mass_Actions::pending_queue( $limit );
		}
		$items = array();
		foreach ( (array) $rows as $q ) {
			$items[] = array(
				'id'         => (int) $q->id,
				'user_id'    => (int) $q->user_id,
				'channel'    => (string) $q->channel,
				'recipient'  => (string) $q->recipient,
				'status'     => (string) $q->status,
				'error_text' => (string) ( $q->error_text ?? '' ),
				'created_at' => (string) ( $q->created_at ?? '' ),
				'created_fa' => self::to_jalali( $q->created_at ?? '' ),
			);
		}
		return rest_ensure_response( array( 'ok' => true, 'items' => $items ) );
	}

	public static function search_users( WP_REST_Request $request ) {
		$q = sanitize_text_field( $request->get_param( 'q' ) ?: '' );
		if ( strlen( $q ) < 2 ) {
			return rest_ensure_response( array( 'ok' => true, 'items' => array() ) );
		}
		$query = new WP_User_Query(
			array(
				'search'         => '*' . $q . '*',
				'search_columns' => array( 'user_login', 'user_email', 'display_name' ),
				'number'         => 20,
				'fields'         => array( 'ID', 'display_name', 'user_email', 'user_login' ),
			)
		);
		$items = array();
		foreach ( (array) $query->get_results() as $u ) {
			$phone = (string) get_user_meta( $u->ID, 'billing_phone', true );
			if ( ! $phone ) {
				$phone = (string) get_user_meta( $u->ID, 'user_phone', true );
			}
			$items[] = array(
				'id'           => (int) $u->ID,
				'display_name' => $u->display_name,
				'email'        => $u->user_email,
				'login'        => $u->user_login,
				'phone'        => $phone,
			);
		}
		return rest_ensure_response( array( 'ok' => true, 'items' => $items ) );
	}
}

EzLens_Manager_Mass_REST::init();
