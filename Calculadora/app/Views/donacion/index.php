<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Donaciones - TaxImporter</title>
    <link rel="stylesheet" href="<?= base_url('css/ind.css') ?>">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="bg-dark">
<div class="position-absolute" style="top: 5px; left: 22px; z-index: 1000;">
    <a href="<?= base_url() ?>">
        <img src="<?= base_url('img/taximporterlogo.png') ?>" 
             alt="Logo TaxImporter" 
             style="max-width: 70px; height: auto; 
                    filter: drop-shadow(2px 2px 6px rgba(0,0,0,1.9));">
    </a>
</div>

<!-- ✅ NAVBAR -->
<nav class="card-custom navbar navbar-expand-lg navbar-dark">
    <div class="container">
        <a class="navbar-brand textcolor" href="<?= base_url('/') ?>">
        </a>
        
        <div class="navbar-nav ms-auto">
            <span class="navbar-text me-3 textcolor">
                👤 <strong><?= esc(session()->get('usuario_nombre')) ?></strong>
            </span>
            <a class="btn btn-outline-secondary btn-sm me-2" href="<?= base_url('/historial') ?>">
                <i class="bi bi-arrow-left textcolor2"></i> Volver al Historial
            </a>
            <a class="btn btn-outline-dark btn-sm" href="<?= base_url('usuario/logout') ?>">
                <i class="bi bi-box-arrow-right textcolor2"></i> Salir
            </a>
        </div>
    </div>
</nav>

<div class="container mt-4">
    
    <!-- ✅ MENSAJES DE ÉXITO/ERROR -->
    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <i class="bi bi-check-circle"></i> <?= session()->getFlashdata('success') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="bi bi-exclamation-triangle"></i> <?= session()->getFlashdata('error') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- ✅ HEADER PRINCIPAL -->
    <div class="row mb-4">
        <div class="col-12 text-center">
            <h1 class="text-light mb-3">🧡 Apoyar a TaxImporter</h1>
            <p class="text-light lead">
                Ayúdanos a mantener y mejorar esta herramienta gratuita para toda la comunidad
            </p>
        </div>
    </div>

    <!-- ✅ ESTADÍSTICAS GENERALES -->
    <?php if (isset($estadisticas) && $estadisticas): ?>
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card card-custom textcolor text-center">
                <div class="card-body">
                    <i class="bi bi-heart-fill text-danger h1"></i>
                    <h4>$<?= number_format($estadisticas['total_recaudado'], 2) ?></h4>
                    <small>Total Recaudado</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card card-custom textcolor text-center">
                <div class="card-body">
                    <i class="bi bi-people-fill text-primary h1"></i>
                    <h4><?= $estadisticas['cantidad_donaciones'] ?></h4>
                    <small>Donaciones Realizadas</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card card-custom textcolor text-center">
                <div class="card-body">
                    <i class="bi bi-graph-up text-success h1"></i>
                    <h4>$<?= number_format($estadisticas['promedio_donacion'], 2) ?></h4>
                    <small>Promedio por Donación</small>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            
            <!-- ✅ FORMULARIO DE DONACIÓN -->
            <div class="card card-custom2 shadow-lg mb-4">
                <div class="card-header card-custom bg-warning text-dark text-center">
                    <h3><i class="bi bi-gift"></i> Realizar Donación</h3>
                    <p class="mb-0">Tu apoyo nos ayuda a seguir desarrollando nuevas funciones</p>
                </div>
                <div class="card-body card-custom2">
                    <form action="<?= base_url('donacion/crear') ?>" method="post" novalidate>
                        <?= csrf_field() ?>

                        <!-- ✅ BOTONES DE MONTO RÁPIDO -->
                        <div class="mb-4">
                            <label class="form-label text-light">
                                <i class="bi bi-currency-dollar"></i> Selecciona un monto o ingresa uno personalizado
                            </label>
                            <div class="d-grid gap-2 d-md-flex justify-content-md-center mb-3">
                                <button type="button" class="btn btn-outline-success flex-fill" onclick="setMonto(500)">$500</button>
                                <button type="button" class="btn btn-outline-success flex-fill" onclick="setMonto(1000)">$1.000</button>
                                <button type="button" class="btn btn-outline-success flex-fill" onclick="setMonto(2500)">$2.500</button>
                                <button type="button" class="btn btn-outline-success flex-fill" onclick="setMonto(5000)">$5.000</button>
                                <button type="button" class="btn btn-outline-success flex-fill" onclick="setMonto(10000)">$10.000</button>
                            </div>
                        </div>
<<<<<<< HEAD

                        <!-- ✅ MONTO PERSONALIZADO -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="monto" class="form-label text-light">
                                    <i class="bi bi-cash-coin"></i> Monto Personalizado (ARS) *
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" 
                                           class="form-control" 
                                           id="monto" 
                                           name="monto" 
                                           min="100" 
                                           max="100000" 
                                           step="0.01"
                                           placeholder="Ingresa el monto"
                                           value="<?= set_value('monto') ?>" 
                                           required>
                                    <span class="input-group-text">ARS</span>
                                </div>
                                <div class="form-text text-light">
                                    Monto mínimo: $100 - Máximo: $100.000
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-light">
                                    <i class="bi bi-credit-card"></i> Métodos de Pago Disponibles
                                </label>
                                <div class="d-flex flex-wrap gap-2">
                                    <span class="badge bg-primary">Tarjeta de Crédito</span>
                                    <span class="badge bg-primary">Tarjeta de Débito</span>
                                    <span class="badge bg-success">MercadoPago</span>
                                    <span class="badge bg-info">Rapipago</span>
                                    <span class="badge bg-warning text-dark">Pago Fácil</span>
                                </div>
                            </div>
                        </div>

                        <!-- ✅ MENSAJE OPCIONAL -->
                        <div class="mb-4">
                            <label for="mensaje" class="form-label text-light">
                                <i class="bi bi-chat-left-text"></i> Mensaje Opcional
                            </label>
                            <textarea class="form-control" 
                                      id="mensaje" 
                                      name="mensaje" 
                                      rows="3" 
                                      maxlength="500"
                                      placeholder="Deja un mensaje de apoyo (opcional)..."><?= set_value('mensaje') ?></textarea>
                            <div class="form-text text-light">
                                Máximo 500 caracteres
                            </div>
                        </div>

=======
>>>>>>> 50fd9707a52edbf8c23b8fb56d8bc9ebff2db1f4
                        <!-- ✅ INFORMACIÓN SOBRE EL USO DE FONDOS -->
                        <div class="card bg-info text-dark mb-4">
                            <div class="card-body">
                                <h6><i class="bi bi-info-circle"></i> ¿Para qué usamos las donaciones?</h6>
                                <ul class="mb-0">
                                    <li>🖥️ Mantenimiento del servidor y hosting</li>
                                    <li>🔄 Actualizaciones de cotizaciones en tiempo real</li>
                                    <li>🆕 Desarrollo de nuevas funcionalidades</li>
                                    <li>🛠️ Mejoras en el sistema y corrección de errores</li>
                                    <li>📊 APIs premium para datos más precisos</li>
                                </ul>
                            </div>
                        </div>

                        <!-- ✅ BOTÓN DE DONACIÓN -->
                        <div class="d-grid">
                            <button type="submit" class="btn btn-success btn-lg">
                                <i class="bi bi-heart-fill"></i> Donar con MercadoPago
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- ✅ HISTORIAL DE MIS DONACIONES -->
            <?php if (!empty($mis_donaciones)): ?>
            <div class="card card-custom2 shadow">
                <div class="card-header card-custom bg-secondary textcolor">
                    <h5><i class="bi bi-clock-history"></i> Mis Donaciones</h5>
                    <?php if (isset($resumen) && $resumen): ?>
                        <small>
                            Total donado: $<?= number_format($resumen['total_donado'] ?? 0, 2) ?> ARS 
                            (<?= $resumen['total_donaciones'] ?? 0 ?> donaciones)
                        </small>
                    <?php endif; ?>
                </div>
                <div class="card-body card-custom2">
                    <div class="table-responsive">
                        <table class="table table-dark table-hover">
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
                                        <?= date('d/m/Y H:i', strtotime($donacion['fecha_donacion'])) ?>
                                    </td>
                                    <td>
                                        <strong>$<?= number_format($donacion['monto_ars'], 2) ?></strong>
                                    </td>
                                    <td>
                                        <?php 
                                        $badges = [
                                            'pendiente' => 'warning',
                                            'aprobado' => 'success', 
                                            'rechazado' => 'danger',
                                            'cancelado' => 'secondary'
                                        ];
                                        $badge = $badges[$donacion['estado']] ?? 'secondary';
                                        ?>
                                        <span class="badge bg-<?= $badge ?>">
                                            <?= ucfirst($donacion['estado']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="<?= base_url('donacion/ver/' . $donacion['id']) ?>" 
                                           class="btn btn-sm btn-outline-info">
                                            <i class="bi bi-eye"></i> Ver
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

        </div>
    </div>

    <!-- ✅ MENSAJE DE AGRADECIMIENTO -->
<<<<<<< HEAD
    <div class="d-flex justify-content-center align-items-center mt-1" style="min-height: 300px;">
            <div class="texto-transparente p-4 text-center">
                <h3 class="text-light">🙏 ¡Gracias por tu apoyo!</h3>
                <p class="textcolor1">
                    TaxImporter es un proyecto independiente creado por estudiantes.<br>
                    Cada donación nos ayuda a mantener el servicio gratuito y mejorarlo continuamente.
                </p>
=======
<div class="d-flex justify-content-center align-items-center mt-1" style="min-height: 300px;">
    <div class="texto-transparente p-4 text-center">
        <h3 class="textcolor">🙏 ¡Gracias por tu apoyo!</h3>
        <p class="textcolor">
            TaxImporter es un proyecto independiente creado por estudiantes.<br>
            Cada donación nos ayuda a mantener el servicio gratuito y mejorarlo continuamente.
        </p>
>>>>>>> 50fd9707a52edbf8c23b8fb56d8bc9ebff2db1f4
            </div>
        </div>
    </div>
</div>

<!-- ✅ JAVASCRIPT PARA FUNCIONALIDAD -->
<script>
function setMonto(monto) {
    document.getElementById('monto').value = monto;
    
    // Efecto visual en el botón seleccionado
    document.querySelectorAll('.btn-outline-success').forEach(btn => {
        btn.classList.remove('active');
    });
    
    event.target.classList.add('active');
}

// Validación en tiempo real del monto
document.getElementById('monto').addEventListener('input', function() {
    const monto = parseFloat(this.value) || 0;
    const submitBtn = document.querySelector('button[type="submit"]');
    
    if (monto < 100) {
        this.classList.add('is-invalid');
        submitBtn.disabled = true;
        this.setCustomValidity('El monto mínimo es $100');
    } else if (monto > 100000) {
        this.classList.add('is-invalid');
        submitBtn.disabled = true;
        this.setCustomValidity('El monto máximo es $100.000');
    } else {
        this.classList.remove('is-invalid');
        this.classList.add('is-valid');
        submitBtn.disabled = false;
        this.setCustomValidity('');
    }
});

// Contador de caracteres para el mensaje
document.getElementById('mensaje').addEventListener('input', function() {
    const restantes = 500 - this.value.length;
    let texto = this.nextElementSibling;
    
    if (restantes < 0) {
        texto.classList.add('text-danger');
        texto.textContent = `Excede por ${Math.abs(restantes)} caracteres`;
    } else {
        texto.classList.remove('text-danger');
        texto.textContent = `${restantes} caracteres restantes`;
    }
});
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>