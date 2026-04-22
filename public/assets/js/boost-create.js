(function () {
    const form = document.getElementById('boost-form');
    if (!form) return;

    const cfg = document.getElementById('boost-config');
    const saldo = cfg ? parseInt(cfg.dataset.saldo || '0', 10) : 0;

    const horasI   = document.getElementById('boost-horas');
    const modosR   = form.querySelectorAll('input[name="modo_inicio"]');
    const inicioI  = document.getElementById('boost-inicio');
    const resTarifa = document.getElementById('res-tarifa');
    const resHoras  = document.getElementById('res-horas');
    const resCosto  = document.getElementById('res-costo');
    const saldoW    = document.getElementById('saldo-warning');
    const faltanEl  = document.getElementById('faltan');
    const submit    = document.getElementById('boost-submit');

    form.querySelectorAll('input[name="tipo"]').forEach(r => {
        r.addEventListener('change', () => {
            form.querySelectorAll('label.plan-card').forEach(l => {
                const input = l.querySelector('input[name="tipo"]');
                l.classList.toggle('selected', input && input.checked);
            });
            calcular();
        });
    });
    const tipoInit = form.querySelector('input[name="tipo"]:checked');
    if (tipoInit) tipoInit.closest('label')?.classList.add('selected');

    document.querySelectorAll('.boost-h-preset').forEach(b => {
        b.addEventListener('click', () => {
            if (horasI) { horasI.value = b.dataset.h; calcular(); }
        });
    });
    if (horasI) horasI.addEventListener('input', calcular);

    modosR.forEach(r => r.addEventListener('change', () => {
        const programado = form.querySelector('input[name="modo_inicio"]:checked').value === 'programado';
        if (inicioI) inicioI.disabled = !programado;
        if (programado && inicioI && !inicioI.value) {
            const d = new Date(Date.now() + 30 * 60000);
            inicioI.value = d.toISOString().slice(0, 16);
        }
    }));

    function calcular() {
        const tipo  = form.querySelector('input[name="tipo"]:checked');
        const tph   = tipo ? parseInt(tipo.dataset.tph) : 0;
        const horas = Math.max(1, parseInt(horasI?.value) || 0);
        const costo = tph * horas;

        if (resTarifa) resTarifa.textContent = tph + ' t/h';
        if (resHoras)  resHoras.textContent  = horas + ' h';
        if (resCosto)  resCosto.textContent  = costo.toLocaleString();

        const insuficiente = costo > saldo;
        if (saldoW) saldoW.classList.toggle('d-none', !insuficiente);
        if (insuficiente && faltanEl) faltanEl.textContent = (costo - saldo).toLocaleString();
        if (submit) submit.disabled = insuficiente || horas < 1;
    }

    calcular();
})();
