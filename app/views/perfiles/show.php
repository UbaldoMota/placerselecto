<?php
/**
 * perfiles/show.php — Detalle público de un perfil.
 */
$tieneWA  = !empty($perfil['whatsapp']);
$tieneTG  = !empty($perfil['telegram']);
$tieneEM  = !empty($perfil['email_contacto']);
$tieneZona= !empty($perfil['zona_lat']) && !empty($perfil['zona_lng']);
$tieneContacto = $tieneWA || $tieneTG || $tieneEM;

// Construir URLs para galería y lightbox
$totalFotos   = count($fotos);
$lightboxUrls = array_map(fn($f) => APP_URL . '/img/' . $f['token'], $fotos);

// Fallback legacy
if ($totalFotos === 0 && !empty($perfil['imagen_token'])) {
    $lightboxUrls = [APP_URL . '/img/' . $perfil['imagen_token']];
    $totalFotos   = 1;
} elseif ($totalFotos === 0 && !empty($perfil['imagen_principal'])) {
    $lightboxUrls = [APP_URL . '/uploads/anuncios/' . basename($perfil['imagen_principal'])];
    $totalFotos   = 1;
}

$score      = $confiabilidad['score'];
$totalScore = $confiabilidad['total'];
$pct        = $totalScore > 0 ? round($score / $totalScore * 100) : 0;
$colorScore = $pct >= 75 ? '#10B981' : ($pct >= 40 ? '#F59E0B' : '#FF2D75');
?>

<!-- Barra de búsqueda (persistente al abrir un perfil) -->
<?php require VIEWS_PATH . '/partials/perfil-search.php'; ?>

<div class="container py-4">
    <div class="row g-4">

        <!-- COLUMNA PRINCIPAL -->
        <div class="col-12 col-lg-8">

            <!-- Breadcrumb -->
            <nav aria-label="breadcrumb" class="mb-3">
                <ol class="breadcrumb" style="font-size:.8rem;background:none;padding:0;margin:0">
                    <li class="breadcrumb-item"><a href="<?= APP_URL ?>">Inicio</a></li>
                    <li class="breadcrumb-item"><a href="<?= APP_URL ?>/perfiles<?= !empty($nav) ? '?' . http_build_query($nav['filtros']) : '' ?>">Perfiles</a></li>
                    <li class="breadcrumb-item active text-muted" aria-current="page">
                        <?= e(Security::truncate($perfil['nombre'], 40)) ?>
                    </li>
                </ol>
            </nav>

            <!-- Navegación prev/next respecto a la búsqueda -->
            <?php if (!empty($nav)):
                $qsNav = http_build_query(array_merge($nav['filtros'], ['nav' => '1']));
            ?>
            <div class="perfil-navresult mb-3">
                <?php if ($nav['prev']): ?>
                <a href="<?= APP_URL ?>/perfil/<?= (int)$nav['prev'] ?>?<?= $qsNav ?>"
                   class="btn btn-sm btn-secondary d-flex align-items-center gap-1" title="Perfil anterior">
                    <i class="bi bi-chevron-left"></i><span class="d-none d-sm-inline">Anterior</span>
                </a>
                <?php else: ?>
                <span class="btn btn-sm btn-secondary d-flex align-items-center gap-1 disabled" aria-disabled="true">
                    <i class="bi bi-chevron-left"></i><span class="d-none d-sm-inline">Anterior</span>
                </span>
                <?php endif; ?>

                <a href="<?= APP_URL ?>/perfiles?<?= http_build_query($nav['filtros']) ?>"
                   class="btn btn-sm btn-secondary perfil-navresult__back"
                   title="Volver a los resultados de búsqueda" aria-label="Volver a búsqueda">
                    <i class="bi bi-list-ul"></i>
                </a>

                <?php if ($nav['next']): ?>
                <a href="<?= APP_URL ?>/perfil/<?= (int)$nav['next'] ?>?<?= $qsNav ?>"
                   class="btn btn-sm btn-primary d-flex align-items-center gap-1" title="Siguiente perfil">
                    <span class="d-none d-sm-inline">Siguiente</span><i class="bi bi-chevron-right"></i>
                </a>
                <?php else: ?>
                <span class="btn btn-sm btn-secondary d-flex align-items-center gap-1 disabled" aria-disabled="true">
                    <span class="d-none d-sm-inline">Siguiente</span><i class="bi bi-chevron-right"></i>
                </span>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <!-- Estado (solo si no publicado) -->
            <?php if ($perfil['estado'] !== 'publicado'): ?>
            <div class="alert py-2 px-3 mb-3"
                 style="background:rgba(255,193,7,.08);border:1px solid rgba(255,193,7,.2);font-size:.82rem;color:#ffd44d">
                <?php if ($perfil['estado'] === 'pendiente'): ?>
                    <i class="bi bi-clock me-2"></i>Este perfil está <strong>en revisión</strong> y no es visible para el público.
                <?php else: ?>
                    <i class="bi bi-x-circle me-2"></i>Este perfil fue <strong>rechazado</strong>.
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <!-- 1. NOMBRE Y DESCRIPCIÓN -->
            <div class="card mb-3">
                <div class="card-body p-4">

                    <!-- Meta tags -->
                    <div class="d-flex flex-wrap gap-2 mb-3" style="font-size:.78rem">
                        <span style="color:var(--color-text-muted)">
                            <i class="bi bi-tag text-primary me-1"></i><?= e($perfil['categoria_nombre'] ?? '—') ?>
                        </span>
                        <?php if (!empty($perfil['municipio_nombre']) || !empty($perfil['ciudad'])): ?>
                        <span style="color:var(--color-text-muted)">
                            <i class="bi bi-geo-alt text-primary me-1"></i>
                            <?= e($perfil['municipio_nombre'] ?? '') ?>
                            <?php if (!empty($perfil['estado_nombre'])): ?>, <?= e($perfil['estado_nombre']) ?><?php endif; ?>
                        </span>
                        <?php endif; ?>
                        <span style="color:var(--color-text-muted)">
                            <i class="bi bi-eye text-primary me-1"></i><?= number_format((int)$perfil['vistas']) ?> vistas
                        </span>
                    </div>

                    <!-- Nombre y edad -->
                    <h1 class="h3 fw-bold mb-3">
                        <?= e($perfil['nombre']) ?>
                        <?php if (!empty($perfil['edad']) && (int)($perfil['edad_publica'] ?? 1)): ?>
                        <span class="fw-normal" style="font-size:.75em;color:var(--color-text-muted)">, <?= (int)$perfil['edad'] ?> años</span>
                        <?php endif; ?>
                        <?php if ($perfil['destacado']): ?>
                        <span class="ms-2" style="font-size:.55em;vertical-align:middle;background:rgba(255,193,7,.15);color:#F59E0B;border:1px solid rgba(255,193,7,.3);border-radius:20px;padding:.15em .6em">
                            <i class="bi bi-star-fill me-1"></i>Destacado
                        </span>
                        <?php endif; ?>
                    </h1>

                    <!-- Descripción -->
                    <div class="perfil-descripcion">
                        <?php
                        $desc = $perfil['descripcion'] ?? '';
                        // Compatibilidad: si no tiene etiquetas HTML (texto plano antiguo) usar nl2br
                        if (strip_tags($desc) === $desc) {
                            echo nl2br(e($desc));
                        } else {
                            echo $desc; // ya sanitizado por sanitizeHtml()
                        }
                        ?>
                    </div>

                    <!-- Acciones del propietario -->
                    <?php if ($esPropio): ?>
                    <div class="d-flex gap-2 flex-wrap mt-4 pt-3"
                         style="border-top:1px solid var(--color-border)">
                        <a href="<?= APP_URL ?>/perfil/<?= (int)$perfil['id'] ?>/editar"
                           class="btn btn-sm btn-secondary">
                            <i class="bi bi-pencil me-1"></i>Editar perfil
                        </a>
                        <form method="POST"
                              action="<?= APP_URL ?>/perfil/<?= (int)$perfil['id'] ?>/eliminar">
                            <?= $csrfField ?>
                            <button type="submit" class="btn btn-sm btn-danger"
                                    data-confirm="¿Eliminar este perfil permanentemente?">
                                <i class="bi bi-trash me-1"></i>Eliminar
                            </button>
                        </form>
                    </div>
                    <?php endif; ?>

                    <!-- Reportar (usuarios logueados no propietarios) -->
                    <?php if (!$esPropio && $perfil['estado'] === 'publicado'): ?>
                    <div class="mt-3 pt-3" style="border-top:1px solid var(--color-border)">
                        <?php if ($currentUser): ?>
                        <button type="button" class="btn btn-sm btn-secondary"
                                data-bs-toggle="collapse" data-bs-target="#report-form">
                            <i class="bi bi-flag me-1"></i>Reportar perfil
                        </button>
                        <div class="collapse mt-2" id="report-form">
                            <div class="p-3 rounded" style="background:var(--color-bg-alt);border:1px solid var(--color-border)">
                                <h4 class="fw-bold mb-1" style="font-size:1rem">
                                    <i class="bi bi-flag-fill text-danger me-1"></i>Formulario de denuncia
                                </h4>
                                <p class="text-muted mb-3" style="font-size:.78rem">
                                    Cuéntanos qué pasa con este perfil. Todos los reportes son revisados.
                                </p>

                                <form method="POST" action="<?= APP_URL ?>/perfil/<?= (int)$perfil['id'] ?>/reportar" id="report-perfil-form">
                                    <?= $csrfField ?>

                                    <div class="mb-3">
                                        <label for="rp-motivo" class="form-label" style="font-size:.82rem;font-weight:600">
                                            Asunto <span class="text-danger">*</span>
                                        </label>
                                        <select id="rp-motivo" name="motivo" class="form-select" required>
                                            <option value="">Seleccionar…</option>
                                            <option value="verificar_edad">Verificar edad del usuario</option>
                                            <option value="mal_clasificado">Mal clasificado</option>
                                            <option value="difamaciones">Difamaciones</option>
                                            <option value="fotos_de_internet">Las fotos son de Internet</option>
                                            <option value="fotos_son_mias">Las fotos son mías</option>
                                            <option value="usan_mi_telefono">Usan mi teléfono</option>
                                            <option value="estafa">Estafa</option>
                                            <option value="extorsion">Extorsión</option>
                                            <option value="menor_de_edad">Posible menor de edad</option>
                                            <option value="contenido_ilegal">Contenido ilegal</option>
                                            <option value="otro">Otro</option>
                                        </select>
                                    </div>

                                    <!-- Campo URL visible solo si motivo = fotos_de_internet -->
                                    <div class="mb-3 d-none" id="rp-url-wrap">
                                        <label for="rp-url" class="form-label" style="font-size:.82rem;font-weight:600">
                                            URL de las imágenes en Internet <span class="text-danger">*</span>
                                        </label>
                                        <input type="url" id="rp-url" name="url_referencia" class="form-control"
                                               placeholder="Pon http:// o https:// delante" maxlength="500">
                                        <small class="text-muted" style="font-size:.72rem">
                                            Link a la página donde se encuentran las fotos originales.
                                        </small>
                                    </div>

                                    <div class="mb-3">
                                        <label for="rp-desc" class="form-label" style="font-size:.82rem;font-weight:600">
                                            Comentario <span class="text-danger">*</span>
                                        </label>
                                        <textarea id="rp-desc" name="descripcion" class="form-control"
                                                  rows="4" required minlength="10" maxlength="500"
                                                  placeholder="Describe qué está pasando (mínimo 10 caracteres)…"></textarea>
                                    </div>

                                    <div class="mb-3 form-check">
                                        <input type="checkbox" id="rp-acepto" class="form-check-input" required>
                                        <label for="rp-acepto" class="form-check-label" style="font-size:.78rem">
                                            Acepto la <a href="<?= APP_URL ?>/privacidad" target="_blank">política de privacidad</a>
                                            y <a href="<?= APP_URL ?>/terminos" target="_blank">condiciones de uso</a>.
                                        </label>
                                    </div>

                                    <div class="d-flex gap-2">
                                        <button type="submit" class="btn btn-danger">
                                            <i class="bi bi-send me-1"></i>Enviar denuncia
                                        </button>
                                        <button type="button" class="btn btn-secondary" data-bs-toggle="collapse" data-bs-target="#report-form">
                                            Cancelar
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                        <?php else: ?>
                        <a href="<?= APP_URL ?>/login" class="btn btn-sm btn-secondary">
                            <i class="bi bi-flag me-1"></i>Inicia sesión para reportar
                        </a>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>

                    <?php if (!$esPropio && $currentUser && $perfil['estado'] === 'publicado'): ?>
                    <script>
                    (function(){
                        var sel = document.getElementById('rp-motivo');
                        var urlW = document.getElementById('rp-url-wrap');
                        var urlI = document.getElementById('rp-url');
                        if (!sel || !urlW) return;
                        function toggleUrl() {
                            var needs = sel.value === 'fotos_de_internet';
                            urlW.classList.toggle('d-none', !needs);
                            if (urlI) urlI.required = needs;
                        }
                        sel.addEventListener('change', toggleUrl);
                        toggleUrl();
                    })();
                    </script>
                    <?php endif; ?>

                </div>
            </div>

            <!-- 2. FOTOS -->
            <?php if ($totalFotos > 0): ?>
            <div class="card mb-3">
                <?php if ($totalFotos === 1): ?>
                <div class="foto-galeria--1">
                    <img src="<?= e($lightboxUrls[0]) ?>"
                         alt="<?= e($perfil['nombre']) ?>"
                         loading="eager"
                         onclick="openLightbox(0)"
                         style="cursor:zoom-in">
                </div>
                <?php else: ?>
                <div class="foto-galeria--multi">
                    <?php foreach ($lightboxUrls as $i => $url): ?>
                    <div class="foto-galeria__item" onclick="openLightbox(<?= $i ?>)">
                        <img src="<?= e($url) ?>"
                             alt="<?= e($perfil['nombre']) ?> — foto <?= $i + 1 ?>"
                             loading="<?= $i < 2 ? 'eager' : 'lazy' ?>">
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <!-- Lightbox -->
            <div id="lightbox" class="lightbox" onclick="handleLightboxClick(event)">
                <button class="lightbox__close" onclick="closeLightbox()" title="Cerrar">&times;</button>
                <button class="lightbox__nav lightbox__prev" onclick="lightboxNav(-1,event)" title="Anterior">&#8249;</button>
                <img id="lightbox-img" class="lightbox__img" src="" alt="">
                <button class="lightbox__nav lightbox__next" onclick="lightboxNav(1,event)" title="Siguiente">&#8250;</button>
                <div class="lightbox__counter" id="lightbox-counter"></div>
            </div>
            <script>
            (function(){
                const urls  = <?= json_encode(array_values($lightboxUrls)) ?>;
                const total = urls.length;
                let current = 0;

                window.openLightbox = function(idx) {
                    current = idx;
                    const img = document.getElementById('lightbox-img');
                    const ctr = document.getElementById('lightbox-counter');
                    const lb  = document.getElementById('lightbox');
                    img.src = urls[current] || '';
                    if (ctr) ctr.textContent = total > 1 ? (current + 1) + ' / ' + total : '';
                    lb.classList.add('is-open');
                    document.querySelectorAll('.lightbox__nav').forEach(b => {
                        b.style.display = total > 1 ? '' : 'none';
                    });
                };
                window.closeLightbox = function() {
                    document.getElementById('lightbox').classList.remove('is-open');
                };
                window.lightboxNav = function(dir, e) {
                    if (e) e.stopPropagation();
                    current = (current + dir + total) % total;
                    document.getElementById('lightbox-img').src = urls[current] || '';
                    const ctr = document.getElementById('lightbox-counter');
                    if (ctr) ctr.textContent = (current + 1) + ' / ' + total;
                };
                window.handleLightboxClick = function(e) {
                    if (e.target === document.getElementById('lightbox')) closeLightbox();
                };
                document.addEventListener('keydown', function(e) {
                    if (!document.getElementById('lightbox').classList.contains('is-open')) return;
                    if (e.key === 'Escape')     closeLightbox();
                    if (e.key === 'ArrowRight') lightboxNav(1);
                    if (e.key === 'ArrowLeft')  lightboxNav(-1);
                });
            })();
            </script>

            <!-- 2.b VIDEOS (si el perfil tiene) -->
            <?php if (!empty($videos)): ?>
            <div class="card mb-3">
                <div class="card-header">
                    <span class="fw-semibold" style="font-size:.875rem">
                        <i class="bi bi-play-btn-fill text-primary me-2"></i>Videos <span class="text-muted fw-normal">(<?= count($videos) ?>)</span>
                    </span>
                </div>
                <div class="card-body" style="padding:1rem">
                    <div class="row g-2">
                        <?php foreach ($videos as $v): ?>
                        <div class="col-12 col-sm-6 col-md-<?= count($videos) >= 3 ? '4' : '6' ?>">
                            <div style="background:#000;border-radius:var(--radius-sm);overflow:hidden;aspect-ratio:16/9">
                                <video src="<?= APP_URL . '/video/' . e($v['token']) ?>"
                                       style="width:100%;height:100%;object-fit:contain;background:#000;display:block"
                                       controls preload="metadata" playsinline></video>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- 3. VERIFICACIONES DEL PERFIL -->
            <div class="card mb-3">
                <div class="card-body" style="padding:1rem 1.1rem">
                    <ul class="list-unstyled mb-0" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(180px,1fr));gap:.6rem">
                    <?php foreach ($confiabilidad['indicadores'] as $ind): ?>
                        <li class="d-flex align-items-center gap-2"
                            title="<?= e($ind['descripcion']) ?>"
                            style="cursor:default">
                            <i class="bi <?= e($ind['icono']) ?> flex-shrink-0"
                               style="color:<?= $ind['activo'] ? '#10B981' : 'var(--color-text-muted)' ?>;opacity:<?= $ind['activo'] ? '1' : '.35' ?>;font-size:.95rem"></i>
                            <span style="font-size:.78rem;color:<?= $ind['activo'] ? 'var(--color-text)' : 'var(--color-text-muted)' ?>">
                                <?= e($ind['label']) ?>
                            </span>
                        </li>
                    <?php endforeach; ?>
                    </ul>
                </div>
            </div>

            <!-- 4. COMENTARIOS -->
            <div class="card mb-3" id="comentarios">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <span class="fw-semibold" style="font-size:.875rem">
                        <i class="bi bi-chat-square-text-fill text-primary me-2"></i>Comentarios
                        <span class="text-muted fw-normal">(<?= (int)$comPromedio['total'] ?>)</span>
                    </span>
                    <?php if ($comPromedio['total'] > 0): ?>
                    <span class="d-inline-flex align-items-center gap-1" style="color:#F59E0B;font-weight:700">
                        <i class="bi bi-star-fill"></i>
                        <?= number_format($comPromedio['promedio'], 1) ?>
                        <small class="text-muted fw-normal">/ 5</small>
                    </span>
                    <?php endif; ?>
                </div>

                <div class="card-body" style="padding:1rem">

                    <!-- Form (si logueado y no es el dueño) -->
                    <?php
                    $esAdminUser = $currentUser && ($currentUser['rol'] ?? '') === 'admin';
                    $yaComentado = $miComentario !== null;
                    // Si el comentario está eliminado y el cooldown expiró, puede comentar otra vez
                    $cooldownVigente = $yaComentado
                        && $miComentario['estado'] === 'eliminado'
                        && !empty($miComentario['fecha_cooldown_hasta'])
                        && strtotime($miComentario['fecha_cooldown_hasta']) > time();
                    $yaComentadoActivo = $yaComentado && $miComentario['estado'] !== 'eliminado';
                    $puedeComentar = !$esPropio && $currentUser && !$esAdminUser
                                     && !$yaComentadoActivo && !$cooldownVigente;
                    ?>
                    <?php
                    // Caso especial: cooldown vigente por eliminación
                    if ($cooldownVigente):
                        $cdRest = strtotime($miComentario['fecha_cooldown_hasta']) - time();
                    ?>
                    <div class="mb-3 pb-3 border-bottom" style="border-color:var(--color-border) !important">
                        <div class="p-2 rounded mb-2"
                             style="background:rgba(245,158,11,.08);border:1px solid rgba(245,158,11,.25);color:#92400E;font-size:.82rem">
                            <i class="bi bi-hourglass-split me-1"></i>
                            Eliminaste tu comentario. Podrás dejar uno nuevo en
                            <strong>
                                <?php
                                if ($cdRest >= 86400) {
                                    $d = (int)ceil($cdRest / 86400);
                                    echo $d . ' día' . ($d > 1 ? 's' : '');
                                } else {
                                    $h = (int)ceil($cdRest / 3600);
                                    echo $h . ' hora' . ($h > 1 ? 's' : '');
                                }
                                ?>
                            </strong>.
                        </div>
                    </div>
                    <?php elseif ($yaComentadoActivo && !$esPropio && !$esAdminUser):
                        $estadoInfo = [
                            'pendiente' => ['bg'=>'rgba(245,158,11,.08)','bd'=>'rgba(245,158,11,.25)','fg'=>'#92400E','icon'=>'hourglass-split','label'=>'Tu comentario está pendiente de aprobación por el equipo.'],
                            'publicado' => ['bg'=>'rgba(16,185,129,.08)','bd'=>'rgba(16,185,129,.25)','fg'=>'#065F46','icon'=>'check-circle-fill','label'=>'Tu comentario fue aprobado y es visible públicamente.'],
                            'reportado' => ['bg'=>'rgba(239,68,68,.08)','bd'=>'rgba(239,68,68,.25)','fg'=>'#991B1B','icon'=>'flag-fill','label'=>'Tu comentario fue reportado y está en revisión.'],
                            'oculto'    => ['bg'=>'rgba(153,153,153,.10)','bd'=>'rgba(153,153,153,.25)','fg'=>'#555','icon'=>'eye-slash-fill','label'=>'Tu comentario fue ocultado por un administrador.'],
                        ];
                        $ei = $estadoInfo[$miComentario['estado']] ?? $estadoInfo['pendiente'];
                    ?>
                    <!-- Ya comentó: vista solo lectura (no editable) -->
                    <div class="mb-3 pb-3 border-bottom" style="border-color:var(--color-border) !important">
                        <div class="p-2 rounded mb-2"
                             style="background:<?= $ei['bg'] ?>;border:1px solid <?= $ei['bd'] ?>;color:<?= $ei['fg'] ?>;font-size:.78rem">
                            <i class="bi bi-<?= e($ei['icon']) ?> me-1"></i>
                            <?= e($ei['label']) ?>
                            <div style="font-size:.72rem;opacity:.9;margin-top:.15rem">
                                Los comentarios no se pueden editar. Si quieres cambiarlo, elimínalo y escribe uno nuevo.
                            </div>
                        </div>
                        <div class="p-3 rounded mb-2" style="background:var(--color-bg-alt);border:1px solid var(--color-border)">
                            <div style="color:#F59E0B;font-size:.82rem">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <i class="bi <?= $i <= $miComentario['calificacion'] ? 'bi-star-fill' : 'bi-star' ?>"></i>
                                <?php endfor; ?>
                            </div>
                            <p class="mb-0 mt-1" style="font-size:.88rem;white-space:pre-wrap"><?= nl2br(e($miComentario['comentario'])) ?></p>
                        </div>
                        <form method="POST" action="<?= APP_URL ?>/comentario/<?= (int)$miComentario['id'] ?>/eliminar" class="m-0"
                              onsubmit="return confirm('¿Eliminar tu comentario? Luego podrás escribir uno nuevo.')">
                            <?= $csrfField ?>
                            <button type="submit" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-trash me-1"></i>Eliminar comentario
                            </button>
                        </form>
                    </div>
                    <?php elseif ($puedeComentar): ?>
                    <form method="POST" action="<?= APP_URL ?>/perfil/<?= (int)$perfil['id'] ?>/comentar"
                          class="mb-3 pb-3 border-bottom" style="border-color:var(--color-border) !important">
                        <?= $csrfField ?>
                        <label class="form-label" style="font-size:.82rem;font-weight:600">
                            Deja tu comentario
                        </label>
                        <div class="rating-stars mb-2" data-selected="5">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                            <button type="button" class="rating-star is-active"
                                    data-v="<?= $i ?>" aria-label="<?= $i ?> estrella<?= $i > 1 ? 's' : '' ?>">
                                <i class="bi bi-star-fill"></i>
                            </button>
                            <?php endfor; ?>
                            <input type="hidden" name="calificacion" value="5">
                        </div>

                        <textarea name="comentario" class="form-control" rows="3"
                                  minlength="10" maxlength="2000" required
                                  placeholder="Comparte tu experiencia (mínimo 10 caracteres)..."></textarea>
                        <div class="d-flex justify-content-between align-items-center mt-2 flex-wrap gap-2">
                            <small class="text-muted" style="font-size:.72rem">
                                <i class="bi bi-shield-check me-1"></i>Todos los comentarios pasan por revisión antes de publicarse. Una vez enviado, no podrás editarlo.
                            </small>
                            <button type="submit" class="btn btn-primary btn-sm">
                                <i class="bi bi-send me-1"></i>Enviar a revisión
                            </button>
                        </div>
                    </form>
                    <?php elseif (!$currentUser): ?>
                    <div class="alert alert-info py-2 px-3 mb-3" style="font-size:.85rem">
                        <i class="bi bi-info-circle me-1"></i>
                        <a href="<?= APP_URL ?>/login">Inicia sesión</a> o
                        <a href="<?= APP_URL ?>/registro/comentarista">crea una cuenta</a>
                        para dejar un comentario.
                    </div>
                    <?php endif; ?>

                    <!-- Lista de comentarios -->
                    <?php if (empty($comentarios)): ?>
                        <div class="text-center text-muted py-4" style="font-size:.85rem">
                            <i class="bi bi-chat-square" style="font-size:1.8rem"></i>
                            <p class="mt-2 mb-0">Todavía no hay comentarios. Sé el primero.</p>
                        </div>
                    <?php else: ?>
                        <div class="d-flex flex-column gap-3">
                            <?php foreach ($comentarios as $c): ?>
                            <?php $esMio = $currentUser && (int)$c['id_usuario'] === (int)$currentUser['id']; ?>
                            <div class="comentario">
                                <div class="d-flex align-items-start gap-2">
                                    <div class="comentario__avatar"><?= strtoupper(mb_substr($c['autor_nombre'] ?? '?', 0, 1)) ?></div>
                                    <div class="flex-grow-1" style="min-width:0">
                                        <div class="d-flex justify-content-between align-items-start gap-2 flex-wrap">
                                            <div>
                                                <strong style="font-size:.88rem"><?= e($c['autor_nombre']) ?></strong>
                                                <div style="color:#F59E0B;font-size:.78rem">
                                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                                        <i class="bi <?= $i <= $c['calificacion'] ? 'bi-star-fill' : 'bi-star' ?>"></i>
                                                    <?php endfor; ?>
                                                </div>
                                            </div>
                                            <small class="text-muted" style="font-size:.72rem"><?= e(Security::timeAgo($c['fecha_creacion'])) ?></small>
                                        </div>
                                        <p class="mb-1 mt-1" style="font-size:.86rem;white-space:pre-wrap"><?= nl2br(e($c['comentario'])) ?></p>
                                        <div class="d-flex gap-2" style="font-size:.72rem">
                                            <?php if ($esMio): ?>
                                            <form method="POST" action="<?= APP_URL ?>/comentario/<?= (int)$c['id'] ?>/eliminar" class="m-0"
                                                  onsubmit="return confirm('¿Eliminar tu comentario?')">
                                                <?= $csrfField ?>
                                                <button type="submit" class="btn btn-link btn-sm p-0 text-danger" style="font-size:.72rem;text-decoration:none">
                                                    <i class="bi bi-trash"></i> Eliminar
                                                </button>
                                            </form>
                                            <?php elseif ($currentUser): ?>
                                            <form method="POST" action="<?= APP_URL ?>/comentario/<?= (int)$c['id'] ?>/reportar" class="m-0"
                                                  onsubmit="return confirm('¿Reportar este comentario como abusivo o inapropiado?')">
                                                <?= $csrfField ?>
                                                <button type="submit" class="btn btn-link btn-sm p-0 text-muted" style="font-size:.72rem;text-decoration:none">
                                                    <i class="bi bi-flag"></i> Reportar
                                                </button>
                                            </form>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                </div>
            </div>

            <!-- Perfiles relacionados -->
            <?php if (!empty($relacionados)): ?>
            <div class="mt-2">
                <h2 class="h6 fw-bold mb-3 text-muted text-uppercase" style="letter-spacing:.5px">
                    <i class="bi bi-grid me-2"></i>Perfiles similares
                </h2>
                <div class="row g-3">
                    <?php foreach ($relacionados as $rel):
                        $relImg = !empty($rel['imagen_token'])
                            ? APP_URL . '/img/' . $rel['imagen_token']
                            : null;
                    ?>
                    <div class="col-6 col-sm-3">
                        <a href="<?= APP_URL ?>/perfil/<?= (int)$rel['id'] ?>"
                           class="text-decoration-none">
                            <div class="ad-card">
                                <div class="ad-card__image">
                                    <?php if ($relImg): ?>
                                        <img src="<?= e($relImg) ?>" alt="" loading="lazy">
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
                                    <div class="ad-card__title"><?= e($rel['nombre']) ?></div>
                                    <div class="ad-card__meta">
                                        <span><i class="bi bi-geo-alt"></i><?= e($rel['municipio_nombre'] ?? $rel['ciudad'] ?? '—') ?></span>
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

            <!-- Medios de contacto -->
            <?php if ($tieneContacto): ?>
            <div class="card mb-4 contact-card">
                <div class="card-body" style="padding:1rem">

                    <!-- Badge de confianza arriba -->
                    <?php if ($perfil['pide_anticipo'] ?? false): ?>
                    <div class="contact-trust contact-trust--warn mb-3">
                        <i class="bi bi-info-circle"></i>
                        <div>
                            <strong>Solicita pago anticipado</strong>
                            <div style="font-size:.7rem;opacity:.9">Te recomendamos confirmar en persona antes de pagar.</div>
                        </div>
                    </div>
                    <?php else: ?>
                    <div class="contact-trust contact-trust--safe mb-3">
                        <i class="bi bi-shield-check-fill"></i>
                        <div>
                            <strong>No solicita anticipo</strong>
                            <div style="font-size:.72rem;opacity:.9">Puedes acordar el pago directamente.</div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <div class="text-muted mb-2" style="font-size:.72rem;text-transform:uppercase;letter-spacing:.06em;font-weight:600">
                        <i class="bi bi-chat-dots me-1"></i>Medios de contacto
                    </div>

                    <div class="d-flex flex-column gap-2">
                        <?php if ($tieneWA): ?>
                        <a href="<?= APP_URL ?>/perfil/<?= (int)$perfil['id'] ?>/whatsapp"
                           target="_blank" rel="noopener noreferrer"
                           class="contact-cta contact-cta--whatsapp">
                            <div class="contact-cta__icon"><i class="bi bi-whatsapp"></i></div>
                            <div class="contact-cta__body">
                                <div class="contact-cta__title">Escríbele por WhatsApp</div>
                                <div class="contact-cta__sub">Abre el chat directamente</div>
                            </div>
                            <i class="bi bi-arrow-right contact-cta__arrow"></i>
                        </a>
                        <?php endif; ?>

                        <?php if ($tieneTG): ?>
                        <a href="https://t.me/<?= e($perfil['telegram']) ?>"
                           target="_blank" rel="noopener noreferrer"
                           class="contact-cta contact-cta--telegram">
                            <div class="contact-cta__icon"><i class="bi bi-telegram"></i></div>
                            <div class="contact-cta__body">
                                <div class="contact-cta__title">Chat en Telegram</div>
                                <div class="contact-cta__sub">@<?= e($perfil['telegram']) ?></div>
                            </div>
                            <i class="bi bi-arrow-right contact-cta__arrow"></i>
                        </a>
                        <?php endif; ?>

                        <?php if ($tieneEM): ?>
                        <a href="mailto:<?= e($perfil['email_contacto']) ?>"
                           class="contact-cta contact-cta--email">
                            <div class="contact-cta__icon"><i class="bi bi-envelope-fill"></i></div>
                            <div class="contact-cta__body">
                                <div class="contact-cta__title">Enviar correo</div>
                                <div class="contact-cta__sub"><?= e($perfil['email_contacto']) ?></div>
                            </div>
                            <i class="bi bi-arrow-right contact-cta__arrow"></i>
                        </a>
                        <?php endif; ?>
                    </div>

                </div>
            </div>
            <?php endif; ?>

            <!-- Zona de trabajo -->
            <?php if ($tieneZona): ?>
            <div class="card mb-4">
                <div class="card-header">
                    <span class="fw-semibold" style="font-size:.875rem">
                        <i class="bi bi-geo-alt text-primary me-2"></i>Zona de trabajo
                    </span>
                </div>
                <div class="card-body" style="padding:.75rem">
                    <?php if (!empty($perfil['zona_descripcion'])): ?>
                    <p class="text-muted mb-2" style="font-size:.8rem">
                        <i class="bi bi-pin-map me-1"></i><?= e($perfil['zona_descripcion']) ?>
                        <span class="ms-1" style="color:var(--color-primary)">· <?= (int)$perfil['zona_radio'] ?> km de radio</span>
                    </p>
                    <?php endif; ?>
                    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
                    <div id="mapa-show" style="height:200px;border-radius:8px;overflow:hidden"></div>
                    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
                    <script>
                    (function(){
                        const m = L.map('mapa-show', {zoomControl:false, dragging:false, scrollWheelZoom:false, doubleClickZoom:false, touchZoom:false, keyboard:false, attributionControl:false})
                            .setView([<?= (float)$perfil['zona_lat'] ?>, <?= (float)$perfil['zona_lng'] ?>], 12);
                        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(m);
                        L.circle([<?= (float)$perfil['zona_lat'] ?>, <?= (float)$perfil['zona_lng'] ?>], {
                            radius: <?= (int)$perfil['zona_radio'] ?> * 1000,
                            color:'#FF2D75', fillColor:'#FF2D75', fillOpacity:.2, weight:2
                        }).addTo(m);
                        const ic = L.divIcon({className:'',html:'<div style="width:16px;height:16px;background:#FF2D75;border:3px solid #fff;border-radius:50%;box-shadow:0 2px 6px rgba(0,0,0,.4)"></div>',iconSize:[16,16],iconAnchor:[8,8]});
                        L.marker([<?= (float)$perfil['zona_lat'] ?>, <?= (float)$perfil['zona_lng'] ?>], {icon:ic}).addTo(m);
                        setTimeout(()=>m.invalidateSize(),200);
                    })();
                    </script>
                </div>
            </div>
            <?php endif; ?>

            <!-- Información del perfil -->
            <div class="card">
                <div class="card-body">
                    <h3 class="h6 fw-bold mb-3">
                        <i class="bi bi-info-circle text-primary me-2"></i>Información
                    </h3>
                    <ul class="list-unstyled mb-0" style="font-size:.82rem">
                        <li class="d-flex justify-content-between py-2" style="border-bottom:1px solid var(--color-border)">
                            <span class="text-muted">Categoría</span>
                            <span><?= e($perfil['categoria_nombre'] ?? '—') ?></span>
                        </li>
                        <?php if (!empty($perfil['edad']) && (int)($perfil['edad_publica'] ?? 1)): ?>
                        <li class="d-flex justify-content-between py-2" style="border-bottom:1px solid var(--color-border)">
                            <span class="text-muted">Edad</span>
                            <span><?= (int)$perfil['edad'] ?> años</span>
                        </li>
                        <?php endif; ?>
                        <li class="d-flex justify-content-between py-2" style="border-bottom:1px solid var(--color-border)">
                            <span class="text-muted">Ubicación</span>
                            <span class="text-end">
                                <?php
                                $loc = array_filter([
                                    $perfil['municipio_nombre'] ?? null,
                                    $perfil['estado_nombre'] ?? null
                                ]);
                                echo $loc ? e(implode(', ', $loc)) : e($perfil['ciudad'] ?? '—');
                                ?>
                            </span>
                        </li>
                        <li class="d-flex justify-content-between py-2" style="border-bottom:1px solid var(--color-border)">
                            <span class="text-muted">Vistas</span>
                            <span><?= number_format((int)$perfil['vistas']) ?></span>
                        </li>
                        <li class="d-flex justify-content-between py-2">
                            <span class="text-muted">Anticipo</span>
                            <?php if ($perfil['pide_anticipo'] ?? false): ?>
                            <span style="color:#e57373;font-size:.82rem"><i class="bi bi-exclamation-triangle-fill me-1"></i>Solicita</span>
                            <?php else: ?>
                            <span style="color:#66bb6a;font-size:.82rem"><i class="bi bi-shield-check me-1"></i>No solicita</span>
                            <?php endif; ?>
                        </li>
                    </ul>
                </div>
            </div>

        </div>
    </div>
</div>
