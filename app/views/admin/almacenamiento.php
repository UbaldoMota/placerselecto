<?php
/** @var array $resumen @var array $total @var array $top
 *  @var int $limitMb @var int $warnPct @var int $limitB @var float $usedPct
 *  @var bool $isWarn @var bool $isOver
 *  @var string $catFilter @var array $archivos
 *  @var int $totalArchivos @var int $paginaActual @var int $totalPaginas @var int $perPage */
?>
<div class="container-fluid px-4 py-4" style="max-width:1200px">

    <div class="d-flex flex-wrap align-items-start justify-content-between gap-3 mb-4">
        <div>
            <h1 class="h4 fw-bold mb-1">
                <i class="bi bi-hdd-fill text-primary me-2"></i>Almacenamiento
            </h1>
            <p class="text-muted mb-0" style="font-size:.875rem">
                Uso del disco por fotos, videos, documentos y archivos de verificación
            </p>
        </div>
        <a href="<?= APP_URL ?>/admin" class="btn btn-sm btn-secondary">
            <i class="bi bi-arrow-left me-1"></i>Panel
        </a>
    </div>

    <!-- Alertas de umbral -->
    <?php if ($isOver): ?>
    <div class="alert alert-danger mb-4" role="alert">
        <i class="bi bi-exclamation-octagon-fill me-1"></i>
        <strong>Límite superado.</strong> Estás usando <?= StorageScannerModel::fmtBytes($total['bytes']) ?> de <?= StorageScannerModel::fmtBytes($limitB) ?> (<?= $usedPct ?>%). Elimina archivos o aumenta el límite.
    </div>
    <?php elseif ($isWarn): ?>
    <div class="alert alert-warning mb-4" role="alert">
        <i class="bi bi-exclamation-triangle-fill me-1"></i>
        <strong>Cerca del límite.</strong> Usando <?= StorageScannerModel::fmtBytes($total['bytes']) ?> de <?= StorageScannerModel::fmtBytes($limitB) ?> (<?= $usedPct ?>%). Alerta configurada al <?= $warnPct ?>%.
    </div>
    <?php endif; ?>

    <!-- Uso global -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-2">
                <div>
                    <div class="text-muted" style="font-size:.72rem;text-transform:uppercase;letter-spacing:.05em;font-weight:600">
                        Uso total
                    </div>
                    <div class="fw-bold" style="font-size:1.8rem;line-height:1">
                        <?= StorageScannerModel::fmtBytes($total['bytes']) ?>
                        <span class="text-muted fw-normal" style="font-size:1rem"> / <?= StorageScannerModel::fmtBytes($limitB) ?></span>
                    </div>
                    <div class="text-muted" style="font-size:.82rem">
                        <?= number_format($total['files']) ?> archivos totales · <?= $usedPct ?>% del límite
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2" style="min-width:180px">
                    <button type="button" class="btn btn-sm btn-outline-primary"
                            data-bs-toggle="collapse" data-bs-target="#storage-config">
                        <i class="bi bi-gear me-1"></i>Configurar límite
                    </button>
                </div>
            </div>
            <div style="height:12px;background:var(--color-bg-alt);border-radius:10px;overflow:hidden">
                <div style="height:100%;width:<?= min(100, $usedPct) ?>%;background:<?= $isOver ? 'var(--color-danger)' : ($isWarn ? 'var(--color-warning)' : 'linear-gradient(90deg,var(--color-primary),var(--color-primary-l))') ?>;transition:width .3s"></div>
            </div>

            <!-- Config collapse -->
            <div class="collapse mt-3" id="storage-config">
                <form method="POST" action="<?= APP_URL ?>/admin/almacenamiento/config"
                      class="row g-2 align-items-end p-3 rounded" style="background:var(--color-bg-alt);border:1px solid var(--color-border)">
                    <?= $csrfField ?>
                    <div class="col-md-4">
                        <label class="form-label">Límite total (MB)</label>
                        <input type="number" name="storage_limit_mb" class="form-control form-control-sm"
                               min="100" value="<?= (int)$limitMb ?>">
                        <small class="text-muted" style="font-size:.72rem">1024 MB = 1 GB</small>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Alerta al llegar a (%)</label>
                        <input type="number" name="storage_warning_pct" class="form-control form-control-sm"
                               min="1" max="100" value="<?= (int)$warnPct ?>">
                    </div>
                    <div class="col-md-4">
                        <button type="submit" class="btn btn-primary btn-sm w-100">
                            <i class="bi bi-save me-1"></i>Guardar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Grid por categoría -->
    <h2 class="h6 fw-bold text-muted mb-3" style="text-transform:uppercase;letter-spacing:.06em;font-size:.78rem">
        Desglose por categoría
    </h2>
    <div class="row g-3 mb-4">
        <?php foreach ($resumen as $key => $r):
            $pct = $total['bytes'] > 0 ? round($r['bytes'] / $total['bytes'] * 100, 1) : 0;
        ?>
        <div class="col-12 col-sm-6 col-lg-4">
            <a href="<?= APP_URL ?>/admin/almacenamiento?cat=<?= e($key) ?>"
               class="card h-100 text-decoration-none" style="color:var(--color-text)">
                <div class="card-body">
                    <div class="d-flex align-items-start gap-3">
                        <div style="width:40px;height:40px;border-radius:50%;background:<?= e($r['color']) ?>1f;color:<?= e($r['color']) ?>;display:flex;align-items:center;justify-content:center;font-size:1.1rem;flex-shrink:0">
                            <i class="bi bi-<?= e($r['icon']) ?>"></i>
                        </div>
                        <div class="flex-grow-1" style="min-width:0">
                            <div class="fw-semibold" style="font-size:.88rem;color:var(--color-text)"><?= e($r['label']) ?></div>
                            <div class="text-muted" style="font-size:.72rem;overflow:hidden;text-overflow:ellipsis;white-space:nowrap"><code><?= e($r['path']) ?></code></div>
                            <div class="mt-2 d-flex align-items-baseline justify-content-between">
                                <span class="fw-bold" style="font-size:1.2rem"><?= StorageScannerModel::fmtBytes($r['bytes']) ?></span>
                                <small class="text-muted"><?= number_format($r['files']) ?> archivos</small>
                            </div>
                            <div style="height:4px;background:var(--color-bg-alt);border-radius:4px;overflow:hidden;margin-top:.35rem">
                                <div style="height:100%;width:<?= $pct ?>%;background:<?= e($r['color']) ?>"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Top archivos más pesados -->
    <?php if (!$catFilter): ?>
    <h2 class="h6 fw-bold text-muted mb-3" style="text-transform:uppercase;letter-spacing:.06em;font-size:.78rem">
        Archivos más pesados
    </h2>
    <div class="card">
        <div class="table-responsive">
            <table class="table mb-0">
                <thead>
                    <tr>
                        <th>Archivo</th>
                        <th>Categoría</th>
                        <th class="text-end">Tamaño</th>
                        <th>Modificado</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($top)): ?>
                    <tr><td colspan="4" class="text-center text-muted py-4">Sin archivos</td></tr>
                <?php endif; ?>
                <?php foreach ($top as $f): ?>
                    <tr>
                        <td style="font-family:monospace;font-size:.82rem;word-break:break-all"><?= e($f['nombre']) ?></td>
                        <td style="font-size:.82rem"><?= e($f['categoria_label']) ?></td>
                        <td class="text-end fw-semibold"><?= StorageScannerModel::fmtBytes($f['bytes']) ?></td>
                        <td style="font-size:.78rem" class="text-muted"><?= e(Security::timeAgo(date('Y-m-d H:i:s', $f['mtime']))) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php else:
        $label = StorageScannerModel::CATEGORIAS[$catFilter]['label'] ?? $catFilter;
        $imgExts = ['jpg','jpeg','png','webp','gif'];
        $vidExts = ['mp4','webm','mov'];
    ?>
    <?php
        // Rango mostrado actualmente
        $desde = $totalArchivos > 0 ? (($paginaActual - 1) * $perPage) + 1 : 0;
        $hasta = min($paginaActual * $perPage, $totalArchivos);
    ?>
    <!-- Header con toggle lista/galería -->
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
        <h2 class="h6 fw-bold mb-0" style="text-transform:uppercase;letter-spacing:.06em;font-size:.82rem">
            <?= e($label) ?> — <?= number_format($totalArchivos) ?> archivos
            <?php if ($totalPaginas > 1): ?>
                <span class="text-muted fw-normal" style="text-transform:none;letter-spacing:0">
                    · mostrando <?= number_format($desde) ?>–<?= number_format($hasta) ?>
                </span>
            <?php endif; ?>
        </h2>
        <div class="d-flex gap-2">
            <div class="btn-group" role="group">
                <a href="?cat=<?= e($catFilter) ?>&vista=lista"
                   class="btn btn-sm <?= $vista !== 'galeria' ? 'btn-primary' : 'btn-secondary' ?>">
                    <i class="bi bi-list-ul me-1"></i>Lista
                </a>
                <a href="?cat=<?= e($catFilter) ?>&vista=galeria"
                   class="btn btn-sm <?= $vista === 'galeria' ? 'btn-primary' : 'btn-secondary' ?>">
                    <i class="bi bi-grid-3x3-gap-fill me-1"></i>Galería
                </a>
            </div>
            <a href="<?= APP_URL ?>/admin/almacenamiento" class="btn btn-sm btn-secondary">
                <i class="bi bi-x-lg"></i>
            </a>
        </div>
    </div>

    <?php if ($vista === 'galeria'): ?>
    <!-- GALERÍA -->
    <?php if (empty($archivos)): ?>
    <div class="card"><div class="card-body text-center text-muted py-5">Sin archivos en esta categoría</div></div>
    <?php else: ?>
    <?php
        // Construir lista de media (imagen/video) para navegación en lightbox
        $mediaItems = [];
        foreach ($archivos as $idx => $f) {
            $tipo = in_array($f['ext'], $imgExts, true) ? 'img'
                  : (in_array($f['ext'], $vidExts, true) ? 'vid' : null);
            if ($tipo === null) continue;
            $mediaItems[] = [
                'tipo'  => $tipo,
                'url'   => APP_URL . '/admin/archivo?cat=' . urlencode($catFilter) . '&f=' . urlencode($f['nombre']),
                'nombre'=> $f['nombre'],
                'bytes' => StorageScannerModel::fmtBytes($f['bytes']),
                'mtime' => Security::timeAgo(date('Y-m-d H:i:s', $f['mtime'])),
            ];
        }
    ?>
    <div class="storage-gallery" id="storageGallery">
        <?php $mIdx = -1; foreach ($archivos as $f):
            $isImg = in_array($f['ext'], $imgExts, true);
            $isVid = in_array($f['ext'], $vidExts, true);
            $url   = APP_URL . '/admin/archivo?cat=' . urlencode($catFilter) . '&f=' . urlencode($f['nombre']);
            $isMedia = $isImg || $isVid;
            if ($isMedia) $mIdx++;
        ?>
        <figure class="storage-gallery__item">
            <div class="storage-gallery__media<?= $isMedia ? ' storage-gallery__media--clickable' : '' ?>"
                 <?= $isMedia ? 'data-lb-index="' . $mIdx . '"' : '' ?>>
                <?php if ($isImg): ?>
                    <img src="<?= e($url) ?>" alt="" loading="lazy">
                <?php elseif ($isVid): ?>
                    <video src="<?= e($url) ?>#t=0.1" preload="metadata" muted playsinline
                           class="storage-gallery__vthumb"></video>
                    <span class="storage-gallery__playicon"><i class="bi bi-play-fill"></i></span>
                <?php else: ?>
                    <a href="<?= e($url) ?>" target="_blank" rel="noopener" class="storage-gallery__file">
                        <i class="bi bi-file-earmark"></i>
                        <span><?= e(strtoupper($f['ext'])) ?></span>
                    </a>
                <?php endif; ?>
            </div>
            <figcaption class="storage-gallery__cap">
                <span class="storage-gallery__name" title="<?= e($f['nombre']) ?>"><?= e($f['nombre']) ?></span>
                <span class="storage-gallery__meta">
                    <?= StorageScannerModel::fmtBytes($f['bytes']) ?> · <?= e(Security::timeAgo(date('Y-m-d H:i:s', $f['mtime']))) ?>
                </span>
            </figcaption>
        </figure>
        <?php endforeach; ?>
    </div>

    <!-- Lightbox -->
    <div class="storage-lb" id="storageLb" aria-hidden="true" role="dialog">
        <button type="button" class="storage-lb__close" data-lb-close aria-label="Cerrar">
            <i class="bi bi-x-lg"></i>
        </button>
        <button type="button" class="storage-lb__nav storage-lb__nav--prev" data-lb-prev aria-label="Anterior">
            <i class="bi bi-chevron-left"></i>
        </button>
        <button type="button" class="storage-lb__nav storage-lb__nav--next" data-lb-next aria-label="Siguiente">
            <i class="bi bi-chevron-right"></i>
        </button>
        <div class="storage-lb__stage" id="storageLbStage"></div>
        <div class="storage-lb__bar">
            <div class="storage-lb__info">
                <div class="storage-lb__name" id="storageLbName"></div>
                <div class="storage-lb__meta" id="storageLbMeta"></div>
            </div>
            <div class="storage-lb__counter" id="storageLbCounter"></div>
        </div>
    </div>
    <script id="storageLbData" type="application/json"><?= json_encode($mediaItems, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?></script>
    <script src="<?= APP_URL ?>/public/assets/js/storage-lb.js" defer></script>
    <?php endif; ?>

    <?php require VIEWS_PATH . '/admin/_storage-pagination.php'; ?>

    <?php else: ?>
    <!-- LISTA -->
    <div class="card">
        <div class="table-responsive">
            <table class="table mb-0">
                <thead>
                    <tr>
                        <th>Archivo</th>
                        <th>Extensión</th>
                        <th class="text-end">Tamaño</th>
                        <th>Modificado</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($archivos)): ?>
                    <tr><td colspan="5" class="text-center text-muted py-4">Sin archivos en esta categoría</td></tr>
                <?php endif; ?>
                <?php foreach ($archivos as $f):
                    $url = APP_URL . '/admin/archivo?cat=' . urlencode($catFilter) . '&f=' . urlencode($f['nombre']);
                ?>
                    <tr>
                        <td style="font-family:monospace;font-size:.82rem;word-break:break-all"><?= e($f['nombre']) ?></td>
                        <td style="font-size:.78rem"><span class="badge-estado badge-expirado"><?= e($f['ext']) ?></span></td>
                        <td class="text-end fw-semibold"><?= StorageScannerModel::fmtBytes($f['bytes']) ?></td>
                        <td style="font-size:.78rem" class="text-muted"><?= e(Security::timeAgo(date('Y-m-d H:i:s', $f['mtime']))) ?></td>
                        <td class="text-end">
                            <a href="<?= e($url) ?>" target="_blank" rel="noopener"
                               class="btn btn-sm btn-secondary" title="Abrir archivo">
                                <i class="bi bi-box-arrow-up-right"></i>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php require VIEWS_PATH . '/admin/_storage-pagination.php'; ?>

    <?php endif; ?>
    <?php endif; ?>

</div>
