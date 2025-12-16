<?php
/**
 * Interface Configuration for WiFi Capture Scripts
 * 
 * Handles interface selection from command line or defaults to wlan0mon
 * Also parses command-line flags
 * 
 * Used by: capture_probes.php, capture_all.php
 */

// Parse command-line flags
$onlyUniquePairs = true;  // Default: only show unique device+SSID pairs
$verbose = false;

foreach ($argv as $arg) {
    if ($arg === '--all' || $arg === '-a') {
        $onlyUniquePairs = false;  // Show all captures
    }
    if ($arg === '--verbose' || $arg === '-v') {
        $verbose = true;
    }
}

// Get interface from command line or default to wlan0mon
// Filter out flags starting with --
$interfaceArg = null;
foreach ($argv as $arg) {
    if ($arg !== $argv[0] && !str_starts_with($arg, '-')) {
        $interfaceArg = $arg;
        break;
    }
}
$interface = $interfaceArg;

if (!$interface) {
    // Check if wlan0mon exists
    exec("ip link show wlan0mon 2>/dev/null", $output, $returnCode);
    if ($returnCode === 0) {
        $interface = "wlan0mon";
    } else {
        echo "Error: No interface specified and wlan0mon not found.\n";
        echo "Usage: {$argv[0]} [interface]\n";
        echo "Example: {$argv[0]} wlan0mon\n";
        echo "\nAvailable wireless interfaces:\n";
        exec("iw dev | grep Interface | awk '{print $2}'", $wlanInterfaces);
        foreach ($wlanInterfaces as $iface) {
            echo "  - $iface\n";
        }
        exit(1);
    }
}

// $interface is now available for the script to use

