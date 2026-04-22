(function(){
    const dataEl = document.getElementById('planes-data');
    if (!dataEl) return;
    let planes;
    try { planes = JSON.parse(dataEl.textContent || '[]'); } catch (_) { planes = []; }

    function selectPlan(dias) {
        document.querySelectorAll('.plan-card').forEach(c => {
            c.classList.remove('selected');
            c.querySelector('.plan-check')?.classList.add('d-none');
        });
        const card = document.querySelector('.plan-card[data-dias="' + dias + '"]');
        if (card) {
            card.classList.add('selected');
            card.querySelector('.plan-check')?.classList.remove('d-none');
        }
        const inp = document.getElementById('plan-input');
        if (inp) inp.value = dias;

        const plan = planes.find(p => p.dias === dias);
        if (plan) {
            const sp = document.getElementById('summary-plan');
            const sd = document.getElementById('summary-duracion');
            const st = document.getElementById('summary-total');
            if (sp) sp.textContent = plan.label;
            if (sd) sd.textContent = plan.dias + ' días';
            if (st) st.textContent = '$' + Number(plan.precio).toLocaleString('es-MX', {minimumFractionDigits:0}) + ' MXN';
            document.getElementById('payment-summary')?.classList.remove('d-none');
            document.getElementById('pay-btn')?.classList.remove('d-none');
        }
    }

    document.querySelectorAll('.plan-card[data-dias]').forEach(card => {
        card.addEventListener('click', () => selectPlan(parseInt(card.dataset.dias, 10)));
    });

    // Auto-seleccionar plan popular (7 días)
    document.addEventListener('DOMContentLoaded', () => selectPlan(7));
    // Si ya carga
    if (document.readyState !== 'loading') selectPlan(7);
})();
