#!/usr/bin/env php
<?php

// Include shared emoji configuration
require_once __DIR__ . '/emoji_config.php';

echo "Capturing all WiFi communication...\n\n";

// Build tshark command with parameters
$cmd = "tshark " .
    "-l " .                                          // Line buffered output (live streaming)
    "-i wlan0mon " .                                 // Interface to capture on (monitor mode)
    "-T fields " .                                   // Output format: fields (tab-separated values)
    "-e wlan.sa_resolved " .                         // Field: Source address manufacturer name
    "-e wlan.sa " .                                  // Field: Source MAC address
    "-e wlan.bssid_resolved " .                      // Field: BSSID manufacturer name
    "-e wlan.bssid " .                               // Field: BSSID MAC address
    "-e wlan.ssid " .                                // Field: SSID (network name)
    "-e radiotap.dbm_antsignal " .                   // Field: Signal strength in dBm
    "2>/dev/null";                                   // Suppress error messages

$fp = popen($cmd, 'r');

while (!feof($fp)) {
    $line = fgets($fp);
    if (!$line) continue;
    
    $f = explode("\t", trim($line));
    if (count($f) < 2) continue;
    
    $source = $f[0] ?: $f[1];
    
    // Decode SSID from hex if needed
    $ssidRaw = $f[4];
    
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
    if ($f[5]) {
        $rssiValues = array_map('intval', explode(',', $f[5]));
        $bestRssi = max($rssiValues); // max because -45 > -80
        $rssi = $bestRssi." dB";
    }
    
    if ($f[1]) {
        $macEmoji = getEmojiForMac($f[1], $source);
        $ssidEmoji = getEmojiForSSID($ssid);
        printf("%s%s %s → %-20s %s\n", $macEmoji, $ssidEmoji, $source, $ssid, $rssi);
    }
}

pclose($fp);
