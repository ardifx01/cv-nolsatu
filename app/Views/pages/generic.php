<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?><?= esc($menu['title']) ?><?= $this->endSection() ?>

<?= $this->section('content') ?>
<?php
  $pages      = $pages ?? [];
  $pagesCount = is_array($pages) ? count($pages) : 0;

  $req        = \Config\Services::request();
  $pickSlug   = trim((string)$req->getGet('p') ?? '');

  $activePage = null;
  if ($pagesCount > 0) {
    if ($pickSlug !== '') {
      foreach ($pages as $pg) {
        if (!empty($pg['slug']) && $pg['slug'] === $pickSlug) {
          $activePage = $pg; break;
        }
      }
    }
    if (!$activePage) $activePage = $pages[0];
  }

  $pageUrlOf = function(array $p){
    return !empty($p['path']) ? site_url(ltrim($p['path'], '/')) : site_url('page/'.$p['slug']);
  };
?>
<section class="py-5">
  <div class="container">
    <nav aria-label="breadcrumb" class="mb-3">
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?= site_url('/') ?>">Beranda</a></li>
        <?php if($parent): ?>
          <li class="breadcrumb-item"><a href="<?= esc($parent['url'] ?? '#') ?>"><?= esc($parent['title']) ?></a></li>
        <?php endif; ?>
        <li class="breadcrumb-item active"><?= esc($menu['title']) ?></li>
      </ol>
    </nav>

    <h1 class="mb-3"><?= esc($menu['title']) ?></h1>

    <div class="row g-4">
      <?php if ($pagesCount === 0): ?>
        <!-- 0 PAGE → full 12 -->
        <div class="col-12">
          <div class="alert alert-light border shadow-sm p-4 text-center">
            <i class="bi bi-info-circle text-primary fs-3 d-block mb-2"></i>
            <strong>Belum ada konten yang terhubung.</strong>
            <p class="mb-0 text-muted">Silakan kembali lagi nanti ketika halaman terkait sudah tersedia.</p>
          </div>
        </div>

      <?php elseif ($pagesCount === 1): ?>
        <!-- 1 PAGE → full 12 -->
        <div class="col-12">
          <?php $p = $activePage; ?>
          <article class="card shadow-soft rounded-2xl">
            <?php if(!empty($p['cover_image'])): ?>
              <img src="<?= esc($p['cover_image']) ?>" class="card-img-top" alt="<?= esc($p['title']) ?>">
            <?php endif; ?>
            <div class="card-body">
              <h4 class="mb-1"><?= esc($p['title']) ?></h4>
              <?php if(!empty($p['published_at'])): ?>
                <div class="text-muted small mb-3">
                  <i class="bi bi-calendar3 me-1"></i><?= date('d M Y', strtotime($p['published_at'])) ?>
                </div>
              <?php endif; ?>
              <?php if(!empty($p['excerpt'])): ?>
                <p class="lead"><?= esc($p['excerpt']) ?></p>
              <?php endif; ?>
              <div class="content-html">
                <?= $p['content_html'] ?? '' ?>
              </div>
            </div>
          </article>
        </div>

      <?php else: ?>
        <!-- MULTI PAGES → full 12, pecah 3 : 9 -->
        <div class="col-12">
          <div class="card shadow-soft rounded-2xl">
            <div class="card-body">
              <div class="row g-4">
                <!-- KIRI: 3 -->
                <div class="col-md-4 col-lg-3">
                  <!--<h5 class="mb-3">Daftar Halaman Terkait</h5>-->
                  <div class="list-group">
                    <?php foreach($pages as $pg):
                      $isActive = isset($activePage['slug']) && $pg['slug'] === $activePage['slug'];
                      $href     = current_url().'?p='.urlencode($pg['slug']);
                    ?>
                      <a href="<?= $href ?>"
                         class="list-group-item list-group-item-action <?= $isActive?'active':'' ?>">
                        <div class="d-flex justify-content-between align-items-center">
                          <span class="<?= $isActive?'fw-semibold':'' ?>"><?= esc($pg['title']) ?></span>
                          <!--<?php if(!empty($pg['published_at'])): ?>-->
                          <!--  <small class="<?= $isActive?'text-white-50':'text-muted' ?>">-->
                          <!--    <?= date('d M Y', strtotime($pg['published_at'])) ?>-->
                          <!--  </small>-->
                          <!--<?php endif; ?>-->
                        </div>
                      </a>
                    <?php endforeach; ?>
                  </div>
                </div>

                <!-- KANAN: 9 -->
                <div class="col-md-8 col-lg-9">
                  <?php if($activePage): ?>
                    <?php if(!empty($activePage['cover_image'])): ?>
                      <img src="<?= esc($activePage['cover_image']) ?>" class="img-fluid rounded mb-3" alt="<?= esc($activePage['title']) ?>">
                    <?php endif; ?>

                    <h4 class="mb-1"><?= esc($activePage['title']) ?></h4>

                    <!--<?php if(!empty($activePage['published_at'])): ?>-->
                    <!--  <div class="text-muted small mb-2">-->
                    <!--    <i class="bi bi-calendar3 me-1"></i><?= date('d M Y', strtotime($activePage['published_at'])) ?>-->
                    <!--  </div>-->
                    <!--<?php endif; ?>-->

                    <?php if(!empty($activePage['excerpt'])): ?>
                      <p class="lead"><?= esc($activePage['excerpt']) ?></p>
                    <?php endif; ?>

                    <div class="content-html">
                      <?= $activePage['content_html'] ?? '' ?>
                    </div>

                    <!--<div class="mt-3">-->
                    <!--  <a class="btn btn-sm btn-outline-primary" href="<?= $pageUrlOf($activePage) ?>" target="_blank" rel="noopener">Buka Halaman</a>-->
                    <!--</div>-->
                  <?php endif; ?>
                </div>
              </div>
            </div>
          </div>
        </div>
      <?php endif; ?>

      <!-- DOCS: selalu di bawah sebagai col-12 -->
      <?php if(!empty($docs)): ?>
        <div class="col-12">
          <div class="card shadow-soft rounded-2xl">
            <div class="card-body">
              <h6 class="mb-3">Dokumen Terkait</h6>
              <ul class="list-group list-group-flush">
                <?php foreach($docs as $d): ?>
                  <li class="list-group-item d-flex justify-content-between align-items-center">
                    <?= esc($d['title']) ?>
                    <a href="<?= esc($d['file_url']) ?>" target="_blank" class="btn btn-sm btn-outline-primary">Unduh</a>
                  </li>
                <?php endforeach; ?>
              </ul>
            </div>
          </div>
        </div>
      <?php endif; ?>
    </div>

  </div>
</section>
<?= $this->endSection() ?>
