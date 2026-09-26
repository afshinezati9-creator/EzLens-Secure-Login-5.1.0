<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
// Self-heal legacy installations: wallet tables must exist before any queries.
if ( class_exists( 'EzLens_CD_Wallet' ) ) {
	EzLens_CD_Wallet::maybe_create_table();
}
if ( class_exists( 'EzLens_CD_Wallet_Deposits' ) ) {
	EzLens_CD_Wallet_Deposits::maybe_create_table();
}

$balance  = class_exists( 'EzLens_CD_Wallet' ) ? EzLens_CD_Wallet::balance() : 0;
$history  = class_exists( 'EzLens_CD_Wallet' ) ? EzLens_CD_Wallet::history() : array();
$deposits = class_exists( 'EzLens_CD_Wallet_Deposits' ) ? EzLens_CD_Wallet_Deposits::for_user() : array();
$wallet_settings = class_exists( 'EzLens_CD_Wallet_Settings' ) ? EzLens_CD_Wallet_Settings::bank_account() : array();
$wallet_methods  = class_exists( 'EzLens_CD_Wallet_Settings' ) ? EzLens_CD_Wallet_Settings::enabled_methods() : array();
$bank = '';
if ( ! empty( $wallet_settings['bank_name'] ) || ! empty( $wallet_settings['card_number'] ) || ! empty( $wallet_settings['account_number'] ) || ! empty( $wallet_settings['iban'] ) ) {
	$bank .= '<div class="ezcd-bank-grid">';
	if ( ! empty( $wallet_settings['bank_name'] ) ) $bank .= '<div><span>بانک</span><strong>' . esc_html( $wallet_settings['bank_name'] ) . '</strong></div>';
	if ( ! empty( $wallet_settings['owner'] ) ) $bank .= '<div><span>صاحب حساب</span><strong>' . esc_html( $wallet_settings['owner'] ) . '</strong></div>';
	if ( ! empty( $wallet_settings['account_name'] ) ) $bank .= '<div><span>عنوان حساب</span><strong>' . esc_html( $wallet_settings['account_name'] ) . '</strong></div>';
	if ( ! empty( $wallet_settings['card_number'] ) ) $bank .= '<div><span>شماره کارت</span><strong dir="ltr">' . esc_html( $wallet_settings['card_number'] ) . '</strong></div>';
	if ( ! empty( $wallet_settings['account_number'] ) ) $bank .= '<div><span>شماره حساب</span><strong dir="ltr">' . esc_html( $wallet_settings['account_number'] ) . '</strong></div>';
	if ( ! empty( $wallet_settings['iban'] ) ) $bank .= '<div><span>شماره شبا</span><strong dir="ltr">' . esc_html( $wallet_settings['iban'] ) . '</strong></div>';
	if ( ! empty( $wallet_settings['note'] ) ) $bank .= '<p>' . esc_html( $wallet_settings['note'] ) . '</p>';
	$bank .= '</div>';
}
$fa_bal   = function_exists( 'ezcd_fa' ) ? ezcd_fa( number_format_i18n( (int) $balance ) ) : number_format_i18n( (int) $balance );

// Query flags after gateway return
$topup_ok   = isset( $_GET['ezcd_topup'] ) && 'ok' === sanitize_key( wp_unslash( $_GET['ezcd_topup'] ) );
$topup_fail = isset( $_GET['ezcd_topup'] ) && 'fail' === sanitize_key( wp_unslash( $_GET['ezcd_topup'] ) );
$topup_amt  = isset( $_GET['amt'] ) ? absint( $_GET['amt'] ) : 0;
?>
<div class="ezcd-wallet">
	<div class="ezcd-wallet-hero">
		<span class="ezcd-ico"><?php echo function_exists( 'ezcd_icon' ) ? ezcd_icon( 'wallet' ) : ''; ?></span>
		<div>
			<p class="ezcd-muted">موجودی قابل استفاده</p>
			<strong class="ezcd-wallet-balance"><?php echo esc_html( $fa_bal ); ?> تومان</strong>
		</div>
	</div>

	<?php if ( $topup_ok ) : ?>
		<div class="ezcd-alert ezcd-alert-ok" role="status">
			<strong>شارژ موفق</strong>
			<p>پرداخت تأیید شد<?php echo $topup_amt ? ' و مبلغ ' . esc_html( function_exists( 'ezcd_fa' ) ? ezcd_fa( number_format_i18n( $topup_amt ) ) : number_format_i18n( $topup_amt ) ) . ' تومان' : ''; ?> بدون نیاز به تأیید مدیر به کیف پول شما اضافه شد.</p>
		</div>
	<?php elseif ( $topup_fail ) : ?>
		<div class="ezcd-alert ezcd-alert-err" role="alert">
			<strong>پرداخت ناموفق یا ناتمام</strong>
			<p>اگر مبلغ از حسابتان کم شده، از بخش پشتیبانی تیکت بزنید تا پیگیری شود. شماره پیگیری درگاه یا ۴ رقم آخر کارت را در تیکت بنویسید.</p>
			<a class="ezcd-btn ezcd-btn-ghost" href="<?php echo esc_url( function_exists( 'wc_get_account_endpoint_url' ) ? wc_get_account_endpoint_url( 'support' ) : home_url( '/my-account/support/' ) ); ?>">ثبت تیکت پشتیبانی</a>
		</div>
	<?php endif; ?>

	<div class="ezcd-section-block ezcd-card-panel">
		<h2 class="ezcd-h2">شارژ کیف پول</h2>
		<?php if ( $bank ) : ?>
			<div class="ezcd-bank-box"><?php echo wp_kses_post( wpautop( $bank ) ); ?></div>
		<?php endif; ?>

		<form class="ezcd-form" id="ezcd-wallet-deposit-form" enctype="multipart/form-data" novalidate>
			<label class="ezcd-amt-label">مبلغ شارژ (تومان)
				<input type="text" name="amount" id="ezcd-dep-amount" inputmode="numeric" placeholder="مثلاً 500,000" required autocomplete="off" dir="ltr" class="ezcd-amt-input">
				<small class="ezcd-amt-hint" id="ezcd-dep-amount-hint">حداقل ۱۰٬۰۰۰ تومان</small>
				<small class="ezcd-amt-words" id="ezcd-dep-amount-words" aria-live="polite"></small>
			</label>

			<p class="ezcd-method-label">روش پرداخت را انتخاب کنید</p>
			<div class="ezcd-method-grid" role="radiogroup" aria-label="روش پرداخت">
				<?php $first_method = ''; foreach ( array( 'online' => array( 'credit-card', 'درگاه پرداخت', 'پرداخت آنلاین — شارژ خودکار بعد از موفقیت' ), 'bank' => array( 'globe', 'اینترنت‌بانک', 'اسکرین یا شناسه پرداخت بفرستید' ), 'card' => array( 'receipt', 'کارت به کارت / فیش', 'فیش واریز را آپلود کنید' ) as $method_key => $meta ) : if ( ! isset( $wallet_methods[ $method_key ] ) ) continue; if ( '' === $first_method ) $first_method = $method_key; ?>
				<button type="button" class="ezcd-method-card<?php echo $first_method === $method_key ? ' is-active' : ''; ?>" data-method="<?php echo esc_attr( $method_key ); ?>" aria-pressed="<?php echo $first_method === $method_key ? 'true' : 'false'; ?>">
					<span class="ezcd-method-ico"><?php echo function_exists( 'ezcd_icon' ) ? ezcd_icon( $meta[0] ) : ''; ?></span>
					<strong><?php echo esc_html( $meta[1] ); ?></strong>
					<small><?php echo esc_html( $meta[2] ); ?></small>
				</button>
				<?php endforeach; ?>
			</div>
			<?php if ( ! empty( $first_method ) ) : ?><input type="hidden" name="method" id="ezcd-dep-method" value="<?php echo esc_attr( $first_method ); ?>"><?php else : ?><div class="ezcd-alert ezcd-alert-err">در حال حاضر هیچ روش شارژی برای کیف پول فعال نشده است.</div><?php endif; ?>

			<?php if ( isset( $wallet_methods['online'] ) ) : ?>
			<!-- Online panel -->
			<div class="ezcd-dep-panel is-open" data-dep-panel="online" id="ezcd-dep-panel-online">
				<div class="ezcd-dep-summary">
					<span>مبلغ قابل پرداخت</span>
					<strong id="ezcd-dep-online-amt">—</strong>
				</div>
				<p class="ezcd-hint">پس از پرداخت موفق، به همین صفحه برمی‌گردید و موجودی بدون تأیید مدیر شارژ می‌شود. اگر پرداخت ناموفق بود و پول کم شد، تیکت پشتیبانی بزنید.</p>
				<button type="submit" class="ezcd-btn ezcd-btn-grad" id="ezcd-dep-submit-online">پرداخت از درگاه</button>
			</div>
			<?php endif; ?>

			<?php if ( isset( $wallet_methods['bank'] ) ) : ?>
			<!-- Bank panel -->
			<div class="ezcd-dep-panel" data-dep-panel="bank" id="ezcd-dep-panel-bank" hidden>
				<p class="ezcd-hint">بعد از واریز اینترنت‌بانک، شناسه پیگیری یا اسکرین‌شات را بفرستید. پس از تأیید سریع مدیر، مبلغ به کیف پول اضافه می‌شود.</p>
				<label>شناسه / کد پیگیری پرداخت <span class="ezcd-req">*</span>
					<input type="text" name="ref_code" id="ezcd-dep-ref" placeholder="مثلاً شماره پیگیری بانک" autocomplete="off">
				</label>
				<label class="ezcd-dropzone" id="ezcd-deposit-drop-bank">
					<input type="file" name="receipt" id="ezcd-deposit-file-bank" accept="image/*,.pdf" hidden>
					<span class="ezcd-drop-title">آپلود اسکرین پرداخت (اختیاری ولی بهتر است)</span>
					<span class="ezcd-drop-name" id="ezcd-deposit-file-name-bank"></span>
				</label>
				<button type="submit" class="ezcd-btn ezcd-btn-grad" id="ezcd-dep-submit-bank">ثبت درخواست بررسی</button>
			</div>
			<?php endif; ?>

			<?php if ( isset( $wallet_methods['card'] ) ) : ?>
			<!-- Card panel -->
			<div class="ezcd-dep-panel" data-dep-panel="card" id="ezcd-dep-panel-card" hidden>
				<p class="ezcd-hint">کارت‌به‌کارت کنید و فیش را بفرستید. بعد از پیگیری و تأیید، مبلغ به کیف پولتان اضافه می‌شود.</p>
				<label>شماره رسید / کد پیگیری <span class="ezcd-req">*</span>
					<input type="text" name="ref_code_card" id="ezcd-dep-ref-card" placeholder="شماره پیگیری یا زمان واریز" autocomplete="off">
				</label>
				<label class="ezcd-dropzone" id="ezcd-deposit-drop">
					<input type="file" name="receipt_card" id="ezcd-deposit-file" accept="image/*,.pdf" hidden>
					<span class="ezcd-drop-title">آپلود فیش واریز *</span>
					<span class="ezcd-drop-name" id="ezcd-deposit-file-name"></span>
				</label>
				<button type="submit" class="ezcd-btn ezcd-btn-grad" id="ezcd-dep-submit-card">ارسال فیش و ثبت درخواست</button>
			</div>
			<?php endif; ?>

			<p class="ezcd-hint" id="ezcd-dep-msg" aria-live="polite"></p>
		</form>
	</div>

	<?php if ( $deposits ) : ?>
		<div class="ezcd-section-block">
			<h2 class="ezcd-h2">درخواست‌های شارژ</h2>
			<div class="ezcd-deposit-list">
				<?php foreach ( $deposits as $d ) :
					$st = isset( $d->status ) ? $d->status : 'pending';
					$methods = class_exists( 'EzLens_CD_Wallet_Deposits' ) ? EzLens_CD_Wallet_Deposits::methods() : array();
					$ml = isset( $methods[ $d->method ] ) ? $methods[ $d->method ] : $d->method;
					?>
					<div class="ezcd-deposit-item st-<?php echo esc_attr( $st ); ?>">
						<strong><?php echo esc_html( function_exists( 'ezcd_fa' ) ? ezcd_fa( number_format_i18n( (int) $d->amount ) ) : number_format_i18n( (int) $d->amount ) ); ?> تومان</strong>
						<span><?php echo esc_html( $ml ); ?></span>
						<span class="ezcd-badge"><?php echo esc_html( EzLens_CD_Wallet_Deposits::status_label( $st ) ); ?></span>
						<?php if ( ! empty( $d->ref_code ) ) : ?>
							<small class="ezcd-muted">پیگیری: <?php echo esc_html( $d->ref_code ); ?></small>
						<?php endif; ?>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
	<?php endif; ?>

	<?php if ( $history ) : ?>
		<div class="ezcd-section-block">
			<h2 class="ezcd-h2">گردش حساب</h2>
			<div class="ezcd-ledger">
				<?php foreach ( $history as $h ) :
					$sign = ( 'credit' === $h->entry_type ) ? '+' : '−';
					$cls  = ( 'credit' === $h->entry_type ) ? 'is-credit' : 'is-debit';
					?>
					<div class="ezcd-ledger-row <?php echo esc_attr( $cls ); ?>">
						<span><?php echo esc_html( EzLens_CD_Wallet::reason_label( $h->reason ) ); ?></span>
						<strong><?php echo esc_html( $sign . ( function_exists( 'ezcd_fa' ) ? ezcd_fa( number_format_i18n( (int) $h->amount ) ) : number_format_i18n( (int) $h->amount ) ) ); ?></strong>
						<small class="ezcd-muted"><?php echo esc_html( $h->created_at ); ?><?php echo $h->note ? ' — ' . esc_html( $h->note ) : ''; ?></small>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
	<?php endif; ?>
</div>
