<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Confirma tu cuenta en <?= htmlspecialchars(APP_NAME, ENT_QUOTES, 'UTF-8') ?></title>
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
            <p style="margin:0 0 20px;font-size:15px;line-height:1.55;color:#444">
                Gracias por registrarte en <strong>PlacerSelecto</strong>.
                Para poder iniciar sesión, confirma tu dirección de correo haciendo clic en el siguiente botón:
            </p>
            <div style="text-align:center;margin:32px 0">
                <a href="<?= htmlspecialchars($link, ENT_QUOTES, 'UTF-8') ?>"
                   style="display:inline-block;background:#FF2D75;color:#fff;text-decoration:none;padding:14px 36px;border-radius:8px;font-weight:700;font-size:16px">
                    Confirmar mi correo
                </a>
            </div>
            <p style="margin:24px 0 12px;font-size:13px;line-height:1.55;color:#666">
                Si el botón no funciona, copia y pega este enlace en tu navegador:
            </p>
            <p style="margin:0 0 24px;font-size:12px;line-height:1.45;color:#FF2D75;word-break:break-all;background:rgba(255,45,117,.05);padding:10px 12px;border-radius:6px">
                <?= htmlspecialchars($link, ENT_QUOTES, 'UTF-8') ?>
            </p>
            <p style="margin:20px 0 0;font-size:13px;color:#777;line-height:1.5">
                Este enlace expira en <strong>24 horas</strong>.
                Si no te registraste, puedes ignorar este correo y tu cuenta será eliminada automáticamente.
            </p>
        </div>
        <div style="background:#fafafa;padding:16px 28px;border-top:1px solid #eee;text-align:center;font-size:12px;color:#888">
            <?= htmlspecialchars(APP_NAME, ENT_QUOTES, 'UTF-8') ?> — Este es un mensaje automático, no respondas a este correo.
        </div>
    </div>
</body>
</html>
