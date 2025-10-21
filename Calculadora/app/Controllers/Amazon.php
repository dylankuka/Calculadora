<?php

namespace App\Controllers;

use App\Services\AmazonService;

class Amazon extends BaseController
{
    /**
     * Obtiene datos del producto desde Amazon PA-API
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
            log_message('error', 'Error en Amazon controller: ' . $e->getMessage());
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
            $json = $this->request->getJSON();
            $url = $json->url ?? $this->request->getPost('url') ?? '';
            
            if (empty($url)) {
                return $this->response->setJSON([
                    'valid' => false,
                    'message' => 'URL vacía'
                ]);
            }
            
            $amazonService = new AmazonService();
            $resultado = $amazonService->validar($url);
            
            return $this->response->setJSON($resultado);
            
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'valid' => false,
                'message' => 'Error validando URL: ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * Buscar productos por palabra clave
     */
    public function buscar()
    {
        try {
            $json = $this->request->getJSON();
            $keywords = $json->keywords ?? '';
            $categoria = $json->categoria ?? null;
            $limite = $json->limite ?? 10;
            
            if (empty($keywords)) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Palabra clave requerida'
                ]);
            }
            
            $amazonService = new AmazonService();
            $productos = $amazonService->buscarProductos($keywords, $categoria, $limite);
            
            return $this->response->setJSON([
                'success' => true,
                'data' => $productos,
                'count' => count($productos)
            ]);
            
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error en búsqueda: ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * Test de conexión con Amazon PA-API
     */
    public function testConexion()
    {
        try {
            $amazonService = new AmazonService();
            
            // Probar con un ASIN conocido (ejemplo: Kindle)
            $testUrl = 'https://www.amazon.com/dp/B09SWW583J';
            
            $resultado = $amazonService->obtenerProducto($testUrl);
            
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Conexión exitosa con Amazon PA-API',
                'test_product' => $resultado
            ]);
            
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error de conexión: ' . $e->getMessage(),
                'help' => 'Verifica tus credenciales en el archivo .env'
            ]);
        }
    }
}