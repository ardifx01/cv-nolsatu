<?php
if (!function_exists('is_active')) {
    /**
     * @param string $path   path saat ini, mis: "/admin/posts/edit/12"
     * @param string $regex  pola regex lengkap dgn delimiter, mis: '#^/admin$#'
     * @param string $class  kelas CSS yang dikembalikan
     */
    function is_active(string $path, string $regex, string $class='active'): string
    {
        return preg_match($regex, $path) ? $class : '';
    }
}
