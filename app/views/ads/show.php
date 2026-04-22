<?php
/**
 * show.php — Página de detalle de un anuncio.
 */
$esPropio = $currentUser && (int)$currentUser['id'] === (int)$anuncio['id_usuario'];
$tieneWA  = !empty($anuncio['whatsapp']);
$waLink   = $tieneWA ? 'https://wa.me/' . preg_replace('/\D/', '', $anuncio['whatsapp']) : '';
?>

<div class="container py-4">
    <div class="row g-4">

        <!-- COLUMNA PRINCIPAL -->
        <div class="col-12 col-lg-8">

            <!-- Breadcrumb -->
            <nav aria-label="breadcrumb" class="mb-3">
                <ol class="breadcrumb" style="font-size:.8rem;background:none;padding:0;margin:0">
                    <li class="breadcrumb-item"><a href="<?= APP_URL ?>">Inicio</a></li>
                    <li class="breadcrumb-item"><a href="<?= APP_URL ?>/anuncios">Anuncios</a></li>
                    <li class="breadcrumb-item active text-muted" aria-current="page">
                        <?= e(Security::truncate($anuncio['titulo'], 40)) ?>
                    </li>
                </ol>
            </nav>

            <!-- Card del anuncio -->
            <div class="card">

                <!-- Galería de fotos — proporciones originales, sin recorte -->
                <?php
                $fotos      = $fotosPrincipal ?? [];
                $totalFotos = count($fotos);

                // Fallback legacy: si no hay tokens pero sí imagen_principal
                if ($totalFotos === 0 && ($legacyUrl = Security::imgUrl($anuncio))) {
                    $fotos      = [['token' => null, '_legacy_url' => $legacyUrl]];
                    $totalFotos = 1;
                }

                // URLs para el lightbox
                $lightboxUrls = array_map(fn($f) =>
                    !empty($f['token'])
                        ? APP_URL . '/img/' . $f['token']
                        : ($f['_legacy_url'] ?? ''),
                    $fotos
                );
                ?>
                <?php if ($totalFotos > 0): ?>

                <?php if ($totalFotos === 1):
                    $url = $lightboxUrls[0];
                ?>
                <div class="foto-galeria--1">
                    <img src="<?= e($url) ?>"
                         alt="<?= e($anuncio['titulo']) ?>"
                         loading="eager"
                         data-lightbox-open="0"
                         style="cursor:zoom-in">
                    <?php if ($anuncio['destacado']): ?>
                    <span class="foto-galeria__destacado">
                        <i class="bi bi-star-fill me-1"></i>Destacado
                    </span>
                    <?php endif; ?>
                </div>

                <?php else: ?>
                <div class="foto-galeria--multi">
                    <?php foreach ($fotos as $i => $f):
                        $url = $lightboxUrls[$i];
                    ?>
                    <div class="foto-galeria__item" data-lightbox-open="<?= $i ?>">
                        <img src="<?= e($url) ?>"
                             alt="<?= e($anuncio['titulo']) ?> — foto <?= $i + 1 ?>"
                             loading="<?= $i < 2 ? 'eager' : 'lazy' ?>">
                        <?php if ($i === 0 && $anuncio['destacado']): ?>
                        <span class="foto-galeria__destacado">
                            <i class="bi bi-star-fill me-1"></i>Destacado
                        </span>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>

                <?php endif; ?>

                <!-- Lightbox -->
                <div id="lightbox" class="lightbox">
                    <button class="lightbox__close" data-lightbox="close" title="Cerrar">&times;</button>
                    <button class="lightbox__nav lightbox__prev" data-lightbox-nav="-1" title="Anterior">&#8249;</button>
                    <img id="lightbox-img" class="lightbox__img" src="" alt="">
                    <button class="lightbox__nav lightbox__next" data-lightbox-nav="1" title="Siguiente">&#8250;</button>
                    <div class="lightbox__counter" id="lightbox-counter"></div>
                </div>
                <script id="lightbox-data" type="application/json"><?= json_encode(array_values($lightboxUrls)) ?></script>
                <script src="<?= APP_URL ?>/public/assets/js/lightbox.js" defer></script>

                <div class="card-body p-4">

                    <!-- Meta superior -->
                    <div class="d-flex flex-wrap gap-2 mb-3" style="font-size:.78rem">
                        <span style="color:var(--color-text-muted)">
                            <i class="bi bi-tag text-primary me-1"></i><?= e($anuncio['categoria_nombre'] ?? '—') ?>
                        </span>
                        <span style="color:var(--color-text-muted)">
                            <i class="bi bi-geo-alt text-primary me-1"></i><?php
                            if (!empty($anuncio['municipio_nombre']) && !empty($anuncio['estado_nombre'])) {
                                echo e($anuncio['municipio_nombre']) . ', ' . e($anuncio['estado_nombre']);
                            } else {
                                echo e($anuncio['ciudad'] ?? '—');
                            }
                            ?>
                        </span>
                        <span style="color:var(--color-text-muted)">
                            <i class="bi bi-eye text-primary me-1"></i><?= number_format((int)$anuncio['vistas']) ?> vistas
                        </span>
                        <span style="color:var(--color-text-muted)">
                            <i class="bi bi-clock text-primary me-1"></i><?= e(Security::timeAgo($anuncio['fecha_publicacion'] ?? $anuncio['fecha_creacion'])) ?>
                        </span>
                    </div>

                    <!-- Título -->
                    <h1 class="h3 fw-bold mb-3"><?= e($anuncio['titulo']) ?></h1>

                    <!-- Descripción -->
                    <div class="mb-4"
                         style="font-size:.93rem;line-height:1.8;color:var(--color-text);white-space:pre-wrap">
                        <?= e($anuncio['descripcion']) ?>
                    </div>

                    <?php if ($esPropio): ?>
                    <!-- Acciones del propietario -->
                    <div class="d-flex gap-2 flex-wrap pt-3"
                         style="border-top:1px solid var(--color-border)">
                        <a href="<?= APP_URL ?>/anuncio/<?= (int)$anuncio['id'] ?>/editar"
                           class="btn btn-sm btn-secondary">
                            <i class="bi bi-pencil me-1"></i>Editar
                        </a>
                        <?php if ($anuncio['estado'] === 'publicado' && !$anuncio['destacado']): ?>
                        <a href="<?= APP_URL ?>/destacar/<?= (int)$anuncio['id'] ?>"
                           class="btn btn-sm"
                           style="background:rgba(245,158,11,.1);color:#F59E0B;border:1px solid rgba(245,158,11,.25)">
                            <i class="bi bi-star me-1"></i>Destacar anuncio
                        </a>
                        <?php endif; ?>
                        <form method="POST"
                              action="<?= APP_URL ?>/anuncio/<?= (int)$anuncio['id'] ?>/eliminar">
                            <?= $csrfField ?>
                            <button type="submit" class="btn btn-sm btn-danger"
                                    data-confirm="¿Eliminar este anuncio permanentemente?">
                                <i class="bi bi-trash me-1"></i>Eliminar
                            </button>
                        </form>
                    </div>
                    <?php endif; ?>

                </div>
            </div>

            <!-- Anuncios relacionados -->
            <?php if (!empty($relacionados)): ?>
            <div class="mt-4">
                <h2 class="h6 fw-bold mb-3 text-muted text-uppercase" style="letter-spacing:.5px">
                    <i class="bi bi-grid me-2"></i>Anuncios similares
                </h2>
                <div class="row g-3">
                    <?php foreach ($relacionados as $rel): ?>
                    <div class="col-6 col-sm-3">
                        <a href="<?= APP_URL ?>/anuncio/<?= (int)$rel['id'] ?>"
                           class="text-decoration-none">
                            <div class="ad-card">
                                <div class="ad-card__image">
                                    <?php $relImgUrl = Security::imgUrl($rel); ?>
                                    <?php if ($relImgUrl): ?>
                                        <img src="<?= e($relImgUrl) ?>"
                                             alt="" loading="lazy">
                                    <?php else: ?>
                                        <div class="no-image"><i class="bi bi-person"></i></div>
                                    <?php endif; ?>
                                    <?php if ($rel['destacado']): ?>
                                        <span class="ad-card__badge-destacado">
                                            <i class="bi bi-star-fill me-1"></i>Top
                                        </span>
                                    <?php endif; ?>
                                </div>
                                <div class="ad-card__body">
                                    <div class="ad-card__title"><?= e($rel['titulo']) ?></div>
                                    <div class="ad-card__meta">
                                        <span><i class="bi bi-geo-alt"></i><?= e($rel['ciudad'] ?? '—') ?></span>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

        </div>

        <!-- SIDEBAR -->
        <div class="col-12 col-lg-4">

            <!-- Panel de confiabilidad -->
            <?php
            $score = $confiabilidad['score'];
            $total = $confiabilidad['total'];
            $pct   = $total > 0 ? round($score / $total * 100) : 0;
            $colorScore = $pct >= 75 ? '#10B981' : ($pct >= 40 ? '#F59E0B' : '#FF2D75');
            ?>
            <div class="card mb-4">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <span class="fw-semibold" style="font-size:.875rem">
                        <i class="bi bi-patch-check text-primary me-2"></i>Confiabilidad del perfil
                    </span>
                    <span class="fw-bold" style="color:<?= $colorScore ?>;font-size:.95rem">
                        <?= $score ?>/<?= $total ?>
                    </span>
                </div>
                <div class="card-body" style="padding:.75rem 1rem">

                    <!-- Barra de progreso -->
                    <div style="background:var(--color-bg-card2);border-radius:20px;height:6px;margin-bottom:1rem;overflow:hidden">
                        <div style="width:<?= $pct ?>%;height:100%;background:<?= $colorScore ?>;border-radius:20px;transition:width .4s"></div>
                    </div>

                    <p style="font-size:.75rem;color:var(--color-text-muted);margin-bottom:.85rem">
                        Entienda qué hace que este perfil sea confiable:
                    </p>

                    <!-- Indicadores -->
                    <ul class="list-unstyled mb-0" style="display:flex;flex-direction:column;gap:.55rem">
                    <?php foreach ($confiabilidad['indicadores'] as $ind): ?>
                        <li class="d-flex align-items-start gap-2"
                            title="<?= e($ind['descripcion']) ?>"
                            style="cursor:default">
                            <?php if ($ind['activo']): ?>
                                <i class="bi <?= e($ind['icono']) ?> flex-shrink-0 mt-1"
                                   style="color:#10B981;font-size:.95rem"></i>
                            <?php else: ?>
                                <i class="bi <?= e($ind['icono']) ?> flex-shrink-0 mt-1"
                                   style="color:var(--color-text-muted);opacity:.35;font-size:.95rem"></i>
                            <?php endif; ?>
                            <span style="font-size:.78rem;color:<?= $ind['activo'] ? 'var(--color-text)' : 'var(--color-text-muted)' ?>;<?= $ind['activo'] ? '' : 'opacity:.5' ?>">
                                <?= e($ind['label']) ?>
                            </span>
                        </li>
                    <?php endforeach; ?>
                    </ul>

                    <?php if ($pct < 100): ?>
                    <p style="font-size:.7rem;color:var(--color-text-muted);margin-top:.85rem;margin-bottom:0;border-top:1px solid var(--color-border);padding-top:.6rem">
                        <i class="bi bi-info-circle me-1"></i>Los indicadores inactivos no han sido verificados aún.
                    </p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Card de contacto -->
            <div class="card mb-4 position-sticky" style="top:80px">
                <div class="card-header">
                    <span class="fw-semibold">
                        <i class="bi bi-person-circle text-primary me-2"></i>Contactar
                    </span>
                </div>
                <div class="card-body text-center">

                    <!-- Avatar genérico -->
                    <div style="width:64px;height:64px;border-radius:50%;background:rgba(255,45,117,.1);border:2px solid rgba(255,45,117,.25);display:flex;align-items:center;justify-content:center;font-size:1.8rem;color:var(--color-primary);margin:0 auto .75rem">
                        <i class="bi bi-person"></i>
                    </div>
                    <p class="text-muted mb-3" style="font-size:.82rem">
                        Para contactar a este anunciante usa el botón de WhatsApp.
                    </p>

                    <?php if ($tieneWA): ?>
                    <a href="<?= e($waLink) ?>"
                       class="btn btn-success w-100 mb-2"
                       target="_blank"
                       rel="noopener noreferrer">
                        <i class="bi bi-whatsapp me-2"></i>Contactar por WhatsApp
                    </a>
                    <?php else: ?>
                    <div class="alert py-2 mb-2"
                         style="background:rgba(0,0,0,.03);border:1px solid var(--color-border);font-size:.82rem;color:var(--color-text-muted)">
                        <i class="bi bi-info-circle me-1"></i>El anunciante no proporcionó WhatsApp.
                    </div>
                    <?php endif; ?>

                    <!-- Reportar -->
                    <button type="button"
                            class="btn btn-sm btn-secondary w-100 mt-2"
                            data-bs-toggle="collapse"
                            data-bs-target="#reportForm">
                        <i class="bi bi-flag me-1"></i>Reportar anuncio
                    </button>

                    <!-- Formulario de reporte -->
                    <div class="collapse mt-3 text-start" id="reportForm">
                        <form method="POST"
                              action="<?= APP_URL ?>/anuncio/<?= (int)$anuncio['id'] ?>/reportar">
                            <?= $csrfField ?>
                            <label class="form-label" style="font-size:.8rem">Motivo del reporte</label>
                            <select name="motivo" class="form-select form-select-sm mb-2">
                                <option value="contenido_ilegal">Contenido ilegal</option>
                                <option value="menor_de_edad">Posible menor de edad</option>
                                <option value="spam">Spam / repetido</option>
                                <option value="engaño">Información engañosa</option>
                                <option value="datos_falsos">Datos falsos</option>
                                <option value="otro">Otro</option>
                            </select>
                            <textarea name="descripcion"
                                      class="form-control form-control-sm mb-2"
                                      rows="2"
                                      placeholder="Detalle adicional (opcional)"
                                      maxlength="300"></textarea>
                            <button type="submit" class="btn btn-danger btn-sm w-100">
                                <i class="bi bi-flag me-1"></i>Enviar reporte
                            </button>
                        </form>
                    </div>

                </div>
            </div>

        </div>

    </div>
</div>
