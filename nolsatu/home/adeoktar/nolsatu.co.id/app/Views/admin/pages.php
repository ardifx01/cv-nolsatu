<?= $this->extend('admin/layouts/main') ?>
<?= $this->section('title') ?>Pages<?= $this->endSection() ?>

<?= $this->section('styles') ?>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/quill@1.3.7/dist/quill.snow.css">
<meta name="csrf-token" content="<?= csrf_hash() ?>">
<style>
  .thumb-sm{ width:64px; height:40px; object-fit:cover; border-radius:.35rem; border:1px solid var(--bs-border-color); }
  .q-editor{ height: 360px; }
  .badge-draft{ background: var(--bs-secondary); }
  .badge-pub  { background: var(--bs-success-bg-subtle); color: var(--bs-success-text-emphasis); }
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
      <table id="tblPages" class="table table-striped align-middle w-100">
        <thead>
          <tr>
            <th width="48">#</th>
            <th>Judul</th>
            <th>Slug</th>
            <th>Menu</th>
            <th width="90">Status</th>
            <th width="160">Terbit</th>
            <th width="80">Cover</th>
            <th width="160">Aksi</th>
          </tr>
        </thead>
        <tbody></tbody>
      </table>
    </div>
  </div>
</div>

<!-- Modal Add/Edit -->
<div class="modal fade" id="modalPage" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-scrollable">
    <form class="modal-content" id="frmPage" enctype="multipart/form-data">
      <?= csrf_field() ?>
      <input type="hidden" name="id" id="p_id">
      <div class="modal-header">
        <h5 class="modal-title" id="modalTitle">Tambah Page</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
      </div>
      <div class="modal-body">
        <div class="row g-3">
          <div class="col-lg-8">
            <label class="form-label">Judul</label>
            <input type="text" class="form-control" name="title" id="p_title" required>
          </div>
          <div class="col-lg-4">
            <label class="form-label">Slug</label>
            <input type="text" class="form-control" name="slug" id="p_slug" placeholder="opsional (auto dari judul)">
          </div>

          <div class="col-md-4">
            <label class="form-label">Tautkan ke Menu (opsional)</label>
            <select class="form-select" name="menu_item_id" id="p_menu">
              <option value="">— Tidak ditautkan —</option>
              <?php foreach(($menus ?? []) as $m): ?>
                <option value="<?= $m['id'] ?>"><?= esc($m['title']).(!empty($m['url'])?' ('.$m['url'].')':'') ?></option>
              <?php endforeach; ?>
            </select>
            <div class="form-text">Jika dipilih, halaman ini akan dipakai untuk URL menu tersebut.</div>
          </div>
          <div class="col-md-4">
            <label class="form-label">Status</label>
            <select class="form-select" name="status" id="p_status">
              <option value="draft">Draft</option>
              <option value="published">Published</option>
            </select>
          </div>
          <div class="col-md-4">
            <label class="form-label">Tanggal Terbit</label>
            <input type="datetime-local" class="form-control" name="published_at" id="p_published">
            <div class="form-text">Kosongkan untuk auto saat publish.</div>
          </div>

          <div class="col-12">
            <label class="form-label">Excerpt</label>
            <textarea class="form-control" rows="2" name="excerpt" id="p_excerpt" placeholder="Ringkasan singkat (opsional)"></textarea>
          </div>

          <div class="col-md-6">
            <label class="form-label">Cover (JPG/PNG/WEBP ≤ 2MB)</label>
            <input type="file" class="form-control" name="cover" id="p_cover" accept=".jpg,.jpeg,.png,.webp">
            <div class="small mt-1" id="currentCover" style="display:none;"></div>
          </div>
          <div class="col-md-6">
            <label class="form-label">OG Image (URL)</label>
            <input type="url" class="form-control" name="og_image" id="p_og" placeholder="https://... (opsional)">
          </div>

          <div class="col-md-6">
            <label class="form-label">SEO Title</label>
            <input type="text" class="form-control" name="seo_title" id="p_seo_title" placeholder="opsional">
          </div>
          <div class="col-md-6">
            <label class="form-label">SEO Description</label>
            <input type="text" class="form-control" name="seo_description" id="p_seo_desc" placeholder="opsional">
          </div>

          <div class="col-12">
            <label class="form-label">Konten</label>
            <div id="qEditor" class="form-control q-editor"></div>
            <textarea class="d-none" name="content_html" id="p_content"></textarea>
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

  const modalEl = document.getElementById('modalPage');
  const modal   = new bootstrap.Modal(modalEl);
  const form    = document.getElementById('frmPage');
  const btnAdd  = document.getElementById('btnAdd');
  const btnSave = document.getElementById('btnSave');

  // Quill
  const quill = new Quill('#qEditor', {
    theme: 'snow',
    placeholder: 'Tulis konten halaman…',
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
  const tbl = new DataTable('#tblPages', {
    ajax: {
      url: '<?= site_url('admin/pages/list') ?>',
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
      { data: 'slug', render: d=> `<code>${escapeHtml(d||'')}</code>` },
      { data: 'menu_title', render: d=> d ? escapeHtml(d) : '<span class="text-secondary small">—</span>' },
      { data: 'status', render: d=> d==='published'
            ? '<span class="badge badge-pub">Published</span>'
            : '<span class="badge badge-draft">Draft</span>' },
      { data: 'published_at', render: d=> d ? `<span class="small">${escapeHtml(d)}</span>` : '<span class="text-secondary small">—</span>' },
      { data: 'cover_image', orderable:false, render: d=> d ? `<img class="thumb-sm" src="${escapeAttr(d)}" alt="">` : '—' },
      { data: null, orderable:false, render: (row)=>{
          return `<div class="btn-group btn-group-sm" role="group">
              <button class="btn btn-outline-primary" onclick='onEdit(${JSON.stringify(row)})'><i class="bi bi-pencil"></i></button>
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
  function toLocalInputValue(iso){
    const d = new Date((iso||'').replace(' ','T'));
    if (Number.isNaN(d.getTime())) return '';
    const pad = (n)=> (n<10?'0':'')+n;
    return `${d.getFullYear()}-${pad(d.getMonth()+1)}-${pad(d.getDate())}T${pad(d.getHours())}:${pad(d.getMinutes())}`;
  }

  // Tambah
  btnAdd?.addEventListener('click', ()=>{
    form.reset();
    form.p_id.value = '';
    document.getElementById('modalTitle').textContent = 'Tambah Page';
    quill.setContents([]);
    document.getElementById('currentCover').style.display = 'none';
    modal.show();
  });

  // Edit
  window.onEdit = (row)=>{
    form.reset();
    form.p_id.value        = row.id;
    form.p_title.value     = row.title || '';
    form.p_slug.value      = row.slug  || '';
    form.p_menu.value      = row.menu_item_id || '';
    form.p_status.value    = row.status || 'draft';
    form.p_published.value = row.published_at ? toLocalInputValue(row.published_at) : '';
    form.p_excerpt.value   = row.excerpt || '';
    form.p_og.value        = row.og_image || '';
    form.p_seo_title.value = row.seo_title || '';
    form.p_seo_desc.value  = row.seo_description || '';
    quill.setContents([]);
    // konten tidak dikirim di list; ambil ulang? opsi 1: ikutkan di list (lebih besar)
    // supaya simpel: minta konten via endpoint detail kecil? Untuk sekarang:
    // kita isi editor kosong jika tidak ada.
    // Jika kamu mau, ubah pagesList ikut select p.content_html.
    // Berikut contoh jika row.content_html tersedia:
    if (row.content_html) quill.clipboard.dangerouslyPasteHTML(row.content_html);

    // cover saat ini
    const cc = document.getElementById('currentCover');
    if (row.cover_image){
      cc.innerHTML = `Cover saat ini: <a href="${escapeAttr(row.cover_image)}" target="_blank" rel="noopener">${escapeHtml(row.cover_image)}</a>`;
      cc.style.display='';
    } else cc.style.display='none';

    document.getElementById('modalTitle').textContent = 'Edit Page';
    modal.show();
  };

  // Toggle
  window.onToggle = (id)=>{
    Swal.fire({title:'Ubah Status?',icon:'question',showCancelButton:true,confirmButtonText:'Ya'})
      .then(res=>{
        if(!res.isConfirmed) return;
        fetch('<?= site_url('admin/pages/toggle') ?>/'+id, {
          method:'POST', headers:{'X-CSRF-TOKEN': getToken()}
        }).then(r=>r.json()).then(j=>{
          if (j.token) setToken(j.token);
          if (j.status){ Swal.fire('Berhasil', j.message||'Status diperbarui.','success'); tbl.ajax.reload(null,false); }
          else { Swal.fire('Gagal', j.message||'Terjadi kesalahan.','error'); }
        }).catch(()=> Swal.fire('Error','Tidak dapat menghubungi server.','error'));
      });
  };

  // Delete
  window.onDelete = (id)=>{
    Swal.fire({title:'Hapus Halaman?',text:'Tindakan ini tidak bisa dibatalkan.',icon:'warning',showCancelButton:true,confirmButtonText:'Ya, hapus'})
      .then(res=>{
        if(!res.isConfirmed) return;
        fetch('<?= site_url('admin/pages/delete') ?>/'+id, {
          method:'POST', headers:{'X-CSRF-TOKEN': getToken()}
        }).then(r=>r.json()).then(j=>{
          if (j.token) setToken(j.token);
          if (j.status){ Swal.fire('Berhasil','Data dihapus.','success'); tbl.ajax.reload(null,false); }
          else { Swal.fire('Gagal', j.message||'Terjadi kesalahan.','error'); }
        }).catch(()=> Swal.fire('Error','Tidak dapat menghubungi server.','error'));
      });
  };

  // Submit create/update
  form?.addEventListener('submit', (e)=>{
    e.preventDefault();
    // masukkan konten Quill ke textarea hidden
    form.p_content.value = quill.root.innerHTML;

    const data = new FormData(form);
    btnSave.disabled = true;
    btnSave.querySelector('.spinner-border').classList.remove('d-none');
    btnSave.querySelector('.txt').textContent = 'Menyimpan...';

    fetch('<?= site_url('admin/pages/save') ?>', {
      method:'POST',
      headers:{'X-CSRF-TOKEN': getToken()},
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
      form.p_cover.value = '';
    });
  });
</script>
<?= $this->endSection() ?>
