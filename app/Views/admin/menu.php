<?= $this->extend('admin/layouts/main') ?>
<?= $this->section('title') ?>Menu Items<?= $this->endSection() ?>

<?= $this->section('styles') ?>
<meta name="csrf-token" content="<?= csrf_hash() ?>">
<style>
  .w-80px{width:80px}.w-120px{width:120px}.w-150px{width:150px}
  .badge-on  { background: var(--bs-success-bg-subtle); color: var(--bs-success-text-emphasis); }
  .badge-off { background: var(--bs-secondary-bg); color: var(--bs-secondary-color); }
</style>
<?= $this->endSection() ?>

<?= $this->section('toolbar') ?>
<button class="btn btn-primary btn-sm" id="btnAdd"><i class="bi bi-plus-lg me-1"></i> Tambah</button>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="card card-soft">
  <div class="card-body">
    <div class="table-responsive">
      <table id="tblMenu" class="table table-striped align-middle w-100">
        <thead>
          <tr>
            <th class="w-80px">#</th>
            <th>Judul</th>
            <th>Parent</th>
            <th class="w-150px">Icon</th>
            <th>URL</th>
            <th class="w-120px">Ext/Blank</th>
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
<div class="modal fade" id="modalMenu" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <form class="modal-content" id="frmMenu">
      <?= csrf_field() ?>
      <input type="hidden" name="id" id="m_id">
      <div class="modal-header">
        <h5 class="modal-title">Tambah Menu</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
      </div>
      <div class="modal-body">
        <div class="row g-3">
          <div class="col-lg-6">
            <label class="form-label">Judul</label>
            <input type="text" class="form-control" name="title" id="m_title" required>
          </div>
          <div class="col-lg-6">
            <label class="form-label">Parent</label>
            <select class="form-select" name="parent_id" id="m_parent">
              <option value="">— Top Level —</option>
            </select>
            <div class="form-text">Biarkan kosong untuk menu tingkat atas.</div>
          </div>

          <div class="col-lg-6">
            <label class="form-label">Icon (Bootstrap Icons)</label>
            <input type="text" class="form-control" name="icon" id="m_icon" placeholder="contoh: bi-people">
            <div class="form-text">
              <a href="https://icons.getbootstrap.com/" target="_blank" rel="noopener">Lihat daftar ikon</a>
            </div>
          </div>
          <div class="col-lg-6">
            <label class="form-label">URL</label>
            <input type="text" class="form-control" name="url" id="m_url" placeholder="/wna/permohonan-visa">
            <div class="form-text">Kosongkan untuk judul saja (hanya sebagai induk).</div>
          </div>

          <div class="col-md-3">
            <label class="form-label">Sort</label>
            <input type="number" class="form-control" name="sort" id="m_sort" value="0" min="0" step="1">
          </div>

          <div class="col-md-3">
            <label class="form-label d-block">Aktif?</label>
            <div class="form-check form-switch">
              <input class="form-check-input" type="checkbox" id="m_active" name="is_active" checked>
              <label class="form-check-label" for="m_active">Tampilkan</label>
            </div>
          </div>
          <div class="col-md-3">
            <label class="form-label d-block">External?</label>
            <div class="form-check form-switch">
              <input class="form-check-input" type="checkbox" id="m_external" name="is_external">
              <label class="form-check-label" for="m_external">Buka situs lain</label>
            </div>
          </div>
          <div class="col-md-3">
            <label class="form-label d-block">Target _blank?</label>
            <div class="form-check form-switch">
              <input class="form-check-input" type="checkbox" id="m_blank" name="target_blank">
              <label class="form-check-label" for="m_blank">Buka tab baru</label>
            </div>
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
  // CSRF helpers
  const CSRF_META = document.querySelector('meta[name="csrf-token"]');
  const setToken  = (t)=>{ CSRF_META?.setAttribute('content', t); };
  const getToken  = ()=> CSRF_META?.getAttribute('content') || '';

  const modalEl = document.getElementById('modalMenu');
  const modal   = new bootstrap.Modal(modalEl);
  const form    = document.getElementById('frmMenu');
  const btnAdd  = document.getElementById('btnAdd');
  const btnSave = document.getElementById('btnSave');

  // Load parent options
  async function loadParents(selectedId = '') {
    const r = await fetch('<?= site_url('admin/menu/parents') ?>');
    const j = await r.json();
    if (j.token) setToken(j.token);
    const sel = document.getElementById('m_parent');
    sel.innerHTML = '<option value="">— Top Level —</option>';
    (j.data||[]).forEach(it=>{
      const opt = document.createElement('option');
      opt.value = it.id;
      opt.textContent = it.title;
      sel.appendChild(opt);
    });
    if (selectedId) sel.value = String(selectedId);
  }

  // DataTable
  const tbl = new DataTable('#tblMenu', {
    ajax: {
      url: '<?= site_url('admin/menu/list') ?>',
      dataSrc: (json)=>{ if (json.token) setToken(json.token); return json.data || []; }
    },
    order: [[6, 'asc']],
    columns: [
      { data: 'id' },
      { data: 'title', render: d => `<div class="fw-semibold">${escapeHtml(d||'')}</div>` },
      { data: 'parent_title', render: d => d ? escapeHtml(d) : '<span class="text-secondary">—</span>' },
      { data: 'icon', render: d => d ? `<i class="${escapeAttr(d)} me-1"></i><code>${escapeHtml(d)}</code>` : '<span class="text-secondary">—</span>' },
      { data: 'url', render: d => d ? `<code>${escapeHtml(d)}</code>` : '<span class="text-secondary">—</span>' },
      { data: null, render: row => {
          const ext = Number(row.is_external)===1 ? '<span class="badge bg-info-subtle text-info-emphasis">Ext</span>' : '';
          const blk = Number(row.target_blank)===1 ? '<span class="badge bg-warning-subtle text-warning-emphasis ms-1">_blank</span>' : '';
          return (ext || blk) ? (ext + blk) : '<span class="text-secondary">—</span>';
        }, className:'text-nowrap' },
      { data: 'sort' },
      { data: 'is_active', render: v => Number(v)===1 ? '<span class="badge badge-on">Aktif</span>' : '<span class="badge badge-off">Nonaktif</span>' },
      { data: null, orderable:false, render: row => {
          const on = Number(row.is_active)===1;
          return `<div class="btn-group btn-group-sm" role="group">
            <button class="btn btn-outline-primary" onclick='onEdit(${JSON.stringify(row)})'><i class="bi bi-pencil"></i></button>
            <button class="btn btn-outline-${on?'warning':'success'}" onclick="onToggle(${row.id})">
              <i class="bi bi-${on?'pause':'play'}-circle"></i>
            </button>
            <button class="btn btn-outline-danger" onclick="onDelete(${row.id})"><i class="bi bi-trash"></i></button>
          </div>`;
        }, className:'text-nowrap' }
    ]
  });

  function escapeHtml(s){ return (s??'').toString().replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m])); }
  function escapeAttr(s){ return (s??'').toString().replace(/"/g,'&quot;'); }

  // Tambah
  btnAdd?.addEventListener('click', async ()=>{
    form.reset();
    form.m_id.value = '';
    document.querySelector('#modalMenu .modal-title').textContent = 'Tambah Menu';
    await loadParents('');
    modal.show();
  });

  // Edit
  window.onEdit = async (row)=>{
    form.reset();
    form.m_id.value      = row.id;
    form.m_title.value   = row.title || '';
    form.m_icon.value    = row.icon || '';
    form.m_url.value     = row.url  || '';
    form.m_sort.value    = row.sort ?? 0;
    form.m_active.checked   = Number(row.is_active)===1;
    form.m_external.checked = Number(row.is_external)===1;
    form.m_blank.checked    = Number(row.target_blank)===1;
    document.querySelector('#modalMenu .modal-title').textContent = 'Edit Menu';
    await loadParents(row.parent_id || '');
    modal.show();
  };

  // Toggle aktif
  window.onToggle = (id)=>{
    Swal.fire({ title:'Ubah Status?', icon:'question', showCancelButton:true, confirmButtonText:'Ya' })
    .then(res=>{
      if(!res.isConfirmed) return;
      fetch('<?= site_url('admin/menu/toggle') ?>/'+id, {
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
    Swal.fire({ title:'Hapus Menu?', text:'Anak (sub-menu) juga akan ikut terhapus.', icon:'warning', showCancelButton:true, confirmButtonText:'Ya, hapus' })
    .then(res=>{
      if(!res.isConfirmed) return;
      fetch('<?= site_url('admin/menu/delete') ?>/'+id, {
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

  // Submit (Tambah/Edit)
  form?.addEventListener('submit', (e)=>{
    e.preventDefault();
    const data = new FormData(form);
    // pastikan checkbox terkirim 1/0
    data.set('is_active',   form.m_active.checked   ? '1' : '0');
    data.set('is_external', form.m_external.checked ? '1' : '0');
    data.set('target_blank',form.m_blank.checked    ? '1' : '0');

    btnSave.disabled = true;
    btnSave.querySelector('.spinner-border').classList.remove('d-none');
    btnSave.querySelector('.txt').textContent = 'Menyimpan...';

    fetch('<?= site_url('admin/menu/save') ?>', {
      method: 'POST',
      headers: { 'X-CSRF-TOKEN': getToken() },
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
