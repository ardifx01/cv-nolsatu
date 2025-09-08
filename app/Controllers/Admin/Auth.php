<?php namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class Auth extends BaseController
{
    protected $db;

    public function __construct()
    {
        helper(['url','form']);
        $this->db = \Config\Database::connect();
    }

    public function login()
    {
        if (session('admin_id')) return redirect()->to(site_url('admin'));
        return view('admin/auth/login');
    }

    public function doLogin()
    {
        $email = trim($this->request->getPost('email'));
        $pass  = (string)$this->request->getPost('password');

        $user = $this->db->table('admin_users')
            ->where('email', $email)
            ->where('is_active', 1)
            ->get()->getRowArray();

        if (!$user || !password_verify($pass, $user['password_hash'])) {
            return redirect()->back()->with('error','Email atau password salah.');
        }

        session()->set([
            'admin_id'       => $user['id'],
            'admin_username' => $user['username'],
            'admin_role'     => $user['role'],
            'is_admin'       => true,
        ]);

        $this->db->table('admin_users')->where('id',$user['id'])->update([
            'last_login' => date('Y-m-d H:i:s')
        ]);

        $redir = session()->getFlashdata('redirect') ?: site_url('admin');
        return redirect()->to($redir);
    }

    public function logout()
    {
        session()->remove(['admin_id','admin_username','admin_role','is_admin']);
        session()->destroy();
        return redirect()->to(site_url('admin/login'))->with('success','Anda telah keluar.');
    }
}
