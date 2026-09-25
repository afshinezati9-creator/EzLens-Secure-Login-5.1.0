<?php
/**
 * EzLens Product Options palette #14
 * Meta + fields. Raw editor code also in 14-d8b9db8cd986daa9-d8a2d981d8aad8a7d8a8db8c.code.html
 */
if (!defined('ABSPATH')) { exit; }

return array (
  'id' => 14,
  'title' => 'عینک آفتابی',
  'description' => 'انتخاب لنز آفتابی، UV و در صورت نیاز نمره — مناسب فروشگاه عینک آفتابی.',
  'status' => 'active',
  'fields' => 
  array (
    0 => 
    array (
      'name' => 'sg_intro',
      'type' => 'heading',
      'label' => 'عینک آفتابی',
      'placeholder' => '',
      'description' => '',
      'required' => false,
      'price' => 0,
      'width' => 'full',
      'settings' => 
      array (
        'level' => 'h3',
      ),
      'options' => 
      array (
      ),
      'children' => 
      array (
      ),
      'conditions' => 
      array (
        'logic' => 'all',
        'rules' => 
        array (
        ),
      ),
    ),
    1 => 
    array (
      'name' => 'sg_hint',
      'type' => 'html',
      'label' => 'راهنما',
      'content' => '<p style="margin:0 0 12px;color:#64748b;font-size:13px;line-height:1.7;">اگر عینک آفتابی <strong>نمره‌دار</strong> می‌خواهید، بخش نسخه را پر کنید؛ در غیر این صورت فقط ظاهر و لنز را انتخاب کنید.</p>',
      'placeholder' => '',
      'description' => '',
      'required' => false,
      'price' => 0,
      'width' => 'full',
      'settings' => 
      array (
        'code' => '',
      ),
      'options' => 
      array (
      ),
      'children' => 
      array (
      ),
      'conditions' => 
      array (
        'logic' => 'all',
        'rules' => 
        array (
        ),
      ),
    ),
    2 => 
    array (
      'name' => 'sg_mode',
      'type' => 'select',
      'label' => 'نوع سفارش',
      'required' => true,
      'options' => 
      array (
        0 => 
        array (
          'label' => 'بدون نمره (فقط آفتابی)',
          'value' => 'plano',
          'price' => 0,
        ),
        1 => 
        array (
          'label' => 'نمره‌دار (با نسخه)',
          'value' => 'rx',
          'price' => 0,
        ),
      ),
      'placeholder' => '',
      'description' => '',
      'price' => 0,
      'width' => 'full',
      'settings' => 
      array (
      ),
      'children' => 
      array (
      ),
      'conditions' => 
      array (
        'logic' => 'all',
        'rules' => 
        array (
        ),
      ),
    ),
    3 => 
    array (
      'name' => 'lens_tint',
      'type' => 'select',
      'label' => 'رنگ / نوع لنز',
      'required' => true,
      'options' => 
      array (
        0 => 
        array (
          'label' => 'خاکستری کلاسیک',
          'value' => 'gray',
          'price' => 0,
        ),
        1 => 
        array (
          'label' => 'قهوه‌ای',
          'value' => 'brown',
          'price' => 0,
        ),
        2 => 
        array (
          'label' => 'سبز',
          'value' => 'green',
          'price' => 0,
        ),
        3 => 
        array (
          'label' => 'پولاریزه',
          'value' => 'polarized',
          'price' => 0,
        ),
        4 => 
        array (
          'label' => 'فتوکرومیک (تغییر با نور)',
          'value' => 'photochromic',
          'price' => 0,
        ),
        5 => 
        array (
          'label' => 'آینه‌ای',
          'value' => 'mirror',
          'price' => 0,
        ),
      ),
      'placeholder' => '',
      'description' => '',
      'price' => 0,
      'width' => 'full',
      'settings' => 
      array (
      ),
      'children' => 
      array (
      ),
      'conditions' => 
      array (
        'logic' => 'all',
        'rules' => 
        array (
        ),
      ),
    ),
    4 => 
    array (
      'name' => 'uv_class',
      'type' => 'select',
      'label' => 'حفاظت UV',
      'options' => 
      array (
        0 => 
        array (
          'label' => 'UV400 (استاندارد)',
          'value' => 'uv400',
          'price' => 0,
        ),
        1 => 
        array (
          'label' => 'دسته ۳ — آفتابی روز',
          'value' => 'cat3',
          'price' => 0,
        ),
        2 => 
        array (
          'label' => 'دسته ۴ — کوه/برف (رانندگی نه)',
          'value' => 'cat4',
          'price' => 0,
        ),
      ),
      'placeholder' => '',
      'description' => '',
      'required' => false,
      'price' => 0,
      'width' => 'full',
      'settings' => 
      array (
      ),
      'children' => 
      array (
      ),
      'conditions' => 
      array (
        'logic' => 'all',
        'rules' => 
        array (
        ),
      ),
    ),
    5 => 
    array (
      'name' => 'frame_size',
      'type' => 'select',
      'label' => 'سایز قاب (در صورت مشخص بودن)',
      'options' => 
      array (
        0 => 
        array (
          'label' => 'کوچک',
          'value' => 's',
          'price' => 0,
        ),
        1 => 
        array (
          'label' => 'متوسط',
          'value' => 'm',
          'price' => 0,
        ),
        2 => 
        array (
          'label' => 'بزرگ',
          'value' => 'l',
          'price' => 0,
        ),
      ),
      'placeholder' => '',
      'description' => '',
      'required' => false,
      'price' => 0,
      'width' => 'full',
      'settings' => 
      array (
      ),
      'children' => 
      array (
      ),
      'conditions' => 
      array (
        'logic' => 'all',
        'rules' => 
        array (
        ),
      ),
    ),
    6 => 
    array (
      'name' => 'od_sph',
      'type' => 'number',
      'label' => 'SPH راست (اگر نمره‌دار)',
      'placeholder' => 'اختیاری',
      'step' => '0.25',
      'description' => '',
      'required' => false,
      'price' => 0,
      'width' => 'full',
      'settings' => 
      array (
        'min' => '',
        'max' => '',
        'step' => '1',
      ),
      'options' => 
      array (
      ),
      'children' => 
      array (
      ),
      'conditions' => 
      array (
        'logic' => 'all',
        'rules' => 
        array (
        ),
      ),
    ),
    7 => 
    array (
      'name' => 'os_sph',
      'type' => 'number',
      'label' => 'SPH چپ (اگر نمره‌دار)',
      'placeholder' => 'اختیاری',
      'step' => '0.25',
      'description' => '',
      'required' => false,
      'price' => 0,
      'width' => 'full',
      'settings' => 
      array (
        'min' => '',
        'max' => '',
        'step' => '1',
      ),
      'options' => 
      array (
      ),
      'children' => 
      array (
      ),
      'conditions' => 
      array (
        'logic' => 'all',
        'rules' => 
        array (
        ),
      ),
    ),
    8 => 
    array (
      'name' => 'pd',
      'type' => 'number',
      'label' => 'PD',
      'placeholder' => 'برای نمره‌دار توصیه می‌شود',
      'step' => '0.5',
      'description' => '',
      'required' => false,
      'price' => 0,
      'width' => 'full',
      'settings' => 
      array (
        'min' => '',
        'max' => '',
        'step' => '1',
      ),
      'options' => 
      array (
      ),
      'children' => 
      array (
      ),
      'conditions' => 
      array (
        'logic' => 'all',
        'rules' => 
        array (
        ),
      ),
    ),
    9 => 
    array (
      'name' => 'sg_note',
      'type' => 'textarea',
      'label' => 'توضیحات',
      'placeholder' => 'ترجیح فریم، برند، …',
      'description' => '',
      'required' => false,
      'price' => 0,
      'width' => 'full',
      'settings' => 
      array (
        'rows' => 3,
      ),
      'options' => 
      array (
      ),
      'children' => 
      array (
      ),
      'conditions' => 
      array (
        'logic' => 'all',
        'rules' => 
        array (
        ),
      ),
    ),
    '_code_html' => 'undefined',
    '_code_css' => '',
    '_code_js' => '',
  ),
  'code' => 'undefined',
  'code_file' => '14-d8b9db8cd986daa9-d8a2d981d8aad8a7d8a8db8c.code.html',
  'is_preset' => 0,
  'preset_slug' => '',
  'updated_at' => '2026-09-19 16:31:20',
);
