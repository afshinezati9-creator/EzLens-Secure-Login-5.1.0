<?php
if (!defined('ABSPATH')) exit;
$real = __DIR__ . '/settings/index.php';
if (is_readable($real)) {
	require $real;
	if (function_exists('ezlens_po_render_settings_page')) ezlens_po_render_settings_page();
	elseif (class_exists('EzLens_PO_Settings_Page')) EzLens_PO_Settings_Page::render();
	return;
}
echo '<div class="wrap"><p style="color:#dc2626;">فایل تنظیمات یافت نشد.</p></div>';
