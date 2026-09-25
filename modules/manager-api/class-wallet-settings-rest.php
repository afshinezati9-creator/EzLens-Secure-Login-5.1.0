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
