<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Confirmación de eliminación de cuenta — <?= htmlspecialchars(APP_NAME, ENT_QUOTES, 'UTF-8') ?></title>
</head>
<body style="margin:0;padding:20px;background:#f5f5f5;font-family:Arial,Helvetica,sans-serif;color:#1a1a1a">
    <div style="max-width:560px;margin:0 auto;background:#fff;border-radius:12px;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,.06)">
        <div style="background:#FF2D75;padding:24px;text-align:center">
            <h1 style="color:#fff;margin:0;font-size:22px;letter-spacing:.3px">PlacerSelecto</h1>
        </div>
        <div style="padding:32px 28px">
            <h2 style="margin:0 0 16px;font-size:20px;color:#1a1a1a">
                Solicitud de eliminación recibida
            </h2>
            <p style="margin:0 0 16px;font-size:15px;line-height:1.55;color:#444">
                Hola<?= !empty($nombre) ? ' ' . htmlspecialchars($nombre, ENT_QUOTES, 'UTF-8') : '' ?>, recibimos tu solicitud de eliminar la cuenta. Te confirmamos los detalles:
            </p>

            <div style="background:rgba(245,158,11,.08);border:1px solid rgba(245,158,11,.2);border-radius:8px;padding:14px 16px;margin:20px 0">
                <div style="font-size:13px;color:#555;margin-bottom:6px;text-transform:uppercase;letter-spacing:.5px;font-weight:700">Cuándo se eliminará</div>
                <div style="font-size:16px;color:#222;font-weight:700">
                    <?= htmlspecialchars($fechaProg ?? '', ENT_QUOTES, 'UTF-8') ?>
                </div>
                <div style="font-size:13px;color:#666;margin-top:6px;line-height:1.5">
                    Tienes 30 días para revertirla. Si no haces nada, en esa fecha se eliminarán de forma permanente: tus perfiles, fotos, videos y documento de identidad.
                </div>
            </div>

            <p style="margin:0 0 8px;font-size:15px;line-height:1.55;color:#444">
                <strong>Si fue un error o cambias de opinión:</strong>
            </p>

            <div style="text-align:center;margin:20px 0">
                <a href="<?= htmlspecialchars($linkRevert ?? '', ENT_QUOTES, 'UTF-8') ?>"
                   style="display:inline-block;background:#10B981;color:#fff;text-decoration:none;padding:12px 28px;border-radius:8px;font-weight:700;font-size:15px">
                    Cancelar eliminación
                </a>
            </div>

            <p style="margin:20px 0 0;font-size:13px;color:#777;line-height:1.5">
                También puedes cancelar la eliminación entrando a tu cuenta normalmente desde
                <a href="<?= htmlspecialchars(APP_URL, ENT_QUOTES, 'UTF-8') ?>/login" style="color:#FF2D75"><?= htmlspecialchars(APP_URL, ENT_QUOTES, 'UTF-8') ?>/login</a>.
            </p>

            <div style="background:#fafafa;border-radius:6px;padding:12px 14px;margin-top:24px;font-size:12px;color:#777">
                <strong>Solicitud registrada:</strong> <?= htmlspecialchars(date('d/m/Y H:i'), ENT_QUOTES, 'UTF-8') ?> · IP <?= htmlspecialchars($ip ?? '—', ENT_QUOTES, 'UTF-8') ?>
                <br>
                <strong>Tokens del saldo:</strong> no se reembolsan al medio de pago (los tokens son virtuales, no canjeables).
            </div>

            <p style="margin:20px 0 0;font-size:13px;color:#777;line-height:1.5">
                <strong>¿No solicitaste esto?</strong> Cambia tu contraseña inmediatamente y escribe a
                <a href="mailto:soporte@placerselecto.com" style="color:#FF2D75">soporte@placerselecto.com</a>.
            </p>
        </div>
        <div style="background:#fafafa;padding:16px 28px;border-top:1px solid #eee;text-align:center;font-size:12px;color:#888">
            <?= htmlspecialchars(APP_NAME, ENT_QUOTES, 'UTF-8') ?> — Este es un mensaje automático, no respondas a este correo.
        </div>
    </div>
</body>
</html>
