<?php
/**
 * Login Diagnostic Script
 *
 * Run via browser: https://yourdomain.com/api/debug-login.php
 * DELETE THIS FILE AFTER USE!
 */

header('Content-Type: application/json');

$dataDir = __DIR__ . '/data/';
$usersFile = $dataDir . 'users.json';

$result = [
    'checks' => []
];

// Check 1: Does data directory exist?
$result['checks']['data_dir_exists'] = is_dir($dataDir);
$result['checks']['data_dir_path'] = $dataDir;

// Check 2: Does users.json exist?
$result['checks']['users_file_exists'] = file_exists($usersFile);
$result['checks']['users_file_path'] = $usersFile;

// Check 3: Is users.json readable?
$result['checks']['users_file_readable'] = is_readable($usersFile);

// Check 4: Is users.json writable?
$result['checks']['users_file_writable'] = is_writable($usersFile);

if (file_exists($usersFile)) {
    $content = file_get_contents($usersFile);
    $users = json_decode($content, true);

    // Check 5: Is JSON valid?
    $result['checks']['json_valid'] = ($users !== null);
    $result['checks']['json_error'] = json_last_error_msg();

    if ($users) {
        $result['checks']['total_users'] = count($users);

        // Find admin user
        $adminUser = null;
        foreach ($users as $user) {
            if ($user['username'] === 'admin' || $user['role'] === 'admin') {
                $adminUser = $user;
                break;
            }
        }

        if ($adminUser) {
            $result['checks']['admin_found'] = true;
            $result['checks']['admin_username'] = $adminUser['username'];
            $result['checks']['admin_role'] = $adminUser['role'];
            $result['checks']['password_hash_starts'] = substr($adminUser['password'], 0, 20) . '...';

            // Test password verification
            $testPassword = 'admin123';
            $verifyResult = password_verify($testPassword, $adminUser['password']);
            $result['checks']['password_admin123_works'] = $verifyResult;

            // Also test 'password'
            $verifyResult2 = password_verify('password', $adminUser['password']);
            $result['checks']['password_password_works'] = $verifyResult2;
        } else {
            $result['checks']['admin_found'] = false;
        }

        // List all usernames
        $result['checks']['all_usernames'] = array_map(function($u) {
            return $u['username'] . ' (' . $u['role'] . ')';
        }, $users);
    }
} else {
    $result['checks']['json_valid'] = false;
    $result['checks']['note'] = 'users.json file not found';
}

// Check PHP version
$result['checks']['php_version'] = PHP_VERSION;

echo json_encode($result, JSON_PRETTY_PRINT);
