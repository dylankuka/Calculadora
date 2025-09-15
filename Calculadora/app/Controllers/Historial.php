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

    // ✅ CREATE - MOSTRAR FORMULARIO
    // ✅ CAMBIAR ESTE MÉTODO EXISTENTE:
public function crear()
{
    $redirect = $this->validarSesion();
    if ($redirect) return $redirect;

    // ✅ AGREGAR ESTAS LÍNEAS:
    $dolarService = new \App\Services\DolarService();
    
    if ($dolarService->necesitaActualizacion('tarjeta')) {
        $dolarService->obtenerCotizaciones();
    }
    
    $cotizaciones = [
        'tarjeta' => $dolarService->obtenerCotizacion('tarjeta'),
        'MEP' => $dolarService->obtenerCotizacion('MEP')
    ];

    return view('historial/crear', [
        'cotizaciones' => $cotizaciones  // ← AGREGAR ESTA LÍNEA
    ]);
}

    // ✅ CREATE - GUARDAR NUEVO REGISTRO
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
    // ✅ NUEVO MÉTODO CALCULAR CON LOCALIDADES E IMPUESTOS
public function calcular()
{
    $redirect = $this->validarSesion();
    if ($redirect) return $redirect;

    // ✅ VALIDACIONES
    $rules = [
        'amazon_url' => 'required|valid_url',
        'nombre_producto' => 'required|min_length[3]|max_length[200]',
        'precio_usd' => 'required|decimal|greater_than[0]',
        'provincia' => 'required|max_length[10]',
'tipo_cambio' => 'required|in_list[tarjeta,MEP]',
        'total_ars' => 'required|decimal|greater_than[0]'
    ];

    if (!$this->validate($rules)) {
        return redirect()->back()
            ->withInput()
            ->with('error', 'Por favor completa todos los campos correctamente.');
    }

    // ✅ OBTENER DATOS
    $amazonUrl = $this->request->getPost('amazon_url');
    $nombreProducto = $this->request->getPost('nombre_producto');
    $precioUSD = (float)$this->request->getPost('precio_usd');
    $provincia = $this->request->getPost('provincia');
    $tipoCambio = $this->request->getPost('tipo_cambio');
    $totalARS = (float)$this->request->getPost('total_ars');

    // ✅ OBTENER COTIZACIONES ACTUALES
    $dolarService = new \App\Services\DolarService();
    
    if ($dolarService->necesitaActualizacion($tipoCambio)) {
        $dolarService->obtenerCotizaciones();
    }
    
    $cotizacion = $dolarService->obtenerCotizacion($tipoCambio);

    // ✅ CALCULAR IMPUESTOS POR LOCALIDAD
    $impuestos = $this->obtenerImpuestosPorLocalidad($provincia);
    
    // ✅ DESGLOSE DETALLADO
    $envioUSD = 25;
    $baseARS = $precioUSD * $cotizacion;
    $ivaARS = $baseARS * ($impuestos['iva'] / 100);
    $derechosARS = $baseARS * ($impuestos['derechos'] / 100);
    $adicionalesARS = $baseARS * ($impuestos['adicionales'] / 100);
    $envioARS = $envioUSD * $cotizacion;
    
    $totalCalculadoARS = $baseARS + $ivaARS + $derechosARS + $adicionalesARS + $envioARS;

    // ✅ GUARDAR EN BASE DE DATOS
    $datos = [
        'usuario_id' => session()->get('usuario_id'),
        'amazon_url' => trim($amazonUrl),
        'nombre_producto' => trim($nombreProducto),
        'precio_usd' => $precioUSD,
        'total_ars' => round($totalCalculadoARS, 2),
        'desglose_json' => json_encode([
            'precio_usd' => $precioUSD,
            'envio_usd' => $envioUSD,
            'cotizacion' => $cotizacion,
            'tipo_cambio' => $tipoCambio,
            'provincia' => $provincia,
            'impuestos' => $impuestos,
            'desglose_ars' => [
                'base' => round($baseARS, 2),
                'iva' => round($ivaARS, 2),
                'derechos' => round($derechosARS, 2),
                'adicionales' => round($adicionalesARS, 2),
                'envio' => round($envioARS, 2),
                'total' => round($totalCalculadoARS, 2)
            ],
            'fecha_cotizacion' => date('Y-m-d H:i:s')
        ]),
        'fecha_calculo' => date('Y-m-d H:i:s')
    ];

    try {
        $this->historialModel->insert($datos);
        
        return redirect()->to('/historial')
            ->with('success', "✅ Cálculo guardado. Total: $" . number_format($totalCalculadoARS, 2) . " ARS ($provincia - $tipoCambio: $$cotizacion)");
            
    } catch (\Exception $e) {
        log_message('error', 'Error guardando cálculo: ' . $e->getMessage());
        return redirect()->back()
            ->withInput()
            ->with('error', '❌ Error al guardar el cálculo.');
    }
}

// ✅ MÉTODO AUXILIAR PARA IMPUESTOS POR LOCALIDAD
private function obtenerImpuestosPorLocalidad($provincia)
{
    $impuestosPorProvincia = [
        'CABA' => ['iva' => 21, 'derechos' => 50, 'adicionales' => 0],
        'BA' => ['iva' => 21, 'derechos' => 50, 'adicionales' => 2.5], // ARBA
        'CB' => ['iva' => 21, 'derechos' => 50, 'adicionales' => 1.5], // Rentas Córdoba
        'SF' => ['iva' => 21, 'derechos' => 50, 'adicionales' => 2.0], // ATER
        'MZ' => ['iva' => 21, 'derechos' => 50, 'adicionales' => 1.8],
        'TU' => ['iva' => 21, 'derechos' => 50, 'adicionales' => 1.0],
        'ER' => ['iva' => 21, 'derechos' => 50, 'adicionales' => 1.5],
        'SA' => ['iva' => 21, 'derechos' => 50, 'adicionales' => 1.2],
        // Más provincias...
    ];

    return $impuestosPorProvincia[$provincia] ?? ['iva' => 21, 'derechos' => 50, 'adicionales' => 0];
}
}