<?php
/**
 * Manager Wallet REST — deposit requests + balance adjust.
 *
 * GET    /ezlens/v1/manager/wallet/deposits
 * GET    /ezlens/v1/manager/wallet/deposits/{id}
 * POST   /ezlens/v1/manager/wallet/deposits/{id}/approve
 * POST   /ezlens/v1/manager/wallet/deposits/{id}/reject
 * POST   /ezlens/v1/manager/wallet/adjust
 * GET    /ezlens/v1/manager/wallet/user/{id}
 * GET    /ezlens/v1/manager/wallet/stats
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class EzLens_Manager_Wallet_REST {

	const NS = 'ezlens/v1';

	public static function init() {
		add_action( 'rest_api_init', array( __CLASS__, 'register_routes' ) );
	}

	public static function can_manage() {
		return current_user_can( 'manage_options' )
			|| current_user_can( 'manage_woocommerce' )
			|| current_user_can( 'edit_users' );
	}

	private static function ensure_classes() {
		$base = defined( 'EZLAUTH_MODULES_DIR' )
			? EZLAUTH_MODULES_DIR . 'customer-dashboard/includes/'
			: dirname( __FILE__, 3 ) . '/customer-dashboard/includes/';
		foreach ( array( 'class-wallet.php', 'class-wallet-deposits.php' ) as $f ) {
			$p = $base . $f;
			if ( is_readable( $p ) ) {
				require_once $p;
			}
		}
		if ( class_exists( 'EzLens_CD_Wallet' ) ) {
			EzLens_CD_Wallet::maybe_create_table();
		}
		if ( class_exists( 'EzLens_CD_Wallet_Deposits' ) ) {
			EzLens_CD_Wallet_Deposits::maybe_create_table();
		}
	}

	public static function register_routes() {
		register_rest_route(
			self::NS,
			'/manager/wallet/config',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( __CLASS__, 'config' ),
				'permission_callback' => array( __CLASS__, 'can_manage' ),
			)
		);

		register_rest_route(
			self::NS,
			'/manager/wallet/deposits',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( __CLASS__, 'list_deposits' ),
				'permission_callback' => array( __CLASS__, 'can_manage' ),
			)
		);
		register_rest_route(
			self::NS,
			'/manager/wallet/deposits/(?P<id>\d+)',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( __CLASS__, 'get_deposit' ),
				'permission_callback' => array( __CLASS__, 'can_manage' ),
			)
		);
		register_rest_route(
			self::NS,
			'/manager/wallet/deposits/(?P<id>\d+)/approve',
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( __CLASS__, 'approve' ),
				'permission_callback' => array( __CLASS__, 'can_manage' ),
			)
		);
		register_rest_route(
			self::NS,
			'/manager/wallet/deposits/(?P<id>\d+)/reject',
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( __CLASS__, 'reject' ),
				'permission_callback' => array( __CLASS__, 'can_manage' ),
			)
		);
		register_rest_route(
			self::NS,
			'/manager/wallet/adjust',
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( __CLASS__, 'adjust' ),
				'permission_callback' => array( __CLASS__, 'can_manage' ),
			)
		);
		register_rest_route(
			self::NS,
			'/manager/wallet/user/(?P<id>\d+)',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( __CLASS__, 'user_wallet' ),
				'permission_callback' => array( __CLASS__, 'can_manage' ),
			)
		);
		register_rest_route(
			self::NS,
			'/manager/wallet/stats',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( __CLASS__, 'stats' ),
				'permission_callback' => array( __CLASS__, 'can_manage' ),
			)
		);
		register_rest_route(
			self::NS,
			'/manager/wallet/search-users',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( __CLASS__, 'search_users' ),
				'permission_callback' => array( __CLASS__, 'can_manage' ),
			)
		);
	}

	public static function config() {
		$keys = array(
			'wallet_bank_name','wallet_account_owner','wallet_account_name','wallet_card_number',
			'wallet_account_number','wallet_iban','wallet_account_note',
		);
		$data = array();
		foreach ( $keys as $key ) {
			$data[ $key ] = class_exists( 'EzLens_Auth_Settings' ) ? (string) EzLens_Auth_Settings::get( $key ) : '';
		}
		return rest_ensure_response(
			array(
				'ok'      => true,
				'methods' => array(
					'online' => ! class_exists( 'EzLens_Auth_Settings' ) || '1' === (string) EzLens_Auth_Settings::get( 'wallet_payment_online_enabled' ),
					'card'   => ! class_exists( 'EzLens_Auth_Settings' ) || '1' === (string) EzLens_Auth_Settings::get( 'wallet_payment_card_enabled' ),
					'bank'   => ! class_exists( 'EzLens_Auth_Settings' ) || '1' === (string) EzLens_Auth_Settings::get( 'wallet_payment_bank_enabled' ),
				),
				'account' => $data,
			)
		);
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

	private static function user_brief( $user_id ) {
		$user = get_userdata( $user_id );
		if ( ! $user ) {
			return array(
				'id'           => (int) $user_id,
				'display_name' => '',
				'email'        => '',
				'login'        => '',
				'phone'        => '',
			);
		}
		$phone = get_user_meta( $user_id, 'billing_phone', true );
		if ( ! $phone ) {
			$phone = get_user_meta( $user_id, 'user_phone', true );
		}
		if ( ! $phone && preg_match( '/^09\d{9}$/', $user->user_login ) ) {
			$phone = $user->user_login;
		}
		return array(
			'id'           => (int) $user_id,
			'display_name' => $user->display_name,
			'email'        => $user->user_email,
			'login'        => $user->user_login,
			'phone'        => (string) $phone,
		);
	}

	private static function method_label( $method ) {
		if ( class_exists( 'EzLens_CD_Wallet_Deposits' ) ) {
			$m = EzLens_CD_Wallet_Deposits::methods();
			if ( isset( $m[ $method ] ) ) {
				return $m[ $method ];
			}
		}
		$map = array(
			'card'   => 'کارت به کارت / فیش',
			'bank'   => 'اینترنت‌بانک',
			'online' => 'درگاه پرداخت',
		);
		return isset( $map[ $method ] ) ? $map[ $method ] : $method;
	}

	private static function map_deposit( $row ) {
		$user = self::user_brief( (int) $row->user_id );
		$receipt_url = '';
		$receipt_id  = (int) ( $row->receipt_id ?? 0 );
		if ( $receipt_id ) {
			$receipt_url = (string) wp_get_attachment_url( $receipt_id );
		}
		$status = (string) $row->status;
		$label  = class_exists( 'EzLens_CD_Wallet_Deposits' )
			? EzLens_CD_Wallet_Deposits::status_label( $status )
			: $status;
		$balance = 0;
		if ( class_exists( 'EzLens_CD_Wallet' ) ) {
			$balance = (int) EzLens_CD_Wallet::balance( (int) $row->user_id );
		}
		return array(
			'id'            => (int) $row->id,
			'user_id'       => (int) $row->user_id,
			'amount'        => (int) $row->amount,
			'method'        => (string) $row->method,
			'method_label'  => self::method_label( $row->method ),
			'ref_code'      => (string) ( $row->ref_code ?? '' ),
			'receipt_id'    => $receipt_id,
			'receipt_url'   => $receipt_url,
			'status'        => $status,
			'status_label'  => $label,
			'admin_note'    => (string) ( $row->admin_note ?? '' ),
			'created_at'    => (string) $row->created_at,
			'created_fa'    => self::to_jalali( $row->created_at ),
			'updated_at'    => (string) ( $row->updated_at ?? '' ),
			'updated_fa'    => self::to_jalali( $row->updated_at ?? '' ),
			'user'          => $user,
			'wallet_balance'=> $balance,
		);
	}

	public static function list_deposits( WP_REST_Request $request ) {
		self::ensure_classes();
		if ( ! class_exists( 'EzLens_CD_Wallet_Deposits' ) ) {
			return new WP_Error( 'no_wallet', 'ماژول کیف پول بارگذاری نشده', array( 'status' => 500 ) );
		}
		global $wpdb;
		$table  = EzLens_CD_Wallet_Deposits::table();
		$page   = max( 1, (int) $request->get_param( 'page' ) );
		$per    = max( 5, min( 50, (int) ( $request->get_param( 'per_page' ) ?: 20 ) ) );
		$status = sanitize_key( $request->get_param( 'status' ) ?: 'all' );
		$search = sanitize_text_field( $request->get_param( 'search' ) ?: '' );
		$method = sanitize_key( $request->get_param( 'method' ) ?: 'all' );

		$where  = array( '1=1' );
		$params = array();
		if ( $status && $status !== 'all' ) {
			$where[]  = 'd.status = %s';
			$params[] = $status;
		}
		if ( $method && $method !== 'all' ) {
			$where[]  = 'd.method = %s';
			$params[] = $method;
		}
		$join = '';
		if ( $search !== '' ) {
			$join     = " LEFT JOIN {$wpdb->users} u ON u.ID = d.user_id ";
			$like     = '%' . $wpdb->esc_like( $search ) . '%';
			$where[]  = '(u.display_name LIKE %s OR u.user_login LIKE %s OR u.user_email LIKE %s OR d.ref_code LIKE %s)';
			$params[] = $like;
			$params[] = $like;
			$params[] = $like;
			$params[] = $like;
		}
		$where_sql = implode( ' AND ', $where );

		$count_sql = "SELECT COUNT(*) FROM {$table} d {$join} WHERE {$where_sql}";
		$total     = (int) ( $params ? $wpdb->get_var( $wpdb->prepare( $count_sql, $params ) ) : $wpdb->get_var( $count_sql ) );

		$offset    = ( $page - 1 ) * $per;
		$list_sql  = "SELECT d.* FROM {$table} d {$join} WHERE {$where_sql} ORDER BY d.id DESC LIMIT %d OFFSET %d";
		$list_args = array_merge( $params, array( $per, $offset ) );
		$rows      = $wpdb->get_results( $wpdb->prepare( $list_sql, $list_args ) );

		$items = array();
		foreach ( (array) $rows as $row ) {
			$items[] = self::map_deposit( $row );
		}

		return rest_ensure_response(
			array(
				'ok'    => true,
				'items' => $items,
				'total' => $total,
				'page'  => $page,
				'pages' => max( 1, (int) ceil( $total / max( 1, $per ) ) ),
			)
		);
	}

	public static function get_deposit( WP_REST_Request $request ) {
		self::ensure_classes();
		if ( ! class_exists( 'EzLens_CD_Wallet_Deposits' ) ) {
			return new WP_Error( 'no_wallet', 'ماژول کیف پول بارگذاری نشده', array( 'status' => 500 ) );
		}
		global $wpdb;
		$id  = (int) $request['id'];
		$row = $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . EzLens_CD_Wallet_Deposits::table() . ' WHERE id = %d', $id ) );
		if ( ! $row ) {
			return new WP_Error( 'not_found', 'درخواست یافت نشد', array( 'status' => 404 ) );
		}
		return rest_ensure_response( array( 'ok' => true, 'deposit' => self::map_deposit( $row ) ) );
	}

	public static function approve( WP_REST_Request $request ) {
		self::ensure_classes();
		if ( ! class_exists( 'EzLens_CD_Wallet_Deposits' ) ) {
			return new WP_Error( 'no_wallet', 'ماژول کیف پول بارگذاری نشده', array( 'status' => 500 ) );
		}
		$id   = (int) $request['id'];
		$note = sanitize_textarea_field( $request->get_param( 'admin_note' ) ?: '' );
		$r    = EzLens_CD_Wallet_Deposits::approve( $id, $note );
		if ( is_wp_error( $r ) ) {
			return $r;
		}
		global $wpdb;
		$row = $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . EzLens_CD_Wallet_Deposits::table() . ' WHERE id = %d', $id ) );
		return rest_ensure_response(
			array(
				'ok'      => true,
				'message' => 'تأیید شد و کیف پول شارژ شد',
				'deposit' => self::map_deposit( $row ),
			)
		);
	}

	public static function reject( WP_REST_Request $request ) {
		self::ensure_classes();
		if ( ! class_exists( 'EzLens_CD_Wallet_Deposits' ) ) {
			return new WP_Error( 'no_wallet', 'ماژول کیف پول بارگذاری نشده', array( 'status' => 500 ) );
		}
		$id   = (int) $request['id'];
		$note = sanitize_textarea_field( $request->get_param( 'admin_note' ) ?: '' );
		EzLens_CD_Wallet_Deposits::reject( $id, $note );
		global $wpdb;
		$row = $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . EzLens_CD_Wallet_Deposits::table() . ' WHERE id = %d', $id ) );
		return rest_ensure_response(
			array(
				'ok'      => true,
				'message' => 'درخواست رد شد',
				'deposit' => self::map_deposit( $row ),
			)
		);
	}

	public static function adjust( WP_REST_Request $request ) {
		self::ensure_classes();
		if ( ! class_exists( 'EzLens_CD_Wallet' ) ) {
			return new WP_Error( 'no_wallet', 'ماژول کیف پول بارگذاری نشده', array( 'status' => 500 ) );
		}
		$user_id = absint( $request->get_param( 'user_id' ) );
		$amount  = absint( preg_replace( '/\D+/', '', (string) $request->get_param( 'amount' ) ) );
		$type    = sanitize_key( $request->get_param( 'type' ) ?: 'credit' ); // credit | debit
		$note    = sanitize_textarea_field( $request->get_param( 'note' ) ?: '' );
		if ( ! $user_id || $amount < 1 ) {
			return new WP_Error( 'invalid', 'کاربر یا مبلغ نامعتبر است', array( 'status' => 400 ) );
		}
		if ( ! in_array( $type, array( 'credit', 'debit' ), true ) ) {
			$type = 'credit';
		}
		$r = EzLens_CD_Wallet::add_entry( $user_id, $amount, $type, 'admin', $note ? $note : 'تعدیل مدیریتی' );
		if ( is_wp_error( $r ) ) {
			return $r;
		}
		$balance = (int) EzLens_CD_Wallet::balance( $user_id );
		return rest_ensure_response(
			array(
				'ok'      => true,
				'message' => $type === 'credit' ? 'شارژ انجام شد' : 'کسر انجام شد',
				'entry_id'=> (int) $r,
				'balance' => $balance,
				'user'    => self::user_brief( $user_id ),
			)
		);
	}

	public static function user_wallet( WP_REST_Request $request ) {
		self::ensure_classes();
		$user_id = (int) $request['id'];
		if ( ! get_userdata( $user_id ) ) {
			return new WP_Error( 'not_found', 'کاربر یافت نشد', array( 'status' => 404 ) );
		}
		$balance = class_exists( 'EzLens_CD_Wallet' ) ? (int) EzLens_CD_Wallet::balance( $user_id ) : 0;
		$history = array();
		if ( class_exists( 'EzLens_CD_Wallet' ) ) {
			$rows = EzLens_CD_Wallet::history( $user_id, 30 );
			foreach ( (array) $rows as $row ) {
				$history[] = array(
					'id'         => (int) $row->id,
					'amount'     => (int) $row->amount,
					'entry_type' => (string) $row->entry_type,
					'reason'     => (string) $row->reason,
					'reason_label' => EzLens_CD_Wallet::reason_label( $row->reason ),
					'note'       => (string) ( $row->note ?? '' ),
					'created_at' => (string) $row->created_at,
					'created_fa' => self::to_jalali( $row->created_at ),
				);
			}
		}
		$deposits = array();
		if ( class_exists( 'EzLens_CD_Wallet_Deposits' ) ) {
			foreach ( EzLens_CD_Wallet_Deposits::for_user( $user_id, 20 ) as $d ) {
				$deposits[] = self::map_deposit( $d );
			}
		}
		return rest_ensure_response(
			array(
				'ok'       => true,
				'user'     => self::user_brief( $user_id ),
				'balance'  => $balance,
				'history'  => $history,
				'deposits' => $deposits,
			)
		);
	}

	public static function stats( WP_REST_Request $request ) {
		self::ensure_classes();
		global $wpdb;
		$pending = 0;
		$approved_sum = 0;
		if ( class_exists( 'EzLens_CD_Wallet_Deposits' ) ) {
			$t       = EzLens_CD_Wallet_Deposits::table();
			$pending = (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$t} WHERE status = %s", 'pending' ) );
			$approved_sum = (int) $wpdb->get_var( $wpdb->prepare( "SELECT COALESCE(SUM(amount),0) FROM {$t} WHERE status = %s", 'approved' ) );
		}
		return rest_ensure_response(
			array(
				'ok'            => true,
				'pending_count' => $pending,
				'approved_sum'  => $approved_sum,
			)
		);
	}

	public static function search_users( WP_REST_Request $request ) {
		self::ensure_classes();
		$q     = sanitize_text_field( $request->get_param( 'search' ) ?: '' );
		$limit = max( 5, min( 30, (int) ( $request->get_param( 'per_page' ) ?: 15 ) ) );
		$args  = array(
			'number'   => $limit,
			'orderby'  => 'registered',
			'order'    => 'DESC',
			'role__in' => array( 'customer', 'subscriber' ),
		);
		if ( $q !== '' ) {
			$args['search']         = '*' . $q . '*';
			$args['search_columns'] = array( 'user_login', 'user_email', 'display_name' );
		}
		$users = get_users( $args );
		$items = array();
		foreach ( $users as $u ) {
			$brief            = self::user_brief( $u->ID );
			$brief['balance'] = class_exists( 'EzLens_CD_Wallet' ) ? (int) EzLens_CD_Wallet::balance( $u->ID ) : 0;
			$items[]          = $brief;
		}
		return rest_ensure_response( array( 'ok' => true, 'items' => $items ) );
	}
}

EzLens_Manager_Wallet_REST::init();
