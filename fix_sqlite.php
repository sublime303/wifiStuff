#!/usr/bin/env php
<?php
/**
 * SQLite Extension Troubleshooter
 */

echo "=== PHP SQLite Extension Troubleshooter ===\n\n";

// Check if already loaded
if (extension_loaded('pdo_sqlite')) {
    echo "✓ PDO SQLite is already loaded and working!\n";
    exit(0);
}

echo "✗ PDO SQLite extension is NOT loaded\n\n";

// Show PHP info
echo "PHP Version: " . PHP_VERSION . "\n";
echo "PHP Binary: " . PHP_BINARY . "\n";

// Find php.ini location
$iniPath = php_ini_loaded_file();
echo "Loaded php.ini: " . ($iniPath ?: "None") . "\n";

// Find additional ini files
$scanDir = php_ini_scanned_files();
if ($scanDir) {
    echo "Additional .ini files:\n";
    foreach (explode(',', $scanDir) as $file) {
        echo "  " . trim($file) . "\n";
    }
}

echo "\n--- Checking for SQLite extension files ---\n";

// Common extension paths
$possiblePaths = [
    '/usr/lib/php/modules/pdo_sqlite.so',
    '/usr/lib64/php/modules/pdo_sqlite.so',
    '/usr/lib/php/8.3/modules/pdo_sqlite.so',
    '/usr/lib/php/8.2/modules/pdo_sqlite.so',
    '/usr/lib/php8/modules/pdo_sqlite.so',
];

$foundExtension = null;
foreach ($possiblePaths as $path) {
    if (file_exists($path)) {
        echo "✓ Found: $path\n";
        $foundExtension = $path;
        break;
    }
}

if (!$foundExtension) {
    echo "✗ Extension file not found in common locations\n";
    echo "\nTo install:\n";
    echo "  Arch Linux:    sudo pacman -S php-sqlite\n";
    echo "  Debian/Ubuntu: sudo apt install php-sqlite3\n";
    exit(1);
}

echo "\n--- Solution ---\n\n";

// Check for extension dir
$extDir = ini_get('extension_dir');
echo "Extension directory: $extDir\n\n";

// Provide solutions
echo "The extension is installed but not enabled. Choose one option:\n\n";

echo "Option 1: Enable in CLI php.ini\n";
if ($iniPath) {
    echo "  Edit: $iniPath\n";
} else {
    echo "  Create: /etc/php/php.ini\n";
}
echo "  Add this line:\n";
echo "    extension=pdo_sqlite\n";
echo "    extension=sqlite3\n\n";

echo "Option 2: Check additional .ini directory\n";
$confD = dirname($iniPath ?: '/etc/php/php.ini') . '/conf.d';
if (is_dir($confD)) {
    echo "  Directory exists: $confD\n";
    echo "  Create file: $confD/sqlite.ini\n";
    echo "  With contents:\n";
    echo "    extension=pdo_sqlite\n";
    echo "    extension=sqlite3\n\n";
} else {
    echo "  Directory not found: $confD\n\n";
}

echo "Option 3: Quick fix for Arch Linux\n";
echo "  sudo pacman -S php-sqlite\n";
echo "  Edit /etc/php/php.ini and uncomment these lines:\n";
echo "    ;extension=pdo_sqlite  → extension=pdo_sqlite\n";
echo "    ;extension=sqlite3     → extension=sqlite3\n\n";

echo "After making changes, verify:\n";
echo "  php -m | grep -i sqlite\n";
echo "  ./fix_sqlite.php\n\n";

echo "Or just run the scripts - they work fine without database logging!\n";

