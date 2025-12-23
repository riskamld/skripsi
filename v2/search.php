<?php
require 'config.php';

$keyword = $_GET['keyword'] ?? '';
$nelat = $_GET['nelat'];
$nelng = $_GET['nelng'];
$swlat = $_GET['swlat'];
$swlng = $_GET['swlng'];

$body = [
  "textQuery" => $keyword,
  "locationRestriction" => [
    "rectangle" => [
      "low" => ["latitude"=>$swlat,"longitude"=>$swlng],
      "high"=> ["latitude"=>$nelat,"longitude"=>$nelng]
    ]
  ],
  "languageCode"=>"id"
];

$ch = curl_init("https://places.googleapis.com/v1/places:searchText");
curl_setopt_array($ch,[
  CURLOPT_RETURNTRANSFER=>true,
  CURLOPT_POST=>true,
  CURLOPT_HTTPHEADER=>[
    "Content-Type: application/json",
    "X-Goog-Api-Key: ".GOOGLE_API_KEY,
    "X-Goog-FieldMask: places.displayName,places.formattedAddress,places.rating,places.userRatingCount,places.location,places.photos,places.nationalPhoneNumber,places.id,places.businessStatus,places.types,places.primaryType,places.primaryTypeDisplayName,places.currentOpeningHours,places.regularOpeningHours,places.priceLevel,places.websiteUri,places.accessibilityOptions"
  ],
  CURLOPT_POSTFIELDS=>json_encode($body)
]);

$res = json_decode(curl_exec($ch),true);
curl_close($ch);

$hasil=[];

foreach($res['places'] ?? [] as $p){
    $foto = "https://via.placeholder.com/400x300";
    if(!empty($p['photos'][0]['name'])){
        $foto = "https://places.googleapis.com/v1/".$p['photos'][0]['name']."/media?maxWidthPx=400&key=".GOOGLE_API_KEY;
    }

        // Get multiple photos if available
    $fotos = [];
    if(!empty($p['photos'])){
        foreach(array_slice($p['photos'], 0, 5) as $photo){ // Limit to 5 photos
            $fotos[] = "https://places.googleapis.com/v1/".$photo['name']."/media?maxWidthPx=400&key=".GOOGLE_API_KEY;
        }
    }

    // Process opening hours
    $jam_buka = [];
    $status_buka = 'Tidak diketahui';
    $jam_buka_text = '';

    if(!empty($p['currentOpeningHours'])){
        $status_buka = $p['currentOpeningHours']['openNow'] ? 'Buka' : 'Tutup';
    }

    // Extract today's hours only (more concise)
    if(!empty($p['regularOpeningHours']['weekdayDescriptions'])){
        $hari = [
            'Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'
        ];
        $hari_ini = $hari[date('w')]; // Get current day index (0=Sunday, 1=Monday, etc.)

        foreach($p['regularOpeningHours']['weekdayDescriptions'] as $desc){
            if(strpos($desc, $hari_ini . ':') === 0){
                // Extract just the time part
                $jam_buka_text = str_replace($hari_ini . ': ', '', $desc);
                break;
            }
        }

        // If no specific hours found for today, use the first available
        if(empty($jam_buka_text) && !empty($p['regularOpeningHours']['weekdayDescriptions'])){
            $first_desc = $p['regularOpeningHours']['weekdayDescriptions'][0];
            $jam_buka_text = str_replace(explode(': ', $first_desc)[0] . ': ', '', $first_desc);
        }
    }

    // Process price level
    $harga_level = '';
    if(isset($p['priceLevel'])){
        $harga_level = str_repeat('$', $p['priceLevel']);
    }

    // Process parking info from accessibility or types
    $ada_parkir = false;
    if(!empty($p['accessibilityOptions']['hasWheelchairAccessibleParking'])){
        $ada_parkir = $p['accessibilityOptions']['hasWheelchairAccessibleParking'];
    } elseif(in_array('parking', $p['types'] ?? [])){
        $ada_parkir = true;
    }

    $hasil[]=[
        "nama"=>$p['displayName']['text'],
        "alamat"=>$p['formattedAddress'] ?? '',
        "rating"=>$p['rating'] ?? 0,
        "ulasan"=>$p['userRatingCount'] ?? 0,
        "lat"=>$p['location']['latitude'],
        "lng"=>$p['location']['longitude'],
        "foto_utama"=>$foto,
        "fotos"=>$fotos,
        "telepon"=>$p['nationalPhoneNumber'] ?? '',
        "id"=>$p['id'],
        "status_bisnis"=>$p['businessStatus'] ?? 'UNKNOWN',
        "kategori_utama"=>$p['primaryTypeDisplayName']['text'] ?? '',
        "kategori_type"=>$p['types'] ?? [],
        "status_buka"=>$status_buka,
        "jam_buka_text"=>$jam_buka_text,
        "harga_level"=>$harga_level,
        "ada_parkir"=>$ada_parkir,
        "website"=>$p['websiteUri'] ?? ''
    ];
}

// 🔥 SORT BERDASARKAN ULASAN TERBANYAK
usort($hasil, fn($a,$b)=>$b['ulasan']<=>$a['ulasan']);

echo json_encode($hasil);
