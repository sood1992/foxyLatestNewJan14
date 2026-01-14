<?php
/**
 * Data Import Script
 *
 * This script imports old data from CSV/SQL files into the JSON database.
 *
 * Usage:
 * - Via CLI: php import-data.php
 * - Via Browser: Access https://yoursite.com/api/import-data.php
 *
 * Make sure to backup your existing data before running this script!
 */

header('Content-Type: application/json');

// Configuration
$importDir = __DIR__ . '/../Old Data to Import/';
$dataDir = __DIR__ . '/data/';

// Check if import directory exists
if (!is_dir($importDir)) {
    die(json_encode([
        'success' => false,
        'error' => 'Import directory not found: ' . $importDir
    ]));
}

// Helper function to parse CSV
function parseCSV($filepath) {
    $data = [];
    if (($handle = fopen($filepath, 'r')) !== FALSE) {
        $headers = fgetcsv($handle);
        $headerCount = count($headers);

        while (($row = fgetcsv($handle)) !== FALSE) {
            // Skip empty rows
            if (empty($row) || (count($row) === 1 && empty($row[0]))) {
                continue;
            }

            // Pad or trim row to match header count
            if (count($row) < $headerCount) {
                $row = array_pad($row, $headerCount, '');
            } elseif (count($row) > $headerCount) {
                $row = array_slice($row, 0, $headerCount);
            }

            $data[] = array_combine($headers, $row);
        }
        fclose($handle);
    }
    return $data;
}

// Helper function to clean SQL NULL values
function cleanValue($value) {
    if ($value === 'NULL' || $value === null || $value === '') {
        return null;
    }
    return trim($value, '"\'');
}

// Import assets from CSV
$csvFile = $importDir . 'sunburni_foxy-2.csv';
$importedAssets = [];
$errors = [];

if (file_exists($csvFile)) {
    $csvData = parseCSV($csvFile);

    foreach ($csvData as $row) {
        $asset = [
            'id' => cleanValue($row['id'] ?? ''),
            'asset_id' => cleanValue($row['asset_id'] ?? ''),
            'asset_name' => cleanValue($row['asset_name'] ?? ''),
            'category' => cleanValue($row['category'] ?? ''),
            'description' => cleanValue($row['description'] ?? ''),
            'serial_number' => cleanValue($row['serial_number'] ?? ''),
            'qr_code' => cleanValue($row['qr_code'] ?? ''),
            'status' => cleanValue($row['status'] ?? 'available'),
            'current_borrower' => cleanValue($row['current_borrower']),
            'checkout_date' => cleanValue($row['checkout_date']),
            'expected_return_date' => cleanValue($row['expected_return_date']),
            'condition_status' => cleanValue($row['condition_status'] ?? 'excellent'),
            'notes' => cleanValue($row['notes'] ?? ''),
            'created_at' => cleanValue($row['created_at'] ?? date('Y-m-d H:i:s')),
            'updated_at' => cleanValue($row['updated_at'] ?? date('Y-m-d H:i:s')),
            'last_maintenance_date' => cleanValue($row['last_maintenance_date']),
            'next_maintenance_due' => cleanValue($row['next_maintenance_due']),
            'maintenance_interval_days' => cleanValue($row['maintenance_interval_days'] ?? '90'),
            'last_returned_date' => cleanValue($row['last_returned_date']),
            'total_checkouts' => cleanValue($row['total_checkouts'] ?? '0')
        ];

        // Only add if asset_id is present
        if (!empty($asset['asset_id'])) {
            $importedAssets[] = $asset;
        }
    }

    // Deduplicate by asset_id - keep the latest record (by updated_at)
    $uniqueAssets = [];
    foreach ($importedAssets as $asset) {
        $assetId = $asset['asset_id'];
        if (!isset($uniqueAssets[$assetId])) {
            $uniqueAssets[$assetId] = $asset;
        } else {
            // Keep the one with the latest updated_at
            $existingDate = strtotime($uniqueAssets[$assetId]['updated_at'] ?? '1970-01-01');
            $newDate = strtotime($asset['updated_at'] ?? '1970-01-01');
            if ($newDate > $existingDate) {
                $uniqueAssets[$assetId] = $asset;
            }
        }
    }
    $importedAssets = array_values($uniqueAssets);

    // Save to assets.json
    $assetsFile = $dataDir . 'assets.json';

    // Backup existing file
    if (file_exists($assetsFile)) {
        $backupFile = $dataDir . 'assets_backup_' . date('Y-m-d_H-i-s') . '.json';
        copy($assetsFile, $backupFile);
    }

    // Write new data
    if (file_put_contents($assetsFile, json_encode($importedAssets, JSON_PRETTY_PRINT))) {
        $result = [
            'success' => true,
            'message' => 'Data imported successfully',
            'stats' => [
                'total_records_in_csv' => count($csvData),
                'unique_assets_imported' => count($importedAssets),
                'source_file' => 'sunburni_foxy-2.csv'
            ]
        ];
    } else {
        $result = [
            'success' => false,
            'error' => 'Failed to write assets.json'
        ];
    }
} else {
    $result = [
        'success' => false,
        'error' => 'CSV file not found: ' . $csvFile
    ];
}

// Also try to import from JSON if exists
$jsonFile = $importDir . 'sunburni_foxy.json';
if (file_exists($jsonFile)) {
    $jsonContent = file_get_contents($jsonFile);
    $jsonData = json_decode($jsonContent, true);

    if ($jsonData && is_array($jsonData)) {
        $result['json_stats'] = [
            'json_file_found' => true,
            'records_in_json' => count($jsonData)
        ];
    }
}

echo json_encode($result, JSON_PRETTY_PRINT);
