<?php
/**
 * Admin customer notes
 *
 * @package EzLens_Secure_Login
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class EzLens_CD_Notes {

	public static function table() {
		global $wpdb;
		return $wpdb->prefix . 'ezlens_cd_customer_notes';
	}

	public static function maybe_create_table() {
		global $wpdb;
		$table   = self::table();
		$charset = $wpdb->get_charset_collate();
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta(
			"CREATE TABLE {$table} (
				id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				customer_id bigint(20) unsigned NOT NULL,
				admin_id bigint(20) unsigned NOT NULL DEFAULT 0,
				note text NOT NULL,
				created_at datetime NOT NULL,
				PRIMARY KEY (id),
				KEY customer_id (customer_id)
			) {$charset};"
		);
	}

	public static function list_for( $customer_id, $limit = 50 ) {
		global $wpdb;
		$rows = $wpdb->get_results(
			$wpdb->prepare(
				'SELECT * FROM ' . self::table() . ' WHERE customer_id = %d ORDER BY id DESC LIMIT %d',
				absint( $customer_id ),
				absint( $limit )
			)
		);
		return is_array( $rows ) ? $rows : array();
	}

	public static function add( $customer_id, $note, $admin_id = 0 ) {
		global $wpdb;
		$customer_id = absint( $customer_id );
		$admin_id    = $admin_id ? absint( $admin_id ) : get_current_user_id();
		$note        = sanitize_textarea_field( $note );
		if ( ! $customer_id || $note === '' ) {
			return new WP_Error( 'invalid', 'یادداشت نامعتبر است' );
		}
		$ok = $wpdb->insert(
			self::table(),
			array(
				'customer_id' => $customer_id,
				'admin_id'    => $admin_id,
				'note'        => $note,
				'created_at'  => current_time( 'mysql' ),
			)
		);
		return $ok ? (int) $wpdb->insert_id : new WP_Error( 'db', 'ذخیره یادداشت ممکن نشد' );
	}
}
