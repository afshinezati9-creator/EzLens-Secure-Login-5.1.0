<?php
if (!defined('ABSPATH')) exit;

if (is_readable(dirname(__FILE__) . '/includes/class-template-file-storage.php')) {
    require_once dirname(__FILE__) . '/includes/class-template-file-storage.php';
}

class EzLens_Product_Options_Ajax {
    private static $instance = null;
    private $manager;

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        $this->manager = EzLens_Product_Options_Template_Manager::get_instance();

        // ===== عملیات اصلی =====
        add_action('wp_ajax_ezlens_save_template', [$this, 'save_template']);
        add_action('wp_ajax_ezlens_duplicate_template', [$this, 'duplicate_template']);
        add_action('wp_ajax_ezlens_get_template_preview', [$this, 'get_template_preview']);
        add_action('wp_ajax_ezlens_po_temp_preview', [$this, 'temp_preview_html']);
        add_action('wp_ajax_ezlens_upload_file', [$this, 'upload_file']);
        // Guest product pages need upload; hardened inside upload_file() (rate limit + mime + size).
        add_action('wp_ajax_nopriv_ezlens_upload_file', [$this, 'upload_file']);
        add_action('wp_ajax_ezlens_delete_template', [$this, 'delete_template']);
        add_action('wp_ajax_ezlens_create_preset', [$this, 'create_preset']);

        // ===== ✅ گام ۹: Export و Import =====
        add_action('wp_ajax_ezlens_export_templates', [$this, 'export_templates']);
        add_action('wp_ajax_ezlens_import_templates', [$this, 'import_templates']);
    }

    /**
     * ذخیره پالت (افزودن یا ویرایش)
     */
    public function save_template() {
        check_ajax_referer('ezlens_template_editor_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'دسترسی غیرمجاز'], 403);
        }

        $id = isset($_POST['template_id']) ? absint($_POST['template_id']) : 0;
        $title = sanitize_text_field(wp_unslash($_POST['title'] ?? ''));
        $description = sanitize_textarea_field(wp_unslash($_POST['description'] ?? ''));
        $status = sanitize_key(wp_unslash($_POST['status'] ?? 'active'));
        $status = in_array($status, ['active', 'inactive'], true) ? $status : 'active';

        if (empty($title)) {
            wp_send_json_error(['message' => 'عنوان قالب الزامی است.'], 400);
        }

        // دریافت فیلدها (از سازنده بصری)
        $fields_raw = $_POST['fields'] ?? [];
        if (is_string($fields_raw)) {
            $fields_raw = wp_unslash($fields_raw);
            $fields = json_decode($fields_raw, true);
            if (JSON_ERROR_NONE !== json_last_error()) {
                $fields = [];
            }
        } elseif (is_array($fields_raw)) {
            $fields = $fields_raw;
        } else {
            $fields = [];
        }

        // دریافت کدهای ویرایشگر (از باکس واحد)
        $code_editor = isset($_POST['code_editor']) ? wp_unslash($_POST['code_editor']) : '';
        if (!empty($code_editor)) {
            $parts = preg_split('/\/\*\*CSS\*\*\/|\/\*\*JS\*\*\//', $code_editor);
            $html = isset($parts[0]) ? trim($parts[0]) : '';
            $css = isset($parts[1]) ? trim($parts[1]) : '';
            $js = isset($parts[2]) ? trim($parts[2]) : '';
            $fields['_code_html'] = $html;
            $fields['_code_css'] = $css;
            $fields['_code_js'] = $js;
        }

        $data = [
            'title'       => $title,
            'description' => $description,
            'status'      => $status,
            'fields'      => $fields,
            'code'        => is_string($code_editor) ? $code_editor : '',
        ];

        if ($id > 0) {
            $result = $this->manager->update($id, $data);
            if (!empty($result['success'])) {
                $file = class_exists('EzLens_PO_Template_File_Storage')
                    ? EzLens_PO_Template_File_Storage::write($id, $data)
                    : false;
                wp_send_json_success([
                    'message' => 'پالت با موفقیت به‌روزرسانی شد.' . ($file ? ' فایل ذخیره شد.' : ''),
                    'id'      => $id,
                    'file'    => $file ? basename($file) : '',
                ]);
            }
            $reason = isset($result['message']) ? $result['message'] : 'به‌روزرسانی در دیتابیس ناموفق بود.';
            wp_send_json_error(['message' => 'خطا در ذخیره پالت', 'reason' => $reason], 500);
        }

        $result = $this->manager->create($data);
        if (!empty($result['success'])) {
            $new_id = isset($result['id']) ? (int) $result['id'] : 0;
            $file = ($new_id && class_exists('EzLens_PO_Template_File_Storage'))
                ? EzLens_PO_Template_File_Storage::write($new_id, $data)
                : false;
            wp_send_json_success([
                'message' => 'پالت جدید با موفقیت ایجاد شد.' . ($file ? ' فایل ذخیره شد.' : ''),
                'id'      => $new_id,
                'file'    => $file ? basename($file) : '',
                'redirect'=> admin_url('admin.php?page=ezlens-product-options&action=edit&id=' . $new_id),
            ]);
        }
        $reason = isset($result['message']) ? $result['message'] : 'ایجاد پالت در دیتابیس ناموفق بود.';
        wp_send_json_error(['message' => 'خطا در ایجاد پالت', 'reason' => $reason], 500);
    }

    /**
     * کپی پالت
     */
    public function duplicate_template() {
        check_ajax_referer('ezlens_template_editor_nonce', 'nonce');
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'دسترسی غیرمجاز'], 403);
        }

        $id = isset($_POST['template_id']) ? absint($_POST['template_id']) : 0;
        if (!$id) {
            wp_send_json_error(['message' => 'شناسه پالت نامعتبر است.', 'reason' => 'template_id خالی یا صفر است.'], 400);
        }

        $result = $this->manager->duplicate($id);
        if ($result['success']) {
            wp_send_json_success(['message' => 'قالب کپی شد.', 'id' => $result['id']]);
        }
        wp_send_json_error(['message' => $result['message']], 500);
    }

    /**
     * پیش‌نمایش فیلدها (برای متاباکس محصول)
     */

    /**
     * Temporary HTML preview of palette form with real field types (no disk file).
     */
    
    /**
     * Preview = exact code-editor contents (HTML + CSS + JS separators).
     * No field re-render — code editor is the source of truth.
     */
    public function temp_preview_html() {
        check_ajax_referer('ezlens_template_editor_nonce', 'nonce');
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'دسترسی غیرمجاز'), 403);
        }

        $title = isset($_POST['title']) ? sanitize_text_field(wp_unslash($_POST['title'])) : 'پیش‌نمایش';
        $code_raw = isset($_POST['code_editor']) ? wp_unslash($_POST['code_editor']) : '';
        if (!is_string($code_raw)) {
            $code_raw = '';
        }

        $html_code = $code_raw;
        $css_code  = '';
        $js_code   = '';
        if ($code_raw !== '' && (strpos($code_raw, '/**CSS**/') !== false || strpos($code_raw, '/**JS**/') !== false)) {
            $parts = preg_split('/\/\*\*CSS\*\*\/|\/\*\*JS\*\*\//', $code_raw);
            $html_code = isset($parts[0]) ? trim($parts[0]) : '';
            $css_code  = isset($parts[1]) ? trim($parts[1]) : '';
            $js_code   = isset($parts[2]) ? trim($parts[2]) : '';
        }

        $doc  = "<!DOCTYPE html>\n<html lang=\"fa\" dir=\"rtl\">\n<head>\n<meta charset=\"utf-8\">\n";
        $doc .= "<meta name=\"viewport\" content=\"width=device-width, initial-scale=1\">\n";
        $doc .= '<title>' . esc_html($title) . " — پیش‌نمایش کد</title>\n";
        $doc .= "<style>body{margin:0;font-family:Tahoma,Arial,sans-serif;background:#f8fafc;color:#0f172a}.ezpo-preview-badge{position:fixed;top:12px;left:12px;z-index:9999;padding:6px 12px;border-radius:999px;background:#0f172a;color:#fff;font-size:11px;font-weight:700;opacity:.85}</style>\n";
        if ($css_code !== '') {
            $doc .= "<style id=\"ezpo-from-editor\">\n" . $css_code . "\n</style>\n";
        }
        $doc .= "</head>\n<body>\n";
        $doc .= '<div class="ezpo-preview-badge">پیش‌نمایش از ویرایشگر کد · ذخیره نمی‌شود</div>\n';
        $doc .= $html_code . "\n";
        if ($js_code !== '') {
            $doc .= "<script>\n" . $js_code . "\n</script>\n";
        }
        $doc .= "</body>\n</html>";

        wp_send_json_success(array(
            'html'    => $doc,
            'message' => 'پیش‌نمایش از ویرایشگر کد',
            'source'  => 'code_editor',
        ));
    }


    public function get_template_preview() {
        check_ajax_referer('ezlens_template_preview_nonce', 'nonce');
        if (!current_user_can('edit_products') && !current_user_can('manage_woocommerce') && !current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'دسترسی غیرمجاز'], 403);
        }

        $template_id = isset($_POST['template_id']) ? absint($_POST['template_id']) : 0;
        if (!$template_id) {
            wp_send_json_error(['message' => 'شناسه پالت نامعتبر است.', 'reason' => 'template_id خالی یا صفر است.'], 400);
        }

        $template = $this->manager->get($template_id);
        if (!$template) {
            wp_send_json_error(['message' => 'قالب یافت نشد.'], 404);
        }

        $fields = [];
        foreach ((array) $template['fields'] as $key => $field) {
            if (strpos((string) $key, '_code_') === 0 || !is_array($field)) continue;
            $fields[] = [
                'label'    => $field['label'] ?? 'فیلد',
                'type'     => $field['type'] ?? 'text',
                'required' => !empty($field['required']),
            ];
        }

        wp_send_json_success([
            'fields' => $fields,
            'title'  => $template['title'],
            'status' => $template['status'],
        ]);
    }

    /**
     * آپلود فایل
     */
    public function upload_file() {
        check_ajax_referer('ezlens_po_nonce', 'nonce');

        // Rate limit: max 10 uploads / 10 minutes per IP (+ user id if logged in).
        $ip = isset($_SERVER['REMOTE_ADDR']) ? sanitize_text_field(wp_unslash($_SERVER['REMOTE_ADDR'])) : '0.0.0.0';
        $uid = get_current_user_id();
        $rl_key = 'ezlens_po_up_' . md5($ip . '|' . $uid);
        $rl_count = (int) get_transient($rl_key);
        if ($rl_count >= 10) {
            wp_send_json_error(['message' => 'تعداد آپلود بیش از حد مجاز است. چند دقیقه بعد دوباره تلاش کنید.'], 429);
        }
        set_transient($rl_key, $rl_count + 1, 10 * MINUTE_IN_SECONDS);

        if (!isset($_FILES['file']) || !is_array($_FILES['file'])) {
            wp_send_json_error(['message' => 'فایلی برای آپلود ارسال نشده است.'], 400);
        }

        $file = $_FILES['file'];
        $ferr = (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE);
        if ($ferr !== UPLOAD_ERR_OK) {
            $map = [
                UPLOAD_ERR_INI_SIZE   => 'حجم فایل از حد مجاز سرور بیشتر است.',
                UPLOAD_ERR_FORM_SIZE  => 'حجم فایل از حد مجاز فرم بیشتر است.',
                UPLOAD_ERR_PARTIAL    => 'فایل ناقص آپلود شد.',
                UPLOAD_ERR_NO_FILE    => 'فایلی ارسال نشده است.',
                UPLOAD_ERR_NO_TMP_DIR => 'پوشه موقت سرور در دسترس نیست.',
                UPLOAD_ERR_CANT_WRITE => 'نوشتن فایل روی سرور ممکن نشد.',
                UPLOAD_ERR_EXTENSION  => 'افزونه PHP آپلود را متوقف کرد.',
            ];
            wp_send_json_error(['message' => $map[$ferr] ?? 'خطا در آپلود فایل.'], 400);
        }

        $max_size = 5 * 1024 * 1024;
        $size = isset($file['size']) ? (int) $file['size'] : 0;
        if ($size <= 0 || $size > $max_size) {
            wp_send_json_error(['message' => 'حجم فایل باید بیشتر از صفر و حداکثر ۵ مگابایت باشد.'], 400);
        }

        if (empty($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
            wp_send_json_error(['message' => 'فایل آپلودشده معتبر نیست.'], 400);
        }

        $filename = sanitize_file_name($file['name'] ?? '');
        if ($filename === '') {
            wp_send_json_error(['message' => 'نام فایل نامعتبر است.'], 400);
        }

        $file['name'] = $filename;
        $allowed_mimes = [
            'jpg|jpeg|jpe' => 'image/jpeg',
            'png'          => 'image/png',
            'gif'          => 'image/gif',
            'webp'         => 'image/webp',
            'pdf'          => 'application/pdf',
            'doc'          => 'application/msword',
            'docx'         => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        ];
        $filetype = wp_check_filetype_and_ext($file['tmp_name'], $filename, $allowed_mimes);

        if (empty($filetype['ext']) || empty($filetype['type'])) {
            wp_send_json_error(['message' => 'نوع واقعی فایل مجاز نیست.'], 400);
        }

        if (!in_array($filetype['type'], array_values($allowed_mimes), true)) {
            wp_send_json_error(['message' => 'نوع فایل مجاز نیست.'], 400);
        }

        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/media.php';
        require_once ABSPATH . 'wp-admin/includes/image.php';

        $attachment_id = media_handle_upload('file', 0, [], [
            'test_form' => false,
            'mimes' => $allowed_mimes,
        ]);

        if (is_wp_error($attachment_id)) {
            wp_send_json_error(['message' => 'خطا در ذخیره فایل: ' . $attachment_id->get_error_message()], 500);
        }

        $url = wp_get_attachment_url($attachment_id);
        if (!$url) {
            wp_delete_attachment($attachment_id, true);
            wp_send_json_error(['message' => 'آدرس فایل ایجاد نشد.'], 500);
        }

        $mime = isset($filetype['type']) ? $filetype['type'] : '';
        wp_send_json_success([
            'url' => esc_url_raw($url),
            'filename' => basename($filename),
            'attachment_id' => (int) $attachment_id,
            'mime' => $mime,
            'size' => $size,
        ]);
    }

    /**
     * حذف پالت
     */
    public function delete_template() {
        // file cleanup runs after successful DB delete below
        /* patch-marker */
        check_ajax_referer('ezlens_template_delete_nonce', 'nonce');
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'دسترسی غیرمجاز'], 403);
        }

        $id = isset($_POST['template_id']) ? absint($_POST['template_id']) : 0;
        if (!$id) {
            wp_send_json_error(['message' => 'شناسه پالت نامعتبر است.', 'reason' => 'template_id خالی یا صفر است.'], 400);
        }

        $result = $this->manager->delete($id);
        if ($result['success']) {
            wp_send_json_success(['message' => $result['message']]);
        }
        wp_send_json_error(['message' => $result['message']], 500);
    }

    /**
     * ایجاد از پالت آماده (Preset)
     */
    public function create_preset() {
        check_ajax_referer('ezlens_template_editor_nonce', 'nonce');
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'دسترسی غیرمجاز'], 403);
        }

        $preset_id = sanitize_key($_POST['preset_id'] ?? '');
        $title = sanitize_text_field($_POST['title'] ?? '');
        if (empty($preset_id)) {
            wp_send_json_error(['message' => 'شناسه پالت آماده نامعتبر است.'], 400);
        }

        $result = $this->manager->create_from_preset($preset_id, $title);
        if (!empty($result['success'])) {
            wp_send_json_success([
                'message' => isset($result['message']) ? $result['message'] : 'پالت آماده در لیست ایجاد شد.',
                'id'      => isset($result['id']) ? (int) $result['id'] : 0,
            ]);
        }
        wp_send_json_error([
            'message' => isset($result['message']) ? $result['message'] : 'ایجاد پالت آماده ناموفق بود.',
            'reason'  => isset($result['reason']) ? $result['reason'] : ('preset_id=' . $preset_id),
        ], 500);
    }

    // ========================================================================
    // ===== ✅ گام ۹: Export و Import =====
    // ========================================================================

    /**
     * ✅ خروجی گرفتن از پالت‌ها (Export)
     * تمام پالت‌ها را به‌صورت JSON دانلود می‌کند
     */
    public function export_templates() {
        check_ajax_referer('ezlens_export_import_nonce', 'nonce');
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'دسترسی غیرمجاز'], 403);
            return;
        }

        // دریافت پارامترهای فیلتر
        $status = isset($_POST['status']) ? sanitize_key($_POST['status']) : 'all';
        $search = isset($_POST['search']) ? sanitize_text_field($_POST['search']) : '';
        $limit = isset($_POST['limit']) ? absint($_POST['limit']) : 9999;

        // دریافت تمام پالت‌ها
        $list = $this->manager->get_list([
            'status' => $status,
            'search' => $search,
            'limit'  => $limit,
            'offset' => 0
        ]);

        $templates = $list['items'];

        // آماده‌سازی داده‌ها برای خروجی
        $export_data = [
            'version'      => '1.0',
            'exported_at'  => current_time('mysql'),
            'site_url'     => home_url(),
            'total'        => count($templates),
            'templates'    => $templates
        ];

        wp_send_json_success([
            'data'     => $export_data,
            'filename' => 'ezlens-templates-backup-' . date('Y-m-d-H-i') . '.json'
        ]);
    }

    /**
     * ✅ وارد کردن پالت‌ها (Import)
     * فایل JSON را دریافت و پالت‌ها را به سیستم اضافه می‌کند
     */
    public function import_templates() {
        check_ajax_referer('ezlens_export_import_nonce', 'nonce');
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'دسترسی غیرمجاز'], 403);
            return;
        }

        // دریافت داده‌های JSON
        $json_data = isset($_POST['json_data']) ? wp_unslash($_POST['json_data']) : '';
        if (empty($json_data)) {
            wp_send_json_error(['message' => 'داده‌ای برای وارد کردن وجود ندارد.'], 400);
            return;
        }

        $data = json_decode($json_data, true);
        if (!is_array($data) || empty($data['templates']) || !is_array($data['templates'])) {
            wp_send_json_error(['message' => 'فرمت JSON نامعتبر است یا فاقد پالت‌های معتبر است.'], 400);
            return;
        }

        // پارامترهای وارد کردن
        $overwrite = isset($_POST['overwrite']) && $_POST['overwrite'] === '1';
        $templates = $data['templates'];
        $imported = 0;
        $skipped = 0;
        $updated = 0;
        $errors = [];

        foreach ($templates as $template) {
            // بررسی وجود عنوان
            if (empty($template['title'])) {
                $skipped++;
                continue;
            }

            // بررسی وجود پالت با عنوان مشابه
            $existing = $this->manager->get_list([
                'search' => $template['title'],
                'limit'  => 1,
                'offset' => 0
            ]);

            $exists = !empty($existing['items']);

            // اگر پالت وجود دارد و overwrite فعال نیست، رد کن
            if ($exists && !$overwrite) {
                $skipped++;
                continue;
            }

            // آماده‌سازی داده‌ها
            $template_data = [
                'title'       => $template['title'],
                'description' => $template['description'] ?? '',
                'fields'      => $template['fields'] ?? [],
                'status'      => $template['status'] ?? 'active'
            ];

            if ($exists && $overwrite) {
                // ویرایش پالت موجود
                $result = $this->manager->update($existing['items'][0]['id'], $template_data);
                if ($result['success']) {
                    $updated++;
                } else {
                    $errors[] = $template['title'] . ': ' . $result['message'];
                }
            } else {
                // ایجاد پالت جدید
                $result = $this->manager->create($template_data);
                if ($result['success']) {
                    $imported++;
                } else {
                    $errors[] = $template['title'] . ': ' . $result['message'];
                }
            }
        }

        wp_send_json_success([
            'imported' => $imported,
            'updated'  => $updated,
            'skipped'  => $skipped,
            'errors'   => $errors,
            'message'  => sprintf(
                '%d پالت جدید ایجاد شد، %d پالت به‌روزرسانی شد، %d پالت نادیده گرفته شد.',
                $imported,
                $updated,
                $skipped
            )
        ]);
    }
}

// مقداردهی اولیه
EzLens_Product_Options_Ajax::get_instance();