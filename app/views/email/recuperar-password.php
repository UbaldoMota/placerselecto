<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Recuperar contraseña</title>
</head>
<body style="margin:0;padding:20px;background:#f5f5f5;font-family:Arial,Helvetica,sans-serif;color:#1a1a1a">
    <div style="max-width:560px;margin:0 auto;background:#fff;border-radius:12px;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,.06)">
        <div style="background:#FF2D75;padding:24px;text-align:center">
            <h1 style="color:#fff;margin:0;font-size:22px;letter-spacing:.3px">PlacerSelecto</h1>
        </div>
        <div style="padding:32px 28px">
            <h2 style="margin:0 0 16px;font-size:18px;color:#1a1a1a">Recupera el acceso a tu cuenta</h2>
            <p style="margin:0 0 20px;font-size:15px;line-height:1.55;color:#444">
                Hola<?= !empty($nombre) ? ' ' . htmlspecialchars($nombre, ENT_QUOTES, 'UTF-8') : '' ?>,<br>
                Recibimos una solicitud para restablecer tu contraseña. Da clic en el botón para crear una nueva. El enlace tiene validez de <strong>1 hora</strong>.
            </p>
            <div style="text-align:center;margin:28px 0">
                <a href="<?= htmlspecialchars($link ?? '#', ENT_QUOTES, 'UTF-8') ?>"
                   style="display:inline-block;background:#FF2D75;color:#fff;text-decoration:none;font-weight:700;padding:14px 32px;border-radius:10px;font-size:15px;letter-spacing:.3px">
                    Restablecer contraseña
                </a>
            </div>
            <p style="margin:24px 0 8px;font-size:13px;color:#777;line-height:1.5">
                Si el botón no funciona, copia y pega este enlace en tu navegador:
            </p>
            <p style="margin:0 0 24px;font-size:12px;color:#FF2D75;word-break:break-all;line-height:1.5">
                <?= htmlspecialchars($link ?? '', ENT_QUOTES, 'UTF-8') ?>
            </p>
            <p style="margin:24px 0 0;font-size:13px;color:#777;line-height:1.5">
                Si no solicitaste este cambio, puedes ignorar este correo. Tu contraseña actual seguirá funcionando.
            </p>
        </div>
        <div style="background:#fafafa;padding:16px 28px;border-top:1px solid #eee;text-align:center;font-size:12px;color:#888">
            <?= htmlspecialchars(APP_NAME, ENT_QUOTES, 'UTF-8') ?> — Este es un mensaje automático, no respondas a este correo.
        </div>
    </div>
</body>
</html>
