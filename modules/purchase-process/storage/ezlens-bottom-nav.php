<?php
/**
 * Mobile bottom nav — EzLens
 * Items: Shop, Category+Brand, Cart, Login/Account
 * Guest login: AJAX OTP sheet (no full page reload)
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* ============================================================
 * 1) Render bar + sheets
 * ============================================================ */
add_action( 'wp_footer', 'ez_bottom_nav_render', 999 );
function ez_bottom_nav_render() {

	if ( function_exists( 'is_account_page' ) && is_account_page() ) {
		return;
	}
	if ( function_exists( 'is_wc_endpoint_url' ) ) {
		$endpoints = array( 'orders', 'view-order', 'downloads', 'edit-account', 'edit-address', 'payment-methods' );
		foreach ( $endpoints as $ep ) {
			if ( is_wc_endpoint_url( $ep ) ) {
				return;
			}
		}
	}
	if ( isset( $GLOBALS['pagenow'] ) && in_array( $GLOBALS['pagenow'], array( 'wp-login.php', 'wp-register.php' ), true ) ) {
		return;
	}

	$is_logged_in = is_user_logged_in();
	$cart_count   = ( function_exists( 'WC' ) && WC()->cart ) ? WC()->cart->get_cart_contents_count() : 0;
	$account_url  = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'myaccount' ) : wp_login_url();
	$shop_url     = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/' );
	$cart_url     = function_exists( 'wc_get_cart_url' ) ? wc_get_cart_url() : home_url( '/cart/' );

	$auth_nonce = wp_create_nonce( 'minimal_auth_secure_nonce_v5' );
	$ajax_url   = admin_url( 'admin-ajax.php' );
	?>
	<nav class="ez-bnav" id="ezBottomNav" role="navigation" aria-label="ناوبری پایین">
		<a class="ez-bnav-item" href="<?php echo esc_url( $shop_url ); ?>" aria-label="فروشگاه">
			<svg viewBox="0 0 24 24" width="22" height="22" aria-hidden="true">
				<path d="M3 9l9-6 9 6v10a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z" fill="none" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>
				<path d="M9 21v-8h6v8" fill="none" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>
			</svg>
			<span>فروشگاه</span>
		</a>

		<button type="button" class="ez-bnav-item" data-ez-open="cat" aria-label="دسته‌بندی و برند">
			<svg viewBox="0 0 24 24" width="22" height="22" aria-hidden="true">
				<rect x="3" y="3" width="7" height="7" rx="2" fill="none" stroke="currentColor" stroke-width="2"/>
				<rect x="14" y="3" width="7" height="7" rx="2" fill="none" stroke="currentColor" stroke-width="2"/>
				<rect x="3" y="14" width="7" height="7" rx="2" fill="none" stroke="currentColor" stroke-width="2"/>
				<rect x="14" y="14" width="7" height="7" rx="2" fill="none" stroke="currentColor" stroke-width="2"/>
			</svg>
			<span>دسته و برند</span>
		</button>

		<a class="ez-bnav-item ez-bnav-cart" href="<?php echo esc_url( $cart_url ); ?>" aria-label="سبد خرید">
			<svg viewBox="0 0 24 24" width="22" height="22" aria-hidden="true">
				<path d="M6 6h15l-1.5 9h-12z" fill="none" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>
				<circle cx="9" cy="20" r="1.5" fill="currentColor"/>
				<circle cx="18" cy="20" r="1.5" fill="currentColor"/>
				<path d="M6 6L5 3H2" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
			</svg>
			<span>سبد خرید</span>
			<em class="ez-bnav-badge<?php echo $cart_count > 0 ? ' is-on' : ''; ?>" data-ez-cart-badge><?php echo esc_html( number_format_i18n( $cart_count ) ); ?></em>
		</a>

		<?php if ( $is_logged_in ) : ?>
			<a class="ez-bnav-item" href="<?php echo esc_url( $account_url ); ?>" aria-label="حساب من">
				<svg viewBox="0 0 24 24" width="22" height="22" aria-hidden="true">
					<circle cx="12" cy="8" r="4" fill="none" stroke="currentColor" stroke-width="2"/>
					<path d="M4 21v-2a5 5 0 0 1 5-5h6a5 5 0 0 1 5 5v2" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
				</svg>
				<span>حساب من</span>
			</a>
		<?php else : ?>
			<button type="button" class="ez-bnav-item" data-ez-open="auth" aria-label="ورود / ثبت‌نام" id="ezBnavAuthBtn">
				<svg viewBox="0 0 24 24" width="22" height="22" aria-hidden="true">
					<path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
					<path d="M10 17l5-5-5-5M15 12H3" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				</svg>
				<span>ورود / ثبت‌نام</span>
			</button>
		<?php endif; ?>
	</nav>

	<!-- Category / Brand sheet -->
	<div class="ez-bnav-sheet" id="ezCatSheet" aria-hidden="true">
		<div class="ez-bnav-sheet-bg" data-ez-close></div>
		<div class="ez-bnav-sheet-panel" role="dialog" aria-label="دسته‌بندی و برندها">
			<div class="ez-bnav-sheet-head">
				<span class="ez-bnav-sheet-title">دسته‌بندی و برندها</span>
				<button type="button" class="ez-bnav-sheet-x" data-ez-close aria-label="بستن"><span aria-hidden="true">×</span></button>
			</div>
			<div class="ez-bnav-sheet-tabs" role="tablist">
				<button type="button" class="ez-bnav-tab is-active" data-ez-tab="cat" role="tab" aria-selected="true">دسته‌بندی‌ها</button>
				<button type="button" class="ez-bnav-tab" data-ez-tab="brand" role="tab" aria-selected="false">برندها</button>
			</div>
			<div class="ez-bnav-sheet-body">
				<div class="ez-bnav-tab-panel is-active" data-ez-panel="cat">
					<?php echo ez_bottom_nav_terms_html( 'product_cat' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				</div>
				<div class="ez-bnav-tab-panel" data-ez-panel="brand">
					<?php echo ez_bottom_nav_terms_html( 'product_brand' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				</div>
			</div>
		</div>
	</div>

	<?php if ( ! $is_logged_in ) : ?>
	<!-- Auth OTP sheet (AJAX, no page reload) -->
	<div class="ez-bnav-sheet" id="ezAuthSheet" aria-hidden="true">
		<div class="ez-bnav-sheet-bg" data-ez-auth-close></div>
		<div class="ez-bnav-sheet-panel ez-auth-panel" role="dialog" aria-label="ورود و ثبت‌نام" aria-modal="true">
			<div class="ez-bnav-sheet-head">
				<span class="ez-bnav-sheet-title" id="ezAuthTitle">ورود / ثبت‌نام</span>
				<button type="button" class="ez-bnav-sheet-x" data-ez-auth-close aria-label="بستن"><span aria-hidden="true">×</span></button>
			</div>
			<div class="ez-bnav-sheet-body ez-auth-body">
				<p class="ez-auth-hint" id="ezAuthHint">شماره موبایل خود را وارد کنید. کد تأیید پیامک می‌شود.</p>

				<!-- Step phone -->
				<div class="ez-auth-step is-active" data-ez-auth-step="phone">
					<label class="ez-auth-label" for="ezAuthPhone">شماره موبایل</label>
					<input
						type="tel"
						id="ezAuthPhone"
						class="ez-auth-input"
						inputmode="numeric"
						autocomplete="tel"
						placeholder="0912xxxxxxx"
						maxlength="11"
						dir="ltr"
					/>
					<button type="button" class="ez-auth-btn" id="ezAuthSendBtn">
						<span class="ez-auth-btn-txt">ارسال کد تأیید</span>
						<span class="ez-auth-btn-spin" hidden aria-hidden="true"></span>
					</button>
				</div>

				<!-- Step OTP -->
				<div class="ez-auth-step" data-ez-auth-step="otp">
					<label class="ez-auth-label" for="ezAuthCode">کد ۶ رقمی</label>
					<input
						type="text"
						id="ezAuthCode"
						class="ez-auth-input ez-auth-code"
						inputmode="numeric"
						autocomplete="one-time-code"
						placeholder="------"
						maxlength="6"
						dir="ltr"
					/>
					<button type="button" class="ez-auth-btn" id="ezAuthVerifyBtn">
						<span class="ez-auth-btn-txt">تأیید و ورود</span>
						<span class="ez-auth-btn-spin" hidden aria-hidden="true"></span>
					</button>
					<div class="ez-auth-resend-row">
						<span class="ez-auth-timer" id="ezAuthTimer">۰۲:۰۰</span>
						<button type="button" class="ez-auth-link" id="ezAuthResend" disabled>ارسال مجدد</button>
						<button type="button" class="ez-auth-link" id="ezAuthBackPhone">تغییر شماره</button>
					</div>
				</div>

				<div class="ez-auth-msg" id="ezAuthMsg" role="status" aria-live="polite" hidden></div>
			</div>
		</div>
	</div>
	<?php endif; ?>

	<style id="ez-bnav-css">
	.ez-bnav,
	.ez-bnav *{ box-sizing:border-box; }
	.ez-bnav{
		position:fixed; bottom:0; left:0; right:0; z-index:99999;
		display:none; justify-content:space-around; align-items:stretch;
		background:#ffffff; border-top:1px solid #eef1f7;
		padding:6px 6px calc(6px + env(safe-area-inset-bottom, 0px));
		height:62px; font-family:IRANYekan,Tahoma,sans-serif; direction:rtl;
		box-shadow:0 -6px 24px -14px rgba(15,23,42,.18);
	}
	.ez-bnav-item{
		flex:1 1 0; min-width:0; display:flex; flex-direction:column; align-items:center; justify-content:center;
		gap:3px; position:relative; padding:4px 2px; border:none; background:transparent; color:#64748b;
		text-decoration:none; font-family:inherit; font-size:10.5px; font-weight:500; line-height:1.2;
		border-radius:10px; cursor:pointer; transition:color .2s, transform .15s;
		-webkit-tap-highlight-color:transparent;
	}
	.ez-bnav-item svg{ width:22px; height:22px; flex-shrink:0; }
	.ez-bnav-item span{ display:block; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; max-width:100%; }
	.ez-bnav-item:hover,.ez-bnav-item:focus,.ez-bnav-item:active{ color:#031f8a; outline:none; }
	.ez-bnav-item:active{ transform:scale(.94); }
	.ez-bnav-cart{ position:relative; }
	.ez-bnav-badge{
		position:absolute; top:2px; inset-inline-end:calc(50% - 22px);
		min-width:16px; height:16px; padding:0 4px; background:#ff6b6d; color:#fff;
		font-style:normal; font-size:9.5px; font-weight:700; line-height:16px; text-align:center;
		border-radius:99px; display:none; box-shadow:0 2px 6px rgba(255,107,109,.35);
	}
	.ez-bnav-badge.is-on{ display:inline-block; }

	.ez-bnav-sheet{ position:fixed; inset:0; z-index:100000; pointer-events:none; }
	.ez-bnav-sheet.is-open{ pointer-events:auto; }
	.ez-bnav-sheet-bg{
		position:absolute; inset:0; background:rgba(15,23,42,.45); opacity:0;
		transition:opacity .25s ease; backdrop-filter:blur(2px); -webkit-backdrop-filter:blur(2px);
	}
	.ez-bnav-sheet.is-open .ez-bnav-sheet-bg{ opacity:1; }
	.ez-bnav-sheet-panel{
		position:absolute; left:0; right:0; bottom:0; background:#ffffff;
		border-radius:20px 20px 0 0; max-height:80vh; display:flex; flex-direction:column;
		transform:translateY(110%); transition:transform .32s cubic-bezier(.22,1,.36,1);
		will-change:transform; box-shadow:0 -10px 40px -18px rgba(15,23,42,.35);
		font-family:IRANYekan,Tahoma,sans-serif; direction:rtl;
	}
	.ez-bnav-sheet.is-open .ez-bnav-sheet-panel{ transform:translateY(0); }
	.ez-bnav-sheet-head{
		display:flex; align-items:center; justify-content:space-between;
		padding:14px 18px 12px; border-bottom:1px solid #f1f5f9; flex-shrink:0;
	}
	.ez-bnav-sheet-title{ font-size:15px; font-weight:700; color:#0f172a; }
	.ez-bnav-sheet-x{
		width:34px; height:34px; display:inline-flex; align-items:center; justify-content:center;
		border:none; border-radius:50%; background:#f1f5f9; color:#334155; font-family:inherit;
		font-size:22px; font-weight:400; line-height:1; cursor:pointer; padding:0; padding-bottom:2px;
		transition:background .18s, color .18s, transform .15s;
	}
	.ez-bnav-sheet-x span{ display:inline-block; line-height:1; font-size:22px; transform:translateY(-1px); }
	.ez-bnav-sheet-x:hover,.ez-bnav-sheet-x:focus{ background:#031f8a; color:#ffffff; outline:none; }
	.ez-bnav-sheet-x:active{ transform:scale(.92); }
	.ez-bnav-sheet-tabs{
		display:flex; gap:6px; padding:10px 18px; background:#ffffff;
		border-bottom:1px solid #f1f5f9; flex-shrink:0;
	}
	.ez-bnav-tab{
		flex:1 1 0; padding:8px 12px; background:#f8fafc; color:#475569; border:1px solid transparent;
		border-radius:10px; font-family:inherit; font-size:12.5px; font-weight:600; cursor:pointer;
		transition:background .18s, color .18s, border-color .18s;
	}
	.ez-bnav-tab:hover{ color:#031f8a; background:rgba(3,31,138,.06); }
	.ez-bnav-tab.is-active{ background:#031f8a; color:#ffffff; border-color:#031f8a; }
	.ez-bnav-sheet-body{
		padding:14px 16px calc(24px + env(safe-area-inset-bottom, 0px));
		overflow-y:auto; -webkit-overflow-scrolling:touch;
	}
	.ez-bnav-tab-panel{ display:none; }
	.ez-bnav-tab-panel.is-active{ display:block; }
	.ez-bnav-cats{ display:grid; grid-template-columns:1fr; gap:8px; margin:0; padding:0; list-style:none; }
	.ez-bnav-cat{
		display:flex; align-items:center; justify-content:space-between; gap:12px;
		padding:12px 14px; background:#f8fafc; border:1px solid #eef1f7; border-radius:12px;
		color:#0f172a; text-decoration:none; font-size:13px; font-weight:600;
		transition:background .18s, border-color .18s, color .18s, transform .15s;
	}
	.ez-bnav-cat:hover,.ez-bnav-cat:focus{
		background:#031f8a; border-color:#031f8a; color:#ffffff; outline:none; transform:translateY(-1px);
	}
	.ez-bnav-cat-name{
		display:inline-flex; align-items:center; gap:10px; min-width:0;
		overflow:hidden; text-overflow:ellipsis; white-space:nowrap;
	}
	.ez-bnav-cat-name svg{ width:16px; height:16px; flex-shrink:0; opacity:.75; }
	.ez-bnav-cat-count{
		flex-shrink:0; display:inline-flex; align-items:center; justify-content:center;
		min-width:28px; height:22px; padding:0 8px; background:rgba(3,31,138,.08); color:#031f8a;
		font-size:11.5px; font-weight:700; border-radius:99px;
	}
	.ez-bnav-cat:hover .ez-bnav-cat-count,.ez-bnav-cat:focus .ez-bnav-cat-count{
		background:rgba(255,255,255,.18); color:#ffffff;
	}
	.ez-bnav-cat.is-empty{ opacity:.72; }
	.ez-bnav-cat.is-empty .ez-bnav-cat-count{ background:rgba(100,116,139,.12); color:#64748b; }
	.ez-bnav-empty{ text-align:center; padding:32px 16px; color:#64748b; font-size:13px; }

	/* ---- Auth sheet ---- */
	.ez-auth-panel{ max-height:88vh; }
	.ez-auth-body{ padding-top:18px; }
	.ez-auth-hint{
		margin:0 0 16px; font-size:13px; color:#64748b; line-height:1.7;
	}
	.ez-auth-step{ display:none; }
	.ez-auth-step.is-active{ display:block; animation:ezAuthIn .28s ease; }
	@keyframes ezAuthIn{
		from{ opacity:0; transform:translateY(8px); }
		to{ opacity:1; transform:translateY(0); }
	}
	.ez-auth-label{
		display:block; font-size:12.5px; font-weight:600; color:#0f172a; margin-bottom:8px;
	}
	.ez-auth-input{
		width:100%; height:48px; padding:0 14px; border:1.5px solid #e2e8f0; border-radius:12px;
		font-family:inherit; font-size:16px; color:#0f172a; background:#f8fafc;
		transition:border-color .18s, box-shadow .18s, background .18s;
		-webkit-appearance:none; appearance:none;
	}
	.ez-auth-input:focus{
		outline:none; border-color:#031f8a; background:#fff;
		box-shadow:0 0 0 3px rgba(3,31,138,.12);
	}
	.ez-auth-code{
		letter-spacing:.35em; text-align:center; font-weight:700; font-size:20px;
	}
	.ez-auth-btn{
		width:100%; margin-top:14px; height:48px; border:none; border-radius:12px;
		background:#031f8a; color:#ffffff; font-family:inherit; font-size:14.5px; font-weight:700;
		cursor:pointer; display:inline-flex; align-items:center; justify-content:center; gap:8px;
		transition:background .18s, transform .15s, opacity .18s;
	}
	.ez-auth-btn:hover{ background:#02156a; }
	.ez-auth-btn:active{ transform:scale(.98); }
	.ez-auth-btn:disabled{ opacity:.65; cursor:not-allowed; transform:none; }
	.ez-auth-btn-spin{
		width:18px; height:18px; border:2px solid rgba(255,255,255,.35);
		border-top-color:#fff; border-radius:50%; animation:ezSpin .7s linear infinite;
	}
	@keyframes ezSpin{ to{ transform:rotate(360deg); } }
	.ez-auth-resend-row{
		display:flex; flex-wrap:wrap; align-items:center; justify-content:space-between;
		gap:8px; margin-top:14px;
	}
	.ez-auth-timer{ font-size:12.5px; font-weight:600; color:#031f8a; font-variant-numeric:tabular-nums; }
	.ez-auth-link{
		border:none; background:transparent; color:#031f8a; font-family:inherit;
		font-size:12.5px; font-weight:600; cursor:pointer; padding:4px 2px;
	}
	.ez-auth-link:disabled{ color:#94a3b8; cursor:not-allowed; }
	.ez-auth-msg{
		margin-top:14px; padding:10px 12px; border-radius:10px; font-size:12.5px; line-height:1.6;
	}
	.ez-auth-msg.is-ok{ background:rgba(16,185,129,.1); color:#047857; }
	.ez-auth-msg.is-err{ background:rgba(239,68,68,.1); color:#b91c1c; }

	@media (max-width:1024px){
		.ez-bnav{ display:flex; }
		body{ padding-bottom:62px; }
		.wd-toolbar{ display:none !important; }
	}
	@media (max-width:380px){
		.ez-bnav-item{ font-size:10px; }
		.ez-bnav-item svg{ width:20px; height:20px; }
		.ez-bnav-cat{ font-size:12.5px; padding:11px 12px; }
		.ez-bnav-tab{ font-size:12px; padding:7px 10px; }
	}
	@media (prefers-reduced-motion:reduce){
		.ez-bnav-item,.ez-bnav-sheet-panel,.ez-bnav-sheet-bg,.ez-bnav-cat,
		.ez-bnav-sheet-x,.ez-bnav-tab,.ez-auth-step{ transition:none !important; animation:none !important; }
	}
	</style>

	<script id="ez-bnav-js">
	(function(){
		"use strict";

		/* ---------- Category sheet ---------- */
		var catSheet = document.getElementById('ezCatSheet');
		if (catSheet) {
			var catOpeners = document.querySelectorAll('[data-ez-open="cat"]');
			var catClosers = catSheet.querySelectorAll('[data-ez-close]');
			function openCat(){
				catSheet.classList.add('is-open');
				catSheet.setAttribute('aria-hidden','false');
				document.body.style.overflow = 'hidden';
			}
			function closeCat(){
				catSheet.classList.remove('is-open');
				catSheet.setAttribute('aria-hidden','true');
				if (!document.getElementById('ezAuthSheet') || !document.getElementById('ezAuthSheet').classList.contains('is-open')) {
					document.body.style.overflow = '';
				}
			}
			catOpeners.forEach(function(btn){
				btn.addEventListener('click', function(e){ e.preventDefault(); openCat(); });
			});
			catClosers.forEach(function(btn){ btn.addEventListener('click', closeCat); });
			document.addEventListener('keydown', function(e){
				if (e.key === 'Escape') closeCat();
			});
			var tabs = catSheet.querySelectorAll('[data-ez-tab]');
			var panels = catSheet.querySelectorAll('[data-ez-panel]');
			tabs.forEach(function(tab){
				tab.addEventListener('click', function(){
					var key = tab.getAttribute('data-ez-tab');
					tabs.forEach(function(t){
						var on = t === tab;
						t.classList.toggle('is-active', on);
						t.setAttribute('aria-selected', on ? 'true' : 'false');
					});
					panels.forEach(function(p){
						p.classList.toggle('is-active', p.getAttribute('data-ez-panel') === key);
					});
					var scroller = catSheet.querySelector('.ez-bnav-sheet-body');
					if (scroller) scroller.scrollTop = 0;
				});
			});
		}

		/* ---------- Cart badge ---------- */
		var ajaxUrl = <?php echo wp_json_encode( $ajax_url ); ?>;
		var cartNonce = <?php echo wp_json_encode( wp_create_nonce( 'ez_bnav_nonce' ) ); ?>;
		function refreshBadge(){
			var badge = document.querySelector('[data-ez-cart-badge]');
			if (!badge) return;
			var fd = new FormData();
			fd.append('action', 'ez_bnav_cart_count');
			fd.append('nonce', cartNonce);
			fetch(ajaxUrl, { method:'POST', credentials:'same-origin', body: fd })
				.then(function(r){ return r.json(); })
				.then(function(res){
					if (res && res.success) {
						var n = parseInt(res.data.count || 0, 10);
						badge.textContent = (n).toLocaleString('fa-IR');
						badge.classList.toggle('is-on', n > 0);
					}
				})
				.catch(function(){});
		}
		if (window.jQuery) {
			jQuery(document.body).on(
				'added_to_cart removed_from_cart updated_cart_totals wc_fragments_refreshed',
				function(){ setTimeout(refreshBadge, 200); }
			);
		}

		/* ---------- Auth OTP sheet (guest only) ---------- */
		var authSheet = document.getElementById('ezAuthSheet');
		if (!authSheet) return;

		var authNonce   = <?php echo wp_json_encode( $auth_nonce ); ?>;
		var accountUrl  = <?php echo wp_json_encode( $account_url ); ?>;
		var phoneInput  = document.getElementById('ezAuthPhone');
		var codeInput   = document.getElementById('ezAuthCode');
		var sendBtn     = document.getElementById('ezAuthSendBtn');
		var verifyBtn   = document.getElementById('ezAuthVerifyBtn');
		var resendBtn   = document.getElementById('ezAuthResend');
		var backBtn     = document.getElementById('ezAuthBackPhone');
		var timerEl     = document.getElementById('ezAuthTimer');
		var msgEl       = document.getElementById('ezAuthMsg');
		var titleEl     = document.getElementById('ezAuthTitle');
		var hintEl      = document.getElementById('ezAuthHint');
		var stepPhone   = authSheet.querySelector('[data-ez-auth-step="phone"]');
		var stepOtp     = authSheet.querySelector('[data-ez-auth-step="otp"]');
		var mobileCache = '';
		var timerId     = null;
		var timerLeft   = 0;

		function openAuth(){
			authSheet.classList.add('is-open');
			authSheet.setAttribute('aria-hidden','false');
			document.body.style.overflow = 'hidden';
			setTimeout(function(){ if (phoneInput) phoneInput.focus(); }, 280);
		}
		function closeAuth(){
			authSheet.classList.remove('is-open');
			authSheet.setAttribute('aria-hidden','true');
			document.body.style.overflow = '';
		}
		document.querySelectorAll('[data-ez-open="auth"]').forEach(function(btn){
			btn.addEventListener('click', function(e){
				e.preventDefault();
				openAuth();
			});
		});
		authSheet.querySelectorAll('[data-ez-auth-close]').forEach(function(btn){
			btn.addEventListener('click', closeAuth);
		});
		document.addEventListener('keydown', function(e){
			if (e.key === 'Escape' && authSheet.classList.contains('is-open')) closeAuth();
		});

		function showMsg(text, ok){
			if (!msgEl) return;
			msgEl.hidden = !text;
			msgEl.textContent = text || '';
			msgEl.classList.toggle('is-ok', !!ok);
			msgEl.classList.toggle('is-err', !ok && !!text);
		}
		function setLoading(btn, on){
			if (!btn) return;
			btn.disabled = !!on;
			var spin = btn.querySelector('.ez-auth-btn-spin');
			var txt  = btn.querySelector('.ez-auth-btn-txt');
			if (spin) spin.hidden = !on;
			if (txt) txt.style.opacity = on ? '0.7' : '1';
		}
		function normalizePhone(v){
			v = (v || '').replace(/[^\d]/g, '');
			if (v.indexOf('98') === 0 && v.length === 12) v = '0' + v.slice(2);
			if (v.indexOf('9') === 0 && v.length === 10) v = '0' + v;
			return v;
		}
		function toFaDigits(s){
			return String(s).replace(/\d/g, function(d){
				return '۰۱۲۳۴۵۶۷۸۹'[d];
			});
		}
		function showStep(name){
			if (stepPhone) stepPhone.classList.toggle('is-active', name === 'phone');
			if (stepOtp) stepOtp.classList.toggle('is-active', name === 'otp');
			if (titleEl) titleEl.textContent = name === 'otp' ? 'تأیید کد' : 'ورود / ثبت‌نام';
			if (hintEl) {
				hintEl.textContent = name === 'otp'
					? ('کد ارسال‌شده به ' + mobileCache + ' را وارد کنید.')
					: 'شماره موبایل خود را وارد کنید. کد تأیید پیامک می‌شود.';
			}
			showMsg('', true);
			if (name === 'otp' && codeInput) {
				codeInput.value = '';
				setTimeout(function(){ codeInput.focus(); }, 200);
			}
		}
		function startTimer(sec){
			timerLeft = sec || 120;
			if (timerId) clearInterval(timerId);
			if (resendBtn) resendBtn.disabled = true;
			function tick(){
				var m = Math.floor(timerLeft / 60);
				var s = timerLeft % 60;
				if (timerEl) {
					timerEl.textContent = toFaDigits(
						(m < 10 ? '0' : '') + m + ':' + (s < 10 ? '0' : '') + s
					);
				}
				if (timerLeft <= 0) {
					clearInterval(timerId);
					timerId = null;
					if (resendBtn) resendBtn.disabled = false;
					return;
				}
				timerLeft--;
			}
			tick();
			timerId = setInterval(tick, 1000);
		}
		function postAuth(action, fields){
			var fd = new FormData();
			fd.append('action', action);
			fd.append('security', authNonce);
			Object.keys(fields).forEach(function(k){ fd.append(k, fields[k]); });
			return fetch(ajaxUrl, {
				method: 'POST',
				credentials: 'same-origin',
				body: fd
			}).then(function(r){ return r.json(); });
		}
		function sendOtp(){
			var phone = normalizePhone(phoneInput && phoneInput.value);
			if (!/^09\d{9}$/.test(phone)) {
				showMsg('شماره موبایل معتبر نیست (مثال: 09123456789)', false);
				return;
			}
			mobileCache = phone;
			setLoading(sendBtn, true);
			showMsg('', true);
			postAuth('ezlens_otp_send', { mobile: phone })
				.then(function(res){
					setLoading(sendBtn, false);
					if (res && res.success) {
						showStep('otp');
						startTimer(120);
						showMsg((res.data && res.data.message) || 'کد تأیید ارسال شد.', true);
					} else {
						var msg = (res && res.data && res.data.message) || 'ارسال کد ناموفق بود.';
						showMsg(msg, false);
					}
				})
				.catch(function(){
					setLoading(sendBtn, false);
					showMsg('خطا در ارتباط با سرور. دوباره تلاش کنید.', false);
				});
		}
		function verifyOtp(){
			var code = (codeInput && codeInput.value || '').replace(/[^\d]/g, '');
			if (code.length !== 6) {
				showMsg('کد باید ۶ رقم باشد.', false);
				return;
			}
			if (!mobileCache) {
				showStep('phone');
				return;
			}
			setLoading(verifyBtn, true);
			showMsg('', true);
			postAuth('ezlens_otp_verify', { mobile: mobileCache, code: code })
				.then(function(res){
					if (res && res.success) {
						showMsg((res.data && res.data.message) || 'ورود موفق', true);
						var go = (res.data && res.data.redirect) ? res.data.redirect : accountUrl;
						// Prefer account/dashboard for bottom-nav flow
						if (accountUrl) go = accountUrl;
						setTimeout(function(){ window.location.href = go; }, 350);
					} else {
						setLoading(verifyBtn, false);
						var msg = (res && res.data && res.data.message) || 'کد نادرست است.';
						showMsg(msg, false);
					}
				})
				.catch(function(){
					setLoading(verifyBtn, false);
					showMsg('خطا در ارتباط با سرور. دوباره تلاش کنید.', false);
				});
		}

		if (sendBtn) sendBtn.addEventListener('click', sendOtp);
		if (verifyBtn) verifyBtn.addEventListener('click', verifyOtp);
		if (resendBtn) resendBtn.addEventListener('click', function(){
			if (resendBtn.disabled) return;
			if (phoneInput) phoneInput.value = mobileCache;
			sendOtp();
		});
		if (backBtn) backBtn.addEventListener('click', function(){
			showStep('phone');
			if (timerId) { clearInterval(timerId); timerId = null; }
		});
		if (phoneInput) {
			phoneInput.addEventListener('keydown', function(e){
				if (e.key === 'Enter') { e.preventDefault(); sendOtp(); }
			});
			phoneInput.addEventListener('input', function(){
				var v = normalizePhone(phoneInput.value);
				if (v.length > 11) v = v.slice(0, 11);
				phoneInput.value = v;
			});
		}
		if (codeInput) {
			codeInput.addEventListener('keydown', function(e){
				if (e.key === 'Enter') { e.preventDefault(); verifyOtp(); }
			});
			codeInput.addEventListener('input', function(){
				codeInput.value = codeInput.value.replace(/[^\d]/g, '').slice(0, 6);
				if (codeInput.value.length === 6) verifyOtp();
			});
		}
	})();
	</script>
	<?php
}

/* ============================================================
 * 2) Terms list
 * ============================================================ */
function ez_bottom_nav_terms_html( $taxonomy ) {

	if ( ! taxonomy_exists( $taxonomy ) ) {
		$label = ( $taxonomy === 'product_brand' ) ? 'برندی' : 'دسته‌بندی‌ای';
		return '<div class="ez-bnav-empty">' . esc_html( $label ) . ' یافت نشد.</div>';
	}

	$terms = get_terms( array(
		'taxonomy'   => $taxonomy,
		'hide_empty' => false,
		'parent'     => 0,
		'orderby'    => 'count',
		'order'      => 'DESC',
		'number'     => 60,
	) );

	if ( is_wp_error( $terms ) || empty( $terms ) ) {
		$label = ( $taxonomy === 'product_brand' ) ? 'برندی' : 'دسته‌بندی‌ای';
		return '<div class="ez-bnav-empty">' . esc_html( $label ) . ' یافت نشد.</div>';
	}

	usort( $terms, function( $a, $b ) {
		if ( (int) $a->count === (int) $b->count ) {
			return strcasecmp( $a->name, $b->name );
		}
		return ( (int) $b->count < (int) $a->count ) ? -1 : 1;
	} );

	ob_start();
	?>
	<ul class="ez-bnav-cats">
		<?php foreach ( $terms as $term ) : ?>
			<?php
			$link = get_term_link( $term );
			if ( is_wp_error( $link ) ) {
				continue;
			}
			$is_empty = ( (int) $term->count === 0 );
			?>
			<li>
				<a class="ez-bnav-cat<?php echo $is_empty ? ' is-empty' : ''; ?>"
				   href="<?php echo esc_url( $link ); ?>">
					<span class="ez-bnav-cat-name">
						<?php if ( $taxonomy === 'product_brand' ) : ?>
							<svg viewBox="0 0 24 24" aria-hidden="true">
								<path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"
								      fill="none" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>
								<circle cx="7" cy="7" r="1.5" fill="currentColor"/>
							</svg>
						<?php else : ?>
							<svg viewBox="0 0 24 24" aria-hidden="true">
								<rect x="3" y="3" width="7" height="7" rx="2" fill="none" stroke="currentColor" stroke-width="2"/>
								<rect x="14" y="3" width="7" height="7" rx="2" fill="none" stroke="currentColor" stroke-width="2"/>
								<rect x="3" y="14" width="7" height="7" rx="2" fill="none" stroke="currentColor" stroke-width="2"/>
								<rect x="14" y="14" width="7" height="7" rx="2" fill="none" stroke="currentColor" stroke-width="2"/>
							</svg>
						<?php endif; ?>
						<span><?php echo esc_html( $term->name ); ?></span>
					</span>
					<span class="ez-bnav-cat-count"><?php echo esc_html( number_format_i18n( $term->count ) ); ?></span>
				</a>
			</li>
		<?php endforeach; ?>
	</ul>
	<?php
	return ob_get_clean();
}

/* ============================================================
 * 3) AJAX cart count
 * ============================================================ */
add_action( 'wp_ajax_ez_bnav_cart_count', 'ez_bnav_ajax_cart_count' );
add_action( 'wp_ajax_nopriv_ez_bnav_cart_count', 'ez_bnav_ajax_cart_count' );
function ez_bnav_ajax_cart_count() {
	check_ajax_referer( 'ez_bnav_nonce', 'nonce' );
	$count = ( function_exists( 'WC' ) && WC()->cart ) ? WC()->cart->get_cart_contents_count() : 0;
	wp_send_json_success( array( 'count' => (int) $count ) );
}
