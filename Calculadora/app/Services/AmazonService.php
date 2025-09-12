<?php

namespace App\Services;

class AmazonService
{
    /**
     * Obtiene datos del producto desde Amazon usando scrapping básico
     */
    public function obtenerProducto($url)
    {
        try {
            // Validar que sea URL de Amazon
            if (!$this->esUrlAmazon($url)) {
                throw new \Exception('URL no válida de Amazon');
            }
            
            $html = $this->obtenerHTML($url);
            
            if (!$html) {
                throw new \Exception('No se pudo obtener el contenido de Amazon');
            }
            
            return $this->extraerDatos($html, $url);
            
        } catch (\Exception $e) {
            log_message('error', 'Error Amazon API: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Obtiene el HTML de la página de Amazon
     */
    private function obtenerHTML($url)
    {
        $context = stream_context_create([
            'http' => [
                'method' => 'GET',
                'header' => [
                    'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
                    'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                    'Accept-Language: en-US,en;q=0.5',
                    'Accept-Encoding: gzip, deflate',
                    'Connection: keep-alive'
                ],
                'timeout' => 15
            ]
        ]);
        
        return file_get_contents($url, false, $context);
    }
    
    /**
     * Extrae datos del HTML usando DOMDocument y XPath
     */
    private function extraerDatos($html, $url)
    {
        $dom = new \DOMDocument();
        libxml_use_internal_errors(true);
        $dom->loadHTML($html);
        libxml_clear_errors();
        
        $xpath = new \DOMXPath($dom);
        
        $producto = [
            'url' => $url,
            'nombre' => $this->extraerNombre($xpath),
            'precio' => $this->extraerPrecio($xpath),
            'imagen' => $this->extraerImagen($xpath),
            'disponibilidad' => $this->extraerDisponibilidad($xpath),
            'vendedor' => $this->extraerVendedor($xpath),
            'descripcion' => $this->extraerDescripcion($xpath)
        ];
        
        return $producto;
    }
    
    /**
     * Extrae el nombre del producto
     */
    private function extraerNombre($xpath)
    {
        // Múltiples selectores para el título
        $selectores = [
            "//span[@id='productTitle']",
            "//h1[@id='title']//span",
            "//h1[contains(@class, 'product-title')]",
            "//*[@data-automation-id='product-title']"
        ];
        
        foreach ($selectores as $selector) {
            $nodes = $xpath->query($selector);
            if ($nodes->length > 0) {
                return trim($nodes->item(0)->textContent);
            }
        }
        
        return 'Producto de Amazon';
    }
    
    /**
     * Extrae el precio del producto
     */
    private function extraerPrecio($xpath)
    {
        // Selectores para diferentes tipos de precios
        $selectores = [
            "//span[@class='a-price-whole']",
            "//span[contains(@class, 'a-offscreen')]",
            "//*[@data-automation-id='product-price']",
            "//span[@id='priceblock_dealprice']",
            "//span[@id='priceblock_ourprice']",
            "//span[contains(@class, 'a-price') and contains(@class, 'a-text-price')]"
        ];
        
        foreach ($selectores as $selector) {
            $nodes = $xpath->query($selector);
            if ($nodes->length > 0) {
                $precioTexto = $nodes->item(0)->textContent;
                return $this->limpiarPrecio($precioTexto);
            }
        }
        
        return 0.00;
    }
    
    /**
     * Extrae la imagen principal del producto
     */
    private function extraerImagen($xpath)
    {
        $selectores = [
            "//img[@id='landingImage']/@src",
            "//div[@id='imgTagWrapperId']//img/@src",
            "//img[contains(@class, 'a-dynamic-image')]/@src"
        ];
        
        foreach ($selectores as $selector) {
            $nodes = $xpath->query($selector);
            if ($nodes->length > 0) {
                return $nodes->item(0)->nodeValue;
            }
        }
        
        return 'https://via.placeholder.com/200x200?text=No+Image';
    }
    
    /**
     * Extrae la disponibilidad
     */
    private function extraerDisponibilidad($xpath)
    {
        $selectores = [
            "//div[@id='availability']//span",
            "//*[@data-automation-id='availability-text']",
            "//span[contains(@class, 'a-size-medium') and contains(text(), 'stock')]"
        ];
        
        foreach ($selectores as $selector) {
            $nodes = $xpath->query($selector);
            if ($nodes->length > 0) {
                $texto = trim($nodes->item(0)->textContent);
                if (!empty($texto)) {
                    return $texto;
                }
            }
        }
        
        return 'Disponibilidad no especificada';
    }
    
    /**
     * Extrae información del vendedor
     */
    private function extraerVendedor($xpath)
    {
        $selectores = [
            "//span[contains(text(), 'Ships from')]/following-sibling::span",
            "//span[contains(text(), 'Sold by')]/following-sibling::span",
            "//*[@id='merchant-info']//span"
        ];
        
        foreach ($selectores as $selector) {
            $nodes = $xpath->query($selector);
            if ($nodes->length > 0) {
                return trim($nodes->item(0)->textContent);
            }
        }
        
        return 'Amazon';
    }
    
    /**
     * Extrae descripción básica
     */
    private function extraerDescripcion($xpath)
    {
        $selectores = [
            "//div[@id='feature-bullets']//span[@class='a-list-item']",
            "//div[@id='productDescription']//p"
        ];
        
        $descripcion = '';
        
        foreach ($selectores as $selector) {
            $nodes = $xpath->query($selector);
            if ($nodes->length > 0) {
                for ($i = 0; $i < min(3, $nodes->length); $i++) {
                    $texto = trim($nodes->item($i)->textContent);
                    if (!empty($texto) && strlen($texto) > 10) {
                        $descripcion .= $texto . '. ';
                    }
                }
                break;
            }
        }
        
        return trim($descripcion);
    }
    
    /**
     * Limpia y convierte el precio a número
     */
    private function limpiarPrecio($precioTexto)
    {
        // Remover símbolos de moneda y espacios
        $precio = preg_replace('/[^\d.,]/', '', $precioTexto);
        
        // Convertir formato americano (1,234.56) a float
        if (preg_match('/\d{1,3}(,\d{3})*\.\d{2}/', $precio)) {
            $precio = str_replace(',', '', $precio);
        }
        
        return (float) $precio;
    }
    
    /**
     * Valida que sea una URL de Amazon válida
     */
    private function esUrlAmazon($url)
    {
        $dominiosValidos = [
            'amazon.com', 
            'amazon.es', 
            'amazon.co.uk', 
            'amazon.com.ar',
            'amazon.com.mx',
            'amazon.de',
            'amazon.fr',
            'amazon.ca',
            'amazon.it'
        ];
        
        $host = parse_url($url, PHP_URL_HOST);
        
        foreach ($dominiosValidos as $dominio) {
            if (strpos($host, $dominio) !== false) {
                return true;
            }
        }
        
        return false;
    }
}