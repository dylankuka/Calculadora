<?php
namespace App\Models;

use CodeIgniter\Model;

class DonacionModel extends Model
{
    protected $table      = 'donaciones';
    protected $primaryKey = 'id';
    protected $allowedFields = ['id_usuario', 'monto_ars', 'metodo_pago', 'fecha_donacion'];
    protected $returnType = 'array';
    
    protected $useTimestamps = false;
    
    // Validaciones
    protected $validationRules = [
        'id_usuario'  => 'required|integer',
        'monto_ars'   => 'required|decimal',
        'metodo_pago' => 'required|max_length[50]'
    ];
    
    protected $validationMessages = [
        'id_usuario' => [
            'required' => 'El ID del usuario es obligatorio.',
            'integer'  => 'El ID del usuario debe ser un número entero.'
        ],
        'monto_ars' => [
            'required' => 'El monto es obligatorio.',
            'decimal'  => 'El monto debe ser un número válido.'
        ],
        'metodo_pago' => [
            'required'   => 'El método de pago es obligatorio.',
            'max_length' => 'El método de pago no puede exceder 50 caracteres.'
        ]
    ];
}