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
        .nav-tabs .nav-link {
            color: #6c757d;
            border: none;
            border-bottom: 3px solid transparent;
        }
        .nav-tabs .nav-link:hover {
            border-bottom-color: #dc3545;
            color: #dc3545;
        }
        .nav-tabs .nav-link.active {
            color: #dc3545;
            border-bottom-color: #dc3545;
            background: transparent;
            font-weight: bold;
        }
        .badge-usuario { background: #6c757d; }
        .badge-admin { background: #dc3545; }
        .table-dark tbody tr:hover {
            background-color: rgba(220, 53, 69, 0.1);
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
    <div class="container-fluid">
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

<div class="container-fluid mt-4">
    
    <!-- Mensajes -->
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

    <!-- Header con estadísticas generales -->
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="card card-custom2 text-center">
                <div class="card-body">
                    <i class="bi bi-people stat-icon text-primary"></i>
                    <h3 class="text-dark mt-2"><?= $estadisticas['total_usuarios'] ?></h3>
                    <p class="text-dark mb-1">Total Usuarios</p>
                    <small class="text-success">
                        <?= $estadisticas['usuarios_activos'] ?> activos
                    </small>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="card card-custom2 text-center">
                <div class="card-body">
                    <i class="bi bi-calculator stat-icon text-warning"></i>
                    <h3 class="text-dark mt-2"><?= $estadisticas['total_calculos'] ?></h3>
                    <p class="text-dark mb-0">Cálculos Realizados</p>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="card card-custom2 text-center">
                <div class="card-body">
                    <i class="bi bi-heart-fill stat-icon text-danger"></i>
                    <h3 class="text-dark mt-2"><?= $estadisticas['total_donaciones'] ?></h3>
                    <p class="text-dark mb-1">Total Donaciones</p>
                    <small class="text-success">
                        <?= $estadisticas['donaciones_aprobadas'] ?> aprobadas
                    </small>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="card card-custom2 text-center">
                <div class="card-body">
                    <i class="bi bi-currency-dollar stat-icon text-success"></i>
                    <h3 class="text-dark mt-2">$<?= number_format($estadisticas['total_recaudado'], 0) ?></h3>
                    <p class="text-dark mb-0">Recaudado (ARS)</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Pestañas de navegación -->
    <div class="card card-custom2">
        <div class="card-header card-custom5">
            <ul class="nav nav-tabs card-header-tabs" id="adminTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="dashboard-tab" data-bs-toggle="tab" 
                            data-bs-target="#dashboard" type="button" role="tab">
                        <i class="bi bi-speedometer2"></i> Dashboard
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="usuarios-tab" data-bs-toggle="tab" 
                            data-bs-target="#usuarios" type="button" role="tab">
                        <i class="bi bi-people"></i> Usuarios
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="donaciones-tab" data-bs-toggle="tab" 
                            data-bs-target="#donaciones" type="button" role="tab">
                        <i class="bi bi-heart"></i> Donaciones
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="cotizaciones-tab" data-bs-toggle="tab" 
                            data-bs-target="#cotizaciones" type="button" role="tab">
                        <i class="bi bi-currency-exchange"></i> Cotizaciones
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="categorias-tab" data-bs-toggle="tab" 
                            data-bs-target="#categorias" type="button" role="tab">
                        <i class="bi bi-tags"></i> Categorías
                    </button>
                </li>
            </ul>
        </div>
        
        <div class="card-body">
            <div class="tab-content" id="adminTabsContent">
                
                <!-- TAB 1: DASHBOARD -->
                <div class="tab-pane fade show active" id="dashboard" role="tabpanel">
                    <h4 class="text-dark mb-4">
                        <i class="bi bi-graph-up"></i> Actividad Reciente
                    </h4>
                    
                    <div class="row">
                        <!-- Últimos Usuarios -->
                        <div class="col-md-4 mb-3">
                            <div class="card bg-dark border-secondary h-100">
                                <div class="card-header bg-primary text-white">
                                    <i class="bi bi-person-plus"></i> Últimos Usuarios
                                </div>
                                <div class="card-body">
                                    <?php if (!empty($actividad['ultimos_usuarios'])): ?>
                                        <div class="list-group list-group-flush">
                                            <?php foreach ($actividad['ultimos_usuarios'] as $usuario): ?>
                                                <div class="list-group-item bg-transparent border-secondary text-light">
                                                    <div class="d-flex justify-content-between">
                                                        <strong><?= esc($usuario['nombredeusuario']) ?></strong>
                                                        <span class="badge badge-<?= $usuario['rol'] === 'admin' ? 'admin' : 'usuario' ?>">
                                                            <?= strtoupper($usuario['rol']) ?>
                                                        </span>
                                                    </div>
                                                    <small class="text-light">
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
                            <div class="card bg-dark border-secondary h-100">
                                <div class="card-header bg-danger text-white">
                                    <i class="bi bi-heart"></i> Últimas Donaciones
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
                                                        <small class="text-light">
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
                            <div class="card bg-dark border-secondary h-100">
                                <div class="card-header bg-warning text-dark">
                                    <i class="bi bi-calculator"></i> Últimos Cálculos
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
                                                    <small class="text-light d-block">
                                                        <?= esc(substr($calculo['nombre_producto'], 0, 40)) ?>...
                                                    </small>
                                                    <small class="text-light">
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
                            <div class="card bg-dark border-secondary">
                                <div class="card-header bg-info text-white">
                                    <i class="bi bi-currency-exchange"></i> Cotizaciones Actuales
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
                                            <button class="btn btn-warning btn-sm" onclick="actualizarCotizaciones()">
                                                <i class="bi bi-arrow-clockwise"></i> Actualizar Ahora
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- TAB 2: USUARIOS -->
                <div class="tab-pane fade" id="usuarios" role="tabpanel">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h4 class="text-white mb-0">
                            <i class="bi bi-people-fill"></i> Gestión de Usuarios
                        </h4>
                        
                        <!-- Filtros -->
                        <form method="GET" class="d-flex gap-2">
                            <input type="hidden" name="tab" value="usuarios">
                            <input type="text" class="form-control form-control-sm" name="buscar" 
                                   placeholder="Buscar..." value="<?= esc($busqueda ?? '') ?>" style="width: 200px;">
                            <select class="form-select form-select-sm" name="rol" style="width: 150px;">
                                <option value="">Todos</option>
                                <option value="admin" <?= ($rol_filtro ?? '') === 'admin' ? 'selected' : '' ?>>Admins</option>
                                <option value="usuario" <?= ($rol_filtro ?? '') === 'usuario' ? 'selected' : '' ?>>Usuarios</option>
                            </select>
                            <button type="submit" class="btn btn-primary btn-sm">
                                <i class="bi bi-search"></i>
                            </button>
                        </form>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-dark table-hover">
                            <thead class="table-secondary">
                                <tr>
                                    <th>ID</th>
                                    <th>Usuario</th>
                                    <th>Email</th>
                                    <th>Rol</th>
                                    <th>Cálculos</th>
                                    <th>Donaciones</th>
                                    <th>Registro</th>
                                    <th>Estado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($usuarios)): ?>
                                    <tr>
                                        <td colspan="9" class="text-center text-muted py-4">
                                            No se encontraron usuarios
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($usuarios as $usuario): ?>
                                        <tr>
                                            <td><?= $usuario['id'] ?></td>
                                            <td>
                                                <strong><?= esc($usuario['nombredeusuario']) ?></strong>
                                                <?php if ($usuario['id'] == session()->get('usuario_id')): ?>
                                                    <span class="badge bg-info">Tú</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?= esc($usuario['email']) ?></td>
                                            <td>
                                                <span class="badge badge-<?= $usuario['rol'] === 'admin' ? 'admin' : 'usuario' ?>">
                                                    <?= strtoupper($usuario['rol']) ?>
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge bg-primary"><?= $usuario['total_calculos'] ?></span>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge bg-warning"><?= $usuario['total_donaciones'] ?></span>
                                            </td>
                                            <td>
                                                <small><?= date('d/m/Y', strtotime($usuario['fecha_registro'])) ?></small>
                                            </td>
                                            <td>
                                                <?php if ($usuario['activo']): ?>
                                                    <span class="badge bg-success">Activo</span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary">Inactivo</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <button class="btn btn-outline-warning" 
                                                            onclick="cambiarRol(<?= $usuario['id'] ?>, '<?= esc($usuario['nombredeusuario']) ?>', '<?= $usuario['rol'] ?>')"
                                                            <?= $usuario['id'] == session()->get('usuario_id') ? 'disabled' : '' ?>>
                                                        <i class="bi bi-shield"></i>
                                                    </button>
                                                    
                                                    <a href="<?= base_url('admin/usuarios/toggle/' . $usuario['id']) ?>" 
                                                       class="btn btn-outline-<?= $usuario['activo'] ? 'danger' : 'success' ?>"
                                                       onclick="return confirm('¿Estás seguro?')"
                                                       <?= $usuario['id'] == session()->get('usuario_id') ? 'disabled' : '' ?>>
                                                        <i class="bi bi-power"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- TAB 3: DONACIONES -->
                <div class="tab-pane fade" id="donaciones" role="tabpanel">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h4 class="text-dark mb-0">
                            <i class="bi bi-heart-fill"></i> Gestión de Donaciones
                        </h4>
                        
                        <!-- Filtros -->
                        <form method="GET" class="d-flex gap-2">
                            <input type="hidden" name="tab" value="donaciones">
                            <input type="text" class="form-control form-control-sm" name="buscar" 
                                   placeholder="Buscar usuario..." value="<?= esc($busqueda ?? '') ?>" style="width: 200px;">
                            <select class="form-select form-select-sm" name="estado" style="width: 150px;">
                                <option value="">Todos</option>
                                <option value="aprobado" <?= ($estado_filtro ?? '') === 'aprobado' ? 'selected' : '' ?>>Aprobadas</option>
                                <option value="pendiente" <?= ($estado_filtro ?? '') === 'pendiente' ? 'selected' : '' ?>>Pendientes</option>
                                <option value="rechazado" <?= ($estado_filtro ?? '') === 'rechazado' ? 'selected' : '' ?>>Rechazadas</option>
                            </select>
                            <button type="submit" class="btn btn-primary btn-sm">
                                <i class="bi bi-search"></i>
                            </button>
                        </form>
                    </div>

                    <!-- Estadísticas de donaciones -->
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <div class="card bg-dark border-secondary text-center">
                                <div class="card-body">
                                    <h5 class="text-success"><?= $estadisticas_donaciones['aprobadas'] ?></h5>
                                    <small class="text-light">Aprobadas</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-dark border-secondary text-center">
                                <div class="card-body">
                                    <h5 class="text-warning"><?= $estadisticas_donaciones['pendientes'] ?></h5>
                                    <small class="text-light">Pendientes</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-dark border-secondary text-center">
                                <div class="card-body">
                                    <h5 class="text-danger"><?= $estadisticas_donaciones['rechazadas'] ?></h5>
                                    <small class="text-light">Rechazadas</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-dark border-secondary text-center">
                                <div class="card-body">
                                    <h5 class="text-info">$<?= number_format($estadisticas_donaciones['total_recaudado'], 0) ?></h5>
                                    <small class="text-light">Total Recaudado</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-dark table-hover">
                            <thead class="table-secondary">
                                <tr>
                                    <th>ID</th>
                                    <th>Usuario</th>
                                    <th>Monto (ARS)</th>
                                    <th>Método</th>
                                    <th>Estado</th>
                                    <th>Fecha Donación</th>
                                    <th>Payment ID</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($donaciones)): ?>
                                    <tr>
                                        <td colspan="7" class="text-center text-muted py-4">
                                            No se encontraron donaciones
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($donaciones as $donacion): ?>
                                        <tr>
                                            <td><?= $donacion['id'] ?></td>
                                            <td>
                                                <strong><?= esc($donacion['nombredeusuario']) ?></strong>
                                                <br><small class="text-light"><?= esc($donacion['email']) ?></small>
                                            </td>
                                            <td>
                                                <strong class="text-warning">$<?= number_format($donacion['monto_ars'], 2) ?></strong>
                                            </td>
                                            <td>
                                                <span class="badge bg-info"><?= strtoupper($donacion['metodo_pago']) ?></span>
                                            </td>
                                            <td>
                                                <?php
                                                $colorEstado = [
                                                    'aprobado' => 'success',
                                                    'pendiente' => 'warning',
                                                    'rechazado' => 'danger',
                                                    'cancelado' => 'secondary'
                                                ];
                                                $color = $colorEstado[$donacion['estado']] ?? 'secondary';
                                                ?>
                                                <span class="badge bg-<?= $color ?>">
                                                    <?= ucfirst($donacion['estado']) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <small><?= date('d/m/Y H:i', strtotime($donacion['fecha_donacion'])) ?></small>
                                            </td>
                                            <td>
                                                <?php if ($donacion['payment_id']): ?>
                                                    <small class="text-light"><?= esc(substr($donacion['payment_id'], 0, 15)) ?>...</small>
                                                <?php else: ?>
                                                    <small class="text-light">-</small>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- TAB 4: COTIZACIONES -->
                <div class="tab-pane fade" id="cotizaciones" role="tabpanel">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h4 class="text-black mb-0">
                            <i class="bi bi-currency-exchange"></i> Gestión de Cotizaciones
                        </h4>
                        
                        <button class="btn btn-warning" onclick="actualizarCotizaciones()">
                            <i class="bi bi-arrow-clockwise"></i> Actualizar Cotizaciones
                        </button>
                    </div>

                    <!-- Cotizaciones actuales destacadas -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="card bg-warning text-dark">
                                <div class="card-body text-center">
                                    <h3 class="mb-0">Dólar Tarjeta</h3>
                                    <h1 class="display-3">$<?= number_format($ultima_tarjeta['valor_ars'] ?? 0, 2) ?></h1>
                                    <small>Última actualización: <?= isset($ultima_tarjeta['fecha']) ? date('d/m/Y H:i', strtotime($ultima_tarjeta['fecha'])) : '-' ?></small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card bg-info text-white">
                                <div class="card-body text-center">
                                    <h3 class="mb-0">Dólar MEP</h3>
                                    <h1 class="display-3">$<?= number_format($ultimo_mep['valor_ars'] ?? 0, 2) ?></h1>
                                    <small>Última actualización: <?= isset($ultimo_mep['fecha']) ? date('d/m/Y H:i', strtotime($ultimo_mep['fecha'])) : '-' ?></small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Filtros -->
                    <form method="GET" class="mb-3">
                        <input type="hidden" name="tab" value="cotizaciones">
                        <div class="row">
                            <div class="col-md-4">
                                <select class="form-select" name="tipo">
                                    <option value="">Todos los tipos</option>
                                    <option value="tarjeta" <?= ($tipo_filtro ?? '') === 'tarjeta' ? 'selected' : '' ?>>Tarjeta</option>
                                    <option value="MEP" <?= ($tipo_filtro ?? '') === 'MEP' ? 'selected' : '' ?>>MEP</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="bi bi-filter"></i> Filtrar
                                </button>
                            </div>
                        </div>
                    </form>

                    <div class="table-responsive">
                        <table class="table table-dark table-hover">
                            <thead class="table-secondary">
                                <tr>
                                    <th>ID</th>
                                    <th>Tipo</th>
                                    <th>Valor (ARS)</th>
                                    <th>Fecha y Hora</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($cotizaciones)): ?>
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-4">
                                            No hay cotizaciones registradas
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($cotizaciones as $cotizacion): ?>
                                        <tr>
                                            <td><?= $cotizacion['id'] ?></td>
                                            <td>
                                                <span class="badge bg-<?= $cotizacion['tipo'] === 'tarjeta' ? 'warning' : 'info' ?>">
                                                    <?= strtoupper($cotizacion['tipo']) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <strong class="text-success">$<?= number_format($cotizacion['valor_ars'], 2) ?></strong>
                                            </td>
                                            <td>
                                                <?= date('d/m/Y H:i:s', strtotime($cotizacion['fecha'])) ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- TAB 5: CATEGORÍAS -->
                <div class="tab-pane fade" id="categorias" role="tabpanel">
                    <h4 class="text-dark mb-4">
                        <i class="bi bi-tags-fill"></i> Gestión de Categorías de Productos
                    </h4>

                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i> 
                        <strong>Importante:</strong> Los aranceles se aplican automáticamente según la categoría del producto.
                        Modifícalos con cuidado según la normativa vigente.
                    </div>

                    <div class="table-responsive">
                        <table class="table table-dark table-hover">
                            <thead class="table-secondary">
                                <tr>
                                    <th>ID</th>
                                    <th>Categoría</th>
                                    <th>Descripción</th>
                                    <th>Arancel (%)</th>
                                    <th>Exento IVA</th>
                                    <th>Usos</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($categorias)): ?>
                                    <tr>
                                        <td colspan="7" class="text-center text-light py-4">
                                            No hay categorías registradas
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($categorias as $categoria): ?>
                                        <tr>
                                            <td><?= $categoria['id'] ?></td>
                                            <td>
                                                <strong><?= esc($categoria['nombre']) ?></strong>
                                            </td>
                                            <td>
                                                <small class="text-light"><?= esc($categoria['descripcion']) ?></small>
                                            </td>
                                            <td>
                                                <span class="badge bg-primary">
                                                    <?= number_format($categoria['arancel_porcentaje'], 2) ?>%
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <?php if ($categoria['exento_iva']): ?>
                                                    <i class="bi bi-check-circle text-success"></i>
                                                <?php else: ?>
                                                    <i class="bi bi-x-circle text-danger"></i>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge bg-info"><?= $categoria['total_usos'] ?? 0 ?></span>
                                            </td>
                                            <td>
                                                <button class="btn btn-sm btn-outline-warning" 
                                                        onclick="editarArancel(<?= $categoria['id'] ?>, '<?= esc($categoria['nombre']) ?>', <?= $categoria['arancel_porcentaje'] ?>)">
                                                    <i class="bi bi-pencil"></i> Editar
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </div>

</div>

<!-- Modal Cambiar Rol Usuario -->
<div class="modal fade" id="modalCambiarRol" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content bg-dark text-light">
            <form method="POST" id="formCambiarRol">
                <div class="modal-header border-secondary">
                    <h5 class="modal-title">Cambiar Rol de Usuario</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Cambiar rol de: <strong id="nombreUsuario"></strong></p>
                    <select class="form-select" name="rol" id="nuevoRol">
                        <option value="usuario">Usuario</option>
                        <option value="admin">Administrador</option>
                    </select>
                </div>
                <div class="modal-footer border-secondary">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Editar Arancel -->
<div class="modal fade" id="modalEditarArancel" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content bg-dark text-light">
            <form method="POST" id="formEditarArancel">
                <div class="modal-header border-secondary">
                    <h5 class="modal-title">Editar Arancel de Categoría</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Categoría: <strong id="nombreCategoria"></strong></p>
                    <div class="mb-3">
                        <label for="arancelInput" class="form-label">Nuevo Arancel (%)</label>
                        <input type="number" class="form-control" id="arancelInput" name="arancel_porcentaje" 
                               min="0" max="100" step="0.01" required>
                        <small class="text-muted">Ingresa un valor entre 0 y 100</small>
                    </div>
                </div>
                <div class="modal-footer border-secondary">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-warning">Actualizar Arancel</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Cambiar rol de usuario
function cambiarRol(userId, nombre, rolActual) {
    document.getElementById('nombreUsuario').textContent = nombre;
    document.getElementById('nuevoRol').value = rolActual === 'admin' ? 'usuario' : 'admin';
    document.getElementById('formCambiarRol').action = '<?= base_url('admin/usuarios/cambiar-rol/') ?>' + userId;
    
    new bootstrap.Modal(document.getElementById('modalCambiarRol')).show();
}

// Editar arancel de categoría
function editarArancel(categoriaId, nombre, arancelActual) {
    document.getElementById('nombreCategoria').textContent = nombre;
    document.getElementById('arancelInput').value = arancelActual;
    document.getElementById('formEditarArancel').action = '<?= base_url('admin/categorias/actualizar/') ?>' + categoriaId;
    
    new bootstrap.Modal(document.getElementById('modalEditarArancel')).show();
}

// Actualizar cotizaciones
async function actualizarCotizaciones() {
    const btn = event.target;
    const originalHTML = btn.innerHTML;
    
    btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Actualizando...';
    btn.disabled = true;
    
    try {
        const response = await fetch('<?= base_url('admin/cotizaciones/actualizar') ?>');
        const resultado = await response.json();
        
        if (resultado.success) {
            location.reload();
        } else {
            throw new Error(resultado.message || 'Error desconocido');
        }
    } catch (error) {
        alert('❌ Error actualizando cotizaciones: ' + error.message);
        btn.innerHTML = originalHTML;
        btn.disabled = false;
    }
}

// Mantener la pestaña activa después de recargar
document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    const tab = urlParams.get('tab');
    
    if (tab) {
        const tabButton = document.getElementById(tab + '-tab');
        if (tabButton) {
            const bootstrapTab = new bootstrap.Tab(tabButton);
            bootstrapTab.show();
        }
    }
    
    // Agregar el parámetro tab a los formularios
    document.querySelectorAll('form[method="GET"]').forEach(form => {
        form.addEventListener('submit', function() {
            const activeTab = document.querySelector('.nav-link.active');
            if (activeTab && !form.querySelector('input[name="tab"]')) {
                const tabInput = document.createElement('input');
                tabInput.type = 'hidden';
                tabInput.name = 'tab';
                tabInput.value = activeTab.id.replace('-tab', '');
                form.appendChild(tabInput);
            }
        });
    });
});
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>