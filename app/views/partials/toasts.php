<?php
/**
 * partials/toasts.php
 * Render de los flash messages como toasts.
 * Se incluye tanto desde `layout.php` como desde vistas standalone (auth/*).
 */
$toastMeta = [
    'success' => ['icono' => 'check-circle-fill',        'titulo' => 'Éxito'],
    'error'   => ['icono' => 'x-octagon-fill',           'titulo' => 'Error'],
    'warning' => ['icono' => 'exclamation-triangle-fill','titulo' => 'Aviso'],
    'info'    => ['icono' => 'info-circle-fill',         'titulo' => 'Información'],
];
?>
<div id="toast-container" aria-live="polite" aria-atomic="true">
    <?php foreach (($flashMessages ?? []) as $type => $msgs):
        $variant = $type === 'error' ? 'danger' : $type;
        $meta    = $toastMeta[$type] ?? ['icono' => 'bell-fill', 'titulo' => 'Aviso'];
        foreach ($msgs as $msg):
    ?>
    <div class="toast-item toast-<?= e($variant) ?>" role="alert">
        <div class="toast-icon"><i class="bi bi-<?= e($meta['icono']) ?>"></i></div>
        <div class="toast-body">
            <div class="toast-title"><?= e($meta['titulo']) ?></div>
            <div class="toast-message"><?= $msg ?></div>
        </div>
        <button type="button" class="toast-close" aria-label="Cerrar">
            <i class="bi bi-x-lg"></i>
        </button>
        <div class="toast-progress" aria-hidden="true"></div>
    </div>
    <?php endforeach; endforeach; ?>
</div>

<script src="<?= APP_URL ?>/public/assets/js/toasts.js" defer></script>
