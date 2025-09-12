<?php
namespace App\Services;

class MercadoPagoService
{
    private $accessToken;
    private $baseUrl;
    private $publicKey;

    public function __construct()
    {
        // Configuración desde variables de entorno
        $this->accessToken = getenv('MERCADOPAGO_ACCESS_TOKEN') ?: 'TEST-ACCESS-TOKEN-AQUI';
        $this->publicKey = getenv('MERCADOPAGO_PUBLIC_KEY') ?: 'TEST-PUBLIC-KEY-AQUI';
        
        // Determinar si estamos en sandbox o producción
        $isSandbox = strpos($this->accessToken, 'TEST') === 0;
        $this->baseUrl = $isSandbox ? 'https://api.mercadopago.com' : 'https://api.mercadopago.com';
    }

    /**
     * Crear preferencia de pago para donación
     */
    public function crearPreferenciaDonacion($datos)
    {
        try {
            $preference = [
                'items' => [
                    [
                        'id' => 'donacion_' . $datos['donacion_id'],
                        'title' => '💖 Donación para TaxImporter',
                        'description' => 'Apoyo al desarrollo de TaxImporter - Calculadora de impuestos Amazon',
                        'quantity' => 1,
                        'currency_id' => 'ARS',
                        'unit_price' => (float)$datos['monto']
                    ]
                ],
                'payer' => [
                    'name' => $datos['usuario_nombre'],
                    'email' => $datos['usuario_email']
                ],
                'external_reference' => $datos['external_reference'],
                'back_urls' => [
                    'success' => base_url('donacion/exito'),
                    'failure' => base_url('donacion/fallo'),
                    'pending' => base_url('donacion/exito')
                ],
                'auto_return' => 'approved',
                'notification_url' => base_url('donacion/webhook'),
                'statement_descriptor' => 'TaxImporter',
                'payment_methods' => [
                    'default_payment_method_id' => null,
                    'excluded_payment_methods' => [],
                    'excluded_payment_types' => [],
                    'installments' => 12
                ],
                'metadata' => [
                    'donacion_id' => $datos['donacion_id'],
                    'mensaje' => $datos['mensaje'] ?? '',
                    'tipo' => 'donacion'
                ]
            ];

            $response = $this->makeRequest('POST', '/checkout/preferences', $preference);
            
            if (!$response || !isset($response['id'])) {
                throw new \Exception('Respuesta inválida de MercadoPago');
            }

            return $response;

        } catch (\Exception $e) {
            log_message('error', 'Error creando preferencia MP: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Obtener información de un pago específico
     */
    public function obtenerPago($paymentId)
    {
        try {
            return $this->makeRequest('GET', "/v1/payments/{$paymentId}");
        } catch (\Exception $e) {
            log_message('error', 'Error obteniendo pago MP: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Obtener información de una preferencia
     */
    public function obtenerPreferencia($preferenceId)
    {
        try {
            return $this->makeRequest('GET', "/checkout/preferences/{$preferenceId}");
        } catch (\Exception $e) {
            log_message('error', 'Error obteniendo preferencia MP: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Realizar petición HTTP a MercadoPago
     */
    private function makeRequest($method, $endpoint, $data = null)
    {
        $curl = curl_init();

        $headers = [
            'Authorization: Bearer ' . $this->accessToken,
            'Content-Type: application/json',
            'X-Idempotency-Key: ' . uniqid()
        ];

        $options = [
            CURLOPT_URL => $this->baseUrl . $endpoint,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_CUSTOMREQUEST => $method
        ];

        if ($data && in_array($method, ['POST', 'PUT', 'PATCH'])) {
            $options[CURLOPT_POSTFIELDS] = json_encode($data);
        }

        curl_setopt_array($curl, $options);

        $response = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $error = curl_error($curl);
        
        curl_close($curl);

        if ($error) {
            throw new \Exception("Error de cURL: {$error}");
        }

        if (!$response) {
            throw new \Exception("Respuesta vacía de MercadoPago");
        }

        $decodedResponse = json_decode($response, true);

        if ($httpCode >= 400) {
            $errorMsg = isset($decodedResponse['message']) 
                ? $decodedResponse['message'] 
                : "Error HTTP {$httpCode}";
            
            throw new \Exception("Error de MercadoPago: {$errorMsg}");
        }

        return $decodedResponse;
    }

    /**
     * Verificar configuración de MercadoPago
     */
    public function verificarConfiguracion()
    {
        try {
            // Hacer una petición simple para verificar credenciales
            $response = $this->makeRequest('GET', '/v1/payment_methods');
            return !empty($response);
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Obtener métodos de pago disponibles
     */
    public function obtenerMetodosPago()
    {
        try {
            return $this->makeRequest('GET', '/v1/payment_methods');
        } catch (\Exception $e) {
            log_message('error', 'Error obteniendo métodos de pago: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Formatear estado de pago para mostrar al usuario
     */
    public function formatearEstado($estado)
    {
        $estados = [
            'pending' => ['texto' => 'Pendiente', 'clase' => 'warning', 'icono' => 'clock'],
            'approved' => ['texto' => 'Aprobado', 'clase' => 'success', 'icono' => 'check-circle'],
            'authorized' => ['texto' => 'Autorizado', 'clase' => 'success', 'icono' => 'check-circle'],
            'in_process' => ['texto' => 'En Proceso', 'clase' => 'info', 'icono' => 'hourglass'],
            'in_mediation' => ['texto' => 'En Mediación', 'clase' => 'warning', 'icono' => 'exclamation-triangle'],
            'rejected' => ['texto' => 'Rechazado', 'clase' => 'danger', 'icono' => 'x-circle'],
            'cancelled' => ['texto' => 'Cancelado', 'clase' => 'secondary', 'icono' => 'x'],
            'refunded' => ['texto' => 'Reembolsado', 'clase' => 'info', 'icono' => 'arrow-left-circle'],
            'charged_back' => ['texto' => 'Contracargo', 'clase' => 'danger', 'icono' => 'shield-x']
        ];

        return $estados[$estado] ?? ['texto' => 'Desconocido', 'clase' => 'secondary', 'icono' => 'question'];
    }
}