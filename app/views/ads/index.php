<?php
/**
 * ads/index.php — Listado público de anuncios con filtros y paginación.
 */
$q         = $filtros['buscar']    ?? '';
$ciudad    = $filtros['ciudad']    ?? '';
$categoria = (int)($filtros['categoria'] ?? 0);
?>

<!-- BARRA DE BÚSQUEDA SUPERIOR -->
<div style="background:var(--color-bg-card2);border-bottom:1px solid var(--color-border);padding:1rem 0">
    <div class="container-fluid" style="max-width:1400px">
        <form action="<?= APP_URL ?>/anuncios" method="GET"
              class="row g-2 align-items-end" id="top-search-form">
            <div class="col-12 col-sm-4">
                <input type="search" name="q" class="form-control"
                       placeholder="¿Qué buscas?"
                       value="<?= e($q) ?>">
            </div>
            <div class="col-6 col-sm-2">
                <select id="ts-estado" class="form-select">
                    <option value="">Todos los estados</option>
                    <?php foreach ($estados as $est): ?>
                    <option value="<?= (int)$est['id'] ?>"
                            <?= (int)$est['id'] === (int)$filtroEstadoId ? 'selected' : '' ?>>
                        <?= e($est['nombre']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-6 col-sm-2">
                <select id="ts-municipio" name="ciudad"
                        class="form-select"
                        <?= empty($filtroMunicipios) ? 'disabled' : '' ?>>
                    <option value="">Todos los municipios</option>
                    <?php foreach ($filtroMunicipios as $m): ?>
                    <option value="<?= e($m['nombre']) ?>"
                            <?= $m['nombre'] === $ciudad ? 'selected' : '' ?>>
                        <?= e($m['nombre']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-8 col-sm-3">
                <select name="categoria" class="form-select">
                    <option value="">Todas las categorías</option>
                    <?php foreach ($categorias as $cat): ?>
                    <option value="<?= (int)$cat['id'] ?>"
                            <?= (int)$cat['id'] === $categoria ? 'selected' : '' ?>>
                        <?= e($cat['nombre']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-4 col-sm-1">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-search"></i>
                </button>
            </div>
        </form>
    </div>
</div>

<div class="container-fluid py-4" style="max-width:1400px">
    <div class="row g-4">

        <!-- SIDEBAR FILTROS — oculto en móvil, visible desde lg -->
        <div class="col-lg-3 col-xl-2 d-none d-lg-block">
            <div class="filter-sidebar position-lg-sticky" style="top:80px">

                <form id="filter-form" action="<?= APP_URL ?>/anuncios" method="GET">

                    <!-- Búsqueda de texto -->
                    <div class="mb-3">
                        <div class="filter-title">Buscar</div>
                        <div class="input-group input-group-sm">
                            <input type="search"
                                   name="q"
                                   class="form-control"
                                   placeholder="Palabras clave..."
                                   value="<?= e($q) ?>"
                                   id="search-input">
                            <button class="btn btn-primary btn-sm" type="submit">
                                <i class="bi bi-search"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Categoría -->
                    <div class="mb-3">
                        <div class="filter-title">Categoría</div>
                        <div class="d-flex flex-column gap-1">
                            <label class="d-flex align-items-center gap-2 cursor-pointer"
                                   style="font-size:.85rem">
                                <input type="radio" name="categoria" value="0"
                                       <?= $categoria === 0 ? 'checked' : '' ?>
                                       data-auto-submit>
                                Todas
                            </label>
                            <?php foreach ($categorias as $cat): ?>
                            <label class="d-flex align-items-center gap-2 cursor-pointer"
                                   style="font-size:.85rem">
                                <input type="radio" name="categoria"
                                       value="<?= (int)$cat['id'] ?>"
                                       <?= (int)$cat['id'] === $categoria ? 'checked' : '' ?>
                                       data-auto-submit>
                                <i class="bi <?= e($cat['icono'] ?? 'bi-person') ?> text-primary"></i>
                                <?= e($cat['nombre']) ?>
                                <?php if (!empty($cat['total_anuncios'])): ?>
                                <span class="ms-auto text-muted" style="font-size:.72rem">
                                    <?= (int)$cat['total_anuncios'] ?>
                                </span>
                                <?php endif; ?>
                            </label>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Estado -->
                    <div class="mb-2">
                        <div class="filter-title">Estado</div>
                        <select id="fl-estado" class="form-select form-select-sm">
                            <option value="">Todos los estados</option>
                            <?php foreach ($estados as $est): ?>
                            <option value="<?= (int)$est['id'] ?>"
                                    <?= (int)$est['id'] === (int)$filtroEstadoId ? 'selected' : '' ?>>
                                <?= e($est['nombre']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Municipio -->
                    <div class="mb-3">
                        <select id="fl-municipio" name="ciudad"
                                class="form-select form-select-sm"
                                <?= empty($filtroMunicipios) ? 'disabled' : '' ?>
                                data-auto-submit>
                            <option value="">Todos los municipios</option>
                            <?php foreach ($filtroMunicipios as $m): ?>
                            <option value="<?= e($m['nombre']) ?>"
                                    <?= $m['nombre'] === $ciudad ? 'selected' : '' ?>>
                                <?= e($m['nombre']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                        <div id="fl-loading" class="form-text text-muted d-none mt-1" style="font-size:.72rem">
                            <span class="spinner-border spinner-border-sm me-1"></span>Cargando…
                        </div>
                    </div>

                    <!-- Limpiar filtros -->
                    <?php if ($q || $ciudad || $categoria): ?>
                    <a href="<?= APP_URL ?>/anuncios"
                       class="btn btn-sm btn-secondary w-100">
                        <i class="bi bi-x-circle me-1"></i>Limpiar filtros
                    </a>
                    <?php endif; ?>

                </form>
            </div>
        </div>

        <!-- CONTENIDO PRINCIPAL -->
        <div class="col-12 col-lg-9 col-xl-10">

            <!-- Header resultados -->
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
                <div>
                    <h1 class="h5 fw-bold mb-0">
                        <?php if ($q): ?>
                            Resultados para «<?= e($q) ?>»
                        <?php elseif ($ciudad): ?>
                            Anuncios en <?= e($ciudad) ?>
                        <?php else: ?>
                            Todos los anuncios
                        <?php endif; ?>
                    </h1>
                    <span class="text-muted" style="font-size:.82rem">
                        <?= number_format((int)($pagination['total'] ?? 0)) ?> resultado(s)
                    </span>
                </div>
                <!-- Filtros activos -->
                <div class="d-flex flex-wrap gap-1">
                    <?php if ($q): ?>
                    <span class="badge py-1 px-2" style="background:rgba(255,45,117,.12);color:var(--color-primary);font-size:.75rem">
                        <i class="bi bi-search me-1"></i><?= e($q) ?>
                    </span>
                    <?php endif; ?>
                    <?php if ($ciudad): ?>
                    <span class="badge py-1 px-2" style="background:rgba(255,45,117,.12);color:var(--color-primary);font-size:.75rem">
                        <i class="bi bi-geo-alt me-1"></i><?= e($ciudad) ?>
                    </span>
                    <?php endif; ?>
                </div>
            </div>

            <?php if (empty($anuncios)): ?>
            <!-- Sin resultados -->
            <div class="text-center py-5">
                <i class="bi bi-search" style="font-size:3.5rem;color:var(--color-border)"></i>
                <h3 class="h5 mt-3 mb-2">No encontramos anuncios</h3>
                <p class="text-muted mb-4">
                    <?= $q || $ciudad || $categoria
                        ? 'Intenta con otros filtros o amplía tu búsqueda.'
                        : 'Aún no hay anuncios publicados. ¡Sé el primero!' ?>
                </p>
                <?php if ($q || $ciudad || $categoria): ?>
                    <a href="<?= APP_URL ?>/anuncios" class="btn btn-secondary me-2">Ver todos</a>
                <?php endif; ?>
                <a href="<?= APP_URL ?>/<?= $currentUser ? 'anuncio/crear' : 'registro' ?>"
                   class="btn btn-primary">
                    <i class="bi bi-plus-lg me-1"></i>Publicar anuncio
                </a>
            </div>

            <?php else: ?>

            <!-- Lista de anuncios -->
            <div class="ad-list" id="ads-grid">
                <?php foreach ($anuncios as $ad):
                    $ubicacion = (!empty($ad['municipio_nombre']) && !empty($ad['estado_nombre']))
                        ? e($ad['municipio_nombre']) . ', ' . e($ad['estado_nombre'])
                        : e($ad['ciudad'] ?? '—');
                    $descripcionCorta = mb_substr(strip_tags($ad['descripcion'] ?? ''), 0, 160, 'UTF-8');
                    if (mb_strlen($ad['descripcion'] ?? '', 'UTF-8') > 160) $descripcionCorta .= '…';
                ?>
                <a href="<?= APP_URL ?>/anuncio/<?= (int)$ad['id'] ?>"
                   class="ad-row text-decoration-none <?= $ad['destacado'] ? 'ad-row--destacado' : '' ?>">

                    <!-- Imagen -->
                    <div class="ad-row__thumb">
                        <?php $imgUrl = Security::imgUrl($ad); ?>
                        <?php if ($imgUrl): ?>
                            <img src="<?= e($imgUrl) ?>"
                                 alt="<?= e($ad['titulo']) ?>"
                                 loading="lazy">
                        <?php else: ?>
                            <div class="ad-row__no-img"><i class="bi bi-person"></i></div>
                        <?php endif; ?>
                        <?php if ($ad['destacado']): ?>
                            <span class="ad-row__top-badge">
                                <i class="bi bi-star-fill me-1"></i>TOP
                            </span>
                        <?php endif; ?>
                    </div>

                    <!-- Contenido -->
                    <div class="ad-row__body">
                        <div class="ad-row__header">
                            <h3 class="ad-row__title"><?= e($ad['titulo']) ?></h3>
                            <span class="ad-row__time"><?= e(Security::timeAgo($ad['fecha_publicacion'] ?? $ad['fecha_creacion'])) ?></span>
                        </div>

                        <p class="ad-row__desc"><?= e($descripcionCorta) ?></p>

                        <div class="ad-row__meta">
                            <span><i class="bi bi-geo-alt-fill"></i><?= $ubicacion ?></span>
                            <span><i class="bi bi-tag-fill"></i><?= e($ad['categoria_nombre'] ?? '') ?></span>
                            <span><i class="bi bi-eye-fill"></i><?= number_format((int)$ad['vistas']) ?></span>
                        </div>
                    </div>

                    <!-- Flecha -->
                    <div class="ad-row__arrow">
                        <i class="bi bi-chevron-right"></i>
                    </div>
                </a>
                <?php endforeach; ?>
            </div>

            <!-- PAGINACIÓN -->
            <?php if (($pagination['pages'] ?? 1) > 1):
                $base  = APP_URL . '/anuncios?';
                $query = array_filter(['q' => $q, 'ciudad' => $ciudad, 'categoria' => $categoria ?: null]);
            ?>
            <nav class="mt-4 d-flex justify-content-center" aria-label="Paginación">
                <ul class="pagination flex-wrap justify-content-center">
                    <!-- Anterior -->
                    <li class="page-item <?= $pagination['current'] <= 1 ? 'disabled' : '' ?>">
                        <a class="page-link" href="<?= $base . http_build_query(array_merge($query, ['page' => $pagination['current'] - 1])) ?>">
                            <i class="bi bi-chevron-left"></i>
                        </a>
                    </li>

                    <?php
                    $curr  = (int) $pagination['current'];
                    $total = (int) $pagination['pages'];
                    $range = 2; // páginas a mostrar alrededor de la actual
                    for ($p = 1; $p <= $total; $p++):
                        if ($p === 1 || $p === $total || abs($p - $curr) <= $range):
                    ?>
                    <li class="page-item <?= $p === $curr ? 'active' : '' ?>">
                        <a class="page-link" href="<?= $base . http_build_query(array_merge($query, ['page' => $p])) ?>">
                            <?= $p ?>
                        </a>
                    </li>
                    <?php
                        elseif (abs($p - $curr) === $range + 1):
                    ?>
                    <li class="page-item disabled"><span class="page-link">…</span></li>
                    <?php
                        endif;
                    endfor;
                    ?>

                    <!-- Siguiente -->
                    <li class="page-item <?= $pagination['current'] >= $pagination['pages'] ? 'disabled' : '' ?>">
                        <a class="page-link" href="<?= $base . http_build_query(array_merge($query, ['page' => $pagination['current'] + 1])) ?>">
                            <i class="bi bi-chevron-right"></i>
                        </a>
                    </li>
                </ul>
            </nav>
            <?php endif; ?>

            <?php endif; ?>
        </div>
    </div>
</div>


<?php
$extraJs = '<script src="' . APP_URL . '/public/assets/js/ads-index.js" defer></script>';
?>
