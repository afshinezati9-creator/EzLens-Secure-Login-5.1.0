<?php
/**
 * Manager Stats REST — store KPIs with period filters.
 *
 * Periods: day | week | month | all
 * Product views use meta post_views_count (lifetime; snippet has no daily series).
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class EzLens_Manager_Stats_REST {

	const NS = 'ezlens/v1';

	public static function init() {
		add_action( 'rest_api_init', array( __CLASS__, 'register_routes' ) );
	}

	public static function can_manage() {
		return current_user_can( 'manage_options' )
			|| current_user_can( 'manage_woocommerce' )
			|| current_user_can( 'view_woocommerce_reports' );
	}

	public static function register_routes() {
		register_rest_route( self::NS, '/manager/stats', array(
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => array( __CLASS__, 'get_stats' ),
			'permission_callback' => array( __CLASS__, 'can_manage' ),
		) );
		register_rest_route( self::NS, '/manager/stats/top-products', array(
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => array( __CLASS__, 'top_products' ),
			'permission_callback' => array( __CLASS__, 'can_manage' ),
		) );
	}

	private static function period_bounds( $period ) {
		$period = sanitize_key( $period );
		$tz     = wp_timezone();
		$now    = new DateTimeImmutable( 'now', $tz );

		switch ( $period ) {
			case 'day':
				$start = $now->setTime( 0, 0, 0 );
				break;
			case 'week':
				$start = $now->modify( '-6 days' )->setTime( 0, 0, 0 );
				break;
			case 'month':
				$start = $now->modify( '-29 days' )->setTime( 0, 0, 0 );
				break;
			case 'all':
			default:
				return array(
					'period'     => 'all',
					'start'      => null,
					'end'        => null,
					'start_mysql'=> null,
					'end_mysql'  => null,
					'label'      => 'کل',
				);
		}

		$end = $now->setTime( 23, 59, 59 );
		$labels = array(
			'day'   => 'امروز',
			'week'  => '۷ روز',
			'month' => '۳۰ روز',
		);

		return array(
			'period'      => $period,
			'start'       => $start,
			'end'         => $end,
			'start_mysql' => $start->format( 'Y-m-d H:i:s' ),
			'end_mysql'   => $end->format( 'Y-m-d H:i:s' ),
			'label'       => isset( $labels[ $period ] ) ? $labels[ $period ] : $period,
		);
	}

	private static function to_jalali_range( $bounds ) {
		if ( empty( $bounds['start_mysql'] ) ) {
			return 'از ابتدا';
		}
		$fmt = function ( $mysql ) {
			$ts = strtotime( $mysql );
			if ( class_exists( 'EzLens_Inbox' ) && method_exists( 'EzLens_Inbox', 'to_jalali' ) ) {
				return EzLens_Inbox::to_jalali( $ts );
			}
			return date_i18n( 'Y/m/d', $ts );
		};
		return $fmt( $bounds['start_mysql'] ) . ' — ' . $fmt( $bounds['end_mysql'] );
	}

	public static function get_stats( WP_REST_Request $request ) {
		$period = sanitize_key( $request->get_param( 'period' ) ?: 'week' );
		if ( ! in_array( $period, array( 'day', 'week', 'month', 'all' ), true ) ) {
			$period = 'week';
		}
		$bounds = self::period_bounds( $period );

		$orders   = self::order_stats( $bounds );
		$users    = self::user_stats( $bounds );
		$content  = self::content_stats( $bounds );
		$products = self::product_stats();
		$views    = self::views_stats();

		return rest_ensure_response(
			array(
				'ok'           => true,
				'period'       => $period,
				'period_label' => $bounds['label'],
				'range_fa'     => self::to_jalali_range( $bounds ),
				'orders'       => $orders,
				'users'        => $users,
				'content'      => $content,
				'products'     => $products,
				'views'        => $views,
				'notes'        => array(
					'product_views' => 'بازدید محصول از meta post_views_count است و فعلاً تجمعی (کل دوره) محاسبه می‌شود؛ فروش و سفارش‌ها بر اساس بازه فیلتر می‌شوند.',
					'ga4'           => 'برای بازدید کل سایت از GA4، Service Account یا اشتراک Site Kit لازم است. Property: 551454196',
				),
			)
		);
	}

	private static function order_stats( $bounds ) {
		if ( ! function_exists( 'wc_get_orders' ) ) {
			return array(
				'count'   => 0,
				'revenue' => 0,
				'avg'     => 0,
				'by_status' => array(),
			);
		}

		$statuses = array_keys( wc_get_order_statuses() );
		$args     = array(
			'limit'  => -1,
			'return' => 'ids',
			'status' => $statuses,
		);
		if ( ! empty( $bounds['start_mysql'] ) ) {
			$args['date_created'] = $bounds['start_mysql'] . '...' . $bounds['end_mysql'];
		}

		$ids = wc_get_orders( $args );
		$count = count( $ids );
		$revenue = 0.0;
		$by_status = array();

		foreach ( $ids as $oid ) {
			$order = wc_get_order( $oid );
			if ( ! $order ) {
				continue;
			}
			$st = $order->get_status();
			if ( ! isset( $by_status[ $st ] ) ) {
				$by_status[ $st ] = 0;
			}
			$by_status[ $st ]++;

			// Count paid-like statuses toward revenue
			if ( in_array( $st, array( 'completed', 'processing', 'on-hold' ), true ) ) {
				$revenue += (float) $order->get_total();
			}
		}

		$status_items = array();
		foreach ( $by_status as $st => $n ) {
			$status_items[] = array(
				'status' => $st,
				'label'  => wc_get_order_status_name( $st ),
				'count'  => (int) $n,
			);
		}

		return array(
			'count'     => $count,
			'revenue'   => (int) round( $revenue ),
			'avg'       => $count > 0 ? (int) round( $revenue / max( 1, $count ) ) : 0,
			'by_status' => $status_items,
		);
	}

	private static function user_stats( $bounds ) {
		$q = new WP_User_Query(
			array(
				'role__in' => array( 'customer', 'subscriber' ),
				'fields'   => 'ID',
				'number'   => 1,
				'count_total' => true,
			)
		);
		$total_customers = (int) $q->get_total();

		$new = 0;
		if ( ! empty( $bounds['start_mysql'] ) ) {
			$nq = new WP_User_Query(
				array(
					'role__in'    => array( 'customer', 'subscriber' ),
					'date_query'  => array(
						array(
							'after'     => $bounds['start_mysql'],
							'before'    => $bounds['end_mysql'],
							'inclusive' => true,
						),
					),
					'fields'      => 'ID',
					'number'      => 1,
					'count_total' => true,
				)
			);
			$new = (int) $nq->get_total();
		} else {
			$new = $total_customers;
		}

		$user_count = (int) count_users()['total_users'];

		return array(
			'total_users'     => $user_count,
			'total_customers' => $total_customers,
			'new_in_period'   => $new,
		);
	}

	private static function content_stats( $bounds ) {
		$posts_total = (int) wp_count_posts( 'post' )->publish;
		$pages_total = (int) wp_count_posts( 'page' )->publish;

		$new_posts = 0;
		if ( ! empty( $bounds['start_mysql'] ) ) {
			$q = new WP_Query(
				array(
					'post_type'      => 'post',
					'post_status'    => 'publish',
					'posts_per_page' => 1,
					'fields'         => 'ids',
					'date_query'     => array(
						array(
							'after'     => $bounds['start_mysql'],
							'before'    => $bounds['end_mysql'],
							'inclusive' => true,
						),
					),
				)
			);
			$new_posts = (int) $q->found_posts;
		} else {
			$new_posts = $posts_total;
		}

		$comments = 0;
		if ( ! empty( $bounds['start_mysql'] ) ) {
			$comments = (int) get_comments(
				array(
					'count' => true,
					'status'=> 'approve',
					'date_query' => array(
						array(
							'after'     => $bounds['start_mysql'],
							'before'    => $bounds['end_mysql'],
							'inclusive' => true,
						),
					),
				)
			);
		} else {
			$comments = (int) wp_count_comments()->approved;
		}

		return array(
			'posts_total'   => $posts_total,
			'pages_total'   => $pages_total,
			'posts_new'     => $new_posts,
			'comments'      => $comments,
		);
	}

	private static function product_stats() {
		if ( ! post_type_exists( 'product' ) ) {
			return array( 'total' => 0, 'published' => 0 );
		}
		$counts = wp_count_posts( 'product' );
		return array(
			'total'     => (int) ( $counts->publish + $counts->draft + $counts->private ),
			'published' => (int) $counts->publish,
			'draft'     => (int) ( $counts->draft ?? 0 ),
		);
	}

	private static function views_stats() {
		global $wpdb;
		$sum = (int) $wpdb->get_var(
			"SELECT COALESCE(SUM(CAST(meta_value AS UNSIGNED)),0)
			 FROM {$wpdb->postmeta} pm
			 INNER JOIN {$wpdb->posts} p ON p.ID = pm.post_id
			 WHERE pm.meta_key = 'post_views_count'
			   AND p.post_type = 'product'
			   AND p.post_status = 'publish'"
		);
		return array(
			'total_product_views' => $sum,
			'scope'               => 'lifetime',
		);
	}

	public static function top_products( WP_REST_Request $request ) {
		$by    = sanitize_key( $request->get_param( 'by' ) ?: 'views' ); // views|sales
		$limit = max( 3, min( 20, (int) ( $request->get_param( 'limit' ) ?: 8 ) ) );

		global $wpdb;
		$items = array();

		if ( 'sales' === $by ) {
			$rows = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT p.ID, p.post_title, CAST(pm.meta_value AS UNSIGNED) AS score
					 FROM {$wpdb->posts} p
					 INNER JOIN {$wpdb->postmeta} pm ON pm.post_id = p.ID AND pm.meta_key = 'total_sales'
					 WHERE p.post_type = 'product' AND p.post_status = 'publish'
					 ORDER BY score DESC
					 LIMIT %d",
					$limit
				)
			);
		} else {
			$rows = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT p.ID, p.post_title, CAST(pm.meta_value AS UNSIGNED) AS score
					 FROM {$wpdb->posts} p
					 INNER JOIN {$wpdb->postmeta} pm ON pm.post_id = p.ID AND pm.meta_key = 'post_views_count'
					 WHERE p.post_type = 'product' AND p.post_status = 'publish'
					 ORDER BY score DESC
					 LIMIT %d",
					$limit
				)
			);
		}

		foreach ( (array) $rows as $r ) {
			$thumb = get_the_post_thumbnail_url( (int) $r->ID, 'thumbnail' );
			$items[] = array(
				'id'    => (int) $r->ID,
				'title' => (string) $r->post_title,
				'score' => (int) $r->score,
				'image' => $thumb ? (string) $thumb : '',
			);
		}

		return rest_ensure_response(
			array(
				'ok'    => true,
				'by'    => $by,
				'items' => $items,
			)
		);
	}
}

EzLens_Manager_Stats_REST::init();
