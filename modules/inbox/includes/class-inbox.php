<?php
/**
 * EzLens Inbox — IMAP mailbox manager
 *
 * @package EzLens_Secure_Login
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class EzLens_Inbox {

	const OPTION = 'ezlens_inbox_settings';

	public static function defaults() {
		return array(
			'host'     => 'mail.ezlens.ir',
			'port'     => 993,
			'encrypt'  => 'ssl',
			'username' => 'info@ezlens.ir',
			'password' => '',
			'mailbox'  => 'INBOX',
			'sent_box' => 'INBOX.Sent',
		);
	}

	public static function get_settings() {
		$s = get_option( self::OPTION, array() );
		if ( ! is_array( $s ) ) {
			$s = array();
		}
		return wp_parse_args( $s, self::defaults() );
	}

	public static function has_password() {
		$s = self::get_settings();
		return ! empty( $s['password'] );
	}

	public static function save_settings( $data ) {
		$cur = self::get_settings();
		$out = array(
			'host'     => sanitize_text_field( $data['host'] ?? $cur['host'] ),
			'port'     => absint( $data['port'] ?? $cur['port'] ) ?: 993,
			'encrypt'  => sanitize_key( $data['encrypt'] ?? $cur['encrypt'] ),
			'username' => sanitize_text_field( $data['username'] ?? $cur['username'] ),
			'mailbox'  => sanitize_text_field( $data['mailbox'] ?? $cur['mailbox'] ),
			'sent_box' => sanitize_text_field( $data['sent_box'] ?? $cur['sent_box'] ),
			'password' => $cur['password'],
		);
		if ( ! empty( $data['password'] ) ) {
			$out['password'] = self::encrypt( (string) $data['password'] );
		}
		update_option( self::OPTION, $out, false );
		return $out;
	}

	public static function encrypt( $plain ) {
		if ( ! function_exists( 'openssl_encrypt' ) ) {
			return base64_encode( $plain );
		}
		$key = hash( 'sha256', wp_salt( 'auth' ), true );
		$iv  = substr( hash( 'sha256', wp_salt( 'secure_auth' ), true ), 0, 16 );
		return base64_encode( openssl_encrypt( $plain, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv ) );
	}

	public static function decrypt( $cipher ) {
		if ( $cipher === '' || $cipher === null ) {
			return '';
		}
		$raw = base64_decode( $cipher, true );
		if ( false === $raw ) {
			return '';
		}
		if ( ! function_exists( 'openssl_decrypt' ) ) {
			return $raw;
		}
		$key = hash( 'sha256', wp_salt( 'auth' ), true );
		$iv  = substr( hash( 'sha256', wp_salt( 'secure_auth' ), true ), 0, 16 );
		$d   = openssl_decrypt( $raw, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv );
		return false !== $d ? $d : '';
	}

	public static function imap_available() {
		return function_exists( 'imap_open' );
	}

	public static function mailbox_path( $folder = null ) {
		$s      = self::get_settings();
		$host   = $s['host'];
		$port   = (int) $s['port'];
		$enc    = $s['encrypt'];
		$folder = $folder ? $folder : $s['mailbox'];
		$flags  = '/imap';
		if ( 'ssl' === $enc ) {
			$flags .= '/ssl';
		} elseif ( 'tls' === $enc ) {
			$flags .= '/tls';
		}
		$flags .= '/novalidate-cert';
		return '{' . $host . ':' . $port . $flags . '}' . $folder;
	}

	/**
	 * @return resource|false|WP_Error
	 */
	public static function connect( $folder = null ) {
		if ( ! self::imap_available() ) {
			return new WP_Error( 'no_imap', 'افزونه PHP IMAP روی سرور فعال نیست.' );
		}
		$s    = self::get_settings();
		$pass = self::decrypt( $s['password'] );
		if ( $pass === '' ) {
			return new WP_Error( 'no_pass', 'رمز ایمیل ذخیره نشده. از تب تنظیمات وارد کنید.' );
		}
		$mbox = @imap_open( self::mailbox_path( $folder ), $s['username'], $pass );
		if ( ! $mbox ) {
			$err = function_exists( 'imap_last_error' ) ? imap_last_error() : '';
			return new WP_Error( 'connect', 'اتصال ناموفق' . ( $err ? ': ' . $err : '' ) );
		}
		return $mbox;
	}

	public static function decode_mime( $str ) {
		if ( ! is_string( $str ) || $str === '' ) {
			return '';
		}
		if ( ! function_exists( 'imap_mime_header_decode' ) ) {
			return $str;
		}
		$parts = @imap_mime_header_decode( $str );
		$out   = '';
		if ( is_array( $parts ) ) {
			foreach ( $parts as $p ) {
				$out .= isset( $p->text ) ? $p->text : '';
			}
		}
		return $out !== '' ? $out : $str;
	}

	/** تبدیل تقریبی میلادی → هجری شمسی */
	public static function to_jalali( $timestamp ) {
		if ( ! $timestamp ) {
			return '';
		}
		$gy = (int) gmdate( 'Y', $timestamp );
		$gm = (int) gmdate( 'n', $timestamp );
		$gd = (int) gmdate( 'j', $timestamp );
		$g_d_m = array( 0, 31, 59, 90, 120, 151, 181, 212, 243, 273, 304, 334 );
		$gy2   = ( $gm > 2 ) ? ( $gy + 1 ) : $gy;
		$days  = 355666 + ( 365 * $gy ) + (int) ( ( $gy2 + 3 ) / 4 ) - (int) ( ( $gy2 + 99 ) / 100 ) + (int) ( ( $gy2 + 399 ) / 400 ) + $gd + $g_d_m[ $gm - 1 ];
		$jy    = -1595 + ( 33 * (int) ( $days / 12053 ) );
		$days %= 12053;
		$jy   += 4 * (int) ( $days / 1461 );
		$days %= 1461;
		if ( $days > 365 ) {
			$jy   += (int) ( ( $days - 1 ) / 365 );
			$days  = ( $days - 1 ) % 365;
		}
		if ( $days < 186 ) {
			$jm = 1 + (int) ( $days / 31 );
			$jd = 1 + ( $days % 31 );
		} else {
			$jm = 7 + (int) ( ( $days - 186 ) / 30 );
			$jd = 1 + ( ( $days - 186 ) % 30 );
		}
		$h = gmdate( 'H:i', $timestamp + ( (int) get_option( 'gmt_offset', 3.5 ) * HOUR_IN_SECONDS ) );
		return sprintf( '%04d/%02d/%02d · %s', $jy, $jm, $jd, $h );
	}

	public static function parse_date_ts( $date_str ) {
		$t = strtotime( $date_str );
		return $t ? $t : 0;
	}

	/**
	 * لیست پیام‌ها با صفحه‌بندی و جستجو
	 *
	 * @param string $folder inbox|sent
	 * @param int    $page
	 * @param int    $per_page
	 * @param string $search
	 * @param string $filter all|unseen|seen
	 */
	public static function fetch_list( $folder = 'inbox', $page = 1, $per_page = 20, $search = '', $filter = 'all' ) {
		$s      = self::get_settings();
		$box    = ( 'sent' === $folder ) ? $s['sent_box'] : $s['mailbox'];
		$mbox   = self::connect( $box );
		if ( is_wp_error( $mbox ) ) {
			// تلاش پوشه‌های رایج Sent
			if ( 'sent' === $folder ) {
				foreach ( array( 'Sent', 'INBOX.Sent', 'Sent Items', 'INBOX.Sent Messages' ) as $try ) {
					$mbox = self::connect( $try );
					if ( ! is_wp_error( $mbox ) ) {
						break;
					}
				}
			}
			if ( is_wp_error( $mbox ) ) {
				return array( 'ok' => false, 'message' => $mbox->get_error_message() );
			}
		}

		$criteria = 'ALL';
		if ( 'unseen' === $filter ) {
			$criteria = 'UNSEEN';
		} elseif ( 'seen' === $filter ) {
			$criteria = 'SEEN';
		}
		if ( $search !== '' ) {
			$q = str_replace( array( '\\', '"' ), array( '\\\\', '\\"' ), $search );
			$criteria .= ' TEXT "' . $q . '"';
		}

		$uids = @imap_search( $mbox, $criteria, SE_UID );
		if ( false === $uids || ! is_array( $uids ) ) {
			$uids = array();
		}
		rsort( $uids, SORT_NUMERIC );
		$total = count( $uids );
		$page  = max( 1, (int) $page );
		$per   = max( 5, min( 50, (int) $per_page ) );
		$slice = array_slice( $uids, ( $page - 1 ) * $per, $per );
		$items = array();

		foreach ( $slice as $uid ) {
			$msgno = imap_msgno( $mbox, $uid );
			if ( ! $msgno ) {
				continue;
			}
			$h = @imap_headerinfo( $mbox, $msgno );
			if ( ! $h ) {
				continue;
			}
			$from_email = '';
			$from_name  = '';
			if ( ! empty( $h->from[0] ) ) {
				$f          = $h->from[0];
				$from_email = ( isset( $f->mailbox ) ? $f->mailbox : '' ) . '@' . ( isset( $f->host ) ? $f->host : '' );
				$from_name  = isset( $f->personal ) ? self::decode_mime( $f->personal ) : '';
			}
			$to_email = '';
			if ( ! empty( $h->to[0] ) ) {
				$t        = $h->to[0];
				$to_email = ( isset( $t->mailbox ) ? $t->mailbox : '' ) . '@' . ( isset( $t->host ) ? $t->host : '' );
			}
			$date_raw = isset( $h->date ) ? $h->date : '';
			$ts       = self::parse_date_ts( $date_raw );
			$subject  = isset( $h->subject ) ? self::decode_mime( $h->subject ) : '(بدون موضوع)';
			$unseen   = isset( $h->Unseen ) && $h->Unseen === 'U';
			$items[]  = array(
				'uid'       => (int) $uid,
				'msgno'     => (int) $msgno,
				'subject'   => $subject,
				'from'      => $from_email,
				'from_name' => $from_name,
				'to'        => $to_email,
				'date_raw'  => $date_raw,
				'date_fa'   => self::to_jalali( $ts ),
				'seen'      => ! $unseen,
				'folder'    => $folder,
			);
		}
		imap_close( $mbox );
		return array(
			'ok'        => true,
			'items'     => $items,
			'total'     => $total,
			'page'      => $page,
			'per_page'  => $per,
			'pages'     => max( 1, (int) ceil( $total / $per ) ),
			'has_pass'  => self::has_password(),
		);
	}

	public static function fetch_message( $uid, $folder = 'inbox' ) {
		$s    = self::get_settings();
		$box  = ( 'sent' === $folder ) ? $s['sent_box'] : $s['mailbox'];
		$mbox = self::connect( $box );
		if ( is_wp_error( $mbox ) ) {
			return array( 'ok' => false, 'message' => $mbox->get_error_message() );
		}
		$uid   = (int) $uid;
		$msgno = imap_msgno( $mbox, $uid );
		if ( ! $msgno ) {
			imap_close( $mbox );
			return array( 'ok' => false, 'message' => 'پیام یافت نشد.' );
		}
		$h = @imap_headerinfo( $mbox, $msgno );
		$structure = @imap_fetchstructure( $mbox, $msgno );
		$body_html = '';
		$body_text = '';
		$attachments = array();

		self::parse_parts( $mbox, $msgno, $structure, '', $body_html, $body_text, $attachments );

		if ( $body_html === '' && $body_text === '' ) {
			$body_text = @imap_body( $mbox, $msgno );
		}
		@imap_setflag_full( $mbox, (string) $msgno, '\\Seen' );

		$from_email = '';
		$from_name  = '';
		if ( $h && ! empty( $h->from[0] ) ) {
			$f          = $h->from[0];
			$from_email = ( isset( $f->mailbox ) ? $f->mailbox : '' ) . '@' . ( isset( $f->host ) ? $f->host : '' );
			$from_name  = isset( $f->personal ) ? self::decode_mime( $f->personal ) : '';
		}
		$date_raw = $h && isset( $h->date ) ? $h->date : '';
		$ts       = self::parse_date_ts( $date_raw );
		$subject  = $h && isset( $h->subject ) ? self::decode_mime( $h->subject ) : '';

		imap_close( $mbox );
		return array(
			'ok'          => true,
			'uid'         => $uid,
			'folder'      => $folder,
			'subject'     => $subject,
			'from'        => $from_email,
			'from_name'   => $from_name,
			'date_fa'     => self::to_jalali( $ts ),
			'date_raw'    => $date_raw,
			'body_html'   => $body_html,
			'body_text'   => $body_text,
			'attachments' => $attachments,
		);
	}

	private static function parse_parts( $mbox, $msgno, $structure, $prefix, &$html, &$text, &$attachments ) {
		if ( ! $structure ) {
			return;
		}
		$type = isset( $structure->type ) ? (int) $structure->type : 0;
		// multipart
		if ( $type === 1 && ! empty( $structure->parts ) ) {
			foreach ( $structure->parts as $i => $part ) {
				$part_no = $prefix === '' ? (string) ( $i + 1 ) : $prefix . '.' . ( $i + 1 );
				self::parse_parts( $mbox, $msgno, $part, $part_no, $html, $text, $attachments );
			}
			return;
		}
		$part_no = $prefix === '' ? '1' : $prefix;
		$subtype = isset( $structure->subtype ) ? strtoupper( $structure->subtype ) : '';
		$disposition = '';
		if ( ! empty( $structure->disposition ) ) {
			$disposition = strtolower( $structure->disposition );
		}
		$filename = '';
		if ( ! empty( $structure->dparameters ) ) {
			foreach ( $structure->dparameters as $p ) {
				if ( strtolower( $p->attribute ) === 'filename' ) {
					$filename = self::decode_mime( $p->value );
				}
			}
		}
		if ( ! $filename && ! empty( $structure->parameters ) ) {
			foreach ( $structure->parameters as $p ) {
				if ( strtolower( $p->attribute ) === 'name' ) {
					$filename = self::decode_mime( $p->value );
				}
			}
		}
		$is_attach = ( $disposition === 'attachment' ) || ( $filename !== '' && $type !== 0 );
		if ( $is_attach && $filename ) {
			$attachments[] = array(
				'part'     => $part_no,
				'filename' => $filename,
				'size'     => isset( $structure->bytes ) ? (int) $structure->bytes : 0,
			);
			return;
		}
		$data = @imap_fetchbody( $mbox, $msgno, $part_no );
		$enc  = isset( $structure->encoding ) ? (int) $structure->encoding : 0;
		if ( $enc === 3 ) {
			$data = base64_decode( $data );
		} elseif ( $enc === 4 ) {
			$data = quoted_printable_decode( $data );
		}
		if ( $type === 0 && $subtype === 'HTML' && $html === '' ) {
			$html = $data;
		} elseif ( $type === 0 && $html === '' && $text === '' ) {
			$text = $data;
		}
	}

	public static function get_attachment( $uid, $part, $folder = 'inbox' ) {
		$s    = self::get_settings();
		$box  = ( 'sent' === $folder ) ? $s['sent_box'] : $s['mailbox'];
		$mbox = self::connect( $box );
		if ( is_wp_error( $mbox ) ) {
			return $mbox;
		}
		$msgno = imap_msgno( $mbox, (int) $uid );
		if ( ! $msgno ) {
			imap_close( $mbox );
			return new WP_Error( 'nf', 'پیام یافت نشد' );
		}
		$structure = @imap_fetchstructure( $mbox, $msgno );
		$filename  = 'attachment';
		$body      = @imap_fetchbody( $mbox, $msgno, $part );
		$enc       = 0;
		// find encoding of part
		$target = self::find_part( $structure, $part );
		if ( $target ) {
			$enc = isset( $target->encoding ) ? (int) $target->encoding : 0;
			if ( ! empty( $target->dparameters ) ) {
				foreach ( $target->dparameters as $p ) {
					if ( strtolower( $p->attribute ) === 'filename' ) {
						$filename = self::decode_mime( $p->value );
					}
				}
			}
		}
		if ( $enc === 3 ) {
			$body = base64_decode( $body );
		} elseif ( $enc === 4 ) {
			$body = quoted_printable_decode( $body );
		}
		imap_close( $mbox );
		return array(
			'filename' => $filename,
			'data'     => $body,
		);
	}

	private static function find_part( $structure, $part_no, $prefix = '' ) {
		if ( ! $structure ) {
			return null;
		}
		$type = isset( $structure->type ) ? (int) $structure->type : 0;
		if ( $type === 1 && ! empty( $structure->parts ) ) {
			foreach ( $structure->parts as $i => $part ) {
				$no = $prefix === '' ? (string) ( $i + 1 ) : $prefix . '.' . ( $i + 1 );
				if ( $no === (string) $part_no ) {
					return $part;
				}
				$found = self::find_part( $part, $part_no, $no );
				if ( $found ) {
					return $found;
				}
			}
		}
		return null;
	}

	public static function delete_message( $uid, $folder = 'inbox' ) {
		$s    = self::get_settings();
		$box  = ( 'sent' === $folder ) ? $s['sent_box'] : $s['mailbox'];
		$mbox = self::connect( $box );
		if ( is_wp_error( $mbox ) ) {
			return array( 'ok' => false, 'message' => $mbox->get_error_message() );
		}
		$msgno = imap_msgno( $mbox, (int) $uid );
		if ( ! $msgno ) {
			imap_close( $mbox );
			return array( 'ok' => false, 'message' => 'پیام یافت نشد' );
		}
		@imap_delete( $mbox, (string) $msgno );
		@imap_expunge( $mbox );
		imap_close( $mbox );
		return array( 'ok' => true, 'message' => 'ایمیل حذف شد' );
	}

	public static function test_connection() {
		$mbox = self::connect();
		if ( is_wp_error( $mbox ) ) {
			return array( 'ok' => false, 'message' => $mbox->get_error_message() );
		}
		$n = @imap_num_msg( $mbox );
		imap_close( $mbox );
		return array( 'ok' => true, 'message' => 'اتصال موفق — تعداد پیام صندوق: ' . (int) $n );
	}
}
