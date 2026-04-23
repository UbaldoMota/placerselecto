<?php
/** @var array $items */
/** @var array $pagination */
/** @var array $filtros */
/** @var array $stats */
?>
<div class="container py-4" style="max-width:1100px">

    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-4">
        <div>
            <h1 class="h4 fw-bold mb-1">
                <i class="bi bi-chat-square-text-fill text-primary me-2"></i>Moderación de comentarios
            </h1>
            <p class="text-muted mb-0" style="font-size:.88rem">
                <strong class="text-warning"><?= (int)($stats['pendientes'] ?? 0) ?> pendientes</strong> ·
                <?= (int)($stats['publicados'] ?? 0) ?> publicados ·
                <strong class="text-danger"><?= (int)($stats['reportados'] ?? 0) ?> reportados</strong> ·
                <?= (int)($stats['ocultos'] ?? 0) ?> ocultos
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
                        <?php foreach (['pendiente','publicado','reportado','oculto'] as $s): ?>
                        <option value="<?= $s ?>" <?= ($filtros['estado'] ?? '') === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Buscar</label>
                    <input type="text" name="q" class="form-control form-control-sm"
                           value="<?= e($filtros['buscar'] ?? '') ?>"
                           placeholder="comentario, email del autor o perfil">
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary btn-sm me-1">Filtrar</button>
                    <a href="<?= APP_URL ?>/admin/comentarios" class="btn btn-secondary btn-sm">Limpiar</a>
                </div>
            </form>
        </div>
    </div>

    <?php if (empty($items)): ?>
    <div class="card">
        <div class="card-body text-center text-muted py-5">
            <i class="bi bi-inbox" style="font-size:2rem"></i>
            <p class="mt-2 mb-0">No hay comentarios que coincidan.</p>
        </div>
    </div>
    <?php else: ?>

    <?php foreach ($items as $c):
        $badgeMap = [
            'pendiente' => ['badge-pendiente','Pendiente'],
            'publicado' => ['badge-publicado','Publicado'],
            'reportado' => ['badge-rechazado','Reportado'],
            'oculto'    => ['badge-expirado', 'Oculto'],
        ];
        [$cls, $lbl] = $badgeMap[$c['estado']] ?? ['','?'];
        $bLeft = match($c['estado']) {
            'pendiente' => 'var(--color-warning)',
            'reportado' => 'var(--color-danger)',
            default     => null,
        };
    ?>
    <?php
        $imgUrl = !empty($c['perfil_imagen']) ? APP_URL . '/img/' . $c['perfil_imagen'] . '?size=thumb' : null;
        $accionesCambio = $c['estado'] === 'pendiente';
    ?>
    <div class="card mb-3" <?= $bLeft ? 'style="border-left:3px solid ' . $bLeft . '"' : '' ?>>
        <!-- Perfil destinatario (destacado arriba) -->
        <div class="d-flex align-items-center gap-3 px-3 py-2" style="background:var(--color-bg-alt);border-bottom:1px solid var(--color-border)">
            <?php if ($imgUrl): ?>
            <img src="<?= e($imgUrl) ?>" alt=""
                 style="width:48px;height:48px;border-radius:8px;object-fit:cover;border:1px solid var(--color-border)">
            <?php else: ?>
            <div style="width:48px;height:48px;border-radius:8px;background:var(--color-bg-card);border:1px solid var(--color-border);display:flex;align-items:center;justify-content:center;color:var(--color-text-muted)">
                <i class="bi bi-person" style="font-size:1.4rem"></i>
            </div>
            <?php endif; ?>
            <div class="flex-grow-1" style="min-width:0">
                <div class="text-muted" style="font-size:.68rem;text-transform:uppercase;letter-spacing:.05em;font-weight:600">
                    <i class="bi bi-chat-left-dots-fill me-1"></i>Comentario sobre el perfil
                </div>
                <div class="fw-bold" style="font-size:.95rem;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">
                    <?= e($c['perfil_nombre']) ?>
                </div>
                <div class="text-muted" style="font-size:.72rem;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">
                    <?= e($c['perfil_categoria'] ?? '—') ?> · <?= e($c['perfil_municipio'] ?? $c['perfil_ciudad'] ?? '—') ?>
                    <span class="badge-estado badge-<?= $c['perfil_estado'] === 'publicado' ? 'publicado' : ($c['perfil_estado'] === 'pendiente' ? 'pendiente' : 'rechazado') ?> ms-1"><?= e($c['perfil_estado']) ?></span>
                </div>
            </div>
            <div class="d-flex gap-1 flex-shrink-0">
                <a href="<?= APP_URL ?>/admin/perfil/<?= (int)$c['id_perfil'] ?>"
                   class="btn btn-sm btn-secondary" title="Vista admin del perfil">
                    <i class="bi bi-shield-check"></i>
                </a>
                <a href="<?= APP_URL ?>/perfil/<?= (int)$c['id_perfil'] ?>#comentarios"
                   target="_blank" rel="noopener"
                   class="btn btn-sm btn-outline-primary" title="Ver perfil público">
                    <i class="bi bi-box-arrow-up-right"></i>
                </a>
            </div>
        </div>

        <!-- Comentario y autor -->
        <div class="card-body">
            <div class="d-flex justify-content-between flex-wrap gap-2 mb-2">
                <div>
                    <span class="text-muted" style="font-size:.75rem"><i class="bi bi-person-fill me-1"></i>Autor:</span>
                    <strong><?= e($c['autor_nombre']) ?></strong>
                    <small class="text-muted">&lt;<a href="<?= APP_URL ?>/admin/usuario/<?= (int)$c['id_usuario'] ?>"><?= e($c['autor_email']) ?></a>&gt;</small>
                </div>
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <span style="color:#F59E0B;font-size:.85rem">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <i class="bi <?= $i <= $c['calificacion'] ? 'bi-star-fill' : 'bi-star' ?>"></i>
                        <?php endfor; ?>
                    </span>
                    <span class="badge-estado <?= e($cls) ?>"><?= e($lbl) ?></span>
                    <small class="text-muted" style="font-size:.72rem"><?= e($c['fecha_creacion']) ?></small>
                </div>
            </div>
            <div class="p-2 rounded mb-2" style="background:var(--color-bg-alt);white-space:pre-wrap;font-size:.88rem">
                <?= nl2br(e($c['comentario'])) ?>
            </div>
            <div class="d-flex gap-2 flex-wrap">
                <?php if ($accionesCambio): ?>
                <form method="POST" action="<?= APP_URL ?>/admin/comentario/<?= (int)$c['id'] ?>/publicar" class="m-0">
                    <?= $csrfField ?>
                    <button type="submit" class="btn btn-sm btn-primary">
                        <i class="bi bi-check-lg me-1"></i>Aprobar y publicar
                    </button>
                </form>
                <?php elseif ($c['estado'] !== 'publicado'): ?>
                <form method="POST" action="<?= APP_URL ?>/admin/comentario/<?= (int)$c['id'] ?>/publicar" class="m-0">
                    <?= $csrfField ?>
                    <button type="submit" class="btn btn-sm btn-secondary">
                        <i class="bi bi-eye"></i> Restaurar
                    </button>
                </form>
                <?php endif; ?>
                <?php if ($c['estado'] !== 'oculto'): ?>
                <form method="POST" action="<?= APP_URL ?>/admin/comentario/<?= (int)$c['id'] ?>/ocultar" class="m-0">
                    <?= $csrfField ?>
                    <button type="submit" class="btn btn-sm btn-secondary">
                        <i class="bi bi-eye-slash"></i> Ocultar
                    </button>
                </form>
                <?php endif; ?>
                <form method="POST" action="<?= APP_URL ?>/admin/comentario/<?= (int)$c['id'] ?>/eliminar" class="m-0 ms-auto"
                      data-confirm-submit="¿Eliminar este comentario permanentemente?">
                    <?= $csrfField ?>
                    <button type="submit" class="btn btn-sm btn-danger">
                        <i class="bi bi-trash"></i> Eliminar
                    </button>
                </form>
            </div>
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
