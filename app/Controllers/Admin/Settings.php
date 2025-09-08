<?php namespace App\Controllers\Admin;

class Settings extends AdminBaseController
{
    public function index()
    {
        $rows = $this->db->table('settings')->select('`key`,`value`')->get()->getResultArray();
        $map = [];
        foreach ($rows as $r) $map[$r['key']] = $r['value'];

        return view('admin/settings/index', [
            'title' => 'Settings',
            's' => $map
        ]);
    }

public function save()
{
    helper(['filesystem']);

    // Getter umum (mapping "dot" -> "__")
    $get = fn($k, $d='') => (string) $this->request->getPost(str_replace('.','__',$k)) ?: $d;

    // Helpers
    $pickHex = function(string $key, string $def) use ($get): string {
        $val = trim($get($key, $def));
        if ($val === '') return $def;
        if ($val[0] !== '#') $val = '#'.$val;
        if (preg_match('/^#([a-f0-9]{3}|[a-f0-9]{6})$/i', $val)) return strtolower($val);
        return $def;
    };
    $pickIntRange = function(string $key, int $def, int $min, int $max) use ($get): int {
        $v = (int) $get($key, (string)$def);
        return max($min, min($max, $v));
    };

    // Presence-check untuk checkbox (unchecked = key tidak terkirim)
    $posted = (array) $this->request->getPost();
    $has = fn(string $dotKey): bool =>
        array_key_exists(str_replace('.','__',$dotKey), $posted);

    $pairs = [];

    /* =========================
       SITE BASICS
       ========================= */
    $pairs['site.title']            = $get('site.title');
    $pairs['site.title_short']      = $get('site.title_short');
    $pairs['site.subtitle']         = $get('site.subtitle');
    $pairs['site.meta_description'] = $get('site.meta_description');
    $pairs['site.meta_robots']      = $get('site.meta_robots','index,follow');

    $lang = strtolower($get('site.lang','id'));
    $pairs['site.lang'] = in_array($lang, ['id','en'], true) ? $lang : 'id';

    $theme = strtolower($get('site.theme','light'));
    $pairs['site.theme'] = in_array($theme, ['light','dark','auto'], true) ? $theme : 'light';

    $pairs['org.phone']        = $get('org.phone');
    $pairs['org.email']        = $get('org.email');
    $pairs['social.instagram'] = $get('social.instagram');
    $pairs['social.facebook']  = $get('social.facebook');
    $pairs['social.youtube']   = $get('social.youtube');
    $pairs['social.x']         = $get('social.x');

    /* =========================
       THEME COLORS
       ========================= */
    $pairs['theme.primary']   = $pickHex('theme.primary',   '#0d6efd');
    $pairs['theme.success']   = $pickHex('theme.success',   '#198754');
    $pairs['theme.secondary'] = $pickHex('theme.secondary', '#6c757d');
    $pairs['theme.info']      = $pickHex('theme.info',      '#0dcaf0');
    $pairs['theme.warning']   = $pickHex('theme.warning',   '#ffc107');
    $pairs['theme.danger']    = $pickHex('theme.danger',    '#dc3545');
    $pairs['theme.light']     = $pickHex('theme.light',     '#f8f9fa');
    $pairs['theme.dark']      = $pickHex('theme.dark',      '#212529');

    // token opsional
    $pairs['theme.radius_2xl']   = $get('theme.radius_2xl', '1.25rem');
    $pairs['theme.shadow_soft']  = $get('theme.shadow_soft', '0 .5rem 1.25rem rgba(0,0,0,.06)');
    $pairs['theme.shadow_hover'] = $get('theme.shadow_hover','0 1rem 2rem rgba(0,0,0,.12)');

    // dark override (opsional)
    $pairs['theme.dark.primary'] = $pickHex('theme.dark.primary', $pairs['theme.primary']);

    /* =========================
       HERO SETTINGS
       ========================= */
    $ov1 = trim($get('hero.overlay_start', 'rgba(13,110,253,.10)'));
    $ov2 = trim($get('hero.overlay_end',   'rgba(25,135,84,.08)'));
    $pairs['hero.overlay_start'] = $ov1 !== '' ? $ov1 : 'rgba(13,110,253,.10)';
    $pairs['hero.overlay_end']   = $ov2 !== '' ? $ov2 : 'rgba(25,135,84,.08)';

    $pairs['hero.min_height_vh'] = (string) $pickIntRange('hero.min_height_vh', 62, 30, 100);

    $pairs['hero.title']    = $get('hero.title','Pelayanan Keimigrasian Cepat, Transparan, dan Humanis');
    $pairs['hero.subtitle'] = $get('hero.subtitle','Portal resmi Imigrasi Jambi. Ajukan paspor, cek status permohonan, baca informasi persyaratan, dan sampaikan pengaduan secara daring.');

    // Tombol (pakai presence-check)
    $pairs['hero.btn1.show'] = $has('hero.btn1.show') ? '1' : '0';
    $pairs['hero.btn1.text'] = $get('hero.btn1.text','Ajukan Paspor');
    $pairs['hero.btn1.href'] = $get('hero.btn1.href','#layanan-online');
    $btn1Var = strtolower($get('hero.btn1.variant','primary'));
    $pairs['hero.btn1.variant'] = in_array($btn1Var, ['primary','success','warning','info','secondary','danger','dark'], true) ? $btn1Var : 'primary';

    $pairs['hero.btn2.show'] = $has('hero.btn2.show') ? '1' : '0';
    $pairs['hero.btn2.text'] = $get('hero.btn2.text','Pengaduan');
    $pairs['hero.btn2.href'] = $get('hero.btn2.href','#pengaduan');
    $btn2Var = strtolower($get('hero.btn2.variant','success'));
    $pairs['hero.btn2.variant'] = in_array($btn2Var, ['primary','success','warning','info','secondary','danger','dark'], true) ? $btn2Var : 'success';

    // Profil Kepala (pakai presence-check)
    $pairs['hero.leader.show']  = $has('hero.leader.show') ? '1' : '0';
    $pairs['hero.leader.name']  = $get('hero.leader.name','Nama Kepala Imigrasi');
    $pairs['hero.leader.title'] = $get('hero.leader.title','Kepala Kantor Imigrasi Jambi');

    /* =========================
       UPLOADS (branding + hero)
       ========================= */
    $uploadDir = FCPATH.'uploads/branding';
    if (!is_dir($uploadDir)) @mkdir($uploadDir, 0755, true);

    $fileMap = [
        // Branding
        'logo_file'        => ['key' => 'site.logo_url',       'accept' => ['png','jpg','jpeg','webp','svg'], 'preview' => 'prevLogo'],
        'favicon_file'     => ['key' => 'site.favicon_url',    'accept' => ['ico','png','svg'],               'preview' => 'prevFav'],
        'og_file'          => ['key' => 'site.og_image',       'accept' => ['jpg','jpeg','png','webp'],       'preview' => 'prevOg'],
        // Hero
        'hero_bg_file'     => ['key' => 'hero.bg_image',       'accept' => ['jpg','jpeg','png','webp'],       'preview' => 'prevHeroBg'],
        'hero_leader_file' => ['key' => 'hero.leader.photo',   'accept' => ['jpg','jpeg','png','webp'],       'preview' => 'prevLeader'],
    ];

    foreach ($fileMap as $field => $meta) {
        $file = $this->request->getFile($field);
        $targetKey = $meta['key'];

        if ($file && $file->isValid() && !$file->hasMoved()) {
            $ext = strtolower($file->getExtension());
            if (!in_array($ext, $meta['accept'], true)) {
                return redirect()->back()->withInput()->with('error', 'Tipe file tidak didukung untuk '.$targetKey);
            }
            $newName = date('Ymd_His').'_'.$file->getRandomName();
            $file->move($uploadDir, $newName);
            $publicUrl = base_url('uploads/branding/'.$newName);
            $pairs[$targetKey] = $publicUrl;
        } else {
            // tidak upload → gunakan nilai lama (hidden input)
            $pairs[$targetKey] = $get($targetKey);
        }
    }

    /* =========================
       UPSERT settings
       ========================= */
    $now = date('Y-m-d H:i:s');
    $builder = $this->db->table('settings');

    $this->db->transStart();
    try {
        foreach ($pairs as $k => $v) {
            $exist = $builder->select('id')->where('key', $k)->get()->getRowArray();
            if ($exist) {
                $builder->where('id', $exist['id'])->update([
                    'value'      => (string)$v,
                    'updated_at' => $now,
                ]);
            } else {
                $builder->insert([
                    'key'        => $k,
                    'value'      => (string)$v,
                    'updated_at' => $now,
                ]);
            }
            $builder->resetQuery();
        }
        $this->db->transCommit();
    } catch (\Throwable $e) {
        $this->db->transRollback();
        log_message('error', 'Settings save error: {msg}', ['msg' => $e->getMessage()]);
        return redirect()->back()->withInput()->with('error', 'Gagal menyimpan pengaturan.');
    }

    // Refresh cache helper settings (jika ada)
    if (function_exists('app_settings')) {
        app_settings(true);
    }
    try {
        \Config\Services::cache()->delete('settings_all');
    } catch (\Throwable $e) {
        // no-op
    }

    return redirect()->to(site_url('admin/settings'))->with('success','Settings tersimpan.');
}



}
