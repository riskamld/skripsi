@extends('layouts.app')

@section('title', 'Peta - Mafaza Fortuna')

@section('page-title', 'Peta Lokasi')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Peta Lokasi Bisnis</h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                        <i class="fas fa-minus"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div id="map" style="height: 600px; width: 100%;"></div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<!-- Leaflet CSS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
     integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY="
     crossorigin=""/>

<!-- Leaflet Fullscreen CSS -->
<link rel="stylesheet" href="https://api.mapbox.com/mapbox.js/plugins/leaflet-fullscreen/v1.0.1/leaflet.fullscreen.css" />



<style>
#map {
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}


</style>
@endpush

@push('scripts')
<!-- Leaflet JS -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
     integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo="
     crossorigin=""></script>

<!-- Leaflet Fullscreen JS -->
<script src="https://api.mapbox.com/mapbox.js/plugins/leaflet-fullscreen/v1.0.1/Leaflet.fullscreen.min.js"></script>



<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize the map
    var map = L.map('map').setView([-8.1845, 113.6681], 10); // Center on Jember area

    // Add OpenStreetMap tiles (Streets)
    var streets = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: 'Mafaza Fortuna',
        maxZoom: 19,
    });

    // Add Satellite tiles with street labels (Esri World Imagery + Labels)
    var satellite = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
        attribution: 'Mafaza Fortuna',
        maxZoom: 18,
    });

    // Add labels overlay
    var satelliteLabels = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/Reference/World_Transportation/MapServer/tile/{z}/{y}/{x}', {
        attribution: '',
        maxZoom: 18,
        opacity: 0.9
    });

    // Set default layer (streets)
    streets.addTo(map);

    // Places data from PHP
    var places = @json($places);



    // Expanded color palette for unique categories
    var colorPalette = [
        '#e6194b', '#3cb44b', '#ffe119', '#4363d8', '#f58231',
        '#911eb4', '#42d4f4', '#f032e6', '#bfef45', '#fabed4',
        '#469990', '#dcbeff', '#9a6324', '#fffac8', '#800000',
        '#aaffc3', '#808000', '#ffd8b1', '#000075', '#a9a9a9',
        '#ffffff', '#000000', '#FF6B6B', '#4ECDC4', '#45B7D1',
        '#96CEB4', '#FFEAA7', '#DDA0DD', '#98D8C8', '#F7DC6F',
        '#BB8FCE', '#85C1E9', '#F8C471', '#82E0AA', '#F1948A',
        '#85C1E9', '#D7BDE2', '#AED6F1', '#A3E4D7', '#F9E79F'
    ];

    var categoryColorMap = {}; // Store assigned colors for each category
    var colorIndex = 0;

    // Function to get unique color for each category
    function getCategoryColor(category) {
        if (!category) return '#6c757d'; // gray for no category

        // Use case-insensitive key for consistency
        var key = category.toLowerCase();

        if (!categoryColorMap[key]) {
            // Assign next color from palette
            categoryColorMap[key] = colorPalette[colorIndex % colorPalette.length];
            colorIndex++;
        }

        return categoryColorMap[key];
    }

    // Function to create Google Maps URL
    function createGoogleMapsUrl(place) {
        if (place.maps_url) {
            return place.maps_url;
        } else if (place.lat && place.lng) {
            return `https://www.google.com/maps?q=${place.lat},${place.lng}`;
        }
        return null;
    }

    // Create markers for each place
    var markers = [];
    var categoryGroups = {};

    places.forEach(function(place, index) {
        if (place.lat && place.lng) {
            var color = getCategoryColor(place.category);

            // Create custom marker icon (even smaller size)
            var markerIcon = L.divIcon({
                className: 'custom-marker',
                html: `<div style="background-color: ${color}; width: 8px; height: 8px; border-radius: 50%; border: 1px solid white; box-shadow: 0 1px 2px rgba(0,0,0,0.2);"></div>`,
                iconSize: [8, 8],
                iconAnchor: [4, 4]
            });

            var marker = L.marker([place.lat, place.lng], {icon: markerIcon}).addTo(map);

            // Track categories for legend
            if (place.category) {
                if (!categoryGroups[place.category]) {
                    categoryGroups[place.category] = color;
                }
            }

            // Create Google Maps URL
            var googleMapsUrl = createGoogleMapsUrl(place);

            // Create popup content
            var popupContent = `
                <div style="min-width: 250px; font-family: Arial, sans-serif;">
                    <h6 style="margin: 0 0 8px 0; font-weight: bold; color: #333;">${place.name}</h6>

                    ${place.category ? `<div style="margin-bottom: 6px;">
                        <span style="background-color: ${color}; color: white; padding: 2px 8px; border-radius: 12px; font-size: 12px; font-weight: bold;">
                            ${place.category}
                        </span>
                    </div>` : ''}

                    ${place.rating ? `<div style="margin-bottom: 4px;">
                        <strong>Rating:</strong> ⭐ ${place.rating}
                        ${place.review_count ? ` (${place.review_count} ulasan)` : ''}
                    </div>` : ''}

                    ${place.address ? `<div style="margin-bottom: 4px; font-size: 13px;">
                        <strong>📍 Alamat:</strong><br>
                        <span style="color: #666;">${place.address}</span>
                    </div>` : ''}

                    ${place.phone ? `<div style="margin-bottom: 8px; font-size: 13px;">
                        <strong>📞 Telepon:</strong> ${place.phone}
                    </div>` : ''}

                    ${googleMapsUrl ? `<div style="margin-top: 8px;">
                        <a href="${googleMapsUrl}" target="_blank" style="background-color: #4285f4; color: white; padding: 6px 12px; text-decoration: none; border-radius: 4px; font-size: 12px; display: inline-block;">
                            🗺️ Lihat di Google Maps
                        </a>
                    </div>` : ''}
                </div>
            `;

            marker.bindPopup(popupContent);
            markers.push(marker);
        }
    });

    // Fit map to show all markers
    if (markers.length > 0) {
        var group = new L.featureGroup(markers);
        map.fitBounds(group.getBounds().pad(0.1));
    }

    // Create legend control with better positioning
    var legend = L.control({position: 'topright'});

    legend.onAdd = function (map) {
        var div = L.DomUtil.create('div', 'info legend');
        div.style.backgroundColor = 'white';
        div.style.padding = '8px';
        div.style.borderRadius = '5px';
        div.style.boxShadow = '0 2px 5px rgba(0,0,0,0.2)';
        div.style.maxHeight = '300px';
        div.style.maxWidth = '200px';
        div.style.overflowY = 'auto';
        div.style.fontSize = '11px';

        div.innerHTML = '<div style="font-weight: bold; margin-bottom: 6px; font-size: 12px;">📊 Kategori</div>';

        // Sort categories and remove duplicates (should already be unique, but ensure it)
        var uniqueCategories = {};
        Object.keys(categoryGroups).forEach(function(category) {
            uniqueCategories[category] = categoryGroups[category];
        });

        // Case-insensitive deduplication - group categories that are the same ignoring case
        var caseInsensitiveMap = {};
        Object.keys(uniqueCategories).forEach(function(category) {
            var lowerKey = category.toLowerCase();
            if (!caseInsensitiveMap[lowerKey]) {
                caseInsensitiveMap[lowerKey] = {
                    displayName: category, // Keep the first occurrence as display name
                    color: uniqueCategories[category]
                };
            }
        });

        // Sort by display name
        Object.keys(caseInsensitiveMap).sort(function(a, b) {
            return caseInsensitiveMap[a].displayName.localeCompare(caseInsensitiveMap[b].displayName);
        }).forEach(function(lowerKey) {
            var categoryData = caseInsensitiveMap[lowerKey];
            div.innerHTML += `
                <div style="margin-bottom: 3px; display: flex; align-items: center;">
                    <div style="width: 10px; height: 10px; border-radius: 50%; background-color: ${categoryData.color}; margin-right: 6px; flex-shrink: 0; border: 1px solid #ddd;"></div>
                    <span style="font-size: 10px; line-height: 1.2;">${categoryData.displayName}</span>
                </div>
            `;
        });

        return div;
    };

    // Add legend to map if we have categories
    if (Object.keys(categoryGroups).length > 0) {
        legend.addTo(map);
    }

    // Add layers control for Streets/Satellite switching
    var baseMaps = {
        "Streets": streets,
        "Satellite": satellite
    };

    var layersControl = L.control.layers(baseMaps, {}, {
        position: 'topright'
    }).addTo(map);

    // Handle layer switching to show/hide satellite labels
    map.on('baselayerchange', function(e) {
        if (e.name === 'Satellite') {
            // Add satellite labels when switching to satellite
            if (!map.hasLayer(satelliteLabels)) {
                satelliteLabels.addTo(map);
            }
        } else {
            // Remove satellite labels when switching away from satellite
            if (map.hasLayer(satelliteLabels)) {
                map.removeLayer(satelliteLabels);
            }
        }
    });

    // Add fullscreen control in topleft (next to zoom)
    var fullscreenControl = L.control.fullscreen({
        position: 'topleft'
    });
    fullscreenControl.addTo(map);

    // Set titles and handle fullscreen events
    setTimeout(function() {
        var fullscreenButton = document.querySelector('.leaflet-control-fullscreen a');
        if (fullscreenButton) {
            fullscreenButton.title = 'Tampilkan Fullscreen';
            fullscreenButton.setAttribute('data-original-title', 'Tampilkan Fullscreen');

            // Update title when fullscreen state changes
            map.on('fullscreenchange', function() {
                if (map.isFullscreen()) {
                    fullscreenButton.title = 'Keluar Fullscreen';
                    fullscreenButton.setAttribute('data-original-title', 'Keluar Fullscreen');
                } else {
                    fullscreenButton.title = 'Tampilkan Fullscreen';
                    fullscreenButton.setAttribute('data-original-title', 'Tampilkan Fullscreen');
                }
            });
        }
    }, 100);

    console.log('Leaflet map initialized with', markers.length, 'markers and', Object.keys(categoryGroups).length, 'categories');
});
</script>
@endpush
