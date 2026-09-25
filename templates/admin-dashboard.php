<?php
/**
 * داشبورد اصلی EzLens Auth — تم یکپارچه مینیمال
 * @version 3.2.0
 * @var array $stats
 * @var array $users_with_phone
 * @var array $purchase_stats
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$total_users   = (int) ( $stats['users'] ?? 0 );
$total_admins  = (int) ( $stats['admins'] ?? 0 );
$total_logins  = (int) ( $stats['logins'] ?? 0 );
$total_logouts = (int) ( $stats['logouts'] ?? 0 );
$recent_logs   = $stats['recent'] ?? array();

$all_logs = array();
foreach ( (array) $recent_logs as $log ) {
	$action = $log->action ?? '';
	$label  = ( $action === 'login' ) ? 'ورود' : ( ( $action === 'failed_login' ) ? 'ناموفق' : 'خروج' );
	$class  = ( $action === 'login' ) ? 'ok' : ( ( $action === 'failed_login' ) ? 'bad' : 'mute' );
	$all_logs[] = (object) array(
		'username'     => $log->username ?? '',
		'action_label' => $label,
		'action_class' => $class,
		'timestamp'    => $log->timestamp ?? '',
		'ip'           => $log->ip ?? '',
	);
}
usort(
	$all_logs,
	function ( $a, $b ) {
		return strtotime( $b->timestamp ) - strtotime( $a->timestamp );
	}
);
$all_logs = array_slice( $all_logs, 0, 10 );

$icon = function ( $name ) {
	$path = EZLAUTH_PLUGIN_DIR . 'assets/icons/modern/' . $name . '.svg';
	if ( is_readable( $path ) ) {
		return file_get_contents( $path );
	}
	$path2 = EZLAUTH_PLUGIN_DIR . 'assets/icons/' . $name . '.svg';
	return is_readable( $path2 ) ? file_get_contents( $path2 ) : '';
};

$qh = class_exists( 'EzLens_Auth_Queue_Health' ) ? EzLens_Auth_Queue_Health::snapshot() : null;
$maint = get_option( 'ezlens_maintenance_last', null );

$links = array(
	array( 'تنظیمات', 'ezlens-auth-settings', 'settings' ),
	array( 'کمپین و لیست‌ها', 'ezlens-auth-campaign', 'mail' ),
	array( 'پیام به مشتریان', 'ezlens-cd-mass', 'send' ),
	array( 'پشتیبانی', 'ezlens-cd-support', 'headset' ),
	array( 'مشتریان', 'ezlens-cd-customers', 'users' ),
	array( 'ویژگی محصول', 'ezlens-product-options', 'layers' ),
);
?>
<div class="wrap ezlens-dash" dir="rtl">
	<header class="ezd-hero">
		<div>
			<h1>داشبورد EzLens</h1>
			<p>نمای کلی ورود، کمپین و سلامت سیستم — تم یکپارچه با داشبورد مشتری</p>
		</div>
		<div class="ezd-hero-meta">
			<span><?php echo esc_html( wp_date( 'Y/m/d H:i' ) ); ?></span>
		</div>
	</header>

	<div class="ezd-stats">
		<article class="ezd-stat ezd-g1">
			<div class="ezd-stat-icon"><?php echo $icon( 'users' ); // phpcs:ignore ?></div>
			<div>
				<strong><?php echo esc_html( number_format_i18n( $total_users ) ); ?></strong>
				<span>کاربران</span>
			</div>
		</article>
		<article class="ezd-stat ezd-g2">
			<div class="ezd-stat-icon"><?php echo $icon( 'user' ); // phpcs:ignore ?></div>
			<div>
				<strong><?php echo esc_html( number_format_i18n( $total_admins ) ); ?></strong>
				<span>مدیران</span>
			</div>
		</article>
		<article class="ezd-stat ezd-g3">
			<div class="ezd-stat-icon"><?php echo $icon( 'log-in' ); // phpcs:ignore ?></div>
			<div>
				<strong><?php echo esc_html( number_format_i18n( $total_logins ) ); ?></strong>
				<span>ورودها</span>
			</div>
		</article>
		<article class="ezd-stat ezd-g4">
			<div class="ezd-stat-icon"><?php echo $icon( 'log-out' ); // phpcs:ignore ?></div>
			<div>
				<strong><?php echo esc_html( number_format_i18n( $total_logouts ) ); ?></strong>
				<span>خروج‌ها</span>
			</div>
		</article>
	</div>

	<div class="ezd-grid-2">
		<section class="ezd-card">
			<h2>میان‌برها</h2>
			<div class="ezd-links">
				<?php foreach ( $links as $L ) : ?>
					<a class="ezd-link" href="<?php echo esc_url( admin_url( 'admin.php?page=' . $L[1] ) ); ?>">
						<span class="ezd-link-ic"><?php echo $icon( $L[2] ); // phpcs:ignore ?></span>
						<span><?php echo esc_html( $L[0] ); ?></span>
					</a>
				<?php endforeach; ?>
			</div>
		</section>

		<section class="ezd-card">
			<h2>سلامت سیستم</h2>
			<ul class="ezd-health">
				<li>
					<span>کرون کمپین</span>
					<strong><?php echo $qh && ! empty( $qh['queue_tick_scheduled'] ) ? 'فعال' : '—'; ?></strong>
				</li>
				<li>
					<span>کمپین در حال ارسال</span>
					<strong><?php echo esc_html( (string) ( $qh['sending_count'] ?? 0 ) ); ?></strong>
				</li>
				<li>
					<span>گیرنده pending</span>
					<strong><?php echo esc_html( (string) ( $qh['pending_recipients'] ?? 0 ) ); ?></strong>
				</li>
				<li>
					<span>آخرین نگهداری</span>
					<strong><?php echo esc_html( is_array( $maint ) ? ( $maint['at'] ?? '—' ) : '—' ); ?></strong>
				</li>
				<?php if ( ! empty( $qh['message'] ) ) : ?>
					<li class="ezd-warn"><?php echo esc_html( $qh['message'] ); ?></li>
				<?php endif; ?>
			</ul>
		</section>
	</div>

	<section class="ezd-card ezd-logs">
		<h2>آخرین رویدادهای ورود</h2>
		<?php if ( empty( $all_logs ) ) : ?>
			<p class="ezd-empty">هنوز رویدادی ثبت نشده است.</p>
		<?php else : ?>
			<table class="ezd-table">
				<thead>
					<tr>
						<th>کاربر</th>
						<th>رویداد</th>
						<th>زمان</th>
						<th>IP</th>
					</tr>
				</thead>
				<tbody>
				<?php foreach ( $all_logs as $row ) : ?>
					<tr>
						<td><?php echo esc_html( $row->username ); ?></td>
						<td><span class="ezd-badge ezd-<?php echo esc_attr( $row->action_class ); ?>"><?php echo esc_html( $row->action_label ); ?></span></td>
						<td><?php echo esc_html( $row->timestamp ); ?></td>
						<td dir="ltr"><?php echo esc_html( $row->ip ); ?></td>
					</tr>
				<?php endforeach; ?>
				</tbody>
			</table>
		<?php endif; ?>
	</section>
</div>

<style>
.ezlens-dash{max-width:1100px;margin:8px 12px 40px 0;font-family:Tahoma,IRANYekan,sans-serif}
.ezd-hero{
	display:flex;flex-wrap:wrap;justify-content:space-between;gap:12px;align-items:flex-end;
	background:linear-gradient(135deg,#031f8a 0%,#1e4fd6 60%,#3b82f6 100%);
	color:#fff;border-radius:18px;padding:22px 24px;margin:10px 0 18px;
	box-shadow:0 12px 32px rgba(3,31,138,.25);
}
.ezd-hero h1{margin:0 0 6px;font-size:22px;color:#fff!important;font-weight:800}
.ezd-hero p{margin:0;opacity:.92;font-size:13px;max-width:520px;line-height:1.7}
.ezd-hero-meta{font-size:12px;opacity:.85}
.ezd-stats{display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-bottom:16px}
@media(max-width:960px){.ezd-stats{grid-template-columns:1fr 1fr}}
.ezd-stat{
	display:flex;gap:12px;align-items:center;border-radius:16px;padding:16px 14px;color:#fff;
	box-shadow:0 8px 22px rgba(15,23,42,.12);
}
.ezd-stat strong{display:block;font-size:22px;font-weight:800;color:#fff}
.ezd-stat span{font-size:12px;opacity:.9;color:#fff}
.ezd-stat-icon{width:40px;height:40px;border-radius:12px;background:rgba(255,255,255,.18);display:flex;align-items:center;justify-content:center}
.ezd-stat-icon svg{width:22px;height:22px;stroke:#fff;fill:none}
.ezd-g1{background:linear-gradient(135deg,#1e4fd6,#031f8a)}
.ezd-g2{background:linear-gradient(135deg,#10b981,#047857)}
.ezd-g3{background:linear-gradient(135deg,#0ea5e9,#0369a1)}
.ezd-g4{background:linear-gradient(135deg,#6366f1,#4338ca)}
.ezd-grid-2{display:grid;grid-template-columns:1.2fr .8fr;gap:14px;margin-bottom:14px}
@media(max-width:900px){.ezd-grid-2{grid-template-columns:1fr}}
.ezd-card{
	background:#fff;border:1px solid #e2e8f0;border-radius:16px;padding:16px 18px;
	box-shadow:0 6px 20px rgba(15,23,42,.04);
}
.ezd-card h2{margin:0 0 12px;font-size:15px;color:#031f8a}
.ezd-links{display:grid;grid-template-columns:repeat(auto-fill,minmax(160px,1fr));gap:8px}
.ezd-link{
	display:flex;align-items:center;gap:8px;padding:10px 12px;border-radius:12px;
	border:1px solid #e2e8f0;text-decoration:none;color:#0f172a;font-size:13px;font-weight:600;
	background:#f8fafc;transition:.15s ease;
}
.ezd-link:hover{border-color:#93c5fd;background:#eff6ff;color:#031f8a}
.ezd-link-ic{width:28px;height:28px;border-radius:8px;background:#e0e7ff;display:flex;align-items:center;justify-content:center;color:#031f8a}
.ezd-link-ic svg{width:16px;height:16px;stroke:currentColor;fill:none}
.ezd-health{list-style:none;margin:0;padding:0}
.ezd-health li{display:flex;justify-content:space-between;gap:8px;padding:8px 0;border-bottom:1px solid #f1f5f9;font-size:13px}
.ezd-health li span{color:#64748b}
.ezd-health li strong{color:#0f172a}
.ezd-warn{color:#b45309!important;font-weight:600}
.ezd-table{width:100%;border-collapse:collapse;font-size:13px}
.ezd-table th{text-align:right;padding:8px;border-bottom:1px solid #e2e8f0;color:#64748b;font-weight:600}
.ezd-table td{padding:8px;border-bottom:1px solid #f1f5f9}
.ezd-badge{display:inline-block;padding:2px 8px;border-radius:999px;font-size:11px;font-weight:700}
.ezd-ok{background:#d1fae5;color:#065f46}
.ezd-bad{background:#fee2e2;color:#991b1b}
.ezd-mute{background:#f1f5f9;color:#475569}
.ezd-empty{color:#94a3b8;font-size:13px}
</style>
