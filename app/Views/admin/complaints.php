<?= $this->extend('admin/layouts/main') ?>
<?= $this->section('title') ?>Complaints<?= $this->endSection() ?>

<?= $this->section('styles') ?>
<meta name="csrf-token" content="<?= csrf_hash() ?>">
<style>
  .w-80px{width:80px}.w-120px{width:120px}.w-150px{width:150px}.w-200px{width:200px}
  .badge-new         { background: var(--bs-secondary-bg); color: var(--bs-secondary-color); }
  .badge-inprogress  { background: var(--bs-warning-bg-subtle); color: var(--bs-warning-text-emphasis); }
  .badge-resolved    { background: var(--bs-success-bg-subtle); color: var(--bs-success-text-emphasis); }
  .badge-closed      { background: var(--bs-dark-bg-subtle); color: var(--bs-dark-text); }
  .mono{font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, "Liberation Mono", monospace;}
</style>
<?= $this->endSection() ?>

<?= $this->section('toolbar') ?>
<!-- tidak ada tombol tambah -->
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="card card-soft">
  <div class="card-body">
    <div class="table-responsive">
      <table id="tblComplaints" class="table table-striped align-middle w-100">
        <thead>
          <tr>
            <th class="w-80px">#</th>
            <th class="w-200px">Ticket / Nama</th>
            <th class="w-200px">Kontak</th>
            <th>Kategori</th>
            <th class="w-120px">Status</th>
            <th class="w-150px">Dibuat</th>
            <th class="w-150px">Aksi</th>
          </tr>
        </thead>
        <tbody></tbody>
      </table>
    </div>
  </div>
</div>

<!-- Modal Detail/Edit -->
<div class="modal fade" id="modalComplaint" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <form class="modal-content" id="frmComplaint">
      <?= csrf_field() ?>
      <input type="hidden" name="id" id="c_id">
      <div class="modal-header">
        <h5 class="modal-title">Detail Pengaduan</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
      </div>
      <div class="modal-body">
        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label">Ticket</label>
            <input type="text" class="form-control mono" id="c_ticket" readonly>
          </div>
          <div class="col-md-6">
            <label class="form-label">Dibuat</label>
            <input type="text" class="form-control" id="c_created" readonly>
          </div>

          <div class="col-md-6">
            <label class="form-label">Nama</label>
            <input type="text" class="form-control" id="c_name" readonly>
          </div>
          <div class="col-md-6">
            <label class="form-label">Email</label>
            <input type="text" class="form-control" id="c_email" readonly>
          </div>
          <div class="col-md-6">
            <label class="form-label">WhatsApp</label>
            <input type="text" class="form-control" id="c_wa" readonly>
          </div>
          <div class="col-md-6">
            <label class="form-label">IP</label>
            <input type="text" class="form-control" id="c_ip" readonly>
          </div>
          <div class="col-12">
            <label class="form-label">User Agent</label>
            <input type="text" class="form-control" id="c_ua" readonly>
          </div>

          <div class="col-md-6">
            <label class="form-label">Kategori</label>
            <select class="form-select" name="category" id="c_category">
              <option value="Informasi Paspor">Informasi Paspor</option>
              <option value="Pengaduan Layanan">Pengaduan Layanan</option>
              <option value="WNA/Izin Tinggal">WNA/Izin Tinggal</option>
              <option value="Lainnya">Lainnya</option>
            </select>
          </div>
          <div class="col-md-6">
            <label class="form-label">Status</label>
            <select class="form-select" name="status" id="c_status">
              <option value="new">Baru</option>
              <option value="in_progress">Diproses</option>
              <option value="resolved">Selesai</option>
              <option value="closed">Tutup</option>
            </select>
          </div>

          <div class="col-12">
            <label class="form-label">Pesan</label>
            <textarea class="form-control" id="c_message" rows="6" readonly></textarea>
          </div>
          <div class="col-md-6">
            <label class="form-label">Resolved At</label>
            <input type="text" class="form-control" id="c_resolved" readonly>
          </div>
          <div class="col-md-6">
            <label class="form-label">Updated At</label>
            <input type="text" class="form-control" id="c_updated" readonly>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-secondary" type="button" data-bs-dismiss="modal">Tutup</button>
        <button class="btn btn-primary" id="btnSave" type="submit">
          <span class="txt">Simpan Perubahan</span>
          <span class="spinner-border spinner-border-sm d-none ms-2" role="status"></span>
        </button>
      </div>
    </form>
  </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
  // ==== CSRF helpers ====
  const CSRF_META = document.querySelector('meta[name="csrf-token"]');
  const setToken  = (t)=>{ CSRF_META?.setAttribute('content', t); };
  const getToken  = ()=> CSRF_META?.getAttribute('content') || '';

  const modalEl = document.getElementById('modalComplaint');
  const modal   = new bootstrap.Modal(modalEl);
  const form    = document.getElementById('frmComplaint');
  const btnSave = document.getElementById('btnSave');

  // ==== DataTable ====
  const tbl = new DataTable('#tblComplaints', {
    ajax: {
      url: '<?= site_url('admin/complaints/list') ?>',
      dataSrc: (json)=>{ if (json.token) setToken(json.token); return json.data || []; }
    },
    order: [[5, 'desc']],
    columns: [
      { data: 'id' },
      { data: null, render: (r)=>{
          const t = `<div class="mono">${escapeHtml(r.ticket||'')}</div>`;
          const n = `<div class="fw-semibold">${escapeHtml(r.name||'')}</div>`;
          return t + n;
        }},
      { data: null, render: (r)=>{
          const e = escapeHtml(r.email||'');
          const w = escapeHtml(r.whatsapp||'');
          return `<div><i class="bi bi-envelope"></i> ${e}</div><div><i class="bi bi-whatsapp"></i> ${w}</div>`;
        }},
      { data: 'category', render: d => d ? escapeHtml(d) : '—' },
      { data: 'status', render: s => badgeStatus(s), className:'text-nowrap' },
      { data: 'created_at', render: d => d ? `<span class="mono">${escapeHtml(d)}</span>` : '—' },
      { data: null, orderable:false, render: (row)=>{
          return `<div class="btn-group btn-group-sm" role="group">
            <button class="btn btn-outline-primary" onclick="onView(${row.id})"><i class="bi bi-eye"></i></button>
            <button class="btn btn-outline-danger" onclick="onDelete(${row.id})"><i class="bi bi-trash"></i></button>
          </div>`;
        }}
    ]
  });

  function escapeHtml(s){ return (s??'').toString().replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m])); }
  function badgeStatus(s){
    switch(String(s)){
      case 'new':         return '<span class="badge badge-new">Baru</span>';
      case 'in_progress': return '<span class="badge badge-inprogress">Diproses</span>';
      case 'resolved':    return '<span class="badge badge-resolved">Selesai</span>';
      case 'closed':      return '<span class="badge badge-closed">Tutup</span>';
      default:            return '<span class="badge bg-secondary">-</span>';
    }
  }

  // ==== View / Edit ====
  window.onView = (id)=>{
    fetch('<?= site_url('admin/complaints/get') ?>/'+id)
      .then(r=>r.json()).then(j=>{
        if (j.token) setToken(j.token);
        if (!j.status) { Swal.fire('Gagal', j.message||'Data tidak ditemukan','error'); return; }
        const d = j.data || {};
        form.c_id.value     = d.id || '';
        document.querySelector('#modalComplaint .modal-title').textContent = `Ticket ${d.ticket||''}`;

        // isi field
        form.querySelector('#c_ticket').value   = d.ticket || '';
        form.querySelector('#c_created').value  = d.created_at || '';
        form.querySelector('#c_name').value     = d.name || '';
        form.querySelector('#c_email').value    = d.email || '';
        form.querySelector('#c_wa').value       = d.whatsapp || '';
        form.querySelector('#c_ip').value       = d.ip_addr || '';
        form.querySelector('#c_ua').value       = d.user_agent || '';
        form.querySelector('#c_category').value = d.category || 'Lainnya';
        form.querySelector('#c_status').value   = d.status || 'new';
        form.querySelector('#c_message').value  = d.message || '';
        form.querySelector('#c_resolved').value = d.resolved_at || '';
        form.querySelector('#c_updated').value  = d.updated_at || '';

        modal.show();
      }).catch(()=> Swal.fire('Error','Tidak dapat menghubungi server.','error'));
  };

  // ==== Delete ====
  window.onDelete = (id)=>{
    Swal.fire({ title:'Hapus Pengaduan?', text:'Tindakan ini tidak bisa dibatalkan.', icon:'warning', showCancelButton:true, confirmButtonText:'Ya, hapus' })
      .then(res=>{
        if(!res.isConfirmed) return;
        fetch('<?= site_url('admin/complaints/delete') ?>/'+id, {
          method:'POST',
          headers:{'X-CSRF-TOKEN': getToken()}
        }).then(r=>r.json()).then(j=>{
          if (j.token) setToken(j.token);
          if (j.status){
            Swal.fire('Berhasil', j.message||'Data dihapus.', 'success');
            tbl.ajax.reload(null,false);
          } else {
            Swal.fire('Gagal', j.message||'Terjadi kesalahan.', 'error');
          }
        }).catch(()=> Swal.fire('Error','Tidak dapat menghubungi server.','error'));
      });
  };

  // ==== Submit update (kategori/status) ====
  form?.addEventListener('submit', (e)=>{
    e.preventDefault();
    const id = form.c_id.value;
    const data = new FormData(form);
    // hanya kirim field yang bisa diubah
    const payload = new URLSearchParams();
    payload.set('category', data.get('category') || '');
    payload.set('status',   data.get('status') || 'new');

    btnSave.disabled = true;
    btnSave.querySelector('.spinner-border').classList.remove('d-none');
    btnSave.querySelector('.txt').textContent = 'Menyimpan...';

    fetch('<?= site_url('admin/complaints/update') ?>/'+id, {
      method: 'POST',
      headers: { 'X-CSRF-TOKEN': getToken(), 'Content-Type':'application/x-www-form-urlencoded' },
      body: payload.toString()
    }).then(r=>r.json()).then(j=>{
      if (j.token) setToken(j.token);
      if (j.status){
        modal.hide();
        Swal.fire('Berhasil', j.message||'Perubahan disimpan.', 'success');
        tbl.ajax.reload(null,false);
      } else {
        Swal.fire('Gagal', j.message||'Validasi gagal.', 'error');
      }
    }).catch(()=>{
      Swal.fire('Error','Tidak dapat menghubungi server.','error');
    }).finally(()=>{
      btnSave.disabled = false;
      btnSave.querySelector('.spinner-border').classList.add('d-none');
      btnSave.querySelector('.txt').textContent = 'Simpan Perubahan';
    });
  });
</script>
<?= $this->endSection() ?>
