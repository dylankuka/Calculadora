<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= ($estado ?? '') === 'aprobado' ? '¡Donación Exitosa!' : 'Estado de Donación' ?> - TaxImporter</title>
    <link rel="stylesheet" href="<?= base_url('css/ind.css') ?>">
    <link rel="stylesheet" href="<?= base_url('css/donacion.css') ?>">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .success-animation {
            animation: bounceIn 0.8s ease-out;
        }
        
        @keyframes bounceIn {
            0% {
                opacity: 0;
                transform: scale(0.3);
            }
            50% {
                opacity: 1;
                transform: scale(1.05);
            }
            70% {
                transform: scale(0.9);
            }
            100% {
                opacity: 1;
                transform: scale(1);
            }
        }
        
        .status-icon {
            font-size: 4rem;
            margin-bottom: 20px;
        }
        
        .confetti {
            position: absolute;
            width: 10px;
            height: 10px;
            background: #ff9900;
            animation: confetti-fall 3s ease-in-out infinite;
        }
        
        @keyframes confetti-fall {
            0% { 
                transform: translateY(-100vh) rotate(0deg); 
                opacity: 1; 
            }
            100% { 
                transform: translateY(100vh) rotate(720deg); 
                opacity: 0; 
            }
        }
    </style>
</head>
<body class="donation-body position-relative overflow-hidden">

<!-- Confetti animado solo si fue exitoso -->
<?php if (($estado ?? '') === 'aprobado'): ?>
    <!-- Partículas de celebración -->
    <?php for($i = 0; $i < 15; $i++): ?>
        <div class="confetti" style="left: <?= rand(5, 95) ?>%; 
                                           animation-delay: <?= rand(0, 3000) ?>ms; 
                                           background: <?= ['#ff9900', '#28a745', '#dc3545', '#17a2b8', '#ffc107'][rand(0, 4)] ?>;"></div>
    <?php endfor; ?>
<?php endif; ?>

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
            
            <!-- Tarjeta principal de resultado -->
            <div class="amazon-dark-card shadow-lg success-animation">
                <?php 
                $estado = $estado ?? 'pendiente';
                $isApproved = $estado === 'aprobado';
                $isPending = $estado === 'pendiente';
                $isRejected = in_array($estado, ['rechazado', 'cancelado']);
                
                // Configurar colores y iconos según estado
                if ($isApproved) {
                    $headerClass = 'bg-success';
                    $iconClass = 'bi-check-circle-fill text-success';
                    $titleText = '¡Donación Exitosa!';
                } elseif ($isPending) {
                    $headerClass = 'bg-warning';
                    $iconClass = 'bi-clock-fill text-warning';
                    $titleText = 'Donación en Proceso';
                } else {
                    $headerClass = 'bg-secondary';
                    $iconClass = 'bi-x-circle-fill text-danger';
                    $titleText = 'Donación No Procesada';
                }
                ?>
                
                <div class="card-header <?= $headerClass ?> text-white text-center py-4">
                    <div class="status-icon">
                        <i class="bi <?= $iconClass ?>"></i>
                    </div>
                    <h2><?= $titleText ?></h2>
                    <?php if ($isApproved): ?>
                        <p class="mb-0 lead">¡Gracias por tu generoso apoyo!</p>
                    <?php elseif ($isPending): ?>
                        <p class="mb-0 lead">Tu pago está siendo verificado</p>
                    <?php else: ?>
                        <p class="mb-0 lead">Puedes intentar nuevamente</p>
                    <?php endif; ?>
                </div>
                
                <div class="card-body text-center py-5">
                    <!-- Mensaje principal -->
                    <div class="alert alert-<?= $isApproved ? 'success' : ($isPending ? 'warning' : 'secondary') ?> mb-4">
                        <h5 class="alert-heading">
                            <?php if ($isApproved): ?>
                                <i class="bi bi-heart-fill"></i> ¡Tu donación fue procesada con éxito!
                            <?php elseif ($isPending): ?>
                                <i class="bi bi-hourglass-split"></i> Tu donación está siendo procesada
                            <?php else: ?>
                                <i class="bi bi-info-circle"></i> Tu donación no pudo ser procesada
                            <?php endif; ?>
                        </h5>
                        <p class="mb-0"><?= esc($mensaje ?? 'Mensaje no disponible') ?></p>
                    </div>

                    <!-- Detalles de la donación si existe -->
                    <?php if (isset($donacion) && $donacion): ?>
                        <div class="row mb-4">
                            <div class="col-md-4">
                                <div class="amazon-dark-card p-3 mb-3 mb-md-0">
                                    <i class="bi bi-currency-dollar text-amazon-orange h3"></i>
                                    <h4 class="text-amazon-orange mb-1">$<?= number_format($donacion['monto_ars'], 0, ',', '.') ?></h4>
                                    <small class="text-amazon-light">Monto donado</small>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="amazon-dark-card p-3 mb-3 mb-md-0">
                                    <i class="bi bi-calendar-check text-info h3"></i>
                                    <h6 class="mb-1"><?= date('d/m/Y H:i', strtotime($donacion['fecha_donacion'])) ?></h6>
                                    <small class="text-amazon-light">Fecha de donación</small>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="amazon-dark-card p-3">
                                    <i class="bi bi-hash text-warning h3"></i>
                                    <h6 class="mb-1">#<?= $donacion['id'] ?></h6>
                                    <small class="text-amazon-light">ID de referencia</small>
                                </div>
                            </div>
                        </div>

                        <!-- Información del pago de MercadoPago -->
                        <?php if (isset($payment) && $payment && isset($payment['id'])): ?>
                            <div class="amazon-dark-card p-3 mb-4">
                                <h6 class="text-amazon-orange mb-3">
                                    <i class="bi bi-credit-card"></i> Detalles del Pago
                                </h6>
                                <div class="row text-start">
                                    <div class="col-sm-6">
                                        <small><strong>ID MercadoPago:</strong> <?= esc($payment['id']) ?></small>
                                    </div>
                                    <div class="col-sm-6">
                                        <small><strong>Estado:</strong> 
                                            <span class="badge bg-<?= $isApproved ? 'success' : ($isPending ? 'warning' : 'secondary') ?>">
                                                <?= ucfirst($estado) ?>
                                            </span>
                                        </small>
                                    </div>
                                    <?php if (isset($payment['payment_method_id']) && $payment['payment_method_id']): ?>
                                    <div class="col-sm-6">
                                        <small><strong>Método de pago:</strong> <?= ucfirst($payment['payment_method_id']) ?></small>
                                    </div>
                                    <?php endif; ?>
                                    <?php if (isset($payment['date_approved']) && $payment['date_approved']): ?>
                                    <div class="col-sm-6">
                                        <small><strong>Fecha de aprobación:</strong> <?= date('d/m/Y H:i', strtotime($payment['date_approved'])) ?></small>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>

                    <!-- Mensaje de agradecimiento personalizado -->
                    <?php if ($isApproved): ?>
                        <div class="mb-4">
                            <h4 class="text-amazon-orange mb-3">🎉 ¡Eres increíble!</h4>
                            <p class="lead mb-3">
                                Tu donación nos ayuda a mantener TaxImporter <strong>completamente gratuito</strong> 
                                para toda la comunidad argentina.
                            </p>
                            
                            <div class="row text-center mt-4">
                                <div class="col-6 col-md-3 mb-3">
                                    <div class="feature-icon text-amazon-orange">
                                        <i class="bi bi-server h4 mb-0"></i>
                                    </div>
                                    <h6 class="text-white">Hosting</h6>
                                    <small class="text-amazon-light">Mantenemos los servidores</small>
                                </div>
                                <div class="col-6 col-md-3 mb-3">
                                    <div class="feature-icon text-amazon-orange">
                                        <i class="bi bi-code-slash h4 mb-0"></i>
                                    </div>
                                    <h6 class="text-white">Desarrollo</h6>
                                    <small class="text-amazon-light">Nuevas funcionalidades</small>
                                </div>
                                <div class="col-6 col-md-3 mb-3">
                                    <div class="feature-icon text-amazon-orange">
                                        <i class="bi bi-graph-up h4 mb-0"></i>
                                    </div>
                                    <h6 class="text-white">APIs Premium</h6>
                                    <small class="text-amazon-light">Cotizaciones precisas</small>
                                </div>
                                <div class="col-6 col-md-3 mb-3">
                                    <div class="feature-icon text-amazon-orange">
                                        <i class="bi bi-shield-check h4 mb-0"></i>
                                    </div>
                                    <h6 class="text-white">Seguridad</h6>
                                    <small class="text-amazon-light">Actualizaciones constantes</small>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Acciones -->
                    <div class="d-grid gap-2 d-md-flex justify-content-center">
                        <?php if ($isApproved): ?>
                            <a href="<?= base_url('historial/crear') ?>" class="btn btn-success btn-lg">
                                <i class="bi bi-calculator"></i> Seguir Calculando
                            </a>
                        <?php elseif ($isPending): ?>
                            <a href="<?= base_url('donacion') ?>" class="btn btn-warning btn-lg">
                                <i class="bi bi-clock"></i> Ver Mis Donaciones
                            </a>
                        <?php else: ?>
                            <a href="<?= base_url('donacion') ?>" class="btn btn-primary btn-lg">
                                <i class="bi bi-arrow-clockwise"></i> Intentar Nuevamente
                            </a>
                        <?php endif; ?>
                        
                        <a href="<?= base_url('historial') ?>" class="btn btn-outline-light btn-lg">
                            <i class="bi bi-house"></i> Ir al Inicio
                        </a>
                    </div>
                    
                    <!-- Enlace a detalles -->
                    <?php if (isset($donacion) && $donacion): ?>
                        <div class="mt-3">
                            <a href="<?= base_url('donacion/ver/' . $donacion['id']) ?>" class="btn btn-sm btn-outline-info">
                                <i class="bi bi-receipt"></i> Ver Comprobante Completo
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Compartir en redes (solo si fue exitoso) -->
            <?php if ($isApproved): ?>
                <div class="text-center mt-4">
                    <h6 class="text-amazon-light mb-3">¡Comparte tu apoyo!</h6>
                    <div class="d-flex justify-content-center gap-2 flex-wrap">
                        <button onclick="compartirTwitter()" class="btn btn-outline-info btn-sm">
                            <i class="bi bi-twitter"></i> Twitter
                        </button>
                        <button onclick="compartirWhatsApp()" class="btn btn-outline-success btn-sm">
                            <i class="bi bi-whatsapp"></i> WhatsApp
                        </button>
                        <button onclick="copiarEnlace()" class="btn btn-outline-warning btn-sm">
                            <i class="bi bi-link"></i> Compartir TaxImporter
                        </button>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Scripts -->
<script>
function compartirTwitter() {
    const texto = "Acabo de apoyar a @TaxImporter 🧮💖 Una herramienta gratuita que me ayuda a calcular impuestos de importación. ¡Úsala tú también!";
    const url = "https://twitter.com/intent/tweet?text=" + encodeURIComponent(texto) + "&url=" + encodeURIComponent("<?= base_url() ?>");
    window.open(url, '_blank', 'width=600,height=400');
}

function compartirWhatsApp() {
    const texto = "¡Apoyé a TaxImporter! 🧮💖\n\nUna herramienta gratuita que me ayuda a calcular impuestos de importación desde Amazon.\n\n¡Pruébala!";
    const url = "https://wa.me/?text=" + encodeURIComponent(texto + "\n<?= base_url() ?>");
    window.open(url, '_blank');
}

function copiarEnlace() {
    const enlace = "<?= base_url() ?>";
    if (navigator.clipboard) {
        navigator.clipboard.writeText(enlace).then(() => {
            alert('✅ Enlace de TaxImporter copiado al portapapeles');
        });
    } else {
        prompt('Copia este enlace para compartir TaxImporter:', enlace);
    }
}

// Auto-redirect después de 45 segundos (solo para éxito)
<?php if ($isApproved): ?>
setTimeout(() => {
    if (confirm('¿Quieres volver al calculadora principal?')) {
        window.location.href = '<?= base_url("historial") ?>';
    }
}, 45000);
<?php endif; ?>
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>