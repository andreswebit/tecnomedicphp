<?php
require_once __DIR__ . '/../../includes/auth.php';
portal_require_role(['profesional']);

$user = portal_current_user();
$perfilProf = perfil_profesional($user['id']);
$pacientes = pacientes_de_profesional($user['id']);

$portal_titulo = 'Mis pacientes · Mi Portal';
$portal_activo = 'pacientes';
require __DIR__ . '/../../includes/portal_header.php';
?>
<div class="portal-card">
    <h2>Hola, <?= htmlspecialchars($user['nombre']) ?> 👋</h2>
    <p><strong>Área:</strong> <?= htmlspecialchars($perfilProf['area'] ?? '-') ?></p>
    <?php if (!empty($perfilProf['matricula'])): ?>
        <p><strong>Matrícula:</strong> <?= htmlspecialchars($perfilProf['matricula']) ?></p>
    <?php endif; ?>
</div>

<div class="portal-card">
    <h2>Mis pacientes asignados (<?= count($pacientes) ?>)</h2>
    <?php if (!$pacientes): ?>
        <p>Todavía no tenés pacientes asignados. El administrador te asigna pacientes desde el panel.</p>
    <?php else: ?>
        <table class="portal-table">
            <thead><tr><th>Nombre</th><th>DNI</th><th>Obra social</th><th>Teléfono</th><th>Área</th><th></th></tr></thead>
            <tbody>
            <?php foreach ($pacientes as $p): ?>
                <tr>
                    <td><?= htmlspecialchars($p['apellido'] . ', ' . $p['nombre']) ?></td>
                    <td><?= htmlspecialchars($p['dni']) ?></td>
                    <td><?= htmlspecialchars($p['obra_social'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($p['telefono'] ?: '-') ?></td>
                    <td><?= htmlspecialchars($p['area'] ?? '-') ?></td>
                    <td>
                        <div class="btn-actions" style="flex-wrap:wrap;">
                        <button type="button" class="btn-action btn-save" data-tooltip="Ver ficha médica" style="flex:none;padding:8px 14px;" onclick="abrirFicha(<?= $p['id'] ?>)">📋 Ficha</button>
                        <?php if (($p['area'] ?? '') === 'nutricion'): ?>
                        <a href="<?= b('/portal/nutricion/registro.php?paciente_id=' . $p['id']) ?>" class="btn-action btn-print-turn" data-tooltip="Registro alimentario" style="flex:none;padding:8px 14px;">🥗 Nutrición</a>
                        <?php endif; ?>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
<?php require __DIR__ . '/../../includes/portal_footer.php'; ?>
