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
    private const PERCEPCION_GANANCIAS = 30.00; // Solo para pagos con tarjeta argentina
    
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
            
            // ✅ REGLA DE FRANQUICIA DE USD 400
            if ($valorCIF <= self::UMBRAL_FRANQUICIA) {
                // Bajo franquicia: solo IVA (excepto libros)
                $calculo = $this->calcularBajoFranquicia($calculo);
            } else {
                // Sobre franquicia: aranceles + tasa estadística + IVA
                $calculo = $this->calcularSobreFranquicia($calculo);
            }
            
            // ✅ PERCEPCIONES (solo si paga con tarjeta argentina)
            if ($metodoPago === 'tarjeta') {
                $calculo = $this->calcularPercepciones($calculo);
            }
            
            // ✅ TOTALES FINALES
            $calculo = $this->calcularTotales($calculo);
            
            return $calculo;
            
        } catch (\Exception $e) {
            log_message('error', 'Error calculando impuestos: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Cálculo para productos bajo franquicia (≤ $400 USD)
     */
    private function calcularBajoFranquicia($calculo)
    {
        $datos = $calculo['datos_base'];
        
        // Solo aplicar IVA si no está exento
        if (!$datos['exento_iva']) {
            $baseIVA_ARS = $datos['valor_cif_usd'] * $datos['cotizacion'];
            $iva_ARS = $baseIVA_ARS * (self::IVA_PORCENTAJE / 100);
            
            $calculo['impuestos_usd']['iva_base_usd'] = $datos['valor_cif_usd'];
            $calculo['impuestos_ars']['iva_ars'] = round($iva_ARS, 2);
        }
        
        // No hay aranceles ni tasa estadística bajo franquicia
        $calculo['impuestos_usd']['aranceles_usd'] = 0;
        $calculo['impuestos_ars']['aranceles_ars'] = 0;
        $calculo['impuestos_ars']['tasa_estadistica_ars'] = 0;
        
        return $calculo;
    }
    
    /**
     * Cálculo para productos sobre franquicia (> $400 USD)
     */
    private function calcularSobreFranquicia($calculo)
    {
        $datos = $calculo['datos_base'];
        
        // ✅ ARANCELES: aplicar sobre el excedente de $400
        $aranceles_USD = $datos['excedente_400_usd'] * ($datos['arancel_categoria'] / 100);
        $aranceles_ARS = $aranceles_USD * $datos['cotizacion'];
        
        $calculo['impuestos_usd']['aranceles_usd'] = round($aranceles_USD, 2);
        $calculo['impuestos_ars']['aranceles_ars'] = round($aranceles_ARS, 2);
        
        // ✅ TASA ESTADÍSTICA: 3% sobre todo el CIF
        $tasaEstadistica_USD = $datos['valor_cif_usd'] * (self::TASA_ESTADISTICA / 100);
        $tasaEstadistica_ARS = $tasaEstadistica_USD * $datos['cotizacion'];
        
        $calculo['impuestos_usd']['tasa_estadistica_usd'] = round($tasaEstadistica_USD, 2);
        $calculo['impuestos_ars']['tasa_estadistica_ars'] = round($tasaEstadistica_ARS, 2);
        
        // ✅ IVA: sobre (CIF + aranceles) si no está exento
        if (!$datos['exento_iva']) {
            $baseIVA_USD = $datos['valor_cif_usd'] + $aranceles_USD;
            $baseIVA_ARS = $baseIVA_USD * $datos['cotizacion'];
            $iva_ARS = $baseIVA_ARS * (self::IVA_PORCENTAJE / 100);
            
            $calculo['impuestos_usd']['iva_base_usd'] = round($baseIVA_USD, 2);
            $calculo['impuestos_ars']['iva_ars'] = round($iva_ARS, 2);
        } else {
            $calculo['impuestos_ars']['iva_ars'] = 0;
        }
        
        return $calculo;
    }
    
    /**
     * Calcular percepciones (solo tarjeta argentina)
     */
    private function calcularPercepciones($calculo)
    {
        // Sumar todo lo calculado hasta ahora en ARS
        $subtotal_ARS = 0;
        $subtotal_ARS += $calculo['datos_base']['valor_cif_usd'] * $calculo['datos_base']['cotizacion'];
        $subtotal_ARS += $calculo['impuestos_ars']['aranceles_ars'] ?? 0;
        $subtotal_ARS += $calculo['impuestos_ars']['tasa_estadistica_ars'] ?? 0;
        $subtotal_ARS += $calculo['impuestos_ars']['iva_ars'] ?? 0;
        
        // Percepción de Ganancias/Bienes Personales: 30%
        $percepcion_ARS = $subtotal_ARS * (self::PERCEPCION_GANANCIAS / 100);
        
        $calculo['impuestos_ars']['percepcion_ganancias_ars'] = round($percepcion_ARS, 2);
        
        return $calculo;
    }
    
    /**
     * Calcular totales finales
     */
    private function calcularTotales($calculo)
    {
        $datos = $calculo['datos_base'];
        
        // Total en USD (solo CIF + aranceles si los hay)
        $totalUSD = $datos['valor_cif_usd'] + ($calculo['impuestos_usd']['aranceles_usd'] ?? 0);
        
        // Total en ARS (todo)
        $totalARS = 0;
        $totalARS += $datos['valor_cif_usd'] * $datos['cotizacion']; // Producto + envío en ARS
        $totalARS += $calculo['impuestos_ars']['aranceles_ars'] ?? 0;
        $totalARS += $calculo['impuestos_ars']['tasa_estadistica_ars'] ?? 0;
        $totalARS += $calculo['impuestos_ars']['iva_ars'] ?? 0;
        $totalARS += $calculo['impuestos_ars']['percepcion_ganancias_ars'] ?? 0;
        
        $calculo['totales'] = [
            'total_usd' => round($totalUSD, 2),
            'total_ars' => round($totalARS, 2),
            'total_impuestos_ars' => round($totalARS - ($datos['valor_cif_usd'] * $datos['cotizacion']), 2),
            'ahorro_franquicia' => $datos['valor_cif_usd'] <= self::UMBRAL_FRANQUICIA ? 'Sí' : 'No'
        ];
        
        return $calculo;
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