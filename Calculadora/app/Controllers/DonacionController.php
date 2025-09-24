<?php
namespace App\Controllers;

use App\Models\DonacionModel;
use App\Services\MercadoPagoService;

class DonacionController extends BaseController
{
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
            
            // Verificar configuración de MercadoPago
            $verificacion = $this->mercadoPagoService->verificarConfiguracion();
            if (!$verificacion['success']) {
                throw new \Exception('Sistema de pagos temporalmente no disponible: ' . $verificacion['message']);
            }

            // Generar referencia única
            $referencia = 'DON_' . $usuarioId . '_' . time() . '_' . rand(1000, 9999);

            // Crear registro en base de datos
            $datosBasicos = [
                'id_usuario' => $usuarioId,
                'monto_ars' => $monto,
                'metodo_pago' => 'mercadopago',
                'estado' => 'pendiente',
                'external_reference' => $referencia,
                'fecha_donacion' => date('Y-m-d H:i:s'),
                'datos_mp_json' => json_encode([
                    'monto_seleccionado' => $monto,
                    'ip' => $this->request->getIPAddress(),
                    'user_agent' => substr($this->request->getUserAgent(), 0, 200)
                ])
            ];

            $donacionId = $this->donacionModel->insert($datosBasicos);

            if (!$donacionId) {
                throw new \Exception('Error al crear donación en base de datos');
            }

            // Crear preferencia en MercadoPago
            $preferencia = $this->mercadoPagoService->crearPreferenciaDonacion([
                'monto' => $monto,
                'donacion_id' => $donacionId,
                'external_reference' => $referencia,
                'usuario_nombre' => session()->get('usuario_nombre') ?? 'Donante Anónimo',
                'usuario_email' => session()->get('usuario_email') ?? 'donante@ejemplo.com',
                'mensaje' => "Donación de $monto ARS para TaxImporter"
            ]);

            if (!$preferencia || !isset($preferencia['id'])) {
                throw new \Exception('Error al crear preferencia de pago en MercadoPago');
            }

            // Actualizar con preference_id
            $this->donacionModel->update($donacionId, [
                'preference_id' => $preferencia['id']
            ]);

            // Obtener URL de pago correcta
            $urlPago = $this->mercadoPagoService->obtenerUrlPago($preferencia);

            log_message('info', "Checkout directo - Donación {$donacionId} creada. Monto: $monto. Redirigiendo a: {$urlPago}");

            // Redirigir INMEDIATAMENTE a MercadoPago
            return redirect()->to($urlPago);

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