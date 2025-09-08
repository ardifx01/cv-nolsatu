<?= $this->extend('admin/layouts/main') ?>
<?= $this->section('title') ?>Settings<?= $this->endSection() ?>

<?= $this->section('styles') ?>
<meta name="csrf-token" content="<?= csrf_hash() ?>">
<style>
  .card-soft{border:0;border-radius:1rem;box-shadow:0 .5rem 1.25rem rgba(0,0,0,.06)}
  .section-title{font-weight:700}
  .form-hint{font-size:.85rem;color:var(--bs-secondary-color)}
  .img-thumb{width:120px;height:48px;object-fit:contain;border:1px solid var(--bs-border-color);border-radius:.5rem;background:#fff}
  .img-thumb-square{width:48px;height:48px;object-fit:contain;border:1px solid var(--bs-border-color);border-radius:.5rem;background:#fff}
  .img-thumb-og{width:180px;height:94px;object-fit:cover;border:1px solid var(--bs-border-color);border-radius:.5rem;background:#fff}
  .btn-seg .btn{border-radius:.5rem}
  .btn-check:checked + .btn{border-color:var(--bs-primary);box-shadow:0 0 0 .2rem rgba(13,110,253,.15)}
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<?php
  // helper kecil untuk get value setting
  $get = fn($k,$d='') => $s[$k] ?? $d;
?>

<div class="d-flex align-items-center justify-content-between mb-3">
  <h4 class="mb-0 section-title">Pengaturan Situs</h4>
  <?php if(session('success')): ?>
    <span class="badge bg-success-subtle text-success-emphasis px-3 py-2"><?= esc(session('success')) ?></span>
  <?php endif; ?>
</div>

<form method="post" action="<?= site_url('admin/settings') ?>" enctype="multipart/form-data" class="needs-validation" novalidate>
  <?= csrf_field() ?>

  <div class="row g-4">
    <!-- Identitas -->
    <div class="col-xl-6">
      <div class="card card-soft">
        <div class="card-body">
          <h6 class="mb-3"><i class="bi bi-window me-1"></i> Identitas Situs</h6>

          <div class="mb-3">
            <label class="form-label">Judul Situs</label>
            <input type="text" class="form-control" name="site__title" value="<?= esc($get('site.title','Imigrasi Jambi — Pelayanan Keimigrasian')) ?>" required>
          </div>

          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label">Judul Pendek</label>
              <input type="text" class="form-control" name="site__title_short" value="<?= esc($get('site.title_short','Imigrasi Jambi')) ?>">
            </div>
            <div class="col-md-6">
              <label class="form-label">Sub Judul</label>
              <input type="text" class="form-control" name="site__subtitle" value="<?= esc($get('site.subtitle','Portal Layanan Resmi')) ?>">
            </div>
          </div>

          <div class="mt-3">
            <label class="form-label">Meta Description</label>
            <textarea class="form-control" name="site__meta_description" rows="3"><?= esc($get('site.meta_description','Layanan resmi Imigrasi Jambi: paspor, izin tinggal, informasi WNA/WNI, pengaduan, dan status permohonan.')) ?></textarea>
            <div class="form-hint mt-1">Saran 120–160 karakter.</div>
          </div>

          <div class="mt-3">
            <label class="form-label">Meta Robots</label>
            <select class="form-select" name="site__meta_robots">
              <?php $robots = $get('site.meta_robots','index,follow'); ?>
              <option value="index,follow"   <?= $robots==='index,follow'?'selected':'' ?>>index,follow</option>
              <option value="noindex,follow" <?= $robots==='noindex,follow'?'selected':'' ?>>noindex,follow</option>
              <option value="index,nofollow" <?= $robots==='index,nofollow'?'selected':'' ?>>index,nofollow</option>
              <option value="noindex,nofollow" <?= $robots==='noindex,nofollow'?'selected':'' ?>>noindex,nofollow</option>
            </select>
          </div>
        </div>
      </div>
    </div>

    <!-- Tampilan -->
    <div class="col-xl-6">
      <div class="card card-soft">
        <div class="card-body">
          <h6 class="mb-3"><i class="bi bi-palette me-1"></i> Tampilan</h6>

          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label">Bahasa (Lang)</label>
              <?php $lang = $get('site.lang','id'); ?>
              <select class="form-select" name="site__lang">
                <option value="id" <?= $lang==='id'?'selected':'' ?>>Indonesia (id)</option>
                <option value="en" <?= $lang==='en'?'selected':'' ?>>English (en)</option>
              </select>
            </div>

            <div class="col-md-6">
              <label class="form-label d-block">Tema Default</label>
              <?php $theme = $get('site.theme','light'); ?>
              <div class="btn-group btn-seg" role="group" aria-label="Theme">
                <input type="radio" class="btn-check" name="site__theme" id="thLight" value="light" <?= $theme==='light'?'checked':'' ?>>
                <label class="btn btn-outline-primary" for="thLight"><i class="bi bi-sun me-1"></i>Light</label>

                <input type="radio" class="btn-check" name="site__theme" id="thDark" value="dark" <?= $theme==='dark'?'checked':'' ?>>
                <label class="btn btn-outline-primary" for="thDark"><i class="bi bi-moon me-1"></i>Dark</label>

                <input type="radio" class="btn-check" name="site__theme" id="thAuto" value="auto" <?= $theme==='auto'?'checked':'' ?>>
                <label class="btn btn-outline-primary" for="thAuto"><i class="bi bi-circle-half me-1"></i>Auto</label>
              </div>
              <div class="form-hint mt-1">Auto mengikuti preferensi OS pengunjung.</div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Branding (Upload) -->
    <div class="col-xl-6">
      <div class="card card-soft">
        <div class="card-body">
          <h6 class="mb-3"><i class="bi bi-image me-1"></i> Branding</h6>

          <!-- LOGO -->
          <div class="mb-3">
            <label class="form-label d-flex align-items-center gap-2">
              <span>Logo</span>
              <?php if($url = $get('site.logo_url')): ?>
                <img src="<?= esc($url) ?>" class="img-thumb" alt="Logo" id="prevLogo">
              <?php else: ?>
                <img src="" class="img-thumb d-none" alt="Logo" id="prevLogo">
              <?php endif; ?>
            </label>
            <input type="hidden" name="site__logo_url" value="<?= esc($get('site.logo_url','')) ?>">
            <input type="file" class="form-control" name="logo_file" accept=".png,.jpg,.jpeg,.webp,.svg">
            <div class="form-hint mt-1">PNG/SVG/WebP disarankan. Rasio horizontal.</div>
          </div>

          <!-- FAVICON -->
          <div class="mb-3">
            <label class="form-label d-flex align-items-center gap-2">
              <span>Favicon</span>
              <?php if($fav = $get('site.favicon_url')): ?>
                <img src="<?= esc($fav) ?>" class="img-thumb-square" alt="Favicon" id="prevFav">
              <?php else: ?>
                <img src="" class="img-thumb-square d-none" alt="Favicon" id="prevFav">
              <?php endif; ?>
            </label>
            <input type="hidden" name="site__favicon_url" value="<?= esc($get('site.favicon_url','')) ?>">
            <input type="file" class="form-control" name="favicon_file" accept=".ico,.png,.svg">
            <div class="form-hint mt-1">ICO/PNG 32×32 atau 48×48 px.</div>
          </div>

          <!-- OG Image -->
          <div class="mb-2">
            <label class="form-label d-flex align-items-center gap-2">
              <span>OG Image</span>
              <?php if($og = $get('site.og_image')): ?>
                <img src="<?= esc($og) ?>" class="img-thumb-og" alt="OG" id="prevOg">
              <?php else: ?>
                <img src="" class="img-thumb-og d-none" alt="OG" id="prevOg">
              <?php endif; ?>
            </label>
            <input type="hidden" name="site__og_image" value="<?= esc($get('site.og_image','')) ?>">
            <input type="file" class="form-control" name="og_file" accept=".jpg,.jpeg,.png,.webp">
            <div class="form-hint mt-1">Disarankan 1200×630 px (JPG/PNG/WebP).</div>
          </div>
        </div>
      </div>
    </div>

    <!-- Kontak & Sosial -->
    <div class="col-xl-6">
      <div class="card card-soft">
        <div class="card-body">
          <h6 class="mb-3"><i class="bi bi-person-lines-fill me-1"></i> Kontak & Sosial</h6>

          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label">Telepon</label>
              <input type="text" class="form-control" name="org__phone" value="<?= esc($get('org.phone','')) ?>">
            </div>
            <div class="col-md-6">
              <label class="form-label">Email</label>
              <input type="email" class="form-control" name="org__email" value="<?= esc($get('org.email','')) ?>">
            </div>
          </div>

          <div class="row g-3 mt-1">
            <div class="col-md-6">
              <label class="form-label">Instagram</label>
              <input type="url" class="form-control" name="social__instagram" value="<?= esc($get('social.instagram','')) ?>">
            </div>
            <div class="col-md-6">
              <label class="form-label">Facebook</label>
              <input type="url" class="form-control" name="social__facebook" value="<?= esc($get('social.facebook','')) ?>">
            </div>
            <div class="col-md-6">
              <label class="form-label">YouTube</label>
              <input type="url" class="form-control" name="social__youtube" value="<?= esc($get('social.youtube','')) ?>">
            </div>
            <div class="col-md-6">
              <label class="form-label">X (Twitter)</label>
              <input type="url" class="form-control" name="social__x" value="<?= esc($get('social.x','')) ?>">
            </div>
          </div>
        </div>
      </div>
    </div>

<!-- Tema Warna -->
<div class="col-xl-6">
  <div class="card card-soft">
    <div class="card-body">
      <h6 class="mb-3"><i class="bi bi-palette me-1"></i> Tema Warna</h6>

      <div class="row g-3">
        <?php
          $palette = [
            'theme.primary'   => ['label'=>'Primary',   'def'=>'#0d6efd'],
            'theme.success'   => ['label'=>'Success',   'def'=>'#198754'],
            'theme.secondary' => ['label'=>'Secondary', 'def'=>'#6c757d'],
            'theme.info'      => ['label'=>'Info',      'def'=>'#0dcaf0'],
            'theme.warning'   => ['label'=>'Warning',   'def'=>'#ffc107'],
            'theme.danger'    => ['label'=>'Danger',    'def'=>'#dc3545'],
            'theme.light'     => ['label'=>'Light',     'def'=>'#f8f9fa'],
            'theme.dark'      => ['label'=>'Dark',      'def'=>'#212529'],
          ];
        ?>
        <?php foreach($palette as $k => $meta): 
          $val = $get($k, $meta['def']);
          $name = str_replace('.','__',$k);
        ?>
          <div class="col-md-6">
            <label class="form-label d-flex align-items-center justify-content-between">
              <span><?= esc($meta['label']) ?></span>
              <small class="text-muted"><?= esc($val) ?></small>
            </label>
            <input type="color" class="form-control form-control-color w-100" name="<?= $name ?>" value="<?= esc($val) ?>">
          </div>
        <?php endforeach; ?>
      </div>

      <div class="row g-3 mt-1">
        <div class="col-md-6">
          <label class="form-label">Primary (Dark Mode Override)</label>
          <?php $v = $get('theme.dark.primary', $get('theme.primary','#0d6efd')); ?>
          <input type="color" class="form-control form-control-color w-100" name="theme__dark__primary" value="<?= esc($v) ?>">
          <div class="form-hint">Kosongkan bila ingin sama dengan Primary.</div>
        </div>

        <div class="col-md-6">
          <label class="form-label">Radius 2XL</label>
          <input type="text" class="form-control" name="theme__radius_2xl" value="<?= esc($get('theme.radius_2xl','1.25rem')) ?>">
          <div class="form-hint">Contoh: <code>1.25rem</code> atau <code>20px</code>.</div>
        </div>

        <div class="col-md-6">
          <label class="form-label">Shadow Soft</label>
          <input type="text" class="form-control" name="theme__shadow_soft" value="<?= esc($get('theme.shadow_soft','0 .5rem 1.25rem rgba(0,0,0,.06)')) ?>">
        </div>
        <div class="col-md-6">
          <label class="form-label">Shadow Hover</label>
          <input type="text" class="form-control" name="theme__shadow_hover" value="<?= esc($get('theme.shadow_hover','0 1rem 2rem rgba(0,0,0,.12)')) ?>">
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Hero Section -->
<div class="col-xl-6">
  <div class="card card-soft">
    <div class="card-body">
      <h6 class="mb-3"><i class="bi bi-image-alt me-1"></i> Hero</h6>

      <!-- Background Image -->
      <div class="mb-3">
        <label class="form-label d-flex align-items-center gap-2">
          <span>Gambar Latar</span>
          <?php if($bg = $get('hero.bg_image')): ?>
            <img src="<?= esc($bg) ?>" class="img-thumb-og" alt="Hero BG" id="prevHeroBg">
          <?php else: ?>
            <img src="" class="img-thumb-og d-none" alt="Hero BG" id="prevHeroBg">
          <?php endif; ?>
        </label>
        <input type="hidden" name="hero__bg_image" value="<?= esc($get('hero.bg_image','')) ?>">
        <input type="file" class="form-control" name="hero_bg_file" accept=".jpg,.jpeg,.png,.webp">
        <div class="form-hint mt-1">Disarankan 1600×900 ke atas.</div>
      </div>

      <!-- Overlay & Height -->
      <div class="row g-3">
        <div class="col-md-6">
          <label class="form-label">Overlay Start (RGBA/HEX)</label>
          <input type="text" class="form-control" name="hero__overlay_start" value="<?= esc($get('hero.overlay_start','rgba(13,110,253,.10)')) ?>">
          <div class="form-hint">Contoh: <code>rgba(13,110,253,.10)</code> atau <code>#0d6efd</code>.</div>
        </div>
        <div class="col-md-6">
          <label class="form-label">Overlay End (RGBA/HEX)</label>
          <input type="text" class="form-control" name="hero__overlay_end" value="<?= esc($get('hero.overlay_end','rgba(25,135,84,.08)')) ?>">
        </div>
        <div class="col-md-6">
          <label class="form-label">Tinggi Minimal (vh)</label>
          <input type="number" min="30" max="100" class="form-control" name="hero__min_height_vh" value="<?= esc($get('hero.min_height_vh','62')) ?>">
        </div>
      </div>

      <hr>

      <!-- Teks -->
      <div class="mb-3">
        <label class="form-label">Judul</label>
        <input type="text" class="form-control" name="hero__title" value="<?= esc($get('hero.title','Pelayanan Keimigrasian Cepat, Transparan, dan Humanis')) ?>">
      </div>
      <div class="mb-3">
        <label class="form-label">Subjudul</label>
        <textarea class="form-control" name="hero__subtitle" rows="2"><?= esc($get('hero.subtitle','Portal resmi Imigrasi Jambi. Ajukan paspor, cek status permohonan, baca informasi persyaratan, dan sampaikan pengaduan secara daring.')) ?></textarea>
      </div>

      <!-- Tombol -->
      <div class="row g-3">
        <div class="col-md-6">
          <label class="form-label d-block">Tombol 1</label>
          <div class="form-check form-switch mb-2">
            <?php $show1 = $get('hero.btn1.show','1')==='1'; ?>
            <input class="form-check-input" type="checkbox" name="hero__btn1__show" value="1" id="btn1show" <?= $show1?'checked':'' ?>>
            <label class="form-check-label" for="btn1show">Tampilkan</label>
          </div>
          <input type="text" class="form-control mb-2" name="hero__btn1__text" value="<?= esc($get('hero.btn1.text','Ajukan Paspor')) ?>" placeholder="Teks tombol">
          <input type="text" class="form-control mb-2" name="hero__btn1__href" value="<?= esc($get('hero.btn1.href','#layanan-online')) ?>" placeholder="#target atau URL">
          <?php $var1 = $get('hero.btn1.variant','primary'); ?>
          <select class="form-select" name="hero__btn1__variant">
            <?php foreach(['primary','success','warning','info','secondary','danger','dark'] as $opt): ?>
              <option value="<?= $opt ?>" <?= $var1===$opt?'selected':'' ?>><?= ucfirst($opt) ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="col-md-6">
          <label class="form-label d-block">Tombol 2</label>
          <div class="form-check form-switch mb-2">
            <?php $show2 = $get('hero.btn2.show','1')==='1'; ?>
            <input class="form-check-input" type="checkbox" name="hero__btn2__show" value="1" id="btn2show" <?= $show2?'checked':'' ?>>
            <label class="form-check-label" for="btn2show">Tampilkan</label>
          </div>
          <input type="text" class="form-control mb-2" name="hero__btn2__text" value="<?= esc($get('hero.btn2.text','Pengaduan')) ?>" placeholder="Teks tombol">
          <input type="text" class="form-control mb-2" name="hero__btn2__href" value="<?= esc($get('hero.btn2.href','#pengaduan')) ?>" placeholder="#target atau URL">
          <?php $var2 = $get('hero.btn2.variant','success'); ?>
          <select class="form-select" name="hero__btn2__variant">
            <?php foreach(['primary','success','warning','info','secondary','danger','dark'] as $opt): ?>
              <option value="<?= $opt ?>" <?= $var2===$opt?'selected':'' ?>><?= ucfirst($opt) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>

      <hr>

<!-- Leader show -->
<input type="hidden" name="hero__leader__show" value="0">
<div class="form-check form-switch mb-3">
  <?php $leaderShow = $get('hero.leader.show','1')==='1'; ?>
  <input class="form-check-input" type="checkbox" name="hero__leader__show" value="1" id="leadershow" <?= $leaderShow?'checked':'' ?>>
  <label class="form-check-label" for="leadershow">Tampilkan Profil Kepala</label>
</div>

<!-- Tombol 1 -->
<input type="hidden" name="hero__btn1__show" value="0">
<!-- ... checkbox btn1 show ... -->

<!-- Tombol 2 -->
<input type="hidden" name="hero__btn2__show" value="0">
<!-- ... checkbox btn2 show ... -->



      <div class="row g-3">
        <div class="col-md-8">
          <label class="form-label">Nama</label>
          <input type="text" class="form-control" name="hero__leader__name" value="<?= esc($get('hero.leader.name','Nama Kepala Imigrasi')) ?>">
        </div>
        <div class="col-md-4">
          <label class="form-label">Jabatan</label>
          <input type="text" class="form-control" name="hero__leader__title" value="<?= esc($get('hero.leader.title','Kepala Kantor Imigrasi Jambi')) ?>">
        </div>
      </div>

      <div class="mt-3">
        <label class="form-label d-flex align-items-center gap-2">
          <span>Foto Pimpinan</span>
          <?php if($ph = $get('hero.leader.photo')): ?>
            <img src="<?= esc($ph) ?>" class="img-thumb" alt="Leader" id="prevLeader">
          <?php else: ?>
            <img src="" class="img-thumb d-none" alt="Leader" id="prevLeader">
          <?php endif; ?>
        </label>
        <input type="hidden" name="hero__leader__photo" value="<?= esc($get('hero.leader.photo','')) ?>">
        <input type="file" class="form-control" name="hero_leader_file" accept=".jpg,.jpeg,.png,.webp">
      </div>

    </div>
  </div>
</div>

  </div><!-- /row -->

  <div class="mt-3 d-flex gap-2">
    <button class="btn btn-primary"><i class="bi bi-save me-1"></i> Simpan</button>
    <a href="<?= site_url('admin') ?>" class="btn btn-outline-secondary">Batal</a>
  </div>
</form>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
  // Preview gambar saat pilih file
  const bindPreview = (inputName, imgId) => {
    const inp = document.querySelector(`input[name="${inputName}"]`);
    const img = document.getElementById(imgId);
    if(!inp || !img) return;
    inp.addEventListener('change', e=>{
      const f = e.target.files?.[0];
      if(!f) return;
      const url = URL.createObjectURL(f);
      img.src = url;
      img.classList.remove('d-none');
    });
  };

  bindPreview('logo_file','prevLogo');
  bindPreview('favicon_file','prevFav');
  bindPreview('og_file','prevOg');
    bindPreview('hero_bg_file','prevHeroBg');
    bindPreview('hero_leader_file','prevLeader');

  // HTML5 validation
  (() => {
    const forms = document.querySelectorAll('.needs-validation');
    Array.from(forms).forEach(form => {
      form.addEventListener('submit', event => {
        if (!form.checkValidity()) {
          event.preventDefault(); event.stopPropagation();
        }
        form.classList.add('was-validated');
      }, false);
    });
  })();
</script>
<?= $this->endSection() ?>
