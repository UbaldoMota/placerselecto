<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Tu contraseña fue cambiada — <?= htmlspecialchars(APP_NAME, ENT_QUOTES, 'UTF-8') ?></title>
</head>
<body style="margin:0;padding:20px;background:#f5f5f5;font-family:Arial,Helvetica,sans-serif;color:#1a1a1a">
    <div style="max-width:560px;margin:0 auto;background:#fff;border-radius:12px;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,.06)">
        <div style="background:#FF2D75;padding:24px;text-align:center">
            <h1 style="color:#fff;margin:0;font-size:22px;letter-spacing:.3px">PlacerSelecto</h1>
        </div>
        <div style="padding:32px 28px">
            <h2 style="margin:0 0 16px;font-size:20px;color:#1a1a1a">
                Tu contraseña fue cambiada
            </h2>
            <p style="margin:0 0 16px;font-size:15px;line-height:1.55;color:#444">
                Hola<?= !empty($nombre) ? ' ' . htmlspecialchars($nombre, ENT_QUOTES, 'UTF-8') : '' ?>, te avisamos que la contraseña de tu cuenta acaba de cambiarse.
            </p>
            <div style="background:rgba(13,110,253,.06);border:1px solid rgba(13,110,253,.15);border-radius:8px;padding:14px 16px;margin:20px 0">
                <div style="font-size:13px;color:#555;margin-bottom:6px;text-transform:uppercase;letter-spacing:.5px;font-weight:700">Detalles del cambio</div>
                <div style="font-size:14px;color:#222;line-height:1.6">
                    <strong>Fecha:</strong> <?= htmlspecialchars($fecha ?? date('d/m/Y H:i'), ENT_QUOTES, 'UTF-8') ?><br>
                    <strong>IP:</strong> <?= htmlspecialchars($ip ?? '—', ENT_QUOTES, 'UTF-8') ?>
                </div>
            </div>
            <p style="margin:0 0 16px;font-size:15px;line-height:1.55;color:#444">
                Si fuiste tú, puedes ignorar este correo.
            </p>
            <div style="background:rgba(220,53,69,.08);border-left:3px solid #dc3545;padding:12px 16px;margin:20px 0;border-radius:4px">
                <strong style="color:#dc3545;font-size:14px">¿No fuiste tú?</strong>
                <p style="margin:6px 0 0;font-size:13px;color:#555;line-height:1.5">
                    Tu cuenta podría estar comprometida. Inicia sesión inmediatamente y cambia tu contraseña, o escribe a
                    <a href="mailto:soporte@placerselecto.com" style="color:#FF2D75">soporte@placerselecto.com</a>.
                </p>
            </div>
        </div>
        <div style="background:#fafafa;padding:16px 28px;border-top:1px solid #eee;text-align:center;font-size:12px;color:#888">
            <?= htmlspecialchars(APP_NAME, ENT_QUOTES, 'UTF-8') ?> — Este es un mensaje automático, no respondas a este correo.
        </div>
    </div>
</body>
</html>
