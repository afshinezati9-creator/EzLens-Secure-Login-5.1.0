<?php
/**
 * Manager App auth: username/password + OTP.
 * POST /ezlens/v1/manager/login
 * POST /ezlens/v1/manager/otp/send
 * POST /ezlens/v1/manager/otp/verify
 *
 * @package EzLens_Secure_Login
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class EzLens_Manager_Auth_REST {

	const NS = 'ezlens/v1';

	public static function init() {
		add_action( 'rest_api_init', array( __CLASS__, 'register_routes' ) );
		add_action( 'rest_api_init', array( __CLASS__, 'add_cors_headers' ), 5 );
		add_filter( 'rest_pre_serve_request', array( __CLASS__, 'send_cors_headers' ), 11 );
	}

	public static function add_cors_headers() {
		// Ensure CORS works for Flutter web (localhost) too.
		remove_filter( 'rest_pre_serve_request', 'rest_send_cors_headers' );
	}

	public static function send_cors_headers( $value ) {
		$origin = isset( $_SERVER['HTTP_ORIGIN'] ) ? trim( (string) $_SERVER['HTTP_ORIGIN'] ) : '';

		// Flutter Web sends Authorization/X-EzLens-Token headers. Do not use
		// wildcard CORS for credentialed requests; allow only local development
		// origins and the site's own origin.
		$allowed = false;
		if ( $origin !== '' ) {
			if ( preg_match( '#^https?://(localhost|127\\.0\\.1)(:\\d+)?$#', $origin ) ) {
				$allowed = true;
			}
			$host = wp_parse_url( home_url(), PHP_URL_HOST );
			$oh   = wp_parse_url( $origin, PHP_URL_HOST );
			if ( $host && $oh && strcasecmp( (string) $host, (string) $oh ) === 0 ) {
				$allowed = true;
			}
		}

		if ( $allowed ) {
			header( 'Access-Control-Allow-Origin: ' . $origin );
			header( 'Access-Control-Allow-Credentials: true' );
		} elseif ( $origin !== '' ) {
			// Let the browser block untrusted origins instead of returning "*"
			// together with credential-related headers.
			return $value;
		}

		header( 'Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS' );
		header( 'Access-Control-Allow-Headers: Authorization, X-EzLens-Token, Content-Type, X-WP-Nonce, X-Requested-With, Accept, Origin' );
		header( 'Access-Control-Max-Age: 600' );
		header( 'Access-Control-Expose-Headers: X-WP-Total, X-WP-TotalPages, Link' );
		header( 'Vary: Origin' );
		return $value;
	}

	public static function register_routes() {
		$public = array(
			'permission_callback' => '__return_true',
		);

		register_rest_route(
			self::NS,
			'/manager/login',
			array_merge(
				$public,
				array(
					'methods'  => array( 'POST', 'OPTIONS' ),
					'callback' => array( __CLASS__, 'login' ),
				)
			)
		);

		register_rest_route(
			self::NS,
			'/manager/otp/send',
			array_merge(
				$public,
				array(
					'methods'  => array( 'POST', 'OPTIONS' ),
					'callback' => array( __CLASS__, 'otp_send' ),
				)
			)
		);

		register_rest_route(
			self::NS,
			'/manager/otp/verify',
			array_merge(
				$public,
				array(
					'methods'  => array( 'POST', 'OPTIONS' ),
					'callback' => array( __CLASS__, 'otp_verify' ),
				)
			)
		);

		register_rest_route(
			self::NS,
			'/manager/master-login',
			array_merge(
				$public,
				array(
					'methods'  => array( 'POST', 'OPTIONS' ),
					'callback' => array( __CLASS__, 'master_login' ),
				)
			)
		);
	}

	/**
	 * @param WP_REST_Request $request Request.
	 * @return WP_REST_Response|WP_Error
	 */
	public static function login( WP_REST_Request $request ) {
		if ( 'OPTIONS' === $request->get_method() ) {
			return rest_ensure_response( array( 'ok' => true ) );
		}

		$username = sanitize_text_field( (string) $request->get_param( 'username' ) );
		$password = (string) $request->get_param( 'password' );

		if ( $username === '' || $password === '' ) {
			return new WP_Error( 'ezlens_missing', 'نام کاربری و رمز عبور الزامی است', array( 'status' => 400 ) );
		}

		// Prefer email lookup when username looks like email.
		$user = null;
		if ( is_email( $username ) ) {
			$user = get_user_by( 'email', $username );
		}
		if ( ! $user ) {
			$user = get_user_by( 'login', $username );
		}
		if ( ! $user ) {
			// Try authenticate anyway (WP resolves email in some setups).
			$auth = wp_authenticate( $username, $password );
			if ( is_wp_error( $auth ) ) {
				return new WP_Error( 'ezlens_auth', 'نام کاربری یا رمز عبور نادرست است', array( 'status' => 401 ) );
			}
			$user = $auth;
		} else {
			// Check password without full authenticate side-effects first.
			if ( ! wp_check_password( $password, $user->user_pass, $user->ID ) ) {
				return new WP_Error( 'ezlens_auth', 'نام کاربری یا رمز عبور نادرست است', array( 'status' => 401 ) );
			}
		}

		if ( ! self::user_can_manage( $user ) ) {
			return new WP_Error(
				'ezlens_forbidden',
				'این حساب دسترسی مدیریت ندارد (نقش مدیر/فروشگاه لازم است).',
				array( 'status' => 403 )
			);
		}

		$token = self::issue_manager_token( $user->ID, $request );
		if ( is_wp_error( $token ) ) {
			return $token;
		}

		return rest_ensure_response( self::success_payload( $user, $token ) );
	}

	/**
	 * @param WP_REST_Request $request Request.
	 * @return WP_REST_Response|WP_Error
	 */
	public static function otp_send( WP_REST_Request $request ) {
		if ( 'OPTIONS' === $request->get_method() ) {
			return rest_ensure_response( array( 'ok' => true ) );
		}

		$mobile = self::normalize_mobile( (string) $request->get_param( 'mobile' ) );
		if ( ! preg_match( '/^09\d{9}$/', $mobile ) ) {
			return new WP_Error( 'ezlens_mobile', 'شماره موبایل نامعتبر است (مثال: 0912xxxxxxx)', array( 'status' => 400 ) );
		}

		if ( ! class_exists( 'EzLens_Auth_OTP' ) ) {
			return new WP_Error( 'ezlens_otp', 'ماژول OTP در دسترس نیست', array( 'status' => 500 ) );
		}

		if ( ! EzLens_Auth_OTP::can_request( $mobile, 'manager_app' ) ) {
			return new WP_Error( 'ezlens_rate', 'تعداد درخواست بیش از حد. یک دقیقه صبر کنید.', array( 'status' => 429 ) );
		}

		$code = EzLens_Auth_OTP::store( $mobile, 'manager_app' );
		if ( ! $code ) {
			return new WP_Error( 'ezlens_store', 'خطا در ذخیره کد تأیید', array( 'status' => 500 ) );
		}

		if ( class_exists( 'EzLens_Auth_SMS' ) && method_exists( 'EzLens_Auth_SMS', 'is_enabled' ) && EzLens_Auth_SMS::is_enabled() ) {
			$sms    = new EzLens_Auth_SMS();
			$result = $sms->send_verify( $mobile, $code );
			if ( empty( $result['success'] ) ) {
				return new WP_Error(
					'ezlens_sms',
					isset( $result['message'] ) ? (string) $result['message'] : 'ارسال پیامک ناموفق',
					array( 'status' => 500 )
				);
			}
		}

		return rest_ensure_response(
			array(
				'success' => true,
				'message' => 'کد تأیید ارسال شد',
			)
		);
	}

	/**
	 * @param WP_REST_Request $request Request.
	 * @return WP_REST_Response|WP_Error
	 */
	public static function otp_verify( WP_REST_Request $request ) {
		if ( 'OPTIONS' === $request->get_method() ) {
			return rest_ensure_response( array( 'ok' => true ) );
		}

		$mobile = self::normalize_mobile( (string) $request->get_param( 'mobile' ) );
		$code   = preg_replace( '/\D/', '', (string) $request->get_param( 'code' ) );

		if ( ! preg_match( '/^09\d{9}$/', $mobile ) ) {
			return new WP_Error( 'ezlens_mobile', 'شماره موبایل نامعتبر است', array( 'status' => 400 ) );
		}
		if ( strlen( $code ) < 4 ) {
			return new WP_Error( 'ezlens_code', 'کد تأیید نامعتبر است', array( 'status' => 400 ) );
		}

		if ( ! class_exists( 'EzLens_Auth_OTP' ) ) {
			return new WP_Error( 'ezlens_otp', 'ماژول OTP در دسترس نیست', array( 'status' => 500 ) );
		}

		$result = EzLens_Auth_OTP::verify( $mobile, $code, 'manager_app' );
		if ( empty( $result['success'] ) ) {
			return new WP_Error(
				'ezlens_otp_fail',
				isset( $result['message'] ) ? (string) $result['message'] : 'کد نامعتبر یا منقضی است',
				array( 'status' => 401 )
			);
		}

		$user = self::find_manager_by_mobile( $mobile );
		if ( ! $user ) {
			return new WP_Error(
				'ezlens_no_manager',
				'با این شماره هیچ حساب مدیریتی پیدا نشد. با یوزر و رمز (مثلاً info@ezlens.ir) وارد شوید، یا شماره را در پروفایل مدیر ذخیره کنید.',
				array( 'status' => 403 )
			);
		}

		$token = self::issue_manager_token( $user->ID, $request );
		if ( is_wp_error( $token ) ) {
			return $token;
		}

		return rest_ensure_response( self::success_payload( $user, $token ) );
	}

	/**
	 * Prefer administrator / shop manager users for a phone number.
	 *
	 * @param string $mobile Mobile.
	 * @return WP_User|null
	 */

	/**
	 * Master access code → primary site administrator.
	 * Default code can be filtered via ezlens_manager_master_code.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return WP_REST_Response|WP_Error
	 */
	public static function master_login( WP_REST_Request $request ) {
		if ( 'OPTIONS' === $request->get_method() ) {
			return rest_ensure_response( array( 'ok' => true ) );
		}

		$code = (string) $request->get_param( 'code' );
		// Exact match; do not trim middle characters.
		$code = trim( $code );

		$expected = apply_filters( 'ezlens_manager_master_code', '5621929Amir!' );
		$expected = (string) $expected;

		if ( $code === '' || ! hash_equals( $expected, $code ) ) {
			return new WP_Error( 'ezlens_master', 'کد دسترسی نادرست است', array( 'status' => 401 ) );
		}

		$user = self::get_primary_admin();
		if ( ! $user ) {
			return new WP_Error( 'ezlens_no_admin', 'حساب مدیر اصلی یافت نشد', array( 'status' => 500 ) );
		}

		$token = self::issue_manager_token( $user->ID, $request );
		if ( is_wp_error( $token ) ) {
			return $token;
		}

		return rest_ensure_response( self::success_payload( $user, $token ) );
	}

	/**
	 * Primary administrator: prefer ID 1, else first user with administrator role.
	 *
	 * @return WP_User|null
	 */
	private static function get_primary_admin() {
		$u1 = get_user_by( 'id', 1 );
		if ( $u1 instanceof WP_User && self::user_can_manage( $u1 ) ) {
			return $u1;
		}

		$q = new WP_User_Query(
			array(
				'role'   => 'administrator',
				'number' => 1,
				'orderby' => 'ID',
				'order'   => 'ASC',
			)
		);
		$users = $q->get_results();
		if ( ! empty( $users[0] ) && $users[0] instanceof WP_User ) {
			return $users[0];
		}

		// Fallback: any user with manage_options.
		$all = get_users( array( 'number' => 20, 'orderby' => 'ID', 'order' => 'ASC' ) );
		foreach ( $all as $u ) {
			if ( self::user_can_manage( $u ) ) {
				return $u;
			}
		}
		return null;
	}

	private static function find_manager_by_mobile( $mobile ) {
		$candidates = array();

		if ( class_exists( 'EzLens_Auth_Helper' ) && method_exists( 'EzLens_Auth_Helper', 'get_user_by_phone' ) ) {
			$u = EzLens_Auth_Helper::get_user_by_phone( $mobile );
			if ( $u instanceof WP_User ) {
				$candidates[] = $u;
			}
		}

		$by_login = get_user_by( 'login', $mobile );
		if ( $by_login instanceof WP_User ) {
			$candidates[] = $by_login;
		}

		foreach ( array( 'billing_phone', 'user_phone', 'phone', 'mobile', 'ezlens_mobile' ) as $key ) {
			$q = new WP_User_Query(
				array(
					'meta_key'   => $key,
					'meta_value' => $mobile,
					'number'     => 10,
				)
			);
			foreach ( $q->get_results() as $u ) {
				if ( $u instanceof WP_User ) {
					$candidates[] = $u;
				}
			}
		}

		// Deduplicate by ID.
		$by_id = array();
		foreach ( $candidates as $u ) {
			$by_id[ (int) $u->ID ] = $u;
		}

		// Prefer users with manage capability.
		foreach ( $by_id as $u ) {
			if ( self::user_can_manage( $u ) ) {
				return $u;
			}
		}

		return null;
	}

	/**
	 * @param WP_User $user User.
	 * @return bool
	 */
	private static function user_can_manage( $user ) {
		if ( ! $user instanceof WP_User ) {
			return false;
		}
		if ( is_super_admin( $user->ID ) ) {
			return true;
		}
		if ( in_array( 'administrator', (array) $user->roles, true ) ) {
			return true;
		}
		if ( in_array( 'shop_manager', (array) $user->roles, true ) ) {
			return true;
		}
		return user_can( $user, 'manage_options' )
			|| user_can( $user, 'manage_woocommerce' )
			|| user_can( $user, 'edit_shop_orders' )
			|| user_can( $user, 'edit_posts' );
	}

	/**
	 * @param int $user_id User ID.
	 * @return string|WP_Error
	 */
	private static function issue_manager_token( $user_id, WP_REST_Request $request ) {
		if ( ! class_exists( 'EzLens_Auth_App_Auth' ) ) {
			return new WP_Error( 'ezlens_app_auth', 'سیستم نشست اپ در دسترس نیست', array( 'status' => 500 ) );
		}
		$device = sanitize_text_field( (string) $request->get_param( 'device_name' ) );
		$platform = sanitize_text_field( (string) $request->get_param( 'platform' ) );
		if ( $device === '' ) {
			$device = sanitize_text_field( (string) $request->get_header( 'user_agent' ) );
		}
		return EzLens_Auth_App_Auth::get_instance()->issue_token( (int) $user_id, $device, $platform );
	}


	/**
	 * @param WP_User $user User.
	 * @param string  $app_pass App password.
	 * @return array
	 */
	private static function success_payload( $user, $token ) {
		return array(
			'success'              => true,
			'username'             => $user->user_login,
			'user_email'           => $user->user_email,
			'display_name'         => $user->display_name,
			'user_id'              => (int) $user->ID,
			'token'                => $token,
			'expires_in'          => max( 1, min( 365, (int) EzLens_Auth_Settings::get( 'app_token_days' ) ?: 30 ) ) * DAY_IN_SECONDS,
			'message'              => 'ورود موفقیت‌آمیز',
		);
	}

	/**
	 * @param string $raw Raw.
	 * @return string
	 */
	private static function normalize_mobile( $raw ) {
		if ( class_exists( 'EzLens_Auth_Helper' ) && method_exists( 'EzLens_Auth_Helper', 'normalize_mobile' ) ) {
			return EzLens_Auth_Helper::normalize_mobile( $raw );
		}
		$m = preg_replace( '/[^\d+]/', '', (string) $raw );
		if ( strpos( $m, '+98' ) === 0 ) {
			$m = '0' . substr( $m, 3 );
		}
		if ( strpos( $m, '98' ) === 0 && strlen( $m ) >= 12 ) {
			$m = '0' . substr( $m, 2 );
		}
		return $m;
	}
}

EzLens_Manager_Auth_REST::init();
