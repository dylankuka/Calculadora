<?php
namespace App\Services;

require_once APPPATH . '../vendor/autoload.php';

// Importar el SDK de MercadoPago
use MercadoPago\MercadoPagoConfig;
use MercadoPago\Client\Preference\PreferenceClient;
use MercadoPago\Client\Payment\PaymentClient;
use MercadoPago\Exceptions\MPApiException;

class MercadoPagoService
{
    private $accessToken;
    private $publicKey;
    private $isSandbox;

    public function __construct()
    {
        // Configuración desde variables de entorno
        $this->accessToken = getenv('MERCADOPAGO_ACCESS_TOKEN') ?: env('MERCADOPAGO_ACCESS_TOKEN');
        $this->publicKey = getenv('MERCADOPAGO_PUBLIC_KEY') ?: env('MERCADOPAGO_PUBLIC_KEY');
        
        if (!$this->accessToken || !$this->publicKey) {
            throw new \Exception('Credenciales de MercadoPago no configuradas. Verifica tu archivo .env');
        }
        
        // Determinar si estamos en sandbox o producción
        $this->isSandbox = strpos($this->accessToken, 'TEST') === 0;
        
        // Configurar el SDK
        MercadoPagoConfig::setAccessToken($this->accessToken);
        
        // Configurar el entorno correctamente
        if ($this->isSandbox) {
            MercadoPagoConfig::setRuntimeEnviroment(MercadoPagoConfig::LOCAL);
        } else {
            MercadoPagoConfig::setRuntimeEnviroment(MercadoPagoConfig::SERVER);
        }
    }

    /**
     * Crear preferencia de pago para donación
     */
    /**
 * Crear preferencia de pago para donación
 */
public function crearPreferenciaDonacion($datos)
{
    $client = new PreferenceClient();
    
    $preference = $client->create([
        'items' => [[
            'title' => 'Donacion',
            'quantity' => 1,
            'unit_price' => (float)$datos['monto']
        ]],
        'back_urls' => [
            'success' => base_url('donacion/success'),
            'failure' => base_url('donacion/failure')
        ]
    ]);

    return [
        'id' => $preference->id,
        'init_point' => $preference->init_point
    ];
}

    /**
     * Obtener información de un pago específico
     */
    public function obtenerPago($paymentId)
    {
        try {
            $client = new PaymentClient();
            $payment = $client->get($paymentId);
            
            if (!$payment) {
                throw new \Exception('Pago no encontrado');
            }

            // Convertir a array para facilitar el uso
            return [
                'id' => $payment->id,
                'status' => $payment->status,
                'status_detail' => $payment->status_detail ?? '',
                'external_reference' => $payment->external_reference ?? '',
                'transaction_amount' => $payment->transaction_amount ?? 0,
                'net_received_amount' => $payment->net_received_amount ?? 0,
                'payment_method_id' => $payment->payment_method_id ?? '',
                'payment_type_id' => $payment->payment_type_id ?? '',
                'date_created' => $payment->date_created ?? '',
                'date_approved' => $payment->date_approved ?? '',
                'payer' => [
                    'email' => $payment->payer->email ?? '',
                    'identification' => [
                        'type' => $payment->payer->identification->type ?? '',
                        'number' => $payment->payer->identification->number ?? ''
                    ]
                ],
                'metadata' => $payment->metadata ?? new \stdClass()
            ];

        } catch (MPApiException $e) {
            log_message('error', 'Error obteniendo pago MP: ' . $e->getMessage());
            throw new \Exception('Error obteniendo pago: ' . $e->getMessage());
        } catch (\Exception $e) {
            log_message('error', 'Error general obteniendo pago: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Obtener información de una preferencia
     */
    public function obtenerPreferencia($preferenceId)
    {
        try {
            $client = new PreferenceClient();
            $preference = $client->get($preferenceId);
            
            return [
                'id' => $preference->id,
                'external_reference' => $preference->external_reference,
                'items' => $preference->items,
                'payer' => $preference->payer,
                'back_urls' => $preference->back_urls,
                'auto_return' => $preference->auto_return,
                'date_created' => $preference->date_created
            ];

        } catch (MPApiException $e) {
            log_message('error', 'Error obteniendo preferencia MP: ' . $e->getMessage());
            throw new \Exception('Error obteniendo preferencia: ' . $e->getMessage());
        }
    }

    /**
     * Verificar configuración de MercadoPago
     */
    public function verificarConfiguracion()
    {
        try {
            // Crear una preferencia de prueba simple para verificar credenciales
            $client = new PreferenceClient();
            
            $testPreference = [
                'items' => [
                    [
                        'id' => 'test_item',
                        'title' => 'Test',
                        'quantity' => 1,
                        'currency_id' => 'ARS',
                        'unit_price' => 1.0
                    ]
                ]
            ];
            
            // Solo crear para verificar, no usar
            $response = $client->create($testPreference);
            
            return [
                'success' => true,
                'is_sandbox' => $this->isSandbox,
                'message' => $this->isSandbox ? 
                    'Configuración correcta - Modo SANDBOX (pruebas)' : 
                    'Configuración correcta - Modo PRODUCCIÓN'
            ];

        } catch (MPApiException $e) {
            return [
                'success' => false,
                'message' => 'Error de credenciales: ' . $e->getMessage(),
                'is_sandbox' => $this->isSandbox
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error general: ' . $e->getMessage(),
                'is_sandbox' => $this->isSandbox
            ];
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

    /**
     * Obtener URL de pago según el entorno
     */
    public function obtenerUrlPago($preferencia)
    {
        return $this->isSandbox ? 
            ($preferencia['sandbox_init_point'] ?? $preferencia['init_point']) : 
            $preferencia['init_point'];
    }

    /**
     * Validar webhook de MercadoPago
     */
    public function validarWebhook($headers, $body)
    {
        // Implementar validación de webhook si MercadoPago lo requiere
        // Por ahora, validación básica
        return true;
    }

    /**
     * Obtener información del entorno actual
     */
    public function getEntornoInfo()
    {
        return [
            'is_sandbox' => $this->isSandbox,
            'access_token_prefix' => substr($this->accessToken, 0, 10) . '...',
            'public_key_prefix' => substr($this->publicKey, 0, 10) . '...'
        ];
    }
}