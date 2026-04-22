<?php
/**
 * my-ads.php — Listado completo de anuncios del usuario con paginación.
 */
?>

<div class="container py-4">

    <!-- Encabezado -->
    <div class="d-flex flex-column flex-sm-row align-items-start align-items-sm-center justify-content-between gap-3 mb-4">
        <div>
            <h1 class="h4 fw-bold mb-1">
                <i class="bi bi-collection text-primary me-2"></i>Mis anuncios
            </h1>
            <p class="text-muted mb-0" style="font-size:.875rem">
                <?= (int)($pagination['total'] ?? 0) ?> anuncio(s) en total
            </p>
        </div>
        <a href="<?= APP_URL ?>/anuncio/crear" class="btn btn-primary d-flex align-items-center gap-2">
            <i class="bi bi-plus-lg"></i>Nuevo anuncio
        </a>
    </div>

    <?php if (empty($anuncios)): ?>
    <!-- Estado vacío -->
    <div class="text-center py-5">
        <i class="bi bi-inbox" style="font-size:4rem;color:var(--color-border)"></i>
        <h3 class="h5 mt-3 mb-2">Aún no tienes anuncios</h3>
        <p class="text-muted mb-4">Publica tu primer anuncio y llega a miles de personas.</p>
        <a href="<?= APP_URL ?>/anuncio/crear" class="btn btn-primary btn-lg">
            <i class="bi bi-plus-lg me-2"></i>Publicar anuncio
        </a>
    </div>

    <?php else: ?>

    <!-- Tabla de anuncios -->
    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table mb-0">
                    <thead>
                        <tr>
                            <th style="width:60px"></th>
                            <th>Anuncio</th>
                            <th>Estado</th>
                            <th class="d-none d-md-table-cell">Ciudad</th>
                            <th class="d-none d-lg-table-cell">Vistas</th>
                            <th class="d-none d-sm-table-cell">Fecha</th>
                            <th class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($anuncios as $ad): ?>
                        <tr>
                            <!-- Miniatura -->
                            <td>
                                <?php $imgUrl = Security::imgUrl($ad); ?>
                                <?php if ($imgUrl): ?>
                                    <img src="<?= e($imgUrl) ?>"
                                         alt=""
                                         loading="lazy"
                                         style="width:48px;height:48px;object-fit:cover;border-radius:6px;border:1px solid var(--color-border)">
                                <?php else: ?>
                                    <div style="width:48px;height:48px;border-radius:6px;background:var(--color-bg-card2);border:1px solid var(--color-border);display:flex;align-items:center;justify-content:center;color:var(--color-text-muted)">
                                        <i class="bi bi-image"></i>
                                    </div>
                                <?php endif; ?>
                            </td>

                            <!-- Título y categoría -->
                            <td>
                                <div class="fw-semibold" style="font-size:.9rem">
                                    <?= e(Security::truncate($ad['titulo'], 50)) ?>
                                </div>
                                <div class="text-muted" style="font-size:.75rem">
                                    <?= e($ad['categoria_nombre'] ?? '—') ?>
                                    <?php if ($ad['destacado']): ?>
                                        <span class="ms-2 badge-estado badge-destacado">
                                            <i class="bi bi-star-fill me-1" style="font-size:.6rem"></i>Destacado
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </td>

                            <!-- Estado -->
                            <td>
                                <?php
                                $estadoInfo = [
                                    'pendiente' => ['clase' => 'badge-pendiente',  'icono' => 'bi-clock',        'label' => 'En revisión'],
                                    'publicado' => ['clase' => 'badge-publicado',  'icono' => 'bi-check-circle', 'label' => 'Publicado'],
                                    'rechazado' => ['clase' => 'badge-rechazado',  'icono' => 'bi-x-circle',     'label' => 'Rechazado'],
                                    'expirado'  => ['clase' => 'badge-expirado',   'icono' => 'bi-calendar-x',   'label' => 'Expirado'],
                                ];
                                $info = $estadoInfo[$ad['estado']] ?? ['clase'=>'','icono'=>'bi-question','label'=>$ad['estado']];
                                ?>
                                <span class="badge-estado <?= $info['clase'] ?>">
                                    <i class="bi <?= $info['icono'] ?> me-1"></i><?= $info['label'] ?>
                                </span>

                                <?php if ($ad['estado'] === 'rechazado'): ?>
                                <div class="mt-1" style="font-size:.72rem;color:var(--color-text-muted)">
                                    <i class="bi bi-info-circle me-1"></i>Contáctanos para más info.
                                </div>
                                <?php elseif ($ad['estado'] === 'pendiente' && !$currentUser['verificado']): ?>
                                <div class="mt-1" style="font-size:.72rem;color:var(--color-text-muted)">
                                    <i class="bi bi-person-check me-1"></i>Espera tu verificación.
                                </div>
                                <?php endif; ?>
                            </td>

                            <!-- Ciudad -->
                            <td class="d-none d-md-table-cell text-muted" style="font-size:.82rem">
                                <i class="bi bi-geo-alt me-1 text-primary"></i><?= e($ad['ciudad']) ?>
                            </td>

                            <!-- Vistas -->
                            <td class="d-none d-lg-table-cell text-muted" style="font-size:.82rem">
                                <i class="bi bi-eye me-1 text-primary"></i><?= number_format((int)$ad['vistas']) ?>
                            </td>

                            <!-- Fecha -->
                            <td class="d-none d-sm-table-cell text-muted" style="font-size:.78rem;white-space:nowrap">
                                <?= e(Security::timeAgo($ad['fecha_creacion'])) ?>
                            </td>

                            <!-- Acciones -->
                            <td class="text-end">
                                <div class="d-flex gap-1 justify-content-end flex-wrap">
                                    <?php if ($ad['estado'] === 'publicado'): ?>
                                    <!-- Ver público -->
                                    <a href="<?= APP_URL ?>/anuncio/<?= (int)$ad['id'] ?>"
                                       class="btn btn-sm btn-secondary"
                                       title="Ver en el sitio"
                                       target="_blank">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <!-- Destacar -->
                                    <a href="<?= APP_URL ?>/destacar/<?= (int)$ad['id'] ?>"
                                       class="btn btn-sm"
                                       title="Destacar anuncio"
                                       style="background:rgba(245,158,11,.1);color:#F59E0B;border:1px solid rgba(245,158,11,.25)">
                                        <i class="bi bi-star"></i>
                                    </a>
                                    <?php endif; ?>

                                    <?php if ($ad['estado'] !== 'expirado'): ?>
                                    <!-- Editar -->
                                    <a href="<?= APP_URL ?>/anuncio/<?= (int)$ad['id'] ?>/editar"
                                       class="btn btn-sm btn-secondary"
                                       title="Editar">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <?php endif; ?>

                                    <!-- Eliminar -->
                                    <form method="POST"
                                          action="<?= APP_URL ?>/anuncio/<?= (int)$ad['id'] ?>/eliminar"
                                          class="d-inline">
                                        <?= $csrfField ?>
                                        <button type="submit"
                                                class="btn btn-sm btn-danger"
                                                data-confirm="¿Seguro que quieres eliminar «<?= e($ad['titulo']) ?>»? Esta acción no se puede deshacer."
                                                title="Eliminar">
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
        </div>
    </div>

    <!-- Paginación -->
    <?php if (($pagination['pages'] ?? 1) > 1): ?>
    <nav class="mt-4 d-flex justify-content-center" aria-label="Paginación de mis anuncios">
        <ul class="pagination">
            <li class="page-item <?= $pagination['current'] <= 1 ? 'disabled' : '' ?>">
                <a class="page-link" href="?page=<?= $pagination['current'] - 1 ?>">
                    <i class="bi bi-chevron-left"></i>
                </a>
            </li>
            <?php for ($p = 1; $p <= $pagination['pages']; $p++): ?>
            <li class="page-item <?= $p === (int)$pagination['current'] ? 'active' : '' ?>">
                <a class="page-link" href="?page=<?= $p ?>"><?= $p ?></a>
            </li>
            <?php endfor; ?>
            <li class="page-item <?= $pagination['current'] >= $pagination['pages'] ? 'disabled' : '' ?>">
                <a class="page-link" href="?page=<?= $pagination['current'] + 1 ?>">
                    <i class="bi bi-chevron-right"></i>
                </a>
            </li>
        </ul>
    </nav>
    <?php endif; ?>

    <?php endif; ?>
</div>
