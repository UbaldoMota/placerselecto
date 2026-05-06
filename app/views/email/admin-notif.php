<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title><?= htmlspecialchars($titulo ?? 'Notificación', ENT_QUOTES, 'UTF-8') ?></title>
</head>
<body style="margin:0;padding:20px;background:#f5f5f5;font-family:Arial,Helvetica,sans-serif;color:#1a1a1a">
    <div style="max-width:600px;margin:0 auto;background:#fff;border-radius:12px;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,.06)">
        <div style="background:#FF2D75;padding:20px;text-align:center">
            <h1 style="color:#fff;margin:0;font-size:20px;letter-spacing:.3px"><?= htmlspecialchars(APP_NAME, ENT_QUOTES, 'UTF-8') ?> · Notificación administrativa</h1>
        </div>
        <div style="padding:24px 28px">
            <h2 style="margin:0 0 14px;font-size:18px;color:#1a1a1a"><?= htmlspecialchars($titulo ?? '', ENT_QUOTES, 'UTF-8') ?></h2>

            <p style="margin:0 0 18px;font-size:14px;line-height:1.6;color:#444"><?= htmlspecialchars($mensaje ?? '', ENT_QUOTES, 'UTF-8') ?></p>

            <table cellpadding="6" cellspacing="0" style="font-size:13px;color:#666;margin-bottom:18px">
                <tr>
                    <td style="font-weight:bold;width:80px">Tipo:</td>
                    <td><?= htmlspecialchars($tipo ?? '-', ENT_QUOTES, 'UTF-8') ?></td>
                </tr>
                <tr>
                    <td style="font-weight:bold">Fecha:</td>
                    <td><?= htmlspecialchars($fecha ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                </tr>
            </table>

            <div style="text-align:center;margin:24px 0">
                <a href="<?= htmlspecialchars($url ?? '#', ENT_QUOTES, 'UTF-8') ?>"
                   style="display:inline-block;background:#FF2D75;color:#fff;text-decoration:none;font-weight:700;padding:12px 28px;border-radius:10px;font-size:14px;letter-spacing:.3px">
                    Abrir en el panel
                </a>
            </div>

            <p style="margin:18px 0 0;font-size:12px;color:#888;line-height:1.5">
                Recibes este correo porque eres administrador del Sitio. Para dejar de recibir
                estas notificaciones, modifica la configuración <code>ADMIN_NOTIFY_EMAIL</code>.
            </p>
        </div>
        <div style="background:#fafafa;padding:14px 28px;border-top:1px solid #eee;text-align:center;font-size:12px;color:#888">
            <?= htmlspecialchars(APP_NAME, ENT_QUOTES, 'UTF-8') ?> — Mensaje automático.
        </div>
    </div>
</body>
</html>
