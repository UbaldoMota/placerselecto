/**
 * municipios-cascada.js
 * Cascada estado → municipio para cualquier página.
 * Requiere elementos con id="id_estado" y id="id_municipio".
 * Opcional: id="municipio-loading" (spinner).
 */
(function () {
    const urlMeta = document.querySelector('meta[name="app-url"]');
    const BASE_URL = urlMeta ? urlMeta.getAttribute('content') : '';

    const selEstado    = document.getElementById('id_estado');
    const selMunicipio = document.getElementById('id_municipio');
    const loading      = document.getElementById('municipio-loading');
    if (!selEstado || !selMunicipio) return;

    // No duplicar: si ya tiene listener de otro script (perfil-create, perfil-edit, perfil-search),
    // marcamos y salimos.
    if (selEstado.dataset.cascadaBound) return;
    selEstado.dataset.cascadaBound = '1';

    selEstado.addEventListener('change', function () {
        const idEstado = parseInt(this.value, 10);
        selMunicipio.innerHTML = '<option value="">— Cargando… —</option>';
        selMunicipio.disabled  = true;
        if (!idEstado) {
            selMunicipio.innerHTML = '<option value="">— Primero selecciona estado —</option>';
            return;
        }
        if (loading) loading.classList.remove('d-none');
        fetch(BASE_URL + '/api/municipios/' + idEstado)
            .then(r => r.json())
            .then(data => {
                if (loading) loading.classList.add('d-none');
                selMunicipio.innerHTML = '<option value="">— Selecciona municipio —</option>';
                if (data.success && data.municipios.length) {
                    data.municipios.forEach(m => {
                        const opt = document.createElement('option');
                        opt.value       = m.id;
                        opt.textContent = m.nombre;
                        selMunicipio.appendChild(opt);
                    });
                    selMunicipio.disabled = false;
                } else {
                    selMunicipio.innerHTML = '<option value="">Sin municipios</option>';
                }
            })
            .catch(() => {
                if (loading) loading.classList.add('d-none');
                selMunicipio.innerHTML = '<option value="">Error al cargar</option>';
            });
    });
})();
