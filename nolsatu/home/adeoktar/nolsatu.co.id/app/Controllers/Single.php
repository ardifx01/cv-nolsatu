<?php
namespace App\Controllers;

use CodeIgniter\Controller;
use Config\Database;
use CodeIgniter\Exceptions\PageNotFoundException;

class Single extends Controller
{
    protected $db;
    public function __construct() { $this->db = Database::connect(); }

    // /{slug} -> cocok dengan menu_items.url = '/{slug}'
   public function show(string $slug)
{
    $path = '/' . trim($slug, '/');

    // 1) Cari menu aktif berdasarkan URL persis
    $menu = $this->db->table('menu_items')
        ->select('id, title, icon, url, parent_id')
        ->where(['is_active' => 1, 'url' => $path])
        ->get()->getRowArray();

    if (!$menu) {
        // (opsional) normalisasi trailing slash
        $alt = rtrim($path, '/');
        if ($alt !== '' && $alt !== $path) {
            $menu = $this->db->table('menu_items')
                ->select('id, title, icon, url, parent_id')
                ->where(['is_active' => 1, 'url' => $alt])
                ->get()->getRowArray();
        }
    }

    if (!$menu) {
        throw PageNotFoundException::forPageNotFound('Halaman tidak ditemukan: ' . $path);
    }

    // 2) Parent untuk breadcrumb
    $parent = null;
    if (!empty($menu['parent_id'])) {
        $parent = $this->db->table('menu_items')
            ->select('id, title, url')
            ->where('id', $menu['parent_id'])
            ->get()->getRowArray();
    }

    // 3) AMBIL PAGES terkait menu ini (1 menu_items bisa banyak pages)
    $pages = $this->db->table('pages')
        ->select('id, menu_item_id, title, slug, path, excerpt, content_html, cover_image, seo_title, seo_description, og_image, status, published_at, created_at')
        ->where('menu_item_id', (int)$menu['id'])
        ->where('status', 'published') // sesuaikan jika status berbeda
        ->orderBy('COALESCE(published_at, created_at)', 'DESC', false)
        ->get()->getResultArray();

    // 4) (opsional) ambil FAQ & dokumen terkait
    $faqs = $this->db->table('faqs')
        ->select('question, answer')
        ->where('is_active', 1)
        ->like('category', $menu['title'], 'both')
        ->orderBy('sort', 'ASC')->limit(10)
        ->get()->getResultArray();

    $docs = $this->db->table('documents')
        ->select('title, file_url, type')
        ->where('is_active', 1)
        ->like('title', $menu['title'], 'both')
        ->orderBy('sort', 'ASC')->limit(10)
        ->get()->getResultArray();

    // 5) Data global layout (opsional)
    $ann      = $this->getAnnouncements();
    $menuHtml = $this->getSidebarHtml();

    // 6) Render view generik yang extend layout
    return view('pages/generic', [
        'menu'     => $menu,
        'parent'   => $parent,
        'faqs'     => $faqs,
        'docs'     => $docs,
        'pages'    => $pages,   // ← dikirim ke view
        'path'     => $path,
        'ann'      => $ann,
        'menuHtml' => $menuHtml,
    ]);
}

    private function getAnnouncements(): array
    {
        $now = date('Y-m-d H:i:s');
        return $this->db->table('announcements')
    ->select('text, url')
    ->where('is_active', 1)
    ->groupStart()
        ->where('start_at <=', 'NOW()', false)   // gunakan waktu DB
        ->orWhere('start_at IS NULL', null, false)
    ->groupEnd()
    ->groupStart()
        ->where('end_at >=', 'NOW()', false)
        ->orWhere('end_at IS NULL', null, false)
    ->groupEnd()
    ->orderBy('priority', 'DESC')
    ->orderBy('id', 'DESC')
    ->limit(10)
    ->get()->getResultArray();
    }

    private function getSidebarHtml(): string
    {
        $rows = $this->db->table('menu_items')
            ->select('id,parent_id,title,icon,url,is_external,target_blank,has_children,sort')
            ->where('is_active',1)->orderBy('parent_id','ASC')->orderBy('sort','ASC')
            ->get()->getResultArray();

        // (render sama dengan di Posts/Page)
        $tree = [];
        foreach ($rows as $r) { $tree[$r['parent_id'] ?? 0][] = $r; }
        $render = function($pid) use (&$render, $tree): string {
            if (!isset($tree[$pid])) return '';
            $html = '';
            foreach ($tree[$pid] as $item) {
                $hasChild = !empty($item['has_children']) && isset($tree[$item['id']]);
                $icon  = $item['icon'] ? esc($item['icon']) : 'bi-list';
                $title = esc($item['title']);
                if ($hasChild) {
                    $cid = 'm'.$item['id'];
                    $html .= '<li class="nav-item">'
                          .  '<button class="nav-link collapsed px-3 py-2 d-flex align-items-center w-100 text-start" '
                          .  'data-bs-toggle="collapse" data-bs-target="#'.$cid.'" aria-expanded="false" type="button">'
                          .  '<i class="bi '.$icon.' me-2"></i><span>'.$title.'</span>'
                          .  '<i class="bi bi-chevron-down ms-auto toggle-icon"></i></button>'
                          .  '<div class="collapse" id="'.$cid.'"><ul class="nav-content list-unstyled ps-4">'
                          .  $render($item['id']).'</ul></div></li>';
                } else {
                    $url = $item['url'] ?? '#';
                    $target = !empty($item['target_blank']) ? ' target="_blank" rel="noopener"' : '';
                    $html .= '<li><a href="'.esc($url).'" class="nav-link text-decoration-none px-3 py-2 d-flex align-items-center"'.$target.'>'
                          .  '<i class="bi '.$icon.' me-2"></i><span>'.$title.'</span></a></li>';
                }
            }
            return $html;
        };
        return '<ul class="sidebar-nav list-unstyled" id="sidebar-nav">'.$render(0).'</ul>';
    }
}
