<?php
/**
 * Patient prescriptions (Phase 3)
 *
 * @package EzLens_Secure_Login
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class EzLens_CD_Prescriptions {

	public static function table() {
		global $wpdb;
		return $wpdb->prefix . 'ezlens_cd_prescriptions';
	}

	public static function maybe_create_table() {
		global $wpdb;
		$table   = self::table();
		$charset = $wpdb->get_charset_collate();
		$sql     = "CREATE TABLE {$table} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			user_id bigint(20) unsigned NOT NULL,
			doctor_name varchar(191) NOT NULL DEFAULT '',
			issued_at date DEFAULT NULL,
			visited_at date DEFAULT NULL,
			rx_type varchar(32) NOT NULL DEFAULT 'glasses',
			od_sph varchar(16) NOT NULL DEFAULT '',
			od_cyl varchar(16) NOT NULL DEFAULT '',
			od_axis varchar(16) NOT NULL DEFAULT '',
			os_sph varchar(16) NOT NULL DEFAULT '',
			os_cyl varchar(16) NOT NULL DEFAULT '',
			os_axis varchar(16) NOT NULL DEFAULT '',
			pd varchar(16) NOT NULL DEFAULT '',
			notes text NULL,
			attachment_id bigint(20) unsigned NOT NULL DEFAULT 0,
			created_at datetime NOT NULL,
			updated_at datetime NOT NULL,
			PRIMARY KEY  (id),
			KEY user_id (user_id),
			KEY issued_at (issued_at)
		) {$charset};";
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );
	}

	/**
	 * @return object[]
	 */
	public static function list_for_user( $user_id = 0 ) {
		global $wpdb;
		$user_id = $user_id ? absint( $user_id ) : get_current_user_id();
		if ( ! $user_id ) {
			return array();
		}
		$table = self::table();
		// phpcs:ignore WordPress.DB.PreparedSQL
		$rows = $wpdb->get_results( $wpdb->prepare(
			"SELECT * FROM {$table} WHERE user_id = %d ORDER BY issued_at DESC, id DESC",
			$user_id
		) );
		return is_array( $rows ) ? $rows : array();
	}

	public static function get( $id, $user_id = 0 ) {
		global $wpdb;
		$user_id = $user_id ? absint( $user_id ) : get_current_user_id();
		$row     = $wpdb->get_row( $wpdb->prepare(
			'SELECT * FROM ' . self::table() . ' WHERE id = %d AND user_id = %d',
			absint( $id ),
			$user_id
		) );
		return $row;
	}

	public static function is_expired( $row ) {
		if ( empty( $row->issued_at ) ) {
			return false;
		}
		$issued = strtotime( $row->issued_at );
		if ( ! $issued ) {
			return false;
		}
		return ( time() - $issued ) > ( 365 * DAY_IN_SECONDS );
	}

	/**
	 * @param array $data
	 * @return int|WP_Error new id
	 */
	public static function save( $data, $user_id = 0 ) {
		global $wpdb;
		$user_id = $user_id ? absint( $user_id ) : get_current_user_id();
		if ( ! $user_id ) {
			return new WP_Error( 'auth', 'وارد شوید' );
		}

		$id = isset( $data['id'] ) ? absint( $data['id'] ) : 0;
		$row = array(
			'user_id'       => $user_id,
			'doctor_name'   => isset( $data['doctor_name'] ) ? sanitize_text_field( $data['doctor_name'] ) : '',
			'issued_at'     => isset( $data['issued_at'] ) ? sanitize_text_field( $data['issued_at'] ) : null,
			'visited_at'    => isset( $data['visited_at'] ) ? sanitize_text_field( $data['visited_at'] ) : null,
			'rx_type'       => isset( $data['rx_type'] ) ? sanitize_key( $data['rx_type'] ) : 'glasses',
			'od_sph'        => isset( $data['od_sph'] ) ? sanitize_text_field( $data['od_sph'] ) : '',
			'od_cyl'        => isset( $data['od_cyl'] ) ? sanitize_text_field( $data['od_cyl'] ) : '',
			'od_axis'       => isset( $data['od_axis'] ) ? sanitize_text_field( $data['od_axis'] ) : '',
			'os_sph'        => isset( $data['os_sph'] ) ? sanitize_text_field( $data['os_sph'] ) : '',
			'os_cyl'        => isset( $data['os_cyl'] ) ? sanitize_text_field( $data['os_cyl'] ) : '',
			'os_axis'       => isset( $data['os_axis'] ) ? sanitize_text_field( $data['os_axis'] ) : '',
			'pd'            => isset( $data['pd'] ) ? sanitize_text_field( $data['pd'] ) : '',
			'notes'         => isset( $data['notes'] ) ? sanitize_textarea_field( $data['notes'] ) : '',
			'attachment_id' => isset( $data['attachment_id'] ) ? absint( $data['attachment_id'] ) : 0,
			'updated_at'    => current_time( 'mysql' ),
		);

		$allowed_types = array( 'glasses', 'contact', 'scleral', 'rgp', 'other' );
		if ( ! in_array( $row['rx_type'], $allowed_types, true ) ) {
			$row['rx_type'] = 'glasses';
		}
		if ( $row['issued_at'] && ! preg_match( '/^\d{4}-\d{2}-\d{2}$/', $row['issued_at'] ) ) {
			$row['issued_at'] = null;
		}
		if ( $row['visited_at'] && ! preg_match( '/^\d{4}-\d{2}-\d{2}$/', $row['visited_at'] ) ) {
			$row['visited_at'] = null;
		}

		$table = self::table();
		if ( $id ) {
			$exists = self::get( $id, $user_id );
			if ( ! $exists ) {
				return new WP_Error( 'not_found', 'نسخه یافت نشد' );
			}
			$wpdb->update( $table, $row, array( 'id' => $id, 'user_id' => $user_id ) );
			return $id;
		}

		$row['created_at'] = current_time( 'mysql' );
		$wpdb->insert( $table, $row );
		$new_id = (int) $wpdb->insert_id;
		if ( ! $new_id ) {
			return new WP_Error( 'db', 'ذخیره نسخه ممکن نشد' );
		}
		return $new_id;
	}

	public static function delete( $id, $user_id = 0 ) {
		global $wpdb;
		$user_id = $user_id ? absint( $user_id ) : get_current_user_id();
		$exists  = self::get( $id, $user_id );
		if ( ! $exists ) {
			return new WP_Error( 'not_found', 'نسخه یافت نشد' );
		}
		$wpdb->delete( self::table(), array( 'id' => absint( $id ), 'user_id' => $user_id ) );
		return true;
	}

	public static function type_label( $type ) {
		$labels = array(
			'glasses' => 'عینک',
			'contact' => 'لنز تماسی',
			'scleral' => 'اسکلرال',
			'rgp'     => 'RGP',
			'other'   => 'سایر',
		);
		return isset( $labels[ $type ] ) ? $labels[ $type ] : $type;
	}

	/** Patient profile (meta) */
	public static function get_profile( $user_id = 0 ) {
		$user_id = $user_id ? absint( $user_id ) : get_current_user_id();
		$raw     = get_user_meta( $user_id, '_ezcd_patient_profile', true );
		if ( ! is_array( $raw ) ) {
			$raw = array();
		}
		return wp_parse_args(
			$raw,
			array(
				'gender'      => '',
				'birth_year'  => '',
				'conditions'  => array(),
				'allergies'   => '',
				'family_note' => '',
			)
		);
	}

	public static function save_profile( $data, $user_id = 0 ) {
		$user_id = $user_id ? absint( $user_id ) : get_current_user_id();
		if ( ! $user_id ) {
			return new WP_Error( 'auth', 'وارد شوید' );
		}
		$conditions = array();
		if ( ! empty( $data['conditions'] ) && is_array( $data['conditions'] ) ) {
			$conditions = array_map( 'sanitize_text_field', $data['conditions'] );
		}
		$profile = array(
			'gender'      => isset( $data['gender'] ) ? sanitize_text_field( $data['gender'] ) : '',
			'birth_year'  => isset( $data['birth_year'] ) ? sanitize_text_field( $data['birth_year'] ) : '',
			'conditions'  => $conditions,
			'allergies'   => isset( $data['allergies'] ) ? sanitize_textarea_field( $data['allergies'] ) : '',
			'family_note' => isset( $data['family_note'] ) ? sanitize_textarea_field( $data['family_note'] ) : '',
		);
		update_user_meta( $user_id, '_ezcd_patient_profile', $profile );
		return true;
	}
}
