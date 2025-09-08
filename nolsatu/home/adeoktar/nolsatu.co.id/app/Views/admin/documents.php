<?= $this->extend('admin/layouts/main') ?>
<?= $this->section('title') ?>Documents<?= $this->endSection() ?>

<?= $this->section('styles') ?>

<meta name="csrf-token" content="<?= csrf_hash() ?>">
<style>
  .badge-on  { background: var(--bs-success-bg-subtle); color: var(--bs-success-text-emphasis); }
  .badge-off { background: var(--bs-secondary-bg); color: var(--bs-secondary-color); }
  .w-80px{ width:80px; } .w-150px{ width:150px; }
</style>
<?= $this->endSection() ?>

<?= $this->section('toolbar') ?>
<button class="btn btn-primary btn-sm" id="btnAdd"><i class="bi bi-plus-lg me-1"></i> Tambah</button>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="card card-soft">
  <div class="card-body">
    <div class="table-responsive">
      <table id="tblDocs" class="table table-striped align-middle w-100">
        <thead>
          <tr>
            <th class="w-80px">#</th>
            <th>Judul</th>
            <th class="w-150px">Tipe</th>
            <th>File</th>
            <th class="w-80px">Sort</th>
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
<div class="modal fade" id="modalDoc" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <form class="modal-content" id="frmDoc" enctype="multipart/form-data">
      <?= csrf_field() ?>
      <input type="hidden" name="id" id="d_id">
      <div class="modal-header">
        <h5 class="modal-title" id="modalTitle">Tambah Dokumen</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
      </div>
      <div class="modal-body">
        <div class="row g-3">
          <div class="col-12">
            <label class="form-label">Judul</label>
            <input type="text" class="form-control" name="title" id="d_title" required>
          </div>
          <div class="col-md-6">
            <label class="form-label">Tipe</label>
            <select class="form-select" name="type" id="d_type">
              <option value="form">Form</option>
              <option value="regulation">Regulation</option>
              <option value="guide">Guide</option>
              <option value="other" selected>Other</option>
            </select>
          </div>
          <div class="col-md-3">
            <label class="form-label">Sort</label>
            <input type="number" class="form-control" name="sort" id="d_sort" value="0" min="0" step="1">
          </div>
          <div class="col-md-3">
            <label class="form-label d-block">Aktif?</label>
            <div class="form-check form-switch">
              <input class="form-check-input" type="checkbox" role="switch" id="d_active" name="is_active" checked>
              <label class="form-check-label" for="d_active">Tampilkan</label>
            </div>
          </div>
          <div class="col-12">
            <label class="form-label">Deskripsi (opsional)</label>
            <textarea class="form-control" rows="4" name="description" id="d_desc" placeholder="Ringkasan singkat isi dokumen"></textarea>
          </div>
          <div class="col-12">
            <label class="form-label">File <?= '(<span class="text-secondary small">PDF/DOC/XLS/PPT/JPG/PNG/WEBP ≤ 15MB</span>)' ?></label>
            <input type="file" class="form-control" name="file" id="d_file" accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.jpg,.jpeg,.png,.webp">
            <div class="form-text" id="fileHelp">Saat edit, biarkan kosong jika tidak ingin mengganti file.</div>
            <div class="small mt-1" id="currentFile" style="display:none;"></div>
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
  const CSRF_META = document.querySelector('meta[name="csrf-token"]');
  const setToken  = (t)=>{ CSRF_META?.setAttribute('content', t); };
  const getToken  = ()=> CSRF_META?.getAttribute('content') || '';

  const modalEl = document.getElementById('modalDoc');
  const modal   = new bootstrap.Modal(modalEl);
  const form    = document.getElementById('frmDoc');
  const btnAdd  = document.getElementById('btnAdd');
  const btnSave = document.getElementById('btnSave');

  // DataTable
  const tbl = new DataTable('#tblDocs', {
    ajax: {
      url: '<?= site_url('admin/documents/list') ?>',
      dataSrc: (json)=>{ if (json.token) setToken(json.token); return json.data || []; }
    },
    order: [[4, 'asc']], // sort ASC
    columns: [
      { data: 'id' },
      { data: 'title', render: d => `<div class="fw-semibold">${escapeHtml(d||'')}</div>` },
      { data: 'type', render: d => badgeType(d) },
      { data: 'file_url', render: d => d ? `<a class="btn btn-sm btn-outline-primary" href="${escapeAttr(d)}" target="_blank" rel="noopener">Buka</a>` : '<span class="text-secondary small">—</span>' },
      { data: 'sort' },
      { 
  data: 'is_active',
  render: v => Number(v) === 1
    ? '<span class="badge badge-on">Aktif</span>'
    : '<span class="badge badge-off">Nonaktif</span>'
},
     { 
  data: null, orderable:false,
  render: (row)=>{
    const on = Number(row.is_active) === 1;
    return `<div class="btn-group btn-group-sm" role="group">
      <button class="btn btn-outline-primary" onclick='onEdit(${JSON.stringify(row)})'><i class="bi bi-pencil"></i></button>
      <button class="btn btn-outline-${on ? 'warning':'success'}" onclick="onToggle(${row.id})">
        <i class="bi bi-${on ? 'pause':'play'}-circle"></i>
      </button>
      <button class="btn btn-outline-danger" onclick="onDelete(${row.id})"><i class="bi bi-trash"></i></button>
    </div>`;
  }
}
    ]
  });

  function escapeHtml(s){ return (s??'').toString().replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m])); }
  function escapeAttr(s){ return (s??'').toString().replace(/"/g,'&quot;'); }
  function badgeType(t){
    const map = {form:'Form', regulation:'Regulation', guide:'Guide', other:'Other'};
    const label = map[t] || 'Other';
    return `<span class="badge bg-secondary-subtle text-secondary-emphasis">${label}</span>`;
  }

  // Tambah
  btnAdd?.addEventListener('click', ()=>{
    form.reset();
    form.d_id.value = '';
    document.getElementById('modalTitle').textContent = 'Tambah Dokumen';
    document.getElementById('currentFile').style.display = 'none';
    modal.show();
  });

  // Edit
  window.onEdit = (row)=>{
    form.reset();
    form.d_id.value    = row.id;
    form.d_title.value = row.title || '';
    form.d_type.value  = row.type || 'other';
    form.d_sort.value  = row.sort ?? 0;
    form.d_active.checked = !!row.is_active;
    form.d_desc.value  = row.description || '';
    document.getElementById('modalTitle').textContent = 'Edit Dokumen';
    const cf = document.getElementById('currentFile');
    if (row.file_url){
      cf.innerHTML = `File saat ini: <a href="${escapeAttr(row.file_url)}" target="_blank" rel="noopener">${escapeHtml(row.file_url)}</a>`;
      cf.style.display = '';
    } else {
      cf.style.display = 'none';
    }
    modal.show();
  };

  // Toggle aktif
  window.onToggle = (id)=>{
    Swal.fire({ title:'Ubah Status?', icon:'question', showCancelButton:true, confirmButtonText:'Ya' })
      .then(res=>{
        if(!res.isConfirmed) return;
        fetch('<?= site_url('admin/documents/toggle') ?>/'+id, {
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

  // Hapus
  window.onDelete = (id)=>{
    Swal.fire({ title:'Hapus Dokumen?', text:'Tindakan ini tidak bisa dibatalkan.', icon:'warning', showCancelButton:true, confirmButtonText:'Ya, hapus' })
      .then(res=>{
        if(!res.isConfirmed) return;
        fetch('<?= site_url('admin/documents/delete') ?>/'+id, {
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

  // Submit Tambah/Edit (satu endpoint)
  form?.addEventListener('submit', (e)=>{
    e.preventDefault();
    const data = new FormData(form);

    btnSave.disabled = true;
    btnSave.querySelector('.spinner-border').classList.remove('d-none');
    btnSave.querySelector('.txt').textContent = 'Menyimpan...';

    fetch('<?= site_url('admin/documents/save') ?>', {
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
      form.d_file.value = ''; // reset input file
    });
  });
</script>
<?= $this->endSection() ?>
