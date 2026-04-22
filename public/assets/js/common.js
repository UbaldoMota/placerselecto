/**
 * common.js - handlers CSP-safe para patrones repetidos en múltiples vistas.
 * Se activan automáticamente via data-attributes.
 */
(function(){
    'use strict';

    // ─── Toggle password visibility ──────────────────────────
    // <button data-toggle-password="passwordInputId" [data-eye-icon="iconId"]>
    document.addEventListener('click', function(ev){
        const btn = ev.target.closest('[data-toggle-password]');
        if (!btn) return;
        ev.preventDefault();
        const inp = document.getElementById(btn.dataset.togglePassword);
        if (!inp) return;
        const eye = btn.dataset.eyeIcon ? document.getElementById(btn.dataset.eyeIcon) : btn.querySelector('i');
        const visible = inp.type === 'text';
        inp.type = visible ? 'password' : 'text';
        if (eye) eye.className = visible ? 'bi bi-eye' : 'bi bi-eye-slash';
    });

    // ─── Number-only input ───────────────────────────────────
    // <input data-number-only [data-number-max="6"]>
    document.addEventListener('input', function(ev){
        const el = ev.target.closest('[data-number-only]');
        if (!el) return;
        const max = parseInt(el.dataset.numberMax || '999', 10);
        el.value = el.value.replace(/\D/g, '').slice(0, max);
    });

    // ─── Submit confirm ──────────────────────────────────────
    // <form data-confirm-submit="¿Estás seguro?">
    document.addEventListener('submit', function(ev){
        const form = ev.target.closest('[data-confirm-submit]');
        if (!form) return;
        if (!confirm(form.dataset.confirmSubmit)) ev.preventDefault();
    });

    // ─── Click confirm ───────────────────────────────────────
    // <a|button data-confirm-click="¿Continuar?">
    document.addEventListener('click', function(ev){
        const el = ev.target.closest('[data-confirm-click]');
        if (!el) return;
        if (!confirm(el.dataset.confirmClick)) ev.preventDefault();
    });

    // ─── Countdown reenvío ───────────────────────────────────
    // <button data-countdown-seconds="60" data-countdown-display="secsId">
    document.querySelectorAll('[data-countdown-seconds]').forEach(btn => {
        let secs = parseInt(btn.dataset.countdownSeconds || '0', 10);
        if (secs <= 0) return;
        const display = btn.dataset.countdownDisplay ? document.getElementById(btn.dataset.countdownDisplay) : null;
        const wrap    = btn.dataset.countdownWrap ? document.getElementById(btn.dataset.countdownWrap) : null;
        btn.disabled = true;
        const timer = setInterval(() => {
            secs--;
            if (display) display.textContent = secs;
            if (secs <= 0) {
                clearInterval(timer);
                btn.disabled = false;
                if (wrap) wrap.style.display = 'none';
            }
        }, 1000);
    });

    // ─── Trigger file input ──────────────────────────────────
    // <el data-trigger-file="fileInputId">
    document.addEventListener('click', function(ev){
        const el = ev.target.closest('[data-trigger-file]');
        if (!el) return;
        const f = document.getElementById(el.dataset.triggerFile);
        if (f) f.click();
    });

    // ─── Auto-submit form on change ──────────────────────────
    // <select data-auto-submit>
    document.addEventListener('change', function(ev){
        const el = ev.target.closest('[data-auto-submit]');
        if (!el) return;
        const form = el.closest('form');
        if (form) form.submit();
    });

    // ─── Toggle display genérico ─────────────────────────────
    // <button data-toggle-display="targetId">
    document.addEventListener('click', function(ev){
        const btn = ev.target.closest('[data-toggle-display]');
        if (!btn) return;
        ev.preventDefault();
        const tgt = document.getElementById(btn.dataset.toggleDisplay);
        if (!tgt) return;
        tgt.style.display = tgt.style.display === 'none' ? '' : 'none';
    });

})();
