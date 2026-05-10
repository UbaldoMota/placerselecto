<?php
/** @var array  $usuario */
/** @var string $csrfField */

$fechaProg = $usuario['eliminacion_programada_para'] ?? null;
$diasRest  = $fechaProg ? max(0, (int)floor((strtotime($fechaProg) - time()) / 86400)) : 0;
?>
<div class="container py-4" style="max-width:560px">

    <div class="card border-warning">
        <div class="card-body text-center">
            <div style="width:72px;height:72px;border-radius:50%;background:rgba(245,158,11,.12);display:flex;align-items:center;justify-content:center;color:var(--color-warning);font-size:2.2rem;margin:0 auto 16px">
                <i class="bi bi-clock-history"></i>
            </div>

            <h1 class="h5 fw-bold mb-2">Tu cuenta está pendiente de eliminación</h1>

            <p class="text-muted mb-3" style="font-size:.92rem">
                Solicitaste eliminar tu cuenta. Si no haces nada, se borrará el
                <strong><?= e(date('d/m/Y H:i', strtotime($fechaProg))) ?></strong>
                <?php if ($diasRest > 0): ?>
                (en <?= $diasRest ?> día<?= $diasRest === 1 ? '' : 's' ?>).
                <?php endif; ?>
            </p>

            <div class="alert alert-warning text-start" style="font-size:.85rem">
                <strong>Mientras tanto:</strong>
                <ul class="mb-0 mt-2" style="padding-left:1.2rem">
                    <li>Tus perfiles están ocultos del directorio.</li>
                    <li>No puedes navegar el sitio normalmente.</li>
                    <li>Si cambias de opinión, puedes cancelar la eliminación abajo.</li>
                </ul>
            </div>

            <form method="POST" action="<?= APP_URL ?>/mi-cuenta/cancelar-eliminacion" class="d-flex flex-column gap-2">
                <?= $csrfField ?>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-arrow-counterclockwise me-1"></i>Cancelar eliminación y restaurar mi cuenta
                </button>
                <a href="<?= APP_URL ?>/logout" class="btn btn-secondary btn-sm">
                    <i class="bi bi-box-arrow-right me-1"></i>Cerrar sesión
                </a>
            </form>
        </div>
    </div>

    <p class="text-muted text-center mt-3" style="font-size:.78rem">
        Si tienes dudas, escríbenos a <a href="mailto:soporte@placerselecto.com">soporte@placerselecto.com</a>.
    </p>

</div>
