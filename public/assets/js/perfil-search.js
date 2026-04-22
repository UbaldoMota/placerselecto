(function(){
    if (window.__perfilSearchBound) return;
    window.__perfilSearchBound = true;

    const urlMeta = document.querySelector('meta[name="app-url"]');
    const BASE_URL = urlMeta ? urlMeta.getAttribute('content') : '';

    const selEstado    = document.getElementById('ts-estado');
    const selMunicipio = document.getElementById('ts-municipio');
    if (!selEstado || !selMunicipio) return;

    selEstado.addEventListener('change', function(){
        const id = parseInt(this.value, 10);
        selMunicipio.innerHTML = '<option value="">Cargando...</option>';
        selMunicipio.disabled  = true;
        if (!id) {
            selMunicipio.innerHTML = '<option value="">Todos los municipios</option>';
            return;
        }
        fetch(BASE_URL + '/api/municipios/' + id)
            .then(r => r.json())
            .then(data => {
                selMunicipio.innerHTML = '<option value="">Todos los municipios</option>';
                if (data.success && data.municipios.length) {
                    data.municipios.forEach(m => {
                        const opt = document.createElement('option');
                        opt.value = m.id;
                        opt.textContent = m.nombre;
                        selMunicipio.appendChild(opt);
                    });
                    selMunicipio.disabled = false;
                }
            })
            .catch(() => { selMunicipio.innerHTML = '<option value="">Error</option>'; });
    });
})();
