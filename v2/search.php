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
    "X-Goog-FieldMask: places.displayName,places.formattedAddress,places.rating,places.userRatingCount,places.location,places.photos,places.nationalPhoneNumber"
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

    $hasil[]=[
        "nama"=>$p['displayName']['text'],
        "alamat"=>$p['formattedAddress'] ?? '',
        "rating"=>$p['rating'] ?? 0,
        "ulasan"=>$p['userRatingCount'] ?? 0,
        "lat"=>$p['location']['latitude'],
        "lng"=>$p['location']['longitude'],
        "foto"=>$foto,
        "telepon"=>$p['nationalPhoneNumber'] ?? ''
    ];
}

// 🔥 SORT BERDASARKAN ULASAN TERBANYAK
usort($hasil, fn($a,$b)=>$b['ulasan']<=>$a['ulasan']);

echo json_encode($hasil);
