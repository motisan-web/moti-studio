<?php

function json_read(string $path): ?array {
    if (!file_exists($path)) return null;
    $data = json_decode(file_get_contents($path), true);
    return is_array($data) ? $data : null;
}

function json_write(string $path, array $data): void {
    $dir = dirname($path);
    if (!is_dir($dir)) mkdir($dir, 0755, true);
    $tmp = $path . '.tmp';
    file_put_contents($tmp, json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    rename($tmp, $path);
}

function json_list(string $dir): array {
    if (!is_dir($dir)) return [];
    $files = glob($dir . '/*.json');
    if (!$files) return [];
    $result = [];
    foreach ($files as $file) {
        $item = json_read($file);
        if ($item !== null) $result[] = $item;
    }
    return $result;
}

function post_new_id(): string {
    return date('Ymd') . '_' . bin2hex(random_bytes(3));
}

function now_iso(): string {
    return date('Y-m-d\TH:i:s');
}
