<?php
/**
 * صفحات ورود — لیست (ویرایشگر یکسان با فرآیند خرید)
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
if ( ! class_exists( 'EzLens_Login_Pages_Loader' ) ) {
	echo '<div class="wrap"><p>ماژول صفحات ورود لود نشده است.</p></div>';
	return;
}
EzLens_Login_Pages_Loader::get_instance()->render_list();
