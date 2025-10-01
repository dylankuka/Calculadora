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
        // ✅ PERMITIR ACCESO SIN SESIÓN
        if (!session()->get('logueado')) {
            // Usuario no logueado - mostrar página sin datos
            return view('historial/index', [
                'historial' => [],
                'resumen' => ['total_calculado' => 0, 'total_consultas' => 0],
                'busqueda' => null,
                'mensaje' => null,
                'usuario_logueado' => false
            ]);
        }

        // Usuario logueado - mostrar historial normal
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
            'mensaje' => $mensaje,
            'usuario_logueado' => true
        ]);
    }

    // ✅ CREATE - MOSTRAR FORMULARIO (CORREGIDO)
    public function crear()
    {
        $redirect = $this->validarSesion();
        if ($redirect) return $redirect;

        // ✅ CARGAR EL MODELO DE CATEGORÍAS
        $categoriaModel = new \App\Models\CategoriaProductoModel();
        
        // ✅ OBTENER COTIZACIONES
        $dolarService = new \App\Services\DolarService();
        
        if ($dolarService->necesitaActualizacion('tarjeta')) {
            $dolarService->obtenerCotizaciones();
        }
        
        $cotizaciones = [
            'tarjeta' => $dolarService->obtenerCotizacion('tarjeta'),
            'MEP' => $dolarService->obtenerCotizacion('MEP')
        ];

        // ✅ PASAR CATEGORÍAS Y COTIZACIONES A LA VISTA
        return view('historial/crear', [
            'categorias' => $categoriaModel->obtenerTodasOrdenadas(),
            'cotizaciones' => $cotizaciones,
            'old_input' => []
        ]);
    }

    // ✅ CREATE - GUARDAR NUEVO REGISTRO CON COTIZACIONES DINÁMICAS
    public function guardar()
    {
        $redirect = $this->validarSesion();
        if ($redirect) return $redirect;

        // ✅ VALIDACIONES BACKEND ESTRICTAS (sin cambios)
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

        // ✅ OBTENER COTIZACIONES DINÁMICAS
        $dolarService = new \App\Services\DolarService();
        
        // Verificar si necesita actualización
        if ($dolarService->necesitaActualizacion('tarjeta')) {
            $dolarService->obtenerCotizaciones();
        }
        
        $dolarTarjeta = $dolarService->obtenerCotizacion('tarjeta');
        $dolarMEP = $dolarService->obtenerCotizacion('MEP');
        
        // ✅ CÁLCULOS CON VALORES DINÁMICOS
        $precioUSD = (float)$this->request->getPost('precio_usd');
        $envioUSD = 25; // Costo fijo de envío
        
        // Cálculo de impuestos
        $iva = $precioUSD * 0.21;
        $derechosImportacion = max(0, ($precioUSD - 50) * 0.5);
        
        // Total en ARS usando dólar tarjeta
        $totalProductoARS = $precioUSD * $dolarTarjeta;
        $totalImpuestosARS = ($iva + $derechosImportacion) * $dolarTarjeta;
        $totalEnvioARS = $envioUSD * $dolarTarjeta;
        $totalFinalARS = $totalProductoARS + $totalImpuestosARS + $totalEnvioARS;

        $datos = [
            'usuario_id' => session()->get('usuario_id'),
            'amazon_url' => trim($amazonUrl),
            'nombre_producto' => trim($this->request->getPost('nombre_producto')),
            'precio_usd' => $precioUSD,
            'total_ars' => round($totalFinalARS, 2),
            'desglose_json' => json_encode([
                'precio_usd' => $precioUSD,
                'envio_usd' => $envioUSD,
                'iva_usd' => $iva,
                'derechos_usd' => $derechosImportacion,
                'dolar_tarjeta' => $dolarTarjeta,
                'dolar_mep' => $dolarMEP,
                'total_producto_ars' => round($totalProductoARS, 2),
                'total_impuestos_ars' => round($totalImpuestosARS, 2),
                'total_envio_ars' => round($totalEnvioARS, 2),
                'fecha_cotizacion' => date('Y-m-d H:i:s')
            ]),
            'fecha_calculo' => date('Y-m-d H:i:s')
        ];

        try {
            $this->historialModel->insert($datos);
            return redirect()->to('/historial')
                ->with('success', "✅ Cálculo guardado exitosamente. Dólar tarjeta: $$dolarTarjeta ARS");
        } catch (\Exception $e) {
            log_message('error', 'Error guardando cálculo: ' . $e->getMessage());
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

    // ✅ MÉTODO CALCULAR COMPLETAMENTE REESCRITO CON NUEVAS FUNCIONALIDADES
    public function calcular()
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
                'rules' => 'required|decimal|greater_than[0]|less_than[50000]',
                'errors' => [
                    'required' => 'El precio en USD es obligatorio.',
                    'decimal' => 'El precio debe ser un número válido.',
                    'greater_than' => 'El precio debe ser mayor a $0.',
                    'less_than' => 'El precio no puede exceder $50,000.'
                ]
            ],
            'envio_usd' => [
                'rules' => 'required|decimal|greater_than_equal_to[0]|less_than[1000]',
                'errors' => [
                    'required' => 'El costo de envío es obligatorio.',
                    'decimal' => 'El envío debe ser un número válido.',
                    'greater_than_equal_to' => 'El envío no puede ser negativo.',
                    'less_than' => 'El envío no puede exceder $1,000.'
                ]
            ],
            'categoria_id' => [
                'rules' => 'required|integer|greater_than[0]',
                'errors' => [
                    'required' => 'Debes seleccionar una categoría.',
                    'integer' => 'Categoría inválida.',
                    'greater_than' => 'Categoría inválida.'
                ]
            ],
            'metodo_pago' => [
                'rules' => 'required|in_list[tarjeta,MEP]',
                'errors' => [
                    'required' => 'Debes seleccionar un método de pago.',
                    'in_list' => 'Método de pago inválido.'
                ]
            ]
        ];

        // Obtener categorías para recargar el formulario si hay errores
        $categoriaModel = new \App\Models\CategoriaProductoModel();
        $categorias = $categoriaModel->obtenerTodasOrdenadas();
        
        $dolarService = new \App\Services\DolarService();
        $cotizaciones = [
            'tarjeta' => $dolarService->obtenerCotizacion('tarjeta'),
            'MEP' => $dolarService->obtenerCotizacion('MEP')
        ];

        if (!$this->validate($rules)) {
            return view('historial/crear', [
                'validation' => $this->validator,
                'old_input' => $this->request->getPost(),
                'categorias' => $categorias,
                'cotizaciones' => $cotizaciones
            ]);
        }

        // ✅ VALIDACIÓN ADICIONAL: URL DE AMAZON
        $amazonUrl = $this->request->getPost('amazon_url');
        if (!$this->esUrlAmazon($amazonUrl)) {
            return view('historial/crear', [
                'error' => '❌ La URL debe ser de Amazon (amazon.com, amazon.es, etc.)',
                'old_input' => $this->request->getPost(),
                'categorias' => $categorias,
                'cotizaciones' => $cotizaciones
            ]);
        }

        // ✅ OBTENER DATOS DEL FORMULARIO
        $nombreProducto = trim($this->request->getPost('nombre_producto'));
        $precioUSD = (float)$this->request->getPost('precio_usd');
        $envioUSD = (float)$this->request->getPost('envio_usd');
        $categoriaId = (int)$this->request->getPost('categoria_id');
        $metodoPago = $this->request->getPost('metodo_pago');

        try {
            // ✅ CALCULAR IMPUESTOS USANDO EL NUEVO SERVICIO
            $calculoService = new \App\Services\CalculoImpuestosService();
            $resultadoCalculo = $calculoService->calcularImpuestos($precioUSD, $envioUSD, $categoriaId, $metodoPago);
            
            // ✅ PREPARAR DATOS PARA GUARDAR EN BD
            $datos = [
                'usuario_id' => session()->get('usuario_id'),
                'amazon_url' => trim($amazonUrl),
                'nombre_producto' => $nombreProducto,
                'precio_usd' => $precioUSD,
                'total_ars' => $resultadoCalculo['totales']['total_ars'],
                'categoria_id' => $categoriaId,
                'metodo_pago' => $metodoPago,
                'valor_cif_usd' => $resultadoCalculo['datos_base']['valor_cif_usd'],
                'excedente_400_usd' => $resultadoCalculo['datos_base']['excedente_400_usd'],
                'desglose_json' => json_encode($resultadoCalculo, JSON_UNESCAPED_UNICODE),
                'fecha_calculo' => date('Y-m-d H:i:s')
            ];

            // ✅ GUARDAR EN BASE DE DATOS
            $this->historialModel->insert($datos);
            
            // ✅ PREPARAR MENSAJE DE ÉXITO CON RESUMEN
            $resumen = $calculoService->obtenerResumenCalculo($resultadoCalculo);
            $bajoFranquicia = $resumen['bajo_franquicia'] ? 'Bajo franquicia (≤$400)' : 'Sobre franquicia (>$400)';
            
            $mensaje = "✅ Cálculo guardado exitosamente<br>";
            $mensaje .= "💰 Total: $" . number_format($resumen['total_final_ars'], 2) . " ARS<br>";
            $mensaje .= "📦 Categoría: {$resumen['categoria']}<br>";
            $mensaje .= "💳 {$resumen['metodo_pago']}: $" . number_format($resumen['cotizacion'], 2) . " ARS<br>";
            $mensaje .= "📋 Estado: {$bajoFranquicia}";
            
            return redirect()->to('/historial')
                ->with('success', $mensaje);
                
        } catch (\Exception $e) {
            log_message('error', 'Error en calcular(): ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', '❌ Error al calcular. Intenta nuevamente.');
        }
    }
}