<?php
/**
 * Emoji Configuration for WiFi Capture Scripts
 * 
 * This file contains all emoji mappings for:
 * - MAC address manufacturers
 * - SSID names/themes
 * 
 * Used by: capture_probes.php, capture_all.php
 */

// Function to get a consistent emoji for an SSID
function getEmojiForSSID($ssid) {
    if (empty($ssid)) {
        return '';
    }
    
    $ssid = strtolower($ssid);
    
    // Special case for broadcast probes
    if ($ssid === '(broadcast)') {
        return '📡';
    }
    
    // Theme-based SSID emojis
    $themes = [
        // known custom SSIDs (use lowercase)
        ['keywords' => ['slapshot'], 'emojis' => ['🏒']],
        ['keywords' => ['quist'], 'emojis' => ['🌿']], 
        ['keywords' => ['defaultssid'], 'emojis' => ['🚫']],
        ['keywords' => ['tele2'], 'emojis' => ['📶','🔗']],

        // Home/Family related
        ['keywords' => ['home', 'house', 'family', 'familia'], 'emojis' => ['🏠', '🏡', '👨‍👩‍👧‍👦', '👪', '🏘️']],
        
        // Speed/Fast related
        ['keywords' => ['fast', 'speed', 'turbo', 'quick', 'rapid', 'fiber'], 'emojis' => ['⚡', '💨', '🚀', '⏩', '🔥']],
        
        // Location related
        ['keywords' => ['office', 'work', 'business'], 'emojis' => ['🏢', '💼', '👔', '📊', '🖥️']],
        ['keywords' => ['cafe', 'coffee', 'starbucks'], 'emojis' => ['☕', '🍵', '🥤', '🏪', '🍰']],
        ['keywords' => ['hotel', 'motel', 'inn', 'guest'], 'emojis' => ['🏨', '🛏️', '🗝️', '🏩', '🛎️']],
        ['keywords' => ['airport', 'flight', 'terminal'], 'emojis' => ['✈️', '🛫', '🛬', '🛩️', '🌐']],
        
        // Security related
        ['keywords' => ['secure', 'private', 'vpn', 'safe', 'protected'], 'emojis' => ['🔒', '🔐', '🛡️', '🔑', '🚨']],
        ['keywords' => ['guest', 'public', 'free', 'open'], 'emojis' => ['🌍', '🌎', '🌏', '🔓', '📡']],
        
        // Tech/Nerd related
        ['keywords' => ['net', 'wifi', 'network', 'link', 'connect', 'lan'], 'emojis' => ['📡', '🌐', '📶', '🔗', '💻']],
        ['keywords' => ['tech', 'geek', 'nerd', 'hack', 'dev'], 'emojis' => ['🤓', '👨‍💻', '👩‍💻', '⌨️', '🖥️']],
        
        // Fun/Creative
        ['keywords' => ['fun', 'party', 'disco', 'dance'], 'emojis' => ['🎉', '🎊', '🪩', '💃', '🕺']],
        ['keywords' => ['music', 'sound', 'audio', 'beats'], 'emojis' => ['🎵', '🎶', '🎧', '🎤', '🔊']],
        ['keywords' => ['game', 'gaming', 'play', 'xbox', 'playstation'], 'emojis' => ['🎮', '🕹️', '👾', '🎯', '🏆']],
        
        // Nature/Weather
        ['keywords' => ['sun', 'sunny', 'sunshine'], 'emojis' => ['☀️', '🌞', '🌅', '🌄', '⛅']],
        ['keywords' => ['cloud', 'sky'], 'emojis' => ['☁️', '⛅', '🌤️', '🌥️', '☁️']],
        ['keywords' => ['star', 'moon', 'night'], 'emojis' => ['⭐', '🌟', '✨', '🌙', '🌛']],
        
        // Animals
        ['keywords' => ['cat', 'kitty', 'meow'], 'emojis' => ['🐱', '🐈', '😺', '😸', '🐾']],
        ['keywords' => ['dog', 'puppy', 'woof'], 'emojis' => ['🐶', '🐕', '🦴', '🐾', '🐕‍🦺']],
        ['keywords' => ['dragon', 'dino'], 'emojis' => ['🐉', '🐲', '🦕', '🦖', '🔥']],
        
        // Colors
        ['keywords' => ['red', 'rouge'], 'emojis' => ['🔴', '❤️', '🌹', '🍎', '🍓']],
        ['keywords' => ['blue', 'azul'], 'emojis' => ['🔵', '💙', '🌊', '🫐', '🦋']],
        ['keywords' => ['green', 'verde'], 'emojis' => ['🟢', '💚', '🍀', '🌿', '🌲']],
        ['keywords' => ['yellow', 'gold'], 'emojis' => ['🟡', '💛', '⭐', '🌟', '🍋']],
        ['keywords' => ['purple', 'violet'], 'emojis' => ['🟣', '💜', '🔮', '👾', '🍇']],
        
        // Numbers (common in SSIDs)
        ['keywords' => ['5g', '5ghz'], 'emojis' => ['5️⃣', '⚡', '🚀', '💨', '⏩']],
        ['keywords' => ['2g', '2.4', '24g'], 'emojis' => ['2️⃣', '📡', '🌐', '📶', '🔗']],
        
    ];
    
    // Check for keyword matches
    foreach ($themes as $theme) {
        foreach ($theme['keywords'] as $keyword) {
            if (strpos($ssid, $keyword) !== false) {
                $hash = crc32($ssid);
                $index = abs($hash) % count($theme['emojis']);
                return $theme['emojis'][$index];
            }
        }
    }
    
    // Default: use hash of SSID to pick from general emojis
    $defaultEmojis = ['📶', '🌐', '💠', '🔷', '🔶', '🔹', '🔸', '🎯', '🎨', '🎭',
                      '🎪', '🎬', '🎮', '🎰', '🎲', '🎸', '🎹', '🎺', '🎻', '🎤'];
    
    $hash = crc32($ssid);
    $index = abs($hash) % count($defaultEmojis);
    return $defaultEmojis[$index];
}

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
        
        // IoT/Smart devices
        'amazon' => ['📦', '🟠', '🔵', '🎤', '📱', '💡'],
        'sonos' => ['🔊', '🎵', '🎶', '⚫', '⚪'],
        'philips' => ['💡', '🔵', '🟢', '🟡', '🔴', '🟣'],
        'nest' => ['🏠', '🌡️', '📹', '🔵', '🟢'],
        'ring' => ['🔔', '📹', '🔵', '⚫'],
        'bose' => ['🔊', '🎧', '🎵', '⚫'],
        'espressif' => ['🌡️','🔊', '🎧', '🎵', '⚫'],
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

