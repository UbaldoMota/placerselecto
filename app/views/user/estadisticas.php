<?php
/**
 * user/estadisticas.php — Estadísticas de visitas y clics para el usuario.
 */
$hoy       = date('d/m/Y');
$semLabels = [];
foreach ($diasGrafico as $d) {
    $semLabels[] = date('D', strtotime($d['fecha'])); // lun, mar…
}

// Máximos para escalar las barras
$maxVistasGraf = max(1, ...array_column($diasGrafico, 'visitas'));
$maxWaGraf     = max(1, ...array_column($diasGrafico, 'clicks_whatsapp'));
$maxCombinado  = max($maxVistasGraf, $maxWaGraf, 1);
?>

<div class="container py-4" style="max-width:960px">

    <div class="d-flex flex-column flex-sm-row align-items-start align-items-sm-center justify-content-between gap-3 mb-4">
        <div>
            <h1 class="h4 fw-bold mb-1">
                <i class="bi bi-graph-up text-primary me-2"></i>Mis estadísticas
            </h1>
            <p class="text-muted mb-0" style="font-size:.875rem">
                Datos actualizados al <?= $hoy ?>
            </p>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= APP_URL ?>/mis-perfiles" class="btn btn-sm btn-secondary">
                <i class="bi bi-person-lines-fill me-1"></i>Mis perfiles
            </a>
            <a href="<?= APP_URL ?>/dashboard" class="btn btn-sm btn-secondary">
                <i class="bi bi-house me-1"></i>Dashboard
            </a>
        </div>
    </div>

    <!-- TARJETAS RESUMEN -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="stat-card__value" style="color:var(--color-primary)">
                    <?= number_format((int)$totalStats['total_visitas']) ?>
                </div>
                <div class="stat-card__label">Visitas totales</div>
                <i class="bi bi-eye stat-card__icon"></i>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="stat-card__value" style="color:#17a2b8">
                    <?= number_format((int)$totalStats['visitas_hoy']) ?>
                </div>
                <div class="stat-card__label">Visitas hoy</div>
                <i class="bi bi-calendar-day stat-card__icon"></i>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="stat-card__value" style="color:#10B981">
                    <?= number_format((int)$totalStats['total_whatsapp']) ?>
                </div>
                <div class="stat-card__label">Clics WhatsApp</div>
                <i class="bi bi-whatsapp stat-card__icon"></i>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="stat-card__value" style="color:#F59E0B">
                    <?= number_format((int)$totalStats['visitas_semana']) ?>
                </div>
                <div class="stat-card__label">Visitas esta semana</div>
                <i class="bi bi-calendar-week stat-card__icon"></i>
            </div>
        </div>
    </div>

    <!-- GRÁFICA ÚLTIMOS 7 DÍAS (todos los perfiles combinados) -->
    <div class="card mb-4">
        <div class="card-header d-flex align-items-center justify-content-between">
            <span class="fw-semibold" style="font-size:.875rem">
                <i class="bi bi-bar-chart-fill text-primary me-2"></i>Últimos 7 días — todos los perfiles
            </span>
            <div class="d-flex align-items-center gap-3" style="font-size:.72rem;color:var(--color-text-muted)">
                <span><span style="display:inline-block;width:10px;height:10px;background:var(--color-primary);border-radius:2px;margin-right:4px"></span>Visitas</span>
                <span><span style="display:inline-block;width:10px;height:10px;background:#25d366;border-radius:2px;margin-right:4px"></span>WhatsApp</span>
            </div>
        </div>
        <div class="card-body" style="padding:1.25rem">
            <div style="display:flex;gap:6px;align-items:flex-end;height:120px">
                <?php foreach ($diasGrafico as $i => $dia):
                    $pctV  = $maxCombinado > 0 ? round($dia['visitas']         / $maxCombinado * 100) : 0;
                    $pctWa = $maxCombinado > 0 ? round($dia['clicks_whatsapp'] / $maxCombinado * 100) : 0;
                    $esHoy = $dia['fecha'] === date('Y-m-d');
                    $label = $esHoy ? 'Hoy' : mb_strtoupper(substr((new DateTime($dia['fecha']))->format('D'), 0, 3));
                ?>
                <div style="flex:1;display:flex;flex-direction:column;align-items:center;gap:2px;min-width:0">
                    <!-- Barra visitas -->
                    <div style="width:100%;display:flex;gap:2px;align-items:flex-end;height:100px">
                        <div style="flex:1;border-radius:3px 3px 0 0;min-height:2px;
                                    background:<?= $esHoy ? 'var(--color-primary)' : 'rgba(255,45,117,.45)' ?>;
                                    height:<?= max(2, $pctV) ?>%"
                             title="<?= date('d/m', strtotime($dia['fecha'])) ?>: <?= number_format((int)$dia['visitas']) ?> visitas">
                        </div>
                        <div style="flex:1;border-radius:3px 3px 0 0;min-height:2px;
                                    background:<?= $esHoy ? '#25d366' : 'rgba(37,211,102,.4)' ?>;
                                    height:<?= max(2, $pctWa) ?>%"
                             title="<?= date('d/m', strtotime($dia['fecha'])) ?>: <?= number_format((int)$dia['clicks_whatsapp']) ?> clics WA">
                        </div>
                    </div>
                    <div style="font-size:.62rem;color:<?= $esHoy ? 'var(--color-primary)' : 'var(--color-text-muted)' ?>;font-weight:<?= $esHoy ? '700' : '400' ?>;white-space:nowrap">
                        <?= $label ?>
                    </div>
                    <div style="font-size:.58rem;color:var(--color-text-muted)">
                        <?= (int)$dia['visitas'] ?>v <?php if ($dia['clicks_whatsapp'] > 0): ?><span style="color:#25d366"><?= (int)$dia['clicks_whatsapp'] ?>w</span><?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- ESTADÍSTICAS POR PERFIL -->
    <?php if (empty($porPerfil)): ?>
    <div class="card">
        <div class="card-body text-center py-5 text-muted">
            <i class="bi bi-person-x" style="font-size:3rem;opacity:.3"></i>
            <p class="mt-3 mb-3">Aún no tienes perfiles creados.</p>
            <a href="<?= APP_URL ?>/perfil/nuevo" class="btn btn-primary btn-sm">
                <i class="bi bi-plus-lg me-1"></i>Crear perfil
            </a>
        </div>
    </div>
    <?php else: ?>

    <h2 class="h6 fw-bold mb-3" style="color:var(--color-text-muted);text-transform:uppercase;letter-spacing:.05em;font-size:.75rem">
        <i class="bi bi-person-lines-fill me-2"></i>Desglose por perfil
    </h2>

    <div class="d-flex flex-column gap-3">
    <?php foreach ($porPerfil as $p):
        $imgUrl   = !empty($p['imagen_token']) ? APP_URL . '/img/' . $p['imagen_token'] : null;
        $serie    = $seriesPerfil[(int)$p['id']] ?? [];
        $maxS     = max(1, ...array_merge([1], array_column($serie, 'visitas'), array_column($serie, 'clicks_whatsapp')));
        $estadoMap = [
            'publicado' => ['badge-publicado','Publicado'],
            'pendiente' => ['badge-pendiente','En revisión'],
            'rechazado' => ['badge-rechazado','Rechazado'],
        ];
        [$cls, $lbl] = $estadoMap[$p['estado']] ?? ['','?'];
    ?>
    <div class="card">
        <div class="card-body" style="padding:1rem 1.25rem">
            <!-- Encabezado del perfil -->
            <div class="d-flex align-items-center gap-3 mb-3">
                <?php if ($imgUrl): ?>
                <img src="<?= e($imgUrl) ?>" alt=""
                     style="width:48px;height:48px;object-fit:cover;border-radius:8px;border:1px solid var(--color-border);flex-shrink:0">
                <?php else: ?>
                <div style="width:48px;height:48px;border-radius:8px;background:var(--color-bg-card2);border:1px solid var(--color-border);display:flex;align-items:center;justify-content:center;flex-shrink:0;color:var(--color-text-muted)">
                    <i class="bi bi-person"></i>
                </div>
                <?php endif; ?>
                <div class="flex-grow-1 min-width-0">
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <span class="fw-bold" style="font-size:.95rem"><?= e($p['nombre']) ?></span>
                        <span class="badge-estado <?= $cls ?>" style="font-size:.65rem"><?= $lbl ?></span>
                    </div>
                </div>
                <a href="<?= APP_URL ?>/perfil/<?= (int)$p['id'] ?>/editar"
                   class="btn btn-sm btn-secondary flex-shrink-0">
                    <i class="bi bi-pencil"></i>
                </a>
            </div>

            <!-- Métricas -->
            <div class="row g-2 mb-3">
                <div class="col-6 col-sm-3">
                    <div style="background:rgba(255,45,117,.07);border:1px solid rgba(255,45,117,.15);border-radius:8px;padding:.6rem .8rem;text-align:center">
                        <div class="fw-bold" style="font-size:1.1rem;color:var(--color-primary)"><?= number_format((int)$p['total_visitas']) ?></div>
                        <div style="font-size:.7rem;color:var(--color-text-muted)">Visitas totales</div>
                    </div>
                </div>
                <div class="col-6 col-sm-3">
                    <div style="background:rgba(23,162,184,.07);border:1px solid rgba(23,162,184,.15);border-radius:8px;padding:.6rem .8rem;text-align:center">
                        <div class="fw-bold" style="font-size:1.1rem;color:#17a2b8"><?= number_format((int)$p['visitas_hoy']) ?></div>
                        <div style="font-size:.7rem;color:var(--color-text-muted)">Hoy</div>
                    </div>
                </div>
                <div class="col-6 col-sm-3">
                    <div style="background:rgba(37,211,102,.07);border:1px solid rgba(37,211,102,.15);border-radius:8px;padding:.6rem .8rem;text-align:center">
                        <div class="fw-bold" style="font-size:1.1rem;color:#25d366"><?= number_format((int)$p['total_whatsapp']) ?></div>
                        <div style="font-size:.7rem;color:var(--color-text-muted)">Clics WA totales</div>
                    </div>
                </div>
                <div class="col-6 col-sm-3">
                    <div style="background:rgba(255,193,7,.07);border:1px solid rgba(255,193,7,.15);border-radius:8px;padding:.6rem .8rem;text-align:center">
                        <div class="fw-bold" style="font-size:1.1rem;color:#F59E0B"><?= number_format((int)$p['visitas_semana']) ?></div>
                        <div style="font-size:.7rem;color:var(--color-text-muted)">Esta semana</div>
                    </div>
                </div>
            </div>

            <!-- Mini gráfica 7 días -->
            <?php if (!empty($serie)): ?>
            <div style="font-size:.7rem;color:var(--color-text-muted);margin-bottom:.4rem">Últimos 7 días</div>
            <div style="display:flex;gap:4px;align-items:flex-end;height:50px">
                <?php foreach ($serie as $dia):
                    $pV  = $maxS > 0 ? round($dia['visitas']         / $maxS * 100) : 0;
                    $pWa = $maxS > 0 ? round($dia['clicks_whatsapp'] / $maxS * 100) : 0;
                    $esH = $dia['fecha'] === date('Y-m-d');
                ?>
                <div style="flex:1;display:flex;flex-direction:column;align-items:center;gap:1px;min-width:0"
                     title="<?= date('d/m', strtotime($dia['fecha'])) ?>: <?= (int)$dia['visitas'] ?>v / <?= (int)$dia['clicks_whatsapp'] ?>w">
                    <div style="width:100%;display:flex;gap:1px;align-items:flex-end;height:40px">
                        <div style="flex:1;border-radius:2px 2px 0 0;min-height:1px;
                                    background:<?= $esH ? 'var(--color-primary)' : 'rgba(255,45,117,.4)' ?>;
                                    height:<?= max(1, $pV) ?>%"></div>
                        <div style="flex:1;border-radius:2px 2px 0 0;min-height:1px;
                                    background:<?= $esH ? '#25d366' : 'rgba(37,211,102,.35)' ?>;
                                    height:<?= max(1, $pWa) ?>%"></div>
                    </div>
                    <div style="font-size:.52rem;color:<?= $esH ? 'var(--color-primary)' : 'var(--color-text-muted)' ?>">
                        <?= $esH ? '·' : date('d', strtotime($dia['fecha'])) ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
    <?php endforeach; ?>
    </div>

    <?php endif; ?>

    <p class="text-center text-muted mt-4" style="font-size:.75rem">
        <i class="bi bi-info-circle me-1"></i>
        Las visitas propias y del administrador no se contabilizan.
        Los datos se actualizan en tiempo real.
    </p>
</div>
