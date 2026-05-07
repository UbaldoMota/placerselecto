<?php /** @var array $paquete */ /** @var bool $devMode */ /** @var bool $truevoEnabled */ /** @var bool $paycashEnabled */ ?>
<div class="container py-4" style="max-width:780px">

    <div class="d-flex flex-wrap gap-2 justify-content-end mb-3">
        <a href="<?= APP_URL ?>/tokens/comprar" class="btn btn-sm btn-secondary">
            <i class="bi bi-arrow-left me-1"></i>Volver a paquetes
        </a>
    </div>

    <div class="text-center mb-4">
        <h1 class="h4 fw-bold mb-1">
            <i class="bi bi-credit-card-2-front text-primary me-2"></i>Elige cómo pagar
        </h1>
        <p class="text-muted mb-0" style="font-size:.92rem">
            Estás comprando <strong><?= e($paquete['nombre']) ?></strong> por
            <strong>$<?= number_format((float)$paquete['monto_mxn'], 2) ?> MXN</strong>
            (<?= number_format((int)$paquete['tokens']) ?> tokens<?= (int)$paquete['bonus_pct'] > 0 ? ', +'.(int)$paquete['bonus_pct'].'% bonus' : '' ?>)
        </p>
    </div>

    <div class="d-flex flex-column gap-3">

        <?php if ($truevoEnabled || $devMode): ?>
        <!-- Truevo (tarjeta) -->
        <form method="POST" action="<?= APP_URL ?>/tokens/comprar/<?= (int)$paquete['id'] ?>/truevo" class="m-0">
            <?= $csrfField ?>
            <button type="submit" class="card w-100 text-start p-3 border-0 shadow-sm"
                    style="cursor:pointer;background:#fff;border-radius:12px;transition:transform .15s, box-shadow .15s"
                    onmouseover="this.style.transform='translateY(-2px)';this.style.boxShadow='0 6px 18px rgba(0,0,0,.08)'"
                    onmouseout="this.style.transform='';this.style.boxShadow=''">
                <div class="d-flex align-items-center gap-3">
                    <div style="font-size:2.2rem;color:#0a3d62;flex-shrink:0">
                        <i class="bi bi-credit-card-2-front-fill"></i>
                    </div>
                    <div class="flex-fill">
                        <div class="fw-bold" style="font-size:1.05rem">Pago con tarjeta de crédito/débito</div>
                        <div style="color:#1f9d55;font-size:.86rem;font-weight:500">La recarga es inmediata</div>
                        <div class="text-muted" style="font-size:.78rem;margin-top:2px">Procesado por Truevo · Visa · Mastercard</div>
                    </div>
                    <i class="bi bi-arrow-right text-muted"></i>
                </div>
            </button>
        </form>
        <?php endif; ?>

        <?php if ($paycashEnabled || $devMode): ?>
        <!-- PayCash (efectivo) -->
        <form method="POST" action="<?= APP_URL ?>/tokens/comprar/<?= (int)$paquete['id'] ?>/paycash" class="m-0">
            <?= $csrfField ?>
            <button type="submit" class="card w-100 text-start p-3 border-0 shadow-sm"
                    style="cursor:pointer;background:#fff;border-radius:12px;transition:transform .15s, box-shadow .15s"
                    onmouseover="this.style.transform='translateY(-2px)';this.style.boxShadow='0 6px 18px rgba(0,0,0,.08)'"
                    onmouseout="this.style.transform='';this.style.boxShadow=''">
                <div class="d-flex align-items-center gap-3">
                    <div style="font-size:2.2rem;color:#1f9d55;flex-shrink:0">
                        <i class="bi bi-cash-stack"></i>
                    </div>
                    <div class="flex-fill">
                        <div class="fw-bold" style="font-size:1.05rem">Pago en efectivo con PayCash</div>
                        <div style="color:#1f9d55;font-size:.86rem;font-weight:500">Acreditación inmediata</div>
                        <div class="text-muted" style="font-size:.78rem;margin-top:2px">Walmart · 7-Eleven · Soriana · Santander</div>
                        <div class="text-muted" style="font-size:.74rem;font-style:italic">Una vez recibido el pago, se activará automáticamente.</div>
                    </div>
                    <i class="bi bi-arrow-right text-muted"></i>
                </div>
            </button>
        </form>
        <?php endif; ?>

        <?php if (!$truevoEnabled && !$paycashEnabled && !$devMode): ?>
        <div class="alert alert-warning text-center">
            <i class="bi bi-exclamation-triangle me-1"></i>
            Las pasarelas de pago aún se están configurando. Por favor inténtalo más tarde.
        </div>
        <?php endif; ?>

        <?php if ($devMode): ?>
        <!-- Simulado (solo dev) -->
        <form method="POST" action="<?= APP_URL ?>/tokens/comprar/<?= (int)$paquete['id'] ?>" class="m-0">
            <?= $csrfField ?>
            <button type="submit" class="card w-100 text-start p-3 border-0 shadow-sm"
                    style="cursor:pointer;background:#fffbe6;border-radius:12px;border:1px dashed #d4a72c">
                <div class="d-flex align-items-center gap-3">
                    <div style="font-size:2rem;color:#d4a72c;flex-shrink:0">
                        <i class="bi bi-flask"></i>
                    </div>
                    <div class="flex-fill">
                        <div class="fw-bold" style="font-size:1rem">Pago simulado <span class="badge bg-warning text-dark">DEV</span></div>
                        <div class="text-muted" style="font-size:.78rem">Acredita tokens al instante sin pasar por pasarela. Solo para pruebas internas.</div>
                    </div>
                    <i class="bi bi-arrow-right text-muted"></i>
                </div>
            </button>
        </form>
        <?php endif; ?>

    </div>

    <div class="text-center text-muted mt-4" style="font-size:.78rem">
        <i class="bi bi-shield-lock me-1"></i>
        Tu información de pago se procesa de forma segura por la pasarela seleccionada. No almacenamos tus datos de tarjeta.
    </div>
</div>
