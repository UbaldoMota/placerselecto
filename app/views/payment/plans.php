<?php
/**
 * payment/plans.php — Selección de plan de destacado.
 */
?>

<div class="container py-4" style="max-width:860px">

    <!-- Encabezado -->
    <div class="mb-4">
        <a href="<?= APP_URL ?>/mis-anuncios" class="btn btn-sm btn-secondary mb-3">
            <i class="bi bi-arrow-left me-1"></i>Mis anuncios
        </a>
        <h1 class="h4 fw-bold mb-1">
            <i class="bi bi-star-fill text-warning me-2"></i>Destacar anuncio
        </h1>
        <p class="text-muted mb-0" style="font-size:.875rem">
            Los anuncios destacados aparecen primero en el listado y atraen más visitas.
        </p>
    </div>

    <!-- Anuncio seleccionado -->
    <div class="card mb-4">
        <div class="card-body d-flex gap-3 align-items-center">
            <?php $imgUrl = Security::imgUrl($anuncio); ?>
            <?php if ($imgUrl): ?>
                <img src="<?= e($imgUrl) ?>"
                     alt=""
                     style="width:64px;height:64px;object-fit:cover;border-radius:var(--radius-sm);border:1px solid var(--color-border);flex-shrink:0">
            <?php else: ?>
                <div style="width:64px;height:64px;border-radius:var(--radius-sm);background:var(--color-bg-card2);border:1px solid var(--color-border);display:flex;align-items:center;justify-content:center;color:var(--color-text-muted);flex-shrink:0">
                    <i class="bi bi-image fs-4"></i>
                </div>
            <?php endif; ?>
            <div class="flex-grow-1 min-width-0">
                <div class="fw-semibold text-truncate"><?= e($anuncio['titulo']) ?></div>
                <div class="text-muted" style="font-size:.8rem">
                    <i class="bi bi-geo-alt me-1"></i><?= e($anuncio['ciudad']) ?>
                    <span class="ms-2"><i class="bi bi-eye me-1"></i><?= number_format((int)$anuncio['vistas']) ?> vistas</span>
                </div>
            </div>
            <span class="badge-estado badge-publicado flex-shrink-0">
                <i class="bi bi-check-circle me-1"></i>Publicado
            </span>
        </div>
    </div>

    <!-- Destacado activo -->
    <?php if ($tieneDestacado): ?>
    <div class="alert mb-4 d-flex align-items-center gap-2"
         style="background:rgba(245,158,11,.1);border:1px solid rgba(245,158,11,.25);color:#F59E0B;border-radius:var(--radius-md)">
        <i class="bi bi-star-fill fs-5"></i>
        <div>
            <strong>Este anuncio ya tiene destacado activo</strong>
            <?php if ($expira): ?>
            <div style="font-size:.82rem;opacity:.8">
                Expira: <?= e(date('d/m/Y H:i', strtotime($expira))) ?>
            </div>
            <?php endif; ?>
            <div style="font-size:.82rem;opacity:.8">
                Contratar un nuevo plan extenderá el período desde ahora.
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Beneficios -->
    <div class="row g-3 mb-5">
        <?php
        $beneficios = [
            ['icono' => 'bi-arrow-up-circle-fill', 'color' => '#FF2D75', 'titulo' => 'Prioridad máxima',
             'desc'  => 'Aparece antes que todos los anuncios normales'],
            ['icono' => 'bi-eye-fill',              'color' => '#17a2b8', 'titulo' => 'Más visibilidad',
             'desc'  => 'Hasta 5x más vistas que un anuncio estándar'],
            ['icono' => 'bi-badge-ad-fill',         'color' => '#10B981', 'titulo' => 'Badge destacado',
             'desc'  => 'Etiqueta visual que llama la atención del usuario'],
            ['icono' => 'bi-lightning-charge-fill', 'color' => '#F59E0B', 'titulo' => 'Activación inmediata',
             'desc'  => 'El destacado se activa al instante tras el pago'],
        ];
        foreach ($beneficios as $b):
        ?>
        <div class="col-6 col-md-3">
            <div class="text-center p-3 rounded-3 h-100"
                 style="background:var(--color-bg-card);border:1px solid var(--color-border)">
                <i class="bi <?= e($b['icono']) ?> fs-2 d-block mb-2" style="color:<?= e($b['color']) ?>"></i>
                <div class="fw-semibold mb-1" style="font-size:.85rem"><?= e($b['titulo']) ?></div>
                <div class="text-muted" style="font-size:.75rem"><?= e($b['desc']) ?></div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- PLANES -->
    <h2 class="h5 fw-bold mb-3">Elige tu plan</h2>

    <form method="POST"
          action="<?= APP_URL ?>/destacar/<?= (int)$anuncio['id'] ?>"
          id="plan-form">
        <?= $csrfField ?>
        <input type="hidden" name="plan" id="plan-input" value="">

        <div class="row g-3 mb-4" id="planes-container">
            <?php foreach ($planes as $dias => $info):
                $esPopular = ($dias === 7);
            ?>
            <div class="col-12 col-md-4">
                <div class="plan-card <?= $esPopular ? 'popular' : '' ?>"
                     data-dias="<?= (int)$dias ?>"
                     onclick="selectPlan(<?= (int)$dias ?>)">

                    <?php if ($esPopular): ?>
                    <div class="plan-badge-popular">
                        <i class="bi bi-fire me-1"></i>Más popular
                    </div>
                    <?php endif; ?>

                    <!-- Icono y días -->
                    <div class="mb-3">
                        <div style="width:56px;height:56px;border-radius:50%;background:rgba(255,45,117,.1);border:2px solid rgba(255,45,117,.2);display:flex;align-items:center;justify-content:center;margin:0 auto;font-size:1.5rem">
                            <?php if ($dias === 3): ?>
                                <i class="bi bi-lightning-charge text-warning"></i>
                            <?php elseif ($dias === 7): ?>
                                <i class="bi bi-star-fill" style="color:var(--color-primary)"></i>
                            <?php else: ?>
                                <i class="bi bi-trophy-fill text-info"></i>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="fw-black" style="font-size:1.1rem;margin-bottom:.25rem">
                        <?= (int)$dias ?> días
                    </div>
                    <div class="plan-price mb-2">
                        $<?= number_format($info['precio'], 0) ?>
                        <span style="font-size:.85rem;color:var(--color-text-muted)">MXN</span>
                    </div>
                    <div class="text-muted" style="font-size:.78rem">
                        $<?= number_format($info['precio'] / $dias, 1) ?> MXN/día
                    </div>

                    <!-- Checkmark seleccionado -->
                    <div class="plan-check mt-3 d-none"
                         style="width:28px;height:28px;border-radius:50%;background:var(--color-primary);display:flex;align-items:center;justify-content:center;margin:0 auto;color:#fff">
                        <i class="bi bi-check-lg" style="font-size:.9rem"></i>
                    </div>

                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Resumen de pago -->
        <div class="card mb-4 d-none" id="payment-summary">
            <div class="card-body">
                <h6 class="fw-bold mb-3">
                    <i class="bi bi-receipt text-primary me-2"></i>Resumen del pedido
                </h6>
                <div class="d-flex justify-content-between mb-2" style="font-size:.9rem">
                    <span class="text-muted">Plan seleccionado:</span>
                    <span class="fw-semibold" id="summary-plan">—</span>
                </div>
                <div class="d-flex justify-content-between mb-2" style="font-size:.9rem">
                    <span class="text-muted">Duración:</span>
                    <span id="summary-duracion">—</span>
                </div>
                <hr style="border-color:var(--color-border)">
                <div class="d-flex justify-content-between">
                    <span class="fw-bold">Total a pagar:</span>
                    <span class="fw-black fs-5" style="color:var(--color-primary)" id="summary-total">—</span>
                </div>

                <!-- Aviso de simulación -->
                <div class="mt-3 p-2 rounded-2 text-center"
                     style="background:rgba(255,193,7,.06);border:1px solid rgba(255,193,7,.15);font-size:.75rem;color:#ffd44d">
                    <i class="bi bi-info-circle me-1"></i>
                    <strong>Pago simulado.</strong>
                    En producción se integrará con CCBill / Segpay.
                    No se cargará ningún importe real.
                </div>
            </div>
        </div>

        <div class="d-flex gap-3">
            <button type="submit"
                    id="pay-btn"
                    class="btn btn-primary btn-lg flex-fill d-none"
                    data-confirm="¿Confirmar la compra del plan seleccionado?">
                <i class="bi bi-credit-card me-2"></i>Confirmar y pagar
            </button>
            <a href="<?= APP_URL ?>/mis-anuncios" class="btn btn-secondary btn-lg">
                Cancelar
            </a>
        </div>

    </form>
</div>

<script>
const planes = <?= json_encode(array_map(fn($dias, $info) => [
    'dias'   => $dias,
    'precio' => $info['precio'],
    'label'  => $info['label'],
], array_keys(PLANES_DESTACADO), PLANES_DESTACADO), JSON_UNESCAPED_UNICODE) ?>;

function selectPlan(dias) {
    // Deseleccionar todos
    document.querySelectorAll('.plan-card').forEach(c => {
        c.classList.remove('selected');
        c.querySelector('.plan-check')?.classList.add('d-none');
    });

    // Seleccionar el elegido
    const card = document.querySelector(`.plan-card[data-dias="${dias}"]`);
    if (card) {
        card.classList.add('selected');
        card.querySelector('.plan-check')?.classList.remove('d-none');
    }

    // Actualizar input hidden y resumen
    document.getElementById('plan-input').value = dias;

    const plan = planes.find(p => p.dias === dias);
    if (plan) {
        document.getElementById('summary-plan').textContent    = plan.label;
        document.getElementById('summary-duracion').textContent = plan.dias + ' días';
        document.getElementById('summary-total').textContent   = '$' + plan.precio.toLocaleString('es-MX', {minimumFractionDigits:0}) + ' MXN';
        document.getElementById('payment-summary').classList.remove('d-none');
        document.getElementById('pay-btn').classList.remove('d-none');
    }
}

// Auto-seleccionar plan popular (7 días)
document.addEventListener('DOMContentLoaded', () => selectPlan(7));
</script>
