<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $usuario_logueado ? 'Mi Historial' : 'TaxImporter' ?> - Calculadora de Impuestos Amazon</title>
    <link rel="stylesheet" href="<?= base_url('css/ind.css') ?>">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .calculo-card {
            transition: all 0.3s ease;
            border-left: 4px solid #28a745;
        }
        .calculo-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }
        .franquicia-badge {
            background: linear-gradient(45deg, #28a745 0%, #20c997 100%);
            color: white;
        }
        .sobre-franquicia-badge {
            background: linear-gradient(45deg, #ffc107 0%, #fd7e14 100%);
            color: white;
        }
        .resumen-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        .stats-card {
            border-radius: 15px;
            overflow: hidden;
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
<nav class="navbar navbar-expand-lg navbar-dark  card-custom5">
    <div class="container">
        <a class="navbar-brand textcolor" href="<?= base_url('/historial') ?>">
            <i class="bi bi-calculator"></i> TaxImporter
        </a>
        
        <div class="navbar-nav ms-auto">
            <?php if ($usuario_logueado): ?>
                <span class="navbar-text me-3 textcolor">
                    👤 <strong><?= esc(session()->get('usuario_nombre')) ?></strong>
                </span>
                <a class="btn btn-outline-dark btn-sm me-2" href="<?= base_url('historial/crear') ?>">
                    <i class="bi bi-plus-circle textcolor"></i> Nueva Calculadora
                </a>
 <a class="btn btn-outline-dark btn-sm" href="<?= base_url('donacion') ?>">
                    <i class="textcolor2"></i> 🧡donar
                </a>
                <a class="btn btn-outline-dark btn-sm" href="<?= base_url('usuario/logout') ?>">
                    <i class="bi bi-box-arrow-right textcolor"></i> Salir
                </a>
            <?php else: ?>
                <a class="btn btn-outline-dark btn-sm me-2" href="<?= base_url('usuario/login') ?>">
                    <i class="bi bi-box-arrow-in-right textcolor"></i> Iniciar Sesión
                </a>
                <a class="btn btn-warning btn-sm" href="<?= base_url('usuario/registro') ?>">
                    <i class="bi bi-person-plus"></i> Registrarse
                </a>
            <?php endif; ?>
        </div>
    </div>
</nav>

<div class="container mt-4">
    
    <!-- Mensajes -->
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

    <?php if (!$usuario_logueado): ?>
        <!-- Página de bienvenida para usuarios no logueados -->
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="text-center mb-5">
                    <h1 class="display-4 text-white mb-3">
                        <i class="bi bi-calculator text-warning"></i> TaxImporter
                    </h1>
                    <p class="lead text-light">
                        Calculadora inteligente de impuestos para compras en Amazon
                    </p>
                </div>
                
                <div class="card shadow card-custom2 mb-4">
                    <div class="card-body text-center p-5">
                        <h3 class="card-title mb-4">🎯 Calcula todos tus impuestos de importación</h3>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <i class="bi bi-shield-check text-success" style="font-size: 2rem;"></i>
                                <h5 class="mt-2">Cálculos Precisos</h5>
                                <p>Aplicamos la regla de franquicia de $400 USD y todos los aranceles por categoría</p>
                            </div>
                            <div class="col-md-4 mb-3">
                                <i class="bi bi-currency-exchange text-warning" style="font-size: 2rem;"></i>
                                <h5 class="mt-2">Cotizaciones en Tiempo Real</h5>
                                <p>Usamos las cotizaciones oficiales de DolarAPI actualizadas automáticamente</p>
                            </div>
                            <div class="col-md-4 mb-3">
                                <i class="bi bi-graph-up text-info" style="font-size: 2rem;"></i>
                                <h5 class="mt-2">Historial Completo</h5>
                                <p>Guarda y consulta todos tus cálculos con desglose detallado</p>
                            </div>
                        </div>
                        
                        <hr class="my-4">
                        
                        <h4 class="mb-3">¿Cómo funciona?</h4>
                        <div class="row text-start">
                            <div class="col-md-6">
                                <h6><i class="bi bi-1-circle-fill text-primary"></i> Pega la URL de Amazon</h6>
                                <p class="small">Obtenemos automáticamente el precio y datos del producto</p>
                                
                                <h6><i class="bi bi-2-circle-fill text-primary"></i> Selecciona la categoría</h6>
                                <p class="small">Aplicamos el arancel correcto según el tipo de producto</p>
                            </div>
                            <div class="col-md-6">
                                <h6><i class="bi bi-3-circle-fill text-primary"></i> Elige método de pago</h6>
                                <p class="small">Calculamos percepciones AFIP si pagas con tarjeta argentina</p>
                                
                                <h6><i class="bi bi-4-circle-fill text-primary"></i> Obtén el resultado</h6>
                                <p class="small">Total final con desglose completo de todos los impuestos</p>
                            </div>
                        </div>
                        
                        <div class="mt-4">
                            <a href="<?= base_url('usuario/registro') ?>" class="btn btn-warning btn-lg me-2">
                                <i class="bi bi-person-plus"></i> Crear Cuenta Gratis
                            </a>
                            <a href="<?= base_url('usuario/login') ?>" class="btn btn-outline-light btn-lg">
                                <i class="bi bi-box-arrow-in-right"></i> Ya tengo cuenta
                            </a>
                        </div>
                    </div>
                </div>
                
                <!-- Información adicional -->
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <div class="card bg-success text-white h-100">
                            <div class="card-body text-center">
                                <i class="bi bi-check-circle" style="font-size: 2rem;"></i>
                                <h6 class="mt-2">Franquicia $400 USD</h6>
                                <p class="small">Productos ≤ $400 solo pagan IVA</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="card bg-warning text-dark h-100">
                            <div class="card-body text-center">
                                <i class="bi bi-percent" style="font-size: 2rem;"></i>
                                <h6 class="mt-2">Aranceles Variables</h6>
                                <p class="small">Según categoría: 0% a 20%</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="card bg-info text-white h-100">
                            <div class="card-body text-center">
                                <i class="bi bi-credit-card" style="font-size: 2rem;"></i>
                                <h6 class="mt-2">Percepciones AFIP</h6>
                                <p class="small">30% adicional con tarjeta argentina</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php else: ?>
        <!-- Dashboard para usuarios logueados -->
        <div class="row">
            <!-- Resumen de estadísticas -->
            <div class="col-md-4 mb-4">
                <div class="card stats-card resumen-card">
                    <div class="card-body text-center">
                        <h3 class="mb-0">$<?= number_format($resumen['total_calculado'] ?? 0, 2) ?></h3>
                        <p class="mb-1">ARS calculados en total</p>
                        <hr class="border-light">
                        <div class="row">
                            <div class="col-6">
                                <h4><?= $resumen['total_consultas'] ?? 0 ?></h4>
                                <small>Cálculos realizados</small>
                            </div>
                            <div class="col-6">
                                <h4>$<?= number_format($resumen['promedio_cif_usd'] ?? 0, 0) ?></h4>
                                <small>Promedio USD</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Acciones rápidas -->
            <div class="col-md-8 mb-4">
                <div class="card">
                    <div class="card-body card-custom2">
                        <h5 class="card-title">
                            <i class="bi bi-lightning-charge text-warning"></i> Acciones Rápidas
                        </h5>
                        <div class="row">
                            <div class="col-md-6 mb-2">
                                <a href="<?= base_url('historial/crear') ?>" class="btn btn-success w-100">
                                    <i class="bi bi-plus-circle"></i> Nueva Calculadora
                                </a>
                            </div>
                            <div class="col-md-6 mb-2 " >
                                <button class="btn btn-amazon-link w-100 card-custom5" onclick="actualizarCotizaciones()">
                                    <i class="bi bi-arrow-clockwise"></i> Actualizar Cotizaciones
                                </button>
                            </div>
                        </div>
                        
                        <!-- Búsqueda -->
                        <form method="GET" class="mt-3">
                            <div class="input-group">
                                <input type="text" 
                                       class="form-control" 
                                       name="buscar" 
                                       placeholder="Buscar por producto o categoría..."
                                       value="<?= esc($busqueda ?? '') ?>">
                                <button class="btn btn-outline-secondary" type="submit">
                                    <i class="bi bi-search text-light"></i>
                                </button>
                                <?php if ($busqueda): ?>
                                    <a href="<?= base_url('historial') ?>" class="btn btn-outline-danger">
                                        <i class="bi bi-x"></i>
                                    </a>
                                <?php endif; ?>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Lista de cálculos -->
        <div class="row">
            <div class="col-12">
                <div class="card card-custom5">
                    <div class="card-header card-custom textcolor d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="bi bi-clock-history"></i> 
                            <?php if ($mensaje): ?>
                                <?= esc($mensaje) ?>
                            <?php else: ?>
                                Mis Cálculos de Impuestos
                            <?php endif; ?>
                        </h5>
                        <small>Total: <?= count($historial) ?> cálculos</small>
                    </div>
                    <div class="card-body card-custom2">
                        <?php if (empty($historial)): ?>
                            <div class="text-center py-5">
                                <i class="bi bi-inbox text-light" style="font-size: 4rem;"></i>
                                <h4 class="text-light mt-3">No hay cálculos aún</h4>
                                <p class="text-light">¡Crea tu primera calculadora de impuestos!</p>
                                <a href="<?= base_url('historial/crear') ?>" class="btn btn-success btn-lg">
                                    <i class="bi bi-plus-circle"></i> Crear Primera Calculadora
                                </a>
                            </div>
                        <?php else: ?>
                            <div class="row">
                                <?php foreach ($historial as $calculo): ?>
                                    <?php 
                                        $desglose = json_decode($calculo['desglose_json'], true);
                                        $bajoFranquicia = ($calculo['valor_cif_usd'] ?? 0) <= 400;
                                    ?>
                                    <div class="col-md-6 col-lg-4 mb-4">
                                        <div class="card calculo-card h-100">
                                            <div class="card-body card-custom6">
                                                <!-- Header con fecha y estado -->
                                                <div class="d-flex justify-content-between align-items-start mb-2">
                                                    <small class="text-light">
                                                        <i class="bi bi-calendar3"></i> 
                                                        <?= date('d/m/Y H:i', strtotime($calculo['fecha_calculo'])) ?>
                                                    </small>
                                                    <span class="badge <?= $bajoFranquicia ? 'franquicia-badge' : 'sobre-franquicia-badge' ?>">
                                                        <?= $bajoFranquicia ? '≤$400' : '>$400' ?>
                                                    </span>
                                                </div>
                                                
                                                <!-- Nombre del producto -->
                                                <h6 class="card-title text-light" title="<?= esc($calculo['nombre_producto']) ?>">
                                                    <?= esc(substr($calculo['nombre_producto'], 0, 50)) ?><?= strlen($calculo['nombre_producto']) > 50 ? '...' : '' ?>
                                                </h6>
                                                
                                                <!-- Categoría -->
                                                <?php if (isset($calculo['categoria_nombre'])): ?>
                                                    <p class="mb-2">
                                                        <i class="bi bi-tag text-primary"></i> 
                                                        <small><?= esc($calculo['categoria_nombre']) ?></small>
                                                    </p>
                                                <?php endif; ?>
                                                
                                                <!-- Detalles del cálculo -->
                                                <div class="row text-center mb-3">
                                                    <div class="col-6">
                                                        <strong class="text-success">$<?= number_format($calculo['precio_usd'], 2) ?></strong>
                                                        <br><small class="text-light">USD</small>
                                                    </div>
                                                    <div class="col-6">
                                                        <strong class="text-warning">$<?= number_format($calculo['total_ars'], 0) ?></strong>
                                                        <br><small class="text-light">ARS</small>
                                                    </div>
                                                </div>
                                                
                                                <!-- Método de pago y cotización -->
                                                <?php if (isset($calculo['metodo_pago'])): ?>
                                                    <p class="mb-2">
                                                        <i class="bi bi-credit-card text-info"></i> 
                                                        <small>
                                                            <?= strtoupper($calculo['metodo_pago']) ?>
                                                            <?php if (isset($desglose['datos_base']['cotizacion'])): ?>
                                                                - $<?= number_format($desglose['datos_base']['cotizacion'], 2) ?>
                                                            <?php endif; ?>
                                                        </small>
                                                    </p>
                                                <?php endif; ?>
                                                
                                                <!-- Acciones -->
                                                <div class="d-grid gap-2 d-md-flex justify-content-md-between">
                                                    <a href="<?= base_url('historial/ver/' . $calculo['id']) ?>" 
                                                       class="btn btn-outline-primary btn-sm flex-fill">
                                                        <i class="bi bi-eye"></i> Ver
                                                    </a>
                                                    <a href="<?= base_url('historial/editar/' . $calculo['id']) ?>" 
                                                       class="btn btn-outline-primary btn-sm flex-fill">
                                                        <i class="bi bi-pencil"></i> Editar
                                                    </a>
                                                    <button onclick="confirmarEliminar(<?= $calculo['id'] ?>, '<?= esc(addslashes($calculo['nombre_producto'])) ?>')" 
                                                            class="btn btn-outline-danger btn-sm flex-fill">
                                                        <i class="bi bi-trash"></i> Eliminar
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            
                            <!-- Paginación futura -->
                            <?php if (count($historial) >= 20): ?>
                                <div class="text-center mt-3">
                                    <small class="text-muted">
                                        Mostrando los últimos 20 cálculos. 
                                        <a href="#" class="text-decoration-none">Ver más</a>
                                    </small>
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Modal de confirmación para eliminar -->
<div class="modal fade" id="modalEliminar" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content bg-dark text-light">
            <div class="modal-header border-secondary">
                <h5 class="modal-title">
                    <i class="bi bi-exclamation-triangle text-warning"></i> Confirmar Eliminación
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>¿Estás seguro de que deseas eliminar este cálculo?</p>
                <div class="alert alert-warning">
                    <strong id="producto-eliminar">Producto</strong>
                </div>
                <p class="text-muted small">Esta acción no se puede deshacer.</p>
            </div>
            <div class="modal-footer border-secondary">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <a href="#" id="btn-confirmar-eliminar" class="btn btn-danger">
                    <i class="bi bi-trash"></i> Eliminar
                </a>
            </div>
        </div>
    </div>
</div>

<script>
// ✅ FUNCIÓN PARA CONFIRMAR ELIMINACIÓN
function confirmarEliminar(id, nombreProducto) {
    document.getElementById('producto-eliminar').textContent = nombreProducto;
    document.getElementById('btn-confirmar-eliminar').href = '<?= base_url('historial/eliminar/') ?>' + id;
    
    const modal = new bootstrap.Modal(document.getElementById('modalEliminar'));
    modal.show();
}

// ✅ ACTUALIZAR COTIZACIONES
async function actualizarCotizaciones() {
    const btn = event.target;
    const originalHTML = btn.innerHTML;
    
    btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status"></span> Actualizando...';
    btn.disabled = true;
    
    try {
        const response = await fetch('<?= base_url('dolar/actualizar') ?>');
        const resultado = await response.json();
        
        if (resultado.success) {
            // Mostrar mensaje de éxito
            const alertDiv = document.createElement('div');
            alertDiv.className = 'alert alert-success alert-dismissible fade show';
            alertDiv.innerHTML = `
                <i class="bi bi-check-circle"></i> Cotizaciones actualizadas exitosamente
                <br><small>Dólar Tarjeta: ${resultado.data.tarjeta} - MEP: ${resultado.data.MEP}</small>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            
            document.querySelector('.container').insertBefore(alertDiv, document.querySelector('.container').firstChild);
            
            // Auto-dismiss después de 5 segundos
            setTimeout(() => {
                alertDiv.remove();
            }, 5000);
        } else {
            throw new Error(resultado.message || 'Error desconocido');
        }
    } catch (error) {
        console.error('Error:', error);
        alert('❌ Error actualizando cotizaciones: ' + error.message);
    } finally {
        btn.innerHTML = originalHTML;
        btn.disabled = false;
    }
}

// ✅ AUTO-REFRESH PARA MENSAJES
document.addEventListener('DOMContentLoaded', function() {
    // Auto-dismiss alerts después de 10 segundos
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        if (!alert.querySelector('.btn-close')) return;
        
        setTimeout(() => {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        }, 10000);
    });
});
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>