<?php
/**
 * EzLens Product Options palette #15
 * Meta + fields. Raw editor code also in 15-d986d8b3d8aed987-d8b9db8cd986daa9-d8b7d8a8db8c.code.html
 */
if (!defined('ABSPATH')) { exit; }

return array (
  'id' => 15,
  'title' => 'نسخه عینک طبی',
  'description' => 'فرم حرفه‌ای ثبت SPH/CYL/AXIS و PD برای سفارش عینک طبی — مناسب مشتری و بررسی توسط بینایی‌سنج.',
  'status' => 'active',
  'fields' => 
  array (
    0 => 
    array (
      'name' => 'rx_intro',
      'type' => 'heading',
      'label' => 'نسخه عینک طبی',
      'placeholder' => '',
      'description' => '',
      'required' => false,
      'price' => 0,
      '_builder_key' => '0',
    ),
    1 => 
    array (
      'name' => 'rx_hint',
      'type' => 'html',
      'label' => 'راهنما',
      'content' => '<p style="margin:0 0 12px;color:#64748b;font-size:13px;line-height:1.7;">اعداد را دقیقاً مطابق برگه پزشک وارد کنید. SPH نزدیک‌بینی منفی و دوربینی مثبت است. AXIS فقط وقتی CYL دارید معنا دارد.</p>',
      'placeholder' => '',
      'description' => '',
      'required' => false,
      'price' => 0,
      '_builder_key' => '1',
    ),
    2 => 
    array (
      'name' => 'doctor_name',
      'type' => 'text',
      'label' => 'نام پزشک / مرکز',
      'placeholder' => 'اختیاری — مثلاً دکتر …',
      'description' => '',
      'required' => false,
      'price' => 0,
      '_builder_key' => '2',
    ),
    3 => 
    array (
      'name' => 'rx_date',
      'type' => 'date',
      'label' => 'تاریخ نسخه',
      'placeholder' => '',
      'description' => '',
      'required' => false,
      'price' => 0,
      '_builder_key' => '3',
    ),
    4 => 
    array (
      'name' => 'od_block',
      'type' => 'heading',
      'label' => 'چشم راست (OD)',
      'placeholder' => '',
      'description' => '',
      'required' => false,
      'price' => 0,
      '_builder_key' => '4',
    ),
    5 => 
    array (
      'name' => 'od_sph',
      'type' => 'number',
      'label' => 'SPH',
      'placeholder' => 'مثلاً -۲.۵۰',
      'step' => '0.25',
      'required' => true,
      'description' => '',
      'price' => 0,
      '_builder_key' => '5',
    ),
    6 => 
    array (
      'name' => 'od_cyl',
      'type' => 'number',
      'label' => 'CYL',
      'placeholder' => 'مثلاً -۰.۷۵',
      'step' => '0.25',
      'description' => '',
      'required' => false,
      'price' => 0,
      '_builder_key' => '6',
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
      'description' => '',
      'required' => false,
      'price' => 0,
      '_builder_key' => '7',
    ),
    8 => 
    array (
      'name' => 'od_add',
      'type' => 'number',
      'label' => 'ADD',
      'placeholder' => 'در صورت پیرچشمی',
      'step' => '0.25',
      'description' => '',
      'required' => false,
      'price' => 0,
      '_builder_key' => '8',
    ),
    9 => 
    array (
      'name' => 'os_block',
      'type' => 'heading',
      'label' => 'چشم چپ (OS)',
      'placeholder' => '',
      'description' => '',
      'required' => false,
      'price' => 0,
      '_builder_key' => '9',
    ),
    10 => 
    array (
      'name' => 'os_sph',
      'type' => 'number',
      'label' => 'SPH',
      'placeholder' => 'مثلاً -۱.۷۵',
      'step' => '0.25',
      'required' => true,
      'description' => '',
      'price' => 0,
      '_builder_key' => '10',
    ),
    11 => 
    array (
      'name' => 'os_cyl',
      'type' => 'number',
      'label' => 'CYL',
      'placeholder' => 'مثلاً -۰.۵۰',
      'step' => '0.25',
      'description' => '',
      'required' => false,
      'price' => 0,
      '_builder_key' => '11',
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
      'description' => '',
      'required' => false,
      'price' => 0,
      '_builder_key' => '12',
    ),
    13 => 
    array (
      'name' => 'os_add',
      'type' => 'number',
      'label' => 'ADD',
      'placeholder' => 'در صورت پیرچشمی',
      'step' => '0.25',
      'description' => '',
      'required' => false,
      'price' => 0,
      '_builder_key' => '13',
    ),
    14 => 
    array (
      'name' => 'pd_block',
      'type' => 'heading',
      'label' => 'فاصله مردمکی (PD)',
      'placeholder' => '',
      'description' => '',
      'required' => false,
      'price' => 0,
      '_builder_key' => '14',
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
          'price' => 0,
        ),
        1 => 
        array (
          'value' => 'monocular',
          'label' => 'تک‌چشمی (راست / چپ)',
          'price' => 0,
        ),
      ),
      'placeholder' => '',
      'description' => '',
      'price' => 0,
      '_builder_key' => '15',
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
      'description' => '',
      'required' => false,
      'price' => 0,
      '_builder_key' => '16',
    ),
    17 => 
    array (
      'name' => 'pd_od',
      'type' => 'number',
      'label' => 'PD راست',
      'placeholder' => 'مثلاً ۳۱',
      'step' => '0.5',
      'description' => '',
      'required' => false,
      'price' => 0,
      '_builder_key' => '17',
    ),
    18 => 
    array (
      'name' => 'pd_os',
      'type' => 'number',
      'label' => 'PD چپ',
      'placeholder' => 'مثلاً ۳۱',
      'step' => '0.5',
      'description' => '',
      'required' => false,
      'price' => 0,
      '_builder_key' => '18',
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
          'price' => 0,
        ),
        1 => 
        array (
          'value' => 'near',
          'label' => 'نزدیک / مطالعه',
          'price' => 0,
        ),
        2 => 
        array (
          'value' => 'computer',
          'label' => 'کامپیوتر / میانی',
          'price' => 0,
        ),
        3 => 
        array (
          'value' => 'progressive',
          'label' => 'تدریجی (پروگرسیو)',
          'price' => 0,
        ),
        4 => 
        array (
          'value' => 'bifocal',
          'label' => 'دوکانونی',
          'price' => 0,
        ),
      ),
      'placeholder' => '',
      'description' => '',
      'price' => 0,
      '_builder_key' => '19',
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
          'value' => '1.5',
          'label' => '۱.۵۰ استاندارد',
          'price' => 0,
        ),
        1 => 
        array (
          'value' => '1.56',
          'label' => '۱.۵۶',
          'price' => 0,
        ),
        2 => 
        array (
          'value' => '1.6',
          'label' => '۱.۶۰ نازک',
          'price' => 0,
        ),
        3 => 
        array (
          'value' => '1.67',
          'label' => '۱.۶۷ فوق نازک',
          'price' => 0,
        ),
        4 => 
        array (
          'value' => '1.74',
          'label' => '۱.۷۴ بسیار نازک',
          'price' => 0,
        ),
      ),
      'placeholder' => '',
      'description' => '',
      'required' => false,
      'price' => 0,
      '_builder_key' => '20',
    ),
    21 => 
    array (
      'name' => 'rx_file',
      'type' => 'upload',
      'label' => 'آپلود تصویر نسخه (اختیاری)',
      'placeholder' => '',
      'description' => '',
      'required' => false,
      'price' => 0,
      '_builder_key' => '21',
    ),
    22 => 
    array (
      'name' => 'prescription_note',
      'type' => 'textarea',
      'label' => 'یادداشت برای اپتومتریست',
      'placeholder' => 'مثلاً ترجیح پوشش ضدبازتاب، حساسیت به نور، …',
      'description' => '',
      'required' => false,
      'price' => 0,
      '_builder_key' => '22',
    ),
    '_code_html' => 'undefi<!-- ============================================================
     EZLENS — Prescription Form Pro (نسخه عینک طبی)
     ============================================================ -->

<div class="ez-po-wrap" id="ez-po-wrap">

  <!-- ═══════════════ HEADER ═══════════════ -->
  <div class="ez-po-header">
    <div class="ez-po-header-icon">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
        <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7z"/>
        <circle cx="12" cy="12" r="3"/>
      </svg>
    </div>
    <div class="ez-po-header-text">
      <h2>نسخه عینک طبی</h2>
      <p>اطلاعات نسخه‌ات رو وارد کن یا عکسش رو آپلود کن</p>
    </div>
  </div>

  <!-- ═══════════════ BASIC INFO ═══════════════ -->
  <div class="ez-po-section">
    <h3 class="ez-po-heading">اطلاعات پایه</h3>
    <div class="ez-po-grid">
      <div class="ez-po-field" data-type="text">
        <label for="doctor_name" data-hint="اگر نسخه رو پزشک خاصی نوشته، اینجا وارد کن (اختیاری)">
          <span class="ez-po-label-text">نام پزشک / مرکز</span>
          <span class="ez-po-hint-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
              <circle cx="12" cy="12" r="10"/>
              <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/>
              <line x1="12" y1="17" x2="12.01" y2="17"/>
            </svg>
          </span>
        </label>
        <div class="ez-po-input-wrap">
          <span class="ez-po-input-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
              <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
              <circle cx="12" cy="7" r="4"/>
            </svg>
          </span>
          <input type="text" id="doctor_name" name="doctor_name" placeholder="مثلاً دکتر رضایی" autocomplete="off">
          <span class="ez-po-status"></span>
        </div>
      </div>

      <div class="ez-po-field" data-type="date">
        <label for="rx_date" data-hint="تاریخی که پزشک نسخه رو نوشته — معمولاً نسخه‌ها ۶ ماه اعتبار دارن">
          <span class="ez-po-label-text">تاریخ نسخه</span>
          <span class="ez-po-hint-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
              <circle cx="12" cy="12" r="10"/>
              <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/>
              <line x1="12" y1="17" x2="12.01" y2="17"/>
            </svg>
          </span>
        </label>
        <div class="ez-po-input-wrap">
          <span class="ez-po-input-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
              <rect x="3" y="4" width="18" height="18" rx="2"/>
              <line x1="16" y1="2" x2="16" y2="6"/>
              <line x1="8" y1="2" x2="8" y2="6"/>
              <line x1="3" y1="10" x2="21" y2="10"/>
            </svg>
          </span>
          <input type="date" id="rx_date" name="rx_date" placeholder="">
          <span class="ez-po-status"></span>
        </div>
      </div>
    </div>
  </div>

  <!-- ═══════════════ OD SECTION ═══════════════ -->
  <div class="ez-po-section ez-po-section-od">
    <h3 class="ez-po-heading">
      <span class="ez-po-heading-icon ez-po-heading-icon-od">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
          <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7z"/>
          <circle cx="12" cy="12" r="3"/>
        </svg>
      </span>
      <span>چشم راست (OD)</span>
    </h3>
    <div class="ez-po-grid ez-po-grid-4">
      <div class="ez-po-field" data-type="number">
        <label for="od_sph" data-hint="شماره نمره چشم راست — روی نسخه معمولاً سمت راست نوشته می‌شه">
          <span class="ez-po-label-text">SPH</span>
          <span class="ez-po-hint-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
              <circle cx="12" cy="12" r="10"/>
              <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/>
              <line x1="12" y1="17" x2="12.01" y2="17"/>
            </svg>
          </span>
        </label>
        <div class="ez-po-input-wrap ez-po-input-num">
          <input type="number" id="od_sph" name="od_sph" placeholder="-۲.۵۰" step="0.25" required>
          <span class="ez-po-status"></span>
        </div>
      </div>
      <div class="ez-po-field" data-type="number">
        <label for="od_cyl" data-hint="میزان آستیگماتیسم چشم راست — عددی منفی مثل -۰.۷۵">
          <span class="ez-po-label-text">CYL</span>
          <span class="ez-po-hint-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
              <circle cx="12" cy="12" r="10"/>
              <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/>
              <line x1="12" y1="17" x2="12.01" y2="17"/>
            </svg>
          </span>
        </label>
        <div class="ez-po-input-wrap ez-po-input-num">
          <input type="number" id="od_cyl" name="od_cyl" placeholder="-۰.۷۵" step="0.25">
          <span class="ez-po-status"></span>
        </div>
      </div>
      <div class="ez-po-field" data-type="number">
        <label for="od_axis" data-hint="زاویه آستیگماتیسم بین ۰ تا ۱۸۰ درجه">
          <span class="ez-po-label-text">AXIS</span>
          <span class="ez-po-hint-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
              <circle cx="12" cy="12" r="10"/>
              <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/>
              <line x1="12" y1="17" x2="12.01" y2="17"/>
            </svg>
          </span>
        </label>
        <div class="ez-po-input-wrap ez-po-input-num">
          <input type="number" id="od_axis" name="od_axis" placeholder="۰ تا ۱۸۰" min="0" max="180">
          <span class="ez-po-status"></span>
        </div>
      </div>
      <div class="ez-po-field" data-type="number">
        <label for="od_add" data-hint="فقط برای افراد بالای ۴۰ سال که دچار پیرچشمی شدن">
          <span class="ez-po-label-text">ADD</span>
          <span class="ez-po-hint-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
              <circle cx="12" cy="12" r="10"/>
              <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/>
              <line x1="12" y1="17" x2="12.01" y2="17"/>
            </svg>
          </span>
        </label>
        <div class="ez-po-input-wrap ez-po-input-num">
          <input type="number" id="od_add" name="od_add" placeholder="در صورت پیرچشمی" step="0.25">
          <span class="ez-po-status"></span>
        </div>
      </div>
    </div>
  </div>

  <!-- ═══════════════ OS SECTION ═══════════════ -->
  <div class="ez-po-section ez-po-section-os">
    <h3 class="ez-po-heading">
      <span class="ez-po-heading-icon ez-po-heading-icon-os">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
          <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7z"/>
          <circle cx="12" cy="12" r="3"/>
        </svg>
      </span>
      <span>چشم چپ (OS)</span>
    </h3>
    <div class="ez-po-grid ez-po-grid-4">
      <div class="ez-po-field" data-type="number">
        <label for="os_sph" data-hint="شماره نمره چشم چپ">
          <span class="ez-po-label-text">SPH</span>
          <span class="ez-po-hint-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
              <circle cx="12" cy="12" r="10"/>
              <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/>
              <line x1="12" y1="17" x2="12.01" y2="17"/>
            </svg>
          </span>
        </label>
        <div class="ez-po-input-wrap ez-po-input-num">
          <input type="number" id="os_sph" name="os_sph" placeholder="-۱.۷۵" step="0.25" required>
          <span class="ez-po-status"></span>
        </div>
      </div>
      <div class="ez-po-field" data-type="number">
        <label for="os_cyl" data-hint="میزان آستیگماتیسم چشم چپ">
          <span class="ez-po-label-text">CYL</span>
          <span class="ez-po-hint-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
              <circle cx="12" cy="12" r="10"/>
              <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/>
              <line x1="12" y1="17" x2="12.01" y2="17"/>
            </svg>
          </span>
        </label>
        <div class="ez-po-input-wrap ez-po-input-num">
          <input type="number" id="os_cyl" name="os_cyl" placeholder="-۰.۵۰" step="0.25">
          <span class="ez-po-status"></span>
        </div>
      </div>
      <div class="ez-po-field" data-type="number">
        <label for="os_axis" data-hint="زاویه آستیگماتیسم چشم چپ بین ۰ تا ۱۸۰ درجه">
          <span class="ez-po-label-text">AXIS</span>
          <span class="ez-po-hint-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
              <circle cx="12" cy="12" r="10"/>
              <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/>
              <line x1="12" y1="17" x2="12.01" y2="17"/>
            </svg>
          </span>
        </label>
        <div class="ez-po-input-wrap ez-po-input-num">
          <input type="number" id="os_axis" name="os_axis" placeholder="۰ تا ۱۸۰" min="0" max="180">
          <span class="ez-po-status"></span>
        </div>
      </div>
      <div class="ez-po-field" data-type="number">
        <label for="os_add" data-hint="فقط برای افراد بالای ۴۰ سال که دچار پیرچشمی شدن">
          <span class="ez-po-label-text">ADD</span>
          <span class="ez-po-hint-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
              <circle cx="12" cy="12" r="10"/>
              <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/>
              <line x1="12" y1="17" x2="12.01" y2="17"/>
            </svg>
          </span>
        </label>
        <div class="ez-po-input-wrap ez-po-input-num">
          <input type="number" id="os_add" name="os_add" placeholder="در صورت پیرچشمی" step="0.25">
          <span class="ez-po-status"></span>
        </div>
      </div>
    </div>
  </div>

  <!-- ═══════════════ PD SECTION ═══════════════ -->
  <div class="ez-po-section ez-po-section-pd">
    <h3 class="ez-po-heading">
      <span class="ez-po-heading-icon ez-po-heading-icon-pd">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
          <circle cx="6" cy="12" r="3"/>
          <circle cx="18" cy="12" r="3"/>
          <line x1="9" y1="12" x2="15" y2="12"/>
        </svg>
      </span>
      <span>فاصله مردمکی (PD)</span>
    </h3>
    <div class="ez-po-grid">
      <div class="ez-po-field" data-type="select">
        <label for="pd_type" data-hint="اگه یه عدد روی نسخه نوشته شده، دوچشمی رو انتخاب کن. اگه دو عدد جدا، تک‌چشمی">
          <span class="ez-po-label-text">نوع PD</span>
          <span class="ez-po-hint-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
              <circle cx="12" cy="12" r="10"/>
              <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/>
              <line x1="12" y1="17" x2="12.01" y2="17"/>
            </svg>
          </span>
        </label>
        <div class="ez-po-input-wrap">
          <span class="ez-po-input-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
              <polyline points="6 9 12 15 18 9"/>
            </svg>
          </span>
          <select id="pd_type" name="pd_type" required>
            <option value="binocular">دوچشمی (یک عدد)</option>
            <option value="monocular">تک‌چشمی (راست / چپ)</option>
          </select>
          <span class="ez-po-status"></span>
        </div>
      </div>

      <div class="ez-po-field" data-type="number" data-pd-mode="binocular">
        <label for="pd" data-hint="فاصله بین دو مردمک — معمولاً بین ۵۸ تا ۷۰ میلی‌متر">
          <span class="ez-po-label-text">PD دوچشمی</span>
          <span class="ez-po-hint-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
              <circle cx="12" cy="12" r="10"/>
              <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/>
              <line x1="12" y1="17" x2="12.01" y2="17"/>
            </svg>
          </span>
        </label>
        <div class="ez-po-input-wrap ez-po-input-num">
          <input type="number" id="pd" name="pd" placeholder="۶۲" min="40" max="90">
          <span class="ez-po-status"></span>
        </div>
      </div>

      <div class="ez-po-field" data-type="number" data-pd-mode="monocular">
        <label for="pd_od" data-hint="فاصله مردمک چشم راست از مرکز بینی">
          <span class="ez-po-label-text">PD راست</span>
          <span class="ez-po-hint-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
              <circle cx="12" cy="12" r="10"/>
              <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/>
              <line x1="12" y1="17" x2="12.01" y2="17"/>
            </svg>
          </span>
        </label>
        <div class="ez-po-input-wrap ez-po-input-num">
          <input type="number" id="pd_od" name="pd_od" placeholder="۳۱" min="20" max="50">
          <span class="ez-po-status"></span>
        </div>
      </div>

      <div class="ez-po-field" data-type="number" data-pd-mode="monocular">
        <label for="pd_os" data-hint="فاصله مردمک چشم چپ از مرکز بینی">
          <span class="ez-po-label-text">PD چپ</span>
          <span class="ez-po-hint-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
              <circle cx="12" cy="12" r="10"/>
              <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/>
              <line x1="12" y1="17" x2="12.01" y2="17"/>
            </svg>
          </span>
        </label>
        <div class="ez-po-input-wrap ez-po-input-num">
          <input type="number" id="pd_os" name="pd_os" placeholder="۳۱" min="20" max="50">
          <span class="ez-po-status"></span>
        </div>
      </div>
    </div>
  </div>

  <!-- ═══════════════ LENS SETTINGS ═══════════════ -->
  <div class="ez-po-section ez-po-section-lens">
    <h3 class="ez-po-heading">
      <span class="ez-po-heading-icon ez-po-heading-icon-lens">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
          <circle cx="12" cy="12" r="9"/>
          <path d="M3 12h18"/>
        </svg>
      </span>
      <span>تنظیمات عدسی</span>
    </h3>
    <div class="ez-po-grid">
      <div class="ez-po-field" data-type="select">
        <label for="use_type" data-hint="عدسی دور برای رانندگی، نزدیک برای مطالعه، تدریجی برای هر سه فاصله">
          <span class="ez-po-label-text">کاربرد عینک</span>
          <span class="ez-po-hint-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
              <circle cx="12" cy="12" r="10"/>
              <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/>
              <line x1="12" y1="17" x2="12.01" y2="17"/>
            </svg>
          </span>
        </label>
        <div class="ez-po-input-wrap">
          <span class="ez-po-input-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
              <polyline points="6 9 12 15 18 9"/>
            </svg>
          </span>
          <select id="use_type" name="use_type" required>
            <option value="distance">دور</option>
            <option value="near">نزدیک / مطالعه</option>
            <option value="computer">کامپیوتر / میانی</option>
            <option value="progressive">تدریجی (پروگرسیو)</option>
            <option value="bifocal">دوکانونی</option>
          </select>
          <span class="ez-po-status"></span>
        </div>
      </div>

      <div class="ez-po-field" data-type="select">
        <label for="lens_index" data-hint="هرچی بالاتر، عدسی نازک‌تر و سبک‌تر — برای نمره‌های بالا ۱.۶۷ یا ۱.۷۴ بهتره">
          <span class="ez-po-label-text">ضریب شکست عدسی</span>
          <span class="ez-po-hint-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
              <circle cx="12" cy="12" r="10"/>
              <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/>
              <line x1="12" y1="17" x2="12.01" y2="17"/>
            </svg>
          </span>
        </label>
        <div class="ez-po-input-wrap">
          <span class="ez-po-input-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
              <polyline points="6 9 12 15 18 9"/>
            </svg>
          </span>
          <select id="lens_index" name="lens_index">
            <option value="1.5">۱.۵۰ استاندارد</option>
            <option value="1.56">۱.۵۶</option>
            <option value="1.6">۱.۶۰ نازک</option>
            <option value="1.67">۱.۶۷ فوق نازک</option>
            <option value="1.74">۱.۷۴ بسیار نازک</option>
          </select>
          <span class="ez-po-status"></span>
        </div>
      </div>
    </div>
  </div>

  <!-- ═══════════════ UPLOAD BOX ═══════════════ -->
  <div class="ez-po-section">
    <h3 class="ez-po-heading">
      <span class="ez-po-heading-icon ez-po-heading-icon-upload">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
          <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
          <polyline points="17 8 12 3 7 8"/>
          <line x1="12" y1="3" x2="12" y2="15"/>
        </svg>
      </span>
      <span>آپلود تصویر نسخه</span>
      <span class="ez-po-optional">اختیاری</span>
    </h3>

    <div class="ez-po-upload-field">
      <div class="ez-po-upload" id="rx_upload" data-dragover="false">
        <input type="file" id="rx_file" name="rx_file" accept="image/*,.pdf" hidden>

        <!-- Empty State -->
        <div class="ez-po-upload-empty" id="rx_upload_empty">
          <div class="ez-po-upload-icon-wrap">
            <div class="ez-po-upload-icon-ring"></div>
            <svg class="ez-po-upload-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
              <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
              <polyline points="17 8 12 3 7 8"/>
              <line x1="12" y1="3" x2="12" y2="15"/>
            </svg>
          </div>
          <div class="ez-po-upload-title">فایل را اینجا بکشید</div>
          <div class="ez-po-upload-sub">یا <span class="ez-po-upload-link">انتخاب فایل</span> از دستگاه</div>
          <div class="ez-po-upload-formats">
            <span class="ez-po-format-badge">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                <rect x="3" y="3" width="18" height="18" rx="2"/>
                <circle cx="8.5" cy="8.5" r="1.5"/>
                <polyline points="21 15 16 10 5 21"/>
              </svg>
              JPG, PNG
            </span>
            <span class="ez-po-format-badge">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                <polyline points="14 2 14 8 20 8"/>
              </svg>
              PDF
            </span>
            <span class="ez-po-format-badge">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"/>
                <polyline points="12 6 12 12 16 14"/>
              </svg>
              حداکثر ۵MB
            </span>
          </div>
        </div>

        <!-- Preview State -->
        <div class="ez-po-upload-preview" id="rx_upload_preview">
          <div class="ez-po-preview-thumb" id="rx_preview_thumb">
            <!-- filled via JS -->
          </div>
          <div class="ez-po-preview-info">
            <div class="ez-po-preview-name" id="rx_preview_name">—</div>
            <div class="ez-po-preview-size" id="rx_preview_size">—</div>
          </div>
          <button type="button" class="ez-po-preview-remove" id="rx_preview_remove" aria-label="حذف فایل">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
              <line x1="18" y1="6" x2="6" y2="18"/>
              <line x1="6" y1="6" x2="18" y2="18"/>
            </svg>
          </button>
        </div>

        <!-- Progress -->
        <div class="ez-po-upload-progress" id="rx_upload_progress">
          <div class="ez-po-upload-progress-bar" id="rx_upload_progress_bar"></div>
        </div>
      </div>

      <div class="ez-po-upload-note">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
          <circle cx="12" cy="12" r="10"/>
          <line x1="12" y1="16" x2="12" y2="12"/>
          <line x1="12" y1="8" x2="12.01" y2="8"/>
        </svg>
        <span>تصویر نسخه باید واضح و خوانا باشه تا اپتومتریست بتونه بررسی کنه</span>
      </div>
    </div>
  </div>

  <!-- ═══════════════ NOTES ═══════════════ -->
  <div class="ez-po-section">
    <h3 class="ez-po-heading">
      <span class="ez-po-heading-icon ez-po-heading-icon-note">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
          <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
        </svg>
      </span>
      <span>یادداشت برای اپتومتریست</span>
    </h3>

    <div class="ez-po-field" data-type="textarea">
      <label for="prescription_note" data-hint="هر توضیح اضافه‌ای که فکر می‌کنی مهمه — حساسیت به نور، ترجیح برند و...">
        <span class="ez-po-label-text">توضیحات شما</span>
        <span class="ez-po-hint-icon">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="12" cy="12" r="10"/>
            <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/>
            <line x1="12" y1="17" x2="12.01" y2="17"/>
          </svg>
        </span>
      </label>
      <div class="ez-po-input-wrap ez-po-textarea-wrap">
        <span class="ez-po-input-icon">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
            <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
          </svg>
        </span>
        <textarea id="prescription_note" name="prescription_note" rows="4" placeholder="مثلاً ترجیح پوشش ضدبازتاب، حساسیت به نور، …"></textarea>
        <span class="ez-po-status"></span>
      </div>
    </div>
  </div>

</div>


<!-- ============================================================
     CSS
     ============================================================ -->
<style id="ez-po-styles">

/* ============================================================
   RESET & ROOT
   ============================================================ */
*, *::before, *::after { box-sizing: border-box; }

.ez-po-wrap {
    --po-primary: #3b82f6;
    --po-primary-dark: #2563eb;
    --po-accent: #8b5cf6;
    --po-success: #10b981;
    --po-danger: #ef4444;
    --po-warning: #f59e0b;

    --po-text: #0f172a;
    --po-text-soft: #334155;
    --po-text-muted: #64748b;
    --po-text-faint: #94a3b8;

    --po-border: #e8ecf1;
    --po-border-soft: #eef2f7;
    --po-bg-soft: #f8fafc;

    --po-radius-sm: 10px;
    --po-radius: 14px;
    --po-radius-lg: 20px;
    --po-radius-xl: 26px;

    font-family: \'Vazirmatn\', Tahoma, sans-serif;
    direction: rtl;
    max-width: 860px;
    margin: 0 auto;
    padding: 40px 36px;
    background:
        radial-gradient(circle at 100% 0%, rgba(59, 130, 246, 0.05), transparent 40%),
        radial-gradient(circle at 0% 100%, rgba(139, 92, 246, 0.05), transparent 40%),
        linear-gradient(180deg, #ffffff 0%, #fbfcfe 100%);
    border: 1px solid var(--po-border-soft);
    border-radius: var(--po-radius-xl);
    box-shadow:
        0 1px 3px rgba(15, 23, 42, 0.03),
        0 24px 70px -24px rgba(15, 23, 42, 0.1);
    color: var(--po-text);
    line-height: 1.7;
    position: relative;
    overflow: visible;
}

/* ============================================================
   HEADER
   ============================================================ */
.ez-po-header {
    display: flex;
    align-items: center;
    gap: 16px;
    padding: 0 0 28px;
    margin-bottom: 28px;
    border-bottom: 2px solid var(--po-border-soft);
    position: relative;
}

.ez-po-header::after {
    content: "";
    position: absolute;
    bottom: -2px;
    right: 0;
    width: 80px;
    height: 2px;
    background: linear-gradient(90deg, var(--po-primary), var(--po-accent));
    border-radius: 2px;
}

.ez-po-header-icon {
    width: 52px;
    height: 52px;
    flex-shrink: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, var(--po-primary), var(--po-accent));
    border-radius: 16px;
    color: #fff;
    box-shadow: 0 8px 20px -4px rgba(59, 130, 246, 0.4);
    animation: ezPoPulse 3s ease-in-out infinite;
}

@keyframes ezPoPulse {
    0%, 100% { box-shadow: 0 8px 20px -4px rgba(59, 130, 246, 0.4); }
    50% { box-shadow: 0 8px 28px -2px rgba(59, 130, 246, 0.55); }
}

.ez-po-header-icon svg {
    width: 26px;
    height: 26px;
    stroke-width: 1.8;
}

.ez-po-header-text { flex: 1; min-width: 0; }

.ez-po-header-text h2 {
    margin: 0 0 2px;
    font-size: 22px;
    font-weight: 800;
    color: var(--po-text);
    letter-spacing: -0.5px;
}

.ez-po-header-text p {
    margin: 0;
    font-size: 13px;
    color: var(--po-text-muted);
    line-height: 1.6;
}

/* ============================================================
   SECTIONS
   ============================================================ */
.ez-po-section {
    margin-bottom: 28px;
    padding: 22px 22px 24px;
    background: #fff;
    border: 1px solid var(--po-border);
    border-radius: var(--po-radius-lg);
    transition: all .3s ease;
    position: relative;
    overflow: visible;
}

.ez-po-section:hover {
    border-color: #dbe4ff;
    box-shadow: 0 8px 32px -12px rgba(15, 23, 42, 0.08);
}

/* Section variants */
.ez-po-section-od {
    background: linear-gradient(180deg, #ffffff 0%, #f9fbff 100%);
    border-color: #dbeafe;
}
.ez-po-section-od:hover {
    border-color: #bfdbfe;
    box-shadow: 0 8px 32px -12px rgba(59, 130, 246, 0.15);
}

.ez-po-section-os {
    background: linear-gradient(180deg, #ffffff 0%, #fdfbff 100%);
    border-color: #ede9fe;
}
.ez-po-section-os:hover {
    border-color: #ddd6fe;
    box-shadow: 0 8px 32px -12px rgba(139, 92, 246, 0.15);
}

.ez-po-section-pd {
    background: linear-gradient(180deg, #ffffff 0%, #fbfffc 100%);
    border-color: #d1fae5;
}
.ez-po-section-pd:hover {
    border-color: #a7f3d0;
    box-shadow: 0 8px 32px -12px rgba(16, 185, 129, 0.15);
}

.ez-po-section-lens {
    background: linear-gradient(180deg, #ffffff 0%, #fffdf8 100%);
    border-color: #fed7aa;
}
.ez-po-section-lens:hover {
    border-color: #fdba74;
    box-shadow: 0 8px 32px -12px rgba(249, 115, 22, 0.15);
}

/* ============================================================
   HEADINGS
   ============================================================ */
.ez-po-heading {
    display: flex;
    align-items: center;
    gap: 10px;
    margin: 0 0 18px;
    padding: 0 0 14px;
    font-size: 14.5px;
    font-weight: 700;
    color: var(--po-text);
    border-bottom: 1.5px dashed var(--po-border-soft);
    position: relative;
}

.ez-po-heading-icon {
    width: 30px;
    height: 30px;
    flex-shrink: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 9px;
    transition: all .3s;
}

.ez-po-heading-icon svg {
    width: 15px;
    height: 15px;
    stroke-width: 2;
}

.ez-po-heading-icon-od {
    background: linear-gradient(135deg, #dbeafe, #bfdbfe);
    color: #1e40af;
}
.ez-po-heading-icon-os {
    background: linear-gradient(135deg, #ede9fe, #ddd6fe);
    color: #6d28d9;
}
.ez-po-heading-icon-pd {
    background: linear-gradient(135deg, #d1fae5, #a7f3d0);
    color: #047857;
}
.ez-po-heading-icon-lens {
    background: linear-gradient(135deg, #fed7aa, #fdba74);
    color: #c2410c;
}
.ez-po-heading-icon-upload {
    background: linear-gradient(135deg, #e0e7ff, #c7d2fe);
    color: #4338ca;
}
.ez-po-heading-icon-note {
    background: linear-gradient(135deg, #fce7f3, #fbcfe8);
    color: #be185d;
}

.ez-po-optional {
    margin-right: auto;
    padding: 3px 10px;
    background: var(--po-bg-soft);
    color: var(--po-text-faint);
    font-size: 10.5px;
    font-weight: 700;
    border-radius: 100px;
    border: 1px solid var(--po-border);
    letter-spacing: 0.3px;
}

/* ============================================================
   GRID
   ============================================================ */
.ez-po-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 16px;
}

.ez-po-grid-4 {
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 14px;
}

/* ============================================================
   FIELD
   ============================================================ */
.ez-po-field {
    display: flex;
    flex-direction: column;
    gap: 8px;
    min-width: 0;
    position: relative;
}

.ez-po-field > label {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 12.5px;
    font-weight: 600;
    color: var(--po-text-soft);
    line-height: 1.4;
    margin: 0;
    padding: 0;
    letter-spacing: -0.1px;
    cursor: default;
}

.ez-po-label-text {
    flex: 0 1 auto;
    min-width: 0;
    overflow: hidden;
    text-overflow: ellipsis;
}

/* Hint Icon (?) */
.ez-po-hint-icon {
    width: 15px;
    height: 15px;
    flex-shrink: 0;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    color: var(--po-text-faint);
    cursor: help;
    transition: all .2s ease;
    position: relative;
    margin-right: auto;
}

.ez-po-hint-icon svg {
    width: 100%;
    height: 100%;
    stroke-width: 2;
    transition: all .2s ease;
}

.ez-po-hint-icon:hover {
    color: var(--po-primary);
    transform: scale(1.15);
}

/* Tooltip via data-hint on label */
.ez-po-field > label[data-hint] {
    position: relative;
}

.ez-po-field > label[data-hint]::before {
    content: attr(data-hint);
    position: absolute;
    bottom: calc(100% + 10px);
    right: 0;
    left: auto;
    min-width: 180px;
    max-width: 280px;
    background: #0f172a;
    color: #fff;
    font-size: 11.5px;
    font-weight: 500;
    line-height: 1.8;
    padding: 10px 14px;
    border-radius: 10px;
    opacity: 0;
    visibility: hidden;
    transform: translateY(6px);
    transition: opacity .25s, transform .25s, visibility .25s;
    z-index: 99;
    pointer-events: none;
    box-shadow: 0 12px 32px rgba(15, 23, 42, 0.35);
    text-align: right;
    white-space: normal;
    word-break: break-word;
}

.ez-po-field > label[data-hint]::after {
    content: "";
    position: absolute;
    bottom: calc(100% + 5px);
    right: 12px;
    width: 8px;
    height: 8px;
    background: #0f172a;
    transform: rotate(45deg) translateY(6px);
    opacity: 0;
    visibility: hidden;
    transition: all .25s;
    z-index: 99;
    pointer-events: none;
}

.ez-po-field > label[data-hint]:hover::before,
.ez-po-field > label[data-hint]:hover::after {
    opacity: 1;
    visibility: visible;
    transform: translateY(0);
}

.ez-po-field > label[data-hint]:hover::after {
    transform: rotate(45deg) translateY(2px);
}

/* ============================================================
   INPUT WRAPPER
   ============================================================ */
.ez-po-input-wrap {
    position: relative;
    display: flex;
    align-items: stretch;
    background: #fff;
    border: 1.5px solid var(--po-border);
    border-radius: var(--po-radius);
    transition: all .25s ease;
    overflow: hidden;
    box-shadow: 0 1px 2px rgba(15, 23, 42, 0.02);
}

.ez-po-input-wrap:hover {
    border-color: #cbd5e1;
}

.ez-po-input-wrap:focus-within {
    border-color: var(--po-primary);
    box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
    background: #fff;
}

/* Icon inside input */
.ez-po-input-icon {
    width: 44px;
    flex-shrink: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--po-text-faint);
    transition: color .25s;
    pointer-events: none;
}

.ez-po-input-icon svg {
    width: 16px;
    height: 16px;
    stroke-width: 1.8;
}

.ez-po-input-wrap:focus-within .ez-po-input-icon {
    color: var(--po-primary);
}

/* Inputs / selects / textareas */
.ez-po-input-wrap input,
.ez-po-input-wrap select,
.ez-po-input-wrap textarea {
    flex: 1;
    min-width: 0;
    padding: 12px 14px 12px 0;
    font-family: inherit;
    font-size: 14px;
    font-weight: 500;
    color: var(--po-text);
    background: transparent;
    border: 0;
    outline: 0;
    line-height: 1.5;
    min-height: 48px;
    margin: 0;
    text-align: right;
    direction: rtl;
}

.ez-po-input-wrap input[type="number"] {
    direction: ltr;
    text-align: left;
    font-variant-numeric: tabular-nums;
    letter-spacing: 0.3px;
}

/* Number with no icon - full width */
.ez-po-input-num input {
    padding-right: 14px;
}

.ez-po-input-wrap input::placeholder,
.ez-po-input-wrap textarea::placeholder {
    color: #cbd5e1;
    font-weight: 400;
}

.ez-po-input-wrap select {
    cursor: pointer;
    -webkit-appearance: none;
    appearance: none;
    background-image: url("data:image/svg+xml;utf8,<svg xmlns=\'http://www.w3.org/2000/svg\' viewBox=\'0 0 24 24\' fill=\'none\' stroke=\'%2364748b\' stroke-width=\'2\' stroke-linecap=\'round\' stroke-linejoin=\'round\'><polyline points=\'6 9 12 15 18 9\'/></svg>");
    background-repeat: no-repeat;
    background-position: left 14px center;
    background-size: 14px;
    padding-left: 40px;
}

/* Textarea */
.ez-po-textarea-wrap {
    align-items: flex-start;
    padding-top: 2px;
}
.ez-po-textarea-wrap .ez-po-input-icon {
    padding-top: 15px;
    height: auto;
    align-self: flex-start;
}
.ez-po-textarea-wrap textarea {
    min-height: 100px;
    resize: vertical;
    line-height: 1.9;
    padding-top: 14px;
    padding-bottom: 14px;
}

/* Number spinners */
.ez-po-input-wrap input[type="number"] {
    -moz-appearance: textfield;
    appearance: textfield;
}
.ez-po-input-wrap input[type="number"]::-webkit-outer-spin-button,
.ez-po-input-wrap input[type="number"]::-webkit-inner-spin-button {
    -webkit-appearance: none;
    margin: 0;
}

/* ============================================================
   VALIDATION STATUS
   ============================================================ */
.ez-po-status {
    width: 34px;
    flex-shrink: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transform: scale(.5);
    transition: opacity .3s, transform .3s cubic-bezier(.34, 1.56, .64, 1);
    pointer-events: none;
}

.ez-po-input-wrap.is-valid .ez-po-status {
    opacity: 1;
    transform: scale(1);
}

.ez-po-input-wrap.is-valid .ez-po-status::before {
    content: "";
    width: 18px;
    height: 18px;
    background: var(--po-success);
    border-radius: 50%;
    display: block;
    position: relative;
    box-shadow: 0 2px 6px rgba(16, 185, 129, 0.3);
}

.ez-po-input-wrap.is-valid .ez-po-status::after {
    content: "";
    position: absolute;
    width: 9px;
    height: 5px;
    border-left: 2px solid #fff;
    border-bottom: 2px solid #fff;
    transform: rotate(-45deg) translate(-1px, -3px);
}

.ez-po-input-wrap.is-valid {
    border-color: var(--po-success);
    background: #f0fdf4;
}

.ez-po-input-wrap.is-valid .ez-po-input-icon {
    color: var(--po-success);
}

.ez-po-input-wrap.is-error {
    border-color: var(--po-danger);
    background: #fef2f2;
    animation: ezPoShake .4s;
}

.ez-po-input-wrap.is-error .ez-po-input-icon {
    color: var(--po-danger);
}

.ez-po-input-wrap.is-error .ez-po-status {
    opacity: 1;
    transform: scale(1);
}

.ez-po-input-wrap.is-error .ez-po-status::before {
    content: "";
    width: 18px;
    height: 18px;
    background: var(--po-danger);
    border-radius: 50%;
    display: block;
    box-shadow: 0 2px 6px rgba(239, 68, 68, 0.3);
}

.ez-po-input-wrap.is-error .ez-po-status::after {
    content: "";
    position: absolute;
    width: 9px;
    height: 2px;
    background: #fff;
    border-radius: 1px;
}

@keyframes ezPoShake {
    0%, 100% { transform: translateX(0); }
    20% { transform: translateX(-5px); }
    40% { transform: translateX(5px); }
    60% { transform: translateX(-3px); }
    80% { transform: translateX(3px); }
}

/* ============================================================
   UPLOAD BOX
   ============================================================ */
.ez-po-upload-field {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.ez-po-upload {
    position: relative;
    border: 2px dashed var(--po-border);
    border-radius: var(--po-radius-lg);
    background: linear-gradient(180deg, #fafbfc 0%, #f5f7fb 100%);
    padding: 32px 24px;
    cursor: pointer;
    transition: all .3s cubic-bezier(.4, 0, .2, 1);
    overflow: hidden;
    min-height: 200px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.ez-po-upload::before {
    content: "";
    position: absolute;
    inset: 0;
    background:
        radial-gradient(circle at 50% 0%, rgba(59, 130, 246, 0.06), transparent 60%);
    opacity: 0;
    transition: opacity .4s;
    pointer-events: none;
}

.ez-po-upload:hover {
    border-color: var(--po-primary);
    background: linear-gradient(180deg, #f5f9ff 0%, #eef5ff 100%);
    transform: translateY(-2px);
    box-shadow: 0 12px 40px -12px rgba(59, 130, 246, 0.2);
}

.ez-po-upload:hover::before {
    opacity: 1;
}

.ez-po-upload[data-dragover="true"] {
    border-color: var(--po-primary);
    background: linear-gradient(180deg, #eff6ff 0%, #dbeafe 100%);
    transform: scale(1.01);
    box-shadow:
        0 0 0 4px rgba(59, 130, 246, 0.15),
        0 20px 60px -20px rgba(59, 130, 246, 0.3);
}

.ez-po-upload[data-dragover="true"] .ez-po-upload-icon-ring {
    animation: ezPoRingPulse .8s ease-in-out infinite;
}

@keyframes ezPoRingPulse {
    0%, 100% { transform: scale(1); opacity: 1; }
    50% { transform: scale(1.15); opacity: .6; }
}

/* Empty State */
.ez-po-upload-empty {
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
    gap: 12px;
    position: relative;
    z-index: 1;
    transition: opacity .3s, transform .3s;
}

.ez-po-upload.is-has-file .ez-po-upload-empty {
    display: none;
}

.ez-po-upload-icon-wrap {
    position: relative;
    width: 64px;
    height: 64px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 4px;
}

.ez-po-upload-icon-ring {
    position: absolute;
    inset: 0;
    border-radius: 50%;
    background: linear-gradient(135deg, #dbeafe, #e0e7ff);
    animation: ezPoFloat 3s ease-in-out infinite;
}

@keyframes ezPoFloat {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-4px); }
}

.ez-po-upload-icon {
    position: relative;
    z-index: 1;
    width: 26px;
    height: 26px;
    color: var(--po-primary);
    stroke-width: 1.8;
    animation: ezPoIconBounce 2s ease-in-out infinite;
}

@keyframes ezPoIconBounce {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-3px); }
}

.ez-po-upload-title {
    font-size: 15px;
    font-weight: 700;
    color: var(--po-text);
    letter-spacing: -0.2px;
}

.ez-po-upload-sub {
    font-size: 13px;
    color: var(--po-text-muted);
    line-height: 1.5;
}

.ez-po-upload-link {
    color: var(--po-primary);
    font-weight: 700;
    text-decoration: underline;
    text-decoration-style: dashed;
    text-underline-offset: 3px;
}

.ez-po-upload-formats {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
    justify-content: center;
    margin-top: 6px;
}

.ez-po-format-badge {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 4px 10px;
    background: #fff;
    border: 1px solid var(--po-border);
    border-radius: 100px;
    font-size: 10.5px;
    font-weight: 700;
    color: var(--po-text-muted);
    transition: all .2s;
}

.ez-po-format-badge svg {
    width: 11px;
    height: 11px;
    stroke-width: 2;
    color: var(--po-text-faint);
}

.ez-po-upload:hover .ez-po-format-badge {
    border-color: #bfdbfe;
    color: #1e40af;
}

/* Preview State */
.ez-po-upload-preview {
    display: none;
    align-items: center;
    gap: 14px;
    width: 100%;
    padding: 4px 0;
    position: relative;
    z-index: 1;
    animation: ezPoPreviewIn .4s cubic-bezier(.34, 1.56, .64, 1);
}

.ez-po-upload.is-has-file .ez-po-upload-preview {
    display: flex;
}

@keyframes ezPoPreviewIn {
    from { opacity: 0; transform: translateY(8px) scale(.96); }
    to { opacity: 1; transform: translateY(0) scale(1); }
}

.ez-po-preview-thumb {
    width: 72px;
    height: 72px;
    flex-shrink: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 16px;
    background: linear-gradient(135deg, var(--po-primary), var(--po-accent));
    color: #fff;
    overflow: hidden;
    position: relative;
    box-shadow: 0 8px 24px -6px rgba(59, 130, 246, 0.4);
}

.ez-po-preview-thumb img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
}

.ez-po-preview-thumb svg {
    width: 32px;
    height: 32px;
    stroke-width: 1.6;
}

.ez-po-preview-info {
    flex: 1;
    min-width: 0;
    display: flex;
    flex-direction: column;
    gap: 4px;
    text-align: right;
}

.ez-po-preview-name {
    font-size: 14px;
    font-weight: 700;
    color: var(--po-text);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.ez-po-preview-size {
    font-size: 12px;
    color: var(--po-text-muted);
    font-variant-numeric: tabular-nums;
}

.ez-po-preview-remove {
    width: 38px;
    height: 38px;
    flex-shrink: 0;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: #fef2f2;
    color: var(--po-danger);
    border: 1.5px solid #fecaca;
    border-radius: 12px;
    cursor: pointer;
    transition: all .2s cubic-bezier(.34, 1.56, .64, 1);
    font-family: inherit;
}

.ez-po-preview-remove:hover {
    background: var(--po-danger);
    color: #fff;
    border-color: var(--po-danger);
    transform: scale(1.08) rotate(90deg);
    box-shadow: 0 8px 20px -4px rgba(239, 68, 68, 0.4);
}

.ez-po-preview-remove svg {
    width: 16px;
    height: 16px;
    stroke-width: 2.5;
}

/* Progress */
.ez-po-upload-progress {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: transparent;
    overflow: hidden;
    opacity: 0;
    transition: opacity .3s;
}

.ez-po-upload-progress.is-active {
    opacity: 1;
}

.ez-po-upload-progress-bar {
    height: 100%;
    width: 0;
    background: linear-gradient(90deg, var(--po-primary), var(--po-accent));
    border-radius: 0;
    transition: width .2s ease-out;
}

/* Note */
.ez-po-upload-note {
    display: flex;
    align-items: flex-start;
    gap: 8px;
    padding: 10px 14px;
    background: #f0f9ff;
    border: 1px solid #bae6fd;
    border-radius: var(--po-radius-sm);
    font-size: 12px;
    color: #075985;
    line-height: 1.7;
}

.ez-po-upload-note svg {
    width: 14px;
    height: 14px;
    flex-shrink: 0;
    margin-top: 3px;
    stroke-width: 2;
}

/* ============================================================
   RESPONSIVE
   ============================================================ */
@media (max-width: 720px) {
    .ez-po-wrap {
        padding: 28px 20px;
        border-radius: var(--po-radius-lg);
    }

    .ez-po-header-icon {
        width: 44px;
        height: 44px;
        border-radius: 13px;
    }
    .ez-po-header-icon svg { width: 22px; height: 22px; }
    .ez-po-header-text h2 { font-size: 18px; }
    .ez-po-header-text p { font-size: 12px; }

    .ez-po-section {
        padding: 18px 16px;
        border-radius: var(--po-radius);
    }

    .ez-po-grid,
    .ez-po-grid-4 {
        grid-template-columns: 1fr;
        gap: 14px;
    }

    .ez-po-upload {
        padding: 26px 18px;
        min-height: 170px;
    }

    .ez-po-upload-icon-wrap {
        width: 54px;
        height: 54px;
    }

    .ez-po-upload-icon { width: 22px; height: 22px; }

    .ez-po-upload-title { font-size: 14px; }
    .ez-po-upload-sub { font-size: 12px; }

    .ez-po-field > label[data-hint]::before {
        right: 0;
        left: 0;
        max-width: 100%;
    }

    .ez-po-preview-thumb {
        width: 60px;
        height: 60px;
        border-radius: 13px;
    }
}

@media (max-width: 420px) {
    .ez-po-wrap { padding: 22px 14px; }
    .ez-po-header-text h2 { font-size: 16px; }
    .ez-po-heading { font-size: 13.5px; }
    .ez-po-input-wrap input,
    .ez-po-input-wrap select,
    .ez-po-input-wrap textarea {
        font-size: 13.5px;
        min-height: 46px;
    }
}

@media (prefers-reduced-motion: reduce) {
    .ez-po-wrap *,
    .ez-po-wrap *::before,
    .ez-po-wrap *::after {
        animation-duration: .01ms !important;
        transition-duration: .01ms !important;
    }
}

</style>


<!-- ============================================================
     JS
     ============================================================ -->
<script>
(function() {
    \'use strict\';

    document.addEventListener(\'DOMContentLoaded\', function() {
        const wrap = document.getElementById(\'ez-po-wrap\');
        if (!wrap) return;

        /* ============================================
           1. VALIDATION SYSTEM
           ============================================ */
        const inputs = wrap.querySelectorAll(\'input, select, textarea\');

        function validateField(el) {
            const value = el.value.trim();
            const inputWrap = el.closest(\'.ez-po-input-wrap\');
            if (!inputWrap) return;

            // remove old state
            inputWrap.classList.remove(\'is-valid\', \'is-error\');

            // skip empty & not required
            if (!value) {
                if (el.required) {
                    inputWrap.classList.add(\'is-error\');
                }
                return;
            }

            let isValid = true;

            // Custom rules
            if (el.type === \'email\') {
                isValid = /^[^\\s@]+@[^\\s@]+\\.[^\\s@]+$/.test(value);
            } else if (el.type === \'tel\') {
                isValid = /^09\\d{9}$/.test(value.replace(/\\s/g, \'\'));
            } else if (el.type === \'number\') {
                isValid = !isNaN(parseFloat(value));
                const min = el.getAttribute(\'min\');
                const max = el.getAttribute(\'max\');
                if (min !== null && parseFloat(value) < parseFloat(min)) isValid = false;
                if (max !== null && parseFloat(value) > parseFloat(max)) isValid = false;
            } else if (el.tagName === \'TEXTAREA\') {
                isValid = value.length >= 3;
            } else if (el.tagName === \'SELECT\') {
                isValid = value !== \'\';
            } else {
                isValid = value.length >= 2;
            }

            if (isValid) {
                inputWrap.classList.add(\'is-valid\');
            } else {
                inputWrap.classList.add(\'is-error\');
            }
        }

        inputs.forEach(function(el) {
            el.addEventListener(\'blur\', function() { validateField(el); });
            el.addEventListener(\'change\', function() { validateField(el); });
            el.addEventListener(\'input\', function() {
                const inputWrap = el.closest(\'.ez-po-input-wrap\');
                if (!inputWrap) return;
                // If currently in error, live-validate on input
                if (inputWrap.classList.contains(\'is-error\')) {
                    validateField(el);
                }
            });
        });

        /* ============================================
           2. PD MODE TOGGLE
           ============================================ */
        const pdType = document.getElementById(\'pd_type\');
        const pdBinocular = document.querySelector(\'.ez-po-field[data-pd-mode="binocular"]\');
        const pdMonocular = document.querySelectorAll(\'.ez-po-field[data-pd-mode="monocular"]\');

        function updatePDVisibility() {
            if (!pdType) return;
            const mode = pdType.value;

            if (pdBinocular) {
                if (mode === \'binocular\') {
                    pdBinocular.style.display = \'\';
                    pdBinocular.animate([
                        { opacity: 0, transform: \'translateY(-8px)\' },
                        { opacity: 1, transform: \'translateY(0)\' }
                    ], { duration: 300, easing: \'cubic-bezier(.34,1.56,.64,1)\' });
                } else {
                    pdBinocular.style.display = \'none\';
                }
            }
            pdMonocular.forEach(function(el) {
                if (mode === \'monocular\') {
                    el.style.display = \'\';
                    el.animate([
                        { opacity: 0, transform: \'translateY(-8px)\' },
                        { opacity: 1, transform: \'translateY(0)\' }
                    ], { duration: 300, easing: \'cubic-bezier(.34,1.56,.64,1)\' });
                } else {
                    el.style.display = \'none\';
                }
            });
        }

        if (pdType) {
            pdType.addEventListener(\'change\', updatePDVisibility);
            updatePDVisibility();
        }

        /* ============================================
           3. AXIS VALIDATION (0-180)
           ============================================ */
        document.querySelectorAll(\'input[name="od_axis"], input[name="os_axis"]\').forEach(function(input) {
            input.addEventListener(\'blur\', function() {
                let val = parseFloat(input.value);
                if (isNaN(val)) return;
                if (val < 0) input.value = 0;
                if (val > 180) input.value = 180;
                validateField(input);
            });
        });

        /* ============================================
           4. FILE UPLOAD SYSTEM
           ============================================ */
        const uploadZone = document.getElementById(\'rx_upload\');
        const fileInput = document.getElementById(\'rx_file\');
        const uploadEmpty = document.getElementById(\'rx_upload_empty\');
        const uploadPreview = document.getElementById(\'rx_upload_preview\');
        const previewThumb = document.getElementById(\'rx_preview_thumb\');
        const previewName = document.getElementById(\'rx_preview_name\');
        const previewSize = document.getElementById(\'rx_preview_size\');
        const previewRemove = document.getElementById(\'rx_preview_remove\');
        const uploadProgress = document.getElementById(\'rx_upload_progress\');
        const uploadProgressBar = document.getElementById(\'rx_upload_progress_bar\');

        const MAX_SIZE = 5 * 1024 * 1024; // 5MB
        const ALLOWED_TYPES = [\'image/jpeg\', \'image/png\', \'image/jpg\', \'image/webp\', \'application/pdf\'];

        function formatSize(bytes) {
            if (bytes < 1024) return bytes + \' بایت\';
            if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + \' کیلوبایت\';
            return (bytes / (1024 * 1024)).toFixed(2) + \' مگابایت\';
        }

        function showUploadError(message) {
            uploadZone.animate([
                { transform: \'translateX(0)\' },
                { transform: \'translateX(-6px)\' },
                { transform: \'translateX(6px)\' },
                { transform: \'translateX(-4px)\' },
                { transform: \'translateX(4px)\' },
                { transform: \'translateX(0)\' }
            ], { duration: 400 });
            // Temporary visual cue
            uploadZone.style.borderColor = \'#ef4444\';
            uploadZone.style.background = \'#fef2f2\';
            setTimeout(function() {
                uploadZone.style.borderColor = \'\';
                uploadZone.style.background = \'\';
            }, 1500);
            // Show message
            if (window.console && console.warn) console.warn(\'[EzPo Upload]\', message);
            alert(message);
        }

        function renderPreview(file) {
            previewName.textContent = file.name;
            previewSize.textContent = formatSize(file.size);

            // Clear old
            previewThumb.innerHTML = \'\';

            if (file.type.startsWith(\'image/\')) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const img = document.createElement(\'img\');
                    img.src = e.target.result;
                    img.alt = file.name;
                    previewThumb.appendChild(img);
                };
                reader.readAsDataURL(file);
            } else {
                // PDF icon
                previewThumb.innerHTML =
                    \'<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">\' +
                        \'<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>\' +
                        \'<polyline points="14 2 14 8 20 8"/>\' +
                        \'<line x1="9" y1="15" x2="15" y2="15"/>\' +
                    \'</svg>\';
            }
        }

        function handleFile(file) {
            if (!file) return;

            if (file.size > MAX_SIZE) {
                showUploadError(\'حجم فایل بیشتر از ۵ مگابایت است\');
                return;
            }
            if (ALLOWED_TYPES.indexOf(file.type) === -1) {
                showUploadError(\'فقط فرمت‌های JPG، PNG، WebP و PDF مجاز هستند\');
                return;
            }

            // Simulate progress
            uploadProgress.classList.add(\'is-active\');
            uploadProgressBar.style.width = \'0%\';

            let progress = 0;
            const interval = setInterval(function() {
                progress += Math.random() * 18 + 8;
                if (progress >= 100) {
                    progress = 100;
                    uploadProgressBar.style.width = \'100%\';
                    clearInterval(interval);
                    setTimeout(function() {
                        uploadProgress.classList.remove(\'is-active\');
                        uploadProgressBar.style.width = \'0%\';
                        uploadZone.classList.add(\'is-has-file\');
                        renderPreview(file);
                    }, 300);
                } else {
                    uploadProgressBar.style.width = progress + \'%\';
                }
            }, 100);
        }

        function clearFile() {
            fileInput.value = \'\';
            uploadZone.classList.remove(\'is-has-file\');
            previewThumb.innerHTML = \'\';
            previewName.textContent = \'—\';
            previewSize.textContent = \'—\';
        }

        // Click to upload
        uploadZone.addEventListener(\'click\', function(e) {
            if (e.target.closest(\'.ez-po-preview-remove\')) return;
            if (uploadZone.classList.contains(\'is-has-file\')) return;
            fileInput.click();
        });

        fileInput.addEventListener(\'change\', function(e) {
            if (e.target.files.length > 0) {
                handleFile(e.target.files[0]);
            }
        });

        // Drag & Drop
        [\'dragenter\', \'dragover\'].forEach(function(evt) {
            uploadZone.addEventListener(evt, function(e) {
                e.preventDefault();
                e.stopPropagation();
                uploadZone.setAttribute(\'data-dragover\', \'true\');
            });
        });

        [\'dragleave\', \'drop\'].forEach(function(evt) {
            uploadZone.addEventListener(evt, function(e) {
                e.preventDefault();
                e.stopPropagation();
                uploadZone.setAttribute(\'data-dragover\', \'false\');
            });
        });

        uploadZone.addEventListener(\'drop\', function(e) {
            const files = e.dataTransfer.files;
            if (files.length > 0) handleFile(files[0]);
        });

        // Remove file with animation
        previewRemove.addEventListener(\'click\', function(e) {
            e.stopPropagation();
            uploadZone.animate([
                { opacity: 1, transform: \'scale(1)\' },
                { opacity: 0, transform: \'scale(.95)\' }
            ], { duration: 250, easing: \'ease-out\' }).onfinish = function() {
                clearFile();
                uploadZone.animate([
                    { opacity: 0, transform: \'scale(.95)\' },
                    { opacity: 1, transform: \'scale(1)\' }
                ], { duration: 300, easing: \'cubic-bezier(.34,1.56,.64,1)\' });
            };
        });

        /* ============================================
           5. ADD HIGHLIGHT FOR PROGRESSIVE/BIFOCAL
           ============================================ */
        const useType = document.getElementById(\'use_type\');
        function highlightAdd() {
            if (!useType) return;
            const mode = useType.value;
            const needsAdd = (mode === \'progressive\' || mode === \'bifocal\');
            document.querySelectorAll(\'input[name="od_add"], input[name="os_add"]\').forEach(function(input) {
                const field = input.closest(\'.ez-po-field\');
                if (!field) return;
                const label = field.querySelector(\'.ez-po-label-text\');
                if (label) {
                    if (needsAdd) {
                        label.style.color = \'#c2410c\';
                        label.style.fontWeight = \'700\';
                    } else {
                        label.style.color = \'\';
                        label.style.fontWeight = \'\';
                    }
                }
            });
        }
        if (useType) {
            useType.addEventListener(\'change\', highlightAdd);
            highlightAdd();
        }

    });

})();
</script>ned',
    '_code_css' => '',
    '_code_js' => '',
  ),
  'code' => 'undefi<!-- ============================================================
     EZLENS — Prescription Form Pro (نسخه عینک طبی)
     ============================================================ -->

<div class="ez-po-wrap" id="ez-po-wrap">

  <!-- ═══════════════ HEADER ═══════════════ -->
  <div class="ez-po-header">
    <div class="ez-po-header-icon">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
        <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7z"/>
        <circle cx="12" cy="12" r="3"/>
      </svg>
    </div>
    <div class="ez-po-header-text">
      <h2>نسخه عینک طبی</h2>
      <p>اطلاعات نسخه‌ات رو وارد کن یا عکسش رو آپلود کن</p>
    </div>
  </div>

  <!-- ═══════════════ BASIC INFO ═══════════════ -->
  <div class="ez-po-section">
    <h3 class="ez-po-heading">اطلاعات پایه</h3>
    <div class="ez-po-grid">
      <div class="ez-po-field" data-type="text">
        <label for="doctor_name" data-hint="اگر نسخه رو پزشک خاصی نوشته، اینجا وارد کن (اختیاری)">
          <span class="ez-po-label-text">نام پزشک / مرکز</span>
          <span class="ez-po-hint-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
              <circle cx="12" cy="12" r="10"/>
              <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/>
              <line x1="12" y1="17" x2="12.01" y2="17"/>
            </svg>
          </span>
        </label>
        <div class="ez-po-input-wrap">
          <span class="ez-po-input-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
              <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
              <circle cx="12" cy="7" r="4"/>
            </svg>
          </span>
          <input type="text" id="doctor_name" name="doctor_name" placeholder="مثلاً دکتر رضایی" autocomplete="off">
          <span class="ez-po-status"></span>
        </div>
      </div>

      <div class="ez-po-field" data-type="date">
        <label for="rx_date" data-hint="تاریخی که پزشک نسخه رو نوشته — معمولاً نسخه‌ها ۶ ماه اعتبار دارن">
          <span class="ez-po-label-text">تاریخ نسخه</span>
          <span class="ez-po-hint-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
              <circle cx="12" cy="12" r="10"/>
              <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/>
              <line x1="12" y1="17" x2="12.01" y2="17"/>
            </svg>
          </span>
        </label>
        <div class="ez-po-input-wrap">
          <span class="ez-po-input-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
              <rect x="3" y="4" width="18" height="18" rx="2"/>
              <line x1="16" y1="2" x2="16" y2="6"/>
              <line x1="8" y1="2" x2="8" y2="6"/>
              <line x1="3" y1="10" x2="21" y2="10"/>
            </svg>
          </span>
          <input type="date" id="rx_date" name="rx_date" placeholder="">
          <span class="ez-po-status"></span>
        </div>
      </div>
    </div>
  </div>

  <!-- ═══════════════ OD SECTION ═══════════════ -->
  <div class="ez-po-section ez-po-section-od">
    <h3 class="ez-po-heading">
      <span class="ez-po-heading-icon ez-po-heading-icon-od">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
          <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7z"/>
          <circle cx="12" cy="12" r="3"/>
        </svg>
      </span>
      <span>چشم راست (OD)</span>
    </h3>
    <div class="ez-po-grid ez-po-grid-4">
      <div class="ez-po-field" data-type="number">
        <label for="od_sph" data-hint="شماره نمره چشم راست — روی نسخه معمولاً سمت راست نوشته می‌شه">
          <span class="ez-po-label-text">SPH</span>
          <span class="ez-po-hint-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
              <circle cx="12" cy="12" r="10"/>
              <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/>
              <line x1="12" y1="17" x2="12.01" y2="17"/>
            </svg>
          </span>
        </label>
        <div class="ez-po-input-wrap ez-po-input-num">
          <input type="number" id="od_sph" name="od_sph" placeholder="-۲.۵۰" step="0.25" required>
          <span class="ez-po-status"></span>
        </div>
      </div>
      <div class="ez-po-field" data-type="number">
        <label for="od_cyl" data-hint="میزان آستیگماتیسم چشم راست — عددی منفی مثل -۰.۷۵">
          <span class="ez-po-label-text">CYL</span>
          <span class="ez-po-hint-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
              <circle cx="12" cy="12" r="10"/>
              <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/>
              <line x1="12" y1="17" x2="12.01" y2="17"/>
            </svg>
          </span>
        </label>
        <div class="ez-po-input-wrap ez-po-input-num">
          <input type="number" id="od_cyl" name="od_cyl" placeholder="-۰.۷۵" step="0.25">
          <span class="ez-po-status"></span>
        </div>
      </div>
      <div class="ez-po-field" data-type="number">
        <label for="od_axis" data-hint="زاویه آستیگماتیسم بین ۰ تا ۱۸۰ درجه">
          <span class="ez-po-label-text">AXIS</span>
          <span class="ez-po-hint-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
              <circle cx="12" cy="12" r="10"/>
              <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/>
              <line x1="12" y1="17" x2="12.01" y2="17"/>
            </svg>
          </span>
        </label>
        <div class="ez-po-input-wrap ez-po-input-num">
          <input type="number" id="od_axis" name="od_axis" placeholder="۰ تا ۱۸۰" min="0" max="180">
          <span class="ez-po-status"></span>
        </div>
      </div>
      <div class="ez-po-field" data-type="number">
        <label for="od_add" data-hint="فقط برای افراد بالای ۴۰ سال که دچار پیرچشمی شدن">
          <span class="ez-po-label-text">ADD</span>
          <span class="ez-po-hint-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
              <circle cx="12" cy="12" r="10"/>
              <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/>
              <line x1="12" y1="17" x2="12.01" y2="17"/>
            </svg>
          </span>
        </label>
        <div class="ez-po-input-wrap ez-po-input-num">
          <input type="number" id="od_add" name="od_add" placeholder="در صورت پیرچشمی" step="0.25">
          <span class="ez-po-status"></span>
        </div>
      </div>
    </div>
  </div>

  <!-- ═══════════════ OS SECTION ═══════════════ -->
  <div class="ez-po-section ez-po-section-os">
    <h3 class="ez-po-heading">
      <span class="ez-po-heading-icon ez-po-heading-icon-os">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
          <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7z"/>
          <circle cx="12" cy="12" r="3"/>
        </svg>
      </span>
      <span>چشم چپ (OS)</span>
    </h3>
    <div class="ez-po-grid ez-po-grid-4">
      <div class="ez-po-field" data-type="number">
        <label for="os_sph" data-hint="شماره نمره چشم چپ">
          <span class="ez-po-label-text">SPH</span>
          <span class="ez-po-hint-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
              <circle cx="12" cy="12" r="10"/>
              <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/>
              <line x1="12" y1="17" x2="12.01" y2="17"/>
            </svg>
          </span>
        </label>
        <div class="ez-po-input-wrap ez-po-input-num">
          <input type="number" id="os_sph" name="os_sph" placeholder="-۱.۷۵" step="0.25" required>
          <span class="ez-po-status"></span>
        </div>
      </div>
      <div class="ez-po-field" data-type="number">
        <label for="os_cyl" data-hint="میزان آستیگماتیسم چشم چپ">
          <span class="ez-po-label-text">CYL</span>
          <span class="ez-po-hint-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
              <circle cx="12" cy="12" r="10"/>
              <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/>
              <line x1="12" y1="17" x2="12.01" y2="17"/>
            </svg>
          </span>
        </label>
        <div class="ez-po-input-wrap ez-po-input-num">
          <input type="number" id="os_cyl" name="os_cyl" placeholder="-۰.۵۰" step="0.25">
          <span class="ez-po-status"></span>
        </div>
      </div>
      <div class="ez-po-field" data-type="number">
        <label for="os_axis" data-hint="زاویه آستیگماتیسم چشم چپ بین ۰ تا ۱۸۰ درجه">
          <span class="ez-po-label-text">AXIS</span>
          <span class="ez-po-hint-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
              <circle cx="12" cy="12" r="10"/>
              <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/>
              <line x1="12" y1="17" x2="12.01" y2="17"/>
            </svg>
          </span>
        </label>
        <div class="ez-po-input-wrap ez-po-input-num">
          <input type="number" id="os_axis" name="os_axis" placeholder="۰ تا ۱۸۰" min="0" max="180">
          <span class="ez-po-status"></span>
        </div>
      </div>
      <div class="ez-po-field" data-type="number">
        <label for="os_add" data-hint="فقط برای افراد بالای ۴۰ سال که دچار پیرچشمی شدن">
          <span class="ez-po-label-text">ADD</span>
          <span class="ez-po-hint-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
              <circle cx="12" cy="12" r="10"/>
              <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/>
              <line x1="12" y1="17" x2="12.01" y2="17"/>
            </svg>
          </span>
        </label>
        <div class="ez-po-input-wrap ez-po-input-num">
          <input type="number" id="os_add" name="os_add" placeholder="در صورت پیرچشمی" step="0.25">
          <span class="ez-po-status"></span>
        </div>
      </div>
    </div>
  </div>

  <!-- ═══════════════ PD SECTION ═══════════════ -->
  <div class="ez-po-section ez-po-section-pd">
    <h3 class="ez-po-heading">
      <span class="ez-po-heading-icon ez-po-heading-icon-pd">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
          <circle cx="6" cy="12" r="3"/>
          <circle cx="18" cy="12" r="3"/>
          <line x1="9" y1="12" x2="15" y2="12"/>
        </svg>
      </span>
      <span>فاصله مردمکی (PD)</span>
    </h3>
    <div class="ez-po-grid">
      <div class="ez-po-field" data-type="select">
        <label for="pd_type" data-hint="اگه یه عدد روی نسخه نوشته شده، دوچشمی رو انتخاب کن. اگه دو عدد جدا، تک‌چشمی">
          <span class="ez-po-label-text">نوع PD</span>
          <span class="ez-po-hint-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
              <circle cx="12" cy="12" r="10"/>
              <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/>
              <line x1="12" y1="17" x2="12.01" y2="17"/>
            </svg>
          </span>
        </label>
        <div class="ez-po-input-wrap">
          <span class="ez-po-input-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
              <polyline points="6 9 12 15 18 9"/>
            </svg>
          </span>
          <select id="pd_type" name="pd_type" required>
            <option value="binocular">دوچشمی (یک عدد)</option>
            <option value="monocular">تک‌چشمی (راست / چپ)</option>
          </select>
          <span class="ez-po-status"></span>
        </div>
      </div>

      <div class="ez-po-field" data-type="number" data-pd-mode="binocular">
        <label for="pd" data-hint="فاصله بین دو مردمک — معمولاً بین ۵۸ تا ۷۰ میلی‌متر">
          <span class="ez-po-label-text">PD دوچشمی</span>
          <span class="ez-po-hint-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
              <circle cx="12" cy="12" r="10"/>
              <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/>
              <line x1="12" y1="17" x2="12.01" y2="17"/>
            </svg>
          </span>
        </label>
        <div class="ez-po-input-wrap ez-po-input-num">
          <input type="number" id="pd" name="pd" placeholder="۶۲" min="40" max="90">
          <span class="ez-po-status"></span>
        </div>
      </div>

      <div class="ez-po-field" data-type="number" data-pd-mode="monocular">
        <label for="pd_od" data-hint="فاصله مردمک چشم راست از مرکز بینی">
          <span class="ez-po-label-text">PD راست</span>
          <span class="ez-po-hint-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
              <circle cx="12" cy="12" r="10"/>
              <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/>
              <line x1="12" y1="17" x2="12.01" y2="17"/>
            </svg>
          </span>
        </label>
        <div class="ez-po-input-wrap ez-po-input-num">
          <input type="number" id="pd_od" name="pd_od" placeholder="۳۱" min="20" max="50">
          <span class="ez-po-status"></span>
        </div>
      </div>

      <div class="ez-po-field" data-type="number" data-pd-mode="monocular">
        <label for="pd_os" data-hint="فاصله مردمک چشم چپ از مرکز بینی">
          <span class="ez-po-label-text">PD چپ</span>
          <span class="ez-po-hint-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
              <circle cx="12" cy="12" r="10"/>
              <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/>
              <line x1="12" y1="17" x2="12.01" y2="17"/>
            </svg>
          </span>
        </label>
        <div class="ez-po-input-wrap ez-po-input-num">
          <input type="number" id="pd_os" name="pd_os" placeholder="۳۱" min="20" max="50">
          <span class="ez-po-status"></span>
        </div>
      </div>
    </div>
  </div>

  <!-- ═══════════════ LENS SETTINGS ═══════════════ -->
  <div class="ez-po-section ez-po-section-lens">
    <h3 class="ez-po-heading">
      <span class="ez-po-heading-icon ez-po-heading-icon-lens">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
          <circle cx="12" cy="12" r="9"/>
          <path d="M3 12h18"/>
        </svg>
      </span>
      <span>تنظیمات عدسی</span>
    </h3>
    <div class="ez-po-grid">
      <div class="ez-po-field" data-type="select">
        <label for="use_type" data-hint="عدسی دور برای رانندگی، نزدیک برای مطالعه، تدریجی برای هر سه فاصله">
          <span class="ez-po-label-text">کاربرد عینک</span>
          <span class="ez-po-hint-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
              <circle cx="12" cy="12" r="10"/>
              <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/>
              <line x1="12" y1="17" x2="12.01" y2="17"/>
            </svg>
          </span>
        </label>
        <div class="ez-po-input-wrap">
          <span class="ez-po-input-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
              <polyline points="6 9 12 15 18 9"/>
            </svg>
          </span>
          <select id="use_type" name="use_type" required>
            <option value="distance">دور</option>
            <option value="near">نزدیک / مطالعه</option>
            <option value="computer">کامپیوتر / میانی</option>
            <option value="progressive">تدریجی (پروگرسیو)</option>
            <option value="bifocal">دوکانونی</option>
          </select>
          <span class="ez-po-status"></span>
        </div>
      </div>

      <div class="ez-po-field" data-type="select">
        <label for="lens_index" data-hint="هرچی بالاتر، عدسی نازک‌تر و سبک‌تر — برای نمره‌های بالا ۱.۶۷ یا ۱.۷۴ بهتره">
          <span class="ez-po-label-text">ضریب شکست عدسی</span>
          <span class="ez-po-hint-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
              <circle cx="12" cy="12" r="10"/>
              <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/>
              <line x1="12" y1="17" x2="12.01" y2="17"/>
            </svg>
          </span>
        </label>
        <div class="ez-po-input-wrap">
          <span class="ez-po-input-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
              <polyline points="6 9 12 15 18 9"/>
            </svg>
          </span>
          <select id="lens_index" name="lens_index">
            <option value="1.5">۱.۵۰ استاندارد</option>
            <option value="1.56">۱.۵۶</option>
            <option value="1.6">۱.۶۰ نازک</option>
            <option value="1.67">۱.۶۷ فوق نازک</option>
            <option value="1.74">۱.۷۴ بسیار نازک</option>
          </select>
          <span class="ez-po-status"></span>
        </div>
      </div>
    </div>
  </div>

  <!-- ═══════════════ UPLOAD BOX ═══════════════ -->
  <div class="ez-po-section">
    <h3 class="ez-po-heading">
      <span class="ez-po-heading-icon ez-po-heading-icon-upload">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
          <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
          <polyline points="17 8 12 3 7 8"/>
          <line x1="12" y1="3" x2="12" y2="15"/>
        </svg>
      </span>
      <span>آپلود تصویر نسخه</span>
      <span class="ez-po-optional">اختیاری</span>
    </h3>

    <div class="ez-po-upload-field">
      <div class="ez-po-upload" id="rx_upload" data-dragover="false">
        <input type="file" id="rx_file" name="rx_file" accept="image/*,.pdf" hidden>

        <!-- Empty State -->
        <div class="ez-po-upload-empty" id="rx_upload_empty">
          <div class="ez-po-upload-icon-wrap">
            <div class="ez-po-upload-icon-ring"></div>
            <svg class="ez-po-upload-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
              <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
              <polyline points="17 8 12 3 7 8"/>
              <line x1="12" y1="3" x2="12" y2="15"/>
            </svg>
          </div>
          <div class="ez-po-upload-title">فایل را اینجا بکشید</div>
          <div class="ez-po-upload-sub">یا <span class="ez-po-upload-link">انتخاب فایل</span> از دستگاه</div>
          <div class="ez-po-upload-formats">
            <span class="ez-po-format-badge">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                <rect x="3" y="3" width="18" height="18" rx="2"/>
                <circle cx="8.5" cy="8.5" r="1.5"/>
                <polyline points="21 15 16 10 5 21"/>
              </svg>
              JPG, PNG
            </span>
            <span class="ez-po-format-badge">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                <polyline points="14 2 14 8 20 8"/>
              </svg>
              PDF
            </span>
            <span class="ez-po-format-badge">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"/>
                <polyline points="12 6 12 12 16 14"/>
              </svg>
              حداکثر ۵MB
            </span>
          </div>
        </div>

        <!-- Preview State -->
        <div class="ez-po-upload-preview" id="rx_upload_preview">
          <div class="ez-po-preview-thumb" id="rx_preview_thumb">
            <!-- filled via JS -->
          </div>
          <div class="ez-po-preview-info">
            <div class="ez-po-preview-name" id="rx_preview_name">—</div>
            <div class="ez-po-preview-size" id="rx_preview_size">—</div>
          </div>
          <button type="button" class="ez-po-preview-remove" id="rx_preview_remove" aria-label="حذف فایل">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
              <line x1="18" y1="6" x2="6" y2="18"/>
              <line x1="6" y1="6" x2="18" y2="18"/>
            </svg>
          </button>
        </div>

        <!-- Progress -->
        <div class="ez-po-upload-progress" id="rx_upload_progress">
          <div class="ez-po-upload-progress-bar" id="rx_upload_progress_bar"></div>
        </div>
      </div>

      <div class="ez-po-upload-note">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
          <circle cx="12" cy="12" r="10"/>
          <line x1="12" y1="16" x2="12" y2="12"/>
          <line x1="12" y1="8" x2="12.01" y2="8"/>
        </svg>
        <span>تصویر نسخه باید واضح و خوانا باشه تا اپتومتریست بتونه بررسی کنه</span>
      </div>
    </div>
  </div>

  <!-- ═══════════════ NOTES ═══════════════ -->
  <div class="ez-po-section">
    <h3 class="ez-po-heading">
      <span class="ez-po-heading-icon ez-po-heading-icon-note">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
          <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
        </svg>
      </span>
      <span>یادداشت برای اپتومتریست</span>
    </h3>

    <div class="ez-po-field" data-type="textarea">
      <label for="prescription_note" data-hint="هر توضیح اضافه‌ای که فکر می‌کنی مهمه — حساسیت به نور، ترجیح برند و...">
        <span class="ez-po-label-text">توضیحات شما</span>
        <span class="ez-po-hint-icon">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="12" cy="12" r="10"/>
            <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/>
            <line x1="12" y1="17" x2="12.01" y2="17"/>
          </svg>
        </span>
      </label>
      <div class="ez-po-input-wrap ez-po-textarea-wrap">
        <span class="ez-po-input-icon">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
            <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
          </svg>
        </span>
        <textarea id="prescription_note" name="prescription_note" rows="4" placeholder="مثلاً ترجیح پوشش ضدبازتاب، حساسیت به نور، …"></textarea>
        <span class="ez-po-status"></span>
      </div>
    </div>
  </div>

</div>


<!-- ============================================================
     CSS
     ============================================================ -->
<style id="ez-po-styles">

/* ============================================================
   RESET & ROOT
   ============================================================ */
*, *::before, *::after { box-sizing: border-box; }

.ez-po-wrap {
    --po-primary: #3b82f6;
    --po-primary-dark: #2563eb;
    --po-accent: #8b5cf6;
    --po-success: #10b981;
    --po-danger: #ef4444;
    --po-warning: #f59e0b;

    --po-text: #0f172a;
    --po-text-soft: #334155;
    --po-text-muted: #64748b;
    --po-text-faint: #94a3b8;

    --po-border: #e8ecf1;
    --po-border-soft: #eef2f7;
    --po-bg-soft: #f8fafc;

    --po-radius-sm: 10px;
    --po-radius: 14px;
    --po-radius-lg: 20px;
    --po-radius-xl: 26px;

    font-family: \'Vazirmatn\', Tahoma, sans-serif;
    direction: rtl;
    max-width: 860px;
    margin: 0 auto;
    padding: 40px 36px;
    background:
        radial-gradient(circle at 100% 0%, rgba(59, 130, 246, 0.05), transparent 40%),
        radial-gradient(circle at 0% 100%, rgba(139, 92, 246, 0.05), transparent 40%),
        linear-gradient(180deg, #ffffff 0%, #fbfcfe 100%);
    border: 1px solid var(--po-border-soft);
    border-radius: var(--po-radius-xl);
    box-shadow:
        0 1px 3px rgba(15, 23, 42, 0.03),
        0 24px 70px -24px rgba(15, 23, 42, 0.1);
    color: var(--po-text);
    line-height: 1.7;
    position: relative;
    overflow: visible;
}

/* ============================================================
   HEADER
   ============================================================ */
.ez-po-header {
    display: flex;
    align-items: center;
    gap: 16px;
    padding: 0 0 28px;
    margin-bottom: 28px;
    border-bottom: 2px solid var(--po-border-soft);
    position: relative;
}

.ez-po-header::after {
    content: "";
    position: absolute;
    bottom: -2px;
    right: 0;
    width: 80px;
    height: 2px;
    background: linear-gradient(90deg, var(--po-primary), var(--po-accent));
    border-radius: 2px;
}

.ez-po-header-icon {
    width: 52px;
    height: 52px;
    flex-shrink: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, var(--po-primary), var(--po-accent));
    border-radius: 16px;
    color: #fff;
    box-shadow: 0 8px 20px -4px rgba(59, 130, 246, 0.4);
    animation: ezPoPulse 3s ease-in-out infinite;
}

@keyframes ezPoPulse {
    0%, 100% { box-shadow: 0 8px 20px -4px rgba(59, 130, 246, 0.4); }
    50% { box-shadow: 0 8px 28px -2px rgba(59, 130, 246, 0.55); }
}

.ez-po-header-icon svg {
    width: 26px;
    height: 26px;
    stroke-width: 1.8;
}

.ez-po-header-text { flex: 1; min-width: 0; }

.ez-po-header-text h2 {
    margin: 0 0 2px;
    font-size: 22px;
    font-weight: 800;
    color: var(--po-text);
    letter-spacing: -0.5px;
}

.ez-po-header-text p {
    margin: 0;
    font-size: 13px;
    color: var(--po-text-muted);
    line-height: 1.6;
}

/* ============================================================
   SECTIONS
   ============================================================ */
.ez-po-section {
    margin-bottom: 28px;
    padding: 22px 22px 24px;
    background: #fff;
    border: 1px solid var(--po-border);
    border-radius: var(--po-radius-lg);
    transition: all .3s ease;
    position: relative;
    overflow: visible;
}

.ez-po-section:hover {
    border-color: #dbe4ff;
    box-shadow: 0 8px 32px -12px rgba(15, 23, 42, 0.08);
}

/* Section variants */
.ez-po-section-od {
    background: linear-gradient(180deg, #ffffff 0%, #f9fbff 100%);
    border-color: #dbeafe;
}
.ez-po-section-od:hover {
    border-color: #bfdbfe;
    box-shadow: 0 8px 32px -12px rgba(59, 130, 246, 0.15);
}

.ez-po-section-os {
    background: linear-gradient(180deg, #ffffff 0%, #fdfbff 100%);
    border-color: #ede9fe;
}
.ez-po-section-os:hover {
    border-color: #ddd6fe;
    box-shadow: 0 8px 32px -12px rgba(139, 92, 246, 0.15);
}

.ez-po-section-pd {
    background: linear-gradient(180deg, #ffffff 0%, #fbfffc 100%);
    border-color: #d1fae5;
}
.ez-po-section-pd:hover {
    border-color: #a7f3d0;
    box-shadow: 0 8px 32px -12px rgba(16, 185, 129, 0.15);
}

.ez-po-section-lens {
    background: linear-gradient(180deg, #ffffff 0%, #fffdf8 100%);
    border-color: #fed7aa;
}
.ez-po-section-lens:hover {
    border-color: #fdba74;
    box-shadow: 0 8px 32px -12px rgba(249, 115, 22, 0.15);
}

/* ============================================================
   HEADINGS
   ============================================================ */
.ez-po-heading {
    display: flex;
    align-items: center;
    gap: 10px;
    margin: 0 0 18px;
    padding: 0 0 14px;
    font-size: 14.5px;
    font-weight: 700;
    color: var(--po-text);
    border-bottom: 1.5px dashed var(--po-border-soft);
    position: relative;
}

.ez-po-heading-icon {
    width: 30px;
    height: 30px;
    flex-shrink: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 9px;
    transition: all .3s;
}

.ez-po-heading-icon svg {
    width: 15px;
    height: 15px;
    stroke-width: 2;
}

.ez-po-heading-icon-od {
    background: linear-gradient(135deg, #dbeafe, #bfdbfe);
    color: #1e40af;
}
.ez-po-heading-icon-os {
    background: linear-gradient(135deg, #ede9fe, #ddd6fe);
    color: #6d28d9;
}
.ez-po-heading-icon-pd {
    background: linear-gradient(135deg, #d1fae5, #a7f3d0);
    color: #047857;
}
.ez-po-heading-icon-lens {
    background: linear-gradient(135deg, #fed7aa, #fdba74);
    color: #c2410c;
}
.ez-po-heading-icon-upload {
    background: linear-gradient(135deg, #e0e7ff, #c7d2fe);
    color: #4338ca;
}
.ez-po-heading-icon-note {
    background: linear-gradient(135deg, #fce7f3, #fbcfe8);
    color: #be185d;
}

.ez-po-optional {
    margin-right: auto;
    padding: 3px 10px;
    background: var(--po-bg-soft);
    color: var(--po-text-faint);
    font-size: 10.5px;
    font-weight: 700;
    border-radius: 100px;
    border: 1px solid var(--po-border);
    letter-spacing: 0.3px;
}

/* ============================================================
   GRID
   ============================================================ */
.ez-po-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 16px;
}

.ez-po-grid-4 {
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 14px;
}

/* ============================================================
   FIELD
   ============================================================ */
.ez-po-field {
    display: flex;
    flex-direction: column;
    gap: 8px;
    min-width: 0;
    position: relative;
}

.ez-po-field > label {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 12.5px;
    font-weight: 600;
    color: var(--po-text-soft);
    line-height: 1.4;
    margin: 0;
    padding: 0;
    letter-spacing: -0.1px;
    cursor: default;
}

.ez-po-label-text {
    flex: 0 1 auto;
    min-width: 0;
    overflow: hidden;
    text-overflow: ellipsis;
}

/* Hint Icon (?) */
.ez-po-hint-icon {
    width: 15px;
    height: 15px;
    flex-shrink: 0;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    color: var(--po-text-faint);
    cursor: help;
    transition: all .2s ease;
    position: relative;
    margin-right: auto;
}

.ez-po-hint-icon svg {
    width: 100%;
    height: 100%;
    stroke-width: 2;
    transition: all .2s ease;
}

.ez-po-hint-icon:hover {
    color: var(--po-primary);
    transform: scale(1.15);
}

/* Tooltip via data-hint on label */
.ez-po-field > label[data-hint] {
    position: relative;
}

.ez-po-field > label[data-hint]::before {
    content: attr(data-hint);
    position: absolute;
    bottom: calc(100% + 10px);
    right: 0;
    left: auto;
    min-width: 180px;
    max-width: 280px;
    background: #0f172a;
    color: #fff;
    font-size: 11.5px;
    font-weight: 500;
    line-height: 1.8;
    padding: 10px 14px;
    border-radius: 10px;
    opacity: 0;
    visibility: hidden;
    transform: translateY(6px);
    transition: opacity .25s, transform .25s, visibility .25s;
    z-index: 99;
    pointer-events: none;
    box-shadow: 0 12px 32px rgba(15, 23, 42, 0.35);
    text-align: right;
    white-space: normal;
    word-break: break-word;
}

.ez-po-field > label[data-hint]::after {
    content: "";
    position: absolute;
    bottom: calc(100% + 5px);
    right: 12px;
    width: 8px;
    height: 8px;
    background: #0f172a;
    transform: rotate(45deg) translateY(6px);
    opacity: 0;
    visibility: hidden;
    transition: all .25s;
    z-index: 99;
    pointer-events: none;
}

.ez-po-field > label[data-hint]:hover::before,
.ez-po-field > label[data-hint]:hover::after {
    opacity: 1;
    visibility: visible;
    transform: translateY(0);
}

.ez-po-field > label[data-hint]:hover::after {
    transform: rotate(45deg) translateY(2px);
}

/* ============================================================
   INPUT WRAPPER
   ============================================================ */
.ez-po-input-wrap {
    position: relative;
    display: flex;
    align-items: stretch;
    background: #fff;
    border: 1.5px solid var(--po-border);
    border-radius: var(--po-radius);
    transition: all .25s ease;
    overflow: hidden;
    box-shadow: 0 1px 2px rgba(15, 23, 42, 0.02);
}

.ez-po-input-wrap:hover {
    border-color: #cbd5e1;
}

.ez-po-input-wrap:focus-within {
    border-color: var(--po-primary);
    box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
    background: #fff;
}

/* Icon inside input */
.ez-po-input-icon {
    width: 44px;
    flex-shrink: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--po-text-faint);
    transition: color .25s;
    pointer-events: none;
}

.ez-po-input-icon svg {
    width: 16px;
    height: 16px;
    stroke-width: 1.8;
}

.ez-po-input-wrap:focus-within .ez-po-input-icon {
    color: var(--po-primary);
}

/* Inputs / selects / textareas */
.ez-po-input-wrap input,
.ez-po-input-wrap select,
.ez-po-input-wrap textarea {
    flex: 1;
    min-width: 0;
    padding: 12px 14px 12px 0;
    font-family: inherit;
    font-size: 14px;
    font-weight: 500;
    color: var(--po-text);
    background: transparent;
    border: 0;
    outline: 0;
    line-height: 1.5;
    min-height: 48px;
    margin: 0;
    text-align: right;
    direction: rtl;
}

.ez-po-input-wrap input[type="number"] {
    direction: ltr;
    text-align: left;
    font-variant-numeric: tabular-nums;
    letter-spacing: 0.3px;
}

/* Number with no icon - full width */
.ez-po-input-num input {
    padding-right: 14px;
}

.ez-po-input-wrap input::placeholder,
.ez-po-input-wrap textarea::placeholder {
    color: #cbd5e1;
    font-weight: 400;
}

.ez-po-input-wrap select {
    cursor: pointer;
    -webkit-appearance: none;
    appearance: none;
    background-image: url("data:image/svg+xml;utf8,<svg xmlns=\'http://www.w3.org/2000/svg\' viewBox=\'0 0 24 24\' fill=\'none\' stroke=\'%2364748b\' stroke-width=\'2\' stroke-linecap=\'round\' stroke-linejoin=\'round\'><polyline points=\'6 9 12 15 18 9\'/></svg>");
    background-repeat: no-repeat;
    background-position: left 14px center;
    background-size: 14px;
    padding-left: 40px;
}

/* Textarea */
.ez-po-textarea-wrap {
    align-items: flex-start;
    padding-top: 2px;
}
.ez-po-textarea-wrap .ez-po-input-icon {
    padding-top: 15px;
    height: auto;
    align-self: flex-start;
}
.ez-po-textarea-wrap textarea {
    min-height: 100px;
    resize: vertical;
    line-height: 1.9;
    padding-top: 14px;
    padding-bottom: 14px;
}

/* Number spinners */
.ez-po-input-wrap input[type="number"] {
    -moz-appearance: textfield;
    appearance: textfield;
}
.ez-po-input-wrap input[type="number"]::-webkit-outer-spin-button,
.ez-po-input-wrap input[type="number"]::-webkit-inner-spin-button {
    -webkit-appearance: none;
    margin: 0;
}

/* ============================================================
   VALIDATION STATUS
   ============================================================ */
.ez-po-status {
    width: 34px;
    flex-shrink: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transform: scale(.5);
    transition: opacity .3s, transform .3s cubic-bezier(.34, 1.56, .64, 1);
    pointer-events: none;
}

.ez-po-input-wrap.is-valid .ez-po-status {
    opacity: 1;
    transform: scale(1);
}

.ez-po-input-wrap.is-valid .ez-po-status::before {
    content: "";
    width: 18px;
    height: 18px;
    background: var(--po-success);
    border-radius: 50%;
    display: block;
    position: relative;
    box-shadow: 0 2px 6px rgba(16, 185, 129, 0.3);
}

.ez-po-input-wrap.is-valid .ez-po-status::after {
    content: "";
    position: absolute;
    width: 9px;
    height: 5px;
    border-left: 2px solid #fff;
    border-bottom: 2px solid #fff;
    transform: rotate(-45deg) translate(-1px, -3px);
}

.ez-po-input-wrap.is-valid {
    border-color: var(--po-success);
    background: #f0fdf4;
}

.ez-po-input-wrap.is-valid .ez-po-input-icon {
    color: var(--po-success);
}

.ez-po-input-wrap.is-error {
    border-color: var(--po-danger);
    background: #fef2f2;
    animation: ezPoShake .4s;
}

.ez-po-input-wrap.is-error .ez-po-input-icon {
    color: var(--po-danger);
}

.ez-po-input-wrap.is-error .ez-po-status {
    opacity: 1;
    transform: scale(1);
}

.ez-po-input-wrap.is-error .ez-po-status::before {
    content: "";
    width: 18px;
    height: 18px;
    background: var(--po-danger);
    border-radius: 50%;
    display: block;
    box-shadow: 0 2px 6px rgba(239, 68, 68, 0.3);
}

.ez-po-input-wrap.is-error .ez-po-status::after {
    content: "";
    position: absolute;
    width: 9px;
    height: 2px;
    background: #fff;
    border-radius: 1px;
}

@keyframes ezPoShake {
    0%, 100% { transform: translateX(0); }
    20% { transform: translateX(-5px); }
    40% { transform: translateX(5px); }
    60% { transform: translateX(-3px); }
    80% { transform: translateX(3px); }
}

/* ============================================================
   UPLOAD BOX
   ============================================================ */
.ez-po-upload-field {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.ez-po-upload {
    position: relative;
    border: 2px dashed var(--po-border);
    border-radius: var(--po-radius-lg);
    background: linear-gradient(180deg, #fafbfc 0%, #f5f7fb 100%);
    padding: 32px 24px;
    cursor: pointer;
    transition: all .3s cubic-bezier(.4, 0, .2, 1);
    overflow: hidden;
    min-height: 200px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.ez-po-upload::before {
    content: "";
    position: absolute;
    inset: 0;
    background:
        radial-gradient(circle at 50% 0%, rgba(59, 130, 246, 0.06), transparent 60%);
    opacity: 0;
    transition: opacity .4s;
    pointer-events: none;
}

.ez-po-upload:hover {
    border-color: var(--po-primary);
    background: linear-gradient(180deg, #f5f9ff 0%, #eef5ff 100%);
    transform: translateY(-2px);
    box-shadow: 0 12px 40px -12px rgba(59, 130, 246, 0.2);
}

.ez-po-upload:hover::before {
    opacity: 1;
}

.ez-po-upload[data-dragover="true"] {
    border-color: var(--po-primary);
    background: linear-gradient(180deg, #eff6ff 0%, #dbeafe 100%);
    transform: scale(1.01);
    box-shadow:
        0 0 0 4px rgba(59, 130, 246, 0.15),
        0 20px 60px -20px rgba(59, 130, 246, 0.3);
}

.ez-po-upload[data-dragover="true"] .ez-po-upload-icon-ring {
    animation: ezPoRingPulse .8s ease-in-out infinite;
}

@keyframes ezPoRingPulse {
    0%, 100% { transform: scale(1); opacity: 1; }
    50% { transform: scale(1.15); opacity: .6; }
}

/* Empty State */
.ez-po-upload-empty {
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
    gap: 12px;
    position: relative;
    z-index: 1;
    transition: opacity .3s, transform .3s;
}

.ez-po-upload.is-has-file .ez-po-upload-empty {
    display: none;
}

.ez-po-upload-icon-wrap {
    position: relative;
    width: 64px;
    height: 64px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 4px;
}

.ez-po-upload-icon-ring {
    position: absolute;
    inset: 0;
    border-radius: 50%;
    background: linear-gradient(135deg, #dbeafe, #e0e7ff);
    animation: ezPoFloat 3s ease-in-out infinite;
}

@keyframes ezPoFloat {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-4px); }
}

.ez-po-upload-icon {
    position: relative;
    z-index: 1;
    width: 26px;
    height: 26px;
    color: var(--po-primary);
    stroke-width: 1.8;
    animation: ezPoIconBounce 2s ease-in-out infinite;
}

@keyframes ezPoIconBounce {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-3px); }
}

.ez-po-upload-title {
    font-size: 15px;
    font-weight: 700;
    color: var(--po-text);
    letter-spacing: -0.2px;
}

.ez-po-upload-sub {
    font-size: 13px;
    color: var(--po-text-muted);
    line-height: 1.5;
}

.ez-po-upload-link {
    color: var(--po-primary);
    font-weight: 700;
    text-decoration: underline;
    text-decoration-style: dashed;
    text-underline-offset: 3px;
}

.ez-po-upload-formats {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
    justify-content: center;
    margin-top: 6px;
}

.ez-po-format-badge {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 4px 10px;
    background: #fff;
    border: 1px solid var(--po-border);
    border-radius: 100px;
    font-size: 10.5px;
    font-weight: 700;
    color: var(--po-text-muted);
    transition: all .2s;
}

.ez-po-format-badge svg {
    width: 11px;
    height: 11px;
    stroke-width: 2;
    color: var(--po-text-faint);
}

.ez-po-upload:hover .ez-po-format-badge {
    border-color: #bfdbfe;
    color: #1e40af;
}

/* Preview State */
.ez-po-upload-preview {
    display: none;
    align-items: center;
    gap: 14px;
    width: 100%;
    padding: 4px 0;
    position: relative;
    z-index: 1;
    animation: ezPoPreviewIn .4s cubic-bezier(.34, 1.56, .64, 1);
}

.ez-po-upload.is-has-file .ez-po-upload-preview {
    display: flex;
}

@keyframes ezPoPreviewIn {
    from { opacity: 0; transform: translateY(8px) scale(.96); }
    to { opacity: 1; transform: translateY(0) scale(1); }
}

.ez-po-preview-thumb {
    width: 72px;
    height: 72px;
    flex-shrink: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 16px;
    background: linear-gradient(135deg, var(--po-primary), var(--po-accent));
    color: #fff;
    overflow: hidden;
    position: relative;
    box-shadow: 0 8px 24px -6px rgba(59, 130, 246, 0.4);
}

.ez-po-preview-thumb img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
}

.ez-po-preview-thumb svg {
    width: 32px;
    height: 32px;
    stroke-width: 1.6;
}

.ez-po-preview-info {
    flex: 1;
    min-width: 0;
    display: flex;
    flex-direction: column;
    gap: 4px;
    text-align: right;
}

.ez-po-preview-name {
    font-size: 14px;
    font-weight: 700;
    color: var(--po-text);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.ez-po-preview-size {
    font-size: 12px;
    color: var(--po-text-muted);
    font-variant-numeric: tabular-nums;
}

.ez-po-preview-remove {
    width: 38px;
    height: 38px;
    flex-shrink: 0;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: #fef2f2;
    color: var(--po-danger);
    border: 1.5px solid #fecaca;
    border-radius: 12px;
    cursor: pointer;
    transition: all .2s cubic-bezier(.34, 1.56, .64, 1);
    font-family: inherit;
}

.ez-po-preview-remove:hover {
    background: var(--po-danger);
    color: #fff;
    border-color: var(--po-danger);
    transform: scale(1.08) rotate(90deg);
    box-shadow: 0 8px 20px -4px rgba(239, 68, 68, 0.4);
}

.ez-po-preview-remove svg {
    width: 16px;
    height: 16px;
    stroke-width: 2.5;
}

/* Progress */
.ez-po-upload-progress {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: transparent;
    overflow: hidden;
    opacity: 0;
    transition: opacity .3s;
}

.ez-po-upload-progress.is-active {
    opacity: 1;
}

.ez-po-upload-progress-bar {
    height: 100%;
    width: 0;
    background: linear-gradient(90deg, var(--po-primary), var(--po-accent));
    border-radius: 0;
    transition: width .2s ease-out;
}

/* Note */
.ez-po-upload-note {
    display: flex;
    align-items: flex-start;
    gap: 8px;
    padding: 10px 14px;
    background: #f0f9ff;
    border: 1px solid #bae6fd;
    border-radius: var(--po-radius-sm);
    font-size: 12px;
    color: #075985;
    line-height: 1.7;
}

.ez-po-upload-note svg {
    width: 14px;
    height: 14px;
    flex-shrink: 0;
    margin-top: 3px;
    stroke-width: 2;
}

/* ============================================================
   RESPONSIVE
   ============================================================ */
@media (max-width: 720px) {
    .ez-po-wrap {
        padding: 28px 20px;
        border-radius: var(--po-radius-lg);
    }

    .ez-po-header-icon {
        width: 44px;
        height: 44px;
        border-radius: 13px;
    }
    .ez-po-header-icon svg { width: 22px; height: 22px; }
    .ez-po-header-text h2 { font-size: 18px; }
    .ez-po-header-text p { font-size: 12px; }

    .ez-po-section {
        padding: 18px 16px;
        border-radius: var(--po-radius);
    }

    .ez-po-grid,
    .ez-po-grid-4 {
        grid-template-columns: 1fr;
        gap: 14px;
    }

    .ez-po-upload {
        padding: 26px 18px;
        min-height: 170px;
    }

    .ez-po-upload-icon-wrap {
        width: 54px;
        height: 54px;
    }

    .ez-po-upload-icon { width: 22px; height: 22px; }

    .ez-po-upload-title { font-size: 14px; }
    .ez-po-upload-sub { font-size: 12px; }

    .ez-po-field > label[data-hint]::before {
        right: 0;
        left: 0;
        max-width: 100%;
    }

    .ez-po-preview-thumb {
        width: 60px;
        height: 60px;
        border-radius: 13px;
    }
}

@media (max-width: 420px) {
    .ez-po-wrap { padding: 22px 14px; }
    .ez-po-header-text h2 { font-size: 16px; }
    .ez-po-heading { font-size: 13.5px; }
    .ez-po-input-wrap input,
    .ez-po-input-wrap select,
    .ez-po-input-wrap textarea {
        font-size: 13.5px;
        min-height: 46px;
    }
}

@media (prefers-reduced-motion: reduce) {
    .ez-po-wrap *,
    .ez-po-wrap *::before,
    .ez-po-wrap *::after {
        animation-duration: .01ms !important;
        transition-duration: .01ms !important;
    }
}

</style>


<!-- ============================================================
     JS
     ============================================================ -->
<script>
(function() {
    \'use strict\';

    document.addEventListener(\'DOMContentLoaded\', function() {
        const wrap = document.getElementById(\'ez-po-wrap\');
        if (!wrap) return;

        /* ============================================
           1. VALIDATION SYSTEM
           ============================================ */
        const inputs = wrap.querySelectorAll(\'input, select, textarea\');

        function validateField(el) {
            const value = el.value.trim();
            const inputWrap = el.closest(\'.ez-po-input-wrap\');
            if (!inputWrap) return;

            // remove old state
            inputWrap.classList.remove(\'is-valid\', \'is-error\');

            // skip empty & not required
            if (!value) {
                if (el.required) {
                    inputWrap.classList.add(\'is-error\');
                }
                return;
            }

            let isValid = true;

            // Custom rules
            if (el.type === \'email\') {
                isValid = /^[^\\s@]+@[^\\s@]+\\.[^\\s@]+$/.test(value);
            } else if (el.type === \'tel\') {
                isValid = /^09\\d{9}$/.test(value.replace(/\\s/g, \'\'));
            } else if (el.type === \'number\') {
                isValid = !isNaN(parseFloat(value));
                const min = el.getAttribute(\'min\');
                const max = el.getAttribute(\'max\');
                if (min !== null && parseFloat(value) < parseFloat(min)) isValid = false;
                if (max !== null && parseFloat(value) > parseFloat(max)) isValid = false;
            } else if (el.tagName === \'TEXTAREA\') {
                isValid = value.length >= 3;
            } else if (el.tagName === \'SELECT\') {
                isValid = value !== \'\';
            } else {
                isValid = value.length >= 2;
            }

            if (isValid) {
                inputWrap.classList.add(\'is-valid\');
            } else {
                inputWrap.classList.add(\'is-error\');
            }
        }

        inputs.forEach(function(el) {
            el.addEventListener(\'blur\', function() { validateField(el); });
            el.addEventListener(\'change\', function() { validateField(el); });
            el.addEventListener(\'input\', function() {
                const inputWrap = el.closest(\'.ez-po-input-wrap\');
                if (!inputWrap) return;
                // If currently in error, live-validate on input
                if (inputWrap.classList.contains(\'is-error\')) {
                    validateField(el);
                }
            });
        });

        /* ============================================
           2. PD MODE TOGGLE
           ============================================ */
        const pdType = document.getElementById(\'pd_type\');
        const pdBinocular = document.querySelector(\'.ez-po-field[data-pd-mode="binocular"]\');
        const pdMonocular = document.querySelectorAll(\'.ez-po-field[data-pd-mode="monocular"]\');

        function updatePDVisibility() {
            if (!pdType) return;
            const mode = pdType.value;

            if (pdBinocular) {
                if (mode === \'binocular\') {
                    pdBinocular.style.display = \'\';
                    pdBinocular.animate([
                        { opacity: 0, transform: \'translateY(-8px)\' },
                        { opacity: 1, transform: \'translateY(0)\' }
                    ], { duration: 300, easing: \'cubic-bezier(.34,1.56,.64,1)\' });
                } else {
                    pdBinocular.style.display = \'none\';
                }
            }
            pdMonocular.forEach(function(el) {
                if (mode === \'monocular\') {
                    el.style.display = \'\';
                    el.animate([
                        { opacity: 0, transform: \'translateY(-8px)\' },
                        { opacity: 1, transform: \'translateY(0)\' }
                    ], { duration: 300, easing: \'cubic-bezier(.34,1.56,.64,1)\' });
                } else {
                    el.style.display = \'none\';
                }
            });
        }

        if (pdType) {
            pdType.addEventListener(\'change\', updatePDVisibility);
            updatePDVisibility();
        }

        /* ============================================
           3. AXIS VALIDATION (0-180)
           ============================================ */
        document.querySelectorAll(\'input[name="od_axis"], input[name="os_axis"]\').forEach(function(input) {
            input.addEventListener(\'blur\', function() {
                let val = parseFloat(input.value);
                if (isNaN(val)) return;
                if (val < 0) input.value = 0;
                if (val > 180) input.value = 180;
                validateField(input);
            });
        });

        /* ============================================
           4. FILE UPLOAD SYSTEM
           ============================================ */
        const uploadZone = document.getElementById(\'rx_upload\');
        const fileInput = document.getElementById(\'rx_file\');
        const uploadEmpty = document.getElementById(\'rx_upload_empty\');
        const uploadPreview = document.getElementById(\'rx_upload_preview\');
        const previewThumb = document.getElementById(\'rx_preview_thumb\');
        const previewName = document.getElementById(\'rx_preview_name\');
        const previewSize = document.getElementById(\'rx_preview_size\');
        const previewRemove = document.getElementById(\'rx_preview_remove\');
        const uploadProgress = document.getElementById(\'rx_upload_progress\');
        const uploadProgressBar = document.getElementById(\'rx_upload_progress_bar\');

        const MAX_SIZE = 5 * 1024 * 1024; // 5MB
        const ALLOWED_TYPES = [\'image/jpeg\', \'image/png\', \'image/jpg\', \'image/webp\', \'application/pdf\'];

        function formatSize(bytes) {
            if (bytes < 1024) return bytes + \' بایت\';
            if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + \' کیلوبایت\';
            return (bytes / (1024 * 1024)).toFixed(2) + \' مگابایت\';
        }

        function showUploadError(message) {
            uploadZone.animate([
                { transform: \'translateX(0)\' },
                { transform: \'translateX(-6px)\' },
                { transform: \'translateX(6px)\' },
                { transform: \'translateX(-4px)\' },
                { transform: \'translateX(4px)\' },
                { transform: \'translateX(0)\' }
            ], { duration: 400 });
            // Temporary visual cue
            uploadZone.style.borderColor = \'#ef4444\';
            uploadZone.style.background = \'#fef2f2\';
            setTimeout(function() {
                uploadZone.style.borderColor = \'\';
                uploadZone.style.background = \'\';
            }, 1500);
            // Show message
            if (window.console && console.warn) console.warn(\'[EzPo Upload]\', message);
            alert(message);
        }

        function renderPreview(file) {
            previewName.textContent = file.name;
            previewSize.textContent = formatSize(file.size);

            // Clear old
            previewThumb.innerHTML = \'\';

            if (file.type.startsWith(\'image/\')) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const img = document.createElement(\'img\');
                    img.src = e.target.result;
                    img.alt = file.name;
                    previewThumb.appendChild(img);
                };
                reader.readAsDataURL(file);
            } else {
                // PDF icon
                previewThumb.innerHTML =
                    \'<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">\' +
                        \'<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>\' +
                        \'<polyline points="14 2 14 8 20 8"/>\' +
                        \'<line x1="9" y1="15" x2="15" y2="15"/>\' +
                    \'</svg>\';
            }
        }

        function handleFile(file) {
            if (!file) return;

            if (file.size > MAX_SIZE) {
                showUploadError(\'حجم فایل بیشتر از ۵ مگابایت است\');
                return;
            }
            if (ALLOWED_TYPES.indexOf(file.type) === -1) {
                showUploadError(\'فقط فرمت‌های JPG، PNG، WebP و PDF مجاز هستند\');
                return;
            }

            // Simulate progress
            uploadProgress.classList.add(\'is-active\');
            uploadProgressBar.style.width = \'0%\';

            let progress = 0;
            const interval = setInterval(function() {
                progress += Math.random() * 18 + 8;
                if (progress >= 100) {
                    progress = 100;
                    uploadProgressBar.style.width = \'100%\';
                    clearInterval(interval);
                    setTimeout(function() {
                        uploadProgress.classList.remove(\'is-active\');
                        uploadProgressBar.style.width = \'0%\';
                        uploadZone.classList.add(\'is-has-file\');
                        renderPreview(file);
                    }, 300);
                } else {
                    uploadProgressBar.style.width = progress + \'%\';
                }
            }, 100);
        }

        function clearFile() {
            fileInput.value = \'\';
            uploadZone.classList.remove(\'is-has-file\');
            previewThumb.innerHTML = \'\';
            previewName.textContent = \'—\';
            previewSize.textContent = \'—\';
        }

        // Click to upload
        uploadZone.addEventListener(\'click\', function(e) {
            if (e.target.closest(\'.ez-po-preview-remove\')) return;
            if (uploadZone.classList.contains(\'is-has-file\')) return;
            fileInput.click();
        });

        fileInput.addEventListener(\'change\', function(e) {
            if (e.target.files.length > 0) {
                handleFile(e.target.files[0]);
            }
        });

        // Drag & Drop
        [\'dragenter\', \'dragover\'].forEach(function(evt) {
            uploadZone.addEventListener(evt, function(e) {
                e.preventDefault();
                e.stopPropagation();
                uploadZone.setAttribute(\'data-dragover\', \'true\');
            });
        });

        [\'dragleave\', \'drop\'].forEach(function(evt) {
            uploadZone.addEventListener(evt, function(e) {
                e.preventDefault();
                e.stopPropagation();
                uploadZone.setAttribute(\'data-dragover\', \'false\');
            });
        });

        uploadZone.addEventListener(\'drop\', function(e) {
            const files = e.dataTransfer.files;
            if (files.length > 0) handleFile(files[0]);
        });

        // Remove file with animation
        previewRemove.addEventListener(\'click\', function(e) {
            e.stopPropagation();
            uploadZone.animate([
                { opacity: 1, transform: \'scale(1)\' },
                { opacity: 0, transform: \'scale(.95)\' }
            ], { duration: 250, easing: \'ease-out\' }).onfinish = function() {
                clearFile();
                uploadZone.animate([
                    { opacity: 0, transform: \'scale(.95)\' },
                    { opacity: 1, transform: \'scale(1)\' }
                ], { duration: 300, easing: \'cubic-bezier(.34,1.56,.64,1)\' });
            };
        });

        /* ============================================
           5. ADD HIGHLIGHT FOR PROGRESSIVE/BIFOCAL
           ============================================ */
        const useType = document.getElementById(\'use_type\');
        function highlightAdd() {
            if (!useType) return;
            const mode = useType.value;
            const needsAdd = (mode === \'progressive\' || mode === \'bifocal\');
            document.querySelectorAll(\'input[name="od_add"], input[name="os_add"]\').forEach(function(input) {
                const field = input.closest(\'.ez-po-field\');
                if (!field) return;
                const label = field.querySelector(\'.ez-po-label-text\');
                if (label) {
                    if (needsAdd) {
                        label.style.color = \'#c2410c\';
                        label.style.fontWeight = \'700\';
                    } else {
                        label.style.color = \'\';
                        label.style.fontWeight = \'\';
                    }
                }
            });
        }
        if (useType) {
            useType.addEventListener(\'change\', highlightAdd);
            highlightAdd();
        }

    });

})();
</script>ned',
  'code_file' => '15-d986d8b3d8aed987-d8b9db8cd986daa9-d8b7d8a8db8c.code.html',
  'is_preset' => 0,
  'preset_slug' => '',
  'updated_at' => '2026-09-19 17:04:40',
);
