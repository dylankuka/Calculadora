<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Inicio de Sesión - TaxImporter</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>
<body class="bg-light">

<div class="container mt-5" style="max-width: 400px;">
    <h2 class="mb-4 text-center">Iniciar Sesión</h2>
<?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-danger"><?= session()->getFlashdata('error') ?></div>
<?php endif; ?>
    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success"><?= session()->getFlashdata('success') ?></div>
    <?php endif; ?>

    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?= esc($error) ?></div>
    <?php endif; ?>

    <form action="<?= base_url('usuario/iniciarSesion') ?>" method="post" novalidate>
        <?= csrf_field() ?>

        <div class="mb-3">
            <label for="email" class="form-label">Correo Electrónico</label>
            <input type="email" class="form-control" id="email" name="email" required value="<?= set_value('email') ?>" />
        </div>

        <div class="mb-3">
            <label for="password" class="form-label">Contraseña</label>
            <input type="password" class="form-control" id="password" name="password" required />
        </div>

        <button type="submit" class="btn btn-primary w-100">Entrar</button>
    </form>

    <p class="mt-3 text-center">
        ¿No tienes cuenta? <a href="<?= base_url('usuario/registro') ?>">Regístrate aquí</a>
    </p>
</div>

</body>
</html>
