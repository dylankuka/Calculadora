<?php
namespace App\Controllers;

use App\Models\UsuarioModel;
use App\Models\DonacionModel;
use App\Models\CotizacionDolarModel;
use App\Models\HistorialModel;
use App\Models\CategoriaProductoModel;

class Admin extends BaseController
{
    private $usuarioModel;
    private $donacionModel;
    private $cotizacionModel;
    private $historialModel;
    private $categoriaModel;

    public function __construct()
    {
        $this->usuarioModel = new UsuarioModel();
        $this->donacionModel = new DonacionModel();
        $this->cotizacionModel = new CotizacionDolarModel();
        $this->historialModel = new HistorialModel();
        $this->categoriaModel = new CategoriaProductoModel();
    }

    /**
     * Validar que el usuario sea administrador
     */
    private function validarAdmin()
    {
        if (!session()->get('logueado')) {
            return redirect()->to('/usuario/login')
                ->with('error', '❌ Debes iniciar sesión.');
        }

        if (session()->get('usuario_rol') !== 'admin') {
            return redirect()->to('/historial')
                ->with('error', '❌ No tienes permisos de administrador.');
        }

        return null;
    }

    /**
     * Dashboard principal de administración
     */
    public function index()
    {
        $redirect = $this->validarAdmin();
        if ($redirect) return $redirect;

        // Obtener estadísticas generales
        $estadisticas = [
            'total_usuarios' => $this->usuarioModel->countAll(),
            'usuarios_activos' => $this->usuarioModel->where('activo', 1)->countAllResults(),
            'total_calculos' => $this->historialModel->countAll(),
            'total_donaciones' => $this->donacionModel->countAll(),
            'donaciones_aprobadas' => $this->donacionModel->where('estado', 'aprobado')->countAllResults(),
            'total_recaudado' => $this->obtenerTotalRecaudado(),
            'total_categorias' => $this->categoriaModel->countAll(),
            'ultima_cotizacion' => $this->obtenerUltimaCotizacion()
        ];

        // Actividad reciente
        $actividadReciente = [
            'ultimos_usuarios' => $this->usuarioModel->orderBy('fecha_registro', 'DESC')->limit(5)->findAll(),
            'ultimas_donaciones' => $this->obtenerUltimasDonaciones(5),
            'ultimos_calculos' => $this->obtenerUltimosCalculos(5)
        ];

        return view('admin/dashboard', [
            'estadisticas' => $estadisticas,
            'actividad' => $actividadReciente
        ]);
    }

    /**
     * Gestión de usuarios
     */
    public function usuarios()
    {
        $redirect = $this->validarAdmin();
        if ($redirect) return $redirect;

        $busqueda = $this->request->getGet('buscar');
        $rol = $this->request->getGet('rol');

        $builder = $this->usuarioModel;

        if ($busqueda) {
            $builder = $builder->like('nombredeusuario', $busqueda)
                              ->orLike('email', $busqueda);
        }

        if ($rol && in_array($rol, ['admin', 'usuario'])) {
            $builder = $builder->where('rol', $rol);
        }

        $usuarios = $builder->orderBy('fecha_registro', 'DESC')->findAll();

        // Obtener estadísticas por usuario
        foreach ($usuarios as &$usuario) {
            $usuario['total_calculos'] = $this->historialModel->where('usuario_id', $usuario['id'])->countAllResults();
            $usuario['total_donaciones'] = $this->donacionModel->where('id_usuario', $usuario['id'])->countAllResults();
        }

        return view('admin/usuarios', [
            'usuarios' => $usuarios,
            'busqueda' => $busqueda,
            'rol_filtro' => $rol
        ]);
    }

    /**
     * Cambiar rol de usuario
     */
    public function cambiarRol($userId)
    {
        $redirect = $this->validarAdmin();
        if ($redirect) return $redirect;

        $nuevoRol = $this->request->getPost('rol');

        if (!in_array($nuevoRol, ['admin', 'usuario'])) {
            return redirect()->back()->with('error', '❌ Rol inválido.');
        }

        // No permitir que el admin se quite a sí mismo el rol
        if ($userId == session()->get('usuario_id') && $nuevoRol === 'usuario') {
            return redirect()->back()->with('error', '❌ No puedes quitarte tu propio rol de administrador.');
        }

        $this->usuarioModel->update($userId, ['rol' => $nuevoRol]);

        return redirect()->to('/admin/usuarios')
            ->with('success', "✅ Rol actualizado a: $nuevoRol");
    }

    /**
     * Activar/Desactivar usuario
     */
    public function toggleUsuario($userId)
    {
        $redirect = $this->validarAdmin();
        if ($redirect) return $redirect;

        $usuario = $this->usuarioModel->find($userId);

        if (!$usuario) {
            return redirect()->back()->with('error', '❌ Usuario no encontrado.');
        }

        // No permitir desactivar al propio admin
        if ($userId == session()->get('usuario_id')) {
            return redirect()->back()->with('error', '❌ No puedes desactivar tu propia cuenta.');
        }

        $nuevoEstado = $usuario['activo'] ? 0 : 1;
        $this->usuarioModel->update($userId, ['activo' => $nuevoEstado]);

        $mensaje = $nuevoEstado ? 'activado' : 'desactivado';
        return redirect()->to('/admin/usuarios')
            ->with('success', "✅ Usuario $mensaje correctamente.");
    }

    /**
     * Gestión de donaciones
     */
    public function donaciones()
    {
        $redirect = $this->validarAdmin();
        if ($redirect) return $redirect;

        $estado = $this->request->getGet('estado');
        $busqueda = $this->request->getGet('buscar');

        $builder = $this->donacionModel
            ->select('donaciones.*, usuarios.nombredeusuario, usuarios.email')
            ->join('usuarios', 'usuarios.id = donaciones.id_usuario');

        if ($estado && in_array($estado, ['pendiente', 'aprobado', 'rechazado', 'cancelado'])) {
            $builder = $builder->where('donaciones.estado', $estado);
        }

        if ($busqueda) {
            $builder = $builder->like('usuarios.nombredeusuario', $busqueda)
                              ->orLike('usuarios.email', $busqueda);
        }

        $donaciones = $builder->orderBy('donaciones.fecha_donacion', 'DESC')->findAll();

        // Estadísticas de donaciones
        $estadisticas = [
            'total' => $this->donacionModel->countAll(),
            'aprobadas' => $this->donacionModel->where('estado', 'aprobado')->countAllResults(),
            'pendientes' => $this->donacionModel->where('estado', 'pendiente')->countAllResults(),
            'rechazadas' => $this->donacionModel->where('estado', 'rechazado')->countAllResults(),
            'total_recaudado' => $this->obtenerTotalRecaudado()
        ];

        return view('admin/donaciones', [
            'donaciones' => $donaciones,
            'estadisticas' => $estadisticas,
            'estado_filtro' => $estado,
            'busqueda' => $busqueda
        ]);
    }

    /**
     * Gestión de cotizaciones
     */
    public function cotizaciones()
    {
        $redirect = $this->validarAdmin();
        if ($redirect) return $redirect;

        $tipo = $this->request->getGet('tipo');

        $builder = $this->cotizacionModel;

        if ($tipo && in_array($tipo, ['tarjeta', 'MEP'])) {
            $builder = $builder->where('tipo', $tipo);
        }

        $cotizaciones = $builder->orderBy('fecha', 'DESC')->limit(50)->findAll();

        // Últimas cotizaciones
        $ultimaTarjeta = $this->cotizacionModel->obtenerUltimaCotizacion('tarjeta');
        $ultimoMEP = $this->cotizacionModel->obtenerUltimaCotizacion('MEP');

        return view('admin/cotizaciones', [
            'cotizaciones' => $cotizaciones,
            'ultima_tarjeta' => $ultimaTarjeta,
            'ultimo_mep' => $ultimoMEP,
            'tipo_filtro' => $tipo
        ]);
    }

    /**
     * Forzar actualización de cotizaciones
     */
    public function actualizarCotizaciones()
    {
        $redirect = $this->validarAdmin();
        if ($redirect) return $redirect;

        try {
            $dolarService = new \App\Services\DolarService();
            $cotizaciones = $dolarService->obtenerCotizaciones();

            return redirect()->to('/admin/cotizaciones')
                ->with('success', "✅ Cotizaciones actualizadas: Tarjeta \${$cotizaciones['tarjeta']}, MEP \${$cotizaciones['MEP']}");
        } catch (\Exception $e) {
            return redirect()->to('/admin/cotizaciones')
                ->with('error', '❌ Error actualizando cotizaciones: ' . $e->getMessage());
        }
    }

    /**
     * Gestión de categorías
     */
    public function categorias()
    {
        $redirect = $this->validarAdmin();
        if ($redirect) return $redirect;

        $categorias = $this->categoriaModel->obtenerTodasOrdenadas();

        // Obtener uso de cada categoría
        foreach ($categorias as &$categoria) {
            $categoria['total_usos'] = $this->historialModel
                ->where('categoria_id', $categoria['id'])
                ->countAllResults();
        }

        return view('admin/categorias', [
            'categorias' => $categorias
        ]);
    }

    /**
     * Actualizar arancel de categoría
     */
    public function actualizarCategoria($id)
    {
        $redirect = $this->validarAdmin();
        if ($redirect) return $redirect;

        $nuevoArancel = $this->request->getPost('arancel_porcentaje');

        if (!is_numeric($nuevoArancel) || $nuevoArancel < 0 || $nuevoArancel > 100) {
            return redirect()->back()->with('error', '❌ Arancel inválido (debe ser entre 0 y 100).');
        }

        $this->categoriaModel->update($id, ['arancel_porcentaje' => $nuevoArancel]);

        return redirect()->to('/admin/categorias')
            ->with('success', "✅ Arancel actualizado correctamente.");
    }

    /**
     * Estadísticas generales
     */
    public function estadisticas()
    {
        $redirect = $this->validarAdmin();
        if ($redirect) return $redirect;

        // Estadísticas por mes (últimos 12 meses)
        $calculosPorMes = $this->obtenerCalculosPorMes();
        $donacionesPorMes = $this->obtenerDonacionesPorMes();

        // Top usuarios
        $topUsuarios = $this->obtenerTopUsuarios(10);

        // Categorías más usadas
        $categoriasPopulares = $this->obtenerCategoriasPopulares();

        return view('admin/estadisticas', [
            'calculos_mes' => $calculosPorMes,
            'donaciones_mes' => $donacionesPorMes,
            'top_usuarios' => $topUsuarios,
            'categorias_populares' => $categoriasPopulares
        ]);
    }

    // ========================================
    // MÉTODOS AUXILIARES
    // ========================================

    private function obtenerTotalRecaudado()
    {
        $result = $this->donacionModel
            ->selectSum('monto_ars', 'total')
            ->where('estado', 'aprobado')
            ->first();

        return $result['total'] ?? 0;
    }

    private function obtenerUltimaCotizacion()
    {
        $tarjeta = $this->cotizacionModel->obtenerUltimaCotizacion('tarjeta');
        $mep = $this->cotizacionModel->obtenerUltimaCotizacion('MEP');

        return [
            'tarjeta' => $tarjeta['valor_ars'] ?? 0,
            'mep' => $mep['valor_ars'] ?? 0,
            'fecha' => $tarjeta['fecha'] ?? date('Y-m-d H:i:s')
        ];
    }

    private function obtenerUltimasDonaciones($limite = 5)
    {
        return $this->donacionModel
            ->select('donaciones.*, usuarios.nombredeusuario')
            ->join('usuarios', 'usuarios.id = donaciones.id_usuario')
            ->orderBy('donaciones.fecha_donacion', 'DESC')
            ->limit($limite)
            ->findAll();
    }

    private function obtenerUltimosCalculos($limite = 5)
    {
        return $this->historialModel
            ->select('historial_calculos.*, usuarios.nombredeusuario')
            ->join('usuarios', 'usuarios.id = historial_calculos.usuario_id')
            ->orderBy('historial_calculos.fecha_calculo', 'DESC')
            ->limit($limite)
            ->findAll();
    }

    private function obtenerCalculosPorMes()
    {
        $db = \Config\Database::connect();
        return $db->query("
            SELECT 
                DATE_FORMAT(fecha_calculo, '%Y-%m') as mes,
                COUNT(*) as cantidad
            FROM historial_calculos
            WHERE fecha_calculo >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
            GROUP BY mes
            ORDER BY mes ASC
        ")->getResultArray();
    }

    private function obtenerDonacionesPorMes()
    {
        $db = \Config\Database::connect();
        return $db->query("
            SELECT 
                DATE_FORMAT(fecha_donacion, '%Y-%m') as mes,
                COUNT(*) as cantidad,
                SUM(monto_ars) as total
            FROM donaciones
            WHERE fecha_donacion >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
            AND estado = 'aprobado'
            GROUP BY mes
            ORDER BY mes ASC
        ")->getResultArray();
    }

    private function obtenerTopUsuarios($limite = 10)
    {
        $db = \Config\Database::connect();
        return $db->query("
            SELECT 
                u.id,
                u.nombredeusuario,
                u.email,
                COUNT(h.id) as total_calculos,
                COALESCE(SUM(d.monto_ars), 0) as total_donado
            FROM usuarios u
            LEFT JOIN historial_calculos h ON u.id = h.usuario_id
            LEFT JOIN donaciones d ON u.id = d.id_usuario AND d.estado = 'aprobado'
            GROUP BY u.id
            ORDER BY total_calculos DESC
            LIMIT $limite
        ")->getResultArray();
    }

    private function obtenerCategoriasPopulares()
    {
        $db = \Config\Database::connect();
        return $db->query("
            SELECT 
                c.nombre,
                c.arancel_porcentaje,
                COUNT(h.id) as cantidad_usos
            FROM categorias_productos c
            LEFT JOIN historial_calculos h ON c.id = h.categoria_id
            GROUP BY c.id
            ORDER BY cantidad_usos DESC
            LIMIT 10
        ")->getResultArray();
    }
}