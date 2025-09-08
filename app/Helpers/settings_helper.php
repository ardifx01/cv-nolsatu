<?php
use Config\Database;

if (!function_exists('app_settings')) {
  function app_settings(bool $refresh=false): array {
    static $mem=null;
    if ($refresh) $mem=null;
    if (is_array($mem)) return $mem;

    $cache = \Config\Services::cache();
    if (!$refresh && ($cached=$cache->get('settings_all'))) return $mem=$cached;

    $db = Database::connect();
    $rows = $db->table('settings')->select('`key`,`value`')->get()->getResultArray();
    $out = [];
    foreach ($rows as $r) $out[$r['key']] = $r['value'];
    $cache->save('settings_all', $out, 600);
    return $mem=$out;
  }
}

if (!function_exists('setting')) {
  function setting(string $key, $default=null) {
    $all = app_settings();
    return array_key_exists($key, $all) ? $all[$key] : $default;
  }
}
