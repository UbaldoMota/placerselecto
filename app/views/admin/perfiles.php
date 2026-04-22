<?php
/**
 * admin/perfiles.php — Gestión de perfiles de anunciantes.
 */
$estado = $filtros['estado'] ?? '';
$buscar = $filtros['buscar'] ?? '';
?>

<div class="container-fluid px-4 py-4">

    <!-- Encabezado -->
    <div class="d-flex flex-column flex-sm-row align-items-start align-items-sm-center justify-content-between gap-3 mb-4">
        <div>
            <h1 class="h4 fw-bold mb-1">
                <i class="bi bi-person-lines-fill text-primary me-2"></i>Perfiles
            </h1>
            <p class="text-muted mb-0" style="font-size:.875rem">
                <?= number_format((int)($pagination['total'] ?? 0)) ?> resultado(s)
                <?php if (!empty($stats)): ?>
                &mdash;
                <span style="font-size:.8rem">
                    Pendientes: <strong><?= (int)$stats['pendientes'] ?></strong> &nbsp;
                    Publicados: <strong><?= (int)$stats['publicados'] ?></strong> &nbsp;
                    Rechazados: <strong><?= (int)$stats['rechazados'] ?></strong>
                </span>
                <?php endif; ?>
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
                           placeholder="Nombre o email..." value="<?= e($buscar) ?>" style="width:220px">
                </div>
                <div>
                    <label class="form-label mb-1" style="font-size:.75rem">Estado</label>
                    <select name="estado" class="form-select form-select-sm" style="width:160px">
                        <option value="">Todos</option>
                        <option value="pendiente" <?= $estado==='pendiente' ? 'selected':'' ?>>En revisión</option>
                        <option value="publicado" <?= $estado==='publicado' ? 'selected':'' ?>>Publicados</option>
                        <option value="rechazado" <?= $estado==='rechazado' ? 'selected':'' ?>>Rechazados</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-sm btn-primary">
                    <i class="bi bi-search me-1"></i>Filtrar
                </button>
                <?php if ($buscar || $estado): ?>
                <a href="<?= APP_URL ?>/admin/perfiles" class="btn btn-sm btn-secondary">
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
                            <th>Perfil</th>
                            <th>Usuario</th>
                            <th>Estado</th>
                            <th class="d-none d-md-table-cell">Ubicación</th>
                            <th class="d-none d-lg-table-cell">Vistas</th>
                            <th class="d-none d-xl-table-cell">Fecha</th>
                            <th class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (empty($perfiles)): ?>
                        <tr><td colspan="8" class="text-center text-muted py-5">No se encontraron perfiles.</td></tr>
                    <?php else: ?>
                    <?php foreach ($perfiles as $p):
                        $imgUrl = !empty($p['imagen_token'])
                            ? APP_URL . '/img/' . $p['imagen_token']
                            : null;
                    ?>
                    <tr>
                        <!-- Miniatura -->
                        <td>
                            <?php if ($imgUrl): ?>
                            <img src="<?= e($imgUrl) ?>"
                                 alt=""
                                 style="width:44px;height:44px;object-fit:cover;border-radius:6px;border:1px solid var(--color-border)">
                            <?php else: ?>
                            <div style="width:44px;height:44px;border-radius:6px;background:var(--color-bg-card2);border:1px solid var(--color-border);display:flex;align-items:center;justify-content:center;color:var(--color-text-muted)">
                                <i class="bi bi-person"></i>
                            </div>
                            <?php endif; ?>
                        </td>

                        <!-- Nombre -->
                        <td>
                            <div class="fw-semibold text-truncate" style="max-width:200px;font-size:.875rem">
                                <?= e($p['nombre']) ?>
                                <?php if (!empty($p['edad'])): ?>
                                <span class="text-muted fw-normal">, <?= (int)$p['edad'] ?></span>
                                <?php endif; ?>
                            </div>
                            <div class="text-muted" style="font-size:.73rem">
                                <?= e($p['categoria_nombre'] ?? '—') ?>
                                <?php if ($p['destacado']): ?>
                                <span class="ms-1 badge-estado badge-destacado" style="font-size:.62rem">
                                    <i class="bi bi-star-fill"></i> Top
                                </span>
                                <?php endif; ?>
                            </div>
                        </td>

                        <!-- Usuario -->
                        <td>
                            <a href="<?= APP_URL ?>/admin/usuario/<?= (int)$p['id_usuario'] ?>"
                               style="font-size:.83rem;color:var(--color-primary)">
                                <?= e($p['usuario_nombre'] ?? '—') ?>
                            </a>
                            <div class="text-muted" style="font-size:.72rem"><?= e($p['usuario_email'] ?? '') ?></div>
                        </td>

                        <!-- Estado + verificación -->
                        <td>
                            <?php
                            $sMap = [
                                'pendiente' => ['badge-pendiente','hourglass-split','En revisión'],
                                'publicado' => ['badge-publicado','check-circle','Publicado'],
                                'rechazado' => ['badge-rechazado','x-circle','Rechazado'],
                            ];
                            [$sc,$si,$sl] = $sMap[$p['estado']] ?? ['','question','?'];
                            $pFotos = (int)$p['fotos_ver'] > 0;
                            $pVideo = !empty($p['video_verificacion']);
                            $verCompleta = $pFotos && $pVideo;
                            ?>
                            <span class="badge-estado <?= $sc ?>">
                                <i class="bi bi-<?= $si ?> me-1"></i><?= $sl ?>
                            </span>
                            <br>
                            <?php if ($verCompleta): ?>
                            <span class="badge-estado" style="background:rgba(16,185,129,.12);color:#10B981;font-size:.68rem;margin-top:.25rem">
                                <i class="bi bi-shield-check me-1"></i>Verificación completa
                            </span>
                            <?php elseif ($pFotos || $pVideo): ?>
                            <span class="badge-estado" style="background:rgba(255,193,7,.1);color:#c8a000;font-size:.68rem;margin-top:.25rem">
                                <i class="bi bi-shield-half me-1"></i>Ver. incompleta
                                (falta <?= !$pFotos ? 'fotos' : 'video' ?>)
                            </span>
                            <?php else: ?>
                            <span class="badge-estado" style="background:rgba(220,53,69,.08);color:#c0392b;font-size:.68rem;margin-top:.25rem">
                                <i class="bi bi-shield-x me-1"></i>Sin verificación
                            </span>
                            <?php endif; ?>
                        </td>

                        <td class="d-none d-md-table-cell text-muted" style="font-size:.82rem">
                            <i class="bi bi-geo-alt me-1 text-primary"></i>
                            <?= e($p['municipio_nombre'] ?? $p['ciudad'] ?? '—') ?>
                        </td>
                        <td class="d-none d-lg-table-cell text-muted" style="font-size:.82rem">
                            <i class="bi bi-eye me-1"></i><?= number_format((int)$p['vistas']) ?>
                        </td>
                        <td class="d-none d-xl-table-cell text-muted" style="font-size:.78rem;white-space:nowrap">
                            <?= e(Security::timeAgo($p['fecha_creacion'])) ?>
                        </td>

                        <!-- Acciones -->
                        <td class="text-end">
                            <div class="d-flex gap-1 justify-content-end">
                                <!-- Preview admin (siempre visible) -->
                                <a href="<?= APP_URL ?>/admin/perfil/<?= (int)$p['id'] ?>"
                                   class="btn btn-sm btn-secondary" title="Previsualizar">
                                    <i class="bi bi-eye"></i>
                                </a>

                                <?php if ($p['estado'] === 'publicado'): ?>
                                <a href="<?= APP_URL ?>/perfil/<?= (int)$p['id'] ?>"
                                   class="btn btn-sm btn-secondary" title="Ver página pública" target="_blank">
                                    <i class="bi bi-box-arrow-up-right"></i>
                                </a>
                                <?php endif; ?>

                                <?php if ($p['estado'] === 'pendiente'): ?>
                                <form method="POST" action="<?= APP_URL ?>/admin/perfil/<?= (int)$p['id'] ?>/publicar">
                                    <?= $csrfField ?>
                                    <button class="btn btn-sm" title="Publicar"
                                            style="background:rgba(16,185,129,.1);color:#10B981;border:1px solid rgba(16,185,129,.25)">
                                        <i class="bi bi-check-lg"></i>
                                    </button>
                                </form>
                                <form method="POST" action="<?= APP_URL ?>/admin/perfil/<?= (int)$p['id'] ?>/rechazar">
                                    <?= $csrfField ?>
                                    <button class="btn btn-sm" title="Rechazar"
                                            style="background:rgba(220,53,69,.1);color:#dc3545;border:1px solid rgba(220,53,69,.25)">
                                        <i class="bi bi-x-lg"></i>
                                    </button>
                                </form>
                                <?php elseif ($p['estado'] === 'rechazado'): ?>
                                <form method="POST" action="<?= APP_URL ?>/admin/perfil/<?= (int)$p['id'] ?>/publicar">
                                    <?= $csrfField ?>
                                    <button class="btn btn-sm" title="Publicar igualmente"
                                            style="background:rgba(16,185,129,.1);color:#10B981;border:1px solid rgba(16,185,129,.25)">
                                        <i class="bi bi-check-lg"></i>
                                    </button>
                                </form>
                                <?php endif; ?>

                                <form method="POST" action="<?= APP_URL ?>/admin/perfil/<?= (int)$p['id'] ?>/eliminar">
                                    <?= $csrfField ?>
                                    <button class="btn btn-sm btn-danger" title="Eliminar"
                                            data-confirm="¿Eliminar el perfil «<?= e($p['nombre']) ?>»? Esta acción es irreversible.">
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
            <?php for ($pg = 1; $pg <= $pagination['pages']; $pg++): ?>
            <li class="page-item <?= $pg === (int)$pagination['current'] ? 'active' : '' ?>">
                <a class="page-link" href="?page=<?= $pg ?>&estado=<?= e($estado) ?>&q=<?= e($buscar) ?>"><?= $pg ?></a>
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
