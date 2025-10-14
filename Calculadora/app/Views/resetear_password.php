<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Resetear Contraseña - TaxImporter</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="/Calculadora/Calculadora-1/Calculadora/public/css/ind.css">
</head>
<body class="bg-dark">
  <div class="position-absolute" style="top: 5px; left: 3px; z-index: 1000;">
    <a href="<?= base_url('/') ?>">
      <img src="<?= base_url('img/taximporterlogo.png') ?>" alt="Logo" style="max-width: 95px;">
    </a>
  </div>

  <div class="container mt-5" style="max-width: 420px;">
    <div class="text-center mb-4">
      <h2>Crear Nueva Contraseña</h2>
    </div>

    <?php if (isset($validation) && $validation): ?>
      <div class="alert alert-danger">
        <?php foreach ($validation->getErrors() as $error): ?>
          <div>❌ <?= $error ?></div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <div class="card shadow card-custom2">
      <div class="card-body">
        <form action="<?= base_url('usuario/guardar-password') ?>" method="post">
          <?= csrf_field() ?>
          <input type="hidden" name="token" value="<?= $token ?>">
          
          <div class="mb-3">
            <label for="password" class="form-label">Nueva Contraseña</label>
            <input type="password" class="form-control" id="password" name="password" required>
            <small class="form-text text-muted">Mín. 6 caracteres, 1 mayúscula, 1 minúscula, 1 número</small>
          </div>

          <div class="mb-3">
            <label for="pass_confirm" class="form-label">Confirmar Contraseña</label>
            <input type="password" class="form-control" id="pass_confirm" name="pass_confirm" required>
          </div>

          <button type="submit" class="btn card-custom textcolor w-100">Guardar Contraseña</button>
        </form>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>