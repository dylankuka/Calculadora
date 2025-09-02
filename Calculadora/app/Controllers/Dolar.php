<?php

namespace App\Controllers;

use App\Services\DolarService;

class Dolar extends BaseController
{
    /**
     * Actualiza las cotizaciones manualmente
     */
    public function actualizar()
    {
        try {
            $dolarService = new \App\Services\DolarService();
            $cotizaciones = $dolarService->obtenerCotizaciones();
            
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Cotizaciones actualizadas exitosamente',
                'data' => $cotizaciones,
                'timestamp' => date('d/m/Y H:i:s')
            ]);
            
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error actualizando cotizaciones: ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * Obtiene las cotizaciones actuales
     */
    public function obtener()
    {
        try {
            $dolarService = new DolarService();
            
            $cotizaciones = [
                'tarjeta' => $dolarService->obtenerCotizacion('tarjeta'),
                'MEP' => $dolarService->obtenerCotizacion('MEP')
            ];
            
            return $this->response->setJSON([
                'success' => true,
                'data' => $cotizaciones,
                'timestamp' => date('d/m/Y H:i:s')
            ]);
            
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error obteniendo cotizaciones: ' . $e->getMessage()
            ]);
        }
    }
}