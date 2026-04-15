<?php
// C:\xampp\htdocs\dashboardtaxi\views\live_map.php
?>
<!-- Leaflet CSS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin="anonymous" referrerpolicy="no-referrer" />
<!-- Leaflet Routing CSS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet-routing-machine@3.2.12/dist/leaflet-routing-machine.css" crossorigin="anonymous" referrerpolicy="no-referrer" />
<!-- Leaflet JS -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<!-- Leaflet Routing JS -->
<script src="https://unpkg.com/leaflet-routing-machine@3.2.12/dist/leaflet-routing-machine.js" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<!-- Pusher JS -->
<script src="https://js.pusher.com/8.0.1/pusher.min.js" crossorigin="anonymous" referrerpolicy="no-referrer"></script>

<div class="space-y-8 animate__animated animate__fadeIn">
    <!-- Live Fleet Tracking -->
    <div class="glass-card p-8 rounded-[40px] bg-white border border-slate-100 shadow-premium overflow-hidden relative">
        <div class="flex items-center justify-between mb-6">
            <div class="flex items-center space-x-4">
                <?php if (isset($selectedRide)): ?>
                    <a href="?p=live_map" class="p-2.5 bg-slate-100 rounded-2xl text-slate-500 hover:text-primary transition active:scale-95 border border-slate-200">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                    </a>
                <?php endif; ?>
                <div>
                    <h3 class="text-2xl font-black text-slate-800 uppercase tracking-tighter italic">
                        <?php echo $selectedRide ? __('ride_tracking') . " #".$selectedRide['id'] : __('live_fleet'); ?>
                    </h3>
                    <p class="text-slate-400 text-[10px] font-black uppercase tracking-widest mt-1">
                        <?php echo $selectedRide ? htmlspecialchars($selectedRide['rider_name']) . " → " . htmlspecialchars($selectedRide['driver_name'] ?? __('searching')) : __('realtime_monitoring'); ?>
                    </p>
                </div>
            </div>
            
            <div class="flex items-center space-x-4">
                <!-- LIVE Indicator -->
                <div id="live-indicator" class="flex items-center space-x-2 bg-red-50 border border-red-200 rounded-2xl px-4 py-2">
                    <span class="live-dot"></span>
                    <span class="text-[10px] font-black text-red-500 uppercase tracking-widest">LIVE</span>
                </div>

                <!-- Night Mode Toggle -->
                <button id="night-mode-toggle" class="night-toggle-btn group" onclick="toggleNightMode()" title="Toggle Night Mode">
                    <span id="night-icon-sun" class="night-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                    </span>
                    <span id="night-icon-moon" class="night-icon hidden">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                        </svg>
                    </span>
                    <span id="night-label" class="text-[10px] font-black uppercase tracking-widest">Day</span>
                </button>

                <!-- Full Screen Toggle -->
                <button id="fullscreen-toggle" class="night-toggle-btn shadow-sm hover:shadow-md transition-all active:scale-95 px-3 py-2" onclick="toggleFullScreen()" title="Full Screen">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4" />
                    </svg>
                </button>

                <!-- Legend -->
                <div class="flex items-center space-x-4">
                    <div class="flex items-center">
                        <span class="w-2.5 h-2.5 rounded-full bg-blue-500 mr-2 shadow-[0_0_10px_rgba(59,130,246,0.5)]"></span>
                        <span class="text-[10px] font-black text-slate-500 uppercase tracking-widest"><?php echo __('waiting'); ?></span>
                    </div>
                    <div class="flex items-center">
                        <span class="w-2.5 h-2.5 rounded-full bg-green-500 mr-2 shadow-[0_0_10px_rgba(34,197,94,0.5)]"></span>
                        <span class="text-[10px] font-black text-slate-500 uppercase tracking-widest"><?php echo __('on_trip'); ?></span>
                    </div>
                    <div id="ws-status" class="flex items-center opacity-50">
                        <span class="w-2.5 h-2.5 rounded-full bg-slate-300 mr-2"></span>
                        <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Reverb: Off</span>
                    </div>
                </div>
            </div>
        </div>

        <div id="live-fleet-map" style="height: 650px;" class="rounded-[32px] border border-slate-50 shadow-inner z-10 box-border"></div>

        <?php if ($selectedRide): ?>
        <!-- Focused Ride Overlay -->
        <div class="absolute bottom-12 left-12 right-12 z-20 flex justify-center animate__animated animate__slideInUp">
            <div class="glass-card bg-white/90 backdrop-blur-xl p-6 rounded-[32px] border border-primary/10 shadow-2xl flex items-center space-x-8 max-w-4xl w-full">
                <!-- Rider Info -->
                <div class="flex items-center space-x-4 border-r border-slate-100 pr-8">
                    <div class="w-12 h-12 rounded-2xl bg-secondary/10 flex items-center justify-center text-primary font-black border border-secondary/20">
                        <?php echo strtoupper(substr($selectedRide['rider_name'] ?? 'R', 0, 1)); ?>
                    </div>
                    <div>
                        <p class="text-[10px] font-black uppercase text-slate-400 tracking-widest"><?php echo __('rider'); ?></p>
                        <p class="text-sm font-bold text-slate-800"><?php echo htmlspecialchars($selectedRide['rider_name']); ?></p>
                        <p class="text-[10px] font-bold text-primary"><?php echo htmlspecialchars($selectedRide['rider_phone']); ?></p>
                    </div>
                </div>

                <!-- Status & Route -->
                <div class="flex-1 text-center">
                    <div class="flex items-center justify-center space-x-3 mb-1">
                         <span class="text-[10px] font-bold text-slate-500 truncate max-w-[150px] uppercase tracking-tighter"><?php echo htmlspecialchars($selectedRide['pickup_address']); ?></span>
                         <div class="flex flex-col items-center px-4">
                             <div class="h-0.5 w-16 bg-slate-100 relative">
                                 <div class="absolute inset-0 bg-primary animate-progress-line"></div>
                             </div>
                             <span class="text-[8px] font-black text-primary mt-1 uppercase"><?php echo __($selectedRide['status']); ?></span>
                         </div>
                         <span class="text-[10px] font-bold text-slate-500 truncate max-w-[150px] uppercase tracking-tighter"><?php echo htmlspecialchars($selectedRide['dropoff_address']); ?></span>
                    </div>
                    <p class="text-[10px] font-black text-slate-300 uppercase tracking-[0.2em]"><?php echo $selectedRide['distance_text'] ?? 'Calculating...'; ?> • <?php echo $selectedRide['duration_text'] ?? '...'; ?></p>
                </div>

                <!-- Driver Info -->
                <div class="flex items-center space-x-4 border-l border-slate-100 pl-8">
                    <?php if ($selectedRide['driver_name']): ?>
                    <div>
                        <p class="text-[10px] font-black uppercase text-slate-400 tracking-widest text-right"><?php echo __('captain'); ?></p>
                        <p class="text-sm font-bold text-slate-800 text-right"><?php echo htmlspecialchars($selectedRide['driver_name']); ?></p>
                        <p class="text-[10px] font-bold text-green-500 text-right"><?php echo htmlspecialchars($selectedRide['driver_phone']); ?></p>
                    </div>
                    <div class="w-12 h-12 rounded-2xl bg-green-50 flex items-center justify-center text-green-500 font-black border border-green-100">
                        <?php echo strtoupper(substr($selectedRide['driver_name'], 0, 1)); ?>
                    </div>
                    <?php else: ?>
                    <div class="text-right">
                        <p class="text-[10px] font-black uppercase text-slate-300 tracking-widest"><?php echo __('searching_msg'); ?></p>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Price -->
                <div class="pl-8 flex flex-col items-end">
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1"><?php echo __('fare'); ?></p>
                    <p class="text-xl font-black text-slate-800 italic"><?php echo number_format($selectedRide['ride_price']); ?> <span class="text-xs font-bold text-slate-300 not-italic">SYP</span></p>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<style>
    @keyframes progress-line {
        0% { transform: scaleX(0); transform-origin: left; }
        50% { transform: scaleX(1); transform-origin: left; }
        51% { transform: scaleX(1); transform-origin: right; }
        100% { transform: scaleX(0); transform-origin: right; }
    }
    .animate-progress-line {
        animation: progress-line 2s infinite ease-in-out;
    }
    .ride-popup .leaflet-popup-content-wrapper {
        border-radius: 16px;
        padding: 0;
        overflow: hidden;
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(8px);
        border: 1px solid rgba(0, 51, 132, 0.1);
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    }
    .ride-popup .leaflet-popup-content {
        margin: 0;
    }
    .ride-popup .leaflet-popup-tip-container {
        display: none;
    }

    /* LIVE Pulsing Dot */
    @keyframes live-pulse {
        0% { box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.7); }
        70% { box-shadow: 0 0 0 8px rgba(239, 68, 68, 0); }
        100% { box-shadow: 0 0 0 0 rgba(239, 68, 68, 0); }
    }
    .live-dot {
        display: inline-block;
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background-color: #EF4444;
        animation: live-pulse 1.5s infinite;
    }

    /* Night Mode Toggle Button */
    .night-toggle-btn {
        display: flex;
        align-items: center;
        gap: 6px;
        padding: 8px 16px;
        border-radius: 16px;
        border: 1px solid #e2e8f0;
        background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
        cursor: pointer;
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        color: #64748b;
    }
    .night-toggle-btn:hover {
        border-color: #cbd5e1;
        box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        transform: translateY(-1px);
    }
    .night-toggle-btn.active {
        background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
        border-color: #334155;
        color: #fbbf24;
        box-shadow: 0 4px 20px rgba(15, 23, 42, 0.3);
    }
    .night-toggle-btn.active #night-label {
        color: #94a3b8;
    }
    .night-icon {
        display: flex;
        align-items: center;
        transition: all 0.3s ease;
    }

    /* Smooth marker transitions */
    .leaflet-marker-icon {
        transition: transform 0.8s cubic-bezier(0.25, 0.46, 0.45, 0.94) !important;
    }
    .smooth-marker {
        transition: all 0.8s cubic-bezier(0.25, 0.46, 0.45, 0.94);
    }

    /* Fullscreen map styles */
    .fullscreen-map {
        width: 100vw !important;
        height: 100vh !important;
        position: fixed !important;
        top: 0 !important;
        left: 0 !important;
        z-index: 9999 !important;
        border-radius: 0 !important;
    }
</style>

<script>
    // Night mode state
    let isNightMode = false;
    let lightTileLayer = null;
    let darkTileLayer = null;
    let mapInstance = null;

    function toggleNightMode() {
        isNightMode = !isNightMode;
        const btn = document.getElementById('night-mode-toggle');
        const sunIcon = document.getElementById('night-icon-sun');
        const moonIcon = document.getElementById('night-icon-moon');
        const label = document.getElementById('night-label');

        if (isNightMode) {
            btn.classList.add('active');
            sunIcon.classList.add('hidden');
            moonIcon.classList.remove('hidden');
            label.textContent = 'Night';
            if (mapInstance) {
                mapInstance.removeLayer(lightTileLayer);
                darkTileLayer.addTo(mapInstance);
            }
        } else {
            btn.classList.remove('active');
            sunIcon.classList.remove('hidden');
            moonIcon.classList.add('hidden');
            label.textContent = 'Day';
            if (mapInstance) {
                mapInstance.removeLayer(darkTileLayer);
                lightTileLayer.addTo(mapInstance);
            }
        }
    }

    function toggleFullScreen() {
        const mapContainer = document.getElementById('live-fleet-map');
        if (!document.fullscreenElement) {
            if (mapContainer.requestFullscreen) {
                mapContainer.requestFullscreen();
            } else if (mapContainer.webkitRequestFullscreen) { /* Safari */
                mapContainer.webkitRequestFullscreen();
            } else if (mapContainer.msRequestFullscreen) { /* IE11 */
                mapContainer.msRequestFullscreen();
            }
        } else {
            if (document.exitFullscreen) {
                document.exitFullscreen();
            }
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        // Initialize Map
        const map = L.map('live-fleet-map', {
            zoomControl: false
        }).setView([33.5138, 36.2765], 13);
        
        mapInstance = map;
        L.control.zoom({ position: 'bottomright' }).addTo(map);

        // Tile Layers (Day & Night)
        lightTileLayer = L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png', {
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(map);

        darkTileLayer = L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', {
            attribution: '&copy; OpenStreetMap contributors'
        });

        // Configuration
        const selectedRide = <?php echo json_encode($selectedRide); ?>;
        const onlineDrivers = <?php echo json_encode($onlineDrivers); ?>;
        const activeRides = <?php echo json_encode($activeRides ?? []); ?>;

        const driverMarkers = {};
        const journeyLayers = {}; // To store polylines/markers for each active ride

        // --- Global Fleet Display ---
        function initFleet() {
            // 1. Render all online drivers
            onlineDrivers.forEach(d => {
                const isFocused = selectedRide && d.id == selectedRide.driver_id;
                const lat = parseFloat(d.lat);
                const lng = parseFloat(d.lng);
                
                // Color: Green if on trip, Blue if waiting
                const color = d.ride_status ? '#10B981' : '#3B82F6';
                updateDriverMarker(d.id, lat, lng, color, d.name, isFocused);
            });

            // 2. Render all active journeys
            activeRides.forEach(ride => {
                renderJourney(ride);
            });

            // 3. Special focus for selected ride
            if (selectedRide) {
                const isActive = activeRides.some(r => r.id == selectedRide.id);
                if (!isActive) {
                    renderJourney(selectedRide);
                }

                const pLat = parseFloat(selectedRide.pickup_lat);
                const pLng = parseFloat(selectedRide.pickup_lng);
                const dLat = parseFloat(selectedRide.dropoff_lat);
                const dLng = parseFloat(selectedRide.dropoff_lng);
                
                const bounds = L.latLngBounds([[pLat, pLng], [dLat, dLng]]);
                map.fitBounds(bounds, { padding: [100, 100] });
            }
        }

        function renderJourney(ride) {
            const id = ride.id;
            if (journeyLayers[id]) return; // Already rendered

            const pLat = parseFloat(ride.pickup_lat || ride.pickup_latitude);
            const pLng = parseFloat(ride.pickup_lng || ride.pickup_longitude);
            const dLat = parseFloat(ride.dropoff_lat || ride.dropoff_latitude);
            const dLng = parseFloat(ride.dropoff_lng || ride.dropoff_longitude);

            if (!pLat || !pLng || !dLat || !dLng) return;

            // 1. Custom Markers
            const pMarker = L.marker([pLat, pLng], { icon: getIcon('#003384', 'pickup') });
            pMarker.bindTooltip(`Rider: ${ride.rider_name} (Pickup)`, { direction: 'top' });

            const dMarker = L.marker([dLat, dLng], { icon: getIcon('#E74C3C', 'dropoff') });
            dMarker.bindTooltip(`Rider: ${ride.rider_name} (Dropoff)`, { direction: 'top' });

            // 2. Real Road Routing
            const routingControl = L.Routing.control({
                waypoints: [
                    L.latLng(pLat, pLng),
                    L.latLng(dLat, dLng)
                ],
                routeWhileDragging: false,
                addWaypoints: false,
                draggableWaypoints: false,
                fitSelectedRoutes: false,
                show: false, // Hide instruction panel
                createMarker: function() { return null; }, // Use our custom markers instead
                lineOptions: {
                    styles: [{ color: '#003384', opacity: 0.5, weight: 4, dashArray: '10, 10' }]
                }
            }).addTo(map);

            const layerGroup = L.layerGroup([pMarker, dMarker]).addTo(map);
            
            // Store both the layer group and the routing control for removal later
            journeyLayers[id] = {
                group: layerGroup,
                routing: routingControl
            };
        }

        // --- WebSocket Handlers ---
        const pusher = new Pusher('<?php echo REVERB_APP_KEY; ?>', {
            wsHost: '<?php echo REVERB_HOST; ?>',
            wsPort: <?php echo REVERB_PORT; ?>,
            forceTLS: false,
            disableStats: true,
            enabledTransports: ['ws', 'wss'],
            cluster: 'mt1'
        });

        const statusEl = document.getElementById('ws-status');
        pusher.connection.bind('connected', () => {
            statusEl.querySelector('span:first-child').classList.replace('bg-slate-300', 'bg-green-500');
            statusEl.querySelector('span:last-child').innerText = 'Reverb: ON';
            statusEl.classList.remove('opacity-50');
        });

        const channel = pusher.subscribe('admin');

        // ✅ FIXED: Match the exact broadcastAs() name from backend
        // Backend DriverLocationUpdated::broadcastAs() returns 'driverLocationUpdated'
        channel.bind('driverLocationUpdated', function(data) {
            console.log('[LIVE_MAP] driverLocationUpdated:', data);
            const rideId = data.ride_id;
            // For ride-specific tracking, use ride_id to find the driver
            if (selectedRide && rideId == selectedRide.id && selectedRide.driver_id) {
                smoothMoveMarker(selectedRide.driver_id, data.latitude, data.longitude, '#10B981', '', true);
            }
            // Also try to update by ride_id as a fallback key
            smoothMoveMarker('ride_' + rideId, data.latitude, data.longitude, '#10B981', '', false);
        });

        // Global location updates (from AuthController - driver going online/moving)
        channel.bind('driver.global_location.updated', function(data) {
            console.log('[LIVE_MAP] global_location.updated:', data);
            const color = data.is_online ? (data.has_active_ride ? '#10B981' : '#3B82F6') : '#94A3B8';
            const isFocused = selectedRide && data.driver_id == selectedRide.driver_id;
            smoothMoveMarker(data.driver_id, data.latitude, data.longitude, color, '', isFocused);
        });

        // Ride Status Updates (To catch new journeys dynamically)
        channel.bind('ride.status.updated', function(data) {
             // If a ride becomes active, draw it
             if (['accepted', 'arrived', 'started'].includes(data.status)) {
                 renderJourney(data.ride);
             } else if (['completed', 'cancelled'].includes(data.status)) {
                 // Remove journey layer if exists
                 if (journeyLayers[data.ride.id]) {
                     map.removeLayer(journeyLayers[data.ride.id].group);
                     journeyLayers[data.ride.id].routing.remove();
                     delete journeyLayers[data.ride.id];
                 }
             }
        });

        // --- Smooth Marker Animation ---
        function smoothMoveMarker(id, lat, lng, color, name = '', isFocused = false) {
            if (!lat || !lng) return;
            lat = parseFloat(lat);
            lng = parseFloat(lng);

            if (driverMarkers[id]) {
                // Animate: use Leaflet's internal DOM for smooth CSS transition
                const marker = driverMarkers[id];
                const newLatLng = L.latLng(lat, lng);
                
                // Smooth slide using requestAnimationFrame
                const startLatLng = marker.getLatLng();
                const duration = 800; // ms
                const startTime = performance.now();

                function animate(currentTime) {
                    const elapsed = currentTime - startTime;
                    const progress = Math.min(elapsed / duration, 1);
                    // Ease out cubic
                    const ease = 1 - Math.pow(1 - progress, 3);
                    
                    const currentLat = startLatLng.lat + (lat - startLatLng.lat) * ease;
                    const currentLng = startLatLng.lng + (lng - startLatLng.lng) * ease;
                    
                    marker.setLatLng([currentLat, currentLng]);
                    
                    if (progress < 1) {
                        requestAnimationFrame(animate);
                    }
                }
                requestAnimationFrame(animate);

                marker.setIcon(getDriverIcon(color, isFocused));
                
                // Pan map to follow focused driver
                if (isFocused) {
                    map.panTo([lat, lng], { animate: true, duration: 0.8 });
                }
            } else {
                // Create new marker
                updateDriverMarker(id, lat, lng, color, name, isFocused);
            }
        }

        function updateDriverMarker(id, lat, lng, color, name = '', isFocused = false) {
            if (!lat || !lng) return;
            
            if (driverMarkers[id]) {
                driverMarkers[id].setLatLng([lat, lng]);
                driverMarkers[id].setIcon(getDriverIcon(color, isFocused));
            } else {
                driverMarkers[id] = L.marker([lat, lng], { 
                    icon: getDriverIcon(color, isFocused) 
                }).addTo(map);
                
                if (name) {
                    driverMarkers[id].bindTooltip(name, { 
                        permanent: isFocused, 
                        direction: 'top',
                        className: 'font-black text-[8px] uppercase tracking-widest bg-white border-2 border-primary/20 rounded-lg px-2 text-primary'
                    });
                }
            }
        }

        function getDriverIcon(color, isBig = false) {
            const size = isBig ? 100 : 80;
            const shadow = 'drop-shadow(0 0 10px ' + color + ')';
            
            return L.divIcon({
                className: 'custom-div-icon',
                html: `<div style="filter: ${shadow}; transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1); display: flex; align-items: center; justify-content: center;">
                        <img src="images/car1.png" style="width: ${size}px; height: auto;" alt="Car">
                       </div>`,
                iconSize: [size, size],
                iconAnchor: [size/2, size/2]
            });
        }

        function getIcon(color, type = 'dot') {
            let html = '';
            if (type === 'dot') {
                html = `<div style="background-color: ${color}; width: 16px; height: 16px; border: 3px solid white; border-radius: 50%; box-shadow: 0 0 15px ${color}88;"></div>`;
            } else if (type === 'pickup') {
                html = `<div class="bg-primary p-1.5 rounded-xl border-2 border-white shadow-xl shadow-primary/30 flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                            </svg>
                        </div>`;
            } else if (type === 'dropoff') {
                html = `<div class="bg-red-500 p-1.5 rounded-xl border-2 border-white shadow-xl shadow-red-500/30 flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                        </div>`;
            }
            return L.divIcon({
                className: 'custom-div-icon',
                html: html,
                iconSize: [32, 32],
                iconAnchor: [16, 16],
                popupAnchor: [0, -20]
            });
        }

        // --- Polling Fallback (every 5s) ---
        // Ensures markers move even if WebSocket has issues
        if (selectedRide && selectedRide.driver_id) {
            setInterval(async () => {
                try {
                    const resp = await fetch(`ajax/driver_location.php?ride_id=${selectedRide.id}`);
                    if (resp.ok) {
                        const data = await resp.json();
                        if (data.latitude && data.longitude) {
                            smoothMoveMarker(selectedRide.driver_id, data.latitude, data.longitude, '#10B981', '', true);
                        }
                    }
                } catch (e) {
                    // Silently ignore polling errors
                }
            }, 5000);
        }

        // Initialize!
        initFleet();
    });
</script>
