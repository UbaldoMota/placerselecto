(function () {
    const urlMeta = document.querySelector('meta[name="app-url"]');
    const BASE_URL = urlMeta ? urlMeta.getAttribute('content') : '';

    const selEstado = document.getElementById('hs-estado');
    const selMun    = document.getElementById('hs-municipio');
    if (!selEstado || !selMun) return;

    selEstado.addEventListener('change', function () {
        const id = parseInt(this.value, 10);
        selMun.innerHTML = '<option value="">Todos los municipios</option>';
        selMun.disabled  = true;
        if (!id) return;
        fetch(BASE_URL + '/api/municipios/' + id)
            .then(r => r.json())
            .then(data => {
                if (data.success && data.municipios.length) {
                    data.municipios.forEach(m => {
                        const opt = document.createElement('option');
                        opt.value = m.id; opt.textContent = m.nombre;
                        selMun.appendChild(opt);
                    });
                    selMun.disabled = false;
                }
            });
    });
})();
