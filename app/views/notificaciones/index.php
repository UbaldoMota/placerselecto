<?php /** @var array $notifs */ /** @var array $pagination */ ?>
<div class="container py-4" style="max-width:720px">

    <div class="d-flex flex-column flex-sm-row align-items-start align-items-sm-center justify-content-between gap-2 mb-3">
        <h1 class="h5 mb-0 d-flex align-items-center gap-2">
            <i class="bi bi-bell-fill text-primary"></i>
            Mis notificaciones
        </h1>
        <div class="d-flex flex-wrap gap-2">
            <a href="<?= APP_URL ?>/mis-perfiles" class="btn btn-sm btn-secondary">
                <i class="bi bi-person-lines-fill me-1"></i>Mis perfiles
            </a>
            <a href="<?= APP_URL ?>/dashboard" class="btn btn-sm btn-secondary">
                <i class="bi bi-house me-1"></i>Dashboard
            </a>
            <?php if (($pagination['total'] ?? 0) > 0): ?>
            <form method="POST" action="<?= APP_URL ?>/notificaciones/leer-todas" class="m-0">
                <?= $csrfField ?>
                <button type="submit" class="btn btn-sm btn-outline-primary">
                    <i class="bi bi-check2-all"></i> Marcar todas leídas
                </button>
            </form>
            <?php endif; ?>
        </div>
    </div>

    <div class="card" style="background:var(--color-bg-card);border-color:var(--color-border)">

        <?php if (empty($notifs)): ?>
            <div class="text-center py-5 text-muted">
                <i class="bi bi-bell-slash" style="font-size:2rem"></i>
                <p class="mt-2 mb-0">No tienes notificaciones aún.</p>
            </div>
        <?php else: ?>

            <?php foreach ($notifs as $n): ?>
                <?php $unread = !(int)$n['leida']; ?>
                <div class="notif-row <?= $unread ? 'notif-unread' : '' ?>">
                    <div class="notif-row-icon text-<?= e($n['color']) ?>">
                        <i class="bi bi-<?= e($n['icono']) ?>"></i>
                    </div>
                    <div class="flex-grow-1" style="min-width:0">
                        <div class="d-flex justify-content-between gap-2">
                            <strong><?= e($n['titulo']) ?></strong>
                            <small class="text-muted" style="white-space:nowrap">
                                <?= e(Security::timeAgo($n['fecha_creacion'])) ?>
                            </small>
                        </div>
                        <div class="text-muted mt-1" style="font-size:.88rem">
                            <?= e($n['mensaje']) ?>
                        </div>
                        <div class="d-flex gap-2 mt-2 align-items-center">
                            <?php if (!empty($n['url'])): ?>
                                <a href="<?= APP_URL . e($n['url']) ?>"
                                   class="btn btn-sm btn-primary"
                                   style="font-size:.75rem">
                                    <i class="bi bi-arrow-right"></i> Ver
                                </a>
                            <?php endif; ?>
                            <?php if ($unread): ?>
                                <form method="POST"
                                      action="<?= APP_URL ?>/notificacion/<?= (int)$n['id'] ?>/leer"
                                      class="m-0">
                                    <?= $csrfField ?>
                                    <button type="submit" class="btn btn-sm btn-link p-0 text-primary"
                                            style="font-size:.75rem;text-decoration:none">
                                        Marcar leída
                                    </button>
                                </form>
                            <?php endif; ?>
                            <form method="POST"
                                  action="<?= APP_URL ?>/notificacion/<?= (int)$n['id'] ?>/eliminar"
                                  class="m-0 ms-auto"
                                  data-confirm-submit="¿Eliminar esta notificación?">
                                <?= $csrfField ?>
                                <button type="submit" class="btn btn-sm btn-link p-0 text-danger"
                                        style="font-size:.75rem;text-decoration:none">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>

        <?php endif; ?>
    </div>

    <?php
    $pages   = (int)($pagination['pages']   ?? 1);
    $current = (int)($pagination['current'] ?? 1);
    $total   = (int)($pagination['total']   ?? 0);
    $perPage = (int)($pagination['perPage'] ?? 20);
    ?>
    <?php if ($pages > 1): ?>
    <nav class="mt-3" aria-label="Paginación de notificaciones">
        <div class="d-flex flex-column flex-sm-row align-items-center justify-content-between gap-2">
            <small class="text-muted" style="font-size:.78rem">
                Mostrando
                <strong><?= min(($current - 1) * $perPage + 1, $total) ?>–<?= min($current * $perPage, $total) ?></strong>
                de <strong><?= number_format($total) ?></strong>
            </small>

            <ul class="pagination pagination-sm mb-0 flex-wrap">
                <!-- Primera -->
                <li class="page-item <?= $current <= 1 ? 'disabled' : '' ?>">
                    <a class="page-link" href="?page=1" aria-label="Primera"><i class="bi bi-chevron-bar-left"></i></a>
                </li>
                <!-- Anterior -->
                <li class="page-item <?= $current <= 1 ? 'disabled' : '' ?>">
                    <a class="page-link" href="?page=<?= max(1, $current - 1) ?>" aria-label="Anterior"><i class="bi bi-chevron-left"></i></a>
                </li>

                <?php
                // Ventana de 5 páginas alrededor de la actual
                $start = max(1, $current - 2);
                $end   = min($pages, $current + 2);
                if ($start > 1): ?>
                <li class="page-item disabled"><span class="page-link">…</span></li>
                <?php endif; ?>

                <?php for ($p = $start; $p <= $end; $p++): ?>
                <li class="page-item <?= $p === $current ? 'active' : '' ?>">
                    <a class="page-link" href="?page=<?= $p ?>"><?= $p ?></a>
                </li>
                <?php endfor; ?>

                <?php if ($end < $pages): ?>
                <li class="page-item disabled"><span class="page-link">…</span></li>
                <?php endif; ?>

                <!-- Siguiente -->
                <li class="page-item <?= $current >= $pages ? 'disabled' : '' ?>">
                    <a class="page-link" href="?page=<?= min($pages, $current + 1) ?>" aria-label="Siguiente"><i class="bi bi-chevron-right"></i></a>
                </li>
                <!-- Última -->
                <li class="page-item <?= $current >= $pages ? 'disabled' : '' ?>">
                    <a class="page-link" href="?page=<?= $pages ?>" aria-label="Última"><i class="bi bi-chevron-bar-right"></i></a>
                </li>
            </ul>
        </div>
    </nav>
    <?php elseif ($total > 0): ?>
    <div class="mt-2 text-muted text-center" style="font-size:.78rem">
        <?= number_format($total) ?> notificación<?= $total > 1 ? 'es' : '' ?> en total
    </div>
    <?php endif; ?>

</div>
