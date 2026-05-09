<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Nueva solicitud de pago</title>
</head>
<body style="margin:0;padding:20px;background:#f5f5f5;font-family:Arial,Helvetica,sans-serif;color:#1a1a1a">
    <div style="max-width:560px;margin:0 auto;background:#fff;border-radius:12px;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,.06)">
        <div style="background:#FF2D75;padding:24px;text-align:center">
            <h1 style="color:#fff;margin:0;font-size:22px;letter-spacing:.3px">PlacerSelecto</h1>
        </div>
        <div style="padding:32px 28px">
            <h2 style="margin:0 0 16px;font-size:18px;color:#1a1a1a">Nueva solicitud de pago</h2>
            <p style="margin:0 0 16px;font-size:15px;line-height:1.55;color:#444">
                Una usuaria solicitó pagar un paquete de tokens. Envíale el link de pago por WhatsApp y, al confirmarse, marca el pago como pagado en el panel admin.
            </p>

            <div style="background:#fafafa;border:1px solid #eee;border-radius:8px;padding:16px;margin:20px 0;font-size:14px;line-height:1.7;color:#444">
                <div><strong>Usuaria:</strong> <?= htmlspecialchars($usuario_nombre ?? '—', ENT_QUOTES, 'UTF-8') ?></div>
                <div><strong>Correo:</strong> <?= htmlspecialchars($usuario_email ?? '—', ENT_QUOTES, 'UTF-8') ?></div>
                <div><strong>Paquete:</strong> <?= htmlspecialchars($paquete_nombre ?? '—', ENT_QUOTES, 'UTF-8') ?> (<?= htmlspecialchars(number_format((int)($tokens ?? 0)), ENT_QUOTES, 'UTF-8') ?> tokens)</div>
                <div><strong>Monto:</strong> $<?= htmlspecialchars(number_format((float)($monto ?? 0), 2), ENT_QUOTES, 'UTF-8') ?> MXN</div>
                <div><strong>Referencia:</strong> <?= htmlspecialchars($referencia ?? '—', ENT_QUOTES, 'UTF-8') ?></div>
            </div>

            <div style="text-align:center;margin:28px 0">
                <a href="<?= htmlspecialchars($url_admin_pagos ?? '#', ENT_QUOTES, 'UTF-8') ?>"
                   style="display:inline-block;background:#FF2D75;color:#fff;text-decoration:none;font-weight:700;padding:14px 32px;border-radius:10px;font-size:15px;letter-spacing:.3px">
                    Ver pagos pendientes
                </a>
            </div>

            <p style="margin:24px 0 0;font-size:13px;color:#777;line-height:1.5">
                Cuando el pago se confirme en tu cuenta de Mercado Pago, marca este pago como pagado y los tokens se acreditarán automáticamente a la usuaria.
            </p>
        </div>
        <div style="background:#fafafa;padding:16px 28px;border-top:1px solid #eee;text-align:center;font-size:12px;color:#888">
            <?= htmlspecialchars(APP_NAME, ENT_QUOTES, 'UTF-8') ?> — Notificación interna del sistema, no respondas a este correo.
        </div>
    </div>
</body>
</html>
