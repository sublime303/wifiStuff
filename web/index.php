<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WiFi 3D Visualization</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            overflow: hidden;
        }
        
        #container {
            width: 100vw;
            height: 100vh;
            position: relative;
        }
        
        #info {
            position: absolute;
            top: 20px;
            left: 20px;
            background: rgba(0, 0, 0, 0.8);
            color: white;
            padding: 20px;
            border-radius: 10px;
            font-size: 14px;
            max-width: 350px;
            z-index: 100;
            backdrop-filter: blur(10px);
        }
        
        #info h1 {
            font-size: 24px;
            margin-bottom: 10px;
            color: #4fc3f7;
        }
        
        #info p {
            margin: 5px 0;
            line-height: 1.5;
        }
        
        #stats {
            position: absolute;
            top: 20px;
            right: 20px;
            background: rgba(0, 0, 0, 0.8);
            color: white;
            padding: 15px;
            border-radius: 10px;
            font-size: 12px;
            z-index: 100;
            backdrop-filter: blur(10px);
        }
        
        #ssid-list {
            position: absolute;
            top: 20px;
            right: 250px;
            background: rgba(0, 0, 0, 0.8);
            color: white;
            padding: 15px;
            border-radius: 10px;
            font-size: 12px;
            z-index: 100;
            backdrop-filter: blur(10px);
            max-height: 80vh;
            overflow-y: auto;
            min-width: 300px;
            max-width: 400px;
            transition: transform 0.3s ease, opacity 0.3s ease;
        }
        
        #ssid-list.hidden {
            transform: translateX(450px);
            opacity: 0;
            pointer-events: none;
        }
        
        #ssid-list h3 {
            margin: 0 0 10px 0;
            color: #4fc3f7;
            font-size: 16px;
            border-bottom: 2px solid #4fc3f7;
            padding-bottom: 5px;
        }
        
        .ssid-item {
            padding: 8px;
            margin: 5px 0;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 5px;
            border-left: 3px solid #00ff88;
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .ssid-item:hover {
            background: rgba(255, 255, 255, 0.15);
            transform: translateX(-3px);
            border-left-color: #00ffcc;
        }
        
        .ssid-name {
            font-weight: bold;
            color: #00ff88;
            margin-bottom: 3px;
        }
        
        .ssid-details {
            font-size: 10px;
            color: #aaa;
            display: flex;
            justify-content: space-between;
            margin-top: 3px;
        }
        
        .client-count {
            cursor: pointer;
            color: #4fc3f7;
            transition: color 0.2s;
        }
        
        .client-count:hover {
            color: #00ffcc;
            text-decoration: underline;
        }
        
        .client-list {
            display: none;
            margin-top: 8px;
            padding-top: 8px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s ease;
        }
        
        .client-list.expanded {
            display: block;
            max-height: 500px;
        }
        
        .client-list-item {
            padding: 5px;
            margin: 3px 0;
            background: rgba(79, 195, 247, 0.1);
            border-radius: 3px;
            border-left: 2px solid #4fc3f7;
            cursor: pointer;
            font-size: 10px;
            transition: all 0.2s;
        }
        
        .client-list-item:hover {
            background: rgba(79, 195, 247, 0.2);
            transform: translateX(3px);
            border-left-color: #00ffcc;
        }
        
        .client-mac {
            font-family: monospace;
            color: #4fc3f7;
            font-weight: bold;
        }
        
        .client-mfg {
            color: #888;
            font-size: 9px;
        }
        
        .client-rssi {
            color: #00ff88;
            font-size: 9px;
        }
        
        .ssid-bssid {
            font-size: 10px;
            color: #888;
            font-family: monospace;
        }
        
        /* Custom scrollbar */
        #ssid-list::-webkit-scrollbar {
            width: 8px;
        }
        
        #ssid-list::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 10px;
        }
        
        #ssid-list::-webkit-scrollbar-thumb {
            background: #4fc3f7;
            border-radius: 10px;
        }
        
        #ssid-list::-webkit-scrollbar-thumb:hover {
            background: #00ff88;
        }
        
        .node-label {
            color: white;
            font-size: 11px;
            font-family: 'Segoe UI', sans-serif;
            padding: 3px 6px;
            background: rgba(0, 0, 0, 0.7);
            border-radius: 3px;
            white-space: nowrap;
            pointer-events: none;
            user-select: none;
            backdrop-filter: blur(5px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            text-shadow: 0 0 3px rgba(0, 0, 0, 0.8);
        }
        
        .node-label.ap-label {
            border-color: rgba(0, 255, 136, 0.5);
            color: #00ff88;
        }
        
        .node-label.client-label {
            border-color: rgba(79, 195, 247, 0.5);
            color: #4fc3f7;
        }
        
        .stat-item {
            display: flex;
            justify-content: space-between;
            margin: 8px 0;
            padding: 5px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .stat-label {
            color: #4fc3f7;
            font-weight: bold;
        }
        
        .stat-value {
            color: #fff;
        }
        
        #controls {
            position: absolute;
            bottom: 20px;
            left: 20px;
            background: rgba(0, 0, 0, 0.8);
            color: white;
            padding: 15px;
            border-radius: 10px;
            font-size: 12px;
            z-index: 100;
            backdrop-filter: blur(10px);
        }
        
        button {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 10px 20px;
            margin: 5px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            transition: transform 0.2s;
        }
        
        button:hover {
            transform: scale(1.05);
        }
        
        button:active {
            transform: scale(0.95);
        }
        
        #tooltip {
            position: absolute;
            background: rgba(0, 0, 0, 0.9);
            color: white;
            padding: 10px;
            border-radius: 5px;
            font-size: 12px;
            pointer-events: none;
            display: none;
            z-index: 200;
            max-width: 300px;
            backdrop-filter: blur(10px);
            transition: border 0.2s;
        }
        
        #tooltip.sticky {
            border: 2px solid #4fc3f7;
            box-shadow: 0 0 20px rgba(79, 195, 247, 0.5);
            pointer-events: auto;
        }
        
        .tooltip-close {
            position: absolute;
            top: 5px;
            right: 5px;
            background: #ff4444;
            color: white;
            border: none;
            border-radius: 3px;
            width: 20px;
            height: 20px;
            cursor: pointer;
            font-size: 12px;
            line-height: 1;
            padding: 0;
            display: none;
        }
        
        #tooltip.sticky .tooltip-close {
            display: block;
        }
        
        .tooltip-close:hover {
            background: #ff6666;
        }
        
        .legend {
            position: absolute;
            bottom: 20px;
            right: 20px;
            background: rgba(0, 0, 0, 0.8);
            color: white;
            padding: 15px;
            border-radius: 10px;
            font-size: 12px;
            z-index: 100;
            backdrop-filter: blur(10px);
        }
        
        .legend-item {
            display: flex;
            align-items: center;
            margin: 8px 0;
        }
        
        .legend-color {
            width: 20px;
            height: 20px;
            border-radius: 50%;
            margin-right: 10px;
        }
        
        .loading {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: rgba(0, 0, 0, 0.9);
            color: white;
            padding: 30px;
            border-radius: 10px;
            font-size: 18px;
            z-index: 300;
        }
    </style>
</head>
<body>
    <div id="container"></div>
    
    <div id="loading" class="loading">Loading WiFi data...</div>
    
    <div id="info">
        <h1>📡 WiFi 3D Visualizer</h1>
        <p>🟢 <strong>Green spheres</strong> = Access Points (SSIDs)</p>
        <p>🔵 <strong>Blue cubes</strong> = Client Devices</p>
        <p>Distance = RSSI signal strength</p>
        <p><small>Closer objects = stronger signal</small></p>
        <p><small>⚠️ Broadcasts hidden by default</small></p>
        <hr style="margin: 10px 0; opacity: 0.3;">
        <p><strong>Controls:</strong></p>
        <p>🖱️ Left click + drag to rotate</p>
        <p>🖱️ Right click + drag to pan</p>
        <p>🖱️ Scroll to zoom</p>
        <p>🖱️ Hover over objects for details</p>
        <p>🖱️ Click on object to pin tooltip</p>
        <p>🎥 Auto-rotate for cinematic view</p>
        <p>🏷️ Toggle labels for easy identification</p>
    </div>
    
    <div id="stats">
        <div class="stat-item">
            <span class="stat-label">Access Points:</span>
            <span class="stat-value" id="ap-count">0</span>
        </div>
        <div class="stat-item">
            <span class="stat-label">Clients:</span>
            <span class="stat-value" id="client-count">0</span>
        </div>
        <div class="stat-item">
            <span class="stat-label">Connections:</span>
            <span class="stat-value" id="connection-count">0</span>
        </div>
        <div class="stat-item">
            <span class="stat-label">Last Update:</span>
            <span class="stat-value" id="last-update">Never</span>
        </div>
    </div>
    
    <div id="ssid-list">
        <h3>📡 Access Points</h3>
        <div id="ssid-list-content">
            <p style="color: #888; font-style: italic;">Loading...</p>
        </div>
    </div>
    
    <div id="controls">
        <button onclick="refreshData()">🔄 Refresh Data</button>
        <button onclick="toggleConnections()">🔗 Toggle Lines</button>
        <button onclick="toggleBroadcasts()" id="broadcast-toggle">📡 Show Broadcasts</button>
        <button onclick="toggleSSIDList()" id="ssid-list-toggle">📋 Hide SSID List</button>
        <button onclick="toggleLabels()" id="labels-toggle">🏷️ Show Labels</button>
        <button onclick="toggleAutoRotate()" id="auto-rotate-toggle">🎥 Auto Rotate</button>
        <button onclick="resetCamera()">📷 Reset Camera</button>
    </div>
    
    <div id="tooltip"></div>
    
    <script type="importmap">
    {
        "imports": {
            "three": "https://cdn.jsdelivr.net/npm/three@0.160.0/build/three.module.js",
            "three/addons/": "https://cdn.jsdelivr.net/npm/three@0.160.0/examples/jsm/"
        }
    }
    </script>
    
    <script type="module">
        import * as THREE from 'three';
        import { OrbitControls } from 'three/addons/controls/OrbitControls.js';
        import { CSS2DRenderer, CSS2DObject } from 'three/addons/renderers/CSS2DRenderer.js';
        
        let scene, camera, renderer, controls;
        let accessPoints = new Map();
        let clients = new Map();
        let connections = [];
        let showConnections = true;
        let includeBroadcasts = false; // Default: hide broadcasts
        let showSSIDList = true; // Default: show SSID list
        let showLabels = false; // Default: labels hidden
        let autoRotate = false; // Default: no auto-rotation
        let rotationAngle = 0;
        let labelRenderer;
        let raycaster = new THREE.Raycaster();
        let mouse = new THREE.Vector2();
        let tooltip = document.getElementById('tooltip');
        let stickyTooltip = false;
        let stickyObject = null;
        
        // Initialize the 3D scene
        function init() {
            // Scene
            scene = new THREE.Scene();
            scene.background = new THREE.Color(0x0a0a0a);
            scene.fog = new THREE.Fog(0x0a0a0a, 50, 200);
            
            // Camera
            camera = new THREE.PerspectiveCamera(
                75,
                window.innerWidth / window.innerHeight,
                0.1,
                1000
            );
            camera.position.set(0, 30, 50);
            
            // Renderer
            renderer = new THREE.WebGLRenderer({ antialias: true });
            renderer.setSize(window.innerWidth, window.innerHeight);
            renderer.setPixelRatio(window.devicePixelRatio);
            document.getElementById('container').appendChild(renderer.domElement);
            
            // Label Renderer (CSS2D)
            labelRenderer = new CSS2DRenderer();
            labelRenderer.setSize(window.innerWidth, window.innerHeight);
            labelRenderer.domElement.style.position = 'absolute';
            labelRenderer.domElement.style.top = '0';
            labelRenderer.domElement.style.pointerEvents = 'none';
            document.getElementById('container').appendChild(labelRenderer.domElement);
            
            // Controls
            controls = new OrbitControls(camera, renderer.domElement);
            controls.enableDamping = true;
            controls.dampingFactor = 0.05;
            controls.minDistance = 10;
            controls.maxDistance = 200;
            
            // Lighting
            const ambientLight = new THREE.AmbientLight(0x404040, 2);
            scene.add(ambientLight);
            
            const directionalLight = new THREE.DirectionalLight(0xffffff, 1);
            directionalLight.position.set(50, 50, 50);
            scene.add(directionalLight);
            
            // Add grid
            const gridHelper = new THREE.GridHelper(100, 20, 0x444444, 0x222222);
            scene.add(gridHelper);
            
            // Event listeners
            window.addEventListener('resize', onWindowResize);
            window.addEventListener('mousemove', onMouseMove);
            window.addEventListener('click', onMouseClick);
            
            // Start animation
            animate();
            
            // Load initial data
            loadData();
        }
        
        // Convert RSSI to distance (in 3D space units)
        // RSSI typically ranges from -30 (very strong) to -90 (very weak)
        function rssiToDistance(rssi) {
            if (!rssi || rssi === 0) return 40; // Default distance
            
            // Map RSSI to distance: -30dB = 5 units, -90dB = 50 units
            const minRssi = -90;
            const maxRssi = -30;
            const minDistance = 5;
            const maxDistance = 50;
            
            const normalized = (rssi - maxRssi) / (minRssi - maxRssi);
            const distance = minDistance + (normalized * (maxDistance - minDistance));
            
            return Math.max(minDistance, Math.min(maxDistance, distance));
        }
        
        // Create an access point (green sphere)
        function createAccessPoint(ssid, bssid) {
            const geometry = new THREE.SphereGeometry(1.5, 32, 32);
            const material = new THREE.MeshStandardMaterial({
                color: 0x00ff88,
                emissive: 0x00ff88,
                emissiveIntensity: 0.5,
                metalness: 0.3,
                roughness: 0.4
            });
            const sphere = new THREE.Mesh(geometry, material);
            
            // Add glow effect
            const glowGeometry = new THREE.SphereGeometry(2, 32, 32);
            const glowMaterial = new THREE.MeshBasicMaterial({
                color: 0x00ff88,
                transparent: true,
                opacity: 0.2
            });
            const glow = new THREE.Mesh(glowGeometry, glowMaterial);
            sphere.add(glow);
            
            // Create label (not as child, will be positioned separately)
            const labelDiv = document.createElement('div');
            labelDiv.className = 'node-label ap-label';
            labelDiv.textContent = ssid || bssid || 'Unknown';
            
            const label = new CSS2DObject(labelDiv);
            label.visible = showLabels; // Use Three.js visible property
            scene.add(label); // Add to scene, not to sphere
            
            sphere.userData = {
                type: 'accessPoint',
                ssid: ssid,
                bssid: bssid,
                clients: [],
                label: label
            };
            
            scene.add(sphere);
            return sphere;
        }
        
        // Create a client device (blue cube)
        function createClient(mac, manufacturer) {
            const geometry = new THREE.BoxGeometry(1.2, 1.2, 1.2);
            const material = new THREE.MeshStandardMaterial({
                color: 0x4fc3f7,
                emissive: 0x4fc3f7,
                emissiveIntensity: 0.3,
                metalness: 0.5,
                roughness: 0.3
            });
            const cube = new THREE.Mesh(geometry, material);
            
            // Create label (not as child, will be positioned separately)
            const labelDiv = document.createElement('div');
            labelDiv.className = 'node-label client-label';
            labelDiv.textContent = manufacturer || mac.substring(0, 17); // Show manufacturer or truncated MAC
            
            const label = new CSS2DObject(labelDiv);
            label.visible = showLabels; // Use Three.js visible property
            scene.add(label); // Add to scene, not to cube
            
            cube.userData = {
                type: 'client',
                mac: mac,
                manufacturer: manufacturer,
                connections: [],
                label: label
            };
            
            scene.add(cube);
            return cube;
        }
        
        // Create connection line between client and AP
        function createConnection(client, ap, rssi) {
            const points = [
                client.position,
                ap.position
            ];
            
            const geometry = new THREE.BufferGeometry().setFromPoints(points);
            
            // Color based on signal strength
            let color;
            if (rssi > -50) color = 0x00ff00; // Green - strong
            else if (rssi > -70) color = 0xffff00; // Yellow - medium
            else color = 0xff0000; // Red - weak
            
            const material = new THREE.LineBasicMaterial({
                color: color,
                transparent: true,
                opacity: 0.3
            });
            
            const line = new THREE.Line(geometry, material);
            line.userData = {
                type: 'connection',
                rssi: rssi,
                client: client,
                ap: ap
            };
            
            scene.add(line);
            connections.push(line);
            
            return line;
        }
        
        // Load data from the API
        async function loadData() {
            try {
                const url = `api.php?include_broadcasts=${includeBroadcasts}`;
                const response = await fetch(url);
                const data = await response.json();
                
                if (data.error) {
                    console.error('Error loading data:', data.error);
                    document.getElementById('loading').innerHTML = 
                        '❌ Error: ' + data.error + '<br><small>Make sure wifi_captures.db exists</small>';
                    return;
                }
                
                // Clear existing objects
                clearScene();
                
                // Create access points
                data.access_points.forEach(ap => {
                    const apObject = createAccessPoint(ap.ssid, ap.bssid);
                    
                    // Store client list with the AP
                    apObject.userData.clients = ap.clients || [];
                    apObject.userData.manufacturer = ap.manufacturer;
                    
                    // Position randomly but spread out
                    const angle = Math.random() * Math.PI * 2;
                    const radius = Math.random() * 20 + 10;
                    apObject.position.x = Math.cos(angle) * radius;
                    apObject.position.y = Math.random() * 10;
                    apObject.position.z = Math.sin(angle) * radius;
                    
                    accessPoints.set(ap.ssid || ap.bssid, apObject);
                });
                
                // Create clients and connections
                data.clients.forEach(client => {
                    const clientObject = createClient(client.mac, client.manufacturer);
                    clients.set(client.mac, clientObject);
                    
                    // Position client based on connected APs
                    let totalX = 0, totalY = 0, totalZ = 0;
                    let connectionCount = 0;
                    
                    client.connections.forEach(conn => {
                        const ap = accessPoints.get(conn.ssid || conn.bssid);
                        if (ap) {
                            const distance = rssiToDistance(conn.rssi);
                            const angle = Math.random() * Math.PI * 2;
                            const elevation = (Math.random() - 0.5) * Math.PI * 0.3;
                            
                            const x = ap.position.x + Math.cos(angle) * distance * Math.cos(elevation);
                            const y = ap.position.y + Math.sin(elevation) * distance;
                            const z = ap.position.z + Math.sin(angle) * distance * Math.cos(elevation);
                            
                            totalX += x;
                            totalY += y;
                            totalZ += z;
                            connectionCount++;
                            
                            // Create connection line
                            createConnection(clientObject, ap, conn.rssi);
                        }
                    });
                    
                    if (connectionCount > 0) {
                        clientObject.position.x = totalX / connectionCount;
                        clientObject.position.y = totalY / connectionCount;
                        clientObject.position.z = totalZ / connectionCount;
                    } else {
                        // Random position if no connections
                        clientObject.position.x = (Math.random() - 0.5) * 60;
                        clientObject.position.y = Math.random() * 10;
                        clientObject.position.z = (Math.random() - 0.5) * 60;
                    }
                    
                    clientObject.userData.connections = client.connections;
                });
                
                // Update stats
                updateStats(data);
                
                // Update SSID list
                updateSSIDList(data.access_points);
                
                // Hide loading
                document.getElementById('loading').style.display = 'none';
                
            } catch (error) {
                console.error('Error loading data:', error);
                document.getElementById('loading').innerHTML = 
                    '❌ Error loading data<br><small>' + error.message + '</small>';
            }
        }
        
        function clearScene() {
            // Remove all access points and their labels
            accessPoints.forEach(ap => {
                if (ap.userData.label) {
                    scene.remove(ap.userData.label);
                }
                scene.remove(ap);
            });
            accessPoints.clear();
            
            // Remove all clients and their labels
            clients.forEach(client => {
                if (client.userData.label) {
                    scene.remove(client.userData.label);
                }
                scene.remove(client);
            });
            clients.clear();
            
            // Remove all connections
            connections.forEach(conn => scene.remove(conn));
            connections = [];
        }
        
        function updateStats(data) {
            document.getElementById('ap-count').textContent = data.access_points.length;
            document.getElementById('client-count').textContent = data.clients.length;
            
            let totalConnections = 0;
            data.clients.forEach(client => {
                totalConnections += client.connections.length;
            });
            document.getElementById('connection-count').textContent = totalConnections;
            
            const now = new Date();
            document.getElementById('last-update').textContent = now.toLocaleTimeString();
        }
        
        function updateSSIDList(aps) {
            const listContent = document.getElementById('ssid-list-content');
            
            if (aps.length === 0) {
                listContent.innerHTML = '<p style="color: #888; font-style: italic;">No access points found</p>';
                return;
            }
            
            let html = '';
            aps.forEach((ap, index) => {
                const ssid = ap.ssid || 'Hidden Network';
                const bssid = ap.bssid || 'Unknown';
                const clientCount = ap.clients ? ap.clients.length : 0;
                const avgSignal = ap.avg_signal ? `${ap.avg_signal} dBm` : 'N/A';
                const identifier = ap.ssid || ap.bssid;
                const apId = `ap-${index}`;
                
                html += `
                    <div class="ssid-item">
                        <div onclick="focusOnAP('${identifier.replace(/'/g, "\\'")}')" style="cursor: pointer;">
                            <div class="ssid-name">${escapeHtml(ssid)}</div>
                            <div class="ssid-bssid">${escapeHtml(bssid)}</div>
                        </div>
                        <div class="ssid-details">
                            <span class="client-count" onclick="toggleClientList('${apId}', event)">👥 ${clientCount} client${clientCount !== 1 ? 's' : ''}</span>
                            <span>📶 ${avgSignal}</span>
                        </div>
                        <div class="client-list" id="${apId}">
                `;
                
                // Add client list
                if (ap.clients && ap.clients.length > 0) {
                    ap.clients.forEach(client => {
                        const mfg = client.manufacturer ? escapeHtml(client.manufacturer) : 'Unknown';
                        const rssi = client.avg_rssi ? `${client.avg_rssi} dBm` : 'N/A';
                        const mac = escapeHtml(client.mac);
                        
                        html += `
                            <div class="client-list-item" onclick="focusOnClient('${mac.replace(/'/g, "\\'")}', event)">
                                <div class="client-mac">${mac}</div>
                                <div style="display: flex; justify-content: space-between; margin-top: 2px;">
                                    <span class="client-mfg">${mfg}</span>
                                    <span class="client-rssi">${rssi}</span>
                                </div>
                            </div>
                        `;
                    });
                } else {
                    html += '<p style="color: #666; font-style: italic; font-size: 10px; padding: 5px;">No clients connected</p>';
                }
                
                html += `
                        </div>
                    </div>
                `;
            });
            
            listContent.innerHTML = html;
        }
        
        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
        
        function focusOnAP(identifier) {
            const ap = accessPoints.get(identifier);
            if (!ap) return;
            
            // Stop auto-rotate if active
            if (autoRotate) {
                toggleAutoRotate();
            }
            
            // Animate camera to focus on the AP
            const targetPos = ap.position;
            const distance = 15;
            
            // Calculate camera position (offset from AP)
            const cameraPos = new THREE.Vector3(
                targetPos.x + distance,
                targetPos.y + distance / 2,
                targetPos.z + distance
            );
            
            // Smooth animation
            animateCamera(cameraPos, targetPos);
            
            // Highlight effect
            highlightObject(ap);
        }
        
        function focusOnClient(mac, event) {
            if (event) event.stopPropagation();
            
            const client = clients.get(mac);
            if (!client) return;
            
            // Stop auto-rotate if active
            if (autoRotate) {
                toggleAutoRotate();
            }
            
            // Animate camera to focus on the client
            const targetPos = client.position;
            const distance = 15;
            
            // Calculate camera position (offset from client)
            const cameraPos = new THREE.Vector3(
                targetPos.x + distance,
                targetPos.y + distance / 2,
                targetPos.z + distance
            );
            
            // Smooth animation
            animateCamera(cameraPos, targetPos);
            
            // Highlight effect
            highlightObject(client);
        }
        
        function toggleClientList(apId, event) {
            if (event) event.stopPropagation();
            
            const clientList = document.getElementById(apId);
            if (!clientList) return;
            
            clientList.classList.toggle('expanded');
        }
        
        function animateCamera(targetPosition, lookAtPosition) {
            const startPos = camera.position.clone();
            const startTarget = controls.target.clone();
            const duration = 1000; // ms
            const startTime = Date.now();
            
            function animate() {
                const elapsed = Date.now() - startTime;
                const progress = Math.min(elapsed / duration, 1);
                
                // Easing function (ease-in-out)
                const eased = progress < 0.5
                    ? 2 * progress * progress
                    : 1 - Math.pow(-2 * progress + 2, 2) / 2;
                
                camera.position.lerpVectors(startPos, targetPosition, eased);
                controls.target.lerpVectors(startTarget, lookAtPosition, eased);
                controls.update();
                
                if (progress < 1) {
                    requestAnimationFrame(animate);
                }
            }
            
            animate();
        }
        
        function highlightObject(object) {
            const originalEmissiveIntensity = object.material.emissiveIntensity;
            const highlightIntensity = 2.0;
            const duration = 1000;
            const startTime = Date.now();
            
            function animate() {
                const elapsed = Date.now() - startTime;
                const progress = elapsed / duration;
                
                if (progress < 0.5) {
                    // Pulse up
                    object.material.emissiveIntensity = originalEmissiveIntensity + 
                        (highlightIntensity - originalEmissiveIntensity) * (progress * 2);
                } else if (progress < 1) {
                    // Pulse down
                    object.material.emissiveIntensity = highlightIntensity - 
                        (highlightIntensity - originalEmissiveIntensity) * ((progress - 0.5) * 2);
                } else {
                    object.material.emissiveIntensity = originalEmissiveIntensity;
                    return;
                }
                
                requestAnimationFrame(animate);
            }
            
            animate();
        }
        
        function onWindowResize() {
            camera.aspect = window.innerWidth / window.innerHeight;
            camera.updateProjectionMatrix();
            renderer.setSize(window.innerWidth, window.innerHeight);
            labelRenderer.setSize(window.innerWidth, window.innerHeight);
        }
        
        function onMouseMove(event) {
            // Don't update tooltip if it's sticky
            if (stickyTooltip) return;
            
            mouse.x = (event.clientX / window.innerWidth) * 2 - 1;
            mouse.y = -(event.clientY / window.innerHeight) * 2 + 1;
            
            // Raycast to find intersected objects
            raycaster.setFromCamera(mouse, camera);
            
            const allObjects = [
                ...Array.from(accessPoints.values()),
                ...Array.from(clients.values())
            ];
            
            const intersects = raycaster.intersectObjects(allObjects, false);
            
            if (intersects.length > 0) {
                const object = intersects[0].object;
                showTooltip(event, object, false);
            } else {
                hideTooltip();
            }
        }
        
        function onMouseClick(event) {
            mouse.x = (event.clientX / window.innerWidth) * 2 - 1;
            mouse.y = -(event.clientY / window.innerHeight) * 2 + 1;
            
            // Raycast to find intersected objects
            raycaster.setFromCamera(mouse, camera);
            
            const allObjects = [
                ...Array.from(accessPoints.values()),
                ...Array.from(clients.values())
            ];
            
            const intersects = raycaster.intersectObjects(allObjects, false);
            
            if (intersects.length > 0) {
                const object = intersects[0].object;
                
                // If clicking the same object, unstick it
                if (stickyTooltip && stickyObject === object) {
                    unstickTooltip();
                } else {
                    // Stick the tooltip to this object
                    showTooltip(event, object, true);
                }
            } else {
                // Clicked empty space, unstick tooltip
                unstickTooltip();
            }
        }
        
        function showTooltip(event, object, sticky = false) {
            const userData = object.userData;
            let content = '';
            
            // Add close button if sticky
            if (sticky) {
                content = '<button class="tooltip-close" onclick="unstickTooltip()">×</button>';
            }
            
            if (userData.type === 'accessPoint') {
                content += `
                    <strong>📡 Access Point</strong><br>
                    SSID: ${userData.ssid || 'Hidden'}<br>
                    BSSID: ${userData.bssid || 'Unknown'}
                `;
                
                if (userData.manufacturer) {
                    content += `<br>Manufacturer: ${userData.manufacturer}`;
                }
                
                if (userData.clients && userData.clients.length > 0) {
                    content += `<br><br><strong>Clients connected: ${userData.clients.length}</strong><br>`;
                    // Show up to 10 clients
                    const displayClients = userData.clients.slice(0, 10);
                    displayClients.forEach(client => {
                        const mfg = client.manufacturer ? ` (${client.manufacturer})` : '';
                        const rssi = client.avg_rssi ? ` ${client.avg_rssi} dBm` : '';
                        content += `• ${client.mac}${mfg}${rssi}<br>`;
                    });
                    
                    if (userData.clients.length > 10) {
                        content += `<small>... and ${userData.clients.length - 10} more</small><br>`;
                    }
                }
            } else if (userData.type === 'client') {
                content += `
                    <strong>📱 Client Device</strong><br>
                    MAC: ${userData.mac}<br>
                    Manufacturer: ${userData.manufacturer || 'Unknown'}<br>
                    Connections: ${userData.connections.length}
                `;
                
                if (userData.connections.length > 0) {
                    content += '<br><br><strong>Connected to:</strong><br>';
                    userData.connections.forEach(conn => {
                        content += `• ${conn.ssid} (${conn.rssi} dBm)<br>`;
                    });
                }
            }
            
            if (sticky) {
                content += '<br><small style="color: #4fc3f7;">📌 Tooltip pinned - Click to unpin</small>';
            }
            
            tooltip.innerHTML = content;
            tooltip.style.display = 'block';
            tooltip.style.left = (event.clientX + 15) + 'px';
            tooltip.style.top = (event.clientY + 15) + 'px';
            
            if (sticky) {
                tooltip.classList.add('sticky');
                stickyTooltip = true;
                stickyObject = object;
            } else {
                tooltip.classList.remove('sticky');
            }
        }
        
        function hideTooltip() {
            if (!stickyTooltip) {
                tooltip.style.display = 'none';
            }
        }
        
        function unstickTooltip() {
            stickyTooltip = false;
            stickyObject = null;
            tooltip.classList.remove('sticky');
            tooltip.style.display = 'none';
        }
        
        function animate() {
            requestAnimationFrame(animate);
            
            // Auto-rotate camera if enabled
            if (autoRotate) {
                rotationAngle += 0.003; // Rotation speed
                const radius = 60; // Distance from center
                const height = 30; // Height of camera
                
                camera.position.x = Math.cos(rotationAngle) * radius;
                camera.position.z = Math.sin(rotationAngle) * radius;
                camera.position.y = height;
                
                // Look at center
                controls.target.set(0, 0, 0);
            }
            
            controls.update();
            
            // Rotate objects slowly and update label positions
            accessPoints.forEach(ap => {
                ap.rotation.y += 0.005;
                
                // Update label position to float above the object
                if (ap.userData.label) {
                    ap.userData.label.position.set(
                        ap.position.x,
                        ap.position.y + 3,
                        ap.position.z
                    );
                }
            });
            
            clients.forEach(client => {
                client.rotation.x += 0.01;
                client.rotation.y += 0.01;
                
                // Update label position to float above the object
                if (client.userData.label) {
                    client.userData.label.position.set(
                        client.position.x,
                        client.position.y + 2,
                        client.position.z
                    );
                }
            });
            
            // Update connection lines
            connections.forEach(conn => {
                const points = [conn.userData.client.position, conn.userData.ap.position];
                conn.geometry.setFromPoints(points);
            });
            
            renderer.render(scene, camera);
            labelRenderer.render(scene, camera);
        }
        
        // Global functions for buttons
        window.refreshData = function() {
            document.getElementById('loading').style.display = 'block';
            loadData();
        };
        
        window.toggleConnections = function() {
            showConnections = !showConnections;
            connections.forEach(conn => {
                conn.visible = showConnections;
            });
        };
        
        window.toggleBroadcasts = function() {
            includeBroadcasts = !includeBroadcasts;
            const btn = document.getElementById('broadcast-toggle');
            
            if (includeBroadcasts) {
                btn.textContent = '📡 Hide Broadcasts';
                btn.style.background = 'linear-gradient(135deg, #ff6b6b 0%, #ee5a6f 100%)';
            } else {
                btn.textContent = '📡 Show Broadcasts';
                btn.style.background = 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)';
            }
            
            // Reload data with new filter
            document.getElementById('loading').style.display = 'block';
            loadData();
        };
        
        window.resetCamera = function() {
            camera.position.set(0, 30, 50);
            controls.target.set(0, 0, 0);
            controls.update();
        };
        
        window.toggleSSIDList = function() {
            showSSIDList = !showSSIDList;
            const listPanel = document.getElementById('ssid-list');
            const btn = document.getElementById('ssid-list-toggle');
            
            if (showSSIDList) {
                listPanel.classList.remove('hidden');
                btn.textContent = '📋 Hide SSID List';
            } else {
                listPanel.classList.add('hidden');
                btn.textContent = '📋 Show SSID List';
            }
        };
        
        window.toggleAutoRotate = function() {
            autoRotate = !autoRotate;
            const btn = document.getElementById('auto-rotate-toggle');
            
            if (autoRotate) {
                btn.textContent = '⏸️ Stop Rotate';
                btn.style.background = 'linear-gradient(135deg, #ff6b6b 0%, #ee5a6f 100%)';
                
                // Disable orbit controls when auto-rotating
                controls.enabled = false;
                
                // Set initial rotation angle based on current camera position
                rotationAngle = Math.atan2(camera.position.z, camera.position.x);
            } else {
                btn.textContent = '🎥 Auto Rotate';
                btn.style.background = 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)';
                
                // Re-enable orbit controls
                controls.enabled = true;
            }
        };
        
        function toggleLabels() {
            showLabels = !showLabels;
            const btn = document.getElementById('labels-toggle');
            
            if (showLabels) {
                btn.textContent = '🏷️ Hide Labels';
            } else {
                btn.textContent = '🏷️ Show Labels';
            }
            
            // Update all AP labels - use Three.js visible property
            accessPoints.forEach(ap => {
                if (ap.userData.label) {
                    ap.userData.label.visible = showLabels;
                }
            });
            
            // Update all client labels - use Three.js visible property
            clients.forEach(client => {
                if (client.userData.label) {
                    client.userData.label.visible = showLabels;
                }
            });
        }
        
        window.toggleLabels = toggleLabels;
        
        window.focusOnAP = focusOnAP;
        window.focusOnClient = focusOnClient;
        window.toggleClientList = toggleClientList;
        window.unstickTooltip = unstickTooltip;
        
        // Start the application
        init();
    </script>
</body>
</html>

