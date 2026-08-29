<?php
require_once __DIR__ . '/../includes/auth.php';
portal_require_role(['admin']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';
    if ($accion === 'aprobar') {
        usuario_aprobar((int)($_POST['id'] ?? 0));
        header('Location: ' . strtok($_SERVER['REQUEST_URI'], '?') . '?ok=1');
        exit;
    } elseif ($accion === 'rechazar') {
        usuario_rechazar((int)($_POST['id'] ?? 0));
        header('Location: ' . strtok($_SERVER['REQUEST_URI'], '?') . '?ok=1');
        exit;
    }
}

$pendientes = usuarios_pendientes();
$pacientes  = pacientes_todos_activos();

$portal_titulo = 'Pacientes · Mi Portal';
$portal_activo = 'pacientes';
require __DIR__ . '/../includes/portal_header.php';
?>

<div class="stats" style="margin:28px 28px 0;">
    <div class="stat-card blue">
        <div class="stat-label">Total</div>
        <div class="stat-value"><?= count($pacientes) ?></div>
        <div class="stat-sub">pacientes activos</div>
    </div>
    <div class="stat-card amber">
        <div class="stat-label">Pendientes</div>
        <div class="stat-value"><?= count($pendientes) ?></div>
        <div class="stat-sub">por aprobar</div>
    </div>
</div>

<div style="padding:24px 28px 0;">
    <div class="topbar" style="margin-bottom:20px;">
        <div style="display:flex;align-items:center;gap:12px;">
            <div class="page-title">Pacientes</div>
        </div>
    </div>
</div>

<?php if (count($pendientes)): ?>
<div class="table-card" style="margin:0 28px 20px;">
    <div class="table-header">
        <div class="table-title">⏳ Pendientes de aprobación (<?= count($pendientes) ?>)</div>
        <div class="search-wrap">
            <span class="search-icon">🔍</span>
            <input type="text" id="searchPendientes" placeholder="Buscar…" style="width:200px;">
        </div>
    </div>
    <div class="table-wrap">
        <table id="tablaPendientes">
            <thead>
                <tr>
                    <th class="sortable" data-col="0">Nombre<span class="sort-icon"></span></th>
                    <th class="sortable" data-col="1">DNI<span class="sort-icon"></span></th>
                    <th class="sortable" data-col="2">Email<span class="sort-icon"></span></th>
                    <th class="sortable" data-col="3">Fecha<span class="sort-icon"></span></th>
                    <th class="actions-col">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($pendientes as $p): ?>
                <tr>
                    <td><strong><?= htmlspecialchars($p['apellido'] . ', ' . $p['nombre']) ?></strong></td>
                    <td><?= htmlspecialchars($p['dni']) ?></td>
                    <td><?= htmlspecialchars($p['email']) ?></td>
                    <td><?= htmlspecialchars($p['fecha_alta']) ?></td>
                    <td class="actions-col">
                        <div class="btn-actions">
                            <form method="post" style="flex:1;">
                                <input type="hidden" name="accion" value="aprobar">
                                <input type="hidden" name="id" value="<?= $p['id'] ?>">
                                <button type="submit" class="btn-action btn-save" data-tooltip="Aprobar" style="width:100%;">✅ Aprobar</button>
                            </form>
                            <form method="post" style="flex:1;" onsubmit="return confirm('¿Rechazar y eliminar esta solicitud?');">
                                <input type="hidden" name="accion" value="rechazar">
                                <input type="hidden" name="id" value="<?= $p['id'] ?>">
                                <button type="submit" class="btn-action btn-del" data-tooltip="Rechazar" style="width:100%;">❌ Rechazar</button>
                            </form>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<div class="table-card" style="margin:0 28px 28px;">
    <div class="table-header">
        <div class="table-title">🧑‍⚕️ Pacientes activos (<?= count($pacientes) ?>)</div>
        <div class="search-wrap">
            <span class="search-icon">🔍</span>
            <input type="text" id="searchActivos" placeholder="Buscar…" style="width:220px;">
        </div>
    </div>
    <div class="table-wrap">
        <table id="tablaActivos">
            <thead>
                <tr>
                    <th class="sortable" data-col="0">Nombre<span class="sort-icon"></span></th>
                    <th class="sortable" data-col="1">DNI<span class="sort-icon"></span></th>
                    <th class="sortable" data-col="2">Email<span class="sort-icon"></span></th>
                    <th>Teléfono</th>
                    <th class="actions-col">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($pacientes as $p): ?>
                <tr>
                    <td><strong><?= htmlspecialchars($p['apellido'] . ', ' . $p['nombre']) ?></strong></td>
                    <td><?= htmlspecialchars($p['dni']) ?></td>
                    <td><?= htmlspecialchars($p['email']) ?></td>
                    <td><?= htmlspecialchars($p['telefono'] ?: '-') ?></td>
                    <td class="actions-col">
                        <div class="btn-actions">
                            <button class="btn-action btn-save" data-tooltip="Ver ficha médica" style="padding:7px 12px;" onclick="abrirFicha(<?= $p['id'] ?>)">
                                📋 Ficha
                            </button>
                            <a href="<?= b('/portal/nutricion/registro.php?paciente_id=' . $p['id']) ?>" class="btn-action btn-print-turn" data-tooltip="Nutrición" style="padding:7px 12px;display:flex;align-items:center;">
                                🥗 Nutrición
                            </a>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php if (!$pacientes): ?>
        <div class="empty-state">
            <div class="empty-state-icon">🧑‍⚕️</div>
            <div>No hay pacientes activos.</div>
        </div>
        <?php endif; ?>
    </div>
</div>

<div id="toast-ok">✅ Acción realizada</div>

<script>
(function() {
    if (new URLSearchParams(location.search).get('ok') === '1') {
        var t = document.getElementById('toast-ok');
        t.classList.add('show');
        setTimeout(function() { t.classList.remove('show'); history.replaceState({}, '', location.pathname); }, 3000);
    }

    function setupTable(tableId, searchId) {
        var search = document.getElementById(searchId);
        if (!search) return;
        search.addEventListener('input', function() {
            var q = this.value.toLowerCase();
            document.querySelectorAll('#' + tableId + ' tbody tr').forEach(function(r) {
                r.style.display = r.textContent.toLowerCase().includes(q) ? '' : 'none';
            });
        });

        var sortState = { col: -1, dir: 'asc' };
        document.querySelectorAll('#' + tableId + ' th.sortable').forEach(function(th) {
            th.addEventListener('click', function() {
                var col = parseInt(th.dataset.col);
                var dir = (sortState.col === col && sortState.dir === 'asc') ? 'desc' : 'asc';
                sortState = { col: col, dir: dir };
                document.querySelectorAll('#' + tableId + ' th.sortable').forEach(function(h) { h.classList.remove('asc','desc'); });
                th.classList.add(dir);
                sortTable(tableId, col, dir);
            });
        });
    }

    function sortTable(tableId, col, dir) {
        var tbody = document.querySelector('#' + tableId + ' tbody');
        var rows = Array.from(tbody.querySelectorAll('tr'));
        rows.sort(function(a, b) {
            var aVal = (a.querySelectorAll('td')[col] || {textContent:''}).textContent.trim().toLowerCase();
            var bVal = (b.querySelectorAll('td')[col] || {textContent:''}).textContent.trim().toLowerCase();
            if (aVal < bVal) return dir === 'asc' ? -1 : 1;
            if (aVal > bVal) return dir === 'asc' ? 1 : -1;
            return 0;
        });
        rows.forEach(function(r) { tbody.appendChild(r); });
    }

    setupTable('tablaPendientes', 'searchPendientes');
    setupTable('tablaActivos', 'searchActivos');
})();
</script>

<?php require __DIR__ . '/../includes/portal_footer.php'; ?>
