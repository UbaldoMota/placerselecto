<?php
/**
 * home/index.php — Página de inicio pública.
 */
?>

<?php if (function_exists('promoLanzamientoVigente') && promoLanzamientoVigente()): ?>
<!-- BANNER FLOTANTE PROMO (sticky top, compacto en mobile, cerrable) -->
<style>
    .promo-banner-2026 {
        position: sticky;
        top: 0;
        z-index: 1080;
        background:
            radial-gradient(circle at 15% 50%, rgba(255,255,255,.10) 0%, transparent 35%),
            linear-gradient(115deg,
                #06B6D4 0%, #3B82F6 22%, #6366F1 42%,
                #A855F7 62%, #EC4899 82%, #FF2D75 100%);
        background-size: 240% 240%;
        animation: promoGradient 14s ease infinite;
        color: #FFFFFF;
        padding: .65rem 1rem .55rem;
        font-size: .85rem;
        font-weight: 500;
        box-shadow: 0 3px 12px rgba(168,85,247,.30);
        border-bottom: 1px solid rgba(255,255,255,.18);
        overflow: hidden;
    }
    @keyframes promoGradient {
        0%, 100% { background-position: 0% 50%; }
        50%      { background-position: 100% 50%; }
    }
    .promo-banner-2026 .promo-cta {
        background: linear-gradient(135deg, #FFFFFF 0%, #FFE0EC 100%);
        color: #C2185B;
        padding: .25rem .7rem;
        border-radius: 14px;
        font-size: .78rem;
        font-weight: 800;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: .2rem;
        box-shadow: 0 3px 10px rgba(255,45,117,.40);
        white-space: nowrap;
    }
    .promo-banner-2026 .promo-cta:hover {
        color: #C2185B;
        transform: translateY(-1px);
    }
    .promo-banner-2026 .promo-highlight {
        color: #FFF59D;
        font-weight: 800;
    }
    .promo-banner-2026 .promo-sub {
        font-size: .72rem;
        opacity: .85;
        font-weight: 400;
    }
    .promo-close {
        position: absolute;
        top: 4px;
        right: 6px;
        background: rgba(255,255,255,.25);
        border: 0;
        color: #fff;
        width: 22px;
        height: 22px;
        border-radius: 50%;
        font-size: .85rem;
        line-height: 1;
        cursor: pointer;
        padding: 0;
    }
    .promo-close:hover { background: rgba(255,255,255,.4); }
    /* MOBILE: super compacto — solo 1-2 lineas, sin sub, fonts pequeños */
    @media (max-width: 767.98px) {
        .promo-banner-2026 {
            padding: .45rem 2.2rem .45rem .75rem; /* margin derecho para botón cerrar */
            font-size: .76rem;
            line-height: 1.3;
        }
        .promo-banner-2026 .promo-cta {
            padding: .18rem .55rem;
            font-size: .7rem;
        }
        .promo-banner-2026 .promo-sub-mobile-hide {
            display: none;
        }
    }
</style>

<div class="promo-banner-2026" id="promoBanner">
    <button type="button" class="promo-close" aria-label="Cerrar"
            onclick="document.getElementById('promoBanner').style.display='none';try{sessionStorage.setItem('promoCerrado','1');}catch(e){}">
        ×
    </button>
    <div class="container" style="max-width:1100px">
        <div class="d-flex flex-wrap align-items-center justify-content-center gap-2" style="row-gap:.3rem">
            <span>
                🎁 <strong>Las primeras 50</strong> reciben
                <span class="promo-highlight"><?= number_format((int)PROMO_LANZAMIENTO_TOKENS) ?> tokens GRATIS</span>
            </span>
            <?php if (empty($currentUser)): ?>
            <a href="<?= APP_URL ?>/registro" class="promo-cta">
                Inscríbete <i class="bi bi-arrow-right"></i>
            </a>
            <?php endif; ?>
        </div>
        <div class="text-center mt-1 promo-sub promo-sub-mobile-hide">
            <i class="bi bi-eye-fill me-1"></i>
            ¿Eres visitante? Ten paciencia — sumamos perfiles a diario.
        </div>
    </div>
</div>

<script>
// Recordar que el usuario cerró el banner durante esta sesión del navegador
(function(){
    try {
        if (sessionStorage.getItem('promoCerrado') === '1') {
            var b = document.getElementById('promoBanner');
            if (b) b.style.display = 'none';
        }
    } catch(e) {}
})();
</script>
<?php endif; ?>

<!-- HERO -->
<section class="hero-section" style="padding:1.5rem 0">
    <div class="container">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">

            <!-- Izquierda: título + pills abajo -->
            <div class="hero-left">
                <div class="mb-1" style="font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:1px;color:var(--color-primary)">
                    <i class="bi bi-shield-check me-1"></i>Solo mayores de 18 años
                </div>
                <h1 class="fw-black mb-2" style="font-size:clamp(1.4rem,3vw,2rem);line-height:1.2">
                    Perfiles para <span class="text-gradient">adultos</span> en México
                </h1>
                <p class="text-muted mb-3" style="font-size:.82rem">
                    Plataforma segura, verificada y 100% discreta
                </p>
                <!-- Pills (debajo del texto, separadas de los botones) -->
                <div class="d-flex flex-wrap gap-2">
                    <?php
                    $pills = [
                        ['texto' => 'Perfiles verificados',  'icono' => 'bi-patch-check-fill'],
                        ['texto' => 'Contacto directo',      'icono' => 'bi-chat-dots-fill'],
                        ['texto' => 'Discreto y seguro',     'icono' => 'bi-shield-lock-fill'],
                    ];
                    foreach ($pills as $p):
                    ?>
                    <div class="hero-pill">
                        <i class="bi <?= e($p['icono']) ?>"></i>
                        <span><?= e($p['texto']) ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Derecha: solo botones de acción -->
            <div class="hero-actions d-flex gap-2 flex-shrink-0">
                <a href="<?= APP_URL ?>/perfiles" class="btn btn-primary">
                    <i class="bi bi-person-lines-fill me-1"></i>Ver perfiles
                </a>
                <?php if (!$currentUser): ?>
                <a href="<?= APP_URL ?>/registro" class="btn btn-secondary">
                    <i class="bi bi-person-plus me-1"></i>Registrarme
                </a>
                <?php else: ?>
                <a href="<?= APP_URL ?>/perfil/nuevo" class="btn btn-secondary">
                    <i class="bi bi-plus-lg me-1"></i>Crear perfil
                </a>
                <?php endif; ?>
            </div>

        </div>
    </div>
</section>

<!-- BÚSQUEDA RÁPIDA -->
<div style="background:var(--color-bg-card2);border-bottom:1px solid var(--color-border);padding:1.25rem 0">
    <div class="container">
        <form action="<?= APP_URL ?>/perfiles" method="GET" class="row g-2 align-items-end">
            <div class="col-12 col-sm-4">
                <input type="search" name="q" class="form-control"
                       placeholder="Buscar perfil..." value="<?= e($_GET['q'] ?? '') ?>">
            </div>
            <div class="col-6 col-sm-2">
                <select id="hs-estado" name="id_estado" class="form-select">
                    <option value="">Todos los estados</option>
                    <?php foreach ($estados as $est): ?>
                    <option value="<?= (int)$est['id'] ?>"><?= e($est['nombre']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-6 col-sm-2">
                <select id="hs-municipio" name="id_municipio" class="form-select" disabled>
                    <option value="">Todos los municipios</option>
                </select>
            </div>
            <div class="col-8 col-sm-3">
                <select name="id_categoria" class="form-select">
                    <option value="">Todas las categorías</option>
                    <?php foreach ($categorias as $cat): ?>
                    <option value="<?= (int)$cat['id'] ?>"><?= e($cat['nombre']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-4 col-sm-1">
                <button class="btn btn-primary w-100" type="submit">
                    <i class="bi bi-search"></i>
                </button>
            </div>
        </form>
    </div>
</div>

<script src="<?= APP_URL ?>/public/assets/js/home.js" defer></script>

<div class="container py-5">

    <!-- CATEGORÍAS -->
    <section class="mb-5">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h2 class="h5 fw-bold mb-0">Explorar por categoría</h2>
                <p class="text-muted mb-0" style="font-size:.78rem">Elige la categoría que buscas</p>
            </div>
            <a href="<?= APP_URL ?>/perfiles" class="btn btn-sm btn-secondary">
                Ver todas <i class="bi bi-arrow-right ms-1"></i>
            </a>
        </div>

        <div class="cat-grid">
            <?php foreach ($categorias as $cat): ?>
            <a href="<?= APP_URL ?>/perfiles?id_categoria=<?= (int)$cat['id'] ?>"
               class="cat-tile text-decoration-none">
                <div class="cat-tile__icon">
                    <i class="bi <?= e($cat['icono'] ?? 'bi-person') ?>"></i>
                </div>
                <div class="cat-tile__name"><?= e($cat['nombre']) ?></div>
            </a>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- PERFILES DESTACADOS -->
    <section>
        <div class="d-flex align-items-center justify-content-between mb-3">
            <h2 class="h5 fw-bold mb-0">
                <i class="bi bi-fire text-primary me-2"></i>Perfiles destacados
            </h2>
            <a href="<?= APP_URL ?>/perfiles" class="btn btn-sm btn-secondary">
                Ver todos <i class="bi bi-arrow-right ms-1"></i>
            </a>
        </div>

        <?php if (empty($perfiles)): ?>
        <div class="text-center py-5">
            <i class="bi bi-person-x" style="font-size:3rem;color:var(--color-border)"></i>
            <p class="text-muted mt-3">Aún no hay perfiles publicados. ¡Sé el primero!</p>
            <a href="<?= APP_URL ?>/registro" class="btn btn-primary">Crear perfil</a>
        </div>
        <?php else: ?>
        <div class="row g-3">
            <?php foreach ($perfiles as $p):
                $imgUrl = !empty($p['imagen_token'])
                    ? APP_URL . '/img/' . $p['imagen_token'] . '?size=medium'
                    : null;
            ?>
            <div class="col-6 col-sm-4 col-md-3">
                <a href="<?= APP_URL ?>/perfil/<?= (int)$p['id'] ?>"
                   class="text-decoration-none d-block h-100">
                    <?php
                        $boostCls = [];
                        if (!empty($p['boost_top']))         $boostCls[] = 'ad-card--top';
                        if (!empty($p['boost_resaltado']))   $boostCls[] = 'ad-card--resaltado';
                    ?>
                    <div class="ad-card h-100 <?= implode(' ', $boostCls) ?>">
                        <div class="ad-card__image">
                            <?php if ($imgUrl): ?>
                                <img src="<?= e($imgUrl) ?>"
                                     alt="<?= e($p['nombre']) ?>"
                                     loading="lazy">
                            <?php else: ?>
                                <div class="no-image"><i class="bi bi-person"></i></div>
                            <?php endif; ?>
                            <?php if (!empty($p['boost_top'])): ?>
                                <span class="ad-card__badge-destacado">
                                    <i class="bi bi-arrow-up-square-fill me-1"></i>Top
                                </span>
                            <?php elseif (!empty($p['boost_resaltado'])): ?>
                                <span class="ad-card__badge-destacado" style="background:var(--color-warning);box-shadow:none">
                                    <i class="bi bi-stars me-1"></i>Resaltado
                                </span>
                            <?php endif; ?>
                        </div>
                        <div class="ad-card__body">
                            <div class="ad-card__title">
                                <?= e($p['nombre']) ?>
                                <?php if (!empty($p['edad']) && (int)($p['edad_publica'] ?? 1)): ?>
                                <span class="text-muted fw-normal">, <?= (int)$p['edad'] ?></span>
                                <?php endif; ?>
                            </div>
                            <?php if (!empty($p['es_demo'])): ?>
                            <div class="ad-card__demo" style="display:inline-flex;align-items:center;gap:.3rem;color:#0DCAF0;font-size:.72rem;font-weight:600;margin:.15rem 0 .25rem">
                                <i class="bi bi-info-circle-fill"></i><span>Perfil de muestra</span>
                            </div>
                            <?php endif; ?>
                            <div class="ad-card__meta">
                                <span><i class="bi bi-geo-alt"></i><?= e($p['municipio_nombre'] ?? $p['ciudad'] ?? '—') ?></span>
                                <span><i class="bi bi-tag"></i><?= e($p['categoria_nombre'] ?? '') ?></span>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
            <?php endforeach; ?>
        </div>

        <div class="text-center mt-4">
            <a href="<?= APP_URL ?>/perfiles" class="btn btn-outline-primary">
                <i class="bi bi-grid me-2"></i>Ver todos los perfiles
            </a>
        </div>
        <?php endif; ?>
    </section>

</div>
