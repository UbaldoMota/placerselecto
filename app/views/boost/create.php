<?php
/** @var array $perfil */
/** @var int   $saldo */
/** @var array $tarifas */
/** @var array $boosts */
/** @var int   $minHoras */
/** @var int   $maxHoras */

$tphTop  = (int)($tarifas['top']['tokens_por_hora']       ?? 3);
$tphRes  = (int)($tarifas['resaltado']['tokens_por_hora'] ?? 1);

// Cuantas horas alcanza el saldo para cada tipo
$horasTopAlcance = $tphTop > 0 ? floor($saldo / $tphTop) : 0;
$horasResAlcance = $tphRes > 0 ? floor($saldo / $tphRes) : 0;
?>
<div class="container py-4" style="max-width:920px">

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

    <!-- ============================================================
         SECCION EDUCATIVA — diferencia entre TOP y Resaltado
         ============================================================ -->
    <div class="card mb-4">
        <div class="card-header">
            <span class="fw-semibold">
                <i class="bi bi-info-circle text-primary me-2"></i>¿Cuál elegir? Diferencia entre TOP y Resaltado
            </span>
        </div>
        <div class="card-body">
            <div class="row g-3">

                <!-- TOP -->
                <div class="col-12 col-md-6">
                    <div class="p-3 rounded h-100" style="background:rgba(255,45,117,.06);border:1px solid rgba(255,45,117,.18)">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <div style="width:36px;height:36px;border-radius:8px;background:var(--color-primary);color:#fff;display:flex;align-items:center;justify-content:center">
                                <i class="bi bi-arrow-up-square-fill" style="font-size:1.1rem"></i>
                            </div>
                            <strong style="font-size:1rem;color:var(--color-primary)">TOP — sales primero</strong>
                        </div>
                        <p class="mb-2" style="font-size:.85rem;line-height:1.5">
                            Tu perfil <strong>aparece en la cabecera</strong> de tu municipio durante el tiempo que actives el boost. Es lo más visible. Si solo eliges una opción, esta es la que más contactos te trae.
                        </p>
                        <ul class="mb-2 ps-3" style="font-size:.8rem;line-height:1.6">
                            <li><strong><?= $tphTop ?> tokens / hora</strong> de boost</li>
                            <li>Posición: <strong>primera fila</strong> del listado</li>
                            <li>Visibilidad: <strong>máxima</strong></li>
                        </ul>
                        <?php if ($horasTopAlcance > 0): ?>
                        <div class="mt-2 p-2 rounded" style="background:rgba(255,255,255,.7);font-size:.78rem">
                            <i class="bi bi-piggy-bank-fill text-primary me-1"></i>
                            Con tu saldo de <strong><?= number_format($saldo) ?> tk</strong> te destacas
                            <strong style="color:var(--color-primary)"><?= number_format($horasTopAlcance) ?> horas</strong> en TOP.
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- RESALTADO -->
                <div class="col-12 col-md-6">
                    <div class="p-3 rounded h-100" style="background:rgba(245,158,11,.07);border:1px solid rgba(245,158,11,.22)">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <div style="width:36px;height:36px;border-radius:8px;background:#F59E0B;color:#fff;display:flex;align-items:center;justify-content:center">
                                <i class="bi bi-stars" style="font-size:1.1rem"></i>
                            </div>
                            <strong style="font-size:1rem;color:#92400E">RESALTADO — destaca visualmente</strong>
                        </div>
                        <p class="mb-2" style="font-size:.85rem;line-height:1.5">
                            Tu tarjeta tiene un <strong>fondo amarillo distintivo</strong> que llama la atención entre las demás del listado. <strong>No</strong> sube tu posición. Es ideal como complemento del TOP o cuando el TOP está saturado.
                        </p>
                        <ul class="mb-2 ps-3" style="font-size:.8rem;line-height:1.6">
                            <li><strong><?= $tphRes ?> token / hora</strong> de boost (<?= $tphTop > 0 ? round($tphTop / max(1,$tphRes), 1) : '?' ?>× más barato que TOP)</li>
                            <li>Posición: <strong>donde te toque</strong> por orden normal</li>
                            <li>Visibilidad: <strong>tarjeta amarilla en el listado</strong></li>
                        </ul>
                        <?php if ($horasResAlcance > 0): ?>
                        <div class="mt-2 p-2 rounded" style="background:rgba(255,255,255,.7);font-size:.78rem">
                            <i class="bi bi-piggy-bank-fill me-1" style="color:#F59E0B"></i>
                            Con tu saldo de <strong><?= number_format($saldo) ?> tk</strong> te resaltas
                            <strong style="color:#92400E"><?= number_format($horasResAlcance) ?> horas</strong>.
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

            </div>

            <div class="mt-3 p-2 rounded" style="background:rgba(59,130,246,.06);border:1px solid rgba(59,130,246,.18);font-size:.82rem">
                <i class="bi bi-lightbulb-fill" style="color:#3B82F6"></i>
                <strong>Recomendación:</strong> activa <strong>TOP en tus horas peak</strong> (vie/sáb/dom de 8pm a 4am) y <strong>Resaltado el resto del tiempo</strong> para mantenerte visible siempre con menor consumo.
            </div>
        </div>
    </div>

    <form method="POST" action="<?= APP_URL ?>/perfil/<?= (int)$perfil['id'] ?>/destacar" id="boost-form">
        <?= $csrfField ?>

        <!-- Tipo -->
        <div class="mb-3">
            <label class="form-label fw-semibold">1. Elige el tipo de boost</label>
            <div class="row g-2">
                <div class="col-md-6">
                    <label class="plan-card h-100 d-block" style="cursor:pointer">
                        <input type="radio" name="tipo" value="top" class="d-none" data-tph="<?= $tphTop ?>" checked>
                        <div class="d-flex align-items-center gap-2 justify-content-center mb-2">
                            <i class="bi bi-arrow-up-square-fill text-primary" style="font-size:1.5rem"></i>
                            <strong>TOP municipio</strong>
                        </div>
                        <div class="plan-price" style="font-size:1.5rem"><?= $tphTop ?></div>
                        <div class="text-muted" style="font-size:.75rem">tokens / hora</div>
                        <div class="mt-2 text-muted" style="font-size:.78rem;line-height:1.4">Aparece en la primera fila del listado</div>
                    </label>
                </div>
                <div class="col-md-6">
                    <label class="plan-card h-100 d-block" style="cursor:pointer">
                        <input type="radio" name="tipo" value="resaltado" class="d-none" data-tph="<?= $tphRes ?>">
                        <div class="d-flex align-items-center gap-2 justify-content-center mb-2">
                            <i class="bi bi-stars text-warning" style="font-size:1.5rem"></i>
                            <strong>RESALTADO visual</strong>
                        </div>
                        <div class="plan-price" style="font-size:1.5rem;color:var(--color-warning)"><?= $tphRes ?></div>
                        <div class="text-muted" style="font-size:.75rem">tokens / hora</div>
                        <div class="mt-2 text-muted" style="font-size:.78rem;line-height:1.4">Tarjeta con fondo amarillo en el listado</div>
                    </label>
                </div>
            </div>
        </div>

        <!-- Duración con presets estratégicos -->
        <div class="mb-3">
            <label class="form-label fw-semibold">2. Elige cuánto tiempo</label>
            <div class="d-flex gap-2 flex-wrap mb-2">
                <button type="button" class="btn btn-sm btn-secondary boost-h-preset" data-h="8" title="Una sesión nocturna">
                    🌙 1 noche peak (8h)
                </button>
                <button type="button" class="btn btn-sm btn-secondary boost-h-preset" data-h="24" title="Un día completo">
                    📅 1 día (24h)
                </button>
                <button type="button" class="btn btn-sm btn-secondary boost-h-preset" data-h="48" title="Vie + sáb">
                    🎉 Finde corto (48h)
                </button>
                <button type="button" class="btn btn-sm btn-secondary boost-h-preset" data-h="72" title="Vie + sáb + dom">
                    🔥 Finde completo (72h)
                </button>
                <button type="button" class="btn btn-sm btn-secondary boost-h-preset" data-h="168" title="Una semana entera">
                    💎 Semana entera (168h)
                </button>
            </div>
            <div class="d-flex gap-2 flex-wrap mb-2">
                <span class="text-muted me-2" style="font-size:.75rem;align-self:center">O elige horas exactas:</span>
                <?php foreach ([1, 3, 6, 12] as $h): ?>
                <button type="button" class="btn btn-sm boost-h-preset"
                        style="background:rgba(0,0,0,.04);color:var(--color-text);border:1px solid var(--color-border);font-size:.75rem;padding:.2rem .5rem"
                        data-h="<?= $h ?>"><?= $h ?>h</button>
                <?php endforeach; ?>
            </div>
            <input type="number" name="horas" id="boost-horas" class="form-control"
                   min="<?= $minHoras ?>" max="<?= $maxHoras ?>" value="24" required>
            <small class="text-muted" style="font-size:.78rem">Entre <?= $minHoras ?> y <?= $maxHoras ?> horas (máx. 7 días).</small>
        </div>

        <!-- Inicio -->
        <div class="mb-3">
            <label class="form-label fw-semibold">3. ¿Cuándo empieza?</label>
            <div class="d-flex gap-3 align-items-center mb-2">
                <label class="form-check">
                    <input type="radio" name="modo_inicio" value="ahora" class="form-check-input" checked>
                    <span class="form-check-label"><i class="bi bi-lightning-charge-fill text-primary me-1"></i>Ahora mismo</span>
                </label>
                <label class="form-check">
                    <input type="radio" name="modo_inicio" value="programado" class="form-check-input">
                    <span class="form-check-label"><i class="bi bi-calendar-event me-1"></i>Programado para después</span>
                </label>
            </div>
            <input type="datetime-local" name="inicio" id="boost-inicio" class="form-control" disabled
                   min="<?= date('Y-m-d\TH:i') ?>">
            <small class="text-muted d-block mt-1" style="font-size:.75rem">
                <i class="bi bi-info-circle me-1"></i>Si lo programas, puedes cancelarlo y recuperar el 100% de los tokens hasta antes de que empiece.
            </small>
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
                    <a href="<?= APP_URL ?>/tokens/comprar" class="alert-link">Recargar →</a>
                </div>
            </div>
        </div>

        <button type="submit" class="btn btn-primary w-100 btn-lg" id="boost-submit">
            <i class="bi bi-check-circle me-1"></i>Confirmar y destacar
        </button>

        <!-- Aviso de cancelacion (alineado con codigo real) -->
        <div class="mt-3 p-3 rounded" style="background:rgba(0,0,0,.03);border:1px solid var(--color-border);font-size:.82rem;line-height:1.55">
            <strong><i class="bi bi-shield-check text-primary me-1"></i>Sobre cancelar y recuperar tokens</strong>
            <ul class="mb-0 mt-2 ps-3 text-muted" style="font-size:.78rem">
                <li><strong>Boost programado</strong> (no iniciado): puedes cancelarlo y recuperar el <strong>100% de los tokens al saldo</strong>.</li>
                <li><strong>Boost activo</strong> (corriendo): no se puede cancelar. Los tokens consumidos no se reembolsan.</li>
                <li><strong>Boost finalizado</strong>: no aplica reembolso.</li>
            </ul>
            <div class="text-muted mt-2" style="font-size:.75rem">
                Ver <a href="<?= APP_URL ?>/pagos" style="color:var(--color-primary)">política de pagos completa</a>.
            </div>
        </div>
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
