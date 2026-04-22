<?php
/** @var array $perfil */
/** @var int   $saldo */
/** @var array $tarifas */
/** @var array $boosts */
/** @var int   $minHoras */
/** @var int   $maxHoras */

$tphTop  = (int)($tarifas['top']['tokens_por_hora']       ?? 0);
$tphRes  = (int)($tarifas['resaltado']['tokens_por_hora'] ?? 0);
$descTop = (string)($tarifas['top']['descripcion']        ?? '');
$descRes = (string)($tarifas['resaltado']['descripcion']  ?? '');
?>
<div class="container py-4" style="max-width:860px">

    <div class="d-flex flex-wrap gap-2 mb-3">
        <a href="<?= APP_URL ?>/mis-perfiles" class="btn btn-sm btn-secondary">
            <i class="bi bi-person-lines-fill me-1"></i>Mis perfiles
        </a>
        <a href="<?= APP_URL ?>/dashboard" class="btn btn-sm btn-secondary">
            <i class="bi bi-house me-1"></i>Dashboard
        </a>
    </div>

    <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
        <div>
            <h1 class="h4 fw-bold mb-1">
                <i class="bi bi-stars text-primary me-2"></i>Destacar perfil
            </h1>
            <p class="text-muted mb-0" style="font-size:.9rem">
                Perfil: <strong><?= e($perfil['nombre']) ?></strong>
            </p>
        </div>
        <div class="d-flex gap-2 align-items-center">
            <span class="text-muted" style="font-size:.85rem">Tu saldo:</span>
            <strong class="text-primary" style="font-size:1.1rem">
                <i class="bi bi-coin"></i> <span id="saldo-actual"><?= number_format($saldo) ?></span>
            </strong>
            <a href="<?= APP_URL ?>/tokens/comprar" class="btn btn-sm btn-outline-primary">
                <i class="bi bi-plus-lg"></i> Comprar
            </a>
        </div>
    </div>

    <form method="POST" action="<?= APP_URL ?>/perfil/<?= (int)$perfil['id'] ?>/destacar" id="boost-form">
        <?= $csrfField ?>

        <!-- Tipo -->
        <div class="mb-3">
            <label class="form-label">Tipo de boost</label>
            <div class="row g-2">
                <div class="col-md-6">
                    <label class="plan-card h-100 d-block" style="cursor:pointer">
                        <input type="radio" name="tipo" value="top" class="d-none" data-tph="<?= $tphTop ?>" checked>
                        <div class="d-flex align-items-center gap-2 justify-content-center mb-2">
                            <i class="bi bi-arrow-up-square-fill text-primary" style="font-size:1.5rem"></i>
                            <strong>Top municipio</strong>
                        </div>
                        <div class="plan-price" style="font-size:1.5rem"><?= $tphTop ?></div>
                        <div class="text-muted" style="font-size:.75rem">tokens / hora</div>
                        <div class="mt-2 text-muted" style="font-size:.78rem;line-height:1.4"><?= e($descTop) ?></div>
                    </label>
                </div>
                <div class="col-md-6">
                    <label class="plan-card h-100 d-block" style="cursor:pointer">
                        <input type="radio" name="tipo" value="resaltado" class="d-none" data-tph="<?= $tphRes ?>">
                        <div class="d-flex align-items-center gap-2 justify-content-center mb-2">
                            <i class="bi bi-stars text-warning" style="font-size:1.5rem"></i>
                            <strong>Resaltado visual</strong>
                        </div>
                        <div class="plan-price" style="font-size:1.5rem;color:var(--color-warning)"><?= $tphRes ?></div>
                        <div class="text-muted" style="font-size:.75rem">tokens / hora</div>
                        <div class="mt-2 text-muted" style="font-size:.78rem;line-height:1.4"><?= e($descRes) ?></div>
                    </label>
                </div>
            </div>
        </div>

        <!-- Duración -->
        <div class="mb-3">
            <label class="form-label">Duración (horas)</label>
            <div class="d-flex gap-2 flex-wrap mb-2">
                <?php foreach ([1, 3, 6, 12, 24, 48, 72, 168] as $h): ?>
                <button type="button" class="btn btn-sm btn-secondary boost-h-preset" data-h="<?= $h ?>">
                    <?= $h < 24 ? $h . 'h' : ($h === 168 ? '7 días' : ($h / 24) . ' días') ?>
                </button>
                <?php endforeach; ?>
            </div>
            <input type="number" name="horas" id="boost-horas" class="form-control"
                   min="<?= $minHoras ?>" max="<?= $maxHoras ?>" value="24" required>
            <small class="text-muted" style="font-size:.78rem">Entre <?= $minHoras ?> y <?= $maxHoras ?> horas.</small>
        </div>

        <!-- Inicio -->
        <div class="mb-3">
            <label class="form-label">Inicio</label>
            <div class="d-flex gap-3 align-items-center mb-2">
                <label class="form-check">
                    <input type="radio" name="modo_inicio" value="ahora" class="form-check-input" checked>
                    <span class="form-check-label">Ahora</span>
                </label>
                <label class="form-check">
                    <input type="radio" name="modo_inicio" value="programado" class="form-check-input">
                    <span class="form-check-label">Programado</span>
                </label>
            </div>
            <input type="datetime-local" name="inicio" id="boost-inicio" class="form-control" disabled
                   min="<?= date('Y-m-d\TH:i') ?>">
        </div>

        <!-- Resumen / costo -->
        <div class="card mb-3">
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-4">
                        <div class="text-muted" style="font-size:.72rem;text-transform:uppercase">Tarifa</div>
                        <div class="fw-bold" id="res-tarifa"><?= $tphTop ?> t/h</div>
                    </div>
                    <div class="col-4">
                        <div class="text-muted" style="font-size:.72rem;text-transform:uppercase">Duración</div>
                        <div class="fw-bold" id="res-horas">24 h</div>
                    </div>
                    <div class="col-4">
                        <div class="text-muted" style="font-size:.72rem;text-transform:uppercase">Costo total</div>
                        <div class="fw-bold text-primary" style="font-size:1.3rem">
                            <i class="bi bi-coin"></i> <span id="res-costo"><?= $tphTop * 24 ?></span>
                        </div>
                    </div>
                </div>
                <div id="saldo-warning" class="alert alert-warning mt-3 mb-0 d-none py-2" style="font-size:.85rem">
                    <i class="bi bi-exclamation-triangle me-1"></i>
                    Saldo insuficiente. Te faltan <strong id="faltan">0</strong> tokens.
                </div>
            </div>
        </div>

        <button type="submit" class="btn btn-primary w-100 btn-lg" id="boost-submit">
            <i class="bi bi-check-circle me-1"></i>Confirmar y destacar
        </button>
    </form>

    <!-- Historial de boosts del perfil -->
    <?php if (!empty($boosts)): ?>
    <div class="card mt-4">
        <div class="card-header">
            <strong><i class="bi bi-clock-history me-1"></i>Historial de boosts de este perfil</strong>
        </div>
        <div class="table-responsive">
            <table class="table mb-0">
                <thead>
                    <tr>
                        <th>Tipo</th><th>Inicio</th><th>Fin</th><th>Tokens</th><th>Estado</th><th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($boosts as $b):
                        $estadoBadge = [
                            'programado' => 'pendiente',
                            'activo'     => 'publicado',
                            'finalizado' => 'expirado',
                            'cancelado'  => 'rechazado',
                        ][$b['estado']] ?? 'expirado';
                    ?>
                    <tr>
                        <td>
                            <?php if ($b['tipo'] === 'top'): ?>
                                <i class="bi bi-arrow-up-square-fill text-primary me-1"></i>Top
                            <?php else: ?>
                                <i class="bi bi-stars text-warning me-1"></i>Resaltado
                            <?php endif; ?>
                            <?php if ((int)$b['es_legacy']): ?><span class="badge-estado badge-expirado ms-1">Legacy</span><?php endif; ?>
                        </td>
                        <td style="font-size:.82rem"><?= e(substr($b['inicio'], 0, 16)) ?></td>
                        <td style="font-size:.82rem"><?= e(substr($b['fin'], 0, 16)) ?></td>
                        <td><i class="bi bi-coin text-primary"></i> <?= number_format((int)$b['tokens_gastados']) ?></td>
                        <td><span class="badge-estado badge-<?= e($estadoBadge) ?>"><?= e($b['estado']) ?></span></td>
                        <td class="text-end">
                            <?php if ($b['estado'] === 'programado'): ?>
                            <form method="POST" action="<?= APP_URL ?>/perfil/boost/<?= (int)$b['id'] ?>/cancelar" class="m-0"
                                  data-confirm-submit="¿Cancelar y recuperar <?= (int)$b['tokens_gastados'] ?> tokens?">
                                <?= $csrfField ?>
                                <button type="submit" class="btn btn-sm btn-secondary">
                                    <i class="bi bi-x"></i> Cancelar
                                </button>
                            </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>

</div>

<div id="boost-config" data-saldo="<?= (int)$saldo ?>" style="display:none"></div>
<script src="<?= APP_URL ?>/public/assets/js/boost-create.js" defer></script>
