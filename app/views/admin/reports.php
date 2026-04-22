<?php
/**
 * admin/reports.php — Gestión de reportes de contenido (vista de cards con acciones ricas).
 */
$estado = $filtros['estado'] ?? '';

$motivoLabels = [
    'verificar_edad'    => ['#B91C1C', 'bi-person-vcard',        'Verificar edad'],
    'mal_clasificado'   => ['#1E40AF', 'bi-tag-fill',            'Mal clasificado'],
    'difamaciones'      => ['#92400E', 'bi-chat-square-quote',   'Difamaciones'],
    'fotos_de_internet' => ['#92400E', 'bi-images',              'Fotos de Internet'],
    'fotos_son_mias'    => ['#B91C1C', 'bi-camera-fill',         'Fotos son mías'],
    'usan_mi_telefono'  => ['#B91C1C', 'bi-telephone-x-fill',    'Usan mi teléfono'],
    'estafa'            => ['#B91C1C', 'bi-cash-coin',           'Estafa'],
    'extorsion'         => ['#B91C1C', 'bi-shield-exclamation',  'Extorsión'],
    'menor_de_edad'     => ['#B91C1C', 'bi-person-x',            'Posible menor de edad'],
    'contenido_ilegal'  => ['#B91C1C', 'bi-exclamation-octagon', 'Contenido ilegal'],
    'spam'              => ['#92400E', 'bi-envelope-slash',      'Spam'],
    'engaño'            => ['#92400E', 'bi-question-circle',     'Engaño'],
    'datos_falsos'      => ['#1E40AF', 'bi-file-x',              'Datos falsos'],
    'otro'              => ['#555',    'bi-chat-dots',           'Otro'],
];
?>

<div class="container-fluid px-4 py-4" style="max-width:1100px">

    <div class="d-flex flex-wrap align-items-start justify-content-between gap-3 mb-4">
        <div>
            <h1 class="h4 fw-bold mb-1">
                <i class="bi bi-flag-fill text-primary me-2"></i>Reportes de contenido
            </h1>
            <p class="text-muted mb-0" style="font-size:.875rem">
                <?= number_format((int)($pagination['total'] ?? 0)) ?> reporte(s)
            </p>
        </div>
        <a href="<?= APP_URL ?>/admin" class="btn btn-sm btn-secondary">
            <i class="bi bi-arrow-left me-1"></i>Dashboard
        </a>
    </div>

    <!-- Tabs de estado -->
    <div class="d-flex gap-2 mb-4 flex-wrap">
        <?php
        $tabs = [
            ''          => ['Todos',      'bi-list-ul'],
            'pendiente' => ['Pendientes', 'bi-hourglass-split'],
            'revisado'  => ['En revisión','bi-eye'],
            'resuelto'  => ['Resueltos',  'bi-check-circle'],
            'rechazado' => ['Rechazados', 'bi-x-circle'],
        ];
        foreach ($tabs as $val => [$label, $icon]):
            $active = $estado === $val;
        ?>
        <a href="<?= APP_URL ?>/admin/reportes?estado=<?= e($val) ?>"
           class="btn btn-sm <?= $active ? 'btn-primary' : 'btn-secondary' ?>">
            <i class="bi <?= e($icon) ?> me-1"></i><?= e($label) ?>
        </a>
        <?php endforeach; ?>
    </div>

    <?php if (empty($reportes)): ?>
    <div class="card">
        <div class="card-body text-center py-5">
            <i class="bi bi-check-all text-success" style="font-size:2.5rem"></i>
            <p class="mt-2 mb-0 text-muted">No hay reportes en esta categoría.</p>
        </div>
    </div>
    <?php else: ?>

    <?php foreach ($reportes as $r):
        [$mColor, $mIcon, $mLabel] = $motivoLabels[$r['motivo']] ?? ['#555','bi-question','?'];
        $estadoMap = [
            'pendiente' => ['badge-pendiente','Pendiente','var(--color-warning)'],
            'revisado'  => ['badge-publicado','En revisión','var(--color-info)'],
            'resuelto'  => ['badge-publicado','Resuelto','var(--color-success)'],
            'rechazado' => ['badge-rechazado','Rechazado','var(--color-danger)'],
        ];
        [$eCls, $eLbl, $eBarColor] = $estadoMap[$r['estado']] ?? ['','?','var(--color-border)'];
        $imgUrl = !empty($r['perfil_imagen']) ? APP_URL . '/img/' . $r['perfil_imagen'] : null;
        $activo = in_array($r['estado'], ['pendiente','revisado'], true);
    ?>
    <div class="card mb-3" style="border-left:3px solid <?= $eBarColor ?>">

        <!-- Contenido reportado destacado arriba -->
        <?php if (!empty($r['perfil_nombre'])): ?>
        <div class="d-flex align-items-center gap-3 px-3 py-2"
             style="background:var(--color-bg-alt);border-bottom:1px solid var(--color-border)">
            <?php if ($imgUrl): ?>
                <img src="<?= e($imgUrl) ?>" alt=""
                     style="width:54px;height:54px;border-radius:8px;object-fit:cover;border:1px solid var(--color-border);flex-shrink:0">
            <?php else: ?>
                <div style="width:54px;height:54px;border-radius:8px;background:var(--color-bg-card);border:1px solid var(--color-border);display:flex;align-items:center;justify-content:center;color:var(--color-text-muted);flex-shrink:0">
                    <i class="bi bi-person" style="font-size:1.4rem"></i>
                </div>
            <?php endif; ?>
            <div class="flex-grow-1" style="min-width:0">
                <div class="text-muted" style="font-size:.68rem;text-transform:uppercase;letter-spacing:.05em;font-weight:600">
                    <i class="bi bi-flag-fill me-1"></i>Perfil reportado
                </div>
                <div class="fw-bold" style="font-size:1rem;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">
                    <?= e($r['perfil_nombre']) ?>
                </div>
                <div class="text-muted" style="font-size:.75rem;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">
                    <?= e($r['perfil_categoria'] ?? '—') ?> · <?= e($r['perfil_municipio'] ?? $r['perfil_ciudad'] ?? '—') ?>
                    <span class="badge-estado badge-<?= $r['perfil_estado']==='publicado'?'publicado':($r['perfil_estado']==='pendiente'?'pendiente':'rechazado') ?> ms-1"><?= e($r['perfil_estado'] ?? '—') ?></span>
                    <?php if (!empty($r['perfil_dueno'])): ?>
                    · Dueño: <strong><?= e($r['perfil_dueno']) ?></strong>
                    <?php endif; ?>
                </div>
            </div>
            <div class="d-flex gap-1 flex-shrink-0 flex-wrap">
                <a href="<?= APP_URL ?>/admin/perfil/<?= (int)$r['id_perfil'] ?>"
                   class="btn btn-sm btn-secondary" title="Vista admin del perfil">
                    <i class="bi bi-shield-check"></i>
                </a>
                <?php if ($r['perfil_estado'] === 'publicado'): ?>
                <a href="<?= APP_URL ?>/perfil/<?= (int)$r['id_perfil'] ?>"
                   target="_blank" rel="noopener"
                   class="btn btn-sm btn-outline-primary" title="Ver público (nueva pestaña)">
                    <i class="bi bi-box-arrow-up-right"></i>
                </a>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <div class="card-body">

            <!-- Motivo + estado + fecha -->
            <div class="d-flex justify-content-between flex-wrap gap-2 mb-2">
                <div>
                    <span class="badge-estado <?= $eCls ?>"><?= e($eLbl) ?></span>
                    <span class="ms-2" style="color:<?= $mColor ?>;font-size:.88rem;font-weight:600">
                        <i class="bi <?= $mIcon ?> me-1"></i><?= e($mLabel) ?>
                    </span>
                </div>
                <small class="text-muted" style="font-size:.75rem">
                    #<?= (int)$r['id'] ?> · <?= e(Security::timeAgo($r['fecha'])) ?>
                </small>
            </div>

            <!-- Descripción del reporte -->
            <?php if (!empty($r['descripcion'])): ?>
            <div class="p-2 rounded mb-2" style="background:var(--color-bg-alt);font-size:.88rem;white-space:pre-wrap">
                <?= nl2br(e($r['descripcion'])) ?>
            </div>
            <?php endif; ?>

            <!-- URL de referencia -->
            <?php if (!empty($r['url_referencia'])): ?>
            <div class="mb-2" style="font-size:.8rem">
                <i class="bi bi-link-45deg me-1"></i>
                URL de referencia:
                <a href="<?= e($r['url_referencia']) ?>" target="_blank" rel="noopener noreferrer"
                   style="color:var(--color-primary);word-break:break-all">
                    <?= e($r['url_referencia']) ?>
                </a>
            </div>
            <?php endif; ?>

            <!-- Reportero + IP -->
            <div class="d-flex flex-wrap gap-3 text-muted mb-3" style="font-size:.76rem">
                <?php if (!empty($r['usuario_nombre'])): ?>
                <span>
                    <i class="bi bi-person me-1"></i>Reportado por:
                    <a href="<?= APP_URL ?>/admin/usuario/<?= (int)$r['id_usuario'] ?>">
                        <strong><?= e($r['usuario_nombre']) ?></strong>
                    </a>
                    <?php if (!empty($r['usuario_email'])): ?>
                    &lt;<?= e($r['usuario_email']) ?>&gt;
                    <?php endif; ?>
                </span>
                <?php else: ?>
                <span><i class="bi bi-incognito me-1"></i>Reportero anónimo</span>
                <?php endif; ?>
                <?php if (!empty($r['ip_reporte'])): ?>
                <span style="font-family:monospace"><i class="bi bi-globe me-1"></i><?= e($r['ip_reporte']) ?></span>
                <?php endif; ?>
            </div>

            <!-- Nota admin editable -->
            <form method="POST" action="<?= APP_URL ?>/admin/reporte/<?= (int)$r['id'] ?>/nota" class="mb-3">
                <?= $csrfField ?>
                <div class="d-flex gap-2 align-items-end">
                    <div class="flex-grow-1">
                        <label class="form-label text-muted" style="font-size:.7rem;text-transform:uppercase;letter-spacing:.05em;font-weight:600">
                            <i class="bi bi-sticky me-1"></i>Nota interna (solo admin)
                        </label>
                        <textarea name="nota" class="form-control form-control-sm" rows="2"
                                  maxlength="1000" placeholder="Agrega notas internas sobre este caso..."><?= e($r['nota_admin'] ?? '') ?></textarea>
                    </div>
                    <button type="submit" class="btn btn-sm btn-secondary" title="Guardar nota">
                        <i class="bi bi-save"></i>
                    </button>
                </div>
            </form>

            <!-- Info de resolución (si ya está cerrado) -->
            <?php if (!$activo && (!empty($r['fecha_resolucion']) || !empty($r['id_admin_resolucion']))): ?>
            <div class="p-2 rounded mb-3" style="background:rgba(16,185,129,.06);border:1px solid rgba(16,185,129,.2);font-size:.78rem">
                <i class="bi bi-check-circle-fill text-success me-1"></i>
                <strong><?= ucfirst($r['estado']) ?></strong>
                <?php if (!empty($r['fecha_resolucion'])): ?>
                · <?= e(date('d/m/Y H:i', strtotime($r['fecha_resolucion']))) ?>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <!-- Acciones -->
            <?php if ($activo): ?>
            <div class="d-flex gap-2 flex-wrap">

                <?php if ($r['estado'] === 'pendiente'): ?>
                <form method="POST" action="<?= APP_URL ?>/admin/reporte/<?= (int)$r['id'] ?>/revisado" class="m-0">
                    <?= $csrfField ?>
                    <button type="submit" class="btn btn-sm btn-secondary">
                        <i class="bi bi-eye me-1"></i>Marcar en revisión
                    </button>
                </form>
                <?php endif; ?>

                <!-- Pedir más info -->
                <button type="button" class="btn btn-sm btn-outline-primary"
                        data-bs-toggle="collapse" data-bs-target="#r-info-<?= (int)$r['id'] ?>"
                        <?= empty($r['id_usuario']) ? 'disabled title="Reporte anónimo, no hay a quién pedirle"' : '' ?>>
                    <i class="bi bi-question-circle me-1"></i>Pedir más información
                </button>

                <!-- Rechazar -->
                <button type="button" class="btn btn-sm btn-secondary"
                        data-bs-toggle="collapse" data-bs-target="#r-rej-<?= (int)$r['id'] ?>">
                    <i class="bi bi-x-circle me-1"></i>Rechazar reporte
                </button>

                <!-- Resolver sin acción -->
                <form method="POST" action="<?= APP_URL ?>/admin/reporte/<?= (int)$r['id'] ?>/resolver" class="m-0"
                      onsubmit="return confirm('¿Marcar como resuelto sin tomar acción sobre el perfil?')">
                    <?= $csrfField ?>
                    <input type="hidden" name="accion" value="resolver">
                    <button type="submit" class="btn btn-sm btn-success">
                        <i class="bi bi-check-lg me-1"></i>Resolver
                    </button>
                </form>

                <div class="ms-auto d-flex gap-2 flex-wrap">
                    <?php if (!empty($r['id_perfil'])): ?>
                    <!-- Suspender cuenta del denunciado -->
                    <form method="POST" action="<?= APP_URL ?>/admin/reporte/<?= (int)$r['id'] ?>/suspender" class="m-0"
                          onsubmit="return confirm('¿Suspender la cuenta del denunciado? Dejará de poder publicar.')">
                        <?= $csrfField ?>
                        <button type="submit" class="btn btn-sm btn-outline-primary" style="border-color:var(--color-warning);color:#B45309">
                            <i class="bi bi-slash-circle me-1"></i>Suspender cuenta
                        </button>
                    </form>
                    <!-- Eliminar perfil reportado -->
                    <form method="POST" action="<?= APP_URL ?>/admin/reporte/<?= (int)$r['id'] ?>/eliminar-perfil" class="m-0"
                          onsubmit="return confirm('¿Eliminar permanentemente el perfil reportado? Esta acción no se puede deshacer.')">
                        <?= $csrfField ?>
                        <button type="submit" class="btn btn-sm btn-danger">
                            <i class="bi bi-trash me-1"></i>Eliminar perfil
                        </button>
                    </form>
                    <?php elseif (!empty($r['anuncio_titulo'])): ?>
                    <form method="POST" action="<?= APP_URL ?>/admin/reporte/<?= (int)$r['id'] ?>/resolver" class="m-0"
                          onsubmit="return confirm('¿Resolver el reporte Y eliminar el anuncio?')">
                        <?= $csrfField ?>
                        <input type="hidden" name="accion" value="eliminar_anuncio">
                        <button type="submit" class="btn btn-sm btn-danger">
                            <i class="bi bi-trash me-1"></i>Eliminar anuncio
                        </button>
                    </form>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Collapse: Pedir más información -->
            <div class="collapse mt-3" id="r-info-<?= (int)$r['id'] ?>">
                <form method="POST" action="<?= APP_URL ?>/admin/reporte/<?= (int)$r['id'] ?>/pedir-info"
                      class="p-3 rounded" style="background:rgba(59,130,246,.06);border:1px solid rgba(59,130,246,.25)">
                    <?= $csrfField ?>
                    <label class="form-label" style="font-size:.82rem;font-weight:600">
                        <i class="bi bi-chat-left-dots me-1"></i>¿Qué información adicional necesitas?
                    </label>
                    <textarea name="pregunta" class="form-control form-control-sm" rows="3"
                              required minlength="10" maxlength="500"
                              placeholder="Ej. ¿Puedes compartir capturas o URLs adicionales que respalden tu reporte?"></textarea>
                    <small class="text-muted" style="font-size:.72rem">
                        Se envía como notificación al reportero.
                    </small>
                    <div class="mt-2">
                        <button type="submit" class="btn btn-sm btn-primary">
                            <i class="bi bi-send me-1"></i>Enviar solicitud
                        </button>
                    </div>
                </form>
            </div>

            <!-- Collapse: Rechazar -->
            <div class="collapse mt-3" id="r-rej-<?= (int)$r['id'] ?>">
                <form method="POST" action="<?= APP_URL ?>/admin/reporte/<?= (int)$r['id'] ?>/rechazar"
                      class="p-3 rounded" style="background:rgba(239,68,68,.04);border:1px solid rgba(239,68,68,.2)">
                    <?= $csrfField ?>
                    <label class="form-label" style="font-size:.82rem;font-weight:600">
                        <i class="bi bi-x-circle me-1"></i>Motivo del rechazo (opcional)
                    </label>
                    <textarea name="motivo_rechazo" class="form-control form-control-sm" rows="2"
                              maxlength="500"
                              placeholder="El reportero verá este mensaje. Ej. No se encontró evidencia suficiente."></textarea>
                    <div class="mt-2">
                        <button type="submit" class="btn btn-sm btn-danger">
                            <i class="bi bi-x-circle me-1"></i>Rechazar reporte
                        </button>
                    </div>
                </form>
            </div>

            <?php else: ?>
            <div class="text-muted" style="font-size:.82rem">
                <i class="bi bi-lock me-1"></i>Reporte cerrado. No hay más acciones disponibles.
            </div>
            <?php endif; ?>

        </div>
    </div>
    <?php endforeach; ?>

    <!-- Paginación -->
    <?php
    $pages   = (int)($pagination['pages']   ?? 1);
    $current = (int)($pagination['current'] ?? 1);
    $total   = (int)($pagination['total']   ?? 0);
    $qsBase  = '?estado=' . urlencode($estado) . '&page=';
    ?>
    <?php if ($pages > 1): ?>
    <nav class="mt-4 d-flex justify-content-between flex-wrap gap-2 align-items-center">
        <small class="text-muted" style="font-size:.78rem">
            Página <?= $current ?> de <?= $pages ?> · <?= number_format($total) ?> reportes
        </small>
        <ul class="pagination pagination-sm mb-0 flex-wrap">
            <li class="page-item <?= $current <= 1 ? 'disabled' : '' ?>">
                <a class="page-link" href="<?= $qsBase . 1 ?>"><i class="bi bi-chevron-bar-left"></i></a>
            </li>
            <li class="page-item <?= $current <= 1 ? 'disabled' : '' ?>">
                <a class="page-link" href="<?= $qsBase . max(1, $current - 1) ?>"><i class="bi bi-chevron-left"></i></a>
            </li>
            <?php
            $start = max(1, $current - 2);
            $end   = min($pages, $current + 2);
            if ($start > 1): ?>
            <li class="page-item disabled"><span class="page-link">…</span></li>
            <?php endif; ?>
            <?php for ($p = $start; $p <= $end; $p++): ?>
            <li class="page-item <?= $p === $current ? 'active' : '' ?>">
                <a class="page-link" href="<?= $qsBase . $p ?>"><?= $p ?></a>
            </li>
            <?php endfor; ?>
            <?php if ($end < $pages): ?>
            <li class="page-item disabled"><span class="page-link">…</span></li>
            <?php endif; ?>
            <li class="page-item <?= $current >= $pages ? 'disabled' : '' ?>">
                <a class="page-link" href="<?= $qsBase . min($pages, $current + 1) ?>"><i class="bi bi-chevron-right"></i></a>
            </li>
            <li class="page-item <?= $current >= $pages ? 'disabled' : '' ?>">
                <a class="page-link" href="<?= $qsBase . $pages ?>"><i class="bi bi-chevron-bar-right"></i></a>
            </li>
        </ul>
    </nav>
    <?php endif; ?>

    <?php endif; ?>

</div>
