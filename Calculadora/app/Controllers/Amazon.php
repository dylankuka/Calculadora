<?php

namespace App\Controllers;

use App\Services\AmazonService;

class Amazon extends BaseController
{
    /**
     * Obtiene datos del producto desde Rainforest API
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
                'message' => 'Producto obtenido exitosamente desde Rainforest API'
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
                'count' => count($productos),
                'source' => 'Rainforest API'
            ]);
            
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error en búsqueda: ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * Test de conexión con Rainforest API
     */
    public function testConexion()
    {
        try {
            $amazonService = new AmazonService();
            $resultado = $amazonService->testConexion();
            
            return $this->response->setJSON($resultado);
            
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error de conexión: ' . $e->getMessage(),
                'help' => 'Verifica tu RAINFOREST_API_KEY en el archivo .env'
            ]);
        }
    }
    
    /**
     * Obtener información de tu cuenta de Rainforest
     */
    public function verificarCuenta()
    {
        try {
            $apiKey = getenv('RAINFOREST_API_KEY');
            
            if (empty($apiKey)) {
                throw new \Exception('API key no configurada');
            }
            
            // Endpoint para verificar account info
            $url = "https://api.rainforestapi.com/account?api_key=" . $apiKey;
            
            $ch = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 10,
                CURLOPT_SSL_VERIFYPEER => true
            ]);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($httpCode !== 200) {
                throw new \Exception("HTTP {$httpCode}: Verifica tu API key");
            }
            
            $data = json_decode($response, true);
            
            return $this->response->setJSON([
                'success' => true,
                'account_info' => $data,
                'message' => 'Cuenta verificada exitosamente'
            ]);
            
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error verificando cuenta: ' . $e->getMessage()
            ]);
        }
    }
}