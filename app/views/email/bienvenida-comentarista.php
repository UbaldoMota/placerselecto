<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Bienvenido a <?= htmlspecialchars(APP_NAME, ENT_QUOTES, 'UTF-8') ?></title>
</head>
<body style="margin:0;padding:20px;background:#f5f5f5;font-family:Arial,Helvetica,sans-serif;color:#1a1a1a">
    <div style="max-width:560px;margin:0 auto;background:#fff;border-radius:12px;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,.06)">
        <div style="background:#FF2D75;padding:24px;text-align:center">
            <h1 style="color:#fff;margin:0;font-size:22px;letter-spacing:.3px">PlacerSelecto</h1>
        </div>
        <div style="padding:32px 28px">
            <h2 style="margin:0 0 16px;font-size:20px;color:#1a1a1a">
                ¡Hola<?= !empty($nombre) ? ' ' . htmlspecialchars($nombre, ENT_QUOTES, 'UTF-8') : '' ?>! 👋
            </h2>
            <p style="margin:0 0 16px;font-size:15px;line-height:1.55;color:#444">
                Tu cuenta en <strong>PlacerSelecto</strong> fue creada correctamente.
            </p>
            <div style="background:rgba(13,110,253,.06);border:1px solid rgba(13,110,253,.15);border-radius:8px;padding:14px 16px;margin:20px 0">
                <div style="font-size:13px;color:#555;margin-bottom:6px;text-transform:uppercase;letter-spacing:.5px;font-weight:700">
                    Tu cuenta
                </div>
                <div style="font-size:15px;color:#222">
                    <strong>Tipo:</strong> Comentarista<br>
                    <strong>Correo:</strong> <?= htmlspecialchars($email ?? '', ENT_QUOTES, 'UTF-8') ?>
                </div>
            </div>
            <p style="margin:0 0 20px;font-size:15px;line-height:1.55;color:#444">
                Ya puedes comentar y calificar perfiles publicados en la plataforma.
            </p>
            <div style="text-align:center;margin:28px 0">
                <a href="<?= htmlspecialchars(APP_URL, ENT_QUOTES, 'UTF-8') ?>/login"
                   style="display:inline-block;background:#FF2D75;color:#fff;text-decoration:none;padding:12px 28px;border-radius:8px;font-weight:700;font-size:15px">
                    Iniciar sesión
                </a>
            </div>
            <p style="margin:24px 0 0;font-size:13px;color:#777;line-height:1.5">
                Si no creaste esta cuenta, puedes ignorar este correo o escribir a
                <a href="mailto:soporte@placerselecto.com" style="color:#FF2D75">soporte@placerselecto.com</a>.
            </p>
        </div>
        <div style="background:#fafafa;padding:16px 28px;border-top:1px solid #eee;text-align:center;font-size:12px;color:#888">
            <?= htmlspecialchars(APP_NAME, ENT_QUOTES, 'UTF-8') ?> — Este es un mensaje automático, no respondas a este correo.
        </div>
    </div>
</body>
</html>
