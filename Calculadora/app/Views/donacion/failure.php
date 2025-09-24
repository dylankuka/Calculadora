<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Donación Cancelada - TaxImporter</title>
    <link rel="stylesheet" href="<?= base_url('css/ind.css') ?>">
    <link rel="stylesheet" href="<?= base_url('css/donacion.css') ?>">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .shake-animation {
            animation: shake 0.8s ease-in-out;
        }
        
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            10%, 30%, 50%, 70%, 90% { transform: translateX(-5px); }
            20%, 40%, 60%, 80% { transform: translateX(5px); }
        }
        
        .failure-icon {
            font-size: 4rem;
            margin-bottom: 20px;
            color: #dc3545;
        }
    </style>
</head>
<body class="donation-body">

<!-- Logo -->
<div class="position-absolute" style="top: 5px; left: 22px; z-index: 1000;">
    <a href="<?= base_url() ?>">
        <img src="<?= base_url('img/taximporterlogo.png') ?>" 
             alt="Logo TaxImporter" 
             style="max-width: 70px; height: auto; 
                    filter: drop-shadow(2px 2px 6px rgba(0,0,0,0.8));">
    </a>
</div>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark navbar-amazon">
    <div class="container">
        <a class="navbar-brand text-amazon-orange fw-bold" href="<?= base_url('/') ?>">
            <i class="bi bi-calculator"></i> TaxImporter
        </a>
        
        <div class="navbar-nav ms-auto">
            <span class="navbar-text me-3 text-white">
                <i class="bi bi-person-circle text-amazon-orange"></i> 
                <strong><?= esc(session()->get('usuario_nombre')) ?></strong>
            </span>
            <a class="btn btn-outline-light btn-sm" href="<?= base_url('/historial') ?>">
                <i class="bi bi-arrow-left"></i> Volver al Historial
            </a>
        </div>
    </div>
</nav>

<div class="container">
    <div class="row justify-content-center align-items-center" style="min-height: 80vh;">
        <div class="col-lg-8 col-xl-6">
            
            <!-- Tarjeta principal de fallo -->
            <div class="amazon-dark-card shadow-lg shake-animation">
                <div class="card-header bg-warning text-dark text-center py-4">
                    <div class="failure-icon">
                        <i class="bi bi-exclamation-triangle"></i>
                    </div>
                    <h2>Donación No Completada</h2>
                    <p class="mb-0 lead">El pago fue cancelado o no pudo procesarse</p>
                </div>
                
                <div class="card-body text-center py-5">
                    <!-- Mensaje principal -->
                    <div class="alert alert-warning mb-4">
                        <h5 class="alert-heading">
                            <i class="bi bi-info-circle"></i> ¿Qué pasó?
                        </h5>
                        <p class="mb-0"><?= esc($mensaje ?? 'El pago fue cancelado o rechazado por MercadoPago.') ?></p>
                    </div>

                    <!-- Detalles de la donación si existe -->
                    <?php if (isset($donacion) && $donacion): ?>
                        <div class="amazon-dark-card p-4 mb-4">
                            <h6 class="text-amazon-orange mb-3">
                                <i class="bi bi-receipt"></i> Información de la Donación
                            </h6>
                            <div class="row">
                                <div class="col-md-4">
                                    <strong>Monto:</strong><br>
                                    <span class="text-amazon-orange">$<?= number_format($donacion['monto_ars'], 0, ',', '.') ?> ARS</span>
                                </div>
                                <div class="col-md-4">
                                    <strong>Fecha:</strong><br>
                                    <?= date('d/m/Y H:i', strtotime($donacion['fecha_donacion'])) ?>
                                </div>
                                <div class="col-md-4">
                                    <strong>ID:</strong><br>
                                    #<?= $donacion['id'] ?>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Posibles razones -->
                    <div class="funds-usage mb-4">
                        <h6 class="mb-3">
                            <i class="bi bi-question-circle"></i> Posibles razones del problema
                        </h6>
                        <div class="row">
                            <div class="col-md-6">
                                <ul class="text-start mb-0">
                                    <li><i class="bi bi-x-circle text-danger"></i> Cancelaste el pago voluntariamente</li>
                                    <li><i class="bi bi-credit-card text-warning"></i> Fondos insuficientes en tu tarjeta</li>
                                    <li><i class="bi bi-shield-x text-warning"></i> Tarjeta bloqueada por el banco</li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <ul class="text-start mb-0">
                                    <li><i class="bi bi-wifi-off text-warning"></i> Problemas de conexión temporales</li>
                                    <li><i class="bi bi-clock text-info"></i> Sesión de pago expirada</li>
                                    <li><i class="bi bi-gear text-secondary"></i> Error técnico de MercadoPago</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- Mensaje de apoyo -->
                    <div class="mb-4">
                        <h4 class="text-amazon-orange mb-3">😊 ¡No te preocupes!</h4>
                        <p class="mb-3">
                            TaxImporter sigue siendo <strong>completamente gratuito</strong> para todos los usuarios. 
                            Tu intención de apoyo ya significa mucho para nosotros.
                        </p>
                        <p class="text-amazon-light">
                            Si quieres intentar nuevamente, puedes hacerlo en cualquier momento. 
                            También puedes seguir usando todas las funciones de TaxImporter sin restricciones.
                        </p>
                    </div>

                    <!-- Acciones disponibles -->
                    <div class="d-grid gap-2 d-md-flex justify-content-center mb-4">
                        <a href="<?= base_url('donacion') ?>" class="btn btn-warning btn-lg">
                            <i class="bi bi-arrow-clockwise"></i> Intentar Nuevamente
                        </a>
                        <a href="<?= base_url('historial/crear') ?>" class="btn btn-success btn-lg">
                            <i class="bi bi-calculator"></i> Usar Calculadora
                        </a>
                        <a href="<?= base_url('historial') ?>" class="btn btn-outline-light btn-lg">
                            <i class="bi bi-house"></i> Ir al Inicio
                        </a>
                    </div>

                    <!-- Información de contacto -->
                    <div class="amazon-dark-card p-3">
                        <h6 class="text-amazon-orange mb-2">
                            <i class="bi bi-headset"></i> ¿Necesitas Ayuda?
                        </h6>
                        <p class="small text-amazon-light mb-2">
                            Si tienes problemas recurrentes con los pagos o crees que es un error, puedes:
                        </p>
                        <div class="d-flex justify-content-center gap-2 flex-wrap">
                            <a href="mailto:soporte@taximporter.com" class="btn btn-sm btn-outline-info">
                                <i class="bi bi-envelope"></i> Contactar Soporte
                            </a>
                            <button onclick="reportarProblema()" class="btn btn-sm btn-outline-warning">
                                <i class="bi bi-bug"></i> Reportar Problema
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Mensaje motivacional -->
            <div class="text-center mt-4">
                <div class="amazon-dark-card p-4">
                    <h5 class="text-amazon-orange mb-3">
                        <i class="bi bi-heart"></i> ¡Gracias por la intención!
                    </h5>
                    <p class="text-amazon-light mb-0">
                        Ya el hecho de que hayas intentado apoyarnos nos llena de alegría. 
                        TaxImporter existe para ayudarte, <strong>con o sin donaciones</strong>. 
                        ¡Seguí calculando tus impuestos sin problemas!
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Scripts -->
<script>
function reportarProblema() {
    const problema = `Reporte de Problema en Donación
    
Usuario: <?= esc(session()->get('usuario_nombre')) ?>
Fecha: <?= date('d/m/Y H:i:s') ?>
<?php if (isset($donacion)): ?>
Donación ID: <?= $donacion['id'] ?>
Monto: $<?= number_format($donacion['monto_ars'], 0, ',', '.') ?> ARS
<?php endif; ?>
URL actual: ${window.location.href}
User Agent: ${navigator.userAgent}

Descripción del problema:
[Describe aquí qué pasó]`;
    
    const mailtoLink = `mailto:soporte@taximporter.com?subject=Problema con Donación&body=${encodeURIComponent(problema)}`;
    window.location.href = mailtoLink;
}

// Auto-redirect después de 30 segundos
setTimeout(() => {
    if (confirm('¿Quieres volver a la página principal de donaciones?')) {
        window.location.href = '<?= base_url("donacion") ?>';
    }
}, 30000);
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>