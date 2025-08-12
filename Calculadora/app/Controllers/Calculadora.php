<?php
namespace App\Controllers;

class Calculadora extends BaseController
{
    private function validarSesion()
    {
        if (!session()->get('logueado')) {
            return redirect()->to('/usuario/login')->with('error', 'Debes iniciar sesión para continuar')->send();
            exit;
        }
    }

    public function formulario()
    {
        $this->validarSesion();
        return view('url');
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
