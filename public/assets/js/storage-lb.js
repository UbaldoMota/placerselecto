(function(){
    const dataEl = document.getElementById('storageLbData');
    if (!dataEl) return;
    let items;
    try { items = JSON.parse(dataEl.textContent || '[]'); } catch (_) { items = []; }
    if (!items.length) return;

    const lb     = document.getElementById('storageLb');
    const stage  = document.getElementById('storageLbStage');
    const nameEl = document.getElementById('storageLbName');
    const metaEl = document.getElementById('storageLbMeta');
    const cntEl  = document.getElementById('storageLbCounter');
    if (!lb || !stage) return;
    let current = 0;

    function render(i){
        current = (i + items.length) % items.length;
        const it = items[current];
        stage.innerHTML = '';
        let node;
        if (it.tipo === 'img') {
            node = document.createElement('img');
            node.src = it.url; node.alt = it.nombre;
        } else {
            node = document.createElement('video');
            node.src = it.url; node.controls = true;
            node.autoplay = true; node.playsInline = true;
        }
        stage.appendChild(node);
        if (nameEl) nameEl.textContent = it.nombre;
        if (metaEl) metaEl.textContent = it.bytes + ' · ' + it.mtime;
        if (cntEl)  cntEl.textContent  = (current + 1) + ' / ' + items.length;
    }
    function open(i){
        render(i);
        lb.classList.add('is-open');
        lb.setAttribute('aria-hidden','false');
        document.body.style.overflow = 'hidden';
    }
    function close(){
        lb.classList.remove('is-open');
        lb.setAttribute('aria-hidden','true');
        stage.innerHTML = '';
        document.body.style.overflow = '';
    }
    function next(){ render(current + 1); }
    function prev(){ render(current - 1); }

    document.querySelectorAll('#storageGallery [data-lb-index]').forEach(el => {
        el.addEventListener('click', e => {
            e.preventDefault();
            open(parseInt(el.dataset.lbIndex, 10));
        });
    });
    lb.querySelector('[data-lb-close]')?.addEventListener('click', close);
    lb.querySelector('[data-lb-prev]')?.addEventListener('click', prev);
    lb.querySelector('[data-lb-next]')?.addEventListener('click', next);
    lb.addEventListener('click', e => { if (e.target === lb) close(); });
    document.addEventListener('keydown', e => {
        if (!lb.classList.contains('is-open')) return;
        if (e.key === 'Escape') close();
        else if (e.key === 'ArrowRight') next();
        else if (e.key === 'ArrowLeft')  prev();
    });
})();
