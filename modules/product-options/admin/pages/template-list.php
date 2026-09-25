<?php
if (!defined('ABSPATH')) exit;
$real = __DIR__ . '/list/index.php';
if (is_readable($real)) { require $real; return; }
echo '<div class="wrap"><p style="color:#dc2626;">فایل یافت نشد.</p></div>';
