<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Nuevo mensaje de contacto</title>
</head>
<body style="margin:0;padding:20px;background:#f5f5f5;font-family:Arial,Helvetica,sans-serif;color:#1a1a1a">
    <div style="max-width:600px;margin:0 auto;background:#fff;border-radius:12px;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,.06)">
        <div style="background:#FF2D75;padding:20px;text-align:center">
            <h1 style="color:#fff;margin:0;font-size:20px;letter-spacing:.3px">Nuevo mensaje de contacto</h1>
        </div>
        <div style="padding:24px 28px">
            <table cellpadding="6" cellspacing="0" style="width:100%;font-size:14px;border-collapse:collapse">
                <tr>
                    <td style="font-weight:bold;color:#666;width:140px;vertical-align:top">Nombre:</td>
                    <td style="vertical-align:top"><?= htmlspecialchars($nombre ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                </tr>
                <tr>
                    <td style="font-weight:bold;color:#666;vertical-align:top">Email:</td>
                    <td style="vertical-align:top">
                        <a href="mailto:<?= htmlspecialchars($email ?? '', ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($email ?? '', ENT_QUOTES, 'UTF-8') ?></a>
                    </td>
                </tr>
                <tr>
                    <td style="font-weight:bold;color:#666;vertical-align:top">Tipo:</td>
                    <td style="vertical-align:top"><?= htmlspecialchars($asunto ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                </tr>
                <tr>
                    <td style="font-weight:bold;color:#666;vertical-align:top">IP:</td>
                    <td style="vertical-align:top"><?= htmlspecialchars($ip ?? '-', ENT_QUOTES, 'UTF-8') ?></td>
                </tr>
                <tr>
                    <td style="font-weight:bold;color:#666;vertical-align:top">Fecha:</td>
                    <td style="vertical-align:top"><?= htmlspecialchars($fecha ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                </tr>
            </table>

            <hr style="border:0;border-top:1px solid #eee;margin:18px 0">

            <div style="font-weight:bold;color:#444;margin-bottom:8px;font-size:14px">Mensaje:</div>
            <div style="background:#fafafa;border-left:3px solid #FF2D75;padding:12px 14px;font-size:14px;line-height:1.6;white-space:pre-wrap;color:#333"><?= htmlspecialchars($mensaje ?? '', ENT_QUOTES, 'UTF-8') ?></div>

            <p style="margin:20px 0 0;font-size:12px;color:#888;line-height:1.5">
                Para responder, escribe directamente a <a href="mailto:<?= htmlspecialchars($email ?? '', ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($email ?? '', ENT_QUOTES, 'UTF-8') ?></a>.
            </p>
        </div>
        <div style="background:#fafafa;padding:14px 28px;border-top:1px solid #eee;text-align:center;font-size:12px;color:#888">
            <?= htmlspecialchars(APP_NAME, ENT_QUOTES, 'UTF-8') ?> — Mensaje recibido a través del formulario de contacto del Sitio.
        </div>
    </div>
</body>
</html>
