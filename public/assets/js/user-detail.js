(function () {
    // Motivo rechazo: toggle "Otro" + highlight
    document.querySelectorAll('input[name="motivo"]').forEach(r => {
        r.addEventListener('change', function () {
            const wrap = document.getElementById('detalleOtroWrap');
            if (wrap) wrap.style.display = this.value === 'otro' ? 'block' : 'none';
            document.querySelectorAll('input[name="motivo"]').forEach(x => {
                const lbl = x.closest('label'); if (lbl) lbl.style.borderColor = '';
            });
            const sel = this.closest('label');
            if (sel) sel.style.borderColor = 'var(--color-primary)';
        });
    });

    // Confirmar eliminar "SI_ELIMINAR"
    const input = document.getElementById('inputConfirmarDetalle');
    const btn   = document.getElementById('btnConfirmarEliminarDetalle');
    if (input && btn) {
        input.addEventListener('input', function () {
            btn.disabled = this.value.trim() !== 'SI_ELIMINAR';
        });
        const modal = document.getElementById('modalEliminarDetalle');
        if (modal) modal.addEventListener('hidden.bs.modal', function () {
            input.value  = '';
            btn.disabled = true;
        });
    }
})();
