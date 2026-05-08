<?php
/**
 * head-meta.php — favicon, theme-color y application-name.
 * Reutilizable: incluir desde cualquier vista que defina su propio <head>
 * (layout.php, age-gate, errores 4xx/5xx, vistas auth standalone).
 */
?>
<link rel="icon" type="image/svg+xml" href="<?= APP_URL ?>/public/favicon.svg">
<link rel="alternate icon" href="<?= APP_URL ?>/public/favicon.svg">
<link rel="apple-touch-icon" href="<?= APP_URL ?>/public/favicon.svg">
<link rel="mask-icon" href="<?= APP_URL ?>/public/favicon.svg" color="#FF2D75">
<meta name="theme-color" content="#FF2D75">
<meta name="application-name" content="<?= e(APP_NAME) ?>">
<meta name="apple-mobile-web-app-title" content="<?= e(APP_NAME) ?>">
