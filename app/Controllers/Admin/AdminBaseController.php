<?php namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class AdminBaseController extends BaseController
{
    protected $db;
    protected $admin;

    public function initController(\CodeIgniter\HTTP\RequestInterface $request, \CodeIgniter\HTTP\ResponseInterface $response, \Psr\Log\LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);
        helper(['url','form','settings']);
        $this->db = \Config\Database::connect();
        $this->admin = [
            'id'       => session('admin_id'),
            'username' => session('admin_username'),
            'role'     => session('admin_role'),
        ];
    }

    protected function logActivity(string $action, ?string $entity=null, ?string $entityId=null, ?array $detail=null)
    {
        $this->db->table('admin_activity_log')->insert([
            'admin_id'  => $this->admin['id'],
            'action'    => $action,
            'entity'    => $entity,
            'entity_id' => $entityId,
            'detail'    => $detail ? json_encode($detail) : null,
            'ip_addr'   => $this->request->getIPAddress(),
            'user_agent'=> (string)$this->request->getUserAgent(),
            'created_at'=> date('Y-m-d H:i:s'),
        ]);
    }
}
