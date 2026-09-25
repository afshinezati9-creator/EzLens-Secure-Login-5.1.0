jQuery(document).ready(function($) {

    'use strict';


    /* ============================================================
       تا کردن / باز کردن بخش‌ها
       ============================================================ */

    $('.toggle-section').on('click', function() {

        var target = $(this).data('target');
        var $target = $('#' + target);

        $target.toggleClass('collapsed');

        $(this).text(
            $target.hasClass('collapsed')
                ? '▸'
                : '▾'
        );
    });


    /* ============================================================
       تغییر وضعیت
       ============================================================ */

    $('.toggle-switch input[type="checkbox"]').on('change', function() {

        var label = $(this)
            .closest('.status-toggle')
            .find('.status-label');

        label.text(
            $(this).prop('checked')
                ? 'فعال'
                : 'غیرفعال'
        );
    });


    /* ============================================================
       انتخاب تم CSS
       ============================================================ */

    $('#css-theme-select').on('change', function() {

        var theme = $(this).val();

        var themes = {

            'default':
                '/* استایل پیش‌فرض */',

            'dark':
                '.ezlens-product-options-wrapper { background: #1a202c; color: #e2e8f0; } .ezlens-options-title { border-color: #2d3748; color: #f1f5f9; }',

            'glass':
                '.ezlens-product-options-wrapper { background: rgba(255,255,255,0.6); backdrop-filter: blur(10px); }',

            'minimal':
                '.ezlens-product-options-wrapper { border: none; box-shadow: none; padding: 0; }'
        };

        $('#custom-css-editor').val(
            themes[theme] || ''
        );

        if (typeof updatePreview === 'function') {
            updatePreview();
        }
    });


    /* ============================================================
       کپی پالت
       ============================================================ */

    $('#btn-duplicate').on('click', function() {

        if (!confirm('آیا از این پالت کپی تهیه شود؟')) {
            return;
        }

        var templateId = $('input[name="template_id"]').val();

        $.post(
            ajaxurl,
            {
                action: 'ezlens_duplicate_template',

                nonce: (window.ezlensPoEditor && ezlensPoEditor.nonce) || '',

                template_id: templateId
            },
            function(response) {

                if (response.success) {

                    window.location.href =
                        '?page=ezlens-product-options&action=edit&id=' +
                        response.data.id;

                } else {

                    alert(
                        'خطا: ' +
                        (
                            response.data.message ||
                            'خطای نامشخص'
                        )
                    );
                }
            }
        );
    });


    /* ============================================================
       شمارشگر خطوط
       ============================================================ */

    var $textarea = $('#code-editor-input');
    var $lineNumbers = $('#code-line-numbers');


    function updateLineNumbers() {

        if (!$textarea.length || !$lineNumbers.length) {
            return;
        }

        var lines = $textarea.val().split('\n');

        var count = lines.length;

        var numbers = [];

        for (var i = 1; i <= count; i++) {
            numbers.push(i);
        }

        $lineNumbers.text(
            numbers.join('\n')
        );

        syncScroll();
    }


    function syncScroll() {

        if (!$textarea.length || !$lineNumbers.length) {
            return;
        }

        $lineNumbers.scrollTop(
            $textarea.scrollTop()
        );
    }


    $textarea.on('input', function() {

        updateLineNumbers();

        if (
            typeof window.updatePreview === 'function'
        ) {

            clearTimeout(
                window._codeEditorTimeout
            );

            window._codeEditorTimeout =
                setTimeout(function() {

                    window.updatePreview();

                }, 400);
        }
    });


    $textarea.on('scroll', syncScroll);

    $(window).on('resize', syncScroll);


    /* ============================================================
       فرمت کد
       ============================================================ */

    $('#code-format').on('click', function() {

        try {

            var val = $textarea.val();

            var lines = val
                .split('\n')
                .map(function(line) {
                    return line.trim();
                });

            var formatted = [];

            var lastEmpty = false;

            lines.forEach(function(line) {

                if (line === '') {

                    if (!lastEmpty) {
                        formatted.push('');
                        lastEmpty = true;
                    }

                } else {

                    formatted.push(line);

                    lastEmpty = false;
                }
            });

            $textarea.val(
                formatted.join('\n')
            );

            updateLineNumbers();

            if (
                typeof window.updatePreview === 'function'
            ) {
                window.updatePreview();
            }

        } catch (e) {

            alert('خطا در فرمت کد');
        }
    });


    /* ============================================================
       داده‌های سازنده
       ============================================================ */

    window.getFieldsData = function() {

        var fields = [];

        $('.ezlens-field-card:not(.ezlens-child-card)').each(function() {

            var $field = $(this);

            var type =
                $field.data('type') ||
                'text';

            var label =
                $field.find('.field-label').val() ||
                'فیلد';

            var required =
                $field.find('.field-required').is(':checked');

            var price =
                parseFloat(
                    $field.find('.field-price').val()
                ) || 0;

            var width =
                $field.find('.field-width').val() ||
                'full';

            fields.push({
                label: label,
                type: type,
                required: required,
                price: price,
                width: width
            });
        });

        return fields;
    };


    /* ============================================================
       پیش‌نمایش
       ============================================================ */

    window.updatePreview = function() {

        var preview =
            $('#preview-container');

        var fields =
            window.getFieldsData
                ? window.getFieldsData()
                : [];

        if (
            !fields ||
            fields.length === 0
        ) {

            preview.html(
                '<div class="preview-placeholder">هنوز فیلدی اضافه نشده است.</div>'
            );

            $('#preview-field-count').text(
                '۰ فیلد'
            );

            return;
        }


        var html =
            '<div style="display:flex;flex-wrap:wrap;gap:6px;">';


        fields.forEach(function(f) {

            var width =
                f.width === 'half'
                    ? 'calc(50% - 4px)'
                    : f.width === 'third'
                        ? 'calc(33.33% - 6px)'
                        : f.width === 'quarter'
                            ? 'calc(25% - 6px)'
                            : '100%';


            html +=
                '<div class="preview-field" style="width:' +
                width +
                ';">';


            html +=
                '<span class="label">' +
                (f.label || 'فیلد') +
                (
                    f.required
                        ? ' <span style="color:#dc2626;">*</span>'
                        : ''
                ) +
                '</span>';


            if (f.price > 0) {

                html +=
                    '<span class="value" style="color:#2b6cb0;font-weight:600;">+' +
                    f.price +
                    ' تومان</span>';
            }


            html += '</div>';
        });


        html += '</div>';

        preview.html(html);


        $('#preview-field-count').text(
            fields.length + ' فیلد'
        );


        if (
            typeof window.syncBuilderToCode === 'function'
        ) {
            window.syncBuilderToCode();
        }
    };


    /* ============================================================
       دستگاه پیش‌نمایش
       ============================================================ */

    $('#preview-device').on('change', function() {

        var device = $(this).val();

        var $box =
            $('#preview-container');

        $box.removeClass(
            'preview-desktop preview-tablet preview-mobile'
        );

        $box.addClass(
            'preview-' + device
        );

        if (
            typeof updatePreview === 'function'
        ) {
            updatePreview();
        }
    });


    /* ============================================================
       رفرش پیش‌نمایش
       ============================================================ */

    $('#refresh-preview').on('click', function() {

        if (
            typeof updatePreview === 'function'
        ) {
            updatePreview();
        }

        $(this).text('✓');

        var $button = $(this);

        setTimeout(function() {
            $button.text('↻');
        }, 1000);
    });


    /* ============================================================
       تغییرات سازنده
       ============================================================ */

    $(document).on(
        'change keyup',
        '.ezlens-field-card input, .ezlens-field-card select',
        function() {

            if (
                typeof updatePreview === 'function'
            ) {
                updatePreview();
            }
        }
    );


    /* ============================================================
       ذخیره AJAX
       ============================================================ */

    function saveTemplate() {

        var $form =
            $('#template-editor-form');

        var $status =
            $('#ezlens-save-status, #save-status');


        $status.html(
            '<span style="color:#2b6cb0;">در حال ذخیره...</span>'
        );


        // Flush CodeMirror → textarea before FormData
        try {
            if (window.ezpoCodeMirror && typeof window.ezpoCodeMirror.save === 'function') {
                window.ezpoCodeMirror.save();
            } else if (window.ezpo_editor && window.ezpo_editor.codemirror && window.ezpo_editor.codemirror.save) {
                window.ezpo_editor.codemirror.save();
            }
        } catch (eFlush) {}

        var formData = new FormData($form[0]);

        var codeEditor = '';
        try {
            if (typeof window.getPoCodeValue === 'function') {
                codeEditor = window.getPoCodeValue() || '';
            } else if (window.ezpoCodeMirror && window.ezpoCodeMirror.getValue) {
                codeEditor = window.ezpoCodeMirror.getValue() || '';
            } else if (window.ezpo_editor && window.ezpo_editor.codemirror) {
                codeEditor = window.ezpo_editor.codemirror.getValue() || '';
            }
        } catch (eCode) {}
        if (!codeEditor) {
            codeEditor = $('#code_editor').val() || $('#code-editor-input').val() || '';
        }

        // Force real editor content (overwrite any empty form field)
        formData.set('code_editor', codeEditor);
        formData.append('action', (window.ezlensPoEditor && ezlensPoEditor.saveAction) ? ezlensPoEditor.saveAction : 'ezlens_save_template');



        var fieldsJson =
            $('#ezlens-builder-fields-json').val();


        if (fieldsJson) {

            formData.append(
                'fields',
                fieldsJson
            );
        }


        $.ajax({

            url: (window.ezlensPoEditor && ezlensPoEditor.ajaxUrl) || (typeof ajaxurl !== 'undefined' ? ajaxurl : '/wp-admin/admin-ajax.php'),

            type: 'POST',

            data: formData,

            processData: false,

            contentType: false,


            success: function(response) {

                if (response.success) {

                    $status.html(
                        '<span style="color:#16a34a;">' +
                        response.data.message +
                        '</span>'
                    );


                    var existingId = parseInt($('input[name="template_id"]').val() || $('#template_id').val() || '0', 10) || 0;
                    var newId = response.data && response.data.id ? parseInt(response.data.id, 10) : 0;
                    // Only navigate when creating a brand-new palette (no id yet)
                    if (existingId < 1 && newId > 0) {
                        setTimeout(function() {
                            window.location.href = '?page=ezlens-product-options&action=edit&id=' + newId;
                        }, 900);
                    }
                    // Existing edit: keep page (code stays in editor)

                } else {

                    $status.html(
                        '<span style="color:#dc2626;">' +
                        response.data.message +
                        '</span>'
                    );
                }
            },


            error: function(
                xhr,
                status,
                error
            ) {

                $status.html(
                    '<span style="color:#dc2626;">' +
                    'خطا در ارتباط با سرور: ' +
                    error +
                    '</span>'
                );

                console.log(
                    'AJAX Error:',
                    xhr.responseText
                );
            }
        });
    }


    /* ============================================================
       دکمه‌های ذخیره
       ============================================================ */

    $(
        '#ezlens-save-template-btn, #ezlens-save-template-btn2'
    ).on('click', function(e) {

        e.preventDefault();

        saveTemplate();
    });


    /* ============================================================
       جلوگیری از Submit با Enter
       ============================================================ */

    $('#template-editor-form').on(
        'keydown',
        function(e) {

            if (
                e.key === 'Enter' &&
                $(e.target).is(
                    'input, textarea'
                )
            ) {

                e.preventDefault();
            }
        }
    );


    /* ============================================================
       بارگذاری اولیه
       ============================================================ */

    setTimeout(function() {

        updateLineNumbers();


        if (
            typeof window.syncBuilderToCode === 'function'
        ) {
            window.syncBuilderToCode();
        }


        if (
            typeof window.updatePreview === 'function'
        ) {
            window.updatePreview();
        }

    }, 300);

});


/* ---- EzLens PO: toast + status helpers (path fix batch) ---- */
(function ($) {
  function poNotify(msg, type, reason) {
    var full = msg + (reason ? " — " + reason : "");
    var $s = $("#ezlens-save-status, #save-status");
    if ($s.length) {
      $s.removeClass("is-ok is-err is-error is-success")
        .addClass(type === "ok" || type === "success" ? "is-ok is-success" : "is-err is-error")
        .text(full)
        .show();
    }
    try {
      if (window.console && console.log) console.log("[EzLens PO]", full);
    } catch (e) {}
    if (type !== "ok" && type !== "success") {
      // non-blocking
    }
  }
  // Patch jQuery ajax success for save if global handler missing
  $(document).on("ezlensPo:saved", function (e, res) {
    if (!res) return;
    if (res.success) {
      poNotify((res.data && res.data.message) || "ذخیره شد", "ok");
      if (res.data && res.data.redirect && !(res.data.id && $("input[name=template_id]").val() > 0)) {
        // optional redirect for new
      }
    } else {
      poNotify((res.data && res.data.message) || "خطا", "err", res.data && res.data.reason);
    }
  });
})(jQuery);

/* Preview = exact CodeMirror contents */
(function ($) {
  'use strict';

  function poToast(msg, type) {
    type = type || 'info';
    var $t = $('#ezpo-toast');
    if (!$t.length) {
      $t = $('<div id="ezpo-toast" aria-live="polite"></div>').appendTo('body');
      if (!$('#ezpo-toast-style').length) {
        $('head').append('<style id="ezpo-toast-style">#ezpo-toast{position:fixed;bottom:24px;left:24px;z-index:100000;min-width:240px;max-width:400px;padding:14px 16px;border-radius:12px;color:#fff;font-size:13px;font-weight:600;box-shadow:0 12px 32px rgba(15,23,42,.28);opacity:0;transform:translateY(12px);transition:.25s ease}#ezpo-toast.is-show{opacity:1;transform:none}#ezpo-toast.is-ok{background:linear-gradient(135deg,#059669,#10b981)}#ezpo-toast.is-err{background:linear-gradient(135deg,#dc2626,#ef4444)}#ezpo-toast.is-info{background:linear-gradient(135deg,#1d4ed8,#3b82f6)}</style>');
      }
    }
    $t.removeClass('is-show is-ok is-err is-info')
      .addClass(type === 'ok' || type === 'success' ? 'is-ok' : (type === 'err' || type === 'error' ? 'is-err' : 'is-info'))
      .text(msg);
    void $t[0].offsetWidth;
    $t.addClass('is-show');
    setTimeout(function () { $t.removeClass('is-show'); }, 3200);
  }

  function getEditorCode() {
    try {
      if (window.ezpoCodeMirror && window.ezpoCodeMirror.getValue) return window.ezpoCodeMirror.getValue();
      if (window.ezpo_editor && window.ezpo_editor.codemirror) return window.ezpo_editor.codemirror.getValue();
      if (typeof window.getPoCodeValue === 'function') return window.getPoCodeValue() || '';
    } catch (e) {}
    return ($('#code_editor').val() || '');
  }

  /** Build full HTML document from exact editor text */
  function docFromCode(title, code) {
    code = code == null ? '' : String(code);
    var html = code, css = '', js = '';
    if (code.indexOf('/**CSS**/') !== -1 || code.indexOf('/**JS**/') !== -1) {
      var parts = code.split(/\*\*CSS\*\*\/|\*\*JS\*\*\//);
      // split on /**CSS**/ and /**JS**/ leaves empties carefully
      parts = code.split('/**CSS**/');
      html = (parts[0] || '').trim();
      var rest = parts[1] || '';
      var parts2 = rest.split('/**JS**/');
      css = (parts2[0] || '').trim();
      js = (parts2[1] || '').trim();
    }
    return '<!DOCTYPE html><html lang="fa" dir="rtl"><head><meta charset="utf-8">' +
      '<meta name="viewport" content="width=device-width,initial-scale=1">' +
      '<title>' + $('<div>').text(title || 'پیش‌نمایش').html() + '</title>' +
      '<style>body{margin:0;font-family:Tahoma,Arial,sans-serif}.ezpo-preview-badge{position:fixed;top:12px;left:12px;z-index:9999;padding:6px 12px;border-radius:999px;background:#0f172a;color:#fff;font-size:11px;font-weight:700;opacity:.9}</style>' +
      (css ? '<style id="from-editor">' + css + '</style>' : '') +
      '</head><body><div class="ezpo-preview-badge">پیش‌نمایش ویرایشگر کد</div>' +
      html +
      (js ? '<script>' + js + '<\/script>' : '') +
      '</body></html>';
  }

  function openTempPreview() {
    var title = $('#template_title').val() || 'پیش‌نمایش';
    var code = getEditorCode();
    if (!code || !String(code).trim()) {
      poToast('ویرایشگر کد خالی است', 'err');
      return;
    }
    poToast('در حال باز کردن پیش‌نمایش…', 'info');

    var html = docFromCode(title, code);
    var blob = new Blob([html], { type: 'text/html;charset=utf-8' });
    var url = URL.createObjectURL(blob);
    var w = window.open(url, '_blank');
    if (!w) {
      poToast('پنجره مسدود شد — اجازه پاپ‌آپ بدهید', 'err');
    } else {
      poToast('پیش‌نمایش عین ویرایشگر کد باز شد', 'ok');
    }
    setTimeout(function () { try { URL.revokeObjectURL(url); } catch (e) {} }, 60000);
  }

  window.ezpoOpenTempPreview = openTempPreview;
  $(document).on('click', '#ezlens-preview-palette-btn, #ezlens-preview-palette-btn-header, .btn-preview', function (e) {
    e.preventDefault();
    e.stopPropagation();
    openTempPreview();
  });
})(jQuery);
