<?php
/**
 * Manager Wallet Settings REST.
 *
 * GET /ezlens/v1/manager/wallet/settings
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class EzLens_Manager_Wallet_Settings_REST {

	const NS = 'ezlens/v1';

	public static function init() {
		add_action( 'rest_api_init', array( __CLASS__, 'register_routes' ) );
	}

	public static function can_manage() {
		return current_user_can( 'manage_options' )
			|| current_user_can( 'manage_woocommerce' )
			|| current_user_can( 'edit_users' );
	}

	public static function register_routes() {
		register_rest_route(
			self::NS,
			'/manager/wallet/settings',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( __CLASS__, 'get_settings' ),
				'permission_callback' => array( __CLASS__, 'can_manage' ),
			)
		);

		register_rest_route(
			self::NS,
			'/manager/wallet/settings',
			array(
				'methods'             => WP_REST_Server::EDITABLE,
				'callback'            => array( __CLASS__, 'save_settings' ),
				'permission_callback' => array( __CLASS__, 'can_manage' ),
			)
		);
	}

	public static function save_settings( WP_REST_Request $request ) {
		if ( ! class_exists( 'EzLens_Auth_Settings' ) ) {
			return new WP_Error( 'settings_unavailable', 'سیستم تنظیمات پلاگین در دسترس نیست', array( 'status' => 500 ) );
		}

		$params = $request->get_json_params();
		if ( ! is_array( $params ) ) {
			$params = $request->get_params();
		}

		$checkboxes = array(
			'online' => 'wallet_payment_online_enabled',
			'card'   => 'wallet_payment_card_enabled',
			'bank'   => 'wallet_payment_bank_enabled',
		);
		foreach ( $checkboxes as $input => $key ) {
			$value = ! empty( $params['methods'][ $input ] ) ? '1' : '0';
			EzLens_Auth_Settings::set( $key, $value );
		}

		$fields = array(
			'bank_name'      => 'wallet_bank_name',
			'owner'          => 'wallet_account_owner',
			'account_name'   => 'wallet_account_name',
			'card_number'    => 'wallet_card_number',
			'account_number' => 'wallet_account_number',
			'iban'           => 'wallet_iban',
			'note'           => 'wallet_account_note',
		);
		$account = isset( $params['bank_account'] ) && is_array( $params['bank_account'] ) ? $params['bank_account'] : array();
		foreach ( $fields as $input => $key ) {
			$value = isset( $account[ $input ] ) ? wp_unslash( $account[ $input ] ) : '';
			$value = ( 'note' === $input ) ? sanitize_textarea_field( $value ) : sanitize_text_field( $value );
			EzLens_Auth_Settings::set( $key, $value );
		}

		EzLens_Auth_Settings::clear_cache();
		return self::get_settings( $request );
	}

	public static function get_settings( WP_REST_Request $request ) {
		$enabled = class_exists( 'EzLens_CD_Wallet_Settings' )
			? EzLens_CD_Wallet_Settings::enabled_methods()
			: array();

		$account = class_exists( 'EzLens_CD_Wallet_Settings' )
			? EzLens_CD_Wallet_Settings::bank_account()
			: array(
				'bank_name' => '',
				'owner' => '',
				'account_name' => '',
				'card_number' => '',
				'account_number' => '',
				'iban' => '',
				'note' => '',
			);

		return rest_ensure_response(
			array(
				'ok' => true,
				'methods' => array(
					'online' => isset( $enabled['online'] ),
					'card'   => isset( $enabled['card'] ),
					'bank'   => isset( $enabled['bank'] ),
				),
				'bank_account' => $account,
			)
		);
	}
}

EzLens_Manager_Wallet_Settings_REST::init();
