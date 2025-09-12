<?php

namespace App\Controllers;

use App\Services\AmazonService;

class Amazon extends BaseController
{
    /**
     * Obtiene datos del producto desde Amazon
     */
    public function obtener()
    {
        try {
            $json = $this->request->getJSON();
            $url = $json->url ?? '';
            
            if (empty($url)) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'URL requerida'
                ]);
            }
            
            $amazonService = new AmazonService();
            $producto = $amazonService->obtenerProducto($url);
            
            return $this->response->setJSON([
                'success' => true,
                'data' => $producto,
                'message' => 'Producto obtenido exitosamente'
            ]);
            
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * Valida una URL de Amazon
     */
    public function validar()
    {
        try {
            $url = $this->request->getPost('url');
            
            if (empty($url)) {
                return $this->response->setJSON([
                    'valid' => false,
                    'message' => 'URL vacía'
                ]);
            }
            
            // Validar formato básico
            if (!filter_var($url, FILTER_VALIDATE_URL)) {
                return $this->response->setJSON([
                    'valid' => false,
                    'message' => 'URL no válida'
                ]);
            }
            
            // Validar que sea de Amazon
            $dominiosValidos = [
                'amazon.com', 'amazon.es', 'amazon.co.uk', 
                'amazon.com.ar', 'amazon.com.mx', 'amazon.de',
                'amazon.fr', 'amazon.ca', 'amazon.it'
            ];
            
            $host = parse_url($url, PHP_URL_HOST);
            $esAmazon = false;
            
            foreach ($dominiosValidos as $dominio) {
                if (strpos($host, $dominio) !== false) {
                    $esAmazon = true;
                    break;
                }
            }
            
            if (!$esAmazon) {
                return $this->response->setJSON([
                    'valid' => false,
                    'message' => 'Debe ser una URL de Amazon válida'
                ]);
            }
            
            return $this->response->setJSON([
                'valid' => true,
                'message' => 'URL válida',
                'domain' => $host
            ]);
            
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'valid' => false,
                'message' => 'Error validando URL: ' . $e->getMessage()
            ]);
        }
    }
}