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
        'fecha_calculo',
        'categoria_id',
        'metodo_pago',
        'valor_cif_usd',
        'excedente_400_usd'
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
        return $this->select('historial_calculos.*, categorias_productos.nombre as categoria_nombre')
                    ->join('categorias_productos', 'categorias_productos.id = historial_calculos.categoria_id', 'left')
                    ->where('historial_calculos.usuario_id', $usuarioId)
                    ->orderBy('historial_calculos.fecha_calculo', 'DESC')
                    ->limit($limite)
                    ->find();
    }

    public function obtenerResumenUsuario($usuarioId)
    {
        $resultado = $this->selectSum('total_ars', 'total_calculado')
                          ->selectCount('id', 'total_consultas')
                          ->selectAvg('valor_cif_usd', 'promedio_cif_usd')
                          ->where('usuario_id', $usuarioId)
                          ->first();
        
        return [
            'total_calculado' => $resultado['total_calculado'] ?? 0,
            'total_consultas' => $resultado['total_consultas'] ?? 0,
            'promedio_cif_usd' => $resultado['promedio_cif_usd'] ?? 0
        ];
    }

    public function buscarPorProducto($usuarioId, $busqueda)
    {
        return $this->select('historial_calculos.*, categorias_productos.nombre as categoria_nombre')
                    ->join('categorias_productos', 'categorias_productos.id = historial_calculos.categoria_id', 'left')
                    ->where('historial_calculos.usuario_id', $usuarioId)
                    ->groupStart()
                        ->like('historial_calculos.nombre_producto', $busqueda)
                        ->orLike('categorias_productos.nombre', $busqueda)
                    ->groupEnd()
                    ->orderBy('historial_calculos.fecha_calculo', 'DESC')
                    ->find();
    }
    
    /**
     * Obtener estadísticas generales (para admin)
     */
    public function obtenerEstadisticasGenerales()
    {
        return $this->selectSum('total_ars', 'total_calculado')
                    ->selectCount('id', 'total_calculos')
                    ->selectAvg('precio_usd', 'promedio_usd')
                    ->selectAvg('total_ars', 'promedio_ars')
                    ->first();
    }
    
    /**
     * Obtener cálculos por mes (últimos 12 meses)
     */
    public function obtenerCalculosPorMes($limite = 12)
    {
        $db = \Config\Database::connect();
        return $db->query("
            SELECT 
                DATE_FORMAT(fecha_calculo, '%Y-%m') as mes,
                COUNT(*) as cantidad,
                SUM(total_ars) as total_ars,
                AVG(precio_usd) as promedio_usd
            FROM historial_calculos
            WHERE fecha_calculo >= DATE_SUB(NOW(), INTERVAL {$limite} MONTH)
            GROUP BY mes
            ORDER BY mes ASC
        ")->getResultArray();
    }
    
    /**
     * Obtener top usuarios por cantidad de cálculos
     */
    public function obtenerTopUsuarios($limite = 10)
    {
        return $this->select('usuarios.id, usuarios.nombredeusuario, usuarios.email')
                    ->selectCount('historial_calculos.id', 'total_calculos')
                    ->selectSum('historial_calculos.total_ars', 'total_calculado')
                    ->join('usuarios', 'usuarios.id = historial_calculos.usuario_id')
                    ->groupBy('usuarios.id')
                    ->orderBy('total_calculos', 'DESC')
                    ->limit($limite)
                    ->find();
    }
    
    /**
     * Obtener categorías más usadas
     */
    public function obtenerCategoriasPopulares($limite = 10)
    {
        return $this->select('categorias_productos.id, categorias_productos.nombre, categorias_productos.arancel_porcentaje')
                    ->selectCount('historial_calculos.id', 'cantidad_usos')
                    ->selectAvg('historial_calculos.precio_usd', 'promedio_usd')
                    ->join('categorias_productos', 'categorias_productos.id = historial_calculos.categoria_id')
                    ->groupBy('categorias_productos.id')
                    ->orderBy('cantidad_usos', 'DESC')
                    ->limit($limite)
                    ->find();
    }
}