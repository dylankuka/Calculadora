<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Nueva Calculadora de Impuestos - TaxImporter</title>
    <link rel="stylesheet" href="<?= base_url('css/ind.css') ?>">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .url-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .step-card { border-left: 4px solid #ffc107; transition: all 0.3s ease; }
        .step-card:hover { transform: translateY(-2px); box-shadow: 0 8px 25px rgba(0,0,0,0.1); }
        .categoria-info { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border-radius: 10px; padding: 15px; margin-top: 10px; }
        .calculo-resultado { background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); color: white; border-radius: 10px; padding: 20px; }
        .franquicia-badge { background: linear-gradient(45deg, #f093fb 0%, #f5576c 100%); color: white; padding: 8px 16px; border-radius: 20px; font-weight: bold; }
        .producto-preview { background: white; border-radius: 10px; padding: 15px; margin-top: 15px; display: none; }
        .loading-spinner { display: none; }
    </style>
</head>
<body class="bg-dark">

<!-- Logo -->
<div class="position-absolute" style="top: 5px; left: 22px; z-index: 1000;">
    <a href="<?= base_url() ?>">
        <img src="<?= base_url('img/taximporterlogo.png') ?>" alt="Logo TaxImporter" style="max-width: 70px; height: auto; filter: drop-shadow(2px 2px 6px rgba(0,0,0,1.9));">
    </a>
</div>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark card-custom">
    <div class="container">
        <a class="navbar-brand textcolor" href="<?= base_url('/historial') ?>">
            <i class="bi bi-calculator"></i> TaxImporter
        </a>
        <div class="navbar-nav ms-auto">
            <span class="navbar-text me-3 textcolor">👤 <strong><?= esc(session()->get('usuario_nombre')) ?></strong></span>
            <a class="btn btn-outline-dark btn-sm" href="<?= base_url('usuario/logout') ?>"><i class="bi bi-box-arrow-right textcolor"></i> Salir</a>
        </div>
    </div>
</nav>

<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-lg-12 col-xl-10">
            
            <!-- Breadcrumb -->
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= base_url('/historial') ?>" class="text-light"><i class="bi bi-house"></i> Historial</a></li>
                    <li class="breadcrumb-item active text-light"><i class="bi bi-plus-circle"></i> Nueva Calculadora</li>
                </ol>
            </nav>

            <!-- SECCIÓN NUEVA: Obtener datos desde URL -->
            <div class="url-section">
                <h5><i class="bi bi-link-45deg"></i> Obtener Datos Automáticamente</h5>
                <p class="mb-3">Pega la URL de Amazon y obtendremos automáticamente los datos del producto</p>
                <div class="input-group mb-3">
                    <input type="text" class="form-control form-control-lg" id="amazon_url_input" placeholder="https://www.amazon.com/dp/B073JYC4XM">
                    <button class="btn btn-warning btn-lg" type="button" onclick="obtenerDatosProducto()">
                        <span class="normal-text"><i class="bi bi-download"></i> Obtener Datos</span>
                        <span class="loading-spinner"><span class="spinner-border spinner-border-sm"></span> Obteniendo...</span>
                    </button>
                </div>
                <small><i class="bi bi-info-circle"></i> Ejemplo: https://www.amazon.com/dp/B073JYC4XM</small>
                
                <!-- Preview del producto obtenido -->
                <div id="producto-preview" class="producto-preview">
                    <div class="row align-items-center">
                        <div class="col-md-2 text-center">
                            <img id="preview-imagen" src="" class="img-fluid rounded" style="max-height: 100px;">
                        </div>
                        <div class="col-md-10">
                            <h6 class="text-dark mb-1" id="preview-nombre">-</h6>
                            <p class="mb-1 text-muted small">
                                <strong>Precio:</strong> <span id="preview-precio">-</span> | 
                                <strong>Marca:</strong> <span id="preview-marca">-</span>
                            </p>
                            <button class="btn btn-success btn-sm" onclick="usarDatosProducto()">
                                <i class="bi bi-check-circle"></i> Usar estos datos
                            </button>
                            <button class="btn btn-secondary btn-sm" onclick="limpiarPreview()">
                                <i class="bi bi-x"></i> Cancelar
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Alerta informativa -->
            <div class="alert alert-info alert-dismissible fade show" role="alert">
                <h5 class="alert-heading"><i class="bi bi-info-circle"></i> Modo de Entrada</h5>
                <p class="mb-0">Puedes obtener datos automáticamente desde Amazon o ingresar manualmente. Los cálculos son 100% reales según normativa AFIP.</p>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>

            <!-- Mensajes -->
            <?php if (isset($error)): ?>
                <div class="alert alert-danger"><i class="bi bi-exclamation-triangle"></i> <?= $error ?></div>
            <?php endif; ?>

            <?php if (isset($validation)): ?>
                <div class="alert alert-danger">
                    <h6><i class="bi bi-x-circle"></i> Errores de validación:</h6>
                    <ul class="mb-0"><?php foreach ($validation->getErrors() as $error): ?><li><?= esc($error) ?></li><?php endforeach; ?></ul>
                </div>
            <?php endif; ?>

            <!-- Formulario Principal -->
            <form id="calculoForm" action="<?= base_url('historial/calcular') ?>" method="post" novalidate>
                <?= csrf_field() ?>
                
                <!-- Hidden field para guardar URL -->
                <input type="hidden" id="amazon_url_hidden" name="amazon_url" value="">

                <!-- PASO 1: Datos del Producto -->
                <div class="card mb-4 shadow card-custom2">
                    <div class="card-header card-custom textcolor">
                        <h5><i class="bi bi-1-circle-fill text-warning"></i> Datos del Producto</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label for="nombre_producto" class="form-label"><i class="bi bi-tag"></i> Nombre del Producto *</label>
                                <input type="text" class="form-control" id="nombre_producto" name="nombre_producto" placeholder="Ej: iPhone 15 Pro Max 256GB" value="<?= set_value('nombre_producto', $old_input['nombre_producto'] ?? '') ?>" required>
                                <div class="form-text">Describe el producto que deseas importar desde Amazon</div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="precio_usd" class="form-label"><i class="bi bi-currency-dollar"></i> Precio del Producto *</label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" class="form-control" id="precio_usd" name="precio_usd" placeholder="999.99" step="0.01" min="0.01" max="50000" value="<?= set_value('precio_usd', $old_input['precio_usd'] ?? '') ?>" required onchange="simularCalculoEnTiempoReal()">
                                    <span class="input-group-text">USD</span>
                                </div>
                                <div class="form-text"><i class="bi bi-info-circle"></i> Precio en dólares según Amazon</div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="envio_usd" class="form-label"><i class="bi bi-truck"></i> Costo de Envío *</label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" class="form-control" id="envio_usd" name="envio_usd" placeholder="25.00" step="0.01" min="0" max="1000" value="<?= set_value('envio_usd', $old_input['envio_usd'] ?? '25.00') ?>" required onchange="simularCalculoEnTiempoReal()">
                                    <span class="input-group-text">USD</span>
                                </div>
                                <div class="form-text"><i class="bi bi-info-circle"></i> Promedio: $25 USD</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- PASO 2: Categoría -->
                <div class="card shadow card-custom2 mb-4">
                    <div class="card-header card-custom textcolor">
                        <h5><i class="bi bi-2-circle-fill text-warning"></i> Categoría del Producto</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-8">
                                <label for="categoria_id" class="form-label"><i class="bi bi-tag-fill"></i> Selecciona la Categoría *</label>
                                <select class="form-select" id="categoria_id" name="categoria_id" required onchange="mostrarInfoCategoria()">
                                    <option value="">-- Selecciona una categoría --</option>
                                    <?php if (isset($categorias) && !empty($categorias)): ?>
                                        <?php foreach ($categorias as $categoria): ?>
                                            <option value="<?= $categoria['id'] ?>" data-arancel="<?= $categoria['arancel_porcentaje'] ?>" data-exento="<?= $categoria['exento_iva'] ?>" data-descripcion="<?= esc($categoria['descripcion']) ?>" <?= set_select('categoria_id', $categoria['id'], ($old_input['categoria_id'] ?? '') == $categoria['id']) ?>>
                                                <?= esc($categoria['nombre']) ?> (Arancel: <?= $categoria['arancel_porcentaje'] ?>%)
                                            </option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <div id="categoria-info" class="categoria-info" style="display: none;">
                                    <h6><i class="bi bi-info-circle"></i> Información</h6>
                                    <div class="row text-center">
                                        <div class="col-6">
                                            <div class="border-end border-light">
                                                <h4 class="mb-1" id="categoria-arancel">0%</h4>
                                                <small>Arancel</small>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <h4 class="mb-1" id="categoria-iva">21%</h4>
                                            <small>IVA</small>
                                        </div>
                                    </div>
                                    <div class="mt-2"><small id="categoria-descripcion">-</small></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- PASO 3: Método de Pago -->
                <div class="card shadow card-custom2 mb-4">
                    <div class="card-header card-custom textcolor">
                        <h5><i class="bi bi-3-circle-fill text-warning"></i> Método de Pago</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <label class="form-label"><i class="bi bi-credit-card"></i> Forma de Pago *</label>
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="radio" name="metodo_pago" id="tarjeta" value="tarjeta" <?= set_radio('metodo_pago', 'tarjeta', ($old_input['metodo_pago'] ?? 'tarjeta') == 'tarjeta') ?> onchange="actualizarCotizacion()" required>
                                    <label class="form-check-label" for="tarjeta"><strong>💳 Tarjeta Argentina</strong><small class="d-block text-muted">Incluye percepciones AFIP (30%)</small></label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="metodo_pago" id="mep" value="MEP" <?= set_radio('metodo_pago', 'MEP', ($old_input['metodo_pago'] ?? '') == 'MEP') ?> onchange="actualizarCotizacion()">
                                    <label class="form-check-label" for="mep"><strong>📈 Dólar MEP/CCL</strong><small class="d-block text-muted">Sin percepciones adicionales</small></label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label"><i class="bi bi-currency-exchange"></i> Cotización Actual</label>
                                <div class="card resumen-card text-white">
                                    <div class="card-body p-3">
                                        <div class="d-flex justify-content-between">
                                            <div>
                                                <h4 class="mb-0">$<span id="cotizacion-actual">0.00</span></h4>
                                                <small>ARS por USD</small>
                                            </div>
                                            <div class="text-end">
                                                <button type="button" class="btn btn-sm btn-light" onclick="actualizarCotizaciones()"><i class="bi bi-arrow-clockwise"></i></button>
                                            </div>
                                        </div>
                                        <small class="opacity-75"><i class="bi bi-clock"></i> Actualizado: <span id="fecha-cotizacion"><?= date('d/m/Y H:i') ?></span></small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- RESULTADO DEL CÁLCULO -->
                <div class="card shadow mb-4" id="resultado-calculo" style="display: none;">
                    <div class="calculo-resultado">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5><i class="bi bi-calculator"></i> Resultado del Cálculo</h5>
                            <div><span class="franquicia-badge" id="estado-franquicia">Calculando...</span></div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <h6 class="border-bottom border-light pb-2 mb-3">💵 Valores Base</h6>
                                <div class="mb-2 d-flex justify-content-between"><span>Precio producto:</span><strong>$<span id="resumen-precio-usd">0.00</span> USD</strong></div>
                                <div class="mb-2 d-flex justify-content-between"><span>Envío:</span><strong>$<span id="resumen-envio-usd">0.00</span> USD</strong></div>
                                <div class="mb-2 d-flex justify-content-between"><span>Valor CIF:</span><strong>$<span id="resumen-cif-usd">0.00</span> USD</strong></div>
                                <div class="mb-2 d-flex justify-content-between"><span>Base en ARS:</span><strong>$<span id="resumen-base-ars">0.00</span></strong></div>
                            </div>
                            <div class="col-md-6">
                                <h6 class="border-bottom border-light pb-2 mb-3">🇦🇷 Impuestos</h6>
                                <div class="mb-2 d-flex justify-content-between"><span>Aranceles:</span><strong>$<span id="resumen-aranceles-ars">0.00</span></strong></div>
                                <div class="mb-2 d-flex justify-content-between"><span>Tasa estadística:</span><strong>$<span id="resumen-tasa-estadistica-ars">0.00</span></strong></div>
                                <div class="mb-2 d-flex justify-content-between"><span>IVA (21%):</span><strong>$<span id="resumen-iva-ars">0.00</span></strong></div>
                                <div class="mb-2 d-flex justify-content-between" id="percepcion-row" style="display: none;"><span>Percepción AFIP:</span><strong>$<span id="resumen-percepcion-ars">0.00</span></strong></div>
                            </div>
                        </div>
                        <hr class="border-light my-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <h4 class="mb-0">TOTAL FINAL:</h4>
                            <h3 class="mb-0 text-warning">$<span id="resumen-total-ars">0.00</span></h3>
                        </div>
                        <div class="mt-3 pt-3 border-top border-light">
                            <div class="row text-center">
                                <div class="col-md-4"><small>💱 Cotización</small><br><strong><span id="tipo-cambio-usado">-</span>: $<span id="cotizacion-usada">0.00</span></strong></div>
                                <div class="col-md-4"><small>📦 Categoría</small><br><strong id="categoria-usada">-</strong></div>
                                <div class="col-md-4"><small>💰 Total impuestos</small><br><strong>$<span id="solo-impuestos-ars">0.00</span></strong></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- BOTONES -->
                <div class="card shadow card-custom2 mb-4">
                    <div class="card-body">
                        <div class="d-grid gap-2 d-md-flex justify-content-md-between">
                            <a href="<?= base_url('/historial') ?>" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Volver</a>
                            <div class="d-grid gap-2 d-md-flex">
                                <button type="button" class="btn btn-warning" onclick="simularCalculoCompleto()"><i class="bi bi-calculator"></i> Calcular Impuestos</button>
                                <button type="submit" class="btn btn-success" id="btn-guardar" disabled><i class="bi bi-save"></i> Guardar Cálculo</button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
const cotizacionesActuales = <?= json_encode($cotizaciones ?? ['tarjeta' => 1943.50, 'MEP' => 1485.70]) ?>;
let calculoActual = null;
let productoObtenido = null;

document.addEventListener('DOMContentLoaded', function() {
    actualizarCotizacion();
    const categoriaSelect = document.getElementById('categoria_id');
    if (categoriaSelect.value) mostrarInfoCategoria();
});

// ✅ NUEVA FUNCIÓN: Obtener datos del producto desde URL
async function obtenerDatosProducto() {
    const url = document.getElementById('amazon_url_input').value.trim();
    const btn = event.target;
    
    if (!url) {
        alert('⚠️ Por favor ingresa una URL de Amazon');
        return;
    }
    
    // Mostrar loading
    btn.querySelector('.normal-text').style.display = 'none';
    btn.querySelector('.loading-spinner').style.display = 'inline-block';
    btn.disabled = true;
    
    try {
        const response = await fetch('<?= base_url("amazon/obtener") ?>', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ url: url })
        });
        
        const resultado = await response.json();
        
        if (resultado.success) {
            productoObtenido = resultado.data;
            mostrarPreviewProducto(resultado.data);
        } else {
            throw new Error(resultado.message || 'Error obteniendo producto');
        }
    } catch (error) {
        console.error('Error:', error);
        alert('❌ Error: ' + error.message);
    } finally {
        btn.querySelector('.normal-text').style.display = 'inline-block';
        btn.querySelector('.loading-spinner').style.display = 'none';
        btn.disabled = false;
    }
}

// Mostrar preview del producto
function mostrarPreviewProducto(producto) {
    document.getElementById('preview-nombre').textContent = producto.nombre;
    document.getElementById('preview-precio').textContent = `${producto.moneda} ${producto.precio}`;
    document.getElementById('preview-marca').textContent = producto.marca || 'N/A';
    document.getElementById('preview-imagen').src = producto.imagen || '';
    document.getElementById('producto-preview').style.display = 'block';
}

// Usar los datos obtenidos
function usarDatosProducto() {
    if (!productoObtenido) return;
    
    document.getElementById('nombre_producto').value = productoObtenido.nombre;
    document.getElementById('precio_usd').value = productoObtenido.precio || '';
    document.getElementById('amazon_url_hidden').value = document.getElementById('amazon_url_input').value;
    
    // Mantener envío por defecto
    if (!document.getElementById('envio_usd').value) {
        document.getElementById('envio_usd').value = '25.00';
    }
    
    limpiarPreview();
    
    // Scroll al formulario
    document.querySelector('.card-custom2').scrollIntoView({ behavior: 'smooth' });
    
    // Validar y simular si está completo
    if (validarFormularioCompleto()) {
        simularCalculoEnTiempoReal();
    }
    
    mostrarAlerta('✅ Datos del producto cargados exitosamente', 'success');
}

function limpiarPreview() {
    document.getElementById('producto-preview').style.display = 'none';
    productoObtenido = null;
}

// Resto de funciones existentes...
function mostrarInfoCategoria() {
    const select = document.getElementById('categoria_id');
    const selectedOption = select.options[select.selectedIndex];
    
    if (selectedOption.value) {
        const arancel = selectedOption.dataset.arancel;
        const exento = selectedOption.dataset.exento === '1';
        const descripcion = selectedOption.dataset.descripcion;
        
        document.getElementById('categoria-arancel').textContent = arancel + '%';
        document.getElementById('categoria-iva').textContent = exento ? 'EXENTO' : '21%';
        document.getElementById('categoria-descripcion').textContent = descripcion;
        document.getElementById('categoria-info').style.display = 'block';
        
        if (validarFormularioCompleto()) simularCalculoEnTiempoReal();
    } else {
        document.getElementById('categoria-info').style.display = 'none';
    }
}

function actualizarCotizacion() {
    const metodoPago = document.querySelector('input[name="metodo_pago"]:checked')?.value || 'tarjeta';
    const cotizacion = cotizacionesActuales[metodoPago] || 0;
    document.getElementById('cotizacion-actual').textContent = cotizacion.toLocaleString('es-AR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    if (validarFormularioCompleto()) simularCalculoEnTiempoReal();
}

function validarFormularioCompleto() {
    const precio = parseFloat(document.getElementById('precio_usd').value) || 0;
    const envio = parseFloat(document.getElementById('envio_usd').value) || 0;
    const categoria = document.getElementById('categoria_id').value;
    const metodoPago = document.querySelector('input[name="metodo_pago"]:checked');
    const nombre = document.getElementById('nombre_producto').value.trim();
    return precio > 0 && envio >= 0 && categoria && metodoPago && nombre.length > 0;
}

async function simularCalculoEnTiempoReal() {
    if (!validarFormularioCompleto()) {
        document.getElementById('resultado-calculo').style.display = 'none';
        document.getElementById('btn-guardar').disabled = true;
        return;
    }
    
    const datos = {
        precio_usd: parseFloat(document.getElementById('precio_usd').value),
        envio_usd: parseFloat(document.getElementById('envio_usd').value),
        categoria_id: parseInt(document.getElementById('categoria_id').value),
        metodo_pago: document.querySelector('input[name="metodo_pago"]:checked').value
    };
    
    try {
        const response = await fetch('<?= base_url("historial/simular") ?>', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            body: JSON.stringify(datos)
        });
        
        const resultado = await response.json();
        
        if (resultado.success) {
            mostrarResultadoCalculo(resultado.data);
            calculoActual = resultado.data;
            document.getElementById('btn-guardar').disabled = false;
        } else {
            throw new Error(resultado.message || 'Error en el cálculo');
        }
    } catch (error) {
        console.error('Error:', error);
        mostrarAlerta('Error al calcular: ' + error.message, 'danger');
    }
}

function mostrarResultadoCalculo(calculo) {
    const baseARS = calculo.impuestos_ars.valor_cif_ars || (calculo.datos_base.valor_cif_usd * calculo.datos_base.cotizacion);
    document.getElementById('resumen-precio-usd').textContent = formatNumber(calculo.datos_base.precio_usd);
    document.getElementById('resumen-envio-usd').textContent = formatNumber(calculo.datos_base.envio_usd || 0);
    document.getElementById('resumen-cif-usd').textContent = formatNumber(calculo.datos_base.valor_cif_usd);
    document.getElementById('resumen-base-ars').textContent = formatNumber(baseARS);
    document.getElementById('resumen-aranceles-ars').textContent = formatNumber(calculo.impuestos_ars.aranceles_ars || 0);
    document.getElementById('resumen-tasa-estadistica-ars').textContent = formatNumber(calculo.impuestos_ars.tasa_estadistica_ars || 0);
    document.getElementById('resumen-iva-ars').textContent = formatNumber(calculo.impuestos_ars.iva_ars || 0);
    
    const percepcionARS = calculo.impuestos_ars.percepcion_ganancias_ars || 0;
    if (percepcionARS > 0) {
        document.getElementById('resumen-percepcion-ars').textContent = formatNumber(percepcionARS);
        document.getElementById('percepcion-row').style.display = 'flex';
    } else {
        document.getElementById('percepcion-row').style.display = 'none';
    }
    
    document.getElementById('resumen-total-ars').textContent = formatNumber(calculo.totales.total_ars);
    document.getElementById('solo-impuestos-ars').textContent = formatNumber(calculo.totales.total_impuestos_ars);
    document.getElementById('tipo-cambio-usado').textContent = calculo.datos_base.metodo_pago.toUpperCase();
    document.getElementById('cotizacion-usada').textContent = formatNumber(calculo.datos_base.cotizacion);
    document.getElementById('categoria-usada').textContent = calculo.datos_base.categoria;
    
    const bajoFranquicia = calculo.datos_base.bajo_franquicia || calculo.datos_base.valor_cif_usd <= 400;
    document.getElementById('estado-franquicia').textContent = bajoFranquicia ? '✅ Bajo Franquicia (≤$400)' : '⚠️ Sobre Franquicia (>$400)';
    
    document.getElementById('resultado-calculo').style.display = 'block';
    document.getElementById('resultado-calculo').scrollIntoView({ behavior: 'smooth', block: 'nearest' });
}

function formatNumber(num) {
    return parseFloat(num).toLocaleString('es-AR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

function mostrarAlerta(mensaje, tipo = 'info') {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${tipo} alert-dismissible fade show`;
    alertDiv.innerHTML = `${mensaje}<button type="button" class="btn-close" data-bs-dismiss="alert"></button>`;
    const container = document.querySelector('.container');
    container.insertBefore(alertDiv, container.firstChild);
    setTimeout(() => alertDiv.remove(), 5000);
}

async function actualizarCotizaciones() {
    const btn = event.target;
    const originalHTML = btn.innerHTML;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';
    btn.disabled = true;
    
    try {
        const response = await fetch('<?= base_url("dolar/actualizar") ?>');
        const resultado = await response.json();
        
        if (resultado.success) {
            Object.assign(cotizacionesActuales, resultado.data);
            actualizarCotizacion();
            if (validarFormularioCompleto()) simularCalculoEnTiempoReal();
            document.getElementById('fecha-cotizacion').textContent = resultado.timestamp;
            mostrarAlerta('✅ Cotizaciones actualizadas exitosamente', 'success');
        } else {
            throw new Error(resultado.message);
        }
    } catch (error) {
        console.error('Error:', error);
        mostrarAlerta('❌ Error al actualizar cotizaciones', 'danger');
    } finally {
        btn.innerHTML = originalHTML;
        btn.disabled = false;
    }
}

document.getElementById('precio_usd').addEventListener('input', simularCalculoEnTiempoReal);
document.getElementById('envio_usd').addEventListener('input', simularCalculoEnTiempoReal);
document.getElementById('categoria_id').addEventListener('change', simularCalculoEnTiempoReal);
document.getElementById('nombre_producto').addEventListener('input', function() {
    if (validarFormularioCompleto()) simularCalculoEnTiempoReal();
});

document.querySelectorAll('input[name="metodo_pago"]').forEach(radio => {
    radio.addEventListener('change', function() {
        actualizarCotizacion();
        simularCalculoEnTiempoReal();
    });
});

function simularCalculoCompleto() {
    if (!validarFormularioCompleto()) {
        mostrarAlerta('⚠️ Por favor completa todos los campos requeridos', 'warning');
        return;
    }
    simularCalculoEnTiempoReal();
}

document.getElementById('calculoForm').addEventListener('submit', function(e) {
    if (!validarFormularioCompleto()) {
        e.preventDefault();
        mostrarAlerta('⚠️ Por favor completa todos los campos antes de guardar', 'warning');
        return false;
    }
    if (!calculoActual) {
        e.preventDefault();
        mostrarAlerta('⚠️ Por favor calcula los impuestos antes de guardar', 'warning');
        return false;
    }
    return true;
});

// Detectar Enter en el campo de URL
document.getElementById('amazon_url_input').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        e.preventDefault();
        obtenerDatosProducto();
    }
});

/**
 * Mapeo automático de categorías de Amazon a categorías locales
 * Esto sugiere una categoría basada en la categoría del producto de Amazon
 */
function sugerirCategoria(categoriaAmazon) {
    if (!categoriaAmazon) return null;
    
    const categoria = categoriaAmazon.toLowerCase();
    
    // Mapeo de categorías
    const mapeo = {
        // Electrónica (16%)
        'electronics': 1,
        'computers': 1,
        'tablets': 1,
        'smartphones': 1,
        'laptops': 1,
        'cameras': 1,
        'headphones': 1,
        'cell phones': 1,
        'accessories': 1,
        
        // Consolas y Videojuegos (20%)
        'video games': 2,
        'gaming': 2,
        'playstation': 2,
        'xbox': 2,
        'nintendo': 2,
        'console': 2,
        
        // Ropa y Calzado (20%)
        'clothing': 3,
        'fashion': 3,
        'shoes': 3,
        'apparel': 3,
        'footwear': 3,
        
        // Electrodomésticos (20%)
        'appliances': 4,
        'home': 4,
        'kitchen': 4,
        
        // Juguetes (20%)
        'toys': 5,
        'games': 5,
        'kids': 5,
        
        // Libros (0% - exento IVA)
        'books': 6,
        'kindle': 6,
        'ebooks': 6,
        
        // Herramientas (12.6%)
        'tools': 7,
        'hardware': 7,
        'automotive': 7,
        
        // Deportes (20%)
        'sports': 8,
        'outdoors': 8,
        'fitness': 8,
        
        // Salud y Belleza (20%)
        'beauty': 9,
        'health': 9,
        'personal care': 9,
        
        // Oficina (16%)
        'office': 10,
        'supplies': 10,
        'stationery': 10
    };
    
    // Buscar coincidencias
    for (const [keyword, categoriaId] of Object.entries(mapeo)) {
        if (categoria.includes(keyword)) {
            return categoriaId;
        }
    }
    
    return null; // No se encontró mapeo
}

/**
 * Aplicar sugerencia de categoría cuando se obtiene un producto
 */
function aplicarSugerenciaCategoria(producto) {
    if (!producto.categoria) return;
    
    const categoriaIdSugerida = sugerirCategoria(producto.categoria);
    
    if (categoriaIdSugerida) {
        const selectCategoria = document.getElementById('categoria_id');
        
        // Verificar si existe esa opción
        const opcion = selectCategoria.querySelector(`option[value="${categoriaIdSugerida}"]`);
        
        if (opcion) {
            selectCategoria.value = categoriaIdSugerida;
            mostrarInfoCategoria();
            
            // Mostrar notificación
            const categoriaNombre = opcion.textContent;
            mostrarAlerta(`💡 Categoría sugerida: ${categoriaNombre}`, 'info');
        }
    }
}

// Modificar la función usarDatosProducto para incluir la sugerencia
function usarDatosProductoConSugerencia() {
    if (!productoObtenido) return;
    
    document.getElementById('nombre_producto').value = productoObtenido.nombre;
    document.getElementById('precio_usd').value = productoObtenido.precio || '';
    document.getElementById('amazon_url_hidden').value = document.getElementById('amazon_url_input').value;
    
    if (!document.getElementById('envio_usd').value) {
        document.getElementById('envio_usd').value = '25.00';
    }
    
    // Aplicar sugerencia de categoría
    aplicarSugerenciaCategoria(productoObtenido);
    
    limpiarPreview();
    document.querySelector('.card-custom2').scrollIntoView({ behavior: 'smooth' });
    
    if (validarFormularioCompleto()) {
        simularCalculoEnTiempoReal();
    }
    
    mostrarAlerta('✅ Datos del producto cargados exitosamente', 'success');
}
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>