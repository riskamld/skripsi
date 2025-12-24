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

.status-indicator {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 20px;
    height: 20px;
    border-radius: 50%;
    font-size: 12px;
    margin-right: 5px;
}

.status-indicator.operational {
    background: #28a745;
    color: white;
}

.card-rating-excellent {
    border-left: 4px solid #007bff !important; /* Biru - sangat bagus */
}

.card-rating-good {
    border-left: 4px solid #28a745 !important; /* Hijau - bagus */
}

.card-rating-average {
    border-left: 4px solid #fd7e14 !important; /* Orange - biasa */
}

.card-rating-poor {
    border-left: 4px solid #dc3545 !important; /* Merah - buruk */
}

/* Photo counter styling */
.photo-counter {
    position: absolute;
    bottom: 10px;
    right: 10px;
    background: rgba(0, 0, 0, 0.7);
    color: white;
    padding: 4px 8px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: bold;
    z-index: 10;
}

/* Carousel positioning for counter */
.carousel-item {
    position: relative;
}

/* Weather info styling */
.weather-info {
    font-weight: 500;
    color: #2c3e50;
    display: inline-flex;
    align-items: center;
    gap: 4px;
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

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://unpkg.com/leaflet.locatecontrol@0.79.0/dist/L.Control.Locate.min.js"></script>
<script src="https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.js"></script>
<link rel="stylesheet" href="https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.css" />

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

// Add geocoder control for search functionality
L.Control.geocoder({
    defaultMarkGeocode: false,
    position: 'topleft',
    placeholder: 'Cari alamat, tempat, atau koordinat...'
}).on('markgeocode', function(e) {
    // When a search result is selected, zoom to that location
    const bbox = e.geocode.bbox;
    const poly = L.polygon([
        bbox.getSouthEast(),
        bbox.getNorthEast(),
        bbox.getNorthWest(),
        bbox.getSouthWest()
    ]);
    map.fitBounds(poly.getBounds());

    // Add a marker at the result location
    L.marker(e.geocode.center, {
        icon: L.icon({
            iconUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-icon.png',
            shadowUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-shadow.png',
            iconSize: [25, 41],
            iconAnchor: [12, 41],
            popupAnchor: [1, -34]
        })
    }).addTo(map)
    .bindPopup(`<strong>${e.geocode.name}</strong><br>${e.geocode.html || ''}`)
    .openPopup();
}).addTo(map);

// Add reverse geocoding on map click
map.on('click', function(e) {
    // Only do reverse geocoding if not clicking on existing markers
    const clickedOnMarker = e.originalEvent.target.closest('.leaflet-marker-icon');
    if (!clickedOnMarker) {
        // Use Nominatim for reverse geocoding (free)
        fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${e.latlng.lat}&lon=${e.latlng.lng}&zoom=18&addressdetails=1`)
            .then(response => response.json())
            .then(data => {
                if (data && data.display_name) {
                    // Show popup with address
                    L.popup()
                        .setLatLng(e.latlng)
                        .setContent(`
                            <div style="max-width: 200px;">
                                <strong>📍 Lokasi:</strong><br>
                                ${data.display_name}<br><br>
                                <strong>📌 Koordinat:</strong><br>
                                ${e.latlng.lat.toFixed(6)}, ${e.latlng.lng.toFixed(6)}
                            </div>
                        `)
                        .openOn(map);
                }
            })
            .catch(error => {
                console.error('Reverse geocoding error:', error);
            });
    }
});

// Default to Satellite
satellite.addTo(map);

let markers = [];
let currentLocationMarker = null;
let drivingMode = false;
let watchId = null;
let searchInterval = null;
let selectedMarker = null;

// Function to format business status
function formatBusinessStatus(status) {
    switch(status) {
        case 'OPERATIONAL':
            return { text: '🟢', class: 'status-indicator operational', showText: false };
        case 'CLOSED_TEMPORARILY':
            return { text: '🟡 Tutup Sementara', class: 'badge bg-warning', showText: true };
        case 'CLOSED_PERMANENTLY':
            return { text: '🔴 Tutup Permanen', class: 'badge bg-danger', showText: true };
        default:
            return { text: '⚪ Tidak Diketahui', class: 'badge bg-secondary', showText: true };
    }
}

// Function to format category
function formatCategory(category) {
    if (!category) return '';
    // Convert common types to readable format
    const typeMap = {
        'restaurant': '🍽️ Restoran',
        'gas_station': '⛽ SPBU',
        'store': '🏪 Toko',
        'hospital': '🏥 Rumah Sakit',
        'pharmacy': '💊 Apotek',
        'hotel': '🏨 Hotel',
        'cafe': '☕ Cafe',
        'bank': '🏦 Bank',
        'school': '🏫 Sekolah',
        'park': '🌳 Taman'
    };
    return typeMap[category] || `🏢 ${category.charAt(0).toUpperCase() + category.slice(1).replace('_', ' ')}`;
}

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

// Function to get badge color based on review count
function getBadgeColor(reviewCount) {
    if (reviewCount >= 1000) return '#dc3545'; // Merah - sangat populer
    if (reviewCount >= 100) return '#007bff';  // Biru - populer
    if (reviewCount >= 10) return '#28a745';   // Hijau - cukup populer
    return '#6c757d'; // Abu-abu - kurang populer
}

// Function to get card class based on rating
function getCardClass(rating) {
    if (rating >= 4.6) return 'card-rating-excellent'; // 4.6-5 - Biru
    if (rating >= 4.1) return 'card-rating-good';       // 4.1-4.5 - Hijau
    if (rating >= 3.1) return 'card-rating-average';    // 3.1-4.0 - Orange
    return 'card-rating-poor'; // 1-3 - Merah
}

// Function to get star color based on rating
function getStarColor(rating) {
    if (rating >= 4.6) return '#007bff'; // Biru - excellent
    if (rating >= 4.1) return '#28a745'; // Hijau - good
    if (rating >= 3.1) return '#fd7e14'; // Orange - average
    return '#dc3545'; // Merah - poor
}

// Function to fetch weather data from Open-Meteo
async function getWeatherData(lat, lng) {
    try {
        const response = await fetch(`https://api.open-meteo.com/v1/forecast?latitude=${lat}&longitude=${lng}&current_weather=true&timezone=Asia/Jakarta`);
        const data = await response.json();

        if (data.current_weather) {
            const weather = data.current_weather;
            return {
                temperature: Math.round(weather.temperature),
                weathercode: weather.weathercode,
                windspeed: Math.round(weather.windspeed),
                condition: getWeatherCondition(weather.weathercode),
                icon: getWeatherIcon(weather.weathercode)
            };
        }
    } catch (error) {
        console.error('Weather API error:', error);
    }
    return null;
}

// Function to get weather condition text from weathercode
function getWeatherCondition(weathercode) {
    const conditions = {
        0: 'Cerah',
        1: 'Sebagian Berawan',
        2: 'Sebagian Berawan',
        3: 'Berawan',
        45: 'Berkabut',
        48: 'Berkabut',
        51: 'Gerimis Ringan',
        53: 'Gerimis',
        55: 'Gerimis Lebat',
        56: 'Gerimis Beku',
        57: 'Gerimis Beku Lebat',
        61: 'Hujan Ringan',
        63: 'Hujan',
        65: 'Hujan Lebat',
        66: 'Hujan Beku',
        67: 'Hujan Beku Lebat',
        71: 'Salju Ringan',
        73: 'Salju',
        75: 'Salju Lebat',
        77: 'Butiran Salju',
        80: 'Hujan Ringan',
        81: 'Hujan',
        82: 'Hujan Lebat',
        85: 'Salju Ringan',
        86: 'Salju Lebat',
        95: 'Badai',
        96: 'Badai dengan Hail',
        99: 'Badai dengan Hail'
    };
    return conditions[weathercode] || 'Tidak diketahui';
}

// Function to get weather icon emoji
function getWeatherIcon(weathercode) {
    if (weathercode === 0) return '☀️'; // Clear sky
    if (weathercode >= 1 && weathercode <= 3) return '⛅'; // Partly cloudy
    if (weathercode >= 45 && weathercode <= 48) return '🌫️'; // Foggy
    if (weathercode >= 51 && weathercode <= 67) return '🌦️'; // Rainy
    if (weathercode >= 71 && weathercode <= 86) return '❄️'; // Snowy
    if (weathercode >= 95) return '⛈️'; // Thunderstorm
    return '🌤️'; // Default
}

// Function to calculate Haversine distance between two points
function calculateDistance(lat1, lon1, lat2, lon2) {
    const R = 6371; // Earth's radius in kilometers
    const dLat = (lat2 - lat1) * Math.PI / 180;
    const dLon = (lon2 - lon1) * Math.PI / 180;
    const a =
        Math.sin(dLat/2) * Math.sin(dLat/2) +
        Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
        Math.sin(dLon/2) * Math.sin(dLon/2);
    const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
    const distance = R * c;
    return distance;
}

// Function to estimate travel time
function estimateTravelTime(distanceKm) {
    // Average speeds for Indonesian cities
    const carSpeed = 35; // km/h (accounting for traffic)
    const motorcycleSpeed = 45; // km/h (more flexible)

    const carTimeMinutes = Math.round((distanceKm / carSpeed) * 60);
    const motorcycleTimeMinutes = Math.round((distanceKm / motorcycleSpeed) * 60);

    return {
        car: carTimeMinutes,
        motorcycle: motorcycleTimeMinutes
    };
}

// Function to format distance and time display
function formatDistanceTime(distanceKm, times) {
    const distance = distanceKm < 1 ?
        `${(distanceKm * 1000).toFixed(0)}m` :
        `${distanceKm.toFixed(1)} km`;

    const carTime = times.car < 1 ? '<1' : times.car;
    const motorcycleTime = times.motorcycle < 1 ? '<1' : times.motorcycle;

    return `📍 ${distance} • 🚗 ~${carTime} min • 🏍️ ~${motorcycleTime} min`;
}

async function search(){
    let keyword = document.getElementById('keyword').value.trim();
    if(!keyword) return; // Skip if no keyword

    let b = map.getBounds();

    let url = `search.php?keyword=${encodeURIComponent(keyword)}&nelat=${b.getNorth()}&nelng=${b.getEast()}&swlat=${b.getSouth()}&swlng=${b.getWest()}`;

    fetch(url)
      .then(r=>r.json())
      .then(async data=>{

        // Get current user location for distance calculation
        let userLocation = null;
        if (currentLocationMarker) {
            userLocation = currentLocationMarker.getLatLng();
        } else if (navigator.geolocation) {
            // Try to get current location if not available
            try {
                const position = await new Promise((resolve, reject) => {
                    navigator.geolocation.getCurrentPosition(resolve, reject, {
                        enableHighAccuracy: true,
                        timeout: 5000,
                        maximumAge: 300000 // 5 minutes
                    });
                });
                userLocation = L.latLng(position.coords.latitude, position.coords.longitude);
            } catch (error) {
                console.log('Could not get user location for distance calculation');
            }
        }
        document.getElementById('result').innerHTML = `<div class="col-12 mb-3"><div class="alert alert-info text-center fw-bold">Menampilkan ${data.length} hasil</div></div>`;

        // Remove all markers except the selected one
        markers.forEach(m => {
            if (m !== selectedMarker) {
                map.removeLayer(m);
            }
        });

        // Filter out non-selected markers from the array
        markers = markers.filter(m => m === selectedMarker);

        // Process places with weather and distance data
        const placePromises = data.map(async (p) => {
            // Fetch weather data for this location
            const weatherData = await getWeatherData(p.lat, p.lng);

            // Calculate distance and time if user location is available
            let distanceInfo = null;
            if (userLocation) {
                const distance = calculateDistance(userLocation.lat, userLocation.lng, p.lat, p.lng);
                const travelTimes = estimateTravelTime(distance);
                distanceInfo = {
                    distance: distance,
                    times: travelTimes,
                    formatted: formatDistanceTime(distance, travelTimes)
                };
            }

            // WA LINK
            let wa = '';
            if(p.telepon){
                let no = p.telepon.replace(/\D/g, '').replace(/^0+/, '');
                wa = `https://wa.me/62${no}`;
            }

            // Format review count and get badge color
            const formattedReviews = formatReviewCount(p.ulasan);
            const badgeSize = getBadgeSize(formattedReviews);
            const badgeColor = getBadgeColor(p.ulasan);

            // MARKER with review badge
            let popupContent = `<b>${p.nama}</b><br>${p.ulasan} ulasan`;
            if(p.telepon) popupContent += `<br>📞 ${p.telepon}`;
            popupContent += `<br><a href="https://maps.google.com/maps?q=${p.lat},${p.lng}" target="_blank">🗺️ Navigasi</a>`;
            popupContent += `<br><a href="https://www.google.com/maps/search/?api=1&query=${encodeURIComponent(p.nama)}&query_place_id=${p.id}" target="_blank">⭐ Lihat Ulasan</a>`;

            // Create marker with review badge
            let markerIcon = L.divIcon({
                className: 'custom-marker',
                html: `<div class="marker-container">
                          <div class="marker-icon">🎯</div>
                          <div class="review-badge" style="width: ${badgeSize.width}px; height: ${badgeSize.height}px; font-size: ${badgeSize.fontSize}px; background-color: ${badgeColor};">${formattedReviews}</div>
                       </div>`,
                iconSize: [35, 35],
                iconAnchor: [17, 35],
                popupAnchor: [0, -35]
            });

            let m = L.marker([p.lat,p.lng], {icon: markerIcon}).addTo(map)
                    .bindPopup(popupContent);
            markers.push(m);

            return { ...p, weatherData, distanceInfo };
        });

        // Wait for all weather data to be fetched
        const placesWithWeather = await Promise.all(placePromises);

        placesWithWeather.forEach(p=>{

            // CARD with new fields
            let contactBtns = '';
            if(p.telepon) {
                const telLink = `tel:+62${p.telepon.replace(/\D/g, '').replace(/^0+/, '')}`;
                const waLink = `https://wa.me/62${p.telepon.replace(/\D/g, '').replace(/^0+/, '')}`;
                const mapsLink = `https://maps.google.com/maps/dir/?api=1&destination=${p.lat},${p.lng}`;
                contactBtns = `
                    <div class="d-flex gap-2 mt-2">
                        <a href="${telLink}" class="btn btn-outline-primary btn-sm" style="flex: 1;">📞</a>
                        <a href="${waLink}" class="btn btn-success btn-sm whatsapp-btn" style="flex: 1;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
                        <path fill="currentColor" d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.742.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893A11.821 11.821 0 0020.465 3.488"/>
                    </svg>
                </a>
                        <a href="${mapsLink}" target="_blank" class="btn btn-warning btn-sm" style="flex: 1;" title="Navigasi ke lokasi">🚗</a>
                    </div>
                `;
            }

            // Format business status and category
            const statusInfo = formatBusinessStatus(p.status_bisnis);
            const categoryInfo = formatCategory(p.kategori_utama);
            const cardRatingClass = getCardClass(p.rating);
            const starColor = getStarColor(p.rating);

            // Create photo display with carousel if multiple photos
            let photoHtml = '';
            if(p.fotos && p.fotos.length > 1) {
                const carouselId = `carousel-${Date.now()}-${markers.length}`;
                photoHtml = `<div id="${carouselId}" class="carousel slide interactive-carousel" data-bs-ride="carousel" data-bs-interval="3000">
                    <div class="carousel-indicators">`;
                p.fotos.forEach((foto, index) => {
                    photoHtml += `<button type="button" data-bs-target="#${carouselId}" data-bs-slide-to="${index}" class="${index === 0 ? 'active' : ''}" aria-current="${index === 0 ? 'true' : 'false'}" aria-label="Foto ${index + 1}"></button>`;
                });
                photoHtml += `</div>
                    <div class="carousel-inner">`;
                p.fotos.forEach((foto, index) => {
                    photoHtml += `<div class="carousel-item ${index === 0 ? 'active' : ''}">
                        <img src="${foto}" class="d-block w-100" style="height: 160px; object-fit: cover;" alt="Foto ${index + 1}" loading="lazy">
                        <div class="photo-counter">${index + 1} / ${p.fotos.length}</div>
                    </div>`;
                });
                photoHtml += `</div>
                    <button class="carousel-control-prev" type="button" data-bs-target="#${carouselId}" data-bs-slide="prev">
                        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                        <span class="visually-hidden">Previous</span>
                    </button>
                    <button class="carousel-control-next" type="button" data-bs-target="#${carouselId}" data-bs-slide="next">
                        <span class="carousel-control-next-icon" aria-hidden="true"></span>
                        <span class="visually-hidden">Next</span>
                    </button>
                </div>`;
            } else if(p.foto_utama && p.foto_utama.trim() !== '') {
                photoHtml = `<img src="${p.foto_utama}" class="card-img-top" loading="lazy">`;
            } else {
                photoHtml = `<div class="card-img-top d-flex align-items-center justify-content-center bg-light text-muted" style="height: 160px;">
                    <div class="text-center">
                        <svg width="48" height="48" viewBox="0 0 24 24" fill="currentColor" opacity="0.3">
                            <path d="M21 19V5c0-1.1-.9-2-2-2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2zM8.5 13.5l2.5 3.01L14.5 12l4.5 6H5l3.5-4.5z"/>
                        </svg>
                        <div class="mt-2">Tidak ada gambar</div>
                    </div>
                </div>`;
            }

            // Format additional info - simplified opening hours
            let openingHoursInfo = '';
            if (p.status_buka && p.status_buka !== 'Tidak diketahui') {
                const statusColor = p.status_buka === 'Buka' ? 'text-success' : 'text-danger';
                openingHoursInfo = `<span class="${statusColor}">🕐 ${p.status_buka}</span>`;
                if (p.jam_buka_text) {
                    openingHoursInfo += ` hari ini: ${p.jam_buka_text}`;
                }
            }

            let weatherInfo = '';
            if (p.weatherData) {
                weatherInfo = `<span class="weather-info">${p.weatherData.icon} ${p.weatherData.temperature}°C, ${p.weatherData.condition}</span>`;
            }

            let distanceInfo = '';
            if (p.distanceInfo) {
                distanceInfo = `<span class="distance-info">${p.distanceInfo.formatted}</span>`;
            }

            let parkingInfo = '';
            if (p.ada_parkir) {
                parkingInfo = '<span class="text-success">🅿️ Parkir tersedia</span>';
            }

            let priceInfo = '';
            if (p.harga_level) {
                priceInfo = `<span class="badge bg-warning">${p.harga_level}</span>`;
            }

            let websiteInfo = '';
            if (p.website) {
                websiteInfo = `<a href="${p.website}" target="_blank" class="badge bg-primary">🌐 Website</a>`;
            }

            let cardId = `card-${markers.length - 1}`;
            document.getElementById('result').innerHTML += `
            <div class="col-lg-3 col-md-4 col-sm-6">
                <div class="card h-100 shadow-sm result-card ${cardRatingClass}" id="${cardId}" data-lat="${p.lat}" data-lng="${p.lng}" data-marker-index="${markers.length - 1}">
                    ${photoHtml}
                    <div class="card-body">
                        <b>${p.nama}</b><br>
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="${starColor}" style="display: inline-block; margin-right: 4px;">
                            <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                        </svg>${p.rating} (${p.ulasan})<br>
                        ${distanceInfo ? distanceInfo + '<br>' : ''}
                        ${weatherInfo ? weatherInfo + '<br>' : ''}
                        <small>${p.alamat}</small><br>
                        <small class="text-muted">ID: ${p.id}</small><br>
                        <div class="mb-1">
                            ${openingHoursInfo ? openingHoursInfo + '<br>' : ''}
                            ${parkingInfo ? parkingInfo + '<br>' : ''}
                        </div>
                        <div class="mb-2">
                            <span class="${statusInfo.class} me-1">${statusInfo.text}</span>
                            ${categoryInfo ? `<span class="badge bg-info">${categoryInfo}</span>` : ''}
                            ${priceInfo ? priceInfo : ''}
                            ${websiteInfo ? websiteInfo : ''}
                        </div>
                        ${contactBtns}
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
    // Ignore clicks on carousel elements to prevent map refresh
    if (e.target.closest('.interactive-carousel, .carousel-control-prev, .carousel-control-next, .carousel-indicators button')) {
        return;
    }

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

// Interactive carousel functionality - auto-play on hover
function initializeInteractiveCarousels() {
    // Helper function to safely check closest
    function findClosest(element, selector) {
        if (!element || typeof element.closest !== 'function') return null;
        return element.closest(selector);
    }

    // Use event delegation for dynamically created carousels
    document.addEventListener('mouseenter', function(e) {
        const carousel = findClosest(e.target, '.interactive-carousel');
        if (carousel) {
            // Start auto-play on mouse enter
            if (!carousel.hasAttribute('data-bs-ride')) {
                const bsCarousel = new bootstrap.Carousel(carousel, {
                    ride: 'carousel',
                    interval: 2500, // Faster auto-play: 2.5 seconds
                    wrap: true
                });
                carousel.setAttribute('data-bs-ride', 'carousel');
            } else {
                // If already cycling, ensure it continues
                const bsCarousel = bootstrap.Carousel.getInstance(carousel);
                if (bsCarousel) {
                    bsCarousel.cycle();
                }
            }
        }
    }, true);

    document.addEventListener('mouseleave', function(e) {
        const carousel = findClosest(e.target, '.interactive-carousel');
        if (carousel) {
            // Pause on mouse leave
            const bsCarousel = bootstrap.Carousel.getInstance(carousel);
            if (bsCarousel) {
                bsCarousel.pause();
            }
        }
    });

    // Prevent card selection when clicking on carousel
    document.addEventListener('click', function(e) {
        const carouselElement = findClosest(e.target, '.interactive-carousel, .carousel-control-prev, .carousel-control-next, .carousel-indicators button');
        if (carouselElement) {
            e.stopPropagation();
        }
    });
}

// Initialize interactive carousels when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeInteractiveCarousels);
} else {
    initializeInteractiveCarousels();
}
</script>

</body>
</html>
