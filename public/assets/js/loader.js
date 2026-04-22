/**
 * loader.js — Overlay de carga global.
 * Se muestra automáticamente al navegar o enviar formularios.
 *
 * API pública:
 *   window.PlacerLoader.show(texto?)  — mostrar
 *   window.PlacerLoader.hide()        — ocultar
 *
 * Para excluir un elemento del loader automático:
 *   <a data-no-loader>, <form data-no-loader>, <button data-no-loader>
 */
(function(){
    'use strict';

    const loaderEl = document.getElementById('global-loader');
    if (!loaderEl) return;

    const textEl = loaderEl.querySelector('.global-loader__text');

    function show(texto) {
        if (texto && textEl) textEl.textContent = texto;
        loaderEl.classList.add('is-visible');
        loaderEl.setAttribute('aria-hidden', 'false');
    }
    function hide() {
        loaderEl.classList.remove('is-visible');
        loaderEl.setAttribute('aria-hidden', 'true');
        if (textEl) textEl.textContent = 'Cargando…';
    }

    // API global
    window.PlacerLoader = { show, hide };

    // Ocultar al cargar la página o volver desde bfcache
    window.addEventListener('pageshow', hide);
    document.addEventListener('DOMContentLoaded', hide);

    // Interceptar clicks en enlaces de navegación interna
    document.addEventListener('click', function(ev){
        const a = ev.target.closest('a');
        if (!a) return;
        if (a.hasAttribute('data-no-loader')) return;
        const href = a.getAttribute('href');
        if (!href) return;
        if (href.startsWith('#')) return;                   // ancla
        if (href.startsWith('javascript:')) return;
        if (href.startsWith('mailto:')) return;
        if (href.startsWith('tel:')) return;
        if (a.getAttribute('target') === '_blank') return;  // nueva pestaña
        if (a.hasAttribute('download')) return;
        // Hrefs "javascript:void(0)" o vacíos
        if (href === '' || href === '#') return;
        // Si es modifier click (abrir en nueva pestaña) no mostrar
        if (ev.ctrlKey || ev.metaKey || ev.shiftKey || ev.button !== 0) return;
        show();
    });

    // Interceptar submits de formularios
    document.addEventListener('submit', function(ev){
        const form = ev.target.closest('form');
        if (!form) return;
        if (form.hasAttribute('data-no-loader')) return;
        // Si el submit fue prevenido, no mostrar
        // Usamos un microtask para chequear si defaultPrevented
        setTimeout(() => {
            if (!ev.defaultPrevented) show('Procesando…');
        }, 0);
    }, { capture: true });

    // Ocultar si la navegación fue abortada
    window.addEventListener('beforeunload', function(){
        // Muestra el loader antes de dejar la página
        show();
    });
})();
