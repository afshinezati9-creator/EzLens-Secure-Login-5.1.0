<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$stats     = EzLens_CD_Charity::stats();
$cases     = EzLens_CD_Charity::admin_cases( 40 );
$donations = EzLens_CD_Charity::admin_donations( 40 );
$needs     = EzLens_CD_Charity::need_labels();
$nonce     = wp_create_nonce( 'ezcd_admin' );
$tab       = isset( $_GET['tab'] ) ? sanitize_key( $_GET['tab'] ) : 'cases';
?>
<div class="wrap ezcd-admin" dir="rtl">
	<h1>هم‌یاری بینایی</h1>
	<p class="description">مدیریت موارد نیازمند کمک، مشاهده کمک‌ها و ثبت گزارش شفاف برای اهداکنندگان.</p>

	<div class="ezcd-admin-stats" style="grid-template-columns:repeat(4,1fr)">
		<div class="ezcd-admin-stat is-blue"><strong><?php echo esc_html( number_format_i18n( $stats['raised'] ) ); ?></strong><span>جمع کمک‌ها (تومان)</span></div>
		<div class="ezcd-admin-stat is-green"><strong><?php echo esc_html( number_format_i18n( $stats['donations'] ) ); ?></strong><span>تعداد کمک</span></div>
		<div class="ezcd-admin-stat is-amber"><strong><?php echo esc_html( number_format_i18n( $stats['open'] ) ); ?></strong><span>مورد باز</span></div>
		<div class="ezcd-admin-stat"><strong><?php echo esc_html( number_format_i18n( $stats['funded'] ) ); ?></strong><span>تأمین‌شده</span></div>
	</div>

	<nav class="ezcd-admin-tabs">
		<a class="ezcd-admin-tab<?php echo 'cases' === $tab ? ' is-active' : ''; ?>" href="<?php echo esc_url( admin_url( 'admin.php?page=ezlens-cd-charity&tab=cases' ) ); ?>">موارد</a>
		<a class="ezcd-admin-tab<?php echo 'donations' === $tab ? ' is-active' : ''; ?>" href="<?php echo esc_url( admin_url( 'admin.php?page=ezlens-cd-charity&tab=donations' ) ); ?>">کمک‌ها</a>
		<a class="ezcd-admin-tab<?php echo 'report' === $tab ? ' is-active' : ''; ?>" href="<?php echo esc_url( admin_url( 'admin.php?page=ezlens-cd-charity&tab=report' ) ); ?>">گزارش اثر</a>
	</nav>

	<?php if ( 'cases' === $tab ) : ?>
		<div class="ezcd-admin-grid" style="grid-template-columns:1fr 1fr;align-items:start">
			<form class="ezcd-admin-card" id="ezcd-case-form">
				<h2>مورد جدید / ویرایش</h2>
				<input type="hidden" name="id" value="">
				<p><label>عنوان<br><input type="text" name="title" class="widefat" required placeholder="مثلاً: تأمین عینک برای دانش‌آموز"></label></p>
				<p><label>خلاصه عمومی (با حفظ حریم)<br><textarea name="summary" class="widefat" rows="3" placeholder="بدون ذکر نام واقعی…"></textarea></label></p>
				<p><label>نوع نیاز
					<select name="need_type">
						<?php foreach ( $needs as $k => $l ) : ?>
							<option value="<?php echo esc_attr( $k ); ?>"><?php echo esc_html( $l ); ?></option>
						<?php endforeach; ?>
					</select>
				</label></p>
				<p><label>هدف (تومان)<br><input type="number" name="goal_amount" class="regular-text" min="0" step="1000" value="0"></label></p>
				<p><label>وضعیت
					<select name="status">
						<option value="open">باز</option>
						<option value="funded">تأمین شد</option>
						<option value="closed">بسته</option>
					</select>
				</label></p>
				<p><label><input type="checkbox" name="is_public" value="1" checked> نمایش در داشبورد مشتری</label></p>
				<p><button type="submit" class="button button-primary">ذخیره مورد</button> <span id="ezcd-case-msg" class="ezcd-admin-msg"></span></p>
			</form>
			<div class="ezcd-admin-card">
				<h2>لیست موارد</h2>
				<table class="wp-list-table widefat striped">
					<thead><tr><th>عنوان</th><th>پیشرفت</th><th>وضعیت</th></tr></thead>
					<tbody>
					<?php if ( empty( $cases ) ) : ?>
						<tr><td colspan="3">موردی نیست.</td></tr>
					<?php else : ?>
						<?php foreach ( $cases as $c ) : ?>
							<tr>
								<td>
									<strong><?php echo esc_html( $c->title ); ?></strong>
									<div class="description"><?php echo esc_html( $needs[ $c->need_type ] ?? '' ); ?></div>
								</td>
								<td><?php echo esc_html( number_format_i18n( (int) $c->raised_amount ) ); ?> / <?php echo esc_html( number_format_i18n( (int) $c->goal_amount ) ); ?></td>
								<td><?php echo esc_html( $c->status ); ?><?php echo $c->is_public ? ' · عمومی' : ''; ?></td>
							</tr>
						<?php endforeach; ?>
					<?php endif; ?>
					</tbody>
				</table>
			</div>
		</div>
	<?php elseif ( 'donations' === $tab ) : ?>
		<table class="wp-list-table widefat striped">
			<thead><tr><th>ID</th><th>اهداکننده</th><th>مبلغ</th><th>مورد</th><th>زمان</th></tr></thead>
			<tbody>
			<?php if ( empty( $donations ) ) : ?>
				<tr><td colspan="5">کمکی ثبت نشده.</td></tr>
			<?php else : ?>
				<?php foreach ( $donations as $d ) : ?>
					<tr>
						<td><?php echo esc_html( (string) $d->id ); ?></td>
						<td><?php echo $d->is_anonymous ? 'ناشناس' : esc_html( $d->display_name ?: ( '#' . $d->user_id ) ); ?></td>
						<td><strong><?php echo esc_html( number_format_i18n( (int) $d->amount ) ); ?></strong></td>
						<td><?php echo esc_html( $d->case_title ?: 'عمومی' ); ?></td>
						<td><?php echo esc_html( $d->created_at ); ?></td>
					</tr>
				<?php endforeach; ?>
			<?php endif; ?>
			</tbody>
		</table>
	<?php else : ?>
		<form class="ezcd-admin-card" id="ezcd-impact-form" style="max-width:640px">
			<h2>ثبت گزارش اثر برای یک کمک</h2>
			<p class="description">اهداکننده این متن را در «گزارش اثر» می‌بیند. نام واقعی بیمار را فقط در حدی بنویسید که حریم حفظ شود (مثلاً «نوجوان ۱۵ ساله»).</p>
			<p><label>شناسه کمک (از تب کمک‌ها)<br><input type="number" name="donation_id" class="regular-text" required min="1"></label></p>
			<p><label>مبلغ هزینه‌شده<br><input type="number" name="spent_amount" class="regular-text" min="0"></label></p>
			<p><label>برچسب دریافت‌کننده<br><input type="text" name="beneficiary_label" class="widefat" placeholder="مثلاً: مادر خانواده در شهرستان…"></label></p>
			<p><label>صرف چه شد؟<br><textarea name="purpose" class="widefat" rows="3" placeholder="خرید عدسی عینک طبی + فریم ساده"></textarea></label></p>
			<p><label>پیام تشکر برای اهداکننده<br><textarea name="thank_you" class="widefat" rows="3" placeholder="با کمک شما، امروز کسی دوباره واضح می‌بیند. سپاسگزاریم."></textarea></label></p>
			<p><label>پیوست (رسید / عکس با رضایت)<br><input type="file" name="attachment" accept="image/*,.pdf"></label></p>
			<p><button type="submit" class="button button-primary">ثبت و اطلاع به اهداکننده</button> <span id="ezcd-impact-msg" class="ezcd-admin-msg"></span></p>
		</form>
	<?php endif; ?>
</div>
<script>
(function(){
	var nonce = '<?php echo esc_js( $nonce ); ?>';
	var caseForm = document.getElementById('ezcd-case-form');
	if(caseForm){
		caseForm.addEventListener('submit', function(e){
			e.preventDefault();
			var body = new FormData(caseForm);
			body.append('action','ezcd_admin_charity_case');
			body.append('nonce', nonce);
			fetch(ajaxurl,{method:'POST',credentials:'same-origin',body:body}).then(function(r){return r.json();}).then(function(res){
				var m = document.getElementById('ezcd-case-msg');
				m.textContent = (res && res.data && res.data.message) || (res && res.success ? 'ذخیره شد' : 'خطا');
				m.className = 'ezcd-admin-msg ' + (res && res.success ? 'is-ok' : 'is-err');
				if(res && res.success) setTimeout(function(){ location.reload(); }, 700);
			});
		});
	}
	var imp = document.getElementById('ezcd-impact-form');
	if(imp){
		imp.addEventListener('submit', function(e){
			e.preventDefault();
			var body = new FormData(imp);
			body.append('action','ezcd_admin_charity_impact');
			body.append('nonce', nonce);
			fetch(ajaxurl,{method:'POST',credentials:'same-origin',body:body}).then(function(r){return r.json();}).then(function(res){
				var m = document.getElementById('ezcd-impact-msg');
				m.textContent = (res && res.data && res.data.message) || (res && res.success ? 'ثبت شد' : 'خطا');
				m.className = 'ezcd-admin-msg ' + (res && res.success ? 'is-ok' : 'is-err');
			});
		});
	}
})();
</script>
