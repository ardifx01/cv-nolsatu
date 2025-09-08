<?php
namespace App\Controllers;

use CodeIgniter\Controller;
use Config\Database;

class Action extends Controller
{
    protected $db;

    public function __construct()
    {
        $this->db = Database::connect();
        helper(['url','form','security']); // form+security untuk csrf_*()
    }

    /**
     * Terima form pengaduan via AJAX, validasi, simpan ke tabel `complaints`,
     * balas JSON + kirim token CSRF baru.
     */
    public function pengaduan()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(405)->setJSON([
                'ok' => false,
                'msg' => 'Metode tidak diizinkan. Gunakan AJAX (XMLHttpRequest).',
                'csrf' => ['name' => csrf_token(), 'hash' => csrf_hash()],
            ]);
        }

        // Ambil input
        $name     = trim((string)$this->request->getPost('name'));
        $email    = trim((string)$this->request->getPost('email'));
        $whatsapp = preg_replace('/\s+/', '', (string)$this->request->getPost('whatsapp'));
        $category = trim((string)$this->request->getPost('category'));
        $message  = trim((string)$this->request->getPost('message'));

        // Validasi sederhana
        $validation = \Config\Services::validation();
        $validation->setRules([
            'name'     => 'required|min_length[3]|max_length[120]',
            'email'    => 'required|valid_email|max_length[150]',
            'whatsapp' => 'required|min_length[8]|max_length[30]',
            'category' => 'required|max_length[80]',
            'message'  => 'required|min_length[10]',
        ]);

        if (!$validation->withRequest($this->request)->run()) {
            return $this->jsonError(
                array_values($validation->getErrors()),
                422
            );
        }

        // (Opsional) throttle anti-spam 1 menit per email/IP
        $ip = $this->request->getIPAddress();
        $recent = $this->db->table('complaints')
            ->select('id')->where('email', $email)
            ->where('created_at >=', date('Y-m-d H:i:s', time()-60))
            ->get()->getRowArray();
        if ($recent) {
            return $this->jsonError(
                ['Terlalu sering. Coba lagi dalam beberapa saat.'],
                429
            );
        }

        // Buat tiket
        $ticket = 'IMJ-'.date('ymd').'-'.strtoupper(bin2hex(random_bytes(3)));

        // Simpan
        try {
            $this->db->table('complaints')->insert([
                'ticket'     => $ticket,
                'name'       => $name,
                'email'      => $email,
                'whatsapp'   => $whatsapp,
                'category'   => $category,
                'message'    => $message,
                'status'     => 'new',
                'ip_addr'    => $ip,
                'user_agent' => substr((string)$this->request->getUserAgent(), 0, 255),
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        } catch (\Throwable $e) {
            return $this->jsonError(['Gagal menyimpan data.'], 500);
        }

        // Berhasil
        return $this->response->setJSON([
            'ok'     => true,
            'ticket' => $ticket,
            'msg'    => 'Pengaduan terkirim. Nomor tiket: '.$ticket,
            // kirim token baru agar form berikutnya tetap valid
            'csrf'   => ['name' => csrf_token(), 'hash' => csrf_hash()],
        ]);
    }


 public function subscribe()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(405)->setJSON([
                'ok' => false,
                'msg' => 'Metode tidak diizinkan. Gunakan AJAX.',
                'csrf' => ['name' => csrf_token(), 'hash' => csrf_hash()],
            ]);
        }

        $email = strtolower(trim((string)$this->request->getPost('email')));

        // Validasi
        $validation = \Config\Services::validation();
        $validation->setRules([
            'email' => 'required|valid_email|max_length[150]',
        ]);
        if (!$validation->withRequest($this->request)->run()) {
            return $this->jsonError(array_values($validation->getErrors()), 422);
        }

        // Cek apakah sudah ada
        $row = $this->db->table('subscribers')
            ->select('id, verified')
            ->where('email', $email)
            ->get()->getRowArray();

        try {
            if ($row) {
                // Sudah terdaftar
                if ((int)$row['verified'] === 1) {
                    return $this->response->setJSON([
                        'ok'   => true,
                        'msg'  => 'Email sudah terdaftar dalam buletin.',
                        'csrf' => ['name' => csrf_token(), 'hash' => csrf_hash()],
                    ]);
                }
                // Belum verifikasi (kalau suatu saat pakai mekanisme verifikasi)
                return $this->response->setJSON([
                    'ok'   => true,
                    'msg'  => 'Email sudah terdaftar (menunggu verifikasi).',
                    'csrf' => ['name' => csrf_token(), 'hash' => csrf_hash()],
                ]);
            }

            // Insert baru
            $this->db->table('subscribers')->insert([
                'email'      => $email,
                'verified'   => 0, // default
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        } catch (\Throwable $e) {
            // Antisipasi unique key race
            return $this->jsonError(['Gagal mendaftarkan email.'], 500);
        }

        return $this->response->setJSON([
            'ok'   => true,
            'msg'  => 'Berhasil mendaftar buletin. Terima kasih!',
            'csrf' => ['name' => csrf_token(), 'hash' => csrf_hash()],
        ]);
    }



    private function jsonError(array $errors, int $code = 400)
    {
        return $this->response->setStatusCode($code)->setJSON([
            'ok'     => false,
            'errors' => $errors,
            'csrf'   => ['name' => csrf_token(), 'hash' => csrf_hash()],
        ]);
    }
}
