<?php
namespace App\Controllers;

use App\Models\HistorialModel;

class Historial extends BaseController
{
    private $historialModel;

    public function __construct()
    {
        $this->historialModel = new HistorialModel();
    }

    // ✅ VALIDACIÓN DE SESIÓN MEJORADA
    private function validarSesion()
    {
        if (!session()->get('logueado')) {
            return redirect()->to('/usuario/login')
                ->with('error', '❌ Debes iniciar sesión para acceder al historial.');
        }
        return null;
    }

    // ✅ INDEX PRINCIPAL - REDIRIGE SI NO ESTÁ AUTENTICADO
    public function index()
    {
        // Si el usuario no está logueado, redirigir al login
        if (!session()->get('logueado')) {
            return redirect()->to('/usuario/login')
                ->with('info', '👋 Bienvenido a TaxImporter. Inicia sesión para acceder a tu historial.');
        }

        // Si está logueado, mostrar el historial normal
        $usuarioId = session()->get('usuario_id');
        $busqueda = $this->request->getGet('buscar');

        if ($busqueda) {
            $historial = $this->historialModel->buscarPorProducto($usuarioId, $busqueda);
            $mensaje = "Resultados para: " . esc($busqueda);
        } else {
            $historial = $this->historialModel->obtenerPorUsuario($usuarioId);
            $mensaje = null;
        }

        $resumen = $this->historialModel->obtenerResumenUsuario($usuarioId);

        return view('historial/index', [
            'historial' => $historial,
            'resumen' => $resumen,
            'busqueda' => $busqueda,
            'mensaje' => $mensaje
        ]);
    }

    // ✅ CREATE - MOSTRAR FORMULARIO
    public function crear()
    {
        $redirect = $this->validarSesion();
        if ($redirect) return $redirect;

        return view('historial/crear');
    }

    // ✅ CREATE - GUARDAR NUEVO REGISTRO
    public function guardar()
    {
        $redirect = $this->validarSesion();
        if ($redirect) return $redirect;

        // ✅ VALIDACIONES BACKEND ESTRICTAS
        $rules = [
            'amazon_url' => [
                'rules' => 'required|valid_url|max_length[500]',
                'errors' => [
                    'required' => 'La URL de Amazon es obligatoria.',
                    'valid_url' => 'Debe ser una URL válida.',
                    'max_length' => 'La URL no puede exceder 500 caracteres.'
                ]
            ],
            'nombre_producto' => [
                'rules' => 'required|min_length[3]|max_length[200]',
                'errors' => [
                    'required' => 'El nombre del producto es obligatorio.',
                    'min_length' => 'El nombre debe tener al menos 3 caracteres.',
                    'max_length' => 'El nombre no puede exceder 200 caracteres.'
                ]
            ],
            'precio_usd' => [
                'rules' => 'required|decimal|greater_than[0]|less_than[99999]',
                'errors' => [
                    'required' => 'El precio en USD es obligatorio.',
                    'decimal' => 'El precio debe ser un número válido.',
                    'greater_than' => 'El precio debe ser mayor a $0.',
                    'less_than' => 'El precio no puede exceder $99,999.'
                ]
            ],
            'total_ars' => [
                'rules' => 'required|decimal|greater_than[0]',
                'errors' => [
                    'required' => 'El total en ARS es obligatorio.',
                    'decimal' => 'El total debe ser un número válido.',
                    'greater_than' => 'El total debe ser mayor a $0.'
                ]
            ]
        ];

        if (!$this->validate($rules)) {
            return view('historial/crear', [
                'validation' => $this->validator,
                'old_input' => $this->request->getPost()
            ]);
        }

        // ✅ VALIDACIÓN ADICIONAL: URL DE AMAZON
        $amazonUrl = $this->request->getPost('amazon_url');
        if (!$this->esUrlAmazon($amazonUrl)) {
            return view('historial/crear', [
                'error' => '❌ La URL debe ser de Amazon (amazon.com, amazon.es, etc.)',
                'old_input' => $this->request->getPost()
            ]);
        }

        $datos = [
            'usuario_id' => session()->get('usuario_id'),
            'amazon_url' => trim($amazonUrl),
            'nombre_producto' => trim($this->request->getPost('nombre_producto')),
            'precio_usd' => (float)$this->request->getPost('precio_usd'),
            'total_ars' => (float)$this->request->getPost('total_ars'),
            'desglose_json' => json_encode([
                'iva' => $this->request->getPost('precio_usd') * 0.21,
                'derechos' => max(0, ($this->request->getPost('precio_usd') - 50) * 0.5),
                'envio' => 25
            ]),
            'fecha_calculo' => date('Y-m-d H:i:s')
        ];

        try {
            $this->historialModel->insert($datos);
            return redirect()->to('/historial')
                ->with('success', '✅ Cálculo guardado exitosamente.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', '❌ Error al guardar. Intenta nuevamente.');
        }
    }

    // ✅ READ - MOSTRAR UN REGISTRO
    public function ver($id)
    {
        $redirect = $this->validarSesion();
        if ($redirect) return $redirect;

        $usuarioId = session()->get('usuario_id');
        $calculo = $this->historialModel->where('id', $id)
                                       ->where('usuario_id', $usuarioId)
                                       ->first();

        if (!$calculo) {
            return redirect()->to('/historial')
                ->with('error', '❌ Cálculo no encontrado.');
        }

        return view('historial/ver', [
            'calculo' => $calculo
        ]);
    }

    // ✅ UPDATE - MOSTRAR FORMULARIO DE EDICIÓN
    public function editar($id)
    {
        $redirect = $this->validarSesion();
        if ($redirect) return $redirect;

        $usuarioId = session()->get('usuario_id');
        $calculo = $this->historialModel->where('id', $id)
                                       ->where('usuario_id', $usuarioId)
                                       ->first();

        if (!$calculo) {
            return redirect()->to('/historial')
                ->with('error', '❌ Cálculo no encontrado.');
        }

        return view('historial/editar', [
            'calculo' => $calculo
        ]);
    }

    // ✅ UPDATE - ACTUALIZAR REGISTRO
    public function actualizar($id)
    {
        $redirect = $this->validarSesion();
        if ($redirect) return $redirect;

        $usuarioId = session()->get('usuario_id');
        $calculo = $this->historialModel->where('id', $id)
                                       ->where('usuario_id', $usuarioId)
                                       ->first();

        if (!$calculo) {
            return redirect()->to('/historial')
                ->with('error', '❌ Cálculo no encontrado.');
        }

        // Mismas validaciones que en guardar()
        $rules = [
            'nombre_producto' => [
                'rules' => 'required|min_length[3]|max_length[200]',
                'errors' => [
                    'required' => 'El nombre del producto es obligatorio.',
                    'min_length' => 'El nombre debe tener al menos 3 caracteres.',
                    'max_length' => 'El nombre no puede exceder 200 caracteres.'
                ]
            ],
            'precio_usd' => [
                'rules' => 'required|decimal|greater_than[0]|less_than[99999]',
                'errors' => [
                    'required' => 'El precio en USD es obligatorio.',
                    'decimal' => 'El precio debe ser un número válido.',
                    'greater_than' => 'El precio debe ser mayor a $0.',
                    'less_than' => 'El precio no puede exceder $99,999.'
                ]
            ]
        ];

        if (!$this->validate($rules)) {
            return view('historial/editar', [
                'calculo' => $calculo,
                'validation' => $this->validator,
                'old_input' => $this->request->getPost()
            ]);
        }

        $datosActualizar = [
            'nombre_producto' => trim($this->request->getPost('nombre_producto')),
            'precio_usd' => (float)$this->request->getPost('precio_usd'),
            'total_ars' => (float)$this->request->getPost('precio_usd') * 1683.5 * 1.71 // Cálculo básico
        ];

        try {
            $this->historialModel->update($id, $datosActualizar);
            return redirect()->to('/historial')
                ->with('success', '✅ Cálculo actualizado exitosamente.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', '❌ Error al actualizar. Intenta nuevamente.');
        }
    }

    // ✅ DELETE - ELIMINAR REGISTRO
    public function eliminar($id)
    {
        $redirect = $this->validarSesion();
        if ($redirect) return $redirect;

        $usuarioId = session()->get('usuario_id');
        $calculo = $this->historialModel->where('id', $id)
                                       ->where('usuario_id', $usuarioId)
                                       ->first();

        if (!$calculo) {
            return redirect()->to('/historial')
                ->with('error', '❌ Cálculo no encontrado.');
        }

        try {
            $this->historialModel->delete($id);
            return redirect()->to('/historial')
                ->with('success', '✅ Cálculo eliminado exitosamente.');
        } catch (\Exception $e) {
            return redirect()->to('/historial')
                ->with('error', '❌ Error al eliminar. Intenta nuevamente.');
        }
    }

    // ✅ MÉTODO AUXILIAR PARA VALIDAR URLs DE AMAZON
    private function esUrlAmazon($url)
    {
        $dominiosValidos = [
            'amazon.com', 
            'amazon.es', 
            'amazon.co.uk', 
            'amazon.com.ar',
            'amazon.com.mx',
            'amazon.de',
            'amazon.fr'
        ];
        
        $host = parse_url($url, PHP_URL_HOST);
        
        foreach ($dominiosValidos as $dominio) {
            if (strpos($host, $dominio) !== false) {
                return true;
            }
        }
        return false;
    }
}