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
            background: linear-gradient(to left, #FFD700, #FF8C00);
            color: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .step-card { border-left: 4px solid #ffc107; transition: all 0.3s ease; }
        .step-card:hover { transform: translateY(-2px); box-shadow: 0 8px 25px rgba(0,0,0,0.1); }
        .categoria-info { background: linear-gradient(to left, #FFD700, #FF8C00); color: white; border-radius: 10px; padding: 15px; margin-top: 10px; }
        .calculo-resultado { background: linear-gradient(135deg, #003e79ff 0%, #146eb4 100%); color: white; border-radius: 10px; padding: 20px; }
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
            <div class="text-dark url-section">
                <h5><i class="bi text-dark bi-link-45deg"></i> Obtener Datos Automáticamente</h5>
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
                                    <input type="number" class="form-control" id="envio_usd" name="envio_usd" placeholder="10.00" step="0.01" min="0" max="1000" value="<?= set_value('envio_usd', $old_input['envio_usd'] ?? '10.00') ?>" required onchange="simularCalculoEnTiempoReal()">
                                    <span class="input-group-text">USD</span>
                                </div>
                                <div class="form-text text-warning">
                                    <i class="bi bi-exclamation-triangle"></i> 
                                    <strong>Importante:</strong> Este es solo un promedio estimado ($10 USD). El costo de envío puede variar considerablemente según el vendedor, peso, tamaño y destino. <strong>Debes verificar el costo real en Amazon y ajustar este valor antes de calcular.</strong>
                                </div>
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
                                <div id="categoria-info" class="categoria-info text-dark" style="display: none;">
                                    <h6><i class="bi bi-info-circle text-dark"></i> Información</h6>
                                    <div class="row text-dark text-center">
                                        <div class="text-dark col-6">
                                            <div class="border-end text-dark border-light">
                                                <h4 class="mb-1" id="categoria-arancel">0%</h4>
                                                <small>Arancel</small>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <h4 class="text-dark mb-1" id="categoria-iva">21%</h4>
                                            <small>IVA</small>
                                        </div>
                                    </div>
                                    <div class="text-dark mt-2"><small id="categoria-descripcion">-</small></div>
                                </div>
                                
                            </div>
                            <div class="accordion mt-3" id="accordionCategorias">
                            <div class="accordion-item bg-dark border-secondary">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed bg-dark text-light" type="button" data-bs-toggle="collapse" data-bs-target="#collapseCategorias">
                                        <i class="bi bi-question-circle text-warning me-2"></i> ¿No sabes qué categoría usar? Ver ejemplos
                                    </button>
                                </h2>
                                <div id="collapseCategorias" class="accordion-collapse collapse" data-bs-parent="#accordionCategorias">
                                    <div class="accordion-body bg-dark text-light" style="max-height: 400px; overflow-y: auto;">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <h6 class="text-warning">📱 1 – Electrónica (Celulares)</h6>
                                                <ul class="small mb-3">
                                                    <li>Teléfonos celulares (iPhone, Samsung, Xiaomi)</li>
                                                    <li>Fundas, vidrios templados, cargadores</li>
                                                    <li>Smartwatches y pulseras inteligentes</li>
                                                </ul>
                                                
                                                <h6 class="text-warning">📲 2 – Electrónica (Tablets)</h6>
                                                <ul class="small mb-3">
                                                    <li>Tablets (iPad, Samsung Galaxy Tab)</li>
                                                    <li>Stylus, fundas, protectores</li>
                                                    <li>Teclados Bluetooth para tablet</li>
                                                </ul>
                                                
                                                <h6 class="text-warning">💻 3 – Electrónica (Computadoras)</h6>
                                                <ul class="small mb-3">
                                                    <li>Laptops, notebooks, PC de escritorio</li>
                                                    <li>Placas de video, motherboards, procesadores</li>
                                                    <li>Teclados, ratones, monitores</li>
                                                    <li>Memorias USB, hubs, adaptadores</li>
                                                </ul>
                                                
                                                <h6 class="text-warning">🎮 4 – Consolas y Videojuegos</h6>
                                                <ul class="small mb-3">
                                                    <li>Consolas: PlayStation 5, Xbox Series X, Nintendo Switch</li>
                                                    <li>Mandos y controles</li>
                                                    <li>Bases de carga, fundas, cables HDMI</li>
                                                </ul>
                                                
                                                <h6 class="text-warning">🕹️ 5 – Videojuegos</h6>
                                                <ul class="small mb-3">
                                                    <li>Juegos físicos (discos, cartuchos)</li>
                                                    <li>Códigos digitales</li>
                                                    <li>Tarjetas de regalo (PSN, Xbox Live, Nintendo eShop)</li>
                                                </ul>
                                                
                                                <h6 class="text-warning">☕ 6 – Electrodomésticos Pequeños</h6>
                                                <ul class="small mb-3">
                                                    <li>Cafeteras, licuadoras, procesadoras</li>
                                                    <li>Microondas, tostadoras</li>
                                                    <li>Planchas, aspiradoras, freidoras de aire</li>
                                                </ul>
                                                
                                                <h6 class="text-warning">🧊 7 – Electrodomésticos Grandes</h6>
                                                <ul class="small mb-3">
                                                    <li>Heladeras / Freezers</li>
                                                    <li>Lavarropas, secarropas</li>
                                                    <li>Cocinas, hornos, lavavajillas</li>
                                                    <li>Aires acondicionados</li>
                                                </ul>
                                                
                                                <h6 class="text-warning">👕 8 – Ropa</h6>
                                                <ul class="small mb-3">
                                                    <li>Remeras, pantalones, camperas</li>
                                                    <li>Ropa interior, medias</li>
                                                    <li>Abrigos, trajes de baño</li>
                                                    <li>Uniformes de trabajo</li>
                                                </ul>
                                                
                                                <h6 class="text-warning">👟 9 – Calzado</h6>
                                                <ul class="small mb-3">
                                                    <li>Zapatillas deportivas</li>
                                                    <li>Botas, sandalias, mocasines</li>
                                                    <li>Zapatos de vestir</li>
                                                    <li>Calzado de seguridad / trabajo</li>
                                                </ul>
                                                
                                                <h6 class="text-warning">🧵 10 – Telas</h6>
                                                <ul class="small mb-3">
                                                    <li>Rollos de tela (algodón, lino, poliéster)</li>
                                                    <li>Fieltros y encajes</li>
                                                    <li>Retazos decorativos, vinilos textiles</li>
                                                </ul>
                                                
                                                <h6 class="text-warning">🧶 11 – Hilados</h6>
                                                <ul class="small mb-3">
                                                    <li>Hilos de lana, algodón o seda</li>
                                                    <li>Agujas de tejer y crochet</li>
                                                    <li>Ovillos, madejas, kits de bordado</li>
                                                </ul>
                                            </div>
                                            
                                            <div class="col-md-6">
                                                <h6 class="text-warning">🔧 12 – Herramientas</h6>
                                                <ul class="small mb-3">
                                                    <li>Taladros, sierras, lijadoras</li>
                                                    <li>Multímetros, destornilladores eléctricos</li>
                                                    <li>Kits de reparación automotriz</li>
                                                    <li>Llaves, martillos, prensas</li>
                                                </ul>
                                                
                                                <h6 class="text-warning">⚙️ 13 – Maquinarias</h6>
                                                <ul class="small mb-3">
                                                    <li>Compresores, generadores eléctricos</li>
                                                    <li>Soldadoras, cortadoras industriales</li>
                                                    <li>Bombas de agua</li>
                                                    <li>Equipos para taller o fábrica</li>
                                                </ul>
                                                
                                                <h6 class="text-warning">📚 14 – Libros</h6>
                                                <ul class="small mb-3">
                                                    <li>Libros físicos o ediciones Kindle</li>
                                                    <li>Manuales técnicos o universitarios</li>
                                                    <li>Cómics y novelas gráficas</li>
                                                    <li>Revistas especializadas</li>
                                                </ul>
                                                
                                                <h6 class="text-warning">🧸 15 – Juguetes</h6>
                                                <ul class="small mb-3">
                                                    <li>Muñecos, figuras de acción</li>
                                                    <li>Juegos de mesa, rompecabezas</li>
                                                    <li>LEGO y sets de construcción</li>
                                                    <li>Vehículos a control remoto</li>
                                                </ul>
                                                
                                                <h6 class="text-warning">⚽ 16 – Deportes</h6>
                                                <ul class="small mb-3">
                                                    <li>Pesas, bandas elásticas, esterillas de yoga</li>
                                                    <li>Ropa deportiva y zapatillas de running</li>
                                                    <li>Palos de golf, raquetas de tenis</li>
                                                    <li>Cascos y protecciones de ciclismo</li>
                                                    <li>Artículos de camping o senderismo</li>
                                                </ul>
                                                
                                                <h6 class="text-warning">🏡 17 – Hogar y Jardín</h6>
                                                <ul class="small mb-3">
                                                    <li>Muebles (sillas, mesas, estanterías)</li>
                                                    <li>Decoración (cuadros, lámparas, alfombras)</li>
                                                    <li>Artículos de jardinería (macetas, tijeras de podar)</li>
                                                    <li>Organizadores, utensilios de cocina</li>
                                                </ul>
                                                
                                                <h6 class="text-warning">💄 18 – Belleza y Cuidado</h6>
                                                <ul class="small mb-3">
                                                    <li>Perfumes y colonias</li>
                                                    <li>Cremas faciales y corporales</li>
                                                    <li>Maquillaje (labiales, bases, máscaras)</li>
                                                    <li>Secadores y planchas de cabello</li>
                                                    <li>Afeitadoras eléctricas y depiladoras</li>
                                                </ul>
                                                
                                                <h6 class="text-warning">🏎️ 19 – Automotriz</h6>
                                                <ul class="small mb-3">
                                                    <li>Filtros de aceite / aire / combustible</li>
                                                    <li>Baterías, bujías, correas</li>
                                                    <li>Ceras, limpiadores y líquidos de freno</li>
                                                    <li>Fundas de asiento, luces LED, GPS para autos</li>
                                                </ul>
                                                
                                                <h6 class="text-warning">🎸 20 – Música e Instrumentos</h6>
                                                <ul class="small mb-3">
                                                    <li>Guitarras, bajos, baterías</li>
                                                    <li>Micrófonos, consolas de sonido</li>
                                                    <li>Auriculares profesionales</li>
                                                    <li>Partituras, accesorios musicales</li>
                                                </ul>
                                                
                                                <h6 class="text-warning">📦 21 – Otros</h6>
                                                <ul class="small mb-3">
                                                    <li>Artículos difíciles de clasificar</li>
                                                    <li>Souvenirs, merch, artículos de colección</li>
                                                    <li>Productos multipropósito o kits variados</li>
                                                    <li>Repuestos específicos sin categoría clara</li>
                                                </ul>
                                            </div>
                                        </div>
                                        <div class="alert alert-warning mt-3">
                                            <i class="bi bi-lightbulb"></i> <strong>Tip:</strong> Si tu producto no encaja perfectamente en ninguna categoría, elige la más cercana. Los aranceles varían según la categoría, así que una buena elección es importante para un cálculo preciso.
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        </div>
                    </div>
                </div>
                        <!-- Acordeón de ayuda: Ejemplos de categorías -->
                        

                

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
                <div class=" mb-4" id="resultado-calculo" style="display: none;">
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
    
    // 1. Llenar nombre del producto
    document.getElementById('nombre_producto').value = productoObtenido.nombre;
    
    // 2. Llenar precio
    document.getElementById('precio_usd').value = productoObtenido.precio || '';
    
    // 3. Guardar URL en campo hidden
    document.getElementById('amazon_url_hidden').value = document.getElementById('amazon_url_input').value;
    
    // 4. Configurar envío por defecto si está vacío
    if (!document.getElementById('envio_usd').value || document.getElementById('envio_usd').value == '0') {
        document.getElementById('envio_usd').value = '10.00';
    }
    
    // 5. Aplicar sugerencia de categoría ANTES de limpiar preview
    aplicarSugerenciaCategoria(productoObtenido);
    
    // 6. Limpiar preview
    limpiarPreview();
    
    // 7. Scroll al formulario
    document.querySelector('.card-custom2').scrollIntoView({ behavior: 'smooth' });
    
    // 8. Validar y simular si está completo
    setTimeout(() => {
        if (validarFormularioCompleto()) {
            simularCalculoEnTiempoReal();
        }
    }, 300);
    
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
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>