<?php
/**
 * Database Configuration for WiFi Capture Scripts
 * 
 * SQLite database for storing captured WiFi probes and traffic
 * 
 * Used by: capture_probes.php, capture_all.php
 */

// Check if SQLite PDO driver is available
$dbEnabled = false;
$db = null;

if (!extension_loaded('pdo_sqlite')) {
    echo "Warning: PDO SQLite extension not available. Database logging disabled.\n";
    echo "To enable: Install php-sqlite3 package (e.g., 'sudo pacman -S php-sqlite' or 'sudo apt install php-sqlite3')\n\n";
} else {
    // Database file location
    $dbFile = __DIR__ . '/wifi_captures.db';
    
    // Create/open database connection
    try {
        $db = new PDO("sqlite:$dbFile");
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $dbEnabled = true;
    
    // Create tables if they don't exist
    $db->exec("
        CREATE TABLE IF NOT EXISTS captures (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            timestamp DATETIME DEFAULT CURRENT_TIMESTAMP,
            mac_address TEXT NOT NULL,
            manufacturer TEXT,
            ssid TEXT,
            bssid TEXT,
            bssid_manufacturer TEXT,
            signal_strength INTEGER,
            interface TEXT,
            capture_type TEXT
        );
        
        CREATE INDEX IF NOT EXISTS idx_mac ON captures(mac_address);
        CREATE INDEX IF NOT EXISTS idx_ssid ON captures(ssid);
        CREATE INDEX IF NOT EXISTS idx_timestamp ON captures(timestamp);
    ");
    
    // Create devices summary table for unique devices
    $db->exec("
        CREATE TABLE IF NOT EXISTS devices (
            mac_address TEXT PRIMARY KEY,
            manufacturer TEXT,
            first_seen DATETIME DEFAULT CURRENT_TIMESTAMP,
            last_seen DATETIME DEFAULT CURRENT_TIMESTAMP,
            packet_count INTEGER DEFAULT 1,
            ssids_seen TEXT
        );
        
        CREATE INDEX IF NOT EXISTS idx_last_seen ON devices(last_seen);
    ");
    
    } catch (PDOException $e) {
        echo "Warning: Database initialization failed: " . $e->getMessage() . "\n";
        echo "Continuing without database logging...\n\n";
        $dbEnabled = false;
        $db = null;
    }
}

/**
 * Insert a capture into the database
 */
function logCapture($db, $macAddress, $manufacturer, $ssid, $bssid, $bssidManufacturer, $signalStrength, $interface, $captureType) {
    // Skip if database is not available
    if (!$db) {
        return;
    }
    
    try {
        // Insert capture record
        $stmt = $db->prepare("
            INSERT INTO captures (mac_address, manufacturer, ssid, bssid, bssid_manufacturer, signal_strength, interface, capture_type)
            VALUES (:mac, :mfg, :ssid, :bssid, :bssid_mfg, :signal, :interface, :type)
        ");
        
        $stmt->execute([
            ':mac' => $macAddress,
            ':mfg' => $manufacturer,
            ':ssid' => $ssid,
            ':bssid' => $bssid,
            ':bssid_mfg' => $bssidManufacturer,
            ':signal' => $signalStrength,
            ':interface' => $interface,
            ':type' => $captureType
        ]);
        
        // Update devices summary
        updateDeviceSummary($db, $macAddress, $manufacturer, $ssid);
        
    } catch (PDOException $e) {
        // Silently fail to not interrupt capture
        error_log("DB insert error: " . $e->getMessage());
    }
}

/**
 * Update device summary table
 */
function updateDeviceSummary($db, $macAddress, $manufacturer, $ssid) {
    // Skip if database is not available
    if (!$db) {
        return;
    }
    
    try {
        // Check if device exists
        $stmt = $db->prepare("SELECT mac_address, ssids_seen FROM devices WHERE mac_address = :mac");
        $stmt->execute([':mac' => $macAddress]);
        $device = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($device) {
            // Update existing device
            $ssidsSeen = $device['ssids_seen'] ? explode(',', $device['ssids_seen']) : [];
            if ($ssid && $ssid !== '(broadcast)' && !in_array($ssid, $ssidsSeen)) {
                $ssidsSeen[] = $ssid;
            }
            
            $stmt = $db->prepare("
                UPDATE devices 
                SET last_seen = CURRENT_TIMESTAMP, 
                    packet_count = packet_count + 1,
                    ssids_seen = :ssids
                WHERE mac_address = :mac
            ");
            $stmt->execute([
                ':mac' => $macAddress,
                ':ssids' => implode(',', $ssidsSeen)
            ]);
        } else {
            // Insert new device
            $stmt = $db->prepare("
                INSERT INTO devices (mac_address, manufacturer, ssids_seen)
                VALUES (:mac, :mfg, :ssids)
            ");
            $stmt->execute([
                ':mac' => $macAddress,
                ':mfg' => $manufacturer,
                ':ssids' => ($ssid && $ssid !== '(broadcast)') ? $ssid : ''
            ]);
        }
    } catch (PDOException $e) {
        error_log("DB update error: " . $e->getMessage());
    }
}

// $db and $dbEnabled are now available for the scripts to use
// $dbEnabled will be true if database is working, false otherwise

