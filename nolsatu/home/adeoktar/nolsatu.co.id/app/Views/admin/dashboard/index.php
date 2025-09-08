<?= $this->extend('admin/layouts/main') ?>

<?= $this->section('title') ?>Dashboard<?= $this->endSection() ?>

<?= $this->section('styles') ?>
<style>
  /* KPI Cards */
  .card-kpi{
    border:0; border-radius:1rem;
    background: var(--bs-body-bg);
    box-shadow: 0 .5rem 1.25rem rgba(0,0,0,.06);
    transition: transform .15s ease, box-shadow .15s ease;
  }
  .card-kpi:hover{
    transform: translateY(-3px);
    box-shadow: 0 1rem 2rem rgba(0,0,0,.12);
  }
  .kpi-icon{
    display:inline-grid; place-items:center;
    width:52px; height:52px; border-radius:50%;
    background: var(--bs-light);
    font-size:1.25rem;
  }
  [data-bs-theme="dark"] .kpi-icon{ background: rgba(255,255,255,.08); }
  .kpi-label{ font-size:.85rem; color: var(--bs-secondary-color); }
  .kpi-value{ font-weight:800; letter-spacing:.2px; }
  .card-kpi .stretched-link::after{ border-radius:1rem; }
  .dash-actions .btn{ border-radius:.75rem; }
</style>
<?= $this->endSection() ?>

<?= $this->section('toolbar') ?>
<div class="dash-actions d-flex flex-wrap gap-2">
  <a href="<?= site_url('admin/posts') ?>" class="btn btn-primary btn-sm">
    <i class="bi bi-plus-lg me-1"></i> Tulis Post
  </a>
  <a href="<?= site_url('admin/services') ?>" class="btn btn-outline-primary btn-sm">
    <i class="bi bi-box me-1"></i> Tambah Service
  </a>
  <a href="<?= site_url('admin/announcements') ?>" class="btn btn-outline-warning btn-sm">
    <i class="bi bi-megaphone me-1"></i> Pengumuman
  </a>
  <a href="<?= site_url('admin/settings') ?>" class="btn btn-outline-secondary btn-sm">
    <i class="bi bi-gear me-1"></i> Settings
  </a>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<h4 class="mb-3">Ringkasan</h4>

<?php
  // label, key var, icon, color, url
  $cards = [
    ['Services','c_services','bi-box','primary',        site_url('admin/services')],
    ['Posts','c_posts','bi-newspaper','info',           site_url('admin/posts')],
    ['FAQs','c_faqs','bi-question-circle','success',    site_url('admin/faqs')],
    ['Documents','c_docs','bi-file-earmark-arrow-down','secondary', site_url('admin/documents')],
    ['Announcements','c_ann','bi-megaphone','warning',  site_url('admin/announcements')],
    ['Menu Items','c_menu','bi-list-nested','dark',     site_url('admin/menu')],
    ['Complaints','c_complaints','bi-inboxes','danger', site_url('admin/complaints')],
    ['Subscribers','c_subs','bi-people','primary',      site_url('admin/subscribers')],
  ];
?>

<div class="row g-3 g-md-4">
  <?php foreach ($cards as [$label,$key,$icon,$color,$url]): ?>
    <?php $val = (int)($$key ?? 0); ?>
    <div class="col-6 col-md-4 col-lg-3">
      <div class="card card-kpi h-100">
        <div class="card-body d-flex align-items-center gap-3">
          <span class="kpi-icon text-<?= $color ?>"><i class="bi <?= $icon ?>"></i></span>
          <div class="flex-grow-1">
            <div class="kpi-label"><?= esc($label) ?></div>
            <div class="h3 kpi-value mb-0"><?= $val ?></div>
          </div>
          <a href="<?= $url ?>" class="stretched-link" aria-label="Buka <?= esc($label) ?>"></a>
        </div>
      </div>
    </div>
  <?php endforeach; ?>
</div>

<!-- (Opsional) dua panel cepat -->
<div class="row g-3 g-md-4 mt-1">
  <div class="col-lg-7">
    <div class="card card-kpi">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-2">
          <h6 class="mb-0">Aktivitas</h6>
          <a href="<?= site_url('admin/posts') ?>" class="small text-decoration-none">Lihat semua</a>
        </div>
        <ul class="list-group list-group-flush small">
          <li class="list-group-item d-flex justify-content-between">
            <span>Publikasi terbaru</span>
            <strong><?= (int)($c_posts_published ?? 0) ?></strong>
          </li>
          <li class="list-group-item d-flex justify-content-between">
            <span>Pengaduan baru</span>
            <strong><?= (int)($c_complaints_new ?? 0) ?></strong>
          </li>
          <li class="list-group-item d-flex justify-content-between">
            <span>Pelanggan buletin</span>
            <strong><?= (int)($c_subs ?? 0) ?></strong>
          </li>
        </ul>
      </div>
    </div>
  </div>
  <div class="col-lg-5">
    <div class="card card-kpi">
      <div class="card-body">
        <h6 class="mb-2">Sistem</h6>
        <div class="row small g-2">
          <div class="col-6">
            <div class="p-3 rounded-3 border bg-body-tertiary h-100">
              <div class="text-secondary">Role</div>
              <div class="fw-semibold"><?= esc(session('admin_role') ?? '-') ?></div>
            </div>
          </div>
          <div class="col-6">
            <div class="p-3 rounded-3 border bg-body-tertiary h-100">
              <div class="text-secondary">Pengguna</div>
              <div class="fw-semibold"><?= esc(session('admin_username') ?? '-') ?></div>
            </div>
          </div>
        </div>
        <div class="mt-3 d-flex gap-2">
          <a href="<?= site_url('admin/logout') ?>" class="btn btn-outline-danger btn-sm">
            <i class="bi bi-box-arrow-right me-1"></i> Keluar
          </a>
          <a href="<?= site_url('admin/settings') ?>" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-sliders me-1"></i> Preferensi
          </a>
        </div>
      </div>
    </div>
  </div>
</div>

<?= $this->endSection() ?>
