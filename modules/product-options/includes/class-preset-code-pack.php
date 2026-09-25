<?php
/** Professional ready palette HTML/CSS/JS packs. */
if (!defined('ABSPATH')) { exit; }
class EzLens_PO_Preset_Code_Pack {
  public static function for_slug($slug) {
    $all = self::all();
    return isset($all[$slug]) ? $all[$slug] : null;
  }
  public static function all() {
    return array(
      'glasses-prescription' => array(
        'html' => <<<'EZCODE'
<!-- نسخه عینک طبی -->
<div class="ez-rx" dir="rtl">
  <header class="ez-rx__hero ez-rx__hero--blue">
    <div class="ez-rx__hero-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="6" cy="12" r="3.2"/><circle cx="18" cy="12" r="3.2"/><path d="M9.2 12h5.6"/><path d="M3 12H1.5"/><path d="M23 12h-1.5"/></svg></div>
    <div><h2 class="ez-rx__title">نسخه عینک طبی</h2><p class="ez-rx__sub">اعداد را دقیقاً مطابق برگه پزشک وارد کنید یا تصویر نسخه را آپلود نمایید.</p></div>
  </header>
  <div class="ez-rx__tip"><span class="ez-rx__tip-ico"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><path d="M12 10v6"/><path d="M12 7h.01"/></svg></span><div><strong>راهنما:</strong> SPH نزدیک‌بینی منفی (−) و دوربینی مثبت (+) است. AXIS فقط با CYL معنا دارد (۰ تا ۱۸۰).</div></div>

  <section class="ez-rx__card ez-rx__card--upload">
    <div class="ez-rx__card-head">
      <span class="ez-rx__card-ico" aria-hidden="true">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><path d="m17 8-5-5-5 5"/><path d="M12 3v12"/></svg>
      </span>
      <div>
        <h3>آپلود نسخه پزشک</h3>
        <p>می‌توانید به‌جای پر کردن فرم، تصویر یا PDF نسخه را بفرستید</p>
      </div>
    </div>
    <label class="ez-rx__upload-zone" for="rx_upload">
      <input type="file" id="rx_upload" name="rx_upload" accept="image/*,.pdf,application/pdf">
      <strong>تصویر یا PDF نسخه را انتخاب یا اینجا رها کنید</strong>
      <span>JPG · PNG · PDF — حداکثر ۱۰ مگابایت · بررسی توسط کارشناس</span>
      <div class="ez-rx__upload-name"></div>
    </label>
    <p style="margin:10px 0 0;font-size:11.5px;color:#047857;line-height:1.6;">پس از آپلود، تیم بینایی‌سنجی نسخه را بررسی می‌کند و در صورت نیاز با شما هماهنگ می‌شود.</p>
  </section>
  <div class="ez-rx__or">یا فرم زیر را پر کنید</div>

  <section class="ez-rx__card ez-rx__card--blue">
    <div class="ez-rx__card-head"><span class="ez-rx__card-ico"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M14 3H6a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9z"/><path d="M14 3v6h6"/></svg></span><div><h3>اطلاعات نسخه</h3><p>اختیاری</p></div></div>
    <div class="ez-rx__grid ez-rx__grid--2">
      <div class="ez-rx__field"><label for="rx_doctor">نام پزشک / مرکز</label><input type="text" id="rx_doctor" name="rx_doctor" placeholder="مثلاً دکتر …"></div>
      <div class="ez-rx__field"><label for="rx_date">تاریخ نسخه</label><input type="date" id="rx_date" name="rx_date"></div>
    </div>
  </section>
  <section class="ez-rx__card ez-rx__card--violet">
    <div class="ez-rx__card-head"><span class="ez-rx__card-ico"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="3"/><path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6S2 12 2 12z"/></svg></span><div><h3>چشم راست <span class="ez-rx__badge">OD</span></h3><p>ستون راست نسخه</p></div></div>
    <div class="ez-rx__grid ez-rx__grid--4">
      <div class="ez-rx__field"><label for="od_sph">SPH <span class="req">*</span></label><input type="number" id="od_sph" name="od_sph" step="0.25" placeholder="-۲.۵۰" inputmode="decimal" required><small>قدرت کروی</small></div>
      <div class="ez-rx__field"><label for="od_cyl">CYL</label><input type="number" id="od_cyl" name="od_cyl" step="0.25" placeholder="-۰.۷۵" inputmode="decimal"><small>آستیگماتیسم</small></div>
      <div class="ez-rx__field"><label for="od_axis">AXIS</label><input type="number" id="od_axis" name="od_axis" min="0" max="180" step="1" placeholder="۹۰" inputmode="numeric"><small>۰ تا ۱۸۰</small></div>
      <div class="ez-rx__field"><label for="od_add">ADD</label><input type="number" id="od_add" name="od_add" step="0.25" placeholder="+۱.۵۰" inputmode="decimal"><small>پیرچشمی</small></div>
    </div>
  </section>
  <section class="ez-rx__card ez-rx__card--teal">
    <div class="ez-rx__card-head"><span class="ez-rx__card-ico"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="3"/><path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6S2 12 2 12z"/></svg></span><div><h3>چشم چپ <span class="ez-rx__badge">OS</span></h3><p>ستون چپ نسخه</p></div></div>
    <div class="ez-rx__grid ez-rx__grid--4">
      <div class="ez-rx__field"><label for="os_sph">SPH <span class="req">*</span></label><input type="number" id="os_sph" name="os_sph" step="0.25" placeholder="-۱.۷۵" inputmode="decimal" required><small>قدرت کروی</small></div>
      <div class="ez-rx__field"><label for="os_cyl">CYL</label><input type="number" id="os_cyl" name="os_cyl" step="0.25" placeholder="-۰.۵۰" inputmode="decimal"><small>آستیگماتیسم</small></div>
      <div class="ez-rx__field"><label for="os_axis">AXIS</label><input type="number" id="os_axis" name="os_axis" min="0" max="180" step="1" placeholder="۸۵" inputmode="numeric"><small>۰ تا ۱۸۰</small></div>
      <div class="ez-rx__field"><label for="os_add">ADD</label><input type="number" id="os_add" name="os_add" step="0.25" placeholder="+۱.۵۰" inputmode="decimal"><small>پیرچشمی</small></div>
    </div>
  </section>
  <section class="ez-rx__card ez-rx__card--amber">
    <div class="ez-rx__card-head"><span class="ez-rx__card-ico"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 7h16"/><path d="M4 17h16"/><path d="M9 7v10"/><path d="M15 7v10"/></svg></span><div><h3>PD و کاربرد</h3><p>فاصله مردمکی و نوع استفاده</p></div></div>
    <div class="ez-rx__grid ez-rx__grid--3">
      <div class="ez-rx__field"><label for="pd_type">نوع PD <span class="req">*</span></label><select id="pd_type" name="pd_type" required><option value="">انتخاب کنید</option><option value="binocular">دوچشمی</option><option value="monocular">تک‌چشمی</option></select></div>
      <div class="ez-rx__field" data-pd="binocular"><label for="pd">PD دوچشمی</label><input type="number" id="pd" name="pd" min="40" max="80" step="0.5" placeholder="۶۲" inputmode="decimal"><small>معمولاً ۵۸ تا ۶۸</small></div>
      <div class="ez-rx__field ez-rx__hidden" data-pd="monocular"><label for="pd_od">PD راست</label><input type="number" id="pd_od" name="pd_od" step="0.5" placeholder="۳۱"></div>
      <div class="ez-rx__field ez-rx__hidden" data-pd="monocular"><label for="pd_os">PD چپ</label><input type="number" id="pd_os" name="pd_os" step="0.5" placeholder="۳۱"></div>
      <div class="ez-rx__field"><label for="use_type">کاربرد عینک <span class="req">*</span></label><select id="use_type" name="use_type" required><option value="">انتخاب کنید</option><option value="distance">دور</option><option value="near">نزدیک</option><option value="computer">کامپیوتر</option><option value="progressive">پروگرسیو</option><option value="bifocal">دوکانونی</option></select></div>
      <div class="ez-rx__field"><label for="lens_index">ضریب شکست</label><select id="lens_index" name="lens_index"><option value="">مهم نیست</option><option value="1.5">۱.۵۰</option><option value="1.56">۱.۵۶</option><option value="1.6">۱.۶۰</option><option value="1.67">۱.۶۷</option><option value="1.74">۱.۷۴</option></select></div>
    </div>
  </section>
  <section class="ez-rx__card ez-rx__card--slate">
    <div class="ez-rx__card-head"><span class="ez-rx__card-ico"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4Z"/></svg></span><div><h3>یادداشت</h3><p>برای اپتومتریست</p></div></div>
    <div class="ez-rx__field"><label for="rx_note">توضیحات</label><textarea id="rx_note" name="rx_note" rows="3" placeholder="پوشش ضدبازتاب، حساسیت به نور، …"></textarea></div>
  </section>

  <footer class="ez-rx__foot">
    <span class="ez-rx__verified">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><path d="m9 12 2 2 4-4"/></svg>
      فرم استاندارد — قابل بررسی توسط متخصص بینایی‌سنجی
    </span>
  </footer>

</div>
EZCODE,
        'css' => <<<'EZCODE'
.ez-rx{--rx-text:#0f172a;--rx-muted:#64748b;--rx-border:#e2e8f0;--rx-radius:16px;font-family:Tahoma,Arial,system-ui,sans-serif;color:var(--rx-text);max-width:920px;margin:0 auto;padding:8px;animation:ezRxFade .45s ease both}
@keyframes ezRxFade{from{opacity:0;transform:translateY(8px)}to{opacity:1;transform:none}}
.ez-rx__hero{display:flex;align-items:center;gap:14px;padding:18px;border-radius:var(--rx-radius);color:#fff;margin-bottom:14px;box-shadow:0 12px 28px rgba(15,23,42,.2)}
.ez-rx__hero-icon{width:48px;height:48px;border-radius:14px;display:grid;place-items:center;background:rgba(255,255,255,.12);flex-shrink:0}
.ez-rx__hero-icon svg{width:26px;height:26px}
.ez-rx__title{margin:0 0 4px;font-size:1.15rem;font-weight:800}
.ez-rx__sub{margin:0;font-size:12.5px;opacity:.9;line-height:1.6}
.ez-rx__tip{display:flex;gap:12px;align-items:flex-start;padding:12px 14px;margin-bottom:14px;border-radius:12px;background:linear-gradient(135deg,#eff6ff,#f0f9ff);border:1px solid #bfdbfe;color:#1e3a8a;font-size:12.5px;line-height:1.7}
.ez-rx__tip-ico{flex-shrink:0;width:22px;height:22px;color:#2563eb}
.ez-rx__tip-ico svg{width:22px;height:22px;display:block}
.ez-rx__card{background:#fff;border:1px solid var(--rx-border);border-radius:var(--rx-radius);padding:16px;margin-bottom:14px;box-shadow:0 6px 18px rgba(15,23,42,.04);position:relative;overflow:hidden;transition:transform .2s ease,box-shadow .2s ease;animation:ezRxFade .55s ease both}
.ez-rx__card:hover{transform:translateY(-2px);box-shadow:0 12px 28px rgba(15,23,42,.08)}
.ez-rx__card::before{content:"";position:absolute;inset:0 auto 0 0;width:4px;background:linear-gradient(180deg,#94a3b8,#cbd5e1)}
.ez-rx__card--blue::before{background:linear-gradient(180deg,#3b82f6,#60a5fa)}
.ez-rx__card--violet::before{background:linear-gradient(180deg,#8b5cf6,#a78bfa)}
.ez-rx__card--teal::before{background:linear-gradient(180deg,#14b8a6,#2dd4bf)}
.ez-rx__card--amber::before{background:linear-gradient(180deg,#f59e0b,#fbbf24)}
.ez-rx__card--slate::before{background:linear-gradient(180deg,#475569,#94a3b8)}
.ez-rx__card--upload::before{background:linear-gradient(180deg,#059669,#34d399)}
.ez-rx__card-head{display:flex;align-items:center;gap:12px;margin-bottom:14px}
.ez-rx__card-ico{width:40px;height:40px;border-radius:12px;display:grid;place-items:center;flex-shrink:0;background:linear-gradient(145deg,#f8fafc,#e2e8f0);color:#334155}
.ez-rx__card--blue .ez-rx__card-ico{background:linear-gradient(145deg,#dbeafe,#eff6ff);color:#1d4ed8}
.ez-rx__card--violet .ez-rx__card-ico{background:linear-gradient(145deg,#ede9fe,#f5f3ff);color:#6d28d9}
.ez-rx__card--teal .ez-rx__card-ico{background:linear-gradient(145deg,#ccfbf1,#f0fdfa);color:#0f766e}
.ez-rx__card--amber .ez-rx__card-ico{background:linear-gradient(145deg,#fef3c7,#fffbeb);color:#b45309}
.ez-rx__card--upload .ez-rx__card-ico{background:linear-gradient(145deg,#d1fae5,#ecfdf5);color:#047857}
.ez-rx__card-ico svg{width:20px;height:20px}
.ez-rx__card-head h3{margin:0;font-size:14px;font-weight:800}
.ez-rx__card-head p{margin:2px 0 0;font-size:11.5px;color:var(--rx-muted)}
.ez-rx__badge{display:inline-block;margin-right:6px;padding:1px 8px;border-radius:999px;font-size:10px;font-weight:800;background:linear-gradient(135deg,#1e293b,#334155);color:#fff;vertical-align:middle}
.ez-rx__grid{display:grid;gap:12px}
.ez-rx__grid--2{grid-template-columns:1fr 1fr}
.ez-rx__grid--3{grid-template-columns:repeat(3,1fr)}
.ez-rx__grid--4{grid-template-columns:repeat(4,1fr)}
@media (max-width:720px){.ez-rx__grid--2,.ez-rx__grid--3,.ez-rx__grid--4{grid-template-columns:1fr 1fr}}
@media (max-width:480px){.ez-rx__grid--2,.ez-rx__grid--3,.ez-rx__grid--4{grid-template-columns:1fr}}
.ez-rx__field label{display:block;font-size:12px;font-weight:700;margin-bottom:6px;color:#1e293b}
.ez-rx__field .req{color:#dc2626}
.ez-rx__field input,.ez-rx__field select,.ez-rx__field textarea{width:100%;box-sizing:border-box;padding:10px 12px;border:1px solid #cbd5e1;border-radius:10px;background:#fff;font-size:13.5px;color:var(--rx-text);transition:border-color .15s ease,box-shadow .15s ease,transform .15s ease}
.ez-rx__field input:focus,.ez-rx__field select:focus,.ez-rx__field textarea:focus{outline:none;border-color:#3b82f6;box-shadow:0 0 0 3px rgba(59,130,246,.18);transform:translateY(-1px)}
.ez-rx__field input[type=number],.ez-rx__field input[type=date]{direction:ltr;text-align:left}
.ez-rx__field small{display:block;margin-top:4px;font-size:10.5px;color:#94a3b8}
.ez-rx__hidden{display:none!important}
.ez-rx__upload-zone{border:2px dashed #6ee7b7;border-radius:14px;padding:22px 16px;text-align:center;background:linear-gradient(180deg,#ecfdf5,#f0fdf4);cursor:pointer;transition:border-color .2s,background .2s,transform .2s}
.ez-rx__upload-zone:hover,.ez-rx__upload-zone.is-drag{border-color:#059669;background:linear-gradient(180deg,#d1fae5,#ecfdf5);transform:translateY(-1px)}
.ez-rx__upload-zone input[type=file]{display:none}
.ez-rx__upload-zone strong{display:block;font-size:13.5px;color:#065f46;margin-bottom:4px}
.ez-rx__upload-zone span{font-size:12px;color:#047857}
.ez-rx__upload-name{margin-top:10px;font-size:12px;color:#0f766e;font-weight:700}
.ez-rx__or{display:flex;align-items:center;gap:12px;margin:6px 0 14px;color:#94a3b8;font-size:12px;font-weight:700}
.ez-rx__or::before,.ez-rx__or::after{content:"";flex:1;height:1px;background:#e2e8f0}
.ez-rx__foot{margin-top:6px;padding:4px 2px 8px}
.ez-rx__verified{display:inline-flex;align-items:center;gap:8px;font-size:11.5px;color:#047857;font-weight:600}
.ez-rx__verified svg{width:16px;height:16px;flex-shrink:0}
.ez-rx__hero--blue{background:linear-gradient(135deg,#0f172a 0%,#1e3a8a 55%,#2563eb 100%)}
.ez-rx__hero--teal{background:linear-gradient(135deg,#042f2e 0%,#0f766e 50%,#14b8a6 100%)}
.ez-rx__hero--amber{background:linear-gradient(135deg,#451a03 0%,#b45309 50%,#f59e0b 100%)}
.ez-rx__hero--violet{background:linear-gradient(135deg,#2e1065 0%,#6d28d9 50%,#a78bfa 100%)}
EZCODE,
        'js' => <<<'EZCODE'
(function(){
  var root=document.querySelector('.ez-rx');
  if(!root) return;
  function updatePd(){
    var sel=root.querySelector('#pd_type');
    if(!sel) return;
    var mode=sel.value;
    root.querySelectorAll('[data-pd]').forEach(function(el){
      var need=el.getAttribute('data-pd');
      if(!mode){el.classList.toggle('ez-rx__hidden',need!=='binocular');return;}
      el.classList.toggle('ez-rx__hidden',need!==mode);
    });
  }
  var pd=root.querySelector('#pd_type');
  if(pd){pd.addEventListener('change',updatePd);updatePd();}
  function bindCyl(cylId,axisId){
    var cyl=root.querySelector(cylId),axis=root.querySelector(axisId);
    if(!cyl||!axis) return;
    function sync(){
      var empty=!cyl.value||cyl.value==='0'||cyl.value==='0.00';
      axis.style.opacity=empty?'0.55':'1';
      axis.placeholder=empty?'در صورت CYL':'۰ تا ۱۸۰';
    }
    cyl.addEventListener('input',sync);sync();
  }
  bindCyl('#od_cyl','#od_axis');bindCyl('#os_cyl','#os_axis');
  var zone=root.querySelector('.ez-rx__upload-zone');
  var file=root.querySelector('#rx_upload');
  var nameEl=root.querySelector('.ez-rx__upload-name');
  if(zone&&file){
    zone.addEventListener('click',function(){file.click();});
    zone.addEventListener('dragover',function(e){e.preventDefault();zone.classList.add('is-drag');});
    zone.addEventListener('dragleave',function(){zone.classList.remove('is-drag');});
    zone.addEventListener('drop',function(e){
      e.preventDefault();zone.classList.remove('is-drag');
      if(e.dataTransfer&&e.dataTransfer.files&&e.dataTransfer.files[0]){
        try{file.files=e.dataTransfer.files;}catch(err){}
        if(nameEl) nameEl.textContent='فایل: '+e.dataTransfer.files[0].name;
      }
    });
    file.addEventListener('change',function(){
      if(file.files&&file.files[0]&&nameEl) nameEl.textContent='فایل: '+file.files[0].name;
    });
  }
  var sg=root.querySelector('#sg_mode');
  if(sg){
    function tog(){
      var rx=sg.value==='rx';
      root.querySelectorAll('[data-sg]').forEach(function(el){
        el.classList.toggle('ez-rx__hidden',!rx && el.getAttribute('data-sg')==='rx');
      });
    }
    sg.addEventListener('change',tog);tog();
  }
})();
EZCODE,
      ),
      'contact-lens-prescription' => array(
        'html' => <<<'EZCODE'
<!-- لنز تماسی -->
<div class="ez-rx" dir="rtl">
  <header class="ez-rx__hero ez-rx__hero--teal">
    <div class="ez-rx__hero-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="9"/><circle cx="12" cy="12" r="4"/><path d="M12 3v2"/><path d="M12 19v2"/></svg></div>
    <div><h2 class="ez-rx__title">نسخه لنز تماسی</h2><p class="ez-rx__sub">Power · BC · DIA را از جعبه یا نسخه بردارید، یا تصویر نسخه را آپلود کنید.</p></div>
  </header>
  <div class="ez-rx__tip"><span class="ez-rx__tip-ico"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><path d="M12 10v6"/><path d="M12 7h.01"/></svg></span><div><strong>راهنما:</strong> BC انحنا و DIA قطر لنز است. برای دو چشم جداگانه وارد کنید.</div></div>

  <section class="ez-rx__card ez-rx__card--upload">
    <div class="ez-rx__card-head">
      <span class="ez-rx__card-ico" aria-hidden="true">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><path d="m17 8-5-5-5 5"/><path d="M12 3v12"/></svg>
      </span>
      <div>
        <h3>آپلود نسخه پزشک</h3>
        <p>می‌توانید به‌جای پر کردن فرم، تصویر یا PDF نسخه را بفرستید</p>
      </div>
    </div>
    <label class="ez-rx__upload-zone" for="rx_upload">
      <input type="file" id="rx_upload" name="rx_upload" accept="image/*,.pdf,application/pdf">
      <strong>تصویر یا PDF نسخه را انتخاب یا اینجا رها کنید</strong>
      <span>JPG · PNG · PDF — حداکثر ۱۰ مگابایت · بررسی توسط کارشناس</span>
      <div class="ez-rx__upload-name"></div>
    </label>
    <p style="margin:10px 0 0;font-size:11.5px;color:#047857;line-height:1.6;">پس از آپلود، تیم بینایی‌سنجی نسخه را بررسی می‌کند و در صورت نیاز با شما هماهنگ می‌شود.</p>
  </section>
  <div class="ez-rx__or">یا فرم زیر را پر کنید</div>

  <section class="ez-rx__card ez-rx__card--teal">
    <div class="ez-rx__card-head"><span class="ez-rx__card-ico"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 2v4"/><path d="M12 18v4"/><circle cx="12" cy="12" r="6"/></svg></span><div><h3>نوع مصرف</h3><p>بازه تعویض لنز</p></div></div>
    <div class="ez-rx__grid ez-rx__grid--2">
      <div class="ez-rx__field"><label for="wear_mode">نوع مصرف <span class="req">*</span></label><select id="wear_mode" name="wear_mode" required><option value="">انتخاب کنید</option><option value="daily">روزانه</option><option value="biweekly">دو هفته‌ای</option><option value="monthly">ماهانه</option><option value="yearly">سالانه / سخت</option></select></div>
      <div class="ez-rx__field"><label for="cl_brand">برند (اختیاری)</label><input type="text" id="cl_brand" name="cl_brand" placeholder="مثلاً Acuvue"></div>
    </div>
  </section>
  <section class="ez-rx__card ez-rx__card--violet">
    <div class="ez-rx__card-head"><span class="ez-rx__card-ico"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="3"/><path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6S2 12 2 12z"/></svg></span><div><h3>چشم راست <span class="ez-rx__badge">OD</span></h3><p>مقادیر لنز راست</p></div></div>
    <div class="ez-rx__grid ez-rx__grid--4">
      <div class="ez-rx__field"><label for="od_power">Power <span class="req">*</span></label><input type="number" id="od_power" name="od_power" step="0.25" placeholder="-۲.۵۰" required><small>نمره</small></div>
      <div class="ez-rx__field"><label for="od_bc">BC <span class="req">*</span></label><input type="number" id="od_bc" name="od_bc" step="0.1" placeholder="۸.۶" required><small>انحنا</small></div>
      <div class="ez-rx__field"><label for="od_dia">DIA <span class="req">*</span></label><input type="number" id="od_dia" name="od_dia" step="0.1" placeholder="۱۴.۲" required><small>قطر</small></div>
      <div class="ez-rx__field"><label for="od_cyl">CYL</label><input type="number" id="od_cyl" name="od_cyl" step="0.25" placeholder="-۰.۷۵"><small>توریک</small></div>
    </div>
  </section>
  <section class="ez-rx__card ez-rx__card--blue">
    <div class="ez-rx__card-head"><span class="ez-rx__card-ico"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="3"/><path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6S2 12 2 12z"/></svg></span><div><h3>چشم چپ <span class="ez-rx__badge">OS</span></h3><p>مقادیر لنز چپ</p></div></div>
    <div class="ez-rx__grid ez-rx__grid--4">
      <div class="ez-rx__field"><label for="os_power">Power <span class="req">*</span></label><input type="number" id="os_power" name="os_power" step="0.25" placeholder="-۱.۷۵" required><small>نمره</small></div>
      <div class="ez-rx__field"><label for="os_bc">BC <span class="req">*</span></label><input type="number" id="os_bc" name="os_bc" step="0.1" placeholder="۸.۶" required><small>انحنا</small></div>
      <div class="ez-rx__field"><label for="os_dia">DIA <span class="req">*</span></label><input type="number" id="os_dia" name="os_dia" step="0.1" placeholder="۱۴.۲" required><small>قطر</small></div>
      <div class="ez-rx__field"><label for="os_cyl">CYL</label><input type="number" id="os_cyl" name="os_cyl" step="0.25" placeholder="-۰.۵۰"><small>توریک</small></div>
    </div>
  </section>
  <section class="ez-rx__card ez-rx__card--slate">
    <div class="ez-rx__card-head"><span class="ez-rx__card-ico"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4Z"/></svg></span><div><h3>یادداشت</h3><p>رنگ، برند ترجیحی، …</p></div></div>
    <div class="ez-rx__field"><label for="cl_note">توضیحات</label><textarea id="cl_note" name="cl_note" rows="3" placeholder="مثلاً لنز رنگی خاکستری، …"></textarea></div>
  </section>

  <footer class="ez-rx__foot">
    <span class="ez-rx__verified">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><path d="m9 12 2 2 4-4"/></svg>
      فرم استاندارد — قابل بررسی توسط متخصص بینایی‌سنجی
    </span>
  </footer>

</div>
EZCODE,
        'css' => <<<'EZCODE'
.ez-rx{--rx-text:#0f172a;--rx-muted:#64748b;--rx-border:#e2e8f0;--rx-radius:16px;font-family:Tahoma,Arial,system-ui,sans-serif;color:var(--rx-text);max-width:920px;margin:0 auto;padding:8px;animation:ezRxFade .45s ease both}
@keyframes ezRxFade{from{opacity:0;transform:translateY(8px)}to{opacity:1;transform:none}}
.ez-rx__hero{display:flex;align-items:center;gap:14px;padding:18px;border-radius:var(--rx-radius);color:#fff;margin-bottom:14px;box-shadow:0 12px 28px rgba(15,23,42,.2)}
.ez-rx__hero-icon{width:48px;height:48px;border-radius:14px;display:grid;place-items:center;background:rgba(255,255,255,.12);flex-shrink:0}
.ez-rx__hero-icon svg{width:26px;height:26px}
.ez-rx__title{margin:0 0 4px;font-size:1.15rem;font-weight:800}
.ez-rx__sub{margin:0;font-size:12.5px;opacity:.9;line-height:1.6}
.ez-rx__tip{display:flex;gap:12px;align-items:flex-start;padding:12px 14px;margin-bottom:14px;border-radius:12px;background:linear-gradient(135deg,#eff6ff,#f0f9ff);border:1px solid #bfdbfe;color:#1e3a8a;font-size:12.5px;line-height:1.7}
.ez-rx__tip-ico{flex-shrink:0;width:22px;height:22px;color:#2563eb}
.ez-rx__tip-ico svg{width:22px;height:22px;display:block}
.ez-rx__card{background:#fff;border:1px solid var(--rx-border);border-radius:var(--rx-radius);padding:16px;margin-bottom:14px;box-shadow:0 6px 18px rgba(15,23,42,.04);position:relative;overflow:hidden;transition:transform .2s ease,box-shadow .2s ease;animation:ezRxFade .55s ease both}
.ez-rx__card:hover{transform:translateY(-2px);box-shadow:0 12px 28px rgba(15,23,42,.08)}
.ez-rx__card::before{content:"";position:absolute;inset:0 auto 0 0;width:4px;background:linear-gradient(180deg,#94a3b8,#cbd5e1)}
.ez-rx__card--blue::before{background:linear-gradient(180deg,#3b82f6,#60a5fa)}
.ez-rx__card--violet::before{background:linear-gradient(180deg,#8b5cf6,#a78bfa)}
.ez-rx__card--teal::before{background:linear-gradient(180deg,#14b8a6,#2dd4bf)}
.ez-rx__card--amber::before{background:linear-gradient(180deg,#f59e0b,#fbbf24)}
.ez-rx__card--slate::before{background:linear-gradient(180deg,#475569,#94a3b8)}
.ez-rx__card--upload::before{background:linear-gradient(180deg,#059669,#34d399)}
.ez-rx__card-head{display:flex;align-items:center;gap:12px;margin-bottom:14px}
.ez-rx__card-ico{width:40px;height:40px;border-radius:12px;display:grid;place-items:center;flex-shrink:0;background:linear-gradient(145deg,#f8fafc,#e2e8f0);color:#334155}
.ez-rx__card--blue .ez-rx__card-ico{background:linear-gradient(145deg,#dbeafe,#eff6ff);color:#1d4ed8}
.ez-rx__card--violet .ez-rx__card-ico{background:linear-gradient(145deg,#ede9fe,#f5f3ff);color:#6d28d9}
.ez-rx__card--teal .ez-rx__card-ico{background:linear-gradient(145deg,#ccfbf1,#f0fdfa);color:#0f766e}
.ez-rx__card--amber .ez-rx__card-ico{background:linear-gradient(145deg,#fef3c7,#fffbeb);color:#b45309}
.ez-rx__card--upload .ez-rx__card-ico{background:linear-gradient(145deg,#d1fae5,#ecfdf5);color:#047857}
.ez-rx__card-ico svg{width:20px;height:20px}
.ez-rx__card-head h3{margin:0;font-size:14px;font-weight:800}
.ez-rx__card-head p{margin:2px 0 0;font-size:11.5px;color:var(--rx-muted)}
.ez-rx__badge{display:inline-block;margin-right:6px;padding:1px 8px;border-radius:999px;font-size:10px;font-weight:800;background:linear-gradient(135deg,#1e293b,#334155);color:#fff;vertical-align:middle}
.ez-rx__grid{display:grid;gap:12px}
.ez-rx__grid--2{grid-template-columns:1fr 1fr}
.ez-rx__grid--3{grid-template-columns:repeat(3,1fr)}
.ez-rx__grid--4{grid-template-columns:repeat(4,1fr)}
@media (max-width:720px){.ez-rx__grid--2,.ez-rx__grid--3,.ez-rx__grid--4{grid-template-columns:1fr 1fr}}
@media (max-width:480px){.ez-rx__grid--2,.ez-rx__grid--3,.ez-rx__grid--4{grid-template-columns:1fr}}
.ez-rx__field label{display:block;font-size:12px;font-weight:700;margin-bottom:6px;color:#1e293b}
.ez-rx__field .req{color:#dc2626}
.ez-rx__field input,.ez-rx__field select,.ez-rx__field textarea{width:100%;box-sizing:border-box;padding:10px 12px;border:1px solid #cbd5e1;border-radius:10px;background:#fff;font-size:13.5px;color:var(--rx-text);transition:border-color .15s ease,box-shadow .15s ease,transform .15s ease}
.ez-rx__field input:focus,.ez-rx__field select:focus,.ez-rx__field textarea:focus{outline:none;border-color:#3b82f6;box-shadow:0 0 0 3px rgba(59,130,246,.18);transform:translateY(-1px)}
.ez-rx__field input[type=number],.ez-rx__field input[type=date]{direction:ltr;text-align:left}
.ez-rx__field small{display:block;margin-top:4px;font-size:10.5px;color:#94a3b8}
.ez-rx__hidden{display:none!important}
.ez-rx__upload-zone{border:2px dashed #6ee7b7;border-radius:14px;padding:22px 16px;text-align:center;background:linear-gradient(180deg,#ecfdf5,#f0fdf4);cursor:pointer;transition:border-color .2s,background .2s,transform .2s}
.ez-rx__upload-zone:hover,.ez-rx__upload-zone.is-drag{border-color:#059669;background:linear-gradient(180deg,#d1fae5,#ecfdf5);transform:translateY(-1px)}
.ez-rx__upload-zone input[type=file]{display:none}
.ez-rx__upload-zone strong{display:block;font-size:13.5px;color:#065f46;margin-bottom:4px}
.ez-rx__upload-zone span{font-size:12px;color:#047857}
.ez-rx__upload-name{margin-top:10px;font-size:12px;color:#0f766e;font-weight:700}
.ez-rx__or{display:flex;align-items:center;gap:12px;margin:6px 0 14px;color:#94a3b8;font-size:12px;font-weight:700}
.ez-rx__or::before,.ez-rx__or::after{content:"";flex:1;height:1px;background:#e2e8f0}
.ez-rx__foot{margin-top:6px;padding:4px 2px 8px}
.ez-rx__verified{display:inline-flex;align-items:center;gap:8px;font-size:11.5px;color:#047857;font-weight:600}
.ez-rx__verified svg{width:16px;height:16px;flex-shrink:0}
.ez-rx__hero--blue{background:linear-gradient(135deg,#0f172a 0%,#1e3a8a 55%,#2563eb 100%)}
.ez-rx__hero--teal{background:linear-gradient(135deg,#042f2e 0%,#0f766e 50%,#14b8a6 100%)}
.ez-rx__hero--amber{background:linear-gradient(135deg,#451a03 0%,#b45309 50%,#f59e0b 100%)}
.ez-rx__hero--violet{background:linear-gradient(135deg,#2e1065 0%,#6d28d9 50%,#a78bfa 100%)}
EZCODE,
        'js' => <<<'EZCODE'
(function(){
  var root=document.querySelector('.ez-rx');
  if(!root) return;
  function updatePd(){
    var sel=root.querySelector('#pd_type');
    if(!sel) return;
    var mode=sel.value;
    root.querySelectorAll('[data-pd]').forEach(function(el){
      var need=el.getAttribute('data-pd');
      if(!mode){el.classList.toggle('ez-rx__hidden',need!=='binocular');return;}
      el.classList.toggle('ez-rx__hidden',need!==mode);
    });
  }
  var pd=root.querySelector('#pd_type');
  if(pd){pd.addEventListener('change',updatePd);updatePd();}
  function bindCyl(cylId,axisId){
    var cyl=root.querySelector(cylId),axis=root.querySelector(axisId);
    if(!cyl||!axis) return;
    function sync(){
      var empty=!cyl.value||cyl.value==='0'||cyl.value==='0.00';
      axis.style.opacity=empty?'0.55':'1';
      axis.placeholder=empty?'در صورت CYL':'۰ تا ۱۸۰';
    }
    cyl.addEventListener('input',sync);sync();
  }
  bindCyl('#od_cyl','#od_axis');bindCyl('#os_cyl','#os_axis');
  var zone=root.querySelector('.ez-rx__upload-zone');
  var file=root.querySelector('#rx_upload');
  var nameEl=root.querySelector('.ez-rx__upload-name');
  if(zone&&file){
    zone.addEventListener('click',function(){file.click();});
    zone.addEventListener('dragover',function(e){e.preventDefault();zone.classList.add('is-drag');});
    zone.addEventListener('dragleave',function(){zone.classList.remove('is-drag');});
    zone.addEventListener('drop',function(e){
      e.preventDefault();zone.classList.remove('is-drag');
      if(e.dataTransfer&&e.dataTransfer.files&&e.dataTransfer.files[0]){
        try{file.files=e.dataTransfer.files;}catch(err){}
        if(nameEl) nameEl.textContent='فایل: '+e.dataTransfer.files[0].name;
      }
    });
    file.addEventListener('change',function(){
      if(file.files&&file.files[0]&&nameEl) nameEl.textContent='فایل: '+file.files[0].name;
    });
  }
  var sg=root.querySelector('#sg_mode');
  if(sg){
    function tog(){
      var rx=sg.value==='rx';
      root.querySelectorAll('[data-sg]').forEach(function(el){
        el.classList.toggle('ez-rx__hidden',!rx && el.getAttribute('data-sg')==='rx');
      });
    }
    sg.addEventListener('change',tog);tog();
  }
})();
EZCODE,
      ),
      'sunglasses' => array(
        'html' => <<<'EZCODE'
<!-- عینک آفتابی -->
<div class="ez-rx" dir="rtl">
  <header class="ez-rx__hero ez-rx__hero--amber">
    <div class="ez-rx__hero-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="4"/><path d="M12 2v2"/><path d="M12 20v2"/><path d="m4.9 4.9 1.4 1.4"/><path d="m17.7 17.7 1.4 1.4"/><path d="M2 12h2"/><path d="M20 12h2"/><path d="m4.9 19.1 1.4-1.4"/><path d="m17.7 6.3 1.4-1.4"/></svg></div>
    <div><h2 class="ez-rx__title">عینک آفتابی</h2><p class="ez-rx__sub">بدون نمره یا نمره‌دار — در صورت نسخه، می‌توانید تصویر را آپلود کنید.</p></div>
  </header>

  <section class="ez-rx__card ez-rx__card--upload">
    <div class="ez-rx__card-head">
      <span class="ez-rx__card-ico" aria-hidden="true">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><path d="m17 8-5-5-5 5"/><path d="M12 3v12"/></svg>
      </span>
      <div>
        <h3>آپلود نسخه پزشک</h3>
        <p>می‌توانید به‌جای پر کردن فرم، تصویر یا PDF نسخه را بفرستید</p>
      </div>
    </div>
    <label class="ez-rx__upload-zone" for="rx_upload">
      <input type="file" id="rx_upload" name="rx_upload" accept="image/*,.pdf,application/pdf">
      <strong>تصویر یا PDF نسخه را انتخاب یا اینجا رها کنید</strong>
      <span>JPG · PNG · PDF — حداکثر ۱۰ مگابایت · بررسی توسط کارشناس</span>
      <div class="ez-rx__upload-name"></div>
    </label>
    <p style="margin:10px 0 0;font-size:11.5px;color:#047857;line-height:1.6;">پس از آپلود، تیم بینایی‌سنجی نسخه را بررسی می‌کند و در صورت نیاز با شما هماهنگ می‌شود.</p>
  </section>
  <div class="ez-rx__or">یا فرم زیر را پر کنید</div>

  <section class="ez-rx__card ez-rx__card--amber">
    <div class="ez-rx__card-head"><span class="ez-rx__card-ico"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 3v3"/><circle cx="12" cy="14" r="6"/></svg></span><div><h3>نوع سفارش</h3><p>آفتابی ساده یا نمره‌دار</p></div></div>
    <div class="ez-rx__grid ez-rx__grid--2">
      <div class="ez-rx__field"><label for="sg_mode">نوع سفارش <span class="req">*</span></label><select id="sg_mode" name="sg_mode" required><option value="">انتخاب کنید</option><option value="plano">بدون نمره</option><option value="rx">نمره‌دار (با نسخه)</option></select></div>
      <div class="ez-rx__field"><label for="sg_lens">نوع لنز</label><select id="sg_lens" name="sg_lens"><option value="">انتخاب کنید</option><option value="polarized">پلاریزه</option><option value="uv400">UV400</option><option value="mirror">آینه‌ای</option><option value="photo">فوتوکرومیک</option></select></div>
    </div>
  </section>
  <div data-sg="rx">
    <section class="ez-rx__card ez-rx__card--violet">
      <div class="ez-rx__card-head"><span class="ez-rx__card-ico"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="3"/><path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6S2 12 2 12z"/></svg></span><div><h3>نمره (در صورت نمره‌دار)</h3><p>OD / OS</p></div></div>
      <div class="ez-rx__grid ez-rx__grid--4">
        <div class="ez-rx__field"><label for="od_sph">SPH راست</label><input type="number" id="od_sph" name="od_sph" step="0.25" placeholder="-۲.۰۰"></div>
        <div class="ez-rx__field"><label for="od_cyl">CYL راست</label><input type="number" id="od_cyl" name="od_cyl" step="0.25"></div>
        <div class="ez-rx__field"><label for="os_sph">SPH چپ</label><input type="number" id="os_sph" name="os_sph" step="0.25" placeholder="-۱.۵۰"></div>
        <div class="ez-rx__field"><label for="os_cyl">CYL چپ</label><input type="number" id="os_cyl" name="os_cyl" step="0.25"></div>
      </div>
    </section>
  </div>
  <section class="ez-rx__card ez-rx__card--slate">
    <div class="ez-rx__card-head"><span class="ez-rx__card-ico"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4Z"/></svg></span><div><h3>یادداشت</h3><p>رنگ فریم، برند، …</p></div></div>
    <div class="ez-rx__field"><label for="sg_note">توضیحات</label><textarea id="sg_note" name="sg_note" rows="3" placeholder="ترجیحات ظاهر و کیفیت"></textarea></div>
  </section>

  <footer class="ez-rx__foot">
    <span class="ez-rx__verified">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><path d="m9 12 2 2 4-4"/></svg>
      فرم استاندارد — قابل بررسی توسط متخصص بینایی‌سنجی
    </span>
  </footer>

</div>
EZCODE,
        'css' => <<<'EZCODE'
.ez-rx{--rx-text:#0f172a;--rx-muted:#64748b;--rx-border:#e2e8f0;--rx-radius:16px;font-family:Tahoma,Arial,system-ui,sans-serif;color:var(--rx-text);max-width:920px;margin:0 auto;padding:8px;animation:ezRxFade .45s ease both}
@keyframes ezRxFade{from{opacity:0;transform:translateY(8px)}to{opacity:1;transform:none}}
.ez-rx__hero{display:flex;align-items:center;gap:14px;padding:18px;border-radius:var(--rx-radius);color:#fff;margin-bottom:14px;box-shadow:0 12px 28px rgba(15,23,42,.2)}
.ez-rx__hero-icon{width:48px;height:48px;border-radius:14px;display:grid;place-items:center;background:rgba(255,255,255,.12);flex-shrink:0}
.ez-rx__hero-icon svg{width:26px;height:26px}
.ez-rx__title{margin:0 0 4px;font-size:1.15rem;font-weight:800}
.ez-rx__sub{margin:0;font-size:12.5px;opacity:.9;line-height:1.6}
.ez-rx__tip{display:flex;gap:12px;align-items:flex-start;padding:12px 14px;margin-bottom:14px;border-radius:12px;background:linear-gradient(135deg,#eff6ff,#f0f9ff);border:1px solid #bfdbfe;color:#1e3a8a;font-size:12.5px;line-height:1.7}
.ez-rx__tip-ico{flex-shrink:0;width:22px;height:22px;color:#2563eb}
.ez-rx__tip-ico svg{width:22px;height:22px;display:block}
.ez-rx__card{background:#fff;border:1px solid var(--rx-border);border-radius:var(--rx-radius);padding:16px;margin-bottom:14px;box-shadow:0 6px 18px rgba(15,23,42,.04);position:relative;overflow:hidden;transition:transform .2s ease,box-shadow .2s ease;animation:ezRxFade .55s ease both}
.ez-rx__card:hover{transform:translateY(-2px);box-shadow:0 12px 28px rgba(15,23,42,.08)}
.ez-rx__card::before{content:"";position:absolute;inset:0 auto 0 0;width:4px;background:linear-gradient(180deg,#94a3b8,#cbd5e1)}
.ez-rx__card--blue::before{background:linear-gradient(180deg,#3b82f6,#60a5fa)}
.ez-rx__card--violet::before{background:linear-gradient(180deg,#8b5cf6,#a78bfa)}
.ez-rx__card--teal::before{background:linear-gradient(180deg,#14b8a6,#2dd4bf)}
.ez-rx__card--amber::before{background:linear-gradient(180deg,#f59e0b,#fbbf24)}
.ez-rx__card--slate::before{background:linear-gradient(180deg,#475569,#94a3b8)}
.ez-rx__card--upload::before{background:linear-gradient(180deg,#059669,#34d399)}
.ez-rx__card-head{display:flex;align-items:center;gap:12px;margin-bottom:14px}
.ez-rx__card-ico{width:40px;height:40px;border-radius:12px;display:grid;place-items:center;flex-shrink:0;background:linear-gradient(145deg,#f8fafc,#e2e8f0);color:#334155}
.ez-rx__card--blue .ez-rx__card-ico{background:linear-gradient(145deg,#dbeafe,#eff6ff);color:#1d4ed8}
.ez-rx__card--violet .ez-rx__card-ico{background:linear-gradient(145deg,#ede9fe,#f5f3ff);color:#6d28d9}
.ez-rx__card--teal .ez-rx__card-ico{background:linear-gradient(145deg,#ccfbf1,#f0fdfa);color:#0f766e}
.ez-rx__card--amber .ez-rx__card-ico{background:linear-gradient(145deg,#fef3c7,#fffbeb);color:#b45309}
.ez-rx__card--upload .ez-rx__card-ico{background:linear-gradient(145deg,#d1fae5,#ecfdf5);color:#047857}
.ez-rx__card-ico svg{width:20px;height:20px}
.ez-rx__card-head h3{margin:0;font-size:14px;font-weight:800}
.ez-rx__card-head p{margin:2px 0 0;font-size:11.5px;color:var(--rx-muted)}
.ez-rx__badge{display:inline-block;margin-right:6px;padding:1px 8px;border-radius:999px;font-size:10px;font-weight:800;background:linear-gradient(135deg,#1e293b,#334155);color:#fff;vertical-align:middle}
.ez-rx__grid{display:grid;gap:12px}
.ez-rx__grid--2{grid-template-columns:1fr 1fr}
.ez-rx__grid--3{grid-template-columns:repeat(3,1fr)}
.ez-rx__grid--4{grid-template-columns:repeat(4,1fr)}
@media (max-width:720px){.ez-rx__grid--2,.ez-rx__grid--3,.ez-rx__grid--4{grid-template-columns:1fr 1fr}}
@media (max-width:480px){.ez-rx__grid--2,.ez-rx__grid--3,.ez-rx__grid--4{grid-template-columns:1fr}}
.ez-rx__field label{display:block;font-size:12px;font-weight:700;margin-bottom:6px;color:#1e293b}
.ez-rx__field .req{color:#dc2626}
.ez-rx__field input,.ez-rx__field select,.ez-rx__field textarea{width:100%;box-sizing:border-box;padding:10px 12px;border:1px solid #cbd5e1;border-radius:10px;background:#fff;font-size:13.5px;color:var(--rx-text);transition:border-color .15s ease,box-shadow .15s ease,transform .15s ease}
.ez-rx__field input:focus,.ez-rx__field select:focus,.ez-rx__field textarea:focus{outline:none;border-color:#3b82f6;box-shadow:0 0 0 3px rgba(59,130,246,.18);transform:translateY(-1px)}
.ez-rx__field input[type=number],.ez-rx__field input[type=date]{direction:ltr;text-align:left}
.ez-rx__field small{display:block;margin-top:4px;font-size:10.5px;color:#94a3b8}
.ez-rx__hidden{display:none!important}
.ez-rx__upload-zone{border:2px dashed #6ee7b7;border-radius:14px;padding:22px 16px;text-align:center;background:linear-gradient(180deg,#ecfdf5,#f0fdf4);cursor:pointer;transition:border-color .2s,background .2s,transform .2s}
.ez-rx__upload-zone:hover,.ez-rx__upload-zone.is-drag{border-color:#059669;background:linear-gradient(180deg,#d1fae5,#ecfdf5);transform:translateY(-1px)}
.ez-rx__upload-zone input[type=file]{display:none}
.ez-rx__upload-zone strong{display:block;font-size:13.5px;color:#065f46;margin-bottom:4px}
.ez-rx__upload-zone span{font-size:12px;color:#047857}
.ez-rx__upload-name{margin-top:10px;font-size:12px;color:#0f766e;font-weight:700}
.ez-rx__or{display:flex;align-items:center;gap:12px;margin:6px 0 14px;color:#94a3b8;font-size:12px;font-weight:700}
.ez-rx__or::before,.ez-rx__or::after{content:"";flex:1;height:1px;background:#e2e8f0}
.ez-rx__foot{margin-top:6px;padding:4px 2px 8px}
.ez-rx__verified{display:inline-flex;align-items:center;gap:8px;font-size:11.5px;color:#047857;font-weight:600}
.ez-rx__verified svg{width:16px;height:16px;flex-shrink:0}
.ez-rx__hero--blue{background:linear-gradient(135deg,#0f172a 0%,#1e3a8a 55%,#2563eb 100%)}
.ez-rx__hero--teal{background:linear-gradient(135deg,#042f2e 0%,#0f766e 50%,#14b8a6 100%)}
.ez-rx__hero--amber{background:linear-gradient(135deg,#451a03 0%,#b45309 50%,#f59e0b 100%)}
.ez-rx__hero--violet{background:linear-gradient(135deg,#2e1065 0%,#6d28d9 50%,#a78bfa 100%)}
EZCODE,
        'js' => <<<'EZCODE'
(function(){
  var root=document.querySelector('.ez-rx');
  if(!root) return;
  function updatePd(){
    var sel=root.querySelector('#pd_type');
    if(!sel) return;
    var mode=sel.value;
    root.querySelectorAll('[data-pd]').forEach(function(el){
      var need=el.getAttribute('data-pd');
      if(!mode){el.classList.toggle('ez-rx__hidden',need!=='binocular');return;}
      el.classList.toggle('ez-rx__hidden',need!==mode);
    });
  }
  var pd=root.querySelector('#pd_type');
  if(pd){pd.addEventListener('change',updatePd);updatePd();}
  function bindCyl(cylId,axisId){
    var cyl=root.querySelector(cylId),axis=root.querySelector(axisId);
    if(!cyl||!axis) return;
    function sync(){
      var empty=!cyl.value||cyl.value==='0'||cyl.value==='0.00';
      axis.style.opacity=empty?'0.55':'1';
      axis.placeholder=empty?'در صورت CYL':'۰ تا ۱۸۰';
    }
    cyl.addEventListener('input',sync);sync();
  }
  bindCyl('#od_cyl','#od_axis');bindCyl('#os_cyl','#os_axis');
  var zone=root.querySelector('.ez-rx__upload-zone');
  var file=root.querySelector('#rx_upload');
  var nameEl=root.querySelector('.ez-rx__upload-name');
  if(zone&&file){
    zone.addEventListener('click',function(){file.click();});
    zone.addEventListener('dragover',function(e){e.preventDefault();zone.classList.add('is-drag');});
    zone.addEventListener('dragleave',function(){zone.classList.remove('is-drag');});
    zone.addEventListener('drop',function(e){
      e.preventDefault();zone.classList.remove('is-drag');
      if(e.dataTransfer&&e.dataTransfer.files&&e.dataTransfer.files[0]){
        try{file.files=e.dataTransfer.files;}catch(err){}
        if(nameEl) nameEl.textContent='فایل: '+e.dataTransfer.files[0].name;
      }
    });
    file.addEventListener('change',function(){
      if(file.files&&file.files[0]&&nameEl) nameEl.textContent='فایل: '+file.files[0].name;
    });
  }
  var sg=root.querySelector('#sg_mode');
  if(sg){
    function tog(){
      var rx=sg.value==='rx';
      root.querySelectorAll('[data-sg]').forEach(function(el){
        el.classList.toggle('ez-rx__hidden',!rx && el.getAttribute('data-sg')==='rx');
      });
    }
    sg.addEventListener('change',tog);tog();
  }
})();
EZCODE,
      ),
      'medical-contact-lens' => array(
        'html' => <<<'EZCODE'
<!-- لنز طبی تخصصی -->
<div class="ez-rx" dir="rtl">
  <header class="ez-rx__hero ez-rx__hero--violet">
    <div class="ez-rx__hero-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg></div>
    <div><h2 class="ez-rx__title">نسخه لنز طبی تخصصی</h2><p class="ez-rx__sub">توریک · مولتی‌فوکال · سخت — آپلود نسخه پزشک توصیه می‌شود.</p></div>
  </header>
  <div class="ez-rx__tip"><span class="ez-rx__tip-ico"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><path d="M12 10v6"/><path d="M12 7h.01"/></svg></span><div><strong>توجه:</strong> برای لنزهای تخصصی، آپلود نسخه کامل پزشک دقت سفارش را بالا می‌برد.</div></div>

  <section class="ez-rx__card ez-rx__card--upload">
    <div class="ez-rx__card-head">
      <span class="ez-rx__card-ico" aria-hidden="true">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><path d="m17 8-5-5-5 5"/><path d="M12 3v12"/></svg>
      </span>
      <div>
        <h3>آپلود نسخه پزشک</h3>
        <p>می‌توانید به‌جای پر کردن فرم، تصویر یا PDF نسخه را بفرستید</p>
      </div>
    </div>
    <label class="ez-rx__upload-zone" for="rx_upload">
      <input type="file" id="rx_upload" name="rx_upload" accept="image/*,.pdf,application/pdf">
      <strong>تصویر یا PDF نسخه را انتخاب یا اینجا رها کنید</strong>
      <span>JPG · PNG · PDF — حداکثر ۱۰ مگابایت · بررسی توسط کارشناس</span>
      <div class="ez-rx__upload-name"></div>
    </label>
    <p style="margin:10px 0 0;font-size:11.5px;color:#047857;line-height:1.6;">پس از آپلود، تیم بینایی‌سنجی نسخه را بررسی می‌کند و در صورت نیاز با شما هماهنگ می‌شود.</p>
  </section>
  <div class="ez-rx__or">یا فرم زیر را پر کنید</div>

  <section class="ez-rx__card ez-rx__card--violet">
    <div class="ez-rx__card-head"><span class="ez-rx__card-ico"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="8"/></svg></span><div><h3>نوع لنز تخصصی</h3><p>بر اساس تجویز</p></div></div>
    <div class="ez-rx__grid ez-rx__grid--2">
      <div class="ez-rx__field"><label for="med_type">نوع لنز <span class="req">*</span></label><select id="med_type" name="med_type" required><option value="">انتخاب کنید</option><option value="toric">توریک (آستیگمات)</option><option value="multifocal">مولتی‌فوکال</option><option value="rgp">سخت (RGP)</option><option value="scleral">اسکلرال</option><option value="other">سایر</option></select></div>
      <div class="ez-rx__field"><label for="med_wear">بازه مصرف</label><select id="med_wear" name="med_wear"><option value="monthly">ماهانه</option><option value="daily">روزانه</option><option value="yearly">سالانه</option></select></div>
    </div>
  </section>
  <section class="ez-rx__card ez-rx__card--teal">
    <div class="ez-rx__card-head"><span class="ez-rx__card-ico"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="3"/><path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6S2 12 2 12z"/></svg></span><div><h3>چشم راست <span class="ez-rx__badge">OD</span></h3></div></div>
    <div class="ez-rx__grid ez-rx__grid--4">
      <div class="ez-rx__field"><label for="od_power">Power</label><input type="number" id="od_power" name="od_power" step="0.25" placeholder="-۳.۰۰"></div>
      <div class="ez-rx__field"><label for="od_cyl">CYL</label><input type="number" id="od_cyl" name="od_cyl" step="0.25"></div>
      <div class="ez-rx__field"><label for="od_axis">AXIS</label><input type="number" id="od_axis" name="od_axis" min="0" max="180" step="1"></div>
      <div class="ez-rx__field"><label for="od_bc">BC / DIA</label><input type="text" id="od_bc" name="od_bc" placeholder="۸.۶ / ۱۴.۲"></div>
    </div>
  </section>
  <section class="ez-rx__card ez-rx__card--blue">
    <div class="ez-rx__card-head"><span class="ez-rx__card-ico"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="3"/><path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6S2 12 2 12z"/></svg></span><div><h3>چشم چپ <span class="ez-rx__badge">OS</span></h3></div></div>
    <div class="ez-rx__grid ez-rx__grid--4">
      <div class="ez-rx__field"><label for="os_power">Power</label><input type="number" id="os_power" name="os_power" step="0.25" placeholder="-۲.۲۵"></div>
      <div class="ez-rx__field"><label for="os_cyl">CYL</label><input type="number" id="os_cyl" name="os_cyl" step="0.25"></div>
      <div class="ez-rx__field"><label for="os_axis">AXIS</label><input type="number" id="os_axis" name="os_axis" min="0" max="180" step="1"></div>
      <div class="ez-rx__field"><label for="os_bc">BC / DIA</label><input type="text" id="os_bc" name="os_bc" placeholder="۸.۶ / ۱۴.۲"></div>
    </div>
  </section>
  <section class="ez-rx__card ez-rx__card--slate">
    <div class="ez-rx__card-head"><span class="ez-rx__card-ico"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4Z"/></svg></span><div><h3>یادداشت تخصصی</h3></div></div>
    <div class="ez-rx__field"><label for="med_note">توضیحات</label><textarea id="med_note" name="med_note" rows="3" placeholder="نکات تجویز پزشک، برند خاص، …"></textarea></div>
  </section>

  <footer class="ez-rx__foot">
    <span class="ez-rx__verified">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><path d="m9 12 2 2 4-4"/></svg>
      فرم استاندارد — قابل بررسی توسط متخصص بینایی‌سنجی
    </span>
  </footer>

</div>
EZCODE,
        'css' => <<<'EZCODE'
.ez-rx{--rx-text:#0f172a;--rx-muted:#64748b;--rx-border:#e2e8f0;--rx-radius:16px;font-family:Tahoma,Arial,system-ui,sans-serif;color:var(--rx-text);max-width:920px;margin:0 auto;padding:8px;animation:ezRxFade .45s ease both}
@keyframes ezRxFade{from{opacity:0;transform:translateY(8px)}to{opacity:1;transform:none}}
.ez-rx__hero{display:flex;align-items:center;gap:14px;padding:18px;border-radius:var(--rx-radius);color:#fff;margin-bottom:14px;box-shadow:0 12px 28px rgba(15,23,42,.2)}
.ez-rx__hero-icon{width:48px;height:48px;border-radius:14px;display:grid;place-items:center;background:rgba(255,255,255,.12);flex-shrink:0}
.ez-rx__hero-icon svg{width:26px;height:26px}
.ez-rx__title{margin:0 0 4px;font-size:1.15rem;font-weight:800}
.ez-rx__sub{margin:0;font-size:12.5px;opacity:.9;line-height:1.6}
.ez-rx__tip{display:flex;gap:12px;align-items:flex-start;padding:12px 14px;margin-bottom:14px;border-radius:12px;background:linear-gradient(135deg,#eff6ff,#f0f9ff);border:1px solid #bfdbfe;color:#1e3a8a;font-size:12.5px;line-height:1.7}
.ez-rx__tip-ico{flex-shrink:0;width:22px;height:22px;color:#2563eb}
.ez-rx__tip-ico svg{width:22px;height:22px;display:block}
.ez-rx__card{background:#fff;border:1px solid var(--rx-border);border-radius:var(--rx-radius);padding:16px;margin-bottom:14px;box-shadow:0 6px 18px rgba(15,23,42,.04);position:relative;overflow:hidden;transition:transform .2s ease,box-shadow .2s ease;animation:ezRxFade .55s ease both}
.ez-rx__card:hover{transform:translateY(-2px);box-shadow:0 12px 28px rgba(15,23,42,.08)}
.ez-rx__card::before{content:"";position:absolute;inset:0 auto 0 0;width:4px;background:linear-gradient(180deg,#94a3b8,#cbd5e1)}
.ez-rx__card--blue::before{background:linear-gradient(180deg,#3b82f6,#60a5fa)}
.ez-rx__card--violet::before{background:linear-gradient(180deg,#8b5cf6,#a78bfa)}
.ez-rx__card--teal::before{background:linear-gradient(180deg,#14b8a6,#2dd4bf)}
.ez-rx__card--amber::before{background:linear-gradient(180deg,#f59e0b,#fbbf24)}
.ez-rx__card--slate::before{background:linear-gradient(180deg,#475569,#94a3b8)}
.ez-rx__card--upload::before{background:linear-gradient(180deg,#059669,#34d399)}
.ez-rx__card-head{display:flex;align-items:center;gap:12px;margin-bottom:14px}
.ez-rx__card-ico{width:40px;height:40px;border-radius:12px;display:grid;place-items:center;flex-shrink:0;background:linear-gradient(145deg,#f8fafc,#e2e8f0);color:#334155}
.ez-rx__card--blue .ez-rx__card-ico{background:linear-gradient(145deg,#dbeafe,#eff6ff);color:#1d4ed8}
.ez-rx__card--violet .ez-rx__card-ico{background:linear-gradient(145deg,#ede9fe,#f5f3ff);color:#6d28d9}
.ez-rx__card--teal .ez-rx__card-ico{background:linear-gradient(145deg,#ccfbf1,#f0fdfa);color:#0f766e}
.ez-rx__card--amber .ez-rx__card-ico{background:linear-gradient(145deg,#fef3c7,#fffbeb);color:#b45309}
.ez-rx__card--upload .ez-rx__card-ico{background:linear-gradient(145deg,#d1fae5,#ecfdf5);color:#047857}
.ez-rx__card-ico svg{width:20px;height:20px}
.ez-rx__card-head h3{margin:0;font-size:14px;font-weight:800}
.ez-rx__card-head p{margin:2px 0 0;font-size:11.5px;color:var(--rx-muted)}
.ez-rx__badge{display:inline-block;margin-right:6px;padding:1px 8px;border-radius:999px;font-size:10px;font-weight:800;background:linear-gradient(135deg,#1e293b,#334155);color:#fff;vertical-align:middle}
.ez-rx__grid{display:grid;gap:12px}
.ez-rx__grid--2{grid-template-columns:1fr 1fr}
.ez-rx__grid--3{grid-template-columns:repeat(3,1fr)}
.ez-rx__grid--4{grid-template-columns:repeat(4,1fr)}
@media (max-width:720px){.ez-rx__grid--2,.ez-rx__grid--3,.ez-rx__grid--4{grid-template-columns:1fr 1fr}}
@media (max-width:480px){.ez-rx__grid--2,.ez-rx__grid--3,.ez-rx__grid--4{grid-template-columns:1fr}}
.ez-rx__field label{display:block;font-size:12px;font-weight:700;margin-bottom:6px;color:#1e293b}
.ez-rx__field .req{color:#dc2626}
.ez-rx__field input,.ez-rx__field select,.ez-rx__field textarea{width:100%;box-sizing:border-box;padding:10px 12px;border:1px solid #cbd5e1;border-radius:10px;background:#fff;font-size:13.5px;color:var(--rx-text);transition:border-color .15s ease,box-shadow .15s ease,transform .15s ease}
.ez-rx__field input:focus,.ez-rx__field select:focus,.ez-rx__field textarea:focus{outline:none;border-color:#3b82f6;box-shadow:0 0 0 3px rgba(59,130,246,.18);transform:translateY(-1px)}
.ez-rx__field input[type=number],.ez-rx__field input[type=date]{direction:ltr;text-align:left}
.ez-rx__field small{display:block;margin-top:4px;font-size:10.5px;color:#94a3b8}
.ez-rx__hidden{display:none!important}
.ez-rx__upload-zone{border:2px dashed #6ee7b7;border-radius:14px;padding:22px 16px;text-align:center;background:linear-gradient(180deg,#ecfdf5,#f0fdf4);cursor:pointer;transition:border-color .2s,background .2s,transform .2s}
.ez-rx__upload-zone:hover,.ez-rx__upload-zone.is-drag{border-color:#059669;background:linear-gradient(180deg,#d1fae5,#ecfdf5);transform:translateY(-1px)}
.ez-rx__upload-zone input[type=file]{display:none}
.ez-rx__upload-zone strong{display:block;font-size:13.5px;color:#065f46;margin-bottom:4px}
.ez-rx__upload-zone span{font-size:12px;color:#047857}
.ez-rx__upload-name{margin-top:10px;font-size:12px;color:#0f766e;font-weight:700}
.ez-rx__or{display:flex;align-items:center;gap:12px;margin:6px 0 14px;color:#94a3b8;font-size:12px;font-weight:700}
.ez-rx__or::before,.ez-rx__or::after{content:"";flex:1;height:1px;background:#e2e8f0}
.ez-rx__foot{margin-top:6px;padding:4px 2px 8px}
.ez-rx__verified{display:inline-flex;align-items:center;gap:8px;font-size:11.5px;color:#047857;font-weight:600}
.ez-rx__verified svg{width:16px;height:16px;flex-shrink:0}
.ez-rx__hero--blue{background:linear-gradient(135deg,#0f172a 0%,#1e3a8a 55%,#2563eb 100%)}
.ez-rx__hero--teal{background:linear-gradient(135deg,#042f2e 0%,#0f766e 50%,#14b8a6 100%)}
.ez-rx__hero--amber{background:linear-gradient(135deg,#451a03 0%,#b45309 50%,#f59e0b 100%)}
.ez-rx__hero--violet{background:linear-gradient(135deg,#2e1065 0%,#6d28d9 50%,#a78bfa 100%)}
EZCODE,
        'js' => <<<'EZCODE'
(function(){
  var root=document.querySelector('.ez-rx');
  if(!root) return;
  function updatePd(){
    var sel=root.querySelector('#pd_type');
    if(!sel) return;
    var mode=sel.value;
    root.querySelectorAll('[data-pd]').forEach(function(el){
      var need=el.getAttribute('data-pd');
      if(!mode){el.classList.toggle('ez-rx__hidden',need!=='binocular');return;}
      el.classList.toggle('ez-rx__hidden',need!==mode);
    });
  }
  var pd=root.querySelector('#pd_type');
  if(pd){pd.addEventListener('change',updatePd);updatePd();}
  function bindCyl(cylId,axisId){
    var cyl=root.querySelector(cylId),axis=root.querySelector(axisId);
    if(!cyl||!axis) return;
    function sync(){
      var empty=!cyl.value||cyl.value==='0'||cyl.value==='0.00';
      axis.style.opacity=empty?'0.55':'1';
      axis.placeholder=empty?'در صورت CYL':'۰ تا ۱۸۰';
    }
    cyl.addEventListener('input',sync);sync();
  }
  bindCyl('#od_cyl','#od_axis');bindCyl('#os_cyl','#os_axis');
  var zone=root.querySelector('.ez-rx__upload-zone');
  var file=root.querySelector('#rx_upload');
  var nameEl=root.querySelector('.ez-rx__upload-name');
  if(zone&&file){
    zone.addEventListener('click',function(){file.click();});
    zone.addEventListener('dragover',function(e){e.preventDefault();zone.classList.add('is-drag');});
    zone.addEventListener('dragleave',function(){zone.classList.remove('is-drag');});
    zone.addEventListener('drop',function(e){
      e.preventDefault();zone.classList.remove('is-drag');
      if(e.dataTransfer&&e.dataTransfer.files&&e.dataTransfer.files[0]){
        try{file.files=e.dataTransfer.files;}catch(err){}
        if(nameEl) nameEl.textContent='فایل: '+e.dataTransfer.files[0].name;
      }
    });
    file.addEventListener('change',function(){
      if(file.files&&file.files[0]&&nameEl) nameEl.textContent='فایل: '+file.files[0].name;
    });
  }
  var sg=root.querySelector('#sg_mode');
  if(sg){
    function tog(){
      var rx=sg.value==='rx';
      root.querySelectorAll('[data-sg]').forEach(function(el){
        el.classList.toggle('ez-rx__hidden',!rx && el.getAttribute('data-sg')==='rx');
      });
    }
    sg.addEventListener('change',tog);tog();
  }
})();
EZCODE,
      ),
    );
  }
}
