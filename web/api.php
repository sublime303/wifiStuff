<?php
/**
 * WiFi 3D Visualization API
 * 
 * Returns JSON data for access points and clients with their connections
 */

header('Content-Type: application/json');

$dbFile = __DIR__ . '/../wifi_captures.db';

if (!file_exists($dbFile)) {
    echo json_encode([
        'error' => 'Database file not found. Run capture scripts first to create the database.',
        'access_points' => [],
        'clients' => []
    ]);
    exit;
}

try {
    $db = new PDO("sqlite:$dbFile");
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Get unique access points (SSIDs/BSSIDs)
    $accessPoints = [];
    $stmt = $db->query("
        SELECT 
            COALESCE(ssid, bssid) as identifier,
            ssid,
            bssid,
            bssid_manufacturer,
            COUNT(*) as capture_count,
            AVG(signal_strength) as avg_signal,
            MAX(timestamp) as last_seen
        FROM captures
        WHERE (ssid IS NOT NULL AND ssid != '(broadcast)') OR bssid IS NOT NULL
        GROUP BY COALESCE(ssid, bssid)
        ORDER BY capture_count DESC
    ");
    
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $accessPoints[] = [
            'ssid' => $row['ssid'] ?: null,
            'bssid' => $row['bssid'] ?: null,
            'manufacturer' => $row['bssid_manufacturer'] ?: null,
            'capture_count' => (int)$row['capture_count'],
            'avg_signal' => $row['avg_signal'] ? (int)$row['avg_signal'] : null,
            'last_seen' => $row['last_seen']
        ];
    }
    
    // Get clients with their connections
    $clients = [];
    $stmt = $db->query("
        SELECT 
            mac_address,
            manufacturer,
            MAX(timestamp) as last_seen,
            COUNT(*) as packet_count
        FROM captures
        WHERE mac_address IS NOT NULL
        GROUP BY mac_address
        ORDER BY packet_count DESC
    ");
    
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $macAddress = $row['mac_address'];
        
        // Get connections for this client
        $connStmt = $db->prepare("
            SELECT 
                ssid,
                bssid,
                signal_strength as rssi,
                COUNT(*) as packet_count,
                MAX(timestamp) as last_seen
            FROM captures
            WHERE mac_address = :mac
                AND ((ssid IS NOT NULL AND ssid != '(broadcast)') OR bssid IS NOT NULL)
                AND signal_strength IS NOT NULL
            GROUP BY COALESCE(ssid, bssid)
            ORDER BY packet_count DESC
        ");
        $connStmt->execute([':mac' => $macAddress]);
        
        $connections = [];
        while ($conn = $connStmt->fetch(PDO::FETCH_ASSOC)) {
            $connections[] = [
                'ssid' => $conn['ssid'] ?: null,
                'bssid' => $conn['bssid'] ?: null,
                'rssi' => (int)$conn['rssi'],
                'packet_count' => (int)$conn['packet_count'],
                'last_seen' => $conn['last_seen']
            ];
        }
        
        // Only include clients that have at least one connection with signal strength
        if (count($connections) > 0) {
            $clients[] = [
                'mac' => $macAddress,
                'manufacturer' => $row['manufacturer'] ?: null,
                'packet_count' => (int)$row['packet_count'],
                'last_seen' => $row['last_seen'],
                'connections' => $connections
            ];
        }
    }
    
    // Return the data
    echo json_encode([
        'access_points' => $accessPoints,
        'clients' => $clients,
        'stats' => [
            'total_captures' => (int)$db->query("SELECT COUNT(*) FROM captures")->fetchColumn(),
            'unique_devices' => (int)$db->query("SELECT COUNT(*) FROM devices")->fetchColumn(),
            'time_range' => [
                'first' => $db->query("SELECT MIN(timestamp) FROM captures")->fetchColumn(),
                'last' => $db->query("SELECT MAX(timestamp) FROM captures")->fetchColumn()
            ]
        ]
    ], JSON_PRETTY_PRINT);
    
} catch (PDOException $e) {
    echo json_encode([
        'error' => 'Database error: ' . $e->getMessage(),
        'access_points' => [],
        'clients' => []
    ]);
}

