(function ($) {
	'use strict';
	if (!window.ezInbox) return;

	var state = { folder: 'inbox', page: 1, search: '', filter: 'all', uid: 0 };

	function toast(el, msg, ok) {
		var $s = $(el);
		$s.text(msg).removeClass('is-ok is-err').addClass(ok ? 'is-ok' : 'is-err');
	}

	function post(action, data) {
		data = data || {};
		data.action = action;
		data.nonce = ezInbox.nonce;
		return $.post(ezInbox.ajax, data);
	}

	/* Tabs */
	$('.ezi-tab').on('click', function () {
		var tab = $(this).data('tab');
		$('.ezi-tab').removeClass('is-active');
		$(this).addClass('is-active');
		if (tab === 'settings') {
			$('#ezi-panel-mail').removeClass('is-active');
			$('#ezi-panel-settings').addClass('is-active');
			return;
		}
		$('#ezi-panel-settings').removeClass('is-active');
		$('#ezi-panel-mail').addClass('is-active');
		state.folder = tab;
		state.page = 1;
		state.uid = 0;
		loadList();
	});

	function loadList() {
		var $list = $('#ezi-list').addClass('ezi-loading');
		post('ezlens_inbox_list', {
			folder: state.folder,
			page: state.page,
			search: state.search,
			filter: state.filter
		}).done(function (res) {
			$list.removeClass('ezi-loading');
			if (!res || !res.success) {
				$list.html('<div class="ezi-empty">' + ((res && res.data && res.data.message) || 'خطا در دریافت') + '</div>');
				$('#ezi-pager').empty();
				return;
			}
			var d = res.data;
			if (!d.items || !d.items.length) {
				$list.html('<div class="ezi-empty">ایمیلی یافت نشد.</div>');
			} else {
				var html = '';
				d.items.forEach(function (it) {
					html += '<button type="button" class="ezi-item' + (it.seen ? '' : ' is-unread') + (state.uid === it.uid ? ' is-active' : '') + '" data-uid="' + it.uid + '">';
					html += '<div class="ezi-item-sub">' + $('<div/>').text(it.subject || '(بدون موضوع)').html() + '</div>';
					html += '<div class="ezi-item-meta"><span>' + $('<div/>').text(it.from_name || it.from || it.to || '').html() + '</span>';
					html += '<span>' + $('<div/>').text(it.date_fa || '').html() + '</span></div></button>';
				});
				$list.html(html);
			}
			var pages = d.pages || 1;
			$('#ezi-pager-top, #ezi-pager').html(
				'<button type="button" class="ezi-btn" id="ezi-prev" ' + (state.page <= 1 ? 'disabled' : '') + '>قبلی</button>' +
				'<span>صفحه ' + state.page + ' از ' + pages + ' · ' + d.total + ' پیام</span>' +
				'<button type="button" class="ezi-btn" id="ezi-next" ' + (state.page >= pages ? 'disabled' : '') + '>بعدی</button>'
			);
		}).fail(function () {
			$list.removeClass('ezi-loading').html('<div class="ezi-empty">خطای ارتباط</div>');
		});
	}

	$('#ezi-refresh').on('click', function () { state.page = 1; loadList(); });
	$('#ezi-filter').on('change', function () { state.filter = $(this).val(); state.page = 1; loadList(); });
	var searchT;
	$('#ezi-search').on('input', function () {
		var v = $(this).val();
		clearTimeout(searchT);
		searchT = setTimeout(function () { state.search = v; state.page = 1; loadList(); }, 400);
	});
	$(document).on('click', '#ezi-prev', function () { if (state.page > 1) { state.page--; loadList(); } });
	$(document).on('click', '#ezi-next', function () { state.page++; loadList(); });

	$(document).on('click', '.ezi-item', function () {
		var uid = parseInt($(this).data('uid'), 10);
		state.uid = uid;
		$('.ezi-item').removeClass('is-active');
		$(this).addClass('is-active').removeClass('is-unread');
		loadMessage(uid);
	});

	function loadMessage(uid) {
		var $r = $('#ezi-reader').html('<div class="ezi-empty">در حال بارگذاری…</div>');
		post('ezlens_inbox_message', { uid: uid, folder: state.folder }).done(function (res) {
			if (!res || !res.success) {
				$r.html('<div class="ezi-empty">' + ((res && res.data && res.data.message) || 'خطا') + '</div>');
				return;
			}
			var m = res.data;
			var body = m.body_html || ('<pre style="white-space:pre-wrap;font-family:inherit">' + $('<div/>').text(m.body_text || '').html() + '</pre>');
			var att = '';
			if (m.attachments && m.attachments.length) {
				att = '<div class="ezi-attach">';
				m.attachments.forEach(function (a) {
					var url = ezInbox.ajax + '?action=ezlens_inbox_attachment&nonce=' + encodeURIComponent(ezInbox.nonce) +
						'&uid=' + uid + '&part=' + encodeURIComponent(a.part) + '&folder=' + state.folder;
					att += '<a href="' + url + '" target="_blank" rel="noopener">' + $('<div/>').text(a.filename).html() + '</a>';
				});
				att += '</div>';
			}
			$r.html(
				'<div class="ezi-reader-head">' +
				'<h2>' + $('<div/>').text(m.subject || '').html() + '</h2>' +
				'<div class="ezi-reader-meta">از: ' + $('<div/>').text((m.from_name ? m.from_name + ' · ' : '') + (m.from || '')).html() +
				'<br>تاریخ: ' + $('<div/>').text(m.date_fa || m.date_raw || '').html() + '</div>' +
				'<div class="ezi-reader-actions">' +
				'<button type="button" class="ezi-btn ezi-btn-danger" id="ezi-del" data-uid="' + uid + '">حذف ایمیل</button>' +
				'</div></div>' +
				'<div class="ezi-reader-body">' + body + '</div>' + att + '<div class="ezi-reply"><strong>پاسخ به این ایمیل</strong>' + '<textarea id="ezi-reply-body" placeholder="متن پاسخ…"></textarea>' + '<button type="button" class="ezi-btn ezi-btn-primary" id="ezi-reply-send" data-uid="' + uid + '" data-to="' + $('<div/>').text(m.from||'').html() + '" data-subject="' + $('<div/>').text(m.subject||'').html() + '">ارسال پاسخ</button>' + '<span class="ezi-status" id="ezi-reply-status"></span></div>'
			);
		});
	}

	$(document).on('click', '#ezi-del', function () {
		if (!confirm('این ایمیل حذف شود؟')) return;
		var uid = $(this).data('uid');
		post('ezlens_inbox_delete', { uid: uid, folder: state.folder }).done(function (res) {
			if (res && res.success) {
				$('#ezi-reader').html('<div class="ezi-empty">ایمیل حذف شد.</div>');
				loadList();
			} else {
				alert((res && res.data && res.data.message) || 'حذف ناموفق');
			}
		});
	});

	$(document).on('click', '#ezi-reply-send', function () {
		var body = $('#ezi-reply-body').val() || '';
		if (!body.trim()) { $('#ezi-reply-status').text('متن پاسخ خالی است').addClass('is-err'); return; }
		$('#ezi-reply-status').text('در حال ارسال…').removeClass('is-err is-ok');
		post('ezlens_inbox_reply', {
			uid: $(this).data('uid'), folder: state.folder,
			to: $(this).data('to'), subject: $(this).data('subject'), body: body
		}).done(function (res) {
			if (res && res.success) {
				$('#ezi-reply-status').text((res.data && res.data.message) || 'ارسال شد').addClass('is-ok');
				$('#ezi-reply-body').val('');
			} else {
				$('#ezi-reply-status').text((res && res.data && res.data.message) || 'خطا').addClass('is-err');
			}
		});
	});
	/* Settings */
	$('#ezi-settings-form').on('submit', function (e) {
		e.preventDefault();
		var data = $(this).serializeArray().reduce(function (o, x) { o[x.name] = x.value; return o; }, {});
		post('ezlens_inbox_save_settings', data).done(function (res) {
			if (res && res.success) {
				toast('#ezi-settings-status', res.data.message || 'ذخیره شد', true);
				if (res.data.has_pass) {
					$('#ezi-pass-pill').text('رمز ذخیره شده').removeClass('is-warn').addClass('is-ok');
					$('input[name=password]').val('').attr('placeholder', '••••••••  (خالی = بدون تغییر)');
				}
			} else {
				toast('#ezi-settings-status', (res && res.data && res.data.message) || 'خطا', false);
			}
		});
	});

	$('#ezi-test').on('click', function () {
		toast('#ezi-settings-status', 'در حال تست…', true);
		post('ezlens_inbox_test', {}).done(function (res) {
			toast('#ezi-settings-status', (res && res.data && res.data.message) || (res && res.data && res.data.message) || 'نتیجه نامشخص', !!(res && res.success));
		}).fail(function () { toast('#ezi-settings-status', 'خطای ارتباط', false); });
	});

	/* auto load inbox */
	loadList();
})(jQuery);
