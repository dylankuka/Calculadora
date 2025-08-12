<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Registro - TaxImporter</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>
<body class="bg-light">

<div class="container mt-5" style="max-width: 480px;">
    <h2 class="mb-4 text-center">Crear Cuenta</h2>

    <?php if (isset($validation)): ?>
    <div class="alert alert-danger">
        <?= $validation->listErrors() ?>
    </div>
    <?php endif; ?>

    <form action="<?= base_url('usuario/registrar') ?>" method="post" novalidate>
        <?= csrf_field() ?>

        <div class="mb-3">
            <label for="nombredeusuario" class="form-label">Nombre de Usuario</label>
            <input type="text" class="form-control" id="nombredeusuario" name="nombredeusuario" value="<?= set_value('nombredeusuario') ?>" required />
        </div>

        <div class="mb-3">
            <label for="email" class="form-label">Correo Electrónico</label>
            <input type="email" class="form-control" id="email" name="email" value="<?= set_value('email') ?>" required />
        </div>

        <div class="mb-3">
            <label for="password" class="form-label">Contraseña</label>
            <input type="password" class="form-control" id="password" name="password" required />
        </div>

        <div class="mb-3">
            <label for="pass_confirm" class="form-label">Confirmar Contraseña</label>
            <input type="password" class="form-control" id="pass_confirm" name="pass_confirm" required />
        </div>

        <button type="submit" class="btn btn-primary w-100">Registrar</button>
    </form>

    <p class="mt-3 text-center">
        ¿Ya tienes cuenta? <a href="<?= base_url('usuario/login') ?>">Iniciar sesión</a>
    </p>
</div>

</body>
</html>
