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
     * Obtiene las cotizaciones desde la API y guarda en BD
     */
    public function obtenerCotizaciones()
    {
        try {
            $tarjeta = $this->fetchFromApi('tarjeta');
            $mep     = $this->fetchFromApi('mep');

            $out = [
                'tarjeta' => $tarjeta,
                'MEP'     => $mep,
            ];

            // Guardar en base de datos
            foreach ($out as $tipo => $valor) {
                $this->cotizacionModel->insert([
                    'tipo'      => $tipo,
                    'valor_ars' => $valor,
                    'fecha'     => date('Y-m-d H:i:s'),
                ]);
            }

            return $out;
        } catch (\Throwable $e) {
            log_message('error', 'Error con API: '.$e->getMessage());
            return $this->obtenerCotizacionesRespaldo();
        }
    }

    /**
     * Consulta la API oficial de dolarapi.com
     */
    private function toFloatAr(string $s): float
{
    // deja sólo dígitos, puntos y comas
    $s = preg_replace('/[^\d\.,]/', '', $s);

    // Si viene con miles y decimales "1.787,50"
    if (strpos($s, ',') !== false && strpos($s, '.') !== false) {
        $s = str_replace('.', '', $s); // quita separador de miles
        $s = str_replace(',', '.', $s); // coma -> punto decimal
    } else {
        // "1787,50" o "1787.50"
        $s = str_replace(',', '.', $s);
    }

    return (float) $s;
}

private function fetchFromApi(string $tipo): float
{
    // mapeo de tipos a endpoints posibles (prueba en orden)
    $map = [
        'tarjeta' => ['tarjeta'],
        'MEP'     => ['mep','bolsa','contadoconliqui','bolsa'], // probar variantes
        'mep'     => ['mep','bolsa']
    ];

    $candidates = $map[$tipo] ?? [$tipo];

    foreach ($candidates as $candidate) {
        $url = "https://dolarapi.com/v1/dolares/{$candidate}";

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 10,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_USERAGENT      => 'TaxImporter/1.0',
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlErr  = curl_error($ch);
        curl_close($ch);

        // Log para depuración (mira writable/logs/)
        log_message('debug', "[DolarAPI] {$candidate} HTTP:{$httpCode} ERR:{$curlErr} RAW:".substr($response ?? '', 0, 400));

        if (!$response || $httpCode !== 200) {
            // siguiente candidato
            continue;
        }

        $data = json_decode($response, true);

        // Si vino JSON y tiene 'venta' directo
        if (is_array($data) && isset($data['venta'])) {
            $raw = $data['venta'];
            return $this->toFloatAr((string)$raw);
        }

        // A veces la API devuelve un array con objetos: buscar primer 'venta'
        if (is_array($data) && isset($data[0]) && is_array($data[0]) && isset($data[0]['venta'])) {
            return $this->toFloatAr((string)$data[0]['venta']);
        }

        // fallback: buscar "venta" por regex en el HTML/JSON crudo
        if (preg_match('/"venta"\s*:\s*"?\s*([0-9\.,]+)\s*"?/i', $response, $m)) {
            return $this->toFloatAr($m[1]);
        }

        // si llegamos acá, este candidato no sirvió, probamos el siguiente
    }

    // Ningún endpoint funcionó
    throw new \Exception('No se pudo obtener venta desde DolarAPI para ' . $tipo);
}


    /**
     * Respaldo: últimas cotizaciones guardadas en BD
     */
    private function obtenerCotizacionesRespaldo()
    {
        $cotizaciones = [];
        
        $tarjeta = $this->cotizacionModel->obtenerUltimaCotizacion('tarjeta');
        $mep     = $this->cotizacionModel->obtenerUltimaCotizacion('MEP');
        
        if ($tarjeta) {
            $cotizaciones['tarjeta'] = $tarjeta['valor_ars'];
        }
        if ($mep) {
            $cotizaciones['MEP'] = $mep['valor_ars'];
        }
        
        // Valores por defecto si no hay nada en BD
        if (empty($cotizaciones)) {
            $cotizaciones = [
                'tarjeta' => 1683.5,
                'MEP'     => 1650.0,
            ];
        }
        
        return $cotizaciones;
    }

    /**
     * Cotización más reciente de un tipo
     */
    public function obtenerCotizacion($tipo = 'tarjeta')
    {
        $cotizacion = $this->cotizacionModel->obtenerUltimaCotizacion($tipo);
        
        if ($cotizacion) {
            return $cotizacion['valor_ars'];
        }

        // Si no hay en BD, ir a la API
        $cotizaciones = $this->obtenerCotizaciones();
        
        return $cotizaciones[$tipo] ?? ($tipo === 'tarjeta' ? 1683.5 : 1650.0);
    }

    /**
     * Verifica si han pasado más de 1 hora desde la última actualización
     */
    public function necesitaActualizacion($tipo = 'tarjeta')
    {
        $cotizacion = $this->cotizacionModel->obtenerUltimaCotizacion($tipo);
        
        if (!$cotizacion) {
            return true;
        }
        
        $fechaCotizacion = strtotime($cotizacion['fecha']);
        $ahora = time();
        
        return ($ahora - $fechaCotizacion) > 3600;
    }
}
