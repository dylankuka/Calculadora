<?php
namespace App\Services;

use CodeIgniter\Email\Email;

class PasswordRecoveryService
{
    private $email;

    public function __construct()
    {
        $this->email = new Email();
        $fromEmail = getenv('MAIL_FROM_EMAIL') ?: env('MAIL_FROM_EMAIL');
        $fromName = getenv('MAIL_FROM_NAME') ?: env('MAIL_FROM_NAME', 'TaxImporter');
        
        $this->email->setFrom($fromEmail, $fromName);
        $this->email->initialize([
            'protocol'  => getenv('MAIL_PROTOCOL') ?: env('MAIL_PROTOCOL', 'smtp'),
            'SMTPHost'  => getenv('MAIL_HOST') ?: env('MAIL_HOST'),
            'SMTPPort'  => getenv('MAIL_PORT') ?: env('MAIL_PORT'),
            'SMTPUser'  => getenv('MAIL_USERNAME') ?: env('MAIL_USERNAME'),
            'SMTPPass'  => getenv('MAIL_PASSWORD') ?: env('MAIL_PASSWORD'),
            'mailType'  => 'html',
            'charset'   => 'UTF-8',
            'newline'   => "\r\n"
        ]);
    }

    /**
     * Generar token de recuperación
     */
    public function generarToken($usuarioId)
    {
        $token = bin2hex(random_bytes(32));
        $expiracion = date('Y-m-d H:i:s', strtotime('+1 hour'));
        
        $db = \Config\Database::connect();
        $db->table('usuarios')->update(
            ['reset_token' => $token, 'reset_expiracion' => $expiracion],
            ['id' => $usuarioId]
        );
        
        return $token;
    }

    /**
     * Enviar email de recuperación
     */
    public function enviarRecuperacion($email, $nombreUsuario, $token)
    {
        try {
            $urlRecuperacion = base_url('usuario/resetear/' . $token);
            
            $this->email->setTo($email);
            $this->email->setSubject('🔐 Recupera tu contraseña - TaxImporter');
            $this->email->setMessage($this->generarCuerpo($nombreUsuario, $urlRecuperacion));

            if ($this->email->send()) {
                log_message('info', "Email de recuperación enviado a: {$email}");
                return true;
            } else {
                log_message('error', 'Error enviando email: ' . $this->email->printDebugger());
                return false;
            }
        } catch (\Exception $e) {
            log_message('error', 'Error en recuperación: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Validar token
     */
public function validarToken($token)
{
    $db = \Config\Database::connect();

    /** @var \CodeIgniter\Database\BaseBuilder $builder */
    $builder = $db->table('usuarios');

    $usuario = $builder
        ->where('reset_token', $token)
        ->where('reset_expiracion >', date('Y-m-d H:i:s'))
        ->get()
        ->getRow();

    return $usuario;
}


    /**
     * Resetear contraseña
     */
    public function resetearPassword($usuarioId, $nuevaPassword)
    {
        $db = \Config\Database::connect();
        return $db->table('usuarios')->update(
            [
                'password' => password_hash($nuevaPassword, PASSWORD_DEFAULT),
                'reset_token' => null,
                'reset_expiracion' => null
            ],
            ['id' => $usuarioId]
        );
    }

    private function generarCuerpo($nombre, $enlace)
    {
        return "
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; color: #333; }
                .container { max-width: 600px; margin: 0 auto; background: #f9f9f9; padding: 20px; border-radius: 8px; }
                .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; text-align: center; border-radius: 8px 8px 0 0; }
                .content { background: white; padding: 30px; border-radius: 0 0 8px 8px; }
                .btn { display: inline-block; padding: 12px 30px; background: #667eea; color: white; text-decoration: none; border-radius: 5px; margin: 20px 0; }
                .warning { background: #fff3cd; padding: 15px; border-left: 4px solid #ffc107; margin: 20px 0; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>🔐 Recupera tu contraseña</h1>
                </div>
                <div class='content'>
                    <p>Hola <strong>{$nombre}</strong>,</p>
                    
                    <p>Recibimos una solicitud para recuperar tu contraseña. Haz clic en el enlace a continuación:</p>
                    
                    <a href='{$enlace}' class='btn'>Resetear Contraseña</a>
                    
                    <div class='warning'>
                        <strong>⚠️ Importante:</strong> Este enlace expira en 1 hora por seguridad.
                    </div>
                    
                    <p>Si no solicitaste recuperar tu contraseña, ignora este email.</p>
                    
                    <p style='margin-top: 40px; color: #666;'>Equipo TaxImporter</p>
                </div>
            </div>
        </body>
        </html>
        ";
    }
}