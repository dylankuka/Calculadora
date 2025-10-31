<?php
// ✅ Configura tu clave de Rainforest API
$apiKey = "TU_API_KEY_AQUI"; // ← reemplazá con tu API key real

// ✅ Producto de ejemplo (ASIN de Amazon)
$asin = "B08N5WRWNW"; // Podés cambiarlo por otro

// ✅ URL del endpoint de Rainforest API
$url = "https://api.rainforestapi.com/request?api_key={$apiKey}&type=product&amazon_domain=amazon.com&asin={$asin}";

// ✅ Ejecutar la solicitud con cURL
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);

if (curl_errno($ch)) {
    echo "❌ Error en la conexión: " . curl_error($ch);
    exit;
}

curl_close($ch);

// ✅ Decodificar respuesta JSON
$data = json_decode($response, true);

// ✅ Mostrar resultado
if (isset($data['product'])) {
    echo "<h2>✅ Conexión exitosa con Rainforest API</h2>";
    echo "<strong>Título:</strong> " . htmlspecialchars($data['product']['title']) . "<br>";
    echo "<strong>Precio:</strong> " . htmlspecialchars($data['product']['buybox_winner']['price']['value'] ?? 'No disponible') . " ";
    echo htmlspecialchars($data['product']['buybox_winner']['price']['currency'] ?? '') . "<br>";
} else {
    echo "<h3>❌ Error o sin datos recibidos:</h3>";
    echo "<pre>" . print_r($data, true) . "</pre>";
}
?>
