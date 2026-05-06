<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Tu perfil fue aprobado</title>
</head>
<body style="margin:0;padding:20px;background:#f5f5f5;font-family:Arial,Helvetica,sans-serif;color:#1a1a1a">
    <div style="max-width:560px;margin:0 auto;background:#fff;border-radius:12px;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,.06)">
        <div style="background:#FF2D75;padding:24px;text-align:center">
            <h1 style="color:#fff;margin:0;font-size:22px;letter-spacing:.3px">PlacerSelecto</h1>
        </div>
        <div style="padding:32px 28px">
            <h2 style="margin:0 0 16px;font-size:20px;color:#1a1a1a">¡Tu perfil ya está publicado! 🎉</h2>
            <p style="margin:0 0 16px;font-size:15px;line-height:1.55;color:#444">
                Hola<?= !empty($nombre_usuario) ? ' ' . htmlspecialchars($nombre_usuario, ENT_QUOTES, 'UTF-8') : '' ?>,
            </p>
            <p style="margin:0 0 20px;font-size:15px;line-height:1.55;color:#444">
                Tu perfil <strong>"<?= htmlspecialchars($nombre_perfil ?? '', ENT_QUOTES, 'UTF-8') ?>"</strong> pasó la revisión y ya es visible para todos los usuarios de la plataforma.
            </p>
            <div style="text-align:center;margin:28px 0">
                <a href="<?= htmlspecialchars($url_perfil ?? '#', ENT_QUOTES, 'UTF-8') ?>"
                   style="display:inline-block;background:#FF2D75;color:#fff;text-decoration:none;font-weight:700;padding:14px 32px;border-radius:10px;font-size:15px;letter-spacing:.3px">
                    Ver mi perfil publicado
                </a>
            </div>
            <p style="margin:24px 0 8px;font-size:14px;color:#444;line-height:1.55">
                <strong>¿Qué puedes hacer ahora?</strong>
            </p>
            <ul style="margin:0 0 16px;padding-left:20px;font-size:14px;color:#555;line-height:1.7">
                <li>Compartir el enlace de tu perfil con tus contactos</li>
                <li>Subir más fotos o videos para que sea más atractivo</li>
                <li>Activar un boost para aparecer arriba en los listados</li>
            </ul>
            <p style="margin:24px 0 0;font-size:13px;color:#777;line-height:1.5">
                Recuerda: mantén tus datos actualizados y responde a tiempo a las consultas para conservar buenos indicadores de confiabilidad.
            </p>
        </div>
        <div style="background:#fafafa;padding:16px 28px;border-top:1px solid #eee;text-align:center;font-size:12px;color:#888">
            <?= htmlspecialchars(APP_NAME, ENT_QUOTES, 'UTF-8') ?> — Este es un mensaje automático, no respondas a este correo.
        </div>
    </div>
</body>
</html>
