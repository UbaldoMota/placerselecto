/**
 * verificar-camara.js
 * Grabación y envío de video de verificación (user + perfil).
 * Endpoint se lee del data-attribute del contenedor.
 */
(function () {
    const cfg = document.getElementById('cam-verif-config');
    if (!cfg) return;

    const appUrl   = cfg.dataset.appUrl || (document.querySelector('meta[name="app-url"]')?.getAttribute('content') || '');
    const endpoint = cfg.dataset.endpoint || (appUrl + '/verificacion/video');
    const csrfToken= cfg.dataset.csrfToken || (document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '');

    const btnActivar   = document.getElementById('btnActivar');
    const btnIniciar   = document.getElementById('btnIniciar');
    const btnRegrabar  = document.getElementById('btnRegrabar');
    const btnEnviar    = document.getElementById('btnEnviar');
    const videoEl      = document.getElementById('videoPreview');
    const videoReview  = document.getElementById('videoReview');
    const estadoCamara = document.getElementById('estadoCamara');
    const alertaCamara = document.getElementById('alertaCamara');
    const linkReintento= document.getElementById('linkReintento');
    const countdownEl  = document.getElementById('countdown');
    const recIndicator = document.getElementById('recIndicator');
    const paso1        = document.getElementById('paso1');
    const paso1b       = document.getElementById('paso1b');
    const paso2        = document.getElementById('paso2');
    const paso3        = document.getElementById('paso3');
    const progressBar  = document.getElementById('progressBar');

    if (!btnActivar || !btnIniciar || !videoEl) return;

    let stream    = null;
    let recorder  = null;
    let chunks    = [];
    let blobFinal = null;
    let mimeUsado = '';

    async function solicitarCamara() {
        btnActivar.disabled = true;
        btnActivar.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Activando…';
        if (alertaCamara) alertaCamara.style.display = 'none';
        if (estadoCamara) {
            estadoCamara.style.display = 'flex';
            estadoCamara.innerHTML = '<div class="spinner-border text-secondary" style="width:2rem;height:2rem"></div><span style="font-size:.85rem">Solicitando acceso a la cámara…</span>';
        }

        // Comprobaciones previas — iOS/Android ignoran getUserMedia sin HTTPS
        if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
            mostrarAlertaError('Tu navegador no soporta acceso a la cámara. Usa un navegador actualizado (Chrome, Safari, Edge).');
            return;
        }
        if (!window.isSecureContext) {
            mostrarAlertaError('Se requiere conexión HTTPS para acceder a la cámara.');
            return;
        }

        // Constraint con facingMode para móviles — pide cámara frontal
        const constraints = {
            video: {
                facingMode: { ideal: 'user' },
                width:  { ideal: 1280 },
                height: { ideal: 720 }
            },
            audio: false
        };

        try {
            stream = await navigator.mediaDevices.getUserMedia(constraints);
        } catch (err1) {
            // Fallback: sin facingMode (puede fallar en escritorio sin cam frontal)
            try {
                stream = await navigator.mediaDevices.getUserMedia({ video: true, audio: false });
            } catch (err2) {
                const msg = interpretarErrorCamara(err2);
                mostrarAlertaError(msg);
                return;
            }
        }

        if (estadoCamara) estadoCamara.style.display = 'none';
        videoEl.style.display = 'block';
        videoEl.setAttribute('playsinline', '');
        videoEl.setAttribute('muted', '');
        videoEl.muted = true;
        videoEl.srcObject = stream;
        try { await videoEl.play(); } catch (_) { /* algunos browsers ya autoplay */ }
        btnActivar.style.display = 'none';
        btnIniciar.style.display = 'inline-flex';
    }

    function mostrarAlertaError(msg) {
        btnActivar.disabled = false;
        btnActivar.innerHTML = '<i class="bi bi-camera-video me-2"></i>Activar cámara';
        if (estadoCamara) {
            estadoCamara.innerHTML = '<i class="bi bi-camera-video-off" style="font-size:3rem;color:#555"></i><span style="font-size:.85rem;color:#888">Haz clic en "Activar cámara" para continuar</span>';
        }
        if (alertaCamara) {
            alertaCamara.innerHTML = '<i class="bi bi-exclamation-triangle-fill me-2"></i>' + msg;
            alertaCamara.style.display = 'block';
        }
    }

    function interpretarErrorCamara(err) {
        const name = err && err.name ? err.name : '';
        switch (name) {
            case 'NotAllowedError':
            case 'SecurityError':
                return '<strong>Permiso denegado.</strong> Da acceso a la cámara desde la configuración del navegador y recarga la página.';
            case 'NotFoundError':
            case 'OverconstrainedError':
                return '<strong>No se encontró cámara.</strong> Verifica que tu dispositivo tenga cámara frontal disponible.';
            case 'NotReadableError':
                return '<strong>Cámara ocupada.</strong> Otra app puede estar usándola. Ciérrala y vuelve a intentar.';
            case 'AbortError':
                return 'La activación fue interrumpida. Vuelve a intentar.';
            default:
                return '<strong>No fue posible acceder a la cámara.</strong> Acepta el permiso cuando el navegador lo pida. (' + (err.message || name) + ')';
        }
    }

    btnActivar.addEventListener('click', solicitarCamara);
    if (linkReintento) linkReintento.addEventListener('click', e => { e.preventDefault(); solicitarCamara(); });

    btnIniciar.addEventListener('click', async function () {
        if (!stream) return;
        btnIniciar.disabled = true;
        await new Promise(r => setTimeout(r, 800));

        chunks    = [];
        blobFinal = null;
        mimeUsado = MediaRecorder.isTypeSupported('video/webm;codecs=vp9')
            ? 'video/webm;codecs=vp9'
            : (MediaRecorder.isTypeSupported('video/webm') ? 'video/webm' : 'video/mp4');

        recorder = new MediaRecorder(stream, { mimeType: mimeUsado });
        recorder.ondataavailable = e => { if (e.data.size > 0) chunks.push(e.data); };
        recorder.onstop = () => mostrarRevision();
        recorder.start(200);

        if (recIndicator) recIndicator.style.display = 'flex';
        if (countdownEl)  countdownEl.style.display  = 'block';

        let secs = 5;
        if (countdownEl) countdownEl.textContent = secs;
        const interval = setInterval(() => {
            secs--;
            if (countdownEl) countdownEl.textContent = secs;
            if (secs <= 0) {
                clearInterval(interval);
                recorder.stop();
                stream.getTracks().forEach(t => t.stop());
                videoEl.srcObject = null;
                if (recIndicator) recIndicator.style.display = 'none';
                if (countdownEl)  countdownEl.style.display  = 'none';
            }
        }, 1000);
    });

    function mostrarRevision() {
        blobFinal = new Blob(chunks, { type: mimeUsado });
        if (videoReview) {
            videoReview.src = URL.createObjectURL(blobFinal);
            videoReview.load();
        }
        if (paso1)  paso1.style.display  = 'none';
        if (paso1b) paso1b.style.display = 'block';
    }

    if (btnRegrabar) btnRegrabar.addEventListener('click', async function () {
        if (videoReview && videoReview.src) URL.revokeObjectURL(videoReview.src);
        if (videoReview) videoReview.src = '';
        if (paso1b) paso1b.style.display = 'none';
        if (paso1)  paso1.style.display  = 'block';
        stream = null;
        videoEl.style.display = 'none';
        videoEl.srcObject     = null;
        if (estadoCamara) {
            estadoCamara.style.display = 'flex';
            estadoCamara.innerHTML = '<i class="bi bi-camera-video-off" style="font-size:3rem;color:#555"></i><span style="font-size:.85rem;color:#888">Haz clic en "Activar cámara" para continuar</span>';
        }
        btnIniciar.style.display = 'none';
        btnIniciar.disabled      = false;
        btnActivar.style.display = 'inline-flex';
        btnActivar.disabled      = false;
        btnActivar.innerHTML     = '<i class="bi bi-camera-video me-2"></i>Activar cámara';
    });

    if (btnEnviar) btnEnviar.addEventListener('click', async function () {
        if (!blobFinal) return;
        btnEnviar.disabled = true;
        if (btnRegrabar) btnRegrabar.disabled = true;
        if (paso1b) paso1b.style.display = 'none';
        if (paso2)  paso2.style.display  = 'block';

        const formData = new FormData();
        formData.append('video',       blobFinal, 'verificacion.webm');
        formData.append('_csrf_token', csrfToken);

        let pct = 0;
        const progInterval = setInterval(() => {
            pct = Math.min(pct + 3, 90);
            if (progressBar) progressBar.style.width = pct + '%';
        }, 200);

        try {
            const resp = await fetch(endpoint, {
                method:  'POST',
                body:    formData,
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            clearInterval(progInterval);
            if (progressBar) progressBar.style.width = '100%';
            const json = await resp.json();
            if (json.ok) {
                if (paso2) paso2.style.display = 'none';
                if (paso3) paso3.style.display = 'block';
            } else {
                if (paso2)  paso2.style.display  = 'none';
                if (paso1b) paso1b.style.display = 'block';
                btnEnviar.disabled = false;
                if (btnRegrabar) btnRegrabar.disabled = false;
                if (alertaCamara) {
                    alertaCamara.innerHTML = '<i class="bi bi-exclamation-triangle-fill me-2"></i>' + (json.error || 'Error al subir el video.');
                    alertaCamara.style.display = 'block';
                }
            }
        } catch (err) {
            clearInterval(progInterval);
            if (paso2)  paso2.style.display  = 'none';
            if (paso1b) paso1b.style.display = 'block';
            btnEnviar.disabled = false;
            if (btnRegrabar) btnRegrabar.disabled = false;
            if (alertaCamara) {
                alertaCamara.innerHTML = '<i class="bi bi-exclamation-triangle-fill me-2"></i>Error de conexión. Inténtalo de nuevo.';
                alertaCamara.style.display = 'block';
            }
        }
    });
})();
