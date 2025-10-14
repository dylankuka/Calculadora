<?php
namespace App\Controllers;

use App\Models\DonacionModel;
use App\Services\MercadoPagoService;
use MercadoPago\Client\Preference\PreferenceClient;
use MercadoPago\MercadoPagoConfig;

MercadoPagoConfig::setAccessToken(getenv('MERCADOPAGO_ACCESS_TOKEN'));


class DonacionController extends BaseController
{

public function testCredenciales() 
{
    try {
        echo "<h2>🔍 Test de Credenciales MercadoPago</h2>";
        echo "<hr>";
        
        // 1. Verificar variables de entorno
        echo "<h3>1️⃣ Variables de Entorno</h3>";
        $accessToken = getenv('MERCADOPAGO_ACCESS_TOKEN');
        $publicKey = getenv('MERCADOPAGO_PUBLIC_KEY');
        
        echo "Access Token: " . ($accessToken ? "✅ Configurado (" . substr($accessToken, 0, 20) . "...)" : "❌ NO configurado") . "<br>";
        echo "Public Key: " . ($publicKey ? "✅ Configurado (" . substr($publicKey, 0, 20) . "...)" : "❌ NO configurado") . "<br>";
        echo "Modo: " . (strpos($accessToken, 'TEST') === 0 ? "🧪 SANDBOX (Pruebas)" : "🚀 PRODUCCIÓN") . "<br>";
        echo "<hr>";
        
        if (!$accessToken || !$publicKey) {
            die("<strong>❌ ERROR:</strong> Credenciales no configuradas en .env");
        }
        
        // 2. Configurar SDK
        echo "<h3>2️⃣ Configuración del SDK</h3>";
        MercadoPagoConfig::setAccessToken($accessToken);
        echo "SDK inicializado ✅<br>";
        echo "<hr>";
        
        // 3. Crear preferencia de prueba
        echo "<h3>3️⃣ Crear Preferencia de Prueba</h3>";
        $client = new PreferenceClient();
        
        $testPreference = [
            'items' => [
                [
                    'id' => 'test_' . time(),
                    'title' => 'Test Item',
                    'quantity' => 1,
                    'currency_id' => 'ARS',
                    'unit_price' => 100.0
                ]
            ],
            'back_urls' => [
                'success' => base_url('donacion/success'),
                'failure' => base_url('donacion/failure'),
                'pending' => base_url('donacion/success')
            ],
            'external_reference' => 'TEST_' . time()
        ];
        
        echo "Enviando solicitud a MercadoPago...<br>";
        $preference = $client->create($testPreference);
        
        echo "✅ <strong>Preferencia creada exitosamente!</strong><br>";
        echo "ID: " . $preference->id . "<br>";
        echo "Init Point: <a href='{$preference->init_point}' target='_blank'>{$preference->init_point}</a><br>";
        
        if (isset($preference->sandbox_init_point)) {
            echo "Sandbox Init Point: <a href='{$preference->sandbox_init_point}' target='_blank'>{$preference->sandbox_init_point}</a><br>";
        }
        
        echo "<hr>";
        echo "<h3>✅ TODO FUNCIONA CORRECTAMENTE</h3>";
        echo "<p>Tus credenciales están bien configuradas y la integración funciona.</p>";
        echo "<p><a href='" . base_url('donacion') . "'>Ir a Donaciones</a></p>";
        
    } catch (\MercadoPago\Exceptions\MPApiException $e) {
        echo "<h3>❌ ERROR DE API MERCADOPAGO</h3>";
        echo "<strong>Mensaje:</strong> " . $e->getMessage() . "<br>";
        echo "<strong>Status Code:</strong> " . ($e->getApiResponse() ? $e->getApiResponse()->getStatusCode() : 'N/A') . "<br>";
        echo "<strong>Response:</strong> <pre>" . json_encode($e->getApiResponse() ? $e->getApiResponse()->getContent() : 'N/A', JSON_PRETTY_PRINT) . "</pre>";
        
        echo "<hr><h4>Posibles soluciones:</h4><ul>";
        echo "<li>Verifica que tu Access Token sea válido</li>";
        echo "<li>Asegúrate de usar credenciales TEST para modo sandbox</li>";
        echo "<li>Revisa que las credenciales estén correctamente copiadas en el .env</li>";
        echo "</ul>";
        
    } catch (\Exception $e) {
        echo "<h3>❌ ERROR GENERAL</h3>";
        echo "<strong>Mensaje:</strong> " . $e->getMessage() . "<br>";
        echo "<strong>Trace:</strong> <pre>" . $e->getTraceAsString() . "</pre>";
    }
}
    private $donacionModel;
    private $mercadoPagoService;

    public function __construct()
    {
        $this->donacionModel = new DonacionModel();
        $this->mercadoPagoService = new MercadoPagoService();
    }

    /**
     * Validar sesión de usuario
     */
    private function validarSesion()
    {
        if (!session()->get('logueado')) {
            return redirect()->to('/usuario/login')
                ->with('error', '❌ Debes iniciar sesión para realizar donaciones.');
        }
        return null;
    }

    /**
     * Mostrar página de donaciones
     */
    public function index()
    {
        $redirect = $this->validarSesion();
        if ($redirect) return $redirect;

        $usuarioId = session()->get('usuario_id');
        
        // Obtener historial de donaciones del usuario
        $misDonaciones = $this->donacionModel->obtenerPorUsuario($usuarioId, 10);
        $resumen = $this->donacionModel->obtenerResumenUsuario($usuarioId);
        $estadisticas = $this->donacionModel->obtenerEstadisticas();

        return view('donacion/index', [
            'mis_donaciones' => $misDonaciones,
            'resumen' => $resumen,
            'estadisticas' => $estadisticas
        ]);
    }


    /**
     * Crear nueva donación y redirigir a MercadoPago
     */
   /**
     * Checkout directo - Crear preferencia y redirigir inmediatamente
     */

/**
 * Checkout directo - Crear preferencia y redirigir inmediatamente
 */
public function checkout($monto)
{
    $redirect = $this->validarSesion();
    if ($redirect) return $redirect;

    // Validar monto
    $montosPermitidos = [500, 1000, 2500, 5000, 10000];
    $monto = (int)$monto;
    
    if (!in_array($monto, $montosPermitidos)) {
        return redirect()->to('/donacion')
            ->with('error', '❌ Monto de donación no válido.');
    }

    try {
        $usuarioId = session()->get('usuario_id');
        
        // Log inicial
        log_message('info', "Iniciando checkout para usuario {$usuarioId} - Monto: {$monto}");
        
        // Verificar que las credenciales estén configuradas
        $accessToken = getenv('MERCADOPAGO_ACCESS_TOKEN');
        $publicKey = getenv('MERCADOPAGO_PUBLIC_KEY');
        
        if (empty($accessToken) || empty($publicKey)) {
            log_message('error', 'Credenciales de MercadoPago no configuradas');
            throw new \Exception('Sistema de pagos no disponible. Contacta al administrador.');
        }

        log_message('info', "Credenciales encontradas. Access Token: " . substr($accessToken, 0, 15) . "...");

        // Generar referencia única
        $referencia = 'DON_' . $usuarioId . '_' . time() . '_' . rand(1000, 9999);
        log_message('info', "Referencia generada: {$referencia}");

        // Crear registro en base de datos PRIMERO
        $datosBasicos = [
'id_usuario' => $usuarioId,
    'monto_ars' => $monto,
    'metodo_pago' => 'mercadopago',
    'estado' => 'pendiente',
    'external_reference' => $referencia,
    'fecha_donacion' => date('Y-m-d H:i:s'),
    'usuario_email' => session()->get('usuario_email'),  // ✅ AGREGAR
    'usuario_nombre' => session()->get('usuario_nombre'), // ✅ AGREGAR
    'datos_mp_json' => json_encode([
        'monto_seleccionado' => $monto,
        'ip' => $this->request->getIPAddress(),
        'user_agent' => substr($this->request->getUserAgent()->__toString(), 0, 200)
    ])
        ];

        $donacionId = $this->donacionModel->insert($datosBasicos);

        if (!$donacionId) {
            log_message('error', 'Error al insertar donación en BD');
            throw new \Exception('Error al registrar donación. Intenta nuevamente.');
        }

        log_message('info', "Donación {$donacionId} creada en BD");

        // Preparar datos para la preferencia
        $datosPreferencia = [
            'monto' => $monto,
            'donacion_id' => $donacionId,
            'external_reference' => $referencia,
            'usuario_nombre' => session()->get('usuario_nombre') ?? 'Donante Anónimo',
            'usuario_email' => session()->get('usuario_email') ?? 'donante@ejemplo.com',
            'mensaje' => "Donación de $monto ARS para TaxImporter"
        ];

        log_message('info', "Creando preferencia en MercadoPago para donación {$donacionId}");

        // Crear preferencia en MercadoPago
        $preferencia = $this->mercadoPagoService->crearPreferenciaDonacion($datosPreferencia);

        if (!$preferencia || !isset($preferencia['id'])) {
            log_message('error', 'MercadoPago no devolvió preference_id válido');
            throw new \Exception('Error al comunicarse con MercadoPago. Intenta en unos minutos.');
        }

        log_message('info', "Preferencia creada: {$preferencia['id']}");

        // Actualizar donación con preference_id
        $updateResult = $this->donacionModel->update($donacionId, [
            'preference_id' => $preferencia['id']
        ]);

        if (!$updateResult) {
            log_message('warning', "No se pudo actualizar preference_id en donación {$donacionId}");
        }

        // Obtener URL de pago
        $urlPago = $this->mercadoPagoService->obtenerUrlPago($preferencia);

        if (empty($urlPago)) {
            log_message('error', 'URL de pago vacía');
            throw new \Exception('No se pudo generar el enlace de pago.');
        }

        log_message('info', "Redirigiendo a: {$urlPago}");

        // Redirigir a MercadoPago
        return redirect()->to($urlPago);

    } catch (\MercadoPago\Exceptions\MPApiException $e) {
        log_message('error', 'Error API MercadoPago en checkout: ' . $e->getMessage());
        log_message('error', 'Status Code: ' . $e->getApiResponse()->getStatusCode());
        log_message('error', 'Response: ' . json_encode($e->getApiResponse()->getContent()));
        
        if (isset($donacionId)) {
            $this->donacionModel->update($donacionId, [
                'estado' => 'cancelado',
                'datos_mp_json' => json_encode([
                    'error' => $e->getMessage(),
                    'api_response' => $e->getApiResponse()->getContent()
                ])
            ]);
        }
        
        return redirect()->to('/donacion')
            ->with('error', '❌ Error de MercadoPago: ' . $e->getMessage() . '. Verifica tus credenciales.');
            
            
        } catch (\Exception $e) {
            log_message('error', 'Error en checkout directo: ' . $e->getMessage());
            
            // Si ya se creó la donación, marcarla como error
            if (isset($donacionId)) {
                $this->donacionModel->update($donacionId, [
                    'estado' => 'cancelado',
                    'datos_mp_json' => json_encode(['error' => $e->getMessage()])
                ]);
            }
            
            return redirect()->to('/donacion')
                ->with('error', '❌ Error al procesar donación: ' . $e->getMessage());
        }
    }

    /**
     * Página de éxito después del pago
     */
    public function success()
    {
        $paymentId = $this->request->getGet('payment_id');
        $status = $this->request->getGet('status');
        $externalReference = $this->request->getGet('external_reference');

        // Inicializar variables
        $donacion = null;
        $payment = null;
        $estadoLocal = 'pendiente';

        if ($paymentId && $externalReference) {
            // Obtener donación
            $donacion = $this->donacionModel->obtenerPorReferencia($externalReference);
            
            if ($donacion && $donacion['id_usuario'] == session()->get('usuario_id')) {
                try {
                    // Verificar estado con MercadoPago
                    $payment = $this->mercadoPagoService->obtenerPago($paymentId);
                    
                    if ($payment) {
                        $estadoLocal = $this->mapearEstadoMP($payment['status']);
                        
                        $this->donacionModel->actualizarEstado(
                            $donacion['id'], 
                            $estadoLocal, 
                            $paymentId, 
                            $payment
                        );
                    }
                } catch (\Exception $e) {
                    log_message('warning', 'Error verificando pago en success: ' . $e->getMessage());
                }
            }
        }

        return view('donacion/success', [
            'donacion' => $donacion,
            'payment' => $payment,
            'estado' => $estadoLocal,
            'mensaje' => $this->obtenerMensajeEstado($estadoLocal)
        ]);
    }

    /**
     * Página de fallo/cancelación
     */
    public function failure()
    {
        $externalReference = $this->request->getGet('external_reference');
        $donacion = null;

        if ($externalReference) {
            $donacion = $this->donacionModel->obtenerPorReferencia($externalReference);
            
            if ($donacion && $donacion['id_usuario'] == session()->get('usuario_id')) {
                // Actualizar estado a cancelado
                $this->donacionModel->actualizarEstado($donacion['id'], 'cancelado');
            }
        }

        return view('donacion/failure', [
            'donacion' => $donacion,
            'mensaje' => 'El pago fue cancelado o rechazado. Puedes intentar nuevamente cuando gustes.'
        ]);
    }

    /**
     * Página de pendiente (redirige a success)
     */
    public function pending()
    {
        // Redirigir a success ya que manejaremos todos los estados ahí
        return $this->success();
    }

    /**
     * Obtener mensaje según estado del pago
     */
    private function obtenerMensajeEstado($estado)
    {
        $mensajes = [
            'aprobado' => '✅ ¡Tu donación fue procesada exitosamente! Gracias por apoyar TaxImporter.',
            'pendiente' => '⏳ Tu donación está siendo procesada. Te notificaremos cuando se complete.',
            'rechazado' => '❌ Tu pago fue rechazado. Puedes intentar con otro método de pago.',
            'cancelado' => '🚫 El pago fue cancelado. Puedes intentar nuevamente cuando gustes.'
        ];

        return $mensajes[$estado] ?? '❓ Estado de pago desconocido. Contacta soporte si persiste el problema.';
    }

    /**
     * Webhook de MercadoPago para notificaciones
     */
    public function webhook()
{
    try {
        $json = $this->request->getJSON();
        
        // Verificar que sea una notificación de payment
        if ($json->type !== 'payment') {
            return $this->response->setStatusCode(200, 'OK');
        }

        $paymentId = $json->data->id ?? null;
        
        if (!$paymentId) {
            return $this->response->setStatusCode(400, 'Payment ID requerido');
        }

        // Obtener información del pago desde MercadoPago
        $payment = $this->mercadoPagoService->obtenerPago($paymentId);
        
        if (!$payment) {
            return $this->response->setStatusCode(404, 'Payment no encontrado');
        }

        // Buscar donación por external_reference
        $donacion = $this->donacionModel->obtenerPorReferencia($payment['external_reference']);
        
        if (!$donacion) {
            log_message('warning', 'Donación no encontrada para referencia: ' . $payment['external_reference']);
            return $this->response->setStatusCode(404, 'Donación no encontrada');
        }

        // Mapear estado de MercadoPago
        $estadoMP = $payment['status'];
        $estadoLocal = $this->mapearEstadoMP($estadoMP);

        // Actualizar estado
        $this->donacionModel->actualizarEstado(
            $donacion['id'], 
            $estadoLocal, 
            $paymentId, 
            $payment
        );

        log_message('info', "Donación {$donacion['id']} actualizada a estado: $estadoLocal");

        // ✅ ENVIAR EMAIL SI EL PAGO FUE APROBADO
if ($estadoLocal === 'aprobado') {
    try {
        $emailService = new \App\Services\EmailService();
        $emailService->enviarConfirmacionDonacion(
            $donacion['usuario_email'],      // ✅ Cambiar esto
            $donacion['usuario_nombre'],     // ✅ Y esto
            $donacion['monto_ars'],
            $donacion['external_reference']
        );
        log_message('info', "Email de confirmación enviado para donación {$donacion['id']}");
    } catch (\Exception $e) {
        log_message('error', "Error enviando email: " . $e->getMessage());
    }
}

        return $this->response->setStatusCode(200, 'OK');

    } catch (\Exception $e) {
        log_message('error', 'Error en webhook: ' . $e->getMessage());
        return $this->response->setStatusCode(500, 'Error interno');
    }
}

    /**
     * Página de éxito después del pago
     */
    public function exito()
    {
        $paymentId = $this->request->getGet('payment_id');
        $status = $this->request->getGet('status');
        $externalReference = $this->request->getGet('external_reference');

        if ($paymentId && $externalReference) {
            // Obtener donación
            $donacion = $this->donacionModel->obtenerPorReferencia($externalReference);
            
            if ($donacion && $donacion['id_usuario'] == session()->get('usuario_id')) {
                // Verificar estado con MercadoPago
                $payment = $this->mercadoPagoService->obtenerPago($paymentId);
                
                if ($payment) {
                    $estadoLocal = $this->mapearEstadoMP($payment['status']);
                    
                    $this->donacionModel->actualizarEstado(
                        $donacion['id'], 
                        $estadoLocal, 
                        $paymentId, 
                        $payment
                    );
                }

                return view('donacion/exito', [
                    'donacion' => $donacion,
                    'payment' => $payment ?? null,
                    'estado' => $estadoLocal ?? 'pendiente'
                ]);
            }
        }

        return redirect()->to('/donacion')
            ->with('success', '✅ ¡Gracias por tu donación! Será procesada en breve.');
    }

    /**
     * Página de fallo/cancelación
     */
    public function fallo()
    {
        $externalReference = $this->request->getGet('external_reference');

        if ($externalReference) {
            $donacion = $this->donacionModel->obtenerPorReferencia($externalReference);
            
            if ($donacion && $donacion['id_usuario'] == session()->get('usuario_id')) {
                // Actualizar estado a cancelado
                $this->donacionModel->actualizarEstado($donacion['id'], 'cancelado');
            }
        }

        return view('donacion/fallo', [
            'mensaje' => 'El pago fue cancelado o rechazado. Puedes intentar nuevamente.'
        ]);
    }

    /**
     * Ver detalles de una donación específica
     */
    public function ver($id)
    {
        $redirect = $this->validarSesion();
        if ($redirect) return $redirect;

        $usuarioId = session()->get('usuario_id');
        $donacion = $this->donacionModel->where('id', $id)
                                       ->where('id_usuario', $usuarioId)
                                       ->first();

        if (!$donacion) {
            return redirect()->to('/donacion')
                ->with('error', '❌ Donación no encontrada.');
        }

        // Obtener información adicional del pago si existe
        $detallesPago = null;
        if ($donacion['payment_id']) {
            try {
                $detallesPago = $this->mercadoPagoService->obtenerPago($donacion['payment_id']);
            } catch (\Exception $e) {
                log_message('warning', 'No se pudieron obtener detalles del pago: ' . $e->getMessage());
            }
        }

        return view('donacion/ver', [
            'donacion' => $donacion,
            'detalles_pago' => $detallesPago
        ]);
    }

    /**
     * Mapear estados de MercadoPago a estados locales
     */
    private function mapearEstadoMP($estadoMP)
    {
        $mapeo = [
            'pending' => 'pendiente',
            'approved' => 'aprobado',
            'authorized' => 'aprobado',
            'in_process' => 'pendiente',
            'in_mediation' => 'pendiente',
            'rejected' => 'rechazado',
            'cancelled' => 'cancelado',
            'refunded' => 'cancelado',
            'charged_back' => 'cancelado'
        ];

        return $mapeo[$estadoMP] ?? 'pendiente';
    }
}