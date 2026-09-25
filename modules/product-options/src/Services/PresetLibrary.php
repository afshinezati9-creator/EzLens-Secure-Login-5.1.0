<?php

namespace EzLens\ProductOptions\Services;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Built-in starter templates for Iranian optical and vision-care stores.
 * Presets are immutable definitions; using one creates a normal editable template.
 */
final class PresetLibrary {
    public const VERSION = 4;

    /**
     * Return all built-in presets.
     */
    public function all() {
        return [
            $this->glasses_prescription(),
            $this->sunglasses(),
            $this->contact_lens_prescription(),
            $this->medical_contact_lens(),
            $this->optical_lens(),
            $this->eyeglass_frame(),
            $this->colored_contact_lens(),
            $this->lens_care(),
            $this->accessories(),
            $this->vision_care_product(),
            $this->custom_product(),
        ];
    }

    public function get($slug) {
        $slug = sanitize_key($slug);
        foreach ($this->all() as $preset) {
            if ($preset['slug'] === $slug) return $preset;
        }
        return null;
    }

    private function base($slug, $title, $description, $icon, $category, $fields, $settings = [], $layout = []) {
        return [
            'version' => self::VERSION,
            'slug' => $slug,
            'title' => $title,
            'description' => $description,
            'icon' => $icon,
            'category' => $category,
            'fields' => $fields,
            'settings' => wp_parse_args($settings, [
                'direction' => 'rtl',
                'locale' => 'fa_IR',
                'currency_display' => 'toman',
            ]),
            'layout' => wp_parse_args($layout, [
                'columns' => 2,
                'gap' => 'medium',
            ]),
        ];
    }

    private function eye_fields($prefix, $label) {
        return [
            $prefix . '_sph' => ['name' => $prefix . '_sph', 'type' => 'number', 'label' => $label . ' — SPH', 'placeholder' => 'مثلاً -2.50', 'step' => '0.25'],
            $prefix . '_cyl' => ['name' => $prefix . '_cyl', 'type' => 'number', 'label' => $label . ' — CYL', 'placeholder' => 'مثلاً -1.25', 'step' => '0.25'],
            $prefix . '_axis' => ['name' => $prefix . '_axis', 'type' => 'number', 'label' => $label . ' — AXIS', 'placeholder' => '۰ تا ۱۸۰', 'min' => 0, 'max' => 180, 'step' => 1],
            $prefix . '_add' => ['name' => $prefix . '_add', 'type' => 'number', 'label' => $label . ' — ADD', 'placeholder' => 'در صورت نیاز', 'step' => '0.25'],
        ];
    }

    private function glasses_prescription() {
        $fields = [
            'rx_intro' => [
                'name' => 'rx_intro', 'type' => 'heading',
                'label' => 'نسخه عینک طبی',
            ],
            'rx_hint' => [
                'name' => 'rx_hint', 'type' => 'html',
                'label' => 'راهنما',
                'content' => '<p style="margin:0 0 12px;color:#64748b;font-size:13px;line-height:1.7;">اعداد را دقیقاً مطابق برگه پزشک وارد کنید. SPH نزدیک‌بینی منفی و دوربینی مثبت است. AXIS فقط وقتی CYL دارید معنا دارد.</p>',
            ],
            'doctor_name' => [
                'name' => 'doctor_name', 'type' => 'text', 'label' => 'نام پزشک / مرکز',
                'placeholder' => 'اختیاری — مثلاً دکتر …',
            ],
            'rx_date' => [
                'name' => 'rx_date', 'type' => 'date', 'label' => 'تاریخ نسخه',
            ],
            'od_block' => ['name' => 'od_block', 'type' => 'heading', 'label' => 'چشم راست (OD)'],
            'od_sph' => ['name' => 'od_sph', 'type' => 'number', 'label' => 'SPH', 'placeholder' => 'مثلاً -۲.۵۰', 'step' => '0.25', 'required' => true],
            'od_cyl' => ['name' => 'od_cyl', 'type' => 'number', 'label' => 'CYL', 'placeholder' => 'مثلاً -۰.۷۵', 'step' => '0.25'],
            'od_axis' => ['name' => 'od_axis', 'type' => 'number', 'label' => 'AXIS', 'placeholder' => '۰ تا ۱۸۰', 'min' => 0, 'max' => 180, 'step' => 1],
            'od_add' => ['name' => 'od_add', 'type' => 'number', 'label' => 'ADD', 'placeholder' => 'در صورت پیرچشمی', 'step' => '0.25'],
            'os_block' => ['name' => 'os_block', 'type' => 'heading', 'label' => 'چشم چپ (OS)'],
            'os_sph' => ['name' => 'os_sph', 'type' => 'number', 'label' => 'SPH', 'placeholder' => 'مثلاً -۱.۷۵', 'step' => '0.25', 'required' => true],
            'os_cyl' => ['name' => 'os_cyl', 'type' => 'number', 'label' => 'CYL', 'placeholder' => 'مثلاً -۰.۵۰', 'step' => '0.25'],
            'os_axis' => ['name' => 'os_axis', 'type' => 'number', 'label' => 'AXIS', 'placeholder' => '۰ تا ۱۸۰', 'min' => 0, 'max' => 180, 'step' => 1],
            'os_add' => ['name' => 'os_add', 'type' => 'number', 'label' => 'ADD', 'placeholder' => 'در صورت پیرچشمی', 'step' => '0.25'],
            'pd_block' => ['name' => 'pd_block', 'type' => 'heading', 'label' => 'فاصله مردمکی (PD)'],
            'pd_type' => [
                'name' => 'pd_type', 'type' => 'select', 'label' => 'نوع PD', 'required' => true,
                'options' => [
                    ['value' => 'binocular', 'label' => 'دوچشمی (یک عدد)'],
                    ['value' => 'monocular', 'label' => 'تک‌چشمی (راست / چپ)'],
                ],
            ],
            'pd' => ['name' => 'pd', 'type' => 'number', 'label' => 'PD دوچشمی', 'placeholder' => 'مثلاً ۶۲', 'min' => 40, 'max' => 80, 'step' => '0.5'],
            'pd_od' => ['name' => 'pd_od', 'type' => 'number', 'label' => 'PD راست', 'placeholder' => 'مثلاً ۳۱', 'step' => '0.5'],
            'pd_os' => ['name' => 'pd_os', 'type' => 'number', 'label' => 'PD چپ', 'placeholder' => 'مثلاً ۳۱', 'step' => '0.5'],
            'use_type' => [
                'name' => 'use_type', 'type' => 'select', 'label' => 'کاربرد عینک', 'required' => true,
                'options' => [
                    ['value' => 'distance', 'label' => 'دور'],
                    ['value' => 'near', 'label' => 'نزدیک / مطالعه'],
                    ['value' => 'computer', 'label' => 'کامپیوتر / میانی'],
                    ['value' => 'progressive', 'label' => 'تدریجی (پروگرسیو)'],
                    ['value' => 'bifocal', 'label' => 'دوکانونی'],
                ],
            ],
            'lens_index' => [
                'name' => 'lens_index', 'type' => 'select', 'label' => 'ضریب شکست عدسی (در صورت مشخص بودن)',
                'options' => [
                    ['value' => '', 'label' => 'مهم نیست / بعداً'],
                    ['value' => '1.5', 'label' => '۱.۵۰ استاندارد'],
                    ['value' => '1.56', 'label' => '۱.۵۶'],
                    ['value' => '1.6', 'label' => '۱.۶۰ نازک'],
                    ['value' => '1.67', 'label' => '۱.۶۷ فوق نازک'],
                    ['value' => '1.74', 'label' => '۱.۷۴ بسیار نازک'],
                ],
            ],
            'rx_file' => ['name' => 'rx_file', 'type' => 'upload', 'label' => 'آپلود تصویر نسخه (اختیاری)'],
            'prescription_note' => [
                'name' => 'prescription_note', 'type' => 'textarea',
                'label' => 'یادداشت برای اپتومتریست',
                'placeholder' => 'مثلاً ترجیح پوشش ضدبازتاب، حساسیت به نور، …',
            ],
        ];
        return $this->base(
            'glasses-prescription',
            'نسخه عینک طبی',
            'فرم حرفه‌ای ثبت SPH/CYL/AXIS و PD برای سفارش عینک طبی — مناسب مشتری و بررسی توسط بینایی‌سنج.',
            '👓',
            'نسخه و اندازه‌گیری',
            $fields
        );
    }


    private function contact_lens_prescription() {
        $fields = [
            'cl_intro' => ['name' => 'cl_intro', 'type' => 'heading', 'label' => 'لنز تماسی (عمومی / رنگی)'],
            'cl_hint' => [
                'name' => 'cl_hint', 'type' => 'html', 'label' => 'راهنما',
                'content' => '<p style="margin:0 0 12px;color:#64748b;font-size:13px;line-height:1.7;">Power همان شماره نزدیک‌بینی/دوربینی است. BC انحنای لنز و DIA قطر آن است — این دو را از جعبه یا نسخه بردارید.</p>',
            ],
            'wear_mode' => [
                'name' => 'wear_mode', 'type' => 'select', 'label' => 'نوع مصرف', 'required' => true,
                'options' => [
                    ['value' => 'daily', 'label' => 'روزانه یک‌بار مصرف'],
                    ['value' => 'biweekly', 'label' => 'دو هفته‌ای'],
                    ['value' => 'monthly', 'label' => 'ماهانه'],
                    ['value' => 'yearly', 'label' => 'سالانه / سخت'],
                ],
            ],
            'od_power' => ['name' => 'od_power', 'type' => 'number', 'label' => 'Power چشم راست (OD)', 'placeholder' => 'مثلاً -۲.۵۰', 'step' => '0.25', 'required' => true],
            'os_power' => ['name' => 'os_power', 'type' => 'number', 'label' => 'Power چشم چپ (OS)', 'placeholder' => 'مثلاً -۲.۲۵', 'step' => '0.25', 'required' => true],
            'bc' => ['name' => 'bc', 'type' => 'number', 'label' => 'BC (انحنا)', 'placeholder' => 'مثلاً ۸.۶', 'step' => '0.1', 'required' => true],
            'dia' => ['name' => 'dia', 'type' => 'number', 'label' => 'DIA (قطر)', 'placeholder' => 'مثلاً ۱۴.۲', 'step' => '0.1', 'required' => true],
            'od_cyl' => ['name' => 'od_cyl', 'type' => 'number', 'label' => 'CYL راست (در صورت آستیگمات)', 'placeholder' => 'اختیاری', 'step' => '0.25'],
            'od_axis' => ['name' => 'od_axis', 'type' => 'number', 'label' => 'AXIS راست', 'placeholder' => '۰–۱۸۰', 'min' => 0, 'max' => 180],
            'os_cyl' => ['name' => 'os_cyl', 'type' => 'number', 'label' => 'CYL چپ (در صورت آستیگمات)', 'placeholder' => 'اختیاری', 'step' => '0.25'],
            'os_axis' => ['name' => 'os_axis', 'type' => 'number', 'label' => 'AXIS چپ', 'placeholder' => '۰–۱۸۰', 'min' => 0, 'max' => 180],
            'color' => [
                'name' => 'color', 'type' => 'select', 'label' => 'رنگ لنز',
                'options' => [
                    ['value' => 'clear', 'label' => 'شفاف'],
                    ['value' => 'gray', 'label' => 'خاکستری'],
                    ['value' => 'blue', 'label' => 'آبی'],
                    ['value' => 'green', 'label' => 'سبز'],
                    ['value' => 'brown', 'label' => 'عسلی / قهوه‌ای'],
                    ['value' => 'other', 'label' => 'سایر'],
                ],
            ],
            'pack_qty' => [
                'name' => 'pack_qty', 'type' => 'select', 'label' => 'تعداد / بسته‌بندی',
                'options' => [
                    ['value' => '2', 'label' => '۲ عدد (یک جفت)'],
                    ['value' => '30', 'label' => '۳۰ عددی'],
                    ['value' => '90', 'label' => '۹۰ عددی'],
                    ['value' => 'other', 'label' => 'سایر'],
                ],
            ],
            'cl_note' => ['name' => 'cl_note', 'type' => 'textarea', 'label' => 'توضیحات', 'placeholder' => 'برند قبلی، حساسیت، خشکی چشم، …'],
        ];
        return $this->base(
            'contact-lens-prescription',
            'لنز تماسی',
            'ثبت Power، BC و DIA برای سفارش لنز تماسی — مینیمال و خوانا برای مشتری.',
            '👁️',
            'لنز تماسی',
            $fields
        );
    }


    private function sunglasses() {
        $fields = [
            'sg_intro' => ['name' => 'sg_intro', 'type' => 'heading', 'label' => 'عینک آفتابی'],
            'sg_hint' => [
                'name' => 'sg_hint', 'type' => 'html', 'label' => 'راهنما',
                'content' => '<p style="margin:0 0 12px;color:#64748b;font-size:13px;line-height:1.7;">اگر عینک آفتابی <strong>نمره‌دار</strong> می‌خواهید، بخش نسخه را پر کنید؛ در غیر این صورت فقط ظاهر و لنز را انتخاب کنید.</p>',
            ],
            'sg_mode' => [
                'name' => 'sg_mode', 'type' => 'select', 'label' => 'نوع سفارش', 'required' => true,
                'options' => [
                    ['value' => 'plano', 'label' => 'بدون نمره (فقط آفتابی)'],
                    ['value' => 'rx', 'label' => 'نمره‌دار (با نسخه)'],
                ],
            ],
            'lens_tint' => [
                'name' => 'lens_tint', 'type' => 'select', 'label' => 'رنگ / نوع لنز', 'required' => true,
                'options' => [
                    ['value' => 'gray', 'label' => 'خاکستری کلاسیک'],
                    ['value' => 'brown', 'label' => 'قهوه‌ای'],
                    ['value' => 'green', 'label' => 'سبز'],
                    ['value' => 'polarized', 'label' => 'پولاریزه'],
                    ['value' => 'photochromic', 'label' => 'فتوکرومیک (تغییر با نور)'],
                    ['value' => 'mirror', 'label' => 'آینه‌ای'],
                ],
            ],
            'uv_class' => [
                'name' => 'uv_class', 'type' => 'select', 'label' => 'حفاظت UV',
                'options' => [
                    ['value' => 'uv400', 'label' => 'UV400 (استاندارد)'],
                    ['value' => 'cat3', 'label' => 'دسته ۳ — آفتابی روز'],
                    ['value' => 'cat4', 'label' => 'دسته ۴ — کوه/برف (رانندگی نه)'],
                ],
            ],
            'frame_size' => [
                'name' => 'frame_size', 'type' => 'select', 'label' => 'سایز قاب (در صورت مشخص بودن)',
                'options' => [
                    ['value' => '', 'label' => 'مطابق مدل انتخاب‌شده'],
                    ['value' => 's', 'label' => 'کوچک'],
                    ['value' => 'm', 'label' => 'متوسط'],
                    ['value' => 'l', 'label' => 'بزرگ'],
                ],
            ],
            'od_sph' => ['name' => 'od_sph', 'type' => 'number', 'label' => 'SPH راست (اگر نمره‌دار)', 'placeholder' => 'اختیاری', 'step' => '0.25'],
            'os_sph' => ['name' => 'os_sph', 'type' => 'number', 'label' => 'SPH چپ (اگر نمره‌دار)', 'placeholder' => 'اختیاری', 'step' => '0.25'],
            'pd' => ['name' => 'pd', 'type' => 'number', 'label' => 'PD', 'placeholder' => 'برای نمره‌دار توصیه می‌شود', 'step' => '0.5'],
            'sg_note' => ['name' => 'sg_note', 'type' => 'textarea', 'label' => 'توضیحات', 'placeholder' => 'ترجیح فریم، برند، …'],
        ];
        return $this->base(
            'sunglasses',
            'عینک آفتابی',
            'انتخاب لنز آفتابی، UV و در صورت نیاز نمره — مناسب فروشگاه عینک آفتابی.',
            '🕶️',
            'فریم و اکسسوری',
            $fields
        );
    }

    private function medical_contact_lens() {
        $fields = [
            'mcl_intro' => ['name' => 'mcl_intro', 'type' => 'heading', 'label' => 'نسخه لنز طبی (تخصصی)'],
            'mcl_hint' => [
                'name' => 'mcl_hint', 'type' => 'html', 'label' => 'راهنما',
                'content' => '<p style="margin:0 0 12px;color:#64748b;font-size:13px;line-height:1.7;">برای لنزهای توریک، مولتی‌فوکال یا اسکلرال، تمام پارامترهای نسخه پزشک را وارد کنید. در صورت ابهام، تصویر نسخه را پیوست کنید تا اپتومتریست بررسی کند.</p>',
            ],
            'lens_category' => [
                'name' => 'lens_category', 'type' => 'select', 'label' => 'دسته لنز', 'required' => true,
                'options' => [
                    ['value' => 'spherical', 'label' => 'کروی (Spherical)'],
                    ['value' => 'toric', 'label' => 'توریک / آستیگمات'],
                    ['value' => 'multifocal', 'label' => 'چندکانونی'],
                    ['value' => 'scleral', 'label' => 'اسکلرال'],
                    ['value' => 'rgp', 'label' => 'سخت گازگذر (RGP)'],
                ],
            ],
            'od_h' => ['name' => 'od_h', 'type' => 'heading', 'label' => 'چشم راست (OD)'],
            'od_power' => ['name' => 'od_power', 'type' => 'number', 'label' => 'Power / SPH', 'placeholder' => '-۳.۰۰', 'step' => '0.25', 'required' => true],
            'od_cyl' => ['name' => 'od_cyl', 'type' => 'number', 'label' => 'CYL', 'placeholder' => 'توریک', 'step' => '0.25'],
            'od_axis' => ['name' => 'od_axis', 'type' => 'number', 'label' => 'AXIS', 'min' => 0, 'max' => 180, 'step' => 1],
            'od_add' => ['name' => 'od_add', 'type' => 'number', 'label' => 'ADD', 'step' => '0.25'],
            'od_bc' => ['name' => 'od_bc', 'type' => 'number', 'label' => 'BC', 'placeholder' => '۸.۶', 'step' => '0.1', 'required' => true],
            'od_dia' => ['name' => 'od_dia', 'type' => 'number', 'label' => 'DIA', 'placeholder' => '۱۴.۲', 'step' => '0.1', 'required' => true],
            'os_h' => ['name' => 'os_h', 'type' => 'heading', 'label' => 'چشم چپ (OS)'],
            'os_power' => ['name' => 'os_power', 'type' => 'number', 'label' => 'Power / SPH', 'placeholder' => '-۲.۷۵', 'step' => '0.25', 'required' => true],
            'os_cyl' => ['name' => 'os_cyl', 'type' => 'number', 'label' => 'CYL', 'step' => '0.25'],
            'os_axis' => ['name' => 'os_axis', 'type' => 'number', 'label' => 'AXIS', 'min' => 0, 'max' => 180, 'step' => 1],
            'os_add' => ['name' => 'os_add', 'type' => 'number', 'label' => 'ADD', 'step' => '0.25'],
            'os_bc' => ['name' => 'os_bc', 'type' => 'number', 'label' => 'BC', 'placeholder' => '۸.۶', 'step' => '0.1', 'required' => true],
            'os_dia' => ['name' => 'os_dia', 'type' => 'number', 'label' => 'DIA', 'placeholder' => '۱۴.۲', 'step' => '0.1', 'required' => true],
            'brand_pref' => ['name' => 'brand_pref', 'type' => 'text', 'label' => 'برند / مدل پیشنهادی پزشک', 'placeholder' => 'اختیاری'],
            'rx_file' => ['name' => 'rx_file', 'type' => 'upload', 'label' => 'تصویر نسخه لنز'],
            'mcl_note' => ['name' => 'mcl_note', 'type' => 'textarea', 'label' => 'نکات بالینی', 'placeholder' => 'قوز قرنیه، خشکی، سابقه جراحی، …'],
        ];
        return $this->base(
            'medical-contact-lens',
            'نسخه لنز طبی',
            'پارامترهای کامل لنز طبی برای توریک، چندکانونی و اسکلرال — با نگاه تخصصی بینایی‌سنجی.',
            '🩺',
            'لنز تماسی',
            $fields
        );
    }

    private function optical_lens() {
        $fields = [
            'lens_kind' => ['name' => 'lens_kind', 'type' => 'select', 'label' => 'نوع عدسی', 'required' => true, 'options' => [
                ['value' => 'single_vision', 'label' => 'تک‌دید'], ['value' => 'bifocal', 'label' => 'دو دید'], ['value' => 'progressive', 'label' => 'تدریجی'], ['value' => 'office', 'label' => 'اداری/کامپیوتر'],
            ]],
            'index' => ['name' => 'index', 'type' => 'select', 'label' => 'ضریب شکست', 'options' => [
                ['value' => '1.56', 'label' => '1.56'], ['value' => '1.60', 'label' => '1.60'], ['value' => '1.67', 'label' => '1.67'], ['value' => '1.74', 'label' => '1.74'],
            ]],
            'coating' => ['name' => 'coating', 'type' => 'checkbox', 'label' => 'پوشش‌ها', 'options' => [
                ['value' => 'antireflective', 'label' => 'آنتی‌رفلکس'], ['value' => 'blue_cut', 'label' => 'فیلتر نور آبی'], ['value' => 'uv', 'label' => 'UV'], ['value' => 'scratch', 'label' => 'ضدخش'],
            ]],
            'photochromic' => ['name' => 'photochromic', 'type' => 'radio', 'label' => 'فتوکرومیک', 'options' => [
                ['value' => 'no', 'label' => 'خیر'], ['value' => 'yes', 'label' => 'بله'],
            ]],
        ];
        return $this->base('optical-lens', 'لنز طبی عینک', 'انتخاب نوع عدسی، ضریب شکست و پوشش‌های قابل سفارش.', '🔍', 'عدسی عینک', $fields);
    }

    private function eyeglass_frame() {
        $fields = [
            'frame_color' => ['name' => 'frame_color', 'type' => 'select', 'label' => 'رنگ فریم', 'options' => [
                ['value' => 'black', 'label' => 'مشکی'], ['value' => 'brown', 'label' => 'قهوه‌ای'], ['value' => 'gold', 'label' => 'طلایی'], ['value' => 'silver', 'label' => 'نقره‌ای'], ['value' => 'transparent', 'label' => 'شفاف'], ['value' => 'other', 'label' => 'سایر'],
            ]],
            'frame_size' => ['name' => 'frame_size', 'type' => 'select', 'label' => 'سایز فریم', 'options' => [
                ['value' => 'small', 'label' => 'کوچک'], ['value' => 'medium', 'label' => 'متوسط'], ['value' => 'large', 'label' => 'بزرگ'],
            ]],
            'frame_material' => ['name' => 'frame_material', 'type' => 'select', 'label' => 'جنس فریم', 'options' => [
                ['value' => 'acetate', 'label' => 'استات'], ['value' => 'metal', 'label' => 'فلزی'], ['value' => 'mixed', 'label' => 'ترکیبی'], ['value' => 'other', 'label' => 'سایر'],
            ]],
            'frame_shape' => ['name' => 'frame_shape', 'type' => 'select', 'label' => 'فرم فریم', 'options' => [
                ['value' => 'round', 'label' => 'گرد'], ['value' => 'square', 'label' => 'مربعی'], ['value' => 'rectangular', 'label' => 'مستطیلی'], ['value' => 'cat_eye', 'label' => 'کت‌آی'], ['value' => 'aviator', 'label' => 'خلبانی'], ['value' => 'other', 'label' => 'سایر'],
            ]],
        ];
        return $this->base('eyeglass-frame', 'فریم عینک', 'ویژگی‌های قابل انتخاب برای فریم و مدل عینک.', '🕶️', 'فریم و اکسسوری', $fields);
    }

    private function colored_contact_lens() {
        $fields = [
            'color' => ['name' => 'color', 'type' => 'select', 'label' => 'رنگ لنز', 'required' => true, 'options' => [
                ['value' => 'brown', 'label' => 'قهوه‌ای'], ['value' => 'hazel', 'label' => 'عسلی'], ['value' => 'green', 'label' => 'سبز'], ['value' => 'gray', 'label' => 'طوسی'], ['value' => 'blue', 'label' => 'آبی'], ['value' => 'other', 'label' => 'سایر'],
            ]],
            'power' => ['name' => 'power', 'type' => 'number', 'label' => 'نمره لنز', 'placeholder' => 'مثلاً -1.50', 'step' => '0.25'],
            'wear_period' => ['name' => 'wear_period', 'type' => 'select', 'label' => 'مدت مصرف', 'options' => [
                ['value' => 'daily', 'label' => 'روزانه'], ['value' => 'monthly', 'label' => 'ماهانه'], ['value' => 'quarterly', 'label' => 'سه‌ماهه'],
            ]],
            'quantity' => ['name' => 'quantity', 'type' => 'number', 'label' => 'تعداد', 'min' => 1, 'step' => 1],
        ];
        return $this->base('colored-contact-lens', 'لنز رنگی', 'قالب ساده و کاربردی برای محصولات لنز رنگی.', '🌈', 'لنز تماسی', $fields);
    }

    private function lens_care() {
        $fields = [
            'volume' => ['name' => 'volume', 'type' => 'select', 'label' => 'حجم', 'options' => [
                ['value' => '60', 'label' => '۶۰ میلی‌لیتر'], ['value' => '120', 'label' => '۱۲۰ میلی‌لیتر'], ['value' => '360', 'label' => '۳۶۰ میلی‌لیتر'], ['value' => '500', 'label' => '۵۰۰ میلی‌لیتر'],
            ]],
            'care_type' => ['name' => 'care_type', 'type' => 'select', 'label' => 'نوع محصول', 'options' => [
                ['value' => 'multipurpose', 'label' => 'محلول چندمنظوره'], ['value' => 'saline', 'label' => 'محلول سالین'], ['value' => 'rewetting', 'label' => 'قطره مرطوب‌کننده'], ['value' => 'case', 'label' => 'جا لنزی'],
            ]],
            'brand' => ['name' => 'brand', 'type' => 'text', 'label' => 'برند', 'placeholder' => 'نام برند'],
        ];
        return $this->base('lens-care', 'محلول و مراقبت لنز', 'برای محلول، قطره، جا لنزی و محصولات مراقبت از لنز.', '🧴', 'مراقبت لنز', $fields);
    }

    private function accessories() {
        $fields = [
            'accessory_type' => ['name' => 'accessory_type', 'type' => 'select', 'label' => 'نوع اکسسوری', 'options' => [
                ['value' => 'case', 'label' => 'جاقابی/قاب عینک'], ['value' => 'cloth', 'label' => 'دستمال عینک'], ['value' => 'chain', 'label' => 'بند عینک'], ['value' => 'tool', 'label' => 'ابزار و لوازم جانبی'], ['value' => 'other', 'label' => 'سایر'],
            ]],
            'color' => ['name' => 'color', 'type' => 'select', 'label' => 'رنگ', 'options' => [
                ['value' => 'black', 'label' => 'مشکی'], ['value' => 'white', 'label' => 'سفید'], ['value' => 'brown', 'label' => 'قهوه‌ای'], ['value' => 'other', 'label' => 'سایر'],
            ]],
            'model' => ['name' => 'model', 'type' => 'text', 'label' => 'مدل/شناسه', 'placeholder' => 'مدل محصول'],
        ];
        return $this->base('accessories', 'اکسسوری عینک و لنز', 'قالب عمومی برای لوازم جانبی عینک و لنز.', '🧼', 'فریم و اکسسوری', $fields);
    }

    private function vision_care_product() {
        $fields = [
            'product_usage' => ['name' => 'product_usage', 'type' => 'select', 'label' => 'کاربرد محصول', 'options' => [
                ['value' => 'eye_care', 'label' => 'مراقبت از چشم'], ['value' => 'lens_care', 'label' => 'مراقبت از لنز'], ['value' => 'comfort', 'label' => 'راحتی و خشکی چشم'], ['value' => 'other', 'label' => 'سایر'],
            ]],
            'usage_note' => ['name' => 'usage_note', 'type' => 'textarea', 'label' => 'راهنمای مصرف/توضیحات', 'placeholder' => 'اطلاعات تکمیلی محصول'],
            'manufacturer' => ['name' => 'manufacturer', 'type' => 'text', 'label' => 'تولیدکننده/برند'],
        ];
        return $this->base('vision-care-product', 'محصولات مرتبط با سلامت بینایی', 'قالب عمومی برای محصولات حوزه مراقبت و سلامت بینایی.', '🩺', 'سلامت بینایی', $fields);
    }

    private function custom_product() {
        $fields = [
            'product_note' => ['name' => 'product_note', 'type' => 'textarea', 'label' => 'توضیحات سفارش', 'placeholder' => 'اطلاعات موردنیاز مشتری برای این محصول را وارد کنید.'],
        ];
        return $this->base('custom-product', 'محصول سفارشی', 'یک نقطه شروع ساده برای محصولاتی که قالب اختصاصی ندارند.', '🛒', 'عمومی', $fields, [], ['columns' => 1]);
    }

    /**
     * Create a normal editable template from a preset slug (appears in list).
     *
     * @param string $slug
     * @param string $title Optional override
     * @return array
     */
    public function create_template($slug, $title = '') {
        $preset = $this->get($slug);
        if (!$preset) {
            return array('success' => false, 'message' => 'پالت آماده یافت نشد.', 'reason' => 'slug:' . $slug);
        }
        if (!class_exists('EzLens_Product_Options_Template_Manager')) {
            return array('success' => false, 'message' => 'Template Manager در دسترس نیست.');
        }
        $manager = \EzLens_Product_Options_Template_Manager::get_instance();

        // Validator expects a list of field arrays (not only assoc map).
        $raw_fields = isset($preset['fields']) && is_array($preset['fields']) ? $preset['fields'] : array();
        $fields_list = array();
        foreach ($raw_fields as $key => $field) {
            if (!is_array($field)) {
                continue;
            }
            if (!isset($field['name']) || $field['name'] === '') {
                if (is_string($key) && !is_numeric($key)) {
                    $field['name'] = $key;
                }
            }
            $fields_list[] = $field;
        }

        // Attach professional code pack (HTML/CSS/JS) when available
        $pack_file = dirname(__DIR__, 2) . '/includes/class-preset-code-pack.php';
        if (is_readable($pack_file)) {
            require_once $pack_file;
        }
        $code_pack = class_exists('EzLens_PO_Preset_Code_Pack')
            ? \EzLens_PO_Preset_Code_Pack::for_slug((string) $preset['slug'])
            : null;
        if (is_array($code_pack)) {
            $fields_list['_code_html'] = (string) ($code_pack['html'] ?? '');
            $fields_list['_code_css']  = (string) ($code_pack['css'] ?? '');
            $fields_list['_code_js']   = (string) ($code_pack['js'] ?? '');
        }
        $full_code = '';
        if (is_array($code_pack)) {
            $full_code = (string) ($code_pack['html'] ?? '') . "\n\n/**CSS**/\n" . (string) ($code_pack['css'] ?? '') . "\n\n/**JS**/\n" . (string) ($code_pack['js'] ?? '');
        }

        $data = array(
            'title'       => $title !== '' ? $title : (string) $preset['title'],
            'description' => (string) ($preset['description'] ?? ''),
            'status'      => 'active',
            'fields'      => $fields_list,
            'code'        => $full_code,
            'settings'    => isset($preset['settings']) ? $preset['settings'] : array(),
            'layout'      => isset($preset['layout']) ? $preset['layout'] : array(),
            'is_preset'   => 1,
            'preset_slug' => (string) $preset['slug'],
        );
        $result = $manager->create($data);
        if (!empty($result['success']) && !empty($result['id'])) {
            $id = (int) $result['id'];
            update_option('ezlens_po_preset_flag_' . $id, array(
                'is_preset'   => 1,
                'preset_slug' => (string) $preset['slug'],
            ), false);
            if (class_exists('EzLens_PO_Template_File_Storage')) {
                \EzLens_PO_Template_File_Storage::write($id, $data);
            }
            $result['message'] = 'پالت آماده حرفه‌ای در لیست ایجاد شد.';
        } elseif (empty($result['message'])) {
            $result = array('success' => false, 'message' => 'ایجاد پالت در دیتابیس ناموفق بود.');
        }
        return $result;
    }
}
