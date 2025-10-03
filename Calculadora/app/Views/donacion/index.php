<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Apoyar TaxImporter - Donaciones</title>
    <link rel="stylesheet" href="<?= base_url('css/ind.css') ?>">
    <link rel="stylesheet" href="<?= base_url('css/donacion.css') ?>">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
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

<!-- Navbar estilo Amazon -->
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
            <a class="btn btn-outline-light btn-sm me-2" href="<?= base_url('/historial') ?>">
                <i class="bi bi-arrow-left"></i> Volver al Historial
            </a>
            <a class="btn btn-outline-danger btn-sm" href="<?= base_url('usuario/logout') ?>">
                <i class="bi bi-box-arrow-right"></i> Salir
            </a>
        </div>
    </div>
</nav>

<div class="container mt-4">
    
    <!-- Mensajes de estado -->
    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success alert-dismissible fade show slide-in">
            <i class="bi bi-check-circle"></i> <?= session()->getFlashdata('success') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show slide-in">
            <i class="bi bi-exclamation-triangle"></i> <?= session()->getFlashdata('error') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (isset($error)): ?>
        <div class="alert alert-warning alert-dismissible fade show slide-in">
            <i class="bi bi-exclamation-triangle"></i> <?= $error ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Header principal -->
    <div class="row mb-5">
        <div class="col-12 text-center slide-in">
            <h1 class="text-amazon-orange mb-3 fw-bold">
                <i class="bi bi-heart-fill pulse-effect"></i> Apoyar TaxImporter
            </h1>
            <p class="text-amazon-light lead mb-0">
                Ayúdanos a mantener esta herramienta <strong>gratuita</strong> y en constante mejora
            </p>
        </div>
    </div>

    <!-- Estadísticas generales -->
    <?php if (isset($estadisticas) && $estadisticas): ?>
    <div class="row mb-5 slide-in">
        <div class="col-md-4 mb-3">
            <div class="stats-card text-center p-4">
                <div class="stats-icon">
                    <i class="bi bi-heart-fill text-danger"></i>
                </div>
                <div class="stats-number">$<?= number_format($estadisticas['total_recaudado'], 0, ',', '.') ?></div>
                <div class="stats-label">Total Recaudado</div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="stats-card text-center p-4">
                <div class="stats-icon">
                    <i class="bi bi-people-fill text-primary"></i>
                </div>
                <div class="stats-number"><?= $estadisticas['cantidad_donaciones'] ?></div>
                <div class="stats-label">Donaciones Realizadas</div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="stats-card text-center p-4">
                <div class="stats-icon">
                    <i class="bi bi-graph-up text-success"></i>
                </div>
                <div class="stats-number">$<?= number_format($estadisticas['promedio_donacion'], 0, ',', '.') ?></div>
                <div class="stats-label">Promedio por Donación</div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <div class="row justify-content-center">
        <div class="col-xl-8 col-lg-10">
            
                <!-- Formulario de donación principal -->
            <div class="amazon-dark-card shadow-lg mb-5 slide-in">
                <div class="amazon-header text-center py-4">
                    <h3 class="mb-2 fw-bold">
                        <i class="bi bi-gift"></i> Realizar Donación
                    </h3>
                    <p class="mb-0">Selecciona un monto y serás redirigido a MercadoPago automáticamente</p>
                </div>
                
                <div class="card-body p-4">
                    <!-- Opciones de monto con enlaces directos -->
                    <div class="mb-4">
                        <label class="form-label text-amazon-orange fs-5 fw-bold mb-3">
                            <i class="bi bi-currency-dollar"></i> Elige tu monto de apoyo
                        </label>
                        
                        <div class="row g-3">
                            <div class="col-md-6 col-lg-3">
                                <a href="<?= base_url('donacion/checkout/500') ?>" class="text-decoration-none">
                                    <div class="donation-option">
                                        <div class="donation-amount">$500</div>
                                        <div class="donation-description">Un cafecito ☕</div>
                                        <small class="text-amazon-light mt-2 d-block">
                                            <i class="bi bi-arrow-right-circle"></i> Clic para donar
                                        </small>
                                    </div>
                                </a>
                            </div>
                            <div class="col-md-6 col-lg-3">
                                <a href="<?= base_url('donacion/checkout/1000') ?>" class="text-decoration-none">
                                    <div class="donation-option">
                                        <div class="donation-amount">$1.000</div>
                                        <div class="donation-description">Apoyo básico 🤝</div>
                                        <small class="text-amazon-light mt-2 d-block">
                                            <i class="bi bi-arrow-right-circle"></i> Clic para donar
                                        </small>
                                    </div>
                                </a>
                            </div>
                            <div class="col-md-6 col-lg-3">
                                <a href="<?= base_url('donacion/checkout/2500') ?>" class="text-decoration-none">
                                    <div class="donation-option">
                                        <div class="donation-amount">$2.500</div>
                                        <div class="donation-description">Apoyo sólido 💪</div>
                                        <small class="text-amazon-light mt-2 d-block">
                                            <i class="bi bi-arrow-right-circle"></i> Clic para donar
                                        </small>
                                    </div>
                                </a>
                            </div>
                            <div class="col-md-6 col-lg-3">
                                <a href="<?= base_url('donacion/checkout/5000') ?>" class="text-decoration-none">
                                    <div class="donation-option">
                                        <div class="donation-amount">$5.000</div>
                                        <div class="donation-description">Apoyo premium ⭐</div>
                                        <small class="text-amazon-light mt-2 d-block">
                                            <i class="bi bi-arrow-right-circle"></i> Clic para donar
                                        </small>
                                    </div>
                                </a>
                            </div>
                            <div class="col-md-8 col-lg-6 mx-auto">
                                <a href="<?= base_url('donacion/checkout/10000') ?>" class="text-decoration-none">
                                    <div class="donation-option">
                                        <div class="donation-amount">$10.000</div>
                                        <div class="donation-description">Apoyo excepcional 🚀</div>
                                        <small class="text-amazon-light mt-2 d-block">
                                            <i class="bi bi-arrow-right-circle"></i> Clic para donar
                                        </small>
                                    </div>
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Información sobre métodos de pago -->
                    <div class="mb-4">
                        <h6 class="text-amazon-orange mb-2">
                            <i class="bi bi-credit-card"></i> Métodos de Pago Disponibles en MercadoPago
                        </h6>
                        <div class="payment-methods">
                            <span class="payment-badge">
                                <i class="bi bi-credit-card"></i> Tarjetas de Crédito
                            </span>
                            <span class="payment-badge">
                                <i class="bi bi-credit-card-2-front"></i> Tarjetas de Débito
                            </span>
                            <span class="payment-badge">
                                <i class="bi bi-wallet2"></i> Dinero en Cuenta MP
                            </span>
                            <span class="payment-badge">
                                <i class="bi bi-building"></i> Rapipago
                            </span>
                            <span class="payment-badge">
                                <i class="bi bi-shop"></i> Pago Fácil
                            </span>
                            <span class="payment-badge">
                                <i class="bi bi-bank"></i> Transferencia Bancaria
                            </span>
                        </div>
                    </div>

                    <!-- Información sobre el uso de fondos -->
                    <div class="funds-usage mb-4">
                        <h6 class="mb-3">
                            <i class="bi bi-info-circle"></i> ¿Para qué usamos las donaciones?
                        </h6>
                        <div class="row">
                            <div class="col-md-6">
                                <ul class="mb-0">
                                    <li><i class="bi bi-server"></i> Hosting y mantenimiento del servidor</li>
                                    <li><i class="bi bi-arrow-clockwise"></i> APIs de cotizaciones en tiempo real</li>
                                    <li><i class="bi bi-plus-circle"></i> Desarrollo de nuevas funcionalidades</li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <ul class="mb-0">
                                    <li><i class="bi bi-tools"></i> Mejoras y corrección de errores</li>
                                    <li><i class="bi bi-shield-check"></i> Seguridad y actualizaciones</li>
                                    <li><i class="bi bi-graph-up"></i> APIs premium para mayor precisión</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- Nota informativa -->
                    <div class="text-center">
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle"></i> 
                            <strong>¡Es muy simple!</strong> Haz clic en cualquier monto y serás redirigido automáticamente al checkout seguro de MercadoPago.
                        </div>
                    </div>
                </div>
            </div>

<!-- Historial de donaciones del usuario -->
<style>
/* Card principal con bordes redondeados */
.amazon-dark-card.shadow.mb-5.slide-in {
    background: #1b2329 !important;
    border: 1px solid #2d3a45;
    border-radius: 12px !important;
    overflow: hidden;
}

/* Header oscuro con bordes redondeados superiores */
.amazon-dark-card .card-header {
    background-color: #1a1a1a !important; /* Gris muy oscuro, NO naranja */
    color: #ffffff !important;
    border-radius: 12px 12px 0 0 !important;
    border-bottom: 2px solid #ff9900;
    padding: 15px 20px;
}

/* Tabla completa oscura */
.table-dark-custom {
    background-color: #1a1a1a !important;
    color: #eaeaea !important;
    border: none;
    margin-bottom: 0;
    width: 100%;
}

/* Header de la tabla - NEGRO OSCURO */
.table-dark-custom thead {
    background-color: #0a0a0a !important;
}

.table-dark-custom thead th {
    background-color: #0a0a0a !important; /* Negro muy oscuro */
    color: #ff9900 !important;
    border-bottom: 2px solid #333333;
    padding: 15px 12px;
    font-weight: 600;
    text-transform: uppercase;
    font-size: 0.85rem;
    letter-spacing: 0.5px;
    border-top: none;
}

.table-dark-custom thead tr {
    background-color: #0a0a0a !important;
}

/* Filas del body - GRIS OSCURO (no blanco) */
.table-dark-custom tbody {
    background-color: #1a1a1a !important;
}

.table-dark-custom tbody tr {
    background-color: #2a2a2a !important; /* Gris medio-oscuro */
    color: #ffffff !important; /* Texto BLANCO para que se lea */
    border-bottom: 1px solid #1a1a1a;
    transition: all 0.2s ease;
}

/* Filas alternas (efecto zebra) */
.table-dark-custom tbody tr:nth-child(odd) {
    background-color: #2a2a2a !important; /* Gris medio-oscuro */
}

.table-dark-custom tbody tr:nth-child(even) {
    background-color: #333333 !important; /* Un poco más claro */
}

/* Hover en filas */
.table-dark-custom tbody tr:hover {
    background-color: #3d3d3d !important; /* Más claro al hacer hover */
    transform: translateX(2px);
    box-shadow: 0 2px 8px rgba(255, 153, 0, 0.2);
}

/* Celdas - TEXTO BLANCO */
.table-dark-custom tbody td {
    padding: 15px 12px;
    vertical-align: middle;
    color: #ffffff !important; /* Texto BLANCO */
    border: none;
    background-color: transparent !important;
}

/* Card body oscuro */
.card-body.card-custom1 {
    background-color: #1a1a1a !important;
    padding: 0 !important;
}

/* Container de tabla */
.table-responsive {
    background-color: #1a1a1a !important;
    border-radius: 0 0 12px 12px;
    overflow: hidden;
}

/* Badges de estado mejorados */
.badge-status {
    padding: 8px 14px;
    font-size: 0.85rem;
    font-weight: 600;
    border-radius: 20px;
    display: inline-flex;
    align-items: center;
    gap: 5px;
}

.badge-pendiente {
    background-color: #ffc107 !important;
    color: #000 !important;
    box-shadow: 0 2px 5px rgba(255, 193, 7, 0.3);
}

.badge-aprobado {
    background-color: #28a745 !important;
    color: #fff !important;
    box-shadow: 0 2px 5px rgba(40, 167, 69, 0.3);
}

.badge-rechazado {
    background-color: #dc3545 !important;
    color: #fff !important;
    box-shadow: 0 2px 5px rgba(220, 53, 69, 0.3);
}

.badge-cancelado {
    background-color: #6c757d !important;
    color: #fff !important;
    box-shadow: 0 2px 5px rgba(108, 117, 125, 0.3);
}

/* Botón Ver Detalles mejorado */
.btn-outline-info {
    border-color: #17a2b8 !important;
    color: #17a2b8 !important;
    background-color: transparent !important;
    transition: all 0.2s ease;
}

.btn-outline-info:hover {
    background-color: #17a2b8 !important;
    border-color: #17a2b8 !important;
    color: #fff !important;
    transform: translateY(-1px);
    box-shadow: 0 3px 8px rgba(23, 162, 184, 0.3);
}

/* Texto naranja de Amazon */
.text-amazon-orange {
    color: #ff9900 !important;
}

.text-amazon-light {
    color: #cccccc !important;
}

/* Icono del calendario */
.bi-calendar3.text-amazon-orange {
    color: #ff9900 !important;
    margin-right: 5px;
}

/* Texto de fecha en blanco */
.text-light {
    color: #ffffff !important;
}

/* Pequeño texto ARS */
.text-muted {
    color: #999999 !important;
}
</style>

<div class="amazon-dark-card shadow mb-5 slide-in">
    <!-- Header con bordes redondeados -->
    <div class="card-header py-3">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="mb-0 text-white">
                <i class="bi bi-clock-history"></i> Mis Donaciones
            </h5>
            <?php if (isset($resumen) && $resumen): ?>
                <small class="text-amazon-light">
                    Total donado: <strong class="text-amazon-orange">$<?= number_format($resumen['total_donado'] ?? 0, 0, ',', '.') ?></strong> 
                    (<?= $resumen['total_donaciones'] ?? 0 ?> donaciones)
                </small>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Tabla -->
    <div class="card-body card-custom1 p-0">
        <div class="table-responsive">
            <table class="table table-dark-custom mb-0">
                <thead>
                    <tr>
                        <th><i class="bi bi-calendar"></i> Fecha</th>
                        <th><i class="bi bi-currency-dollar"></i> Monto</th>
                        <th><i class="bi bi-check-circle"></i> Estado</th>
                        <th><i class="bi bi-gear"></i> Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($mis_donaciones as $donacion): ?>
                    <tr>
                        <td>
                            <i class="bi bi-calendar3 text-amazon-orange"></i>
                            <span class="text-light">
                                <?= date('d/m/Y H:i', strtotime($donacion['fecha_donacion'])) ?>
                            </span>
                        </td>
                        <td>
                            <strong class="text-amazon-orange" style="font-size: 1.1rem;">
                                $<?= number_format($donacion['monto_ars'], 0, ',', '.') ?>
                            </strong>
                            <small class="text-muted d-block">ARS</small>
                        </td>
                        <td>
                            <?php
                            $estadoIcons = [
                                'pendiente' => 'clock',
                                'aprobado' => 'check-circle-fill',
                                'rechazado' => 'x-circle-fill',
                                'cancelado' => 'dash-circle-fill'
                            ];
                            $icon = $estadoIcons[$donacion['estado']] ?? 'question-circle';
                            ?>
                            <span class="badge badge-status badge-<?= $donacion['estado'] ?>">
                                <i class="bi bi-<?= $icon ?>"></i>
                                <?= ucfirst($donacion['estado']) ?>
                            </span>
                        </td>
                        <td>
                            <a href="<?= base_url('donacion/ver/' . $donacion['id']) ?>" 
                               class="btn btn-sm btn-outline-info">
                                <i class="bi bi-eye"></i> Ver Detalles
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
            <!-- Mensaje de agradecimiento -->
            <div class="gratitude-section text-center slide-in">
                <h3 class="gratitude-title">
                    <i class="bi bi-heart-fill"></i> ¡Gracias por tu apoyo!
                </h3>
                <p class="gratitude-text mb-4">
                    <strong>TaxImporter</strong> es un proyecto independiente desarrollado con pasión para ayudar a la comunidad. 
                    Cada donación, sin importar el monto, nos permite mantener el servicio <strong>completamente gratuito</strong> 
                    y seguir agregando nuevas funcionalidades.
                </p>
                <div class="row text-center">
                    <div class="col-md-3 col-6 mb-3">
                        <div class="feature-icon text-amazon-orange">
                            <i class="bi bi-code-slash h3 mb-0"></i>
                        </div>
                        <h6 class="text-white">Código Abierto</h6>
                        <small class="text-amazon-light">Transparente y confiable</small>
                    </div>
                    <div class="col-md-3 col-6 mb-3">
                        <div class="feature-icon text-amazon-orange">
                            <i class="bi bi-shield-check h3 mb-0"></i>
                        </div>
                        <h6 class="text-white">Totalmente Seguro</h6>
                        <small class="text-amazon-light">Tus datos protegidos</small>
                    </div>
                    <div class="col-md-3 col-6 mb-3">
                        <div class="feature-icon text-amazon-orange">
                            <i class="bi bi-lightning-charge h3 mb-0"></i>
                        </div>
                        <h6 class="text-white">Siempre Actualizado</h6>
                        <small class="text-amazon-light">Con las últimas regulaciones</small>
                    </div>
                    <div class="col-md-3 col-6 mb-3">
                        <div class="feature-icon text-amazon-orange">
                            <i class="bi bi-people h3 mb-0"></i>
                        </div>
                        <h6 class="text-white">Para la Comunidad</h6>
                        <small class="text-amazon-light">Hecho por y para usuarios</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- JavaScript simplificado para checkout directo -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-dismiss alerts después de 8 segundos
    setTimeout(() => {
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(alert => {
            const bsAlert = new bootstrap.Alert(alert);
            if (bsAlert) {
                bsAlert.close();
            }
        });
    }, 8000);

    // Efecto hover mejorado para las opciones de donación
    const donationOptions = document.querySelectorAll('.donation-option');
    donationOptions.forEach(option => {
        option.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-3px) scale(1.02)';
        });
        
        option.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0) scale(1)';
        });

        // Efecto de clic
        option.addEventListener('click', function() {
            // Mostrar feedback visual
            this.style.transform = 'scale(0.98)';
            setTimeout(() => {
                this.style.transform = 'scale(1.02)';
            }, 100);

            // Cambiar cursor a loading
            document.body.style.cursor = 'wait';
            
            // Mostrar mensaje de redirección
            const amount = this.querySelector('.donation-amount').textContent;
            
            // Crear overlay de carga
            const overlay = document.createElement('div');
            overlay.innerHTML = `
                <div style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; 
                           background: rgba(0,0,0,0.8); z-index: 9999; 
                           display: flex; align-items: center; justify-content: center;">
                    <div class="text-center text-white">
                        <div class="spinner-border text-warning mb-3" role="status"></div>
                        <h4>Preparando tu donación de ${amount}</h4>
                        <p>Serás redirigido a MercadoPago en unos segundos...</p>
                    </div>
                </div>
            `;
            document.body.appendChild(overlay);

            // Remover overlay después de 10 segundos (por si falla la redirección)
            setTimeout(() => {
                if (document.body.contains(overlay)) {
                    overlay.remove();
                    document.body.style.cursor = 'default';
                }
            }, 10000);
        });
    });
});
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>