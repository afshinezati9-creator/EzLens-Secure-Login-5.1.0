# بازیابی پلاگین EzLens Secure Login

## علت از کار افتادن
فایل‌های پچ قبلی (مثل `bootstrap-snippet.php`) را **داخل ریشه پلاگین** extract کرده بودید.
اگر محتوای `ezlens-secure-login.php` با snippet جایگزین شده باشد، پلاگین دیگر بوت نمی‌شود.

## کارهایی که باید انجام دهید

### ۱) حذف فایل‌های اضافه از ریشه پلاگین
این‌ها را **پاک کنید** (فقط راهنما بودند، جزء پلاگین نیستند):

- `api-v2-create-user.FRAGMENT.php`
- `bootstrap-snippet.php`
- `cd-admin-create.FRAGMENT.php`
- `PATCH-NOTES.md`

### ۲) بازگردانی فایل اصلی
فایل کامل این بسته را کپی کنید روی:

`wp-content/plugins/EzLens-Secure-Login-5.1.0/ezlens-secure-login.php`

(باید حدود ۳۷KB و دارای `Plugin Name: EzLens Secure Login` باشد)

### ۳) فایل‌های پچ امن
کپی کنید:

| از بسته | به |
|---------|-----|
| `modules/auth/class-username-policy.php` | همان مسیر در پلاگین |
| `api/class-api-v2.php` | همان مسیر |
| `modules/customer-dashboard/class-admin.php` | همان مسیر |

### ۴) تست
- پیشخوان وردپرس را باز کنید
- پلاگین باید در لیست فعال باشد
- اگر سفید بود: `wp-config.php` موقتاً `define('WP_DEBUG', true);` و خطا را بخوانید

## قانون نام کاربری (یادآوری)
- `user_login` = فقط `09xxxxxxxxx`
- ایمیل می‌تواند `09...@ezlens.ir` باشد (این ایمیل است نه نام کاربری)
