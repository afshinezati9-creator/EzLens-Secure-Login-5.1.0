<?php
/**
 * Manager Support REST — tickets connected to EzLens_Auth_Support.
 *
 * GET    /ezlens/v1/manager/support/tickets
 * GET    /ezlens/v1/manager/support/tickets/{id}
 * POST   /ezlens/v1/manager/support/tickets/{id}/reply
 * POST   /ezlens/v1/manager/support/tickets/{id}/status
 * POST   /ezlens/v1/manager/support/tickets/{id}/notify
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class EzLens_Manager_Support_REST {

	const NS = 'ezlens/v1';

	public static function init() {
		add_action( 'rest_api_init', array( __CLASS__, 'register_routes' ) );
	}

	public static function can_manage() {
		return current_user_can( 'manage_options' )
			|| current_user_can( 'manage_woocommerce' )
			|| current_user_can( 'edit_others_posts' );
	}

	private static function core() {
		if ( class_exists( 'EzLens_Auth_Support' ) ) {
			return EzLens_Auth_Support::get_instance();
		}
		$path = defined( 'EZLAUTH_MODULES_DIR' )
			? EZLAUTH_MODULES_DIR . 'support/class-support.php'
			: dirname( __FILE__, 3 ) . '/support/class-support.php';
		if ( is_readable( $path ) ) {
			require_once $path;
		}
		return class_exists( 'EzLens_Auth_Support' ) ? EzLens_Auth_Support::get_instance() : null;
	}

	public static function register_routes() {
		register_rest_route(
			self::NS,
			'/manager/support/tickets',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( __CLASS__, 'list_tickets' ),
				'permission_callback' => array( __CLASS__, 'can_manage' ),
			)
		);
		register_rest_route(
			self::NS,
			'/manager/support/tickets/(?P<id>\d+)',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( __CLASS__, 'get_ticket' ),
				'permission_callback' => array( __CLASS__, 'can_manage' ),
			)
		);
		register_rest_route(
			self::NS,
			'/manager/support/tickets/(?P<id>\d+)/reply',
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( __CLASS__, 'reply' ),
				'permission_callback' => array( __CLASS__, 'can_manage' ),
			)
		);
		register_rest_route(
			self::NS,
			'/manager/support/tickets/(?P<id>\d+)/status',
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( __CLASS__, 'set_status' ),
				'permission_callback' => array( __CLASS__, 'can_manage' ),
			)
		);
		register_rest_route(
			self::NS,
			'/manager/support/tickets/(?P<id>\d+)/notify',
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( __CLASS__, 'notify' ),
				'permission_callback' => array( __CLASS__, 'can_manage' ),
			)
		);
	}

	private static function to_jalali( $mysql_datetime ) {
		if ( empty( $mysql_datetime ) ) {
			return '';
		}
		$ts = strtotime( $mysql_datetime );
		if ( ! $ts ) {
			return (string) $mysql_datetime;
		}
		if ( class_exists( 'EzLens_Inbox' ) && method_exists( 'EzLens_Inbox', 'to_jalali' ) ) {
			return EzLens_Inbox::to_jalali( $ts );
		}
		// Fallback simple Gregorian with time (client also formats Jalali)
		return gmdate( 'Y-m-d H:i', $ts + ( (int) get_option( 'gmt_offset', 0 ) * HOUR_IN_SECONDS ) );
	}

	private static function map_ticket( $t ) {
		if ( ! $t ) {
			return null;
		}
		$user_id = (int) $t->user_id;
		$phone   = get_user_meta( $user_id, 'billing_phone', true );
		if ( ! $phone ) {
			$phone = get_user_meta( $user_id, 'user_phone', true );
		}
		if ( ! $phone && ! empty( $t->user_login ) && preg_match( '/^09\d{9}$/', $t->user_login ) ) {
			$phone = $t->user_login;
		}
		return array(
			'id'           => (int) $t->id,
			'user_id'      => $user_id,
			'status'       => (string) $t->status,
			'subject'      => (string) ( $t->subject ?? '' ),
			'created_at'   => (string) $t->created_at,
			'updated_at'   => (string) $t->updated_at,
			'created_fa'   => self::to_jalali( $t->created_at ),
			'updated_fa'   => self::to_jalali( $t->updated_at ),
			'display_name' => (string) ( $t->display_name ?? '' ),
			'user_email'   => (string) ( $t->user_email ?? '' ),
			'user_login'   => (string) ( $t->user_login ?? '' ),
			'phone'        => (string) $phone,
		);
	}

	private static function map_message( $m ) {
		$file = (string) ( $m->file_attachment ?? '' );
		return array(
			'id'          => (int) $m->id,
			'ticket_id'   => (int) $m->ticket_id,
			'sender_id'   => (int) $m->sender_id,
			'sender_type' => (string) $m->sender_type,
			'message'     => (string) $m->message,
			'file_url'    => $file,
			'has_file'    => $file !== '',
			'is_read'     => ! empty( $m->is_read ),
			'created_at'  => (string) $m->created_at,
			'created_fa'  => self::to_jalali( $m->created_at ),
		);
	}

	public static function list_tickets( WP_REST_Request $request ) {
		$core = self::core();
		if ( ! $core ) {
			return new WP_Error( 'no_support', 'ماژول پشتیبانی بارگذاری نشده', array( 'status' => 500 ) );
		}
		$status = sanitize_key( $request->get_param( 'status' ) ?: 'all' );
		$search = sanitize_text_field( $request->get_param( 'search' ) ?: '' );
		$page   = max( 1, (int) $request->get_param( 'page' ) );
		$per    = max( 5, min( 50, (int) ( $request->get_param( 'per_page' ) ?: 20 ) ) );
		$offset = ( $page - 1 ) * $per;

		$rows  = $core->get_tickets( $status === 'all' ? null : $status, $search, $per, $offset );
		$total = $core->count_tickets( $status === 'all' ? null : $status, $search );
		$items = array();
		foreach ( (array) $rows as $row ) {
			$items[] = self::map_ticket( $row );
		}
		return rest_ensure_response(
			array(
				'ok'          => true,
				'items'       => $items,
				'total'       => (int) $total,
				'page'        => $page,
				'per_page'    => $per,
				'pages'       => max( 1, (int) ceil( $total / $per ) ),
			)
		);
	}

	public static function get_ticket( WP_REST_Request $request ) {
		$core = self::core();
		if ( ! $core ) {
			return new WP_Error( 'no_support', 'ماژول پشتیبانی بارگذاری نشده', array( 'status' => 500 ) );
		}
		$id     = (int) $request['id'];
		$ticket = $core->get_ticket( $id );
		if ( ! $ticket ) {
			return new WP_Error( 'not_found', 'تیکت یافت نشد', array( 'status' => 404 ) );
		}
		$msgs = $core->get_messages( $id );
		$messages = array();
		foreach ( (array) $msgs as $m ) {
			$messages[] = self::map_message( $m );
		}

		$user_id = (int) $ticket->user_id;
		$customer = self::customer_snapshot( $user_id );

		return rest_ensure_response(
			array(
				'ok'       => true,
				'ticket'   => self::map_ticket( $ticket ),
				'messages' => $messages,
				'customer' => $customer,
			)
		);
	}

	private static function customer_snapshot( $user_id ) {
		$user = get_userdata( $user_id );
		if ( ! $user ) {
			return null;
		}
		$phone = get_user_meta( $user_id, 'billing_phone', true );
		if ( ! $phone ) {
			$phone = get_user_meta( $user_id, 'user_phone', true );
		}
		$wallet = 0;
		if ( class_exists( 'EzLens_CD_Wallet' ) && method_exists( 'EzLens_CD_Wallet', 'balance' ) ) {
			$wallet = (int) EzLens_CD_Wallet::balance( $user_id );
		}
		$orders_count = function_exists( 'wc_get_customer_order_count' ) ? (int) wc_get_customer_order_count( $user_id ) : 0;
		$total_spent  = function_exists( 'wc_get_customer_total_spent' ) ? (string) wc_get_customer_total_spent( $user_id ) : '0';

		$recent = array();
		if ( function_exists( 'wc_get_orders' ) ) {
			$orders = wc_get_orders(
				array(
					'customer_id' => $user_id,
					'limit'       => 5,
					'orderby'     => 'date',
					'order'       => 'DESC',
					'return'      => 'objects',
				)
			);
			foreach ( $orders as $o ) {
				$created = $o->get_date_created();
				$recent[] = array(
					'id'     => $o->get_id(),
					'number' => $o->get_order_number(),
					'status' => $o->get_status(),
					'total'  => $o->get_total(),
					'date'   => $created ? $created->date( 'c' ) : '',
				);
			}
		}

		// Persistent cart
		$cart_items = array();
		$blog_id    = function_exists( 'get_current_blog_id' ) ? get_current_blog_id() : 1;
		$cart       = get_user_meta( $user_id, '_woocommerce_persistent_cart_' . $blog_id, true );
		if ( empty( $cart ) || ! is_array( $cart ) ) {
			$cart = get_user_meta( $user_id, '_woocommerce_persistent_cart', true );
		}
		$contents = array();
		if ( is_array( $cart ) && isset( $cart['cart'] ) && is_array( $cart['cart'] ) ) {
			$contents = $cart['cart'];
		} elseif ( is_array( $cart ) ) {
			$contents = $cart;
		}
		foreach ( $contents as $line ) {
			if ( ! is_array( $line ) ) {
				continue;
			}
			$pid = isset( $line['product_id'] ) ? (int) $line['product_id'] : 0;
			$name = '';
			if ( $pid && function_exists( 'wc_get_product' ) ) {
				$p = wc_get_product( $pid );
				if ( $p ) {
					$name = $p->get_name();
				}
			}
			$cart_items[] = array(
				'product_id' => $pid,
				'name'       => $name,
				'quantity'   => isset( $line['quantity'] ) ? (int) $line['quantity'] : 0,
			);
		}

		return array(
			'id'           => $user_id,
			'display_name' => $user->display_name,
			'email'        => $user->user_email,
			'login'        => $user->user_login,
			'phone'        => (string) $phone,
			'wallet'       => $wallet,
			'orders_count' => $orders_count,
			'total_spent'  => $total_spent,
			'recent_orders'=> $recent,
			'cart'         => $cart_items,
		);
	}

	public static function reply( WP_REST_Request $request ) {
		$core = self::core();
		if ( ! $core ) {
			return new WP_Error( 'no_support', 'ماژول پشتیبانی بارگذاری نشده', array( 'status' => 500 ) );
		}
		$id      = (int) $request['id'];
		$message = wp_kses_post( $request->get_param( 'message' ) ?: '' );
		$status  = sanitize_key( $request->get_param( 'status' ) ?: 'replied' );
		if ( $message === '' ) {
			return new WP_Error( 'empty', 'متن پاسخ خالی است', array( 'status' => 400 ) );
		}
		if ( ! in_array( $status, array( 'open', 'replied', 'closed' ), true ) ) {
			$status = 'replied';
		}

		$attachment_id = 0;
		// Base64 file upload (optional)
		$b64      = $request->get_param( 'file_base64' );
		$filename = sanitize_file_name( $request->get_param( 'file_name' ) ?: 'attachment.bin' );
		if ( is_string( $b64 ) && $b64 !== '' ) {
			$attachment_id = self::sideload_base64( $b64, $filename );
			if ( is_wp_error( $attachment_id ) ) {
				return $attachment_id;
			}
		} elseif ( $request->get_param( 'attachment_id' ) ) {
			$attachment_id = absint( $request->get_param( 'attachment_id' ) );
		}

		$admin_id = get_current_user_id() ?: 1;
		$core->add_message( $id, $admin_id, 'admin', $message, $attachment_id ? $attachment_id : null, true );
		$core->update_status( $id, $status );

		return rest_ensure_response(
			array(
				'ok'     => true,
				'status' => $status,
				'message'=> 'پاسخ ثبت شد',
			)
		);
	}

	private static function sideload_base64( $b64, $filename ) {
		if ( strpos( $b64, ',' ) !== false ) {
			$b64 = explode( ',', $b64, 2 )[1];
		}
		$data = base64_decode( $b64 );
		if ( false === $data ) {
			return new WP_Error( 'bad_file', 'فایل نامعتبر است', array( 'status' => 400 ) );
		}
		$upload = wp_upload_bits( $filename, null, $data );
		if ( ! empty( $upload['error'] ) ) {
			return new WP_Error( 'upload', $upload['error'], array( 'status' => 500 ) );
		}
		$filetype = wp_check_filetype( $filename );
		$attachment = array(
			'post_mime_type' => $filetype['type'] ? $filetype['type'] : 'application/octet-stream',
			'post_title'     => sanitize_file_name( pathinfo( $filename, PATHINFO_FILENAME ) ),
			'post_content'   => '',
			'post_status'    => 'inherit',
		);
		$attach_id = wp_insert_attachment( $attachment, $upload['file'] );
		if ( is_wp_error( $attach_id ) ) {
			return $attach_id;
		}
		require_once ABSPATH . 'wp-admin/includes/image.php';
		$meta = wp_generate_attachment_metadata( $attach_id, $upload['file'] );
		wp_update_attachment_metadata( $attach_id, $meta );
		return (int) $attach_id;
	}

	public static function set_status( WP_REST_Request $request ) {
		$core = self::core();
		if ( ! $core ) {
			return new WP_Error( 'no_support', 'ماژول پشتیبانی بارگذاری نشده', array( 'status' => 500 ) );
		}
		$id     = (int) $request['id'];
		$status = sanitize_key( $request->get_param( 'status' ) ?: '' );
		if ( ! in_array( $status, array( 'open', 'replied', 'closed' ), true ) ) {
			return new WP_Error( 'bad_status', 'وضعیت نامعتبر', array( 'status' => 400 ) );
		}
		$core->update_status( $id, $status );
		return rest_ensure_response( array( 'ok' => true, 'status' => $status ) );
	}

	public static function notify( WP_REST_Request $request ) {
		$core = self::core();
		if ( ! $core ) {
			return new WP_Error( 'no_support', 'ماژول پشتیبانی بارگذاری نشده', array( 'status' => 500 ) );
		}
		$id      = (int) $request['id'];
		$channel = sanitize_key( $request->get_param( 'channel' ) ?: 'email' ); // email | sms | both
		$text    = sanitize_textarea_field( $request->get_param( 'message' ) ?: '' );
		$ticket  = $core->get_ticket( $id );
		if ( ! $ticket ) {
			return new WP_Error( 'not_found', 'تیکت یافت نشد', array( 'status' => 404 ) );
		}
		$user_id = (int) $ticket->user_id;
		$user    = get_userdata( $user_id );
		if ( ! $user ) {
			return new WP_Error( 'no_user', 'کاربر یافت نشد', array( 'status' => 404 ) );
		}
		if ( $text === '' ) {
			$text = 'پاسخ پشتیبانی برای تیکت #' . $id . ' ثبت شده است. لطفاً پنل خود را بررسی کنید.';
		}

		$results = array();
		if ( $channel === 'email' || $channel === 'both' ) {
			$ok = $core->send_notification_email( $user_id, 'پشتیبانی ایزی‌لنز #' . $id, $text );
			// force send even if setting off
			if ( ! $ok ) {
				$ok = wp_mail( $user->user_email, 'پشتیبانی ایزی‌لنز #' . $id, $text, array( 'Content-Type: text/plain; charset=UTF-8' ) );
			}
			$results['email'] = (bool) $ok;
		}
		if ( $channel === 'sms' || $channel === 'both' ) {
			$phone = get_user_meta( $user_id, 'billing_phone', true );
			if ( ! $phone ) {
				$phone = get_user_meta( $user_id, 'user_phone', true );
			}
			if ( ! $phone && preg_match( '/^09\d{9}$/', $user->user_login ) ) {
				$phone = $user->user_login;
			}
			$sms_ok = false;
			if ( $phone && class_exists( 'EzLens_Auth_Messaging' ) ) {
				$msg = EzLens_Auth_Messaging::get_instance();
				if ( method_exists( $msg, 'send_campaign_sms' ) ) {
					$r = $msg->send_campaign_sms( $phone, $text );
					$sms_ok = ! empty( $r['success'] );
				}
			}
			$results['sms'] = $sms_ok;
			$results['phone'] = (string) $phone;
		}
		return rest_ensure_response( array( 'ok' => true, 'results' => $results ) );
	}
}

EzLens_Manager_Support_REST::init();
