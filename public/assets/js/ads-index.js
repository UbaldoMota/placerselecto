(function () {
    const urlMeta = document.querySelector('meta[name="app-url"]');
    const BASE_URL = urlMeta ? urlMeta.getAttribute('content') : '';

    function initCascade(selEstadoId, selMunId, loadingId, autoSubmitForm) {
        const selEstado = document.getElementById(selEstadoId);
        const selMun    = document.getElementById(selMunId);
        const loading   = loadingId ? document.getElementById(loadingId) : null;
        if (!selEstado || !selMun) return;

        selEstado.addEventListener('change', function () {
            const idEstado = parseInt(this.value, 10);
            selMun.innerHTML = '<option value="">Todos los municipios</option>';
            selMun.disabled  = true;

            if (!idEstado) {
                selMun.removeAttribute('name');
                if (autoSubmitForm) autoSubmitForm.submit();
                return;
            }

            if (loading) loading.classList.remove('d-none');

            fetch(BASE_URL + '/api/municipios/' + idEstado)
                .then(r => r.json())
                .then(data => {
                    if (loading) loading.classList.add('d-none');
                    if (data.success && data.municipios.length) {
                        data.municipios.forEach(m => {
                            const opt = document.createElement('option');
                            opt.value       = m.nombre;
                            opt.textContent = m.nombre;
                            selMun.appendChild(opt);
                        });
                        selMun.disabled = false;
                        selMun.setAttribute('name', 'ciudad');
                    }
                })
                .catch(() => { if (loading) loading.classList.add('d-none'); });
        });
    }

    initCascade('fl-estado', 'fl-municipio', 'fl-loading', document.getElementById('filter-form'));
    initCascade('ts-estado', 'ts-municipio', null, null);
})();
