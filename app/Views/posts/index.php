<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>Berita<?= $this->endSection() ?>

<?= $this->section('content') ?>
<section class="py-5" id="berita">
  <div class="container">
    <div class="d-flex justify-content-between align-items-end mb-3">
      <h2 class="section-title">Berita & Siaran Pers</h2>
      <div class="btn-group btn-group-sm" role="group" aria-label="Filter">
        <a href="<?= site_url('berita') ?>" class="btn btn-outline-primary <?= $activeType==='all'?'active':'' ?>">Semua</a>
        <a href="<?= site_url('berita?type=press') ?>" class="btn btn-outline-primary <?= $activeType==='press'?'active':'' ?>">Siaran Pers</a>
        <a href="<?= site_url('berita?type=news') ?>"  class="btn btn-outline-primary <?= $activeType==='news'?'active':'' ?>">Berita</a>
        <a href="<?= site_url('berita?type=tips') ?>"  class="btn btn-outline-primary <?= $activeType==='tips'?'active':'' ?>">Tips</a>
      </div>
    </div>

    <div class="row g-4">
      <?php if(empty($posts)): ?>
        <div class="col-12">
          <div class="alert alert-info">Belum ada artikel.</div>
        </div>
      <?php endif; ?>

      <?php foreach ($posts as $p): 
        $badgeClass = ['news'=>'text-bg-primary','press'=>'text-bg-success','tips'=>'text-bg-warning'][$p['type']] ?? 'text-bg-secondary';
        $thumb = $p['thumbnail'] ?: 'https://picsum.photos/800/500?blur=1';
      ?>
      <div class="col-md-6 col-lg-4">
        <article class="card h-100 shadow-soft rounded-2xl">
          <img src="<?= esc($thumb) ?>" class="card-img-top" alt="<?= esc($p['title']) ?>" loading="lazy" decoding="async">
          <div class="card-body">
            <span class="badge <?= $badgeClass ?> mb-2"><?= strtoupper(esc($p['type'])) ?></span>
            <h5 class="card-title"><?= esc($p['title']) ?></h5>
            <?php if(!empty($p['excerpt'])): ?>
              <p class="card-text small text-secondary"><?= esc($p['excerpt']) ?></p>
            <?php endif; ?>
            <div class="small text-secondary mb-2"><?= date('d M Y', strtotime($p['published_at'] ?? 'now')) ?></div>
            <a class="stretched-link" href="<?= site_url('berita/'.urlencode($p['slug'])) ?>">Baca Selengkapnya</a>
          </div>
        </article>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?= $this->endSection() ?>
