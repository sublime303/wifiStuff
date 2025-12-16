#!/usr/bin/env php
<?php

// Function to get a consistent emoji for a MAC address based on manufacturer
function getEmojiForMac($mac, $manufacturer) {
    $manufacturer = strtolower($manufacturer);
    
    // Themed emoji sets for specific manufacturers
    $themedEmojis = [
        // Apple devices
        'apple' => ['🍎', '🍏', '📱', '💻', '⌚', '🎧', '🖥️', '⌨️', '🖱️', '🔌'],
        
        // Car brands
        'tesla' => ['🚗', '⚡', '🔋', '🏎️', '🚙', '🔌', '💡', '🌟'],
        'bmw' => ['🚗', '🏎️', '🚙', '🚕', '🏁', '⚙️', '🔧'],
        'mercedes' => ['🚗', '🏎️', '🚙', '⭐', '👑', '💎'],
        'audi' => ['🚗', '🏎️', '🚙', '⭕', '🔵', '⚪'],
        'volkswagen' => ['🚗', '🚙', '🚐', '🚎', '🔵'],
        'ford' => ['🚗', '🚙', '🚓', '🚚', '🛻'],
        'toyota' => ['🚗', '🚙', '🚕', '🔴', '⭕'],
        'honda' => ['🚗', '🚙', '🏍️', '🛵', '🔴'],
        'nissan' => ['🚗', '🚙', '⚫', '🔴', '⚪'],
        'chevrolet' => ['🚗', '🚙', '🏁', '⭐', '🔵'],
        'gm' => ['🚗', '🚙', '🚓', '🚐'],
        'chrysler' => ['🚗', '🚙', '⭐', '🔵'],
        'jeep' => ['🚙', '⛰️', '🏕️', '🌲', '🗻'],
        'volvo' => ['🚗', '🚙', '🔵', '⚪', '🛡️'],
        'porsche' => ['🏎️', '🐎', '⚡', '🏁', '👑'],
        
        // Phone/Tech brands
        'samsung' => ['📱', '💻', '📺', '⌚', '🎧', '📷', '🔵', '⚪', '⚫'],
        'google' => ['🔵', '🔴', '🟡', '🟢', '🔍', '📱', '💻'],
        'microsoft' => ['💻', '🖥️', '⌨️', '🖱️', '🪟', '🔵', '🟢', '🔴', '🟡'],
        'dell' => ['💻', '🖥️', '⌨️', '🔵', '⚪'],
        'hp' => ['💻', '🖥️', '🖨️', '🔵', '⚪'],
        'lenovo' => ['💻', '🖥️', '🔴', '⚫'],
        'asus' => ['💻', '🖥️', '🎮', '⚡', '🔵'],
        'sony' => ['📺', '🎮', '🎧', '📷', '🎬', '🔵', '⚫', '⚪'],
        'lg' => ['📺', '📱', '🔴', '⚪', '⚫'],
        'huawei' => ['📱', '💻', '🔴', '⚫', '🌸'],
        'xiaomi' => ['📱', '💻', '🟠', '⚫', '⚪'],
        'motorola' => ['📱', '📻', '📡', '🔵', '⚪'],
        'nokia' => ['📱', '🔵', '⚪', '📟'],
        
        // Network equipment
        'cisco' => ['🌐', '📡', '🔵', '⚪', '🔌', '💻'],
        'netgear' => ['📡', '🌐', '🔵', '⚪', '🔌'],
        'tp-link' => ['📡', '🌐', '🟢', '🔵', '⚪'],
        'linksys' => ['📡', '🌐', '🔵', '⚪'],
        'ubiquiti' => ['📡', '🌐', '🔵', '⚪', '☁️'],
        'd-link' => ['📡', '🌐', '🟢', '⚫'],
        'asus' => ['📡', '🎮', '💻', '🔵'],
        
        // IoT/Smart devices
        'amazon' => ['📦', '🟠', '🔵', '🎤', '📱', '💡'],
        'sonos' => ['🔊', '🎵', '🎶', '⚫', '⚪'],
        'philips' => ['💡', '🔵', '🟢', '🟡', '🔴', '🟣'],
        'nest' => ['🏠', '🌡️', '📹', '🔵', '🟢'],
        'ring' => ['🔔', '📹', '🔵', '⚫'],
        'bose' => ['🔊', '🎧', '🎵', '⚫'],
    ];
    
    // Check if manufacturer matches any themed set
    foreach ($themedEmojis as $brand => $emojis) {
        if (strpos($manufacturer, $brand) !== false) {
            $hash = crc32($mac);
            $index = abs($hash) % count($emojis);
            return $emojis[$index];
        }
    }
    
    // Default emoji set for unknown manufacturers
    $defaultEmojis = ['🔴', '🟠', '🟡', '🟢', '🔵', '🟣', '🟤', '⚫', '⚪', '🔺', 
                      '🔻', '🔶', '🔷', '🔸', '🔹', '💠', '🌟', '⭐', '✨', '💫',
                      '🎯', '🎨', '🎭', '🎪', '🎬', '🎮', '🎰', '🎲', '🧩', '🎸',
                      '🎹', '🎺', '🎻', '🎤', '🎧', '📱', '💻', '⌚', '📡', '🔌',
                      '💡', '🔦', '🏮', '📻', '📺', '📷', '📹', '🎥', '☎️', '📞',
                      '🚀', '🛸', '🚂', '🚆', '⛵', '🚤', '⚓', '🎢', '🎡', '🎠'];
    
    $hash = crc32($mac);
    $index = abs($hash) % count($defaultEmojis);
    return $defaultEmojis[$index];
}

echo "Capturing probe requests...\n\n";

// Build tshark command with parameters
$cmd = "tshark " .
    "-l " .                                          // Line buffered output (live streaming)
    "-i wlan0mon " .                                 // Interface to capture on (monitor mode)
    "-Y 'wlan.fc.type_subtype == 0x04' " .          // Display filter: only probe requests (0x04)
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
        $emoji = getEmojiForMac($f[1], $source);
        printf("%s %s → %-20s %s\n", $emoji, $source, $ssid, $rssi);
    }
}

pclose($fp);
