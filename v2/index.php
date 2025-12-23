<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<title>Pencarian Tempat</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" rel="stylesheet">

<style>
#map { height: 420px; }
.card-img-top { height:160px; object-fit:cover; }
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

L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',{
    attribution:'© OpenStreetMap'
}).addTo(map);

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
            // MARKER
            let m = L.marker([p.lat,p.lng]).addTo(map)
                    .bindPopup(`<b>${p.nama}</b><br>${p.ulasan} ulasan`);
            markers.push(m);

            // CARD
            document.getElementById('result').innerHTML += `
            <div class="col-lg-3 col-md-4 col-sm-6">
                <div class="card h-100 shadow-sm">
                    <img src="${p.foto}" class="card-img-top">
                    <div class="card-body">
                        <b>${p.nama}</b><br>
                        ⭐ ${p.rating} (${p.ulasan})<br>
                        <small>${p.alamat}</small>
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
