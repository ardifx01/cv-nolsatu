<?= $this->extend('layouts/main') ?>

<?php
  $p = $page ?? [];
  $title = $p['seo_title'] ?: $p['title'] ?? ($menu['title'] ?? 'Halaman');
  $desc  = $p['seo_description'] ?: ($p['excerpt'] ?? mb_substr(strip_tags($p['content_html'] ?? ''),0,160));
  $ogImg = $p['og_image'] ?: ($p['cover_image'] ?? '');
?>
<?= $this->section('title') ?><?= esc($title) ?><?= $this->endSection() ?>
<?= $this->section('meta_desc') ?><?= esc($desc) ?><?= $this->endSection() ?>
<?php if ($ogImg): ?>
  <?= $this->section('og_image_url') ?><?= esc($ogImg) ?><?= $this->endSection() ?>
<?php endif; ?>

<?= $this->section('content') ?>
<section class="py-5">
  <div class="container">
    <nav aria-label="breadcrumb" class="mb-3">
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?= site_url('/') ?>">Beranda</a></li>
        <?php if(!empty($parent)): ?>
          <li class="breadcrumb-item"><a href="<?= esc($parent['url'] ?? '#') ?>"><?= esc($parent['title'] ?? '') ?></a></li>
        <?php endif; ?>
        <li class="breadcrumb-item active"><?= esc($menu['title'] ?? $p['title'] ?? 'Halaman') ?></li>
      </ol>
    </nav>

    <div class="row g-4">
      <div class="col-lg-8">
        <article class="card shadow-soft rounded-2xl">
          <div class="card-body p-4 p-lg-5">
            <h1 class="mb-3"><?= esc($p['title'] ?? $menu['title'] ?? '') ?></h1>
            <?php if (!empty($p['excerpt'])): ?>
              <p class="lead text-secondary mb-4"><?= esc($p['excerpt']) ?></p>
            <?php endif; ?>

            <?php if (!empty($p['cover_image'])): ?>
              <img src="<?= esc($p['cover_image']) ?>" alt="" class="img-fluid rounded-2 mb-4" loading="lazy">
            <?php endif; ?>

            <!-- konten HTML langsung (sudah disanitasi saat simpan) -->
            <div class="page-content">
              <?= $p['content_html'] ?? '' ?>
            </div>
          </div>
        </article>
      </div>

      <div class="col-lg-4">
        <?php if(!empty($docs)): ?>
        <div class="card shadow-soft rounded-2xl mb-4">
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
        <?php endif; ?>

        <?php if(!empty($faqs)): ?>
        <div class="card shadow-soft rounded-2xl">
          <div class="card-body">
            <h6 class="mb-3">FAQ Terkait</h6>
            <div class="accordion" id="faqPage">
              <?php foreach($faqs as $i => $f): $aid = 'f'.$i; ?>
                <div class="accordion-item">
                  <h2 class="accordion-header" id="h<?= $aid ?>">
                    <button class="accordion-button <?= $i?'collapsed':'' ?>" type="button" data-bs-toggle="collapse" data-bs-target="#c<?= $aid ?>">
                      <?= esc($f['question']) ?>
                    </button>
                  </h2>
                  <div id="c<?= $aid ?>" class="accordion-collapse collapse <?= $i? '':'show' ?>" data-bs-parent="#faqPage">
                    <div class="accordion-body small"><?= esc($f['answer']) ?></div>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
          </div>
        </div>
        <?php endif; ?>
      </div>
    </div>

  </div>
</section>
<?= $this->endSection() ?>
