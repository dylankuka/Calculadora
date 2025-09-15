<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Nuevo Cálculo - TaxImporter</title>
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
    <div class="row justify-content-center">
        <div class="col-lg-10">
            
            <!-- ✅ BREADCRUMB -->
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="<?= base_url('/historial') ?>" class="text-light">
                            <i class="bi bi-house"></i> Historial
                        </a>
                    </li>
                    <li class="breadcrumb-item active text-light">Nuevo Cálculo</li>
                </ol>
            </nav>

            <!-- ✅ MENSAJES -->
            <?php if (isset($error)): ?>
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle"></i> <?= esc($error) ?>
                </div>
            <?php endif; ?>

            <?php if (isset($validation)): ?>
                <div class="alert alert-danger">
                    <h6><i class="bi bi-x-circle"></i> Errores:</h6>
                    <ul class="mb-0">
                        <?php foreach ($validation->getErrors() as $error): ?>
                            <li><?= esc($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <!-- ✅ FORMULARIO PRINCIPAL -->
            <div class="card shadow card-custom2">
                <div class="card-header card-custom textcolor">
                    <h4><i class="bi bi-plus-circle"></i> Calculadora de Impuestos Amazon</h4>
                </div>
                <div class="card-body">
                    <form action="<?= base_url('historial/calcular') ?>" method="post" novalidate>
                        <?= csrf_field() ?>

                        <!-- ✅ PASO 1: URL DE AMAZON -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h5 class="text-warning">📦 Paso 1: Producto de Amazon</h5>
                                <div class="mb-3">
                                    <label for="amazon_url" class="form-label">
                                        <i class="bi bi-link-45deg"></i> URL del Producto *
                                    </label>
                                    <div class="input-group">
                                        <input type="url" 
                                               class="form-control" 
                                               id="amazon_url" 
                                               name="amazon_url" 
                                               placeholder="https://www.amazon.com/dp/..."
                                               value="<?= set_value('amazon_url', $old_input['amazon_url'] ?? '') ?>" 
                                               required>
                                        <button type="button" class="btn card-custom" onclick="obtenerProducto()">
                                            <i class="bi bi-search"></i> Obtener Datos
                                        </button>
                                    </div>
                                </div>
                                
                                <!-- ✅ DATOS DEL PRODUCTO (OCULTO INICIALMENTE) -->
                                <div id="producto-info" class="card bg-dark text-light" style="display: none;">
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-3">
                                                <img id="producto-imagen" src="" alt="Producto" class="img-fluid rounded">
                                            </div>
                                            <div class="col-md-9">
                                                <h6 id="producto-nombre">Cargando...</h6>
                                                <p class="mb-1"><strong>Precio:</strong> $<span id="producto-precio">0.00</span> USD</p>
                                                <p class="mb-1"><strong>Disponibilidad:</strong> <span id="producto-disponibilidad">-</span></p>
                                                <p class="mb-0"><strong>Vendedor:</strong> <span id="producto-vendedor">-</span></p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- ✅ PASO 2: LOCALIDAD ARGENTINA -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h5 class="text-warning">🇦🇷 Paso 2: Localidad de Entrega</h5>
                                <div class="row">
                                    <div class="col-md-6">
                                        <label for="provincia" class="form-label">
                                            <i class="bi bi-geo-alt"></i> Provincia *
                                        </label>
                                        <select class="form-select" id="provincia" name="provincia">
                                            <option value="">Selecciona tu provincia</option>
                                            <option value="CABA">Ciudad de Buenos Aires</option>
                                            <option value="BA">Buenos Aires</option>
                                            <option value="CB">Córdoba</option>
                                            <option value="SF">Santa Fe</option>
                                            <option value="MZ">Mendoza</option>
                                            <option value="TU">Tucumán</option>
                                            <option value="ER">Entre Ríos</option>
                                            <option value="SA">Salta</option>
                                            <option value="CC">Chaco</option>
                                            <option value="CR">Corrientes</option>
                                            <option value="MI">Misiones</option>
                                            <option value="SJ">San Juan</option>
                                            <option value="SL">San Luis</option>
                                            <option value="JY">Jujuy</option>
                                            <option value="RN">Río Negro</option>
                                            <option value="NQ">Neuquén</option>
                                            <option value="CH">Chubut</option>
                                            <option value="LP">La Pampa</option>
                                            <option value="FO">Formosa</option>
                                            <option value="CT">Catamarca</option>
                                            <option value="LR">La Rioja</option>
                                            <option value="SC">Santiago del Estero</option>
                                            <option value="TF">Tierra del Fuego</option>
                                            <option value="SC">Santa Cruz</option>
                                        </select>
                                
                                <!-- ✅ INFO IMPUESTOS POR LOCALIDAD -->
                                <div id="impuestos-info" class="mt-3 card bg-warning text-dark" style="display: none;">
                                    <div class="card-body">
                                        <h6><i class="bi bi-info-circle"></i> Impuestos Aplicables</h6>
                                        <div class="row">
                                            <div class="col-md-4">
                                                <strong>IVA:</strong> <span id="iva-porcentaje">21%</span>
                                            </div>
                                            <div class="col-md-4">
                                                <strong>Derechos:</strong> <span id="derechos-porcentaje">50%</span>
                                            </div>
                                            <div class="col-md-4">
                                                <strong>Adicionales:</strong> <span id="adicionales-porcentaje">0%</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- ✅ PASO 3: TIPO DE CAMBIO -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h5 class="text-warning">💱 Paso 3: Tipo de Cambio</h5>
                                <div class="row">
                                    <div class="col-md-6">
                                        <label for="tipo_cambio" class="form-label">
                                            <i class="bi bi-currency-exchange"></i> Cotización *
                                        </label>
                                        <select class="form-select" id="tipo_cambio" name="tipo_cambio" required onchange="actualizarCotizacion()">
                                            <option value="">Selecciona tipo de cambio</option>
                                            <option value="tarjeta">💳 Dólar Tarjeta/Turista</option>
                                            <option value="MEP">📈 Dólar MEP/Bolsa</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">
                                            <i class="bi bi-calculator"></i> Cotización Actual
                                        </label>
                                        <div class="input-group">
                                            <span class="input-group-text">$</span>
                                            <input type="text" class="form-control" id="cotizacion_actual" readonly>
                                            <span class="input-group-text">ARS</span>
                                        </div>
                                        <small class="text-muted">
                                            <i class="bi bi-clock"></i> Actualizado: <span id="fecha_cotizacion">-</span>
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- ✅ RESUMEN Y CÁLCULO -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <div class="card bg-success text-white">
                                    <div class="card-header">
                                        <h5><i class="bi bi-calculator"></i> Resumen del Cálculo</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <p><strong>Precio del producto:</strong> $<span id="resumen-precio">0.00</span> USD</p>
                                                <p><strong>Envío estimado:</strong> $<span id="resumen-envio">25.00</span> USD</p>
                                                <p><strong>Subtotal:</strong> $<span id="resumen-subtotal">0.00</span> USD</p>
                                            </div>
                                            <div class="col-md-6">
                                                <p><strong>Tipo de cambio:</strong> $<span id="resumen-cambio">0.00</span> ARS</p>
                                                <p><strong>Impuestos totales:</strong> $<span id="resumen-impuestos">0.00</span> ARS</p>
                                                <h4><strong>TOTAL FINAL:</strong> $<span id="resumen-total">0.00</span> ARS</h4>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- ✅ BOTONES -->
                        <div class="d-grid gap-2 d-md-flex justify-content-md-between">
                            <a href="<?= base_url('/historial') ?>" class="btn btn-secondary">
                                <i class="bi bi-arrow-left"></i> Cancelar
                            </a>
                            
                            <div>
                                <button type="button" class="btn btn-warning" onclick="calcularTotal()">
                                    <i class="bi bi-calculator"></i> Calcular Total
                                </button>
                                <button type="submit" class="btn btn-success">
                                    <i class="bi bi-save"></i> Guardar Cálculo
                                </button>
                            </div>
                        </div>

                        <!-- Campos ocultos para enviar datos -->
                        <input type="hidden" id="nombre_producto" name="nombre_producto">
                        <input type="hidden" id="precio_usd" name="precio_usd">
                        <input type="hidden" id="total_ars" name="total_ars">
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Datos de cotizaciones actuales (desde PHP)
const cotizacionesActuales = <?= json_encode($cotizaciones ?? ['tarjeta' => 1683.5, 'MEP' => 1650.0]) ?>;

// Datos de ciudades por provincia
const ciudadesPorProvincia = {
    'CABA': ['Ciudad de Buenos Aires'],
    'BA': ['La Plata', 'Mar del Plata', 'Bahía Blanca', 'Tandil', 'Olavarría'],
    'CB': ['Córdoba', 'Río Cuarto', 'Villa María', 'San Francisco'],
    'SF': ['Santa Fe', 'Rosario', 'Rafaela', 'Venado Tuerto'],
    // ... más ciudades
};

// Impuestos por provincia (ejemplo)
const impuestosPorProvincia = {
    'CABA': { iva: 21, derechos: 50, adicionales: 0 },
    'BA': { iva: 21, derechos: 50, adicionales: 2.5 },
    'CB': { iva: 21, derechos: 50, adicionales: 1.5 },
    // ... más configuraciones
};


function actualizarCotizacion() {
    const tipo = document.getElementById('tipo_cambio').value;
    const cotizacion = cotizacionesActuales[tipo] || 0;
    
    document.getElementById('cotizacion_actual').value = cotizacion.toLocaleString();
    document.getElementById('fecha_cotizacion').textContent = new Date().toLocaleString();
}

async function obtenerProducto() {
    const url = document.getElementById('amazon_url').value;
    
    if (!url) {
        alert('Ingresa una URL de Amazon válida');
        return;
    }
    
    const btn = event.target;
    btn.innerHTML = '<i class="bi bi-hourglass-split"></i> Cargando...';
    btn.disabled = true;
    
    try {
        // Aquí llamarías a tu API de Amazon
        const response = await fetch('<?= base_url("amazon/obtener") ?>', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({url: url})
        });
        
        const data = await response.json();
        
        if (data.success) {
            // Mostrar datos del producto
            document.getElementById('producto-nombre').textContent = data.nombre;
            document.getElementById('producto-precio').textContent = data.precio;
            document.getElementById('producto-imagen').src = data.imagen;
            document.getElementById('producto-disponibilidad').textContent = data.disponibilidad;
            document.getElementById('producto-info').style.display = 'block';
            
            // Llenar campos ocultos
            document.getElementById('nombre_producto').value = data.nombre;
            document.getElementById('precio_usd').value = data.precio;
            
            // Actualizar resumen
            document.getElementById('resumen-precio').textContent = data.precio;
            actualizarResumen();
        } else {
            alert('Error obteniendo datos: ' + data.message);
        }
        
    } catch (error) {
        alert('Error de conexión');
    }
    
    btn.innerHTML = '<i class="bi bi-search"></i> Obtener Datos';
    btn.disabled = false;
}

function calcularTotal() {
    const precio = parseFloat(document.getElementById('precio_usd').value) || 0;
    const tipo = document.getElementById('tipo_cambio').value;
    const provincia = document.getElementById('provincia').value;
    
    if (!precio || !tipo || !provincia) {
        alert('Complete todos los campos primero');
        return;
    }
    
    const cotizacion = cotizacionesActuales[tipo];
    const impuestos = impuestosPorProvincia[provincia];
    const envio = 25;
    
    // Cálculo completo
    const subtotalUSD = precio + envio;
    const baseARS = precio * cotizacion;
    const impuestosARS = (baseARS * (impuestos.iva + impuestos.derechos + impuestos.adicionales) / 100);
    const envioARS = envio * cotizacion;
    const totalARS = baseARS + impuestosARS + envioARS;
    
    // Actualizar resumen
    document.getElementById('resumen-subtotal').textContent = subtotalUSD.toFixed(2);
    document.getElementById('resumen-cambio').textContent = cotizacion.toLocaleString();
    document.getElementById('resumen-impuestos').textContent = impuestosARS.toLocaleString();
    document.getElementById('resumen-total').textContent = totalARS.toLocaleString();
    
    // Campo oculto para envío
    document.getElementById('total_ars').value = totalARS.toFixed(2);
}
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>