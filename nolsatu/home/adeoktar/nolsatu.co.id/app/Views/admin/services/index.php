<?= $this->extend('admin/layouts/main') ?>
<?= $this->section('title') ?>Services<?= $this->endSection() ?>

<?= $this->section('styles') ?>
<!-- DataTables Bootstrap 5 -->
<link href="https://cdn.datatables.net/v/bs5/dt-2.0.8/datatables.min.css" rel="stylesheet">
<!-- SweetAlert2 -->
<link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">

<meta name="csrf-token" content="<?= csrf_hash() ?>">
<style>
  .badge-publish{ background: var(--bs-success-bg-subtle); color: var(--bs-success-text); }
  .badge-draft{ background: var(--bs-secondary-bg); color: var(--bs-secondary-text); }
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
      <table id="tblServices" class="table table-striped align-middle w-100">
        <thead>
          <tr>
            <th width="48">#</th>
            <th>Judul</th>
            <th>Slug</th>
            <th>Ikon</th>
            <th>URL</th>
            <th width="70">Sort</th>
            <th width="90">Status</th>
            <th width="130">Aksi</th>
          </tr>
        </thead>
        <tbody></tbody>
      </table>
    </div>
  </div>
</div>

<!-- Modal Add/Edit -->
<div class="modal fade" id="modalForm" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <form class="modal-content" id="frmService">
      <?= csrf_field() ?>
      <input type="hidden" name="id" id="f_id">
      <div class="modal-header">
        <h5 class="modal-title" id="modalTitle">Tambah Service</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
      </div>
      <div class="modal-body">
        <div class="row g-3">
          <div class="col-md-8">
            <label class="form-label">Judul</label>
            <input type="text" class="form-control" name="title" id="f_title" required>
          </div>
          <div class="col-md-4">
            <label class="form-label">Slug</label>
            <input type="text" class="form-control" name="slug" id="f_slug" placeholder="opsional (auto dari judul)">
          </div>
          <div class="col-md-6">
            <label class="form-label">Ikon (class)</label>
            <input type="text" class="form-control" name="icon" id="f_icon" placeholder="contoh: bi-passport atau fa-solid fa-passport">
            <div class="form-text">Gunakan kelas Bootstrap Icons / Font Awesome.</div>
          </div>
          <div class="col-md-6">
            <label class="form-label">URL</label>
            <input type="url" class="form-control" name="url" id="f_url" placeholder="https://... atau /path" required>
          </div>
          <div class="col-md-12">
            <label class="form-label">Deskripsi</label>
            <textarea class="form-control" rows="3" name="description" id="f_desc"></textarea>
          </div>
          <div class="col-md-3">
            <label class="form-label">Sort</label>
            <input type="number" class="form-control" name="sort" id="f_sort" value="0">
          </div>
          <div class="col-md-3 d-flex align-items-end">
            <div class="form-check">
              <input class="form-check-input" type="checkbox" value="1" id="f_active" name="is_active" checked>
              <label class="form-check-label" for="f_active">Aktif</label>
            </div>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-secondary" type="button" data-bs-dismiss="modal">Batal</button>
        <button class="btn btn-primary" id="btnSave" type="submit">
          <span class="txt">Simpan</span>
          <span class="spinner-border spinner-border-sm d-none ms-2" role="status" aria-hidden="true"></span>
        </button>
      </div>
    </form>
  </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="https://cdn.datatables.net/v/bs5/dt-2.0.8/datatables.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
  const CSRF_META = document.querySelector('meta[name="csrf-token"]');
  const setToken  = (t)=>{ if(CSRF_META) CSRF_META.setAttribute('content', t); };
  const getToken  = ()=> CSRF_META?.getAttribute('content') || '';

  const modalEl   = document.getElementById('modalForm');
  const modal     = new bootstrap.Modal(modalEl);
  const form      = document.getElementById('frmService');
  const btnAdd    = document.getElementById('btnAdd');
  const btnSave   = document.getElementById('btnSave');
  const tbl       = new DataTable('#tblServices', {
    ajax: {
      url: '<?= site_url('admin/services/list') ?>',
      dataSrc: (json)=>{
        if (json.token) setToken(json.token);
        return json.data || [];
      }
    },
    order: [[5,'asc']],
    columns: [
      { data: 'id' },
      { data: 'title',
        render: (d, t, row)=> `<div class="fw-semibold">${escapeHtml(d)}</div>
                               <div class="small text-secondary">${escapeHtml(row.description || '')}</div>` },
      { data: 'slug', render: (d)=> `<code>${escapeHtml(d)}</code>` },
      { data: 'icon', render: (d)=> d ? `<i class="${escapeHtml(d)}"></i> <span class="small text-secondary ms-1">${escapeHtml(d)}</span>` : '-' },
      { data: 'url',  render: (d)=> d ? `<a href="${escapeAttr(d)}" target="_blank" rel="noopener" class="text-decoration-none">${escapeHtml(d)}</a>` : '-' },
      { data: 'sort' },
      { data: 'is_active',
        render: (d)=> d==1 ? '<span class="badge bg-success-subtle text-success-emphasis">Aktif</span>'
                           : '<span class="badge bg-secondary">Nonaktif</span>' },
      { data: null, orderable:false, render: (row)=> {
          return `
            <div class="btn-group btn-group-sm" role="group">
              <button class="btn btn-outline-primary" onclick='onEdit(${JSON.stringify(row)})'><i class="bi bi-pencil"></i></button>
              <button class="btn btn-outline-${row.is_active==1?'warning':'success'}" onclick="onToggle(${row.id})">
                <i class="bi bi-${row.is_active==1?'pause':'play'}-circle"></i>
              </button>
              <button class="btn btn-outline-danger" onclick="onDelete(${row.id})"><i class="bi bi-trash"></i></button>
            </div>`;
        }}
    ]
  });

  function escapeHtml(s){ return (s??'').toString().replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m])); }
  function escapeAttr(s){ return escapeHtml(s).replace(/"/g,'&quot;'); }

  // Add
  btnAdd?.addEventListener('click', ()=>{
    form.reset();
    form.f_id.value = '';
    form.f_title.focus();
    document.getElementById('modalTitle').textContent = 'Tambah Service';
    modal.show();
  });

  // Edit (prefill dari row)
  window.onEdit = (row)=>{
    form.reset();
    form.f_id.value    = row.id;
    form.f_title.value = row.title || '';
    form.f_slug.value  = row.slug || '';
    form.f_icon.value  = row.icon || '';
    form.f_url.value   = row.url  || '';
    form.f_desc.value  = row.description || '';
    form.f_sort.value  = row.sort || 0;
    form.f_active.checked = row.is_active == 1;
    document.getElementById('modalTitle').textContent = 'Edit Service';
    modal.show();
  };

  // Toggle aktif
  window.onToggle = (id)=>{
    Swal.fire({
      title: 'Ubah Status?',
      text: 'Mengaktifkan/nonaktifkan service ini.',
      icon: 'question', showCancelButton: true, confirmButtonText: 'Ya, ubah',
      cancelButtonText: 'Batal'
    }).then(res=>{
      if(!res.isConfirmed) return;
      fetch('<?= site_url('admin/services/toggle') ?>/'+id, {
        method: 'POST',
        headers: {'X-CSRF-TOKEN': getToken()},
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

  // Delete
  window.onDelete = (id)=>{
    Swal.fire({
      title: 'Hapus Service?',
      text: 'Tindakan ini tidak bisa dibatalkan.',
      icon: 'warning', showCancelButton: true, confirmButtonText: 'Ya, hapus',
      cancelButtonText: 'Batal'
    }).then(res=>{
      if(!res.isConfirmed) return;
      fetch('<?= site_url('admin/services/delete') ?>/'+id, {
        method: 'POST',
        headers: {'X-CSRF-TOKEN': getToken()},
      }).then(r=>r.json()).then(j=>{
        if (j.token) setToken(j.token);
        if (j.status){
          Swal.fire('Berhasil', j.message || 'Data dihapus.', 'success');
          tbl.ajax.reload(null,false);
        } else {
          Swal.fire('Gagal', j.message || 'Terjadi kesalahan.','error');
        }
      }).catch(()=> Swal.fire('Error','Tidak dapat menghubungi server.','error'));
    });
  };

  // Submit Add/Edit
  form?.addEventListener('submit', (e)=>{
    e.preventDefault();
    const id   = form.f_id.value.trim();
    const data = new FormData(form);
    // checkbox
    data.set('is_active', form.f_active.checked ? '1' : '0');

    // state loading
    btnSave.disabled = true;
    btnSave.querySelector('.spinner-border').classList.remove('d-none');
    btnSave.querySelector('.txt').textContent = 'Menyimpan...';

    const url = id
      ? '<?= site_url('admin/services/update') ?>/'+id
      : '<?= site_url('admin/services/store') ?>';

    fetch(url, {
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
