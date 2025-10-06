<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Gestión de Usuarios - Admin TaxImporter</title>
    <link rel="stylesheet" href="<?= base_url('css/ind.css') ?>">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="bg-dark">

<!-- Logo -->
<div class="position-absolute" style="top: 5px; left: 22px; z-index: 1000;">
    <a href="<?= base_url() ?>">
        <img src="<?= base_url('img/taximporterlogo.png') ?>" alt="Logo" style="max-width: 70px; height: auto; filter: drop-shadow(2px 2px 6px rgba(0,0,0,1.9));">
    </a>
</div>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark card-custom">
    <div class="container">
        <a class="navbar-brand textcolor" href="<?= base_url('admin') ?>">
            <i class="bi bi-speedometer2"></i> Panel Admin
        </a>
        <div class="navbar-nav ms-auto">
            <a class="btn btn-outline-dark btn-sm me-2" href="<?= base_url('admin') ?>">
                <i class="bi bi-arrow-left textcolor"></i> Dashboard
            </a>
            <a class="btn btn-outline-dark btn-sm" href="<?= base_url('historial') ?>">
                <i class="bi bi-house textcolor"></i> Sitio
            </a>
        </div>
    </div>
</nav>

<div class="container mt-4">
    
    <!-- Mensajes -->
    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <?= session()->getFlashdata('success') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <?= session()->getFlashdata('error') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card card-custom2">
                <div class="card-body">
                    <h2 class="text-white">
                        <i class="bi bi-people-fill text-primary"></i> Gestión de Usuarios
                    </h2>
                    
                    <!-- Filtros y búsqueda -->
                    <form method="GET" class="row g-3 mt-3">
                        <div class="col-md-6">
                            <input type="text" class="form-control" name="buscar" 
                                   placeholder="Buscar por nombre o email..." 
                                   value="<?= esc($busqueda ?? '') ?>">
                        </div>
                        <div class="col-md-3">
                            <select class="form-select" name="rol">
                                <option value="">Todos los roles</option>
                                <option value="admin" <?= ($rol_filtro ?? '') === 'admin' ? 'selected' : '' ?>>Administradores</option>
                                <option value="usuario" <?= ($rol_filtro ?? '') === 'usuario' ? 'selected' : '' ?>>Usuarios</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="bi bi-search"></i> Buscar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla de usuarios -->
    <div class="row">
        <div class="col-12">
            <div class="card card-custom2">
                <div class="card-header card-custom">
                    <h5 class="mb-0 textcolor">
                        <i class="bi bi-list"></i> Lista de Usuarios (<?= count($usuarios) ?>)
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-dark table-hover mb-0">
                            <thead class="table-secondary">
                                <tr>
                                    <th>ID</th>
                                    <th>Usuario</th>
                                    <th>Email</th>
                                    <th>Rol</th>
                                    <th>Cálculos</th>
                                    <th>Donaciones</th>
                                    <th>Registro</th>
                                    <th>Estado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($usuarios)): ?>
                                    <tr>
                                        <td colspan="9" class="text-center text-muted py-4">
                                            No se encontraron usuarios
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($usuarios as $usuario): ?>
                                        <tr>
                                            <td><?= $usuario['id'] ?></td>
                                            <td>
                                                <strong><?= esc($usuario['nombredeusuario']) ?></strong>
                                                <?php if ($usuario['id'] == session()->get('usuario_id')): ?>
                                                    <span class="badge bg-info">Tú</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?= esc($usuario['email']) ?></td>
                                            <td>
                                                <?php if ($usuario['rol'] === 'admin'): ?>
                                                    <span class="badge bg-danger">ADMIN</span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary">Usuario</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge bg-primary"><?= $usuario['total_calculos'] ?></span>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge bg-warning"><?= $usuario['total_donaciones'] ?></span>
                                            </td>
                                            <td>
                                                <small><?= date('d/m/Y', strtotime($usuario['fecha_registro'])) ?></small>
                                            </td>
                                            <td>
                                                <?php if ($usuario['activo']): ?>
                                                    <span class="badge bg-success">Activo</span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary">Inactivo</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <!-- Cambiar rol -->
                                                    <button class="btn btn-outline-warning" 
                                                            onclick="cambiarRol(<?= $usuario['id'] ?>, '<?= esc($usuario['nombredeusuario']) ?>', '<?= $usuario['rol'] ?>')"
                                                            <?= $usuario['id'] == session()->get('usuario_id') ? 'disabled' : '' ?>>
                                                        <i class="bi bi-shield"></i>
                                                    </button>
                                                    
                                                    <!-- Activar/Desactivar -->
                                                    <a href="<?= base_url('admin/usuarios/toggle/' . $usuario['id']) ?>" 
                                                       class="btn btn-outline-<?= $usuario['activo'] ? 'danger' : 'success' ?>"
                                                       onclick="return confirm('¿Estás seguro?')"
                                                       <?= $usuario['id'] == session()->get('usuario_id') ? 'disabled' : '' ?>>
                                                        <i class="bi bi-power"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal cambiar rol -->
<div class="modal fade" id="modalCambiarRol" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content bg-dark text-light">
            <form method="POST" id="formCambiarRol">
                <div class="modal-header">
                    <h5 class="modal-title">Cambiar Rol de Usuario</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Cambiar rol de: <strong id="nombreUsuario"></strong></p>
                    <select class="form-select" name="rol" id="nuevoRol">
                        <option value="usuario">Usuario</option>
                        <option value="admin">Administrador</option>
                    </select>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function cambiarRol(userId, nombre, rolActual) {
    document.getElementById('nombreUsuario').textContent = nombre;
    document.getElementById('nuevoRol').value = rolActual === 'admin' ? 'usuario' : 'admin';
    document.getElementById('formCambiarRol').action = '<?= base_url('admin/usuarios/cambiar-rol/') ?>' + userId;
    
    new bootstrap.Modal(document.getElementById('modalCambiarRol')).show();
}
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>