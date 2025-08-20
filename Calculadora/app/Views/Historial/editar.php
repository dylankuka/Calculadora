<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Editar Cálculo - TaxImporter</title>
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
                    <li class="breadcrumb-item">
                        <a href="<?= base_url('historial/ver/' . $calculo['id']) ?>">
                            Ver Cálculo
                        </a>
                    </li>
                    <li class="breadcrumb-item active">Editar</li>
                </ol>
            </nav>

            <!-- ✅ MENSAJES DE ERROR -->
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

            <!-- ✅ FORMULARIO DE EDICIÓN -->
            <div class="card shadow">
                <div class="card-header bg-warning text-dark">
                    <h4><i class="bi bi-pencil"></i> Editar Cálculo</h4>
                    <small class="d-block">
                        <i class="bi bi-info-circle"></i> 
                        Solo puedes editar el nombre y precio. La URL original se mantiene.
                    </small>
                </div>
                <div class="card-body">
                    <form action="<?= base_url('historial/actualizar/' . $calculo['id']) ?>" method="post" novalidate>
                        <?= csrf_field() ?>

                        <!-- ✅ URL SOLO LECTURA -->
                        <div class="mb-3">
                            <label class="form-label">
                                <i class="bi bi-link-45deg"></i> URL Original (No editable)
                            </label>
                            <div class="input-group">
                                <input type="text" 
                                       class="form-control" 
                                       value="<?= esc($calculo['amazon_url']) ?>" 
                                       readonly>
                                <a href="<?= esc($calculo['amazon_url']) ?>" 
                                   target="_blank" 
                                   class="btn btn-outline-primary">
                                    <i class="bi bi-box-arrow-up-right"></i>
                                </a>
                            </div>
                            <div class="form-text">
                                <i class="bi bi-lock"></i> 
                                La URL no se puede modificar por seguridad
                            </div>
                        </div>

                        <!-- ✅ NOMBRE EDITABLE -->
                        <div class="mb-3">
                            <label for="nombre_producto" class="form-label">
                                <i class="bi bi-box"></i> Nombre del Producto *
                            </label>
                            <input type="text" 
                                   class="form-control <?= isset($validation) && $validation->hasError('nombre_producto') ? 'is-invalid' : '' ?>" 
                                   id="nombre_producto" 
                                   name="nombre_producto" 
                                   placeholder="Ej: iPhone 15 Pro 256GB"
                                   value="<?= set_value('nombre_producto', $old_input['nombre_producto'] ?? $calculo['nombre_producto']) ?>" 
                                   required>
                            <?php if (isset($validation) && $validation->hasError('nombre_producto')): ?>
                                <div class="invalid-feedback">
                                    <?= $validation->getError('nombre_producto') ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- ✅ PRECIO EDITABLE -->
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
                                           value="<?= set_value('precio_usd', $old_input['precio_usd'] ?? $calculo['precio_usd']) ?>" 
                                           required>
                                </div>
                                <?php if (isset($validation) && $validation->hasError('precio_usd')): ?>
                                    <div class="invalid-feedback">
                                        <?= $validation->getError('precio_usd') ?>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">
                                    <i class="bi bi-calculator"></i> Total ARS (Auto-calculado)
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="text" 
                                           class="form-control bg-light" 
                                           id="total_display"
                                           value="<?= number_format($calculo['total_ars'], 2) ?>" 
                                           readonly>
                                </div>
                                <div class="form-text">
                                    <i class="bi bi-info-circle"></i> 
                                    Se recalcula automáticamente al cambiar el precio USD
                                </div>
                            </div>
                        </div>

                        <!-- ✅ INFORMACIÓN HISTÓRICA -->
                        <div class="card bg-light mb-3">
                            <div class="card-body">
                                <h6><i class="bi bi-clock-history"></i> Información Original</h6>
                                <div class="row">
                                    <div class="col-md-6">
                                        <small class="text-muted">
                                            <strong>Fecha de creación:</strong><br>
                                            <?= date('d/m/Y H:i', strtotime($calculo['fecha_calculo'])) ?>
                                        </small>
                                    </div>
                                    <div class="col-md-6">
                                        <small class="text-muted">
                                            <strong>Total original:</strong><br>
                                            $<?= number_format($calculo['total_ars'], 2) ?> ARS
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-grid gap-2 d-md-flex justify-content-md-between">
                            <a href="<?= base_url('historial/ver/' . $calculo['id']) ?>" class="btn btn-secondary">
                                <i class="bi bi-arrow-left"></i> Cancelar
                            </a>
                            
                            <div>
                                <button type="button" 
                                        class="btn btn-outline-info me-2" 
                                        onclick="recalcular()">
                                    <i class="bi bi-arrow-clockwise"></i> Recalcular
                                </button>
                                <button type="submit" class="btn btn-success">
                                    <i class="bi bi-check-circle"></i> Actualizar Cálculo
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ✅ JAVASCRIPT PARA RECÁLCULO AUTOMÁTICO -->
<script>
// Recalcular cuando cambia el precio
document.getElementById('precio_usd').addEventListener('input', function() {
    recalcular();
});

function recalcular() {
    const precioUSD = parseFloat(document.getElementById('precio_usd').value) || 0;
    
    if (precioUSD <= 0) {
        document.getElementById('total_display').value = '0.00';
        return;
    }
    
    // Fórmula básica (mismo cálculo que en crear.php)
    const dolarTarjeta = 1683.5;
    const factorImpuestos = 1.71;
    const envioUSD = 25;
    
    const total = (precioUSD * dolarTarjeta * factorImpuestos) + (envioUSD * dolarTarjeta);
    
    document.getElementById('total_display').value = total.toLocaleString('es-AR', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
}

// Mostrar comparación con valor original
function mostrarComparacion() {
    const precioOriginal = <?= $calculo['precio_usd'] ?>;
    const totalOriginal = <?= $calculo['total_ars'] ?>;
    const precioNuevo = parseFloat(document.getElementById('precio_usd').value) || 0;
    
    if (precioNuevo <= 0) return;
    
    const dolarTarjeta = 1683.5;
    const factorImpuestos = 1.71;
    const envioUSD = 25;
    const totalNuevo = (precioNuevo * dolarTarjeta * factorImpuestos) + (envioUSD * dolarTarjeta);
    
    const diferencia = totalNuevo - totalOriginal;
    const porcentaje = ((totalNuevo - totalOriginal) / totalOriginal * 100).toFixed(1);
    
    let mensaje = `📊 Comparación:
💰 Precio original: ${precioOriginal} USD → ${totalOriginal.toLocaleString()} ARS
💰 Precio nuevo: ${precioNuevo} USD → ${totalNuevo.toLocaleString()} ARS
📈 Diferencia: ${diferencia >= 0 ? '+' : ''}${diferencia.toLocaleString()} ARS (${porcentaje}%)`;
    
    alert(mensaje);
}

// Auto-recalcular al cargar la página
document.addEventListener('DOMContentLoaded', function() {
    recalcular();
});
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>