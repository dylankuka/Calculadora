<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>¡Donación Exitosa! - TaxImporter</title>
    <link rel="stylesheet" href="<?= base_url('css/ind.css') ?>">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .success-animation {
            animation: bounce 0.6s ease-in-out;
        }
        @keyframes bounce {
            0%, 20%, 60%, 100% { transform: translateY(0); }
            40% { transform: translateY(-20px); }
            80% { transform: translateY(-10px); }
        }
        .confetti {
            position: absolute;
            width: 10px;
            height: 10px;
            background: #f39c12;
            animation: confetti 3s ease-in-out infinite;
        }
        @keyframes confetti {
            0% { transform: translateY(-100vh) rotate(0deg); opacity: 1; }
            100% { transform: translateY(100vh) rotate(720deg); opacity: 0; }
        }
    </style>
</head>
<body class="bg-dark position-relative overflow-hidden">

<!-- Confetti decorativo -->
<div class="confetti" style="left: 10%; animation-delay: 0s; background: #e74c3c;"></div>
<div class="confetti" style="left: 20%; animation-delay: 0.5s; background: #f39c12;"></div>
<div class="confetti" style="left: 30%; animation-delay: 1s; background: #27ae60;"></div>
<div class="confetti" style="left: 40%; animation-delay: 1.5s; background: #3498db;"></div>
<div class="confetti" style="left: 50%; animation-delay: 2s; background: #9b59b6;"></div>
<div class="confetti" style="left: 60%; animation-delay: 0.3s; background: #e67e22;"></div>
<div class="confetti" style="left: 70%; animation-delay: 0.8s; background: #2ecc71;"></div>
<div class="confetti" style="left: 80%; animation-delay: 1.3s; background: #e74c3c;"></div>
<div class="confetti" style="left: 90%; animation-delay: 1.8s; background: #f39c12;"></div>

<!-- Logo -->
<div class="position-absolute" style="top: 5px; left: 22px; z-index: 1000;">
    <a href="<?= base_url() ?>">
        <img src="<?= base_url('img/taximporterlogo.png') ?>" 
             alt="Logo TaxImporter" 
             style="max-width: 70px; height: auto; 
                    filter: drop-shadow(2px 2px 6px rgba(0,0,0,1.9));">
    </a>
</div>

<div class="container">
    <div class="row justify-content-center align-items-center min-vh-100">
        <div class="col-lg-8 col-xl-6">
            
            <!-- Tarjeta principal de éxito -->
            <div class="card shadow-lg border-success success-animation">
                <div class="card-header bg-success text-white text-center py-4">
                    <div class="mb-3">
                        <i class="bi bi-check-circle-fill" style="font-size: 4rem;"></i>
                    </div>
                    <h2>¡Donación Exitosa!</h2>
                    <p class="mb-0 lead">Tu apoyo significa mucho para nosotros</p>
                </div>
                
                <div class="card-body text-center py-5">
                    <?php if (isset($donacion) && $donacion): ?>
                        <div class="row mb-4">
                            <div class="col-md-4">
                                <div class="border rounded p-3 mb-3 mb-md-0">
                                    <i class="bi bi-currency-dollar text-success h3"></i>
                                    <h4 class="text-success mb-1">$<?= number_format($donacion['monto_ars'], 2) ?></h4>
                                    <small class="text-muted">Monto donado</small>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="border rounded p-3 mb-3 mb-md-0">
                                    <i class="bi bi-calendar-check text-info h3"></i>
                                    <h6 class="mb-1"><?= date('d/m/Y', strtotime($donacion['fecha_donacion'])) ?></h6>
                                    <small class="text-muted">Fecha</small>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="border rounded p-3">
                                    <i class="bi bi-hash text-warning h3"></i>
                                    <h6 class="mb-1">#<?= $donacion['id'] ?></h6>
                                    <small class="text-muted">ID Donación</small>
                                </div>
                            </div>
                        </div>

                        <?php if (isset($payment) && $payment): ?>
                            <div class="alert alert-info">
                                <h6><i class="bi bi-info-circle"></i> Detalles del Pago</h6>
                                <div class="row text-start">
                                    <div class="col-sm-6">
                                        <small><strong>ID MercadoPago:</strong> <?= esc($payment['id']) ?></small>
                                    </div>
                                    <div class="col-sm-6">
                                        <small><strong>Estado:</strong> 
                                            <span class="badge bg-<?= ($estado ?? 'pendiente') === 'aprobado' ? 'success' : 'warning' ?>">
                                                <?= ucfirst($estado ?? 'pendiente') ?>
                                            </span>
                                        </small>
                                    </div>
                                    <?php if (isset($payment['payment_method_id'])): ?>
                                    <div class="col-sm-6">
                                        <small><strong>Método:</strong> <?= ucfirst($payment['payment_method_id']) ?></small>
                                    </div>
                                    <?php endif; ?>
                                    <?php if (isset($payment['date_approved'])): ?>
                                    <div class="col-sm-6">
                                        <small><strong>Aprobado:</strong> <?= date('d/m/Y H:i', strtotime($payment['date_approved'])) ?></small>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>

                    <div class="mb-4">
                        <h4 class="text-success mb-3">🎉 ¡Gracias por tu generosidad!</h4>
                        <p class="lead mb-3">
                            Tu donación nos ayuda a mantener TaxImporter gratuito y seguir desarrollando nuevas funcionalidades.
                        </p>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <div class="border rounded p-3 h-100">
                                    <i class="bi bi-server text-primary h4"></i>
                                    <h6>Mantenimiento del Servidor</h6>
                                    <small class="text-muted">Hosting y infraestructura</small>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="border rounded p-3 h-100">
                                    <i class="bi bi-code-slash text-info h4"></i>
                                    <h6>Desarrollo Continuo</h6>
                                    <small class="text-muted">Nuevas funcionalidades</small>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="border rounded p-3 h-100">
                                    <i class="bi bi-graph-up text-success h4"></i>
                                    <h6>APIs Premium</h6>
                                    <small class="text-muted">Cotizaciones en tiempo real</small>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="border rounded p-3 h-100">
                                    <i class="bi bi-shield-check text-warning h4"></i>
                                    <h6>Seguridad y Actualizaciones</h6>
                                    <small class="text-muted">Mantener todo actualizado</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Acciones -->
                    <div class="d-grid gap-2 d-md-flex justify-content-center">
                        <a href="<?= base_url('historial') ?>" class="btn btn-primary btn-lg">
                            <i class="bi bi-calculator"></i> Seguir Calculando
                        </a>
                        <a href="<?= base_url('donacion') ?>" class="btn btn-outline-secondary btn-lg">
                            <i class="bi bi-heart"></i> Ver Mis Donaciones
                        </a>
                    </div>
                    
                    <?php if (isset($donacion)): ?>
                        <div class="mt-3">
                            <a href="<?= base_url('donacion/ver/' . $donacion['id']) ?>" class="btn btn-sm btn-outline-info">
                                <i class="bi bi-receipt"></i> Ver Comprobante Detallado
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Mensaje adicional -->
            <div class="text-center mt-4">
                <div class="card bg-dark text-light">
                    <div class="card-body">
                        <h6><i class="bi bi-heart text-danger"></i> Un mensaje del equipo</h6>
                        <p class="mb-0">
                            "Cada donación, sin importar el monto, nos motiva a seguir mejorando. 
                            Gracias por ser parte de esta comunidad que apoya las herramientas libres y gratuitas."
                        </p>
                        <small class="text-muted">- Equipo TaxImporter</small>
                    </div>
                </div>
            </div>

            <!-- Compartir en redes sociales -->
            <div class="text-center mt-3">
                <h6 class="text-light mb-2">Comparte tu apoyo:</h6>
                <div class="d-flex justify-content-center gap-2">
                    <button onclick="compartirTwitter()" class="btn btn-sm btn-outline-info">
                        <i class="bi bi-twitter"></i> Twitter
                    </button>
                    <button onclick="compartirWhatsApp()" class="btn btn-sm btn-outline-success">
                        <i class="bi bi-whatsapp"></i> WhatsApp
                    </button>
                    <button onclick="copiarEnlace()" class="btn btn-sm btn-outline-warning">
                        <i class="bi bi-link"></i> Copiar Enlace
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Script para compartir -->
<script>
function compartirTwitter() {
    const texto = "Acabo de donar a @TaxImporter para apoyar esta increíble herramienta gratuita de cálculo de impuestos 🧮💖 #TaxImporter #DonacionesLibres";
    const url = "https://twitter.com/intent/tweet?text=" + encodeURIComponent(texto) + "&url=" + encodeURIComponent(window.location.origin);
    window.open(url, '_blank');
}

function compartirWhatsApp() {
    const texto = "¡Acabo de donar a TaxImporter! 🧮💖\n\nEsta herramienta gratuita me ayuda a calcular impuestos de importación y decidí apoyar su desarrollo.\n\n¡Úsala tú también!";
    const url = "https://wa.me/?text=" + encodeURIComponent(texto + "\n" + window.location.origin);
    window.open(url, '_blank');
}

function copiarEnlace() {
    const enlace = "<?= base_url() ?>";
    if (navigator.clipboard) {
        navigator.clipboard.writeText(enlace).then(() => {
            alert('✅ Enlace copiado al portapapeles');
        });
    } else {
        prompt('Copia este enlace:', enlace);
    }
}

// Auto-redirect después de 30 segundos (opcional)
setTimeout(() => {
    const autoRedirect = confirm('¿Quieres volver al calculadora principal?');
    if (autoRedirect) {
        window.location.href = '<?= base_url('historial') ?>';
    }
}, 30000);
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>