<?php
namespace App\Controllers;

use CodeIgniter\Controller;
use Config\Database;

class Home extends Controller
{
    protected $db;

    public function __construct()
    {
        $this->db = Database::connect();
    }

    public function index()
    {
        $now = date('Y-m-d H:i:s');

        // Announcements aktif dalam window waktu
       $ann = $this->db->table('announcements')
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


        // Services aktif
        $services = $this->db->table('services')
            ->select('title, slug, description, icon, url')
            ->where('is_active', 1)->orderBy('sort', 'ASC')->limit(12)
            ->get()->getResultArray();

        // Posts published terbaru (news/press/tips)
        $posts = $this->db->table('posts')
            ->select('title, slug, excerpt, thumbnail, type, published_at')
            ->where('status', 'published')->orderBy('published_at', 'DESC')->limit(6)
            ->get()->getResultArray();

        // FAQ aktif
        $faqs = $this->db->table('faqs')
            ->select('id, question, answer, category')
            ->where('is_active', 1)->orderBy('sort', 'ASC')->limit(10)
            ->get()->getResultArray();

        // Dokumen aktif
        $docs = $this->db->table('documents')
            ->select('id, title, file_url, type')
            ->where('is_active', 1)->orderBy('sort', 'ASC')->limit(10)
            ->get()->getResultArray();

        // Menu items aktif → build tree → render HTML siap tempel
        $menuItems = $this->db->table('menu_items')
            ->select('id, parent_id, title, icon, url, is_external, target_blank, has_children, sort')
            ->where('is_active', 1)->orderBy('parent_id', 'ASC')->orderBy('sort', 'ASC')
            ->get()->getResultArray();

        $menuHtml = $this->renderSidebar($menuItems);

        return view('home', [
            'ann'      => $ann,
            'services' => $services,
            'posts'    => $posts,
            'faqs'     => $faqs,
            'docs'     => $docs,
            'menuHtml' => $menuHtml,
        ]);
    }

    /** Build menu tree (parent→children) lalu render <li>… */
    private function renderSidebar(array $rows): string
    {
        // index by parent
        $tree = [];
        foreach ($rows as $r) {
            $pid = $r['parent_id'] ?? 0;
            $tree[$pid][] = $r;
        }

        // recursive renderer
        $render = function($parentId) use (&$render, $tree): string {
            if (!isset($tree[$parentId])) return '';
            $html = '';
            foreach ($tree[$parentId] as $item) {
                $hasChild = !empty($item['has_children']) && isset($tree[$item['id']]);
                $icon = $item['icon'] ? esc($item['icon']) : 'bi-list';
                $title = esc($item['title']);

                if ($hasChild) {
                    $collapseId = 'm'. $item['id'];
                    $html .= '<li class="nav-item">';
                    $html .= '<button class="nav-link collapsed px-3 py-2 d-flex align-items-center w-100 text-start" '
                         . 'data-bs-toggle="collapse" data-bs-target="#'.$collapseId.'" aria-expanded="false" type="button">'
                         . '<i class="bi '. $icon .' me-2"></i><span>'. $title .'</span>'
                         . '<i class="bi bi-chevron-down ms-auto toggle-icon"></i>'
                         . '</button>';
                    $html .= '<div class="collapse" id="'.$collapseId.'"><ul class="nav-content list-unstyled ps-4">';
                    $html .= $render($item['id']);
                    $html .= '</ul></div></li>';
                } else {
                    // leaf
                    $url = $item['url'] ?? '#';
                    $target = (!empty($item['target_blank'])) ? ' target="_blank" rel="noopener"' : '';
                    $html .= '<li><a href="'. esc($url) .'" class="nav-link text-decoration-none px-3 py-2 d-flex align-items-center"'.$target.'>'
                          . '<i class="bi '. $icon .' me-2"></i><span>'. $title .'</span></a></li>';
                }
            }
            return $html;
        };

        return '<ul class="sidebar-nav list-unstyled" id="sidebar-nav">' . $render(0) . '</ul>';
    }
}
