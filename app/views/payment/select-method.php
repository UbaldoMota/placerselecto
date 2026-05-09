<?php
/** @var array $paquete */
/** @var bool  $devMode */
/** @var bool  $truevoEnabled */
/** @var bool  $paycashEnabled */
/** @var string|null $whatsappPagos */
/** @var bool  $tienePerfilAprobado */

$tieneWA = !empty($whatsappPagos);
$puedeComprar = $tieneWA && !empty($tienePerfilAprobado);
?>
<div class="container py-4" style="max-width:780px">

    <div class="d-flex flex-wrap gap-2 justify-content-end mb-3">
        <a href="<?= APP_URL ?>/tokens/comprar" class="btn btn-sm btn-secondary">
            <i class="bi bi-arrow-left me-1"></i>Volver a paquetes
        </a>
    </div>

    <div class="text-center mb-4">
        <h1 class="h4 fw-bold mb-1">
            <i class="bi bi-credit-card-2-front text-primary me-2"></i>Pagar paquete
        </h1>
        <p class="text-muted mb-0" style="font-size:.92rem">
            Estás comprando <strong><?= e($paquete['nombre']) ?></strong> por
            <strong>$<?= number_format((float)$paquete['monto_mxn'], 2) ?> MXN</strong>
            (<?= number_format((int)$paquete['tokens']) ?> tokens)
        </p>
    </div>

    <!-- Métodos de pago disponibles (informativo, sin nombrar pasarela) -->
    <div class="card mb-3">
        <div class="card-body" style="padding:1rem 1.2rem">
            <div class="text-muted mb-2" style="font-size:.72rem;text-transform:uppercase;letter-spacing:.06em;font-weight:700">
                <i class="bi bi-shield-check text-primary me-1"></i>Métodos disponibles
            </div>
            <div class="row g-2" style="font-size:.85rem">
                <div class="col-12 col-sm-6 d-flex align-items-center gap-2">
                    <i class="bi bi-credit-card-2-front-fill" style="color:#0a3d62;font-size:1.1rem"></i>
                    Tarjeta de crédito o débito
                </div>
                <div class="col-12 col-sm-6 d-flex align-items-center gap-2">
                    <i class="bi bi-bank2" style="color:#1f9d55;font-size:1.1rem"></i>
                    Transferencia bancaria (SPEI)
                </div>
                <div class="col-12 col-sm-6 d-flex align-items-center gap-2">
                    <i class="bi bi-shop" style="color:#dc3545;font-size:1.1rem"></i>
                    Efectivo en OXXO, 7-Eleven y otras tiendas
                </div>
                <div class="col-12 col-sm-6 d-flex align-items-center gap-2">
                    <i class="bi bi-wallet2" style="color:#6f42c1;font-size:1.1rem"></i>
                    Billetera digital y otros métodos
                </div>
            </div>
        </div>
    </div>

    <!-- CTA principal -->
    <?php if (empty($tienePerfilAprobado)): ?>
    <div class="card" style="background:rgba(245,158,11,.08);border:1px solid rgba(245,158,11,.35)">
        <div class="card-body p-4 text-center">
            <i class="bi bi-person-badge" style="font-size:2.5rem;color:#F59E0B"></i>
            <h2 class="h6 fw-bold mt-3 mb-2">Primero necesitas un perfil publicado</h2>
            <p class="text-muted mb-3" style="font-size:.9rem;line-height:1.55">
                Para comprar tokens debes tener al menos <strong>un perfil creado y aceptado</strong> por nuestro equipo.
                Crea tu perfil, completa la verificación y espera la aprobación. En cuanto sea publicado podrás recargar tokens y destacarlo.
            </p>
            <div class="d-flex flex-wrap gap-2 justify-content-center">
                <a href="<?= APP_URL ?>/perfil/nuevo" class="btn btn-primary">
                    <i class="bi bi-plus-lg me-1"></i>Crear perfil
                </a>
                <a href="<?= APP_URL ?>/mis-perfiles" class="btn btn-secondary">
                    <i class="bi bi-person-lines-fill me-1"></i>Mis perfiles
                </a>
            </div>
        </div>
    </div>
    <?php elseif ($tieneWA): ?>
    <form method="POST" action="<?= APP_URL ?>/tokens/comprar/<?= (int)$paquete['id'] ?>/whatsapp" class="m-0">
        <?= $csrfField ?>
        <button type="submit" class="btn w-100 d-flex align-items-center justify-content-center gap-2"
                style="background:#25D366;color:#fff;font-weight:800;font-size:1.05rem;padding:.85rem 1rem;border-radius:14px;border:0;box-shadow:0 6px 18px rgba(37,211,102,.35);transition:transform .15s,box-shadow .15s"
                onmouseover="this.style.transform='translateY(-1px)';this.style.boxShadow='0 8px 24px rgba(37,211,102,.5)'"
                onmouseout="this.style.transform='';this.style.boxShadow='0 6px 18px rgba(37,211,102,.35)'">
            <i class="bi bi-whatsapp" style="font-size:1.35rem"></i>
            Pagar por WhatsApp
        </button>
    </form>

    <div class="card mt-3" style="background:rgba(13,202,240,.06);border:1px solid rgba(13,202,240,.25)">
        <div class="card-body" style="padding:.9rem 1.1rem;font-size:.85rem;line-height:1.55">
            <strong style="color:#0891B2;font-size:.95rem">
                <i class="bi bi-info-circle-fill me-1"></i>¿Cómo funciona?
            </strong>
            <ol class="mb-0 mt-2 ps-3" style="font-size:.84rem">
                <li>Te abrimos chat con nuestro equipo en WhatsApp con tu pedido pre-llenado.</li>
                <li>Recibirás el <strong>link de pago seguro</strong> con el método que prefieras (tarjeta, SPEI, efectivo, etc.).</li>
                <li>Realizas el pago.</li>
                <li>Cuando lo confirmamos, los <strong><?= number_format((int)$paquete['tokens']) ?> tokens</strong> se acreditan a tu saldo automáticamente.</li>
            </ol>
            <div class="text-muted mt-2" style="font-size:.78rem">
                <i class="bi bi-clock-history me-1"></i>
                Suele tardar entre 5 y 30 minutos en horario hábil.
            </div>
        </div>
    </div>
    <?php else: ?>
    <div class="alert alert-warning">
        <i class="bi bi-exclamation-triangle me-2"></i>
        El método de pago no está disponible en este momento. Intenta más tarde o escribe a soporte.
    </div>
    <?php endif; ?>

    <div class="mt-3 text-center text-muted" style="font-size:.78rem">
        <i class="bi bi-shield-lock me-1"></i>
        Cada cuenta paga su propio perfil. Si no se cubre el importe acordado, no se acreditarán tokens.
    </div>
</div>
