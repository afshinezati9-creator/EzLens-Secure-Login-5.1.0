<?php
/**
 * عنوان: سبد خرید حرفه‌ای
 * نام فایل: custom-cart-page
 * نسخه: 3.1
 * شورت‌کد: [custom_cart]
 */

if ( ! defined( 'ABSPATH' ) ) exit;

// ============================================================
// 1. آیکون‌ها
// ============================================================
if ( ! function_exists( 'ezp_cart_icon' ) ) {
	function ezp_cart_icon( $name, $fallback = '' ) {
		$path = EZLAUTH_PLUGIN_DIR . 'assets/icons/modern/' . $name . '.svg';
		if ( file_exists( $path ) ) {
			$content = file_get_contents( $path );
			if ( false !== $content && trim( $content ) !== '' ) return $content;
		}
		return $fallback;
	}
}

if ( ! function_exists( 'ezp_cart_fa' ) ) {
	function ezp_cart_fa( $text ) {
		if ( is_array( $text ) ) $text = implode( '', $text );
		return str_replace(
			array( '0','1','2','3','4','5','6','7','8','9' ),
			array( '۰','۱','۲','۳','۴','۵','۶','۷','۸','۹' ),
			(string) $text
		);
	}
}

// ============================================================
// 2. برچسب فارسی فیلدها
// ============================================================
if ( ! function_exists( 'ezp_cart_field_label' ) ) {
	function ezp_cart_field_label( $key ) {
		$key = preg_replace( '/^\d+:/', '', $key );

		$labels = array(
			'power_od'    => 'Power چشم راست',
			'power_os'    => 'Power چشم چپ',
			'power'       => 'Power',
			'bc'          => 'BC (پایه)',
			'dia'         => 'DIA (قطر)',
			'lens_color'  => 'رنگ لنز',
			'lens_type'   => 'نوع لنز',
			'axis'        => 'محور (Axis)',
			'cylinder'    => 'سیلندر',
			'addition'    => 'Addition',
			'color'       => 'رنگ',
			'size'        => 'سایز',
		);
		if ( isset( $labels[ $key ] ) ) return $labels[ $key ];
		return ucfirst( str_replace( '_', ' ', $key ) );
	}
}

// ============================================================
// 3. برچسب فارسی مقدار
// ============================================================
if ( ! function_exists( 'ezp_cart_value_label' ) ) {
	function ezp_cart_value_label( $key, $val ) {
		$map = array(
			'clear'     => 'شفاف',
			'blue'      => 'آبی',
			'green'     => 'سبز',
			'brown'     => 'قهوه‌ای',
			'daily'     => 'روزانه',
			'monthly'   => 'ماهانه',
			'quarterly' => 'سه‌ماهه',
			'yearly'    => 'سالانه',
		);
		if ( isset( $map[ $val ] ) ) return $map[ $val ];
		return ezp_cart_fa( $val );
	}
}

// ============================================================
// 4. بررسی ارسال رایگان
// ============================================================
if ( ! function_exists( 'ezp_cart_check_free_shipping' ) ) {
	function ezp_cart_check_free_shipping() {
		if ( ! function_exists( 'WC' ) || ! WC()->cart ) {
			return array( 'enabled' => false, 'threshold' => 500000 );
		}
		$threshold = 500000;
		if ( class_exists( 'WC_Shipping_Zones' ) ) {
			$zones = array_merge(
				array( array( 'zone_id' => 0 ) ),
				WC_Shipping_Zones::get_zones()
			);
			foreach ( $zones as $zone_data ) {
				$zone = WC_Shipping_Zones::get_zone( $zone_data['zone_id'] );
				if ( ! $zone ) continue;
				foreach ( $zone->get_shipping_methods() as $method ) {
					if ( $method->id === 'free_shipping' ) {
						$min = (float) $method->get_option( 'min_amount' );
						if ( $min > 0 ) $threshold = $min;
						break 2;
					}
				}
			}
		}
		return array( 'enabled' => true, 'threshold' => $threshold );
	}
}

// ============================================================
// 5. استخراج تمیز فیلدهای EzLens
// ============================================================
if ( ! function_exists( 'ezp_cart_extract_fields' ) ) {
	function ezp_cart_extract_fields( $cart_item ) {
		$fields = array();
		if ( empty( $cart_item['ezlens_options'] ) || ! is_array( $cart_item['ezlens_options'] ) ) {
			return $fields;
		}
		foreach ( $cart_item['ezlens_options'] as $key => $val ) {
			if ( $val === '' || $val === null ) continue;
			$clean_key = preg_replace( '/^\d+:/', '', $key );
			$clean_key = sanitize_key( str_replace( ' ', '_', $clean_key ) );
			$label = ezp_cart_field_label( $clean_key );
			$value = ezp_cart_value_label( $clean_key, $val );
			if ( preg_match( '/\.(jpg|jpeg|png|gif|webp|pdf)$/i', $val ) ) {
				$value = 'فایل پیوست';
			}
			$fields[] = array( 'label' => $label, 'value' => $value );
		}
		return $fields;
	}
}

// ============================================================
// 6. شورت‌کد اصلی
// ============================================================
if ( ! function_exists( 'ezp_custom_cart_shortcode' ) ) {

	add_shortcode( 'custom_cart', 'ezp_custom_cart_shortcode' );

	function ezp_custom_cart_shortcode( $atts = array() ) {

		if ( ! function_exists( 'WC' ) || ! WC()->cart ) {
			return '<div class="ezp-cart-empty"><p>ووکامرس فعال نیست.</p></div>';
		}

		$cart       = WC()->cart;
		$cart_items = $cart->get_cart();

		$subtotal       = (float) $cart->get_subtotal();
		$cart_count     = (int) $cart->get_cart_contents_count();
		$free_ship_info = ezp_cart_check_free_shipping();
		$free_ship_min  = $free_ship_info['threshold'];
		$remaining      = max( 0, $free_ship_min - $subtotal );
		$progress       = $free_ship_min > 0 ? min( 100, ( $subtotal / $free_ship_min ) * 100 ) : 100;
		$is_free_ship   = $remaining <= 0;

		if ( empty( $cart_items ) ) {
			ob_start();
			?>
			<div class="ezp-cart-page" dir="rtl">
				<style><?php echo ezp_cart_css(); ?></style>
				<div class="ezp-cart-empty">
					<div class="ezp-cart-empty-icon">
						<?php echo ezp_cart_icon( 'cart', '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="9" cy="20" r="1.5"/><circle cx="18" cy="20" r="1.5"/><path d="M3 3h2l2.5 12h11l2-8H6"/></svg>' ); ?>
					</div>
					<h2>سبد خرید شما خالی است</h2>
					<p>محصولات مورد علاقه خود را انتخاب کنید و به سبد اضافه کنید.</p>
					<a href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>" class="ezp-cart-btn-primary">
						<?php echo ezp_cart_icon( 'shop', '' ); ?>
						<span>مشاهده فروشگاه</span>
					</a>
				</div>
			</div>
			<?php
			return ob_get_clean();
		}

		$cross_sell_ids = $cart->get_cross_sells();
		$cross_sells = array();
		if ( ! empty( $cross_sell_ids ) ) {
			$cross_sells = wc_get_products( array(
				'include' => $cross_sell_ids,
				'limit'   => 4,
				'status'  => 'publish',
			) );
		}

		$checkout_url = wc_get_checkout_url();
		$shop_url     = wc_get_page_permalink( 'shop' );

		ob_start();
		?>
		<div class="ezp-cart-page" dir="rtl">
			<style><?php echo ezp_cart_css(); ?></style>

			<!-- ==================================================
			     Steps
			     ================================================== -->
			<div class="ezp-steps">
				<div class="ezp-step is-active">
					<div class="ezp-step-dot">
						<?php echo ezp_cart_icon( 'cart', '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="9" cy="20" r="1.5"/><circle cx="18" cy="20" r="1.5"/><path d="M3 3h2l2.5 12h11l2-8H6"/></svg>' ); ?>
					</div>
					<span class="ezp-step-text">سبد خرید</span>
				</div>
				<div class="ezp-step-line"></div>
				<div class="ezp-step">
					<div class="ezp-step-dot">
						<?php echo ezp_cart_icon( 'checkout', '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12l5 5L20 7"/></svg>' ); ?>
					</div>
					<span class="ezp-step-text">اطلاعات ارسال</span>
				</div>
				<div class="ezp-step-line"></div>
				<div class="ezp-step">
					<div class="ezp-step-dot">
						<?php echo ezp_cart_icon( 'credit-card', '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="5" width="20" height="14" rx="2"/><path d="M2 10h20"/></svg>' ); ?>
					</div>
					<span class="ezp-step-text">پرداخت</span>
				</div>
				<div class="ezp-step-line"></div>
				<div class="ezp-step">
					<div class="ezp-step-dot">
						<?php echo ezp_cart_icon( 'check-circle', '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><path d="M8 12l2.5 2.5L16 9"/></svg>' ); ?>
					</div>
					<span class="ezp-step-text">تأیید سفارش</span>
				</div>
			</div>

			<!-- ==================================================
			     Free Ship Bar
			     ================================================== -->
			<div class="ezp-ship-bar <?php echo $is_free_ship ? 'is-done' : ''; ?>">
				<div class="ezp-ship-bar-row">
					<div class="ezp-ship-bar-icon">
						<?php echo ezp_cart_icon( $is_free_ship ? 'check-circle' : 'delivery', ezp_cart_icon( $is_free_ship ? 'check-circle' : 'truck', '' ) ); ?>
					</div>
					<div class="ezp-ship-bar-text">
						<?php if ( $is_free_ship ) : ?>
							<strong>ارسال این سفارش رایگان است</strong>
							<span>هزینه‌ای برای ارسال پرداخت نمی‌کنید</span>
						<?php else : ?>
							<strong>
								تا ارسال رایگان
								<span class="ezp-ship-amount"><?php echo esc_html( ezp_cart_fa( number_format( $remaining ) ) ); ?> تومان</span>
								باقی مانده
							</strong>
							<span>با تکمیل سبد، ارسال سفارش رایگان می‌شود</span>
						<?php endif; ?>
					</div>
					<div class="ezp-ship-bar-percent">
						<?php echo esc_html( ezp_cart_fa( round( $progress ) ) ); ?>٪
					</div>
				</div>
				<div class="ezp-ship-bar-track">
					<div class="ezp-ship-bar-fill" style="width:<?php echo esc_attr( $progress ); ?>%;">
						<span class="ezp-ship-bar-shine"></span>
					</div>
				</div>
			</div>

			<!-- Header -->
			<div class="ezp-cart-head">
				<h1 class="ezp-cart-head-title">
					سبد خرید
					<span class="ezp-cart-head-badge"><?php echo esc_html( ezp_cart_fa( $cart_count ) ); ?> کالا</span>
				</h1>
				<a href="<?php echo esc_url( $shop_url ); ?>" class="ezp-cart-head-back">
					<?php echo ezp_cart_icon( 'arrow-right', '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>' ); ?>
					<span>ادامه خرید</span>
				</a>
			</div>

			<div class="ezp-cart-grid">

				<!-- ===== Items ===== -->
				<div class="ezp-cart-list">

					<?php foreach ( $cart_items as $cart_item_key => $cart_item ) :

						$_product = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );
						if ( ! $_product || ! $_product->exists() || $cart_item['quantity'] <= 0 ) continue;

						// ✅ نام محصول بدون فیلتر که HTML تزریق نکند
						$product_name = $_product->get_name();

						$product_id       = apply_filters( 'woocommerce_cart_item_product_id', $cart_item['product_id'], $cart_item, $cart_item_key );
						$thumbnail        = apply_filters( 'woocommerce_cart_item_thumbnail', $_product->get_image( 'woocommerce_thumbnail' ), $cart_item, $cart_item_key );
						$product_price    = apply_filters( 'woocommerce_cart_item_price', WC()->cart->get_product_price( $_product ), $cart_item, $cart_item_key );
						$product_subtotal = apply_filters( 'woocommerce_cart_item_subtotal', WC()->cart->get_product_subtotal( $_product, $cart_item['quantity'] ), $cart_item, $cart_item_key );
						$permalink        = $_product->get_permalink( $cart_item );

						$fields = ezp_cart_extract_fields( $cart_item );
					?>
						<div class="ezp-item" data-key="<?php echo esc_attr( $cart_item_key ); ?>">

							<a href="<?php echo esc_url( $permalink ); ?>" class="ezp-item-img">
								<?php echo wp_kses_post( $thumbnail ); ?>
							</a>

							<div class="ezp-item-body">

								<div class="ezp-item-top">
									<a href="<?php echo esc_url( $permalink ); ?>" class="ezp-item-title">
										<?php echo esc_html( $product_name ); ?>
									</a>
									<a href="<?php echo esc_url( $permalink ); ?>" class="ezp-item-edit">
										<?php echo ezp_cart_icon( 'edit', '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L8 18l-4 1 1-4Z"/></svg>' ); ?>
										<span>ویرایش</span>
									</a>
								</div>

								<?php if ( ! empty( $fields ) ) : ?>
								<div class="ezp-item-attrs">
									<button type="button" class="ezp-item-attrs-toggle" aria-expanded="false">
										<span class="ezp-item-attrs-label">
											<?php echo ezp_cart_icon( 'options', '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="3"/><path d="M12 1v6m0 10v6M4.22 4.22l4.24 4.24m7.08 7.08l4.24 4.24M1 12h6m10 0h6M4.22 19.78l4.24-4.24m7.08-7.08l4.24-4.24"/></svg>' ); ?>
											مشخصات سفارش
											<span class="ezp-item-attrs-count"><?php echo esc_html( ezp_cart_fa( count( $fields ) ) ); ?></span>
										</span>
										<svg class="ezp-item-attrs-caret" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg>
									</button>
									<div class="ezp-item-attrs-body" hidden>
										<div class="ezp-item-attrs-table">
											<?php foreach ( $fields as $f ) : ?>
												<div class="ezp-item-attr-row">
													<span class="ezp-item-attr-lbl"><?php echo esc_html( $f['label'] ); ?></span>
													<span class="ezp-item-attr-val"><?php echo esc_html( $f['value'] ); ?></span>
												</div>
											<?php endforeach; ?>
										</div>
									</div>
								</div>
								<?php endif; ?>

								<div class="ezp-item-unit">
									قیمت واحد: <strong><?php echo wp_kses_post( ezp_cart_fa( $product_price ) ); ?></strong>
								</div>

								<div class="ezp-item-actions">
									<div class="ezp-qty">
										<button type="button" class="ezp-qty-btn ezp-qty-minus" data-key="<?php echo esc_attr( $cart_item_key ); ?>" aria-label="کاهش">
											<svg viewBox="0 0 24 24" width="16" height="16"><line x1="5" y1="12" x2="19" y2="12" stroke="#0f172a" stroke-width="2.5" stroke-linecap="round"/></svg>
										</button>
										<input
											type="number"
											class="ezp-qty-input"
											data-key="<?php echo esc_attr( $cart_item_key ); ?>"
											value="<?php echo esc_attr( $cart_item['quantity'] ); ?>"
											min="1"
											max="<?php echo $_product->managing_stock() && $_product->get_stock_quantity() ? esc_attr( $_product->get_stock_quantity() ) : 999; ?>"
											step="1"
											inputmode="numeric"
										>
										<button type="button" class="ezp-qty-btn ezp-qty-plus" data-key="<?php echo esc_attr( $cart_item_key ); ?>" aria-label="افزایش">
											<svg viewBox="0 0 24 24" width="16" height="16"><line x1="12" y1="5" x2="12" y2="19" stroke="#0f172a" stroke-width="2.5" stroke-linecap="round"/><line x1="5" y1="12" x2="19" y2="12" stroke="#0f172a" stroke-width="2.5" stroke-linecap="round"/></svg>
										</button>
									</div>

									<button type="button" class="ezp-item-remove" data-key="<?php echo esc_attr( $cart_item_key ); ?>">
										<?php echo ezp_cart_icon( 'trash', '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 6h18M8 6V4h8v2M19 6l-1 15H6L5 6M10 11v6M14 11v6"/></svg>' ); ?>
										<span>حذف</span>
									</button>
								</div>
							</div>

							<div class="ezp-item-subtotal">
								<?php echo wp_kses_post( ezp_cart_fa( $product_subtotal ) ); ?>
							</div>
						</div>
					<?php endforeach; ?>

					<!-- Coupon -->
					<div class="ezp-coupon">
						<button type="button" class="ezp-coupon-toggle">
							<?php echo ezp_cart_icon( 'coupon', '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M20 12a2 2 0 0 1-2-2V4H6v6a2 2 0 0 1-2 2 2 2 0 0 0 0 4 2 2 0 0 1 2 2v6h12v-6a2 2 0 0 1 2-2 2 2 0 0 0 0-4z"/></svg>' ); ?>
							<span>کد تخفیف دارید؟</span>
							<svg class="ezp-coupon-caret" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg>
						</button>
						<div class="ezp-coupon-body" hidden>
							<form class="ezp-coupon-form">
								<input type="text" class="ezp-coupon-input" placeholder="کد تخفیف" autocomplete="off">
								<button type="submit" class="ezp-coupon-submit">اعمال</button>
							</form>
							<div class="ezp-coupon-msg"></div>
						</div>
					</div>
				</div>

				<!-- ===== Summary ===== -->
				<aside class="ezp-summary">
					<div class="ezp-summary-card">
						<h3 class="ezp-summary-card-title">
							<?php echo ezp_cart_icon( 'bill', '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M6 2h12v20l-3-2-3 2-3-2-3 2z"/><path d="M9 7h6M9 11h6M9 15h4"/></svg>' ); ?>
							خلاصه سفارش
						</h3>

						<div class="ezp-summary-rows">
							<div class="ezp-summary-row">
								<span>جمع کالاها (<?php echo esc_html( ezp_cart_fa( $cart_count ) ); ?>)</span>
								<span class="val"><?php echo wp_kses_post( ezp_cart_fa( wc_price( $subtotal ) ) ); ?></span>
							</div>

							<?php if ( WC()->cart->needs_shipping() ) :
								$shipping_total = WC()->cart->get_shipping_total();
								$shipping_label = $is_free_ship ? 'رایگان' : ( $shipping_total > 0 ? wc_price( $shipping_total ) : 'در حال محاسبه' );
							?>
								<div class="ezp-summary-row">
									<span>هزینه ارسال</span>
									<span class="val <?php echo $is_free_ship ? 'free' : ''; ?>"><?php echo wp_kses_post( ezp_cart_fa( $shipping_label ) ); ?></span>
								</div>
							<?php endif; ?>

							<?php $discount_total = WC()->cart->get_discount_total();
							if ( $discount_total > 0 ) : ?>
								<div class="ezp-summary-row discount">
									<span>تخفیف</span>
									<span class="val">− <?php echo wp_kses_post( ezp_cart_fa( wc_price( $discount_total ) ) ); ?></span>
								</div>
							<?php endif; ?>

							<?php $tax_total = WC()->cart->get_total_tax();
							if ( $tax_total > 0 ) : ?>
								<div class="ezp-summary-row">
									<span>مالیات</span>
									<span class="val"><?php echo wp_kses_post( ezp_cart_fa( wc_price( $tax_total ) ) ); ?></span>
								</div>
							<?php endif; ?>
						</div>

						<div class="ezp-summary-total">
							<span>مبلغ قابل پرداخت</span>
							<strong><?php echo wp_kses_post( ezp_cart_fa( WC()->cart->get_total() ) ); ?></strong>
						</div>

						<a href="<?php echo esc_url( $checkout_url ); ?>" class="ezp-checkout-btn">
							<?php echo ezp_cart_icon( 'checkout', '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12l5 5L20 7"/></svg>' ); ?>
							<span>ادامه فرآیند خرید</span>
							<svg class="ezp-checkout-arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg>
						</a>

						<div class="ezp-summary-trust">
							<div class="ezp-trust">
								<?php echo ezp_cart_icon( 'shield', '' ); ?>
								<span>پرداخت امن</span>
							</div>
							<div class="ezp-trust">
								<?php echo ezp_cart_icon( 'truck', '' ); ?>
								<span>ارسال سریع</span>
							</div>
							<div class="ezp-trust">
								<?php echo ezp_cart_icon( 'support', '' ); ?>
								<span>پشتیبانی ۲۴/۷</span>
							</div>
						</div>
					</div>
				</aside>
			</div>

			<?php if ( ! empty( $cross_sells ) ) : ?>
				<div class="ezp-cross">
					<h2 class="ezp-cross-title">
						<?php echo ezp_cart_icon( 'gift', '' ); ?>
						شاید این‌ها را هم دوست داشته باشید
					</h2>
					<div class="ezp-cross-grid">
						<?php foreach ( $cross_sells as $cs ) :
							$img = wp_get_attachment_image_url( $cs->get_image_id(), 'woocommerce_thumbnail' ) ?: wc_placeholder_img_src();
						?>
							<a class="ezp-cross-card" href="<?php echo esc_url( $cs->get_permalink() ); ?>">
								<div class="ezp-cross-img">
									<img src="<?php echo esc_url( $img ); ?>" alt="<?php echo esc_attr( $cs->get_name() ); ?>" loading="lazy">
								</div>
								<div class="ezp-cross-body">
									<div class="ezp-cross-name"><?php echo esc_html( $cs->get_name() ); ?></div>
									<div class="ezp-cross-price"><?php echo wp_kses_post( ezp_cart_fa( $cs->get_price_html() ) ); ?></div>
								</div>
							</a>
						<?php endforeach; ?>
					</div>
				</div>
			<?php endif; ?>
		</div>

		<div class="ezp-toast" id="ezp-toast">
			<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="9"/><path d="M8 12l2.5 2.5L16 9"/></svg>
			<span id="ezp-toast-text">عملیات انجام شد.</span>
		</div>

		<script>
		(function () {
			'use strict';

			const root = document.querySelector('.ezp-cart-page');
			if (!root) return;

			const ajaxUrl = '<?php echo esc_js( admin_url( 'admin-ajax.php' ) ); ?>';
			const nonce   = '<?php echo esc_js( wp_create_nonce( 'ezp_cart_action' ) ); ?>';

			const toast = document.getElementById('ezp-toast');
			const toastText = document.getElementById('ezp-toast-text');
			let toastTimer = null;

			const showToast = (msg, type) => {
				if (!toast) return;
				toastText.textContent = msg;
				toast.classList.add('show');
				toast.classList.toggle('is-error', type === 'error');
				clearTimeout(toastTimer);
				toastTimer = setTimeout(() => toast.classList.remove('show'), 3000);
			};

			root.querySelectorAll('.ezp-checkout-btn, .ezp-coupon-submit, .ezp-cart-head-back').forEach((btn) => {
				btn.addEventListener('click', (e) => {
					const rect = btn.getBoundingClientRect();
					const size = Math.max(rect.width, rect.height);
					const ripple = document.createElement('span');
					ripple.className = 'ezp-ripple';
					ripple.style.width = ripple.style.height = size + 'px';
					ripple.style.left = (e.clientX - rect.left - size / 2) + 'px';
					ripple.style.top = (e.clientY - rect.top - size / 2) + 'px';
					btn.appendChild(ripple);
					setTimeout(() => ripple.remove(), 600);
				});
			});

			// Attrs accordion
			root.querySelectorAll('.ezp-item-attrs-toggle').forEach((btn) => {
				btn.addEventListener('click', () => {
					const body = btn.nextElementSibling;
					const open = btn.getAttribute('aria-expanded') === 'true';
					btn.setAttribute('aria-expanded', !open);
					if (body) {
						if (open) {
							body.style.maxHeight = '0px';
							setTimeout(() => { body.hidden = true; }, 300);
						} else {
							body.hidden = false;
							body.style.maxHeight = body.scrollHeight + 'px';
						}
					}
				});
			});

			// Coupon accordion
			const couponToggle = root.querySelector('.ezp-coupon-toggle');
			if (couponToggle) {
				couponToggle.addEventListener('click', () => {
					const body = couponToggle.nextElementSibling;
					const open = couponToggle.classList.toggle('is-open');
					if (body) {
						if (open) {
							body.hidden = false;
							body.style.maxHeight = body.scrollHeight + 'px';
						} else {
							body.style.maxHeight = '0px';
							setTimeout(() => { body.hidden = true; }, 300);
						}
					}
				});
			}

			const refreshCart = () => {
				if (typeof jQuery !== 'undefined') {
					jQuery(document.body).trigger('wc_fragment_refresh');
				}
				setTimeout(() => window.location.reload(), 350);
			};

			const updateQty = (key, qty) => {
				const body = new URLSearchParams();
				body.append('action', 'ezp_cart_update_qty');
				body.append('security', nonce);
				body.append('cart_item_key', key);
				body.append('quantity', qty);
				return fetch(ajaxUrl, { method: 'POST', body, credentials: 'same-origin' }).then(r => r.json());
			};

			root.querySelectorAll('.ezp-qty-minus, .ezp-qty-plus').forEach((btn) => {
				btn.addEventListener('click', () => {
					const key = btn.getAttribute('data-key');
					const input = root.querySelector('.ezp-qty-input[data-key="' + key + '"]');
					if (!input) return;

					let val = parseInt(input.value || 1, 10);
					const isPlus = btn.classList.contains('ezp-qty-plus');
					const newVal = isPlus ? val + 1 : Math.max(1, val - 1);

					if (newVal === val) return;
					input.value = newVal;

					updateQty(key, newVal).then(res => {
						if (res && res.success) {
							refreshCart();
						} else {
							input.value = val;
							showToast((res && res.data && res.data.message) || 'خطا در بروزرسانی', 'error');
						}
					}).catch(() => {
						input.value = val;
						showToast('خطای ارتباط با سرور', 'error');
					});
				});
			});

			root.querySelectorAll('.ezp-qty-input').forEach((input) => {
				let timer = null;
				input.addEventListener('input', () => {
					clearTimeout(timer);
					const key = input.getAttribute('data-key');
					timer = setTimeout(() => {
						let val = parseInt(input.value || 0, 10);
						if (isNaN(val) || val < 1) val = 1;
						updateQty(key, val).then(res => {
							if (res && res.success) refreshCart();
							else showToast((res && res.data && res.data.message) || 'خطا', 'error');
						}).catch(() => showToast('خطای ارتباط', 'error'));
					}, 700);
				});
			});

			root.querySelectorAll('.ezp-item-remove').forEach((btn) => {
				btn.addEventListener('click', () => {
					const key = btn.getAttribute('data-key');
					const item = btn.closest('.ezp-item');
					if (!confirm('این کالا از سبد حذف شود؟')) return;

					btn.disabled = true;
					if (item) item.classList.add('is-removing');

					const body = new URLSearchParams();
					body.append('action', 'ezp_cart_remove_item');
					body.append('security', nonce);
					body.append('cart_item_key', key);

					fetch(ajaxUrl, { method: 'POST', body, credentials: 'same-origin' })
						.then(r => r.json())
						.then(res => {
							if (res && res.success) {
								showToast('کالا از سبد حذف شد', 'success');
								setTimeout(refreshCart, 250);
							} else {
								btn.disabled = false;
								if (item) item.classList.remove('is-removing');
								showToast((res && res.data && res.data.message) || 'خطا', 'error');
							}
						}).catch(() => {
							btn.disabled = false;
							if (item) item.classList.remove('is-removing');
							showToast('خطای ارتباط با سرور', 'error');
						});
				});
			});

			const couponForm = root.querySelector('.ezp-coupon-form');
			if (couponForm) {
				couponForm.addEventListener('submit', (e) => {
					e.preventDefault();
					const input = couponForm.querySelector('.ezp-coupon-input');
					const msg = root.querySelector('.ezp-coupon-msg');
					const code = (input.value || '').trim();

					if (!code) {
						msg.textContent = 'کد تخفیف را وارد کنید.';
						msg.className = 'ezp-coupon-msg error';
						return;
					}

					const btn = couponForm.querySelector('.ezp-coupon-submit');
					btn.disabled = true;
					btn.textContent = 'در حال بررسی...';

					const body = new URLSearchParams();
					body.append('action', 'ezp_cart_apply_coupon');
					body.append('security', nonce);
					body.append('coupon_code', code);

					fetch(ajaxUrl, { method: 'POST', body, credentials: 'same-origin' })
						.then(r => r.json())
						.then(res => {
							btn.disabled = false;
							btn.textContent = 'اعمال';
							if (res && res.success) {
								msg.textContent = (res.data && res.data.message) || 'کد تخفیف اعمال شد';
								msg.className = 'ezp-coupon-msg success';
								showToast((res.data && res.data.message) || 'کد تخفیف اعمال شد', 'success');
								setTimeout(refreshCart, 800);
							} else {
								msg.textContent = (res && res.data && res.data.message) || 'کد نامعتبر است.';
								msg.className = 'ezp-coupon-msg error';
							}
						}).catch(() => {
							btn.disabled = false;
							btn.textContent = 'اعمال';
							msg.textContent = 'خطای ارتباط با سرور.';
							msg.className = 'ezp-coupon-msg error';
						});
				});
			}

		})();
		</script>
		<?php
		return ob_get_clean();
	}
}

// ============================================================
// 7. CSS
// ============================================================
if ( ! function_exists( 'ezp_cart_css' ) ) {
	function ezp_cart_css() {
		$font_url = EZLAUTH_PLUGIN_URL . 'assets/fonts/vazirmatn/';
		return <<<CSS
		@font-face {
			font-family: 'EzLensVazir';
			src: url('{$font_url}Vazirmatn-Regular.woff2') format('woff2');
			font-weight: 400; font-display: swap;
		}
		@font-face {
			font-family: 'EzLensVazir';
			src: url('{$font_url}Vazirmatn-Medium.woff2') format('woff2');
			font-weight: 500; font-display: swap;
		}
		@font-face {
			font-family: 'EzLensVazir';
			src: url('{$font_url}Vazirmatn-Bold.woff2') format('woff2');
			font-weight: 700; font-display: swap;
		}

		/* مخفی کردن sticky وودمارت */
		.wd-sticky-btn,
		.wd-sticky-btn-container,
		.wd-sticky-btn-shown,
		.ezlens-cart-opts { 
			display: none !important; 
		}

		.ezp-cart-page {
			--primary: #071b7a;
			--primary-dark: #041252;
			--accent: #e11d48;
			--text: #0f172a;
			--muted: #64748b;
			--border: #e5e9f0;
			--soft: #f7f9fc;
			--success: #059669;
			--warning: #f59e0b;
			--danger: #dc2626;

			width: 100%; max-width: 1280px; margin: 0 auto;
			padding: 20px 16px 40px;
			font-family: 'EzLensVazir', Tahoma, Arial, sans-serif;
			color: var(--text); line-height: 1.8; font-size: 14px; direction: rtl;
		}
		.ezp-cart-page * { box-sizing: border-box; }
		.ezp-cart-page button { font-family: inherit; }

		/* ==================================================
		   Steps
		   ================================================== */
		.ezp-steps {
			display: flex; align-items: center; justify-content: center;
			gap: 0; margin-bottom: 20px; padding: 18px 16px;
			background: #fff;
			border: 1px solid var(--border);
			border-radius: 16px;
			box-shadow: 0 1px 3px rgba(15,23,42,.03);
		}
		.ezp-step {
			display: flex; align-items: center; gap: 8px;
			flex: 0 0 auto;
		}
		.ezp-step-dot {
			width: 32px; height: 32px;
			display: flex; align-items: center; justify-content: center;
			border-radius: 50%;
			background: var(--soft);
			color: #94a3b8;
			border: 2px solid var(--border);
			transition: all .3s ease;
		}
		.ezp-step-dot svg {
			width: 16px; height: 16px;
			fill: none; stroke: currentColor; stroke-width: 2;
			stroke-linecap: round; stroke-linejoin: round;
		}
		.ezp-step-text {
			font-size: 12px; font-weight: 600;
			color: #94a3b8;
			transition: color .3s;
		}
		.ezp-step.is-active .ezp-step-dot {
			background: var(--primary);
			color: #fff;
			border-color: var(--primary);
			box-shadow: 0 0 0 4px rgba(7,27,122,.12);
		}
		.ezp-step.is-active .ezp-step-text {
			color: var(--primary);
			font-weight: 700;
		}
		.ezp-step-line {
			height: 2px; flex: 1;
			min-width: 24px; max-width: 80px;
			background: var(--border);
			margin: 0 12px;
			border-radius: 2px;
		}

		/* ==================================================
		   Ship Bar
		   ================================================== */
		.ezp-ship-bar {
			margin-bottom: 20px;
			padding: 16px 18px;
			background: linear-gradient(135deg, #fff9e6 0%, #fef3c7 100%);
			border: 1px solid #fde68a;
			border-radius: 16px;
			transition: background .4s ease, border-color .4s ease;
		}
		.ezp-ship-bar.is-done {
			background: linear-gradient(135deg, #ecfdf5 0%, #d1fae5 100%);
			border-color: #86efac;
		}
		.ezp-ship-bar-row {
			display: flex; align-items: center; gap: 12px;
			margin-bottom: 12px;
		}
		.ezp-ship-bar-icon {
			width: 44px; height: 44px; flex: 0 0 44px;
			display: flex; align-items: center; justify-content: center;
			background: #fff; border-radius: 12px;
			color: #d97706;
			box-shadow: 0 2px 8px rgba(0,0,0,.06);
			transition: .3s ease;
		}
		.ezp-ship-bar.is-done .ezp-ship-bar-icon { color: var(--success); animation: ezpPop .5s ease; }
		@keyframes ezpPop {
			0% { transform: scale(.7); }
			60% { transform: scale(1.1); }
			100% { transform: scale(1); }
		}
		.ezp-ship-bar-icon svg,
		.ezp-ship-bar-icon img {
			width: 24px; height: 24px;
			fill: none; stroke: currentColor; stroke-width: 2;
			stroke-linecap: round; stroke-linejoin: round;
			display: block;
		}
		.ezp-ship-bar-text { flex: 1; min-width: 0; }
		.ezp-ship-bar-text strong {
			display: block; color: #78350f;
			font-size: 13px; font-weight: 700;
			margin-bottom: 2px;
			transition: color .3s;
		}
		.ezp-ship-bar.is-done .ezp-ship-bar-text strong { color: #065f46; }
		.ezp-ship-bar-text span {
			display: block; color: #a16207; font-size: 11px;
		}
		.ezp-ship-bar.is-done .ezp-ship-bar-text span { color: #047857; }
		.ezp-ship-amount {
			display: inline-block;
			padding: 1px 8px;
			background: #fff;
			border-radius: 6px;
			font-weight: 800;
			color: #b45309;
			margin: 0 4px;
			font-size: 12px;
		}
		.ezp-ship-bar.is-done .ezp-ship-amount { color: var(--success); }
		.ezp-ship-bar-percent {
			flex: 0 0 auto;
			min-width: 48px; text-align: center;
			padding: 5px 10px;
			background: #fff;
			border-radius: 8px;
			font-size: 14px; font-weight: 800;
			color: #b45309;
			transition: .3s;
		}
		.ezp-ship-bar.is-done .ezp-ship-bar-percent { color: var(--success); }
		.ezp-ship-bar-track {
			height: 8px; background: #fff;
			border-radius: 20px; overflow: hidden;
			box-shadow: inset 0 1px 2px rgba(0,0,0,.05);
		}
		.ezp-ship-bar-fill {
			height: 100%;
			background: linear-gradient(90deg, #f59e0b 0%, #fbbf24 100%);
			border-radius: inherit;
			transition: width .9s cubic-bezier(.4,0,.2,1), background .4s;
			position: relative; overflow: hidden;
		}
		.ezp-ship-bar.is-done .ezp-ship-bar-fill {
			background: linear-gradient(90deg, #059669 0%, #10b981 100%);
		}
		.ezp-ship-bar-shine {
			position: absolute; top: 0; left: 0; right: 0; bottom: 0;
			background: linear-gradient(90deg, transparent, rgba(255,255,255,.55), transparent);
			animation: ezpShine 2.2s infinite;
		}
		@keyframes ezpShine {
			0% { transform: translateX(-100%); }
			100% { transform: translateX(100%); }
		}

		/* ==================================================
		   Head
		   ================================================== */
		.ezp-cart-head {
			display: flex; align-items: center; justify-content: space-between;
			flex-wrap: wrap; gap: 12px; margin-bottom: 16px;
		}
		.ezp-cart-head-title {
			display: flex; align-items: center; gap: 10px;
			margin: 0; font-size: 20px; font-weight: 700;
		}
		.ezp-cart-head-badge {
			display: inline-block; padding: 3px 10px;
			background: var(--soft); color: var(--muted);
			font-size: 12px; font-weight: 600; border-radius: 20px;
		}
		.ezp-cart-head-back {
			display: inline-flex; align-items: center; gap: 6px;
			padding: 9px 16px;
			border-radius: 10px;
			background: #fff;
			border: 1px solid var(--border);
			color: var(--text) !important;
			font-size: 13px; font-weight: 600;
			text-decoration: none;
			transition: all .2s;
			position: relative; overflow: hidden;
			cursor: pointer;
		}
		.ezp-cart-head-back:hover {
			background: var(--soft);
			border-color: #cbd5e1;
			transform: translateX(-2px);
		}
		.ezp-cart-head-back svg {
			width: 15px; height: 15px;
			fill: none; stroke: currentColor; stroke-width: 2.2;
			stroke-linecap: round; stroke-linejoin: round;
		}

		/* ==================================================
		   Grid
		   ================================================== */
		.ezp-cart-grid {
			display: grid;
			grid-template-columns: minmax(0, 1fr) 350px;
			gap: 20px;
			align-items: start;
		}
		@media (max-width: 900px) {
			.ezp-cart-grid { grid-template-columns: 1fr; }
		}

		/* ==================================================
		   Item
		   ================================================== */
		.ezp-cart-list {
			background: #fff;
			border: 1px solid var(--border);
			border-radius: 16px;
			padding: 6px;
			box-shadow: 0 1px 3px rgba(15,23,42,.03);
		}

		.ezp-item {
			display: grid;
			grid-template-columns: 96px 1fr auto;
			gap: 14px;
			padding: 16px 12px;
			border-bottom: 1px solid var(--border);
			align-items: flex-start;
			transition: opacity .3s, height .3s, background .2s;
			overflow: hidden;
			animation: ezpItemIn .35s ease both;
		}
		@keyframes ezpItemIn {
			from { opacity: 0; transform: translateY(6px); }
			to { opacity: 1; transform: translateY(0); }
		}
		.ezp-item:last-child { border-bottom: 0; }
		.ezp-item:hover { background: #fafbfc; border-radius: 12px; }
		.ezp-item.is-removing { opacity: .35; pointer-events: none; }

		.ezp-item-img {
			display: block;
			width: 96px; height: 96px;
			background: var(--soft);
			border-radius: 12px;
			overflow: hidden;
			transition: transform .3s ease;
			flex: 0 0 96px;
		}
		.ezp-item-img:hover { transform: scale(1.03); }
		.ezp-item-img img {
			width: 100%; height: 100%; object-fit: contain;
		}

		.ezp-item-body { min-width: 0; }

		.ezp-item-top {
			display: flex; align-items: flex-start;
			justify-content: space-between;
			gap: 10px; margin-bottom: 10px;
		}
		.ezp-item-title {
			font-size: 14px; font-weight: 700;
			color: var(--text) !important; line-height: 1.6;
			text-decoration: none;
			transition: color .2s;
			flex: 1; min-width: 0;
		}
		.ezp-item-title:hover { color: var(--primary) !important; }
		.ezp-item-edit {
			display: inline-flex; align-items: center; gap: 5px;
			padding: 4px 10px;
			background: var(--soft);
			border: 1px solid var(--border);
			border-radius: 8px;
			color: var(--muted) !important;
			font-size: 11px; font-weight: 600;
			text-decoration: none; flex-shrink: 0;
			transition: .2s;
		}
		.ezp-item-edit:hover {
			background: #eef2ff;
			border-color: #c7d2fe;
			color: var(--primary) !important;
		}
		.ezp-item-edit svg {
			width: 12px; height: 12px;
			fill: none; stroke: currentColor;
			stroke-width: 1.8; stroke-linecap: round; stroke-linejoin: round;
		}

		/* ==================================================
		   Attrs Accordion
		   ================================================== */
		.ezp-item-attrs {
			margin-bottom: 10px;
			border: 1px solid #e0e7ff;
			background: #f8faff;
			border-radius: 10px;
			overflow: hidden;
		}
		.ezp-item-attrs-toggle {
			width: 100%;
			display: flex; align-items: center; justify-content: space-between;
			gap: 8px;
			padding: 8px 12px;
			background: transparent;
			border: 0;
			cursor: pointer;
			font-family: inherit;
			font-size: 12px; font-weight: 700;
			color: #4338ca !important;
			transition: background .2s;
		}
		.ezp-item-attrs-toggle:hover { background: #eef2ff; }
		.ezp-item-attrs-label {
			display: flex; align-items: center; gap: 6px;
		}
		.ezp-item-attrs-label svg {
			width: 14px; height: 14px;
			fill: none; stroke: currentColor;
			stroke-width: 1.8; stroke-linecap: round; stroke-linejoin: round;
		}
		.ezp-item-attrs-count {
			display: inline-flex; align-items: center; justify-content: center;
			min-width: 18px; height: 18px;
			padding: 0 5px;
			background: #c7d2fe;
			color: #312e81;
			font-size: 10px; font-weight: 800;
			border-radius: 10px;
			margin-right: 4px;
		}
		.ezp-item-attrs-caret {
			width: 14px; height: 14px;
			fill: none; stroke: currentColor;
			stroke-width: 2; stroke-linecap: round; stroke-linejoin: round;
			transition: transform .3s;
		}
		.ezp-item-attrs-toggle[aria-expanded="true"] .ezp-item-attrs-caret { transform: rotate(180deg); }
		.ezp-item-attrs-body {
			max-height: 0; overflow: hidden;
			transition: max-height .35s ease;
		}
		.ezp-item-attrs-table {
			padding: 4px 12px 10px;
			display: grid;
			grid-template-columns: 1fr 1fr;
			gap: 6px;
		}
		@media (max-width: 600px) {
			.ezp-item-attrs-table { grid-template-columns: 1fr; }
		}
		.ezp-item-attr-row {
			display: flex; align-items: center; justify-content: space-between;
			gap: 8px;
			padding: 7px 11px;
			background: #fff;
			border-radius: 8px;
			font-size: 11px;
			border: 1px solid #e0e7ff;
		}
		.ezp-item-attr-lbl {
			color: #6366f1;
			font-weight: 600;
		}
		.ezp-item-attr-val {
			color: #1e1b4b;
			font-weight: 800;
			direction: ltr;
			font-variant-numeric: tabular-nums;
		}

		.ezp-item-unit {
			display: none;
			font-size: 12px; color: var(--muted);
			margin-bottom: 8px;
		}
		.ezp-item-unit strong {
			color: var(--text); font-weight: 700;
		}

		/* ==================================================
		   Actions
		   ================================================== */
		.ezp-item-actions {
			display: flex; align-items: center; gap: 10px;
			flex-wrap: wrap;
		}

		/* ============ QTY ============ */
		.ezp-qty {
			display: inline-flex !important; align-items: center !important;
			height: 40px !important;
			border: 2px solid var(--border) !important;
			border-radius: 10px !important;
			overflow: hidden !important;
			background: #fff !important;
			transition: border-color .2s, box-shadow .2s;
		}
		.ezp-qty:focus-within {
			border-color: var(--primary) !important;
			box-shadow: 0 0 0 3px rgba(7,27,122,.10) !important;
		}
		.ezp-qty-btn {
			width: 36px !important; height: 100% !important;
			border: 0 !important; 
			background: #f1f5f9 !important;
			cursor: pointer !important;
			display: flex !important; 
			align-items: center !important; 
			justify-content: center !important;
			color: #0f172a !important;
			transition: background .2s, color .2s;
			padding: 0 !important;
			margin: 0 !important;
			flex-shrink: 0;
		}
		.ezp-qty-btn:hover { 
			background: var(--primary) !important; 
			color: #fff !important; 
		}
		.ezp-qty-btn:hover svg line { stroke: #fff !important; }
		.ezp-qty-btn:active { transform: scale(.92); }
		.ezp-qty-btn svg {
			width: 16px !important; 
			height: 16px !important;
			fill: none !important; 
			display: block !important;
			pointer-events: none;
		}
		.ezp-qty-btn svg line {
			stroke: currentColor !important;
			stroke-width: 2.5 !important;
			stroke-linecap: round !important;
		}
		.ezp-qty-input {
			width: 52px !important; 
			height: 100% !important;
			border: 0 !important; 
			outline: 0 !important; 
			padding: 0 !important;
			margin: 0 !important;
			text-align: center !important;
			font-size: 15px !important; 
			font-weight: 700 !important;
			font-family: inherit !important;
			color: var(--text) !important;
			background: #fff !important;
			-moz-appearance: textfield !important;
			appearance: textfield !important;
		}
		.ezp-qty-input:focus {
			background: #eff6ff !important;
		}
		.ezp-qty-input::-webkit-outer-spin-button,
		.ezp-qty-input::-webkit-inner-spin-button {
			-webkit-appearance: none !important; 
			margin: 0 !important;
		}
		.ezp-qty-input:disabled {
			opacity: 1 !important;
			color: var(--text) !important;
		}

		/* ============ Remove ============ */
		.ezp-item-remove {
			display: inline-flex !important; 
			align-items: center !important; 
			gap: 5px;
			padding: 8px 12px !important;
			background: transparent !important;
			border: 1px solid transparent !important;
			border-radius: 8px !important;
			cursor: pointer !important;
			font-size: 12px !important; 
			font-weight: 600 !important;
			color: var(--muted) !important;
			transition: .2s;
		}
		.ezp-item-remove:hover {
			color: var(--danger) !important;
			background: #fef2f2 !important;
			border-color: #fecaca !important;
		}
		.ezp-item-remove:disabled { opacity: .5; cursor: wait; }
		.ezp-item-remove svg {
			width: 14px !important; 
			height: 14px !important;
			fill: none !important; 
			stroke: currentColor !important;
			stroke-width: 1.8 !important; 
			stroke-linecap: round !important; 
			stroke-linejoin: round !important;
		}

		.ezp-item-subtotal {
			font-size: 15px; font-weight: 800;
			color: var(--primary);
			text-align: left;
			white-space: nowrap;
			padding-top: 6px;
		}

		/* ==================================================
		   Coupon
		   ================================================== */
		.ezp-coupon {
			margin-top: 10px;
			padding: 12px 14px;
			background: var(--soft);
			border-radius: 12px;
			transition: background .2s;
		}
		.ezp-coupon-toggle {
			display: flex; align-items: center; gap: 8px;
			cursor: pointer; user-select: none;
			font-size: 13px; font-weight: 600;
			color: var(--primary) !important;
			background: transparent;
			border: 0;
			padding: 0;
			width: 100%;
			font-family: inherit;
		}
		.ezp-coupon-toggle svg {
			width: 18px; height: 18px;
			fill: none; stroke: currentColor;
			stroke-width: 1.8; stroke-linecap: round; stroke-linejoin: round;
			flex-shrink: 0;
		}
		.ezp-coupon-caret {
			margin-right: auto;
			width: 14px !important; height: 14px !important;
			transition: transform .3s;
		}
		.ezp-coupon-toggle.is-open .ezp-coupon-caret { transform: rotate(180deg); }
		.ezp-coupon-body {
			max-height: 0; overflow: hidden;
			transition: max-height .3s ease;
		}
		.ezp-coupon-form { display: flex; gap: 8px; padding-top: 12px; }
		.ezp-coupon-input {
			flex: 1; min-height: 42px;
			padding: 8px 12px;
			border: 1px solid var(--border);
			border-radius: 10px;
			background: #fff; font-family: inherit;
			font-size: 13px; outline: none;
			transition: border-color .2s;
		}
		.ezp-coupon-input:focus { border-color: var(--primary); }
		.ezp-coupon-submit {
			min-height: 42px; padding: 0 20px;
			border: 0; border-radius: 10px;
			background: var(--primary); color: #fff;
			font-size: 13px; font-weight: 700;
			cursor: pointer; transition: .2s;
			position: relative; overflow: hidden;
		}
		.ezp-coupon-submit:hover { background: var(--primary-dark); }
		.ezp-coupon-submit:disabled { opacity: .7; cursor: wait; }
		.ezp-coupon-msg {
			margin-top: 8px; font-size: 12px; font-weight: 600;
			min-height: 16px;
		}
		.ezp-coupon-msg.error { color: var(--danger); }
		.ezp-coupon-msg.success { color: var(--success); }

		/* ==================================================
		   Summary
		   ================================================== */
		.ezp-summary { position: sticky; top: 20px; }
		@media (max-width: 900px) { .ezp-summary { position: static; } }

		.ezp-summary-card {
			padding: 20px;
			background: #fff;
			border: 1px solid var(--border);
			border-radius: 16px;
			box-shadow: 0 1px 3px rgba(15,23,42,.03);
		}
		.ezp-summary-card-title {
			display: flex; align-items: center; gap: 8px;
			margin: 0 0 16px;
			font-size: 16px; font-weight: 700;
			padding-bottom: 14px;
			border-bottom: 1px dashed var(--border);
		}
		.ezp-summary-card-title svg {
			width: 20px; height: 20px;
			fill: none; stroke: var(--primary);
			stroke-width: 1.8; stroke-linecap: round; stroke-linejoin: round;
		}

		.ezp-summary-rows {
			display: flex; flex-direction: column; gap: 10px;
			margin-bottom: 16px;
		}
		.ezp-summary-row {
			display: flex; align-items: center; justify-content: space-between;
			font-size: 13px; color: var(--muted);
		}
		.ezp-summary-row .val {
			font-weight: 700; color: var(--text);
		}
		.ezp-summary-row .val.free { color: var(--success); }
		.ezp-summary-row.discount .val { color: var(--accent); }

		.ezp-summary-total {
			display: flex; align-items: center; justify-content: space-between;
			padding: 14px 0;
			border-top: 2px solid var(--border);
			border-bottom: 2px solid var(--border);
			margin-bottom: 16px;
		}
		.ezp-summary-total span {
			font-size: 13px; font-weight: 600; color: var(--muted);
		}
		.ezp-summary-total strong {
			font-size: 20px; font-weight: 800;
			color: var(--primary);
		}

		.ezp-checkout-btn {
			display: flex !important; align-items: center !important; justify-content: center !important;
			gap: 8px; width: 100%;
			min-height: 52px; padding: 0 24px;
			border-radius: 12px; border: 0;
			background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%) !important;
			color: #fff !important;
			font-size: 15px; font-weight: 700;
			cursor: pointer; text-decoration: none;
			position: relative; overflow: hidden;
			box-shadow: 0 6px 20px rgba(7,27,122,.25);
			transition: transform .15s, box-shadow .25s;
		}
		.ezp-checkout-btn:hover {
			transform: translateY(-2px);
			box-shadow: 0 10px 30px rgba(7,27,122,.4);
			color: #fff !important;
		}
		.ezp-checkout-btn:active { transform: scale(.98); }
		.ezp-checkout-btn svg {
			width: 20px !important; height: 20px !important;
			fill: none !important; 
			stroke: #fff !important; 
			stroke-width: 2 !important;
			stroke-linecap: round !important; 
			stroke-linejoin: round !important;
			flex-shrink: 0;
		}
		.ezp-checkout-arrow {
			width: 16px !important; height: 16px !important;
			transition: transform .3s;
		}
		.ezp-checkout-btn:hover .ezp-checkout-arrow { transform: translateX(-4px); }

		.ezp-ripple {
			position: absolute; border-radius: 50%;
			background: rgba(255,255,255,.5);
			transform: scale(0);
			animation: ezpRipple .6s linear;
			pointer-events: none;
		}
		@keyframes ezpRipple {
			to { transform: scale(4); opacity: 0; }
		}

		.ezp-summary-trust {
			display: grid; grid-template-columns: repeat(3, 1fr);
			gap: 6px; margin-top: 14px;
			padding-top: 14px;
			border-top: 1px dashed var(--border);
		}
		.ezp-trust {
			display: flex; flex-direction: column;
			align-items: center; gap: 5px;
			text-align: center;
			font-size: 10px; font-weight: 600;
			color: var(--muted);
		}
		.ezp-trust svg {
			width: 20px !important; height: 20px !important;
			fill: none !important; 
			stroke: var(--primary) !important;
			stroke-width: 1.7 !important; 
			stroke-linecap: round !important; 
			stroke-linejoin: round !important;
		}

		/* ==================================================
		   Cross-sell
		   ================================================== */
		.ezp-cross { margin-top: 30px; }
		.ezp-cross-title {
			display: flex; align-items: center; gap: 8px;
			margin: 0 0 14px;
			font-size: 17px; font-weight: 700;
		}
		.ezp-cross-title svg {
			width: 20px; height: 20px;
			fill: none; stroke: var(--primary);
			stroke-width: 1.8; stroke-linecap: round; stroke-linejoin: round;
		}
		.ezp-cross-grid {
			display: grid;
			grid-template-columns: repeat(4, minmax(0, 1fr));
			gap: 12px;
		}
		@media (max-width: 900px) { .ezp-cross-grid { grid-template-columns: repeat(2, 1fr); } }
		@media (max-width: 480px) { .ezp-cross-grid { grid-template-columns: 1fr; } }

		.ezp-cross-card {
			display: block;
			background: #fff;
			border: 1px solid var(--border);
			border-radius: 12px;
			overflow: hidden;
			text-decoration: none;
			color: inherit;
			transition: .25s;
		}
		.ezp-cross-card:hover {
			transform: translateY(-3px);
			box-shadow: 0 8px 24px rgba(15,23,42,.08);
		}
		.ezp-cross-img {
			aspect-ratio: 1;
			background: var(--soft);
			overflow: hidden;
		}
		.ezp-cross-img img {
			width: 100%; height: 100%;
			object-fit: contain;
			transition: transform .3s;
		}
		.ezp-cross-card:hover .ezp-cross-img img { transform: scale(1.05); }
		.ezp-cross-body { padding: 12px; }
		.ezp-cross-name {
			font-size: 13px; font-weight: 700;
			line-height: 1.6; margin-bottom: 4px;
			min-height: 42px;
			display: -webkit-box;
			-webkit-line-clamp: 2;
			-webkit-box-orient: vertical;
			overflow: hidden;
		}
		.ezp-cross-price {
			font-size: 13px; font-weight: 700;
			color: var(--primary);
		}

		/* ==================================================
		   Empty
		   ================================================== */
		.ezp-cart-empty {
			text-align: center;
			padding: 60px 20px;
			background: #fff;
			border: 1px solid var(--border);
			border-radius: 20px;
		}
		.ezp-cart-empty-icon {
			width: 90px; height: 90px;
			margin: 0 auto 18px;
			display: flex; align-items: center; justify-content: center;
			background: var(--soft);
			border-radius: 50%;
			color: var(--muted);
		}
		.ezp-cart-empty-icon svg { width: 44px; height: 44px; }
		.ezp-cart-empty h2 {
			margin: 0 0 8px;
			font-size: 20px; font-weight: 700;
		}
		.ezp-cart-empty p {
			margin: 0 0 24px;
			color: var(--muted); font-size: 14px;
		}
		.ezp-cart-btn-primary {
			display: inline-flex; align-items: center; gap: 8px;
			min-height: 50px; padding: 0 24px;
			border-radius: 12px;
			background: linear-gradient(135deg, var(--primary), var(--primary-dark));
			color: #fff !important;
			font-size: 14px; font-weight: 700;
			text-decoration: none;
			box-shadow: 0 6px 20px rgba(7,27,122,.25);
			transition: .2s;
			position: relative; overflow: hidden;
		}
		.ezp-cart-btn-primary:hover {
			transform: translateY(-2px);
			box-shadow: 0 10px 30px rgba(7,27,122,.4);
		}
		.ezp-cart-btn-primary svg {
			width: 18px !important; height: 18px !important;
			fill: none !important; 
			stroke: #fff !important; 
			stroke-width: 2 !important;
			stroke-linecap: round !important; 
			stroke-linejoin: round !important;
		}

		/* Toast */
		.ezp-toast {
			position: fixed;
			bottom: 24px; left: 50%;
			transform: translateX(-50%) translateY(120px);
			z-index: 999999;
			display: flex; align-items: center; gap: 10px;
			padding: 14px 22px;
			background: linear-gradient(135deg, #059669, #047857);
			color: #fff; border-radius: 14px;
			box-shadow: 0 15px 45px rgba(5,150,105,.5);
			font-size: 13px; font-weight: 700;
			opacity: 0;
			transition: all .4s cubic-bezier(.4,0,.2,1);
			max-width: calc(100% - 32px);
			font-family: 'EzLensVazir', Tahoma, sans-serif;
		}
		.ezp-toast.show { opacity: 1; transform: translateX(-50%) translateY(0); }
		.ezp-toast.is-error {
			background: linear-gradient(135deg, #dc2626, #b91c1c);
			box-shadow: 0 15px 45px rgba(220,38,38,.5);
		}
		.ezp-toast svg {
			width: 22px !important; height: 22px !important;
			fill: none !important; 
			stroke: #fff !important; 
			stroke-width: 2.5 !important;
			stroke-linecap: round !important; 
			stroke-linejoin: round !important;
			flex: 0 0 22px;
		}

		/* Responsive */
		@media (max-width: 768px) {
			.ezp-steps { padding: 14px 10px; }
			.ezp-step { flex-direction: column; gap: 4px; }
			.ezp-step-text { font-size: 10px; }
			.ezp-step-dot { width: 28px; height: 28px; }
			.ezp-step-dot svg { width: 14px; height: 14px; }
			.ezp-step-line { min-width: 16px; margin: 0 6px; }
		}
		@media (max-width: 600px) {
			.ezp-cart-page { padding: 14px 10px 30px; }
			.ezp-cart-head-title { font-size: 17px; }
			.ezp-ship-bar { padding: 14px; }
			.ezp-ship-bar-icon { width: 38px; height: 38px; flex: 0 0 38px; }
			.ezp-ship-bar-icon svg { width: 20px; height: 20px; }
			.ezp-ship-bar-text strong { font-size: 12px; }
			.ezp-ship-bar-percent { font-size: 13px; padding: 4px 8px; min-width: 42px; }
			.ezp-item {
				grid-template-columns: 76px 1fr;
				gap: 12px;
				padding: 14px 8px;
			}
			.ezp-item-img { width: 76px; height: 76px; }
			.ezp-item-subtotal {
				grid-column: 1 / -1;
				text-align: center;
				padding: 10px 8px 4px;
				border-top: 1px dashed var(--border);
				margin-top: 8px;
				font-size: 14px;
			}
			.ezp-item-unit { display: block; }
			.ezp-item-edit span { display: none; }
			.ezp-item-edit { padding: 5px 8px; }
		}
		@media (max-width: 400px) {
			.ezp-item-img { width: 68px; height: 68px; }
			.ezp-item { grid-template-columns: 68px 1fr; }
			.ezp-item-attr-row { font-size: 10px; padding: 5px 8px; }
		}
CSS;
	}
}


// ============================================================
// 8. AJAX
// ============================================================
if ( ! function_exists( 'ezp_cart_update_qty_handler' ) ) {
	add_action( 'wp_ajax_ezp_cart_update_qty', 'ezp_cart_update_qty_handler' );
	add_action( 'wp_ajax_nopriv_ezp_cart_update_qty', 'ezp_cart_update_qty_handler' );
	function ezp_cart_update_qty_handler() {
		if ( ! isset( $_POST['security'] ) || ! wp_verify_nonce( $_POST['security'], 'ezp_cart_action' ) ) {
			wp_send_json_error( array( 'message' => 'درخواست نامعتبر.' ) );
		}
		$cart_item_key = sanitize_text_field( wp_unslash( $_POST['cart_item_key'] ?? '' ) );
		$quantity      = max( 0, absint( $_POST['quantity'] ?? 0 ) );
		if ( ! $cart_item_key || ! WC()->cart ) {
			wp_send_json_error( array( 'message' => 'اطلاعات نامعتبر.' ) );
		}
		if ( $quantity === 0 ) {
			WC()->cart->remove_cart_item( $cart_item_key );
		} else {
			WC()->cart->set_quantity( $cart_item_key, $quantity, true );
		}
		WC()->cart->calculate_totals();
		wp_send_json_success( array( 'message' => 'بروزرسانی شد.' ) );
	}
}

if ( ! function_exists( 'ezp_cart_remove_item_handler' ) ) {
	add_action( 'wp_ajax_ezp_cart_remove_item', 'ezp_cart_remove_item_handler' );
	add_action( 'wp_ajax_nopriv_ezp_cart_remove_item', 'ezp_cart_remove_item_handler' );
	function ezp_cart_remove_item_handler() {
		if ( ! isset( $_POST['security'] ) || ! wp_verify_nonce( $_POST['security'], 'ezp_cart_action' ) ) {
			wp_send_json_error( array( 'message' => 'درخواست نامعتبر.' ) );
		}
		$cart_item_key = sanitize_text_field( wp_unslash( $_POST['cart_item_key'] ?? '' ) );
		if ( ! $cart_item_key || ! WC()->cart ) {
			wp_send_json_error( array( 'message' => 'اطلاعات نامعتبر.' ) );
		}
		WC()->cart->remove_cart_item( $cart_item_key );
		WC()->cart->calculate_totals();
		wp_send_json_success( array( 'message' => 'حذف شد.' ) );
	}
}

if ( ! function_exists( 'ezp_cart_apply_coupon_handler' ) ) {
	add_action( 'wp_ajax_ezp_cart_apply_coupon', 'ezp_cart_apply_coupon_handler' );
	add_action( 'wp_ajax_nopriv_ezp_cart_apply_coupon', 'ezp_cart_apply_coupon_handler' );
	function ezp_cart_apply_coupon_handler() {
		if ( ! isset( $_POST['security'] ) || ! wp_verify_nonce( $_POST['security'], 'ezp_cart_action' ) ) {
			wp_send_json_error( array( 'message' => 'درخواست نامعتبر.' ) );
		}
		$code = sanitize_text_field( wp_unslash( $_POST['coupon_code'] ?? '' ) );
		if ( ! $code || ! WC()->cart ) {
			wp_send_json_error( array( 'message' => 'کد تخفیف را وارد کنید.' ) );
		}
		wc_clear_notices();
		$applied = WC()->cart->apply_coupon( $code );
		WC()->cart->calculate_totals();
		if ( $applied ) {
			wc_clear_notices();
			wp_send_json_success( array( 'message' => 'کد تخفیف اعمال شد' ) );
		} else {
			$notices = wc_get_notices( 'error' );
			$msg = ! empty( $notices ) ? wp_strip_all_tags( $notices[0]['notice'] ) : 'کد تخفیف نامعتبر است.';
			wc_clear_notices();
			wp_send_json_error( array( 'message' => $msg ) );
		}
	}
}