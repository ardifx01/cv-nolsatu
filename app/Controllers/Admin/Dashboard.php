<?php namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class Dashboard extends BaseController
{
    protected $db;

    public function __construct()
    {
        helper(['url','text','security']); 
        $this->db = \Config\Database::connect();
    }

    public function index()
    {
        $db = $this->db;
        $count = fn($t)=> $db->table($t)->countAllResults();
        $data = [
            'title'         => 'Dashboard',
            'c_services'    => $count('services'),
            'c_posts'       => $count('posts'),
            'c_faqs'        => $count('faqs'),
            'c_docs'        => $count('documents'),
            'c_ann'         => $count('announcements'),
            'c_menu'        => $count('menu_items'),
            'c_complaints'  => $count('complaints'),
            'c_subs'        => $count('subscribers'),
        ];
        return view('admin/dashboard/index', $data);
    }

    /* ==============================
     * SERVICES: LISTING + JSON DATATABLES
     * ============================== */
    public function services()
    {
        return view('admin/services/index', [
            'title'    => 'Services',
            'subtitle' => 'Kelola kartu layanan daring pada halaman depan',
        ]);
    }

    public function servicesList()
    {
        // Ambil semua (biarkan DataTables client-side)
        $rows = $this->db->table('services')
            ->select('id,title,slug,description,icon,url,sort,is_active,created_at,updated_at')
            ->orderBy('sort','ASC')->orderBy('id','DESC')
            ->get()->getResultArray();

        return $this->response->setJSON([
            'data'  => $rows,
            'token' => csrf_hash(),
        ]);
    }

    /* ==============================
     * SERVICES: CREATE / UPDATE / DELETE / TOGGLE
     * ============================== */

    public function serviceStore()
    {
        $title = trim($this->request->getPost('title'));
        $slug  = trim($this->request->getPost('slug'));
        $desc  = (string)$this->request->getPost('description');
        $icon  = trim($this->request->getPost('icon'));
        $url   = trim($this->request->getPost('url'));
        $sort  = (int)$this->request->getPost('sort');
        $isAct = (int)$this->request->getPost('is_active');

        // Validasi minimal
        $errors = [];
        if ($title === '') $errors[] = 'Judul wajib diisi.';
        if ($url   === '') $errors[] = 'URL wajib diisi.';
        if (!empty($slug) && !preg_match('~^[a-z0-9\-]+$~', $slug)) {
            $errors[] = 'Slug hanya boleh huruf kecil, angka, dan tanda minus.';
        }

        if ($slug === '') $slug = $this->slugify($title);
        $slug = $this->uniqueSlug($slug);

        if ($errors) {
            return $this->response->setJSON([
                'status' => false,
                'message'=> implode(' ', $errors),
                'token'  => csrf_hash(),
            ]);
        }

        $now = date('Y-m-d H:i:s');

        $this->db->table('services')->insert([
            'title'       => $title,
            'slug'        => $slug,
            'description' => $desc,
            'icon'        => $icon,
            'url'         => $url,
            'sort'        => $sort,
            'is_active'   => $isAct ? 1 : 0,
            'created_at'  => $now,
            'updated_at'  => $now,
        ]);

        return $this->response->setJSON([
            'status'  => true,
            'message' => 'Service berhasil ditambahkan.',
            'token'   => csrf_hash(),
        ]);
    }

    public function serviceUpdate($id)
    {
        $id    = (int)$id;
        $exist = $this->db->table('services')->where('id',$id)->get()->getRowArray();
        if (!$exist) {
            return $this->response->setJSON([
                'status'=> false, 'message'=> 'Data tidak ditemukan.', 'token'=> csrf_hash()
            ]);
        }

        $title = trim($this->request->getPost('title'));
        $slug  = trim($this->request->getPost('slug'));
        $desc  = (string)$this->request->getPost('description');
        $icon  = trim($this->request->getPost('icon'));
        $url   = trim($this->request->getPost('url'));
        $sort  = (int)$this->request->getPost('sort');
        $isAct = (int)$this->request->getPost('is_active');

        $errors = [];
        if ($title === '') $errors[] = 'Judul wajib diisi.';
        if ($url   === '') $errors[] = 'URL wajib diisi.';
        if ($slug === '') $slug = $this->slugify($title);
        if (!preg_match('~^[a-z0-9\-]+$~', $slug)) {
            $errors[] = 'Slug hanya boleh huruf kecil, angka, dan tanda minus.';
        }
        // pastikan unik (kecuali dirinya)
        $slug = $this->uniqueSlug($slug, $id);

        if ($errors) {
            return $this->response->setJSON([
                'status' => false,
                'message'=> implode(' ', $errors),
                'token'  => csrf_hash(),
            ]);
        }

        $now = date('Y-m-d H:i:s');

        $this->db->table('services')->where('id',$id)->update([
            'title'       => $title,
            'slug'        => $slug,
            'description' => $desc,
            'icon'        => $icon,
            'url'         => $url,
            'sort'        => $sort,
            'is_active'   => $isAct ? 1 : 0,
            'updated_at'  => $now,
        ]);

        return $this->response->setJSON([
            'status'  => true,
            'message' => 'Service berhasil diperbarui.',
            'token'   => csrf_hash(),
        ]);
    }

    public function serviceDelete($id)
    {
        $id = (int)$id;
        $this->db->table('services')->where('id',$id)->delete();

        return $this->response->setJSON([
            'status'=> true, 'message'=> 'Service dihapus.', 'token'=> csrf_hash()
        ]);
    }

    public function serviceToggle($id)
    {
        $id  = (int)$id;
        $row = $this->db->table('services')->where('id',$id)->get()->getRowArray();
        if (!$row) {
            return $this->response->setJSON([
                'status'=> false, 'message'=> 'Data tidak ditemukan.', 'token'=> csrf_hash()
            ]);
        }
        $new = $row['is_active'] ? 0 : 1;
        $this->db->table('services')->where('id',$id)->update([
            'is_active'=> $new,
            'updated_at'=> date('Y-m-d H:i:s')
        ]);

        return $this->response->setJSON([
            'status'=> true, 'message'=> 'Status diperbarui.', 'token'=> csrf_hash()
        ]);
    }




    /* ==============================
     * POSTS: LISTING + VIEW
     * ============================== */
    public function posts()
    {
        return view('admin/posts', [
            'title'    => 'Posts',
            'subtitle' => 'Kelola berita/siaran pers/tips',
        ]);
    }
public function postsList()
{
    $rows = $this->db->table('posts')
        ->select('id,title,slug,excerpt,content,type,status,thumbnail,published_at')
        ->orderBy('published_at','DESC')
        ->get()->getResultArray();

    return $this->response->setJSON([
        'data'  => $rows,
        'token' => csrf_hash(),
    ]);
}

// Detail untuk form edit (biar konten lengkap & aman)
public function postGet($id)
{
    $row = $this->db->table('posts')
        ->where('id', (int)$id)
        ->get()->getRowArray();

    if (!$row) {
        return $this->response->setJSON([
            'status' => false,
            'message'=> 'Data tidak ditemukan',
            'token'  => csrf_hash(),
        ]);
    }

    return $this->response->setJSON([
        'status' => true,
        'data'   => $row,
        'token'  => csrf_hash(),
    ]);
}

// Simpan (create & update jadi satu)
// - thumbnail_file: upload gambar (jpg/jpeg/png/webp) ke /public/uploads/posts/YYYY/MM/
// - jika tidak upload saat edit → gunakan thumb_current
public function postSave()
{
    helper(['text']); // url_title

    $id      = (int) $this->request->getPost('id');
    $title   = trim((string)$this->request->getPost('title'));
    $slug    = trim((string)$this->request->getPost('slug'));
    $type    = (string)($this->request->getPost('type') ?: 'news');
    $status  = (string)($this->request->getPost('status') ?: 'draft');
    $excerpt = (string)$this->request->getPost('excerpt');
    $content = (string)$this->request->getPost('content');
    $pubIn   = (string)$this->request->getPost('published_at');
    $thumbCurrent = (string)$this->request->getPost('thumb_current'); // dari form hidden

    if ($title === '') {
        return $this->response->setJSON(['status'=>false,'message'=>'Judul wajib diisi','token'=>csrf_hash()]);
    }

    // Slug
    if ($slug === '') $slug = url_title($title, '-', true);
    // Pastikan slug unik
    $q = $this->db->table('posts')->select('id')->where('slug', $slug);
    if ($id) $q->where('id !=', $id);
    if ($q->get()->getRowArray()) {
        $slug .= '-'.substr(sha1(uniqid((string)time(), true)), 0, 6);
    }

    // published_at
    $now = date('Y-m-d H:i:s');
    $publishedAt = null;
    if ($status === 'published') {
        $publishedAt = $pubIn ? date('Y-m-d H:i:s', strtotime($pubIn)) : $now;
    }

    // Upload thumbnail (opsional)
    $thumbPath = $thumbCurrent ?: null;
    $file = $this->request->getFile('thumbnail_file');
    if ($file && $file->isValid() && !$file->hasMoved()) {
        // Validasi sederhana
        $ext   = strtolower($file->getExtension());
        $allow = ['jpg','jpeg','png','webp'];
        if (!in_array($ext, $allow, true)) {
            return $this->response->setJSON(['status'=>false,'message'=>'Thumbnail harus JPG/PNG/WEBP','token'=>csrf_hash()]);
        }
        if ($file->getSize() > 5 * 1024 * 1024) { // 5MB
            return $this->response->setJSON(['status'=>false,'message'=>'Ukuran thumbnail maks 5MB','token'=>csrf_hash()]);
        }

        $y  = date('Y'); $m = date('m');
        $dir = FCPATH.'uploads/posts/'.$y.'/'.$m.'/';
        if (!is_dir($dir)) @mkdir($dir, 0755, true);

        $newName = $file->getRandomName();
        if (!$file->move($dir, $newName)) {
            return $this->response->setJSON(['status'=>false,'message'=>'Gagal menyimpan file thumbnail','token'=>csrf_hash()]);
        }

        // Hapus file lama jika ada & berada di /uploads
        if ($thumbCurrent) {
            $old = FCPATH . ltrim($thumbCurrent, '/');
            if (is_file($old) && strpos($thumbCurrent, '/uploads/') === 0) {
                @unlink($old);
            }
        }

        $thumbPath = '/uploads/posts/'.$y.'/'.$m.'/'.$newName;
    }

    $data = [
        'title'        => $title,
        'slug'         => $slug,
        'excerpt'      => $excerpt,
        'content'      => $content,
        'type'         => $type,
        'status'       => $status,
        'thumbnail'    => $thumbPath,
        'published_at' => $publishedAt,
        'updated_at'   => $now,
    ];

    if (!$id) {
        $data['author_id']  = (int)(session('admin_id') ?: 0);
        $data['created_at'] = $now;
        $this->db->table('posts')->insert($data);
        return $this->response->setJSON(['status'=>true,'message'=>'Post ditambahkan','token'=>csrf_hash()]);
    } else {
        $row = $this->db->table('posts')->where('id', $id)->get()->getRowArray();
        if (!$row) {
            return $this->response->setJSON(['status'=>false,'message'=>'Data tidak ditemukan','token'=>csrf_hash()]);
        }
        $this->db->table('posts')->where('id', $id)->update($data);
        return $this->response->setJSON(['status'=>true,'message'=>'Post diperbarui','token'=>csrf_hash()]);
    }
}



    public function postDelete($id)
    {
        $id = (int)$id;
        $this->db->table('posts')->where('id',$id)->delete();
        return $this->response->setJSON(['status'=>true,'message'=>'Post dihapus.','token'=>csrf_hash()]);
    }

    public function postToggle($id)
    {
        $id  = (int)$id;
        $row = $this->db->table('posts')->where('id',$id)->get()->getRowArray();
        if (!$row) {
            return $this->response->setJSON(['status'=>false,'message'=>'Data tidak ditemukan.','token'=>csrf_hash()]);
        }

        $now      = date('Y-m-d H:i:s');
        $toStatus = $row['status']==='published' ? 'draft' : 'published';
        $upd = ['status'=>$toStatus, 'updated_at'=>$now];

        if ($toStatus==='published' && empty($row['published_at'])) {
            $upd['published_at'] = $now;
        }
        $this->db->table('posts')->where('id',$id)->update($upd);

        return $this->response->setJSON(['status'=>true,'message'=>'Status diperbarui.','token'=>csrf_hash()]);
    }
    
    
    
    /* ==============================
 * FAQs: LISTING + VIEW
 * ============================== */
public function faqs()
{
    return view('admin/faqs', [
        'title'    => 'FAQs',
        'subtitle' => 'Kelola Pertanyaan yang Sering Diajukan',
    ]);
}

public function faqsList()
{
    try {
        $rows = $this->db->table('faqs')
            ->select('id,question,answer,category,sort,is_active,created_at,updated_at')
            ->orderBy('sort','ASC')->orderBy('id','ASC')
            ->get()->getResultArray();

        return $this->response->setJSON([
            'data'  => $rows,
            'token' => csrf_hash(),
        ]);
    } catch (\Throwable $e) {
        log_message('error','faqsList error: {msg}',['msg'=>$e->getMessage()]);
        return $this->response->setStatusCode(500)->setJSON([
            'status'=>false,'message'=>'Gagal memuat data.','token'=>csrf_hash()
        ]);
    }
}

/* ==============================
 * FAQs: CREATE / UPDATE / DELETE / TOGGLE
 * ============================== */
public function faqStore()
{
    try {
        $q   = trim((string)$this->request->getPost('question'));
        $ans = trim((string)$this->request->getPost('answer'));
        $cat = trim((string)$this->request->getPost('category'));
        $srt = (int)$this->request->getPost('sort');
        $act = $this->request->getPost('is_active');

        $errors = [];
        if ($q==='')   $errors[] = 'Pertanyaan wajib diisi.';
        if ($ans==='') $errors[] = 'Jawaban wajib diisi.';
        if (mb_strlen($cat) > 100) $errors[] = 'Kategori terlalu panjang (maks 100).';
        if ($srt < 0)  $srt = 0;
        $isActive = in_array((string)$act, ['1','true','on','yes'], true) ? 1 : 0;

        if ($errors){
            return $this->response->setJSON(['status'=>false,'message'=>implode(' ', $errors),'token'=>csrf_hash()]);
        }

        $now = date('Y-m-d H:i:s');
        $this->db->table('faqs')->insert([
            'question'   => $q,
            'answer'     => $ans,
            'category'   => ($cat !== '') ? $cat : null,
            'sort'       => $srt,
            'is_active'  => $isActive,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        return $this->response->setJSON(['status'=>true,'message'=>'FAQ ditambahkan.','token'=>csrf_hash()]);
    } catch (\Throwable $e) {
        log_message('error','faqStore error: {msg}',['msg'=>$e->getMessage()]);
        return $this->response->setStatusCode(500)->setJSON([
            'status'=>false,'message'=>'Gagal menyimpan: '.$e->getMessage(),'token'=>csrf_hash()
        ]);
    }
}

public function faqUpdate($id)
{
    try {
        $id    = (int)$id;
        $exist = $this->db->table('faqs')->where('id',$id)->get()->getRowArray();
        if (!$exist) {
            return $this->response->setJSON(['status'=>false,'message'=>'Data tidak ditemukan.','token'=>csrf_hash()]);
        }

        $q   = trim((string)$this->request->getPost('question'));
        $ans = trim((string)$this->request->getPost('answer'));
        $cat = trim((string)$this->request->getPost('category'));
        $srt = (int)$this->request->getPost('sort');
        $act = $this->request->getPost('is_active');

        $errors = [];
        if ($q==='')   $errors[] = 'Pertanyaan wajib diisi.';
        if ($ans==='') $errors[] = 'Jawaban wajib diisi.';
        if (mb_strlen($cat) > 100) $errors[] = 'Kategori terlalu panjang (maks 100).';
        if ($srt < 0)  $srt = 0;
        $isActive = in_array((string)$act, ['1','true','on','yes'], true) ? 1 : 0;

        if ($errors){
            return $this->response->setJSON(['status'=>false,'message'=>implode(' ', $errors),'token'=>csrf_hash()]);
        }

        $now = date('Y-m-d H:i:s');
        $this->db->table('faqs')->where('id',$id)->update([
            'question'   => $q,
            'answer'     => $ans,
            'category'   => ($cat !== '') ? $cat : null,
            'sort'       => $srt,
            'is_active'  => $isActive,
            'updated_at' => $now,
        ]);

        return $this->response->setJSON(['status'=>true,'message'=>'FAQ diperbarui.','token'=>csrf_hash()]);
    } catch (\Throwable $e) {
        log_message('error','faqUpdate error: {msg}',['msg'=>$e->getMessage()]);
        return $this->response->setStatusCode(500)->setJSON([
            'status'=>false,'message'=>'Gagal memperbarui: '.$e->getMessage(),'token'=>csrf_hash()
        ]);
    }
}

public function faqDelete($id)
{
    try {
        $id = (int)$id;
        $this->db->table('faqs')->where('id',$id)->delete();
        return $this->response->setJSON(['status'=>true,'message'=>'FAQ dihapus.','token'=>csrf_hash()]);
    } catch (\Throwable $e) {
        log_message('error','faqDelete error: {msg}',['msg'=>$e->getMessage()]);
        return $this->response->setStatusCode(500)->setJSON([
            'status'=>false,'message'=>'Gagal menghapus: '.$e->getMessage(),'token'=>csrf_hash()
        ]);
    }
}

public function faqToggle($id)
{
    try {
        $id  = (int)$id;
        $row = $this->db->table('faqs')->where('id',$id)->get()->getRowArray();
        if (!$row) {
            return $this->response->setJSON(['status'=>false,'message'=>'Data tidak ditemukan.','token'=>csrf_hash()]);
        }
        $now = date('Y-m-d H:i:s');
        $this->db->table('faqs')->where('id',$id)->update([
            'is_active'  => $row['is_active'] ? 0 : 1,
            'updated_at' => $now
        ]);
        return $this->response->setJSON(['status'=>true,'message'=>'Status diperbarui.','token'=>csrf_hash()]);
    } catch (\Throwable $e) {
        log_message('error','faqToggle error: {msg}',['msg'=>$e->getMessage()]);
        return $this->response->setStatusCode(500)->setJSON([
            'status'=>false,'message'=>'Gagal mengubah status: '.$e->getMessage(),'token'=>csrf_hash()
        ]);
    }
}



/* ==============================
 * DOCUMENTS: LISTING + VIEW
 * ============================== */
public function documents()
{
    return view('admin/documents', [
        'title'    => 'Documents',
        'subtitle' => 'Kelola Unduhan & Regulasi',
    ]);
}

public function documentsList()
{
    try {
        $rows = $this->db->table('documents')
            ->select('id,title,file_url,type,description,is_active,sort,created_at,updated_at')
            ->orderBy('sort','ASC')->orderBy('id','DESC')
            ->get()->getResultArray();

        return $this->response->setJSON([
            'data'  => $rows,
            'token' => csrf_hash(),
        ]);
    } catch (\Throwable $e) {
        log_message('error','documentsList error: {msg}', ['msg'=>$e->getMessage()]);
        return $this->response->setStatusCode(500)->setJSON([
            'status'=>false,'message'=>'Gagal memuat data.','token'=>csrf_hash()
        ]);
    }
}

/* ==============================
 * DOCUMENTS: SAVE (CREATE & UPDATE)
 * ============================== */
public function documentSave()
{
    try {
        $id    = (int)($this->request->getPost('id') ?? 0);
        $title = trim((string)$this->request->getPost('title'));
        $type  = trim((string)$this->request->getPost('type'));
        $desc  = trim((string)$this->request->getPost('description'));
        $sort  = (int)($this->request->getPost('sort') ?? 0);
        $act   = $this->request->getPost('is_active');

        $errors = [];
        if ($title==='') $errors[] = 'Judul wajib diisi.';

        // Sanitasi ENUM
        $allowedTypes = ['form','regulation','guide','other'];
        if (!in_array($type, $allowedTypes, true)) $type = 'other';

        if ($sort < 0) $sort = 0;
        $isActive = in_array((string)$act, ['1','true','on','yes'], true) ? 1 : 0;

        // Cek update vs create
        $exist = null;
        if ($id) {
            $exist = $this->db->table('documents')->where('id',$id)->get()->getRowArray();
            if (!$exist) $errors[] = 'Data tidak ditemukan.';
        }

        // Upload handling
        $file     = $this->request->getFile('file'); // <input name="file">
        $fileUrl  = $exist['file_url'] ?? null;

        // Validasi saat create: file wajib
        if (!$id && (!$file || !$file->isValid())) {
            $errors[] = 'File wajib diunggah.';
        }

        // Validasi file jika ada upload baru
        if ($file && $file->isValid()) {
            $maxSizeMB = 15; // batas 15MB
            if ($file->getSizeByUnit('mb') > $maxSizeMB) {
                $errors[] = 'Ukuran file melebihi batas 15MB.';
            }
            // Bolehkan beberapa tipe umum
            $okExt = ['pdf','doc','docx','xls','xlsx','ppt','pptx','jpg','jpeg','png','webp'];
            $ext   = strtolower($file->getExtension() ?: pathinfo($file->getName(), PATHINFO_EXTENSION));
            if (!in_array($ext, $okExt, true)) {
                $errors[] = 'Ekstensi tidak didukung. Gunakan: '.implode(', ',$okExt).'.';
            }
        }

        if ($errors){
            return $this->response->setJSON(['status'=>false,'message'=>implode(' ', $errors),'token'=>csrf_hash()]);
        }

        // Pastikan folder upload tersedia (public/uploads/documents)
        $uploadDir = rtrim(FCPATH, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'documents';
        if (!is_dir($uploadDir)) {
            @mkdir($uploadDir, 0775, true);
        }

        // Jika ada upload baru: simpan dan siapkan URL
        if ($file && $file->isValid() && !$file->hasMoved()) {
            // Nama aman
            $safeName = time() . '-' . preg_replace('~[^a-z0-9\-]+~','-', strtolower(pathinfo($file->getClientName(), PATHINFO_FILENAME)));
            $safeName = trim($safeName,'-') . '.' . strtolower($file->getExtension());
            $file->move($uploadDir, $safeName, true);
            $fileUrl = base_url('uploads/documents/'.$safeName);

            // Hapus file lama jika update & file lama berada di folder yang sama
            if ($id && !empty($exist['file_url'])) {
                $old = parse_url($exist['file_url'], PHP_URL_PATH); // /uploads/documents/xxx.pdf
                if ($old && str_starts_with($old, '/uploads/documents/')) {
                    $oldPath = rtrim(FCPATH, DIRECTORY_SEPARATOR) . $old;
                    if (is_file($oldPath)) @unlink($oldPath);
                }
            }
        }

        $now = date('Y-m-d H:i:s');
        $data = [
            'title'       => $title,
            'file_url'    => $fileUrl,
            'type'        => $type,
            'description' => ($desc !== '') ? $desc : null,
            'is_active'   => $isActive,
            'sort'        => $sort,
            'updated_at'  => $now,
        ];

        if ($id) {
            $this->db->table('documents')->where('id',$id)->update($data);
            $msg = 'Dokumen diperbarui.';
        } else {
            $data['created_at'] = $now;
            $this->db->table('documents')->insert($data);
            $msg = 'Dokumen ditambahkan.';
        }

        return $this->response->setJSON(['status'=>true,'message'=>$msg,'token'=>csrf_hash()]);
    } catch (\Throwable $e) {
        log_message('error','documentSave error: {msg}', ['msg'=>$e->getMessage()]);
        return $this->response->setStatusCode(500)->setJSON([
            'status'=>false,'message'=>'Gagal menyimpan: '.$e->getMessage(),'token'=>csrf_hash()
        ]);
    }
}

/* ==============================
 * DOCUMENTS: DELETE / TOGGLE
 * ============================== */
public function documentDelete($id)
{
    try {
        $id  = (int)$id;
        $row = $this->db->table('documents')->where('id',$id)->get()->getRowArray();
        if ($row) {
            // Hapus file fisik bila dari folder uploads/documents
            if (!empty($row['file_url'])) {
                $old = parse_url($row['file_url'], PHP_URL_PATH);
                if ($old && str_starts_with($old, '/uploads/documents/')) {
                    $oldPath = rtrim(FCPATH, DIRECTORY_SEPARATOR) . $old;
                    if (is_file($oldPath)) @unlink($oldPath);
                }
            }
            $this->db->table('documents')->where('id',$id)->delete();
        }
        return $this->response->setJSON(['status'=>true,'message'=>'Dokumen dihapus.','token'=>csrf_hash()]);
    } catch (\Throwable $e) {
        log_message('error','documentDelete error: {msg}', ['msg'=>$e->getMessage()]);
        return $this->response->setStatusCode(500)->setJSON([
            'status'=>false,'message'=>'Gagal menghapus: '.$e->getMessage(),'token'=>csrf_hash()
        ]);
    }
}

public function documentToggle($id)
{
    try {
        $id  = (int)$id;
        $row = $this->db->table('documents')->where('id',$id)->get()->getRowArray();
        if (!$row) {
            return $this->response->setJSON(['status'=>false,'message'=>'Data tidak ditemukan.','token'=>csrf_hash()]);
        }
        $now = date('Y-m-d H:i:s');
        $this->db->table('documents')->where('id',$id)->update([
            'is_active'  => $row['is_active'] ? 0 : 1,
            'updated_at' => $now
        ]);
        return $this->response->setJSON(['status'=>true,'message'=>'Status diperbarui.','token'=>csrf_hash()]);
    } catch (\Throwable $e) {
        log_message('error','documentToggle error: {msg}', ['msg'=>$e->getMessage()]);
        return $this->response->setStatusCode(500)->setJSON([
            'status'=>false,'message'=>'Gagal mengubah status: '.$e->getMessage(),'token'=>csrf_hash()
        ]);
    }
}



  // ==== MENU LIST VIEW ====
    public function menu()
    {
        return view('admin/menu', [
            'title' => 'Menu Items',
        ]);
    }

    // ==== DATATABLES SOURCE ====
    public function menuList()
    {
        $rows = $this->db->table('menu_items mi')
            ->select('mi.id, mi.parent_id, p.title AS parent_title, mi.title, mi.icon, mi.url,
                      mi.is_external, mi.target_blank, mi.has_children, mi.sort, mi.is_active')
            ->join('menu_items p', 'p.id = mi.parent_id', 'left')
            ->orderBy('mi.parent_id', 'ASC')
            ->orderBy('mi.sort', 'ASC')
            ->get()->getResultArray();

        foreach ($rows as &$r) {
            $r['is_external']  = (int) $r['is_external'];
            $r['target_blank'] = (int) $r['target_blank'];
            $r['has_children'] = (int) $r['has_children'];
            $r['is_active']    = (int) $r['is_active'];
            $r['sort']         = (int) $r['sort'];
        }

        return $this->response->setJSON([
            'data'  => $rows,
            'token' => csrf_hash(),
        ]);
    }

    // ==== DROPDOWN PARENT ====
    public function menuParents()
    {
        $rows = $this->db->table('menu_items')
            ->select('id, title')
            ->where('is_active', 1)
            ->orderBy('parent_id', 'ASC')->orderBy('sort', 'ASC')
            ->get()->getResultArray();

        return $this->response->setJSON([
            'data'  => $rows,
            'token' => csrf_hash(),
        ]);
    }

    // ==== SAVE (INSERT & UPDATE) ====
    public function menuSave()
    {
        // Ambil data
        $id          = (int) ($this->request->getPost('id') ?? 0);
        $title       = trim((string) $this->request->getPost('title'));
        $parent_id   = $this->request->getPost('parent_id');
        $parent_id   = ($parent_id === '' || $parent_id === null) ? null : (int) $parent_id;
        $icon        = trim((string) $this->request->getPost('icon'));
        $url         = trim((string) $this->request->getPost('url'));
        $is_external = (int) $this->request->getPost('is_external');
        $target_blank= (int) $this->request->getPost('target_blank');
        $sort        = (int) $this->request->getPost('sort');
        $is_active   = (int) $this->request->getPost('is_active');

        if ($title === '') {
            return $this->response->setJSON(['status'=>false,'message'=>'Judul wajib diisi','token'=>csrf_hash()]);
        }
        if ($id && $parent_id === $id) {
            return $this->response->setJSON(['status'=>false,'message'=>'Parent tidak boleh diri sendiri','token'=>csrf_hash()]);
        }

        $now = date('Y-m-d H:i:s');
        $data = [
            'parent_id'    => $parent_id,
            'title'        => $title,
            'icon'         => $icon ?: null,
            'url'          => $url ?: null,
            'is_external'  => $is_external ? 1 : 0,
            'target_blank' => $target_blank ? 1 : 0,
            'sort'         => $sort,
            'is_active'    => $is_active ? 1 : 0,
            'updated_at'   => $now,
        ];

        $builder = $this->db->table('menu_items');

        // Untuk update parent lama nanti
        $oldParent = null;
        if ($id) {
            $old = $builder->select('parent_id')->where('id',$id)->get()->getRowArray();
            $oldParent = $old ? $old['parent_id'] : null;
        }

        if ($id) {
            $builder->where('id', $id)->update($data);
        } else {
            $data['created_at'] = $now;
            $builder->insert($data);
            $id = (int) $this->db->insertID();
        }

        // Update flag has_children pada parent yang baru
        if ($parent_id) {
            $cnt = $this->db->table('menu_items')->where('parent_id', $parent_id)->countAllResults();
            $this->db->table('menu_items')->where('id',$parent_id)->update([
                'has_children' => $cnt > 0 ? 1 : 0,
                'updated_at'   => $now
            ]);
        }
        // Bila pindah parent, evaluasi parent lama
        if ($oldParent && $oldParent != $parent_id) {
            $cntOld = $this->db->table('menu_items')->where('parent_id', $oldParent)->countAllResults();
            $this->db->table('menu_items')->where('id',$oldParent)->update([
                'has_children' => $cntOld > 0 ? 1 : 0,
                'updated_at'   => $now
            ]);
        }

        return $this->response->setJSON(['status'=>true,'message'=>'Tersimpan','token'=>csrf_hash()]);
    }

    // ==== DELETE ====
    public function menuDelete($id)
    {
        $id = (int)$id;
        $row = $this->db->table('menu_items')->where('id',$id)->get()->getRowArray();
        if (!$row) {
            return $this->response->setJSON(['status'=>false,'message'=>'Data tidak ditemukan','token'=>csrf_hash()]);
        }

        $this->db->table('menu_items')->where('id',$id)->delete(); // FK ON DELETE CASCADE akan menghapus anak

        // perbarui has_children parent kalau ada
        if (!empty($row['parent_id'])) {
            $now = date('Y-m-d H:i:s');
            $cnt = $this->db->table('menu_items')->where('parent_id', $row['parent_id'])->countAllResults();
            $this->db->table('menu_items')->where('id',$row['parent_id'])->update([
                'has_children' => $cnt > 0 ? 1 : 0,
                'updated_at'   => $now
            ]);
        }

        return $this->response->setJSON(['status'=>true,'message'=>'Terhapus','token'=>csrf_hash()]);
    }

    // ==== TOGGLE AKTIF ====
    public function menuToggle($id)
    {
        $id = (int)$id;
        $row = $this->db->table('menu_items')->select('id,is_active')->where('id',$id)->get()->getRowArray();
        if (!$row) {
            return $this->response->setJSON(['status'=>false,'message'=>'Data tidak ditemukan','token'=>csrf_hash()]);
        }
        $new = ((int)$row['is_active'] === 1) ? 0 : 1;
        $this->db->table('menu_items')->where('id',$id)->update([
            'is_active'  => $new,
            'updated_at' => date('Y-m-d H:i:s')
        ]);
        return $this->response->setJSON(['status'=>true,'message'=>'Status diperbarui','token'=>csrf_hash()]);
    }
    
    
    // ============ Complaints ============
public function complaints()
{
    return view('admin/complaints', [
        'title' => 'Complaints',
    ]);
}

public function complaintsList()
{
    $rows = $this->db->table('complaints')
        ->select('id, ticket, name, email, whatsapp, category, status, created_at, ip_addr, user_agent')
        ->orderBy('created_at', 'DESC')
        ->get()->getResultArray();

    return $this->response->setJSON([
        'data'  => $rows,
        'token' => csrf_hash(),
    ]);
}

public function complaintGet($id)
{
    $row = $this->db->table('complaints')->where('id', (int)$id)->get()->getRowArray();
    if (!$row) {
        return $this->response->setJSON(['status'=>false,'message'=>'Data tidak ditemukan','token'=>csrf_hash()]);
    }
    return $this->response->setJSON([
        'status'=>true,
        'data'  => $row,
        'token' => csrf_hash(),
    ]);
}

// Ubah kategori / status (resolved_at otomatis)
public function complaintUpdate($id)
{
    $id = (int)$id;
    $row = $this->db->table('complaints')->where('id', $id)->get()->getRowArray();
    if (!$row) {
        return $this->response->setJSON(['status'=>false,'message'=>'Data tidak ditemukan','token'=>csrf_hash()]);
    }

    $category = trim((string)$this->request->getPost('category'));
    $status   = trim((string)$this->request->getPost('status')); // new|in_progress|resolved|closed
    $now      = date('Y-m-d H:i:s');

    if (!in_array($status, ['new','in_progress','resolved','closed'], true)) {
        return $this->response->setJSON(['status'=>false,'message'=>'Status tidak valid','token'=>csrf_hash()]);
    }

    $data = [
        'category'   => $category ?: $row['category'],
        'status'     => $status,
        'updated_at' => $now,
    ];

    // atur resolved_at otomatis
    if ($status === 'resolved') {
        $data['resolved_at'] = $row['resolved_at'] ?: $now;
    } else {
        $data['resolved_at'] = null;
    }

    $this->db->table('complaints')->where('id', $id)->update($data);

    return $this->response->setJSON(['status'=>true,'message'=>'Perubahan disimpan','token'=>csrf_hash()]);
}

public function complaintDelete($id)
{
    $id = (int)$id;
    $row = $this->db->table('complaints')->where('id', $id)->get()->getRowArray();
    if (!$row) {
        return $this->response->setJSON(['status'=>false,'message'=>'Data tidak ditemukan','token'=>csrf_hash()]);
    }
    $this->db->table('complaints')->where('id', $id)->delete();

    return $this->response->setJSON(['status'=>true,'message'=>'Data dihapus','token'=>csrf_hash()]);
}


// ============ Subscribers ============
public function subscribers()
{
    return view('admin/subscribers', [
        'title' => 'Subscribers',
    ]);
}

public function subscribersList()
{
    $rows = $this->db->table('subscribers')
        ->select('id, email, verified, created_at')
        ->orderBy('created_at', 'DESC')
        ->get()->getResultArray();

    return $this->response->setJSON([
        'data'  => $rows,
        'token' => csrf_hash(),
    ]);
}

public function subscriberGet($id)
{
    $row = $this->db->table('subscribers')->where('id', (int)$id)->get()->getRowArray();
    if (!$row) {
        return $this->response->setJSON(['status'=>false, 'message'=>'Data tidak ditemukan', 'token'=>csrf_hash()]);
    }
    return $this->response->setJSON(['status'=>true, 'data'=>$row, 'token'=>csrf_hash()]);
}

/**
 * Create & Update disatukan:
 * - Jika POST[id] kosong -> INSERT
 * - Jika ada -> UPDATE
 * Validasi: email wajib format benar dan unik (kecuali id sama).
 */
public function subscriberSave()
{
    $id       = (int)$this->request->getPost('id');
    $email    = strtolower(trim((string)$this->request->getPost('email')));
    $verified = $this->request->getPost('verified') ? 1 : 0;

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return $this->response->setJSON(['status'=>false,'message'=>'Email tidak valid','token'=>csrf_hash()]);
    }

    // Cek duplikat email
    $builder = $this->db->table('subscribers');
    $builder->select('id')->where('email', $email);
    if ($id) $builder->where('id !=', $id);
    $dup = $builder->get()->getRowArray();
    if ($dup) {
        return $this->response->setJSON(['status'=>false,'message'=>'Email sudah terdaftar','token'=>csrf_hash()]);
    }

    $now = date('Y-m-d H:i:s');
    if (!$id) {
        // INSERT
        $this->db->table('subscribers')->insert([
            'email'      => $email,
            'verified'   => $verified,
            'created_at' => $now,
        ]);
        return $this->response->setJSON(['status'=>true,'message'=>'Subscriber ditambahkan','token'=>csrf_hash()]);
    } else {
        // UPDATE
        $row = $this->db->table('subscribers')->where('id', $id)->get()->getRowArray();
        if (!$row) {
            return $this->response->setJSON(['status'=>false,'message'=>'Data tidak ditemukan','token'=>csrf_hash()]);
        }
        $this->db->table('subscribers')->where('id', $id)->update([
            'email'    => $email,
            'verified' => $verified,
        ]);
        return $this->response->setJSON(['status'=>true,'message'=>'Subscriber diperbarui','token'=>csrf_hash()]);
    }
}

public function subscriberToggle($id)
{
    $id  = (int)$id;
    $row = $this->db->table('subscribers')->where('id', $id)->get()->getRowArray();
    if (!$row) {
        return $this->response->setJSON(['status'=>false,'message'=>'Data tidak ditemukan','token'=>csrf_hash()]);
    }
    $new = (int)!((int)$row['verified']);
    $this->db->table('subscribers')->where('id', $id)->update(['verified' => $new]);
    return $this->response->setJSON([
        'status'=>true,
        'message'=> $new ? 'Subscriber diverifikasi' : 'Verifikasi dibatalkan',
        'token'=>csrf_hash()
    ]);
}

public function subscriberDelete($id)
{
    $id  = (int)$id;
    $row = $this->db->table('subscribers')->where('id', $id)->get()->getRowArray();
    if (!$row) {
        return $this->response->setJSON(['status'=>false,'message'=>'Data tidak ditemukan','token'=>csrf_hash()]);
    }
    $this->db->table('subscribers')->where('id', $id)->delete();
    return $this->response->setJSON(['status'=>true,'message'=>'Data dihapus','token'=>csrf_hash()]);
}



// app/Controllers/Admin/Dashboard.php (tambahkan methods berikut)
public function pages()
{
    // dropdown menu yang aktif (opsional)
    $menus = $this->db->table('menu_items')
        ->select('id, title, url')
        ->where('is_active', 1)
        ->orderBy('parent_id','ASC')->orderBy('sort','ASC')
        ->get()->getResultArray();

    return view('admin/pages', [
        'title' => 'Pages',
        'menus' => $menus,
    ]);
}

public function pagesList()
{
    $rows = $this->db->table('pages p')
        ->select('p.id,p.menu_item_id,p.title,p.slug,p.excerpt,p.status,p.published_at,p.updated_at,p.cover_image,m.title as menu_title')
        ->join('menu_items m','m.id=p.menu_item_id','left')
        ->orderBy('p.updated_at','DESC')
        ->get()->getResultArray();

    return $this->response->setJSON([
        'data'  => $rows,
        'token' => csrf_hash(),
    ]);
}

public function pageSave()
{
    helper('text');

    $id    = (int) $this->request->getPost('id');
    $title = trim((string)$this->request->getPost('title'));
    if ($title === '') {
        return $this->response->setJSON(['status'=>false,'message'=>'Judul wajib diisi','token'=>csrf_hash()]);
    }

    // slug
    $slug = trim((string)$this->request->getPost('slug'));
    if ($slug === '') {
        $slug = url_title($title, '-', true);
    }
    // slug unik
    $slugQ = $this->db->table('pages')->where('slug',$slug);
    if ($id) $slugQ->where('id !=', $id);
    if ($slugQ->get()->getRowArray()) {
        return $this->response->setJSON(['status'=>false,'message'=>'Slug sudah digunakan','token'=>csrf_hash()]);
    }

    // -------- PATH RESOLVER --------
    $menuItemId = (int) ($this->request->getPost('menu_item_id') ?: 0);
    $pathInput  = trim((string)$this->request->getPost('path'));         // opsional input path manual
    $parentPath = trim((string)$this->request->getPost('parent_path'));  // opsional, contoh "/wni"

    $path   = '';
    $miUrl  = null;

    // 1) Kalau pilih menu item → pakai url di menu_items (harus exist)
    if ($menuItemId) {
        $mi = $this->db->table('menu_items')->select('id,url')->where('id',$menuItemId)->get()->getRowArray();
        if (!$mi) { // ✅ validasi menu exists
            return $this->response->setJSON(['status'=>false,'message'=>'Menu tidak ditemukan','token'=>csrf_hash()]);
        }
        $miUrl = (string) ($mi['url'] ?? '');
        if ($miUrl !== '') {
            // ✅ normalisasi leading slash + kompres double slash
            if ($miUrl[0] !== '/') $miUrl = '/'.ltrim($miUrl,'/');
            $miUrl = preg_replace('#/{2,}#', '/', $miUrl);
            $path  = $miUrl;
        }
    }

    // 2) Kalau admin isi path manual → pakai itu (akan override pilihan menu)
    if ($path === '' && $pathInput !== '') {
        $path = $pathInput;
    }

    // 3) Jika masih kosong → bentuk dari parent_path + slug (fallback)
    if ($path === '') {
        $path = rtrim('/' . ltrim(trim($parentPath,'/') . '/' . trim($slug,'/'), '/'), '/');
        if ($path === '') $path = '/' . $slug; // fallback terakhir
    }

    // ✅ Normalisasi path final
    $path = '/'.ltrim($path, '/');
    $path = preg_replace('#/{2,}#', '/', $path);

    // ✅ path unik
    // $pathQ = $this->db->table('pages')->where('path',$path);
    // if ($id) $pathQ->where('id !=', $id);
    // if ($pathQ->get()->getRowArray()) {
    //     return $this->response->setJSON(['status'=>false,'message'=>'Path URL sudah digunakan','token'=>csrf_hash()]);
    // }
    // -------- END PATH RESOLVER --------

    // ✅ status: dukung 'published' / 1
    $rawStatus = $this->request->getPost('status');
    $status    = ($rawStatus === 'published' || $rawStatus === '1' || $rawStatus === 1) ? 'published' : 'draft';

    $data = [
        'menu_item_id'    => $menuItemId ?: null,
        'title'           => $title,
        'slug'            => $slug,
        'path'            => $path, // simpan path final
        'excerpt'         => (string)$this->request->getPost('excerpt'),
        'content_html'    => (string)$this->request->getPost('content_html'),
        'seo_title'       => (string)$this->request->getPost('seo_title') ?: null,
        'seo_description' => (string)$this->request->getPost('seo_description') ?: null,
        'og_image'        => (string)$this->request->getPost('og_image') ?: null,
        'status'          => $status,
    ];

    // published_at
    $pub = trim((string)$this->request->getPost('published_at'));
    $data['published_at'] = $pub ? date('Y-m-d H:i:s', strtotime($pub)) : null;
    if ($data['status']==='published' && !$data['published_at']) {
        $data['published_at'] = date('Y-m-d H:i:s');
    }

    // Upload cover (opsional)
    $file = $this->request->getFile('cover');
    if ($file && $file->isValid() && !$file->hasMoved()) {
        $ext = strtolower($file->getExtension());
        if (! in_array($ext, ['jpg','jpeg','png','webp'])) {
            return $this->response->setJSON(['status'=>false,'message'=>'Cover harus JPG/PNG/WEBP','token'=>csrf_hash()]);
        }
        if ($file->getSize() > 2*1024*1024) {
            return $this->response->setJSON(['status'=>false,'message'=>'Ukuran cover maks 2MB','token'=>csrf_hash()]);
        }
        $uploadDir = FCPATH.'uploads/pages';
        if (!is_dir($uploadDir)) { @mkdir($uploadDir, 0775, true); }
        $newName = 'cover_'.time().'_'.bin2hex(random_bytes(4)).'.'.$ext;
        $file->move($uploadDir, $newName);
        $data['cover_image'] = base_url('uploads/pages/'.$newName);
    }

    $now = date('Y-m-d H:i:s');
    if ($id) {
        $data['updated_at'] = $now;
        $this->db->table('pages')->where('id',$id)->update($data);
    } else {
        $data['created_at'] = $now;
        $data['updated_at'] = $now;
        $this->db->table('pages')->insert($data);
        $id = (int) $this->db->insertID();
    }

    // ✅ OPSIONAL: sinkronkan menu_items.url jika admin override path manual
    // (aktifkan bila kamu ingin URL menu ikut berubah mengikuti path page)
    // if ($menuItemId && $miUrl !== null && $path !== $miUrl) {
    //     // Pastikan url menu tidak bentrok
    //     $mq = $this->db->table('menu_items')->where('url', $path)->where('id !=', $menuItemId);
    //     if (! $mq->get()->getRowArray()) {
    //         $this->db->table('menu_items')->where('id', $menuItemId)->update([
    //             'url'        => $path,
    //             'updated_at' => $now
    //         ]);
    //     }
    // }

    return $this->response->setJSON([
        'status'=>true,
        'id'=>$id,
        'path'=>$path,   // ✅ kirim balik path biar gampang dicek di UI
        'message'=>'Halaman tersimpan',
        'token'=>csrf_hash()
    ]);
}


public function pageToggle($id)
{
    $row = $this->db->table('pages')->where('id',$id)->get()->getRowArray();
    if (!$row) return $this->response->setJSON(['status'=>false,'message'=>'Data tidak ditemukan','token'=>csrf_hash()]);

    $new = $row['status']==='published' ? 'draft' : 'published';
    $up  = ['status'=>$new, 'updated_at'=>date('Y-m-d H:i:s')];
    if ($new==='published' && empty($row['published_at'])) $up['published_at'] = date('Y-m-d H:i:s');

    $this->db->table('pages')->where('id',$id)->update($up);
    return $this->response->setJSON(['status'=>true,'message'=>'Status diubah','token'=>csrf_hash()]);
}

public function pageDelete($id)
{
    $this->db->table('pages')->where('id',$id)->delete();
    return $this->response->setJSON(['status'=>true,'message'=>'Data dihapus','token'=>csrf_hash()]);
}



    
    /* ==============================
     * UTIL: SLUG
     * ============================== */
    private function slugify(string $str): string
    {
        $str = mb_strtolower($str);
        $str = preg_replace('~[^a-z0-9]+~','-', $str);
        $str = trim($str, '-');
        return $str ?: 'item';
    }

    private function uniqueSlug(string $slug, ?int $ignoreId=null): string
    {
        $base = $slug; $i = 2;
        while (true) {
            $b = $this->db->table('services')->where('slug', $slug);
            if ($ignoreId) $b->where('id !=', $ignoreId);
            $found = $b->get()->getRowArray();
            if (!$found) return $slug;
            $slug = $base.'-'.$i++;
        }
    }
     private function uniquePostSlug(string $slug, ?int $ignoreId=null): string
    {
        $base = $slug; $i = 2;
        while (true) {
            $b = $this->db->table('posts')->where('slug',$slug);
            if ($ignoreId) $b->where('id !=',$ignoreId);
            $found = $b->get()->getRowArray();
            if (!$found) return $slug;
            $slug = $base.'-'.$i++;
        }
    }
}
