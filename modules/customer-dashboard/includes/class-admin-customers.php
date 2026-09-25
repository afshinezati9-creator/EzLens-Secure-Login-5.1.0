<?php
/**
 * Admin customer queries & stats
 *
 * @package EzLens_Secure_Login
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class EzLens_CD_Admin_Customers {

	/**
	 * @return array{items:WP_User[],total:int}
	 */
	public static function query( $args = array() ) {
		$defaults = array(
			'search'   => '',
			'paged'    => 1,
			'per_page' => 20,
			'role__in' => array( 'customer', 'subscriber' ),
		);
		$args     = wp_parse_args( $args, $defaults );
		$paged    = max( 1, absint( $args['paged'] ) );
		$per_page = max( 5, min( 100, absint( $args['per_page'] ) ) );

		$q = array(
			'number'  => $per_page,
			'paged'   => $paged,
			'orderby' => 'registered',
			'order'   => 'DESC',
			'fields'  => 'all',
		);

		// Include all roles that shop - also search without role filter if searching
		if ( ! empty( $args['search'] ) ) {
			$q['search']         = '*' . esc_attr( $args['search'] ) . '*';
			$q['search_columns'] = array( 'user_login', 'user_email', 'display_name', 'user_nicename' );
		} else {
			$q['role__in'] = $args['role__in'];
		}

		$query = new WP_User_Query( $q );
		$users = $query->get_results();
		$total = (int) $query->get_total();

		// Secondary phone search if needed
		if ( ! empty( $args['search'] ) && empty( $users ) ) {
			$phone_q = new WP_User_Query(
				array(
					'number'     => $per_page,
					'meta_query' => array(
						'relation' => 'OR',
						array(
							'key'     => 'billing_phone',
							'value'   => $args['search'],
							'compare' => 'LIKE',
						),
						array(
							'key'     => 'digits_phone',
							'value'   => $args['search'],
							'compare' => 'LIKE',
						),
					),
				)
			);
			$users = $phone_q->get_results();
			$total = (int) $phone_q->get_total();
		}

		return array(
			'items' => is_array( $users ) ? $users : array(),
			'total' => $total,
		);
	}

	public static function phone( $user_id ) {
		$phone = get_user_meta( $user_id, 'billing_phone', true );
		if ( ! $phone ) {
			$phone = get_user_meta( $user_id, 'digits_phone', true );
		}
		return $phone ? $phone : '—';
	}

	public static function order_stats( $user_id ) {
		if ( ! function_exists( 'wc_get_orders' ) ) {
			return array( 'count' => 0, 'spent' => 0 );
		}
		$orders = wc_get_orders(
			array(
				'customer_id' => absint( $user_id ),
				'status'      => array( 'wc-completed', 'wc-processing', 'wc-on-hold' ),
				'limit'       => -1,
				'return'      => 'ids',
			)
		);
		$spent = 0;
		foreach ( $orders as $oid ) {
			$o = wc_get_order( $oid );
			if ( $o ) {
				$spent += (float) $o->get_total();
			}
		}
		return array(
			'count' => count( $orders ),
			'spent' => $spent,
		);
	}

	public static function global_stats() {
		if ( class_exists( 'EzLens_CD_Cache' ) ) {
			$cached = EzLens_CD_Cache::get( 'admin_global_stats' );
			if ( false !== $cached ) {
				return $cached;
			}
		}
		$counts = count_users();
		$customers = 0;
		foreach ( array( 'customer', 'subscriber' ) as $role ) {
			$customers += isset( $counts['avail_roles'][ $role ] ) ? (int) $counts['avail_roles'][ $role ] : 0;
		}
		$out = array(
			'total_users' => (int) $counts['total_users'],
			'customers'   => $customers,
		);
		if ( class_exists( 'EzLens_CD_Cache' ) ) {
			EzLens_CD_Cache::set( 'admin_global_stats', $out, 120 );
		}
		return $out;
	}

	public static function profile_bundle( $user_id ) {
		$user_id = absint( $user_id );
		$user    = get_userdata( $user_id );
		if ( ! $user ) {
			return null;
		}
		$orders = array();
		if ( function_exists( 'wc_get_orders' ) ) {
			$orders = wc_get_orders(
				array(
					'customer_id' => $user_id,
					'limit'       => 15,
					'orderby'     => 'date',
					'order'       => 'DESC',
				)
			);
		}
		return array(
			'user'          => $user,
			'phone'         => self::phone( $user_id ),
			'stats'         => self::order_stats( $user_id ),
			'orders'        => $orders,
			'prescriptions' => class_exists( 'EzLens_CD_Prescriptions' ) ? EzLens_CD_Prescriptions::list_for_user( $user_id ) : array(),
			'wallet'        => class_exists( 'EzLens_CD_Wallet' ) ? EzLens_CD_Wallet::balance( $user_id ) : 0,
			'wallet_hist'   => class_exists( 'EzLens_CD_Wallet' ) ? EzLens_CD_Wallet::history( $user_id, 15 ) : array(),
			'tickets'       => class_exists( 'EzLens_CD_Support_Bridge' ) ? EzLens_CD_Support_Bridge::list_tickets( $user_id ) : array(),
			'notes'         => class_exists( 'EzLens_CD_Notes' ) ? EzLens_CD_Notes::list_for( $user_id ) : array(),
			'profile'       => class_exists( 'EzLens_CD_Prescriptions' ) ? EzLens_CD_Prescriptions::get_profile( $user_id ) : array(),
		);
	}
}
