<?php
/** @var array<int,array{clave:string, valor:?string, descripcion:?string, tipo:string, fecha_actualizacion:string}> $items */

$labels = [
    'whatsapp_pagos'   => 'WhatsApp para pagos',
    'whatsapp_soporte' => 'WhatsApp de soporte',
    'email_pagos'      => 'Email de pagos',
];
?>
<div class="container py-4" style="max-width:880px">

    <div class="d-flex flex-wrap gap-2 justify-content-end mb-3">
        <a href="<?= APP_URL ?>/admin" class="btn btn-sm btn-secondary">
            <i class="bi bi-arrow-left me-1"></i>Panel admin
        </a>
    </div>

    <div class="mb-4">
        <h1 class="h4 fw-bold mb-1">
            <i class="bi bi-sliders text-primary me-2"></i>Configuración general
        </h1>
        <p class="text-muted mb-0" style="font-size:.88rem">
            Datos de contacto y opciones que pueden cambiar con el tiempo. Se aplican al instante en el sitio público.
        </p>
    </div>

    <?php if (empty($items)): ?>
    <div class="alert alert-info">
        Aún no hay parámetros de configuración. Ejecuta <code>migration_configuracion.sql</code>.
    </div>
    <?php else: ?>

    <form method="POST" action="<?= APP_URL ?>/admin/configuracion">
        <?= $csrfField ?>

        <div class="card">
            <div class="card-body p-4">
                <div class="d-flex flex-column gap-4">
                    <?php foreach ($items as $i): ?>
                    <?php
                        $label = $labels[$i['clave']] ?? ucfirst(str_replace('_', ' ', $i['clave']));
                        $tipo  = $i['tipo'] ?? 'texto';
                        $val   = $i['valor'] ?? '';
                        $tipoInput = match ($tipo) {
                            'email'    => 'email',
                            'telefono' => 'tel',
                            'url'      => 'url',
                            'numero'   => 'number',
                            default    => 'text',
                        };
                    ?>
                    <div>
                        <label class="form-label fw-semibold" for="cfg-<?= e($i['clave']) ?>">
                            <?= e($label) ?>
                            <code class="text-muted ms-2" style="font-size:.7rem;font-weight:400"><?= e($i['clave']) ?></code>
                        </label>

                        <?php if ($tipo === 'textarea'): ?>
                        <textarea id="cfg-<?= e($i['clave']) ?>"
                                  name="cfg[<?= e($i['clave']) ?>]"
                                  class="form-control"
                                  rows="3"><?= e($val) ?></textarea>
                        <?php elseif ($tipo === 'booleano'): ?>
                        <select id="cfg-<?= e($i['clave']) ?>"
                                name="cfg[<?= e($i['clave']) ?>]"
                                class="form-select">
                            <option value="0" <?= !$val ? 'selected' : '' ?>>Desactivado</option>
                            <option value="1" <?= $val  ? 'selected' : '' ?>>Activado</option>
                        </select>
                        <?php else: ?>
                        <input type="<?= $tipoInput ?>"
                               id="cfg-<?= e($i['clave']) ?>"
                               name="cfg[<?= e($i['clave']) ?>]"
                               value="<?= e($val) ?>"
                               <?= $tipo === 'telefono' ? 'placeholder="ej. 5215555555555 (sin signos)" inputmode="numeric"' : '' ?>
                               class="form-control">
                        <?php endif; ?>

                        <?php if (!empty($i['descripcion'])): ?>
                        <div class="form-text" style="font-size:.78rem"><?= e($i['descripcion']) ?></div>
                        <?php endif; ?>

                        <?php if (!empty($i['fecha_actualizacion'])): ?>
                        <div class="text-muted mt-1" style="font-size:.7rem">
                            <i class="bi bi-clock-history me-1"></i>
                            Última actualización: <?= e(substr($i['fecha_actualizacion'], 0, 16)) ?>
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>

                <div class="d-flex justify-content-end mt-4 pt-3" style="border-top:1px solid var(--color-border)">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-lg me-1"></i>Guardar cambios
                    </button>
                </div>
            </div>
        </div>
    </form>

    <div class="alert alert-info mt-3" style="font-size:.85rem">
        <i class="bi bi-info-circle me-1"></i>
        Los números de WhatsApp se guardan solo con dígitos (formato internacional, ej. <code>5215555555555</code> = 52 + 1 + 10 dígitos del celular MX).
    </div>

    <?php endif; ?>
</div>
