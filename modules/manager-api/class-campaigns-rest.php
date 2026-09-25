<?php
/**
 * Manager Campaign REST — contact books (groups) + group email/SMS campaigns.
 * Wraps EzLens_Auth_Campaign (not single messaging).
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class EzLens_Manager_Campaign_REST {

	const NS = 'ezlens/v1';

	public static function init() {
		add_action( 'rest_api_init', array( __CLASS__, 'register_routes' ) );
	}

	public static function can_manage() {
		return current_user_can( 'manage_options' )
			|| current_user_can( 'manage_woocommerce' )
			|| current_user_can( 'edit_users' );
	}

	private static function core() {
		if ( class_exists( 'EzLens_Auth_Campaign' ) ) {
			return EzLens_Auth_Campaign::get_instance();
		}
		$path = defined( 'EZLAUTH_MODULES_DIR' )
			? EZLAUTH_MODULES_DIR . 'campaign/class-campaign.php'
			: dirname( __FILE__, 3 ) . '/campaign/class-campaign.php';
		if ( is_readable( $path ) ) {
			require_once $path;
		}
		return class_exists( 'EzLens_Auth_Campaign' ) ? EzLens_Auth_Campaign::get_instance() : null;
	}

	public static function register_routes() {
		// Contact books (groups)
		register_rest_route( self::NS, '/manager/campaign/books', array(
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( __CLASS__, 'list_books' ),
				'permission_callback' => array( __CLASS__, 'can_manage' ),
			),
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( __CLASS__, 'create_book' ),
				'permission_callback' => array( __CLASS__, 'can_manage' ),
			),
		) );
		register_rest_route( self::NS, '/manager/campaign/books/(?P<id>\d+)', array(
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( __CLASS__, 'get_book' ),
				'permission_callback' => array( __CLASS__, 'can_manage' ),
			),
			array(
				'methods'             => WP_REST_Server::EDITABLE,
				'callback'            => array( __CLASS__, 'update_book' ),
				'permission_callback' => array( __CLASS__, 'can_manage' ),
			),
			array(
				'methods'             => WP_REST_Server::DELETABLE,
				'callback'            => array( __CLASS__, 'delete_book' ),
				'permission_callback' => array( __CLASS__, 'can_manage' ),
			),
		) );

		// Site customers for picker
		register_rest_route( self::NS, '/manager/campaign/customers', array(
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => array( __CLASS__, 'list_customers' ),
			'permission_callback' => array( __CLASS__, 'can_manage' ),
		) );

		// Campaigns
		register_rest_route( self::NS, '/manager/campaign/campaigns', array(
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( __CLASS__, 'list_campaigns' ),
				'permission_callback' => array( __CLASS__, 'can_manage' ),
			),
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( __CLASS__, 'create_and_send' ),
				'permission_callback' => array( __CLASS__, 'can_manage' ),
			),
		) );
		register_rest_route( self::NS, '/manager/campaign/campaigns/(?P<id>\d+)', array(
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( __CLASS__, 'get_campaign' ),
				'permission_callback' => array( __CLASS__, 'can_manage' ),
			),
			array(
				'methods'             => WP_REST_Server::EDITABLE,
				'callback'            => array( __CLASS__, 'update_campaign' ),
				'permission_callback' => array( __CLASS__, 'can_manage' ),
			),
			array(
				'methods'             => WP_REST_Server::DELETABLE,
				'callback'            => array( __CLASS__, 'delete_campaign' ),
				'permission_callback' => array( __CLASS__, 'can_manage' ),
			),
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

	private static function map_group( $g, $core = null ) {
		if ( ! $g ) {
			return null;
		}
		$contact_ids = json_decode( $g->contact_ids ?? '[]', true );
		if ( ! is_array( $contact_ids ) ) {
			$contact_ids = array();
		}
		$count = count( $contact_ids );
		if ( $core && method_exists( $core, 'get_group_recipients' ) ) {
			$recs = $core->get_group_recipients( (int) $g->id );
			$count = is_array( $recs ) ? count( $recs ) : $count;
		}
		return array(
			'id'           => (int) $g->id,
			'name'         => (string) $g->name,
			'description'  => (string) ( $g->description ?? '' ),
			'type'         => (string) ( $g->type ?? 'custom' ),
			'contact_ids'  => array_map( 'intval', $contact_ids ),
			'contact_count'=> $count,
			'created_at'   => (string) ( $g->created_at ?? '' ),
			'created_fa'   => self::to_jalali( $g->created_at ?? '' ),
		);
	}

	public static function list_books( WP_REST_Request $request ) {
		$core = self::core();
		if ( ! $core ) {
			return new WP_Error( 'no_campaign', 'ماژول کمپین بارگذاری نشده', array( 'status' => 500 ) );
		}
		$search = sanitize_text_field( $request->get_param( 'search' ) ?: '' );
		$groups = $core->get_groups( null, $search );
		$items  = array();
		foreach ( (array) $groups as $g ) {
			$items[] = self::map_group( $g, $core );
		}
		return rest_ensure_response( array( 'ok' => true, 'items' => $items ) );
	}

	public static function get_book( WP_REST_Request $request ) {
		$core = self::core();
		if ( ! $core ) {
			return new WP_Error( 'no_campaign', 'ماژول کمپین بارگذاری نشده', array( 'status' => 500 ) );
		}
		$id = (int) $request['id'];
		$g  = $core->get_group( $id );
		if ( ! $g ) {
			return new WP_Error( 'not_found', 'دفترچه یافت نشد', array( 'status' => 404 ) );
		}
		$recipients = $core->get_group_recipients( $id );
		$contacts   = array();
		foreach ( (array) $recipients as $r ) {
			$contacts[] = array(
				'id'    => isset( $r['id'] ) ? (int) $r['id'] : 0,
				'name'  => (string) ( $r['name'] ?? '' ),
				'email' => (string) ( $r['email'] ?? '' ),
				'phone' => (string) ( $r['phone'] ?? '' ),
				'source'=> (string) ( $r['source'] ?? '' ),
			);
		}
		return rest_ensure_response(
			array(
				'ok'       => true,
				'book'     => self::map_group( $g, $core ),
				'contacts' => $contacts,
			)
		);
	}

	/**
	 * Create contact book from aggregated contacts.
	 * Body: name, contacts:[{name,email,phone}], user_ids:[int]
	 */
	public static function create_book( WP_REST_Request $request ) {
		$core = self::core();
		if ( ! $core ) {
			return new WP_Error( 'no_campaign', 'ماژول کمپین بارگذاری نشده', array( 'status' => 500 ) );
		}
		$name = sanitize_text_field( $request->get_param( 'name' ) ?: '' );
		if ( strlen( $name ) < 2 ) {
			return new WP_Error( 'bad_name', 'نام دفترچه حداقل ۲ کاراکتر باشد', array( 'status' => 400 ) );
		}

		$contact_ids = array();
		$raw_contacts = $request->get_param( 'contacts' );
		if ( is_string( $raw_contacts ) ) {
			$raw_contacts = json_decode( $raw_contacts, true );
		}
		if ( is_array( $raw_contacts ) ) {
			foreach ( $raw_contacts as $c ) {
				if ( ! is_array( $c ) ) {
					continue;
				}
				$email = sanitize_email( $c['email'] ?? '' );
				$phone = isset( $c['phone'] ) ? (string) $c['phone'] : '';
				$cname = sanitize_text_field( $c['name'] ?? '' );
				if ( ! $email && ! $phone ) {
					continue;
				}
				// ensure unique email for table; synthetic if only phone
				if ( ! $email && $phone ) {
					$email = preg_replace( '/\D+/', '', $phone ) . '@sms.local';
				}
				$r = $core->add_contact(
					array(
						'name'     => $cname,
						'email'    => $email,
						'phone'    => $phone,
						'category' => 'manager',
					)
				);
				if ( ! empty( $r['success'] ) && ! empty( $r['id'] ) ) {
					$contact_ids[] = (int) $r['id'];
				} elseif ( $email ) {
					// already exists — find id
					global $wpdb;
					$tid = (int) $wpdb->get_var(
						$wpdb->prepare(
							'SELECT id FROM ' . $wpdb->prefix . 'ezlens_campaign_contacts WHERE email = %s LIMIT 1',
							$email
						)
					);
					if ( $tid ) {
						$contact_ids[] = $tid;
					}
				}
			}
		}

		// Site users → convert to contacts
		$user_ids = $request->get_param( 'user_ids' );
		if ( is_string( $user_ids ) ) {
			$user_ids = json_decode( $user_ids, true );
		}
		if ( is_array( $user_ids ) ) {
			foreach ( $user_ids as $uid ) {
				$uid  = absint( $uid );
				$user = get_userdata( $uid );
				if ( ! $user ) {
					continue;
				}
				$phone = get_user_meta( $uid, 'billing_phone', true );
				if ( ! $phone ) {
					$phone = get_user_meta( $uid, 'user_phone', true );
				}
				if ( ! $phone && preg_match( '/^09\d{9}$/', $user->user_login ) ) {
					$phone = $user->user_login;
				}
				$email = $user->user_email ? $user->user_email : ( 'user' . $uid . '@site.local' );
				$r     = $core->add_contact(
					array(
						'name'     => $user->display_name,
						'email'    => $email,
						'phone'    => (string) $phone,
						'category' => 'site',
					)
				);
				if ( ! empty( $r['success'] ) && ! empty( $r['id'] ) ) {
					$contact_ids[] = (int) $r['id'];
				} else {
					global $wpdb;
					$tid = (int) $wpdb->get_var(
						$wpdb->prepare(
							'SELECT id FROM ' . $wpdb->prefix . 'ezlens_campaign_contacts WHERE email = %s LIMIT 1',
							$email
						)
					);
					if ( $tid ) {
						$contact_ids[] = $tid;
					}
				}
			}
		}

		$contact_ids = array_values( array_unique( array_filter( array_map( 'intval', $contact_ids ) ) ) );
		if ( ! $contact_ids ) {
			return new WP_Error( 'empty', 'حداقل یک مخاطب لازم است', array( 'status' => 400 ) );
		}

		$result = $core->create_group(
			array(
				'name'        => $name,
				'description' => sanitize_textarea_field( $request->get_param( 'description' ) ?: '' ),
				'type'        => 'custom',
				'contact_ids' => $contact_ids,
			)
		);
		if ( empty( $result['success'] ) ) {
			return new WP_Error( 'create_failed', $result['message'] ?? 'خطا در ایجاد دفترچه', array( 'status' => 400 ) );
		}
		$g = $core->get_group( (int) $result['id'] );
		return rest_ensure_response(
			array(
				'ok'      => true,
				'message' => $result['message'] ?? 'دفترچه ذخیره شد',
				'book'    => self::map_group( $g, $core ),
			)
		);
	}

	public static function update_book( WP_REST_Request $request ) {
		$core = self::core();
		if ( ! $core ) {
			return new WP_Error( 'no_campaign', 'ماژول کمپین بارگذاری نشده', array( 'status' => 500 ) );
		}
		$id   = (int) $request['id'];
		$data = array();
		if ( null !== $request->get_param( 'name' ) ) {
			$data['name'] = sanitize_text_field( $request->get_param( 'name' ) );
		}
		if ( null !== $request->get_param( 'description' ) ) {
			$data['description'] = sanitize_textarea_field( $request->get_param( 'description' ) );
		}
		// optional full contact rebuild
		$raw = $request->get_param( 'contact_ids' );
		if ( null !== $raw ) {
			if ( is_string( $raw ) ) {
				$raw = json_decode( $raw, true );
			}
			$data['contact_ids'] = wp_json_encode( array_map( 'intval', (array) $raw ) );
		}
		$r = $core->update_group( $id, $data );
		if ( empty( $r['success'] ) && is_array( $r ) ) {
			return new WP_Error( 'update_failed', $r['message'] ?? 'به‌روزرسانی ناموفق', array( 'status' => 400 ) );
		}
		$g = $core->get_group( $id );
		return rest_ensure_response(
			array(
				'ok'      => true,
				'message' => is_array( $r ) ? ( $r['message'] ?? 'به‌روز شد' ) : 'به‌روز شد',
				'book'    => self::map_group( $g, $core ),
			)
		);
	}

	public static function delete_book( WP_REST_Request $request ) {
		$core = self::core();
		if ( ! $core ) {
			return new WP_Error( 'no_campaign', 'ماژول کمپین بارگذاری نشده', array( 'status' => 500 ) );
		}
		$id = (int) $request['id'];
		if ( ! $core->delete_group( $id ) ) {
			return new WP_Error( 'delete_failed', 'حذف دفترچه ناموفق', array( 'status' => 400 ) );
		}
		return rest_ensure_response( array( 'ok' => true, 'message' => 'دفترچه حذف شد' ) );
	}

	public static function list_customers( WP_REST_Request $request ) {
		$q     = sanitize_text_field( $request->get_param( 'search' ) ?: '' );
		$limit = max( 5, min( 100, (int) ( $request->get_param( 'per_page' ) ?: 40 ) ) );
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

	private static function map_campaign( $c, $core = null ) {
		if ( ! $c ) {
			return null;
		}
		$stats = array();
		if ( ! empty( $c->stats ) ) {
			$decoded = json_decode( $c->stats, true );
			if ( is_array( $decoded ) ) {
				$stats = $decoded;
			}
		}
		$progress = array( 'total' => 0, 'sent' => 0, 'failed' => 0, 'pending' => 0 );
		if ( $core && method_exists( $core, 'get_campaign_progress' ) ) {
			$p = $core->get_campaign_progress( (int) $c->id );
			if ( is_array( $p ) ) {
				$progress = array_merge( $progress, $p );
			}
		}
		$sent = (int) ( $progress['sent'] ?? ( $stats['sent'] ?? 0 ) );
		$total = (int) ( $progress['total'] ?? ( $stats['total'] ?? 0 ) );
		return array(
			'id'         => (int) $c->id,
			'name'       => (string) $c->name,
			'type'       => (string) $c->type,
			'subject'    => (string) ( $c->subject ?? '' ),
			'message'    => (string) ( $c->message ?? '' ),
			'file_url'   => (string) ( $c->file_attachment ?? '' ),
			'status'     => (string) $c->status,
			'created_at' => (string) ( $c->created_at ?? '' ),
			'created_fa' => self::to_jalali( $c->created_at ?? '' ),
			'sent_at'    => (string) ( $c->sent_at ?? '' ),
			'sent_fa'    => self::to_jalali( $c->sent_at ?? '' ),
			'sent'       => $sent,
			'total'      => $total,
			'failed'     => (int) ( $progress['failed'] ?? 0 ),
			'stats'      => $stats,
		);
	}

	public static function list_campaigns( WP_REST_Request $request ) {
		$core = self::core();
		if ( ! $core ) {
			return new WP_Error( 'no_campaign', 'ماژول کمپین بارگذاری نشده', array( 'status' => 500 ) );
		}
		$status = sanitize_key( $request->get_param( 'status' ) ?: 'all' );
		$search = sanitize_text_field( $request->get_param( 'search' ) ?: '' );
		$page   = max( 1, (int) $request->get_param( 'page' ) );
		$per    = max( 5, min( 50, (int) ( $request->get_param( 'per_page' ) ?: 20 ) ) );
		$offset = ( $page - 1 ) * $per;
		$rows   = $core->get_campaigns( $status === 'all' ? null : $status, $search, $per, $offset );
		$total  = method_exists( $core, 'count_campaigns' )
			? (int) $core->count_campaigns( $status === 'all' ? null : $status, $search )
			: count( (array) $rows );
		$items = array();
		foreach ( (array) $rows as $c ) {
			$items[] = self::map_campaign( $c, $core );
		}
		return rest_ensure_response(
			array(
				'ok'    => true,
				'items' => $items,
				'total' => $total,
				'page'  => $page,
				'pages' => max( 1, (int) ceil( $total / $per ) ),
			)
		);
	}

	public static function get_campaign( WP_REST_Request $request ) {
		$core = self::core();
		if ( ! $core ) {
			return new WP_Error( 'no_campaign', 'ماژول کمپین بارگذاری نشده', array( 'status' => 500 ) );
		}
		global $wpdb;
		$id = (int) $request['id'];
		$c  = $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . $wpdb->prefix . 'ezlens_campaigns WHERE id = %d', $id ) );
		if ( ! $c ) {
			return new WP_Error( 'not_found', 'کمپین یافت نشد', array( 'status' => 404 ) );
		}
		return rest_ensure_response( array( 'ok' => true, 'campaign' => self::map_campaign( $c, $core ) ) );
	}

	/**
	 * Create + optionally send email and/or SMS campaigns for a book.
	 * Body: book_id, name, subject, email_body, sms_body, file_base64, file_name, send (bool)
	 */
	public static function create_and_send( WP_REST_Request $request ) {
		$core = self::core();
		if ( ! $core ) {
			return new WP_Error( 'no_campaign', 'ماژول کمپین بارگذاری نشده', array( 'status' => 500 ) );
		}
		$book_id    = absint( $request->get_param( 'book_id' ) );
		$name       = sanitize_text_field( $request->get_param( 'name' ) ?: '' );
		$subject    = sanitize_text_field( $request->get_param( 'subject' ) ?: '' );
		$email_body = wp_kses_post( $request->get_param( 'email_body' ) ?: '' );
		$sms_body   = sanitize_textarea_field( $request->get_param( 'sms_body' ) ?: '' );
		$do_send    = filter_var( $request->get_param( 'send' ), FILTER_VALIDATE_BOOLEAN );
		if ( ! $book_id ) {
			return new WP_Error( 'no_book', 'دفترچه مخاطبان را انتخاب کنید', array( 'status' => 400 ) );
		}
		if ( $name === '' ) {
			return new WP_Error( 'no_name', 'عنوان کمپین را وارد کنید', array( 'status' => 400 ) );
		}
		if ( $email_body === '' && $sms_body === '' ) {
			return new WP_Error( 'no_body', 'حداقل یکی از محتوای ایمیل یا پیامک لازم است', array( 'status' => 400 ) );
		}

		$file_url = '';
		$b64      = $request->get_param( 'file_base64' );
		$filename = sanitize_file_name( $request->get_param( 'file_name' ) ?: 'attachment.bin' );
		if ( is_string( $b64 ) && $b64 !== '' ) {
			$up = self::sideload_base64( $b64, $filename );
			if ( is_wp_error( $up ) ) {
				return $up;
			}
			$file_url = $up['url'];
		}

		$created = array();
		$results = array();

		if ( $email_body !== '' ) {
			$cid = $core->create_campaign(
				array(
					'name'            => $name . ( $sms_body !== '' ? ' (ایمیل)' : '' ),
					'type'            => 'email',
					'subject'         => $subject ? $subject : $name,
					'message'         => $email_body,
					'file_attachment' => $file_url,
					'status'          => 'draft',
					'group_ids'       => array( $book_id ),
				)
			);
			if ( ! $cid ) {
				return new WP_Error( 'create_email', 'ایجاد کمپین ایمیل ناموفق', array( 'status' => 500 ) );
			}
			$created[] = (int) $cid;
			if ( $do_send ) {
				$core->ensure_recipient_snapshot( (int) $cid );
				$results['email'] = $core->send_campaign( (int) $cid, true );
			}
		}

		if ( $sms_body !== '' ) {
			$cid = $core->create_campaign(
				array(
					'name'            => $name . ( $email_body !== '' ? ' (پیامک)' : '' ),
					'type'            => 'sms',
					'subject'         => '',
					'message'         => $sms_body,
					'file_attachment' => '',
					'status'          => 'draft',
					'group_ids'       => array( $book_id ),
				)
			);
			if ( ! $cid ) {
				return new WP_Error( 'create_sms', 'ایجاد کمپین پیامک ناموفق', array( 'status' => 500 ) );
			}
			$created[] = (int) $cid;
			if ( $do_send ) {
				$core->ensure_recipient_snapshot( (int) $cid );
				$results['sms'] = $core->send_campaign( (int) $cid, true );
			}
		}

		$msg = $do_send ? 'کمپین ایجاد و ارسال شد' : 'کمپین به‌عنوان پیش‌نویس ذخیره شد';
		return rest_ensure_response(
			array(
				'ok'       => true,
				'message'  => $msg,
				'ids'      => $created,
				'results'  => $results,
			)
		);
	}

	public static function update_campaign( WP_REST_Request $request ) {
		$core = self::core();
		if ( ! $core ) {
			return new WP_Error( 'no_campaign', 'ماژول کمپین بارگذاری نشده', array( 'status' => 500 ) );
		}
		$id   = (int) $request['id'];
		$data = array();
		foreach ( array( 'name', 'subject', 'message', 'status' ) as $k ) {
			if ( null !== $request->get_param( $k ) ) {
				$data[ $k ] = $request->get_param( $k );
			}
		}
		if ( null !== $request->get_param( 'book_id' ) ) {
			$data['group_ids'] = array( absint( $request->get_param( 'book_id' ) ) );
		}
		$r = $core->update_campaign( $id, $data );
		if ( is_array( $r ) && empty( $r['success'] ) ) {
			return new WP_Error( 'update_failed', $r['message'] ?? 'ویرایش ناموفق', array( 'status' => 400 ) );
		}
		return rest_ensure_response( array( 'ok' => true, 'message' => is_array( $r ) ? ( $r['message'] ?? 'به‌روز شد' ) : 'به‌روز شد' ) );
	}

	public static function delete_campaign( WP_REST_Request $request ) {
		$core = self::core();
		if ( ! $core ) {
			return new WP_Error( 'no_campaign', 'ماژول کمپین بارگذاری نشده', array( 'status' => 500 ) );
		}
		$id = (int) $request['id'];
		if ( ! $core->delete_campaign( $id ) ) {
			return new WP_Error( 'delete_failed', 'حذف کمپین ناموفق', array( 'status' => 400 ) );
		}
		return rest_ensure_response( array( 'ok' => true, 'message' => 'کمپین حذف شد' ) );
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
		return array( 'path' => $upload['file'], 'url' => $upload['url'] );
	}
}

EzLens_Manager_Campaign_REST::init();
