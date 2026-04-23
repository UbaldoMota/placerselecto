<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Código de verificación</title>
</head>
<body style="margin:0;padding:20px;background:#f5f5f5;font-family:Arial,Helvetica,sans-serif;color:#1a1a1a">
    <div style="max-width:560px;margin:0 auto;background:#fff;border-radius:12px;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,.06)">
        <div style="background:#FF2D75;padding:24px;text-align:center">
            <h1 style="color:#fff;margin:0;font-size:22px;letter-spacing:.3px">PlacerSelecto</h1>
        </div>
        <div style="padding:32px 28px">
            <h2 style="margin:0 0 16px;font-size:18px;color:#1a1a1a">Tu código de verificación</h2>
            <p style="margin:0 0 20px;font-size:15px;line-height:1.55;color:#444">
                Hola<?= !empty($nombre) ? ' ' . htmlspecialchars($nombre, ENT_QUOTES, 'UTF-8') : '' ?>,<br>
                Usa este código para verificar tu cuenta. Tiene una validez de <strong>10 minutos</strong>:
            </p>
            <div style="text-align:center;margin:28px 0">
                <div style="display:inline-block;background:rgba(255,45,117,.08);border:2px dashed rgba(255,45,117,.4);padding:18px 32px;border-radius:10px">
                    <div style="font-size:34px;font-weight:800;letter-spacing:8px;color:#FF2D75;font-family:'Courier New',monospace">
                        <?= htmlspecialchars($codigo ?? '------', ENT_QUOTES, 'UTF-8') ?>
                    </div>
                </div>
            </div>
            <p style="margin:24px 0 0;font-size:13px;color:#777;line-height:1.5">
                Si no solicitaste este código, puedes ignorar este correo. Nadie podrá acceder a tu cuenta sin él.
            </p>
        </div>
        <div style="background:#fafafa;padding:16px 28px;border-top:1px solid #eee;text-align:center;font-size:12px;color:#888">
            <?= htmlspecialchars(APP_NAME, ENT_QUOTES, 'UTF-8') ?> — Este es un mensaje automático, no respondas a este correo.
        </div>
    </div>
</body>
</html>
