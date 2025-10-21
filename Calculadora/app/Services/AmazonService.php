<?php

namespace App\Services;

class AmazonService
{
    private $accessKey;
    private $secretKey;
    private $partnerTag;
    private $region;
    private $host;
    private $marketplace;
    
    public function __construct()
    {
        // Cargar credenciales desde .env
        $this->accessKey = getenv('AMAZON_ACCESS_KEY');
        $this->secretKey = getenv('AMAZON_SECRET_KEY');
        $this->partnerTag = getenv('AMAZON_PARTNER_TAG');
        $this->region = getenv('AMAZON_REGION') ?: 'us-east-1';
        $this->host = getenv('AMAZON_HOST') ?: 'webservices.amazon.com';
        $this->marketplace = getenv('AMAZON_MARKETPLACE') ?: 'www.amazon.com';
        
        // Validar credenciales
        if (empty($this->accessKey) || empty($this->secretKey) || empty($this->partnerTag)) {
            log_message('error', 'Amazon PA-API credentials not configured');
        }
    }
    
    /**
     * Obtiene información del producto usando PA-API
     */
    public function obtenerProducto($url)
    {
        try {
            // Validar URL
            if (!$this->esUrlAmazon($url)) {
                throw new \Exception('URL no válida de Amazon');
            }
            
            // Extraer ASIN de la URL
            $asin = $this->extraerASIN($url);
            
            if (!$asin) {
                throw new \Exception('No se pudo extraer el ASIN de la URL');
            }
            
            // Obtener datos del producto desde PA-API
            $productoData = $this->getItemInfo($asin);
            
            return [
                'success' => true,
                'asin' => $asin,
                'url' => $url,
                'nombre' => $productoData['title'] ?? 'Producto de Amazon',
                'precio' => $productoData['price'] ?? 0,
                'precio_original' => $productoData['list_price'] ?? null,
                'moneda' => $productoData['currency'] ?? 'USD',
                'imagen' => $productoData['image_url'] ?? null,
                'imagenes' => $productoData['images'] ?? [],
                'disponibilidad' => $productoData['availability'] ?? 'Desconocido',
                'categoria' => $productoData['category'] ?? null,
                'marca' => $productoData['brand'] ?? null,
                'caracteristicas' => $productoData['features'] ?? [],
                'descripcion' => $productoData['description'] ?? null,
                'rating' => $productoData['rating'] ?? null,
                'num_reviews' => $productoData['num_reviews'] ?? null
            ];
            
        } catch (\Exception $e) {
            log_message('error', 'Error Amazon PA-API: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Llama a Amazon PA-API para obtener información del item
     */
    private function getItemInfo($asin)
    {
        $timestamp = gmdate('Ymd\THis\Z');
        $datestamp = gmdate('Ymd');
        
        // Payload de la petición
        $payload = json_encode([
            'ItemIds' => [$asin],
            'Resources' => [
                'Images.Primary.Large',
                'Images.Variants.Large',
                'ItemInfo.Title',
                'ItemInfo.Features',
                'ItemInfo.ContentInfo',
                'ItemInfo.TechnicalInfo',
                'ItemInfo.ManufactureInfo',
                'Offers.Listings.Price',
                'Offers.Listings.Availability.Message',
                'Offers.Listings.Condition',
                'Offers.Listings.MerchantInfo',
                'BrowseNodeInfo.BrowseNodes'
            ],
            'PartnerTag' => $this->partnerTag,
            'PartnerType' => 'Associates',
            'Marketplace' => $this->marketplace
        ]);
        
        // Endpoint
        $endpoint = '/paapi5/getitems';
        $url = 'https://' . $this->host . $endpoint;
        
        // Headers
        $headers = [
            'Content-Type: application/json; charset=utf-8',
            'Content-Encoding: amz-1.0',
            'X-Amz-Target: com.amazon.paapi5.v1.ProductAdvertisingAPIv1.GetItems',
            'X-Amz-Date: ' . $timestamp,
            'Authorization: ' . $this->generarAutorizacion($payload, $timestamp, $datestamp, $endpoint)
        ];
        
        // Realizar petición
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $payload,
            CURLOPT_TIMEOUT => 15,
            CURLOPT_SSL_VERIFYPEER => true
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);
        
        if ($curlError) {
            throw new \Exception('Error de conexión con Amazon: ' . $curlError);
        }
        
        if ($httpCode !== 200) {
            log_message('error', "Amazon PA-API HTTP {$httpCode}: {$response}");
            throw new \Exception("Error de Amazon PA-API: HTTP {$httpCode}");
        }
        
        $data = json_decode($response, true);
        
        if (!isset($data['ItemsResult']['Items'][0])) {
            throw new \Exception('Producto no encontrado en Amazon PA-API');
        }
        
        return $this->parsearRespuestaAPI($data['ItemsResult']['Items'][0]);
    }
    
    /**
     * Genera la firma de autorización AWS Signature Version 4
     */
    private function generarAutorizacion($payload, $timestamp, $datestamp, $endpoint)
    {
        $algorithm = 'AWS4-HMAC-SHA256';
        $credentialScope = "{$datestamp}/{$this->region}/ProductAdvertisingAPI/aws4_request";
        
        // Crear canonical request
        $canonicalHeaders = "content-encoding:amz-1.0\n";
        $canonicalHeaders .= "content-type:application/json; charset=utf-8\n";
        $canonicalHeaders .= "host:{$this->host}\n";
        $canonicalHeaders .= "x-amz-date:{$timestamp}\n";
        $canonicalHeaders .= "x-amz-target:com.amazon.paapi5.v1.ProductAdvertisingAPIv1.GetItems\n";
        
        $signedHeaders = 'content-encoding;content-type;host;x-amz-date;x-amz-target';
        
        $payloadHash = hash('sha256', $payload);
        
        $canonicalRequest = "POST\n";
        $canonicalRequest .= "{$endpoint}\n";
        $canonicalRequest .= "\n"; // Query string vacío
        $canonicalRequest .= $canonicalHeaders . "\n";
        $canonicalRequest .= $signedHeaders . "\n";
        $canonicalRequest .= $payloadHash;
        
        // Crear string to sign
        $stringToSign = "{$algorithm}\n";
        $stringToSign .= "{$timestamp}\n";
        $stringToSign .= "{$credentialScope}\n";
        $stringToSign .= hash('sha256', $canonicalRequest);
        
        // Calcular firma
        $kDate = hash_hmac('sha256', $datestamp, 'AWS4' . $this->secretKey, true);
        $kRegion = hash_hmac('sha256', $this->region, $kDate, true);
        $kService = hash_hmac('sha256', 'ProductAdvertisingAPI', $kRegion, true);
        $kSigning = hash_hmac('sha256', 'aws4_request', $kService, true);
        
        $signature = hash_hmac('sha256', $stringToSign, $kSigning);
        
        // Construir header de autorización
        $authorization = "{$algorithm} ";
        $authorization .= "Credential={$this->accessKey}/{$credentialScope}, ";
        $authorization .= "SignedHeaders={$signedHeaders}, ";
        $authorization .= "Signature={$signature}";
        
        return $authorization;
    }
    
    /**
     * Parsea la respuesta de Amazon PA-API
     */
    private function parsearRespuestaAPI($item)
    {
        $resultado = [];
        
        // Título
        $resultado['title'] = $item['ItemInfo']['Title']['DisplayValue'] ?? null;
        
        // Precio
        if (isset($item['Offers']['Listings'][0]['Price']['Amount'])) {
            $resultado['price'] = $item['Offers']['Listings'][0]['Price']['Amount'];
            $resultado['currency'] = $item['Offers']['Listings'][0]['Price']['Currency'] ?? 'USD';
        }
        
        // Precio de lista (precio original antes de descuentos)
        if (isset($item['Offers']['Listings'][0]['SavingBasis']['Amount'])) {
            $resultado['list_price'] = $item['Offers']['Listings'][0]['SavingBasis']['Amount'];
        }
        
        // Disponibilidad
        $resultado['availability'] = $item['Offers']['Listings'][0]['Availability']['Message'] ?? 'Desconocido';
        
        // Imágenes
        if (isset($item['Images']['Primary']['Large']['URL'])) {
            $resultado['image_url'] = $item['Images']['Primary']['Large']['URL'];
        }
        
        if (isset($item['Images']['Variants'])) {
            $resultado['images'] = array_map(function($img) {
                return $img['Large']['URL'] ?? null;
            }, $item['Images']['Variants']);
        }
        
        // Características
        if (isset($item['ItemInfo']['Features']['DisplayValues'])) {
            $resultado['features'] = $item['ItemInfo']['Features']['DisplayValues'];
        }
        
        // Marca
        $resultado['brand'] = $item['ItemInfo']['ByLineInfo']['Brand']['DisplayValue'] ?? null;
        
        // Categoría
        if (isset($item['BrowseNodeInfo']['BrowseNodes'][0]['DisplayName'])) {
            $resultado['category'] = $item['BrowseNodeInfo']['BrowseNodes'][0]['DisplayName'];
        }
        
        // Rating (si está disponible)
        $resultado['rating'] = null;
        $resultado['num_reviews'] = null;
        
        return $resultado;
    }
    
    /**
     * Extrae el ASIN de una URL de Amazon
     */
    private function extraerASIN($url)
    {
        // Patrones comunes de URLs de Amazon
        $patrones = [
            '/\/dp\/([A-Z0-9]{10})/',           // /dp/ASIN
            '/\/gp\/product\/([A-Z0-9]{10})/',  // /gp/product/ASIN
            '/\/exec\/obidos\/ASIN\/([A-Z0-9]{10})/', // ASIN antiguo
            '/\/product-reviews\/([A-Z0-9]{10})/',    // Reviews
            '/[?&]asin=([A-Z0-9]{10})/',        // Query param
        ];
        
        foreach ($patrones as $patron) {
            if (preg_match($patron, $url, $matches)) {
                return $matches[1];
            }
        }
        
        return null;
    }
    
    /**
     * Valida URL de Amazon
     */
    public function validar($url)
    {
        try {
            if (empty($url)) {
                return [
                    'valid' => false,
                    'message' => 'URL vacía'
                ];
            }
            
            if (!filter_var($url, FILTER_VALIDATE_URL)) {
                return [
                    'valid' => false,
                    'message' => 'URL no válida'
                ];
            }
            
            if (!$this->esUrlAmazon($url)) {
                return [
                    'valid' => false,
                    'message' => 'Debe ser una URL de Amazon válida'
                ];
            }
            
            $asin = $this->extraerASIN($url);
            
            if (!$asin) {
                return [
                    'valid' => false,
                    'message' => 'No se pudo identificar el producto'
                ];
            }
            
            $host = parse_url($url, PHP_URL_HOST);
            
            return [
                'valid' => true,
                'message' => 'URL válida',
                'domain' => $host,
                'asin' => $asin
            ];
            
        } catch (\Exception $e) {
            return [
                'valid' => false,
                'message' => 'Error validando URL: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Valida que sea URL de Amazon
     */
    private function esUrlAmazon($url)
    {
        $dominiosValidos = [
            'amazon.com', 'amazon.es', 'amazon.co.uk', 
            'amazon.com.ar', 'amazon.com.mx', 'amazon.de',
            'amazon.fr', 'amazon.ca', 'amazon.it',
            'amazon.co.jp', 'amazon.com.br', 'amazon.in'
        ];
        
        $host = parse_url($url, PHP_URL_HOST);
        
        foreach ($dominiosValidos as $dominio) {
            if (strpos($host, $dominio) !== false) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Buscar productos por palabra clave (requiere PA-API SearchItems)
     */
    public function buscarProductos($keywords, $categoria = null, $limite = 10)
    {
        try {
            $timestamp = gmdate('Ymd\THis\Z');
            $datestamp = gmdate('Ymd');
            
            $payload = json_encode([
                'Keywords' => $keywords,
                'Resources' => [
                    'Images.Primary.Medium',
                    'ItemInfo.Title',
                    'Offers.Listings.Price'
                ],
                'PartnerTag' => $this->partnerTag,
                'PartnerType' => 'Associates',
                'Marketplace' => $this->marketplace,
                'ItemCount' => min($limite, 10) // Máximo 10
            ]);
            
            $endpoint = '/paapi5/searchitems';
            $url = 'https://' . $this->host . $endpoint;
            
            $headers = [
                'Content-Type: application/json; charset=utf-8',
                'Content-Encoding: amz-1.0',
                'X-Amz-Target: com.amazon.paapi5.v1.ProductAdvertisingAPIv1.SearchItems',
                'X-Amz-Date: ' . $timestamp,
                'Authorization: ' . $this->generarAutorizacionBusqueda($payload, $timestamp, $datestamp, $endpoint)
            ];
            
            $ch = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTPHEADER => $headers,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => $payload,
                CURLOPT_TIMEOUT => 15
            ]);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($httpCode !== 200) {
                throw new \Exception("Error en búsqueda: HTTP {$httpCode}");
            }
            
            $data = json_decode($response, true);
            
            if (!isset($data['SearchResult']['Items'])) {
                return [];
            }
            
            return array_map([$this, 'parsearRespuestaAPI'], $data['SearchResult']['Items']);
            
        } catch (\Exception $e) {
            log_message('error', 'Error Amazon Search: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Generar autorización para búsqueda
     */
    private function generarAutorizacionBusqueda($payload, $timestamp, $datestamp, $endpoint)
    {
        $algorithm = 'AWS4-HMAC-SHA256';
        $credentialScope = "{$datestamp}/{$this->region}/ProductAdvertisingAPI/aws4_request";
        
        $canonicalHeaders = "content-encoding:amz-1.0\n";
        $canonicalHeaders .= "content-type:application/json; charset=utf-8\n";
        $canonicalHeaders .= "host:{$this->host}\n";
        $canonicalHeaders .= "x-amz-date:{$timestamp}\n";
        $canonicalHeaders .= "x-amz-target:com.amazon.paapi5.v1.ProductAdvertisingAPIv1.SearchItems\n";
        
        $signedHeaders = 'content-encoding;content-type;host;x-amz-date;x-amz-target';
        $payloadHash = hash('sha256', $payload);
        
        $canonicalRequest = "POST\n{$endpoint}\n\n{$canonicalHeaders}\n{$signedHeaders}\n{$payloadHash}";
        
        $stringToSign = "{$algorithm}\n{$timestamp}\n{$credentialScope}\n" . hash('sha256', $canonicalRequest);
        
        $kDate = hash_hmac('sha256', $datestamp, 'AWS4' . $this->secretKey, true);
        $kRegion = hash_hmac('sha256', $this->region, $kDate, true);
        $kService = hash_hmac('sha256', 'ProductAdvertisingAPI', $kRegion, true);
        $kSigning = hash_hmac('sha256', 'aws4_request', $kService, true);
        
        $signature = hash_hmac('sha256', $stringToSign, $kSigning);
        
        return "{$algorithm} Credential={$this->accessKey}/{$credentialScope}, SignedHeaders={$signedHeaders}, Signature={$signature}";
    }
}