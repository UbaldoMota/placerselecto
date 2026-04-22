<?php /** @var array $pago */ /** @var ?array $paquete */ /** @var int $saldo */ ?>
<div class="container py-5" style="max-width:560px">
    <div class="card text-center">
        <div class="card-body py-5">
            <div class="mx-auto mb-3" style="width:72px;height:72px;border-radius:50%;background:rgba(16,185,129,.12);border:2px solid rgba(16,185,129,.3);display:flex;align-items:center;justify-content:center;color:var(--color-success);font-size:2rem">
                <i class="bi bi-check-lg"></i>
            </div>
            <h1 class="h4 fw-bold mb-2">¡Compra exitosa!</h1>
            <p class="text-muted mb-4">
                Se acreditaron <strong class="text-primary"><?= number_format((int)($pago['tokens_otorgados'] ?? 0)) ?> tokens</strong>
                a tu cuenta.
            </p>

            <div class="p-3 rounded mb-4" style="background:var(--color-bg-alt)">
                <dl class="row mb-0" style="font-size:.88rem;text-align:left;row-gap:.3rem">
                    <?php if ($paquete): ?>
                    <dt class="col-5 text-muted">Paquete</dt>
                    <dd class="col-7 mb-0"><?= e($paquete['nombre']) ?></dd>
                    <?php endif; ?>

                    <dt class="col-5 text-muted">Monto</dt>
                    <dd class="col-7 mb-0">$<?= number_format((float)$pago['monto'], 2) ?> <?= e($pago['moneda']) ?></dd>

                    <dt class="col-5 text-muted">Referencia</dt>
                    <dd class="col-7 mb-0" style="font-family:monospace;font-size:.82rem"><?= e($pago['referencia_ext']) ?></dd>

                    <dt class="col-5 text-muted">Fecha</dt>
                    <dd class="col-7 mb-0"><?= e($pago['fecha_pago']) ?></dd>

                    <dt class="col-5 text-muted">Nuevo saldo</dt>
                    <dd class="col-7 mb-0">
                        <strong class="text-primary"><i class="bi bi-coin"></i> <?= number_format($saldo) ?></strong>
                    </dd>
                </dl>
            </div>

            <div class="d-flex gap-2 justify-content-center">
                <a href="<?= APP_URL ?>/mis-perfiles" class="btn btn-primary">
                    <i class="bi bi-stars me-1"></i>Destacar un perfil
                </a>
                <a href="<?= APP_URL ?>/mis-tokens" class="btn btn-secondary">
                    <i class="bi bi-list-ul me-1"></i>Ver historial
                </a>
            </div>
        </div>
    </div>
</div>
