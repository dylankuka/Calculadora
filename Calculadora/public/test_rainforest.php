<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Rainforest API - TaxImporter</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; padding: 40px 0; }
        .test-card { background: white; border-radius: 15px; box-shadow: 0 10px 40px rgba(0,0,0,0.2); }
        .result-box { background: #f8f9fa; border-left: 4px solid #28a745; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .error-box { background: #fff3cd; border-left: 4px solid #dc3545; padding: 15px; border-radius: 5px; margin: 10px 0; }
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

                    <!-- Test 1: Verificar API Key -->
                    <div class="card mb-4">
                        <div class="card-header bg-primary text-white">
                            <h5><i class="bi bi-1-circle"></i> Verificar API Key</h5>
                        </div>
                        <div class="card-body">
                            <button class="btn btn-primary" onclick="testApiKey()">
                                <i class="bi bi-key"></i> Verificar Configuración
                            </button>
                            <div id="result-apikey" class="mt-3"></div>
                        </div>
                    </div>

                    <!-- Test 2: Obtener Producto -->
                    <div class="card mb-4">
                        <div class="card-header bg-success text-white">
                            <h5><i class="bi bi-2-circle"></i> Obtener Datos de Producto</h5>
                        </div>
                        <div class="card-body">
                            <div class="input-group mb-3">
                                <input type="text" 
                                       class="form-control" 
                                       id="amazon-url"
                                       placeholder="https://www.amazon.com/dp/B073JYC4XM"
                                       value="https://www.amazon.com/dp/B073JYC4XM">
                                <button class="btn btn-success" onclick="testProducto()">
                                    <i class="bi bi-search"></i> Obtener Producto
                                </button>
                            </div>
                            <small class="text-muted">
                                Ejemplos de ASINs para probar: B073JYC4XM, B08N5WRWNW, B09SWW583J
                            </small>
                            <div id="result-producto" class="mt-3"></div>
                        </div>
                    </div>

                    <!-- Test 3: Buscar Productos -->
                    <div class="card mb-4">
                        <div class="card-header bg-warning text-dark">
                            <h5><i class="bi bi-3-circle"></i> Buscar Productos</h5>
                        </div>
                        <div class="card-body">
                            <div class="input-group mb-3">
                                <input type="text" 
                                       class="form-control" 
                                       id="search-keywords"
                                       placeholder="Ej: laptop gaming"
                                       value="iphone 15">
                                <button class="btn btn-warning text-dark" onclick="testBusqueda()">
                                    <i class="bi bi-search"></i> Buscar
                                </button>
                            </div>
                            <div id="result-busqueda" class="mt-3"></div>
                        </div>
                    </div>

                    <!-- Test 4: Validar URL -->
                    <div class="card mb-4">
                        <div class="card-header bg-info text-white">
                            <h5><i class="bi bi-4-circle"></i> Validar URL de Amazon</h5>
                        </div>
                        <div class="card-body">
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

                    <div class="text-center mt-4">
                        <a href="<?= base_url() ?>" class="btn btn-secondary">
                            <i class="bi bi-arrow-left"></i> Volver al Inicio
                        </a>
                        <a href="<?= base_url('historial/crear') ?>" class="btn btn-primary">
                            <i class="bi bi-calculator"></i> Ir a la Calculadora
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        const baseUrl = '<?= base_url() ?>';

        // Test 1: Verificar API Key
        async function testApiKey() {
            const resultDiv = document.getElementById('result-apikey');
            resultDiv.innerHTML = '<div class="spinner-border text-primary" role="status"></div> Verificando...';
            
            try {
                const response = await fetch(`${baseUrl}/amazon/verificarCuenta`);
                const data = await response.json();
                
                if (data.success) {
                    resultDiv.innerHTML = `
                        <div class="result-box">
                            <h6 class="text-success"><i class="bi bi-check-circle"></i> API Key Válida</h6>
                            <p class="mb-0"><strong>Plan:</strong> ${data.account_info?.plan || 'Free'}</p>
                            <p class="mb-0"><strong>Requests disponibles:</strong> ${data.account_info?.credits_remaining || 'N/A'}</p>
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

        // Test 2: Obtener Producto
        async function testProducto() {
            const url = document.getElementById('amazon-url').value;
            const resultDiv = document.getElementById('result-producto');
            resultDiv.innerHTML = '<div class="spinner-border text-success" role="status"></div> Obteniendo datos...';
            
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
                            <h6 class="text-success"><i class="bi bi-check-circle"></i> Producto Obtenido</h6>
                            <div class="row">
                                <div class="col-md-3">
                                    ${producto.imagen ? `<img src="${producto.imagen}" class="img-fluid rounded" alt="Producto">` : '<div class="text-muted">Sin imagen</div>'}
                                </div>
                                <div class="col-md-9">
                                    <p><strong>Nombre:</strong> ${producto.nombre}</p>
                                    <p><strong>ASIN:</strong> ${producto.asin}</p>
                                    <p><strong>Precio:</strong> ${producto.moneda} ${producto.precio}</p>
                                    <p><strong>Marca:</strong> ${producto.marca || 'N/A'}</p>
                                    <p><strong>Rating:</strong> ${producto.rating ? producto.rating + '/5' : 'N/A'}</p>
                                    <p><strong>Reviews:</strong> ${producto.num_reviews || 'N/A'}</p>
                                    <p><strong>Disponibilidad:</strong> ${producto.disponibilidad}</p>
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
                        <h6 class="text-danger"><i class="bi bi-x-circle"></i> Error</h6>
                        <p class="mb-0">${error.message}</p>
                    </div>
                `;
            }
        }

        // Test 3: Buscar Productos
        async function testBusqueda() {
            const keywords = document.getElementById('search-keywords').value;
            const resultDiv = document.getElementById('result-busqueda');
            resultDiv.innerHTML = '<div class="spinner-border text-warning" role="status"></div> Buscando...';
            
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
                    let html = `<div class="result-box"><h6 class="text-success"><i class="bi bi-check-circle"></i> Encontrados ${data.count} productos</h6><div class="row">`;
                    
                    data.data.forEach(prod => {
                        html += `
                            <div class="col-md-6 mb-3">
                                <div class="card">
                                    <div class="card-body">
                                        <h6 class="card-title">${prod.title ? prod.title.substring(0, 60) + '...' : 'Sin título'}</h6>
                                        <p class="mb-1"><strong>ASIN:</strong> ${prod.asin}</p>
                                        <p class="mb-1"><strong>Precio:</strong> ${prod.currency} ${prod.price || 'N/A'}</p>
                                        <p class="mb-0"><strong>Rating:</strong> ${prod.rating || 'N/A'}/5</p>
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
                            <h6 class="text-warning"><i class="bi bi-info-circle"></i> Sin resultados</h6>
                            <p class="mb-0">${data.message || 'No se encontraron productos'}</p>
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

        // Test 4: Validar URL
        async function testValidacion() {
            const url = document.getElementById('validate-url').value;
            const resultDiv = document.getElementById('result-validacion');
            resultDiv.innerHTML = '<div class="spinner-border text-info" role="status"></div> Validando...';
            
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
                            <p class="mb-0"><strong>Dominio:</strong> ${data.domain}</p>
                            <p class="mb-0"><strong>ASIN:</strong> ${data.asin}</p>
                            <p class="mb-0">${data.message}</p>
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
    </script>
</body>
</html>