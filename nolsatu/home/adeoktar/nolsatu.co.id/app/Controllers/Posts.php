<?php
namespace App\Controllers;

use CodeIgniter\Controller;
use Config\Database;
use CodeIgniter\Exceptions\PageNotFoundException;

class Posts extends Controller
{
    protected $db;
    public function __construct() { $this->db = Database::connect(); }

    // /berita?type=news|press|tips
    public function index()
    {
        $type = $this->request->getGet('type');
        $allowed = ['news','press','tips'];
        $builder = $this->db->table('posts')
            ->select('id,title,slug,excerpt,thumbnail,type,published_at')
            ->where('status','published');

        if ($type && in_array($type, $allowed, true)) {
            $builder->where('type', $type);
        }

        $posts = $builder->orderBy('published_at','DESC')->limit(12)->get()->getResultArray();

        // Data global untuk layout
        $ann      = $this->getAnnouncements();
        $menuHtml = $this->getSidebarHtml();

        return view('posts/index', [
            'posts'    => $posts,
            'activeType' => $type ?: 'all',
            'ann'      => $ann,
            'menuHtml' => $menuHtml,
        ]);
    }

    // /berita/{slug}
    public function detail(string $slug)
    {
        $post = $this->db->table('posts')
            ->select('id,title,slug,excerpt,content,type,thumbnail,published_at')
            ->where(['slug'=>$slug, 'status'=>'published'])
            ->get()->getRowArray();

        if (!$post) {
            throw PageNotFoundException::forPageNotFound('Berita tidak ditemukan');
        }

        // (opsional) related posts
        $related = $this->db->table('posts')
            ->select('title,slug,thumbnail,type,published_at')
            ->where('status','published')
            ->where('id !=', $post['id'])
            ->orderBy('published_at','DESC')->limit(6)
            ->get()->getResultArray();

        $ann      = $this->getAnnouncements();
        $menuHtml = $this->getSidebarHtml();

        return view('posts/detail', [
            'post'     => $post,
            'related'  => $related,
            'ann'      => $ann,
            'menuHtml' => $menuHtml,
        ]);
    }

    /* ===== Helpers layout (copy dari Home/Page agar seragam) ===== */

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
            ->where('is_active',1)
            ->orderBy('parent_id','ASC')->orderBy('sort','ASC')
            ->get()->getResultArray();

        return $this->renderSidebar($rows);
    }

    private function renderSidebar(array $rows): string
    {
        $tree = [];
        foreach ($rows as $r) { $tree[$r['parent_id'] ?? 0][] = $r; }

        $render = function($parentId) use (&$render, $tree): string {
            if (!isset($tree[$parentId])) return '';
            $html = '';
            foreach ($tree[$parentId] as $item) {
                $hasChild = !empty($item['has_children']) && isset($tree[$item['id']]);
                $icon  = $item['icon'] ? esc($item['icon']) : 'bi-list';
                $title = esc($item['title']);

                if ($hasChild) {
                    $cid  = 'm'.$item['id'];
                    $html .= '<li class="nav-item">';
                    $html .= '<button class="nav-link collapsed px-3 py-2 d-flex align-items-center w-100 text-start" '
                          .'data-bs-toggle="collapse" data-bs-target="#'.$cid.'" aria-expanded="false" type="button">'
                          .'<i class="bi '.$icon.' me-2"></i><span>'.$title.'</span>'
                          .'<i class="bi bi-chevron-down ms-auto toggle-icon"></i></button>';
                    $html .= '<div class="collapse" id="'.$cid.'"><ul class="nav-content list-unstyled ps-4">'
                          .  $render($item['id'])
                          .  '</ul></div></li>';
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
