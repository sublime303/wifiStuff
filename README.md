# WiFi Scanner Scripts

Beautiful, colorful WiFi packet capture tools with emoji identifiers and SQLite logging.

## Features

- 🎨 **Emoji-based device identification** - Each manufacturer gets themed emojis
- 📡 **SSID-aware emojis** - Network names get contextual emojis  
- 💾 **SQLite database logging** - Track all captures and device history
- 🔧 **Graceful degradation** - Works even if database unavailable
- ⚡ **Real-time display** - Live packet capture with formatted output
- 🎯 **Flexible filtering** - Capture probe requests only or all traffic

## Quick Start

```bash
# Check dependencies
./check_dependencies.php

# Enable monitor mode (if needed)
sudo ip link set wlan0 down
sudo iw wlan0 set monitor control
sudo ip link set wlan0 up

# Capture probe requests (default: new devices only)
sudo ./capture_probes.php

# Capture all WiFi traffic (default: new devices only)
sudo ./capture_all.php

# Use specific interface
sudo ./capture_probes.php wlan1

# Show ALL captures (not just new devices)
sudo ./capture_probes.php --all
sudo ./capture_probes.php wlan1 --all

# Verbose mode (shows activity indicators(dots))
sudo ./capture_probes.php --verbose
sudo ./capture_probes.php --all --verbose
```

## Scripts

### Capture Scripts
- **`capture_probes.php`** - Capture WiFi probe requests only
  - **Default**: Shows only unique device+SSID pairs (discovery mode)
  - Supports `--all` or `-a` flag to show all captures
  - Supports `--verbose` or `-v` flag to show activity indicators (one dot per 100 packets)
- **`capture_all.php`** - Capture all WiFi communication
  - **Default**: Shows only unique device+SSID pairs (discovery mode)
  - Supports `--all` or `-a` flag to show all captures
  - Supports `--verbose` or `-v` flag to show activity indicators (one dot per 100 packets)

### Database Tools
- **`view_db.php`** - Query and view captured data
  ```bash
  ./view_db.php              # Summary
  ./view_db.php devices      # List all unique devices
  ./view_db.php recent 50    # Show recent 50 captures
  ./view_db.php ssids        # All SSIDs seen
  ./view_db.php stats        # Detailed statistics
  ```

### Utility Scripts
- **`check_dependencies.php`** - Check all dependencies

## Configuration Files

- **`emoji_config.php`** - Emoji mappings for manufacturers and SSIDs
- **`interface_config.php`** - Interface selection logic
- **`database_config.php`** - SQLite database setup

## Example Output

**Default mode (unique pairs only):**
```
🍎🏒 Apple_8a:78:ef → snapStop             -79 dB (NEW!)
🍎🏠 Apple_8a:78:ef → HomeNetwork          -65 dB (NEW!)
🚗⚡ Tesla_be:34:de → FastFiber5G          -65 dB (NEW!)
```
(Only shows unique device+SSID combinations never seen before)

**Show all mode (`--all`):**
```
🍎🏒 Apple_8a:78:ef → snapStop             -79 dB
📱📡 Samsung_32:56:ab → (broadcast)         -45 dB
🚗⚡ Tesla_be:34:de → FastFiber5G          -65 dB
💻🏠 Dell_a1:12:cd → HomeNetwork           -70 dB
🍎🏒 Apple_8a:78:ef → snapStop             -79 dB
📱📡 Samsung_32:56:ab → (broadcast)         -46 dB
```
(Shows every capture, including duplicates)

**Verbose mode (`--verbose`):**
```
...
🍎🏒 Apple_8a:78:ef → snapStop             -79 dB (NEW!)
..
🍎🏠 Apple_8a:78:ef → HomeNetwork          -65 dB (NEW!)
```
(Shows `.` for every 100 packets to see background activity)

## Customization

### Add Custom SSID Emojis

Edit `emoji_config.php`:

```php
// Add to SSID themes (use lowercase)
['keywords' => ['mynetwork'], 'emojis' => ['🌟']],
```

### Add Custom Manufacturer Emojis

Edit `emoji_config.php`:

```php
// Add to manufacturer themes
'mycompany' => ['🏢', '💻', '📱'],
```

## Dependencies

### Required
- PHP 7.4+
- tshark (wireshark-cli)
- Wireless interface with monitor mode support

### Optional
- PHP PDO SQLite extension (for database logging)
  - Arch: `sudo pacman -S php-sqlite`
  - Debian/Ubuntu: `sudo apt install php-sqlite3`

## Database Schema

### captures table
Stores every packet capture:
- timestamp, mac_address, manufacturer
- ssid, bssid, bssid_manufacturer
- signal_strength, interface, capture_type

### devices table
Tracks unique devices:
- mac_address (primary key)
- manufacturer, first_seen, last_seen
- packet_count, ssids_seen (comma-separated)

## Monitor Mode Setup

```bash
# Find your wireless interface
iw dev

# Enable monitor mode
sudo ip link set wlan0 down
sudo iw wlan0 set monitor control
sudo ip link set wlan0 up

# Verify
iw dev wlan0 info
```

## Tips

- Run with `sudo` - packet capture requires root privileges
- Database logging is optional - scripts work without it
- Use `Ctrl+C` to stop capture gracefully
- SSID keywords are case-insensitive
- **Default mode**: Only shows unique device+network relationships (great for wardriving!)
- Use `--all` flag to show all captures including duplicates
- With database: remembers pairs from previous sessions; without: tracks current session only

## File Structure

```
wifiStuff/
├── capture_probes.php      # Probe request capture
├── capture_all.php         # All traffic capture
├── view_db.php             # Database viewer
├── check_dependencies.php  # Dependency checker
├── emoji_config.php        # Emoji mappings
├── interface_config.php    # Interface logic
├── database_config.php     # Database setup
├── wifi_captures.db        # SQLite database (auto-created)
└── README.md               # This file
```


# TODO: 
- add bluetooth scanning too


## License

Use freely for educational and research purposes.

