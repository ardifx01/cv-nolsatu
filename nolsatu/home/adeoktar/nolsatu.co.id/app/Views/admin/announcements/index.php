<?= $this->extend('admin/layouts/main') ?>
<?= $this->section('title') ?>Announcements<?= $this->endSection() ?>

<?= $this->section('styles') ?>
<meta name="csrf-token" content="<?= csrf_hash() ?>">
<style>
  .badge-on  { background: var(--bs-success-bg-subtle); color: var(--bs-success-text-emphasis); }
  .badge-off { background: var(--bs-secondary-bg); color: var(--bs-secondary-color); }
  .w-80px{ width:80px; } .w-120px{ width:120px; } .w-150px{ width:150px; }
</style>
<?= $this->endSection() ?>

<?= $this->section('toolbar') ?>
<button class="btn btn-primary btn-sm" id="btnAdd">
  <i class="bi bi-plus-lg me-1"></i> Tambah
</button>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="card card-soft">
  <div class="card-body">
    <div class="table-responsive">
      <table id="tblAnn" class="table table-striped align-middle w-100">
        <thead>
          <tr>
            <th class="w-80px">#</th>
            <th>Teks</th>
            <th class="w-150px">URL</th>
            <th class="w-120px">Mulai</th>
            <th class="w-120px">Selesai</th>
            <th class="w-80px">Prioritas</th>
            <th class="w-80px">Aktif</th>
            <th class="w-150px">Aksi</th>
          </tr>
        </thead>
        <tbody></tbody>
      </table>
    </div>
  </div>
</div>

<!-- Modal Add/Edit -->
<div class="modal fade" id="modalAnn" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <form class="modal-content" id="frmAnn">
      <?= csrf_field() ?>
      <input type="hidden" name="id" id="a_id">
      <div class="modal-header">
        <h5 class="modal-title" id="modalTitle">Tambah Pengumuman</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
      </div>
      <div class="modal-body">
        <div class="row g-3">
          <div class="col-12">
            <label class="form-label">Teks Pengumuman</label>
            <textarea class="form-control" name="text" id="a_text" rows="2" maxlength="500" required></textarea>
            <div class="form-text">Maks 500 karakter. HTML tidak disarankan.</div>
          </div>
          <div class="col-md-6">
            <label class="form-label">URL (opsional)</label>
            <input type="url" class="form-control" name="url" id="a_url" placeholder="https://...">
          </div>
          <div class="col-md-3">
            <label class="form-label">Prioritas</label>
            <input type="number" class="form-control" name="priority" id="a_priority" value="0" step="1">
            <div class="form-text">Semakin besar semakin di depan.</div>
          </div>
          <div class="col-md-3">
            <label class="form-label d-block">Aktif?</label>
            <div class="form-check form-switch">
              <input class="form-check-input" type="checkbox" role="switch" id="a_active" name="is_active" checked>
              <label class="form-check-label" for="a_active">Tampilkan</label>
            </div>
          </div>
          <div class="col-md-6">
            <label class="form-label">Mulai Tayang</label>
            <input type="datetime-local" class="form-control" name="start_at" id="a_start">
            <div class="form-text">Kosongkan = mulai sekarang.</div>
          </div>
          <div class="col-md-6">
            <label class="form-label">Akhir Tayang</label>
            <input type="datetime-local" class="form-control" name="end_at" id="a_end">
            <div class="form-text">Kosongkan = tanpa tanggal berakhir.</div>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-secondary" type="button" data-bs-dismiss="modal">Batal</button>
        <button class="btn btn-primary" id="btnSave" type="submit">
          <span class="txt">Simpan</span>
          <span class="spinner-border spinner-border-sm d-none ms-2" role="status"></span>
        </button>
      </div>
    </form>
  </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
  // === CSRF helpers ===
  const CSRF_META = document.querySelector('meta[name="csrf-token"]');
  const setToken  = (t)=>{ CSRF_META?.setAttribute('content', t); };
  const getToken  = ()=> CSRF_META?.getAttribute('content') || '';

  // === Refs ===
  const modalEl = document.getElementById('modalAnn');
  const modal   = new bootstrap.Modal(modalEl);
  const form    = document.getElementById('frmAnn');
  const btnAdd  = document.getElementById('btnAdd');
  const btnSave = document.getElementById('btnSave');

  // === DataTable (DataTables v2, tanpa jQuery) ===
  const tbl = new DataTable('#tblAnn', {
    ajax: {
      url: '<?= site_url('admin/announcements/list') ?>',
      dataSrc: (json)=>{ if (json.token) setToken(json.token); return json.data || []; }
    },
    order: [[5,'desc']], // priority desc by default
    columns: [
      { data: 'id' },
      { data: 'text', render: d => `<div class="fw-semibold">${escapeHtml(d||'')}</div>` },
      { data: 'url', render: d => d ? `<a href="${escapeAttr(d)}" target="_blank" rel="noopener">Buka</a>` : '<span class="text-secondary small">—</span>' },
      { data: 'start_at', render: d => d ? `<span class="small">${escapeHtml(d)}</span>` : '<span class="text-secondary small">—</span>' },
      { data: 'end_at',   render: d => d ? `<span class="small">${escapeHtml(d)}</span>` : '<span class="text-secondary small">—</span>' },
      { data: 'priority' },
      { data: 'is_active', render: v => Number(v) ? '<span class="badge badge-on">Aktif</span>' : '<span class="badge badge-off">Nonaktif</span>' },
      { data: null, orderable:false, render: (row)=>{
          return `<div class="btn-group btn-group-sm" role="group">
              <button class="btn btn-outline-primary" onclick='onEdit(${JSON.stringify(row)})'><i class="bi bi-pencil"></i></button>
              <button class="btn btn-outline-${Number(row.is_active) ? 'warning':'success'}" onclick="onToggle(${row.id})">
                <i class="bi bi-${Number(row.is_active) ? 'pause':'play'}-circle"></i>
              </button>
              <button class="btn btn-outline-danger" onclick="onDelete(${row.id})"><i class="bi bi-trash"></i></button>
          </div>`;
        }}
    ]
  });

  function escapeHtml(s){ return (s??'').toString().replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m])); }
  function escapeAttr(s){ return escapeHtml(s).replace(/"/g,'&quot;'); }

  function toLocalInputValue(iso){
    // 'YYYY-mm-dd HH:ii:ss' -> input datetime-local
    const d = new Date((iso||'').replace(' ','T'));
    if (isNaN(d.getTime())) return '';
    const p = n => n<10 ? '0'+n : n;
    return `${d.getFullYear()}-${p(d.getMonth()+1)}-${p(d.getDate())}T${p(d.getHours())}:${p(d.getMinutes())}`;
  }

  // === Add ===
  btnAdd?.addEventListener('click', ()=>{
    form.reset();
    form.a_id.value = '';
    form.a_priority.value = 0;
    form.a_active.checked = true;
    document.getElementById('modalTitle').textContent = 'Tambah Pengumuman';
    modal.show();
  });

  // === Edit (pakai data row dari DataTables) ===
  window.onEdit = (row)=>{
    form.reset();
    form.a_id.value       = row.id;
    form.a_text.value     = row.text || '';
    form.a_url.value      = row.url || '';
    form.a_priority.value = row.priority ?? 0;
    form.a_active.checked = !!Number(row.is_active);
    form.a_start.value    = row.start_at ? toLocalInputValue(row.start_at) : '';
    form.a_end.value      = row.end_at   ? toLocalInputValue(row.end_at)   : '';
    document.getElementById('modalTitle').textContent = 'Edit Pengumuman';
    modal.show();
  };

  // === Toggle aktif ===
  window.onToggle = (id)=>{
    Swal.fire({ title:'Ubah Status?', icon:'question', showCancelButton:true, confirmButtonText:'Ya' })
      .then(res=>{
        if(!res.isConfirmed) return;
        fetch('<?= site_url('admin/announcements/toggle') ?>/'+id, {
          method:'POST', headers:{'X-CSRF-TOKEN': getToken()}
        }).then(r=>r.json()).then(j=>{
          if (j.token) setToken(j.token);
          if (j.status){
            Swal.fire('Berhasil', j.message || 'Status diperbarui.', 'success');
            tbl.ajax.reload(null,false);
          } else {
            Swal.fire('Gagal', j.message || 'Terjadi kesalahan.', 'error');
          }
        }).catch(()=> Swal.fire('Error','Tidak dapat menghubungi server.','error'));
      });
  };

  // === Delete ===
  window.onDelete = (id)=>{
    Swal.fire({ title:'Hapus Pengumuman?', text:'Tindakan ini tidak bisa dibatalkan.', icon:'warning', showCancelButton:true, confirmButtonText:'Ya, hapus' })
      .then(res=>{
        if(!res.isConfirmed) return;
        fetch('<?= site_url('admin/announcements/delete') ?>/'+id, {
          method:'POST', headers:{'X-CSRF-TOKEN': getToken()}
        }).then(r=>r.json()).then(j=>{
          if (j.token) setToken(j.token);
          if (j.status){
            Swal.fire('Berhasil', j.message || 'Data dihapus.', 'success');
            tbl.ajax.reload(null,false);
          } else {
            Swal.fire('Gagal', j.message || 'Terjadi kesalahan.', 'error');
          }
        }).catch(()=> Swal.fire('Error','Tidak dapat menghubungi server.','error'));
      });
  };

  // === Submit Add/Edit ===
  form?.addEventListener('submit', (e)=>{
    e.preventDefault();
    const data = new FormData(form);

    btnSave.disabled = true;
    btnSave.querySelector('.spinner-border').classList.remove('d-none');
    btnSave.querySelector('.txt').textContent = 'Menyimpan...';

    fetch('<?= site_url('admin/announcements/save') ?>', {
      method: 'POST',
      headers: {'X-CSRF-TOKEN': getToken()},
      body: data
    }).then(r=>r.json()).then(j=>{
      if (j.token) setToken(j.token);
      if (j.status){
        modal.hide();
        Swal.fire('Berhasil', j.message || 'Tersimpan.', 'success');
        tbl.ajax.reload(null,false);
      } else {
        Swal.fire('Gagal', j.message || 'Validasi gagal.', 'error');
      }
    }).catch(()=>{
      Swal.fire('Error','Tidak dapat menghubungi server.','error');
    }).finally(()=>{
      btnSave.disabled = false;
      btnSave.querySelector('.spinner-border').classList.add('d-none');
      btnSave.querySelector('.txt').textContent = 'Simpan';
    });
  });
</script>
<?= $this->endSection() ?>
