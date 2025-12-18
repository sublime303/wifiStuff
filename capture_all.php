#!/usr/bin/env php
<?php

// Include shared configurations
require_once __DIR__ . '/emoji_config.php';
require_once __DIR__ . '/interface_config.php';  // Provides: $interface, $onlyUniquePairs, $verbose
require_once __DIR__ . '/database_config.php';

$seenPairs = [];

// Load previously seen device+SSID pairs from database if available
if ($onlyUniquePairs && $dbEnabled && $db) {
    try {
        $stmt = $db->query("SELECT DISTINCT mac_address, ssid FROM captures WHERE ssid IS NOT NULL AND ssid != '(broadcast)'");
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $key = $row['mac_address'] . '|' . $row['ssid'];
            $seenPairs[$key] = true;
        }
        echo "Loaded " . count($seenPairs) . " previously seen device+SSID pairs from database\n";
    } catch (PDOException $e) {
        // Silently continue
    }
}

echo "Capturing all WiFi communication on interface: $interface\n";
if ($dbEnabled) {
    echo "Logging to database: wifi_captures.db\n";
}
if (!$onlyUniquePairs) {
    echo "Mode: Showing ALL captures (use default for unique pairs only)\n";
} else {
    echo "Mode: Showing UNIQUE device+SSID pairs only (use --all to show everything)\n";
}
if ($verbose) {
    echo "Verbose mode: Activity indicators ENABLED\n";
}
echo "\n";

// Build tshark command with parameters
$cmd = "tshark " .
    "-l " .                                          // Line buffered output (live streaming)
    "-i $interface " .                               // Interface to capture on (monitor mode)
    "-T fields " .                                   // Output format: fields (tab-separated values)
    "-e wlan.sa_resolved " .                         // Field: Source address manufacturer name
    "-e wlan.sa " .                                  // Field: Source MAC address
    "-e wlan.bssid_resolved " .                      // Field: BSSID manufacturer name
    "-e wlan.bssid " .                               // Field: BSSID MAC address
    "-e wlan.ssid " .                                // Field: SSID (network name)
    "-e radiotap.dbm_antsignal " .                   // Field: Signal strength in dBm
    "2>/dev/null";                                   // Suppress error messages

$fp = popen($cmd, 'r');
$packetCount = 0;
$dotsPrinted = false;

while (!feof($fp)) {
    $line = fgets($fp);
    if (!$line) continue;
    
    $f = explode("\t", trim($line));
    if (count($f) < 2) continue;
    
    $packetCount++;
    
    $source = $f[0] ?: $f[1];
    
    // Decode SSID from hex if needed
    $ssidRaw = $f[4] ?? '';
    
    // Check for tshark's <MISSING> placeholder or empty value
    if (empty($ssidRaw) || $ssidRaw === '<MISSING>') {
        $ssid = "(broadcast)";
    } elseif (ctype_xdigit(str_replace(':', '', $ssidRaw))) {
        // It's hex, decode it
        $ssid = hex2bin(str_replace(':', '', $ssidRaw));
        // If decoded to empty, it's broadcast
        if (empty($ssid)) {
            $ssid = "(broadcast)";
        }
    } else {
        $ssid = $ssidRaw;
    }
    
    // Get best (strongest/least negative) RSSI value
    $rssi = "";
    $signalValue = null;
    if (isset($f[5]) && trim($f[5]) !== '') {
        $rssiValues = array_map('intval', explode(',', $f[5]));
        // Filter out zero values (invalid/missing readings)
        $rssiValues = array_filter($rssiValues, fn($v) => $v != 0);
        if (!empty($rssiValues)) {
            $bestRssi = max($rssiValues); // max because -45 > -80
            $rssi = $bestRssi." dB";
            $signalValue = $bestRssi;
        }
    }
    
    // Show activity dot every 100 packets (if verbose enabled)
    if ($verbose && $packetCount % 100 === 0) {
        echo ".";
        $dotsPrinted = true;
    }
    
    if ($f[1]) {
        // Check if this device+SSID pair is unique
        $isUniquePair = false;
        if ($ssid && $ssid !== '(broadcast)') {
            $pairKey = $f[1] . '|' . $ssid;
            if (!isset($seenPairs[$pairKey])) {
                $isUniquePair = true;
                $seenPairs[$pairKey] = true;
            }
        }
        
        // In unique mode: still update device stats even if pair isn't unique
        if ($onlyUniquePairs && !$isUniquePair) {
            // Update device summary (packet count, last seen) but don't log capture
            if ($dbEnabled && $db) {
                updateDeviceSummary($db, $f[1], $source, $ssid);
            }
            continue;
        }
        
        // Display the capture
        $macEmoji = getEmojiForMac($f[1], $source);
        $ssidEmoji = getEmojiForSSID($ssid);
        
        // Add newline before output if dots were printed
        if ($dotsPrinted) {
            echo "\n";
            $dotsPrinted = false;
        }
        
        if ($isUniquePair && $onlyUniquePairs) {
            printf("%s%s %s → %-20s %s (NEW PAIR!)\n", $macEmoji, $ssidEmoji, $source, $ssid, $rssi);
        } else {
            printf("%s%s %s → %-20s %s\n", $macEmoji, $ssidEmoji, $source, $ssid, $rssi);
        }
        
        // Log to database (only logs if displayed)
        $signalValue = isset($f[5]) && $f[5] ? max(array_map('intval', explode(',', $f[5]))) : null;
        logCapture($db, $f[1], $source, $ssid, $f[3] ?? null, $f[2] ?? null, $signalValue, $interface, 'all_traffic');
    }
}

pclose($fp);
