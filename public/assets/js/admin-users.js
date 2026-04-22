(function () {
    const modal    = document.getElementById('modalEliminar');
    const form     = document.getElementById('formEliminar');
    const inputConf= document.getElementById('inputConfirmar');
    const btnConf  = document.getElementById('btnConfirmarEliminar');
    const nombreEl = document.getElementById('modalNombreUsuario');
    if (!modal || !form || !inputConf || !btnConf || !nombreEl) return;

    const urlMeta = document.querySelector('meta[name="app-url"]');
    const baseUrl = urlMeta ? urlMeta.getAttribute('content') : '';

    modal.addEventListener('show.bs.modal', function (e) {
        const btn    = e.relatedTarget;
        if (!btn) return;
        const userId = btn.dataset.userId;
        const nombre = btn.dataset.userNombre;

        nombreEl.textContent = nombre;
        form.action = baseUrl + '/admin/usuario/' + userId + '/eliminar';
        inputConf.value = '';
        btnConf.disabled = true;
    });

    inputConf.addEventListener('input', function () {
        btnConf.disabled = this.value.trim() !== 'SI_ELIMINAR';
    });
})();
