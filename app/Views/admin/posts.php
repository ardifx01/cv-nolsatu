<?= $this->extend('admin/layouts/main') ?>
<?= $this->section('title') ?>Posts<?= $this->endSection() ?>

<?= $this->section('styles') ?>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/quill@1.3.7/dist/quill.snow.css">
<meta name="csrf-token" content="<?= csrf_hash() ?>">
<style>
  .thumb-sm{ width:52px; height:36px; object-fit:cover; border-radius:.35rem; border:1px solid var(--bs-border-color); }
  .q-editor{ height: 320px; }
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
      <table id="tblPosts" class="table table-striped align-middle w-100">
        <thead>
          <tr>
            <th width="48">#</th>
            <th>Judul</th>
            <th>Slug</th>
            <th width="90">Tipe</th>
            <th width="90">Status</th>
            <th width="150">Terbit</th>
            <th width="90">Thumb</th>
            <th width="150">Aksi</th>
          </tr>
        </thead>
        <tbody></tbody>
      </table>
    </div>
  </div>
</div>

<!-- Modal Add/Edit -->
<div class="modal fade" id="modalPost" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-scrollable">
    <form class="modal-content" id="frmPost" enctype="multipart/form-data">
      <?= csrf_field() ?>
      <input type="hidden" name="id" id="p_id">
      <input type="hidden" name="thumb_current" id="p_thumb_current">

      <div class="modal-header">
        <h5 class="modal-title" id="modalTitle">Tambah Post</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
      </div>

      <div class="modal-body">
        <div class="row g-3">
          <div class="col-md-8">
            <label class="form-label">Judul</label>
            <input type="text" class="form-control" name="title" id="p_title" required>
          </div>
          <div class="col-md-4">
            <label class="form-label">Slug</label>
            <input type="text" class="form-control" name="slug" id="p_slug" placeholder="opsional (auto)">
          </div>

          <div class="col-md-3">
            <label class="form-label">Tipe</label>
            <select class="form-select" name="type" id="p_type">
              <option value="news">News</option>
              <option value="press">Press</option>
              <option value="tips">Tips</option>
            </select>
          </div>
          <div class="col-md-3">
            <label class="form-label">Status</label>
            <select class="form-select" name="status" id="p_status">
              <option value="draft">Draft</option>
              <option value="published">Published</option>
            </select>
          </div>
          <div class="col-md-3">
            <label class="form-label">Tanggal Terbit</label>
            <input type="datetime-local" class="form-control" name="published_at" id="p_published">
            <div class="form-text">Kosongkan untuk auto (saat publish).</div>
          </div>
          <div class="col-md-3">
            <label class="form-label">Thumbnail (gambar)</label>
            <input type="file" class="form-control" name="thumbnail_file" id="p_thumb_file" accept=".jpg,.jpeg,.png,.webp">
            <div class="form-text">JPG/PNG/WEBP maks 5MB. Biarkan kosong saat edit jika tidak diganti.</div>
            <div id="thumbPrevWrap" class="small mt-2" style="display:none;">
              Sudah ada: <a id="thumbPrevLink" href="#" target="_blank" rel="noopener">lihat</a>
            </div>
          </div>

          <div class="col-12">
            <label class="form-label">Excerpt</label>
            <textarea class="form-control" rows="2" name="excerpt" id="p_excerpt" placeholder="Ringkasan singkat (opsional)"></textarea>
          </div>

          <div class="col-12">
            <label class="form-label">Konten</label>
            <div id="qEditor" class="form-control q-editor"></div>
            <textarea class="d-none" name="content" id="p_content"></textarea>
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
<script src="https://cdn.jsdelivr.net/npm/quill@1.3.7/dist/quill.min.js"></script>
<script>
  const CSRF_META = document.querySelector('meta[name="csrf-token"]');
  const setToken  = (t)=>{ CSRF_META?.setAttribute('content', t); };
  const getToken  = ()=> CSRF_META?.getAttribute('content') || '';

  const modalEl = document.getElementById('modalPost');
  const modal   = new bootstrap.Modal(modalEl);
  const form    = document.getElementById('frmPost');
  const btnAdd  = document.getElementById('btnAdd');
  const btnSave = document.getElementById('btnSave');

  const quill = new Quill('#qEditor', {
    theme: 'snow',
    placeholder: 'Tulis konten di sini...',
    modules: {
      toolbar: [
        [{ header: [1,2,3,false] }],
        ['bold','italic','underline','strike'],
        [{'list':'ordered'},{'list':'bullet'}],
        [{'align':[]}],
        ['link','blockquote','code-block','image'],
        ['clean']
      ]
    }
  });

  // DataTable
  const tbl = new DataTable('#tblPosts', {
    ajax: {
      url: '<?= site_url('admin/posts/list') ?>',
      dataSrc: (json)=>{ if (json.token) setToken(json.token); return json.data || []; }
    },
    order: [[5,'desc']],
    columns: [
      { data: 'id' },
      { data: null, render: (row)=>{
          const t = escapeHtml(row.title||'');
          const ex= escapeHtml(row.excerpt||'');
          return `<div class="fw-semibold">${t}</div>
                  <div class="small text-secondary">${ex}</div>`;
        }},
      { data: 'slug', render: (d)=> `<code>${escapeHtml(d||'')}</code>` },
      { data: 'type', render: d=> `<span class="badge bg-info-subtle text-info-emphasis text-uppercase">${escapeHtml(d)}</span>` },
      { data: 'status', render: d=> d==='published'
            ? '<span class="badge bg-success-subtle text-success-emphasis">Published</span>'
            : '<span class="badge bg-secondary">Draft</span>' },
      { data: 'published_at', render: d=> d ? `<span class="small">${escapeHtml(d)}</span>` : '<span class="text-secondary small">—</span>' },
      { data: 'thumbnail', orderable:false, render: d=> d ? `<img class="thumb-sm" src="${escapeAttr(d)}" alt="">` : '—' },
      { data: null, orderable:false, render: (row)=>{
          return `<div class="btn-group btn-group-sm" role="group">
              <button class="btn btn-outline-primary" onclick='onEdit(${row.id})'><i class="bi bi-pencil"></i></button>
              <button class="btn btn-outline-${row.status==='published'?'warning':'success'}" onclick="onToggle(${row.id})">
                <i class="bi bi-${row.status==='published'?'pause':'play'}-circle"></i>
              </button>
              <button class="btn btn-outline-danger" onclick="onDelete(${row.id})"><i class="bi bi-trash"></i></button>
          </div>`;
        }}
    ]
  });

  function escapeHtml(s){ return (s??'').toString().replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m])); }
  function escapeAttr(s){ return escapeHtml(s).replace(/"/g,'&quot;'); }

  // Tambah
  btnAdd?.addEventListener('click', ()=>{
    form.reset();
    form.p_id.value = '';
    form.p_slug.value = '';
    form.p_type.value = 'news';
    form.p_status.value = 'draft';
    form.p_published.value = '';
    form.p_thumb_current.value = '';
    document.getElementById('thumbPrevWrap').style.display = 'none';
    quill.setContents([]);
    document.getElementById('modalTitle').textContent = 'Tambah Post';
    modal.show();
  });

  // Edit (ambil via endpoint agar data lengkap & aman)
  window.onEdit = (id)=>{
    fetch('<?= site_url('admin/posts/get') ?>/'+id)
      .then(r=>r.json()).then(j=>{
        if (j.token) setToken(j.token);
        if (!j.status) { Swal.fire('Gagal', j.message||'Data tidak ditemukan','error'); return; }
        const row = j.data || {};
        form.reset();
        form.p_id.value      = row.id || '';
        form.p_title.value   = row.title || '';
        form.p_slug.value    = row.slug  || '';
        form.p_type.value    = row.type  || 'news';
        form.p_status.value  = row.status|| 'draft';
        form.p_excerpt.value = row.excerpt || '';
        form.p_published.value = row.published_at ? toLocalInputValue(row.published_at) : '';
        form.p_thumb_current.value = row.thumbnail || '';

        if (row.thumbnail) {
          document.getElementById('thumbPrevLink').href = row.thumbnail;
          document.getElementById('thumbPrevWrap').style.display = '';
        } else {
          document.getElementById('thumbPrevWrap').style.display = 'none';
        }

        quill.setContents([]);
        quill.clipboard.dangerouslyPasteHTML(row.content || '');
        document.getElementById('modalTitle').textContent = 'Edit Post';
        modal.show();
      }).catch(()=> Swal.fire('Error','Tidak dapat menghubungi server.','error'));
  };

  function toLocalInputValue(iso){
    const d = new Date(iso.replace(' ', 'T'));
    if (Number.isNaN(d.getTime())) return '';
    const pad = (n)=> (n<10?'0':'')+n;
    return `${d.getFullYear()}-${pad(d.getMonth()+1)}-${pad(d.getDate())}T${pad(d.getHours())}:${pad(d.getMinutes())}`;
  }

  // Toggle Publish/Draft
  window.onToggle = (id)=>{
    Swal.fire({ title:'Ubah Status?', icon:'question', showCancelButton:true, confirmButtonText:'Ya, ubah' })
      .then(res=>{
        if(!res.isConfirmed) return;
        fetch('<?= site_url('admin/posts/toggle') ?>/'+id, {
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
    Swal.fire({ title:'Hapus Post?', text:'Tindakan ini tidak bisa dibatalkan.', icon:'warning', showCancelButton:true, confirmButtonText:'Ya, hapus' })
      .then(res=>{
        if(!res.isConfirmed) return;
        fetch('<?= site_url('admin/posts/delete') ?>/'+id, {
          method:'POST', headers:{'X-CSRF-TOKEN': getToken()}
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

  // Submit (Create/Update → satu endpoint)
  form?.addEventListener('submit', (e)=>{
    e.preventDefault();
    form.p_content.value = quill.root.innerHTML; // ambil html

    const data = new FormData(form);
    btnSave.disabled = true;
    btnSave.querySelector('.spinner-border').classList.remove('d-none');
    btnSave.querySelector('.txt').textContent = 'Menyimpan...';

    fetch('<?= site_url('admin/posts/save') ?>', {
      method: 'POST',
      headers: {'X-CSRF-TOKEN': getToken()},
      body: data
    }).then(r=>r.json()).then(j=>{
      if (j.token) setToken(j.token);
      if (j.status){
        modal.hide();
        Swal.fire('Berhasil', j.message || 'Tersimpan.', 'success');
        tbl.ajax.reload(null,false);
        form.p_thumb_file.value = ''; // reset input file
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
