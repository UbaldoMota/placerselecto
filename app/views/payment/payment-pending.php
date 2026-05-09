<?php /** @var array $pago */ /** @var ?array $paquete */ /** @var bool $devMode */ /** @var ?string $whatsappUrl */
$esPayCash   = ($pago['metodo_pago'] ?? '') === 'paycash';
$esTruevo    = ($pago['metodo_pago'] ?? '') === 'truevo';
$esExternoWA = ($pago['metodo_pago'] ?? '') === 'externo_wa';
$ref         = $pago['referencia_pasarela'] ?? '';
$expira      = $pago['expira_en'] ?? null;
?>
<div class="container py-4" style="max-width:680px">

    <div class="text-center mb-4">
        <?php if ($esExternoWA): ?>
            <i class="bi bi-whatsapp" style="font-size:3rem;color:#25D366"></i>
            <h1 class="h4 fw-bold mt-2 mb-1">Solicitud de pago registrada</h1>
            <p class="text-muted" style="font-size:.92rem">
                Te abrimos WhatsApp en otra pestaña para que envíes tu pedido a nuestro equipo.<br>
                <span style="font-size:.85rem">En cuanto recibamos el pago, los tokens se acreditarán automáticamente a tu saldo.</span>
            </p>
        <?php elseif ($esPayCash): ?>
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
            <?php if ($ref !== ''): ?>
            <div class="mt-2 pt-2" style="border-top:1px solid var(--color-border);font-size:.82rem">
                <span class="text-muted">Referencia:</span>
                <code style="font-size:.85rem;background:var(--color-bg-card2);padding:.1rem .4rem;border-radius:3px;color:var(--color-text)"><?= e($ref) ?></code>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <?php /* ============================================================
         FLUJO EXTERNO_WA — boton WhatsApp + auto-open + pasos
         ============================================================ */ ?>
    <?php if ($esExternoWA && !empty($whatsappUrl)): ?>
    <a href="<?= e($whatsappUrl) ?>" target="_blank" rel="noopener noreferrer"
       id="btn-abrir-wa"
       class="btn w-100 d-flex align-items-center justify-content-center gap-2 mb-3"
       style="background:#25D366;color:#fff;font-weight:800;font-size:1.05rem;padding:.85rem 1rem;border-radius:14px;border:0;box-shadow:0 6px 18px rgba(37,211,102,.35);text-decoration:none">
        <i class="bi bi-whatsapp" style="font-size:1.35rem"></i>
        Abrir WhatsApp
    </a>

    <div class="card mb-3" style="background:rgba(13,202,240,.06);border:1px solid rgba(13,202,240,.25)">
        <div class="card-body" style="padding:.9rem 1.1rem;font-size:.85rem;line-height:1.55">
            <strong style="color:#0891B2;font-size:.92rem">
                <i class="bi bi-info-circle-fill me-1"></i>¿Qué sigue?
            </strong>
            <ol class="mb-0 mt-2 ps-3" style="font-size:.83rem">
                <li>En WhatsApp envía el mensaje pre-llenado a nuestro equipo.</li>
                <li>Recibirás el <strong>link de pago seguro</strong> con el método que prefieras.</li>
                <li>Realizas el pago.</li>
                <li>Cuando lo confirmamos, los tokens se acreditan a tu saldo automáticamente.</li>
            </ol>
            <div class="text-muted mt-2" style="font-size:.78rem">
                <i class="bi bi-clock-history me-1"></i>
                Suele tardar entre 5 y 30 minutos en horario hábil. Puedes seguir navegando o cerrar esta pestaña — la solicitud queda registrada.
            </div>
        </div>
    </div>

    <script>
    // Auto-open de WhatsApp en pestaña nueva al cargar la pantalla.
    // Si el navegador bloquea por popup blocker, el botón visible permite abrirlo manualmente.
    (function () {
        try {
            window.open(<?= json_encode($whatsappUrl, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>, '_blank', 'noopener,noreferrer');
        } catch (e) { /* sin acción — el botón queda visible */ }
    })();
    </script>
    <?php endif; ?>

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

    <div class="text-center mt-3 d-flex justify-content-center gap-3 flex-wrap">
        <a href="<?= APP_URL ?>/mis-tokens" class="text-muted" style="font-size:.85rem">
            <i class="bi bi-clock-history me-1"></i>Mis pagos
        </a>
        <a href="<?= APP_URL ?>/dashboard" class="text-muted" style="font-size:.85rem">
            <i class="bi bi-house me-1"></i>Dashboard
        </a>
    </div>
</div>
