<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Ver Cálculo - TaxImporter</title>
    <link rel="stylesheet" href="<?= base_url('css/ind.css') ?>">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="/Calculadora/Calculadora-1/Calculadora/public/css/ind.css">
</head>
<body class="bg-light">
     <div class="position-absolute" style="top: 5px; left: 22px; z-index: 1000;">
   <img src="<?= base_url('img/taximporterlogo.png') ?>" 
        alt="Logo TaxImporter" 
        style="max-width: 70px; height: auto; 
               filter: drop-shadow(2px 2px 6px rgba(0,0,0,1.9));">
</div>

<!-- ✅ NAVBAR -->
<nav class="navbar navbar-expand-lg navbar-dark bg-success">
    <div class="container">
        <a class="navbar-brand" href="<?= base_url('/historial') ?>">
            <i class="bi bi-calculator"></i> TaxImporter
        </a>
        
        <div class="navbar-nav ms-auto">
            <span class="navbar-text me-3">
                👤 <strong><?= esc(session()->get('usuario_nombre')) ?></strong>
            </span>
            <a class="btn btn-outline-light btn-sm" href="<?= base_url('usuario/logout') ?>">
                <i class="bi bi-box-arrow-right"></i> Salir
            </a>
        </div>
    </div>
</nav>

<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            
            <!-- ✅ BREADCRUMB -->
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="<?= base_url('/historial') ?>">
                            <i class="bi bi-house"></i> Historial
                        </a>
                    </li>
                    <li class="breadcrumb-item active">Ver Cálculo</li>
                </ol>
            </nav>

            <!-- ✅ INFORMACIÓN PRINCIPAL -->
            <div class="card shadow-lg mb-4">
                <div class="card-header bg-info text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4><i class="bi bi-eye"></i> Detalles del Cálculo</h4>
                        <small>
                            <i class="bi bi-calendar"></i> 
                            <?= date('d/m/Y H:i', strtotime($calculo['fecha_calculo'])) ?>
                        </small>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-lg-8">
                            <h3 class="text-primary">
                                <i class="bi bi-box"></i> <?= esc($calculo['nombre_producto']) ?>
                            </h3>
                            
                            <div class="mb-3">
                                <h6><i class="bi bi-link-45deg"></i> URL Original:</h6>
                                <a href="<?= esc($calculo['amazon_url']) ?>" 
                                   target="_blank" 
                                   class="btn btn-outline-warning btn-sm">
                                    <i class="bi bi-box-arrow-up-right"></i> Ver en Amazon
                                </a>
                            </div>
                            
                            <!-- ✅ DESGLOSE DETALLADO -->
                            <?php 
                            $desglose = json_decode($calculo['desglose_json'], true);
                            ?>
                            
                            <?php if ($desglose): ?>
                            <div class="card bg-light">
                                <div class="card-header">
                                    <h6><i class="bi bi-graph-up"></i> Desglose de Cálculo</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row text-center">
                                        <div class="col-md-4">
                                            <div class="border rounded p-2">
                                                <h6>🏛️ IVA (21%)</h6>
                                                <strong>$<?= number_format($desglose['iva'] ?? 0, 2) ?></strong>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="border rounded p-2">
                                                <h6>🚢 Derechos Import.</h6>
                                                <strong>$<?= number_format($desglose['derechos'] ?? 0, 2) ?></strong>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="border rounded p-2">
                                                <h6>✈️ Envío</h6>
                                                <strong>$<?= number_format($desglose['envio'] ?? 0, 2) ?></strong>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>
                            
                        </div>
                        <div class="col-lg-4">
                            <!-- ✅ RESUMEN FINANCIERO -->
                            <div class="card bg-success text-white">
                                <div class="card-body text-center">
                                    <h5><i class="bi bi-currency-dollar"></i> Precio Original</h5>
                                    <h2>$<?= number_format($calculo['precio_usd'], 2) ?> USD</h2>
                                </div>
                            </div>
                            
                            <div class="card bg-danger text-white mt-3">
                                <div class="card-body text-center">
                                    <h5><i class="bi bi-calculator"></i> Total Final</h5>
                                    <h2>$<?= number_format($calculo['total_ars'], 2) ?> ARS</h2>
                                </div>
                            </div>
                            
                            <!-- ✅ COMPARACIÓN -->
                            <div class="card bg-warning text-dark mt-3">
                                <div class="card-body text-center">
                                    <h6><i class="bi bi-graph-up-arrow"></i> Incremento Total</h6>
                                    <?php 
                                    $incremento = (($calculo['total_ars'] / 1683.5) - $calculo['precio_usd']) / $calculo['precio_usd'] * 100;
                                    ?>
                                    <h4><?= number_format($incremento, 1) ?>%</h4>
                                    <small>vs precio original</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ✅ ACCIONES -->
            <div class="d-grid gap-2 d-md-flex justify-content-md-between">
                <a href="<?= base_url('/historial') ?>" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Volver al Historial
                </a>
                
                <div>
                    <a href="<?= base_url('historial/editar/' . $calculo['id']) ?>" 
                       class="btn btn-warning">
                        <i class="bi bi-pencil"></i> Editar
                    </a>
                    <a href="<?= base_url('historial/eliminar/' . $calculo['id']) ?>" 
                       class="btn btn-danger"
                       onclick="return confirm('¿Estás seguro de eliminar este cálculo?')">
                        <i class="bi bi-trash"></i> Eliminar
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>