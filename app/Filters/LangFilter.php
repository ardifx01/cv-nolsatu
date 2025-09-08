<?php
namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;

class LangFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $allowed = ['id','en'];

        $getLang = strtolower((string) $request->getGet('lang'));
        if ($getLang && in_array($getLang, $allowed, true)) {
            // simpan preferensi
            session()->set('lang', $getLang);
            service('response')->setCookie('lang', $getLang, 365*24*60*60);

            // bersihkan query ?lang=... → redirect (GET & non-AJAX saja)
            if ($request->getMethod() === 'get' && !$request->isAJAX()) {
                $uri = current_url(true);                  // URI tanpa query
                $qs  = $request->getGet();                 // semua query
                unset($qs['lang']);                        // hapus 'lang'
                $to  = (string) $uri . (empty($qs) ? '' : ('?' . http_build_query($qs)));
                return redirect()->to($to);                // <— penting: return Response
            }
        }

        // terapkan locale aktif (urutan prioritas)
        $active = session('lang') ?? $request->getCookie('lang') ?? 'id';
        $request->setLocale($active);
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // no-op
    }
}
