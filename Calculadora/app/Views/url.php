<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Ingresar URL - TaxImporter</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>
<body class="bg-light">

<div class="container mt-3" style="max-width: 600px;">

    <!-- Botones para login y registro -->
    <div class="d-flex justify-content-end mb-3 gap-2">
        <a href="<?= base_url('usuario/login') ?>" class="btn btn-outline-primary">Iniciar Sesión</a>
        <a href="<?= base_url('usuario/registro') ?>" class="btn btn-primary">Registrarse</a>
    </div>

    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
            <h4 class="mb-0">Calculadora de Impuestos - Amazon Argentina</h4>
        </div>
        <div class="card-body">
            <form action="<?= base_url('calcular') ?>" method="post">
                <?= csrf_field() ?>

                <div class="mb-3">
                    <label for="amazon_url" class="form-label">Pega la URL del producto</label>
                    <input type="url" name="amazon_url" id="amazon_url" class="form-control" placeholder="https://www.amazon.com/..." required>
                </div>

                <button type="submit" class="btn btn-success">Calcular impuestos</button>
            </form>
        </div>
    </div>
</div>

</body>
</html>
