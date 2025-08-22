<?php
namespace App\Controllers;

use App\Models\UsuarioModel;

class Usuario extends BaseController
{
    public function registro()
    {
        return view('registro');
    }

    public function registrar()
    {
        // ✅ VALIDACIONES MEJORADAS
        $rules = [
            'nombredeusuario' => [
                'rules' => 'required|min_length[3]|max_length[50]|is_unique[usuarios.nombredeusuario]',
                'errors' => [
                    'required' => 'El nombre de usuario es obligatorio.',
                    'min_length' => 'El nombre de usuario debe tener al menos 3 caracteres.',
                    'max_length' => 'El nombre de usuario no puede exceder 50 caracteres.',
                    'is_unique' => 'Este nombre de usuario ya está registrado.'
                ]
            ],
            'email' => [
                'rules' => 'required|valid_email|is_unique[usuarios.email]',
                'errors' => [
                    'required' => 'El email es obligatorio.',
                    'valid_email' => 'Debes ingresar un email válido.',
                    'is_unique' => 'Este email ya está registrado.'
                ]
            ],
            'password' => [
                'rules' => 'required|min_length[6]|regex_match[/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/]',
                'errors' => [
                'required' => 'La contraseña es obligatoria.',
                'min_length' => 'La contraseña debe tener al menos 6 caracteres.',
                'regex_match' => 'La contraseña debe contener al menos: 1 minúscula, 1 mayúscula y 1 número.'
                ]
            ],
            'pass_confirm' => [
                'rules' => 'required|matches[password]',
                'errors' => [
                    'required' => 'Debes confirmar tu contraseña.',
                    'matches' => 'Las contraseñas no coinciden.'
                ]
            ]
        ];

        if (!$this->validate($rules)) {
            return view('registro', [
                'validation' => $this->validator,
                'old_input' => $this->request->getPost()
            ]);
        }

        $usuarioModel = new UsuarioModel();

        $datos = [
            'nombredeusuario' => trim($this->request->getPost('nombredeusuario')),
            'email'           => strtolower(trim($this->request->getPost('email'))),
            'password'        => password_hash($this->request->getPost('password'), PASSWORD_DEFAULT),
            'fecha_registro'  => date('Y-m-d H:i:s')
        ];

        try {
            $usuarioModel->insert($datos);
            return redirect()->to('/usuario/login')
                ->with('success', '✅ Registro exitoso. Ya puedes iniciar sesión.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', '❌ Error al registrar usuario. Intenta nuevamente.');
        }
    }

    public function login()
    {
        return view('login');
    }

    public function iniciarSesion()
    {
        // PASO 1: Validar datos de entrada
        $rules = [
            'email' => [
                'rules' => 'required|valid_email',
                'errors' => [
                    'required' => 'El email es obligatorio.',
                    'valid_email' => 'Debe ingresar un email válido.'
                ]
            ],
            'password' => [
                'rules' => 'required|min_length[1]',
                'errors' => [
                    'required' => 'La contraseña es obligatoria.',
                    'min_length' => 'La contraseña no puede estar vacía.'
                ]
            ]
        ];

        if (!$this->validate($rules)) {
            return view('login', [
                'validation' => $this->validator
            ]);
        }

        // PASO 2: Obtener y limpiar datos
        $email = trim(strtolower($this->request->getPost('email')));
        $password = $this->request->getPost('password');

        // PASO 3: Verificar que los datos no estén vacíos
        if (empty($email) || empty($password)) {
            return view('login', [
                'error' => 'Email y contraseña son obligatorios.'
            ]);
        }

        // PASO 4: Buscar usuario en la base de datos
        $usuarioModel = new UsuarioModel();
        $usuario = $usuarioModel->where('email', $email)->first();

        // PASO 5: Verificación de seguridad paso a paso
        
        // Si no existe el usuario
        if (!$usuario) {
            log_message('warning', "Intento de login fallido - Usuario no existe: {$email}");
            return view('login', [
                'error' => '❌ Email o contraseña incorrectos. Verifica tus datos e intenta nuevamente.',
                'old_input' => ['email' => $email]
            ]);
        }

        // Si el usuario está inactivo (si agregaste el campo activo)
        if (isset($usuario['activo']) && $usuario['activo'] != 1) {
            log_message('warning', "Intento de login fallido - Usuario inactivo: {$email}");
            return view('login', [
                'error' => 'Tu cuenta está desactivada. Contacta al administrador.'
            ]);
        }

        // PASO 6: Verificar contraseña - AQUÍ ESTÁ LA CLAVE
        $passwordValida = password_verify($password, $usuario['password']);
        
        // Log para debugging (quitar en producción)
        log_message('debug', "Login attempt for {$email}: " . ($passwordValida ? 'SUCCESS' : 'FAILED'));

        if (!$passwordValida) {
            log_message('warning', "Intento de login fallido - Contraseña incorrecta: {$email}");
            return view('login', [
                'error' => '❌ Email o contraseña incorrectos. Verifica tus datos e intenta nuevamente.',
                'old_input' => ['email' => $email]
            ]);
        }

        // PASO 7: Login exitoso - Crear sesión
        log_message('info', "Login exitoso para usuario: {$email}");
        
        $sesionData = [
            'usuario_id' => $usuario['id'],
            'usuario_nombre' => $usuario['nombredeusuario'],
            'usuario_email' => $usuario['email'],
            'logueado' => true,
            'tiempo_login' => time()
        ];

        session()->set($sesionData);

        return redirect()->to('/formulario')
                        ->with('success', 'Bienvenido/a ' . $usuario['nombredeusuario']);
    }

    public function logout()
    {
        session()->destroy();
        return redirect()->to('/usuario/login')
            ->with('success', '✅ Sesión cerrada correctamente.');
    }
}