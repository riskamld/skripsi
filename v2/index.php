<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Pencarian Tempat</title>
<link rel="manifest" href="manifest.json">

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" rel="stylesheet">

<style>
#map { height: 420px; }
.card-img-top { height:160px; object-fit:cover; }
.card-body { min-height: 120px; }
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

<div id="result" class="row g-3"></div>

</div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
let map = L.map('map').setView([-8.1727,113.6995], 13); // Jember

let osm = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '© OpenStreetMap'
});

let satellite = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
    attribution: 'Tiles &copy; Esri &mdash; Source: Esri, i-cubed, USDA, USGS, AEX, GeoEye, Getmapping, Aerogrid, IGN, IGP, UPR-EGP, and the GIS User Community'
});

let baseLayers = {
    "Normal": osm,
    "Satellite": satellite
};

L.control.layers(baseLayers).addTo(map);

// Default to Satellite
satellite.addTo(map);

let markers = [];

function search(){
    let keyword = document.getElementById('keyword').value.trim();
    if(!keyword) return; // Skip if no keyword

    let b = map.getBounds();

    let url = `search.php?keyword=${encodeURIComponent(keyword)}&nelat=${b.getNorth()}&nelng=${b.getEast()}&swlat=${b.getSouth()}&swlng=${b.getWest()}`;

    fetch(url)
      .then(r=>r.json())
      .then(data=>{
        document.getElementById('result').innerHTML = '';
        markers.forEach(m=>map.removeLayer(m));
        markers=[];

        data.forEach(p=>{
            // WA LINK
            let wa = '';
            if(p.telepon){
                let no = p.telepon.replace(/\D/g, '').replace(/^0+/, '');
                wa = `https://wa.me/62${no}`;
            }

            // MARKER
            let popupContent = `<b>${p.nama}</b><br>${p.ulasan} ulasan`;
            if(p.telepon) popupContent += `<br>📞 ${p.telepon}`;
            let m = L.marker([p.lat,p.lng]).addTo(map)
                    .bindPopup(popupContent);
            markers.push(m);

            // CARD
            let waBtn = wa ? `<a href="${wa}" target="_blank" class="btn btn-success btn-sm w-100 mt-2">📞 WhatsApp</a>` : '';
            document.getElementById('result').innerHTML += `
            <div class="col-lg-3 col-md-4 col-sm-6">
                <div class="card h-100 shadow-sm">
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

// Auto search on map move
map.on('moveend', search);
</script>

</body>
</html>
