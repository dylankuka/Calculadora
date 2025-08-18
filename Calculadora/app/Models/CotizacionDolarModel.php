<?php
namespace App\Models;

use CodeIgniter\Model;

class CotizacionDolarModel extends Model
{
    protected $table      = 'cotizaciones_dolar';
    protected $primaryKey = 'id';
    protected $allowedFields = ['tipo', 'valor_ars', 'fecha', 'id_usuario'];
    protected $returnType = 'array';
    
    protected $useTimestamps = false;
    
    // Validaciones
    protected $validationRules = [
        'tipo'      => 'required|in_list[MEP,tarjeta,oficial,blue]',
        'valor_ars' => 'required|decimal',
        'id_usuario' => 'permit_empty|integer'
    ];
    
    protected $validationMessages = [
        'tipo' => [
            'required' => 'El tipo de cotización es obligatorio.',
            'in_list'  => 'El tipo debe ser: MEP, tarjeta, oficial o blue.'
        ],
        'valor_ars' => [
            'required' => 'El valor en ARS es obligatorio.',
            'decimal'  => 'El valor debe ser un número decimal válido.'
        ]
    ];
    
    /**
     * Obtener la cotización más reciente por tipo
     */
    public function obtenerUltimaCotizacion($tipo)
    {
        return $this->where('tipo', $tipo)
                    ->orderBy('fecha', 'DESC')
                    ->first();
    }
    
    /**
     * Obtener cotizaciones del día actual
     */
    public function obtenerCotizacionesHoy()
    {
        return $this->where('DATE(fecha)', date('Y-m-d'))
                    ->orderBy('fecha', 'DESC')
                    ->findAll();
    }
}