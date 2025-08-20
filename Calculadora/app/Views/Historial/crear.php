<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Nuevo Cálculo - TaxImporter</title>
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
        <div class="col-lg-8">
            
            <!-- ✅ BREADCRUMB -->
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="<?= base_url('/historial') ?>">
                            <i class="bi bi-house"></i> Historial
                        </a>
                    </li>
                    <li class="breadcrumb-item active">Nuevo Cálculo</li>
                </ol>
            </nav>

            <!-- ✅ MENSAJES DE ERROR -->
            <?php if (isset($error)): ?>
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle"></i> <?= esc($error) ?>
                </div>
            <?php endif; ?>

            <?php if (isset($validation)): ?>
                <div class="alert alert-danger">
                    <h6><i class="bi bi-x-circle"></i> Por favor corrige los siguientes errores:</h6>
                    <ul class="mb-0">
                        <?php foreach ($validation->getErrors() as $error): ?>
                            <li><?= esc($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <!-- ✅ FORMULARIO -->
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h4><i class="bi bi-plus-circle"></i> Agregar Nuevo Cálculo</h4>
                </div>
                <div class="card-body">
                    <form action="<?= base_url('historial/guardar') ?>" method="post" novalidate>
                        <?= csrf_field() ?>

                        <div class="mb-3">
                            <label for="amazon_url" class="form-label">
                                <i class="bi bi-link-45deg"></i> URL de Amazon *
                            </label>
                            <input type="url" 
                                   class="form-control <?= isset($validation) && $validation->hasError('amazon_url') ? 'is-invalid' : '' ?>" 
                                   id="amazon_url" 
                                   name="amazon_url" 
                                   placeholder="https://www.amazon.com/dp/XXXXXXXXXX"
                                   value="<?= set_value('amazon_url', $old_input['amazon_url'] ?? '') ?>" 
                                   required>
                            <div class="form-text">
                                <i class="bi bi-info-circle"></i> 
                                Pega aquí la URL completa del producto de Amazon
                            </div>
                            <?php if (isset($validation) && $validation->hasError('amazon_url')): ?>
                                <div class="invalid-feedback">
                                    <?= $validation->getError('amazon_url') ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="mb-3">
                            <label for="nombre_producto" class="form-label">
                                <i class="bi bi-box"></i> Nombre del Producto *
                            </label>
                            <input type="text" 
                                   class="form-control <?= isset($validation) && $validation->hasError('nombre_producto') ? 'is-invalid' : '' ?>" 
                                   id="nombre_producto" 
                                   name="nombre_producto" 
                                   placeholder="Ej: iPhone 15 Pro 256GB"
                                   value="<?= set_value('nombre_producto', $old_input['nombre_producto'] ?? '') ?>" 
                                   required>
                            <?php if (isset($validation) && $validation->hasError('nombre_producto')): ?>
                                <div class="invalid-feedback">
                                    <?= $validation->getError('nombre_producto') ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="precio_usd" class="form-label">
                                    <i class="bi bi-currency-dollar"></i> Precio en USD *
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" 
                                           class="form-control <?= isset($validation) && $validation->hasError('precio_usd') ? 'is-invalid' : '' ?>" 
                                           id="precio_usd" 
                                           name="precio_usd" 
                                           step="0.01" 
                                           min="0.01" 
                                           max="99999"
                                           placeholder="199.99"
                                           value="<?= set_value('precio_usd', $old_input['precio_usd'] ?? '') ?>" 
                                           required>
                                </div>
                                <?php if (isset($validation) && $validation->hasError('precio_usd')): ?>
                                    <div class="invalid-feedback">
                                        <?= $validation->getError('precio_usd') ?>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="total_ars" class="form-label">
                                    <i class="bi bi-calculator"></i> Total Calculado ARS *
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" 
                                           class="form-control <?= isset($validation) && $validation->hasError('total_ars') ? 'is-invalid' : '' ?>" 
                                           id="total_ars" 
                                           name="total_ars" 
                                           step="0.01" 
                                           min="0.01"
                                           placeholder="450000.00"
                                           value="<?= set_value('total_ars', $old_input['total_ars'] ?? '') ?>" 
                                           required>
                                </div>
                                <div class="form-text">
                                    <i class="bi bi-lightbulb"></i> 
                                    Incluye impuestos + envío + recargos
                                </div>
                                <?php if (isset($validation) && $validation->hasError('total_ars')): ?>
                                    <div class="invalid-feedback">
                                        <?= $validation->getError('total_ars') ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- ✅ CALCULADORA RÁPIDA -->
                        <div class="card bg-light mb-3">
                            <div class="card-body">
                                <h6><i class="bi bi-calculator"></i> Calculadora Rápida</h6>
                                <p class="small text-muted">
                                    💡 <strong>Fórmula aproximada:</strong> 
                                    Precio USD × 1.683,5 (dólar tarjeta) × 1.71 (impuestos) + $25 USD envío
                                </p>
                                <button type="button" 
                                        class="btn btn-sm btn-outline-info" 
                                        onclick="calcularRapido()">
                                    🧮 Calcular Automáticamente
                                </button>
                            </div>
                        </div>

                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <a href="<?= base_url('/historial') ?>" class="btn btn-secondary">
                                <i class="bi bi-arrow-left"></i> Cancelar
                            </a>
                            <button type="submit" class="btn btn-success">
                                <i class="bi bi-check-circle"></i> Guardar Cálculo
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ✅ JAVASCRIPT PARA CALCULADORA RÁPIDA -->
<script>
function calcularRapido() {
    const precioUSD = parseFloat(document.getElementById('precio_usd').value) || 0;
    
    if (precioUSD <= 0) {
        alert('⚠️ Por favor ingresa un precio en USD válido');
        return;
    }
    
    // Fórmula básica
    const dolarTarjeta = 1683.5;
    const factorImpuestos = 1.71; // IVA + Derechos + otros
    const envioUSD = 25;
    
    const total = (precioUSD * dolarTarjeta * factorImpuestos) + (envioUSD * dolarTarjeta);
    
    document.getElementById('total_ars').value = total.toFixed(2);
    
    // Mostrar breakdown
    alert(`💰 Cálculo aproximado:
📦 Producto: $${precioUSD} USD = $${(precioUSD * dolarTarjeta).toLocaleString()} ARS
🏛️ Impuestos: $${((precioUSD * dolarTarjeta * factorImpuestos) - (precioUSD * dolarTarjeta)).toLocaleString()} ARS
✈️ Envío: $${envioUSD} USD = $${(envioUSD * dolarTarjeta).toLocaleString()} ARS
💳 TOTAL: $${total.toLocaleString()} ARS`);
}
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>