# WiFi 3D Visualization Plugin

Beautiful 3D visualization of WiFi clients and access points using Three.js, with positioning based on RSSI signal strength.

## Features

- 🌐 **Interactive 3D Environment** - Rotate, pan, and zoom to explore your WiFi network
- 📡 **Access Points** - Displayed as green glowing spheres
- 📱 **Client Devices** - Displayed as blue rotating cubes
- 📶 **RSSI-based Positioning** - Distance from AP reflects signal strength
- 🔗 **Connection Lines** - Color-coded by signal strength (green=strong, yellow=medium, red=weak)
- 🖱️ **Interactive Tooltips** - Hover over objects to see detailed information
- 📊 **Real-time Stats** - View counts and last update time
- 🔄 **Live Refresh** - Update visualization with latest data

## Installation

### Option 1: PHP Built-in Server (Quick Start)

```bash
# From the web directory
cd /home/ben/wifiStuff/web
php -S localhost:8080
```

Then open your browser to: http://localhost:8080/

### Option 2: Apache/Nginx

Copy the `web` directory to your web server's document root, or configure a virtual host pointing to it.

#### Apache Example:
```apache
<VirtualHost *:80>
    ServerName wifi.local
    DocumentRoot /home/ben/wifiStuff/web
    <Directory /home/ben/wifiStuff/web>
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

#### Nginx Example:
```nginx
server {
    listen 80;
    server_name wifi.local;
    root /home/ben/wifiStuff/web;
    index index.php;
    
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php-fpm/php-fpm.sock;
        fastcgi_index index.php;
        include fastcgi.conf;
    }
}
```

## Requirements

- PHP 7.4+ with PDO SQLite extension
- Modern web browser with WebGL support
- WiFi capture database (`wifi_captures.db`) with data

## Usage

1. **Capture WiFi Data** (from parent directory):
   ```bash
   cd /home/ben/wifiStuff
   sudo ./capture_all.php
   ```

2. **Start Web Server**:
   ```bash
   cd web
   php -S localhost:8080
   ```

3. **Open Browser**: Navigate to `http://localhost:8080/`

4. **Interact with Visualization**:
   - **Left Click + Drag**: Rotate view
   - **Right Click + Drag**: Pan camera
   - **Scroll**: Zoom in/out
   - **Hover**: View device details
   - **Click "Refresh Data"**: Update with latest captures
   - **Click "Toggle Lines"**: Show/hide connection lines
   - **Click "Reset Camera"**: Return to default view

## Color Coding

### Objects
- 🟢 **Green Spheres**: WiFi Access Points (SSIDs/BSSIDs)
- 🔵 **Blue Cubes**: Client Devices

### Connection Lines
- 🟢 **Green Line**: Strong signal (> -50 dBm)
- 🟡 **Yellow Line**: Medium signal (-50 to -70 dBm)
- 🔴 **Red Line**: Weak signal (< -70 dBm)

## How It Works

### Distance Calculation
The visualization uses RSSI (Received Signal Strength Indicator) to calculate distance:
- **-30 dBm** (very strong) = 5 units away
- **-90 dBm** (very weak) = 50 units away
- Values are interpolated between these extremes

### Positioning Algorithm
1. Access Points are placed in a circular pattern around the center
2. Client devices are positioned relative to their connected APs
3. If a client connects to multiple APs, its position is averaged
4. Connection lines show the relationship between clients and APs

## API Endpoint

The visualization uses `api.php` to fetch data from the database.

### API Response Format:
```json
{
  "access_points": [
    {
      "ssid": "MyNetwork",
      "bssid": "aa:bb:cc:dd:ee:ff",
      "manufacturer": "Cisco",
      "capture_count": 150,
      "avg_signal": -65,
      "last_seen": "2025-12-18 10:30:00"
    }
  ],
  "clients": [
    {
      "mac": "11:22:33:44:55:66",
      "manufacturer": "Apple",
      "packet_count": 50,
      "last_seen": "2025-12-18 10:30:00",
      "connections": [
        {
          "ssid": "MyNetwork",
          "bssid": "aa:bb:cc:dd:ee:ff",
          "rssi": -65,
          "packet_count": 30,
          "last_seen": "2025-12-18 10:30:00"
        }
      ]
    }
  ],
  "stats": {
    "total_captures": 500,
    "unique_devices": 25
  }
}
```

## Troubleshooting

### "Database file not found" Error
- Make sure you've run the capture scripts first: `sudo ./capture_all.php`
- Check that `wifi_captures.db` exists in the parent directory
- Verify file permissions

### "No data to display"
- Capture some WiFi traffic first
- Ensure your captures include SSID and signal strength data
- Check the database has data: `./view_db.php stats`

### Visualization appears empty
- Click "Refresh Data" to reload
- Check browser console for errors (F12)
- Verify the API endpoint is working: visit `http://localhost:8080/api.php`

### Performance Issues
- Large datasets (>1000 devices) may cause lag
- Consider filtering old data or reducing capture time range
- Close other browser tabs for better performance

## Customization

### Change Colors
Edit the color values in `index.php`:

```javascript
// Access Points (line ~223)
color: 0x00ff88,  // Green

// Clients (line ~250)
color: 0x4fc3f7,  // Blue

// Connection lines (line ~279-281)
if (rssi > -50) color = 0x00ff00; // Strong
else if (rssi > -70) color = 0xffff00; // Medium
else color = 0xff0000; // Weak
```

### Adjust Distance Scaling
Modify the `rssiToDistance()` function (line ~161):

```javascript
const minRssi = -90;
const maxRssi = -30;
const minDistance = 5;   // Closest distance
const maxDistance = 50;  // Farthest distance
```

### Change Object Sizes
Adjust geometry parameters:

```javascript
// Access Points (line ~218)
new THREE.SphereGeometry(1.5, 32, 32);

// Clients (line ~245)
new THREE.BoxGeometry(1.2, 1.2, 1.2);
```

## Technologies Used

- **Three.js** (v0.160.0) - 3D graphics library
- **OrbitControls** - Camera controls
- **PHP** - Backend API
- **SQLite** - Database
- **WebGL** - 3D rendering

## License

Use freely for educational and research purposes.

## Credits

Part of the wifiStuff project by Ben.

