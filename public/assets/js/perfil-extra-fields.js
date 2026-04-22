(function(){
    const cfgEl = document.getElementById('perfil-extra-config');
    if (!cfgEl) return;

    const tieneZona = cfgEl.dataset.tieneZona === '1';
    const initLat   = parseFloat(cfgEl.dataset.lat  || '23.6345');
    const initLng   = parseFloat(cfgEl.dataset.lng  || '-102.5528');
    const initZoom  = parseInt(cfgEl.dataset.zoom  || (tieneZona ? '12' : '5'), 10);
    let radioKm     = parseInt(cfgEl.dataset.radio || '5', 10);

    /* Contact method toggles */
    function toggleCM(key, force) {
        const tog  = document.getElementById('tog-' + key);
        const body = document.getElementById('body-' + key);
        const card = document.getElementById('cm-' + key);
        if (!tog || !body || !card) return;
        const open = force !== undefined ? force : !tog.checked;
        tog.checked = open;
        body.classList.toggle('open', open);
        card.classList.toggle('activo', open);
        if (!open) {
            const inp = document.getElementById('inp-' + key);
            if (inp) inp.value = '';
        }
    }

    document.addEventListener('click', function(ev){
        const el = ev.target.closest('[data-cm-toggle]');
        if (!el) return;
        // Ignorar clicks en el switch interno (para que el onchange funcione)
        if (ev.target.closest('.form-check')) return;
        toggleCM(el.dataset.cmToggle);
    });

    document.addEventListener('change', function(ev){
        const el = ev.target.closest('[data-cm-switch]');
        if (!el) return;
        toggleCM(el.dataset.cmSwitch, el.checked);
    });

    /* Mapa Leaflet (solo si existe #mapa-zona y L está cargado) */
    const mapaEl = document.getElementById('mapa-zona');
    if (!mapaEl || typeof L === 'undefined') return;

    const mapa = L.map('mapa-zona').setView([initLat, initLng], initZoom);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© <a href="https://openstreetmap.org">OpenStreetMap</a>',
        maxZoom: 18
    }).addTo(mapa);

    const icono = L.divIcon({
        className: '',
        html: '<div style="width:22px;height:22px;background:var(--color-primary);border:3px solid #fff;border-radius:50%;box-shadow:0 2px 8px rgba(0,0,0,.5)"></div>',
        iconSize: [22, 22], iconAnchor: [11, 11]
    });

    let marker = null;
    let circle = null;

    if (tieneZona) {
        marker = L.marker([initLat, initLng], {draggable:true, icon: icono}).addTo(mapa);
        circle = L.circle([initLat, initLng], {radius: radioKm * 1000, color:'#FF2D75', fillColor:'#FF2D75', fillOpacity:.15, weight:2}).addTo(mapa);
        marker.on('dragend', function(e){
            const pos = e.target.getLatLng();
            setZona(pos.lat.toFixed(7), pos.lng.toFixed(7));
            circle.setLatLng(pos);
        });
    }

    function setZona(lat, lng) {
        document.getElementById('zona_lat').value   = lat;
        document.getElementById('zona_lng').value   = lng;
        document.getElementById('zona_radio').value = radioKm;

        if (!marker) {
            marker = L.marker([lat, lng], {draggable:true, icon: icono}).addTo(mapa);
            marker.on('dragend', function(e){
                const pos = e.target.getLatLng();
                setZona(pos.lat.toFixed(7), pos.lng.toFixed(7));
                if (circle) circle.setLatLng(pos);
            });
        } else {
            marker.setLatLng([lat, lng]);
        }

        if (!circle) {
            circle = L.circle([lat, lng], {radius: radioKm * 1000, color:'#FF2D75', fillColor:'#FF2D75', fillOpacity:.15, weight:2}).addTo(mapa);
        } else {
            circle.setLatLng([lat, lng]).setRadius(radioKm * 1000);
        }
    }

    mapa.on('click', function(e){
        setZona(e.latlng.lat.toFixed(7), e.latlng.lng.toFixed(7));
        mapa.setView(e.latlng);
    });

    const slider   = document.getElementById('sliderRadio');
    const lblRadio = document.getElementById('lblRadio');
    const inpRadio = document.getElementById('zona_radio');
    if (slider) {
        slider.addEventListener('input', function(){
            radioKm = parseInt(this.value, 10);
            if (lblRadio) lblRadio.textContent = radioKm + ' km';
            if (inpRadio) inpRadio.value = radioKm;
            if (circle)   circle.setRadius(radioKm * 1000);
        });
    }

    function buscarZona() {
        const q = document.getElementById('mapSearch').value.trim();
        if (!q) return;
        fetch('https://nominatim.openstreetmap.org/search?q=' + encodeURIComponent(q) + '&format=json&limit=1&countrycodes=mx,us,co,ar,es,pe,ve,cl', {
            headers: {'Accept-Language': 'es'}
        })
        .then(r => r.json())
        .then(data => {
            if (data && data.length) {
                const lat = parseFloat(data[0].lat).toFixed(7);
                const lng = parseFloat(data[0].lon).toFixed(7);
                setZona(lat, lng);
                mapa.setView([lat, lng], 13);
                if (!document.getElementById('zona_descripcion').value) {
                    document.getElementById('zona_descripcion').value = data[0].display_name.split(',').slice(0,3).join(',');
                }
            } else {
                alert('No se encontró esa ubicación. Prueba con otro término.');
            }
        })
        .catch(() => alert('Error al buscar. Verifica tu conexión.'));
    }

    const btnSearch = document.getElementById('btnMapSearch');
    const inpSearch = document.getElementById('mapSearch');
    if (btnSearch) btnSearch.addEventListener('click', buscarZona);
    if (inpSearch) inpSearch.addEventListener('keydown', function(e){
        if (e.key === 'Enter') { e.preventDefault(); buscarZona(); }
    });

    const btnLimpiar = document.getElementById('btnLimpiarZona');
    if (btnLimpiar) {
        btnLimpiar.addEventListener('click', function(){
            document.getElementById('zona_lat').value         = '';
            document.getElementById('zona_lng').value         = '';
            document.getElementById('zona_descripcion').value = '';
            if (marker) { mapa.removeLayer(marker); marker = null; }
            if (circle) { mapa.removeLayer(circle); circle = null; }
        });
    }

    setTimeout(() => mapa.invalidateSize(), 300);
})();
