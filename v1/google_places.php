<?php
require 'config.php';

/* =========================
   FUNGSI REQUEST GOOGLE
========================= */
function google_places($url, $body)
{
    $headers = [
        "Content-Type: application/json",
        "X-Goog-Api-Key: " . GOOGLE_API_KEY,
        "X-Goog-FieldMask: places.id,places.displayName,places.formattedAddress,places.rating,places.userRatingCount,places.nationalPhoneNumber,places.photos"
    ];

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_POSTFIELDS => json_encode($body),
        CURLOPT_TIMEOUT => 20
    ]);

    $response = curl_exec($ch);
    curl_close($ch);

    return json_decode($response, true);
}

/* =========================
   QUERY TEMPAT
========================= */
$body = [
    "textQuery" => "toko buah Jember",
    "languageCode" => "id"
];

$url = "https://places.googleapis.com/v1/places:searchText";
$data = google_places($url, $body);

if (empty($data['places'])) {
    echo "<pre>";
    print_r($data);
    echo "</pre>";
    exit;
}
?>
<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<title>Direktori Toko Buah</title>

<!-- BOOTSTRAP 5 (CDN RINGAN) -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
body{
    background:#f5f7fa;
}
.card-img-top{
    height:180px;
    object-fit:cover;
}
.card{
    border:none;
    border-radius:12px;
}
.rating{
    color:#f5b301;
    font-weight:bold;
}
.small-text{
    font-size:13px;
}
</style>
</head>

<body>

<div class="container py-4">

    <h3 class="mb-4">📍 Direktori Toko Buah – Jember</h3>

    <div class="row g-3">

        <?php foreach ($data['places'] as $p): ?>

        <?php
            // FOTO
            $foto = "https://via.placeholder.com/400x300?text=No+Image";
            if (!empty($p['photos'][0]['name'])) {
                $foto = "https://places.googleapis.com/v1/".$p['photos'][0]['name'].
                        "/media?maxWidthPx=400&key=".GOOGLE_API_KEY;
            }

            // WA LINK
            $wa = "";
            if (!empty($p['nationalPhoneNumber'])) {
                $no = preg_replace('/\D/', '', $p['nationalPhoneNumber']);
                $no = ltrim($no, '0');
                $wa = "https://wa.me/62".$no;
            }
        ?>

        <div class="col-xl-3 col-lg-4 col-md-6">
            <div class="card shadow-sm h-100">

                <img src="<?= $foto ?>" class="card-img-top">

                <div class="card-body">
                    <h6 class="card-title mb-1">
                        <?= $p['displayName']['text'] ?? '-' ?>
                    </h6>

                    <div class="rating mb-1">
                        ⭐ <?= $p['rating'] ?? '-' ?>
                        <span class="text-muted small">
                            (<?= $p['userRatingCount'] ?? 0 ?>)
                        </span>
                    </div>

                    <p class="small-text text-muted mb-2">
                        <?= $p['formattedAddress'] ?? '-' ?>
                    </p>

                    <?php if ($wa): ?>
                        <a href="<?= $wa ?>" target="_blank"
                           class="btn btn-success btn-sm w-100">
                           📞 WhatsApp
                        </a>
                    <?php endif; ?>
                </div>

            </div>
        </div>

        <?php endforeach; ?>

    </div>
</div>

</body>
</html>
