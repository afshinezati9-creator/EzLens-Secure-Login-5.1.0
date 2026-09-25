<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$customers = count_users();
$total_c = isset( $customers['total_users'] ) ? (int) $customers['total_users'] : 0;
$pending_dep = (int) get_option( 'ezlens_cd_wallet_pending_count', 0 );
$order_counts = function_exists( 'wc_orders_count' ) ? null : null;
$processing = 0;
$completed = 0;
if ( function_exists( 'wc_orders_count' ) ) {
	$processing = (int) wc_orders_count( 'processing' ) + (int) wc_orders_count( 'on-hold' );
	$completed  = (int) wc_orders_count( 'completed' );
} elseif ( function_exists( 'wc_get_orders' ) ) {
	$processing = count( wc_get_orders( array( 'status' => array( 'processing', 'on-hold', 'pending' ), 'limit' => 1, 'return' => 'ids', 'paginate' => true ) )->orders ?? array() );
}
global $wpdb;
$rx_table = $wpdb->prefix . 'ezlens_cd_prescriptions';
$rx_n = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$rx_table}" );
?>
<div class="wrap ezcd-admin" dir="rtl">
	<h1>داشبورد مشتری — نمای مدیریت</h1>
	<div class="ezcd-admin-stats">
		<a class="ezcd-admin-stat" href="<?php echo esc_url( admin_url( 'admin.php?page=ezlens-cd-customers' ) ); ?>">
			<strong><?php echo esc_html( number_format_i18n( $total_c ) ); ?></strong>
			<span>کاربران</span>
		</a>
		<a class="ezcd-admin-stat is-blue" href="<?php echo esc_url( admin_url( 'admin.php?page=ezlens-cd-orders&tab=processing' ) ); ?>">
			<strong><?php echo esc_html( number_format_i18n( $processing ) ); ?></strong>
			<span>سفارش جاری</span>
		</a>
		<a class="ezcd-admin-stat is-green" href="<?php echo esc_url( admin_url( 'admin.php?page=ezlens-cd-orders&tab=completed' ) ); ?>">
			<strong><?php echo esc_html( number_format_i18n( $completed ) ); ?></strong>
			<span>تحویل‌شده</span>
		</a>
		<a class="ezcd-admin-stat is-amber" href="<?php echo esc_url( admin_url( 'admin.php?page=ezlens-cd-prescriptions' ) ); ?>">
			<strong><?php echo esc_html( number_format_i18n( $rx_n ) ); ?></strong>
			<span>نسخه‌ها</span>
		</a>
		<a class="ezcd-admin-stat is-rose" href="<?php echo esc_url( admin_url( 'admin.php?page=ezlens-cd-deposits' ) ); ?>">
			<strong><?php echo esc_html( number_format_i18n( $pending_dep ) ); ?></strong>
			<span>شارژ در انتظار</span>
		</a>
	</div>
	<div class="ezcd-admin-grid">
		<a class="ezcd-admin-card" href="<?php echo esc_url( admin_url( 'admin.php?page=ezlens-cd-customers' ) ); ?>"><h2>مشتریان</h2><p>لیست، پروفایل، یادداشت و کیف پول</p></a>
		<a class="ezcd-admin-card" href="<?php echo esc_url( admin_url( 'admin.php?page=ezlens-cd-orders' ) ); ?>"><h2>سفارش‌ها</h2><p>جاری، تکمیل، لغو، مرجوعی — تغییر وضعیت AJAX</p></a>
		<a class="ezcd-admin-card" href="<?php echo esc_url( admin_url( 'admin.php?page=ezlens-cd-prescriptions' ) ); ?>"><h2>نسخه‌ها</h2><p>پرونده‌های ثبت‌شده مشتریان</p></a>
		<a class="ezcd-admin-card" href="<?php echo esc_url( admin_url( 'admin.php?page=ezlens-cd-reviews' ) ); ?>"><h2>نظرات</h2><p>تأیید، پاسخ، حذف</p></a>
		<a class="ezcd-admin-card" href="<?php echo esc_url( admin_url( 'admin.php?page=ezlens-cd-rewards' ) ); ?>"><h2>تخفیف و هدیه</h2><p>کدها و کمپین</p></a>
		<a class="ezcd-admin-card" href="<?php echo esc_url( admin_url( 'admin.php?page=ezlens-cd-support' ); ?>"><h2>پشتیبانی</h2><p>تیکت‌ها، پاسخ، ایمیل و پیامک</p></a>
		<a class="ezcd-admin-card" href="<?php echo esc_url( admin_url( 'admin.php?page=ezlens-cd-charity' ) ); ?>"><h2>هم‌یاری بینایی</h2><p>موارد نیازمند کمک، گزارش اثر، شفافیت</p></a>
		<a class="ezcd-admin-card" href="<?php echo esc_url( admin_url( 'admin.php?page=ezlens-cd-settings' ) ); ?>"><h2>تنظیمات</h2><p>فعال/غیرفعال بخش‌های داشبورد مشتری</p></a>
	</div>
</div>
