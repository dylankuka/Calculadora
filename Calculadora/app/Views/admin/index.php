<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Panel de Administración - TaxImporter</title>
    <link rel="stylesheet" href="<?= base_url('css/ind.css') ?>">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .admin-card {
            transition: all 0.3s ease;
            border-left: 4px solid #dc3545;
        }
        .admin-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(220, 53, 69, 0.3);
        }
        .stat-icon {
            font-size: 2.5rem;
            opacity: 0.8;
        }
    </style>
</head>
<body class="bg-dark">

<!-- Logo -->
<div class="position-absolute" style="top: 5px; left: 22px; z-index: 1000;">
    <a href="<?= base_url() ?>">
        <img src="<?= base_url('img/taximporterlogo.png') ?>" 
             alt="Logo TaxImporter" 
             style="max-width: 70px; height: auto; 
                    filter: drop-shadow(2px 2px 6px rgba(0,0,0,1.9));">
    </a>
</div>

<!-- Navbar Admin -->
<nav class="navbar navbar-expand-lg navbar-dark card-custom">
    <div class="container">
        <a class="navbar-brand textcolor" href="<?= base_url('admin') ?>">
            <i class="bi bi-speedometer2"></i> Panel de Administración
        </a>
        
        <div class="navbar-nav ms-auto">
            <span class="navbar-text me-3 textcolor">
                👤 <strong><?= esc(session()->get('usuario_nombre')) ?></strong>
                <span class="badge bg-danger ms-1">ADMIN</span>
            </span>
            <a class="btn btn-outline-dark btn-sm me-2" href="<?= base_url('historial') ?>">
                <i class="bi bi-arrow-left textcolor"></i> Volver al Sitio
            </a>
            <a class="btn btn-outline-dark btn-sm" href="<?= base_url('usuario/logout') ?>">
                <i class="bi bi-box-arrow-right textcolor"></i> Salir
            </a>
        </div>
    </div>
</nav>

<div class="container mt-4">
    
    <!-- Mensajes -->
    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <i class="bi bi-check-circle"></i> <?= session()->getFlashdata('success') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card admin-card card-custom2">
                <div class="card-body">
                    <h2 class="text-white">
                        <i class="bi bi-speedometer2 text-danger"></i> Dashboard de Administración
                    </h2>
                    <p class="text-light mb-0">Panel de control y gestión de TaxImporter</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Estadísticas Generales -->
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="card admin-card card-custom2 text-center">
                <div class="card-body">
                    <i class="bi bi-heart-fill stat-icon text-danger"></i>
                    <h3 class="text-white mt-2"><?= $estadisticas['total_donaciones'] ?></h3>
                    <p class="text-light mb-1">Total Donaciones</p>
                    <small class="text-success">
                        <?= $estadisticas['donaciones_aprobadas'] ?> aprobadas
                    </small>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="card admin-card card-custom2 text-center">
                <div class="card-body">
                    <i class="bi bi-currency-dollar stat-icon text-success"></i>
                    <h3 class="text-white mt-2">$<?= number_format($estadisticas['total_recaudado'], 0) ?></h3>
                    <p class="text-light mb-0">Total Recaudado (ARS)</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Menú de Gestión -->
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <a href="<?= base_url('admin/usuarios') ?>" class="text-decoration-none">
                <div class="card admin-card card-custom2 h-100">
                    <div class="card-body text-center">
                        <i class="bi bi-people-fill text-primary" style="font-size: 3rem;"></i>
                        <h5 class="text-white mt-3">Gestión de Usuarios</h5>
                        <p class="text-light">Ver, editar y gestionar usuarios</p>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-md-3 mb-3">
            <a href="<?= base_url('admin/donaciones') ?>" class="text-decoration-none">
                <div class="card admin-card card-custom2 h-100">
                    <div class="card-body text-center">
                        <i class="bi bi-heart-fill text-danger" style="font-size: 3rem;"></i>
                        <h5 class="text-white mt-3">Gestión de Donaciones</h5>
                        <p class="text-light">Ver y administrar donaciones</p>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-md-3 mb-3">
            <a href="<?= base_url('admin/cotizaciones') ?>" class="text-decoration-none">
                <div class="card admin-card card-custom2 h-100">
                    <div class="card-body text-center">
                        <i class="bi bi-currency-exchange text-warning" style="font-size: 3rem;"></i>
                        <h5 class="text-white mt-3">Cotizaciones</h5>
                        <p class="text-light">Gestionar cotizaciones de dólar</p>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-md-3 mb-3">
            <a href="<?= base_url('admin/categorias') ?>" class="text-decoration-none">
                <div class="card admin-card card-custom2 h-100">
                    <div class="card-body text-center">
                        <i class="bi bi-tags-fill text-info" style="font-size: 3rem;"></i>
                        <h5 class="text-white mt-3">Categorías</h5>
                        <p class="text-light">Gestionar aranceles por categoría</p>
                    </div>
                </div>
            </a>
        </div>
    </div>

    <!-- Actividad Reciente -->
    <div class="row">
        <!-- Últimos Usuarios -->
        <div class="col-md-4 mb-3">
            <div class="card admin-card card-custom2 h-100">
                <div class="card-header card-custom">
                    <h6 class="mb-0 textcolor">
                        <i class="bi bi-person-plus"></i> Últimos Usuarios Registrados
                    </h6>
                </div>
                <div class="card-body">
                    <?php if (!empty($actividad['ultimos_usuarios'])): ?>
                        <div class="list-group list-group-flush">
                            <?php foreach ($actividad['ultimos_usuarios'] as $usuario): ?>
                                <div class="list-group-item bg-transparent border-secondary text-light">
                                    <div class="d-flex justify-content-between">
                                        <strong><?= esc($usuario['nombredeusuario']) ?></strong>
                                        <?php if ($usuario['rol'] === 'admin'): ?>
                                            <span class="badge bg-danger">ADMIN</span>
                                        <?php endif; ?>
                                    </div>
                                    <small class="text-muted">
                                        <?= date('d/m/Y H:i', strtotime($usuario['fecha_registro'])) ?>
                                    </small>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p class="text-center text-muted">No hay usuarios recientes</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Últimas Donaciones -->
        <div class="col-md-4 mb-3">
            <div class="card admin-card card-custom2 h-100">
                <div class="card-header card-custom">
                    <h6 class="mb-0 textcolor">
                        <i class="bi bi-heart"></i> Últimas Donaciones
                    </h6>
                </div>
                <div class="card-body">
                    <?php if (!empty($actividad['ultimas_donaciones'])): ?>
                        <div class="list-group list-group-flush">
                            <?php foreach ($actividad['ultimas_donaciones'] as $donacion): ?>
                                <div class="list-group-item bg-transparent border-secondary text-light">
                                    <div class="d-flex justify-content-between">
                                        <span><?= esc($donacion['nombredeusuario']) ?></span>
                                        <strong class="text-warning">$<?= number_format($donacion['monto_ars'], 0) ?></strong>
                                    </div>
                                    <div class="d-flex justify-content-between">
                                        <small class="text-muted">
                                            <?= date('d/m/Y H:i', strtotime($donacion['fecha_donacion'])) ?>
                                        </small>
                                        <span class="badge bg-<?= $donacion['estado'] === 'aprobado' ? 'success' : 'warning' ?>">
                                            <?= ucfirst($donacion['estado']) ?>
                                        </span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p class="text-center text-muted">No hay donaciones recientes</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Últimos Cálculos -->
        <div class="col-md-4 mb-3">
            <div class="card admin-card card-custom2 h-100">
                <div class="card-header card-custom">
                    <h6 class="mb-0 textcolor">
                        <i class="bi bi-calculator"></i> Últimos Cálculos
                    </h6>
                </div>
                <div class="card-body">
                    <?php if (!empty($actividad['ultimos_calculos'])): ?>
                        <div class="list-group list-group-flush">
                            <?php foreach ($actividad['ultimos_calculos'] as $calculo): ?>
                                <div class="list-group-item bg-transparent border-secondary text-light">
                                    <div class="d-flex justify-content-between">
                                        <span><?= esc($calculo['nombredeusuario']) ?></span>
                                        <strong class="text-success">$<?= number_format($calculo['precio_usd'], 0) ?></strong>
                                    </div>
                                    <small class="text-muted d-block">
                                        <?= esc(substr($calculo['nombre_producto'], 0, 40)) ?>...
                                    </small>
                                    <small class="text-muted">
                                        <?= date('d/m/Y H:i', strtotime($calculo['fecha_calculo'])) ?>
                                    </small>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p class="text-center text-muted">No hay cálculos recientes</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Cotizaciones Actuales -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card admin-card card-custom2">
                <div class="card-header card-custom">
                    <h6 class="mb-0 textcolor">
                        <i class="bi bi-currency-exchange"></i> Cotizaciones Actuales
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-4">
                            <h5 class="text-warning">Dólar Tarjeta</h5>
                            <h2 class="text-white">$<?= number_format($estadisticas['ultima_cotizacion']['tarjeta'], 2) ?></h2>
                        </div>
                        <div class="col-md-4">
                            <h5 class="text-info">Dólar MEP</h5>
                            <h2 class="text-white">$<?= number_format($estadisticas['ultima_cotizacion']['mep'], 2) ?></h2>
                        </div>
                        <div class="col-md-4">
                            <h5 class="text-light">Última Actualización</h5>
                            <p class="text-white">
                                <?= date('d/m/Y H:i', strtotime($estadisticas['ultima_cotizacion']['fecha'])) ?>
                            </p>
                            <a href="<?= base_url('admin/cotizaciones/actualizar') ?>" class="btn btn-warning btn-sm">
                                <i class="bi bi-arrow-clockwise"></i> Actualizar Ahora
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>="bi bi-people stat-icon text-primary"></i>
                    <h3 class="text-white mt-2"><?= $estadisticas['total_usuarios'] ?></h3>
                    <p class="text-light mb-1">Total Usuarios</p>
                    <small class="text-success">
                        <?= $estadisticas['usuarios_activos'] ?> activos
                    </small>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="card admin-card card-custom2 text-center">
                <div class="card-body">
                    <i class="bi bi-calculator stat-icon text-warning"></i>
                    <h3 class="text-white mt-2"><?= $estadisticas['total_calculos'] ?></h3>
                    <p class="text-light mb-0">Cálculos Realizados</p>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="card admin-card card-custom2 text-center">
                <div class="card-body">
                    <i class