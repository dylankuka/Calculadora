<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pegar URL de Amazon</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5">
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
            <h4 class="mb-0">Calculadora de Impuestos - Amazon Argentina</h4>
        </div>
        <div class="card-body">
            <form action="<?= base_url('calcular') ?>" method="post">
                <?= csrf_field() ?> <!-- Seguridad CSRF -->

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
