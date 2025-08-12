<?php
namespace App\Controllers;

use App\Models\UsuarioModel;
use CodeIgniter\Controller;

class Usuario extends BaseController
{
    public function registro()
    {
        return view('registro');
    }

    public function registrar()
    {
        helper(['form']);

        $rules = [
            'nombredeusuario' => 'required|min_length[3]|max_length[50]',
            'email'           => 'required|valid_email|is_unique[usuarios.email]',
            'password'        => 'required|min_length[6]',
            'pass_confirm'    => 'matches[password]'
        ];

        if (!$this->validate($rules)) {
            return view('registro', [
                'validation' => $this->validator
            ]);
        }

        $usuarioModel = new UsuarioModel();

        $datos = [
            'nombredeusuario' => $this->request->getPost('nombredeusuario'),
            'email'           => $this->request->getPost('email'),
            'password'        => password_hash($this->request->getPost('password'), PASSWORD_DEFAULT),
            'fecha_registro'  => date('Y-m-d H:i:s')
        ];

        $usuarioModel->insert($datos);

        return redirect()->to('/usuario/login')->with('success', 'Registro exitoso. Ahora puedes iniciar sesión.');
    }

    public function login()
    {
        return view('login');
    }

    public function iniciarSesion()
    {
        $usuarioModel = new UsuarioModel();

        $email    = $this->request->getPost('email');
        $password = $this->request->getPost('password');

        $usuario = $usuarioModel->where('email', $email)->first();

        if ($usuario && password_verify($password, $usuario['password'])) {
            session()->set([
                'usuario_id' => $usuario['id'],
                'usuario_nombre' => $usuario['nombredeusuario'],
                'logueado' => true
            ]);

return redirect()->to('/formulario'); // en vez de /dashboard

        }

        return view('login', [
            'error' => 'Credenciales incorrectas'
        ]);
    }

    public function logout()
    {
        session()->destroy();
        return redirect()->to('/usuario/login')->with('success', 'Sesión cerrada correctamente.');
    }
}
