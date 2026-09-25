<?php
/**
 * Force-apply article content + CORS + sanitize full HTML docs + skip wpautop.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class EzLens_Manager_Posts_REST {

	const NS = 'ezlens/v1';

	public static function init() {
		add_action( 'rest_api_init', array( __CLASS__, 'register_routes' ), 20 );
		add_action( 'rest_api_init', array( __CLASS__, 'register_elementor_meta' ), 15 );
		add_action( 'rest_api_init', array( __CLASS__, 'add_cors_headers' ), 5 );
		add_filter( 'rest_pre_serve_request', array( __CLASS__, 'serve_cors' ), 15, 4 );
		// Front: do not let wpautop break <style>/<script>
		add_action( 'wp', array( __CLASS__, 'maybe_disable_wpautop' ), 1 );
	}

	public static function maybe_disable_wpautop() {
		if ( ! is_singular( 'post' ) ) {
			return;
		}
		$post = get_post();
		if ( ! $post ) {
			return;
		}
		$c = (string) $post->post_content;
		if ( false !== strpos( $c, 'ezlens-article-css' )
			|| false !== strpos( $c, 'ezlens-article-js' )
			|| false !== strpos( $c, 'class="ez-article"' )
			|| false !== strpos( $c, "class='ez-article'" )
			|| false !== strpos( $c, 'EZLENS_CODE' ) ) {
			remove_filter( 'the_content', 'wpautop' );
			remove_filter( 'the_content', 'shortcode_unautop' );
			// Keep scripts intact
			remove_filter( 'the_content', 'wptexturize' );
		}
	}

	public static function can_manage() {
		return is_user_logged_in()
			&& (
				current_user_can( 'edit_posts' )
				|| current_user_can( 'manage_options' )
				|| current_user_can( 'edit_others_posts' )
			);
	}

	public static function add_cors_headers() {
		remove_filter( 'rest_pre_serve_request', 'rest_send_cors_headers' );
		add_filter( 'rest_pre_serve_request', array( __CLASS__, 'send_cors_headers' ), 11 );
	}

	public static function send_cors_headers( $value ) {
		$origin = isset( $_SERVER['HTTP_ORIGIN'] ) ? (string) $_SERVER['HTTP_ORIGIN'] : '';
		$allowed = false;
		if ( $origin !== '' ) {
			if ( preg_match( '#^https?://(localhost|127\.0\.0\.1)(:\d+)?$#', $origin ) ) {
				$allowed = true;
			}
			$host = wp_parse_url( home_url(), PHP_URL_HOST );
			$oh   = wp_parse_url( $origin, PHP_URL_HOST );
			if ( $host && $oh && strcasecmp( $host, $oh ) === 0 ) {
				$allowed = true;
			}
		}
		if ( $allowed ) {
			header( 'Access-Control-Allow-Origin: ' . $origin );
			header( 'Access-Control-Allow-Credentials: true' );
			header( 'Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS' );
			header( 'Access-Control-Allow-Headers: Authorization, Content-Type, X-WP-Nonce, X-Requested-With, Accept' );
			header( 'Access-Control-Expose-Headers: X-WP-Total, X-WP-TotalPages, Link' );
			header( 'Vary: Origin' );
		} elseif ( $origin === '' || strpos( $origin, 'localhost' ) !== false || strpos( $origin, '127.0.0.1' ) !== false ) {
			header( 'Access-Control-Allow-Origin: *' );
			header( 'Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS' );
			header( 'Access-Control-Allow-Headers: Authorization, Content-Type, X-WP-Nonce, X-Requested-With, Accept' );
		}
		return $value;
	}

	public static function serve_cors( $served, $result, $request, $server ) {
		self::send_cors_headers( null );
		if ( 'OPTIONS' === $request->get_method() ) {
			status_header( 200 );
			exit;
		}
		return $served;
	}

	public static function register_elementor_meta() {
		$keys = array(
			'_elementor_edit_mode'     => 'string',
			'_elementor_data'          => 'string',
			'_elementor_template_type' => 'string',
			'_elementor_version'       => 'string',
			'_elementor_page_settings' => 'string',
		);
		foreach ( $keys as $key => $type ) {
			register_post_meta(
				'post',
				$key,
				array(
					'show_in_rest'  => true,
					'single'        => true,
					'type'          => $type,
					'auth_callback' => function () {
						return current_user_can( 'edit_posts' );
					},
				)
			);
		}
	}

	public static function register_routes() {
		register_rest_route(
			self::NS,
			'/manager/posts/(?P<id>\d+)/apply-content',
			array(
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( __CLASS__, 'apply_content' ),
					'permission_callback' => array( __CLASS__, 'can_manage' ),
				),
				array(
					'methods'             => WP_REST_Server::EDITABLE,
					'callback'            => array( __CLASS__, 'apply_content' ),
					'permission_callback' => array( __CLASS__, 'can_manage' ),
				),
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => function () {
						return array( 'ok' => true, 'endpoint' => 'apply-content' );
					},
					'permission_callback' => '__return_true',
				),
				array(
					'methods'             => 'OPTIONS',
					'callback'            => function () {
						return new WP_REST_Response( null, 200 );
					},
					'permission_callback' => '__return_true',
				),
			)
		);
	}

	/**
	 * Peel full HTML documents into body fragment + style + script.
	 */
	public static function sanitize_html_for_wp( $raw ) {
		$s = trim( (string) $raw );
		if ( $s === '' ) {
			return $s;
		}

		$styles  = array();
		$scripts = array();
		$json_ld = array();

		if ( preg_match_all( '#<style[^>]*>([\s\S]*?)</style>#i', $s, $m ) ) {
			foreach ( $m[1] as $body ) {
				$body = trim( $body );
				if ( $body !== '' ) {
					$styles[] = $body;
				}
			}
		}
		if ( preg_match_all( '#<script[^>]*type\s*=\s*["\']application/ld\+json["\'][^>]*>([\s\S]*?)</script>#i', $s, $m ) ) {
			foreach ( $m[0] as $full ) {
				$json_ld[] = $full;
			}
		}
		if ( preg_match_all( '#<script(?![^>]*application/ld\+json)[^>]*>([\s\S]*?)</script>#i', $s, $m ) ) {
			foreach ( $m[1] as $body ) {
				$body = trim( $body );
				if ( $body !== '' ) {
					$scripts[] = $body;
				}
			}
		}

		$html = $s;
		if ( preg_match( '#<body[^>]*>([\s\S]*?)</body>#i', $s, $bm ) ) {
			$html = $bm[1];
		} elseif ( preg_match( '#<!DOCTYPE\s+html|<html[\s>]#i', $s ) ) {
			$html = preg_replace( '#<head[^>]*>[\s\S]*?</head>#i', '', $html );
			$html = preg_replace( '#<!DOCTYPE[^>]*>#i', '', $html );
			$html = preg_replace( '#</?html[^>]*>#i', '', $html );
			$html = preg_replace( '#</?body[^>]*>#i', '', $html );
		}

		$html = preg_replace( '#<style[^>]*>[\s\S]*?</style>#i', '', $html );
		$html = preg_replace( '#<script[\s\S]*?</script>#i', '', $html );
		$html = preg_replace( '#<!DOCTYPE[^>]*>#i', '', $html );
		$html = preg_replace( '#</?html[^>]*>#i', '', $html );
		$html = preg_replace( '#<head[^>]*>[\s\S]*?</head>#i', '', $html );
		$html = trim( $html );

		$out = $html;
		foreach ( $json_ld as $j ) {
			$out .= "\n" . $j;
		}
		if ( ! empty( $styles ) ) {
			$out .= "\n<style type=\"text/css\" id=\"ezlens-article-css\">\n";
			$out .= implode( "\n\n", $styles );
			$out .= "\n</style>";
		}
		if ( ! empty( $scripts ) ) {
			$out .= "\n<script type=\"text/javascript\" id=\"ezlens-article-js\">\n";
			$js = implode( "\n\n", $scripts );
			$js = str_replace( '</script>', '<\/script>', $js );
			$out .= $js;
			$out .= "\n</script>";
		}
		return trim( $out );
	}

	private static function elementor_meta_keys() {
		return array(
			'_elementor_data',
			'_elementor_edit_mode',
			'_elementor_template_type',
			'_elementor_version',
			'_elementor_pro_version',
			'_elementor_page_settings',
			'_elementor_controls_usage',
			'_elementor_css',
			'_elementor_page_assets',
			'_elementor_conditions',
		);
	}

	private static function clear_elementor( $post_id ) {
		foreach ( self::elementor_meta_keys() as $key ) {
			delete_post_meta( $post_id, $key );
		}
		update_post_meta( $post_id, '_elementor_edit_mode', '' );
		$upload = wp_upload_dir();
		$css    = trailingslashit( $upload['basedir'] ) . 'elementor/css/post-' . (int) $post_id . '.css';
		if ( file_exists( $css ) ) {
			@unlink( $css );
		}
	}

	private static function flush_caches( $post_id ) {
		clean_post_cache( $post_id );
		if ( class_exists( 'LiteSpeed\Purge' ) && method_exists( 'LiteSpeed\Purge', 'purge_post' ) ) {
			try {
				\LiteSpeed\Purge::purge_post( $post_id );
			} catch ( \Throwable $e ) { // phpcs:ignore
			}
		}
		do_action( 'litespeed_purge_post', $post_id );
		$link = get_permalink( $post_id );
		if ( $link ) {
			do_action( 'litespeed_purge_url', $link );
		}
	}

	public static function apply_content( WP_REST_Request $request ) {
		try {
			$post_id = (int) $request['id'];
			$post    = get_post( $post_id );
			if ( ! $post || 'post' !== $post->post_type ) {
				return new WP_Error( 'not_found', 'مقاله یافت نشد', array( 'status' => 404 ) );
			}

			$content = $request->get_param( 'content' );
			if ( ! is_string( $content ) ) {
				$content = '';
			}
			$content = self::sanitize_html_for_wp( $content );

			$allow_empty = (bool) $request->get_param( 'allow_empty' );
			if ( '' === trim( $content ) && ! $allow_empty ) {
				return new WP_Error( 'empty_content', 'محتوا خالی است', array( 'status' => 400 ) );
			}

			$update = array(
				'ID'           => $post_id,
				'post_content' => $content,
			);

			$title = $request->get_param( 'title' );
			if ( is_string( $title ) && '' !== $title ) {
				$update['post_title'] = sanitize_text_field( $title );
			}
			$excerpt = $request->get_param( 'excerpt' );
			if ( is_string( $excerpt ) ) {
				$update['post_excerpt'] = wp_kses_post( $excerpt );
			}
			$status = $request->get_param( 'status' );
			if ( is_string( $status ) && in_array( $status, array( 'draft', 'publish', 'pending', 'private', 'future' ), true ) ) {
				$update['post_status'] = $status;
			}
			$slug = $request->get_param( 'slug' );
			if ( is_string( $slug ) && '' !== $slug ) {
				$update['post_name'] = sanitize_title( $slug );
			}

			// Keep raw HTML/JS for capable users
			$result = wp_update_post( wp_slash( $update ), true );
			if ( is_wp_error( $result ) ) {
				return $result;
			}

			$disable = $request->get_param( 'disable_elementor' );
			if ( null === $disable || $disable ) {
				self::clear_elementor( $post_id );
			}

			$meta = $request->get_param( 'meta' );
			if ( is_array( $meta ) ) {
				foreach ( $meta as $k => $v ) {
					$key = is_string( $k ) ? $k : '';
					if ( 0 === strpos( $key, 'rank_math_' ) ) {
						update_post_meta( $post_id, $key, sanitize_text_field( (string) $v ) );
					}
				}
			}

			$cats = $request->get_param( 'categories' );
			if ( is_array( $cats ) ) {
				wp_set_post_categories( $post_id, array_map( 'intval', $cats ) );
			}

			self::flush_caches( $post_id );

			$fresh = get_post( $post_id );
			return rest_ensure_response(
				array(
					'ok'                => true,
					'id'                => $post_id,
					'content_length'    => strlen( (string) $fresh->post_content ),
					'elementor_cleared' => ( null === $disable || $disable ),
					'sanitized'         => true,
					'link'              => get_permalink( $post_id ),
					'modified'          => $fresh->post_modified,
					'status'            => $fresh->post_status,
					'message'           => 'محتوا روی سایت اعمال شد (HTML خالص بدون doctype)',
				)
			);
		} catch ( \Throwable $e ) {
			return new WP_Error( 'server_error', $e->getMessage(), array( 'status' => 500 ) );
		}
	}
}

EzLens_Manager_Posts_REST::init();
