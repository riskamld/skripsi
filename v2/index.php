<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Pencarian Tempat</title>
<link rel="manifest" href="manifest.json">

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" rel="stylesheet">
<link href="https://unpkg.com/leaflet.locatecontrol@0.79.0/dist/L.Control.Locate.min.css" rel="stylesheet">

<style>
body {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    min-height: 100vh;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

.container-fluid {
    background: rgba(255, 255, 255, 0.95);
    border-radius: 15px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    margin: 20px auto;
    max-width: 1400px;
    padding: 20px;
}

#map {
    height: 420px;
    border-radius: 10px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.2);
}

.card-img-top {
    height:160px;
    object-fit:cover;
    border-radius: 10px 10px 0 0;
}

.card-body {
    min-height: 120px;
    background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
}

.card {
    border: none;
    border-radius: 15px;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    overflow: hidden;
}

.card:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 35px rgba(0,0,0,0.2);
}

.btn-primary {
    background: linear-gradient(45deg, #007bff, #0056b3);
    border: none;
    border-radius: 25px;
    font-weight: bold;
    transition: all 0.3s ease;
}

.btn-primary:hover {
    background: linear-gradient(45deg, #0056b3, #004085);
    transform: scale(1.05);
}

.btn-success {
    background: linear-gradient(45deg, #28a745, #20c997);
    border: none;
    border-radius: 20px;
    font-weight: bold;
}

.form-control {
    border-radius: 25px;
    border: 2px solid #ddd;
    transition: border-color 0.3s ease;
}

.form-control:focus {
    border-color: #007bff;
    box-shadow: 0 0 10px rgba(0,123,255,0.3);
}

#count {
    font-size: 1.2rem;
    text-align: center;
    padding: 10px;
    background: rgba(0,123,255,0.1);
    border-radius: 10px;
    margin-bottom: 20px;
}

#driving-mode-btn {
    position: absolute;
    top: 10px;
    right: 40px;
    z-index: 1000;
    background: white;
    border: 2px solid #28a745;
    border-radius: 50%;
    width: 40px;
    height: 40px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 2px 10px rgba(0,0,0,0.2);
}

#driving-mode-btn:hover {
    background: #f8f9fa;
}

#driving-mode-btn.active {
    background: #28a745;
    color: white;
}

.result-card.selected {
    border: 3px solid #007bff !important;
    box-shadow: 0 0 15px rgba(0, 123, 255, 0.6) !important;
    transform: scale(1.02) !important;
}

.marker-container {
    position: relative;
    width: 35px;
    height: 35px;
}

.marker-icon {
    font-size: 24px;
    position: absolute;
    bottom: 0;
    left: 50%;
    transform: translateX(-50%);
}

.review-badge {
    position: absolute;
    top: -5px;
    right: -5px;
    background: #ff6b35;
    color: white;
    border-radius: 50%;
    width: 18px;
    height: 18px;
    font-size: 10px;
    font-weight: bold;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 2px solid white;
    box-shadow: 0 1px 3px rgba(0,0,0,0.3);
}

.custom-marker {
    background: none !important;
    border: none !important;
}
</style>
</head>

<body>

<div class="container-fluid p-3">

<div class="row mb-3">
    <div class="col-md-4">
        <input id="keyword" class="form-control" placeholder="Cari: toko buah, apotek, dll">
    </div>
    <div class="col-md-2">
        <button class="btn btn-primary w-100" onclick="search()">🔍 Search</button>
    </div>
</div>

<div id="map" class="mb-4"></div>

<div id="count" class="mb-3 fw-bold text-primary"></div>

<div id="result" class="row g-3"></div>

</div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://unpkg.com/leaflet.locatecontrol@0.79.0/dist/L.Control.Locate.min.js"></script>

<script>
let map = L.map('map').setView([-8.1727,113.6995], 13); // Jember

let osm = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '© OpenStreetMap'
});

let satellite = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
    attribution: 'Tiles &copy; Esri &mdash; Source: Esri, i-cubed, USDA, USGS, AEX, GeoEye, Getmapping, Aerogrid, IGN, IGP, UPR-EGP, and the GIS User Community'
});

let satelliteLabels = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/Reference/World_Transportation/MapServer/tile/{z}/{y}/{x}', {
    attribution: 'Tiles &copy; Esri &mdash; Source: Esri',
    opacity: 0.8
});

let placeLabels = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/Reference/World_Boundaries_and_Places/MapServer/tile/{z}/{y}/{x}', {
    attribution: 'Tiles &copy; Esri &mdash; Source: Esri',
    opacity: 0.9
});

let satelliteWithLabels = L.layerGroup([satellite, satelliteLabels, placeLabels]);

let baseLayers = {
    "Normal": osm,
    "Satellite": satellite,
    "Satellite with Labels": satelliteWithLabels
};

L.control.layers(baseLayers).addTo(map);

// Default to Satellite
satellite.addTo(map);

let markers = [];
let currentLocationMarker = null;
let drivingMode = false;
let watchId = null;
let searchInterval = null;
let selectedMarker = null;

// Function to format review count
function formatReviewCount(count) {
    if (count >= 1000) {
        return (count / 1000).toFixed(1).replace('.0', '') + 'K';
    }
    return count.toString();
}

// Function to get badge size based on content length
function getBadgeSize(content) {
    const length = content.length;
    if (length <= 2) return { width: 18, height: 18, fontSize: 10 };
    if (length <= 3) return { width: 22, height: 18, fontSize: 9 };
    return { width: 26, height: 18, fontSize: 8 };
}

function search(){
    let keyword = document.getElementById('keyword').value.trim();
    if(!keyword) return; // Skip if no keyword

    let b = map.getBounds();

    let url = `search.php?keyword=${encodeURIComponent(keyword)}&nelat=${b.getNorth()}&nelng=${b.getEast()}&swlat=${b.getSouth()}&swlng=${b.getWest()}`;

    fetch(url)
      .then(r=>r.json())
      .then(data=>{
        document.getElementById('result').innerHTML = `<div class="col-12 mb-3"><div class="alert alert-info text-center fw-bold">Menampilkan ${data.length} hasil</div></div>`;

        // Remove all markers except the selected one
        markers.forEach(m => {
            if (m !== selectedMarker) {
                map.removeLayer(m);
            }
        });

        // Filter out non-selected markers from the array
        markers = markers.filter(m => m === selectedMarker);

        data.forEach(p=>{
            // WA LINK
            let wa = '';
            if(p.telepon){
                let no = p.telepon.replace(/\D/g, '').replace(/^0+/, '');
                wa = `https://wa.me/62${no}`;
            }

            // Format review count
            const formattedReviews = formatReviewCount(p.ulasan);
            const badgeSize = getBadgeSize(formattedReviews);

            // MARKER with review badge
            let popupContent = `<b>${p.nama}</b><br>${p.ulasan} ulasan`;
            if(p.telepon) popupContent += `<br>📞 ${p.telepon}`;
            popupContent += `<br><a href="https://maps.google.com/maps?q=${p.lat},${p.lng}" target="_blank">🗺️ Navigasi</a>`;
            popupContent += `<br><a href="https://www.google.com/maps/place/?q=place_id:${p.id}" target="_blank">⭐ Lihat Ulasan</a>`;

            // Create marker with review badge
            let markerIcon = L.divIcon({
                className: 'custom-marker',
                html: `<div class="marker-container">
                          <div class="marker-icon">🎯</div>
                          <div class="review-badge" style="width: ${badgeSize.width}px; height: ${badgeSize.height}px; font-size: ${badgeSize.fontSize}px;">${formattedReviews}</div>
                       </div>`,
                iconSize: [35, 35],
                iconAnchor: [17, 35],
                popupAnchor: [0, -35]
            });

            let m = L.marker([p.lat,p.lng], {icon: markerIcon}).addTo(map)
                    .bindPopup(popupContent);
            markers.push(m);

            // CARD
            let waBtn = wa ? `<a href="${wa}" target="_blank" class="btn btn-success btn-sm w-100 mt-2">📞 WhatsApp</a>` : '';
            let cardId = `card-${markers.length - 1}`;
            document.getElementById('result').innerHTML += `
            <div class="col-lg-3 col-md-4 col-sm-6">
                <div class="card h-100 shadow-sm result-card" id="${cardId}" data-lat="${p.lat}" data-lng="${p.lng}" data-marker-index="${markers.length - 1}">
                    <img src="${p.foto}" class="card-img-top">
                    <div class="card-body">
                        <b>${p.nama}</b><br>
                        ⭐ ${p.rating} (${p.ulasan})<br>
                        <small>${p.alamat}</small>
                        ${waBtn}
                    </div>
                </div>
            </div>`;
        });

      });
}

// Add current location control
let locateControl = L.control.locate({
    position: 'topright',
    strings: {
        title: "Tampilkan lokasi saya"
    },
    locateOptions: {
        enableHighAccuracy: true,
        watch: true,
        maximumAge: 10000,
        timeout: 10000
    },
    onLocationError: function(err) {
        alert("Tidak dapat mengakses lokasi: " + err.message);
    }
}).addTo(map);

// Store original locate control function
const originalOnLocationFound = locateControl._onLocationFound;

// Override locate control to handle driving mode
locateControl._onLocationFound = function(e) {
    // Call original function first
    originalOnLocationFound.call(this, e);

    // Store current location marker reference
    if (this._marker) {
        currentLocationMarker = this._marker;
    }

    // Additional handling for driving mode
    if (drivingMode && this.options.setView) {
        // In driving mode, don't change zoom level, just center
        this._map.setView(e.latlng, this._map.getZoom());
    }
};

// Add driving mode button
const drivingModeBtn = document.createElement('div');
drivingModeBtn.id = 'driving-mode-btn';
drivingModeBtn.innerHTML = '🚗';
drivingModeBtn.title = 'Mode Berkendara';
drivingModeBtn.onclick = toggleDrivingMode;
document.body.appendChild(drivingModeBtn);

function toggleDrivingMode() {
    drivingMode = !drivingMode;
    const btn = document.getElementById('driving-mode-btn');

    if (drivingMode) {
        btn.classList.add('active');
        btn.innerHTML = '🛑';
        btn.title = 'Stop Mode Berkendara';

        // Start continuous location tracking
        if (navigator.geolocation) {
            watchId = navigator.geolocation.watchPosition(function(position) {
                const latlng = L.latLng(position.coords.latitude, position.coords.longitude);

                // Update current location marker
                if (currentLocationMarker) {
                    map.removeLayer(currentLocationMarker);
                }
                currentLocationMarker = L.marker(latlng, {
                    icon: L.divIcon({
                        className: 'current-location-icon',
                        html: '📍',
                        iconSize: [30, 30],
                        iconAnchor: [15, 30]
                    })
                }).addTo(map);

                // Center map on current location
                map.setView(latlng, map.getZoom());

                // Search for places in current area
                if (document.getElementById('keyword').value.trim()) {
                    search();
                }

            }, function(error) {
                console.error('GPS Error:', error);
                alert('GPS Error: ' + error.message);
            }, {
                enableHighAccuracy: true,
                timeout: 10000,
                maximumAge: 5000
            });
        }

        // Auto search every 30 seconds
        searchInterval = setInterval(function() {
            if (document.getElementById('keyword').value.trim()) {
                search();
            }
        }, 30000);

    } else {
        btn.classList.remove('active');
        btn.innerHTML = '🚗';
        btn.title = 'Mode Berkendara';

        // Stop location tracking
        if (watchId) {
            navigator.geolocation.clearWatch(watchId);
            watchId = null;
        }

        // Stop auto search
        if (searchInterval) {
            clearInterval(searchInterval);
            searchInterval = null;
        }
    }
}

// Add click event for result cards
document.addEventListener('click', function(e) {
    if (e.target.closest('.result-card')) {
        const card = e.target.closest('.result-card');
        const markerIndex = parseInt(card.dataset.markerIndex);

        // Reset previously selected marker
        if (selectedMarker) {
            selectedMarker.setIcon(L.icon({
                iconUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-icon.png',
                shadowUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-shadow.png',
                iconSize: [25, 41],
                iconAnchor: [12, 41],
                popupAnchor: [1, -34],
                shadowSize: [41, 41]
            }));
        }

        // Set new selected marker
        if (markers[markerIndex]) {
            const marker = markers[markerIndex];
            selectedMarker = marker;

            // Create selected marker icon (different color/size)
            const selectedIcon = L.divIcon({
                className: 'selected-marker',
                html: '🏷️',
                iconSize: [35, 35],
                iconAnchor: [17, 35],
                popupAnchor: [0, -35]
            });

            marker.setIcon(selectedIcon);

            // Pan and zoom to the marker
            map.setView(marker.getLatLng(), 16);

            // Open popup
            marker.openPopup();

            // Highlight the card
            document.querySelectorAll('.result-card').forEach(c => {
                c.classList.remove('selected');
            });
            card.classList.add('selected');
        }
    }
});

// Change cursor when hovering over markers
map.on('mouseover', function(e) {
    const target = e.originalEvent.target;
    if (target && target.closest && target.closest('.leaflet-marker-icon')) {
        document.body.style.cursor = 'pointer';
    }
});

map.on('mouseout', function(e) {
    document.body.style.cursor = '';
});

// Auto search on map move (only when not in driving mode)
map.on('moveend', function() {
    if (!drivingMode) {
        search();
    }
});
</script>

</body>
</html>
