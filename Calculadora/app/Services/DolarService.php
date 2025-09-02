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
private function httpGet($url)
{
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_TIMEOUT => 12,
        CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/116 Safari/537.36',
        CURLOPT_HTTPHEADER => ['Accept-Language: es-AR,es;q=0.9,en;q=0.8'],
    ]);
    $html = curl_exec($ch);
    curl_close($ch);
    return $html ?: '';
}

private function toFloatAr($s)
{
    // limpia todo salvo dígitos, coma y punto
    $s = preg_replace('/[^0-9\.,]/', '', $s);
    // caso AR: "1.787,50" -> quitar miles (.) y cambiar coma por punto
    if (strpos($s, ',') !== false && strpos($s, '.') !== false) {
        $s = str_replace('.', '', $s);
        $s = str_replace(',', '.', $s);
    } else {
        // si viene "1787,50"
        $s = str_replace(',', '.', $s);
    }
    return (float)$s;
}

private function fetchVenta($url, $pattern)
{
    $html = $this->httpGet($url);
    if (!$html) {
        throw new \Exception('sin HTML');
    }
    // busca el primer "Venta $xxxx"
    if (preg_match($pattern, $html, $m)) {
        return $this->toFloatAr($m[1]);
    }
    throw new \Exception('sin match');
}

public function obtenerCotizaciones()
{
    try {
        // Páginas dedicadas (más estables que el home)
        $tarjeta = $this->fetchVenta(
            'https://dolarhoy.com/cotizacion-dolar-tarjeta',
            '/Venta\\s*\\$\\s*([0-9\\.,]+)/su'
        );

        $mep = $this->fetchVenta(
            'https://dolarhoy.com/cotizacion-dolar-mep',
            '/D[óo]lar\\s*MEP.*?Venta\\s*\\$\\s*([0-9\\.,]+)/su'
        );

        $out = ['tarjeta' => $tarjeta, 'MEP' => $mep];

        // guardá en DB
        foreach ($out as $tipo => $valor) {
            if ($valor > 0) {
                $this->cotizacionModel->insert([
                    'tipo' => $tipo,
                    'valor_ars' => $valor,
                    'fecha' => date('Y-m-d H:i:s'),
                ]);
            }
        }
        return $out;

    } catch (\Throwable $e) {
        log_message('error', 'Cotizaciones fallback: '.$e->getMessage());
        return $this->obtenerCotizacionesRespaldo();
    }
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