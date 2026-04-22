<?php /** @var array $paquetes */ ?>
<div class="container py-4">

    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h4 fw-bold mb-1"><i class="bi bi-box-seam text-primary me-2"></i>Paquetes de recarga</h1>
            <p class="text-muted mb-0" style="font-size:.88rem">
                Define cuánto paga el usuario y cuántos tokens recibe.
            </p>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= APP_URL ?>/admin/tokens" class="btn btn-sm btn-secondary">
                <i class="bi bi-arrow-left me-1"></i>Resumen
            </a>
            <button type="button" class="btn btn-sm btn-primary"
                    data-bs-toggle="collapse" data-bs-target="#new-pkg-form">
                <i class="bi bi-plus-lg me-1"></i>Nuevo paquete
            </button>
        </div>
    </div>

    <!-- Formulario nuevo -->
    <div class="collapse mb-4" id="new-pkg-form">
        <div class="card">
            <div class="card-body">
                <form method="POST" action="<?= APP_URL ?>/admin/tokens/paquete/crear" class="row g-3">
                    <?= $csrfField ?>
                    <div class="col-md-3">
                        <label class="form-label">Nombre</label>
                        <input type="text" name="nombre" class="form-control" required maxlength="80">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Monto (MXN)</label>
                        <input type="number" name="monto_mxn" class="form-control" step="0.01" min="0" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Tokens</label>
                        <input type="number" name="tokens" class="form-control" min="1" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Bonus %</label>
                        <input type="number" name="bonus_pct" class="form-control" min="0" max="200" value="0">
                    </div>
                    <div class="col-md-1">
                        <label class="form-label">Orden</label>
                        <input type="number" name="orden" class="form-control" value="0">
                    </div>
                    <div class="col-md-2 d-flex align-items-end gap-2">
                        <div class="form-check">
                            <input type="checkbox" name="activo" class="form-check-input" id="newActivo" checked>
                            <label class="form-check-label" for="newActivo" style="font-size:.85rem">Activo</label>
                        </div>
                        <button type="submit" class="btn btn-primary btn-sm ms-auto">Crear</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Tabla -->
    <div class="card">
        <div class="table-responsive">
            <table class="table mb-0">
                <thead>
                    <tr>
                        <th>#</th><th>Nombre</th><th>Monto</th><th>Tokens</th>
                        <th>Bonus</th><th>$/token</th><th>Orden</th><th>Estado</th><th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($paquetes)): ?>
                        <tr><td colspan="9" class="text-center text-muted py-4">No hay paquetes.</td></tr>
                    <?php endif; ?>
                    <?php foreach ($paquetes as $p): ?>
                        <?php $porToken = $p['tokens'] > 0 ? ((float)$p['monto_mxn'] / (int)$p['tokens']) : 0; ?>
                        <tr>
                            <td><?= (int)$p['id'] ?></td>
                            <td>
                                <strong><?= e($p['nombre']) ?></strong>
                                <?php if ((int)$p['bonus_pct'] > 0): ?>
                                    <span class="badge-estado badge-destacado ms-1">+<?= (int)$p['bonus_pct'] ?>%</span>
                                <?php endif; ?>
                            </td>
                            <td>$<?= number_format((float)$p['monto_mxn'], 2) ?></td>
                            <td><strong><?= number_format((int)$p['tokens']) ?></strong></td>
                            <td><?= (int)$p['bonus_pct'] ?>%</td>
                            <td class="text-muted">$<?= number_format($porToken, 3) ?></td>
                            <td><?= (int)$p['orden'] ?></td>
                            <td>
                                <?php if ((int)$p['activo']): ?>
                                    <span class="badge-estado badge-publicado">Activo</span>
                                <?php else: ?>
                                    <span class="badge-estado badge-expirado">Inactivo</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end">
                                <div class="d-flex justify-content-end gap-1">
                                    <button type="button" class="btn btn-sm btn-secondary"
                                            data-bs-toggle="collapse"
                                            data-bs-target="#edit-<?= (int)$p['id'] ?>"
                                            title="Editar">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <form method="POST" action="<?= APP_URL ?>/admin/tokens/paquete/<?= (int)$p['id'] ?>/toggle" class="m-0">
                                        <?= $csrfField ?>
                                        <button type="submit" class="btn btn-sm btn-secondary"
                                                title="<?= (int)$p['activo'] ? 'Desactivar' : 'Activar' ?>">
                                            <i class="bi bi-<?= (int)$p['activo'] ? 'toggle-on text-success' : 'toggle-off' ?>"></i>
                                        </button>
                                    </form>
                                    <form method="POST" action="<?= APP_URL ?>/admin/tokens/paquete/<?= (int)$p['id'] ?>/eliminar" class="m-0"
                                          onsubmit="return confirm('¿Eliminar este paquete? Esta acción no se puede deshacer.')">
                                        <?= $csrfField ?>
                                        <button type="submit" class="btn btn-sm btn-danger" title="Eliminar">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        <!-- Formulario editar -->
                        <tr class="collapse" id="edit-<?= (int)$p['id'] ?>">
                            <td colspan="9" class="bg-alt">
                                <form method="POST" action="<?= APP_URL ?>/admin/tokens/paquete/<?= (int)$p['id'] ?>/editar" class="row g-2 py-2">
                                    <?= $csrfField ?>
                                    <div class="col-md-3">
                                        <label class="form-label">Nombre</label>
                                        <input type="text" name="nombre" class="form-control form-control-sm" value="<?= e($p['nombre']) ?>" required>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label">Monto (MXN)</label>
                                        <input type="number" name="monto_mxn" class="form-control form-control-sm" step="0.01" value="<?= e($p['monto_mxn']) ?>" required>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label">Tokens</label>
                                        <input type="number" name="tokens" class="form-control form-control-sm" value="<?= (int)$p['tokens'] ?>" required>
                                    </div>
                                    <div class="col-md-1">
                                        <label class="form-label">Bonus %</label>
                                        <input type="number" name="bonus_pct" class="form-control form-control-sm" value="<?= (int)$p['bonus_pct'] ?>">
                                    </div>
                                    <div class="col-md-1">
                                        <label class="form-label">Orden</label>
                                        <input type="number" name="orden" class="form-control form-control-sm" value="<?= (int)$p['orden'] ?>">
                                    </div>
                                    <div class="col-md-3 d-flex align-items-end gap-2">
                                        <div class="form-check">
                                            <input type="checkbox" name="activo" class="form-check-input" id="ed-<?= (int)$p['id'] ?>" <?= (int)$p['activo'] ? 'checked' : '' ?>>
                                            <label class="form-check-label" for="ed-<?= (int)$p['id'] ?>" style="font-size:.85rem">Activo</label>
                                        </div>
                                        <button type="submit" class="btn btn-primary btn-sm ms-auto">
                                            <i class="bi bi-check-lg"></i> Guardar
                                        </button>
                                    </div>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>
