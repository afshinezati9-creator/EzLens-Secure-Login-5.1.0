<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$logs  = class_exists( 'EzLens_CD_Mass_Actions' ) ? EzLens_CD_Mass_Actions::recent_logs( 15 ) : array();
$queue = class_exists( 'EzLens_CD_Mass_Actions' ) ? EzLens_CD_Mass_Actions::queue_recent( 20 ) : array();
$preselect = isset( $_GET['user_ids'] ) ? sanitize_text_field( wp_unslash( $_GET['user_ids'] ) ) : '';

$users = get_users(
	array(
		'role__in' => array( 'customer', 'subscriber' ),
		'number'  => 200,
		'orderby' => 'registered',
		'order'   => 'DESC',
	)
);
if ( empty( $users ) ) {
	$users = get_users( array( 'number' => 100, 'orderby' => 'registered', 'order' => 'DESC' ) );
}

$has_sms = class_exists( 'EzLens_Auth_Messaging' ) || has_filter( 'ezlens_send_sms' );
$has_mail = true;
?>
<div class="wrap ezcd-admin" dir="rtl">
	<h1>پیام به مشتریان</h1>
	<p class="description" style="max-width:720px;line-height:1.7;">
		<strong>میان‌بر کاربران سایت:</strong> فقط مشتریان/کاربران ثبت‌نام‌شده.
		برای لیست خارج از سایت، CSV یا کمپین زمان‌بندی‌شده به
		<a href="<?php echo esc_url( admin_url( 'admin.php?page=ezlens-auth-campaign' ) ); ?>">کمپین و لیست‌ها</a> بروید.
		ارسال از مسیر Messaging پلاگین انجام می‌شود.
	</p>

	<div class="ezcd-admin-grid" style="grid-template-columns:1.2fr .9fr;align-items:start">
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="ezcd-admin-card" id="ezcd-mass-form">
			<input type="hidden" name="action" value="ezcd_mass_send">
			<?php wp_nonce_field( 'ezcd_mass_send' ); ?>

			<h2>۱) انتخاب مخاطب</h2>
			<p>
				<label><input type="radio" name="audience" value="selected" checked class="ezcd-aud"> انتخاب از لیست / دستی</label>
				&nbsp;
				<label><input type="radio" name="audience" value="all_customers" class="ezcd-aud"> همه مشتریان</label>
				&nbsp;
				<label><input type="radio" name="audience" value="with_orders" class="ezcd-aud"> خریداران</label>
				&nbsp;
				<label><input type="radio" name="audience" value="no_orders" class="ezcd-aud"> بدون سفارش</label>
			</p>

			<div id="ezcd-picker-wrap">
				<div class="ezcd-picker-bar">
					<input type="search" id="ezcd-user-filter" placeholder="جستجوی نام / ایمیل / موبایل…" class="regular-text">
					<button type="button" class="button" id="ezcd-select-all">انتخاب همه</button>
					<button type="button" class="button" id="ezcd-select-none">حذف انتخاب</button>
					<span id="ezcd-sel-count" class="ezcd-admin-msg">۰ نفر</span>
				</div>
				<div class="ezcd-user-picker">
					<?php foreach ( $users as $u ) :
						$phone = method_exists( 'EzLens_CD_Admin_Customers', 'phone' ) ? EzLens_CD_Admin_Customers::phone( $u->ID ) : get_user_meta( $u->ID, 'billing_phone', true );
						?>
						<label class="ezcd-user-row" data-q="<?php echo esc_attr( strtolower( $u->display_name . ' ' . $u->user_email . ' ' . $phone ) ); ?>">
							<input type="checkbox" name="user_ids[]" value="<?php echo esc_attr( $u->ID ); ?>" class="ezcd-uid">
							<span class="ezcd-user-meta">
								<strong><?php echo esc_html( $u->display_name ); ?></strong>
								<small><?php echo esc_html( $u->user_email ); ?><?php echo $phone ? ' · ' . esc_html( $phone ) : ''; ?></small>
							</span>
						</label>
					<?php endforeach; ?>
				</div>
				<p class="description">یا شناسه‌ها را دستی وارد کنید (با کاما):</p>
				<textarea name="user_ids_text" id="ezcd-user-ids-text" rows="2" class="large-text" placeholder="12,45,78"><?php echo esc_textarea( $preselect ); ?></textarea>
			</div>

			<h2>۲) کانال ارسال</h2>
			<p>
				<label class="ezcd-channel"><input type="radio" name="channel" value="sms" checked> پیامک <?php echo $has_sms ? '✅' : '⚠️'; ?></label>
				<label class="ezcd-channel"><input type="radio" name="channel" value="email"> ایمیل</label>
				<label class="ezcd-channel"><input type="radio" name="channel" value="both"> هر دو</label>
			</p>
			<p class="description">ارسال واقعی از <code>EzLens_Auth_Messaging</code> / فیلتر <code>ezlens_send_sms</code> و ایمیل وردپرس انجام می‌شود.</p>

			<label>موضوع ایمیل
				<input type="text" name="subject" class="regular-text" placeholder="اختیاری برای پیامک">
			</label>
			<label style="display:block;margin-top:10px">متن پیام
				<textarea name="message" rows="5" class="large-text" required placeholder="متن کوتاه، محترمانه و شفاف…"></textarea>
			</label>

			<p style="margin-top:14px">
				<button type="submit" class="button button-primary" onclick="return confirm('ارسال برای مخاطبان انتخاب‌شده؟');">ارسال پیام</button>
			</p>
		</form>

		<div class="ezcd-admin-card">
			<h2>راهنما</h2>
			<ul style="line-height:1.9;margin:0;padding-right:18px">
				<li>برای پیامک تکی یا چندتایی: تیک بزنید و کانال «پیامک» را بگذارید.</li>
				<li>گروه‌های آماده برای کمپین سریع هستند.</li>
				<li>اگر درگاه پیامک وصل نباشد، پیام در صف داخلی می‌ماند.</li>
			</ul>
			<p>
				<a class="button" href="<?php echo esc_url( admin_url( 'admin.php?page=ezlens-cd-customers' ) ); ?>">لیست مشتریان</a>
				<?php if ( class_exists( 'EzLens_Auth_Campaign' ) ) : ?>
					<a class="button" href="<?php echo esc_url( admin_url( 'admin.php?page=ezlens-auth-campaign' ) ); ?>">کمپین EzLens</a>
				<?php endif; ?>
			</p>
		</div>
	</div>

	<h2>آخرین ارسال‌ها</h2>
	<?php if ( empty( $logs ) ) : ?>
		<p>هنوز ارسالی ثبت نشده.</p>
	<?php else : ?>
		<table class="wp-list-table widefat striped">
			<thead><tr><th>زمان</th><th>کانال</th><th>هدف</th><th>موفق</th><th>ناموفق</th><th>ادمین</th></tr></thead>
			<tbody>
			<?php foreach ( $logs as $log ) :
				$admin = get_userdata( (int) $log->admin_id );
				?>
				<tr>
					<td><?php echo esc_html( $log->created_at ); ?></td>
					<td><?php echo esc_html( $log->channel ); ?></td>
					<td><?php echo esc_html( (string) $log->target_count ); ?></td>
					<td><?php echo esc_html( (string) $log->success_count ); ?></td>
					<td><?php echo esc_html( (string) $log->fail_count ); ?></td>
					<td><?php echo esc_html( $admin ? $admin->display_name : '—' ); ?></td>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>
	<?php endif; ?>

	<?php if ( ! empty( $queue ) ) : ?>
		<h2>صف ناموفق / در انتظار</h2>
		<table class="wp-list-table widefat striped">
			<thead><tr><th>کاربر</th><th>کانال</th><th>گیرنده</th><th>وضعیت</th><th>خطا</th></tr></thead>
			<tbody>
			<?php foreach ( $queue as $q ) : ?>
				<tr>
					<td>#<?php echo esc_html( (string) $q->user_id ); ?></td>
					<td><?php echo esc_html( $q->channel ); ?></td>
					<td><code><?php echo esc_html( $q->recipient ); ?></code></td>
					<td><?php echo esc_html( $q->status ); ?></td>
					<td><?php echo esc_html( $q->error_text ); ?></td>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>
	<?php endif; ?>
</div>

	<div class="ezcd-admin-card" style="margin:16px 0;padding:16px;border:1px solid #e2e8f0;border-radius:12px;background:#f8fafc;max-width:720px;">
		<h2 style="margin-top:0;font-size:16px;">پل به کمپین</h2>
		<p class="description" style="margin-top:0;">از کاربران انتخاب‌شده (تیک‌خورده) یک <strong>لیست مخاطب کمپین</strong> بساز تا بعداً در «کمپین و لیست‌ها» زمان‌بندی/SMS/ایمیل انبوه بفرستی. ارسال فوری اینجا انجام نمی‌شود.</p>
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" id="ezcd-mass-to-list-form">
			<input type="hidden" name="action" value="ezcd_mass_to_campaign_list">
			<?php wp_nonce_field( 'ezcd_mass_to_campaign_list' ); ?>
			<input type="hidden" name="user_ids" id="ezcd-bridge-uids" value="">
			<p>
				<label>نام لیست<br>
					<input type="text" name="list_name" class="regular-text" placeholder="مثلاً: خریداران ویژه — شهریور" style="min-width:280px;">
				</label>
			</p>
			<p>
				<button type="submit" class="button button-secondary" id="ezcd-bridge-submit">ساخت لیست کمپین از انتخاب‌شده‌ها</button>
			</p>
		</form>
		<?php if ( ! empty( $_GET['list_ok'] ) ) : ?>
			<div class="notice notice-success inline"><p><?php echo esc_html( rawurldecode( wp_unslash( $_GET['list_msg'] ?? 'لیست ساخته شد.' ) ) ); ?>
				<?php if ( ! empty( $_GET['camp'] ) ) : ?>
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=ezlens-auth-campaign' ) ); ?>">رفتن به کمپین و لیست‌ها</a>
				<?php endif; ?>
			</p></div>
		<?php endif; ?>
		<?php if ( ! empty( $_GET['list_err'] ) ) : ?>
			<div class="notice notice-error inline"><p><?php echo esc_html( rawurldecode( wp_unslash( $_GET['list_err'] ) ) ); ?></p></div>
		<?php endif; ?>
	</div>

<script>
(function(){
	function countSel(){
		var n = document.querySelectorAll('.ezcd-uid:checked').length;
		var el = document.getElementById('ezcd-sel-count');
		if(el) el.textContent = n + ' نفر';
	}
	document.querySelectorAll('.ezcd-uid').forEach(function(c){ c.addEventListener('change', countSel); });
	var fa = document.getElementById('ezcd-user-filter');
	if(fa){
		fa.addEventListener('input', function(){
			var q = (fa.value||'').toLowerCase();
			document.querySelectorAll('.ezcd-user-row').forEach(function(row){
				row.style.display = !q || (row.getAttribute('data-q')||'').indexOf(q) !== -1 ? '' : 'none';
			});
		});
	}
	var all = document.getElementById('ezcd-select-all');
	var none = document.getElementById('ezcd-select-none');
	if(all) all.addEventListener('click', function(){
		document.querySelectorAll('.ezcd-user-row').forEach(function(row){
			if(row.style.display === 'none') return;
			var c = row.querySelector('.ezcd-uid'); if(c) c.checked = true;
		});
		countSel();
	});
	if(none) none.addEventListener('click', function(){
		document.querySelectorAll('.ezcd-uid').forEach(function(c){ c.checked = false; });
		countSel();
	});
	var bridgeForm=document.getElementById('ezcd-mass-to-list-form');
	if(bridgeForm){
		bridgeForm.addEventListener('submit', function(e){
			var ids=[];
			document.querySelectorAll('.ezcd-uid:checked').forEach(function(c){ ids.push(c.value); });
			if(!ids.length){ e.preventDefault(); alert('حداقل یک مشتری را تیک بزنید'); return; }
			document.getElementById('ezcd-bridge-uids').value = ids.join(',');
		});
	}
})();
</script>
