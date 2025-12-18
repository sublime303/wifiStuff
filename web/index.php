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
        <hr style="margin: 10px 0; opacity: 0.3;">
        <p><strong>Controls:</strong></p>
        <p>🖱️ Left click + drag to rotate</p>
        <p>🖱️ Right click + drag to pan</p>
        <p>🖱️ Scroll to zoom</p>
        <p>🖱️ Hover over objects for details</p>
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
    
    <div id="controls">
        <button onclick="refreshData()">🔄 Refresh Data</button>
        <button onclick="toggleConnections()">🔗 Toggle Lines</button>
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
        let raycaster = new THREE.Raycaster();
        let mouse = new THREE.Vector2();
        let tooltip = document.getElementById('tooltip');
        
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
            
            sphere.userData = {
                type: 'accessPoint',
                ssid: ssid,
                bssid: bssid,
                clients: []
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
            
            cube.userData = {
                type: 'client',
                mac: mac,
                manufacturer: manufacturer,
                connections: []
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
                const response = await fetch('api.php');
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
                
                // Hide loading
                document.getElementById('loading').style.display = 'none';
                
            } catch (error) {
                console.error('Error loading data:', error);
                document.getElementById('loading').innerHTML = 
                    '❌ Error loading data<br><small>' + error.message + '</small>';
            }
        }
        
        function clearScene() {
            // Remove all access points
            accessPoints.forEach(ap => scene.remove(ap));
            accessPoints.clear();
            
            // Remove all clients
            clients.forEach(client => scene.remove(client));
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
        
        function onWindowResize() {
            camera.aspect = window.innerWidth / window.innerHeight;
            camera.updateProjectionMatrix();
            renderer.setSize(window.innerWidth, window.innerHeight);
        }
        
        function onMouseMove(event) {
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
                showTooltip(event, object);
            } else {
                hideTooltip();
            }
        }
        
        function showTooltip(event, object) {
            const userData = object.userData;
            let content = '';
            
            if (userData.type === 'accessPoint') {
                content = `
                    <strong>📡 Access Point</strong><br>
                    SSID: ${userData.ssid || 'Hidden'}<br>
                    BSSID: ${userData.bssid || 'Unknown'}
                `;
            } else if (userData.type === 'client') {
                content = `
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
            
            tooltip.innerHTML = content;
            tooltip.style.display = 'block';
            tooltip.style.left = (event.clientX + 15) + 'px';
            tooltip.style.top = (event.clientY + 15) + 'px';
        }
        
        function hideTooltip() {
            tooltip.style.display = 'none';
        }
        
        function animate() {
            requestAnimationFrame(animate);
            
            controls.update();
            
            // Rotate objects slowly
            accessPoints.forEach(ap => {
                ap.rotation.y += 0.005;
            });
            
            clients.forEach(client => {
                client.rotation.x += 0.01;
                client.rotation.y += 0.01;
            });
            
            // Update connection lines
            connections.forEach(conn => {
                const points = [conn.userData.client.position, conn.userData.ap.position];
                conn.geometry.setFromPoints(points);
            });
            
            renderer.render(scene, camera);
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
        
        window.resetCamera = function() {
            camera.position.set(0, 30, 50);
            controls.target.set(0, 0, 0);
            controls.update();
        };
        
        // Start the application
        init();
    </script>
</body>
</html>

