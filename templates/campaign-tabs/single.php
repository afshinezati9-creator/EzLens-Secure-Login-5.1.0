<?php
/**
 * ارسال تکی SMS / ایمیل — به یک مشتری یا مخاطب دستی
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$has_sms  = class_exists( 'EzLens_Auth_Messaging' );
$has_mail = true;
?>
<div class="campaign-tab-content ezc-panel ezc-single-send">
	<div class="ezc-panel-head">
		<div>
			<h2>ارسال تکی</h2>
			<p class="ezc-muted">یک پیامک یا ایمیل به یک نفر — مشتری سایت یا شماره/ایمیل دستی. برای لیست انبوه از تب ایجاد کمپین استفاده کنید.</p>
		</div>
	</div>

	<div class="ezc-single-grid">
		<section class="ezc-single-card">
			<h3>گیرنده</h3>
			<div class="ezc-field">
				<label>منبع</label>
				<select id="ezs-source">
					<option value="manual">ورود دستی</option>
					<option value="user">جستجوی کاربر سایت</option>
				</select>
			</div>
			<div id="ezs-manual-fields">
				<div class="ezc-field">
					<label>نام (اختیاری)</label>
					<input type="text" id="ezs-name" placeholder="مثلاً افشین">
				</div>
				<div class="ezc-field">
					<label>موبایل</label>
					<input type="text" id="ezs-phone" placeholder="09xxxxxxxxx" dir="ltr" inputmode="tel">
				</div>
				<div class="ezc-field">
					<label>ایمیل</label>
					<input type="email" id="ezs-email" placeholder="name@example.com" dir="ltr">
				</div>
			</div>
			<div id="ezs-user-fields" style="display:none;">
				<div class="ezc-field">
					<label>جستجو (نام / موبایل / ایمیل)</label>
					<input type="search" id="ezs-user-q" placeholder="حداقل ۲ کاراکتر…">
				</div>
				<div id="ezs-user-results" class="ezc-user-results"></div>
				<input type="hidden" id="ezs-user-id" value="">
			</div>
		</section>

		<section class="ezc-single-card">
			<h3>پیام</h3>
			<div class="ezc-field">
				<label>کانال</label>
				<select id="ezs-channel">
					<option value="sms">پیامک</option>
					<option value="email">ایمیل</option>
				</select>
			</div>
			<div class="ezc-field" id="ezs-subject-wrap" style="display:none;">
				<label>موضوع ایمیل</label>
				<input type="text" id="ezs-subject" placeholder="موضوع">
			</div>
			<div class="ezc-field">
				<label>متن پیام</label>
				<textarea id="ezs-body" rows="6" placeholder="متن پیام… می‌توانید {name} بگذارید"></textarea>
			</div>
			<p class="ezc-hint">ارسال از مسیر Messaging کمپین (تنظیمات پیام‌رسانی). برای OTP جدا است.</p>
			<button type="button" class="button button-primary ezc-btn-primary" id="ezs-send">ارسال</button>
			<span id="ezs-status" class="ezc-muted" style="margin-right:10px;"></span>
		</section>
	</div>
</div>
<style>
.ezc-single-grid{display:grid;grid-template-columns:1fr 1fr;gap:16px}
@media(max-width:900px){.ezc-single-grid{grid-template-columns:1fr}}
.ezc-single-card{background:#f8fafc;border:1px solid #e2e8f0;border-radius:14px;padding:16px 18px}
.ezc-single-card h3{margin:0 0 12px;font-size:15px;color:#031f8a}
.ezc-single-card .ezc-field{margin-bottom:12px}
.ezc-single-card label{display:block;font-size:12px;font-weight:600;color:#475569;margin-bottom:4px}
.ezc-single-card input,.ezc-single-card select,.ezc-single-card textarea{
	width:100%;border:1px solid #e2e8f0;border-radius:10px;padding:8px 12px;font-size:13px;background:#fff
}
.ezc-user-results{max-height:180px;overflow:auto;margin-top:8px}
.ezc-user-results button{
	display:block;width:100%;text-align:right;border:1px solid #e2e8f0;background:#fff;
	padding:8px 10px;margin-bottom:6px;border-radius:8px;cursor:pointer;font-size:12px
}
.ezc-user-results button:hover{border-color:#93c5fd;background:#eff6ff}
</style>
<script>
(function($){
	function ch(){
		var c=$('#ezs-channel').val();
		$('#ezs-subject-wrap').toggle(c==='email');
	}
	$('#ezs-channel').on('change', ch); ch();
	$('#ezs-source').on('change', function(){
		var v=$(this).val();
		$('#ezs-manual-fields').toggle(v==='manual');
		$('#ezs-user-fields').toggle(v==='user');
	});
	var t=null;
	$('#ezs-user-q').on('input', function(){
		var q=$(this).val();
		clearTimeout(t);
		if(q.length<2){ $('#ezs-user-results').empty(); return; }
		t=setTimeout(function(){
			$.post(ezlens_auth_ajax.ajax_url,{
				action:'ezlens_campaign_search_users',
				nonce:ezlens_auth_ajax.nonce,
				q:q
			},function(res){
				var $box=$('#ezs-user-results').empty();
				if(!res||!res.success){ $box.text('یافت نشد'); return; }
				(res.data.users||[]).forEach(function(u){
					$('<button type="button"/>').text(u.label)
						.data('u',u)
						.on('click',function(){
							var u=$(this).data('u');
							$('#ezs-user-id').val(u.id);
							$('#ezs-name').val(u.name||'');
							$('#ezs-phone').val(u.phone||'');
							$('#ezs-email').val(u.email||'');
							$box.html('<div style="font-size:12px;color:#059669">انتخاب شد: '+u.label+'</div>');
						}).appendTo($box);
				});
			});
		},300);
	});
	$('#ezs-send').on('click', function(){
		var $btn=$(this), $st=$('#ezs-status');
		$btn.prop('disabled',true); $st.text('در حال ارسال…');
		$.post(ezlens_auth_ajax.ajax_url,{
			action:'ezlens_campaign_single_send',
			nonce:ezlens_auth_ajax.nonce,
			channel:$('#ezs-channel').val(),
			name:$('#ezs-name').val(),
			phone:$('#ezs-phone').val(),
			email:$('#ezs-email').val(),
			subject:$('#ezs-subject').val(),
			body:$('#ezs-body').val(),
			user_id:$('#ezs-user-id').val()
		},function(res){
			$btn.prop('disabled',false);
			if(res&&res.success){
				$st.css('color','#059669').text(res.data&&res.data.message?res.data.message:'ارسال شد');
			}else{
				$st.css('color','#b91c1c').text((res&&res.data&&(res.data.message||res.data))||'خطا');
			}
		}).fail(function(){ $btn.prop('disabled',false); $st.css('color','#b91c1c').text('خطای ارتباط'); });
	});
})(jQuery);
</script>
