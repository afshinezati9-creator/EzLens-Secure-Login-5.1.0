<?php
/**
 * تب مرور کمپین — فاز ۳: آمار + میان‌بر + راهنما
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$mass = admin_url( 'admin.php?page=ezlens-cd-mass' );
$msg  = admin_url( 'admin.php?page=ezlens-auth-settings&tab=messaging' );
?>
<div class="campaign-tab-content ezc-panel">
	<div class="ezc-panel-head">
		<div>
			<h2>مرور کمپین</h2>
			<p class="ezc-muted">وضعیت ارسال‌ها و میان‌برهای پرکاربرد</p>
		</div>
		<button type="button" class="button campaign-tab-jump" data-tab="history">مشاهده تاریخچه کامل</button>
	</div>

	<div id="campaign-dashboard-stats" class="ezc-stat-grid">
		<div class="ezc-stat ezc-sk"><span>کل کمپین‌ها</span><strong>—</strong></div>
		<div class="ezc-stat ezc-sk"><span>ارسال‌شده</span><strong>—</strong></div>
		<div class="ezc-stat ezc-sk"><span>پیش‌نویس</span><strong>—</strong></div>
		<div class="ezc-stat ezc-sk"><span>زمان‌بندی‌شده</span><strong>—</strong></div>
	</div>

	<div class="ezc-quick-grid">
		<button type="button" class="ezc-quick campaign-tab-jump" data-tab="audience">
			<strong>مخاطبان و CSV</strong>
			<span>لیست خارجی، ایمپورت، گروه</span>
		</button>
		<button type="button" class="ezc-quick campaign-tab-jump" data-tab="create">
			<strong>ایجاد کمپین</strong>
			<span>SMS یا ایمیل روی لیست</span>
		</button>
		<a class="ezc-quick" href="<?php echo esc_url( $mass ); ?>">
			<strong>پیام به مشتریان</strong>
			<span>میان‌بر فقط کاربران سایت</span>
		</a>
		<a class="ezc-quick" href="<?php echo esc_url( $msg ); ?>">
			<strong>پیام‌رسانی</strong>
			<span>API، قالب SMS، ایمیل</span>
		</a>
	</div>

	<div class="ezc-callout">
		<p>
			<strong>یادآوری:</strong> کمپین برای لیست‌های داخل و خارج سایت است.
			پیام فوری روزمره به خریداران ثبت‌نام‌شده را از «پیام به مشتریان» بفرستید؛ از آنجا می‌توانید لیست کمپین هم بسازید.
		</p>
	</div>
</div>

	<div id="ezlens-queue-health" class="ezc-callout" style="margin-top:14px;">
		<p><strong>سلامت صف:</strong> <span id="ezlens-qh-text">در حال بررسی…</span></p>
	</div>
	<script>
	(function($){
		function run(){
			if (typeof ezlens_auth_ajax === 'undefined') return;
			$.post(ezlens_auth_ajax.ajax_url,{action:'ezlens_queue_health',nonce:ezlens_auth_ajax.nonce},function(res){
				if(!res||!res.success){ $('#ezlens-qh-text').text('در دسترس نیست'); return; }
				var s=res.data||{};
				var parts=[];
				parts.push(s.queue_tick_scheduled ? 'کرون تیک فعال' : 'کرون تیک نیست');
				parts.push('در حال ارسال: '+(s.sending_count||0));
				parts.push('زمان‌بندی: '+(s.scheduled_count||0));
				parts.push('گیرنده pending: '+(s.pending_recipients||0));
				if (s.failed_recipients_24h) parts.push('خطای ۲۴س: '+s.failed_recipients_24h);
				if (s.action_scheduler) parts.push('Action Scheduler: بله');
				var msg = parts.join(' · ');
				if (s.message) msg += ' — ' + s.message;
				$('#ezlens-qh-text').text(msg);
				if (s.status==='warn') $('#ezlens-queue-health').css({background:'#fff7ed',borderColor:'#fdba74'});
			});
		}
		setTimeout(run, 300);
	})(jQuery);
	</script>
