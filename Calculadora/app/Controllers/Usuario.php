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
            
            // ✅ AUTO-LOGIN DESPUÉS DEL REGISTRO
            $usuario = $usuarioModel->where('email', $datos['email'])->first();
            session()->set([
                'usuario_id' => $usuario['id'],
                'usuario_nombre' => $usuario['nombredeusuario'],
                'usuario_email' => $usuario['email'],
                'usuario_rol' => $usuario['rol'] ?? 'usuario',
                'logueado' => true
            ]);

            // Verificar si hay un cálculo pendiente para guardar
            if (session()->getTempdata('calculo_pendiente')) {
                return redirect()->to('/historial/crear')
                    ->with('success', '✅ ¡Cuenta creada! Ahora puedes guardar tu cálculo.');
            }
            
            return redirect()->to('/historial')
                ->with('success', '✅ ¡Bienvenido/a ' . $usuario['nombredeusuario'] . '! Tu cuenta ha sido creada exitosamente.');
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
    // Validaciones
    $rules = [
        'email' => [
            'rules' => 'required|valid_email',
            'errors' => [
                'required' => 'El email es obligatorio.',
                'valid_email' => 'Debes ingresar un email válido.'
            ]
        ],
        'password' => [
            'rules' => 'required|min_length[6]',
            'errors' => [
                'required' => 'La contraseña es obligatoria.',
                'min_length' => 'La contraseña debe tener al menos 6 caracteres.'
            ]
        ]
    ];

    if (!$this->validate($rules)) {
        return view('login', [
            'validation' => $this->validator,
            'old_input' => $this->request->getPost()
        ]);
    }

    $usuarioModel = new UsuarioModel();
    $email = strtolower(trim($this->request->getPost('email')));
    $password = $this->request->getPost('password');
    $usuario = $usuarioModel->where('email', $email)->first();

    if ($usuario && password_verify($password, $usuario['password'])) {
        
        // ✅ VERIFICAR SI EL USUARIO ESTÁ ACTIVO
        if ($usuario['activo'] == 0) {
            return view('login', [
                'error' => '❌ Tu cuenta ha sido desactivada. Contacta al administrador para más información.',
                'old_input' => ['email' => $email]
            ]);
        }
        
        // Usuario activo - permitir login
        session()->set([
            'usuario_id' => $usuario['id'],
            'usuario_nombre' => $usuario['nombredeusuario'],
            'usuario_email' => $usuario['email'],
            'usuario_rol' => $usuario['rol'] ?? 'usuario',
            'logueado' => true
        ]);

        if (session()->getTempdata('calculo_pendiente')) {
            return redirect()->to('/historial/crear')
                ->with('success', '✅ ¡Sesión iniciada! Ahora puedes guardar tu cálculo.');
        }

        return redirect()->to('/historial')
            ->with('success', '✅ ¡Bienvenido/a ' . $usuario['nombredeusuario'] . '!');
    }

    // Error de credenciales
    return view('login', [
        'error' => '❌ Email o contraseña incorrectos. Verifica tus datos e intenta nuevamente.',
        'old_input' => ['email' => $email]
    ]);
}

    public function logout()
    {
        session()->destroy();
        return redirect()->to('/')
            ->with('success', '✅ Sesión cerrada correctamente. ¡Vuelve pronto!');
    }
    public function olvidePassword()
{
    return view('olvide_password');
}

public function enviarRecuperacion()
{
    $email = strtolower(trim($this->request->getPost('email')));
    
    $usuarioModel = new UsuarioModel();
    $usuario = $usuarioModel->where('email', $email)->first();

    if (!$usuario) {
        return redirect()->back()
            ->with('error', '❌ Email no registrado en el sistema.');
    }

    $recoveryService = new \App\Services\PasswordRecoveryService();
    $token = $recoveryService->generarToken($usuario['id']);
    
    if ($recoveryService->enviarRecuperacion($email, $usuario['nombredeusuario'], $token)) {
        return redirect()->to('/usuario/login')
            ->with('success', '✅ Email de recuperación enviado. Revisa tu bandeja de entrada.');
    }

    return redirect()->back()
        ->with('error', '❌ Error al enviar email. Intenta más tarde.');
}

public function resetear($token)
{
    $recoveryService = new \App\Services\PasswordRecoveryService();
    $usuario = $recoveryService->validarToken($token);

    if (!$usuario) {
        return redirect()->to('/usuario/olvide-password')
            ->with('error', '❌ Token inválido o expirado.');
    }

    return view('resetear_password', ['token' => $token]);
}

public function guardarPassword()
{
    $token = $this->request->getPost('token');
    $password = $this->request->getPost('password');
    $passConfirm = $this->request->getPost('pass_confirm');

    $rules = [
        'password' => [
            'rules' => 'required|min_length[6]|regex_match[/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/]',
            'errors' => [
                'required' => 'La contraseña es obligatoria.',
                'min_length' => 'Mínimo 6 caracteres.',
                'regex_match' => 'Debe contener: 1 minúscula, 1 mayúscula y 1 número.'
            ]
        ],
        'pass_confirm' => [
            'rules' => 'required|matches[password]',
            'errors' => [
                'matches' => 'Las contraseñas no coinciden.'
            ]
        ]
    ];

    if (!$this->validate($rules)) {
        return redirect()->back()
            ->with('validation', $this->validator)
            ->with('token', $token);
    }

    $recoveryService = new \App\Services\PasswordRecoveryService();
    $usuario = $recoveryService->validarToken($token);

    if (!$usuario) {
        return redirect()->to('/usuario/olvide-password')
            ->with('error', '❌ Token expirado.');
    }

    $recoveryService->resetearPassword($usuario['id'], $password);

    return redirect()->to('/usuario/login')
        ->with('success', '✅ Contraseña actualizada. Inicia sesión con tu nueva contraseña.');
}
}
