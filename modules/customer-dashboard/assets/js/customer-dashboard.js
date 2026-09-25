/**
 * EzLens Customer Dashboard — SPA + Phase 3
 */
(function () {
	'use strict';
	if (window.__ezcdInit) return;
	window.__ezcdInit = true;

	var data = window.ezcdData || {};
	var root, body, titleEl;

	function latinizeSvg(scope) {
		if (!scope) return;
		var map = { '۰': '0', '۱': '1', '۲': '2', '۳': '3', '۴': '4', '۵': '5', '۶': '6', '۷': '7', '۸': '8', '۹': '9' };
		function fix(s) { return String(s || '').replace(/[۰-۹]/g, function (ch) { return map[ch] || ch; }); }
		scope.querySelectorAll('svg, svg *').forEach(function (el) {
			if (!el.attributes) return;
			for (var j = 0; j < el.attributes.length; j++) {
				var a = el.attributes[j];
				var v = fix(a.value);
				if (v !== a.value) el.setAttribute(a.name, v);
			}
		});
	}

	function toast(message, type) {
		type = type || 'ok';
		var box = document.getElementById('ezcd-toast');
		if (!box) {
			box = document.createElement('div');
			box.id = 'ezcd-toast';
			box.className = 'ezcd-toast';
			document.body.appendChild(box);
		}
		box.textContent = message;
		box.className = 'ezcd-toast is-' + type + ' is-show';
		clearTimeout(box._t);
		box._t = setTimeout(function () { box.classList.remove('is-show'); }, 3200);
	}

	function post(action, payload, cb) {
		var bodyData = new URLSearchParams();
		bodyData.append('action', action);
		bodyData.append('nonce', data.nonce || '');
		Object.keys(payload || {}).forEach(function (k) {
			var val = payload[k];
			if (val && typeof val === 'object' && !(val instanceof File) && !Array.isArray(val)) {
				Object.keys(val).forEach(function (sk) {
					bodyData.append(k + '[' + sk + ']', val[sk] == null ? '' : val[sk]);
				});
			} else if (Array.isArray(val)) {
				val.forEach(function (item) { bodyData.append(k + '[]', item); });
			} else if (val != null) {
				bodyData.append(k, val);
			}
		});
		fetch(data.ajax, {
			method: 'POST',
			credentials: 'same-origin',
			headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
			body: bodyData.toString()
		})
			.then(function (r) { return r.json(); })
			.then(function (res) { cb(null, res); })
			.catch(function (e) { cb(e); });
	}

	function postFormData(action, formData, cb) {
		formData.append('action', action);
		formData.append('nonce', data.nonce || '');
		fetch(data.ajax, { method: 'POST', credentials: 'same-origin', body: formData })
			.then(function (r) { return r.json(); })
			.then(function (res) { cb(null, res); })
			.catch(function (e) { cb(e); });
	}

	function setActiveNav(section) {
		if (!root) return;
		root.querySelectorAll('[data-ezcd-nav]').forEach(function (a) {
			var sec = a.getAttribute('data-ezcd-nav');
			var on = sec === section || (section === 'view-order' && sec === 'orders');
			a.classList.toggle('is-active', on);
			var li = a.closest('li');
			if (li) li.classList.toggle('is-active', on);
		});
	}

	function bindSection(scope) {
		latinizeSvg(scope);
		bindReorder(scope);
		bindWishlist(scope);
		bindAccount(scope);
		bindPassword(scope);
		bindAddress(scope);
		bindPrescriptions(scope);
		bindReviews(scope);
		bindAddresses(scope);
		bindWalletDeposit(scope);
		bindRxTabs(scope);
		bindCharity(scope);
		bindOrderTabs(scope);
		bindGift(scope);
		bindCopy(scope);
		bindSupport(scope);
		initMap(scope);
		// Internal links that should stay SPA
		scope.querySelectorAll('a[href*="my-account"]').forEach(function (a) {
			if (a.hasAttribute('data-ezcd-nav') || a.target === '_blank') return;
			a.addEventListener('click', function (e) {
				var href = a.getAttribute('href') || '';
				var sec = parseSectionFromUrl(href);
				if (!sec) return;
				e.preventDefault();
				var args = {};
				if (sec === 'view-order') {
					var m = href.match(/view-order\/(\d+)/);
					if (m) args.order_id = m[1];
				}
				var u = new URL(href, window.location.origin);
				if (u.searchParams.get('edit')) args.edit = u.searchParams.get('edit');
				if (u.searchParams.get('tab')) args.tab = u.searchParams.get('tab');
				loadSection(sec, args, true);
			});
		});
	}

	function parseSectionFromUrl(href) {
		try {
			var u = new URL(href, window.location.origin);
			var path = u.pathname.replace(/\/+$/, '');
			var parts = path.split('/');
			var known = ['orders','view-order','edit-address','edit-account','wishlist','security','prescriptions','wallet','gift-cards','support','coupons','referral','reviews'];
			for (var i = 0; i < parts.length; i++) {
				if (known.indexOf(parts[i]) >= 0) {
					if (parts[i] === 'edit-address') return 'addresses';
					if (parts[i] === 'edit-account') return 'account';
					return parts[i];
				}
			}
			if (/my-account\/?$/.test(path)) return 'overview';
		} catch (e) {}
		return null;
	}

	function loadSection(section, args, push) {
		if (!body) return;
		args = args || {};
		body.classList.add('is-loading');
		var payload = { section: section };
		if (args.order_id) payload.order_id = args.order_id;
		if (args.edit) payload.edit = args.edit;
		if (args.tab) payload.tab = args.tab;
		if (args.ticket) payload.ticket = args.ticket;
		if (args.stab) payload.stab = args.stab;
		if (args.edit !== undefined && args.edit !== null) payload.edit = args.edit;
		if (args.ptab) payload.ptab = args.ptab;

		post('ezcd_load_section', payload, function (err, res) {
			body.classList.remove('is-loading');
			if (err || !res || !res.success) {
				toast((res && res.data && res.data.message) || 'بارگذاری بخش ممکن نشد', 'err');
				return;
			}
			body.innerHTML = res.data.html;
			body.setAttribute('data-section', res.data.section);
			if (titleEl) titleEl.textContent = res.data.title;
			setActiveNav(res.data.section === 'view-order' ? 'orders' : res.data.section);
			bindSection(body);
			if (push && res.data.url) {
				history.pushState({ ezcd: true, section: res.data.section, args: args }, res.data.title, res.data.url);
			}
			window.scrollTo({ top: 0, behavior: 'smooth' });
		});
	}

	function bindNav() {
		if (!root) return;
		root.querySelectorAll('[data-ezcd-nav]').forEach(function (a) {
			a.addEventListener('click', function (e) {
				var sec = a.getAttribute('data-ezcd-nav');
				if (!sec || sec === 'logout') return; // allow real logout
				e.preventDefault();
				var args = {};
				if (a.getAttribute('data-tab')) args.tab = a.getAttribute('data-tab');
				if (a.getAttribute('data-edit')) args.edit = a.getAttribute('data-edit');
				if (a.getAttribute('data-ptab')) args.ptab = a.getAttribute('data-ptab');
				if (a.getAttribute('data-ticket-id')) args.ticket = a.getAttribute('data-ticket-id');
				loadSection(sec, args, true);
			});
		});
		window.addEventListener('popstate', function (e) {
			if (e.state && e.state.ezcd) {
				loadSection(e.state.section || 'overview', e.state.args || {}, false);
			} else {
				var sec = parseSectionFromUrl(window.location.href) || 'overview';
				loadSection(sec, {}, false);
			}
		});
	}

	function bindReorder(scope) {
		scope.querySelectorAll('.ezcd-reorder').forEach(function (btn) {
			btn.addEventListener('click', function () {
				btn.disabled = true;
				post('ezcd_reorder', { order_id: btn.getAttribute('data-order-id') }, function (err, res) {
					btn.disabled = false;
					if (err || !res || !res.success) {
						toast((res && res.data && res.data.message) || 'خطا در سفارش مجدد', 'err');
						return;
					}
					toast(res.data.message, 'ok');
					if (res.data.cart_url && confirm(res.data.message + '\nرفتن به سبد خرید؟')) {
						window.location.href = res.data.cart_url;
					}
				});
			});
		});
	}

	function bindWishlist(scope) {
		scope.querySelectorAll('.ezcd-wish-remove').forEach(function (btn) {
			btn.addEventListener('click', function () {
				var id = btn.getAttribute('data-product-id');
				post('ezcd_wishlist_remove', { product_id: id }, function (err, res) {
					if (res && res.success) {
						toast(res.data.message, 'ok');
						var card = btn.closest('.ezcd-prod-card');
						if (card) card.remove();
					} else toast((res && res.data && res.data.message) || 'خطا', 'err');
				});
			});
		});
		scope.querySelectorAll('.ezcd-add-wish-cart').forEach(function (btn) {
			btn.addEventListener('click', function () {
				btn.disabled = true;
				post('ezcd_wishlist_add_cart', { product_id: btn.getAttribute('data-product-id') }, function (err, res) {
					btn.disabled = false;
					if (err || !res || !res.success) {
						toast((res && res.data && res.data.message) || 'خطا', 'err');
						return;
					}
					toast(res.data.message, 'ok');
				});
			});
		});
	}

	function bindAccount(scope) {
		var form = scope.querySelector('#ezcd-account-form');
		if (!form) return;
		form.addEventListener('submit', function (e) {
			e.preventDefault();
			var fd = new FormData(form);
			var payload = {};
			fd.forEach(function (v, k) { payload[k] = v; });
			post('ezcd_save_account', payload, function (err, res) {
				if (err || !res || !res.success) toast((res && res.data && res.data.message) || 'ذخیره نشد', 'err');
				else toast(res.data.message, 'ok');
			});
		});
	}

	function bindPassword(scope) {
		var form = scope.querySelector('#ezcd-password-form');
		if (!form) return;
		form.addEventListener('submit', function (e) {
			e.preventDefault();
			var fd = new FormData(form);
			var payload = {};
			fd.forEach(function (v, k) { payload[k] = v; });
			post('ezcd_change_password', payload, function (err, res) {
				if (err || !res || !res.success) toast((res && res.data && res.data.message) || 'خطا', 'err');
				else { toast(res.data.message, 'ok'); form.reset(); }
			});
		});
	}

	function bindAddress(scope) {
		var form = scope.querySelector('#ezcd-address-form');
		if (!form) return;
		form.addEventListener('submit', function (e) {
			e.preventDefault();
			var fd = new FormData(form);
			var address = {};
			fd.forEach(function (v, k) { address[k] = v; });
			post('ezcd_save_address', { type: form.getAttribute('data-type'), address: address }, function (err, res) {
				if (err || !res || !res.success) toast((res && res.data && res.data.message) || 'ذخیره نشد', 'err');
				else toast(res.data.message, 'ok');
			});
		});
	}

	
	
	
	
	function bindCharity(scope) {
		var root = scope.querySelector('.ezcd-charity');
		if (!root) return;
		function showCtab(tab) {
			root.querySelectorAll('[data-ctab]').forEach(function (b) {
				if (b.classList.contains('ezcd-rx-tab') || b.tagName === 'BUTTON') {
					b.classList.toggle('is-active', b.getAttribute('data-ctab') === tab);
				}
			});
			root.querySelectorAll('[data-cpanel]').forEach(function (p) {
				var on = p.getAttribute('data-cpanel') === tab;
				p.classList.toggle('is-hidden', !on);
				p.style.display = on ? '' : 'none';
			});
		}
		root.querySelectorAll('[data-ctab]').forEach(function (btn) {
			btn.addEventListener('click', function (e) {
				e.preventDefault();
				e.stopPropagation();
				showCtab(btn.getAttribute('data-ctab'));
			});
		});
		root.querySelectorAll('.ezcd-ch-pick').forEach(function (btn) {
			btn.addEventListener('click', function () {
				var id = btn.getAttribute('data-case') || '0';
				var title = btn.getAttribute('data-title') || '';
				var hid = root.querySelector('#ezcd-ch-case-id');
				var lab = root.querySelector('#ezcd-ch-case-label');
				if (hid) hid.value = id;
				if (lab) lab.textContent = title ? ('مورد انتخاب‌شده: ' + title) : 'کمک عمومی';
				showCtab('help');
				var form = root.querySelector('#ezcd-charity-form');
				if (form) form.scrollIntoView({ behavior: 'smooth', block: 'center' });
			});
		});
		root.querySelectorAll('.ezcd-ch-amt').forEach(function (btn) {
			btn.addEventListener('click', function () {
				var input = root.querySelector('#ezcd-charity-form input[name=amount]');
				if (input) input.value = btn.getAttribute('data-amt') || '';
			});
		});
		var form = root.querySelector('#ezcd-charity-form');
		if (form && !form.__ezcdBound) {
			form.__ezcdBound = true;
			form.addEventListener('submit', function (e) {
				e.preventDefault();
				e.stopPropagation();
				var fd = new FormData(form);
				var payload = {
					amount: fd.get('amount'),
					case_id: fd.get('case_id') || 0,
					message: fd.get('message') || '',
					is_anonymous: fd.get('is_anonymous') ? 1 : 0,
					source: 'wallet'
				};
				post('ezcd_charity_donate', payload, function (err, res) {
					if (err || !res || !res.success) {
						toast((res && res.data && res.data.message) || 'ثبت کمک ممکن نشد', 'err');
						return;
					}
					toast(res.data.message, 'ok');
					loadSection('charity', {}, false);
				});
			});
		}
	}

function bindOrderTabs(scope) {
		scope.querySelectorAll('.ezcd-tab[data-tab]').forEach(function (a) {
			a.addEventListener('click', function (e) {
				e.preventDefault();
				e.stopPropagation();
				loadSection('orders', { tab: a.getAttribute('data-tab') }, true);
			});
		});
	}
function bindRxTabs(scope) {
		function showPtab(tab) {
			if (!tab) return;
			scope.querySelectorAll('.ezcd-rx-tab').forEach(function (b) {
				b.classList.toggle('is-active', b.getAttribute('data-ptab') === tab);
			});
			scope.querySelectorAll('.ezcd-rx-panel').forEach(function (p) {
				var on = p.getAttribute('data-panel') === tab;
				if (on) {
					p.classList.remove('is-hidden');
					p.style.display = '';
					p.setAttribute('aria-hidden', 'false');
				} else {
					p.classList.add('is-hidden');
					p.style.display = 'none';
					p.setAttribute('aria-hidden', 'true');
				}
			});
			// remember active tab on root
			var rootRx = scope.querySelector('.ezcd-rx') || scope;
			rootRx.setAttribute('data-ptab', tab);
		}

		// ONLY tab buttons — never all [data-ptab] (avoids form quirks)
		
		scope.querySelectorAll('.ezcd-rx-tab-jump[data-ptab]').forEach(function (btn) {
			btn.addEventListener('click', function () {
				var t = btn.getAttribute('data-ptab');
				var tab = scope.querySelector('.ezcd-rx-tab[data-ptab="' + t + '"]');
				if (tab) tab.click();
			});
		});
		scope.querySelectorAll('.ezcd-rx-tab[data-ptab], button[data-ptab], a[data-ptab]').forEach(function (btn) {
			btn.addEventListener('click', function (e) {
				e.preventDefault();
				e.stopPropagation();
				showPtab(btn.getAttribute('data-ptab'));
			});
		});

		// restore tab if set
		var initial = (scope.querySelector('.ezcd-rx') || scope).getAttribute('data-ptab') || 'list';
		var activeBtn = scope.querySelector('.ezcd-rx-tab.is-active');
		if (activeBtn) initial = activeBtn.getAttribute('data-ptab') || initial;
		showPtab(initial);

		var typeM = scope.querySelector('#ezcd-rx-type-manual');
		if (typeM) {
			typeM.addEventListener('change', function () {
				var opt = typeM.options[typeM.selectedIndex];
				var h = scope.querySelector('#ezcd-rx-type-hint-m');
				if (h && opt) h.textContent = opt.getAttribute('data-hint') || '';
				var extra = scope.querySelector('.ezcd-rx-extra-contact');
				if (extra) extra.classList.toggle('is-hidden', ['contact','scleral'].indexOf(typeM.value) === -1);
			});
		}

		// Block native form submit / enter navigation inside rx panels
		scope.querySelectorAll('.ezcd-rx form, .ezcd-rx-panel form').forEach(function (form) {
			form.setAttribute('action', '#');
			form.setAttribute('method', 'post');
			form.addEventListener('submit', function (e) {
				e.preventDefault();
				e.stopPropagation();
			});
			form.addEventListener('keydown', function (e) {
				if (e.key === 'Enter' && e.target && e.target.tagName === 'INPUT') {
					e.preventDefault();
				}
			});
		});

		function wireRxForm(form, needFile) {
			if (!form || form.__ezcdBound) return;
			form.__ezcdBound = true;
			form.addEventListener('submit', function (e) {
				e.preventDefault();
				e.stopPropagation();
				var fd = new FormData(form);
				var payload = {};
				fd.forEach(function (v, k) { payload[k] = v; });
				if (payload.issued_at_jalali) {
					payload.issued_at = '';
					payload.notes = (payload.notes || '') + '\nتاریخ صدور شمسی: ' + payload.issued_at_jalali;
				}
				if (payload.visited_at_jalali) {
					payload.visited_at = '';
					payload.notes = (payload.notes || '') + '\nتاریخ مراجعه شمسی: ' + payload.visited_at_jalali;
				}
				if (payload.bc || payload.dia) {
					payload.notes = (payload.notes || '') + '\nBC: ' + (payload.bc || '') + ' DIA: ' + (payload.dia || '');
				}
				function doSave() {
					post('ezcd_rx_save', payload, function (err, res) {
						if (err || !res || !res.success) {
							toast((res && res.data && res.data.message) || 'ذخیره نشد', 'err');
							return;
						}
						toast(res.data.message, 'ok');
						loadSection('prescriptions', { ptab: 'list' }, false);
					});
				}
				var fileInput = form.querySelector('input[type=file]');
				if (needFile && fileInput && fileInput.files && fileInput.files[0]) {
					var up = new FormData();
					up.append('action', 'ezcd_rx_upload');
					up.append('nonce', (data.nonce || (window.ezcdData && window.ezcdData.nonce) || ''));
					up.append('file', fileInput.files[0]);
					fetch((data.ajax || (window.ezcdData && window.ezcdData.ajax) || '/wp-admin/admin-ajax.php'), { method: 'POST', credentials: 'same-origin', body: up })
						.then(function (r) { return r.json(); })
						.then(function (res) {
							if (!res || !res.success) {
								toast((res && res.data && res.data.message) || 'آپلود ناموفق', 'err');
								return;
							}
							payload.attachment_id = res.data.attachment_id || res.data.id;
							doSave();
						})
						.catch(function () { toast('آپلود ناموفق', 'err'); });
				} else {
					if (needFile && !payload.attachment_id) {
						toast('لطفاً فایل نسخه را انتخاب کنید', 'err');
						return;
					}
					doSave();
				}
			});
		}
		wireRxForm(scope.querySelector('#ezcd-rx-form-manual'), false);
		wireRxForm(scope.querySelector('#ezcd-rx-form-upload'), true);

		var profileForm = scope.querySelector('#ezcd-patient-profile-form');
		if (profileForm && !profileForm.__ezcdBound) {
			profileForm.__ezcdBound = true;
			profileForm.addEventListener('submit', function (e) {
				e.preventDefault();
				e.stopPropagation();
				var fd = new FormData(profileForm);
				var payload = {};
				fd.forEach(function (v, k) {
					if (k === 'conditions[]') {
						if (!payload.conditions) payload.conditions = [];
						payload.conditions.push(v);
					} else {
						payload[k] = v;
					}
				});
				post('ezcd_patient_profile_save', payload, function (err, res) {
					if (err || !res || !res.success) {
						toast((res && res.data && res.data.message) || 'ذخیره نشد', 'err');
						return;
					}
					toast(res.data.message || 'ذخیره شد', 'ok');
					// stay on profile tab
					showPtab('profile');
				});
			});
		}

		var drop = scope.querySelector('#ezcd-rx-dropzone');
		var fi = scope.querySelector('#ezcd-rx-file');
		if (drop && fi) {
			drop.addEventListener('click', function (e) {
				e.preventDefault();
				e.stopPropagation();
				fi.click();
			});
			fi.addEventListener('change', function () {
				var n = scope.querySelector('#ezcd-rx-file-name');
				if (n && fi.files[0]) n.textContent = fi.files[0].name;
			});
		}
	}

	function bindAddresses(scope) {
		scope.querySelectorAll('.ezcd-addr-del').forEach(function (btn) {
			btn.addEventListener('click', function () {
				if (!confirm('این آدرس حذف شود؟')) return;
				post('ezcd_delete_address', { id: btn.getAttribute('data-id') }, function (err, res) {
					if (err || !res || !res.success) {
						toast((res && res.data && res.data.message) || 'حذف نشد', 'err');
						return;
					}
					toast(res.data.message, 'ok');
					loadSection('addresses', {}, true);
				});
			});
		});

		scope.querySelectorAll('[data-ezcd-addr-edit]').forEach(function (btn) {
			btn.addEventListener('click', function (e) {
				e.preventDefault();
				var ed = btn.getAttribute('data-ezcd-addr-edit') || '';
				if (ed) loadSection('addresses', { edit: ed }, true);
				else loadSection('addresses', {}, true);
			});
		});
		var form = scope.querySelector('#ezcd-address-form');
		if (form) {
			form.addEventListener('submit', function (e) {
				e.preventDefault();
				var fd = new FormData(form);
				var payload = { type: form.getAttribute('data-type') || 'billing' };
				fd.forEach(function (v, k) { payload[k] = v; });
				post('ezcd_save_address', payload, function (err, res) {
					if (err || !res || !res.success) {
						toast((res && res.data && res.data.message) || 'ذخیره آدرس ممکن نشد', 'err');
						return;
					}
					toast(res.data.message || 'آدرس ذخیره شد', 'ok');
					loadSection('addresses', {}, true);
				});
			});
		}
	}

	function bindWalletDeposit(scope) {
		var form = scope.querySelector('#ezcd-wallet-deposit-form');
		if (!form) return;

		/* --- Persian amount words --- */
		function faDigits(s) {
			return String(s).replace(/[0-9]/g, function (d) {
				return '۰۱۲۳۴۵۶۷۸۹'[d];
			});
		}
		function onlyDigits(s) {
			var fa = '۰۱۲۳۴۵۶۷۸۹';
			var out = '';
			String(s || '').split('').forEach(function (ch) {
				var i = fa.indexOf(ch);
				if (i >= 0) out += String(i);
				else if (/\d/.test(ch)) out += ch;
			});
			return out;
		}
		function formatGrouped(n) {
			var d = onlyDigits(n);
			if (!d) return '';
			return d.replace(/\B(?=(\d{3})+(?!\d))/g, ',');
		}
		function toPersianWords(num) {
			num = parseInt(num, 10) || 0;
			if (num === 0) return 'صفر';
			var ones = ['', 'یک', 'دو', 'سه', 'چهار', 'پنج', 'شش', 'هفت', 'هشت', 'نه'];
			var teens = ['ده', 'یازده', 'دوازده', 'سیزده', 'چهارده', 'پانزده', 'شانزده', 'هفده', 'هجده', 'نوزده'];
			var tens = ['', '', 'بیست', 'سی', 'چهل', 'پنجاه', 'شصت', 'هفتاد', 'هشتاد', 'نود'];
			var hundreds = ['', 'یکصد', 'دویست', 'سیصد', 'چهارصد', 'پانصد', 'ششصد', 'هفتصد', 'هشتصد', 'نهصد'];
			var scales = ['', 'هزار', 'میلیون', 'میلیارد', 'تریلیون'];
			function underThousand(n) {
				if (n === 0) return '';
				if (n < 10) return ones[n];
				if (n < 20) return teens[n - 10];
				if (n < 100) {
					var t = Math.floor(n / 10), o = n % 10;
					return tens[t] + (o ? ' و ' + ones[o] : '');
				}
				var h = Math.floor(n / 100), r = n % 100;
				return hundreds[h] + (r ? ' و ' + underThousand(r) : '');
			}
			var parts = [];
			var scale = 0;
			while (num > 0 && scale < scales.length) {
				var chunk = num % 1000;
				if (chunk) {
					var w = underThousand(chunk);
					if (scales[scale]) w += ' ' + scales[scale];
					parts.unshift(w);
				}
				num = Math.floor(num / 1000);
				scale++;
			}
			return parts.join(' و ');
		}

		var amountInp = scope.querySelector('#ezcd-dep-amount');
		var wordsEl = scope.querySelector('#ezcd-dep-amount-words');
		var onlineAmt = scope.querySelector('#ezcd-dep-online-amt');
		function refreshAmountUI() {
			if (!amountInp) return;
			var raw = onlyDigits(amountInp.value);
			var grouped = formatGrouped(raw);
			if (amountInp.value !== grouped) {
				var pos = amountInp.selectionStart;
				amountInp.value = grouped;
			}
			var n = parseInt(raw || '0', 10) || 0;
			if (wordsEl) {
				if (n > 0) {
					wordsEl.textContent = faDigits(toPersianWords(n)) + ' تومان به کیف پول اضافه می‌شود';
					wordsEl.style.display = 'block';
				} else {
					wordsEl.textContent = '';
				}
			}
			if (onlineAmt) {
				onlineAmt.textContent = n > 0 ? (faDigits(formatGrouped(String(n))) + ' تومان') : '—';
			}
		}
		if (amountInp) {
			amountInp.addEventListener('input', refreshAmountUI);
			amountInp.addEventListener('blur', refreshAmountUI);
			refreshAmountUI();
		}

		/* --- Method cards (AJAX panels) --- */
		var methodInput = scope.querySelector('#ezcd-dep-method');
		function showMethod(m) {
			if (methodInput) methodInput.value = m;
			scope.querySelectorAll('.ezcd-method-card').forEach(function (c) {
				var on = c.getAttribute('data-method') === m;
				c.classList.toggle('is-active', on);
				c.setAttribute('aria-pressed', on ? 'true' : 'false');
			});
			scope.querySelectorAll('.ezcd-dep-panel').forEach(function (p) {
				var on = p.getAttribute('data-dep-panel') === m;
				p.classList.toggle('is-open', on);
				if (on) {
					p.hidden = false;
					p.style.display = '';
				} else {
					p.hidden = true;
					p.style.display = 'none';
				}
			});
		}
		scope.querySelectorAll('.ezcd-method-card[data-method]').forEach(function (btn) {
			btn.addEventListener('click', function () {
				showMethod(btn.getAttribute('data-method'));
			});
		});
		showMethod((methodInput && methodInput.value) || 'online');

		/* file drops */
		function bindDrop(zoneId, fileId, nameId) {
			var drop = scope.querySelector(zoneId);
			var fi = scope.querySelector(fileId);
			if (!drop || !fi) return;
			drop.addEventListener('click', function () { fi.click(); });
			fi.addEventListener('change', function () {
				var n = scope.querySelector(nameId);
				if (n && fi.files[0]) n.textContent = fi.files[0].name;
			});
		}
		bindDrop('#ezcd-deposit-drop', '#ezcd-deposit-file', '#ezcd-deposit-file-name');
		bindDrop('#ezcd-deposit-drop-bank', '#ezcd-deposit-file-bank', '#ezcd-deposit-file-name-bank');

		form.addEventListener('submit', function (e) {
			e.preventDefault();
			var method = (methodInput && methodInput.value) || 'online';
			var rawAmt = onlyDigits(amountInp ? amountInp.value : '');
			var n = parseInt(rawAmt || '0', 10) || 0;
			if (n < 10000) {
				toast('حداقل مبلغ ۱۰٬۰۰۰ تومان است', 'err');
				return;
			}
			var body = new FormData();
			body.append('action', 'ezcd_wallet_deposit');
			body.append('nonce', (data.nonce || (window.ezcdData && window.ezcdData.nonce) || ''));
			body.append('method', method);
			body.append('amount', rawAmt);

			if (method === 'bank') {
				var refB = scope.querySelector('#ezcd-dep-ref');
				body.append('ref_code', refB ? refB.value : '');
				var fB = scope.querySelector('#ezcd-deposit-file-bank');
				if (fB && fB.files[0]) body.append('receipt', fB.files[0]);
			} else if (method === 'card') {
				var refC = scope.querySelector('#ezcd-dep-ref-card');
				body.append('ref_code', refC ? refC.value : '');
				var fC = scope.querySelector('#ezcd-deposit-file');
				if (fC && fC.files[0]) body.append('receipt', fC.files[0]);
			}

			var msg = scope.querySelector('#ezcd-dep-msg');
			if (msg) msg.textContent = 'در حال ارسال…';

			fetch((data.ajax || (window.ezcdData && window.ezcdData.ajax) || '/wp-admin/admin-ajax.php'), {
				method: 'POST',
				credentials: 'same-origin',
				body: body
			})
				.then(function (r) { return r.json(); })
				.then(function (res) {
					if (!res || !res.success) {
						toast((res && res.data && res.data.message) || 'ثبت نشد', 'err');
						if (msg) msg.textContent = (res && res.data && res.data.message) || '';
						return;
					}
					toast(res.data.message, 'ok');
					if (msg) msg.textContent = res.data.message || '';
					if (res.data.redirect) {
						window.location.href = res.data.redirect;
						return;
					}
					loadSection('wallet', {}, false);
				})
				.catch(function () {
					toast('خطای شبکه', 'err');
					if (msg) msg.textContent = 'خطای شبکه';
				});
		});
	}

function bindReviews(scope) {
		scope.querySelectorAll('.ezcd-review-card').forEach(function (card) {
			var id = card.getAttribute('data-id');
			var view = card.querySelector('.ezcd-review-view');
			var form = card.querySelector('.ezcd-review-edit-form');
			var editBtn = card.querySelector('.ezcd-review-edit');
			var delBtn = card.querySelector('.ezcd-review-del');
			var cancel = card.querySelector('.ezcd-review-cancel');
			if (editBtn && form && view) {
				editBtn.addEventListener('click', function () {
					view.classList.add('is-hidden');
					form.classList.remove('is-hidden');
				});
			}
			if (cancel && form && view) {
				cancel.addEventListener('click', function () {
					form.classList.add('is-hidden');
					view.classList.remove('is-hidden');
				});
			}
			if (form) {
				form.addEventListener('submit', function (e) {
					e.preventDefault();
					var fd = new FormData(form);
					post('ezcd_review_update', {
						comment_id: id,
						content: fd.get('content'),
						rating: fd.get('rating')
					}, function (err, res) {
						if (err || !res || !res.success) {
							toast((res && res.data && res.data.message) || 'ذخیره نشد', 'err');
							return;
						}
						toast(res.data.message, 'ok');
						loadSection('reviews', {}, false);
					});
				});
			}
			if (delBtn) {
				delBtn.addEventListener('click', function () {
					if (!confirm('این نظر حذف شود؟')) return;
					post('ezcd_review_delete', { comment_id: id }, function (err, res) {
						if (err || !res || !res.success) {
							toast((res && res.data && res.data.message) || 'حذف نشد', 'err');
							return;
						}
						toast(res.data.message, 'ok');
						loadSection('reviews', {}, false);
					});
				});
			}
		});
	}

function bindPrescriptions(scope) {

		// Mode + type UI
		var modeBtns = scope.querySelectorAll('.ezcd-mode-btn');
		modeBtns.forEach(function (btn) {
			btn.addEventListener('click', function () {
				modeBtns.forEach(function (b) { b.classList.remove('is-active'); });
				btn.classList.add('is-active');
				var mode = btn.getAttribute('data-mode');
				scope.querySelectorAll('.ezcd-rx-mode-panel').forEach(function (p) {
					p.classList.toggle('is-hidden', p.getAttribute('data-panel') !== mode);
				});
				var hint = scope.querySelector('#ezcd-rx-mode-hint');
				if (hint) {
					hint.textContent = mode === 'upload'
						? 'فقط یک عکس واضح کافی است؛ تیم ما عددها را می‌خواند.'
						: 'اگر با عددها راحتید همین‌جا وارد کنید؛ وگرنه تب آپلود را بزنید.';
				}
			});
		});
		var typeSel = scope.querySelector('#ezcd-rx-type');
		function syncTypeUi() {
			if (!typeSel) return;
			var opt = typeSel.options[typeSel.selectedIndex];
			var th = scope.querySelector('#ezcd-rx-type-hint');
			if (th && opt) th.textContent = opt.getAttribute('data-hint') || '';
			var val = typeSel.value;
			scope.querySelectorAll('.ezcd-rx-extra').forEach(function (ex) {
				var forTypes = (ex.getAttribute('data-for') || '').split(/\s+/);
				ex.classList.toggle('is-hidden', forTypes.indexOf(val) === -1);
			});
		}
		if (typeSel) {
			typeSel.addEventListener('change', syncTypeUi);
			syncTypeUi();
		}
		var drop = scope.querySelector('#ezcd-rx-dropzone');
		var fileInput = scope.querySelector('#ezcd-rx-file');
		if (drop && fileInput) {
			drop.addEventListener('click', function () { fileInput.click(); });
			['dragenter','dragover'].forEach(function (ev) {
				drop.addEventListener(ev, function (e) { e.preventDefault(); drop.classList.add('is-drag'); });
			});
			['dragleave','drop'].forEach(function (ev) {
				drop.addEventListener(ev, function (e) {
					e.preventDefault();
					drop.classList.remove('is-drag');
					if (ev === 'drop' && e.dataTransfer && e.dataTransfer.files[0]) {
						fileInput.files = e.dataTransfer.files;
						fileInput.dispatchEvent(new Event('change'));
					}
				});
			});
		}

		var modal = scope.querySelector('#ezcd-rx-modal');
		var form = scope.querySelector('#ezcd-rx-form');
		var openBtn = scope.querySelector('#ezcd-rx-open-form');
		var closeBtn = scope.querySelector('#ezcd-rx-close');
		var fileInput = scope.querySelector('#ezcd-rx-file');
		var fileName = scope.querySelector('#ezcd-rx-file-name');
		var title = scope.querySelector('#ezcd-rx-modal-title');

		function openModal(edit) {
			if (!modal || !form) return;
			form.reset();
			form.querySelector('[name=id]').value = '';
			scope.querySelector('#ezcd-rx-attachment').value = '';
			if (fileName) fileName.textContent = '';
			if (title) title.textContent = edit ? 'ویرایش نسخه' : 'افزودن نسخه';
			if (edit) {
				Object.keys(edit).forEach(function (k) {
					var el = form.querySelector('[name="' + k + '"]');
					if (el && edit[k] != null) el.value = edit[k];
				});
				if (edit.attachment_id) scope.querySelector('#ezcd-rx-attachment').value = edit.attachment_id;
			}
			modal.hidden = false;
		}
		function closeModal() { if (modal) modal.hidden = true; }

		if (openBtn) openBtn.addEventListener('click', function () { openModal(null); });
		if (closeBtn) closeBtn.addEventListener('click', closeModal);
		if (modal) modal.addEventListener('click', function (e) { if (e.target === modal) closeModal(); });

		scope.querySelectorAll('.ezcd-rx-edit').forEach(function (btn) {
			btn.addEventListener('click', function () {
				try { openModal(JSON.parse(btn.getAttribute('data-rx'))); }
				catch (e) { toast('خواندن نسخه ممکن نشد', 'err'); }
			});
		});
		scope.querySelectorAll('.ezcd-rx-delete').forEach(function (btn) {
			btn.addEventListener('click', function () {
				if (!confirm('این نسخه حذف شود؟')) return;
				post('ezcd_rx_delete', { id: btn.getAttribute('data-id') }, function (err, res) {
					if (err || !res || !res.success) toast((res && res.data && res.data.message) || 'حذف نشد', 'err');
					else { toast(res.data.message, 'ok'); loadSection('prescriptions', {}, false); }
				});
			});
		});

		if (fileInput) {
			fileInput.addEventListener('change', function () {
				if (!fileInput.files || !fileInput.files[0]) return;
				var fd = new FormData();
				fd.append('file', fileInput.files[0]);
				if (fileName) fileName.textContent = 'در حال آپلود…';
				postFormData('ezcd_rx_upload', fd, function (err, res) {
					if (err || !res || !res.success) {
						toast((res && res.data && res.data.message) || 'آپلود ناموفق', 'err');
						if (fileName) fileName.textContent = '';
						return;
					}
					scope.querySelector('#ezcd-rx-attachment').value = res.data.attachment_id;
					if (fileName) fileName.textContent = 'فایل آماده است';
					toast(res.data.message, 'ok');
				});
			});
		}

		if (form) {
			form.addEventListener('submit', function (e) {
				e.preventDefault();
				var fd = new FormData(form);
				var payload = {};
				fd.forEach(function (v, k) { payload[k] = v; });
				post('ezcd_rx_save', payload, function (err, res) {
					if (err || !res || !res.success) toast((res && res.data && res.data.message) || 'ذخیره نشد', 'err');
					else {
						toast(res.data.message, 'ok');
						closeModal();
						loadSection('prescriptions', {}, false);
					}
				});
			});
		}

		var profileForm = scope.querySelector('#ezcd-patient-profile-form');
		if (profileForm) {
			profileForm.addEventListener('submit', function (e) {
				e.preventDefault();
				var fd = new FormData(profileForm);
				var payload = { conditions: [] };
				fd.forEach(function (v, k) {
					if (k === 'conditions[]') payload.conditions.push(v);
					else payload[k] = v;
				});
				post('ezcd_patient_profile_save', payload, function (err, res) {
					if (err || !res || !res.success) toast((res && res.data && res.data.message) || 'ذخیره نشد', 'err');
					else toast(res.data.message, 'ok');
				});
			});
		}
	}


	function bindCopy(scope) {
		scope.querySelectorAll('.ezcd-copy-code').forEach(function (btn) {
			btn.addEventListener('click', function () {
				var text = btn.getAttribute('data-copy') || '';
				if (!text) return;
				if (navigator.clipboard && navigator.clipboard.writeText) {
					navigator.clipboard.writeText(text).then(function () {
						toast('کپی شد', 'ok');
					}).catch(function () {
						fallbackCopy(text);
					});
				} else {
					fallbackCopy(text);
				}
			});
		});
	}

	function fallbackCopy(text) {
		var ta = document.createElement('textarea');
		ta.value = text;
		document.body.appendChild(ta);
		ta.select();
		try {
			document.execCommand('copy');
			toast('کپی شد', 'ok');
		} catch (e) {
			toast('کپی ممکن نشد', 'err');
		}
		document.body.removeChild(ta);
	}


	function bindSupport(scope) {
		var root = scope.querySelector('.ezcd-sp') || scope.querySelector('.ezcd-support');
		if (!root) return;

		function showStab(tab) {
			root.querySelectorAll('.ezcd-sp-tab, .ezcd-pill-tab').forEach(function (b) {
				var on = b.getAttribute('data-stab') === tab;
				b.classList.toggle('is-on', on);
				b.classList.toggle('is-active', on);
				if (b.hasAttribute('aria-selected')) b.setAttribute('aria-selected', on ? 'true' : 'false');
			});
			root.querySelectorAll('[data-spanel]').forEach(function (p) {
				var on = p.getAttribute('data-spanel') === tab;
				p.classList.toggle('is-on', on);
				if (on) {
					p.removeAttribute('hidden');
					p.style.display = '';
				} else {
					p.setAttribute('hidden', 'hidden');
					p.style.display = 'none';
				}
			});
			root.setAttribute('data-stab', tab);
		}

		root.querySelectorAll('[data-stab]').forEach(function (btn) {
			btn.addEventListener('click', function (e) {
				e.preventDefault();
				e.stopPropagation();
				var tab = btn.getAttribute('data-stab');
				if (!tab) return;
				// Always SPA-load so thread state resets cleanly
				if (tab === 'list' || tab === 'new') {
					loadSection('support', { stab: tab }, true);
				}
			});
		});

		var topic = scope.querySelector('#ezcd-support-topic');
		var otherWrap = scope.querySelector('#ezcd-support-other-wrap');
		if (topic && otherWrap) {
			topic.addEventListener('change', function () {
				var on = topic.value === 'other';
				if (on) otherWrap.removeAttribute('hidden');
				else otherWrap.setAttribute('hidden', 'hidden');
			});
		}

		function wireDrop(drop, fileInput, nameEl) {
			if (!drop || !fileInput) return;
			drop.addEventListener('click', function (e) {
				if (e.target === fileInput) return;
				fileInput.click();
			});
			fileInput.addEventListener('change', function () {
				if (!fileInput.files[0]) return;
				if (fileInput.files[0].size > 20 * 1024 * 1024) {
					toast('حجم فایل نباید بیشتر از ۲۰ مگابایت باشد', 'err');
					fileInput.value = '';
					if (nameEl) nameEl.textContent = '';
					return;
				}
				if (nameEl) nameEl.textContent = fileInput.files[0].name;
			});
		}
		wireDrop(scope.querySelector('#ezcd-support-drop'), scope.querySelector('#ezcd-support-file'), scope.querySelector('#ezcd-support-file-name'));
		wireDrop(scope.querySelector('#ezcd-reply-drop'), scope.querySelector('#ezcd-reply-file'), scope.querySelector('#ezcd-reply-file-name'));

		var create = scope.querySelector('#ezcd-ticket-create-form');
		if (create && !create.__ezcdBound) {
			create.__ezcdBound = true;
			create.addEventListener('submit', function (e) {
				e.preventDefault();
				e.stopPropagation();
				var fd = new FormData(create);
				var btn = create.querySelector('[type=submit]');
				if (btn) { btn.disabled = true; btn.style.opacity = '.7'; }
				postFormData('ezcd_ticket_create', fd, function (err, res) {
					if (btn) { btn.disabled = false; btn.style.opacity = ''; }
					if (err || !res || !res.success) {
						toast((res && res.data && res.data.message) || 'ثبت درخواست ممکن نشد', 'err');
						return;
					}
					toast(res.data.message, 'ok');
					loadSection('support', { ticket: res.data.ticket_id }, true);
				});
			});
		}

		var reply = scope.querySelector('#ezcd-ticket-reply-form');
		if (reply && !reply.__ezcdBound) {
			reply.__ezcdBound = true;
			reply.addEventListener('submit', function (e) {
				e.preventDefault();
				e.stopPropagation();
				var tid = reply.getAttribute('data-ticket-id');
				var fd = new FormData(reply);
				fd.set('ticket_id', tid);
				var btn = reply.querySelector('[type=submit]');
				if (btn) { btn.disabled = true; btn.style.opacity = '.7'; }
				postFormData('ezcd_ticket_reply', fd, function (err, res) {
					if (btn) { btn.disabled = false; btn.style.opacity = ''; }
					if (err || !res || !res.success) {
						toast((res && res.data && res.data.message) || 'ارسال ممکن نشد', 'err');
						return;
					}
					toast(res.data.message, 'ok');
					loadSection('support', { ticket: tid }, false);
				});
			});
		}

		scope.querySelectorAll('.ezcd-sp-card, .ezcd-sp-item, .ezcd-ticket-row').forEach(function (btn) {
			btn.addEventListener('click', function () {
				var id = btn.getAttribute('data-ticket-id');
				if (id) loadSection('support', { ticket: id }, true);
			});
		});

		var thread = scope.querySelector('#ezcd-ticket-thread');
		if (thread) {
			requestAnimationFrame(function () { thread.scrollTop = thread.scrollHeight; });
		}
	}

	function bindGift(scope) {
		var redeem = scope.querySelector('#ezcd-gift-redeem-form');
		if (redeem) {
			redeem.addEventListener('submit', function (e) {
				e.preventDefault();
				var code = (redeem.querySelector('[name=code]') || {}).value || '';
				post('ezcd_gift_redeem', { code: code }, function (err, res) {
					if (err || !res || !res.success) {
						toast((res && res.data && res.data.message) || 'اعمال کد ممکن نشد', 'err');
						return;
					}
					toast(res.data.message, 'ok');
					loadSection('gift-cards', {}, false);
				});
			});
		}
		var buy = scope.querySelector('#ezcd-gift-buy-form');
		if (buy) {
			buy.addEventListener('submit', function (e) {
				e.preventDefault();
				var fd = new FormData(buy);
				var payload = {};
				fd.forEach(function (v, k) { payload[k] = v; });
				post('ezcd_gift_purchase', payload, function (err, res) {
					if (err || !res || !res.success) {
						toast((res && res.data && res.data.message) || 'صدور کارت ممکن نشد', 'err');
						return;
					}
					toast(res.data.message + (res.data.code ? (' — کد: ' + res.data.code) : ''), 'ok');
					loadSection('gift-cards', {}, false);
				});
			});
		}
	}

	function initMap(scope) {
		var el = scope.querySelector('#ezcd-map');
		if (!el || typeof L === 'undefined') return;
		var lat = parseFloat(el.getAttribute('data-lat')) || 35.6892;
		var lng = parseFloat(el.getAttribute('data-lng')) || 51.3890;
		var map = L.map(el).setView([lat, lng], 13);
		L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19, attribution: '&copy; OSM' }).addTo(map);
		var plaqueEl = scope.querySelector('#ezcd-plaque');
		function plaqueText() {
			var p = plaqueEl ? (plaqueEl.value || '').trim() : (el.getAttribute('data-plaque') || '');
			return p ? ('پلاک ' + p) : 'موقعیت شما';
		}
		var marker = L.marker([lat, lng], { draggable: true }).addTo(map);
		marker.bindPopup(plaqueText()).openPopup();
		if (plaqueEl) {
			plaqueEl.addEventListener('input', function () {
				marker.setPopupContent(plaqueText());
				marker.openPopup();
			});
		}
		function setLL(la, ln) {
			var a = scope.querySelector('#ezcd-lat');
			var b = scope.querySelector('#ezcd-lng');
			if (a) a.value = String(la);
			if (b) b.value = String(ln);
		}
		function reverse(la, ln) {
			fetch('https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=' + la + '&lon=' + ln, { headers: { 'Accept-Language': 'fa' } })
				.then(function (r) { return r.json(); })
				.then(function (j) {
					if (!j || !j.address) return;
					var ad = j.address;
					var line = [ad.road, ad.neighbourhood, ad.suburb].filter(Boolean).join('، ');
					var city = ad.city || ad.town || ad.village || ad.county || '';
					var a1 = scope.querySelector('#ezcd-address-1');
					var c = scope.querySelector('#ezcd-city');
					if (a1 && line) a1.value = line;
					if (c && city) c.value = city;
				}).catch(function () {});
		}
		marker.on('dragend', function () {
			var p = marker.getLatLng();
			setLL(p.lat, p.lng);
			reverse(p.lat, p.lng);
		});
		map.on('click', function (e) {
			marker.setLatLng(e.latlng);
			setLL(e.latlng.lat, e.latlng.lng);
			reverse(e.latlng.lat, e.latlng.lng);
		});
		setTimeout(function () { map.invalidateSize(); }, 250);
	}

	function ready(fn) {
		if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', fn);
		else fn();
	}

	ready(function () {
		root = document.getElementById('ezcd');
		if (!root) return;
		body = document.getElementById('ezcd-body') || root.querySelector('.ezcd-body');
		titleEl = document.getElementById('ezcd-title');
		latinizeSvg(root);
		bindNav();
		if (body) bindSection(body);
		history.replaceState({ ezcd: true, section: (body && body.getAttribute('data-section')) || 'overview', args: {} }, document.title, window.location.href);
	});
})();
