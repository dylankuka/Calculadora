<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Registro - TaxImporter</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/Calculadora/Calculadora-1/Calculadora/public/css/ind.css">
</head>
<body class="bg-dark">
    
    <div class="position-absolute" style="top: 5px; left: 3px; z-index: 1000;">
   <a href="<?= base_url('/') ?>">
       <img src="<?= base_url('img/taximporterlogo.png') ?>" 
            alt="Logo TaxImporter" 
            style="max-width: 95px; height: auto; 
                   filter: drop-shadow(2px 2px 4px rgba(0,0,0,0.2)); 
                   cursor: pointer;">
   </a>
</div>

<div class="container mt-5" style="max-width: 500px;">
    <div class="text-center mb-4">
        <h2>Crear Cuenta - TaxImporter</h2>
        <p class="text-muted">Registrate para guardar tu historial de cálculos</p>
    </div>

    <!-- ✅ MENSAJES DE ERROR Y ÉXITO -->
    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger">
            <i class="bi bi-exclamation-triangle"></i>
            <?= session()->getFlashdata('error') ?>
        </div>
    <?php endif; ?>

    <?php if (isset($validation)): ?>
        <div class="alert alert-danger">
            <h6>❌ Por favor corrige los siguientes errores:</h6>
            <ul class="mb-0">
                <?php foreach ($validation->getErrors() as $error): ?>
                    <li><?= esc($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <div class="card shadow card-custom2">
        <div class="card-body">
            <form action="<?= base_url('usuario/registrar') ?>" method="post" novalidate>
                <?= csrf_field() ?>

                <div class="mb-3">
                    <label for="nombredeusuario" class="form-label">Nombre de Usuario</label>
                    <input type="text" 
                           class="form-control <?= isset($validation) && $validation->hasError('nombredeusuario') ? 'is-invalid' : '' ?>" 
                           id="nombredeusuario" 
                           name="nombredeusuario" 
                           value="<?= set_value('nombredeusuario', $old_input['nombredeusuario'] ?? '') ?>" 
                           required>
                    <?php if (isset($validation) && $validation->hasError('nombredeusuario')): ?>
                        <div class="invalid-feedback">
                            <?= $validation->getError('nombredeusuario') ?>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label">Correo Electrónico</label>
                    <input type="email" 
                           class="form-control <?= isset($validation) && $validation->hasError('email') ? 'is-invalid' : '' ?>" 
                           id="email" 
                           name="email" 
                           value="<?= set_value('email', $old_input['email'] ?? '') ?>" 
                           required>
                    <?php if (isset($validation) && $validation->hasError('email')): ?>
                        <div class="invalid-feedback">
                            <?= $validation->getError('email') ?>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label">Contraseña</label>
                    <input type="password" 
                           class="form-control <?= isset($validation) && $validation->hasError('password') ? 'is-invalid' : '' ?>" 
                           id="password" 
                           name="password" 
                           required>
                    <div class="form-custom form-text">Mínimo 6 caracteres, debe incluir mayúscula, minúscula y número</div>
                    <?php if (isset($validation) && $validation->hasError('password')): ?>
                        <div class="invalid-feedback">
                            <?= $validation->getError('password') ?>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="mb-3">
                    <label for="pass_confirm" class="form-label">Confirmar Contraseña</label>
                    <input type="password" 
                           class="form-control <?= isset($validation) && $validation->hasError('pass_confirm') ? 'is-invalid' : '' ?>" 
                           id="pass_confirm" 
                           name="pass_confirm" 
                           required>
                    <?php if (isset($validation) && $validation->hasError('pass_confirm')): ?>
                        <div class="invalid-feedback">
                            <?= $validation->getError('pass_confirm') ?>
                        </div>
                    <?php endif; ?>
                </div>

                <button type="submit" class="card-custom textcolor btn w-100">Crear Cuenta</button>
            </form>
        </div>
    </div>

    <p class="mt-3 text-center">
        ¿Ya tienes cuenta? <a href="<?= base_url('usuario/login') ?>">Iniciar sesión</a>
    </p>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
