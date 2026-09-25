<?php
/**
 * Bridge: Customer Dashboard ↔ EzLens Auth Support
 *
 * Uses the same tables as modules/support (ezlens_support_tickets / messages)
 * so tickets created in the new dashboard appear in the admin support panel.
 *
 * @package EzLens_Secure_Login
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class EzLens_CD_Support_Bridge {

	/** Max concurrent open tickets per customer (filterable). */
	const MAX_OPEN_TICKETS = 3;

	/** @return EzLens_Auth_Support|null */
	public static function core() {
		if ( class_exists( 'EzLens_Auth_Support' ) ) {
			return EzLens_Auth_Support::get_instance();
		}
		return null;
	}

	public static function tickets_table() {
		global $wpdb;
		return $wpdb->prefix . 'ezlens_support_tickets';
	}

	public static function messages_table() {
		global $wpdb;
		return $wpdb->prefix . 'ezlens_support_messages';
	}

	/**
	 * Ensure main support schema exists (and optional priority column).
	 */
	public static function maybe_create_fallback_tables() {
		global $wpdb;
		$core = self::core();
		if ( $core && method_exists( $core, 'create_tables' ) ) {
			$core->create_tables();
		} else {
			$charset = $wpdb->get_charset_collate();
			require_once ABSPATH . 'wp-admin/includes/upgrade.php';
			dbDelta(
				'CREATE TABLE ' . self::tickets_table() . " (
					id bigint(20) NOT NULL AUTO_INCREMENT,
					user_id bigint(20) NOT NULL,
					status varchar(20) NOT NULL DEFAULT 'open',
					subject varchar(255) DEFAULT '',
					priority varchar(16) NOT NULL DEFAULT 'normal',
					topic varchar(32) NOT NULL DEFAULT '',
					created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
					updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
					PRIMARY KEY (id),
					KEY user_id (user_id),
					KEY status (status)
				) {$charset};"
			);
			dbDelta(
				'CREATE TABLE ' . self::messages_table() . " (
					id bigint(20) NOT NULL AUTO_INCREMENT,
					ticket_id bigint(20) NOT NULL,
					sender_id bigint(20) NOT NULL,
					sender_type varchar(10) NOT NULL DEFAULT 'user',
					message text NOT NULL,
					file_attachment varchar(255) DEFAULT '',
					is_read tinyint(1) NOT NULL DEFAULT 0,
					created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
					PRIMARY KEY (id),
					KEY ticket_id (ticket_id),
					KEY sender_id (sender_id)
				) {$charset};"
			);
		}

		// Soft-migrate optional columns on existing installs.
		$table = self::tickets_table();
		// phpcs:ignore WordPress.DB.PreparedSQL
		$cols = $wpdb->get_col( "DESCRIBE {$table}", 0 );
		if ( is_array( $cols ) ) {
			if ( ! in_array( 'priority', $cols, true ) ) {
				// phpcs:ignore WordPress.DB.PreparedSQL
				$wpdb->query( "ALTER TABLE {$table} ADD COLUMN priority varchar(16) NOT NULL DEFAULT 'normal' AFTER subject" );
			}
			if ( ! in_array( 'topic', $cols, true ) ) {
				// phpcs:ignore WordPress.DB.PreparedSQL
				$wpdb->query( "ALTER TABLE {$table} ADD COLUMN topic varchar(32) NOT NULL DEFAULT '' AFTER priority" );
			}
		}
	}

	public static function topics() {
		return array(
			'order'    => 'پیگیری سفارش',
			'product'  => 'محصول / نسخه چشم',
			'payment'  => 'پرداخت و کیف پول',
			'shipping' => 'ارسال و تحویل',
			'return'   => 'مرجوعی و تعویض',
			'charity'  => 'هم‌یاری بینایی',
			'other'    => 'سایر',
		);
	}

	public static function priorities() {
		return array(
			'normal' => 'عادی',
			'high'   => 'بالا',
			'urgent' => 'فوری',
		);
	}

	/**
	 * @return object[]
	 */
	public static function list_tickets( $user_id = 0, $limit = 40 ) {
		$user_id = $user_id ? absint( $user_id ) : get_current_user_id();
		if ( ! $user_id ) {
			return array();
		}
		$core = self::core();
		if ( $core && method_exists( $core, 'get_user_tickets' ) ) {
			$rows = $core->get_user_tickets( $user_id, $limit );
			return is_array( $rows ) ? $rows : array();
		}
		global $wpdb;
		self::maybe_create_fallback_tables();
		// phpcs:ignore WordPress.DB.PreparedSQL
		$rows = $wpdb->get_results(
			$wpdb->prepare(
				'SELECT * FROM ' . self::tickets_table() . ' WHERE user_id = %d ORDER BY updated_at DESC LIMIT %d',
				$user_id,
				$limit
			)
		);
		return is_array( $rows ) ? $rows : array();
	}

	/**
	 * @return object|null
	 */
	public static function get_ticket( $ticket_id, $user_id = 0 ) {
		$ticket_id = absint( $ticket_id );
		$user_id   = $user_id ? absint( $user_id ) : get_current_user_id();
		$core      = self::core();
		$ticket    = null;
		if ( $core && method_exists( $core, 'get_ticket' ) ) {
			$ticket = $core->get_ticket( $ticket_id );
		} else {
			global $wpdb;
			$ticket = $wpdb->get_row(
				$wpdb->prepare( 'SELECT * FROM ' . self::tickets_table() . ' WHERE id = %d', $ticket_id )
			);
		}
		if ( ! $ticket ) {
			return null;
		}
		// Ownership: customer may only open own tickets (staff bypass handled by admin UI).
		if ( $user_id && (int) $ticket->user_id !== (int) $user_id && ! current_user_can( 'manage_woocommerce' ) ) {
			return null;
		}
		return $ticket;
	}

	/**
	 * @return object[]
	 */
	public static function get_messages( $ticket_id ) {
		$ticket_id = absint( $ticket_id );
		$core      = self::core();
		if ( $core && method_exists( $core, 'get_messages' ) ) {
			$rows = $core->get_messages( $ticket_id );
			return is_array( $rows ) ? $rows : array();
		}
		global $wpdb;
		$rows = $wpdb->get_results(
			$wpdb->prepare(
				'SELECT * FROM ' . self::messages_table() . ' WHERE ticket_id = %d ORDER BY created_at ASC',
				$ticket_id
			)
		);
		return is_array( $rows ) ? $rows : array();
	}

	/**
	 * Create ticket in the SAME tables as admin support.
	 *
	 * @param string $subject
	 * @param string $message
	 * @param int    $attachment_id Media attachment ID
	 * @param string $priority
	 * @param string $topic
	 * @return int|WP_Error ticket id
	 */
	public static function create_ticket( $subject, $message, $attachment_id = 0, $priority = 'normal', $topic = '' ) {
		global $wpdb;
		$user_id = get_current_user_id();
		if ( ! $user_id ) {
			return new WP_Error( 'auth', 'وارد حساب شوید' );
		}
		$max_open = (int) apply_filters( 'ezcd_max_open_tickets', self::MAX_OPEN_TICKETS );
		if ( $max_open > 0 ) {
			$open = self::open_count( $user_id );
			if ( $open >= $max_open ) {
				return new WP_Error(
					'limit',
					sprintf(
						'حداکثر %s درخواست باز می‌توانید داشته باشید. لطفاً پاسخ قبلی را منتظر بمانید یا گفتگوی بسته‌شده را از لیست بررسی کنید.',
						function_exists( 'ezcd_fa' ) ? ezcd_fa( (string) $max_open ) : (string) $max_open
					)
				);
			}
		}
		$message = trim( wp_strip_all_tags( (string) $message ) );
		$subject = trim( wp_strip_all_tags( (string) $subject ) );
		if ( $message === '' ) {
			return new WP_Error( 'message', 'متن پیام لازم است' );
		}
		if ( $subject === '' ) {
			$subject = mb_substr( $message, 0, 48 );
			if ( function_exists( 'mb_strlen' ) && mb_strlen( $message ) > 48 ) {
				$subject .= '…';
			}
		}

		$priority = sanitize_key( $priority );
		if ( ! in_array( $priority, array( 'normal', 'high', 'urgent' ), true ) ) {
			$priority = 'normal';
		}
		$topic = sanitize_key( $topic );
		$topics = self::topics();
		if ( $topic && isset( $topics[ $topic ] ) && 'other' !== $topic ) {
			// Prefix human label for admin list readability.
			$subject = $topics[ $topic ] . ' — ' . $subject;
		}

		self::maybe_create_fallback_tables();
		$now = current_time( 'mysql' );

		$row = array(
			'user_id'    => $user_id,
			'status'     => 'open',
			'subject'    => $subject,
			'created_at' => $now,
			'updated_at' => $now,
		);

		// Optional columns
		// phpcs:ignore WordPress.DB.PreparedSQL
		$cols = $wpdb->get_col( 'DESCRIBE ' . self::tickets_table(), 0 );
		if ( is_array( $cols ) ) {
			if ( in_array( 'priority', $cols, true ) ) {
				$row['priority'] = $priority;
			}
			if ( in_array( 'topic', $cols, true ) ) {
				$row['topic'] = $topic;
			}
		}

		$ok = $wpdb->insert( self::tickets_table(), $row );
		if ( ! $ok ) {
			return new WP_Error( 'db', 'ثبت تیکت ممکن نشد. لطفاً دوباره تلاش کنید.' );
		}
		$ticket_id = (int) $wpdb->insert_id;

		// First message via core API when possible (keeps notifications / file_attachment consistent).
		$core = self::core();
		if ( $core && method_exists( $core, 'add_message' ) ) {
			$core->add_message( $ticket_id, $user_id, 'user', $message, $attachment_id ? absint( $attachment_id ) : null, false );
		} else {
			self::insert_message_row( $ticket_id, $user_id, 'user', $message, $attachment_id );
		}

		do_action( 'ezlens_support_ticket_created', $ticket_id );
		if ( class_exists( 'EzLens_Auth_Audit' ) ) {
			EzLens_Auth_Audit::get_instance()->log( 'support.ticket_created', array( 'ticket_id' => $ticket_id, 'source' => 'customer-dashboard' ), 'ticket', $ticket_id );
		}
		if ( class_exists( 'EzLens_Auth_Notifications' ) ) {
			EzLens_Auth_Notifications::get_instance()->create( $user_id, 'تیکت ثبت شد', 'درخواست پشتیبانی شما ثبت شد. به‌زودی پاسخ می‌دهیم.', 'support' );
		}

		// Soft notify admin.
		if ( class_exists( 'EzLens_Auth_Settings' ) && EzLens_Auth_Settings::get( 'support_notify_admin_email' ) === '1' ) {
			$admin_email = EzLens_Auth_Settings::get( 'support_admin_email' ) ?: get_option( 'admin_email' );
			if ( is_email( $admin_email ) ) {
				wp_mail(
					$admin_email,
					'تیکت جدید پشتیبانی #' . $ticket_id,
					"موضوع: {$subject}\n\n" . $message
				);
			}
		}

		do_action( 'ezcd_support_ticket_created', $ticket_id, $user_id );
		return $ticket_id;
	}

	/**
	 * Customer reply on own ticket.
	 *
	 * @return true|WP_Error
	 */
	public static function add_message( $ticket_id, $message, $attachment_id = 0, $is_staff = false ) {
		$user_id = get_current_user_id();
		$message = trim( wp_strip_all_tags( (string) $message ) );
		if ( $message === '' && ! $attachment_id ) {
			return new WP_Error( 'message', 'پیام خالی است' );
		}
		$ticket = self::get_ticket( $ticket_id, $is_staff ? 0 : $user_id );
		if ( ! $ticket ) {
			return new WP_Error( 'not_found', 'تیکت یافت نشد' );
		}
		if ( ! $is_staff && in_array( strtolower( (string) $ticket->status ), array( 'closed' ), true ) ) {
			return new WP_Error( 'closed', 'این تیکت بسته شده است' );
		}

		$sender_type = $is_staff ? 'admin' : 'user';
		$core        = self::core();
		if ( $core && method_exists( $core, 'add_message' ) ) {
			$core->add_message(
				(int) $ticket_id,
				$user_id,
				$sender_type,
				$message !== '' ? $message : 'پیوست',
				$attachment_id ? absint( $attachment_id ) : null,
				true
			);
		} else {
			self::insert_message_row( (int) $ticket_id, $user_id, $sender_type, $message, $attachment_id );
			global $wpdb;
			$wpdb->update(
				self::tickets_table(),
				array(
					'updated_at' => current_time( 'mysql' ),
					'status'     => $is_staff ? 'replied' : 'open',
				),
				array( 'id' => (int) $ticket_id )
			);
		}

		do_action( 'ezcd_support_message_added', (int) $ticket_id, $user_id, $is_staff );
		if ( $is_staff ) {
			self::notify_customer_reply( (int) $ticket_id, $message, true, true );
		}
		return true;
	}

	private static function insert_message_row( $ticket_id, $sender_id, $sender_type, $message, $attachment_id = 0 ) {
		global $wpdb;
		$file_url = $attachment_id ? (string) wp_get_attachment_url( absint( $attachment_id ) ) : '';
		$wpdb->insert(
			self::messages_table(),
			array(
				'ticket_id'       => (int) $ticket_id,
				'sender_id'       => (int) $sender_id,
				'sender_type'     => $sender_type,
				'message'         => wp_kses_post( $message ),
				'file_attachment' => $file_url,
				'created_at'      => current_time( 'mysql' ),
			)
		);
	}

	public static function status_label( $status ) {
		$map = array(
			'open'        => 'باز',
			'replied'     => 'پاسخ پشتیبانی',
			'answered'    => 'پاسخ داده‌شده',
			'pending'     => 'در انتظار',
			'closed'      => 'بسته',
			'resolved'    => 'حل‌شده',
			'in_progress' => 'در حال بررسی',
		);
		$s = strtolower( (string) $status );
		return isset( $map[ $s ] ) ? $map[ $s ] : $status;
	}

	public static function message_text( $row ) {
		if ( isset( $row->message ) && $row->message !== '' ) {
			return (string) $row->message;
		}
		if ( isset( $row->content ) ) {
			return (string) $row->content;
		}
		if ( isset( $row->body ) ) {
			return (string) $row->body;
		}
		return '';
	}

	public static function is_staff_message( $row ) {
		if ( isset( $row->sender_type ) ) {
			return in_array( $row->sender_type, array( 'staff', 'admin', 'agent' ), true );
		}
		if ( isset( $row->is_staff ) ) {
			return (int) $row->is_staff === 1;
		}
		return false;
	}

	public static function attachment_url( $row ) {
		if ( ! empty( $row->file_attachment ) ) {
			return (string) $row->file_attachment;
		}
		return '';
	}

	public static function open_count( $user_id = 0 ) {
		$user_id = $user_id ? absint( $user_id ) : get_current_user_id();
		if ( ! $user_id ) {
			return 0;
		}
		global $wpdb;
		return (int) $wpdb->get_var(
			$wpdb->prepare(
				'SELECT COUNT(*) FROM ' . self::tickets_table() . " WHERE user_id = %d AND status IN ('open','replied','pending','in_progress')",
				$user_id
			)
		);
	}

	/**
	 * Notify customer via SMS + email after staff reply (uses Messaging Bridge).
	 */
	public static function notify_customer_reply( $ticket_id, $reply_text, $do_sms = true, $do_email = true ) {
		$ticket = self::get_ticket( $ticket_id, 0 );
		if ( ! $ticket || empty( $ticket->user_id ) ) {
			return;
		}
		$user_id = (int) $ticket->user_id;
		$user    = get_userdata( $user_id );
		$subject = 'پاسخ پشتیبانی #' . (int) $ticket_id;
		$snippet = mb_substr( wp_strip_all_tags( (string) $reply_text ), 0, 120 );
		$body    = "برای درخواست پشتیبانی شما پاسخ جدید ثبت شد.\n\n" . $snippet . "\n\nورود به حساب: " . ( function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'myaccount' ) : home_url( '/' ) );

		if ( $do_email && $user && is_email( $user->user_email ) && class_exists( 'EzLens_CD_Messaging_Bridge' ) ) {
			EzLens_CD_Messaging_Bridge::send_email( $user->user_email, $subject, nl2br( esc_html( $body ) ) );
		}
		if ( $do_sms && class_exists( 'EzLens_CD_Messaging_Bridge' ) ) {
			$phone = EzLens_CD_Messaging_Bridge::user_phone( $user_id );
			if ( $phone ) {
				EzLens_CD_Messaging_Bridge::send_sms( $phone, 'پاسخ پشتیبانی #' . (int) $ticket_id . ' در حساب کاربری شما ثبت شد.' );
			}
		}
	}

	public static function set_status( $ticket_id, $status ) {
		$status = sanitize_key( $status );
		$core   = self::core();
		if ( $core && method_exists( $core, 'update_status' ) ) {
			return (bool) $core->update_status( (int) $ticket_id, $status );
		}
		global $wpdb;
		return (bool) $wpdb->update(
			self::tickets_table(),
			array(
				'status'     => $status,
				'updated_at' => current_time( 'mysql' ),
			),
			array( 'id' => (int) $ticket_id )
		);
	}

	/**
	 * Admin: all tickets.
	 */
	public static function admin_list( $status = '', $search = '', $limit = 40, $offset = 0 ) {
		$core = self::core();
		if ( $core && method_exists( $core, 'get_tickets' ) ) {
			$rows = $core->get_tickets( $status ? $status : null, $search, $limit, $offset );
			return is_array( $rows ) ? $rows : array();
		}
		global $wpdb;
		$sql    = 'SELECT t.*, u.display_name, u.user_email FROM ' . self::tickets_table() . ' t LEFT JOIN ' . $wpdb->users . ' u ON u.ID = t.user_id WHERE 1=1';
		$params = array();
		if ( $status && 'all' !== $status ) {
			$sql     .= ' AND t.status = %s';
			$params[] = $status;
		}
		if ( $search ) {
			$sql     .= ' AND (t.subject LIKE %s OR u.display_name LIKE %s OR u.user_email LIKE %s)';
			$like     = '%' . $wpdb->esc_like( $search ) . '%';
			$params[] = $like;
			$params[] = $like;
			$params[] = $like;
		}
		$sql .= ' ORDER BY t.updated_at DESC LIMIT %d OFFSET %d';
		$params[] = $limit;
		$params[] = $offset;
		// phpcs:ignore
		$rows = $params ? $wpdb->get_results( $wpdb->prepare( $sql, $params ) ) : $wpdb->get_results( $sql );
		return is_array( $rows ) ? $rows : array();
	}

	/**
	 * Admin-initiated ticket for a customer (shows in customer support dashboard).
	 *
	 * @return int|WP_Error
	 */
	public static function create_for_user( $user_id, $subject, $message, $attachment_id = 0, $notify_email = true, $notify_sms = true ) {
		global $wpdb;
		$user_id = absint( $user_id );
		if ( ! $user_id || ! get_userdata( $user_id ) ) {
			return new WP_Error( 'user', 'مشتری یافت نشد' );
		}
		$message = trim( wp_strip_all_tags( (string) $message ) );
		$subject = trim( wp_strip_all_tags( (string) $subject ) );
		if ( $message === '' ) {
			return new WP_Error( 'message', 'متن پیام لازم است' );
		}
		if ( $subject === '' ) {
			$subject = 'پیام پشتیبانی';
		}

		self::maybe_create_fallback_tables();
		$now = current_time( 'mysql' );
		$row = array(
			'user_id'    => $user_id,
			'status'     => 'replied',
			'subject'    => $subject,
			'created_at' => $now,
			'updated_at' => $now,
		);
		// phpcs:ignore WordPress.DB.PreparedSQL
		$cols = $wpdb->get_col( 'DESCRIBE ' . self::tickets_table(), 0 );
		if ( is_array( $cols ) ) {
			if ( in_array( 'priority', $cols, true ) ) {
				$row['priority'] = 'normal';
			}
			if ( in_array( 'topic', $cols, true ) ) {
				$row['topic'] = 'admin';
			}
		}
		$ok = $wpdb->insert( self::tickets_table(), $row );
		if ( ! $ok ) {
			return new WP_Error( 'db', 'ثبت پیام ممکن نشد' );
		}
		$ticket_id = (int) $wpdb->insert_id;
		$admin_id  = get_current_user_id();

		$core = self::core();
		if ( $core && method_exists( $core, 'add_message' ) ) {
			$core->add_message( $ticket_id, $admin_id, 'admin', $message, $attachment_id ? absint( $attachment_id ) : null, false );
		} else {
			self::insert_message_row( $ticket_id, $admin_id, 'admin', $message, $attachment_id );
		}

		if ( class_exists( 'EzLens_Auth_Notifications' ) ) {
			EzLens_Auth_Notifications::get_instance()->create(
				$user_id,
				'پیام پشتیبانی',
				'یک پیام جدید از پشتیبانی در حساب کاربری شما ثبت شد.',
				'support'
			);
		}

		self::notify_customer_reply( $ticket_id, $message, (bool) $notify_sms, (bool) $notify_email );
		do_action( 'ezcd_support_admin_message', $ticket_id, $user_id );
		return $ticket_id;
	}
}
