<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Rainforest API - TaxImporter</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); 
            min-height: 100vh; 
            padding: 40px 0; 
        }
        .test-card { 
            background: white; 
            border-radius: 15px; 
            box-shadow: 0 10px 40px rgba(0,0,0,0.2); 
        }
        .result-box { 
            background: #d4edda; 
            border-left: 4px solid #28a745; 
            padding: 15px; 
            border-radius: 5px; 
            margin: 10px 0; 
        }
        .error-box { 
            background: #fff3cd; 
            border-left: 4px solid #dc3545; 
            padding: 15px; 
            border-radius: 5px; 
            margin: 10px 0; 
        }
        .loading-box {
            background: #cfe2ff;
            border-left: 4px solid #0d6efd;
            padding: 15px;
            border-radius: 5px;
            margin: 10px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="test-card p-5">
                    <h1 class="text-center mb-4">
                        <i class="bi bi-lightning-charge text-warning"></i>
                        Test Rainforest API
                    </h1>
                    
                    <div class="alert alert-info">
                        <strong><i class="bi bi-info-circle"></i> Información:</strong>
                        Esta página prueba la integración con Rainforest API para obtener datos de productos de Amazon.
                    </div>

                    <!-- Test 1: Verificar Configuración -->
                    <div class="card mb-4">
                        <div class="card-header bg-primary text-white">
                            <h5><i class="bi bi-1-circle"></i> Verificar Configuración de API</h5>
                        </div>
                        <div class="card-body">
                            <p class="text-muted">Verifica que la API key esté correctamente configurada en el .env</p>
                            <button class="btn btn-primary" onclick="testConfiguracion()">
                                <i class="bi bi-gear"></i> Verificar Configuración
                            </button>
                            <div id="result-config" class="mt-3"></div>
                        </div>
                    </div>

                    <!-- Test 2: Test de Conexión -->
                    <div class="card mb-4">
                        <div class="card-header bg-success text-white">
                            <h5><i class="bi bi-2-circle"></i> Test de Conexión con Rainforest</h5>
                        </div>
                        <div class="card-body">
                            <p class="text-muted">Prueba la conexión real con la API de Rainforest usando un producto de ejemplo</p>
                            <button class="btn btn-success" onclick="testConexion()">
                                <i class="bi bi-wifi"></i> Test de Conexión
                            </button>
                            <div id="result-conexion" class="mt-3"></div>
                        </div>
                    </div>

                    <!-- Test 3: Obtener Producto -->
                    <div class="card mb-4">
                        <div class="card-header bg-warning text-dark">
                            <h5><i class="bi bi-3-circle"></i> Obtener Datos de Producto</h5>
                        </div>
                        <div class="card-body">
                            <p class="text-muted">Ingresa una URL de Amazon para obtener información del producto</p>
                            <div class="input-group mb-3">
                                <input type="text" 
                                       class="form-control" 
                                       id="validate-url"
                                       placeholder="URL de Amazon a validar"
                                       value="https://www.amazon.com/dp/B08N5WRWNW">
                                <button class="btn btn-info" onclick="testValidacion()">
                                    <i class="bi bi-check-circle"></i> Validar
                                </button>
                            </div>
                            <div id="result-validacion" class="mt-3"></div>
                        </div>
                    </div>

                    <!-- Test 5: Buscar Productos -->
                    <div class="card mb-4">
                        <div class="card-header bg-danger text-white">
                            <h5><i class="bi bi-5-circle"></i> Buscar Productos</h5>
                        </div>
                        <div class="card-body">
                            <p class="text-muted">Busca productos por palabra clave en Amazon</p>
                            <div class="input-group mb-3">
                                <input type="text" 
                                       class="form-control" 
                                       id="search-keywords"
                                       placeholder="Ej: laptop gaming"
                                       value="iphone 15">
                                <button class="btn btn-danger" onclick="testBusqueda()">
                                    <i class="bi bi-search"></i> Buscar
                                </button>
                            </div>
                            <div id="result-busqueda" class="mt-3"></div>
                        </div>
                    </div>

                    <div class="text-center mt-4">
                        <a href="<?= base_url() ?>" class="btn btn-secondary btn-lg">
                            <i class="bi bi-arrow-left"></i> Volver al Inicio
                        </a>
                        <a href="<?= base_url('historial/crear') ?>" class="btn btn-primary btn-lg">
                            <i class="bi bi-calculator"></i> Ir a la Calculadora
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        const baseUrl = '<?= base_url() ?>';

        // Test 1: Verificar Configuración
        async function testConfiguracion() {
            const resultDiv = document.getElementById('result-config');
            resultDiv.innerHTML = '<div class="loading-box"><i class="bi bi-hourglass-split"></i> Verificando configuración...</div>';
            
            try {
                // Verificar que la API key esté configurada
                const response = await fetch(`${baseUrl}/amazon/verificarCuenta`);
                const data = await response.json();
                
                if (data.success) {
                    resultDiv.innerHTML = `
                        <div class="result-box">
                            <h6 class="text-success"><i class="bi bi-check-circle"></i> Configuración Correcta</h6>
                            <p class="mb-1"><strong>✅ API Key:</strong> Configurada correctamente</p>
                            <p class="mb-1"><strong>Plan:</strong> ${data.account_info?.plan || 'Free'}</p>
                            <p class="mb-0"><strong>Requests restantes:</strong> ${data.account_info?.credits_remaining || 'Ilimitado'}</p>
                        </div>
                    `;
                } else {
                    resultDiv.innerHTML = `
                        <div class="error-box">
                            <h6 class="text-danger"><i class="bi bi-x-circle"></i> Error de Configuración</h6>
                            <p class="mb-1">${data.message}</p>
                            <p class="mb-0"><small><strong>Solución:</strong> Verifica que RAINFOREST_API_KEY esté en tu archivo .env</small></p>
                        </div>
                    `;
                }
            } catch (error) {
                resultDiv.innerHTML = `
                    <div class="error-box">
                        <h6 class="text-danger"><i class="bi bi-x-circle"></i> Error de Conexión</h6>
                        <p class="mb-0">${error.message}</p>
                    </div>
                `;
            }
        }

        // Test 2: Test de Conexión
        async function testConexion() {
            const resultDiv = document.getElementById('result-conexion');
            resultDiv.innerHTML = '<div class="loading-box"><i class="bi bi-hourglass-split"></i> Probando conexión con Rainforest API...</div>';
            
            try {
                const response = await fetch(`${baseUrl}/amazon/testConexion`);
                const data = await response.json();
                
                if (data.success) {
                    resultDiv.innerHTML = `
                        <div class="result-box">
                            <h6 class="text-success"><i class="bi bi-check-circle"></i> Conexión Exitosa</h6>
                            <p class="mb-1"><strong>Estado:</strong> ${data.api_status}</p>
                            <p class="mb-1"><strong>Mensaje:</strong> ${data.message}</p>
                            <p class="mb-0"><strong>Producto de prueba:</strong> ${data.test_product}</p>
                        </div>
                    `;
                } else {
                    resultDiv.innerHTML = `
                        <div class="error-box">
                            <h6 class="text-danger"><i class="bi bi-x-circle"></i> Error de Conexión</h6>
                            <p class="mb-1">${data.message}</p>
                            <p class="mb-0"><small>${data.help}</small></p>
                        </div>
                    `;
                }
            } catch (error) {
                resultDiv.innerHTML = `
                    <div class="error-box">
                        <h6 class="text-danger"><i class="bi bi-x-circle"></i> Error</h6>
                        <p class="mb-0">${error.message}</p>
                    </div>
                `;
            }
        }

        // Test 3: Obtener Producto
        async function testProducto() {
            const url = document.getElementById('amazon-url').value;
            const resultDiv = document.getElementById('result-producto');
            
            if (!url) {
                resultDiv.innerHTML = `
                    <div class="error-box">
                        <h6 class="text-danger"><i class="bi bi-x-circle"></i> Error</h6>
                        <p class="mb-0">Por favor ingresa una URL</p>
                    </div>
                `;
                return;
            }
            
            resultDiv.innerHTML = '<div class="loading-box"><i class="bi bi-hourglass-split"></i> Obteniendo datos del producto...</div>';
            
            try {
                const response = await fetch(`${baseUrl}/amazon/obtener`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ url: url })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    const producto = data.data;
                    resultDiv.innerHTML = `
                        <div class="result-box">
                            <h6 class="text-success"><i class="bi bi-check-circle"></i> Producto Obtenido Exitosamente</h6>
                            <div class="row mt-3">
                                <div class="col-md-3 text-center">
                                    ${producto.imagen ? 
                                        `<img src="${producto.imagen}" class="img-fluid rounded shadow" alt="Producto" style="max-height: 200px;">` : 
                                        '<div class="bg-light p-4 rounded"><i class="bi bi-image text-muted" style="font-size: 3rem;"></i><br><small class="text-muted">Sin imagen</small></div>'
                                    }
                                </div>
                                <div class="col-md-9">
                                    <h5 class="text-primary">${producto.nombre}</h5>
                                    <hr>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <p class="mb-1"><strong><i class="bi bi-upc-scan"></i> ASIN:</strong> ${producto.asin}</p>
                                            <p class="mb-1"><strong><i class="bi bi-currency-dollar"></i> Precio:</strong> ${producto.moneda} ${producto.precio || 'No disponible'}</p>
                                            ${producto.precio_original ? `<p class="mb-1"><strong><i class="bi bi-tag"></i> Precio original:</strong> ${producto.moneda} ${producto.precio_original}</p>` : ''}
                                            <p class="mb-1"><strong><i class="bi bi-bookmark"></i> Marca:</strong> ${producto.marca || 'N/A'}</p>
                                        </div>
                                        <div class="col-md-6">
                                            <p class="mb-1"><strong><i class="bi bi-star-fill text-warning"></i> Rating:</strong> ${producto.rating ? producto.rating + '/5' : 'N/A'}</p>
                                            <p class="mb-1"><strong><i class="bi bi-chat-dots"></i> Reviews:</strong> ${producto.num_reviews || 'N/A'}</p>
                                            <p class="mb-1"><strong><i class="bi bi-box"></i> Disponibilidad:</strong> ${producto.disponibilidad}</p>
                                            ${producto.categoria ? `<p class="mb-1"><strong><i class="bi bi-folder"></i> Categoría:</strong> ${producto.categoria}</p>` : ''}
                                        </div>
                                    </div>
                                    ${producto.caracteristicas && producto.caracteristicas.length > 0 ? `
                                        <hr>
                                        <p class="mb-1"><strong><i class="bi bi-list-check"></i> Características:</strong></p>
                                        <ul class="small">
                                            ${producto.caracteristicas.slice(0, 3).map(f => `<li>${f}</li>`).join('')}
                                            ${producto.caracteristicas.length > 3 ? `<li><em>... y ${producto.caracteristicas.length - 3} más</em></li>` : ''}
                                        </ul>
                                    ` : ''}
                                </div>
                            </div>
                        </div>
                    `;
                } else {
                    resultDiv.innerHTML = `
                        <div class="error-box">
                            <h6 class="text-danger"><i class="bi bi-x-circle"></i> Error</h6>
                            <p class="mb-0">${data.message}</p>
                        </div>
                    `;
                }
            } catch (error) {
                resultDiv.innerHTML = `
                    <div class="error-box">
                        <h6 class="text-danger"><i class="bi bi-x-circle"></i> Error de Conexión</h6>
                        <p class="mb-0">${error.message}</p>
                    </div>
                `;
            }
        }

        // Test 4: Validar URL
        async function testValidacion() {
            const url = document.getElementById('validate-url').value;
            const resultDiv = document.getElementById('result-validacion');
            
            if (!url) {
                resultDiv.innerHTML = `
                    <div class="error-box">
                        <h6 class="text-danger"><i class="bi bi-x-circle"></i> Error</h6>
                        <p class="mb-0">Por favor ingresa una URL</p>
                    </div>
                `;
                return;
            }
            
            resultDiv.innerHTML = '<div class="loading-box"><i class="bi bi-hourglass-split"></i> Validando URL...</div>';
            
            try {
                const response = await fetch(`${baseUrl}/amazon/validar`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ url: url })
                });
                
                const data = await response.json();
                
                if (data.valid) {
                    resultDiv.innerHTML = `
                        <div class="result-box">
                            <h6 class="text-success"><i class="bi bi-check-circle"></i> URL Válida</h6>
                            <p class="mb-1"><strong><i class="bi bi-globe"></i> Dominio:</strong> ${data.domain}</p>
                            <p class="mb-1"><strong><i class="bi bi-upc-scan"></i> ASIN extraído:</strong> <code>${data.asin}</code></p>
                            <p class="mb-0"><strong><i class="bi bi-info-circle"></i> Mensaje:</strong> ${data.message}</p>
                        </div>
                    `;
                } else {
                    resultDiv.innerHTML = `
                        <div class="error-box">
                            <h6 class="text-danger"><i class="bi bi-x-circle"></i> URL No Válida</h6>
                            <p class="mb-0">${data.message}</p>
                        </div>
                    `;
                }
            } catch (error) {
                resultDiv.innerHTML = `
                    <div class="error-box">
                        <h6 class="text-danger"><i class="bi bi-x-circle"></i> Error</h6>
                        <p class="mb-0">${error.message}</p>
                    </div>
                `;
            }
        }

        // Test 5: Buscar Productos
        async function testBusqueda() {
            const keywords = document.getElementById('search-keywords').value;
            const resultDiv = document.getElementById('result-busqueda');
            
            if (!keywords) {
                resultDiv.innerHTML = `
                    <div class="error-box">
                        <h6 class="text-danger"><i class="bi bi-x-circle"></i> Error</h6>
                        <p class="mb-0">Por favor ingresa palabras clave</p>
                    </div>
                `;
                return;
            }
            
            resultDiv.innerHTML = '<div class="loading-box"><i class="bi bi-hourglass-split"></i> Buscando productos...</div>';
            
            try {
                const response = await fetch(`${baseUrl}/amazon/buscar`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ keywords: keywords, limite: 5 })
                });
                
                const data = await response.json();
                
                if (data.success && data.data.length > 0) {
                    let html = `
                        <div class="result-box">
                            <h6 class="text-success"><i class="bi bi-check-circle"></i> Encontrados ${data.count} productos</h6>
                            <small class="text-muted">Fuente: ${data.source}</small>
                            <div class="row mt-3">
                    `;
                    
                    data.data.forEach(prod => {
                        html += `
                            <div class="col-md-6 mb-3">
                                <div class="card h-100">
                                    <div class="card-body">
                                        <h6 class="card-title">${prod.title ? (prod.title.length > 60 ? prod.title.substring(0, 60) + '...' : prod.title) : 'Sin título'}</h6>
                                        <hr>
                                        <p class="mb-1 small"><strong>ASIN:</strong> <code>${prod.asin}</code></p>
                                        <p class="mb-1 small"><strong>Precio:</strong> ${prod.currency} ${prod.price || 'N/A'}</p>
                                        <p class="mb-0 small"><strong>Rating:</strong> ${prod.rating ? '⭐ ' + prod.rating + '/5' : 'N/A'}</p>
                                    </div>
                                </div>
                            </div>
                        `;
                    });
                    
                    html += '</div></div>';
                    resultDiv.innerHTML = html;
                } else {
                    resultDiv.innerHTML = `
                        <div class="error-box">
                            <h6 class="text-warning"><i class="bi bi-info-circle"></i> Sin Resultados</h6>
                            <p class="mb-0">${data.message || 'No se encontraron productos para esta búsqueda'}</p>
                        </div>
                    `;
                }
            } catch (error) {
                resultDiv.innerHTML = `
                    <div class="error-box">
                        <h6 class="text-danger"><i class="bi bi-x-circle"></i> Error</h6>
                        <p class="mb-0">${error.message}</p>
                    </div>
                `;
            }
        }
    </script>
</body>
</html><input type="text" 
                                       class="form-control" 
                                       id="amazon-url"
                                       placeholder="https://www.amazon.com/dp/B073JYC4XM"
                                       value="https://www.amazon.com/dp/B073JYC4XM">
                                <button class="btn btn-warning text-dark" onclick="testProducto()">
                                    <i class="bi bi-search"></i> Obtener Producto
                                </button>
                            </div>
                            <small class="text-muted">
                                <strong>ASINs de ejemplo para probar:</strong><br>
                                • B073JYC4XM (Fire TV Stick)<br>
                                • B08N5WRWNW (Echo Dot)<br>
                                • B09SWW583J (Kindle)<br>
                                • B08KTZ8249 (Kindle Paperwhite)
                            </small>
                            <div id="result-producto" class="mt-3"></div>
                        </div>
                    </div>

                    <!-- Test 4: Validar URL -->
                    <div class="card mb-4">
                        <div class="card-header bg-info text-white">
                            <h5><i class="bi bi-4-circle"></i> Validar URL de Amazon</h5>
                        </div>
                        <div class="card-body">
                            <p class="text-muted">Valida si una URL de Amazon es correcta y extrae el ASIN</p>
                            <div class="input-group mb-3">