<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Recuperar Contraseña - TaxImporter</title>
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
      <h2>🔐 Recuperar Contraseña</h2>
      <p>Ingresa tu email para recibir instrucciones</p>
    </div>

    <?php if (session()->getFlashdata('success')): ?>
      <div class="alert alert-success"><?= session()->getFlashdata('success') ?></div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('error')): ?>
      <div class="alert alert-danger"><?= session()->getFlashdata('error') ?></div>
    <?php endif; ?>

    <div class="card shadow card-custom2">
      <div class="card-body">
        <form action="<?= base_url('usuario/enviar-recuperacion') ?>" method="post">
          <?= csrf_field() ?>
          
          <div class="mb-3">
            <label for="email" class="form-label">Correo Electrónico</label>
            <input type="email" class="form-control" id="email" name="email" placeholder="ejemplo@correo.com" required>
          </div>

          <button type="submit" class="btn card-custom textcolor w-100">Enviar Email</button>
        </form>
      </div>
    </div>

    <p class="mt-3 text-center">
      <a href="<?= base_url('usuario/login') ?>">Volver al login</a>
    </p>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>