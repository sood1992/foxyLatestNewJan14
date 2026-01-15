<?php
/**
 * Direct Login Test Script
 * Run via browser: https://yourdomain.com/api/test-login.php
 * DELETE THIS FILE AFTER USE!
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// Load the same files the API uses
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/utils/JsonDatabase.php';
require_once __DIR__ . '/utils/Auth.php';
require_once __DIR__ . '/utils/Response.php';

$result = ['tests' => []];

// Test 1: Can we load users?
try {
    $users = JsonDatabase::getAll('users');
    $result['tests']['load_users'] = 'OK - ' . count($users) . ' users found';
} catch (Exception $e) {
    $result['tests']['load_users'] = 'FAILED: ' . $e->getMessage();
    echo json_encode($result, JSON_PRETTY_PRINT);
    exit;
}

// Test 2: Find admin user
$adminUser = null;
foreach ($users as $u) {
    if (strtolower($u['username']) === 'admin') {
        $adminUser = $u;
        break;
    }
}

if ($adminUser) {
    $result['tests']['find_admin'] = 'OK - Found admin user';
    $result['tests']['admin_password_hash'] = substr($adminUser['password'], 0, 30) . '...';
} else {
    $result['tests']['find_admin'] = 'FAILED - No admin user found';
    echo json_encode($result, JSON_PRETTY_PRINT);
    exit;
}

// Test 3: Verify password directly
$testPassword = 'password';
$verified = password_verify($testPassword, $adminUser['password']);
$result['tests']['password_verify'] = $verified ? 'OK - Password "password" works' : 'FAILED';

// Test 4: Try Auth class verify
$authVerified = Auth::verifyPassword($testPassword, $adminUser['password']);
$result['tests']['auth_verify'] = $authVerified ? 'OK - Auth::verifyPassword works' : 'FAILED';

// Test 5: Generate token
if ($authVerified) {
    try {
        $token = Auth::generateToken($adminUser);
        $result['tests']['generate_token'] = 'OK - Token generated';
        $result['tests']['token_preview'] = substr($token, 0, 50) . '...';

        // Test 6: Verify the token
        $decoded = Auth::verifyToken($token);
        $result['tests']['verify_token'] = $decoded ? 'OK - Token verified' : 'FAILED';
    } catch (Exception $e) {
        $result['tests']['generate_token'] = 'FAILED: ' . $e->getMessage();
    }
}

// Test 7: Check JWT config
$result['tests']['jwt_secret_set'] = defined('JWT_SECRET') ? 'OK' : 'MISSING';
$result['tests']['jwt_expiry_set'] = defined('JWT_EXPIRY') ? 'OK - ' . JWT_EXPIRY . ' seconds' : 'MISSING';

// Test 8: Simulate full login
$result['simulated_login'] = [];
if ($authVerified) {
    $result['simulated_login'] = [
        'success' => true,
        'user' => [
            'id' => $adminUser['id'],
            'username' => $adminUser['username'],
            'email' => $adminUser['email'] ?? null,
            'role' => $adminUser['role']
        ],
        'token' => Auth::generateToken($adminUser)
    ];
}

echo json_encode($result, JSON_PRETTY_PRINT);
