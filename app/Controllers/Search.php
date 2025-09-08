<?php
namespace App\Controllers;

use CodeIgniter\Controller;
use Config\Database;

class Search extends Controller
{
    protected $db;
    public function __construct(){ $this->db = Database::connect(); }

    public function index()
    {
        $q     = trim((string)$this->request->getGet('q'));
        $type  = strtolower((string)$this->request->getGet('type'));
        $types = ['all','service','post','faq','document'];
        if (!in_array($type, $types, true)) $type = 'all';

        $results = [];
        if ($q !== '') {
            $like = '%'.$this->db->escapeLikeString($q).'%';

            if ($type === 'all' || $type === 'service') {
                // SERVICES
                $sql = "
                SELECT 'service' AS source, id,
                       title,
                       COALESCE(description,'') AS snippet,
                       url,
                       NULL AS published_at,
                       (CASE WHEN title LIKE ? THEN 10 ELSE 0 END) +
                       (CASE WHEN description LIKE ? THEN 4 ELSE 0 END) AS score
                FROM services
                WHERE is_active=1 AND (title LIKE ? OR description LIKE ?)
                ";
                $res = $this->db->query($sql, [$like,$like,$like,$like])->getResultArray();
                foreach ($res as $r) { $results[] = $r; }
            }

            if ($type === 'all' || $type === 'post') {
                // POSTS (berita/siaran pers/tips)
                $sql = "
                SELECT 'post' AS source, id,
                       title,
                       COALESCE(excerpt,'') AS snippet,
                       CONCAT('/berita/', slug) AS url,
                       published_at,
                       (CASE WHEN title LIKE ? THEN 12 ELSE 0 END) +
                       (CASE WHEN excerpt LIKE ? THEN 5 ELSE 0 END) +
                       (CASE WHEN content LIKE ? THEN 3 ELSE 0 END) AS score
                FROM posts
                WHERE status='published'
                  AND (title LIKE ? OR excerpt LIKE ? OR content LIKE ?)
                ";
                $res = $this->db->query($sql, [$like,$like,$like,$like,$like,$like])->getResultArray();
                foreach ($res as $r) { $results[] = $r; }
            }

            if ($type === 'all' || $type === 'faq') {
                // FAQS
                $sql = "
                SELECT 'faq' AS source, id,
                       question AS title,
                       answer AS snippet,
                       '/#faq' AS url,
                       NULL AS published_at,
                       (CASE WHEN question LIKE ? THEN 8 ELSE 0 END) +
                       (CASE WHEN answer LIKE ? THEN 2 ELSE 0 END) AS score
                FROM faqs
                WHERE is_active=1 AND (question LIKE ? OR answer LIKE ?)
                ";
                $res = $this->db->query($sql, [$like,$like,$like,$like])->getResultArray();
                foreach ($res as $r) { $results[] = $r; }
            }

            if ($type === 'all' || $type === 'document') {
                // DOCUMENTS
                $sql = "
                SELECT 'document' AS source, id,
                       title,
                       COALESCE(description,'') AS snippet,
                       file_url AS url,
                       NULL AS published_at,
                       (CASE WHEN title LIKE ? THEN 9 ELSE 0 END) +
                       (CASE WHEN description LIKE ? THEN 3 ELSE 0 END) AS score
                FROM documents
                WHERE is_active=1 AND (title LIKE ? OR description LIKE ?)
                ";
                $res = $this->db->query($sql, [$like,$like,$like,$like])->getResultArray();
                foreach ($res as $r) { $results[] = $r; }
            }

            // Urutkan: score desc, published_at (yang NULL di belakang), lalu title
            usort($results, function($a,$b){
                $cmp = ($b['score'] <=> $a['score']);
                if ($cmp !== 0) return $cmp;
                $aNull = empty($a['published_at']); $bNull = empty($b['published_at']);
                if ($aNull !== $bNull) return $aNull ? 1 : -1; // NULL last
                return strcmp(strtolower($a['title']), strtolower($b['title']));
            });

            // Potong maksimal 50
            $results = array_slice($results, 0, 50);

            // Highlight & snippet singkat
            foreach ($results as &$r) {
                $r['title_h']   = $this->highlight($r['title'], $q);
                $r['snippet_h'] = $this->snippet($r['snippet'], $q, 180);
                // Perbaiki URL absolut dengan base_url jika perlu (services & documents bisa url eksternal/relatif)
                if (is_string($r['url']) && strlen($r['url']) && $r['url'][0] === '/') {
                    $r['url_abs'] = site_url(ltrim($r['url'],'/'));
                } else {
                    $r['url_abs'] = $r['url'];
                }
            }
            unset($r);
        }

        // Data layout global
        $ann      = $this->getAnnouncements();
        $menuHtml = $this->getSidebarHtml();

        return view('search/index', [
            'q'        => $q,
            'type'     => $type,
            'results'  => $results,
            'ann'      => $ann,
            'menuHtml' => $menuHtml,
        ]);
    }

    /* ============ Helpers tampilan ============ */

    private function highlight(string $text, string $q): string
    {
        if ($q === '') return esc($text);
        $safe = esc($text);
        $words = preg_split('/\s+/', trim($q));
        foreach ($words as $w) {
            if ($w === '') continue;
            $pattern = '/('.preg_quote($w,'/').')/i';
            $safe = preg_replace($pattern, '<mark>$1</mark>', $safe);
        }
        return $safe;
    }

    private function snippet(string $text, string $q, int $len = 160): string
    {
        $plain = trim(strip_tags($text));
        if ($plain === '') return '';
        $plain = preg_replace('/\s+/',' ', $plain);

        $pos = 0;
        if ($q !== '') {
            $first = preg_split('/\s+/', trim($q))[0] ?? '';
            if ($first !== '') {
                $pos = stripos($plain, $first);
                if ($pos === false) $pos = 0;
            }
        }
        $start = max(0, $pos - intval($len/3));
        $snip = mb_substr($plain, $start, $len);
        if ($start > 0) $snip = '…' . $snip;
        if ($start + $len < mb_strlen($plain)) $snip .= '…';
        return $this->highlight($snip, $q);
    }

    /* ============ Helpers layout (ticker & sidebar) ============ */

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

        // build tree
        $tree = [];
        foreach ($rows as $r) { $tree[$r['parent_id'] ?? 0][] = $r; }

        $render = function($pid) use (&$render, $tree): string {
            if (!isset($tree[$pid])) return '';
            $html = '';
            foreach ($tree[$pid] as $item) {
                $hasChild = !empty($item['has_children']) && isset($tree[$item['id']]);
                $icon = $item['icon'] ? esc($item['icon']) : 'bi-list';
                $title = esc($item['title']);
                if ($hasChild) {
                    $cid = 'm'.$item['id'];
                    $html .= '<li class="nav-item">'
                           . '<button class="nav-link collapsed px-3 py-2 d-flex align-items-center w-100 text-start" '
                           . 'data-bs-toggle="collapse" data-bs-target="#'.$cid.'" aria-expanded="false" type="button">'
                           . '<i class="bi '.$icon.' me-2"></i><span>'.$title.'</span>'
                           . '<i class="bi bi-chevron-down ms-auto toggle-icon"></i></button>'
                           . '<div class="collapse" id="'.$cid.'"><ul class="nav-content list-unstyled ps-4">'
                           . $render($item['id']).'</ul></div></li>';
                } else {
                    $url = $item['url'] ?? '#';
                    $target = !empty($item['target_blank']) ? ' target="_blank" rel="noopener"' : '';
                    $html .= '<li><a href="'.esc($url).'" class="nav-link text-decoration-none px-3 py-2 d-flex align-items-center"'.$target.'>'
                           . '<i class="bi '.$icon.' me-2"></i><span>'.$title.'</span></a></li>';
                }
            }
            return $html;
        };
        return '<ul class="sidebar-nav list-unstyled" id="sidebar-nav">'.$render(0).'</ul>';
    }
}
