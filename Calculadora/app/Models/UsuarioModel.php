<?php
namespace App\Models;

use CodeIgniter\Model;

class UsuarioModel extends Model
{
    protected $table      = 'usuarios';
    protected $primaryKey = 'id';
    protected $allowedFields = ['nombredeusuario', 'email', 'password', 'rol', 'activo', 'fecha_registro'];
    protected $returnType = 'array';
    
    protected $useTimestamps = false;
    
    // Validaciones a nivel de modelo
    protected $validationRules = [
        'nombredeusuario' => 'required|min_length[3]|max_length[100]',
        'email'           => 'required|valid_email|max_length[100]|is_unique[usuarios.email]',
        'password'        => 'required|min_length[6]|max_length[255]',
        'rol'             => 'permit_empty|in_list[usuario,admin]',
        'activo'          => 'permit_empty|in_list[0,1]'
    ];
    
    protected $validationMessages = [
        'nombredeusuario' => [
            'required'   => 'El nombre de usuario es obligatorio.',
            'min_length' => 'El nombre debe tener al menos 3 caracteres.',
            'max_length' => 'El nombre no puede exceder 100 caracteres.'
        ],
        'email' => [
            'required'    => 'El email es obligatorio.',
            'valid_email' => 'Debe ingresar un email válido.',
            'max_length'  => 'El email es demasiado largo.',
            'is_unique'   => 'Este email ya está registrado.'
        ],
        'password' => [
            'required'   => 'La contraseña es obligatoria.',
            'min_length' => 'La contraseña debe tener al menos 6 caracteres.',
            'max_length' => 'La contraseña es demasiado larga.'
        ],
        'rol' => [
            'in_list' => 'El rol debe ser usuario o admin.'
        ],
        'activo' => [
            'in_list' => 'El estado activo debe ser 0 o 1.'
        ]
    ];
    
    /**
     * Buscar usuario por email para login
     */
    public function buscarPorEmail($email)
    {
        return $this->where('email', strtolower(trim($email)))->first();
    }
    
    /**
     * Verificar si un username ya existe
     */
    public function usuarioExiste($username)
    {
        return $this->where('nombredeusuario', $username)->first() !== null;
    }
    
    /**
     * Obtener solo usuarios activos
     */
    public function obtenerActivos()
    {
        return $this->where('activo', 1)->findAll();
    }
    
    /**
     * Activar/Desactivar usuario
     */
    public function toggleActivo($id)
    {
        $usuario = $this->find($id);
        if (!$usuario) return false;
        
        $nuevoEstado = $usuario['activo'] ? 0 : 1;
        return $this->update($id, ['activo' => $nuevoEstado]);
    }
}