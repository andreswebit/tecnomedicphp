<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db_recursos.php';
portal_require_login();

$recursos = recursos_listar_todos();
$areasNombres = [
    'audiologia' => 'Audiología', 'hiperbarica' => 'Medicina Hiperbárica',
    'nutricion' => 'Nutrición', 'ortopedia' => 'Ortopedia y Rehabilitación',
    'equipamiento' => 'Equipamiento Médico y Quirúrgico',
];

$portal_titulo = 'Recursos · Mi Portal';
require __DIR__ . '/../includes/portal_header.php';
?>
<div class="portal-card">
    <h2>📚 Recursos y formularios</h2>
    <?php if (!$recursos): ?>
        <p>Todavía no hay recursos cargados.</p>
    <?php else: ?>
        <table class="portal-table">
            <thead><tr><th>Título</th><th>Área</th><th>Tipo</th><th></th></tr></thead>
            <tbody>
            <?php foreach ($recursos as $r): ?>
                <tr>
                    <td>
                        <?= htmlspecialchars($r['titulo']) ?>
                        <?php if ($r['descripcion']): ?><br><small style="color:#64748b;"><?= htmlspecialchars($r['descripcion']) ?></small><?php endif; ?>
                    </td>
                    <td><?= htmlspecialchars($r['area'] ? ($areasNombres[$r['area']] ?? $r['area']) : 'General') ?></td>
                    <td><?= htmlspecialchars($r['tipo']) ?></td>
                    <td><a href="<?= b('/recursos_descargar.php?id=' . $r['id']) ?>" target="_blank" class="portal-btn secundario" style="padding:5px 12px;margin:0;">⬇ Descargar</a></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
<?php require __DIR__ . '/../includes/portal_footer.php'; ?>
