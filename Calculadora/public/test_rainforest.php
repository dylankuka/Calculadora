<?php
$apiKey = "39A47B8978654C59A203E41D988AD2F0";
$asin = "B073JYC4XM"; // producto de ejemplo

$url = "https://api.rainforestapi.com/request?api_key={$apiKey}&type=product&amazon_domain=amazon.com&asin={$asin}";

// Inicializar cURL
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

// Ejecutar solicitud
$response = curl_exec($ch);
curl_close($ch);

// Decodificar JSON
$data = json_decode($response, true);

// Mostrar resultado
if (isset($data['product'])) {
    echo "✅ Conexión exitosa<br>";
    echo "Título: " . htmlspecialchars($data['product']['title']) . "<br>";
    echo "Precio: " . htmlspecialchars($data['product']['buybox_winner']['price']['raw']) . "<br>";
} else {
    echo "❌ Error o sin datos recibidos:<br><pre>";
    print_r($data);
    echo "</pre>";
}
?>
