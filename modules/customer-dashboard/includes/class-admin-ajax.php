<?php
/**
 * Admin AJAX endpoints for Customer Dashboard CRM
 *
 * @package EzLens_Secure_Login
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class EzLens_CD_Admin_Ajax {

	/** @var self|null */
	private static $instance = null;

	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		$actions = array(
			'ezcd_admin_customers',
			'ezcd_admin_profile',
			'ezcd_admin_add_note',
			'ezcd_admin_wallet',
			'ezcd_admin_mass_send',
			'ezcd_admin_direct_message',
			'ezcd_admin_save_sections',
			'ezcd_admin_stats',
		);
		foreach ( $actions as $action ) {
			add_action( 'wp_ajax_' . $action, array( $this, 'dispatch' ) );
		}
	}

	public function dispatch() {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_send_json_error( array( 'message' => 'دسترسی غیرمجاز' ), 403 );
		}
		$action = isset( $_REQUEST['action'] ) ? sanitize_key( $_REQUEST['action'] ) : '';
		$nonce  = isset( $_REQUEST['nonce'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['nonce'] ) ) : '';
		if ( ! wp_verify_nonce( $nonce, 'ezcd_admin' ) ) {
			wp_send_json_error( array( 'message' => 'نشست نامعتبر است' ), 403 );
		}

		switch ( $action ) {
			case 'ezcd_admin_customers':
				$this->customers();
				break;
			case 'ezcd_admin_profile':
				$this->profile();
				break;
			case 'ezcd_admin_add_note':
				$this->add_note();
				break;
			case 'ezcd_admin_wallet':
				$this->wallet();
				break;
			case 'ezcd_admin_mass_send':
				$this->mass_send();
				break;
			case 'ezcd_admin_direct_message':
				$this->direct_message();
				break;
			case 'ezcd_admin_save_sections':
				$this->save_sections();
				break;
			case 'ezcd_admin_stats':
				$this->stats();
				break;
			default:
				wp_send_json_error( array( 'message' => 'اکشن نامعتبر' ) );
		}
	}

	private function customers() {
		$search = isset( $_REQUEST['s'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['s'] ) ) : '';
		$paged  = isset( $_REQUEST['paged'] ) ? max( 1, absint( $_REQUEST['paged'] ) ) : 1;
		$result = EzLens_CD_Admin_Customers::query(
			array(
				'search'   => $search,
				'paged'    => $paged,
				'per_page' => 20,
			)
		);
		$rows = array();
		foreach ( $result['items'] as $u ) {
			$st = EzLens_CD_Admin_Customers::order_stats( $u->ID );
			$w  = class_exists( 'EzLens_CD_Wallet' ) ? EzLens_CD_Wallet::balance( $u->ID ) : 0;
			$rows[] = array(
				'id'     => $u->ID,
				'name'   => $u->display_name,
				'login'  => $u->user_login,
				'email'  => $u->user_email,
				'phone'  => EzLens_CD_Admin_Customers::phone( $u->ID ),
				'orders' => $st['count'],
				'spent'  => $st['spent'],
				'wallet' => $w,
				'url'    => admin_url( 'admin.php?page=ezlens-cd-customer&user_id=' . $u->ID ),
			);
		}
		wp_send_json_success(
			array(
				'items' => $rows,
				'total' => $result['total'],
				'paged' => $paged,
				'pages' => max( 1, (int) ceil( $result['total'] / 20 ) ),
			)
		);
	}

	private function profile() {
		$user_id = isset( $_REQUEST['user_id'] ) ? absint( $_REQUEST['user_id'] ) : 0;
		$tab     = isset( $_REQUEST['tab'] ) ? sanitize_key( $_REQUEST['tab'] ) : 'overview';
		$bundle  = EzLens_CD_Admin_Customers::profile_bundle( $user_id );
		if ( ! $bundle ) {
			wp_send_json_error( array( 'message' => 'مشتری یافت نشد' ) );
		}
		// Serialize safely
		$orders = array();
		foreach ( $bundle['orders'] as $o ) {
			$orders[] = array(
				'id'     => $o->get_id(),
				'number' => $o->get_order_number(),
				'status' => wc_get_order_status_name( $o->get_status() ),
				'total'  => $o->get_formatted_order_total(),
				'date'   => $o->get_date_created() ? $o->get_date_created()->date_i18n( 'Y/m/d H:i' ) : '',
				'url'    => $o->get_edit_order_url(),
			);
		}
		$notes = array();
		foreach ( $bundle['notes'] as $n ) {
			$admin = get_userdata( (int) $n->admin_id );
			$notes[] = array(
				'id'         => (int) $n->id,
				'note'       => $n->note,
				'admin_name' => $admin ? $admin->display_name : 'سیستم',
				'created_at' => $n->created_at,
			);
		}
		wp_send_json_success(
			array(
				'tab'           => $tab,
				'user'          => array(
					'id'           => $bundle['user']->ID,
					'display_name' => $bundle['user']->display_name,
					'email'        => $bundle['user']->user_email,
					'phone'        => $bundle['phone'],
					'registered'   => $bundle['user']->user_registered,
					'roles'        => $bundle['user']->roles,
				),
				'stats'         => $bundle['stats'],
				'wallet'        => $bundle['wallet'],
				'orders'        => $orders,
				'notes'         => $notes,
				'tickets_count' => count( $bundle['tickets'] ),
				'rx_count'      => count( $bundle['prescriptions'] ),
			)
		);
	}

	private function add_note() {
		$cid  = isset( $_POST['customer_id'] ) ? absint( $_POST['customer_id'] ) : 0;
		$note = isset( $_POST['note'] ) ? wp_unslash( $_POST['note'] ) : '';
		$r    = EzLens_CD_Notes::add( $cid, $note );
		if ( is_wp_error( $r ) ) {
			wp_send_json_error( array( 'message' => $r->get_error_message() ) );
		}
		EzLens_CD_Cache::flush_user( $cid );
		wp_send_json_success( array( 'message' => 'یادداشت ذخیره شد', 'id' => $r ) );
	}

	private function wallet() {
		$cid    = isset( $_POST['customer_id'] ) ? absint( $_POST['customer_id'] ) : 0;
		$amount = isset( $_POST['amount'] ) ? absint( $_POST['amount'] ) : 0;
		$type   = isset( $_POST['entry_type'] ) && 'debit' === $_POST['entry_type'] ? 'debit' : 'credit';
		$note   = isset( $_POST['note'] ) ? sanitize_text_field( wp_unslash( $_POST['note'] ) ) : 'تعدیل ادمین';
		if ( ! class_exists( 'EzLens_CD_Wallet' ) ) {
			wp_send_json_error( array( 'message' => 'ماژول کیف پول فعال نیست' ) );
		}
		$r = EzLens_CD_Wallet::add_entry( $cid, $amount, $type, 'admin', $note );
		if ( is_wp_error( $r ) ) {
			wp_send_json_error( array( 'message' => $r->get_error_message() ) );
		}
		EzLens_CD_Cache::flush_user( $cid );
		wp_send_json_success(
			array(
				'message' => 'موجودی به‌روز شد',
				'balance' => EzLens_CD_Wallet::balance( $cid ),
			)
		);
	}

	private function mass_send() {
		$audience = isset( $_POST['audience'] ) ? sanitize_key( $_POST['audience'] ) : 'selected';
		$channel  = isset( $_POST['channel'] ) ? sanitize_key( $_POST['channel'] ) : 'sms';
		$subject  = isset( $_POST['subject'] ) ? sanitize_text_field( wp_unslash( $_POST['subject'] ) ) : '';
		$message  = isset( $_POST['message'] ) ? sanitize_textarea_field( wp_unslash( $_POST['message'] ) ) : '';
		$ids_raw  = isset( $_POST['user_ids'] ) ? sanitize_text_field( wp_unslash( $_POST['user_ids'] ) ) : '';
		if ( $message === '' ) {
			wp_send_json_error( array( 'message' => 'متن پیام الزامی است' ) );
		}
		if ( 'selected' === $audience ) {
			$ids = array_filter( array_map( 'absint', preg_split( '/[\s,;]+/', $ids_raw ) ) );
		} else {
			$ids = EzLens_CD_Mass_Actions::audience( $audience );
		}
		if ( empty( $ids ) ) {
			wp_send_json_error( array( 'message' => 'هیچ مخاطبی انتخاب نشده' ) );
		}
		$result = EzLens_CD_Mass_Actions::send_to_users( $ids, $channel, $subject, $message );
		wp_send_json_success(
			array(
				'message' => sprintf( 'ارسال انجام شد — موفق: %d / ناموفق: %d', $result['success'], $result['fail'] ),
				'success' => $result['success'],
				'fail'    => $result['fail'],
				'errors'  => array_slice( $result['errors'], 0, 5 ),
			)
		);
	}

	private function direct_message() {
		$cid     = isset( $_POST['customer_id'] ) ? absint( $_POST['customer_id'] ) : 0;
		$channel = isset( $_POST['channel'] ) ? sanitize_key( $_POST['channel'] ) : 'sms';
		$subject = isset( $_POST['subject'] ) ? sanitize_text_field( wp_unslash( $_POST['subject'] ) ) : '';
		$message = isset( $_POST['message'] ) ? sanitize_textarea_field( wp_unslash( $_POST['message'] ) ) : '';
		if ( ! $cid || $message === '' ) {
			wp_send_json_error( array( 'message' => 'پیام نامعتبر' ) );
		}
		$result = EzLens_CD_Mass_Actions::send_to_users( array( $cid ), $channel, $subject, $message );
		if ( $result['success'] > 0 ) {
			wp_send_json_success( array( 'message' => 'پیام ارسال شد' ) );
		}
		$err = ! empty( $result['errors'][0] ) ? $result['errors'][0] : 'ارسال ناموفق';
		wp_send_json_error( array( 'message' => $err ) );
	}

	private function save_sections() {
		$all = array(
			'overview', 'orders', 'prescriptions', 'wishlist', 'addresses',
			'reviews', 'coupons', 'gift_cards', 'wallet', 'support',
			'account', 'security', 'referral',
		);
		$posted = isset( $_POST['sections'] ) && is_array( $_POST['sections'] ) ? array_map( 'sanitize_key', wp_unslash( $_POST['sections'] ) ) : array();
		$save   = array();
		foreach ( $all as $key ) {
			$save[ $key ] = in_array( $key, $posted, true ) ? 1 : 0;
		}
		$save['overview'] = 1;
		update_option( 'ezlens_cd_sections', $save, false );
		$replace = ! empty( $_POST['replace_my_account'] ) ? 1 : 0;
		update_option( 'ezlens_cd_replace_my_account', $replace, false );
		EzLens_CD_Cache::flush_admin_stats();
		wp_send_json_success( array( 'message' => 'تنظیمات ذخیره شد', 'sections' => $save ) );
	}

	private function stats() {
		$stats = EzLens_CD_Cache::remember(
			'admin_global_stats',
			function () {
				return EzLens_CD_Admin_Customers::global_stats();
			},
			120
		);
		wp_send_json_success( $stats );
	}
}
