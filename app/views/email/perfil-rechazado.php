<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Tu perfil necesita ajustes</title>
</head>
<body style="margin:0;padding:20px;background:#f5f5f5;font-family:Arial,Helvetica,sans-serif;color:#1a1a1a">
    <div style="max-width:560px;margin:0 auto;background:#fff;border-radius:12px;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,.06)">
        <div style="background:#FF2D75;padding:24px;text-align:center">
            <h1 style="color:#fff;margin:0;font-size:22px;letter-spacing:.3px">PlacerSelecto</h1>
        </div>
        <div style="padding:32px 28px">
            <h2 style="margin:0 0 16px;font-size:20px;color:#1a1a1a">Tu perfil necesita algunos ajustes</h2>
            <p style="margin:0 0 16px;font-size:15px;line-height:1.55;color:#444">
                Hola<?= !empty($nombre_usuario) ? ' ' . htmlspecialchars($nombre_usuario, ENT_QUOTES, 'UTF-8') : '' ?>,
            </p>
            <p style="margin:0 0 20px;font-size:15px;line-height:1.55;color:#444">
                Tu perfil <strong>"<?= htmlspecialchars($nombre_perfil ?? '', ENT_QUOTES, 'UTF-8') ?>"</strong> no pudo ser publicado en su forma actual. Necesita algunos cambios antes de poder volver a enviarlo a revisión.
            </p>
            <div style="background:rgba(255,193,7,.08);border:1px solid rgba(255,193,7,.25);border-radius:8px;padding:14px 18px;margin:20px 0">
                <p style="margin:0;font-size:14px;color:#444;line-height:1.55">
                    <strong>Razones comunes de rechazo:</strong> fotos no nítidas, datos incompletos, descripción que viola las políticas, falta de verificación o información engañosa.
                </p>
            </div>
            <p style="margin:0 0 20px;font-size:15px;line-height:1.55;color:#444">
                Edita tu perfil corrigiendo lo necesario y vuelve a enviarlo a revisión. Nuestro equipo lo revisará nuevamente.
            </p>
            <div style="text-align:center;margin:28px 0">
                <a href="<?= htmlspecialchars($url_editar ?? '#', ENT_QUOTES, 'UTF-8') ?>"
                   style="display:inline-block;background:#FF2D75;color:#fff;text-decoration:none;font-weight:700;padding:14px 32px;border-radius:10px;font-size:15px;letter-spacing:.3px">
                    Editar mi perfil
                </a>
            </div>
            <p style="margin:24px 0 0;font-size:13px;color:#777;line-height:1.5">
                Si crees que se trata de un error o necesitas más detalles, escríbenos desde el panel de soporte y te ayudaremos.
            </p>
        </div>
        <div style="background:#fafafa;padding:16px 28px;border-top:1px solid #eee;text-align:center;font-size:12px;color:#888">
            <?= htmlspecialchars(APP_NAME, ENT_QUOTES, 'UTF-8') ?> — Este es un mensaje automático, no respondas a este correo.
        </div>
    </div>
</body>
</html>
