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

/* Custom marker with label styling */
.custom-marker-with-label {
    pointer-events: none;
}

.marker-container {
    display: flex;
    align-items: center;
    position: relative;
    pointer-events: auto;
    cursor: pointer;
}

.marker-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    border: 1px solid rgba(255, 255, 255, 0.9);
    box-shadow: 0 1px 4px rgba(0,0,0,0.25), 0 1px 1px rgba(0,0,0,0.1);
    flex-shrink: 0;
    z-index: 2;
    position: relative;
}

.marker-dot::after {
    content: '';
    position: absolute;
    top: 0.5px;
    left: 0.5px;
    right: 0.5px;
    bottom: 0.5px;
    border-radius: 50%;
    background: radial-gradient(circle, rgba(255,255,255,0.5) 0%, transparent 85%);
}

.marker-content {
    background: linear-gradient(135deg,
        rgba(255, 255, 255, 0.6) 0%,
        rgba(255, 255, 255, 0.4) 50%,
        rgba(255, 255, 255, 0.3) 100%);
    backdrop-filter: blur(12px) saturate(180%);
    border: 1px solid rgba(255, 255, 255, 0.4);
    border-radius: 8px;
    padding: 3px 8px;
    margin-left: 4px;
    box-shadow:
        0 2px 16px rgba(0,0,0,0.08),
        0 1px 4px rgba(0,0,0,0.06),
        inset 0 1px 0 rgba(255,255,255,0.3);
    white-space: nowrap;
    max-width: 150px;
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    position: relative;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.marker-content::before {
    content: '';
    position: absolute;
    left: -4px;
    top: 50%;
    transform: translateY(-50%);
    width: 0;
    height: 0;
    border-top: 4px solid transparent;
    border-bottom: 4px solid transparent;
    border-right: 4px solid rgba(255, 255, 255, 0.6);
    filter: drop-shadow(0 1px 2px rgba(0,0,0,0.1));
}

.marker-name {
    font-weight: 500;
    color: #2d3748;
    font-size: 10px;
    margin-bottom: 0px;
    letter-spacing: -0.01em;
    line-height: 1.1;
}

.marker-meta {
    display: flex;
    align-items: center;
    gap: 4px;
}

.marker-rating {
    color: #ed8936;
    font-size: 9px;
    filter: drop-shadow(0 0.5px 1px rgba(0,0,0,0.03));
}

.marker-reviews {
    color: #718096;
    font-size: 8px;
    font-weight: 400;
    background: rgba(0, 0, 0, 0.02);
    padding: 0px 2px;
    border-radius: 3px;
}

.marker-review-wrapper {
    margin-left: 2px;
    position: relative;
}

.review-badge {
    display: inline-block;
    padding: 1px 4px;
    border-radius: 4px;
    font-size: 9px;
    font-weight: 600;
    text-align: center;
    min-width: 16px;
    line-height: 1;
    border: 1px solid rgba(255, 255, 255, 0.3);
    box-shadow: 0 1px 2px rgba(0,0,0,0.1);
    position: relative;
    transition: all 0.3s ease;
}

/* Make high-review badges more prominent */
.review-badge[data-reviews="high"] {
    font-size: 10px;
    padding: 2px 6px;
    min-width: 20px;
    font-weight: 700;
    animation: pulse 2s infinite;
    box-shadow: 0 2px 8px rgba(16, 185, 129, 0.4), 0 1px 3px rgba(0,0,0,0.2);
}

.review-badge[data-reviews="medium"] {
    font-size: 9px;
    padding: 1px 5px;
    box-shadow: 0 1px 4px rgba(245, 158, 11, 0.3), 0 1px 2px rgba(0,0,0,0.1);
}

@keyframes pulse {
    0% {
        transform: scale(1);
    }
    50% {
        transform: scale(1.05);
    }
    100% {
        transform: scale(1);
    }
}

/* Hover effects */
.marker-container:hover .marker-dot {
    transform: scale(1.2);
    transition: transform 0.2s ease;
}

.marker-container:hover .marker-label {
    background: rgba(255, 255, 255, 0.98);
    transform: translateY(-1px);
    transition: all 0.2s ease;
}

/* Ensure markers don't interfere with map controls */
.custom-marker-with-label {
    z-index: 1000;
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

    // Track markers and last update timestamp for real-time updates
    var markers = [];
    var existingPlaceIds = new Set();
    var lastUpdateTimestamp = null;
    var currentZoomLevel = map.getZoom();
    var labelZoomThreshold = 14; // Show labels when zoom >= 14



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

    // Function to create marker for a place
    function createMarkerForPlace(place) {
        if (!place.lat || !place.lng) return null;

        var color = getCategoryColor(place.category);

        // Create shortened place name (max 15 chars)
        var shortName = place.name.length > 15 ? place.name.substring(0, 15) + '...' : place.name;

        // Create review badge with color coding based on count
        var reviewBadge = '';
        var reviewCount = place.review_count || 0;
        if (reviewCount > 0) {
            var badgeColor = '#e2e8f0'; // default gray
            var badgeTextColor = '#64748b';
            var reviewLevel = 'low';

            if (reviewCount >= 100) {
                badgeColor = '#10b981'; // green for high reviews
                badgeTextColor = '#ffffff';
                reviewLevel = 'high';
            } else if (reviewCount >= 50) {
                badgeColor = '#f59e0b'; // amber for medium-high
                badgeTextColor = '#ffffff';
                reviewLevel = 'medium';
            } else if (reviewCount >= 20) {
                badgeColor = '#f97316'; // orange for medium
                badgeTextColor = '#ffffff';
                reviewLevel = 'medium';
            } else if (reviewCount >= 10) {
                badgeColor = '#eab308'; // yellow for low-medium
                badgeTextColor = '#1f2937';
                reviewLevel = 'low';
            }

            reviewBadge = `<span class="review-badge" data-reviews="${reviewLevel}" style="background-color: ${badgeColor}; color: ${badgeTextColor};">${reviewCount}</span>`;
        }

        // Create custom marker icon with name and review badge
        var markerIcon = L.divIcon({
            className: 'custom-marker-with-label',
            html: `
                <div class="marker-container">
                    <div class="marker-dot" style="background-color: ${color};"></div>
                    <div class="marker-content">
                        <div class="marker-name">${shortName}</div>
                    </div>
                    ${reviewBadge ? `<div class="marker-review-wrapper">${reviewBadge}</div>` : ''}
                </div>
            `,
            iconSize: [280, 60], // Increased size for better layout
            iconAnchor: [20, 20] // Anchor at marker dot position
        });

        var marker = L.marker([place.lat, place.lng], {icon: markerIcon}).addTo(map);

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
        marker.placeId = place.id; // Store place ID for tracking

        return marker;
    }

    // Create initial markers
    places.forEach(function(place, index) {
        if (place.lat && place.lng) {
            var color = getCategoryColor(place.category);

            // Track categories for legend
            if (place.category) {
                if (!categoryGroups[place.category]) {
                    categoryGroups[place.category] = color;
                }
            }

            var marker = createMarkerForPlace(place);
            if (marker) {
                markers.push(marker);
                existingPlaceIds.add(place.id);
            }

            // Set last update timestamp from initial data
            if (!lastUpdateTimestamp || place.updated_at > lastUpdateTimestamp) {
                lastUpdateTimestamp = place.updated_at;
            }
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

    // Add zoom display control
    var zoomDisplay = L.control({position: 'topleft'});

    zoomDisplay.onAdd = function(map) {
        var div = L.DomUtil.create('div', 'zoom-display');
        div.innerHTML = '<div class="zoom-level">Zoom: ' + map.getZoom() + '</div>';
        div.style.backgroundColor = 'white';
        div.style.padding = '3px 8px';
        div.style.borderRadius = '4px';
        div.style.boxShadow = '0 1px 3px rgba(0,0,0,0.2)';
        div.style.fontSize = '11px';
        div.style.fontWeight = '500';
        div.style.color = '#333';
        div.style.border = '1px solid #ccc';
        div.style.marginTop = '10px';

        // Update zoom level when map zooms
        map.on('zoomend', function() {
            div.innerHTML = '<div class="zoom-level">Zoom: ' + map.getZoom() + '</div>';
        });

        return div;
    };

    zoomDisplay.addTo(map);

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

    // Handle zoom level changes to show/hide marker labels
    map.on('zoomend', function() {
        currentZoomLevel = map.getZoom();
        toggleMarkerLabels(currentZoomLevel >= labelZoomThreshold);
        console.log('Zoom level:', currentZoomLevel, 'Labels:', currentZoomLevel >= labelZoomThreshold ? 'shown' : 'hidden');
    });

    // Function to toggle marker labels visibility based on zoom level
    function toggleMarkerLabels(showLabels) {
        markers.forEach(function(marker) {
            var markerIcon = marker.getIcon();
            if (markerIcon && markerIcon.options && markerIcon.options.html) {
                var html = markerIcon.options.html;
                var newHtml;

                if (showLabels) {
                    // Show labels by ensuring marker-content is visible
                    newHtml = html.replace(/marker-content.*display:\s*none/g, 'marker-content').replace(/marker-content/g, function(match) {
                        if (match.indexOf('display: none') === -1) {
                            return 'marker-content';
                        }
                        return 'marker-content';
                    });
                } else {
                    // Hide labels by adding display: none to marker-content
                    newHtml = html.replace(/(class="marker-content)/g, '$1" style="display: none');
                }

                if (newHtml !== html) {
                    var newIcon = L.divIcon({
                        className: 'custom-marker-with-label',
                        html: newHtml,
                        iconSize: markerIcon.options.iconSize,
                        iconAnchor: markerIcon.options.iconAnchor
                    });
                    marker.setIcon(newIcon);
                }
            }
        });
    }

    // Set initial label visibility based on current zoom
    toggleMarkerLabels(currentZoomLevel >= labelZoomThreshold);

    // Real-time marker updates functionality
    function checkForUpdates() {
        // Prepare data to send
        var requestData = {};
        if (lastUpdateTimestamp) {
            requestData.last_update = lastUpdateTimestamp;
        }
        requestData.place_ids = Array.from(existingPlaceIds);

        fetch('/map/check-updates', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify(requestData)
        })
            .then(response => response.json())
            .then(data => {
                if (data.has_changes) {
                    updateMarkersFromDelta(data.updated_places, data.deleted_place_ids);
                    lastUpdateTimestamp = data.last_update;
                    console.log('Markers updated - Updated:', data.updated_places.length, 'Deleted:', data.deleted_place_ids.length);
                }
            })
            .catch(error => {
                console.error('Error checking for updates:', error);
            });
    }

    function updateMarkersFromDelta(updatedPlaces, deletedPlaceIds) {
        // Track what needs to be done
        var markersToRemove = [];
        var placesToAdd = [];
        var placesToUpdate = [];

        // Find markers that need to be removed based on deleted IDs
        markers.forEach(function(marker, index) {
            if (deletedPlaceIds.includes(marker.placeId)) {
                markersToRemove.push(index);
            }
        });

        // Process updated places
        updatedPlaces.forEach(function(updatedPlace) {
            if (updatedPlace.lat && updatedPlace.lng) {
                var existingMarkerIndex = -1;

                // Check if marker already exists
                for (var i = 0; i < markers.length; i++) {
                    if (markers[i].placeId === updatedPlace.id) {
                        existingMarkerIndex = i;
                        break;
                    }
                }

                if (existingMarkerIndex >= 0) {
                    // Marker exists, update it
                    placesToUpdate.push({
                        markerIndex: existingMarkerIndex,
                        place: updatedPlace
                    });
                } else {
                    // New marker to add
                    placesToAdd.push(updatedPlace);
                }

                // Ensure place is in existing set
                existingPlaceIds.add(updatedPlace.id);
            }
        });

        // Remove markers that were deleted
        markersToRemove.reverse().forEach(function(index) {
            var markerToRemove = markers[index];
            map.removeLayer(markerToRemove);
            markers.splice(index, 1);
        });

        // Update existing markers
        placesToUpdate.forEach(function(updateData) {
            var marker = markers[updateData.markerIndex];
            var place = updateData.place;

            // Update position if changed
            if (marker.getLatLng().lat !== place.lat || marker.getLatLng().lng !== place.lng) {
                marker.setLatLng([place.lat, place.lng]);
            }

            // Update popup content
            var color = getCategoryColor(place.category);
            var googleMapsUrl = createGoogleMapsUrl(place);
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

            marker.setPopupContent(popupContent);
        });

        // Add new markers with animation
        placesToAdd.forEach(function(newPlace) {
            if (newPlace.lat && newPlace.lng) {
                var color = getCategoryColor(newPlace.category);

                // Track categories for legend
                if (newPlace.category) {
                    if (!categoryGroups[newPlace.category]) {
                        categoryGroups[newPlace.category] = color;
                        // Update legend if needed
                        updateLegend();
                    }
                }

                var newMarker = createMarkerForPlace(newPlace);
                if (newMarker) {
                    markers.push(newMarker);
                    existingPlaceIds.add(newPlace.id);

                    // Animate new marker appearance
                    newMarker.setOpacity(0);
                    setTimeout(function() {
                        newMarker.setOpacity(1);
                    }, 100);
                }
            }
        });

        // Note: fitBounds removed from real-time updates to preserve user's zoom level
        // Map bounds are only adjusted on initial load

        console.log('Markers updated - Added:', placesToAdd.length, 'Updated:', placesToUpdate.length, 'Removed:', markersToRemove.length);
    }

    function updateLegend() {
        // Find existing legend control and update it
        map.eachLayer(function(layer) {
            if (layer instanceof L.Control && layer._container && layer._container.classList.contains('leaflet-control')) {
                var legendDiv = layer._container.querySelector('.info.legend');
                if (legendDiv) {
                    legendDiv.innerHTML = '<div style="font-weight: bold; margin-bottom: 6px; font-size: 12px;">📊 Kategori</div>';

                    // Sort categories and remove duplicates
                    var uniqueCategories = {};
                    Object.keys(categoryGroups).forEach(function(category) {
                        uniqueCategories[category] = categoryGroups[category];
                    });

                    // Case-insensitive deduplication
                    var caseInsensitiveMap = {};
                    Object.keys(uniqueCategories).forEach(function(category) {
                        var lowerKey = category.toLowerCase();
                        if (!caseInsensitiveMap[lowerKey]) {
                            caseInsensitiveMap[lowerKey] = {
                                displayName: category,
                                color: uniqueCategories[category]
                            };
                        }
                    });

                    // Sort by display name
                    Object.keys(caseInsensitiveMap).sort(function(a, b) {
                        return caseInsensitiveMap[a].displayName.localeCompare(caseInsensitiveMap[b].displayName);
                    }).forEach(function(lowerKey) {
                        var categoryData = caseInsensitiveMap[lowerKey];
                        legendDiv.innerHTML += `
                            <div style="margin-bottom: 3px; display: flex; align-items: center;">
                                <div style="width: 10px; height: 10px; border-radius: 50%; background-color: ${categoryData.color}; margin-right: 6px; flex-shrink: 0; border: 1px solid #ddd;"></div>
                                <span style="font-size: 10px; line-height: 1.2;">${categoryData.displayName}</span>
                            </div>
                        `;
                    });
                }
            }
        });
    }

    // Start polling for updates every 10 seconds
    setInterval(checkForUpdates, 10000);

    console.log('Leaflet map initialized with', markers.length, 'markers and', Object.keys(categoryGroups).length, 'categories');
    console.log('Real-time updates enabled - checking for changes every 10 seconds');
});
</script>
@endpush
