<?php
namespace App\Models;

use CodeIgniter\Model;

class HistorialModel extends Model
{
    protected $table = 'historial_calculos';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'usuario_id',
        'amazon_url', 
        'nombre_producto',
        'precio_usd', 
        'total_ars',
        'desglose_json',
        'fecha_calculo'
    ];
    protected $returnType = 'array';
    protected $useTimestamps = false;
    
    // ✅ VALIDACIONES EN EL MODELO
    protected $validationRules = [
        'usuario_id' => 'required|integer',
        'amazon_url' => 'required|valid_url|max_length[500]',
        'nombre_producto' => 'required|max_length[200]',
        'precio_usd' => 'required|decimal|greater_than[0]',
        'total_ars' => 'required|decimal|greater_than[0]'
    ];

    protected $validationMessages = [
        'usuario_id' => [
            'required' => 'El ID de usuario es obligatorio.',
            'integer' => 'El ID de usuario debe ser un número válido.'
        ],
        'amazon_url' => [
            'required' => 'La URL es obligatoria.',
            'valid_url' => 'Debe ser una URL válida.',
            'max_length' => 'La URL no puede exceder 500 caracteres.'
        ],
        'nombre_producto' => [
            'required' => 'El nombre del producto es obligatorio.',
            'max_length' => 'El nombre no puede exceder 200 caracteres.'
        ],
        'precio_usd' => [
            'required' => 'El precio en USD es obligatorio.',
            'decimal' => 'El precio debe ser un número válido.',
            'greater_than' => 'El precio debe ser mayor a 0.'
        ],
        'total_ars' => [
            'required' => 'El total en ARS es obligatorio.',
            'decimal' => 'El total debe ser un número válido.',
            'greater_than' => 'El total debe ser mayor a 0.'
        ]
    ];

    // ✅ MÉTODOS PERSONALIZADOS PARA EL CRUD
    public function obtenerPorUsuario($usuarioId, $limite = 20)
    {
        return $this->where('usuario_id', $usuarioId)
                    ->orderBy('fecha_calculo', 'DESC')
                    ->limit($limite)
                    ->find();
    }

    public function obtenerResumenUsuario($usuarioId)
    {
        return $this->selectSum('total_ars', 'total_calculado')
                    ->selectCount('id', 'total_consultas')
                    ->where('usuario_id', $usuarioId)
                    ->first();
    }

    public function buscarPorProducto($usuarioId, $busqueda)
    {
        return $this->where('usuario_id', $usuarioId)
                    ->like('nombre_producto', $busqueda)
                    ->orderBy('fecha_calculo', 'DESC')
                    ->find();
    }
}