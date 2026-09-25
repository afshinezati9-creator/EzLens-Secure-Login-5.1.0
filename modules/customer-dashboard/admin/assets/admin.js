(function () {
	'use strict';
	if (typeof ezcdAdmin === 'undefined') return;

	function toast(msg, type) {
		var el = document.getElementById('ezcd-admin-toast');
		if (!el) {
			el = document.createElement('div');
			el.id = 'ezcd-admin-toast';
			el.style.cssText = 'position:fixed;bottom:24px;left:24px;z-index:99999;padding:12px 18px;border-radius:12px;color:#fff;font-weight:700;box-shadow:0 8px 24px rgba(0,0,0,.15);max-width:360px;transition:opacity .2s';
			document.body.appendChild(el);
		}
		el.style.background = type === 'err' ? '#b91c1c' : '#031f8a';
		el.textContent = msg;
		el.style.opacity = '1';
		clearTimeout(el._t);
		el._t = setTimeout(function () { el.style.opacity = '0'; }, 3200);
	}

	function post(action, data, cb) {
		var body = new FormData();
		body.append('action', action);
		body.append('nonce', ezcdAdmin.nonce);
		if (data) {
			Object.keys(data).forEach(function (k) {
				if (Array.isArray(data[k])) {
					data[k].forEach(function (v) { body.append(k + '[]', v); });
				} else if (data[k] !== undefined && data[k] !== null) {
					body.append(k, data[k]);
				}
			});
		}
		fetch(ezcdAdmin.ajaxUrl, { method: 'POST', credentials: 'same-origin', body: body })
			.then(function (r) { return r.json(); })
			.then(function (json) { cb(null, json); })
			.catch(function (err) { cb(err); });
	}

	function get(action, params, cb) {
		var q = new URLSearchParams();
		q.set('action', action);
		q.set('nonce', ezcdAdmin.nonce);
		if (params) {
			Object.keys(params).forEach(function (k) { q.set(k, params[k]); });
		}
		fetch(ezcdAdmin.ajaxUrl + '?' + q.toString(), { credentials: 'same-origin' })
			.then(function (r) { return r.json(); })
			.then(function (json) { cb(null, json); })
			.catch(function (err) { cb(err); });
	}

	/* ——— Customers list live search ——— */
	var searchInput = document.querySelector('.ezcd-admin-search input[name="s"]');
	var tableBody = document.querySelector('.ezcd-admin-table tbody');
	var searchTimer = null;

	function renderRows(items) {
		if (!tableBody) return;
		if (!items || !items.length) {
			tableBody.innerHTML = '<tr><td colspan="8">مشتری‌ای یافت نشد.</td></tr>';
			return;
		}
		tableBody.innerHTML = items.map(function (u) {
			return '<tr>' +
				'<td><input type="checkbox" class="ezcd-row-check" value="' + u.id + '"></td>' +
				'<td><strong><a href="' + u.url + '">' + escapeHtml(u.name) + '</a></strong>' +
				'<div class="description">#' + u.id + ' — ' + escapeHtml(u.login) + '</div></td>' +
				'<td>' + escapeHtml(u.email) + '</td>' +
				'<td><code>' + escapeHtml(u.phone) + '</code></td>' +
				'<td>' + u.orders + '</td>' +
				'<td>' + formatNum(u.spent) + '</td>' +
				'<td>' + formatNum(u.wallet) + '</td>' +
				'<td><a class="button button-small" href="' + u.url + '">پروفایل</a></td>' +
				'</tr>';
		}).join('');
	}

	function escapeHtml(s) {
		var d = document.createElement('div');
		d.textContent = s == null ? '' : String(s);
		return d.innerHTML;
	}
	function formatNum(n) {
		try { return Number(n).toLocaleString('fa-IR'); } catch (e) { return n; }
	}

	function loadCustomers(s, paged) {
		if (!tableBody) return;
		tableBody.innerHTML = '<tr><td colspan="8"><span class="ezcd-skeleton">در حال بارگذاری…</span></td></tr>';
		get('ezcd_admin_customers', { s: s || '', paged: paged || 1 }, function (err, res) {
			if (err || !res || !res.success) {
				toast((res && res.data && res.data.message) || 'خطا در بارگذاری', 'err');
				return;
			}
			renderRows(res.data.items);
		});
	}

	if (searchInput && tableBody) {
		searchInput.addEventListener('input', function () {
			clearTimeout(searchTimer);
			searchTimer = setTimeout(function () {
				loadCustomers(searchInput.value, 1);
			}, 350);
		});
		// Intercept form submit for AJAX
		var searchForm = searchInput.closest('form');
		if (searchForm) {
			searchForm.addEventListener('submit', function (e) {
				e.preventDefault();
				loadCustomers(searchInput.value, 1);
			});
		}
	}

	/* ——— Mass send AJAX ——— */
	var massForm = document.querySelector('.ezcd-mass-form');
	if (massForm) {
		massForm.addEventListener('submit', function (e) {
			e.preventDefault();
			if (!confirm('از ارسال پیام به این گروه مطمئن هستید؟')) return;
			var fd = new FormData(massForm);
			var btn = massForm.querySelector('[type=submit]');
			if (btn) { btn.disabled = true; btn.value = 'در حال ارسال…'; }
			post('ezcd_admin_mass_send', {
				audience: fd.get('audience'),
				channel: fd.get('channel'),
				subject: fd.get('subject'),
				message: fd.get('message'),
				user_ids: fd.get('user_ids')
			}, function (err, res) {
				if (btn) { btn.disabled = false; btn.value = 'ارسال پیام جمعی'; }
				if (err || !res || !res.success) {
					toast((res && res.data && res.data.message) || 'ارسال ناموفق', 'err');
					return;
				}
				toast(res.data.message, 'ok');
				if (res.data.errors && res.data.errors.length) {
					console.warn('ezcd mass errors', res.data.errors);
				}
				setTimeout(function () { location.reload(); }, 900);
			});
		});
	}

	/* ——— Profile: note + wallet + direct message ——— */
	var noteForm = document.querySelector('form[action*="admin-post.php"] input[value="ezcd_add_note"]');
	if (noteForm) {
		var nf = noteForm.closest('form');
		nf.addEventListener('submit', function (e) {
			e.preventDefault();
			var fd = new FormData(nf);
			post('ezcd_admin_add_note', {
				customer_id: fd.get('customer_id'),
				note: fd.get('note')
			}, function (err, res) {
				if (err || !res || !res.success) {
					toast((res && res.data && res.data.message) || 'ذخیره نشد', 'err');
					return;
				}
				toast(res.data.message, 'ok');
				setTimeout(function () { location.reload(); }, 600);
			});
		});
	}

	var walletForm = document.querySelector('form input[value="ezcd_wallet_adjust"]');
	if (walletForm) {
		var wf = walletForm.closest('form');
		wf.addEventListener('submit', function (e) {
			e.preventDefault();
			var fd = new FormData(wf);
			post('ezcd_admin_wallet', {
				customer_id: fd.get('customer_id'),
				amount: fd.get('amount'),
				entry_type: fd.get('entry_type'),
				note: fd.get('note')
			}, function (err, res) {
				if (err || !res || !res.success) {
					toast((res && res.data && res.data.message) || 'خطا', 'err');
					return;
				}
				toast(res.data.message + (res.data.balance != null ? ' — موجودی: ' + formatNum(res.data.balance) : ''), 'ok');
				setTimeout(function () { location.reload(); }, 700);
			});
		});
	}

	var dmForm = document.querySelector('form input[value="ezcd_direct_message"]');
	if (dmForm) {
		var df = dmForm.closest('form');
		df.addEventListener('submit', function (e) {
			e.preventDefault();
			var fd = new FormData(df);
			post('ezcd_admin_direct_message', {
				customer_id: fd.get('customer_id'),
				channel: fd.get('channel'),
				subject: fd.get('subject'),
				message: fd.get('message')
			}, function (err, res) {
				if (err || !res || !res.success) {
					toast((res && res.data && res.data.message) || 'ارسال نشد', 'err');
					return;
				}
				toast(res.data.message, 'ok');
				df.reset();
			});
		});
	}

	/* ——— Settings AJAX ——— */
	var settingsForm = document.querySelector('form input[value="ezcd_save_sections"]');
	if (settingsForm) {
		var sf = settingsForm.closest('form');
		sf.addEventListener('submit', function (e) {
			e.preventDefault();
			var fd = new FormData(sf);
			var sections = [];
			fd.getAll('sections[]').forEach(function (v) { sections.push(v); });
			post('ezcd_admin_save_sections', {
				sections: sections,
				replace_my_account: fd.get('replace_my_account') ? 1 : 0
			}, function (err, res) {
				if (err || !res || !res.success) {
					toast((res && res.data && res.data.message) || 'ذخیره نشد', 'err');
					return;
				}
				toast(res.data.message, 'ok');
			});
		});
	}
})();
