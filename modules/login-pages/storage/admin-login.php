<?php
/**
 * ورود مدیر — [admin_login_page]
 * پیش‌فرض یکپارچه از templates/defaults (admin-login.html|css|js)
 * ویرایش از: صفحات ورود → ویرایشگر کد
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$ezlp_primary = '#031f8a';
if ( class_exists( 'EzLens_Auth_Settings' ) ) {
	$c = EzLens_Auth_Settings::get( 'primary_color' );
	if ( $c ) {
		$ezlp_primary = $c;
	}
}
?>
<style id="ezlens-lp-admin-login-css">
/* ============================================================
   استایل‌های پیش‌فرض ورود مدیر
   ============================================================ */
.admin-auth-wrap {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 20px;
    background: #0f172a;
    min-height: 100vh;
    font-family: IRANYekan, Tahoma, Arial, sans-serif;
    direction: rtl;
}
.admin-auth-card {
    background: rgba(255,255,255,0.85);
    backdrop-filter: blur(24px);
    border: 2px solid #2b6cb0;
    border-radius: 28px;
    padding: 28px 24px;
    max-width: 420px;
    width: 100%;
    box-shadow: 0 25px 50px rgba(0,0,0,0.08);
}
.admin-auth-badge {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    background: #2b6cb0;
    color: #fff;
    padding: 2px 12px;
    border-radius: 999px;
    font-size: 0.7rem;
    font-weight: 700;
    width: fit-content;
    margin: 0 auto 8px;
}
.admin-auth-card h2 {
    font-size: 22px;
    font-weight: 800;
    text-align: center;
    color: #0f172a;
    margin: 0 0 2px;
}
.admin-auth-url-hint {
    text-align: center;
    font-size: 13px;
    color: #64748b;
    margin: 0 0 18px;
}
.admin-auth-url-hint code {
    background: #f1f5f9;
    padding: 2px 8px;
    border-radius: 6px;
    direction: ltr;
}
.admin-auth-field { margin-bottom: 14px; }
.admin-auth-field label {
    display: block;
    font-size: 13px;
    font-weight: 600;
    color: #334155;
    margin-bottom: 5px;
}
.admin-auth-field input {
    width: 100%;
    padding: 12px 14px;
    background: #f8fafc;
    border: 2px solid transparent;
    border-radius: 14px;
    font-size: 15px;
    outline: none;
    min-height: 48px;
    transition: all 0.3s;
}
.admin-auth-field input:focus {
    background: #fff;
    border-color: #2b6cb0;
}
.admin-auth-extra {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin: -4px 0 14px 0;
    flex-wrap: wrap;
    gap: 6px;
}
.admin-auth-remember {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 13px;
    color: #475569;
    cursor: pointer;
}
.admin-auth-remember input[type="checkbox"] {
    width: 17px;
    height: 17px;
    accent-color: #2b6cb0;
}
.admin-auth-forgot {
    font-size: 13px;
    color: #3b82f6;
    text-decoration: none;
    font-weight: 600;
}
.admin-auth-captcha {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 10px 12px;
    background: #f8fafc;
    border: 2px solid #e2e8f0;
    border-radius: 14px;
    margin-bottom: 14px;
    flex-wrap: wrap;
}
.admin-auth-captcha .captcha-label {
    font-size: 13px;
    font-weight: 600;
    color: #0f172a;
}
.admin-auth-captcha .captcha-numbers {
    display: flex;
    align-items: center;
    gap: 4px;
    font-size: 17px;
    font-weight: 700;
    color: #2b6cb0;
}
.admin-auth-captcha .captcha-numbers span {
    background: #fff;
    padding: 3px 9px;
    border-radius: 8px;
    border: 1px solid #e2e8f0;
    min-width: 22px;
    text-align: center;
}
.admin-auth-captcha input {
    flex: 1;
    min-width: 50px;
    max-width: 70px;
    padding: 8px 6px;
    border: 2px solid #e2e8f0;
    border-radius: 10px;
    font-size: 16px;
    text-align: center;
    background: #fff;
    outline: none;
    min-height: 40px;
}
.admin-auth-captcha input:focus { border-color: #2b6cb0; }
.admin-auth-captcha .captcha-reload {
    cursor: pointer;
    background: #f1f5f9;
    border: none;
    border-radius: 50%;
    width: 34px;
    height: 34px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 16px;
    color: #475569;
    transition: all 0.2s;
}
.admin-auth-captcha .captcha-reload:hover { background: #e2e8f0; transform: rotate(45deg); }
.admin-auth-btn {
    width: 100%;
    padding: 14px;
    border: none;
    border-radius: 14px;
    background: #2b6cb0;
    color: #fff;
    font-size: 15px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s;
    min-height: 50px;
}
.admin-auth-btn:hover {
    background: #2c5282;
    transform: translateY(-2px);
    box-shadow: 0 10px 25px rgba(43,108,176,0.2);
}
.admin-auth-warning {
    margin-top: 12px;
    text-align: center;
    font-size: 0.75rem;
    color: #64748b;
    background: #fef3c7;
    padding: 8px 12px;
    border-radius: 10px;
    border: 1px solid #fcd34d;
}
.admin-auth-warning code {
    background: #fff;
    padding: 1px 6px;
    border-radius: 4px;
    direction: ltr;
}
.admin-auth-back {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    margin-top: 14px;
    padding: 10px 18px;
    background: rgba(43,108,176,0.06);
    border: 1px solid rgba(43,108,176,0.1);
    border-radius: 12px;
    color: #2b6cb0;
    font-size: 14px;
    font-weight: 600;
    text-decoration: none;
    width: 100%;
    text-align: center;
}
.admin-auth-back:hover { background: rgba(43,108,176,0.1); }
@media (max-width: 480px) {
    .admin-auth-card { padding: 20px 16px; border-radius: 20px; }
    .admin-auth-card h2 { font-size: 20px; }
}
</style>
<!-- ============================================================
     قالب پیش‌فرض صفحه ورود مدیر
     کلاس‌های اصلی: .admin-auth-wrap, .admin-auth-card
     ============================================================ -->
<div class="admin-auth-wrap">
    <div class="admin-auth-logo" style="text-align:center;margin-bottom:20px;">
        <div style="font-size:28px;font-weight:800;color:#fff;">ایزی لنز</div>
    </div>
    <div class="admin-auth-card">
        <div class="admin-auth-badge">🔒 ورود مدیران</div>
        <h2>ورود به پنل مدیریت</h2>
        <p class="admin-auth-url-hint">
            آدرس جدید: <code>ezlens.ir/admin-secret</code>
        </p>
        <form>
            <div class="admin-auth-field">
                <label>نام کاربری مدیریت</label>
                <input type="text" placeholder="admin" value="admin">
            </div>
            <div class="admin-auth-field">
                <label>رمز عبور</label>
                <input type="password" placeholder="••••••••" value="admin123">
            </div>
            <div class="admin-auth-extra">
                <label class="admin-auth-remember">
                    <input type="checkbox" checked> مرا به خاطر بسپار
                </label>
                <a class="admin-auth-forgot" href="#">فراموشی رمز؟</a>
            </div>
            <div class="admin-auth-captcha">
                <span class="captcha-label">کد امنیتی:</span>
                <span class="captcha-numbers"><span>6</span><span>+</span><span>3</span></span>
                <span class="captcha-equals">=</span>
                <input type="text" class="captcha-input" placeholder="?">
                <button type="button" class="captcha-reload">🔄</button>
            </div>
            <button class="admin-auth-btn" type="submit">ورود به مدیریت</button>
            <div class="admin-auth-warning">
                ⚠️ آدرس‌های <code>wp-admin</code> و <code>wp-login.php</code> غیرفعال شده‌اند.
            </div>
        </form>
        <a class="admin-auth-back" href="#">← بازگشت به فروشگاه</a>
    </div>
</div>
<script id="ezlens-lp-admin-login-js">
// ============================================================
// تعاملات پیش‌فرض ورود مدیر (ایزوله با IIFE)
// ============================================================
(function() {
    // رفرش کپچا
    document.querySelectorAll('.admin-auth-captcha .captcha-reload').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var container = this.closest('.admin-auth-captcha');
            var num1 = container.querySelector('.captcha-numbers span:first-child');
            var num2 = container.querySelector('.captcha-numbers span:nth-child(3)');
            var input = container.querySelector('.captcha-input');
            var n1 = Math.floor(Math.random() * 9) + 1;
            var n2 = Math.floor(Math.random() * 9) + 1;
            if (num1) num1.textContent = n1;
            if (num2) num2.textContent = n2;
            if (input) input.value = '';
        });
    });

    // شبیه‌سازی ارسال فرم
    document.querySelector('.admin-auth-card form').addEventListener('submit', function(e) {
        e.preventDefault();
        var btn = this.querySelector('.admin-auth-btn');
        var orig = btn.textContent;
        btn.textContent = '⏳ در حال ورود...';
        btn.style.opacity = '0.7';
        setTimeout(function() {
            btn.textContent = '✅ ورود موفق!';
            btn.style.background = '#38a169';
            setTimeout(function() {
                btn.textContent = orig;
                btn.style.background = '';
                btn.style.opacity = '1';
                alert('🚀 به پنل مدیریت خوش آمدید!');
            }, 1200);
        }, 600);
    });

    console.log('✅ صفحه ورود مدیر بارگذاری شد.');
})();
</script>
