<?php
/**
 * ورود / ثبت‌نام مشتری
 * v23 — No Eye Badge · Clear Inputs · 850px Panel
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$core = defined( 'EZLAUTH_FRONTEND_DIR' ) ? EZLAUTH_FRONTEND_DIR . 'pages/login.php' : '';
if ( ! $core || ! is_readable( $core ) ) {
	echo '<p style="text-align:center;padding:40px;font-family:Tahoma,sans-serif;">قالب ورود یافت نشد.</p>';
	return;
}

$primary = '#031f8a';
if ( class_exists( 'EzLens_Auth_Settings' ) ) {
	$c = EzLens_Auth_Settings::get( 'primary_color' );
	if ( is_string( $c ) && $c !== '' ) {
		$primary = $c;
	}
}
?>
<style id="ezlens-login-v23">
/* ============================================================
   EzLens Login — v23
   ============================================================ */

.min-auth-wrapper,
.min-auth-wrapper * {
	box-sizing: border-box !important;
	-webkit-tap-highlight-color: transparent;
}

/* ============================================================
   1) WRAPPER
   ============================================================ */
.min-auth-wrapper {
	isolation: isolate !important;
	position: relative !important;
	display: flex !important;
	flex-direction: column !important;
	align-items: center !important;
	justify-content: center !important;

	width: 100% !important;
	max-width: 100% !important;
	min-height: 80vh !important;
	margin: 0 !important;
	padding: 40px 16px !important;

	font-family: Tahoma, IRANYekan, "Vazirmatn", sans-serif !important;
	color: #0f172a !important;
	background: transparent !important;
	overflow: visible !important;
}

body.ezlens-login-active,
body.ezlens-login-active .site-content,
body.ezlens-login-active .content-area,
body.ezlens-login-active #main,
body.ezlens-login-active .main-page-wrapper,
body.ezlens-login-active .container,
body.ezlens-login-active .row {
	overflow-x: hidden !important;
	max-width: 100% !important;
	padding-left: 0 !important;
	padding-right: 0 !important;
	margin-left: 0 !important;
	margin-right: 0 !important;
}

/* ============================================================
   2) PANEL — 850px شیشه‌ای
   ============================================================ */
.min-auth-panel {
	position: relative !important;
	z-index: 2 !important;
	width: 100% !important;
	max-width: 850px !important;
	margin: 0 auto !important;
	padding: 44px 40px 40px !important;

	background: linear-gradient(155deg, rgba(255,255,255,.85) 0%, rgba(240,245,255,.78) 50%, rgba(250,245,255,.82) 100%) !important;
	border: 1px solid rgba(255,255,255,.95) !important;
	border-radius: 32px !important;
	backdrop-filter: blur(32px) saturate(200%) !important;
	-webkit-backdrop-filter: blur(32px) saturate(200%) !important;
	box-shadow:
		0 50px 120px rgba(30,64,175,.18),
		0 20px 40px rgba(30,64,175,.08),
		0 4px 12px rgba(15,23,42,.04),
		inset 0 1px 0 rgba(255,255,255,1),
		inset 0 -1px 0 rgba(255,255,255,.5) !important;
	overflow: hidden !important;
	animation: ezFadeUp .6s cubic-bezier(.2,.7,.3,1) both !important;
}

/* نوار رنگی بالای پنل */
.min-auth-panel::before {
	content: "" !important;
	position: absolute !important;
	top: 0 !important;
	left: 0 !important;
	right: 0 !important;
	height: 4px !important;
	background: linear-gradient(90deg, #06b6d4, #3b82f6, #8b5cf6, #ec4899) !important;
	background-size: 300% 100% !important;
	animation: ezTopBar 6s linear infinite !important;
	z-index: 3 !important;
}
@keyframes ezTopBar {
	0% { background-position: 0% 50%; }
	100% { background-position: 300% 50%; }
}

/* درخشش شیشه */
.min-auth-panel::after {
	content: "" !important;
	position: absolute !important;
	top: -30% !important;
	right: -10% !important;
	width: 55% !important;
	height: 160% !important;
	background: linear-gradient(120deg, rgba(255,255,255,.5) 0%, transparent 55%) !important;
	transform: rotate(15deg) !important;
	pointer-events: none !important;
	z-index: 1 !important;
	opacity: .55 !important;
}

/* ============================================================
   3) CONTENT — 520px وسط‌چین داخل پنل
   ============================================================ */
.min-auth-panel > .min-auth-logo,
.min-auth-panel > .min-auth-tabs,
.min-auth-panel > .min-auth-message,
.min-auth-panel > .min-auth-form,
.min-auth-panel > .ez-waiting-screen,
.min-auth-panel > .min-auth-empty {
	position: relative !important;
	z-index: 2 !important;
	width: 100% !important;
	max-width: 520px !important;
	margin-left: auto !important;
	margin-right: auto !important;
}

/* ============================================================
   4) TITLE (بدون Eye Badge)
   ============================================================ */
.min-auth-logo {
	text-align: center !important;
	margin: 0 auto 28px !important;
	padding-bottom: 26px !important;
	border-bottom: 1px solid rgba(226,232,240,.6) !important;
	position: relative !important;
	animation: ezFadeUp .55s cubic-bezier(.2,.7,.3,1) both !important;
}
.min-auth-logo::after {
	content: "" !important;
	position: absolute !important;
	bottom: -1px !important;
	left: 50% !important;
	transform: translateX(-50%) !important;
	width: 80px !important;
	height: 2px !important;
	background: linear-gradient(90deg, transparent, #3b82f6, transparent) !important;
	border-radius: 2px !important;
}
.min-auth-logo h1 {
	display: block !important;
	margin: 0 0 10px !important;
	font-size: 1.85rem !important;
	font-weight: 800 !important;
	line-height: 1.3 !important;
	letter-spacing: -.02em !important;
	background: linear-gradient(135deg, #1e40af 0%, #0891b2 50%, #7c3aed 100%) !important;
	-webkit-background-clip: text !important;
	background-clip: text !important;
	-webkit-text-fill-color: transparent !important;
	color: #1e40af !important;
}
.min-auth-logo .subtitle {
	display: block !important;
	color: #64748b !important;
	font-size: 13.5px !important;
	line-height: 1.7 !important;
	font-weight: 500 !important;
}

/* ============================================================
   5) TABS
   ============================================================ */
.min-auth-tabs {
	display: grid !important;
	grid-template-columns: repeat(3, 1fr) !important;
	gap: 6px !important;
	padding: 6px !important;
	margin: 0 auto 28px !important;
	width: 100% !important;
	background: rgba(255,255,255,.6) !important;
	border: 1px solid rgba(255,255,255,.9) !important;
	border-radius: 16px !important;
	backdrop-filter: blur(16px) saturate(180%) !important;
	-webkit-backdrop-filter: blur(16px) saturate(180%) !important;
	box-shadow:
		0 8px 24px rgba(30,64,175,.08),
		inset 0 1px 0 rgba(255,255,255,.95) !important;
}
.min-auth-tab {
	position: relative !important;
	display: flex !important;
	flex-direction: row !important;
	align-items: center !important;
	justify-content: center !important;
	gap: 7px !important;
	padding: 13px 10px !important;
	margin: 0 !important;
	border: none !important;
	border-radius: 11px !important;
	background: transparent !important;
	color: #64748b !important;
	font-family: inherit !important;
	font-size: 13px !important;
	font-weight: 700 !important;
	cursor: pointer !important;
	transition: all .4s cubic-bezier(.4,0,.2,1) !important;
	white-space: nowrap !important;
	overflow: hidden !important;
}
.min-auth-tab svg {
	width: 16px !important;
	height: 16px !important;
	stroke: currentColor !important;
	fill: none !important;
	stroke-width: 2 !important;
	flex-shrink: 0 !important;
	transition: transform .5s cubic-bezier(.34,1.4,.64,1) !important;
}
.min-auth-tab.active {
	background: linear-gradient(135deg, #3b82f6 0%, #1e40af 100%) !important;
	color: #ffffff !important;
	box-shadow:
		0 10px 24px rgba(30,64,175,.35),
		inset 0 1px 0 rgba(255,255,255,.3) !important;
}
.min-auth-tab.active svg { animation: ezTabPulse 2.8s ease-in-out infinite !important; }
.min-auth-tab:not(.active):hover {
	background: rgba(59,130,246,.12) !important;
	color: #1e40af !important;
}
.min-auth-tab:not(.active):hover svg {
	transform: scale(1.18) rotate(-10deg) !important;
}
@keyframes ezTabPulse {
	0%, 100% { transform: scale(1.1); }
	50% { transform: scale(1.22); }
}

/* ============================================================
   6) FORM
   ============================================================ */
.min-auth-form {
	display: none !important;
	flex-direction: column !important;
	height: auto !important;
	margin: 0 auto !important;
	width: 100% !important;
}
.min-auth-form.active {
	display: flex !important;
	animation: ezFormIn .5s cubic-bezier(.2,.7,.3,1) both !important;
}
@keyframes ezFormIn {
	from { opacity: 0; transform: translateY(12px); }
	to { opacity: 1; transform: translateY(0); }
}

/* ============================================================
   7) FIELDS
   ============================================================ */
.min-auth-input-group {
	display: flex !important;
	flex-direction: column !important;
	width: 100% !important;
	margin: 0 0 18px !important;
	position: relative !important;
}
.min-auth-input-group label {
	display: block !important;
	width: 100% !important;
	margin: 0 0 8px !important;
	font-size: 12.5px !important;
	font-weight: 700 !important;
	color: #334155 !important;
	text-align: right !important;
	transition: color .3s ease !important;
}
.min-auth-input-group:focus-within label { color: #1e40af !important; }
.min-auth-input-group.has-error label { color: #dc2626 !important; }
.min-auth-input-group.has-success label { color: #059669 !important; }

.ez-input-wrap {
	position: relative !important;
	display: block !important;
	width: 100% !important;
}
.ez-input-icon {
	position: absolute !important;
	left: 16px !important;
	top: 50% !important;
	transform: translateY(-50%) !important;
	width: 20px !important;
	height: 20px !important;
	color: #64748b !important;
	pointer-events: none !important;
	display: flex !important;
	align-items: center !important;
	justify-content: center !important;
	transition: color .3s ease, transform .3s ease !important;
	z-index: 2 !important;
}
.ez-input-icon svg {
	width: 20px !important;
	height: 20px !important;
	stroke: currentColor !important;
	fill: none !important;
	stroke-width: 2 !important;
	stroke-linecap: round !important;
	stroke-linejoin: round !important;
}
.ez-input-wrap:hover .ez-input-icon {
	color: #1e40af !important;
	transform: translateY(-50%) scale(1.1) !important;
}
.ez-input-wrap:focus-within .ez-input-icon { color: #1e40af !important; }

/* input اصلی — واضح‌تر */
.min-auth-input-group input[type="tel"],
.min-auth-input-group input[type="text"],
.min-auth-input-group input[type="email"],
.min-auth-input-group input[type="password"],
.min-auth-input-group input:not([type="hidden"]):not([type="checkbox"]) {
	display: block !important;
	width: 100% !important;
	max-width: 100% !important;
	margin: 0 !important;
	padding: 15px 52px 15px 52px !important;
	border: 1.5px solid #d5dce7 !important;
	border-radius: 14px !important;
	background: #f7f9fc !important;
	color: #0f172a !important;
	font-size: 14.5px !important;
	font-family: inherit !important;
	line-height: 1.4 !important;
	font-weight: 600 !important;
	transition: all .3s cubic-bezier(.4,0,.2,1) !important;
	box-shadow: inset 0 1px 2px rgba(15,23,42,.04) !important;
}
.min-auth-input-group input::placeholder {
	color: #94a3b8 !important;
	font-weight: 500 !important;
	font-size: 13.5px !important;
}
.min-auth-input-group input[type="email"],
.min-auth-input-group input[type="tel"],
.min-auth-input-group input[type="password"],
.password-wrap input {
	direction: ltr !important;
	text-align: left !important;
	font-family: "Inter", -apple-system, Tahoma, sans-serif !important;
	letter-spacing: .02em !important;
}
.min-auth-input-group input[type="email"]::placeholder,
.min-auth-input-group input[type="tel"]::placeholder,
.min-auth-input-group input[type="password"]::placeholder,
.password-wrap input::placeholder {
	text-align: left !important;
	direction: ltr !important;
}

/* Hover — جذاب‌تر */
.min-auth-input-group input:hover {
	border-color: #93b4e8 !important;
	background: #ffffff !important;
	box-shadow:
		inset 0 1px 2px rgba(15,23,42,.03),
		0 4px 12px rgba(59,130,246,.08) !important;
}
/* Focus */
.min-auth-input-group input:focus {
	border-color: #3b82f6 !important;
	background: #ffffff !important;
	box-shadow:
		0 0 0 4px rgba(59,130,246,.15),
		0 8px 20px rgba(59,130,246,.12) !important;
	outline: none !important;
}
.min-auth-input-group.has-error input {
	border-color: #f87171 !important;
	background: #fef2f2 !important;
	animation: ezShake .5s cubic-bezier(.36,.07,.19,.97) both !important;
}
.min-auth-input-group.has-error .ez-input-icon { color: #ef4444 !important; }
.min-auth-input-group.has-success input {
	border-color: #10b981 !important;
	background: #f0fdf4 !important;
}
.min-auth-input-group.has-success .ez-input-icon { color: #10b981 !important; }
@keyframes ezShake {
	10%, 90% { transform: translateX(-2px); }
	20%, 80% { transform: translateX(4px); }
	30%, 50%, 70% { transform: translateX(-7px); }
	40%, 60% { transform: translateX(7px); }
}

/* ============================================================
   8) Messages
   ============================================================ */
.ez-field-error,
.ez-field-success {
	display: none !important;
	margin: 8px 2px 0 !important;
	font-size: 12px !important;
	font-weight: 600 !important;
	line-height: 1.6 !important;
	text-align: right !important;
	align-items: center !important;
	gap: 6px !important;
	flex-direction: row-reverse !important;
	justify-content: flex-end !important;
}
.min-auth-input-group.has-error .ez-field-error {
	display: flex !important;
	color: #dc2626 !important;
	animation: ezErrSlide .35s cubic-bezier(.2,.7,.3,1) both !important;
}
.min-auth-input-group.has-success .ez-field-success {
	display: flex !important;
	color: #059669 !important;
	animation: ezErrSlide .35s cubic-bezier(.2,.7,.3,1) both !important;
}
@keyframes ezErrSlide {
	from { opacity: 0; transform: translateY(-5px); }
	to { opacity: 1; transform: translateY(0); }
}
.ez-msg-icon {
	display: inline-flex !important;
	align-items: center !important;
	justify-content: center !important;
	width: 16px !important;
	height: 16px !important;
	flex-shrink: 0 !important;
}
.ez-msg-icon svg {
	width: 16px !important;
	height: 16px !important;
	stroke: currentColor !important;
	stroke-width: 2.2 !important;
	fill: none !important;
	stroke-linecap: round !important;
	stroke-linejoin: round !important;
}
.ez-msg-text { flex: 1 !important; text-align: right !important; }

.ez-inline-svg {
	display: inline-flex !important;
	align-items: center !important;
	justify-content: center !important;
	width: 1.1em !important;
	height: 1.1em !important;
	vertical-align: -0.18em !important;
	margin: 0 3px !important;
	flex-shrink: 0 !important;
}
.ez-inline-svg svg {
	width: 100% !important;
	height: 100% !important;
	stroke: currentColor !important;
	stroke-width: 2.2 !important;
	fill: none !important;
	stroke-linecap: round !important;
	stroke-linejoin: round !important;
}
.min-auth-message.is-success .ez-inline-svg { color: #047857 !important; }
.min-auth-message.is-error   .ez-inline-svg { color: #b91c1c !important; }
.min-auth-message.is-info    .ez-inline-svg { color: #1e40af !important; }

/* ============================================================
   9) Check / Cross
   ============================================================ */
.ez-input-check,
.ez-input-cross {
	position: absolute !important;
	right: 16px !important;
	top: 50% !important;
	transform: translateY(-50%) scale(.5) !important;
	width: 22px !important;
	height: 22px !important;
	opacity: 0 !important;
	transition: opacity .35s ease, transform .45s cubic-bezier(.34,1.4,.64,1) !important;
	pointer-events: none !important;
	z-index: 2 !important;
	border-radius: 50% !important;
	display: flex !important;
	align-items: center !important;
	justify-content: center !important;
}
.ez-input-check svg,
.ez-input-cross svg {
	width: 14px !important;
	height: 14px !important;
	stroke-width: 3 !important;
	fill: none !important;
	stroke-linecap: round !important;
	stroke-linejoin: round !important;
}
.ez-input-check { background: #d1fae5 !important; }
.ez-input-check svg { stroke: #059669 !important; }
.ez-input-cross { background: #fee2e2 !important; }
.ez-input-cross svg { stroke: #dc2626 !important; }
.min-auth-input-group.has-success .ez-input-check,
.min-auth-input-group.has-error .ez-input-cross {
	opacity: 1 !important;
	transform: translateY(-50%) scale(1) !important;
	animation: ezCheckPop .5s cubic-bezier(.34,1.4,.64,1) !important;
}
@keyframes ezCheckPop {
	0% { transform: translateY(-50%) scale(.3); opacity: 0; }
	60% { transform: translateY(-50%) scale(1.15); opacity: 1; }
	100% { transform: translateY(-50%) scale(1); opacity: 1; }
}

/* ============================================================
   10) Password — toggle سمت راست
   ============================================================ */
.password-wrap {
	position: relative !important;
	display: block !important;
	width: 100% !important;
}
.password-wrap input {
	padding-left: 52px !important;
	padding-right: 52px !important;
}
.toggle-password {
	position: absolute !important;
	right: 12px !important;
	left: auto !important;
	top: 50% !important;
	transform: translateY(-50%) !important;
	background: transparent !important;
	border: none !important;
	padding: 6px !important;
	cursor: pointer !important;
	color: #64748b !important;
	display: flex !important;
	align-items: center !important;
	justify-content: center !important;
	transition: all .3s ease !important;
	z-index: 3 !important;
	border-radius: 8px !important;
}
.toggle-password:hover {
	color: #1e40af !important;
	background: rgba(59,130,246,.1) !important;
}
.toggle-password svg {
	width: 18px !important;
	height: 18px !important;
	stroke: currentColor !important;
	fill: none !important;
	stroke-width: 2 !important;
}

/* ============================================================
   11) Captcha
   ============================================================ */
.min-auth-captcha {
	display: grid !important;
	grid-template-columns: auto auto auto 1fr auto !important;
	align-items: center !important;
	gap: 10px !important;
	width: 100% !important;
	margin: 0 0 18px !important;
	padding: 14px 16px !important;
	background: #f7f9fc !important;
	border: 1.5px solid #d5dce7 !important;
	border-radius: 14px !important;
	transition: all .3s ease !important;
}
.min-auth-captcha:focus-within {
	border-color: #3b82f6 !important;
	background: #ffffff !important;
	box-shadow: 0 0 0 4px rgba(59,130,246,.12) !important;
}
.min-auth-captcha.has-error {
	border-color: #f87171 !important;
	background: #fef2f2 !important;
	animation: ezShake .5s cubic-bezier(.36,.07,.19,.97) both !important;
}
.min-auth-captcha.has-success {
	border-color: #10b981 !important;
	background: #f0fdf4 !important;
}
.min-auth-captcha .captcha-label {
	font-size: 13px !important;
	font-weight: 700 !important;
	color: #334155 !important;
	white-space: nowrap !important;
}
.min-auth-captcha .captcha-numbers {
	display: inline-flex !important;
	align-items: center !important;
	gap: 6px !important;
	padding: 8px 14px !important;
	background: #ffffff !important;
	border: 1.5px solid #d5dce7 !important;
	border-radius: 10px !important;
	font-weight: 800 !important;
	font-size: 16px !important;
	color: #1e40af !important;
	font-family: "Inter", monospace !important;
	letter-spacing: .06em !important;
	user-select: none !important;
	white-space: nowrap !important;
}
.min-auth-captcha .captcha-input {
	width: 100% !important;
	max-width: 140px !important;
	padding: 10px 12px !important;
	text-align: center !important;
	border: 1.5px solid #d5dce7 !important;
	border-radius: 10px !important;
	background: #ffffff !important;
	color: #0f172a !important;
	font-weight: 800 !important;
	font-size: 16px !important;
	font-family: "Inter", monospace !important;
	direction: ltr !important;
	transition: all .25s ease !important;
	justify-self: end !important;
}
.min-auth-captcha .captcha-input:focus {
	border-color: #3b82f6 !important;
	outline: none !important;
	box-shadow: 0 0 0 3px rgba(59,130,246,.14) !important;
}
.captcha-reload {
	border: 1.5px solid #d5dce7 !important;
	background: #ffffff !important;
	border-radius: 10px !important;
	width: 40px !important;
	height: 40px !important;
	display: inline-flex !important;
	align-items: center !important;
	justify-content: center !important;
	cursor: pointer !important;
	color: #1e40af !important;
	transition: all .4s cubic-bezier(.34,1.4,.64,1) !important;
	justify-self: start !important;
}
.captcha-reload:hover {
	background: #f1f5f9 !important;
	transform: rotate(-30deg) scale(1.05) !important;
}
.captcha-reload svg {
	width: 18px !important;
	height: 18px !important;
	stroke: currentColor !important;
	fill: none !important;
	stroke-width: 2.2 !important;
}

@media (max-width: 600px) {
	.min-auth-captcha {
		grid-template-columns: auto auto auto auto !important;
		gap: 8px !important;
		padding: 12px 14px !important;
	}
	.min-auth-captcha .captcha-input {
		grid-column: 1 / -1 !important;
		max-width: 100% !important;
		width: 100% !important;
	}
	.captcha-reload {
		order: 10 !important;
		justify-self: end !important;
	}
}

/* ============================================================
   12) Button
   ============================================================ */
.min-auth-btn {
	position: relative !important;
	display: flex !important;
	align-items: center !important;
	justify-content: center !important;
	gap: 8px !important;
	width: 100% !important;
	margin: 12px 0 0 !important;
	padding: 16px 24px !important;
	border: none !important;
	border-radius: 14px !important;
	background: linear-gradient(135deg, #3b82f6 0%, #1e40af 50%, #1e3a8a 100%) !important;
	background-size: 200% 200% !important;
	color: #ffffff !important;
	font-size: 15px !important;
	font-weight: 800 !important;
	font-family: inherit !important;
	cursor: pointer !important;
	letter-spacing: .015em !important;
	box-shadow:
		0 14px 32px rgba(30,64,175,.4),
		0 4px 10px rgba(30,64,175,.25),
		inset 0 1px 0 rgba(255,255,255,.3) !important;
	transition: all .4s cubic-bezier(.4,0,.2,1) !important;
	overflow: hidden !important;
	animation: ezBtnShift 6s ease-in-out infinite !important;
}
@keyframes ezBtnShift {
	0%, 100% { background-position: 0% 50%; }
	50% { background-position: 100% 50%; }
}
.min-auth-btn::before {
	content: "" !important;
	position: absolute !important;
	inset: 0 !important;
	background: linear-gradient(110deg, transparent 25%, rgba(255,255,255,.5) 50%, transparent 75%) !important;
	transform: translateX(-100%) !important;
	transition: transform .9s cubic-bezier(.4,0,.2,1) !important;
	pointer-events: none !important;
}
.min-auth-btn:hover::before { transform: translateX(100%) !important; }
.min-auth-btn:hover {
	transform: translateY(-2px) !important;
	box-shadow:
		0 22px 48px rgba(30,64,175,.5),
		0 8px 16px rgba(30,64,175,.3),
		inset 0 1px 0 rgba(255,255,255,.4) !important;
}
.min-auth-btn:active { transform: translateY(0) scale(.985) !important; }
.min-auth-btn:disabled { opacity: .75 !important; cursor: not-allowed !important; }
.min-auth-btn.is-sending {
	background: linear-gradient(135deg, #1e40af 0%, #0a1447 100%) !important;
	animation: none !important;
	cursor: wait !important;
}
.min-auth-btn.is-sending::before { display: none !important; }
.min-auth-btn svg {
	width: 18px !important;
	height: 18px !important;
	stroke: currentColor !important;
	fill: none !important;
	stroke-width: 2 !important;
}
.ez-dots {
	display: inline-flex !important;
	gap: 4px !important;
	align-items: center !important;
	margin-right: 6px !important;
}
.ez-dots span {
	width: 5px !important;
	height: 5px !important;
	border-radius: 50% !important;
	background: #ffffff !important;
	display: inline-block !important;
	animation: ezDotBounce 1.4s ease-in-out infinite both !important;
}
.ez-dots span:nth-child(1) { animation-delay: 0s; }
.ez-dots span:nth-child(2) { animation-delay: .2s; }
.ez-dots span:nth-child(3) { animation-delay: .4s; }
@keyframes ezDotBounce {
	0%, 80%, 100% { transform: scale(.6); opacity: .4; }
	40% { transform: scale(1.2); opacity: 1; }
}

/* ============================================================
   13) Links
   ============================================================ */
.min-auth-back {
	display: inline-flex !important;
	align-items: center !important;
	justify-content: center !important;
	gap: 6px !important;
	width: 100% !important;
	margin-top: 16px !important;
	padding: 13px !important;
	color: #64748b !important;
	font-size: 13px !important;
	font-weight: 700 !important;
	text-decoration: none !important;
	background: rgba(255,255,255,.7) !important;
	border: 1px solid #d5dce7 !important;
	transition: all .3s ease !important;
	cursor: pointer !important;
	border-radius: 13px !important;
}
.min-auth-back:hover {
	color: #1e40af !important;
	background: #ffffff !important;
	border-color: #93b4e8 !important;
}
.min-auth-back svg {
	width: 16px !important;
	height: 16px !important;
	stroke: currentColor !important;
	fill: none !important;
	transition: transform .3s ease !important;
}
.min-auth-back:hover svg { transform: translateX(4px) !important; }

.min-auth-form label[for],
.min-auth-form .remember,
.min-auth-extra {
	display: flex !important;
	align-items: center !important;
	gap: 9px !important;
	font-size: 13px !important;
	color: #475569 !important;
	margin-bottom: 18px !important;
	font-weight: 500 !important;
	cursor: pointer !important;
	user-select: none !important;
	justify-content: space-between !important;
}
.min-auth-remember {
	display: inline-flex !important;
	align-items: center !important;
	gap: 8px !important;
	cursor: pointer !important;
}
.min-auth-form input[type="checkbox"] {
	appearance: none !important;
	-webkit-appearance: none !important;
	width: 19px !important;
	height: 19px !important;
	border: 1.5px solid #cbd5e1 !important;
	border-radius: 6px !important;
	background: #ffffff !important;
	cursor: pointer !important;
	position: relative !important;
	transition: all .3s ease !important;
	flex-shrink: 0 !important;
}
.min-auth-form input[type="checkbox"]:checked {
	background: linear-gradient(135deg, #3b82f6, #1e40af) !important;
	border-color: transparent !important;
}
.min-auth-form input[type="checkbox"]:checked::after {
	content: "" !important;
	position: absolute !important;
	left: 6px !important;
	top: 2px !important;
	width: 5px !important;
	height: 10px !important;
	border: solid #ffffff !important;
	border-width: 0 2.5px 2.5px 0 !important;
	transform: rotate(45deg) !important;
}
.min-auth-forgot-link {
	display: inline-flex !important;
	align-items: center !important;
	justify-content: center !important;
	gap: 6px !important;
	padding: 8px 16px !important;
	margin: 0 !important;
	border: 1.5px solid rgba(59,130,246,.3) !important;
	border-radius: 10px !important;
	background: rgba(255,255,255,.7) !important;
	color: #1e40af !important;
	font-weight: 700 !important;
	font-size: 12.5px !important;
	cursor: pointer !important;
	font-family: inherit !important;
	transition: all .35s ease !important;
}
.min-auth-forgot-link svg {
	width: 14px !important;
	height: 14px !important;
	stroke: currentColor !important;
	fill: none !important;
	stroke-width: 2 !important;
}
.min-auth-forgot-link:hover {
	background: linear-gradient(135deg, #3b82f6, #1e40af) !important;
	border-color: transparent !important;
	color: #ffffff !important;
	box-shadow: 0 8px 20px rgba(30,64,175,.35) !important;
}

/* ============================================================
   14) Alert
   ============================================================ */
.min-auth-message {
	display: none !important;
	padding: 14px 18px !important;
	margin: 0 auto 18px !important;
	border-radius: 13px !important;
	font-size: 13px !important;
	font-weight: 700 !important;
	text-align: right !important;
	line-height: 1.6 !important;
	align-items: center !important;
	gap: 10px !important;
	flex-direction: row-reverse !important;
	justify-content: flex-start !important;
	width: 100% !important;
}
.min-auth-message.is-show,
.min-auth-message[style*="block"]:not([style*="none"]),
.min-auth-message:not(:empty) {
	display: flex !important;
	animation: ezAlertIn .4s cubic-bezier(.2,.7,.3,1) both !important;
}
@keyframes ezAlertIn {
	0% { opacity: 0; transform: translateY(-8px) scale(.97); }
	100% { opacity: 1; transform: translateY(0) scale(1); }
}
.min-auth-message .ez-msg-icon { width: 20px !important; height: 20px !important; }
.min-auth-message .ez-msg-icon svg { width: 20px !important; height: 20px !important; }
.min-auth-message .ez-msg-text { flex: 1 !important; text-align: right !important; }
.min-auth-message.is-error,
.min-auth-message.error {
	background: #fef2f2 !important;
	color: #b91c1c !important;
	border: 1px solid #fecaca !important;
}
.min-auth-message.is-success,
.min-auth-message.success {
	background: #f0fdf4 !important;
	color: #047857 !important;
	border: 1px solid #bbf7d0 !important;
}
.min-auth-message.is-info,
.min-auth-message.info {
	background: #eff6ff !important;
	color: #1e40af !important;
	border: 1px solid #bfdbfe !important;
}

/* ============================================================
   15) Waiting Screen
   ============================================================ */
.ez-waiting-screen {
	display: none !important;
	flex-direction: column !important;
	align-items: center !important;
	justify-content: center !important;
	padding: 12px 0 4px !important;
	text-align: center !important;
	position: relative !important;
	z-index: 3 !important;
	height: auto !important;
	background: transparent !important;
	width: 100% !important;
}
.ez-waiting-screen.active { display: flex !important; }
.min-auth-wrapper:not(.is-waiting) .ez-waiting-screen { display: none !important; }
.min-auth-wrapper.is-waiting .min-auth-tabs,
.min-auth-wrapper.is-waiting .min-auth-form { display: none !important; }
.min-auth-wrapper.is-waiting .min-auth-logo {
	margin-bottom: 22px !important;
	padding-bottom: 20px !important;
}
.ez-radar {
	position: relative !important;
	width: 140px !important;
	height: 140px !important;
	margin: 0 auto 26px !important;
	display: flex !important;
	align-items: center !important;
	justify-content: center !important;
}
.ez-radar .ez-radar-circle {
	position: absolute !important;
	inset: 0 !important;
	border-radius: 50% !important;
	border: 2px solid rgba(59,130,246,.5) !important;
	opacity: 0 !important;
	animation: ezRadarPulse 3s ease-out infinite !important;
}
.ez-radar .ez-radar-circle:nth-child(1) { animation-delay: 0s; }
.ez-radar .ez-radar-circle:nth-child(2) { animation-delay: 1s; }
.ez-radar .ez-radar-circle:nth-child(3) { animation-delay: 2s; }
@keyframes ezRadarPulse {
	0% { transform: scale(.4); opacity: 0; border-color: rgba(59,130,246,.7); }
	40% { opacity: .9; }
	100% { transform: scale(1.35); opacity: 0; border-color: rgba(139,92,246,.1); }
}
.ez-radar .ez-radar-center {
	position: relative !important;
	width: 76px !important;
	height: 76px !important;
	border-radius: 24px !important;
	background: linear-gradient(135deg, #06b6d4 0%, #3b82f6 50%, #8b5cf6 100%) !important;
	display: flex !important;
	align-items: center !important;
	justify-content: center !important;
	box-shadow:
		0 18px 42px rgba(99,102,241,.45),
		0 4px 12px rgba(59,130,246,.3),
		inset 0 1px 0 rgba(255,255,255,.5) !important;
	z-index: 2 !important;
	animation: ezCenterFloat 3s ease-in-out infinite !important;
}
@keyframes ezCenterFloat {
	0%, 100% { transform: translateY(0) rotate(0deg); }
	50% { transform: translateY(-6px) rotate(-3deg); }
}
.ez-radar .ez-radar-center svg {
	width: 36px !important;
	height: 36px !important;
	color: #ffffff !important;
	stroke: currentColor !important;
	fill: none !important;
	stroke-width: 1.8 !important;
}
.ez-waiting-title {
	font-size: 19px !important;
	font-weight: 800 !important;
	color: #1e40af !important;
	margin: 0 0 8px !important;
	line-height: 1.5 !important;
}
.ez-waiting-subtitle {
	font-size: 13.5px !important;
	color: #64748b !important;
	margin: 0 0 18px !important;
	line-height: 1.7 !important;
	font-weight: 500 !important;
}
.ez-waiting-phone {
	display: inline-block !important;
	padding: 9px 20px !important;
	background: rgba(59,130,246,.1) !important;
	border: 1px solid rgba(59,130,246,.25) !important;
	border-radius: 12px !important;
	font-family: "Inter", monospace !important;
	font-weight: 700 !important;
	color: #1e40af !important;
	direction: ltr !important;
	letter-spacing: .06em !important;
	font-size: 15px !important;
	margin-bottom: 22px !important;
}
.ez-timer {
	display: inline-flex !important;
	align-items: center !important;
	justify-content: center !important;
	gap: 10px !important;
	margin: 0 0 22px !important;
	padding: 11px 22px !important;
	background: #ffffff !important;
	border: 1.5px solid #d5dce7 !important;
	border-radius: 14px !important;
}
.ez-timer-label {
	font-size: 12.5px !important;
	color: #64748b !important;
	font-weight: 600 !important;
}
.ez-timer-value {
	font-family: "Inter", monospace !important;
	font-size: 20px !important;
	font-weight: 800 !important;
	color: #1e40af !important;
	letter-spacing: .06em !important;
	direction: ltr !important;
	min-width: 62px !important;
	text-align: center !important;
}
.ez-timer-value.is-low {
	color: #dc2626 !important;
	animation: ezTimerPulse 1s ease-in-out infinite !important;
}
@keyframes ezTimerPulse {
	0%, 100% { transform: scale(1); }
	50% { transform: scale(1.12); }
}
.ez-waiting-tips {
	display: flex !important;
	align-items: center !important;
	justify-content: center !important;
	min-height: 44px !important;
	margin: 0 0 22px !important;
	padding: 0 8px !important;
}
.ez-tip {
	display: none !important;
	font-size: 13px !important;
	color: #64748b !important;
	line-height: 1.7 !important;
	font-weight: 500 !important;
}
.ez-tip.active {
	display: block !important;
	animation: ezTipIn .5s cubic-bezier(.2,.7,.3,1) both !important;
}
@keyframes ezTipIn {
	from { opacity: 0; transform: translateY(8px); }
	to { opacity: 1; transform: translateY(0); }
}
.ez-code-inputs {
	display: flex !important;
	flex-direction: row !important;
	direction: ltr !important;
	gap: 10px !important;
	justify-content: center !important;
	margin: 0 0 24px !important;
}
.ez-code-inputs input {
	width: 54px !important;
	height: 64px !important;
	text-align: center !important;
	font-size: 26px !important;
	font-weight: 800 !important;
	font-family: "Inter", monospace !important;
	color: #1e40af !important;
	background: #ffffff !important;
	border: 1.5px solid #d5dce7 !important;
	border-radius: 14px !important;
	padding: 0 !important;
	transition: all .3s ease !important;
	caret-color: #1e40af !important;
}
.ez-code-inputs input:focus {
	border-color: #3b82f6 !important;
	background: #ffffff !important;
	box-shadow: 0 0 0 4px rgba(59,130,246,.18) !important;
	outline: none !important;
	transform: translateY(-3px) !important;
}
.ez-code-inputs input.is-filled {
	border-color: rgba(16,185,129,.6) !important;
	background: #f0fdf4 !important;
	color: #059669 !important;
	animation: ezCodePop .3s cubic-bezier(.34,1.4,.64,1) !important;
}
@keyframes ezCodePop {
	0% { transform: scale(1); }
	50% { transform: scale(1.1); }
	100% { transform: scale(1); }
}
.ez-waiting-actions {
	display: flex !important;
	flex-direction: column !important;
	gap: 10px !important;
	width: 100% !important;
	max-width: 400px !important;
	margin: 0 auto !important;
}
.ez-resend-btn {
	display: flex !important;
	align-items: center !important;
	justify-content: center !important;
	gap: 8px !important;
	width: 100% !important;
	padding: 14px 20px !important;
	border: 1.5px solid rgba(59,130,246,.3) !important;
	border-radius: 13px !important;
	background: #ffffff !important;
	color: #1e40af !important;
	font-family: inherit !important;
	font-size: 14px !important;
	font-weight: 700 !important;
	cursor: pointer !important;
	transition: all .35s ease !important;
}
.ez-resend-btn svg {
	width: 16px !important;
	height: 16px !important;
	stroke: currentColor !important;
	fill: none !important;
	stroke-width: 2 !important;
	transition: transform .4s cubic-bezier(.34,1.4,.64,1) !important;
}
.ez-resend-btn:hover:not(:disabled) {
	background: linear-gradient(135deg, #3b82f6, #1e40af) !important;
	border-color: transparent !important;
	color: #ffffff !important;
	transform: translateY(-2px) !important;
	box-shadow: 0 10px 24px rgba(30,64,175,.3) !important;
}
.ez-resend-btn:hover:not(:disabled) svg { transform: rotate(-180deg) !important; }
.ez-resend-btn:disabled { opacity: .45 !important; cursor: not-allowed !important; }
.ez-back-btn {
	display: flex !important;
	align-items: center !important;
	justify-content: center !important;
	gap: 6px !important;
	width: 100% !important;
	padding: 12px !important;
	border: none !important;
	background: transparent !important;
	color: #64748b !important;
	font-family: inherit !important;
	font-size: 13px !important;
	font-weight: 700 !important;
	cursor: pointer !important;
	transition: all .3s ease !important;
	border-radius: 10px !important;
}
.ez-back-btn svg {
	width: 16px !important;
	height: 16px !important;
	stroke: currentColor !important;
	fill: none !important;
	stroke-width: 2 !important;
	transition: transform .3s ease !important;
}
.ez-back-btn:hover {
	color: #1e40af !important;
	background: rgba(59,130,246,.08) !important;
}
.ez-back-btn:hover svg { transform: translateX(4px) !important; }

/* ============================================================
   16) Forgot Overlay
   ============================================================ */
.min-auth-forgot-overlay {
	position: fixed !important;
	inset: 0 !important;
	z-index: 99999 !important;
	background: rgba(15,23,42,.5) !important;
	backdrop-filter: blur(12px) !important;
	-webkit-backdrop-filter: blur(12px) !important;
	display: none !important;
	align-items: center !important;
	justify-content: center !important;
	padding: 16px !important;
	margin: 0 !important;
}
.min-auth-forgot-overlay.active {
	display: flex !important;
	animation: ezFadeIn .25s ease both !important;
}
@keyframes ezFadeIn {
	from { opacity: 0; }
	to { opacity: 1; }
}
.min-auth-forgot-box {
	position: relative !important;
	width: 100% !important;
	max-width: 420px !important;
	padding: 32px 30px !important;
	background: rgba(255,255,255,.95) !important;
	border: 1px solid rgba(255,255,255,.95) !important;
	border-radius: 26px !important;
	backdrop-filter: blur(30px) saturate(200%) !important;
	-webkit-backdrop-filter: blur(30px) saturate(200%) !important;
	box-shadow:
		0 40px 90px rgba(15,23,42,.4),
		inset 0 1px 0 rgba(255,255,255,.95) !important;
	animation: ezPopIn .4s cubic-bezier(.34,1.4,.64,1) both !important;
	display: flex !important;
	flex-direction: column !important;
}
@keyframes ezPopIn {
	0% { opacity: 0; transform: scale(.92) translateY(12px); }
	100% { opacity: 1; transform: scale(1) translateY(0); }
}
.min-auth-forgot-box h3 {
	margin: 0 0 6px !important;
	color: #1e40af !important;
	font-size: 1.2rem !important;
	font-weight: 800 !important;
	text-align: center !important;
}
.min-auth-forgot-box p {
	color: #64748b !important;
	font-size: 13px !important;
	text-align: center !important;
	margin: 0 0 18px !important;
	line-height: 1.7 !important;
}
.min-auth-forgot-close {
	position: absolute !important;
	top: 14px !important;
	left: 14px !important;
	width: 34px !important;
	height: 34px !important;
	border: 1px solid #d5dce7 !important;
	border-radius: 10px !important;
	background: #ffffff !important;
	cursor: pointer !important;
	display: flex !important;
	align-items: center !important;
	justify-content: center !important;
	color: #64748b !important;
	transition: all .25s !important;
}
.min-auth-forgot-close:hover {
	background: #fef2f2 !important;
	color: #ef4444 !important;
	border-color: #fecaca !important;
}
.min-auth-forgot-close span { font-size: 22px !important; line-height: 1 !important; }
.min-auth-forgot-tabs {
	display: grid !important;
	grid-template-columns: 1fr 1fr !important;
	gap: 5px !important;
	margin: 8px 0 18px !important;
	padding: 5px !important;
	background: #f1f5f9 !important;
	border: 1px solid #d5dce7 !important;
	border-radius: 12px !important;
}
.min-auth-forgot-tab {
	display: flex !important;
	align-items: center !important;
	justify-content: center !important;
	gap: 6px !important;
	padding: 10px !important;
	border: none !important;
	border-radius: 9px !important;
	background: transparent !important;
	font-weight: 700 !important;
	font-size: 12.5px !important;
	cursor: pointer !important;
	font-family: inherit !important;
	color: #64748b !important;
	transition: all .35s ease !important;
}
.min-auth-forgot-tab svg { width: 15px !important; height: 15px !important; stroke: currentColor !important; fill: none !important; }
.min-auth-forgot-tab.active {
	background: linear-gradient(135deg, #3b82f6, #1e40af) !important;
	color: #ffffff !important;
	box-shadow: 0 6px 14px rgba(30,64,175,.3) !important;
}
.min-auth-forgot-form {
	display: none !important;
	flex-direction: column !important;
	width: 100% !important;
}
.min-auth-forgot-form.active {
	display: flex !important;
	animation: ezFadeIn .3s ease both !important;
}
.min-auth-forgot-btn {
	display: block !important;
	width: 100% !important;
	padding: 14px !important;
	margin-top: 8px !important;
	border: none !important;
	border-radius: 12px !important;
	background: linear-gradient(135deg, #3b82f6, #1e40af) !important;
	color: #ffffff !important;
	font-weight: 800 !important;
	font-family: inherit !important;
	font-size: 14px !important;
	cursor: pointer !important;
	box-shadow: 0 10px 24px rgba(30,64,175,.35) !important;
	transition: all .3s ease !important;
}
.min-auth-forgot-btn:hover { transform: translateY(-2px) !important; }
.min-auth-forgot-back {
	display: inline-flex !important;
	align-items: center !important;
	justify-content: center !important;
	gap: 6px !important;
	width: 100% !important;
	margin-top: 12px !important;
	padding: 10px !important;
	border: none !important;
	background: transparent !important;
	color: #64748b !important;
	font-weight: 700 !important;
	font-family: inherit !important;
	cursor: pointer !important;
	font-size: 13px !important;
	transition: color .25s !important;
	border-radius: 8px !important;
}
.min-auth-forgot-back svg { width: 15px !important; height: 15px !important; stroke: currentColor !important; fill: none !important; }
.min-auth-forgot-back:hover { color: #1e40af !important; background: rgba(59,130,246,.08) !important; }

/* ============================================================
   17) Animation
   ============================================================ */
@keyframes ezFadeUp {
	from { opacity: 0; transform: translateY(16px); }
	to { opacity: 1; transform: translateY(0); }
}

/* ============================================================
   18) Responsive
   ============================================================ */
@media (max-width: 890px) {
	.min-auth-panel {
		padding: 40px 28px 36px !important;
		border-radius: 28px !important;
	}
}
@media (max-width: 640px) {
	.min-auth-wrapper {
		padding: 24px 12px !important;
		min-height: 70vh !important;
	}
	.min-auth-panel {
		padding: 30px 20px !important;
		border-radius: 24px !important;
	}
	.min-auth-logo h1 { font-size: 1.5rem !important; }
	.min-auth-logo .subtitle { font-size: 12.5px !important; }
	.min-auth-tab {
		font-size: 12px !important;
		padding: 12px 6px !important;
		gap: 5px !important;
	}
	.min-auth-tab svg { width: 15px !important; height: 15px !important; }
	.min-auth-input-group input {
		padding: 14px 48px 14px 48px !important;
		font-size: 15px !important;
	}
	.password-wrap input { padding-left: 48px !important; padding-right: 48px !important; }
	.toggle-password { right: 10px !important; }
	.ez-radar { width: 115px !important; height: 115px !important; }
	.ez-radar .ez-radar-center { width: 62px !important; height: 62px !important; }
	.ez-radar .ez-radar-center svg { width: 28px !important; height: 28px !important; }
	.ez-code-inputs { gap: 7px !important; }
	.ez-code-inputs input {
		width: 46px !important;
		height: 56px !important;
		font-size: 22px !important;
		border-radius: 12px !important;
	}
}
@media (max-width: 420px) {
	.min-auth-panel {
		padding: 24px 16px !important;
		border-radius: 22px !important;
	}
	.min-auth-tab {
		flex-direction: column !important;
		gap: 4px !important;
		font-size: 11px !important;
		padding: 10px 4px !important;
	}
}

@media (prefers-reduced-motion: reduce) {
	* {
		animation-duration: .01ms !important;
		animation-iteration-count: 1 !important;
		transition-duration: .01ms !important;
	}
}
</style>

<script id="ezlens-login-validate">
(function () {
	'use strict';
	if (window.__ezLoginValidateV23) return;
	window.__ezLoginValidateV23 = true;

	function ensureBodyClass() {
		if (document.body && !document.body.classList.contains('ezlens-login-active')) {
			document.body.classList.add('ezlens-login-active');
		}
	}
	ensureBodyClass();
	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', ensureBodyClass);
	}

	var ICONS = {
		alert:     '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>',
		check:     '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>',
		info:      '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>',
		x:         '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>',
		clock:     '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>',
		bell:      '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>',
		lock:      '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>',
		mail:      '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="4" width="20" height="16" rx="2"/><polyline points="2,7 12,13 22,7"/></svg>',
		phone:     '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><rect x="5" y="2" width="14" height="20" rx="2"/><line x1="12" y1="18" x2="12.01" y2="18"/></svg>',
		hourglass: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 22h14"/><path d="M5 2h14"/><path d="M17 22v-4.172a2 2 0 0 0-.586-1.414L12 12l-4.414 4.414A2 2 0 0 0 7 17.828V22"/><path d="M7 2v4.172a2 2 0 0 0 .586 1.414L12 12l4.414-4.414A2 2 0 0 0 17 6.172V2"/></svg>'
	};

	var EMOJI_MAP = {
		'✅': ICONS.check, '☑️': ICONS.check, '✔️': ICONS.check, '✔': ICONS.check,
		'❌': ICONS.x, '✖️': ICONS.x, '✖': ICONS.x,
		'⏳': ICONS.hourglass, '⌛': ICONS.hourglass,
		'⏱️': ICONS.clock, '⏱': ICONS.clock, '🕐': ICONS.clock, '🕒': ICONS.clock,
		'⚠️': ICONS.alert, '⚠': ICONS.alert,
		'ℹ️': ICONS.info, 'ℹ': ICONS.info,
		'🔔': ICONS.bell, '🔒': ICONS.lock, '🔓': ICONS.lock,
		'📧': ICONS.mail, '✉️': ICONS.mail, '✉': ICONS.mail,
		'📱': ICONS.phone, '📞': ICONS.phone, '☎️': ICONS.phone, '☎': ICONS.phone
	};

	function ready(fn) {
		if (document.readyState !== 'loading') fn();
		else document.addEventListener('DOMContentLoaded', fn);
	}

	function toEN(str) {
		if (!str) return str;
		var fa = '۰۱۲۳۴۵۶۷۸۹', ar = '٠١٢٣٤٥٦٧٨٩', en = '0123456789', out = '';
		for (var i = 0; i < str.length; i++) {
			var ch = str.charAt(i);
			var iF = fa.indexOf(ch), iA = ar.indexOf(ch);
			if (iF > -1) out += en.charAt(iF);
			else if (iA > -1) out += en.charAt(iA);
			else out += ch;
		}
		return out;
	}

	function buildMsg(iconName, text) {
		return '<span class="ez-msg-icon">' + (ICONS[iconName] || '') + '</span><span class="ez-msg-text">' + text + '</span>';
	}

	function replaceEmojiInNode(root) {
		if (!root || !root.nodeType) return;
		if (root.nodeType === 3) {
			root = root.parentNode;
			if (!root) return;
		}
		var walker = document.createTreeWalker(root, NodeFilter.SHOW_TEXT, {
			acceptNode: function (node) {
				var parent = node.parentNode;
				if (!parent) return NodeFilter.FILTER_REJECT;
				var tag = (parent.nodeName || '').toUpperCase();
				var skip = ['SCRIPT','STYLE','SVG','PATH','CIRCLE','RECT','POLYLINE','LINE','TEXTAREA','INPUT'];
				if (skip.indexOf(tag) !== -1) return NodeFilter.FILTER_REJECT;
				if (parent.classList && parent.classList.contains('ez-inline-svg')) return NodeFilter.FILTER_REJECT;
				return NodeFilter.FILTER_ACCEPT;
			}
		});
		var textNodes = [];
		var n;
		while (n = walker.nextNode()) { if (/\S/.test(n.nodeValue || '')) textNodes.push(n); }
		textNodes.forEach(function (textNode) {
			var text = textNode.nodeValue;
			var hasEmoji = false;
			Object.keys(EMOJI_MAP).forEach(function (emoji) { if (text.indexOf(emoji) !== -1) hasEmoji = true; });
			if (!hasEmoji) return;
			var frag = document.createDocumentFragment();
			var remaining = text;
			while (remaining.length > 0) {
				var earliest = -1, matched = null;
				Object.keys(EMOJI_MAP).forEach(function (emoji) {
					var idx = remaining.indexOf(emoji);
					if (idx !== -1 && (earliest === -1 || idx < earliest)) { earliest = idx; matched = emoji; }
				});
				if (earliest === -1) { frag.appendChild(document.createTextNode(remaining)); break; }
				if (earliest > 0) frag.appendChild(document.createTextNode(remaining.slice(0, earliest)));
				var span = document.createElement('span');
				span.className = 'ez-inline-svg';
				span.setAttribute('aria-hidden', 'true');
				span.innerHTML = EMOJI_MAP[matched];
				frag.appendChild(span);
				remaining = remaining.slice(earliest + matched.length);
			}
			if (textNode.parentNode) textNode.parentNode.replaceChild(frag, textNode);
		});
	}

	ready(function () {
		var wrapper = document.querySelector('.min-auth-wrapper');
		if (!wrapper) return;

		/* ============================================================
		   انتقال محتوا به پالت + حذف Eye Badge (اگر وجود دارد)
		   ============================================================ */
		var panel = wrapper.querySelector('.min-auth-panel');
		if (!panel) {
			panel = document.createElement('div');
			panel.className = 'min-auth-panel';

			var children = Array.prototype.slice.call(wrapper.childNodes);
			children.forEach(function (node) {
				if (node.nodeType === 1 || (node.nodeType === 3 && node.nodeValue.trim())) {
					panel.appendChild(node);
				}
			});
			wrapper.appendChild(panel);
		}

		// حذف eye badge اگر از نسخه قبل باقی مانده
		wrapper.querySelectorAll('.ez-eye-badge').forEach(function (b) { b.remove(); });

		wrapper.querySelectorAll('.ez-waiting-screen').forEach(function (scr) {
			if (!wrapper.classList.contains('is-waiting')) {
				scr.style.display = 'none';
				scr.classList.remove('active');
			}
		});

		var patterns = {
			email: /^[a-zA-Z0-9._%+\-]+@[a-zA-Z0-9.\-]+\.[a-zA-Z]{2,}$/,
			phone: /^09[0-9]{9}$/,
			digitsOnly: /^[0-9]+$/
		};

		var messages = {
			required: 'این فیلد را پر کنید.',
			email_no_at: 'ایمیل باید علامت @ داشته باشد.',
			email_invalid: 'ایمیل معتبر نیست. مثال: name@gmail.com',
			phone_no_09: 'شماره موبایل باید با ۰۹ شروع شود.',
			phone_length: 'شماره موبایل باید ۱۱ رقم باشد.',
			phone_only_digits: 'فقط عدد وارد کنید.',
			password_short: 'رمز عبور باید حداقل ۶ کاراکتر باشد.',
			captcha_invalid: 'پاسخ کپچا اشتباه است.'
		};

		var successMessages = {
			email: 'ایمیل معتبر است.',
			phone: 'شماره موبایل معتبر است.',
			password: 'رمز عبور قابل قبول است.',
			captcha: 'پاسخ صحیح است.'
		};

		function getGroup(input) {
			return input.closest('.min-auth-input-group') || input.closest('.min-auth-captcha');
		}
		function showError(input, msg) {
			var g = getGroup(input);
			if (!g) return;
			g.classList.add('has-error');
			g.classList.remove('has-success');
			var e = g.querySelector('.ez-field-error');
			if (e) e.innerHTML = buildMsg('alert', msg);
		}
		function showSuccess(input, msg) {
			var g = getGroup(input);
			if (!g) return;
			g.classList.remove('has-error');
			g.classList.add('has-success');
			var s = g.querySelector('.ez-field-success');
			if (s) s.innerHTML = buildMsg('check', msg || '');
		}
		function clearState(input) {
			var g = getGroup(input);
			if (!g) return;
			g.classList.remove('has-error', 'has-success');
			var e = g.querySelector('.ez-field-error');
			var s = g.querySelector('.ez-field-success');
			if (e) e.innerHTML = '';
			if (s) s.innerHTML = '';
		}

		function getType(input) {
			var type = (input.getAttribute('type') || '').toLowerCase();
			var name = (input.getAttribute('name') || '').toLowerCase();
			var all = name + ' ' + type + ' ' + (input.id || '') + ' ' + (input.placeholder || '');
			if (type === 'email' || /email|mail/.test(all)) return 'email';
			if (type === 'tel' || /phone|mobile|tel/.test(all)) return 'phone';
			if (type === 'password') return 'password';
			if (/captcha/.test(all)) return 'captcha';
			return 'text';
		}

		function validateField(input) {
			var t = getType(input);
			var v = (input.value || '').trim();
			if (!v) {
				if (input.hasAttribute('required')) { showError(input, messages.required); return false; }
				clearState(input); return true;
			}
			if (t === 'email') {
				if (v.indexOf('@') === -1) { showError(input, messages.email_no_at); return false; }
				if (!patterns.email.test(v)) { showError(input, messages.email_invalid); return false; }
				showSuccess(input, successMessages.email); return true;
			}
			if (t === 'phone') {
				var c = v.replace(/[\s\-()]/g, '').replace(/^\+98/, '0').replace(/^98/, '0').replace(/^0098/, '0');
				if (!patterns.digitsOnly.test(c)) { showError(input, messages.phone_only_digits); return false; }
				if (c.length !== 11) { showError(input, messages.phone_length); return false; }
				if (!/^09/.test(c)) { showError(input, messages.phone_no_09); return false; }
				if (!patterns.phone.test(c)) { showError(input, messages.phone_length); return false; }
				showSuccess(input, successMessages.phone); return true;
			}
			if (t === 'password') {
				if (v.length < 6) { showError(input, messages.password_short); return false; }
				showSuccess(input, successMessages.password); return true;
			}
			if (t === 'captcha') {
				var wrap = input.closest('.min-auth-captcha');
				if (!wrap) return true;
				if (!patterns.digitsOnly.test(v)) {
					wrap.classList.add('has-error'); wrap.classList.remove('has-success');
					var e1 = wrap.querySelector('.ez-field-error');
					if (e1) e1.innerHTML = buildMsg('alert', messages.phone_only_digits);
					return false;
				}
				var exp = wrap.getAttribute('data-answer') || wrap.getAttribute('data-sum') || '';
				if (exp && String(v) !== String(exp)) {
					wrap.classList.add('has-error'); wrap.classList.remove('has-success');
					var e2 = wrap.querySelector('.ez-field-error');
					if (e2) e2.innerHTML = buildMsg('alert', messages.captcha_invalid);
					return false;
				}
				if (exp) {
					wrap.classList.remove('has-error'); wrap.classList.add('has-success');
					var s2 = wrap.querySelector('.ez-field-success');
					if (s2) s2.innerHTML = buildMsg('check', successMessages.captcha);
				}
				return true;
			}
			showSuccess(input, '');
			return true;
		}

		function restrict(input) {
			var t = getType(input);
			var numeric = (t === 'phone' || t === 'captcha');
			if (numeric) {
				input.addEventListener('keydown', function (e) {
					var ok = ['Backspace','Delete','Tab','Escape','Enter','ArrowLeft','ArrowRight','ArrowUp','ArrowDown','Home','End'];
					if (ok.indexOf(e.key) !== -1) return;
					if (e.ctrlKey || e.metaKey) return;
					if (!/^[0-9]$/.test(e.key)) e.preventDefault();
				});
				input.addEventListener('paste', function (e) {
					var p = (e.clipboardData || window.clipboardData).getData('text') || '';
					p = toEN(p);
					if (!/^[0-9\s\-()+]*$/.test(p)) e.preventDefault();
				});
				input.addEventListener('input', function () {
					var c = toEN(input.value);
					if (c !== input.value) {
						var pos = input.selectionStart;
						input.value = c;
						try { input.setSelectionRange(pos, pos); } catch (err) {}
					}
				});
			}
			if (t === 'phone') {
				input.setAttribute('maxlength', '11');
				input.setAttribute('inputmode', 'numeric');
				input.setAttribute('autocomplete', 'tel');
			}
			if (t === 'captcha') {
				input.setAttribute('maxlength', '6');
				input.setAttribute('inputmode', 'numeric');
			}
			if (t === 'email') {
				input.setAttribute('inputmode', 'email');
				input.setAttribute('autocomplete', 'email');
				input.setAttribute('spellcheck', 'false');
				input.addEventListener('keydown', function (e) { if (e.key === ' ') e.preventDefault(); });
				input.addEventListener('input', function () {
					var v = toEN(input.value.replace(/\s/g, ''));
					if (v !== input.value) input.value = v;
				});
			}
			if (t === 'password') input.setAttribute('autocomplete', 'current-password');
		}

		wrapper.querySelectorAll('input[type="text"], input[type="tel"], input[type="email"], input[type="password"]').forEach(function (input) {
			restrict(input);
			var db = null;
			input.addEventListener('input', function () {
				clearTimeout(db);
				db = setTimeout(function () { validateField(input); }, 320);
			});
			input.addEventListener('blur', function () { validateField(input); });
		});

		wrapper.querySelectorAll('.min-auth-captcha').forEach(function (cap) {
			if (!cap.getAttribute('data-answer')) {
				var ns = cap.querySelectorAll('.captcha-numbers span, .captcha-numbers b, .captcha-numbers strong');
				if (ns.length >= 2) {
					var a = parseInt((ns[0].textContent || '').replace(/\D/g, ''), 10);
					var b = parseInt((ns[1].textContent || '').replace(/\D/g, ''), 10);
					if (!isNaN(a) && !isNaN(b)) cap.setAttribute('data-answer', String(a + b));
				}
			}
		});

		wrapper.querySelectorAll('form').forEach(function (form) {
			form.addEventListener('submit', function (e) {
				var valid = true;
				form.querySelectorAll('input[type="text"], input[type="tel"], input[type="email"], input[type="password"]').forEach(function (input) {
					if (!validateField(input)) valid = false;
				});
				if (!valid) {
					e.preventDefault();
					var first = form.querySelector('.has-error input, .has-error .captcha-input');
					if (first) {
						try { first.focus(); } catch (err) {}
						try { first.scrollIntoView({ behavior: 'smooth', block: 'center' }); } catch (err) {}
					}
					return;
				}
				var btn = form.querySelector('.min-auth-btn');
				if (btn) {
					btn.classList.add('is-sending');
					btn.disabled = true;
					var txt = btn.querySelector('.ez-btn-text') || btn;
					if (!btn.querySelector('.ez-dots')) {
						var orig = (txt.textContent || '').trim();
						txt.innerHTML = orig + ' <span class="ez-dots"><span></span><span></span><span></span></span>';
					}
				}
			}, true);
		});

		function showWaiting(phoneVal) {
			wrapper.querySelectorAll('.ez-waiting-screen').forEach(function (s) { s.remove(); });
			wrapper.classList.add('is-waiting');

			var screen = document.createElement('div');
			screen.className = 'ez-waiting-screen active';
			screen.innerHTML =
				'<div class="ez-radar">' +
					'<span class="ez-radar-circle"></span>' +
					'<span class="ez-radar-circle"></span>' +
					'<span class="ez-radar-circle"></span>' +
					'<div class="ez-radar-center">' +
						'<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>' +
					'</div>' +
				'</div>' +
				'<h3 class="ez-waiting-title">کد تأیید ارسال شد</h3>' +
				'<p class="ez-waiting-subtitle">کد ۶ رقمی به شماره زیر پیامک شد</p>' +
				'<div class="ez-waiting-phone" dir="ltr">' + phoneVal + '</div>' +
				'<div class="ez-timer">' +
					'<span class="ez-timer-label">ارسال مجدد تا</span>' +
					'<span class="ez-timer-value" id="ez-timer-val">02:00</span>' +
				'</div>' +
				'<div class="ez-waiting-tips">' +
					'<div class="ez-tip active">پیامک ممکن است چند لحظه طول بکشد</div>' +
					'<div class="ez-tip">پوشه اسپم پیامک‌ها را بررسی کنید</div>' +
					'<div class="ez-tip">اطمینان حاصل کنید شماره صحیح است</div>' +
					'<div class="ez-tip">اگر کد را دریافت نکردید، ارسال مجدد را بزنید</div>' +
				'</div>' +
				'<div class="ez-code-inputs" dir="ltr">' +
					'<input type="text" maxlength="1" inputmode="numeric" data-index="0" />' +
					'<input type="text" maxlength="1" inputmode="numeric" data-index="1" />' +
					'<input type="text" maxlength="1" inputmode="numeric" data-index="2" />' +
					'<input type="text" maxlength="1" inputmode="numeric" data-index="3" />' +
					'<input type="text" maxlength="1" inputmode="numeric" data-index="4" />' +
					'<input type="text" maxlength="1" inputmode="numeric" data-index="5" />' +
				'</div>' +
				'<div class="ez-waiting-actions">' +
					'<button type="button" class="ez-resend-btn" disabled>' +
						'<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="23 4 23 10 17 10"/><polyline points="1 20 1 14 7 14"/><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10"/><path d="M1 14l4.64 4.36A9 9 0 0 0 20.49 15"/></svg>' +
						'<span>ارسال مجدد کد</span>' +
					'</button>' +
					'<button type="button" class="ez-back-btn">' +
						'<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>' +
						'<span>ویرایش شماره موبایل</span>' +
					'</button>' +
				'</div>';

			if (panel) {
				panel.appendChild(screen);
			} else {
				wrapper.appendChild(screen);
			}

			var tval = screen.querySelector('#ez-timer-val');
			var rbtn = screen.querySelector('.ez-resend-btn');
			var sec = 120;
			var tid = setInterval(function () {
				sec--;
				if (sec <= 0) {
					clearInterval(tid);
					tval.textContent = '00:00';
					tval.classList.add('is-low');
					rbtn.disabled = false;
					return;
				}
				var m = Math.floor(sec / 60), s = sec % 60;
				tval.textContent = (m < 10 ? '0' : '') + m + ':' + (s < 10 ? '0' : '') + s;
				if (sec <= 30) tval.classList.add('is-low');
			}, 1000);

			var tips = screen.querySelectorAll('.ez-tip');
			var idx = 0;
			var tipId = setInterval(function () {
				tips[idx].classList.remove('active');
				idx = (idx + 1) % tips.length;
				tips[idx].classList.add('active');
			}, 4000);

			var cis = screen.querySelectorAll('.ez-code-inputs input');
			cis.forEach(function (inp, i) {
				inp.addEventListener('input', function () {
					var v = toEN(inp.value).replace(/\D/g, '').slice(0, 1);
					inp.value = v;
					if (v) {
						inp.classList.add('is-filled');
						if (i < cis.length - 1) cis[i + 1].focus();
					} else inp.classList.remove('is-filled');
				});
				inp.addEventListener('keydown', function (e) {
					if (e.key === 'Backspace' && !inp.value && i > 0) cis[i - 1].focus();
				});
				inp.addEventListener('paste', function (e) {
					e.preventDefault();
					var p = toEN((e.clipboardData || window.clipboardData).getData('text') || '').replace(/\D/g, '').slice(0, 6);
					for (var k = 0; k < p.length && k < cis.length; k++) {
						cis[k].value = p.charAt(k);
						cis[k].classList.add('is-filled');
					}
					cis[Math.min(p.length, cis.length - 1)].focus();
				});
			});
			if (cis.length) cis[0].focus();

			screen.querySelector('.ez-back-btn').addEventListener('click', function () {
				clearInterval(tid);
				clearInterval(tipId);
				screen.remove();
				wrapper.classList.remove('is-waiting');
				var btn = wrapper.querySelector('.min-auth-btn.is-sending');
				if (btn) { btn.classList.remove('is-sending'); btn.disabled = false; }
			});

			rbtn.addEventListener('click', function () {
				rbtn.disabled = true;
				sec = 120;
				tval.classList.remove('is-low');
				clearInterval(tid);
				tid = setInterval(function () {
					sec--;
					if (sec <= 0) {
						clearInterval(tid);
						tval.textContent = '00:00';
						rbtn.disabled = false;
						return;
					}
					var m = Math.floor(sec / 60), s = sec % 60;
					tval.textContent = (m < 10 ? '0' : '') + m + ':' + (s < 10 ? '0' : '') + s;
					if (sec <= 30) tval.classList.add('is-low');
				}, 1000);
			});
		}

		window.ezShowWaitingScreen = showWaiting;

		wrapper.querySelectorAll('.min-auth-tab').forEach(function (btn) {
			btn.addEventListener('click', function () {
				if (btn.classList.contains('active')) return;
				wrapper.querySelectorAll('.min-auth-tab').forEach(function (b) { b.classList.remove('active'); });
				btn.classList.add('active');
				var target = btn.getAttribute('data-target') || btn.getAttribute('data-tab');
				wrapper.querySelectorAll('.min-auth-form').forEach(function (f) { f.classList.remove('active'); });
				var map = { otp: 'otpForm', login: 'loginForm', register: 'registerForm' };
				if (map[target]) {
					var f = document.getElementById(map[target]);
					if (f) f.classList.add('active');
				}
			});
		});

		var forgotOverlay = wrapper.querySelector('#forgotOverlay');
		var showForgotBtn = wrapper.querySelector('#showForgot');
		var closeForgotBtn = wrapper.querySelector('#closeForgot');
		var backToLoginBtn = wrapper.querySelector('#backToLogin');
		if (showForgotBtn && forgotOverlay) showForgotBtn.addEventListener('click', function () { forgotOverlay.classList.add('active'); });
		if (closeForgotBtn && forgotOverlay) closeForgotBtn.addEventListener('click', function () { forgotOverlay.classList.remove('active'); });
		if (backToLoginBtn && forgotOverlay) backToLoginBtn.addEventListener('click', function () { forgotOverlay.classList.remove('active'); });
		forgotOverlay && forgotOverlay.addEventListener('click', function (e) { if (e.target === forgotOverlay) forgotOverlay.classList.remove('active'); });

		wrapper.querySelectorAll('.min-auth-forgot-tab').forEach(function (btn) {
			btn.addEventListener('click', function () {
				wrapper.querySelectorAll('.min-auth-forgot-tab').forEach(function (b) { b.classList.remove('active'); });
				btn.classList.add('active');
				var target = btn.getAttribute('data-target');
				wrapper.querySelectorAll('.min-auth-forgot-form').forEach(function (f) { f.classList.remove('active'); });
				var map = { forgotSms: 'forgotSmsForm', forgotEmail: 'forgotEmailForm' };
				if (map[target]) {
					var f = document.getElementById(map[target]);
					if (f) f.classList.add('active');
				}
			});
		});

		replaceEmojiInNode(wrapper);

		if (window.MutationObserver) {
			var mo = new MutationObserver(function (mutations) {
				mutations.forEach(function (m) {
					if (m.type === 'childList') {
						m.addedNodes.forEach(function (node) {
							if (node.nodeType === 1) replaceEmojiInNode(node);
							else if (node.nodeType === 3 && node.parentNode) replaceEmojiInNode(node.parentNode);
						});
					} else if (m.type === 'characterData' && m.target.parentNode) {
						replaceEmojiInNode(m.target.parentNode);
					}
				});
			});
			mo.observe(wrapper, { childList: true, subtree: true, characterData: true });
		}
	});
})();
</script>
<?php
include $core;