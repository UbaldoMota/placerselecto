<?php
/**
 * partials/back-nav.php
 * Botones de navegación de regreso: opcional "link previo" + siempre "Dashboard".
 * Uso:
 *   $backExtra = ['url' => '/mis-perfiles', 'label' => 'Mis perfiles', 'icon' => 'bi-person-lines-fill'];
 *   require VIEWS_PATH . '/partials/back-nav.php';
 *
 * Puedes omitir $backExtra si solo necesitas el botón Dashboard.
 */
$__extra = $backExtra ?? null;
?>
<div class="d-flex gap-2">
    <?php if (is_array($__extra) && !empty($__extra['url'])): ?>
    <a href="<?= APP_URL . e($__extra['url']) ?>" class="btn btn-sm btn-secondary">
        <i class="bi <?= e($__extra['icon'] ?? 'bi-arrow-left') ?> me-1"></i><?= e($__extra['label']) ?>
    </a>
    <?php endif; ?>
    <a href="<?= APP_URL ?>/dashboard" class="btn btn-sm btn-secondary">
        <i class="bi bi-house me-1"></i>Dashboard
    </a>
</div>
<?php $backExtra = null; // limpiar para siguientes includes ?>
