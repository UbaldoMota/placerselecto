<?php /** @var array $tarifas */ ?>
<div class="container py-4" style="max-width:800px">

    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h4 fw-bold mb-1"><i class="bi bi-tag text-primary me-2"></i>Tarifas de consumo</h1>
            <p class="text-muted mb-0" style="font-size:.88rem">
                Cuántos tokens consume el usuario por cada hora de publicidad.
            </p>
        </div>
        <a href="<?= APP_URL ?>/admin/tokens" class="btn btn-sm btn-secondary">
            <i class="bi bi-arrow-left me-1"></i>Resumen
        </a>
    </div>

    <?php
    $tipos = [
        'top'       => ['label' => 'Top — aparecer primero en su municipio', 'icono' => 'arrow-up-square-fill', 'color' => 'primary'],
        'resaltado' => ['label' => 'Resaltado — destaque visual sin subir en orden', 'icono' => 'stars', 'color' => 'warning'],
    ];
    foreach ($tipos as $tipo => $meta):
        $t = $tarifas[$tipo] ?? ['tokens_por_hora' => 0, 'descripcion' => '', 'fecha_actualizacion' => null];
    ?>
    <div class="card mb-3">
        <div class="card-header d-flex justify-content-between align-items-center">
            <strong class="text-<?= e($meta['color']) ?>">
                <i class="bi bi-<?= e($meta['icono']) ?> me-1"></i><?= e($meta['label']) ?>
            </strong>
            <?php if (!empty($t['fecha_actualizacion'])): ?>
            <small class="text-muted">Actualizada: <?= e($t['fecha_actualizacion']) ?></small>
            <?php endif; ?>
        </div>
        <div class="card-body">
            <form method="POST" action="<?= APP_URL ?>/admin/tokens/tarifa/<?= e($tipo) ?>" class="row g-3 align-items-end">
                <?= $csrfField ?>
                <div class="col-md-3">
                    <label class="form-label">Tokens por hora</label>
                    <input type="number" name="tokens_por_hora" class="form-control"
                           value="<?= (int)$t['tokens_por_hora'] ?>" min="1" max="999" required>
                </div>
                <div class="col-md-7">
                    <label class="form-label">Descripción (visible al usuario)</label>
                    <input type="text" name="descripcion" class="form-control"
                           value="<?= e($t['descripcion'] ?? '') ?>" maxlength="200">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-check-lg me-1"></i>Guardar
                    </button>
                </div>
            </form>

            <div class="mt-3 p-2 rounded" style="background:var(--color-bg-alt);font-size:.82rem">
                <i class="bi bi-info-circle text-primary me-1"></i>
                <span class="text-muted">
                    Ejemplos: a <strong><?= (int)$t['tokens_por_hora'] ?></strong> tokens/hora,
                    10h = <strong><?= (int)$t['tokens_por_hora'] * 10 ?></strong> tokens,
                    24h = <strong><?= (int)$t['tokens_por_hora'] * 24 ?></strong> tokens.
                </span>
            </div>
        </div>
    </div>
    <?php endforeach; ?>

</div>
