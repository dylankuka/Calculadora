<?php

namespace App\Controllers;

use App\Models\HistorialModel;
use App\Models\CategoriaProductoModel;
use App\Services\CalculoImpuestosService;
use App\Services\DolarService;

class Historial extends BaseController
{
    protected $historialModel;
    protected $categoriaModel;
    protected $calculoService;
    protected $dolarService;

    public function __construct()
    {
        $this->historialModel = new HistorialModel();
        $this->categoriaModel = new CategoriaProductoModel();
        $this->calculoService = new CalculoImpuestosService();
        $this->dolarService = new DolarService();
    }

    /**
     * Vista principal del historial
     */
    public function index()
    {
        if (!session()->get('logueado')) {
            $data = [
                'usuario_logueado' => false,
                'historial' => [],
                'resumen' => [],
                'busqueda' => null,
                'mensaje' => null
            ];
            return view('Historial/index', $data);
        }

        $usuarioId = session()->get('usuario_id');
        $busqueda = $this->request->getGet('buscar');

        if ($busqueda) {
            $historial = $this->historialModel->buscarPorProducto($usuarioId, $busqueda);
            $mensaje = "Resultados para: " . esc($busqueda);
        } else {
            $historial = $this->historialModel->obtenerPorUsuario($usuarioId, 20);
            $mensaje = null;
        }

        $resumen = $this->historialModel->obtenerResumenUsuario($usuarioId);

        $data = [
            'usuario_logueado' => true,
            'historial' => $historial,
            'resumen' => $resumen,
            'busqueda' => $busqueda,
            'mensaje' => $mensaje
        ];

        return view('Historial/index', $data);
    }

    /**
     * Formulario para crear nuevo cálculo (SIN API de Amazon)
     */
    public function crear()
    {
        if (!session()->get('logueado')) {
            return redirect()->to('/usuario/login')
                ->with('error', 'Debes iniciar sesión para usar la calculadora');
        }

        // Obtener cotizaciones actuales
        try {
            $cotizaciones = [
                'tarjeta' => $this->dolarService->obtenerCotizacion('tarjeta'),
                'MEP' => $this->dolarService->obtenerCotizacion('MEP')
            ];
        } catch (\Exception $e) {
            $cotizaciones = [
                'tarjeta' => 1943.50,
                'MEP' => 1485.70
            ];
        }

        $data = [
            'categorias' => $this->categoriaModel->obtenerTodasOrdenadas(),
            'cotizaciones' => $cotizaciones,
            'validation' => session()->getFlashdata('validation') ?? null,
            'error' => session()->getFlashdata('error') ?? null,
            'old_input' => session()->getFlashdata('old_input') ?? []
        ];

        return view('Historial/crear', $data);
    }

    /**
     * Simular cálculo en tiempo real (AJAX)
     */
    public function simularCalculo()
    {
        try {
            $json = $this->request->getJSON();

            // Validar datos recibidos
            if (!$json || !isset($json->precio_usd, $json->envio_usd, $json->categoria_id, $json->metodo_pago)) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Faltan datos requeridos'
                ]);
            }

            $precioUSD = floatval($json->precio_usd);
            $envioUSD = floatval($json->envio_usd);
            $categoriaId = intval($json->categoria_id);
            $metodoPago = $json->metodo_pago;

            // Validaciones básicas
            if ($precioUSD <= 0 || $precioUSD > 50000) {
                throw new \Exception('El precio debe estar entre $0.01 y $50,000 USD');
            }

            if ($envioUSD < 0 || $envioUSD > 1000) {
                throw new \Exception('El envío debe estar entre $0 y $1,000 USD');
            }

            if (!in_array($metodoPago, ['tarjeta', 'MEP'])) {
                throw new \Exception('Método de pago inválido');
            }

            // Realizar cálculo
            $calculo = $this->calculoService->calcularImpuestos(
                $precioUSD,
                $envioUSD,
                $categoriaId,
                $metodoPago
            );

            return $this->response->setJSON([
                'success' => true,
                'data' => $calculo,
                'message' => 'Cálculo realizado exitosamente'
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Error en simulación: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Guardar cálculo en la base de datos
     */
    public function calcular()
    {
        if (!session()->get('logueado')) {
            return redirect()->to('/usuario/login')
                ->with('error', 'Debes iniciar sesión');
        }

        // Validación de datos
        $rules = [
            'nombre_producto' => 'required|max_length[200]',
            'precio_usd' => 'required|decimal|greater_than[0]|less_than_equal_to[50000]',
            'envio_usd' => 'required|decimal|greater_than_equal_to[0]|less_than_equal_to[1000]',
            'categoria_id' => 'required|integer|is_not_unique[categorias_productos.id]',
            'metodo_pago' => 'required|in_list[tarjeta,MEP]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()
                ->withInput()
                ->with('validation', $this->validator);
        }

        try {
            $usuarioId = session()->get('usuario_id');
            $nombreProducto = $this->request->getPost('nombre_producto');
            $precioUSD = floatval($this->request->getPost('precio_usd'));
            $envioUSD = floatval($this->request->getPost('envio_usd'));
            $categoriaId = intval($this->request->getPost('categoria_id'));
            $metodoPago = $this->request->getPost('metodo_pago');

            // URL ficticia para mantener compatibilidad con la BD
            $amazonUrl = 'https://www.amazon.com/manual-entry-' . time();

            // Realizar cálculo completo
            $calculo = $this->calculoService->calcularImpuestos(
                $precioUSD,
                $envioUSD,
                $categoriaId,
                $metodoPago
            );

            // Guardar en base de datos
            $datos = [
                'usuario_id' => $usuarioId,
                'amazon_url' => $amazonUrl,
                'nombre_producto' => $nombreProducto,
                'precio_usd' => $precioUSD,
                'total_ars' => $calculo['totales']['total_ars'],
                'desglose_json' => json_encode($calculo),
                'categoria_id' => $categoriaId,
                'metodo_pago' => $metodoPago,
                'valor_cif_usd' => $calculo['datos_base']['valor_cif_usd'],
                'excedente_400_usd' => $calculo['datos_base']['excedente_400_usd']
            ];

            $idCalculo = $this->historialModel->insert($datos);

            if (!$idCalculo) {
                throw new \Exception('Error al guardar el cálculo en la base de datos');
            }

            return redirect()->to('/historial/ver/' . $idCalculo)
                ->with('success', '✅ Cálculo guardado exitosamente');

        } catch (\Exception $e) {
            log_message('error', 'Error guardando cálculo: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error al guardar: ' . $e->getMessage());
        }
    }

    /**
     * Ver detalles de un cálculo
     */
    public function ver($id)
    {
        if (!session()->get('logueado')) {
            return redirect()->to('/usuario/login');
        }

        $usuarioId = session()->get('usuario_id');
        $calculo = $this->historialModel
            ->where('usuario_id', $usuarioId)
            ->find($id);

        if (!$calculo) {
            return redirect()->to('/historial')
                ->with('error', 'Cálculo no encontrado');
        }

        $data = ['calculo' => $calculo];
        return view('Historial/ver', $data);
    }

    /**
     * Editar un cálculo existente
     */
    public function editar($id)
    {
        if (!session()->get('logueado')) {
            return redirect()->to('/usuario/login');
        }

        $usuarioId = session()->get('usuario_id');
        $calculo = $this->historialModel
            ->where('usuario_id', $usuarioId)
            ->find($id);

        if (!$calculo) {
            return redirect()->to('/historial')
                ->with('error', 'Cálculo no encontrado');
        }

        $data = [
            'calculo' => $calculo,
            'categorias' => $this->categoriaModel->obtenerTodasOrdenadas(),
            'validation' => session()->getFlashdata('validation') ?? null,
            'old_input' => session()->getFlashdata('old_input') ?? []
        ];

        return view('Historial/editar', $data);
    }

    /**
     * Actualizar un cálculo
     */
    public function actualizar($id)
    {
        if (!session()->get('logueado')) {
            return redirect()->to('/usuario/login');
        }

        $usuarioId = session()->get('usuario_id');
        $calculo = $this->historialModel
            ->where('usuario_id', $usuarioId)
            ->find($id);

        if (!$calculo) {
            return redirect()->to('/historial')
                ->with('error', 'Cálculo no encontrado');
        }

        $rules = [
            'nombre_producto' => 'required|max_length[200]',
            'precio_usd' => 'required|decimal|greater_than[0]|less_than_equal_to[50000]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()
                ->withInput()
                ->with('validation', $this->validator);
        }

        try {
            $nombreProducto = $this->request->getPost('nombre_producto');
            $precioUSD = floatval($this->request->getPost('precio_usd'));

            // Recalcular con los nuevos valores
            $desglose = json_decode($calculo['desglose_json'], true);
            $envioUSD = $desglose['datos_base']['envio_usd'] ?? 25;
            $categoriaId = $calculo['categoria_id'];
            $metodoPago = $calculo['metodo_pago'];

            $nuevoCalculo = $this->calculoService->calcularImpuestos(
                $precioUSD,
                $envioUSD,
                $categoriaId,
                $metodoPago
            );

            $datos = [
                'nombre_producto' => $nombreProducto,
                'precio_usd' => $precioUSD,
                'total_ars' => $nuevoCalculo['totales']['total_ars'],
                'desglose_json' => json_encode($nuevoCalculo),
                'valor_cif_usd' => $nuevoCalculo['datos_base']['valor_cif_usd'],
                'excedente_400_usd' => $nuevoCalculo['datos_base']['excedente_400_usd']
            ];

            $this->historialModel->update($id, $datos);

            return redirect()->to('/historial/ver/' . $id)
                ->with('success', '✅ Cálculo actualizado exitosamente');

        } catch (\Exception $e) {
            log_message('error', 'Error actualizando: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error al actualizar: ' . $e->getMessage());
        }
    }

    /**
     * Eliminar un cálculo
     */
    public function eliminar($id)
    {
        if (!session()->get('logueado')) {
            return redirect()->to('/usuario/login');
        }

        $usuarioId = session()->get('usuario_id');
        $calculo = $this->historialModel
            ->where('usuario_id', $usuarioId)
            ->find($id);

        if (!$calculo) {
            return redirect()->to('/historial')
                ->with('error', 'Cálculo no encontrado');
        }

        try {
            $this->historialModel->delete($id);
            
            return redirect()->to('/historial')
                ->with('success', '🗑️ Cálculo eliminado exitosamente');
                
        } catch (\Exception $e) {
            log_message('error', 'Error eliminando: ' . $e->getMessage());
            return redirect()->to('/historial')
                ->with('error', 'Error al eliminar el cálculo');
        }
    }

    /**
     * Obtener información de una categoría (AJAX)
     */
    public function obtenerCategoria($id)
    {
        try {
            $categoria = $this->categoriaModel->find($id);
            
            if (!$categoria) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Categoría no encontrada'
                ]);
            }

            return $this->response->setJSON([
                'success' => true,
                'data' => $categoria
            ]);

        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
}