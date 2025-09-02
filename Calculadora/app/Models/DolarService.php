<?php

namespace App\Services;

use App\Models\CotizacionDolarModel;

class DolarService
{
    private $cotizacionModel;
    
    public function __construct()
    {
        $this->cotizacionModel = new CotizacionDolarModel();
    }
    
    /**
     * Obtiene las cotizaciones del dólar desde DólarHoy
     */
    public function obtenerCotizaciones()
    {
        try {
            $url = 'https://dolarhoy.com/';
            
            // Configurar contexto para evitar bloqueos
            $context = stream_context_create([
                'http' => [
                    'method' => 'GET',
                    'header' => [
                        'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
                        'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
                        'Accept-Language: es-ES,es;q=0.8,en-US;q=0.5,en;q=0.3',
                        'Connection: keep-alive',
                        'Upgrade-Insecure-Requests: 1'
                    ],
                    'timeout' => 10
                ]
            ]);
            
            $html = file_get_contents($url, false, $context);
            
            if ($html === false) {
                throw new \Exception('No se pudo obtener el contenido de la página');
            }
            
            $cotizaciones = $this->parsearHTML($html);
            
            // Guardar en base de datos
            foreach ($cotizaciones as $tipo => $valor) {
                if ($valor > 0) {
                    $this->cotizacionModel->insert([
                        'tipo' => $tipo,
                        'valor_ars' => $valor,
                        'fecha' => date('Y-m-d H:i:s')
                    ]);
                }
            }
            
            return $cotizaciones;
            
        } catch (\Exception $e) {
            log_message('error', 'Error obteniendo cotizaciones: ' . $e->getMessage());
            return $this->obtenerCotizacionesRespaldo();
        }
    }
    
    /**
     * Parsea el HTML de DólarHoy para extraer los valores
     */
    private function parsearHTML($html)
    {
        $cotizaciones = [];
        
        // Usar DOMDocument para parsear HTML
        $dom = new \DOMDocument();
        libxml_use_internal_errors(true);
        $dom->loadHTML($html);
        libxml_clear_errors();
        
        $xpath = new \DOMXPath($dom);
        
        try {
            // Buscar dólar tarjeta/turista
            $tarjetaNodes = $xpath->query("//div[contains(@class, 'tile') or contains(@class, 'cotizacion')]//span[contains(text(), 'Tarjeta') or contains(text(), 'Turista')]");
            if ($tarjetaNodes->length > 0) {
                $cotizaciones['tarjeta'] = $this->extraerValor($tarjetaNodes->item(0)->parentNode);
            }
            
            // Buscar dólar MEP
            $mepNodes = $xpath->query("//div[contains(@class, 'tile') or contains(@class, 'cotizacion')]//span[contains(text(), 'MEP')]");
            if ($mepNodes->length > 0) {
                $cotizaciones['MEP'] = $this->extraerValor($mepNodes->item(0)->parentNode);
            }
            
            // Si no encuentra por clase, buscar por patrones de texto
            if (empty($cotizaciones)) {
                $cotizaciones = $this->parsearPorRegex($html);
            }
            
        } catch (\Exception $e) {
            log_message('error', 'Error parseando HTML: ' . $e->getMessage());
            $cotizaciones = $this->parsearPorRegex($html);
        }
        
        return $cotizaciones;
    }
    
    /**
     * Extrae el valor numérico de un nodo DOM
     */
    private function extraerValor($node)
    {
        $texto = $node->textContent;
        
        // Buscar patrón de precio: $1,234.56 o $1234.56
        preg_match('/\$?\s*([0-9]{1,3}(?:[.,][0-9]{3})*(?:[.,][0-9]{2})?)/i', $texto, $matches);
        
        if (!empty($matches[1])) {
            $valor = str_replace([',', '.'], ['', '.'], $matches[1]);
            return (float) $valor;
        }
        
        return 0;
    }
    
    /**
     * Parsea usando expresiones regulares como respaldo
     */
    private function parsearPorRegex($html)
    {
        $cotizaciones = [];
        
        // Patrones para buscar cotizaciones
        $patrones = [
            'tarjeta' => '/(?:tarjeta|turista).*?\$?\s*([0-9]{1,3}(?:[.,][0-9]{3})*(?:[.,][0-9]{2})?)/i',
            'MEP' => '/MEP.*?\$?\s*([0-9]{1,3}(?:[.,][0-9]{3})*(?:[.,][0-9]{2})?)/i'
        ];
        
        foreach ($patrones as $tipo => $patron) {
            if (preg_match($patron, $html, $matches)) {
                $valor = str_replace([',', '.'], ['', '.'], $matches[1]);
                $cotizaciones[$tipo] = (float) $valor;
            }
        }
        
        return $cotizaciones;
    }
    
    /**
     * Obtiene cotizaciones de respaldo desde la base de datos
     */
    private function obtenerCotizacionesRespaldo()
    {
        $cotizaciones = [];
        
        $tarjeta = $this->cotizacionModel->obtenerUltimaCotizacion('tarjeta');
        $mep = $this->cotizacionModel->obtenerUltimaCotizacion('MEP');
        
        if ($tarjeta) {
            $cotizaciones['tarjeta'] = $tarjeta['valor_ars'];
        }
        
        if ($mep) {
            $cotizaciones['MEP'] = $mep['valor_ars'];
        }
        
        // Valores por defecto si no hay datos
        if (empty($cotizaciones)) {
            $cotizaciones = [
                'tarjeta' => 1683.5,
                'MEP' => 1650.0
            ];
        }
        
        return $cotizaciones;
    }
    
    /**
     * Obtiene la cotización más reciente de un tipo específico
     */
    public function obtenerCotizacion($tipo = 'tarjeta')
    {
        $cotizacion = $this->cotizacionModel->obtenerUltimaCotizacion($tipo);
        
        if ($cotizacion) {
            return $cotizacion['valor_ars'];
        }
        
        // Si no hay datos, intentar scrappear
        $cotizaciones = $this->obtenerCotizaciones();
        
        return $cotizaciones[$tipo] ?? ($tipo === 'tarjeta' ? 1683.5 : 1650.0);
    }
    
    /**
     * Verifica si las cotizaciones necesitan actualizarse (más de 1 hora)
     */
    public function necesitaActualizacion($tipo = 'tarjeta')
    {
        $cotizacion = $this->cotizacionModel->obtenerUltimaCotizacion($tipo);
        
        if (!$cotizacion) {
            return true;
        }
        
        $fechaCotizacion = strtotime($cotizacion['fecha']);
        $ahora = time();
        
        // Actualizar si han pasado más de 1 hora
        return ($ahora - $fechaCotizacion) > 3600;
    }
}