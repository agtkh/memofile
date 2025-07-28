<?php
// --- 関数定義 ---
if (!function_exists('formatBytes')) {
    function formatBytes($bytes, $precision = 2) {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= (1 << (10 * $pow));
        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}

if (!function_exists('truncateMemo')) {
    function truncateMemo($content, $length = 100) {
        if (mb_strlen($content) > $length) {
            return mb_substr($content, 0, $length) . '...';
        }
        return $content;
    }
}
