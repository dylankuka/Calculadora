<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Detalles del Cálculo - TaxImporter</title>
    <link rel="stylesheet" href="<?= base_url('css/ind.css') ?>">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .detalle-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px;
        }
        .impuesto-row {
            padding: 8px 0;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        .impuesto-row:last-child {
            border-bottom: none;
        }
        .total-final {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            color: white;
            border-radius: 10px;
            padding: 20px;
        }
        .franquicia-info {
            background: linear-gradient(45deg, #f093fb 0%, #f5576c 100%);
            color: white;
            border-radius: 10px;
            padding: 15px;
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

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark card-custom">
    <div class="container">
        <a class="navbar-brand textcolor" href="<?= base_url('/historial') ?>">
            <i class="bi bi-calculator"></i> TaxImporter
        </a>
        
        <div class="navbar-nav ms-auto">
            <span class="navbar-text me-3 textcolor">
                👤 <strong><?= esc(session()->get('usuario_nombre')) ?></strong>
            </span>
            <a class="btn btn-outline-dark btn-sm" href="<?= base_url('usuario/logout') ?>">
                <i class="bi bi-box-arrow-right textcolor"></i> Salir
            </a>
        </div>
    </div>
</nav>

<div class="container mt-4">
    
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="<?= base_url('/historial') ?>" class="text-light">
                    <i class="bi bi-house"></i> Historial
                </a>
            </li>
            <li class="breadcrumb-item active text-light">
                <i class="bi bi-eye"></i> Detalles del Cálculo
            </li>
        </ol>
    </nav>

    <?php 
        $desglose = json_decode($calculo['desglose_json'], true);
        $bajoFranquicia = ($calculo['valor_cif_usd'] ?? 0) <= 400;
        
        // Datos del cálculo
        $datosBase = $desglose['datos_base'] ?? [];
        $impuestosUSD = $desglose['impuestos_usd'] ?? [];
        $impuestosARS = $desglose['impuestos_ars'] ?? [];
        $totales = $desglose['totales'] ?? [];
    ?>

    <div class="row">
        <!-- Columna principal: Detalles del cálculo -->
        <div class="col-lg-8">
            
            <!-- Información del producto -->
            <div class="card card-custom2 mb-4">
                <div class="card-header card-custom textcolor">
                    <h5><i class="bi bi-box-seam"></i> Información del Producto</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <h4 class="text-primary mb-3"><?= esc($calculo['nombre_producto']) ?></h4>
                            
                            <div class="row mb-3">
                                <div class="col-sm-6">
                                    <p class="mb-2">
                                        <i class="bi bi-link-45deg text-info"></i> 
                                        <strong>URL de Amazon:</strong>
                                    </p>
                                    <a href="<?= esc($calculo['amazon_url']) ?>" target="_blank" class="btn btn-outline-info btn-sm">
                                        <i class="bi bi-box-arrow-up-right"></i> Ver en Amazon
                                    </a>
                                </div>
                                <div class="col-sm-6">
                                    <p class="mb-2">
                                        <i class="bi bi-calendar3 text-warning"></i> 
                                        <strong>Fecha del cálculo:</strong>
                                    </p>
                                    <span class="badge bg-warning text-dark">
                                        <?= date('d/m/Y H:i:s', strtotime($calculo['fecha_calculo'])) ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 text-end">
                            <!-- Estado de franquicia -->
                            <div class="<?= $bajoFranquicia ? 'franquicia-info' : 'bg-warning text-dark rounded p-3' ?>">
                                <h6>
                                    <i class="bi bi-info-circle"></i> 
                                    <?= $bajoFranquicia ? 'Bajo Franquicia' : 'Sobre Franquicia' ?>
                                </h6>
                                <p class="mb-0">
                                    Valor CIF: <strong>$<?= number_format($calculo['valor_cif_usd'] ?? 0, 2) ?> USD</strong>
                                </p>
                                <?php if (!$bajoFranquicia): ?>
                                    <small>
                                        Excedente: $<?= number_format(($calculo['excedente_400_usd'] ?? 0), 2) ?> USD
                                    </small>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Desglose detallado de impuestos -->
            <div class="card detalle-card mb-4">
                <div class="card-header">
                    <h5><i class="bi bi-calculator"></i> Desglose Detallado de Impuestos</h5>
                </div>
                <div class="card-body">
                    
                    <!-- Valores base -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h6 class="border-bottom border-light pb-2">💵 Valores en USD</h6>
                            <div class="impuesto-row d-flex justify-content-between">
                                <span>Precio del producto:</span>
                                <strong>$<?= number_format($calculo['precio_usd'], 2) ?></strong>
                            </div>
                            <div class="impuesto-row d-flex justify-content-between">
                                <span>Costo de envío:</span>
                                <strong>$<?= number_format($datosBase['envio_usd'] ?? 0, 2) ?></strong>
                            </div>
                            <div class="impuesto-row d-flex justify-content-between">
                                <span><strong>Valor CIF total:</strong></span>
                                <strong class="text-warning">$<?= number_format($datosBase['valor_cif_usd'] ?? 0, 2) ?></strong>
                            </div>
                            <?php if (!$bajoFranquicia): ?>
                                <div class="impuesto-row d-flex justify-content-between">
                                    <span>Excedente sobre $400:</span>
                                    <strong>$<?= number_format($datosBase['excedente_400_usd'] ?? 0, 2) ?></strong>
                                </div>
                                <div class="impuesto-row d-flex justify-content-between">
                                    <span>Aranceles (<?= $datosBase['arancel_categoria'] ?? 0 ?>%):</span>
                                    <strong>$<?= number_format($impuestosUSD['aranceles_usd'] ?? 0, 2) ?></strong>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="col-md-6">