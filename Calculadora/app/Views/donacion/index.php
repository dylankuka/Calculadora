<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Apoyar TaxImporter - Donaciones</title>
    <link rel="stylesheet" href="<?= base_url('css/ind.css') ?>">
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
                    <p class="mb-0">Selecciona un monto y apóyanos con MercadoPago</p>
                </div>
                
                <div class="card-body p-4">
                    <form action="<?= base_url('donacion/crear') ?>" method="post" id="donationForm">
                        <?= csrf_field() ?>
                        
                        <!-- Opciones de monto -->
                        <div class="mb-4">
                            <label class="form-label text-amazon-orange fs-5 fw-bold mb-3">
                                <i class="bi bi-currency-dollar"></i> Elige tu monto de apoyo
                            </label>
                            
                            <div class="row g-3">
                                <div class="col-md-6 col-lg-3">
                                    <div class="donation-option" data-amount="500">
                                        <div class="donation-amount">$500</div>
                                        <div class="donation-description">Un cafecito ☕</div>
                                    </div>
                                </div>
                                <div class="col-md-6 col-lg-3">
                                    <div class="donation-option" data-amount="1000">
                                        <div class="donation-amount">$1.000</div>
                                        <div class="donation-description">Apoyo básico 🤝</div>
                                    </div>
                                </div>
                                <div class="col-md-6 col-lg-3">
                                    <div class="donation-option" data-amount="2500">
                                        <div class="donation-amount">$2.500</div>
                                        <div class="donation-description">Apoyo sólido 💪</div>
                                    </div>
                                </div>
                                <div class="col-md-6 col-lg-3">
                                    <div class="donation-option" data-amount="5000">
                                        <div class="donation-amount">$5.000</div>
                                        <div class="donation-description">Apoyo premium ⭐</div>
                                    </div>
                                </div>
                                <div class="col-md-6 col-lg-4 mx-auto">
                                    <div class="donation-option" data-amount="10000">
                                        <div class="donation-amount">$10.000</div>
                                        <div class="donation-description">Apoyo excepcional 🚀</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Input oculto para el monto -->
                        <input type="hidden" id="selectedAmount" name="monto" value="" required>
                        
                        <!-- Información sobre métodos de pago -->
                        <div class="mb-4">
                            <h6 class="text-amazon-orange mb-2">
                                <i class="bi bi-credit-card"></i> Métodos de Pago Disponibles
                            </h6>
                            <div class="payment-methods">
                                <span class="payment-badge">
                                    <i class="bi bi-credit-card"></i> Tarjetas de Crédito
                                </span>
                                <span class="payment-badge">
                                    <i class="bi bi-credit-card-2-front"></i> Tarjetas de Débito
                                </span>
                                <span class="payment-badge">
                                    <i class="bi bi-wallet2"></i> MercadoPago
                                </span>
                                <span class="payment-badge">
                                    <i class="bi bi-building"></i> Rapipago
                                </span>
                                <span class="payment-badge">
                                    <i class="bi bi-shop"></i> Pago Fácil
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

                        <!-- Botón de donación -->
                        <div class="text-center">
                            <button type="submit" class="btn btn-donate btn-lg" id="donateButton" disabled>
                                <i class="bi bi-heart-fill me-2"></i>
                                <span id="buttonText">Selecciona un monto para continuar</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Historial de donaciones del usuario -->
            <?php if (!empty($mis_donaciones)): ?>
            <div class="amazon-dark-card shadow mb-5 slide-in">
                <div class="card-header bg-secondary text-white py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
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
                <div class="card-body p-0">
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
                                    <td class="text-amazon-light">
                                        <?= date('d/m/Y H:i', strtotime($donacion['fecha_donacion'])) ?>
                                    </td>
                                    <td>
                                        <strong class="text-amazon-orange">$<?= number_format($donacion['monto_ars'], 0, ',', '.') ?></strong>
                                    </td>
                                    <td>
                                        <span class="badge badge-status badge-<?= $donacion['estado'] ?>">
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
            <?php endif; ?>

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

<!-- JavaScript para funcionalidad -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const donationOptions = document.querySelectorAll('.donation-option');
    const selectedAmountInput = document.getElementById('selectedAmount');
    const donateButton = document.getElementById('donateButton');
    const buttonText = document.getElementById('buttonText');

    // Manejar selección de monto
    donationOptions.forEach(option => {
        option.addEventListener('click', function() {
            // Remover selección anterior
            donationOptions.forEach(opt => opt.classList.remove('selected'));
            
            // Seleccionar actual
            this.classList.add('selected');
            
            // Obtener monto
            const amount = this.dataset.amount;
            selectedAmountInput.value = amount;
            
            // Habilitar botón y actualizar texto
            donateButton.disabled = false;
            buttonText.textContent = `Donar $${parseInt(amount).toLocaleString()} ARS con MercadoPago`;
            
            // Efecto visual
            donateButton.classList.add('pulse-effect');
            setTimeout(() => {
                donateButton.classList.remove('pulse-effect');
            }, 1000);
        });
    });

    // Validación del formulario
    document.getElementById('donationForm').addEventListener('submit', function(e) {
        if (!selectedAmountInput.value) {
            e.preventDefault();
            alert('Por favor selecciona un monto antes de continuar.');
            return false;
        }
        
        // Cambiar texto del botón mientras procesa
        donateButton.innerHTML = '<i class="bi bi-hourglass-split me-2"></i>Procesando...';
        donateButton.disabled = true;
    });

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
});
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>