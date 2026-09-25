<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$book = EzLens_CD_Addresses::book();
$edit = isset( $_GET['edit'] ) ? sanitize_text_field( wp_unslash( $_GET['edit'] ) ) : '';
$item = null;
if ( $edit && 'new' !== $edit ) {
	foreach ( $book as $row ) {
		if ( ( $row['id'] ?? '' ) === $edit ) {
			$item = $row;
			break;
		}
	}
}
$is_form = ( 'new' === $edit ) || $item;
if ( ! $item ) {
	$item = array(
		'id' => '', 'label' => '', 'first_name' => '', 'last_name' => '',
		'address_1' => '', 'address_2' => '', 'plaque' => '', 'note' => '',
		'city' => '', 'state' => '', 'postcode' => '', 'phone' => '',
		'lat' => '35.6892', 'lng' => '51.3890',
	);
}
?>
<div class="ezcd-addresses">
	<?php if ( ! $is_form ) : ?>
		<div class="ezcd-addr-toolbar">
			<button type="button" class="ezcd-btn ezcd-btn-grad" data-ezcd-addr-edit="new">
				<span class="ezcd-btn-ico"><?php echo ezcd_icon( 'plus' ); ?></span>
				افزودن آدرس جدید
			</button>
		</div>
		<?php if ( empty( $book ) ) : ?>
			<div class="ezcd-empty ezcd-empty-rich">
				<div class="ezcd-empty-ico"><?php echo ezcd_icon( 'map-pin' ); ?></div>
				<p>هنوز آدرسی ذخیره نکرده‌اید. یک‌بار اضافه کنید تا در پرداخت بعدی فقط انتخابش کنید.</p>
			</div>
		<?php else : ?>
			<div class="ezcd-addr-grid">
				<?php foreach ( $book as $a ) :
					$line = EzLens_CD_Addresses::line( $a );
					?>
					<div class="ezcd-addr-card" data-id="<?php echo esc_attr( $a['id'] ); ?>">
						<div class="ezcd-addr-head">
							<span class="ezcd-ico"><?php echo ezcd_icon( 'map-pin' ); ?></span>
							<strong><?php echo esc_html( $a['label'] ?: 'آدرس' ); ?></strong>
						</div>
						<p class="ezcd-addr-line"><?php echo esc_html( $line ); ?></p>
						<?php if ( ! empty( $a['note'] ) ) : ?>
							<p class="ezcd-muted"><?php echo esc_html( $a['note'] ); ?></p>
						<?php endif; ?>
						<div class="ezcd-order-actions">
							<button type="button" class="ezcd-btn ezcd-btn-ghost" data-ezcd-addr-edit="<?php echo esc_attr( $a['id'] ); ?>">ویرایش</button>
							<button type="button" class="ezcd-btn ezcd-btn-danger-soft ezcd-addr-del" data-id="<?php echo esc_attr( $a['id'] ); ?>">حذف</button>
						</div>
					</div>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
	<?php else : ?>
		<button type="button" class="ezcd-btn ezcd-btn-back" data-ezcd-addr-edit="">
			<span class="ezcd-btn-ico"><?php echo ezcd_icon( 'arrow-right' ); ?></span>
			بازگشت به آدرس‌ها
		</button>
		<form class="ezcd-form ezcd-card-panel" id="ezcd-address-form" data-type="shipping">
			<input type="hidden" name="id" value="<?php echo esc_attr( $item['id'] ); ?>">
			<h2 class="ezcd-h2"><?php echo $item['id'] ? 'ویرایش آدرس' : 'آدرس جدید'; ?></h2>
			<p class="ezcd-hint">روی نقشه نقطه را انتخاب کنید یا همه فیلدها را دستی پر کنید. پلاک را حتماً بنویسید تا پیک راحت‌تر پیدا کند.</p>

			<div class="ezcd-map-wrap">
				<div id="ezcd-map" class="ezcd-map"
					data-lat="<?php echo esc_attr( $item['lat'] ?: '35.6892' ); ?>"
					data-lng="<?php echo esc_attr( $item['lng'] ?: '51.3890' ); ?>"
					data-plaque="<?php echo esc_attr( $item['plaque'] ); ?>"></div>
				<p class="ezcd-hint">با کلیک روی نقشه، موقعیت و پلاک روی نشانگر نمایش داده می‌شود.</p>
			</div>
			<input type="hidden" name="lat" id="ezcd-lat" value="<?php echo esc_attr( $item['lat'] ); ?>">
			<input type="hidden" name="lng" id="ezcd-lng" value="<?php echo esc_attr( $item['lng'] ); ?>">

			<label>عنوان آدرس
				<input type="text" name="label" value="<?php echo esc_attr( $item['label'] ); ?>" placeholder="مثال: خانه / محل کار">
			</label>
			<div class="ezcd-form-row">
				<label>نام<input type="text" name="first_name" value="<?php echo esc_attr( $item['first_name'] ); ?>" placeholder="سارا" required></label>
				<label>نام خانوادگی<input type="text" name="last_name" value="<?php echo esc_attr( $item['last_name'] ); ?>" placeholder="محمدی" required></label>
			</div>
			<label>آدرس
				<input type="text" name="address_1" id="ezcd-address-1" value="<?php echo esc_attr( $item['address_1'] ); ?>" placeholder="خیابان و کوچه" required>
			</label>
			<div class="ezcd-form-row">
				<label>پلاک
					<input type="text" name="plaque" id="ezcd-plaque" value="<?php echo esc_attr( $item['plaque'] ); ?>" placeholder="۱۲" dir="ltr" required>
				</label>
				<label>واحد / طبقه
					<input type="text" name="address_2" value="<?php echo esc_attr( $item['address_2'] ); ?>" placeholder="واحد ۳" dir="ltr">
				</label>
			</div>
			<label>توضیحات برای پیک
				<textarea name="note" rows="2" placeholder="زنگ دوم، کنار نانوایی"><?php echo esc_textarea( $item['note'] ); ?></textarea>
			</label>
			<div class="ezcd-form-row">
				<label>شهر<input type="text" name="city" id="ezcd-city" value="<?php echo esc_attr( $item['city'] ); ?>" required></label>
				<label>استان<input type="text" name="state" id="ezcd-state" value="<?php echo esc_attr( $item['state'] ); ?>"></label>
			</div>
			<div class="ezcd-form-row">
				<label>کد پستی<input type="text" name="postcode" value="<?php echo esc_attr( $item['postcode'] ); ?>" placeholder="۱۲۳۴۵۶۷۸۹۰" dir="ltr" inputmode="numeric"></label>
				<label>موبایل گیرنده<input type="text" name="phone" value="<?php echo esc_attr( $item['phone'] ); ?>" placeholder="۰۹۱۲۳۴۵۶۷۸۹" dir="ltr" inputmode="tel"></label>
			</div>
			<div class="ezcd-modal-actions">
				<button type="button" class="ezcd-btn ezcd-btn-ghost" data-ezcd-addr-edit="">انصراف</button>
				<button type="submit" class="ezcd-btn ezcd-btn-grad">ذخیره آدرس</button>
			</div>
			<p class="ezcd-form-msg" hidden></p>
		</form>
	<?php endif; ?>
</div>
