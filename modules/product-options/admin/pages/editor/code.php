<?php
/**
 * Code editor tab — WordPress CodeMirror (same stack as Purchase Process).
 * Code editor is the source of truth for palette HTML/CSS/JS.
 *
 * @package EzLens_Secure_Login
 */
if (!defined('ABSPATH')) {
	exit;
}

if (!class_exists('EzLens_PO_Template_File_Storage')) {
	$_sf = dirname(__DIR__, 3) . '/includes/class-template-file-storage.php';
	if (!is_readable($_sf)) {
		$_sf = dirname(__DIR__, 2) . '/includes/class-template-file-storage.php';
	}
	if (is_readable($_sf)) {
		require_once $_sf;
	}
}

if (function_exists('wp_enqueue_code_editor')) {
	wp_enqueue_code_editor(array('type' => 'text/html'));
	wp_enqueue_script('wp-theme-plugin-editor');
	wp_enqueue_style('wp-codemirror');
}

$html = '';
$css  = '';
$js   = '';
if (!empty($fields) && is_array($fields)) {
	$html = isset($fields['_code_html']) ? (string) $fields['_code_html'] : '';
	$css  = isset($fields['_code_css']) ? (string) $fields['_code_css'] : '';
	$js   = isset($fields['_code_js']) ? (string) $fields['_code_js'] : '';
}
$combined = $html;
if ($css !== '' || $js !== '') {
	$combined .= "\n\n/**CSS**/\n" . $css . "\n\n/**JS**/\n" . $js;
}

// Prefer exact file from storage/{id}-*.code.html when editing
$tid = 0;
if (!empty($template_id)) {
	$tid = (int) $template_id;
} elseif (!empty($_GET['id'])) {
	$tid = absint($_GET['id']);
}
if ($tid > 0 && class_exists('EzLens_PO_Template_File_Storage')) {
	$file_code = EzLens_PO_Template_File_Storage::read_code($tid);
	if (is_string($file_code) && trim($file_code) !== '') {
		$combined = $file_code;
	}
}

// Only auto-generate minimal HTML from visual fields when there is truly no code
if (trim($combined) === '' && !empty($fields) && is_array($fields)) {
	$html_parts = array();
	$html_parts[] = '<!-- Generated from visual builder fields -->';
	$html_parts[] = '<div class="ez-po-wrap">';
	foreach ($fields as $key => $field) {
		if (!is_array($field)) {
			continue;
		}
		if (is_string($key) && strpos($key, '_code_') === 0) {
			continue;
		}
		if (!empty($field['_meta'])) {
			continue;
		}
		$type = isset($field['type']) ? (string) $field['type'] : 'text';
		$label = isset($field['label']) ? (string) $field['label'] : '';
		$name = isset($field['name']) ? (string) $field['name'] : (is_string($key) ? $key : 'field');
		$ph = isset($field['placeholder']) ? (string) $field['placeholder'] : '';
		$req = !empty($field['required']) ? ' required' : '';
		if ($type === 'heading') {
			$html_parts[] = '  <h3 class="ez-po-heading">' . esc_html($label) . '</h3>';
			continue;
		}
		if ($type === 'html' || $type === 'divider' || $type === 'spacer') {
			continue;
		}
		$html_parts[] = '  <div class="ez-po-field" data-type="' . esc_attr($type) . '">';
		$html_parts[] = '    <label for="' . esc_attr($name) . '">' . esc_html($label) . '</label>';
		if ($type === 'textarea') {
			$html_parts[] = '    <textarea id="' . esc_attr($name) . '" name="' . esc_attr($name) . '" placeholder="' . esc_attr($ph) . '"' . $req . '></textarea>';
		} elseif ($type === 'select') {
			$html_parts[] = '    <select id="' . esc_attr($name) . '" name="' . esc_attr($name) . '"' . $req . '></select>';
		} else {
			$input = in_array($type, array('email', 'tel', 'date', 'time', 'number'), true) ? $type : 'text';
			if ($type === 'phone') {
				$input = 'tel';
			}
			if ($type === 'upload') {
				$input = 'file';
			}
			$html_parts[] = '    <input type="' . esc_attr($input) . '" id="' . esc_attr($name) . '" name="' . esc_attr($name) . '" placeholder="' . esc_attr($ph) . '"' . $req . '>';
		}
		$html_parts[] = '  </div>';
	}
	$html_parts[] = '</div>';
	$combined = implode("\n", $html_parts) . "\n\n/**CSS**/\n.ez-po-wrap{display:grid;gap:12px}\n\n/**JS**/\n";
}

if (trim($combined) === '') {
	$combined = "<!-- HTML پالت -->\n<div class=\"ez-po-wrap\">\n\n</div>\n\n/**CSS**/\n/* استایل پالت */\n\n/**JS**/\n// اسکریپت پالت\n";
}
?>
<style>
.ezpo-code-shell{margin-top:12px;border-radius:14px;overflow:hidden;border:1px solid #1e293b;background:#0f172a;box-shadow:0 12px 32px rgba(15,23,42,.18)}
.ezpo-code-toolbar{display:flex;align-items:center;justify-content:space-between;gap:10px;padding:10px 14px;background:linear-gradient(180deg,#1e293b,#0f172a);border-bottom:1px solid #334155}
.ezpo-code-toolbar-left{display:flex;align-items:center;gap:8px;flex-wrap:wrap}
.ezpo-lang{display:inline-flex;align-items:center;gap:6px;padding:4px 10px;border-radius:999px;background:rgba(59,130,246,.15);color:#93c5fd;font-size:12px;font-weight:700}
.ezpo-lang-dot{width:8px;height:8px;border-radius:50%;background:#3b82f6;box-shadow:0 0 0 3px rgba(59,130,246,.25)}
.ezpo-code-hint{color:#94a3b8;font-size:11px}
.ezpo-code-tools{display:flex;gap:6px}
.ezpo-tool{display:inline-flex;align-items:center;justify-content:center;width:32px;height:32px;border:0;border-radius:8px;background:transparent;color:#cbd5e1;cursor:pointer}
.ezpo-tool:hover{background:rgba(148,163,184,.15);color:#fff}
.ezpo-code-body{min-height:360px}
.ezpo-code-body .CodeMirror{height:420px;font-size:13px;line-height:1.55;direction:ltr;text-align:left;border:0;background:#0b1220}
.ezpo-code-body .CodeMirror-gutters{background:#0a101c;border-right:1px solid #1e293b}
.ezpo-code-body .CodeMirror-linenumber{color:#64748b}
.ezpo-code-status{display:flex;justify-content:space-between;gap:10px;padding:8px 14px;background:#0b1220;border-top:1px solid #1e293b;color:#94a3b8;font-size:11px;font-variant-numeric:tabular-nums}
.ezpo-code-status strong{color:#e2e8f0;font-weight:700}
.ezpo-sep-note{margin:8px 0 0;color:#64748b;font-size:12px;line-height:1.6}
.ezpo-sep-note code{background:#e2e8f0;padding:1px 6px;border-radius:4px;font-size:11px}

/* Light readable code text on dark shell */
.ezpo-code-shell .CodeMirror,
.ezp-code-editor .CodeMirror {
  color: #e2e8f0 !important;
  background: #0b1220 !important;
  font-size: 13.5px !important;
  line-height: 1.65 !important;
}
.ezpo-code-shell .CodeMirror-cursor,
.ezp-code-editor .CodeMirror-cursor { border-left-color: #93c5fd !important; }
.ezpo-code-shell .CodeMirror-gutters,
.ezp-code-editor .CodeMirror-gutters { background: #0a101c !important; border-right: 1px solid #1e293b !important; }
.ezpo-code-shell .CodeMirror-linenumber,
.ezp-code-editor .CodeMirror-linenumber { color: #94a3b8 !important; }
.ezpo-code-shell .cm-s-default .cm-keyword,
.ezp-code-editor .cm-s-default .cm-keyword { color: #c4b5fd !important; }
.ezpo-code-shell .cm-s-default .cm-tag,
.ezp-code-editor .cm-s-default .cm-tag { color: #7dd3fc !important; }
.ezpo-code-shell .cm-s-default .cm-string,
.ezp-code-editor .cm-s-default .cm-string { color: #86efac !important; }
.ezpo-code-shell .cm-s-default .cm-comment,
.ezp-code-editor .cm-s-default .cm-comment { color: #94a3b8 !important; }
.ezpo-code-shell .cm-s-default .cm-attribute,
.ezp-code-editor .cm-s-default .cm-attribute { color: #fcd34d !important; }
.ezpo-code-shell .cm-s-default .cm-number,
.ezp-code-editor .cm-s-default .cm-number { color: #fdba74 !important; }
.ezpo-code-shell .CodeMirror-selected,
.ezp-code-editor .CodeMirror-selected { background: rgba(59,130,246,.28) !important; }
.ezpo-code-status { color: #cbd5e1 !important; }

</style>

<div class="card code-editor-card">
	<div class="card-header">
		<strong>ویرایشگر کد</strong>
		<span class="ezlens-muted" style="font-size:12px;margin-right:8px;">HTML + CSS + JS (مشابه فرآیند خرید)</span>
	</div>

	<div class="ezpo-code-shell">
		<div class="ezpo-code-toolbar">
			<div class="ezpo-code-toolbar-left">
				<span class="ezpo-lang"><span class="ezpo-lang-dot"></span> HTML / CSS / JS</span>
				<span class="ezpo-code-hint">جداکننده: <code style="color:#93c5fd">/**CSS**/</code> و <code style="color:#93c5fd">/**JS**/</code></span>
			</div>
			<div class="ezpo-code-tools">
				<button type="button" class="ezpo-tool" id="ezpo-code-copy" title="کپی کد">
					<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
				</button>
				<button type="button" class="ezpo-tool" id="ezpo-code-clear" title="پاک کردن کد">
					<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18"/><path d="M8 6V4h8v2"/><path d="M19 6l-1 14H6L5 6"/></svg>
				</button>
				<button type="button" class="ezpo-tool" id="ezpo-code-test" title="تست / پیش‌نمایش">
					<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="5 3 19 12 5 21 5 3"/></svg>
				</button>
				<button type="button" class="ezpo-tool" id="ezpo-code-format" title="تمیزکاری فاصله">
					<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 6h16M4 12h10M4 18h14"/></svg>
				</button>
			</div>
		</div>
		<div class="ezpo-code-body">
			<textarea id="code_editor" name="code_editor" class="large-text code" rows="18"><?php echo esc_textarea($combined); ?></textarea>
		</div>
		<div class="ezpo-code-status">
			<span>Ln <strong id="ezpo-ln">1</strong> · Col <strong id="ezpo-col">1</strong> · Lines <strong id="ezpo-lines">1</strong></span>
			<span>Chars <strong id="ezpo-chars">0</strong></span>
		</div>
	</div>
	<p class="ezpo-sep-note">ترتیب بلوک‌ها: HTML سپس <code>/**CSS**/</code> سپس <code>/**JS**/</code>. ذخیره به‌صورت AJAX همراه با فایل در <code>modules/product-options/storage/</code> انجام می‌شود.</p>
</div>

<script>
(function(){
	function toast(msg, ok) {
		var t = document.getElementById('ezpo-code-toast');
		if (!t) {
			t = document.createElement('div');
			t.id = 'ezpo-code-toast';
			t.style.cssText = 'position:fixed;bottom:24px;left:24px;z-index:100000;padding:12px 16px;border-radius:12px;color:#fff;font-size:13px;font-weight:700;box-shadow:0 12px 28px rgba(0,0,0,.25);opacity:0;transition:.25s;pointer-events:none';
			document.body.appendChild(t);
		}
		t.style.background = ok ? 'linear-gradient(135deg,#059669,#10b981)' : 'linear-gradient(135deg,#dc2626,#ef4444)';
		t.textContent = msg;
		t.style.opacity = '1';
		setTimeout(function(){ t.style.opacity = '0'; }, 2200);
	}
	function getVal(cm, ta) {
		try { if (cm && cm.getValue) return cm.getValue(); } catch(e){}
		return ta ? ta.value : '';
	}
	function setVal(cm, ta, v) {
		try { if (cm && cm.setValue) { cm.setValue(v); if (cm.save) cm.save(); } } catch(e){}
		if (ta) ta.value = v;
	}
	function boot(){
		var ta = document.getElementById('code_editor');
		if (!ta || typeof wp === 'undefined' || !wp.codeEditor) return;
		var ed = wp.codeEditor.initialize(ta, {
			codemirror: {
				lineNumbers: true,
				lineWrapping: true,
				mode: 'htmlmixed',
				indentUnit: 2,
				tabSize: 2,
				direction: 'ltr'
			}
		});
		var cm = ed && ed.codemirror ? ed.codemirror : null;
		if (!cm) return;
		window.ezpoCodeMirror = cm;
		window.ezpo_editor = { codemirror: cm };
		window.ezp_editor = window.ezpo_editor;
		window.getPoCodeValue = function(){ return getVal(cm, ta); };

		// Never auto-overwrite editor from builder on load.

		function syncStat(){
			var cur = cm.getCursor();
			var ln = document.getElementById('ezpo-ln');
			var col = document.getElementById('ezpo-col');
			var lines = document.getElementById('ezpo-lines');
			var chars = document.getElementById('ezpo-chars');
			if (ln) ln.textContent = cur.line + 1;
			if (col) col.textContent = cur.ch + 1;
			if (lines) lines.textContent = cm.lineCount();
			if (chars) chars.textContent = cm.getValue().length;
		}
		cm.on('cursorActivity', syncStat);
		cm.on('changes', syncStat);
		syncStat();

		var copyBtn = document.getElementById('ezpo-code-copy');
		if (copyBtn) copyBtn.addEventListener('click', function(){
			var v = getVal(cm, ta);
			function done(ok){ toast(ok ? 'کپی شد ✓' : 'کپی ناموفق', ok); }
			if (navigator.clipboard && navigator.clipboard.writeText) {
				navigator.clipboard.writeText(v).then(function(){ done(true); }).catch(function(){ done(false); });
			} else {
				try {
					var tmp = document.createElement('textarea');
					tmp.value = v; document.body.appendChild(tmp); tmp.select();
					done(document.execCommand('copy'));
					document.body.removeChild(tmp);
				} catch (e) { done(false); }
			}
		});

		var clearBtn = document.getElementById('ezpo-code-clear');
		if (clearBtn) clearBtn.addEventListener('click', function(){
			if (!window.confirm('کل محتوای ویرایشگر کد پاک شود؟')) return;
			setVal(cm, ta, '');
			toast('کد پاک شد', true);
			syncStat();
		});

		var testBtn = document.getElementById('ezpo-code-test');
		if (testBtn) testBtn.addEventListener('click', function(){
			if (typeof window.ezpoOpenTempPreview === 'function') {
				window.ezpoOpenTempPreview();
				return;
			}
			var code = getVal(cm, ta);
			if (!code.trim()) { toast('ویرایشگر خالی است', false); return; }
			var parts = code.split('/**CSS**/');
			var html = (parts[0] || '').trim();
			var rest = parts[1] || '';
			var parts2 = rest.split('/**JS**/');
			var css = (parts2[0] || '').trim();
			var js = (parts2[1] || '').trim();
			var doc = '<!DOCTYPE html><html lang="fa" dir="rtl"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>پیش‌نمایش</title>' +
				(css ? '<style>' + css + '</style>' : '') + '</head><body>' + html + (js ? '<script>' + js + '<\/script>' : '') + '</body></html>';
			var blob = new Blob([doc], { type: 'text/html;charset=utf-8' });
			var url = URL.createObjectURL(blob);
			if (!window.open(url, '_blank')) toast('پاپ‌آپ مسدود است', false);
			else toast('پیش‌نمایش باز شد', true);
			setTimeout(function(){ try{URL.revokeObjectURL(url);}catch(e){} }, 60000);
		});

		var fmtBtn = document.getElementById('ezpo-code-format');
		if (fmtBtn) fmtBtn.addEventListener('click', function(){
			var v = getVal(cm, ta);
			v = v.split('\n').map(function(l){ return l.replace(/[ \t]+$/,''); }).join('\n');
			setVal(cm, ta, v);
			toast('فاصله‌ها تمیز شد', true);
			syncStat();
		});
	}
	if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', boot);
	else boot();
})();
</script>
