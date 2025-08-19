<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Mi Historial - TaxImporter</title>
<link rel="stylesheet" href="<?= base_url('css/ind.css') ?>">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
<link rel="stylesheet" href="/Calculadora/Calculadora-1/Calculadora/public/css/ind.css">


</head>

<!-- ✅ NAVBAR CON USUARIO LOGUEADO -->
<nav class="card-custom navbar navbar-expand-lg navbar-dark">
    <div class="container">
        <a class="navbar-brand textcolor" href="<?= base_url('/historial') ?>">
            <i class="bi bi-calculator"></i> TaxImporter
        </a>
    <div class="position-absolute" style="top: 0px; left: 0px; z-index: 1000;">
   <img src="<?= base_url('img/taximporterlogo.png') ?>" 
        alt="Logo TaxImporter" 
        style="max-width: 240px; height: auto; 
               filter: drop-shadow(2px 2px 4px rgba(0,0,0,0.2));">
            <span class="navbar-text me-3 textcolor">
                👤 Hola, <strong><?= esc(session()->get('usuario_nombre')) ?></strong>
            </span>
            <a class="btn btn-outline-dark btn-sm" href="<?= base_url('usuario/logout') ?>">
                <i class="bi bi-box-arrow-right textcolor"></i> Salir
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

    <!-- ✅ HEADER CON ESTADÍSTICAS -->
    <div class="row mb-4">
        <div class="col-md-8">
            <h2><i class="bi bi-clock-history"></i> Mi Historial de Cálculos</h2>
        </div>
        <div class="col-md-4 text-end">
            <a href="<?= base_url('historial/crear') ?>" class="btn textcolor card-custom">
                <i class="bi bi-plus-circle card-custom textcolor"></i> Nuevo Cálculo
            </a>
        </div>
    </div>

    <!-- ✅ TARJETAS DE RESUMEN -->
    <?php if ($resumen): ?>
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card card-custom bg-info textcolor">
                <div class="card-body">
                    <h5><i class="bi bi-graph-up"></i> Total Consultas</h5>
                    <h3><?= number_format($resumen['total_consultas'] ?? 0) ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card bg-success textcolor">
                <div class="card-body card-custom bg-info textcolor">
                    <h5><i class="bi bi-currency-dollar"></i> Total Calculado</h5>
                    <h3>$<?= number_format($resumen['total_calculado'] ?? 0, 2) ?> ARS</h3>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- ✅ BUSCADOR -->
    <div class="card mb-4 card-custom2">
        <div class="card-custom2">
           <form method="GET" action="<?= base_url('historial') ?>" class="form-custom">
    <div class="input-group">
        <input type="text" 
               class="form-control input-custom" 
               name="buscar" 
               placeholder="Buscar por nombre de producto..." 
               value="<?= esc($busqueda ?? '') ?>">
        <button class="btn btn-search-custom" type="submit">
            <i class="bi bi-search"></i> Buscar
        </button>
        <?php if ($busqueda): ?>
            <a href="<?= base_url('historial') ?>" class="btn btn-clear-custom">
                <i class="bi bi-x"></i> Limpiar
            </a>
        <?php endif; ?>
    </div>
</form>
            
            <?php if ($mensaje): ?>
                <div class="mt-2">
                    <small class="text-muted"><i class="bi bi-info-circle"></i> <?= esc($mensaje) ?></small>
                </div>
            <?php endif; ?>
        </div>
    </div>

   <!-- ✅ TABLA DE HISTORIAL -->
<?php if (!empty($historial)): ?>
    <div class="card card-historial">
        <div class="card-header card-header-historial">
            <h5><i class="bi bi-table"></i> Tus Cálculos Guardados</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-historial mb-0">
                    <thead>
                        <tr>
                            <th><i class="bi bi-calendar me-1"></i> Fecha</th>
                            <th><i class="bi bi-cart me-1"></i> Producto</th>
                            <th><i class="bi bi-currency-dollar me-1"></i> USD</th>
                            <th><i class="bi bi-currency-exchange me-1"></i> Total ARS</th>
                            <th><i class="bi bi-link me-1"></i> Amazon</th>
                            <th><i class="bi bi-gear me-1"></i> Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($historial as $item): ?>
                            <tr>
                                <td>
                                    <small class="text-muted">
                                        <?= date('d/m/Y H:i', strtotime($item['fecha_calculo'])) ?>
                                    </small>
                                </td>
                                <td>
                                    <strong><?= esc($item['nombre_producto']) ?></strong>
                                </td>
                                <td>
                                    <span class="badge badge-historial-usd">
                                        $<?= number_format($item['precio_usd'], 2) ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge badge-historial-ars">
                                        $<?= number_format($item['total_ars'], 2) ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="<?= esc($item['amazon_url']) ?>" 
                                       target="_blank" 
                                       class="btn btn-sm btn-amazon-link">
                                        <i class="bi bi-box-arrow-up-right"></i>
                                    </a>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm btn-group-historial">
                                        <a href="<?= base_url('historial/ver/' . $item['id']) ?>" 
                                           class="btn btn-action-view" 
                                           title="Ver detalles">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="<?= base_url('historial/editar/' . $item['id']) ?>" 
                                           class="btn btn-action-edit" 
                                           title="Editar">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <a href="<?= base_url('historial/eliminar/' . $item['id']) ?>" 
                                           class="btn btn-action-delete" 
                                           title="Eliminar"
                                           onclick="return confirm('¿Estás seguro de eliminar este cálculo?')">
                                            <i class="bi bi-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
<?php else: ?>
    <!-- ✅ ESTADO VACÍO -->
    <div class="card text-center card-empty-state">
        <div class="card-body py-5">
            <i class="bi bi-inbox empty-state-icon text-white"></i>
            <h4 class="mt-3 textcolor1">No hay cálculos guardados</h4>
            <p class="textcolor1">Comienza agregando tu primer cálculo de Amazon</p>
            <a href="<?= base_url('historial/crear') ?>" class="btn btn-primary btn-empty-action">
                <i class="bi bi-plus-circle"></i> Crear Primer Cálculo
            </a>
        </div>
    </div>
<?php endif; ?>
<div class="container d-flex justify-content-center">
    <div class="texto-transparente p-4 text-center mt-5"> <!-- mt-5 = margin-top -->
        <h2>¡Bienvenido a TaxImporter!</h2>
        <p>
            Esta pagina fue creada como proyecto escolar por Dylan Kiyama y Juan Cruz Gayzio en 2025<br>
            Con esta pagina podras calcular el precio de un producto de Amazon en Argentina,
            teniendo en cuenta el precio en dolares, el IVA y los impuestos de importación.
        </p>
    </div>
</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>