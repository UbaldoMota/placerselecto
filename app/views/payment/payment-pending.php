<?php /** @var array $pago */ /** @var ?array $paquete */ /** @var bool $devMode */
$esPayCash = ($pago['metodo_pago'] ?? '') === 'paycash';
$esTruevo  = ($pago['metodo_pago'] ?? '') === 'truevo';
$ref       = $pago['referencia_pasarela'] ?? '';
$expira    = $pago['expira_en'] ?? null;
?>
<div class="container py-4" style="max-width:680px">

    <div class="text-center mb-4">
        <?php if ($esPayCash): ?>
            <i class="bi bi-cash-stack text-primary" style="font-size:3rem"></i>
            <h1 class="h4 fw-bold mt-2 mb-1">Paga en efectivo en tu tienda</h1>
            <p class="text-muted" style="font-size:.92rem">
                Lleva esta referencia a cualquier <strong>Walmart, 7-Eleven, Soriana o Santander</strong> y paga en caja.
                Al recibir el pago activaremos tus tokens automáticamente.
            </p>
        <?php else: ?>
            <i class="bi bi-hourglass-split text-primary" style="font-size:3rem"></i>
            <h1 class="h4 fw-bold mt-2 mb-1">Procesando tu pago…</h1>
            <p class="text-muted" style="font-size:.92rem">
                Estamos confirmando la transacción con la pasarela. Esto suele tomar unos segundos.
            </p>
        <?php endif; ?>
    </div>

    <!-- Resumen del pedido -->
    <div class="card mb-3">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-start mb-2">
                <div>
                    <div class="text-muted" style="font-size:.74rem;text-transform:uppercase;letter-spacing:.05em">Pedido</div>
                    <div class="fw-semibold"><?= e($paquete['nombre'] ?? 'Paquete de tokens') ?></div>
                </div>
                <div class="text-end">
                    <div class="text-muted" style="font-size:.74rem;text-transform:uppercase;letter-spacing:.05em">Total</div>
                    <div class="fw-bold text-primary" style="font-size:1.4rem">$<?= number_format((float)$pago['monto'], 2) ?> MXN</div>
                </div>
            </div>
            <?php if (!empty($pago['tokens_otorgados'])): ?>
            <div class="text-muted" style="font-size:.82rem">
                Recibirás <strong><?= number_format((int)$pago['tokens_otorgados']) ?> tokens</strong> al confirmarse el pago.
            </div>
            <?php endif; ?>
        </div>
    </div>

    <?php if ($esPayCash && $ref !== ''): ?>
    <!-- Código de barras y referencia -->
    <div class="card mb-3 text-center">
        <div class="card-body py-4">
            <div class="text-muted mb-2" style="font-size:.74rem;text-transform:uppercase;letter-spacing:.05em">Tu referencia</div>

            <!-- Barcode visual placeholder -->
            <div style="font-family:'Libre Barcode 39',monospace;letter-spacing:2px;font-size:1rem;line-height:1;
                        background:repeating-linear-gradient(90deg,#000 0 2px,transparent 2px 4px,#000 4px 5px,transparent 5px 8px);
                        height:80px;margin:1rem auto;max-width:380px;border-radius:4px"></div>

            <div class="fw-bold" style="font-size:1.6rem;letter-spacing:3px;font-family:'Courier New',monospace">
                <?= e(chunk_split($ref, 4, ' ')) ?>
            </div>
            <button type="button" class="btn btn-sm btn-secondary mt-2"
                    onclick="navigator.clipboard.writeText('<?= e($ref) ?>').then(()=>this.innerText='✓ Copiado')">
                <i class="bi bi-clipboard me-1"></i>Copiar referencia
            </button>

            <?php if ($expira): ?>
            <div class="text-muted mt-3" style="font-size:.84rem">
                <i class="bi bi-clock"></i> Válido hasta el <?= date('d/m/Y H:i', strtotime($expira)) ?>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Pasos -->
    <div class="card mb-3">
        <div class="card-body">
            <ol style="font-size:.9rem;line-height:1.7" class="mb-0">
                <li>Ve a la caja de cualquier Walmart, 7-Eleven, Soriana o Santander.</li>
                <li>Indica que vas a hacer un pago de servicio "PayCash" con la referencia de arriba.</li>
                <li>Paga en efectivo el monto exacto: <strong>$<?= number_format((float)$pago['monto'], 2) ?> MXN</strong>.</li>
                <li>En cuanto recibamos la confirmación, los tokens se acreditarán a tu cuenta automáticamente.</li>
            </ol>
        </div>
    </div>
    <?php endif; ?>

    <?php if ($esTruevo): ?>
    <div class="alert alert-info" style="font-size:.88rem">
        <i class="bi bi-info-circle me-1"></i>
        En cuanto la pasarela confirme tu pago, esta página se actualizará y verás tus tokens en la cuenta.
    </div>
    <?php endif; ?>

    <?php if ($devMode): ?>
    <div class="card mb-3" style="border:1px dashed #d4a72c;background:#fffbe6">
        <div class="card-body">
            <div class="fw-semibold mb-2" style="color:#8a6d00">
                <i class="bi bi-flask me-1"></i>Modo desarrollo
            </div>
            <p class="text-muted mb-3" style="font-size:.82rem">
                Simula que la pasarela ya confirmó el pago para probar el flujo end-to-end.
            </p>
            <form method="POST" action="<?= APP_URL ?>/pago/<?= (int)$pago['id'] ?>/simular-completar" class="m-0">
                <?= $csrfField ?>
                <button type="submit" class="btn btn-warning btn-sm">
                    <i class="bi bi-check2-circle me-1"></i>Simular pago confirmado
                </button>
            </form>
        </div>
    </div>
    <?php endif; ?>

    <div class="text-center mt-3">
        <a href="<?= APP_URL ?>/mis-tokens" class="text-muted" style="font-size:.85rem">Ver mis pagos pendientes</a>
    </div>
</div>
