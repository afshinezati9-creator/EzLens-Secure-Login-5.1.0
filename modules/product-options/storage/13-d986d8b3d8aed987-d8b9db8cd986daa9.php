<?php
/**
 * EzLens Product Options palette #13
 * Auto-generated file storage (like purchase-process).
 */
if (!defined('ABSPATH')) { exit; }

return array (
  'id' => 13,
  'title' => 'نسخه عینک',
  'description' => 'فرم حرفه‌ای ثبت SPH/CYL/AXIS و PD برای سفارش عینک طبی — مناسب مشتری و بررسی توسط بینایی‌سنج.',
  'status' => 'active',
  'fields' => 
  array (
    0 => 
    array (
      'name' => 'rx_intro',
      'type' => 'heading',
      'label' => 'نسخه عینک طبی',
    ),
    1 => 
    array (
      'name' => 'rx_hint',
      'type' => 'html',
      'label' => 'راهنما',
      'content' => '<p style="margin:0 0 12px;color:#64748b;font-size:13px;line-height:1.7;">اعداد را دقیقاً مطابق برگه پزشک وارد کنید. SPH نزدیک‌بینی منفی و دوربینی مثبت است. AXIS فقط وقتی CYL دارید معنا دارد.</p>',
    ),
    2 => 
    array (
      'name' => 'doctor_name',
      'type' => 'text',
      'label' => 'نام پزشک / مرکز',
      'placeholder' => 'اختیاری — مثلاً دکتر …',
    ),
    3 => 
    array (
      'name' => 'rx_date',
      'type' => 'date',
      'label' => 'تاریخ نسخه',
    ),
    4 => 
    array (
      'name' => 'od_block',
      'type' => 'heading',
      'label' => 'چشم راست (OD)',
    ),
    5 => 
    array (
      'name' => 'od_sph',
      'type' => 'number',
      'label' => 'SPH',
      'placeholder' => 'مثلاً -۲.۵۰',
      'step' => '0.25',
      'required' => true,
    ),
    6 => 
    array (
      'name' => 'od_cyl',
      'type' => 'number',
      'label' => 'CYL',
      'placeholder' => 'مثلاً -۰.۷۵',
      'step' => '0.25',
    ),
    7 => 
    array (
      'name' => 'od_axis',
      'type' => 'number',
      'label' => 'AXIS',
      'placeholder' => '۰ تا ۱۸۰',
      'min' => 0,
      'max' => 180,
      'step' => 1,
    ),
    8 => 
    array (
      'name' => 'od_add',
      'type' => 'number',
      'label' => 'ADD',
      'placeholder' => 'در صورت پیرچشمی',
      'step' => '0.25',
    ),
    9 => 
    array (
      'name' => 'os_block',
      'type' => 'heading',
      'label' => 'چشم چپ (OS)',
    ),
    10 => 
    array (
      'name' => 'os_sph',
      'type' => 'number',
      'label' => 'SPH',
      'placeholder' => 'مثلاً -۱.۷۵',
      'step' => '0.25',
      'required' => true,
    ),
    11 => 
    array (
      'name' => 'os_cyl',
      'type' => 'number',
      'label' => 'CYL',
      'placeholder' => 'مثلاً -۰.۵۰',
      'step' => '0.25',
    ),
    12 => 
    array (
      'name' => 'os_axis',
      'type' => 'number',
      'label' => 'AXIS',
      'placeholder' => '۰ تا ۱۸۰',
      'min' => 0,
      'max' => 180,
      'step' => 1,
    ),
    13 => 
    array (
      'name' => 'os_add',
      'type' => 'number',
      'label' => 'ADD',
      'placeholder' => 'در صورت پیرچشمی',
      'step' => '0.25',
    ),
    14 => 
    array (
      'name' => 'pd_block',
      'type' => 'heading',
      'label' => 'فاصله مردمکی (PD)',
    ),
    15 => 
    array (
      'name' => 'pd_type',
      'type' => 'select',
      'label' => 'نوع PD',
      'required' => true,
      'options' => 
      array (
        0 => 
        array (
          'value' => 'binocular',
          'label' => 'دوچشمی (یک عدد)',
        ),
        1 => 
        array (
          'value' => 'monocular',
          'label' => 'تک‌چشمی (راست / چپ)',
        ),
      ),
    ),
    16 => 
    array (
      'name' => 'pd',
      'type' => 'number',
      'label' => 'PD دوچشمی',
      'placeholder' => 'مثلاً ۶۲',
      'min' => 40,
      'max' => 80,
      'step' => '0.5',
    ),
    17 => 
    array (
      'name' => 'pd_od',
      'type' => 'number',
      'label' => 'PD راست',
      'placeholder' => 'مثلاً ۳۱',
      'step' => '0.5',
    ),
    18 => 
    array (
      'name' => 'pd_os',
      'type' => 'number',
      'label' => 'PD چپ',
      'placeholder' => 'مثلاً ۳۱',
      'step' => '0.5',
    ),
    19 => 
    array (
      'name' => 'use_type',
      'type' => 'select',
      'label' => 'کاربرد عینک',
      'required' => true,
      'options' => 
      array (
        0 => 
        array (
          'value' => 'distance',
          'label' => 'دور',
        ),
        1 => 
        array (
          'value' => 'near',
          'label' => 'نزدیک / مطالعه',
        ),
        2 => 
        array (
          'value' => 'computer',
          'label' => 'کامپیوتر / میانی',
        ),
        3 => 
        array (
          'value' => 'progressive',
          'label' => 'تدریجی (پروگرسیو)',
        ),
        4 => 
        array (
          'value' => 'bifocal',
          'label' => 'دوکانونی',
        ),
      ),
    ),
    20 => 
    array (
      'name' => 'lens_index',
      'type' => 'select',
      'label' => 'ضریب شکست عدسی (در صورت مشخص بودن)',
      'options' => 
      array (
        0 => 
        array (
          'value' => '',
          'label' => 'مهم نیست / بعداً',
        ),
        1 => 
        array (
          'value' => '1.5',
          'label' => '۱.۵۰ استاندارد',
        ),
        2 => 
        array (
          'value' => '1.56',
          'label' => '۱.۵۶',
        ),
        3 => 
        array (
          'value' => '1.6',
          'label' => '۱.۶۰ نازک',
        ),
        4 => 
        array (
          'value' => '1.67',
          'label' => '۱.۶۷ فوق نازک',
        ),
        5 => 
        array (
          'value' => '1.74',
          'label' => '۱.۷۴ بسیار نازک',
        ),
      ),
    ),
    21 => 
    array (
      'name' => 'rx_file',
      'type' => 'upload',
      'label' => 'آپلود تصویر نسخه (اختیاری)',
    ),
    22 => 
    array (
      'name' => 'prescription_note',
      'type' => 'textarea',
      'label' => 'یادداشت برای اپتومتریست',
      'placeholder' => 'مثلاً ترجیح پوشش ضدبازتاب، حساسیت به نور، …',
    ),
  ),
  'is_preset' => 1,
  'preset_slug' => 'glasses-prescription',
  'updated_at' => '2026-09-19 15:50:42',
);
