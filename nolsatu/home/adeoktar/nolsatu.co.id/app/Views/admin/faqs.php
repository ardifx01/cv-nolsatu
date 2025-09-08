<?= $this->extend('admin/layouts/main') ?>
<?= $this->section('title') ?>FAQs<?= $this->endSection() ?>

<?= $this->section('styles') ?>
<meta name="csrf-token" content="<?= csrf_hash() ?>">
<style>
  .badge-on  { background: var(--bs-success-bg-subtle); color: var(--bs-success-text-emphasis); }
  .badge-off { background: var(--bs-secondary-bg); color: var(--bs-secondary-color); }
  .w-80px{ width:80px; }
</style>
<?= $this->endSection() ?>

<?= $this->section('toolbar') ?>
<button class="btn btn-primary btn-sm" id="btnAdd"><i class="bi bi-plus-lg me-1"></i> Tambah</button>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="card card-soft">
  <div class="card-body">
    <div class="table-responsive">
      <table id="tblFaqs" class="table table-striped align-middle w-100">
        <thead>
          <tr>
            <th class="w-80px">#</th>
            <th>Pertanyaan</th>
            <th>Kategori</th>
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
<div class="modal fade" id="modalFaq" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <form class="modal-content" id="frmFaq">
      <?= csrf_field() ?>
      <input type="hidden" name="id" id="f_id">
      <div class="modal-header">
        <h5 class="modal-title" id="modalTitle">Tambah FAQ</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
      </div>
      <div class="modal-body">
        <div class="row g-3">
          <div class="col-12">
            <label class="form-label">Pertanyaan</label>
            <input type="text" class="form-control" name="question" id="f_question" required>
          </div>
          <div class="col-md-6">
            <label class="form-label">Kategori (opsional)</label>
            <input type="text" class="form-control" name="category" id="f_category" maxlength="100" placeholder="Contoh: Paspor, Visa, Izin Tinggal">
          </div>
          <div class="col-md-3">
            <label class="form-label">Sort</label>
            <input type="number" class="form-control" name="sort" id="f_sort" value="0" min="0" step="1">
          </div>
          <div class="col-md-3">
            <label class="form-label d-block">Aktif?</label>
            <div class="form-check form-switch">
              <input class="form-check-input" type="checkbox" role="switch" id="f_active" name="is_active" checked>
              <label class="form-check-label" for="f_active">Tampilkan</label>
            </div>
          </div>
          <div class="col-12">
            <label class="form-label">Jawaban</label>
            <textarea class="form-control" rows="6" name="answer" id="f_answer" required placeholder="Tulis jawaban singkat, bisa gunakan baris baru untuk poin-poin."></textarea>
            <div class="form-text">* Di halaman publik saat ini jawaban ditampilkan sebagai teks (bukan HTML).</div>
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

  const modalEl = document.getElementById('modalFaq');
  const modal   = new bootstrap.Modal(modalEl);
  const form    = document.getElementById('frmFaq');
  const btnAdd  = document.getElementById('btnAdd');
  const btnSave = document.getElementById('btnSave');

  // DataTable
  const tbl = new DataTable('#tblFaqs', {
    ajax: {
      url: '<?= site_url('admin/faqs/list') ?>',
      dataSrc: (json)=>{ if (json.token) setToken(json.token); return json.data || []; }
    },
    order: [[3,'asc']], // sort ASC
    columns: [
      { data: 'id' },
      { data: null, render: (row)=>{
          const q = escapeHtml(row.question || '');
          const a = escapeHtml((row.answer || '').slice(0,180));
          return `<div class="fw-semibold">${q}</div>
                  <div class="small text-secondary">${a}${(row.answer||'').length>180?'…':''}</div>`;
        }},
      { data: 'category', render: d=> d? `<span class="badge bg-info-subtle text-info-emphasis">${escapeHtml(d)}</span>` : '<span class="text-secondary small">—</span>' },
      { data: 'sort' },
      // Kolom "Aktif"
{
  data: 'is_active',
  render: v => Number(v) === 1
    ? '<span class="badge badge-on">Aktif</span>'
    : '<span class="badge badge-off">Nonaktif</span>'
},

// Kolom "Aksi"
{
  data: null,
  orderable: false,
  render: (row) => {
    const on = Number(row.is_active) === 1;
    return `<div class="btn-group btn-group-sm" role="group">
      <button class="btn btn-outline-primary" onclick='onEdit(${JSON.stringify(row)})'>
        <i class="bi bi-pencil"></i>
      </button>
      <button class="btn btn-outline-${on ? 'warning' : 'success'}" onclick="onToggle(${row.id})">
        <i class="bi bi-${on ? 'pause' : 'play'}-circle"></i>
      </button>
      <button class="btn btn-outline-danger" onclick="onDelete(${row.id})">
        <i class="bi bi-trash"></i>
      </button>
    </div>`;
  }
}

    ]
  });

  function escapeHtml(s){ return (s??'').toString().replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m])); }

  // Tambah
  btnAdd?.addEventListener('click', ()=>{
    form.reset();
    form.f_id.value      = '';
    form.f_question.value= '';
    form.f_category.value= '';
    form.f_sort.value    = '0';
    form.f_active.checked= true;
    form.f_answer.value  = '';
    document.getElementById('modalTitle').textContent = 'Tambah FAQ';
    modal.show();
  });

  // Edit
  window.onEdit = (row)=>{
    form.reset();
    form.f_id.value       = row.id;
    form.f_question.value = row.question || '';
    form.f_category.value = row.category || '';
    form.f_sort.value     = row.sort ?? 0;
    form.f_active.checked = !!row.is_active;
    form.f_answer.value   = row.answer || '';
    document.getElementById('modalTitle').textContent = 'Edit FAQ';
    modal.show();
  };

  // Toggle aktif
  window.onToggle = (id)=>{
    Swal.fire({ title:'Ubah Status?', icon:'question', showCancelButton:true, confirmButtonText:'Ya' })
      .then(res=>{
        if(!res.isConfirmed) return;
        fetch('<?= site_url('admin/faqs/toggle') ?>/'+id, {
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
    Swal.fire({ title:'Hapus FAQ?', text:'Tindakan ini tidak bisa dibatalkan.', icon:'warning', showCancelButton:true, confirmButtonText:'Ya, hapus' })
      .then(res=>{
        if(!res.isConfirmed) return;
        fetch('<?= site_url('admin/faqs/delete') ?>/'+id, {
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

  // Submit Add/Edit
  form?.addEventListener('submit', (e)=>{
    e.preventDefault();
    const id   = form.f_id.value.trim();
    const data = new FormData(form);

    btnSave.disabled = true;
    btnSave.querySelector('.spinner-border').classList.remove('d-none');
    btnSave.querySelector('.txt').textContent = 'Menyimpan...';

    const url = id
      ? '<?= site_url('admin/faqs/update') ?>/'+id
      : '<?= site_url('admin/faqs/store') ?>';

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
