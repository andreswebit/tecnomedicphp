<?php
require_once __DIR__ . '/../includes/auth.php';
portal_require_role(['admin']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';
    if ($accion === 'crear') {
        $nombre    = trim($_POST['nombre'] ?? '');
        $apellido  = trim($_POST['apellido'] ?? '');
        $email     = trim($_POST['email'] ?? '');
        $password  = $_POST['password'] ?? '';
        $dni       = trim($_POST['dni'] ?? '');
        $telefono  = '';
        if ($nombre && $apellido && $email && $password) {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $st = db()->prepare("INSERT INTO tm_usuarios (nombre,apellido,email,password_hash,dni,telefono,rol,activo,fecha_aprobacion) VALUES (?,?,?,?,?,?,?,1,NOW())");
            $rol = 'profesional';
            $st->bind_param('sssssss', $nombre, $apellido, $email, $hash, $dni, $telefono, $rol);
            $st->execute();
            header('Location: ' . strtok($_SERVER['REQUEST_URI'], '?') . '?ok=1');
            exit;
        }
    } elseif ($accion === 'asignar') {
        $paciente_id    = (int)($_POST['paciente_id'] ?? 0);
        $profesional_id = (int)($_POST['profesional_id'] ?? 0);
        $area           = trim($_POST['area'] ?? '');
        if ($paciente_id && $profesional_id && $area) {
            $st = db()->prepare("INSERT INTO tm_asignaciones (paciente_id, profesional_id, area, activa, fecha_asignacion) VALUES (?,?,?,1,NOW()) ON DUPLICATE KEY UPDATE activa=1");
            $st->bind_param('iis', $paciente_id, $profesional_id, $area);
            $st->execute();
            header('Location: ' . strtok($_SERVER['REQUEST_URI'], '?') . '?ok=1');
            exit;
        }
    } elseif ($accion === 'desasignar') {
        $id = (int)($_POST['id'] ?? 0);
        $st = db()->prepare("UPDATE tm_asignaciones SET activa = 0 WHERE id = ?");
        $st->bind_param('i', $id);
        $st->execute();
        header('Location: ' . strtok($_SERVER['REQUEST_URI'], '?') . '?ok=1');
        exit;
    }
}

$profesionales = [];
$asignaciones = [];
$pacientes = [];

try {
    $profesionales = db()->query(
        "SELECT * FROM tm_usuarios WHERE rol = 'profesional' ORDER BY apellido, nombre"
    )->fetch_all(MYSQLI_ASSOC);
    $asignaciones = db()->query(
        "SELECT a.id, a.area, a.fecha_asignacion, a.activa,
            p.id AS paciente_id, p.nombre AS paciente_nombre, p.apellido AS paciente_apellido,
            pr.id AS profesional_id, pr.nombre AS profesional_nombre, pr.apellido AS profesional_apellido
         FROM tm_asignaciones a
         JOIN tm_usuarios p ON p.id = a.paciente_id
         JOIN tm_usuarios pr ON pr.id = a.profesional_id
         WHERE a.activa = 1
         ORDER BY a.fecha_asignacion DESC"
    )->fetch_all(MYSQLI_ASSOC);
    $pacientes = db()->query(
        "SELECT id, nombre, apellido, dni FROM tm_usuarios WHERE rol = 'paciente' AND activo = 1 ORDER BY apellido, nombre"
    )->fetch_all(MYSQLI_ASSOC);
} catch (Throwable $e) {
    $profesionales = [];
    $asignaciones = [];
    $pacientes = [];
}

$asignaciones_json = json_encode($asignaciones, JSON_UNESCAPED_UNICODE);

$portal_titulo = 'Profesionales · Mi Portal';
$portal_activo = 'profesionales';
require __DIR__ . '/../includes/portal_header.php';
?>

<div class="stats" style="margin:28px 28px 0;">
    <div class="stat-card blue">
        <div class="stat-label">Profesionales</div>
        <div class="stat-value" style="text-align: end"><?= count($profesionales) ?></div>
        <div class="stat-sub">registrados</div>
    </div>
    <div class="stat-card green">
        <div class="stat-label">Asignaciones</div>
        <div class="stat-value" style="text-align: end"><?= count($asignaciones) ?></div>
        <div class="stat-sub">activas</div>
    </div>
    <div class="stat-card amber">
        <div class="stat-label">Pacientes</div>
        <div class="stat-value" style="text-align: end"><?= count($pacientes) ?></div>
        <div class="stat-sub">disponibles</div>
    </div>
</div>

<div style="padding:24px 28px 0;">
    <div class="topbar" style="margin-bottom:20px;">
        <div style="display:flex;align-items:center;gap:12px;">
            <div class="page-title">Profesionales</div>
        </div>
        <div style="display:flex;gap:8px;">
            <button class="btn-action btn-save" style="padding:9px 18px;font-size:13px;" onclick="openModal('prof')">
                ➕ Nuevo profesional
            </button>
            <button class="btn-action btn-save" style="padding:9px 18px;font-size:13px;" onclick="openModal('asig')">
                🔗 Nueva asignación
            </button>
        </div>
    </div>
</div>

<div class="table-card" style="margin:0 28px 28px;">
    <div class="table-header">
        <div class="table-title">👨‍⚕️ <?= count($profesionales) ?> profesional<?= count($profesionales) !== 1 ? 'es' : '' ?></div>
        <div class="search-wrap">
            <span class="search-icon">🔍</span>
            <input type="text" id="searchProfs" placeholder="Buscar…" style="width:200px;">
        </div>
    </div>
    <div class="table-wrap">
        <table id="tablaProfs">
            <thead>
                <tr>
                    <th class="sortable" data-col="0">Nombre<span class="sort-icon"></span></th>
                    <th class="sortable" data-col="1">DNI<span class="sort-icon"></span></th>
                    <th class="sortable" data-col="2">Email<span class="sort-icon"></span></th>
                    <th class="sortable" data-col="3">Estado<span class="sort-icon"></span></th>
                    <th class="actions-col">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($profesionales as $pr): ?>
                <tr>
                    <td><strong><?= htmlspecialchars($pr['apellido'] . ', ' . $pr['nombre']) ?></strong></td>
                    <td><?= htmlspecialchars($pr['dni']) ?></td>
                    <td><?= htmlspecialchars($pr['email']) ?></td>
                    <td>
                        <span class="badge <?= $pr['activo'] == 1 ? 'aprobado' : 'pendiente' ?>">
                            <?= $pr['activo'] == 1 ? '✅ Activo' : '⏳ Inactivo' ?>
                        </span>
                    </td>
                    <td class="actions-col">
                        <div class="btn-actions">
                            <button class="btn-action btn-save" style="padding:7px 12px;" onclick='verProfs(<?= json_encode($pr, JSON_UNESCAPED_UNICODE) ?>)'>👁</button>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php if (!$profesionales): ?>
        <div class="empty-state">
            <div class="empty-state-icon">👨‍⚕️</div>
            <div>No hay profesionales registrados.</div>
        </div>
        <?php endif; ?>
    </div>
</div>

<div class="table-card" style="margin:0 28px 28px;">
    <div class="table-header">
        <div class="table-title">🔗 <?= count($asignaciones) ?> asignación<?= count($asignaciones) !== 1 ? 'es' : '' ?> activas</div>
        <div class="search-wrap">
            <span class="search-icon">🔍</span>
            <input type="text" id="searchAsig" placeholder="Buscar…" style="width:200px;">
        </div>
    </div>
    <div class="table-wrap">
        <table id="tablaAsig">
            <thead>
                <tr>
                    <th class="sortable" data-col="0">Paciente<span class="sort-icon"></span></th>
                    <th class="sortable" data-col="1">Profesional<span class="sort-icon"></span></th>
                    <th class="sortable" data-col="2">Área<span class="sort-icon"></span></th>
                    <th class="sortable" data-col="3">Fecha<span class="sort-icon"></span></th>
                    <th class="actions-col">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($asignaciones as $a): ?>
                <tr>
                    <td><strong><?= htmlspecialchars($a['paciente_apellido'] . ', ' . $a['paciente_nombre']) ?></strong></td>
                    <td><?= htmlspecialchars($a['profesional_apellido'] . ', ' . $a['profesional_nombre']) ?></td>
                    <td><span class="badge"><?= htmlspecialchars(ucfirst($a['area'])) ?></span></td>
                    <td><?= htmlspecialchars(date('d/m/Y', strtotime($a['fecha_asignacion']))) ?></td>
                    <td class="actions-col">
                        <form method="post" onsubmit="return confirm('¿Desasignar este paciente?');">
                            <input type="hidden" name="accion" value="desasignar">
                            <input type="hidden" name="id" value="<?= $a['id'] ?>">
                            <button type="submit" class="btn-action btn-del" data-tooltip="Desasignar" style="width:100%;">🔓 Desasignar</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php if (!$asignaciones): ?>
        <div class="empty-state">
            <div class="empty-state-icon">🔗</div>
            <div>No hay asignaciones activas.</div>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Modal profesional -->
<div class="edit-modal-overlay" id="modalProf">
    <div class="edit-modal">
        <div class="edit-modal-header">
            <div class="edit-modal-title">➕ Nuevo profesional</div>
            <button class="edit-modal-close" onclick="closeModal('modalProf')">✕</button>
        </div>
        <form action="<?= b('/admin/profesionales.php') ?>" method="post">
            <input type="hidden" name="accion" value="crear">
            <div class="edit-grid">
                <div class="edit-group">
                    <div class="edit-label">Nombre *</div>
                    <div class="edit-input-wrap"><input class="edit-input" type="text" name="nombre" required></div>
                </div>
                <div class="edit-group">
                    <div class="edit-label">Apellido *</div>
                    <div class="edit-input-wrap"><input class="edit-input" type="text" name="apellido" required></div>
                </div>
                <div class="edit-group">
                    <div class="edit-label">DNI</div>
                    <div class="edit-input-wrap"><input class="edit-input" type="text" name="dni"></div>
                </div>
                <div class="edit-group">
                    <div class="edit-label">Especialidad</div>
                    <div class="edit-input-wrap"><input class="edit-input" type="text" name="especialidad" placeholder="Ej: Audiología"></div>
                </div>
                <div class="edit-group full">
                    <div class="edit-label">Email *</div>
                    <div class="edit-input-wrap"><input class="edit-input" type="email" name="email" required></div>
                </div>
                <div class="edit-group full">
                    <div class="edit-label">Contraseña *</div>
                    <div class="edit-input-wrap"><input class="edit-input" type="password" name="password" required minlength="6" placeholder="Mínimo 6 caracteres"></div>
                </div>
            </div>
            <div class="edit-footer">
                <button type="button" class="btn btn-outline" onclick="closeModal('modalProf')">Cancelar</button>
                <button type="submit" class="btn-edit-save">💾 Crear profesional</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal asignar -->
<div class="edit-modal-overlay" id="modalAsig">
    <div class="edit-modal">
        <div class="edit-modal-header">
            <div class="edit-modal-title">🔗 Nueva asignación</div>
            <button class="edit-modal-close" onclick="closeModal('modalAsig')">✕</button>
        </div>
        <form action="<?= b('/admin/profesionales.php') ?>" method="post">
            <input type="hidden" name="accion" value="asignar">
            <div class="edit-grid">
                <div class="edit-group full">
                    <div class="edit-label">Paciente *</div>
                    <div class="edit-input-wrap">
                        <select class="edit-input" name="paciente_id" required>
                            <option value="">— Seleccionar paciente —</option>
                            <?php foreach ($pacientes as $p): ?>
                            <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['apellido'] . ', ' . $p['nombre'] . ' (' . $p['dni'] . ')') ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="edit-group full">
                    <div class="edit-label">Profesional *</div>
                    <div class="edit-input-wrap">
                        <select class="edit-input" name="profesional_id" required>
                            <option value="">— Seleccionar profesional —</option>
                            <?php foreach ($profesionales as $pr): ?>
                            <option value="<?= $pr['id'] ?>"><?= htmlspecialchars($pr['apellido'] . ', ' . $pr['nombre']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="edit-group full">
                    <div class="edit-label">Área *</div>
                    <div class="edit-input-wrap">
                        <select class="edit-input" name="area" required>
                            <option value="">— Seleccionar área —</option>
                            <option value="audiologia">Audiología</option>
                            <option value="hiperbarica">Medicina hiperbárica</option>
                            <option value="nutricion">Nutrición</option>
                            <option value="ortopedia">Ortopedia y rehabilitación</option>
                            <option value="equipamiento">Equipamiento médico y quirúrgico</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="edit-footer">
                <button type="button" class="btn btn-outline" onclick="closeModal('modalAsig')">Cancelar</button>
                <button type="submit" class="btn-edit-save">💾 Asignar</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal ver profesional y pacientes por área -->
<div class="edit-modal-overlay" id="modalVerProf" onclick="if(event.target===this) closeModalVerProf()">
    <div class="edit-modal" style="max-width:500px;max-height:80vh;overflow-y:auto;">
        <div class="edit-modal-header">
            <div class="edit-modal-title" id="verProfTitle"></div>
            <button class="edit-modal-close btn-cerrar-rojo" onclick="closeModalVerProf()">❌</button>
        </div>
        <div class="edit-modal-body" id="verProfBody">Cargando…</div>
    </div>
</div>

<div id="toast-ok">✅ Acción realizada</div>

<script>
(function() {
    // Datos de asignaciones para ver pacientes por profesional
    var ASIGNACIONES = <?= $asignaciones_json ?>;

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

    setupTable('tablaProfs', 'searchProfs');
    setupTable('tablaAsig', 'searchAsig');

    window.verProfs = function(d) {
        var areas = {};
        ASIGNACIONES.forEach(function(a) {
            if (a.profesional_id == d.id) {
                if (!areas[a.area]) areas[a.area] = [];
                areas[a.area].push(a.paciente_apellido + ', ' + a.paciente_nombre);
            }
        });
        var html = '<div style="margin-bottom:8px;font-weight:600;">Email: ' + (d.email||'-') + '</div>';
        html += '<div style="margin-bottom:8px;font-weight:600;">DNI: ' + (d.dni||'-') + '</div>';
        html += '<div style="margin-bottom:12px;font-weight:600;">Pacientes por área:</div>';
        var areasNombres = {
            'audiologia': 'Audiología',
            'hiperbarica': 'Medicina Hiperbárica',
            'nutricion': 'Nutrición',
            'ortopedia': 'Ortopedia y Rehabilitación',
            'equipamiento': 'Equipamiento Médico y Quirúrgico'
        };
        Object.keys(areas).forEach(function(area) {
            html += '<div style="margin-bottom:10px;border-left:3px solid var(--tm-teal);padding-left:10px;">';
            html += '<div style="font-weight:600;margin-bottom:4px;">' + (areasNombres[area] || area) + '</div>';
            areas[area].forEach(function(p) {
                html += '<div style="padding:2px 0;">• ' + p + '</div>';
            });
            html += '</div>';
        });
        if (Object.keys(areas).length === 0) {
            html += '<div style="color:var(--muted);">Este profesional no tiene asignaciones activas.</div>';
        }
        document.getElementById('verProfTitle').textContent = d.apellido + ', ' + d.nombre;
        document.getElementById('verProfBody').innerHTML = html;
        document.getElementById('modalVerProf').classList.add('open');
    };

    window.closeModalVerProf = function() {
        document.getElementById('modalVerProf').classList.remove('open');
    };

    window.openModal = function(mode) {
        if (mode === 'prof') document.getElementById('modalProf').classList.add('open');
        else if (mode === 'asig') document.getElementById('modalAsig').classList.add('open');
    };
    window.closeModal = function(id) { document.getElementById(id).classList.remove('open'); };

    document.getElementById('modalProf').addEventListener('click', function(e) { if (e.target === this) closeModal('modalProf'); });
    document.getElementById('modalAsig').addEventListener('click', function(e) { if (e.target === this) closeModal('modalAsig'); });
    document.getElementById('modalVerProf').addEventListener('click', function(e) { if (e.target === this) closeModalVerProf(); });
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeModal('modalProf');
            closeModal('modalAsig');
            closeModalVerProf();
        }
    });
})();
</script>

<?php require __DIR__ . '/../includes/portal_footer.php'; ?>
