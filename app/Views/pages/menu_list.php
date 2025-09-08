<?= $this->extend('layouts/main') ?>
<?php $pageTitle = $menu['title'] ?? 'Halaman'; ?>
<?= $this->section('title') ?><?= esc($pageTitle) ?><?= $this->endSection() ?>

<?= $this->section('styles') ?>
<style>
  .card.hoverable { transition: transform .2s ease, box-shadow .2s ease; }
  .card.hoverable:hover { transform: translateY(-2px); box-shadow: 0 10px 24px rgba(0,0,0,.08); }
  .ratio-16x9 { aspect-ratio: 16/9; object-fit: cover; width: 100%; }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<section class="py-5">
  <div class="container">
    <nav aria-label="breadcrumb" class="mb-3">
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?= site_url('/') ?>">Beranda</a></li>
        <?php if(!empty($parent)): ?>
          <li class="breadcrumb-item"><a href="<?= esc($parent['url'] ?? '#') ?>"><?= esc($parent['title'] ?? '') ?></a></li>
        <?php endif; ?>
        <li class="breadcrumb-item active"><?= esc($pageTitle) ?></li>
      </ol>
    </nav>

    <h1 class="mb-4"><?= esc($pageTitle) ?></h1>

    <?php if(empty($pages)): ?>
      <div class="alert alert-info mb-0">Belum ada halaman pada menu ini.</div>
    <?php else: ?>
      <div class="row g-4">
        <?php foreach($pages as $p): ?>
          <div class="col-md-6 col-lg-4">
            <a class="text-decoration-none text-reset" href="<?= esc($p['path']) ?>">
              <div class="card h-100 hoverable">
                <?php if(!empty($p['cover_image'])): ?>
                  <img src="<?= esc($p['cover_image']) ?>" alt="<?= esc($p['title']) ?>" class="ratio-16x9">
                <?php endif; ?>
                <div class="card-body d-flex flex-column">
                  <h5 class="card-title"><?= esc($p['title']) ?></h5>
                  <?php if(!empty($p['excerpt'])): ?>
                    <p class="card-text text-secondary small"><?= esc($p['excerpt']) ?></p>
                  <?php endif; ?>
                  <?php if(!empty($p['published_at'])): ?>
                    <div class="mt-auto text-secondary small">
                      <i class="bi bi-calendar2-week me-1"></i>
                      <?= esc(date('d M Y', strtotime($p['published_at']))) ?>
                    </div>
                  <?php endif; ?>
                </div>
              </div>
            </a>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <!-- (opsional) sidebar/menuHtml/ann bisa dipasang di layout, biar halaman ini fokus list -->
  </div>
</section>
<?= $this->endSection() ?>
