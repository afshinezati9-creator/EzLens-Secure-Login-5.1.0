<?php
/**
 * صندوق ایمیل‌ها — UI مینیمال با تب‌های AJAX
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'EzLens_Inbox' ) ) {
	$f = dirname( __FILE__ ) . '/../includes/class-inbox.php';
	if ( is_readable( $f ) ) {
		require_once $f;
	} else {
		wp_die( 'فایل class-inbox.php یافت نشد.' );
	}
}
if ( ! class_exists( 'EzLens_Inbox_Ajax' ) ) {
	$f = dirname( __FILE__ ) . '/../includes/class-ajax.php';
	if ( is_readable( $f ) ) {
		require_once $f;
	}
}

if ( ! current_user_can( 'manage_options' ) ) {
	wp_die( 'دسترسی غیرمجاز' );
}

$s        = EzLens_Inbox::get_settings();
$has_pass = EzLens_Inbox::has_password();
$imap_ok  = EzLens_Inbox::imap_available();
?>
<div class="wrap ezi-wrap" dir="rtl" id="ez-inbox-app">
	<header class="ezi-head">
		<div>
			<h1>صندوق ایمیل‌ها</h1>
			<p class="ezi-sub">مدیریت ایمیل‌های <?php echo esc_html( $s['username'] ); ?></p>
		</div>
		<div class="ezi-head-actions">
			<span class="ezi-pill <?php echo $imap_ok ? 'is-ok' : 'is-err'; ?>">
				<?php echo $imap_ok ? 'IMAP فعال' : 'IMAP غیرفعال'; ?>
			</span>
			<span class="ezi-pill <?php echo $has_pass ? 'is-ok' : 'is-warn'; ?>" id="ezi-pass-pill">
				<?php echo $has_pass ? 'رمز ذخیره شده' : 'رمز تنظیم نشده'; ?>
			</span>
		</div>
	</header>

	<nav class="ezi-tabs" role="tablist">
		<button type="button" class="ezi-tab is-active" data-tab="inbox">دریافتی</button>
		<button type="button" class="ezi-tab" data-tab="sent">ارسالی</button>
		<button type="button" class="ezi-tab" data-tab="settings">تنظیمات اتصال</button>
	</nav>

	<!-- دریافتی / ارسالی -->
	<section class="ezi-panel is-active" id="ezi-panel-mail" data-panels="inbox sent">
		<div class="ezi-toolbar">
			<div class="ezi-search">
				<input type="search" id="ezi-search" placeholder="جستجو در موضوع و متن…" />
			</div>
			<select id="ezi-filter">
				<option value="all">همه</option>
				<option value="unseen">خوانده‌نشده</option>
				<option value="seen">خوانده‌شده</option>
			</select>
			<button type="button" class="ezi-btn ezi-btn-primary" id="ezi-refresh">بروزرسانی</button>
		</div>
		<div class="ezi-pager" id="ezi-pager-top"></div>
		<div class="ezi-layout">
			<div class="ezi-list" id="ezi-list">
				<div class="ezi-empty">برای مشاهده، بروزرسانی را بزنید.</div>
			</div>
			<div class="ezi-reader" id="ezi-reader">
				<div class="ezi-empty">یک ایمیل را از لیست انتخاب کنید.</div>
			</div>
		</div>
		<div class="ezi-pager" id="ezi-pager"></div>
	</section>

	<!-- تنظیمات -->
	<section class="ezi-panel" id="ezi-panel-settings" data-panels="settings">
		<form class="ezi-card" id="ezi-settings-form">
			<div class="ezi-grid">
				<label class="ezi-field">
					<span>هاست IMAP</span>
					<input name="host" value="<?php echo esc_attr( $s['host'] ); ?>" required />
				</label>
				<label class="ezi-field">
					<span>پورت</span>
					<input name="port" type="number" value="<?php echo esc_attr( $s['port'] ); ?>" required />
				</label>
				<label class="ezi-field">
					<span>رمزنگاری</span>
					<select name="encrypt">
						<option value="ssl" <?php selected( $s['encrypt'], 'ssl' ); ?>>SSL</option>
						<option value="tls" <?php selected( $s['encrypt'], 'tls' ); ?>>TLS</option>
						<option value="none" <?php selected( $s['encrypt'], 'none' ); ?>>بدون</option>
					</select>
				</label>
				<label class="ezi-field">
					<span>نام کاربری</span>
					<input name="username" value="<?php echo esc_attr( $s['username'] ); ?>" required />
				</label>
				<label class="ezi-field ezi-field-full">
					<span>رمز عبور <?php echo $has_pass ? '<em class="ezi-hint">(ذخیره شده — برای تغییر رمز جدید بنویسید)</em>' : ''; ?></span>
					<input name="password" type="password" placeholder="<?php echo $has_pass ? '••••••••  (خالی = بدون تغییر)' : 'رمز ایمیل'; ?>" autocomplete="new-password" />
				</label>
				<label class="ezi-field">
					<span>پوشه دریافتی</span>
					<input name="mailbox" value="<?php echo esc_attr( $s['mailbox'] ); ?>" />
				</label>
				<label class="ezi-field">
					<span>پوشه ارسالی</span>
					<input name="sent_box" value="<?php echo esc_attr( $s['sent_box'] ); ?>" placeholder="INBOX.Sent" />
				</label>
			</div>
			<div class="ezi-form-actions">
				<button type="submit" class="ezi-btn ezi-btn-primary">ذخیره تنظیمات</button>
				<button type="button" class="ezi-btn" id="ezi-test">تست اتصال</button>
				<span class="ezi-status" id="ezi-settings-status"></span>
			</div>
		</form>
	</section>
</div>
