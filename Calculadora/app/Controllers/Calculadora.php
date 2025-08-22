<?php
namespace App\Controllers;

class Calculadora extends BaseController
{
    private function validarSesion()
    {
        if (!session()->get('logueado')) {
            return false;
        }
        return true;
    }

    // ✅ MÉTODO INDEX AGREGADO
    public function index()
    {
        // Si el usuario está logueado, redirigir al historial
        if (session()->get('logueado')) {
            return redirect()->to('/historial');
        }
        
        // Si no está logueado, mostrar el login
        return redirect()->to('/usuario/login');
    }

public function formulario()
{
    // Si no está logueado, redirigir al login
    if (!session()->get('logueado')) {
        return redirect()->to('/usuario/login');
    }
    
    // Redirigir al historial en lugar de cargar la vista directamente
    return redirect()->to('/historial');
}

    public function calcular()
    {
        $this->validarSesion();

        $amazonUrl = $this->request->getPost('amazon_url');

        // Validación URL (ejemplo simple)
        if (!filter_var($amazonUrl, FILTER_VALIDATE_URL) || strpos($amazonUrl, 'amazon.') === false) {
            return redirect()->back()->with('error', 'Por favor ingresa una URL válida de Amazon.');
        }

        return "Procesando: " . esc($amazonUrl);
    }
}