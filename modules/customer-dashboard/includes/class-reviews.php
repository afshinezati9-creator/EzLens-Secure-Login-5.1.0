<?php
/**
 * Customer product reviews (WooCommerce comments)
 *
 * @package EzLens_Secure_Login
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class EzLens_CD_Reviews {

	/**
	 * @return WP_Comment[]
	 */
	public static function for_user( $user_id = 0, $limit = 30 ) {
		$user_id = $user_id ? absint( $user_id ) : get_current_user_id();
		if ( ! $user_id ) {
			return array();
		}
		$comments = get_comments(
			array(
				'user_id' => $user_id,
				'type'    => 'review',
				'status'  => 'all',
				'number'  => $limit,
				'orderby' => 'comment_date_gmt',
				'order'   => 'DESC',
			)
		);
		return is_array( $comments ) ? $comments : array();
	}

	public static function status_label( $comment ) {
		$approved = (string) $comment->comment_approved;
		if ( '1' === $approved ) {
			return array( 'label' => 'تأیید شده', 'class' => 'ok' );
		}
		if ( '0' === $approved ) {
			return array( 'label' => 'در انتظار بررسی', 'class' => 'wait' );
		}
		if ( 'spam' === $approved ) {
			return array( 'label' => 'اسپم', 'class' => 'bad' );
		}
		return array( 'label' => 'رد شده', 'class' => 'bad' );
	}

	public static function rating( $comment_id ) {
		return (int) get_comment_meta( $comment_id, 'rating', true );
	}

	/**
	 * @return true|WP_Error
	 */
	public static function update( $comment_id, $content, $rating = 0, $user_id = 0 ) {
		$user_id    = $user_id ? absint( $user_id ) : get_current_user_id();
		$comment_id = absint( $comment_id );
		$comment    = get_comment( $comment_id );
		if ( ! $comment || (int) $comment->user_id !== (int) $user_id ) {
			return new WP_Error( 'forbidden', 'اجازه ویرایش این نظر را ندارید' );
		}
		$content = sanitize_textarea_field( $content );
		if ( strlen( $content ) < 2 ) {
			return new WP_Error( 'empty', 'متن نظر خیلی کوتاه است' );
		}
		$r = wp_update_comment(
			array(
				'comment_ID'      => $comment_id,
				'comment_content' => $content,
			)
		);
		if ( false === $r ) {
			return new WP_Error( 'db', 'ذخیره ممکن نشد' );
		}
		$rating = absint( $rating );
		if ( $rating >= 1 && $rating <= 5 ) {
			update_comment_meta( $comment_id, 'rating', $rating );
		}
		return true;
	}

	/**
	 * @return true|WP_Error
	 */
	public static function delete( $comment_id, $user_id = 0 ) {
		$user_id    = $user_id ? absint( $user_id ) : get_current_user_id();
		$comment_id = absint( $comment_id );
		$comment    = get_comment( $comment_id );
		if ( ! $comment || (int) $comment->user_id !== (int) $user_id ) {
			return new WP_Error( 'forbidden', 'اجازه حذف این نظر را ندارید' );
		}
		$r = wp_delete_comment( $comment_id, true );
		return $r ? true : new WP_Error( 'db', 'حذف ممکن نشد' );
	}
}
