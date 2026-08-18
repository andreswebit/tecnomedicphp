<?php
require_once __DIR__ . '/../../includes/auth.php';
portal_require_role(['profesional']);

$user = portal_current_user();
$perfilProf = perfil_profesional($user['id']);
$pacientes = pacientes_de_profesional($user['id']);

$portal_titulo = 'Mis pacientes · Mi Portal';
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
                        <button type="button" class="portal-btn secundario" style="padding:6px 12px;margin:0;" onclick="abrirFicha(<?= $p['id'] ?>)">📋 Ver ficha médica</button>
                        <?php if (($p['area'] ?? '') === 'nutricion'): ?>
                        <a href="<?= b('/portal/nutricion/registro.php?paciente_id=' . $p['id']) ?>" class="portal-btn secundario" style="padding:6px 12px;margin:0;">🥗 Registro alimentario</a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
<?php require __DIR__ . '/../../includes/portal_footer.php'; ?>
