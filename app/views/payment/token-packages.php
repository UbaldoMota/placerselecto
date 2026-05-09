<?php /** @var array $paquetes */ /** @var int $saldo */ ?>
<div class="container py-4" style="max-width:1100px">

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
            Los tokens te permiten <strong>destacar tu perfil cuando tú quieras</strong>. Sin mensualidades, pagas solo por las horas que uses.
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

    <!-- Comparativa rapida con la competencia -->
    <div class="alert d-flex align-items-center gap-2 mb-3 py-2 px-3"
         style="background:rgba(16,185,129,.06);border:1px solid rgba(16,185,129,.2);font-size:.85rem;color:#065F46">
        <i class="bi bi-piggy-bank-fill" style="font-size:1.1rem"></i>
        <span>
            <strong>Hasta 33% más barato</strong> que otras plataformas, con
            <strong>+25% más tokens</strong> en cada paquete y procesamiento de pagos en tiempo real.
        </span>
    </div>

    <div class="row g-3">
        <?php foreach ($paquetes as $p):
            $porToken    = $p['tokens'] > 0 ? ((float)$p['monto_mxn'] / (int)$p['tokens']) : 0;
            $esDestacado = (int)($p['destacado'] ?? 0) === 1;
        ?>
        <div class="col-12 col-md-6 col-lg">
            <div class="plan-card h-100 d-flex flex-column position-relative <?= $esDestacado ? 'plan-card--popular' : '' ?>"
                 style="<?= $esDestacado
                    ? 'border:2px solid var(--color-primary);box-shadow:0 8px 24px rgba(255,45,117,.18);transform:translateY(-4px);background:linear-gradient(180deg,#FFF5F8 0%,#FFFFFF 30%)'
                    : 'border:1px solid var(--color-border)' ?>;border-radius:var(--radius-lg);padding:1.25rem;background-color:#fff">

                <?php if ($esDestacado): ?>
                <span style="position:absolute;top:-12px;left:50%;transform:translateX(-50%);background:var(--color-primary);color:#fff;font-size:.7rem;font-weight:800;letter-spacing:.05em;padding:.35rem .9rem;border-radius:20px;box-shadow:0 4px 10px rgba(255,45,117,.4);white-space:nowrap">
                    <i class="bi bi-star-fill me-1"></i>MÁS POPULAR
                </span>
                <?php endif; ?>

                <div class="text-center mb-2 fw-bold mt-2" style="font-size:1.05rem;color:<?= $esDestacado ? 'var(--color-primary)' : 'inherit' ?>">
                    <?= e($p['nombre']) ?>
                </div>

                <div class="text-center fw-black" style="font-size:2rem;line-height:1;color:#1A1A1A">
                    $<?= number_format((float)$p['monto_mxn'], 0) ?>
                    <small class="fw-semibold text-muted ms-1" style="font-size:.7rem">MXN</small>
                </div>

                <div class="my-3 p-2 rounded text-center" style="background:rgba(255,45,117,.06);border:1px solid rgba(255,45,117,.12)">
                    <div class="text-muted" style="font-size:.7rem;text-transform:uppercase;letter-spacing:.05em">Recibes</div>
                    <div class="fw-bold text-primary" style="font-size:1.6rem;line-height:1.1">
                        <?= number_format((int)$p['tokens']) ?>
                        <small class="text-muted fw-semibold" style="font-size:.7rem">tokens</small>
                    </div>
                </div>

                <div class="text-center text-muted mb-3" style="font-size:.78rem">
                    ≈ $<?= number_format($porToken, 2) ?> por token
                </div>

                <a href="<?= APP_URL ?>/tokens/comprar/<?= (int)$p['id'] ?>/metodo"
                   class="btn <?= $esDestacado ? 'btn-primary' : 'btn-secondary' ?> w-100 mt-auto"
                   style="font-weight:700">
                    <i class="bi bi-cart-check me-1"></i>Comprar
                </a>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- ¿Para que sirven los tokens? -->
    <div class="card mt-4">
        <div class="card-header">
            <span class="fw-semibold">
                <i class="bi bi-lightbulb-fill text-primary me-2"></i>¿Para qué sirven los tokens?
            </span>
        </div>
        <div class="card-body">
            <div class="row g-3">

                <div class="col-12 col-md-6">
                    <div class="d-flex align-items-start gap-2 mb-3">
                        <div style="width:40px;height:40px;border-radius:8px;background:rgba(255,45,117,.12);color:var(--color-primary);display:flex;align-items:center;justify-content:center;flex-shrink:0">
                            <i class="bi bi-arrow-up-square-fill" style="font-size:1.2rem"></i>
                        </div>
                        <div>
                            <div class="fw-semibold" style="font-size:.92rem">Boost TOP — sales primero</div>
                            <div class="text-muted" style="font-size:.78rem;line-height:1.4">
                                Tu perfil aparece en la <strong>cabecera de tu municipio</strong>, rotando con otras TOPs cada minuto para que todas tengan tiempo en #1. Más visibilidad = más contactos.
                            </div>
                        </div>
                    </div>

                    <div class="d-flex align-items-start gap-2">
                        <div style="width:40px;height:40px;border-radius:8px;background:rgba(245,158,11,.12);color:#F59E0B;display:flex;align-items:center;justify-content:center;flex-shrink:0">
                            <i class="bi bi-stars" style="font-size:1.2rem"></i>
                        </div>
                        <div>
                            <div class="fw-semibold" style="font-size:.92rem">Boost RESALTADO — destaca visualmente</div>
                            <div class="text-muted" style="font-size:.78rem;line-height:1.4">
                                Un <strong>fondo amarillo distintivo</strong> hace que tu tarjeta llame la atención entre las demás del listado. No altera tu posición.
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-md-6">
                    <div class="p-3 rounded h-100" style="background:rgba(255,45,117,.04);border:1px solid rgba(255,45,117,.12)">
                        <div class="fw-semibold mb-2" style="font-size:.92rem">
                            <i class="bi bi-clock-history text-primary me-1"></i>Pagas solo por las horas que tú elijas
                        </div>
                        <div class="text-muted" style="font-size:.82rem;line-height:1.5">
                            No hay mensualidades. Activas el boost solo en tus mejores horarios — vie/sáb/dom de 8pm a 4am, por ejemplo. El resto del tiempo no consumes nada.
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <div class="mt-4 p-3 rounded text-center" style="background:var(--color-bg-alt);font-size:.85rem;color:var(--color-text-muted)">
        <i class="bi bi-shield-check text-primary me-1"></i>
        Pago seguro vía Truevo (tarjeta) o PayCash (efectivo: Walmart, 7-Eleven, Soriana, Santander).
        Si compras y no usas en 48h, te devolvemos completo.
    </div>
    <?php endif; ?>
</div>
