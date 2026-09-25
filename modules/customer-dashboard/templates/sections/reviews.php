<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$reviews = EzLens_CD_Reviews::for_user();
?>
<div class="ezcd-reviews">
	<div class="ezcd-rx-intro">
		<span class="ezcd-ico"><?php echo ezcd_icon( 'star' ); ?></span>
		<div>
			<strong>نظرات شما</strong>
			<p class="ezcd-muted">بازخوردتان به دیگران برای انتخاب مطمئن‌تر کمک می‌کند. می‌توانید همین‌جا ویرایش یا حذف کنید.</p>
		</div>
	</div>

	<?php if ( empty( $reviews ) ) : ?>
		<div class="ezcd-empty">
			<div class="ezcd-empty-ico"><?php echo ezcd_icon( 'star' ); ?></div>
			<p>هنوز نظری ثبت نکرده‌اید. بعد از دریافت محصول، از صفحه همان کالا می‌توانید امتیاز بدهید.</p>
			<a class="ezcd-btn ezcd-btn-grad" href="<?php echo esc_url( function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/' ) ); ?>">مشاهده فروشگاه</a>
		</div>
	<?php else : ?>
		<div class="ezcd-review-list">
			<?php foreach ( $reviews as $c ) :
				$st   = EzLens_CD_Reviews::status_label( $c );
				$rate = EzLens_CD_Reviews::rating( $c->comment_ID );
				$pid  = (int) $c->comment_post_ID;
				$product = function_exists( 'wc_get_product' ) ? wc_get_product( $pid ) : null;
				$title = $product ? $product->get_name() : get_the_title( $pid );
				$url   = $product ? $product->get_permalink() : get_permalink( $pid );
				?>
				<article class="ezcd-review-card" data-id="<?php echo esc_attr( $c->comment_ID ); ?>">
					<div class="ezcd-review-top">
						<div>
							<a href="<?php echo esc_url( $url ); ?>" class="ezcd-review-product"><?php echo esc_html( $title ); ?></a>
							<?php if ( $rate ) : ?>
								<div class="ezcd-stars" aria-label="امتیاز <?php echo esc_attr( $rate ); ?>">
									<?php for ( $i = 1; $i <= 5; $i++ ) : ?>
										<span class="<?php echo $i <= $rate ? 'is-on' : ''; ?>">★</span>
									<?php endfor; ?>
								</div>
							<?php endif; ?>
						</div>
						<span class="ezcd-pill <?php echo 'ok' === $st['class'] ? 'is-ok' : ( 'wait' === $st['class'] ? 'is-wait' : 'is-muted' ); ?>">
							<?php echo esc_html( $st['label'] ); ?>
						</span>
					</div>
					<div class="ezcd-review-view">
						<p class="ezcd-review-body"><?php echo esc_html( $c->comment_content ); ?></p>
						<div class="ezcd-muted"><?php echo esc_html( ezcd_fa( $c->comment_date ) ); ?></div>
						<div class="ezcd-order-actions">
							<button type="button" class="ezcd-btn ezcd-btn-ghost ezcd-review-edit">ویرایش</button>
							<button type="button" class="ezcd-btn ezcd-btn-danger-soft ezcd-review-del">حذف</button>
						</div>
					</div>
					<form class="ezcd-form ezcd-review-edit-form is-hidden">
						<label>متن نظر
							<textarea name="content" rows="3" required><?php echo esc_textarea( $c->comment_content ); ?></textarea>
						</label>
						<label>امتیاز
							<select name="rating">
								<?php for ( $i = 5; $i >= 1; $i-- ) : ?>
									<option value="<?php echo $i; ?>" <?php selected( $rate, $i ); ?>><?php echo $i; ?> ستاره</option>
								<?php endfor; ?>
							</select>
						</label>
						<div class="ezcd-modal-actions">
							<button type="button" class="ezcd-btn ezcd-btn-ghost ezcd-review-cancel">انصراف</button>
							<button type="submit" class="ezcd-btn ezcd-btn-grad">ذخیره نظر</button>
						</div>
					</form>
				</article>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>
</div>
