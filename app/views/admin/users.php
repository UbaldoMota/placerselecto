<?php
/**
 * admin/users.php — Gestión de usuarios.
 */
$estado = $filtros['estado'] ?? '';
$buscar = $filtros['buscar'] ?? '';
?>

<!-- Modal de confirmación de eliminación -->
<div class="modal fade" id="modalEliminar" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="background:var(--color-bg-card);border:1px solid var(--color-border)">
            <div class="modal-header" style="border-color:var(--color-border)">
                <h5 class="modal-title fw-bold text-danger">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>Eliminar usuario permanentemente
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="mb-2">Vas a eliminar a <strong id="modalNombreUsuario"></strong>.</p>
                <div class="alert" style="background:rgba(220,53,69,.1);border:1px solid rgba(220,53,69,.25);color:#dc3545;font-size:.85rem">
                    <i class="bi bi-trash3-fill me-1"></i>
                    Esta acción es <strong>irreversible</strong>. Se borrarán:
                    <ul class="mb-0 mt-1">
                        <li>La cuenta del usuario</li>
                        <li>Todos sus perfiles publicados y en revisión</li>
                        <li>Todas las fotos e imágenes</li>
                        <li>Sus anuncios y pagos</li>
                    </ul>
                </div>
                <label class="form-label" style="font-size:.85rem">
                    Escribe <code>SI_ELIMINAR</code> para confirmar:
                </label>
                <input type="text" id="inputConfirmar" class="form-control" placeholder="SI_ELIMINAR" autocomplete="off">
            </div>
            <div class="modal-footer" style="border-color:var(--color-border)">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <form id="formEliminar" method="POST" style="display:inline">
                    <?= $csrfField ?>
                    <input type="hidden" name="confirmar" value="SI_ELIMINAR">
                    <button type="submit" id="btnConfirmarEliminar" class="btn btn-danger" disabled>
                        <i class="bi bi-trash3 me-1"></i>Eliminar definitivamente
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="container-fluid px-4 py-4" data-app-url="<?= APP_URL ?>">

    <!-- Encabezado -->
    <div class="d-flex flex-column flex-sm-row align-items-start align-items-sm-center justify-content-between gap-3 mb-4">
        <div>
            <h1 class="h4 fw-bold mb-1">
                <i class="bi bi-people text-primary me-2"></i>Usuarios
            </h1>
            <p class="text-muted mb-0" style="font-size:.875rem">
                <?= number_format((int)($pagination['total'] ?? 0)) ?> usuario(s) encontrado(s)
            </p>
        </div>
        <a href="<?= APP_URL ?>/admin" class="btn btn-sm btn-secondary">
            <i class="bi bi-arrow-left me-1"></i>Dashboard
        </a>
    </div>

    <!-- Filtros -->
    <div class="card mb-4">
        <div class="card-body py-2">
            <form class="d-flex flex-wrap gap-2 align-items-end" method="GET">
                <div>
                    <label class="form-label mb-1" style="font-size:.75rem">Buscar</label>
                    <input type="search" name="q" class="form-control form-control-sm"
                           placeholder="Nombre o email..." value="<?= e($buscar) ?>" style="width:220px">
                </div>
                <div>
                    <label class="form-label mb-1" style="font-size:.75rem">Estado</label>
                    <select name="estado" class="form-select form-select-sm" style="width:160px">
                        <option value="">Todos</option>
                        <option value="pendiente"  <?= $estado==='pendiente'  ? 'selected':'' ?>>Pendiente</option>
                        <option value="aprobado"   <?= $estado==='aprobado'   ? 'selected':'' ?>>Aprobado</option>
                        <option value="rechazado"  <?= $estado==='rechazado'  ? 'selected':'' ?>>Rechazado</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-sm btn-primary">
                    <i class="bi bi-search me-1"></i>Filtrar
                </button>
                <?php if ($buscar || $estado): ?>
                <a href="<?= APP_URL ?>/admin/usuarios" class="btn btn-sm btn-secondary">
                    <i class="bi bi-x-circle me-1"></i>Limpiar
                </a>
                <?php endif; ?>
            </form>
        </div>
    </div>

    <!-- Tabla -->
    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table mb-0" style="font-size:.82rem">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Usuario</th>
                            <th>Estado</th>
                            <th class="text-center" title="En sesión ahora (último login < 30 min)">
                                <i class="bi bi-circle-fill" style="color:#10B981;font-size:.65rem"></i> Activo
                            </th>
                            <th class="d-none d-md-table-cell text-center">Conexiones</th>
                            <th class="d-none d-lg-table-cell text-center">Perfiles</th>
                            <th class="d-none d-lg-table-cell text-center">Visitas</th>
                            <th class="d-none d-xl-table-cell">Registro</th>
                            <th class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (empty($usuarios)): ?>
                        <tr><td colspan="9" class="text-center text-muted py-5">No se encontraron usuarios.</td></tr>
                    <?php else: ?>
                    <?php foreach ($usuarios as $u):
                        $enSesion = (bool)($u['en_sesion'] ?? false);
                        $conexiones = (int)($u['conexiones'] ?? 0);
                        $vistas = (int)($u['vistas_perfiles'] ?? 0);
                        $totalPerfiles = (int)($u['total_perfiles'] ?? 0);
                        $perfilesPub = (int)($u['perfiles_publicados'] ?? 0);
                    ?>
                    <tr style="<?= $enSesion ? 'background:rgba(16,185,129,.04)' : '' ?>">
                        <td class="text-muted" style="font-size:.78rem"><?= (int)$u['id'] ?></td>

                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div style="position:relative;flex-shrink:0">
                                    <div style="width:36px;height:36px;border-radius:50%;background:rgba(255,45,117,.1);border:1px solid rgba(255,45,117,.2);display:flex;align-items:center;justify-content:center;color:var(--color-primary);font-weight:700;font-size:.85rem">
                                        <?= strtoupper(mb_substr($u['nombre'], 0, 1)) ?>
                                    </div>
                                    <?php if ($enSesion): ?>
                                    <span style="position:absolute;bottom:0;right:0;width:10px;height:10px;border-radius:50%;background:#10B981;border:2px solid var(--color-bg-card)"></span>
                                    <?php endif; ?>
                                </div>
                                <div>
                                    <div class="fw-semibold"><?= e($u['nombre']) ?></div>
                                    <div class="text-muted" style="font-size:.72rem"><?= e($u['email']) ?></div>
                                </div>
                            </div>
                        </td>

                        <td>
                            <?php if ($u['rol'] === 'admin'): ?>
                            <span class="badge-estado" style="background:rgba(23,162,184,.15);color:#17a2b8">
                                <i class="bi bi-shield-check me-1"></i>Admin
                            </span>
                            <?php else:
                                $vMap = [
                                    'pendiente' => ['badge-pendiente', 'clock-history',   'Pendiente'],
                                    'aprobado'  => ['badge-publicado', 'patch-check-fill','Activo'],
                                    'rechazado' => ['badge-rechazado', 'x-octagon',       'Suspendido'],
                                ];
                                [$cls, $ico, $lbl] = $vMap[$u['estado_verificacion']] ?? ['','question','?'];
                            ?>
                            <span class="badge-estado <?= $cls ?>">
                                <i class="bi bi-<?= $ico ?> me-1"></i><?= $lbl ?>
                            </span>
                            <?php if (($u['documento_estado'] ?? '') === 'pendiente'): ?>
                            <br>
                            <span class="badge-estado mt-1"
                                  style="background:rgba(255,193,7,.12);color:#c8a000;font-size:.65rem;display:inline-flex;gap:3px;align-items:center"
                                  title="El usuario envió su documento de identidad y espera revisión">
                                <i class="bi bi-card-checklist"></i> Doc. pendiente
                            </span>
                            <?php endif; ?>
                            <?php endif; ?>
                        </td>

                        <!-- En sesión -->
                        <td class="text-center">
                            <?php if ($enSesion): ?>
                            <span title="En sesión ahora">
                                <i class="bi bi-circle-fill" style="color:#10B981;font-size:.7rem"></i>
                                <span class="d-none d-sm-inline text-success" style="font-size:.75rem"> Online</span>
                            </span>
                            <?php elseif ($u['ultimo_login']): ?>
                            <span class="text-muted" title="Último acceso: <?= e($u['ultimo_login']) ?>" style="font-size:.75rem">
                                <?= e(Security::timeAgo($u['ultimo_login'])) ?>
                            </span>
                            <?php else: ?>
                            <span class="text-muted" style="font-size:.75rem">Nunca</span>
                            <?php endif; ?>
                        </td>

                        <!-- Conexiones -->
                        <td class="d-none d-md-table-cell text-center">
                            <span style="font-size:.8rem"><?= number_format($conexiones) ?></span>
                        </td>

                        <!-- Perfiles -->
                        <td class="d-none d-lg-table-cell text-center">
                            <span style="font-size:.8rem">
                                <?= $perfilesPub ?><span class="text-muted">/<?= $totalPerfiles ?></span>
                            </span>
                            <div class="text-muted" style="font-size:.7rem">pub/total</div>
                        </td>

                        <!-- Visitas -->
                        <td class="d-none d-lg-table-cell text-center">
                            <span style="font-size:.8rem"><?= number_format($vistas) ?></span>
                        </td>

                        <!-- Fecha registro -->
                        <td class="d-none d-xl-table-cell text-muted" style="font-size:.78rem;white-space:nowrap">
                            <?= e(Security::timeAgo($u['fecha_creacion'])) ?>
                        </td>

                        <!-- Acciones -->
                        <td class="text-end">
                            <div class="d-flex gap-1 justify-content-end">
                                <a href="<?= APP_URL ?>/admin/usuario/<?= (int)$u['id'] ?>"
                                   class="btn btn-sm btn-secondary" title="Ver detalle">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <?php if ($u['rol'] !== 'admin'): ?>
                                    <?php if ($u['estado_verificacion'] !== 'aprobado'): ?>
                                    <form method="POST" action="<?= APP_URL ?>/admin/usuario/<?= (int)$u['id'] ?>/aprobar">
                                        <?= $csrfField ?>
                                        <button class="btn btn-sm" title="Activar"
                                                style="background:rgba(16,185,129,.1);color:#10B981;border:1px solid rgba(16,185,129,.25)">
                                            <i class="bi bi-check-lg"></i>
                                        </button>
                                    </form>
                                    <?php endif; ?>
                                    <?php if ($u['estado_verificacion'] !== 'rechazado'): ?>
                                    <form method="POST" action="<?= APP_URL ?>/admin/usuario/<?= (int)$u['id'] ?>/rechazar">
                                        <?= $csrfField ?>
                                        <button class="btn btn-sm" title="Suspender"
                                                style="background:rgba(255,193,7,.1);color:#F59E0B;border:1px solid rgba(255,193,7,.25)"
                                                data-confirm="¿Suspender a <?= e($u['nombre']) ?>?">
                                            <i class="bi bi-slash-circle"></i>
                                        </button>
                                    </form>
                                    <?php endif; ?>
                                    <button class="btn btn-sm btn-danger" title="Eliminar permanentemente"
                                            data-bs-toggle="modal" data-bs-target="#modalEliminar"
                                            data-user-id="<?= (int)$u['id'] ?>"
                                            data-user-nombre="<?= e($u['nombre']) ?>">
                                        <i class="bi bi-trash3"></i>
                                    </button>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Paginación -->
    <?php if (($pagination['pages'] ?? 1) > 1): ?>
    <nav class="mt-4 d-flex justify-content-center">
        <ul class="pagination">
            <li class="page-item <?= $pagination['current'] <= 1 ? 'disabled' : '' ?>">
                <a class="page-link" href="?page=<?= $pagination['current']-1 ?>&estado=<?= e($estado) ?>&q=<?= e($buscar) ?>">
                    <i class="bi bi-chevron-left"></i>
                </a>
            </li>
            <?php for ($p = 1; $p <= $pagination['pages']; $p++): ?>
            <li class="page-item <?= $p === (int)$pagination['current'] ? 'active' : '' ?>">
                <a class="page-link" href="?page=<?= $p ?>&estado=<?= e($estado) ?>&q=<?= e($buscar) ?>"><?= $p ?></a>
            </li>
            <?php endfor; ?>
            <li class="page-item <?= $pagination['current'] >= $pagination['pages'] ? 'disabled' : '' ?>">
                <a class="page-link" href="?page=<?= $pagination['current']+1 ?>&estado=<?= e($estado) ?>&q=<?= e($buscar) ?>">
                    <i class="bi bi-chevron-right"></i>
                </a>
            </li>
        </ul>
    </nav>
    <?php endif; ?>

</div>

<?php $extraJs = <<<'JS'
<script>
(function () {
    const modal    = document.getElementById('modalEliminar');
    const form     = document.getElementById('formEliminar');
    const inputConf= document.getElementById('inputConfirmar');
    const btnConf  = document.getElementById('btnConfirmarEliminar');
    const nombreEl = document.getElementById('modalNombreUsuario');

    modal.addEventListener('show.bs.modal', function (e) {
        const btn    = e.relatedTarget;
        const userId = btn.dataset.userId;
        const nombre = btn.dataset.userNombre;

        nombreEl.textContent = nombre;
        const base = document.querySelector('[data-app-url]')?.dataset.appUrl ?? '';
        form.action = base + '/admin/usuario/' + userId + '/eliminar';
        inputConf.value = '';
        btnConf.disabled = true;
    });

    inputConf.addEventListener('input', function () {
        btnConf.disabled = this.value.trim() !== 'SI_ELIMINAR';
    });
})();
</script>
JS;
?>
