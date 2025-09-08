<?= $this->extend('admin/layouts/main') ?>
<?= $this->section('content') ?>
<h4 class="mb-3"><?= esc($title) ?></h4>

<form method="post" action="<?= site_url('admin/announcements/'.($row?'update/'.$row['id']:'store')) ?>">
  <?= csrf_field() ?>
  <div class="row g-3">
    <div class="col-lg-8">
      <div class="card">
        <div class="card-body">
          <div class="mb-3">
            <label class="form-label">Teks</label>
            <input name="text" class="form-control" required value="<?= esc($row['text'] ?? '') ?>">
          </div>
          <div class="mb-3">
            <label class="form-label">URL (opsional)</label>
            <input name="url" class="form-control" value="<?= esc($row['url'] ?? '') ?>">
          </div>
        </div>
      </div>
    </div>
    <div class="col-lg-4">
      <div class="card">
        <div class="card-body">
          <div class="mb-3"><label class="form-label">Priority</label>
            <input type="number" name="priority" class="form-control" value="<?= esc($row['priority'] ?? 0) ?>">
          </div>
          <div class="mb-3"><label class="form-label">Mulai</label>
            <input type="datetime-local" name="start_at" class="form-control" value="<?= isset($row['start_at']) && $row['start_at'] ? date('Y-m-d\TH:i', strtotime($row['start_at'])) : '' ?>">
          </div>
          <div class="mb-3"><label class="form-label">Selesai</label>
            <input type="datetime-local" name="end_at" class="form-control" value="<?= isset($row['end_at']) && $row['end_at'] ? date('Y-m-d\TH:i', strtotime($row['end_at'])) : '' ?>">
          </div>
          <div class="form-check form-switch mb-3">
            <input class="form-check-input" type="checkbox" name="is_active" value="1" <?= !empty($row) && $row['is_active'] ? 'checked':'' ?>>
            <label class="form-check-label">Aktif</label>
          </div>
          <button class="btn btn-primary"><?= $row?'Simpan Perubahan':'Simpan' ?></button>
          <a href="<?= site_url('admin/announcements') ?>" class="btn btn-outline-secondary">Kembali</a>
        </div>
      </div>
    </div>
  </div>
</form>
<?= $this->endSection() ?>
