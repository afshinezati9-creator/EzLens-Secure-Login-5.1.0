<?php
/**
 * Wallet configuration bridge.
 *
 * Centralizes wallet payment-method visibility and bank-account data so the
 * customer dashboard and EzLens Manager use the same source of truth.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class EzLens_CD_Wallet_Settings {

	public static function enabled_methods() {
		$out = array();
		if ( class_exists( 'EzLens_Auth_Settings' ) ) {
			if ( '1' === (string) EzLens_Auth_Settings::get( 'wallet_payment_online_enabled' ) ) {
				$out['online'] = 'درگاه پرداخت';
			}
			if ( '1' === (string) EzLens_Auth_Settings::get( 'wallet_payment_card_enabled' ) ) {
				$out['card'] = 'کارت به کارت / فیش';
			}
			if ( '1' === (string) EzLens_Auth_Settings::get( 'wallet_payment_bank_enabled' ) ) {
				$out['bank'] = 'اینترنت‌بانک';
			}
		}
		return $out;
	}

	public static function bank_account() {
		$s = class_exists( 'EzLens_Auth_Settings' ) ? EzLens_Auth_Settings::get_all() : array();
		return array(
			'bank_name'     => isset( $s['wallet_bank_name'] ) ? (string) $s['wallet_bank_name'] : '',
			'owner'         => isset( $s['wallet_account_owner'] ) ? (string) $s['wallet_account_owner'] : '',
			'account_name'  => isset( $s['wallet_account_name'] ) ? (string) $s['wallet_account_name'] : '',
			'card_number'   => isset( $s['wallet_card_number'] ) ? (string) $s['wallet_card_number'] : '',
			'account_number'=> isset( $s['wallet_account_number'] ) ? (string) $s['wallet_account_number'] : '',
			'iban'          => isset( $s['wallet_iban'] ) ? (string) $s['wallet_iban'] : '',
			'note'          => isset( $s['wallet_account_note'] ) ? (string) $s['wallet_account_note'] : '',
		);
	}

	public static function init() {
		add_filter(
			'ezcd_wallet_deposit_methods',
			array( __CLASS__, 'filter_methods' ),
			20
		);
	}

	public static function filter_methods( $methods ) {
		$enabled = self::enabled_methods();
		$allowed = array();
		foreach ( $enabled as $key => $label ) {
			if ( isset( $methods[ $key ] ) ) {
				$allowed[ $key ] = $methods[ $key ];
			}
		}
		return $allowed;
	}
}

EzLens_CD_Wallet_Settings::init();
