<?php
/** @var array $mensajes */
/** @var array $pagination */
/** @var array $filtros */
/** @var int   $abiertos */
?>
<div class="container py-4" style="max-width:1100px">

    <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
        <div>
            <h1 class="h4 fw-bold mb-1">
                <i class="bi bi-envelope-fill text-primary me-2"></i>Mensajes de soporte
            </h1>
            <p class="text-muted mb-0" style="font-size:.88rem">
                Solicitudes de reactivación y consultas de usuarios.
                <?php if ($abiertos > 0): ?>
                    <strong class="text-warning"><?= (int)$abiertos ?> abierto(s)</strong>
                <?php endif; ?>
            </p>
        </div>
        <a href="<?= APP_URL ?>/admin" class="btn btn-sm btn-secondary">
            <i class="bi bi-arrow-left me-1"></i>Panel
        </a>
    </div>

    <!-- Filtros -->
    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-md-3">
                    <label class="form-label">Estado</label>
                    <select name="estado" class="form-select form-select-sm">
                        <option value="">Todos</option>
                        <?php foreach (['abierto','respondido','cerrado'] as $s): ?>
                        <option value="<?= $s ?>" <?= ($filtros['estado'] ?? '') === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Tipo</label>
                    <select name="tipo" class="form-select form-select-sm">
                        <option value="">Todos</option>
                        <?php foreach (['reactivacion','general','duda','reporte_problema'] as $t): ?>
                        <option value="<?= $t ?>" <?= ($filtros['tipo'] ?? '') === $t ? 'selected' : '' ?>><?= ucfirst(str_replace('_',' ', $t)) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Buscar</label>
                    <input type="text" name="q" class="form-control form-control-sm"
                           value="<?= e($filtros['buscar'] ?? '') ?>"
                           placeholder="asunto, mensaje o email">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary btn-sm me-1">Filtrar</button>
                    <a href="<?= APP_URL ?>/admin/mensajes" class="btn btn-secondary btn-sm">Limpiar</a>
                </div>
            </form>
        </div>
    </div>

    <?php if (empty($mensajes)): ?>
        <div class="card">
            <div class="card-body text-center text-muted py-5">
                <i class="bi bi-inbox" style="font-size:2rem"></i>
                <p class="mt-2 mb-0">No hay mensajes que coincidan.</p>
            </div>
        </div>
    <?php else: ?>

    <!-- Lista -->
    <?php foreach ($mensajes as $m):
        $abierto = $m['estado'] === 'abierto';
        $esReactivacion = $m['tipo'] === 'reactivacion';
        $usuarioBloqueado = in_array($m['usuario_estado'] ?? '', ['rechazado', 'suspendido'], true);
        $estadoMap = [
            'abierto'    => ['badge-pendiente', 'clock-history', 'Abierto'],
            'respondido' => ['badge-publicado', 'check-circle',  'Respondido'],
            'cerrado'    => ['badge-expirado',  'x-circle',      'Cerrado'],
        ];
        [$cls, $icono, $lbl] = $estadoMap[$m['estado']] ?? ['', '', $m['estado']];
    ?>
    <div class="card mb-3" <?= $abierto ? 'style="border-left:3px solid var(--color-warning)"' : '' ?>>
        <div class="card-body">

            <!-- Header -->
            <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-2">
                <div>
                    <h3 class="h6 fw-bold mb-1"><?= e($m['asunto']) ?></h3>
                    <div class="text-muted" style="font-size:.82rem">
                        De: <strong><?= e($m['usuario_nombre'] ?? 'Usuario') ?></strong>
                        &lt;<a href="<?= APP_URL ?>/admin/usuario/<?= (int)$m['id_usuario'] ?>"><?= e($m['usuario_email'] ?? '') ?></a>&gt;
                        <span class="ms-2">· <?= e(date('d/m/Y H:i', strtotime($m['fecha_creacion']))) ?></span>
                    </div>
                </div>
                <div class="d-flex gap-2 align-items-center flex-wrap">
                    <?php if ($esReactivacion): ?>
                    <span class="badge-estado badge-destacado">
                        <i class="bi bi-arrow-clockwise me-1"></i>Reactivación
                    </span>
                    <?php else: ?>
                    <span class="badge-estado badge-expirado"><?= e(str_replace('_',' ', $m['tipo'])) ?></span>
                    <?php endif; ?>
                    <span class="badge-estado <?= e($cls) ?>">
                        <i class="bi bi-<?= e($icono) ?> me-1"></i><?= e($lbl) ?>
                    </span>
                    <?php if ($usuarioBloqueado): ?>
                    <span class="badge-estado badge-rechazado">
                        <i class="bi bi-slash-circle me-1"></i>
                        Cuenta <?= e($m['usuario_estado']) ?>
                    </span>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Mensaje -->
            <div class="p-3 rounded mb-3" style="background:var(--color-bg-alt)">
                <p class="mb-0" style="white-space:pre-wrap;font-size:.9rem"><?= nl2br(e($m['mensaje'])) ?></p>
            </div>

            <!-- Respuesta existente -->
            <?php if (!empty($m['respuesta_admin'])): ?>
            <div class="p-3 rounded mb-3" style="background:rgba(255,45,117,.05);border-left:3px solid var(--color-primary)">
                <div class="text-muted mb-1" style="font-size:.72rem;text-transform:uppercase;letter-spacing:.05em">
                    <i class="bi bi-shield-check me-1"></i>Respuesta admin
                    <span class="ms-2"><?= e(date('d/m/Y H:i', strtotime($m['fecha_respuesta']))) ?></span>
                </div>
                <p class="mb-0" style="white-space:pre-wrap;font-size:.9rem"><?= nl2br(e($m['respuesta_admin'])) ?></p>
            </div>
            <?php endif; ?>

            <!-- Acciones -->
            <?php if ($abierto): ?>
            <div class="border-top pt-3" style="border-color:var(--color-border) !important">
                <form method="POST" action="<?= APP_URL ?>/admin/mensaje/<?= (int)$m['id'] ?>/responder">
                    <?= $csrfField ?>
                    <div class="mb-2">
                        <label class="form-label">Tu respuesta</label>
                        <textarea name="respuesta" class="form-control" rows="3" required minlength="5" maxlength="5000"
                                  placeholder="Responde al usuario..."></textarea>
                    </div>

                    <?php if ($esReactivacion && $usuarioBloqueado): ?>
                    <div class="mb-2 form-check">
                        <input type="checkbox" name="aprobar_reactivacion" class="form-check-input"
                               id="aprobar-<?= (int)$m['id'] ?>" value="1">
                        <label class="form-check-label" for="aprobar-<?= (int)$m['id'] ?>">
                            <strong class="text-success">Aprobar reactivación de cuenta</strong>
                            <small class="text-muted d-block">
                                Si se marca: la cuenta pasa a <code>aprobado</code> y el usuario podrá crear perfiles.
                            </small>
                        </label>
                    </div>
                    <?php endif; ?>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary btn-sm">
                            <i class="bi bi-send me-1"></i>Enviar respuesta
                        </button>
                    </div>
                </form>

                <form method="POST" action="<?= APP_URL ?>/admin/mensaje/<?= (int)$m['id'] ?>/cerrar" class="mt-2"
                      data-confirm-submit="¿Cerrar este mensaje sin responder?">
                    <?= $csrfField ?>
                    <button type="submit" class="btn btn-sm btn-link text-muted p-0" style="font-size:.78rem;text-decoration:none">
                        <i class="bi bi-x-circle me-1"></i>Cerrar sin responder
                    </button>
                </form>
            </div>
            <?php endif; ?>

        </div>
    </div>
    <?php endforeach; ?>

    <?php if (($pagination['pages'] ?? 1) > 1): ?>
    <nav class="mt-3 d-flex justify-content-center">
        <ul class="pagination pagination-sm mb-0">
            <?php $qs = http_build_query(array_filter($filtros));
            for ($p = 1; $p <= $pagination['pages']; $p++): ?>
            <li class="page-item <?= $p === $pagination['current'] ? 'active' : '' ?>">
                <a class="page-link" href="?page=<?= $p ?>&<?= $qs ?>"><?= $p ?></a>
            </li>
            <?php endfor; ?>
        </ul>
    </nav>
    <?php endif; ?>

    <?php endif; ?>
</div>
