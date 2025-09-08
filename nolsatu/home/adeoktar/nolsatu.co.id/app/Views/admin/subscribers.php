<?= $this->extend('admin/layouts/main') ?>
<?= $this->section('title') ?>Subscribers<?= $this->endSection() ?>

<?= $this->section('styles') ?>
<meta name="csrf-token" content="<?= csrf_hash() ?>">
<style>
  .w-80px{width:80px}.w-120px{width:120px}.w-150px{width:150px}.mono{font-family:ui-monospace,Menlo,Consolas,monospace}
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
      <table id="tblSubs" class="table table-striped align-middle w-100">
        <thead>
          <tr>
            <th class="w-80px">#</th>
            <th>Email</th>
            <th class="w-120px">Verified</th>
            <th class="w-150px">Didaftarkan</th>
            <th class="w-150px">Aksi</th>
          </tr>
        </thead>
        <tbody></tbody>
      </table>
    </div>
  </div>
</div>

<!-- Modal Add/Edit -->
<div class="modal fade" id="modalSub" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <form class="modal-content" id="frmSub">
      <?= csrf_field() ?>
      <input type="hidden" name="id" id="s_id">
      <div class="modal-header">
        <h5 class="modal-title" id="modalTitle">Tambah Subscriber</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
          <label class="form-label">Email</label>
          <input type="email" class="form-control" name="email" id="s_email" required>
        </div>
        <div class="form-check form-switch">
          <input class="form-check-input" type="checkbox" role="switch" id="s_verified" name="verified">
          <label class="form-check-label" for="s_verified">Verified</label>
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

  const modalEl = document.getElementById('modalSub');
  const modal   = new bootstrap.Modal(modalEl);
  const form    = document.getElementById('frmSub');
  const btnAdd  = document.getElementById('btnAdd');
  const btnSave = document.getElementById('btnSave');

  // DataTable
  const tbl = new DataTable('#tblSubs', {
    ajax: {
      url: '<?= site_url('admin/subscribers/list') ?>',
      dataSrc: (json)=>{ if (json.token) setToken(json.token); return json.data || []; }
    },
    order: [[3, 'desc']],
    columns: [
      { data: 'id' },
      { data: 'email', render: d => `<span class="fw-semibold">${escapeHtml(d||'')}</span>` },
      { data: 'verified', render: v => (Number(v) ? '<span class="badge badge-on">Ya</span>' : '<span class="badge badge-off">Tidak</span>'), className:'text-center' },
      { data: 'created_at', render: d => d ? `<span class="mono">${escapeHtml(d)}</span>` : '—' },
      { data: null, orderable:false, render: (row)=>{
          const verified = Number(row.verified) === 1;
          return `<div class="btn-group btn-group-sm" role="group">
            <button class="btn btn-outline-primary" onclick="onEdit(${row.id})"><i class="bi bi-pencil"></i></button>
            <button class="btn btn-outline-${verified ? 'warning':'success'}" onclick="onToggle(${row.id})">
              <i class="bi bi-${verified ? 'x-circle':'check-circle'}"></i>
            </button>
            <button class="btn btn-outline-danger" onclick="onDelete(${row.id})"><i class="bi bi-trash"></i></button>
          </div>`;
        }}
    ]
  });

  function escapeHtml(s){ return (s??'').toString().replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m])); }

  // Tambah
  btnAdd?.addEventListener('click', ()=>{
    form.reset();
    form.s_id.value = '';
    document.getElementById('modalTitle').textContent = 'Tambah Subscriber';
    modal.show();
  });

  // Edit (ambil detail via endpoint agar data selalu fresh)
  window.onEdit = (id)=>{
    fetch('<?= site_url('admin/subscribers/get') ?>/'+id)
      .then(r=>r.json()).then(j=>{
        if (j.token) setToken(j.token);
        if (!j.status) { Swal.fire('Gagal', j.message||'Data tidak ditemukan','error'); return; }
        const d = j.data || {};
        form.s_id.value      = d.id || '';
        form.s_email.value   = d.email || '';
        form.s_verified.checked = Number(d.verified) === 1;
        document.getElementById('modalTitle').textContent = 'Edit Subscriber';
        modal.show();
      }).catch(()=> Swal.fire('Error','Tidak dapat menghubungi server.','error'));
  };

  // Toggle verified
  window.onToggle = (id)=>{
    Swal.fire({ title:'Ubah verifikasi?', icon:'question', showCancelButton:true, confirmButtonText:'Ya' })
      .then(res=>{
        if(!res.isConfirmed) return;
        fetch('<?= site_url('admin/subscribers/toggle') ?>/'+id, {
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
    Swal.fire({ title:'Hapus Subscriber?', text:'Tindakan ini tidak bisa dibatalkan.', icon:'warning', showCancelButton:true, confirmButtonText:'Ya, hapus' })
      .then(res=>{
        if(!res.isConfirmed) return;
        fetch('<?= site_url('admin/subscribers/delete') ?>/'+id, {
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

    fetch('<?= site_url('admin/subscribers/save') ?>', {
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
