<?php
/** @var array $mensajes  */
/** @var array $pagination */
/** @var array $filtros   */
/** @var int   $noLeidos  */
?>
<div class="container py-4" style="max-width:1100px">

    <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
        <div>
            <h1 class="h4 fw-bold mb-1">
                <i class="bi bi-envelope-paper-fill text-primary me-2"></i>Mensajes de contacto público
            </h1>
            <p class="text-muted mb-0" style="font-size:.88rem">
                Mensajes recibidos a través del formulario público <code>/contacto</code>.
                <?php if ($noLeidos > 0): ?>
                    <strong class="text-warning"><?= (int)$noLeidos ?> sin leer</strong>
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
                    <select name="leido" class="form-select form-select-sm">
                        <option value="">Todos</option>
                        <option value="0" <?= ($filtros['leido'] ?? '') === '0' ? 'selected' : '' ?>>No leídos</option>
                        <option value="1" <?= ($filtros['leido'] ?? '') === '1' ? 'selected' : '' ?>>Leídos</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Tipo</label>
                    <select name="asunto" class="form-select form-select-sm">
                        <option value="">Todos</option>
                        <?php foreach (ContactoMensajeModel::ASUNTOS_LABELS as $key => $label): ?>
                        <option value="<?= e($key) ?>" <?= ($filtros['asunto'] ?? '') === $key ? 'selected' : '' ?>><?= e($label) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Buscar</label>
                    <input type="text" name="q" class="form-control form-control-sm"
                           value="<?= e($filtros['buscar'] ?? '') ?>"
                           placeholder="nombre, email o mensaje">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary btn-sm me-1">Filtrar</button>
                    <a href="<?= APP_URL ?>/admin/contactos" class="btn btn-secondary btn-sm">Limpiar</a>
                </div>
            </form>
        </div>
    </div>

    <?php if (empty($mensajes)): ?>
        <div class="card">
            <div class="card-body text-center text-muted py-5">
                <i class="bi bi-inbox" style="font-size:2rem"></i>
                <p class="mt-2 mb-0">No hay mensajes que coincidan con los filtros.</p>
            </div>
        </div>
    <?php else: ?>

    <?php foreach ($mensajes as $m):
        $noLeido = empty($m['leido']);
        $asuntoLabel = ContactoMensajeModel::ASUNTOS_LABELS[$m['asunto']] ?? $m['asunto'];
    ?>
    <div class="card mb-3" <?= $noLeido ? 'style="border-left:3px solid var(--color-warning)"' : '' ?>>
        <div class="card-body">

            <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-2">
                <div class="flex-grow-1">
                    <div class="d-flex align-items-center gap-2 flex-wrap mb-1">
                        <h3 class="h6 fw-bold mb-0"><?= e($m['nombre']) ?></h3>
                        <?php if ($noLeido): ?>
                        <span class="badge bg-warning bg-opacity-25 text-warning border border-warning border-opacity-25" style="font-size:.7rem">
                            <i class="bi bi-circle-fill me-1" style="font-size:.5rem"></i>Sin leer
                        </span>
                        <?php endif; ?>
                        <span class="badge bg-primary bg-opacity-10 text-primary" style="font-size:.7rem">
                            <?= e($asuntoLabel) ?>
                        </span>
                    </div>
                    <div class="text-muted" style="font-size:.82rem">
                        <a href="mailto:<?= e($m['email']) ?>"><?= e($m['email']) ?></a>
                        <span class="ms-2">· <?= e(date('d/m/Y H:i', strtotime($m['fecha_creacion']))) ?></span>
                        <?php if (!empty($m['ip'])): ?>
                        <span class="ms-2 text-muted-2" style="font-size:.75rem">IP: <?= e($m['ip']) ?></span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <p class="mb-3" style="font-size:.875rem;line-height:1.7;color:var(--color-text);white-space:pre-line">
                <?= e(mb_strimwidth($m['mensaje'], 0, 280, '…')) ?>
            </p>

            <div class="d-flex gap-2 flex-wrap">
                <a href="<?= APP_URL ?>/admin/contactos/<?= (int)$m['id'] ?>" class="btn btn-sm btn-primary">
                    <i class="bi bi-eye me-1"></i>Ver mensaje completo
                </a>
                <a href="mailto:<?= e($m['email']) ?>?subject=Re:%20<?= urlencode($asuntoLabel) ?>" class="btn btn-sm btn-secondary">
                    <i class="bi bi-reply me-1"></i>Responder por correo
                </a>
                <form method="POST" action="<?= APP_URL ?>/admin/contactos/<?= (int)$m['id'] ?>/leido" class="d-inline">
                    <?= $csrfField ?>
                    <input type="hidden" name="leido" value="<?= $noLeido ? '1' : '0' ?>">
                    <button type="submit" class="btn btn-sm btn-outline-secondary">
                        <i class="bi <?= $noLeido ? 'bi-check2' : 'bi-arrow-counterclockwise' ?> me-1"></i>
                        <?= $noLeido ? 'Marcar como leído' : 'Marcar como no leído' ?>
                    </button>
                </form>
                <form method="POST" action="<?= APP_URL ?>/admin/contactos/<?= (int)$m['id'] ?>/eliminar" class="d-inline"
                      data-confirm-click="¿Eliminar este mensaje permanentemente?">
                    <?= $csrfField ?>
                    <button type="submit" class="btn btn-sm btn-outline-danger">
                        <i class="bi bi-trash me-1"></i>Eliminar
                    </button>
                </form>
            </div>
        </div>
    </div>
    <?php endforeach; ?>

    <!-- Paginación -->
    <?php if (!empty($pagination) && $pagination['pages'] > 1): ?>
    <nav class="mt-4">
        <ul class="pagination pagination-sm justify-content-center">
            <?php for ($p = 1; $p <= $pagination['pages']; $p++):
                $qs = $_GET; $qs['page'] = $p;
            ?>
            <li class="page-item <?= $p == $pagination['current'] ? 'active' : '' ?>">
                <a class="page-link" href="?<?= http_build_query($qs) ?>"><?= $p ?></a>
            </li>
            <?php endfor; ?>
        </ul>
    </nav>
    <?php endif; ?>

    <?php endif; ?>

</div>
