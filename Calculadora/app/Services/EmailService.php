<?php
namespace App\Services;

use CodeIgniter\Email\Email;

class EmailService
{
    private $email;

    public function __construct()
{
    $this->email = new Email();
    
    $this->email->initialize([
        'protocol'  => 'smtp',
        'SMTPHost'  => 'smtp.gmail.com',
        'SMTPPort'  => 587,
        'SMTPUser'  => 'dylankiyama1@gmail.com',
        'SMTPPass'  => 'urek pabb wiot uwvs',
        'SMTPCrypto' => 'tls',  // ✅ AGREGAR ESTO
        'mailType'  => 'html',
        'charset'   => 'UTF-8',
        'newline'   => "\r\n"
    ]);
    
    $this->email->setFrom('dylankiyama1@gmail.com', 'TaxImporter');
}

    /**
     * Enviar email de donación exitosa
     */
    public function enviarConfirmacionDonacion($email, $nombreUsuario, $monto, $numeroReferencia)
    {
        try {
            $this->email->setTo($email);
            $this->email->setSubject('✅ Donación recibida - TaxImporter');
            
            $cuerpo = $this->generarCuerpoConfirmacion($nombreUsuario, $monto, $numeroReferencia);
            $this->email->setMessage($cuerpo);

            if ($this->email->send()) {
                log_message('info', "Email de confirmación enviado a: {$email}");
                return true;
            } else {
                log_message('error', 'Error enviando email: ' . $this->email->printDebugger());
                return false;
            }
        } catch (\Exception $e) {
            log_message('error', 'Excepción al enviar email: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Generar HTML del email
     */
    private function generarCuerpoConfirmacion($nombre, $monto, $referencia)
    {
        return "
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; background: #f9f9f9; padding: 20px; border-radius: 8px; }
                .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; text-align: center; border-radius: 8px 8px 0 0; }
                .content { background: white; padding: 30px; border-radius: 0 0 8px 8px; }
                .monto { font-size: 32px; color: #667eea; font-weight: bold; margin: 20px 0; }
                .detalles { background: #f0f4ff; padding: 15px; border-left: 4px solid #667eea; margin: 20px 0; }
                .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
                .btn { display: inline-block; padding: 12px 30px; background: #667eea; color: white; text-decoration: none; border-radius: 5px; margin-top: 20px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>¡Gracias por tu donación! 🎉</h1>
                </div>
                <div class='content'>
                    <p>Hola <strong>{$nombre}</strong>,</p>
                    
                    <p>Nos complace informarte que tu donación ha sido <strong>procesada exitosamente</strong>.</p>
                    
                    <div class='monto'>\${$monto} ARS</div>
                    
                    <div class='detalles'>
                        <p><strong>Número de referencia:</strong> {$referencia}</p>
                        <p><strong>Fecha:</strong> " . date('d/m/Y H:i:s') . "</p>
                    </div>
                    
                    <p>Tu aporte ayuda a mantener y mejorar <strong>TaxImporter</strong>, una herramienta que facilita los cálculos fiscales para miles de usuarios.</p>
                    
                    <p>Si tienes preguntas o necesitas más información, no dudes en contactarnos.</p>
                    
                    <p><a href='" . base_url('donacion') . "' class='btn'>Ver mis donaciones</a></p>
                    
                    <p style='margin-top: 40px; color: #666;'>Con gratitud,<br><strong>El equipo de TaxImporter</strong></p>
                </div>
                <div class='footer'>
                    <p>&copy; 2025 TaxImporter. Todos los derechos reservados.</p>
                </div>
            </div>
        </body>
        </html>
        ";
    }
}