<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }
?>
<form class="ezlens-settings-form">
    <div class="card">
        <h2 style="margin-top:0;">تنظیمات کیف پول</h2>
        <p class="description">روش‌های شارژ کیف پول را کنترل کنید. روش خاموش‌شده در پنل کاربر نمایش داده نمی‌شود و API نیز آن را نمی‌پذیرد.</p>

        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:14px;margin-top:18px;">
            <label style="display:flex;align-items:center;gap:10px;padding:16px;border:1px solid #e2e8f0;border-radius:14px;background:#fff;">
                <input type="checkbox" name="wallet_payment_online_enabled" value="1" <?php checked( $settings['wallet_payment_online_enabled'] ?? '1', '1' ); ?>>
                <span><strong>درگاه پرداخت</strong><br><small>شارژ آنلاین و خودکار</small></span>
            </label>
            <label style="display:flex;align-items:center;gap:10px;padding:16px;border:1px solid #e2e8f0;border-radius:14px;background:#fff;">
                <input type="checkbox" name="wallet_payment_card_enabled" value="1" <?php checked( $settings['wallet_payment_card_enabled'] ?? '1', '1' ); ?>>
                <span><strong>کارت به کارت</strong><br><small>ثبت فیش و کد پیگیری</small></span>
            </label>
            <label style="display:flex;align-items:center;gap:10px;padding:16px;border:1px solid #e2e8f0;border-radius:14px;background:#fff;">
                <input type="checkbox" name="wallet_payment_bank_enabled" value="1" <?php checked( $settings['wallet_payment_bank_enabled'] ?? '1', '1' ); ?>>
                <span><strong>اینترنت‌بانک</strong><br><small>شناسه یا اسکرین پرداخت</small></span>
            </label>
        </div>
    </div>

    <div class="card" style="margin-top:18px;">
        <h2 style="margin-top:0;">حساب مقصد</h2>
        <p class="description">این اطلاعات هم در کیف پول مشتری و هم در EzLens Manager نمایش داده می‌شود.</p>
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:16px;margin-top:18px;">
            <p><label><strong>نام بانک</strong><br><input class="widefat" name="wallet_bank_name" value="<?php echo esc_attr( $settings['wallet_bank_name'] ?? '' ); ?>" placeholder="مثلاً بانک ملت"></label></p>
            <p><label><strong>صاحب حساب</strong><br><input class="widefat" name="wallet_account_owner" value="<?php echo esc_attr( $settings['wallet_account_owner'] ?? '' ); ?>" placeholder="مثلاً ایزی لنز"></label></p>
            <p><label><strong>عنوان حساب</strong><br><input class="widefat" name="wallet_account_name" value="<?php echo esc_attr( $settings['wallet_account_name'] ?? '' ); ?>" placeholder="حساب جاری / تجاری"></label></p>
            <p><label><strong>شماره کارت</strong><br><input class="widefat" name="wallet_card_number" inputmode="numeric" dir="ltr" value="<?php echo esc_attr( $settings['wallet_card_number'] ?? '' ); ?>" placeholder="6037 ..."></label></p>
            <p><label><strong>شماره حساب</strong><br><input class="widefat" name="wallet_account_number" inputmode="numeric" dir="ltr" value="<?php echo esc_attr( $settings['wallet_account_number'] ?? '' ); ?>"></label></p>
            <p><label><strong>شماره شبا</strong><br><input class="widefat" name="wallet_iban" dir="ltr" value="<?php echo esc_attr( $settings['wallet_iban'] ?? '' ); ?>" placeholder="IR..."></label></p>
        </div>
        <p><label><strong>توضیحات پرداخت</strong><br><textarea class="widefat" rows="3" name="wallet_account_note" placeholder="مثلاً بعد از واریز، کد پیگیری را ثبت کنید."><?php echo esc_textarea( $settings['wallet_account_note'] ?? '' ); ?></textarea></label></p>
    </div>
</form>