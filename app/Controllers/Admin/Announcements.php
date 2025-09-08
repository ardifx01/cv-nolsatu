<?php namespace App\Controllers\Admin;

class Announcements extends AdminBaseController
{
    public function index()
    {
        $rows = $this->db->table('announcements')
            ->select('*')
            ->orderBy('priority','DESC')->orderBy('id','DESC')
            ->get()->getResultArray();

        return view('admin/announcements/index', [
            'title' => 'Announcements',
            'rows'  => $rows
        ]);
    }

 public function list()
    {
        $rows = $this->db->table('announcements')
            ->select('id, text, url, is_active, start_at, end_at, priority, created_at, updated_at')
            ->orderBy('priority','DESC')
            ->orderBy('created_at','DESC')
            ->get()->getResultArray();

        return $this->response->setJSON([
            'data'  => $rows,
            'token' => csrf_hash(),
        ]);
    }

    public function get($id)
    {
        $row = $this->db->table('announcements')->where('id', (int)$id)->get()->getRowArray();
        if (!$row) {
            return $this->response->setJSON(['status'=>false,'message'=>'Data tidak ditemukan','token'=>csrf_hash()]);
        }
        return $this->response->setJSON(['status'=>true,'data'=>$row,'token'=>csrf_hash()]);
    }

    public function save()
    {
        $id        = (int) ($this->request->getPost('id') ?? 0);
        $text      = trim((string)$this->request->getPost('text'));
        $url       = trim((string)$this->request->getPost('url'));
        $priority  = (int) ($this->request->getPost('priority') ?? 0);
        $isActive  = $this->request->getPost('is_active') ? 1 : 0;

        // datetime-local -> 'Y-m-d H:i:s'
        $startAtIn = trim((string)$this->request->getPost('start_at'));
        $endAtIn   = trim((string)$this->request->getPost('end_at'));
        $startAt   = $startAtIn ? str_replace('T', ' ', $startAtIn) . (strlen($startAtIn)===16?':00':'') : null;
        $endAt     = $endAtIn   ? str_replace('T', ' ', $endAtIn)   . (strlen($endAtIn)===16?':00':'') : null;

        // Validasi sederhana
        $errors = [];
        if ($text === '') $errors[] = 'Teks pengumuman wajib diisi.';
        if ($url !== '' && ! filter_var($url, FILTER_VALIDATE_URL)) $errors[] = 'URL tidak valid.';
        if ($startAt && $endAt && strcmp($endAt, $startAt) < 0) $errors[] = 'End date harus >= Start date.';

        if ($errors) {
            return $this->response->setJSON(['status'=>false, 'message'=>implode("\n", $errors), 'token'=>csrf_hash()]);
        }

        $data = [
            'text'       => $text,
            'url'        => $url ?: null,
            'is_active'  => $isActive,
            'start_at'   => $startAt,
            'end_at'     => $endAt,
            'priority'   => $priority,
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        if ($id) {
            $ok = $this->db->table('announcements')->where('id', $id)->update($data);
            $msg = $ok ? 'Pengumuman diperbarui.' : 'Gagal memperbarui data.';
            return $this->response->setJSON(['status'=>$ok, 'message'=>$msg, 'token'=>csrf_hash()]);
        } else {
            $data['created_at'] = date('Y-m-d H:i:s');
            $ok = $this->db->table('announcements')->insert($data);
            $msg = $ok ? 'Pengumuman ditambahkan.' : 'Gagal menambah data.';
            return $this->response->setJSON(['status'=>$ok, 'message'=>$msg, 'token'=>csrf_hash()]);
        }
    }

    public function toggle($id)
    {
        $row = $this->db->table('announcements')->select('id,is_active')->where('id',(int)$id)->get()->getRowArray();
        if (!$row) {
            return $this->response->setJSON(['status'=>false,'message'=>'Data tidak ditemukan','token'=>csrf_hash()]);
        }
        $new = $row['is_active'] ? 0 : 1;
        $ok  = $this->db->table('announcements')->where('id',$row['id'])->update([
            'is_active'  => $new,
            'updated_at' => date('Y-m-d H:i:s')
        ]);
        return $this->response->setJSON([
            'status'=>$ok,
            'message'=>$ok? 'Status diperbarui.' : 'Gagal memperbarui status.',
            'token'=>csrf_hash()
        ]);
    }

    public function delete($id)
    {
        $ok = $this->db->table('announcements')->where('id',(int)$id)->delete();
        return $this->response->setJSON([
            'status'=>$ok,
            'message'=>$ok? 'Data dihapus.' : 'Gagal menghapus data.',
            'token'=>csrf_hash()
        ]);
    }
}
