<?php
/**
 * user/verificar-camara.php
 * Verificación de identidad del perfil de usuario mediante video de 5 segundos.
 * La cámara se solicita automáticamente al cargar la página.
 */
$tieneVideo = !empty($usuario['video_verificacion']);
$appUrl     = APP_URL;
$csrfToken  = Middleware::generateCsrfToken();
?>

<div class="container py-4" style="max-width:700px">

    <div class="text-center mb-4">
        <h1 class="h4 fw-bold mb-1">
            <i class="bi bi-camera-video text-primary me-2"></i>Verificación de identidad
        </h1>
        <p class="text-muted mb-0" style="font-size:.875rem">
            Graba un video de 5 segundos para verificar que eres una persona real.
            Esta verificación es para tu <strong>cuenta de usuario</strong>, no por cada perfil.
        </p>
    </div>

    <?php if ($tieneVideo): ?>
    <div class="alert alert-success mb-4 d-flex align-items-center gap-2" style="font-size:.875rem">
        <i class="bi bi-check-circle-fill"></i>
        <div>
            <strong>Ya tienes un video enviado.</strong>
            Puedes grabar uno nuevo si lo deseas — reemplazará al anterior.
            <?php if (!empty($usuario['video_verificacion_at'])): ?>
            <span class="text-muted d-block" style="font-size:.8rem">
                Enviado: <?= e(date('d/m/Y H:i', strtotime($usuario['video_verificacion_at']))) ?>
            </span>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Alerta de error de cámara -->
    <div id="alertaCamara" class="alert alert-danger mb-3" style="display:none;font-size:.875rem">
        <i class="bi bi-exclamation-triangle-fill me-2"></i>
        <strong>No ha sido posible acceder a la cámara.</strong>
        Asegúrese de tener los permisos concedidos.
        <a href="#" class="alert-link ms-2" id="linkReintento">Intentar de nuevo</a>
    </div>

    <!-- Card principal -->
    <div class="card">
        <div class="card-body">

            <!-- PASO 1: cámara + grabación -->
            <div id="paso1">
                <p class="fw-bold mb-3" style="color:var(--color-primary);font-size:.9rem">
                    <i class="bi bi-1-circle-fill me-2"></i>INSTRUCCIONES
                </p>
                <ul class="mb-3" style="font-size:.875rem;line-height:1.8">
                    <li>Sitúese ante la cámara mostrando <strong>2/3 de su cuerpo.</strong></li>
                    <li>Sostenga un cartel con el nombre <strong style="color:var(--color-primary)"><?= e(APP_NAME) ?></strong></li>
                    <li><strong><em>Mueva el cartel o muévase para demostrar que el video es real.</em></strong></li>
                    <li class="text-danger fw-semibold">No grabe con luz de fondo.</li>
                </ul>

                <!-- Área de video -->
                <div style="position:relative;background:#000;border-radius:var(--radius-md);overflow:hidden;aspect-ratio:16/9;margin-bottom:1.25rem">
                    <video id="videoPreview" autoplay muted playsinline
                           style="width:100%;height:100%;object-fit:cover;display:none"></video>

                    <!-- Estado inicial -->
                    <div id="estadoCamara"
                         style="display:flex;align-items:center;justify-content:center;height:100%;flex-direction:column;gap:.75rem">
                        <i class="bi bi-camera-video-off" style="font-size:3rem;color:#555"></i>
                        <span style="font-size:.85rem;color:#888">Haz clic en "Activar cámara" para continuar</span>
                    </div>

                    <!-- Countdown -->
                    <div id="countdown" style="display:none;position:absolute;top:12px;right:14px;background:rgba(220,53,69,.85);color:#fff;border-radius:8px;padding:.25rem .65rem;font-size:1.3rem;font-weight:700">5</div>

                    <!-- Indicador grabando -->
                    <div id="recIndicator" style="display:none;position:absolute;top:12px;left:14px;align-items:center;gap:.4rem;background:rgba(0,0,0,.55);border-radius:8px;padding:.2rem .6rem">
                        <span style="width:10px;height:10px;border-radius:50%;background:#FF2D75;animation:blink 1s infinite"></span>
                        <span style="color:#fff;font-size:.78rem;font-weight:600">GRABANDO</span>
                    </div>
                </div>

                <div class="text-center d-flex gap-3 justify-content-center flex-wrap">
                    <button id="btnActivar" class="btn btn-outline-primary btn-lg px-4">
                        <i class="bi bi-camera-video me-2"></i>Activar cámara
                    </button>
                    <button id="btnIniciar" class="btn btn-primary btn-lg px-5" style="display:none">
                        <i class="bi bi-record-circle me-2"></i>GRABAR (5 seg)
                    </button>
                </div>
            </div>

            <!-- PASO 1b: revisión antes de enviar -->
            <div id="paso1b" style="display:none">
                <p class="fw-bold mb-3" style="color:var(--color-primary);font-size:.9rem">
                    <i class="bi bi-2-circle-fill me-2"></i>REVISA TU VIDEO
                </p>
                <p class="text-muted mb-3" style="font-size:.875rem">
                    Comprueba que el video es correcto antes de enviarlo.
                    Si no estás conforme, puedes volver a grabar.
                </p>
                <div style="background:#000;border-radius:var(--radius-md);overflow:hidden;aspect-ratio:16/9;margin-bottom:1.25rem">
                    <video id="videoReview" controls playsinline
                           style="width:100%;height:100%;object-fit:contain"></video>
                </div>
                <div class="d-flex gap-3 justify-content-center flex-wrap">
                    <button id="btnRegrabar" class="btn btn-secondary btn-lg px-4">
                        <i class="bi bi-arrow-counterclockwise me-2"></i>Volver a grabar
                    </button>
                    <button id="btnEnviar" class="btn btn-primary btn-lg px-5">
                        <i class="bi bi-send me-2"></i>Enviar video
                    </button>
                </div>
            </div>

            <!-- PASO 2: subiendo -->
            <div id="paso2" style="display:none;text-align:center;padding:2rem 0">
                <div class="spinner-border text-primary mb-3" style="width:3rem;height:3rem"></div>
                <p class="fw-semibold mb-1">Subiendo video de verificación…</p>
                <p class="text-muted" style="font-size:.85rem">No cierres esta página.</p>
                <div class="progress mt-3" style="height:6px;max-width:300px;margin:0 auto">
                    <div id="progressBar" class="progress-bar bg-primary progress-bar-striped progress-bar-animated" style="width:0%"></div>
                </div>
            </div>

            <!-- PASO 3: éxito -->
            <div id="paso3" style="display:none;text-align:center;padding:2rem 0">
                <div style="width:72px;height:72px;border-radius:50%;background:rgba(16,185,129,.15);border:2px solid rgba(16,185,129,.3);display:flex;align-items:center;justify-content:center;margin:0 auto 1.25rem;font-size:2rem">
                    <i class="bi bi-check-circle-fill" style="color:#10B981"></i>
                </div>
                <h3 class="h5 fw-bold mb-2 text-success">¡Video enviado!</h3>
                <p class="mb-1" style="font-size:.9rem">
                    Tu identidad está siendo verificada por nuestro equipo.
                </p>
                <p class="text-muted mb-4" style="font-size:.85rem">
                    <i class="bi bi-clock me-1"></i>
                    Recibirás una notificación en un máximo de <strong>24 horas</strong>.
                </p>
                <a href="<?= APP_URL ?>/dashboard" class="btn btn-primary px-5">
                    <i class="bi bi-house me-2"></i>Ir al dashboard
                </a>
            </div>

        </div>
    </div>

    <p class="text-center text-muted mt-3" style="font-size:.75rem">
        Tu video solo se utiliza para verificar que eres una persona real y nunca será publicado.
    </p>

</div>

<style>
@keyframes blink { 0%,100%{opacity:1} 50%{opacity:.2} }
</style>

<?php
$extraJs = <<<HTML
<script>
(function () {
    const appUrl    = '{$appUrl}';
    const csrfToken = '{$csrfToken}';

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

    let stream    = null;
    let recorder  = null;
    let chunks    = [];
    let blobFinal = null;
    let mimeUsado = '';

    async function solicitarCamara() {
        btnActivar.disabled = true;
        btnActivar.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Activando…';
        alertaCamara.style.display = 'none';
        estadoCamara.style.display = 'flex';
        estadoCamara.innerHTML = '<div class="spinner-border text-secondary" style="width:2rem;height:2rem"></div><span style="font-size:.85rem">Solicitando acceso a la cámara…</span>';

        try {
            stream = await navigator.mediaDevices.getUserMedia({ video: true, audio: false });
            estadoCamara.style.display  = 'none';
            videoEl.style.display       = 'block';
            videoEl.srcObject           = stream;
            btnActivar.style.display    = 'none';
            btnIniciar.style.display    = 'inline-flex';
        } catch (err) {
            btnActivar.disabled = false;
            btnActivar.innerHTML = '<i class="bi bi-camera-video me-2"></i>Activar cámara';
            estadoCamara.innerHTML = '<i class="bi bi-camera-video-off" style="font-size:3rem;color:#555"></i><span style="font-size:.85rem;color:#888">Haz clic en "Activar cámara" para continuar</span>';
            alertaCamara.innerHTML = '<i class="bi bi-exclamation-triangle-fill me-2"></i><strong>No fue posible acceder a la cámara.</strong> Acepta el permiso cuando el navegador lo pida y vuelve a intentarlo. Si ya lo aceptaste, recarga la página.';
            alertaCamara.style.display = 'block';
        }
    }

    btnActivar.addEventListener('click', solicitarCamara);
    linkReintento.addEventListener('click', e => { e.preventDefault(); solicitarCamara(); });

    // ── Iniciar grabación ──
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

        recIndicator.style.display = 'flex';
        countdownEl.style.display  = 'block';

        let secs = 5;
        countdownEl.textContent = secs;
        const interval = setInterval(() => {
            secs--;
            countdownEl.textContent = secs;
            if (secs <= 0) {
                clearInterval(interval);
                recorder.stop();
                stream.getTracks().forEach(t => t.stop());
                videoEl.srcObject          = null;
                recIndicator.style.display = 'none';
                countdownEl.style.display  = 'none';
            }
        }, 1000);
    });

    // ── Mostrar revisión ──
    function mostrarRevision() {
        blobFinal = new Blob(chunks, { type: mimeUsado });
        videoReview.src = URL.createObjectURL(blobFinal);
        videoReview.load();
        paso1.style.display  = 'none';
        paso1b.style.display = 'block';
    }

    // ── Volver a grabar ──
    btnRegrabar.addEventListener('click', async function () {
        if (videoReview.src) URL.revokeObjectURL(videoReview.src);
        videoReview.src = '';
        paso1b.style.display = 'none';
        paso1.style.display  = 'block';

        stream = null;
        videoEl.style.display      = 'none';
        videoEl.srcObject          = null;
        estadoCamara.style.display = 'flex';
        estadoCamara.innerHTML     = '<i class="bi bi-camera-video-off" style="font-size:3rem;color:#555"></i><span style="font-size:.85rem;color:#888">Haz clic en "Activar cámara" para continuar</span>';
        btnIniciar.style.display   = 'none';
        btnIniciar.disabled        = false;
        btnActivar.style.display   = 'inline-flex';
        btnActivar.disabled        = false;
        btnActivar.innerHTML       = '<i class="bi bi-camera-video me-2"></i>Activar cámara';
    });

    // ── Enviar video ──
    btnEnviar.addEventListener('click', async function () {
        if (!blobFinal) return;
        btnEnviar.disabled   = true;
        btnRegrabar.disabled = true;

        paso1b.style.display = 'none';
        paso2.style.display  = 'block';

        const formData = new FormData();
        formData.append('video',       blobFinal, 'verificacion.webm');
        formData.append('_csrf_token', csrfToken);

        let pct = 0;
        const progInterval = setInterval(() => {
            pct = Math.min(pct + 3, 90);
            progressBar.style.width = pct + '%';
        }, 200);

        try {
            const resp = await fetch(appUrl + '/verificacion/video', {
                method:  'POST',
                body:    formData,
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });

            clearInterval(progInterval);
            progressBar.style.width = '100%';

            const json = await resp.json();
            if (json.ok) {
                paso2.style.display = 'none';
                paso3.style.display = 'block';
            } else {
                paso2.style.display  = 'none';
                paso1b.style.display = 'block';
                btnEnviar.disabled   = false;
                btnRegrabar.disabled = false;
                alertaCamara.innerHTML = '<i class="bi bi-exclamation-triangle-fill me-2"></i>' + (json.error || 'Error al subir el video.');
                alertaCamara.style.display = 'block';
            }
        } catch (err) {
            clearInterval(progInterval);
            paso2.style.display  = 'none';
            paso1b.style.display = 'block';
            btnEnviar.disabled   = false;
            btnRegrabar.disabled = false;
            alertaCamara.innerHTML = '<i class="bi bi-exclamation-triangle-fill me-2"></i>Error de conexión. Inténtalo de nuevo.';
            alertaCamara.style.display = 'block';
        }
    });
})();
</script>
HTML;
?>
