<?php
/**
 * partials/perfil-search.php
 * Barra de búsqueda reutilizable para /perfiles y /perfil/{id}.
 * Espera: $categorias, $estados, y opcionalmente $filtros (con q, id_categoria, id_estado, id_municipio).
 */
$__f         = $filtros ?? [];
$__q         = $__f['buscar']        ?? '';
$__cat       = (int)($__f['id_categoria'] ?? 0);
$__est       = (int)($__f['id_estado']    ?? 0);
$__mun       = (int)($__f['id_municipio'] ?? 0);
$__mpreload  = $municipios ?? [];
?>
<div class="perfil-search-bar">
    <div class="container-fluid" style="max-width:1400px">
        <form action="<?= APP_URL ?>/perfiles" method="GET"
              class="row g-2 align-items-end" id="top-search-form">
            <div class="col-12 col-sm-4">
                <input type="search" name="q" class="form-control"
                       placeholder="Buscar perfil..."
                       value="<?= e($__q) ?>">
            </div>
            <div class="col-6 col-sm-2">
                <select id="ts-estado" name="id_estado" class="form-select">
                    <option value="">Todos los estados</option>
                    <?php foreach (($estados ?? []) as $est): ?>
                    <option value="<?= (int)$est['id'] ?>"
                            <?= (int)$est['id'] === $__est ? 'selected' : '' ?>>
                        <?= e($est['nombre']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-6 col-sm-2">
                <select id="ts-municipio" name="id_municipio" class="form-select"
                        <?= empty($__mpreload) ? 'disabled' : '' ?>>
                    <option value="">Todos los municipios</option>
                    <?php foreach ($__mpreload as $m): ?>
                    <option value="<?= (int)$m['id'] ?>"
                            <?= (int)$m['id'] === $__mun ? 'selected' : '' ?>>
                        <?= e($m['nombre']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-8 col-sm-3">
                <select name="id_categoria" class="form-select">
                    <option value="">Todas las categorías</option>
                    <?php foreach (($categorias ?? []) as $cat): ?>
                    <option value="<?= (int)$cat['id'] ?>"
                            <?= (int)$cat['id'] === $__cat ? 'selected' : '' ?>>
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

<script>
(function(){
    if (window.__perfilSearchBound) return;
    window.__perfilSearchBound = true;
    var BASE_URL   = <?= json_encode(APP_URL) ?>;
    var selEstado  = document.getElementById('ts-estado');
    var selMunicipio = document.getElementById('ts-municipio');
    if (!selEstado || !selMunicipio) return;

    selEstado.addEventListener('change', function(){
        var id = parseInt(this.value, 10);
        selMunicipio.innerHTML = '<option value="">Cargando...</option>';
        selMunicipio.disabled  = true;
        if (!id) {
            selMunicipio.innerHTML = '<option value="">Todos los municipios</option>';
            return;
        }
        fetch(BASE_URL + '/api/municipios/' + id)
            .then(function(r){ return r.json(); })
            .then(function(data){
                selMunicipio.innerHTML = '<option value="">Todos los municipios</option>';
                if (data.success && data.municipios.length) {
                    data.municipios.forEach(function(m){
                        var opt = document.createElement('option');
                        opt.value = m.id;
                        opt.textContent = m.nombre;
                        selMunicipio.appendChild(opt);
                    });
                    selMunicipio.disabled = false;
                }
            })
            .catch(function(){ selMunicipio.innerHTML = '<option value="">Error</option>'; });
    });
})();
</script>
