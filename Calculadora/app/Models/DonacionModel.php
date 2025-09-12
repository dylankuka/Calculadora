<?php
namespace App\Models;

use CodeIgniter\Model;

class DonacionModel extends Model
{
    protected $table      = 'donaciones';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'id_usuario', 
        'monto_ars', 
        'metodo_pago', 
        'estado',
        'payment_id',
        'preference_id',
        'external_reference',
        'fecha_donacion',
        'fecha_aprobacion',
        'datos_mp_json'
    ];
    protected $returnType = 'array';
    
    protected $useTimestamps = false;
    
    // Validaciones
    protected $validationRules = [
        'id_usuario'  => 'permit_empty|integer',
        'monto_ars'   => 'required|decimal|greater_than[0]',
        'metodo_pago' => 'required|max_length[50]',
        'estado'      => 'required|in_list[pendiente,aprobado,rechazado,cancelado]'
    ];
    
    protected $validationMessages = [
        'monto_ars' => [
            'required' => 'El monto es obligatorio.',
            'decimal'  => 'El monto debe ser un número válido.',
            'greater_than' => 'El monto debe ser mayor a $0.'
        ],
        'metodo_pago' => [
            'required'   => 'El método de pago es obligatorio.',
            'max_length' => 'El método de pago no puede exceder 50 caracteres.'
        ],
        'estado' => [
            'required' => 'El estado es obligatorio.',
            'in_list' => 'Estado debe ser: pendiente, aprobado, rechazado o cancelado.'
        ]
    ];

    /**
     * Obtener donaciones por usuario
     */
    public function obtenerPorUsuario($usuarioId, $limite = 20)
    {
        return $this->where('id_usuario', $usuarioId)
                    ->orderBy('fecha_donacion', 'DESC')
                    ->limit($limite)
                    ->findAll();
    }

    /**
     * Obtener resumen de donaciones de un usuario
     */
    public function obtenerResumenUsuario($usuarioId)
    {
        return $this->selectSum('monto_ars', 'total_donado')
                    ->selectCount('id', 'total_donaciones')
                    ->where('id_usuario', $usuarioId)
                    ->where('estado', 'aprobado')
                    ->first();
    }

    /**
     * Obtener donación por payment_id de MercadoPago
     */
    public function obtenerPorPaymentId($paymentId)
    {
        return $this->where('payment_id', $paymentId)->first();
    }

    /**
     * Obtener donación por external_reference
     */
    public function obtenerPorReferencia($referencia)
    {
        return $this->where('external_reference', $referencia)->first();
    }

    /**
     * Actualizar estado de donación
     */
    public function actualizarEstado($id, $estado, $paymentId = null, $datosMP = null)
    {
        $datos = [
            'estado' => $estado
        ];

        if ($paymentId) {
            $datos['payment_id'] = $paymentId;
        }

        if ($estado === 'aprobado') {
            $datos['fecha_aprobacion'] = date('Y-m-d H:i:s');
        }

        if ($datosMP) {
            $datos['datos_mp_json'] = json_encode($datosMP);
        }

        return $this->update($id, $datos);
    }

    /**
     * Obtener estadísticas generales de donaciones
     */
    public function obtenerEstadisticas()
    {
        $total = $this->selectSum('monto_ars', 'total')
                     ->where('estado', 'aprobado')
                     ->first();

        $cantidad = $this->where('estado', 'aprobado')
                        ->countAllResults();

        $promedio = $cantidad > 0 ? $total['total'] / $cantidad : 0;

        return [
            'total_recaudado' => $total['total'] ?? 0,
            'cantidad_donaciones' => $cantidad,
            'promedio_donacion' => $promedio
        ];
    }
}