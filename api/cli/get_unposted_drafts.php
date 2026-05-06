<?php
require_once __DIR__ . '/../../config.php';

$files = glob(DATA_DRAFTS . '/*.md') ?: [];
$names = array_map('basename', $files);

echo json_encode($names, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . PHP_EOL;
