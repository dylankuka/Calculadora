<?php
namespace App\Controllers;

class Calculadora extends BaseController
{
    public function formulario()
    {
        return view('pegar_url');
    }

    public function calcular()
    {
        $amazonUrl = $this->request->getPost('amazon_url');

        if (!filter_var($amazonUrl, FILTER_VALIDATE_URL) || strpos($amazonUrl, 'amazon.') === false) {
            return redirect()->back()->with('error', 'Por favor ingresa una URL válida de Amazon.');
        }

        return "Procesando: " . esc($amazonUrl);
    }
}
