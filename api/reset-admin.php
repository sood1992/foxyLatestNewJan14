<?php
/**
 * Admin Password Reset Script
 *
 * Run this via browser: https://yourdomain.com/api/reset-admin.php
 * Or via terminal: php api/reset-admin.php
 *
 * DELETE THIS FILE AFTER USE!
 */

header('Content-Type: application/json');

$dataDir = __DIR__ . '/data/';
$usersFile = $dataDir . 'users.json';

// New admin credentials
$newPassword = 'admin123';  // Change this to your desired password!
$hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

if (!file_exists($usersFile)) {
    // Create new users file with admin
    $users = [
        [
            'id' => '1',
            'username' => 'admin',
            'password' => $hashedPassword,
            'email' => 'admin@neofoxmedia.com',
            'role' => 'admin',
            'created_at' => date('Y-m-d H:i:s')
        ]
    ];
} else {
    // Load existing users
    $users = json_decode(file_get_contents($usersFile), true);

    // Find and update admin user
    $adminFound = false;
    foreach ($users as &$user) {
        if ($user['username'] === 'admin' || $user['role'] === 'admin') {
            $user['password'] = $hashedPassword;
            $adminFound = true;
            break;
        }
    }

    // If no admin found, create one
    if (!$adminFound) {
        $maxId = 0;
        foreach ($users as $user) {
            if ((int)$user['id'] > $maxId) {
                $maxId = (int)$user['id'];
            }
        }

        $users[] = [
            'id' => (string)($maxId + 1),
            'username' => 'admin',
            'password' => $hashedPassword,
            'email' => 'admin@neofoxmedia.com',
            'role' => 'admin',
            'created_at' => date('Y-m-d H:i:s')
        ];
    }
}

// Save users
if (file_put_contents($usersFile, json_encode($users, JSON_PRETTY_PRINT))) {
    echo json_encode([
        'success' => true,
        'message' => 'Admin password has been reset',
        'credentials' => [
            'username' => 'admin',
            'password' => $newPassword
        ],
        'warning' => 'DELETE THIS FILE (reset-admin.php) IMMEDIATELY AFTER USE!'
    ], JSON_PRETTY_PRINT);
} else {
    echo json_encode([
        'success' => false,
        'error' => 'Failed to save users file. Check file permissions.'
    ], JSON_PRETTY_PRINT);
}
