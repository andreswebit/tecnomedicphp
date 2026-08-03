<?php
require_once __DIR__ . '/../includes/auth.php';
portal_require_role(['admin']);

$resultado = null;
$dniBuscado = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['dni'])) {
    $dniBuscado = preg_replace('/\D/', '', $_POST['dni']);
    $resultado = persona_estado($dniBuscado);
}

$portal_titulo = 'Consultar DNI · Mi Portal';
require __DIR__ . '/../includes/portal_header.php';
?>
<div class="portal-card" style="max-width:520px;margin:0 auto;">
    <h2>Consultar un DNI</h2>
    <p style="font-size:0.9rem;">Cruza si esa persona ya tiene cuenta en el portal y/o turnos cargados.</p>
    <form class="portal-form" method="post">
        <label>DNI</label>
        <input type="text" name="dni" required value="<?= htmlspecialchars($dniBuscado) ?>">
        <button type="submit" class="portal-btn">Buscar</button>
    </form>

    <?php if ($resultado): ?>
        <div style="margin-top:20px;">
            <p>
                <span class="badge <?= $resultado['tiene_cuenta'] ? 'aprobado' : 'pendiente' ?>">
                    <?= $resultado['tiene_cuenta'] ? 'Tiene cuenta en el portal' : 'Sin cuenta en el portal' ?>
                </span>
                <span class="badge <?= $resultado['tiene_turnos'] ? 'aprobado' : 'pendiente' ?>" style="margin-left:8px;">
                    <?= $resultado['tiene_turnos'] ? $resultado['cantidad_turnos'] . ' turno(s) cargado(s)' : 'Sin turnos' ?>
                </span>
            </p>

            <?php if ($resultado['usuario']): ?>
                <p><strong>Cuenta portal:</strong>
                    <?= htmlspecialchars($resultado['usuario']['apellido'] . ', ' . $resultado['usuario']['nombre']) ?>
                    (<?= htmlspecialchars($resultado['usuario']['rol']) ?>,
                    <?= $resultado['usuario']['activo'] ? 'activo' : 'pendiente de aprobación' ?>)
                </p>
            <?php endif; ?>

            <?php if ($resultado['persona']): ?>
                <p><strong>En el padrón:</strong>
                    <?= htmlspecialchars($resultado['persona']['apellido'] . ', ' . $resultado['persona']['nombre']) ?> —
                    Tel: <?= htmlspecialchars($resultado['persona']['telefono'] ?: '-') ?> —
                    Obra social: <?= htmlspecialchars($resultado['persona']['obra_social'] ?? '-') ?>
                </p>
            <?php else: ?>
                <p>Este DNI todavía no figura en el padrón (ni turnos ni registro en el portal).</p>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>
<?php require __DIR__ . '/../includes/portal_footer.php'; ?>
