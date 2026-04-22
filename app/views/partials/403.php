<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>403 — Acceso denegado | <?= APP_NAME ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <style>
        body { background: #FFFFFF; color: #1A1A1A; font-family: Inter,system-ui,sans-serif; display: flex; align-items: center; justify-content: center; min-height: 100vh; }
        .error-code { font-size: 8rem; font-weight: 900; color: #FF2D75; line-height: 1; }
    </style>
</head>
<body>
    <div class="text-center p-4">
        <div class="error-code">403</div>
        <h2 class="mt-3 mb-2">Acceso denegado</h2>
        <p class="text-muted mb-4">No tienes permisos para acceder a esta sección.</p>
        <a href="<?= APP_URL ?>" class="btn btn-outline-secondary">Ir al inicio</a>
    </div>
</body>
</html>
