<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>
  <?= esc($post['title']) ?>
<?= $this->endSection() ?>

<?= $this->section('meta_desc') ?>
  <?= esc($post['excerpt'] ?: mb_substr(strip_tags($post['content']), 0, 160)) ?>
<?= $this->endSection() ?>

<?php
  // pilih og:image: pakai thumbnail kalau ada, kalau tidak biarkan kosong (layout akan fallback)
  $thumb = !empty($post['thumbnail']) ? $post['thumbnail'] : '';
?>
<?= $this->section('meta_image') ?>
  <?= esc($thumb) ?>
<?= $this->endSection() ?>

<?= $this->section('og_type') ?>article<?= $this->endSection() ?>



<?= $this->section('content') ?>
<section class="py-5">
  <div class="container">
    <nav aria-label="breadcrumb" class="mb-3">
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?= site_url('/') ?>">Beranda</a></li>
        <li class="breadcrumb-item"><a href="<?= site_url('berita') ?>">Berita</a></li>
        <li class="breadcrumb-item active" aria-current="page"><?= esc($post['title']) ?></li>
      </ol>
    </nav>

    <div class="row g-4">
      <div class="col-lg-8">
        <article class="card shadow-soft rounded-2xl">
          <?php if(!empty($post['thumbnail'])): ?>
            <img src="<?= esc($post['thumbnail']) ?>" class="card-img-top" alt="<?= esc($post['title']) ?>">
          <?php endif; ?>
          <div class="card-body">
            <div class="d-flex align-items-center gap-2 mb-2">
              <?php $badgeClass = ['news'=>'text-bg-primary','press'=>'text-bg-success','tips'=>'text-bg-warning'][$post['type']] ?? 'text-bg-secondary'; ?>
              <span class="badge <?= $badgeClass ?>"><?= strtoupper(esc($post['type'])) ?></span>
              <small class="text-secondary"><?= date('d M Y', strtotime($post['published_at'] ?? 'now')) ?></small>
            </div>
            <h1 class="h3 mb-3"><?= esc($post['title']) ?></h1>

            <div class="post-content">
              <?= $post['content'] ?>
              <?php // jika konten sudah bersih/terpercaya, bisa pakai echo $post['content']; ?>
            </div>
          </div>
        </article>
      </div>

      <div class="col-lg-4">
        <?php if(!empty($related)): ?>
        <div class="card shadow-soft rounded-2xl">
          <div class="card-body">
            <h6 class="mb-3">Artikel Lainnya</h6>
            <ul class="list-group list-group-flush">
              <?php foreach($related as $r): ?>
                <li class="list-group-item">
                  <a class="text-decoration-none" href="<?= site_url('berita/'.urlencode($r['slug'])) ?>">
                    <?= esc($r['title']) ?>
                  </a>
                  <div class="small text-secondary"><?= date('d M Y', strtotime($r['published_at'] ?? 'now')) ?></div>
                </li>
              <?php endforeach; ?>
            </ul>
          </div>
        </div>
        <?php endif; ?>
      </div>
    </div>

  </div>
</section>
<?= $this->endSection() ?>
