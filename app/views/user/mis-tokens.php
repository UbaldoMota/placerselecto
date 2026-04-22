<?php
/** @var int   $saldo */
/** @var array $historial */
/** @var array $pagination */
/** @var array $boostsActivos */
/** @var array $boostsProg */
?>
<div class="container py-4" style="max-width:900px">

    <div class="d-flex flex-column flex-sm-row align-items-start align-items-sm-center justify-content-between gap-2 mb-4">
        <h1 class="h4 fw-bold mb-0">
            <i class="bi bi-coin text-primary me-2"></i>Mis tokens
        </h1>
        <div class="d-flex flex-wrap gap-2">
            <a href="<?= APP_URL ?>/mis-perfiles" class="btn btn-sm btn-secondary">
                <i class="bi bi-person-lines-fill me-1"></i>Mis perfiles
            </a>
            <a href="<?= APP_URL ?>/dashboard" class="btn btn-sm btn-secondary">
                <i class="bi bi-house me-1"></i>Dashboard
            </a>
            <a href="<?= APP_URL ?>/tokens/comprar" class="btn btn-sm btn-primary">
                <i class="bi bi-plus-lg me-1"></i>Comprar más
            </a>
        </div>
    </div>

    <!-- Saldo -->
    <div class="card mb-4 text-center">
        <div class="card-body py-4">
            <div class="text-muted" style="font-size:.8rem;text-transform:uppercase;letter-spacing:.05em">Saldo disponible</div>
            <div class="fw-bold text-primary mt-1" style="font-size:2.5rem;line-height:1">
                <i class="bi bi-coin"></i> <?= number_format($saldo) ?>
            </div>
            <div class="text-muted" style="font-size:.82rem">tokens</div>
        </div>
    </div>

    <!-- Boosts activos y programados -->
    <?php if (!empty($boostsActivos) || !empty($boostsProg)): ?>
    <div class="card mb-4">
        <div class="card-header">
            <strong><i class="bi bi-lightning-charge-fill text-primary me-1"></i>Boosts en curso</strong>
        </div>
        <div class="card-body">
            <?php
            $merged = array_merge(
                array_map(fn($b) => $b + ['_grupo' => 'Activos'],       $boostsActivos),
                array_map(fn($b) => $b + ['_grupo' => 'Programados'],   $boostsProg),
            );
            foreach ($merged as $b):
                $esTop    = $b['tipo'] === 'top';
                $ahora    = time();
                $finTs    = strtotime($b['fin']);
                $horasRest= max(0, round(($finTs - $ahora) / 3600, 1));
            ?>
            <div class="d-flex align-items-center justify-content-between py-2 border-bottom" style="border-color:var(--color-border) !important">
                <div class="d-flex align-items-center gap-3">
                    <div style="width:38px;height:38px;border-radius:50%;background:<?= $esTop ? 'rgba(255,45,117,.12)' : 'rgba(245,158,11,.12)' ?>;display:flex;align-items:center;justify-content:center;color:<?= $esTop ? 'var(--color-primary)' : 'var(--color-warning)' ?>;font-size:1.1rem">
                        <i class="bi bi-<?= $esTop ? 'arrow-up-square-fill' : 'stars' ?>"></i>
                    </div>
                    <div>
                        <strong><?= e($b['perfil_nombre']) ?></strong>
                        <span class="badge-estado <?= $esTop ? 'badge-destacado' : 'badge-pendiente' ?> ms-1"><?= e($b['tipo']) ?></span>
                        <div class="text-muted" style="font-size:.78rem">
                            <?= e(substr($b['inicio'], 0, 16)) ?> → <?= e(substr($b['fin'], 0, 16)) ?>
                            <?php if ($b['_grupo'] === 'Activos'): ?>
                                · <span class="text-primary"><?= $horasRest ?>h restantes</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <div class="d-flex gap-2 align-items-center">
                    <span class="text-muted" style="font-size:.85rem"><i class="bi bi-coin"></i> <?= number_format((int)$b['tokens_gastados']) ?></span>
                    <?php if ($b['_grupo'] === 'Programados'): ?>
                    <form method="POST" action="<?= APP_URL ?>/perfil/boost/<?= (int)$b['id'] ?>/cancelar" class="m-0"
                          data-confirm-submit="¿Cancelar y recuperar <?= (int)$b['tokens_gastados'] ?> tokens?">
                        <?= $csrfField ?>
                        <button type="submit" class="btn btn-sm btn-secondary" title="Cancelar y reembolsar">
                            <i class="bi bi-x-circle"></i> Cancelar
                        </button>
                    </form>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Historial de movimientos -->
    <div class="card">
        <div class="card-header">
            <strong><i class="bi bi-list-ul me-1"></i>Historial de movimientos</strong>
        </div>
        <?php if (empty($historial)): ?>
            <div class="card-body text-center text-muted py-4">
                Aún no tienes movimientos. Haz tu primera recarga para empezar.
            </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table mb-0">
                <thead>
                    <tr>
                        <th>Fecha</th><th>Tipo</th><th>Descripción</th>
                        <th class="text-end">Cambio</th><th class="text-end">Saldo</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($historial as $m):
                        $cant = (int)$m['cantidad'];
                        $sig  = $cant >= 0 ? '+' : '';
                        $cls  = $cant >= 0 ? 'success' : 'danger';
                        $tipoMap = [
                            'recarga' => ['Recarga', 'publicado'],
                            'consumo' => ['Consumo', 'rechazado'],
                            'reembolso' => ['Reembolso', 'pendiente'],
                            'ajuste_admin' => ['Ajuste admin', 'destacado'],
                        ];
                        [$tLabel, $tBadge] = $tipoMap[$m['tipo']] ?? [$m['tipo'], 'expirado'];
                    ?>
                    <tr>
                        <td style="font-size:.82rem;white-space:nowrap"><?= e(substr($m['fecha'], 0, 16)) ?></td>
                        <td><span class="badge-estado badge-<?= e($tBadge) ?>"><?= e($tLabel) ?></span></td>
                        <td class="text-muted" style="font-size:.85rem"><?= e($m['descripcion']) ?></td>
                        <td class="text-end text-<?= $cls ?>"><strong><?= e($sig) . number_format($cant) ?></strong></td>
                        <td class="text-end"><?= number_format((int)$m['saldo_despues']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>

    <?php if (($pagination['pages'] ?? 1) > 1): ?>
    <nav class="mt-3 d-flex justify-content-center">
        <ul class="pagination pagination-sm mb-0">
            <?php for ($p = 1; $p <= $pagination['pages']; $p++): ?>
            <li class="page-item <?= $p === $pagination['current'] ? 'active' : '' ?>">
                <a class="page-link" href="?page=<?= $p ?>"><?= $p ?></a>
            </li>
            <?php endfor; ?>
        </ul>
    </nav>
    <?php endif; ?>

</div>
