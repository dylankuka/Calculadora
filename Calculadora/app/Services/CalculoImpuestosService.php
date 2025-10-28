<?php

namespace App\Services;

use App\Models\CategoriaProductoModel;
use App\Services\DolarService;

class CalculoImpuestosService
{
    private $categoriaModel;
    private $dolarService;
    
    // Constantes para cálculos
    private const UMBRAL_FRANQUICIA = 400.00; // USD
    private const IVA_PORCENTAJE = 21.00;
    private const TASA_ESTADISTICA = 3.00;
    private const PERCEPCION_AFIP = 30.00; // Se aplica sobre TODO el total
    
    public function __construct()
    {
        $this->categoriaModel = new CategoriaProductoModel();
        $this->dolarService = new DolarService();
    }
    
    /**
     * Cálculo principal de impuestos
     */
    public function calcularImpuestos($precioUSD, $envioUSD, $categoriaId, $metodoPago = 'tarjeta')
    {
        try {
            // Validaciones básicas
            $this->validarDatos($precioUSD, $envioUSD, $categoriaId, $metodoPago);
            
            // Obtener datos de la categoría
            $categoria = $this->categoriaModel->obtenerPorId($categoriaId);
            if (!$categoria) {
                throw new \Exception('Categoría no encontrada');
            }
            
            // Obtener cotización actual
            $cotizacion = $this->dolarService->obtenerCotizacion($metodoPago);
            
            // Calcular valores base
            $valorCIF = $precioUSD + $envioUSD;
            $excedente400 = max(0, $valorCIF - self::UMBRAL_FRANQUICIA);
            
            // Inicializar estructura de cálculo
            $calculo = [
                'datos_base' => [
                    'precio_usd' => $precioUSD,
                    'envio_usd' => $envioUSD,
                    'valor_cif_usd' => $valorCIF,
                    'excedente_400_usd' => $excedente400,
                    'categoria' => $categoria['nombre'],
                    'arancel_categoria' => $categoria['arancel_porcentaje'],
                    'exento_iva' => (bool)$categoria['exento_iva'],
                    'metodo_pago' => $metodoPago,
                    'cotizacion' => $cotizacion
                ],
                'impuestos_usd' => [],
                'impuestos_ars' => [],
                'totales' => []
            ];
            
            // ✅ PASO 1: Convertir CIF a ARS
            $cifARS = $valorCIF * $cotizacion;
            
            // ✅ PASO 2: Calcular ARANCELES (solo si supera $400 USD)
            $arancelesARS = 0;
            if ($valorCIF > self::UMBRAL_FRANQUICIA) {
                // Aranceles se aplican sobre el excedente de $400
                $arancelesUSD = $excedente400 * ($categoria['arancel_porcentaje'] / 100);
                $arancelesARS = $arancelesUSD * $cotizacion;
                
                $calculo['impuestos_usd']['aranceles_usd'] = round($arancelesUSD, 2);
                $calculo['impuestos_ars']['aranceles_ars'] = round($arancelesARS, 2);
                
                // También aplicar tasa estadística (3% sobre TODO el CIF)
                $tasaEstadisticaUSD = $valorCIF * (self::TASA_ESTADISTICA / 100);
                $tasaEstadisticaARS = $tasaEstadisticaUSD * $cotizacion;
                
                $calculo['impuestos_usd']['tasa_estadistica_usd'] = round($tasaEstadisticaUSD, 2);
                $calculo['impuestos_ars']['tasa_estadistica_ars'] = round($tasaEstadisticaARS, 2);
            } else {
                // Bajo franquicia: sin aranceles ni tasa estadística
                $calculo['impuestos_ars']['aranceles_ars'] = 0;
                $calculo['impuestos_ars']['tasa_estadistica_ars'] = 0;
            }
            
            // ✅ PASO 3: Calcular IVA (sobre CIF + aranceles, a menos que esté exento)
            $ivaARS = 0;
            if (!$categoria['exento_iva']) {
                // Base imponible del IVA: (Producto + Envío + Aranceles) en ARS
                $baseIVA_ARS = $cifARS + $arancelesARS;
                $ivaARS = $baseIVA_ARS * (self::IVA_PORCENTAJE / 100);
                
                $calculo['impuestos_ars']['iva_ars'] = round($ivaARS, 2);
            } else {
                $calculo['impuestos_ars']['iva_ars'] = 0;
            }
            
            // ✅ PASO 4: Calcular SUBTOTAL antes de percepción
            $subtotalARS = $cifARS + $arancelesARS + ($calculo['impuestos_ars']['tasa_estadistica_ars'] ?? 0) + $ivaARS;
            
            // ✅ PASO 5: Calcular PERCEPCIÓN AFIP (30% sobre TODO si paga con tarjeta)
            $percepcionARS = 0;
            if ($metodoPago === 'tarjeta') {
                // La percepción se aplica sobre: Producto + Envío + Aranceles + Tasa estadística + IVA
                $percepcionARS = $subtotalARS * (self::PERCEPCION_AFIP / 100);
                $calculo['impuestos_ars']['percepcion_ganancias_ars'] = round($percepcionARS, 2);
            } else {
                $calculo['impuestos_ars']['percepcion_ganancias_ars'] = 0;
            }
            
            // ✅ PASO 6: Calcular TOTALES FINALES
            $totalARS = $subtotalARS + $percepcionARS;
            $totalImpuestosARS = $totalARS - $cifARS; // Todo menos el CIF base
            
            $calculo['totales'] = [
                'total_usd' => round($valorCIF, 2),
                'total_ars' => round($totalARS, 2),
                'total_impuestos_ars' => round($totalImpuestosARS, 2),
                'subtotal_antes_percepcion' => round($subtotalARS, 2),
                'ahorro_franquicia' => $valorCIF <= self::UMBRAL_FRANQUICIA ? 'Sí' : 'No'
            ];
            
            return $calculo;
            
        } catch (\Exception $e) {
            log_message('error', 'Error calculando impuestos: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Validar datos de entrada
     */
    private function validarDatos($precio, $envio, $categoriaId, $metodoPago)
    {
        if (!is_numeric($precio) || $precio <= 0) {
            throw new \Exception('El precio debe ser un número mayor a 0');
        }
        
        if (!is_numeric($envio) || $envio < 0) {
            throw new \Exception('El envío debe ser un número mayor o igual a 0');
        }
        
        if (!is_numeric($categoriaId) || $categoriaId <= 0) {
            throw new \Exception('Categoría inválida');
        }
        
        if (!in_array($metodoPago, ['tarjeta', 'MEP'])) {
            throw new \Exception('Método de pago inválido');
        }
        
        // Validar límites razonables
        if ($precio > 50000) {
            throw new \Exception('El precio no puede exceder $50,000 USD');
        }
        
        if ($envio > 1000) {
            throw new \Exception('El envío no puede exceder $1,000 USD');
        }
    }
    
    /**
     * Obtener resumen simplificado para mostrar al usuario
     */
    public function obtenerResumenCalculo($calculo)
    {
        return [
            'precio_producto' => $calculo['datos_base']['precio_usd'],
            'envio' => $calculo['datos_base']['envio_usd'],
            'categoria' => $calculo['datos_base']['categoria'],
            'metodo_pago' => $calculo['datos_base']['metodo_pago'],
            'cotizacion' => $calculo['datos_base']['cotizacion'],
            'bajo_franquicia' => $calculo['datos_base']['valor_cif_usd'] <= self::UMBRAL_FRANQUICIA,
            'total_impuestos_ars' => $calculo['totales']['total_impuestos_ars'],
            'total_final_ars' => $calculo['totales']['total_ars'],
            'fecha_calculo' => date('d/m/Y H:i:s')
        ];
    }
}