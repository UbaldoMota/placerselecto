<?php /** @var array $items */ /** @var array $pagination */ /** @var array $filtros */ ?>
<div class="container py-4">

    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h4 fw-bold mb-1"><i class="bi bi-list-ul text-primary me-2"></i>Movimientos de tokens</h1>
            <p class="text-muted mb-0" style="font-size:.88rem">
                Ledger de todos los cambios de saldo — recargas, consumos, reembolsos y ajustes.
            </p>
        </div>
        <a href="<?= APP_URL ?>/admin/tokens" class="btn btn-sm btn-secondary">
            <i class="bi bi-arrow-left me-1"></i>Resumen
        </a>
    </div>

    <!-- Filtros -->
    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-md-3">
                    <label class="form-label">Tipo</label>
                    <select name="tipo" class="form-select form-select-sm">
                        <option value="">Todos</option>
                        <?php foreach (['recarga','consumo','reembolso','ajuste_admin'] as $t): ?>
                            <option value="<?= $t ?>" <?= ($filtros['tipo'] ?? '') === $t ? 'selected' : '' ?>><?= ucfirst(str_replace('_',' ', $t)) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-5">
                    <label class="form-label">Email del usuario</label>
                    <input type="text" name="email" class="form-control form-control-sm"
                           value="<?= e($filtros['email'] ?? '') ?>" placeholder="parte del email">
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-primary btn-sm me-1">Filtrar</button>
                    <a href="<?= APP_URL ?>/admin/tokens/movimientos" class="btn btn-secondary btn-sm">Limpiar</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Tabla -->
    <div class="card">
        <div class="table-responsive">
            <table class="table mb-0">
                <thead>
                    <tr>
                        <th>Fecha</th><th>Usuario</th><th>Tipo</th>
                        <th class="text-end">Cantidad</th><th class="text-end">Saldo tras</th>
                        <th>Descripción</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($items)): ?>
                    <tr><td colspan="6" class="text-center text-muted py-4">Sin movimientos.</td></tr>
                    <?php endif; ?>
                    <?php foreach ($items as $m):
                        $cant = (int)$m['cantidad'];
                        $sig  = $cant >= 0 ? '+' : '';
                        $cls  = $cant >= 0 ? 'success' : 'danger';
                        $tipoBadgeMap = [
                            'recarga'       => ['Recarga',   'publicado'],
                            'consumo'       => ['Consumo',   'rechazado'],
                            'reembolso'     => ['Reembolso', 'pendiente'],
                            'ajuste_admin'  => ['Ajuste',    'destacado'],
                        ];
                        [$tipoLabel, $tipoBadge] = $tipoBadgeMap[$m['tipo']] ?? [$m['tipo'], 'expirado'];
                    ?>
                    <tr>
                        <td style="white-space:nowrap;font-size:.8rem"><?= e($m['fecha']) ?></td>
                        <td>
                            <?php if (!empty($m['id_usuario'])): ?>
                                <a href="<?= APP_URL ?>/admin/usuario/<?= (int)$m['id_usuario'] ?>"
                                   style="color:var(--color-text)">
                                    <strong><?= e($m['usuario_nombre'] ?? '#' . (int)$m['id_usuario']) ?></strong>
                                </a>
                                <div class="text-muted" style="font-size:.72rem"><?= e($m['usuario_email'] ?? '') ?></div>
                            <?php else: ?>
                                <span class="text-muted">—</span>
                            <?php endif; ?>
                        </td>
                        <td><span class="badge-estado badge-<?= e($tipoBadge) ?>"><?= e($tipoLabel) ?></span></td>
                        <td class="text-end text-<?= $cls ?>"><strong><?= e($sig) . number_format($cant) ?></strong></td>
                        <td class="text-end"><?= number_format((int)$m['saldo_despues']) ?></td>
                        <td class="text-muted" style="font-size:.82rem"><?= e($m['descripcion'] ?? '') ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php if (($pagination['pages'] ?? 1) > 1): ?>
    <nav class="mt-3 d-flex justify-content-center">
        <ul class="pagination pagination-sm mb-0">
            <?php
            $qs = http_build_query($filtros);
            for ($p = 1; $p <= $pagination['pages']; $p++):
            ?>
            <li class="page-item <?= $p === $pagination['current'] ? 'active' : '' ?>">
                <a class="page-link" href="?page=<?= $p ?>&<?= $qs ?>"><?= $p ?></a>
            </li>
            <?php endfor; ?>
        </ul>
    </nav>
    <?php endif; ?>

</div>
