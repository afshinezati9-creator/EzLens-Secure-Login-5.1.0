<?php
/**
 * EzLens — Professional article comments UI
 * Scope: single posts / articles (not products)
 * Install: Code Snippets (run everywhere) or require in theme/plugin
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Only on single posts (articles).
 */
function ez_ac_is_article() {
	if ( is_admin() ) {
		return false;
	}
	if ( function_exists( 'is_singular' ) && is_singular( 'post' ) ) {
		return true;
	}
	// Allow on custom article CPTs if used later
	if ( function_exists( 'is_singular' ) && is_singular( array( 'post', 'article', 'blog' ) ) ) {
		return true;
	}
	return false;
}

add_action( 'wp_enqueue_scripts', 'ez_ac_assets', 40 );
function ez_ac_assets() {
	if ( ! ez_ac_is_article() ) {
		return;
	}
	wp_register_style( 'ez-article-comments', false, array(), '1.0.0' );
	wp_enqueue_style( 'ez-article-comments' );
	wp_add_inline_style( 'ez-article-comments', ez_ac_css() );
	wp_register_script( 'ez-article-comments', false, array(), '1.0.0', true );
	wp_enqueue_script( 'ez-article-comments' );
	wp_add_inline_script( 'ez-article-comments', ez_ac_js() );
}

/**
 * Soften default comment form labels / placeholders (optional polish).
 */
add_filter( 'comment_form_defaults', 'ez_ac_form_defaults', 20 );
function ez_ac_form_defaults( $defaults ) {
	if ( ! ez_ac_is_article() ) {
		return $defaults;
	}
	$defaults['title_reply']          = 'نظر خود را بنویسید';
	$defaults['title_reply_to']       = 'پاسخ به %s';
	$defaults['cancel_reply_link']    = 'لغو پاسخ';
	$defaults['label_submit']         = 'ارسال نظر';
	$defaults['comment_notes_before'] = '';
	$defaults['comment_notes_after']  = '';
	$defaults['class_form']           = 'ez-ac-form comment-form';
	$defaults['class_submit']         = 'ez-ac-submit submit';
	$defaults['submit_button']        = '<button name="%1$s" type="submit" id="%2$s" class="%3$s">%4$s</button>';
	$defaults['submit_field']         = '<p class="form-submit ez-ac-submit-wrap">%1$s %2$s</p>';
	return $defaults;
}

/**
 * Field markup: modern labels + hints.
 */
add_filter( 'comment_form_default_fields', 'ez_ac_fields', 20 );
function ez_ac_fields( $fields ) {
	if ( ! ez_ac_is_article() ) {
		return $fields;
	}
	$commenter = wp_get_current_commenter();
	$req       = get_option( 'require_name_email' );
	$aria      = $req ? ' aria-required="true" required' : '';
	$star      = $req ? ' <span class="ez-ac-req">*</span>' : '';

	$fields['author'] = sprintf(
		'<p class="comment-form-author ez-ac-field">
			<label for="author">نام%s</label>
			<input id="author" name="author" type="text" value="%s" size="30" maxlength="245" placeholder="نام شما"%s />
		</p>',
		$star,
		esc_attr( $commenter['comment_author'] ),
		$aria
	);

	$fields['email'] = sprintf(
		'<p class="comment-form-email ez-ac-field">
			<label for="email">ایمیل%s</label>
			<input id="email" name="email" type="email" value="%s" size="30" maxlength="100" placeholder="email@example.com" dir="ltr"%s />
			<span class="ez-ac-hint">نمایش داده نمی‌شود</span>
		</p>',
		$star,
		esc_attr( $commenter['comment_author_email'] ),
		$aria
	);

	// Hide URL field for cleaner form
	$fields['url'] = '';

	if ( isset( $fields['cookies'] ) ) {
		$fields['cookies'] = sprintf(
			'<p class="comment-form-cookies-consent ez-ac-cookies">
				<input id="wp-comment-cookies-consent" name="wp-comment-cookies-consent" type="checkbox" value="yes"%s />
				<label for="wp-comment-cookies-consent">نام و ایمیل را برای دفعات بعد ذخیره کن</label>
			</p>',
			empty( $commenter['comment_author_email'] ) ? '' : ' checked="checked"'
		);
	}

	return $fields;
}

add_filter( 'comment_form_field_comment', 'ez_ac_comment_field', 20 );
function ez_ac_comment_field( $field ) {
	if ( ! ez_ac_is_article() ) {
		return $field;
	}
	return '<p class="comment-form-comment ez-ac-field ez-ac-field--comment">
		<label for="comment">متن نظر <span class="ez-ac-req">*</span></label>
		<textarea id="comment" name="comment" cols="45" rows="5" maxlength="65525" required placeholder="نظر خود را بنویسید…"></textarea>
	</p>';
}

/**
 * Wrapper class on comments area when theme uses comments_template.
 */
add_filter( 'comments_template', 'ez_ac_template_mark', 99 );
function ez_ac_template_mark( $template ) {
	if ( ez_ac_is_article() ) {
		add_filter( 'comments_template_query_args', function ( $args ) {
			return $args;
		} );
		add_action( 'comment_form_before', function () {
			echo '<div class="ez-ac-wrap" id="ez-article-comments">';
		}, 1 );
		add_action( 'comment_form_after', function () {
			echo '</div>';
		}, 99 );
	}
	return $template;
}

function ez_ac_css() {
	return <<<'CSS'
/* =========================================================
   EzLens Article Comments — modern / minimal / RTL
   ========================================================= */
.single-post #comments,
.single-post .comments-area,
.single-post .ez-ac-wrap,
body.single #comments,
body.single .comments-area {
	--ez-ac-primary: #031f8a;
	--ez-ac-primary-soft: rgba(3, 31, 138, 0.08);
	--ez-ac-text: #0f172a;
	--ez-ac-muted: #64748b;
	--ez-ac-border: #e8edf5;
	--ez-ac-bg: #f8fafc;
	--ez-ac-surface: #ffffff;
	--ez-ac-radius: 14px;
	--ez-ac-shadow: 0 8px 28px -16px rgba(15, 23, 42, 0.18);
	font-family: IRANYekan, Vazirmatn, Tahoma, sans-serif;
	direction: rtl;
	color: var(--ez-ac-text);
	max-width: 780px;
	margin: 2.5rem auto 3rem;
	padding: 0 1rem;
}

/* Title */
.single-post .comments-title,
.single-post #comments > h2,
.single-post #comments > h3,
.single-post .comments-area > h2,
.single-post .comments-area > h3 {
	display: flex;
	align-items: center;
	gap: 0.6rem;
	font-size: 1.2rem;
	font-weight: 800;
	color: var(--ez-ac-text);
	margin: 0 0 1.25rem;
	padding-bottom: 0.85rem;
	border-bottom: 1px solid var(--ez-ac-border);
	letter-spacing: -0.02em;
}
.single-post .comments-title::before,
.single-post #comments > h2::before,
.single-post #comments > h3::before {
	content: "";
	width: 4px;
	height: 1.1em;
	border-radius: 4px;
	background: linear-gradient(180deg, var(--ez-ac-primary), #1e40af);
	flex-shrink: 0;
}

/* List */
.single-post .comment-list,
.single-post ol.commentlist,
.single-post .comments-area ol {
	list-style: none;
	margin: 0 0 2rem;
	padding: 0;
}
.single-post .comment-list > .comment,
.single-post .comment-list .children > .comment,
.single-post ol.commentlist > li {
	list-style: none;
	margin: 0 0 0.9rem;
	padding: 0;
}

/* Card */
.single-post .comment-body,
.single-post .comment > .comment-body,
.single-post article.comment-body {
	background: var(--ez-ac-surface);
	border: 1px solid var(--ez-ac-border);
	border-radius: var(--ez-ac-radius);
	padding: 1rem 1.1rem 0.95rem;
	box-shadow: var(--ez-ac-shadow);
	transition: border-color 0.2s ease, box-shadow 0.2s ease;
}
.single-post .comment-body:hover {
	border-color: rgba(3, 31, 138, 0.22);
	box-shadow: 0 10px 32px -14px rgba(3, 31, 138, 0.2);
}

/* Nested replies */
.single-post .comment-list .children,
.single-post ol.commentlist .children {
	list-style: none;
	margin: 0.75rem 0 0;
	padding: 0 0 0 0.85rem;
	border-right: 2px solid var(--ez-ac-primary-soft);
}
.single-post .comment-list .children .comment-body {
	background: var(--ez-ac-bg);
	box-shadow: none;
}

/* Meta row */
.single-post .comment-meta,
.single-post .comment-author {
	display: flex;
	flex-wrap: wrap;
	align-items: center;
	gap: 0.55rem 0.75rem;
	margin-bottom: 0.55rem;
}
.single-post .comment-author .avatar,
.single-post .comment-meta .avatar {
	width: 40px !important;
	height: 40px !important;
	border-radius: 12px !important;
	object-fit: cover;
	border: 2px solid #fff;
	box-shadow: 0 0 0 1px var(--ez-ac-border);
}
.single-post .comment-author .fn,
.single-post .comment-author b.fn,
.single-post .fn {
	font-style: normal;
	font-weight: 700;
	font-size: 0.95rem;
	color: var(--ez-ac-text);
}
.single-post .comment-author .fn a,
.single-post .fn a {
	color: inherit;
	text-decoration: none;
}
.single-post .comment-metadata,
.single-post .comment-meta .comment-metadata,
.single-post .comment-meta time {
	font-size: 0.78rem;
	color: var(--ez-ac-muted);
	font-weight: 500;
}
.single-post .comment-metadata a {
	color: var(--ez-ac-muted);
	text-decoration: none;
}
.single-post .comment-metadata a:hover {
	color: var(--ez-ac-primary);
}

/* Content */
.single-post .comment-content,
.single-post .comment-body .comment-content {
	font-size: 0.94rem;
	line-height: 1.85;
	color: #1e293b;
	margin: 0.35rem 0 0.5rem;
}
.single-post .comment-content p {
	margin: 0 0 0.6em;
}
.single-post .comment-content p:last-child {
	margin-bottom: 0;
}
.single-post .comment-content a {
	color: var(--ez-ac-primary);
	text-decoration: none;
	border-bottom: 1px solid rgba(3, 31, 138, 0.25);
}
.single-post .comment-content a:hover {
	border-bottom-color: var(--ez-ac-primary);
}

/* Reply link */
.single-post .reply,
.single-post .comment-reply-link {
	margin-top: 0.35rem;
}
.single-post .comment-reply-link {
	display: inline-flex;
	align-items: center;
	gap: 0.35rem;
	font-size: 0.8rem;
	font-weight: 700;
	color: var(--ez-ac-primary);
	background: var(--ez-ac-primary-soft);
	padding: 0.35rem 0.75rem;
	border-radius: 999px;
	text-decoration: none;
	border: none;
	transition: background 0.18s ease, color 0.18s ease, transform 0.15s ease;
}
.single-post .comment-reply-link:hover,
.single-post .comment-reply-link:focus {
	background: var(--ez-ac-primary);
	color: #fff;
	outline: none;
	transform: translateY(-1px);
}

/* Awaiting moderation */
.single-post .comment-awaiting-moderation {
	display: inline-block;
	margin: 0.4rem 0;
	padding: 0.4rem 0.7rem;
	font-size: 0.8rem;
	font-weight: 600;
	color: #b45309;
	background: #fffbeb;
	border-radius: 8px;
	border: 1px solid #fde68a;
}

/* ---------- Form (#respond) ---------- */
.single-post #respond,
.single-post .comment-respond {
	background: var(--ez-ac-surface);
	border: 1px solid var(--ez-ac-border);
	border-radius: 16px;
	padding: 1.25rem 1.2rem 1.35rem;
	box-shadow: var(--ez-ac-shadow);
	margin-top: 1.5rem;
}
.single-post #reply-title,
.single-post .comment-reply-title {
	font-size: 1.05rem;
	font-weight: 800;
	margin: 0 0 1rem;
	color: var(--ez-ac-text);
	display: flex;
	align-items: center;
	justify-content: space-between;
	gap: 0.75rem;
	flex-wrap: wrap;
}
.single-post #cancel-comment-reply-link {
	font-size: 0.8rem;
	font-weight: 600;
	color: var(--ez-ac-muted);
	text-decoration: none;
	padding: 0.25rem 0.55rem;
	border-radius: 8px;
	background: var(--ez-ac-bg);
}
.single-post #cancel-comment-reply-link:hover {
	color: var(--ez-ac-primary);
	background: var(--ez-ac-primary-soft);
}

.single-post .ez-ac-form,
.single-post .comment-form {
	display: grid;
	gap: 0.85rem;
	margin: 0;
}
.single-post .ez-ac-field {
	margin: 0 !important;
	display: flex;
	flex-direction: column;
	gap: 0.4rem;
}
.single-post .ez-ac-field label {
	font-size: 0.82rem;
	font-weight: 700;
	color: var(--ez-ac-text);
}
.single-post .ez-ac-req {
	color: #dc2626;
	font-weight: 800;
}
.single-post .ez-ac-hint {
	font-size: 0.72rem;
	color: var(--ez-ac-muted);
	margin-top: -0.15rem;
}
.single-post .comment-form input[type="text"],
.single-post .comment-form input[type="email"],
.single-post .comment-form input[type="url"],
.single-post .comment-form textarea {
	width: 100%;
	max-width: 100%;
	box-sizing: border-box;
	border: 1.5px solid var(--ez-ac-border);
	border-radius: 12px;
	background: var(--ez-ac-bg);
	padding: 0.7rem 0.9rem;
	font-family: inherit;
	font-size: 0.95rem;
	color: var(--ez-ac-text);
	transition: border-color 0.18s ease, box-shadow 0.18s ease, background 0.18s ease;
	-webkit-appearance: none;
	appearance: none;
}
.single-post .comment-form textarea {
	min-height: 120px;
	resize: vertical;
	line-height: 1.7;
}
.single-post .comment-form input:focus,
.single-post .comment-form textarea:focus {
	outline: none;
	border-color: var(--ez-ac-primary);
	background: #fff;
	box-shadow: 0 0 0 3px rgba(3, 31, 138, 0.12);
}

/* Name + email side by side on wider screens */
.single-post .comment-form {
	grid-template-columns: 1fr 1fr;
}
.single-post .comment-form-comment,
.single-post .ez-ac-field--comment,
.single-post .comment-form-cookies-consent,
.single-post .ez-ac-cookies,
.single-post .form-submit,
.single-post .ez-ac-submit-wrap,
.single-post .comment-notes,
.single-post .logged-in-as {
	grid-column: 1 / -1;
}

.single-post .ez-ac-cookies,
.single-post .comment-form-cookies-consent {
	display: flex;
	align-items: flex-start;
	gap: 0.5rem;
	font-size: 0.82rem;
	color: var(--ez-ac-muted);
	margin: 0 !important;
}
.single-post .ez-ac-cookies input {
	margin-top: 0.2rem;
	accent-color: var(--ez-ac-primary);
}

.single-post .logged-in-as {
	font-size: 0.85rem;
	color: var(--ez-ac-muted);
	margin: 0 0 0.5rem !important;
}
.single-post .logged-in-as a {
	color: var(--ez-ac-primary);
	text-decoration: none;
	font-weight: 600;
}

.single-post .ez-ac-submit,
.single-post .comment-form .submit,
.single-post .comment-form input[type="submit"],
.single-post .comment-form button[type="submit"] {
	display: inline-flex;
	align-items: center;
	justify-content: center;
	min-height: 46px;
	padding: 0.65rem 1.4rem;
	border: none;
	border-radius: 12px;
	background: linear-gradient(135deg, #031f8a 0%, #1e40af 100%);
	color: #ffffff !important;
	font-family: inherit;
	font-size: 0.95rem;
	font-weight: 700;
	cursor: pointer;
	box-shadow: 0 8px 20px -10px rgba(3, 31, 138, 0.55);
	transition: transform 0.15s ease, box-shadow 0.18s ease, filter 0.18s ease;
}
.single-post .ez-ac-submit:hover,
.single-post .comment-form .submit:hover,
.single-post .comment-form input[type="submit"]:hover,
.single-post .comment-form button[type="submit"]:hover {
	filter: brightness(1.06);
	transform: translateY(-1px);
	box-shadow: 0 12px 24px -10px rgba(3, 31, 138, 0.6);
}
.single-post .ez-ac-submit:active {
	transform: translateY(0);
}

/* Empty state */
.single-post .no-comments,
.single-post .comments-area > p.no-comments {
	text-align: center;
	padding: 1.5rem 1rem;
	color: var(--ez-ac-muted);
	background: var(--ez-ac-bg);
	border-radius: var(--ez-ac-radius);
	border: 1px dashed var(--ez-ac-border);
	font-size: 0.92rem;
}

/* Navigation (older / newer comments) */
.single-post .comment-navigation,
.single-post .nav-links {
	display: flex;
	justify-content: space-between;
	gap: 0.75rem;
	margin: 1rem 0 1.5rem;
}
.single-post .comment-navigation a {
	font-size: 0.85rem;
	font-weight: 600;
	color: var(--ez-ac-primary);
	text-decoration: none;
	padding: 0.4rem 0.8rem;
	border-radius: 10px;
	background: var(--ez-ac-primary-soft);
}
.single-post .comment-navigation a:hover {
	background: var(--ez-ac-primary);
	color: #fff;
}

@media (max-width: 640px) {
	.single-post .comment-form {
		grid-template-columns: 1fr;
	}
	.single-post #comments,
	.single-post .comments-area {
		margin-top: 1.75rem;
		padding: 0 0.5rem;
	}
	.single-post .comment-body {
		padding: 0.9rem;
	}
	.single-post .comment-list .children {
		padding-right: 0.55rem;
	}
	.single-post #respond {
		padding: 1rem;
	}
}

@media (prefers-reduced-motion: reduce) {
	.single-post .comment-body,
	.single-post .comment-reply-link,
	.single-post .ez-ac-submit {
		transition: none !important;
	}
}
CSS;
}

function ez_ac_js() {
	return <<<'JS'
(function () {
  "use strict";
  if (!document.body || !document.body.classList.contains("single")) return;

  // Smooth focus on reply
  document.addEventListener("click", function (e) {
    var a = e.target.closest && e.target.closest(".comment-reply-link");
    if (!a) return;
    setTimeout(function () {
      var ta = document.getElementById("comment");
      if (ta) {
        ta.focus();
        ta.scrollIntoView({ behavior: "smooth", block: "center" });
      }
    }, 120);
  });

  // Character counter on textarea
  var ta = document.getElementById("comment");
  if (ta && !document.getElementById("ez-ac-counter")) {
    var counter = document.createElement("div");
    counter.id = "ez-ac-counter";
    counter.style.cssText =
      "font-size:0.75rem;color:#94a3b8;text-align:left;direction:ltr;margin-top:4px;";
    ta.parentNode.appendChild(counter);
    function upd() {
      var n = (ta.value || "").length;
      counter.textContent = n ? n + " chars" : "";
    }
    ta.addEventListener("input", upd);
    upd();
  }
})();
JS;
}
