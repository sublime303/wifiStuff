#!/usr/bin/env php
<?php
/**
 * View WiFi Captures Database
 * 
 * Simple viewer for the SQLite database
 */

$dbFile = __DIR__ . '/wifi_captures.db';

if (!file_exists($dbFile)) {
    echo "Error: Database file not found: $dbFile\n";
    echo "Run capture_probes.php or capture_all.php first to create the database.\n";
    exit(1);
}

try {
    $db = new PDO("sqlite:$dbFile");
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $command = $argv[1] ?? 'summary';
    
    switch ($command) {
        case 'devices':
        case 'd':
            echo "\n=== Unique Devices ===\n\n";
            $stmt = $db->query("
                SELECT mac_address, manufacturer, first_seen, last_seen, packet_count, ssids_seen
                FROM devices
                ORDER BY last_seen DESC
            ");
            
            printf("%-18s %-20s %-10s %-20s %s\n", "MAC", "Manufacturer", "Packets", "Last Seen", "SSIDs");
            echo str_repeat("-", 120) . "\n";
            
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                printf("%-18s %-20s %-10d %-20s %s\n",
                    $row['mac_address'],
                    substr($row['manufacturer'] ?: 'Unknown', 0, 20),
                    $row['packet_count'],
                    $row['last_seen'],
                    $row['ssids_seen'] ?: '-'
                );
            }
            break;
            
        case 'recent':
        case 'r':
            $limit = $argv[2] ?? 20;
            echo "\n=== Recent Captures (Last $limit) ===\n\n";
            
            $stmt = $db->prepare("
                SELECT timestamp, mac_address, manufacturer, ssid, signal_strength, interface
                FROM captures
                ORDER BY timestamp DESC
                LIMIT :limit
            ");
            $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
            $stmt->execute();
            
            printf("%-20s %-18s %-20s %-20s %-8s %s\n", "Timestamp", "MAC", "Manufacturer", "SSID", "Signal", "Interface");
            echo str_repeat("-", 120) . "\n";
            
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                printf("%-20s %-18s %-20s %-20s %-8s %s\n",
                    $row['timestamp'],
                    $row['mac_address'],
                    substr($row['manufacturer'] ?: 'Unknown', 0, 20),
                    substr($row['ssid'] ?: '-', 0, 20),
                    $row['signal_strength'] ? $row['signal_strength'] . ' dB' : '-',
                    $row['interface']
                );
            }
            break;
            
        case 'ssids':
        case 's':
            echo "\n=== Unique SSIDs Seen ===\n\n";
            
            $stmt = $db->query("
                SELECT ssid, COUNT(*) as count, 
                       COUNT(DISTINCT mac_address) as unique_devices,
                       MAX(timestamp) as last_seen
                FROM captures
                WHERE ssid IS NOT NULL AND ssid != '(broadcast)'
                GROUP BY ssid
                ORDER BY count DESC
            ");
            
            printf("%-30s %-10s %-10s %s\n", "SSID", "Packets", "Devices", "Last Seen");
            echo str_repeat("-", 80) . "\n";
            
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                printf("%-30s %-10d %-10d %s\n",
                    substr($row['ssid'], 0, 30),
                    $row['count'],
                    $row['unique_devices'],
                    $row['last_seen']
                );
            }
            break;
            
        case 'stats':
            echo "\n=== Database Statistics ===\n\n";
            
            $total = $db->query("SELECT COUNT(*) FROM captures")->fetchColumn();
            $devices = $db->query("SELECT COUNT(*) FROM devices")->fetchColumn();
            $ssids = $db->query("SELECT COUNT(DISTINCT ssid) FROM captures WHERE ssid != '(broadcast)'")->fetchColumn();
            $oldest = $db->query("SELECT MIN(timestamp) FROM captures")->fetchColumn();
            $newest = $db->query("SELECT MAX(timestamp) FROM captures")->fetchColumn();
            
            echo "Total Captures: $total\n";
            echo "Unique Devices: $devices\n";
            echo "Unique SSIDs: $ssids\n";
            echo "First Capture: $oldest\n";
            echo "Last Capture: $newest\n";
            break;
            
        case 'summary':
        default:
            echo "\n=== WiFi Captures Database Summary ===\n\n";
            
            $total = $db->query("SELECT COUNT(*) FROM captures")->fetchColumn();
            $devices = $db->query("SELECT COUNT(*) FROM devices")->fetchColumn();
            
            echo "Total Captures: $total\n";
            echo "Unique Devices: $devices\n\n";
            
            echo "Commands:\n";
            echo "  ./view_db.php devices     - List all unique devices\n";
            echo "  ./view_db.php recent [N]  - Show N recent captures (default: 20)\n";
            echo "  ./view_db.php ssids       - List all unique SSIDs\n";
            echo "  ./view_db.php stats       - Show detailed statistics\n";
            break;
    }
    
    echo "\n";
    
} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage() . "\n";
    exit(1);
}

