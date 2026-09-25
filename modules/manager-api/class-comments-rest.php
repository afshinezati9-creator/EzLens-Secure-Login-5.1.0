<?php
/**
 * Manager Comments REST — WordPress comments + EzLens enrichment.
 *
 * GET    /ezlens/v1/manager/comments
 * POST   /ezlens/v1/manager/comments/{id}/status
 * DELETE /ezlens/v1/manager/comments/{id}
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class EzLens_Manager_Comments_REST {

	const NS = 'ezlens/v1';

	public static function init() {
		add_action( 'rest_api_init', array( __CLASS__, 'register_routes' ) );
	}

	public static function can_manage() {
		return current_user_can( 'moderate_comments' )
			|| current_user_can( 'manage_options' )
			|| current_user_can( 'manage_woocommerce' )
			|| current_user_can( 'edit_posts' );
	}

	public static function register_routes() {
		register_rest_route(
			self::NS,
			'/manager/comments',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( __CLASS__, 'list_comments' ),
				'permission_callback' => array( __CLASS__, 'can_manage' ),
			)
		);
		register_rest_route(
			self::NS,
			'/manager/comments/(?P<id>\d+)/status',
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( __CLASS__, 'set_status' ),
				'permission_callback' => array( __CLASS__, 'can_manage' ),
			)
		);
		register_rest_route(
			self::NS,
			'/manager/comments/(?P<id>\d+)',
			array(
				'methods'             => WP_REST_Server::DELETABLE,
				'callback'            => array( __CLASS__, 'delete_comment' ),
				'permission_callback' => array( __CLASS__, 'can_manage' ),
			)
		);
	}

	private static function phone_for_user( $user_id ) {
		if ( ! $user_id ) {
			return '';
		}
		$phone = get_user_meta( $user_id, 'billing_phone', true );
		if ( ! $phone ) {
			$phone = get_user_meta( $user_id, 'user_phone', true );
		}
		$user = get_userdata( $user_id );
		if ( ! $phone && $user && preg_match( '/^09\d{9}$/', $user->user_login ) ) {
			$phone = $user->user_login;
		}
		return (string) $phone;
	}

	private static function map_comment( $c ) {
		if ( ! $c || ! ( $c instanceof WP_Comment ) ) {
			return null;
		}
		$post = get_post( (int) $c->comment_post_ID );
		$post_type = $post ? $post->post_type : 'post';
		$type_label = 'نوشته';
		if ( 'product' === $post_type ) {
			$type_label = 'محصول';
		} elseif ( 'page' === $post_type ) {
			$type_label = 'برگه';
		}

		$avatar = get_avatar_url( $c->comment_author_email, array( 'size' => 96 ) );
		$user_id = (int) $c->user_id;
		$role = '';
		if ( $user_id ) {
			$user = get_userdata( $user_id );
			if ( $user && ! empty( $user->roles[0] ) ) {
				$role = $user->roles[0];
			}
		}

		$content = $c->comment_content;
		// strip tags for plain text field, keep original in content.rendered style
		$status = wp_get_comment_status( $c );
		// WP returns 'approved','spam','trash','unapproved' — map unapproved -> pending
		if ( 'unapproved' === $status ) {
			$status = 'pending';
		}

		return array(
			'id'              => (int) $c->comment_ID,
			'author'          => $user_id,
			'author_name'     => $c->comment_author,
			'author_email'    => $c->comment_author_email,
			'author_url'      => $c->comment_author_url,
			'author_avatar_urls' => array(
				'96' => $avatar ? $avatar : '',
			),
			'content'         => array(
				'rendered' => wpautop( $content ),
				'raw'      => $content,
			),
			'date'            => $c->comment_date,
			'date_gmt'        => $c->comment_date_gmt,
			'status'          => $status,
			'link'            => get_comment_link( $c ),
			'post'            => (int) $c->comment_post_ID,
			'post_title'      => $post ? get_the_title( $post ) : '',
			'post_link'       => $post ? get_permalink( $post ) : '',
			'post_type'       => $post_type,
			'post_type_label' => $type_label,
			'parent'          => (int) $c->comment_parent,
			'author_ip'       => $c->comment_author_IP,
			'author_user_agent' => $c->comment_agent,
			'author_role'     => $role,
			'author_phone'    => self::phone_for_user( $user_id ),
			'meta'            => array(),
		);
	}

	public static function list_comments( WP_REST_Request $request ) {
		$page   = max( 1, (int) $request->get_param( 'page' ) );
		$per    = max( 5, min( 50, (int) ( $request->get_param( 'per_page' ) ?: 20 ) ) );
		$status = sanitize_key( $request->get_param( 'status' ) ?: 'any' );
		$search = sanitize_text_field( $request->get_param( 'search' ) ?: '' );
		$post   = absint( $request->get_param( 'post' ) );
		$author = absint( $request->get_param( 'author' ) );

		$args = array(
			'number'  => $per,
			'offset'  => ( $page - 1 ) * $per,
			'orderby' => 'comment_date_gmt',
			'order'   => 'DESC',
			'status'  => ( $status && $status !== 'any' ) ? $status : 'all',
		);
		if ( $search !== '' ) {
			$args['search'] = $search;
		}
		if ( $post ) {
			$args['post_id'] = $post;
		}
		if ( $author ) {
			$args['user_id'] = $author;
		}

		$query = new WP_Comment_Query( $args );
		$comments = $query->comments;
		// total
		$count_args = $args;
		unset( $count_args['number'], $count_args['offset'] );
		$count_args['count'] = true;
		$total = (int) ( new WP_Comment_Query( $count_args ) )->get_comments();

		$items = array();
		foreach ( (array) $comments as $c ) {
			$mapped = self::map_comment( $c );
			if ( $mapped ) {
				$items[] = $mapped;
			}
		}

		return rest_ensure_response(
			array(
				'ok'    => true,
				'items' => $items,
				'total' => $total,
				'page'  => $page,
				'pages' => max( 1, (int) ceil( $total / $per ) ),
			)
		);
	}

	public static function set_status( WP_REST_Request $request ) {
		$id     = (int) $request['id'];
		$status = sanitize_key( $request->get_param( 'status' ) );
		$allowed = array( 'approved', 'pending', 'spam', 'trash' );
		if ( ! in_array( $status, $allowed, true ) ) {
			return new WP_Error( 'bad_status', 'وضعیت نامعتبر', array( 'status' => 400 ) );
		}
		$result = wp_set_comment_status( $id, $status === 'pending' ? 'hold' : $status );
		if ( ! $result ) {
			return new WP_Error( 'failed', 'تغییر وضعیت ناموفق', array( 'status' => 500 ) );
		}
		$c = get_comment( $id );
		return rest_ensure_response(
			array(
				'ok'      => true,
				'comment' => self::map_comment( $c ),
			)
		);
	}

	public static function delete_comment( WP_REST_Request $request ) {
		$id    = (int) $request['id'];
		$force = (bool) $request->get_param( 'force' );
		$r     = wp_delete_comment( $id, $force );
		if ( ! $r ) {
			return new WP_Error( 'failed', 'حذف ناموفق', array( 'status' => 500 ) );
		}
		return rest_ensure_response( array( 'ok' => true ) );
	}
}

EzLens_Manager_Comments_REST::init();
