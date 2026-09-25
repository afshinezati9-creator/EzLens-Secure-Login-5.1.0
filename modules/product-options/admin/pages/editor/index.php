<?php
if (!defined('ABSPATH')) exit;

// Phase 5 modular assets

if (!function_exists('ezpo_get_svg_icon')) {
    function ezpo_get_svg_icon($name, $fallback = '') {
        $path = EZLAUTH_PLUGIN_DIR . 'assets/icons/modern/' . $name . '.svg';
        if (is_readable($path)) {
            $c = file_get_contents($path);
            if (false !== $c) return $c;
        }
        return $fallback;
    }
}

$_ezlens_editor_css = __DIR__ . '/assets/editor.css';
$_ezlens_editor_js  = __DIR__ . '/assets/editor.js';
$_ezlens_editor_css_url = EZLAUTH_MODULES_URL . 'product-options/admin/pages/editor/assets/editor.css';
$_ezlens_editor_js_url  = EZLAUTH_MODULES_URL . 'product-options/admin/pages/editor/assets/editor.js';
if (is_readable($_ezlens_editor_css)) {
    echo '<link rel="stylesheet" href="' . esc_url($_ezlens_editor_css_url) . '?v=' . esc_attr((string) filemtime($_ezlens_editor_css)) . '">';
}


$manager = EzLens_Product_Options_Template_Manager::get_instance();

// تشخیص حالت (افزودن یا ویرایش)
$template_id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$is_edit = $template_id > 0;

// اگر ویرایش است، داده‌ها را از دیتابیس بگیر
$template_data = null;
if ($is_edit) {
    $template_data = $manager->get($template_id);
    if (!$template_data) {
        echo '<div class="wrap"><h1>🧩 ویرایش پالت</h1><p style="color:#dc2626;">پالت یافت نشد.</p></div>';
        return;
    }
}

// مقداردهی پیش‌فرض
$title = $template_data['title'] ?? '';
$description = $template_data['description'] ?? '';
$fields = $template_data['fields'] ?? [];
$status = $template_data['status'] ?? 'active';
$custom_css = $template_data['custom_css'] ?? '';

// دریافت کدهای ذخیره‌شده
$code_html = $fields['_code_html'] ?? '';
$code_css  = $fields['_code_css'] ?? '';
$code_js   = $fields['_code_js'] ?? '';

// عملگرهای AJAX
$ajax_url = admin_url('admin-ajax.php');
$nonce = wp_create_nonce('ezlens_template_editor_nonce');
?>

<div class="wrap ezlens-template-editor">

    <!-- ===== هدر ===== -->
    <div class="ezlens-editor-header">
        <div class="ezlens-header-left">
            <h1 class="ezlens-page-title">
                <span
                    class="ezlens-icon-title"
                    style="background-image:url('<?php echo esc_url(EZLAUTH_PLUGIN_URL . 'assets/icons/modern/edit.svg'); ?>');">
                </span>

                <?php echo $is_edit ? 'ویرایش پالت' : 'افزودن پالت جدید'; ?>
            </h1>

            <span class="ezlens-badge-status">
                <?php echo $is_edit ? 'ویرایش' : 'جدید'; ?>
            </span>
        </div>

        <div class="ezlens-header-right">
            <a
                href="<?php echo esc_url(admin_url('admin.php?page=ezlens-product-options')); ?>"
                class="ezlens-btn ezlens-btn-outline">

                <span
                    class="ezlens-icon-btn"
                    style="background-image:url('<?php echo esc_url(EZLAUTH_PLUGIN_URL . 'assets/icons/modern/arrow-left.svg'); ?>');">
                </span>

                <span class="ezlens-btn-text">بازگشت به لیست</span>
            </a>

            <button
                type="button"
                class="ezlens-btn ezlens-btn-outline"
                id="ezlens-preview-palette-btn-header"
                title="پیش‌نمایش موقت فرم">
                <span class="ezlens-btn-text">پیش‌نمایش</span>
            </button>

            <button
                type="button"
                class="ezlens-btn ezlens-btn-primary"
                id="ezlens-save-template-btn">

                <span
                    class="ezlens-icon-btn"
                    style="background-image:url('<?php echo esc_url(EZLAUTH_PLUGIN_URL . 'assets/icons/modern/save.svg'); ?>');">
                </span>

                <span class="ezlens-btn-text">ذخیره پالت</span>
            </button>

            <span id="ezlens-save-status" class="ezlens-save-status"></span>
        </div>
    </div>

    <hr class="wp-header-end">

    <form id="template-editor-form" method="post">

        <input type="hidden" name="template_id" value="<?php echo esc_attr($template_id); ?>">
        <input type="hidden" name="action" value="ezlens_save_template">
        <input type="hidden" name="nonce" value="<?php echo esc_attr($nonce); ?>">

        <div class="editor-grid-3 editor-grid-no-live-preview">

            <!-- ستون چپ -->
            <div class="editor-left">

                <div class="form-group">
                    <label for="template_title">
                        عنوان پالت <span class="required">*</span>
                    </label>

                    <input
                        type="text"
                        id="template_title"
                        name="title"
                        value="<?php echo esc_attr($title); ?>"
                        placeholder="مثلاً: ویژگی‌های عینک آفتابی"
                        required>
                </div>

                <div class="form-group">
                    <label for="template_description">توضیحات</label>

                    <textarea
                        id="template_description"
                        name="description"
                        rows="2"
                        placeholder="توضیح مختصری درباره این پالت"><?php echo esc_textarea($description); ?></textarea>
                </div>

                <div class="form-row">

                    <div class="form-group half">
                        <label>وضعیت</label>

                        <div class="status-toggle">
                            <label class="toggle-switch">
                                <input
                                    type="checkbox"
                                    name="status"
                                    value="active"
                                    <?php checked($status, 'active'); ?>>

                                <span class="slider"></span>
                            </label>

                            <span class="status-label">
                                <?php echo $status === 'active' ? 'فعال' : 'غیرفعال'; ?>
                            </span>
                        </div>
                    </div>


                </div>

                <!-- بخش سازنده بصری -->
                <div class="editor-section">

                    <div class="section-header">

                        <h2>
                            <span
                                class="ezlens-icon-section"
                                style="background-image:url('<?php echo esc_url(EZLAUTH_PLUGIN_URL . 'assets/icons/modern/layers.svg'); ?>');">
                            </span>

                            سازنده بصری
                        </h2>

                        <span class="badge">بدون کد</span>

                        <button
                            type="button"
                            class="toggle-section"
                            data-target="visual-builder">
                            ▾
                        </button>

                    </div>

                    <div id="visual-builder" class="section-body">
                        <?php include __DIR__ . '/builder.php'; ?>
                    </div>

                </div>

                <!-- ویرایشگر کد -->
                <div class="editor-section">

                    <div class="section-header">

                        <h2>
                            <span
                                class="ezlens-icon-section"
                                style="background-image:url('<?php echo esc_url(EZLAUTH_PLUGIN_URL . 'assets/icons/modern/code.svg'); ?>');">
                            </span>

                            ویرایشگر کد
                        </h2>

                        <span class="badge">HTML + CSS + JS</span>

                        <button
                            type="button"
                            class="toggle-section"
                            data-target="code-editor">
                            ▾
                        </button>

                    </div>

                    <div id="code-editor" class="section-body">
                        <?php
                        if ( ! isset( $fields ) || ! is_array( $fields ) ) {
                            $fields = array();
                        }
                        if ( ! isset( $fields['_code_html'] ) ) {
                            $fields['_code_html'] = isset( $code_html ) ? $code_html : '';
                        }
                        if ( ! isset( $fields['_code_css'] ) ) {
                            $fields['_code_css'] = isset( $code_css ) ? $code_css : '';
                        }
                        if ( ! isset( $fields['_code_js'] ) ) {
                            $fields['_code_js'] = isset( $code_js ) ? $code_js : '';
                        }
                        include __DIR__ . '/code.php';
                        ?>
                    </div>

                </div>

            </div>

            <!-- سایدبار: ذخیره + پیش‌نمایش موقت -->
            <div class="editor-right editor-right-slim">
                <div class="card actions">
                    <button type="button" id="ezlens-preview-palette-btn" class="ezlens-btn ezlens-btn-outline btn-preview">
                        <span class="ezlens-icon-btn ezlens-icon-inline"><?php echo function_exists('ezpo_get_svg_icon') ? ezpo_get_svg_icon('eye', '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8S1 12 1 12z"/><circle cx="12" cy="12" r="3"/></svg>') : ''; ?></span>
                        <span class="ezlens-btn-text">پیش‌نمایش فرم</span>
                    </button>
                    <button type="button" id="ezlens-save-template-btn2" class="ezlens-btn ezlens-btn-primary btn-save">
                        <span class="ezlens-icon-btn" style="background-image:url('<?php echo esc_url(EZLAUTH_PLUGIN_URL . 'assets/icons/modern/save.svg'); ?>');"></span>
                        <span class="ezlens-btn-text">ذخیره پالت</span>
                    </button>
                    <button type="button" id="btn-duplicate" class="ezlens-btn ezlens-btn-secondary" style="display:<?php echo $is_edit ? 'flex' : 'none'; ?>;">
                        <span class="ezlens-icon-btn" style="background-image:url('<?php echo esc_url(EZLAUTH_PLUGIN_URL . 'assets/icons/modern/copy.svg'); ?>');"></span>
                        <span class="ezlens-btn-text">کپی پالت</span>
                    </button>
                    <p class="ezlens-preview-hint" style="margin:8px 0 0;font-size:12px;color:#64748b;line-height:1.5;">پیش‌نمایش در تب جدید باز می‌شود و فایل دائمی ذخیره نمی‌شود.</p>
                    <div id="save-status" class="save-status"></div>
                </div>
            </div>

        </div>
    </form>
</div>


<script>
window.ezlensPoEditor = window.ezlensPoEditor || {
  ajaxUrl: <?php echo wp_json_encode(admin_url('admin-ajax.php')); ?>,
  nonce: <?php echo wp_json_encode(wp_create_nonce('ezlens_template_editor_nonce')); ?>
};
</script>

<script id="ezlens-preview-bootstrap">
jQuery(function($){
  $(document).on('click.ezpoPreview', '#ezlens-preview-palette-btn, #ezlens-preview-palette-btn-header, .btn-preview', function(e){
    e.preventDefault();
    e.stopPropagation();
    if (typeof window.ezpoOpenTempPreview === 'function') {
      window.ezpoOpenTempPreview();
      return;
    }
    // ultra-fallback: open code editor text as page
    var code = (window.ezpoCodeMirror && window.ezpoCodeMirror.getValue) ? window.ezpoCodeMirror.getValue() : ($('#code_editor').val()||'');
    if (!code) { alert('ویرایشگر کد خالی است'); return; }
    var blob = new Blob([code], {type:'text/html;charset=utf-8'});
    var url = URL.createObjectURL(blob);
    if (!window.open(url, '_blank')) alert('پاپ‌آپ مسدود است');
    setTimeout(function(){ try{URL.revokeObjectURL(url);}catch(e){} }, 60000);
  });
});
</script>

<?php if (is_readable($_ezlens_editor_js)) : ?>
<script src="<?php echo esc_url($_ezlens_editor_js_url); ?>?v=<?php echo esc_attr((string) filemtime($_ezlens_editor_js)); ?>"></script>
<?php endif; ?>
