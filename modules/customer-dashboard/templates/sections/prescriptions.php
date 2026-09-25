<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$rows    = EzLens_CD_Prescriptions::list_for_user();
$profile = EzLens_CD_Prescriptions::get_profile();
$ptab    = isset( $_GET['ptab'] ) ? sanitize_key( $_GET['ptab'] ) : 'list';
if ( ! in_array( $ptab, array( 'list', 'profile', 'manual', 'upload' ), true ) ) {
	$ptab = 'list';
}
$conds = array(
	'keratoconus' => array(
		'label' => 'قوز قرنیه',
		'help'  => 'قرنیه به شکل مخروطی نازک می‌شود و ممکن است دید تار یا دوبینی ایجاد کند. برای انتخاب لنز مخصوص مهم است.',
	),
	'dry_eye'     => array(
		'label' => 'خشکی چشم',
		'help'  => 'اشک کافی یا باکیفیت کم است؛ سوزش، احساس جسم خارجی یا خستگی چشم رایج است. روی راحتی لنز اثر دارد.',
	),
	'cataract'    => array(
		'label' => 'آب مروارید',
		'help'  => 'کدر شدن عدسی چشم که باعث تار دیدن و حساسیت به نور می‌شود. اگر عمل کرده‌اید در یادداشت بنویسید.',
	),
	'glaucoma'    => array(
		'label' => 'گلوکوم (آب سیاه)',
		'help'  => 'فشار داخل چشم بالاست و به عصب بینایی فشار می‌آورد. داروها و مراقبت‌های خاص ممکن است لازم باشد.',
	),
	'other'       => array(
		'label' => 'سایر',
		'help'  => 'هر مشکل چشمی دیگری که در لیست نیست؛ جزئیات را در بخش یادداشت بنویسید.',
	),
);
$types = array(
	'glasses' => array( 'label' => 'نسخه عینک', 'hint' => 'عددهای Sph و Cyl و Axis و PD را از برگه پزشک بردارید.' ),
	'contact' => array( 'label' => 'لنز تماسی', 'hint' => 'روی نسخه لنز معمولاً BC و DIA هم هست.' ),
	'scleral' => array( 'label' => 'لنز اسکلرال', 'hint' => 'اگر مطمئن نیستید، از تب آپلود عکس استفاده کنید.' ),
	'other'   => array( 'label' => 'سایر', 'hint' => 'هر توضیح یا فایلی که دارید کافی است.' ),
);
$help_ico = function_exists( 'ezcd_icon' ) ? ezcd_icon( 'help-circle' ) : '?';
?>
<div class="ezcd-rx" data-ptab="<?php echo esc_attr( $ptab ); ?>">
	<div class="ezcd-rx-intro ezcd-rx-intro-fun">
		<span class="ezcd-ico"><?php echo ezcd_icon( 'eye' ); ?></span>
		<div>
			<strong>پرونده بینایی</strong>
			<p class="ezcd-muted">اینجا آرشیو نسخه‌های شماست — پر کردنش سخت نیست؛ یا عدد می‌نویسید یا فقط عکس می‌فرستید.</p>
		</div>
	</div>

	<nav class="ezcd-rx-tabs" role="tablist">
		<button type="button" class="ezcd-rx-tab<?php echo 'list' === $ptab ? ' is-active' : ''; ?>" data-ptab="list" role="tab" aria-selected="<?php echo 'list' === $ptab ? 'true' : 'false'; ?>">
			<span class="ezcd-tab-ico"><?php echo ezcd_icon( 'list' ); ?></span> نسخه‌ها
		</button>
		<button type="button" class="ezcd-rx-tab<?php echo 'profile' === $ptab ? ' is-active' : ''; ?>" data-ptab="profile" role="tab" aria-selected="<?php echo 'profile' === $ptab ? 'true' : 'false'; ?>">
			<span class="ezcd-tab-ico"><?php echo ezcd_icon( 'user' ); ?></span> اطلاعات پایه
		</button>
		<button type="button" class="ezcd-rx-tab<?php echo 'manual' === $ptab ? ' is-active' : ''; ?>" data-ptab="manual" role="tab" aria-selected="<?php echo 'manual' === $ptab ? 'true' : 'false'; ?>">
			<span class="ezcd-tab-ico"><?php echo ezcd_icon( 'edit-3' ); ?></span> ورود دستی
		</button>
		<button type="button" class="ezcd-rx-tab<?php echo 'upload' === $ptab ? ' is-active' : ''; ?>" data-ptab="upload" role="tab" aria-selected="<?php echo 'upload' === $ptab ? 'true' : 'false'; ?>">
			<span class="ezcd-tab-ico"><?php echo ezcd_icon( 'upload' ); ?></span> آپلود نسخه
		</button>
	</nav>

	<!-- Panel: list / history -->
	<div class="ezcd-rx-panel ezcd-rx-history-palette<?php echo 'list' !== $ptab ? ' is-hidden' : ''; ?>" data-panel="list" role="tabpanel">
		<?php if ( empty( $rows ) ) : ?>
			<div class="ezcd-empty ezcd-empty-rich">
				<div class="ezcd-empty-ico"><?php echo ezcd_icon( 'eye' ); ?></div>
				<p>هنوز نسخه‌ای ندارید. از تب «ورود دستی» یا «آپلود نسخه» شروع کنید — کمتر از یک دقیقه وقت می‌گیرد.</p>
				<div class="ezcd-empty-actions">
					<button type="button" class="ezcd-btn ezcd-btn-grad ezcd-rx-tab-jump" data-ptab="upload">آپلود سریع نسخه</button>
					<button type="button" class="ezcd-btn ezcd-btn-ghost ezcd-rx-tab-jump" data-ptab="manual">ورود دستی</button>
				</div>
			</div>
		<?php else : ?>
			<div class="ezcd-rx-list">
				<?php foreach ( $rows as $row ) :
					$expired = false;
					if ( ! empty( $row->issued_at ) ) {
						$ts = strtotime( $row->issued_at );
						if ( $ts && ( time() - $ts ) > ( 365 * DAY_IN_SECONDS ) ) {
							$expired = true;
						}
					}
					$visited = ! empty( $row->visited_at ) ? $row->visited_at : '';
					?>
					<article class="ezcd-rx-card<?php echo $expired ? ' is-expired' : ''; ?>" data-id="<?php echo esc_attr( $row->id ); ?>">
						<div class="ezcd-rx-card-top">
							<div>
								<strong><?php echo esc_html( EzLens_CD_Prescriptions::type_label( $row->rx_type ) ); ?></strong>
								<span class="ezcd-rx-date">صدور: <?php echo $row->issued_at ? esc_html( ezcd_fa( $row->issued_at ) ) : '—'; ?></span>
								<?php if ( $visited ) : ?>
									<span class="ezcd-rx-date">مراجعه: <?php echo esc_html( ezcd_fa( $visited ) ); ?></span>
								<?php endif; ?>
							</div>
							<?php if ( $expired ) : ?><span class="ezcd-rx-badge">منقضی</span><?php endif; ?>
						</div>
						<?php if ( $row->doctor_name ) : ?>
							<p class="ezcd-muted">پزشک: <?php echo esc_html( $row->doctor_name ); ?></p>
						<?php endif; ?>
						<?php if ( empty( $row->od_sph ) && empty( $row->os_sph ) && $row->attachment_id ) : ?>
							<p class="ezcd-muted">ثبت‌شده با فایل تصویری</p>
						<?php else : ?>
							<div class="ezcd-rx-grid">
								<div><span class="ezcd-rx-eye">OD</span><span>Sph <?php echo esc_html( $row->od_sph ?: '—' ); ?></span><span>Cyl <?php echo esc_html( $row->od_cyl ?: '—' ); ?></span><span>Axis <?php echo esc_html( $row->od_axis ?: '—' ); ?></span></div>
								<div><span class="ezcd-rx-eye">OS</span><span>Sph <?php echo esc_html( $row->os_sph ?: '—' ); ?></span><span>Cyl <?php echo esc_html( $row->os_cyl ?: '—' ); ?></span><span>Axis <?php echo esc_html( $row->os_axis ?: '—' ); ?></span></div>
							</div>
						<?php endif; ?>
						<?php if ( $row->attachment_id ) :
							$url = wp_get_attachment_url( (int) $row->attachment_id );
							if ( $url ) : ?><p><a class="ezcd-link" href="<?php echo esc_url( $url ); ?>" target="_blank" rel="noopener">مشاهده فایل</a></p><?php endif;
						endif; ?>
						<div class="ezcd-order-actions">
							<button type="button" class="ezcd-btn ezcd-btn-ghost ezcd-rx-edit" data-rx="<?php echo esc_attr( wp_json_encode( $row ) ); ?>">ویرایش</button>
							<button type="button" class="ezcd-btn ezcd-btn-danger-soft ezcd-rx-del" data-id="<?php echo esc_attr( $row->id ); ?>">حذف</button>
						</div>
					</article>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
	</div>

	<!-- Panel: profile -->
	<div class="ezcd-rx-panel<?php echo 'profile' !== $ptab ? ' is-hidden' : ''; ?>" data-panel="profile" role="tabpanel">
		<form class="ezcd-form ezcd-card-panel ezcd-rx-box" id="ezcd-patient-profile-form">
			<p class="ezcd-hint">اختیاری است؛ فقط برای دقت بیشتر در توصیه‌ها.</p>
			<div class="ezcd-form-row">
				<label>جنسیت
					<select name="gender">
						<option value="">انتخاب کنید</option>
						<option value="female" <?php selected( $profile['gender'], 'female' ); ?>>زن</option>
						<option value="male" <?php selected( $profile['gender'], 'male' ); ?>>مرد</option>
						<option value="other" <?php selected( $profile['gender'], 'other' ); ?>>سایر</option>
					</select>
				</label>
				<label>سال تولد
					<input type="text" name="birth_year" value="<?php echo esc_attr( $profile['birth_year'] ); ?>" placeholder="مثال: ۱۳۷۰" inputmode="numeric">
				</label>
			</div>

			<div class="ezcd-switch-box">
				<div class="ezcd-switch-box-head">
					<strong>سوابق مرتبط</strong>
					<span class="ezcd-muted">هر موردی که دارید را روشن کنید</span>
				</div>
				<div class="ezcd-switch-grid">
					<?php foreach ( $conds as $ck => $cinfo ) :
						$on = in_array( $ck, (array) $profile['conditions'], true );
						?>
						<label class="ezcd-switch<?php echo $on ? ' is-on' : ''; ?>">
							<input type="checkbox" name="conditions[]" value="<?php echo esc_attr( $ck ); ?>" <?php checked( $on ); ?>>
							<span class="ezcd-switch-track" aria-hidden="true"><span class="ezcd-switch-thumb"></span></span>
							<span class="ezcd-switch-body">
								<span class="ezcd-switch-title">
									<?php echo esc_html( $cinfo['label'] ); ?>
									<span class="ezcd-tip" tabindex="0" aria-label="راهنما">
										<span class="ezcd-tip-ico"><?php echo $help_ico; ?></span>
										<span class="ezcd-tip-pop" role="tooltip"><?php echo esc_html( $cinfo['help'] ); ?></span>
									</span>
								</span>
							</span>
						</label>
					<?php endforeach; ?>
				</div>
			</div>

			<label>حساسیت‌ها<textarea name="allergies" rows="2" placeholder="اگر چیزی نیست خالی بگذارید"><?php echo esc_textarea( $profile['allergies'] ); ?></textarea></label>
			<label>یادداشت<textarea name="family_note" rows="2" placeholder="نکته‌ای برای تیم پشتیبانی"><?php echo esc_textarea( $profile['family_note'] ); ?></textarea></label>
			<button type="submit" class="ezcd-btn ezcd-btn-grad">ذخیره اطلاعات پایه</button>
		</form>
	</div>

	<!-- Panel: manual entry -->
	<div class="ezcd-rx-panel<?php echo 'manual' !== $ptab ? ' is-hidden' : ''; ?>" data-panel="manual" role="tabpanel">
		<form class="ezcd-form ezcd-card-panel ezcd-rx-box" id="ezcd-rx-form-manual">
			<input type="hidden" name="id" value="">
			<input type="hidden" name="mode" value="manual">
			<p class="ezcd-hint">عددها را دقیق مثل نسخه بنویسید. تاریخ را شمسی وارد کنید (مثال: ۱۴۰۳/۰۷/۲۴).</p>
			<label>نوع نسخه
				<select name="rx_type" id="ezcd-rx-type-manual">
					<?php foreach ( $types as $tk => $tv ) : ?>
						<option value="<?php echo esc_attr( $tk ); ?>" data-hint="<?php echo esc_attr( $tv['hint'] ); ?>"><?php echo esc_html( $tv['label'] ); ?></option>
					<?php endforeach; ?>
				</select>
			</label>
			<p class="ezcd-hint" id="ezcd-rx-type-hint-m"><?php echo esc_html( $types['glasses']['hint'] ); ?></p>
			<div class="ezcd-form-row">
				<label>تاریخ صدور (شمسی)<input type="text" name="issued_at_jalali" placeholder="۱۴۰۳/۰۷/۲۴" dir="ltr"></label>
				<label>تاریخ مراجعه (شمسی)<input type="text" name="visited_at_jalali" placeholder="۱۴۰۳/۰۷/۲۰" dir="ltr"></label>
			</div>
			<label>نام پزشک<input type="text" name="doctor_name" placeholder="اختیاری"></label>
			<div class="ezcd-rx-eyes">
				<fieldset class="ezcd-rx-eye-box">
					<legend>چشم راست (OD)</legend>
					<div class="ezcd-form-row">
						<label>Sph<input type="text" name="od_sph" placeholder="-۲.۵۰" dir="ltr"></label>
						<label>Cyl<input type="text" name="od_cyl" placeholder="-۰.۷۵" dir="ltr"></label>
						<label>Axis<input type="text" name="od_axis" placeholder="۱۸۰" dir="ltr"></label>
					</div>
				</fieldset>
				<fieldset class="ezcd-rx-eye-box">
					<legend>چشم چپ (OS)</legend>
					<div class="ezcd-form-row">
						<label>Sph<input type="text" name="os_sph" placeholder="-۱.۷۵" dir="ltr"></label>
						<label>Cyl<input type="text" name="os_cyl" placeholder="-۰.۲۵" dir="ltr"></label>
						<label>Axis<input type="text" name="os_axis" placeholder="۱۷۰" dir="ltr"></label>
					</div>
				</fieldset>
			</div>
			<div class="ezcd-form-row ezcd-rx-extra-contact is-hidden">
				<label>BC<input type="text" name="bc" placeholder="۸.۶" dir="ltr"></label>
				<label>DIA<input type="text" name="dia" placeholder="۱۴.۲" dir="ltr"></label>
			</div>
			<label>PD<input type="text" name="pd" placeholder="۶۲" dir="ltr"></label>
			<label>یادداشت<textarea name="notes" rows="2" placeholder="مثال: برای کار با مانیتور"></textarea></label>
			<button type="submit" class="ezcd-btn ezcd-btn-grad">ذخیره نسخه</button>
		</form>
	</div>

	<!-- Panel: upload -->
	<div class="ezcd-rx-panel<?php echo 'upload' !== $ptab ? ' is-hidden' : ''; ?>" data-panel="upload" role="tabpanel">
		<form class="ezcd-form ezcd-card-panel ezcd-rx-box" id="ezcd-rx-form-upload">
			<input type="hidden" name="id" value="">
			<input type="hidden" name="mode" value="upload">
			<input type="hidden" name="attachment_id" id="ezcd-rx-attachment" value="">
			<p class="ezcd-hint">عکس واضح بگیرید؛ نور کافی باشد و عددها خوانا باشند. PDF هم قبول است. تیم ما بررسی می‌کند.</p>
			<label>نوع نسخه
				<select name="rx_type">
					<?php foreach ( $types as $tk => $tv ) : ?>
						<option value="<?php echo esc_attr( $tk ); ?>"><?php echo esc_html( $tv['label'] ); ?></option>
					<?php endforeach; ?>
				</select>
			</label>
			<div class="ezcd-form-row">
				<label>تاریخ صدور (شمسی)<input type="text" name="issued_at_jalali" placeholder="۱۴۰۳/۰۷/۲۴" dir="ltr"></label>
				<label>تاریخ مراجعه (شمسی)<input type="text" name="visited_at_jalali" placeholder="۱۴۰۳/۰۷/۲۰" dir="ltr"></label>
			</div>
			<label>نام پزشک<input type="text" name="doctor_name" placeholder="اختیاری"></label>
			<label class="ezcd-dropzone" id="ezcd-rx-dropzone">
				<input type="file" id="ezcd-rx-file" accept="image/*,.pdf" hidden>
				<span class="ezcd-drop-ico"><?php echo ezcd_icon( 'upload' ); ?></span>
				<span class="ezcd-drop-title">انتخاب یا رها کردن فایل نسخه</span>
				<span class="ezcd-drop-sub">JPG، PNG یا PDF</span>
				<span class="ezcd-drop-name" id="ezcd-rx-file-name"></span>
			</label>
			<label>یادداشت<textarea name="notes" rows="2" placeholder="اگر نکته‌ای هست بنویسید"></textarea></label>
			<button type="submit" class="ezcd-btn ezcd-btn-grad">ثبت نسخه با فایل</button>
		</form>
	</div>
</div>
