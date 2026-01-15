<?php
/**
 * Request Debug Script
 * Shows exactly what the server receives
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

echo json_encode([
    'REQUEST_METHOD' => $_SERVER['REQUEST_METHOD'] ?? 'not set',
    'REQUEST_URI' => $_SERVER['REQUEST_URI'] ?? 'not set',
    'SCRIPT_NAME' => $_SERVER['SCRIPT_NAME'] ?? 'not set',
    'PHP_SELF' => $_SERVER['PHP_SELF'] ?? 'not set',
    'DOCUMENT_ROOT' => $_SERVER['DOCUMENT_ROOT'] ?? 'not set',
    'SCRIPT_FILENAME' => $_SERVER['SCRIPT_FILENAME'] ?? 'not set',
    'QUERY_STRING' => $_SERVER['QUERY_STRING'] ?? 'not set',
    'PATH_INFO' => $_SERVER['PATH_INFO'] ?? 'not set',
    'current_dir' => __DIR__,
    'htaccess_exists' => file_exists(__DIR__ . '/../.htaccess') ? 'yes' : 'no',
    'api_htaccess_exists' => file_exists(__DIR__ . '/.htaccess') ? 'yes' : 'no'
], JSON_PRETTY_PRINT);
