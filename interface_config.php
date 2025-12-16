<?php
/**
 * Interface Configuration for WiFi Capture Scripts
 * 
 * Handles interface selection from command line or defaults to wlan0mon
 * 
 * Used by: capture_probes.php, capture_all.php
 */

// Get interface from command line or default to wlan0mon
$interface = $argv[1] ?? null;

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

