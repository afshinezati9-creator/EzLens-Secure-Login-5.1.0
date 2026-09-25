<?php
/**
 * هم‌یاری بینایی — donations for eye-care needs
 *
 * Separate from wallet/gift: purpose-driven, transparent impact.
 *
 * @package EzLens_Secure_Login
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class EzLens_CD_Charity {

	public static function cases_table() {
		global $wpdb;
		return $wpdb->prefix . 'ezlens_cd_charity_cases';
	}

	public static function donations_table() {
		global $wpdb;
		return $wpdb->prefix . 'ezlens_cd_charity_donations';
	}

	public static function updates_table() {
		global $wpdb;
		return $wpdb->prefix . 'ezlens_cd_charity_updates';
	}

	public static function maybe_create_tables() {
		global $wpdb;
		$charset = $wpdb->get_charset_collate();
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		dbDelta(
			'CREATE TABLE ' . self::cases_table() . " (
				id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				title varchar(200) NOT NULL DEFAULT '',
				summary text NULL,
				need_type varchar(32) NOT NULL DEFAULT 'glasses',
				goal_amount decimal(18,0) NOT NULL DEFAULT 0,
				raised_amount decimal(18,0) NOT NULL DEFAULT 0,
				status varchar(16) NOT NULL DEFAULT 'open',
				is_public tinyint(1) NOT NULL DEFAULT 1,
				cover_id bigint(20) unsigned NOT NULL DEFAULT 0,
				created_at datetime NOT NULL,
				updated_at datetime NOT NULL,
				PRIMARY KEY (id),
				KEY status (status)
			) {$charset};"
		);

		dbDelta(
			'CREATE TABLE ' . self::donations_table() . " (
				id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				user_id bigint(20) unsigned NOT NULL DEFAULT 0,
				case_id bigint(20) unsigned NOT NULL DEFAULT 0,
				amount decimal(18,0) NOT NULL DEFAULT 0,
				source varchar(16) NOT NULL DEFAULT 'wallet',
				is_anonymous tinyint(1) NOT NULL DEFAULT 0,
				message text NULL,
				status varchar(16) NOT NULL DEFAULT 'confirmed',
				created_at datetime NOT NULL,
				PRIMARY KEY (id),
				KEY user_id (user_id),
				KEY case_id (case_id),
				KEY status (status)
			) {$charset};"
		);

		dbDelta(
			'CREATE TABLE ' . self::updates_table() . " (
				id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				donation_id bigint(20) unsigned NOT NULL DEFAULT 0,
				case_id bigint(20) unsigned NOT NULL DEFAULT 0,
				spent_amount decimal(18,0) NOT NULL DEFAULT 0,
				beneficiary_label varchar(200) NOT NULL DEFAULT '',
				purpose text NULL,
				thank_you text NULL,
				attachment_id bigint(20) unsigned NOT NULL DEFAULT 0,
				admin_id bigint(20) unsigned NOT NULL DEFAULT 0,
				created_at datetime NOT NULL,
				PRIMARY KEY (id),
				KEY donation_id (donation_id),
				KEY case_id (case_id)
			) {$charset};"
		);
	}

	public static function need_labels() {
		return array(
			'glasses'   => 'عینک طبی',
			'lenses'    => 'لنز تماسی',
			'surgery'   => 'جراحی چشم',
			'exam'      => 'معاینه و تشخیص',
			'medicine'  => 'دارو و درمان',
			'other'     => 'سایر',
		);
	}

	public static function format_amount( $n ) {
		$n = (int) $n;
		if ( function_exists( 'wc_price' ) ) {
			return wp_strip_all_tags( wc_price( $n ) );
		}
		return number_format_i18n( $n ) . ' تومان';
	}

	/** @return object[] */
	public static function public_cases( $limit = 12 ) {
		global $wpdb;
		$rows = $wpdb->get_results(
			$wpdb->prepare(
				'SELECT * FROM ' . self::cases_table() . " WHERE is_public = 1 AND status IN ('open','funded') ORDER BY FIELD(status,'open','funded'), id DESC LIMIT %d",
				$limit
			)
		);
		return is_array( $rows ) ? $rows : array();
	}

	public static function get_case( $id ) {
		global $wpdb;
		return $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . self::cases_table() . ' WHERE id = %d', absint( $id ) ) );
	}

	/** @return object[] */
	public static function user_donations( $user_id = 0, $limit = 50 ) {
		global $wpdb;
		$user_id = $user_id ? absint( $user_id ) : get_current_user_id();
		$rows    = $wpdb->get_results(
			$wpdb->prepare(
				'SELECT d.*, c.title AS case_title FROM ' . self::donations_table() . ' d
				LEFT JOIN ' . self::cases_table() . ' c ON c.id = d.case_id
				WHERE d.user_id = %d ORDER BY d.id DESC LIMIT %d',
				$user_id,
				$limit
			)
		);
		return is_array( $rows ) ? $rows : array();
	}

	public static function user_total( $user_id = 0 ) {
		global $wpdb;
		$user_id = $user_id ? absint( $user_id ) : get_current_user_id();
		return (int) $wpdb->get_var(
			$wpdb->prepare(
				'SELECT COALESCE(SUM(amount),0) FROM ' . self::donations_table() . " WHERE user_id = %d AND status = 'confirmed'",
				$user_id
			)
		);
	}

	/** Updates visible to a donor for their donations */
	public static function updates_for_user( $user_id = 0 ) {
		global $wpdb;
		$user_id = $user_id ? absint( $user_id ) : get_current_user_id();
		$rows    = $wpdb->get_results(
			$wpdb->prepare(
				'SELECT u.*, d.amount AS donation_amount, c.title AS case_title
				FROM ' . self::updates_table() . ' u
				INNER JOIN ' . self::donations_table() . ' d ON d.id = u.donation_id
				LEFT JOIN ' . self::cases_table() . ' c ON c.id = u.case_id
				WHERE d.user_id = %d
				ORDER BY u.id DESC LIMIT 40',
				$user_id
			)
		);
		return is_array( $rows ) ? $rows : array();
	}

	/**
	 * Donate from wallet balance (preferred transparent path).
	 *
	 * @return int|WP_Error donation id
	 */
	public static function donate( $args, $user_id = 0 ) {
		global $wpdb;
		$user_id = $user_id ? absint( $user_id ) : get_current_user_id();
		if ( ! $user_id ) {
			return new WP_Error( 'auth', 'برای ثبت کمک وارد شوید' );
		}

		$amount = isset( $args['amount'] ) ? absint( preg_replace( '/\D+/', '', str_replace(
			array( '۰','۱','۲','۳','۴','۵','۶','۷','۸','۹' ),
			array( '0','1','2','3','4','5','6','7','8','9' ),
			(string) $args['amount']
		) ) ) : 0;

		if ( $amount < 10000 ) {
			return new WP_Error( 'amount', 'حداقل مبلغ کمک ۱۰٬۰۰۰ تومان است' );
		}

		$case_id = isset( $args['case_id'] ) ? absint( $args['case_id'] ) : 0;
		$anon    = ! empty( $args['is_anonymous'] ) ? 1 : 0;
		$message = isset( $args['message'] ) ? sanitize_textarea_field( $args['message'] ) : '';
		$source  = isset( $args['source'] ) ? sanitize_key( $args['source'] ) : 'wallet';

		if ( $case_id ) {
			$case = self::get_case( $case_id );
			if ( ! $case || 'closed' === $case->status ) {
				return new WP_Error( 'case', 'این مورد برای دریافت کمک باز نیست' );
			}
		}

		if ( 'wallet' === $source && class_exists( 'EzLens_CD_Wallet' ) ) {
			$bal = EzLens_CD_Wallet::balance( $user_id );
			if ( $bal < $amount ) {
				return new WP_Error( 'balance', 'موجودی کیف پول کافی نیست. ابتدا کیف پول را شارژ کنید یا مبلغ کمتری انتخاب کنید.' );
			}
			$debit = EzLens_CD_Wallet::add_entry(
				$user_id,
				$amount,
				'debit',
				'charity',
				$case_id ? ( 'هم‌یاری بینایی — مورد #' . $case_id ) : 'هم‌یاری بینایی'
			);
			if ( is_wp_error( $debit ) ) {
				return $debit;
			}
		}

		$now = current_time( 'mysql' );
		$ok  = $wpdb->insert(
			self::donations_table(),
			array(
				'user_id'      => $user_id,
				'case_id'      => $case_id,
				'amount'       => $amount,
				'source'       => $source,
				'is_anonymous' => $anon,
				'message'      => $message,
				'status'       => 'confirmed',
				'created_at'   => $now,
			)
		);
		if ( ! $ok ) {
			return new WP_Error( 'db', 'ثبت کمک ممکن نشد' );
		}
		$donation_id = (int) $wpdb->insert_id;

		if ( $case_id ) {
			$wpdb->query(
				$wpdb->prepare(
					'UPDATE ' . self::cases_table() . ' SET raised_amount = raised_amount + %d, updated_at = %s WHERE id = %d',
					$amount,
					$now,
					$case_id
				)
			);
			$case = self::get_case( $case_id );
			if ( $case && (int) $case->goal_amount > 0 && (int) $case->raised_amount >= (int) $case->goal_amount ) {
				$wpdb->update( self::cases_table(), array( 'status' => 'funded', 'updated_at' => $now ), array( 'id' => $case_id ) );
			}
		}

		do_action( 'ezcd_charity_donated', $donation_id, $user_id, $amount, $case_id );
		return $donation_id;
	}

	/* ---------- Admin ---------- */

	public static function admin_cases( $limit = 50 ) {
		global $wpdb;
		$rows = $wpdb->get_results(
			$wpdb->prepare( 'SELECT * FROM ' . self::cases_table() . ' ORDER BY id DESC LIMIT %d', $limit )
		);
		return is_array( $rows ) ? $rows : array();
	}

	public static function admin_donations( $limit = 50 ) {
		global $wpdb;
		$rows = $wpdb->get_results(
			$wpdb->prepare(
				'SELECT d.*, c.title AS case_title, u.display_name
				FROM ' . self::donations_table() . ' d
				LEFT JOIN ' . self::cases_table() . ' c ON c.id = d.case_id
				LEFT JOIN ' . $wpdb->users . ' u ON u.ID = d.user_id
				ORDER BY d.id DESC LIMIT %d',
				$limit
			)
		);
		return is_array( $rows ) ? $rows : array();
	}

	public static function save_case( $data, $id = 0 ) {
		global $wpdb;
		$id   = absint( $id );
		$now  = current_time( 'mysql' );
		$row  = array(
			'title'         => isset( $data['title'] ) ? sanitize_text_field( $data['title'] ) : '',
			'summary'       => isset( $data['summary'] ) ? sanitize_textarea_field( $data['summary'] ) : '',
			'need_type'     => isset( $data['need_type'] ) ? sanitize_key( $data['need_type'] ) : 'glasses',
			'goal_amount'   => isset( $data['goal_amount'] ) ? absint( $data['goal_amount'] ) : 0,
			'status'        => isset( $data['status'] ) ? sanitize_key( $data['status'] ) : 'open',
			'is_public'     => ! empty( $data['is_public'] ) ? 1 : 0,
			'updated_at'    => $now,
		);
		if ( $row['title'] === '' ) {
			return new WP_Error( 'title', 'عنوان لازم است' );
		}
		if ( $id ) {
			$wpdb->update( self::cases_table(), $row, array( 'id' => $id ) );
			return $id;
		}
		$row['raised_amount'] = 0;
		$row['created_at']    = $now;
		$wpdb->insert( self::cases_table(), $row );
		return (int) $wpdb->insert_id;
	}

	/**
	 * Record how a donation was used + thank the donor.
	 *
	 * @return int|WP_Error
	 */
	public static function add_update( $data ) {
		global $wpdb;
		$donation_id = isset( $data['donation_id'] ) ? absint( $data['donation_id'] ) : 0;
		$donation    = $donation_id ? $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . self::donations_table() . ' WHERE id = %d', $donation_id ) ) : null;
		if ( ! $donation ) {
			return new WP_Error( 'donation', 'کمک یافت نشد' );
		}
		$now = current_time( 'mysql' );
		$ok  = $wpdb->insert(
			self::updates_table(),
			array(
				'donation_id'       => $donation_id,
				'case_id'           => (int) $donation->case_id,
				'spent_amount'      => isset( $data['spent_amount'] ) ? absint( $data['spent_amount'] ) : (int) $donation->amount,
				'beneficiary_label' => isset( $data['beneficiary_label'] ) ? sanitize_text_field( $data['beneficiary_label'] ) : '',
				'purpose'           => isset( $data['purpose'] ) ? sanitize_textarea_field( $data['purpose'] ) : '',
				'thank_you'         => isset( $data['thank_you'] ) ? sanitize_textarea_field( $data['thank_you'] ) : '',
				'attachment_id'     => isset( $data['attachment_id'] ) ? absint( $data['attachment_id'] ) : 0,
				'admin_id'          => get_current_user_id(),
				'created_at'        => $now,
			)
		);
		if ( ! $ok ) {
			return new WP_Error( 'db', 'ثبت گزارش ممکن نشد' );
		}
		$update_id = (int) $wpdb->insert_id;

		// Notify donor (optional soft message)
		if ( $donation->user_id && ! empty( $data['thank_you'] ) && class_exists( 'EzLens_CD_Messaging_Bridge' ) ) {
			$user = get_userdata( (int) $donation->user_id );
			if ( $user && $user->user_email ) {
				EzLens_CD_Messaging_Bridge::send_email(
					$user->user_email,
					'گزارش شفاف هم‌یاری بینایی',
					$data['thank_you']
				);
			}
		}

		do_action( 'ezcd_charity_update_added', $update_id, $donation_id );
		return $update_id;
	}

	public static function stats() {
		global $wpdb;
		return array(
			'donations' => (int) $wpdb->get_var( 'SELECT COUNT(*) FROM ' . self::donations_table() . " WHERE status='confirmed'" ),
			'raised'    => (int) $wpdb->get_var( 'SELECT COALESCE(SUM(amount),0) FROM ' . self::donations_table() . " WHERE status='confirmed'" ),
			'open'      => (int) $wpdb->get_var( 'SELECT COUNT(*) FROM ' . self::cases_table() . " WHERE status='open'" ),
			'funded'    => (int) $wpdb->get_var( 'SELECT COUNT(*) FROM ' . self::cases_table() . " WHERE status='funded'" ),
		);
	}
}
