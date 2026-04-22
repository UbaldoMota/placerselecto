<?php /** @var array $paquetes */ /** @var int $saldo */ ?>
<div class="container py-4" style="max-width:900px">

    <div class="d-flex flex-wrap gap-2 justify-content-end mb-3">
        <a href="<?= APP_URL ?>/mis-tokens" class="btn btn-sm btn-secondary">
            <i class="bi bi-coin me-1"></i>Mis tokens
        </a>
        <a href="<?= APP_URL ?>/dashboard" class="btn btn-sm btn-secondary">
            <i class="bi bi-house me-1"></i>Dashboard
        </a>
    </div>

    <div class="text-center mb-4">
        <h1 class="h4 fw-bold mb-1">
            <i class="bi bi-coin text-primary me-2"></i>Comprar tokens
        </h1>
        <p class="text-muted mb-0" style="font-size:.92rem">
            Los tokens se usan para destacar tus perfiles. Cuanto más grande el paquete, más bonus.
        </p>
    </div>

    <!-- Saldo actual -->
    <div class="card mb-4 text-center">
        <div class="card-body py-3">
            <div class="text-muted" style="font-size:.8rem;text-transform:uppercase;letter-spacing:.05em">Tu saldo actual</div>
            <div class="fw-bold text-primary mt-1" style="font-size:2rem">
                <i class="bi bi-coin"></i> <?= number_format($saldo) ?> <small class="text-muted" style="font-size:.9rem">tokens</small>
            </div>
            <a href="<?= APP_URL ?>/mis-tokens" class="small text-primary" style="text-decoration:none">
                Ver historial de movimientos →
            </a>
        </div>
    </div>

    <?php if (empty($paquetes)): ?>
        <div class="alert alert-info text-center">
            No hay paquetes disponibles en este momento. Intenta más tarde.
        </div>
    <?php else: ?>

    <div class="row g-3">
        <?php foreach ($paquetes as $p):
            $porToken = $p['tokens'] > 0 ? ((float)$p['monto_mxn'] / (int)$p['tokens']) : 0;
            $esPopular = (int)$p['bonus_pct'] >= 25;
        ?>
        <div class="col-12 col-md-6 col-lg-3">
            <div class="plan-card <?= $esPopular ? 'popular' : '' ?> h-100 d-flex flex-column">
                <?php if ($esPopular): ?>
                    <span class="plan-badge-popular">Mejor valor</span>
                <?php endif; ?>

                <div class="mb-2 fw-bold" style="font-size:1rem"><?= e($p['nombre']) ?></div>

                <div class="plan-price">$<?= number_format((float)$p['monto_mxn'], 0) ?></div>
                <div class="text-muted" style="font-size:.75rem">MXN</div>

                <div class="my-3 p-2 rounded" style="background:var(--color-bg-alt)">
                    <div class="text-muted" style="font-size:.72rem;text-transform:uppercase;letter-spacing:.05em">Recibes</div>
                    <div class="fw-bold text-primary" style="font-size:1.5rem">
                        <?= number_format((int)$p['tokens']) ?>
                        <small class="text-muted" style="font-size:.7rem">tokens</small>
                    </div>
                    <?php if ((int)$p['bonus_pct'] > 0): ?>
                        <span class="badge-estado badge-destacado">+<?= (int)$p['bonus_pct'] ?>% bonus</span>
                    <?php endif; ?>
                </div>

                <div class="text-muted mb-3" style="font-size:.78rem">
                    ≈ $<?= number_format($porToken, 3) ?> por token
                </div>

                <form method="POST" action="<?= APP_URL ?>/tokens/comprar/<?= (int)$p['id'] ?>" class="mt-auto m-0">
                    <?= $csrfField ?>
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-cart-check me-1"></i>Comprar
                    </button>
                </form>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <div class="mt-4 p-3 rounded text-center" style="background:var(--color-bg-alt);font-size:.85rem;color:var(--color-text-muted)">
        <i class="bi bi-shield-check text-primary me-1"></i>
        Pago seguro simulado (desarrollo). En producción se integrará con pasarela real.
    </div>
    <?php endif; ?>
</div>
