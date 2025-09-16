<?php
namespace App\Models;

use CodeIgniter\Model;

class CategoriaProductoModel extends Model
{
    protected $table = 'categorias_productos';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'nombre',
        'arancel_porcentaje', 
        'descripcion',
        'exento_iva'
    ];
    protected $returnType = 'array';
    protected $useTimestamps = false;
    
    // Validaciones
    protected $validationRules = [
        'nombre' => 'required|max_length[100]|is_unique[categorias_productos.nombre,id,{id}]',
        'arancel_porcentaje' => 'required|decimal|greater_than_equal_to[0]|less_than_equal_to[100]',
        'descripcion' => 'permit_empty|max_length[500]',
        'exento_iva' => 'permit_empty|in_list[0,1]'
    ];
    
    protected $validationMessages = [
        'nombre' => [
            'required' => 'El nombre de la categoría es obligatorio.',
            'max_length' => 'El nombre no puede exceder 100 caracteres.',
            'is_unique' => 'Ya existe una categoría con este nombre.'
        ],
        'arancel_porcentaje' => [
            'required' => 'El porcentaje de arancel es obligatorio.',
            'decimal' => 'El arancel debe ser un número decimal válido.',
            'greater_than_equal_to' => 'El arancel no puede ser negativo.',
            'less_than_equal_to' => 'El arancel no puede exceder 100%.'
        ]
    ];
    
    /**
     * Obtener todas las categorías ordenadas alfabéticamente
     */
    public function obtenerTodasOrdenadas()
    {
        return $this->orderBy('nombre', 'ASC')->findAll();
    }
    
    /**
     * Obtener categoría por ID con validación
     */
    public function obtenerPorId($id)
    {
        if (!is_numeric($id) || $id <= 0) {
            return null;
        }
        
        return $this->find($id);
    }
    
    /**
     * Obtener categorías con arancel específico
     */
    public function obtenerPorArancel($porcentaje)
    {
        return $this->where('arancel_porcentaje', $porcentaje)
                    ->orderBy('nombre', 'ASC')
                    ->findAll();
    }
    
    /**
     * Obtener categorías exentas de IVA
     */
    public function obtenerExentasIVA()
    {
        return $this->where('exento_iva', true)
                    ->orderBy('nombre', 'ASC')
                    ->findAll();
    }
    
    /**
     * Actualizar arancel de una categoría específica
     * Útil para cambios de regulación (ej: electrónica de 16% a 8% o 0%)
     */
    public function actualizarArancel($id, $nuevoArancel)
    {
        return $this->update($id, ['arancel_porcentaje' => $nuevoArancel]);
    }
    
    /**
     * Búsqueda de categorías por nombre
     */
    public function buscarPorNombre($termino)
    {
        return $this->like('nombre', $termino)
                    ->orLike('descripcion', $termino)
                    ->orderBy('nombre', 'ASC')
                    ->findAll();
    }
}