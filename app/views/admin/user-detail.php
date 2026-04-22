<?php
/**
 * admin/user-detail.php — Detalle completo de un usuario.
 */
$estadoVer = $usuario['estado_verificacion'];
?>

<div class="container-fluid px-4 py-4">

    <a href="<?= APP_URL ?>/admin/usuarios" class="btn btn-sm btn-secondary mb-4">
        <i class="bi bi-arrow-left me-1"></i>Volver a usuarios
    </a>

    <div class="row g-4">

        <!-- Columna izquierda: datos del usuario -->
        <div class="col-12 col-lg-4">

            <!-- Perfil -->
            <div class="card mb-4">
                <div class="card-body text-center py-4">
                    <div style="width:72px;height:72px;border-radius:50%;background:rgba(255,45,117,.12);border:2px solid rgba(255,45,117,.25);display:flex;align-items:center;justify-content:center;font-size:1.8rem;font-weight:900;color:var(--color-primary);margin:0 auto 1rem">
                        <?= strtoupper(mb_substr($usuario['nombre'], 0, 1)) ?>
                    </div>
                    <h2 class="h5 fw-bold mb-1"><?= e($usuario['nombre']) ?></h2>
                    <p class="text-muted mb-3" style="font-size:.82rem"><?= e($usuario['email']) ?></p>

                    <!-- Estado de verificación -->
                    <?php
                    $vMap = [
                        'pendiente' => ['badge-pendiente','clock-history','Pendiente de revisión'],
                        'aprobado'  => ['badge-publicado','patch-check-fill','Verificado'],
                        'rechazado' => ['badge-rechazado','x-octagon','Rechazado'],
                    ];
                    [$cls, $ico, $lbl] = $vMap[$estadoVer] ?? ['','question','?'];
                    ?>
                    <span class="badge-estado <?= $cls ?> d-inline-flex align-items-center gap-1 px-3 py-1" style="font-size:.78rem">
                        <i class="bi bi-<?= $ico ?>"></i><?= $lbl ?>
                    </span>
                </div>

                <!-- Datos -->
                <div class="card-body pt-0">
                    <dl class="row mb-0" style="font-size:.85rem;row-gap:.5rem">
                        <dt class="col-5 text-muted fw-normal">ID</dt>
                        <dd class="col-7 mb-0"><?= (int)$usuario['id'] ?></dd>

                        <dt class="col-5 text-muted fw-normal">Rol</dt>
                        <dd class="col-7 mb-0 text-capitalize"><?= e($usuario['rol']) ?></dd>

                        <dt class="col-5 text-muted fw-normal">Teléfono</dt>
                        <dd class="col-7 mb-0"><?= $usuario['telefono'] ? e($usuario['telefono']) : '—' ?></dd>

                        <dt class="col-5 text-muted fw-normal">IP registro</dt>
                        <dd class="col-7 mb-0" style="font-family:monospace;font-size:.78rem">
                            <?= $usuario['ip_registro'] ? e($usuario['ip_registro']) : '—' ?>
                        </dd>

                        <dt class="col-5 text-muted fw-normal">Último login</dt>
                        <dd class="col-7 mb-0">
                            <?= $usuario['ultimo_login'] ? e(Security::timeAgo($usuario['ultimo_login'])) : '—' ?>
                        </dd>

                        <dt class="col-5 text-muted fw-normal">Registro</dt>
                        <dd class="col-7 mb-0"><?= e(date('d/m/Y H:i', strtotime($usuario['fecha_creacion']))) ?></dd>

                        <dt class="col-5 text-muted fw-normal">Saldo tokens</dt>
                        <dd class="col-7 mb-0">
                            <strong class="text-primary" style="font-size:1rem">
                                <i class="bi bi-coin"></i> <?= number_format((int)($usuario['saldo_tokens'] ?? 0)) ?>
                            </strong>
                        </dd>
                    </dl>
                </div>
            </div>

            <!-- Ajuste de saldo -->
            <div class="card mb-4">
                <div class="card-header">
                    <strong><i class="bi bi-coin text-primary me-1"></i>Ajustar saldo</strong>
                </div>
                <div class="card-body">
                    <form method="POST" action="<?= APP_URL ?>/admin/usuario/<?= (int)$usuario['id'] ?>/saldo">
                        <?= $csrfField ?>
                        <div class="mb-2">
                            <label class="form-label">Acción</label>
                            <select name="accion" class="form-select form-select-sm" required>
                                <option value="sumar">Sumar tokens (+)</option>
                                <option value="restar">Restar tokens (−)</option>
                            </select>
                        </div>
                        <div class="mb-2">
                            <label class="form-label">Cantidad</label>
                            <input type="number" name="cantidad" class="form-control form-control-sm" min="1" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Motivo</label>
                            <input type="text" name="motivo" class="form-control form-control-sm" maxlength="200" required
                                   placeholder="Compensación por incidencia, corrección de error, etc.">
                        </div>
                        <button type="submit" class="btn btn-primary btn-sm w-100">
                            <i class="bi bi-check-lg me-1"></i>Aplicar ajuste
                        </button>
                        <small class="text-muted d-block mt-2" style="font-size:.72rem">
                            Se registra en el ledger como <code>ajuste_admin</code> y se notifica al usuario.
                        </small>
                    </form>
                </div>
            </div>

            <!-- Acciones de moderación -->
            <?php if ($usuario['rol'] !== 'admin'): ?>
            <div class="card">
                <div class="card-header">
                    <span class="fw-semibold" style="font-size:.875rem">
                        <i class="bi bi-shield-lock text-primary me-2"></i>Moderación
                    </span>
                </div>
                <div class="card-body d-flex flex-column gap-2">
                    <?php if ($estadoVer !== 'aprobado'): ?>
                    <form method="POST" action="<?= APP_URL ?>/admin/usuario/<?= (int)$usuario['id'] ?>/aprobar">
                        <?= $csrfField ?>
                        <button type="submit" class="btn w-100"
                                style="background:rgba(16,185,129,.1);color:#10B981;border:1px solid rgba(16,185,129,.3)">
                            <i class="bi bi-patch-check me-2"></i>Activar cuenta
                        </button>
                    </form>
                    <?php else: ?>
                    <div class="btn w-100 disabled"
                         style="background:rgba(16,185,129,.05);color:#10B981;border:1px solid rgba(16,185,129,.15);font-size:.85rem">
                        <i class="bi bi-check-circle me-2"></i>Cuenta activa
                    </div>
                    <?php endif; ?>

                    <?php if ($estadoVer !== 'rechazado'): ?>
                    <form method="POST" action="<?= APP_URL ?>/admin/usuario/<?= (int)$usuario['id'] ?>/rechazar">
                        <?= $csrfField ?>
                        <button type="submit" class="btn w-100"
                                style="background:rgba(255,193,7,.1);color:#F59E0B;border:1px solid rgba(255,193,7,.25)"
                                data-confirm="¿Suspender a <?= e($usuario['nombre']) ?>? Sus perfiles dejarán de ser visibles.">
                            <i class="bi bi-slash-circle me-2"></i>Suspender cuenta
                        </button>
                    </form>
                    <?php endif; ?>

                    <!-- Separador -->
                    <div style="border-top:1px solid var(--color-border);padding-top:.5rem;margin-top:.25rem">
                        <button type="button" class="btn btn-danger w-100"
                                data-bs-toggle="modal" data-bs-target="#modalEliminarDetalle">
                            <i class="bi bi-trash3 me-2"></i>Eliminar usuario permanentemente
                        </button>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Modal eliminación desde detalle -->
            <div class="modal fade" id="modalEliminarDetalle" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content" style="background:var(--color-bg-card);border:1px solid var(--color-border)">
                        <div class="modal-header" style="border-color:var(--color-border)">
                            <h5 class="modal-title fw-bold text-danger">
                                <i class="bi bi-exclamation-triangle-fill me-2"></i>Eliminar permanentemente
                            </h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <p>Vas a eliminar a <strong><?= e($usuario['nombre']) ?></strong>.</p>
                            <div class="alert mb-3" style="background:rgba(220,53,69,.1);border:1px solid rgba(220,53,69,.25);color:#dc3545;font-size:.85rem">
                                <i class="bi bi-trash3-fill me-1"></i>
                                Esta acción es <strong>irreversible</strong>. Se borrarán la cuenta, todos sus perfiles, fotos, anuncios y pagos.
                            </div>
                            <label class="form-label" style="font-size:.85rem">
                                Escribe <code>SI_ELIMINAR</code> para confirmar:
                            </label>
                            <input type="text" id="inputConfirmarDetalle" class="form-control" placeholder="SI_ELIMINAR" autocomplete="off">
                        </div>
                        <div class="modal-footer" style="border-color:var(--color-border)">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                            <form method="POST" action="<?= APP_URL ?>/admin/usuario/<?= (int)$usuario['id'] ?>/eliminar">
                                <?= $csrfField ?>
                                <input type="hidden" name="confirmar" value="SI_ELIMINAR">
                                <button type="submit" id="btnConfirmarEliminarDetalle" class="btn btn-danger" disabled>
                                    <i class="bi bi-trash3 me-1"></i>Eliminar definitivamente
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Documento de identidad -->
            <?php if ($usuario['rol'] !== 'admin'):
                $docEstado = $usuario['documento_estado'] ?? null;
                $docVerif  = (bool)$usuario['documento_verificado'];
                $docMotivo = $usuario['documento_rechazo_motivo'] ?? null;
            ?>
            <div class="card mt-4">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <span class="fw-semibold" style="font-size:.875rem">
                        <i class="bi bi-card-checklist text-primary me-2"></i>Documento de identidad
                    </span>
                    <?php if (!empty($usuario['documento_identidad'])): ?>
                        <?php if ($docEstado === 'verificado'): ?>
                        <span class="badge-estado badge-publicado" style="font-size:.7rem">
                            <i class="bi bi-patch-check-fill me-1"></i>Verificado
                        </span>
                        <?php elseif ($docEstado === 'rechazado'): ?>
                        <span class="badge-estado badge-rechazado" style="font-size:.7rem">
                            <i class="bi bi-x-circle me-1"></i>Rechazado
                        </span>
                        <?php else: ?>
                        <span class="badge-estado badge-pendiente" style="font-size:.7rem">
                            <i class="bi bi-clock me-1"></i>En revisión
                        </span>
                        <?php endif; ?>
                    <?php else: ?>
                    <span class="badge-estado" style="background:rgba(0,0,0,.04);color:var(--color-text-muted);font-size:.7rem">
                        <i class="bi bi-dash me-1"></i>No enviado
                    </span>
                    <?php endif; ?>
                </div>
                <div class="card-body" style="font-size:.83rem">
                    <?php if (!empty($usuario['documento_identidad'])): ?>
                        <!-- Imagen del documento -->
                        <a href="<?= APP_URL ?>/admin/usuario/<?= (int)$usuario['id'] ?>/documento"
                           target="_blank" rel="noopener"
                           style="display:block;border-radius:8px;overflow:hidden;border:1px solid var(--color-border)">
                            <img src="<?= APP_URL ?>/admin/usuario/<?= (int)$usuario['id'] ?>/documento"
                                 alt="Documento de identidad"
                                 style="width:100%;display:block;max-height:300px;object-fit:contain;background:#111"
                                 loading="lazy">
                        </a>
                        <p class="text-muted mt-2 mb-0" style="font-size:.74rem">
                            <i class="bi bi-clock me-1"></i>
                            Enviado: <?= !empty($usuario['documento_identidad_at']) ? e(date('d/m/Y H:i', strtotime($usuario['documento_identidad_at']))) : '—' ?>
                            &nbsp;·&nbsp;
                            <a href="<?= APP_URL ?>/admin/usuario/<?= (int)$usuario['id'] ?>/documento"
                               target="_blank" style="color:var(--color-primary)">
                                Ver en tamaño completo
                            </a>
                        </p>

                        <?php if ($docEstado === 'rechazado' && $docMotivo): ?>
                        <div class="mt-2 px-2 py-2 rounded" style="background:rgba(220,53,69,.07);border:1px solid rgba(220,53,69,.2);font-size:.76rem;color:#dc3545">
                            <i class="bi bi-info-circle me-1"></i>
                            <strong>Motivo de rechazo:</strong> <?= e($docMotivo) ?>
                        </div>
                        <?php endif; ?>

                        <!-- Acciones -->
                        <div class="mt-3 pt-2 d-flex flex-column gap-2" style="border-top:1px solid var(--color-border)">
                            <?php if (!$docVerif): ?>
                            <form method="POST" action="<?= APP_URL ?>/admin/usuario/<?= (int)$usuario['id'] ?>/verificacion">
                                <?= $csrfField ?>
                                <input type="hidden" name="campo" value="documento_verificado">
                                <input type="hidden" name="valor" value="1">
                                <button type="submit" class="btn btn-sm w-100"
                                        style="background:rgba(16,185,129,.1);color:#10B981;border:1px solid rgba(16,185,129,.25);font-size:.8rem">
                                    <i class="bi bi-check-lg me-1"></i>Marcar como verificado
                                </button>
                            </form>
                            <button type="button" class="btn btn-sm w-100"
                                    data-bs-toggle="modal" data-bs-target="#modalRechazarDoc"
                                    style="background:rgba(220,53,69,.1);color:#dc3545;border:1px solid rgba(220,53,69,.25);font-size:.8rem">
                                <i class="bi bi-x-lg me-1"></i>Rechazar documento
                            </button>
                            <?php else: ?>
                            <form method="POST" action="<?= APP_URL ?>/admin/usuario/<?= (int)$usuario['id'] ?>/verificacion">
                                <?= $csrfField ?>
                                <input type="hidden" name="campo" value="documento_verificado">
                                <input type="hidden" name="valor" value="0">
                                <button type="submit" class="btn btn-sm w-100"
                                        style="background:rgba(220,53,69,.1);color:#dc3545;border:1px solid rgba(220,53,69,.25);font-size:.8rem">
                                    <i class="bi bi-arrow-counterclockwise me-1"></i>Revocar verificación
                                </button>
                            </form>
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-3 text-muted">
                            <i class="bi bi-card-checklist" style="font-size:2rem;opacity:.35"></i>
                            <p class="mt-2 mb-0" style="font-size:.82rem">
                                El usuario no ha enviado su documento de identidad.
                            </p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Modal: rechazar documento -->
            <div class="modal fade" id="modalRechazarDoc" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content" style="background:var(--color-bg-card);border:1px solid var(--color-border)">
                        <div class="modal-header" style="border-color:var(--color-border)">
                            <h5 class="modal-title fw-bold" style="font-size:.95rem">
                                <i class="bi bi-x-circle-fill text-danger me-2"></i>Rechazar documento
                            </h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                        </div>
                        <form method="POST" action="<?= APP_URL ?>/admin/usuario/<?= (int)$usuario['id'] ?>/documento/rechazar">
                            <?= $csrfField ?>
                            <div class="modal-body">
                                <p class="text-muted mb-3" style="font-size:.84rem">
                                    El usuario recibirá una notificación con el motivo y podrá volver a subir su documento.
                                </p>
                                <p class="fw-semibold mb-2" style="font-size:.84rem">Selecciona el motivo:</p>
                                <div class="d-flex flex-column gap-2">
                                    <?php
                                    $motivos = [
                                        'ilegible'      => ['bi-image',          'Imagen ilegible o borrosa'],
                                        'incompleto'    => ['bi-crop',           'Documento recortado o incompleto'],
                                        'no_coincide'   => ['bi-person-x',       'El nombre no coincide con el de la cuenta'],
                                        'vencido'       => ['bi-calendar-x',     'Documento vencido o expirado'],
                                        'tipo_invalido' => ['bi-file-earmark-x', 'Tipo de documento no aceptado (solo INE, IFE o pasaporte)'],
                                        'manipulado'    => ['bi-shield-exclamation', 'Documento manipulado o editado digitalmente'],
                                        'sin_rostro'    => ['bi-eye-slash',      'No se distingue el rostro claramente'],
                                        'otro'          => ['bi-chat-text',      'Otro motivo'],
                                    ];
                                    foreach ($motivos as $val => [$ico, $label]):
                                    ?>
                                    <label class="hover-bg"
                                           style="display:flex;align-items:center;gap:.75rem;padding:.5rem .75rem;border-radius:8px;border:1px solid var(--color-border);cursor:pointer;font-size:.82rem;transition:background .15s">
                                        <input type="radio" name="motivo" value="<?= $val ?>" required
                                               style="accent-color:var(--color-primary);flex-shrink:0"
                                               <?= $val === 'otro' ? '' : '' ?>>
                                        <i class="bi <?= $ico ?>" style="color:var(--color-text-muted);font-size:.95rem;flex-shrink:0"></i>
                                        <?= e($label) ?>
                                    </label>
                                    <?php endforeach; ?>
                                </div>
                                <!-- Campo detalle para "otro" -->
                                <div id="detalleOtroWrap" style="display:none;margin-top:.75rem">
                                    <input type="text" name="detalle" class="form-control form-control-sm"
                                           placeholder="Describe brevemente el motivo..." maxlength="120">
                                </div>
                            </div>
                            <div class="modal-footer" style="border-color:var(--color-border)">
                                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
                                <button type="submit" class="btn btn-danger btn-sm">
                                    <i class="bi bi-x-lg me-1"></i>Confirmar rechazo
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <script src="<?= APP_URL ?>/public/assets/js/user-detail.js" defer></script>
            <?php endif; ?>

            <!-- Video de verificación de cuenta -->
            <?php if ($usuario['rol'] !== 'admin'): ?>
            <div class="card mt-4">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <span class="fw-semibold" style="font-size:.875rem">
                        <i class="bi bi-camera-video text-primary me-2"></i>Video de identidad
                    </span>
                    <?php if (!empty($usuario['video_verificacion'])): ?>
                    <span class="badge-estado badge-publicado" style="font-size:.7rem">
                        <i class="bi bi-check-circle me-1"></i>Enviado
                    </span>
                    <?php else: ?>
                    <span class="badge-estado badge-pendiente" style="font-size:.7rem">
                        <i class="bi bi-clock me-1"></i>Pendiente
                    </span>
                    <?php endif; ?>
                </div>
                <div class="card-body" style="font-size:.83rem">
                    <?php if (!empty($usuario['video_verificacion'])): ?>
                        <video controls preload="metadata"
                               style="width:100%;border-radius:var(--radius-sm);background:#000;max-height:240px">
                            <source src="<?= APP_URL ?>/admin/usuario/<?= (int)$usuario['id'] ?>/video">
                        </video>
                        <?php if (!empty($usuario['video_verificacion_at'])): ?>
                        <p class="text-muted mt-2 mb-0" style="font-size:.74rem">
                            <i class="bi bi-clock me-1"></i>
                            Enviado: <?= e(date('d/m/Y H:i', strtotime($usuario['video_verificacion_at']))) ?>
                        </p>
                        <?php endif; ?>
                    <?php else: ?>
                        <div class="text-center py-3 text-muted">
                            <i class="bi bi-camera-video-off" style="font-size:2rem;opacity:.4"></i>
                            <p class="mt-2 mb-0" style="font-size:.82rem">
                                El usuario aún no ha enviado el video de verificación.
                            </p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Verificaciones de confiabilidad -->
            <?php if ($usuario['rol'] !== 'admin'): ?>
            <div class="card mt-4">
                <div class="card-header">
                    <span class="fw-semibold" style="font-size:.875rem">
                        <i class="bi bi-patch-check text-primary me-2"></i>Confiabilidad
                    </span>
                </div>
                <div class="card-body d-flex flex-column gap-2">
                    <?php
                    $checks = [
                        ['campo' => 'documento_verificado', 'label' => 'Documento de identidad', 'icono' => 'bi-card-checklist'],
                        ['campo' => 'fotos_verificadas',    'label' => 'Fotos / selfie verificadas', 'icono' => 'bi-camera-video-fill'],
                    ];
                    foreach ($checks as $chk):
                        $activo = (bool) $usuario[$chk['campo']];
                    ?>
                    <div class="d-flex align-items-center justify-content-between gap-2"
                         style="padding:.4rem .2rem;border-bottom:1px solid var(--color-border)">
                        <span style="font-size:.8rem;color:<?= $activo ? 'var(--color-text)' : 'var(--color-text-muted)' ?>">
                            <i class="bi <?= $chk['icono'] ?> me-2"
                               style="color:<?= $activo ? '#10B981' : 'var(--color-text-muted)' ?>"></i>
                            <?= e($chk['label']) ?>
                        </span>
                        <form method="POST" action="<?= APP_URL ?>/admin/usuario/<?= (int)$usuario['id'] ?>/verificacion">
                            <?= $csrfField ?>
                            <input type="hidden" name="campo" value="<?= e($chk['campo']) ?>">
                            <input type="hidden" name="valor" value="<?= $activo ? '0' : '1' ?>">
                            <button type="submit" class="btn btn-sm"
                                    style="<?= $activo
                                        ? 'background:rgba(220,53,69,.1);color:#dc3545;border:1px solid rgba(220,53,69,.25)'
                                        : 'background:rgba(16,185,129,.1);color:#10B981;border:1px solid rgba(16,185,129,.25)' ?>;
                                           font-size:.72rem;padding:.2rem .55rem">
                                <?= $activo ? '<i class="bi bi-x-lg"></i> Revocar' : '<i class="bi bi-check-lg"></i> Verificar' ?>
                            </button>
                        </form>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

        </div>

        <!-- Columna derecha: anuncios y pagos -->
        <div class="col-12 col-lg-8 d-flex flex-column gap-4">

            <!-- Anuncios -->
            <div class="card">
                <div class="card-header">
                    <span class="fw-semibold">
                        <i class="bi bi-collection text-primary me-2"></i>
                        Anuncios (<?= count($anuncios) ?>)
                    </span>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($anuncios)): ?>
                    <p class="text-center text-muted py-4 mb-0" style="font-size:.875rem">Sin anuncios.</p>
                    <?php else: ?>
                    <div class="table-responsive">
                        <table class="table mb-0">
                            <thead>
                                <tr>
                                    <th>Título</th>
                                    <th>Estado</th>
                                    <th class="d-none d-sm-table-cell">Ciudad</th>
                                    <th class="d-none d-md-table-cell">Vistas</th>
                                    <th class="text-end">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($anuncios as $ad): ?>
                            <tr>
                                <td>
                                    <div style="font-size:.85rem;max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">
                                        <?= e($ad['titulo']) ?>
                                    </div>
                                    <?php if ($ad['destacado']): ?>
                                    <span class="badge-estado badge-destacado" style="font-size:.65rem">
                                        <i class="bi bi-star-fill me-1"></i>Destacado
                                    </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php
                                    $sMap = ['pendiente'=>['badge-pendiente','En revisión'],'publicado'=>['badge-publicado','Publicado'],'rechazado'=>['badge-rechazado','Rechazado'],'expirado'=>['badge-expirado','Expirado']];
                                    [$sc,$sl] = $sMap[$ad['estado']] ?? ['',''];
                                    ?>
                                    <span class="badge-estado <?= $sc ?>"><?= $sl ?></span>
                                </td>
                                <td class="d-none d-sm-table-cell text-muted" style="font-size:.8rem"><?= e($ad['ciudad']) ?></td>
                                <td class="d-none d-md-table-cell text-muted" style="font-size:.8rem">
                                    <i class="bi bi-eye me-1"></i><?= number_format((int)$ad['vistas']) ?>
                                </td>
                                <td class="text-end">
                                    <div class="d-flex gap-1 justify-content-end">
                                        <?php if ($ad['estado'] === 'pendiente'): ?>
                                        <form method="POST" action="<?= APP_URL ?>/admin/anuncio/<?= (int)$ad['id'] ?>/publicar">
                                            <?= $csrfField ?>
                                            <button class="btn btn-sm" title="Publicar"
                                                    style="background:rgba(16,185,129,.1);color:#10B981;border:1px solid rgba(16,185,129,.25)">
                                                <i class="bi bi-check-lg"></i>
                                            </button>
                                        </form>
                                        <?php endif; ?>
                                        <form method="POST" action="<?= APP_URL ?>/admin/anuncio/<?= (int)$ad['id'] ?>/eliminar">
                                            <?= $csrfField ?>
                                            <button class="btn btn-sm btn-danger" title="Eliminar"
                                                    data-confirm="¿Eliminar «<?= e($ad['titulo']) ?>»?">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Pagos -->
            <div class="card">
                <div class="card-header">
                    <span class="fw-semibold">
                        <i class="bi bi-credit-card text-primary me-2"></i>
                        Pagos (<?= count($pagos) ?>)
                    </span>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($pagos)): ?>
                    <p class="text-center text-muted py-4 mb-0" style="font-size:.875rem">Sin pagos.</p>
                    <?php else: ?>
                    <div class="table-responsive">
                        <table class="table mb-0">
                            <thead>
                                <tr>
                                    <th>Anuncio</th>
                                    <th>Plan</th>
                                    <th>Monto</th>
                                    <th>Estado</th>
                                    <th class="d-none d-md-table-cell">Fecha</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($pagos as $p): ?>
                            <tr>
                                <td style="font-size:.83rem"><?= e(Security::truncate($p['anuncio_titulo'] ?? '—', 35)) ?></td>
                                <td style="font-size:.83rem"><?= (int)$p['tipo_destacado'] ?> días</td>
                                <td class="fw-semibold" style="color:var(--color-primary)"><?= e(Security::formatMoney((float)$p['monto'])) ?></td>
                                <td>
                                    <?php
                                    $pMap = ['completado'=>['badge-publicado','Completado'],'pendiente'=>['badge-pendiente','Pendiente'],'fallido'=>['badge-rechazado','Fallido']];
                                    [$pc,$pl] = $pMap[$p['estado']] ?? ['',''];
                                    ?>
                                    <span class="badge-estado <?= $pc ?>"><?= $pl ?></span>
                                </td>
                                <td class="d-none d-md-table-cell text-muted" style="font-size:.78rem">
                                    <?= $p['fecha_pago'] ? e(date('d/m/Y', strtotime($p['fecha_pago']))) : '—' ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

        </div>
    </div>
</div>

<?php
// El JS está en public/assets/js/user-detail.js que ya se incluye arriba.
?>
