<?php
/** Paginación del listado de almacenamiento.
 *  Requiere: $totalPaginas, $paginaActual, $catFilter, $vista */
if (($totalPaginas ?? 1) <= 1) return;

$baseUrl = APP_URL . '/admin/almacenamiento?cat=' . urlencode($catFilter) . '&vista=' . urlencode($vista);
$pg = (int)$paginaActual;
$total = (int)$totalPaginas;
$ventana = 2; // páginas a cada lado
$from = max(1, $pg - $ventana);
$to   = min($total, $pg + $ventana);
?>
<nav class="mt-4 d-flex justify-content-center">
    <ul class="pagination mb-0">
        <li class="page-item <?= $pg <= 1 ? 'disabled' : '' ?>">
            <a class="page-link" href="<?= $pg <= 1 ? '#' : $baseUrl . '&page=1' ?>" aria-label="Primera">
                <i class="bi bi-chevron-double-left"></i>
            </a>
        </li>
        <li class="page-item <?= $pg <= 1 ? 'disabled' : '' ?>">
            <a class="page-link" href="<?= $pg <= 1 ? '#' : $baseUrl . '&page=' . ($pg - 1) ?>" aria-label="Anterior">
                <i class="bi bi-chevron-left"></i>
            </a>
        </li>

        <?php if ($from > 1): ?>
            <li class="page-item"><a class="page-link" href="<?= $baseUrl . '&page=1' ?>">1</a></li>
            <?php if ($from > 2): ?>
                <li class="page-item disabled"><span class="page-link">…</span></li>
            <?php endif; ?>
        <?php endif; ?>

        <?php for ($i = $from; $i <= $to; $i++): ?>
            <li class="page-item <?= $i === $pg ? 'active' : '' ?>">
                <a class="page-link" href="<?= $baseUrl . '&page=' . $i ?>"><?= $i ?></a>
            </li>
        <?php endfor; ?>

        <?php if ($to < $total): ?>
            <?php if ($to < $total - 1): ?>
                <li class="page-item disabled"><span class="page-link">…</span></li>
            <?php endif; ?>
            <li class="page-item"><a class="page-link" href="<?= $baseUrl . '&page=' . $total ?>"><?= $total ?></a></li>
        <?php endif; ?>

        <li class="page-item <?= $pg >= $total ? 'disabled' : '' ?>">
            <a class="page-link" href="<?= $pg >= $total ? '#' : $baseUrl . '&page=' . ($pg + 1) ?>" aria-label="Siguiente">
                <i class="bi bi-chevron-right"></i>
            </a>
        </li>
        <li class="page-item <?= $pg >= $total ? 'disabled' : '' ?>">
            <a class="page-link" href="<?= $pg >= $total ? '#' : $baseUrl . '&page=' . $total ?>" aria-label="Última">
                <i class="bi bi-chevron-double-right"></i>
            </a>
        </li>
    </ul>
</nav>
