<?php
/**
 * admin/ads.php — Gestión de anuncios.
 */
$estado = $filtros['estado'] ?? '';
$buscar = $filtros['buscar'] ?? '';
?>

<div class="container-fluid px-4 py-4">

    <!-- Encabezado -->
    <div class="d-flex flex-column flex-sm-row align-items-start align-items-sm-center justify-content-between gap-3 mb-4">
        <div>
            <h1 class="h4 fw-bold mb-1">
                <i class="bi bi-collection text-primary me-2"></i>Anuncios
            </h1>
            <p class="text-muted mb-0" style="font-size:.875rem">
                <?= number_format((int)($pagination['total'] ?? 0)) ?> resultado(s)
            </p>
        </div>
        <a href="<?= APP_URL ?>/admin" class="btn btn-sm btn-secondary">
            <i class="bi bi-arrow-left me-1"></i>Dashboard
        </a>
    </div>

    <!-- Filtros -->
    <div class="card mb-4">
        <div class="card-body py-2">
            <form class="d-flex flex-wrap gap-2 align-items-end" method="GET">
                <div>
                    <label class="form-label mb-1" style="font-size:.75rem">Buscar</label>
                    <input type="search" name="q" class="form-control form-control-sm"
                           placeholder="Título o usuario..." value="<?= e($buscar) ?>" style="width:220px">
                </div>
                <div>
                    <label class="form-label mb-1" style="font-size:.75rem">Estado</label>
                    <select name="estado" class="form-select form-select-sm" style="width:160px">
                        <option value="">Todos</option>
                        <option value="pendiente"  <?= $estado==='pendiente'  ? 'selected':'' ?>>En revisión</option>
                        <option value="publicado"  <?= $estado==='publicado'  ? 'selected':'' ?>>Publicados</option>
                        <option value="rechazado"  <?= $estado==='rechazado'  ? 'selected':'' ?>>Rechazados</option>
                        <option value="expirado"   <?= $estado==='expirado'   ? 'selected':'' ?>>Expirados</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-sm btn-primary">
                    <i class="bi bi-search me-1"></i>Filtrar
                </button>
                <?php if ($buscar || $estado): ?>
                <a href="<?= APP_URL ?>/admin/anuncios" class="btn btn-sm btn-secondary">
                    <i class="bi bi-x-circle me-1"></i>Limpiar
                </a>
                <?php endif; ?>
            </form>
        </div>
    </div>

    <!-- Tabla -->
    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table mb-0">
                    <thead>
                        <tr>
                            <th style="width:52px"></th>
                            <th>Anuncio</th>
                            <th>Usuario</th>
                            <th>Estado</th>
                            <th class="d-none d-md-table-cell">Ciudad</th>
                            <th class="d-none d-lg-table-cell">Vistas</th>
                            <th class="d-none d-xl-table-cell">Fecha</th>
                            <th class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (empty($anuncios)): ?>
                        <tr><td colspan="8" class="text-center text-muted py-5">No se encontraron anuncios.</td></tr>
                    <?php else: ?>
                    <?php foreach ($anuncios as $ad): ?>
                    <tr>
                        <!-- Miniatura -->
                        <td>
                            <?php $imgUrl = Security::imgUrl($ad); ?>
                            <?php if ($imgUrl): ?>
                            <img src="<?= e($imgUrl) ?>"
                                 alt=""
                                 style="width:44px;height:44px;object-fit:cover;border-radius:6px;border:1px solid var(--color-border)">
                            <?php else: ?>
                            <div style="width:44px;height:44px;border-radius:6px;background:var(--color-bg-card2);border:1px solid var(--color-border);display:flex;align-items:center;justify-content:center;color:var(--color-text-muted)">
                                <i class="bi bi-image"></i>
                            </div>
                            <?php endif; ?>
                        </td>

                        <!-- Título -->
                        <td>
                            <div class="fw-semibold text-truncate" style="max-width:200px;font-size:.875rem">
                                <?= e($ad['titulo']) ?>
                            </div>
                            <div class="text-muted" style="font-size:.73rem">
                                <?= e($ad['categoria_nombre'] ?? '—') ?>
                                <?php if ($ad['destacado']): ?>
                                <span class="ms-1 badge-estado badge-destacado" style="font-size:.62rem">
                                    <i class="bi bi-star-fill"></i> Top
                                </span>
                                <?php endif; ?>
                            </div>
                        </td>

                        <!-- Usuario -->
                        <td>
                            <a href="<?= APP_URL ?>/admin/usuario/<?= (int)$ad['id_usuario'] ?>"
                               style="font-size:.83rem;color:var(--color-primary)">
                                <?= e($ad['usuario_nombre'] ?? '—') ?>
                            </a>
                            <div class="text-muted" style="font-size:.72rem"><?= e($ad['usuario_email'] ?? '') ?></div>
                        </td>

                        <!-- Estado -->
                        <td>
                            <?php
                            $sMap = [
                                'pendiente' => ['badge-pendiente','hourglass-split','En revisión'],
                                'publicado' => ['badge-publicado','check-circle','Publicado'],
                                'rechazado' => ['badge-rechazado','x-circle','Rechazado'],
                                'expirado'  => ['badge-expirado','calendar-x','Expirado'],
                            ];
                            [$sc,$si,$sl] = $sMap[$ad['estado']] ?? ['','question','?'];
                            ?>
                            <span class="badge-estado <?= $sc ?>">
                                <i class="bi bi-<?= $si ?> me-1"></i><?= $sl ?>
                            </span>
                        </td>

                        <td class="d-none d-md-table-cell text-muted" style="font-size:.82rem">
                            <i class="bi bi-geo-alt me-1 text-primary"></i><?= e($ad['ciudad']) ?>
                        </td>
                        <td class="d-none d-lg-table-cell text-muted" style="font-size:.82rem">
                            <i class="bi bi-eye me-1"></i><?= number_format((int)$ad['vistas']) ?>
                        </td>
                        <td class="d-none d-xl-table-cell text-muted" style="font-size:.78rem;white-space:nowrap">
                            <?= e(Security::timeAgo($ad['fecha_creacion'])) ?>
                        </td>

                        <!-- Acciones -->
                        <td class="text-end">
                            <div class="d-flex gap-1 justify-content-end">
                                <?php if ($ad['estado'] === 'publicado'): ?>
                                <a href="<?= APP_URL ?>/anuncio/<?= (int)$ad['id'] ?>"
                                   class="btn btn-sm btn-secondary" title="Ver público" target="_blank">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <?php endif; ?>
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
                                            data-confirm="¿Eliminar «<?= e($ad['titulo']) ?>»? Esta acción es irreversible.">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Paginación -->
    <?php if (($pagination['pages'] ?? 1) > 1): ?>
    <nav class="mt-4 d-flex justify-content-center">
        <ul class="pagination">
            <li class="page-item <?= $pagination['current'] <= 1 ? 'disabled' : '' ?>">
                <a class="page-link" href="?page=<?= $pagination['current']-1 ?>&estado=<?= e($estado) ?>&q=<?= e($buscar) ?>">
                    <i class="bi bi-chevron-left"></i>
                </a>
            </li>
            <?php for ($p = 1; $p <= $pagination['pages']; $p++): ?>
            <li class="page-item <?= $p === (int)$pagination['current'] ? 'active' : '' ?>">
                <a class="page-link" href="?page=<?= $p ?>&estado=<?= e($estado) ?>&q=<?= e($buscar) ?>"><?= $p ?></a>
            </li>
            <?php endfor; ?>
            <li class="page-item <?= $pagination['current'] >= $pagination['pages'] ? 'disabled' : '' ?>">
                <a class="page-link" href="?page=<?= $pagination['current']+1 ?>&estado=<?= e($estado) ?>&q=<?= e($buscar) ?>">
                    <i class="bi bi-chevron-right"></i>
                </a>
            </li>
        </ul>
    </nav>
    <?php endif; ?>

</div>
