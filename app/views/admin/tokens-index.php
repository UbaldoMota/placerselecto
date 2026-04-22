<?php /** @var array $stats */ /** @var array $statsBoost */ /** @var array $paquetes */ /** @var array $tarifas */ ?>
<div class="container py-4">

    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h4 fw-bold mb-1">
                <i class="bi bi-coin text-primary me-2"></i>Sistema de tokens
            </h1>
            <p class="text-muted mb-0" style="font-size:.88rem">
                Monetización por tokens: configura paquetes de recarga, tarifas de consumo y revisa movimientos.
            </p>
        </div>
        <a href="<?= APP_URL ?>/admin" class="btn btn-sm btn-secondary">
            <i class="bi bi-arrow-left me-1"></i>Panel
        </a>
    </div>

    <!-- Tabs -->
    <ul class="nav nav-tabs mb-4" style="border-color:var(--color-border)">
        <li class="nav-item">
            <span class="nav-link active" style="background:var(--color-primary);color:#fff;border-color:var(--color-primary)">
                <i class="bi bi-speedometer2 me-1"></i>Resumen
            </span>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="<?= APP_URL ?>/admin/tokens/paquetes"
               style="color:var(--color-text)">
                <i class="bi bi-box-seam me-1"></i>Paquetes (<?= count($paquetes) ?>)
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="<?= APP_URL ?>/admin/tokens/tarifas"
               style="color:var(--color-text)">
                <i class="bi bi-tag me-1"></i>Tarifas
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="<?= APP_URL ?>/admin/tokens/movimientos"
               style="color:var(--color-text)">
                <i class="bi bi-list-ul me-1"></i>Movimientos
            </a>
        </li>
    </ul>

    <!-- Stats grid -->
    <div class="row g-3 mb-4">
        <?php
        $cards = [
            ['valor' => number_format((int)($stats['total_recargado']       ?? 0)), 'label' => 'Tokens recargados',   'icono' => 'arrow-up-circle',   'color' => 'success'],
            ['valor' => number_format((int)($stats['total_consumido']       ?? 0)), 'label' => 'Tokens consumidos',   'icono' => 'arrow-down-circle', 'color' => 'warning'],
            ['valor' => number_format((int)($stats['total_reembolsado']     ?? 0)), 'label' => 'Tokens reembolsados', 'icono' => 'arrow-counterclockwise', 'color' => 'info'],
            ['valor' => number_format((int)($stats['saldo_total_circulante']?? 0)), 'label' => 'Saldo circulante',    'icono' => 'wallet2',           'color' => 'primary'],
            ['valor' => number_format((int)($stats['usuarios_con_recarga']  ?? 0)), 'label' => 'Usuarios con recarga','icono' => 'people',            'color' => 'secondary'],
            ['valor' => number_format((int)($statsBoost['activos']          ?? 0)), 'label' => 'Boosts activos',      'icono' => 'lightning-charge',  'color' => 'primary'],
            ['valor' => number_format((int)($statsBoost['programados']      ?? 0)), 'label' => 'Boosts programados',  'icono' => 'calendar-event',    'color' => 'info'],
            ['valor' => number_format((int)($statsBoost['tokens_totales_consumidos'] ?? 0)), 'label' => 'Tokens usados en boosts', 'icono' => 'graph-up-arrow', 'color' => 'warning'],
        ];
        foreach ($cards as $c):
        ?>
        <div class="col-6 col-md-3">
            <div class="stat-card h-100">
                <div class="stat-card__value text-<?= e($c['color']) ?>"><?= e($c['valor']) ?></div>
                <div class="stat-card__label"><?= e($c['label']) ?></div>
                <i class="bi bi-<?= e($c['icono']) ?> stat-card__icon"></i>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Resumen rápido de configuración actual -->
    <div class="row g-3">
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <strong><i class="bi bi-box-seam me-1"></i>Paquetes de recarga</strong>
                    <a href="<?= APP_URL ?>/admin/tokens/paquetes" class="btn btn-sm btn-outline-primary">Gestionar</a>
                </div>
                <div class="card-body">
                    <?php if (empty($paquetes)): ?>
                        <p class="text-muted mb-0">No hay paquetes configurados.</p>
                    <?php else: ?>
                        <table class="table table-sm mb-0">
                            <thead>
                                <tr><th>Paquete</th><th class="text-end">Monto</th><th class="text-end">Tokens</th><th>Estado</th></tr>
                            </thead>
                            <tbody>
                            <?php foreach ($paquetes as $p): ?>
                                <tr>
                                    <td><?= e($p['nombre']) ?>
                                        <?php if ((int)$p['bonus_pct'] > 0): ?>
                                        <span class="badge-estado badge-destacado ms-1">+<?= (int)$p['bonus_pct'] ?>%</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end">$<?= number_format((float)$p['monto_mxn'], 2) ?></td>
                                    <td class="text-end"><strong><?= number_format((int)$p['tokens']) ?></strong></td>
                                    <td>
                                        <?php if ((int)$p['activo']): ?>
                                            <span class="badge-estado badge-publicado">Activo</span>
                                        <?php else: ?>
                                            <span class="badge-estado badge-expirado">Inactivo</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <strong><i class="bi bi-tag me-1"></i>Tarifas de consumo</strong>
                    <a href="<?= APP_URL ?>/admin/tokens/tarifas" class="btn btn-sm btn-outline-primary">Editar</a>
                </div>
                <div class="card-body">
                    <table class="table table-sm mb-0">
                        <thead>
                            <tr><th>Tipo</th><th class="text-end">Tokens/hora</th><th>Descripción</th></tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><i class="bi bi-arrow-up-square-fill text-primary me-1"></i><strong>Top</strong></td>
                                <td class="text-end"><strong class="text-primary"><?= (int)($tarifas['top']['tokens_por_hora'] ?? 0) ?></strong></td>
                                <td class="text-muted" style="font-size:.82rem"><?= e($tarifas['top']['descripcion'] ?? '') ?></td>
                            </tr>
                            <tr>
                                <td><i class="bi bi-stars text-warning me-1"></i><strong>Resaltado</strong></td>
                                <td class="text-end"><strong class="text-warning"><?= (int)($tarifas['resaltado']['tokens_por_hora'] ?? 0) ?></strong></td>
                                <td class="text-muted" style="font-size:.82rem"><?= e($tarifas['resaltado']['descripcion'] ?? '') ?></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

</div>
