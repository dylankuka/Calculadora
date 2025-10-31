<?php

namespace App\Services;

class AmazonService
{
    private $apiKey;
    private $baseUrl = 'https://api.rainforestapi.com/request';
    
    public function __construct()
    {
        // Cargar API key desde .env
        $this->apiKey = getenv('RAINFOREST_API_KEY');
        
        if (empty($this->apiKey)) {
            log_message('error', 'Rainforest API key not configured in .env');
            throw new \Exception('API key de Rainforest no configurada');
        }
    }
    
    /**
     * Obtiene información del producto usando Rainforest API
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
            
            // Detectar dominio de Amazon
            $domain = $this->detectarDominioAmazon($url);
            
            // Obtener datos del producto desde Rainforest API
            $productoData = $this->getItemInfo($asin, $domain);
            
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
            log_message('error', 'Error Rainforest API: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Llama a Rainforest API para obtener información del producto
     */
    private function getItemInfo($asin, $domain = 'amazon.com')
    {
        // Construir URL de la API
        $params = [
            'api_key' => $this->apiKey,
            'type' => 'product',
            'amazon_domain' => $domain,
            'asin' => $asin,
            'output' => 'json'
        ];
        
        $url = $this->baseUrl . '?' . http_build_query($params);
        
        // Realizar petición con cURL
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTPHEADER => [
                'Accept: application/json',
                'User-Agent: TaxImporter/1.0'
            ]
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);
        
        if ($curlError) {
            throw new \Exception('Error de conexión con Rainforest API: ' . $curlError);
        }
        
        if ($httpCode !== 200) {
            log_message('error', "Rainforest API HTTP {$httpCode}: {$response}");
            throw new \Exception("Error de Rainforest API: HTTP {$httpCode}");
        }
        
        $data = json_decode($response, true);
        
        if (!isset($data['product'])) {
            throw new \Exception('Producto no encontrado en Rainforest API');
        }
        
        return $this->parsearRespuestaAPI($data);
    }
    
    /**
     * Parsea la respuesta de Rainforest API
     */
    private function parsearRespuestaAPI($data)
    {
        $producto = $data['product'] ?? [];
        $resultado = [];
        
        // Título
        $resultado['title'] = $producto['title'] ?? null;
        
        // Precio actual
        if (isset($producto['buybox_winner']['price']['value'])) {
            $resultado['price'] = $producto['buybox_winner']['price']['value'];
            $resultado['currency'] = $producto['buybox_winner']['price']['currency'] ?? 'USD';
        } elseif (isset($producto['price']['value'])) {
            $resultado['price'] = $producto['price']['value'];
            $resultado['currency'] = $producto['price']['currency'] ?? 'USD';
        }
        
        // Precio de lista (antes de descuentos)
        if (isset($producto['buybox_winner']['rrp']['value'])) {
            $resultado['list_price'] = $producto['buybox_winner']['rrp']['value'];
        }
        
        // Disponibilidad
        $resultado['availability'] = $producto['buybox_winner']['availability']['raw'] ?? 
                                    $producto['availability']['raw'] ?? 
                                    'Desconocido';
        
        // Imagen principal
        if (isset($producto['main_image']['link'])) {
            $resultado['image_url'] = $producto['main_image']['link'];
        }
        
        // Imágenes adicionales
        if (isset($producto['images'])) {
            $resultado['images'] = array_map(function($img) {
                return $img['link'] ?? null;
            }, $producto['images']);
        }
        
        // Características/Features
        if (isset($producto['feature_bullets'])) {
            $resultado['features'] = $producto['feature_bullets'];
        } elseif (isset($producto['features'])) {
            $resultado['features'] = $producto['features'];
        }
        
        // Descripción
        $resultado['description'] = $producto['description'] ?? null;
        
        // Marca
        $resultado['brand'] = $producto['brand'] ?? null;
        
        // Categoría principal
        if (isset($producto['categories'][0]['name'])) {
            $resultado['category'] = $producto['categories'][0]['name'];
        }
        
        // Rating y reviews
        if (isset($producto['rating'])) {
            $resultado['rating'] = $producto['rating'];
        }
        if (isset($producto['ratings_total'])) {
            $resultado['num_reviews'] = $producto['ratings_total'];
        }
        
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
            '/\/([A-Z0-9]{10})(?:\/|\?|$)/',    // ASIN al final de URL
        ];
        
        foreach ($patrones as $patron) {
            if (preg_match($patron, $url, $matches)) {
                return $matches[1];
            }
        }
        
        return null;
    }
    
    /**
     * Detectar dominio de Amazon desde la URL
     */
    private function detectarDominioAmazon($url)
    {
        $host = parse_url($url, PHP_URL_HOST);
        
        // Mapeo de dominios
        $dominios = [
            'amazon.com' => 'amazon.com',
            'amazon.es' => 'amazon.es',
            'amazon.co.uk' => 'amazon.co.uk',
            'amazon.com.ar' => 'amazon.com',  // Argentina usa amazon.com
            'amazon.com.mx' => 'amazon.com.mx',
            'amazon.de' => 'amazon.de',
            'amazon.fr' => 'amazon.fr',
            'amazon.ca' => 'amazon.ca',
            'amazon.it' => 'amazon.it',
            'amazon.co.jp' => 'amazon.co.jp',
            'amazon.com.br' => 'amazon.com.br',
            'amazon.in' => 'amazon.in',
            'amazon.com.au' => 'amazon.com.au'
        ];
        
        foreach ($dominios as $dominio => $apiDominio) {
            if (strpos($host, $dominio) !== false) {
                return $apiDominio;
            }
        }
        
        // Por defecto usar amazon.com
        return 'amazon.com';
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
                    'message' => 'No se pudo identificar el producto (ASIN no encontrado)'
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
            'amazon.co.jp', 'amazon.com.br', 'amazon.in',
            'amazon.com.au', 'smile.amazon.com'
        ];
        
        $host = parse_url($url, PHP_URL_HOST);
        
        // Eliminar 'www.' si existe
        $host = str_replace('www.', '', $host);
        
        foreach ($dominiosValidos as $dominio) {
            if (strpos($host, $dominio) !== false) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Buscar productos por palabra clave usando Rainforest API
     */
    public function buscarProductos($keywords, $categoria = null, $limite = 10)
    {
        try {
            // Construir parámetros
            $params = [
                'api_key' => $this->apiKey,
                'type' => 'search',
                'amazon_domain' => 'amazon.com',
                'search_term' => $keywords,
                'output' => 'json'
            ];
            
            if ($categoria) {
                $params['category_id'] = $categoria;
            }
            
            $url = $this->baseUrl . '?' . http_build_query($params);
            
            $ch = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_SSL_VERIFYPEER => true
            ]);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($httpCode !== 200) {
                throw new \Exception("Error en búsqueda: HTTP {$httpCode}");
            }
            
            $data = json_decode($response, true);
            
            if (!isset($data['search_results'])) {
                return [];
            }
            
            // Limitar resultados
            $resultados = array_slice($data['search_results'], 0, $limite);
            
            // Parsear resultados
            return array_map(function($item) {
                return [
                    'asin' => $item['asin'] ?? null,
                    'title' => $item['title'] ?? null,
                    'price' => $item['price']['value'] ?? null,
                    'currency' => $item['price']['currency'] ?? 'USD',
                    'image_url' => $item['image'] ?? null,
                    'rating' => $item['rating'] ?? null,
                    'num_reviews' => $item['ratings_total'] ?? null,
                    'url' => $item['link'] ?? null
                ];
            }, $resultados);
            
        } catch (\Exception $e) {
            log_message('error', 'Error Rainforest Search: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Verificar estado de la API (test de conexión)
     */
    public function testConexion()
    {
        try {
            // Probar con un ASIN conocido (ejemplo: Kindle Paperwhite)
            $testAsin = 'B08KTZ8249';
            
            $params = [
                'api_key' => $this->apiKey,
                'type' => 'product',
                'amazon_domain' => 'amazon.com',
                'asin' => $testAsin,
                'output' => 'json'
            ];
            
            $url = $this->baseUrl . '?' . http_build_query($params);
            
            $ch = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 15,
                CURLOPT_SSL_VERIFYPEER => true
            ]);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($httpCode !== 200) {
                throw new \Exception("HTTP {$httpCode}");
            }
            
            $data = json_decode($response, true);
            
            if (!isset($data['product'])) {
                throw new \Exception('Respuesta inválida de la API');
            }
            
            return [
                'success' => true,
                'message' => 'Conexión exitosa con Rainforest API',
                'api_status' => 'OK',
                'test_product' => $data['product']['title'] ?? 'Producto de prueba'
            ];
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error de conexión: ' . $e->getMessage(),
                'help' => 'Verifica tu RAINFOREST_API_KEY en el archivo .env'
            ];
        }
    }
}