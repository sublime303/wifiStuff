#!/usr/bin/env php
<?php

echo "Capturing probe requests...\n\n";

$fp = popen("tshark -l -i wlan0mon -Y 'wlan.fc.type_subtype == 0x04' -T fields -e wlan.sa_resolved -e wlan.sa -e wlan.bssid_resolved -e wlan.bssid -e wlan.ssid -e radiotap.dbm_antsignal 2>/dev/null", 'r');

while (!feof($fp)) {
    $line = fgets($fp);
    if (!$line) continue;
    
    $f = explode("\t", trim($line));
    if (count($f) < 2) continue;
    
    $source = $f[0] ?: $f[1];
    
    // Decode SSID from hex if needed
    $ssidRaw = $f[4];
    if ($ssidRaw && ctype_xdigit(str_replace(':', '', $ssidRaw))) {
        // It's hex, decode it
        $ssid = hex2bin(str_replace(':', '', $ssidRaw));
    } else {
        $ssid = $ssidRaw ?: "";
    }
    
    // Get best (strongest/least negative) RSSI value
    $rssi = "";
    if ($f[5]) {
        $rssiValues = array_map('intval', explode(',', $f[5]));
        $bestRssi = max($rssiValues); // max because -45 > -80
        $rssi = $bestRssi." dB";
    }
    
    if ($f[1]) {
        printf("%s → %-20s %s\n", $source, $ssid, $rssi);
    }
}

pclose($fp);
