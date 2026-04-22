<?php
/**
 * perfiles/index.php — Listado público de perfiles con filtros.
 */
$q            = $filtros['buscar']       ?? '';
$idCategoria  = (int)($filtros['id_categoria'] ?? 0);
$idEstado     = (int)($filtros['id_estado']    ?? 0);
$idMunicipio  = (int)($filtros['id_municipio'] ?? 0);
?>

<!-- Barra de búsqueda -->
<?php require VIEWS_PATH . '/partials/perfil-search.php'; ?>

<div class="container-fluid py-4" style="max-width:1400px">

    <!-- Encabezado resultados -->
    <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
        <p class="text-muted mb-0" style="font-size:.875rem">
            <?php if ($q || $idCategoria || $idEstado || $idMunicipio): ?>
                <?= number_format((int)($pagination['total'] ?? 0)) ?> perfil(es) encontrado(s)
            <?php else: ?>
                <?= number_format((int)($pagination['total'] ?? 0)) ?> perfiles disponibles
            <?php endif; ?>
        </p>
        <?php if ($q || $idCategoria || $idEstado || $idMunicipio): ?>
        <a href="<?= APP_URL ?>/perfiles" class="btn btn-sm btn-secondary">
            <i class="bi bi-x-circle me-1"></i>Limpiar filtros
        </a>
        <?php endif; ?>
    </div>

    <?php if (empty($perfiles)): ?>
    <div class="text-center py-5">
        <i class="bi bi-person-x" style="font-size:4rem;color:var(--color-border)"></i>
        <h3 class="h5 mt-3 mb-2">No se encontraron perfiles</h3>
        <p class="text-muted mb-4">Prueba con otros filtros o vuelve más tarde.</p>
        <a href="<?= APP_URL ?>/perfiles" class="btn btn-secondary">Ver todos los perfiles</a>
    </div>
    <?php else: ?>

    <!-- Lista de perfiles (filas) -->
    <?php
    // Query string para propagar filtros a /perfil/{id} (usado por prev/next)
    $qsNav = http_build_query(array_filter([
        'q'            => $q,
        'id_categoria' => $idCategoria ?: null,
        'id_estado'    => $idEstado    ?: null,
        'id_municipio' => $idMunicipio ?: null,
        'nav'          => '1',
    ]));
    ?>
    <div class="perfil-list">
        <?php foreach ($perfiles as $p):
            $imgUrl = !empty($p['imagen_token']) ? APP_URL . '/img/' . $p['imagen_token'] : null;
            $descTxt = trim(strip_tags($p['descripcion'] ?? ''));
            $descCorta = mb_strlen($descTxt) > 180 ? mb_substr($descTxt, 0, 180) . '…' : $descTxt;
            $tieneWA = !empty($p['whatsapp']);
            $tieneTG = !empty($p['telegram']);
            $tieneEM = !empty($p['email_contacto']);
            $sinAnticipo = empty($p['pide_anticipo']);
            $tiempoRel = !empty($p['fecha_publicacion']) ? Security::timeAgo($p['fecha_publicacion']) : null;

            $rowCls = '';
            if (!empty($p['boost_top']))            $rowCls = 'perfil-row--top';
            elseif (!empty($p['boost_resaltado']))  $rowCls = 'perfil-row--resaltado';
        ?>
        <a href="<?= APP_URL ?>/perfil/<?= (int)$p['id'] ?><?= $qsNav ? '?' . $qsNav : '' ?>" class="perfil-row <?= $rowCls ?>">

            <!-- FOTO -->
            <div class="perfil-row__photo">
                <?php if ($imgUrl): ?>
                    <img src="<?= e($imgUrl) ?>" alt="<?= e($p['nombre']) ?>" loading="lazy">
                <?php else: ?>
                    <div class="perfil-row__noimg"><i class="bi bi-person"></i></div>
                <?php endif; ?>

                <?php if (!empty($p['boost_top'])): ?>
                <span class="perfil-row__badge perfil-row__badge--top">
                    <i class="bi bi-arrow-up-square-fill"></i>TOP
                </span>
                <?php elseif (!empty($p['boost_resaltado'])): ?>
                <span class="perfil-row__badge perfil-row__badge--resaltado">
                    <i class="bi bi-stars"></i>
                </span>
                <?php endif; ?>
            </div>

            <!-- CUERPO -->
            <div class="perfil-row__body">
                <div class="perfil-row__head">
                    <h3 class="perfil-row__name">
                        <?= e($p['nombre']) ?>
                        <?php if (!empty($p['edad']) && (int)($p['edad_publica'] ?? 1)): ?>
                            <span class="text-muted fw-normal" style="font-size:.88em">, <?= (int)$p['edad'] ?></span>
                        <?php endif; ?>
                    </h3>
                    <?php if ($tiempoRel): ?>
                    <span class="perfil-row__time"><i class="bi bi-clock-history"></i> <?= e($tiempoRel) ?></span>
                    <?php endif; ?>
                </div>

                <div class="perfil-row__meta">
                    <?php if (!empty($p['categoria_nombre'])): ?>
                    <span><i class="bi bi-tag-fill"></i><?= e($p['categoria_nombre']) ?></span>
                    <?php endif; ?>
                    <?php if (!empty($p['municipio_nombre']) || !empty($p['estado_nombre'])): ?>
                    <span><i class="bi bi-geo-alt-fill"></i>
                        <?= e(trim(($p['municipio_nombre'] ?? '') . (!empty($p['estado_nombre']) ? ', ' . $p['estado_nombre'] : ''), ', ')) ?>
                    </span>
                    <?php endif; ?>
                    <?php
                    $comN    = (int)($p['com_count'] ?? 0);
                    $comProm = round((float)($p['com_promedio'] ?? 0), 1);
                    if ($comN > 0):
                    ?>
                    <span class="perfil-row__rating" title="<?= $comProm ?> de 5 (<?= $comN ?> opinión<?= $comN > 1 ? 'es' : '' ?>)">
                        <?php $filled = (int)floor($comProm); for ($i = 1; $i <= 5; $i++): ?>
                        <i class="bi <?= $i <= $filled ? 'bi-star-fill' : ($i - .5 <= $comProm ? 'bi-star-half' : 'bi-star') ?>"></i>
                        <?php endfor; ?>
                        <strong><?= $comProm ?></strong>
                        <span class="text-muted">(<?= $comN ?>)</span>
                    </span>
                    <?php endif; ?>
                </div>

                <?php if ($descCorta !== ''): ?>
                <p class="perfil-row__desc"><?= e($descCorta) ?></p>
                <?php endif; ?>

                <div class="perfil-row__foot">
                    <div class="perfil-row__contacts">
                        <?php if ($tieneWA): ?>
                        <span class="perfil-row__contact" style="color:#25d366" title="WhatsApp"><i class="bi bi-whatsapp"></i></span>
                        <?php endif; ?>
                        <?php if ($tieneTG): ?>
                        <span class="perfil-row__contact" style="color:#29b6f6" title="Telegram"><i class="bi bi-telegram"></i></span>
                        <?php endif; ?>
                        <?php if ($tieneEM): ?>
                        <span class="perfil-row__contact" style="color:#F59E0B" title="Email"><i class="bi bi-envelope-fill"></i></span>
                        <?php endif; ?>
                    </div>

                    <?php if ($sinAnticipo): ?>
                    <span class="perfil-row__flag perfil-row__flag--safe">
                        <i class="bi bi-shield-check-fill"></i>No pide anticipo
                    </span>
                    <?php endif; ?>

                    <!-- Indicadores de contenido -->
                    <div class="perfil-row__assets">
                        <?php $fotosN = (int)($p['fotos_count'] ?? 0); if ($fotosN > 0): ?>
                        <span class="perfil-row__asset" title="<?= $fotosN ?> foto(s)">
                            <i class="bi bi-images"></i><?= $fotosN ?>
                        </span>
                        <?php endif; ?>

                        <?php $videosN = (int)($p['videos_count'] ?? 0); if ($videosN > 0): ?>
                        <span class="perfil-row__asset" title="<?= $videosN ?> video(s)">
                            <i class="bi bi-play-btn-fill"></i><?= $videosN ?>
                        </span>
                        <?php endif; ?>

                        <?php if (!empty($p['tiene_mapa'])): ?>
                        <span class="perfil-row__asset" title="Incluye mapa de zona">
                            <i class="bi bi-geo-alt-fill"></i>Mapa
                        </span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- FLECHA -->
            <div class="perfil-row__arrow">
                <i class="bi bi-chevron-right"></i>
            </div>
        </a>
        <?php endforeach; ?>
    </div>

    <!-- Paginación -->
    <?php if (($pagination['pages'] ?? 1) > 1):
        $qs = http_build_query(array_filter(['q'=>$q,'id_categoria'=>$idCategoria,'id_estado'=>$idEstado,'id_municipio'=>$idMunicipio]));
        $base = APP_URL . '/perfiles?' . ($qs ? $qs . '&' : '');
    ?>
    <nav class="mt-4 d-flex justify-content-center">
        <ul class="pagination">
            <li class="page-item <?= $pagination['current'] <= 1 ? 'disabled' : '' ?>">
                <a class="page-link" href="<?= $base ?>page=<?= $pagination['current']-1 ?>">
                    <i class="bi bi-chevron-left"></i>
                </a>
            </li>
            <?php for ($pg = max(1, $pagination['current']-2); $pg <= min($pagination['pages'], $pagination['current']+2); $pg++): ?>
            <li class="page-item <?= $pg === (int)$pagination['current'] ? 'active' : '' ?>">
                <a class="page-link" href="<?= $base ?>page=<?= $pg ?>"><?= $pg ?></a>
            </li>
            <?php endfor; ?>
            <li class="page-item <?= $pagination['current'] >= $pagination['pages'] ? 'disabled' : '' ?>">
                <a class="page-link" href="<?= $base ?>page=<?= $pagination['current']+1 ?>">
                    <i class="bi bi-chevron-right"></i>
                </a>
            </li>
        </ul>
    </nav>
    <?php endif; ?>

    <?php endif; ?>
</div>

<?php // La cascada estado→municipio ya la maneja partials/perfil-search.php ?>
