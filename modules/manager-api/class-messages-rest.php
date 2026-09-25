<?php
/**
 * Manager single messaging REST — email/SMS via Campaign Messaging module.
 *
 * GET    /ezlens/v1/manager/messages
 * POST   /ezlens/v1/manager/messages          send
 * DELETE /ezlens/v1/manager/messages/{id}
 * GET    /ezlens/v1/manager/messages/customers search recipients
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class EzLens_Manager_Messages_REST {

	const NS    = 'ezlens/v1';
	const TABLE = 'ezlens_manager_messages';

	public static function init() {
		add_action( 'rest_api_init', array( __CLASS__, 'register_routes' ) );
		add_action( 'init', array( __CLASS__, 'maybe_create_table' ), 20 );
	}

	public static function table() {
		global $wpdb;
		return $wpdb->prefix . self::TABLE;
	}

	public static function maybe_create_table() {
		global $wpdb;
		$table   = self::table();
		$charset = $wpdb->get_charset_collate();
		$sql     = "CREATE TABLE {$table} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			channel varchar(16) NOT NULL DEFAULT 'email',
			recipient varchar(191) NOT NULL DEFAULT '',
			recipient_name varchar(191) NOT NULL DEFAULT '',
			user_id bigint(20) unsigned NOT NULL DEFAULT 0,
			subject varchar(255) NOT NULL DEFAULT '',
			body longtext NOT NULL,
			attachment_url varchar(500) NOT NULL DEFAULT '',
			status varchar(32) NOT NULL DEFAULT 'sent',
			error_message text NULL,
			created_at datetime NOT NULL,
			created_by bigint(20) unsigned NOT NULL DEFAULT 0,
			PRIMARY KEY  (id),
			KEY channel (channel),
			KEY user_id (user_id),
			KEY created_at (created_at)
		) {$charset};";
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );
	}

	public static function can_manage() {
		return current_user_can( 'manage_options' )
			|| current_user_can( 'manage_woocommerce' )
			|| current_user_can( 'edit_users' );
	}

	public static function register_routes() {
		register_rest_route(
			self::NS,
			'/manager/messages',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( __CLASS__, 'list_messages' ),
					'permission_callback' => array( __CLASS__, 'can_manage' ),
				),
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( __CLASS__, 'send_message' ),
					'permission_callback' => array( __CLASS__, 'can_manage' ),
				),
			)
		);
		register_rest_route(
			self::NS,
			'/manager/messages/(?P<id>\d+)',
			array(
				'methods'             => WP_REST_Server::DELETABLE,
				'callback'            => array( __CLASS__, 'delete_message' ),
				'permission_callback' => array( __CLASS__, 'can_manage' ),
			)
		);
		register_rest_route(
			self::NS,
			'/manager/messages/customers',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( __CLASS__, 'search_customers' ),
				'permission_callback' => array( __CLASS__, 'can_manage' ),
			)
		);
	}

	private static function to_jalali( $mysql ) {
		$ts = strtotime( $mysql );
		if ( ! $ts ) {
			return (string) $mysql;
		}
		if ( class_exists( 'EzLens_Inbox' ) && method_exists( 'EzLens_Inbox', 'to_jalali' ) ) {
			return EzLens_Inbox::to_jalali( $ts );
		}
		return date_i18n( 'Y-m-d H:i', $ts );
	}

	public static function list_messages( WP_REST_Request $request ) {
		global $wpdb;
		self::maybe_create_table();
		$table  = self::table();
		$page   = max( 1, (int) $request->get_param( 'page' ) );
		$per    = max( 5, min( 50, (int) ( $request->get_param( 'per_page' ) ?: 20 ) ) );
		$search = sanitize_text_field( $request->get_param( 'search' ) ?: '' );
		$channel = sanitize_key( $request->get_param( 'channel' ) ?: 'all' );
		$from   = sanitize_text_field( $request->get_param( 'from' ) ?: '' );
		$to     = sanitize_text_field( $request->get_param( 'to' ) ?: '' );

		$where  = array( '1=1' );
		$params = array();
		if ( $channel && $channel !== 'all' ) {
			$where[]  = 'channel = %s';
			$params[] = $channel;
		}
		if ( $search !== '' ) {
			$like     = '%' . $wpdb->esc_like( $search ) . '%';
			$where[]  = '(recipient LIKE %s OR recipient_name LIKE %s OR subject LIKE %s OR body LIKE %s)';
			$params[] = $like;
			$params[] = $like;
			$params[] = $like;
			$params[] = $like;
		}
		if ( $from !== '' ) {
			$where[]  = 'created_at >= %s';
			$params[] = $from . ' 00:00:00';
		}
		if ( $to !== '' ) {
			$where[]  = 'created_at <= %s';
			$params[] = $to . ' 23:59:59';
		}
		$where_sql = implode( ' AND ', $where );

		$count_sql = "SELECT COUNT(*) FROM {$table} WHERE {$where_sql}";
		$total     = (int) ( $params ? $wpdb->get_var( $wpdb->prepare( $count_sql, $params ) ) : $wpdb->get_var( $count_sql ) );

		$offset    = ( $page - 1 ) * $per;
		$list_sql  = "SELECT * FROM {$table} WHERE {$where_sql} ORDER BY id DESC LIMIT %d OFFSET %d";
		$list_args = array_merge( $params, array( $per, $offset ) );
		$rows      = $wpdb->get_results( $wpdb->prepare( $list_sql, $list_args ) );

		$items = array();
		foreach ( (array) $rows as $row ) {
			$items[] = array(
				'id'             => (int) $row->id,
				'channel'        => (string) $row->channel,
				'recipient'      => (string) $row->recipient,
				'recipient_name' => (string) $row->recipient_name,
				'user_id'        => (int) $row->user_id,
				'subject'        => (string) $row->subject,
				'body'           => (string) $row->body,
				'attachment_url' => (string) $row->attachment_url,
				'status'         => (string) $row->status,
				'error_message'  => (string) $row->error_message,
				'created_at'     => (string) $row->created_at,
				'created_fa'     => self::to_jalali( $row->created_at ),
				'created_by'     => (int) $row->created_by,
			);
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

	public static function send_message( WP_REST_Request $request ) {
		global $wpdb;
		self::maybe_create_table();

		$channel  = sanitize_key( $request->get_param( 'channel' ) ?: 'email' ); // email | sms
		$user_id  = absint( $request->get_param( 'user_id' ) );
		$to       = sanitize_text_field( $request->get_param( 'recipient' ) ?: '' );
		$name     = sanitize_text_field( $request->get_param( 'recipient_name' ) ?: '' );
		$subject  = sanitize_text_field( $request->get_param( 'subject' ) ?: '' );
		$body     = wp_kses_post( $request->get_param( 'body' ) ?: '' );
		$attach_url = '';

		if ( $body === '' ) {
			return new WP_Error( 'empty', 'متن پیام خالی است', array( 'status' => 400 ) );
		}

		// Resolve recipient from user
		if ( $user_id ) {
			$user = get_userdata( $user_id );
			if ( ! $user ) {
				return new WP_Error( 'no_user', 'کاربر یافت نشد', array( 'status' => 404 ) );
			}
			$name = $name ?: $user->display_name;
			if ( 'email' === $channel ) {
				$to = $to ?: $user->user_email;
			} else {
				$phone = get_user_meta( $user_id, 'billing_phone', true );
				if ( ! $phone ) {
					$phone = get_user_meta( $user_id, 'user_phone', true );
				}
				if ( ! $phone && preg_match( '/^09\d{9}$/', $user->user_login ) ) {
					$phone = $user->user_login;
				}
				$to = $to ?: $phone;
			}
		}

		if ( 'email' === $channel ) {
			$to = sanitize_email( $to );
			if ( ! is_email( $to ) ) {
				return new WP_Error( 'bad_email', 'ایمیل نامعتبر است', array( 'status' => 400 ) );
			}
		} else {
			$to = preg_replace( '/\D+/', '', $to );
			if ( strpos( $to, '98' ) === 0 && strlen( $to ) >= 12 ) {
				$to = '0' . substr( $to, 2 );
			}
			if ( strpos( $to, '9' ) === 0 && strlen( $to ) === 10 ) {
				$to = '0' . $to;
			}
			if ( ! preg_match( '/^09\d{9}$/', $to ) ) {
				return new WP_Error( 'bad_phone', 'شماره موبایل نامعتبر است', array( 'status' => 400 ) );
			}
			$channel = 'sms';
		}

		// Optional base64 attachment (email only mainly)
		$b64      = $request->get_param( 'file_base64' );
		$filename = sanitize_file_name( $request->get_param( 'file_name' ) ?: 'attachment.bin' );
		$attachments = array();
		if ( is_string( $b64 ) && $b64 !== '' && 'email' === $channel ) {
			$file = self::sideload_base64( $b64, $filename );
			if ( is_wp_error( $file ) ) {
				return $file;
			}
			$attachments[] = $file['path'];
			$attach_url    = $file['url'];
		}

		$status = 'sent';
		$error  = '';
		$ok     = false;

		if ( 'sms' === $channel ) {
			if ( class_exists( 'EzLens_Auth_Messaging' ) ) {
				$msg = EzLens_Auth_Messaging::get_instance();
				if ( method_exists( $msg, 'send_campaign_sms' ) ) {
					$r  = $msg->send_campaign_sms( $to, wp_strip_all_tags( $body ) );
					$ok = ! empty( $r['success'] );
					if ( ! $ok ) {
						$error  = isset( $r['message'] ) ? (string) $r['message'] : 'ارسال پیامک ناموفق';
						$status = 'failed';
					}
				} else {
					$error  = 'متد ارسال پیامک موجود نیست';
					$status = 'failed';
				}
			} else {
				$error  = 'ماژول Messaging بارگذاری نشده';
				$status = 'failed';
			}
		} else {
			// email via messaging module (bypasses campaign flag when possible)
			if ( class_exists( 'EzLens_Auth_Messaging' ) ) {
				$msg = EzLens_Auth_Messaging::get_instance();
				if ( method_exists( $msg, 'test_email' ) && empty( $attachments ) && $subject === '' ) {
					// not ideal; use send_campaign_email
				}
				if ( method_exists( $msg, 'send_campaign_email' ) ) {
					$r  = $msg->send_campaign_email( $to, $subject ? $subject : 'پیام از ایزی‌لنز', $body );
					$ok = ! empty( $r['success'] );
					if ( ! $ok ) {
						// fallback wp_mail
						$headers = array( 'Content-Type: text/html; charset=UTF-8' );
						$ok      = wp_mail( $to, $subject ? $subject : 'پیام از ایزی‌لنز', $body, $headers, $attachments );
						if ( ! $ok ) {
							$error  = isset( $r['message'] ) ? (string) $r['message'] : 'ارسال ایمیل ناموفق';
							$status = 'failed';
						} else {
							$status = 'sent';
							$error  = '';
						}
					}
				} else {
					$headers = array( 'Content-Type: text/html; charset=UTF-8' );
					$ok      = wp_mail( $to, $subject ? $subject : 'پیام از ایزی‌لنز', $body, $headers, $attachments );
					if ( ! $ok ) {
						$error  = 'ارسال ایمیل ناموفق';
						$status = 'failed';
					}
				}
			} else {
				$headers = array( 'Content-Type: text/html; charset=UTF-8' );
				$ok      = wp_mail( $to, $subject ? $subject : 'پیام از ایزی‌لنز', $body, $headers, $attachments );
				if ( ! $ok ) {
					$error  = 'ارسال ایمیل ناموفق';
					$status = 'failed';
				}
			}
		}

		$wpdb->insert(
			self::table(),
			array(
				'channel'        => $channel,
				'recipient'      => $to,
				'recipient_name' => $name,
				'user_id'        => $user_id,
				'subject'        => $subject,
				'body'           => $body,
				'attachment_url' => $attach_url,
				'status'         => $status,
				'error_message'  => $error,
				'created_at'     => current_time( 'mysql' ),
				'created_by'     => get_current_user_id(),
			),
			array( '%s', '%s', '%s', '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%d' )
		);
		$id = (int) $wpdb->insert_id;

		if ( 'failed' === $status ) {
			return new WP_Error( 'send_failed', $error ? $error : 'ارسال ناموفق', array( 'status' => 502, 'log_id' => $id ) );
		}

		return rest_ensure_response(
			array(
				'ok'      => true,
				'id'      => $id,
				'status'  => $status,
				'message' => 'ارسال شد',
			)
		);
	}

	private static function sideload_base64( $b64, $filename ) {
		if ( strpos( $b64, ',' ) !== false ) {
			$b64 = explode( ',', $b64, 2 )[1];
		}
		$data = base64_decode( $b64 );
		if ( false === $data ) {
			return new WP_Error( 'bad_file', 'فایل نامعتبر', array( 'status' => 400 ) );
		}
		$upload = wp_upload_bits( $filename, null, $data );
		if ( ! empty( $upload['error'] ) ) {
			return new WP_Error( 'upload', $upload['error'], array( 'status' => 500 ) );
		}
		return array(
			'path' => $upload['file'],
			'url'  => $upload['url'],
		);
	}

	public static function delete_message( WP_REST_Request $request ) {
		global $wpdb;
		$id = (int) $request['id'];
		$n  = $wpdb->delete( self::table(), array( 'id' => $id ), array( '%d' ) );
		if ( ! $n ) {
			return new WP_Error( 'not_found', 'پیام یافت نشد', array( 'status' => 404 ) );
		}
		return rest_ensure_response( array( 'ok' => true ) );
	}

	public static function search_customers( WP_REST_Request $request ) {
		$q     = sanitize_text_field( $request->get_param( 'search' ) ?: '' );
		$limit = max( 5, min( 30, (int) ( $request->get_param( 'per_page' ) ?: 15 ) ) );
		$args  = array(
			'number'  => $limit,
			'orderby' => 'registered',
			'order'   => 'DESC',
			'role__in' => array( 'customer', 'subscriber' ),
		);
		if ( $q !== '' ) {
			$args['search']         = '*' . $q . '*';
			$args['search_columns'] = array( 'user_login', 'user_email', 'display_name' );
		}
		$users = get_users( $args );
		$items = array();
		foreach ( $users as $u ) {
			$phone = get_user_meta( $u->ID, 'billing_phone', true );
			if ( ! $phone ) {
				$phone = get_user_meta( $u->ID, 'user_phone', true );
			}
			if ( ! $phone && preg_match( '/^09\d{9}$/', $u->user_login ) ) {
				$phone = $u->user_login;
			}
			$items[] = array(
				'id'    => (int) $u->ID,
				'name'  => $u->display_name,
				'email' => $u->user_email,
				'login' => $u->user_login,
				'phone' => (string) $phone,
			);
		}
		return rest_ensure_response( array( 'ok' => true, 'items' => $items ) );
	}
}

EzLens_Manager_Messages_REST::init();
