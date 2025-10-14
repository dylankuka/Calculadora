<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Login - TaxImporter</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="/Calculadora/Calculadora-1/Calculadora/public/css/ind.css">
</head>
<body class="bg-dark">

  <!-- ✅ Logo arriba izquierda -->
<div class="position-absolute" style="top: 5px; left: 3px; z-index: 1000;">
   <a href="<?= base_url('/') ?>">
       <img src="<?= base_url('img/taximporterlogo.png') ?>" 
            alt="Logo TaxImporter" 
            style="max-width: 95px; height: auto; 
                   filter: drop-shadow(2px 2px 4px rgba(0,0,0,0.2)); 
                   cursor: pointer;">
   </a>
</div>
</div>

  <div class="container mt-5" style="max-width: 420px;">
    
    <!-- Encabezado -->
    <div class="text-center mb-4">
      <h2>Iniciar Sesión</h2>
      <p class="bg-dark">Accede a tu historial de cálculos</p>
    </div>

    <!-- ✅ Mensajes de Éxito o Error -->
    <?php if (session()->getFlashdata('success')): ?>
      <div class="alert alert-success">
        <?= session()->getFlashdata('success') ?>
      </div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('error')): ?>
      <div class="alert alert-danger">
        <?= session()->getFlashdata('error') ?>
      </div>
    <?php endif; ?>

    <?php if (isset($error)): ?>
      <div class="alert alert-danger">
        <?= esc($error) ?>
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

    <!-- ✅ Formulario de Login -->
    <div class="card shadow card-custom2">
      <div class="card-body">
        <form action="<?= base_url('usuario/iniciarSesion') ?>" method="post" novalidate>
          <?= csrf_field() ?>

          <!-- Email -->
          <div class="mb-3">
            <label for="email" class="form-label">Correo Electrónico</label>
            <input type="email" 
                   class="form-control <?= isset($validation) && $validation->hasError('email') ? 'is-invalid' : '' ?>" 
                   id="email" 
                   name="email" 
                   placeholder="ejemplo@correo.com"
                   value="<?= set_value('email', $old_input['email'] ?? '') ?>" 
                   required>
            <?php if (isset($validation) && $validation->hasError('email')): ?>
              <div class="invalid-feedback">
                <?= $validation->getError('email') ?>
              </div>
            <?php endif; ?>
          </div>

          <!-- Password -->
          <div class="mb-3">
            <label for="password" class="form-label">Contraseña</label>
            <input type="password" 
                   class="form-control <?= isset($validation) && $validation->hasError('password') ? 'is-invalid' : '' ?>" 
                   id="password" 
                   name="password" 
                   placeholder="********"
                   required>
            <?php if (isset($validation) && $validation->hasError('password')): ?>
              <div class="invalid-feedback">
                <?= $validation->getError('password') ?>
              </div>
            <?php endif; ?>
          </div>

          <!-- Botón -->
          <button type="submit" class="btn card-custom textcolor w-100">Entrar</button>
        </form>
      </div>
    </div>

  <!-- Link a registro -->
<p class="mt-3 text-center">
  ¿No tienes cuenta? <a href="<?= base_url('usuario/registro') ?>">Regístrate aquí</a>
  <br>
  <a href="<?= base_url('usuario/olvide-password') ?>">¿Olvidaste tu contraseña?</a>
</p>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

