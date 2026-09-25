<?php
/**
 * تاریخچه کمپین‌ها — فاز ۳
 * @version 3.0.0
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="campaign-tab-content ezc-panel">
	<div class="ezc-panel-head">
		<div>
			<h2>تاریخچه کمپین‌ها</h2>
			<p class="ezc-muted">پیش‌نویس، زمان‌بندی، ارسال و گزارش</p>
		</div>
		<button type="button" class="button button-primary ezc-btn-primary campaign-tab-jump" data-tab="create">کمپین جدید</button>
	</div>

	<div class="ezc-toolbar">
		<button type="button" id="history-refresh" class="button">بروزرسانی</button>
		<select id="history-status-filter">
			<option value="all">همه وضعیت‌ها</option>
			<option value="sent">ارسال‌شده</option>
			<option value="partial">ناقص</option>
			<option value="draft">پیش‌نویس</option>
			<option value="scheduled">زمان‌بندی‌شده</option>
			<option value="sending">در حال ارسال</option>
			<option value="paused">متوقف</option>
			<option value="failed">ناموفق</option>
		</select>
		<input type="search" id="history-search" placeholder="جستجوی نام کمپین…">
		<button type="button" id="history-search-btn" class="button button-primary ezc-btn-primary">جستجو</button>
		<span id="history-total-count" class="ezc-muted" style="margin-right:auto;">تعداد: ۰</span>
	</div>

	<div id="history-list-container" class="ezc-list-host">
		<div class="ezc-skeleton-block compact">
			<div class="ezc-sk-line"></div>
			<div class="ezc-sk-line short"></div>
		</div>
	</div>
</div>

<div id="campaign-editor-modal" class="ezlens-campaign-modal" style="display:none;">
	<div class="ezlens-campaign-modal-backdrop"></div>
	<div class="ezlens-campaign-modal-card" role="dialog" aria-modal="true" aria-labelledby="campaign-editor-title">
		<div class="ezlens-campaign-modal-head">
			<strong id="campaign-editor-title">ویرایش کمپین</strong>
			<button type="button" class="button-link" id="campaign-editor-close">بستن</button>
		</div>
		<div class="ezlens-campaign-modal-body">
			<input type="hidden" id="editor-campaign-id">
			<div class="ezlens-editor-grid">
				<label>نام کمپین *<input id="editor-name" type="text"></label>
				<label>نوع ارسال
					<select id="editor-type">
						<option value="email">ایمیل</option>
						<option value="sms">پیامک</option>
					</select>
				</label>
				<label class="full">موضوع (ایمیل)<input id="editor-subject" type="text"></label>
				<label class="full">متن پیام<textarea id="editor-message" rows="6"></textarea></label>
			</div>
			<div class="ezlens-campaign-modal-foot">
				<button type="button" class="button button-primary ezc-btn-primary" id="campaign-editor-save">ذخیره</button>
			</div>
		</div>
	</div>
</div>
