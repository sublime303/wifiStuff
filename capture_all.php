#!/usr/bin/env php
<?php

echo "Capturing all WiFi communication...\n\n";

$fp = popen("tshark -l -i wlan0mon -T fields -e wlan.sa_resolved -e wlan.sa -e wlan.bssid_resolved -e wlan.bssid -e wlan.ssid -e radiotap.dbm_antsignal 2>/dev/null", 'r');

while (!feof($fp)) {
    $line = fgets($fp);
    if (!$line) continue;
    
    $f = explode("\t", trim($line));
    if (count($f) < 2) continue;
    
    $source = $f[0] ?: $f[1];
    $dest = (!$f[3] || $f[3] == "ff:ff:ff:ff:ff:ff") ? "Broadcast" : ($f[2] ?: $f[3]);
    
    // Decode SSID from hex if needed
    $ssidRaw = $f[4];
    if ($ssidRaw && ctype_xdigit(str_replace(':', '', $ssidRaw))) {
        // It's hex, decode it
        $ssidDecoded = hex2bin(str_replace(':', '', $ssidRaw));
        $ssid = 'SSID="'.$ssidDecoded.'"';
    } else {
        $ssid = $ssidRaw ? 'SSID="'.$ssidRaw.'"' : "";
    }
    
    $rssi = $f[5] ? $f[5]." dB" : "";
    
    if ($f[1]) {
        printf("%s → %-20s %s %s\n", $source, $dest, $ssid, $rssi);
    }
}

pclose($fp);
