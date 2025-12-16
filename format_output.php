#!/usr/bin/env php
<?php

// Disable output buffering
ob_implicit_flush(true);
ob_end_flush();

// Ask user what to capture
echo "Select capture mode:\n";
echo "1) Probe requests only\n";
echo "2) All WiFi communication\n";
echo "Choice (1 or 2): ";

$choice = trim(fgets(STDIN));
echo "\n";

// Build tshark command based on choice
if ($choice === "1") {
    echo "Capturing probe requests only...\n\n";
    $filter = "-Y 'wlan.fc.type_subtype == 0x04'";
} else {
    echo "Capturing all WiFi communication...\n\n";
    $filter = "";
}

$cmd = "tshark -l -i wlan0mon $filter -T fields -e wlan.sa_resolved -e wlan.sa -e wlan.bssid_resolved -e wlan.bssid -e wlan_mgt.ssid -e radiotap.dbm_antsignal 2>/dev/null";

// Open the pipe
$fp = popen($cmd, 'r');

if (!$fp) {
    fwrite(STDERR, "Error: Could not start tshark\n");
    exit(1);
}

// Process each line as it comes
while (!feof($fp)) {
    $line = fgets($fp, 4096);
    
    if ($line === false) {
        break;
    }
    
    $line = trim($line);
    if (empty($line)) {
        continue;
    }
    
    $fields = explode("\t", $line);
    
    if (count($fields) < 2) {
        continue;
    }
    
    $saMfg = isset($fields[0]) ? $fields[0] : "";
    $saMac = isset($fields[1]) ? $fields[1] : "";
    $bssidMfg = isset($fields[2]) ? $fields[2] : "";
    $bssid = isset($fields[3]) ? $fields[3] : "";
    $ssid = isset($fields[4]) ? $fields[4] : "";
    $rssi = isset($fields[5]) ? $fields[5] : "";
    
    // Skip if no source MAC
    if (empty($saMac)) {
        continue;
    }
    
    // Format source
    $source = !empty($saMfg) ? $saMfg : $saMac;
    
    // Format destination
    if (empty($bssid) || $bssid === "ff:ff:ff:ff:ff:ff") {
        $dest = "Broadcast";
    } elseif (!empty($bssidMfg)) {
        $dest = $bssidMfg;
    } else {
        $dest = $bssid;
    }
    
    // Format SSID
    $ssidPart = !empty($ssid) ? 'SSID="' . $ssid . '"' : "";
    
    // Format signal
    $signalFmt = !empty($rssi) ? "$rssi dB" : "";
    
    // Print
    printf("%s → %-20s %s %s\n", $source, $dest, $ssidPart, $signalFmt);
}

pclose($fp);
