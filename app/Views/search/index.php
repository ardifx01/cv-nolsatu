<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>Pencarian<?= $this->endSection() ?>

<?= $this->section('content') ?>
<section class="py-5">
  <div class="container">
    <h1 class="h3 mb-3">Hasil Pencarian</h1>

    <form class="row g-2 mb-3" method="get" action="<?= site_url('search') ?>">
      <div class="col-md-8">
        <input type="search" name="q" class="form-control" placeholder="Ketik kata kunci…" value="<?= esc($q) ?>" autofocus>
      </div>
      <div class="col-md-4 d-grid d-md-flex gap-2">
        <select class="form-select" name="type">
          <option value="all"      <?= $type==='all'?'selected':'' ?>>Semua</option>
          <option value="service"  <?= $type==='service'?'selected':'' ?>>Layanan</option>
          <option value="post"     <?= $type==='post'?'selected':'' ?>>Berita/Siaran Pers/Tips</option>
          <option value="faq"      <?= $type==='faq'?'selected':'' ?>>FAQ</option>
          <option value="document" <?= $type==='document'?'selected':'' ?>>Dokumen</option>
        </select>
        <button class="btn btn-primary" type="submit"><i class="bi bi-search me-1"></i>Cari</button>
      </div>
    </form>

    <?php if($q===''): ?>
      <div class="alert alert-info">Masukkan kata kunci untuk mencari layanan, berita, FAQ, atau dokumen.</div>
    <?php else: ?>
      <div class="small text-secondary mb-3">
        Ditemukan <strong><?= count($results) ?></strong> hasil untuk: <em><?= esc($q) ?></em>
        <?php if($type!=='all'): ?> (filter: <?= esc($type) ?>)<?php endif; ?>
      </div>

      <?php if(empty($results)): ?>
        <div class="alert alert-warning">Tidak ada hasil. Coba variasi kata atau pilih kategori lain.</div>
      <?php else: ?>
        <div class="list-group">
          <?php foreach($results as $r): ?>
            <a href="<?= esc($r['url_abs']) ?>" class="list-group-item list-group-item-action py-3">
              <div class="d-flex w-100 justify-content-between">
                <h5 class="mb-1"><?= $r['title_h'] ?></h5>
                <small class="text-secondary text-uppercase">
                  <?php if($r['source']==='post'): ?>BERITA<?php elseif($r['source']==='service'): ?>LAYANAN<?php elseif($r['source']==='faq'): ?>FAQ<?php else: ?>DOKUMEN<?php endif; ?>
                </small>
              </div>
              <?php if(!empty($r['snippet_h'])): ?>
                <p class="mb-1 small text-secondary"><?= $r['snippet_h'] ?></p>
              <?php endif; ?>
              <div class="d-flex justify-content-between small">
                <span class="text-secondary"><?= esc($r['url']) ?></span>
                <?php if(!empty($r['published_at'])): ?>
                  <span class="text-secondary"><?= date('d M Y', strtotime($r['published_at'])) ?></span>
                <?php endif; ?>
              </div>
            </a>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    <?php endif; ?>
  </div>
</section>
<?= $this->endSection() ?>
