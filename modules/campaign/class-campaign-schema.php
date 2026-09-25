<?php
/**
 * Schema و نسخه دیتابیس ماژول کمپین — فاز ۵
 * مسئولیت: فقط ساخت/ارتقای جداول (جدا از منطق ارسال)
 *
 * @package EzLens
 * @version 1.0.0-phase5
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class EzLens_Auth_Campaign_Schema {

	const VERSION = '1.2.0';
	const OPTION  = 'ezlens_campaign_db_version';

	/** @var string */
	public $campaigns_table;
	/** @var string */
	public $contacts_table;
	/** @var string */
	public $groups_table;
	/** @var string */
	public $campaign_groups_table;
	/** @var string */
	public $track_table;
	/** @var string */
	public $recipients_table;

	public function __construct() {
		global $wpdb;
		$p = $wpdb->prefix;
		$this->campaigns_table       = $p . 'ezlens_campaigns';
		$this->contacts_table        = $p . 'ezlens_campaign_contacts';
		$this->groups_table          = $p . 'ezlens_campaign_groups';
		$this->campaign_groups_table = $p . 'ezlens_campaign_group_relations';
		$this->track_table           = $p . 'ezlens_campaign_tracks';
		$this->recipients_table      = $p . 'ezlens_campaign_recipients';
	}

	/**
	 * در صورت نیاز schema را به VERSION می‌رساند.
	 */
	public function maybe_upgrade() {
		$current = get_option( self::OPTION, '' );
		if ( $current === self::VERSION ) {
			// حتی با نسخه برابر، recipients را برای نصب‌های خراب چک کن
			if ( ! $this->table_exists( $this->recipients_table ) ) {
				$this->install();
			}
			return;
		}
		$this->install();
		update_option( self::OPTION, self::VERSION, false );
	}

	public function install() {
		global $wpdb;
		$charset_collate = $wpdb->get_charset_collate();

		$campaigns_sql = "CREATE TABLE IF NOT EXISTS {$this->campaigns_table} (
			id bigint(20) NOT NULL AUTO_INCREMENT,
			name varchar(255) NOT NULL,
			type varchar(20) NOT NULL DEFAULT 'email',
			subject varchar(255) DEFAULT '',
			message longtext NOT NULL,
			file_attachment varchar(255) DEFAULT '',
			status varchar(20) NOT NULL DEFAULT 'draft',
			scheduled_at datetime DEFAULT NULL,
			sent_at datetime DEFAULT NULL,
			stats longtext,
			created_by bigint(20) NOT NULL,
			created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			KEY status (status),
			KEY created_at (created_at)
		) $charset_collate;";

		$contacts_sql = "CREATE TABLE IF NOT EXISTS {$this->contacts_table} (
			id bigint(20) NOT NULL AUTO_INCREMENT,
			name varchar(255) NOT NULL,
			email varchar(100) NOT NULL,
			phone varchar(20) DEFAULT '',
			category varchar(100) DEFAULT 'عمومی',
			extra_fields longtext,
			created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			KEY email (email),
			KEY phone (phone),
			KEY category (category),
			KEY created_at (created_at)
		) $charset_collate;";

		$groups_sql = "CREATE TABLE IF NOT EXISTS {$this->groups_table} (
			id bigint(20) NOT NULL AUTO_INCREMENT,
			name varchar(255) NOT NULL,
			description text,
			type varchar(20) NOT NULL DEFAULT 'custom',
			user_filters longtext,
			contact_ids longtext,
			created_by bigint(20) NOT NULL,
			created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			KEY created_by (created_by)
		) $charset_collate;";

		$relation_sql = "CREATE TABLE IF NOT EXISTS {$this->campaign_groups_table} (
			id bigint(20) NOT NULL AUTO_INCREMENT,
			campaign_id bigint(20) NOT NULL,
			group_id bigint(20) NOT NULL,
			PRIMARY KEY (id),
			KEY campaign_id (campaign_id),
			KEY group_id (group_id)
		) $charset_collate;";

		$track_sql = "CREATE TABLE IF NOT EXISTS {$this->track_table} (
			id bigint(20) NOT NULL AUTO_INCREMENT,
			campaign_id bigint(20) NOT NULL,
			recipient_email varchar(100) NOT NULL,
			opened_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
			ip varchar(45) DEFAULT '',
			user_agent text,
			PRIMARY KEY (id),
			KEY campaign_id (campaign_id),
			KEY recipient_email (recipient_email)
		) $charset_collate;";

		$recipients_sql = "CREATE TABLE IF NOT EXISTS {$this->recipients_table} (
			id bigint(20) NOT NULL AUTO_INCREMENT,
			campaign_id bigint(20) NOT NULL,
			recipient_key varchar(64) NOT NULL DEFAULT '',
			source varchar(32) NOT NULL DEFAULT 'custom',
			source_id bigint(20) NOT NULL DEFAULT 0,
			name varchar(255) NOT NULL DEFAULT '',
			email varchar(100) NOT NULL DEFAULT '',
			phone varchar(32) NOT NULL DEFAULT '',
			status varchar(20) NOT NULL DEFAULT 'pending',
			response text,
			message_id varchar(191) NOT NULL DEFAULT '',
			retry_count int(11) NOT NULL DEFAULT 0,
			sent_at datetime DEFAULT NULL,
			created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
			updated_at datetime DEFAULT NULL,
			PRIMARY KEY (id),
			KEY campaign_id (campaign_id),
			KEY status (status),
			KEY campaign_status (campaign_id, status),
			KEY email (email),
			KEY phone (phone),
			KEY recipient_key (recipient_key)
		) $charset_collate;";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $campaigns_sql );
		dbDelta( $contacts_sql );
		dbDelta( $groups_sql );
		dbDelta( $relation_sql );
		dbDelta( $track_sql );
		dbDelta( $recipients_sql );

		$this->maybe_add_category_column();
		$this->maybe_upgrade_recipients_table();
	}

	public function table_exists( $table ) {
		global $wpdb;
		return (bool) $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) );
	}

	private function maybe_add_category_column() {
		global $wpdb;
		if ( ! $this->table_exists( $this->contacts_table ) ) {
			return;
		}
		$row = $wpdb->get_results( "SHOW COLUMNS FROM {$this->contacts_table} LIKE 'category'" );
		if ( empty( $row ) ) {
			$wpdb->query( "ALTER TABLE {$this->contacts_table} ADD COLUMN category varchar(100) DEFAULT 'عمومی'" );
		}
	}

	private function maybe_upgrade_recipients_table() {
		global $wpdb;
		$table = $this->recipients_table;
		if ( ! $this->table_exists( $table ) ) {
			return;
		}
		$cols = $wpdb->get_col( "DESCRIBE {$table}", 0 );
		if ( ! is_array( $cols ) ) {
			return;
		}
		$add = array(
			'recipient_key' => "ALTER TABLE {$table} ADD COLUMN recipient_key varchar(64) NOT NULL DEFAULT '' AFTER campaign_id",
			'source'        => "ALTER TABLE {$table} ADD COLUMN source varchar(32) NOT NULL DEFAULT 'custom'",
			'source_id'     => "ALTER TABLE {$table} ADD COLUMN source_id bigint(20) NOT NULL DEFAULT 0",
			'retry_count'   => "ALTER TABLE {$table} ADD COLUMN retry_count int(11) NOT NULL DEFAULT 0",
			'message_id'    => "ALTER TABLE {$table} ADD COLUMN message_id varchar(191) NOT NULL DEFAULT ''",
			'updated_at'    => "ALTER TABLE {$table} ADD COLUMN updated_at datetime DEFAULT NULL",
			'sent_at'       => "ALTER TABLE {$table} ADD COLUMN sent_at datetime DEFAULT NULL",
			'response'      => "ALTER TABLE {$table} ADD COLUMN response text",
		);
		foreach ( $add as $col => $sql ) {
			if ( ! in_array( $col, $cols, true ) ) {
				// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
				$wpdb->query( $sql );
			}
		}
	}
}
