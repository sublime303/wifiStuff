#!/usr/bin/env php
<?php
/**
 * Dependency Checker for WiFi Capture Scripts
 * 
 * Checks for required and optional dependencies
 */

echo "=== WiFi Capture Scripts - Dependency Checker ===\n\n";

$allGood = true;

// Check PHP version
echo "PHP Version: " . PHP_VERSION;
if (version_compare(PHP_VERSION, '7.4.0', '>=')) {
    echo " ✓\n";
} else {
    echo " ✗ (7.4.0+ required)\n";
    $allGood = false;
}

// Check tshark
echo "tshark: ";
exec("which tshark 2>/dev/null", $output, $returnCode);
if ($returnCode === 0) {
    echo "✓ " . trim($output[0]) . "\n";
    unset($output);
    exec("tshark --version 2>&1 | head -1", $output);
    echo "  " . trim($output[0]) . "\n";
} else {
    echo "✗ Not found\n";
    echo "  Install: sudo pacman -S wireshark-cli  (or apt install tshark)\n";
    $allGood = false;
}

// Check PDO SQLite extension
echo "\nPDO SQLite Extension: ";
if (extension_loaded('pdo_sqlite')) {
    echo "✓ Available\n";
    echo "  Database logging: Enabled\n";
} else {
    echo "✗ Not loaded\n";
    echo "  Install: sudo pacman -S php-sqlite  (or apt install php-sqlite3)\n";
    echo "  Then enable in php.ini: extension=pdo_sqlite\n";
    echo "  Database logging: Disabled (scripts will still work)\n";
}

// Check for wireless interface
echo "\nWireless Interfaces:\n";
exec("iw dev 2>/dev/null | grep Interface | awk '{print $2}'", $interfaces);
if (!empty($interfaces)) {
    foreach ($interfaces as $iface) {
        echo "  ✓ $iface\n";
        
        // Check if in monitor mode
        exec("iw dev $iface info | grep 'type monitor'", $monOutput);
        if (!empty($monOutput)) {
            echo "    (Monitor mode active)\n";
        }
    }
} else {
    echo "  ✗ No wireless interfaces found\n";
    echo "    You may need to load wireless drivers\n";
    $allGood = false;
}

// Check for monitor mode interface
echo "\nMonitor Mode Interfaces:\n";
exec("iw dev 2>/dev/null | grep -A 5 'type monitor' | grep Interface | awk '{print $2}'", $monInterfaces);
if (!empty($monInterfaces)) {
    foreach ($monInterfaces as $iface) {
        echo "  ✓ $iface\n";
    }
} else {
    echo "  ⚠ No monitor mode interfaces active\n";
    echo "    To enable monitor mode:\n";
    echo "      sudo ip link set wlan0 down\n";
    echo "      sudo iw wlan0 set monitor control\n";
    echo "      sudo ip link set wlan0 up\n";
}

// Summary
echo "\n" . str_repeat("=", 50) . "\n";
if ($allGood && !empty($monInterfaces)) {
    echo "✓ All required dependencies met! Ready to capture.\n";
} elseif ($allGood) {
    echo "⚠ Dependencies OK, but enable monitor mode first.\n";
} else {
    echo "✗ Some dependencies missing. See above for installation.\n";
}
echo "\n";

