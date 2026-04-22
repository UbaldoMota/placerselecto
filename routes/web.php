<?php
/**
 * web.php
 * Definición de todas las rutas de la aplicación.
 * Formato: [METHOD, URI_PATTERN, Controller, metodo, middleware[]]
 *
 * URI patterns soportados:
 *   - Exacta:    '/login'
 *   - Con param: '/anuncio/{id}'   → capturado como $params['id']
 *   - Con param: '/usuario/{id}/editar'
 */

return [

    // ---------------------------------------------------------
    // RUTAS PÚBLICAS
    // ---------------------------------------------------------
    ['GET',  '/',                        'HomeController',     'index',     []],
    ['GET',  '/anuncios',                'AdsController',      'index',     []],
    ['GET',  '/anuncio/crear',           'AdsController',      'create',    ['auth']],
    ['POST', '/anuncio/crear',           'AdsController',      'store',     ['auth', 'csrf']],
    ['GET',  '/anuncio/{id}',            'AdsController',      'show',      []],
    ['GET',  '/buscar',                  'AdsController',      'search',    []],

    // ---------------------------------------------------------
    // AUTENTICACIÓN
    // ---------------------------------------------------------
    // Paso 1 — selección de tipo de cuenta
    ['GET',  '/registro',                        'AuthController', 'showTipoRegistro',        ['guest']],
    // Registro rápido — comentarista (email + password)
    ['GET',  '/registro/comentarista',           'AuthController', 'showRegistroComentarista', ['guest']],
    ['POST', '/registro/comentarista',           'AuthController', 'storeRegistroComentarista',['guest', 'csrf']],
    // Paso 2 — datos de contacto (publicador)
    ['GET',  '/registro/publicador',             'AuthController', 'showContactoPublicador',  ['guest']],
    ['POST', '/registro/publicador',             'AuthController', 'storeContacto',           ['guest', 'csrf']],
    // Paso 3a — verificar SMS
    ['GET',  '/registro/verificar-sms',          'AuthController', 'showVerificarSms',        ['guest']],
    ['POST', '/registro/verificar-sms',          'AuthController', 'verificarSms',            ['guest', 'csrf']],
    ['POST', '/registro/reenviar-sms',           'AuthController', 'reenviarSms',             ['guest', 'csrf']],
    // Paso 3b — verificar email
    ['GET',  '/registro/verificar-email',        'AuthController', 'showVerificarEmail',      ['guest']],
    ['POST', '/registro/verificar-email',        'AuthController', 'verificarEmail',          ['guest', 'csrf']],
    ['POST', '/registro/reenviar-email',         'AuthController', 'reenviarEmail',           ['guest', 'csrf']],
    ['POST', '/registro/corregir-email',         'AuthController', 'corregirEmail',           ['guest', 'csrf']],
    // Paso 4 — contraseña y nombre
    ['GET',  '/registro/completar',              'AuthController', 'showCompletar',           ['guest']],
    ['POST', '/registro/completar',              'AuthController', 'completar',               ['guest', 'csrf']],

    ['GET',  '/login',                   'AuthController',     'showLogin',       ['guest']],
    ['POST', '/login',                   'AuthController',     'login',           ['guest', 'csrf']],
    ['POST', '/logout',                  'AuthController',     'logout',          ['auth']],
    ['GET',  '/recuperar-password',      'AuthController',     'showRecover',     ['guest']],
    ['POST', '/recuperar-password',      'AuthController',     'recoverPassword', ['guest', 'csrf']],

    // ---------------------------------------------------------
    // VERIFICACIÓN DE MAYORÍA DE EDAD
    // ---------------------------------------------------------
    ['GET',  '/verificar-edad',          'AuthController',     'showAgeGate',     []],
    ['POST', '/verificar-edad',          'AuthController',     'confirmAge',      ['csrf']],

    // ---------------------------------------------------------
    // PANEL DE USUARIO (requiere autenticación)
    // ---------------------------------------------------------
    ['GET',  '/dashboard',               'UserController',     'dashboard',     ['auth']],
    ['GET',  '/mis-anuncios',            'UserController',     'myAds',         ['auth']],
    ['GET',  '/anuncio/{id}/editar',     'AdsController',      'edit',          ['auth']],
    ['POST', '/anuncio/{id}/editar',     'AdsController',      'update',        ['auth', 'csrf']],
    ['POST', '/anuncio/{id}/eliminar',   'AdsController',      'delete',        ['auth', 'csrf']],

    // ---------------------------------------------------------
    // SISTEMA DE DESTACADOS / PAGOS (legacy anuncios)
    // ---------------------------------------------------------
    ['GET',  '/destacar/{id_anuncio}',   'PaymentController',  'showPlans',    ['auth']],
    ['POST', '/destacar/{id_anuncio}',   'PaymentController',  'processPlan',  ['auth', 'csrf']],
    ['GET',  '/pago/confirmacion/{id}',  'PaymentController',  'confirmation', ['auth']],

    // ---------------------------------------------------------
    // TOKENS — compra de paquetes + historial
    // ---------------------------------------------------------
    ['GET',  '/tokens/comprar',                 'PaymentController', 'showPackages',             ['auth']],
    ['POST', '/tokens/comprar/{id_paquete}',    'PaymentController', 'buyPackage',               ['auth', 'csrf']],
    ['GET',  '/tokens/confirmacion/{id}',       'PaymentController', 'tokenPurchaseConfirmation',['auth']],
    ['GET',  '/mis-tokens',                     'UserController',    'misTokens',                ['auth']],

    // ---------------------------------------------------------
    // BOOSTS — destacar perfil con tokens
    // ---------------------------------------------------------
    ['GET',  '/perfil/{id}/destacar',         'BoostController', 'show',     ['auth']],
    ['POST', '/perfil/{id}/destacar',         'BoostController', 'create',   ['auth', 'csrf']],
    ['POST', '/perfil/boost/{id}/cancelar',   'BoostController', 'cancel',   ['auth', 'csrf']],

    // ---------------------------------------------------------
    // REPORTES
    // ---------------------------------------------------------
    ['POST', '/anuncio/{id}/reportar',   'AdsController',      'report',       ['csrf']],

    // ---------------------------------------------------------
    // PANEL ADMIN (requiere rol admin)
    // ---------------------------------------------------------
    ['GET',  '/admin',                    'AdminController',   'dashboard',           ['auth', 'admin']],
    ['GET',  '/admin/usuarios',           'AdminController',   'users',               ['auth', 'admin']],
    ['GET',  '/admin/usuario/{id}',       'AdminController',   'userDetail',          ['auth', 'admin']],
    ['POST', '/admin/usuario/{id}/aprobar',  'AdminController','approveUser',         ['auth', 'admin', 'csrf']],
    ['POST', '/admin/usuario/{id}/rechazar', 'AdminController','rejectUser',          ['auth', 'admin', 'csrf']],
    ['POST', '/admin/usuario/{id}/eliminar', 'AdminController','deleteUser',          ['auth', 'admin', 'csrf']],
    ['GET',  '/admin/anuncios',           'AdminController',   'ads',                 ['auth', 'admin']],
    ['POST', '/admin/anuncio/{id}/publicar', 'AdminController','publishAd',           ['auth', 'admin', 'csrf']],
    ['POST', '/admin/anuncio/{id}/eliminar', 'AdminController','deleteAd',            ['auth', 'admin', 'csrf']],
    ['GET',  '/admin/reportes',           'AdminController',   'reports',             ['auth', 'admin']],
    ['POST', '/admin/reporte/{id}/resolver',        'AdminController','resolveReport',       ['auth', 'admin', 'csrf']],
    ['POST', '/admin/reporte/{id}/rechazar',        'AdminController','rejectReport',        ['auth', 'admin', 'csrf']],
    ['POST', '/admin/reporte/{id}/pedir-info',      'AdminController','askInfoReport',       ['auth', 'admin', 'csrf']],
    ['POST', '/admin/reporte/{id}/revisado',        'AdminController','markReviewed',        ['auth', 'admin', 'csrf']],
    ['POST', '/admin/reporte/{id}/nota',            'AdminController','saveNotaReport',      ['auth', 'admin', 'csrf']],
    ['POST', '/admin/reporte/{id}/eliminar-perfil', 'AdminController','deletePerfilFromReport', ['auth', 'admin', 'csrf']],
    ['POST', '/admin/reporte/{id}/suspender',       'AdminController','suspendUserFromReport',  ['auth', 'admin', 'csrf']],
    ['GET',  '/admin/pagos',              'AdminController',   'payments',            ['auth', 'admin']],
    ['POST', '/admin/usuario/{id}/verificacion', 'AdminController', 'toggleVerificacion', ['auth', 'admin', 'csrf']],
    ['POST', '/admin/usuario/{id}/saldo',        'AdminController', 'ajustarSaldo',       ['auth', 'admin', 'csrf']],

    // ---------------------------------------------------------
    // ADMIN — TOKENS (paquetes, tarifas, movimientos)
    // ---------------------------------------------------------
    ['GET',  '/admin/tokens',                       'AdminController', 'tokensIndex',         ['auth', 'admin']],
    ['GET',  '/admin/tokens/paquetes',              'AdminController', 'tokensPaquetes',      ['auth', 'admin']],
    ['POST', '/admin/tokens/paquete/crear',         'AdminController', 'tokensPaqueteCrear',  ['auth', 'admin', 'csrf']],
    ['POST', '/admin/tokens/paquete/{id}/editar',   'AdminController', 'tokensPaqueteEditar', ['auth', 'admin', 'csrf']],
    ['POST', '/admin/tokens/paquete/{id}/toggle',   'AdminController', 'tokensPaqueteToggle', ['auth', 'admin', 'csrf']],
    ['POST', '/admin/tokens/paquete/{id}/eliminar', 'AdminController', 'tokensPaqueteEliminar', ['auth', 'admin', 'csrf']],
    ['GET',  '/admin/tokens/tarifas',               'AdminController', 'tokensTarifas',       ['auth', 'admin']],
    ['POST', '/admin/tokens/tarifa/{tipo}',         'AdminController', 'tokensTarifaActualizar', ['auth', 'admin', 'csrf']],
    ['GET',  '/admin/tokens/movimientos',           'AdminController', 'tokensMovimientos',   ['auth', 'admin']],

    // ---------------------------------------------------------
    // ADMIN — almacenamiento
    // ---------------------------------------------------------
    ['GET',  '/admin/almacenamiento',                 'AdminController', 'almacenamiento',         ['auth', 'admin']],
    ['POST', '/admin/almacenamiento/config',          'AdminController', 'almacenamientoConfig',   ['auth', 'admin', 'csrf']],
    ['GET',  '/admin/archivo',                        'AdminController', 'serveArchivo',           ['auth', 'admin']],

    // ---------------------------------------------------------
    // ADMIN — mensajes de soporte / reactivación
    // ---------------------------------------------------------
    ['GET',  '/admin/mensajes',               'AdminController', 'mensajes',          ['auth', 'admin']],
    ['POST', '/admin/mensaje/{id}/responder', 'AdminController', 'responderMensaje',  ['auth', 'admin', 'csrf']],
    ['POST', '/admin/mensaje/{id}/cerrar',    'AdminController', 'cerrarMensaje',     ['auth', 'admin', 'csrf']],

    // ---------------------------------------------------------
    // PERFILES (sistema principal)
    // ---------------------------------------------------------
    ['GET',  '/perfiles',                 'PerfilesController', 'index',    []],
    ['GET',  '/perfil/nuevo',             'PerfilesController', 'create',   ['auth']],
    ['POST', '/perfil/nuevo',             'PerfilesController', 'store',    ['auth', 'csrf']],
    ['GET',  '/perfil/{id}',              'PerfilesController', 'show',     []],
    ['GET',  '/perfil/{id}/editar',          'PerfilesController', 'edit',         ['auth']],
    ['POST', '/perfil/{id}/editar',          'PerfilesController', 'update',       ['auth', 'csrf']],
    ['GET',  '/perfil/{id}/verificar',         'PerfilesController', 'showVerificar',           ['auth']],
    ['GET',  '/perfil/{id}/verificar/fotos',  'PerfilesController', 'showVerificarFotos',      ['auth']],
    ['POST', '/perfil/{id}/verificar/fotos',  'PerfilesController', 'subirFotosVerificacion',  ['auth', 'csrf']],
    ['GET',  '/perfil/{id}/verificar/camara', 'PerfilesController', 'showVerificarCamara',     ['auth']],
    ['POST', '/perfil/{id}/verificar/video',  'PerfilesController', 'subirVideoVerificacion',  ['auth', 'csrf']],
    ['GET',  '/perfil/{id}/whatsapp',         'PerfilesController', 'whatsappRedirect', []],
    ['POST', '/perfil/{id}/eliminar',     'PerfilesController', 'delete',   ['auth', 'csrf']],
    ['POST', '/perfil/{id}/reportar',     'PerfilesController', 'report',   ['auth', 'csrf']],

    // Comentarios en perfiles (requieren login)
    ['POST', '/perfil/{id}/comentar',       'ComentarioController', 'store',  ['auth', 'csrf']],
    ['POST', '/comentario/{id}/eliminar',   'ComentarioController', 'delete', ['auth', 'csrf']],
    ['POST', '/comentario/{id}/reportar',   'ComentarioController', 'report', ['auth', 'csrf']],

    // Admin — moderación de comentarios
    ['GET',  '/admin/comentarios',               'AdminController', 'comentarios',           ['auth', 'admin']],
    ['POST', '/admin/comentario/{id}/ocultar',   'AdminController', 'comentarioOcultar',     ['auth', 'admin', 'csrf']],
    ['POST', '/admin/comentario/{id}/publicar',  'AdminController', 'comentarioPublicar',    ['auth', 'admin', 'csrf']],
    ['POST', '/admin/comentario/{id}/eliminar',  'AdminController', 'comentarioEliminar',    ['auth', 'admin', 'csrf']],
    ['GET',  '/mis-perfiles',             'UserController',     'misPerfiles',           ['auth']],
    ['GET',  '/mis-estadisticas',         'UserController',     'estadisticas',          ['auth']],
    ['GET',  '/verificacion/camara',      'UserController',     'showVerificacionCamara',['auth']],
    ['POST', '/verificacion/video',       'UserController',     'subirVideoVerificacion',['auth', 'csrf']],
    ['GET',  '/mi-cuenta/documento',      'UserController',     'showSubirDocumento',    ['auth']],
    ['POST', '/mi-cuenta/documento',      'UserController',     'subirDocumento',        ['auth', 'csrf']],
    ['GET',  '/cuenta/reactivar',         'UserController',     'showReactivacion',      ['auth']],
    ['POST', '/cuenta/reactivar',         'UserController',     'enviarReactivacion',    ['auth', 'csrf']],

    // Admin perfiles
    ['POST', '/admin/foto/{id}/ocultar',      'AdminController', 'toggleHidePhoto', ['auth', 'admin', 'csrf']],
    ['POST', '/admin/foto/{id}/eliminar',     'AdminController', 'deletePhoto',     ['auth', 'admin', 'csrf']],
    ['POST', '/admin/video/{id}/publicar',    'AdminController', 'videoPublicar',   ['auth', 'admin', 'csrf']],
    ['POST', '/admin/video/{id}/rechazar',    'AdminController', 'videoRechazar',   ['auth', 'admin', 'csrf']],
    ['POST', '/admin/video/{id}/eliminar',    'AdminController', 'videoEliminar',   ['auth', 'admin', 'csrf']],
    ['GET',  '/admin/perfiles',               'AdminController', 'perfiles',       ['auth', 'admin']],
    ['GET',  '/admin/perfil/{id}',            'AdminController', 'previewProfile', ['auth', 'admin']],
    ['GET',  '/admin/usuario/{id}/video',      'AdminController', 'serveUserVideo',       ['auth', 'admin']],
    ['GET',  '/admin/usuario/{id}/documento',          'AdminController', 'serveUserDocumento',   ['auth', 'admin']],
    ['POST', '/admin/usuario/{id}/documento/rechazar', 'AdminController', 'rejectDocument',      ['auth', 'admin', 'csrf']],
    ['GET',  '/admin/perfil/{id}/video',      'AdminController', 'serveProfileVideo', ['auth', 'admin']],
    ['POST', '/admin/perfil/{id}/publicar',   'AdminController', 'publishProfile', ['auth', 'admin', 'csrf']],
    ['POST', '/admin/perfil/{id}/rechazar',   'AdminController', 'rejectProfile',  ['auth', 'admin', 'csrf']],
    ['POST', '/admin/perfil/{id}/eliminar',   'AdminController', 'deleteProfile',  ['auth', 'admin', 'csrf']],

    // Toggle sin-anticipo (usuario autenticado)
    ['POST', '/perfil/sin-anticipo',      'UserController',    'toggleSinAnticipo',   ['auth', 'csrf']],

    // ---------------------------------------------------------
    // NOTIFICACIONES (campanita en tiempo real)
    // ---------------------------------------------------------
    ['GET',  '/api/notificaciones/pendientes', 'NotificacionController', 'pendientes',         []],
    ['GET',  '/notificaciones',                'NotificacionController', 'index',              ['auth']],
    ['POST', '/notificaciones/leer-todas',     'NotificacionController', 'marcarTodasLeidas',  ['auth', 'csrf']],
    ['POST', '/notificacion/{id}/leer',        'NotificacionController', 'marcarLeida',        ['auth', 'csrf']],
    ['POST', '/notificacion/{id}/eliminar',    'NotificacionController', 'eliminar',           ['auth', 'csrf']],

    // ---------------------------------------------------------
    // PÁGINAS LEGALES
    // ---------------------------------------------------------
    ['GET',  '/terminos',                'LegalController',    'terms',      []],
    ['GET',  '/privacidad',              'LegalController',    'privacy',    []],
    ['GET',  '/mayores-18',              'LegalController',    'adultNotice', []],

    // ---------------------------------------------------------
    // PROXY DE IMÁGENES (seguro — nunca expone ruta real)
    // ---------------------------------------------------------
    ['GET',  '/img/{token}',              'ImageController',    'serve',         []],
    ['GET',  '/video/{token}',            'ImageController',    'serveVideo',    []],

    // ---------------------------------------------------------
    // API REST (JSON)
    // ---------------------------------------------------------
    ['GET',  '/api/municipios/{id_estado}', 'ApiController',   'getMunicipios', []],
    ['GET',  '/api/anuncios',            'ApiController',      'getAds',       []],
    ['GET',  '/api/anuncio/{id}',        'ApiController',      'getAd',        []],
    ['POST', '/api/anuncio/{id}/like',   'ApiController',      'likeAd',       ['auth']],

    // Proxy de tiles OpenStreetMap — evade CSP img-src
    ['GET',  '/tile/{z}/{x}/{y}',        'ApiController',      'tile',         []],
    // Proxy de Nominatim — evade CSP connect-src
    ['GET',  '/api/geosearch',           'ApiController',      'geosearch',    []],

];
